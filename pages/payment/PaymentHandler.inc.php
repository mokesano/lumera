<?php
declare(strict_types=1);

/**
 * @file pages/payment/PaymentHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentHandler
 * @ingroup pages_payment
 *
 * @brief Handle requests for payment functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.handler.Handler');

class PaymentHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PaymentHandler() {
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
     * Pass request to plugin.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function plugin($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $paymentMethodPlugins = PluginRegistry::loadCategory('paymethod');
        $paymentMethodPluginName = array_shift($args);
        
        if (empty($paymentMethodPluginName) || !isset($paymentMethodPlugins[$paymentMethodPluginName])) {
            $request->redirect(null, null, 'index');
        }

        $paymentMethodPlugin = $paymentMethodPlugins[$paymentMethodPluginName];
        if (!$paymentMethodPlugin->isConfigured()) {
            $request->redirect(null, null, 'index');
        }

        $paymentMethodPlugin->handle($args, $request);
    }
}
?>