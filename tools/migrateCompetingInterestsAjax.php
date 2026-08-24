<?php
declare(strict_types=1);

/**
 * @file tools/migrateCompetingInterestsAjax.php
 *
 * [ALAT SEKALI PAKAI - BUKAN BAGIAN PERMANEN SISTEM]
 * Dipasang sejajar index.php, diakses via browser oleh Site Administrator.
 * Menjalankan migrasi data competingInterests PER-PENULIS (field lama)
 * ke competingInterest LEVEL ARTIKEL (field baru) -- lihat
 * classes/article/Article.inc.php (setCompetingInterest) dan
 * classes/install/Upgrade.inc.php (migrateAuthorCompetingInterestsToArticle,
 * versi RESMI yang berjalan via alur upgrade.xml biasa).
 *
 * ALASAN ALAT INI TERPISAH DARI Upgrade::migrateAuthorCompetingInterestsToArticle():
 * Upgrade extends Installer -- constructor-nya memicu pemrosesan
 * upgrade.xml PENUH (bukan cuma method migrasi ini saja). Alat ini
 * SENGAJA TIDAK menginstansiasi Upgrade/Installer sama sekali --
 * logika migrasinya DITULIS ULANG BERDIRI SENDIRI di sini (bukan
 * duplikasi salah, karena diverifikasi hasilnya HARUS identik dengan
 * milik Upgrade.inc.php -- lihat perbandingan di bagian bawah),
 * memakai DAO/data-object yang SAMA persis (ArticleDAO, Author,
 * Article) yang MEMANG bagian permanen sistem, tapi TIDAK memicu alur
 * Installer/upgrade.xml yang jauh lebih berat dan berisiko di server
 * live.
 *
 * AMAN DIJALANKAN ULANG: artikel yang field competingInterest-nya
 * SUDAH terisi (baik dari migrasi sebelumnya, atau sudah diisi manual
 * lewat wizard submit baru) OTOMATIS DILEWATI, tidak pernah ditimpa.
 *
 * Alur:
 *  1) PREVIEW (?action=preview) -- HANYA membaca (SELECT), TIDAK
 *     mengubah data apa pun. Menampilkan daftar artikel yang akan
 *     terpengaruh migrasi, plus status masing-masing (akan
 *     dimigrasi / dilewati karena sudah terisi).
 *  2) MIGRATE_ONE (?action=migrate_one&articleId=N) -- migrasi SATU
 *     artikel per panggilan (dipicu berulang oleh front-end, satu per
 *     satu, bukan satu query massal) -- supaya progres terlihat jelas
 *     dan tidak ada satu request pun berisiko kena max_execution_time
 *     kalau jumlah artikel banyak.
 *
 * Setelah selesai dan diverifikasi, HAPUS file ini dari server.
 */

require('tools/bootstrap.inc.php');

import('classes.security.Validation');
if (!Validation::isLoggedIn() || !Validation::isSiteAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    die("Akses Ditolak. Harap login sebagai Site Administrator.");
}

import('classes.article.Article');
import('classes.article.Author');

/**
 * Cari SEMUA article_id yang punya minimal satu penulis dengan
 * competingInterests (field LAMA, per-penulis) terisi -- QUERY SAMA
 * PERSIS dengan Upgrade::migrateAuthorCompetingInterestsToArticle().
 * MURNI BACA, tidak mengubah apa pun.
 * @return int[]
 */
function migrateAjaxFindAffectedArticleIds(): array {
    /** @var ArticleDAO $articleDao */
    $articleDao = DAORegistry::getDAO('ArticleDAO');
    $result = $articleDao->retrieve(
        "SELECT DISTINCT a.article_id
         FROM author_settings aset
         INNER JOIN authors a ON a.author_id = aset.author_id
         WHERE aset.setting_name = 'competingInterests'
           AND aset.setting_value IS NOT NULL
           AND aset.setting_value != ''
         ORDER BY a.article_id"
    );

    $articleIds = [];
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $articleIds[] = (int) $row['article_id'];
        $result->MoveNext();
    }
    $result->Close();
    return $articleIds;
}

/**
 * Gabungkan competingInterests semua penulis satu artikel jadi satu
 * pernyataan "Nama: isi" per baris -- logika PERSIS sama dengan
 * Upgrade::migrateAuthorCompetingInterestsToArticle(). Dipisah jadi
 * function sendiri supaya bisa dipakai baik oleh preview (untuk
 * pratinjau isi) maupun migrate_one (untuk eksekusi sungguhan).
 * @param Article $article
 * @return string
 */
