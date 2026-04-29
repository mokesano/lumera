<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_dc11_schema
 */

/**
 * @file plugins/metadata/dc11/schema/Dc11Schema.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Dc11Schema
 * @ingroup plugins_metadata_dc11_schema
 * @see PKPDc11Schema
 *
 * @brief OJS-specific implementation of the Dc11Schema.
 */

import('lib.pkp.plugins.metadata.dc11.schema.PKPDc11Schema');

class Dc11Schema extends PKPDc11Schema {

    /**
     * Constructor
     */
    public function __construct() {
        // Configure the MODS schema.
        parent::__construct(ASSOC_TYPE_ARTICLE);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Dc11Schema() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}

?>