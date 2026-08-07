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
     * [SHIM] Legacy Constructor.
     */
    public function SiteDAO() {
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
     * Retrieve site information.
     * @return Site|null
     */
    public function getSite() {
        $result = $this->retrieve('SELECT * FROM site');
        $site = null;

        if (!$result->EOF) {
            $site = $this->_returnSiteFromRow($result->GetRowAssoc(false));
            $result->Close();

            // Query eksklusif hanya untuk 2 kolom yang dibutuhkan
            $settingsResult = $this->retrieve(
                "SELECT setting_name, setting_value, setting_type, locale 
                 FROM site_settings 
                 WHERE setting_name IN ('contactName', 'contactEmail')"
            );
                
            while (!$settingsResult->EOF) {
                $sRow = $settingsResult->GetRowAssoc(false);
                $name = $sRow['setting_name'];
                $value = $this->convertFromDB($sRow['setting_value'], $sRow['setting_type']);
                $locale = $sRow['locale'];
                    
                if ($locale === '') {
                    $site->setData($name, $value);
                } else {
                    $existingData = $site->getData($name) ?? [];
                    $existingData[$locale] = $value;
                    $site->setData($name, $existingData);
                }
                $settingsResult->MoveNext();
            }
            $settingsResult->Close();
        } else {
            $result->Close();
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
     * 
     * @param array $row
     * @param bool $callHook
     * @return Site
     */
    public function _returnSiteFromRow($row, $callHook = true) {
        $site = $this->newDataObject();
        $site->setRedirect((int) $row['redirect']);
        $site->setMinPasswordLength((int) $row['min_password_length']);
        $site->setPrimaryLocale($row['primary_locale']);
        $site->setOriginalStyleFilename($row['original_style_file_name']);
        $site->setInstalledLocales(
            isset($row['installed_locales']) && $row['installed_locales'] !== '' 
                ? explode(':', (string) $row['installed_locales']) 
                : []
        );
        $site->setSupportedLocales(
            isset($row['supported_locales']) && $row['supported_locales'] !== '' 
                ? explode(':', (string) $row['supported_locales']) 
                : []
        );

        if ($callHook) {
            HookRegistry::dispatch('SiteDAO::_returnSiteFromRow', [$site, &$row]);
        }

        return $site;
    }

    /**
     * Insert site information.
     * 
     * @param Site $site
     * @return bool
     */
    public function insertSite($site) {
        return $this->update(
            'INSERT INTO site
                (redirect, min_password_length, primary_locale, installed_locales, supported_locales, original_style_file_name)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            [
                (int) $site->getRedirect(),
                (int) $site->getMinPasswordLength(),
                $site->getPrimaryLocale(),
                implode(':', $site->getInstalledLocales()), // [WIZDAM] Modernisasi: join() -> implode()
                implode(':', $site->getSupportedLocales()),
                $site->getOriginalStyleFilename()
            ]
        );
    }

    /**
     * Update existing site information.
     * 
     * @param Site $site
     * @return bool
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
            [
                (int) $site->getRedirect(),
                (int) $site->getMinPasswordLength(),
                $site->getPrimaryLocale(),
                implode(':', $site->getInstalledLocales()), // [WIZDAM] Modernisasi: join() -> implode()
                implode(':', $site->getSupportedLocales()),
                $site->getOriginalStyleFilename()
            ]
        );
    }

    /**
     * [DEPRECATED] Legacy Update Shim.
     * Use updateObject()
     * @param Site $site
     * @return bool
     */
    public function updateSite($site) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function. Use updateObject() instead.', E_USER_DEPRECATED);
        }
        return $this->updateObject($site);
    }

}
?>