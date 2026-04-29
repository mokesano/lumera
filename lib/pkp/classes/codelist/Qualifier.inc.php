<?php
declare(strict_types=1);

/**
 * @defgroup qualifier
 */

/**
 * @file classes/codelist/Qualifier.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Qualifier
 * @ingroup codelist
 * @see QualifierDAO
 *
 * @brief Basic class describing a BIC Qualifier.
 *
 */

import('lib.pkp.classes.codelist.CodelistItem');

class Qualifier extends CodelistItem {

    /**
     * The numerical representation of these Subject Qualifiers in ONIX 3.0
     * @var string
     */
    protected string $_onixSubjectSchemeIdentifier = '17';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Qualifier() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * @return string the numerical value representing this item in the ONIX 3.0 schema
     */
    public function getOnixSubjectSchemeIdentifier(): string {
        return $this->_onixSubjectSchemeIdentifier;
    }
}

?>