function migrateAjaxCombineStatements(Article $article): string {
    $locale = $article->getLocale();
    $authors = $article->getAuthors();
    $lines = [];
    if (is_array($authors)) {
        foreach ($authors as $author) {
            $ci = trim((string) $author->getCompetingInterests($locale));
            if ($ci === '') {
                continue;
            }
            $name = trim($author->getFirstName() . ' ' . $author->getLastName());
            $lines[] = ($name !== '' ? "$name: " : '') . $ci;
        }
    }
    return implode("\n", $lines);
}

if (isset($_GET['action'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        if ($_GET['action'] === 'preview') {
            $articleIds = migrateAjaxFindAffectedArticleIds();
            $items = [];
            foreach ($articleIds as $articleId) {
                $article = $articleDao->getArticle($articleId);
                if ($article === null) {
                    continue;
                }
                $locale = $article->getLocale();
                $existing = trim((string) $article->getCompetingInterest($locale));
                $willSkip = ($existing !== '');
                $preview = $willSkip ? $existing : migrateAjaxCombineStatements($article);

                $items[] = [
                    'articleId' => $articleId,
                    'title' => (string) $article->getLocalizedTitle(),
                    'willSkip' => $willSkip,
                    'skipReason' => $willSkip ? 'competingInterest level artikel sudah terisi sebelumnya' : null,
                    'preview' => mb_strimwidth($preview, 0, 200, '...'),
                ];
            }
            echo json_encode([
                'status' => 'done',
                'total' => count($items),
                'to_migrate' => count(array_filter($items, function ($i) { return !$i['willSkip']; })),
                'to_skip' => count(array_filter($items, function ($i) { return $i['willSkip']; })),
                'items' => $items,
            ]);
            exit;
        }

        if ($_GET['action'] === 'migrate_one') {
            $articleId = (int) ($_GET['articleId'] ?? 0);
            if ($articleId <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'articleId tidak valid.']);
                exit;
            }

            $article = $articleDao->getArticle($articleId);
            if ($article === null) {
                echo json_encode(['status' => 'error', 'message' => "Artikel #$articleId tidak ditemukan."]);
                exit;
            }

            $locale = $article->getLocale();
            $existing = trim((string) $article->getCompetingInterest($locale));
            if ($existing !== '') {
                echo json_encode([
                    'status' => 'skipped',
                    'articleId' => $articleId,
                    'message' => 'Dilewati -- competingInterest level artikel sudah terisi sebelumnya.',
                ]);
                exit;
            }

            $combined = migrateAjaxCombineStatements($article);
            if ($combined === '') {
                echo json_encode([
                    'status' => 'skipped',
                    'articleId' => $articleId,
                    'message' => 'Dilewati -- tidak ada competingInterests per-penulis yang bisa digabung (data kosong).',
                ]);
                exit;
            }

            $article->setCompetingInterest($combined, $locale);
            $articleDao->updateLocaleFields($article);

            echo json_encode([
                'status' => 'migrated',
                'articleId' => $articleId,
                'combined' => $combined,
            ]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali.']);
        exit;

    } catch (\Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Migrasi Competing Interest -- Alat Sekali Pakai</title>
    <style>
        body { font-family: -apple-system, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 20px; background: #0f1117; color: #e6e6e6; }
        h1 { font-size: 20px; }
        .warn { background: #3a2a12; border: 1px solid #ffa502; color: #ffd580; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        button { background: #2f6feb; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-size: 14px; margin-right: 8px; }
        button:disabled { background: #444; cursor: not-allowed; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #2a2d36; }
        th { color: #9aa4b2; }
        .skip { color: #ffa502; }
        .migrate { color: #2ed573; }
        .error { color: #ff6b81; }
        #logs { margin-top: 16px; max-height: 300px; overflow-y: auto; background: #12141c; border-radius: 6px; padding: 10px; font-size: 13px; }
        #logs div { padding: 3px 0; border-bottom: 1px solid #1e212b; }
        #progressWrap { background: #1e212b; border-radius: 6px; height: 20px; margin-top: 12px; overflow: hidden; display: none; }
        #progressBar { background: #2f6feb; height: 100%; width: 0%; text-align: center; color: #fff; font-size: 12px; line-height: 20px; transition: width 0.2s; }
    </style>
</head>
<body>
    <h1>Migrasi Competing Interest (Penulis -&gt; Artikel)</h1>
    <div class="warn">Alat sekali pakai. HAPUS dari server setelah selesai dan diverifikasi. Aman dijalankan ulang -- artikel yang sudah termigrasi otomatis dilewati.</div>

    <button id="btnPreview" onclick="doPreview()">1. Preview (baca saja, tidak mengubah apa pun)</button>
    <button id="btnRun" onclick="doRun()" disabled>2. Jalankan Migrasi</button>

    <div id="summary"></div>
    <table id="previewTable" style="display:none">
        <thead><tr><th>ID</th><th>Judul</th><th>Status</th><th>Pratinjau</th></tr></thead>
        <tbody id="previewBody"></tbody>
    </table>

    <div id="progressWrap"><div id="progressBar">0%</div></div>
    <div id="logs"></div>

    <script>
        let toMigrateIds = [];
        let currentIndex = 0;

        function doPreview() {
            document.getElementById('btnPreview').disabled = true;
            document.getElementById('summary').innerText = 'Memuat preview...';
            fetch('?action=preview').then(r => r.json()).then(data => {
                document.getElementById('btnPreview').disabled = false;
                if (data.status !== 'done') {
                    document.getElementById('summary').innerText = 'Gagal: ' + (data.message || 'unknown error');
                    return;
                }
                document.getElementById('summary').innerText =
                    `Total ${data.total} artikel terdeteksi. ${data.to_migrate} akan dimigrasi, ${data.to_skip} akan dilewati (sudah terisi).`;

                const tbody = document.getElementById('previewBody');
                tbody.innerHTML = '';
                toMigrateIds = [];
                data.items.forEach(item => {
                    const tr = document.createElement('tr');
                    const statusHtml = item.willSkip
                        ? `<span class="skip">DILEWATI</span>`
                        : `<span class="migrate">AKAN DIMIGRASI</span>`;
                    tr.innerHTML = `<td>${item.articleId}</td><td>${item.title}</td><td>${statusHtml}</td><td>${item.preview.replace(/</g,'&lt;')}</td>`;
                    tbody.appendChild(tr);
                    if (!item.willSkip) toMigrateIds.push(item.articleId);
                });
                document.getElementById('previewTable').style.display = data.total ? 'table' : 'none';
                document.getElementById('btnRun').disabled = (toMigrateIds.length === 0);
            }).catch(err => {
                document.getElementById('btnPreview').disabled = false;
                document.getElementById('summary').innerText = 'Gagal: ' + err.message;
            });
        }

        function doRun() {
            if (!confirm(`${toMigrateIds.length} artikel akan dimigrasi. Lanjutkan?`)) return;
            document.getElementById('btnRun').disabled = true;
            document.getElementById('btnPreview').disabled = true;
            document.getElementById('progressWrap').style.display = 'block';
            currentIndex = 0;
            runNext();
        }

        function runNext() {
            if (currentIndex >= toMigrateIds.length) {
                document.getElementById('progressBar').style.width = '100%';
                document.getElementById('progressBar').innerText = '100%';
                log('<strong>Selesai untuk semua artikel.</strong>');
                return;
            }
            const articleId = toMigrateIds[currentIndex];
            fetch('?action=migrate_one&articleId=' + articleId).then(r => r.json()).then(data => {
                if (data.status === 'migrated') {
                    log(`<span class="migrate">[#${data.articleId}] BERHASIL dimigrasi.</span>`);
                } else if (data.status === 'skipped') {
                    log(`<span class="skip">[#${data.articleId}] ${data.message}</span>`);
                } else {
                    log(`<span class="error">[#${articleId}] GAGAL: ${data.message}</span>`);
                }
                currentIndex++;
                const pct = Math.round(currentIndex / toMigrateIds.length * 100);
                document.getElementById('progressBar').style.width = pct + '%';
                document.getElementById('progressBar').innerText = pct + '%';
                setTimeout(runNext, 200);
            }).catch(err => {
                log(`<span class="error">[#${articleId}] GAGAL: ${err.message}</span>`);
                currentIndex++;
                setTimeout(runNext, 200);
            });
        }

        function log(html) {
            const div = document.createElement('div');
            div.innerHTML = html;
            const box = document.getElementById('logs');
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
        }
    </script>
</body>
</html>