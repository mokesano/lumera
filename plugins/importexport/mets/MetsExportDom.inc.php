<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/mets/MetsExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetsExportDom
 * @ingroup GatewayPlugin
 *
 * @brief MetsExportDom export plugin DOM functions for export
 * [WIZDAM EDITION] Refactored for PHP 7.4/8.0+ (Static Methods, Strict Types, XML DOM)
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

class MetsExportDom {

    /**
     * Creates the METS:structMap element for an issue with multiple issues
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Journal $journal
     * @param array $issues
     */
    public static function generateStructMap(DOMDocument $doc, DOMElement $root, Journal $journal, array $issues): void {
        $structMap = XMLCustomWriter::createElement($doc, 'METS:structMap');
        XMLCustomWriter::setAttribute($structMap, 'TYPE', 'logical');
        
        $sDiv = XMLCustomWriter::createElement($doc, 'METS:div');
        XMLCustomWriter::setAttribute($sDiv, 'TYPE', 'journal');
        XMLCustomWriter::setAttribute($sDiv, 'DMDID', 'J-' . $journal->getId());
        
        foreach ($issues as $issue) {
            self::generateIssueDiv($doc, $sDiv, $issue);
        }
        
        XMLCustomWriter::appendChild($structMap, $sDiv);
        XMLCustomWriter::appendChild($root, $structMap);
    }

    /**
     * Generate Issue DIV
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Issue $issue
     */
    public static function generateIssueDiv(DOMDocument $doc, DOMElement $root, Issue $issue): void {
        $pDiv = XMLCustomWriter::createElement($doc, 'METS:div');
        XMLCustomWriter::setAttribute($pDiv, 'TYPE', 'issue');
        XMLCustomWriter::setAttribute($pDiv, 'DMDID', 'I-' . $issue->getId());
        
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionArray = $sectionDao->getSectionsForIssue($issue->getId());
        
        foreach ($sectionArray as $section) {
            self::generateSectionDiv($doc, $pDiv, $section, $issue);
        }
        
        XMLCustomWriter::appendChild($root, $pDiv);
    }

    /**
     * Generate Section DIV
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Section $section
     * @param Issue $issue
     */
    public static function generateSectionDiv(DOMDocument $doc, DOMElement $root, Section $section, Issue $issue): void {
        $pDiv = XMLCustomWriter::createElement($doc, 'METS:div');
        XMLCustomWriter::setAttribute($pDiv, 'TYPE', 'section');
        XMLCustomWriter::setAttribute($pDiv, 'DMDID', 'S-' . $section->getId());
        
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticleArray = $publishedArticleDao->getPublishedArticlesBySectionId($section->getId(), $issue->getId());
        
        foreach ($publishedArticleArray as $publishedArticle) {
            self::generateArticleDiv($doc, $pDiv, $publishedArticle, $issue);
        }
        
        XMLCustomWriter::appendChild($root, $pDiv);
    }

    /**
     * Creates the METS:div element for a submission
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle|Article $article
     * @param Issue $issue
     */
    public static function generateArticleDiv(DOMDocument $doc, DOMElement $root, $article, Issue $issue): void {
        $pDiv = XMLCustomWriter::createElement($doc, 'METS:div');
        XMLCustomWriter::setAttribute($pDiv, 'TYPE', 'article');
        XMLCustomWriter::setAttribute($pDiv, 'DMDID', 'A-' . $article->getId());
        
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleysArray = $articleGalleyDao->getGalleysByArticle($article->getId());
        
        foreach ($galleysArray as $galley) {
            self::generateArticleFileDiv($doc, $pDiv, $galley);
            
            if ($galley->isHTMLGalley()) {
                $images = $galley->getImageFiles();
                foreach ($images as $image) {
                    self::generateArticleHtmlGalleyImageFileDiv($doc, $pDiv, $image, $article);
                }
            }
        }
        
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFilesArray = $suppFileDao->getSuppFilesByArticle($article->getId());
        
        foreach ($suppFilesArray as $suppFile) {
            self::generateArticleSuppFilesDiv($doc, $pDiv, $suppFile);
        }
        
        XMLCustomWriter::appendChild($root, $pDiv);
    }

    /**
     * Creates the METS:fptr element for a ArticleGalley
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param ArticleGalley $file
     */
    public static function generateArticleFileDiv(DOMDocument $doc, DOMElement $root, $file): void {
        $fDiv = XMLCustomWriter::createElement($doc, 'METS:fptr');
        XMLCustomWriter::setAttribute($fDiv, 'FILEID', 'F' . $file->getFileId() . '-A' . $file->getArticleId());
        XMLCustomWriter::appendChild($root, $fDiv);
    }

