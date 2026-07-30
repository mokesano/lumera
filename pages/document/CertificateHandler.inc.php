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
 * @brief Handler untuk menampilkan dan mengunduh Sertifikat Reviewer & Editor.
 * Sertifikat TIDAK diterbitkan untuk Author -- Author memakai LoA (LoAHandler).
 * Terintegrasi dengan Smart Router, SecurityHashService, dan Ownership Validation.
 */

import('classes.handler.Handler');
import('lib.wizdam.classes.services.CertificateService');
import('lib.wizdam.classes.services.PdfService');
import('lib.wizdam.classes.services.QrCodeService');
import('lib.wizdam.classes.services.PublisherProfileService');
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
            LOCALE_COMPONENT_APPLICATION_COMMON,
            LOCALE_COMPONENT_APP_PAYMENT
        ]);
    }

    /**
     * SMART ROUTER: Menampilkan HTML atau mengunduh PDF Sertifikat.
     * Rute HTML: /document/certificate/reviewer/[hash]-[reviewId]
     *            /document/certificate/editor/[hash]-[editId]
     * Rute PDF:  /document/certificate/reviewer/pdf-[hash]-[reviewId]
     *            /document/certificate/editor/pdf-[hash]-[editId]
     *
     * [FIX KRITIS] Method ini SEBELUMNYA bernama index() -- dispatcher
     * pages/document/index.php meneruskan op 'certificate' langsung sebagai
     * nama method yang dipanggil router (tanpa fallback __call()), jadi
     * method bernama index() TIDAK PERNAH tercapai lewat rute ini -- selalu
     * 404. Sudah diganti jadi certificate().
     * @param array $args [0] = tipe ('reviewer'|'editor'), [1] = param keamanan
     * @param mixed $request
     */
    public function certificate(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $user = $request->getUser();

        $type = strtolower((string) ($args[0] ?? ''));
        if (!in_array($type, ['reviewer', 'editor'], true)) {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $param = $args[1] ?? '';
        if ($param === '') {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $isPdf = str_starts_with($param, 'pdf-');
        $cleanParam = $isPdf ? substr($param, 4) : $param;

        if (strlen($cleanParam) <= 65 || ($cleanParam[64] ?? '') !== '-') {
            $this->_redirectWithError($request, 'document.cert.invalidRequest');
        }

        $providedHash = substr($cleanParam, 0, 64);
        $id = (int) substr($cleanParam, 65);

        // [PENTING] scope hash berbeda untuk reviewer vs editor -- mencegah
        // hash sertifikat reviewer #5 dipakai untuk memvalidasi sertifikat
        // editor #5 (dua entitas ID berbeda yang kebetulan bernilai sama).
        $hashScope = $type === 'editor' ? 'certificate_editor' : 'certificate';

        if (!$this->securityHashService->validateHash($hashScope, $id, $providedHash)) {
            $this->_redirectWithError($request, 'document.error.hashValidationFailed');
        }

        // Ownership Validation
        if ($type === 'reviewer') {
            /** @var ReviewAssignmentDAO $reviewAssignmentDao */
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $assignment = $reviewAssignmentDao->getById($id);
            if (!$assignment || (int) $assignment->getReviewerId() !== (int) $user->getId()) {
                $this->_redirectWithError($request, 'document.cert.unauthorized');
            }
        } else {
            /** @var EditAssignmentDAO $editAssignmentDao */
            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $assignment = $editAssignmentDao->getEditAssignment($id);
            if (!$assignment || $assignment->getEditorId() !== (int) $user->getId()) {
                $this->_redirectWithError($request, 'document.cert.unauthorized');
            }
        }

        // Business Logic
        $certData = null;
        try {
            $certData = $type === 'reviewer'
                ? $this->certService->getReviewerCertificateData($id)
                : $this->certService->getEditorCertificateData($id);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'INCOMPLETE_REVIEW' || $e->getMessage() === 'INCOMPLETE_ASSIGNMENT') {
                $this->_redirectWithError($request, 'document.cert.incompleteReview');
            } else {
                $this->_redirectWithError($request, 'document.cert.notFound');
            }
        }

        // QR Code Autentikasi Publik
        $authHash = $this->securityHashService->generateHash($hashScope, $id);
        $authenticateUrl = $request->url(null, 'authenticate', 'certificate', [$type, "{$authHash}-{$id}"]);
        
        $qrService = new QrCodeService();
        $qrCodeBase64 = $qrService->generateBase64($authenticateUrl);

        if ($isPdf) {
            $pdfService = new PdfService();
            $pdfService->generateCertificatePdf($certData, $qrCodeBase64);
        } else {
            $templateMgr = TemplateManager::getManager($request);
            $pdfDownloadUrl = $request->url(null, 'document', 'certificate', [$type, "pdf-{$providedHash}-{$id}"]);

            $templateMgr->assign([
                'certData' => $certData,
                'qrCodeImage' => $qrCodeBase64,
                'pdfDownloadUrl' => $pdfDownloadUrl,
                // [BARU] Identitas resmi Penerbit -- untuk letterhead logo/warna.
                'publisher' => (new PublisherProfileService())->getProfile(),
                'pageTitle' => 'document.cert.pageTitle',
                'pageHierarchy' => [[$request->url(null, 'user'), 'navigation.user']]
            ]);

            $templateMgr->display('document/certificate/certificatePrivate.tpl');
        }
    }

    /**
     * [BARU] Halaman indeks -- daftar semua Sertifikat (Reviewer+Editor) milik pengguna.
     * Rute: /document/certificateIndex
     * @param array $args
     * @param Request|null $request
     */
    public function certificateIndex(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        $this->setupTemplate($request);
        $user = $request->getUser();

        $entries = $this->certService->getCertificateIndexForUser((int) $user->getId());

        foreach ($entries as &$entry) {
            $scope = $entry['type'] === 'editor' ? 'certificate_editor' : 'certificate';
            $hash = $this->securityHashService->generateHash($scope, $entry['id']);
            $entry['detailUrl'] = $request->url(null, 'document', 'certificate', [$entry['type'], "{$hash}-{$entry['id']}"]);
            $entry['pdfUrl'] = $request->url(null, 'document', 'certificate', [$entry['type'], "pdf-{$hash}-{$entry['id']}"]);
        }
        unset($entry);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'certificateEntries' => $entries,
            'pageTitle' => 'document.cert.indexTitle',
            'pageHierarchy' => [[$request->url(null, 'document', 'index'), 'document.index.pageTitle']]
        ]);
        $templateMgr->display('document/certificate/certificateIndex.tpl');
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