<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for site administration functions.
 */

import('classes.handler.Handler');

class AdminHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SITE_ADMIN]));
        $this->addCheck(new HandlerValidatorCustom(
            $this, 
            true, 
            null, 
            null, 
            function() {
                // Singleton access required inside closure if not passed as arg
                $request = Application::get()->getRequest();
                return $request->getRequestedJournalPath() == 'index';
            }
        ));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AdminHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AdminHandler(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display site admin index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();

        // Display a warning message if there is a new version available
        $newVersionAvailable = false;
        if (Config::getVar('general', 'show_upgrade_warning')) {
            import('lib.pkp.classes.site.VersionCheck');
            if ($latestVersion = VersionCheck::checkIfNewVersionExists()) {
                $newVersionAvailable = true;
                $templateMgr->assign('latestVersion', $latestVersion);
                $currentVersion = VersionCheck::getCurrentDBVersion();
                $templateMgr->assign('currentVersion', $currentVersion->getVersionString());
            }
        }

        $templateMgr->assign('newVersionAvailable', $newVersionAvailable);
        $templateMgr->assign('helpTopicId', 'site.index');

        $templateMgr->display('admin/index.tpl');
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate();
        
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_ADMIN, LOCALE_COMPONENT_APP_ADMIN, LOCALE_COMPONENT_APP_MANAGER);
        $templateMgr = TemplateManager::getManager();
        
        // [WIZDAM] Singleton Fallback
        $request = Application::get()->getRequest();
        
        $templateMgr->assign('pageHierarchy',
            $subclass ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'admin'), 'admin.siteAdmin']]
                : [[$request->url(null, 'user'), 'navigation.user']]
        );
    }
    
    /**
     * Tampilkan formulir 'About Site Settings' kustom.
     * @param array $args
     * @param PKPRequest $request
     */
    public function aboutSite($args, $request) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate(true);
        
        import('classes.admin.form.AboutSiteForm');
        $form = new AboutSiteForm();
        if (!$request->isPost()) {
            $form->initData();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('justSaved', (bool) $request->getUserVar('saved'));
        
        $form->display($request); // Hanya ini yang kita perlukan
    }

    /**
     * Simpan formulir 'About Site Settings' kustom.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveAboutSite($args, $request) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        import('classes.admin.form.AboutSiteForm');
        $form = new AboutSiteForm();
        
        // PERBAIKAN ALUR LOGIKA ---
        // 1. Muat struktur data (kunci) terlebih dahulu
        $form->initData(); 
        
        // 2. Baca data POST berdasarkan kunci yang sudah ada di _data
        $form->readInputData(); 
        // AKHIR PERBAIKAN ALUR ---

        // [BARU] Proses unggahan logo Penerbit SEBELUM validate(), supaya
        // kalau gagal (format file tidak valid) bisa ditolak sekaligus
        // lewat jalur error form biasa, bukan silent-fail terpisah.
        if (!$form->uploadPublisherLogo()) { // Undefined method 'uploadPublisherLogo'.
            $form->addError('publisherLogo', __('admin.siteSettings.error.logoUploadFailed'));
        }

        if ($form->validate()) {
            $form->execute();
            
            // PERBAIKAN NOTIFIKASI ---
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            
            // Parameter 1: Dapatkan ID pengguna dari request
            $user = $request->getUser();
            
            // Parameter 3: Buat array dengan kunci 'message'
            $params = ['contents' => __('common.changesSaved')];
            
            $notificationManager->createTrivialNotification(
                (int) $user->getId(),       // Param 1: int (User ID)
                NOTIFICATION_TYPE_SUCCESS,  // Param 2: Konstanta (Tipe Notifikasi)
                (array) $params             // Param 3: array (Pesan)
            );
            // AKHIR PERBAIKAN NOTIFIKASI ---

            // Redirect kembali ke halaman form
            $request->redirect(null, null, 'aboutSite', null, ['saved' => 1]);
            
        } else {
            // Validasi gagal (misal: jika CSRF nanti diaktifkan), 
            // tampilkan form kembali
            
            $this->setupTemplate(true); // Setup template dasar
            
            // Muat TinyMCE (Wajib ada di blok 'else') ---
            // Ini penting agar form tampil benar saat ada error validasi
            $templateMgr = TemplateManager::getManager($request);
            PluginRegistry::loadPlugin('generic', 'TinyMCEPlugin');
            $plugin = PluginRegistry::getPlugin('generic', 'TinyMCEPlugin');
            if ($plugin != null) { 
                $pluginPath = $plugin->getPluginPath();
                $templateMgr->addJavaScript($pluginPath . '/js/tiny_mce.js');
                $templateMgr->addJavaScript($pluginPath . '/js/tiny_mce_init.js');
            }
            // Akhir Muat TinyMCE ---
            
            $form->display($request); // Tampilkan form dengan pesan error
        }
    }
    
}
?>