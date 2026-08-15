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
    public function __construct($request = null) {
        if ($request === null) {
            $request = Application::get()->getRequest();
        }
        parent::__construct($request);
        $router = $request->getRouter();

        $this->addCheck(new HandlerValidatorJournal($this));
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
        if ($op === 'metrics') {
            import('pages.article.ArticleMetricsHandler');
            $metricsHandler = new ArticleMetricsHandler();
            return $metricsHandler->metrics([$articleIdInput], $request);
        }
        
        $router = $request->getRouter();
        $journal = $request->getJournal();
        $galleyId = $args[1] ?? 0;
        
        if (!$journal) {
            return $request->redirect(null, 'index');
        }
        $currentJournalId = (int) $journal->getId();

        // [WIZDAM] DETEKSI ID YANG LEBIH CERDAS (NUMERIC PUBLIC ID)
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $articleObj = null;

        // 1. Coba cari pakai cara standar (Best ID)
        $articleObj = $publishedArticleDao->getPublishedArticleByBestArticleId($currentJournalId, $articleIdInput, true);
        // 2. FALLBACK: Jika gagal, inputnya angka, paksa cari sebagai Public ID
        if (!$articleObj && is_numeric($articleIdInput)) {
            $articleObj = $publishedArticleDao->getPublishedArticleByPubId(
                'publisher-id', 
                (string) $articleIdInput, 
                $currentJournalId
            );
        }
        // 3. RESOLUSI ID: Pastikan $articleId jadi INTERNAL ID (Integer) valid
        $articleId = $articleObj ? (int) $articleObj->getId() : (int) $articleIdInput;
        
        $issue = $issueDao ? $issueDao->getIssueByArticleId($articleId, $currentJournalId) : null;

        // [WIZDAM] FIX UTAMA: validate() sekarang menerima 3 parameter dengan benar
        $this->validate($request, $articleId, $galleyId);
        
        // Setup objek standar OJS
        $journal = $this->journal;
        $issue = $this->issue;
        $article = $this->article;
        $this->setupTemplate($request);

        /** @var RTDAO $rtDao */
        $rtDao = DAORegistry::getDAO('RTDAO');
        $journalRt = $rtDao ? $rtDao->getJournalRTByJournal($journal) : null;

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao ? $sectionDao->getSection($article->getSectionId(), $currentJournalId, true) : null;

        // RTVersion sebagai Subject
        $version = null;
        if ($journalRt && $journalRt->getVersion() != null) {
            $version = $rtDao->getVersion($journalRt->getVersion(), $journalRt->getJournalId(), true);
        }

        // Article Galley PDF/HTML/XML
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = null;
        if ($galleyDao) {
            if ($journal->getSetting('enablePublicGalleyId')) {
                $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
            } else {
                $galley = $galleyDao->getGalley($galleyId, $article->getId());
            }
        }

        if ($galley && !$galley->isHtmlGalley() && !$galley->isPdfGalley()) {
            if ($galley->getRemoteURL()) {
                if (!HookRegistry::dispatch('ArticleHandler::viewRemoteGalley', [&$article, &$galley])) {
                    $request->redirectUrl($galley->getRemoteURL());
                }
            }
            if ($galley->isInlineable()) {
                return $this->viewFile([$galley->getArticleId(), $galley->getId()], $request);
            } else {
                return $this->download([$galley->getArticleId(), $galley->getId()], $request);
            }
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->addJavaScript('js/inlinePdf.js');
        $templateMgr->addJavaScript('js/pdfobject.js');
        
        $galleys = $galleyDao ? $galleyDao->getGalleysByArticle($article->getId()) : [];
        
        if (!$galley) {
            import('classes.issue.IssueAction');

            // [WIZDAM] Micro-payloads untuk data langganan
            $subscriptionData = [
                'galleys' => $galleys,
                'showGalleyLinks' => (bool) $journal->getSetting('showGalleyLinks')
            ];

            if ($issue) {
                $subscriptionData['subscriptionRequired'] = IssueAction::subscriptionRequired($issue);
            }

            $subscriptionData['subscribedUser'] = IssueAction::subscribedUser($journal, $issue ? $issue->getId() : null, $article ? $article->getId() : null);
            $subscriptionData['subscribedDomain'] = IssueAction::subscribedDomain($journal, $issue ? $issue->getId() : null, $article ? $article->getId() : null);

            $templateMgr->assign($subscriptionData);

            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            $paymentFlags = [];
            if ($paymentManager->onlyPdfEnabled()) {
                $paymentFlags['restrictOnlyPdf'] = true;
            }
            if ($paymentManager->purchaseArticleEnabled()) {
                $paymentFlags['purchaseArticleEnabled'] = true;
            }
            if (!empty($paymentFlags)) {
                $templateMgr->assign($paymentFlags);
            }

            // Article cover page
            if ($article && $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage() && !$article->getLocalizedHideCoverPageAbstract()) {
                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $coverPagePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()) . '/';
                $templateMgr->assign([
                    'coverPagePath'    => $coverPagePath,
                    'coverPageFileName' => $article->getLocalizedFileName(),
                    'width'            => $article->getLocalizedWidth(),
                    'height'           => $article->getLocalizedHeight(),
                    'coverPageAltText' => $article->getLocalizedCoverPageAltText()
                ]);
            }

            // References list
            /** @var CitationDAO $citationDao */
            $citationDao = DAORegistry::getDAO('CitationDAO');
            $citationFactory = $citationDao ? $citationDao->getObjectsByAssocId(ASSOC_TYPE_ARTICLE, $article->getId()) : [];
            $templateMgr->assign('citationFactory', $citationFactory);
        } else {
            $templateMgr->assign('galleys', $galleys);
            import('classes.article.ArticleHTMLGalley');
            if ($galley instanceof ArticleHTMLGalley && $styleFile = $galley->getStyleFile()) {
                $templateMgr->addStyleSheet($router->url($request, null, 'article', 'viewFile', [
                    $article->getId(),
                    $galley->getBestGalleyId($journal),
                    $styleFile->getFileId()
                ]));
            }
        }

        // [LUMERA] Pembersih spasi pada abstract
        $allAbstracts = $article->getData('abstract'); 
        if (is_array($allAbstracts)) {
            foreach ($allAbstracts as $localeKey => $textValue) {
                if (empty($textValue) || !is_string($textValue)) continue;
                $clean = str_replace(['&nbsp;', chr(194).chr(160)], ' ', $textValue);
                $regexResult = preg_replace('/\s+/', ' ', $clean);
                if ($regexResult !== null) {
                    $clean = $regexResult;
                }
                $article->setData('abstract', trim((string)$clean), $localeKey);
            }
        }

        // [LUMERA] Micro-payloads untuk data utama
        $templateMgr->assign([
            'issue'      => $issue,
            'article'    => $article,
            'galley'     => $galley,
            'section'    => $section,
            'journalRt'  => $journalRt,
            'version'    => $version,
            'journal'    => $journal,
            'articleId'  => $articleId,
            'galleyId'   => $galleyId
        ]);
        
        // [NOTE] Ini masih jadi bagian RT, seharusnya dihapus di masa depan
        $templateMgr->assign('articleSearchByOptions', [
            'query' => 'search.allFields',
            'authors' => 'search.author',
            'title' => 'article.title',
            'abstract' => 'search.abstract',
            'indexTerms' => 'search.indexTerms',
            'galleyFullText' => 'search.fullText'
        ]);

        // --- START MODERNISASI FORK Lumera ---        
        // --- 1. INTEGRASI DATA GENESIS ---
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $timeline = $articleDao ? $articleDao->getEditorialTimeline($article->getId()) : [];
        
        $templateMgr->assign([
            'revisionDate'  => $timeline['revisionDate'] ?? null,
            'acceptedDate'  => $timeline['acceptedDate'] ?? null
        ]);
        
        // --- 2. Penugasan Editor/Reviewer ---
        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editorsData = $editAssignmentDao ? $editAssignmentDao->getEditorsWithDetails($article) : [];
        
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewersData = $reviewAssignmentDao ? $reviewAssignmentDao->getReviewersWithDetails($article->getId()) : [];
        
        $templateMgr->assign([
            'editAssignments'   => $editorsData,
            'reviewAssignments' => $reviewersData,
            'locale'            => AppLocale::getLocale()
        ]);

        // --- 3. Foto Penulis & Peta Afiliasi ---
        $authors = $article->getAuthors(); 
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $authorProfileData = $authorDao ? $authorDao->getAuthorProfileDataMaps($authors) : ['profileImages' => [], 'gravatars' => [], 'userData' => []]; 
        $affiliationMap = PKPAuthor::buildAffiliationMap($authors);
        
        $templateMgr->assign([
            'affiliationMap'      => $affiliationMap,
            'authorProfileImages' => $authorProfileData['profileImages'] ?? [],
            'authorGravatarMap'   => $authorProfileData['gravatars'] ?? [],
            'authorUserDataMap'   => $authorProfileData['userData'] ?? []
        ]);
        
        // --- 4. INTEGRASI NAVIGASI ---
        $navigation = $publishedArticleDao ? $publishedArticleDao->getGlobalArticleNavigation($article->getId(), $journal->getId()) : ['prev' => null, 'next' => null];
    
        $templateMgr->assign([
            'prevArticle' => $navigation['prev'] ?? null,
            'nextArticle' => $navigation['next'] ?? null
        ]);
        
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins ?: []);

        $articleDoi = $article->getPubId('doi');
        $citingArticles = [];
        $citationCount = 0;
        if (!empty($articleDoi)) {
            import('lib.wizdam.classes.citation.CitationFetcherService');
            $citationFetcher = new CitationFetcherService($journal);
            $citationData = $citationFetcher->getCachedCitations((string) $articleDoi);
            if ($citationData !== null) {
                $citationCount = (int) ($citationData['citation_count'] ?? 0);
                $citingArticles = array_slice($citationData['citing_articles'] ?? [], 0, 7);
                $citationSources = $citationData['citation_sources'] ?? null;
                $citationTimestamp = (int) ($citationData['last_updated'] ?? $citationData['timestamp'] ?? 0);
            }
        }
        $templateMgr->assign([
            'articleDoi'     => $articleDoi,
            'citingArticles' => $citingArticles,
            'citationCount'  => $citationCount,
            'citationSourcesJson' => isset($citationSources) ? json_encode($citationSources) : '',
            'citationTimestamp'   => $citationTimestamp ?? 0,
        ]);

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
        $journal = $this->journal;
        $this->setupTemplate($request);

        if (!$galley) {
            /** @var ArticleGalleyDAO $galleyDao */
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            if ($galleyDao) {
                if ($journal->getSetting('enablePublicGalleyId')) {
                    $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
                } else {
                    $galley = $galleyDao->getGalley($galleyId, $article->getId());
                }
            }
        }

        if (!$galley) {
            $request->redirect(null, null, 'view', $articleId);
            return;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'articleId' => $articleId,
            'galleyId'  => $galleyId,
            'galley'    => $galley,
            'article'   => $article
        ]);

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
            /** @var ArticleGalleyDAO $galleyDao */
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            if ($galleyDao) {
                if ($journal->getSetting('enablePublicGalleyId')) {
                    $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
                } else {
                    $galley = $galleyDao->getGalley($galleyId, $article->getId());
                }
            }
        }

        if (!$galley) {
            $request->redirect(null, null, 'view', $articleId);
            return;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'articleId' => $articleId,
            'galleyId'  => $galleyId,
            'galley'    => $galley,
            'article'   => $article
        ]);

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

        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = null;
        if ($galleyDao) {
            if ($journal->getSetting('enablePublicGalleyId')) {
                $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
            } else {
                $galley = $galleyDao->getGalley($galleyId, $article->getId());
            }
        }

        if (!$galley) {
            $request->redirect(null, null, 'view', $articleId);
            return;
        }

        if (!$fileId) {
            $fileId = $galley->getFileId();
        } else {
            if (!$galley->isDependentFile($fileId)) {
                $request->redirect(null, null, 'view', $articleId);
                return;
            }
        }

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

        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = null;
        if ($galleyDao) {
            if ($this->journal->getSetting('enablePublicGalleyId')) {
                $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
            } else {
                $galley = $galleyDao->getGalley($galleyId, $article->getId());
            }
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

        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFile = null;
        if ($suppFileDao) {
            if ($journal->getSetting('enablePublicSuppFileId')) {
                $suppFile = $suppFileDao->getSuppFileByBestSuppFileId($suppId, $article->getId());
            } else {
                $suppFile = $suppFileDao->getSuppFile((int) $suppId, $article->getId());
            }
        }

        if ($article && $suppFile && !HookRegistry::dispatch('ArticleHandler::downloadSuppFile', [&$article, &$suppFile])) {
            import('classes.file.ArticleFileManager');
            $articleFileManager = new ArticleFileManager($article->getId());
            if ($suppFile->getRemoteURL()) {
                $request->redirectUrl($suppFile->getRemoteURL());
                return;
            }
            $articleFileManager->downloadFile($suppFile->getFileId(), null, $suppFile->isInlineable());
        }
    }

    /**
     * Validation (Refactored for PHP 8 Compatibility)
     * @param mixed $arg1 Bisa $request (PKPRequest) atau $articleId (int)
     * @param mixed $arg2 Bisa $articleId (int) atau $request (PKPRequest - legacy)
     * @param mixed $arg3 $galleyId (int|null)
     */
    public function validate($arg1 = null, $arg2 = null, $arg3 = null) {
        $request = null;
        $articleId = null;
        $galleyId = null;
        
        if ($arg1 instanceof PKPRequest) {
            // Pola modern: validate($request, $articleId, $galleyId)
            $request = $arg1;
            $articleId = $arg2;
            $galleyId = $arg3;
        } elseif ($arg2 instanceof PKPRequest) {
            // Pola legacy parent: validate($requiredContexts, $request)
            $request = $arg2;
            $articleId = $arg1;
            $galleyId = null;
        } else {
            // Pola fallback: validate($articleId) atau validate()
            $request = Application::get()->getRequest();
            $articleId = $arg1;
            $galleyId = $arg2;
        }
        
        if ($request === null) {
            $request = Application::get()->getRequest();
        }

        parent::validate(null, $request);

        import('classes.issue.IssueAction');

        $router = $request->getRouter();
        $journal = $router->getContext($request);
        $journalId = (int) $journal->getId();
        $article = null;
        $publishedArticle = null;
        $issue = null;
        
        $user = $request->getUser();
        $userId = $user ? $user->getId() : 0;

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        if ($publishedArticleDao) {
            if ($journal->getSetting('enablePublicArticleId')) {
                $publishedArticle = $publishedArticleDao->getPublishedArticleByBestArticleId($journalId, $articleId, true);
            } else {
                $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId((int) $articleId, $journalId, true);
            }
        }

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        if (isset($publishedArticle)) {
            $issue = $issueDao ? $issueDao->getIssueById($publishedArticle->getIssueId(), $publishedArticle->getJournalId(), true) : null;
        } else {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao ? $articleDao->getArticle((int) $articleId, $journalId, true) : null;
        }

        // If this is an editorial user who can view unpublished/unscheduled articles
        $viewableArticle = $publishedArticle ?? $article;
        if ($viewableArticle && IssueAction::allowedPrePublicationAccess($journal, $viewableArticle)) {
            $this->journal = $journal;
            $this->issue = $issue;
            $this->article = $viewableArticle;
            return true;
        }

        // [BUGFIX] Tambah null check pada $publishedArticle
        if ($issue && $issue->getPublished() && $publishedArticle && $publishedArticle->getStatus() == STATUS_PUBLISHED) {
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $isSubscribedDomain = IssueAction::subscribedDomain($journal, $issue->getId(), $publishedArticle->getId());

            if (!$isSubscribedDomain && !Validation::isLoggedIn() && $journal->getSetting('restrictArticleAccess') && $galleyId) {
                Validation::redirectLogin();
            }

            if ((!$isSubscribedDomain && $subscriptionRequired) && $galleyId) {
                $subscribedUser = IssueAction::subscribedUser($journal, $issue->getId(), $publishedArticle->getId());

                import('classes.payment.ojs.OJSPaymentManager');
                $paymentManager = new OJSPaymentManager($request);

                $purchasedIssue = false;
                if (!$subscribedUser && $paymentManager->purchaseIssueEnabled()) {
                    /** @var OJSCompletedPaymentDAO $completedPaymentDao */
                    $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
                    $purchasedIssue = $completedPaymentDao ? $completedPaymentDao->hasPaidPurchaseIssue($userId, $issue->getId()) : false;
                }

                if (!(!$subscriptionRequired || $publishedArticle->getAccessStatus() == ARTICLE_ACCESS_OPEN || $subscribedUser || $purchasedIssue)) {

                    if ($paymentManager->purchaseArticleEnabled() || $paymentManager->membershipEnabled()) {
                        if ($paymentManager->onlyPdfEnabled()) {
                            /** @var ArticleGalleyDAO $galleyDao */
                            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                            $galley = null;
                            if ($galleyDao) {
                                if ($journal->getSetting('enablePublicGalleyId')) {
                                    $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $publishedArticle->getId());
                                } else {
                                    $galley = $galleyDao->getGalley($galleyId, $publishedArticle->getId());
                                }
                            }
                            if ($galley && !$galley->isPdfGalley()) {
                                $this->journal = $journal;
                                $this->issue = $issue;
                                $this->article = $publishedArticle;
                                return true;
                            }
                        }

                        if (!Validation::isLoggedIn()) {
                            Validation::redirectLogin("payment.loginRequired.forArticle");
                        }

                        /** @var OJSCompletedPaymentDAO $completedPaymentDao */
                        $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
                        $dateEndMembership = $user ? $user->getSetting('dateEndMembership', 0) : 0;
                        if (($completedPaymentDao && $completedPaymentDao->hasPaidPurchaseArticle($userId, $publishedArticle->getId()))
                            || (!is_null($dateEndMembership) && $dateEndMembership > time())) {
                            $this->journal = $journal;
                            $this->issue = $issue;
                            $this->article = $publishedArticle;
                            return true;
                        } else {
                            $queuedPayment = $paymentManager->createQueuedPayment($journalId, PAYMENT_TYPE_PURCHASE_ARTICLE, $userId, $publishedArticle->getId(), $journal->getSetting('purchaseArticleFee'));
                            $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

                            $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
                            exit;
                        }
                    }

                    if ($galleyId) {
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
     * @param PKPRequest|null $request
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate();
        
        if ($request === null) {
            $args = func_get_args();
            $request = isset($args[0]) ? $args[0] : null;
        }
        
        if ($request === null) {
            $request = Application::get()->getRequest();
        }
        
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_READER, LOCALE_COMPONENT_CORE_SUBMISSION);
        if ($this->article) {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('ccLicenseBadge', Application::getCCLicenseBadge($this->article->getLicenseURL()));
        }
    }

}
?>