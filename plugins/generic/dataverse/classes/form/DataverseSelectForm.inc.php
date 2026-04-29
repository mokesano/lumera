<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/form/DataverseSelectForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseSelectForm
 * @ingroup plugins_generic_dataverse
 *
 * @brief Plugin settings: select a specific Dataverse collection via Native REST API.
 * [WIZDAM EDITION] Modernized for PHP 8.4 and Dataverse Native REST API.
 */

import('lib.pkp.classes.form.Form');

class DataverseSelectForm extends Form {

    /** @var DataversePlugin */
    public $_plugin;

    /** @var int */
    public $_journalId;

    /**
     * Constructor
     * @param $plugin DataversePlugin
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        $this->_plugin = $plugin;
        // [WIZDAM FIX] Force Integer Cast untuk PHP 8.4 Strict Types
        $this->_journalId = (int) $journalId;
        
        parent::__construct($plugin->getTemplatePath() . 'dataverseSelectForm.tpl');
        
        $this->addCheck(new FormValidator($this, 'dataverse', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.dataverseRequired'));        
        $this->addCheck(new FormValidatorPost($this));        
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataverseSelectForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($plugin, $journalId);
    }

    /**
     * Initialize form data.
     * Mengambil daftar Dataverse (sub-collections) dari Dataverse induk menggunakan REST API.
     * @see Form::initData()
     */
    public function initData() {
        $this->_plugin->import('classes.api.DataverseApiClient');
        $apiClient = new DataverseApiClient($this->_plugin);
        
        $dvnUri = (string) $this->_plugin->getSetting($this->_journalId, 'dvnUri');
        
        // Ekstrak alias Dataverse induk dari URL
        $parentAlias = '';
        if (preg_match("/.+\/(\w+)$/", $dvnUri, $matches)) {
            $parentAlias = $matches[1];
        }

        $dataverses = [];
        
        if (!empty($parentAlias)) {
            /** * [WIZDAM REST API] 
             * Memanggil endpoint /dataverses/{alias}/contents untuk mendapatkan sub-dataverses.
             * Kita perlu mengakses executeRequest. Pastikan method tersebut di DataverseApiClient sudah PUBLIC.
             */
            $endpoint = '/dataverses/' . urlencode($parentAlias) . '/contents';
            
            // Karena executeRequest di apiClient sebelumnya PRIVATE, kita asumsikan Anda telah mengubahnya ke PUBLIC
            // atau tambahkan method pembantu di apiClient. 
            // Untuk keandalan, kita panggil testConnection() dulu untuk memastikan token valid.
            if ($apiClient->testConnection($this->_journalId)) {
                // Di sini kita asumsikan apiClient memiliki akses ke endpoint contents
                // Jika belum ada di ApiClient, Anda bisa menambahkan method getCollections() di sana.
                
                // Sebagai solusi sementara yang solid, kita coba ambil data:
                $response = $this->_fetchDataverseContents($apiClient, $parentAlias);
                
                if ($response && isset($response['status']) && $response['status'] === 'OK') {
                    foreach ($response['data'] as $item) {
                        // Hanya ambil item dengan tipe 'dataverse' (koleksi)
                        if (isset($item['type']) && $item['type'] === 'dataverse') {
                            $dataverses[$item['id']] = (string) $item['title'];
                        }
                    }
                }
            }
        }

        // Tambahkan Dataverse induk sendiri ke dalam daftar pilihan
        if (!empty($parentAlias)) {
            $dataverses[$parentAlias] = __('plugins.generic.dataverse.settings.useParentDataverse') . " ($parentAlias)";
        }

        $this->setData('dataverses', $dataverses);
        
        $dataverseUri = $this->_plugin->getSetting($this->_journalId, 'dvUri');
        if (isset($dataverseUri) && array_key_exists($dataverseUri, $dataverses)) {
            $this->setData('dataverseUri', $dataverseUri);
        }              
    }

    /**
     * Helper untuk mengambil konten dataverse
     * [WIZDAM ARCHITECTURE] Ini seharusnya ada di DataverseApiClient, 
     * tapi kita taruh di sini sebagai shim jika ApiClient belum diupdate.
     */
    private function _fetchDataverseContents($apiClient, $alias) {
        // Kita gunakan Reflection atau ubah manual visibility executeRequest di ApiClient menjadi public.
        // Di sini saya asumsikan Anda sudah mengubah executeRequest di DataverseApiClient.inc.php menjadi PUBLIC.
        return $apiClient->executeRequest($this->_journalId, 'GET', '/dataverses/' . urlencode($alias) . '/contents');
    }

    /**
     * Read user input.
     */
    public function readInputData() {
        $this->readUserVars(['dataverse']);
    }

    /**
     * Save settings.
     * Menyimpan alias atau ID Dataverse yang dipilih.
     */
    public function execute($object = null) {
        $selectedDataverse = $this->getData('dataverse');
        // Simpan sebagai dvUri (Dataverse URI/Alias tujuan deposit)
        $this->_plugin->updateSetting($this->_journalId, 'dvUri', (string) $selectedDataverse, 'string');
    }
}
?>