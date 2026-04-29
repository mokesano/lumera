<?php
declare(strict_types=1);

/**
 * @file pages/issue/IssueHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueHandler
 * @ingroup pages_issue
 *
 * @brief Handle requests for issue functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & SEO logic preserved
 * [WIZDAM v2] Degradasi URL bertingkat: Issue → Volume → Year → Archive
 */

import ('classes.issue.IssueAction');
import('classes.handler.Handler');

class IssueHandler extends Handler {
    
    /** @var Issue retrieved issue */
    protected $_issue = null;

    /** @var IssueGalley retrieved issue galley */
    protected $_galley = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->addCheck(new HandlerValidatorJournal($this));
        
        // [WIZDAM FIX] Replaced deprecated create_function with Closure/Anonymous Function for PHP 8+
        $this->addCheck(new HandlerValidatorCustom($this, false, null, null, function($journal) {
            return $journal->getSetting('publishingMode') != PUBLISHING_MODE_NONE;
        }, [Request::getJournal()]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function IssueHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Display about index page.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function index($args = [], $request = null) {
        $this->current($args, $request);
    }

    /**
     * Display current issue page.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function current($args, $request) {
        $this->validate($request);
        $this->setupTemplate();

        $showToc = isset($args[0]) ? $args[0] : '';

        $journal = $request->getJournal();

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getCurrentIssue((int) $journal->getId(), true);

        $templateMgr = TemplateManager::getManager();

        if ($issue != null) {
            if ($showToc == 'showToc') {
                $request->redirect(null, 'issue', 'view', [(int) $issue->getId($journal), "showToc"], $request->getQueryArray());
            } else {
                $request->redirect(null, 'issue', 'view', (int) $issue->getId($journal), $request->getQueryArray());
            }
        } else {
            $issueCrumbTitle = __('current.noCurrentIssue');
            $issueHeadingTitle = __('current.noCurrentIssue');
        }

        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'issue', 'current'), 'current.current']]);
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->display('issue/viewPage.tpl');
    }

    /**
     * Display issue view page.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function view($args, $request) {
        // =================================================================
        // WIZDAM: BLOK 301 REDIRECT BERTINGKAT (Issue → Volume → Year)
        //
        // Dijalankan HANYA ketika URL mentah masih menggunakan format LAMA
        // (/issue/view/), bukan URL "native" baru (/volumes/.../issue/...).
        //
        // LEVEL DEGRADASI:
        //   L1 (Normal)       : Ada volume + issue slug → /volumes/{vol}/issue/{slug}
        //   L2 (Degradasi)    : Ada volume, issue null  → /volumes/{vol}
        //   L3 (Degradasi Penuh): Volume null           → /year/{year} atau /volumes/
        // =================================================================
        $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

        if (strpos($pathInfo, '/issue/view/') !== false) {
            $issueId  = isset($args[0]) ? (int)$args[0] : 0;
            $showToc  = (isset($args[1]) && $args[1] == 'showToc');

            $journal = $request->getJournal();

            if ($issueId && $journal) {
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue    = $issueDao->getIssueById($issueId, $journal->getId());

                if ($issue) {
                    $journalPath = $journal->getPath();
                    $volumeId    = (string) $issue->getVolume();    // Kosong jika null/0
                    $issueNumber = (string) $issue->getNumber();     // Bisa "", "0", "1", "Supplement"

                    // --- Hitung issueIdentifier ---
                    $issueIdentifier = '';
                    if ($issueNumber !== '') {
                        $slug = PKPString::slugify($issueNumber);
                        // Jika slug kosong setelah slugify (misal karakter aneh saja),
                        // gunakan ID sebagai fallback
                        $issueIdentifier = ($slug !== '') ? $slug : (string) $issue->getId();
                    }
                    // Jika issueNumber kosong, issueIdentifier tetap ''
                    // → trigger Level 2 atau Level 3 di bawah

                    // --- Tentukan Level Degradasi ---
                    if ($issueIdentifier !== '' && $volumeId !== '') {
                        // *** LEVEL 1 (Normal): /volumes/{vol}/issue/{slug}[/showToc] ***
                        $newUrl = $request->getBaseUrl()
                            . '/' . $journalPath
                            . '/volumes/' . $volumeId
                            . '/issue/' . $issueIdentifier;
                        if ($showToc) {
                            $newUrl .= '/showToc';
                        }

                    } elseif ($volumeId !== '') {
                        // *** LEVEL 2 (Degradasi): Issue null, tapi Volume ada ***
                        // → /volumes/{vol}
                        $newUrl = $request->getBaseUrl()
                            . '/' . $journalPath
                            . '/volumes/' . $volumeId;

                    } else {
                        // *** LEVEL 3 (Degradasi Penuh): Volume null → Year atau Archive ***
                        // Volume null berarti issue juga pasti null secara logika bisnis
                        $year = '';

                        // Coba ambil tahun dari kolom year
                        if ($issue->getYear()) {
                            $year = (string) $issue->getYear();
                        }

                        // Fallback: ekstrak tahun dari tanggal terbit
                        if ($year === '' && $issue->getDatePublished()) {
                            $year = date('Y', strtotime($issue->getDatePublished()));
                        }

                        if ($year !== '') {
                            // → /year/{year}
                            $newUrl = $request->getBaseUrl()
                                . '/' . $journalPath
                                . '/year/' . $year;
                        } else {
                            // Fallback terakhir: ke halaman arsip utama
                            $newUrl = $request->getBaseUrl()
                                . '/' . $journalPath
                                . '/volumes/';
                        }
                    }

                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: " . $newUrl);
                    exit;
                }
            }
        }
        // =================================================================
        // AKHIR BLOK 301 REDIRECT
        // =================================================================

        $issueId = isset($args[0]) ? $args[0] : 0;
        $showToc = isset($args[1]) ? $args[1] : '';

        $this->validate(null, $request, $issueId);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $issue   = $this->getIssue();

        $templateMgr = TemplateManager::getManager();

        // --- BLOK LOGIKA HANDLER (NAVIGASI & BREADCRUMB) ---
        if ($issue) {

            // 1. Navigasi Prev/Next Issue
            $issueDao = DAORegistry::getDAO('IssueDAO');
            list($prevIssue, $nextIssue) = $issueDao->getSurroundingIssues($issue->getId(), $journal->getId());

            $templateMgr->assign('nextIssue', $nextIssue);
            $templateMgr->assign('prevIssue', $prevIssue);

            // 2. Breadcrumb Bertingkat
            $journalPath = $journal->getPath();
            $baseUrl     = $request->getBaseUrl();
            $volumeId    = $issue->getVolume();

            // Level 1: "Volumes" → /volumes
            $volumesUrl = $baseUrl . '/' . $journalPath . '/volumes';
            $volumesKey = 'archive.archives';

            // Level 2: "Volume N" → /volumes/{vol}
            $volumeUrl         = $baseUrl . '/' . $journalPath . '/volumes/' . $volumeId;
            $volumeTitleString = AppLocale::translate('issue.volume') . ' ' . $volumeId;

            // Level 3: "Issue X" (Halaman saat ini, tidak bisa diklik)
            $issueTitleString = AppLocale::translate('issue.issue') . ' ' . $issue->getNumber();

            $templateMgr->assign('pageHierarchy', [
                [$volumesUrl, $volumesKey],
                [$volumeUrl, $volumeTitleString, true]
            ]);
            $templateMgr->assign('pageCrumbTitleTranslated', $issueTitleString);
            $templateMgr->assign('pageTitleTranslated', $issueTitleString);
            $templateMgr->assign('issueId', $issue->getBestIssueId());
        }
        // --- AKHIR BLOK LOGIKA HANDLER ---

        $this->_setupIssueTemplate($request, $issue, ($showToc == 'showToc'));

        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
        $templateMgr->display('issue/viewPage.tpl');
    }

    /**
     * Display the issue archive listings
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function archive($args, $request) {
        // 301 Redirect: /issue/archive → /volumes/
        $journal = $request->getJournal();
        if ($journal) {
            $journalPath = $journal->getPath();
            $newUrl = $request->getBaseUrl() . '/' . $journalPath . '/volumes/';

            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $newUrl);
            exit;
        }

        // Fallback jika tidak ada konteks jurnal
        $this->validate($request);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $rangeInfo = $this->getRangeInfo('issues');

        $publishedIssuesIterator = $issueDao->getPublishedIssues($journal->getId(), $rangeInfo);

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/';
        $coverPagePath .= $publicFileManager->getJournalFilesPath($journal->getId()) . '/';

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('coverPagePath', $coverPagePath);
        $templateMgr->assign('locale', AppLocale::getLocale());
        $templateMgr->assign('primaryLocale', $journal->getPrimaryLocale());
        $templateMgr->assign('issues', $publishedIssuesIterator);
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
        $templateMgr->display('issue/archive.tpl');
    }

    /**
     * View a PDF issue galley inline
     * @param array $args ($issueId, $galleyId)
     * @param PKPRequest $request
     * @return void
     */
    public function viewIssue($args, $request) {
        $issueId  = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;

        $this->validate(null, $request, $issueId, $galleyId);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $issue   = $this->getIssue();
        $galley  = $this->getGalley();

        if (!$galley->isPdfGalley()) {
            $request->redirect(null, null, 'viewDownloadInterstitial', [$issueId, $galleyId]);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('js/inlinePdf.js');
        $templateMgr->addJavaScript('js/pdfobject.js');
        $templateMgr->addStyleSheet($request->getBaseUrl().'/styles/pdfView.css');

        $templateMgr->assign('issue', $issue);
        $templateMgr->assign('galley', $galley);
        $templateMgr->assign('journal', $journal);
        $templateMgr->assign('issueId', $issueId);
        $templateMgr->assign('galleyId', $galleyId);

        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'issue', 'view', $issueId), $issue->getIssueIdentification(false, true), true]]);
        $templateMgr->assign('issueHeadingTitle', __('issue.viewIssue'));
        $templateMgr->assign('locale', AppLocale::getLocale());

