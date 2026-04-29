<?php
declare(strict_types=1);

/**
 * @file classes/site/SiteDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteDAO
 * @ingroup site
 * @see Site
 *
 * @brief Operations for retrieving and modifying the Site object.
 */

import('lib.pkp.classes.site.Site');

class SiteDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Legacy Constructor Shim.
     */
    public function SiteDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::SiteDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve site information.
     * @return Site
     */
    public function getSite() {
        $site = null;
        $result = $this->retrieve(
            'SELECT * FROM site'
        );

        if ($result->RecordCount() != 0) {
            $site = $this->_returnSiteFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);
        
        if ($site !== null) {
            // Query eksklusif hanya untuk 2 kolom yang dibutuhkan
            $settingsResult = $this->retrieve(
                "SELECT setting_name, setting_value, setting_type, locale 
                 FROM site_settings 
                 WHERE setting_name IN ('contactName', 'contactEmail')"
            );
                
            while (!$settingsResult->EOF) {
                $sRow = $settingsResult->GetRowAssoc(false);
                $name = $sRow['setting_name'];
                // Gunakan fungsi konversi bawaan DAO induk
                $value = $this->convertFromDB($sRow['setting_value'], $sRow['setting_type']);
                $locale = $sRow['locale'];
                    
                if ($locale == '') {
                    $site->setData($name, $value);
                } else {
                    $existingData = $site->getData($name) ? $site->getData($name) : [];
                    $existingData[$locale] = $value;
                    $site->setData($name, $existingData);
                }
                $settingsResult->MoveNext();
            }
            $settingsResult->Close();
        }

        return $site;
    }

    /**
     * Instantiate and return a new DataObject.
     * @return Site
     */
    public function newDataObject() {
        return new Site();
    }

    /**
     * Internal function to return a Site object from a row.
     * @param $row array
     * @param $callHook boolean
     * @return Site
     */
    public function _returnSiteFromRow($row, $callHook = true) {
        $site = $this->newDataObject();
        $site->setRedirect($row['redirect']);
        $site->setMinPasswordLength($row['min_password_length']);
        $site->setPrimaryLocale($row['primary_locale']);
        $site->setOriginalStyleFilename($row['original_style_file_name']);
        $site->setInstalledLocales(isset($row['installed_locales']) && !empty($row['installed_locales']) ? explode(':', $row['installed_locales']) : array());
        $site->setSupportedLocales(isset($row['supported_locales']) && !empty($row['supported_locales']) ? explode(':', $row['supported_locales']) : array());

        // MODERN HOOK: Using dispatch() and NO references for objects
        if ($callHook) HookRegistry::dispatch('SiteDAO::_returnSiteFromRow', array($site, $row));

        return $site;
    }

    /**
     * Insert site information.
     * @param $site Site
     */
    public function insertSite($site) {
        $returner = $this->update(
            'INSERT INTO site
                (redirect, min_password_length, primary_locale, installed_locales, supported_locales, original_style_file_name)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            array(
                $site->getRedirect(),
                (int) $site->getMinPasswordLength(),
                $site->getPrimaryLocale(),
                join(':', $site->getInstalledLocales()),
                join(':', $site->getSupportedLocales()),
                $site->getOriginalStyleFilename()
            )
        );
        return $returner;
    }

    /**
     * Update existing site information.
     * @param $site Site
     */
    public function updateObject($site) {
        return $this->update(
            'UPDATE site
                SET
                    redirect = ?,
                    min_password_length = ?,
                    primary_locale = ?,
                    installed_locales = ?,
                    supported_locales = ?,
                    original_style_file_name = ?',
            array(
                $site->getRedirect(),
                (int) $site->getMinPasswordLength(),
                $site->getPrimaryLocale(),
                join(':', $site->getInstalledLocales()),
                join(':', $site->getSupportedLocales()),
                $site->getOriginalStyleFilename()
            )
        );
    }

    public function updateSite($site) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($site);
    }
}

?>