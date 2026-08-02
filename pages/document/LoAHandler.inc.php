<?php
declare(strict_types=1);

/**
 * @file pages/document/LoAHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
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
import('lib.wizdam.classes.services.PublisherProfileService');
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
     * @param \PKPRequest|null $request
     */
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
     * SMART ROUTER: Menampilkan Web View (HTML) atau Mengunduh PDF dari LoA Privat.
     * Rute HTML: /document/loa/[hash]-[submissionId]
     * Rute PDF:  /document/loa/pdf-[hash]-[submissionId]
     *
     * [FIX KRITIS] Method ini SEBELUMNYA bernama index() -- karena dispatcher
     * pages/document/index.php meneruskan op 'loa' langsung sebagai nama
     * method yang dipanggil (call_user_func([$handler, $op], ...) di
     * PKPPageRouter, tanpa fallback __call()), method bernama index() TIDAK
     * PERNAH bisa dipanggil lewat rute ini -- selalu 404. Sudah diganti
     * jadi loa() supaya benar-benar tercapai.
     * @param array $args
     * @param Request|null $request
     */
    public function loa(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request);
        $user = $request->getUser();

        $param = $args[0] ?? '';
        if (empty($param)) {
            $this->_redirectWithError($request, 'billing.error.malformedRequest');
        }

        // 1. Deteksi Mode & Ekstraksi String
        $isPdf = str_starts_with($param, 'pdf-');
        $cleanParam = $isPdf ? substr($param, 4) : $param;

        // Validasi struktur hash (64 char + '-' + ID)
        if (strlen($cleanParam) <= 65 || $cleanParam[64] !== '-') {
            $this->_redirectWithError($request, 'billing.error.malformedRequest');
        }

        $providedHash = substr($cleanParam, 0, 64);
        $submissionId = (int) substr($cleanParam, 65);

        // 2. Validasi Keamanan URL (Mencegah Tampering)
        if (!$this->securityHashService->validateHash('loa', $submissionId, $providedHash)) {
            $this->_redirectWithError($request, 'billing.error.hashValidationFailed');
        }

        // 3. [Catatan Keamanan Terealisasi] Validasi Kepemilikan (Ownership Check)
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($submissionId);
        
        if (!$article || $article->getUserId() !== (int) $user->getId()) {
            $this->_redirectWithError($request, 'document.loa.unauthorized');
        }

        // 4. Proses Logika Bisnis LoA
        $loaData = $this->loaService->getPublicLoASummary($submissionId);

        if ($loaData['status'] === 'PENDING_PAYMENT') {
            // [UX Fix] Arahkan ke dasbor tagihan aktif dengan notifikasi ramah
            $this->_redirectWithError($request, 'document.loa.pendingPaymentAlert');
        }

        if ($loaData['status'] === 'NOT_YET_ACCEPTED') {
            $this->_redirectWithError($request, 'document.loa.notYetAccepted');
        }

        if ($loaData['status'] === 'NOT_FOUND') {
            $this->_redirectWithError($request, 'document.loa.notFound');
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
            
            // [FIX] Rute sebelumnya salah: page="billing" op="loa" TIDAK ADA
            // di dispatcher manapun. Halaman ini live di page="document"
            // op="loa" (LoAHandler sendiri) -- diperbaiki supaya tombol
            // Download PDF di halaman HTML benar-benar mengarah ke rute yang hidup.
            $pdfDownloadUrl = $request->url(null, 'document', 'loa', ["pdf-{$providedHash}-{$submissionId}"]);

            $templateMgr->assign([
                'loaData' => $loaData,
                'qrCodeImage' => $qrCodeBase64,
                'submissionId' => $submissionId,
                'pdfDownloadUrl' => $pdfDownloadUrl,
                // [BARU] Identitas resmi Penerbit -- untuk letterhead logo/warna.
                'publisher' => (new PublisherProfileService())->getProfile(),
                'pageTitle' => 'document.loa.pageTitle',
                'pageHierarchy' => [
                    [$request->url(null, 'user'), 'navigation.user'],
                    [$request->url(null, 'billing', 'index'), 'billing.globalBilling']
                ]
            ]);

            $templateMgr->display('document/loa/loaPrivate.tpl');
        }
    }

    /**
     * [BARU] Halaman indeks -- daftar semua naskah penulis yang punya LoA.
     * Rute: /document/loaIndex
     * @param array $args
     * @param Request|null $request
     */
    public function loaIndex(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $user = $request->getUser();

        $entries = $this->loaService->getLoAIndexForUser((int) $user->getId());

        foreach ($entries as &$entry) {
            $hash = $this->securityHashService->generateHash('loa', (int) $entry['submissionId']);
            $entry['detailUrl'] = $request->url(null, 'document', 'loa', ["{$hash}-{$entry['submissionId']}"]);
            $entry['pdfUrl'] = $request->url(null, 'document', 'loa', ["pdf-{$hash}-{$entry['submissionId']}"]);
        }
        unset($entry);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'loaEntries' => $entries,
            'pageTitle' => 'billing.loa.indexTitle',
            'pageHierarchy' => [[$request->url(null, 'document', 'index'), 'document.index.pageTitle']]
        ]);
        $templateMgr->display('document/loa/loaIndex.tpl');
    }

    /**
     * HELPER: Mengalihkan pengguna kembali dengan Notifikasi Error.
     * @param Request $request
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
        
        // Kembalikan pengguna ke dasbor author/user, bukan ke billing
        $request->redirect(null, 'user', 'index');
        exit;
    }

}
?>