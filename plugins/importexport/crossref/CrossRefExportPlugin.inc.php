<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/CrossRefExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefExportPlugin
 * @ingroup plugins_importexport_crossref
 *
 * @brief CrossRef export/registration plugin.
 */

if (!class_exists('DOIExportPlugin')) {
    import('plugins.importexport.crossref.classes.DOIExportPlugin');
}

define('CROSSREF_STATUS_SUBMITTED', 'submitted');
define('CROSSREF_STATUS_COMPLETED', 'completed');
define('CROSSREF_STATUS_FAILED', 'failed');
define('CROSSREF_STATUS_REGISTERED', 'found');
define('CROSSREF_STATUS_MARKEDREGISTERED', 'markedRegistered');
define('CROSSREF_STATUS_NOT_DEPOSITED', 'notDeposited');

// DataCite API
define('CROSSREF_API_DEPOSIT_OK', 303);
define('CROSSREF_API_RESPONSE_OK', 200);
define('CROSSREF_API_URL', 'https://api.crossref.org/deposits');

// TESTING
// define('CROSSREF_API_URL', 'https://api.crossref.org/deposits?test=true');

define('CROSSREF_SEARCH_API', 'http://search.crossref.org/dois');
define('CROSSREF_WORKS_API', 'http://api.crossref.org/works/');

// The name of the settings used to save the registered DOI and the URL with the deposit status.
define('CROSSREF_DEPOSIT_STATUS', 'depositStatus');

