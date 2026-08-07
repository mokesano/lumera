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
 * @brief Manajer Kredensial DOI.
 *
 * Scope ganda ditentukan SATU flag tunggal: journal->getSetting('publisherPartnerships')
 * -- sama persis dipakai PaymentSettingsService/PaymentAuthorityResolver,
 * sehingga payment, DOI, dan publisher (JournalSetupStep1Form) semuanya
 * ikut satu status jurnal yang sama, tidak ada lagi flag terpisah per fitur.
 *
 * ATURAN RESOLUSI:
 * 1. Kredensial Crossref (username/password/email) -- SUMBER DATANYA BEDA
 *    per scope:
 *    - Scope Publisher (Ownership, publisherPartnerships=false): setting
 *      kustom (site_settings, wizdam_doi_*) -- diisi lewat halaman admin
 *      DOI Settings.
 *    - Scope Jurnal Partnership (publisherPartnerships=true): DIBACA
 *      LANGSUNG dari plugin bawaan OJS yang SUDAH ADA -- CrossRefExportPlugin
 *      (endpoint jurnal: /manager/plugin/importexport/CrossRefExportPlugin/
 *      settings) -- BUKAN disimpan ulang dengan mekanisme sendiri.
 *    resolveForJournal(): jurnal Partnership COBA kredensial SENDIRI dulu;
 *    kalau belum diisi, FALLBACK ke kredensial Ownership/Publisher.
 *
 * 2. Sumber kutipan LAIN (OpenAlex, Semantic Scholar, Dimensions) --
 *    SELALU dari scope Publisher/Ownership, TIDAK PEDULI jurnal apapun
 *    yang meminta (termasuk jurnal Partnership) -- jurnal Partnership
 *    TIDAK punya pengaturan sendiri untuk sumber-sumber ini.
 */

class DoiCredentialService {

    /** @var object $siteSettingsDao */
    private object $siteSettingsDao;

    /** @var null|object $journalSettingsDao */
    private ?object $journalSettingsDao = null;

    /** @var int|null Jika di-set, scope Jurnal Partnership. Null = scope Publisher/Ownership (default). */
    private ?int $journalScopeId = null;

    /** @var object|null Instance CrossRefExportPlugin, dipakai HANYA untuk scope jurnal. */
    private ?object $crossrefPlugin = null;

    /**
     * Constructor
     * @param int|null $journalScopeId ID jurnal untuk scope Partnership, null untuk scope Publisher/Ownership (default)
     */
    public function __construct(?int $journalScopeId = null) {
        $this->siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        $this->journalScopeId = $journalScopeId;
        if ($journalScopeId !== null) {
            $this->journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            $this->crossrefPlugin = PluginRegistry::getPlugin('importexport', 'CrossRefExportPlugin');
        }
    }

    /**
     * Factory untuk kredensial CROSSREF: pilih scope otomatis.
     * - Jurnal Partnership (publisherPartnerships=true): coba kredensial
     *   SENDIRI dulu (dari CrossRefExportPlugin miliknya) -- kalau belum
     *   diisi, FALLBACK ke kredensial Ownership/Publisher.
     * - Jurnal Ownership (publisherPartnerships=false, default): SELALU
     *   scope Publisher, tanpa fallback ke jurnal manapun.
     * @param object|null $journal
     * @return self
     */
    public static function resolveForJournal($journal): self {
        if ($journal && (bool) $journal->getSetting('publisherPartnerships')) {
            $journalService = new self((int) $journal->getId());
            if ($journalService->isConfigured()) {
                return $journalService;
            }
            return new self(); // fallback ke Ownership/Publisher
        }
        return new self();
    }

    /**
     * Mengambil pengaturan generik dengan hierarki: DB (scope terkait) -> Config -> Default.
     * [CATATAN] HANYA dipakai untuk field yang TIDAK ada di CrossRefExportPlugin
     * (dan HANYA relevan untuk scope Publisher -- lihat catatan kelas soal
     * OpenAlex/Semantic Scholar/Dimensions yang selalu scope Publisher).
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
     * Menyimpan pengaturan generik ke scope Publisher (site-level) SELALU --
     * field non-Crossref (semantic_scholar_api_key, dimensions_api_key)
     * memang cuma ada di level Publisher, tidak pernah per-jurnal.
     * @param string $key
     * @param mixed $value
     * @param string $type
     */
    public function updateSetting(string $key, mixed $value, string $type = 'string'): void {
        $settingKey = 'wizdam_doi_' . $key;
        $this->siteSettingsDao->updateSetting($settingKey, $value, $type);
    }

    //
    // GETTER KREDENSIAL CROSSREF
    // Scope Publisher -> setting kustom (wizdam_doi_*).
    // Scope Jurnal    -> baca LANGSUNG dari CrossRefExportPlugin (sudah ada).
    //

    public function getCrossrefEmail(): string {
        if ($this->journalScopeId !== null) {
            return $this->_getCrossrefPluginSetting('depositorEmail');
        }
        return trim((string) $this->getSetting('crossref_email', ''));
    }

    public function getCrossrefUsername(): string {
        if ($this->journalScopeId !== null) {
            return $this->_getCrossrefPluginSetting('username');
        }
        return trim((string) $this->getSetting('crossref_username', ''));
    }

    public function getCrossrefPassword(): string {
        if ($this->journalScopeId !== null) {
            return $this->_getCrossrefPluginSetting('password');
        }
        return trim((string) $this->getSetting('crossref_password', ''));
    }

    private function _getCrossrefPluginSetting(string $settingName): string {
        if (!$this->crossrefPlugin) return '';
        $value = $this->crossrefPlugin->getSetting($this->journalScopeId, $settingName);
        return trim((string) ($value ?? ''));
    }

    //
    // GETTER SUMBER LAIN (OpenAlex tidak butuh kredensial; Semantic
    // Scholar & Dimensions -- API key opsional). SELALU scope Publisher,
    // TIDAK PEDULI journalScopeId instance ini -- jurnal Partnership TIDAK
    // punya pengaturan sendiri untuk sumber-sumber ini (lihat catatan kelas).
    //

    public function getSemanticScholarApiKey(): string {
        return trim((string) $this->_getPublisherOnlySetting('semantic_scholar_api_key'));
    }

    public function getDimensionsApiKey(): string {
        return trim((string) $this->_getPublisherOnlySetting('dimensions_api_key'));
    }

    private function _getPublisherOnlySetting(string $key): string {
        $settingKey = 'wizdam_doi_' . $key;
        $value = $this->siteSettingsDao->getSetting($settingKey);
        if ($value !== null && $value !== '') return (string) $value;

        $configValue = Config::getVar('wizdam_doi', $key);
        return $configValue !== null ? (string) $configValue : '';
    }

    /**
     * Kredensial minimal (username+password Crossref) sudah diisi atau belum
     * PADA SCOPE INSTANCE INI (bukan scope lain). Dipakai resolveForJournal()
     * untuk memutuskan apakah kredensial jurnal Partnership sendiri sudah
     * "siap pakai" atau masih perlu fallback ke Ownership/Publisher.
     */
    public function isConfigured(): bool {
        return $this->getCrossrefUsername() !== '' && $this->getCrossrefPassword() !== '';
    }

}
?>