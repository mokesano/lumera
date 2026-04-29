<?php
declare(strict_types=1);

/**
 * File: MetricsHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Menangani permintaan untuk halaman "metrik" kustom sebuah artikel.
 *
 * @class MetricsHandler
 * @extends ArticleHandler
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.article.ArticleHandler');

class MetricsHandler extends ArticleHandler {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MetricsHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Menampilkan halaman metrik untuk artikel tertentu.
     * Fungsi ini adalah titik masuk utama untuk URL:
     * /article/view/<articleId>/metrics
     *
     * @param array $args Argumen URL (misal, $args[0] adalah <articleId>)
     * @param PKPRequest $request Objek Request OJS
     */
    public function metrics($args = [], $request = null) {

        // --- 1. Inisialisasi dan Pengambilan Data Dasar ---

        // [WIZDAM] Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        // Ambil ID artikel dari argumen URL.
        $articleId = isset($args[0]) ? (int) $args[0] : 0;

        // Ambil objek penting: Jurnal saat ini dan Pengguna yang login (jika ada)
        $journal = $request->getJournal();
        $user = $request->getUser();

        // --- 2. Validasi Input (Guard Clauses) ---

        // Jika tidak ada ID artikel di URL, kembalikan pengguna ke halaman indeks.
        if (!$articleId) {
            $request->redirect(null, 'index');
            return;
        }

        // Muat data artikel dari database menggunakan ArticleDAO
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        // Jika artikel dengan ID tersebut tidak ditemukan, kembalikan ke indeks.
        if (!$article) {
            $request->redirect(null, 'index');
            return;
        }

        // --- 3. Pengecekan Izin Akses (Permission Check) ---

        // Aturan: Metrik hanya boleh dilihat publik jika artikel sudah 'published'.
        // [WIZDAM] Gunakan konstanta status yang benar
        if ($article->getStatus() != STATUS_PUBLISHED) {
            
            // Jika artikel BELUM 'published', kita perlu cek lebih lanjut.
            // Apakah pengguna adalah penulis artikel ini?
            $isAuthor = $user && $user->getId() == $article->getUserId();
            
            // Apakah pengguna adalah editor?
            // [WIZDAM] Standardized OJS validation check
            $isEditor = Validation::isEditor($journal->getId()) || Validation::isSectionEditor($journal->getId());

            // Jika pengguna tidak login, ATAU
            // jika dia bukan penulis DAN bukan editor, maka akses ditolak.
            if (!$user || (!$isAuthor && !$isEditor)) {
                $request->redirect(null, 'index');
                return;
            }
        }

        // --- 4. Menyiapkan Halaman (Template) ---

        // Jika lolos semua pemeriksaan di atas, lanjutkan ke penyiapan template.
        $templateMgr = TemplateManager::getManager($request);

        // Kirim data artikel ke file template (.tpl)
        $templateMgr->assign('article', $article);

        // --- 5. Pengambilan Statistik (Metode Coba-Coba) ---

        // Inisialisasi variabel statistik
        $views = 0;

        // PENTING: Blok ini adalah metode "rapuh" (fragile) untuk mendapatkan statistik.
        // Ini bekerja dengan MENEBAK nama DAO (Data Access Object) statistik
        
        // Daftar nama DAO yang akan dicoba
        $triedDaoNames = [
            'UsageStatsDAO',
            'MetricsDAO',
            'ArticleStatisticsDAO',
            'StatisticsDAO'
        ];

        foreach ($triedDaoNames as $daoName) {
            // Coba dapatkan DAO dari registri OJS
            $dao = DAORegistry::getDAO($daoName);

            // Periksa apakah OJS berhasil menemukan dan memuat DAO tersebut
            // [WIZDAM] Updated is_a to instanceof
            if ($dao && $dao instanceof DAO) {
                
                // Jika DAO ada, coba tebak nama fungsinya (method)
                if (method_exists($dao, 'getTotalViews')) {
                    $views = (int) $dao->getTotalViews($articleId);
                    
                    // Jika kita berhasil menemukan data, hentikan perulangan (break)
                    break;
                }
            }
        } // Akhir dari foreach loop

        // --- 6. Menampilkan Halaman ---

        // Kirim data 'views' yang didapat ke template
        $templateMgr->assign('views', $views);
        
        // Tampilkan file template .tpl yang sesuai
        $templateMgr->display('article/metrics.tpl');
        
    } // Akhir dari fungsi metrics()

} // Akhir dari kelas MetricsHandler

?>