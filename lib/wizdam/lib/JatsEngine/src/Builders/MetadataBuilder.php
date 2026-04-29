<?php

declare(strict_types=1);

namespace Wizdam\JatsEngine\Builders;

use DAORegistry;
use DOMDocument;
use DOMElement;
use Config;

class MetadataBuilder {

    protected int $articleId;
    protected $articleDAO;
    protected $authorDAO;
    protected $journalDAO;
    protected $issueDAO;
    protected $sectionDAO;
    protected $publishedArticleDAO;
    protected $sectionEditorSubmissionDAO;

    public function __construct(int $articleId) {
        $this->articleId = $articleId;
        $this->articleDAO = DAORegistry::getDAO('ArticleDAO');
        $this->authorDAO = DAORegistry::getDAO('AuthorDAO');
        $this->journalDAO = DAORegistry::getDAO('JournalDAO');
        $this->issueDAO = DAORegistry::getDAO('IssueDAO');
        $this->sectionDAO = DAORegistry::getDAO('SectionDAO');
        $this->publishedArticleDAO = DAORegistry::getDAO('PublishedArticleDAO');
        
        // OJS 2.x DAO Import
        import('classes.submission.sectionEditor.SectionEditorSubmissionDAO');
        $this->sectionEditorSubmissionDAO = DAORegistry::getDAO('SectionEditorSubmissionDAO');
    }

    protected function getArticleObj() {
        $article = $this->publishedArticleDAO->getPublishedArticleByArticleId($this->articleId);
        if (!$article) {
            $article = $this->articleDAO->getArticle($this->articleId);
        }
        return $article;
    }

    protected function getJournalSafe($journalId) {
        if (method_exists($this->journalDAO, 'getById')) {
            return $this->journalDAO->getById($journalId);
        }
        return $this->journalDAO->getJournal($journalId);
    }

    /**
     * HELPER BARU: Ambil data dengan aman (String atau Array)
     * Mengatasi konflik Locale PHP vs OJS
     */
    protected function getSafeData($article, $key) {
        // 1. Ambil Raw Data
        $val = $article->getData($key);

        // 2. Jika Array (Multibahasa), kita harus pilih satu
        if (is_array($val)) {
            $locale = null;
            
            // SOLUSI CRASH: Gunakan AppLocale, bukan Locale
            if (class_exists('AppLocale')) {
                $locale = \AppLocale::getLocale();
            } elseif (class_exists('Locale') && method_exists('Locale', 'getLocale')) {
                // Fallback legacy OJS lama sekali (jarang terjadi jika PHP modern)
                $locale = \Locale::getLocale();
            }

            // Jika locale ditemukan dan datanya ada, kembalikan
            if ($locale && isset($val[$locale])) {
                return $val[$locale];
            }
            
            // ROBUST FALLBACK: Jika locale error/tidak ketemu, 
            // ambil saja elemen pertama dari array data. Jangan crash.
            return reset($val); 
        }

        // 3. Jika String, kembalikan langsung
        return $val;
    }

