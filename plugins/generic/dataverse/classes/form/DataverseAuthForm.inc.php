<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/form/DataverseAuthForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseAuthForm
 * @ingroup plugins_generic_dataverse
 *
 * @brief Plugin settings: connect to a Dataverse Network 
 * [WIZDAM EDITION] Modernized for PHP 8.4 and Native REST API (API Token Auth)
 */

define('DATAVERSE_PLUGIN_PASSWORD_SLUG', '********');

import('lib.pkp.classes.form.Form');

class DataverseAuthForm extends Form {

    /** @var DataversePlugin */
    public $_plugin;

    /** @var int */
    public $_journalId;

    /**
     * Constructor
     */
    public function __construct($plugin, $journalId) {
        $this->_plugin = $plugin;
        $this->_journalId = (int) $journalId;

        parent::__construct($plugin->getTemplatePath() . 'dataverseAuthForm.tpl');
        
        $this->addCheck(new FormValidatorUrl($this, 'dvnUri', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.dvnUriRequired'));
        // Username sebenarnya tidak lagi relevan di REST API (hanya butuh Token), tapi kita biarkan untuk kompatibilitas DB lama
        $this->addCheck(new FormValidator($this, 'username', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.usernameRequired'));
        
        // [WIZDAM FIX] Ubah callback ke validator REST API
        $this->addCheck(new FormValidatorCustom($this, 'dvnUri', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.dvnUriNotValid', [$this, '_testRestConnection']));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * Initialize form data from plugin settings
     */
    public function initData() {
        $plugin = $this->_plugin;

        $this->setData('dvnUri', $plugin->getSetting($this->_journalId, 'dvnUri'));         
        $this->setData('username', $plugin->getSetting($this->_journalId, 'username'));                  
        
        // Di REST API, kolom 'password' digunakan untuk menyimpan 'API Token'
        $password = $plugin->getSetting($this->_journalId, 'password');
        if (!empty($password)) {
            if ($password === DATAVERSE_PLUGIN_PASSWORD_SLUG) {
                $this->setData('password', '');
            } else {
                $this->setData('password', DATAVERSE_PLUGIN_PASSWORD_SLUG);
            }
        }
    }

    /**
     * Read user-submitted data and handle password masking
     */
    public function readInputData() {
        $this->readUserVars(['dvnUri', 'username', 'password']);
        
        $request = Registry::get('request');
        $password = $request->getUserVar('password');
        
        if ($password === DATAVERSE_PLUGIN_PASSWORD_SLUG) {
            $plugin = $this->_plugin;
            $password = $plugin->getSetting($this->_journalId, 'password');
        }
        if (!$password) {
            $password = DATAVERSE_PLUGIN_PASSWORD_SLUG;
        }
        $this->setData('password', $password);
        
        $dvnUri = rtrim((string) $this->getData('dvnUri'), '/');
        $this->setData('dvnUri', $dvnUri);
    }

    /**
     * Save form data to plugin settings
     */
    public function execute($object = null) {
        $plugin = $this->_plugin;
        
        $plugin->updateSetting($this->_journalId, 'dvnUri', (string) $this->getData('dvnUri'), 'string');
        $plugin->updateSetting($this->_journalId, 'username', (string) $this->getData('username'), 'string');
        // Simpan API Token ke kolom password
        $plugin->updateSetting($this->_journalId, 'password', (string) $this->getData('password'), 'string'); 
        $plugin->updateSetting($this->_journalId, 'apiVersion', (string) $this->getData('apiVersion'), 'string');
    }
    
    /**
     * [WIZDAM REST API] Form validator: Test Connection to Dataverse REST API
     * Menggantikan _getServiceDocument SWORD yang usang.
     * @return boolean 
     */
    public function _testRestConnection() {
        $dvnUri = (string) $this->getData('dvnUri');
        $apiToken = (string) $this->getData('password');
        
        if (empty($dvnUri)) return false;

        // Native REST API Endpoint untuk mengecek status dan versi server
        $url = $dvnUri . '/api/info/version';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Jika user typo memasukkan http alih-alih https, coba paksa https
        if ($httpCode !== 200 && strpos($dvnUri, 'http://') === 0) {
            $dvnUriSecure = str_replace('http://', 'https://', $dvnUri);
            $this->setData('dvnUri', $dvnUriSecure);
            
            $urlSecure = $dvnUriSecure . '/api/info/version';
            $chSecure = curl_init($urlSecure);
            curl_setopt($chSecure, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chSecure, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($chSecure, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($chSecure);
            $httpCode = curl_getinfo($chSecure, CURLINFO_HTTP_CODE);
            curl_close($chSecure);
        }

        if ($httpCode === 200 && $response) {
            try {
                $data = json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
                if (isset($data['status']) && $data['status'] === 'OK') {
                    // Simpan versi Dataverse secara otomatis
                    if (isset($data['data']['version'])) {
                        $this->setData('apiVersion', (string) $data['data']['version']);
                    }
                    return true;
                }
            } catch (JsonException $e) {
                error_log('WIZDAM AuthForm Error: Respons bukan JSON yang valid.');
                return false;
            }
        }
        
        return false;
    }
}
?>