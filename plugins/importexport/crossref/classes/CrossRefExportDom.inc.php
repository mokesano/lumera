<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/CrossRefExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefExportDom
 * @ingroup plugins_importexport_crossref_classes
 *
 * @brief CrossRef XML export format implementation.
 */

if (!class_exists('DOIExportDom')) {
    import('plugins.importexport.crossref.classes.DOIExportDom');
}

// XML attributes
define('CROSSREF_XMLNS_XSI' , 'http://www.w3.org/2001/XMLSchema-instance');
define('CROSSREF_XMLNS' , 'http://www.crossref.org/schema/4.3.6');
define('CROSSREF_VERSION' , '4.3.6');
define('CROSSREF_XSI_SCHEMAVERSION' , '4.3.6');
define('CROSSREF_XSI_SCHEMALOCATION' , 'http://www.crossref.org/schema/4.3.6 https://www.crossref.org/schemas/crossref4.3.6.xsd');

class CrossRefExportDom extends DOIExportDom {

    /**
     * Constructor
     * @param PKPRequest $request
     * @param CrossrefDoiExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function __construct($request, $plugin, $journal, $objectCache) {
        parent::__construct($request, $plugin, $journal, $objectCache);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param PKPRequest $request
     * @param CrossrefDoiExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function CrossRefExportDom($request, $plugin, $journal, $objectCache) {
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
    // Public methods
    //
    /**
     * Generate the CrossRef XML document.
     * @param array $objects Array of objects to export
     * @return object XMLDocument
     * @see DOIExportDom::generate()
     */
    public function generate($objects) {
        $journal = $this->getJournal();

        // Create the XML document and its root element.
        $doc = $this->getDoc();
        $rootElement = $this->rootElement();
        XMLCustomWriter::appendChild($doc, $rootElement);

        // Create Head Node and all parts inside it
        $head = $this->_generateHeadDom($doc, $journal);
        XMLCustomWriter::appendChild($rootElement, $head);

        // The body node contains everything
        $bodyNode = XMLCustomWriter::createElement($doc, 'body');
        XMLCustomWriter::appendChild($rootElement, $bodyNode);

        foreach ($objects as $object) {
            // Retrieve required publication objects.
            $pubObjects = $this->retrievePublicationObjects($object);
            $issue = $pubObjects['issue'] ?? null;
            $article = $pubObjects['article'] ?? null;

            if ($object instanceof Issue && $issue) {
                $articlesByIssue = $pubObjects['articlesByIssue'] ?? [];
                foreach ($articlesByIssue as $articleItem) {
                    if ($articleItem->getPubId('doi')) {
                        $this->_appendArticleXML($doc, $journal, $issue, $articleItem, $bodyNode);
                    }
                }
            } elseif ($object instanceof PublishedArticle && $article) {
                if ($article->getPubId('doi')) {
                    $this->_appendArticleXML($doc, $journal, $issue, $article, $bodyNode);
                }
            }
        }

        return $doc;
    }

    //
    // Implementation of template methods from DOIExportDom
    //
    /**
     * Get the name of the root element.
     * @see DOIExportDom::getRootElementName()
     * @return string
     */
    public function getRootElementName(): string {
        return 'doi_batch';
    }

    /**
     * Get the XML namespace.
     * @see DOIExportDom::getNamespace()
     * @return string
     */
    public function getNamespace(): string {
        return CROSSREF_XMLNS;
    }

    /**
     * Get the XML schema version.
     * @see DOIExportDom::getXmlSchemaVersion()
     * @return string
     */
    public function getXmlSchemaVersion(): string {
        return CROSSREF_XSI_SCHEMAVERSION;
    }

    /**
     * Get the XML schema location.
     * @see DOIExportDom::getXmlSchemaLocation()
     * @return string
     */
    public function getXmlSchemaLocation(): string {
        return CROSSREF_XSI_SCHEMALOCATION;
    }

    /**
     * Retrieve the publication objects required for export.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return array
     * @see DOIExportDom::retrievePublicationObjects()
     */
    public function retrievePublicationObjects($object): array {
        // Retrieve basic Lumera objects.
        $publicationObjects = parent::retrievePublicationObjects($object);

        // For issues: Retrieve all articles of the issue.
        if ($object instanceof Issue && isset($publicationObjects['issue'])) {
            $publicationObjects['articlesByIssue'] = $this->retrieveArticlesByIssue($publicationObjects['issue']);
        }

        return $publicationObjects;
    }

