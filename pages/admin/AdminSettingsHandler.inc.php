<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminSettingsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminSettingsHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for changing site admin settings.
 */

import('pages.admin.AdminHandler');

class AdminSettingsHandler extends AdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AdminSettingsHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * [WIZDAM] Kirim notifikasi (flash message) ke user setelah sebuah aksi.
     * @param PKPRequest $request
     * @param string|null $localeKey
     */
    private function _notifyAction($request, $localeKey = null): void {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = $request->getUser();
        if (!$user) {
            return;
        }

        if ($localeKey) {
            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_SUCCESS,
                ['contents' => __($localeKey)]
            );
        } else {
            $notificationManager->createTrivialNotification($user->getId());
        }
    }

    /**
     * Display form to modify site settings.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function settings($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.admin.form.SiteSettingsForm');

        $settingsForm = new SiteSettingsForm();
        if ($settingsForm->isLocaleResubmit()) {
            $settingsForm->readInputData();
        } else {
            $settingsForm->initData();
        }
        $settingsForm->display($request);
    }

    /**
     * Validate and save changes to site settings.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function saveSettings($args, $request) {
        $this->validate();
        $this->setupTemplate(true);
        
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $site = $request->getSite();

        import('classes.admin.form.SiteSettingsForm');
        import('classes.file.PublicFileManager');

        $settingsForm = new SiteSettingsForm();
        $settingsForm->readInputData();

        if ($request->getUserVar('uploadSiteStyleSheet')) {
            if (!$settingsForm->uploadSiteStyleSheet()) {
                $settingsForm->addError('siteStyleSheet', __('admin.settings.siteStyleSheetInvalid'));
            } else {
                $this->_notifyAction($request, 'admin.settings.notification.fileUploaded');
            }
        } elseif ($request->getUserVar('deleteSiteStyleSheet')) {
            $publicFileManager = new PublicFileManager();
            $publicFileManager->removeSiteFile($site->getSiteStyleFilename());
            $this->_notifyAction($request, 'admin.settings.notification.fileRemoved');
        } elseif ($request->getUserVar('uploadPageHeaderTitleImage')) {
            if (!$settingsForm->uploadPageHeaderTitleImage($settingsForm->getFormLocale())) {
                $settingsForm->addError('pageHeaderTitleImage', __('admin.settings.homeHeaderImageInvalid'));
            } else {
                $this->_notifyAction($request, 'admin.settings.notification.fileUploaded');
            }
        } elseif ($request->getUserVar('deletePageHeaderTitleImage')) {
            $publicFileManager = new PublicFileManager();
            $setting = $site->getSetting('pageHeaderTitleImage');

            if (!is_array($setting)) {
                $setting = [];
            }
            
            $formLocale = $settingsForm->getFormLocale();
            if (isset($setting[$formLocale])) {
                if (!empty($setting[$formLocale]['uploadName'])) {
                    $publicFileManager->removeSiteFile($setting[$formLocale]['uploadName']);
                }
                unset($setting[$formLocale]);
                $site->updateSetting('pageHeaderTitleImage', $setting, 'object', true);

                // Refresh site header
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign('displayPageHeaderTitle', $site->getLocalizedPageHeaderTitle());

                $this->_notifyAction($request, 'admin.settings.notification.fileRemoved');
            }
        } elseif ($settingsForm->validate()) {
            $settingsForm->execute();
            $this->_notifyAction($request);
            $request->redirect(null, null, 'index');
        }
        
        $settingsForm->display($request);
    }
    
}
?>