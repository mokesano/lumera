<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/doaj/DOAJExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOAJExportDom
 * @ingroup plugins_importexport_DOAJ
 *
 * @brief DOAJ import/export plugin DOM functions for export
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

class DOAJExportDom {

    /**
     * Constructor
     */
    public function __construct() {
        // Static utility class
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DOAJExportDom() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Generate the export DOM tree for a given journal.
     * @param DOMDocument $doc DOM object
     * @param Journal $journal Journal to export
     * @param array $selectedObjects
     * @return DOMElement
     */
    public static function generateJournalDom($doc, $journal, $selectedObjects) {
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $pubArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $journalId = $journal->getId();

        // Records node contains all articles, each called a record
        $records = XMLCustomWriter::createElement($doc, 'records');
        
        // retrieve selected issues
        $selectedIssues = [];
        if (isset($selectedObjects[DOAJ_EXPORT_ISSUES])) {
            $selectedIssues = $selectedObjects[DOAJ_EXPORT_ISSUES];
            
            // make sure the selected issues belong to the current journal
            foreach($selectedIssues as $key => $selectedIssueId) {
                $selectedIssue = $issueDao->getIssueById($selectedIssueId, $journalId);
                if (!$selectedIssue) unset($selectedIssues[$key]);
            }
        }

        // retrieve selected articles
        $selectedArticles = [];
        if (isset($selectedObjects[DOAJ_EXPORT_ARTICLES])) {
            $selectedArticles = $selectedObjects[DOAJ_EXPORT_ARTICLES];
        
            // make sure the selected articles belong to the current journal
            foreach($selectedArticles as $key => $selectedArticleId) {
                $selectedArticle = $articleDao->getArticle($selectedArticleId, $journalId);
                if (!$selectedArticle) unset($selectedArticles[$key]);
            }
        }

        $pubArticles = $pubArticleDao->getPublishedArticlesByJournalId($journalId);
        while ($pubArticle = $pubArticles->next()) {
            
            // check for selected issues:
            $issueId = $pubArticle->getIssueId();
            if (!empty($selectedIssues) && !in_array($issueId, $selectedIssues)) continue;

            $issue = $issueDao->getIssueById($issueId);
            if(!$issue) continue;
            
            // check for selected articles:
            $articleId = $pubArticle->getArticleId();
            if (!empty($selectedArticles) && !in_array($articleId, $selectedArticles)) continue;


            $section = $sectionDao->getSection($pubArticle->getSectionId());
            $articleNode = self::generateArticleDom($doc, $journal, $issue, $section, $pubArticle);

            XMLCustomWriter::appendChild($records, $articleNode);

            unset($issue, $section, $articleNode);
        }

        return $records;
    }

    /**
     * Generate the DOM tree for a given article.
     * @param DOMDocument $doc DOM object
     * @param Journal $journal
     * @param Issue $issue
     * @param Section $section
     * @param PublishedArticle $article
     * @return DOMElement
     */
    public static function generateArticleDom($doc, $journal, $issue, $section, $article) {
        $root = XMLCustomWriter::createElement($doc, 'record');

        /* --- Article Language --- */
        XMLCustomWriter::createChildWithText($doc, $root, 'language', self::mapLang($article->getLanguage()), false);

        /* --- Publisher name (i.e. institution name) --- */
        XMLCustomWriter::createChildWithText($doc, $root, 'publisher', $journal->getSetting('publisherInstitution'), false);

        /* --- Journal's title --- */
        XMLCustomWriter::createChildWithText($doc, $root, 'journalTitle', $journal->getTitle($journal->getPrimaryLocale()), false);

        /* --- Identification Numbers --- */
        XMLCustomWriter::createChildWithText($doc, $root, 'issn', $journal->getSetting('printIssn'), false);
        XMLCustomWriter::createChildWithText($doc, $root, 'eissn', $journal->getSetting('onlineIssn'), false);

        /* --- Article's publication date, volume, issue, DOI --- */
        if ($article->getDatePublished()) {
            XMLCustomWriter::createChildWithText($doc, $root, 'publicationDate', self::formatDate($article->getDatePublished()), false);
        }
        else {
            XMLCustomWriter::createChildWithText($doc, $root, 'publicationDate', self::formatDate($issue->getDatePublished()), false);
        }

        XMLCustomWriter::createChildWithText($doc, $root, 'volume',  (string) $issue->getVolume(), false);

        XMLCustomWriter::createChildWithText($doc, $root, 'issue',  (string) $issue->getNumber(), false);

        /** --- FirstPage / LastPage (from PubMed plugin)---
         * there is some ambiguity for online journals as to what
         * "page numbers" are; for example, some journals (eg. JMIR)
         * use the "e-location ID" as the "page numbers" in PubMed
         */
        $pages = $article->getPages();
        $matches = [];
        if (preg_match("/([0-9]+)\s*-\s*([0-9]+)/i", $pages, $matches)) {
            // simple pagination (eg. "pp. 3-8")
            XMLCustomWriter::createChildWithText($doc, $root, 'startPage', $matches[1]);
            XMLCustomWriter::createChildWithText($doc, $root, 'endPage', $matches[2]);
        } elseif (preg_match("/(e[0-9]+)/i", $pages, $matches)) {
            // elocation-id (eg. "e12")
            XMLCustomWriter::createChildWithText($doc, $root, 'startPage', $matches[1]);
            XMLCustomWriter::createChildWithText($doc, $root, 'endPage', $matches[1]);
        }

        XMLCustomWriter::createChildWithText($doc, $root, 'doi',  $article->getPubId('doi'), false);

        /* --- Article's publication date, volume, issue, DOI --- */
        XMLCustomWriter::createChildWithText($doc, $root, 'publisherRecordId',  (string) $article->getPublishedArticleId(), false);

        XMLCustomWriter::createChildWithText($doc, $root, 'documentType',  $article->getType($article->getLocale()), false);

        /* --- Article title --- */
        foreach ((array) $article->getTitle(null) as $locale => $title) {
            if (empty($title)) continue;

            $titleNode = XMLCustomWriter::createChildWithText($doc, $root, 'title', $title);
            if (strlen($locale) == 5) XMLCustomWriter::setAttribute($titleNode, 'language', self::mapLang(PKPString::substr($locale, 0, 2)));
        }

        /* --- Authors and affiliations --- */
        $authors = XMLCustomWriter::createElement($doc, 'authors');
        XMLCustomWriter::appendChild($root, $authors);

        $affilList = self::generateAffiliationsList($article->getAuthors(), $article);

        foreach ($article->getAuthors() as $author) {
            $authorNode = self::generateAuthorDom($doc, $root, $issue, $article, $author, $affilList);
            XMLCustomWriter::appendChild($authors, $authorNode);
            unset($authorNode);
        }

        if (!empty($affilList[0])) {
            $affils = XMLCustomWriter::createElement($doc, 'affiliationsList');
            XMLCustomWriter::appendChild($root, $affils);

            for ($i = 0; $i < count($affilList); $i++) {
                $affilNode = XMLCustomWriter::createChildWithText($doc, $affils, 'affiliationName', $affilList[$i]);
                XMLCustomWriter::setAttribute($affilNode, 'affiliationId', (string) $i);
                unset($affilNode);
            }
        }

        /* --- Abstract --- */
        foreach ((array) $article->getAbstract(null) as $locale => $abstract) {
            if (empty($abstract)) continue;

            $abstractNode = XMLCustomWriter::createChildWithText($doc, $root, 'abstract', PKPString::html2text($abstract));
            if (strlen($locale) == 5) XMLCustomWriter::setAttribute($abstractNode, 'language', self::mapLang(PKPString::substr($locale, 0, 2)));
        }

        /* --- FullText URL --- */
        $fullTextUrl = XMLCustomWriter::createChildWithText($doc, $root, 'fullTextUrl', Request::url(null, 'article', 'view', $article->getId()));
        XMLCustomWriter::setAttribute($fullTextUrl, 'format', 'html');

        /* --- Keywords --- */
        $keywords = XMLCustomWriter::createElement($doc, 'keywords');
        XMLCustomWriter::appendChild($root, $keywords);

        $subjects = array_map('trim', explode(';', $article->getSubject($article->getLocale())));

        foreach ($subjects as $keyword) {
            XMLCustomWriter::createChildWithText($doc, $keywords, 'keyword', $keyword, false);
        }

        return $root;
    }

    /**
     * Generate the author export DOM tree.
     * @param DOMDocument $doc DOM object
     * @param DOMElement $root Parent DOMElement
     * @param Issue $issue Issue
     * @param PublishedArticle $article Article
     * @param Author $author Author
     * @param array $affilList List of author affiliations
     * @return DOMElement
     */
    public static function generateAuthorDom($doc, $root, $issue, $article, $author, $affilList) {
        $node = XMLCustomWriter::createElement($doc, 'author');

        XMLCustomWriter::createChildWithText($doc, $node, 'name', $author->getFullName());
        XMLCustomWriter::createChildWithText($doc, $node, 'email', $author->getEmail(), false);

        if(in_array($author->getAffiliation($article->getLocale()), $affilList)  && !empty($affilList[0])) {
            $affilKey = current(array_keys($affilList, $author->getAffiliation($article->getLocale())));
            XMLCustomWriter::createChildWithText($doc, $node, 'affiliationId', (string) $affilKey);
        }

        return $node;
    }

    /**
     * Generate a list of affiliations among all authors of an article.
     * @param array $authors Array of article authors
     * @param PublishedArticle $article Article
     * @return array
     */
    public static function generateAffiliationsList($authors, $article) {
        $affilList = [];

        foreach ($authors as $author) {
            if(!in_array($author->getAffiliation($article->getLocale()), $affilList)) {
                $affilList[] = $author->getAffiliation($article->getLocale()) ;
            }
        }

        return $affilList;
    }

    /* --- Utility functions: --- */

    /**
     * Get the file extension of a filename.
     * @param string $filename
     * @return string
     */
    public static function file_ext($filename) {
        return strtolower_codesafe(str_replace('.', '', strrchr($filename, '.')));
    }

    /**
     * Format a date by Y-m-d format.
     * @param string $date
     * @return string|null
     */
    public static function formatDate($date): ?string {
        if ($date == '') return null;
        return date('Y-m-d', strtotime($date));
    }

    /**
     * Map a language from a 2-letter code to a 3-letter code.
     * FIXME: This should be moved to XML and reconciled against
     * other mapping implementations.
     * @param string $val 2-letter language code to map
     * @return string
     */
    public static function mapLang($val) {
        switch ($val) {
            case "aa": return "aar";
            case "ab": return "abk";
            case "af": return "afr";
            case "ak": return "aka";
            case "sq": return "alb";
            case "sqi": return "alb";
            case "am": return "amh";
            case "ar": return "ara";
            case "an": return "arg";
            case "hy": return "arm";
            case "hye": return "arm";
            case "as": return "asm";
            case "av": return "ava";
            case "ae": return "ave";
            case "ay": return "aym";
            case "az": return "aze";
            case "ba": return "bak";
            case "bm": return "bam";
            case "eu": return "baq";
            case "eus": return "baq";
            case "be": return "bel";
            case "bn": return "ben";
            case "bh": return "bih";
            case "bi": return "bis";
            case "bo": return "tib";
            case "bod": return "tib";
            case "bs": return "bos";
            case "br": return "bre";
            case "bg": return "bul";
            case "my": return "bur";
            case "mya": return "bur";
            case "ca": return "cat";
            case "cs": return "cze";
            case "ces": return "cze";
            case "ch": return "cha";
            case "ce": return "che";
            case "zh": return "chi";
            case "zho": return "chi";
            case "cv": return "chv";
            case "kw": return "cor";
            case "co": return "cos";
            case "cr": return "cre";
            case "cy": return "wel";
            case "cym": return "wel";
            case "da": return "dan";
            case "de": return "ger";
            case "deu": return "ger";
            case "dv": return "div";
            case "nl": return "dut";
            case "nld": return "dut";
            case "dz": return "dzo";
            case "el": return "gre";
            case "ell": return "gre";
            case "en": return "eng";
            case "eo": return "epo";
            case "et": return "est";
            case "ee": return "ewe";
            case "fo": return "fao";
            case "fa": return "per";
            case "fas": return "per";
            case "fj": return "fij";
            case "fi": return "fin";
            case "fr": return "fre";
            case "fra": return "fre";
            case "fy": return "fry";
            case "ff": return "ful";
            case "ka": return "geo";
            case "kat": return "geo";
            case "gd": return "gla";
            case "ga": return "gle";
            case "gl": return "glg";
            case "gv": return "glv";
            case "gn": return "grn";
            case "gu": return "guj";
            case "ht": return "hat";
            case "ha": return "hau";
            case "he": return "heb";
            case "hz": return "her";
            case "hi": return "hin";
            case "ho": return "hmo";
            case "hr": return "scr";
            case "hrv": return "scr";
            case "hu": return "hun";
            case "ig": return "ibo";
            case "is": return "ice";
            case "isl": return "ice";
            case "io": return "ido";
            case "ii": return "iii";
            case "iu": return "iku";
            case "ie": return "ile";
            case "ia": return "ina";
            case "id": return "ind";
            case "ik": return "ipk";
            case "it": return "ita";
            case "jv": return "jav";
            case "ja": return "jpn";
            case "kl": return "kal";
            case "kn": return "kan";
            case "ks": return "kas";
            case "kr": return "kau";
            case "kk": return "kaz";
            case "km": return "khm";
            case "ki": return "kik";
            case "rw": return "kin";
            case "ky": return "kir";
            case "kv": return "kom";
            case "kg": return "kon";
            case "ko": return "kor";
            case "kj": return "kua";
            case "ku": return "kur";
            case "lo": return "lao";
            case "la": return "lat";
            case "lv": return "lav";
            case "li": return "lim";
            case "ln": return "lin";
            case "lt": return "lit";
            case "lb": return "ltz";
            case "lu": return "lub";
            case "lg": return "lug";
            case "mk": return "mac";
            case "mkd": return "mac";
            case "mh": return "mah";
            case "ml": return "mal";
            case "mi": return "mao";
            case "mri": return "mao";
            case "mr": return "mar";
            case "ms": return "may";
            case "msa": return "may";
            case "mg": return "mlg";
            case "mt": return "mlt";
            case "mo": return "mol";
            case "mn": return "mon";
            case "na": return "nau";
            case "nv": return "nav";
            case "nr": return "nbl";
            case "nd": return "nde";
            case "ng": return "ndo";
            case "ne": return "nep";
            case "nn": return "nno";
            case "nb": return "nob";
            case "no": return "nor";
            case "ny": return "nya";
            case "oc": return "oci";
            case "oj": return "oji";
            case "or": return "ori";
            case "om": return "orm";
            case "os": return "oss";
            case "pa": return "pan";
            case "pi": return "pli";
            case "pl": return "pol";
            case "pt": return "por";
            case "ps": return "pus";
            case "qu": return "que";
            case "rm": return "roh";
            case "ro": return "rum";
            case "ron": return "rum";
            case "rn": return "run";
            case "ru": return "rus";
            case "sg": return "sag";
            case "sa": return "san";
            case "sr": return "scc";
            case "srp": return "scc";
            case "si": return "sin";
            case "sk": return "slo";
            case "slk": return "slo";
            case "sl": return "slv";
            case "se": return "sme";
            case "sm": return "smo";
            case "sn": return "sna";
            case "sd": return "snd";
            case "so": return "som";
            case "st": return "sot";
            case "es": return "spa";
            case "sc": return "srd";
            case "ss": return "ssw";
            case "su": return "sun";
            case "sw": return "swa";
            case "sv": return "swe";
            case "ty": return "tah";
            case "ta": return "tam";
            case "tt": return "tat";
            case "te": return "tel";
            case "tg": return "tgk";
            case "tl": return "tgl";
            case "th": return "tha";
            case "ti": return "tir";
            case "to": return "ton";
            case "tn": return "tsn";
            case "ts": return "tso";
            case "tk": return "tuk";
            case "tr": return "tur";
            case "tw": return "twi";
            case "ug": return "uig";
            case "uk": return "ukr";
            case "ur": return "urd";
            case "uz": return "uzb";
            case "ve": return "ven";
            case "vi": return "vie";
            case "vo": return "vol";
            case "wa": return "wln";
            case "wo": return "wol";
            case "xh": return "xho";
            case "yi": return "yid";
            case "yo": return "yor";
            case "za": return "zha";
            case "zu": return "zul";
            default: return "";
        }
    }
}

?>