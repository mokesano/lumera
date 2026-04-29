<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/doaj/DOAJPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOAJPlugin
 * @ingroup plugins_importexport_doaj
 *
 * @brief DOAJ import/export plugin
 */

import('lib.pkp.classes.xml.XMLCustomWriter');
import('classes.plugins.ImportExportPlugin');

// Export types.
define('DOAJ_EXPORT_ISSUES', 0x01);
define('DOAJ_EXPORT_ARTICLES', 0x02);

define('DOAJ_XSD_URL', 'http://doaj.org/static/doaj/doajArticles.xsd');

class DOAJPlugin extends ImportExportPlugin {

    /** @var object|null PubObjectCache */
    protected $_cache;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DOAJPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the cache object.
     * @return PubObjectCache
     */
    protected function _getCache() {
        if (!$this->_cache instanceof PubObjectCache) {
            // Instantiate the cache.
            if (!class_exists('PubObjectCache')) { // Bug #7848
                $this->import('classes.PubObjectCache');
            }
            $this->_cache = new PubObjectCache();
        }
        return $this->_cache;
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path to plugin
     * @return bool True iff plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath($inCore) . 'templates/';
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'DOAJPlugin';
    }

    /**
     * Get the display name for this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.doaj.displayName');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.doaj.description');
    }

    /**
     * Display the plugin
     * @param array $args
     * @param object $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager();
        parent::display($args, $request);
        $journal = $request->getJournal();

        $command = array_shift($args);

        switch ($command) {
            case 'unregistered':
                $this->_displayArticleList($templateMgr, $journal, true);
                break;
            case 'issues':
                $this->_displayIssueList($templateMgr, $journal);
                break;
            case 'articles':
                $this->_displayArticleList($templateMgr, $journal);
                break;
            case 'process':
                $this->_process($request, $journal);
                break;
            default:
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
        }
    }

    /**
     * Export a journal's content
     * @param object $journal
     * @param array $selectedObjects
     * @param string|null $outputFile
     * @return bool
     */
    protected function _exportJournal($journal, $selectedObjects, $outputFile = null): bool {
        $this->import('classes.DOAJExportDom');
        $doc = XMLCustomWriter::createDocument();

        $journalNode = DOAJExportDom::generateJournalDom($doc, $journal, $selectedObjects);
        $journalNode->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $journalNode->setAttribute('xsi:noNamespaceSchemaLocation', DOAJ_XSD_URL);
        XMLCustomWriter::appendChild($doc, $journalNode);

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'wb')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"journal-" . $journal->getId() . ".xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Label articles (on article or issue level) with a 'doaj::registered' flag
     * @param object $request PKPRequest
     * @param array $selectedObjects
     */
    protected function _markRegistered($request, $selectedObjects): void {
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->registerDaoHook('ArticleDAO');

        // check for articles
        $selectedArticles = $selectedObjects[DOAJ_EXPORT_ARTICLES] ?? [];
        if (is_array($selectedArticles) && !empty($selectedArticles)) {
            foreach($selectedArticles as $articleId) {
                $article = $articleDao->getArticle($articleId);
                $article->setData('doaj::registered', 1);
                $articleDao->updateArticle($article);
            }
        }

        // check for issues
        $selectedIssues = $selectedObjects[DOAJ_EXPORT_ISSUES] ?? [];
        if (is_array($selectedIssues) && !empty($selectedIssues)) {
            foreach($selectedIssues as $issueId) {
                $articles = $this->_retrieveArticlesByIssueId($issueId);
                foreach($articles as $article) {
                    $article->setData('doaj::registered', 1);
                    $articleDao->updateArticle($article);
                }
            }
        }

        // show message & redirect
        $this->_sendNotification(
            $request,
            'plugins.importexport.doaj.markRegistered.success',
            NOTIFICATION_TYPE_SUCCESS
        );
        
        $action = '';
        switch($request->getUserVar('target')) {
            case('article'):
                $action = 'articles';
                break;
            case('issue'):
                $action = 'issues';
                break;
            default: 
                assert(false);
        }
        $request->redirect(null, null, null, ['plugin', $this->getName(), $action]);
    }

