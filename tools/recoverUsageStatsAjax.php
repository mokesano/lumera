<?php
declare(strict_types=1);

/**
 * @file tools/recoverUsageStatsAjax.php
 *
 * [ALAT SEKALI PAKAI - BUKAN BAGIAN PERMANEN SISTEM]
 * Dipasang sejajar index.php, diakses via browser oleh Site Administrator.
 * Dipakai untuk memulihkan file usage stats yang sudah terlanjur diarsipkan
 * (archive/) TANPA datanya masuk ke tabel metrics, akibat bug strict
 * in_array() pada UsageStatsLoader::processFile() yang menyaring habis
 * semua baris log (lihat UsageStatsLoader.inc.php baris ~211). Bug itu
 * harus SUDAH diperbaiki di kode sebelum alat ini dipakai.
 *
 * TIDAK ADA operasi database/file yang berjalan hanya dengan membuka
 * halaman ini. Setiap langkah (Scan, Proses) HANYA berjalan lewat klik
 * tombol, yang memicu fetch() ke ?action=...
 *
 * Alur wajib berurutan:
 *  1) SCAN     -> baca isi folder archive/ plugin usageStats, untuk tiap
 *                 file cek apakah tabel metrics sudah punya baris dengan
 *                 load_id = nama file tsb (tanpa akhiran .gz). File yang
 *                 metrics-nya 0 baris ditandai "perlu diproses ulang".
 *  2) PROSES   -> untuk tiap file yang ditandai, satu per satu (via AJAX,
 *                 supaya tidak timeout kalau filenya banyak):
 *                   a. Kalau file terkompresi (.gz), didekompres dengan
 *                      zlib PHP murni (tidak bergantung binary gzip di
 *                      server, karena banyak hosting men-nonaktifkan
 *                      exec()/shell_exec()).
 *                   b. File dipindah ke stage/.
 *                   c. UsageStatsLoader dijalankan langsung (setara
 *                      dengan scheduled task, tanpa argumen 'autoStage'
 *                      supaya HANYA memproses file yang baru kita taruh
 *                      di stage/, bukan menyapu ulang usageEventLogs/).
 *                   d. Jumlah baris metrics untuk load_id itu dicek lagi
 *                      setelah eksekusi, untuk konfirmasi berhasil/tidak.
 *
 * Setelah selesai dan diverifikasi, HAPUS file ini dari server.
 */

require('tools/bootstrap.inc.php');

import('classes.security.Validation');
if (!Validation::isLoggedIn() || !Validation::isSiteAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    die("Akses Ditolak. Harap login sebagai Site Administrator.");
}

/**
 * Siapkan instance UsageStatsLoader (tanpa argumen 'autoStage') untuk
 * dipakai membaca path stage/processing/archive dan, saat action=process,
 * untuk benar-benar menjalankan pemrosesan file.
 * @return UsageStatsLoader|null null kalau plugin usageStats tidak aktif.
 */
function recoverAjaxGetTask() {
    import('plugins.generic.usageStats.UsageStatsLoader');
    $task = new UsageStatsLoader([]);
    if (!$task->getArchivePath()) {
        return null;
    }
    return $task;
}

/**
 * Hitung berapa baris di tabel metrics untuk load_id tertentu.
 * @param string $loadId
 * @return int
 */
function recoverAjaxCountMetrics(string $loadId): int {
    /** @var MetricsDAO $metricsDao */
    $metricsDao = DAORegistry::getDAO('MetricsDAO');
    $result = $metricsDao->retrieve('SELECT COUNT(*) AS total FROM metrics WHERE load_id = ?', [$loadId]);
    $total = 0;
    if ($result && !$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $total = (int) ($row['total'] ?? 0);
    }
    if ($result) $result->Close();
    return $total;
}

/**
 * Nama file -> load_id yang dipakai di tabel metrics (selalu tanpa
 * akhiran .gz, karena FileLoader mendekompres dulu sebelum memproses,
 * dan load_id diambil dari basename file HASIL dekompresi).
 * @param string $filename
 * @return string
 */
function recoverAjaxLoadIdFromFilename(string $filename): string {
    return preg_replace('/\.gz$/', '', $filename);
}

/**
 * Intip beberapa baris pertama file log (mendukung .gz) dan cek apakah
 * domain di URL-nya cocok dengan general.base_url situs saat ini.
 * Ini HANYA petunjuk dini untuk admin -- bukan jaminan; keputusan
 * sebenarnya tetap ditentukan oleh _getAssocFromUrl() saat file betulan
 * diproses. Baris log yang domainnya beda hampir pasti akan gagal
 * diresolve ke jurnal (lihat pembuktian dengan file log 2018 sebelumnya).
 * @param string $filePath
 * @param bool $isGz
 * @return string|null host yang ditemukan di baris pertama, atau null.
 */
