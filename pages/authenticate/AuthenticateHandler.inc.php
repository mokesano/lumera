<?php
declare(strict_types=1);

/**
 * @file pages/authenticate/AuthenticateHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
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
import('lib.wizdam.classes.services.PublisherProfileService');
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
            [
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON,
                LOCALE_COMPONENT_APP_PAYMENT
            ]
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
            // [BARU] Identitas resmi Penerbit -- untuk letterhead logo/warna.
            'publisher' => (new PublisherProfileService())->getProfile(),
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
            // [BARU] Identitas resmi Penerbit -- untuk letterhead logo/warna.
            'publisher' => (new PublisherProfileService())->getProfile(),
            'pageTitle' => 'authenticate.invoice.verifiedTitle'
        ]);
        
        $templateMgr->display('authenticate/invoicePublic.tpl');
    }

    /**
     * Endpoint untuk memverifikasi keabsahan Sertifikat (Reviewer/Editor)
     * URL Contoh: /authenticate/certificate/reviewer/[hash]-[reviewId]
     *             /authenticate/certificate/editor/[hash]-[editId]
     * @param array $args [0] = tipe ('reviewer'|'editor'), [1] = param keamanan
     * @param Request|null $request
     */
    public function certificate(array $args, $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        $type = strtolower((string) ($args[0] ?? ''));
        if (!in_array($type, ['reviewer', 'editor'], true)) {
            $this->_renderPublicError($templateMgr, 'authenticate.error.malformedUrl');
            return;
        }

        $param = $args[1] ?? '';
        if (strlen($param) <= 65 || ($param[64] ?? '') !== '-') {
            $this->_renderPublicError($templateMgr, 'authenticate.error.malformedUrl');
            return;
        }

        $providedHash = substr($param, 0, 64);
        $id = (int) substr($param, 65);
        $hashScope = $type === 'editor' ? 'certificate_editor' : 'certificate';

        if (!$this->securityHashService->validateHash($hashScope, $id, $providedHash)) {
            $this->_renderPublicError($templateMgr, 'authenticate.error.hashValidationFailed');
            return;
        }

        $certService = new CertificateService();
        
        try {
            $certData = $type === 'reviewer'
                ? $certService->getReviewerCertificateData($id)
                : $certService->getEditorCertificateData($id);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if ($msg === 'INCOMPLETE_REVIEW' || $msg === 'INCOMPLETE_ASSIGNMENT') {
                $this->_renderPublicError($templateMgr, 'authenticate.cert.incompleteReview');
            } else {
                $this->_renderPublicError($templateMgr, 'document.cert.notFound');
            }
            return;
        }

        $templateMgr->assign([
            'certData' => $certData,
            'isVerified' => true,
            // [BARU] Identitas resmi Penerbit -- untuk letterhead logo/warna.
            'publisher' => (new PublisherProfileService())->getProfile(),
            'pageTitle' => 'authenticate.cert.verifiedTitle'
        ]);
        
        $templateMgr->display('authenticate/certificatePublic.tpl');
    }

    /**
     * Helper Privat: Merender halaman error publik dengan rapi.
     * @param TemplateManager $templateMgr
     * @param string $messageLocaleKey
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