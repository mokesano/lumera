<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/donation/DonationBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DonationBlockPlugin
 * @ingroup plugins_blocks_donation
 *
 * @brief Class for donation block plugin
 * [WIZDAM EDITION] Modernized PHP 8
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class DonationBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DonationBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::DonationBlockPlugin(). Please refactor to parent::__construct().", 
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
        return __('plugins.block.donation.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        // [FIX] Sebelumnya mengarah ke 'user.description' (Typo bawaan OJS)
        return __('plugins.block.donation.description');
    }

    /**
     * @see BlockPlugin::getContents
     */
    public function getContents($templateMgr, $request = null) {
        // [WIZDAM] GUNAKAN LOGIKA MODERN
        $journal = $request->getJournal(); 
        if (!$journal) return '';
        
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        
        // Cek apakah donasi aktif di Payment Settings
        $templateMgr->assign('donationEnabled', $paymentManager->donationEnabled());

        // [WIZDAM] SOLUSI BYPASS PARENT (WAJIB)
        // Mengambil filename template secara manual
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>