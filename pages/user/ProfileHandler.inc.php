<?php
declare(strict_types=1);

/**
 * @file pages/user/ProfileHandler.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class ProfileHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for modifying user profiles.
 *
 * [WIZDAM EDITION] 
 * Refactored for PHP 8.1+ Strict Compliance
 * Refactored for Semantic RESTful URLs (update-profile, my-profile).
 */

import('pages.user.UserHandler');

class ProfileHandler extends UserHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ProfileHandler() {
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
     * Display the private profile for the currently logged-in user.
     * [WIZDAM ARCHITECTURE] New dedicated semantic method for viewing own profile.
     * URL: /user/my-profile
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function myProfile($args = [], $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        // Wajib login
        $this->validate();
        $this->setupTemplate($request, true);

        $user = $request->getUser();
        $templateMgr = TemplateManager::getManager();
        
        $templateMgr->assign('user', $user);
        
        // Anda bisa membuat file myProfile.tpl khusus nanti yang berisi data 
        // lebih lengkap/privat, atau sementara meminjam publicProfile.tpl
        $templateMgr->display('user/myProfile.tpl'); 
    }
    
    /**
     * Display form to edit user's profile.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function updateProfile($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        // Cegat POST request dan arahkan ke domain logic penyimpanan
        if ($request->isPost()) {
            $this->saveProfile($args, $request);
            return;
        }

        $this->validate();
        $this->setupTemplate($request, true);

        import('classes.user.form.ProfileForm');

        $profileForm = new ProfileForm();
        if ($profileForm->isLocaleResubmit()) {
            $profileForm->readInputData();
        } else {
            $profileForm->initData($args, $request);
        }
        $profileForm->display();
    }

    /**
     * Validate and save changes to user's profile.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function saveProfile($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);
        $dataModified = false;

        import('classes.user.form.ProfileForm');

        $profileForm = new ProfileForm();
        $profileForm->readInputData();

        if ((int) $request->getUserVar('uploadProfileImage')) {
            if (!$profileForm->uploadProfileImage()) {
                $profileForm->addError('profileImage', __('user.profile.form.profileImageInvalid'));
            }
            $dataModified = true;
        } elseif ((int) $request->getUserVar('deleteProfileImage')) {
            $profileForm->deleteProfileImage();
            $dataModified = true;
        }

        if (!$dataModified && $profileForm->validate()) {
            $profileForm->execute();
            $request->redirect(null, $request->getRequestedPage());

        } else {
            $profileForm->display();
        }
    }

    /**
     * View the public user profile for a user, specified by user ID,
     * if that user should be exposed for public view.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function publicProfile($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate(false); // Tidak wajib login
        
        $userId = (int) array_shift($args);
        $accountIsVisible = false;

        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');
        if ($commentDao->attributedCommentsExistForUser($userId)) {
            $accountIsVisible = true;
        }
        
        // Jika tidak berhak, tendang ke index
        if(!$accountIsVisible) {
             $request->redirect(null, 'index');
             return;
        }

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getById($userId);
        
        if (!$user) {
            $request->redirect(null, 'index');
            return;
        }

        $this->setupTemplate($request, false);
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('user', $user);
        $templateMgr->display('user/publicProfile.tpl');
    }

    /**
     * Display form to change user's password.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function changePassword($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        import('classes.user.form.ChangePasswordForm');

        $passwordForm = new ChangePasswordForm();
        $passwordForm->initData();
        $passwordForm->display();
    }

    /**
     * Save user's new password.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function savePassword($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        import('classes.user.form.ChangePasswordForm');

        $passwordForm = new ChangePasswordForm();
        $passwordForm->readInputData();

        $this->setupTemplate($request, true);
        if ($passwordForm->validate()) {
            $passwordForm->execute();
            $request->redirect(null, $request->getRequestedPage());

        } else {
            $passwordForm->display();
        }
    }

    /**
     * Menampilkan halaman khusus Linked Accounts
     * URL Akses: /user/linked-accounts
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function linkedAccounts($args, $request) {
        // Validasi akses: Pastikan pengguna sudah login
        $this->validate();
        $this->setupTemplate($request, true);

        $user = $request->getUser();
        $templateMgr = TemplateManager::getManager($request);

        /** @var UserSettingsDAO $userSettingsDao */
        $userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
        $googleId = $userSettingsDao->getSetting($user->getId(), 'google_id');
        $googleEmail = $userSettingsDao->getSetting($user->getId(), 'google_email');
        $orcid = $user->getData('orcid');

        // Lempar status tautan ke template
        $templateMgr->assign('isGoogleLinked', !empty($googleId));
        $templateMgr->assign('googleEmail', $googleEmail);
        
        // Lempar ID/URL spesifik jika dibutuhkan untuk ditampilkan (Opsional)
        $templateMgr->assign('isOrcidLinked', !empty($orcid));
        $templateMgr->assign('orcidUrl', $orcid);

        // Lempar status fitur SSO dari config.inc.php
        $templateMgr->assign('googleSsoEnabled', Config::getVar('sso', 'google'));
        $templateMgr->assign('orcidSsoEnabled', Config::getVar('sso', 'orcid'));

        // Tampilkan file template antarmuka
        $templateMgr->display('user/linkedAccounts.tpl');
    }
    
}
?>