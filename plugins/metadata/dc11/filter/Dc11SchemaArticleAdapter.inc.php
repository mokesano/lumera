<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/dc11/filter/Dc11SchemaArticleAdapter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Dc11SchemaArticleAdapter
 * @ingroup plugins_metadata_dc11_filter
 * @see Article
 * @see PKPDc11Schema
 *
 * @brief Abstract base class for meta-data adapters that
 * injects/extracts Dublin Core schema compliant meta-data into/from
 * an PublishedArticle object.
 */

import('lib.pkp.classes.metadata.MetadataDataObjectAdapter');

class Dc11SchemaArticleAdapter extends MetadataDataObjectAdapter {
    
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
    public function Dc11SchemaArticleAdapter($filterGroup) {
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
        return 'plugins.metadata.dc11.filter.Dc11SchemaArticleAdapter';
    }

    //
    // Implement template methods from MetadataDataObjectAdapter
    //
    /**
     * Injects metadata from a MetadataDescription into a DataObject.
     * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
     * 
     * @param MetadataDescription $metadataDescription
     * @param Article $targetDataObject
     * @return DataObject|bool False if not implemented
     */
    public function injectMetadataIntoDataObject($metadataDescription, $targetDataObject) {
        // Not implemented
        assert(false);
        $returner = false;
        return $returner;
    }

