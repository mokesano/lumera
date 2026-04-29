<?php
declare(strict_types=1);

/**
 * @file classes/user/form/ChangePasswordForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChangePasswordForm
 * @ingroup user_form
 *
 * @brief Form to change a user's password.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (create_function removal, Constructor)
 * - Security Guard Clause (User Session)
 * - Strict Visibility
 */

import('lib.pkp.classes.form.Form');

class ChangePasswordForm extends Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('user/changePassword.tpl');
        
        $user = Request::getUser();
        
        // --- PERBAIKAN WIZDAM: Guard Clause ---
        if (!$user) {
            // Jika sesi pengguna habis, paksa login ulang
            Validation::redirectLogin();
            return; // Hentikan eksekusi konstruktor agar tidak crash di bawah
        }
        // --------------------------------------
        
        $site = Request::getSite();

        // Validation checks for this form
        
        // MOD: Replaced deprecated create_function with Closure
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'oldPassword', 
            'required', 
            'user.profile.form.oldPasswordInvalid', 
            function($password) use ($user) {
                return Validation::checkCredentials($user->getUsername(), $password);
            }
        ));

        $this->addCheck(new FormValidatorLength($this, 'password', 'required', 'user.register.form.passwordLengthTooShort', '>=', (int) $site->getMinPasswordLength()));
        $this->addCheck(new FormValidator($this, 'password', 'required', 'user.profile.form.newPasswordRequired'));

        // MOD: Replaced deprecated create_function with Closure
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'password', 
            'required', 
            'user.register.form.passwordsDoNotMatch', 
            function($password) {
                return $password == $this->getData('password2');
            }
        ));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ChangePasswordForm() {
        trigger_error(
            "Class " . get_class($this) . " uses deprecated constructor parent::ChangePasswordForm(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Display the form.
     * @param PKPRequest $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $user = Request::getUser();
        
        // PHP 8 Safety: Ensure user exists before rendering
        if (!$user) {
            Validation::redirectLogin();
            return;
        }

        $templateMgr = TemplateManager::getManager();
        $site = Request::getSite();
        
        $templateMgr->assign('minPasswordLength', $site->getMinPasswordLength());
        $templateMgr->assign('username', $user->getUsername());
        
        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('oldPassword', 'password', 'password2'));
    }

    /**
     * Save new password.
     * @param object|null $object
     */
    public function execute($object = null) {
        $user = Request::getUser();
        $auth = null;

        if ($user->getAuthId()) {
            $authDao = DAORegistry::getDAO('AuthSourceDAO');
            $auth = $authDao->getPlugin($user->getAuthId());
        }

        if (isset($auth)) {
            $auth->doSetUserPassword($user->getUsername(), $this->getData('password'));
            // Used for PW reset hash only
            $user->setPassword(Validation::encryptCredentials($user->getId(), Validation::generatePassword())); 
        } else {
            $user->setPassword(Validation::encryptCredentials($user->getUsername(), $this->getData('password')));
        }

        $userDao = DAORegistry::getDAO('UserDAO');
        $userDao->updateObject($user);
    }
}

?>