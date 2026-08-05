<?php
declare(strict_types=1);

/**
 * @defgroup subscription_form
 */

/**
 * @file classes/subscription/form/SubscriptionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionForm
 * @ingroup subscription_form
 *
 * @brief Base form class for subscription create/edits.
 */

import('lib.pkp.classes.form.Form');

class SubscriptionForm extends Form {

    /** @var Subscription|null */
    protected $subscription;

    /** @var int|null */
    protected $userId;

    /** @var array */
    protected $subscriptionTypes = [];

    /** @var array */
    protected $validStatus = [];

    /** @var array */
    protected $validCountries = [];

    /**
     * Constructor.
     * @param string $template
     * @param int|null $subscriptionId
     * @param int|null $userId
     */
    public function __construct($template, $subscriptionId = null, $userId = null) {
        parent::__construct($template);

        $this->subscription = null;
        $this->userId = $userId !== null ? (int) $userId : null;

        /** @var SubscriptionDAO $subscriptionDao */
        $subscriptionDao = DAORegistry::getDAO('SubscriptionDAO');
        $this->validStatus = $subscriptionDao->getStatusOptions();

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $this->validCountries = $countryDao->getCountries();

        $this->addCheck(new FormValidator($this, 'userId', 'required', 'manager.subscriptions.form.userIdRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'manager.subscriptions.form.userIdValid', function($userId) {
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            return $userDao->userExistsById((int) $userId);
        }));

