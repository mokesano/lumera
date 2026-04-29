<?php
declare(strict_types=1);

/**
 * @file pages/document/CertificateHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class CertificateHandler
 * @ingroup pages_document
 *
 * @brief Handler untuk menampilkan dan mengunduh Sertifikat (Reviewer/Author).
 * Terintegrasi dengan Smart Router, SecurityHashService, dan Ownership Validation.
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
     * Constructor
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
     * Setup the template
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents(
            array(
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON
            )
        );
    }

    /**
     * SMART ROUTER: Menampilkan HTML atau Mengunduh PDF dari Sertifikat.
     * Rute HTML: /document/certificate/[hash]-[reviewId]
     * Rute PDF:  /document/certificate/pdf-[hash]-[reviewId]
     * Validasi Keamanan URL, Ownership Validation, dan Logika Bisnis Terintegrasi.
     * @param array $args URL arguments (mengandung hash dan reviewId)
     * @param Request|null $request HTTP request object
     */
    public function index(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request);
        $user = $request->getUser();

        $param = $args[0] ?? '';
        if (empty($param)) {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $isPdf = str_starts_with($param, 'pdf-');
        $cleanParam = $isPdf ? substr($param, 4) : $param;

        if (strlen($cleanParam) <= 65 || $cleanParam[64] !== '-') {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $providedHash = substr($cleanParam, 0, 64);
        $reviewId = (int) substr($cleanParam, 65);

        // 1. Validasi Keamanan URL
        if (!$this->securityHashService->validateHash('certificate', $reviewId, $providedHash)) {
            $this->_redirectWithError($request, 'document.error.hashValidationFailed');
        }

        // 2. Ownership Validation (Hanya Reviewer yang bersangkutan yang boleh unduh)
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        
        if (!$reviewAssignment || $reviewAssignment->getReviewerId() !== (int) $user->getId()) {
            $this->_redirectWithError($request, 'document.cert.unauthorized');
        }

        // 3. Proses Logika Bisnis
        try {
            $certData = $this->certService->getReviewerCertificateData($reviewId);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'INCOMPLETE_REVIEW') {
                $this->_redirectWithError($request, 'document.cert.incompleteReview');
            } else {
                $this->_redirectWithError($request, 'document.cert.notFound');
            }
        }

        // 4. Generate QR Code untuk Autentikasi Publik
        $authHash = $this->securityHashService->generateHash('certificate', $reviewId);
        $authenticateUrl = $request->url(null, 'authenticate', 'certificate', ["{$authHash}-{$reviewId}"]);
        
        $qrService = new QrCodeService();
        $qrCodeBase64 = $qrService->generateBase64($authenticateUrl);

        // 5. Eksekusi PDF atau HTML
        if ($isPdf) {
            $pdfService = new PdfService();
            // Asumsi Anda akan menambahkan fungsi generateCertificatePdf di PdfService
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
     * Redirect to the user dashboard with an error message
     * @param Request $request The HTTP request object
     * @param string $localeKey The locale key for the error message
     */
    private function _redirectWithError($request, string $localeKey): void {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = $request->getUser();
        
        if ($user) {
            $notificationManager->createTrivialNotification(
                $user->getId(), NOTIFICATION_TYPE_ERROR, ['contents' => __($localeKey)]
            );
        }
        $request->redirect(null, 'user', 'index');
        exit;
    }
}
?>