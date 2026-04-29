<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_openurl10_schema
 */

/**
 * @file plugins/metadata/openurl10/schema/Openurl10JournalBookBaseSchema.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Openurl10JournalBookBaseSchema
 * @ingroup plugins_metadata_openurl10_schema
 * @see Openurl10BaseSchema
 *
 * @brief Class that provides meta-data properties common to the
 * journal and book variants of the OpenURL 1.0 standard.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.plugins.metadata.openurl10.schema.Openurl10BaseSchema');

define('OPENURL10_GENRE_CONFERENCE', 'conference');
define('OPENURL10_GENRE_PROCEEDING', 'proceeding');
define('OPENURL10_GENRE_UNKNOWN', 'unknown');

class Openurl10JournalBookBaseSchema extends Openurl10BaseSchema {
    
    /**
     * Constructor
     * @param $name string the meta-data schema name
     * @param $classname string the fully qualified class name
     */
    public function __construct($name, $classname) {
        // [WIZDAM FIX] Modern Parent Constructor Call
        // Pass parameters up to Openurl10BaseSchema
        parent::__construct($name, $classname);

        // Add meta-data properties common to the OpenURL book/journal standard
        $this->addProperty('aucorp');   // Organization or corporation that is the author or creator
        $this->addProperty('atitle');
        $this->addProperty('spage', METADATA_PROPERTY_TYPE_INTEGER);
        $this->addProperty('epage', METADATA_PROPERTY_TYPE_INTEGER);
        $this->addProperty('pages');
        $this->addProperty('issn');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Openurl10JournalBookBaseSchema($name, $classname) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to use __construct().", E_USER_DEPRECATED);
        }
        self::__construct($name, $classname);
    }
}

?>