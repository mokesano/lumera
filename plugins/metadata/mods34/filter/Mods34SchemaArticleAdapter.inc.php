<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_mods34_filter
 */

/**
 * @file plugins/metadata/mods34/filter/Mods34SchemaArticleAdapter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34SchemaArticleAdapter
 * @ingroup plugins_metadata_mods34_filter
 * @see Article
 * @see PublishedArticle
 * @see Mods34Schema
 *
 * @brief Class that inject/extract MODS schema compliant meta-data
 * into/from an Article or PublishedArticle object.
 * * [WIZDAM EDITION] REFACTOR: PHP 8.1+ Signature Compatibility Fix
 */

import('lib.pkp.plugins.metadata.mods34.filter.Mods34SchemaSubmissionAdapter');

class Mods34SchemaArticleAdapter extends Mods34SchemaSubmissionAdapter {

    /**
     * Constructor
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup) {
        // Configure the submission adapter
        parent::__construct($filterGroup);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Mods34SchemaArticleAdapter($filterGroup) {
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
    // Implement template methods from Filter
    //
    /**
     * Get the class name of the filter.
     * @see Filter::getClassName()
     * @return string
     */
    public function getClassName(): string {
        return 'plugins.metadata.mods34.filter.Mods34SchemaArticleAdapter';
    }

    //
    // Implement template methods from MetadataDataObjectAdapter
    //
    /**
     * Inject metadata into an Article object.
     * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
     * 
     * [WIZDAM FIX] Added $authorClassName parameter to match Parent Signature.
     * Note: Internal logic still forces 'classes.article.Author' specifically for Articles.
     * 
     * @param MetadataDescription $metadataDescription
     * @param DataObject $targetDataObject
     * @param string $authorClassName (Default 'Author' to match parent)
     * @return DataObject
     */
    public function injectMetadataIntoDataObject($metadataDescription, $targetDataObject, string $authorClassName = 'Author') {
        // Mapping variables from argument
        $mods34Description = $metadataDescription;
        $article = $targetDataObject;
        
        // Ensure strictly Article
        if (!$article instanceof Article) {
            assert(false); // Atau throw exception di production modern
            return $article;
        }
        
        // Pass specific author class name for OJS articles.
        // We intentionally ignore the $authorClassName passed in argument (it's there just for signature match)
        // and force 'classes.article.Author'.
        $article = parent::injectMetadataIntoDataObject($mods34Description, $article, 'classes.article.Author');

        // ...
        // [WIZDAM NOTE] The logic below is marked as FIXME in original code.
        // It requires mapping MODS specific fields to OJS Journal/Issue settings.
        // FIXME: Go through MODS schema and see what context-specific
        // information needs to be added, e.g. from Article, PublishedArticle
        // Issue, Journal, journal settings or site settings.

        return $article;
    }

    /**
     * Extract metadata from an Article object.
     * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
     * 
     * @param Article $submission
     * @param string $authorMarcrelatorRole
     * @return MetadataDescription
     */
    public function extractMetadataFromDataObject($submission, $authorMarcrelatorRole = 'aut') {
        $article = $submission;
        
        if (!$article instanceof Article) {
             // Fail safe
             return new MetadataDescription('lib.pkp.plugins.metadata.mods34.schema.Mods34Schema', ASSOC_TYPE_ARTICLE);
        }

        // Extract meta-data from the submission.
        $mods34Description = parent::extractMetadataFromDataObject($article, 'aut');

        return $mods34Description;
    }
}

?>