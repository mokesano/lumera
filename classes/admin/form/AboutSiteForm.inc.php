<?php
declare(strict_types=1);

/**
 * @file classes/admin/form/AboutSiteForm.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class AboutSiteForm
 * @ingroup admin_form
 * 
 * @brief Form untuk mengatur "About Site" -- narasi (Mission/History/dst)
 * SEKALIGUS identitas resmi Penerbit (nama, motto, tagline, legalitas,
 * alamat, logo, warna brand) yang dipakai ulang oleh Invoice/LoA/Sertifikat
 * dan halaman root Penerbit -- satu sumber data, bukan hardcode berulang.
 */

import('lib.pkp.classes.form.Form');

class AboutSiteForm extends Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/aboutSite.tpl');
        $this->addCheck(new FormValidatorPost($this));

        // [BARU] Validasi ringan untuk warna brand -- harus format hex valid
        // kalau diisi, supaya tidak menyimpan sampah yang bikin CSS rusak.
        $this->addCheck(new FormValidatorCustom(
            $this, 'publisherColorPrimary', 'optional', 'admin.siteSettings.error.invalidColor',
            function ($color) { return empty($color) || preg_match('/^#[0-9A-Fa-f]{6}$/', $color); }
        ));
        $this->addCheck(new FormValidatorCustom(
            $this, 'publisherColorSecondary', 'optional', 'admin.siteSettings.error.invalidColor',
            function ($color) { return empty($color) || preg_match('/^#[0-9A-Fa-f]{6}$/', $color); }
        ));
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function AboutSiteForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Initialize form data from site settings.
     */
    public function initData() {
        $request = Application::get()->getRequest();
        $site = $request->getSite();
        
        if ($site) {
            $this->setData('publisherMission', $site->getSetting('publisherMission') ?? []);
            $this->setData('publisherHistory', $site->getSetting('publisherHistory') ?? []);
            $this->setData('publisherLeaderships', $site->getSetting('publisherLeaderships') ?? []);
            $this->setData('publisherAwards', $site->getSetting('publisherAwards') ?? []);

            // [BARU] Identitas resmi Penerbit
            $this->setData('publisherName', $site->getSetting('publisherName') ?? '');
            $this->setData('publisherMotto', $site->getSetting('publisherMotto') ?? []);
            $this->setData('publisherTagline', $site->getSetting('publisherTagline') ?? []);
            $this->setData('publisherLegalEntity', $site->getSetting('publisherLegalEntity') ?? '');
            $this->setData('publisherAddress', $site->getSetting('publisherAddress') ?? '');
            $this->setData('publisherColorPrimary', $site->getSetting('publisherColorPrimary') ?? '#1a4f8b');
            $this->setData('publisherColorSecondary', $site->getSetting('publisherColorSecondary') ?? '#28a745');
            $this->setData('publisherLogo', $site->getSetting('publisherLogo') ?? null);
        }
    }

    /**
     * Read user input (kecuali logo -- itu ditangani terpisah lewat
     * uploadPublisherLogo(), karena berasal dari $_FILES, bukan $_POST).
     */
    public function readInputData() {
        $this->readUserVars([
            'publisherMission', 
            'publisherHistory', 
            'publisherLeaderships', 
            'publisherAwards',
            'publisherName',
            'publisherMotto',
            'publisherTagline',
            'publisherLegalEntity',
            'publisherAddress',
            'publisherColorPrimary',
            'publisherColorSecondary',
        ]);
    }

    /**
     * Validate action
     */
    public function validate($callHooks = true) {
        return parent::validate($callHooks);
    }

    /**
     * [BARU] Menangani unggah logo Penerbit. Dipanggil terpisah oleh
     * AdminHandler::saveAboutSite() SEBELUM execute(), pola identik dengan
     * ProfileForm::uploadProfileImage() yang sudah ada untuk foto profil.
     * @return bool
     */
    public function uploadPublisherLogo(): bool {
        import('classes.file.PublicFileManager');
        $fileManager = new PublicFileManager();

        $type = $fileManager->getUploadedFileType('publisherLogo');
        if (!$type) return true; // Tidak ada file diunggah -- bukan error, cuma dilewati.

        $extension = $fileManager->getImageExtension($type);
        if (!$extension) return false;

        $uploadName = 'publisher-logo-' . time() . $extension;

        // Hapus logo lama supaya tidak menumpuk file yatim
        $request = Application::get()->getRequest();
        $site = $request->getSite();
        $oldLogo = $site ? $site->getSetting('publisherLogo') : null;
        if (is_array($oldLogo) && !empty($oldLogo['uploadName'])) {
            $fileManager->removeSiteFile((string) $oldLogo['uploadName']);
        }

        if (!$fileManager->uploadSiteFile('publisherLogo', $uploadName)) return false;

        $filePath = $fileManager->getSiteFilesPath() . '/' . $uploadName;
        if (!file_exists($filePath)) return false;

        $imageSize = @getimagesize($filePath);
        if ($imageSize === false) {
            $fileManager->removeSiteFile($uploadName);
            return false;
        }

        $this->setData('publisherLogo', [
            'uploadName' => $uploadName,
            'width' => (int) $imageSize[0],
            'height' => (int) $imageSize[1],
            'dateUploaded' => Core::getCurrentDate(),
        ]);

        return true;
    }

    /**
     * Save settings.
     * @param mixed $functionArgs
     * @return bool
     */
    public function execute(...$functionArgs) {
        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        
        if (!$siteSettingsDao) {
            return false;
        }

        $siteSettingsDao->updateSetting('publisherMission', $this->getData('publisherMission'), null, true);
        $siteSettingsDao->updateSetting('publisherHistory', $this->getData('publisherHistory'), null, true);
        $siteSettingsDao->updateSetting('publisherLeaderships', $this->getData('publisherLeaderships'), null, true);
        $siteSettingsDao->updateSetting('publisherAwards', $this->getData('publisherAwards'), null, true);

        // [BARU]
        $siteSettingsDao->updateSetting('publisherName', $this->getData('publisherName'), 'string', false);

        // BAU BUSUK LOGIC
        // [WIZDAM] publisherName di sini adalah SUMBER KEBENARAN untuk
        // publisherInstitution seluruh jurnal Ownership (lihat
        // JournalSetupStep1Form). Setiap kali Publisher ganti nama, sebar
        // LANGSUNG ke journal_settings tiap jurnal Ownership -- supaya XML
        // export (Crossref/mEDRA/DataCite/dst) yang membaca
        // $journal->getSetting('publisherInstitution') selalu dapat nama
        // terbaru, tanpa menunggu tiap journal manager kebetulan membuka
        // & menyimpan ulang Setup > Step 1 miliknya masing-masing.
        $this->_syncPublisherInstitutionToOwnershipJournals($this->getData('publisherName'));
        $siteSettingsDao->updateSetting('publisherMotto', $this->getData('publisherMotto'), null, true);
        $siteSettingsDao->updateSetting('publisherTagline', $this->getData('publisherTagline'), null, true);
        $siteSettingsDao->updateSetting('publisherLegalEntity', $this->getData('publisherLegalEntity'), 'string', false);
        $siteSettingsDao->updateSetting('publisherAddress', $this->getData('publisherAddress'), 'string', false);
        $siteSettingsDao->updateSetting('publisherColorPrimary', $this->getData('publisherColorPrimary'), 'string', false);
        $siteSettingsDao->updateSetting('publisherColorSecondary', $this->getData('publisherColorSecondary'), 'string', false);

        // Logo HANYA ditulis kalau memang ada unggahan baru (setData sudah
        // dipanggil oleh uploadPublisherLogo() sebelum execute() ini jalan).
        if ($this->getData('publisherLogo') !== null) {
            $siteSettingsDao->updateSetting('publisherLogo', $this->getData('publisherLogo'), 'object', false);
        }
        
        return true;
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        $currentLogo = $this->getData('publisherLogo');
        $logoUrl = '';
        if (is_array($currentLogo) && !empty($currentLogo['uploadName'])) {
            import('classes.file.PublicFileManager');
            $fileManager = new PublicFileManager();
            $logoUrl = $request->getBaseUrl() . '/' . $fileManager->getSiteFilesPath() . '/' . (string) $currentLogo['uploadName'];
        }
        
        $templateMgr->assign([
            'pageTitle' => 'admin.aboutSiteSettings',
            'publisherMissionKey' => 'admin.siteSettings.publisherMission',
            'publisherHistoryKey' => 'admin.siteSettings.publisherHistory',
            'publisherLeadershipsKey' => 'admin.siteSettings.publisherLeaderships',
            'publisherAwardsKey' => 'admin.siteSettings.publisherAwards',

            'publisherMission' => $this->getData('publisherMission') ?? [],
            'publisherHistory' => $this->getData('publisherHistory') ?? [],
            'publisherLeaderships' => $this->getData('publisherLeaderships') ?? [],
            'publisherAwards' => $this->getData('publisherAwards') ?? [],

            // [BARU]
            'publisherName' => $this->getData('publisherName') ?? '',
            'publisherMotto' => $this->getData('publisherMotto') ?? [],
            'publisherTagline' => $this->getData('publisherTagline') ?? [],
            'publisherLegalEntity' => $this->getData('publisherLegalEntity') ?? '',
            'publisherAddress' => $this->getData('publisherAddress') ?? '',
            'publisherColorPrimary' => $this->getData('publisherColorPrimary') ?? '#1a4f8b',
            'publisherColorSecondary' => $this->getData('publisherColorSecondary') ?? '#28a745',
            'currentLogoUrl' => $logoUrl,
        ]);
        
        parent::display($request, $template);
    }

    /**
     * Sebar nama Publisher terbaru ke journal_settings.publisherInstitution
     * SEMUA jurnal Ownership (publisherPartnerships=false) sekaligus.
     * @param string $publisherName
     */
    private function _syncPublisherInstitutionToOwnershipJournals(string $publisherName): void {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);
        if (!$journals) return;

        while ($journal = $journals->next()) {
            if (!$journal->getSetting('publisherPartnerships')) {
                $journal->updateSetting('publisherInstitution', $publisherName, 'string');
            }
        }
    }

}
?>