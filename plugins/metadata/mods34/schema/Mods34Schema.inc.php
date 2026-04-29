<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_mods34_schema
 */

/**
 * @file plugins/metadata/mods34/schema/Mods34Schema.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34Schema
 * @ingroup plugins_metadata_mods34_schema
 * @see PKPMods34Schema
 *
 * @brief OJS-specific implementation of the Mods34Schema.
 */

import('lib.pkp.plugins.metadata.mods34.schema.PKPMods34Schema');

class Mods34Schema extends PKPMods34Schema {

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
    public function Mods34Schema() {
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