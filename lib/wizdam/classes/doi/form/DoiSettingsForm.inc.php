<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/doi/form/DoiSettingsForm.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class DoiSettingsForm
 *
 * @brief Form untuk mengatur kredensial DOI (Crossref, dst) di level Admin
 * Publisher/Site -- dipakai jurnal yang TIDAK ditandai publisherPartnerships
 * (bagian dari ownership prefix DOI penerbit). Jurnal Partnership
 * mengatur kredensialnya sendiri secara terpisah (level jurnal, lihat
 * DoiCredentialService::resolveForJournal()).
 */

import('lib.pkp.classes.form.Form');
import('lib.wizdam.classes.services.DoiCredentialService');

class DoiSettingsForm extends Form {

    /** @var \DoiCredentialService $credentialService */
    private DoiCredentialService $credentialService;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/doiSettings.tpl');

        $this->credentialService = new DoiCredentialService();
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidator($this, 'crossref_depositor_name', 'required', 'admin.doi.crossrefDepositorNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'crossref_email', 'required', 'admin.doi.crossrefEmailRequired'));
    }

    /**
     * Inisialisasi data form dari Database / Config
     */
    public function initData(): void {
        $this->_data = [
            'crossref_depositor_name' => $this->credentialService->getCrossrefDepositorName(),
            'crossref_email' => $this->credentialService->getCrossrefEmail(),
            'crossref_username' => $this->credentialService->getCrossrefUsername(),
            'crossref_password' => $this->credentialService->getCrossrefPassword(),
            'crossref_automatic_registration' => $this->credentialService->getAutomaticRegistration(),
            'semantic_scholar_api_key' => $this->credentialService->getSemanticScholarApiKey(),
            'dimensions_api_key' => $this->credentialService->getDimensionsApiKey(),
        ];
    }

    /**
     * Membaca input dari POST (saat tombol Save ditekan)
     */
    public function readInputData(): void {
        $this->readUserVars([
            'crossref_depositor_name',
            'crossref_email',
            'crossref_username',
            'crossref_password',
            'crossref_automatic_registration',
            'semantic_scholar_api_key',
            'dimensions_api_key',
        ]);
        $this->setData('crossref_automatic_registration', (int) (bool) $this->getData('crossref_automatic_registration'));
    }

    /**
     * Menyimpan pengaturan ke Database (Site Settings)
     */
    public function execute($object = null): void {
        $this->credentialService->updateSetting('crossref_depositor_name', $this->getData('crossref_depositor_name'), 'string');
        $this->credentialService->updateSetting('crossref_email', $this->getData('crossref_email'), 'string');
        $this->credentialService->updateSetting('crossref_username', $this->getData('crossref_username'), 'string');
        $this->credentialService->updateSetting('crossref_password', $this->getData('crossref_password'), 'string');
        $this->credentialService->updateSetting('crossref_automatic_registration', $this->getData('crossref_automatic_registration') ? 1 : 0, 'bool');
        $this->credentialService->updateSetting('semantic_scholar_api_key', $this->getData('semantic_scholar_api_key'), 'string');
        $this->credentialService->updateSetting('dimensions_api_key', $this->getData('dimensions_api_key'), 'string');

        DoiCredentialService::syncToAllOwnershipJournals();
    }

}
?>