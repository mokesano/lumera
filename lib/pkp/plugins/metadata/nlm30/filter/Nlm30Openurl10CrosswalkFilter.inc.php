<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30Openurl10CrosswalkFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30Openurl10CrosswalkFilter
 * @ingroup plugins_metadata_nlm30_filter
 * @see Nlm30CitationSchema
 * @see Openurl10BookSchema
 * @see Openurl10JournalSchema
 * @see Openurl10DissertationSchema
 *
 * @brief Filter that converts from NLM citation to
 * OpenURL schemas.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.metadata.CrosswalkFilter');
import('lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema');
import('lib.pkp.plugins.metadata.openurl10.schema.Openurl10JournalSchema');
import('lib.pkp.plugins.metadata.openurl10.schema.Openurl10BookSchema');
import('lib.pkp.plugins.metadata.openurl10.schema.Openurl10DissertationSchema');

class Nlm30Openurl10CrosswalkFilter extends CrosswalkFilter {
    
    /**
     * Constructor
     * @param string $fromSchema fully qualified class name of supported input meta-data schema
     * @param string $toSchema fully qualified class name of supported output meta-data schema
     */
    public function __construct($fromSchema = 'lib.pkp.plugins.metadata.nlm30.schema.Nlm30CitationSchema', $toSchema = 'lib.pkp.plugins.metadata.openurl10.schema.Openurl10BaseSchema') {
        $this->setDisplayName('Crosswalk from NLM Citation to Open URL');
        parent::__construct($fromSchema, $toSchema);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30Openurl10CrosswalkFilter($fromSchema = null, $toSchema = null) {
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
    // Protected helper methods
    //
    /**
     * Create a mapping of NLM properties to OpenURL
     * properties that do not need special processing.
     * [WIZDAM FIX] Added $input parameter because it was used inside but undefined.
     * [WIZDAM FIX] Removed references (&) for PHP 8 compatibility.
     * @param int $publicationType The NLM publication type
     * @param MetadataSchema $openurl10Schema
     * @param MetadataDescription|null $input
     * @return array
     */
    public function nlmOpenurl10Mapping($publicationType, $openurl10Schema, $input = null) {
        $propertyMap = [];

        // Map titles and date
        switch($publicationType) {
            case NLM30_PUBLICATION_TYPE_JOURNAL:
                $propertyMap['source'] = 'jtitle';
                $propertyMap['article-title'] = 'atitle';
                break;

            case NLM30_PUBLICATION_TYPE_CONFPROC:
                $propertyMap['conf-name'] = 'jtitle';
                $propertyMap['article-title'] = 'atitle';
                // [WIZDAM FIX] Ensure $input is available
                if ($input && $input->hasStatement('conf-date')) {
                    $propertyMap['conf-date'] = 'date';
                }
                break;

            case NLM30_PUBLICATION_TYPE_BOOK:
                $propertyMap['source'] = 'btitle';
                $propertyMap['chapter-title'] = 'atitle';
                break;

            case NLM30_PUBLICATION_TYPE_THESIS:
                $propertyMap['article-title'] = 'title';
                break;
        }

        // Map the date (if it's not already mapped).
        if (!isset($propertyMap['conf-date'])) {
            $propertyMap['date'] = 'date';
        }

        // ISBN is common to all OpenURL schemas and
        // can be mapped one-to-one.
        $propertyMap['isbn'] = 'isbn';

        // Properties common to OpenURL book and journal
        if ($openurl10Schema instanceof Openurl10JournalBookBaseSchema) {
            // Some properties can be mapped one-to-one
            $propertyMap += [
                'issn[@pub-type="ppub"]' => 'issn',
                'fpage' => 'spage',
                'lpage' => 'epage'
            ];

            // FIXME: Map 'aucorp' for OpenURL journal/book when we
            // have 'collab' statements in NLM citation.
        }

        // OpenURL journal properties
        // The properties 'chron' and 'quarter' remain unmatched.
        if ($openurl10Schema instanceof Openurl10JournalSchema) {
            $propertyMap += [
                'season' => 'ssn',
                'volume' => 'volume',
                'supplement' => 'part',
                'issue' => 'issue',
                'issn[@pub-type="epub"]' => 'eissn',
                'pub-id[@pub-id-type="publisher-id"]' => 'artnum',
                'pub-id[@pub-id-type="coden"]' => 'coden',
                'pub-id[@pub-id-type="sici"]' => 'sici'
            ];
        }

        // OpenURL book properties
        // The 'bici' property remains unmatched.
        if ($openurl10Schema instanceof Openurl10BookSchema) {
            $propertyMap += [
                'publisher-loc' => 'place',
                'publisher-name' => 'pub',
                'edition' => 'edition',
                'size' => 'tpages',
                'series' => 'series'
            ];
        }

        // OpenURL dissertation properties
        // The properties 'cc', 'advisor' and 'degree' remain unmatched
        // as NLM does not have good dissertation support.
        if ($openurl10Schema instanceof Openurl10DissertationSchema) {
            $propertyMap += [
                'size' => 'tpages',
                'publisher-loc' => 'co',
                'institution' => 'inst'
            ];
        }

        return $propertyMap;
    }
}
?>