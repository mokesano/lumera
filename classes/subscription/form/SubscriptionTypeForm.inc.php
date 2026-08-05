<?php
declare(strict_types=1);

/**
 * @file classes/subscription/form/SubscriptionTypeForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionTypeForm
 * @ingroup manager_form
 *
 * @brief Form for journal managers to create/edit subscription types.
 */

import('lib.pkp.classes.form.Form');

class SubscriptionTypeForm extends Form {

    /** @var int|null */
    protected $_typeId;

    /** @var array */
    protected $_validFormats;

    /** @var array */
    protected $_validCurrencies;

    /**
     * Constructor.
     * @param int|null $typeId
     */
    public function __construct($typeId = null) {
        $this->_validFormats = [
            SUBSCRIPTION_TYPE_FORMAT_ONLINE => __('subscriptionTypes.format.online'),
            SUBSCRIPTION_TYPE_FORMAT_PRINT => __('subscriptionTypes.format.print'),
            SUBSCRIPTION_TYPE_FORMAT_PRINT_ONLINE => __('subscriptionTypes.format.printOnline')
        ];

        /** @var CurrencyDAO $currencyDao */
        $currencyDao = DAORegistry::getDAO('CurrencyDAO');
        $currencies = $currencyDao->getCurrencies();
        $this->_validCurrencies = [];

        if (is_array($currencies) || $currencies instanceof \Traversable) {
            foreach ($currencies as $currency) {
                $this->_validCurrencies[(string) $currency->getCodeAlpha()] = 
                    (string) $currency->getName() . ' (' . (string) $currency->getCodeAlpha() . ')';
            }
        }

        $this->_typeId = $typeId !== null ? (int) $typeId : null;
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        parent::__construct('subscription/subscriptionTypeForm.tpl');

        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'manager.subscriptionTypes.form.typeNameRequired'));
        
        $this->addCheck(new FormValidator($this, 'cost', 'required', 'manager.subscriptionTypes.form.costRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'cost', 'required', 'manager.subscriptionTypes.form.costNumeric', function($cost) {
            return is_numeric($cost) && (float) $cost >= 0;
        }));

        $this->addCheck(new FormValidator($this, 'currency', 'required', 'manager.subscriptionTypes.form.currencyRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'currency', 'required', 'manager.subscriptionTypes.form.currencyValid', array_keys($this->_validCurrencies)));

        $this->addCheck(new FormValidatorInSet($this, 'nonExpiring', 'optional', 'manager.subscriptionTypes.form.nonExpiringValid', ['0', '1']));

        $this->addCheck(new FormValidator($this, 'format', 'required', 'manager.subscriptionTypes.form.formatRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'format', 'required', 'manager.subscriptionTypes.form.formatValid', array_keys($this->_validFormats)));

        $this->addCheck(new FormValidatorInSet($this, 'institutional', 'optional', 'manager.subscriptionTypes.form.institutionalValid', ['0', '1']));
        $this->addCheck(new FormValidatorInSet($this, 'membership', 'optional', 'manager.subscriptionTypes.form.membershipValid', ['1']));
        $this->addCheck(new FormValidatorInSet($this, 'disable_public_display', 'optional', 'manager.subscriptionTypes.form.publicValid', ['1']));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param int|null $typeId
     */
    public function SubscriptionTypeForm($typeId = null) {
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
     * Get a list of localized field names for this form.
     * @return array
     */
    public function getLocaleFieldNames() {
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        return $subscriptionTypeDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('typeId', $this->_typeId);
        $templateMgr->assign('validCurrencies', $this->_validCurrencies);
        $templateMgr->assign('validFormats', $this->_validFormats);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.subscriptions');

        parent::display($request, $template);
    }

    /**
     * Initialize form data from current subscription type.
     * @return void
     */
    public function initData() {
        if ($this->_typeId !== null) {
            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $subscriptionType = $subscriptionTypeDao->getSubscriptionType($this->_typeId);

            if ($subscriptionType !== null) {
                $this->_data = [
                    'name' => $subscriptionType->getName(null),
                    'description' => $subscriptionType->getDescription(null),
                    'cost' => (float) $subscriptionType->getCost(),
                    'currency' => (string) $subscriptionType->getCurrencyCodeAlpha(),
                    'nonExpiring' => (int) $subscriptionType->getNonExpiring(),
                    'duration' => (int) $subscriptionType->getDuration(),
                    'format' => (int) $subscriptionType->getFormat(),
                    'institutional' => (int) $subscriptionType->getInstitutional(),
                    'membership' => (int) $subscriptionType->getMembership(),
                    'disable_public_display' => (int) $subscriptionType->getDisablePublicDisplay()
                ];
            } else {
                $this->_typeId = null;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'name', 'description', 'cost', 'currency', 'nonExpiring', 
            'duration', 'format', 'institutional', 'membership', 'disable_public_display'
        ]);

        if ((int) $this->getData('nonExpiring') === 0) {
            $this->addCheck(new FormValidator($this, 'duration', 'required', 'manager.subscriptionTypes.form.durationRequired'));
            $this->addCheck(new FormValidatorCustom($this, 'duration', 'required', 'manager.subscriptionTypes.form.durationNumeric', function($duration) {
                return is_numeric($duration) && (float) $duration >= 0;
            }));
        }
    }

    /**
     * Save subscription type.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        if ($journal === null) {
            return;
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        
        $subscriptionType = null;
        $nonExpiring = 0;

        if ($this->_typeId !== null) {
            $subscriptionType = $subscriptionTypeDao->getSubscriptionType($this->_typeId);
            if ($subscriptionType !== null) {
                $nonExpiring = (int) $subscriptionType->getNonExpiring();
            }
        }

        if ($subscriptionType === null) {
            $subscriptionType = new SubscriptionType();
            $nonExpiring = $this->getData('nonExpiring') !== null ? (int) $this->getData('nonExpiring') : 0;
            $subscriptionType->setNonExpiring($nonExpiring);
            $subscriptionType->setInstitutional($this->getData('institutional') !== null ? (int) $this->getData('institutional') : 0);
        }

        $subscriptionType->setJournalId($journal->getId());
        $subscriptionType->setName($this->getData('name'), null);
        $subscriptionType->setDescription($this->getData('description'), null);
        $subscriptionType->setCost(round((float) $this->getData('cost'), 2));
        $subscriptionType->setCurrencyCodeAlpha((string) $this->getData('currency'));
        $subscriptionType->setDuration($nonExpiring ? null : (int) $this->getData('duration'));
        $subscriptionType->setFormat((int) $this->getData('format'));
        $subscriptionType->setMembership($this->getData('membership') !== null ? (int) $this->getData('membership') : 0);
        $subscriptionType->setDisablePublicDisplay($this->getData('disable_public_display') !== null ? (int) $this->getData('disable_public_display') : 0);

        if ($subscriptionType->getTypeId() !== null) {
            $subscriptionTypeDao->updateSubscriptionType($subscriptionType);
        } else {
            $subscriptionType->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
            $subscriptionTypeDao->insertSubscriptionType($subscriptionType);
            $subscriptionTypeDao->resequenceSubscriptionTypes($subscriptionType->getJournalId());
        }
    }
    
}
?>