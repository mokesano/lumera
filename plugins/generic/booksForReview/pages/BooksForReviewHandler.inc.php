<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/pages/BooksForReviewHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooksForReviewHandler
 * @ingroup plugins_generic_booksForReview
 *
 * @brief Handle requests for public book for review functions.
 */

import('classes.handler.Handler');

class BooksForReviewHandler extends Handler {

    /**
     * Display books for review public index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index(array $args = [], $request = null) {
        $journal = $request->getJournal();
        if (!$journal) {
            return;
        }
        $journalId = (int) $journal->getId();

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        if (!$bookReviewPlugin || !$bookReviewPlugin->getEnabled()) {
            $request->redirect(null, 'index');
            return;
        }

        $bookReviewPlugin->import('classes.BookForReview');
        
        $search = trim((string) $request->getUserVar('search'));
        $searchField = !empty($search) ? trim((string) $request->getUserVar('searchField')) : null;
        $searchMatch = !empty($search) ? trim((string) $request->getUserVar('searchMatch')) : null;

        $rangeInfo = $this->getRangeInfo('booksForReview');
        
        /** @var BookForReviewDAO $bookReviewDao */
        $bookReviewDao = DAORegistry::getDAO('BookForReviewDAO');
        $booksForReview = $bookReviewDao->getBooksForReviewByJournalId(
            $journalId, $searchField, $search, $searchMatch, BFR_STATUS_AVAILABLE, null, null, $rangeInfo
        );

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('booksForReview', $booksForReview);
        $templateMgr->assign('isAuthor', Validation::isAuthor());

        $duplicateParameters = ['searchField', 'searchMatch', 'search'];
        foreach ($duplicateParameters as $param) {
            $rawInput = trim((string) $request->getUserVar($param));
            $templateMgr->assign($param, htmlspecialchars($rawInput, ENT_QUOTES, 'UTF-8'));
        }

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/';
        
        $templateMgr->assign([
            'coverPagePath' => $coverPagePath,
            'locale' => AppLocale::getLocale(),
            'fieldOptions' => [
                BFR_FIELD_TITLE       => 'plugins.generic.booksForReview.field.title',
                BFR_FIELD_PUBLISHER   => 'plugins.generic.booksForReview.field.publisher',
                BFR_FIELD_YEAR        => 'plugins.generic.booksForReview.field.year',
                BFR_FIELD_ISBN        => 'plugins.generic.booksForReview.field.isbn',
                BFR_FIELD_DESCRIPTION => 'plugins.generic.booksForReview.field.description'
            ],
            'additionalInformation' => $bookReviewPlugin->getSetting($journalId, 'additionalInformation')
        ]);

        $templateMgr->display($bookReviewPlugin->getTemplatePath() . 'booksForReview.tpl');
    }

    /**
     * Public view book for review details.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewBookForReview(array $args, PKPRequest $request): void {
        $journal = $request->getJournal();
        if (!$journal) {
            return;
        }
        $journalId = (int) $journal->getId();

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bookReviewPlugin->import('classes.BookForReview');

        $bookId = isset($args[0]) ? (int) $args[0] : null;

        if ($bookId) {
            /** @var BookForReviewDAO $bookReviewDao */
            $bookReviewDao = DAORegistry::getDAO('BookForReviewDAO');
            
            if ($bookReviewDao->getBookForReviewJournalId($bookId) === $journalId) {
                $book = $bookReviewDao->getBookForReview($bookId);

                if ($book && $book->getStatus() === BFR_STATUS_AVAILABLE) {
                    import('classes.file.PublicFileManager');
                    $publicFileManager = new PublicFileManager();
                    $coverPagePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/';

                    $templateMgr = TemplateManager::getManager($request);
                    $templateMgr->assign([
                        'coverPagePath' => $coverPagePath,
                        'locale' => AppLocale::getLocale(),
                        'bookForReview' => $book,
                        'isAuthor' => Validation::isAuthor()
                    ]);
                    $templateMgr->display($bookReviewPlugin->getTemplatePath() . 'bookForReview.tpl');
                    return;
                }
            }
        }
        
        $request->redirect(null, 'booksForReview');
    }

    /**
     * Ensure that we have a selected journal and the plugin is enabled.
     * @see PKPHandler::authorize()
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, $args, $roleAssignments) {
        $journal = $request->getJournal();
        if (!$journal) {
            return false;
        }

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);

        if (!$bookReviewPlugin || !$bookReviewPlugin->getEnabled()) {
            return false;
        }

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param PKPRequest $request
     * @param bool $subclass
     */
    public function setupTemplate($request = null, $subclass = false) {
        $templateMgr = TemplateManager::getManager($request);

        if ($subclass) {
            $templateMgr->append('pageHierarchy', [
                $request->url(null, 'booksForReview'), 
                AppLocale::translate('plugins.generic.booksForReview.displayName'),
                true
            ]);
        }

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        if ($bookReviewPlugin) {
            $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $bookReviewPlugin->getStyleSheet());
        }
    }

}
?>