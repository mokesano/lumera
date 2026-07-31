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
 *  2) PROSES   -> untuk tiap file yang ditandai, satu per satu, dan DALAM
 *                 satu file, per-BATCH baris (bukan satu file sekaligus
 *                 dalam satu eksekusi PHP) -- supaya tidak ada satu request
 *                 pun yang berpotensi kena max_execution_time, baik karena
 *                 banyak file maupun karena satu file isinya banyak baris:
 *                   a. ?action=process_start: file didekompres (zlib PHP
 *                      murni, tidak bergantung binary gzip/exec()), domain
 *                      lama ditulis-ulang kalau perlu, lalu dipindah ke
 *                      processing/ (BUKAN stage/, supaya real cron/Acron
 *                      yang mungkin jalan bersamaan tidak ikut mengklaim
 *                      file yang sama selama proses batch berlangsung).
 *                   b. ?action=process_batch (dipanggil berulang oleh
 *                      front-end dengan parameter offset & batch_size,
 *                      dengan jeda di antaranya): setiap panggilan HANYA
 *                      memproses N baris log mulai dari offset byte
 *                      terakhir, lalu mengembalikan next_offset untuk
 *                      panggilan berikutnya, sampai file habis dibaca.
 *                   c. ?action=process_commit: setelah semua baris selesai
 *                      dibaca, baris-baris yang terkumpul di tabel sementara
 *                      dipindahkan ke tabel metrics (purge+insert, sama
 *                      seperti UsageStatsLoader::_loadData()), lalu file
 *                      dipindah ke archive/ (berhasil) atau reject/ (gagal).
 *
 * Setelah selesai dan diverifikasi, HAPUS file ini dari server.
 */

require('tools/bootstrap.inc.php');

import('classes.security.Validation');
if (!Validation::isLoggedIn() || !Validation::isSiteAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    die("Akses Ditolak. Harap login sebagai Site Administrator.");
}

import('plugins.generic.usageStats.UsageStatsLoader');

/**
 * Subclass UsageStatsLoader khusus alat ini, menambahkan kemampuan memproses
 * satu file LOG SECARA BERTAHAP (per batch baris) alih-alih sekaligus dalam
 * satu eksekusi PHP seperti UsageStatsLoader::processFile() aslinya.
 *
 * processLogBatch() adalah versi "dipotong-potong" dari logika baris-demi-
 * baris di processFile() (parsing, filter bot/return-code, resolve
 * jurnal/artikel, GeoIP, filter double-klik COUNTER, insert ke tabel
 * sementara) -- HANYA menangani sejumlah baris terbatas per panggilan, lalu
 * berhenti dan melaporkan posisi berhenti (byte offset) supaya panggilan
 * AJAX berikutnya bisa melanjutkan dari situ.
 *
 * commitLoad() adalah versi terpisah dari _loadData(): mengumpulkan semua
 * baris yang sudah masuk tabel sementara untuk satu load_id, lalu
 * purge+insert ke tabel metrics -- dipanggil SEKALI di akhir, setelah semua
 * batch baris selesai, bukan di setiap batch.
 */
class RecoverBatchUsageStatsLoader extends UsageStatsLoader {

