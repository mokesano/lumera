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
 * @brief Unified Handler untuk Halaman Standalone Statistik Jurnal & Publisher
 * @version 2.0 (Strict MVC Compliant)
 */

import('classes.handler.Handler');
import('lib.wizdam.statistics.StatsManager');

class JournalStatsHandler extends Handler {

    /**
     * Constructor 
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Mengizinkan halaman statistik ini diakses di root (Site Level)
     * tanpa memicu error 404/Context Required.
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        import('classes.security.authorization.ContextRequiredPolicy');
        // Parameter ke-3 adalah false untuk mengizinkan akses tanpa konteks jurnal
        $this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noContext'));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Menggabungkan logika Site Level dan Journal Level di satu tempat.
     * @param array $args
     * @param Request $request
     */
    public function index(array $args = [], $request = NULL) {
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        // Fitur: Parameter refresh cache via URL (?refresh_stats=true)
        $forceRefresh = ($request->getUserVar('refresh_stats') === 'true');

        if ($journal) {
            import('classes.handler.validation.HandlerValidatorJournal');
            $this->addCheck(new HandlerValidatorJournal($this));
        }

        // Setup dasar halaman
        $this->setupTemplate($request, $journal);

        StatsManager::assignWidgetPayload($templateMgr, $journal, $forceRefresh);
        if ($journal) {
            // Tampilan Level Jurnal
            $templateMgr->display('trends/journalStats_standalone.tpl');
        } else {
            // Tampilan Level Publisher
            $templateMgr->display('trends/siteStats_standalone.tpl');
        }
    }

    /**
     * Helper untuk memuat template header/footer.
     * @param Request $request
     * @param Journal|null $journal
     */
    public function setupTemplate($request = null, $journal = null) {
        parent::setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign('pageHierarchy', []);
        $templateMgr->assign(
            'pageTitle',
            $journal ? 'manager.statistics.statistics' : 'about.statistics'
        );
    }

}
?>