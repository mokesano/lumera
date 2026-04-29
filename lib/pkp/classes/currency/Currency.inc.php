<?php
declare(strict_types=1);

/**
 * @defgroup currency
 */

/**
 * @file classes/currency/Currency.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Currency
 * @ingroup currency
 * @see CurrencyDAO
 *
 * @brief Basic class describing a currency.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

class Currency extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Currency() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the name of the currency.
     * @return string
     */
    public function getName() {
        return $this->getData('name');
    }

    /**
     * Set the name of the currency.
     * @param string $name
     */
    public function setName($name) {
        return $this->setData('name', $name);
    }

    /**
     * Get currency alpha code.
     * @return string
     */
    public function getCodeAlpha() {
        return $this->getData('codeAlpha');
    }

    /**
     * Set currency alpha code.
     * @param string $codeAlpha
     */
    public function setCodeAlpha($codeAlpha) {
        return $this->setData('codeAlpha', $codeAlpha);
    }

    /**
     * Get currency numeric code.
     * @return int
     */
    public function getCodeNumeric() {
        return (int) $this->getData('codeNumeric');
    }

    /**
     * Set currency numeric code.
     * @param int|string $codeNumeric
     */
    public function setCodeNumeric($codeNumeric) {
        return $this->setData('codeNumeric', $codeNumeric);
    }
}

?>