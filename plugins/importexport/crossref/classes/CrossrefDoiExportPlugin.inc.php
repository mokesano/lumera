<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/CrossrefDoiExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossrefDoiExportPlugin
 * @ingroup plugins_importexport_crossref_classes
 *
 * @brief Base class for the Crossref DOI export/registration plugin.
 */

import('classes.plugins.ImportExportPlugin');

if (!defined('DOI_EXPORT_ISSUES')) {
    // Export types.
    define('DOI_EXPORT_ISSUES', 0x01);
    define('DOI_EXPORT_ARTICLES', 0x02);
    define('DOI_EXPORT_GALLEYS', 0x03);
    define('DOI_EXPORT_SUPPFILES', 0x04);

    // Current registration state.
    define('DOI_OBJECT_NEEDS_UPDATE', 0x01);
    define('DOI_OBJECT_REGISTERED', 0x02);

    // Export file types.
    define('DOI_EXPORT_FILE_XML', 0x01);
    define('DOI_EXPORT_FILE_TAR', 0x02);

    // Configuration errors.
    define('DOI_EXPORT_CONFIGERROR_DOIPREFIX', 0x01);
    define('DOI_EXPORT_CONFIGERROR_SETTINGS', 0x02);

    // The name of the setting used to save the registered DOI.
    define('DOI_EXPORT_REGDOI', 'registeredDoi');
}

class CrossrefDoiExportPlugin extends ImportExportPlugin {

    //
    // Protected Properties
    //
    /** @var PubObjectCache|null */
    public ?PubObjectCache $_cache = null;

    /**
     * Get the publication object cache.
     * @return PubObjectCache
     */
    public function getCache(): PubObjectCache {
        if (!$this->_cache instanceof PubObjectCache) {
            if (!class_exists('PubObjectCache')) {
                $this->import('classes.PubObjectCache');
            }
            $this->_cache = new PubObjectCache();
        }
        return $this->_cache;
    }

