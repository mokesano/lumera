<?php
declare(strict_types=1);

/**
 * @defgroup submission_common
 */

/**
 * @file classes/submission/common/Action.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Action
 * @ingroup submission_common
 *
 * @brief Application-specific submission actions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & HookRegistry::dispatch
 */


/* These constants correspond to editing decision "decision codes". */
define('SUBMISSION_EDITOR_DECISION_ACCEPT', 1);
define('SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS', 2);
define('SUBMISSION_EDITOR_DECISION_RESUBMIT', 3);
define('SUBMISSION_EDITOR_DECISION_DECLINE', 4);

/* These constants are used as search fields for the various submission lists */
define('SUBMISSION_FIELD_AUTHOR', 1);
define('SUBMISSION_FIELD_EDITOR', 2);
define('SUBMISSION_FIELD_TITLE', 3);
define('SUBMISSION_FIELD_REVIEWER', 4);
define('SUBMISSION_FIELD_COPYEDITOR', 5);
define('SUBMISSION_FIELD_LAYOUTEDITOR', 6);
define('SUBMISSION_FIELD_PROOFREADER', 7);
define('SUBMISSION_FIELD_ID', 8);

define('SUBMISSION_FIELD_DATE_SUBMITTED', 4);
define('SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE', 5);
define('SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE', 6);
define('SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE', 7);

import('lib.pkp.classes.submission.common.PKPAction');

class Action extends PKPAction {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Action() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Actions.
    //
    
