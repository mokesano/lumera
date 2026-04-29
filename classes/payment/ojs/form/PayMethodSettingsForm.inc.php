<?php
declare(strict_types=1);

/**
 * @file classes/payments/ojs/form/PaymentSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PayMethodSettingsForm
 * @ingroup payments 
 *
 * @brief Form for managers to modify Payment Plugin settings
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class PayMethodSettingsForm extends Form {
    
    /** @var string|null */
    public $errors;

    /** @var array */
    public $plugins;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('payments/payMethodSettingsForm.tpl');

        // Load the plugins.
        $this->plugins = PluginRegistry::loadCategory('paymethod');

        // Add form checks
        $this->addCheck(new FormValidatorInSet($this, 'paymentMethodPluginName', 'optional', 'manager.payment.paymentPluginInvalid', array_keys($this->plugins)));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PayMethodSettingsForm() {
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
     * Display the form.
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('paymentMethodPlugins', $this->plugins);
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current group group.
     */
    public function initData() {
        $journal = Request::getJournal();

        // Allow the current selection to supercede the stored value
        $paymentMethodPluginName = (string) Request::getUserVar('paymentMethodPluginName');
        if (empty($paymentMethodPluginName) || !in_array($paymentMethodPluginName, array_keys($this->plugins))) {
            $paymentMethodPluginName = $journal->getSetting('paymentMethodPluginName');
        }

        $this->_data = [
            'paymentMethodPluginName' => $paymentMethodPluginName
        ];

        if (isset($this->plugins[$paymentMethodPluginName])) {
            $plugin = $this->plugins[$paymentMethodPluginName];
            foreach ($plugin->getSettingsFormFieldNames() as $field) {
                $this->_data[$field] = $plugin->getSetting($journal->getId(), $field);
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars([
            'paymentMethodPluginName'
        ]);

        $paymentMethodPluginName = $this->getData('paymentMethodPluginName');
        if (isset($this->plugins[$paymentMethodPluginName])) {
            $plugin = $this->plugins[$paymentMethodPluginName];
            $this->readUserVars($plugin->getSettingsFormFieldNames());
        }
    }

    /**
     * Save settings
     */
    public function execute($object = null) {
        $journal = Request::getJournal();
        // Save the general settings for the form
        foreach (['paymentMethodPluginName'] as $journalSettingName) {
            $journal->updateSetting($journalSettingName, $this->getData($journalSettingName));
        }

        // Save the specific settings for the plugin
        $paymentMethodPluginName = $this->getData('paymentMethodPluginName');
        if (isset($this->plugins[$paymentMethodPluginName])) {
            $plugin = $this->plugins[$paymentMethodPluginName];
            foreach ($plugin->getSettingsFormFieldNames() as $field) {
                $plugin->updateSetting($journal->getId(), $field, $this->getData($field));
            }
        }
    }
}

?>