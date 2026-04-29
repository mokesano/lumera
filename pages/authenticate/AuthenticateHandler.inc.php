<?php
declare(strict_types=1);

/**
 * @file pages/authenticate/AuthenticateHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class AuthenticateHandler
 * 
 * @brief Handler Publik untuk memverifikasi keabsahan LoA, Sertifikat, dan Invoice.
 * Terlindungi oleh SecurityHashService (SHA-256). Tidak membutuhkan otentikasi login.
 */

import('classes.handler.Handler');

// [WIZDAM BRIDGE] Memanggil WIZDAM Services
import('lib.wizdam.classes.services.LoAService');
import('lib.wizdam.classes.services.InvoiceService');
import('lib.wizdam.classes.services.CertificateService'); // Layanan sertifikat
import('lib.wizdam.classes.security.SecurityHashService');

class AuthenticateHandler extends Handler {

    /** @var SecurityHashService */
    private SecurityHashService $securityHashService;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        // Tidak ada HandlerValidator otentikasi di sini, ini adalah halaman PUBLIK.
        $this->securityHashService = new SecurityHashService();
    }

    /**
     * Memuat dependensi antarmuka dan Locale untuk halaman publik
     * @param Request|null $request
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents(
            array(
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON,
                LOCALE_COMPONENT_APP_PAYMENT
            )
        );
    }

    /**
     * Fallback utama jika URL tidak lengkap.
     * URL Contoh: /authenticate/ atau /authenticate/index
     * Hanya merender halaman informasi umum atau redirect ke homepage.
     * @param array $args
     * @param Request|null $request
     */
    public function index(array $args = [], $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
        $request->redirect(null, 'index');
    }

    /**
     * Endpoint untuk memverifikasi keabsahan Letter of Acceptance (LoA)
     * URL Contoh: /authenticate/loa/[hash]-[submissionId]
     * @param array $args
     * @param Request|null $request
     */
    public function loa(array $args, $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        $param = $args[0] ?? '';
        if (strlen($param) <= 65 || $param[64] !== '-') {
            $this->_renderPublicError($templateMgr, 'authenticate.error.malformedUrl');
            return;
        }

        $providedHash = substr($param, 0, 64);
        $submissionId = (int) substr($param, 65);

        if (!$this->securityHashService->validateHash('loa', $submissionId, $providedHash)) {
            $this->_renderPublicError($templateMgr, 'authenticate.error.hashValidationFailed');
            return;
        }
        
        $loaService = new LoAService();
        $loaData = $loaService->getPublicLoASummary($submissionId);

        if ($loaData['status'] === 'PENDING_PAYMENT') {
            $templateMgr->assign('pageTitle', 'authenticate.loa.title');
            $templateMgr->assign('message', 'document.loa.pendingPaymentAlert');
            $templateMgr->display('authenticate/loaPending.tpl');
            return;
        }

        if ($loaData['status'] === 'NOT_FOUND') {
            $this->_renderPublicError($templateMgr, 'document.loa.notFound');
            return;
        }

        $templateMgr->assign([
            'loaData' => $loaData,
            'isVerified' => true,
            'pageTitle' => 'authenticate.loa.verifiedTitle'
        ]);
        
        $templateMgr->display('authenticate/loaPublic.tpl');
    }

    /**
     * Endpoint untuk memverifikasi keabsahan Invoice/Kuitansi
     * URL Contoh: /authenticate/invoice/[hash]-[invoiceId]
     * @param array $args
     * @param Request|null $request
     */
    public function invoice(array $args, $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        $param = $args[0] ?? '';
        if (strlen($param) <= 65 || $param[64] !== '-') {
            $this->_renderPublicError($templateMgr, 'authenticate.error.malformedUrl');
            return;
        }

        $providedHash = substr($param, 0, 64);
        $invoiceId = (int) substr($param, 65);

        if (!$this->securityHashService->validateHash('invoice', $invoiceId, $providedHash)) {
            $this->_renderPublicError($templateMgr, 'authenticate.error.hashValidationFailed');
            return;
        }

        $invoiceService = new InvoiceService();
        $invoice = $invoiceService->getInvoiceById($invoiceId);

        if (!$invoice) {
            $this->_renderPublicError($templateMgr, 'checkout.invoice.notFound');
            return;
        }

        $templateMgr->assign([
            'invoice' => $invoice,
            'isVerified' => true,
            'pageTitle' => 'authenticate.invoice.verifiedTitle'
        ]);
        
        $templateMgr->display('authenticate/invoicePublic.tpl');
    }

    /**
     * Endpoint untuk memverifikasi keabsahan Sertifikat (Reviewer/Author)
     * URL Contoh: /authenticate/certificate/[hash]-[reviewId]
     * @param array $args
     * @param Request|null $request
     */
    public function certificate(array $args, $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        // 1. Ekstraksi dan Validasi Hash
        $param = $args[0] ?? '';
        if (strlen($param) <= 65 || $param[64] !== '-') {
            $this->_renderPublicError($templateMgr, 'authenticate.error.malformedUrl');
            return;
        }

        $providedHash = substr($param, 0, 64);
        $reviewId = (int) substr($param, 65);

        // 2. Verifikasi Kriptografi URL
        if (!$this->securityHashService->validateHash('certificate', $reviewId, $providedHash)) {
            $this->_renderPublicError($templateMgr, 'authenticate.error.hashValidationFailed');
            return;
        }

        // 3. Proses Logika Sertifikat
        $certService = new CertificateService();
        
        try {
            $certData = $certService->getReviewerCertificateData($reviewId);
        } catch (\Exception $e) {
            // Tangkap error secara spesifik dari Service
            if ($e->getMessage() === 'INCOMPLETE_REVIEW') {
                $this->_renderPublicError($templateMgr, 'authenticate.cert.incompleteReview');
            } else {
                $this->_renderPublicError($templateMgr, 'document.cert.notFound');
            }
            return;
        }

        // 4. Render Halaman Publik
        $templateMgr->assign([
            'certData' => $certData,
            'isVerified' => true,
            'pageTitle' => 'authenticate.cert.verifiedTitle'
        ]);
        
        $templateMgr->display('authenticate/certificatePublic.tpl');
    }

    /**
     * Helper Privat: Merender halaman error publik dengan rapi.
     * @param TemplateManager $templateMgr
     * @param string $messageLocaleKey Kunci locale untuk pesan error
     */
    private function _renderPublicError(TemplateManager $templateMgr, string $messageLocaleKey): void {
        $templateMgr->assign([
            'pageTitle' => 'authenticate.error.title',
            'message' => $messageLocaleKey,
            'backLink' => Application::get()->getRequest()->url(null, 'index'),
            'backLinkLabel' => 'navigation.home'
        ]);
        $templateMgr->display('common/message.tpl');
    }
}
?>