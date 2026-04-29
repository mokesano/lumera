<?php
/**
 * @file pages/trends/TrendsHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM] - Hub/Landing Page untuk semua metrik Trends ScholarWizdam.
 * URL Target: /{context}/trends
 */

declare(strict_types=1);

import('classes.handler.Handler');

class TrendsHandler extends Handler {

    public function authorize($request, $args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
        $this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noContext', false));
        return parent::authorize($request, $args, $roleAssignments);
    }

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