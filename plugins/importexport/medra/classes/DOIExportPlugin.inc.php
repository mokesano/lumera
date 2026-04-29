<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/common/classes/DOIExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIExportPlugin
 * @ingroup plugins_importexport_classes
 *
 * @brief Base class for DOI export/registration plugins.
 * [WIZDAM EDITION] Refactored for PHP 7.4/8.0+ (Strict Types, NotificationManager Integration)
 */

import('classes.plugins.ImportExportPlugin');

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

class DOIExportPlugin extends ImportExportPlugin {

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
    public function DOIExportPlugin() {
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
            // Instantiate the cache.
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
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();

        HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);

        return $success;
    }

    /**
     * Get the path to the templates.
     * @see PKPPlugin::getTemplatePath()
     * @param bool $inCore
     * @return string
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath($inCore) . 'templates/';
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

        // Add shared locale keys.
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
     * @param PKPRequest|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs();
        $verbs[] = ['settings', __('plugins.importexport.common.settings')];
        return $verbs;
    }

    /**
     * Display the plugin interface.
     * @see ImportExportPlugin::display()
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function display($args, $request): void {
        parent::display($args, $request);
        $templateMgr = TemplateManager::getManager();

        // Retrieve journal from the request context.
        $journal = $request->getJournal();

        $op = array_shift($args);

        switch($op) {
            // Show the plugin homepage
            case '':
            case 'index':
                $this->_displayPluginHomePage($templateMgr, $journal);
                return;

            // Display cases: show a list of the specified objects
            case 'all':
            case 'issues':
            case 'articles':
            case 'galleys':
            case 'suppFiles':
                // Test mode.
                $templateMgr->assign('testMode', $this->isTestMode($request) ? ['testMode' => 1] : []);
                $templateMgr->assign('filter', $request->getUserVar('filter'));

                // Export without account.
                $username = $this->getSetting($journal->getId(), 'username');
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

            // Process register/reset/export/mark actions.
            case 'process':
                $this->process($request, $journal);
                break;

            default:
                fatalError('Invalid command.');
        }
    }

    /**
     * Process a DOI activity request.
     * [WIZDAM PROTOCOL] Using NotificationManager for UI feedback logic.
     * @param PKPRequest $request
     * @param Journal $journal
     * @return void
     */
    public function process($request, $journal): void {
        $objectTypes = $this->getAllObjectTypes();
        $target = $request->getUserVar('target');
        $result = false;
        $errors = [];

        // Dispatch the action.
        switch(true) {
            case $request->getUserVar('export'):
            case $request->getUserVar('register'):
            case $request->getUserVar('markRegistered'):
                // Find the objects to be exported (registered).
                if ($target == 'all') {
                    $exportSpec = [];
                    foreach ($objectTypes as $objectName => $exportType) {
                        $objectIds = (array) $request->getUserVar($objectName . 'Id');
                        if (!empty($objectIds)) {
                            $exportSpec[$exportType] = $objectIds;
                        }
                    }
                } else {
                    assert(isset($objectTypes[$target]));
                    $exportSpec = [$objectTypes[$target] => (array) $request->getUserVar($target . 'Id')];
                }

                if ($request->getUserVar('export')) {
                    // Export selected objects.
                    $result = $this->exportObjects($request, $exportSpec, $journal);
                } elseif ($request->getUserVar('markRegistered')) {
                    foreach($exportSpec as $exportType => $objectIds) {
                        // Normalize the object id(s) into an array.
                        if (is_scalar($objectIds)) $objectIds = [$objectIds];
                        // Retrieve the object(s).
                        $objects = $this->_getObjectsFromIds($exportType, $objectIds, $journal->getId(), $errors);
                        $this->processMarkRegistered($request, $exportType, $objects, $journal);
                    }
                    // Redisplay the changed object list.
                    $listAction = $target . ($target == 'all' ? '' : 's');
                    $request->redirect(
                        null, null, null,
                        ['plugin', $this->getName(), $listAction],
                        ($this->isTestMode($request) ? ['testMode' => 1] : null)
                    );
                    return;
                } else { // Register selected objects.
                    assert($request->getUserVar('register') != false);
                    $result = $this->registerObjects($request, $exportSpec, $journal);

                    // Provide the user with some visual feedback that registration was successful.
                    if ($result === true) {
                        $this->_sendNotification(
                            $request,
                            'plugins.importexport.'.$this->getPluginId() .'.register.success',
                            NOTIFICATION_TYPE_SUCCESS
                        );

                        // Redisplay the changed object list.
                        $listAction = $target . ($target == 'all' ? '' : 's');
                        $request->redirect(
                            null, null, null,
                            ['plugin', $this->getName(), $listAction],
                            ($this->isTestMode($request) ? ['testMode' => 1] : null)
                        );
                    }
                }
                break;
            case $request->getUserVar('reset'):
                // Reset the selected target object to "unregistered" state.
                $ids = (array) $request->getUserVar($target . 'Id');
                $result = $this->resetRegistration($objectTypes[$target], array_shift($ids), $journal);

                // Redisplay the changed object list.
                if ($result === true) {
                    $request->redirect(
                        null, null, null,
                        ['plugin', $this->getName(), $target.'s'],
                        ($this->isTestMode($request) ? ['testMode' => 1] : null)
                    );
                }
                break;
        }

        // If something went wrong, display errors using NotificationManager
        if ($result !== true) {
            if (is_array($result)) {
                foreach($result as $error) {
                    assert(is_array($error) && count($error) >= 1);
                    $this->_sendNotification(
                        $request,
                        $error[0],
                        NOTIFICATION_TYPE_ERROR,
                        ($error[1] ?? null)
                    );
                }
            }
            // Redirect back to plugin page
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

        // Add additional locale file.
        AppLocale::requireComponents([LOCALE_COMPONENT_APPLICATION_COMMON]);

        // Command.
        $command = strtolower_codesafe(array_shift($args));
        if (!in_array($command, ['export', 'register'])) {
            $result = false;
        }

        if ($command == 'export') {
            // Output file.
            if (is_array($result)) {
                $xmlFile = array_shift($args);
                if (empty($xmlFile)) {
                    $result = false;
                }
            }
        }

        // Journal.
        $journal = null;
        if (is_array($result)) {
            $journalPath = array_shift($args);
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getJournalByPath($journalPath);
            if (!$journal) {
                if ($journalPath != '') {
                    $result[] = ['plugins.importexport.common.export.error.unknownJournal', $journalPath];
                } elseif(empty($result)) {
                    $result = false;
                }
            }
        }

        // Object type.
        $objectType = '';
        if (is_array($result) && empty($result)) {
            $objectType = strtolower_codesafe(array_shift($args));

            // Accept both singular and plural forms.
            if (substr($objectType, -1) == 's') $objectType = substr($objectType, 0, -1);
            if ($objectType == 'suppfile') $objectType = 'suppFile';

            // Check whether the object type exists.
            $objectTypes = $this->getAllObjectTypes();
            if (!in_array($objectType, array_keys($objectTypes))) {
                // Return an error for unhandled object types.
                $result[] = ['plugins.importexport.common.export.error.unknownObjectType', $objectType];
            }
        }

        // Export (or register) objects.
        if (is_array($result) && empty($result)) {
            assert(isset($objectTypes[$objectType]));
            $exportSpec = [$objectTypes[$objectType] => $args];
            $request = Application::getRequest();
            if ($command == 'export') {
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
     * [WIZDAM PROTOCOL] Removing reference & from $message/$messageParams to match parent class signature and Protocol #3.
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array $messageParams
     * @param PKPRequest|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message = null, $messageParams = null, $request = null): bool {
        parent::manage($verb, $args, $message, $messageParams, $request);

        switch ($verb) {
            case 'settings':
                $journal = $request->getJournal();
                $form = $this->_instantiateSettingsForm($journal);

                // Check for configuration errors:
                $configurationErrors = [];

                // 1) missing DOI prefix
                $doiPrefix = null;
                $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
                if (isset($pubIdPlugins['DOIPubIdPlugin'])) {
                    $doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting($journal->getId(), 'doiPrefix');
                }
                if (empty($doiPrefix)) {
                    $configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
                }

                // 2) missing plug-in setting.
                $form = $this->_instantiateSettingsForm($journal);
                foreach($form->getFormFields() as $fieldName => $fieldType) {
                    if ($form->isOptional($fieldName)) continue;

                    $setting = $this->getSetting($journal->getId(), $fieldName);
                    if (empty($setting)) {
                        $configurationErrors[] = DOI_EXPORT_CONFIGERROR_SETTINGS;
                        break;
                    }
                }

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('configurationErrors', $configurationErrors);

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'importexport', ['plugin', $this->getName()]);
                    } else {
                        $this->setBreadCrumbs([], true);
                        $form->display($request);
                    }
                } else {
                    $this->setBreadCrumbs([], true);
                    $form->initData();
                    $form->display($request);
                }
                return true;

            default:
                // Unknown management verb.
                assert(false);
        }
        return false;
    }

    //
    // Protected template methods
    //

    /**
     * Return the directory below the files dir where export files should be placed.
     * @return string
     */
    public function getPluginId(): string {
        assert(false);
        return '';
    }

    /**
     * Return the class name of the plug-in's settings form.
     * @return string
     */
    public function getSettingsFormClassName(): string {
        assert(false);
        return '';
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

        // Retrieve all published issues.
        AppLocale::requireComponents([LOCALE_COMPONENT_OJS_EDITOR]);
        $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
        $this->registerDaoHook('IssueDAO');
        $issueIterator = $issueDao->getPublishedIssues($journal->getId(), Handler::getRangeInfo('issues'));

        // Get issues that should be excluded i.e. that have no DOI.
        $excludes = [];
        $allExcluded = true;
        while ($issue = $issueIterator->next()) {
            $excludes[$issue->getId()] = true;
            $errors = [];
            if ($this->canBeExported($issue, $errors)) {
                $excludes[$issue->getId()] = false;
                $allExcluded = false;
            }
            unset($issue);
        }
        unset($issueIterator);

        // Prepare and display the issue template.
        // Get the issue iterator from the DB for the template again.
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
        AppLocale::requireComponents([LOCALE_COMPONENT_PKP_SUBMISSION]);

        // Prepare and display the template.
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
        fatalError('Not implemented for this plug-in');
    }

    /**
     * Retrieve all published articles.
     * @param Journal $journal
     * @return array
     */
    public function getAllPublishedArticles($journal): array {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
        $articleIterator = $publishedArticleDao->getPublishedArticlesByJournalId($journal->getId());

        // Return articles from published issues only.
        $articles = [];
        while ($article = $articleIterator->next()) {
            // Retrieve issue
            $issue = $this->_getArticleIssue($article, $journal);

            // Check whether the issue is published.
            if ($issue && $issue->getPublished()) {
                $articles[] = $article;
                unset($article);
            }
        }
        unset($articleIterator);

        return $articles;
    }

    /**
     * Identify published article and issue of the given article file.
     * @param ArticleFile $articleFile
     * @param Journal $journal
     * @return array|null
     */
    public function prepareArticleFileData($articleFile, $journal): ?array {
        // Prepare and return article data for the article file.
        $articleData = $this->_prepareArticleDataByArticleId($articleFile->getArticleId(), $journal);
        if (!is_array($articleData)) {
            return null;
        }

        // Add the article file to the cache.
        $cache = $this->getCache();
        $cache->add($articleFile, $articleData['article']);

        return $articleData;
    }

    /**
     * Export publishing objects.
     * @param PKPRequest $request
     * @param array $exportSpec
     * @param Journal $journal
     * @param string|null $outputFile
     * @return bool|array
     */
    public function exportObjects($request, $exportSpec, $journal, $outputFile = null) {
        // Initialize local variables.
        $errors = [];

        // If we have more than one object type, then we'll need the
        // tar tool to package the resulting export files. Check this
        // early on to avoid unnecessary export processing.
        if (count($exportSpec) > 1) {
            $errors = $this->_checkForTar();
            if (is_array($errors)) return $errors;
        }

        // Get the target directory.
        $result = $this->_getExportPath();
        if (is_array($result)) return $result;
        $exportPath = $result;

        // Run through the export spec and generate the corresponding
        // export files.
        $exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
        if ($exportFiles === false) {
            return $errors;
        }

        // Check whether we need the tar tool for this export if
        // we've not checked this before.
        if (count($exportFiles) > 1 && !$this->_checkedForTar) {
            $errors = $this->_checkForTar();
            if (is_array($errors)) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return $errors;
            }
        }

        // If we have more than one export file we package the files
        // up as a single tar before going on.
        assert(count($exportFiles) >= 1);
        if (count($exportFiles) > 1) {
            $finalExportFileName = $exportPath . $this->getPluginId() . '-export.tar.gz';
            $finalExportFileType = DOI_EXPORT_FILE_TAR;
            $this->tarFiles($exportPath, $finalExportFileName, array_keys($exportFiles));
            $exportFiles[$finalExportFileName] = [];
        } else {
            $finalExportFileName = key($exportFiles);
            $finalExportFileType = DOI_EXPORT_FILE_XML;
        }

        // Stream the results to the browser...
        if (is_null($outputFile)) {
            header('Content-Type: application/' . ($finalExportFileType == DOI_EXPORT_FILE_TAR ? 'x-gtar' : 'xml'));
            header('Cache-Control: private');
            header('Content-Disposition: attachment; filename="' . basename($finalExportFileName) . '"');
            readfile($finalExportFileName);

        // ...or save them as a file.
        } else {
            $outputFileExtension = ($finalExportFileType == DOI_EXPORT_FILE_TAR ? '.tar.gz' : '.xml');
            if (substr($outputFile, -strlen($outputFileExtension)) != $outputFileExtension) {
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

        // Remove all temporary files.
        $this->cleanTmpfiles($exportPath, array_keys($exportFiles));

        return true;
    }

    /**
     * Register publishing objects.
     * @param PKPRequest $request
     * @param array $exportSpec
     * @param Journal $journal
     * @return bool|array
     */
    public function registerObjects($request, $exportSpec, $journal) {
        // Registering can take a long time.
        @set_time_limit(0);

        // Get the target directory.
        $result = $this->_getExportPath();
        if (is_array($result)) return $result;
        $exportPath = $result;

        // Run through the export spec and generate the corresponding
        // export files.
        $errors = [];
        $exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
        if ($exportFiles === false) {
            return $errors;
        }

        $arrayResult = [];
        $falseResult = false; // medra can return also false
        // Register DOIs and their meta-data.
        foreach($exportFiles as $exportFile => $objects) {
            $result = $this->registerDoi($request, $journal, $objects, $exportFile);
            if ($result !== true) {
                if (is_array($result)) {
                    $arrayResult = array_merge($arrayResult, $result);
                } elseif ($result == false) {
                    $falseResult = true;
                }
            }
        }

        // Remove all temporary files.
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
        // Define the prefix of the exported files.
        $targetFileName = $exportPath . date('Ymd-Hi-') . $this->getObjectName($exportType);

        // Define the target file type and the final target file name.
        if (is_null($objectId)) {
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
        assert(isset($objectNames[$exportType]));
        return $objectNames[$exportType];
    }

    /**
     * The selected object can be exported if it has a DOI.
     * [WIZDAM FIX] Populates &$errors so NotificationManager knows why it failed.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $foundObject
     * @param array $errors Output parameter for error messages
     * @return bool
     */
    public function canBeExported($foundObject, &$errors): bool {
        // Check if article is archived
        if ($foundObject instanceof PublishedArticle && (int)$foundObject->getStatus() === STATUS_ARCHIVED) {
            // Push specific error for UI
            $errors[] = ['plugins.importexport.common.export.error.articleArchived', $foundObject->getId()];
            return false;
        }

        // Check if DOI content is empty (null or empty string)
        $doi = $foundObject->getPubId('doi');
        if (empty($doi)) {
             // Push specific error for UI
            $errors[] = ['plugins.importexport.common.export.error.noDOIContent', $foundObject->getId()];
            return false;
        }

        return true;
    }

    /**
     * Generate the export data model.
     * @param PKPRequest $request
     * @param int $exportType
     * @param array $objects
     * @param string $targetPath
     * @param Journal $journal
     * @param array $errors
     * @return array|bool
     */
    public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
        assert(false);
        return false;
    }

    /**
     * Process the marking of the selected objects as registered.
     * @param PKPRequest $request
     * @param int $exportType
     * @param array $objects
     * @param Journal $journal
     */
    public function processMarkRegistered($request, $exportType, $objects, $journal): void {
        assert(false);
    }

    /**
     * Create a tar archive.
     * @param string $targetPath
     * @param string $targetFile
     * @param array $sourceFiles
     */
    public function tarFiles($targetPath, $targetFile, $sourceFiles): void {
        assert($this->_checkedForTar);

        // GZip compressed result file.
        $tarCommand = Config::getVar('cli', 'tar') . ' -czf ' . escapeshellarg($targetFile);

        // Do not reveal our internal export path by exporting only relative filenames.
        $tarCommand .= ' -C ' . escapeshellarg($targetPath);

        // Do not reveal our webserver user by forcing root as owner.
        $tarCommand .= ' --owner 0 --group 0 --';

        // Add each file individually so that other files in the directory
        // will not be included.
        foreach($sourceFiles as $sourceFile) {
            assert(dirname($sourceFile) . '/' === $targetPath);
            if (dirname($sourceFile) . '/' !== $targetPath) continue;
            $tarCommand .= ' ' . escapeshellarg(basename($sourceFile));
        }

        // Execute the command.
        exec($tarCommand);
    }

    /**
     * Register the given DOI.
     * @param PKPRequest $request
     * @param Journal $journal
     * @param array $objects
     * @param string $file
     * @return bool|array
     */
    public function registerDoi($request, $journal, $objects, $file) {
        fatalError('Not implemented for this plug-in');
        return false;
    }

    /**
     * Check whether we are in test mode.
     * @param PKPRequest $request
     * @return bool
     */
    public function isTestMode($request): bool {
        return ($request->getUserVar('testMode') == '1');
    }

    /**
     * Mark an object as "registered".
     * @param PKPRequest $request
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $testPrefix
     */
    public function markRegistered($request, $object, $testPrefix = '10.1234'): void {
        $registeredDoi = $object->getPubId('doi');
        assert(!empty($registeredDoi));
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
        // Identify the object to be reset.
        $errors = [];
        $objects = $this->_getObjectsFromIds($objectType, [$objectId], $journal->getId(), $errors);
        if ($objects === false || count($objects) != 1) {
            return $errors;
        }

        // Reset the object.
        $this->saveRegisteredDoi($objects[0], '');

        return true;
    }

    /**
     * Set the object's "registeredDoi" setting.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $registeredDoi
     */
    public function saveRegisteredDoi($object, $registeredDoi): void {
        // Identify the dao name and update method for the given object.
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
            // Using is_a for string-based check compatibility
            if (is_a($object, $objectType)) { 
                $foundConfig = true;
                list($daoName, $daoMethod) = $configuration;
                break;
            }
        }
        assert($foundConfig);

        // Register a hook for the required additional object fields.
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
        assert(count($args) == 2);
        // $dao =& $args[0]; 
        $returner =& $args[1];
        assert(is_array($returner));
        $returner[] = $this->getPluginId() . '::' . DOI_EXPORT_REGDOI;
    }

    /**
     * Remove the given temporary files.
     * @param string $tempdir
     * @param array $tempfiles
     */
    public function cleanTmpfiles($tempdir, $tempfiles): void {
        foreach ($tempfiles as $tempfile) {
            $tempfilePath = dirname($tempfile) . '/';
            assert($tempdir === $tempfilePath);
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
        assert(isset($daoNames[$exportType]));
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
        assert(isset($errorKeys[$exportType]));
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

        // Check for configuration errors:
        $configurationErrors = [];

        // 1) missing DOI prefix
        $doiPrefix = null;
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        if (isset($pubIdPlugins['DOIPubIdPlugin'])) {
            $doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting($journal->getId(), 'doiPrefix');
        }
        if (empty($doiPrefix)) {
            $configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
        }

        // 2) missing plug-in setting.
        $form = $this->_instantiateSettingsForm($journal);
        foreach($form->getFormFields() as $fieldName => $fieldType) {
            if ($form->isOptional($fieldName)) continue;

            $setting = $this->getSetting($journal->getId(), $fieldName);
            if (empty($setting)) {
                $configurationErrors[] = DOI_EXPORT_CONFIGERROR_SETTINGS;
                break;
            }
        }

        $templateMgr->assign('configurationErrors', $configurationErrors);

        // Prepare and display the index page template.
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

        // Retrieve all published articles.
        $this->registerDaoHook('PublishedArticleDAO');
        $allArticles = $this->getAllPublishedArticles($journal);

        // Filter only articles that can be exported.
        $articles = [];
        foreach($allArticles as $article) {
            $errors = [];
            if ($this->canBeExported($article, $errors)) {
                $articles[] = $article;
            }
            unset($article);
        }
        unset($allArticles);

        // Paginate articles.
        $totalArticles = count($articles);
        $rangeInfo = Handler::getRangeInfo('articles');
        if ($rangeInfo->isValid()) {
            $articles = array_slice($articles, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
        }

        // Retrieve article data.
        $articleData = [];
        foreach($articles as $article) {
            $preparedArticle = $this->_prepareArticleData($article, $journal);
            assert(is_array($preparedArticle));
            $articleData[] = $preparedArticle;
            unset($article, $preparedArticle);
        }
        unset($articles);

        // Instantiate article iterator.
        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($articleData, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());

        // Prepare and display the article template.
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

        // Retrieve all published articles.
        $allArticles = $this->getAllPublishedArticles($journal);

        // Retrieve galley data.
        $this->registerDaoHook('ArticleGalleyDAO');
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $galleyDao ArticleGalleyDAO */
        $galleys = [];
        foreach($allArticles as $article) {
            // Retrieve galleys for the article.
            $articleGalleys = $galleyDao->getGalleysByArticle($article->getId());

            // Filter only galleys that can be exported.
            foreach ($articleGalleys as $galley) {
                $errors = [];
                if ($this->canBeExported($galley, $errors)) {
                    $galleys[] = $galley;
                }
                unset($galley);
            }
            unset($article, $articleGalleys);
        }
        unset($allArticles);

        // Paginate galleys.
        $totalGalleys = count($galleys);
        $rangeInfo = Handler::getRangeInfo('galleys');
        if ($rangeInfo->isValid()) {
            $galleys = array_slice($galleys, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
        }

        // Retrieve galley data.
        $galleyData = [];
        foreach($galleys as $galley) {
            $preparedGalley = $this->_prepareGalleyData($galley, $journal);
            assert(is_array($preparedGalley));
            if (is_array($preparedGalley)) {
                $galleyData[] = $preparedGalley;
            }
            unset($galley, $preparedGalley);
        }
        unset($galleys);

        // Instantiate galley iterator.
        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($galleyData, $totalGalleys, $rangeInfo->getPage(), $rangeInfo->getCount());

        // Prepare and display the galley template.
        $templateMgr->assign('galleys', $iterator);
        $templateMgr->display($this->getTemplatePath() . 'galleys.tpl');
    }

    /**
     * Retrieve all unregistered issues.
     * @param Journal $journal
     * @return array
     */
    public function _getUnregisteredIssues($journal): array {
        // Retrieve all issues that have not yet been registered.
        $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
        $issues = $issueDao->getIssuesBySetting($this->getPluginId(). '::' . DOI_EXPORT_REGDOI, null, $journal->getId());

        // Filter and cache issues.
        $nullVar = null;
        $cache = $this->getCache();
        $issueData = [];
        foreach ($issues as $issue) {
            $cache->add($issue, $nullVar);
            if ($issue->getPublished()) {
                // Only propose published issues for export.
                $issueData[] = $issue;
            }
            unset($issue);
        }
        return $issueData;
    }

    /**
     * Retrieve all unregistered articles and their corresponding issues.
     * @param Journal $journal
     * @return array
     */
    public function _getUnregisteredArticles($journal): array {
        // Retrieve all published articles that have not yet been registered.
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
        $articles = $publishedArticleDao->getBySetting($this->getPluginId(). '::' . DOI_EXPORT_REGDOI, null, $journal->getId());

        // Retrieve issues for articles.
        $articleData = [];
        foreach ($articles as $article) {
            $preparedArticle = $this->_prepareArticleData($article, $journal);
            if (is_array($preparedArticle)) {
                $articleData[] = $preparedArticle;
            }
            unset($article, $preparedArticle);
        }
        return $articleData;
    }

    /**
     * Retrieve all unregistered galleys and their corresponding issues and articles.
     * @param Journal $journal
     * @return array
     */
    public function _getUnregisteredGalleys($journal): array {
        // Retrieve all galleys that have not yet been registered.
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $galleyDao ArticleGalleyDAO */
        $galleys = $galleyDao->getGalleysBySetting($this->getPluginId(). '::' . DOI_EXPORT_REGDOI, null, null, $journal->getId());

        // Retrieve issues, articles and language for galleys.
        $galleyData = [];
        foreach ($galleys as $galley) {
            $preparedGalley = $this->_prepareGalleyData($galley, $journal);
            if (is_array($preparedGalley)) {
                $galleyData[] = $preparedGalley;
            }
            unset($galley, $preparedGalley);
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
        // Retrieve article and issue for the galley.
        $galleyData = $this->prepareArticleFileData($galley, $journal);
        if (!is_array($galleyData)) {
            return null;
        }

        // Add the galley language.
        $languageDao = DAORegistry::getDAO('LanguageDAO'); /* @var $languageDao LanguageDAO */
        $galleyData['language'] = $languageDao->getLanguageByCode(AppLocale::getIso1FromLocale($galley->getLocale()));

        // Add the galley itself.
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
        // Get the cache.
        $cache = $this->getCache();

        // Retrieve article if not yet cached.
        $article = null;
        if (!$cache->isCached('articles', $articleId)) {
            $nullVar = null;
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
            $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId, $journal->getId(), true);
            if (!($article instanceof PublishedArticle)) {
                return null;
            }
            $cache->add($article, $nullVar);
        }
        if (!$article) $article = $cache->get('articles', $articleId);
        assert($article instanceof PublishedArticle);

        // Prepare and return article data for the article file.
        return $this->_prepareArticleData($article, $journal);
    }

    /**
     * Identify the issue of the given article.
     * @param PublishedArticle $article
     * @param Journal $journal
     * @return array|null
     */
    public function _prepareArticleData($article, $journal): ?array {
        // Add the article to the cache.
        $cache = $this->getCache();
        $cache->add($article, null);

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
     * Return the issue of an article.
     * @param Article $article
     * @param Journal $journal
     * @return Issue
     */
    public function _getArticleIssue($article, $journal) {
        $issueId = $article->getIssueId();

        // Retrieve issue if not yet cached.
        $cache = $this->getCache();
        if (!$cache->isCached('issues', $issueId)) {
            $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
            $issue = $issueDao->getIssueById($issueId, $journal->getId(), true);
            assert($issue instanceof Issue);
            $cache->add($issue, null);
            unset($issue);
        }

        return $cache->get('issues', $issueId);
    }

    /**
     * Generate export files for the given export spec.
     * @param PKPRequest $request
     * @param Journal $journal
     * @param array $exportSpec
     * @param string $exportPath
     * @param array $errors
     * @return array|bool
     */
    public function _generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, &$errors) {
        $exportFiles = [];
        foreach($exportSpec as $exportType => $objectIds) {
            // Normalize the object id(s) into an array.
            if (is_scalar($objectIds)) $objectIds = [$objectIds];

            // Retrieve the object(s).
            $objects = $this->_getObjectsFromIds($exportType, $objectIds, $journal->getId(), $errors);
            if (empty($objects)) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return false;
            }

            // Export the object(s) to a file.
            $newFiles = $this->generateExportFiles($request, $exportType, $objects, $exportPath, $journal, $errors);
            if ($newFiles === false) {
                $this->cleanTmpfiles($exportPath, array_keys($exportFiles));
                return false;
            }

            // Add the new files to the result array.
            $exportFiles = array_merge($exportFiles, $newFiles);
        }

        return $exportFiles;
    }

    /**
     * Test whether the tar binary is available.
     * @return bool|array Boolean true if available otherwise an array with an error message.
     */
    public function _checkForTar() {
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
     * @return string|array The export directory name or an array with errors if something went wrong.
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

        // Instantiate the correct DAO.
        list($daoName, $daoMethodName) = $this->getDaoName($exportType);
        $dao = DAORegistry::getDAO($daoName);
        $daoMethod = [$dao, $daoMethodName];

        $objects = [];
        foreach ($objectIds as $objectId) {
            // Retrieve the objects from the DAO.
            $daoMethodArgs = [$objectId];
            if ($exportType != DOI_EXPORT_GALLEYS && $exportType != DOI_EXPORT_SUPPFILES) {
                $daoMethodArgs[] = $journalId;
            }
            $foundObjects = call_user_func_array($daoMethod, $daoMethodArgs);
            if (!$foundObjects || empty($foundObjects)) {
                $objectNotFoundKey = $this->getObjectNotFoundErrorKey($exportType);
                $errors[] = [$objectNotFoundKey, $objectId];
                return false;
            }

            // Add the objects to our result array.
            if (!is_array($foundObjects)) $foundObjects = [$foundObjects];
            foreach ($foundObjects as $foundObject) {
                // Only consider objects that should be exported.
                // NB: This may generate DOIs for the selected objects on the fly.
                if ($this->canBeExported($foundObject, $errors)) {
                    $objects[] = $foundObject;
                } else {
                    return false;
                }
                unset($foundObject);
            }
            unset($foundObjects);
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
                assert(is_array($error) && count($error) >=1);
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
        $settingsForm = new $settingsFormClassName($this, $journal->getId());
        return $settingsForm;
    }

    /**
     * Add a notification.
     * [WIZDAM PROTOCOL] Refactored: Removed references (&), typed parameters, safe instantiation.
     * @param PKPRequest $request
     * @param string $message An i18n key.
     * @param int $notificationType One of the NOTIFICATION_TYPE_* constants.
     * @param string|null $param An additional parameter for the message.
     */
    protected function _sendNotification($request, string $message, int $notificationType, ?string $param = null): void {
        // Optimization: Instantiate directly without static caching if simpler, 
        // or check class existence if lazy loading is needed.
        if (!class_exists('NotificationManager')) {
            import('classes.notification.NotificationManager');
        }
        $notificationManager = new NotificationManager();

        $params = $param !== null ? ['param' => $param] : null;

        $user = $request->getUser();
        if ($user) {
            $notificationManager->createTrivialNotification(
                $user->getId(),
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
     * @return bool False to let other plugins process the hook as well.
     */
    public function callbackParseCronTab($hookName, $args) {
        return false;
    }
}
?>