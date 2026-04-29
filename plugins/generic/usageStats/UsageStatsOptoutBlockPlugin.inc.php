<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/UsageStatsOptoutBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsOptoutBlockPlugin
 * @ingroup plugins_generic_usageStats
 *
 * @brief Opt-out component.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class UsageStatsOptoutBlockPlugin extends BlockPlugin {

    /** @var string */
    public $_parentPluginName;


    /**
     * Constructor
     * @param $parentPluginName string
     */
    public function __construct($parentPluginName) {
        $this->_parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UsageStatsOptoutBlockPlugin($parentPluginName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UsageStatsOptoutBlockPlugin(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($parentPluginName);
    }

    //
    // Implement template methods from PKPPlugin.
    //
    /**
     * Get whether or not management functions should be hidden
     * @see PKPPlugin::getHideManagement()
     * @return boolean
     */
    public function getHideManagement(): bool {
        return true;
    }

    /**
     * Get the name of this plugin
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'UsageStatsOptoutBlockPlugin';
    }

    /**
     * Get the display name of this plugin
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.usageStats.optout.displayName');
    }

    /**
     * Get the description of this plugin
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.usageStats.optout.description');
    }

    /**
     * Indicate that this is not a site-wide plugin
     * @see PKPPlugin::isSitePlugin()
     * @return boolean
     */
    public function isSitePlugin(): bool {
        return false;
    }

    /**
     * Get the plugin path
     * @see PKPPlugin::getPluginPath()
     * @return string
     */
    public function getPluginPath(): string {
        // Removed & reference
        $plugin = $this->_getPlugin();
        return $plugin->getPluginPath();
    }

    /**
     * Get the template path
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath(): string {
        // Removed & reference
        $plugin = $this->_getPlugin();
        return $plugin->getTemplatePath();
    }

    /**
     * Get the sequence of this plugin
     * @see PKPPlugin::getSeq()
     * @return integer
     */
    public function getSeq(): int {
        // Identify the position of the faceting block.
        $seq = parent::getSeq();

        // If nothing has been configured then show the privacy
        // block after all other blocks in the context.
        if (!is_numeric($seq)) $seq = 99;

        return $seq;
    }


    //
    // Implement template methods from LazyLoadPlugin
    //
    /**
     * Get whether or not this plugin is enabled
     * @see LazyLoadPlugin::getEnabled()
     * @return boolean
     */
    public function getEnabled(): bool {
        // Removed & reference
        $plugin = $this->_getPlugin();
        return $plugin->getEnabled();
    }


    //
    // Implement template methods from BlockPlugin
    //
    /**
     * Get the block context
     * @see BlockPlugin::getBlockContext()
     * @return integer
     */
    public function getBlockContext() {
        $blockContext = parent::getBlockContext();

        // Place the block on the right by default.
        if (!in_array($blockContext, $this->getSupportedContexts())) {
            $blockContext = BLOCK_CONTEXT_RIGHT_SIDEBAR;
        }

        return $blockContext;
    }

    /**
     * Get the block template filename
     * @see BlockPlugin::getBlockTemplateFilename()
     * @return string
     */
    public function getBlockTemplateFilename() {
        // Return the opt-out template.
        return 'optoutBlock.tpl';
    }

    /**
     * Get the contents of the block
     * @see BlockPlugin::getContents()
     * @param $templateMgr TemplateManager
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // Removed & from $templateMgr
        $router = $request->getRouter(); /* @var $router PageRouter */
        $privacyInfoUrl = $router->url($request, null, 'usageStats', 'privacyInformation');
        $templateMgr->assign('privacyInfoUrl', $privacyInfoUrl);
        return parent::getContents($templateMgr, $request);
    }


    //
    // Private helper methods
    //
    /**
     * Get the plugin object
     * @return UsageStatsPlugin
     */
    protected function _getPlugin() {
        // Removed & reference from return and assignment
        $plugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        return $plugin;
    }
}

?>