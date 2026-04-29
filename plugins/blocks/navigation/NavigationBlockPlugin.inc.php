<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/navigation/NavigationBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationBlockPlugin
 * @ingroup plugins_blocks_navigation
 *
 * @brief Class for navigation block plugin
 * [WIZDAM EDITION] Modernized. Transitioning to Publisher-Centric Nav.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class NavigationBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NavigationBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::NavigationBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Install default settings on system install.
     * @return string
     */
    public function getInstallSitePluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Install default settings on journal creation.
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.block.navigation.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        return __('plugins.block.navigation.description');
    }

    /**
     * Get the contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        $journal = $request->getJournal();

        // Opsi pencarian standar OJS
        $templateMgr->assign('articleSearchByOptions', array(
            'query' => 'search.allFields',
            'authors' => 'search.author',
            'title' => 'article.title',
            'abstract' => 'search.abstract',
            'indexTerms' => 'search.indexTerms',
            'galleyFullText' => 'search.fullText'
        ));
        
        // [WIZDAM] SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>