    /**
     * Generate HTML Galley Image File DIV
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param object $imageFile
     * @param Article $article
     */
    public static function generateArticleHtmlGalleyImageFileDiv(DOMDocument $doc, DOMElement $root, $imageFile, Article $article): void {
        $fDiv = XMLCustomWriter::createElement($doc, 'METS:fptr');
        XMLCustomWriter::setAttribute($fDiv, 'FILEID', 'F' . $imageFile->getFileId() . '-A' . $article->getId());
        XMLCustomWriter::appendChild($root, $fDiv);
    }

    /**
     * Creates the METS:div @TYPE=additional_material for the Supp Files
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param SuppFile $suppFile
     */
    public static function generateArticleSuppFilesDiv(DOMDocument $doc, DOMElement $root, SuppFile $suppFile): void {
        $sDiv = XMLCustomWriter::createElement($doc, 'METS:div');
        XMLCustomWriter::setAttribute($sDiv, 'TYPE', 'additional_material');
        XMLCustomWriter::setAttribute($sDiv, 'DMDID', 'DMD-SF' . $suppFile->getFileId() . '-A' . $suppFile->getArticleId());
        
        $fDiv = XMLCustomWriter::createElement($doc, 'METS:fptr');
        XMLCustomWriter::setAttribute($fDiv, 'FILEID', 'SF' . $suppFile->getFileId() . '-A' . $suppFile->getArticleId());
        
        XMLCustomWriter::appendChild($sDiv, $fDiv);
        XMLCustomWriter::appendChild($root, $sDiv);
    }

    /**
     * Creates the METS:dmdSec element for the Journal
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Journal $journal
     */
    public static function generateJournalDmdSecDom(DOMDocument $doc, DOMElement $root, Journal $journal): void {
        $dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
        XMLCustomWriter::setAttribute($dmdSec, 'ID', 'J-' . $journal->getId());
        
        $mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
        $xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
        XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
        
        $mods = XMLCustomWriter::createElement($doc, 'mods:mods');
        XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
        
        // Handle Schema Location safely
        $existingSchema = $root->getAttribute('xsi:schemaLocation');
        $newSchema = str_replace(
            ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
            '',
            $existingSchema
        ) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);
        
