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
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('classes.handler.Handler');

class BooksForReviewAuthorHandler extends Handler {

    /**
     * Display books for review author listing page.
     * [MODERNISASI] Hapus referensi & pada $request
     */
    public function booksForReview($args = array(), $request) {
        $this->setupTemplate();

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $bfrPlugin->import('classes.BookForReview');
        
        $path = !isset($args) || empty($args) ? null : $args[0];
        $user = $request->getUser();
        $userId = $user->getId();

        switch($path) {
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

        $rangeInfo = Handler::getRangeInfo('booksForReview');
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');
        $booksForReview = $bfrDao->getBooksForReviewByJournalId($journalId, null, null, null, $status, $userId, null, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        // [MODERNISASI] Gunakan assign, bukan assign_by_ref
        $templateMgr->assign('booksForReview', $booksForReview);
        $templateMgr->assign('counts', $bfrDao->getStatusCounts($journalId, $userId));
        
        $templateMgr->display($bfrPlugin->getTemplatePath() . 'author' . '/' . $template);
    }

    /**
     * Author requests a book for review.
     * [MODERNISASI] Hapus referensi & pada $request
     */
    public function requestBookForReview($args = array(), $request) {
        $this->setupTemplate();

        if (empty($args)) {
            $request->redirect(null, 'user');
        }

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $bookId = (int) $args[0];
        $bfrDao = DAORegistry::getDAO('BookForReviewDAO');

        // Ensure book for review is for this journal
        if ($bfrDao->getBookForReviewJournalId($bookId) == $journalId) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('BFR_BOOK_REQUESTED');
            
            // [SECURITY FIX] Amankan 'send' sebagai flag boolean
            $send = $request->getUserVar('send') ? true : false;

            // Author has filled out mail form or decided to skip email
            if ($send && !$email->hasErrors()) {

                // Update book for review as requested
                $book = $bfrDao->getBookForReview($bookId);
                $status = $book->getStatus();
                $bfrPlugin->import('classes.BookForReview');

                // Ensure book for review is avaliable
                if ($status == BFR_STATUS_AVAILABLE) {
                    $user = $request->getUser();
                    $userId = $user->getId();

                    $book->setStatus(BFR_STATUS_REQUESTED);
                    $book->setUserId($userId);
                    $book->setDateRequested(date('Y-m-d H:i:s', time()));
                    $bfrDao->updateObject($book);

                    $email->send();

                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $notificationManager->createTrivialNotification($userId, NOTIFICATION_TYPE_BOOK_REQUESTED);
                }
                $request->redirect(null, 'author', 'booksForReview');

            // Display mail form for author
            } else {
                // [SECURITY FIX] Amankan 'continued'
                $continuedFlag = $request->getUserVar('continued') ? true : false;
                
                if (!$continuedFlag) {
                    $book = $bfrDao->getBookForReview($bookId);
                    $status = $book->getStatus();
                    $bfrPlugin->import('classes.BookForReview');

                    // Ensure book for review is avaliable
                    if ($status == BFR_STATUS_AVAILABLE) {
                        $user = $request->getUser();
                        $userId = $user->getId();

                        $userFullName = $user->getFullName();
                        $userEmail = $user->getEmail();

                        $editorFullName = $book->getEditorFullName();
                        $editorEmail = $book->getEditorEmail();

                        $paramArray = array(
                            'editorName' => strip_tags($editorFullName),
                            'bookForReviewTitle' => '"' . strip_tags($book->getLocalizedTitle()) . '"',
                            'authorContactSignature' => PKPString::html2text($user->getContactSignature())
                        );

                        $email->addRecipient($editorEmail, $editorFullName);
                        $email->setFrom($userEmail, $userFullName);
                        $email->assignParams($paramArray);
                    }
                    $returnUrl = $request->url(null, 'author', 'requestBookForReview', $bookId);
                    $email->displayEditForm($returnUrl);
                }
            }
        }
        $request->redirect(null, 'booksForReview');
    }

    /**
     * Ensure that we have a journal, plugin is enabled, and user is author.
     * [MODERNISASI] Perbaiki signature authorize sesuai parent (tanpa &)
     */
    public function authorize($request, &$args, $roleAssignments) {
        $journal = $request->getJournal();
        if (!isset($journal)) return false;

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);

        if (!isset($bfrPlugin)) return false;

        if (!$bfrPlugin->getEnabled()) return false;

        if (!Validation::isAuthor($journal->getId())) Validation::redirectLogin();

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param $subclass boolean set to true if caller is below this handler in the hierarchy
     */
    public function setupTemplate($subclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = array(
            array(
                Request::url(null, 'user'),
                'navigation.user'
            ),
            array(
                Request::url(null, 'author'),
                'user.role.author'
            )
        );
        $templateMgr->assign('pageHierarchy', $pageCrumbs);

        $bfrPlugin = PluginRegistry::getPlugin('generic', BOOKS_FOR_REVIEW_PLUGIN_NAME);
        $templateMgr->addStyleSheet(Request::getBaseUrl() . '/' . $bfrPlugin->getStyleSheet());
    }
}

?>