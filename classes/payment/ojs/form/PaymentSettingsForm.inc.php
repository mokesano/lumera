<?php
declare(strict_types=1);

/**
 * @file classes/payments/ojs/form/PaymentSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentSettingsForm
 * @ingroup payments
 *
 * @brief Form for managers to modify Payment costs and settings
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class PaymentSettingsForm extends Form {
    
    /** @var array keys are valid subscription type currencies */
    public $validCurrencies;

    /** @var array the setting names */
    public $settings;

    /** $var string */
    public $errors;

    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct('payments/paymentSettings.tpl');

        $this->settings = [
            'journalPaymentsEnabled' => 'bool',
            'currency' => 'string',
            'submissionFeeEnabled' => 'bool',
            'submissionFee' => 'float',
            'submissionFeeName' => 'string',
            'submissionFeeDescription' => 'string',
            'publicationFeeEnabled' => 'bool',
            'publicationFee' => 'float',
            'publicationFeeName' => 'string',
            'publicationFeeDescription' => 'string',
            'fastTrackFeeEnabled' => 'bool',
            'fastTrackFee' => 'float',
            'fastTrackFeeName' => 'string',
            'fastTrackFeeDescription' => 'string',
            'purchaseArticleFeeEnabled' => 'bool',
            'purchaseArticleFee' => 'float',
            'purchaseArticleFeeName' => 'string',
            'purchaseArticleFeeDescription' => 'string',
            'purchaseIssueFeeEnabled' => 'bool',
            'purchaseIssueFee' => 'float',
            'purchaseIssueFeeName' => 'string',
            'purchaseIssueFeeDescription' => 'string',
            'membershipFeeEnabled' => 'bool',
            'membershipFee' => 'float',
            'membershipFeeName' => 'string',
            'membershipFeeDescription' => 'string',
            'waiverPolicy' => 'string',
            'donationFeeEnabled' => 'bool',
            'donationFeeName' => 'string',
            'donationFeeDescription' => 'string',
            'restrictOnlyPdf' => 'bool',
            'acceptSubscriptionPayments' => 'bool',
            'acceptGiftSubscriptionPayments' => 'bool',
            
            // [WIZDAM UX] REGISTER NEW TAX & DISCOUNT VARIABLES ---
            'paymentTax' => 'float',
            'paymentTaxInclusive' => 'bool',
            'paymentDiscount' => 'float',
        ];

        // [WIZDAM FIX] Replaced create_function with anonymous functions
        $this->addCheck(new FormValidatorCustom($this, 'submissionFee', 'optional', 'manager.payment.form.numeric', function($submissionFee) { return is_numeric($submissionFee) && $submissionFee >= 0; }));
        $this->addCheck(new FormValidatorCustom($this, 'publicationFee', 'optional', 'manager.payment.form.numeric', function($publicationFee) { return is_numeric($publicationFee) && $publicationFee >= 0; }));
        $this->addCheck(new FormValidatorCustom($this, 'fastTrackFee', 'optional', 'manager.payment.form.numeric', function($fastTrackFee) { return is_numeric($fastTrackFee) && $fastTrackFee >= 0; }));
        $this->addCheck(new FormValidatorCustom($this, 'purchaseArticleFee', 'optional', 'manager.payment.form.numeric', function($purchaseArticleFee) { return is_numeric($purchaseArticleFee) && $purchaseArticleFee >= 0; }));
        $this->addCheck(new FormValidatorCustom($this, 'purchaseIssueFee', 'optional', 'manager.payment.form.numeric', function($purchaseIssueFee) { return is_numeric($purchaseIssueFee) && $purchaseIssueFee >= 0; }));
        $this->addCheck(new FormValidatorCustom($this, 'membershipFee', 'optional', 'manager.payment.form.numeric', function($membershipFee) { return is_numeric($membershipFee) && $membershipFee >= 0; }));
        
        // [WIZDAM UX] VALIDASI PAJAK & DISKON (Wajib angka & tidak boleh minus)
        $this->addCheck(new FormValidatorCustom($this, 'paymentTax', 'optional', 'manager.payment.form.numeric', function($tax) { return is_numeric($tax) && $tax >= 0; }));
        $this->addCheck(new FormValidatorCustom($this, 'paymentDiscount', 'optional', 'manager.payment.form.numeric', function($discount) { return is_numeric($discount) && $discount >= 0; }));

        // [WIZDAM FIX] Pendaftaran Validasi Manual Payment yang Clean & Clear
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'paymentMethodPluginName', 
            'required', 
            'plugins.paymethod.manual.settings.requirement', 
            [$this, 'validateManualInstructions']
        ));
        
        // grab valid currencies and add Validator
        $currencyDao = DAORegistry::getDAO('CurrencyDAO');
        $currencies = $currencyDao->getCurrencies();
        $this->validCurrencies = [];
        // [WIZDAM FIX] Replaced list/each with foreach
        foreach ($currencies as $currency) {
            $this->validCurrencies[$currency->getCodeAlpha()] = $currency->getName() . ' (' . $currency->getCodeAlpha() . ')';
        }

        // Currency is provided and is valid value
        $this->addCheck(new FormValidator($this, 'currency', 'required', 'manager.subscriptionTypes.form.currencyRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'currency', 'required', 'manager.subscriptionTypes.form.currencyValid', array_keys($this->validCurrencies)));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PaymentSettingsForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the list of field names for which localized settings are used.
     * @return array
     */
    public function getLocaleFieldNames() {
        return [
            'submissionFeeName',
            'submissionFeeDescription',
            'publicationFeeName',
            'publicationFeeDescription',
            'waiverPolicy',
            'fastTrackFeeName',
            'fastTrackFeeDescription',
            'purchaseArticleFeeName',
            'purchaseArticleFeeDescription',
            'purchaseIssueFeeName',
            'purchaseIssueFeeDescription',
            'membershipFeeName',
            'membershipFeeDescription',
            'donationFeeName',
            'donationFeeDescription',
        ];
    }

    /**
     * Display the form.
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('validCurrencies', $this->validCurrencies);
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current group group.
     */
    public function initData() {
        $journal = Request::getJournal();
        foreach ($this->settings as $settingName => $settingType) {
            $this->_data[$settingName] = $journal->getSetting($settingName);
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array_keys($this->settings));
    }

    /**
     * [WIZDAM FIX] Callback khusus untuk memvalidasi instruksi Manual Payment.
     * @param string $pluginName Nama plugin yang dipilih admin
     * @return bool
     */
    public function validateManualInstructions($manualInstructions) {
        $pluginName = $this->getData('journalPayMethodPluginName');
        
        // Jika plugin yang dipilih adalah ManualPayment, maka instruksi tidak boleh kosong
        if ($pluginName === 'ManualPayment') {
            return !empty(trim((string) $manualInstructions));
        }
        
        // Jika menggunakan metode pembayaran lain (misal: PayPal), abaikan validasi ini
        return true;
    }

    /**
     * Save settings
     */
    public function save() {
        $journal = Request::getJournal();
        $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        foreach ($this->_data as $name => $value) {
            $isLocalized = in_array($name, $this->getLocaleFieldNames());
            $settingsDao->updateSetting(
                $journal->getId(),
                $name,
                $value,
                $this->settings[$name],
                $isLocalized
            );
        }
    }
}
?>