    //
    // Private Properties
    //
    /** @var bool */
    public bool $_checkedForTar = false;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CrossrefDoiExportPlugin() {
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
    // Implement template methods from PKPPlugin
    //
    /**
     * Register the plugin.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();

        HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);

        return $success;
    }

    /**
     * Get the path to the templates.
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the path to the context-specific settings file.
     * @see PKPPlugin::getInstallSitePluginSettingsFile()
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'settings.xml';
    }

    /**
     * Get the locale filename for a specific locale.
     * @see PKPPlugin::getLocaleFilename($locale)
     * @param string $locale
     * @return array
     */
    public function getLocaleFilename($locale): array {
        $localeFilenames = parent::getLocaleFilename($locale);
        $localeFilenames[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'common.xml';
        return $localeFilenames;
    }

    //
    // Implement template methods from ImportExportPlugin
    //
    /**
     * Return the management verbs for this plugin.
     * @see ImportExportPlugin::getManagementVerbs()
     * @param array $verbs
     * @param Request|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        $verbs[] = ['settings', __('plugins.importexport.common.settings')];
        return $verbs;
    }

    /**
     * Display the plugin homepage.
     * @see ImportExportPlugin::display()
     * @param array $args
     * @param Request|null $request
     */
    public function display($args, $request) {
        parent::display($args, $request);
        $templateMgr = TemplateManager::getManager($request);
        $journal = $request->getJournal();
        $op = array_shift($args);

        switch ($op) {
            case '':
            case 'index':
                $this->_displayPluginHomePage($templateMgr, $journal);
                break;

            case 'all':
            case 'issues':
            case 'articles':
            case 'galleys':
            case 'suppFiles':
                $templateMgr->assign('testMode', $this->isTestMode($request) ? ['testMode' => 1] : []);
                $templateMgr->assign('filter', $request->getUserVar('filter'));
                $username = $this->getSetting($journal->getId(), 'username');
                $hasCredentials = !empty($username);
                if (!$hasCredentials && $this instanceof CrossRefExportPlugin) {
                    import('lib.wizdam.classes.services.DoiCredentialService');
                    $doiCredentials = DoiCredentialService::resolveForJournal($journal);
                    $hasCredentials = $doiCredentials->isConfigured();
                }
                $templateMgr->assign('hasCredentials', $hasCredentials);

                switch ($op) {
                    case 'issues':
                        $this->displayIssueList($templateMgr, $journal);
                        break;
                    case 'articles':
                        $this->displayArticleList($templateMgr, $journal);
                        break;
                    case 'galleys':
                        $this->_displayGalleyList($templateMgr, $journal);
                        break;
                    case 'suppFiles':
                        $this->displaySuppFileList($templateMgr, $journal);
                        break;
                    case 'all':
                        $this->displayAllUnregisteredObjects($templateMgr, $journal);
                        break;
                }
                break;

            case 'process':
                $this->process($request, $journal);
                break;

            default:
                $request->redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
                return;
        }
    }

    /**
     * Process a DOI activity request.
     * @see ImportExportPlugin::process()
     * @param Request $request
     * @param Journal $journal
     */
    public function process($request, $journal): void {
        $objectTypes = $this->getAllObjectTypes();
        $target = $request->getUserVar('target');
        $result = false;

        switch (true) {
            case (bool) $request->getUserVar('export'):
            case (bool) $request->getUserVar('register'):
            case (bool) $request->getUserVar('markRegistered'):
                if ($target === 'all') {
                    $exportSpec = [];
                    foreach ($objectTypes as $objectName => $exportType) {
                        $objectIds = (array) $request->getUserVar($objectName . 'Id');
                        if (!empty($objectIds)) {
                            $exportSpec[$exportType] = $objectIds;
                        }
                    }
                } else {
                    if (!isset($objectTypes[$target])) {
                        break;
                    }
                    $exportSpec = [$objectTypes[$target] => (array) $request->getUserVar($target . 'Id')];
                }

                if ($request->getUserVar('export')) {
                    $result = $this->exportObjects($request, $exportSpec, $journal);
                } elseif ($request->getUserVar('markRegistered')) {
                    foreach ($exportSpec as $exportType => $objectIds) {
                        if (is_scalar($objectIds)) {
                            $objectIds = [(int) $objectIds];
                        }
                        $errors = [];
                        $objects = $this->_getObjectsFromIds($exportType, $objectIds, $journal->getId(), $errors);
                        $this->processMarkRegistered($request, $exportType, $objects, $journal);
                    }
                    $listAction = $target . ($target === 'all' ? '' : 's');
                    $request->redirect(
                        null, null, null,
                        ['plugin', $this->getName(), $listAction],
                        $this->isTestMode($request) ? ['testMode' => 1] : null
                    );
                    break;
                } else {
                    $result = $this->registerObjects($request, $exportSpec, $journal);
                    if ($result === true) {
                        $this->_sendNotification(
                            $request,
                            'plugins.importexport.' . $this->getPluginId() . '.register.success',
                            NOTIFICATION_TYPE_SUCCESS
                        );
                        $listAction = $target . ($target === 'all' ? '' : 's');
                        $request->redirect(
                            null, null, null,
                            ['plugin', $this->getName(), $listAction],
                            $this->isTestMode($request) ? ['testMode' => 1] : null
                        );
                    }
                }
                break;
            case (bool) $request->getUserVar('reset'):
                $ids = (array) $request->getUserVar($target . 'Id');
                if (isset($objectTypes[$target])) {
                    $result = $this->resetRegistration($objectTypes[$target], array_shift($ids), $journal);
                    if ($result === true) {
                        $request->redirect(
                            null, null, null,
                            ['plugin', $this->getName(), $target . 's'],
                            $this->isTestMode($request) ? ['testMode' => 1] : null
                        );
                    }
                }
                break;
        }

        if ($result !== true && is_array($result)) {
            foreach ($result as $error) {
                if (is_array($error) && count($error) >= 1) {
                    $this->_sendNotification(
                        $request,
                        $error[0],
                        NOTIFICATION_TYPE_ERROR,
                        $error[1] ?? null
                    );
                }
            }
            $request->redirect(null, null, null, ['plugin', $this->getName()]);
        }
    }

    /**
     * CLI execution.
     * @see ImportExportPlugin::executeCLI()
     * @param string $scriptName
     * @param array $args
     */
    public function executeCLI($scriptName, $args): void {
        $result = [];
        $journal = null;
        $objectType = null;
        $xmlFile = null;

        AppLocale::requireComponents([LOCALE_COMPONENT_APPLICATION_COMMON]);

        $command = strtolower_codesafe(array_shift($args) ?? '');
        if (!in_array($command, ['export', 'register'], true)) {
            $result = false;
        }

        if ($command === 'export' && is_array($result)) {
            $xmlFile = array_shift($args);
            if (empty($xmlFile)) {
                $result = false;
            }
        }

        if (is_array($result)) {
            $journalPath = array_shift($args) ?? '';
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao ? $journalDao->getJournalByPath($journalPath) : null;

            if (!$journal) {
                if ($journalPath !== '') {
                    $result[] = ['plugins.importexport.common.export.error.unknownJournal', $journalPath];
                } elseif (empty($result)) {
                    $result = false;
                }
            }
        }

        if (is_array($result) && empty($result)) {
            $objectTypeRaw = array_shift($args);
            if ($objectTypeRaw !== null) {
                $objectType = strtolower_codesafe($objectTypeRaw);
                if (substr($objectType, -1) === 's') {
                    $objectType = substr($objectType, 0, -1);
                }
                if ($objectType === 'suppfile') {
                    $objectType = 'suppFile';
                }

                $objectTypes = $this->getAllObjectTypes();
                if (!array_key_exists($objectType, $objectTypes)) {
                    $result[] = ['plugins.importexport.common.export.error.unknownObjectType', $objectType];
                }
            } else {
                $result = false;
            }
        }

        if (is_array($result) && empty($result) && $objectType !== null && $journal !== null) {
            $objectTypes = $this->getAllObjectTypes();
            $exportSpec = [$objectTypes[$objectType] => $args];
            $request = Application::get()->getRequest();

            if ($command === 'export') {
                $result = $this->exportObjects($request, $exportSpec, $journal, $xmlFile);
            } else {
                $result = $this->registerObjects($request, $exportSpec, $journal);
                if ($result === true) {
                    echo __('plugins.importexport.common.register.success') . "\n";
                }
            }
        }

        if ($result !== true) {
            $this->_usage($scriptName, $result);
        }
    }

    /**
     * Manage the plugin.
     * @see ImportExportPlugin::manage()
     * @param string $verb
     * @param array $args
     * @param string|null &$message
     * @param array|null &$messageParams
     * @param Request|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        parent::manage($verb, $args, $message, $messageParams, $request);

        if ($verb !== 'settings') {
            throw new \UnexpectedValueException("Unknown management verb: {$verb}");
        }

        if (!$request instanceof PKPRequest) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        if (!$journal) {
            return false;
        }

        if ($this instanceof CrossRefExportPlugin) {
            import('lib.wizdam.classes.services.JournalOwnershipService');
            if (JournalOwnershipService::isOwnership($journal)) {
                $request->redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
                return true;
            }
        }

        $configurationErrors = [];
        $doiPrefix = null;
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        
        if (isset($pubIdPlugins['DOIPubIdPlugin'])) {
            $doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting($journal->getId(), 'doiPrefix');
        }
        
        if (empty($doiPrefix)) {
            $configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
        }

        $form = $this->_instantiateSettingsForm($journal);
        $formFields = $form->getFormFields();

        if (is_array($formFields)) {
            foreach ($formFields as $fieldName => $fieldType) {
                if ($form->isOptional($fieldName)) {
                    continue;
                }
                $setting = $this->getSetting($journal->getId(), $fieldName);
                if (empty($setting)) {
                    $configurationErrors[] = DOI_EXPORT_CONFIGERROR_SETTINGS;
                    break;
                }
            }
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('configurationErrors', $configurationErrors);

        if ($request->getUserVar('save')) {
            $form->readInputData();
            if ($form->validate()) {
                $form->execute();
                $request->redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
                return true; 
            } else {
                $this->setBreadcrumbs([], true);
                $form->display($request);
            }
        } else {
            $this->setBreadcrumbs([], true);
            $form->initData();
            $form->display($request);
        }

        return true;
    }

    //
    // Protected template methods
    //
    /**
     * Return the directory below the files dir where export files should be placed.
     * @return string
     */
    public function getPluginId(): string {
        throw new \BadMethodCallException('Must be implemented by subclass.');
    }

    /**
     * Return the class name of the plug-in's settings form.
     * @return string
     */
    public function getSettingsFormClassName(): string {
        throw new \BadMethodCallException('Must be implemented by subclass.');
    }

    /**
     * Return the object types supported by this plug-in.
     * @return array
     */
    public function getAllObjectTypes(): array {
        return [
            'issue'   => DOI_EXPORT_ISSUES,
            'article' => DOI_EXPORT_ARTICLES,
            'galley'  => DOI_EXPORT_GALLEYS
        ];
    }

    /**
     * Display a list of issues for export.
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

        $excludes = [];
        $allExcluded = true;
        while ($issue = $issueIterator->next()) {
            $excludes[$issue->getId()] = true;
            $errors = [];
            if ($this->canBeExported($issue, $errors)) {
                $excludes[$issue->getId()] = false;
                $allExcluded = false;
            }
        }
        unset($issueIterator);

        $issueIterator = $issueDao->getPublishedIssues($journal->getId(), Handler::getRangeInfo('issues'));
        $templateMgr->assign('issues', $issueIterator);
        $templateMgr->assign('allExcluded', $allExcluded);
        $templateMgr->assign('excludes', $excludes);
        $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
    }

    /**
     * Display a list of all yet unregistered objects.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displayAllUnregisteredObjects($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);
        AppLocale::requireComponents([LOCALE_COMPONENT_CORE_SUBMISSION]);

        $templateMgr->assign('issues', $this->_getUnregisteredIssues($journal));
        $templateMgr->assign('articles', $this->_getUnregisteredArticles($journal));
        $templateMgr->assign('galleys', $this->_getUnregisteredGalleys($journal));
        $templateMgr->display($this->getTemplatePath() . 'all.tpl');
    }

    /**
     * Display a list of supplementary files for export.
     * @see ImportExportPlugin::displaySuppFileList()
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displaySuppFileList($templateMgr, $journal): void {
        throw new \BadMethodCallException('Not implemented for this plug-in');
    }

    /**
     * Retrieve all published articles.
     * @see ImportExportPlugin::getAllPublishedArticles()
     * @param Journal $journal
     * @return array
     */
    public function getAllPublishedArticles($journal): array {
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleIterator = $publishedArticleDao->getPublishedArticlesByJournalId($journal->getId());

        $articles = [];
        while ($article = $articleIterator->next()) {
            $issue = $this->_getArticleIssue($article, $journal);
            if ($issue->getPublished()) {
                $articles[] = $article;
            }
        }
        unset($articleIterator);

        return $articles;
    }

    /**
     * Identify published article and issue of the given article file.
     * @see ImportExportPlugin::prepareArticleFileData()
     * @param ArticleFile $articleFile
     * @param Journal $journal
     * @return array|null
     */
    public function prepareArticleFileData($articleFile, $journal): ?array {
        $articleData = $this->_prepareArticleDataByArticleId((int) $articleFile->getArticleId(), $journal);
        if (!is_array($articleData)) {
            return null;
        }

        $cache = $this->getCache();
        $cache->add($articleFile, $articleData['article']);

        return $articleData;
    }

    /**
     * Export publishing objects.
     * @see ImportExportPlugin::exportObjects()
     * @param Request $request
     * @param array $exportSpec
     * @param Journal $journal
     * @param string|null $outputFile
     * @return bool|array
     */
    public function exportObjects($request, array $exportSpec, $journal, ?string $outputFile = null) {
        $errors = [];

        if (count($exportSpec) > 1) {
            $errors = $this->_checkForTar();
            if (is_array($errors)) {
                return $errors;
            }
        }

        $result = $this->_getExportPath();
        if (is_array($result)) {
            return $result;
        }
        $exportPath = $result;

        $exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
        if ($exportFiles === false) {
            return $errors;
        }

        // FIX (deposit manual): sebelumnya exportObjects() (tombol "Export XML")
        // TIDAK PERNAH menulis apapun ke DB -- setelah admin download XML lalu
        // upload sendiri ke portal Crossref, aplikasi ini tidak punya cara sama
        // sekali untuk tahu itu terjadi. Objek akan selamanya tampak "belum
        // diregister" di panel, terpisah total dari kondisi riil di Crossref.
        //
        // Sekarang, begitu file XML berhasil digenerate, objek yang diekspor
        // ditandai CROSSREF_STATUS_EXPORTED + timestamp -- MEMAKAI HELPER YANG
        // SAMA PERSIS dengan yang dipakai deposit otomatis (registerDoi()).
        // Ini membuat status "menunggu konfirmasi" langsung terlihat di panel
        // yang sama, TANPA admin perlu memilih status secara manual: polling
        // berkala (lihat CrossrefInfoSender/checkPendingDepositStatuses()) akan
        // mengecek histori riil DOI ini di Crossref API -- yang mencerminkan
        // deposit APAPUN sumbernya, baik lewat POST otomatis aplikasi ini
        // maupun upload manual di portal Crossref -- dan memperbarui status
        // (queued/in_process/completed/failed) secara otomatis sesuai kondisi
        // sebenarnya, persis seperti alur deposit otomatis.
        $this->_markObjectsAsExported($exportSpec, $journal, $errors);

        if (count($exportFiles) > 1 && !$this->_checkedForTar) {
            $errors = $this->_checkForTar();
            if (is_array($errors)) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return $errors;
            }
        }

        if (count($exportFiles) > 1) {
            $finalExportFileName = $exportPath . $this->getPluginId() . '-export.tar.gz';
            $finalExportFileType = DOI_EXPORT_FILE_TAR;
            $this->tarFiles($exportPath, $finalExportFileName, array_keys($exportFiles));
            $exportFiles[$finalExportFileName] = [];
        } else {
            $finalExportFileName = key($exportFiles);
            $finalExportFileType = DOI_EXPORT_FILE_XML;
        }

        if ($outputFile === null) {
            header('Content-Type: application/' . ($finalExportFileType === DOI_EXPORT_FILE_TAR ? 'x-gtar' : 'xml'));
            header('Cache-Control: private');
            header('Content-Disposition: attachment; filename="' . basename($finalExportFileName) . '"');
            readfile($finalExportFileName);
        } else {
            $outputFileExtension = ($finalExportFileType === DOI_EXPORT_FILE_TAR ? '.tar.gz' : '.xml');
            if (substr($outputFile, -strlen($outputFileExtension)) !== $outputFileExtension) {
                $outputFile .= $outputFileExtension;
            }
            $outputDir = dirname($outputFile);
            if (empty($outputDir)) {
                $outputDir = getcwd();
            }
            if (!is_writable($outputDir) || (file_exists($outputFile) && !is_writable($outputFile))) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return [['plugins.importexport.common.export.error.outputFileNotWritable', $outputFile]];
            }
            $fileManager = new FileManager();
            $fileManager->copyFile($finalExportFileName, $outputFile);
        }

        $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
        return true;
    }

