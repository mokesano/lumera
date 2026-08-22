<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/pages/BooksForReviewEditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooksForReviewEditorHandler
 * @ingroup plugins_generic_booksForReview
 *
 * @brief Handle requests for editor books for review functions.
 */

import('classes.handler.Handler');

if (!defined('BOOKS_FOR_REVIEW_PLUGIN_NAME')) define('BOOKS_FOR_REVIEW_PLUGIN_NAME', 'booksForReview');
if (!defined('BFR_BOOK_SEARCH_TITLE')) define('BFR_BOOK_SEARCH_TITLE', 1);
if (!defined('BFR_BOOK_SEARCH_AUTHOR')) define('BFR_BOOK_SEARCH_AUTHOR', 2);
if (!defined('BFR_BOOK_SEARCH_ISBN')) define('BFR_BOOK_SEARCH_ISBN', 3);
if (!defined('BFR_STATUS_AVAILABLE')) define('BFR_STATUS_AVAILABLE', 1);
if (!defined('BFR_STATUS_REQUESTED')) define('BFR_STATUS_REQUESTED', 2);
if (!defined('BFR_STATUS_ASSIGNED')) define('BFR_STATUS_ASSIGNED', 3);
if (!defined('BFR_STATUS_MAILED')) define('BFR_STATUS_MAILED', 4);
if (!defined('BFR_STATUS_SUBMITTED')) define('BFR_STATUS_SUBMITTED', 5);
if (!defined('FILTER_EDITOR_ALL')) define('FILTER_EDITOR_ALL', 0);
if (!defined('FILTER_EDITOR_ME')) define('FILTER_EDITOR_ME', 1);
if (!defined('BFR_FIELD_TITLE')) define('BFR_FIELD_TITLE', 1);
if (!defined('BFR_FIELD_PUBLISHER')) define('BFR_FIELD_PUBLISHER', 2);
if (!defined('BFR_FIELD_YEAR')) define('BFR_FIELD_YEAR', 3);
if (!defined('BFR_FIELD_ISBN')) define('BFR_FIELD_ISBN', 4);
if (!defined('BFR_FIELD_DESCRIPTION')) define('BFR_FIELD_DESCRIPTION', 5);

class BooksForReviewEditorHandler extends Handler {

