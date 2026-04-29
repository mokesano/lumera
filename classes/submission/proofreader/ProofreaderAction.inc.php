<?php
declare(strict_types=1);

/**
 * @defgroup submission_proofreader_ProofreaderAction
 */

/**
 * @file classes/submission/proofreader/ProofreaderAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofreaderAction
 * @ingroup submission_proofreader_ProofreaderAction
 *
 * @brief ProofreaderAction class.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & HookRegistry::dispatch
 */

import('classes.submission.common.Action');

class ProofreaderAction extends Action {

    /**
     * Select a proofreader for submission
     * @param int $userId
     * @param object $article Article
     * @param object $request PKPRequest
     */
    public static function selectProofreader($userId, $article, $request) {
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $proofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $article->getId());

        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('ProofreaderAction::selectProofreader', [&$userId, &$article])) {
            $proofSignoff->setUserId($userId);
            $signoffDao->updateObject($proofSignoff);

            // Add log entry
            // [WIZDAM] Use injected request
            $user = $request->getUser();
            $userDao = DAORegistry::getDAO('UserDAO');
            $proofreader = $userDao->getById((int) $userId);
            if (!isset($proofreader)) return;
            
            import('classes.article.log.ArticleLog');
            import('classes.article.log.ArticleEventLogEntry');
            ArticleLog::logEvent($request, $article, ARTICLE_LOG_PROOFREAD_ASSIGN, 'log.proofread.assign', ['assignerName' => $user->getFullName(), 'proofreaderName' => $proofreader->getFullName()]);
        }
    }

    /**
     * Proofread Emails
     * @param int $articleId
     * @param string $mailType defined string - type of proofread mail being sent
     * @param object $request PKPRequest
     * @param string $actionPath - form action
     * @return boolean true iff ready for a redirect
     */
    public static function proofreadEmail($articleId, $mailType, $request, $actionPath = '') {
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $sectionEditorSubmission = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
        $userDao = DAORegistry::getDAO('UserDAO');
        
        // [WIZDAM] Use injected request
        $journal = $request->getJournal();
        $user = $request->getUser();
        $ccs = [];

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, $mailType);

        switch($mailType) {
            case 'PROOFREAD_AUTHOR_REQUEST':
                $eventType = ARTICLE_EMAIL_PROOFREAD_NOTIFY_AUTHOR;
                $signoffType = 'SIGNOFF_PROOFREADING_AUTHOR';
                $setDateField = 'setDateNotified';
                $nullifyDateFields = ['setDateUnderway', 'setDateCompleted', 'setDateAcknowledged'];
                $setUserId = (int) $sectionEditorSubmission->getUserId();
                $receiver = $userDao->getById($setUserId);
                $setUserId = $receiver; // Legacy assign, preserved
                if (!isset($receiver)) return true;
                $receiverName = $receiver->getFullName();
                $receiverAddress = $receiver->getEmail();
                $email->ccAssignedEditingSectionEditors($sectionEditorSubmission->getId());
                $addParamArray = [
                    'authorName' => $receiver->getFullName(),
                    'authorUsername' => $receiver->getUsername(),
                    'authorPassword' => $receiver->getPassword(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionUrl' => $request->url(null, 'author', 'submissionEditing', $articleId)
                ];
                break;

            case 'PROOFREAD_AUTHOR_ACK':
                $eventType = ARTICLE_EMAIL_PROOFREAD_THANK_AUTHOR;
                $signoffType = 'SIGNOFF_PROOFREADING_AUTHOR';
                $setDateField = 'setDateAcknowledged';
                $receiver = $userDao->getById((int) $sectionEditorSubmission->getUserId());
                if (!isset($receiver)) return true;
                $receiverName = $receiver->getFullName();
                $receiverAddress = $receiver->getEmail();
                $email->ccAssignedEditingSectionEditors($sectionEditorSubmission->getId());
                $addParamArray = [
                    'authorName' => $receiver->getFullName(),
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                break;

            case 'PROOFREAD_AUTHOR_COMPLETE':
                $eventType = ARTICLE_EMAIL_PROOFREAD_NOTIFY_AUTHOR_COMPLETE;
                $signoffType = 'SIGNOFF_PROOFREADING_AUTHOR';
                $setDateField = 'setDateCompleted';
                $getDateField = 'getDateCompleted';

                $editAssignments = $sectionEditorSubmission->getEditAssignments();
                $nextSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);

                if ((int) $nextSignoff->getUserId() !== 0) {
                    $setNextDateField = 'setDateNotified';
                    $proofreader = $userDao->getById((int) $nextSignoff->getUserId());

                    $receiverName = $proofreader->getFullName();
                    $receiverAddress = $proofreader->getEmail();

                    $editorAdded = false;
                    foreach ($editAssignments as $editAssignment) {
                        if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
                            $ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
                            $editorAdded = true;
                        }
                    }
                    if (!$editorAdded) $ccs[$journal->getSetting('contactEmail')] = $journal->getSetting('contactName');
                } else {
                    $editorAdded = false;
                    $assignmentIndex = 0;
                    foreach ($editAssignments as $editAssignment) {
                        if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
                            if ($assignmentIndex++ == 0) {
                                $receiverName = $editAssignment->getEditorFullName();
                                $receiverAddress = $editAssignment->getEditorEmail();
                            } else {
                                $ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
                            }
                            $editorAdded = true;
                        }
                    }
                    if (!$editorAdded) {
                        $receiverAddress = $journal->getSetting('contactEmail');
                        $receiverName =  $journal->getSetting('contactName');
                    }
                }

                $addParamArray = [
                    'editorialContactName' => $receiverName,
                    'authorName' => $user->getFullName()
                ];
                break;

            case 'PROOFREAD_REQUEST':
                $eventType = ARTICLE_EMAIL_PROOFREAD_NOTIFY_PROOFREADER;
                $signoffType = 'SIGNOFF_PROOFREADING_PROOFREADER';
                $setDateField = 'setDateNotified';
                $nullifyDateFields = ['setDateUnderway', 'setDateCompleted', 'setDateAcknowledged'];

                $receiver = $sectionEditorSubmission->getUserBySignoffType($signoffType);
                if (!isset($receiver)) return true;
                $receiverName = $receiver->getFullName();
                $receiverAddress = $receiver->getEmail();
                $email->ccAssignedEditingSectionEditors($sectionEditorSubmission->getId());

                $addParamArray = [
                    'proofreaderName' => $receiverName,
                    'proofreaderUsername' => $receiver->getUsername(),
                    'proofreaderPassword' => $receiver->getPassword(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionUrl' => $request->url(null, 'proofreader', 'submission', $articleId)
                ];
                break;

            case 'PROOFREAD_ACK':
                $eventType = ARTICLE_EMAIL_PROOFREAD_THANK_PROOFREADER;
                $signoffType = 'SIGNOFF_PROOFREADING_PROOFREADER';
                $setDateField = 'setDateAcknowledged';

                $receiver = $sectionEditorSubmission->getUserBySignoffType($signoffType);
                if (!isset($receiver)) return true;
                $receiverName = $receiver->getFullName();
                $receiverAddress = $receiver->getEmail();
                $email->ccAssignedEditingSectionEditors($sectionEditorSubmission->getId());

                $addParamArray = [
                    'proofreaderName' => $receiverName,
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                break;

            case 'PROOFREAD_COMPLETE':
                $eventType = ARTICLE_EMAIL_PROOFREAD_NOTIFY_PROOFREADER_COMPLETE;
                $signoffType = 'SIGNOFF_PROOFREADING_PROOFREADER';
                $setDateField = 'setDateCompleted';
                $getDateField = 'getDateCompleted';

                $setNextDateField = 'setDateNotified';
                $nextSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);

                $editAssignments = $sectionEditorSubmission->getEditAssignments();

                $receiver = null;

                $editorAdded = false;
                foreach ($editAssignments as $editAssignment) {
                    if ((bool)$editAssignment->getIsEditor() || (bool)$editAssignment->getCanEdit()) {
                        if ($receiver === null) {
                            $receiver = $userDao->getById((int) $editAssignment->getEditorId());
                        } else {
                            $ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
                        }
                        $editorAdded = true;
                    }
                }
                
                // [WIZDAM] Safety check for $receiver usage
                if (isset($receiver) && $receiver instanceof User) {
                    $receiverName = $receiver->getFullName();
                    $receiverAddress = $receiver->getEmail();
                } else {
                    $receiverAddress = $journal->getSetting('contactEmail');
                    $receiverName =  $journal->getSetting('contactName');
                }
                if (!$editorAdded) {
                    $ccs[$journal->getSetting('contactEmail')] = $journal->getSetting('contactName');
                }

                $addParamArray = [
                    'editorialContactName' => $receiverName,
                    'proofreaderName' => $user->getFullName()
                ];
                break;

            case 'PROOFREAD_LAYOUT_REQUEST':
                $eventType = ARTICLE_EMAIL_PROOFREAD_NOTIFY_LAYOUTEDITOR;
                $signoffType = 'SIGNOFF_PROOFREADING_LAYOUT';
                $setDateField = 'setDateNotified';
                $nullifyDateFields = ['setDateUnderway', 'setDateCompleted', 'setDateAcknowledged'];

                $receiver = $sectionEditorSubmission->getUserBySignoffType($signoffType);
                if (!isset($receiver)) return true;
                $receiverName = $receiver->getFullName();
                $receiverAddress = $receiver->getEmail();
                $email->ccAssignedEditingSectionEditors($sectionEditorSubmission->getId());

                $addParamArray = [
                    'layoutEditorName' => $receiverName,
                    'layoutEditorUsername' => $receiver->getUsername(),
                    'layoutEditorPassword' => $receiver->getPassword(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionUrl' => $request->url(null, 'layoutEditor', 'submission', $articleId)
                ];

                if (!$actionPath) {
                    // Reset underway/complete/thank dates
                    $signoffReset = $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $articleId);
                    $signoffReset->setDateUnderway(null);
                    $signoffReset->setDateCompleted(null);
                    $signoffReset->setDateAcknowledged(null);
                }
                break;

            case 'PROOFREAD_LAYOUT_ACK':
                $eventType = ARTICLE_EMAIL_PROOFREAD_THANK_LAYOUTEDITOR;
                $signoffType = 'SIGNOFF_PROOFREADING_LAYOUT';
                $setDateField = 'setDateAcknowledged';

                $receiver = $sectionEditorSubmission->getUserBySignoffType($signoffType);
                if (!isset($receiver)) return true;
                $receiverName = $receiver->getFullName();
                $receiverAddress = $receiver->getEmail();
                $email->ccAssignedEditingSectionEditors($sectionEditorSubmission->getId());

                $addParamArray = [
                    'layoutEditorName' => $receiverName,
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                break;

            case 'PROOFREAD_LAYOUT_COMPLETE':
                $eventType = ARTICLE_EMAIL_PROOFREAD_NOTIFY_LAYOUTEDITOR_COMPLETE;
                $signoffType = 'SIGNOFF_PROOFREADING_LAYOUT';
                $setDateField = 'setDateCompleted';
                $getDateField = 'getDateCompleted';

                $editAssignments = $sectionEditorSubmission->getEditAssignments();
                $assignmentIndex = 0;
                $editorAdded = false;
                foreach ($editAssignments as $editAssignment) {
                    if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
                        if ($assignmentIndex++ == 0) {
                            $receiverName = $editAssignment->getEditorFullName();
                            $receiverAddress = $editAssignment->getEditorEmail();
                        } else {
                            $ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
                        }
                        $editorAdded = true;
                    }
                }
                if (!$editorAdded) {
                    $receiverAddress = $journal->getSetting('contactEmail');
                    $receiverName =  $journal->getSetting('contactName');
                }

                $addParamArray = [
                    'editorialContactName' => $receiverName,
                    'layoutEditorName' => $user->getFullName()
                ];
                break;

            default:
                return true;
        }

        $signoff = $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $articleId);

        if (isset($getDateField)) {
            $date = $signoff->$getDateField();
            if (isset($date)) {
                $request->redirect(null, null, 'submission', $articleId);
            }
        }

        if ($email->isEnabled() && ($actionPath || $email->hasErrors())) {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($receiverAddress, $receiverName);
                if (isset($ccs)) foreach ($ccs as $address => $name) {
                    $email->addCc($address, $name);
                }

                $paramArray = [];

                if (isset($addParamArray)) {
                    $paramArray += $addParamArray;
                }
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($actionPath, ['articleId' => $articleId]);
            return false;
        } else {
            // [WIZDAM] HookRegistry::dispatch
            HookRegistry::dispatch('ProofreaderAction::proofreadEmail', [&$email, $mailType]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $signoff->$setDateField(Core::getCurrentDate());
            if (isset($setNextDateField)) {
                $nextSignoff->$setNextDateField(Core::getCurrentDate());
            }
            if (isset($nullifyDateFields)) foreach ($nullifyDateFields as $fieldSetter) {
                $signoff->$fieldSetter(null);
            }

            $signoffDao->updateObject($signoff);
            if(isset($nextSignoff)) $signoffDao->updateObject($nextSignoff);

            return true;
        }
    }

    /**
     * Set date for author/proofreader/LE proofreading underway
     * @param object $submission
     * @param string $signoffType
     */
    public static function proofreadingUnderway($submission, $signoffType) {
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $signoff = $signoffDao->build($signoffType, ASSOC_TYPE_ARTICLE, $submission->getId());

        // [WIZDAM] HookRegistry::dispatch
        if (!$signoff->getDateUnderway() && $signoff->getDateNotified() && !HookRegistry::dispatch('ProofreaderAction::proofreadingUnderway', [&$submission, &$signoffType])) {
            $dateUnderway = Core::getCurrentDate();
            $signoff->setDateUnderway($dateUnderway);
            $signoffDao->updateObject($signoff);
        }
    }

    //
    // Misc
    //

    /**
     * Download a file a proofreader has access to.
     * @param object $submission
     * @param int $fileId
     * @param int|null $revision
     */
    public static function downloadProofreaderFile($submission, $fileId, $revision = null) {
        $canDownload = false;

        // Proofreaders have access to:
        // 1) All supplementary files.
        // 2) All galley files.

        // Check supplementary files
        $suppFiles = $submission->getSuppFiles() ?? []; // [WIZDAM] Null Coalescing
        foreach ($suppFiles as $suppFile) {
            if ($suppFile->getFileId() == $fileId) {
                $canDownload = true;
            }
        }

        // Check galley files
        $galleys = $submission->getGalleys() ?? []; // [WIZDAM] Null Coalescing
        foreach ($galleys as $galleyFile) {
            if ($galleyFile->getFileId() == $fileId) {
                $canDownload = true;
            }
        }

        $result = false;
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('ProofreaderAction::downloadProofreaderFile', [&$submission, &$fileId, &$revision, &$canDownload, &$result])) {
            if ($canDownload) {
                return Action::downloadFile($submission->getId(), $fileId, $revision);
            } else {
                return false;
            }
        }
        return $result;
    }

    /**
     * View proofread comments.
     * @param object $article
     */
    public static function viewProofreadComments($article) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('ProofreaderAction::viewProofreadComments', [&$article])) {
            import('classes.submission.form.comment.ProofreadCommentForm');

            $commentForm = new ProofreadCommentForm($article, ROLE_ID_PROOFREADER);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Post proofread comment.
     * @param object $article
     * @param boolean $emailComment
     * @param object $request PKPRequest
     */
    public static function postProofreadComment($article, $emailComment, $request) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('ProofreaderAction::postProofreadComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.ProofreadCommentForm');

            $commentForm = new ProofreadCommentForm($article, ROLE_ID_PROOFREADER);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(true, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_PROOFREAD_COMMENT,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                if ($emailComment) {
                    $commentForm->email($request);
                }

            } else {
                $commentForm->display();
                return false;
            }
            return true;
        }
    }

    /**
     * View layout comments.
     * @param object $article
     */
    public static function viewLayoutComments($article) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('ProofreaderAction::viewLayoutComments', [&$article])) {
            import('classes.submission.form.comment.LayoutCommentForm');

            $commentForm = new LayoutCommentForm($article, ROLE_ID_PROOFREADER);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Post layout comment.
     * @param object $article
     * @param boolean $emailComment
     * @param object $request PKPRequest
     */
    public static function postLayoutComment($article, $emailComment, $request) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('ProofreaderAction::postLayoutComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.LayoutCommentForm');

            $commentForm = new LayoutCommentForm($article, ROLE_ID_PROOFREADER);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(true, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_LAYOUT_COMMENT,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                if ($emailComment) {
                    $commentForm->email($request);
                }
            } else {
                $commentForm->display();
                return false;
            }
            return true;
        }
    }
}
?>