    /**
     * Register publishing objects.
     * @see ImportExportPlugin::registerObjects()
     * @param Request $request
     * @param array $exportSpec
     * @param Journal $journal
     * @return bool|array
     */
    public function registerObjects($request, array $exportSpec, $journal) {
        set_time_limit(0);

        $result = $this->_getExportPath();
        if (is_array($result)) {
            return $result;
        }
        $exportPath = $result;

        $errors = [];
        $exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
        if ($exportFiles === false) {
            return $errors;
        }

        $arrayResult = [];
        $falseResult = false;

        foreach ($exportFiles as $exportFile => $objects) {
            // FIX: setiap registerDoi() melakukan HTTP POST blocking ke Crossref
            // (bisa lama untuk file besar) diikuti write DB per artikel. Untuk
            // batch dengan banyak file export, pastikan koneksi masih hidup
            // sebelum memulai setiap batch, bukan hanya sekali di awal task.
            DBConnection::ensureConnection();

            $result = $this->registerDoi($request, $journal, $objects, $exportFile);
            if ($result !== true) {
                if (is_array($result)) {
                    $arrayResult = array_merge($arrayResult, $result);
                } elseif ($result === false) {
                    $falseResult = true;
                }
            }
        }

        $this->cleanTmpfiles($exportPath, array_keys($exportFiles));

        if (!empty($arrayResult)) {
            return $arrayResult;
        }
        if ($falseResult) {
            return false;
        }

        return true;
    }

