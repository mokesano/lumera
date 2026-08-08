<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/medra/classes/MedraDoiExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MedraDoiExportPlugin
 * @ingroup plugins_importexport_classes
 *
 * @brief Base class for the mEDRA DOI export/registration plugin.
 */

import('classes.plugins.ImportExportPlugin');

// [WIZDAM BUGFIX] Guard defined() -- lihat penjelasan lengkap di
// CrossrefDoiExportPlugin.inc.php (konstanta sama dipakai 3 plugin).
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

class MedraDoiExportPlugin extends ImportExportPlugin {

    //
    // Protected Properties
    //
    /** @var PubObjectCache|null */
    protected ?PubObjectCache $_cache = null;

    //
    // Private Properties
    //
    /** @var bool */
    private bool $_checkedForTar = false;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility for legacy calls
     */
    public function MedraDoiExportPlugin() {
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
     * Get the publication object cache.
     * @return PubObjectCache
     */
    public function getCache(): PubObjectCache {
        if (!($this->_cache instanceof PubObjectCache)) {
            if (!class_exists('PubObjectCache')) { 
                $this->import('classes.PubObjectCache');
            }
            $this->_cache = new PubObjectCache();
        }
        return $this->_cache;
    }

    //
    // Implement template methods from PKPPlugin
    //

    /**
     * Register the plugin.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path);
        if ($success) {
            $this->addLocaleData();
            HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);
        }
        return $success;
    }

    /**
     * Get the path to the templates.
     * @see PKPPlugin::getTemplatePath()
     * @param bool $inCore
     * @return string
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Get the context-specific plugin settings file.
     * @see PKPPlugin::getInstallSitePluginSettingsFile()
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the locale filename for the plugin.
     * @see PKPPlugin::getLocaleFilename($locale)
     * @param string $locale
     * @return array
     */
    public function getLocaleFilename($locale) {
        $localeFilenames = parent::getLocaleFilename($locale);
        $localeFilenames[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'common.xml';
        return $localeFilenames;
    }

    //
    // Implement template methods from ImportExportPlugin
    //

    /**
     * Get the management verbs.
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
     * Display the plugin interface.
     * @see ImportExportPlugin::display()
     * @param array $args
     * @param Request $request
     * @return void
     */
    public function display($args, $request): void {
        parent::display($args, $request);
        $templateMgr = TemplateManager::getManager($request);

        $journal = $request->getJournal();
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        $op = array_shift($args);

        switch($op) {
            case '':
            case 'index':
                $this->_displayPluginHomePage($templateMgr, $journal);
                return;

            case 'all':
            case 'issues':
            case 'articles':
            case 'galleys':
            case 'suppFiles':
                $templateMgr->assign('testMode', $this->isTestMode($request) ? ['testMode' => 1] : []);
                $templateMgr->assign('filter', $request->getUserVar('filter'));

                $username = $this->getSetting((int) $journal->getId(), 'username');
                $templateMgr->assign('hasCredentials', !empty($username));

                switch ($op) {
                    case 'issues':
                        $this->displayIssueList($templateMgr, $journal);
                        return;
                    case 'articles':
                        $this->displayArticleList($templateMgr, $journal);
                        return;
                    case 'galleys':
                        $this->_displayGalleyList($templateMgr, $journal);
                        return;
                    case 'suppFiles':
                        $this->displaySuppFileList($templateMgr, $journal);
                        return;
                    case 'all':
                        $this->displayAllUnregisteredObjects($templateMgr, $journal);
                        return;
                }
                break;

            case 'process':
                $this->process($request, $journal);
                break;

            case 'settings':
            default:
                $request->redirect(null, 'manager', 'importexport', array('plugin', $this->getName()));
                return;
        }
    }

    /**
     * Process a DOI activity request.
     * @param Request $request
     * @param Journal $journal
     * @return void
     */
    public function process($request, $journal): void {
        $objectTypes = $this->getAllObjectTypes();
        $target = (string) $request->getUserVar('target');
        $result = false;
        $errors = [];

        if ($request->getUserVar('export') || $request->getUserVar('register') || $request->getUserVar('markRegistered')) {
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
                    throw new \RuntimeException('Invalid target object type.');
                }
                $exportSpec = [$objectTypes[$target] => (array) $request->getUserVar($target . 'Id')];
            }

            if ($request->getUserVar('export')) {
                $result = $this->exportObjects($request, $exportSpec, $journal);
            } elseif ($request->getUserVar('markRegistered')) {
                foreach($exportSpec as $exportType => $objectIds) {
                    if (is_scalar($objectIds)) $objectIds = [$objectIds];
                    $objects = $this->_getObjectsFromIds($exportType, $objectIds, (int) $journal->getId(), $errors);
                    if ($objects !== false) {
                        $this->processMarkRegistered($request, $exportType, $objects, $journal);
                    }
                }
                $listAction = $target . ($target === 'all' ? '' : 's');
                $request->redirect(
                    null, null, null,
                    ['plugin', $this->getName(), $listAction],
                    ($this->isTestMode($request) ? ['testMode' => 1] : null)
                );
                return;
            } else { 
                $result = $this->registerObjects($request, $exportSpec, $journal);

                if ($result === true) {
                    $this->_sendNotification(
                        $request,
                        'plugins.importexport.'.$this->getPluginId() .'.register.success',
                        NOTIFICATION_TYPE_SUCCESS
                    );

                    $listAction = $target . ($target === 'all' ? '' : 's');
                    $request->redirect(
                        null, null, null,
                        ['plugin', $this->getName(), $listAction],
                        ($this->isTestMode($request) ? ['testMode' => 1] : null)
                    );
                    return;
                }
            }
        } elseif ($request->getUserVar('reset')) {
            $ids = (array) $request->getUserVar($target . 'Id');
            if (isset($objectTypes[$target])) {
                $result = $this->resetRegistration($objectTypes[$target], array_shift($ids), $journal);
            }

            if ($result === true) {
                $request->redirect(
                    null, null, null,
                    ['plugin', $this->getName(), $target.'s'],
                    ($this->isTestMode($request) ? ['testMode' => 1] : null)
                );
                return;
            }
        }

        if ($result !== true) {
            if (is_array($result)) {
                foreach($result as $error) {
                    if (is_array($error) && count($error) >= 1) {
                        $this->_sendNotification(
                            $request,
                            $error[0],
                            NOTIFICATION_TYPE_ERROR,
                            ($error[1] ?? null)
                        );
                    }
                }
            }
            $path = ['plugin', $this->getName()];
            $request->redirect(null, null, null, $path);
        }
    }

