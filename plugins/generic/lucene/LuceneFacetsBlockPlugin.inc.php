<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/LuceneFacetsBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LuceneFacetsBlockPlugin
 * @ingroup plugins_generic_lucene
 *
 * @brief Lucene plugin, faceting block component
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class LuceneFacetsBlockPlugin extends BlockPlugin {

    /** @var string */
    protected $_parentPluginName;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        $this->_parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LuceneFacetsBlockPlugin($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::LuceneFacetsBlockPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Implement template methods from PKPPlugin.
    //
    /**
     * Manage the pluginn's installation and upgrade process.
     * @return boolean True on success.
     * @see PKPPlugin::getHideManagement()
     */
    public function getHideManagement(): bool {
        return true;
    }

    /**
     * Get the plugin name.
     * @see PKPPlugin::getName()
     */
    public function getName(): string {
        return 'LuceneFacetsBlockPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see PKPPlugin::getDisplayName()
     */
    public function getDisplayName(): string {
        return __('plugins.generic.lucene.faceting.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see PKPPlugin::getDescription()
     */
    public function getDescription(): string {
        return __('plugins.generic.lucene.faceting.description');
    }

    /**
     * Get the path to this plugin.
     * @see PKPPlugin::getPluginPath()
     */
    public function getPluginPath(): string {
        $plugin = $this->_getLucenePlugin();
        return $plugin->getPluginPath();
    }

    /**
     * Get the path to this plugin's templates.
     * @see PKPPlugin::getTemplatePath()
     */
    public function getTemplatePath(): string {
        $plugin = $this->_getLucenePlugin();
        return $plugin->getTemplatePath();
    }

    /**
     * Get the sequence of this plugin.
     * @see PKPPlugin::getSeq()
     */
    public function getSeq(): int {
        // Identify the position of the faceting block.
        $seq = parent::getSeq();

        // If nothing has been configured then use the first
        // position. This is ok as we'll only display facets
        // in a search results context where they have a high
        // relevance by default.
        if (!is_numeric($seq)) $seq = 0;

        return $seq;
    }


    //
    // Implement template methods from LazyLoadPlugin
    //
    /**
     * Get the enabled status of this plugin.
     * @see LazyLoadPlugin::getEnabled()
     */
    public function getEnabled() {
        $plugin = $this->_getLucenePlugin();
        return $plugin->getEnabled();
    }


    //
    // Implement template methods from BlockPlugin
    //
    /**
     * Get the block context.
     * @see BlockPlugin::getBlockContext()
     */
    public function getBlockContext() {
        $blockContext = parent::getBlockContext();

        // Place the block on the left by default
        // where navigation will usually be expected
        // by the user.
        if (!in_array($blockContext, $this->getSupportedContexts())) {
            $blockContext = BLOCK_CONTEXT_LEFT_SIDEBAR;
        }

        return $blockContext;
    }

    /**
     * Get the block's supported contexts.
     * @see BlockPlugin::getBlockTemplateFilename()
     */
    public function getBlockTemplateFilename() {
        // Return the facets template.
        return 'facetsBlock.tpl';
    }

    /**
     * Get the block's contents.
     * @see BlockPlugin::getContents()
     */
    public function getContents($templateMgr, $request = null) {
        // Get facets from the parent plug-in.
        $plugin = $this->_getLucenePlugin();
        $facets = $plugin->getFacets();

        // Check whether we got any facets to display.
        $hasFacets = false;
        if (is_array($facets)) {
            foreach($facets as $facetCategory => $facetList) {
                if (count($facetList) > 0) {
                    $hasFacets = true;
                    break;
                }
            }
        }

        // Do not display the block if we got no facets.
        if (!$hasFacets) return '';

        $templateMgr->assign('facets', $facets);
        return parent::getContents($templateMgr, $request);
    }


    //
    // Private helper methods
    //
    /**
     * Get the lucene plugin object
     * @return LucenePlugin
     */
    protected function _getLucenePlugin() {
        $plugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        return $plugin;
    }
}

?>