    /**
     * Return the target file name for the given export type and object id.
     * @see ImportExportPlugin::getTargetFileName()
     * @param string $exportPath
     * @param int $exportType
     * @param int|null $objectId
     * @return string
     */
    public function getTargetFileName(string $exportPath, int $exportType, ?int $objectId = null): string {
        $targetFileName = $exportPath . date('Ymd-Hi-') . $this->getObjectName($exportType);

        if ($objectId === null) {
            $targetFileName .= 's.xml';
        } else {
            $targetFileName .= '-' . $objectId . '.xml';
        }
        return $targetFileName;
    }

    /**
     * Return the name of the object for the given export type.
     * @see ImportExportPlugin::getObjectName()
     * @param int $exportType
     * @return string
     */
    public function getObjectName(int $exportType): string {
        $objectNames = [
            DOI_EXPORT_ISSUES   => 'issue',
            DOI_EXPORT_ARTICLES => 'article',
            DOI_EXPORT_GALLEYS  => 'galley',
        ];
        return $objectNames[$exportType] ?? 'unknown';
    }

    /**
     * The selected object can be exported if it has a DOI.
     * @see ImportExportPlugin::canBeExported()
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $foundObject
     * @param array $errors
     * @return bool
     */
    public function canBeExported($foundObject, &$errors): bool {
        if ($foundObject instanceof PublishedArticle && (int) $foundObject->getStatus() === STATUS_ARCHIVED) {
            return false;
        }
        return $foundObject->getPubId('doi') !== null;
    }

    /**
     * Generate the export data model.
     * @see ImportExportPlugin::generateExportFiles()
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param string $targetPath
     * @param Journal $journal
     * @param array $errors
     * @return array|bool
     */
    public function generateExportFiles($request, int $exportType, array $objects, string $targetPath, $journal, &$errors) {
        throw new \BadMethodCallException('Must be implemented by subclass.');
    }

    /**
     * Process the marking of the selected objects as registered.
     * @see ImportExportPlugin::processMarkRegistered()
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param Journal $journal
     */
    public function processMarkRegistered($request, int $exportType, array $objects, $journal): void {
        throw new \BadMethodCallException('Must be implemented by subclass.');
    }

    /**
     * Create a tar archive.
     * @see ImportExportPlugin::tarFiles()
     * @param string $targetPath
     * @param string $targetFile
     * @param array $sourceFiles
     */
    public function tarFiles(string $targetPath, string $targetFile, array $sourceFiles): void {
        if (!$this->_checkedForTar) {
            return;
        }

        $tarCommand = Config::getVar('cli', 'tar') . ' -czf ' . escapeshellarg($targetFile);
        $tarCommand .= ' -C ' . escapeshellarg($targetPath);
        $tarCommand .= ' --owner 0 --group 0 --';

        foreach ($sourceFiles as $sourceFile) {
            if (dirname($sourceFile) . '/' !== $targetPath) {
                continue;
            }
            $tarCommand .= ' ' . escapeshellarg(basename($sourceFile));
        }

        exec($tarCommand);
    }

    /**
     * Register the given DOI.
     * @see ImportExportPlugin::registerDoi()
     * @param Request $request
     * @param Journal $journal
     * @param array $objects
     * @param string $file
     * @return bool|array
     */
    public function registerDoi($request, $journal, array $objects, string $file) {
        throw new \BadMethodCallException('Not implemented for this plug-in');
    }

    /**
     * Check whether we are in test mode.
     * @see ImportExportPlugin::isTestMode()
     * @param Request $request
     * @return bool
     */
    public function isTestMode($request): bool {
        return $request->getUserVar('testMode') === '1';
    }

    /**
     * Mark the given object as registered.
     * @see ImportExportPlugin::markRegistered()
     * @param Request $request
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $testPrefix
     */
    public function markRegistered($request, $object, string $testPrefix = '10.1234'): void {
        $registeredDoi = $object->getPubId('doi');
        if (empty($registeredDoi)) {
            return;
        }
        if ($this->isTestMode($request)) {
            $registeredDoi = PKPString::regexp_replace('#^[^/]+/#', $testPrefix . '/', $registeredDoi);
        }
        $this->saveRegisteredDoi($object, $registeredDoi);
    }

    /**
     * Reset the given object.
     * @see ImportExportPlugin::resetRegistration()
     * @param int $objectType
     * @param int $objectId
     * @param Journal $journal
     * @return bool|array
     */
    public function resetRegistration(int $objectType, int $objectId, $journal) {
        $errors = [];
        $objects = $this->_getObjectsFromIds($objectType, $objectId, $journal->getId(), $errors);
        if ($objects === false || count($objects) !== 1) {
            return $errors;
        }

        $this->saveRegisteredDoi($objects[0], '');
        return true;
    }

    /**
     * Set the object's "registeredDoi" setting.
     * @see ImportExportPlugin::saveRegisteredDoi()
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $registeredDoi
     */
    public function saveRegisteredDoi($object, string $registeredDoi): void {
        $configurations = [
            'Issue'         => ['IssueDAO', 'updateIssue'],
            'Article'       => ['ArticleDAO', 'updateArticle'],
            'ArticleGalley' => ['ArticleGalleyDAO', 'updateGalley'],
            'SuppFile'      => ['SuppFileDAO', 'updateSuppFile']
        ];

        $configuration = null;
        foreach ($configurations as $objectType => $config) {
            if ($object instanceof $objectType) {
                $configuration = $config;
                break;
            }
        }

        if ($configuration === null) {
            throw new \UnexpectedValueException('Unsupported object type for DOI registration: ' . get_class($object));
        }

        [$daoName, $daoMethod] = $configuration;
        $this->registerDaoHook($daoName);
        
        /** @var DAO $dao */
        $dao = DAORegistry::getDAO($daoName);
        $object->setData($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, $registeredDoi);
        $dao->$daoMethod($object);
    }

