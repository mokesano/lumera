<?php
declare(strict_types=1);

/**
 * @defgroup codelist
 */

/**
 * @file classes/codelist/CodelistItem.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CodelistItem
 * @ingroup codelist
 * @see CodelistItemDAO
 *
 * @brief Basic class describing a codelist item.
 *
 */


class CodelistItem extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CodelistItem() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the text component of the codelist.
     * @return string
     */
    public function getText(): string {
        return (string) $this->getData('text');
    }

    /**
     * Set the text component of the codelist.
     * @param string $text
     */
    public function setText(string $text) {
        return $this->setData('text', $text);
    }

    /**
     * Get codelist code.
     * @return string
     */
    public function getCode(): string {
        return (string) $this->getData('code');
    }

    /**
     * Set codelist code.
     * @param string $code
     */
    public function setCode(string $code) {
        return $this->setData('code', $code);
    }

    /**
     * @return string the numerical value representing this item in the ONIX 3.0 schema
     * @throws BadMethodCallException if not overridden by subclass
     */
    public function getOnixSubjectSchemeIdentifier(): string {
        // [WIZDAM] Replaced assert(false) with a proper exception for runtime safety
        throw new BadMethodCallException('This method must be implemented by a subclass.');
    }
}

?>