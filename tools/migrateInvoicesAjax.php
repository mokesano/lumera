<?php
declare(strict_types=1);

/**
 * @file tools/migrateInvoicesAjax.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * [ALAT SEKALI PAKAI - BUKAN BAGIAN PERMANEN SISTEM]
 * Dipasang sejajar index.php, diakses via browser oleh Site Administrator.
 * Dipakai untuk memperbaiki instalasi yang sudah live dengan data invoices
 * yang rusak akibat bug mapLegacyFeeType() versi lama. Setelah dipakai
 * sekali dengan sukses, file ini boleh dihapus dari server.
 *
 * TIDAK ADA operasi database yang berjalan hanya dengan membuka halaman ini.
 * Setiap langkah (Preview, Wipe, Migrate) HANYA berjalan lewat klik tombol,
 * yang memicu fetch() ke ?action=...
 *
 * Alur wajib berurutan:
 *  1) PREVIEW  -> backup tabel invoices + identifikasi baris yang WAJIB
 *                 dipertahankan (legacy_source_table IS NULL, artinya
 *                 invoice ASLI dari alur Pay Now/Payment Receive, BUKAN
 *                 hasil migrasi).
 *  2) WIPE     -> TRUNCATE invoices, lalu insert-ulang HANYA baris yang
 *                 teridentifikasi di langkah Preview.
 *  3) MIGRATE  -> jalankan copyLegacyQueuedPayments()/copyLegacyCompletedPayments()
 *                 atau moveLegacyQueuedPayments()/moveLegacyCompletedPayments()
 *                 dari InvoiceLegacyMigrationDAO, sesuai mode yang dipilih.
 */

define('BATCH_SIZE', 100);

require('tools/bootstrap.inc.php');

import('classes.security.Validation');
if (!Validation::isLoggedIn() || !Validation::isSiteAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    die("Akses Ditolak. Harap login sebagai Site Administrator.");
}

import('lib.wizdam.classes.invoice.InvoiceLegacyMigrationDAO');
$migrationDao = new InvoiceLegacyMigrationDAO();

/**
 * [FIX Temuan 3] Identifikasi baris invoices yang WAJIB dipertahankan saat
 * wipe -- yaitu invoice ASLI (bukan hasil migrasi), ditandai dengan
 * legacy_source_table IS NULL. Sebelumnya fungsi ini menebak lewat
 * pencocokan (journal_id/user_id + fee_type) terhadap completed_payments,
 * yang berisiko salah menghapus invoice live asli kalau kebetulan ada
 * kombinasi yang sama. Sekarang membaca penanda migrasi langsung dari
 * kolom yang memang dibuat untuk ini -- akurat, bukan heuristik.
 * @param InvoiceLegacyMigrationDAO $migrationDao
 * @return array daftar baris invoices (assoc array) yang wajib dipertahankan
 */
function migrateAjaxFindRowsToPreserve(InvoiceLegacyMigrationDAO $migrationDao): array {
    $result = $migrationDao->retrieve('SELECT * FROM invoices WHERE legacy_source_table IS NULL');
    $toPreserve = [];
    while ($result && !$result->EOF) {
        $toPreserve[] = $result->GetRowAssoc(false);
        $result->MoveNext();
    }
    if ($result) $result->Close();
    return $toPreserve;
}

