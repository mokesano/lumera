<?php
declare(strict_types=1);

/**
 * @file classes/language/LanguageDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageDAO
 * @ingroup language
 * @see Language
 *
 * @brief Operations for retrieving and modifying Language objects.
 *
 */

import('lib.pkp.classes.language.Language');

class LanguageDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LanguageDAO() {
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
     * Return the language cache.
     * @param string|null $locale
     * @return FileCache
     */
    public function _getCache($locale = null) {
        if (is_null($locale)) {
            $locale = AppLocale::getLocale();
        }
        $cache = Registry::get('languageCache-'.$locale, true, null);
        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                'languages', $locale,
                [$this, '_cacheMiss']
            );
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime($this->getLanguageFilename($locale))) {
                $cache->flush();
            }
        }

        return $cache;
    }

    public function _cacheMiss($cache, $id) {
        $allLanguages = Registry::get('allLanguages-'.$cache->cacheId, true, null);
        if ($allLanguages === null) {
            // Add a locale load to the debug notes.
            $notes = Registry::get('system.debug.notes');
            $locale = $cache->cacheId;
            if ($locale == null) {
                $locale = AppLocale::getLocale();
            }
            $filename = $this->getLanguageFilename($locale);
            $notes[] = ['debug.notes.languageListLoad', ['filename' => $filename]];

            // Reload locale registry file
            $xmlDao = new XMLDAO();
            $data = $xmlDao->parseStruct($filename, ['language']);

            // Build array with ($charKey => array(stuff))
            if (isset($data['language'])) {
                foreach ($data['language'] as $languageData) {
                    $allLanguages[$languageData['attributes']['code']] = [
                        $languageData['attributes']['name'],
                    ];
                }
            }
            if (is_array($allLanguages)) {
                asort($allLanguages);
            }
            $cache->setEntireCache($allLanguages);
        }
        if (isset($allLanguages[$id])) {
            return $allLanguages[$id];
        } else {
            return null;
        }
    }

    /**
     * Get the filename of the language database
     * @param string $locale
     * @return string
     */
    public function getLanguageFilename($locale) {
        return "lib/pkp/locale/$locale/languages.xml";
    }

    /**
     * Retrieve a language by code.
     * @param string $code ISO 639-1
     * @param string|null $locale
     * @return Language
     */
    public function getLanguageByCode($code, $locale = null) {
        $cache = $this->_getCache($locale);
        $returner = $this->_returnLanguageFromRow($code, $cache->get($code));
        return $returner;
    }

    /**
     * Retrieve an array of all languages.
     * @param string|null $locale an optional locale to use
     * @return array of Languages
     */
    public function getLanguages($locale = null) {
        $cache = $this->_getCache($locale);
        $returner = [];
        foreach ($cache->getContents() as $code => $entry) {
            $returner[] = $this->_returnLanguageFromRow($code, $entry);
        }
        return $returner;
    }

    /**
     * Retrieve an array of all languages names.
     * @param string|null $locale an optional locale to use
     * @return array of Languages names
     */
    public function getLanguageNames($locale = null) {
        $cache = $this->_getCache($locale);
        $returner = [];
        $cacheContents = $cache->getContents();
        if (is_array($cacheContents)) {
            foreach ($cache->getContents() as $code => $entry) {
                $returner[] = $entry[0];
            }
        }
        return $returner;
    }

    /**
     * Instantiate a new data object.
     * @return Language
     */
    public function newDataObject() {
        return new Language();
    }

    /**
     * Internal function to return a Language object from a row.
     * @param string $code
     * @param array $entry
     * @return Language
     */
    public function _returnLanguageFromRow($code, $entry) {
        $language = $this->newDataObject();
        $language->setCode($code);
        $language->setName($entry[0]);

        HookRegistry::dispatch('LanguageDAO::_returnLanguageFromRow', [&$language, &$code, &$entry]);

        return $language;
    }
}
?>