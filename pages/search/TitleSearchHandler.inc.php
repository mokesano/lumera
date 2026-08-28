<?php
declare(strict_types=1);

/**
 * @file pages/search/TitleSearchHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class TitleSearchHandler
 * @ingroup pages_search
 *
 * @brief Handle requests for browsing published articles by title.
 *
 * [WIZDAM] Dipecah dari SearchHandler.inc.php -- lihat catatan lengkap di
 * AuthorSearchHandler.inc.php. Isi method titles() di bawah SALINAN PERSIS
 * dari file asli, tidak ada logika yang diubah.
 */

import('pages.search.SearchHandler');

class TitleSearchHandler extends SearchHandler {

    /**
     * Show index of published articles by title.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function titles($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $rangeInfo = $this->getRangeInfo('search');

        $articleIds = $publishedArticleDao 
            ? $publishedArticleDao->getPublishedArticleIdsAlphabetizedByJournal($journal ? $journal->getId() : null) 
            : [];
        $totalResults = count($articleIds);
        
        $offset = $rangeInfo ? $rangeInfo->getCount() * ($rangeInfo->getPage() - 1) : 0;
        $limit = $rangeInfo ? $rangeInfo->getCount() : $totalResults;
        $articleIds = array_slice($articleIds, $offset, $limit);
        
        $resultsArray = ArticleSearch::formatResults($articleIds, $journal);
        
        $sections = [];
        $issues = [];
        $journals = [];
        
        foreach ($resultsArray as $result) {
            if (isset($result['section']) && is_object($result['section'])) {
                $sections[$result['section']->getId()] = $result['section'];
            }
            if (isset($result['issue']) && is_object($result['issue'])) {
                $issues[$result['issue']->getId()] = $result['issue'];
            }
            if (isset($result['journal']) && is_object($result['journal'])) {
                $journals[$result['journal']->getId()] = $result['journal'];
            }
        }
        
        import('lib.pkp.classes.core.VirtualArrayIterator');
        $results = new VirtualArrayIterator(
            $resultsArray,
            $totalResults,
            $rangeInfo ? $rangeInfo->getPage() : 1,
            $rangeInfo ? $rangeInfo->getCount() : $totalResults
        );

        // [WIZDAM] Isi label Tipe Artikel untuk SELURUH hasil di halaman
        // indeks judul ini sekaligus, TEPAT 2 query total -- lihat
        // ArticleType::attachDisplayLabels() untuk penjelasan lengkap.
        // Bentuknya SAMA seperti SearchHandler::search() (baris hasil dari
        // ArticleSearch::formatResults()), aman dipanggil dengan $results
        // apa adanya.
        import('classes.article.ArticleType');
        ArticleType::attachDisplayLabels($results);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'results' => $results,
            'sections' => $sections,
            'issues' => $issues,
            'journals' => $journals
        ]);
        
        $templateMgr->display('search/titleIndex.tpl');
    }

}
?>