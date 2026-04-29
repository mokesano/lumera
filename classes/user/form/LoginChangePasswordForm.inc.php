<?php
declare(strict_types=1);

/**
 * @file classes/user/form/LoginChangePasswordForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LoginChangePasswordForm
 * @ingroup user_form
 *
 * @brief Form to change a user's password in order to login.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (create_function removal -> Closures)
 * - Constructor Standardization
 * - Visibility Explicit
 */

import('lib.pkp.classes.form.Form');

class LoginChangePasswordForm extends Form {

    /** @var string|null */
    public $_confirmHash = null;

    /**
     * Constructor.
     * @param string|null $confirmHash
     */
    public function __construct($confirmHash = null) {
        parent::__construct('user/loginChangePassword.tpl');
        
        $site = Request::getSite();
        $this->_confirmHash = $confirmHash;

        // Validation checks for this form
        if (!$confirmHash) {
            // MOD: Replaced deprecated create_function with Closure
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'oldPassword', 
                'required', 
                'user.profile.form.oldPasswordInvalid', 
                function($password) {
                    return Validation::checkCredentials($this->getData('username'), $password);
                }
            ));
        } else {
            $userDao = DAORegistry::getDAO('UserDAO');
            // MOD: Replaced deprecated create_function with Closure (using 'use' to capture $userDao)
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'confirmHash', 
                'required', 
                'user.profile.form.hashInvalid',
                function($confirmHash) use ($userDao) {
                    $user = $userDao->getByUsername($this->getData('username'));
                    return $user && Validation::verifyPasswordResetHash($user->getId(), $confirmHash);
                }
            ));
        }

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
    public function LoginChangePasswordForm($confirmHash = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class " . get_class($this) . " uses deprecated constructor parent::LoginChangePasswordForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        self::__construct($confirmHash);
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $site = Request::getSite();
        
        if ($this->_confirmHash) {
            $templateMgr->assign('confirmHash', $this->_confirmHash);
        }
        
        $templateMgr->assign('minPasswordLength', $site->getMinPasswordLength());
        
        parent::display($request, $template);
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('username', 'oldPassword', 'password', 'password2', 'confirmHash'));
    }

    /**
     * Save new password.
     * @return boolean success
     */
    public function execute($object = null) {
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getByUsername($this->getData('username'), false);
        $auth = null;

        if ($user != null) {
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

            $user->setMustChangePassword(0);
            $userDao->updateObject($user);
            return true;

        } else {
            return false;
        }
    }
}

?>