class CrossRefExportPlugin extends DOIExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CrossRefExportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Implement template methods from ImportExportPlugin
    //
    /**
     * Get the name of this plugin.
     * @see ImportExportPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'CrossRefExportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see ImportExportPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.crossref.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see ImportExportPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.crossref.description');
    }

    /**
     * Register the plugin.
     * @see LazyLoadPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        if (!Config::getVar('general', 'installed')) {
            return false;
        }

        if ($success) {
            HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);
        }
        return $success;
    }

    //
    // Implement template methods from DOIExportPlugin
    //
    /**
     * Get the plugin ID.
     * @see DOIExportPlugin::getPluginId()
     * @return string
     */
    public function getPluginId(): string {
        return 'crossref';
    }

    /**
     * Get the class name of the settings form.
     * @see DOIExportPlugin::getSettingsFormClassName()
     * @return string
     */
    public function getSettingsFormClassName(): string {
        return 'CrossRefSettingsForm';
    }

    /**
     * Get all object types that can be exported/registered via this plugin.
     * @see DOIExportPlugin::getAllObjectTypes()
     * @return array
     */
    public function getAllObjectTypes(): array {
        return [
            'issue'   => DOI_EXPORT_ISSUES,
            'article' => DOI_EXPORT_ARTICLES
        ];
    }

    /**
     * Process a DOI activity request.
     * @see DOIExportPlugin::process()
     * @param Request $request
     * @param Journal $journal
     */
    public function process($request, $journal): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        if ($request->getUserVar('checkStatus')) {
            $articleIds = (array) $request->getUserVar('articleId');
            $errors = [];
            $articles = $this->_getObjectsFromIds(DOI_EXPORT_ARTICLES, $articleIds, (int) $journal->getId(), $errors);
            
            foreach ($articles as $article) {
                $this->updateDepositStatus($request, $journal, $article);
            }
            $request->redirect(
                null, null, null,
                ['plugin', $this->getName(), 'articles'],
                null
            );
        } else {
            parent::process($request, $journal);
        }
    }

    /**
     * Display a list of issues for export.
     * @see DOIExportPlugin::displayIssueList()
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displayIssueList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);
        
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $this->registerDaoHook('IssueDAO');
        $issueIterator = $issueDao->getPublishedIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $excludes = [];
        $allExcluded = true;
        $numArticles = [];
        $allArticlesRegistered = [];

        while ($issue = $issueIterator->next()) {
            $issueId = (int) $issue->getId();
            $excludes[$issueId] = true;
            $issueArticles = $publishedArticleDao->getPublishedArticles($issueId);
            $issueArticlesNo = 0;
            $allArticlesRegistered[$issueId] = true;
            
            foreach ($issueArticles as $issueArticle) {
                $articleRegistered = $issueArticle->getData($this->getPluginId() . '::registeredDoi');
                $errors = [];
                
                if ($this->canBeExported($issueArticle, $errors)) {
                    $excludes[$issueId] = false;
                    $allExcluded = false;
                    $issueArticlesNo++;
                }
                if ($allArticlesRegistered[$issueId] && !isset($articleRegistered)) {
                    $allArticlesRegistered[$issueId] = false;
                }
            }
            $numArticles[$issueId] = $issueArticlesNo;
        }

        $issueIterator = $issueDao->getPublishedIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

        $templateMgr->assign([
            'issues'                => $issueIterator,
            'allExcluded'           => $allExcluded,
            'excludes'              => $excludes,
            'numArticles'           => $numArticles,
            'allArticlesRegistered' => $allArticlesRegistered
        ]);

        $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
    }

    /**
     * Display a list of articles for export.
     * @see DOIExportPlugin::displayArticleList
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displayArticleList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        $filter = $templateMgr->get_template_vars('filter');
        
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articles = [];
        
        if ($filter) {
            if ($filter === CROSSREF_STATUS_NOT_DEPOSITED) {
                $allArticles = $publishedArticleDao->getBySetting($this->getDepositStatusSettingName(), null, (int) $journal->getId());
            } else {
                $allArticles = $publishedArticleDao->getBySetting($this->getDepositStatusSettingName(), $filter, (int) $journal->getId());
            }
        } else {
            $allArticles = $this->getAllPublishedArticles($journal);
        }

        $articleData = [];
        $errors = [];
        foreach ($allArticles as $article) {
            if ($this->canBeExported($article, $errors)) {
                $preparedArticle = $this->_prepareArticleData($article, $journal);
                if (is_array($preparedArticle)) {
                    $articleData[] = $preparedArticle;
                    $articles[] = $article;
                }
            }
        }

        $totalArticles = count($articleData);
        $rangeInfo = Handler::getRangeInfo('articles');
        if ($rangeInfo && $rangeInfo->isValid()) {
            $articleData = array_slice($articleData, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
        }
        
        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($articleData, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr->assign([
            'articles'                  => $iterator,
            'depositStatusSettingName'  => $this->getDepositStatusSettingName(),
            'depositStatusUrlSettingName'=> $this->getDepositStatusUrlSettingName(),
            'statusMapping'             => $this->getStatusMapping(),
            'isEditor'                  => Validation::isEditor((int) $journal->getId())
        ]);

        $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
    }

    /**
     * The selected issue can be exported if it contains an article that has a DOI.
     * The selected article can be exported if it has a DOI.
     * @see DOIExportPlugin::displayIssueList() 
     * @see DOIExportPlugin::displayArticleList()
     * @param Issue|PublishedArticle $foundObject
     * @param array $errors
     * @return bool
     */
    public function canBeExported($foundObject, &$errors): bool {
        if ($foundObject instanceof Issue) {
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $issueArticles = $publishedArticleDao->getPublishedArticles((int) $foundObject->getId());
            foreach ($issueArticles as $issueArticle) {
                if (parent::canBeExported($issueArticle, $errors)) {
                    return true;
                }
            }
        }
        if ($foundObject instanceof PublishedArticle) {
            return parent::canBeExported($foundObject, $errors);
        }
        return false;
    }

    /**
     * Prepare article data for display in the article list template.
     * @see DOIExportPlugin::generateExportFiles()
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param string $targetPath
     * @param Journal $journal
     * @param array $errors
     * @return array|bool
     */
    public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);

        $this->import('classes.CrossRefExportDom');
        $dom = new CrossRefExportDom($request, $this, $journal, $this->getCache());
        $doc = $dom->generate($objects);
        
        if ($doc === false) {
            $errors = $dom->getErrors();
            return false;
        }

        $exportFileName = $this->getTargetFileName($targetPath, $exportType);
        file_put_contents($exportFileName, XMLCustomWriter::getXML($doc));

        return [$exportFileName => $objects];
    }

    /**
     * Mark the DOI as registered in the system, so that it is not exported/registered again and update the status of the deposit.
     * @see DOIExportPlugin::processMarkRegistered()
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param Journal $journal
     */
    public function processMarkRegistered($request, $exportType, $objects, $journal): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->import('classes.CrossRefExportDom');
        $dom = new CrossRefExportDom($request, $this, $journal, $this->getCache());
        
        // NOTE: $statusUpdatePossible declare but not use
        // $statusUpdatePossible = $this->getSetting($journal->getId(), 'username') && $this->getSetting($journal->getId(), 'password');

        foreach ($objects as $object) {
            if ($object instanceof Issue) {
                $articlesByIssue = $dom->retrieveArticlesByIssue($object);
                foreach ($articlesByIssue as $article) {
                    if ($article->getPubId('doi')) {
                        $articleDao->updateSetting((int) $article->getId(), $this->getDepositStatusSettingName(), CROSSREF_STATUS_MARKEDREGISTERED, 'string');
                        $this->markRegistered($request, $article);
                    }
                }
            } else {
                if ($object->getPubId('doi')) {
                    $articleDao->updateSetting((int) $object->getId(), $this->getDepositStatusSettingName(), CROSSREF_STATUS_MARKEDREGISTERED, 'string');
                    $this->markRegistered($request, $object);
                }
            }
        }
    }

    /**
     * Register DOIs with the DOI registration agency.
     * @param mixed $request
     * @param Journal $journal
     * @param array $objects
     * @param string $filename
     * @return bool|array
     */
    public function registerDoi($request, $journal, $objects, $filename) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $curlCh = curl_init();
        $proxyHost = Config::getVar('proxy', 'http_host');
        if ($proxyHost) {
            curl_setopt($curlCh, CURLOPT_PROXY, $proxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $proxyUsername = Config::getVar('proxy', 'username');
            if ($proxyUsername) {
                $proxyPassword = Config::getVar('proxy', 'password');
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
            }
        }
        
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlCh, CURLOPT_POST, true);
        curl_setopt($curlCh, CURLOPT_HEADER, 1);
        curl_setopt($curlCh, CURLOPT_BINARYTRANSFER, true);

        $crossrefUsername = (string) $this->getSetting((int) $journal->getId(), 'username');
        $crossrefPassword = (string) $this->getSetting((int) $journal->getId(), 'password');

        curl_setopt($curlCh, CURLOPT_URL, CROSSREF_API_URL);
        curl_setopt($curlCh, CURLOPT_USERPWD, "$crossrefUsername:$crossrefPassword");

        if (!is_readable($filename)) {
            return [['plugins.importexport.crossref.register.error.mdsError', 'File is not readable.']];
        }
        
        $fh = fopen($filename, 'rb');
        $fileSize = (int) filesize($filename);

        $httpheaders = [
            'Content-Type: application/vnd.crossref.deposit+xml',
            'Content-Length: ' . $fileSize
        ];

        curl_setopt($curlCh, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($curlCh, CURLOPT_INFILE, $fh);
        curl_setopt($curlCh, CURLOPT_INFILESIZE, $fileSize);

        $response = curl_exec($curlCh);
        $status = curl_getinfo($curlCh, CURLINFO_HTTP_CODE);
        
        if (is_resource($fh)) {
            fclose($fh);
        }
        
        curl_close($curlCh);

        if ($response === false) {
            return [['plugins.importexport.crossref.register.error.mdsError', 'No response from server.']];
        } elseif ($status !== CROSSREF_API_DEPOSIT_OK) {
            return [['plugins.importexport.crossref.register.error.mdsError', "$status - $response"]];
        }

        foreach ($objects as $article) {
            if ($article instanceof Article) {
                $this->updateDepositStatus($request, $journal, $article);
            }
        }
        
        return true;
    }

    /**
     * This method checks the CrossRef APIs, if deposits and registration have been successful.
     * @param Request $request
     * @param Journal $journal
     * @param Article $article
     * @return bool
     */
    public function updateDepositStatus($request, $journal, $article): bool {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();

        $curlCh = curl_init();
        $proxyHost = Config::getVar('proxy', 'http_host');
        if ($proxyHost) {
            curl_setopt($curlCh, CURLOPT_PROXY, $proxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            
            $proxyUsername = Config::getVar('proxy', 'username');
            if ($proxyUsername) {
                $proxyPassword = Config::getVar('proxy', 'password');
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
            }
        }
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);

        $crossrefUsername = (string) $this->getSetting((int) $journal->getId(), 'username');
        $crossrefPassword = (string) $this->getSetting((int) $journal->getId(), 'password');
        curl_setopt($curlCh, CURLOPT_USERPWD, "$crossrefUsername:$crossrefPassword");

        $doi = urlencode((string) $article->getPubId('doi'));
        $params = 'filter=doi:' . $doi;
        curl_setopt(
            $curlCh,
            CURLOPT_URL,
            CROSSREF_API_URL . (strpos(CROSSREF_API_URL, '?') === false ? '?' : '&') . $params
        );

        $response = curl_exec($curlCh);

        if ($response && curl_getinfo($curlCh, CURLINFO_HTTP_CODE) === CROSSREF_API_RESPONSE_OK) {
            $decodedResponse = $jsonManager->decode($response);
            $pastDeposits = [];
            
            if (isset($decodedResponse->message->items) && is_array($decodedResponse->message->items)) {
                foreach ($decodedResponse->message->items as $item) {
                    $submittedAt = strtotime($item->{'submitted-at'} ?? 'now');
                    $pastDeposits[$submittedAt] = [
                        'status'   => $item->status ?? '',
                        'batch-id' => $item->{'batch-id'} ?? ''
                    ];
                }
            }

            if (count($pastDeposits) > 0) {
                $lastDeposit = $pastDeposits[max(array_keys($pastDeposits))];
                $lastStatus = $lastDeposit['status'];
                $lastBatchId = $lastDeposit['batch-id'];
                
                $statusUrlSettingName = $this->getDepositStatusUrlSettingName();
                $depositStatusSettingName = $this->getDepositStatusSettingName();
                
                if ($article->getData($statusUrlSettingName) !== '/deposits/' . $lastBatchId) {
                    $articleDao->updateSetting((int) $article->getId(), $statusUrlSettingName, '/deposits/' . $lastBatchId, 'string');
                }
                
                if ($lastStatus === CROSSREF_STATUS_COMPLETED) {
                    curl_setopt($curlCh, CURLOPT_URL, CROSSREF_WORKS_API . $doi);
                    $worksResponse = curl_exec($curlCh);
                    
                    if ($worksResponse && curl_getinfo($curlCh, CURLINFO_HTTP_CODE) === CROSSREF_API_RESPONSE_OK) {
                        $article->setData($depositStatusSettingName, CROSSREF_STATUS_REGISTERED);
                        $articleDao->updateSetting((int) $article->getId(), $depositStatusSettingName, CROSSREF_STATUS_REGISTERED, 'string');
                        $this->markRegistered($request, $article);
                        curl_close($curlCh);
                        return true;
                    }
                }
                
                if ($article->getData($depositStatusSettingName) !== $lastStatus) {
                    $article->setData($depositStatusSettingName, $lastStatus);
                    $articleDao->updateSetting((int) $article->getId(), $depositStatusSettingName, $lastStatus, 'string');
                }
                
                if ($article->getData($this->getPluginId() . '::' . DOI_EXPORT_REGDOI)) {
                    $articleDao->updateSetting((int) $article->getId(), $this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, 'string');
                }
            }
        }

        curl_close($curlCh);
        return false;
    }

    /**
     * Add scheduled tasks to the system cron tab.
     * @see AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args): bool {
        $taskFilesPath =& $args[0];
        $taskFilesPath[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'scheduledTasks.xml';
        return false;
    }

    /**
     * Get the name of the setting used to save the deposit status.
     * @return string
     */
    public function getDepositStatusSettingName(): string {
        return $this->getPluginId() . '::' . CROSSREF_DEPOSIT_STATUS;
    }

    /**
     * Get the name of the setting used to save the URL with the deposit status.
     * @return string
     */
    public function getDepositStatusUrlSettingName(): string {
        return $this->getPluginId() . '::' . CROSSREF_DEPOSIT_STATUS . 'Url';
    }

    /**
     * Get status mapping for the status display.
     * @return array
     */
    public function getStatusMapping(): array {
        return [
            CROSSREF_STATUS_SUBMITTED        => __('plugins.importexport.crossref.status.submitted'),
            CROSSREF_STATUS_COMPLETED        => __('plugins.importexport.crossref.status.completed'),
            CROSSREF_STATUS_FAILED           => __('plugins.importexport.crossref.status.failed'),
            CROSSREF_STATUS_REGISTERED       => __('plugins.importexport.crossref.status.registered'),
            CROSSREF_STATUS_MARKEDREGISTERED => __('plugins.importexport.crossref.status.markedRegistered')
        ];
    }

}
?>