    /**
     * Display books for review listing pages.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function booksForReview($args = [], $request) {
        $this->setupTemplate();

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $mode = $bfrPlugin->getSetting($journalId, 'mode');
        $bfrPlugin->import('classes.BookForReview');
        
        $searchField = null;
        $searchMatch = null;
        $search = trim((string) $request->getUserVar('search'));

        if (!empty($search)) {
            $validSearchFields = [
                BFR_BOOK_SEARCH_TITLE,
                BFR_BOOK_SEARCH_AUTHOR,
                BFR_BOOK_SEARCH_ISBN
            ];
            $searchField = $request->getUserVar('searchField');
            if (!in_array($searchField, $validSearchFields, true)) {
                $searchField = null;
            }

            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim((string) $request->getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches, true)) {
                $searchMatch = 'contains';
            }
        }

        $path = $args[0] ?? null;

        switch ($path) {
            case 'available':
                $status = BFR_STATUS_AVAILABLE;
                $template = 'booksForReviewAvailable.tpl';
                break;
            case 'requested':
                $status = BFR_STATUS_REQUESTED;
                $template = 'booksForReviewRequested.tpl';
                break;
            case 'assigned':
                $status = BFR_STATUS_ASSIGNED;
                $template = 'booksForReviewAssigned.tpl';
                break;
            case 'mailed':
                $status = BFR_STATUS_MAILED;
                $template = 'booksForReviewMailed.tpl';
                break;
            case 'submitted':
                $status = BFR_STATUS_SUBMITTED;
                $template = 'booksForReviewSubmitted.tpl';
                break;
            default:
                $path = '';
                $status = null;
                $template = 'booksForReviewAll.tpl';
        }

        import('pages.editor.EditorHandler');
        $user = $request->getUser();
        $filterEditorOptions = [
            FILTER_EDITOR_ALL => __('editor.allEditors'),
            FILTER_EDITOR_ME => __('editor.me')
        ];

        $filterEditor = (int) $request->getUserVar('filterEditor');

        if (array_key_exists($filterEditor, $filterEditorOptions)) {
            $user->updateSetting('filterEditor', $filterEditor, 'int', $journalId);
        } else {
            $filterEditor = $user->getSetting('filterEditor', $journalId);
            if ($filterEditor === null) {
                $filterEditor = FILTER_EDITOR_ALL;
                $user->updateSetting('filterEditor', $filterEditor, 'int', $journalId);
            }
        }

        $editorId = ($filterEditor === FILTER_EDITOR_ME) ? $user->getId() : null;

        $rangeInfo = Handler::getRangeInfo('booksForReview');
        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        $booksForReview = $bfrDao->getBooksForReviewByJournalId($journalId, $searchField, $search, $searchMatch, $status, null, $editorId, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('mode', $mode);
        $templateMgr->assign('booksForReview', $booksForReview);
        $templateMgr->assign('filterEditor', $filterEditor);
        $templateMgr->assign('returnPage', $path);

        $duplicateParameters = ['searchField', 'searchMatch', 'search'];
        foreach ($duplicateParameters as $param) {
            $templateMgr->assign($param, htmlspecialchars(trim((string) $request->getUserVar($param)), ENT_QUOTES, 'UTF-8'));
        }

        $fieldOptions = [
            BFR_FIELD_TITLE => 'plugins.generic.booksForReview.field.title',
            BFR_FIELD_PUBLISHER => 'plugins.generic.booksForReview.field.publisher',
            BFR_FIELD_YEAR => 'plugins.generic.booksForReview.field.year',
            BFR_FIELD_ISBN => 'plugins.generic.booksForReview.field.isbn',
            BFR_FIELD_DESCRIPTION => 'plugins.generic.booksForReview.field.description'
        ];
        
        $templateMgr->assign('fieldOptions', $fieldOptions);
        $templateMgr->assign('editorOptions', $filterEditorOptions);
        $templateMgr->assign('counts', $bfrDao->getStatusCounts($journalId));

        $templateMgr->display($bfrPlugin->getTemplatePath() . 'editor/' . $template);
    }

    /**
     * Create book for review.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function createBookForReview($args = [], $request) {
        $this->editBookForReview($args, $request);
    }

    /**
     * Edit book for review.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function editBookForReview($args = [], $request) {
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $mode = $bfrPlugin->getSetting($journalId, 'mode');
        $bookId = isset($args[0]) ? (int) $args[0] : null;
        
        $returnPage = trim((string) $request->getUserVar('returnPage'));

        if (!empty($returnPage)) { 
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        if (($bookId !== null && $bfrDao->getBookForReviewJournalId($bookId) === $journalId) || $bookId === null) {
            $bfrPlugin->import('classes.form.BookForReviewForm');

            /** @var JournalSettingsDAO $journalSettingsDao */
            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            $journalSettings = $journalSettingsDao->getJournalSettings($journalId);

            /** @var CountryDAO $countryDao */
            $countryDao = DAORegistry::getDAO('CountryDAO');
            $countries = $countryDao->getCountries();

