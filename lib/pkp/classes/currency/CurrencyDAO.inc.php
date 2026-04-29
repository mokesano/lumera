<?php
declare(strict_types=1);

/**
 * @file classes/currency/CurrencyDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CurrencyDAO
 * @ingroup currency
 * @see Currency
 *
 * @brief Operations for retrieving and modifying Currency objects.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.currency.Currency');

class CurrencyDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CurrencyDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the currency cache.
     * @return FileCache
     */
    public function _getCache() {
        $locale = AppLocale::getLocale();
        $cache = Registry::get('currencyCache', true, null);
        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                'currencies', 
                $locale,
                [$this, '_cacheMiss']
            );
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime($this->getCurrencyFilename($locale))) {
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
        $allCurrencies = Registry::get('allCurrencies', true, null);
        if ($allCurrencies === null) {
            // Add a locale load to the debug notes.
            $notes = Registry::get('system.debug.notes');
            $filename = $this->getCurrencyFilename(AppLocale::getLocale());
            $notes[] = ['debug.notes.currencyListLoad', ['filename' => $filename]];

            // Reload locale registry file
            $xmlDao = new XMLDAO();
            $data = $xmlDao->parseStruct($filename, ['currency']);

            // Build array with ($charKey => array(stuff))
            if (isset($data['currency'])) {
                foreach ($data['currency'] as $currencyData) {
                    $allCurrencies[$currencyData['attributes']['code_alpha']] = [
                        $currencyData['attributes']['name'],
                        $currencyData['attributes']['code_numeric']
                    ];
                }
            }
            asort($allCurrencies);
            $cache->setEntireCache($allCurrencies);
        }
        return null;
    }

    /**
     * Get the filename of the currency database
     * @param string $locale
     * @return string
     */
    public function getCurrencyFilename($locale) {
        return "lib/pkp/locale/$locale/currencies.xml";
    }

    /**
     * Retrieve a currency by alpha currency ID.
     * @param string $codeAlpha
     * @return Currency|null
     */
    public function getCurrencyByAlphaCode($codeAlpha) {
        $cache = $this->_getCache();
        $entry = $cache->get($codeAlpha);
        
        // [WIZDAM FIX] Prevent fatal error if currency code not found
        if (!$entry) {
            return null;
        }

        return $this->_returnCurrencyFromRow($codeAlpha, $entry);
    }

    /**
     * Retrieve an array of all currencies.
     * @return array of Currencies
     */
    public function getCurrencies() {
        $cache = $this->_getCache();
        $returner = [];
        foreach ($cache->getContents() as $codeAlpha => $entry) {
            $returner[] = $this->_returnCurrencyFromRow($codeAlpha, $entry);
        }
        return $returner;
    }

    /**
     * Instantiate and return a new data object.
     * @return Currency
     */
    public function newDataObject() {
        return new Currency();
    }

    /**
     * Internal function to return a Currency object from a row.
     * @param string $codeAlpha
     * @param array $entry
     * @return Currency
     */
    public function _returnCurrencyFromRow($codeAlpha, $entry) {
        $currency = $this->newDataObject();
        $currency->setCodeAlpha($codeAlpha);
        $currency->setName($entry[0]);
        $currency->setCodeNumeric($entry[1]);

        HookRegistry::dispatch('CurrencyDAO::_returnCurrencyFromRow', [&$currency, &$codeAlpha, &$entry]);

        return $currency;
    }
}

?>