        $this->addCheck(new FormValidator($this, 'userFirstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'userLastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
        $this->addCheck(new FormValidatorInSet($this, 'userCountry', 'optional', 'manager.subscriptions.form.countryValid', array_keys($this->validCountries)));

        $this->addCheck(new FormValidator($this, 'status', 'required', 'manager.subscriptions.form.statusRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'status', 'required', 'manager.subscriptions.form.statusValid', array_keys($this->validStatus)));

        $this->addCheck(new FormValidator($this, 'typeId', 'required', 'manager.subscriptions.form.typeIdRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'notifyEmail', 'optional', 'manager.subscriptions.form.notifyEmailValid', ['1']));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param int|null $subscriptionId
     * @param int|null $userId
     */
    public function SubscriptionForm($subscriptionId = null, $userId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], array_merge(['subscription/subscriptionForm.tpl'], $args));
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $req = Application::get()->getRequest();
        $journal = $req->getJournal();

        $subscriptionId = $this->subscription !== null ? $this->subscription->getId() : null;
        
        if ($this->subscription !== null) {
            $templateMgr->assign('dateRemindedBefore', $this->subscription->getDateRemindedBefore());
            $templateMgr->assign('dateRemindedAfter', $this->subscription->getDateRemindedAfter());
        }

        $templateMgr->assign('subscriptionId', $subscriptionId);
        $templateMgr->assign('yearOffsetPast', SUBSCRIPTION_YEAR_OFFSET_PAST);
        $templateMgr->assign('yearOffsetFuture', SUBSCRIPTION_YEAR_OFFSET_FUTURE);

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $targetUserId = $this->userId ?? $this->getData('userId');
        $user = $targetUserId !== null ? $userDao->getUser((int) $targetUserId) : null;

        if ($user !== null) {
            $templateMgr->assign('userId', $user->getId());
            $templateMgr->assign('username', $user->getUsername());
            $templateMgr->assign('userSalutation', $user->getSalutation());
            $templateMgr->assign('userFirstName', $user->getFirstName());
            $templateMgr->assign('userMiddleName', $user->getMiddleName());
            $templateMgr->assign('userLastName', $user->getLastName());
            $templateMgr->assign('userInitials', $user->getInitials());
            $templateMgr->assign('userGender', $user->getGender());
            $templateMgr->assign('userAffiliation', $user->getAffiliation(null));
            $templateMgr->assign('orcid', $user->getData('orcid'));
            $templateMgr->assign('userUrl', $user->getUrl());
            $templateMgr->assign('userFullName', $user->getFullName());
            $templateMgr->assign('userEmail', $user->getEmail());
            $templateMgr->assign('userPhone', $user->getPhone());
            $templateMgr->assign('userFax', $user->getFax());
            $templateMgr->assign('userMailingAddress', $user->getMailingAddress());
            $templateMgr->assign('userCountry', $user->getCountry());
            $templateMgr->assign('genderOptions', $userDao->getGenderOptions());
        }

        $templateMgr->assign('validStatus', $this->validStatus);
        $templateMgr->assign('subscriptionTypes', $this->subscriptionTypes);
        $templateMgr->assign('validCountries', $this->validCountries);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.subscriptions');

        parent::display($request, $template);
    }

    /**
     * Initialize form data from current subscription.
     * @return void
     */
    public function initData() {
        if ($this->subscription !== null) {
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $targetUserId = $this->userId ?? $this->subscription->getUserId();
            $user = $targetUserId !== null ? $userDao->getUser((int) $targetUserId) : null;

            if ($user !== null) {
                $this->_data = [
                    'status' => $this->subscription->getStatus(),
                    'userId' => $user->getId(),
                    'typeId' => $this->subscription->getTypeId(),
                    'dateStart' => $this->subscription->getDateStart(),
                    'dateEnd' => $this->subscription->getDateEnd(),
                    'username' => $user->getUsername(),
                    'userSalutation' => $user->getSalutation(),
                    'userFirstName' => $user->getFirstName(),
                    'userMiddleName' => $user->getMiddleName(),
                    'userLastName' => $user->getLastName(),
                    'userInitials' => $user->getInitials(),
                    'userGender' => $user->getGender(),
                    'userAffiliation' => $user->getAffiliation(null),
                    'orcid' => $user->getData('orcid'),
                    'userUrl' => $user->getUrl(),
                    'userEmail' => $user->getEmail(),
                    'userPhone' => $user->getPhone(),
                    'userFax' => $user->getFax(),
                    'userMailingAddress' => $user->getMailingAddress(),
                    'userCountry' => $user->getCountry(),
                    'membership' => $this->subscription->getMembership(),
                    'referenceNumber' => $this->subscription->getReferenceNumber(),
                    'notes' => $this->subscription->getNotes()
                ];
            }
        }
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'status', 'userId', 'typeId', 'dateStartYear', 'dateStartMonth', 'dateStartDay', 
            'dateEndYear', 'dateEndMonth', 'dateEndDay', 'userSalutation', 'userFirstName', 
            'userMiddleName', 'userLastName', 'userInitials', 'userGender', 'userAffiliation', 
            'orcid', 'userUrl', 'userEmail', 'userPhone', 'userFax', 'userMailingAddress', 
            'userCountry', 'membership', 'referenceNumber', 'notes', 'notifyEmail'
        ]);

        $req = Application::get()->getRequest();
        $this->_data['dateStart'] = $req->getUserDateVar('dateStart');
        $this->_data['dateEnd'] = $req->getUserDateVar('dateEnd');

        $this->addCheck(new FormValidatorEmail($this, 'userEmail', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'userEmail', 'required', 'user.register.form.emailExists', [DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'], [$this->getData('userId'), true], true));

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $needMembership = $subscriptionTypeDao->getSubscriptionTypeMembership((int) $this->getData('typeId'));

        if ($needMembership) {
            $this->addCheck(new FormValidator($this, 'membership', 'required', 'manager.subscriptions.form.membershipRequired'));
        }

        $nonExpiring = $subscriptionTypeDao->getSubscriptionTypeNonExpiring((int) $this->getData('typeId'));

        if (!$nonExpiring) {
            $minYear = (int) date('Y') + SUBSCRIPTION_YEAR_OFFSET_PAST;
            $maxYear = (int) date('Y') + SUBSCRIPTION_YEAR_OFFSET_FUTURE;

            $this->addCheck(new FormValidator($this, 'dateStartYear', 'required', 'manager.subscriptions.form.dateStartRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'dateStartYear', 'required', 'manager.subscriptions.form.dateStartValid', function($dateStartYear) use ($minYear, $maxYear) {
                return (int) $dateStartYear >= $minYear && (int) $dateStartYear <= $maxYear;
            }));
            $this->addCheck(new FormValidator($this, 'dateStartMonth', 'required', 'manager.subscriptions.form.dateStartRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'dateStartMonth', 'required', 'manager.subscriptions.form.dateStartValid', function($dateStartMonth) {
                return (int) $dateStartMonth >= 1 && (int) $dateStartMonth <= 12;
            }));
            $this->addCheck(new FormValidator($this, 'dateStartDay', 'required', 'manager.subscriptions.form.dateStartRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'dateStartDay', 'required', 'manager.subscriptions.form.dateStartValid', function($dateStartDay) {
                return (int) $dateStartDay >= 1 && (int) $dateStartDay <= 31;
            }));

            $this->addCheck(new FormValidator($this, 'dateEndYear', 'required', 'manager.subscriptions.form.dateEndRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'dateEndYear', 'required', 'manager.subscriptions.form.dateEndValid', function($dateEndYear) use ($minYear, $maxYear) {
                return (int) $dateEndYear >= $minYear && (int) $dateEndYear <= $maxYear;
            }));
            $this->addCheck(new FormValidator($this, 'dateEndMonth', 'required', 'manager.subscriptions.form.dateEndRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'dateEndMonth', 'required', 'manager.subscriptions.form.dateEndValid', function($dateEndMonth) {
                return (int) $dateEndMonth >= 1 && (int) $dateEndMonth <= 12;
            }));
            $this->addCheck(new FormValidator($this, 'dateEndDay', 'required', 'manager.subscriptions.form.dateEndRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'dateEndDay', 'required', 'manager.subscriptions.form.dateEndValid', function($dateEndDay) {
                return (int) $dateEndDay >= 1 && (int) $dateEndDay <= 31;
            }));
        }

