<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/services/PaymentSettingsService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class PaymentSettingsService
 * @brief Manajer Konfigurasi Payment Gateway.
 * Hierarki: Admin UI (DB: site_settings) > config.inc.php.
 */

class PaymentSettingsService {

    // DAO untuk mengakses site_settings, dipanggil sekali di constructor
    private object $siteSettingsDao;

    /**
     * Constructor
     */
    public function __construct() {
        // Hanya panggil DAO, tanpa perlu mencari Site ID
        $this->siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
    }

    /**
     * Mengambil pengaturan dengan hierarki: DB -> Config -> Default
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed {
        // Parameter benar: getSetting($name, $locale = null)
        $dbValue = $this->siteSettingsDao->getSetting('wizdam_payment_' . $key);

        if ($dbValue !== null && $dbValue !== '') {
            return $dbValue;
        }

        // Fallback ke config.inc.php
        $configValue = Config::getVar('wizdam_payment', $key);

        if ($configValue !== null && $configValue !== '') {
            return $configValue;
        }

        return $default;
    }

    /**
     * Menyimpan pengaturan ke Database (Dipanggil oleh Controller Admin UI)
     * @param string $key
     * @param mixed $value
     * @param string $type (default: 'string', bisa 'bool', 'int
     * @param string $type
     */
    public function updateSetting(string $key, mixed $value, string $type = 'string'): void {
        // Parameter benar: updateSetting($name, $value, $type = null, $isLocalized = false)
        $this->siteSettingsDao->updateSetting('wizdam_payment_' . $key, $value, $type);
    }

    // ==========================================
    // GETTER SPECIFIC (Helpers)
    // ==========================================

    /**
     * Mengambil gateway aktif, default 'midtrans'
     * @return string
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
}
?>