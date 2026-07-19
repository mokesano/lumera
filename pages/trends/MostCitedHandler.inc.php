<?php
declare(strict_types=1);

/**
 * @file pages/trends/MostCitedHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @class MostCitedHandler
 * @ingroup pages_trends
 * 
 * @brief Handler for displaying the most cited articles.
 * 
 * URL Target: /{context}/trends/cited ATAU /index/trends/cited
 */

import('classes.handler.Handler');

class MostCitedHandler extends Handler {

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
     * Display the most cited articles.
     * @param $args array
     * @param $request PKPRequest
     */
    // Nama method WAJIB "cited" sesuai parameter $op
    public function cited(array $args, PKPRequest $request) {
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        $journal = $request->getJournal();

        // Validasi opsional jika berada di dalam jurnal
        if ($journal) {
            $this->addCheck(new HandlerValidatorJournal($this));
        }

        // [WIZDAM] Eksekusi WIZDAM Trends Manager
        import('lib.wizdam.trends.WizdamTrendsManager');
        WizdamTrendsManager::assignMostCitedPayload($templateMgr, $journal, $request);

        // Path ke template yang menyatukan header/footer WIZDAM dan most_cited.tpl
        return $templateMgr->display('trends/most_cited.tpl');
    }

}
?>