        if (isset($this->_data['notifyEmail']) && (int) $this->_data['notifyEmail'] === 1) {
            $this->addCheck(new FormValidatorCustom($this, 'notifyEmail', 'required', 'manager.subscriptions.form.subscriptionContactRequired', function() {
                $req = Application::get()->getRequest();
                $journal = $req->getJournal();
                if ($journal === null) return false;
                
                /** @var JournalSettingsDAO $journalSettingsDao */
                $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                $subscriptionName = (string) $journalSettingsDao->getSetting($journal->getId(), 'subscriptionName');
                $subscriptionEmail = (string) $journalSettingsDao->getSetting($journal->getId(), 'subscriptionEmail');
                
                return $subscriptionName !== '' && $subscriptionEmail !== '';
            }));
        }
    }

    /**
     * Save subscription.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $req = Application::get()->getRequest();
        $journal = $req->getJournal();
        if ($journal === null) return;

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getUser((int) $this->getData('userId'));
        
        if ($user === null || $this->subscription === null) return;

        $this->subscription->setJournalId($journal->getId());
        $this->subscription->setStatus((int) $this->getData('status'));
        $this->subscription->setUserId($user->getId());
        $this->subscription->setTypeId((int) $this->getData('typeId'));
        $this->subscription->setMembership($this->getData('membership') ? (string) $this->getData('membership') : null);
        $this->subscription->setReferenceNumber($this->getData('referenceNumber') ? (string) $this->getData('referenceNumber') : null);
        $this->subscription->setNotes($this->getData('notes') ? (string) $this->getData('notes') : null);

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $nonExpiring = $subscriptionTypeDao->getSubscriptionTypeNonExpiring((int) $this->getData('typeId'));
        
        $this->subscription->setDateStart($nonExpiring ? null : $this->getData('dateStart'));
        $this->subscription->setDateEnd($nonExpiring ? null : $this->getData('dateEnd'));

        $user->setSalutation((string) $this->getData('userSalutation'));
        $user->setFirstName((string) $this->getData('userFirstName'));
        $user->setMiddleName((string) $this->getData('userMiddleName'));
        $user->setLastName((string) $this->getData('userLastName'));
        $user->setInitials((string) $this->getData('userInitials'));
        $user->setGender((string) $this->getData('userGender'));
        $user->setAffiliation($this->getData('userAffiliation'), null);
        $user->setData('orcid', $this->getData('orcid'));
        $user->setUrl((string) $this->getData('userUrl'));
        $user->setEmail((string) $this->getData('userEmail'));
        $user->setPhone((string) $this->getData('userPhone'));
        $user->setFax((string) $this->getData('userFax'));
        $user->setMailingAddress((string) $this->getData('userMailingAddress'));
        $user->setCountry((string) $this->getData('userCountry'));

        parent::execute($object);
        $userDao->updateObject($user);
    }

    /**
     * Internal function to prepare notification email.
     * @param string $mailTemplateKey
     * @return MailTemplate|null
     */
    public function _prepareNotificationEmail($mailTemplateKey) {
        if ($this->subscription === null) return null;

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        $req = Application::get()->getRequest();
        $journal = $req->getJournal();
        if ($journal === null) return null;

        $user = $userDao->getUser($this->subscription->getUserId());
        if ($user === null) return null;

        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($this->subscription->getTypeId());
        if ($subscriptionType === null) return null;

        $journalId = $journal->getId();
        $subscriptionName = (string) $journalSettingsDao->getSetting($journalId, 'subscriptionName');
        $subscriptionEmail = (string) $journalSettingsDao->getSetting($journalId, 'subscriptionEmail');
        $subscriptionPhone = (string) $journalSettingsDao->getSetting($journalId, 'subscriptionPhone');
        $subscriptionFax = (string) $journalSettingsDao->getSetting($journalId, 'subscriptionFax');
        $subscriptionMailingAddress = (string) $journalSettingsDao->getSetting($journalId, 'subscriptionMailingAddress');
        
        $subscriptionContactSignature = $subscriptionName;
        if ($subscriptionMailingAddress !== '') $subscriptionContactSignature .= "\n" . $subscriptionMailingAddress;
        if ($subscriptionPhone !== '') $subscriptionContactSignature .= "\n" . __('user.phone') . ': ' . $subscriptionPhone;
        if ($subscriptionFax !== '') $subscriptionContactSignature .= "\n" . __('user.fax') . ': ' . $subscriptionFax;
        $subscriptionContactSignature .= "\n" . __('user.email') . ': ' . $subscriptionEmail;

        $paramArray = [
            'subscriberName' => $user->getFullName(),
            'journalName' => $journal->getLocalizedTitle(),
            'subscriptionType' => $subscriptionType->getSummaryString(),
            'username' => $user->getUsername(),
            'subscriptionContactSignature' => $subscriptionContactSignature
        ];

        import('classes.mail.MailTemplate');
        $mail = new MailTemplate($mailTemplateKey);
        $mail->setFrom($subscriptionEmail, $subscriptionName);
        $mail->addRecipient($user->getEmail(), $user->getFullName());
        $mail->setSubject($mail->getSubject());
        $mail->setBody($mail->getBody());
        $mail->assignParams($paramArray);

        return $mail;
    }

}
?>