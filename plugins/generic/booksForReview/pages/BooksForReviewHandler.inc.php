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
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('classes.handler.Handler');

class BooksForReviewHandler extends Handler {

    /**
     * Display books for review public index page.
     * [MODERNISASI] Hapus referensi & pada $request
     */
    public function index($args = array(), $request) {
        $this->setupTemplate();

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);

        $bfrPlugin->import('classes.BookForReview');
        $searchField = null;
        $searchMatch = null;
        
        // [SECURITY FIX] Amankan 'search' (string teks pencarian) dengan trim()
        $search = trim($request->getUserVar('search'));

        if (!empty($search)) {
            // [SECURITY FIX] Amankan 'searchField' (string key/field) dengan trim()
            $searchField = trim($request->getUserVar('searchField'));
            
            // [SECURITY FIX] Amankan 'searchMatch' (string key/match type) dengan trim()
            $searchMatch = trim($request->getUserVar('searchMatch'));
        }

        $rangeInfo = Handler::getRangeInfo('booksForReview');
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        $booksForReview = $bfrDao->getBooksForReviewByJournalId($journalId, $searchField, $search, $searchMatch, BFR_STATUS_AVAILABLE, null, null, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        // [MODERNISASI] Gunakan assign, bukan assign_by_ref
        $templateMgr->assign('booksForReview', $booksForReview);

        $isAuthor = Validation::isAuthor();
        $templateMgr->assign('isAuthor', $isAuthor);

        // Set search parameters
        $duplicateParameters = array(
            'searchField', 'searchMatch', 'search'
        );
        
        // [SECURITY FIX] Amankan semua parameter duplikat dari XSS dan input kotor
        foreach ($duplicateParameters as $param) {
            $rawInput = trim($request->getUserVar($param));
            $sanitizedValue = htmlspecialchars($rawInput, ENT_QUOTES, 'UTF-8');
            $templateMgr->assign($param, $sanitizedValue);
        }

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPagePath = $request->getBaseUrl() . '/';
        $coverPagePath .= $publicFileManager->getJournalFilesPath($journalId) . '/';
        $templateMgr->assign('coverPagePath', $coverPagePath);
        $templateMgr->assign('locale', AppLocale::getLocale());

        $fieldOptions = Array(
            BFR_FIELD_TITLE => 'plugins.generic.booksForReview.field.title',
            BFR_FIELD_PUBLISHER => 'plugins.generic.booksForReview.field.publisher',
            BFR_FIELD_YEAR => 'plugins.generic.booksForReview.field.year',
            BFR_FIELD_ISBN => 'plugins.generic.booksForReview.field.isbn',
            BFR_FIELD_DESCRIPTION => 'plugins.generic.booksForReview.field.description'
        );
        $templateMgr->assign('fieldOptions', $fieldOptions);
        
        $templateMgr->assign('additionalInformation', $bfrPlugin->getSetting($journalId, 'additionalInformation'));
        $templateMgr->display($bfrPlugin->getTemplatePath() . 'booksForReview.tpl');
    }

    /**
     * Public view book for review details.
     * [MODERNISASI] Hapus referensi & pada $request
     */
    public function viewBookForReview($args = array(), $request) {
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);

        $bookId = !isset($args) || empty($args) ? null : (int) $args[0];

        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        // Ensure book for review is valid and for this journal
        if ($bfrDao->getBookForReviewJournalId($bookId) == $journalId) {
            $book = $bfrDao->getBookForReview($bookId);
            $bfrPlugin->import('classes.BookForReview');

            // Ensure book is still available
            if ($book->getStatus() == BFR_STATUS_AVAILABLE) {
                $isAuthor = Validation::isAuthor();

                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $coverPagePath = $request->getBaseUrl() . '/';
                $coverPagePath .= $publicFileManager->getJournalFilesPath($journalId) . '/';

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('coverPagePath', $coverPagePath);
                $templateMgr->assign('locale', AppLocale::getLocale());
                // [MODERNISASI] Gunakan assign
                $templateMgr->assign('bookForReview', $book);
                $templateMgr->assign('isAuthor', $isAuthor);
                $templateMgr->display($bfrPlugin->getTemplatePath() . 'bookForReview.tpl');
            }
        }
        $request->redirect(null, 'booksForReview');
    }

    /**
     * Ensure that we have a selected journal and the plugin is enabled
     * [MODERNISASI] Perbaiki signature authorize sesuai parent (tanpa &)
     */
    public function authorize($request, &$args, $roleAssignments) {
        $journal = $request->getJournal();
        if (!isset($journal)) return false;

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);

        if (!isset($bfrPlugin)) return false;
 
        if (!$bfrPlugin->getEnabled()) return false;

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param $subclass boolean set to true if caller is below this handler in the hierarchy
     */
    public function setupTemplate($subclass = false) {
        $templateMgr = TemplateManager::getManager();

        if ($subclass) {
            $templateMgr->append(
                'pageHierarchy',
                array(
                    Request::url(null, 'booksForReview'), 
                    AppLocale::Translate('plugins.generic.booksForReview.displayName'),
                    true
                )
            );
        }

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $templateMgr->addStyleSheet(Request::getBaseUrl() . '/' . $bfrPlugin->getStyleSheet());
    }
}

?>