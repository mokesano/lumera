<?php
declare(strict_types=1);

/**
 * @file classes/codelist/CodelistItemDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CodelistItemDAO
 * @ingroup codelist
 * @see CodelistItem
 *
 * @brief Parent class for operations involving Codelist objects.
 *
 */

import('lib.pkp.classes.codelist.CodelistItem');

class CodelistItemDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CodelistItemDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the cache object.
     * @param string|null $locale
     * @return GenericCache
     */
    protected function _getCache(?string $locale = null) {
        if ($locale === null) {
            $locale = AppLocale::getLocale();
        }
        
        $cacheName = $this->getCacheName();
        $cache = Registry::get($cacheName, true, null);

        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                $this->getName() . '_codelistItems',
                $locale,
                [$this, '_cacheMiss']
            );
            
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime($this->getFilename($locale))) {
                $cache->flush();
            }
        }

        return $cache;
    }

    /**
     * Callback for cache miss.
     * @param FileCache $cache
     * @param mixed $id
     * @return null
     */
    public function _cacheMiss($cache, $id) {
        $allCodelistItems = Registry::get('all' . $this->getName() . 'CodelistItems', true, null);
        
        if ($allCodelistItems === null) {
            // Add a locale load to the debug notes.
            $notes = Registry::get('system.debug.notes');
            $locale = $cache->cacheId;
            if ($locale === null) {
                $locale = AppLocale::getLocale();
            }
            
            $filename = $this->getFilename($locale);
            $notes[] = ['debug.notes.codelistItemListLoad', ['filename' => $filename]];

            // Reload locale registry file
            $xmlDao = new XMLDAO();
            $nodeName = $this->getName(); // i.e., subject
            $data = $xmlDao->parseStruct($filename, [$nodeName]);

            // Build array with ($charKey => array(stuff))
            if (isset($data[$nodeName])) {
                foreach ($data[$nodeName] as $codelistData) {
                    $allCodelistItems[$codelistData['attributes']['code']] = [
                        $codelistData['attributes']['text'],
                    ];
                }
            }
            
            if (is_array($allCodelistItems)) {
                asort($allCodelistItems);
            }
            
            $cache->setEntireCache($allCodelistItems);
        }
        return null;
    }

    /**
     * Get the cache name for this particular codelist database
     * @return string
     */
    public function getCacheName(): string {
        return $this->getName() . 'Cache';
    }

    /**
     * Get the filename of the codelist database
     * @param string $locale
     * @return string
     * @throws BadMethodCallException
     */
    public function getFilename(string $locale): string {
        throw new BadMethodCallException('This method must be implemented by a subclass.');
    }

    /**
     * Get the base node name particular codelist database
     * @return string
     * @throws BadMethodCallException
     */
    public function getName(): string {
        throw new BadMethodCallException('This method must be implemented by a subclass.');
    }

    /**
     * Get the name of the CodelistItem subclass.
     * @return CodelistItem
     * @throws BadMethodCallException
     */
    public function newDataObject(): CodelistItem {
        throw new BadMethodCallException('This method must be implemented by a subclass.');
    }

    /**
     * Retrieve a codelist by code.
     * @param string $code
     * @return CodelistItem|null
     */
    public function getByCode(string $code): ?CodelistItem {
        $cache = $this->_getCache();
        $entry = $cache->get($code);
        
        if (!$entry) {
            return null;
        }

        return $this->_returnFromRow($code, $entry);
    }

    /**
     * Retrieve an array of all the codelist items.
     * @param string|null $locale an optional locale to use
     * @return CodelistItem[]
     */
    public function getCodelistItems(?string $locale = null): array {
        $cache = $this->_getCache($locale);
        $returner = [];
        foreach ($cache->getContents() as $code => $entry) {
            $returner[] = $this->_returnFromRow((string)$code, $entry);
        }
        return $returner;
    }

    /**
     * Retrieve an array of all codelist names.
     * @param string|null $locale an optional locale to use
     * @return array
     */
    public function getNames(?string $locale = null): array {
        $cache = $this->_getCache($locale);
        $returner = [];
        $cacheContents = $cache->getContents();
        if (is_array($cacheContents)) {
            foreach ($cacheContents as $entry) {
                $returner[] = $entry[0];
            }
        }
        return $returner;
    }

    /**
     * Internal function to return a Codelist object from a row.
     * @param string $code
     * @param array $entry
     * @return CodelistItem
     */
    public function _returnFromRow(string $code, array $entry): CodelistItem {
        $codelistItem = $this->newDataObject();
        $codelistItem->setCode($code);
        $codelistItem->setText($entry[0]);

        HookRegistry::dispatch('CodelistItemDAO::_returnFromRow', [&$codelistItem, &$code, &$entry]);

        return $codelistItem;
    }
}
?>