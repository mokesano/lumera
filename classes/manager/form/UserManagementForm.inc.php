<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/UserManagementForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserManagementForm
 * @ingroup manager_form
 *
 * @brief Form for journal managers to edit user profiles.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class UserManagementForm extends Form {

    /** @var int|null The ID of the user being edited */
    public $userId;

    /**
     * Constructor.
     * @param int|null $userId
     */
    public function __construct($userId = null) {
        parent::__construct('manager/people/userProfileForm.tpl');

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        // [WIZDAM] Strict check for admin access
        if ($userId && !Validation::canAdminister($journal->getId(), (int) $userId)) {
            $userId = null;
        }
        
        $this->userId = isset($userId) ? (int) $userId : null;
        $site = $request->getSite();

        // Validation checks for this form
        if ($userId == null) {
            $this->addCheck(new FormValidator($this, 'username', 'required', 'user.profile.form.usernameRequired'));
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'username', 
                'required', 
                'user.register.form.usernameExists', 
                [DAORegistry::getDAO('UserDAO'), 'userExistsByUsername'], 
                [$this->userId, true], 
                true
            ));
            $this->addCheck(new FormValidatorAlphaNum($this, 'username', 'required', 'user.register.form.usernameAlphaNumeric'));

            $implicitAuth = Config::getVar('security', 'implicit_auth');
            if (!$implicitAuth || strtolower($implicitAuth) === IMPLICIT_AUTH_OPTIONAL) {
                $this->addCheck(new FormValidator($this, 'password', 'required', 'user.profile.form.passwordRequired'));
                $this->addCheck(new FormValidatorLength($this, 'password', 'required', 'user.register.form.passwordLengthTooShort', '>=', (int) $site->getMinPasswordLength()));
                
                // [WIZDAM FIX] Replaced create_function with Closure
                $this->addCheck(new FormValidatorCustom(
                    $this, 
                    'password', 
                    'required', 
                    'user.register.form.passwordsDoNotMatch', 
                    function($password, $form) { 
                        return $password == $form->getData('password2'); 
                    }, 
                    [$this]
                ));
            }
        } else {
            $this->addCheck(new FormValidatorLength($this, 'password', 'optional', 'user.register.form.passwordLengthTooShort', '>=', (int) $site->getMinPasswordLength()));
            // [WIZDAM FIX] Replaced create_function with Closure
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'password', 
                'optional', 
                'user.register.form.passwordsDoNotMatch', 
                function($password, $form) { 
                    return $password == $form->getData('password2'); 
                }, 
                [$this]
            ));
        }
        
        $this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
        $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'email', 
            'required', 
            'user.register.form.emailExists', 
            [DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'], 
            [$this->userId, true], 
            true
        ));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserManagementForm($userId = null) {
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
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $userDao = DAORegistry::getDAO('UserDAO');
        $templateMgr = TemplateManager::getManager();
        $requestObj = Application::get()->getRequest();
        $site = $requestObj->getSite();

        $templateMgr->assign('genderOptions', $userDao->getGenderOptions());
        $templateMgr->assign('minPasswordLength', $site->getMinPasswordLength());
        $templateMgr->assign('source', $requestObj->getUserVar('source'));
        $templateMgr->assign('userId', $this->userId);
        
        if (isset($this->userId)) {
            $user = $userDao->getById($this->userId);
            $templateMgr->assign('username', $user->getUsername());
            $helpTopicId = 'journal.users.index';
        } else {
            $helpTopicId = 'journal.users.createNewUser';
        }

        $journal = $requestObj->getJournal();
        $journalId = $journal == null ? 0 : $journal->getId();
        
        import('pages.manager.PeopleHandler');
        $rolePrefs = PeopleHandler::retrieveRoleAssignmentPreferences($journalId);
        $activeRoles = [
            '' => 'manager.people.doNotEnroll',
            'manager' => 'user.role.manager',
            'editor' => 'user.role.editor',
            'sectionEditor' => 'user.role.sectionEditor',
        ];
        
        foreach($rolePrefs as $roleKey => $use) {
            if($use) {
                switch($roleKey) {
                    case 'useLayoutEditors': $activeRoles['layoutEditor'] = 'user.role.layoutEditor'; break;
                    case 'useCopyeditors': $activeRoles['copyeditor'] = 'user.role.copyeditor'; break;
                    case 'useProofreaders': $activeRoles['proofreader'] = 'user.role.proofreader'; break;
                }
            }
        }
        
        $activeRoles = array_merge($activeRoles, [
            'reviewer' => 'user.role.reviewer',
            'author' => 'user.role.author',
            'reader' => 'user.role.reader',
            'subscriptionManager' => 'user.role.subscriptionManager'
        ]);

        if (Validation::isJournalManager()) {
            $templateMgr->assign('roleOptions', $activeRoles);
        } else {
            // Subscription Manager
            $templateMgr->assign('roleOptions', [
                '' => 'manager.people.doNotEnroll',
                'reader' => 'user.role.reader'
            ]);
        }

        // Send implicitAuth setting down to template
        $templateMgr->assign('implicitAuth', strtolower((string) Config::getVar('security', 'implicit_auth')));
        $templateMgr->assign('availableLocales', $site->getSupportedLocaleNames());
        $templateMgr->assign('helpTopicId', $helpTopicId);

        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('countries', $countries);

        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $authSources = $authDao->getSources();
        $authSourceOptions = [];
        foreach ($authSources->toArray() as $auth) {
            $authSourceOptions[$auth->getAuthId()] = $auth->getTitle();
        }
        if (!empty($authSourceOptions)) {
            $templateMgr->assign('authSourceOptions', $authSourceOptions);
        }
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current user profile.
     */
    public function initData() {
        if (isset($this->userId)) {
            $userDao = DAORegistry::getDAO('UserDAO');
            $user = $userDao->getById($this->userId);

            import('lib.pkp.classes.user.InterestManager');
            $interestManager = new InterestManager();

            if ($user != null) {
                $this->_data = [
                    'authId' => $user->getAuthId(),
                    'username' => $user->getUsername(),
                    'salutation' => $user->getSalutation(),
                    'firstName' => $user->getFirstName(),
                    'middleName' => $user->getMiddleName(),
                    'lastName' => $user->getLastName(),
                    'signature' => $user->getSignature(null), // Localized
                    'initials' => $user->getInitials(),
                    'gender' => $user->getGender(),
                    'affiliation' => $user->getAffiliation(null), // Localized
                    'email' => $user->getEmail(),
                    'orcid' => $user->getData('orcid'),
                    'userUrl' => $user->getUrl(),
                    'phone' => $user->getPhone(),
                    'fax' => $user->getFax(),
                    'mailingAddress' => $user->getMailingAddress(),
                    'country' => $user->getCountry(),
                    'biography' => $user->getBiography(null), // Localized
                    'interestsKeywords' => $interestManager->getInterestsForUser($user),
                    'interestsTextOnly' => $interestManager->getInterestsString($user),
                    'gossip' => $user->getGossip(null), // Localized
                    'userLocales' => $user->getLocales()
                ];

            } else {
                $this->userId = null;
            }
        }
        
        if (!isset($this->userId)) {
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleId = Application::get()->getRequest()->getUserVar('roleId');
            $roleSymbolic = $roleDao->getRolePath($roleId);

            $this->_data = [
                'enrollAs' => [$roleSymbolic],
                'generatePassword' => 1,
                'sendNotify' => 1,
                'mustChangePassword' => 1
            ];
        }
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'authId',
            'enrollAs',
            'password',
            'password2',
            'salutation',
            'firstName',
            'middleName',
            'lastName',
            'gender',
            'initials',
            'signature',
            'affiliation',
            'email',
            'orcid',
            'userUrl',
            'phone',
            'fax',
            'mailingAddress',
            'country',
            'biography',
            'interestsTextOnly',
            'keywords',
            'gossip',
            'userLocales',
            'generatePassword',
            'sendNotify',
            'mustChangePassword'
        ]);
        
        if ($this->userId == null) {
            $this->readUserVars(['username']);
        }

        if ($this->getData('userLocales') == null || !is_array($this->getData('userLocales'))) {
            $this->setData('userLocales', []);
        }

        if ($this->getData('username') != null) {
            // Usernames must be lowercase
            $this->setData('username', strtolower($this->getData('username')));
        }

        $keywords = $this->getData('keywords');
        if ($keywords != null && is_array($keywords['interests'])) {
            // The interests are coming in encoded -- Decode them for DB storage
            $this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
        }
    }

    /**
     * Get locale field names
     * @return array
     */
    public function getLocaleFieldNames() {
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getLocaleFieldNames();
    }

    /**
     * Get additional field names
     * @return array
     */
    public function getAdditionalFieldNames() {
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getAdditionalFieldNames();
    }

    /**
     * Register a new user.
     * @param mixed $object
     */
    public function execute($object = null) {
        $userDao = DAORegistry::getDAO('UserDAO');
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        if (isset($this->userId)) {
            $user = $userDao->getById($this->userId);
        }

        if (!isset($user)) {
            $user = new User();
        }

        $user->setSalutation($this->getData('salutation'));
        $user->setFirstName($this->getData('firstName'));
        $user->setMiddleName($this->getData('middleName'));
        $user->setLastName($this->getData('lastName'));
        $user->setInitials($this->getData('initials'));
        $user->setGender($this->getData('gender'));
        $user->setAffiliation($this->getData('affiliation'), null); // Localized
        $user->setSignature($this->getData('signature'), null); // Localized
        $user->setEmail($this->getData('email'));
        $user->setData('orcid', $this->getData('orcid'));
        $user->setUrl($this->getData('userUrl'));
        $user->setPhone($this->getData('phone'));
        $user->setFax($this->getData('fax'));
        $user->setMailingAddress($this->getData('mailingAddress'));
        $user->setCountry($this->getData('country'));
        $user->setBiography($this->getData('biography'), null); // Localized
        $user->setGossip($this->getData('gossip'), null); // Localized
        $user->setMustChangePassword($this->getData('mustChangePassword') ? 1 : 0);
        $user->setAuthId((int) $this->getData('authId'));

        $site = $request->getSite();
        $availableLocales = $site->getSupportedLocales();

        $locales = [];
        foreach ($this->getData('userLocales') as $locale) {
            if (AppLocale::isLocaleValid($locale) && in_array($locale, $availableLocales)) {
                $locales[] = $locale;
            }
        }
        $user->setLocales($locales);

        if ($user->getAuthId()) {
            $authDao = DAORegistry::getDAO('AuthSourceDAO');
            $auth = $authDao->getPlugin($user->getAuthId());
        }

        if ($user->getId() != null) {
            $userId = $user->getId();
            if ($this->getData('password') !== '') {
                if (isset($auth)) {
                    $auth->doSetUserPassword($user->getUsername(), $this->getData('password'));
                    $user->setPassword(Validation::encryptCredentials($userId, Validation::generatePassword())); // Used for PW reset hash only
                } else {
                    $user->setPassword(Validation::encryptCredentials($user->getUsername(), $this->getData('password')));
                }
            }

            if (isset($auth)) {
                // FIXME Should try to create user here too?
                $auth->doSetUserInfo($user);
            }
            // [WIZDAM] Removed & ref
            parent::execute($user);
            $userDao->updateObject($user);
        } else {
            $user->setUsername($this->getData('username'));
            if ($this->getData('generatePassword')) {
                $password = Validation::generatePassword();
                $sendNotify = true;
            } else {
                $password = $this->getData('password');
                $sendNotify = $this->getData('sendNotify');
            }

            if (isset($auth)) {
                $user->setPassword($password);
                // FIXME Check result and handle failures
                $auth->doCreateUser($user);
                $user->setAuthId($auth->authId);
                $user->setPassword(Validation::encryptCredentials($user->getId(), Validation::generatePassword())); // Used for PW reset hash only
            } else {
                $user->setPassword(Validation::encryptCredentials($this->getData('username'), $password));
            }

            $user->setDateRegistered(Core::getCurrentDate());
            // [WIZDAM] Removed & ref
            parent::execute($user);
            $userId = $userDao->insertUser($user);

            $isManager = Validation::isJournalManager();

            if (!empty($this->_data['enrollAs'])) {
                foreach ($this->getData('enrollAs') as $roleName) {
                    // Enroll new user into an initial role
                    $roleDao = DAORegistry::getDAO('RoleDAO');
                    $roleId = $roleDao->getRoleIdFromPath($roleName);
                    if (!$isManager && $roleId != ROLE_ID_READER) continue;
                    if ($roleId != null) {
                        $role = new Role();
                        $role->setJournalId($journal->getId());
                        $role->setUserId($userId);
                        $role->setRoleId($roleId);
                        $roleDao->insertRole($role);
                    }
                }
            }

            if ($sendNotify) {
                // Send welcome email to user
                import('classes.mail.MailTemplate');
                $mail = new MailTemplate('USER_REGISTER');
                $mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                $mail->assignParams(['username' => $this->getData('username'), 'password' => $password, 'userFullName' => $user->getFullName()]);
                $mail->addRecipient($user->getEmail(), $user->getFullName());
                $mail->send();
            }
        }

        // Insert the user interests
        $interests = $this->getData('interestsKeywords') ? $this->getData('interestsKeywords') : $this->getData('interestsTextOnly');
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        $interestManager->setInterestsForUser($user, $interests);
    }
}

?>