    // ==========================================
    // BAGIAN 1: FRONT MATTER
    // ==========================================
    public function buildFront(DOMDocument $dom): void {
        $root = $dom->documentElement;
        $article = $this->getArticleObj();

        if (!$article) {
            throw new \Exception("Artikel ID " . $this->articleId . " tidak ditemukan.");
        }

        $front = $dom->createElement('front');
        $root->appendChild($front);

        // --- 1. JOURNAL METADATA ---
        $journal = $this->getJournalSafe($article->getJournalId());
        $journalMeta = $dom->createElement('journal-meta');
        
        $jTitleGroup = $dom->createElement('journal-title-group');
        $jTitleGroup->appendChild($dom->createElement('journal-title', htmlspecialchars($journal->getLocalizedTitle())));
        
        $abbrev = $journal->getLocalizedSetting('abbreviation') ?: $journal->getPath();
        $jTitleGroup->appendChild($dom->createElement('journal-subtitle', htmlspecialchars($abbrev)));
        $journalMeta->appendChild($jTitleGroup);

        if ($issn = $journal->getSetting('printIssn')) {
            $issnNode = $dom->createElement('issn', htmlspecialchars($issn));
            $issnNode->setAttribute('publication-format', 'print');
            $journalMeta->appendChild($issnNode);
        }
        if ($eissn = $journal->getSetting('onlineIssn') ?: $journal->getSetting('eIssn')) {
            $issnNode = $dom->createElement('issn', htmlspecialchars($eissn));
            $issnNode->setAttribute('publication-format', 'electronic');
            $journalMeta->appendChild($issnNode);
        }

        $publisher = $dom->createElement('publisher');
        $pubName = $journal->getSetting('publisherInstitution') ?: 'Publisher';
        $publisher->appendChild($dom->createElement('publisher-name', htmlspecialchars($pubName)));
        $journalMeta->appendChild($publisher);

        $front->appendChild($journalMeta);

        // --- 2. ARTICLE METADATA ---
        $articleMeta = $dom->createElement('article-meta');

        // ID & DOI
        $articleIdNode = $dom->createElement('article-id', (string)$this->articleId);
        $articleIdNode->setAttribute('pub-id-type', 'publisher-id');
        $articleMeta->appendChild($articleIdNode);

        $doi = $this->getDoiSafe($article);
        if ($doi) {
            $doiNode = $dom->createElement('article-id', htmlspecialchars($doi));
            $doiNode->setAttribute('pub-id-type', 'doi');
            $articleMeta->appendChild($doiNode);
        }

        // Section
        if ($article->getSectionId()) {
            $section = $this->sectionDAO->getSection($article->getSectionId());
            if ($section) {
                $categories = $dom->createElement('article-categories');
                $subjGroup = $dom->createElement('subj-group');
                $subjGroup->setAttribute('subj-group-type', 'heading');
                $subject = $dom->createElement('subject', htmlspecialchars($section->getLocalizedTitle()));
                $subjGroup->appendChild($subject);
                $categories->appendChild($subjGroup);
                $articleMeta->appendChild($categories);
            }
        }

        // Title
        $titleGroup = $dom->createElement('title-group');
        $titleGroup->appendChild($dom->createElement('article-title', htmlspecialchars($article->getLocalizedTitle())));
        $articleMeta->appendChild($titleGroup);

        // Contributors
        $contribGroup = $dom->createElement('contrib-group');
        $authors = $article->getAuthors(); 
        
        foreach ($authors as $author) {
            $contrib = $dom->createElement('contrib');
            $contrib->setAttribute('contrib-type', 'author');
            
            if ($author->getPrimaryContact()) {
                $contrib->setAttribute('corresp', 'yes');
            }
            
            $name = $dom->createElement('name');
            $name->appendChild($dom->createElement('surname', htmlspecialchars($author->getLastName())));
            $name->appendChild($dom->createElement('given-names', htmlspecialchars($author->getFirstName())));
            $contrib->appendChild($name);

            if ($email = $author->getEmail()) {
                $contrib->appendChild($dom->createElement('email', htmlspecialchars($email)));
            }
            
            $affRaw = $author->getAffiliation(null);
            $affString = is_array($affRaw) ? reset($affRaw) : (string)$affRaw;
            if (!empty($affString)) {
                $affiliations = preg_split('/\r\n|\r|\n/', $affString);
                foreach ($affiliations as $singleAff) {
                    if (trim($singleAff) !== '') {
                        $contrib->appendChild($dom->createElement('aff', htmlspecialchars(trim($singleAff))));
                    }
                }
            }
            $contribGroup->appendChild($contrib);
        }
        $articleMeta->appendChild($contribGroup);

        // --- HISTORY / DATES ---
        $this->buildDates($dom, $articleMeta, $article);

        // Volume & Issue
        if (method_exists($article, 'getIssueId') && $article->getIssueId()) {
            $issue = $this->issueDAO->getIssueById($article->getIssueId());
            if ($issue) {
                if ($issue->getVolume()) $articleMeta->appendChild($dom->createElement('volume', htmlspecialchars((string)$issue->getVolume())));
                if ($issue->getNumber()) $articleMeta->appendChild($dom->createElement('issue', htmlspecialchars((string)$issue->getNumber())));
            }
        }

        // Pages
        if ($pages = $article->getPages()) {
            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $pages, $matches)) {
                $articleMeta->appendChild($dom->createElement('fpage', $matches[1]));
                $articleMeta->appendChild($dom->createElement('lpage', $matches[2]));
            } else {
                $articleMeta->appendChild($dom->createElement('fpage', htmlspecialchars($pages)));
            }
        }

        // PERMISSIONS
        $permissions = $dom->createElement('permissions');
        
        // Gunakan getSafeData
        $copyHolder = $this->getSafeData($article, 'copyrightHolder') ?: $pubName;
        $copyYear   = $this->getSafeData($article, 'copyrightYear');
        
        if (!$copyYear && $article->getDatePublished()) {
            $copyYear = date('Y', strtotime($article->getDatePublished()));
        }

        $permissions->appendChild($dom->createElement('copyright-statement', "Copyright © $copyYear $copyHolder"));
        $permissions->appendChild($dom->createElement('copyright-year', (string)$copyYear));
        $permissions->appendChild($dom->createElement('copyright-holder', htmlspecialchars($copyHolder)));

        // Gunakan getSafeData
        $licenseUrl = $this->getSafeData($article, 'licenseURL');
        if ($licenseUrl) {
            $license = $dom->createElement('license');
            $license->setAttribute('xlink:href', $licenseUrl);
            $permissions->appendChild($license);
        }
        $articleMeta->appendChild($permissions);

        // Abstract
        if ($absContent = $article->getLocalizedAbstract()) {
            $abstract = $dom->createElement('abstract');
            $cleanAbs = strip_tags($absContent); 
            $abstract->appendChild($dom->createElement('p', htmlspecialchars($cleanAbs)));
            $articleMeta->appendChild($abstract);
        }

        // Keywords
        $subjects = $article->getLocalizedSubject(); 
        if (!$subjects) $subjects = $this->getSafeData($article, 'subject');

        if ($subjects) {
            $kwdGroup = $dom->createElement('kwd-group');
            $kwdGroup->setAttribute('kwd-group-type', 'author');
            $kwds = preg_split('/[,;]+/', $subjects);
            foreach ($kwds as $k) {
                if (trim($k)) {
                    $kwdGroup->appendChild($dom->createElement('kwd', htmlspecialchars(trim($k))));
                }
            }
            $articleMeta->appendChild($kwdGroup);
        }

        // Funding
        $sponsor = $this->getSafeData($article, 'sponsor');
        if ($sponsor) {
            $fundingGroup = $dom->createElement('funding-group');
            $awardGroup = $dom->createElement('award-group');
            $fundingSource = $dom->createElement('funding-source', htmlspecialchars(strip_tags($sponsor)));
            $awardGroup->appendChild($fundingSource);
            $fundingGroup->appendChild($awardGroup);
            $articleMeta->appendChild($fundingGroup);
        }

        $front->appendChild($articleMeta);
    }

    // --- LOGIKA TANGGAL ---
    protected function buildDates(DOMDocument $dom, DOMElement $articleMeta, $article) {
        $history = $dom->createElement('history');
        
        if ($dateSubmitted = $article->getDateSubmitted()) {
            $this->appendDateNode($dom, $history, 'received', $dateSubmitted);
        }

        $rawDecisions = $this->sectionEditorSubmissionDAO->getEditorDecisions($this->articleId);
        $allDecisions = [];
        if (is_array($rawDecisions)) {
            foreach ($rawDecisions as $k => $v) {
                if (is_array($v)) {
                    if (isset($v['decision'])) {
                        $allDecisions[] = $v;
                    } else {
                        foreach ($v as $subV) {
                            if (is_array($subV) && isset($subV['decision'])) {
                                $allDecisions[] = $subV;
                            }
                        }
                    }
                }
            }
        }

        $acceptedDate = null;
        $revisedDate = null;

        foreach ($allDecisions as $d) {
            if (!isset($d['decision']) || !isset($d['dateDecided'])) continue;
            if ($d['decision'] == 1) $acceptedDate = $d['dateDecided']; 
            if ($d['decision'] == 2 || $d['decision'] == 3) $revisedDate = $d['dateDecided']; 
        }

        if ($revisedDate) {
            $this->appendDateNode($dom, $history, 'revised', $revisedDate);
        }

        if ($acceptedDate) {
            $this->appendDateNode($dom, $history, 'accepted', $acceptedDate);
        }

        if ($dateStatusModified = $article->getDateStatusModified()) {
            $this->appendDateNode($dom, $history, 'online', $dateStatusModified);
        }

        if ($lastModified = $article->getLastModified()) {
            $this->appendDateNode($dom, $history, 'version-of-record', $lastModified);
        }

        $articleMeta->appendChild($history);

        if ($article->getDatePublished()) {
            $pubDate = $dom->createElement('pub-date');
            $pubDate->setAttribute('pub-type', 'epub');
            $ts = strtotime($article->getDatePublished());
            if ($ts !== false) {
                $pubDate->appendChild($dom->createElement('day', date('d', $ts)));
                $pubDate->appendChild($dom->createElement('month', date('m', $ts)));
                $pubDate->appendChild($dom->createElement('year', date('Y', $ts)));
                $articleMeta->appendChild($pubDate);
            }
        }
    }

    protected function appendDateNode($dom, $parent, $type, $dateString) {
        if (empty($dateString)) return;
        $ts = strtotime($dateString);
        if ($ts === false) return;

        $dateNode = $dom->createElement('date');
        $dateNode->setAttribute('date-type', $type);
        $dateNode->appendChild($dom->createElement('day', date('d', $ts)));
        $dateNode->appendChild($dom->createElement('month', date('m', $ts)));
        $dateNode->appendChild($dom->createElement('year', date('Y', $ts)));
        $parent->appendChild($dateNode);
    }

    protected function getDoiSafe($article) {
        $doi = '';
        if (method_exists($article, 'getData')) {
            $doi = $article->getData('doi') ?: $article->getData('pub-id::doi');
        }
        if (!$doi) {
            try {
                if (method_exists($article, 'getPubId')) $doi = $article->getPubId('doi');
                elseif (method_exists($article, 'getDOI')) $doi = $article->getDOI();
            } catch (\Throwable $e) {}
        }
        return $doi;
    }

    public function buildBack(DOMDocument $dom): void {
        $root = $dom->documentElement;
        $article = $this->getArticleObj();
        if (!$article) return;

        $rawCitations = $article->getCitations();
        if (!empty($rawCitations)) {
            $back = $dom->createElement('back');
            $refList = $dom->createElement('ref-list');
            $refList->appendChild($dom->createElement('title', 'References'));
            
            $citations = explode("\n", $rawCitations);
            $i = 1;
            foreach ($citations as $cit) {
                if (trim($cit) === '') continue;
                $ref = $dom->createElement('ref');
                $ref->setAttribute('id', 'B' . $i);
                $mixedCit = $dom->createElement('mixed-citation', htmlspecialchars(trim($cit)));
                $mixedCit->setAttribute('publication-type', 'other');
                $ref->appendChild($mixedCit);
                $refList->appendChild($ref);
                $i++;
            }
            $back->appendChild($refList);
            $root->appendChild($back);
        }
    }
}