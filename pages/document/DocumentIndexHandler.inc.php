<?php
declare(strict_types=1);

/**
 * @file pages/document/DocumentIndexHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class DocumentIndexHandler
 * @ingroup pages_document
 *
 * @brief Halaman landing Dokumen Resmi -- titik masuk tunggal menuju
 * indeks LoA dan indeks Sertifikat.
 */

import('classes.handler.Handler');
import('lib.wizdam.classes.services.LoAService');
import('lib.wizdam.classes.services.CertificateService');

class DocumentIndexHandler extends Handler {

    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));
    }

    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents([
            LOCALE_COMPONENT_CORE_COMMON,
            LOCALE_COMPONENT_CORE_USER,
            LOCALE_COMPONENT_APPLICATION_COMMON,
            LOCALE_COMPONENT_APP_PAYMENT
        ]);
    }

    /**
     * Rute: /document/index
     * @param array $args
     * @param Request|null $request
     */
    public function index(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $user = $request->getUser();

        $loaService = new LoAService();
        $certService = new CertificateService();

        $loaCount = count($loaService->getLoAIndexForUser((int) $user->getId()));
        $certCount = count($certService->getCertificateIndexForUser((int) $user->getId()));

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'loaCount' => $loaCount,
            'certificateCount' => $certCount,
            'loaIndexUrl' => $request->url(null, 'document', 'loaIndex'),
            'certificateIndexUrl' => $request->url(null, 'document', 'certificateIndex'),
            'pageTitle' => 'document.index.pageTitle',
        ]);
        $templateMgr->display('document/index.tpl');
    }

}
?>