<?php
declare(strict_types=1);

/**
 * @file pages/checkout/AdminPaymentHandler.inc.php
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * @class AdminPaymentHandler
 * @brief Handler khusus untuk Site Administrator mengelola Payment Gateway.
 */

import('classes.handler.Handler');

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
            array(
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON, 
                LOCALE_COMPONENT_APP_PAYMENT
            )
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
}
?>