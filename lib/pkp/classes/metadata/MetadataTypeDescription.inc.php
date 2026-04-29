<?php
declare(strict_types=1);

/**
 * @file classes/metadata/MetadataTypeDescription.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetadataTypeDescription
 * @ingroup metadata
 *
 * @brief Type validator for metadata input/output.
 *
 * This type description accepts descriptors of the following form:
 * metadata::fully.qualified.MetadataSchema(ASSOC)
 *
 * e.g.:
 * metadata::lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema(ARTICLE)
 *
 * The assoc form must be the final part of a ASSOC_TYPE_* definition.
 * It can be '*' to designate any assoc type.
 */

import('lib.pkp.classes.filter.ClassTypeDescription');

define('ASSOC_TYPE_ANY', -1);

class MetadataTypeDescription extends ClassTypeDescription {
    /** @var string the expected meta-data schema package */
    public $_metadataSchemaPackageName;

    /** @var string the expected meta-data schema class */
    public $_metadataSchemaClassName;

    /** @var integer the expected assoc type of the meta-data description */
    public $_assocType;

    /**
     * Constructor
     *
     * @param $typeName string a fully qualified class name.
     */
    public function __construct($typeName) {
        parent::__construct($typeName);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $typeName string
     */
    public function MetadataTypeDescription($typeName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::MetadataTypeDescription(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($typeName);
    }

    //
    // Setters and Getters
    //
    /**
     * @see TypeDescription::getNamespace()
     */
    public function getNamespace() {
        return TYPE_DESCRIPTION_NAMESPACE_METADATA;
    }

    /**
     * @return string the fully qualified class name of the meta-data schema.
     */
    public function getMetadataSchemaClass() {
        return $this->_metadataSchemaPackageName.'.'.$this->_metadataSchemaClassName;
    }

    /**
     * @return integer
     */
    public function getAssocType() {
        return $this->_assocType;
    }


    //
    // Implement abstract template methods from TypeDescription
    //
    /**
     * @see TypeDescription::parseTypeName()
     */
    public function parseTypeName($typeName) {
        // Configure the parent class type description
        // with the expected meta-data class.
        parent::parseTypeName('lib.pkp.classes.metadata.MetadataDescription');

        // Split the type name into class name and assoc type.
        $typeNameParts = explode('(', $typeName);
        if (!count($typeNameParts) == 2) return false;

        // The meta-data schema class must be
        // a fully qualified class name.
        $splitMetadataSchemaClass = $this->splitClassName($typeNameParts[0]);
        if ($splitMetadataSchemaClass === false) return false;
        list($this->_metadataSchemaPackageName, $this->_metadataSchemaClassName) = $splitMetadataSchemaClass;

        // Identify the assoc type.
        $assocTypeString = trim($typeNameParts[1], ')');
        if ($assocTypeString == '*') {
            $this->_assocType = ASSOC_TYPE_ANY;
        } else {
            // Make sure that the given assoc type exists.
            $assocTypeString = 'ASSOC_TYPE_'.$assocTypeString;
            if (!defined($assocTypeString)) return false;
            $this->_assocType = constant($assocTypeString);
        }

        return true;
    }

    /**
     * @see TypeDescription::checkType()
     */
    public function checkType($object) {
        // First of all check whether this is a
        // meta-data description at all.
        if (!parent::checkType($object)) return false;

        // Check the meta-data schema.
        // Removed & from getMetadataSchema call
        $metadataSchema = $object->getMetadataSchema();
        
        // Modernized type check
        // Note: is_a allows for class name strings which matches logic here if $_metadataSchemaClassName is just a string class name
        if (!is_a($metadataSchema, $this->_metadataSchemaClassName)) return false;

        // Check the assoc type
        if ($this->_assocType != ASSOC_TYPE_ANY) {
            if ($object->getAssocType() != $this->_assocType) return false;
        }

        return true;
    }
}
?>