    /**
     * View metadata of an article.
     * @param object $article Article
     * @param object $journal Journal
     */
    public static function viewMetadata($article, $journal) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('Action::viewMetadata', [&$article, &$journal])) {
            import('classes.submission.form.MetadataForm');
            $metadataForm = new MetadataForm($article, $journal);
            if ($metadataForm->getCanEdit() && $metadataForm->isLocaleResubmit()) {
                $metadataForm->readInputData();
            } else {
                $metadataForm->initData();
            }
            $metadataForm->display();
        }
    }

    /**
     * Save metadata.
     * @param object $article Article
     * @param object $request PKPRequest
     */
    public static function saveMetadata($article, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $router = $request->getRouter();

        if (!HookRegistry::dispatch('Action::saveMetadata', [&$article])) {
            import('classes.submission.form.MetadataForm');
            $journal = $request->getJournal();
            $metadataForm = new MetadataForm($article, $journal);
            $metadataForm->readInputData();

            // Check for any special cases before trying to save
            if ($request->getUserVar('addAuthor')) {
                // Add an author
                $editData = true;
                $authors = $metadataForm->getData('authors');
                $authors[] = []; // [WIZDAM] Modern syntax
                $metadataForm->setData('authors', $authors);

            } else if (($delAuthor = $request->getUserVar('delAuthor')) && count($delAuthor) == 1) {
                // Delete an author
                $editData = true;
                list($delAuthorIndex) = array_keys($delAuthor);
                $delAuthorIndex = (int) $delAuthorIndex;
                $authors = $metadataForm->getData('authors');
                
                if (isset($authors[$delAuthorIndex]['authorId']) && !empty($authors[$delAuthorIndex]['authorId'])) {
                    $deletedAuthors = explode(':', $metadataForm->getData('deletedAuthors'));
                    $deletedAuthors[] = $authors[$delAuthorIndex]['authorId']; // [WIZDAM] Modern syntax
                    $metadataForm->setData('deletedAuthors', join(':', $deletedAuthors));
                }
                array_splice($authors, $delAuthorIndex, 1);
                $metadataForm->setData('authors', $authors);

                if ($metadataForm->getData('primaryContact') == $delAuthorIndex) {
                    $metadataForm->setData('primaryContact', 0);
                }

            } else if ($request->getUserVar('moveAuthor')) {
                // Move an author up/down
                $editData = true;
                $moveAuthorDir = $request->getUserVar('moveAuthorDir');
                $moveAuthorDir = $moveAuthorDir == 'u' ? 'u' : 'd';
                $moveAuthorIndex = (int) $request->getUserVar('moveAuthorIndex');
                $authors = $metadataForm->getData('authors');

                if (!(($moveAuthorDir == 'u' && $moveAuthorIndex <= 0) || ($moveAuthorDir == 'd' && $moveAuthorIndex >= count($authors) - 1))) {
                    $tmpAuthor = $authors[$moveAuthorIndex];
                    $primaryContact = $metadataForm->getData('primaryContact');
                    if ($moveAuthorDir == 'u') {
                        $authors[$moveAuthorIndex] = $authors[$moveAuthorIndex - 1];
                        $authors[$moveAuthorIndex - 1] = $tmpAuthor;
                        if ($primaryContact == $moveAuthorIndex) {
                            $metadataForm->setData('primaryContact', $moveAuthorIndex - 1);
                        } else if ($primaryContact == ($moveAuthorIndex - 1)) {
                            $metadataForm->setData('primaryContact', $moveAuthorIndex);
                        }
                    } else {
                        $authors[$moveAuthorIndex] = $authors[$moveAuthorIndex + 1];
                        $authors[$moveAuthorIndex + 1] = $tmpAuthor;
                        if ($primaryContact == $moveAuthorIndex) {
                            $metadataForm->setData('primaryContact', $moveAuthorIndex + 1);
                        } else if ($primaryContact == ($moveAuthorIndex + 1)) {
                            $metadataForm->setData('primaryContact', $moveAuthorIndex);
                        }
                    }
                }
                $metadataForm->setData('authors', $authors);
            }

            if (isset($editData)) {
                $metadataForm->display();
                return false;

            } else {
                if (!$metadataForm->validate()) {
                    return $metadataForm->display();
                }
                $metadataForm->execute($request);

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds();
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_METADATA_MODIFIED,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                // Add log entry
                $user = $request->getUser();
                import('classes.article.log.ArticleLog');
                ArticleLog::logEvent($request, $article, ARTICLE_LOG_METADATA_UPDATE, 'log.editor.metadataModified', ['editorName' => $user->getFullName()]);

                return true;
            }
        }
    }

    /**
     * Download file.
     * @param int $articleId
     * @param int $fileId
     * @param int|null $revision
     * @return boolean
     */
    public static function downloadFile($articleId, $fileId, $revision = null) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($articleId);
        return $articleFileManager->downloadFile($fileId, $revision);
    }

    /**
     * View file.
     * @param int $articleId
     * @param int $fileId
     * @param int|null $revision
     * @return boolean
     */
    public static function viewFile($articleId, $fileId, $revision = null) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($articleId);
        return $articleFileManager->downloadFile($fileId, $revision, true);
    }

    /**
     * Display submission management instructions.
     * @param string $type the type of instructions (copy, layout, proof, referenceLinking)
     * @param array $allowed
     * @return boolean
     */
    public static function instructions($type, $allowed = ['copy', 'layout', 'proof', 'referenceLinking']) {
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();

        if (!HookRegistry::dispatch('Action::instructions', [&$type, &$allowed])) {
            if (!in_array($type, $allowed)) {
                return false;
            }

            AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
            switch ($type) {
                case 'copy':
                    $title = 'submission.copyedit.instructions';
                    $instructions = $journal->getLocalizedSetting('copyeditInstructions');
                    break;
                case 'layout':
                    $title = 'submission.layout.instructions';
                    $instructions = $journal->getLocalizedSetting('layoutInstructions');
                    break;
                case 'proof':
                    $title = 'submission.proofread.instructions';
                    $instructions = $journal->getLocalizedSetting('proofInstructions');
                    break;
                case 'referenceLinking':
                    if (!$journal->getSetting('provideRefLinkInstructions')) return false;
                    $title = 'submission.layout.referenceLinking';
                    $instructions = $journal->getLocalizedSetting('refLinkInstructions');
                    break;
                default:
                    return false;
            }
        }

        $templateMgr->assign('pageTitle', $title);
        $templateMgr->assign('instructions', $instructions);
        $templateMgr->display('submission/instructions.tpl');

        return true;
    }

    /**
     * Edit comment.
     * @param object $article Article
     * @param object $comment ArticleComment
     */
    public static function editComment($article, $comment) {
        if (!HookRegistry::dispatch('Action::editComment', [&$article, &$comment])) {
            import('classes.submission.form.comment.EditCommentForm');

            $commentForm = new EditCommentForm($article, $comment);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Save comment.
     * @param object $article Article
     * @param object $comment ArticleComment
     * @param boolean $emailComment
     * @param object $request PKPRequest
     */
    public static function saveComment($article, $comment, $emailComment, $request) {
        if (!HookRegistry::dispatch('Action::saveComment', [&$article, &$comment, &$emailComment])) {
            import('classes.submission.form.comment.EditCommentForm');

            $commentForm = new EditCommentForm($article, $comment);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(true, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_SUBMISSION_COMMENT,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                if ($emailComment) {
                    $commentForm->email($commentForm->emailHelper(), $request);
                }

            } else {
                $commentForm->display();
            }
        }
    }

    /**
     * Delete comment.
     * @param int $commentId
     * @param object|null $user The user who owns the comment, or null to default to Request::getUser
     */
    public static function deleteComment($commentId, $user = null) {
        if ($user == null) {
            $user = Application::get()->getRequest()->getUser();
        }

        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($commentId);

        if ($comment && $comment->getAuthorId() == $user->getId()) {
            if (!HookRegistry::dispatch('Action::deleteComment', [&$comment])) {
                $articleCommentDao->deleteArticleComment($comment);
            }
        }
    }
}
?>