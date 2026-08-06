<?php
declare(strict_types=1);

/**
 * @file pages/trends/MostPopularHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @class MostPopularHandler
 * @ingroup pages_trends
 * 
 * @brief Handler for displaying the most popular articles.
 * 
 * URL Target: /{context}/trends/popular ATAU /index/trends/popular
 */

import('classes.handler.Handler');

class MostPopularHandler extends Handler {

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
     * Display the most popular articles.
     * @param $args array
     * @param $request PKPRequest
     */
    // Nama method WAJIB "popular" sesuai parameter $op
    public function popular(array $args, PKPRequest $request) {
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        $journal = $request->getJournal();

        // Validasi opsional jika berada di dalam jurnal
        if ($journal) {
            $this->addCheck(new HandlerValidatorJournal($this));
        }

        // [WIZDAM] Eksekusi WIZDAM Trends Manager
        import('lib.wizdam.trends.TrendsManager');
        TrendsManager::assignMostPopularPayload($templateMgr, $journal, $request);

        // Path ke template yang menyatukan header/footer WIZDAM dan most_popular.tpl
        return $templateMgr->display('trends/most_popular.tpl');
    }
    
}
?>