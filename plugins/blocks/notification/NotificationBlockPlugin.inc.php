<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/notification/NotificationBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationBlockPlugin
 * @ingroup plugins_blocks_notification
 *
 * @brief Class for "notification" block plugin
 * [WIZDAM EDITION] Modernized Syntax Only (Safe Mode)
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class NotificationBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::NotificationBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Determine whether the plugin is enabled.
     */
    public function getEnabled($request = null): bool {
        if (!Config::getVar('general', 'installed')) return true;
        return parent::getEnabled();
    }

    /**
     * Install default settings on system install.
     */
    public function getInstallSitePluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Install default settings on journal creation.
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     */
    public function getDisplayName(): string {
        return __('plugins.block.notification.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        return __('plugins.block.notification.description');
    }

    /**
     * Get the contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // [WIZDAM] LOGIKA ORIGINAL DIPERTAHANKAN
        // Kita tidak menambah query baru agar tidak bentrok dengan Navbar.
        
        $user = Request::getUser(); 
        $journal = $request->getJournal();

        if ($user && $journal) {
            $userId = $user->getId();
            // [MODERNISASI] Hapus referensi &
            $notificationDao = DAORegistry::getDAO('NotificationDAO');
            
            // Assign variabel persis seperti aslinya
            $templateMgr->assign(
                'unreadNotifications', 
                $notificationDao->getNotificationCount(false, $userId, $journal->getId())
            );
        }

        // SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}
?>