    /**
     * Register a hook for the given DAO
     * @see DAO::getAdditionalFieldNames()
     * @param string $daoName
     */
    public function registerDaoHook(string $daoName): void {
        HookRegistry::register(strtolower_codesafe($daoName) . '::getAdditionalFieldNames', [$this, 'getAdditionalFieldNames']);
    }

    /**
     * Add the additional field name
     * @see DAO::getAdditionalFieldNames()
     * @param string $hookName
     * @param array $args
     */
    public function getAdditionalFieldNames($hookName, $args): void {
        if (count($args) >= 2 && is_array($args[1])) {
            $args[1][] = $this->getPluginId() . '::' . DOI_EXPORT_REGDOI;

            // FIX (issue-level status tracking): field-field status deposit
            // granular (submitted/queued/in_process/completed/failed + URL +
            // timestamp) sebelumnya hanya pernah ditulis lewat ArticleDAO::
            // updateSetting(), yang TIDAK memerlukan pre-registrasi field apapun.
            // Sekarang field yang sama juga ditulis untuk Issue lewat
            // IssueDAO::updateIssue() -> updateDataObjectSettings(), yang HANYA
            // menyimpan field yang terdaftar di sini. Tanpa baris ini, penulisan
            // status deposit issue akan diam-diam diabaikan (tidak error, tapi
            // tidak pernah benar-benar tersimpan).
            if (method_exists($this, 'getDepositStatusSettingName')) {
                $args[1][] = $this->getDepositStatusSettingName();
            }
            if (method_exists($this, 'getDepositStatusUrlSettingName')) {
                $args[1][] = $this->getDepositStatusUrlSettingName();
            }
            if (method_exists($this, 'getDepositSubmittedAtSettingName')) {
                $args[1][] = $this->getDepositSubmittedAtSettingName();
            }
        }
    }

    /**
     * Remove the given temporary files.
     * @see ImportExportPlugin::cleanTmpfiles()
     * @param string $tempdir
     * @param array $tempfiles
     */
    public function cleanTmpfiles(string $tempdir, array $tempfiles): void {
        foreach ($tempfiles as $tempfile) {
            $tempfilePath = dirname($tempfile) . '/';
            if ($tempdir !== $tempfilePath) {
                continue;
            }
            unlink($tempfile);
        }
    }

    /**
     * Get the DAO name and method name for the given export type.
     * @see ImportExportPlugin::getDaoName()
     * @param int $exportType
     * @return array
     */
    public function getDaoName(int $exportType): array {
        $daoNames = [
            DOI_EXPORT_ISSUES   => ['IssueDAO', 'getIssueById'],
            DOI_EXPORT_ARTICLES => ['PublishedArticleDAO', 'getPublishedArticleByArticleId'],
            DOI_EXPORT_GALLEYS  => ['ArticleGalleyDAO', 'getGalley'],
        ];
        return $daoNames[$exportType] ?? [];
    }

    /**
     * Get the translation key for "object not found" errors.
     * @see ImportExportPlugin::getObjectNotFoundErrorKey()
     * @param int $exportType
     * @return string
     */
    public function getObjectNotFoundErrorKey(int $exportType): string {
        $errorKeys = [
            DOI_EXPORT_ISSUES   => 'plugins.importexport.common.export.error.issueNotFound',
            DOI_EXPORT_ARTICLES => 'plugins.importexport.common.export.error.articleNotFound',
            DOI_EXPORT_GALLEYS  => 'plugins.importexport.common.export.error.galleyNotFound'
        ];
        return $errorKeys[$exportType] ?? 'plugins.importexport.common.export.error.objectNotFound';
    }

    //
    // Private helper methods
    //
    /**
     * Display the plug-in home page.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    private function _displayPluginHomePage($templateMgr, $journal): void {
        $this->setBreadcrumbs();

        $configurationErrors = [];
        $doiPrefix = null;
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        
        if (isset($pubIdPlugins['DOIPubIdPlugin'])) {
            $doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting($journal->getId(), 'doiPrefix');
        }
        if (empty($doiPrefix)) {
            $configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
        }

        $form = $this->_instantiateSettingsForm($journal);
        foreach ($form->getFormFields() as $fieldName => $fieldType) {
            if ($form->isOptional($fieldName)) {
                continue;
            }
            $setting = $this->getSetting($journal->getId(), $fieldName);
            if (empty($setting)) {
                $configurationErrors[] = DOI_EXPORT_CONFIGERROR_SETTINGS;
                break;
            }
        }

        $templateMgr->assign('configurationErrors', $configurationErrors);
        $templateMgr->assign('journal', $journal);

        if ($this instanceof CrossRefExportPlugin) {
            import('lib.wizdam.classes.services.JournalOwnershipService');
            $templateMgr->assign('isPartnershipJournal', JournalOwnershipService::isPartnership($journal));
        }

        $templateMgr->display($this->getTemplatePath() . 'index.tpl');
    }

    /**
     * Display a list of articles for export.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displayArticleList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);
        $this->registerDaoHook('PublishedArticleDAO');
        $allArticles = $this->getAllPublishedArticles($journal);

        $articles = [];
        foreach ($allArticles as $article) {
            $errors = [];
            if ($this->canBeExported($article, $errors)) {
                $articles[] = $article;
            }
        }
        unset($allArticles);

        $totalArticles = count($articles);
        $rangeInfo = Handler::getRangeInfo('articles');
        if ($rangeInfo->isValid()) {
            $articles = array_slice($articles, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
        }

        $articleData = [];
        foreach ($articles as $article) {
            $preparedArticle = $this->_prepareArticleData($article, $journal);
            if (is_array($preparedArticle)) {
                $articleData[] = $preparedArticle;
            }
        }
        unset($articles);

        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($articleData, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr->assign('articles', $iterator);
        $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
    }

    /**
     * Display a list of galleys for export.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    private function _displayGalleyList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);
        $allArticles = $this->getAllPublishedArticles($journal);

        $this->registerDaoHook('ArticleGalleyDAO');
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = [];
        
        foreach ($allArticles as $article) {
            $articleGalleys = $galleyDao->getGalleysByArticle($article->getId());
            foreach ($articleGalleys as $galley) {
                $errors = [];
                if ($this->canBeExported($galley, $errors)) {
                    $galleys[] = $galley;
                }
            }
        }
        unset($allArticles);

        $totalGalleys = count($galleys);
        $rangeInfo = Handler::getRangeInfo('galleys');
        if ($rangeInfo->isValid()) {
            $galleys = array_slice($galleys, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
        }

        $galleyData = [];
        foreach ($galleys as $galley) {
            $preparedGalley = $this->_prepareGalleyData($galley, $journal);
            if (is_array($preparedGalley)) {
                $galleyData[] = $preparedGalley;
            }
        }
        unset($galleys);

        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($galleyData, $totalGalleys, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr->assign('galleys', $iterator);
        $templateMgr->display($this->getTemplatePath() . 'galleys.tpl');
    }

    /**
     * Retrieve all unregistered issues.
     *
     * FIX: parameter $bypassCooldown ditambahkan agar fungsi ini bisa dipakai
     * untuk DUA kebutuhan berbeda yang sebelumnya tercampur:
     * 1. Daftar kandidat untuk export/register (UI admin, dan basis kandidat
     *    resubmit otomatis) -- HARUS menghormati cooldown (default, $bypassCooldown=false),
     *    supaya objek yang baru saja diekspor/disubmit tidak langsung
     *    ditawarkan lagi untuk dikirim ulang.
     * 2. Daftar kandidat untuk REFRESH STATUS SAJA (polling read-only ke
     *    Crossref, lihat CrossrefInfoSender::_pollIssueDepositStatuses()) --
     *    TIDAK BOLEH terpengaruh cooldown, karena mengecek status tidak
     *    berisiko double-submission, dan JUSTRU paling dibutuhkan selama
     *    objek masih dalam masa tunggu (supaya begitu Crossref selesai
     *    memproses, statusnya cepat terdeteksi, bukan menunggu cooldown habis).
     *
     * @param Journal $journal
     * @param bool $bypassCooldown
     * @return array
     */
    public function _getUnregisteredIssues($journal, bool $bypassCooldown = false): array {
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issues = $issueDao->getIssuesBySetting($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, $journal->getId());

        $cache = $this->getCache();
        $issueData = [];
        foreach ($issues as $issue) {
            // FIX (anti double-deposit, issue-level): sama seperti artikel --
            // "registeredDoi" hanya di-set saat status Crossref sudah COMPLETED.
            // Sebelum patch ini, deposit issue bahkan TIDAK PERNAH tercatat sama
            // sekali (lihat catatan di registerDoi()/updateDepositStatus()),
            // sehingga setiap klik "Register" akan mengirim ulang issue yang
            // sama tanpa batas. Sekarang setelah status granular ikut tercatat,
            // terapkan cooldown yang sama dengan artikel di sini -- KECUALI
            // pemanggil secara eksplisit meminta bypass (polling status).
            if (!$bypassCooldown && $this->_isWithinCrossrefResubmitCooldown($issue)) {
                continue;
            }
            $cache->add($issue, null);
            if ($issue->getPublished()) {
                $issueData[] = $issue;
            }
        }
        return $issueData;
    }

