<?php
declare(strict_types=1);

/**
 * @file pages/billing/BillingHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class BillingHandler
 * @ingroup pages_billing
 *
 * @brief Pusat Kendali Finansial (B2B) untuk pengguna (Author/Reviewer). 
 * Handler bertanggung jawab menampilkan daftar tagihan, merender rincian tagihan (HTML/PDF)
 * melalui Smart Router, serta menangani antarmuka pembayaran dan pembatalan
 * dengan validasi keamanan SHA-256 yang disediakan oleh SecurityHashService.
 */

import('classes.handler.Handler');

// Mengimpor Service Layer Wizdam Frontedge
import('lib.wizdam.classes.services.InvoiceService');
import('lib.wizdam.classes.services.QrCodeService');
import('lib.wizdam.classes.services.PdfService');
import('lib.wizdam.classes.services.PaymentSettingsService');
import('lib.wizdam.classes.security.SecurityHashService');

class BillingHandler extends Handler {
    
    /** @var InvoiceService Layanan manipulasi data entitas Invoice */
    private InvoiceService $invoiceService;

    /** @var SecurityHashService Layanan pembuatan dan validasi hash URL */
    private SecurityHashService $securityHashService;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Memastikan hanya pengguna yang login dapat mengakses domain billing
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));

        // Inisialisasi Service Layer
        $this->invoiceService = new InvoiceService();
        $this->securityHashService = new SecurityHashService();
    }

    /**
     * Memuat dependensi antarmuka dan Locale
     * @param Request|null $request
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents(
            [
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON,
                LOCALE_COMPONENT_APP_MANAGER,
                LOCALE_COMPONENT_APP_PAYMENT
            ]
        );
    }

    /**
     * Menampilkan Dasbor Tagihan Aktif (UNPAID/PENDING).
     * Rute: GET /billing
     * @param array $args
     * @param Request|null $request
     */
    public function index(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();
        
        if (!$request) $request = Application::get()->getRequest();
        $user = $request->getUser();

        $invoices = $this->invoiceService->getUserInvoices((int) $user->getId(), 'active');

        // [FIX] Sisipkan label fee type yang sudah diterjemahkan, supaya
        // billing/index.tpl tidak lagi mencetak enum mentah (PUBLICATION/7/dst).
        foreach ($invoices as $invoice) {
            $invoice->setData('localizedFeeType', $this->invoiceService->getLocalizedFeeName($invoice->getFeeType()));
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'invoices' => $invoices,
            'pageTitle' => 'billing.activeInvoices', 
            'activeTab' => 'pending',
            'hashService' => $this->securityHashService
        ]);

        $templateMgr->display('billing/index.tpl');
    }

    /**
     * Menampilkan Arsip Tagihan (PAID/VOID/EXPIRED).
     * Rute: GET /billing/history
     * @param array $args Parameter
     * @param Request|null $request
     */
    public function history(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();
        
        if (!$request) $request = Application::get()->getRequest();
        $user = $request->getUser();

        $invoices = $this->invoiceService->getUserInvoices((int) $user->getId(), 'history');

        // [FIX] Sama seperti index() di atas.
        foreach ($invoices as $invoice) {
            $invoice->setData('localizedFeeType', $this->invoiceService->getLocalizedFeeName($invoice->getFeeType()));
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'invoices' => $invoices,
            'pageTitle' => 'billing.historyInvoices',
            'activeTab' => 'history',
            'hashService' => $this->securityHashService
        ]);

        $templateMgr->display('billing/history.tpl');
    }

    /**
     * SMART ROUTER
     * Menangani Web View (HTML) dan Download (PDF) berdasarkan prefix parameter
     * Rute HTML: GET /billing/invoice/[hash]-[id]
     * Rute PDF:  GET /billing/invoice/pdf-[hash]-[id]
     * @param array $args Harus berisi format: [hash]-[id] atau pdf-[hash]-[id]
     * @param Request|null $request
     */
    public function invoice(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request);
        
        $param = $args[0] ?? '';
        if (empty($param)) {
            $request->redirect(null, 'billing');
            return;
        }

        $isPdf = str_starts_with($param, 'pdf-');
        $cleanParam = $isPdf ? substr($param, 4) : $param;

        // Poin 1 & 5: Alihkan dengan notifikasi error, bukan "die()"
        if (strlen($cleanParam) <= 65 || $cleanParam[64] !== '-') {
            $this->_redirectWithError($request, 'billing.error.malformedRequest');
        }

        $providedHash = substr($cleanParam, 0, 64);
        $invoiceId = (int) substr($cleanParam, 65);

        if (!$this->securityHashService->validateHash('invoice', $invoiceId, $providedHash)) {
            $this->_redirectWithError($request, 'billing.error.hashValidationFailed');
        }

        $user = $request->getUser();
        $invoice = $this->invoiceService->getInvoiceById($invoiceId);

        if (!$invoice || $invoice->getUserId() !== (int) $user->getId()) {
            $this->_redirectWithError($request, 'billing.error.invoiceNotFound');
        }

        $authHash = $this->securityHashService->generateHash('invoice', $invoiceId);
        $authenticateUrl = $request->url(null, 'authenticate', 'invoice', ["{$authHash}-{$invoiceId}"]);
        
        $qrService = new QrCodeService();
        $qrCodeBase64 = $qrService->generateBase64($authenticateUrl);

        if ($isPdf) {
            $this->_handlePdfDownload($invoice, $qrCodeBase64, $request);
        } else {
            $this->_handleHtmlView($invoice, $qrCodeBase64, $providedHash, $request);
        }
    }

    /**
     * Memproses permintaan inisiasi pembayaran ke Payment Gateway (AJAX/POST).
     * Rute: POST /billing/pay/[hash]-[id]
     * @param array $args Harus berisi format keamanan: [hash]-[id]
     * @param Request|null $request
     */
    public function pay(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();

        $paymentType = $request->getUserVar('payment_type') ?: 'all';

        import('lib.pkp.classes.validation.ValidatorCSRF');
        if ($request->isPost()) {
            if (!ValidatorCSRF::checkToken($request->getUserVar('csrfToken'))) {
                $this->_sendJsonResponse($request, 'error', __('billing.error.csrfInvalid'));
            }
        } else {
            $this->_sendJsonResponse($request, 'error', __('billing.error.methodNotAllowed'));
        }

        $param = $args[0] ?? '';
        if (strlen($param) <= 65 || $param[64] !== '-') {
            $this->_sendJsonResponse($request, 'error', __('billing.error.malformedRequest'));
        }
        
        $providedHash = substr($param, 0, 64);
        $invoiceId = (int) substr($param, 65);

        if (!$this->securityHashService->validateHash('invoice', $invoiceId, $providedHash)) {
            $this->_sendJsonResponse($request, 'error', __('billing.error.hashValidationFailed'));
        }

        $user = $request->getUser();
        $invoice = $this->invoiceService->getInvoiceById($invoiceId);

        if (!$invoice || $invoice->getUserId() !== (int) $user->getId()) {
            $this->_sendJsonResponse($request, 'error', __('billing.error.invoiceNotFound'));
        }

        if ($invoice->getStatus() === 'PAID') {
            $this->_sendJsonResponse($request, 'error', __('billing.error.alreadyPaid'));
        }

        $settingsService = new PaymentSettingsService();
        $activeGatewayStr = $settingsService->getActiveGateway();

        // Factory Pattern
        if ($activeGatewayStr === 'xendit') {
            import('lib.wizdam.classes.payment.XenditGateway');
            $gateway = new XenditGateway($settingsService->getXenditApiKey());
        } else {
            import('lib.wizdam.classes.payment.MidtransGateway');
            $gateway = new MidtransGateway(
                $settingsService->getMidtransServerKey(), 
                $settingsService->isProduction()
            );
        }

        $customerData = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail()
        ];

        try {
            $checkoutData = $gateway->getPaymentCheckoutData($invoice, $customerData, $paymentType);
            $this->_sendJsonResponse($request, 'success', __('billing.success.paymentSessionCreated'), $checkoutData);
        } catch (\Throwable $e) {
            // Gunakan message bawaan gateway untuk debugging, atau bungkus dengan locale error general
            $this->_sendJsonResponse($request, 'error', __('billing.error.gatewayFailed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Memproses pembatalan tagihan oleh pengguna.
     * Rute: POST/GET /billing/cancel/[hash]-[id]
     * @param array $args Harus berisi format keamanan: [hash]-[id]
     * @param Request|null $request
     */
    public function cancel(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $param = $args[0] ?? '';
        if (strlen($param) <= 65 || $param[64] !== '-') {
            $this->_redirectWithError($request, 'billing.error.malformedRequest');
        }
        
        $providedHash = substr($param, 0, 64);
        $invoiceId = (int) substr($param, 65);

        if (!$this->securityHashService->validateHash('invoice', $invoiceId, $providedHash)) {
            $this->_redirectWithError($request, 'billing.error.hashValidationFailed');
        }
        
        $user = $request->getUser();
        $invoice = $this->invoiceService->getInvoiceById($invoiceId);

        if ($invoice && $invoice->getUserId() === (int) $user->getId() && $invoice->getStatus() !== 'PAID') {
            $success = $this->invoiceService->deleteInvoice($invoice);
            if ($success) {
                // Berhasil dibatalkan, arahkan dengan Trivial Notification Sukses
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification(
                    $user->getId(), 
                    NOTIFICATION_TYPE_SUCCESS, 
                    ['contents' => __('billing.success.invoiceCancelled')]
                );
                $request->redirect(null, 'billing', 'index');
                return;
            }
        }

        $this->_redirectWithError($request, 'billing.error.cannotCancel');
    }

    /**
     * PRIVATE METHOD: Merender antarmuka HTML Detail Tagihan.
     * @param object $invoice
     * @param string $qrCodeBase64
     * @param string $hash
     * @param Request $request
     */
    private function _handleHtmlView(object $invoice, string $qrCodeBase64, string $hash, $request): void {
        $viewData = $this->invoiceService->getInvoiceSummary($invoice);
        
        $invoiceId  = $invoice->getInvoiceId();
        $securePath = "{$hash}-{$invoiceId}";
        $pdfUrl     = $request->url(null, 'billing', 'invoice', ["pdf-{$securePath}"]);

        $viewData['qrCodeImage']    = $qrCodeBase64;
        $viewData['pdfDownloadUrl'] = $pdfUrl;
        $viewData['securePath']     = $securePath;
        $viewData['pageTitle']      = 'billing.invoiceDetail';
        
        $viewData['pageHierarchy'] = [
            [$request->url(null, 'user'), 'navigation.user'],
            [$request->url(null, 'billing', 'index'), 'billing.globalBilling']
        ];

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign($viewData);
        $templateMgr->display('billing/invoice.tpl');
    }

    /**
     * PRIVATE: Memerintahkan PdfService untuk men-generate dan mengunduh PDF.
     * @param object $invoice
     * @param string $qrCodeBase64
     * @param mixed $request
     */
    private function _handlePdfDownload(object $invoice, string $qrCodeBase64, $request): void {
        $pdfService = new PdfService();
        try {
            $pdfService->generateInvoicePdf($invoice, $qrCodeBase64);
        } catch (\Throwable $e) { 
            // Tangkap kegagalan mPDF (seperti memori penuh/path font salah)
            // dan kembalikan pengguna ke index dengan pemberitahuan rapi.
            $this->_redirectWithError($request, 'billing.error.pdfGenerationFailed');
        }
    }

    /**
     * HELPER: Melempar pengguna ke halaman Billing dengan notifikasi Error.
     * @param mixed $request
     * @param string $localeKey
     */
    private function _redirectWithError($request, string $localeKey): void {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = $request->getUser();
        
        if ($user) {
            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_ERROR,
                ['contents' => __($localeKey)]
            );
        }
        
        $request->redirect(null, 'billing', 'index');
        exit; // Pastikan eksekusi script terhenti seketika
    }

    /**
     * HELPER: Standardisasi respons AJAX.
     * @param mixed $request
     * @param string $status
     * @param string $message
     * @param array $data
     */
    private function _sendJsonResponse($request, string $status, string $message, array $data = []): void {
        $isAjax = $request->getUserVar('ajax') == 1;
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
            exit;
        } else {
            // Jika request bukan AJAX tapi masuk ke endpoint yang seharusnya AJAX, alihkan
            if ($status === 'error') {
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $user = $request->getUser();
                if ($user) {
                    $notificationManager->createTrivialNotification(
                        $user->getId(), 
                        NOTIFICATION_TYPE_ERROR, 
                        ['contents' => $message]
                    );
                }
                $request->redirectUrl($data['url'] ?? $request->url(null, 'billing', 'index'));
            } else {
                $request->redirectUrl($data['url'] ?? '');
            }
            exit;
        }
    }

}
?>