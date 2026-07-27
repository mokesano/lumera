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
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category
     * @param string $path
     * @return bool
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
     * @param PKPRequest $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager($request);
        parent::display($args, $request);

        // Load the DuraCloud-PHP library.
        require_once('lib/DuraCloud-PHP/DuraCloudPHP.inc.php');

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        
        // [WIZDAM FIX] Null safety: Redirect if no journal context exists
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }
        
        $command = array_shift($args);

        switch ($command) {
            case 'importIssue':
                $contentId = array_shift($args);
                $issue = $this->importIssue($user, $journal, (string) $contentId);
                $templateMgr->assign('results', [$contentId => $issue]);
                $templateMgr->display($this->getTemplatePath() . 'importResults.tpl');
                return;

            case 'importIssues':
                $results = $this->importIssues($user, $journal, (array) $request->getUserVar('contentId'));
                $templateMgr->assign('results', $results);
                $templateMgr->display($this->getTemplatePath() . 'importResults.tpl');
                return;

            case 'exportIssues':
                $issueIds = (array) $request->getUserVar('issueId');
                $issues = [];
                foreach ($issueIds as $issueId) {
                    $issue = $issueDao->getIssueById((int) $issueId, (int) $journal->getId());
                    if (!$issue) {
                        $request->redirect(null, null, 'index');
                        return;
                    }
                    $issues[$issue->getId()] = $issue;
                }
                $results = $this->exportIssues($journal, $issues);
                $templateMgr->assign('results', $results);
                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'exportResults.tpl');
                return;

            case 'exportIssue':
                $issueId = array_shift($args);
                $issue = $issueDao->getIssueById((int) $issueId, (int) $journal->getId());
                if (!$issue) {
                    $request->redirect(null, null, 'index');
                    return;
                }
                $results = [$issue->getId() => $this->exportIssue($journal, $issue)];
                $templateMgr->assign('results', $results);
                $templateMgr->assign('issues', [$issue->getId() => $issue]);
                $templateMgr->display($this->getTemplatePath() . 'exportResults.tpl');
                return;

            case 'exportableIssues':
                // Display a list of issues for export
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
                $issues = $issueDao->getIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'exportableIssues.tpl');
                return;

            case 'importableIssues':
                // Display a list of issues for import
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
                $templateMgr->assign('issues', $this->getImportableIssues());
                $templateMgr->display($this->getTemplatePath() . 'importableIssues.tpl');
                return;

            case 'signIn':
                $this->setBreadcrumbs();
                $this->import('DuraCloudLoginForm');
                $duraCloudLoginForm = new DuraCloudLoginForm($this);
                $duraCloudLoginForm->readInputData();
                if ($duraCloudLoginForm->validate()) {
                    $duraCloudLoginForm->execute();
                }
                $duraCloudLoginForm->display($request);
                return;

            case 'signOut':
                $this->forgetDuraCloudConfiguration();
                break;

            case 'selectSpace':
                $this->setDuraCloudSpace((string) $request->getUserVar('duracloudSpace'));
                break;
        }

        // If we fall through: display the form.
        $this->setBreadcrumbs();
        $this->import('DuraCloudLoginForm');
        $duraCloudLoginForm = new DuraCloudLoginForm($this);
        $duraCloudLoginForm->display($request);
    }

    /**
     * Get the native import/export plugin.
     * @return NativeImportExportPlugin|null
     */
    public function getNativeImportExportPlugin() {
        // [WIZDAM FIX] Type narrowing: Beritahu VS Code bahwa ini adalah NativeImportExportPlugin
        /** @var NativeImportExportPlugin|null $plugin */
        $plugin = PluginRegistry::getPlugin('importexport', 'NativeImportExportPlugin');
        return $plugin;
    }

    /**
     * Store an issue in DuraCloud.
     * @param Journal $journal 
     * @param Issue $issue 
     * @return string|false
     */
    public function exportIssue($journal, $issue) {
        // [WIZDAM FIX] Type narrowing untuk menghilangkan warning Undefined method
        /** @var NativeImportExportPlugin $nativeImportExportPlugin */
        $nativeImportExportPlugin = $this->getNativeImportExportPlugin();
        
        if (!$nativeImportExportPlugin) {
            return false;
        }
        
        $filename = tempnam(sys_get_temp_dir(), 'dcissue');
        if (!$filename) {
            return false;
        }
        
        $nativeImportExportPlugin->exportIssue($journal, $issue, $filename);

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
        $location = false;
        if ($fp) {
            $content->setResource($fp);
            $location = $ds->storeContent($this->getDuraCloudSpace(), 'issue-' . $issue->getId(), $content);
            fclose($fp);
        }

        unlink($filename);
        return $location;
    }

    /**
     * Store several issues in DuraCloud.
     * @param Journal $journal
     * @param array $issues
     * @return array
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
     * @param User $user
     * @param Journal $journal
     * @param string $contentId
     * @return object|false
     */
    public function importIssue($user, $journal, $contentId) {
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $content = $ds->getContent($this->getDuraCloudSpace(), $contentId);
        if (!$content) {
            return false;
        }

        $fp = $content->getResource();
        if ($fp) {
            fseek($fp, 0);
        }

        // [WIZDAM FIX] Type narrowing untuk menghilangkan warning Undefined method
        /** @var NativeImportExportPlugin $nativeImportExportPlugin */
        $nativeImportExportPlugin = $this->getNativeImportExportPlugin();
        
        if (!$nativeImportExportPlugin) {
            return false;
        }

        $doc = $nativeImportExportPlugin->getDocument($fp);

        $nativeImportExportPlugin->import('NativeImportDom');
        $dependentItems = [];
        $errors = [];
        $issue = null;
        
        if (!NativeImportDom::importIssue($journal, $doc, $issue, $errors, $user, false, $dependentItems)) {
            return false;
        }

        return $issue;
    }

    /**
     * Import issues from DuraCloud.
     * @param User $user
     * @param Journal $journal
     * @param array $contentIds
     * @return array
     */
    public function importIssues($user, $journal, array $contentIds): array {
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $result = [];
        $errors = [];
        
        // [WIZDAM FIX] Type narrowing untuk menghilangkan warning Undefined method
        /** @var NativeImportExportPlugin $nativeImportExportPlugin */
        $nativeImportExportPlugin = $this->getNativeImportExportPlugin();

        if (!$nativeImportExportPlugin) {
            return $result;
        }

        foreach ($contentIds as $contentId) {
            $content = $ds->getContent($this->getDuraCloudSpace(), (string) $contentId);
            if (!$content) {
                $result[$contentId] = false;
                continue;
            }

            $fp = $content->getResource();
            if ($fp) {
                fseek($fp, 0);
            }

            $doc = $nativeImportExportPlugin->getDocument($fp);

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
     * @param array $args
     */
    public function executeCLI($scriptName, $args) {
        $baseUrl = array_shift($args);
        $username = array_shift($args);
        $password = array_shift($args);

        require_once('lib/DuraCloud-PHP/DuraCloudPHP.inc.php');

        $journalPath = array_shift($args);
        $spaceId = array_shift($args);
        $command = array_shift($args);

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $journalDao->getJournalByPath($journalPath);

        if (!$journal) {
            if ($journalPath !== '') {
                echo __('plugins.importexport.duracloud.cliError') . "\n";
                echo __('plugins.importexport.duracloud.error.unknownJournal', ['journalPath' => $journalPath]) . "\n";
                return;
            }
            $this->usage($scriptName);
            return;
        }

        $this->storeDuraCloudConfiguration($baseUrl, $username, $password);
        $this->setDuraCloudSpace($spaceId);
        
        $dcc = $this->getDuraCloudConnection();
        $ds = new DuraStore($dcc);
        $metadata = null;
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
                    if ($userName !== '') {
                        echo __('plugins.importexport.duracloud.cliError') . "\n";
                        echo __('plugins.importexport.duracloud.error.unknownUser', ['userName' => $userName]) . "\n\n";
                    }
                    $this->usage($scriptName);
                    return;
                }

                $results = $this->importIssues($user, $journal, $args);
                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                foreach ($results as $id => $result) {
                    $issueIden = $result ? $result->getIssueIdentification() : '';
                    echo "    $id: $issueIden\n";
                }
                return;

            case 'exportIssues':
                $issues = [];
                foreach ($args as $issueId) {
                    $issue = $issueDao->getIssueById((int) $issueId, (int) $journal->getId());
                    if ($issue) {
                        $issues[$issue->getId()] = $issue;
                    }
                }
                
                // [WIZDAM] $this merujuk ke DuraCloudImportExportPlugin yang memiliki method exportIssues
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
    public function storeDuraCloudConfiguration(?string $url, ?string $username, ?string $password): void {
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
    public function setDuraCloudSpace(string $space): void {
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
     * @return array
     */
    public function getImportableIssues(): array {
        $dcc = $this->getDuraCloudConnection();
        $duraStore = new DuraStore($dcc);
        $spaceId = $this->getDuraCloudSpace();
        
        $metadata = null;
        $contents = $duraStore->getSpace($spaceId, $metadata, null, 'issue-');
        
        if (!$contents) {
            return [];
        }

        $returner = [];
        foreach ($contents as $contentId) {
            $content = $duraStore->getContent($spaceId, $contentId);
            if (!$content) continue;

            $descriptor = $content->getDescriptor();
            if (!$descriptor) continue;

            $metadata = $descriptor->getMetadata();
            if (!$metadata) continue;

            if (!isset($metadata['creator']) || $metadata['creator'] != $this->getName()) continue;

            if (!isset($metadata['identification'])) continue;

            $returner[$contentId] = $metadata;
        }

        return $returner;
    }

}
?>