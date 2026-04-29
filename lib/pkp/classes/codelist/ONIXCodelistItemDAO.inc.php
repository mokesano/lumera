<?php
declare(strict_types=1);

/**
 * @file classes/codelist/ONIXCodelistItemDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ONIXCodelistItemDAO.inc.php
 * @ingroup codelist
 * @see CodelistItem
 *
 * @brief Parent class for operations involving Codelist objects.
 *
 */

import('lib.pkp.classes.codelist.ONIXCodelistItem');

class ONIXCodelistItemDAO extends DAO {

    /** @var string The name of the codelist we are interested in */
    protected string $_list = '';

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ONIXCodelistItemDAO() {
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

        $cacheName = 'Onix' . $this->getListName() . 'Cache';
        $cache = Registry::get($cacheName, true, null);

        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                $this->getListName() . '_codelistItems',
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
        $allCodelistItems = Registry::get('all' . $this->getListName() . 'CodelistItems', true, null);
        
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
            $listName = $this->getListName(); // i.e., 'List30'
            
            import('lib.pkp.classes.codelist.ONIXParserDOMHandler');
            $handler = new ONIXParserDOMHandler($listName);

            import('lib.pkp.classes.xslt.XSLTransformer');
            import('lib.pkp.classes.file.FileManager');
            import('classes.file.TemporaryFileManager');

            $temporaryFileManager = new TemporaryFileManager();
            $fileManager = new FileManager();

            $tmpName = tempnam($temporaryFileManager->getBasePath(), 'ONX');
            if ($tmpName === false) {
                 throw new RuntimeException('Could not create temporary file for ONIX parsing.');
            }

            $xslTransformer = new XSLTransformer();
            $xslTransformer->setParameters(['listName' => $listName]);
            $xslTransformer->setRegisterPHPFunctions(true);

            $xslFile = 'lib/pkp/xml/onixFilter.xsl';
            $filteredXml = $xslTransformer->transform(
                $filename, 
                XSL_TRANSFORMER_DOCTYPE_FILE, 
                $xslFile, 
                XSL_TRANSFORMER_DOCTYPE_FILE, 
                XSL_TRANSFORMER_DOCTYPE_STRING
            );

            if (!$filteredXml) {
                throw new RuntimeException("XSL Transformation failed for file: $filename");
            }

            $data = null;

            if (is_writeable($tmpName)) {
                $fp = fopen($tmpName, 'wb');
                if ($fp) {
                    fwrite($fp, $filteredXml);
                    fclose($fp);
                    $data = $xmlDao->parseWithHandler($tmpName, $handler);
                    $fileManager->deleteFile($tmpName);
                }
            } else {
                throw new RuntimeException('Misconfigured directory permissions on: ' . $temporaryFileManager->getBasePath());
            }

            // Build array with ($charKey => array(stuff))
            if (isset($data[$listName])) {
                foreach ($data[$listName] as $code => $codelistData) {
                    $allCodelistItems[$code] = $codelistData;
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
     * Get the filename of the ONIX XSD.
     * @param string $locale
     * @return string
     */
    public function getFilename(string $locale): string {
        if (!AppLocale::isLocaleValid($locale)) {
            $locale = AppLocale::MASTER_LOCALE;
        }
        return "lib/pkp/locale/$locale/ONIX_BookProduct_CodeLists.xsd";
    }

    /**
     * Set the name of the list we want.
     * @param string $list
     */
    public function setListName(string $list): void {
        $this->_list = $list;
    }

    /**
     * Get the base node name particular codelist database.
     * @return string
     */
    public function getListName(): string {
        return $this->_list;
    }

    /**
     * Get the name of the CodelistItem subclass.
     * @return ONIXCodelistItem
     */
    public function newDataObject(): ONIXCodelistItem {
        return new ONIXCodelistItem();
    }

    /**
     * Retrieve an array of all the codelist items.
     * @param string $list the List string for this code list (i.e., List30)
     * @param string|null $locale an optional locale to use
     * @return ONIXCodelistItem[]
     */
    public function getCodelistItems(string $list, ?string $locale = null): array {
        $this->setListName($list);
        $cache = $this->_getCache($locale);
        $returner = [];
        
        foreach ($cache->getContents() as $code => $entry) {
            $returner[] = $this->_returnFromRow((string)$code, $entry);
        }
        
        return $returner;
    }

    /**
     * Retrieve an array of all codelist codes and values for a given list.
     * @param string $list the List string for this code list (i.e., List30)
     * @param array $codesToExclude an optional list of codes to exclude from the returned list
     * @param string|null $codesFilter an optional filter to match codes against.
     * @param string|null $locale an optional locale to use
     * @return array
     */
    public function getCodes(string $list, array $codesToExclude = [], ?string $codesFilter = null, ?string $locale = null): array {
        $this->setListName($list);
        $cache = $this->_getCache($locale);
        $returner = [];
        $cacheContents = $cache->getContents();
        
        if (is_array($cacheContents)) {
            foreach ($cacheContents as $code => $entry) {
                if ($code != '') {
                    if (!in_array($code, $codesToExclude) && 
                        (empty($codesFilter) || preg_match("/^" . preg_quote($codesFilter, '/') . "/i", $entry[0]))) {
                        $returner[$code] = $entry[0];
                    }
                }
            }
        }
        return $returner;
    }

    /**
     * Determines if a particular code value is valid for a given list.
     * @param string|null $code
     * @param string $list
     * @return bool
     */
    public function codeExistsInList(?string $code, string $list): bool {
        if ($code === null) return false;
        $listKeys = array_keys($this->getCodes($list));
        return in_array($code, $listKeys);
    }

    /**
     * Internal function to return a Codelist object from a row.
     * @param string $code
     * @param array $entry
     * @return ONIXCodelistItem
     */
    public function _returnFromRow(string $code, array $entry): ONIXCodelistItem {
        $codelistItem = $this->newDataObject();
        $codelistItem->setCode($code);
        $codelistItem->setText($entry[0]);

        HookRegistry::dispatch('ONIXCodelistItemDAO::_returnFromRow', [&$codelistItem, &$code, &$entry]);

        return $codelistItem;
    }
}
?>