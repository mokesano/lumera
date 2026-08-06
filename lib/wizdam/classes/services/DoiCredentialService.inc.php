<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/DoiCredentialService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class DoiCredentialService
 *
 * @brief Manajer Kredensial DOI (Crossref/OpenAlex/Semantic Scholar/dst).
 * Hierarki: Admin UI (DB: site_settings ATAU journal_settings) > config.inc.php.
 * Scope ganda: Publisher (default, site_settings) atau Jurnal Independent
 * (journal_settings, HANYA jika journal->getSetting('doiIndependent') true).
 *
 * DOI adalah prefix milik penerbit -- jurnal yang bukan bagian dari
 * ownership penerbit (doiIndependent=true) TIDAK BOLEH memakai kredensial
 * DOI penerbit, dan wajib punya kredensial sendiri. Konsep ini sejalan
 * dengan paymentIndependent (lihat PaymentSettingsService), tapi sengaja
 * dipisah sebagai flag tersendiri karena status "independent" untuk
 * pembayaran dan untuk DOI bisa berbeda per jurnal.
 */

class DoiCredentialService {

    /** @var object $siteSettingsDao */
    private object $siteSettingsDao;

    /** @var null|object $journalSettingsDao */
    private ?object $journalSettingsDao = null;

    /** @var int|null Jika di-set, baca/tulis dari journal_settings (scope jurnal independent). Null = scope Publisher (default). */
    private ?int $journalScopeId = null;

    /**
     * Constructor
     * @param int|null $journalScopeId ID jurnal untuk scope independent, null untuk scope Publisher (default)
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
     * - Jurnal independent (doiIndependent=true): SELALU scope jurnal itu sendiri
     *   (DOI publisher tidak boleh dipakai jurnal yang bukan bagian ownership-nya).
     * - Jurnal bagian ownership publisher (doiIndependent=false, default): scope
     *   Publisher (site-level) -- TAPI kalau site-level belum pernah diisi sama
     *   sekali, jatuh ke kredensial jurnal itu sendiri supaya jurnal yang sudah
     *   berjalan dengan kredensial lamanya tidak tiba-tiba putus.
     * @param object|null $journal
     * @return self
     */
    public static function resolveForJournal($journal): self {
        if ($journal && (bool) $journal->getSetting('doiIndependent')) {
            return new self((int) $journal->getId());
        }

        $siteService = new self();
        if ($siteService->isConfigured()) {
            return $siteService;
        }
        if ($journal) {
            return new self((int) $journal->getId());
        }
        return $siteService;
    }

    /**
     * Mengambil pengaturan dengan hierarki: DB (scope terkait) -> Config -> Default
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed {
        $settingKey = 'wizdam_doi_' . $key;

        if ($this->journalScopeId !== null) {
            $value = $this->journalSettingsDao->getSetting($this->journalScopeId, $settingKey);
        } else {
            $value = $this->siteSettingsDao->getSetting($settingKey);
        }

        if ($value !== null && $value !== '') return $value;

        $configValue = Config::getVar('wizdam_doi', $key);
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
        $settingKey = 'wizdam_doi_' . $key;
        if ($this->journalScopeId !== null) {
            $this->journalSettingsDao->updateSetting($this->journalScopeId, $settingKey, $value, $type);
        } else {
            $this->siteSettingsDao->updateSetting($settingKey, $value, $type);
        }
    }

    //
    // GETTER SPESIFIK (Helpers)
    //

    public function getCrossrefEmail(): string {
        return trim((string) $this->getSetting('crossref_email', ''));
    }

    public function getCrossrefUsername(): string {
        return trim((string) $this->getSetting('crossref_username', ''));
    }

    public function getCrossrefPassword(): string {
        return trim((string) $this->getSetting('crossref_password', ''));
    }

    public function getSemanticScholarApiKey(): string {
        return trim((string) $this->getSetting('semantic_scholar_api_key', ''));
    }

    public function getDimensionsApiKey(): string {
        return trim((string) $this->getSetting('dimensions_api_key', ''));
    }

    /**
     * Kredensial minimal (username+password Crossref) sudah diisi atau belum.
     * Dipakai resolveForJournal() untuk memutuskan apakah scope Publisher
     * sudah "siap pakai" atau masih perlu fallback ke scope jurnal.
     */
    public function isConfigured(): bool {
        return $this->getCrossrefUsername() !== '' && $this->getCrossrefPassword() !== '';
    }

}
?>