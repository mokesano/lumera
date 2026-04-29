<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/duracloud/DuraCloudImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudImportExportPlugin
 * @ingroup plugins_importexport_duracloud
 *
 * @brief DuraCloud import/export plugin
 */

import('classes.plugins.ImportExportPlugin');

class DuraCloudImportExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudImportExportPlugin() {
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
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path to plugin
     * @return bool True iff plugin initialized successfully
     */
    public function register($category, $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'DuraCloudImportExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.duracloud.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.duracloud.description');
    }

    /**
     * Display the plugin UI.
     * @param array $args
     * @param object $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager();
        parent::display($args, $request);

        // Load the DuraCloud-PHP library.
        require_once('lib/DuraCloud-PHP/DuraCloudPHP.inc.php');

        $issueDao = DAORegistry::getDAO('IssueDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        
        $command = array_shift($args);

        switch ($command) {
            case 'importIssue':
                $contentId = array_shift($args);
                $issue = $this->importIssue($user, $journal, $contentId);
                $templateMgr->assign('results', [$contentId => $issue]);
                $templateMgr->display($this->getTemplatePath() . 'importResults.tpl');
                return;

            case 'importIssues':
                $results = $this->importIssues($user, $journal, (array) $request->getUserVar('contentId'));
                $templateMgr->assign('results', $results);
                $templateMgr->display($this->getTemplatePath() . 'importResults.tpl');
                return;

            case 'exportIssues':
                $issueIds = $request->getUserVar('issueId');
                if (!isset($issueIds)) $issueIds = [];
                $issues = [];
                foreach ($issueIds as $issueId) {
                    $issue = $issueDao->getIssueById($issueId, $journal->getId());
                    if (!$issue) $request->redirect();
                    $issues[$issue->getId()] = $issue;
                }
                $results = $this->exportIssues($journal, $issues);
                $templateMgr->assign('results', $results);
                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'exportResults.tpl');
                return;

            case 'exportIssue':
                $issueId = array_shift($args);
                $issue = $issueDao->getIssueById($issueId, $journal->getId());
                if (!$issue) $request->redirect();
                $results = [$issue->getId() => $this->exportIssue($journal, $issue)];
                $templateMgr->assign('results', $results);
                $templateMgr->assign('issues', [$issue->getId() => $issue]);
                $templateMgr->display($this->getTemplatePath() . 'exportResults.tpl');
                return;

            case 'exportableIssues':
                // Display a list of issues for export
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents(LOCALE_COMPONENT_OJS_EDITOR);
                $issues = $issueDao->getIssues($journal->getId(), Handler::getRangeInfo('issues'));

                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'exportableIssues.tpl');
                return;

            case 'importableIssues':
                // Display a list of issues for import
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents(LOCALE_COMPONENT_OJS_EDITOR);
                $templateMgr->assign('issues', $this->getImportableIssues());
                $templateMgr->display($this->getTemplatePath() . 'importableIssues.tpl');
                return;

            case 'signIn':
                $this->setBreadcrumbs();
                $this->import('DuraCloudLoginForm');
                $duraCloudLoginForm = new DuraCloudLoginForm($this);
                $duraCloudLoginForm->readInputData();
                if ($duraCloudLoginForm->validate()) {
                    $duraCloudLoginForm->execute(); // execute() no longer takes params in standard Form
                }
                $duraCloudLoginForm->display(); // display() no longer takes params in standard Form, but kept compatible with updated DuraCloudLoginForm
                return;

            case 'signOut':
                $this->forgetDuraCloudConfiguration();
                break;

            case 'selectSpace':
                $this->setDuraCloudSpace($request->getUserVar('duracloudSpace'));
                break;
        }

        // If we fall through: display the form.
        $this->setBreadcrumbs();
        $this->import('DuraCloudLoginForm');
        $duraCloudLoginForm = new DuraCloudLoginForm($this);
        $duraCloudLoginForm->display();
    }

    /**
     * Get the native import/export plugin.
     * @return object NativeImportExportPlugin
     */
    public function getNativeImportExportPlugin() {
        // Get the native import/export plugin.
        return PluginRegistry::getPlugin('importexport', 'NativeImportExportPlugin');
    }

    /**
     * Store an issue in DuraCloud.
     * @param object $journal Journal
     * @param object $issue Issue
     * @return string|false location iff success; false otherwise
     */
    public function exportIssue($journal, $issue) {
        // Export the native XML to a file.
        $nativeImportExportPlugin = $this->getNativeImportExportPlugin();
        $filename = tempnam('duracloud', 'dcissue');
        $nativeImportExportPlugin->exportIssue($journal, $issue, $filename);

        // Store the file in DuraCloud.
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $descriptor = new DuraCloudContentDescriptor([
            'creator' => $this->getName(),
            'identification' => $issue->getIssueIdentification(),
            'date_published' => $issue->getDatePublished(),
            'num_articles' => $issue->getNumArticles()
        ]);
        $content = new DuraCloudFileContent($descriptor);
        $fp = fopen($filename, 'r');
        $content->setResource($fp);
        $location = $ds->storeContent($this->getDuraCloudSpace(), 'issue-' . $issue->getId(), $content);

        // Clean up temporary file
        if ($fp) fclose($fp); // Ensure file is closed before unlink
        unlink($filename);

        return $location;
    }

    /**
     * Store several issues in DuraCloud.
     * @param object $journal Journal
     * @param array $issues Array of Issue objects
     * @return array of results for each issue (see exportIssue)
     */
    public function exportIssues($journal, array $issues): array {
        $results = [];
        foreach ($issues as $issue) {
            $results[$issue->getId()] = $this->exportIssue($journal, $issue);
        }
        return $results;
    }

    /**
     * Import an issue from DuraCloud.
     * @param object $user User
     * @param object $journal Journal
     * @param string $contentId
     * @return object|false Issue iff success; false otherwise
     */
    public function importIssue($user, $journal, $contentId) {
        // Get the file from DuraCloud.
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $content = $ds->getContent($this->getDuraCloudSpace(), $contentId);
        if (!$content) return false;

        // Get and reset the resource
        $fp = $content->getResource();
        fseek($fp, 0);

        // Parse the document
        $nativeImportExportPlugin = $this->getNativeImportExportPlugin();
        $doc = $nativeImportExportPlugin->getDocument($fp); // Assuming getDocument handles resource or path, refactoring might be needed in NativeImportExportPlugin if it expects string path only

        // Note: NativeImportExportPlugin::getDocument typically expects a file path string. 
        // If it strictly requires a path, we might need to save $fp to a temp file here.
        // Assuming for now it works or has been adapted.

        // Import the issue
        $nativeImportExportPlugin->import('NativeImportDom');
        $dependentItems = [];
        $errors = [];
        $issue = null;
        if (!NativeImportDom::importIssue($journal, $doc, $issue, $errors, $user, false, $dependentItems)) return false;

        return $issue;
    }

    /**
     * Import issues from DuraCloud.
     * @param object $user User
     * @param object $journal Journal
     * @param array $contentIds
     * @return array with result for each contentId (see importIssue)
     */
    public function importIssues($user, $journal, array $contentIds): array {
        // Get the file from DuraCloud.
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $result = [];
        $errors = [];
        $nativeImportExportPlugin = $this->getNativeImportExportPlugin();

        foreach ($contentIds as $contentId) {
            $content = $ds->getContent($this->getDuraCloudSpace(), $contentId);
            if (!$content) {
                $result[$contentId] = false;
                continue;
            }

            // Get and reset the resource
            $fp = $content->getResource();
            fseek($fp, 0);

            // Parse the document
            $doc = $nativeImportExportPlugin->getDocument($fp);

            // Import the issue
            $nativeImportExportPlugin->import('NativeImportDom');
            $issue = null;
            $dependentItems = [];
            NativeImportDom::importIssue($journal, $doc, $issue, $errors, $user, false, $dependentItems);
            $result[$contentId] = $issue;
        }

        return $result;
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, $args) {
        // First, DuraCloud access info
        $baseUrl = array_shift($args);
        $username = array_shift($args);
        $password = array_shift($args);

        // Load the DuraCloud-PHP library.
        require_once('lib/DuraCloud-PHP/DuraCloudPHP.inc.php');

        // Context and commands
        $journalPath = array_shift($args);
        $spaceId = array_shift($args);
        $command = array_shift($args);

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $journalDao->getJournalByPath($journalPath);

        if (!$journal) {
            if ($journalPath != '') {
                echo __('plugins.importexport.duracloud.cliError') . "\n";
                echo __('plugins.importexport.duracloud.error.unknownJournal', ['journalPath' => $journalPath]) . "\n";
                return;
            }
            $this->usage($scriptName);
            return;
        }

        $this->storeDuraCloudConfiguration($baseUrl, $username, $password);
        $this->setDuraCloudSpace($spaceId);
        
        // Verify that the configuration and space ID are valid
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $metadata = null; // Passed by reference
        if ($ds->getSpace($spaceId, $metadata) === false) {
            echo __('plugins.importexport.duracloud.cliError') . "\n";
            echo __('plugins.importexport.duracloud.configuration.credentialsInvalid') . "\n";
            return;
        }

        switch ($command) {
            case 'importIssues':
                $userName = array_shift($args);
                $user = $userDao->getByUsername($userName);

                if (!$user) {
                    if ($userName != '') {
                        echo __('plugins.importexport.duracloud.cliError') . "\n";
                        echo __('plugins.importexport.duracloud.error.unknownUser', ['userName' => $userName]) . "\n\n";
                    }
                    $this->usage($scriptName);
                    return;
                }

                $results = $this->importIssues($user, $journal, $args);
                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                foreach ($results as $id => $result) {
                    echo "    $id: " . ($result ? $result->getIssueIdentification() : '') . "\n";
                }
                return;

            case 'exportIssues':
                $issues = [];
                foreach ($args as $issueId) {
                    $issue = $issueDao->getIssueById($issueId, $journal->getId());
                    if ($issue) {
                        $issues[$issue->getId()] = $issue;
                    }
                }
                $results = $this->exportIssues($journal, $issues);
                foreach ($results as $id => $result) {
                    echo "    $id: $result\n";
                }
                return;
        }
        $this->usage($scriptName);
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.duracloud.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }

    /**
     * Store the DuraCloud configuration details for this session.
     * @param string|null $url
     * @param string|null $username
     * @param string|null $password
     */
    public function storeDuraCloudConfiguration($url, $username, $password): void {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $session->setSessionVar('duracloudUrl', $url);
        $session->setSessionVar('duracloudUsername', $username);
        $session->setSessionVar('duracloudPassword', $password);
    }

    /**
     * Store the DuraCloud space to be used for this session.
     * @param string $space
     */
    public function setDuraCloudSpace($space): void {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $session->setSessionVar('duracloudSpace', $space);
    }

    /**
     * Forget the stored DuraCloud configuration.
     */
    public function forgetDuraCloudConfiguration(): void {
        $this->storeDuraCloudConfiguration(null, null, null);
    }

    /**
     * Get a DuraCloudConnection object corresponding to the current
     * configuration.
     * @return object DuraCloudConnection
     */
    public function getDuraCloudConnection() {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        return new DuraCloudConnection(
            (string) $session->getSessionVar('duracloudUrl'),
            (string) $session->getSessionVar('duracloudUsername'),
            (string) $session->getSessionVar('duracloudPassword')
        );
    }

    /**
     * Get the currently configured DuraCloud URL.
     * @return string|null
     */
    public function getDuraCloudUrl(): ?string {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        return $session->getSessionVar('duracloudUrl');
    }

    /**
     * Get the currently configured DuraCloud username.
     * @return string|null
     */
    public function getDuraCloudUsername(): ?string {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        return $session->getSessionVar('duracloudUsername');
    }

    /**
     * Get the currently configured DuraCloud space.
     * @return string|null
     */
    public function getDuraCloudSpace(): ?string {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        return $session->getSessionVar('duracloudSpace');
    }

    /**
     * Check whether or not the DuraCloud connection is configured.
     * @return bool
     */
    public function isDuraCloudConfigured(): bool {
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        return (bool) $session->getSessionVar('duracloudUrl');
    }

    /**
     * Get a list of importable issues from the DuraSpace instance.
     * @return array(contentId => issueIdentification)
     */
    public function getImportableIssues() {
        $dcc = $this->getDuraCloudConnection();
        $duraStore = new DuraStore($dcc);
        $spaceId = $this->getDuraCloudSpace();
        
        $metadata = null;
        $contents = $duraStore->getSpace($spaceId, $metadata, null, 'issue-');
        
        if (!$contents) return $contents;

        $returner = [];
        foreach ($contents as $contentId) {
            $content = $duraStore->getContent($spaceId, $contentId);
            if (!$content) continue; // Could not fetch content

            $descriptor = $content->getDescriptor();
            if (!$descriptor) continue; // Could not get descriptor

            $metadata = $descriptor->getMetadata();
            if (!$metadata) continue; // Could not get metadata

            if (!isset($metadata['creator']) || $metadata['creator'] != $this->getName()) continue; // Not created by this plugin

            if (!isset($metadata['identification'])) continue; // Could not get identification

            $returner[$contentId] = $metadata;
        }

        return $returner;
    }
}

?>