<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminPaymentHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class AdminPaymentHandler
 * 
 * @brief Handler khusus untuk Site Administrator mengelola Payment Gateway
 * dan konfirmasi pembayaran manual.
 */

import('classes.handler.Handler');
import('lib.wizdam.classes.services.InvoiceService');

class AdminPaymentHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Kunci pintu rapat-rapat: HANYA Site Admin yang boleh masuk
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn() && Validation::isSiteAdmin();
        }));
    }

    /**
     * Memuat dependensi antarmuka dan Locale
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        // Pastikan komponen bahasa dimuat (sesuaikan LOCALE_COMPONENT) 
        // Jika Wizdam Frontedge memiliki custom dictionary)
        AppLocale::requireComponents(
            [
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON, 
                LOCALE_COMPONENT_APP_PAYMENT
            ]
        );
    }

    /**
     * Menampilkan halaman Form Pengaturan Payment Gateway
     * @param array $args
     * @param Request|null $request
     */
    public function paymentSettings(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();

        if (!$request) $request = Application::get()->getRequest();

        import('lib.wizdam.classes.payment.form.PaymentSettingsForm');
        $settingsForm = new PaymentSettingsForm();
        $settingsForm->initData();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', 'Wizdam Payment Gateway Settings');
        
        $settingsForm->display();
    }

    /**
     * Memproses penyimpanan form
     * @param array $args
     * @param Request|null $request
     */
    public function savePaymentSettings(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();

        if (!$request) $request = Application::get()->getRequest();

        import('lib.wizdam.classes.payment.form.PaymentSettingsForm');
        $settingsForm = new PaymentSettingsForm();
        $settingsForm->readInputData();

        if ($settingsForm->validate()) {
            $settingsForm->execute();
            
            $request->redirect(null, 'admin', 'payment-settings', null, ['saved' => 1]);
        } else {
            // Jika ada error (misal CSRF gagal), tampilkan ulang formnya
            $settingsForm->display();
        }
    }

    /**
     * [BARU] Menampilkan daftar invoice UNPAID yang menunggu konfirmasi
     * manual (payment_method = 'ManualPending').
     * Rute: GET /admin/payment-settings/manualPayments
     * @param array $args
     * @param Request|null $request
     */
    public function manualPayments(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate();
        if (!$request) $request = Application::get()->getRequest();

        /** @var InvoiceDAO $invoiceDao */
        $invoiceDao = DAORegistry::getDAO('InvoiceDAO');
        $result = $invoiceDao->retrieve(
            "SELECT * FROM invoices WHERE status = 'UNPAID' AND payment_method = 'ManualPending' ORDER BY date_billed ASC"
        );
        $pendingInvoices = [];
        while ($result && !$result->EOF) {
            $pendingInvoices[] = $invoiceDao->_fromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }
        if ($result) $result->Close();

        import('lib.pkp.classes.validation.ValidatorCSRF');
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'pendingInvoices' => $pendingInvoices,
            'pageTitle' => 'Manual Payment Confirmations',
        ]);
        $templateMgr->display('admin/manualPayments.tpl');
    }

    /**
     * [BARU] Mengeksekusi konfirmasi: menandai invoice PAID secara resmi.
     * Rute: POST /admin/payment-settings/confirmManualPaymentAction/[invoiceId]
     * @param array $args
     * @param Request|null $request
     */
    public function confirmManualPaymentAction(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();

        import('lib.pkp.classes.validation.ValidatorCSRF');
        if (!$request->isPost() || !\ValidatorCSRF::checkToken($request->getUserVar('csrfToken'))) {
            $request->redirect(null, 'admin', 'payment-settings', 'manualPayments');
            return;
        }

        $invoiceId = (int) ($args[0] ?? 0);

        $invoiceService = new InvoiceService();
        $invoiceService->markAsPaid($invoiceId, 'ManualPayment');

        $request->redirect(null, 'admin', 'payment-settings', 'manualPayments', null, ['confirmed' => 1]);
    }
    
}
?>