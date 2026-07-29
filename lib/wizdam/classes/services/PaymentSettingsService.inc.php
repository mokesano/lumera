<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/PaymentSettingsService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class PaymentSettingsService
 * 
 * @brief Manajer Konfigurasi Payment Gateway.
 * Hierarki: Admin UI (DB: site_settings ATAU journal_settings) > config.inc.php.
 * Scope ganda: Publisher (default, site_settings) atau Partner Journal
 * (journal_settings, HANYA jika journal->getSetting('paymentIndependent') true).
 */

class PaymentSettingsService {

    /** @var null|object $journalSettingsDao */
    private object $siteSettingsDao;

    /** @var null|object $journalSettingsDao */
    private ?object $journalSettingsDao = null;

    /** @var int|null Jika di-set, baca/tulis dari journal_settings (scope Partner). Null = scope Publisher (default). */
    private ?int $journalScopeId = null;

    /**
     * Constructor
     * @param int|null $journalScopeId ID jurnal untuk scope Partner, null untuk scope Publisher (default)
     */
    public function __construct(?int $journalScopeId = null) {
        $this->siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        $this->journalScopeId = $journalScopeId;
        if ($journalScopeId !== null) {
            $this->journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        }
    }

    /**
     * Factory: pilih scope otomatis berdasarkan status jurnal.
     * Jurnal biasa (bagian penerbit) -> scope Publisher.
     * Jurnal partnership (paymentIndependent=true) -> scope Partner (jurnal itu sendiri).
     * @param object|null $journal
     * @return self
     */
    public static function resolveForJournal($journal): self {
        if ($journal && (bool) $journal->getSetting('paymentIndependent')) {
            return new self((int) $journal->getId());
        }
        return new self();
    }

    /**
     * Mengambil pengaturan dengan hierarki: DB (scope terkait) -> Config -> Default
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed {
        $settingKey = 'wizdam_payment_' . $key;

        if ($this->journalScopeId !== null) {
            $value = $this->journalSettingsDao->getSetting($this->journalScopeId, $settingKey);
        } else {
            $value = $this->siteSettingsDao->getSetting($settingKey);
        }

        if ($value !== null && $value !== '') return $value;

        $configValue = Config::getVar('wizdam_payment', $key);
        if ($configValue !== null && $configValue !== '') return $configValue;

        return $default;
    }

    /**
     * Menyimpan pengaturan ke scope yang aktif (Site atau Journal)
     * @param string $key
     * @param mixed $value
     * @param string $type
     */
    public function updateSetting(string $key, mixed $value, string $type = 'string'): void {
        $settingKey = 'wizdam_payment_' . $key;
        if ($this->journalScopeId !== null) {
            $this->journalSettingsDao->updateSetting($this->journalScopeId, $settingKey, $value, $type);
        } else {
            $this->siteSettingsDao->updateSetting($settingKey, $value, $type);
        }
    }

    // 
    // GETTER SPECIFIC (Helpers)
    // 

    /**
     * Daftar gateway yang AKTIF (bukan lagi satu pilihan tunggal).
     * Backward-compatible: kalau 'enabled_gateways' belum pernah di-set,
     * jatuh ke getActiveGateway() lama supaya instalasi existing tidak
     * perlu migrasi apapun.
     * @return string[] mis. ['midtrans', 'xendit']
     */
    public function getEnabledGateways(): array {
        $raw = $this->getSetting('enabled_gateways', null);
        if ($raw === null) {
            return [$this->getActiveGateway()];
        }
        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        return is_array($decoded) ? array_values(array_filter($decoded)) : [$this->getActiveGateway()];
    }

    /**
     * Apakah metode Manual aktif. Default true supaya perilaku existing
     * tidak berubah tanpa konfigurasi eksplisit.
     */
    public function isManualEnabled(): bool {
        return (bool) $this->getSetting('enabled_manual', true);
    }

    /**
     * Apakah metode PayPal aktif. Default false -- baru aktif kalau
     * memang dikonfigurasi.
     */
    public function isPayPalEnabled(): bool {
        return (bool) $this->getSetting('enabled_paypal', false);
    }

    /**
     * @deprecated Dipertahankan untuk backward-compatibility fallback
     * getEnabledGateways(). Gunakan getEnabledGateways() untuk kode baru.
     */
    public function getActiveGateway(): string {
        return strtolower(trim((string) $this->getSetting('active_gateway', 'midtrans')));
    }

    /**
     * Memeriksa apakah mode produksi aktif
     * @return bool
     */
    public function isProduction(): bool {
        return (bool) $this->getSetting('is_production', false);
    }

    /**
     * Mengambil Midtrans Server Key dan Client Key, 
     * serta Xendit API Key dan Webhook Token
     * @return string
     */
    public function getMidtransServerKey(): string {
        return trim((string) $this->getSetting('midtrans_server_key', ''));
    }

    /**
     * Mengambil Midtrans Client Key
     * @return string
     */
    public function getMidtransClientKey(): string {
        return trim((string) $this->getSetting('midtrans_client_key', ''));
    }

    /**
     * Mengambil Xendit API Key
     * @return string
     */
    public function getXenditApiKey(): string {
        return trim((string) $this->getSetting('xendit_api_key', ''));
    }

    /**
     * Mengambil Xendit Webhook Token
     * @return string
     */
    public function getXenditWebhookToken(): string {
        return trim((string) $this->getSetting('xendit_webhook_token', ''));
    }

    /**
     * Email penjual PayPal (Standard Checkout 'business' parameter).
     */
    public function getPayPalSellerEmail(): string {
        return trim((string) $this->getSetting('paypal_seller_email', ''));
    }

    /**
     * Instruksi pembayaran manual (rekening bank, dst).
     */
    public function getManualInstructions(): string {
        return (string) $this->getSetting('manual_instructions', '');
    }
    
}
?>