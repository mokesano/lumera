<?php
declare(strict_types=1);

/**
 * @defgroup subscription_form
 */

/**
 * @file classes/subscription/form/InstitutionalSubscriptionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InstitutionalSubscriptionForm
 * @ingroup subscription_form
 * @see SubscriptionForm
 *
 * @brief Form class for institutional subscription create/edits.
 */

import('classes.subscription.form.SubscriptionForm');

class InstitutionalSubscriptionForm extends SubscriptionForm {

    /**
     * Constructor.
     * @param int|null $subscriptionId
     * @param int|null $userId
     */
    public function __construct($subscriptionId = null, $userId = null) {
        parent::__construct('subscription/institutionalSubscriptionForm.tpl', $subscriptionId, $userId);

        $subId = $subscriptionId !== null ? (int) $subscriptionId : null;
        $uid = $userId !== null ? (int) $userId : null;

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;

        if ($subId !== null) {
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO'); 
            if ($subscriptionDao->subscriptionExists($subId)) {
                $this->subscription = $subscriptionDao->getSubscription($subId);
            }
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, true);
        $this->subscriptionTypes = is_object($subscriptionTypes) && method_exists($subscriptionTypes, 'toArray') 
            ? $subscriptionTypes->toArray() 
            : (is_array($subscriptionTypes) ? $subscriptionTypes : []);

        if (count($this->subscriptionTypes) === 0) {
            $this->addError('typeId', __('manager.subscriptions.form.typeRequired'));
            $this->addErrorField('typeId');
        }

        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'manager.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $typeIdInt = (int) $typeId;
            return $subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeIdInt, $journalId) && 
                   $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeIdInt) === 1;
        }));

        $this->addCheck(new FormValidator($this, 'institutionName', 'required', 'manager.subscriptions.form.institutionNameRequired'));

        $this->addCheck(new FormValidatorRegExp($this, 'domain', 'optional', 'manager.subscriptions.form.domainValid', '/^' .
                '[A-Z0-9]+([\-_\.][A-Z0-9]+)*' .
                '\.' .
                '[A-Z]{2,4}' .
            '$/i'));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param int|null $subscriptionId
     * @param int|null $userId
     */
    public function InstitutionalSubscriptionForm($subscriptionId = null, $userId = null) {
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
     * Initialize form data from current subscription.
     * @return void
     */
    public function initData() {
        parent::initData();

        if ($this->subscription !== null) {
            /** @var InstitutionalSubscription $subscription */
            $subscription = $this->subscription;
            $this->_data = array_merge(
                $this->_data,
                [
                    'institutionName' => $subscription->getInstitutionName(),
                    'institutionMailingAddress' => $subscription->getInstitutionMailingAddress(),
                    'domain' => $subscription->getDomain(),
                    'ipRanges' => $subscription->getIPRanges()
                ]
            );
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        parent::readInputData();

        $this->readUserVars(['institutionName', 'institutionMailingAddress', 'domain', 'ipRanges']);

        $ipRanges = $this->getData('ipRanges');
        $ipRangeProvided = false;
        if (is_array($ipRanges)) {
            foreach ($ipRanges as $ipRange) {
                if ($ipRange !== '') {
                    $ipRangeProvided = true;
                    break;
                }
            }
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $this->getData('typeId'));

        if ($subscriptionType !== null && $subscriptionType->getFormat() !== SUBSCRIPTION_TYPE_FORMAT_PRINT) {
            $this->addCheck(new FormValidatorCustom($this, 'domain', 'required', 'manager.subscriptions.form.domainIPRangeRequired', function($domain) use ($ipRangeProvided) {
                return $domain !== '' || $ipRangeProvided;
            }));
        }

        if ($ipRangeProvided) {
            $this->addCheck(new FormValidatorArrayCustom($this, 'ipRanges', 'required', 'manager.subscriptions.form.ipRangeValid', function($ipRange, $regExp) {
                return PKPString::regexp_match($regExp, (string) $ipRange);
            },
                [
                    '/^' .
                    '((([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])){3}((\s)*[' . SUBSCRIPTION_IP_RANGE_RANGE . '](\s)*([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])){3}){0,1})|(([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])){3}([\/](([3][0-2]{0,1})|([1-2]{0,1}[0-9])))))' .
                    '$/i'
                ],
                false,
                [],
                false        
            ));
        }
    }

    /**
     * Save institutional subscription.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $insert = false;
        if ($this->subscription === null) {
            import('classes.subscription.InstitutionalSubscription');
            $this->subscription = new InstitutionalSubscription();
            $insert = true;
        }

        parent::execute($object);

        /** @var InstitutionalSubscription $subscription */
        $subscription = $this->subscription;
        $subscription->setInstitutionName((string) $this->getData('institutionName'));
        $subscription->setInstitutionMailingAddress((string) $this->getData('institutionMailingAddress'));
        $subscription->setDomain((string) $this->getData('domain'));

        $ipRanges = $this->getData('ipRanges');
        if (empty($ipRanges) || (is_array($ipRanges) && empty($ipRanges[0]))) {
            $ipRanges = [];
        }
        $subscription->setIPRanges(is_array($ipRanges) ? $ipRanges : []);

        /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
        $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        if ($insert) {
            $institutionalSubscriptionDao->insertSubscription($subscription);
        } else {
            $institutionalSubscriptionDao->updateSubscription($subscription);
        } 

        if (isset($this->_data['notifyEmail']) && (int) $this->_data['notifyEmail'] === 1) {
            $mail = $this->_prepareNotificationEmail('SUBSCRIPTION_NOTIFY');
            if ($mail !== null) {
                $mail->send();
            }
        } 
    }

}
?>