<?php
declare(strict_types=1);

/**
 * @file pages/manager/ManagerPaymentHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerPaymentHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for configuring payments.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class ManagerPaymentHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ManagerPaymentHandler() {
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
     * Display Settings Form (main payments page)
     * @param array $args
     * @param PKPRequest $request
     */
    public function payments($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::payments($args);
    }
     
    /**
     * Execute the form or display it again if there are problems
     * @param array $args
     * @param PKPRequest $request
     */
    public function savePaymentSettings($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.payment.ojs.OJSPaymentAction');
        $success = OJSPaymentAction::savePaymentSettings($args);

        if ($success) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign([
                'currentUrl' => $request->url(null, null, 'payments'),
                'pageTitle' => 'manager.payment.feePaymentOptions',
                'message' => 'common.changesSaved',
                'backLink' => $request->url(null, null, 'payments'),
                'backLinkLabel' => 'manager.payment.feePaymentOptions'
            ]);
            $templateMgr->display('common/message.tpl');        
        }
    }     
     
    /** 
     * Display all payments previously made
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewPayments($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::viewPayments($args);
    }

    /** 
     * Display a single Completed payment 
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewPayment($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::viewPayment($args);
    }

    /**
     * Display form to edit program settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function payMethodSettings() {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::payMethodSettings();
    }
    
    /**
     * Save changes to payment settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function savePayMethodSettings($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.payment.ojs.OJSPaymentAction');
        $success = OJSPaymentAction::savePayMethodSettings();

        if ($success) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign([
                'currentUrl' => $request->url(null, null, 'payMethodSettings'),
                'pageTitle' => 'manager.payment.paymentMethods',
                'message' => 'common.changesSaved',
                'backLink' => $request->url(null, null, 'payMethodSettings'),
                'backLinkLabel' => 'manager.payment.paymentMethods'
            ]);
            $templateMgr->display('common/message.tpl');        
        }
    }
}
?>