    //
    // Private helper methods
    //
    /**
     * Generate the <head> tag that accompanies each submission
     * @see DOIExportDom::generateHeadDom()
     * @param object $doc
     * @param Journal $journal
     * @return object
     */
    public function _generateHeadDom($doc, $journal) {
        $head = XMLCustomWriter::createElement($doc, 'head');

        // DOI batch ID is a simple tracking ID: initials + timestamp
        $initials = (string) $journal->getSetting('initials', $journal->getPrimaryLocale());
        XMLCustomWriter::createChildWithText($doc, $head, 'doi_batch_id', $initials . '_' . time());
        XMLCustomWriter::createChildWithText($doc, $head, 'timestamp', (string) time());

        $journalId = (int) $journal->getId();
        $plugin = $this->_plugin;

        // [WIZDAM] Resolusi lewat DoiCredentialService dulu -- jurnal
        // Ownership (publisherPartnerships=false) memakai nama/email
        // depositor Publisher terpusat (halaman DOI Settings site admin).
        // Jurnal Partnership tetap memakai setting plugin miliknya sendiri
        // (DoiCredentialService::resolveForJournal() sudah menanganinya).
        // Fallback ke kontak dukungan jurnal DIPERTAHANKAN sebagai jaring
        // pengaman terakhir kalau kredensial Publisher pun belum diisi.
        import('lib.wizdam.classes.services.DoiCredentialService');
        $doiCredentials = DoiCredentialService::resolveForJournal($journal);

        $depositorName = $doiCredentials->getCrossrefDepositorName();
        if ($depositorName === '') {
            $depositorName = (string) $journal->getSetting('supportName');
        }

        $depositorEmail = $doiCredentials->getCrossrefEmail();
        if ($depositorEmail === '') {
            $depositorEmail = (string) $journal->getSetting('supportEmail');
        }
        
        $depositorNode = $this->_generateDepositorDom($doc, $depositorName, $depositorEmail);
        XMLCustomWriter::appendChild($head, $depositorNode);

        /* The registrant is assumed to be the Publishing institution */
        $publisherInstitution = (string) $journal->getSetting('publisherInstitution');
        XMLCustomWriter::createChildWithText($doc, $head, 'registrant', $publisherInstitution);

        return $head;
    }

    /**
     * Generate depositor node
     * @see DOIExportDom::generateDepositorDom()
     * @param object $doc
     * @param string $name
     * @param string $email
     * @return object
     */
    public function _generateDepositorDom($doc, $name, $email) {
        $depositor = XMLCustomWriter::createElement($doc, 'depositor');
        XMLCustomWriter::createChildWithText($doc, $depositor, 'depositor_name', $name);
        XMLCustomWriter::createChildWithText($doc, $depositor, 'email_address', $email);

        return $depositor;
    }

    /**
     * Generate and append the XML per article
     * @see DOIExportDom::appendArticleXML()
     * @param object $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param PublishedArticle $article
     * @param object $bodyNode
     */
    public function _appendArticleXML($doc, $journal, $issue, $article, $bodyNode) {
        $sectionId = (int) $article->getSectionId();
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($sectionId);

        // Create the journal node
        $journalNode = XMLCustomWriter::createElement($doc, 'journal');
        $journalMetadataNode = $this->_generateJournalMetadataDom($doc, $journal);
        XMLCustomWriter::appendChild($journalNode, $journalMetadataNode);

        // Create the journal_issue node
        $journalIssueNode = $this->_generateJournalIssueDom($doc, $journal, $issue, $section, $article);
        XMLCustomWriter::appendChild($journalNode, $journalIssueNode);

        // Create the article node
        $journalArticleNode = $this->_generateJournalArticleDom($doc, $journal, $issue, $section, $article);
        XMLCustomWriter::appendChild($journalNode, $journalArticleNode);
        XMLCustomWriter::appendChild($bodyNode, $journalNode);
    }

    /**
     * Generate metadata for journal - accompanies every article
     * @see DOIExportDom::generateJournalMetadataDom()
     * @param object $doc
     * @param Journal $journal
     * @return object
     */
    public function _generateJournalMetadataDom($doc, $journal) {
        $journalMetadataNode = XMLCustomWriter::createElement($doc, 'journal_metadata');

        /* Full Title of Journal */
        $journalTitle = (string) $journal->getTitle($journal->getPrimaryLocale());
        // Attempt a fall back, in case the localized name is not set.
        if ($journalTitle === '') {
            $journalTitle = (string) $journal->getSetting('abbreviation', $journal->getPrimaryLocale());
        }
        XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'full_title', $journalTitle);

