<?php
declare(strict_types=1);

/**
 * @file classes/journal/JournalSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSettingsDAO
 * @ingroup journal
 *
 * @brief Operations for retrieving and modifying journal settings.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Ref removal, Visibility, Type Safety)
 * - Strict Types
 */

class JournalSettingsDAO extends DAO {

    /**
     * Get the cache object for a journal.
     * @param int $journalId
     * @return FileCache
     */
    protected function _getCache($journalId) {
        static $settingCache;
        
        if (!isset($settingCache)) {
            $settingCache = array();
        }
        
        if (!isset($settingCache[$journalId])) {
            $cacheManager = CacheManager::getManager();
            $settingCache[$journalId] = $cacheManager->getFileCache(
                'journalSettings', 
                $journalId,
                array($this, '_cacheMiss')
            );
        }
        
        return $settingCache[$journalId];
    }

    /**
     * Retrieve a journal setting value.
     * @param int $journalId
     * @param string $name
     * @param string|null $locale optional
     * @return mixed
     */
    public function getSetting($journalId, $name, $locale = null) {
        $cache = $this->_getCache($journalId);
        $returner = $cache->get($name);

        // [WIZDAM FIX] Validasi Tipe Data Locale
        if ($locale !== null && is_string($locale)) {
            // PHP 8 Safety: Check if returner is strictly an array before access
            if (!is_array($returner) || !isset($returner[$locale])) {
                return null;
            }
            return $returner[$locale];
        }

        return $returner;
    }

    /**
     * Cache miss callback.
     * @param FileCache $cache (Object, no & needed in PHP 5+)
     * @param mixed $id
     * @return mixed
     */
    public function _cacheMiss($cache, $id) {
        $settings = $this->getJournalSettings($cache->getCacheId());
        
        if (!isset($settings[$id])) {
            $cache->setCache($id, null);
            return null;
        }
        
        return $settings[$id];
    }

    /**
     * Retrieve and cache all settings for a journal.
     * @param int $journalId
     * @return array
     */
    public function getJournalSettings($journalId) {
        $journalSettings = array();

        $result = $this->retrieve(
            'SELECT setting_name, setting_value, setting_type, locale FROM journal_settings WHERE journal_id = ?', 
            (int) $journalId
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
            
            if ($row['locale'] == '') {
                $journalSettings[$row['setting_name']] = $value;
            } else {
                if (!isset($journalSettings[$row['setting_name']])) {
                    $journalSettings[$row['setting_name']] = array();
                }
                $journalSettings[$row['setting_name']][$row['locale']] = $value;
            }
            $result->MoveNext();
        }
        $result->Close();
        unset($result);

        $cache = $this->_getCache($journalId);
        $cache->setEntireCache($journalSettings);

        return $journalSettings;
    }

    /**
     * Add/update a journal setting.
     * @param int $journalId
     * @param string $name
     * @param mixed $value
     * @param string|null $type data type of the setting. If omitted, type will be guessed
     * @param boolean $isLocalized
     */
    public function updateSetting($journalId, $name, $value, $type = null, $isLocalized = false) {
        $cache = $this->_getCache($journalId);
        $cache->setCache($name, $value);

        $keyFields = array('setting_name', 'locale', 'journal_id');

        if (!$isLocalized) {
            $value = $this->convertToDB($value, $type);
            $this->replace('journal_settings',
                array(
                    'journal_id' => (int) $journalId,
                    'setting_name' => $name,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'locale' => ''
                ),
                $keyFields
            );
        } else {
            if (is_array($value)) {
                foreach ($value as $locale => $localeValue) {
                    $this->update(
                        'DELETE FROM journal_settings WHERE journal_id = ? AND setting_name = ? AND locale = ?', 
                        array((int) $journalId, $name, $locale)
                    );
                    
                    if (empty($localeValue)) continue;
                    
                    $type = null;
                    $this->update(
                        'INSERT INTO journal_settings
                        (journal_id, setting_name, setting_value, setting_type, locale)
                        VALUES (?, ?, ?, ?, ?)',
                        array(
                            (int) $journalId, 
                            $name, 
                            $this->convertToDB($localeValue, $type), 
                            $type, 
                            $locale
                        )
                    );
                }
            }
        }
    }

    /**
     * Delete a journal setting.
     * @param int $journalId
     * @param string $name
     * @param string|null $locale
     * @return boolean
     */
    public function deleteSetting($journalId, $name, $locale = null) {
        $cache = $this->_getCache($journalId);
        $cache->setCache($name, null);

        $params = array((int) $journalId, $name);
        $sql = 'DELETE FROM journal_settings WHERE journal_id = ? AND setting_name = ?';
        
        if ($locale !== null) {
            $params[] = $locale;
            $sql .= ' AND locale = ?';
        }

        return $this->update($sql, $params);
    }