    /**
     * Retrieve all unregistered articles and their corresponding issues.
     *
     * FIX: lihat catatan lengkap di _getUnregisteredIssues() -- parameter
     * $bypassCooldown yang sama diterapkan di sini, dengan alasan yang identik.
     *
     * @param Journal $journal
     * @param bool $bypassCooldown
     * @return array
     */
    public function _getUnregisteredArticles($journal, bool $bypassCooldown = false): array {
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articles = $publishedArticleDao->getBySetting($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, $journal->getId());

        $articleData = [];
        foreach ($articles as $article) {
            // FIX (anti double-deposit): field "registeredDoi" yang dipakai
            // getBySetting() di atas HANYA di-set saat status Crossref benar-benar
            // COMPLETED. Karena Crossref memproses deposit secara asinkron
            // (queued -> in_process -> completed) dan pengecekan status dilakukan
            // segera setelah submit (lihat registerDoi()), artikel yang BARU SAJA
            // disubmit dan masih diproses akan selalu lolos filter ini dan
            // dianggap "belum diregister" -> disubmit ulang di run cron
            // berikutnya, padahal submission sebelumnya mungkin masih berjalan.
            //
            // Untuk mencegah ini, artikel yang statusnya masih "in-flight"
            // (submitted/queued/in_process) DAN belum melewati masa tunggu
            // (cooldown) sejak submission terakhir, dikecualikan dari kandidat
            // resubmit di sini -- KECUALI pemanggil eksplisit meminta bypass
            // (polling status, lihat _getUnregisteredIssues()).
            if (!$bypassCooldown && $this->_isWithinCrossrefResubmitCooldown($article)) {
                continue;
            }

            $preparedArticle = $this->_prepareArticleData($article, $journal);
            if (is_array($preparedArticle)) {
                $articleData[] = $preparedArticle;
            }
        }
        return $articleData;
    }

    /**
     * FIX: Cek apakah artikel masih dalam masa tunggu (cooldown) sejak submission
     * terakhir ke Crossref, sehingga belum layak disubmit ulang.
     *
     * - Jika status BUKAN salah satu status "in-flight" (submitted/queued/
     *   in_process) -> boleh diproses (bukan bagian dari masalah ini; misalnya
     *   belum pernah disubmit sama sekali, atau sudah completed/failed).
     * - Jika status in-flight TAPI tidak ada timestamp submission tercatat ->
     *   boleh diproses (fail-safe: lebih baik memberi kesempatan submit
     *   daripada mengunci artikel selamanya karena data lama/tidak lengkap).
     * - Jika status in-flight DAN masih dalam window cooldown -> DIKECUALIKAN
     *   dari resubmit.
     * - Jika status in-flight DAN cooldown SUDAH lewat (Crossref belum juga
     *   melaporkan completed/failed setelah waktu yang wajar) -> boleh
     *   diproses lagi, TAPI dicatat sebagai warning eksplisit, karena ini bisa
     *   menandakan submission sebelumnya gagal diproses Crossref tanpa
     *   pernah terlihat di aplikasi (lihat investigasi "Duplicate key
     *   exception" pada submission log Crossref yang tidak pernah dibaca
     *   aplikasi ini).
     *
     * @param PublishedArticle $article
     * @return bool
     */
    private function _isWithinCrossrefResubmitCooldown($article): bool {
        if (!method_exists($this, 'getDepositStatusSettingName') || !method_exists($this, 'getDepositSubmittedAtSettingName')) {
            // Plugin konkret belum mengimplementasikan setting name (lihat
            // CrossRefExportPlugin) -- tidak ada dasar untuk cooldown, jangan blokir.
            return false;
        }

        $status = $article->getData($this->getDepositStatusSettingName());
        $inFlightStatuses = defined('CROSSREF_IN_FLIGHT_STATUSES') ? CROSSREF_IN_FLIGHT_STATUSES : [];
        if (!in_array($status, $inFlightStatuses, true)) {
            return false;
        }

        $submittedAt = (int) $article->getData($this->getDepositSubmittedAtSettingName());
        if ($submittedAt <= 0) {
            return false;
        }

        $cooldownSeconds = defined('CROSSREF_RESUBMIT_COOLDOWN_SECONDS') ? CROSSREF_RESUBMIT_COOLDOWN_SECONDS : (6 * 3600);
        $elapsed = time() - $submittedAt;

        if ($elapsed < $cooldownSeconds) {
            return true;
        }

        // FIX: cooldown sudah lewat tapi status masih "in-flight" -- ini
        // anomali yang layak diketahui admin (kemungkinan submission
        // sebelumnya gagal diproses Crossref secara diam-diam, atau API status
        // Crossref sedang bermasalah). Dicatat, lalu artikel diizinkan masuk
        // kandidat resubmit lagi.
        error_log(sprintf(
            'CrossrefDoiExportPlugin: artikel #%d masih berstatus "%s" setelah %d detik sejak submit (cooldown %d detik terlampaui). Akan dicoba submit ulang; periksa submission log Crossref untuk kemungkinan kegagalan yang tidak tercatat di aplikasi.',
            (int) $article->getId(),
            (string) $status,
            $elapsed,
            $cooldownSeconds
        ));

        return false;
    }

