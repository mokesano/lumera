<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customBlockManager/SettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup Form
 *
 * @brief Form for custom block manager settings.
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

    /** @var CustomBlockManagerPlugin */
    public $plugin;

    /** @var int */
    public $journalId;

    /**
     * Constructor
     * @param $plugin CustomBlockManagerPlugin
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->journalId = $journalId;
        $this->plugin = $plugin;
        // Validasi yang kompleks dihapus sesuai instruksi
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $plugin CustomBlockManagerPlugin
     * @param $journalId int
     */
    public function SettingsForm($plugin, $journalId) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::SettingsForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($plugin, $journalId);
    }

    /**
     * Initialize form data from the database.
     * FIX UTAMA: DATA HILANG DI FORM
     * Masalahnya: PHP cari 'CustomBlockManagerPlugin', Database simpan 'customblockmanagerplugin'.
     * Solusi: Paksa cari versi lowercase agar data KETEMU.
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;
        
        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        $pluginName = $plugin->getName();

        // 1. Coba ambil sesuai nama asli
        $blocks = $pluginSettingsDao->getSetting($journalId, $pluginName, 'blocks');

        // 2. [FIX] Jika kosong, ambil versi HURUF KECIL (Sesuai Screenshot DB Anda)
        if (is_null($blocks)) {
            $blocks = $pluginSettingsDao->getSetting($journalId, strtolower($pluginName), 'blocks');
        }

        if (!is_array($blocks)) {
            $this->setData('blocks', array());
        } else {
            $this->setData('blocks', $blocks);
        }
    }

    /**
     * Read user input.
     */
    public function readInputData() {
        $this->readUserVars(array('blocks', 'deletedBlocks'));
    }

    /**
     * Execute the form (save settings).
     * @param $object object (Optional)
     */
    public function execute($object = NULL) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;
        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');

        // 1. Hapus Blok yang ditandai dihapus
        $deletedBlocks = explode(':', $this->getData('deletedBlocks'));
        foreach ($deletedBlocks as $deletedBlock) {
            if (empty($deletedBlock)) continue;
            
            // Hapus setting spesifik milik blok tersebut (karena setiap blok menyimpan settingnya sendiri berdasarkan namanya)
            $pluginSettingsDao->deleteSetting($journalId, $deletedBlock, 'enabled');
            $pluginSettingsDao->deleteSetting($journalId, $deletedBlock, 'seq');
            $pluginSettingsDao->deleteSetting($journalId, $deletedBlock, 'context');
            $pluginSettingsDao->deleteSetting($journalId, $deletedBlock, 'blockContent');
        }

        // 2. Simpan Daftar Blok
        $blocks = $this->getData('blocks');
        if (!is_array($blocks)) $blocks = array();

        // Bersihkan input kosong
        foreach ($blocks as $key => $value) {
            if (is_null($value) || trim($value) == "") unset($blocks[$key]);
        }
        
        // Re-index array
        ksort($blocks);
        $blocks = array_values($blocks); 
        
        // Simpan daftar blok ke database plugin manager
        $plugin->updateSetting($journalId, 'blocks', $blocks);
        $this->setData('blocks', $blocks);
    }
}
?>