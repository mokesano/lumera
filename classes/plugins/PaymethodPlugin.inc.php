<?php
declare(strict_types=1);

/**
 * @file classes/plugins/PaymethodPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymethodPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for paymethod plugins
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & Signature Fixes
 */

import('classes.plugins.Plugin');

class PaymethodPlugin extends Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PaymethodPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Called as a plugin is registered to the registry. Subclasses over-
     * riding this method should call the parent method first.
     * @param string $category Name of category plugin was registered to
     * @param string $path The path the plugin was found in
     * @param int|null $mainContextId
     * @return bool True iff plugin initialized successfully
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        if ($success) {
            // [MODERNIZATION] Removed reference &$this
            HookRegistry::register('Template::Manager::Payment::displayPaymentSettingsForm', [$this, '_smartyDisplayPaymentSettingsForm']);
        }
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category, and should be suitable for part of a filename
     * (ie short, no spaces, and no dependencies on cases being unique).
     * @return string name of plugin
     */
    public function getName(): string {
        assert(false); // Should always be overridden
        return 'PaymethodPlugin';
    }

    /**
     * Get a description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        assert(false); // Should always be overridden
        return '';
    }

    /**
     * Get the Template path for this plugin.
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates' . DIRECTORY_SEPARATOR ;
    }

    /**
     * Display the payment form.
     * [WIZDAM CRITICAL FIX] Removed $key parameter to match PaymentManager call signature
     * @param int $queuedPaymentId
     * @param QueuedPayment $queuedPayment
     * @param PKPRequest $request
     * @return mixed
     */
    public function displayPaymentForm(int $queuedPaymentId, $queuedPayment, $request) {
        assert(false); // Should always be overridden
        return false;
    }

    /**
     * Determine whether or not the payment plugin is configured for use.
     * @return bool
     */
    public function isConfigured() {
        return false; // Abstract; should be implemented in subclasses
    }

    /**
     * This is a hook wrapper that is responsible for calling
     * displayPaymentSettingsForm. Subclasses should override
     * displayPaymentSettingsForm as necessary.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function _smartyDisplayPaymentSettingsForm($hookName, $args) {
        $params = $args[0];
        $smarty = $args[1];

        if (isset($params['plugin']) && $params['plugin'] == $this->getName()) {
            $smarty->display($this->getTemplatePath() . 'settingsForm.tpl');
        }
        return false;
    }

    /**
     * Display the payment settings form.
     * @param array $params
     * @param Smarty $smarty
     * @return string
     */
    public function displayPaymentSettingsForm($params, $smarty) {
        return $smarty->fetch($this->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * Fetch the settings form field names.
     * @return array
     */
    public function getSettingsFormFieldNames() {
        return []; // Subclasses should override
    }

    /**
     * Handle an incoming request from a user callback or an external
     * payment processing system.
     * @param array $args
     * @param PKPRequest $request
     */
    public function handle($args, $request) {
        // Subclass should override.
        $request->redirect(null, null, 'index');
    }
}
?>