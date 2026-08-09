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

// [WIZDAM BUGFIX] Nama konstanta ini SAMA PERSIS dipakai plugin mEDRA &
// DataCite juga -- karena PluginRegistry::loadCategory('importexport')
// memuat SEMUA plugin dalam satu request, define() tanpa guard akan
// memicu PHP Warning "Constant already defined" berulang kali. Dibungkus
// if (!defined(...)) supaya cuma yang PERTAMA kali berhasil, sisanya
// dilewati dengan aman (nilainya identik di ketiga plugin, jadi tidak
// mengubah perilaku apapun -- cuma menghilangkan warning-nya).
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
                // [WIZDAM BUGFIX] Sebelumnya throw \UnexpectedValueException
                // yang TIDAK DITANGKAP di manapun -- menyebabkan fatal error
                // yang menghentikan seluruh halaman untuk $op apapun yang
                // tidak dikenali switch ini (termasuk kemungkinan verb yang
                // seharusnya lewat manage(), bukan display()). Redirect ke
                // halaman utama plugin jauh lebih aman untuk pengguna
                // daripada crash total -- request yang salah arah cukup
                // dikembalikan, bukan menghentikan aplikasi.
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
     * @param Journal $journal
     * @return array
     */
    protected function _getUnregisteredIssues($journal): array {
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issues = $issueDao->getIssuesBySetting($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, $journal->getId());

        $cache = $this->getCache();
        $issueData = [];
        foreach ($issues as $issue) {
            $cache->add($issue, null);
            if ($issue->getPublished()) {
                $issueData[] = $issue;
            }
        }
        return $issueData;
    }

    /**
     * Retrieve all unregistered articles and their corresponding issues.
     * @param Journal $journal
     * @return array
     */
    protected function _getUnregisteredArticles($journal): array {
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articles = $publishedArticleDao->getBySetting($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, null, $journal->getId());

        $articleData = [];
        foreach ($articles as $article) {
            $preparedArticle = $this->_prepareArticleData($article, $journal);
            if (is_array($preparedArticle)) {
                $articleData[] = $preparedArticle;
            }
        }
        return $articleData;
    }

    /**
     * Retrieve all unregistered galleys and their corresponding issues and articles.
     * @param Journal $journal
     * @return array
     */
    protected function _getUnregisteredGalleys($journal): array {
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
    // [WIZDAM BUGFIX] Sebelumnya "private" -- CrossRefExportPlugin (subclass)
    // memanggil method ini LANGSUNG via $this->..., yang TIDAK VALID untuk
    // method private (cuma bisa diakses dari DALAM class yang sama, BUKAN
    // dari subclass). Harus protected supaya bisa diwarisi & dipanggil.
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
    // [WIZDAM BUGFIX] Sebelumnya "private" -- dipanggil langsung dari
    // CrossRefExportPlugin (subclass), harus protected.
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

            $objects = $this->_getObjectsFromIds($exportType, $objectIds, $journal->getId(), $errors);
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
    // [WIZDAM BUGFIX] Sebelumnya "private" -- dipanggil langsung dari
    // CrossRefExportPlugin (subclass), harus protected.
    protected function _getObjectsFromIds(int $exportType, $objectIds, int $journalId, &$errors) {
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