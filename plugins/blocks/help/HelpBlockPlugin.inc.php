<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/help/HelpBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpBlockPlugin
 * @ingroup plugins_blocks_help
 *
 * @brief Class for help block plugin
 * [WIZDAM EDITION] Modernized. Future Home of Wizdam Chat Assistant.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class HelpBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Determine whether the plugin is enabled. Overrides parent so that
     * the plugin will be displayed during install.
     */
    public function getEnabled($request = null): bool {
        if (!Config::getVar('general', 'installed')) return true;
        return parent::getEnabled();
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
     * Get the block context. Overrides parent so that the plugin will be
     * displayed during install.
     * @return int
     */
    public function getBlockContext() {
        if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_RIGHT_SIDEBAR;
        return parent::getBlockContext();
    }

    /**
     * Determine the plugin sequence. Overrides parent so that
     * the plugin will be displayed during install.
     */
    public function getSeq(): int {
        if (!Config::getVar('general', 'installed')) return 0;
        return parent::getSeq();
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.block.help.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String
     */
    public function getDescription(): string {
        return __('plugins.block.help.description');
    }

    /**
     * Get the HTML contents for this block.
     * [WIZDAM] Added explicit method to facilitate future ChatBox injection
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // [WIZDAM] Future Logic: 
        // Di sini kita bisa inject variabel untuk ChatBot, misal:
        // $currentPage = Request::getRequestedPage();
        // $templateMgr->assign('wizdamCurrentContext', $currentPage);

        // SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>