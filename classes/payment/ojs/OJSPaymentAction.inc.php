<?php
declare(strict_types=1);

/**
 * @file classes/payment/ojs/OJSPaymentAction.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSPaymentAction
 * @ingroup payments
 *
 * @brief Common actions for payment management functions.
 */

class OJSPaymentAction {
    
    // 
    // PUBLIC ACTION METHODS (Controllers)
    // 

    /**
     * Display Payments Settings Form (main payments page).
     * @param array $args
     * @return void
     */
    public static function payments($args) {
        import('classes.payment.ojs.form.PaymentSettingsForm');
        $form = new PaymentSettingsForm();

        self::assignCommonFormVars();

        if ($form->isLocaleResubmit()) {
            $form->readInputData();
        } else {
            $form->initData();
        }
        $form->display();
    }

    /**
     * Execute the form or display it again if there are problems.
     * @param array $args
     * @return bool
     */
    public static function savePaymentSettings($args) {
        import('classes.payment.ojs.form.PaymentSettingsForm');
        $form = new PaymentSettingsForm();
        $form->readInputData();

        if ($form->validate()) {
            $form->save();
            return true;
        } 
        
        self::assignCommonFormVars();
        $form->display();
        return false;
    }

    /**
     * Display all payments previously made.
     * @param array $args
     * @return void
     */
    public static function viewPayments($args) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        // [WIZDAM FIX] Null-safety check for journal context
        if (!$journal) {
            return;
        }

        $rangeInfo = self::getPaginationRange('payments');
        
        /** @var OJSCompletedPaymentDAO $paymentDao */
        $paymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
        $payments = $paymentDao->getPaymentsByJournalId((int) $journal->getId(), $rangeInfo);
        
        $templateMgr = TemplateManager::getManager($request);
        self::assignCommonPaymentDAOs($templateMgr, $journal);
        $templateMgr->assign('payments', $payments);

        $templateMgr->display('payments/viewPayments.tpl');
    }

    /**
     * Display a single Completed payment.
     * @param array $args
     * @return void
     */
    public static function viewPayment($args) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        // [WIZDAM FIX] Null-safety check for journal context
        if (!$journal) {
            return;
        }

        // [WIZDAM FIX] Modern null coalescing operator with internal coercion
        $completedPaymentId = (int) ($args[0] ?? 0);
        
        /** @var OJSCompletedPaymentDAO $paymentDao */
        $paymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
        $payment = $paymentDao->getCompletedPayment($completedPaymentId);

        $templateMgr = TemplateManager::getManager($request);
        self::assignCommonPaymentDAOs($templateMgr, $journal);
        $templateMgr->assign('payment', $payment);

        $templateMgr->display('payments/viewPayment.tpl');
    }

    /**
     * Display form to edit program settings.
     * @return void
     */
    public static function payMethodSettings() {
        import('classes.payment.ojs.form.PayMethodSettingsForm');
        $form = new PayMethodSettingsForm();
        
        self::assignCommonFormVars();
        $form->initData();
        $form->display();
    }

    /**
     * Save changes to payment settings.
     * @return bool
     */
    public static function savePayMethodSettings() {
        import('classes.payment.ojs.form.PayMethodSettingsForm');
        $form = new PayMethodSettingsForm();
        $form->readInputData();

        if ($form->validate()) {
            $form->execute();
            return true;
        } 
        
        self::assignCommonFormVars();
        $form->display();
        return false;
    }

    // 
    // PRIVATE HELPER METHODS (Modularity & Testability)
    // 

    /**
     * Isolates the instantiation of Handler to fetch RangeInfo.
     * Prevents static calling errors and centralizes pagination logic.
     * @param string $rangeName
     * @return DBResultRange
     */
    private static function getPaginationRange(string $rangeName) {
        $handler = new Handler();
        return $handler->getRangeInfo($rangeName);
    }

    /**
     * Assigns common boilerplate variables required by payment forms.
     * @return void
     */
    private static function assignCommonFormVars(): void {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'journal.managementPages.payments');
    }

    /**
     * Assigns common DAOs and permissions required for viewing payment records.
     * Separating this makes the main display methods cleaner and isolates DB dependencies.
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     * @return void
     */
    private static function assignCommonPaymentDAOs($templateMgr, $journal): void {
        $templateMgr->assign([
            'helpTopicId' => 'journal.managementPages.payments',
            'isJournalManager' => Validation::isJournalManager((int) $journal->getId()),
            'individualSubscriptionDao' => DAORegistry::getDAO('IndividualSubscriptionDAO'),
            'institutionalSubscriptionDao' => DAORegistry::getDAO('InstitutionalSubscriptionDAO'),
            'userDao' => DAORegistry::getDAO('UserDAO')
        ]);
    }

}
?>