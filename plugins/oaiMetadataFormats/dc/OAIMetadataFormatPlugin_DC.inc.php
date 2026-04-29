<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/dc/OAIMetadataFormatPlugin_DC.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_DC
 * @ingroup oai_format
 * @see OAI
 *
 * @brief dc metadata format plugin for OAI.
 */

import('lib.pkp.plugins.oaiMetadataFormats.dc.PKPOAIMetadataFormatPlugin_DC');

class OAIMetadataFormatPlugin_DC extends PKPOAIMetadataFormatPlugin_DC {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormatPlugin_DC() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }
}

?>