        $templateMgr->display('issue/issueGalley.tpl');
    }

    /**
     * Issue galley interstitial page for non-PDF files
     * @param array $args ($issueId, $galleyId)
     * @param PKPRequest $request
     * @return void
     */
    public function viewDownloadInterstitial($args, $request) {
        $issueId  = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;

        $this->validate(null, $request, $issueId, $galleyId);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $issue   = $this->getIssue();
        $galley  = $this->getGalley();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('issueId', $issueId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('galley', $galley);
        $templateMgr->assign('issue', $issue);
        $templateMgr->display('issue/interstitial.tpl');
    }

    /**
     * View an issue galley file (inline file).
     * @param array $args ($issueId, $galleyId)
     * @param PKPRequest $request
     * @return void
     */
    public function viewFile($args, $request) {
        $issueId  = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;

        $this->validate(null, $request, $issueId, $galleyId);
        $this->_showIssueGalley($request, true);
    }

    /**
     * Downloads an issue galley file
     * @param array $args ($issueId, $galleyId)
     * @param PKPRequest $request
     * @return void
     */
    public function download($args, $request) {
        $issueId  = isset($args[0]) ? $args[0] : 0;
        $galleyId = isset($args[1]) ? $args[1] : 0;

        $this->validate(null, $request, $issueId, $galleyId);
        $this->_showIssueGalley($request, false);
    }

    /**
     * Get the retrieved issue
     * @return Issue
     */
    public function getIssue() {
        return $this->_issue;
    }

    /**
     * Set a retrieved issue
     * @param Issue $issue
     */
    public function setIssue($issue) {
        $this->_issue = $issue;
    }

    /**
     * Get the retrieved issue galley
     * @return IssueGalley
     */
    public function getGalley() {
        return $this->_galley;
    }

    /**
     * Set a retrieved issue galley
     * @param IssueGalley $galley
     */
    public function setGalley($galley) {
        $this->_galley = $galley;
    }

    /**
     * Validate the request.
     * Overrides parent to add issue-specific validation.
     * Supports both old signature validate($request, $issueId, $galleyId)
     * and new signature validate($requiredContexts, $request, $issueId, $galleyId).
     */
    public function validate($requiredContexts = null, $request = null, $issueId = null, $galleyId = null) {
        // Deteksi signature lama: validate($request, $issueId, $galleyId)
        if ($requiredContexts !== null && is_object($requiredContexts) && method_exists($requiredContexts, 'getRouter')) {
            $actualRequest = $requiredContexts;
            $actualIssueId = $request;
            $actualGalleyId = $issueId;

            $requiredContexts = null;
            $request          = $actualRequest;
            $issueId          = $actualIssueId;
            $galleyId         = $actualGalleyId;
        }

        if ($request === null) {
            return false;
        }

        $returner = parent::validate($requiredContexts, $request);

        if (!$issueId && !$galleyId) {
            return $returner;
        }

        if (!$issueId) $request->redirect(null, 'index');

        import('classes.issue.IssueAction');

        $journal   = $request->getJournal();
        $journalId = $journal->getId();
        $user      = $request->getUser();
        $userId    = $user ? $user->getId() : 0;
        $issue     = null;
        $galley    = null;

        $issueDao = DAORegistry::getDAO('IssueDAO');
        if ($journal->getSetting('enablePublicIssueId')) {
            $issue = $issueDao->getIssueByBestIssueId($issueId, $journalId);
        } else {
            $issue = $issueDao->getIssueById((int) $issueId, null, true);
        }

        if (!$issue || !$this->_isVisibleIssue($issue, $journalId)) $request->redirect(null, null, 'current');

        $this->setIssue($issue);

        if (!$galleyId) return true;

        $galleyDao = DAORegistry::getDAO('IssueGalleyDAO');
        if ($journal->getSetting('enablePublicGalleyId')) {
            $galley = $galleyDao->getGalleyByBestGalleyId($galleyId, $issue->getId());
        } else {
            $galley = $galleyDao->getGalley($galleyId, $issue->getId());
        }

        if (!$galley) $request->redirect(null, null, 'view', $issueId);

        $this->setGalley($galley);

        if (IssueAction::allowedIssuePrePublicationAccess($journal)) return true;

        if ($issue->getPublished()) {
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $isSubscribedDomain   = IssueAction::subscribedDomain($journal, $issueId);

            if (!$isSubscribedDomain && !Validation::isLoggedIn() && $journal->getSetting('restrictArticleAccess')) {
                Validation::redirectLogin();
            }

            if (!$isSubscribedDomain && $subscriptionRequired) {
                $subscribedUser = IssueAction::subscribedUser($journal, $issueId);

                if (!$subscribedUser) {
                    import('classes.payment.ojs.OJSPaymentManager');
                    $paymentManager = new OJSPaymentManager($request);

                    if ($paymentManager->purchaseIssueEnabled() || $paymentManager->membershipEnabled()) {
                        if ($paymentManager->onlyPdfEnabled() && !$galley->isPdfGalley()) return true;

                        if (!Validation::isLoggedIn()) {
                            Validation::redirectLogin("payment.loginRequired.forIssue");
                        }

                        $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
                        $dateEndMembership   = $user->getSetting('dateEndMembership', 0);
                        if ($completedPaymentDao->hasPaidPurchaseIssue($userId, (int) $issueId) || (!is_null($dateEndMembership) && $dateEndMembership > time())) {
                            return true;
                        } else {
                            $queuedPayment   = $paymentManager->createQueuedPayment($journalId, PAYMENT_TYPE_PURCHASE_ISSUE, $userId, (int) $issueId, $journal->getSetting('purchaseIssueFee'));
                            $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

                            $templateMgr = TemplateManager::getManager();
                            $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
                            exit;
                        }
                    }

                    if (!Validation::isLoggedIn()) {
                        Validation::redirectLogin("reader.subscriptionRequiredLoginText");
                    }
                    $request->redirect(null, 'about', 'subscriptions');
                }
            }
        } else {
            $request->redirect(null, 'index');
        }
        return true;
    }

    /**
     * Setup common template variables.
     * @see Handler::setupTemplate()
     * @return void
     */
    public function setupTemplate($request = NULL) {
        parent::setupTemplate();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_READER, LOCALE_COMPONENT_APP_EDITOR);
    }

    /**
     * Show an issue galley file (either inline or download)
     * @param PKPRequest $request
     * @param bool $inline
     * @return void
     */
    public function _showIssueGalley($request, $inline = false) {
        $journal = $request->getJournal();
        $issue   = $this->getIssue();
        $galley  = $this->getGalley();

        $galleyDao = DAORegistry::getDAO('IssueGalleyDAO');

        if (!HookRegistry::dispatch('IssueHandler::viewFile', [&$issue, &$galley])) {
            import('classes.file.IssueFileManager');
            $issueFileManager = new IssueFileManager($issue->getId());
            return $issueFileManager->downloadFile($galley->getFileId(), $inline);
        }
    }

    /**
     * Given an issue and journal id, return whether the current user can view the issue.
     * @param Issue $issue
     * @param int $journalId
     * @return bool
     */
    public static function _isVisibleIssue($issue, $journalId) {
        if (isset($issue) && ($issue->getPublished() || Validation::isEditor($journalId) || Validation::isLayoutEditor($journalId) || Validation::isProofreader($journalId)) && $issue->getJournalId() == $journalId) {
            return true;
        }
        return false;
    }

    /**
     * Given an issue, set up the template with all the required variables.
     * @param PKPRequest $request
     * @param Issue $issue
     * @param bool $showToc
     * @return void
     */
    public static function _setupIssueTemplate($request, $issue, $showToc = false) {
        $journal   = $request->getJournal();
        $journalId = $journal->getId();
        $templateMgr = TemplateManager::getManager();

        if (IssueHandler::_isVisibleIssue($issue, $journalId)) {

            $issueHeadingTitle = $issue->getIssueIdentification(false, true);
            $issueCrumbTitle   = $issue->getIssueIdentification(false, true);

            $locale = AppLocale::getLocale();

            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $coverPagePath     = $request->getBaseUrl() . '/';
            $coverPagePath    .= $publicFileManager->getJournalFilesPath($journalId) . '/';
            $templateMgr->assign('coverPagePath', $coverPagePath);
            $templateMgr->assign('locale', $locale);

            $coverLocale = $issue->getFileName($locale) ? $locale : $journal->getPrimaryLocale();
            if (!$showToc && $issue->getFileName($coverLocale) && $issue->getShowCoverPage($coverLocale) && !$issue->getHideCoverPageCover($coverLocale)) {
                $templateMgr->assign('fileName', $issue->getFileName($coverLocale));
                $templateMgr->assign('width', $issue->getWidth($coverLocale));
                $templateMgr->assign('height', $issue->getHeight($coverLocale));
                $templateMgr->assign('coverPageAltText', $issue->getCoverPageAltText($coverLocale));
                $templateMgr->assign('originalFileName', $issue->getOriginalFileName($coverLocale));
                $templateMgr->assign('coverLocale', $coverLocale);
                $showToc = false;
            } else {
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                $issueGalleys   = $issueGalleyDao->getGalleysByIssue($issue->getId());
                $templateMgr->assign('issueGalleys', $issueGalleys);

                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticles   = $publishedArticleDao->getPublishedArticlesInSections($issue->getId(), true);

                $publicFileManager = new PublicFileManager();
                $templateMgr->assign('publishedArticles', $publishedArticles);
                $showToc = true;
            }
            $templateMgr->assign('showToc', $showToc);
            $templateMgr->assign('issue', $issue);

            import('classes.issue.IssueAction');
            $subscriptionRequired    = IssueAction::subscriptionRequired($issue);
            $subscribedUser          = IssueAction::subscribedUser($journal);
            $subscribedDomain        = IssueAction::subscribedDomain($journal);
            $subscriptionExpiryPartial = $journal->getSetting('subscriptionExpiryPartial');

            if ($showToc && $subscriptionRequired && !$subscribedUser && !$subscribedDomain && $subscriptionExpiryPartial) {
                $templateMgr->assign('subscriptionExpiryPartial', true);

                $partial = IssueAction::subscribedUser($journal, $issue->getId());
                if (!$partial) IssueAction::subscribedDomain($journal, $issue->getId());
                $templateMgr->assign('issueExpiryPartial', $partial);

                $publishedArticleDao   = DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticlesTemp = $publishedArticleDao->getPublishedArticles($issue->getId());

                $articleExpiryPartial = [];
                foreach ($publishedArticlesTemp as $publishedArticle) {
                    $partial = IssueAction::subscribedUser($journal, $issue->getId(), $publishedArticle->getId());
                    if (!$partial) IssueAction::subscribedDomain($journal, $issue->getId(), $publishedArticle->getId());
                    $articleExpiryPartial[$publishedArticle->getId()] = $partial;
                }
                $templateMgr->assign('articleExpiryPartial', $articleExpiryPartial);
            }

            $templateMgr->assign('subscriptionRequired', $subscriptionRequired);
            $templateMgr->assign('subscribedUser', $subscribedUser);
            $templateMgr->assign('subscribedDomain', $subscribedDomain);
            $templateMgr->assign('showGalleyLinks', $journal->getSetting('showGalleyLinks'));

            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            if ($paymentManager->onlyPdfEnabled()) {
                $templateMgr->assign('restrictOnlyPdf', true);
            }
            if ($paymentManager->purchaseArticleEnabled()) {
                $templateMgr->assign('purchaseArticleEnabled', true);
            }

        } else {
            $issueCrumbTitle   = __('archive.issueUnavailable');
            $issueHeadingTitle = __('archive.issueUnavailable');
        }

        if ($issue && $styleFileName = $issue->getStyleFileName()) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $templateMgr->addStyleSheet(
                $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/' . $styleFileName
            );
        }

        $templateMgr->assign('pageCrumbTitleTranslated', $issueCrumbTitle);
        $templateMgr->assign('issueHeadingTitle', $issueHeadingTitle);
    }
}
?>