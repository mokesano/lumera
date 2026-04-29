<?php
declare(strict_types=1);

/**
 * @file pages/section/SectionHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionHandler
 * @ingroup Handler
 *
 * @brief Class handling requests for section pages
 * [WIZDAM] - Dedicated Handler for Section Pages
 * Architecture: Section as "Mini Journal"
 * Supports Dynamic RESTful Slugs (e.g., /section/sosial-ekonomi-pertanian)
 * Supports Sub-routes: /section/slug/about, /section/slug/articles
 */

import('classes.handler.Handler');

class SectionHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Fallback jika URL hanya /section
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index(array $args = [], $request = null) {
        Request::redirect(null, 'index');
    }

    /**
     * Menampilkan halaman about section.
     * URL: /{context}/section/{slug}/about
     * [WIZDAM] $args[0] berisi slug section karena PKPPageRouter
     * meletakkan segmen sebelum $op di dalam $args.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function about(array $args = [], $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $journal = $request->getJournal();
        if (!$journal) {
            Request::redirect(null, 'index');
            return;
        }

        $targetSection = $this->_resolveSection(isset($args[0]) ? $args[0] : '', $journal);
        if (!$targetSection) {
            Request::redirect(null, 'index');
            return;
        }

        $this->_showSectionAbout($targetSection, $journal, $request);
    }

    /**
     * Menampilkan semua artikel section dengan paginasi.
     * URL: /{context}/section/{slug}/articles
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function articles(array $args = [], $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $journal = $request->getJournal();
        if (!$journal) {
            Request::redirect(null, 'index');
            return;
        }

        $targetSection = $this->_resolveSection(isset($args[0]) ? $args[0] : '', $journal);
        if (!$targetSection) {
            Request::redirect(null, 'index');
            return;
        }

        $this->_showSectionArticles($targetSection, $journal, $request);
    }

    /**
     * [WIZDAM] Entry point untuk semua slug section.
     * PKPPageRouter sudah menangani kebab-case → camelCase.
     * __call() hanya menangani slug dinamis — sub-route ditangani method eksplisit.
     * @param string $op slug section dalam camelCase dari router
     * @param array $arguments args dari router
     */
    public function __call(string $op, array $arguments) {
        $journal = Request::getJournal();
        if (!$journal) {
            Request::redirect(null, 'index');
            return;
        }
    
        $targetSection = $this->_resolveSection($op, $journal);
        if (!$targetSection) {
            Request::redirect(null, 'index');
            return;
        }
    
        // [WIZDAM] Sub-route ada di $args, bukan di $op
        $args     = isset($arguments[0]) && is_array($arguments[0]) ? $arguments[0] : [];
        $subRoute = isset($args[0]) ? strtolower(trim((string) $args[0])) : '';
    
        $request = Application::get()->getRequest();
    
        switch ($subRoute) {
            case 'about':
                $this->_showSectionAbout($targetSection, $journal, $request);
                break;
            case 'articles':
                $this->_showSectionArticles($targetSection, $journal, $request);
                break;
            default:
                $this->_showSectionIndex($targetSection, $journal);
        }
    }

    // =========================================================================
    // PRIVATE — ROUTE HANDLERS
    // =========================================================================

    /**
     * Halaman index section: 4 artikel terbaru + editor.
     * [WIZDAM] Data editor dikirim sebagai User object langsung — tidak dibungkus
     * array terbatas. Template bebas mengakses semua getter PKPUser.
     * @param object $section
     * @param object $journal
     */
    private function _showSectionIndex($section, $journal): void {
        $this->setupSectionTemplate($section);
        $templateMgr = TemplateManager::getManager();

        $request = Application::get()->getRequest();

        $templateMgr->assign('section',             $section);
        $templateMgr->assign('journalTitle',         $journal->getLocalizedTitle());
        $templateMgr->assign('journalInitials',      $journal->getLocalizedSetting('initials'));
        $templateMgr->assign('printIssn',            $journal->getSetting('printIssn'));
        $templateMgr->assign('onlineIssn',           $journal->getSetting('onlineIssn'));
        $templateMgr->assign('sectionEditors',       $this->_getSectionEditors($section, $journal));
        $templateMgr->assign('publishedArticles',    $this->_getSectionArticles($section, $journal, 4));
        $templateMgr->assign('totalArticleCount',    $this->_getSectionArticleCount($section));
        $templateMgr->assign('allArticlesUrl',       $request->url(null, 'section', $section->getSectionUrlTitle(), ['articles']));
        $templateMgr->assign('aboutUrl',             $request->url(null, 'section', $section->getSectionUrlTitle(), ['about']));

        $templateMgr->display('section/index.tpl');
    }

    /**
     * Halaman about section: detail + kebijakan + lead editor.
     * [WIZDAM] Lead editor dikirim sebagai User object langsung.
     * @param object $section
     * @param object $journal
     * @param PKPRequest $request
     */
    private function _showSectionAbout($section, $journal, $request): void {
        $this->setupSectionTemplate($section);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('section',             $section);
        $templateMgr->assign('journalTitle',         $journal->getLocalizedTitle());
        $templateMgr->assign('journalInitials',      $journal->getLocalizedSetting('initials'));
        $templateMgr->assign('printIssn',            $journal->getSetting('printIssn'));
        $templateMgr->assign('onlineIssn',           $journal->getSetting('onlineIssn'));
        $templateMgr->assign('leadEditor',           $this->_getLeadEditor($section, $journal));
        $templateMgr->assign('copyrightNotice',      $journal->getLocalizedSetting('copyrightNotice'));
        $templateMgr->assign('submissionChecklist',  $journal->getLocalizedSetting('submissionChecklist'));
        $templateMgr->assign('authorGuidelines',     $journal->getLocalizedSetting('authorGuidelines'));
        $templateMgr->assign('privacyStatement',     $journal->getLocalizedSetting('privacyStatement'));
        $templateMgr->assign('indexUrl',             $request->url(null, 'section', $section->getSectionUrlTitle()));
        $templateMgr->assign('articlesUrl',          $request->url(null, 'section', $section->getSectionUrlTitle(), ['articles']));

        $templateMgr->display('section/about.tpl');
    }

    /**
     * Halaman semua artikel section dengan paginasi OJS.
     * [WIZDAM] Menggunakan VirtualArrayIterator dan getRangeInfo() bawaan OJS.
     * @param object $section
     * @param object $journal
     * @param PKPRequest $request
     */
    private function _showSectionArticles($section, $journal, $request): void {
        $this->setupSectionTemplate($section);

        $rangeInfo    = $this->getRangeInfo('sectionArticles');
        $allFiltered  = $this->_getSectionArticles($section, $journal);
        $totalCount   = count($allFiltered);
        $itemsPerPage = ($rangeInfo && $rangeInfo->isValid())
            ? $rangeInfo->getCount()
            : ((int) Config::getVar('interface', 'items_per_page') ?: 10);
        $currentPage  = ($rangeInfo && $rangeInfo->isValid()) ? $rangeInfo->getPage() : 1;
        $offset       = ($currentPage - 1) * $itemsPerPage;
        $pageArticles = array_slice($allFiltered, $offset, $itemsPerPage);

        import('lib.pkp.classes.core.VirtualArrayIterator');
        $articlesIterator = new VirtualArrayIterator(
            $pageArticles,
            $totalCount,
            $currentPage,
            $itemsPerPage
        );

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('section',          $section);
        $templateMgr->assign('journalTitle',      $journal->getLocalizedTitle());
        $templateMgr->assign('journalInitials',   $journal->getLocalizedSetting('initials'));
        $templateMgr->assign('printIssn',         $journal->getSetting('printIssn'));
        $templateMgr->assign('onlineIssn',        $journal->getSetting('onlineIssn'));
        $templateMgr->assign('articles',          $articlesIterator);
        $templateMgr->assign('totalCount',        $totalCount);
        $templateMgr->assign('rangeInfoName',     'sectionArticles');
        $templateMgr->assign('indexUrl',          $request->url(null, 'section', $section->getSectionUrlTitle()));
        $templateMgr->assign('aboutUrl',          $request->url(null, 'section', $section->getSectionUrlTitle(), ['about']));

        $templateMgr->display('section/articles.tpl');
    }

    // =========================================================================
    // PRIVATE — DATA RESOLVERS
    // =========================================================================

    /**
     * Resolve Section dari op yang sudah dinormalisasi PKPPageRouter.
     * Router sudah mengkonversi kebab-case → camelCase sebelum sampai ke sini.
     * getSectionUrlTitle() menghasilkan format yang sama — satu sumber kebenaran.
     * @param string $op
     * @param Journal $journal
     * @return Section|null
     */
    private function _resolveSection(string $op, $journal): ?object {
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections   = $sectionDao->getJournalSections($journal->getId());

        while ($section = $sections->next()) {
            if ($section->getSectionUrlTitle() === $op) return $section;
        }

        return $sectionDao->getSectionByAbbrev($op, $journal->getId()) ?: null;
    }

    /**
     * Ambil data section editors.
     * [WIZDAM] Mengirim User object penuh — tidak dibungkus array terbatas.
     * Template memanggil langsung: $editor.user->getSintaId(), dll.
     * @param object $section
     * @param object $journal
     * @return array of ['user' => User, 'canReview' => bool, 'canEdit' => bool]
     */
    private function _getSectionEditors($section, $journal): array {
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $userDao           = DAORegistry::getDAO('UserDAO');
        $rawEditors        = $sectionEditorsDao->getEditorsBySectionId(
            $journal->getId(),
            $section->getId()
        );

        $editors = [];
        foreach ($rawEditors as $entry) {
            if (!is_array($entry) || !isset($entry['user'])) continue;
            $user = $userDao->getById((int) $entry['user']->getId());
            if (!$user) continue;
            $editors[] = [
                'user'      => $user,
                'canReview' => $entry['canReview'],
                'canEdit'   => $entry['canEdit'],
            ];
        }

        return $editors;
    }

    /**
     * Ambil editor pertama sebagai Lead Editor.
     * [WIZDAM] Mengirim User object langsung — tidak dibungkus array.
     * @param object $section
     * @param object $journal
     * @return User|null
     */
    private function _getLeadEditor($section, $journal): ?object {
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $userDao           = DAORegistry::getDAO('UserDAO');
        $rawEditors        = $sectionEditorsDao->getEditorsBySectionId(
            $journal->getId(),
            $section->getId()
        );

        foreach ($rawEditors as $entry) {
            if (!is_array($entry) || !isset($entry['user'])) continue;
            $user = $userDao->getById((int) $entry['user']->getId());
            if ($user) return $user;
        }

        return null;
    }

    /**
     * Ambil artikel section dengan limit opsional.
     * [WIZDAM] Mengirim PublishedArticle object langsung.
     * @param object $section
     * @param object $journal
     * @param int $limit 0 untuk semua artikel, >0 untuk limit jumlah artikel
     * @return array of PublishedArticle
     */
    private function _getSectionArticles($section, $journal, int $limit = 0): array {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $all = $publishedArticleDao->getPublishedArticlesByJournalId(
            $journal->getId(),
            null,
            true // terbaru dulu
        );

        $articles = [];
        while ($article = $all->next()) {
            if ((int) $article->getSectionId() === (int) $section->getId()) {
                $articles[] = $article;
                if ($limit > 0 && count($articles) >= $limit) break;
            }
        }

        return $articles;
    }

    /**
     * Hitung total artikel di section — hanya ID, lebih ringan.
     * @param object $section
     * @return int
     */
    private function _getSectionArticleCount($section): int {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        return count(
            $publishedArticleDao->getPublishedArticleIdsBySection($section->getId())
        );
    }

    // =========================================================================
    // PRIVATE — TEMPLATE SETUP
    // =========================================================================

    /**
     * Setup breadcrumbs dan page title untuk semua halaman section.
     * @param object $section
     */
    private function setupSectionTemplate($section): void {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();

        $pageHierarchy = [];
        if ($section) {
            $pageHierarchy[] = [
                Request::url(null, 'section', $section->getSectionUrlTitle()),
                $section->getLocalizedTitle(),
                true
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageHierarchy);
        $templateMgr->assign('pageTitle',     $section ? $section->getLocalizedTitle() : 'common.section');
    }
}
?>