<?php
declare(strict_types=1);

/**
 * @file pages/document/LoAHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class LoAHandler
 * @ingroup pages_document
 *
 * @brief Handler untuk menampilkan dan mengunduh LoA privat bagi Penulis.
 * Terintegrasi dengan Smart Router, SecurityHashService, dan Ownership Validation.
 */

import('classes.handler.Handler');

// Memanggil WIZDAM Services dari folder semantik
import('lib.wizdam.classes.services.LoAService');
import('lib.wizdam.classes.services.PdfService');
import('lib.wizdam.classes.services.QrCodeService');
import('lib.wizdam.classes.security.SecurityHashService');

class LoAHandler extends Handler {
    
    /** @var LoAService */
    private LoAService $loaService;

    /** @var SecurityHashService */
    private SecurityHashService $securityHashService;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Mewajibkan otentikasi login
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));

        $this->loaService = new LoAService();
        $this->securityHashService = new SecurityHashService();
    }

    /**
     * Memuat dependensi antarmuka dan Locale
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
     * SMART ROUTER: Menampilkan Web View (HTML) atau Mengunduh PDF dari LoA Privat.
     * Rute HTML: /document/loa/[hash]-[submissionId]
     * Rute PDF:  /document/loa/pdf-[hash]-[submissionId]
     * @param array $args Argumen URL berformat hash
     * @param Request|null $request
     */
    public function index(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request);
        $user = $request->getUser();

        $param = $args[0] ?? '';
        if (empty($param)) {
            $this->_redirectWithError($request, 'billing.loa.invalidRequest');
        }

        // 1. Deteksi Mode & Ekstraksi String
        $isPdf = str_starts_with($param, 'pdf-');
        $cleanParam = $isPdf ? substr($param, 4) : $param;

        // Validasi struktur hash (64 char + '-' + ID)
        if (strlen($cleanParam) <= 65 || $cleanParam[64] !== '-') {
            $this->_redirectWithError($request, 'billing.loa.invalidRequest');
        }

        $providedHash = substr($cleanParam, 0, 64);
        $submissionId = (int) substr($cleanParam, 65);

        // 2. Validasi Keamanan URL (Mencegah Tampering)
        if (!$this->securityHashService->validateHash('loa', $submissionId, $providedHash)) {
            $this->_redirectWithError($request, 'billing.error.hashValidationFailed');
        }

        // 3. [Catatan Keamanan Terealisasi] Validasi Kepemilikan (Ownership Check)
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($submissionId);
        
        if (!$article || $article->getUserId() !== (int) $user->getId()) {
            $this->_redirectWithError($request, 'billing.loa.unauthorized');
        }

        // 4. Proses Logika Bisnis LoA
        $loaData = $this->loaService->getPublicLoASummary($submissionId);

        if ($loaData['status'] === 'PENDING_PAYMENT') {
            // [UX Fix] Arahkan ke dasbor tagihan aktif dengan notifikasi ramah
            $this->_redirectWithError($request, 'billing.loa.pendingPaymentAlert', 'index');
        }

        if ($loaData['status'] === 'NOT_FOUND') {
            $this->_redirectWithError($request, 'billing.loa.notFound');
        }

        // 5. Generate QR Code untuk Autentikasi Publik
        $authHash = $this->securityHashService->generateHash('loa', $submissionId);
        $authenticateUrl = $request->url(null, 'authenticate', 'loa', ["{$authHash}-{$submissionId}"]);
        
        $qrService = new QrCodeService();
        $qrCodeBase64 = $qrService->generateBase64($authenticateUrl);

        // 6. Eksekusi Berdasarkan Mode (HTML vs PDF)
        if ($isPdf) {
            $pdfService = new PdfService();
            // File PDF akan langsung terunduh/tampil di browser dengan Digital Signature (jika ada)
            $pdfService->generateLoAPdf($loaData, $qrCodeBase64);
        } else {
            // Render HTML
            $templateMgr = TemplateManager::getManager($request);
            
            // Build URL untuk tombol "Download PDF" dengan mewariskan Hash
            $pdfDownloadUrl = $request->url(null, 'billing', 'loa', ["pdf-{$providedHash}-{$submissionId}"]);

            $templateMgr->assign([
                'loaData' => $loaData,
                'qrCodeImage' => $qrCodeBase64,
                'submissionId' => $submissionId,
                'pdfDownloadUrl' => $pdfDownloadUrl,
                'pageTitle' => 'billing.loa.pageTitle',
                'pageHierarchy' => [
                    [$request->url(null, 'user'), 'navigation.user'],
                    [$request->url(null, 'billing', 'index'), 'billing.globalBilling']
                ]
            ]);

            // Pastikan Anda memindahkan template private.tpl ke direktori billing/loa/
            $templateMgr->display('billing/loa/private.tpl');
        }
    }

    /**
     * HELPER: Mengalihkan pengguna kembali dengan Notifikasi Error.
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
        
        // Kembalikan pengguna ke dasbor author/user, bukan ke billing
        $request->redirect(null, 'user', 'index');
        exit;
    }
}
?>