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

        // Kontrol batch (baca maksimal $batchSize baris, catat posisi
        // berhenti) HANYA ada di sini karena memang hanya AJAX yang butuh
        // ini -- UsageStatsLoader.inc.php TIDAK disentuh sama sekali untuk
        // kebutuhan ini. Setiap pemanggilan fungsi di bawah (_getDataFromLogEntry,
        // _isLogEntryValid, _getAssocFromUrl, _getFileType, Core::isUserAgentBot,
        // $statsDao->insert/deleteRecord) memanggil LANGSUNG fungsi yang
        // sudah ada di sistem, bukan menyalin isinya.
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
 * Path folder penanda "sudah dicoba dilengkapi geolocation, tidak
 * bertambah lengkap" -- sibling dari stage/processing/archive/reject.
 * Dipakai supaya file yang sudah terbukti tidak bisa dilengkapi (IP-nya
 * memang tidak ada di database GeoIP) tidak terus-menerus ditawarkan lagi
 * di setiap Scan. HANYA berlaku untuk hasil Langkah 3 (file yang SUDAH
 * punya data sebelum diproses ulang) -- lihat pemakaiannya di
 * action=process_commit.
 * @param UsageStatsLoader $task
 * @return string
 */
function recoverAjaxGeoMarkerDir($task): string {
    return dirname($task->getArchivePath()) . DIRECTORY_SEPARATOR . 'geoNoImprovement';
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

            $geoMarkerDir = recoverAjaxGeoMarkerDir($task);

            $affected = [];      // count = 0: tidak ada data sama sekali
            $geoIncomplete = []; // count > 0 tapi sebagian/semua baris tanpa geolocation, BELUM pernah dicoba dilengkapi (atau belum terbukti gagal)
            $geoSkipped = [];    // count > 0, tanpa geo, TAPI sudah pernah dicoba di Langkah 3 dan TIDAK bertambah lengkap -- disembunyikan dari daftar proses
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
                    } elseif (is_file($geoMarkerDir . DIRECTORY_SEPARATOR . $loadId . '.marker')) {
                        $geoSkipped[] = $entry;
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
                'geo_skipped_count' => count($geoSkipped),
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
                // strtok() mengembalikan false (bukan string) kalau $content
                // kosong (file 0 byte) -- PHP 8.1+ melempar TypeError kalau
                // itu dioper langsung ke preg_match() sebagai $subject.
                $firstLine = strtok($content, "\n");
                if ($firstLine !== false && preg_match('/"[^"]*"\s+(\S+)\s+\S+\s+"/', $firstLine, $m)) {
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

                // Kalau ini pemrosesan ulang file yang SUDAH punya data
                // sebelum diproses (metrics_before > 0 -- ciri khas
                // Langkah 3, bukan Langkah 2 yang selalu mulai dari 0) DAN
                // geolocation-nya sudah bolong sebelumnya TAPI TIDAK
                // bertambah lengkap sama sekali, simpan penanda supaya
                // Scan berikutnya tidak terus menawarkan file ini lagi --
                // IP-nya kemungkinan besar memang tidak ada di database
                // GeoIP, mengulang tidak akan mengubah hasil.
                $markerDir = recoverAjaxGeoMarkerDir($task);
                $markerPath = $markerDir . DIRECTORY_SEPARATOR . $loadId . '.marker';
                $noGeoImproved = ($meta['metrics_before'] > 0 && $meta['no_geo_before'] > 0 && $statsAfter['no_geo'] >= $meta['no_geo_before']);
                if ($noGeoImproved) {
                    if (!is_dir($markerDir)) {
                        @mkdir($markerDir, 0755, true);
                    }
                    @file_put_contents($markerPath, date('Y-m-d H:i:s') . " metrics={$statsAfter['total']} no_geo={$statsAfter['no_geo']}\n");
                } elseif (is_file($markerPath)) {
                    // Geolocation-nya sekarang membaik (mis. database GeoIP
                    // sudah diperbarui) -- lepas penanda lama supaya file
                    // ini tidak lagi disembunyikan.
                    @unlink($markerPath);
                }
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
        button:hover:not(:disabled) { filter: brightness(1.15); }
        button:disabled { background: #555; cursor: not-allowed; }
        .note { color: #ffa502; font-size: 0.9em; }

        /* Kartu pemilih langkah (gaya tab) -- klik kartu untuk menyorot
           langkah itu, bukan tabel/log terpisah per langkah. */
        .step-cards { display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
        .step-card { flex: 1 1 200px; border: 1px solid #444; border-radius: 6px; padding: 12px 15px; cursor: pointer; background: #262626; transition: border-color 0.15s, background 0.15s; position: relative; }
        .step-card:hover:not(.locked) { border-color: #666; background: #2f2f2f; }
        .step-card.active { border-color: #3742fa; background: #262c4d; }
        .step-card.locked { opacity: 0.45; cursor: not-allowed; }
        .step-card h3 { margin: 0 0 4px 0; font-size: 0.95em; }
        .step-card p { margin: 0; font-size: 0.8em; color: #9aa0a6; }
        .step-card-badge { position: absolute; top: 10px; right: 12px; background: #3742fa; color: #fff; font-size: 0.75em; font-weight: bold; padding: 2px 7px; border-radius: 10px; }
        .step-card-badge.empty { display: none; }

        /* Panel bersama: hanya satu section (sesuai kartu aktif) yang
           tampil, dan tabel (scanTableWrap/geoTableWrap) di bawahnya juga
           saling bertukar -- bukan tiga blok bertumpuk. */
        .step-panel { border: 1px solid #444; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .step-panel-section { display: none; }
        .step-panel-section.active { display: block; }
        .step-panel-section h3 { margin-top: 0; }
        .shared-table { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #444; }
        /* Tombol aksi diletakkan sejajar judul, di atas -- langsung
           terlihat tanpa perlu membaca teks deskripsi dulu. */
        .panel-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 8px; }
        .panel-head h3 { margin: 0; }
        .panel-head button { margin: 0; }
        .danger { color: #ff6b81; font-size: 0.9em; }
        .progress-container { width: 100%; background: #444; border-radius: 4px; margin-top: 10px; height: 22px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #2ed573, #7bed9f); width: 0%; transition: width 0.3s ease; text-align: center; line-height: 20px; color: #000; font-weight: bold; font-size: 12px; }
        .info-text { margin-top: 10px; font-size: 0.95em; font-weight: bold; color: #1e90ff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #444; padding: 6px 8px; text-align: left; }
        th { background: #1a1a1a; }

        /* Toolbar tabel: pilihan jumlah baris per halaman + paginasi */
        .table-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 10px; font-size: 0.85em; color: #ccc; }
        .table-toolbar select { background: #1a1a1a; color: #ddd; border: 1px solid #555; border-radius: 4px; padding: 3px 6px; }
        .pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; margin-top: 8px; font-size: 0.85em; }
        .pag-info { color: #9aa0a6; margin-right: 8px; }
        .pag-btn { background: #3a3a3a; color: #ddd; border: 1px solid #555; padding: 4px 9px; border-radius: 4px; cursor: pointer; font-size: 1em; font-weight: normal; margin: 0; }
        .pag-btn:hover:not(:disabled) { background: #4a4a4a; filter: none; }
        .pag-btn.active { background: #3742fa; border-color: #3742fa; color: #fff; }
        .pag-btn:disabled { opacity: 0.4; cursor: not-allowed; background: #3a3a3a; }

        /* Spinner kecil di dalam tombol aksi */
        .btn-spinner { display: none; width: 14px; height: 14px; border: 6px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: proc-spin 0.6s linear infinite; margin-left: 8px; vertical-align: middle; }
        @keyframes proc-spin { to { transform: rotate(360deg); } }

        /* Ikon "sedang berjalan" di baris file dalam jendela pop-up: cincin
           berputar mengelilingi titik yang berdenyut (pulse) di tengah,
           warna kuning/amber khas status "in progress" GitHub Actions. */
        /* Titik inti DIAM (bukan pulse) -- hanya cincin di sekelilingnya
           yang berputar, persis ikon "in progress" GitHub Actions. */
        .spinner-combo { position: relative; display: inline-block; width: 20px; height: 20px; }
        .spinner-combo .ring { position: absolute; inset: 0; border: 3px solid rgba(227, 179, 65, 0.25); border-top-color: #e3b341; border-radius: 50%; animation: proc-spin 0.8s linear infinite; will-change: transform; }
        .spinner-combo .dot { position: absolute; top: 50%; left: 50%; width: 9px; height: 9px; margin: -4.5px 0 0 -4.5px; background: #e3b341; border-radius: 50%; }

        /* Jendela pop-up (modal) pemrosesan, gaya mirip ringkasan run GitHub Actions */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #242424; border: 1px solid #444; border-radius: 8px; width: min(720px, 100%); max-height: 88vh; display: flex; flex-direction: column; box-shadow: 0 12px 45px rgba(0,0,0,0.6); }
        .modal-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid #444; flex: 0 0 auto; }
        .modal-head h3 { margin: 0; font-size: 1.05em; }
        .modal-close-btn { background: none; border: none; color: #ccc; font-size: 22px; cursor: pointer; padding: 0 4px; margin: 0; line-height: 1; }
        .modal-close-btn:hover:not(:disabled) { color: #fff; filter: none; }
        .modal-close-btn:disabled { background: none; color: #555; cursor: not-allowed; }
        .modal-progress-wrap { padding: 14px 20px 0 20px; flex: 0 0 auto; }
        .modal-status-text { margin-top: 6px; font-size: 0.82em; color: #9aa0a6; }
        .modal-file-list { overflow-y: auto; padding: 8px 20px; flex: 1 1 auto; }
        .modal-summary { padding: 14px 20px; border-top: 1px solid #444; font-weight: bold; flex: 0 0 auto; }
        .proc-row { display: flex; align-items: center; gap: 10px; padding: 6px 4px; border-bottom: 1px solid #383838; font-size: 13px; }
        .proc-row:last-child { border-bottom: none; }
        .proc-icon { flex: 0 0 18px; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .proc-filename { flex: 0 0 auto; min-width: 210px; font-family: monospace; word-break: break-all; }
        .proc-message { flex: 1 1 auto; color: #9aa0a6; }
        .proc-row.status-queued .proc-icon { color: #666; }
        .proc-row.status-running .proc-message { color: #1e90ff; }
        .proc-row.status-success .proc-icon { color: #2ed573; font-weight: bold; border: 1px solid #2ed573; border-radius: 50%;}
        .proc-row.status-success .proc-message { color: #2ed573; }
        .proc-row.status-warn .proc-icon { color: #ffa502; }
        .proc-row.status-warn .proc-message { color: #ffa502; }
        .proc-row.status-error .proc-icon { color: #ff6b81; font-weight: bold; }
        .proc-row.status-error .proc-message { color: #ff6b81; }
    </style>
</head>
<body>

<div class="container">
    <h2>Pemulihan Data UsageStats ke Tabel Metrics</h2>
    <div class="safe-banner">
        Halaman ini AMAN dibuka/dimuat ulang kapan saja — tidak ada satupun query database/pemindahan file yang berjalan tanpa Anda menekan tombol secara eksplisit.
    </div>
    <div class="warn-banner">
        [NOTE] Alat sekali-pakai. Jalankan Scan dulu, tinjau daftarnya, baru Proses. Setelah selesai dan diverifikasi, hapus file ini dari server.
    </div>

    <div class="step-cards">
        <div class="step-card active" id="cardScan" onclick="selectStepCard('scan')">
            <span class="step-card-badge empty" id="badgeScan"></span>
            <h3>Langkah 1</h3>
            <p>Scan archive/</p>
        </div>
        <div class="step-card locked" id="cardProcess" onclick="selectStepCard('process')">
            <span class="step-card-badge empty" id="badgeProcess"></span>
            <h3>Langkah 2</h3>
            <p>Proses File Kosong</p>
        </div>
        <div class="step-card locked" id="cardGeo" onclick="selectStepCard('geo')">
            <span class="step-card-badge empty" id="badgeGeo"></span>
            <h3>Langkah 3</h3>
            <p>Lengkapi Geolocation</p>
        </div>
    </div>

    <div class="step-panel">
        <div class="step-panel-section active" id="panelScan">
            <div class="panel-head">
                <h3>Langkah 1 — Scan archive/</h3>
                <button onclick="runScan()" id="btnScan"><span class="btn-label">JALANKAN SCAN</span><span class="btn-spinner"></span></button>
            </div>
            <p>Membaca semua file di folder <code>archive/</code> plugin usageStats, membandingkan tiap file dengan jumlah baris di tabel <code>metrics</code> untuk load_id yang sama.</p>
            <div id="scanStatus" class="info-text"></div>
        </div>

        <div class="step-panel-section" id="panelProcess">
            <div class="panel-head">
                <h3>Langkah 2 — Proses File Kosong</h3>
                <button onclick="startProcess()" id="btnStart" disabled><span class="btn-label">MULAI</span><span class="btn-spinner"></span></button>
            </div>
            <p class="danger">Setiap file diproses satu per satu, dan dalam satu file baris lognya dibaca per-batch (bukan sekaligus) supaya tidak berisiko time-out, lalu diverifikasi jumlah baris metrics-nya. Detail proses ditampilkan di jendela pop-up. Daftar file di bawah sama dengan hasil Scan Langkah 1.</p>
            <div class="info-text" id="status">Status: Menunggu instruksi...</div>
        </div>

        <div class="step-panel-section" id="panelGeo">
            <div class="panel-head">
                <h3>Langkah 3 (opsional) — Lengkapi Geolocation</h3>
                <button onclick="startGeoProcess()" id="btnGeoStart" disabled><span class="btn-label">MULAI</span><span class="btn-spinner"></span></button>
            </div>
            <p class="danger">BEDA dari Langkah 2: file di daftar ini SUDAH punya baris di metrics, tapi sebagian/semua tanpa country/region/city (kemungkinan diproses sebelum GeoIP aktif, atau IP-nya gagal di-lookup saat itu). Memproses ulang di sini akan MENGHAPUS baris lama file itu dan menggantinya dengan hasil baru (termasuk geolocation, kalau sekarang bisa ter-resolve). Tinjau dulu sebelum menekan MULAI.</p>
            <p class="note">Opsional: batasi cakupan berdasarkan tanggal file (diambil dari nama file <code>usage_events_YYYYMMDD</code>) sebelum memproses, alih-alih sekaligus ke seluruh riwayat. Kosongkan untuk memproses semua file pada daftar.</p>
            <div style="margin-bottom:10px;">
                <label>Dari tanggal: <input type="date" id="geoFromDate"></label>
                &nbsp;&nbsp;
                <label>Sampai tanggal: <input type="date" id="geoToDate"></label>
                &nbsp;&nbsp;
                <button onclick="applyGeoDateFilter()" style="background:#3742fa;">TERAPKAN FILTER</button>
                <button onclick="resetGeoDateFilter()" style="background:#57606f;">RESET</button>
            </div>
            <div class="info-text" id="geoStatus">Status: Menunggu instruksi...</div>
        </div>

        <!-- Area tabel BERSAMA -- dipakai Langkah 1 & 2 (daftar file kosong)
             atau Langkah 3 (daftar file geo belum lengkap), tergantung card
             mana yang sedang disorot. Menghindari 3 tabel bertumpuk yang
             harus di-scroll satu per satu. -->
        <div id="scanTableWrap" class="shared-table" style="display:none;">
            <div class="table-toolbar">
                <label>Tampilkan
                    <select id="scanPageSize">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100" selected>100</option>
                        <option value="200">200</option>
                    </select>
                    baris per halaman
                </label>
                <div class="pagination" id="scanPaginationTop"></div>
            </div>
            <table id="scanTable"><thead><tr><th>File</th><th>Ukuran</th><th>Baris metrics saat ini</th><th>Domain di file</th></tr></thead><tbody></tbody></table>
            <div class="pagination" id="scanPaginationBottom"></div>
        </div>
        <div id="geoTableWrap" class="shared-table" style="display:none;">
            <div class="table-toolbar">
                <label>Tampilkan
                    <select id="geoPageSize">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100" selected>100</option>
                        <option value="200">200</option>
                    </select>
                    baris per halaman
                </label>
                <div class="pagination" id="geoPaginationTop"></div>
            </div>
            <table id="geoTable"><thead><tr><th>File</th><th>Total baris</th><th>Tanpa geo</th><th>Domain di file</th></tr></thead><tbody></tbody></table>
            <div class="pagination" id="geoPaginationBottom"></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="procModalOverlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="procModalTitle">Memproses...</h3>
            <button class="modal-close-btn" id="procModalCloseBtn" onclick="closeProcModal()" disabled title="Selesaikan proses dulu">&times;</button>
        </div>
        <div class="modal-progress-wrap">
            <div class="progress-container"><div class="progress-bar" id="procProgressBar">0%</div></div>
            <div class="modal-status-text" id="procStatusText">Menyiapkan...</div>
        </div>
        <div class="modal-file-list" id="procFileList"></div>
        <div class="modal-summary" id="procSummaryBox" style="display:none;"></div>
    </div>
</div>

<script>
    let affectedFiles = [];
    let geoFiles = [];
    let geoIncompleteRaw = []; // daftar mentah hasil scan, sebelum filter tanggal diterapkan
    let geoSkippedCount = 0;   // file yang sudah pernah dicoba geo-lengkapi & tidak bertambah lengkap, disembunyikan dari daftar
    let currentIndex = 0;
    let geoCurrentIndex = 0;

    const BATCH_SIZE = 300;      // baris log per panggilan action=process_batch
    const BATCH_DELAY_MS = 250;  // jeda antar batch baris DALAM satu file
    const FILE_DELAY_MS = 300;   // jeda antar file (setelah satu file selesai commit/reject)

    // ------------------------------------------------------------------
    // Paginasi tabel (Scan / Langkah 3) -- generik, dipakai untuk kedua
    // tabel supaya halaman tidak jadi satu scroll panjang tanpa akhir.
    // ------------------------------------------------------------------
    function makeTablePaginator({ tbodySelector, pageSizeSelectId, paginationTopId, paginationBottomId, onDataChange, rowRenderer }) {
        let allData = [];
        let pageSize = parseInt(document.getElementById(pageSizeSelectId).value, 10) || 100;
        let currentPage = 1;

        function totalPages() { return Math.max(1, Math.ceil(allData.length / pageSize)); }

        function renderPagButtons() {
            const tp = totalPages();
            let html = `<span class="pag-info">${allData.length} baris — halaman ${currentPage}/${tp}</span>`;
            html += `<button class="pag-btn" data-pag="first" ${currentPage <= 1 ? 'disabled' : ''}>&laquo;</button>`;
            html += `<button class="pag-btn" data-pag="prev" ${currentPage <= 1 ? 'disabled' : ''}>&lsaquo;</button>`;
            const windowSize = 5;
            let startP = Math.max(1, currentPage - Math.floor(windowSize / 2));
            let endP = Math.min(tp, startP + windowSize - 1);
            startP = Math.max(1, endP - windowSize + 1);
            for (let p = startP; p <= endP; p++) {
                html += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" data-pag="${p}">${p}</button>`;
            }
            html += `<button class="pag-btn" data-pag="next" ${currentPage >= tp ? 'disabled' : ''}>&rsaquo;</button>`;
            html += `<button class="pag-btn" data-pag="last" ${currentPage >= tp ? 'disabled' : ''}>&raquo;</button>`;
            return html;
        }

        function renderPage() {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = '';
            const start = (currentPage - 1) * pageSize;
            allData.slice(start, start + pageSize).forEach(item => tbody.appendChild(rowRenderer(item)));
            const pagHtml = renderPagButtons();
            [paginationTopId, paginationBottomId].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = pagHtml;
            });
            if (onDataChange) onDataChange(allData.length);
        }

        function handlePagClick(e) {
            const btn = e.target.closest('.pag-btn');
            if (!btn || btn.disabled) return;
            const val = btn.getAttribute('data-pag');
            const tp = totalPages();
            if (val === 'first') currentPage = 1;
            else if (val === 'prev') currentPage = Math.max(1, currentPage - 1);
            else if (val === 'next') currentPage = Math.min(tp, currentPage + 1);
            else if (val === 'last') currentPage = tp;
            else currentPage = parseInt(val, 10);
            renderPage();
        }

        [paginationTopId, paginationBottomId].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', handlePagClick);
        });
        document.getElementById(pageSizeSelectId).addEventListener('change', function () {
            pageSize = parseInt(this.value, 10) || 100;
            currentPage = 1;
            renderPage();
        });

        return {
            setData(data) { allData = data; currentPage = 1; renderPage(); },
        };
    }

    function scanRowRenderer(f) {
        const tr = document.createElement('tr');
        const hostCell = f.domain_mismatch
            ? `<span style="color:#ffa502">${f.detected_host || '?'} (beda!)</span>`
            : (f.detected_host || '-');
        tr.innerHTML = `<td>${f.filename}</td><td>${(f.size / 1024).toFixed(1)} KB</td><td style="color:#ff6b81">${f.metrics_count}</td><td>${hostCell}</td>`;
        return tr;
    }

    function geoRowRenderer(f) {
        const tr = document.createElement('tr');
        const hostCell = f.domain_mismatch
            ? `<span style="color:#ffa502">${f.detected_host || '?'} (beda!)</span>`
            : (f.detected_host || '-');
        tr.innerHTML = `<td>${f.filename}</td><td>${f.metrics_count}</td><td style="color:#ffa502">${f.no_geo_count}</td><td>${hostCell}</td>`;
        return tr;
    }

    const scanPaginator = makeTablePaginator({
        tbodySelector: '#scanTable tbody', pageSizeSelectId: 'scanPageSize',
        paginationTopId: 'scanPaginationTop', paginationBottomId: 'scanPaginationBottom',
        rowRenderer: scanRowRenderer,
        onDataChange: (len) => { scanDataLen = len; refreshTableVisibility(); },
    });
    const geoPaginator = makeTablePaginator({
        tbodySelector: '#geoTable tbody', pageSizeSelectId: 'geoPageSize',
        paginationTopId: 'geoPaginationTop', paginationBottomId: 'geoPaginationBottom',
        rowRenderer: geoRowRenderer,
        onDataChange: (len) => { geoDataLenActive = len; refreshTableVisibility(); },
    });

    // ------------------------------------------------------------------
    // Kartu langkah (tab) + panel & tabel bersama: hanya SATU section dan
    // SATU tabel yang tampil sesuai kartu yang disorot, bukan tiga blok
    // bertumpuk yang harus di-scroll.
    // ------------------------------------------------------------------
    let activeStepCard = 'scan';
    let scanDataLen = 0;
    let geoDataLenActive = 0;

    function selectStepCard(step) {
        const card = document.getElementById('card' + step.charAt(0).toUpperCase() + step.slice(1));
        if (card.classList.contains('locked')) return;
        activeStepCard = step;
        ['scan', 'process', 'geo'].forEach(s => {
            const capitalized = s.charAt(0).toUpperCase() + s.slice(1);
            document.getElementById('card' + capitalized).classList.toggle('active', s === step);
            document.getElementById('panel' + capitalized).classList.toggle('active', s === step);
        });
        refreshTableVisibility();
    }

    function refreshTableVisibility() {
        const showScan = (activeStepCard === 'scan' || activeStepCard === 'process') && scanDataLen > 0;
        const showGeo = (activeStepCard === 'geo') && geoDataLenActive > 0;
        document.getElementById('scanTableWrap').style.display = showScan ? 'block' : 'none';
        document.getElementById('geoTableWrap').style.display = showGeo ? 'block' : 'none';
    }

    function unlockStepCard(step) {
        document.getElementById('card' + step.charAt(0).toUpperCase() + step.slice(1)).classList.remove('locked');
    }

    function setCardBadge(step, count) {
        const badge = document.getElementById('badge' + step.charAt(0).toUpperCase() + step.slice(1));
        badge.textContent = count + ' file';
        badge.classList.toggle('empty', count <= 0);
    }

    // ------------------------------------------------------------------
    // Tombol dengan indikator loading (spinner di dalam tombol)
    // ------------------------------------------------------------------
    function setBtnBusy(btnId, busy, busyLabel, idleLabel) {
        const btn = document.getElementById(btnId);
        const label = btn.querySelector('.btn-label');
        const spinner = btn.querySelector('.btn-spinner');
        btn.disabled = busy;
        if (label && busyLabel !== undefined) label.textContent = busy ? busyLabel : idleLabel;
        if (spinner) spinner.style.display = busy ? 'inline-block' : 'none';
    }

    // Dipanggil saat SATU rangkaian proses benar-benar tuntas. Tombol
    // sengaja tetap dinonaktifkan (bukan dikembalikan aktif) supaya "SELESAI"
    // adalah status akhir yang inert -- kalau tetap aktif, klik ulang akan
    // memicu startProcess()/startGeoProcess() lagi dan label balik jadi
    // "MEMPROSES..." padahal tidak ada proses baru yang benar-benar
    // berjalan (ini persis bug yang dilaporkan).
    function setBtnDone(btnId, doneLabel) {
        const btn = document.getElementById(btnId);
        btn.disabled = true;
        btn.querySelector('.btn-label').textContent = doneLabel;
        btn.querySelector('.btn-spinner').style.display = 'none';
    }

    // Dipanggil setiap kali daftar file yang akan diproses berubah (Scan
    // ulang, Terapkan Filter, atau Reset di Langkah 3) -- mengembalikan
    // tombol aksi ke label semula dan status aktif/nonaktif sesuai isi
    // daftar TERBARU, supaya tidak nyangkut di label "SELESAI" dari
    // pemrosesan sebelumnya.
    function resetActionButton(btnId, idleLabel, enabled) {
        const btn = document.getElementById(btnId);
        btn.querySelector('.btn-label').textContent = idleLabel;
        btn.querySelector('.btn-spinner').style.display = 'none';
        btn.disabled = !enabled;
    }

    // ------------------------------------------------------------------
    // Jendela pop-up (modal) pemantauan proses, gaya mirip ringkasan run
    // GitHub Actions: satu baris per file, ikon status (antre/berjalan/
    // berhasil/dilewati-aman/gagal), progress bar, dan ringkasan di akhir.
    // ------------------------------------------------------------------
    let procCounts = { success: 0, warn: 0, error: 0 };

    function cssEscapeId(s) {
        return s.replace(/[^a-zA-Z0-9_-]/g, '_');
    }

    function openProcModal(title, filenames) {
        procCounts = { success: 0, warn: 0, error: 0 };
        document.getElementById('procModalTitle').textContent = title;
        document.getElementById('procModalCloseBtn').disabled = true;
        document.getElementById('procSummaryBox').style.display = 'none';
        document.getElementById('procProgressBar').style.width = '0%';
        document.getElementById('procProgressBar').textContent = '0%';
        document.getElementById('procStatusText').textContent = `0/${filenames.length} selesai`;
        const list = document.getElementById('procFileList');
        list.innerHTML = '';
        filenames.forEach(f => {
            const row = document.createElement('div');
            row.className = 'proc-row status-queued';
            row.dataset.status = 'queued';
            row.id = 'procrow-' + cssEscapeId(f);
            row.innerHTML = `<span class="proc-icon">○</span><span class="proc-filename">${f}</span><span class="proc-message">Menunggu antrean...</span>`;
            list.appendChild(row);
        });
        document.getElementById('procModalOverlay').classList.add('open');
    }

    function setProcRowStatus(filename, status, message) {
        const row = document.getElementById('procrow-' + cssEscapeId(filename));
        if (!row) return;
        // Ikon HANYA dibuat ulang saat status benar-benar berubah (mis.
        // queued -> running). Selama file yang sama masih 'running', progres
        // (offset/persentase baris terbaca) memanggil fungsi ini berkali-kali
        // HANYA untuk memperbarui teks pesan -- kalau .proc-icon ditulis
        // ulang setiap kali, elemen spinner-combo dibongkar-pasang terus
        // dan animasi CSS-nya restart dari nol setiap panggilan, itulah
        // penyebab animasi terlihat tersendat/patah-patah.
        if (row.dataset.status !== status) {
            row.className = 'proc-row status-' + status;
            row.dataset.status = status;
            const icon = status === 'running' ? '<span class="spinner-combo"><span class="ring"></span><span class="dot"></span></span>'
                : status === 'success' ? '✓'
                : status === 'warn' ? '⚠'
                : status === 'error' ? '✗'
                : '○';
            row.querySelector('.proc-icon').innerHTML = icon;
        }
        row.querySelector('.proc-message').textContent = message || '';
        if (status === 'running') row.scrollIntoView({ block: 'nearest' });
    }

    function updateProcProgress(doneCount, total) {
        const pct = total ? Math.round(doneCount / total * 100) : 0;
        document.getElementById('procProgressBar').style.width = pct + '%';
        document.getElementById('procProgressBar').textContent = pct + '%';
        document.getElementById('procStatusText').textContent = `${doneCount}/${total} selesai`;
    }

    function finishProcModal() {
        document.getElementById('procModalCloseBtn').disabled = false;
        const box = document.getElementById('procSummaryBox');
        box.style.display = 'block';
        box.innerHTML = `Selesai. <span style="color:#2ed573">${procCounts.success} berhasil</span>, ` +
            `<span style="color:#ffa502">${procCounts.warn} dilewati (aman)</span>, ` +
            `<span style="color:#ff6b81">${procCounts.error} gagal (perlu ditinjau)</span>.`;
    }

    function closeProcModal() {
        document.getElementById('procModalOverlay').classList.remove('open');
    }

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
        geoPaginator.setData(list);
        document.getElementById('geoStatus').innerText =
            `${list.length} dari ${geoIncompleteRaw.length} file dengan geolocation belum lengkap dipilih untuk diproses.`;
        // Daftar file berubah (Scan ulang / Terapkan Filter / Reset) -> tombol
        // aksi HARUS kembali ke label semula, bukan tetap "SELESAI" dari
        // pemrosesan sebelumnya (lihat setBtnDone()).
        resetActionButton('btnGeoStart', 'MULAI', list.length > 0);
        setCardBadge('geo', list.length);
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
        setBtnBusy('btnScan', true, 'MEMINDAI...', 'JALANKAN SCAN');
        document.getElementById('scanStatus').innerText = "Memindai archive/...";
        fetch('?action=scan').then(r => r.json()).then(data => {
            setBtnBusy('btnScan', false, 'MEMINDAI...', 'JALANKAN SCAN');
            if (data.status !== 'done') {
                document.getElementById('scanStatus').innerText = "Gagal: " + (data.message || 'Kesalahan tidak diketahui.');
                return;
            }
            const mismatchCount = data.affected.filter(f => f.domain_mismatch).length;
            geoSkippedCount = data.geo_skipped_count || 0;
            document.getElementById('scanStatus').innerText =
                `Total file di archive/: ${data.total_files}. Sudah ada data (aman): ${data.ok_count}. Perlu diproses ulang: ${data.affected_count}` +
                (mismatchCount > 0 ? `. INFO: ${mismatchCount} file domainnya beda dari situs saat ini (${data.site_host}) -- domain ini akan otomatis ditulis-ulang ke base_url saat ini sebelum diproses (lihat action=process_start), jadi tetap diharapkan berhasil diresolve.` : '.') +
                (geoSkippedCount > 0 ? ` ${geoSkippedCount} file geolocation-nya sudah pernah dicoba dilengkapi sebelumnya dan tidak bertambah lengkap -- disembunyikan dari daftar Langkah 3.` : '');

            affectedFiles = data.affected.map(f => ({ filename: f.filename, detectedHost: f.detected_host || '' }));
            scanPaginator.setData(data.affected);
            setCardBadge('process', affectedFiles.length);
            // Scan ulang -> daftar berubah, tombol aksi HARUS kembali ke
            // label semula, bukan tetap "SELESAI" dari Scan sebelumnya.
            resetActionButton('btnStart', 'MULAI', affectedFiles.length > 0);
            if (affectedFiles.length > 0) {
                unlockStepCard('process');
            }

            // Langkah 3: file yang SUDAH punya baris tapi geolocation-nya bolong.
            // Simpan daftar mentahnya supaya filter tanggal (opsional) bisa
            // diterapkan/dilepas berkali-kali tanpa perlu Scan ulang.
            geoIncompleteRaw = data.geo_incomplete;
            renderGeoTable(geoIncompleteRaw);
            if (geoIncompleteRaw.length > 0) {
                unlockStepCard('geo');
            }
        }).catch(err => {
            setBtnBusy('btnScan', false, 'MEMINDAI...', 'JALANKAN SCAN');
            document.getElementById('scanStatus').innerText = "Gagal: " + err.message;
        });
    }

    function startGeoProcess() {
        if (!confirm(`${geoFiles.length} file yang SUDAH punya data akan DIHAPUS baris lamanya lalu diproses ulang untuk melengkapi geolocation. Ini menyentuh data yang sudah ada (bukan yang kosong). Lanjutkan?`)) return;
        setBtnBusy('btnGeoStart', true, 'MEMPROSES...', 'MULAI');
        document.getElementById('geoStatus').innerText = 'Memproses -- lihat detail di jendela pop-up.';
        geoCurrentIndex = 0;
        openProcModal('Langkah 3 — Melengkapi Geolocation', geoFiles.map(f => f.filename));
        geoProcessNext();
    }

    function geoProcessNext() {
        if (geoCurrentIndex >= geoFiles.length) {
            updateProcProgress(geoFiles.length, geoFiles.length);
            finishProcModal();
            document.getElementById('geoStatus').innerText = `Selesai: ${procCounts.success} berhasil, ${procCounts.warn} dilewati (aman), ${procCounts.error} gagal.`;
            setBtnDone('btnGeoStart', 'SELESAI');
            return;
        }

        const { filename, detectedHost } = geoFiles[geoCurrentIndex];
        setProcRowStatus(filename, 'running', 'Memproses...');

        processOneFileBatched(filename, detectedHost, (offset, total) => {
            const pct = total ? Math.round(offset / total * 100) : 0;
            setProcRowStatus(filename, 'running', `Memproses... (${pct}% baris terbaca)`);
        }).then(data => {
            if (data.outcome === 'busy') {
                setProcRowStatus(filename, 'running', `${data.message} Dicoba lagi 3 detik lagi.`);
                setTimeout(geoProcessNext, 3000);
                return;
            }
            if (data.outcome === 'rejected') {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Gagal permanen: ${data.message} Data lama tetap aman (${data.metricsBefore} baris, tidak diubah) karena proses dibatalkan sebelum sempat disimpan. File dipindahkan ke folder reject/ untuk ditinjau manual.`);
            } else if (data.outcome === 'error') {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Gagal: ${data.message}`);
            } else if (data.metrics_after > 0 && data.no_geo_after < data.no_geo_before) {
                procCounts.success++;
                const rewriteNote = data.domain_rewritten ? ` (domain asal ${data.detected_host} ditulis-ulang otomatis ke base_url situs saat ini)` : '';
                const improved = data.no_geo_before - data.no_geo_after;
                setProcRowStatus(filename, 'success', `Berhasil${rewriteNote}. Baris tanpa geolocation: ${data.no_geo_before} -> ${data.no_geo_after} (dari total ${data.metrics_after} baris) -- ${improved} baris kini punya geolocation yang sebelumnya tidak ada.`);
            } else if (data.metrics_after > 0 && data.no_geo_after >= data.no_geo_before) {
                procCounts.warn++;
                setProcRowStatus(filename, 'warn', `Diproses ulang, tapi geolocation TIDAK bertambah lengkap: tetap ${data.no_geo_after} dari total ${data.metrics_after} baris tanpa geolocation -- kemungkinan besar IP di baris-baris itu tidak terdaftar di database GeoIP. Data aman, tidak ada yang hilang. File ini tidak akan ditawarkan lagi di Scan berikutnya.`);
            } else if (data.moved_to_reject) {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Gagal permanen: baris lama (${data.metrics_before} baris) sudah terhapus dan TIDAK ADA PENGGANTI. File dipindahkan ke reject/ -- PERLU DITINJAU MANUAL SEGERA.`);
            } else {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Selesai diproses, tapi baris metrics menjadi 0 (sebelumnya ${data.metrics_before} baris). Perlu ditinjau manual.`);
            }
            geoCurrentIndex++;
            updateProcProgress(geoCurrentIndex, geoFiles.length);
            setTimeout(geoProcessNext, FILE_DELAY_MS);
        });
    }

    function startProcess() {
        if (!confirm(`${affectedFiles.length} file akan diproses ulang satu per satu. Lanjutkan?`)) return;
        setBtnBusy('btnStart', true, 'MEMPROSES...', 'MULAI');
        document.getElementById('status').innerText = 'Memproses -- lihat detail di jendela pop-up.';
        currentIndex = 0;
        openProcModal('Langkah 2 — Memproses File Kosong', affectedFiles.map(f => f.filename));
        processNext();
    }

    function processNext() {
        if (currentIndex >= affectedFiles.length) {
            updateProcProgress(affectedFiles.length, affectedFiles.length);
            finishProcModal();
            document.getElementById('status').innerText = `Selesai: ${procCounts.success} berhasil, ${procCounts.warn} dilewati (aman), ${procCounts.error} gagal.`;
            setBtnDone('btnStart', 'SELESAI');
            return;
        }

        const { filename, detectedHost } = affectedFiles[currentIndex];
        setProcRowStatus(filename, 'running', 'Memproses...');

        processOneFileBatched(filename, detectedHost, (offset, total) => {
            const pct = total ? Math.round(offset / total * 100) : 0;
            setProcRowStatus(filename, 'running', `Memproses... (${pct}% baris terbaca)`);
        }).then(data => {
            if (data.outcome === 'busy') {
                setProcRowStatus(filename, 'running', `${data.message} Dicoba lagi 3 detik lagi.`);
                setTimeout(processNext, 3000);
                return;
            }
            if (data.outcome === 'rejected') {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Gagal permanen: ${data.message} File dipindahkan ke folder reject/ untuk ditinjau manual.`);
            } else if (data.outcome === 'error') {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Gagal: ${data.message}`);
            } else if (data.metrics_after > 0) {
                procCounts.success++;
                const rewriteNote = data.domain_rewritten ? ` (domain asal ${data.detected_host} ditulis-ulang otomatis ke base_url situs saat ini)` : '';
                setProcRowStatus(filename, 'success', `Berhasil${rewriteNote}. Baris metrics: ${data.metrics_before} -> ${data.metrics_after} (total baris yang berhasil dipulihkan: ${data.metrics_after}).`);
            } else if (data.moved_to_reject) {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Gagal permanen: baris metrics tetap 0, kemungkinan URL di file ini tidak cocok dengan base_url situs saat ini atau kontennya sudah tidak ada. File dipindahkan ke reject/.`);
            } else {
                procCounts.error++;
                setProcRowStatus(filename, 'error', `Selesai diproses, tapi baris metrics masih 0. Perlu ditinjau manual.`);
            }
            currentIndex++;
            updateProcProgress(currentIndex, affectedFiles.length);
            setTimeout(processNext, FILE_DELAY_MS);
        });
    }
</script>
</body>
</html>