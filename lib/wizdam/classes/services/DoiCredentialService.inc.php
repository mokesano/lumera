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
 * 1. Kredensial Crossref (username/password/email/depositorName) --
 *    SUMBER KEBENARAN-nya di scope Publisher (site_settings, wizdam_doi_*,
 *    diisi lewat halaman admin DOI Settings). TAPI kredensial itu SELALU
 *    DISALIN (sync) ke plugin_settings TIAP jurnal Ownership juga --
 *    lihat syncToJournal()/syncToAllOwnershipJournals() -- supaya baris
 *    plugin_settings milik jurnal itu TIDAK PERNAH kosong, dan kode
 *    manapun yang membaca kredensial dengan cara ASLI OJS
 *    ($plugin->getSetting($journalId, 'username')) otomatis benar TANPA
 *    perlu tahu soal DoiCredentialService sama sekali.
 *
 *    resolveForJournal() (baca lewat class ini) TETAP dipertahankan
 *    sebagai jaring pengaman KEDUA (defense in depth) -- Jurnal
 *    Partnership COBA kredensial SENDIRI dulu, fallback ke Publisher
 *    kalau belum diisi.
 *
 * 2. Sumber kutipan LAIN (OpenAlex, Semantic Scholar, Dimensions) --
 *    SELALU dari scope Publisher/Ownership, TIDAK PEDULI jurnal apapun
 *    yang meminta (termasuk jurnal Partnership) -- jurnal Partnership
 *    TIDAK punya pengaturan sendiri untuk sumber-sumber ini. Sumber ini
 *    TIDAK di-sync ke plugin_settings jurnal (tidak relevan buat plugin
 *    CrossRefExportPlugin, cuma dipakai CitationFetcherService).
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

    public function getCrossrefDepositorName(): string {
        if ($this->journalScopeId !== null) {
            return $this->_getCrossrefPluginSetting('depositorName');
        }
        return trim((string) $this->getSetting('crossref_depositor_name', ''));
    }

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

    //
    // SYNC: dorong kredensial Publisher ke plugin_settings TIAP jurnal
    // Ownership secara langsung -- supaya baris plugin_settings jurnal itu
    // BETULAN TIDAK PERNAH KOSONG, dan SEMUA kode (termasuk yang belum
    // pernah ditemukan/ditambal, plugin pihak ketiga, atau kode yang
    // ditambahkan nanti) otomatis benar tanpa perlu tahu soal
    // DoiCredentialService sama sekali. resolveForJournal() TETAP
    // dipertahankan sebagai jaring pengaman kedua (defense in depth) --
    // bukan digantikan oleh sync ini.
    //

    /**
     * Salin kredensial Crossref dari scope Publisher SAAT INI ke
     * plugin_settings milik satu jurnal tertentu (lewat CrossRefExportPlugin
     * miliknya sendiri -- BUKAN lewat penyimpanan wizdam_doi_* kustom).
     * @param int $journalId
     */
    public function syncToJournal(int $journalId): void {
        $plugin = PluginRegistry::getPlugin('importexport', 'CrossRefExportPlugin');
        if (!$plugin) return;

        // Sengaja instance BARU scope Publisher (bukan $this) -- supaya
        // method ini selalu menyalin dari sumber kebenaran Publisher,
        // terlepas dari scope instance yang memanggilnya.
        $publisherCredentials = new self();

        $plugin->updateSetting($journalId, 'username', $publisherCredentials->getCrossrefUsername(), 'string');
        $plugin->updateSetting($journalId, 'password', $publisherCredentials->getCrossrefPassword(), 'string');
        $plugin->updateSetting($journalId, 'depositorName', $publisherCredentials->getCrossrefDepositorName(), 'string');
        $plugin->updateSetting($journalId, 'depositorEmail', $publisherCredentials->getCrossrefEmail(), 'string');
    }

    /**
     * Sinkronkan kredensial Publisher ke SEMUA jurnal Ownership
     * (publisherPartnerships=false) sekaligus. Dipanggil setiap kali
     * kredensial Publisher disimpan/diperbarui (lihat DoiSettingsForm),
     * supaya perubahan langsung tersebar ke seluruh jurnal Ownership --
     * bukan cuma baru "kelihatan benar" kalau dibaca lewat
     * DoiCredentialService.
     */
    public static function syncToAllOwnershipJournals(): void {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);
        if (!$journals) return;

        $syncer = new self();
        while ($journal = $journals->next()) {
            if (!$journal->getSetting('publisherPartnerships')) {
                $syncer->syncToJournal((int) $journal->getId());
            }
        }
    }

}
?>