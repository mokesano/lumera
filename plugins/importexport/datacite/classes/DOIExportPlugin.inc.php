<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/.../classes/DOIExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIExportPlugin
 * @ingroup plugins_importexport_..._classes
 *
 * @brief Base class for DOI export/registration plugins.
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
	/** @var PubObjectCache */
	private $_cache;

    /**
     * Get the publication object cache.
     * @return PubObjectCache
     */
	public function getCache() {
		if (!($this->_cache instanceof PubObjectCache)) {
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
	/** @var boolean */
	private $_checkedForTar = false;


    /**
     * Constructor
     */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * [SHIM]: Backward compatibility
	 * @deprecated Use __construct() instead
	 */
	public function DOIExportPlugin() {
		if (Config::getVar('debug', 'deprecation_warnings')) {
			trigger_error(
				"DEPRECATED: Class '" . get_class($this) . "' uses deprecated constructor parent::DOIExportPlugin() causes overhead and breaks PHP 8 compatibility. " .
				"ACTION: Refactor calls to use 'new DOIExportPlugin()' which automatically invokes parent::__construct().",
				E_USER_DEPRECATED
			);
		}
		$args = func_get_args();
		call_user_func_array(array($this, '__construct'), $args);
	}


	//
	// Implement template methods from PKPPlugin
	//
	/**
     * Register the plugin.
	 * @see PKPPlugin::register()
     * @param $category string
     * @param $path string
     * @return boolean True if successfully registered.
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
	 * 
     * @return string
	 */
	public function getTemplatePath(): string {
		return parent::getTemplatePath().'templates/';
	}

	/**
     * Get the path to the context-specific settings file.
	 * @see PKPPlugin::getInstallSitePluginSettingsFile()
	 * 
     * @return string
	 */
	public function getContextSpecificPluginSettingsFile(): string {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
     * Get the locale filename for a specific locale.
	 * @see PKPPlugin::getLocaleFilename($locale)
	 * 
     * @param $locale string
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
     * Get management verbs.
	 * @see ImportExportPlugin::getManagementVerbs()
	 * 
     * @param $verbs array
     * @param $request PKPRequest
     * @return array
	 */
	public function getManagementVerbs(array $verbs = [], $request = null): array {
		$verbs = parent::getManagementVerbs();
		$verbs[] = ['settings', __('plugins.importexport.common.settings')];
		return $verbs;
	}

	/**
     * Display the plugin.
	 * @see ImportExportPlugin::display()
	 * 
     * @param mixed $args array
     * @param mixed $request PKPRequest
     * @return void
	 */
	public function display($args, $request) {
		parent::display($args, $request);
		$templateMgr = TemplateManager::getManager();

		// Retrieve journal from the request context.
		$journal = $request->getJournal();

		$op = array_shift($args);

		switch($op) {
			// Show the plugin homepage
			case '':
			case 'index':
				return $this->_displayPluginHomePage($templateMgr, $journal);

			// Display cases: show a list of the specified objects
			case 'all':
			case 'issues':
			case 'articles':
			case 'galleys':
			case 'suppFiles':
				// Test mode.
				$templateMgr->assign('testMode', $this->isTestMode($request)?array('testMode' => 1):array());
				$templateMgr->assign('filter', $request->getUserVar('filter'));

				// Export without account.
				$username = $this->getSetting($journal->getId(), 'username');
				$templateMgr->assign('hasCredentials', !empty($username));

				switch ($op) {
					case 'issues':
						return $this->displayIssueList($templateMgr, $journal);
					case 'articles':
						return $this->displayArticleList($templateMgr, $journal);
					case 'galleys':
						return $this->_displayGalleyList($templateMgr, $journal);
					case 'suppFiles':
						return $this->displaySuppFileList($templateMgr, $journal);
					case 'all':
						return $this->displayAllUnregisteredObjects($templateMgr, $journal);
				}

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
	 * @param mixed $request PKPRequest
	 * @param mixed $journal Journal
     * @return void
	 */
	public function process($request, $journal) {
		$objectTypes = $this->getAllObjectTypes();
		$target = $request->getUserVar('target');
		$result = false;

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
					break;
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
						array('plugin', $this->getName(), $target.'s'),
						($this->isTestMode($request) ? ['testMode' => 1] : null)
					);
				}
				break;
		}

		// Redirect to the index page.
		if ($result !== true) {
			if (is_array($result)) {
				foreach($result as $error) {
					assert(is_array($error) && count($error) >= 1);
					$this->_sendNotification(
						$request,
						$error[0],
						NOTIFICATION_TYPE_ERROR,
						(isset($error[1]) ? $error[1] : null)
					);
				}
			}
			$path = ['plugin', $this->getName()];
			$request->redirect(null, null, null, $path);
		}
	}

	/**
	 * Execute the CLI command.
	 * @see ImportExportPlugin::executeCLI()
	 * @param string $scriptName
	 * @param array $args
	 * @return void
	 */
	public function executeCLI($scriptName, $args) {
		$result = [];
		$journal = null;
		$objectType = null;
		$xmlFile = null;

		// Add additional locale file.
		AppLocale::requireComponents([LOCALE_COMPONENT_APPLICATION_COMMON]);

		// Command.
		$command = strtolower_codesafe(array_shift($args) ?? '');
		if (!in_array($command, ['export', 'register'], true)) {
			$result = false;
		}

		if ($command === 'export' && is_array($result)) {
			// Output file.
			$xmlFile = array_shift($args);
			if (empty($xmlFile)) {
				$result = false;
			}
		}

		// Journal.
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

		// Object type.
		if (is_array($result) && empty($result)) {
			$objectTypeRaw = array_shift($args);
			
			if ($objectTypeRaw !== null) {
				$objectType = strtolower_codesafe($objectTypeRaw);

				// Accept both singular and plural forms.
				if (substr($objectType, -1) === 's') {
					$objectType = substr($objectType, 0, -1);
				}
				if ($objectType === 'suppfile') {
					$objectType = 'suppFile';
				}

				// Check whether the object type exists.
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
	 * 
	 * @param string $verb
	 * @param array $args
	 * @param string|null &$message
	 * @param array|null &$messageParams
	 * @param PKPRequest|null $request
	 * @return bool
	 */
	public function manage(string $verb, array $args, string &$message = null, &$messageParams = null, $request = null): bool {
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

		$configurationErrors = [];

		// 1) Cek missing DOI prefix
		$doiPrefix = null;
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		
		if (isset($pubIdPlugins['DOIPubIdPlugin'])) {
			$doiPrefix = $pubIdPlugins['DOIPubIdPlugin']->getSetting($journal->getId(), 'doiPrefix');
		}
		
		if (empty($doiPrefix)) {
			$configurationErrors[] = DOI_EXPORT_CONFIGERROR_DOIPREFIX;
		}

		// 2) Cek missing plug-in setting.
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
	public function getPluginId() {
		assert(false);
	}

	/**
	 * Return the class name of the plug-in's settings form.
	 * @return string
	 */
	public function getSettingsFormClassName() {
		assert(false);
	}

	/**
	 * Return the object types supported by this plug-in.
	 * @return array
	 */
	public function getAllObjectTypes() {
		return [
			'issue' => DOI_EXPORT_ISSUES,
			'article' => DOI_EXPORT_ARTICLES,
			'galley' => DOI_EXPORT_GALLEYS
		];
	}

	/**
	 * Display a list of issues for export.
	 * @param mixed $templateMgr TemplateManager
	 * @param mixed $journal Journal
	 */
	public function displayIssueList($templateMgr, $journal) {
		$this->setBreadcrumbs([], true);

		// Retrieve all published issues.
		AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);

		/** @var IssueDAO $issueDao */
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$this->registerDaoHook('IssueDAO');
		$issueIterator = $issueDao->getPublishedIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

		// Get issues that should be excluded i.e. that have no DOI.
		$excludes = [];
		$allExcluded = true;
		while ($issue = $issueIterator->next()) {
			$excludes[$issue->getId()] = true;
			$errors = array();
			if ($this->canBeExported($issue, $errors)) {
				$excludes[$issue->getId()] = false;
				$allExcluded = false;
			}
			unset($issue);
		}
		unset($issueIterator);

		// Prepare and display the issue template.
		$issueIterator = $issueDao->getPublishedIssues($journal->getId(), Handler::getRangeInfo('issues'));

		$templateMgr->assign('issues', $issueIterator);
		$templateMgr->assign('allExcluded', $allExcluded);
		$templateMgr->assign('excludes', $excludes);

		$templateMgr->display($this->getTemplatePath() . 'issues.tpl');
	}

	/**
	 * Display a list of all yet unregistered objects.
	 * @param mixed $templateMgr TemplateManager
	 * @param mixed $journal Journal
	 */
	public function displayAllUnregisteredObjects($templateMgr, $journal) {
		$this->setBreadcrumbs([], true);
		AppLocale::requireComponents([LOCALE_COMPONENT_CORE_SUBMISSION]);

		// Prepare and display the template.
		$templateMgr->assign('issues', $this->_getUnregisteredIssues($journal));
		$templateMgr->assign('articles', $this->_getUnregisteredArticles($journal));
		$templateMgr->assign('galleys', $this->_getUnregisteredGalleys($journal));

		$templateMgr->display($this->getTemplatePath() . 'all.tpl');
	}

	/**
	 * Display a list of supplementary files for export.
	 * @param $templateMgr TemplateManager
	 * @param $journal Journal
	 */
	public function displaySuppFileList($templateMgr, $journal) {
		fatalError('Not implemented for this plug-in');
	}

	/**
	 * Retrieve all published articles.
	 * @param mixed $journal Journal
	 * @return array
	 */
	public function getAllPublishedArticles($journal) {
		/** @var PublishedArticleDAO $publishedArticleDao */
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$articleIterator = $publishedArticleDao->getPublishedArticlesByJournalId($journal->getId());

		// Return articles from published issues only.
		$articles = [];
		while ($article = $articleIterator->next()) {
			// Retrieve issue
			$issue = $this->_getArticleIssue($article, $journal);

			// Check whether the issue is published.
			if ($issue->getPublished()) {
				$articles[] = $article;
				unset($article);
			}
		}
		unset($articleIterator);

		return $articles;
	}

	/**
	 * Identify published article and issue of the given article file.
	 * @param mixed $articleFile ArticleFile
	 * @param mixed $journal Journal
	 * @return array|null
	 */
	public function prepareArticleFileData($articleFile, $journal) {
		// Prepare and return article data for the article file.
		$articleData = $this->_prepareArticleDataByArticleId((int) $articleFile->getArticleId(), $journal);
		if (!is_array($articleData)) {
			$nullVar = null;

			return $nullVar;
		}

		// Add the article file to the cache.
		$cache = $this->getCache();
		$cache->add($articleFile, $articleData['article']);

		return $articleData;
	}

	/**
	 * Export publishing objects.
	 * @param mixed $request Request
	 * @param array $exportSpec array
	 * @param mixed $journal Journal
	 * @param string $outputFile string
	 * @return boolean|array
	 */
	public function exportObjects($request, $exportSpec, $journal, $outputFile = null) {
		// Initialize local variables.
		$errors = [];

		if (count($exportSpec) > 1) {
			if (is_array($errors = $this->_checkForTar())) return $errors;
		}

		// Get the target directory.
		$result = $this->_getExportPath();
		if (is_array($result)) return $result;
		$exportPath = $result;

		// Run through the export spec and generate the corresponding export files.
		$exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
		if ($exportFiles === false) {
			return $errors;
		}

		// Check whether we need the tar tool for this export if we've not checked this before.
		if (count($exportFiles) > 1 && !$this->_checkedForTar) {
			if (is_array($errors = $this->_checkForTar())) {
				$this->cleanTmpfiles($exportPath, array_keys($exportFiles));
				return $errors;
			}
		}

		// If we have more than one export file we package the files up as a single tar before going on.
		assert(count($exportFiles) >= 1);
		if (count($exportFiles) > 1) {
			$finalExportFileName = $exportPath . $this->getPluginId() . '-export.tar.gz';
			$finalExportFileType = DOI_EXPORT_FILE_TAR;
			$this->tarFiles($exportPath, $finalExportFileName, array_keys($exportFiles));
			$exportFiles[$finalExportFileName] = array();
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
	 * 
	 * @param PKPRequest $request
	 * @param array $exportSpec
	 * @param Journal $journal
	 * @return bool|array True on success, false on critical failure, or array of errors.
	 */
	public function registerObjects($request, array $exportSpec, $journal) {
		set_time_limit(0);

		// Get the target directory.
		$result = $this->_getExportPath();
		if (is_array($result)) {
			return $result;
		}
		$exportPath = $result;

		// Run through the export spec and generate the corresponding export files.
		$errors = [];
		$exportFiles = $this->_generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, $errors);
		
		if ($exportFiles === false) {
			return $errors;
		}

		$arrayResult = [];
		$falseResult = false; 

		// Register DOIs and their meta-data.
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

		// Remove all temporary files.
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
	 * Returns file name and file type of the target export
	 * file for the given export type and object ids.
	 * @param string $exportPath string
	 * @param int $exportType integer One of the DOI_EXPORT_* constants.
	 * @param int $objectId int
	 * @return string
	 */
	public function getTargetFileName($exportPath, $exportType, $objectId = null) {
		// Define the prefix of the exported files.
		$targetFileName = $exportPath . date('Ymd-Hi-') . $this->getObjectName($exportType);

		if (is_null($objectId)) {
			$targetFileName .= 's.xml';
		} else {
			$targetFileName .= '-' . $objectId . '.xml';
		}
		return $targetFileName;
	}

	/**
	 * Get a string representation of the object being exported by a given export type.
	 * @param int $exportType integer One of the DOI_EXPORT_* constants.
	 * @return string
	 */
	public function getObjectName($exportType) {
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
	 * @param mixed $foundObject Issue|PublishedArticle|ArticleGalley|SuppFile
	 * @param array $errors array
	 * @return array|boolean
	*/
	public function canBeExported($foundObject, &$errors) {
		if (($foundObject instanceof PublishedArticle) && (int) $foundObject->getStatus() === STATUS_ARCHIVED) {
			return false;
		}
		return !is_null($foundObject->getPubId('doi'));
	}

	/**
	 * Generate the export data model.
	 * @param mixed $request Request
	 * @param int $exportType integer
	 * @param array $objects array
	 * @param string $targetPath string
	 * @param mixed $journal Journal
	 * @param array $errors array
	 * @return array|boolean
	 */
	public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
		assert(false);
	}

	/**
	 * Process the marking of the selected objects as registered.
	 * @param mixed $request Request
	 * @param int $exportType integer
	 * @param array $objects array
	 * @param mixed $journal Journal
	 */
	public function processMarkRegistered($request, $exportType, $objects, $journal) {
		assert(false);
	}

	/**
	 * Create a tar archive.
	 * @param string $targetPath string
	 * @param string $targetFile string
	 * @param array $sourceFiles array
	 */
	public function tarFiles($targetPath, $targetFile, $sourceFiles) {
		assert($this->_checkedForTar);

		// GZip compressed result file.
		$tarCommand = Config::getVar('cli', 'tar') . ' -czf ' . escapeshellarg($targetFile);
		$tarCommand .= ' -C ' . escapeshellarg($targetPath);
		$tarCommand .= ' --owner 0 --group 0 --';

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
	 * @param mixed $request Request
	 * @param array $journal Journal
	 * @param array $objects array
	 * @param string $file string
	 */
	public function registerDoi($request, $journal, $objects, $file) {
		fatalError('Not implemented for this plug-in');
	}

	/**
	 * Check whether we are in test mode.
	 * @param mixed $request Request
	 * @return boolean
	 */
	public function isTestMode($request) {
		return ($request->getUserVar('testMode') == '1');
	}

	/**
	 * Mark an object as "registered" by saving it's DOI to the object's
	 * "registeredDoi" setting. We prefix the setting with the plug-in's
	 * id so that we do not get name clashes when several DOI registration plug-ins
	 * are active at the same time.
	 * @param array $request Request
	 * @param mixed $object Issue|PublishedArticle|ArticleGalley|SuppFile
	 * @param string $testPrefix string
	 */
	public function markRegistered($request, $object, $testPrefix = '10.1234') {
		$registeredDoi = $object->getPubId('doi');
		assert(!empty($registeredDoi));
		if ($this->isTestMode($request)) {
			$registeredDoi = PKPString::regexp_replace('#^[^/]+/#', $testPrefix . '/', $registeredDoi);
		}
		$this->saveRegisteredDoi($object, $registeredDoi);
	}

	/**
	 * Reset the given object.
	 * @param int $objectType int
	 * @param int $objectId integer
	 * @param mixed $journal Journal
	 * @return boolean|array
	 */
	public function resetRegistration($objectType, $objectId, $journal) {
		// Identify the object to be reset.
		$errors = [];
		$objects = $this->_getObjectsFromIds($objectType, $objectId, $journal->getId(), $errors);
		if ($objects === false || count($objects) != 1) {
			return $errors;
		}

		// Reset the object.
		$this->saveRegisteredDoi($objects[0], '');

		return true;
	}

	/**
	 * Set the object's "registeredDoi" setting.
	 * 
	 * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
	 * @param string $registeredDoi
	 * @return void
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
	 * Register the hook that adds an
	 * additional field name to objects.
	 * @param string $daoName string
	 */
	public function registerDaoHook($daoName) {
		HookRegistry::register(strtolower_codesafe($daoName) . '::getAdditionalFieldNames', [$this, 'getAdditionalFieldNames']);
	}

	/**
	 * Hook callback that returns the
	 * "registeredDoi" setting's name prefixed with
	 * the plug-in's id to avoid name collisions.
	 * @see DAO::getAdditionalFieldNames()
	 * @param string $hookName string
	 * @param array $args array
	 */
	public function getAdditionalFieldNames($hookName, $args) {
		assert(count($args) == 2);
		$dao = $args[0];
		$returner =& $args[1];
		assert(is_array($returner));
		$returner[] = $this->getPluginId() . '::' . DOI_EXPORT_REGDOI;
	}

	/**
	 * Remove the given temporary files.
	 * @param string $tempdir string
	 * @param array $tempfiles array
	 */
	public function cleanTmpfiles($tempdir, $tempfiles) {
		foreach ($tempfiles as $tempfile) {
			$tempfilePath = dirname($tempfile) . '/';
			assert($tempdir === $tempfilePath);
			if ($tempdir !== $tempfilePath) continue;
			unlink($tempfile);
		}
	}

	/**
	 * Identify DAO and DAO method to extract objects
	 * for a given export type.
	 * @param mixed $exportType One of the DOI_EXPORT_* constants
	 * @return array
	 */
	public function getDaoName($exportType) {
		$daoNames = [
			DOI_EXPORT_ISSUES => ['IssueDAO', 'getIssueById'],
			DOI_EXPORT_ARTICLES => ['PublishedArticleDAO', 'getPublishedArticleByArticleId'],
			DOI_EXPORT_GALLEYS => ['ArticleGalleyDAO', 'getGalley'],
		];
		assert(isset($daoNames[$exportType]));
		return $daoNames[$exportType];
	}

	/**
	 * Return a translation key for the "object not found" error message
	 * for a given export type.
	 * @param mixed $exportType One of the DOI_EXPORT_* constants
	 * @return string A translation key.
	 */
	public function getObjectNotFoundErrorKey($exportType) {
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
	 * @param mixed $templateMgr TemplateManager
	 * @param mixed $journal Journal
	 */
	private function _displayPluginHomePage($templateMgr, $journal) {
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
		foreach($form->getFormFields() as $fieldName => $fieldType) {
			if ($form->isOptional($fieldName)) continue;

			$setting = $this->getSetting($journal->getId(), $fieldName);
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
	 * @param mixed $templateMgr TemplateManager
	 * @param mixed $journal Journal
	 */
	public function displayArticleList($templateMgr, $journal) {
		$this->setBreadcrumbs(array(), true);

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
			// We should always get a prepared article as we've already
			// filtered non-published articles above.
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

    // Private helper methods
    
	/**
	 * Display a list of galleys for export.
	 * @param mixed $templateMgr TemplateManager
	 * @param mixed $journal Journal
	 */
	private function _displayGalleyList($templateMgr, $journal) {
		$this->setBreadcrumbs([], true);

		// Retrieve all published articles.
		$allArticles = $this->getAllPublishedArticles($journal);

		// Retrieve galley data.
		$this->registerDaoHook('ArticleGalleyDAO');
		$galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /** @var ArticleGalleyDAO $galleyDao */
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
			// As we select only published articles, we should always
			// get data back here.
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
	 * @param mixed $journal Journal
	 * @return array
	 */
	private function _getUnregisteredIssues($journal) {
		// Retrieve all issues that have not yet been registered.
		 /** @var IssueDAO $issueDao */
		$issueDao = DAORegistry::getDAO('IssueDAO');
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
	 * @param mixed $journal Journal
	 * @return array
	 */
	private function _getUnregisteredArticles($journal) {
		// Retrieve all published articles that have not yet been registered.
		/** @var PublishedArticleDAO $publishedArticleDao */
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
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
	 * @param mixed $journal Journal
	 * @return array
	 */
	private function _getUnregisteredGalleys($journal) {
		// Retrieve all galleys that have not yet been registered.
		/** @var ArticleGalleyDAO $galleyDao */
		$galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
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
	 * @param mixed $galley ArticleGalley
	 * @param mixed $journal Journal
	 * @return array|null
	 */
	private function _prepareGalleyData($galley, $journal) {
		// Retrieve article and issue for the galley.
		$galleyData = $this->prepareArticleFileData($galley, $journal);
		if (!is_array($galleyData)) {
			$nullVar = null;
			return $nullVar;
		}

		// Add the galley language.
		/** @var LanguageDAO $languageDao */
		$languageDao = DAORegistry::getDAO('LanguageDAO');
		$galleyData['language'] = $languageDao->getLanguageByCode(AppLocale::getIso1FromLocale($galley->getLocale()));

		// Add the galley itself.
		$galleyData['galley'] = $galley;

		return $galleyData;
	}

	/**
	 * Identify published article and issue for the given article id.
	 * @param int $articleId integer
	 * @param mixed $journal Journal
	 * @return array|null
	 */
	private function _prepareArticleDataByArticleId($articleId, $journal) {
		// Get the cache.
		$cache = $this->getCache();

		// Retrieve article if not yet cached.
		$article = null;
		if (!$cache->isCached('articles', (int) $articleId)) {
			$nullVar = null;

			/** @var PublishedArticleDAO $publishedArticleDao */
			$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
			$article = $publishedArticleDao->getPublishedArticleByArticleId($articleId, $journal->getId(), true);
			
			if (!($article instanceof PublishedArticle)) {
				// It seems that the article ID we got does not belong to a
				// published article. This may happen if we try to prepare
				// article data for a galley or supplementary file.
				return $nullVar;
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
	 * @param mixed $article PublishedArticle
	 * @param mixed $journal Journal
	 * @return array|null
	 */
	private function _prepareArticleData($article, $journal) {
		$nullVar = null;

		// Add the article to the cache.
		$cache = $this->getCache();
		$cache->add($article, $nullVar);

		// Retrieve the issue.
		$issue = $this->_getArticleIssue($article, $journal);

		if ($issue->getPublished()) {
			$articleData = [
				'article' => $article,
				'issue' => $issue
			];
			return $articleData;
		} else {
			return $nullVar;
		}
	}

	/**
	 * Return the issue of an article.
	 * The issue will be cached if it is not yet cached.
	 * @param mixed $article Article
	 * @param mixed $journal Journal
	 * @return Issue
	 */
	private function _getArticleIssue($article, $journal) {
		$issueId = (int) $article->getIssueId();

		// Retrieve issue if not yet cached.
		$cache = $this->getCache();
		if (!$cache->isCached('issues', $issueId)) {
			$issueDao = DAORegistry::getDAO('IssueDAO'); /** @var IssueDAO $issueDao */
			$issue = $issueDao->getIssueById($issueId, $journal->getId(), true);

			assert($issue instanceof Issue);
			$nullVar = null;
			$cache->add($issue, $nullVar);
			unset($issue);
		}

		return $cache->get('issues', $issueId);
	}

	/**
	 * Generate export files for the given export spec.
	 * @param mixed $request Request
	 * @param mixed $journal Journal
	 * @param array $exportSpec array
	 * @param string $exportPath string
	 * @param array $errors array
	 * @return array
	 */
	private function _generateExportFilesForObjects($request, $journal, $exportSpec, $exportPath, &$errors) {
		// Run through the export types and generate the corresponding
		// export files.
		$exportFiles = [];
		foreach($exportSpec as $exportType => $objectIds) {
			// Normalize the object id(s) into an array.
			if (is_scalar($objectIds)) $objectIds = [$objectIds];

			// Retrieve the object(s).
			$objects = $this->_getObjectsFromIds($exportType, $objectIds, $journal->getId(), $errors);
			if (empty($objects)) {
				$this->cleanTmpfiles($exportPath, $exportFiles);
				return false;
			}

			// Export the object(s) to a file.
			$newFiles = $this->generateExportFiles($request, $exportType, $objects, $exportPath, $journal, $errors);
			if ($newFiles === false) {
				$this->cleanTmpfiles($exportPath, $exportFiles);
				return false;
			}

			// Add the new files to the result array.
			$exportFiles = array_merge($exportFiles, $newFiles);
		}

		return $exportFiles;
	}

	/**
	 * Test whether the tar binary is available.
	 * @return boolean|array
	 */
	private function _checkForTar() {
		$tarBinary = Config::getVar('cli', 'tar');
		if (empty($tarBinary) || !is_executable($tarBinary)) {
			$result = [
				['manager.plugins.tarCommandNotFound']
			];
		} else {
			$result = true;
		}
		$this->_checkedForTar = true;
		return $result;
	}

	/**
	 * Return the plug-ins export directory.
	 * This will create the directory if it doesn't exist yet.
	 * @return string|array
	 */
	private function _getExportPath() {
		$exportPath = Config::getVar('files', 'files_dir') . '/' . $this->getPluginId();
		if (!file_exists($exportPath)) {
			$fileManager = new FileManager();
			$fileManager->mkdir($exportPath);
		}
		if (!is_writable($exportPath)) {
			$errors =[
				['plugins.importexport.common.export.error.outputFileNotWritable', $exportPath]
			];
			return $errors;
		}
		return realpath($exportPath) . '/';
	}

	/**
	 * Retrieve the objects corresponding to the given ids.
	 * @param int $exportType int
	 * @param mixed $objectIds integer|array
	 * @param int $journalId integer
	 * @param array $errors array
	 * @return array|boolean
	 */
	private function _getObjectsFromIds($exportType, $objectIds, $journalId, &$errors) {
		$falseVar = false;
		if (empty($objectIds)) return $falseVar;
		if (!is_array($objectIds)) $objectIds = [$objectIds];

		// Instantiate the correct DAO.
		list($daoName, $daoMethod) = $this->getDaoName($exportType);
		$dao = DAORegistry::getDAO($daoName);
		$daoMethod = [$dao, $daoMethod];

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
				return $falseVar;
			}

			// Add the objects to our result array.
			if (!is_array($foundObjects)) $foundObjects = [$foundObjects];
			foreach ($foundObjects as $foundObject) {
				if ($this->canBeExported($foundObject, $errors)) $objects[] = $foundObject;
				else return $falseVar;
				unset($foundObject);
			}
			unset($foundObjects);
		}

		return $objects;
	}

	/**
	 * Display execution errors (if any) and command-line usage information.
	 * @param string $scriptName string
	 * @param array $errors array
	 */
	private function _usage($scriptName, $errors = null) {
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
	 * @param mixed $journal Journal
	 * @return DOIExportSettingsForm
	 */
	private function _instantiateSettingsForm($journal) {
		$settingsFormClassName = $this->getSettingsFormClassName();

		$this->import('classes.form.' . $settingsFormClassName);
		$settingsForm = new $settingsFormClassName($this, $journal->getId());
		assert($settingsForm instanceof DOIExportSettingsForm);

		return $settingsForm;
	}

	/**
	 * Add a notification.
	 * @param mixed $request Request
	 * @param string $message string
	 * @param int $notificationType int
	 * @param string $param string
     * @return void
	 */
	private function _sendNotification($request, $message, $notificationType, $param = null) {
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

	/**
     * Hook callback to parse cron tab entries.
	 * @see AcronPlugin::parseCronTab()
     * @param string $hookName string
     * @param array $args array
     * @return boolean
	 */
	public function callbackParseCronTab($hookName, $args) {
		return false;
	}

}
?>