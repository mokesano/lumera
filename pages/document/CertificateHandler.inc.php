<?php
declare(strict_types=1);

/**
 * @file pages/document/CertificateHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * @class CertificateHandler
 * @ingroup pages_document
 *
 * @brief Handler for displaying and downloading Certificates (Reviewer/Author).
 * Integrated with Smart Router, SecurityHashService, and Ownership Validation.
 */

import('classes.handler.Handler');
import('lib.wizdam.classes.services.CertificateService');
import('lib.wizdam.classes.services.PdfService');
import('lib.wizdam.classes.services.QrCodeService');
import('lib.wizdam.classes.security.SecurityHashService');

class CertificateHandler extends Handler {
    
    /** @var CertificateService */
    private CertificateService $certService;

    /** @var SecurityHashService */
    private SecurityHashService $securityHashService;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));

        $this->certService = new CertificateService();
        $this->securityHashService = new SecurityHashService();
    }

    /**
     * Setup the template.
     * @param mixed $request
     * @return void
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents([
            LOCALE_COMPONENT_CORE_COMMON, 
            LOCALE_COMPONENT_CORE_USER, 
            LOCALE_COMPONENT_APPLICATION_COMMON
        ]);
    }

    /**
     * SMART ROUTER: Display HTML or Download PDF of the Certificate.
     * HTML Route: /document/certificate/[hash]-[reviewId]
     * PDF Route:  /document/certificate/pdf-[hash]-[reviewId]
     * URL Security Validation, Ownership Validation, and Integrated Business Logic.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function index(array $args = [], $request = null): void {
        $this->validate();
        
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $this->setupTemplate($request);
        $user = $request->getUser();

        $param = $args[0] ?? '';
        if ($param === '') {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $isPdf = str_starts_with($param, 'pdf-');
        $cleanParam = $isPdf ? substr($param, 4) : $param;

        if (strlen($cleanParam) <= 65 || ($cleanParam[64] ?? '') !== '-') {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $providedHash = substr($cleanParam, 0, 64);
        $reviewId = (int) substr($cleanParam, 65);

        // 1. URL Security Validation
        if (!$this->securityHashService->validateHash('certificate', $reviewId, $providedHash)) {
            $this->_redirectWithError($request, 'document.error.hashValidationFailed');
        }

        // 2. Ownership Validation (Only the assigned reviewer can download)
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        
        if (!$reviewAssignment || $reviewAssignment->getReviewerId() !== (int) $user->getId()) {
            $this->_redirectWithError($request, 'document.cert.unauthorized');
        }

        // 3. Business Logic Processing
        $certData = null;
        try {
            $certData = $this->certService->getReviewerCertificateData($reviewId);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'INCOMPLETE_REVIEW') {
                $this->_redirectWithError($request, 'document.cert.incompleteReview');
            } else {
                $this->_redirectWithError($request, 'document.cert.notFound');
            }
        }

        // 4. Generate QR Code for Public Authentication
        $authHash = $this->securityHashService->generateHash('certificate', $reviewId);
        $authenticateUrl = $request->url(null, 'authenticate', 'certificate', ["{$authHash}-{$reviewId}"]);
        
        $qrService = new QrCodeService();
        $qrCodeBase64 = $qrService->generateBase64($authenticateUrl);

        // 5. Execute PDF or HTML
        if ($isPdf) {
            $pdfService = new PdfService();
            // Assumption: You will add the generateCertificatePdf function in PdfService
            $pdfService->generateCertificatePdf($certData, $qrCodeBase64);
        } else {
            $templateMgr = TemplateManager::getManager($request);
            $pdfDownloadUrl = $request->url(null, 'document', 'certificate', ["pdf-{$providedHash}-{$reviewId}"]);

            $templateMgr->assign([
                'certData' => $certData,
                'qrCodeImage' => $qrCodeBase64,
                'pdfDownloadUrl' => $pdfDownloadUrl,
                'pageTitle' => 'document.cert.pageTitle',
                'pageHierarchy' => [[$request->url(null, 'user'), 'navigation.user']]
            ]);

            $templateMgr->display('document/certificate/private.tpl');
        }
    }

    /**
     * Redirect to the user dashboard with an error message.
     * @param mixed $request
     * @param string $localeKey
     * @return void
     */
    private function _redirectWithError($request, string $localeKey): void {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = $request->getUser();
        
        if ($user) {
            $notificationManager->createTrivialNotification(
                (int) $user->getId(), 
                NOTIFICATION_TYPE_ERROR, 
                ['contents' => __($localeKey)]
            );
        }
        $request->redirect(null, 'user', 'index');
        exit;
    }

}
?>