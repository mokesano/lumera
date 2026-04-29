<?php
declare(strict_types=1);

/**
 * @file classes/metadata/MetadataDescription.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetadataDescription
 * @ingroup metadata
 * @see MetadataProperty
 * @see MetadataRecord
 * @see MetadataSchema
 *
 * @brief Class modeling a description (DCMI abstract model) or subject-
 * predicate-object graph (RDF). This class and its children provide
 * meta-data (DCMI abstract model: statements of property-value pairs,
 * RDF: assertions of predicate-object pairs) about a given PKP application
 * entity instance (DCMI abstract model: described resource, RDF: subject).
 */

import('lib.pkp.classes.core.DataObject');

define('METADATA_DESCRIPTION_REPLACE_ALL', 0x01);
define('METADATA_DESCRIPTION_REPLACE_PROPERTIES', 0x02);
define('METADATA_DESCRIPTION_REPLACE_NOTHING', 0x03);

define('METADATA_DESCRIPTION_UNKNOWN_LOCALE', 'unknown');

class MetadataDescription extends DataObject {
    /** @var string fully qualified class name of the meta-data schema this description complies to */
    public $_metadataSchemaName;

    /** @var MetadataSchema the schema this description complies to */
    public $_metadataSchema;

    /** @var int association type (the type of the described resource) */
    public $_assocType;

    /** @var int association id (the identifier of the described resource) */
    public $_assocId;

    /**
     * @var string an (optional) display name that describes the contents
     * of this meta-data description to the end user.
     */
    public $_displayName;

    /**
     * @var integer sequence id used when saving several descriptions
     * of the same subject.
     */
    public $_seq;

