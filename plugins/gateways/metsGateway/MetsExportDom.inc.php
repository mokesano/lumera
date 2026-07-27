<?php
declare(strict_types=1);

/**
 * @defgroup GatewayPlugin
 */

/**
 * @file plugins/gateways/metsGateway/MetsExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MetsExportDom
 * @ingroup GatewayPlugin
 *
 * @brief MetsExportDom export plugin DOM functions for export
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

class MetsExportDom {

	/**
	 * Creates the METS:structMap element for an issue with multiple issues.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Journal $journal
	 * @param array $issues
	 */
	public static function generateStructMap(&$doc, &$root, &$journal, &$issues) {
		$structMap = XMLCustomWriter::createElement($doc, 'METS:structMap');
		XMLCustomWriter::setAttribute($structMap, 'TYPE', 'logical');
		$sDiv = XMLCustomWriter::createElement($doc, 'METS:div');
		XMLCustomWriter::setAttribute($sDiv, 'TYPE', 'journal');
		XMLCustomWriter::setAttribute($sDiv, 'DMDID', 'J-' . (int) $journal->getId());
		foreach ($issues as $issue) {
			self::generateIssueDiv($doc, $sDiv, $issue);
		}
		XMLCustomWriter::appendChild($structMap, $sDiv);
		XMLCustomWriter::appendChild($root, $structMap);
	}

	/**
	 * Generates the METS:div element for an issue.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Issue $issue
	 */
	public static function generateIssueDiv(&$doc, &$root, &$issue) {
		$pDiv = XMLCustomWriter::createElement($doc, 'METS:div');
		XMLCustomWriter::setAttribute($pDiv, 'TYPE', 'issue');
		XMLCustomWriter::setAttribute($pDiv, 'DMDID', 'I-' . (int) $issue->getId());

		/** @var SectionDAO $sectionDao */
		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$sectionArray = $sectionDao->getSectionsForIssue((int) $issue->getId());
		foreach ($sectionArray as $section) {
			self::generateSectionDiv($doc, $pDiv, $section, $issue);
		}
		XMLCustomWriter::appendChild($root, $pDiv);
	}

	/**
	 * Generates the METS:div element for a section.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Section $section
	 * @param Issue $issue
	 */
	public static function generateSectionDiv(&$doc, &$root, &$section, &$issue) {
		$pDiv = XMLCustomWriter::createElement($doc, 'METS:div');
		XMLCustomWriter::setAttribute($pDiv, 'TYPE', 'section');
		XMLCustomWriter::setAttribute($pDiv, 'DMDID', 'S-' . (int) $section->getId());

		/** @var PublishedArticleDAO $publishedArticleDao */
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticleArray = $publishedArticleDao->getPublishedArticlesBySectionId((int) $section->getId(), (int) $issue->getId());
		foreach ($publishedArticleArray as $publishedArticle) {
			self::generateArticleDiv($doc, $pDiv, $publishedArticle, $issue);
		}
		XMLCustomWriter::appendChild($root, $pDiv);
	}

	/**
	 * Creates the METS:div element for a submission (article).
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param Issue $issue
	 */
	public static function generateArticleDiv(&$doc, &$root, &$article, &$issue) {
		$pDiv = XMLCustomWriter::createElement($doc, 'METS:div');
		XMLCustomWriter::setAttribute($pDiv, 'TYPE', 'article');
		XMLCustomWriter::setAttribute($pDiv, 'DMDID', 'A-' . (int) $article->getId());

		/** @var ArticleGalleyDAO $articleGalleyDao */
		$articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
		$galleysArray = $articleGalleyDao->getGalleysByArticle((int) $article->getId());
		foreach ($galleysArray as $galley) {
			self::generateArticleFileDiv($doc, $pDiv, $galley);
			if ($galley->isHTMLGalley()) {
				$images = $galley->getImageFiles();
				foreach ($images as $image) {
					self::generateArticleHtmlGalleyImageFileDiv($doc, $pDiv, $image, $article);
				}
			}
		}

		/** @var SuppFileDAO $suppFileDao */
		$suppFileDao = DAORegistry::getDAO('SuppFileDAO');
		$suppFilesArray = $suppFileDao->getSuppFilesByArticle((int) $article->getId());
		foreach ($suppFilesArray as $suppFile) {
			self::generateArticleSuppFilesDiv($doc, $pDiv, $suppFile);
		}
		XMLCustomWriter::appendChild($root, $pDiv);
	}

	/**
	 * Creates the METS:fptr element for an ArticleGalley.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param ArticleGalley $file
	 */
	public static function generateArticleFileDiv(&$doc, &$root, $file) {
		$fDiv = XMLCustomWriter::createElement($doc, 'METS:fptr');
		XMLCustomWriter::setAttribute($fDiv, 'FILEID', 'F' . (int) $file->getFileId() . '-A' . (int) $file->getArticleId());
		XMLCustomWriter::appendChild($root, $fDiv);
	}

	/**
	 * Generates the METS:fptr element for an HTML Galley image file.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param object $imageFile
	 * @param PublishedArticle $article
	 */
	public static function generateArticleHtmlGalleyImageFileDiv(&$doc, &$root, &$imageFile, &$article) {
		$fDiv = XMLCustomWriter::createElement($doc, 'METS:fptr');
		XMLCustomWriter::setAttribute($fDiv, 'FILEID', 'F' . (int) $imageFile->getFileId() . '-A' . (int) $article->getId());
		XMLCustomWriter::appendChild($root, $fDiv);
	}

	/**
	 * Creates the METS:div @TYPE=additional_material for the Supp Files.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param SuppFile $suppFile
	 */
	public static function generateArticleSuppFilesDiv(&$doc, &$root, $suppFile) {
		$sDiv = XMLCustomWriter::createElement($doc, 'METS:div');
		XMLCustomWriter::setAttribute($sDiv, 'TYPE', 'additional_material');
		XMLCustomWriter::setAttribute($sDiv, 'DMDID', 'DMD-SF' . (int) $suppFile->getFileId() . '-A' . (int) $suppFile->getArticleId());
		$fDiv = XMLCustomWriter::createElement($doc, 'METS:fptr');
		XMLCustomWriter::setAttribute($fDiv, 'FILEID', 'SF' . (int) $suppFile->getFileId() . '-A' . (int) $suppFile->getArticleId());
		XMLCustomWriter::appendChild($sDiv, $fDiv);
		XMLCustomWriter::appendChild($root, $sDiv);
	}

	/**
	 * Creates the METS:dmdSec element for the Journal.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Journal $journal
	 */
	public static function generateJournalDmdSecDom(&$doc, $root, &$journal) {
		$dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
		XMLCustomWriter::setAttribute($dmdSec, 'ID', 'J-' . (int) $journal->getId());
		$mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
		$xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
		XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
		$mods = XMLCustomWriter::createElement($doc, 'mods:mods');
		XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
		
		$existingSchema = (string) $root->getAttribute('xsi:schemaLocation');
		$newSchema = str_replace(
			' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
			'',
			$existingSchema
		) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
		XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);
		
		$titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
		XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', (string) $journal->getLocalizedTitle());
		XMLCustomWriter::appendChild($mods, $titleInfo);
		XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'journal');
		XMLCustomWriter::appendChild($xmlData, $mods);
		XMLCustomWriter::appendChild($dmdSec, $mdWrap);
		XMLCustomWriter::appendChild($mdWrap, $xmlData);
		XMLCustomWriter::appendChild($root, $dmdSec);
	}

	/**
	 * Creates the METS:dmdSec element for an Issue.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Issue $issue
	 * @param Journal $journal
	 */
	public static function generateIssueDmdSecDom(&$doc, &$root, &$issue, &$journal) {
		$dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
		XMLCustomWriter::setAttribute($dmdSec, 'ID', 'I-' . (int) $issue->getId());
		$mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
		$xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
		XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
		$mods = XMLCustomWriter::createElement($doc, 'mods:mods');
		XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
		
		$existingSchema = (string) $root->getAttribute('xsi:schemaLocation');
		$newSchema = str_replace(
			' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
			'',
			$existingSchema
		) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
		XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);
		
		$titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
		XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', (string) $issue->getLocalizedTitle());
		XMLCustomWriter::appendChild($mods, $titleInfo);

		$localizedDescription = (string) $issue->getLocalizedDescription();
		if ($localizedDescription !== '') {
			$modsAbstract = XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:abstract', $localizedDescription);
			XMLCustomWriter::appendChild($mods, $modsAbstract);
		}

		XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'issue');
		import('classes.config.Config');
		$base_url = (string) Config::getVar('general', 'base_url');
		$url = $base_url . '/index.php/' . $journal->getPath() . '/issue/view/' . (int) $issue->getId();
		$modsIdentifier = XMLCustomWriter::createChildWithText($doc, $mods, 'mods:identifier', $url);
		XMLCustomWriter::setAttribute($modsIdentifier, 'type', 'uri');
		$modsOriginInfo = XMLCustomWriter::createElement($doc, 'mods:originInfo');

		$datePublished = $issue->getDatePublished();
		if ($datePublished) {
			$timeIssued = date(DATE_W3C, strtotime((string) $datePublished));
			XMLCustomWriter::createChildWithText($doc, $modsOriginInfo, 'mods:dateIssued', $timeIssued);
		}

		XMLCustomWriter::appendChild($mods, $modsOriginInfo);
		$modsRelatedItem = XMLCustomWriter::createElement($doc, 'mods:relatedItem');
		XMLCustomWriter::setAttribute($modsRelatedItem, 'type', 'host');
		$modsTitleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
		XMLCustomWriter::createChildWithText($doc, $modsTitleInfo, 'mods:title', (string) $journal->getLocalizedTitle());
		XMLCustomWriter::appendChild($modsRelatedItem, $modsTitleInfo);
		$url = $base_url . '/index.php/' . $journal->getPath();
		$modsIdentifierRelated = XMLCustomWriter::createChildWithText($doc, $modsRelatedItem, 'mods:identifier', $url);
		XMLCustomWriter::setAttribute($modsIdentifierRelated, 'type', 'uri');
		$modsPart = XMLCustomWriter::createElement($doc, 'mods:part');
		$modsVolumDetail = XMLCustomWriter::createElement($doc, 'mods:detail');
		XMLCustomWriter::setAttribute($modsVolumDetail, 'type', 'volume');
		XMLCustomWriter::createChildWithText($doc, $modsVolumDetail, 'mods:number', (string) $issue->getVolume());
		$modsIssueDetail = XMLCustomWriter::createElement($doc, 'mods:detail');
		XMLCustomWriter::setAttribute($modsIssueDetail, 'type', 'issue');
		XMLCustomWriter::createChildWithText($doc, $modsIssueDetail, 'mods:number', (string) $issue->getNumber());
		XMLCustomWriter::appendChild($modsPart, $modsVolumDetail);
		XMLCustomWriter::appendChild($modsPart, $modsIssueDetail);
		XMLCustomWriter::createChildWithText($doc, $modsPart, 'mods:date', (string) $issue->getYear());
		XMLCustomWriter::appendChild($modsRelatedItem, $modsPart);
		XMLCustomWriter::appendChild($mods, $modsRelatedItem);
		XMLCustomWriter::appendChild($xmlData, $mods);
		XMLCustomWriter::appendChild($dmdSec, $mdWrap);
		XMLCustomWriter::appendChild($mdWrap, $xmlData);
		XMLCustomWriter::appendChild($root, $dmdSec);

		/** @var SectionDAO $sectionDao */
		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$sectionArray = $sectionDao->getSectionsForIssue((int) $issue->getId());
		foreach ($sectionArray as $section) {
			self::generateSectionDmdSecDom($doc, $root, $section, $issue);
		}
	}

	/**
	 * Creates the METS:dmdSec element for a Section.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Section $section
	 * @param Issue $issue
	 */
	public static function generateSectionDmdSecDom(&$doc, &$root, &$section, &$issue) {
		$dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
		XMLCustomWriter::setAttribute($dmdSec, 'ID', 'S-' . (int) $section->getId());
		$mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
		$xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
		XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
		$mods = XMLCustomWriter::createElement($doc, 'mods:mods');
		XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');
		
		$existingSchema = (string) $root->getAttribute('xsi:schemaLocation');
		$newSchema = str_replace(
			' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
			'',
			$existingSchema
		) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
		XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);
		
		$titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
		XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', (string) $section->getLocalizedTitle());
		XMLCustomWriter::appendChild($mods, $titleInfo);
		
		$localizedAbbrev = (string) $section->getLocalizedAbbrev();
		if ($localizedAbbrev !== '') {
			$titleInfoAlt1 = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
			XMLCustomWriter::createChildWithText($doc, $titleInfoAlt1, 'mods:title', $localizedAbbrev);
			XMLCustomWriter::setAttribute($titleInfoAlt1, 'type', 'abbreviated');
			XMLCustomWriter::appendChild($mods, $titleInfoAlt1);
		}
		XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'section');
		XMLCustomWriter::appendChild($xmlData, $mods);
		XMLCustomWriter::appendChild($dmdSec, $mdWrap);
		XMLCustomWriter::appendChild($mdWrap, $xmlData);
		XMLCustomWriter::appendChild($root, $dmdSec);

		/** @var PublishedArticleDAO $publishedArticleDao */
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticleArray = $publishedArticleDao->getPublishedArticlesBySectionId((int) $section->getId(), (int) $issue->getId());
		foreach ($publishedArticleArray as $publishedArticle) {
			self::generateArticleDmdSecDom($doc, $root, $publishedArticle, $issue);
		}
	}

	/**
	 * Creates the METS:dmdSec element for a published Paper (Article).
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param Issue $issue
	 * @param string $locale Bahasa yang digunakan (contoh: 'en_US')
	 */
	public static function generateArticleDmdSecDom(&$doc, &$root, &$article, &$issue, $locale = 'en_US') {
		$dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
		XMLCustomWriter::setAttribute($dmdSec, 'ID', 'A-' . (int) $article->getId());
		$mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
		$xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
		XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
		$mods = XMLCustomWriter::createElement($doc, 'mods:mods');
		XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');

		$existingSchema = (string) $root->getAttribute('xsi:schemaLocation');
		$newSchema = str_replace(
			' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
			'',
			$existingSchema
		) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
		XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

		$titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
		XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', (string) $article->getLocalizedTitle());
		XMLCustomWriter::appendChild($mods, $titleInfo);

		$localizedAbstract = (string) $article->getLocalizedAbstract();
		if ($localizedAbstract !== '') {
			XMLCustomWriter::createChildWithText($doc, $mods, 'mods:abstract', $localizedAbstract);
		}

		$authorsArray = $article->getAuthors();
		foreach ($authorsArray as $author) {
			$presenterNode = self::generateAuthorDom($doc, $author);
			XMLCustomWriter::appendChild($mods, $presenterNode);
		}
		XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'article');

		$datePublished = $issue->getDatePublished();
		if ($datePublished) {
			$timeIssued = date(DATE_W3C, strtotime((string) $datePublished));
			$originInfo = XMLCustomWriter::createElement($doc, 'mods:originInfo');
			XMLCustomWriter::createChildWithText($doc, $originInfo, 'mods:dateIssued', $timeIssued);
			XMLCustomWriter::appendChild($mods, $originInfo);
		}

		$localizedDiscipline = (string) $article->getLocalizedDiscipline();
		if ($localizedDiscipline !== '') {
			$modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
			$disciplineArray = explode(';', $localizedDiscipline);
			foreach ($disciplineArray as $discipline) {
				XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:topic', trim($discipline));
			}
			XMLCustomWriter::appendChild($mods, $modsSubject);
		}

		$localizedSubject = (string) $article->getLocalizedSubject();
		if ($localizedSubject !== '') {
			$modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
			XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:topic', $localizedSubject);
			$subjectClass = (string) $article->getSubjectClass($locale);
			if ($subjectClass !== '') {
				XMLCustomWriter::setAttribute($modsSubject, 'authority', $subjectClass);
			}
			XMLCustomWriter::appendChild($mods, $modsSubject);
		}

		$localizedCoverageGeo = (string) $article->getLocalizedCoverageGeo();
		if ($localizedCoverageGeo !== '') {
			$modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
			$coverageArray = explode(';', $localizedCoverageGeo);
			foreach ($coverageArray as $coverage) {
				XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:geographic', trim($coverage));
			}
			XMLCustomWriter::appendChild($mods, $modsSubject);
		}

		$localizedCoverageChron = (string) $article->getLocalizedCoverageChron();
		if ($localizedCoverageChron !== '') {
			$modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
			$coverageArray = explode(';', $localizedCoverageChron);
			foreach ($coverageArray as $coverage) {
				XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:temporal', trim($coverage));
			}
			XMLCustomWriter::appendChild($mods, $modsSubject);
		}

		$localizedType = (string) $article->getLocalizedType();
		if ($localizedType !== '') {
			$modsSubject = XMLCustomWriter::createElement($doc, 'mods:subject');
			XMLCustomWriter::createChildWithText($doc, $modsSubject, 'mods:genre', $localizedType);
			XMLCustomWriter::appendChild($mods, $modsSubject);
		}

		$localizedSponsor = (string) $article->getLocalizedSponsor();
		if ($localizedSponsor !== '') {
			$presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
			XMLCustomWriter::setAttribute($presenterNode, 'type', 'corporate');
			XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $localizedSponsor);
			$role = XMLCustomWriter::createElement($doc, 'mods:role');
			$roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'sponsor');
			XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
			XMLCustomWriter::appendChild($presenterNode, $role);
			XMLCustomWriter::appendChild($mods, $presenterNode);
		}

		$language = (string) $article->getLanguage();
		if ($language !== '') {
			XMLCustomWriter::createChildWithText($doc, $mods, 'mods:language', $language);
		}

		XMLCustomWriter::appendChild($xmlData, $mods);
		XMLCustomWriter::appendChild($dmdSec, $mdWrap);
		XMLCustomWriter::appendChild($mdWrap, $xmlData);
		XMLCustomWriter::appendChild($root, $dmdSec);

		/** @var SuppFileDAO $suppFileDao */
		$suppFileDao = DAORegistry::getDAO('SuppFileDAO');
		$suppFilesArray = $suppFileDao->getSuppFilesByArticle((int) $article->getId());
		foreach ($suppFilesArray as $suppFile) {
			self::generateArticleSuppFilesDmdSecDom($doc, $root, $suppFile, $locale);
		}
	}

	/**
	 * Creates the METS:dmdSec element for Supplementary Files.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param SuppFile $suppFile
	 * @param string $locale Bahasa yang digunakan (contoh: 'en_US')
	 */
	public static function generateArticleSuppFilesDmdSecDom(&$doc, &$root, $suppFile, $locale = 'en_US') {
		$dmdSec = XMLCustomWriter::createElement($doc, 'METS:dmdSec');
		XMLCustomWriter::setAttribute($dmdSec, 'ID', 'DMD-SF' . (int) $suppFile->getFileId() . '-A' . (int) $suppFile->getArticleId());
		$mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
		$xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
		XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'MODS');
		$mods = XMLCustomWriter::createElement($doc, 'mods:mods');
		XMLCustomWriter::setAttribute($mods, 'xmlns:mods', 'http://www.loc.gov/mods/v3');

		$existingSchema = (string) $root->getAttribute('xsi:schemaLocation');
		$newSchema = str_replace(
			' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd',
			'',
			$existingSchema
		) . ' http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd';
		XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);

		$titleInfo = XMLCustomWriter::createElement($doc, 'mods:titleInfo');
		XMLCustomWriter::createChildWithText($doc, $titleInfo, 'mods:title', (string) $suppFile->getTitle($locale));
		XMLCustomWriter::appendChild($mods, $titleInfo);

		$creator = (string) $suppFile->getCreator($locale);
		if ($creator !== '') {
			$creatorNode = XMLCustomWriter::createElement($doc, 'mods:name');
			XMLCustomWriter::setAttribute($creatorNode, 'type', 'personal');
			XMLCustomWriter::createChildWithText($doc, $creatorNode, 'mods:namePart', $creator);
			$role = XMLCustomWriter::createElement($doc, 'mods:role');
			$roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'creator');
			XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
			XMLCustomWriter::appendChild($creatorNode, $role);
			XMLCustomWriter::appendChild($mods, $creatorNode);
		}

		$description = (string) $suppFile->getDescription($locale);
		if ($description !== '') {
			XMLCustomWriter::createChildWithText($doc, $mods, 'mods:abstract', $description);
		}

		$dateCreated = $suppFile->getDateCreated();
		if ($dateCreated) {
			$originInfo = XMLCustomWriter::createElement($doc, 'mods:originInfo');
			$timeIssued = date(DATE_W3C, strtotime((string) $dateCreated));
			XMLCustomWriter::createChildWithText($doc, $originInfo, 'mods:dateCreated', $timeIssued);
			XMLCustomWriter::appendChild($mods, $originInfo);
		}
		XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', 'additional material');

		$type = (string) $suppFile->getType();
		if ($type !== '') {
			XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', $type);
		}

		$typeOther = (string) $suppFile->getTypeOther($locale);
		if ($typeOther !== '') {
			XMLCustomWriter::createChildWithText($doc, $mods, 'mods:genre', $typeOther);
		}

		// $locale sudah tersedia
		$subject = (string) $suppFile->getSubject($locale);
		if ($subject !== '') {
			$subjNode = XMLCustomWriter::createElement($doc, 'mods:subject');
			XMLCustomWriter::createChildWithText($doc, $subjNode, 'mods:topic', $subject);
			XMLCustomWriter::appendChild($mods, $subjNode);
		}

		$sponsor = (string) $suppFile->getSponsor($locale);
		if ($sponsor !== '') {
			$presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
			XMLCustomWriter::setAttribute($presenterNode, 'type', 'corporate');
			XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $sponsor);
			$role = XMLCustomWriter::createElement($doc, 'mods:role');
			$roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'sponsor');
			XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
			XMLCustomWriter::appendChild($presenterNode, $role);
			XMLCustomWriter::appendChild($mods, $presenterNode);
		}

		$publisher = (string) $suppFile->getPublisher($locale);
		if ($publisher !== '') {
			$presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
			XMLCustomWriter::setAttribute($presenterNode, 'type', 'corporate');
			XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', $publisher);
			$role = XMLCustomWriter::createElement($doc, 'mods:role');
			$roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'publisher');
			XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
			XMLCustomWriter::appendChild($presenterNode, $role);
			XMLCustomWriter::appendChild($mods, $presenterNode);
		}

		$language = (string) $suppFile->getLanguage();
		if ($language !== '') {
			XMLCustomWriter::createChildWithText($doc, $mods, 'mods:language', $language);
		}

		XMLCustomWriter::appendChild($xmlData, $mods);
		XMLCustomWriter::appendChild($dmdSec, $mdWrap);
		XMLCustomWriter::appendChild($mdWrap, $xmlData);
		XMLCustomWriter::appendChild($root, $dmdSec);
	}

	/**
	 * Finds all files associated with this Issue by going through all Articles.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Issue $issue
	 */
	public static function generateIssueFileSecDom(&$doc, &$root, &$issue) {
		/** @var PublishedArticleDAO $publishedArticleDao */
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticleArray = $publishedArticleDao->getPublishedArticles((int) $issue->getId());
		foreach ($publishedArticleArray as $publishedArticle) {
			self::generateArticleFilesDom($doc, $root, $publishedArticle, $issue);
		}
	}

	/**
	 * Finds all HTML Galley files associated with this Issue.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Issue $issue
	 */
	public static function generateIssueHtmlGalleyFileSecDom(&$doc, &$root, &$issue) {
		/** @var PublishedArticleDAO $publishedArticleDao */
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticleArray = $publishedArticleDao->getPublishedArticles((int) $issue->getId());
		foreach ($publishedArticleArray as $publishedArticle) {
			self::generateArticleHtmlGalleyFilesDom($doc, $root, $publishedArticle, $issue);
		}
	}

	/**
	 * Finds all files associated with this published Paper.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param Issue $issue
	 */
	public static function generateArticleFilesDom(&$doc, $root, $article, &$issue) {
		/** @var ArticleGalleyDAO $articleGalleyDao */
		$articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
		$galleysArray = $articleGalleyDao->getGalleysByArticle((int) $article->getId());
		foreach ($galleysArray as $galley) {
			if (!$galley->isHTMLGalley()) {
				self::generateArticleFileDom($doc, $root, $article, $galley, null);
			}
		}

		/** @var SuppFileDAO $suppFileDao */
		$suppFileDao = DAORegistry::getDAO('SuppFileDAO');
		$suppFilesArray = $suppFileDao->getSuppFilesByArticle((int) $article->getId());
		foreach ($suppFilesArray as $suppFile) {
			self::generateArticleSuppFileDom($doc, $root, $article, $suppFile);
		}
	}

	/**
	 * Finds all HTML Galley files associated with this published Paper.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param Issue $issue
	 */
	public static function generateArticleHtmlGalleyFilesDom(&$doc, $root, $article, &$issue) {
		/** @var ArticleGalleyDAO $articleGalleyDao */
		$articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
		$galleysArray = $articleGalleyDao->getGalleysByArticle((int) $article->getId());
		foreach ($galleysArray as $galley) {
			if ($galley->isHTMLGalley()) {
				self::generateArticleFileDom($doc, $root, $article, $galley, 'html');
				$images = $galley->getImageFiles();
				foreach ($images as $image) {
					self::generateArticleHtmlGalleyImageFileDom($doc, $root, $article, $galley, $image, 'html');
				}
			}
		}
	}

	/**
	 * Generates the METS:file element for an HTML Galley image file.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param ArticleGalley $galley
	 * @param object $imageFile
	 * @param string|null $useAttribute
	 */
	public static function generateArticleHtmlGalleyImageFileDom(&$doc, &$root, $article, $galley, $imageFile, $useAttribute) {
		import('classes.file.PublicFileManager');
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		
		/** @var JournalDAO $journalDao */
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$journal = $journalDao->getById((int) $article->getJournalId());
		$journalId = $journal ? (int) $journal->getId() : 0;
		
		// [WIZDAM FIX] Resolve "Undefined method/property" by fetching plugin instance dynamically
		$plugin = PluginRegistry::getPlugin('gateways', 'MetsGatewayPlugin');
		$contentWrapper = 'FLocat';
		if ($plugin && $journalId) {
			$cw = $plugin->getSetting($journalId, 'contentWrapper');
			if ($cw !== null && $cw !== '') {
				$contentWrapper = (string) $cw;
			}
		}

		$mfile = XMLCustomWriter::createElement($doc, 'METS:file');
		$filePath = self::getPublicFilePath($imageFile, '/public/');

		$chkmd5return = file_exists($filePath) ? md5_file($filePath) : '';
		XMLCustomWriter::setAttribute($mfile, 'ID', 'F' . (int) $imageFile->getFileId() . '-A' . (int) $article->getId());
		if ($useAttribute !== null) {
			XMLCustomWriter::setAttribute($mfile, 'USE', (string) $useAttribute);
		}
		XMLCustomWriter::setAttribute($mfile, 'SIZE', (string) $imageFile->getFileSize());
		XMLCustomWriter::setAttribute($mfile, 'MIMETYPE', (string) $imageFile->getFileType());
		XMLCustomWriter::setAttribute($mfile, 'OWNERID', (string) $imageFile->getOriginalFileName());
		XMLCustomWriter::setAttribute($mfile, 'CHECKSUM', $chkmd5return);
		XMLCustomWriter::setAttribute($mfile, 'CHECKSUMTYPE', 'MD5');
		
		if ($contentWrapper === 'FContent') {
			$fileContent = $fileManager->readFile($filePath);
			$fContent = XMLCustomWriter::createElement($doc, 'METS:FContent');
			XMLCustomWriter::createChildWithText($doc, $fContent, 'METS:binData', base64_encode((string) $fileContent));
			XMLCustomWriter::appendChild($mfile, $fContent);
		} else {
			$fLocat = XMLCustomWriter::createElement($doc, 'METS:FLocat');
			// Lumera Singleton Fallback for Request
			$request = Application::get()->getRequest();
			$fileUrl = $request->url(null, 'article', 'viewFile', [(int) $article->getId(), $galley->getBestGalleyId($journal), (int) $imageFile->getFileId()]);
			XMLCustomWriter::setAttribute($fLocat, 'xlink:href', $fileUrl);
			XMLCustomWriter::setAttribute($fLocat, 'LOCTYPE', 'URL');
			XMLCustomWriter::appendChild($mfile, $fLocat);
		}
		XMLCustomWriter::appendChild($root, $mfile);
	}

	/**
	 * Creates a METS:file for the paperfile.
	 * Checks if METS:FContent or METS:FLocat should be used.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param ArticleGalley $galleyFile
	 * @param string|null $useAttribute
	 */
	public static function generateArticleFileDom(&$doc, &$root, $article, &$galleyFile, $useAttribute) {
		import('classes.file.PublicFileManager');
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		
		/** @var JournalDAO $journalDao */
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$journal = $journalDao->getById((int) $article->getJournalId());
		$journalId = $journal ? (int) $journal->getId() : 0;
		
		// [WIZDAM FIX] Resolve "Undefined method/property" by fetching plugin instance dynamically
		$plugin = PluginRegistry::getPlugin('gateways', 'MetsGatewayPlugin');
		$contentWrapper = 'FLocat';
		if ($plugin && $journalId) {
			$cw = $plugin->getSetting($journalId, 'contentWrapper');
			if ($cw !== null && $cw !== '') {
				$contentWrapper = (string) $cw;
			}
		}

		$mfile = XMLCustomWriter::createElement($doc, 'METS:file');
		$filePath = self::getPublicFilePath($galleyFile, '/public/');
		$chkmd5return = file_exists($filePath) ? md5_file($filePath) : '';
		
		XMLCustomWriter::setAttribute($mfile, 'ID', 'F' . (int) $galleyFile->getFileId() . '-A' . (int) $galleyFile->getArticleId());
		if ($useAttribute !== null) {
			XMLCustomWriter::setAttribute($mfile, 'USE', (string) $useAttribute);
		}
		XMLCustomWriter::setAttribute($mfile, 'SIZE', (string) $galleyFile->getFileSize());
		XMLCustomWriter::setAttribute($mfile, 'MIMETYPE', (string) $galleyFile->getFileType());
		XMLCustomWriter::setAttribute($mfile, 'OWNERID', (string) $galleyFile->getFileName());
		XMLCustomWriter::setAttribute($mfile, 'CHECKSUM', $chkmd5return);
		XMLCustomWriter::setAttribute($mfile, 'CHECKSUMTYPE', 'MD5');
		
		if ($contentWrapper === 'FContent') {
			$fileContent = $fileManager->readFile($filePath);
			$fContent = XMLCustomWriter::createElement($doc, 'METS:FContent');
			XMLCustomWriter::createChildWithText($doc, $fContent, 'METS:binData', base64_encode((string) $fileContent));
			XMLCustomWriter::appendChild($mfile, $fContent);
		} else {
			$fLocat = XMLCustomWriter::createElement($doc, 'METS:FLocat');
			$fileUrl = self::getPublicFileUrl($galleyFile);
			XMLCustomWriter::setAttribute($fLocat, 'xlink:href', $fileUrl);
			XMLCustomWriter::setAttribute($fLocat, 'LOCTYPE', 'URL');
			XMLCustomWriter::appendChild($mfile, $fLocat);
		}
		XMLCustomWriter::appendChild($root, $mfile);
	}

	/**
	 * Creates a METS:file for the Supplementary File.
	 * Checks if METS:FContent or METS:FLocat should be used.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param PublishedArticle $article
	 * @param SuppFile $suppFile
	 */
	public static function generateArticleSuppFileDom(&$doc, &$root, $article, &$suppFile) {
		import('classes.file.PublicFileManager');
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();
		
		/** @var JournalDAO $journalDao */
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$journal = $journalDao->getById((int) $article->getJournalId());
		$journalId = $journal ? (int) $journal->getId() : 0;
		
		// [WIZDAM FIX] Resolve "Undefined method/property" by fetching plugin instance dynamically
		$plugin = PluginRegistry::getPlugin('gateways', 'MetsGatewayPlugin');
		$contentWrapper = 'FLocat';
		if ($plugin && $journalId) {
			$cw = $plugin->getSetting($journalId, 'contentWrapper');
			if ($cw !== null && $cw !== '') {
				$contentWrapper = (string) $cw;
			}
		}

		$mfile = XMLCustomWriter::createElement($doc, 'METS:file');
		$filePath = self::getPublicFilePath($suppFile, '/supp/');
		$chkmd5return = file_exists($filePath) ? md5_file($filePath) : '';
		
		XMLCustomWriter::setAttribute($mfile, 'ID', 'SF' . (int) $suppFile->getFileId() . '-A' . (int) $suppFile->getArticleId());
		XMLCustomWriter::setAttribute($mfile, 'SIZE', (string) $suppFile->getFileSize());
		XMLCustomWriter::setAttribute($mfile, 'MIMETYPE', (string) $suppFile->getFileType());
		XMLCustomWriter::setAttribute($mfile, 'OWNERID', (string) $suppFile->getFileName());
		XMLCustomWriter::setAttribute($mfile, 'CHECKSUM', $chkmd5return);
		XMLCustomWriter::setAttribute($mfile, 'CHECKSUMTYPE', 'MD5');
		
		if ($contentWrapper === 'FContent') {
			$fileContent = $fileManager->readFile($filePath);
			$fContent = XMLCustomWriter::createElement($doc, 'METS:FContent');
			XMLCustomWriter::createChildWithText($doc, $fContent, 'METS:binData', base64_encode((string) $fileContent));
			XMLCustomWriter::appendChild($mfile, $fContent);
		} else {
			$fLocat = XMLCustomWriter::createElement($doc, 'METS:FLocat');
			$fileUrl = self::getPublicSuppFileUrl($suppFile);
			XMLCustomWriter::setAttribute($fLocat, 'xlink:href', $fileUrl);
			XMLCustomWriter::setAttribute($fLocat, 'LOCTYPE', 'URL');
			XMLCustomWriter::appendChild($mfile, $fLocat);
		}
		XMLCustomWriter::appendChild($root, $mfile);
	}

	/**
	 * Create mods:name for a presenter (author).
	 * @param DOMDocument $doc
	 * @param Author $author
	 * @return DOMElement
	 */
	public static function &generateAuthorDom(&$doc, $author) {
		$presenterNode = XMLCustomWriter::createElement($doc, 'mods:name');
		XMLCustomWriter::setAttribute($presenterNode, 'type', 'personal');
		$fNameNode = XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', trim((string) $author->getFirstName() . ' ' . (string) $author->getMiddleName()));
		XMLCustomWriter::setAttribute($fNameNode, 'type', 'given');
		$lNameNode = XMLCustomWriter::createChildWithText($doc, $presenterNode, 'mods:namePart', (string) $author->getLastName());
		XMLCustomWriter::setAttribute($lNameNode, 'type', 'family');
		$role = XMLCustomWriter::createElement($doc, 'mods:role');
		$roleTerm = XMLCustomWriter::createChildWithText($doc, $role, 'mods:roleTerm', 'author');
		XMLCustomWriter::setAttribute($roleTerm, 'type', 'text');
		XMLCustomWriter::appendChild($presenterNode, $role);
		
		return $presenterNode;
	}

	/**
	 * Create METS:amdSec for the Conference/Journal.
	 * @param DOMDocument $doc
	 * @param DOMElement $root
	 * @param Journal $journal
	 * @return DOMElement
	 */
	public static function createmetsamdSec($doc, &$root, &$journal) {
		$amdSec = XMLCustomWriter::createElement($doc, 'METS:amdSec');
		$techMD = XMLCustomWriter::createElement($doc, 'METS:techMD');
		XMLCustomWriter::setAttribute($techMD, 'ID', 'A-' . (int) $journal->getId());
		$mdWrap = XMLCustomWriter::createElement($doc, 'METS:mdWrap');
		XMLCustomWriter::setAttribute($mdWrap, 'MDTYPE', 'PREMIS');
		$xmlData = XMLCustomWriter::createElement($doc, 'METS:xmlData');
		$pObject = XMLCustomWriter::createElement($doc, 'premis:object');
		XMLCustomWriter::setAttribute($pObject, 'xmlns:premis', 'http://www.loc.gov/standards/premis/v1');
		
		$existingSchema = (string) $root->getAttribute('xsi:schemaLocation');
		$newSchema = str_replace(
			' http://www.loc.gov/standards/premis/v1 http://www.loc.gov/standards/premis/v1/PREMIS-v1-1.xsd',
			'',
			$existingSchema
		) . ' http://www.loc.gov/standards/premis/v1 http://www.loc.gov/standards/premis/v1/PREMIS-v1-1.xsd';
		XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', $newSchema);
		
		$objectIdentifier = XMLCustomWriter::createElement($doc, 'premis:objectIdentifier');
		XMLCustomWriter::createChildWithText($doc, $objectIdentifier, 'premis:objectIdentifierType', 'internal');
		XMLCustomWriter::createChildWithText($doc, $objectIdentifier, 'premis:objectIdentifierValue', 'J-' . (int) $journal->getId());
		XMLCustomWriter::appendChild($pObject, $objectIdentifier);
		
		$journalId = (int) $journal->getId();
		// [WIZDAM FIX] Resolve "Undefined method/property" by fetching plugin instance dynamically
		$plugin = PluginRegistry::getPlugin('gateways', 'MetsGatewayPlugin');
		$preservationLevel = '1';
		if ($plugin) {
			$pl = $plugin->getSetting($journalId, 'preservationLevel');
			if ($pl !== null && $pl !== '') {
				$preservationLevel = (string) $pl;
			}
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
	 * Create METS:metsHdr for export.
	 * @param DOMDocument $doc
	 * @return DOMElement
	 */
	public static function createmetsHdr($doc) {
		$root = XMLCustomWriter::createElement($doc, 'METS:metsHdr');
		XMLCustomWriter::setAttribute($root, 'CREATEDATE', date('c'));
		XMLCustomWriter::setAttribute($root, 'LASTMODDATE', date('c'));
		$agentNode = XMLCustomWriter::createElement($doc, 'METS:agent');
		XMLCustomWriter::setAttribute($agentNode, 'ROLE', 'DISSEMINATOR');
		XMLCustomWriter::setAttribute($agentNode, 'TYPE', 'ORGANIZATION');
		
		// Lumera Singleton Fallback for Request
		$request = Application::get()->getRequest();
		$journal = $request->getJournal();
		$journalId = $journal ? (int) $journal->getId() : 0;
		
		// [WIZDAM FIX] Resolve "Undefined method/property" by fetching plugin instance dynamically
		$plugin = PluginRegistry::getPlugin('gateways', 'MetsGatewayPlugin');
		$organization = '';
		if ($plugin && $journalId) {
			$org = $plugin->getSetting($journalId, 'organization');
			if ($org !== null && $org !== '') {
				$organization = (string) $org;
			}
		}
		
		if ($organization === '') {
			/** @var SiteDAO $siteDao */
			$siteDao = DAORegistry::getDAO('SiteDAO');
			$site = $siteDao->getSite();
			$organization = $site ? (string) $site->getLocalizedTitle() : 'Unknown Organization';
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
	 * Creator is the Lumera Apps System.
	 * @return string
	 */
	public static function getCreatorString() {
		/** @var VersionDAO $versionDao */
		$versionDao = DAORegistry::getDAO('VersionDAO');
		$cVersion = $versionDao->getCurrentVersion();

		return sprintf('Lumera v%d.%d.%d build %d', $cVersion->getMajor(), $cVersion->getMinor(), $cVersion->getRevision(), $cVersion->getBuild());
	}

	/**
	 * Function getPublicFilePath had to be added due to problems in the current
	 * $paperFile->getFilePath(); for Galley Files.
	 * @param object $file
	 * @param string $pathComponent
	 * @return string
	 */
	public static function getPublicFilePath(&$file, $pathComponent) {
		$articleId = (int) $file->getArticleId();

		/** @var ArticleDAO $articleDao */
		$articleDao = DAORegistry::getDAO('ArticleDAO');
		$article = $articleDao->getArticle($articleId);
		$journalId = $article ? (int) $article->getJournalId() : 0;

		return Config::getVar('files', 'files_dir') . '/journals/' . $journalId .
		'/articles/' . $articleId . '/' . $pathComponent . '/' . $file->getFileName();
	}

	/**
	 * Function getPublicFileUrl !!!! must be a better way....
	 * @param object $file
	 * @return string
	 */
	public static function getPublicFileUrl(&$file) {
		import('classes.config.Config');
		$base_url = (string) Config::getVar('general', 'base_url');

		/** @var ArticleDAO $articleDao */
		$articleDao = DAORegistry::getDAO('ArticleDAO');
		$article = $articleDao->getArticle((int) $file->getArticleId());

		/** @var JournalDAO $journalDao */
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$journal = $journalDao->getById($article ? (int) $article->getJournalId() : 0);
		
		$journalPath = $journal ? $journal->getPath() : 'index';
		$url = $base_url . '/index.php/' . $journalPath . '/article/download/' . (int) $file->getArticleId() . '/' . $file->getBestGalleyId($journal);
		return $url;
	}

	/**
	 * Function getPublicSuppFileUrl !!!! must be a better way....
	 * @param object $file
	 * @return string
	 */
	public static function getPublicSuppFileUrl(&$file) {
		import('classes.config.Config');
		$base_url = (string) Config::getVar('general', 'base_url');

		/** @var ArticleDAO $articleDao */
		$articleDao = DAORegistry::getDAO('ArticleDAO');
		$article = $articleDao->getArticle((int) $file->getArticleId());

		/** @var JournalDAO $journalDao */
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$journal = $journalDao->getById($article ? (int) $article->getJournalId() : 0);

		$journalPath = $journal ? $journal->getPath() : 'index';
		$url = $base_url . '/index.php/' . $journalPath . '/article/downloadSuppFile/' . (int) $file->getArticleId() . '/' . (int) $file->getSuppFileId();
		return $url;
	}

}
?>