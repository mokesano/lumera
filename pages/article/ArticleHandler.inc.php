<?php
declare(strict_types=1);

/**
 * @file pages/article/ArticleHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleHandler
 * @ingroup pages_article
 *
 * @brief Handle requests for article functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.rt.ojs.RTDAO');
import('classes.rt.ojs.JournalRT');
import('classes.handler.Handler');

class ArticleHandler extends Handler {
    
    /** @var Journal|null journal associated with the request */
    public $journal = null;

    /** @var Issue|null issue associated with the request */
    public $issue = null;

    /** @var Article|PublishedArticle|null article associated with the request */
    public $article = null;

    /**
     * Constructor
     */
    public function __construct($request) {
        parent::__construct($request);
        $router = $request->getRouter();

        $this->addCheck(new HandlerValidatorJournal($this));
        
        // [MODERNIZATION] Replaced deprecated create_function with Closure
        $this->addCheck(new HandlerValidatorCustom(
            $this, 
            false, 
            null, 
            null, 
            function($journal) { 
                return $journal->getSetting('publishingMode') != PUBLISHING_MODE_NONE; 
            }, 
            [$router->getContext($request)]
        ));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * View Article.
     * @param array $args
     * @param PKPRequest $request
     */
    public function view($args, $request) {
        $articleIdInput = $args[0] ?? 0;
        $op = $args[1] ?? null;

        // ======== KODE METRICS ========
        if ($op === 'metrics') {
            return $this->metrics($args, $request);
        }
        // ======== AKHIR KODE METRICS ========
        
        $router = $request->getRouter();
        $journal = $request->getJournal();
        $galleyId = $args[1] ?? 0;
        
        // =====================================================================
        // [WIZDAM GUARD] CEK KONTEKS JURNAL
        // =====================================================================
        if (!$journal) {
            // Jika jurnal tidak ditemukan (null), jangan paksa getId().
            // Alihkan ke halaman utama atau berikan 404.
            return $request->redirect(null, 'index');
        }

        $currentJournalId = (int) $journal->getId();

        // =====================================================================
        // [WIZDAM FIX] DETEKSI ID YANG LEBIH CERDAS (NUMERIC PUBLIC ID)
        // =====================================================================
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $articleObj = null;

        // 1. Coba cari pakai cara standar OJS (Best ID)
        // Fungsi otomatis cek: input Int -> Internal ID, String -> Public ID.
        $articleObj = $publishedArticleDao->getPublishedArticleByBestArticleId((int) $journal->getId(), $articleIdInput, true);

        // 2. FALLBACK: Jika gagal, inputnya angka, paksa cari sebagai Public ID
        if (!$articleObj && is_numeric($articleIdInput)) {
            $articleObj = $publishedArticleDao->getPublishedArticleByPubId(
                'publisher-id', 
                (string) $articleIdInput, 
                $currentJournalId
            );
        }

        // 3. RESOLUSI ID: Pastikan $articleId jadi INTERNAL ID (Integer) valid
        if ($articleObj) {
            // Jika artikel ditemukan (baik via Public ID maupun Internal ID),
            // kita ambil ID database aslinya.
            $articleId = (int) $articleObj->getId();
        } else {
            // Jika tidak ketemu, kembalikan ke input awal (nanti akan gagal di validate)
            $articleId = (int) $articleIdInput;
        }
        // =====================================================================
        
        // [WIZDAM FIX] PENGGUNAAN ISSUE DAO YANG AMAN
        $issue = $issueDao->getIssueByArticleId($articleId, $journal->getId());

        // Jalankan validasi dengan Internal ID yang sudah dipastikan Integer
        $this->validate($request, $articleId, $galleyId);
        
        // Setup objek standar OJS
        $journal = $this->journal;
        $issue = $this->issue;
        $article = $this->article;
        $this->setupTemplate($request);

        $rtDao = DAORegistry::getDAO('RTDAO'); /** @var RTDAO $rtDao */
        $journalRt = $rtDao->getJournalRTByJournal($journal);

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId(), $journal->getId(), true);

        // RTVersion sebagai Subject
        $version = null;
        if ($journalRt->getVersion() != null) {
            $version = $rtDao->getVersion($journalRt->getVersion(), $journalRt->getJournalId(), true);
        }

        // Article Galley PDF/HTML/XML
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = null;
        if ($journal->getSetting('enablePublicGalleyId')) {
            $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
        } else {
            $galley = $galleyDao->getGalley($galleyId, $article->getId());
        }

        if ($galley && !$galley->isHtmlGalley() && !$galley->isPdfGalley()) {
            if ($galley->getRemoteURL()) {
                if (!HookRegistry::dispatch('ArticleHandler::viewRemoteGalley', [&$article, &$galley])) {
                    $request->redirectUrl($galley->getRemoteURL());
                }
            }
            if ($galley->isInlineable()) {
                return $this->viewFile(
                    [$galley->getArticleId(), $galley->getId()],
                    $request
                );
            } else {
                return $this->download(
                    [$galley->getArticleId(), $galley->getId()],
                    $request
                );
            }
        }

        $templateMgr = TemplateManager::getManager($request);
        // $templateMgr->addJavaScript('js/relatedItems.js'); // RT
        $templateMgr->addJavaScript('js/inlinePdf.js');
        $templateMgr->addJavaScript('js/pdfobject.js');
        
        $galleys = $galleyDao->getGalleysByArticle($article->getId());
        $templateMgr->assign('galleys', $galleys);
        
        if (!$galley) {
            import('classes.issue.IssueAction');

            if ($issue) {
                $templateMgr->assign('subscriptionRequired', IssueAction::subscriptionRequired($issue));
            }

            $templateMgr->assign('subscribedUser', IssueAction::subscribedUser($journal, isset($issue) ? $issue->getId() : null, isset($article) ? $article->getId() : null));
            $templateMgr->assign('subscribedDomain', IssueAction::subscribedDomain($journal, isset($issue) ? $issue->getId() : null, isset($article) ? $article->getId() : null));

            $templateMgr->assign('showGalleyLinks', $journal->getSetting('showGalleyLinks'));

            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            if ( $paymentManager->onlyPdfEnabled() ) {
                $templateMgr->assign('restrictOnlyPdf', true);
            }
            if ( $paymentManager->purchaseArticleEnabled() ) {
                $templateMgr->assign('purchaseArticleEnabled', true);
            }

            // Article cover page.
            if (isset($article) && $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage() && !$article->getLocalizedHideCoverPageAbstract()) {
                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $coverPagePath = $request->getBaseUrl() . '/';
                $coverPagePath .= $publicFileManager->getJournalFilesPath($journal->getId()) . '/';
                $templateMgr->assign('coverPagePath', $coverPagePath);
                $templateMgr->assign('coverPageFileName', $article->getLocalizedFileName());
                $templateMgr->assign('width', $article->getLocalizedWidth());
                $templateMgr->assign('height', $article->getLocalizedHeight());
                $templateMgr->assign('coverPageAltText', $article->getLocalizedCoverPageAltText());
            }

            // References list.
            $citationDao = DAORegistry::getDAO('CitationDAO'); /* @var $citationDao CitationDAO */
            $citationFactory = $citationDao->getObjectsByAssocId(ASSOC_TYPE_ARTICLE, $article->getId());
            $templateMgr->assign('citationFactory', $citationFactory);
        } else {
            if ($galley->isHTMLGalley() && $styleFile = $galley->getStyleFile()) {
                $templateMgr->addStyleSheet($router->url($request, null, 'article', 'viewFile', [
                    $article->getId(),
                    $galley->getBestGalleyId($journal),
                    $styleFile->getFileId()
                ]));
            }
        }

        // =====================================================================
        // [FIX FINAL - ANTI CRASH] PEMBERSIH SPASI (SAFE MODE)
        // =====================================================================
        $allAbstracts = $article->getData('abstract'); 

        if (is_array($allAbstracts)) {
            foreach ($allAbstracts as $localeKey => $textValue) {
                // 1. CEK KEAMANAN: Jika data kosong atau bukan string, lewati.
                if (empty($textValue) || !is_string($textValue)) continue;

                // 2. BERSIHKAN KARAKTER BANDEL
                // Kita hapus /u di regex untuk mencegah crash pada text encoding yang rusak
                $clean = str_replace(['&nbsp;', chr(194).chr(160)], ' ', $textValue);
                
                // 3. REGEX (Tanpa flag /u supaya aman dari bad-encoding)
                // Jika preg_replace gagal (return null), kita pakai string lama ($clean)
                $regexResult = preg_replace('/\s+/', ' ', $clean);
                if ($regexResult !== null) {
                    $clean = $regexResult;
                }

                // 4. SIMPAN (DENGAN CASTING PAKSA KE STRING)
                // (string) memastikan trim tidak akan pernah menerima NULL
                $article->setData('abstract', trim((string)$clean), $localeKey);
            }
        }
        // =====================================================================

        $templateMgr->assign('issue', $issue);
        $templateMgr->assign('article', $article);
        $templateMgr->assign('galley', $galley);
        $templateMgr->assign('section', $section);
        
        // Menugaskan Variabel RT & Version (Subject)
        $templateMgr->assign('journalRt', $journalRt);
        $templateMgr->assign('version', $version);
        
        $templateMgr->assign('journal', $journal);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);

        // Sepertinya ini masih jadi bagian dari RT yang seharusnya dihapus
        $templateMgr->assign('articleSearchByOptions', [
            'query' => 'search.allFields',
            'authors' => 'search.author',
            'title' => 'article.title',
            'abstract' => 'search.abstract',
            'indexTerms' => 'search.indexTerms',
            'galleyFullText' => 'search.fullText'
        ]);
        
        //======================================================================
        // --- START MODIFIKASI FORK OJS (Arsitektur Bersih - Fungsional) ---
        //======================================================================
        
        // --- 1. INTEGRASI DATA GENESIS ---
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $timeline = $articleDao->getEditorialTimeline($article->getId());
        $templateMgr->assign('revisionDate', $timeline['revisionDate']);
        $templateMgr->assign('acceptedDate', $timeline['acceptedDate']);

        // --- 2. Memanggil penugasan sebagai Objek Data Editor/Reviewer ---
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editorsData = $editAssignmentDao->getEditorsWithDetails($article->getId());
        $editorsData = $editAssignmentDao->getEditorsWithDetails($article);
        
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewersData = $reviewAssignmentDao->getReviewersWithDetails($article->getId());
        
        // Mengirimkan objek utuh ke Template Manager
        $templateMgr->assign('editAssignments', $editorsData);
        $templateMgr->assign('reviewAssignments', $reviewersData);
        
        // Data tambahan untuk kebutuhan tampilan tanggal & locale
        $templateMgr->assign('locale', AppLocale::getLocale());

        // --- 3. MODIFIKASI - Foto Penulis - Logika Akurat ---
        $authors = $article->getAuthors(); 
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $authorProfileData = $authorDao->getAuthorProfileDataMaps($authors); 
        
        $templateMgr->assign('authorProfileImages', $authorProfileData['profileImages']);
        $templateMgr->assign('authorGravatarMap', $authorProfileData['gravatars']);
        $templateMgr->assign('authorUserDataMap', $authorProfileData['userData']);
        
        // --- 4. INTEGRASI NAVIGASI ---
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $navigation = $publishedArticleDao->getGlobalArticleNavigation($article->getId(), $journal->getId());
    
        $templateMgr->assign('prevArticle', $navigation['prev']);
        $templateMgr->assign('nextArticle', $navigation['next']);
        
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->display('article/article.tpl');
    }

    /**
     * Article interstitial page before PDF is shown
     * @param array $args
     * @param PKPRequest $request
     * @param ArticleGalley|null $galley
     */
    public function viewPDFInterstitial($args, $request, $galley = null) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        
        $this->validate($request, $articleId, $galleyId);
        $article = $this->article;
        $journal = $this->journal; // Used in template implicitly or by setupTemplate
        $this->setupTemplate($request);

        if (!$galley) {
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            if ($journal->getSetting('enablePublicGalleyId')) {
                $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
            } else {
                $galley = $galleyDao->getGalley($galleyId, $article->getId());
            }
        }

        if (!$galley) $request->redirect(null, null, 'view', $articleId);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('galley', $galley);
        $templateMgr->assign('article', $article);

        $templateMgr->display('article/pdfInterstitial.tpl');
    }

    /**
     * Article interstitial page before a non-PDF, non-HTML galley is downloaded
     * @param array $args
     * @param PKPRequest $request
     * @param ArticleGalley|null $galley
     */
    public function viewDownloadInterstitial($args, $request, $galley = null) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        
        $this->validate($request, $articleId, $galleyId);
        $article = $this->article;
        $journal = $this->journal;
        $this->setupTemplate($request);

        if (!$galley) {
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            if ($journal->getSetting('enablePublicGalleyId')) {
                $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
            } else {
                $galley = $galleyDao->getGalley($galleyId, $article->getId());
            }
        }

        if (!$galley) $request->redirect(null, null, 'view', $articleId);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('galley', $galley);
        $templateMgr->assign('article', $article);

        $templateMgr->display('article/interstitial.tpl');
    }

    /**
     * Article view (Deprecated)
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewArticle($args, $request) {
        return $this->view($args, $request);
    }

    /**
     * View a file (inlines file).
     * @param array $args ($articleId, $galleyId, $fileId [optional])
     * @param PKPRequest $request
     */
    public function viewFile($args, $request) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;
        $fileId = isset($args[2]) ? (int) $args[2] : 0;

        $this->validate($request, $articleId, $galleyId);
        $journal = $this->journal;
        $article = $this->article;

        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        if ($journal->getSetting('enablePublicGalleyId')) {
            $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
        } else {
            $galley = $galleyDao->getGalley($galleyId, $article->getId());
        }

        if (!$galley) $request->redirect(null, null, 'view', $articleId);

        if (!$fileId) {
            $fileId = $galley->getFileId();
        } else {
            if (!$galley->isDependentFile($fileId)) {
                $request->redirect(null, null, 'view', $articleId);
            }
        }

        // HookRegistry keeps & for object references in array
        if (!HookRegistry::dispatch('ArticleHandler::viewFile', [&$article, &$galley, &$fileId])) {
            import('classes.submission.common.Action');
            Action::viewFile($article->getId(), $fileId);
        }
    }

    /**
     * Downloads the document
     * @param array $args
     * @param PKPRequest $request
     */
    public function download($args, $request) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;
        
        $this->validate($request, $articleId, $galleyId);
        $article = $this->article;

        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        if ($this->journal->getSetting('enablePublicGalleyId')) {
            $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
        } else {
            $galley = $galleyDao->getGalley($galleyId, $article->getId());
        }

        if ($article && $galley) {
            $fileId = $galley->getFileId();
            if (!HookRegistry::dispatch('ArticleHandler::downloadFile', [&$article, &$galley, &$fileId])) {
                import('classes.file.ArticleFileManager');
                $articleFileManager = new ArticleFileManager($article->getId());
                $articleFileManager->downloadFile($fileId);
            }
        }
    }

    /**
     * Download a supplementary file
     * @param array $args
     * @param PKPRequest $request
     */
    public function downloadSuppFile($args, $request) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $suppId = isset($args[1]) ? $args[1] : 0;
        
        $this->validate($request, $articleId);
        $journal = $this->journal;
        $article = $this->article;

        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        if ($journal->getSetting('enablePublicSuppFileId')) {
            $suppFile = $suppFileDao->getSuppFileByBestSuppFileId($suppId, $article->getId());
        } else {
            $suppFile = $suppFileDao->getSuppFile((int) $suppId, $article->getId());
        }

        if ($article && $suppFile && !HookRegistry::dispatch('ArticleHandler::downloadSuppFile', [&$article, &$suppFile])) {
            import('classes.file.ArticleFileManager');
            $articleFileManager = new ArticleFileManager($article->getId());
            if ($suppFile->getRemoteURL()) {
                $request->redirectUrl($suppFile->getRemoteURL());
            }
            $articleFileManager->downloadFile($suppFile->getFileId(), null, $suppFile->isInlineable());
        }
    }

    /**
     * Validation (Refactored for PHP 8 Compatibility)
     * @param mixed $requiredContexts (Logic swapped variable in legacy)
     * @param PKPRequest|null $request
     */
    public function validate($requiredContexts = null, $request = null) {
        // Parameter mapping untuk kompatibilitas
        $originalRequest = $request;
        $articleId = $requiredContexts;
        $galleyId = null;
        
        // Deteksi pemanggilan gaya lama (Legacy swap check)
        if ($requiredContexts instanceof PKPRequest) {
            $originalRequest = $requiredContexts;
            $articleId = $request;
            $request = null;
        }
        
        // Fallback untuk Request object
        if ($originalRequest === null) {
            $originalRequest = Application::get()->getRequest();
        }
        
        $request = $originalRequest;
        
        parent::validate(null, $request);

        import('classes.issue.IssueAction');

        $router = $request->getRouter();
        $journal = $router->getContext($request);
        $journalId = $journal->getId();
        $article = null;
        $publishedArticle = null;
        $issue = null;
        
        $user = $request->getUser();
        $userId = $user ? $user->getId() : 0;

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        if ($journal->getSetting('enablePublicArticleId')) {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByBestArticleId((int) $journalId, $articleId, true);
        } else {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId((int) $articleId, (int) $journalId, true);
        }

        $issueDao = DAORegistry::getDAO('IssueDAO');
        if (isset($publishedArticle)) {
            $issue = $issueDao->getIssueById($publishedArticle->getIssueId(), $publishedArticle->getJournalId(), true);
        } else {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle((int) $articleId, $journalId, true);
        }

        // If this is an editorial user who can view unpublished/unscheduled
        // articles, bypass further validation. Likewise for its author.
        if (($article || $publishedArticle) && (($article && IssueAction::allowedPrePublicationAccess($journal, $article) || ($publishedArticle && IssueAction::allowedPrePublicationAccess($journal, $publishedArticle))))) {
            $this->journal = $journal;
            $this->issue = $issue;
            if(isset($publishedArticle)) {
                $this->article = $publishedArticle;
            } else $this->article = $article;

            return true;
        }

        // Make sure the reader has rights to view the article/issue.
        if ($issue && $issue->getPublished() && $publishedArticle->getStatus() == STATUS_PUBLISHED) {
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $isSubscribedDomain = IssueAction::subscribedDomain($journal, $issue->getId(), $publishedArticle->getId());

            // Check if login is required for viewing.
            if (!$isSubscribedDomain && !Validation::isLoggedIn() && $journal->getSetting('restrictArticleAccess') && isset($galleyId) && $galleyId) {
                Validation::redirectLogin();
            }

            // bypass all validation if subscription based on domain or ip is valid
            // or if the user is just requesting the abstract
            if ( (!$isSubscribedDomain && $subscriptionRequired) && (isset($galleyId) && $galleyId) ) {

                // Subscription Access
                $subscribedUser = IssueAction::subscribedUser($journal, $issue->getId(), $publishedArticle->getId());

                import('classes.payment.ojs.OJSPaymentManager');
                $paymentManager = new OJSPaymentManager($request);

                $purchasedIssue = false;
                if (!$subscribedUser && $paymentManager->purchaseIssueEnabled()) {
                    $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
                    $purchasedIssue = $completedPaymentDao->hasPaidPurchaseIssue($userId, $issue->getId());
                }

                if (!(!$subscriptionRequired || $publishedArticle->getAccessStatus() == ARTICLE_ACCESS_OPEN || $subscribedUser || $purchasedIssue)) {

                    if ( $paymentManager->purchaseArticleEnabled() || $paymentManager->membershipEnabled() ) {
                        /* if only pdf files are being restricted, then approve all non-pdf galleys
                         * and continue checking if it is a pdf galley */
                        if ( $paymentManager->onlyPdfEnabled() ) {
                            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                            if ($journal->getSetting('enablePublicGalleyId')) {
                                $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $publishedArticle->getId());
                            } else {
                                $galley = $galleyDao->getGalley($galleyId, $publishedArticle->getId());
                            }
                            if ( $galley && !$galley->isPdfGalley() ) {
                                $this->journal = $journal;
                                $this->issue = $issue;
                                $this->article = $publishedArticle;
                                return true;
                            }
                        }

                        if (!Validation::isLoggedIn()) {
                            Validation::redirectLogin("payment.loginRequired.forArticle");
                        }

                        /* if the article has been paid for then forget about everything else
                         * and just let them access the article */
                        $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
                        $dateEndMembership = $user->getSetting('dateEndMembership', 0);
                        if ($completedPaymentDao->hasPaidPurchaseArticle($userId, $publishedArticle->getId())
                            || (!is_null($dateEndMembership) && $dateEndMembership > time())) {
                            $this->journal = $journal;
                            $this->issue = $issue;
                            $this->article = $publishedArticle;
                            return true;
                        } else {
                            $queuedPayment = $paymentManager->createQueuedPayment($journalId, PAYMENT_TYPE_PURCHASE_ARTICLE, $user->getId(), $publishedArticle->getId(), $journal->getSetting('purchaseArticleFee'));
                            $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

                            $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
                            exit;
                        }
                    }

                    if (!isset($galleyId) || $galleyId) {
                        if (!Validation::isLoggedIn()) {
                            Validation::redirectLogin("reader.subscriptionRequiredLoginText");
                        }
                        $request->redirect(null, 'about', 'subscriptions');
                    }
                }
            }
        } else {
            $request->redirect(null, 'index');
        }
        $this->journal = $journal;
        $this->issue = $issue;
        $this->article = $publishedArticle;
        return true;
    }

    /**
     * Set up the template
     * @param PKPRequest $request
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate();
        
        // Dapatkan request dari arguments atau context
        if ($request === null) {
            $args = func_get_args();
            $request = isset($args[0]) ? $args[0] : null;
        }
        
        // Jika request tidak ada, coba dapatkan dari context
        if ($request === null) {
            $request = Application::get()->getRequest();
        }
        
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_READER, LOCALE_COMPONENT_CORE_SUBMISSION);
        
        if ($this->article) {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('ccLicenseBadge', Application::getCCLicenseBadge($this->article->getLicenseURL()));
        }
    }
    
    /**
     * Menampilkan halaman metrik untuk artikel tertentu.
     * [WIZDAM] Versi ini menyertakan logika "Best ID" manual yang lebih lama
     * @param array $args Argumen URL (misal, $args[0] adalah <articleId>)
     * @param PKPRequest $request Objek Request OJS
     */
    public function metrics($args = [], $request = null) {

        // --- 1. Inisialisasi dan Pengambilan Data Dasar ---
        $articleId = isset($args[0]) ? $args[0] : 0;
        $journal = $request->getJournal();
        $user = $request->getUser();
        
        // Kita butuh PublishedArticleDAO untuk ini
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        // --- 2. Logika Pemuatan Artikel (Manual "Best ID") ---
        // Langkah 2a: Coba dulu sebagai ID Kustom (PubId) 'publisher-id' 
        // kunci untuk ID Kustom 'true' di akhir adalah untuk checkLocale
        $article = $publishedArticleDao->getPublishedArticleByPubId('publisher-id', $articleId, $journal->getId(), true);

        if (!$article) {
            // Langkah 2b: Jika GAGAL, coba sebagai ID Numerik internal
            // (Kita pastikan itu integer dengan (int))
            $article = $publishedArticleDao->getPublishedArticleByArticleId((int)$articleId, $journal->getId(), true);
        }

        // --- 3. Validasi Input (Guard Clause) ---
        // SEKARANG baru kita cek apakah $article valid setelah SEMUA percobaan
        if (!$article) {
            // Gunakan perbaikan redirect final
            $baseUrl = $request->getBaseUrl();
            header("Location: " . $baseUrl . "/index");
            exit;
        }

        // --- 4. Pengecekan Izin Akses (Permission Check) ---
        $isEditor = Validation::isEditor($journal->getId());
        
        if ($article->getStatus() != STATUS_PUBLISHED) {
            $isAuthor = $user && $user->getId() == $article->getUserId();
            if (!$user || (!$isAuthor && !$isEditor)) {
                // Gunakan perbaikan redirect final
                $baseUrl = $request->getBaseUrl();
                header("Location: " . $baseUrl . "/index");
                exit;
            }
        }

        // --- 5. Menyiapkan Template Manager ---
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('article', $article);

        // --- 6. Pengambilan Statistik ---
        
        // 6a. Abstract Views
        $views = 0;
        if (method_exists($article, 'getViews')) {
            $views = (int) $article->getViews();
        }

        // 6b. Galley (Download) Views
        $downloads = 0;
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); 
        $galleys = $galleyDao->getGalleysByArticle($article->getId());
        
        foreach ($galleys as $galley) {
            if (method_exists($galley, 'getViews')) {
                $downloads += (int) $galley->getViews();
            }
        }

        // --- 7. Menyiapkan dan Menampilkan Halaman ---
        $templateMgr->assign('views', $views);
        $templateMgr->assign('downloads', $downloads);
        
        $doi = $article->getPubId('doi');
        $templateMgr->assign('doi', $doi); 

        // Parent call (uses internal implicit request usually)
        $this->setupTemplate($request);

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getIssueByArticleId($article->getId());

        if ($issue && $issue->getJournalId() == $journal->getId()) {
            $templateMgr->assign('issue', $issue);
        } else {
            $templateMgr->assign('issue', null);
        }
        
        // --- Logika "Last Updated" ---
        $lastUpdatedString = 'N/A';
        $filesDir = Config::getVar('files', 'files_dir');
        $archiveDir = $filesDir . '/usageStats/archive/';

        if (is_dir($archiveDir)) {
            $latestTimestamp = 0;
            if ($handle = opendir($archiveDir)) {
                while (false !== ($entry = readdir($handle))) {
                    if (strpos($entry, '.log') !== false) {
                        $fileTimestamp = filemtime($archiveDir . $entry);
                        if ($fileTimestamp > $latestTimestamp) {
                            $latestTimestamp = $fileTimestamp;
                        }
                    }
                }
                closedir($handle);
            }
            if ($latestTimestamp > 0) {
                $lastUpdatedString = date('l, d M Y H:i:s T', $latestTimestamp);
            } else {
                $lastUpdatedString = 'Stats processing is pending';
            }
        } else {
            $lastUpdatedString = 'N/A (Stats archive path not found)';
        }
        $templateMgr->assign('statsLastUpdated', $lastUpdatedString);

        // Tampilkan template
        $templateMgr->display('article/metrics.tpl');
    }
}
?>