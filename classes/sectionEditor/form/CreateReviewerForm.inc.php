<?php
declare(strict_types=1);

/**
 * @file classes/sectionEditor/form/CreateReviewerForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CreateReviewerForm
 * @ingroup sectionEditor_form
 *
 * @brief Form for section editors to create reviewers.
 */

import('lib.pkp.classes.form.Form');

class CreateReviewerForm extends Form {

    /** @var int The article this form is for */
    protected $_articleId;

    /**
     * Constructor.
     * @param int $articleId
     */
    public function __construct($articleId) {
        parent::__construct('sectionEditor/createReviewerForm.tpl');
        $this->addCheck(new FormValidatorPost($this));

        $this->_articleId = (int) $articleId;

        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $site = $request->getSite();
        $journal = $request->getJournal();

        // Validation checks for this form
        $this->addCheck(new FormValidator($this, 'username', 'required', 'user.profile.form.usernameRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.register.form.usernameExists', [DAORegistry::getDAO('UserDAO'), 'userExistsByUsername'], [null, true], true));
        $this->addCheck(new FormValidatorAlphaNum($this, 'username', 'required', 'user.register.form.usernameAlphaNumeric'));
        $this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
        $this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', [DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'], [null, true], true));

        // Provide a default for sendNotify: If we're using one-click
        // reviewer access or email-based reviews, it's not necessary;
        // otherwise, it should default to on.
        if ($journal) {
            $reviewerAccessKeysEnabled = (bool) $journal->getSetting('reviewerAccessKeysEnabled');
            $isEmailBasedReview = ((int) $journal->getSetting('mailSubmissionsToReviewers')) === 1;
            $this->setData('sendNotify', !($reviewerAccessKeysEnabled || $isEmailBasedReview));
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param int $articleId
     */
    public function CreateReviewerForm($articleId) {
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
     * Get locale field names.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['biography', 'gossip'];
    }

    /**
     * Display the form.
     * @param mixed $args
     * @param mixed $request
     * @return void
     */
    public function display($args = null, $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('articleId', $this->_articleId);

        $site = $request->getSite();
        $templateMgr->assign('availableLocales', $site->getSupportedLocaleNames());
        
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $templateMgr->assign('genderOptions', $userDao->getGenderOptions());

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $templateMgr->assign('countries', $countryDao->getCountries());

        parent::display($args, $request);
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'salutation',
            'firstName',
            'middleName',
            'lastName',
            'gender',
            'initials',
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
            'sendNotify',
            'username'
        ]);

        $userLocales = $this->getData('userLocales');
        if ($userLocales === null || !is_array($userLocales)) {
            $this->setData('userLocales', []);
        }

        $username = $this->getData('username');
        if ($username !== null) {
            // Usernames must be lowercase
            $this->setData('username', strtolower((string) $username));
        }

        $keywords = $this->getData('keywords');
        if ($keywords !== null && is_array($keywords) && isset($keywords['interests']) && is_array($keywords['interests'])) {
            // The interests are coming in encoded -- Decode them for DB storage
            $this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
        }
    }

    /**
     * Register a new user.
     * @param mixed $object Ignored.
     * @return int userId
     */
    public function execute($object = null) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $site = $request->getSite();

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = new User();

        $user->setSalutation((string) $this->getData('salutation'));
        $user->setFirstName((string) $this->getData('firstName'));
        $user->setMiddleName((string) $this->getData('middleName'));
        $user->setLastName((string) $this->getData('lastName'));
        $user->setGender((string) $this->getData('gender'));
        $user->setInitials((string) $this->getData('initials'));
        $user->setAffiliation((string) $this->getData('affiliation'), null); // Localized
        $user->setEmail((string) $this->getData('email'));
        $user->setData('orcid', (string) $this->getData('orcid'));
        $user->setUrl((string) $this->getData('userUrl'));
        $user->setPhone((string) $this->getData('phone'));
        $user->setFax((string) $this->getData('fax'));
        $user->setMailingAddress((string) $this->getData('mailingAddress'));
        $user->setCountry((string) $this->getData('country'));
        $user->setBiography((string) $this->getData('biography'), null); // Localized
        $user->setGossip((string) $this->getData('gossip'), null); // Localized

        /** @var AuthSourceDAO $authDao */
        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $auth = $authDao->getDefaultPlugin();
        $user->setAuthId($auth !== null ? (int) $auth->authId : 0);

        $availableLocales = $site->getSupportedLocales();
        $locales = [];
        $userLocales = $this->getData('userLocales');
        
        if (is_array($userLocales)) {
            foreach ($userLocales as $locale) {
                if (AppLocale::isLocaleValid((string) $locale) && in_array((string) $locale, $availableLocales, true)) {
                    $locales[] = (string) $locale;
                }
            }
        }
        $user->setLocales($locales);

        $user->setUsername((string) $this->getData('username'));
        $password = Validation::generatePassword();
        $sendNotify = (bool) $this->getData('sendNotify');

        if ($auth !== null) {
            $user->setPassword($password);

            $result = $auth->doCreateUser($user);
            if ($result === false) {
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification(
                    null,
                    NOTIFICATION_TYPE_ERROR,
                    ['contents' => __('user.register.authError')]
                );
                $this->addError('auth', __('user.register.authError'));
                return false;
            }

            $user->setAuthId((int) $auth->authId);
            $user->setPassword(Validation::encryptCredentials((string) $user->getId(), Validation::generatePassword()));
        } else {
            $user->setPassword(Validation::encryptCredentials((string) $this->getData('username'), $password));
        }
        $user->setMustChangePassword($auth !== null ? 0 : 1);

        $user->setDateRegistered(Core::getCurrentDate());
        parent::execute();
        $userId = (int) $userDao->insertUser($user);

        // Insert the user interests
        $interests = $this->getData('interestsKeywords') ?? $this->getData('interestsTextOnly');
        import('lib.pkp.classes.user.InterestManager');
        $interestManager = new InterestManager();
        $interestManager->setInterestsForUser($user, $interests);

        if ($journal) {
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $role = new Role();
            $role->setJournalId((int) $journal->getId());
            $role->setUserId($userId);
            $role->setRoleId(ROLE_ID_REVIEWER);
            $roleDao->insertRole($role);
        }

        if ($sendNotify && $journal) {
            // Send welcome email to user
            import('classes.mail.MailTemplate');
            $mail = new MailTemplate('REVIEWER_REGISTER');
            $mail->setFrom((string) $journal->getSetting('contactEmail'), (string) $journal->getSetting('contactName'));
            $mail->assignParams([
                'username' => (string) $this->getData('username'),
                'password' => (string) $password,
                'userFullName' => (string) $user->getFullName()
            ]);
            $mail->addRecipient((string) $user->getEmail(), (string) $user->getFullName());
            $mail->send();
        }

        return $userId;
    }
    
}
?>