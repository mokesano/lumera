<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/languageToggle/LanguageToggleBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageToggleBlockPlugin
 * @ingroup plugins_blocks_languageToggle
 *
 * @brief Class for language selector block plugin
 * [WIZDAM EDITION] Modernized. Ready for Smart Locale Strategy.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class LanguageToggleBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LanguageToggleBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::LanguageToggleBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Determine whether the plugin is enabled.
     * @return bool
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
     * Get the block context.
     */
    public function getBlockContext() {
        if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_RIGHT_SIDEBAR;
        return parent::getBlockContext();
    }

    /**
     * Determine the plugin sequence.
     * @return int
     */
    public function getSeq(): int {
        if (!Config::getVar('general', 'installed')) return 2;
        return parent::getSeq();
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.block.languageToggle.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.block.languageToggle.description');
    }

    /**
     * Get the HTML contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // LOGIKA KUNO (Request::) KITA PERTAHANKAN
        $templateMgr->assign('isPostRequest', Request::isPost());
        
        $locales = array();

        if (!defined('SESSION_DISABLE_INIT')) {
            // [WIZDAM] Modern Request Handling
            $journal = ($request) ? $request->getJournal() : null;
            
            if (isset($journal)) {
                $locales = $journal->getSupportedLocaleNames();
            } else {
                // Site Level Context
                $site = Request::getSite();
                $locales = $site->getSupportedLocaleNames();
            }
        } else {
            $locales = AppLocale::getAllLocales();
            $templateMgr->assign('languageToggleNoUser', true);
        }

        if (isset($locales) && count($locales) > 1) {
            $templateMgr->assign('enableLanguageToggle', true);
            $templateMgr->assign('languageToggleLocales', $locales);
        }

        // [WIZDAM] Pastikan Base URL aman
        $baseUrl = Request::getBaseUrl();
        $templateMgr->addStyleSheet($baseUrl . '/' . $this->getPluginPath() . '/styles/languageToggle.css');

        // SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>