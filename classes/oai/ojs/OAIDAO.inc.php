<?php
declare(strict_types=1);

/**
 * @file classes/oai/ojs/OAIDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * OAI DAO untuk OJS. Mengimplementasikan logika spesifik jurnal
 * di atas PKPOAIDAO yang bersifat generik.
 *
 * [WIZDAM EDITION] REFACTOR: PHP 8.1+ Compatibility, HookRegistry::dispatch
 */

import('lib.pkp.classes.oai.PKPOAIDAO');
import('classes.issue.Issue');

class OAIDAO extends PKPOAIDAO {

    /** @var JournalDAO */
    public JournalDAO $journalDao;

    /** @var SectionDAO */
    public SectionDAO $sectionDao;

    /** @var PublishedArticleDAO */
    public PublishedArticleDAO $publishedArticleDao;

    /** @var ArticleGalleyDAO */
    public ArticleGalleyDAO $articleGalleyDao;

    /** @var IssueDAO */
    public IssueDAO $issueDao;

    /** @var AuthorDAO */
    public AuthorDAO $authorDao;

    /** @var SuppFileDAO */
    public SuppFileDAO $suppFileDao;

    /** @var JournalSettingsDAO */
    public JournalSettingsDAO $journalSettingsDao;

    /** @var array Cache untuk jurnal */
    public array $journalCache = [];

    /** @var array Cache untuk section */
    public array $sectionCache = [];

    /** @var array Cache untuk issue */
    public array $issueCache = [];

    /**
     * Constructor.
     *
     * Menginisialisasi DAO parent dan memuat seluruh DAO terkait
     * yang diperlukan untuk membangun record OAI (artikel, issue,
     * journal, galleys, dsb.)
     */
    public function __construct() {
        parent::__construct();
        
        $this->journalDao = DAORegistry::getDAO('JournalDAO');
        $this->sectionDao = DAORegistry::getDAO('SectionDAO');
        $this->publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $this->articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $this->issueDao = DAORegistry::getDAO('IssueDAO');
        $this->authorDao = DAORegistry::getDAO('AuthorDAO');
        $this->suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $this->journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
    }

    /**
     * Return the *nix timestamp of the earliest published submission.
     *
     * Catatan: fungsi ini override perilaku PKPOAIDAO karena struktur OJS
     * menggunakan join tabel artikel/issue.
     *
     * @param string|array|null $selectStatement Jika array, berarti argumen pertama
     * adalah $setIds (kompatibilitas versi lama).
     * @param array $setIds Daftar ID set OAI (journalId, sectionId)
     * @return int Timestamp UNIX
     */
    public function getEarliestDatestamp($selectStatement = null, $setIds = []) {
        if (is_array($selectStatement)) {
            $setIds = $selectStatement;
        }

        return parent::getEarliestDatestamp(
            'SELECT CASE WHEN COALESCE(dot.date_deleted, a.last_modified) > i.last_modified THEN i.last_modified ELSE COALESCE(dot.date_deleted, a.last_modified) END', 
            $setIds
        );
    }

    /**
     * Ambil jurnal dengan cache internal.
     *
     * @param int $journalId
     * @return Journal|null
     */
    public function getJournal($journalId) {
        if (!isset($this->journalCache[$journalId])) {
            $this->journalCache[$journalId] = $this->journalDao->getById($journalId);
        }
        return $this->journalCache[$journalId];
    }

    /**
     * Ambil section jurnal dengan cache internal.
     *
     * @param int $sectionId
     * @return Section|null
     */
    public function getSection($sectionId) {
        if (!isset($this->sectionCache[$sectionId])) {
            $this->sectionCache[$sectionId] = $this->sectionDao->getSection($sectionId);
        }
        return $this->sectionCache[$sectionId];
    }
    
    /**
     * Ambil issue dengan cache internal.
     *
     * @param int $issueId
     * @return Issue|null
     */
    public function getIssue($issueId) {
        if (!isset($this->issueCache[$issueId])) {
            $this->issueCache[$issueId] = $this->issueDao->getIssueById($issueId);
        }
        return $this->issueCache[$issueId];
    }

    //
    // Sets
    //
    