            $bfrForm = new BookForReviewForm(BOOKS_FOR_REVIEW_PLUGIN_NAME, $bookId);
            $bfrForm->initData();
            
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('mode', $mode);
            $templateMgr->assign('journalSettings', $journalSettings);
            $templateMgr->assign('returnPage', $returnPage);
            $templateMgr->assign('countries', $countries);
            $bfrForm->display();
        } else {
            $request->redirect(null, 'editor', 'booksForReview', $returnPage);
        }
    }

    /**
     * Update book for review.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function updateBookForReview($args = [], $request) {
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $mode = $bfrPlugin->getSetting($journalId, 'mode');
        $bfrPlugin->import('classes.form.BookForReviewForm');
        
        $bookId = (int) $request->getUserVar('bookId');
        $returnPage = trim((string) $request->getUserVar('returnPage'));

        if (!empty($returnPage)) { 
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        if (($bookId !== null && $bfrDao->getBookForReviewJournalId($bookId) === $journalId) || $bookId === null) {

            $bfrForm = new BookForReviewForm(BOOKS_FOR_REVIEW_PLUGIN_NAME, $bookId);
            $bfrForm->readInputData();
            $editData = false;

            if ((bool) $request->getUserVar('addAuthor')) {
                $editData = true;
                $authors = $bfrForm->getData('authors');
                $authors[] = [];
                $bfrForm->setData('authors', $authors);
            } elseif (($delAuthor = (array) $request->getUserVar('delAuthor')) && count($delAuthor) === 1) {
                $editData = true;
                list($delAuthorIndex) = array_keys($delAuthor);
                $authors = $bfrForm->getData('authors');
                array_splice($authors, (int) $delAuthorIndex, 1);
                $bfrForm->setData('authors', $authors);
            } elseif ((bool) $request->getUserVar('moveAuthor')) {
                $editData = true;
                $moveAuthorDir = trim((string) $request->getUserVar('moveAuthorDir'));
                $moveAuthorDir = $moveAuthorDir === 'u' ? 'u' : 'd'; 
                $moveAuthorIndex = (int) $request->getUserVar('moveAuthorIndex');
                $authors = $bfrForm->getData('authors');

                $targetIndex = $moveAuthorIndex + ($moveAuthorDir === 'u' ? -1 : 1);
                if (isset($authors[$moveAuthorIndex]) && isset($authors[$targetIndex])) {
                    $tmpAuthor = $authors[$moveAuthorIndex];
                    $authors[$moveAuthorIndex] = $authors[$targetIndex];
                    $authors[$targetIndex] = $tmpAuthor;
                }
                $bfrForm->setData('authors', $authors);
            }

            if (!$editData && $bfrForm->validate()) {
                $bfrForm->execute();

                $notificationType = ($bookId === null) ? NOTIFICATION_TYPE_BOOK_CREATED : NOTIFICATION_TYPE_BOOK_UPDATED;
                
                $user = $request->getUser();
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), $notificationType);
                
                $redirectParams = !empty($returnPage) ? ['returnPage' => $returnPage] : null;

                if ((bool) $request->getUserVar('createAnother')) {
                    $request->redirect(null, 'editor', 'createBookForReview', null, $redirectParams);
                } else {
                    $request->redirect(null, 'editor', 'booksForReview', $returnPage);
                }
            } else {
                /** @var JournalSettingsDAO $journalSettingsDao */
                $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                $journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());
                
                /** @var CountryDAO $countryDao */
                $countryDao = DAORegistry::getDAO('CountryDAO');
                $countries = $countryDao->getCountries();

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('mode', $mode);
                $templateMgr->assign('journalSettings', $journalSettings);
                $templateMgr->assign('returnPage', htmlspecialchars($returnPage, ENT_QUOTES, 'UTF-8'));
                $templateMgr->assign('countries', $countries);
                $bfrForm->display();
            }
        } else {
            $request->redirect(null, 'editor');
        }
    }

    /**
     * Delete book for review.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function deleteBookForReview($args = [], $request) {
        $this->setupTemplate();

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $returnPage = trim((string) $request->getUserVar('returnPage'));
        if (!empty($returnPage)) { 
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        if (!empty($args)) {
            $bookId = (int) $args[0];
            /** @var BookForReviewDAO $bfrDao */
            $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

            if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
                $bfrDao->deleteBookForReviewById($bookId);
                $user = $request->getUser();
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_DELETED);
            }
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Update book for review settings.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function booksForReviewSettings($args = [], $request) {
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bfrPlugin->import('classes.form.BooksForReviewSettingsForm');
        $templateMgr = TemplateManager::getManager();

        $form = new BooksForReviewSettingsForm($bfrPlugin, $journalId);

        if (Config::getVar('general', 'scheduled_tasks')) {
            $templateMgr->assign('scheduledTasksEnabled', true);
        }

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        $templateMgr->assign('counts', $bfrDao->getStatusCounts($journalId));
        
        if ((bool) $request->getUserVar('save')) {
            $form->readInputData();
            if ($form->validate()) {
                $form->execute();
                $user = $request->getUser();
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_SETTINGS_SAVED);

                $request->redirect(null, 'editor', 'booksForReviewSettings');
            } else {
                $form->display();
            }
        } else {
            $form->initData();
            $form->display();
        }
    }

    /**
     * Display a list of authors from which to choose a book reviewer.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function selectBookForReviewAuthor($args = [], $request) {
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bookId = (int) ($args[0] ?? 0);
        
        $returnPage = trim((string) $request->getUserVar('returnPage'));
        if (!empty($returnPage)) { 
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        if ($bfrDao->getBookForReviewJournalId($bookId) !== $journalId) {
            $request->redirect(null, 'editor', 'booksForReview', $returnPage);
        }

        $templateMgr = TemplateManager::getManager();
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');

        $searchType = null;
        $searchMatch = null;
        $search = $searchQuery = trim((string) $request->getUserVar('search'));

        $searchInitial = trim((string) $request->getUserVar('searchInitial'));
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) {
            $searchInitial = '';
        }

        if (!empty($search)) {
            $validSearchFields = [
                BFR_BOOK_SEARCH_TITLE,
                BFR_BOOK_SEARCH_AUTHOR,
                BFR_BOOK_SEARCH_ISBN,
                USER_FIELD_FIRSTNAME,
                USER_FIELD_LASTNAME,
                USER_FIELD_USERNAME,
                USER_FIELD_EMAIL
            ];
            $searchType = $request->getUserVar('searchField');
            if (!in_array($searchType, $validSearchFields, true)) {
                $searchType = null;
            }

            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim((string) $request->getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches, true)) {
                $searchMatch = 'contains';
            }
        } elseif (!empty($searchInitial)) { 
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchType = USER_FIELD_INITIAL;
            $search = $searchInitial;
        }

        $rangeInfo = Handler::getRangeInfo('users');
        $users = $roleDao->getUsersByRoleId(ROLE_ID_AUTHOR, $journalId, $searchType, $search, $searchMatch, $rangeInfo);

        $templateMgr->assign('searchField', $searchType);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $searchQuery);
        $templateMgr->assign('searchInitial', htmlspecialchars($searchInitial, ENT_QUOTES, 'UTF-8'));

        import('classes.security.Validation');
        $templateMgr->assign('isJournalManager', Validation::isJournalManager());

        $templateMgr->assign('fieldOptions', [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_EMAIL => 'user.email'
        ]);

        $templateMgr->assign('users', $users);
        $templateMgr->assign('helpTopicId', 'journal.roles.author');
        $templateMgr->assign('bookId', $bookId);
        $templateMgr->assign('returnPage', $returnPage);
        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));

        $templateMgr->display($bfrPlugin->getTemplatePath() . 'editor/authors.tpl');
    }

    /**
     * Display a list of submissions from which to choose a book review submission.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function selectBookForReviewSubmission($args = [], $request) {
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bookId = (int) ($args[0] ?? 0);
        
        $returnPage = trim((string) $request->getUserVar('returnPage'));
        if ($returnPage !== null && $returnPage !== '') {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        if ($bfrDao->getBookForReviewJournalId($bookId) !== $journalId) {
            $request->redirect(null, 'editor', 'booksForReview', $returnPage);
        }

        /** @var EditorSubmissionDAO $editorSubmissionDao */
        $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
        $templateMgr = TemplateManager::getManager();

        $searchField = null;
        $searchMatch = null;
        $search = $searchQuery = trim((string) $request->getUserVar('search'));
        
        if (!empty($search)) {
            $searchField = $request->getUserVar('searchField');
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim((string) $request->getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches, true)) {
                $searchMatch = 'contains'; 
            }
        }

        $user = $request->getUser();
        $editorId = $user->getId();
        $rangeInfo = Handler::getRangeInfo('submissions');

        import('lib.pkp.classes.db.DAO');
        $submissions = $editorSubmissionDao->getEditorSubmissions(
            $journalId, 0, $editorId, $searchField, $searchMatch, $search, 
            null, null, null, $rangeInfo, 'id', SORT_DIRECTION_DESC
        );

        $templateMgr->assign('searchField', $searchField);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $searchQuery);

        $templateMgr->assign('fieldOptions', [
            SUBMISSION_FIELD_TITLE => 'article.title',
            SUBMISSION_FIELD_AUTHOR => 'user.role.author'
        ]);

        $templateMgr->assign('submissions', $submissions);
        $templateMgr->assign('helpTopicId', 'journal.roles.editor');
        $templateMgr->assign('bookId', $bookId);
        $templateMgr->assign('returnPage', $returnPage);

        $templateMgr->display($bfrPlugin->getTemplatePath() . 'editor/submissions.tpl');
    }

    /**
     * Assign a book for review submission.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function assignBookForReviewSubmission($args = [], $request) {
        $this->setupTemplate();

        if (empty($args)) {
            $request->redirect(null, 'editor');
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        $returnPage = $request->getUserVar('returnPage');
        if ($returnPage !== null) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $bookId = (int) $args[0];

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
            $book = $bfrDao->getBookForReview($bookId);
            $articleId = (int) $request->getUserVar('articleId');

            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            if ($articleDao->getArticleJournalId($articleId) === $journalId) {
                $book->setArticleId($articleId);
                $book->setStatus(BFR_STATUS_SUBMITTED);
                $bfrDao->updateObject($book);
                
                $user = $request->getUser();
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_SUBMISSION_ASSIGNED);
            }
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Assign a book for review author.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function assignBookForReviewAuthor($args = [], $request) {
        $this->setupTemplate();

        if (empty($args)) {
            $request->redirect(null, 'editor');
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        $returnPage = $request->getUserVar('returnPage');
        if ($returnPage !== null) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $bookId = (int) $args[0];
        
        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
            $book = $bfrDao->getBookForReview($bookId);
            $status = $book->getStatus();

            if ($status === BFR_STATUS_AVAILABLE) {
                $userId = (int) $request->getUserVar('userId');
                /** @var UserDAO $userDao */
                $userDao = DAORegistry::getDAO('UserDAO');
                $user = $userDao->getUser($userId);
                $userName = $user->getFullName();
                $userEmail = $user->getEmail();
                $userMailingAddress = $user->getMailingAddress();
                $userCountryCode = $user->getCountry();
            } else {
                $userId = $book->getUserId();
                $userName = $book->getUserFullName();
                $userEmail = $book->getUserEmail();
                $userMailingAddress = $book->getUserMailingAddress();
                $userCountryCode = $book->getUserCountry();
            }

            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            if ($roleDao->userHasRole($journalId, $userId, ROLE_ID_AUTHOR)) {
                import('classes.mail.MailTemplate');
                $email = new MailTemplate('BFR_BOOK_ASSIGNED');
                $send = (bool) $request->getUserVar('send');

                if ($send && !$email->hasErrors()) {
                    $dueWeeks = $bfrPlugin->getSetting($journalId, 'dueWeeks');
                    $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);
                    $dueDate = date('Y-m-d H:i:s', $dueDateTimestamp);

                    $book->setUserId($userId);
                    $book->setStatus(BFR_STATUS_ASSIGNED);
                    $book->setDateAssigned(Core::getCurrentDate());
                    $book->setDateDue($dueDate);
                    $bfrDao->updateObject($book);

                    $email->send();
                    $user = $request->getUser();

                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_AUTHOR_ASSIGNED);

                    $request->redirect(null, 'editor', 'booksForReview', $returnPage);
                } else {
                    if (!(bool) $request->getUserVar('continued')) {
                        $dueWeeks = $bfrPlugin->getSetting($journalId, 'dueWeeks');
                        $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);

                        if (empty($userMailingAddress)) {
                            $userMailingAddress = __('plugins.generic.booksForReview.editor.noMailingAddress');
                        } else {
                            /** @var CountryDAO $countryDao */
                            $countryDao = DAORegistry::getDAO('CountryDAO');
                            $countries = $countryDao->getCountries();
                            $userCountry = $countries[$userCountryCode] ?? '';
                            $userMailingAddress .= "\n" . $userCountry;
                        }

                        $paramArray = [
                            'authorName' => strip_tags($userName),
                            'authorMailingAddress' => PKPString::html2text($userMailingAddress),
                            'bookForReviewTitle' => '"' . strip_tags($book->getLocalizedTitle()) . '"',
                            'bookForReviewDueDate' => date('l, F j, Y', $dueDateTimestamp),
                            'userProfileUrl' => $request->url(null, 'user', 'profile'),
                            'submissionUrl' => $request->url(null, 'submission', 'submit'),
                            'editorialContactSignature' => PKPString::html2text($book->getEditorContactSignature())
                        ];

                        $email->addRecipient($userEmail, $userName);
                        $email->setFrom($book->getEditorEmail(), $book->getEditorFullName());
                        $email->assignParams($paramArray);
                    }
                    $returnUrl = $request->url(null, 'editor', 'assignBookForReviewAuthor', $bookId, ['returnPage' => $returnPage, 'userId' => $userId]);
                    $email->displayEditForm($returnUrl);
                }
            }
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Deny a book for review request.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function denyBookForReviewAuthor($args = [], $request) {
        $this->setupTemplate();

        if (empty($args)) {
            $request->redirect(null, 'editor');
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        $returnPage = $request->getUserVar('returnPage');
        if ($returnPage !== null) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $bookId = (int) $args[0];
        
        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('BFR_BOOK_DENIED');
            $send = (bool) $request->getUserVar('send');

            if ($send && !$email->hasErrors()) {
                $book = $bfrDao->getBookForReview($bookId);
                $book->setStatus(BFR_STATUS_AVAILABLE);
                $book->setUserId(null);
                $book->setDateRequested(null);
                $bfrDao->updateObject($book);

                $email->send();
                $user = $request->getUser();

                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_AUTHOR_DENIED);

                $request->redirect(null, 'editor', 'booksForReview', $returnPage);
            } else {
                if (!(bool) $request->getUserVar('continued')) {
                    $book = $bfrDao->getBookForReview($bookId);
                    $userFullName = $book->getUserFullName();
                    $userEmail = $book->getUserEmail();

                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'bookForReviewTitle' => '"' . strip_tags($book->getLocalizedTitle()) . '"',
                        'submissionUrl' => $request->url(null, 'submission', 'submit'),
                        'editorialContactSignature' => PKPString::html2text($book->getEditorContactSignature())
                    ];

                    $email->addRecipient($userEmail, $userFullName);
                    $email->setFrom($book->getEditorEmail(), $book->getEditorFullName());
                    $email->assignParams($paramArray);
                }
                $returnUrl = $request->url(null, 'editor', 'denyBookForReviewAuthor', $bookId, ['returnPage' => $returnPage]);
                $email->displayEditForm($returnUrl);
            }
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Mark a book for review as mailed.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function notifyBookForReviewMailed($args = [], $request) {
        $this->setupTemplate();

        if (empty($args)) {
            $request->redirect(null, 'editor');
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        $returnPage = $request->getUserVar('returnPage');
        if ($returnPage !== null) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $bookId = (int) $args[0];
        
        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('BFR_BOOK_MAILED');
            $send = (bool) $request->getUserVar('send');

            if ($send && !$email->hasErrors()) {
                $book = $bfrDao->getBookForReview($bookId);
                $book->setStatus(BFR_STATUS_MAILED);
                $book->setDateMailed(date('Y-m-d H:i:s', time()));
                $bfrDao->updateObject($book);

                $email->send();
                $user = $request->getUser();

                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_MAILED);

                $request->redirect(null, 'editor', 'booksForReview', $returnPage);
            } else {
                if (!(bool) $request->getUserVar('continued')) {
                    $book = $bfrDao->getBookForReview($bookId);
                    $userFullName = $book->getUserFullName();
                    $userEmail = $book->getUserEmail();
                    $userMailingAddress = $book->getUserMailingAddress();
                    $userCountryCode = $book->getUserCountry();

                    if (empty($userMailingAddress)) {
                        $userMailingAddress = __('plugins.generic.booksForReview.editor.noMailingAddress');
                    } else {
                        /** @var CountryDAO $countryDao */
                        $countryDao = DAORegistry::getDAO('CountryDAO');
                        $countries = $countryDao->getCountries();
                        $userCountry = $countries[$userCountryCode] ?? '';
                        $userMailingAddress .= "\n" . $userCountry;
                    }

                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'authorMailingAddress' => PKPString::html2text($userMailingAddress),
                        'bookForReviewTitle' => '"' . strip_tags($book->getLocalizedTitle()) . '"',
                        'submissionUrl' => $request->url(null, 'submission', 'submit'),
                        'editorialContactSignature' => PKPString::html2text($book->getEditorContactSignature())
                    ];

                    $email->addRecipient($userEmail, $userFullName);
                    $email->setFrom($book->getEditorEmail(), $book->getEditorFullName());
                    $email->assignParams($paramArray);
                }
                $returnUrl = $request->url(null, 'editor', 'notifyBookForReviewMailed', $bookId, ['returnPage' => $returnPage]);
                $email->displayEditForm($returnUrl);
            }
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Remove book reviewer and reset book for review.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function removeBookForReviewAuthor($args = [], $request) {
        $this->setupTemplate();

        if (empty($args)) {
            $request->redirect(null, 'editor');
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        $returnPage = $request->getUserVar('returnPage');
        if ($returnPage !== null) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $bookId = (int) $args[0];
        
        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('BFR_REVIEWER_REMOVED');
            $send = (bool) $request->getUserVar('send');

            if ($send && !$email->hasErrors()) {
                $book = $bfrDao->getBookForReview($bookId);
                $book->setStatus(BFR_STATUS_AVAILABLE);
                $book->setUserId(null);
                $book->setDateRequested(null);
                $book->setDateAssigned(null);
                $book->setDateDue(null);
                $book->setDateMailed(null);
                $book->setDateSubmitted(null);
                $book->setArticleId(null);
                $bfrDao->updateObject($book);

                $email->send();
                $user = $request->getUser();

                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_BOOK_AUTHOR_REMOVED);

                $request->redirect(null, 'editor', 'booksForReview', $returnPage);
            } else {
                if (!(bool) $request->getUserVar('continued')) {
                    $book = $bfrDao->getBookForReview($bookId);
                    $userFullName = $book->getUserFullName();
                    $userEmail = $book->getUserEmail();

                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'bookForReviewTitle' => '"' . strip_tags($book->getLocalizedTitle()) . '"',
                        'editorialContactSignature' => PKPString::html2text($book->getEditorContactSignature())
                    ];

                    $email->addRecipient($userEmail, $userFullName);
                    $email->setFrom($book->getEditorEmail(), $book->getEditorFullName());
                    $email->assignParams($paramArray);
                }
                $returnUrl = $request->url(null, 'editor', 'removeBookForReviewAuthor', $bookId, ['returnPage' => $returnPage]);
                $email->displayEditForm($returnUrl);
            }
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Remove book for review cover page image.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function removeBookForReviewCoverPage($args = [], $request) {
        $this->setupTemplate();

        if (empty($args) || count($args) < 2) {
            $request->redirect(null, 'editor');
        }

        $bookId = (int) $args[0];
        $formLocale = $args[1];

        if (!AppLocale::isLocaleValid($formLocale)) {
            $request->redirect(null, 'editor');
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        $returnPage = $request->getUserVar('returnPage');
        if ($returnPage !== null) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = null;
            }
        }

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        /** @var BookForReviewDAO $bfrDao */
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        if ($bfrDao->getBookForReviewJournalId($bookId) === $journalId) {
            $bfrDao->removeCoverPage($bookId, $formLocale);
            $request->redirect(null, 'editor', 'editBookForReview', $bookId, ['returnPage' => $returnPage]);
        }
        $request->redirect(null, 'editor', 'booksForReview', $returnPage);
    }

    /**
     * Return valid landing/return pages.
     * @return array
     */
    public function getValidReturnPages() {
        return ['available', 'requested', 'assigned', 'mailed', 'submitted'];
    }

    /**
     * Ensure that we have a journal, plugin is enabled, and user is editor.
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        $journal = $request->getJournal();
        if ($journal === null) {
            return false;
        }

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        if ($bfrPlugin === null || !$bfrPlugin->getEnabled()) {
            return false;
        }

        if (!Validation::isEditor($journal->getId())) {
            Validation::redirectLogin();
        }

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     * @return void
     */
    public function setupTemplate($subclass = false) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager();
        
        $pageCrumbs = [
            [$request->url(null, 'user'), 'navigation.user'],
            [$request->url(null, 'editor'), 'user.role.editor']
        ];

        if ($subclass) {
            $returnPage = trim((string) $request->getUserVar('returnPage'));
    
            if (!empty($returnPage)) { 
                $validPages = $this->getValidReturnPages();
                if (!in_array($returnPage, $validPages, true)) {
                    $returnPage = null;
                }
            }

            $pageCrumbs[] = [
                $request->url(null, 'editor', 'booksForReview', $returnPage),
                __('plugins.generic.booksForReview.displayName'),
                true
            ];
        }
        $templateMgr->assign('pageHierarchy', $pageCrumbs);

        /** @var BooksForReviewPlugin $bfrPlugin */
        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        if ($bfrPlugin) {
            $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $bfrPlugin->getStyleSheet());
        }
    }

}
?>