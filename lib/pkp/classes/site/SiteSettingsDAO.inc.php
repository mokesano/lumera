<?php
declare(strict_types=1);

/**
 * @file classes/site/SiteSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package site
 * @class SiteSettingsDAO
 *
 * Class for Site Settings DAO.
 * Operations for retrieving and modifying site settings.
 */

import('lib.pkp.classes.xml.PKPXMLParser');

class SiteSettingsDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Legacy Constructor Shim.
     */
    public function SiteSettingsDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . ". Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get setting cache.
     * @return FileCache
     */
    public function _getCache() {
        $settingCache = Registry::get('siteSettingCache', true, null);
        if ($settingCache === null) {
            $cacheManager = CacheManager::getManager();
            $settingCache = $cacheManager->getFileCache(
                'siteSettings', 'site',
                [$this, '_cacheMiss']
            );
            Registry::set('siteSettingCache', $settingCache);
        }
        return $settingCache;
    }

    /**
     * Retrieve a site setting value.
     * @param string $name
     * @param string|null $locale optional
     * @return mixed
     */
    public function getSetting($name, $locale = null) {
        $cache = $this->_getCache();
        $returner = $cache->get($name);
        if ($locale !== null) {
            if (!is_array($returner) || !isset($returner[$locale])) {
                return null;
            }
            return $returner[$locale];
        }
        return $returner;
    }

    /**
     * Cache miss callback.
     * @param FileCache $cache
     * @param string $id
     * @return mixed
     */
    public function _cacheMiss($cache, $id) {
        $settings = $this->getSiteSettings();
        if (!isset($settings[$id])) {
            $cache->setCache($id, null);
            return null;
        }
        return $settings[$id];
    }

    /**
     * Retrieve and cache all settings for a site.
     * @return array|null
     */
    public function getSiteSettings() {
        $result = $this->retrieve('SELECT setting_name, setting_value, setting_type, locale FROM site_settings');

        if (!$result || $result->RecordCount() === 0) {
            if ($result) $result->Close();
            return null;
        }

        $siteSettings = [];
        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
            
            if ($row['locale'] === '') {
                $siteSettings[$row['setting_name']] = $value;
            } else {
                $siteSettings[$row['setting_name']][$row['locale']] = $value;
            }
            $result->MoveNext();
        }
        
        $result->Close();
        unset($result);

        $cache = $this->_getCache();
        $cache->setEntireCache($siteSettings);

        return $siteSettings;
    }

    /**
     * Add/update a site setting.
     * @param string $name
     * @param mixed $value
     * @param string|null $type data type of the setting. If omitted, type will be guessed
     * @param bool $isLocalized
     * @return bool
     */
    public function updateSetting($name, $value, $type = null, $isLocalized = false) {
        $cache = $this->_getCache();
        $cache->setCache($name, $value);

        if (!$isLocalized) {
            $value = $this->convertToDB($value, $type);
            $this->replace('site_settings',
                [
                    'setting_name' => $name,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'locale' => ''
                ],
                ['setting_name', 'locale']
            );
            return true;
        }

        if (!is_array($value) || empty($value)) {
            return false;
        }

        // 1. Hapus semua locale untuk setting_name ini dalam SATU query
        $this->update('DELETE FROM site_settings WHERE setting_name = ?', [$name]);

        // 2. Siapkan batch insert
        $insertValues = [];
        $placeholders = [];
        
        foreach ($value as $locale => $localeValue) {
            if (empty($localeValue)) continue;
            $localeType = null;
            $convertedValue = $this->convertToDB($localeValue, $localeType);
            
            $insertValues[] = $name;
            $insertValues[] = $convertedValue;
            $insertValues[] = $localeType;
            $insertValues[] = $locale;
            $placeholders[] = '(?, ?, ?, ?)';
        }

        // 3. Eksekusi batch insert dalam SATU query
        if (!empty($placeholders)) {
            $sql = 'INSERT INTO site_settings (setting_name, setting_value, setting_type, locale) VALUES ' . implode(', ', $placeholders);
            $this->update($sql, $insertValues);
        }

        return true;
    }

    /**
     * Delete a site setting.
     * @param string $name
     * @param string|null $locale optional
     * @return bool
     */
    public function deleteSetting($name, $locale = null) {
        $cache = $this->_getCache();
        $cache->setCache($name, null);

        $params = [$name];
        $sql = 'DELETE FROM site_settings WHERE setting_name = ?';
        
        if ($locale !== null) {
            $params[] = $locale;
            $sql .= ' AND locale = ?';
        }

        return $this->update($sql, $params);
    }

    /**
     * Used internally by installSettings to perform variable and translation replacements.
     * @param string $rawInput contains text including variable and/or translate replacements.
     * @param array $paramArray contains variables for replacement
     * @return string
     */
    public function _performReplacement($rawInput, $paramArray = []) {
        $value = preg_replace_callback('{{translate key="([^"]+)"}}', [$this, '_installer_regexp_callback'], $rawInput);
        
        foreach ($paramArray as $pKey => $pValue) {
            $value = str_replace('{$' . $pKey . '}', $pValue, $value);
        }
        
        return $value;
    }

    /**
     * Used internally by installSettings to recursively build nested arrays.
     * Deals with translation and variable replacement calls.
     * @param XMLNode $node <array> tag
     * @param array $paramArray Parameters to be replaced in key/value contents
     * @return array
     */
    public function _buildObject($node, $paramArray = []) {
        $value = [];
        
        foreach ($node->getChildren() as $element) {
            $key = $element->getAttribute('key');
            $childArray = $element->getChildByName('array');
            
            if (isset($childArray)) {
                $content = $this->_buildObject($childArray, $paramArray);
            } else {
                $content = $this->_performReplacement($element->getValue(), $paramArray);
            }
            
            if (!empty($key)) {
                $key = $this->_performReplacement($key, $paramArray);
                $value[$key] = $content;
            } else {
                $value[] = $content;
            }
        }
        
        return $value;
    }

    /**
     * Install site settings from an XML file.
     * @param string $filename Name of XML file to parse and install
     * @param array $paramArray Optional parameters for variable replacement in settings
     * @return bool
     */
    public function installSettings($filename, $paramArray = []) {
        $xmlParser = new PKPXMLParser();
        $tree = $xmlParser->parse($filename);

        if (!$tree) {
            $xmlParser->destroy();
            return false;
        }

        foreach ($tree->getChildren() as $setting) {
            $nameNode = $setting->getChildByName('name');
            $valueNode = $setting->getChildByName('value');

            if (isset($nameNode) && isset($valueNode)) {
                $type = $setting->getAttribute('type');
                $isLocaleField = $setting->getAttribute('locale');
                $name = $nameNode->getValue();

                if ($type === 'object') {
                    $arrayNode = $valueNode->getChildByName('array');
                    $value = $this->_buildObject($arrayNode, $paramArray);
                } else {
                    $value = $this->_performReplacement($valueNode->getValue(), $paramArray);
                }

                $this->updateSetting(
                    $name,
                    $isLocaleField ? [AppLocale::getLocale() => $value] : $value,
                    $type,
                    (bool) $isLocaleField
                );
            }
        }

        $xmlParser->destroy();
        return true;
    }

    /**
     * Used internally by site setting installation code to perform translation function.
     * @param array $matches
     * @return string
     */
    public function _installer_regexp_callback($matches) {
        return __($matches[1]);
    }
    
}
?>