    /**
     * Proses maksimal $batchSize baris dari $filePath mulai dari posisi byte
     * $startOffset. $dedupState adalah state filter double-klik COUNTER
     * (peta hash-entri => timestamp terakhir) yang dibawa dari panggilan
     * batch sebelumnya untuk file yang sama, supaya filter itu tetap akurat
     * lintas batas batch (bukan direset tiap panggilan AJAX).
     *
     * @param string $filePath
     * @param string $loadId
     * @param int $startOffset
     * @param int $batchSize
     * @param array $dedupState
     * @return array{done:bool,next_offset:int,lines_read:int,error:?string,dedup_state:array}
     */
    public function processLogBatch(string $filePath, string $loadId, int $startOffset, int $batchSize, array $dedupState): array {
        $fhandle = fopen($filePath, 'rb');
        if (!$fhandle) {
            return ['done' => true, 'next_offset' => $startOffset, 'lines_read' => 0, 'error' => "Gagal membuka file \"$filePath\".", 'dedup_state' => $dedupState];
        }
        if ($startOffset > 0) {
            fseek($fhandle, $startOffset);
        }

        if (!$this->_counterRobotsListFile || !file_exists($this->_counterRobotsListFile)) {
            fclose($fhandle);
            return ['done' => true, 'next_offset' => $startOffset, 'lines_read' => 0, 'error' => 'Daftar bot COUNTER tidak ditemukan atau tidak valid.', 'dedup_state' => $dedupState];
        }

        /** @var UsageStatsTemporaryRecordDAO $statsDao */
        $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
        $geoTool = $this->_geoLocationTool;

        $linesRead = 0;
        $error = null;
        $biggestTimeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER;

        while ($linesRead < $batchSize && !feof($fhandle)) {
            $rawLine = fgets($fhandle);
            if ($rawLine === false) {
                break;
            }
            $linesRead++;

            $line = trim($rawLine);
            if ($line === '' || substr($line, 0, 1) === '#') {
                continue;
            }

            $entryData = $this->_getDataFromLogEntry($line);
            if (!$this->_isLogEntryValid($entryData, $linesRead)) {
                // Sama seperti processFile() asli: satu baris tidak valid
                // membatalkan TOTAL pemrosesan file ini (bukan cuma baris
                // ini). Data metrics lama (kalau ada) tidak disentuh, karena
                // commitLoad() tidak akan pernah dipanggil untuk load_id ini.
                $error = __('plugins.generic.usageStats.invalidLogEntry', ['file' => $filePath, 'lineNumber' => $linesRead]);
                break;
            }

            if ($entryData['url'] === '*') {
                continue; // Apache internal
            }
            if (!in_array($entryData['returnCode'], ['200', '304'], true)) {
                continue; // Non-success codes
            }
            if (Core::isUserAgentBot($entryData['userAgent'], $this->_counterRobotsListFile)) {
                continue; // Bots
            }

            [$assocId, $assocType] = $this->_getAssocFromUrl($entryData['url'], $filePath, $linesRead);
            if (!$assocId || !$assocType) {
                continue;
            }

            $countryCode = null;
            $cityName = null;
            $region = null;
            if ($geoTool) {
                $geoResult = $geoTool->getGeoLocation($entryData['ip']);
                if (is_array($geoResult)) {
                    $countryCode = $geoResult[0] ?? null;
                    $cityName = $geoResult[1] ?? null;
                    $region = $geoResult[2] ?? null;
                }
            }

            $day = date('Ymd', $entryData['date']);
            $type = $this->_getFileType($assocType, $assocId);

            $entryHash = $assocType . $assocId . $entryData['ip'];
            foreach ($dedupState as $hash => $time) {
                if ($time + $biggestTimeFilter < $entryData['date']) {
                    unset($dedupState[$hash]);
                }
            }

            if (isset($dedupState[$entryHash])) {
                if ($type === STATISTICS_FILE_TYPE_PDF || $type === STATISTICS_FILE_TYPE_OTHER) {
                    $timeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER;
                } else {
                    $timeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_HTML;
                }
                $secondsBetweenRequests = $entryData['date'] - $dedupState[$entryHash];
                if ($secondsBetweenRequests < $timeFilter) {
                    $statsDao->deleteRecord($assocType, $assocId, $dedupState[$entryHash], $loadId);
                }
            }
            $dedupState[$entryHash] = $entryData['date'];

            $statsDao->insert($assocType, $assocId, $day, $entryData['date'], $countryCode, $region, $cityName, $type, $loadId);
        }

        $nextOffset = ftell($fhandle);
        $isEof = feof($fhandle);
        fclose($fhandle);

        return [
            'done' => ($error !== null) || $isEof || $linesRead === 0,
            'next_offset' => $nextOffset,
            'lines_read' => $linesRead,
            'error' => $error,
            'dedup_state' => $dedupState,
        ];
    }

    /**
     * Setara _loadData(): kumpulkan semua baris tabel sementara untuk
     * $loadId, purge baris lama di metrics untuk load_id itu (HANYA kalau
     * ada baris baru pengganti -- lihat _loadData()), lalu insert baris
     * baru. Dipanggil sekali di akhir, setelah seluruh file selesai dibaca
     * per-batch.
     * @param string $loadId
     * @return array{success:bool,error:?string}
     */
    public function commitLoad(string $loadId): array {
        $errorMsg = null;
        $result = $this->_loadData($loadId, $errorMsg);
        /** @var UsageStatsTemporaryRecordDAO $statsDao */
        $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
        $statsDao->deleteByLoadId($loadId);
        return ['success' => $result, 'error' => $errorMsg];
    }