        /* Abbreviated title - defaulting to initials if no abbreviation found */
        $abbreviation = (string) $journal->getSetting('abbreviation', $journal->getPrimaryLocale());
        if ($abbreviation !== '') {
            XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'abbrev_title', $abbreviation);
        } else {
            $initials = (string) $journal->getSetting('initials', $journal->getPrimaryLocale());
            XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'abbrev_title', $initials);
        }

        /* Both ISSNs are permitted for CrossRef, so sending whichever one (or both) */
        $onlineIssn = (string) $journal->getSetting('onlineIssn');
        if ($onlineIssn !== '') {
            $onlineISSNNode = XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'issn', $onlineIssn);
            XMLCustomWriter::setAttribute($onlineISSNNode, 'media_type', 'electronic');
        }

        $printIssn = (string) $journal->getSetting('printIssn');
        if ($printIssn !== '') {
            $printISSNNode = XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'issn', $printIssn);
            XMLCustomWriter::setAttribute($printISSNNode, 'media_type', 'print');
        }

        return $journalMetadataNode;
    }

    /**
     * Generate journal issue tag to accompany every article
     * @see DOIExportDom::generateJournalIssueDom()
     * @param object $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param Section $section
     * @param PublishedArticle $article
     * @return object
     */
    public function _generateJournalIssueDom($doc, $journal, $issue, $section, $article) {
        $journalIssueNode = XMLCustomWriter::createElement($doc, 'journal_issue');

        if ($issue->getDatePublished()) {
            $publicationDateNode = $this->_generatePublisherDateDom($doc, $issue->getDatePublished());
            XMLCustomWriter::appendChild($journalIssueNode, $publicationDateNode);
        }

        $volume = $issue->getVolume();
        if ($volume !== null && $volume !== '') {
            $journalVolumeNode = XMLCustomWriter::createElement($doc, 'journal_volume');
            XMLCustomWriter::appendChild($journalIssueNode, $journalVolumeNode);
            XMLCustomWriter::createChildWithText($doc, $journalVolumeNode, 'volume', (string) $volume);
        }
        
        $number = $issue->getNumber();
        if ($number !== null && $number !== '') {
            XMLCustomWriter::createChildWithText($doc, $journalIssueNode, 'issue', (string) $number);
        }

        $issueDoi = $issue->getPubId('doi');
        if ($issue->getDatePublished() && $issueDoi) {
            $request = $this->getRequest();
            $issueUrl = $request->url($journal->getPath(), 'issue', 'view', [$issue->getBestIssueId($journal)]);
            $issueDoiNode = $this->_generateDOIdataDom($doc, $issueDoi, $issueUrl);
            XMLCustomWriter::appendChild($journalIssueNode, $issueDoiNode);
        }

        return $journalIssueNode;
    }

    /**
     * Generate the journal_article node (the heart of the file).
     * @see DOIExportDom::generateJournalArticleDom()
     * @param object $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param Section $section
     * @param PublishedArticle $article
     * @return object
     */
    public function _generateJournalArticleDom($doc, $journal, $issue, $section, $article) {
        // Create the base node
        $journalArticleNode = XMLCustomWriter::createElement($doc, 'journal_article');
        XMLCustomWriter::setAttribute($journalArticleNode, 'publication_type', 'full_text');
        XMLCustomWriter::setAttribute($journalArticleNode, 'metadata_distribution_opts', 'any');

        /* Titles */
        $titlesNode = XMLCustomWriter::createElement($doc, 'titles');
        XMLCustomWriter::createChildWithText($doc, $titlesNode, 'title', (string) $article->getTitle($article->getLocale()));
        XMLCustomWriter::appendChild($journalArticleNode, $titlesNode);

        /* AuthorList */
        $contributorsNode = XMLCustomWriter::createElement($doc, 'contributors');
        $isFirst = true;
        $authors = $article->getAuthors();
        if (is_array($authors)) {
            foreach ($authors as $author) {
                $authorNode = $this->_generateAuthorDom($doc, $author, $isFirst);
                $isFirst = false;
                XMLCustomWriter::appendChild($contributorsNode, $authorNode);
            }
        }
        XMLCustomWriter::appendChild($journalArticleNode, $contributorsNode);

        /* Abstracts */
        $abstract = $article->getAbstract($journal->getPrimaryLocale());
        if (!empty($abstract)) {
            $abstractNode = XMLCustomWriter::createElement($doc, 'jats:abstract');
            XMLCustomWriter::createChildWithText($doc, $abstractNode, 'jats:p', PKPString::html2utf(strip_tags((string) $abstract)));
            XMLCustomWriter::appendChild($journalArticleNode, $abstractNode);
        }

        /* publication date of article */
        $datePublished = $article->getDatePublished() ?: $issue->getDatePublished();
        if ($datePublished) {
            $publicationDateNode = $this->_generatePublisherDateDom($doc, $datePublished);
            XMLCustomWriter::appendChild($journalArticleNode, $publicationDateNode);
        }

        /* publisher_item is the article pages */
        $pages = $article->getPageArray();
        if (is_array($pages) && !empty($pages)) {
            $firstRange = array_shift($pages);
            $firstPage = (string) array_shift($firstRange);
            $lastPage = !empty($firstRange) ? (string) array_shift($firstRange) : '';
            
            // CrossRef accepts no punctuation in first_page or last_page
            if ((!empty($firstPage) || $firstPage === "0") && !preg_match('/[^[:alnum:]]/', $firstPage) && !preg_match('/[^[:alnum:]]/', $lastPage)) {
                $pageNode = XMLCustomWriter::createElement($doc, 'pages');
                XMLCustomWriter::createChildWithText($doc, $pageNode, 'first_page', $firstPage);
                if ($lastPage !== '') {
                    XMLCustomWriter::createChildWithText($doc, $pageNode, 'last_page', $lastPage);
                }
                $otherPages = '';
                foreach ($pages as $range) {
                    $otherPages .= ($otherPages !== '' ? ',' : '') . implode('-', $range);
                }
                if ($otherPages !== '') {
                    XMLCustomWriter::createChildWithText($doc, $pageNode, 'other_pages', $otherPages);
                }
                XMLCustomWriter::appendChild($journalArticleNode, $pageNode);
            }
        }

        /* License URL -- dibangun sebagai NODE dulu, TIDAK langsung
         * di-append. Skema 4.3.6 mendefinisikan <crossmark> dan
         * <ai:program> sebagai <xsd:choice> -- SALING EKSKLUSIF sebagai
         * saudara langsung journal_article. Kalau Crossmark aktif
         * (kebijakan DOI sudah dikonfigurasi), node lisensi ini
         * dipindahkan ke DALAM <crossmark><custom_metadata> oleh
         * _generateCrossmarkDom() di bawah -- BUKAN di-append langsung
         * di sini. Kalau Crossmark belum dikonfigurasi, node ini tetap
         * berdiri sendiri seperti perilaku asli (tidak ada regresi). */
        $licenseUrl = $article->getLicenseUrl();
        $licenseNode = null;
        if (!empty($licenseUrl)) {
            $licenseNode = XMLCustomWriter::createElement($doc, 'ai:program');
            XMLCustomWriter::setAttribute($licenseNode, 'name', 'AccessIndicators');
            XMLCustomWriter::createChildWithText($doc, $licenseNode, 'ai:license_ref', (string) $licenseUrl);
        }

        // [WIZDAM] Crossmark -- lihat _generateCrossmarkDom() untuk detail
        // lengkap. Mengembalikan null kalau kebijakan Crossmark belum
        // dikonfigurasi Publisher (wizdam_doi_crossmark_policy_doi kosong)
        // -- dalam kasus itu, $licenseNode (kalau ada) di-append berdiri
        // sendiri seperti sebelumnya, TIDAK ADA REGRESI untuk instalasi
        // yang belum mengisi pengaturan ini.
        $crossmarkNode = $this->_generateCrossmarkDom($doc, $journal, $article, $licenseNode);
        if ($crossmarkNode) {
            XMLCustomWriter::appendChild($journalArticleNode, $crossmarkNode);
        } elseif ($licenseNode) {
            XMLCustomWriter::appendChild($journalArticleNode, $licenseNode);
        }

        // DOI data node
        /** @var ArticleGalleyDAO $articleGalleyDao */
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $request = $this->getRequest();
        $articleUrl = $request->url($journal->getPath(), 'article', 'view', [$article->getBestArticleId()]);
        $galleys = $articleGalleyDao->getGalleysByArticle((int) $article->getId());
        
        $DOIdataNode = $this->_generateDOIdataDom($doc, (string) $article->getPubId('doi'), $articleUrl, $galleys);
        XMLCustomWriter::appendChild($journalArticleNode, $DOIdataNode);

        // [WIZDAM] Sitasi (referensi) artikel -- diikutsertakan kalau
        // penulis/editor sudah menginput daftar sitasi untuk artikel ini
        // (lewat editor sitasi di halaman submission), terlepas deposit
        // ini dijalankan otomatis (Acron/CrossrefInfoSender) maupun
        // manual (export lewat halaman plugin). Lihat _generateCitationListDom()
        // untuk detail pemetaan ke skema Crossref.
        $citationListNode = $this->_generateCitationListDom($doc, $article);
        if ($citationListNode) {
            XMLCustomWriter::appendChild($journalArticleNode, $citationListNode);
        }

        $componentListNode = $this->_generateComponentListDom($doc, $journal, $article);
        if ($componentListNode) {
            XMLCustomWriter::appendChild($journalArticleNode, $componentListNode);
        }

        return $journalArticleNode;
    }

    /**
     * Generate the citation_list node -- sitasi/referensi artikel yang
     * sudah diinput lewat editor sitasi (CitationGridHandler, disimpan
     * di tabel `citations` lewat CitationDAO). Dipakai baik untuk
     * deposit otomatis (Acron -> CrossrefInfoSender) maupun manual
     * (export lewat halaman pengaturan plugin) karena keduanya sama-sama
     * memanggil generate() -> _generateJournalArticleDom() di kelas ini.
     *
     * Memakai <unstructured_citation> (teks mentah apa adanya, field
     * rawCitation) alih-alih elemen terstruktur (journal_title, author,
     * volume, dst.) -- rawCitation SELALU tersedia begitu sitasi diinput,
     * TIDAK bergantung apakah sitasi itu sudah melalui proses parsing
     * terstruktur (CITATION_PARSED/CITATION_LOOKED_UP) atau belum.
     * @param object $doc
     * @param PublishedArticle $article
     * @return object|null
     */
    public function _generateCitationListDom($doc, $article) {
        /** @var CitationDAO $citationDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $citations = $citationDao->getObjectsByAssocId(ASSOC_TYPE_ARTICLE, (int) $article->getId());

        $citationListNode = null;
        $key = 0;
        while ($citation = $citations->next()) {
            $rawCitation = trim((string) $citation->getRawCitation());
            if ($rawCitation === '') {
                continue;
            }
            if ($citationListNode === null) {
                $citationListNode = XMLCustomWriter::createElement($doc, 'citation_list');
            }
            $key++;
            $citationNode = XMLCustomWriter::createElement($doc, 'citation');
            XMLCustomWriter::setAttribute($citationNode, 'key', 'ref' . $key);
            XMLCustomWriter::createChildWithText(
                $doc,
                $citationNode,
                'unstructured_citation',
                PKPString::html2utf(strip_tags($rawCitation))
            );
            XMLCustomWriter::appendChild($citationListNode, $citationNode);
        }

        return $citationListNode;
    }

    /**
     * Generate elemen <crossmark> -- mengembalikan null (tidak menyisipkan
     * apa pun) kalau kebijakan Crossmark Publisher belum dikonfigurasi.
     *
     * STRUKTUR (sesuai XSD 4.3.6, urutan WAJIB):
     *   crossmark_version, crossmark_policy, custom_metadata
     *     (custom_metadata berisi: ai:program yang DIPINDAHKAN ke sini
     *      -- lihat penjelasan xsd:choice di _generateJournalArticleDom --
     *      lalu assertion publication_history: received/accepted/
     *      published_online)
     *
     * SUMBER TANGGAL publication_history (dikonfirmasi via ArticleHandler
     * dan template artikel, BUKAN dugaan):
     *   - received        -> Submission::getDateSubmitted() (tanggal submit asli)
     *   - accepted         -> ArticleDAO::getEditorialTimeline()['acceptedDate']
     *                         (dari tabel edit_decisions, decision=1 --
     *                         SUMBER SAMA yang dipakai ArticleHandler
     *                         menampilkan tanggal diterima di halaman
     *                         artikel publik)
     *   - published_online -> Article::getDatePublished() (sudah dipakai
     *                         di tempat lain pada file ini)
     * Tanggal yang kosong/tidak ditemukan dilewati -- TIDAK memaksakan
     * assertion dengan nilai kosong/dugaan.
     * @param object $doc
     * @param Journal $journal
     * @param PublishedArticle $article
     * @param object|null $licenseNode Node ai:program yang sudah dibangun
     *   sebelumnya (atau null kalau artikel tidak punya lisensi) --
     *   dipindahkan KE DALAM custom_metadata di sini, bukan dibangun ulang.
     * @return object|null
     */
    public function _generateCrossmarkDom($doc, $journal, $article, $licenseNode) {
        import('lib.wizdam.classes.services.DoiCredentialService');
        $doiCredentials = DoiCredentialService::resolveForJournal($journal);
        $crossmarkPolicyDoi = $doiCredentials->getCrossmarkPolicyDoi();

        if ($crossmarkPolicyDoi === '') {
            return null;
        }

        $crossmarkNode = XMLCustomWriter::createElement($doc, 'crossmark');
        XMLCustomWriter::createChildWithText($doc, $crossmarkNode, 'crossmark_version', '1');
        XMLCustomWriter::createChildWithText($doc, $crossmarkNode, 'crossmark_policy', $crossmarkPolicyDoi);

        $customMetadataNode = XMLCustomWriter::createElement($doc, 'custom_metadata');

        // ai:program (lisensi) yang sebelumnya berdiri sendiri --
        // dipindahkan ke sini karena xsd:choice (lihat dokblok di atas).
        if ($licenseNode) {
            XMLCustomWriter::appendChild($customMetadataNode, $licenseNode);
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $timeline = $articleDao->getEditorialTimeline((int) $article->getId());

        $order = 0;
        $received = $article->getDateSubmitted();
        if (!empty($received)) {
            $this->_appendPublicationHistoryAssertion($doc, $customMetadataNode, 'received', 'Received', $received, $order++);
        }
        $accepted = $timeline['acceptedDate'] ?? null;
        if (!empty($accepted)) {
            $this->_appendPublicationHistoryAssertion($doc, $customMetadataNode, 'accepted', 'Accepted', $accepted, $order++);
        }
        $publishedOnline = $article->getDatePublished();
        if (!empty($publishedOnline)) {
            $this->_appendPublicationHistoryAssertion($doc, $customMetadataNode, 'published_online', 'Published Online', $publishedOnline, $order++);
        }

        XMLCustomWriter::appendChild($crossmarkNode, $customMetadataNode);

        return $crossmarkNode;
    }

    /**
     * Helper -- satu <assertion> grup publication_history, format tanggal
     * YYYY-MM-DD (cocok dengan contoh XML resmi Crossref dan hasil nyata
     * yang sudah terdeposit sebelumnya untuk publisher ini).
     * @param object $doc
     * @param object $parentNode
     * @param string $name
     * @param string $label
     * @param string $rawDate
     * @param int $order
     */
    private function _appendPublicationHistoryAssertion($doc, $parentNode, $name, $label, $rawDate, $order) {
        $timestamp = strtotime((string) $rawDate);
        if ($timestamp === false) {
            return;
        }
        $assertionNode = XMLCustomWriter::createElement($doc, 'assertion');
        XMLCustomWriter::setAttribute($assertionNode, 'name', $name);
        XMLCustomWriter::setAttribute($assertionNode, 'label', $label);
        XMLCustomWriter::setAttribute($assertionNode, 'group_name', 'publication_history');
        XMLCustomWriter::setAttribute($assertionNode, 'group_label', 'Publication History');
        XMLCustomWriter::setAttribute($assertionNode, 'order', (string) $order);
        $textNode = XMLCustomWriter::createTextNode($doc, date('Y-m-d', $timestamp));
        XMLCustomWriter::appendChild($assertionNode, $textNode);
        XMLCustomWriter::appendChild($parentNode, $assertionNode);
    }

    /**
     * Generate the component_list node (supplementary files).
     * @see DOIExportDom::generateComponentListDom()
     * @param object $doc
     * @param Journal $journal
     * @param PublishedArticle $article
     * @return object|null
     */
    public function _generateComponentListDom($doc, $journal, $article) {
        $suppFiles = $article->getSuppFiles();
        if (!is_array($suppFiles)) {
            return null;
        }

        $createComponentList = false;
        foreach ($suppFiles as $suppFile) {
            if ($suppFile->getPubId('doi')) {
                $createComponentList = true;
                break;
            }
        }
        
        if (!$createComponentList) {
            return null;
        }

        // Create the base node
        $componentListNode = XMLCustomWriter::createElement($doc, 'component_list');
        $request = $this->getRequest();

        // Run through supp files and add component nodes.
        foreach ($suppFiles as $suppFile) {
            if ($suppFile->getPubId('doi')) {
                $componentNode = XMLCustomWriter::createElement($doc, 'component');
                XMLCustomWriter::setAttribute($componentNode, 'parent_relation', 'isPartOf');

                /* Titles */
                $suppFileTitle = (string) $suppFile->getSuppFileTitle();
                if ($suppFileTitle !== '') {
                    $titlesNode = XMLCustomWriter::createElement($doc, 'titles');
                    XMLCustomWriter::createChildWithText($doc, $titlesNode, 'title', $suppFileTitle);
                    XMLCustomWriter::appendChild($componentNode, $titlesNode);
                }

                // DOI data node
                $suppFileUrl = $request->url(
                    $journal->getPath(), 'article', 'downloadSuppFile', 
                    [(int) $article->getId(), $suppFile->getBestSuppFileId($journal)]
                );
                $suppFileDoiNode = $this->_generateDOIdataDom($doc, (string) $suppFile->getPubId('doi'), $suppFileUrl);
                XMLCustomWriter::appendChild($componentNode, $suppFileDoiNode);
                XMLCustomWriter::appendChild($componentListNode, $componentNode);
            }
        }

        return $componentListNode;
    }

    /**
     * Generate doi_data element - this is what assigns the DOI
     * @param object $doc 
     * @param string $DOI
     * @param string $url
     * @param array|null $galleys
     * @return object
     */
    public function _generateDOIdataDom($doc, $DOI, $url, $galleys = null) {
        $journal = $this->getJournal();
        $request = $this->getRequest();
        $DOIdataNode = XMLCustomWriter::createElement($doc, 'doi_data');
        XMLCustomWriter::createChildWithText($doc, $DOIdataNode, 'doi', $DOI);
        XMLCustomWriter::createChildWithText($doc, $DOIdataNode, 'resource', $url);

        /* article galleys */
        if (is_array($galleys) && !empty($galleys)) {
            foreach ($galleys as $galley) {
                $collectionNode = XMLCustomWriter::createElement($doc, 'collection');
                XMLCustomWriter::setAttribute($collectionNode, 'property', 'crawler-based');
                XMLCustomWriter::appendChild($DOIdataNode, $collectionNode);
                
                $itemNode = XMLCustomWriter::createElement($doc, 'item');
                XMLCustomWriter::setAttribute($itemNode, 'crawler', 'iParadigms');
                XMLCustomWriter::appendChild($collectionNode, $itemNode);
                
                $resourceNode = XMLCustomWriter::createElement($doc, 'resource');
                XMLCustomWriter::appendChild($itemNode, $resourceNode);

                $galleyUrl = $request->url($journal->getPath(), 'article', 'viewFile', [(int) $galley->getArticleId(), $galley->getBestGalleyId($journal)]);
                $urlNode = XMLCustomWriter::createTextNode($doc, $galleyUrl);
                XMLCustomWriter::appendChild($resourceNode, $urlNode);
            }

            // text-mining collection element
            $collectionNode = XMLCustomWriter::createElement($doc, 'collection');
            XMLCustomWriter::setAttribute($collectionNode, 'property', 'text-mining');
            XMLCustomWriter::appendChild($DOIdataNode, $collectionNode);
            
            foreach ($galleys as $galley) {
                $itemNode = XMLCustomWriter::createElement($doc, 'item');
                XMLCustomWriter::appendChild($collectionNode, $itemNode);
                
                $resourceNode = XMLCustomWriter::createElement($doc, 'resource');
                XMLCustomWriter::appendChild($itemNode, $resourceNode);
                
                $remoteGalleyURL = $galley->getRemoteURL();
                if (empty($remoteGalleyURL)) {
                    XMLCustomWriter::setAttribute($resourceNode, 'mime_type', (string) $galley->getFileType());
                }

                $galleyUrl = $request->url($journal->getPath(), 'article', 'viewFile', [(int) $galley->getArticleId(), $galley->getBestGalleyId($journal)]);
                $urlNode = XMLCustomWriter::createTextNode($doc, $galleyUrl);
                XMLCustomWriter::appendChild($resourceNode, $urlNode);
            }
        }

        return $DOIdataNode;
    }

    /**
     * Generate author node
     * @param object $doc
     * @param Author $author
     * @param bool $isFirst
     * @return object
     */
    public function _generateAuthorDom($doc, $author, $isFirst = false) {
        $authorNode = XMLCustomWriter::createElement($doc, 'person_name');
        XMLCustomWriter::setAttribute($authorNode, 'contributor_role', 'author');
        if ($isFirst) {
            XMLCustomWriter::setAttribute($authorNode, 'sequence', 'first');
        } else {
            XMLCustomWriter::setAttribute($authorNode, 'sequence', 'additional');
        }

        $firstName = ucfirst((string) $author->getFirstName());
        $middleName = $author->getMiddleName() ? ' ' . ucfirst((string) $author->getMiddleName()) : '';
        XMLCustomWriter::createChildWithText($doc, $authorNode, 'given_name', $firstName . $middleName);
        XMLCustomWriter::createChildWithText($doc, $authorNode, 'surname', ucfirst((string) $author->getLastName()));

        // [WIZDAM] Afiliasi penulis -- elemen <affiliation> skema 4.3.6
        // adalah TEKS POLOS SEDERHANA (bukan struktur <affiliations>
        // <institution> yang baru mulai skema 5.3+ -- lihat dokumentasi
        // resmi Crossref: "replace <affiliation> tag with <affiliations>
        // tag ... changes from 4.8.1" ke versi 5.3.1). Codebase ini
        // memakai 4.3.6, jadi sengaja TIDAK memakai struktur
        // <affiliations><institution> yang lebih baru.
        //
        // Urutan elemen WAJIB sesuai XSD 4.3.6: given_name, surname,
        // suffix, affiliation, ORCID -- affiliation HARUS sebelum ORCID.
        //
        // Teks afiliasi dibangun lewat _buildAuthorAffiliationText() --
        // logika PERSIS SAMA dengan PKPAuthor::buildAffiliationMap()
        // (dipakai halaman artikel publik) supaya konsisten: baris
        // afiliasi dipecah per baris baru, dan NEGARA dari field Country
        // terpisah (CountryDAO) ditambahkan ke baris TERAKHIR -- bukan
        // cuma mengandalkan teks yang diketik manual, yang mungkin lupa
        // menyertakan negara.
        $affiliation = $this->_buildAuthorAffiliationText($author);
        if ($affiliation !== '') {
            XMLCustomWriter::createChildWithText($doc, $authorNode, 'affiliation', $affiliation);
        }

        $orcid = $author->getData('orcid');
        if (!empty($orcid)) {
            XMLCustomWriter::createChildWithText($doc, $authorNode, 'ORCID', (string) $orcid);
        }

        return $authorNode;
    }

    /**
     * Bangun teks afiliasi LENGKAP untuk SATU penulis -- baris-baris
     * afiliasi (dipisah enter/baris baru) digabung, dengan nama negara
     * (dari field Country terpisah, CountryDAO) ditambahkan ke baris
     * TERAKHIR. Mereplikasi logika inti PKPAuthor::buildAffiliationMap()
     * (dipakai untuk deduplikasi lintas-penulis di halaman artikel
     * publik) tapi disederhanakan untuk SATU penulis saja -- elemen
     * <affiliation> Crossref 4.3.6 cuma butuh satu string polos per
     * penulis, tidak perlu deduplikasi/referensi silang antar penulis
     * seperti tampilan halaman publik.
     * @param Author $author
     * @return string
     */
    public function _buildAuthorAffiliationText($author) {
        $lines = preg_split('/\r\n|\r|\n/', (string) $author->getLocalizedAffiliation());
        $lines = array_values(array_filter(array_map('trim', $lines), function ($line) {
            return $line !== '';
        }));

        if (!empty($lines) && $author->getCountry()) {
            $countryName = (string) $author->getCountryLocalized();
            if ($countryName !== '') {
                $lastIndex = count($lines) - 1;
                if (stripos($lines[$lastIndex], $countryName) === false) {
                    $lines[$lastIndex] .= ', ' . $countryName;
                }
            }
        }

        return implode('; ', $lines);
    }

    /**
     * Generate publisher date - order matters
     * @param object $doc
     * @param string $pubdate 
     * @return object
     */
    public function _generatePublisherDateDom($doc, $pubdate) {
        $publicationDateNode = XMLCustomWriter::createElement($doc, 'publication_date');
        XMLCustomWriter::setAttribute($publicationDateNode, 'media_type', 'online');

        $parsedPubdate = strtotime((string) $pubdate);
        if ($parsedPubdate !== false) {
            XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'month', date('m', $parsedPubdate), false);
            XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'day', date('d', $parsedPubdate), false);
            XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'year', date('Y', $parsedPubdate));
        }

        return $publicationDateNode;
    }
    
}
?>