        $titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
        XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', $journal->getTitle($journal->getPrimaryLocale()));
        XMLCustomWriter::appendChild($mods, $titleInfo);
        XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'journal');
        
        XMLCustomWriter::appendChild($xmlData, $mods);
        XMLCustomWriter::appendChild($dmdSec, $mdWrap);
        XMLCustomWriter::appendChild($mdWrap, $xmlData);
        XMLCustomWriter::appendChild($root, $dmdSec);
    }

    /**
     * Creates the METS:dmdSec element for an Issue
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateIssueDmdSecDom(DOMDocument $doc, DOMElement $root, Issue $issue, Journal $journal): void {
        $dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
        XMLCustomWriter::setAttribute($dmdSec, 'ID', 'I-' . $issue->getId());
        
        $mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
        $xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
        XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
        
        $mods = XMLCustomWriter::createElement($doc, 'mods:mods');
        XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
        
        // Schema Location Logic
        $existingSchema = $root->getAttribute('xsi:schemaLocation');
        $newSchema = str_replace(
            ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
            '',
            $existingSchema
        ) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

        $titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
        XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', $issue->getTitle($journal->getPrimaryLocale()));
        XMLCustomWriter::appendChild($mods, $titleInfo);

        if ($issue->getDescription($journal->getPrimaryLocale()) != '') {
            $modsAbstract = XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:abstract', $issue->getDescription($journal->getPrimaryLocale()));
            XMLCustomWriter::appendChild($mods, $modsAbstract);
        }

        XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'issue');
        
        import('classes.config.Config');
        $base_url = Config::getVar('general', 'base_url');
        $url = $base_url . '/index.php/' . $journal->getPath() . '/issue/view/' . $issue->getId();
        
        $modsIdentifier = XMLCustomWriter::createChildWithText($doc, $mods, 'mods:identifier', $url);
        XMLCustomWriter::setAttribute($modsIdentifier, 'type', 'uri');
        
        $modsOriginInfo = XMLCustomWriter::createElement($doc, 'mods:originInfo');
        if ($issue->getDatePublished()) {
            $timeIssued = date("Y-m-dTH:i:sP", strtotime($issue->getDatePublished()));
            XMLCustomWriter::createChildWithText($doc, $modsOriginInfo, 'mods:dateIssued', $timeIssued);
        }
        XMLCustomWriter::appendChild($mods, $modsOriginInfo);
        
        $modsRelatedItem = XMLCustomWriter::createElement($doc, 'mods:relatedItem');
        XMLCustomWriter::setAttribute($modsRelatedItem, 'type', 'host');
        
        $modsTitleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
        XMLCustomWriter::createChildWithText($doc, $modsTitleInfo, 'mods:title', $journal->getTitle($journal->getPrimaryLocale()));
        XMLCustomWriter::appendChild($modsRelatedItem, $modsTitleInfo);
        
        $urlJournal = $base_url . '/index.php/' . $journal->getPath();
        $modsIdentifierJournal = XMLCustomWriter::createChildWithText($doc, $modsRelatedItem, 'mods:identifier', $urlJournal);
        XMLCustomWriter::setAttribute($modsIdentifierJournal, 'type', 'uri');
        
        $modsPart = XMLCustomWriter::createElement($doc, 'mods:part');
        $modsVolumDetail = XMLCustomWriter::createElement($doc, 'mods:detail');
        XMLCustomWriter::setAttribute($modsVolumDetail, 'type', 'volume');
        XMLCustomWriter::createChildWithText($doc, $modsVolumDetail, 'mods:number', $issue->getVolume());
        
        $modsIssueDetail = XMLCustomWriter::createElement($doc, 'mods:detail');
        XMLCustomWriter::setAttribute($modsIssueDetail, 'type', 'issue');
        XMLCustomWriter::createChildWithText($doc, $modsIssueDetail, 'mods:number', $issue->getNumber());
        
        XMLCustomWriter::appendChild($modsPart, $modsVolumDetail);
        XMLCustomWriter::appendChild($modsPart, $modsIssueDetail);
        XMLCustomWriter::createChildWithText($doc, $modsPart, 'mods:date', $issue->getYear());
        
        XMLCustomWriter::appendChild($modsRelatedItem, $modsPart);
        XMLCustomWriter::appendChild($mods, $modsRelatedItem);
        XMLCustomWriter::appendChild($xmlData, $mods);
        XMLCustomWriter::appendChild($dmdSec, $mdWrap);
        XMLCustomWriter::appendChild($mdWrap, $xmlData);
        XMLCustomWriter::appendChild($root, $dmdSec);
        
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionArray = $sectionDao->getSectionsForIssue($issue->getId());
        
        foreach ($sectionArray as $section) {
            self::generateSectionDmdSecDom($doc, $root, $section, $issue, $journal);
        }
    }

    /**
     * Creates the METS:dmdSec element for a Section
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Section $section
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateSectionDmdSecDom(DOMDocument $doc, DOMElement $root, Section $section, Issue $issue, Journal $journal): void {
        $dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
        XMLCustomWriter::setAttribute($dmdSec, 'ID', 'S-' . $section->getId());
        
        $mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
        $xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
        XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
        
        $mods = XMLCustomWriter::createElement($doc, 'mods:mods');
        XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
        
        $existingSchema = $root->getAttribute('xsi:schemaLocation');
        $newSchema = str_replace(
            ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
            '',
            $existingSchema
        ) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

        $titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
        XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', $section->getTitle($journal->getPrimaryLocale()));
        XMLCustomWriter::appendChild($mods, $titleInfo);
        
        if ($section->getAbbrev($journal->getPrimaryLocale()) != '') {
            $titleInfoAlt = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
            XMLCustomWriter::createChildWithText($doc, $titleInfoAlt, 'mods:title', $section->getAbbrev($journal->getPrimaryLocale()));
            XMLCustomWriter::setAttribute($titleInfoAlt, 'type', 'abbreviated');
            XMLCustomWriter::appendChild($mods, $titleInfoAlt);
        }
        
        XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'section');
        XMLCustomWriter::appendChild($xmlData, $mods);
        XMLCustomWriter::appendChild($dmdSec, $mdWrap);
        XMLCustomWriter::appendChild($mdWrap, $xmlData);
        XMLCustomWriter::appendChild($root, $dmdSec);
        
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticleArray = $publishedArticleDao->getPublishedArticlesBySectionId($section->getId(), $issue->getId());
        
        foreach ($publishedArticleArray as $publishedArticle) {
            self::generateArticleDmdSecDom($doc, $root, $publishedArticle, $issue, $journal);
        }
    }

    /**
     * Creates the METS:dmdSec element for a published Paper
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle $article
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateArticleDmdSecDom(DOMDocument $doc, DOMElement $root, $article, Issue $issue, Journal $journal): void {
        $dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
        XMLCustomWriter::setAttribute($dmdSec, 'ID', 'A-' . $article->getId());
        
        $mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
        $xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
        XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
        
        $mods = XMLCustomWriter::createElement($doc, 'mods:mods');
        XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
        
        $existingSchema = $root->getAttribute('xsi:schemaLocation');
        $newSchema = str_replace(
            ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
            '',
            $existingSchema
        ) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

        $primaryLocale = $journal->getPrimaryLocale();
        foreach ($article->getTitle(null) as $locale => $title) {
            $titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
            XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', $title);
            if ($locale != $primaryLocale) XMLCustomWriter::setAttribute($titleInfo, 'type', 'alternative');
            XMLCustomWriter::appendChild($mods, $titleInfo);
        }

        $abstracts = $article->getAbstract(null);
        if (is_array($abstracts)) {
            foreach ($abstracts as $locale => $abstract) {
                XMLCustomWriter::createChildWithText($doc, $mods, 'mods:abstract', $abstract);
            }
        }

        $authorsArray = $article->getAuthors();
        foreach ($authorsArray as $author) {
            $presenterNode = self::generateAuthorDom($doc, $author);
            XMLCustomWriter::appendChild($mods, $presenterNode);
        }

        XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'article');
        
        if ($issue->getDatePublished()) {
            $timeIssued = date("Y-m-dTH:i:sP", strtotime($issue->getDatePublished()));
            $originInfo = XMLCustomWriter::createElement($doc, 'mods:originInfo');
            XMLCustomWriter::createChildWithText($doc, $originInfo, 'mods:dateIssued', $timeIssued);
            XMLCustomWriter::appendChild($mods, $originInfo);
        }

        if ($article->getDiscipline($article->getLocale()) != '') {
            $modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
            $disciplineArray = explode(';', $article->getDiscipline($article->getLocale()));
            foreach ($disciplineArray as $discipline) {
                XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:topic', $discipline);
            }
            XMLCustomWriter::appendChild($mods, $modsSubject);
        }

        if ($article->getSubject($article->getLocale()) != '') {
            $modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
            XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:topic', $article->getSubject($article->getLocale()));
            if ($article->getSubjectClass($article->getLocale()) != '') {
                XMLCustomWriter::setAttribute($modsSubject, 'authority', $article->getSubjectClass($article->getLocale()));
            }
            XMLCustomWriter::appendChild($mods, $modsSubject);
        }

        if ($article->getCoverageGeo($article->getLocale()) != '') {
            $modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
            $coverageArray = explode(";", $article->getCoverageGeo($article->getLocale()));
            foreach ($coverageArray as $coverage) {
                XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:geographic', $coverage);
            }
            XMLCustomWriter::appendChild($mods, $modsSubject);
        }

        if ($article->getCoverageChron($article->getLocale()) != '') {
            $modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
            $coverageArray = explode(";", $article->getCoverageChron($article->getLocale()));
            foreach ($coverageArray as $coverage) {
                XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:temporal', $coverage);
            }
            XMLCustomWriter::appendChild($mods, $modsSubject);
        }

        if ($article->getType($article->getLocale()) != '') {
            $modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
            XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:genre', $article->getType($article->getLocale()));
            XMLCustomWriter::appendChild($mods, $modsSubject);
        }

        if ($article->getSponsor($article->getLocale()) != '') {
            $presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
            XMLCustomWriter::setAttribute($presenterNode, 'type', 'corporate');
            XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $article->getSponsor($article->getLocale()));
            $role = XMLCustomWriter::createElement($doc, 'mods:role');
            $roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'sponsor');
            XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
            XMLCustomWriter::appendChild($presenterNode, $role);
            XMLCustomWriter::appendChild($mods, $presenterNode);
        }

        if ($article->getLanguage() != '') {
            XMLCustomWriter::createChildWithText($doc, $mods, 'mods:language', $article->getLanguage());
        }

        XMLCustomWriter::appendChild($xmlData, $mods);
        XMLCustomWriter::appendChild($dmdSec, $mdWrap);
        XMLCustomWriter::appendChild($mdWrap, $xmlData);
        XMLCustomWriter::appendChild($root, $dmdSec);
        
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFilesArray = $suppFileDao->getSuppFilesByArticle($article->getId());
        
        foreach ($suppFilesArray as $suppFile) {
            self::generateArticleSuppFilesDmdSecDom($doc, $root, $suppFile);
        }
    }

    /**
     * Creates the METS:dmdSec element for Supplementary Files
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param SuppFile $suppFile
     */
    public static function generateArticleSuppFilesDmdSecDom(DOMDocument $doc, DOMElement $root, SuppFile $suppFile): void {
        $dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
        XMLCustomWriter::setAttribute($dmdSec, 'ID', 'DMD-SF' . $suppFile->getFileId() . '-A' . $suppFile->getArticleId());
        
        $mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
        $xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
        XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
        
        $mods = XMLCustomWriter::createElement($doc, 'mods:mods');
        XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
        
        $existingSchema = $root->getAttribute('xsi:schemaLocation');
        $newSchema = str_replace(
            ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
            '',
            $existingSchema
        ) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

        foreach ($suppFile->getTitle(null) as $locale => $title) {
            $titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
            XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', $title);
            XMLCustomWriter::appendChild($mods, $titleInfo);
        }
        
        foreach ($suppFile->getCreator(null) as $locale => $creator) {
            $creatorNode = XMLCustomWriter::createElement($doc, 'mods:name');
            XMLCustomWriter::setAttribute($creatorNode, 'type', 'personal');
            XMLCustomWriter::createChildWithText($doc, $creatorNode, 'mods:namePart', $creator);
            $role = XMLCustomWriter::createElement($doc, 'mods:role');
            $roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'creator');
            XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
            XMLCustomWriter::appendChild($creatorNode, $role);
            XMLCustomWriter::appendChild($mods, $creatorNode);
        }
        
        foreach ($suppFile->getDescription(null) as $locale => $description) {
            XMLCustomWriter::createChildWithText($doc, $mods, 'mods:abstract', $description);
        }
        
        if ($suppFile->getDateCreated()) {
            $originInfo = XMLCustomWriter::createElement($doc, 'mods:originInfo');
            $timeIssued = date("Y-m-dTH:i:sP", strtotime($suppFile->getDateCreated()));
            XMLCustomWriter::createChildWithText($doc, $originInfo, 'mods:dateCreated', $timeIssued);
            XMLCustomWriter::appendChild($mods, $originInfo);
        }
        
        XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'additional material');
        if ($suppFile->getType() != '') {
            XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', $suppFile->getType());
        }
        
        foreach ($suppFile->getTypeOther(null) as $locale => $typeOther) {
            XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', $typeOther);
        }
        
        foreach ($suppFile->getSubject(null) as $locale => $subject) {
            $subjNode = XMLCustomWriter::createElement($doc, 'mods:subject');
            XMLCustomWriter::createChildWithText($doc, $subjNode, 'mods:topic', $subject);
            XMLCustomWriter::appendChild($mods, $subjNode);
        }
        
        foreach ($suppFile->getSponsor(null) as $locale => $sponsor) {
            $presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
            XMLCustomWriter::setAttribute($presenterNode, 'type', 'corporate');
            XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $sponsor);
            $role = XMLCustomWriter::createElement($doc, 'mods:role');
            $roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'sponsor');
            XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
            XMLCustomWriter::appendChild($presenterNode, $role);
            XMLCustomWriter::appendChild($mods, $presenterNode);
        }
        
        foreach ($suppFile->getPublisher(null) as $locale => $publisher) {
            $presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
            XMLCustomWriter::setAttribute($presenterNode, 'type', 'corporate');
            XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $publisher);
            $role = XMLCustomWriter::createElement($doc, 'mods:role');
            $roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'publisher');
            XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
            XMLCustomWriter::appendChild($presenterNode, $role);
            XMLCustomWriter::appendChild($mods, $presenterNode);
        }
        
        if ($suppFile->getLanguage() != '') {
            XMLCustomWriter::createChildWithText($doc, $mods, 'mods:language', $suppFile->getLanguage());
        }
        
        XMLCustomWriter::appendChild($xmlData, $mods);
        XMLCustomWriter::appendChild($dmdSec, $mdWrap);
        XMLCustomWriter::appendChild($mdWrap, $xmlData);
        XMLCustomWriter::appendChild($root, $dmdSec);
    }

    /**
     * Finds all files associated with this Issue by going through all Articles
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateIssueFileSecDom(DOMDocument $doc, DOMElement $root, Issue $issue, Journal $journal): void {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticleArray = $publishedArticleDao->getPublishedArticles($issue->getId());
        
        foreach ($publishedArticleArray as $publishedArticle) {
            self::generateArticleFilesDom($doc, $root, $publishedArticle, $issue, $journal);
        }
    }

    /**
     * Finds all HTML Galley files
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateIssueHtmlGalleyFileSecDom(DOMDocument $doc, DOMElement $root, Issue $issue, Journal $journal): void {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticleArray = $publishedArticleDao->getPublishedArticles($issue->getId());
        
        foreach ($publishedArticleArray as $publishedArticle) {
            self::generateArticleHtmlGalleyFilesDom($doc, $root, $publishedArticle, $issue, $journal);
        }
    }

    /**
     * Finds all files associated with this published Papers
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle $article
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateArticleFilesDom(DOMDocument $doc, DOMElement $root, $article, Issue $issue, Journal $journal): void {
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleysArray = $articleGalleyDao->getGalleysByArticle($article->getId());
        
        foreach ($galleysArray as $galley) {
            if (!$galley->isHTMLGalley()) {
                self::generateArticleFileDom($doc, $root, $article, $galley, null, $journal);
            }
        }
        
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFilesArray = $suppFileDao->getSuppFilesByArticle($article->getId());
        
        foreach ($suppFilesArray as $suppFile) {
            self::generateArticleSuppFileDom($doc, $root, $article, $suppFile, $journal);
        }
    }

    /**
     * Generates HTML Galley files DOM
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle $article
     * @param Issue $issue
     * @param Journal $journal
     */
    public static function generateArticleHtmlGalleyFilesDom(DOMDocument $doc, DOMElement $root, $article, Issue $issue, Journal $journal): void {
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleysArray = $articleGalleyDao->getGalleysByArticle($article->getId());
        
        foreach ($galleysArray as $galley) {
            if ($galley->isHTMLGalley()) {
                self::generateArticleFileDom($doc, $root, $article, $galley, 'html', $journal);
                $images = $galley->getImageFiles();
                foreach ($images as $image) {
                    self::generateArticleHtmlGalleyImageFileDom($doc, $root, $article, $galley, $image, 'html', $journal);
                }
            }
        }
    }

    /**
     * Generates HTML Galley Image File DOM
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle $article
     * @param ArticleGalley $galley
     * @param object $imageFile
     * @param string|null $useAttribute
     * @param Journal $journal
     */
    public static function generateArticleHtmlGalleyImageFileDom(DOMDocument $doc, DOMElement $root, $article, $galley, $imageFile, $useAttribute, Journal $journal): void {
        import('classes.file.PublicFileManager');
        import('lib.pkp.classes.file.FileManager');
        
        $fileManager = new FileManager();
        $contentWrapper = Request::getUserVar('contentWrapper');
        $mfile = XMLCustomWriter::createElement($doc, 'METS:file');
        $filePath = self::getPublicFilePath($imageFile, '/public/', $journal);

        $chkmd5return = file_exists($filePath) ? md5_file($filePath) : '';
        XMLCustomWriter::setAttribute($mfile, 'ID', 'F' . $imageFile->getFileId() . '-A' . $article->getId());
        if ($useAttribute != null) {
            XMLCustomWriter::setAttribute($mfile, 'USE', $useAttribute);
        }
        XMLCustomWriter::setAttribute($mfile, 'SIZE', $imageFile->getFileSize());
        XMLCustomWriter::setAttribute($mfile, 'MIMETYPE', $imageFile->getFileType());
        XMLCustomWriter::setAttribute($mfile, 'OWNERID', $imageFile->getOriginalFileName());
        XMLCustomWriter::setAttribute($mfile, 'CHECKSUM', $chkmd5return);
        XMLCustomWriter::setAttribute($mfile, 'CHECKSUMTYPE', 'MD5');
        
        if ($contentWrapper == 'FContent') {
            $fileContent = $fileManager->readFile($filePath);
            $fContent = XMLCustomWriter::createElement($doc, 'METS:FContent');
            XMLCustomWriter::createChildWithText($doc, $fContent, 'METS:binData', base64_encode((string)$fileContent));
            XMLCustomWriter::appendChild($mfile, $fContent);
        } else {
            $fLocat = XMLCustomWriter::createElement($doc, 'METS:FLocat');
            $fileUrl = Request::url(null, 'article', 'viewFile', array($article->getId(), $galley->getBestGalleyId($journal), $imageFile->getFileId()));
            XMLCustomWriter::setAttribute($fLocat, 'xlink:href', $fileUrl);
            XMLCustomWriter::setAttribute($fLocat, 'LOCTYPE', 'URL');
            XMLCustomWriter::appendChild($mfile, $fLocat);
        }
        XMLCustomWriter::appendChild($root, $mfile);
    }

    /**
     * Creates a METS:file for the paperfile
     * checks if METS:FContent or METS:FLocat should be used
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle $article
     * @param ArticleGalley $galleyFile
     * @param string|null $useAttribute
     * @param Journal $journal
     */
    public static function generateArticleFileDom(DOMDocument $doc, DOMElement $root, $article, $galleyFile, $useAttribute, Journal $journal): void {
        import('classes.file.PublicFileManager');
        import('lib.pkp.classes.file.FileManager');
        
        $fileManager = new FileManager();
        $contentWrapper = Request::getUserVar('contentWrapper');
        $mfile = XMLCustomWriter::createElement($doc, 'METS:file');
        $filePath = self::getPublicFilePath($galleyFile, '/public/', $journal);
        
        $chkmd5return = file_exists($filePath) ? md5_file($filePath) : '';
        XMLCustomWriter::setAttribute($mfile, 'ID', 'F' . $galleyFile->getFileId() . '-A' . $galleyFile->getArticleId());
        if ($useAttribute != null) {
            XMLCustomWriter::setAttribute($mfile, 'USE', $useAttribute);
        }
        XMLCustomWriter::setAttribute($mfile, 'SIZE', $galleyFile->getFileSize());
        XMLCustomWriter::setAttribute($mfile, 'MIMETYPE', $galleyFile->getFileType());
        XMLCustomWriter::setAttribute($mfile, 'OWNERID', $galleyFile->getFileName());
        XMLCustomWriter::setAttribute($mfile, 'CHECKSUM', $chkmd5return);
        XMLCustomWriter::setAttribute($mfile, 'CHECKSUMTYPE', 'MD5');
        
        if ($contentWrapper == 'FContent') {
            $fileContent = $fileManager->readFile($filePath);
            $fContent = XMLCustomWriter::createElement($doc, 'METS:FContent');
            XMLCustomWriter::createChildWithText($doc, $fContent, 'METS:binData', base64_encode((string)$fileContent));
            XMLCustomWriter::appendChild($mfile, $fContent);
        } else {
            $fLocat = XMLCustomWriter::createElement($doc, 'METS:FLocat');
            $fileUrl = Request::url(null, 'article', 'viewFile', array($article->getId(), $galleyFile->getBestGalleyId($journal)));
            XMLCustomWriter::setAttribute($fLocat, 'xlink:href', $fileUrl);
            XMLCustomWriter::setAttribute($fLocat, 'LOCTYPE', 'URL');
            XMLCustomWriter::appendChild($mfile, $fLocat);
        }
        XMLCustomWriter::appendChild($root, $mfile);
    }

    /**
     * Creates a METS:file for the Supplementary File
     * checks if METS:FContent or METS:FLocat should be used
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param PublishedArticle $article
     * @param SuppFile $suppFile
     * @param Journal $journal
     */
    public static function generateArticleSuppFileDom(DOMDocument $doc, DOMElement $root, $article, SuppFile $suppFile, Journal $journal): void {
        import('classes.file.PublicFileManager');
        import('lib.pkp.classes.file.FileManager');
        
        $fileManager = new FileManager();
        $contentWrapper = Request::getUserVar('contentWrapper');
        $mfile = XMLCustomWriter::createElement($doc, 'METS:file');
        $filePath = self::getPublicFilePath($suppFile, '/supp/', $journal);
        
        $chkmd5return = file_exists($filePath) ? md5_file($filePath) : '';
        XMLCustomWriter::setAttribute($mfile, 'ID', 'SF' . $suppFile->getFileId() . '-A' . $suppFile->getArticleId());
        XMLCustomWriter::setAttribute($mfile, 'SIZE', $suppFile->getFileSize());
        XMLCustomWriter::setAttribute($mfile, 'MIMETYPE', $suppFile->getFileType());
        XMLCustomWriter::setAttribute($mfile, 'OWNERID', $suppFile->getFileName());
        XMLCustomWriter::setAttribute($mfile, 'CHECKSUM', $chkmd5return);
        XMLCustomWriter::setAttribute($mfile, 'CHECKSUMTYPE', 'MD5');
        
        if ($contentWrapper == 'FContent') {
            $fileContent = $fileManager->readFile($filePath);
            $fContent = XMLCustomWriter::createElement($doc, 'METS:FContent');
            XMLCustomWriter::createChildWithText($doc, $fContent, 'METS:binData', base64_encode((string)$fileContent));
            XMLCustomWriter::appendChild($mfile, $fContent);
        } else {
            $fLocat = XMLCustomWriter::createElement($doc, 'METS:FLocat');
            XMLCustomWriter::setAttribute($fLocat, 'xlink:href', Request::url(
                $journal->getPath(),
                'article',
                'downloadSuppFile',
                array($suppFile->getArticleId(), $suppFile->getId())
            ));
            XMLCustomWriter::setAttribute($fLocat, 'LOCTYPE', 'URL');
            XMLCustomWriter::appendChild($mfile, $fLocat);
        }
        XMLCustomWriter::appendChild($root, $mfile);
    }

    /**
     * Create mods:name for a presenter
     * @param DOMDocument $doc
     * @param Author $author
     * @return DOMElement
     */
    public static function generateAuthorDom(DOMDocument $doc, Author $author): DOMElement {
        $presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
        XMLCustomWriter::setAttribute($presenterNode, 'type', 'personal');
        
        $fNameNode = XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $author->getFirstName() . ' ' . $author->getMiddleName());
        XMLCustomWriter::setAttribute($fNameNode, 'type', 'given');
        
        $lNameNode = XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $author->getLastName());
        XMLCustomWriter::setAttribute($lNameNode, 'type', 'family');
        
        $role = XMLCustomWriter::createElement($doc, 'mods:role');
        $roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'author');
        XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
        
        XMLCustomWriter::appendChild($presenterNode, $role);
        return $presenterNode;
    }

    /**
     * Create METS:amdSec for the Conference
     * @param DOMDocument $doc
     * @param DOMElement $root
     * @param Journal $journal
     * @return DOMElement
     */
    public static function createmetsamdSec(DOMDocument $doc, DOMElement $root, Journal $journal): DOMElement {
        $amdSec = XMLCustomWriter::createElement($doc, 'METS:amdSec');
        $techMD = XMLCustomWriter::createElement($doc, 'METS:techMD');
        XMLCustomWriter::setAttribute($techMD, 'ID', 'A-' . $journal->getId());
        
        $mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
        XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'PREMIS');
        
        $xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
        $pObject = XMLCustomWriter::createElement($doc, 'premis:object');
        XMLCustomWriter::setAttribute($pObject, 'xmlns:premis', 'http://www.loc.gov/standards/premis/v1');
        
        $existingSchema = $root->getAttribute('xsi:schemaLocation');
        $newSchema = str_replace(
            ' http://www.loc.gov/standards/premis/v1 http://www.loc.gov/standards/premis/v1/PREMIS-v1-1.xsd',
            '',
            $existingSchema
        ) . ' http://www.loc.gov/standards/premis/v1 http://www.loc.gov/standards/premis/v1/PREMIS-v1-1.xsd';
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

        $objectIdentifier = XMLCustomWriter::createElement($doc, 'premis:objectIdentifier');
        XMLCustomWriter::createChildWithText($doc, $objectIdentifier, 'premis:objectIdentifierType', 'internal');
        XMLCustomWriter::createChildWithText($doc, $objectIdentifier, 'premis:objectIdentifierValue', 'J-' . $journal->getId());
        XMLCustomWriter::appendChild($pObject, $objectIdentifier);
        
        $preservationLevel = Request::getUserVar('preservationLevel');
        if ($preservationLevel == '') {
            $preservationLevel = '1';
        }
        XMLCustomWriter::createChildWithText($doc, $pObject, 'premis:preservationLevel', 'level ' . $preservationLevel);
        XMLCustomWriter::createChildWithText($doc, $pObject, 'premis:objectCategory', 'Representation');
        
        XMLCustomWriter::appendChild($xmlData, $pObject);
        XMLCustomWriter::appendChild($mdWrap, $xmlData);
        XMLCustomWriter::appendChild($techMD, $mdWrap);
        XMLCustomWriter::appendChild($amdSec, $techMD);
        
        return $amdSec;
    }

    /**
     * Create METS:metsHdr for export
     * @param DOMDocument $doc
     * @return DOMElement
     */
    public static function createmetsHdr(DOMDocument $doc): DOMElement {
        $root = XMLCustomWriter::createElement($doc, 'METS:metsHdr');
        XMLCustomWriter::setAttribute($root, 'CREATEDATE', date('c'));
        XMLCustomWriter::setAttribute($root, 'LASTMODDATE', date('c'));
        
        $agentNode = XMLCustomWriter::createElement($doc, 'METS:agent');
        XMLCustomWriter::setAttribute($agentNode, 'ROLE', 'DISSEMINATOR');
        XMLCustomWriter::setAttribute($agentNode, 'TYPE', 'ORGANIZATION');
        
        $organization = Request::getUserVar('organization');
        if ($organization == '') {
            $siteDao = DAORegistry::getDAO('SiteDAO');
            $site = $siteDao->getSite();
            $organization = $site->getTitle($site->getPrimaryLocale());
        }
        XMLCustomWriter::createChildWithText($doc, $agentNode, 'METS:name', $organization, false);
        XMLCustomWriter::appendChild($root, $agentNode);
        
        $agentNode2 = XMLCustomWriter::createElement($doc, 'METS:agent');
        XMLCustomWriter::setAttribute($agentNode2, 'ROLE', 'CREATOR');
        XMLCustomWriter::setAttribute($agentNode2, 'TYPE', 'OTHER');
        XMLCustomWriter::createChildWithText($doc, $agentNode2, 'METS:name', self::getCreatorString(), false);
        XMLCustomWriter::appendChild($root, $agentNode2);
        
        return $root;
    }

    /**
     * Creator is the OJS System
     * @return string
     */
    public static function getCreatorString(): string {
        $versionDao = DAORegistry::getDAO('VersionDAO');
        $cVersion = $versionDao->getCurrentVersion();
        return sprintf('Open Journal Systems v%d.%d.%d build %d', $cVersion->getMajor(), $cVersion->getMinor(), $cVersion->getRevision(), $cVersion->getBuild());
    }

    /**
     * getPublicFilePath had to be added due to problems in the current
     * $paperFile->getFilePath(); for Galley Files
     * @param object $file
     * @param string $pathComponent
     * @param Journal $journal
     * @return string
     */
    public static function getPublicFilePath($file, string $pathComponent, Journal $journal): string {
        return Config::getVar('files', 'files_dir') . '/journals/' .
            $journal->getId() . '/articles/' .
            $file->getArticleId() . '/' . $pathComponent .
            '/' . $file->getFileName();
    }
}
?>