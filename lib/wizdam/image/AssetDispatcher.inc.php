<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/image/AssetDispatcher.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class AssetDispatcher
 * @ingroup wizdam_lib_image
 *
 * @brief Menangkap request gambar (skema URL semantik baru DAN kompatibilitas
 * URL publik lama) langsung di index.php -- SEBELUM Application::execute()
 * penuh dimuat -- murni lewat PHP, TANPA rewrite rule .htaccess baru.
 *
 * Ini memanfaatkan rule .htaccess yang SUDAH ADA (front-controller pattern:
 * request yang bukan file/direktori nyata otomatis diteruskan ke
 * index.php/$1). Sejak file upload dipindah keluar dari public/ (ke
 * public_files_dir yang baru, di luar webroot), path lama seperti
 * "/public/site/pageHeaderTitleImage_en_US.jpg" TIDAK LAGI ada sebagai file
 * nyata -- otomatis diteruskan ke index.php lewat mekanisme yang sudah ada,
 * lalu class ini yang menangkap dan menyajikannya dari lokasi baru.
 *
 * Dua pola yang ditangani:
 *   1. BARU (semantik, kode khusus/opaque): /assets/images/[MODIFIER]/[TYPE]/[ID]?as=[FORMAT]
 *      -> didelegasikan ke AssetImageRouter (cache hasil resize memakai nama
 *         opaque, TIDAK membawa locale/nama file asli -- lihat AssetImageRouter).
 *   2. LAMA (kompatibilitas, transisi bertahap): /public/{site|journals/{id}}/{namaFileAsli}
 *      -> file SUMBER tetap dicari dengan nama aslinya (locale tetap ada di
 *         nama file SUMBER -- itu TIDAK diubah, supaya link lama yang sudah
 *         terindeks Google / dikutip eksternal tetap jalan), tapi kini
 *         dibaca dari public_files_dir yang baru (di luar webroot), bukan
 *         lagi disajikan sebagai file statis langsung oleh webserver.
 */

import('lib.pkp.classes.core.Core');
import('lib.pkp.classes.config.Config');

class AssetDispatcher {

    /** @var string[] Ekstensi yang boleh disajikan lewat jalur kompatibilitas lama. */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];

    /**
     * Titik masuk utama. Dipanggil dari index.php tepat setelah bootstrap
     * (DB/DAORegistry sudah siap untuk AssetImageRouter), sebelum
     * Application::get()->execute().
     *
     * @param string $pathInfo Nilai $_SERVER['PATH_INFO'] apa adanya.
     * @return bool True kalau request ini ditangani di sini (proses sudah
     *   exit() di dalam) -- false kalau tidak cocok pola manapun, caller
     *   (index.php) lanjut ke Application::execute() seperti biasa.
     */
    public static function dispatch(string $pathInfo): bool {
        if ($pathInfo === '') {
            return false;
        }

        $normalized = '/' . ltrim($pathInfo, '/');

        // 1. Skema BARU (semantik/opaque) -- didelegasikan ke AssetImageRouter.
        if (strpos($normalized, '/assets/images/') === 0) {
            import('lib.wizdam.image.AssetImageRouter');
            $router = new AssetImageRouter();
            $requestUri = $_SERVER['REQUEST_URI'] ?? $normalized;
            // route() sendiri yang exit() di dalam serve()/outputFile() kalau
            // pola cocok. Kalau ternyata tidak cocok (return false), request
            // ini TIDAK dianggap tertangani -- biarkan lanjut ke routing
            // normal supaya tidak diam-diam 404 untuk kasus yang tak terduga.
            if ($router->route($requestUri)) {
                return true;
            }
            return false;
        }

        // 2. Skema LAMA (kompatibilitas) -- /public/site/... atau /public/journals/{id}/...
        if (preg_match('#^/public/(.+)$#', $normalized, $m)) {
            self::_serveLegacyPublicFile($m[1]);
            return true; // _serveLegacyPublicFile() selalu exit() di semua jalurnya.
        }

        return false;
    }

    /**
     * Menyajikan file dari lokasi public_files_dir yang BARU, mempertahankan
     * struktur path relatif & nama file yang PERSIS SAMA dengan skema lama --
     * supaya URL yang sudah terlanjur terindeks/dikutip eksternal tetap
     * berfungsi tanpa perlu tabel pemetaan nama lama->baru.
     *
     * @param string $relativePath Bagian path setelah "/public/", apa adanya dari URL.
     */
    private static function _serveLegacyPublicFile(string $relativePath): void {
        // Sanitasi: buang segmen kosong/'.'/'..' -- mencegah directory traversal
        // MURNI lewat normalisasi segmen, bukan cuma string-replace naif.
        $relativePath = str_replace('\\', '/', $relativePath);
        $segments = array_filter(
            explode('/', $relativePath),
            fn(string $s): bool => $s !== '' && $s !== '.' && $s !== '..'
        );

        if (empty($segments)) {
            self::_notFound();
        }

        $ext = strtolower((string) pathinfo(end($segments), PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            // Bukan tipe file gambar yang dikenal -- jangan sajikan lewat
            // jalur ini (mencegah jalur ini disalahgunakan untuk membaca
            // file non-gambar sembarangan dari dalam public_files_dir).
            self::_notFound();
        }

        $baseDir = Core::getBaseDir() . '/' . Config::getVar('files', 'public_files_dir');
        $requestedPath = $baseDir . '/' . implode('/', $segments);

        // Pertahanan berlapis: pastikan hasil realpath() BENAR-BENAR masih
        // di dalam $baseDir, bukan cuma percaya hasil sanitasi segmen di atas.
        $realBase = realpath($baseDir);
        $realRequested = realpath($requestedPath);
        if ($realBase === false || $realRequested === false || strpos($realRequested, $realBase) !== 0) {
            self::_notFound();
        }

        if (!is_file($realRequested)) {
            self::_notFound();
        }

        self::_streamFile($realRequested);
    }

    /**
     * Stream file ke browser dengan header caching yang benar (public, bukan
     * private -- file ini memang dimaksudkan untuk diakses publik/dicache
     * CDN), plus dukungan conditional GET (ETag/Last-Modified -> 304) supaya
     * kunjungan berulang tidak perlu transfer ulang seluruh file.
     *
     * @param string $path Path absolut yang SUDAH divalidasi aman.
     */
    private static function _streamFile(string $path): void {
        $mtime = (int) filemtime($path);
        $etag = '"' . md5($path . $mtime) . '"';

        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
        $ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime((string) $_SERVER['HTTP_IF_MODIFIED_SINCE']) : null;

        if (($ifNoneMatch !== null && trim($ifNoneMatch) === $etag)
            || ($ifModifiedSince !== null && $ifModifiedSince >= $mtime)) {
            header('HTTP/1.1 304 Not Modified');
            header('ETag: ' . $etag);
            exit;
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            default => 'image/jpeg',
        };

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('ETag: ' . $etag);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        // [CATATAN] 'public' (bukan 'private') -- ini file publik yang
        // sengaja boleh dicache CDN/proxy/browser bersama, bukan file privat
        // per-pengguna. Beda konteks dari FileManager::downloadFile() yang
        // dipakai untuk file privat (submission/review).
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Pragma: public');

        readfile($path);
        exit;
    }

    private static function _notFound(): void {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

}
?>