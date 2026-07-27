<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/datacite/DataciteExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataciteExportPlugin
 * @ingroup plugins_importexport_datacite
 *
 * @brief DataCite export/registration plugin.
 */

if (!class_exists('DOIExportPlugin')) {
    import('plugins.importexport.datacite.classes.DOIExportPlugin');
}

// DataCite API
define('DATACITE_API_RESPONSE_OK', 201);
define('DATACITE_API_URL', 'https://mds.datacite.org/');

// Test DOI prefix
define('DATACITE_API_TESTPREFIX', '10.5072');

class DataciteExportPlugin extends DOIExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataciteExportPlugin() {
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
        return 'DataciteExportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see ImportExportPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.datacite.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see ImportExportPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.datacite.description');
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
        return 'datacite';
    }

    /**
     * Get the class name of the settings form.
     * @see DOIExportPlugin::getSettingsFormClassName()
     * @return string
     */
    public function getSettingsFormClassName(): string {
        return 'DataciteSettingsForm';
    }

    /**
     * Get all object types that can be exported/registered via this plugin.
     * @see DOIExportPlugin::getAllObjectTypes()
     * @return array
     */
    public function getAllObjectTypes(): array {
        $objectTypes = parent::getAllObjectTypes();
        $objectTypes['suppFile'] = DOI_EXPORT_SUPPFILES;
        return $objectTypes;
    }

    /**
     * Display a list of all yet unregistered objects.
     * @see DOIExportPlugin::displayAllUnregisteredObjects()
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     * @return void
     */
    public function displayAllUnregisteredObjects($templateMgr, $journal): void {
        $templateMgr->assign('suppFiles', $this->_getUnregisteredSuppFiles($journal));
        parent::displayAllUnregisteredObjects($templateMgr, $journal);
    }

    /**
     * Get a string representation of the object.
     * @see DOIExportPlugin::getObjectName()
     * @param int $exportType
     * @return string
     */
    public function getObjectName($exportType): string {
        if ($exportType === DOI_EXPORT_SUPPFILES) {
            return 'supp-file';
        }
        return parent::getObjectName($exportType);
    }

    /**
     * Display a list of supplementary files for export.
     * @see DOIExportPlugin::displaySuppFileList()
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     * @return void
     */
    public function displaySuppFileList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        $allArticles = $this->getAllPublishedArticles($journal);

        $this->registerDaoHook('SuppFileDAO');
        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFiles = [];
        
        foreach ($allArticles as $article) {
            $articleSuppFiles = $suppFileDao->getSuppFilesByArticle((int) $article->getId());
            foreach ($articleSuppFiles as $suppFile) {
                if ($suppFile->getPubId('doi')) {
                    $suppFiles[] = $suppFile;
                }
            }
        }

        $totalSuppFiles = count($suppFiles);
        $rangeInfo = Handler::getRangeInfo('suppFiles');
        
        if ($rangeInfo && $rangeInfo->isValid()) {
            $suppFiles = array_slice($suppFiles, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
        }

        $suppFileData = [];
        foreach ($suppFiles as $suppFile) {
            $preparedSuppFile = $this->_prepareSuppFileData($suppFile, $journal);
            if (is_array($preparedSuppFile)) {
                $suppFileData[] = $preparedSuppFile;
            }
        }

        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($suppFileData, $totalSuppFiles, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr->assign('suppFiles', $iterator);
        $templateMgr->display($this->getTemplatePath() . 'suppFiles.tpl');
    }

    /**
     * Generate export files for the given objects.
     * @see DOIExportPlugin::generateExportFiles()
     * @param mixed $request
     * @param int $exportType
     * @param array $objects
     * @param string $targetPath
     * @param Journal $journal
     * @param array $errors
     * @return array|bool
     */
    public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);

        $this->import('classes.DataciteExportDom');
        $exportFiles = [];
        
        foreach ($objects as $object) {
            $dom = new DataciteExportDom($request, $this, $journal, $this->getCache());
            $doc = $dom->generate($object);
            
            if ($doc === false) {
                $this->cleanTmpfiles($targetPath, array_keys($exportFiles));
                $errors = $dom->getErrors();
                return false;
            }

            $exportFile = $this->getTargetFileName($targetPath, $exportType, (int) $object->getId());
            file_put_contents($exportFile, XMLCustomWriter::getXML($doc));
            
            $fileManager = new FileManager();
            $fileManager->setMode($exportFile, FILE_MODE_MASK);
            $exportFiles[$exportFile] = [$object];
        }

        return $exportFiles;
    }

    /**
     * Register DOIs with the DOI registration agency.
     * @see DOIExportPlugin::registerDoi()
     * @param mixed $request
     * @param Journal $journal
     * @param array $objects
     * @param string $file
     * @return bool|array
     */
    public function registerDoi($request, $journal, $objects, $file) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        if (count($objects) !== 1) {
            return [['plugins.importexport.common.register.error.mdsError', 'DataCite requires exactly one object per file.']];
        }
        $object = $objects[0];

        $doi = $object->getPubId('doi');
        if (empty($doi)) {
            return [['plugins.importexport.common.register.error.mdsError', 'No DOI assigned to object.']];
        }
        
        if ($this->isTestMode($request)) {
            $doi = PKPString::regexp_replace('#^[^/]+/#', DATACITE_API_TESTPREFIX . '/', $doi);
        }
        
        $url = $this->_getObjectUrl($request, $journal, $object);
        if (empty($url)) {
            return [['plugins.importexport.common.register.error.mdsError', 'Could not determine object URL.']];
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

        $crossrefUsername = (string) $this->getSetting((int) $journal->getId(), 'username');
        $crossrefPassword = (string) $this->getSetting((int) $journal->getId(), 'password');
        curl_setopt($curlCh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curlCh, CURLOPT_USERPWD, "$crossrefUsername:$crossrefPassword");
        curl_setopt($curlCh, CURLOPT_SSL_VERIFYPEER, false);

        if (!is_readable($file)) {
            return [['plugins.importexport.common.register.error.mdsError', 'File is not readable.']];
        }
        
        $payload = file_get_contents($file);
        if ($payload === false || empty($payload)) {
            return [['plugins.importexport.common.register.error.mdsError', 'File is empty or unreadable.']];
        }

        curl_setopt($curlCh, CURLOPT_URL, DATACITE_API_URL . 'metadata');
        curl_setopt($curlCh, CURLOPT_HTTPHEADER, ['Content-Type: application/xml;charset=UTF-8']);
        curl_setopt($curlCh, CURLOPT_POSTFIELDS, $payload);

        $result = true;
        $response = curl_exec($curlCh);
        
        if ($response === false) {
            $result = [['plugins.importexport.common.register.error.mdsError', 'No response from server.']];
        } else {
            $status = curl_getinfo($curlCh, CURLINFO_HTTP_CODE);
            if ($status !== DATACITE_API_RESPONSE_OK) {
                $result = [['plugins.importexport.common.register.error.mdsError', "$status - $response"]];
            }
        }

        if ($result === true) {
            $payload = "doi=$doi\nurl=$url";
            curl_setopt($curlCh, CURLOPT_URL, DATACITE_API_URL . 'doi');
            curl_setopt($curlCh, CURLOPT_HTTPHEADER, ['Content-Type: text/plain;charset=UTF-8']);
            curl_setopt($curlCh, CURLOPT_POSTFIELDS, $payload);

            $response = curl_exec($curlCh);
            if ($response === false) {
                $result = [['plugins.importexport.common.register.error.mdsError', 'No response from server.']];
            } else {
                $status = curl_getinfo($curlCh, CURLINFO_HTTP_CODE);
                if ($status !== DATACITE_API_RESPONSE_OK) {
                    $result = [['plugins.importexport.common.register.error.mdsError', "$status - $response"]];
                }
            }
        }

        curl_close($curlCh);

        if ($result === true) {
            $this->markRegistered($request, $object, DATACITE_API_TESTPREFIX);
        }

        return $result;
    }

    /**
     * Identify DAO and DAO method to extract objects.
     * @see DOIExportPlugin::getDaoName()
     * @param int $exportType
     * @return array
     */
    public function getDaoName($exportType): array {
        if ($exportType === DOI_EXPORT_SUPPFILES) {
            return ['SuppFileDAO', 'getSuppFile'];
        }
        return parent::getDaoName($exportType);
    }

    /**
     * Return a translation key for the "object not found" error.
     * @see DOIExportPlugin::getObjectNotFoundErrorKey()
     * @param int $exportType
     * @return string
     */
    public function getObjectNotFoundErrorKey($exportType): string {
        if ($exportType === DOI_EXPORT_SUPPFILES) {
            return 'plugins.importexport.datacite.export.error.suppFileNotFound';
        }
        return parent::getObjectNotFoundErrorKey($exportType);
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

    //
    // Private helper methods
    //
    /**
     * Retrieve all unregistered supplementary files and their corresponding issues and articles.
     * @param Journal $journal
     * @return array
     */
    public function _getUnregisteredSuppFiles($journal): array {
        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFiles = $suppFileDao->getSuppFilesBySetting($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, null, (int) $journal->getId());

        $suppFileData = [];
        foreach ($suppFiles as $suppFile) {
            $preparedSuppFile = $this->_prepareSuppFileData($suppFile, $journal);
            if (is_array($preparedSuppFile)) {
                $suppFileData[] = $preparedSuppFile;
            }
        }
        return $suppFileData;
    }

    /**
     * Identify published article and issue of the given supp file.
     * @param SuppFile $suppFile
     * @param Journal $journal
     * @return array|null
     */
    public function _prepareSuppFileData($suppFile, $journal): ?array {
        $suppFileData = $this->prepareArticleFileData($suppFile, $journal);
        if (!is_array($suppFileData)) {
            return null;
        }

        $suppFileData['suppFile'] = $suppFile;
        return $suppFileData;
    }

    /**
     * Get the canonical URL of an object.
     * @param mixed $request
     * @param Journal $journal
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @return string|null
     */
    public function _getObjectUrl($request, $journal, $object) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $article = null;

        if ($object instanceof ArticleFile) {
            $articleData = $this->prepareArticleFileData($object, $journal);
            if (is_array($articleData) && isset($articleData['article'])) {
                $article = $articleData['article'];
            }
        }

        $url = null;
        switch (true) {
            case $object instanceof Issue:
                $url = $router->url($request, $journal->getPath(), 'issue', 'view', [$object->getBestIssueId($journal)]);
                break;

            case $object instanceof PublishedArticle:
                $url = $router->url($request, $journal->getPath(), 'article', 'view', [$object->getBestArticleId($journal)]);
                break;

            case $object instanceof ArticleGalley:
                if ($article instanceof PublishedArticle) {
                    $url = $router->url($request, $journal->getPath(), 'article', 'view', [(int) $article->getBestArticleId($journal), $object->getBestGalleyId($journal)]);
                }
                break;

            case $object instanceof SuppFile:
                if ($article instanceof PublishedArticle) {
                    $url = $router->url($request, $journal->getPath(), 'article', 'downloadSuppFile', [(int) $article->getBestArticleId($journal), $object->getBestSuppFileId($journal)]);
                }
                break;
        }

        if ($url && $this->isTestMode($request)) {
            $url = PKPString::regexp_replace('#://[^\s]+/index.php#', '://example.com/index.php', $url);
        }
        return $url;
    }

}
?>