    /**
     * Display a list of issues for export.
     * @param object $templateMgr TemplateManager
     * @param object $journal Journal
     */
    protected function _displayIssueList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        // Retrieve all published issues.
        AppLocale::requireComponents([LOCALE_COMPONENT_OJS_EDITOR]);
        $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
        $issueIterator = $issueDao->getPublishedIssues($journal->getId());

        // check whether all articles of an issue are doaj::registered or not
        $issues = [];
        while ($issue = $issueIterator->next()) {
            $issueId = $issue->getId();
            $articles = $this->_retrieveArticlesByIssueId($issueId);
            $allArticlesRegistered = true;

            foreach($articles as $article) {
                if (!$article->getData('doaj::registered')) {
                    $allArticlesRegistered = false;
                    break;
                }
            }

            $issue->setData('doaj::registered', $allArticlesRegistered);
            $issues[] = $issue;
            unset($issue);
        }
        unset($issueIterator);

        // Instantiate issue iterator.
        import('lib.pkp.classes.core.ArrayItemIterator');
        $rangeInfo = Handler::getRangeInfo('issues');
        $iterator = new ArrayItemIterator($issues, $rangeInfo->getPage(), $rangeInfo->getCount());

        // Prepare and display the issue template.
        $templateMgr->assign('issues', $iterator);
        $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
    }

    /**
     * Display a list of articles for export.
     * @param object $templateMgr TemplateManager
     * @param object $journal Journal
     * @param bool $unregistered
     */
    protected function _displayArticleList($templateMgr, $journal, bool $unregistered = false): void {
        $this->setBreadcrumbs([], true);

        if ($unregistered == false) {
            // Retrieve all published articles.
            $articles = $this->_getAllPublishedArticles($journal);
        } else {
            // Retrieve array elements without index "doaj::registered"
            // Replaced deprecated create_function with closure
            $articles = array_filter(
                $this->_getAllPublishedArticles($journal), 
                function($article) {
                    return !$article["article"]->getData("doaj::registered");
                }
            );
        }
        
        // Paginate articles.
        $totalArticles = count($articles);
        $rangeInfo = Handler::getRangeInfo('articles');
        if ($rangeInfo->isValid()) {
            $paginatedArticles = array_slice($articles, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
            
            // Instantiate article iterator.
            import('lib.pkp.classes.core.VirtualArrayIterator');
            $iterator = new VirtualArrayIterator($paginatedArticles, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());

            // Prepare and display the article template.
            $templateMgr->assign('articles', $iterator);
            $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
        }
    }

    /**
     * Retrieve all published articles.
     * @param object $journal Journal
     * @return array
     */
    protected function _getAllPublishedArticles($journal): array {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleIterator = $publishedArticleDao->getPublishedArticlesByJournalId($journal->getId());

        // Return articles from published issues only.
        $articles = [];
        while ($article = $articleIterator->next()) {
            $articles[] = $this->_prepareArticleData($article, $journal);
            unset($article);
        }
        unset($articleIterator);

        return $articles;
    }

    /**
     * Return the issue of an article.
     *
     * The issue will be cached if it is not yet cached.
     *
     * @param object $article Article
     * @param object $journal Journal
     *
     * @return Issue
     */
    protected function _getArticleIssue($article, $journal) {
        $issueId = $article->getIssueId();

        // Retrieve issue if not yet cached.
        $cache = $this->_getCache();
        if (!$cache->isCached('issues', (int) $issueId)) {
            $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
            $issue = $issueDao->getIssueById($issueId, $journal->getId(), true);
            assert($issue instanceof Issue);
            $nullVar = null;
            $cache->add($issue, $nullVar);
            unset($issue);
        }

        return $cache->get('issues', (int) $issueId);
    }

    /**
     * Retrieve all articles for the given issue
     * and commit them to the cache.
     * @param int $issueId Issue ID
     * @return array
     */
    protected function _retrieveArticlesByIssueId($issueId): array {
        $articlesByIssue = [];
        $cache = $this->_getCache();
        $nullVar = null;

        if (!$cache->isCached('articlesByIssue', (int) $issueId)) {
            $articleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $articles = $articleDao->getPublishedArticles($issueId);
            if (!empty($articles)) {
                foreach ($articles as $article) {
                    $cache->add($article, $nullVar);
                    unset($article);
                }
                $cache->markComplete('articlesByIssue', (int) $issueId);
                $articlesByIssue = $cache->get('articlesByIssue', (int) $issueId);
            }
        }
        return $articlesByIssue;
    }

    /**
     * Identify the issue of the given article.
     * @param object $article PublishedArticle
     * @param object $journal Journal
     * @return array|null Return prepared article data or
     * null if the article is not from a published issue.
     */
    protected function _prepareArticleData($article, $journal): ?array {
        $nullVar = null;

        // Add the article to the cache.
        $cache = $this->_getCache();
        $cache->add($article, $nullVar);

        // Retrieve the issue.
        $issue = $this->_getArticleIssue($article, $journal);

        if ($issue->getPublished()) {
            return [
                'article' => $article,
                'issue' => $issue
            ];
        } else {
            return null;
        }
    }

    /**
     * Return the object types supported by this plug-in.
     * @return array An array with object names and the
     * corresponding export types.
     */
    protected function _getAllObjectTypes(): array {
        return [
            'issue' => DOAJ_EXPORT_ISSUES,
            'article' => DOAJ_EXPORT_ARTICLES,
        ];
    }

    /**
     * Process a request.
     * @param object $request PKPRequest
     * @param object $journal Journal
     * @return mixed
     */
    protected function _process($request, $journal) {
        $objectTypes = $this->_getAllObjectTypes();
        $target = $request->getUserVar('target');
        $selectedIds = [];
        $action = '';
        
        switch($target) {
            case('article'):
                $action = 'articles';
                $selectedIds = (array) $request->getUserVar('articleId');
                break;
            case('issue'):
                $action = 'issues';
                $selectedIds = (array) $request->getUserVar('issueId');
                break;
            default: 
                assert(false);
        }
        
        if (empty($selectedIds)) {
            $request->redirect(null, null, null, ['plugin', $this->getName(), $action]);
        }

        $selectedObjects = [$objectTypes[$target] => $selectedIds];

        if ($request->getUserVar('export')) {
            return $this->_exportJournal($journal, $selectedObjects);
        }
        if ($request->getUserVar('markRegistered')) {
            $this->_markRegistered($request, $selectedObjects);
        }
        return false;
    }

    /**
     * Register the hook that adds an
     * additional field name to objects.
     * @param string $daoName
     */
    public function registerDaoHook($daoName): void {
        HookRegistry::register(strtolower_codesafe($daoName) . '::getAdditionalFieldNames', [$this, '_getAdditionalFieldNames']);
    }

    /**
     * Hook callback that returns the "daoj:registered" flag
     * @param string $hookName
     * @param array $args
     */
    public function _getAdditionalFieldNames($hookName, $args): void {
        assert(count($args) == 2);
        $returner =& $args[1];
        assert(is_array($returner));
        $returner[] = 'doaj::registered';
    }

    /**
     * Add a notification.
     * @param object $request Request
     * @param string $message An i18n key.
     * @param int $notificationType One of the NOTIFICATION_TYPE_* constants.
     * @param string|null $param An additional parameter for the message.
     */
    protected function _sendNotification($request, $message, $notificationType, $param = null): void {
        static $notificationManager = null;

        if (is_null($notificationManager)) {
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
        }

        if (!is_null($param)) {
            $params = ['param' => $param];
        } else {
            $params = null;
        }

        $user = $request->getUser();
        $notificationManager->createTrivialNotification(
            $user->getId(),
            $notificationType,
            ['contents' => __($message, $params)]
        );
    }
}

?>