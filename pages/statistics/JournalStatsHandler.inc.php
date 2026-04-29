<?php
declare(strict_types=1);

/**
 * @file pages/statistics/JournalStatsHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalStatsHandler
 * @ingroup pages_statistics
 * 
 * @brief Unified Handler untuk Halaman Standalone Statistik Jurnal & Site [WIZDAM EDITION]
 * @version 2.0 (Strict MVC Compliant)
 */

import('classes.handler.Handler');
import('lib.wizdam.statistics.StatsManager'); // Load Service Layer WIZDAM

class JournalStatsHandler extends Handler {

    /**
     * Constructor 
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [WIZDAM] - Golden Rule 5: Otorisasi Fleksibel (Bypass ContextRequiredPolicy)
     * Mengizinkan halaman statistik ini diakses di root (Site Level) tanpa memicu error 404/Context Required.
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        import('classes.security.authorization.ContextRequiredPolicy');
        // Parameter ke-3 adalah false untuk mengizinkan akses tanpa konteks jurnal
        $this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noContext', false));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * [WIZDAM] - Unified Index Method (Golden Rule 1 & 2)
     * Menggabungkan logika Site Level dan Journal Level di satu tempat.
     * @param array $args
     * @param Request $request
     */
    public function index(array $args = [], $request = NULL) {
        // Ambil objek jurnal (akan null jika diakses dari root/site level)
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        // Fitur WIZDAM: Parameter opsional untuk memaksa refresh cache via URL (?refresh_stats=true)
        $forceRefresh = ($request->getUserVar('refresh_stats') === 'true');

        // [WIZDAM] - Context-Aware Controller
        if ($journal) {
            // Validasi tambahan khusus untuk level jurnal
            import('classes.handler.validation.HandlerValidatorJournal');
            $this->addCheck(new HandlerValidatorJournal($this));
        }

        // Setup dasar halaman
        $this->setupTemplate($request);

        // [WIZDAM] - Panggil Service Layer untuk menyuntikkan Micro-Payloads ke TemplateManager
        // Ini akan otomatis mendeteksi apakah kita di level jurnal atau site berdasarkan parameter $journal
        StatsManager::assignWidgetPayload($templateMgr, $journal, $forceRefresh);

        // [WIZDAM] - Tentukan file View (.tpl) yang akan dimuat
        if ($journal) {
            // Tampilan Standalone untuk Level Jurnal
            $templateMgr->display('trends/journalStats_standalone.tpl');
        } else {
            // Tampilan Standalone untuk Level Site (Publisher Root)
            $templateMgr->display('trends/siteStats_standalone.tpl');
        }
    }

    /**
     * Helper untuk memuat template header/footer standar OJS
     * @param Request $request
     */
    public function setupTemplate() {
        parent::setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        
        // [WIZDAM] - Set breadcrumb yang sesuai
        $pageHierarchy = [
            [
                $request->url(null, 'index'),
                'navigation.home'
            ]
        ];
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
        $templateMgr->assign('pageTitle', 'navigation.statistics'); // Pastikan key locale ini ada
    }
}
?>