    /**
     * Delete all settings for a journal.
     * @param int $journalId
     * @return boolean
     */
    public function deleteSettingsByJournal($journalId) {
        $cache = $this->_getCache($journalId);
        $cache->flush();

        return $this->update(
            'DELETE FROM journal_settings WHERE journal_id = ?', 
            (int) $journalId
        );
    }

    /**
     * Used internally by installSettings to perform variable and translation replacements.
     * @param string $rawInput
     * @param array $paramArray
     * @return string
     */
    protected function _performReplacement($rawInput, $paramArray = array()) {
        // Guideline #2 & #5: Removed & from $this, ensure string casting if needed
        $value = preg_replace_callback(
            '{{translate key="([^"]+)"}}', 
            array($this, '_installer_regexp_callback'), 
            (string) $rawInput
        );

        foreach ($paramArray as $pKey => $pValue) {
            $value = str_replace('{$' . $pKey . '}', (string) $pValue, $value);
        }
        return $value;
    }

    /**
     * Used internally by installSettings to recursively build nested arrays.
     * @param XMLNode $node (Object, no & needed)
     * @param array $paramArray
     * @return array
     */
    protected function _buildObject($node, $paramArray = array()) {
        $value = array();
        // $node is object, pass by handle default in PHP 5+
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
     * Install journal settings from an XML file.
     * @param int $journalId
     * @param string $filename
     * @param array $paramArray
     * @return boolean|null
     */
    public function installSettings($journalId, $filename, $paramArray = array()) {
        $xmlParser = new XMLParser();
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

                if ($type == 'object') {
                    $arrayNode = $valueNode->getChildByName('array');
                    $value = $this->_buildObject($arrayNode, $paramArray);
                } else {
                    $value = $this->_performReplacement($valueNode->getValue(), $paramArray);
                }

                // Replace translate calls with translated content
                $this->updateSetting(
                    $journalId,
                    $name,
                    $isLocaleField ? array(AppLocale::getLocale() => $value) : $value,
                    $type,
                    $isLocaleField
                );
            }
        }

        $xmlParser->destroy();
    }

    /**
     * Used internally by reloadLocalizedSettingDefaults.
     * @param string $rawInput
     * @param array $paramArray
     * @param string|null $locale
     * @return string
     */
    protected function _performLocalizedReplacement($rawInput, $paramArray = array(), $locale = null) {
        preg_match('{{translate key="([^"]+)"}}', (string) $rawInput, $matches);
        
        if (isset($matches[1])) {
            AppLocale::requireComponents(LOCALE_COMPONENT_OJS_DEFAULT, LOCALE_COMPONENT_OJS_MANAGER, $locale);
            return __($matches[1], $paramArray, $locale);
        }

        return $rawInput;
    }

    /**
     * Used internally by reloadLocalizedSettingDefaults.
     * @param XMLNode $node (Object, no & needed)
     * @param array $paramArray
     * @param string|null $locale
     * @return array
     */
    protected function _buildLocalizedObject($node, $paramArray = array(), $locale = null) {
        $value = array();
        foreach ($node->getChildren() as $element) {
            $key = $element->getAttribute('key');
            $childArray = $element->getChildByName('array');
            
            if (isset($childArray)) {
                $content = $this->_buildLocalizedObject($childArray, $paramArray, $locale);
            } else {
                $content = $this->_performLocalizedReplacement($element->getValue(), $paramArray, $locale);
            }
            
            if (!empty($key)) {
                $key = $this->_performLocalizedReplacement($key, $paramArray, $locale);
                $value[$key] = $content;
            } else {
                $value[] = $content;
            }
        }
        return $value;
    }

    /**
     * Install locale field Only journal settings from an XML file.
     * @param int $journalId
     * @param string $filename
     * @param array $paramArray
     * @param string $locale
     * @return boolean|null
     */
    public function reloadLocalizedDefaultSettings($journalId, $filename, $paramArray, $locale) {
        $xmlParser = new XMLParser();
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

                // Skip all settings that are not locale fields
                if (!$isLocaleField) continue;

                if ($type == 'object') {
                    $arrayNode = $valueNode->getChildByName('array');
                    $value = $this->_buildLocalizedObject($arrayNode, $paramArray, $locale);
                } else {
                    $value = $this->_performLocalizedReplacement($valueNode->getValue(), $paramArray, $locale);
                }

                // Replace translate calls with translated content
                $this->updateSetting(
                    $journalId,
                    $name,
                    array($locale => $value),
                    $type,
                    true
                );
            }
        }

        $xmlParser->destroy();
    }

    /**
     * Used internally by journal setting installation code to perform translation function.
     * @param array $matches
     * @return string
     */
    public function _installer_regexp_callback($matches) {
        return __($matches[1]);
    }
}
?>