function recoverAjaxPeekHost(string $filePath, bool $isGz): ?string {
    $line = false;
    if ($isGz) {
        $gz = @gzopen($filePath, 'rb');
        if ($gz) {
            $line = gzgets($gz);
            gzclose($gz);
        }
    } else {
        $fh = @fopen($filePath, 'rb');
        if ($fh) {
            $line = fgets($fh);
            fclose($fh);
        }
    }
    if (!$line) return null;

    if (preg_match('/"[^"]*"\s+(\S+)\s+\S+\s+"/', $line, $m)) {
        $host = parse_url($m[1], PHP_URL_HOST);
        return $host ?: null;
    }
    return null;
}

if (isset($_GET['action'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        $task = recoverAjaxGetTask();
        if (!$task) {
            echo json_encode(['status' => 'error', 'message' => 'Plugin usageStats tidak aktif atau gagal diinisialisasi.']);
            exit;
        }

        if ($_GET['action'] === 'scan') {
            $archivePath = $task->getArchivePath();
            $files = glob($archivePath . DIRECTORY_SEPARATOR . '*');
            $files = is_array($files) ? $files : [];

            $siteHost = parse_url((string) Config::getVar('general', 'base_url'), PHP_URL_HOST);

            $affected = [];
            $ok = [];
            foreach ($files as $filePath) {
                if (!is_file($filePath)) continue;
                $filename = basename($filePath);
                $loadId = recoverAjaxLoadIdFromFilename($filename);
                $count = recoverAjaxCountMetrics($loadId);
                $entry = ['filename' => $filename, 'load_id' => $loadId, 'metrics_count' => $count, 'size' => filesize($filePath)];
                if ($count === 0) {
                    $isGz = (substr($filename, -3) === '.gz');
                    $fileHost = recoverAjaxPeekHost($filePath, $isGz);
                    $entry['detected_host'] = $fileHost;
                    $entry['domain_mismatch'] = ($fileHost !== null && $siteHost !== null && strcasecmp($fileHost, $siteHost) !== 0);
                    $affected[] = $entry;
                } else {
                    $ok[] = $entry;
                }
            }

            echo json_encode([
                'status' => 'done',
                'total_files' => count($files),
                'affected_count' => count($affected),
                'ok_count' => count($ok),
                'site_host' => $siteHost,
                'affected' => $affected,
            ]);
            exit;
        }

        if ($_GET['action'] === 'process') {
            $filename = basename((string) ($_GET['file'] ?? ''));
            if ($filename === '') {
                echo json_encode(['status' => 'error', 'message' => 'Nama file tidak dikirim.']);
                exit;
            }

            $archivePath = $task->getArchivePath();
            $stagePath = $task->getStagePath();
            $processingPath = $task->getProcessingPath();

            $sourcePath = $archivePath . DIRECTORY_SEPARATOR . $filename;
            if (!is_file($sourcePath)) {
                echo json_encode(['status' => 'error', 'message' => "File \"$filename\" tidak ditemukan di archive/."]);
                exit;
            }

            // Jangan tabrakan dengan proses lain (Acron/cron) yang mungkin
            // sedang jalan bersamaan.
            $stageBusy = array_filter((array) glob($stagePath . DIRECTORY_SEPARATOR . '*'), 'is_file');
            $processingBusy = array_filter((array) glob($processingPath . DIRECTORY_SEPARATOR . '*'), 'is_file');
            if (count($stageBusy) > 0 || count($processingBusy) > 0) {
                echo json_encode(['status' => 'busy', 'message' => 'stage/ atau processing/ sedang tidak kosong (kemungkinan ada proses lain berjalan). Coba lagi sesaat lagi.']);
                exit;
            }

            $loadId = recoverAjaxLoadIdFromFilename($filename);
            $isGz = (substr($filename, -3) === '.gz');
            $destPath = $stagePath . DIRECTORY_SEPARATOR . $loadId;

            // Baca isi file (decompress dulu kalau .gz) ke memori -- file log
            // per hari ukurannya wajar (ratusan KB), aman dibaca sekaligus.
            if ($isGz) {
                $gz = gzopen($sourcePath, 'rb');
                if (!$gz) {
                    echo json_encode(['status' => 'error', 'message' => "Gagal membuka file gzip \"$filename\"."]);
                    exit;
                }
                $content = '';
                while (!gzeof($gz)) {
                    $content .= gzread($gz, 1048576);
                }
                gzclose($gz);
            } else {
                $content = file_get_contents($sourcePath);
                if ($content === false) {
                    echo json_encode(['status' => 'error', 'message' => "Gagal membaca file \"$filename\"."]);
                    exit;
                }
            }

            // Kalau domain di file ini beda dari base_url situs sekarang
            // (log lama/legacy), tulis-ulang domainnya SEBELUM diproses --
            // supaya Core::removeBaseUrl()/_getAssocFromUrl() bisa
            // meresolve jurnal & artikelnya seperti log format baru.
            // Sudah dibuktikan lewat pengujian: baris yang tadinya gagal
            // 100% jadi berhasil 100% setelah rewrite ini, dan resolusi
            // geolocation (yang baru jalan SETELAH jurnal ter-resolve,
            // lihat UsageStatsLoader.inc.php:219-235) otomatis ikut aktif,
            // tanpa perlu logika terpisah.
            $domainRewritten = false;
            $detectedHost = parse_url((string) ($_GET['detected_host'] ?? ''), PHP_URL_HOST);
            if (!$detectedHost) {
                // Fallback: deteksi ulang dari isi file kalau front-end
                // tidak mengirimkannya (mis. dipanggil manual tanpa scan).
                if (preg_match('/"[^"]*"\s+(\S+)\s+\S+\s+"/', strtok($content, "\n"), $m)) {
                    $detectedHost = parse_url($m[1], PHP_URL_HOST);
                }
            }
            $siteBaseUrl = (string) Config::getVar('general', 'base_url');
            $siteHost = parse_url($siteBaseUrl, PHP_URL_HOST);
            if ($detectedHost && $siteHost && strcasecmp($detectedHost, $siteHost) !== 0) {
                $rewritten = preg_replace(
                    '#https?://' . preg_quote($detectedHost, '#') . '#i',
                    $siteBaseUrl,
                    $content,
                    -1,
                    $rewriteCount
                );
                if ($rewritten !== null) {
                    $content = $rewritten;
                    $domainRewritten = ($rewriteCount > 0);
                }
            }

            if (file_put_contents($destPath, $content) === false) {
                echo json_encode(['status' => 'error', 'message' => "Gagal menulis file tujuan \"$loadId\" di stage/."]);
                exit;
            }
            // Sumber di archive/ baru dihapus setelah tujuan berhasil ditulis.
            // (kalau ada sisa file .gz asli dengan nama beda, biarkan --
            // hanya hapus sumber yang barusan kita baca)
            unlink($sourcePath);

            $countBefore = recoverAjaxCountMetrics($loadId);

            import('plugins.generic.usageStats.UsageStatsLoader');
            $runTask = new UsageStatsLoader([]); // tanpa 'autoStage' -> hanya memproses isi stage/ saat ini
            $execResult = $runTask->execute();

            $countAfter = recoverAjaxCountMetrics($loadId);

            $stillInStage = is_file($stagePath . DIRECTORY_SEPARATOR . $loadId);
            $stillInProcessing = is_file($processingPath . DIRECTORY_SEPARATOR . $loadId);
            $movedToReject = false;

            // Kalau UsageStatsLoader gagal total memproses (0 baris terus,
            // biasanya karena URL di file itu tidak cocok dengan base_url
            // situs saat ini -- domain lama/berbeda), FileLoader mengembalikan
            // filenya ke stage/, BUKAN ke archive atau reject. Kalau
            // dibiarkan, file itu akan memblokir SEMUA proses file
            // berikutnya di alat ini (guard "busy" di atas selalu terpicu).
            // Pindahkan sendiri ke reject/ supaya antrean tidak macet, dan
            // supaya kegagalannya terlihat jelas untuk ditinjau manual.
            if ($stillInStage && $countAfter === 0) {
                $rejectPath = $runTask->getRejectPath();
                $runTask->moveFile($stagePath, $rejectPath, $loadId);
                $stillInStage = false;
                $movedToReject = true;
            }

            echo json_encode([
                'status' => 'done',
                'load_id' => $loadId,
                'exec_result' => (bool) $execResult,
                'metrics_before' => $countBefore,
                'metrics_after' => $countAfter,
                'domain_rewritten' => $domainRewritten,
                'detected_host' => $detectedHost,
                'moved_to_reject' => $movedToReject,
                'still_in_stage' => $stillInStage,
                'still_in_processing' => $stillInProcessing,
            ]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal.']);
        exit;

    } catch (Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => 'Fatal System Error: ' . htmlspecialchars($e->getMessage())]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lumera UsageStats Recovery</title>
    <style>
        body { font-family: 'Segoe UI', monospace; background: #1a1a1a; color: #ddd; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #2d2d2d; padding: 25px; border: 1px solid #444; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);}
        .safe-banner { background: #1e3a2e; border: 1px solid #2ed573; color: #2ed573; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; }
        .warn-banner { background: #3a2e1e; border: 1px solid #ffa502; color: #ffa502; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; }
        button { background: #ff4757; color: white; border: none; padding: 12px 24px; cursor: pointer; font-size: 16px; font-weight: bold; border-radius: 4px; transition: filter 0.3s; margin-right: 10px; margin-bottom: 10px; }
        button:hover { filter: brightness(1.15); }
        button:disabled { background: #555; cursor: not-allowed; }
        .step { border: 1px solid #444; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .step h3 { margin-top: 0; }
        .step.locked { opacity: 0.5; }
        .note { color: #ffa502; font-size: 0.9em; }
        .danger { color: #ff6b81; font-size: 0.9em; }
        .progress-container { width: 100%; background: #444; border-radius: 4px; margin-top: 10px; height: 25px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #2ed573, #7bed9f); width: 0%; transition: width 0.3s ease; text-align: center; line-height: 25px; color: #000; font-weight: bold; font-size: 14px; }
        .log-box { max-height: 300px; overflow-y: auto; background: #000; border: 1px solid #555; margin-top: 15px; padding: 10px; font-size: 13px; line-height: 1.5; border-radius: 4px; font-family: monospace; }
        .success { color: #2ed573; display: block; margin-bottom: 4px; }
        .skip { color: #747d8c; display: block; margin-bottom: 4px; }
        .error { color: #ff6b81; display: block; margin-bottom: 4px; }
        .info-text { margin-top: 10px; font-size: 1em; font-weight: bold; color: #1e90ff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #444; padding: 6px 8px; text-align: left; }
        th { background: #1a1a1a; }
    </style>
</head>
<body>

<div class="container">
    <h2>Pemulihan Data UsageStats</h2>
    <div class="safe-banner">
        Halaman ini AMAN dibuka/dimuat ulang kapan saja — tidak ada satupun query database/pemindahan file yang berjalan tanpa Anda menekan tombol secara eksplisit.
    </div>
    <div class="warn-banner">
        Prasyarat: baris <code>in_array($entryData['returnCode'], ['200', '304'], true)</code> di <code>UsageStatsLoader.inc.php</code> harus SUDAH diperbaiki sebelum memakai alat ini. Kalau belum, langkah Proses di bawah akan gagal lagi dengan cara yang sama.
    </div>
    <p class="note">Alat sekali-pakai. Jalankan Scan dulu, tinjau daftarnya, baru Proses. Setelah selesai dan diverifikasi, hapus file ini dari server.</p>

    <div class="step" id="stepScan">
        <h3>Langkah 1 — Scan archive/</h3>
        <p>Membaca semua file di folder <code>archive/</code> plugin usageStats, membandingkan tiap file dengan jumlah baris di tabel <code>metrics</code> untuk load_id yang sama.</p>
        <button onclick="runScan()" id="btnScan">JALANKAN SCAN</button>
        <div id="scanStatus" class="info-text"></div>
        <div id="scanTableWrap" style="display:none;">
            <table id="scanTable"><thead><tr><th>File</th><th>Ukuran</th><th>Baris metrics saat ini</th><th>Domain di file</th></tr></thead><tbody></tbody></table>
        </div>
    </div>

    <div class="step locked" id="stepProcess">
        <h3>Langkah 2 — Proses Ulang File yang Kosong</h3>
        <p class="danger">Setiap file diproses satu per satu: dipindah ke stage/, dijalankan lewat UsageStatsLoader, lalu diverifikasi jumlah baris metrics-nya.</p>
        <button onclick="startProcess()" id="btnStart" disabled>MULAI PROSES</button>
        <div class="progress-container"><div class="progress-bar" id="progressBar">0%</div></div>
        <div class="info-text" id="status">Status: Menunggu instruksi...</div>
        <div class="log-box" id="logs"></div>
    </div>
</div>

<script>
    let affectedFiles = [];
    let currentIndex = 0;

    function runScan() {
        document.getElementById('btnScan').disabled = true;
        document.getElementById('scanStatus').innerText = "Memindai archive/...";
        fetch('?action=scan').then(r => r.json()).then(data => {
            document.getElementById('btnScan').disabled = false;
            if (data.status !== 'done') {
                document.getElementById('scanStatus').innerText = "Gagal: " + (data.message || 'Kesalahan tidak diketahui.');
                return;
            }
            const mismatchCount = data.affected.filter(f => f.domain_mismatch).length;
            document.getElementById('scanStatus').innerText =
                `Total file di archive/: ${data.total_files}. Sudah ada data (aman): ${data.ok_count}. Perlu diproses ulang: ${data.affected_count}` +
                (mismatchCount > 0 ? `. PERINGATAN: ${mismatchCount} file domainnya beda dari situs saat ini (${data.site_host}) -- kemungkinan besar akan GAGAL diresolve.` : '.');

            affectedFiles = data.affected.map(f => ({ filename: f.filename, detectedHost: f.detected_host || '' }));
            const tbody = document.querySelector('#scanTable tbody');
            tbody.innerHTML = '';
            data.affected.forEach(f => {
                const tr = document.createElement('tr');
                const hostCell = f.domain_mismatch
                    ? `<span style="color:#ffa502">${f.detected_host || '?'} (beda!)</span>`
                    : (f.detected_host || '-');
                tr.innerHTML = `<td>${f.filename}</td><td>${(f.size/1024).toFixed(1)} KB</td><td style="color:#ff6b81">${f.metrics_count}</td><td>${hostCell}</td>`;
                tbody.appendChild(tr);
            });
            document.getElementById('scanTableWrap').style.display = data.affected.length ? 'block' : 'none';

            if (affectedFiles.length > 0) {
                document.getElementById('stepProcess').classList.remove('locked');
                document.getElementById('btnStart').disabled = false;
            }
        }).catch(err => {
            document.getElementById('btnScan').disabled = false;
            document.getElementById('scanStatus').innerText = "Gagal: " + err.message;
        });
    }

    function startProcess() {
        if (!confirm(`${affectedFiles.length} file akan diproses ulang satu per satu. Lanjutkan?`)) return;
        document.getElementById('btnStart').disabled = true;
        document.getElementById('btnStart').innerText = "SEDANG BERJALAN...";
        currentIndex = 0;
        processNext();
    }

    function processNext() {
        if (currentIndex >= affectedFiles.length) {
            document.getElementById('progressBar').style.width = "100%";
            document.getElementById('progressBar').innerText = "100%";
            document.getElementById('status').innerText = "Proses selesai untuk semua file.";
            document.getElementById('btnStart').innerText = "SELESAI";
            return;
        }

        const { filename, detectedHost } = affectedFiles[currentIndex];
        document.getElementById('status').innerText = `Memproses (${currentIndex + 1}/${affectedFiles.length}): ${filename}...`;

        fetch('?action=process&file=' + encodeURIComponent(filename) + '&detected_host=' + encodeURIComponent(detectedHost ? ('https://' + detectedHost) : ''))
            .then(r => r.json())
            .then(data => {
                if (data.status === 'busy') {
                    log(`<span class="skip">[${filename}] ${data.message} Dicoba lagi 3 detik lagi.</span>`);
                    setTimeout(processNext, 3000);
                    return;
                }
                if (data.status !== 'done') {
                    log(`<span class="error">[${filename}] GAGAL: ${data.message}</span>`);
                } else if (data.metrics_after > 0) {
                    const rewriteNote = data.domain_rewritten ? ` (domain ${data.detected_host} ditulis-ulang otomatis)` : '';
                    log(`<span class="success">[${filename}] BERHASIL${rewriteNote}. Baris metrics: ${data.metrics_before} -> ${data.metrics_after}.</span>`);
                } else if (data.moved_to_reject) {
                    log(`<span class="error">[${filename}] GAGAL PERMANEN (baris metrics tetap 0, kemungkinan URL di file ini tidak cocok dengan base_url situs sekarang / kontennya sudah tidak ada). File dipindah ke reject/ untuk ditinjau manual, antrean tetap lanjut.</span>`);
                } else {
                    log(`<span class="error">[${filename}] Selesai diproses TAPI baris metrics masih 0 (still_in_processing=${data.still_in_processing}). Perlu ditinjau manual.</span>`);
                }
                currentIndex++;
                document.getElementById('progressBar').style.width = Math.round(currentIndex / affectedFiles.length * 100) + "%";
                document.getElementById('progressBar').innerText = Math.round(currentIndex / affectedFiles.length * 100) + "%";
                setTimeout(processNext, 300);
            })
            .catch(err => {
                log(`<span class="error">[${filename}] Connection Error: ${err.message}</span>`);
                currentIndex++;
                setTimeout(processNext, 300);
            });
    }

    function log(html) {
        let div = document.createElement('div');
        div.innerHTML = html;
        let box = document.getElementById('logs');
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }
</script>
</body>
</html>