    /**
     * Execute the plugin from the command line.
     * @see ImportExportPlugin::executeCLI()
     * @param string $scriptName
     * @param array $args
     * @return bool|array
     */
    public function executeCLI($scriptName, $args) {
        $result = [];
        $xmlFile = null;

        AppLocale::requireComponents([LOCALE_COMPONENT_APPLICATION_COMMON]);

        $command = strtolower_codesafe((string) array_shift($args));
        if (!in_array($command, ['export', 'register'], true)) {
            $result = false;
        }

        if ($command === 'export') {
            if (is_array($result)) {
                $xmlFile = array_shift($args);
                if (empty($xmlFile)) {
                    $result = false;
                }
            }
        }

        $journal = null;
        if (is_array($result)) {
            $journalPath = array_shift($args);
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getJournalByPath($journalPath);
            if (!$journal) {
                if ($journalPath !== '') {
                    $result[] = ['plugins.importexport.common.export.error.unknownJournal', $journalPath];
                } elseif(empty($result)) {
                    $result = false;
                }
            }
        }

        $objectType = '';
        $objectTypes = $this->getAllObjectTypes();
        if (is_array($result) && empty($result)) {
            $objectType = strtolower_codesafe((string) array_shift($args));

            if (substr($objectType, -1) === 's') $objectType = substr($objectType, 0, -1);
            if ($objectType === 'suppfile') $objectType = 'suppFile';

            if (!in_array($objectType, array_keys($objectTypes), true)) {
                $result[] = ['plugins.importexport.common.export.error.unknownObjectType', $objectType];
            }
        }

        if (is_array($result) && empty($result)) {
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
     * Display the plugin management interface.
     * @see ImportExportPlugin::manage()
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param Request|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        parent::manage($verb, $args, $message, $messageParams, $request);

        switch ($verb) {
            case 'settings':
                if (!$request) $request = Application::get()->getRequest();
                $journal = $request->getJournal();
                if (!$journal) return false;

                $configurationErrors = [];

                $doiPrefix = null;
                $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
                if (is_array($pubIdPlugins) && isset($pubIdPlugins['DOIPubIdPlugin'])) {
                    $doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting((int) $journal->getId(), 'doiPrefix');
                }
                if (empty($doiPrefix)) {
                    $configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
                }

                $form = $this->_instantiateSettingsForm($journal);
                foreach($form->getFormFields() as $fieldName => $fieldType) {
                    if ($form->isOptional($fieldName)) continue;

                    $setting = $this->getSetting((int) $journal->getId(), $fieldName);
                    if (empty($setting)) {
                        $configurationErrors[] = DOI_EXPORT_CONFIGERROR_SETTINGS;
                        break;
                    }
                }

                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign('configurationErrors', $configurationErrors);

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
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

            default:
                throw new \BadMethodCallException('Unknown management verb.');
        }
    }

    //
    // Protected template methods
    //

    /**
     * Return the directory below the files dir where export files should be placed.
     * @return string
     */
    public function getPluginId(): string {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Return the class name of the plug-in's settings form.
     * @return string
     */
    public function getSettingsFormClassName(): string {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Return the object types supported by this plug-in.
     * @return array
     */
    public function getAllObjectTypes(): array {
        return [
            'issue' => DOI_EXPORT_ISSUES,
            'article' => DOI_EXPORT_ARTICLES,
            'galley' => DOI_EXPORT_GALLEYS
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

        $issueIterator = $issueDao->getPublishedIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));
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
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displaySuppFileList($templateMgr, $journal): void {
        throw new \BadMethodCallException('Not implemented for this plug-in');
    }

    /**
     * Retrieve all published articles.
     * @param Journal $journal
     * @return array
     */
    public function getAllPublishedArticles($journal): array {
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleIterator = $publishedArticleDao->getPublishedArticlesByJournalId((int) $journal->getId());

        $articles = [];
        while ($article = $articleIterator->next()) {
            $issue = $this->_getArticleIssue($article, $journal);

            if ($issue && $issue->getPublished()) {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    /**
     * Identify published article and issue of the given article file.
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
     * @param Request $request
     * @param array $exportSpec
     * @param Journal $journal
     * @param string|null $outputFile
     * @return bool|array
     */
    public function exportObjects($request, $exportSpec, $journal, $outputFile = null) {
        $errors = [];

        if (count($exportSpec) > 1) {
            $errors = $this->_checkForTar();
            if (is_array($errors)) return $errors;
        }

        $result = $this->_getExportPath();
        if (is_array($result)) return $result;
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
            if (empty($outputDir)) $outputDir = getcwd();
            if (!is_writable($outputDir) || (file_exists($outputFile) && !is_writable($outputFile))) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                $errors[] = ['plugins.importexport.common.export.error.outputFileNotWritable', $outputFile];
                return $errors;
            }
            $fileManager = new FileManager();
            $fileManager->copyFile($finalExportFileName, $outputFile);
        }

        $this->cleanTmpfiles($exportPath, array_keys($exportFiles));

        return true;
    }

    /**
     * Register publishing objects.
     * @param Request $request
     * @param array $exportSpec
     * @param Journal $journal
     * @return bool|array
     */
    public function registerObjects($request, $exportSpec, $journal) {
        @set_time_limit(0);

        $result = $this->_getExportPath();
        if (is_array($result)) return $result;
        $exportPath = $result;

        $errors = [];
        $exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
        if ($exportFiles === false) {
            return $errors;
        }

        $arrayResult = [];
        $falseResult = false; 
        foreach($exportFiles as $exportFile => $objects) {
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
        if ($falseResult) return false;

        return true;
    }

    /**
     * Returns file name and file type of the target export file.
     * @param string $exportPath
     * @param int $exportType
     * @param int|null $objectId
     * @return string
     */
    public function getTargetFileName($exportPath, $exportType, $objectId = null): string {
        $targetFileName = $exportPath . date('Ymd-Hi-') . $this->getObjectName($exportType);

        if ($objectId === null) {
            $targetFileName .= 's.xml';
        } else {
            $targetFileName .= '-' . $objectId . '.xml';
        }
        return $targetFileName;
    }

    /**
     * Get a string representation of the object.
     * @param int $exportType
     * @return string
     */
    public function getObjectName($exportType): string {
        $objectNames = [
            DOI_EXPORT_ISSUES => 'issue',
            DOI_EXPORT_ARTICLES => 'article',
            DOI_EXPORT_GALLEYS => 'galley',
        ];
        if (!isset($objectNames[$exportType])) {
            throw new \RuntimeException('Unknown export type.');
        }
        return $objectNames[$exportType];
    }

    /**
     * The selected object can be exported if it has a DOI.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $foundObject
     * @param array $errors Output parameter for error messages
     * @return bool
     */
    public function canBeExported($foundObject, &$errors): bool {
        if ($foundObject instanceof PublishedArticle && (int)$foundObject->getStatus() === STATUS_ARCHIVED) {
            $errors[] = ['plugins.importexport.common.export.error.articleArchived', $foundObject->getId()];
            return false;
        }

        $doi = $foundObject->getPubId('doi');
        if (empty($doi)) {
            $errors[] = ['plugins.importexport.common.export.error.noDOIContent', $foundObject->getId()];
            return false;
        }

        return true;
    }

    /**
     * Generate the export data model.
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param string $targetPath
     * @param Journal $journal
     * @param array $errors
     * @return array|bool
     */
    public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Process the marking of the selected objects as registered.
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param Journal $journal
     */
    public function processMarkRegistered($request, $exportType, $objects, $journal): void {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Create a tar archive.
     * @param string $targetPath
     * @param string $targetFile
     * @param array $sourceFiles
     */
    public function tarFiles($targetPath, $targetFile, $sourceFiles): void {
        if (!$this->_checkedForTar) {
            throw new \RuntimeException('Tar availability must be checked before calling tarFiles.');
        }

        $tarBinary = Config::getVar('cli', 'tar');
        $tarCommand = $tarBinary . ' -czf ' . escapeshellarg($targetFile);
        $tarCommand .= ' -C ' . escapeshellarg($targetPath);
        $tarCommand .= ' --owner 0 --group 0 --';

        foreach($sourceFiles as $sourceFile) {
            if (dirname($sourceFile) . '/' !== $targetPath) continue;
            $tarCommand .= ' ' . escapeshellarg(basename($sourceFile));
        }

        exec($tarCommand);
    }

    /**
     * Register the given DOI.
     * @param Request $request
     * @param Journal $journal
     * @param array $objects
     * @param string $file
     * @return bool|array
     */
    public function registerDoi($request, $journal, $objects, $file) {
        throw new \BadMethodCallException('Not implemented for this plug-in');
    }

    /**
     * Check whether we are in test mode.
     * @param Request $request
     * @return bool
     */
    public function isTestMode($request): bool {
        return ($request->getUserVar('testMode') === '1');
    }

    /**
     * Mark an object as "registered".
     * @param Request $request
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $testPrefix
     */
    public function markRegistered($request, $object, $testPrefix = '10.1234'): void {
        $registeredDoi = $object->getPubId('doi');
        if (empty($registeredDoi)) return;
        
        if ($this->isTestMode($request)) {
            $registeredDoi = PKPString::regexp_replace('#^[^/]+/#', $testPrefix . '/', $registeredDoi);
        }
        $this->saveRegisteredDoi($object, $registeredDoi);
    }

    /**
     * Reset the given object.
     * @param int $objectType
     * @param int $objectId
     * @param Journal $journal
     * @return bool|array
     */
    public function resetRegistration($objectType, $objectId, $journal) {
        $errors = [];
        $objects = $this->_getObjectsFromIds($objectType, [$objectId], (int) $journal->getId(), $errors);
        if ($objects === false || count($objects) !== 1) {
            return $errors;
        }

        $this->saveRegisteredDoi($objects[0], '');

        return true;
    }

    /**
     * Set the object's "registeredDoi" setting.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $registeredDoi
     */
    public function saveRegisteredDoi($object, $registeredDoi): void {
        $configurations = [
            'Issue' => ['IssueDAO', 'updateIssue'],
            'Article' => ['ArticleDAO', 'updateArticle'],
            'ArticleGalley' => ['ArticleGalleyDAO', 'updateGalley'],
            'SuppFile' => ['SuppFileDAO', 'updateSuppFile']
        ];
        $foundConfig = false;
        $daoName = '';
        $daoMethod = '';

        foreach($configurations as $objectType => $configuration) {
            if (is_a($object, $objectType)) { 
                $foundConfig = true;
                list($daoName, $daoMethod) = $configuration;
                break;
            }
        }
        
        if (!$foundConfig) {
            throw new \RuntimeException('Unsupported object type for DOI registration.');
        }

        $this->registerDaoHook($daoName);
        $dao = DAORegistry::getDAO($daoName);
        $object->setData($this->getPluginId() . '::' . DOI_EXPORT_REGDOI, $registeredDoi);
        $dao->$daoMethod($object);
    }

    /**
     * Register the hook that adds an additional field name to objects.
     * @param string $daoName
     */
    public function registerDaoHook($daoName): void {
        HookRegistry::register(strtolower_codesafe($daoName) . '::getAdditionalFieldNames', [$this, 'getAdditionalFieldNames']);
    }

    /**
     * Hook callback for additional fields.
     * @param string $hookName
     * @param array $args
     */
    public function getAdditionalFieldNames($hookName, $args): void {
        $returner =& $args[1];
        if (is_array($returner)) {
            $returner[] = $this->getPluginId() . '::' . DOI_EXPORT_REGDOI;
        }
    }

    /**
     * Remove the given temporary files.
     * @param string $tempdir
     * @param array $tempfiles
     */
    public function cleanTmpfiles($tempdir, $tempfiles): void {
        foreach ($tempfiles as $tempfile) {
            $tempfilePath = dirname($tempfile) . '/';
            if ($tempdir !== $tempfilePath) continue;
            if (file_exists($tempfile)) {
                unlink($tempfile);
            }
        }
    }

    /**
     * Identify DAO and DAO method to extract objects.
     * @param int $exportType
     * @return array
     */
    public function getDaoName($exportType): array {
        $daoNames = [
            DOI_EXPORT_ISSUES => ['IssueDAO', 'getIssueById'],
            DOI_EXPORT_ARTICLES => ['PublishedArticleDAO', 'getPublishedArticleByArticleId'],
            DOI_EXPORT_GALLEYS => ['ArticleGalleyDAO', 'getGalley'],
        ];
        if (!isset($daoNames[$exportType])) {
            throw new \RuntimeException('Unknown export type for DAO mapping.');
        }
        return $daoNames[$exportType];
    }

    /**
     * Return a translation key for the "object not found" error.
     * @param int $exportType
     * @return string
     */
    public function getObjectNotFoundErrorKey($exportType): string {
        $errorKeys = [
            DOI_EXPORT_ISSUES => 'plugins.importexport.common.export.error.issueNotFound',
            DOI_EXPORT_ARTICLES => 'plugins.importexport.common.export.error.articleNotFound',
            DOI_EXPORT_GALLEYS => 'plugins.importexport.common.export.error.galleyNotFound'
        ];
        if (!isset($errorKeys[$exportType])) {
            throw new \RuntimeException('Unknown export type for error key mapping.');
        }
        return $errorKeys[$exportType];
    }

    //
    // Private helper methods
    //

    /**
     * Display the plug-in home page.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function _displayPluginHomePage($templateMgr, $journal): void {
        $this->setBreadcrumbs();

        $configurationErrors = [];

        $doiPrefix = null;
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        if (is_array($pubIdPlugins) && isset($pubIdPlugins['DOIPubIdPlugin'])) {
            $doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting((int) $journal->getId(), 'doiPrefix');
        }
        if (empty($doiPrefix)) {
            $configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
        }

        $form = $this->_instantiateSettingsForm($journal);
        foreach($form->getFormFields() as $fieldName => $fieldType) {
            if ($form->isOptional($fieldName)) continue;

            $setting = $this->getSetting((int) $journal->getId(), $fieldName);
            if (empty($setting)) {
                $configurationErrors[] = DOI_EXPORT_CONFIGERROR_SETTINGS;
                break;
            }
        }

        $templateMgr->assign('configurationErrors', $configurationErrors);
        $templateMgr->assign('journal', $journal);
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
        foreach($allArticles as $article) {
            $errors = [];
            if ($this->canBeExported($article, $errors)) {
                $articles[] = $article;
            }
        }

        $totalArticles = count($articles);
        $rangeInfo = Handler::getRangeInfo('articles');
        if ($rangeInfo && $rangeInfo->isValid()) {
            $articles = array_slice($articles, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
        }

        $articleData = [];
        foreach($articles as $article) {
            $preparedArticle = $this->_prepareArticleData($article, $journal);
            if (is_array($preparedArticle)) {
                $articleData[] = $preparedArticle;
            }
        }

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
    public function _displayGalleyList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        $allArticles = $this->getAllPublishedArticles($journal);

        $this->registerDaoHook('ArticleGalleyDAO');
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = [];
        foreach($allArticles as $article) {
            $articleGalleys = $galleyDao->getGalleysByArticle((int) $article->getId());

            foreach ($articleGalleys as $galley) {
                $errors = [];
                if ($this->canBeExported($galley, $errors)) {
                    $galleys[] = $galley;
                }
            }
        }

        $totalGalleys = count($galleys);
        $rangeInfo = Handler::getRangeInfo('galleys');
        if ($rangeInfo && $rangeInfo->isValid()) {
            $galleys = array_slice($galleys, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
        }

        $galleyData = [];
        foreach($galleys as $galley) {
            $preparedGalley = $this->_prepareGalleyData($galley, $journal);
            if (is_array($preparedGalley)) {
                $galleyData[] = $preparedGalley;
            }
        }

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
    public function _getUnregisteredIssues($journal): array {
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issues = $issueDao->getIssuesBySetting($this->getPluginId(). '::' . DOI_EXPORT_REGDOI, null, (int) $journal->getId());

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
    public function _getUnregisteredArticles($journal): array {
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articles = $publishedArticleDao->getBySetting($this->getPluginId(). '::' . DOI_EXPORT_REGDOI, null, (int) $journal->getId());

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
    public function _getUnregisteredGalleys($journal): array {
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = $galleyDao->getGalleysBySetting($this->getPluginId(). '::' . DOI_EXPORT_REGDOI, null, null, (int) $journal->getId());

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
    public function _prepareGalleyData($galley, $journal): ?array {
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
    public function _prepareArticleDataByArticleId($articleId, $journal): ?array {
        $cache = $this->getCache();

        $article = null;
        if (!$cache->isCached('articles', $articleId)) {
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $article = $publishedArticleDao->getPublishedArticleByArticleId((int) $articleId, (int) $journal->getId(), true);
            if (!($article instanceof PublishedArticle)) {
                return null;
            }
            $cache->add($article, null);
        }
        if (!$article) $article = $cache->get('articles', $articleId);
        if (!($article instanceof PublishedArticle)) return null;

        return $this->_prepareArticleData($article, $journal);
    }

    /**
     * Identify the issue of the given article.
     * @param PublishedArticle $article
     * @param Journal $journal
     * @return array|null
     */
    public function _prepareArticleData($article, $journal): ?array {
        $cache = $this->getCache();
        $cache->add($article, null);

        $issue = $this->_getArticleIssue($article, $journal);

        if ($issue && $issue->getPublished()) {
            return [
                'article' => $article,
                'issue' => $issue
            ];
        } else {
            return null;
        }
    }

    /**
     * Return the issue of an article.
     * @param PublishedArticle|Article $article
     * @param Journal $journal
     * @return Issue|null
     */
    public function _getArticleIssue($article, $journal) {
        $issueId = (int) $article->getIssueId();

        $cache = $this->getCache();
        if (!$cache->isCached('issues', $issueId)) {
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($issueId, (int) $journal->getId(), true);
            if (!($issue instanceof Issue)) return null;
            $cache->add($issue, null);
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
    public function _generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, &$errors) {
        $exportFiles = [];
        foreach($exportSpec as $exportType => $objectIds) {
            if (is_scalar($objectIds)) $objectIds = [$objectIds];

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
     * Test whether the tar binary is available.
     * @return bool|array
     */
    public function _checkForTar() {
        $tarBinary = Config::getVar('cli', 'tar');
        if (empty($tarBinary) || !is_executable((string) $tarBinary)) {
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
    public function _getExportPath() {
        $exportPath = Config::getVar('files', 'files_dir') . '/' . $this->getPluginId();
        if (!file_exists($exportPath)) {
            $fileManager = new FileManager();
            $fileManager->mkdir($exportPath);
        }
        if (!is_writable($exportPath)) {
            $errors = [['plugins.importexport.common.export.error.outputFileNotWritable', $exportPath]];
            return $errors;
        }
        return realpath($exportPath) . '/';
    }

    /**
     * Retrieve the objects corresponding to the given ids.
     * @param int $exportType
     * @param array|int $objectIds
     * @param int $journalId
     * @param array $errors
     * @return array|bool
     */
    public function _getObjectsFromIds($exportType, $objectIds, $journalId, &$errors) {
        if (empty($objectIds)) return false;
        if (!is_array($objectIds)) $objectIds = [$objectIds];

        list($daoName, $daoMethodName) = $this->getDaoName($exportType);
        $dao = DAORegistry::getDAO($daoName);
        $daoMethod = [$dao, $daoMethodName];

        $objects = [];
        foreach ($objectIds as $objectId) {
            $daoMethodArgs = [$objectId];
            if ($exportType !== DOI_EXPORT_GALLEYS && $exportType !== DOI_EXPORT_SUPPFILES) {
                $daoMethodArgs[] = $journalId;
            }
            $foundObjects = call_user_func_array($daoMethod, $daoMethodArgs);
            if (!$foundObjects || empty($foundObjects)) {
                $objectNotFoundKey = $this->getObjectNotFoundErrorKey($exportType);
                $errors[] = [$objectNotFoundKey, $objectId];
                return false;
            }

            if (!is_array($foundObjects)) $foundObjects = [$foundObjects];
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
    public function _usage($scriptName, $errors = null): void {
        if (is_array($errors) && !empty($errors)) {
            echo __('plugins.importexport.common.cliError') . "\n";
            foreach ($errors as $error) {
                if (!is_array($error) || count($error) < 1) continue;
                if (isset($error[1])) {
                    $errorMessage = __($error[0], ['param' => $error[1]]);
                } else {
                    $errorMessage = __($error[0]);
                }
                echo "*** $errorMessage\n";
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
    public function _instantiateSettingsForm($journal) {
        $settingsFormClassName = $this->getSettingsFormClassName();
        $this->import('classes.form.' . $settingsFormClassName);
        $settingsForm = new $settingsFormClassName($this, (int) $journal->getId());
        
        return $settingsForm;
    }

    /**
     * Add a notification.
     * @param Request $request
     * @param string $message
     * @param int $notificationType
     * @param string|null $param
     */
    protected function _sendNotification($request, string $message, int $notificationType, ?string $param = null): void {
        if (!class_exists('NotificationManager')) {
            import('classes.notification.NotificationManager');
        }
        $notificationManager = new NotificationManager();

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

    /**
     * Hook callback to parse cron tab.
     * @see AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args) {
        return false;
    }

}
?>