    /**
     * Extracts metadata from a DataObject into a MetadataDescription.
     * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
     * 
     * @param Article $article
     * @return MetadataDescription
     */
    public function extractMetadataFromDataObject($article) {
        assert($article instanceof Article);

        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

        // [WIZDAM PERFORMANCE FIX]
        // Menggunakan Static Variable sebagai Micro-Cache untuk mencegah N+1 Query Problem
        // saat melakukan harvesting OAI masal, tanpa bergantung pada OAIDAO.
        static $journalCache = [];
        static $sectionCache = [];
        static $issueCache = [];

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');

        // 1. Get Journal (With Cache)
        $journalId = $article->getJournalId();
        if (!isset($journalCache[$journalId])) {
            $journalCache[$journalId] = $journalDao->getById($journalId);
        }
        $journal = $journalCache[$journalId];

        // 2. Get Section (With Cache)
        $sectionId = $article->getSectionId();
        if (!isset($sectionCache[$sectionId])) {
            $sectionCache[$sectionId] = $sectionDao->getSection($sectionId);
        }
        $section = $sectionCache[$sectionId];
        
        $issue = null;
        if ($article instanceof PublishedArticle) {
            // 3. Get Issue (With Cache)
            $issueId = $article->getIssueId();
            if (!isset($issueCache[$issueId])) {
                $issueCache[$issueId] = $issueDao->getIssueById($issueId);
            }
            $issue = $issueCache[$issueId];
        }

        $dc11Description = $this->instantiateMetadataDescription();

        // ... (SISA KODE KE BAWAH SAMA PERSIS SEPERTI SEBELUMNYA) ...
        
        // Title
        $this->_addLocalizedElements($dc11Description, 'dc:title', $article->getTitle(null));

        // Creator
        $authors = $article->getAuthors();
        foreach($authors as $author) {
            $dc11Description->addStatement('dc:creator', $author->getFullName(true));
        }

        // Subject
        $subjects = array_merge_recursive(
                (array) $article->getDiscipline(null),
                (array) $article->getSubject(null),
                (array) $article->getSubjectClass(null));
        $this->_addLocalizedElements($dc11Description, 'dc:subject', $subjects);

        // Description
        $this->_addLocalizedElements($dc11Description, 'dc:description', $article->getAbstract(null));

        // Publisher
        $publisherInstitution = $journal->getSetting('publisherInstitution');
        if (!empty($publisherInstitution)) {
            $publishers = [$journal->getPrimaryLocale() => $publisherInstitution];
        } else {
            $publishers = $journal->getTitle(null); // Default
        }
        $this->_addLocalizedElements($dc11Description, 'dc:publisher', $publishers);

        // Contributor
        $contributors = (array) $article->getSponsor(null);
        foreach ($contributors as $locale => $contributor) {
            $contributors[$locale] = array_map('trim', explode(';', $contributor));
        }
        $this->_addLocalizedElements($dc11Description, 'dc:contributor', $contributors);


        // Date
        if ($article instanceof PublishedArticle) {
            if ($article->getDatePublished()) {
                $dc11Description->addStatement('dc:date', date('Y-m-d', strtotime($article->getDatePublished())));
            } elseif ($issue && $issue->getDatePublished()) {
                $dc11Description->addStatement('dc:date', date('Y-m-d', strtotime($issue->getDatePublished())));
            }
        }

        // Type
        $driverType = 'info:eu-repo/semantics/article';
        $dc11Description->addStatement('dc:type', $driverType, METADATA_DESCRIPTION_UNKNOWN_LOCALE);
        $types = $section->getIdentifyType(null);
        $types = array_merge_recursive(
            empty($types) ? [AppLocale::getLocale() => __('rt.metadata.pkp.peerReviewed')] : $types,
            (array) $article->getType(null)
        );
        $this->_addLocalizedElements($dc11Description, 'dc:type', $types);
        $driverVersion = 'info:eu-repo/semantics/publishedVersion';
        $dc11Description->addStatement('dc:type', $driverVersion, METADATA_DESCRIPTION_UNKNOWN_LOCALE);


        // Format
        if ($article instanceof PublishedArticle) {
            $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $articleGalleyDao ArticleGalleyDAO */
            $galleys = $articleGalleyDao->getGalleysByArticle($article->getId());
            foreach ($galleys as $galley) {
                $dc11Description->addStatement('dc:format', $galley->getFileType());
            }
        }

        // Identifier: URL
        if ($article instanceof PublishedArticle) {
            $dc11Description->addStatement('dc:identifier', Request::url($journal->getPath(), 'article', 'view', [$article->getBestArticleId()]));
        }

        // Source (journal title, issue id and pages)
        $sources = $journal->getTitle(null);
        $pages = $article->getPages();
        if (!empty($pages)) $pages = '; ' . $pages;
        foreach ($sources as $locale => $source) {
            if ($article instanceof PublishedArticle && $issue) {
                $sources[$locale] .= '; ' . $issue->getIssueIdentification();
            }
            $sources[$locale] .=  $pages;
        }
        $this->_addLocalizedElements($dc11Description, 'dc:source', $sources);
        if ($issn = $journal->getSetting('onlineIssn')) {
            $dc11Description->addStatement('dc:source', $issn, METADATA_DESCRIPTION_UNKNOWN_LOCALE);
        }
        if ($issn = $journal->getSetting('printIssn')) {
            $dc11Description->addStatement('dc:source', $issn, METADATA_DESCRIPTION_UNKNOWN_LOCALE);
        }

        // Get galleys and supp files.
        $galleys = [];
        $suppFiles = [];
        if ($article instanceof PublishedArticle) {
            $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $articleGalleyDao ArticleGalleyDAO */
            $galleys = $articleGalleyDao->getGalleysByArticle($article->getId());
            $suppFiles = $article->getSuppFiles();
        }

        // Language
        $locales = [];
        if ($article instanceof PublishedArticle) {
            foreach ($galleys as $galley) {
                $galleyLocale = $galley->getLocale();
                if(!is_null($galleyLocale) && !in_array($galleyLocale, $locales)) {
                    $locales[] = $galleyLocale;
                    $dc11Description->addStatement('dc:language', AppLocale::getIso3FromLocale($galleyLocale));
                }
            }
        }
        $articleLanguage = $article->getLanguage();
        if (empty($locales) && !empty($articleLanguage)) {
            $dc11Description->addStatement('dc:language', strip_tags($articleLanguage));
        }

        // Relation
        // full text URLs
        foreach ($galleys as $galley) {
            $relation = Request::url($journal->getPath(), 'article', 'view', [$article->getBestArticleId($journal), $galley->getBestGalleyId($journal)]);
            $dc11Description->addStatement('dc:relation', $relation);
        }
        // supp file URLs
        foreach ($suppFiles as $suppFile) {
            $relation = Request::url($journal->getPath(), 'article', 'downloadSuppFile', [$article->getBestArticleId($journal), $suppFile->getBestSuppFileId($journal)]);
            $dc11Description->addStatement('dc:relation', $relation);
        }

        // Public identifiers
        $pubIdPlugins = (array) PluginRegistry::loadCategory('pubIds', true, $journal->getId());
        foreach ($pubIdPlugins as $pubIdPlugin) {
            if ($issue && ($pubIssueId = $pubIdPlugin->getPubId($issue))) {
                $dc11Description->addStatement('dc:source', $pubIssueId, METADATA_DESCRIPTION_UNKNOWN_LOCALE);
            }
            if ($pubArticleId = $pubIdPlugin->getPubId($article)) {
                $dc11Description->addStatement('dc:identifier', $pubArticleId);
            }
            foreach ($galleys as $galley) {
                if ($pubGalleyId = $pubIdPlugin->getPubId($galley)) {
                    $dc11Description->addStatement('dc:relation', $pubGalleyId);
                }
            }
            foreach ($suppFiles as $suppFile) {
                if ($pubSuppFileId = $pubIdPlugin->getPubId($suppFile)) {
                    $dc11Description->addStatement('dc:relation', $pubSuppFileId);
                }
            }
        }

        // Coverage
        $coverage = array_merge_recursive(
                (array) $article->getCoverageGeo(null),
                (array) $article->getCoverageChron(null),
                (array) $article->getCoverageSample(null));
        $this->_addLocalizedElements($dc11Description, 'dc:coverage', $coverage);

        // Rights: Add both copyright statement and license
        $copyrightHolder = $article->getLocalizedCopyrightHolder();
        $copyrightYear = $article->getCopyrightYear();
        if (!empty($copyrightHolder) && !empty($copyrightYear)) {
            $dc11Description->addStatement('dc:rights', __('submission.copyrightStatement', ['copyrightHolder' => $copyrightHolder, 'copyrightYear' => $copyrightYear]));
        }

        if ($licenseUrl = $article->getLicenseURL()) {
            $dc11Description->addStatement('dc:rights', $licenseUrl);
        }

        // [WIZDAM PROTOCOL] Modernized Hook Dispatch
        HookRegistry::dispatch('Dc11SchemaArticleAdapter::extractMetadataFromDataObject', [&$this, $article, $journal, $issue, &$dc11Description]);

        return $dc11Description;
    }

    /**
     * Get the names of all metadata fields that can be
     * mapped to/from a DataObject.
     * @see MetadataDataObjectAdapter::getDataObjectMetadataFieldNames()
     * 
     * @param bool $translated
     * @return array
     */
    public function getDataObjectMetadataFieldNames($translated = true) {
        // All DC fields are mapped.
        return [];
    }

    //
    // Private helper methods
    //
    /**
     * Add an array of localized values to the given description.
     * @param MetadataDescription $description
     * @param string $propertyName
     * @param array $localizedValues
     */
    protected function _addLocalizedElements($description, $propertyName, $localizedValues) {
        foreach(stripAssocArray((array) $localizedValues) as $locale => $values) {
            if (is_scalar($values)) $values = [$values];
            foreach($values as $value) {
                $description->addStatement($propertyName, $value, $locale);
            }
        }
    }
}
?>