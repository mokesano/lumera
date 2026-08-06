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
 * 
 * @brief Form untuk mengatur Payment Gateway Keys di level Admin -- termasuk
 * toggle Manual/PayPal, daftar gateway aktif, dan daftar rekening bank
 * (multi-rekening) untuk instruksi transfer manual.
 */

import('lib.pkp.classes.form.Form');
import('lib.wizdam.classes.services.PaymentSettingsService');

class PaymentSettingsForm extends Form {

    /** @var \PaymentSettingsService $settingsService */
    private PaymentSettingsService $settingsService;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/paymentSettings.tpl');
        
        $this->settingsService = new PaymentSettingsService();
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * csrfToken TIDAK di-generate ulang di sini -- sudah tersedia global
     * di setiap template lewat PKPTemplateManager (konteks 'global').
     */
    public function display($request = null, $template = null): void {
        parent::display($request, $template);
    }

    /**
     * Inisialisasi data form dari Database / Config
     */
    public function initData(): void {
        $this->_data = [
            'active_gateway' => $this->settingsService->getActiveGateway(),
            'is_production' => $this->settingsService->isProduction() ? 1 : 0,

            'enabled_manual' => $this->settingsService->isManualEnabled() ? 1 : 0,
            'enabled_paypal' => $this->settingsService->isPayPalEnabled() ? 1 : 0,
            'enabled_midtrans' => in_array('midtrans', $this->settingsService->getEnabledGateways(), true) ? 1 : 0,
            'enabled_xendit' => in_array('xendit', $this->settingsService->getEnabledGateways(), true) ? 1 : 0,

            'paypal_seller_email' => $this->settingsService->getPayPalSellerEmail(),
            'bank_accounts' => $this->settingsService->getBankAccounts(),

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
            'enabled_manual',
            'enabled_paypal',
            'enabled_midtrans',
            'enabled_xendit',
            'paypal_seller_email',
            'bank_name',
            'account_number',
            'account_holder',
            'bank_branch',
            'bank_notes',
            'midtrans_server_key',
            'midtrans_client_key',
            'xendit_api_key',
            'xendit_webhook_token'
        ]);

        $this->setData('bank_accounts', $this->_buildBankAccountsFromInput());
    }

    /**
     * Merakit array rekening bank terstruktur dari field array paralel hasil
     * form repeatable (bank_name[], account_number[], dst). Dipakai bersama
     * oleh readInputData() (redisplay saat error) dan execute() (simpan).
     * @return array setiap elemen: ['bankName','accountNumber','accountHolder','branch','notes']
     */
    private function _buildBankAccountsFromInput(): array {
        $bankNames = (array) $this->getData('bank_name');
        $accountNumbers = (array) $this->getData('account_number');
        $accountHolders = (array) $this->getData('account_holder');
        $bankBranches = (array) $this->getData('bank_branch');
        $bankNotes = (array) $this->getData('bank_notes');

        $bankAccounts = [];
        $rowCount = count($bankNames);
        for ($i = 0; $i < $rowCount; $i++) {
            $bankName = trim((string) ($bankNames[$i] ?? ''));
            $accountNumber = trim((string) ($accountNumbers[$i] ?? ''));

            if ($bankName === '' && $accountNumber === '') continue;

            $bankAccounts[] = [
                'bankName' => $bankName,
                'accountNumber' => $accountNumber,
                'accountHolder' => trim((string) ($accountHolders[$i] ?? '')),
                'branch' => trim((string) ($bankBranches[$i] ?? '')),
                'notes' => trim((string) ($bankNotes[$i] ?? '')),
            ];
        }
        return $bankAccounts;
    }

    /**
     * Menyimpan pengaturan ke Database (Site Settings)
     */
    public function execute($object = null): void {
        $this->settingsService->updateSetting('active_gateway', $this->getData('active_gateway'), 'string');
        $this->settingsService->updateSetting('is_production', (bool) $this->getData('is_production'), 'bool');

        $this->settingsService->updateSetting('enabled_manual', (bool) $this->getData('enabled_manual'), 'bool');
        $this->settingsService->updateSetting('enabled_paypal', (bool) $this->getData('enabled_paypal'), 'bool');

        $enabledGateways = [];
        if ($this->getData('enabled_midtrans')) $enabledGateways[] = 'midtrans';
        if ($this->getData('enabled_xendit')) $enabledGateways[] = 'xendit';
        $this->settingsService->updateSetting('enabled_gateways', json_encode($enabledGateways), 'string');

        $this->settingsService->updateSetting('paypal_seller_email', $this->getData('paypal_seller_email'), 'string');

        $bankAccounts = $this->_buildBankAccountsFromInput();
        $this->settingsService->updateSetting('bank_accounts', json_encode($bankAccounts), 'string');
        
        $this->settingsService->updateSetting('midtrans_server_key', $this->getData('midtrans_server_key'), 'string');
        $this->settingsService->updateSetting('midtrans_client_key', $this->getData('midtrans_client_key'), 'string');
        
        $this->settingsService->updateSetting('xendit_api_key', $this->getData('xendit_api_key'), 'string');
        $this->settingsService->updateSetting('xendit_webhook_token', $this->getData('xendit_webhook_token'), 'string');
    }
    
}
?>