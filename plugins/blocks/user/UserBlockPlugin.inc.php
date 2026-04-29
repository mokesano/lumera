<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/user/UserBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserBlockPlugin
 * @ingroup plugins_blocks_user
 *
 * @brief Class for user block plugin
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class UserBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::UserBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Register plugin
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if ($success) {
            AppLocale::requireComponents(array(LOCALE_COMPONENT_CORE_USER));
        }
        return $success;
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
        return __('plugins.block.user.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        return __('plugins.block.user.description');
    }

    /**
     * Get the contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        if (!defined('SESSION_DISABLE_INIT')) {
            // [WIZDAM] Session Logic (Legacy Compatible)
            $session = Request::getSession();
            
            $templateMgr->assign('userSession', $session);
            $templateMgr->assign('loggedInUsername', $session->getSessionVar('username'));
            
            // Build Login URL
            $loginUrl = Request::url(null, 'login', 'signIn');
            
            $forceSSL = false;
            if (Config::getVar('security', 'force_login_ssl')) {
                if (Request::getProtocol() != 'https') {
                    // Jika belum HTTPS, redirect ke versi HTTPS
                    $loginUrl = Request::url(null, 'login');
                    $forceSSL = true;
                }
                // Paksa protokol https:// di string URL
                $loginUrl = PKPString::regexp_replace('/^http:/', 'https:', $loginUrl);
            }
            
            $templateMgr->assign('userBlockLoginSSL', $forceSSL);
            $templateMgr->assign('userBlockLoginUrl', $loginUrl);
        }
        
        // SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>