    /**
     * FIX: Wrapper publik yang menggabungkan DUA syarat kelayakan submit ulang
     * OTOMATIS (dipakai CrossrefInfoSender setelah memanggil
     * _getUnregisteredArticles()/_getUnregisteredIssues() dengan
     * $bypassCooldown=true untuk keperluan polling status):
     * 1. Statusnya termasuk CROSSREF_AUTO_RESUBMIT_STATUSES (submitted/queued/
     *    in_process -- TIDAK termasuk status "exported" dari deposit manual,
     *    lihat definisi konstanta ini di CrossRefExportPlugin.inc.php).
     * 2. TIDAK sedang dalam window cooldown (_isWithinCrossrefResubmitCooldown()).
     *
     * Dipusatkan di sini (bukan diduplikasi logikanya di CrossrefInfoSender)
     * supaya definisi "layak disubmit ulang otomatis" konsisten di satu tempat.
     *
     * @param mixed $object Article atau Issue
     * @param string $currentStatus Status yang sedang dicek (biasanya hasil terbaru dari updateDepositStatus())
     * @return bool
     */
    public function isEligibleForAutoResubmit($object, string $currentStatus): bool {
        if (!defined('CROSSREF_AUTO_RESUBMIT_STATUSES') || !in_array($currentStatus, CROSSREF_AUTO_RESUBMIT_STATUSES, true)) {
            return false;
        }
        return !$this->_isWithinCrossrefResubmitCooldown($object);
    }

    /**
     * Retrieve all unregistered galleys and their corresponding issues and articles.
     * @param Journal $journal
     * @return array
     */
    public function _getUnregisteredGalleys($journal): array {
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = $galleyDao->getGalleysBySetting($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, null, $journal->getId());

        $galleyData = [];
        foreach ($galleys as $galley) {
            $preparedGalley = $this->_prepareGalleyData($galley, $journal);
            if (is_array($preparedGalley)) {
                $galleyData[] = $preparedGalley;
            }
        }
        return $galleyData;
    }

    /**
     * Identify published article, issue and language of the given galley.
     * @param ArticleGalley $galley
     * @param Journal $journal
     * @return array|null
     */
    private function _prepareGalleyData($galley, $journal): ?array {
        $galleyData = $this->prepareArticleFileData($galley, $journal);
        if (!is_array($galleyData)) {
            return null;
        }

        /** @var LanguageDAO $languageDao */
        $languageDao = DAORegistry::getDAO('LanguageDAO');
        $galleyData['language'] = $languageDao->getLanguageByCode(AppLocale::getIso1FromLocale($galley->getLocale()));
        $galleyData['galley'] = $galley;

        return $galleyData;
    }

    /**
     * Identify published article and issue for the given article id.
     * @param int $articleId
     * @param Journal $journal
     * @return array|null
     */
    protected function _prepareArticleDataByArticleId(int $articleId, $journal): ?array {
        $cache = $this->getCache();

        if (!$cache->isCached('articles', $articleId)) {
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId, $journal->getId(), true);
            
            if (!$article instanceof PublishedArticle) {
                return null;
            }
            $cache->add($article, null);
        }

