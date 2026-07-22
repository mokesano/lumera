<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/pages/BooksForReviewAuthorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooksForReviewAuthorHandler
 * @ingroup plugins_generic_booksForReview
 *
 * @brief Handle requests for author book for review functions.
 */

import('classes.handler.Handler');

class BooksForReviewAuthorHandler extends Handler {

    /**
     * Display books for review author listing page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function booksForReview(array $args, $request): void {
        $journal = $request->getJournal();
        if (!$journal) {
            return;
        }
        $journalId = (int) $journal->getId();

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bookReviewPlugin->import('classes.BookForReview');
        
        $path = $args[0] ?? null;
        $user = $request->getUser();
        if (!$user) {
            return;
        }
        $userId = (int) $user->getId();

        switch ($path) {
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
                $path = 'requested';
                $status = BFR_STATUS_REQUESTED;
                $template = 'booksForReviewRequested.tpl';
        }

        $rangeInfo = $this->getRangeInfo('booksForReview');
        /** @var BookForReviewDAO $bookReviewDao */
        $bookReviewDao = DAORegistry::getDAO('BookForReviewDAO');
        $booksForReview = $bookReviewDao->getBooksForReviewByJournalId($journalId, null, null, null, $status, $userId, null, $rangeInfo);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'booksForReview' => $booksForReview,
            'counts' => $bookReviewDao->getStatusCounts($journalId, $userId)
        ]);
        
        $this->setupTemplate($request, true);
        $templateMgr->display($bookReviewPlugin->getTemplatePath() . 'author/' . $template);
    }

    /**
     * Author requests a book for review.
     * @param array $args
     * @param PKPRequest $request
     */
    public function requestBookForReview(array $args, $request): void {
        $journal = $request->getJournal();
        if (!$journal) {
            $request->redirect(null, 'user');
            return;
        }
        $journalId = (int) $journal->getId();

        if (empty($args)) {
            $request->redirect(null, 'user');
            return;
        }

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bookId = (int) $args[0];

        /** @var BookForReviewDAO $bookReviewDao */
        $bookReviewDao = DAORegistry::getDAO('BookForReviewDAO');
        
        if ($bookReviewDao->getBookForReviewJournalId($bookId) === $journalId) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('BFR_BOOK_REQUESTED');
            $send = (bool) $request->getUserVar('send');

            if ($send && !$email->hasErrors()) {
                $book = $bookReviewDao->getBookForReview($bookId);
                
                // Ensure book for review is available
                if ($book && $book->getStatus() === BFR_STATUS_AVAILABLE) {
                    $user = $request->getUser();
                    if ($user) {
                        $userId = (int) $user->getId();

                        $book->setStatus(BFR_STATUS_REQUESTED);
                        $book->setUserId($userId);
                        $book->setDateRequested(date('Y-m-d H:i:s'));
                        $bookReviewDao->updateObject($book);

                        $email->send();

                        import('classes.notification.NotificationManager');
                        $notificationManager = new NotificationManager();
                        $notificationManager->createTrivialNotification($userId, NOTIFICATION_TYPE_BOOK_REQUESTED);
                    }
                }
                $request->redirect(null, 'author', 'booksForReview');
                return;
            } else {
                $continuedFlag = (bool) $request->getUserVar('continued');
                
                if (!$continuedFlag) {
                    $book = $bookReviewDao->getBookForReview($bookId);
                    
                    if ($book && $book->getStatus() === BFR_STATUS_AVAILABLE) {
                        $user = $request->getUser();
                        if ($user) {
                            $userFullName = $user->getFullName();
                            $userEmail = $user->getEmail();

                            $editorFullName = $book->getEditorFullName();
                            $editorEmail = $book->getEditorEmail();

                            $paramArray = [
                                'editorName' => strip_tags((string) $editorFullName),
                                'bookForReviewTitle' => '"' . strip_tags((string) $book->getLocalizedTitle()) . '"',
                                'authorContactSignature' => PKPString::html2text((string) $user->getContactSignature())
                            ];

                            $email->addRecipient((string) $editorEmail, (string) $editorFullName);
                            $email->setFrom((string) $userEmail, (string) $userFullName);
                            $email->assignParams($paramArray);
                        }
                    }
                    $returnUrl = $request->url(null, 'author', 'requestBookForReview', [$bookId]);
                    $email->displayEditForm($returnUrl);
                    return;
                }
            }
        }
        $request->redirect(null, 'booksForReview');
    }

    /**
     * Ensure that we have a journal, plugin is enabled, and user is author.
     * @see PKPHandler::authorize()
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
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

        if (!Validation::isAuthor($journal->getId())) {
            Validation::redirectLogin();
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
        $pageCrumbs = [
            [$request->url(null, 'user'), 'navigation.user'],
            [$request->url(null, 'author'), 'user.role.author']
        ];
        
        if ($subclass) {
            $pageCrumbs[] = [
                $request->url(null, 'author', 'booksForReview'),
                __('plugins.generic.booksForReview.displayName'),
                true
            ];
        }
        
        $templateMgr->assign('pageHierarchy', $pageCrumbs);

        /** @var BooksForReviewPlugin $bookReviewPlugin */
        $bookReviewPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        
        if ($bookReviewPlugin) {
            $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $bookReviewPlugin->getStyleSheet());
        }
    }

}
?>