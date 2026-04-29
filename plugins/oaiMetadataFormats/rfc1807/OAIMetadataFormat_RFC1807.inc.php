<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/rfc1807/OAIMetadataFormat_RFC1807.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_RFC1807
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- RFC 1807.
 * * REFACTORED: Wizdam Edition (Correct Copyright Routing)
 */

class OAIMetadataFormat_RFC1807 extends OAIMetadataFormat {
    
    /**
     * Constructor
     */
    public function __construct($prefix, $schema, $namespace) {
        parent::__construct($prefix, $schema, $namespace);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormat_RFC1807($prefix, $schema, $namespace) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::OAIMetadataFormat_RFC1807(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($prefix, $schema, $namespace);
    }

    /**
     * Convert an OAI record to RFC 1807 XML format.
     * @see OAIMetadataFormat#toXml
     * @param $record OAIRecord
     * @param $format string
     * @return string XML data
     */
    public function toXml($record, $format = null) {
        $article = $record->getData('article');
        $journal = $record->getData('journal');
        $section = $record->getData('section');
        $issue = $record->getData('issue');
        
        // Publisher
        $publisher = $journal->getLocalizedTitle(); 
        $publisherInstitution = $journal->getLocalizedSetting('publisherInstitution');
        if (!empty($publisherInstitution)) {
            $publisher = $publisherInstitution;
        }

        // Sources
        $source = $issue->getIssueIdentification();
        $pages = $article->getPages();
        if (!empty($pages)) $source .= '; ' . $pages;

        // Relation (SuppFiles)
        $relation = array();
        foreach ($article->getSuppFiles() as $suppFile) {
            $relation[] = Request::url($journal->getPath(), 'article', 'download', array($article->getId(), $suppFile->getFileId()));
        }

        // Creators
        $creators = array();
        $authors = $article->getAuthors();
        for ($i = 0, $num = count($authors); $i < $num; $i++) {
            $authorName = $authors[$i]->getFullName(true);
            $affiliation = $authors[$i]->getLocalizedAffiliation();
            if (!empty($affiliation)) {
                $authorName .= '; ' . $affiliation;
            }
            $creators[] = $authorName;
        }

        // Subject (Flattened)
        $subjects = array_merge_recursive(
            $this->stripAssocArray((array) $article->getDiscipline(null)),
            $this->stripAssocArray((array) $article->getSubject(null)),
            $this->stripAssocArray((array) $article->getSubjectClass(null))
        );
        
        $subject = '';
        $locale = $journal->getPrimaryLocale();
        
        if (isset($subjects[$locale])) {
            $rawSubject = $subjects[$locale];
            if (is_array($rawSubject)) {
                $cleanSubjects = array_filter($rawSubject, function($value) {
                    return !is_null($value) && $value !== '';
                });
                $subject = implode('; ', $cleanSubjects);
            } else {
                $subject = $rawSubject;
            }
        }

        // Coverage
        $coverage = array_filter(array(
            $article->getLocalizedCoverageGeo(),
            $article->getLocalizedCoverageChron(),
            $article->getLocalizedCoverageSample()
        ));

        // URL
        $url = Request::url($journal->getPath(), 'article', 'view', array($article->getBestArticleId()));

        // OPTIMIZED COPYRIGHT LOGIC (Wizdam Routing Fix)
        $copyrightUrl = Request::url($journal->getPath(), 'policies', 'copyright');
        
        // Format: [URL] Spasi Teks Penjelasan
        $copyrightNotice = '[' . $copyrightUrl . '] Copyright policy is available at the provided URL.';
        
        $this->formatElement('copyright', $copyrightNotice) .

        // XML Construction
        $response = "<rfc1807\n" .
            "\txmlns=\"http://info.internet.isi.edu:80/in-notes/rfc/files/rfc1807.txt\"\n" .
            "\txmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n" .
            "\txsi:schemaLocation=\"http://info.internet.isi.edu:80/in-notes/rfc/files/rfc1807.txt\n" .
            "\thttp://www.openarchives.org/OAI/1.1/rfc1807.xsd\">\n" .
            "\t<bib-version>v2</bib-version>\n" .
            
            $this->formatElement('id', $url) .
            $this->formatElement('entry', $record->datestamp) .
            $this->formatElement('organization', $publisher) .
            $this->formatElement('organization', $source) .
            $this->formatElement('title', $article->getLocalizedTitle()) .
            $this->formatElement('type', $section->getLocalizedIdentifyType()) .
            $this->formatElement('type', $relation) .
            $this->formatElement('author', $creators) .
            ($article->getDatePublished() ? $this->formatElement('date', $article->getDatePublished()) : '') .
            
            // Output Optimized Copyright
            $this->formatElement('copyright', $copyrightNotice) .
            
            $this->formatElement('other_access', "url:$url") .
            $this->formatElement('keyword', $subject) .
            $this->formatElement('period', $coverage) .
            $this->formatElement('monitoring', $article->getLocalizedSponsor()) .
            $this->formatElement('language', $article->getLanguage()) .
            $this->formatElement('abstract', strip_tags($article->getLocalizedAbstract())) .
            
            "</rfc1807>\n";

        return $response;
    }

    /**
     * Format XML for single RFC 1807 element.
     * @param $name string
     * @param $value mixed
     * @return string XML data
     */
    public function formatElement($name, $value) {
        if (!is_array($value)) {
            $value = array($value);
        }

        $response = '';
        foreach ($value as $v) {
            if ($v === null || $v === '') continue;
            $cleanValue = OAIUtils::prepOutput($v);
            $response .= "\t<$name>$cleanValue</$name>\n";
        }
        return $response;
    }
}

?>