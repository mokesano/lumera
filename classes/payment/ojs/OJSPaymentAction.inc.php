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
 * Common actions for payment management functions.
 * [WIZDAM EDITION] 
 * - FIXED: PHP 8.4 Static Compliance
 * - REFACTORED: De-monolithized into modular, testable methods.
 */

class OJSPaymentAction {
    
    // 
    // PUBLIC ACTION METHODS (Controllers)
    // 

    /**
     * Display Payments Settings Form (main payments page)
     * @param array $args
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
     * Execute the form or display it again if there are problems
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
     * Display all payments previously made
     * @param array $args
     */
    public static function viewPayments($args) {
        $journal = Application::get()->getRequest()->getJournal();
        $rangeInfo = self::getPaginationRange('payments');
        
        $paymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
        $payments = $paymentDao->getPaymentsByJournalId($journal->getId(), $rangeInfo);
        
        $templateMgr = TemplateManager::getManager();
        self::assignCommonPaymentDAOs($templateMgr, $journal);
        $templateMgr->assign('payments', $payments);

        $templateMgr->display('payments/viewPayments.tpl');
    }

    /**
     * Display a single Completed payment
     * @param array $args
     */
    public static function viewPayment($args) {
        $journal = Application::get()->getRequest()->getJournal();
        $completedPaymentId = isset($args[0]) ? (int) $args[0] : 0;
        
        $paymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
        $payment = $paymentDao->getCompletedPayment($completedPaymentId);

        $templateMgr = TemplateManager::getManager();
        self::assignCommonPaymentDAOs($templateMgr, $journal);
        $templateMgr->assign('payment', $payment);

        $templateMgr->display('payments/viewPayment.tpl');
    }

    /**
     * Display form to edit program settings.
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
     * @param TemplateManager $templateMgr
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
     */
    private static function assignCommonPaymentDAOs($templateMgr, $journal): void {
        $templateMgr->assign([
            'helpTopicId' => 'journal.managementPages.payments',
            'isJournalManager' => Validation::isJournalManager($journal->getId()),
            'individualSubscriptionDao' => DAORegistry::getDAO('IndividualSubscriptionDAO'),
            'institutionalSubscriptionDao' => DAORegistry::getDAO('InstitutionalSubscriptionDAO'),
            'userDao' => DAORegistry::getDAO('UserDAO')
        ]);
    }
}
?>