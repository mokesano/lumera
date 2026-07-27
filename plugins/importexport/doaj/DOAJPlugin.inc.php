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

    /** @var PubObjectCache|null */
    protected ?PubObjectCache $_cache = null;

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
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the cache object.
     * @return PubObjectCache
     */
    protected function _getCache() {
        if (!$this->_cache instanceof PubObjectCache) {
            if (!class_exists('PubObjectCache')) {
                $this->import('classes.PubObjectCache');
            }
            $this->_cache = new PubObjectCache();
        }
        return $this->_cache;
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * @see PKPPlugin::getTemplatePath()
     * @param bool $inCore
     * @return string
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath() . 'templates/';
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
     * @param mixed $request
     */
    public function display($args, $request): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $templateMgr = TemplateManager::getManager($request);
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
     * @param Journal $journal
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
            if (($h = fopen($outputFile, 'wb')) === false) {
                return false;
            }
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"journal-" . (int) $journal->getId() . ".xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Label articles (on article or issue level) with a 'doaj::registered' flag
     * @param mixed $request
     * @param array $selectedObjects
     */
    protected function _markRegistered($request, $selectedObjects): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->registerDaoHook('ArticleDAO');

        $selectedArticles = $selectedObjects[DOAJ_EXPORT_ARTICLES] ?? [];
        if (is_array($selectedArticles) && !empty($selectedArticles)) {
            foreach ($selectedArticles as $articleId) {
                $article = $articleDao->getArticle((int) $articleId);
                if ($article) {
                    $article->setData('doaj::registered', 1);
                    $articleDao->updateArticle($article);
                }
            }
        }

        $selectedIssues = $selectedObjects[DOAJ_EXPORT_ISSUES] ?? [];
        if (is_array($selectedIssues) && !empty($selectedIssues)) {
            foreach ($selectedIssues as $issueId) {
                $articles = $this->_retrieveArticlesByIssueId((int) $issueId);
                foreach ($articles as $article) {
                    $article->setData('doaj::registered', 1);
                    $articleDao->updateArticle($article);
                }
            }
        }

        $this->_sendNotification(
            $request,
            'plugins.importexport.doaj.markRegistered.success',
            NOTIFICATION_TYPE_SUCCESS
        );
        
        $action = '';
        $target = (string) $request->getUserVar('target');
        switch ($target) {
            case 'article':
                $action = 'articles';
                break;
            case 'issue':
                $action = 'issues';
                break;
            default: 
                throw new \RuntimeException('Invalid target for markRegistered.');
        }
        $request->redirect(null, null, null, ['plugin', $this->getName(), $action]);
    }

    /**
     * Display a list of issues for export.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    protected function _displayIssueList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issueIterator = $issueDao->getPublishedIssues((int) $journal->getId());

        $issues = [];
        while ($issue = $issueIterator->next()) {
            $issueId = (int) $issue->getId();
            $articles = $this->_retrieveArticlesByIssueId($issueId);
            $allArticlesRegistered = true;

            foreach ($articles as $article) {
                if (!$article->getData('doaj::registered')) {
                    $allArticlesRegistered = false;
                    break;
                }
            }

            $issue->setData('doaj::registered', $allArticlesRegistered);
            $issues[] = $issue;
        }

        import('lib.pkp.classes.core.ArrayItemIterator');
        $rangeInfo = Handler::getRangeInfo('issues');
        $iterator = new ArrayItemIterator($issues, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr->assign('issues', $iterator);
        $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
    }

    /**
     * Display a list of articles for export.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     * @param bool $unregistered
     */
    protected function _displayArticleList($templateMgr, $journal, bool $unregistered = false): void {
        $this->setBreadcrumbs([], true);

        if ($unregistered === false) {
            $articles = $this->_getAllPublishedArticles($journal);
        } else {
            $articles = array_filter(
                $this->_getAllPublishedArticles($journal), 
                function($articleData) {
                    return isset($articleData['article']) && !$articleData['article']->getData('doaj::registered');
                }
            );
        }
        
        $totalArticles = count($articles);
        $rangeInfo = Handler::getRangeInfo('articles');
        
        if ($rangeInfo && $rangeInfo->isValid()) {
            $paginatedArticles = array_slice($articles, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
            
            import('lib.pkp.classes.core.VirtualArrayIterator');
            $iterator = new VirtualArrayIterator($paginatedArticles, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());

            $templateMgr->assign('articles', $iterator);
            $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
        }
    }

    /**
     * Retrieve all published articles.
     * @param Journal $journal
     * @return array
     */
    protected function _getAllPublishedArticles($journal): array {
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleIterator = $publishedArticleDao->getPublishedArticlesByJournalId((int) $journal->getId());

        $articles = [];
        while ($article = $articleIterator->next()) {
            $prepared = $this->_prepareArticleData($article, $journal);
            if ($prepared !== null) {
                $articles[] = $prepared;
            }
        }

        return $articles;
    }

    /**
     * Return the issue of an article.
     *
     * The issue will be cached if it is not yet cached.
     *
     * @param PublishedArticle $article
     * @param Journal $journal
     * @return Issue|null
     */
    protected function _getArticleIssue($article, $journal) {
        $issueId = (int) $article->getIssueId();
        $cache = $this->_getCache();
        
        if (!$cache->isCached('issues', $issueId)) {
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($issueId, (int) $journal->getId(), true);
            
            if ($issue instanceof Issue) {
                $cache->add($issue, null);
            } else {
                return null;
            }
        }

        return $cache->get('issues', $issueId);
    }

    /**
     * Retrieve all articles for the given issue and commit them to the cache.
     * @param int $issueId Issue ID
     * @return array
     */
    protected function _retrieveArticlesByIssueId($issueId): array {
        $issueId = (int) $issueId;
        $cache = $this->_getCache();

        if (!$cache->isCached('articlesByIssue', $issueId)) {
            /** @var PublishedArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $articles = $articleDao->getPublishedArticles($issueId);
            if (!empty($articles)) {
                foreach ($articles as $article) {
                    $cache->add($article, null);
                }
                $cache->markComplete('articlesByIssue', $issueId);
            }
        }
        
        $result = $cache->get('articlesByIssue', $issueId);
        return is_array($result) ? $result : [];
    }

    /**
     * Identify the issue of the given article.
     * @param PublishedArticle $article
     * @param Journal $journal
     * @return array|null
     */
    protected function _prepareArticleData($article, $journal): ?array {
        $cache = $this->_getCache();
        $cache->add($article, null);

        $issue = $this->_getArticleIssue($article, $journal);

        if ($issue && $issue->getPublished()) {
            return [
                'article' => $article,
                'issue' => $issue
            ];
        }
        
        return null;
    }

    /**
     * Return the object types supported by this plug-in.
     * @return array
     */
    protected function _getAllObjectTypes(): array {
        return [
            'issue' => DOAJ_EXPORT_ISSUES,
            'article' => DOAJ_EXPORT_ARTICLES,
        ];
    }

    /**
     * Process a request.
     * @param mixed $request
     * @param Journal $journal
     * @return mixed
     */
    protected function _process($request, $journal) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $objectTypes = $this->_getAllObjectTypes();
        $target = (string) $request->getUserVar('target');
        $selectedIds = [];
        $action = '';
        
        switch ($target) {
            case 'article':
                $action = 'articles';
                $selectedIds = (array) $request->getUserVar('articleId');
                break;
            case 'issue':
                $action = 'issues';
                $selectedIds = (array) $request->getUserVar('issueId');
                break;
            default: 
                throw new \RuntimeException('Invalid target in process.');
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
     * Register the hook that adds an additional field name to objects.
     * @param string $daoName
     */
    public function registerDaoHook($daoName): void {
        HookRegistry::register(strtolower_codesafe($daoName) . '::getAdditionalFieldNames', [$this, '_getAdditionalFieldNames']);
    }

    /**
     * Hook callback that returns the "doaj:registered" flag
     * @param string $hookName
     * @param array $args
     */
    public function _getAdditionalFieldNames($hookName, $args): void {
        if (count($args) < 2) {
            return;
        }
        $returner =& $args[1];
        if (is_array($returner)) {
            $returner[] = 'doaj::registered';
        }
    }

    /**
     * Add a notification.
     * @param mixed $request
     * @param string $message
     * @param int $notificationType
     * @param string|null $param
     */
    protected function _sendNotification($request, $message, $notificationType, $param = null): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        static $notificationManager = null;

        if ($notificationManager === null) {
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
        }

        $params = $param !== null ? ['param' => $param] : null;
        $user = $request->getUser();
        
        if ($user) {
            $notificationManager->createTrivialNotification(
                (int) $user->getId(),
                $notificationType,
                ['contents' => __($message, $params)]
            );
        }
    }

}
?>