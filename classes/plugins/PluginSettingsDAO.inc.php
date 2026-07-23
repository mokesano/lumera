<?php
declare(strict_types=1);

/**
 * @file classes/plugins/PluginSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginSettingsDAO
 * @ingroup plugins
 * @see Plugin
 *
 * @brief Operations for retrieving and modifying plugin settings.
 */

class PluginSettingsDAO extends DAO {
    
    /**
     * Get the cache for a specific plugin setting.
     * @param int $journalId
     * @param string $pluginName
     * @return FileCache
     */
    protected function _getCache($journalId, $pluginName) {
        static $settingCache;

        if (!isset($settingCache)) {
            $settingCache = [];
        }
        if (!isset($settingCache[$journalId])) {
            $settingCache[$journalId] = [];
        }
        if (!isset($settingCache[$journalId][$pluginName])) {
            $cacheManager = CacheManager::getManager();
            $settingCache[$journalId][$pluginName] = $cacheManager->getFileCache(
                'pluginSettings-' . $journalId, 
                $pluginName,
                [$this, '_cacheMiss']
            );
        }
        return $settingCache[$journalId][$pluginName];
    }

    /**
     * Retrieve a plugin setting value.
     * @param int $journalId
     * @param string $pluginName
     * @param string $name
     * @return mixed
     */
    public function getSetting($journalId, $pluginName, $name) {
        $pluginName = strtolower_codesafe((string) $pluginName);

        // Retrieve the setting.
        $cache = $this->_getCache($journalId, $pluginName);
        return $cache->get($name);
    }

    /**
     * Cache miss callback.
     * @param FileCache $cache
     * @param string $id
     * @return mixed
     */
    public function _cacheMiss($cache, $id) {
        $contextParts = explode('-', $cache->getContext());
        $journalId = (int) array_pop($contextParts);
        $settings = $this->getPluginSettings($journalId, $cache->getCacheId());
        
        if (!isset($settings[$id])) {
            // Make sure that even null values are cached
            $cache->setCache($id, null);
            return null;
        }
        return $settings[$id];
    }

    /**
     * Retrieve and cache all settings for a plugin.
     * @param int $journalId
     * @param string $pluginName
     * @return array
     */
    public function getPluginSettings($journalId, $pluginName) {
        $pluginName = strtolower_codesafe((string) $pluginName);

        $result = $this->retrieve(
            'SELECT setting_name, setting_value, setting_type FROM plugin_settings WHERE plugin_name = ? AND journal_id = ?', 
            [(string) $pluginName, (int) $journalId]
        );

        $pluginSettings = [];
        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $pluginSettings[$row['setting_name']] = $this->convertFromDB($row['setting_value'], $row['setting_type']);
            $result->MoveNext();
        }
        $result->Close();

        // Update the cache.
        $cache = $this->_getCache($journalId, $pluginName);
        $cache->setEntireCache($pluginSettings);

