<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/sample/SampleImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SampleImportExportPlugin
 * @ingroup plugins_importexport_sample
 *
 * @brief Sample import/export plugin
 */

import('classes.plugins.ImportExportPlugin');

class SampleImportExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SampleImportExportPlugin() {
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
     * @return bool True iff plugin initialized successfully; if false,
     * the plugin will not be registered.
     */
    public function register($category, $path): bool {
        $success = parent::register($category, $path);
        // Additional registration / initialization code
        // should go here. For example, load additional locale data:
        $this->addLocaleData();

        // This is fixed to return false so that this coding sample
        // isn't actually registered and displayed. If you're using
        // this sample for your own code, make sure you return true
        // if everything is successfully initialized.
        // return $success;
        return false;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        // This should not be used as this is an abstract class
        return 'SampleImportExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.sample.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.sample.description');
    }

    /**
     * Display the plugin.
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request): void {
        parent::display($args, $request);
        
        $command = array_shift($args);

        switch ($command) {
            case 'exportIssue':
                // The actual issue export code would go here
                break;
            default:
                // Display a list of issues for export
                $journal = $request->getJournal();
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issues = $issueDao->getIssues($journal->getId(), Handler::getRangeInfo('issues'));

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
        }
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, $args): void {
        $this->usage($scriptName);
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     */
    public function usage($scriptName): void {
        echo "USAGE NOT AVAILABLE.\n"
            . "This is a sample plugin and does not actually perform a function.\n";
    }
}

?>