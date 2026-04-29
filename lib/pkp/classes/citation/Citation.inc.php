<?php
declare(strict_types=1);

/**
 * @defgroup citation
 */

/**
 * @file classes/citation/Citation.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Citation
 * @ingroup citation
 * @see MetadataDescription
 *
 * @brief Class representing a citation (bibliographic reference)
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

define('CITATION_RAW', 0x01);
define('CITATION_CHECKED', 0x02);
define('CITATION_PARSED', 0x03);
define('CITATION_LOOKED_UP', 0x04);
define('CITATION_APPROVED', 0x05);

import('lib.pkp.classes.core.DataObject');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema');
import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30CitationSchemaCitationAdapter');

class Citation extends DataObject {
    /** @var int citation state (raw, edited, parsed, looked-up) */
    protected $_citationState = CITATION_RAW;

    /** @var array an array of MetadataDescriptions */
    protected $_sourceDescriptions = [];

    /** @var int the max sequence number that has been attributed so far */
    protected $_maxSourceDescriptionSeq = 0;

    /**
     * @var array errors that occurred while
     * checking or filtering the citation.
     */
    protected $_errors = [];

    /**
     * Constructor.
     * @param string|null $rawCitation an unparsed citation string
     */
    public function __construct($rawCitation = null) {
        // Switch on meta-data adapter support.
        $this->setHasLoadableAdapters(true);

        parent::__construct();

        $this->setRawCitation($rawCitation); // this will set state to CITATION_RAW
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Citation() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Getters and Setters
    //
    /**
     * Set meta-data descriptions discovered for this
     * citation from external sources.
     *
     * @param array $sourceDescriptions MetadataDescriptions
     */
    public function setSourceDescriptions($sourceDescriptions) {
        $this->_sourceDescriptions = $sourceDescriptions;
    }

    /**
     * Add a meta-data description discovered for this
     * citation from an external source.
     *
     * @param MetadataDescription $sourceDescription
     * @return int the source description's sequence number.
     */
    public function addSourceDescription($sourceDescription) {
        assert($sourceDescription instanceof MetadataDescription);

        // Identify an appropriate sequence number.
        $seq = $sourceDescription->getSeq();
        if (is_numeric($seq) && $seq > 0) {
            // This description has a pre-set sequence number
            if ($seq > $this->_maxSourceDescriptionSeq) $this->_maxSourceDescriptionSeq = $seq;
        } else {
            // We'll create a sequence number for the description
            $this->_maxSourceDescriptionSeq++;
            $seq = $this->_maxSourceDescriptionSeq;
            $sourceDescription->setSeq($seq);
        }

        // We add descriptions by display name as they are
        // purely informational. This avoids getting duplicates
        // when we update a description.
        $this->_sourceDescriptions[$sourceDescription->getDisplayName()] = $sourceDescription;
        return $seq;
    }

    /**
     * Get all meta-data descriptions discovered for this
     * citation from external sources.
     *
     * @return array MetadataDescriptions
     */
    public function getSourceDescriptions() {
        return $this->_sourceDescriptions;
    }

    /**
     * Get the citationState
     * @return int
     */
    public function getCitationState() {
        return $this->_citationState;
    }

    /**
     * Set the citationState
     * @param int $citationState
     */
    public function setCitationState($citationState) {
        assert(in_array($citationState, Citation::_getSupportedCitationStates()));
        $this->_citationState = $citationState;
    }

    /**
     * Get the association type
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set the association type
     * @param int $assocType
     */
    public function setAssocType($assocType) {
        $this->setData('assocType', $assocType);
    }

    /**
     * Get the association id
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set the association id
     * @param int $assocId
     */
    public function setAssocId($assocId) {
        $this->setData('assocId', $assocId);
    }

    /**
     * Add a checking error
     * @param string $errorMessage
     */
    public function addError($errorMessage) {
        $this->_errors[] = $errorMessage;
    }

    /**
     * Get all checking errors
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * Get the rawCitation
     * @return string
     */
    public function getRawCitation() {
        return $this->getData('rawCitation');
    }

    /**
     * Set the rawCitation
     * @param string $rawCitation
     */
    public function setRawCitation($rawCitation) {
        $rawCitation = $this->_cleanCitationString($rawCitation);
        $this->setData('rawCitation', $rawCitation);
    }

    /**
     * Get the sequence number
     * @return int
     */
    public function getSeq() {
        return $this->getData('seq');
    }

    /**
     * Set the sequence number
     * @param int $seq
     */
    public function setSeq($seq) {
        $this->setData('seq', $seq);
    }

    /**
     * Returns all properties of this citation. The returned
     * array contains the name spaces as key and the property
     * list as values.
     * @return array
     */
    public function getNamespacedMetadataProperties() {
        $metadataSchemas = $this->getSupportedMetadataSchemas();
        $metadataProperties = [];
        foreach($metadataSchemas as $metadataSchema) {
            $metadataProperties[$metadataSchema->getNamespace()] = $metadataSchema->getProperties();
        }
        return $metadataProperties;
    }

    //
    // Private methods
    //
    /**
     * Return supported citation states
     * NB: PHP4 work-around for a private static class member
     * @return array supported citation states
     */
    public static function _getSupportedCitationStates() {
        static $_supportedCitationStates = [
            CITATION_RAW,
            CITATION_CHECKED,
            CITATION_PARSED,
            CITATION_LOOKED_UP,
            CITATION_APPROVED
        ];
        return $_supportedCitationStates;
    }

    /**
     * Take a citation string and clean/normalize it
     * @param string|null $citationString
     * @return string
     */
    public function _cleanCitationString($citationString) {
        // [WIZDAM FIX] Ensure string type to prevent fatal error on trim(null)
        $citationString = (string) $citationString;

        // 1) If the string contains non-UTF8 characters, convert it to UTF-8
        if (Config::getVar('i18n', 'charset_normalization') && !PKPString::utf8_compliant($citationString)) {
            $citationString = PKPString::utf8_normalize($citationString);
        }
        // 2) Strip slashes and whitespace
        $citationString = trim(stripslashes($citationString));

        // 3) Normalize whitespace
        $citationString = PKPString::regexp_replace('/[\s]+/', ' ', $citationString);

        return $citationString;
    }
}
?>