<?php
declare(strict_types=1);

/**
 * @file pages/search/CategorySearchHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategorySearchHandler
 * @ingroup pages_search
 *
 * @brief Handle requests for browsing journal categories/subjects and
 * their contents.
 *
 * [WIZDAM] Dipecah dari SearchHandler.inc.php -- lihat catatan lengkap di
 * AuthorSearchHandler.inc.php. Isi method categories() dan category() di
 * bawah SALINAN PERSIS dari file asli, tidak ada logika yang diubah.
 */

import('pages.search.SearchHandler');

class CategorySearchHandler extends SearchHandler {

    /**
     * Display categories.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function categories($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        $site = $request->getSite();
        $journal = $request->getJournal();

        /** @var CategoryDAO $categoryDao */
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $cache = $categoryDao ? $categoryDao->getCache() : null;

        if ($journal || !$site->getSetting('categoriesEnabled') || !$cache) {
            $request->redirect(null, 'index');
        }

        uasort($cache, function($a, $b) {
            $catA = $a['category'] ?? null; 
            $catB = $b['category'] ?? null; 
            if (!$catA || !$catB) return 0;
            return strcasecmp($catA->getLocalizedName(), $catB->getLocalizedName());
        });

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('categories', $cache);
        $templateMgr->display('search/categories.tpl');
    }

    /**
     * Display category contents.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function category($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $categoryId = (int) array_shift($args);

        $this->validate();
        $this->setupTemplate($request, true, 'categories');

        $site = $request->getSite();
        $journal = $request->getJournal();

        /** @var CategoryDAO $categoryDao */
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $cache = $categoryDao ? $categoryDao->getCache() : null;

        if ($journal || !$site->getSetting('categoriesEnabled') || !$cache || !isset($cache[$categoryId])) {
            $request->redirect(null, 'index');
        }

        $journals = $cache[$categoryId]['journals'] ?? [];
        $category = $cache[$categoryId]['category'] ?? null;

        if (is_array($journals)) {
            uasort($journals, function($a, $b) {
                return strcasecmp($a->getLocalizedTitle(), $b->getLocalizedTitle());
            });
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'journals' => $journals,
            'category' => $category,
            'journalFilesPath' => $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/journals/'
        ]);
        $templateMgr->display('search/category.tpl');
    }

}
?>