    /**
     * Mengembalikan daftar set OAI untuk jurnal tertentu,
     * termasuk:
     * - set jurnal (journal-level set)
     * - set section (section-level set)
     * - set tombstone (record yang sudah dihapus)
     *
     * @param int|null $journalId Jika null, ambil semua jurnal
     * @param int $offset
     * @param int $limit
     * @return array ['data' => array(OAISet), 'total' => int]
     */
    public function getJournalSets($journalId, $offset, $limit) {
        if (isset($journalId)) {
            $journal = $this->journalDao->getById($journalId);
            $journals = $journal ? [$journal] : [];
        } else {
            $journalsResult = $this->journalDao->getJournals(true);
            $journals = $journalsResult->toArray();
        }

        $sets = [];

        foreach ($journals as $journal) {
            $title = $journal->getLocalizedTitle();
            $abbrev = $journal->getPath();
            $sets[] = new OAISet(urlencode($abbrev), $title, '');

            $tombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO');
            $articleTombstoneSets = $tombstoneDao->getSets(ASSOC_TYPE_JOURNAL, $journal->getId());

            $sections = $this->sectionDao->getJournalSections($journal->getId());
            foreach ($sections->toArray() as $section) {
                $spec = urlencode($abbrev) . ':' . urlencode($section->getLocalizedAbbrev());
                if (array_key_exists($spec, $articleTombstoneSets)) {
                    unset($articleTombstoneSets[$spec]);
                }
                $sets[] = new OAISet($spec, $section->getLocalizedTitle(), '');
            }

            foreach ($articleTombstoneSets as $spec => $name) {
                $sets[] = new OAISet($spec, $name, '');
            }
        }

        $total = count($sets);

        // [WIZDAM PROTOCOL] Modernized Hook Call
        HookRegistry::dispatch('OAIDAO::getJournalSets', [$this, $journalId, $offset, $limit, $total, &$sets]);

        return [
            'data' => array_slice($sets, $offset, $limit),
            'total' => $total
        ];
    }

    //
    // Protected: OAI Record Construction
    //

    /**
     * Set metadata OAI spesifik untuk OJS pada objek OAIRecord atau OAIIdentifier.
     *
     * @param OAIRecord|OAIIdentifier $record
     * @param array $row Record dari database
     * @param bool $isRecord True jika menghasilkan OAIRecord (bukan hanya identifier)
     * @return OAIRecord|OAIIdentifier
     */
    public function setOAIData($record, $row, $isRecord) {
        $journal = $this->getJournal($row['journal_id']);
        $section = $this->getSection($row['section_id']);
        $articleId = $row['article_id'];

        $record->identifier = $this->oai->articleIdToIdentifier($articleId);
        $record->sets = [urlencode($journal->getPath()) . ':' . urlencode($section->getLocalizedAbbrev())];

        if ($isRecord) {
            $publishedArticle = $this->publishedArticleDao->getPublishedArticleByArticleId($articleId);
            $issue = $this->getIssue($row['issue_id']);
            $galleys = $this->articleGalleyDao->getGalleysByArticle($articleId);

            $record->setData('article', $publishedArticle);
            $record->setData('journal', $journal);
            $record->setData('section', $section);
            $record->setData('issue', $issue);
            $record->setData('galleys', $galleys);
        }

        return $record;
    }

    /**
     * Mengambil pasangan (journalId, sectionId) untuk sebuah set OAI.
     *
     * @param string $journalSpec Path jurnal (encoded)
     * @param string|null $sectionSpec Abreviasi section (encoded)
     * @param int|null $restrictJournalId Batasi hanya untuk jurnal tertentu
     * @return array Array dengan [journalId, sectionId]
     */
    public function getSetJournalSectionId($journalSpec, $sectionSpec, $restrictJournalId = null) {
        $journal = $this->journalDao->getJournalByPath($journalSpec);
        if (!isset($journal) || (isset($restrictJournalId) && $journal->getId() != $restrictJournalId)) {
            return [0, 0];
        }

        $journalId = $journal->getId();
        $sectionId = null;

        if (isset($sectionSpec)) {
            $section = $this->sectionDao->getSectionByAbbrev($sectionSpec, $journalId);
            $sectionId = $section ? $section->getId() : 0;
        }

        return [$journalId, $sectionId];
    }