    /**
     * Constructor
     * @param $metadataSchemaName string
     * @param $assocType int
     */
    public function __construct($metadataSchemaName, $assocType) {
        assert(is_string($metadataSchemaName) && is_integer($assocType));
        parent::__construct();
        $this->_metadataSchemaName = $metadataSchemaName;
        $this->_assocType = $assocType;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MetadataDescription($metadataSchemaName, $assocType) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::MetadataDescription(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($metadataSchemaName, $assocType);
    }

    //
    // Get/set methods
    //
    /**
     * Get the fully qualified class name of
     * the supported meta-data schema.
     */
    public function getMetadataSchemaName() {
        return $this->_metadataSchemaName;
    }

    /**
     * Get the metadata schema
     * @return MetadataSchema
     */
    public function getMetadataSchema() {
        // Lazy-load the meta-data schema if this has
        // not been done before.
        if (is_null($this->_metadataSchema)) {
            // Removed & from instantiate
            $this->_metadataSchema = instantiate($this->getMetadataSchemaName(), 'MetadataSchema');
            assert(is_object($this->_metadataSchema));
        }
        return $this->_metadataSchema;
    }

    /**
     * Get the association type (described resource type)
     * @return int
     */
    public function getAssocType() {
        return $this->_assocType;
    }

    /**
     * Get the association id (described resource identifier)
     * @return int
     */
    public function getAssocId() {
        return $this->_assocId;
    }

    /**
     * Set the association id (described resource identifier)
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        $this->_assocId = $assocId;
    }

    /**
     * Construct a meta-data application entity id
     * (described resource id / subject id) for
     * this meta-data description object.
     * @return string
     */
    public function getAssoc() {
        $assocType = $this->getAssocType();
        $assocId = $this->getAssocId();
        assert(isset($assocType) && isset($assocId));
        return $assocType.':'.$assocId;
    }

    /**
     * Set the (optional) display name
     * @param $displayName string
     */
    public function setDisplayName($displayName) {
        $this->_displayName = $displayName;
    }

    /**
     * Get the (optional) display name
     * @return string
     */
    public function getDisplayName() {
        return $this->_displayName;
    }

    /**
     * Set the sequence id
     * @param $seq integer
     */
    public function setSeq($seq) {
        $this->_seq = $seq;
    }

    /**
     * Get the sequence id
     * @return integer
     */
    public function getSeq() {
        return $this->_seq;
    }

    /**
     * Add a meta-data statement. Statements can only be added
     * for properties that are part of the meta-data schema. This
     * method will also check the validity of the value for the
     * given property before adding the statement.
     * @param $propertyName string The name of the property
     * @param $value mixed The value to be assigned to the property
     * @param $locale string
     * @param $replace boolean whether to replace an existing statement
     * @return boolean true if a valid statement was added, otherwise false
     */
    public function addStatement($propertyName, $value, $locale = null, $replace = false) {
        // Check the property
        $property = $this->getProperty($propertyName);
        if (is_null($property)) return false;
        // Modernized type check
        assert($property instanceof MetadataProperty);

        // Check that the property is allowed for the described resource
        if (!in_array($this->_assocType, $property->getAssocTypes())) return false;

        // Handle translation
        $translated = $property->getTranslated();
        if (isset($locale) && !$translated) return false;
        if (!isset($locale) && $translated) {
            // Retrieve the current locale
            $locale = AppLocale::getLocale();
        }

        // Check that the value is compliant with the property specification
        if ($property->isValid($value, $locale) === false) return false;

        // Handle cardinality
        $existingValue = $this->getStatement($propertyName, $locale);
        
        switch ($property->getCardinality()) {
            case METADATA_PROPERTY_CARDINALITY_ONE:
                if (isset($existingValue) && !$replace) return false;
                $newValue = $value;
                break;

            case METADATA_PROPERTY_CARDINALITY_MANY:
                if (isset($existingValue) && !$replace) {
                    assert(is_array($existingValue));
                    $newValue = $existingValue;
                    array_push($newValue, $value);
                } else {
                    // Removed reference: just create array with value
                    $newValue = array($value);
                }
                break;

            default:
                assert(false);
        }

        // Add the value
        $this->setData($propertyName, $newValue, $locale);
        return true;
    }

    /**
     * Remove statement. If the property has cardinality 'many'
     * then all statements for the property will be removed at once.
     * If the property is translated and the locale is null then
     * the statements for all locales will be removed.
     * @param $propertyName string
     * @param $locale string
     * @return boolean true if the statement was found and removed, otherwise false
     */
    public function removeStatement($propertyName, $locale = null) {
        // Remove the statement if it exists
        if (isset($propertyName) && $this->hasData($propertyName, $locale)) {
            $this->setData($propertyName, null, $locale);
            return true;
        }

        return false;
    }

    /**
     * Get all statements
     * @return array statements
     */
    public function getStatements() {
        // Retrieve data by value (copy) to ensure unset doesn't affect internal state
        $allData = $this->getAllData();

        // Unset data variables that are not statements
        unset($allData['id']);
        return $allData;
    }

    /**
     * Get a specific statement
     * @param $propertyName string
     * @param $locale string
     * @return mixed a scalar property value or an array of property values
     * if the cardinality of the property is 'many'.
     */
    public function getStatement($propertyName, $locale = null) {
        // Check the property
        $property = $this->getProperty($propertyName);
        assert(isset($property) && $property instanceof MetadataProperty);

        // Handle translation
        $translated = $property->getTranslated();
        if (!$translated) assert(is_null($locale));
        if ($translated && !isset($locale)) {
            // Retrieve the current locale
            $locale = AppLocale::getLocale();
        }

        // Retrieve the value
        return $this->getData($propertyName, $locale);
    }

    /**
     * Returns all translations of a translated property
     * @param $propertyName string
     * @return array all translations of a given property; if the
     * property has cardinality "many" then this returns a two-dimensional
     * array whereby the first key represents the locale and the second
     * the translated values.
     */
    public function getStatementTranslations($propertyName) {
        assert($this->isTranslatedProperty($propertyName));
        return $this->getData($propertyName);
    }

    /**
     * Add several statements at once. If one of the statements
     * is invalid then the meta-data description will remain in its
     * initial state.
     * @param $statements array statements
     * @param $replace integer one of the allowed replace levels.
     * @return boolean true if all statements could be added, false otherwise
     */
    public function setStatements($statements, $replace = METADATA_DESCRIPTION_REPLACE_PROPERTIES) {
        // Removed & from $statements parameter
        assert(in_array($replace, $this->_allowedReplaceLevels()));

        // Make a backup copy of all existing statements.
        $statementsBackup = $this->getAllData();

        if ($replace == METADATA_DESCRIPTION_REPLACE_ALL) {
            // Delete existing statements
            $emptyArray = array();
            $this->setAllData($emptyArray);
        }

        // Add statements one by one to detect invalid values.
        foreach($statements as $propertyName => $content) {
            assert(!empty($content));

            // Transform scalars or translated fields to arrays so that
            // we can handle properties with different cardinalities in
            // the same way.
            if (is_scalar($content) || is_string(key($content))) {
                // Removed reference wrapper
                $values = array($content);
            } else {
                $values = $content;
            }

            if ($replace == METADATA_DESCRIPTION_REPLACE_PROPERTIES) {
                $replaceProperty = true;
            } else {
                $replaceProperty = false;
            }

            $valueIndex = 0;
            foreach($values as $value) {
                $firstValue = ($valueIndex == 0) ? true : false;
                // Is this a translated property?
                if (is_array($value)) {
                    foreach($value as $locale => $translation) {
                        // Handle cardinality many and one in the same way
                        if (is_scalar($translation)) {
                            $translationValues = array($translation);
                        } else {
                            $translationValues = $translation;
                        }
                        $translationIndex = 0;
                        foreach($translationValues as $translationValue) {
                            $firstTranslation = ($translationIndex == 0) ? true : false;
                            // Add a statement (replace existing statement if any)
                            if (!($this->addStatement($propertyName, $translationValue, $locale, $firstTranslation && $replaceProperty))) {
                                $this->setAllData($statementsBackup);
                                return false;
                            }
                            $translationIndex++;
                        }
                    }
                } else {
                    // Add a statement (replace existing statement if any)
                    if (!($this->addStatement($propertyName, $value, null, $firstValue && $replaceProperty))) {
                        $this->setAllData($statementsBackup);
                        return false;
                    }
                }
                $valueIndex++;
            }
        }
        return true;
    }

    /**
     * Convenience method that returns the properties of
     * the underlying meta-data schema.
     * @return array an array of MetadataProperties
     */
    public function getProperties() {
        $metadataSchema = $this->getMetadataSchema();
        return $metadataSchema->getProperties();
    }

    /**
     * Convenience method that returns a property from
     * the underlying meta-data schema.
     * @param $propertyName string
     * @return MetadataProperty
     */
    public function getProperty($propertyName) {
        $metadataSchema = $this->getMetadataSchema();
        return $metadataSchema->getProperty($propertyName);
    }

    /**
     * Convenience method that returns a property id
     * the underlying meta-data schema.
     * @param $propertyName string
     * @return string
     */
    public function getNamespacedPropertyId($propertyName) {
        $metadataSchema = $this->getMetadataSchema();
        return $metadataSchema->getNamespacedPropertyId($propertyName);
    }

    /**
     * Convenience method that returns the valid
     * property names of the underlying meta-data schema.
     * @return array an array of string values representing valid property names
     */
    public function getPropertyNames() {
        $metadataSchema = $this->getMetadataSchema();
        return $metadataSchema->getPropertyNames();
    }

    /**
     * Convenience method that returns the names of properties with a
     * given data type of the underlying meta-data schema.
     * @param $propertyType string
     * @return array an array of string values representing valid property names
     */
    public function getPropertyNamesByType($propertyType) {
        $metadataSchema = $this->getMetadataSchema();
        return $metadataSchema->getPropertyNamesByType($propertyType);
    }

    /**
     * Returns an array of property names for
     * which statements exist.
     * @return array an array of string values representing valid property names
     */
    public function getSetPropertyNames() {
        return array_keys($this->getStatements());
    }

    /**
     * Convenience method that checks the existence
     * of a property in the underlying meta-data schema.
     * @param $propertyName string
     * @return boolean
     */
    public function hasProperty($propertyName) {
        $metadataSchema = $this->getMetadataSchema();
        return $metadataSchema->hasProperty($propertyName);
    }

    /**
     * Check the existence of a statement for the given property.
     * @param $propertyName string
     * @return boolean
     */
    public function hasStatement($propertyName) {
        $statements = $this->getStatements();
        return (isset($statements[$propertyName]));
    }

    /**
     * Convenience method that checks whether a given property
     * is translated.
     * @param $propertyName string
     * @return boolean
     */
    public function isTranslatedProperty($propertyName) {
        $property = $this->getProperty($propertyName);
        assert($property instanceof MetadataProperty);
        return $property->getTranslated();
    }


    //
    // Private helper methods
    //
    /**
     * The allowed replace levels for the
     * setStatements() method.
     * @return array
     */
    public function _allowedReplaceLevels() {
        static $allowedReplaceLevels = array(
            METADATA_DESCRIPTION_REPLACE_ALL,
            METADATA_DESCRIPTION_REPLACE_PROPERTIES,
            METADATA_DESCRIPTION_REPLACE_NOTHING
        );
        return $allowedReplaceLevels;
    }
}
?>