        return $pluginSettings;
    }

    /**
     * Add/update a plugin setting.
     * @param int $journalId
     * @param string $pluginName
     * @param string $name
     * @param mixed $value
     * @param string|null $type data type of the setting.
     * @return bool
     */
    public function updateSetting($journalId, $pluginName, $name, $value, $type = null) {
        $pluginName = strtolower_codesafe((string) $pluginName);

        $cache = $this->_getCache($journalId, $pluginName);
        $cache->setCache($name, $value);

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM plugin_settings WHERE plugin_name = ? AND setting_name = ? AND journal_id = ?',
            [(string) $pluginName, (string) $name, (int) $journalId]
        );
        
        $count = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $count = (int) $row['count'];
        }
        $result->Close();

        $dbValue = $this->convertToDB($value, $type);

        if ($count === 0) {
            $returner = $this->update(
                'INSERT INTO plugin_settings
                    (plugin_name, journal_id, setting_name, setting_value, setting_type)
                VALUES
                    (?, ?, ?, ?, ?)',
                [(string) $pluginName, (int) $journalId, (string) $name, $dbValue, $type]
            );
        } else {
            $returner = $this->update(
                'UPDATE plugin_settings SET
                    setting_value = ?,
                    setting_type = ?
                WHERE plugin_name = ? AND setting_name = ? AND journal_id = ?',
                [$dbValue, $type, (string) $pluginName, (string) $name, (int) $journalId]
            );
        }

        return (bool) $returner;
    }

    /**
     * Delete a plugin setting.
     * @param int $journalId
     * @param string $pluginName
     * @param string $name
     * @return bool
     */
    public function deleteSetting($journalId, $pluginName, $name) {
        $pluginName = strtolower_codesafe((string) $pluginName);

        $cache = $this->_getCache($journalId, $pluginName);
        $cache->setCache($name, null);

        return (bool) $this->update(
            'DELETE FROM plugin_settings WHERE plugin_name = ? AND setting_name = ? AND journal_id = ?',
            [(string) $pluginName, (string) $name, (int) $journalId]
        );
    }

    /**
     * Delete all settings for a plugin.
     * @param string $pluginName
     * @param int|null $journalId
     * @return bool
     */
    public function deleteSettingsByPlugin($pluginName, $journalId = null) {
        $pluginName = strtolower_codesafe((string) $pluginName);

        if ($journalId !== null) {
            $cache = $this->_getCache($journalId, $pluginName);
            $cache->flush();

            return (bool) $this->update(
                'DELETE FROM plugin_settings WHERE journal_id = ? AND plugin_name = ?',
                [(int) $journalId, (string) $pluginName]
            );
        } else {
            $cacheManager = CacheManager::getManager();
            // NB: this actually deletes all plugins' settings cache
            $cacheManager->flush('pluginSettings', CACHE_TYPE_FILE);

            return (bool) $this->update(
                'DELETE FROM plugin_settings WHERE plugin_name = ?',
                [(string) $pluginName]
            );
        }
    }

    /**
     * Delete all settings for a journal.
     * @param int $journalId
     * @return bool
     */
    public function deleteSettingsByJournalId($journalId) {
        return (bool) $this->update(
            'DELETE FROM plugin_settings WHERE journal_id = ?', 
            [(int) $journalId]
        );
    }

    /**
     * Used internally by installSettings to perform variable and translation replacements.
     * @param string $rawInput
     * @param array $paramArray
     * @return string
     */
    protected function _performReplacement($rawInput, $paramArray = []) {
        $value = preg_replace_callback(
            '/{{translate key="([^"]+)"}}/', 
            function($matches) {
                return __($matches[1]);
            }, 
            $rawInput
        );
        
        foreach ($paramArray as $pKey => $pValue) {
            $value = str_replace('{$' . $pKey . '}', (string) $pValue, $value);
        }
        return $value;
    }

    /**
     * Used internally by installSettings to recursively build nested arrays.
     * Deals with translation and variable replacement calls.
     * @param XMLNode $node <array> tag
     * @param array $paramArray
     * @return array
     */
    protected function _buildObject($node, $paramArray = []) {
        $value = [];
        foreach ($node->getChildren() as $element) {
            $key = $element->getAttribute('key');
            $childArray = $element->getChildByName('array');
            
            if ($childArray) {
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
     * Install plugin settings from an XML file.
     * @param int $journalId
     * @param string $pluginName
     * @param string $filename
     * @param array $paramArray 
     * @return bool|void
     */
    public function installSettings($journalId, $pluginName, $filename, $paramArray = []) {
        $xmlParser = new PKPXMLParser();
        $tree = $xmlParser->parse($filename);

        if (!$tree) {
            $xmlParser->destroy();
            return false;
        }

        $currentSettings = $this->getPluginSettings($journalId, $pluginName);

        foreach ($tree->getChildren() as $setting) {
            $nameNode = $setting->getChildByName('name');
            $valueNode = $setting->getChildByName('value');

            if ($nameNode && $valueNode) {
                $type = $setting->getAttribute('type');
                $name = $nameNode->getValue();

                // If the setting already exists, respect it.
                if (array_key_exists($name, $currentSettings)) {
                    continue;
                }

                if ($type === 'object') {
                    $arrayNode = $valueNode->getChildByName('array');
                    $value = $this->_buildObject($arrayNode, $paramArray);
                } else {
                    $value = $this->_performReplacement($valueNode->getValue(), $paramArray);
                }

                $this->updateSetting($journalId, $pluginName, $name, $value, $type);
            }
        }

        $xmlParser->destroy();
    }
}

/**
 * Used internally by plugin setting installation code to perform 
 * translation function.
 * @deprecated Kept for backward compatibility with legacy XML installers that might reference it globally.
 * @param mixed $matches
 */
function _installer_plugin_regexp_callback($matches) {
    return __($matches[1]);
}

?>