    /**
     * @inheritdoc
     * Menghasilkan SELECT untuk OAI record (artikel + tombstone).
     */
    public function getRecordSelectStatement() {
        return 'SELECT CASE WHEN COALESCE(dot.date_deleted, a.last_modified) < i.last_modified THEN i.last_modified ELSE COALESCE(dot.date_deleted, a.last_modified) END AS last_modified,
            COALESCE(a.article_id, dot.data_object_id) AS article_id,
            COALESCE(j.journal_id, tsoj.assoc_id) AS journal_id,
            COALESCE(tsos.assoc_id, s.section_id) AS section_id,
            i.issue_id,
            dot.tombstone_id,
            dot.set_spec,
            dot.oai_identifier';
    }

    /**
     * @inheritdoc
     * Join artikel, issue, jurnal, section, dan tombstone.
     */
    public function getRecordJoinClause($articleId = null, $setIds = [], $set = null) {
        if (isset($setIds[1])) {
            [$journalId, $sectionId] = $setIds;
        } else {
            [$journalId] = $setIds;
        }

        return 'LEFT JOIN published_articles pa ON (m.i=0' . (isset($articleId) ? ' AND pa.article_id = ?' : '') . ')
            LEFT JOIN articles a ON (a.article_id = pa.article_id' . (isset($journalId) ? ' AND a.journal_id = ?' : '') . (isset($sectionId) ? ' AND a.section_id = ?' : '') .')
            LEFT JOIN issues i ON (i.issue_id = pa.issue_id)
            LEFT JOIN sections s ON (s.section_id = a.section_id)
            LEFT JOIN journals j ON (j.journal_id = a.journal_id)
            LEFT JOIN data_object_tombstones dot ON (m.i = 1' . (isset($articleId) ? ' AND dot.data_object_id = ?' : '') . (isset($set) ? ' AND dot.set_spec = ?' : '') .')
            LEFT JOIN data_object_tombstone_oai_set_objects tsoj ON ' . (isset($journalId) ? '(tsoj.tombstone_id = dot.tombstone_id AND tsoj.assoc_type = ' . ASSOC_TYPE_JOURNAL . ' AND tsoj.assoc_id = ?)' : 'tsoj.assoc_id = null') .
            ' LEFT JOIN data_object_tombstone_oai_set_objects tsos ON ' . (isset($sectionId) ? '(tsos.tombstone_id = dot.tombstone_id AND tsos.assoc_type = ' . ASSOC_TYPE_SECTION . ' AND tsos.assoc_id = ?)' : 'tsos.assoc_id = null');
    }

    /**
     * @inheritdoc
     * Mengembalikan kondisi WHERE yang hanya menampilkan artikel yang:
     * - published
     * - section valid
     * - jurnal aktif
     * - tidak di-archive
     * atau record tombstone.
     */
    public function getAccessibleRecordWhereClause() {
        return 'WHERE ((s.section_id IS NOT NULL AND i.published = 1 AND j.enabled = 1 AND a.status <> ' . STATUS_ARCHIVED . ') OR dot.data_object_id IS NOT NULL)';
    }

    /**
     * @inheritdoc
     * Menghasilkan kondisi WHERE berdasarkan rentang tanggal OAI.
     *
     * @param int|null $from Timestamp awal
     * @param int|null $until Timestamp akhir
     * @return string SQL fragment
     */
    public function getDateRangeWhereClause($from, $until) {
        return (isset($from) ? ' AND CASE WHEN COALESCE(dot.date_deleted, a.last_modified) < i.last_modified THEN (i.last_modified >= ' . $this->datetimeToDB($from) . ') ELSE ((dot.date_deleted IS NOT NULL AND dot.date_deleted >= ' . $this->datetimeToDB($from) . ') OR (dot.date_deleted IS NULL AND a.last_modified >= ' . $this->datetimeToDB($from) . ')) END' : '')
            . (isset($until) ? ' AND CASE WHEN COALESCE(dot.date_deleted, a.last_modified) < i.last_modified THEN (i.last_modified <= ' . $this->datetimeToDB($until) . ') ELSE ((dot.date_deleted IS NOT NULL AND dot.date_deleted <= ' . $this->datetimeToDB($until) . ') OR (dot.date_deleted IS NULL AND a.last_modified <= ' . $this->datetimeToDB($until) . ')) END' : '')
            . ' ORDER BY a.journal_id';
    }
}

?>