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
 * @brief Class for "notification" block plugin.
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
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
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
        $user = Request::getUser(); 
        $journal = $request->getJournal();

        // [WIZDAM BUGFIX -- ARSITEKTUR] SEBELUMNYA hitungan ini
        // disaring ke jurnal yang SEDANG DIBUKA ($journal->getId()
        // diteruskan sebagai $contextId) -- bug arsitektur yang sama
        // dengan yang diperbaiki di NotificationHandler::index()
        // (lihat dokblok di sana untuk penjelasan lengkap). Notifikasi
        // melekat pada PENGGUNA, bukan pada jurnal tempat aksinya
        // terjadi -- badge navbar dan halaman /notification HARUS
        // menunjukkan angka yang SAMA, terlepas jurnal mana yang
        // sedang dibuka. $journal tetap dipertahankan untuk syarat
        // "user && journal" (blok ini memang cuma tampil di halaman
        // ber-jurnal), tapi TIDAK LAGI dipakai sebagai filter context.
        if ($user && $journal) {
            $userId = $user->getId();
            /** @var NotificationDAO $notificationDao */
            $notificationDao = DAORegistry::getDAO('NotificationDAO');
            
            // [WIZDAM BUGFIX] Urutan parameter SEBELUMNYA tertukar --
            // sama persis dengan bug di NotificationHandler::index()
            // (lihat dokblok di sana). "false" (dimaksudkan $read) justru
            // terikat ke posisi $userId, membuat query selalu nol baris.
            // Inilah kenapa badge navbar tidak pernah muncul sama sekali,
            // terlepas dari data notifikasi yang sebenarnya ada.
            $templateMgr->assign(
                'unreadNotifications', 
                $notificationDao->getNotificationCount($userId, null, NOTIFICATION_LEVEL_NORMAL, false)
            );
        }

        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }

}
?>