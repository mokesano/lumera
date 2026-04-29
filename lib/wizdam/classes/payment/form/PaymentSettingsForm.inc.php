<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/payment/form/PaymentSettingsForm.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class PaymentSettingsForm
 * @brief Form untuk mengatur Payment Gateway Keys di level Admin.
 */

import('lib.pkp.classes.form.Form');
import('lib.wizdam.classes.services.PaymentSettingsService');

class PaymentSettingsForm extends Form {

    private PaymentSettingsService $settingsService;

    public function __construct() {
        // Arahkan ke template Smarty yang akan kita buat nanti
        parent::__construct('admin/paymentSettings.tpl');
        
        $this->settingsService = new PaymentSettingsService();

        // [WIZDAM SECURITY] Gunakan Validator CSRF yang kita buat sebelumnya!
        import('lib.pkp.classes.form.validation.FormValidatorCSRF');
        $this->addCheck(new FormValidatorCSRF($this));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * Override method display untuk menyuntikkan CSRF Token ke UI
     */
    public function display($request = null, $template = null): void {
        $validRequest = $request ? $request : Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($validRequest);
        
        // [WIZDAM SECURITY] Gunakan Validator CSRF
        import('lib.pkp.classes.validation.ValidatorCSRF');
        $sessionId = $validRequest->getSession()->getId();
        $templateMgr->assign('csrfToken', ValidatorCSRF::generateToken($sessionId));

        parent::display($request, $template);
    }

    /**
     * Inisialisasi data form dari Database / Config
     */
    public function initData(): void {
        $this->_data = [
            'active_gateway' => $this->settingsService->getActiveGateway(),
            'is_production' => $this->settingsService->isProduction() ? 1 : 0,
            
            'midtrans_server_key' => $this->settingsService->getMidtransServerKey(),
            'midtrans_client_key' => $this->settingsService->getMidtransClientKey(),
            
            'xendit_api_key' => $this->settingsService->getXenditApiKey(),
            'xendit_webhook_token' => $this->settingsService->getXenditWebhookToken()
        ];
    }

    /**
     * Membaca input dari POST (saat tombol Save ditekan)
     */
    public function readInputData(): void {
        $this->readUserVars([
            'active_gateway',
            'is_production',
            'midtrans_server_key',
            'midtrans_client_key',
            'xendit_api_key',
            'xendit_webhook_token'
        ]);
    }

    /**
     * Menyimpan pengaturan ke Database (Site Settings)
     */
    public function execute($object = null): void {
        $this->settingsService->updateSetting('active_gateway', $this->getData('active_gateway'), 'string');
        $this->settingsService->updateSetting('is_production', (bool) $this->getData('is_production'), 'bool');
        
        $this->settingsService->updateSetting('midtrans_server_key', $this->getData('midtrans_server_key'), 'string');
        $this->settingsService->updateSetting('midtrans_client_key', $this->getData('midtrans_client_key'), 'string');
        
        $this->settingsService->updateSetting('xendit_api_key', $this->getData('xendit_api_key'), 'string');
        $this->settingsService->updateSetting('xendit_webhook_token', $this->getData('xendit_webhook_token'), 'string');
    }
}
?>