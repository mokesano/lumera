<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/marc/OAIMetadataFormat_MARC.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_MARC
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- MARC.
 * * REFACTORED: Wizdam Edition (Pure PHP Rendering - Fixed Locale)
 */

class OAIMetadataFormat_MARC extends OAIMetadataFormat {
    
    /**
     * Constructor
     */
    public function __construct($prefix, $schema, $namespace) {
        parent::__construct($prefix, $schema, $namespace);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormat_MARC($prefix, $schema, $namespace) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::OAIMetadataFormat_MARC(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($prefix, $schema, $namespace);
    }

    /**
     * Convert the record to the MARC XML format.
     * @see OAIMetadataFormat#toXml
     * @param $record OAIRecord
     * @param $format string
     * @return string XML data
     */
    public function toXml($record, $format = null) {
        // 1. Data Preparation
        $article = $record->getData('article');
        $journal = $record->getData('journal');
        $issue = $record->getData('issue');
        $section = $record->getData('section');
        $locale = $journal->getPrimaryLocale();

        // 2. Header Construction (Single Line for Safety)
        $response = '<oai_marc status="c" type="a" level="m" encLvl="3" catForm="u" xmlns="http://www.openarchives.org/OAI/1.1/oai_marc" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/oai_marc http://www.openarchives.org/OAI/1.1/oai_marc.xsd">' . "\n";

        // 3. Field 008: Date Published + Lang
        if ($article->getDatePublished()) {
            $dateStr = date('ymd Y', strtotime($article->getDatePublished()));
            $response .= "\t<fixfield id=\"008\">\"" . $dateStr . "                        eng  \"</fixfield>\n";
        }

        // 4. Field 022: ISSN
        if ($journal->getSetting('onlineIssn')) {
            $response .= $this->formatVarField('022', '#', '#', 'a', $journal->getSetting('onlineIssn'));
        }
        if ($journal->getSetting('printIssn')) {
            $response .= $this->formatVarField('022', '#', '#', 'a', $journal->getSetting('printIssn'));
        }

        // 5. Field 042: DC Source
        $response .= $this->formatVarField('042', ' ', ' ', 'a', 'dc');

        // 6. Field 245: Title
        $response .= $this->formatVarField('245', '0', '0', 'a', $article->getTitle($locale));

        // 7. Field 100/720: Authors
        $authors = $article->getAuthors();
        foreach ($authors as $author) {
            $tagId = (count($authors) == 1) ? '100' : '720';
            $subfields = array('a' => $author->getFullName(true));
            
            $affiliation = $author->getAffiliation($locale);
            if ($affiliation) {
                $subfields['u'] = $affiliation;
            }
            if ($author->getUrl()) {
                $subfields['0'] = $author->getUrl();
            }
            if ($author->getData('orcid')) {
                 $subfields['0_orcid'] = $author->getData('orcid'); 
            }
            
            $response .= $this->formatVarFieldComplex($tagId, '1', ' ', $subfields);
        }

        // 8. Field 653: Subject (With Array Flattening Fix)
        $subjects = array_merge_recursive(
            $this->stripAssocArray((array) $article->getDiscipline(null)),
            $this->stripAssocArray((array) $article->getSubject(null)),
            $this->stripAssocArray((array) $article->getSubjectClass(null))
        );
        
        if (isset($subjects[$locale])) {
            $rawSubject = $subjects[$locale];
            if (is_array($rawSubject)) {
                $cleanSubjects = array_filter($rawSubject, function($value) {
                    return !is_null($value) && $value !== '';
                });
                $subjectStr = implode('; ', $cleanSubjects);
            } else {
                $subjectStr = $rawSubject;
            }
            
            if (!empty($subjectStr)) {
                $response .= $this->formatVarField('653', ' ', ' ', 'a', $subjectStr);
            }
        }

        // 9. Field 520: Abstract
        $abstract = PKPString::html2text($article->getAbstract($article->getLocale()));
        if (!empty($abstract)) {
             $response .= $this->formatVarField('520', ' ', ' ', 'a', $abstract);
        }

        // 10. Field 260: Publisher & Date
        $publisher = $journal->getTitle($locale);
        if ($journal->getSetting('publisherInstitution')) {
            $publisher = $journal->getSetting('publisherInstitution');
        }
        $response .= $this->formatVarField('260', ' ', ' ', 'b', $publisher);
        $response .= $this->formatVarField('260', ' ', ' ', 'c', $issue->getDatePublished());

        // 11. Field 655: Type
        $identifyType = $section->getIdentifyType($locale);
        if ($identifyType) {
            $response .= $this->formatVarField('655', ' ', '7', 'a', $identifyType);
        }

        // 12. Field 856: Galleys
        foreach ($article->getGalleys() as $galley) {
            $response .= $this->formatVarField('856', ' ', ' ', 'q', $galley->getFileType());
        }
        
        // URL
        $url = Request::url($journal->getPath(), 'article', 'view', array($article->getBestArticleId()));
        $response .= $this->formatVarField('856', '4', '0', 'u', $url);

        // 13. Field 786: Source
        $source = $journal->getTitle($locale) . '; ' . $issue->getIssueIdentification();
        $response .= $this->formatVarField('786', '0', ' ', 'n', $source);

        // 14. Field 546: Language
        $language = AppLocale::get3LetterIsoFromLocale($article->getLocale());
        $response .= $this->formatVarField('546', ' ', ' ', 'a', $language);

        // 15. Field 787: SuppFiles
        foreach ($article->getSuppFiles() as $suppFile) {
            $suppUrl = Request::url($journal->getPath(), 'article', 'download', array($article->getId(), $suppFile->getFileId()));
            $response .= $this->formatVarField('787', '0', ' ', 'n', $suppUrl);
        }

        // 16. Field 500: Coverage
        if ($geo = $article->getCoverageGeo($locale)) {
            $response .= $this->formatVarField('500', ' ', ' ', 'a', $geo);
        }
        if ($chron = $article->getCoverageChron($locale)) {
            $response .= $this->formatVarField('500', ' ', ' ', 'a', $chron);
        }
        if ($sample = $article->getCoverageSample($locale)) {
            $response .= $this->formatVarField('500', ' ', ' ', 'a', $sample);
        }

        // 17. Field 540: Copyright
        // WIZDAM FIX: Replaced Locale::translate with global helper __()
        $copyrightKey = "submission.copyrightStatement";
        $copyright = __($copyrightKey, array(
            'copyrightYear' => $article->getCopyrightYear(),
            'copyrightHolder' => $article->getCopyrightHolder($locale)
        ));
        $response .= $this->formatVarField('540', ' ', ' ', 'a', $copyright);

        // Footer
        $response .= "</oai_marc>\n";

        return $response;
    }

    // --- Private Helper Methods for XML Generation ---
    /**
     * Helper to create a simple varfield with one subfield
     * @return string XML fragment
     * @param $id string
     * @param $i1 string
     * @param $i2 string
     * @param $subfieldLabel string
     * @param $value string
     */
    public function formatVarField($id, $i1, $i2, $subfieldLabel, $value) {
        // Sanitize
        $value = OAIUtils::prepOutput($value);
        
        return "\t<varfield id=\"$id\" i1=\"$i1\" i2=\"$i2\">\n" .
               "\t\t<subfield label=\"$subfieldLabel\">$value</subfield>\n" .
               "\t</varfield>\n";
    }

    /**
     * Helper to create a varfield with multiple subfields
     * @param $subfields array Associative array of label=>value pairs
     * @return string XML fragment
     * @param $id string
     * @param $i1 string
     * @param $i2 string
     * @param $subfields array
     */
    public function formatVarFieldComplex($id, $i1, $i2, $subfields) {
        $out = "\t<varfield id=\"$id\" i1=\"$i1\" i2=\"$i2\">\n";
        foreach ($subfields as $label => $value) {
            // Handle special case for Orcid duplicate key handling trick
            if ($label === '0_orcid') $label = '0';
            
            $value = OAIUtils::prepOutput($value);
            $out .= "\t\t<subfield label=\"$label\">$value</subfield>\n";
        }
        $out .= "\t</varfield>\n";
        return $out;
    }
}
?>