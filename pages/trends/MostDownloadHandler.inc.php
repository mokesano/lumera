<?php
declare(strict_types=1);

/**
 * @file pages/trends/MostDownloadHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @class MostDownloadHandler
 * @ingroup pages_trends
 * 
 * @brief Handler for displaying the most download articles.
 * 
 * URL Target: /{context}/trends/download ATAU /index/trends/download
 */

import('classes.handler.Handler');

class MostDownloadHandler extends Handler {

    /**
     * Authorize the request.
     * @param $request PKPRequest
     * @param $args array
     * @param $roleAssignments array
     * @return boolean
     */
    public function authorize($request, $args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
        // Set context required false, agar bisa diakses di site level maupun journal level
        $this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noContext'));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * [BUGFIX] Home (dan inisial jurnal) SUDAH otomatis dirender
     * breadcrumbs.tpl di luar loop $pageHierarchy -- cukup tautkan balik
     * ke hub Trends (pola yang sama seperti SearchHandler::setupTemplate()).
     * @param PKPRequest|null $request
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate($request);
        if (!$request) $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageHierarchy', [
            [$request->url(null, 'trends'), 'Trends & Metrics', true],
        ]);
    }

    /**
     * Display the most downloaded articles.
     * @param $args array
     * @param $request PKPRequest
     */
    public function download(array $args, PKPRequest $request) {
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        $journal = $request->getJournal();

        // Validasi opsional jika berada di dalam jurnal
        if ($journal) {
            $this->addCheck(new HandlerValidatorJournal($this));
        }

        // [WIZDAM] Eksekusi WIZDAM Trends Manager
        import('lib.wizdam.trends.TrendsManager');
        TrendsManager::assignMostDownloadedPayload($templateMgr, $journal, $request);

        // Path ke template yang menyatukan header/footer WIZDAM dan most_downloaded.tpl
        return $templateMgr->display('trends/most_downloaded.tpl');
    }
    
}
?>