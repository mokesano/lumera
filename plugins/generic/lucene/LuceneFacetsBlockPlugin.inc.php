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
 * @brief Lucene plugin, faceting block component.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class LuceneFacetsBlockPlugin extends BlockPlugin {

    /** @var string The name of the parent plugin */
    protected $_parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        $this->_parentPluginName = (string) $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function LuceneFacetsBlockPlugin($parentPluginName) {
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
    // Implement template methods from PKPPlugin.
    //

    /**
     * Manage the plugin's installation and upgrade process.
     * @return bool True on success.
     * @see PKPPlugin::getHideManagement()
     */
    public function getHideManagement(): bool {
        return true;
    }

    /**
     * Get the plugin name.
     * @return string
     * @see PKPPlugin::getName()
     */
    public function getName(): string {
        return 'LuceneFacetsBlockPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     * @see PKPPlugin::getDisplayName()
     */
    public function getDisplayName(): string {
        return __('plugins.generic.lucene.faceting.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     * @see PKPPlugin::getDescription()
     */
    public function getDescription(): string {
        return __('plugins.generic.lucene.faceting.description');
    }

    /**
     * Get the path to this plugin.
     * @return string
     * @see PKPPlugin::getPluginPath()
     */
    public function getPluginPath(): string {
        $plugin = $this->_getLucenePlugin();
        return $plugin !== null ? $plugin->getPluginPath() : '';
    }

    /**
     * Get the path to this plugin's templates.
     * @return string
     * @see PKPPlugin::getTemplatePath()
     */
    public function getTemplatePath(): string {
        $plugin = $this->_getLucenePlugin();
        return $plugin !== null ? $plugin->getTemplatePath() : '';
    }

    /**
     * Get the sequence of this plugin.
     * @return int
     * @see PKPPlugin::getSeq()
     */
    public function getSeq(): int {
        $seq = parent::getSeq();
        if (!is_numeric($seq)) {
            $seq = 0;
        }

        return (int) $seq;
    }

    //
    // Implement template methods from LazyLoadPlugin
    //

    /**
     * Get the enabled status of this plugin.
     * @param mixed $request
     * @return bool
     * @see LazyLoadPlugin::getEnabled()
     */
    public function getEnabled($request = null): bool {
        $plugin = $this->_getLucenePlugin();
        return $plugin !== null ? $plugin->getEnabled() : false;
    }

    //
    // Implement template methods from BlockPlugin
    //

    /**
     * Get the block context.
     * @return string
     * @see BlockPlugin::getBlockContext()
     */
    public function getBlockContext() {
        $blockContext = parent::getBlockContext();
        if (!is_string($blockContext) || !in_array($blockContext, $this->getSupportedContexts(), true)) {
            $blockContext = BLOCK_CONTEXT_LEFT_SIDEBAR;
        }

        return $blockContext;
    }

    /**
     * Get the block's supported contexts.
     * @param mixed $request
     * @return string
     * @see BlockPlugin::getBlockTemplateFilename()
     */
    public function getBlockTemplateFilename($request = null) {
        // Return the facets template.
        return 'facetsBlock.tpl';
    }

    /**
     * Get the block's contents.
     * @param Smarty $templateMgr
     * @param mixed $request
     * @return string
     * @see BlockPlugin::getContents()
     */
    public function getContents($templateMgr, $request = null) {
        // Get facets from the parent plug-in.
        $plugin = $this->_getLucenePlugin();
        if ($plugin === null) {
            return '';
        }
        
        $facets = $plugin->getFacets();
        $hasFacets = false;
        if (is_array($facets)) {
            foreach ($facets as $facetCategory => $facetList) {
                if (is_array($facetList) && count($facetList) > 0) {
                    $hasFacets = true;
                    break;
                }
            }
        }

        if (!$hasFacets) {
            return '';
        }

        $templateMgr->assign('facets', $facets);
        return parent::getContents($templateMgr, $request);
    }

    //
    // Private helper methods
    //

    /**
     * Get the lucene plugin object.
     * @return LucenePlugin|null
     */
    protected function _getLucenePlugin() {
        $plugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        return $plugin;
    }

}
?>