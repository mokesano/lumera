<?php
declare(strict_types=1);

/**
 * @file pages/trends/TrendsHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @class TrendsHandler
 * @ingroup pages_trends
 * 
 * @brief Handler for displaying the Trends Hub.
 * 
 * [WIZDAM] - Hub/Landing Page untuk semua metrik Trends ScholarWizdam.
 * URL Target: /{context}/trends
 */

import('classes.handler.Handler');

class TrendsHandler extends Handler {

    /**
     * Display the Trends Hub.
     * @param $args array
     * @param $request PKPRequest
     */
    public function authorize($request, $args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
        $this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noContext'));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * [BUGFIX] Home (dan inisial jurnal, kalau di dalam jurnal) SUDAH
     * otomatis dirender breadcrumbs.tpl SEBELUM dan DI LUAR loop
     * $pageHierarchy (lihat blok "1. Link Home" & "2. Link Jurnal" di
     * template) -- jadi TIDAK PERLU ditambahkan lagi di sini. Halaman ini
     * adalah hub /trends itu sendiri, jadi cukup array kosong.
     * @param PKPRequest|null $request
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate($request);
        if (!$request) $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageHierarchy', []);
    }

    /**
     * Display the Trends Hub.
     * @param $args array
     * @param $request PKPRequest
     */
    public function index(array $args = [], $request = NULL) {
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        $journal = $request->getJournal();

        if ($journal) {
            $this->addCheck(new HandlerValidatorJournal($this));
        }

        // Generate URL untuk masing-masing pilar trends agar tombol di Hub bisa diklik
        $templateMgr->assign([
            'hubPopularUrl'  => $request->url(null, 'trends', 'popular'),
            'hubDownloadUrl' => $request->url(null, 'trends', 'download'),
            'hubCitedUrl'    => $request->url(null, 'trends', 'cited')
        ]);

        // Tampilkan template Hub Anda
        return $templateMgr->display('trends/trends.tpl');
    }

}
?>