    /**
     * Setara FileLoader::_archiveFile(), tapi tanpa bergantung pada state
     * protected ($_claimedFilename dkk) yang hanya terisi lewat alur
     * _claimNextFile() normal -- di alat ini file dipindah manual.
     * @param string $sourceDir
     * @param string $filename
     * @return bool
     */
    public function archiveProcessedFile(string $sourceDir, string $filename): bool {
        $this->moveFile($sourceDir, $this->getArchivePath(), $filename);
        if (!$this->getCompressArchives()) {
            return true;
        }
        import('lib.pkp.classes.file.FileManager');
        $fileMgr = new FileManager();
        $errorMsg = null;
        if (!$fileMgr->compressFile($this->getArchivePath() . DIRECTORY_SEPARATOR . $filename, $errorMsg)) {
            return false;
        }
        return true;
    }
}

/**
 * Siapkan instance RecoverBatchUsageStatsLoader (tanpa argumen 'autoStage')
 * untuk dipakai membaca path stage/processing/archive dan, saat
 * action=process_start/process_batch/process_commit, untuk benar-benar
 * menjalankan pemrosesan file per-batch.
 * @return RecoverBatchUsageStatsLoader|null null kalau plugin usageStats tidak aktif.
 */
function recoverAjaxGetTask() {
    $task = new RecoverBatchUsageStatsLoader([]);
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
    return recoverAjaxMetricsStats($loadId)['total'];
}

/**
 * Hitung total baris DAN berapa di antaranya yang country_id-nya kosong,
 * untuk load_id tertentu. Dipakai untuk membedakan "tidak ada data sama
 * sekali" dari "ada data tapi geolocation-nya belum lengkap" -- dua
 * kondisi yang butuh penanganan Scan/Proses berbeda.
 * @param string $loadId
 * @return array{total:int,no_geo:int}
 */
