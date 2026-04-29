<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_dc11_schema
 */

/**
 * @file plugins/metadata/dc11/schema/PKPDc11Schema.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPDc11Schema
 * @ingroup plugins_metadata_dc11_schema
 * @see MetadataSchema
 *
 * @brief Class that provides meta-data properties compliant with
 * the Dublin Core specification, version 1.1.
 *
 * For details see <http://dublincore.org/documents/dces/>,
 */

import('lib.pkp.classes.metadata.MetadataSchema');

class PKPDc11Schema extends MetadataSchema {
    
    /**
     * Constructor
     * @param int $appSpecificAssocType
     */
    public function __construct($appSpecificAssocType) {
        // Configure the meta-data schema.
        parent::__construct(
            'dc-1.1',
            'dc',
            'plugins.metadata.dc11.schema.Dc11Schema',
            (int) $appSpecificAssocType
        );

        $this->addProperty('dc:title', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:creator', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:subject', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:description', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:publisher', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:contributor', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:date', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:type', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:format', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:identifier', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:source', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:language', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:relation', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:coverage', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
        $this->addProperty('dc:rights', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPDc11Schema($appSpecificAssocType) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}
?>