        $article = $cache->get('articles', $articleId);
        return $this->_prepareArticleData($article, $journal);
    }

    /**
     * Identify the issue of the given article.
     * @param PublishedArticle $article
     * @param Journal $journal
     * @return Issue|null
     */
    protected function _prepareArticleData($article, $journal): ?array {
        $cache = $this->getCache();
        $cache->add($article, null);

        $issue = $this->_getArticleIssue($article, $journal);
        if ($issue && $issue->getPublished()) {
            return [
                'article' => $article,
                'issue'   => $issue
            ];
        }
        return null;
    }

    /**
     * Retrieve the issue for the given article.
     * @param PublishedArticle $article
     * @param Journal $journal
     * @return Issue|null
     */
    private function _getArticleIssue($article, $journal) {
        $issueId = (int) $article->getIssueId();
        $cache = $this->getCache();
        
        if (!$cache->isCached('issues', $issueId)) {
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($issueId, $journal->getId(), true);

            if ($issue instanceof Issue) {
                $cache->add($issue, null);
            }
        }

        return $cache->get('issues', $issueId);
    }

    /**
     * Generate export files for the given export spec.
     * @param Request $request
     * @param Journal $journal
     * @param array $exportSpec
     * @param string $exportPath
     * @param array $errors
     * @return array|bool
     */
    private function _generateExportFilesForObjects($request, $journal, array $exportSpec, string $exportPath, &$errors) {
        $exportFiles = [];
        foreach ($exportSpec as $exportType => $objectIds) {
            if (is_scalar($objectIds)) {
                $objectIds = [$objectIds];
            }

            $objects = $this->_getObjectsFromIds($exportType, $objectIds, (int) $journal->getId(), $errors);
            if (empty($objects)) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return false;
            }

            $newFiles = $this->generateExportFiles($request, $exportType, $objects, $exportPath, $journal, $errors);
            if ($newFiles === false) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return false;
            }

            $exportFiles = array_merge($exportFiles, $newFiles);
        }
        return $exportFiles;
    }

    /**
     * FIX (deposit manual, lihat exportObjects()): tandai objek yang baru saja
     * berhasil diekspor sebagai XML (status CROSSREF_STATUS_EXPORTED), supaya
     * statusnya terlihat di panel dan ikut dipantau oleh polling status
     * berkala -- persis seperti objek yang disubmit lewat deposit otomatis.
     *
     * Objek yang statusnya SUDAH lebih maju (mis. sudah completed/registered)
     * TIDAK ditimpa mundur jadi "exported" -- export ulang XML untuk arsip
     * tidak boleh membuat status yang sudah final terlihat mundur ke belum
     * pasti.
     *
     * @param array $exportSpec Peta [exportType => objectIds] seperti pada exportObjects()
     * @param Journal $journal
     * @param array $errors Dilewatkan by-reference ke _getObjectsFromIds(), diabaikan di sini
     *                      (kegagalan generate file sudah ditangani sebelum method ini dipanggil).
     */
    private function _markObjectsAsExported(array $exportSpec, $journal, array &$errors): void {
        if (!method_exists($this, '_supportsDepositStatusTracking') || !method_exists($this, '_persistDepositStatusSettings')) {
            // Plugin konkret belum mengimplementasikan pelacakan status granular
            // (lihat CrossRefExportPlugin) -- tidak ada dasar untuk menulis status.
            return;
        }
        if (!method_exists($this, 'getDepositStatusSettingName') || !method_exists($this, 'getDepositSubmittedAtSettingName')) {
            return;
        }

        $depositStatusSettingName = $this->getDepositStatusSettingName();
        $depositSubmittedAtSettingName = $this->getDepositSubmittedAtSettingName();
        $exportedAt = time();

        // Status yang dianggap "sudah lebih maju dari sekadar diekspor" --
        // jangan ditimpa mundur oleh export ulang (mis. admin export ulang XML
        // untuk arsip padahal objeknya sudah completed/registered sebelumnya).
        $moreAdvancedStatuses = [
            CROSSREF_STATUS_COMPLETED,
            CROSSREF_STATUS_REGISTERED,
            CROSSREF_STATUS_MARKEDREGISTERED,
        ];

        foreach ($exportSpec as $exportType => $objectIds) {
            if (is_scalar($objectIds)) {
                $objectIds = [$objectIds];
            }
            $localErrors = [];
            $objects = $this->_getObjectsFromIds($exportType, $objectIds, (int) $journal->getId(), $localErrors);
            if (empty($objects)) {
                continue;
            }

            foreach ($objects as $object) {
                if (!$this->_supportsDepositStatusTracking($object)) {
                    continue;
                }

                $currentStatus = $object->getData($depositStatusSettingName);
                if (in_array($currentStatus, $moreAdvancedStatuses, true)) {
                    continue;
                }

                $object->setData($depositStatusSettingName, CROSSREF_STATUS_EXPORTED);
                $object->setData($depositSubmittedAtSettingName, $exportedAt);
                $this->_persistDepositStatusSettings($object, [
                    $depositStatusSettingName      => ['value' => CROSSREF_STATUS_EXPORTED, 'type' => 'string'],
                    $depositSubmittedAtSettingName => ['value' => $exportedAt, 'type' => 'int'],
                ]);
            }
        }
    }

    /**
     * Test whether the tar binary is available.
     * @return bool|array
     */
    private function _checkForTar() {
        $tarBinary = Config::getVar('cli', 'tar');
        if (empty($tarBinary) || !is_executable($tarBinary)) {
            $result = [['manager.plugins.tarCommandNotFound']];
        } else {
            $result = true;
        }
        $this->_checkedForTar = true;
        return $result;
    }

    /**
     * Return the plug-ins export directory.
     * @return string|array
     */
    private function _getExportPath() {
        $exportPath = Config::getVar('files', 'files_dir') . '/' . $this->getPluginId();
        if (!file_exists($exportPath)) {
            $fileManager = new FileManager();
            $fileManager->mkdir($exportPath);
        }
        if (!is_writable($exportPath)) {
            return [['plugins.importexport.common.export.error.outputFileNotWritable', $exportPath]];
        }
        return realpath($exportPath) . '/';
    }

    /**
     * Retrieve the objects corresponding to the given ids.
     * @param int $exportType
     * @param int|array $objectIds
     * @param int $journalId
     * @param array $errors
     * @return array|bool
     */
    public function _getObjectsFromIds(int $exportType, $objectIds, int $journalId, &$errors) {
        if (empty($objectIds)) {
            return false;
        }
        if (!is_array($objectIds)) {
            $objectIds = [$objectIds];
        }

        [$daoName, $daoMethod] = $this->getDaoName($exportType);
        $dao = DAORegistry::getDAO($daoName);
        $daoMethodCallable = [$dao, $daoMethod];

        $objects = [];
        foreach ($objectIds as $objectId) {
            $daoMethodArgs = [$objectId];
            if ($exportType !== DOI_EXPORT_GALLEYS && $exportType !== DOI_EXPORT_SUPPFILES) {
                $daoMethodArgs[] = $journalId;
            }
            
            $foundObjects = call_user_func_array($daoMethodCallable, $daoMethodArgs);
            if (!$foundObjects || empty($foundObjects)) {
                $errors[] = [$this->getObjectNotFoundErrorKey($exportType), $objectId];
                return false;
            }

            if (!is_array($foundObjects)) {
                $foundObjects = [$foundObjects];
            }
            
            foreach ($foundObjects as $foundObject) {
                if ($this->canBeExported($foundObject, $errors)) {
                    $objects[] = $foundObject;
                } else {
                    return false;
                }
            }
        }
        return $objects;
    }

    /**
     * Display execution errors (if any) and command-line usage information.
     * @param string $scriptName
     * @param array|null $errors
     */
    private function _usage(string $scriptName, $errors = null): void {
        if (is_array($errors) && !empty($errors)) {
            echo __('plugins.importexport.common.cliError') . "\n";
            foreach ($errors as $error) {
                if (is_array($error) && count($error) >= 1) {
                    $errorMessage = isset($error[1]) ? __($error[0], ['param' => $error[1]]) : __($error[0]);
                    echo "*** $errorMessage\n";
                }
            }
            echo "\n\n";
        }
        echo __(
            'plugins.importexport.' . $this->getPluginId() . '.cliUsage',
            [
                'scriptName' => $scriptName,
                'pluginName' => $this->getName()
            ]
        ) . "\n";
    }

    /**
     * Instantiate the settings form.
     * @param Journal $journal
     * @return DOIExportSettingsForm
     */
    private function _instantiateSettingsForm($journal) {
        $settingsFormClassName = $this->getSettingsFormClassName();
        $this->import('classes.form.' . $settingsFormClassName);
        
        $settingsForm = new $settingsFormClassName($this, $journal->getId());
        return $settingsForm;
    }

    /**
     * Add a notification.
     * @param Request $request
     * @param string $message
     * @param int $notificationType
     * @param string|null $param
     */
    private function _sendNotification($request, string $message, int $notificationType, ?string $param = null): void {
        static $notificationManager = null;

        if ($notificationManager === null) {
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
        }

        $params = $param !== null ? ['param' => $param] : null;
        $user = $request->getUser();
        
        $notificationManager->createTrivialNotification(
            $user->getId(),
            $notificationType,
            ['contents' => __($message, $params)]
        );
    }

    /**
     * Hook callback to parse the cron tab.
     * @see AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args): bool {
        return false;
    }

}
?>