function recoverAjaxMetricsStats(string $loadId): array {
    /** @var MetricsDAO $metricsDao */
    $metricsDao = DAORegistry::getDAO('MetricsDAO');
    $result = $metricsDao->retrieve(
        "SELECT COUNT(*) AS total, SUM(CASE WHEN country_id IS NULL OR country_id = '' THEN 1 ELSE 0 END) AS no_geo
         FROM metrics WHERE load_id = ?",
        [$loadId]
    );
    $total = 0; $noGeo = 0;
    if ($result && !$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $total = (int) ($row['total'] ?? 0);
        $noGeo = (int) ($row['no_geo'] ?? 0);
    }
    if ($result) $result->Close();
    return ['total' => $total, 'no_geo' => $noGeo];
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

            $affected = [];      // count = 0: tidak ada data sama sekali
            $geoIncomplete = []; // count > 0 tapi sebagian/semua baris tanpa geolocation
            $ok = [];            // count > 0 dan geolocation lengkap
            foreach ($files as $filePath) {
                if (!is_file($filePath)) continue;
                $filename = basename($filePath);
                $loadId = recoverAjaxLoadIdFromFilename($filename);
                $stats = recoverAjaxMetricsStats($loadId);
                $count = $stats['total'];
                $entry = [
                    'filename' => $filename,
                    'load_id' => $loadId,
                    'metrics_count' => $count,
                    'no_geo_count' => $stats['no_geo'],
                    'size' => filesize($filePath),
                ];
                if ($count === 0 || $stats['no_geo'] > 0) {
                    $isGz = (substr($filename, -3) === '.gz');
                    $fileHost = recoverAjaxPeekHost($filePath, $isGz);
                    $entry['detected_host'] = $fileHost;
                    $entry['domain_mismatch'] = ($fileHost !== null && $siteHost !== null && strcasecmp($fileHost, $siteHost) !== 0);
                    if ($count === 0) {
                        $affected[] = $entry;
                    } else {
                        $geoIncomplete[] = $entry;
                    }
                } else {
                    $ok[] = $entry;
                }
            }

            echo json_encode([
                'status' => 'done',
                'total_files' => count($files),
                'affected_count' => count($affected),
                'geo_incomplete_count' => count($geoIncomplete),
                'ok_count' => count($ok),
                'site_host' => $siteHost,
                'affected' => $affected,
                'geo_incomplete' => $geoIncomplete,
            ]);
            exit;
        }

        if ($_GET['action'] === 'process_start') {
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
            // sedang jalan bersamaan. processing/ sengaja tetap diperiksa di
            // sini walau file KITA SENDIRI akan duduk di situ selama proses
            // batch berlangsung -- supaya file kedua tidak bisa dimulai
            // sampai file pertama benar-benar selesai (commit/reject).
            $stageBusy = array_filter((array) glob($stagePath . DIRECTORY_SEPARATOR . '*'), 'is_file');
            $processingBusy = array_filter((array) glob($processingPath . DIRECTORY_SEPARATOR . '*'), 'is_file');
            if (count($stageBusy) > 0 || count($processingBusy) > 0) {
                echo json_encode(['status' => 'busy', 'message' => 'stage/ atau processing/ sedang tidak kosong (kemungkinan ada proses lain berjalan). Coba lagi sesaat lagi.']);
                exit;
            }

            $loadId = recoverAjaxLoadIdFromFilename($filename);
            $isGz = (substr($filename, -3) === '.gz');
            // Ditulis ke processing/, BUKAN stage/: kalau real cron/Acron
            // menjalankan UsageStatsLoader normal di tengah-tengah proses
            // batch kita (yang kini bisa berlangsung lama, tidak lagi
            // sekejap), _claimNextFile() miliknya membaca folder stage/ --
            // dengan file kita di processing/, ia tidak akan pernah
            // menyentuhnya.
            $destPath = $processingPath . DIRECTORY_SEPARATOR . $loadId;

            // Baca isi file (decompress dulu kalau .gz) ke memori -- file log
            // per hari ukurannya wajar (ratusan KB), aman dibaca sekaligus.
            // (Baris-per-baris tetap diproses bertahap lewat action=process_batch;
            // ini cuma membaca isi file dari disk, bukan memprosesnya.)
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
                echo json_encode(['status' => 'error', 'message' => "Gagal menulis file tujuan \"$loadId\" di processing/."]);
                exit;
            }
            // Sumber di archive/ baru dihapus setelah tujuan berhasil ditulis.
            unlink($sourcePath);

            $statsBefore = recoverAjaxMetricsStats($loadId);

            // Bersihkan sisa baris tabel sementara dari percobaan sebelumnya
            // untuk load_id ini (kalau ada, mis. karena batch sebelumnya
            // gagal di tengah jalan), dan reset state filter double-klik
            // COUNTER untuk load_id ini.
            /** @var UsageStatsTemporaryRecordDAO $statsDao */
            $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
            $statsDao->deleteByLoadId($loadId);
            unset($_SESSION['recoverAjaxDedup'][$loadId]);

            // Simpan snapshot "sebelum" + info domain di sesi PHP (bukan di
            // front-end), supaya action=process_commit nanti bisa melaporkan
            // perbandingan before/after tanpa harus percaya nilai yang
            // dikirim balik oleh browser.
            $_SESSION['recoverAjaxProcess'][$loadId] = [
                'metrics_before' => $statsBefore['total'],
                'no_geo_before' => $statsBefore['no_geo'],
                'domain_rewritten' => $domainRewritten,
                'detected_host' => $detectedHost,
            ];

            echo json_encode([
                'status' => 'ready',
                'load_id' => $loadId,
                'total_bytes' => strlen($content),
            ]);
            exit;
        }

        if ($_GET['action'] === 'process_batch') {
            $loadId = basename((string) ($_GET['load_id'] ?? ''));
            $offset = (int) ($_GET['offset'] ?? 0);
            $batchSize = max(1, min(2000, (int) ($_GET['batch_size'] ?? 300)));

            if ($loadId === '') {
                echo json_encode(['status' => 'error', 'message' => 'load_id tidak dikirim.']);
                exit;
            }

            $processingPath = $task->getProcessingPath();
            $filePath = $processingPath . DIRECTORY_SEPARATOR . $loadId;
            if (!is_file($filePath)) {
                echo json_encode(['status' => 'error', 'message' => "File proses \"$loadId\" tidak ditemukan di processing/ (sesi mungkin sudah berakhir atau sudah selesai di-commit).", 'done' => true]);
                exit;
            }

            $dedupState = $_SESSION['recoverAjaxDedup'][$loadId] ?? [];
            $result = $task->processLogBatch($filePath, $loadId, $offset, $batchSize, $dedupState);
            $_SESSION['recoverAjaxDedup'][$loadId] = $result['dedup_state'];

            if ($result['error'] !== null) {
                // Sama seperti UsageStatsLoader::processFile() asli: satu
                // baris log yang tidak valid membatalkan TOTAL pemrosesan
                // file ini. Tabel metrics TIDAK disentuh (commit tidak
                // pernah dipanggil) -- data lama untuk load_id ini (kalau
                // ada) tetap utuh. File dipindah ke reject/ untuk ditinjau
                // manual, supaya antrean file berikutnya tidak macet.
                $meta = $_SESSION['recoverAjaxProcess'][$loadId] ?? ['metrics_before' => null, 'no_geo_before' => null];
                /** @var UsageStatsTemporaryRecordDAO $statsDao */
                $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
                $statsDao->deleteByLoadId($loadId);
                $task->moveFile($processingPath, $task->getRejectPath(), $loadId);
                unset($_SESSION['recoverAjaxDedup'][$loadId], $_SESSION['recoverAjaxProcess'][$loadId]);

                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error'],
                    'done' => true,
                    'moved_to_reject' => true,
                    'metrics_before' => $meta['metrics_before'],
                    'no_geo_before' => $meta['no_geo_before'],
                ]);
                exit;
            }

            echo json_encode([
                'status' => 'done',
                'next_offset' => $result['next_offset'],
                'lines_read' => $result['lines_read'],
                'done' => $result['done'],
            ]);
            exit;
        }

        if ($_GET['action'] === 'process_commit') {
            $loadId = basename((string) ($_GET['load_id'] ?? ''));
            if ($loadId === '') {
                echo json_encode(['status' => 'error', 'message' => 'load_id tidak dikirim.']);
                exit;
            }

            $meta = $_SESSION['recoverAjaxProcess'][$loadId] ?? null;
            if ($meta === null) {
                echo json_encode(['status' => 'error', 'message' => "Sesi proses untuk \"$loadId\" tidak ditemukan (mungkin sudah kedaluwarsa atau sudah pernah di-commit)."]);
                exit;
            }

            $processingPath = $task->getProcessingPath();
            $commitResult = $task->commitLoad($loadId);
            $statsAfter = recoverAjaxMetricsStats($loadId);

            $movedToReject = false;
            if ($commitResult['success'] && $statsAfter['total'] > 0) {
                $task->archiveProcessedFile($processingPath, $loadId);
            } else {
                // commitLoad() gagal (tidak ada baris terkumpul sama sekali
                // -- lihat _loadData(): kalau begitu ia TIDAK purge/insert
                // apa pun, jadi data lama untuk load_id ini tetap aman).
                $task->moveFile($processingPath, $task->getRejectPath(), $loadId);
                $movedToReject = true;
            }

            unset($_SESSION['recoverAjaxProcess'][$loadId], $_SESSION['recoverAjaxDedup'][$loadId]);

            echo json_encode([
                'status' => 'done',
                'load_id' => $loadId,
                'metrics_before' => $meta['metrics_before'],
                'metrics_after' => $statsAfter['total'],
                'no_geo_before' => $meta['no_geo_before'],
                'no_geo_after' => $statsAfter['no_geo'],
                'domain_rewritten' => $meta['domain_rewritten'],
                'detected_host' => $meta['detected_host'],
                'moved_to_reject' => $movedToReject,
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
        <p class="danger">Setiap file diproses satu per satu, dan dalam satu file baris lognya dibaca per-batch (bukan sekaligus) supaya tidak berisiko time-out, lalu diverifikasi jumlah baris metrics-nya.</p>
        <button onclick="startProcess()" id="btnStart" disabled>MULAI PROSES</button>
        <div class="progress-container"><div class="progress-bar" id="progressBar">0%</div></div>
        <div class="info-text" id="status">Status: Menunggu instruksi...</div>
        <div class="log-box" id="logs"></div>
    </div>

    <div class="step locked" id="stepGeo">
        <h3>Langkah 3 (opsional) — Lengkapi Geolocation pada Data yang Sudah Ada</h3>
        <p class="danger">BEDA dari Langkah 2: file di daftar ini SUDAH punya baris di metrics, tapi sebagian/semua tanpa country/region/city (kemungkinan diproses sebelum GeoIP aktif, atau IP-nya gagal di-lookup saat itu). Memproses ulang di sini akan MENGHAPUS baris lama file itu dan menggantinya dengan hasil baru (termasuk geolocation, kalau sekarang bisa ter-resolve). Tinjau dulu sebelum menekan tombol.</p>
        <p class="note">Opsional: batasi cakupan berdasarkan tanggal file (diambil dari nama file <code>usage_events_YYYYMMDD</code>) sebelum memproses, alih-alih sekaligus ke seluruh riwayat. Kosongkan untuk memproses semua file pada daftar.</p>
        <div style="margin-bottom:10px;">
            <label>Dari tanggal: <input type="date" id="geoFromDate"></label>
            &nbsp;&nbsp;
            <label>Sampai tanggal: <input type="date" id="geoToDate"></label>
            &nbsp;&nbsp;
            <button onclick="applyGeoDateFilter()" style="background:#3742fa;">TERAPKAN FILTER</button>
            <button onclick="resetGeoDateFilter()" style="background:#57606f;">RESET</button>
        </div>
        <div id="geoTableWrap" style="display:none;">
            <table id="geoTable"><thead><tr><th>File</th><th>Total baris</th><th>Tanpa geo</th><th>Domain di file</th></tr></thead><tbody></tbody></table>
        </div>
        <button onclick="startGeoProcess()" id="btnGeoStart" disabled>PROSES ULANG UNTUK LENGKAPI GEOLOCATION</button>
        <div class="progress-container"><div class="progress-bar" id="geoProgressBar">0%</div></div>
        <div class="info-text" id="geoStatus">Status: Menunggu instruksi...</div>
        <div class="log-box" id="geoLogs"></div>
    </div>
</div>

<script>
    let affectedFiles = [];
    let geoFiles = [];
    let geoIncompleteRaw = []; // daftar mentah hasil scan, sebelum filter tanggal diterapkan
    let currentIndex = 0;
    let geoCurrentIndex = 0;

    const BATCH_SIZE = 300;      // baris log per panggilan action=process_batch
    const BATCH_DELAY_MS = 250;  // jeda antar batch baris DALAM satu file
    const FILE_DELAY_MS = 300;   // jeda antar file (setelah satu file selesai commit/reject)

    // Proses SATU file secara bertahap: process_start -> loop process_batch
    // (dengan jeda BATCH_DELAY_MS di antaranya, sampai seluruh isi file
    // habis dibaca) -> process_commit. Prinsipnya sama seperti kode AJAX
    // anti time-out: setiap panggilan HTTP hanya menangani sebagian kecil
    // pekerjaan (di sini: N baris log), bukan satu file utuh dalam satu
    // eksekusi PHP -- supaya file besar pun tidak berisiko kena
    // max_execution_time di server.
    function processOneFileBatched(filename, detectedHost, onProgress) {
        return new Promise((resolve) => {
            const hostParam = detectedHost ? ('https://' + detectedHost) : '';
            fetch('?action=process_start&file=' + encodeURIComponent(filename) + '&detected_host=' + encodeURIComponent(hostParam))
                .then(r => r.json())
                .then(startData => {
                    if (startData.status === 'busy') {
                        resolve({ outcome: 'busy', message: startData.message });
                        return;
                    }
                    if (startData.status !== 'ready') {
                        resolve({ outcome: 'error', message: startData.message || 'Gagal memulai proses file.' });
                        return;
                    }

                    const loadId = startData.load_id;
                    const totalBytes = startData.total_bytes || 0;

                    function nextBatch(offset) {
                        fetch('?action=process_batch&load_id=' + encodeURIComponent(loadId) + '&offset=' + offset + '&batch_size=' + BATCH_SIZE)
                            .then(r => r.json())
                            .then(batchData => {
                                if (batchData.status === 'error') {
                                    // Baris log tidak valid -> sudah dipindah ke
                                    // reject/ oleh backend, tidak ada commit.
                                    resolve({
                                        outcome: 'rejected',
                                        message: batchData.message,
                                        metricsBefore: batchData.metrics_before,
                                        noGeoBefore: batchData.no_geo_before,
                                    });
                                    return;
                                }
                                if (onProgress) onProgress(batchData.next_offset, totalBytes);
                                if (!batchData.done) {
                                    setTimeout(() => nextBatch(batchData.next_offset), BATCH_DELAY_MS);
                                    return;
                                }
                                // Seluruh baris file sudah terbaca -> commit ke tabel metrics.
                                fetch('?action=process_commit&load_id=' + encodeURIComponent(loadId))
                                    .then(r => r.json())
                                    .then(commitData => {
                                        if (commitData.status !== 'done') {
                                            resolve({ outcome: 'error', message: commitData.message || 'Gagal commit ke tabel metrics.' });
                                            return;
                                        }
                                        resolve(Object.assign({ outcome: 'done' }, commitData));
                                    })
                                    .catch(err => resolve({ outcome: 'error', message: 'Gagal commit: ' + err.message }));
                            })
                            .catch(err => resolve({ outcome: 'error', message: 'Koneksi batch gagal: ' + err.message }));
                    }

                    nextBatch(0);
                })
                .catch(err => resolve({ outcome: 'error', message: 'Gagal memulai: ' + err.message }));
        });
    }

    // Ambil tanggal dari nama file pola "usage_events_YYYYMMDD(.gz)". Kalau
    // polanya tidak cocok, filter tanggal tidak diterapkan pada file itu
    // (file tetap ikut diproses supaya tidak ada yang diam-diam terlewat).
    function extractFileDate(filename) {
        const m = filename.match(/(\d{4})(\d{2})(\d{2})/);
        if (!m) return null;
        return `${m[1]}-${m[2]}-${m[3]}`;
    }

    function renderGeoTable(list) {
        geoFiles = list.map(f => ({ filename: f.filename, detectedHost: f.detected_host || '' }));
        const geoTbody = document.querySelector('#geoTable tbody');
        geoTbody.innerHTML = '';
        list.forEach(f => {
            const tr = document.createElement('tr');
            const hostCell = f.domain_mismatch
                ? `<span style="color:#ffa502">${f.detected_host || '?'} (beda!)</span>`
                : (f.detected_host || '-');
            tr.innerHTML = `<td>${f.filename}</td><td>${f.metrics_count}</td><td style="color:#ffa502">${f.no_geo_count}</td><td>${hostCell}</td>`;
            geoTbody.appendChild(tr);
        });
        document.getElementById('geoTableWrap').style.display = list.length ? 'block' : 'none';
        document.getElementById('geoStatus').innerText =
            `${list.length} dari ${geoIncompleteRaw.length} file dengan geolocation belum lengkap dipilih untuk diproses.`;
        document.getElementById('btnGeoStart').disabled = (list.length === 0);
    }

    function applyGeoDateFilter() {
        const fromVal = document.getElementById('geoFromDate').value;
        const toVal = document.getElementById('geoToDate').value;
        if (!fromVal && !toVal) {
            renderGeoTable(geoIncompleteRaw);
            return;
        }
        const filtered = geoIncompleteRaw.filter(f => {
            const fileDate = extractFileDate(f.filename);
            if (!fileDate) return true; // nama tidak cocok pola -> jangan diam-diam dilewati
            if (fromVal && fileDate < fromVal) return false;
            if (toVal && fileDate > toVal) return false;
            return true;
        });
        renderGeoTable(filtered);
    }

    function resetGeoDateFilter() {
        document.getElementById('geoFromDate').value = '';
        document.getElementById('geoToDate').value = '';
        renderGeoTable(geoIncompleteRaw);
    }

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

            // Langkah 3: file yang SUDAH punya baris tapi geolocation-nya bolong.
            // Simpan daftar mentahnya supaya filter tanggal (opsional) bisa
            // diterapkan/dilepas berkali-kali tanpa perlu Scan ulang.
            geoIncompleteRaw = data.geo_incomplete;
            renderGeoTable(geoIncompleteRaw);
            if (geoIncompleteRaw.length > 0) {
                document.getElementById('stepGeo').classList.remove('locked');
            }
        }).catch(err => {
            document.getElementById('btnScan').disabled = false;
            document.getElementById('scanStatus').innerText = "Gagal: " + err.message;
        });
    }

    function startGeoProcess() {
        if (!confirm(`${geoFiles.length} file yang SUDAH punya data akan DIHAPUS baris lamanya lalu diproses ulang untuk melengkapi geolocation. Ini menyentuh data yang sudah ada (bukan yang kosong). Lanjutkan?`)) return;
        document.getElementById('btnGeoStart').disabled = true;
        document.getElementById('btnGeoStart').innerText = "SEDANG BERJALAN...";
        geoCurrentIndex = 0;
        geoProcessNext();
    }

    function geoLog(html) {
        let div = document.createElement('div');
        div.innerHTML = html;
        let box = document.getElementById('geoLogs');
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }

    function geoProcessNext() {
        if (geoCurrentIndex >= geoFiles.length) {
            document.getElementById('geoProgressBar').style.width = "100%";
            document.getElementById('geoProgressBar').innerText = "100%";
            document.getElementById('geoStatus').innerText = "Proses pelengkapan geolocation selesai untuk semua file.";
            document.getElementById('btnGeoStart').innerText = "SELESAI";
            return;
        }

        const { filename, detectedHost } = geoFiles[geoCurrentIndex];
        document.getElementById('geoStatus').innerText = `Memproses (${geoCurrentIndex + 1}/${geoFiles.length}): ${filename}...`;

        processOneFileBatched(filename, detectedHost, (offset, total) => {
            const pct = total ? Math.round(offset / total * 100) : 0;
            document.getElementById('geoStatus').innerText = `Memproses (${geoCurrentIndex + 1}/${geoFiles.length}): ${filename} (${pct}% baris terbaca)...`;
        }).then(data => {
            if (data.outcome === 'busy') {
                geoLog(`<span class="skip">[${filename}] ${data.message} Dicoba lagi 3 detik lagi.</span>`);
                setTimeout(geoProcessNext, 3000);
                return;
            }
            if (data.outcome === 'rejected') {
                geoLog(`<span class="error">[${filename}] GAGAL PERMANEN -- ${data.message} Baris LAMA (metrics_before=${data.metricsBefore}) tidak disentuh/tidak hilang karena commit dibatalkan. File dipindah ke reject/, PERLU DITINJAU MANUAL.</span>`);
            } else if (data.outcome === 'error') {
                geoLog(`<span class="error">[${filename}] GAGAL: ${data.message}</span>`);
            } else if (data.metrics_after > 0 && data.no_geo_after < data.no_geo_before) {
                const rewriteNote = data.domain_rewritten ? ` (domain ${data.detected_host} ditulis-ulang otomatis)` : '';
                geoLog(`<span class="success">[${filename}] BERHASIL${rewriteNote}. Baris tanpa geo: ${data.no_geo_before} -> ${data.no_geo_after} (dari total ${data.metrics_after} baris).</span>`);
            } else if (data.metrics_after > 0 && data.no_geo_after >= data.no_geo_before) {
                geoLog(`<span class="skip">[${filename}] Diproses ulang, tapi geolocation TIDAK bertambah lengkap (tetap ${data.no_geo_after} dari ${data.metrics_after} baris tanpa geo) -- kemungkinan IP di file ini memang tidak ada di database GeoIP. Data tetap ada, tidak hilang.</span>`);
            } else if (data.moved_to_reject) {
                geoLog(`<span class="error">[${filename}] GAGAL PERMANEN saat diproses ulang -- baris LAMA sudah terhapus dan TIDAK ADA PENGGANTI (metrics_before=${data.metrics_before}). File dipindah ke reject/, PERLU DITINJAU MANUAL SEGERA.</span>`);
            } else {
                geoLog(`<span class="error">[${filename}] Selesai diproses TAPI baris metrics jadi 0 (metrics_before=${data.metrics_before}). Perlu ditinjau manual.</span>`);
            }
            geoCurrentIndex++;
            document.getElementById('geoProgressBar').style.width = Math.round(geoCurrentIndex / geoFiles.length * 100) + "%";
            document.getElementById('geoProgressBar').innerText = Math.round(geoCurrentIndex / geoFiles.length * 100) + "%";
            setTimeout(geoProcessNext, FILE_DELAY_MS);
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

        processOneFileBatched(filename, detectedHost, (offset, total) => {
            const pct = total ? Math.round(offset / total * 100) : 0;
            document.getElementById('status').innerText = `Memproses (${currentIndex + 1}/${affectedFiles.length}): ${filename} (${pct}% baris terbaca)...`;
        }).then(data => {
            if (data.outcome === 'busy') {
                log(`<span class="skip">[${filename}] ${data.message} Dicoba lagi 3 detik lagi.</span>`);
                setTimeout(processNext, 3000);
                return;
            }
            if (data.outcome === 'rejected') {
                log(`<span class="error">[${filename}] GAGAL PERMANEN -- ${data.message} File dipindah ke reject/, PERLU DITINJAU MANUAL.</span>`);
            } else if (data.outcome === 'error') {
                log(`<span class="error">[${filename}] GAGAL: ${data.message}</span>`);
            } else if (data.metrics_after > 0) {
                const rewriteNote = data.domain_rewritten ? ` (domain ${data.detected_host} ditulis-ulang otomatis)` : '';
                log(`<span class="success">[${filename}] BERHASIL${rewriteNote}. Baris metrics: ${data.metrics_before} -> ${data.metrics_after}.</span>`);
            } else if (data.moved_to_reject) {
                log(`<span class="error">[${filename}] GAGAL PERMANEN (baris metrics tetap 0, kemungkinan URL di file ini tidak cocok dengan base_url situs sekarang / kontennya sudah tidak ada). File dipindah ke reject/ untuk ditinjau manual, antrean tetap lanjut.</span>`);
            } else {
                log(`<span class="error">[${filename}] Selesai diproses TAPI baris metrics masih 0. Perlu ditinjau manual.</span>`);
            }
            currentIndex++;
            document.getElementById('progressBar').style.width = Math.round(currentIndex / affectedFiles.length * 100) + "%";
            document.getElementById('progressBar').innerText = Math.round(currentIndex / affectedFiles.length * 100) + "%";
            setTimeout(processNext, FILE_DELAY_MS);
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