<?php
declare(strict_types=1);

/**
 * @file plugins/oaiMetadataFormats/marcxml/OAIMetadataFormat_MARC21.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_MARC21
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- MARC21 (XML).
 * * REFACTORED: Wizdam Edition (Fixed Array-to-String on Tag 653)
 */

class OAIMetadataFormat_MARC21 extends OAIMetadataFormat {
    
    /**
     * Constructor
     */
    public function __construct($prefix, $schema, $namespace) {
        parent::__construct($prefix, $schema, $namespace);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OAIMetadataFormat_MARC21($prefix, $schema, $namespace) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::OAIMetadataFormat_MARC21(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($prefix, $schema, $namespace);
    }

    /**
     * Convert the record to MARCXML format.
     * @see OAIMetadataFormat#toXml
     * @param $record OAIRecord
     * @param $format string
     * @return string XML data
     */
    public function toXml($record, $format = null) {
        $article = $record->getData('article');
        $journal = $record->getData('journal');
        $issue = $record->getData('issue');
        $section = $record->getData('section');
        $locale = $journal->getPrimaryLocale();

        // 1. Header & Leader
        $response = "<record \n" .
            "\txmlns=\"http://www.loc.gov/MARC21/slim\"\n" .
            "\txmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n" .
            "\txsi:schemaLocation=\"http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd\">\n" .
            "\t<leader>     nmb a2200000Iu 4500</leader>\n";

        // 2. Control Field 008 (Date + Lang)
        if ($article->getDatePublished()) {
            $dateStr = date('ymd Y', strtotime($article->getDatePublished()));
            $response .= "\t<controlfield tag=\"008\">\"" . $dateStr . "                        eng  \"</controlfield>\n";
        }

        // 3. ISSN (Tag 022)
        if ($journal->getSetting('onlineIssn')) {
            $response .= $this->formatDataField('022', '#', '#', 'a', $journal->getSetting('onlineIssn'));
        }
        if ($journal->getSetting('printIssn')) {
            $response .= $this->formatDataField('022', '#', '#', 'a', $journal->getSetting('printIssn'));
        }

        // 4. Source (Tag 042)
        $response .= $this->formatDataField('042', ' ', ' ', 'a', 'dc');

        // 5. Title (Tag 245)
        $response .= $this->formatDataField('245', '0', '0', 'a', $article->getTitle($locale));

        // 6. Authors (Tag 100/720)
        $authors = $article->getAuthors();
        foreach ($authors as $author) {
            $tag = (count($authors) == 1) ? '100' : '720';
            $subfields = array('a' => $author->getFullName(true));
            
            $affiliation = $author->getAffiliation($locale);
            if ($affiliation) {
                $subfields['u'] = $affiliation;
            }
            if ($author->getUrl()) {
                $subfields['0'] = $author->getUrl();
            }
            if ($author->getData('orcid')) {
                 $subfields['0_orcid'] = $author->getData('orcid'); // Unique key trick
            }
            
            $response .= $this->formatDataFieldComplex($tag, '1', ' ', $subfields);
        }

        // 7. SUBJECTS (Tag 653) - *** FIX ARRAY BUG HERE ***
        $subjects = array_merge_recursive(
            $this->stripAssocArray((array) $article->getDiscipline(null)),
            $this->stripAssocArray((array) $article->getSubject(null)),
            $this->stripAssocArray((array) $article->getSubjectClass(null))
        );

        if (isset($subjects[$locale])) {
            $rawSubject = $subjects[$locale];
            $subjectStr = '';

            if (is_array($rawSubject)) {
                // WIZDAM FIX: Filter empty & Flatten Array
                $cleanSubjects = array_filter($rawSubject, function($value) {
                    return !is_null($value) && $value !== '';
                });
                $subjectStr = implode('; ', $cleanSubjects);
            } else {
                $subjectStr = $rawSubject;
            }

            if (!empty($subjectStr)) {
                $response .= $this->formatDataField('653', ' ', ' ', 'a', $subjectStr);
            }
        }

        // 8. Abstract (Tag 520)
        $abstract = PKPString::html2text($article->getAbstract($article->getLocale()));
        if (!empty($abstract)) {
             $response .= $this->formatDataField('520', ' ', ' ', 'a', $abstract);
        }

        // 9. Publisher (Tag 260)
        $publisher = $journal->getTitle($locale);
        if ($journal->getSetting('publisherInstitution')) {
            $publisher = $journal->getSetting('publisherInstitution');
        }
        
        // Complex 260 Field (Publisher + Date)
        $pubData = array('b' => $publisher);
        if ($issue->getDatePublished()) {
            $pubData['c'] = $issue->getDatePublished();
        }
        $response .= $this->formatDataFieldComplex('260', ' ', ' ', $pubData);

        // 10. Type (Tag 655)
        $identifyType = $section->getIdentifyType($locale);
        if ($identifyType) {
            $response .= $this->formatDataField('655', ' ', '7', 'a', $identifyType);
        }

        // 11. Galleys (Tag 856)
        foreach ($article->getGalleys() as $galley) {
            $response .= $this->formatDataField('856', ' ', ' ', 'q', $galley->getFileType());
        }
        
        $url = Request::url($journal->getPath(), 'article', 'view', array($article->getBestArticleId()));
        $response .= $this->formatDataField('856', '4', '0', 'u', $url);

        // 12. Source/Citation (Tag 786)
        $source = $journal->getTitle($locale) . '; ' . $issue->getIssueIdentification();
        $response .= $this->formatDataField('786', '0', ' ', 'n', $source);

        // 13. Language (Tag 546)
        $language = AppLocale::get3LetterIsoFromLocale($article->getLocale());
        $response .= $this->formatDataField('546', ' ', ' ', 'a', $language);

        // 14. Copyright (Tag 540)
        $copyrightKey = "submission.copyrightStatement";
        $params = array(
            'copyrightYear' => $article->getCopyrightYear(),
            'copyrightHolder' => $article->getCopyrightHolder($locale)
        );
        
        // Translation Fallback
        if (function_exists('__')) {
            $copyright = __($copyrightKey, $params);
        } else {
            $copyright = "Copyright " . $params['copyrightYear'] . " " . $params['copyrightHolder'];
        }
        
        $response .= $this->formatDataField('540', ' ', ' ', 'a', $copyright);

        $response .= "</record>\n";

        return $response;
    }

    // --- Helpers for MARCXML ---
    /**
     * Helper for single subfield
     * @param $tag string
     * @param $ind1 string
     * @param $ind2 string
     * @param $code string
     * @param $value string
     * @return string XML data
     */
    public function formatDataField($tag, $ind1, $ind2, $code, $value) {
        $value = OAIUtils::prepOutput($value);
        return "\t<datafield tag=\"$tag\" ind1=\"$ind1\" ind2=\"$ind2\">\n" .
               "\t\t<subfield code=\"$code\">$value</subfield>\n" .
               "\t</datafield>\n";
    }

    /**
     * Helper for multiple subfields
     * @param $tag string
     * @param $ind1 string
     * @param $ind2 string
     * @param $subfields array
     * @return string XML data
     */
    public function formatDataFieldComplex($tag, $ind1, $ind2, $subfields) {
        $out = "\t<datafield tag=\"$tag\" ind1=\"$ind1\" ind2=\"$ind2\">\n";
        foreach ($subfields as $code => $value) {
            // Handle duplicate key trick
            if ($code === '0_orcid') $code = '0';
            
            $value = OAIUtils::prepOutput($value);
            $out .= "\t\t<subfield code=\"$code\">$value</subfield>\n";
        }
        $out .= "\t</datafield>\n";
        return $out;
    }
}
?>