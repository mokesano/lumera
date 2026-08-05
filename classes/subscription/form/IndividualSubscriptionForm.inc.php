<?php
declare(strict_types=1);

/**
 * @defgroup subscription_form
 */
 
/**
 * @file classes/subscription/form/IndividualSubscriptionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IndividualSubscriptionForm
 * @ingroup subscription_form
 *
 * @brief Form class for individual subscription create/edits.
 */

import('classes.subscription.form.SubscriptionForm');

class IndividualSubscriptionForm extends SubscriptionForm { // Undefined type 'SubscriptionForm'.

    /**
     * Constructor.
     * @param int|null $subscriptionId
     * @param int|null $userId
     */
    public function __construct($subscriptionId = null, $userId = null) {
        parent::__construct('subscription/individualSubscriptionForm.tpl', $subscriptionId, $userId); // Undefined type 'SubscriptionForm'.

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;

        if ($subscriptionId !== null) {
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO'); 
            if ($subscriptionDao->subscriptionExists((int) $subscriptionId)) {
                $this->subscription = $subscriptionDao->getSubscription((int) $subscriptionId); // Undefined property '$subscription'.
            }
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, false);

        $this->subscriptionTypes = is_object($subscriptionTypes) && method_exists($subscriptionTypes, 'toArray') // Undefined property '$subscriptionTypes'.
            ? $subscriptionTypes->toArray() 
            : (is_array($subscriptionTypes) ? $subscriptionTypes : []);

        if (count($this->subscriptionTypes) === 0) {
            $this->addError('typeId', __('manager.subscriptions.form.typeRequired')); // Undefined method 'addError'.
            $this->addErrorField('typeId'); // Undefined method 'addErrorField'.
        }

        // Ensure subscription type is valid
        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'manager.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $typeIdInt = (int) $typeId;
            return $subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeIdInt, $journalId) && 
                   $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeIdInt) === 0;
        }));

        // Ensure that user does not already have a subscription for this journal
        if ($subscriptionId === null) {
            $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'manager.subscriptions.form.subscriptionExists', [DAORegistry::getDAO('IndividualSubscriptionDAO'), 'subscriptionExistsByUserForJournal'], [$journalId], true));
        } else {
            $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'manager.subscriptions.form.subscriptionExists', function($userId) use ($journalId, $subscriptionId) {
                /** @var IndividualSubscriptionDAO $subscriptionDao */
                $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                $checkId = $subscriptionDao->getSubscriptionIdByUser((int) $userId, $journalId);
                return $checkId === 0 || $checkId === (int) $subscriptionId;
            }));
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param int|null $subscriptionId
     * @param int|null $userId
     */
    public function IndividualSubscriptionForm($subscriptionId = null, $userId = null) {
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
     * Save individual subscription.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $insert = false;
        if (!isset($this->subscription)) {
            import('classes.subscription.IndividualSubscription');
            $this->subscription = new IndividualSubscription();
            $insert = true;
        }

        parent::execute($object); // Undefined type 'SubscriptionForm'.
        
        /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
        $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');

        if ($insert) {
            $individualSubscriptionDao->insertSubscription($this->subscription);
        } else {
            $individualSubscriptionDao->updateSubscription($this->subscription);
        } 

        // Send notification email
        if (isset($this->_data['notifyEmail']) && (int) $this->_data['notifyEmail'] === 1) {
            $mail = $this->_prepareNotificationEmail('SUBSCRIPTION_NOTIFY');
            if ($mail !== null) {
                $mail->send();
            }
        } 
    }
    
}
?>