if (isset($_GET['action'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        if ($_GET['action'] === 'preview') {
            // [FIX Temuan 3] Pengecekan skema juga wajib di sini, bukan cuma
            // di 'wipe' -- preview yang membaca legacy_source_table akan
            // gagal dengan error mentah kalau kolomnya belum ada.
            $migrationDao->ensureSchemaReady();

            $migrationDao->update('DROP TABLE IF EXISTS invoices_backup_manual');
            $migrationDao->update('CREATE TABLE invoices_backup_manual AS SELECT * FROM invoices');

            $totalResult = $migrationDao->retrieve('SELECT COUNT(*) AS total FROM invoices');
            $totalRow = $totalResult->GetRowAssoc(false);
            $totalInvoices = (int) $totalRow['total'];
            $totalResult->Close();

            $toPreserve = migrateAjaxFindRowsToPreserve($migrationDao);

            echo json_encode([
                'status' => 'done',
                'total_invoices' => $totalInvoices,
                'preserve_count' => count($toPreserve),
                'will_be_replaced_count' => $totalInvoices - count($toPreserve),
                'preserve_ids' => array_map(fn($r) => (int) $r['invoice_id'], $toPreserve),
            ]);
            exit;
        }

        if ($_GET['action'] === 'wipe') {
            $migrationDao->ensureSchemaReady();

            $backupExists = false;
            try {
                $checkBackup = $migrationDao->retrieve('SELECT COUNT(*) AS total FROM invoices_backup_manual');
                if ($checkBackup) { $backupExists = true; $checkBackup->Close(); }
            } catch (\Throwable $e) {
                $backupExists = false;
            }
            if (!$backupExists) {
                echo json_encode(['status' => 'error', 'message' => 'Backup belum ada. Jalankan Preview terlebih dahulu.']);
                exit;
            }

            $toPreserve = migrateAjaxFindRowsToPreserve($migrationDao);
            $migrationDao->update('TRUNCATE TABLE invoices');

            $reinserted = 0;
            foreach ($toPreserve as $row) {
                $migrationDao->update(
                    'INSERT INTO invoices (invoice_id, journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, payment_method, legacy_source_table, legacy_source_id, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?, ?)',
                    [
                        $row['invoice_id'], $row['journal_id'], $row['user_id'], $row['submission_id'],
                        $row['invoice_number'], $row['invoice_code'], $row['fee_type'], $row['amount'],
                        $row['currency_code'], $row['status'], $row['payment_method'],
                        $row['date_billed'], $row['date_paid']
                    ]
                );
                $reinserted++;
            }

            echo json_encode(['status' => 'done', 'reinserted' => $reinserted]);
            exit;
        }

        if ($_GET['action'] === 'run') {
            $stage = $_GET['stage'] ?? '';
            $mode = $_GET['mode'] ?? '';
            $afterId = isset($_GET['after']) ? (int) $_GET['after'] : 0;

            // [PILIHAN EKSPLISIT] Tidak ada default diam-diam -- kalau mode
            // tidak dikirim/tidak dikenali, tolak, jangan tebak.
            $methodMap = [
                'copy' => ['queued' => 'copyLegacyQueuedPayments', 'completed' => 'copyLegacyCompletedPayments'],
                'move' => ['queued' => 'moveLegacyQueuedPayments', 'completed' => 'moveLegacyCompletedPayments'],
            ];

            if (!isset($methodMap[$mode][$stage])) {
                echo json_encode(['status' => 'error', 'message' => 'Kombinasi mode/stage tidak dikenal.']);
                exit;
            }

            $method = $methodMap[$mode][$stage];
            $result = $migrationDao->$method(BATCH_SIZE, $afterId);

            $logs = [];
            foreach ($result['logs'] as $log) {
                $cls = $log['type'] === 'success' ? 'success' : ($log['type'] === 'skip' ? 'skip' : 'error');
                $logs[] = "<span class='{$cls}'>" . htmlspecialchars($log['msg']) . "</span>";
            }

            echo json_encode([
                'status' => $result['is_done'] ? 'done' : 'continue',
                'next_after' => $result['next_cursor'],
                'processed' => $result['processed'],
                'logs' => $logs
            ]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal.']);
        exit;

    } catch (Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => "Fatal System Error: " . htmlspecialchars($e->getMessage())]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lumera Invoice Migrator</title>
    <style>
        body { font-family: 'Segoe UI', monospace; background: #1a1a1a; color: #ddd; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #2d2d2d; padding: 25px; border: 1px solid #444; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);}
        .safe-banner { background: #1e3a2e; border: 1px solid #2ed573; color: #2ed573; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; }
        button { background: #ff4757; color: white; border: none; padding: 12px 24px; cursor: pointer; font-size: 16px; font-weight: bold; border-radius: 4px; transition: filter 0.3s; margin-right: 10px; margin-bottom: 10px; }
        button:hover { filter: brightness(1.15); }
        button:disabled { background: #555; cursor: not-allowed; }
        .step { border: 1px solid #444; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .step h3 { margin-top: 0; }
        .step.locked { opacity: 0.5; }
        .note { color: #ffa502; font-size: 0.9em; }
        .danger { color: #ff6b81; font-size: 0.9em; }
        .mode-choice { display: flex; gap: 20px; margin: 15px 0; padding: 12px; background: #1a1a1a; border-radius: 4px; }
        .mode-choice label { display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 0.9em; }
        .mode-choice .mode-desc { display: block; color: #999; font-size: 0.85em; margin-top: 2px; }
        .progress-container { width: 100%; background: #444; border-radius: 4px; margin-top: 10px; height: 25px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #2ed573, #7bed9f); width: 0%; transition: width 0.3s ease; text-align: center; line-height: 25px; color: #000; font-weight: bold; font-size: 14px; }
        .log-box { max-height: 300px; overflow-y: auto; background: #000; border: 1px solid #555; margin-top: 15px; padding: 10px; font-size: 13px; line-height: 1.5; border-radius: 4px; font-family: monospace; }
        .success { color: #2ed573; display: block; margin-bottom: 4px; }
        .skip { color: #747d8c; display: block; margin-bottom: 4px; }
        .error { color: #ff6b81; display: block; margin-bottom: 4px; }
        .info-text { margin-top: 10px; font-size: 1em; font-weight: bold; color: #1e90ff; }
    </style>
</head>
<body>

<div class="container">
    <h2>🧾 Invoice Migrator (Web Mode)</h2>
    <div class="safe-banner">
        ✅ Halaman ini AMAN dibuka/dimuat ulang kapan saja — tidak ada satupun query database yang berjalan tanpa Anda menekan tombol secara eksplisit.
    </div>
    <p class="note">Alat sekali-pakai. Jalankan 3 langkah SECARA BERURUTAN. Setelah selesai dan diverifikasi, hapus file ini dari server.</p>

    <div class="step" id="stepPreview">
        <h3>Langkah 1 — Preview & Backup</h3>
        <p>Membuat tabel cadangan <code>invoices_backup_manual</code>, lalu mengidentifikasi baris mana yang WAJIB dipertahankan (invoice asli, ditandai <code>legacy_source_table IS NULL</code>).</p>
        <button onclick="runPreview()" id="btnPreview">JALANKAN PREVIEW</button>
        <div id="previewStatus" class="info-text"></div>
        <div class="log-box" id="previewLogs" style="display:none;"></div>
    </div>

    <div class="step locked" id="stepWipe">
        <h3>Langkah 2 — Wipe & Reinsert</h3>
        <p class="danger">DESTRUKTIF: mengosongkan seluruh tabel invoices, lalu mengembalikan hanya baris yang teridentifikasi di Langkah 1.</p>
        <button onclick="runWipe()" id="btnWipe" disabled>JALANKAN WIPE & REINSERT</button>
        <div id="wipeStatus" class="info-text"></div>
    </div>

    <div class="step locked" id="stepMigrate">
        <h3>Langkah 3 — Migrasi Data Legacy</h3>
        <p>Pilih mode migrasi TERLEBIH DAHULU sebelum menekan tombol Mulai:</p>
        <div class="mode-choice">
            <label>
                <input type="radio" name="migrateMode" value="copy" checked>
                <span>
                    <strong>Copy</strong> (disarankan)
                    <span class="mode-desc">queued_payments/completed_payments TETAP ADA. Aman diulang kapan saja.</span>
                </span>
            </label>
            <label>
                <input type="radio" name="migrateMode" value="move">
                <span>
                    <strong>Move</strong>
                    <span class="mode-desc">Baris sumber DIHAPUS setelah dipindah. Wajib mode maintenance, tidak bisa dibatalkan.</span>
                </span>
            </label>
        </div>
        <button onclick="startMigration()" id="btnStart" disabled>MULAI MIGRASI</button>
        <div class="progress-container"><div class="progress-bar" id="progressBar">0%</div></div>
        <div class="info-text" id="status">Status: Menunggu instruksi...</div>
        <div class="log-box" id="logs"></div>
    </div>
</div>

<script>
    function runPreview() {
        document.getElementById('btnPreview').disabled = true;
        document.getElementById('previewStatus').innerText = "Membuat backup & menganalisis...";
        fetch('?action=preview').then(r => r.json()).then(data => {
            if (data.status !== 'done') {
                document.getElementById('previewStatus').innerText = "Gagal: " + (data.message || 'Kesalahan tidak diketahui.');
                document.getElementById('btnPreview').disabled = false;
                return;
            }
            document.getElementById('previewStatus').innerText =
                `Selesai. Total invoice saat ini: ${data.total_invoices}. Wajib dipertahankan: ${data.preserve_count}. Akan digantikan hasil migrasi ulang: ${data.will_be_replaced_count}.`;
            const logBox = document.getElementById('previewLogs');
            logBox.style.display = 'block';
            logBox.innerHTML = '<strong>ID invoice yang akan dipertahankan:</strong><br>' + (data.preserve_ids.length ? data.preserve_ids.join(', ') : '(tidak ada)');
            document.getElementById('btnPreview').innerText = "PREVIEW SELESAI";
            document.getElementById('stepWipe').classList.remove('locked');
            document.getElementById('btnWipe').disabled = false;
        }).catch(err => {
            document.getElementById('previewStatus').innerText = "Gagal: " + err.message;
            document.getElementById('btnPreview').disabled = false;
        });
    }

    function runWipe() {
        if (!confirm('Tabel invoices akan DIKOSONGKAN, hanya baris yang teridentifikasi di Preview yang akan dikembalikan. Lanjutkan?')) return;
        document.getElementById('btnWipe').disabled = true;
        document.getElementById('wipeStatus').innerText = "Memproses...";
        fetch('?action=wipe').then(r => r.json()).then(data => {
            if (data.status !== 'done') {
                document.getElementById('wipeStatus').innerText = "Gagal: " + (data.message || 'Kesalahan tidak diketahui.');
                document.getElementById('btnWipe').disabled = false;
                return;
            }
            document.getElementById('wipeStatus').innerText = `Selesai. ${data.reinserted} baris dikembalikan.`;
            document.getElementById('btnWipe').innerText = "WIPE SELESAI";
            document.getElementById('stepMigrate').classList.remove('locked');
            document.getElementById('btnStart').disabled = false;
        }).catch(err => {
            document.getElementById('wipeStatus').innerText = "Gagal: " + err.message;
            document.getElementById('btnWipe').disabled = false;
        });
    }

    function startMigration() {
        const mode = document.querySelector('input[name="migrateMode"]:checked').value;

        if (mode === 'move') {
            if (!confirm('Anda memilih MODE MOVE. Baris di queued_payments/completed_payments akan DIHAPUS setelah dipindah dan TIDAK BISA dibatalkan. Pastikan situs dalam mode maintenance. Lanjutkan?')) {
                return;
            }
        }

        document.querySelectorAll('input[name="migrateMode"]').forEach(el => el.disabled = true);
        document.getElementById('btnStart').disabled = true;
        document.getElementById('btnStart').innerText = "SEDANG BERJALAN (" + mode.toUpperCase() + ")...";
        log(`<strong>=== MODE: ${mode.toUpperCase()} | TAHAP 1: Queued Payments ===</strong>`);
        runStage(mode, 'queued', 0);
    }

    function runStage(mode, stage, afterId) {
        document.getElementById('status').innerText = `[${mode.toUpperCase()}] Memproses tahap "${stage}" setelah ID ${afterId}...`;
        fetch(`?action=run&mode=${mode}&stage=${stage}&after=${afterId}`)
            .then(res => res.text())
            .then(text => { try { return JSON.parse(text); } catch (e) { throw new Error("Respon Server Gagal: \n" + text.substring(0, 300)); } })
            .then(data => {
                if (data.status === 'error') {
                    log("<strong>FATAL:</strong> " + data.message, 'error');
                    resetStartButton();
                    return;
                }
                if (data.logs && data.logs.length > 0) data.logs.forEach(l => log(l));

                if (data.status === 'continue') {
                    setTimeout(() => runStage(mode, stage, data.next_after), 300);
                } else if (stage === 'queued') {
                    document.getElementById('progressBar').style.width = "50%";
                    document.getElementById('progressBar').innerText = "50%";
                    log(`<strong>=== TAHAP 1 SELESAI. Lanjut TAHAP 2: Completed Payments (${mode.toUpperCase()}) ===</strong>`);
                    runStage(mode, 'completed', 0);
                } else {
                    document.getElementById('progressBar').style.width = "100%";
                    document.getElementById('progressBar').innerText = "100%";
                    document.getElementById('status').innerText = "🔥 MIGRASI SELESAI TOTAL.";
                    document.getElementById('btnStart').innerText = "SELESAI";
                    log(`<strong style='color:#fff'>✅ Semua data legacy berhasil diproses (mode: ${mode}).</strong>`);
                }
            })
            .catch(err => {
                log("<strong>Connection Error:</strong> " + err.message, 'error');
                resetStartButton();
            });
    }

    function resetStartButton() {
        document.getElementById('btnStart').disabled = false;
        document.getElementById('btnStart').innerText = "COBA LAGI";
        document.querySelectorAll('input[name="migrateMode"]').forEach(el => el.disabled = false);
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