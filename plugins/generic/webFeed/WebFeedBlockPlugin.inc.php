<?php
declare(strict_types=1);

/**
 * @file plugins/generic/webFeed/WebFeedBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WebFeedBlockPlugin
 * @ingroup plugins_generic_webFeed
 *
 * @brief Class for block component of web feed plugin
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class WebFeedBlockPlugin extends BlockPlugin {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function WebFeedBlockPlugin() {
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
     * Hide this plugin from plugin management UI (it's subsidiary).
     * @return bool
     */
    public function getHideManagement(): bool {
        return true;
    }
    
    /**
     * Get the name of this plugin.
     * @return string
     */
    public function getName(): string {
        return 'WebFeedBlockPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.webfeed.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.webfeed.description');
    }

    /**
     * Supported contexts for this block.
     * @return array
     */
    public function getSupportedContexts() {
        return [BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR];
    }

    /**
     * Get parent WebFeed plugin.
     * @return mixed
     */
    public function getWebFeedPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        return $plugin;
    }

    /**
     * Override to use parent plugin path.
     * @return string
     */
    public function getPluginPath(): string {
        $plugin = $this->getWebFeedPlugin();
        return $plugin ? $plugin->getPluginPath() : '';
    }

    /**
     * Override to use parent plugin template path.
     * @return string
     */
    public function getTemplatePath(): string {
        $plugin = $this->getWebFeedPlugin();
        return $plugin ? $plugin->getTemplatePath() . 'templates/' : '';
    }

    /**
     * Get HTML block contents.
     * @param $templateMgr object
     * @param $request PKPRequest|null
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        if (!$request) {
            $request = Application::getRequest();
        }

        $journal = $request->getJournal();
        if (!$journal) {
            return '';
        }

        $plugin = $this->getWebFeedPlugin();
        if (!$plugin) {
            return '';
        }

        $displayPage = $plugin->getSetting($journal->getId(), 'displayPage');
        $requestedPage = $request->getRequestedPage();

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $currentIssue = $issueDao->getCurrentIssue($journal->getId(), true);

        if (
            $currentIssue &&
            (
                $displayPage === 'all' ||
                ($displayPage === 'homepage' &&
                    (empty($requestedPage) ||
                     $requestedPage === 'index' ||
                     $requestedPage === 'issue')) ||
                ($displayPage === 'issue' &&
                     $displayPage === $requestedPage)
            )
        ) {
            return parent::getContents($templateMgr, $request);
        }

        return '';
    }
}
?>