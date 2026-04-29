<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30CitationSchemaCitationAdapter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30CitationSchemaCitationAdapter
 * @ingroup plugins_metadata_nlm30_filter
 * @see Citation
 * @see Nlm30CitationSchema
 *
 * @brief Class that injects/extracts NLM citation schema compliant
 * meta-data into/from a Citation object.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & Reference Compatibility
 */

import('lib.pkp.classes.metadata.MetadataDataObjectAdapter');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30NameSchema');

class Nlm30CitationSchemaCitationAdapter extends MetadataDataObjectAdapter {

    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup) {
        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30CitationSchemaCitationAdapter($filterGroup) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @see PersistableFilter::getClassName()
     * @return string
     */
    public function getClassName() {
        return 'lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaCitationAdapter';
    }

    //
    // Implement template methods from MetadataDataObjectAdapter
    //
    /**
     * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
     * [WIZDAM FIX] Removed references (&) to match parent signature
     * @param MetadataDescription $metadataDescription
     * @param Citation $dataObject
     * @return DataObject
     */
    public function injectMetadataIntoDataObject($metadataDescription, $dataObject) {
        assert($dataObject instanceof Citation);

        // Add new meta-data statements to the citation. Add the schema
        // name space to each property name so that it becomes unique
        // across schemas.
        $metadataSchemaNamespace = $this->getMetadataNamespace();

        $nullVar = null;
        foreach($metadataDescription->getPropertyNames() as $propertyName) {
            $dataObjectKey = $metadataSchemaNamespace.':'.$propertyName;
            if ($metadataDescription->hasStatement($propertyName)) {
                // Directly retrieve the internal data so that we don't
                // have to care about cardinality and translation.
                $value = $metadataDescription->getData($propertyName);
                if (in_array($propertyName, ['person-group[@person-group-type="author"]', 'person-group[@person-group-type="editor"]'])) {
                    assert(is_array($value));

                    // Copy the value to ensure we don't modify the original by ref (though ref logic removed mostly)
                    $tmpValue = $value;
                    $value = $tmpValue;

                    // Convert MetadataDescription objects to simple key/value arrays.
                    foreach($value as $key => $name) {
                        if($name instanceof MetadataDescription) {
                            // A name can either be a full name description...
                            $value[$key] = $name->getAllData();
                        } else {
                            // ...or an 'et-al' string.
                            assert($name == PERSON_STRING_FILTER_ETAL);
                            // No need to change the value encoding.
                        }
                    }
                }
                $dataObject->setData($dataObjectKey, $value);
                unset($value);
            }
        }

        return $dataObject;
    }

    /**
     * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
     * [WIZDAM FIX] Removed reference (&) from return type to match parent signature if needed, 
     * but usually extraction returns a new object so no ref is safer.
     * @param Citation $dataObject
     * @return MetadataDescription
     */
    public function extractMetadataFromDataObject($dataObject) {
        $metadataDescription = $this->instantiateMetadataDescription();

        // Establish the association between the meta-data description
        // and the citation object.
        $metadataDescription->setAssocId($dataObject->getId());

        // Identify the length of the name space prefix
        $namespacePrefixLength = strlen($this->getMetadataNamespace())+1;

        // Get all meta-data field names
        $fieldNames = array_merge($this->getDataObjectMetadataFieldNames(false),
                $this->getDataObjectMetadataFieldNames(true));

        // Retrieve the statements from the data object
        $statements = [];
        foreach($fieldNames as $fieldName) {
            if ($dataObject->hasData($fieldName)) {
                // Remove the name space prefix
                $propertyName = substr($fieldName, $namespacePrefixLength);
                if (in_array($propertyName, ['person-group[@person-group-type="author"]', 'person-group[@person-group-type="editor"]'])) {
                    // Retrieve the names array
                    $names = $dataObject->getData($fieldName);

                    // Convert key/value arrays to MetadataDescription objects.
                    foreach($names as $key => $name) {
                        if (is_array($name)) {
                            // Construct a meta-data description from
                            // this name array.
                            $assocType = 0;
                            switch($propertyName) {
                                case 'person-group[@person-group-type="author"]':
                                    $assocType = ASSOC_TYPE_AUTHOR;
                                    break;

                                case 'person-group[@person-group-type="editor"]':
                                    $assocType = ASSOC_TYPE_EDITOR;
                                    break;
                            }
                            $nameDescription = new MetadataDescription('lib.pkp.plugins.metadata.nlm30.schema.Nlm30NameSchema', $assocType);
                            $nameDescription->setStatements($name);
                            $names[$key] = $nameDescription;
                            unset($nameDescription);
                        } else {
                            // The only non-structured data allowed here
                            // is the et-al string.
                            import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30PersonStringFilter');
                            assert($name == PERSON_STRING_FILTER_ETAL);
                        }
                    }
                    $statements[$propertyName] = $names;
                    unset($names);
                } else {
                    $statements[$propertyName] = $dataObject->getData($fieldName);
                }
            }
        }

        // Set the statements in the meta-data description
        $success = $metadataDescription->setStatements($statements);
        assert($success);

        return $metadataDescription;
    }
}
?>