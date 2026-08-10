<?php
declare(strict_types=1);

/**
 * @file classes/submission/sectionEditor/SectionEditorAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorAction
 * @ingroup submission
 *
 * @brief SectionEditorAction class.
 */

import('classes.submission.common.Action');

class SectionEditorAction extends Action {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function SectionEditorAction() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Editor Actions.
     */

    /**
     * Changes the section an article belongs in.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $sectionId
     */
    public static function changeSection($sectionEditorSubmission, $sectionId) {
        if (!HookRegistry::dispatch('SectionEditorAction::changeSection', [&$sectionEditorSubmission, $sectionId])) {
            /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
            $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
            $sectionEditorSubmission->setSectionId($sectionId);
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);
        }
    }

    /**
     * Records an editor's submission decision.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $decision
     * @param PKPRequest $request
     */
    public static function recordDecision($sectionEditorSubmission, $decision, $request) {
        $editAssignments = $sectionEditorSubmission->getEditAssignments();
        if (empty($editAssignments)) {
            return;
        }

        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $user = $request->getUser();
        $editorDecision = [
            'editDecisionId' => null,
            'editorId' => $user->getId(),
            'decision' => $decision,
            'dateDecided' => Core::getCurrentDate()
        ];

        if (!HookRegistry::dispatch('SectionEditorAction::recordDecision', [&$sectionEditorSubmission, $editorDecision])) {
            $sectionEditorSubmission->setStatus(STATUS_QUEUED);
            $sectionEditorSubmission->stampStatusModified();
            $sectionEditorSubmission->addDecision($editorDecision, $sectionEditorSubmission->getCurrentRound());
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);

            $decisions = SectionEditorSubmission::getEditorDecisionOptions();
            import('classes.article.log.ArticleLog');
            AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_APP_EDITOR);
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_EDITOR_DECISION, 'log.editor.decision', [
                'editorName' => $user->getFullName(), 
                'decision' => __($decisions[$decision])
            ]);
        }
    }

    /**
     * Assigns a reviewer to a submission.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewerId
     * @param int|null $round
     * @param PKPRequest $request
     */
    public static function addReviewer($sectionEditorSubmission, $reviewerId, $round, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        
        $reviewer = $userDao->getById($reviewerId);
        if ($round === null) {
            $round = $sectionEditorSubmission->getCurrentRound();
        }

        $assigned = $sectionEditorSubmissionDao->reviewerExists($sectionEditorSubmission->getId(), $reviewerId, $round);

        if (!$assigned && $reviewer !== null && !HookRegistry::dispatch('SectionEditorAction::addReviewer', [&$sectionEditorSubmission, $reviewerId])) {
            $reviewAssignment = $reviewAssignmentDao->newDataObject();
            $reviewAssignment->setReviewerId($reviewerId);
            $reviewAssignment->setDateAssigned(Core::getCurrentDate());
            $reviewAssignment->setRound($round);
            $reviewAssignment->setDateDue(self::getReviewDueDate());

            $journalId = $sectionEditorSubmission->getJournalId();
            /** @var SectionDAO $sectionDao */
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            /** @var ReviewFormDAO $reviewFormDao */
            $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');

            $sectionId = $sectionEditorSubmission->getSectionId();
            $section = $sectionDao->getSection($sectionId, $journalId);
            if ($section && ($reviewFormId = (int) $section->getReviewFormId())) {
                if ($reviewFormDao->reviewFormExists($reviewFormId, ASSOC_TYPE_JOURNAL, $journalId)) {
                    $reviewAssignment->setReviewFormId($reviewFormId);
                }
            }

            $sectionEditorSubmission->addReviewAssignment($reviewAssignment);
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);

            $reviewAssignment = $reviewAssignmentDao->getReviewAssignment($sectionEditorSubmission->getId(), $reviewerId, $round);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_REVIEW_ASSIGN, 'log.review.reviewerAssigned', [
                'reviewerName' => $reviewer->getFullName(), 
                'round' => $round, 
                'reviewId' => $reviewAssignment->getId()
            ]);
        }
    }

    /**
     * Clears a review assignment from a submission.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     * @param PKPRequest $request
     */
    public static function clearReview($sectionEditorSubmission, $reviewId, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        if ($reviewAssignment !== null && $reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId() && !HookRegistry::dispatch('SectionEditorAction::clearReview', [&$sectionEditorSubmission, $reviewAssignment])) {
            $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
            if ($reviewer === null) {
                return false;
            }
            $sectionEditorSubmission->removeReviewAssignment($reviewId);
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_REVIEW_CLEAR, 'log.review.reviewCleared', [
                'reviewerName' => $reviewer->getFullName(), 
                'articleId' => $sectionEditorSubmission->getId(), 
                'round' => $reviewAssignment->getRound()
            ]);
        }
    }

    /**
     * Notifies a reviewer about a review assignment.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function notifyReviewer($sectionEditorSubmission, $reviewId, $send, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        $isEmailBasedReview = (int) $journal->getSetting('mailSubmissionsToReviewers') === 1;
        $reviewerAccessKeysEnabled = (bool) $journal->getSetting('reviewerAccessKeysEnabled');
        $preventAddressChanges = $reviewerAccessKeysEnabled;
        
        import('classes.mail.ArticleMailTemplate');
        
        $round = $reviewAssignment->getRound();
        if ($isEmailBasedReview && $round === 1) {
            $emailTemplate = 'REVIEW_REQUEST_ATTACHED';
        } elseif ($isEmailBasedReview && $round > 1) {
            $emailTemplate = 'REVIEW_REQUEST_ATTACHED_SUBSEQUENT';
        } elseif ($reviewerAccessKeysEnabled && $round === 1) {
            $emailTemplate = 'REVIEW_REQUEST_ONECLICK';
        } elseif ($reviewerAccessKeysEnabled && $round > 1) {
            $emailTemplate = 'REVIEW_REQUEST_ONECLICK_SUBSEQUENT';
        } elseif ($round === 1) {
            $emailTemplate = 'REVIEW_REQUEST';
        } else {
            $emailTemplate = 'REVIEW_REQUEST_SUBSEQUENT';
        }

        $email = new ArticleMailTemplate($sectionEditorSubmission, $emailTemplate, null, $isEmailBasedReview);

        if ($preventAddressChanges) {
            $email->setAddressFieldsEnabled(false);
        }

        if ($reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId() && $reviewAssignment->getReviewFileId()) {
            $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
            if ($reviewer === null) {
                return true;
            }

            if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                HookRegistry::dispatch('SectionEditorAction::notifyReviewer', [&$sectionEditorSubmission, &$reviewAssignment, &$email]);
                if ($email->isEnabled()) {
                    if ($reviewerAccessKeysEnabled) {
                        import('lib.pkp.classes.security.AccessKeyManager');
                        import('pages.reviewer.ReviewerHandler');
                        $accessKeyManager = new AccessKeyManager();
                        $keyLifetime = ((int) $journal->getSetting('numWeeksPerReview') + 4) * 7;
                        $email->addPrivateParam('ACCESS_KEY', $accessKeyManager->createKey('ReviewerContext', $reviewer->getId(), $reviewId, $keyLifetime));
                    }

                    if ($preventAddressChanges) {
                        $email->clearAllRecipients();
                        $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
                    }
                    $email->send($request);
                }

                $reviewAssignment->setDateNotified(Core::getCurrentDate());
                $reviewAssignment->setCancelled(0);
                $reviewAssignment->stampModified();
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
                return true;
            } else {
                if (!$request->getUserVar('continued') || $preventAddressChanges) {
                    $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
                }

                if (!$request->getUserVar('continued')) {
                    $dateFormatShort = Config::getVar('general', 'date_format_short');
                    $weekLaterDate = date($dateFormatShort, strtotime('+1 week'));

                    if ($reviewAssignment->getDateDue() !== null) {
                        $reviewDueDate = date($dateFormatShort, strtotime($reviewAssignment->getDateDue()));
                    } else {
                        $numWeeks = max((int) $journal->getSetting('numWeeksPerReview'), 2);
                        $reviewDueDate = date($dateFormatShort, strtotime('+' . $numWeeks . ' week'));
                    }

                    $submissionUrl = $request->url(null, 'reviewer', 'submission', $reviewId, $reviewerAccessKeysEnabled ? ['key' => 'ACCESS_KEY'] : []);

                    $paramArray = [
                        'reviewerName' => $reviewer->getFullName(),
                        'weekLaterDate' => $weekLaterDate,
                        'reviewDueDate' => $reviewDueDate,
                        'reviewerUsername' => $reviewer->getUsername(),
                        'reviewerPassword' => $reviewer->getPassword(),
                        'editorialContactSignature' => $user->getContactSignature(),
                        'reviewGuidelines' => PKPString::html2text($journal->getLocalizedSetting('reviewGuidelines')),
                        'submissionReviewUrl' => $submissionUrl,
                        'abstractTermIfEnabled' => ($sectionEditorSubmission->getLocalizedAbstract() === '' ? '' : __('article.abstract')),
                        'passwordResetUrl' => $request->url(null, 'login', 'resetPassword', $reviewer->getUsername(), ['confirm' => Validation::generatePasswordResetHash($reviewer->getId())])
                    ];
                    $email->assignParams($paramArray);
                    
                    if ($isEmailBasedReview) {
                        import('classes.file.TemporaryFileManager');
                        $temporaryFileManager = new TemporaryFileManager();
                        $reviewVersion = $sectionEditorSubmission->getReviewFile();
                        if ($reviewVersion) {
                            $temporaryFile = $temporaryFileManager->articleToTemporaryFile($reviewVersion, $user->getId());
                            $email->addPersistAttachment($temporaryFile);
                        }
                    }
                }
                $email->displayEditForm($request->url(null, null, 'notifyReviewer'), ['reviewId' => $reviewId, 'articleId' => $sectionEditorSubmission->getId()]);
                return false;
            }
        }
        return true;
    }

    /**
     * Cancels a review.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function cancelReview($sectionEditorSubmission, $reviewId, $send, $request) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
        
        if ($reviewer === null) {
            return true;
        }

        if ($reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId()) {
            if ($reviewAssignment->getDateNotified() !== null && !$reviewAssignment->getCancelled() && ($reviewAssignment->getDateCompleted() === null || $reviewAssignment->getDeclined())) {
                import('classes.mail.ArticleMailTemplate');
                $email = new ArticleMailTemplate($sectionEditorSubmission, 'REVIEW_CANCEL');

                if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                    HookRegistry::dispatch('SectionEditorAction::cancelReview', [&$sectionEditorSubmission, &$reviewAssignment, &$email]);
                    if ($email->isEnabled()) {
                        $email->send($request);
                    }

                    $reviewAssignment->setCancelled(1);
                    $reviewAssignment->setDateCompleted(Core::getCurrentDate());
                    $reviewAssignment->stampModified();
                    $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

                    import('classes.article.log.ArticleLog');
                    ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_REVIEW_CANCEL, 'log.review.reviewCancelled', [
                        'reviewerName' => $reviewer->getFullName(), 
                        'articleId' => $sectionEditorSubmission->getId(), 
                        'round' => $reviewAssignment->getRound()
                    ]);
                } else {
                    if (!$request->getUserVar('continued')) {
                        $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
                        $paramArray = [
                            'reviewerName' => $reviewer->getFullName(),
                            'reviewerUsername' => $reviewer->getUsername(),
                            'reviewerPassword' => $reviewer->getPassword(),
                            'editorialContactSignature' => $user->getContactSignature()
                        ];
                        $email->assignParams($paramArray);
                    }
                    $email->displayEditForm($request->url(null, null, 'cancelReview', 'send'), ['reviewId' => $reviewId, 'articleId' => $sectionEditorSubmission->getId()]);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Reminds a reviewer about a review assignment.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function remindReviewer($sectionEditorSubmission, $reviewId, $send, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewerAccessKeysEnabled = (bool) $journal->getSetting('reviewerAccessKeysEnabled');
        $preventAddressChanges = $reviewerAccessKeysEnabled;

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, $reviewerAccessKeysEnabled ? 'REVIEW_REMIND_ONECLICK' : 'REVIEW_REMIND');

        if ($preventAddressChanges) {
            $email->setAddressFieldsEnabled(false);
        }

        if ($send && !$email->hasErrors()) {
            HookRegistry::dispatch('SectionEditorAction::remindReviewer', [&$sectionEditorSubmission, &$reviewAssignment, &$email]);
            $reviewer = $userDao->getById($reviewAssignment->getReviewerId());

            if ($reviewerAccessKeysEnabled) {
                import('lib.pkp.classes.security.AccessKeyManager');
                import('pages.reviewer.ReviewerHandler');
                $accessKeyManager = new AccessKeyManager();
                $keyLifetime = ((int) $journal->getSetting('numWeeksPerReview') + 4) * 7;
                $email->addPrivateParam('ACCESS_KEY', $accessKeyManager->createKey('ReviewerContext', $reviewer->getId(), $reviewId, $keyLifetime));
            }

            if ($preventAddressChanges) {
                $email->clearAllRecipients();
                $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
            }

            $email->send($request);

            $reviewAssignment->setDateReminded(Core::getCurrentDate());
            $reviewAssignment->setReminderWasAutomatic(0);
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
            return true;
        } elseif ($reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId()) {
            $reviewer = $userDao->getById($reviewAssignment->getReviewerId());

            if (!$request->getUserVar('continued')) {
                if ($reviewer === null) {
                    return true;
                }
                $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());

                $submissionUrl = $request->url(null, 'reviewer', 'submission', $reviewId, $reviewerAccessKeysEnabled ? ['key' => 'ACCESS_KEY'] : []);

                $reviewDueDate = strtotime($reviewAssignment->getDateDue());
                $dateFormatShort = Config::getVar('general', 'date_format_short');
                if ($reviewDueDate === -1 || $reviewDueDate === false) {
                    $reviewDueDate = '_____';
                } else {
                    $reviewDueDate = date($dateFormatShort, $reviewDueDate);
                }

                $paramArray = [
                    'reviewerName' => $reviewer->getFullName(),
                    'reviewerUsername' => $reviewer->getUsername(),
                    'reviewerPassword' => $reviewer->getPassword(),
                    'reviewDueDate' => $reviewDueDate,
                    'editorialContactSignature' => $user->getContactSignature(),
                    'passwordResetUrl' => $request->url(null, 'login', 'resetPassword', $reviewer->getUsername(), ['confirm' => Validation::generatePasswordResetHash($reviewer->getId())]),
                    'submissionReviewUrl' => $submissionUrl
                ];
                $email->assignParams($paramArray);

            } elseif ($preventAddressChanges) {
                $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
            }

            $email->displayEditForm(
                $request->url(null, null, 'remindReviewer', 'send'),
                [
                    'reviewerId' => $reviewer->getId(),
                    'articleId' => $sectionEditorSubmission->getId(),
                    'reviewId' => $reviewId
                ]
            );
            return false;
        }
        return true;
    }

    /**
     * Thanks a reviewer for completing a review assignment.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function thankReviewer($sectionEditorSubmission, $reviewId, $send, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'REVIEW_ACK');

        if ($reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId()) {
            $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
            if ($reviewer === null) {
                return true;
            }

            if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                HookRegistry::dispatch('SectionEditorAction::thankReviewer', [&$sectionEditorSubmission, &$reviewAssignment, &$email]);
                if ($email->isEnabled()) {
                    $email->send($request);
                }

                $reviewAssignment->setDateAcknowledged(Core::getCurrentDate());
                $reviewAssignment->stampModified();
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
            } else {
                if (!$request->getUserVar('continued')) {
                    $email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
                    $paramArray = [
                        'reviewerName' => $reviewer->getFullName(),
                        'editorialContactSignature' => $user->getContactSignature()
                    ];
                    $email->assignParams($paramArray);
                }
                $email->displayEditForm($request->url(null, null, 'thankReviewer', 'send'), ['reviewId' => $reviewId, 'articleId' => $sectionEditorSubmission->getId()]);
                return false;
            }
        }
        return true;
    }

    /**
     * Rates a reviewer for quality of a review.
     * @param int $articleId
     * @param int $reviewId
     * @param int $quality
     * @param PKPRequest $request
     */
    public static function rateReviewer($articleId, $reviewId, $quality, $request) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $request->getUser();

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $sectionEditorSubmission = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
        
        if ($reviewer === null) {
            return;
        }

        if ($reviewAssignment->getSubmissionId() === $articleId && !HookRegistry::dispatch('SectionEditorAction::rateReviewer', [&$reviewAssignment, &$reviewer, &$quality])) {
            if ($quality !== null && $quality >= 1 && $quality <= 5) {
                $reviewAssignment->setQuality($quality);
            }

            $reviewAssignment->setDateRated(Core::getCurrentDate());
            $reviewAssignment->stampModified();
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_REVIEW_RATE, 'log.review.reviewerRated', [
                'reviewerName' => $reviewer->getFullName(), 
                'articleId' => $articleId, 
                'round' => $reviewAssignment->getRound()
            ]);
        }
    }

    /**
     * Makes a reviewer's annotated version of an article available to the author.
     * @param int $articleId
     * @param int $reviewId
     * @param int $fileId
     * @param int $revision
     * @param bool $viewable
     */
    public static function makeReviewerFileViewable($articleId, $reviewId, $fileId, $revision, $viewable = false) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $articleFile = $articleFileDao->getArticleFile($fileId, $revision);

        if ($reviewAssignment->getSubmissionId() === $articleId && $reviewAssignment->getReviewerFileId() === $fileId && !HookRegistry::dispatch('SectionEditorAction::makeReviewerFileViewable', [&$reviewAssignment, &$articleFile, &$viewable])) {
            $articleFile->setViewable($viewable);
            $articleFileDao->updateArticleFile($articleFile);
        }
    }

    /**
     * Returns a formatted review due date.
     * @param string|null $dueDate
     * @param int|null $numWeeks
     * @return string
     */
    public static function getReviewDueDate($dueDate = null, $numWeeks = null) {
        $today = getDate();
        $todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        
        if ($dueDate) {
            $dueDateParts = explode('-', $dueDate);
            if ($todayTimestamp <= strtotime($dueDate)) {
                return date('Y-m-d H:i:s', mktime(0, 0, 0, (int)$dueDateParts[1], (int)$dueDateParts[2], (int)$dueDateParts[0]));
            }
            return date('Y-m-d H:i:s', $todayTimestamp);
        } elseif ($numWeeks) {
            return date('Y-m-d H:i:s', $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60));
        } else {
            $journal = Request::getJournal();
            /** @var JournalSettingsDAO $settingsDao */
            $settingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            $numWeeks = (int) $settingsDao->getSetting($journal->getId(), 'numWeeksPerReview');
            if ($numWeeks < 0) {
                $numWeeks = 0;
            }
            return date('Y-m-d H:i:s', $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60));
        }
    }

    /**
     * Sets the due date for a review assignment.
     * @param int $articleId
     * @param int $reviewId
     * @param string $dueDate
     * @param int $numWeeks
     * @param bool $logEntry
     * @param PKPRequest $request
     */
    public static function setDueDate($articleId, $reviewId, $dueDate, $numWeeks, $logEntry, $request) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
        if ($reviewer === null) {
            return false;
        }

        if ($reviewAssignment->getSubmissionId() === $articleId && !HookRegistry::dispatch('SectionEditorAction::setDueDate', [&$reviewAssignment, &$reviewer, &$dueDate, &$numWeeks])) {
            $dueDate = self::getReviewDueDate($dueDate, $numWeeks);
            $reviewAssignment->setDateDue($dueDate);
            $reviewAssignment->stampModified();
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            if ($logEntry) {
                /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
                $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
                $sectionEditorSubmission = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);
                import('classes.article.log.ArticleLog');
                ArticleLog::logEvent(
                    $request,
                    $sectionEditorSubmission,
                    ARTICLE_LOG_REVIEW_SET_DUE_DATE,
                    'log.review.reviewDueDateSet',
                    [
                        'reviewerName' => $reviewer->getFullName(),
                        'dueDate' => date(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getDateDue())),
                        'articleId' => $articleId,
                        'round' => $reviewAssignment->getRound()
                    ]
                );
            }
        }
    }

    /**
     * Remove cover page from article.
     * @param Article $submission
     * @param string $formLocale
     * @return bool
     */
    public static function removeArticleCoverPage($submission, $formLocale) {
        $journal = Request::getJournal();
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $publicFileManager->removeJournalFile($journal->getId(), $submission->getFileName($formLocale));
        $submission->setFileName('', $formLocale);
        $submission->setOriginalFileName('', $formLocale);
        $submission->setWidth('', $formLocale);
        $submission->setHeight('', $formLocale);

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleDao->updateArticle($submission);

        return true;
    }

    /**
     * Notifies an author that a submission was unsuitable.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function unsuitableSubmission($sectionEditorSubmission, $send, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();
        $author = $userDao->getById($sectionEditorSubmission->getUserId());
        
        if ($author === null) {
            return true;
        }

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'SUBMISSION_UNSUITABLE');

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::unsuitableSubmission', [&$sectionEditorSubmission, &$author, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }
            self::archiveSubmission($sectionEditorSubmission, $request);
            return true;
        } else {
            if (!$request->getUserVar('continued')) {
                $paramArray = [
                    'editorialContactSignature' => $user->getContactSignature(),
                    'authorName' => $author->getFullName()
                ];
                $email->assignParams($paramArray);
                $email->addRecipient($author->getEmail(), $author->getFullName());
            }
            $email->displayEditForm($request->url(null, null, 'unsuitableSubmission'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
    }

    /**
     * Sets the reviewer recommendation for a review assignment.
     * @param Article $article
     * @param int $reviewId
     * @param int $recommendation
     * @param int $acceptOption
     * @param PKPRequest $request
     */
    public static function setReviewerRecommendation($article, $reviewId, $recommendation, $acceptOption, $request) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $request->getUser();

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId(), true);

        if ($reviewAssignment->getSubmissionId() === $article->getId() && !HookRegistry::dispatch('SectionEditorAction::setReviewerRecommendation', [&$reviewAssignment, &$reviewer, &$recommendation, &$acceptOption])) {
            $reviewAssignment->setRecommendation($recommendation);

            $nowDate = Core::getCurrentDate();
            if (!$reviewAssignment->getDateConfirmed()) {
                $reviewAssignment->setDateConfirmed($nowDate);
            }
            $reviewAssignment->setDateCompleted($nowDate);
            $reviewAssignment->stampModified();

            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $article, ARTICLE_LOG_REVIEW_RECOMMENDATION_BY_PROXY, 'log.review.reviewRecommendationSetByProxy', [
                'editorName' => $user->getFullName(), 
                'reviewerName' => $reviewer->getFullName(), 
                'reviewId' => $reviewAssignment->getId(), 
                'round' => $reviewAssignment->getRound()
            ]);
        }
    }

    /**
     * Clear a review form.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     */
    public static function clearReviewForm($sectionEditorSubmission, $reviewId) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        if (HookRegistry::dispatch('SectionEditorAction::clearReviewForm', [&$sectionEditorSubmission, &$reviewAssignment, &$reviewId])) {
            return;
        }

        if ($reviewAssignment !== null && $reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId()) {
            /** @var ReviewFormResponseDAO $reviewFormResponseDao */
            $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
            $responses = $reviewFormResponseDao->getReviewReviewFormResponseValues($reviewId);
            if (!empty($responses)) {
                $reviewFormResponseDao->deleteByReviewId($reviewId);
            }
            $reviewAssignment->setReviewFormId(null);
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
        }
    }

    /**
     * Assigns a review form to a review.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     * @param int $reviewFormId
     */
    public static function addReviewForm($sectionEditorSubmission, $reviewId, $reviewFormId) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        if (HookRegistry::dispatch('SectionEditorAction::addReviewForm', [&$sectionEditorSubmission, &$reviewAssignment, &$reviewId, &$reviewFormId])) {
            return;
        }

        if ($reviewAssignment !== null && $reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId()) {
            if ($reviewAssignment->getReviewFormId() !== $reviewFormId) {
                /** @var ReviewFormResponseDAO $reviewFormResponseDao */
                $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
                $responses = $reviewFormResponseDao->getReviewReviewFormResponseValues($reviewId);
                if (!empty($responses)) {
                    $reviewFormResponseDao->deleteByReviewId($reviewId);
                }
                $reviewAssignment->setReviewFormId($reviewFormId);
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
            }
        }
    }

    /**
     * View review form response.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $reviewId
     */
    public static function viewReviewFormResponse($sectionEditorSubmission, $reviewId) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        if (HookRegistry::dispatch('SectionEditorAction::viewReviewFormResponse', [&$sectionEditorSubmission, &$reviewAssignment, &$reviewId])) {
            return;
        }

        if ($reviewAssignment !== null && $reviewAssignment->getSubmissionId() === $sectionEditorSubmission->getId()) {
            $reviewFormId = $reviewAssignment->getReviewFormId();
            if ($reviewFormId !== null) {
                import('classes.submission.form.ReviewFormResponseForm');
                $reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
                $reviewForm->initData();
                $reviewForm->display();
            }
        }
    }

    /**
     * Set the file to use as the default copyedit file.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $fileId
     * @param int $revision
     * @param PKPRequest $request
     */
    public static function setCopyeditFile($sectionEditorSubmission, $fileId, $revision, $request) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($sectionEditorSubmission->getId());
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        if (!HookRegistry::dispatch('SectionEditorAction::setCopyeditFile', [&$sectionEditorSubmission, &$fileId, &$revision])) {
            $newFileId = $articleFileManager->copyToCopyeditFile($fileId, $revision);
            $copyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $copyeditSignoff->setFileId($newFileId);
            $copyeditSignoff->setFileRevision(1);
            $signoffDao->updateObject($copyeditSignoff);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_COPYEDIT_SET_FILE, 'log.copyedit.copyeditFileSet');
        }
    }

    /**
     * Resubmit the file for review.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $fileId
     * @param int $revision
     * @param PKPRequest $request
     */
    public static function resubmitFile($sectionEditorSubmission, $fileId, $revision, $request) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($sectionEditorSubmission->getId());
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');

        if (!HookRegistry::dispatch('SectionEditorAction::resubmitFile', [&$sectionEditorSubmission, &$fileId, &$revision])) {
            $currentRound = $sectionEditorSubmission->getCurrentRound();
            $sectionEditorSubmission->setCurrentRound($currentRound + 1);
            $sectionEditorSubmission->stampStatusModified();

            $newFileId = $articleFileManager->copyToReviewFile($fileId, $revision, $sectionEditorSubmission->getReviewFileId());
            $newReviewFile = $articleFileDao->getArticleFile($newFileId);
            $newReviewFile->setRound($sectionEditorSubmission->getCurrentRound());
            $articleFileDao->updateArticleFile($newReviewFile);

            $editorFileId = $sectionEditorSubmission->getEditorFileId() !== null ? $sectionEditorSubmission->getEditorFileId() : null;
            $editorFileId = $articleFileManager->copyToEditorFile($newFileId, null, $editorFileId);
            $newEditorFile = $articleFileDao->getArticleFile($editorFileId);
            $newEditorFile->setRound($sectionEditorSubmission->getCurrentRound());
            $articleFileDao->updateArticleFile($newEditorFile);

            $reviewRevision = $articleFileDao->getRevisionNumber($newFileId);
            $sectionEditorSubmission->setReviewRevision($reviewRevision);
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);

            $previousRound = $sectionEditorSubmission->getCurrentRound() - 1;
            foreach ($sectionEditorSubmission->getReviewAssignments($previousRound) as $reviewAssignment) {
                if ($reviewAssignment->getRecommendation()) {
                    self::addReviewer($sectionEditorSubmission, $reviewAssignment->getReviewerId(), $sectionEditorSubmission->getCurrentRound(), $request);
                }
            }

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_REVIEW_RESUBMIT, 'log.review.resubmit');
        }
    }

    /**
     * Assigns a copyeditor to a submission.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param int $copyeditorId
     * @param PKPRequest $request
     */
    public static function selectCopyeditor($sectionEditorSubmission, $copyeditorId, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $assigned = $sectionEditorSubmissionDao->copyeditorExists($sectionEditorSubmission->getId(), $copyeditorId);

        if (!$assigned && !HookRegistry::dispatch('SectionEditorAction::selectCopyeditor', [&$sectionEditorSubmission, &$copyeditorId])) {
            $copyeditInitialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $copyeditInitialSignoff->setUserId($copyeditorId);
            $signoffDao->updateObject($copyeditInitialSignoff);

            $copyeditor = $userDao->getById($copyeditorId);
            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_COPYEDIT_ASSIGN, 'log.copyedit.copyeditorAssigned', ['copyeditorName' => $copyeditor->getFullName()]);
        }
    }

    /**
     * Notifies a copyeditor about a copyedit assignment.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function notifyCopyeditor($sectionEditorSubmission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'COPYEDIT_REQUEST');

        $copyeditor = $sectionEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
        if ($copyeditor === null) {
            return true;
        }

        if ($sectionEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL') && (!$email->isEnabled() || ($send && !$email->hasErrors()))) {
            HookRegistry::dispatch('SectionEditorAction::notifyCopyeditor', [&$sectionEditorSubmission, &$copyeditor, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $copyeditInitialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $copyeditInitialSignoff->setDateNotified(Core::getCurrentDate());
            $copyeditInitialSignoff->setDateUnderway(null);
            $copyeditInitialSignoff->setDateCompleted(null);
            $copyeditInitialSignoff->setDateAcknowledged(null);
            $signoffDao->updateObject($copyeditInitialSignoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
                $paramArray = [
                    'copyeditorName' => $copyeditor->getFullName(),
                    'copyeditorUsername' => $copyeditor->getUsername(),
                    'copyeditorPassword' => $copyeditor->getPassword(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionCopyeditingUrl' => $request->url(null, 'copyeditor', 'submission', $sectionEditorSubmission->getId())
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'notifyCopyeditor', 'send'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Initiates the initial copyedit stage when the editor does the copyediting.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param PKPRequest $request
     */
    public static function initiateCopyedit($sectionEditorSubmission, $request) {
        $user = $request->getUser();

        if ($sectionEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL') && !HookRegistry::dispatch('SectionEditorAction::initiateCopyedit', [&$sectionEditorSubmission])) {
            /** @var SignoffDAO $signoffDao */
            $signoffDao = DAORegistry::getDAO('SignoffDAO');
            $copyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            if (!$copyeditSignoff->getUserId()) {
                $copyeditSignoff->setUserId($user->getId());
            }
            $copyeditSignoff->setDateNotified(Core::getCurrentDate());
            $signoffDao->updateObject($copyeditSignoff);
        }
    }

    /**
     * Thanks a copyeditor about a copyedit assignment.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function thankCopyeditor($sectionEditorSubmission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'COPYEDIT_ACK');

        $copyeditor = $sectionEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
        if ($copyeditor === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::thankCopyeditor', [&$sectionEditorSubmission, &$copyeditor, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $initialSignoff->setDateAcknowledged(Core::getCurrentDate());
            $signoffDao->updateObject($initialSignoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
                $paramArray = [
                    'copyeditorName' => $copyeditor->getFullName(),
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'thankCopyeditor', 'send'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Notifies the author that the copyedit is complete.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function notifyAuthorCopyedit($sectionEditorSubmission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'COPYEDIT_AUTHOR_REQUEST');

        $author = $userDao->getById($sectionEditorSubmission->getUserId());
        if ($author === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::notifyAuthorCopyedit', [&$sectionEditorSubmission, &$author, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $authorSignoff->setUserId($author->getId());
            $authorSignoff->setDateNotified(Core::getCurrentDate());
            $authorSignoff->setDateUnderway(null);
            $authorSignoff->setDateCompleted(null);
            $authorSignoff->setDateAcknowledged(null);
            $signoffDao->updateObject($authorSignoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($author->getEmail(), $author->getFullName());
                $paramArray = [
                    'authorName' => $author->getFullName(),
                    'authorUsername' => $author->getUsername(),
                    'authorPassword' => $author->getPassword(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionCopyeditingUrl' => $request->url(null, 'author', 'submissionEditing', $sectionEditorSubmission->getId())
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'notifyAuthorCopyedit', 'send'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Thanks an author for completing editor / author review.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function thankAuthorCopyedit($sectionEditorSubmission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'COPYEDIT_AUTHOR_ACK');

        $author = $userDao->getById($sectionEditorSubmission->getUserId());
        if ($author === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::thankAuthorCopyedit', [&$sectionEditorSubmission, &$author, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $signoff->setDateAcknowledged(Core::getCurrentDate());
            $signoffDao->updateObject($signoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($author->getEmail(), $author->getFullName());
                $paramArray = [
                    'authorName' => $author->getFullName(),
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'thankAuthorCopyedit', 'send'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Notify copyeditor about final copyedit.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function notifyFinalCopyedit($sectionEditorSubmission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'COPYEDIT_FINAL_REQUEST');

        $copyeditor = $sectionEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
        if ($copyeditor === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::notifyFinalCopyedit', [&$sectionEditorSubmission, &$copyeditor, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $signoff->setUserId($copyeditor->getId());
            $signoff->setDateNotified(Core::getCurrentDate());
            $signoff->setDateUnderway(null);
            $signoff->setDateCompleted(null);
            $signoff->setDateAcknowledged(null);
            $signoffDao->updateObject($signoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
                $paramArray = [
                    'copyeditorName' => $copyeditor->getFullName(),
                    'copyeditorUsername' => $copyeditor->getUsername(),
                    'copyeditorPassword' => $copyeditor->getPassword(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionCopyeditingUrl' => $request->url(null, 'copyeditor', 'submission', $sectionEditorSubmission->getId())
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'notifyFinalCopyedit', 'send'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Thank copyeditor for completing final copyedit.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function thankFinalCopyedit($sectionEditorSubmission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($sectionEditorSubmission, 'COPYEDIT_FINAL_ACK');

        $copyeditor = $sectionEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
        if ($copyeditor === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::thankFinalCopyedit', [&$sectionEditorSubmission, &$copyeditor, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
            $signoff->setDateAcknowledged(Core::getCurrentDate());
            $signoffDao->updateObject($signoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
                $paramArray = [
                    'copyeditorName' => $copyeditor->getFullName(),
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'thankFinalCopyedit', 'send'), ['articleId' => $sectionEditorSubmission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Upload the review version of an article.
     * @param SectionEditorSubmission $sectionEditorSubmission
     */
    public static function uploadReviewVersion($sectionEditorSubmission) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($sectionEditorSubmission->getId());
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        $fileName = 'upload';
        if ($articleFileManager->uploadedFileExists($fileName) && !HookRegistry::dispatch('SectionEditorAction::uploadReviewVersion', [&$sectionEditorSubmission])) {
            if ($sectionEditorSubmission->getReviewFileId() !== null) {
                $reviewFileId = $articleFileManager->uploadReviewFile($fileName, $sectionEditorSubmission->getReviewFileId());
                $sectionEditorSubmission->setReviewRevision($sectionEditorSubmission->getReviewRevision() + 1);
            } else {
                $reviewFileId = $articleFileManager->uploadReviewFile($fileName);
                $sectionEditorSubmission->setReviewRevision(1);
            }
            $editorFileId = $articleFileManager->copyToEditorFile($reviewFileId, $sectionEditorSubmission->getReviewRevision(), $sectionEditorSubmission->getEditorFileId());
        }

        if (isset($reviewFileId) && $reviewFileId !== 0 && isset($editorFileId) && $editorFileId !== 0) {
            $sectionEditorSubmission->setReviewFileId($reviewFileId);
            $sectionEditorSubmission->setEditorFileId($editorFileId);
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);
        }
    }

    /**
     * Upload the post-review version of an article.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param PKPRequest $request
     */
    public static function uploadEditorVersion($sectionEditorSubmission, $request) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($sectionEditorSubmission->getId());
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        $fileName = 'upload';
        if ($articleFileManager->uploadedFileExists($fileName) && !HookRegistry::dispatch('SectionEditorAction::uploadEditorVersion', [&$sectionEditorSubmission])) {
            if ($sectionEditorSubmission->getEditorFileId() !== null) {
                $fileId = $articleFileManager->uploadEditorDecisionFile($fileName, $sectionEditorSubmission->getEditorFileId());
            } else {
                $fileId = $articleFileManager->uploadEditorDecisionFile($fileName);
            }
        }

        if (isset($fileId) && $fileId !== 0) {
            $sectionEditorSubmission->setEditorFileId($fileId);
            $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_EDITOR_FILE, 'log.editor.editorFile', ['fileId' => $sectionEditorSubmission->getEditorFileId()]);
        }
    }

    /**
     * Upload the copyedit version of an article.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param string $copyeditStage
     */
    public static function uploadCopyeditVersion($sectionEditorSubmission, $copyeditStage) {
        $articleId = $sectionEditorSubmission->getId();
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($articleId);
        /** @var ArticleFileDAO $articleFileDao */
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
        $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $articleId);

        if ($copyeditStage === 'final' && $authorSignoff->getDateCompleted() === null) {
            return;
        }
        if ($copyeditStage === 'author' && $initialSignoff->getDateCompleted() === null) {
            return;
        }

        $fileName = 'upload';
        if ($articleFileManager->uploadedFileExists($fileName) && !HookRegistry::dispatch('SectionEditorAction::uploadCopyeditVersion', [&$sectionEditorSubmission])) {
            if ($sectionEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true) !== null) {
                $copyeditFileId = $articleFileManager->uploadCopyeditFile($fileName, $sectionEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true));
            } else {
                $copyeditFileId = $articleFileManager->uploadCopyeditFile($fileName);
            }
        }

        if (isset($copyeditFileId) && $copyeditFileId !== 0) {
            $signoff = null; // [LUMERA FIX] Initialize to prevent undefined variable warning
            if ($copyeditStage === 'initial') {
                $signoff = $initialSignoff;
                $signoff->setFileId($copyeditFileId);
                $signoff->setFileRevision($articleFileDao->getRevisionNumber($copyeditFileId));
            } elseif ($copyeditStage === 'author') {
                $signoff = $authorSignoff;
                $signoff->setFileId($copyeditFileId);
                $signoff->setFileRevision($articleFileDao->getRevisionNumber($copyeditFileId));
            } elseif ($copyeditStage === 'final') {
                $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $articleId);
                $signoff->setFileId($copyeditFileId);
                $signoff->setFileRevision($articleFileDao->getRevisionNumber($copyeditFileId));
            }

            if ($signoff !== null) {
                $signoffDao->updateObject($signoff);
            }
        }
    }

    /**
     * Editor completes initial copyedit (copyeditors disabled).
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param PKPRequest $request
     */
    public static function completeCopyedit($sectionEditorSubmission, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        if ((bool) $journal->getSetting('useCopyeditors')) {
            return;
        }

        if (HookRegistry::dispatch('SectionEditorAction::completeCopyedit', [&$sectionEditorSubmission])) {
            return;
        }

        $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
        $signoff->setDateCompleted(Core::getCurrentDate());
        $signoffDao->updateObject($signoff);

        import('classes.article.log.ArticleLog');
        ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_COPYEDIT_INITIAL, 'log.copyedit.initialEditComplete', ['copyeditorName' => $user->getFullName()]);
    }

    /**
     * Section editor completes final copyedit (copyeditors disabled).
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param PKPRequest $request
     */
    public static function completeFinalCopyedit($sectionEditorSubmission, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        if ((bool) $journal->getSetting('useCopyeditors')) {
            return;
        }

        if (HookRegistry::dispatch('SectionEditorAction::completeFinalCopyedit', [&$sectionEditorSubmission])) {
            return;
        }

        $copyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());
        $copyeditSignoff->setDateCompleted(Core::getCurrentDate());
        $signoffDao->updateObject($copyeditSignoff);

        if ($copyEdFile = $sectionEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL')) {
            $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $sectionEditorSubmission->getId());

            if (!$layoutSignoff->getFileId()) {
                import('classes.file.ArticleFileManager');
                $articleFileManager = new ArticleFileManager($sectionEditorSubmission->getId());
                if ($layoutFileId = $articleFileManager->copyToLayoutFile($copyEdFile->getFileId(), $copyEdFile->getRevision())) {
                    $layoutSignoff->setFileId($layoutFileId);
                    $signoffDao->updateObject($layoutSignoff);
                }
            }
        }

        import('classes.article.log.ArticleLog');
        ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_COPYEDIT_FINAL, 'log.copyedit.finalEditComplete', ['copyeditorName' => $user->getFullName()]);
    }

    /**
     * Archive a submission.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param PKPRequest $request
     */
    public static function archiveSubmission($sectionEditorSubmission, $request) {
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        if (HookRegistry::dispatch('SectionEditorAction::archiveSubmission', [&$sectionEditorSubmission])) {
            return;
        }

        $journal = $request->getJournal();
        if ($sectionEditorSubmission->getStatus() === STATUS_PUBLISHED) {
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($sectionEditorSubmission->getId());
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($publishedArticle->getIssueId(), $publishedArticle->getJournalId());
            if ($issue->getPublished()) {
                import('classes.article.ArticleTombstoneManager');
                $articleTombstoneManager = new ArticleTombstoneManager();
                $articleTombstoneManager->insertArticleTombstone($publishedArticle, $journal);
            }
        }

        $sectionEditorSubmission->setStatus(STATUS_ARCHIVED);
        $sectionEditorSubmission->stampStatusModified();
        $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);

        import('classes.article.log.ArticleLog');
        ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_EDITOR_ARCHIVE, 'log.editor.archived', ['articleId' => $sectionEditorSubmission->getId()]);
    }

    /**
     * Restores a submission to the queue.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param PKPRequest $request
     */
    public static function restoreToQueue($sectionEditorSubmission, $request) {
        if (HookRegistry::dispatch('SectionEditorAction::restoreToQueue', [&$sectionEditorSubmission])) {
            return;
        }
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($sectionEditorSubmission->getId());
        $articleSearchIndex = null;
        
        if ($publishedArticle) {
            $sectionEditorSubmission->setStatus(STATUS_PUBLISHED);
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($publishedArticle->getIssueId(), $publishedArticle->getJournalId());
            if ($issue->getPublished()) {
                /** @var DataObjectTombstoneDAO $tombstoneDao */
                $tombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO');
                $tombstoneDao->deleteByDataObjectId($sectionEditorSubmission->getId());
            }
            import('classes.search.ArticleSearchIndex');
            $articleSearchIndex = new ArticleSearchIndex();
            $articleSearchIndex->articleMetadataChanged($publishedArticle);
        } else {
            $sectionEditorSubmission->setStatus(STATUS_QUEUED);
        }

        $sectionEditorSubmission->stampStatusModified();
        $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);
        if ($articleSearchIndex) {
            $articleSearchIndex->articleChangesFinished();
        }

        import('classes.article.log.ArticleLog');
        ArticleLog::logEvent($request, $sectionEditorSubmission, ARTICLE_LOG_EDITOR_RESTORE, 'log.editor.restored');
    }

    /**
     * Changes the section.
     * @param SectionEditorSubmission $submission
     * @param int $sectionId
     */
    public static function updateSection($submission, $sectionId) {
        if (HookRegistry::dispatch('SectionEditorAction::updateSection', [&$submission, &$sectionId])) {
            return;
        }
        /** @var SectionEditorSubmissionDAO $submissionDao */
        $submissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $submission->setSectionId($sectionId);
        $submissionDao->updateSectionEditorSubmission($submission);

        import('classes.search.ArticleSearchIndex');
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->articleMetadataChanged($submission);
        $articleSearchIndex->articleChangesFinished();
    }

    /**
     * Changes the submission RT comments status.
     * @param SectionEditorSubmission $submission
     * @param int $commentsStatus
     */
    public static function updateCommentsStatus($submission, $commentsStatus) {
        if (HookRegistry::dispatch('SectionEditorAction::updateCommentsStatus', [&$submission, &$commentsStatus])) {
            return;
        }
        /** @var SectionEditorSubmissionDAO $submissionDao */
        $submissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $submission->setCommentsStatus($commentsStatus);
        $submissionDao->updateSectionEditorSubmission($submission);
    }

    //
    // Layout Editing
    //

    /**
     * Upload the layout version of an article.
     * @param SectionEditorSubmission $submission
     */
    public static function uploadLayoutVersion($submission) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($submission->getId());
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());

        $fileName = 'layoutFile';
        if ($articleFileManager->uploadedFileExists($fileName) && !HookRegistry::dispatch('SectionEditorAction::uploadLayoutVersion', [&$submission])) {
            if ($layoutSignoff->getFileId() !== null) {
                $layoutFileId = $articleFileManager->uploadLayoutFile($fileName, $layoutSignoff->getFileId());
            } else {
                $layoutFileId = $articleFileManager->uploadLayoutFile($fileName);
            }
            $layoutSignoff->setFileId($layoutFileId);
            $signoffDao->updateObject($layoutSignoff);
        }
    }

    /**
     * Assign a layout editor to a submission.
     * @param SectionEditorSubmission $submission
     * @param int $editorId
     * @param PKPRequest $request
     */
    public static function assignLayoutEditor($submission, $editorId, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        
        if (HookRegistry::dispatch('SectionEditorAction::assignLayoutEditor', [&$submission, &$editorId])) {
            return;
        }

        import('classes.article.log.ArticleLog');
        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());
        $layoutProofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());
        
        if ($layoutSignoff->getUserId()) {
            $layoutEditor = $userDao->getById($layoutSignoff->getUserId());
            ArticleLog::logEvent($request, $submission, ARTICLE_LOG_LAYOUT_UNASSIGN, 'log.layout.layoutEditorUnassigned', [
                'layoutSignoffId' => $layoutSignoff->getId(), 
                'editorName' => $layoutEditor->getFullName()
            ]);
        }

        $layoutSignoff->setUserId($editorId);
        $layoutSignoff->setDateNotified(null);
        $layoutSignoff->setDateUnderway(null);
        $layoutSignoff->setDateCompleted(null);
        $layoutSignoff->setDateAcknowledged(null);
        
        $layoutProofSignoff->setUserId($editorId);
        $layoutProofSignoff->setDateNotified(null);
        $layoutProofSignoff->setDateUnderway(null);
        $layoutProofSignoff->setDateCompleted(null);
        $layoutProofSignoff->setDateAcknowledged(null);
        
        $signoffDao->updateObject($layoutSignoff);
        $signoffDao->updateObject($layoutProofSignoff);

        $layoutEditor = $userDao->getById($layoutSignoff->getUserId());
        ArticleLog::logEvent($request, $submission, ARTICLE_LOG_LAYOUT_ASSIGN, 'log.layout.layoutEditorAssigned', [
            'layoutSignoffId' => $layoutSignoff->getId(), 
            'editorName' => $layoutEditor->getFullName()
        ]);
    }

    /**
     * Notifies the current layout editor about an assignment.
     * @param SectionEditorSubmission $submission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function notifyLayoutEditor($submission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($submission, 'LAYOUT_REQUEST');
        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());
        $layoutEditor = $userDao->getById($layoutSignoff->getUserId());
        
        if ($layoutEditor === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::notifyLayoutEditor', [&$submission, &$layoutEditor, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $layoutSignoff->setDateNotified(Core::getCurrentDate());
            $layoutSignoff->setDateUnderway(null);
            $layoutSignoff->setDateCompleted(null);
            $layoutSignoff->setDateAcknowledged(null);
            $signoffDao->updateObject($layoutSignoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($layoutEditor->getEmail(), $layoutEditor->getFullName());
                $paramArray = [
                    'layoutEditorName' => $layoutEditor->getFullName(),
                    'layoutEditorUsername' => $layoutEditor->getUsername(),
                    'editorialContactSignature' => $user->getContactSignature(),
                    'submissionLayoutUrl' => $request->url(null, 'layoutEditor', 'submission', $submission->getId())
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'notifyLayoutEditor', 'send'), ['articleId' => $submission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Sends acknowledgement email to the current layout editor.
     * @param SectionEditorSubmission $submission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function thankLayoutEditor($submission, $send, $request) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($submission, 'LAYOUT_ACK');

        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());
        $layoutEditor = $userDao->getById($layoutSignoff->getUserId());
        
        if ($layoutEditor === null) {
            return true;
        }

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('SectionEditorAction::thankLayoutEditor', [&$submission, &$layoutEditor, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $layoutSignoff->setDateAcknowledged(Core::getCurrentDate());
            $signoffDao->updateObject($layoutSignoff);
        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($layoutEditor->getEmail(), $layoutEditor->getFullName());
                $paramArray = [
                    'layoutEditorName' => $layoutEditor->getFullName(),
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, null, 'thankLayoutEditor', 'send'), ['articleId' => $submission->getId()]);
            return false;
        }
        return true;
    }

    /**
     * Change the sequence order of a galley.
     * @param Article $article
     * @param int $galleyId
     * @param string $direction
     */
    public static function orderGalley($article, $galleyId, $direction) {
        import('classes.submission.layoutEditor.LayoutEditorAction');
        LayoutEditorAction::orderGalley($article, $galleyId, $direction);
    }

    /**
     * Delete a galley.
     * @param Article $article
     * @param int $galleyId
     */
    public static function deleteGalley($article, $galleyId) {
        import('classes.submission.layoutEditor.LayoutEditorAction');
        LayoutEditorAction::deleteGalley($article, $galleyId);
    }

    /**
     * Change the sequence order of a supplementary file.
     * @param Article $article
     * @param int $suppFileId
     * @param string $direction
     */
    public static function orderSuppFile($article, $suppFileId, $direction) {
        import('classes.submission.layoutEditor.LayoutEditorAction');
        LayoutEditorAction::orderSuppFile($article, $suppFileId, $direction);
    }

    /**
     * Delete a supplementary file.
     * @param Article $article
     * @param int $suppFileId
     */
    public static function deleteSuppFile($article, $suppFileId) {
        import('classes.submission.layoutEditor.LayoutEditorAction');
        LayoutEditorAction::deleteSuppFile($article, $suppFileId);
    }

    /**
     * Delete a file from an article.
     * @param SectionEditorSubmission $submission
     * @param int $fileId
     * @param int $revision
     */
    public static function deleteArticleFile($submission, $fileId, $revision) {
        import('classes.file.ArticleFileManager');
        $file = $submission->getEditorFile();

        if ($file !== null && $file->getFileId() === $fileId && !HookRegistry::dispatch('SectionEditorAction::deleteArticleFile', [&$submission, &$fileId, &$revision])) {
            $articleFileManager = new ArticleFileManager($submission->getId());
            $articleFileManager->deleteFile($fileId, $revision);
        }
    }

    /**
     * Delete an image from an article galley.
     * @param SectionEditorSubmission $submission
     * @param int $fileId
     * @param int $revision
     */
    public static function deleteArticleImage($submission, $fileId, $revision) {
        import('classes.file.ArticleFileManager');
        /** @var ArticleGalleyDAO $articleGalleyDao */
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        
        if (HookRegistry::dispatch('SectionEditorAction::deleteArticleImage', [&$submission, &$fileId, &$revision])) {
            return;
        }
        
        foreach ($submission->getGalleys() as $galley) {
            $images = $articleGalleyDao->getGalleyImages($galley->getId());
            foreach ($images as $imageFile) {
                if ($imageFile->getArticleId() === $submission->getId() && $fileId === $imageFile->getFileId() && $imageFile->getRevision() === $revision) {
                    $articleFileManager = new ArticleFileManager($submission->getId());
                    $articleFileManager->deleteFile($imageFile->getFileId(), $imageFile->getRevision());
                }
            }
        }
    }

    /**
     * Add Submission Note.
     * @param int $articleId
     * @param PKPRequest $request
     */
    public static function addSubmissionNote($articleId, $request) {
        import('classes.file.ArticleFileManager');
        /** @var NoteDAO $noteDao */
        $noteDao = DAORegistry::getDAO('NoteDAO');
        $user = $request->getUser();

        $note = $noteDao->newDataObject();
        $note->setAssocType(ASSOC_TYPE_ARTICLE);
        $note->setAssocId($articleId);
        $note->setUserId($user->getId());
        $note->setDateCreated(Core::getCurrentDate());
        $note->setDateModified(Core::getCurrentDate());
        $note->setTitle($request->getUserVar('title'));
        $note->setContents($request->getUserVar('note'));

        if (!HookRegistry::dispatch('SectionEditorAction::addSubmissionNote', [&$articleId, &$note])) {
            $articleFileManager = new ArticleFileManager($articleId);
            if ($articleFileManager->uploadedFileExists('upload')) {
                $fileId = $articleFileManager->uploadSubmissionNoteFile('upload');
            } else {
                $fileId = 0;
            }

            $note->setFileId($fileId);
            $noteDao->insertObject($note);
        }
    }

    /**
     * Remove Submission Note.
     * @param int $articleId
     * @param int $noteId
     * @param int $fileId
     */
    public static function removeSubmissionNote($articleId, $noteId, $fileId) {
        if (HookRegistry::dispatch('SectionEditorAction::removeSubmissionNote', [&$articleId, &$noteId, &$fileId])) {
            return;
        }

        if ($fileId) {
            import('classes.file.ArticleFileManager');
            $articleFileManager = new ArticleFileManager($articleId);
            $articleFileManager->deleteFile($fileId);
        }

        /** @var NoteDAO $noteDao */
        $noteDao = DAORegistry::getDAO('NoteDAO');
        $noteDao->deleteById($noteId);
    }

    /**
     * Updates Submission Note.
     * @param int $articleId
     * @param PKPRequest $request
     */
    public static function updateSubmissionNote($articleId, $request) {
        import('classes.file.ArticleFileManager');
        /** @var NoteDAO $noteDao */
        $noteDao = DAORegistry::getDAO('NoteDAO');
        $user = $request->getUser();

        $note = new Note();
        $note->setId($request->getUserVar('noteId'));
        $note->setAssocType(ASSOC_TYPE_ARTICLE);
        $note->setAssocId($articleId);
        $note->setUserId($user->getId());
        $note->setDateModified(Core::getCurrentDate());
        $note->setTitle($request->getUserVar('title'));
        $note->setContents($request->getUserVar('note'));
        $note->setFileId($request->getUserVar('fileId'));

        if (HookRegistry::dispatch('SectionEditorAction::updateSubmissionNote', [&$articleId, &$note])) {
            return;
        }

        $articleFileManager = new ArticleFileManager($articleId);

        if ($articleFileManager->uploadedFileExists('upload')) {
            $fileId = $articleFileManager->uploadSubmissionNoteFile('upload', $note->getFileId(), true);
            $note->setFileId($fileId);
        } else {
            if ($request->getUserVar('removeUploadedFile')) {
                $articleFileManager->deleteFile($note->getFileId());
                $note->setFileId(0);
            }
        }

        $noteDao->updateObject($note);
    }

    /**
     * Clear All Submission Notes.
     * @param int $articleId
     */
    public static function clearAllSubmissionNotes($articleId) {
        if (HookRegistry::dispatch('SectionEditorAction::clearAllSubmissionNotes', [&$articleId])) {
            return;
        }

        import('classes.file.ArticleFileManager');
        /** @var NoteDAO $noteDao */
        $noteDao = DAORegistry::getDAO('NoteDAO');
        $fileIds = $noteDao->getAllFileIds(ASSOC_TYPE_ARTICLE, $articleId);
        
        if (!empty($fileIds)) {
            $articleFileManager = new ArticleFileManager($articleId);
            foreach ($fileIds as $fileId) {
                $articleFileManager->deleteFile($fileId);
            }
        }

        $noteDao->deleteByAssoc(ASSOC_TYPE_ARTICLE, $articleId);
    }

    //
    // Comments
    //

    /**
     * View reviewer comments.
     * @param Article $article
     * @param int $reviewId
     */
    public static function viewPeerReviewComments($article, $reviewId) {
        if (HookRegistry::dispatch('SectionEditorAction::viewPeerReviewComments', [&$article, &$reviewId])) {
            return;
        }

        import('classes.submission.form.comment.PeerReviewCommentForm');
        $commentForm = new PeerReviewCommentForm($article, $reviewId, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->initData();
        $commentForm->display();
    }

    /**
     * Post reviewer comments.
     * @param Article $article
     * @param int $reviewId
     * @param bool $emailComment
     * @param PKPRequest $request
     * @return bool
     */
    public static function postPeerReviewComment($article, $reviewId, $emailComment, $request) {
        if (HookRegistry::dispatch('SectionEditorAction::postPeerReviewComment', [&$article, &$reviewId, &$emailComment])) {
            return;
        }

        import('classes.submission.form.comment.PeerReviewCommentForm');
        $commentForm = new PeerReviewCommentForm($article, $reviewId, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->readInputData();
        
        if ($commentForm->validate()) {
            $commentForm->execute();

            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationUsers = $article->getAssociatedUserIds(false, false);
            foreach ($notificationUsers as $userRole) {
                $notificationManager->createNotification(
                    $request, $userRole['id'], NOTIFICATION_TYPE_REVIEWER_COMMENT,
                    $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                );
            }

            if ($emailComment) {
                $recipients = [];
                $commentForm->email($request, $recipients); 
            }
        } else {
            $commentForm->display();
            return false;
        }
        return true;
    }

    /**
     * View editor decision comments.
     * @param Article $article
     */
    public static function viewEditorDecisionComments($article) {
        if (HookRegistry::dispatch('SectionEditorAction::viewEditorDecisionComments', [&$article])) {
            return;
        }

        import('classes.submission.form.comment.EditorDecisionCommentForm');
        $commentForm = new EditorDecisionCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->initData();
        $commentForm->display();
    }

    /**
     * Post editor decision comment.
     * @param Article $article
     * @param bool $emailComment
     * @param PKPRequest $request
     * @return bool
     */
    public static function postEditorDecisionComment($article, $emailComment, $request) {
        if (HookRegistry::dispatch('SectionEditorAction::postEditorDecisionComment', [&$article, &$emailComment])) {
            return;
        }

        import('classes.submission.form.comment.EditorDecisionCommentForm');
        $commentForm = new EditorDecisionCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->readInputData();

        if ($commentForm->validate()) {
            $commentForm->execute();

            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationUsers = $article->getAssociatedUserIds(true, false);
            foreach ($notificationUsers as $userRole) {
                $notificationManager->createNotification(
                    $request, $userRole['id'], NOTIFICATION_TYPE_EDITOR_DECISION_COMMENT,
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

    /**
     * Email editor decision comment.
     * @param SectionEditorSubmission $sectionEditorSubmission
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function emailEditorDecisionComment($sectionEditorSubmission, $send, $request) {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        /** @var ArticleCommentDAO $articleCommentDao */
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $decisionTemplateMap = [
            SUBMISSION_EDITOR_DECISION_ACCEPT => 'EDITOR_DECISION_ACCEPT',
            SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'EDITOR_DECISION_REVISIONS',
            SUBMISSION_EDITOR_DECISION_RESUBMIT => 'EDITOR_DECISION_RESUBMIT',
            SUBMISSION_EDITOR_DECISION_DECLINE => 'EDITOR_DECISION_DECLINE'
        ];

        $decisions = $sectionEditorSubmission->getDecisions();
        $decisions = array_pop($decisions);
        $decision = array_pop($decisions);
        $decisionConst = $decision ? $decision['decision'] : null;

        $email = new ArticleMailTemplate(
            $sectionEditorSubmission,
            isset($decisionTemplateMap[$decisionConst]) ? $decisionTemplateMap[$decisionConst] : null
        );

        if ($send && !$email->hasErrors()) {
            HookRegistry::dispatch('SectionEditorAction::emailEditorDecisionComment', [&$sectionEditorSubmission, &$send, &$request]);
            $email->send($request);

            if ($decisionConst === SUBMISSION_EDITOR_DECISION_DECLINE) {
                $sectionEditorSubmission->setStatus(STATUS_ARCHIVED);
                $sectionEditorSubmission->stampStatusModified();
                $sectionEditorSubmissionDao->updateSectionEditorSubmission($sectionEditorSubmission);
            }

            $articleComment = new ArticleComment();
            $articleComment->setCommentType(COMMENT_TYPE_EDITOR_DECISION);
            $articleComment->setRoleId(Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
            $articleComment->setArticleId($sectionEditorSubmission->getId());
            $articleComment->setAuthorId($sectionEditorSubmission->getUserId());
            $articleComment->setCommentTitle($email->getSubject());
            $articleComment->setComments($email->getBody());
            $articleComment->setDatePosted(Core::getCurrentDate());
            $articleComment->setViewable(true);
            $articleComment->setAssocId($sectionEditorSubmission->getId());
            $articleCommentDao->insertArticleComment($articleComment);

            return true;
        } else {
            if (!$request->getUserVar('continued')) {
                $authorUser = $userDao->getById($sectionEditorSubmission->getUserId());
                $authorEmail = $authorUser->getEmail();
                $email->assignParams([
                    'editorialContactSignature' => $user->getContactSignature(),
                    'authorName' => $authorUser->getFullName(),
                    'journalTitle' => $journal->getLocalizedTitle()
                ]);
                $email->addRecipient($authorEmail, $authorUser->getFullName());
                if ($journal->getSetting('notifyAllAuthorsOnDecision')) {
                    foreach ($sectionEditorSubmission->getAuthors() as $author) {
                        if ($author->getEmail() !== $authorEmail) {
                            $email->addCc($author->getEmail(), $author->getFullName());
                        }
                    }
                }
            } elseif ($request->getUserVar('importPeerReviews')) {
                /** @var ReviewAssignmentDAO $reviewAssignmentDao */
                $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
                $reviewAssignments = $reviewAssignmentDao->getBySubmissionId($sectionEditorSubmission->getId(), $sectionEditorSubmission->getCurrentRound());
                $reviewIndexes = $reviewAssignmentDao->getReviewIndexesForRound($sectionEditorSubmission->getId(), $sectionEditorSubmission->getCurrentRound());

                $body = '';
                foreach ($reviewAssignments as $reviewAssignment) {
                    if ($reviewAssignment->getDateCompleted() !== null && !$reviewAssignment->getCancelled()) {
                        $articleComments = $articleCommentDao->getArticleComments($sectionEditorSubmission->getId(), COMMENT_TYPE_PEER_REVIEW, $reviewAssignment->getId());
                        if ($articleComments) {
                            $body .= "------------------------------------------------------\n";
                            $body .= __('submission.comments.importPeerReviews.reviewerLetter', ['reviewerLetter' => PKPString::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getReviewId()])]) . "\n";
                            if (is_array($articleComments)) {
                                foreach ($articleComments as $comment) {
                                    if ($comment->getViewable()) {
                                        $body .= PKPString::html2text($comment->getComments()) . "\n\n";
                                    }
                                }
                            }
                            $body .= "------------------------------------------------------\n\n";
                        }
                        
                        if ($reviewFormId = $reviewAssignment->getReviewFormId()) {
                            $reviewId = $reviewAssignment->getId();
                            /** @var ReviewFormResponseDAO $reviewFormResponseDao */
                            $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
                            /** @var ReviewFormElementDAO $reviewFormElementDao */
                            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
                            $reviewFormElements = $reviewFormElementDao->getReviewFormElements($reviewFormId);
                            
                            if (!$articleComments) {
                                $body .= "------------------------------------------------------\n";
                                $body .= __('submission.comments.importPeerReviews.reviewerLetter', ['reviewerLetter' => PKPString::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()])]) . "\n\n";
                            }
                            
                            foreach ($reviewFormElements as $reviewFormElement) {
                                if ($reviewFormElement->getIncluded()) {
                                    $body .= PKPString::html2text($reviewFormElement->getLocalizedQuestion()) . ": \n";
                                    $reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($reviewId, $reviewFormElement->getId());

                                    if ($reviewFormResponse) {
                                        $possibleResponses = $reviewFormElement->getLocalizedPossibleResponses();
                                        $value = $reviewFormResponse->getValue();
                                        
                                        if (in_array($reviewFormElement->getElementType(), $reviewFormElement->getMultipleResponsesElementTypes(), true)) {
                                            if ($reviewFormElement->getElementType() === REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES && is_iterable($value)) {
                                                foreach ($value as $val) {
                                                    $body .= "\t" . PKPString::html2text($possibleResponses[$val - 1]['content']) . "\n";
                                                }
                                            } else {
                                                $body .= "\t" . PKPString::html2text($possibleResponses[$value - 1]['content']) . "\n";
                                            }
                                            $body .= "\n";
                                        } else {
                                            $body .= "\t" . $value . "\n\n";
                                        }
                                    }
                                }
                            }
                            $body .= "------------------------------------------------------\n\n";
                        }
                    }
                }
                $oldBody = $email->getBody();
                if (!empty($oldBody)) {
                    $oldBody .= "\n";
                }
                $email->setBody($oldBody . $body);
            }

            $email->displayEditForm($request->url(null, null, 'emailEditorDecisionComment', 'send'), ['articleId' => $sectionEditorSubmission->getId()], 'submission/comment/editorDecisionEmail.tpl', ['isAnEditor' => true]);
            return false;
        }
    }

    /**
     * Blind CC the editor decision email to reviewers.
     * @param Article $article
     * @param bool $send
     * @param PKPRequest $request
     * @return bool
     */
    public static function bccEditorDecisionCommentToReviewers($article, $send, $request) {
        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($article, 'SUBMISSION_DECISION_REVIEWERS');

        if ($send && !$email->hasErrors()) {
            $reviewAssignments = []; // [LUMERA FIX] Initialize to prevent undefined variable warning
            HookRegistry::dispatch('SectionEditorAction::bccEditorDecisionCommentToReviewers', [&$article, &$reviewAssignments, &$email]);
            $email->send($request);
            return true;
        } else {
            if (!$request->getUserVar('continued')) {
                /** @var UserDAO $userDao */
                $userDao = DAORegistry::getDAO('UserDAO');
                /** @var ReviewAssignmentDAO $reviewAssignmentDao */
                $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
                $reviewAssignments = $reviewAssignmentDao->getBySubmissionId($article->getId(), $article->getCurrentRound());
                $email->clearRecipients();
                
                foreach ($reviewAssignments as $reviewAssignment) {
                    if ($reviewAssignment->getDateCompleted() !== null && !$reviewAssignment->getCancelled()) {
                        $reviewer = $userDao->getById($reviewAssignment->getReviewerId());
                        if ($reviewer !== null) {
                            $email->addBcc($reviewer->getEmail(), $reviewer->getFullName());
                        }
                    }
                }

                $commentsText = "";
                // [LUMERA FIX] Use method_exists to satisfy linter without changing signature type hints
                if (method_exists($article, 'getMostRecentEditorDecisionComment') && $article->getMostRecentEditorDecisionComment()) {
                    $comment = $article->getMostRecentEditorDecisionComment();
                    $commentsText = PKPString::html2text($comment->getComments()) . "\n\n";
                }
                $user = $request->getUser();

                $paramArray = [
                    'comments' => $commentsText,
                    'editorialContactSignature' => $user->getContactSignature()
                ];
                $email->assignParams($paramArray);
            }

            $email->displayEditForm($request->url(null, null, 'bccEditorDecisionCommentToReviewers', 'send'), ['articleId' => $article->getId()]);
            return false;
        }
    }

    /**
     * View copyedit comments.
     * @param Article $article
     */
    public static function viewCopyeditComments($article) {
        if (HookRegistry::dispatch('SectionEditorAction::viewCopyeditComments', [&$article])) {
            return;
        }

        import('classes.submission.form.comment.CopyeditCommentForm');
        $commentForm = new CopyeditCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->initData();
        $commentForm->display();
    }

    /**
     * Post copyedit comment.
     * @param Article $article
     * @param bool $emailComment
     * @param PKPRequest $request
     * @return bool
     */
    public static function postCopyeditComment($article, $emailComment, $request) {
        if (HookRegistry::dispatch('SectionEditorAction::postCopyeditComment', [&$article, &$emailComment])) {
            return;
        }

        import('classes.submission.form.comment.CopyeditCommentForm');
        $commentForm = new CopyeditCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->readInputData();

        if ($commentForm->validate()) {
            $commentForm->execute();

            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationUsers = $article->getAssociatedUserIds(true, false);
            foreach ($notificationUsers as $userRole) {
                $notificationManager->createNotification(
                    $request, $userRole['id'], NOTIFICATION_TYPE_COPYEDIT_COMMENT,
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

    /**
     * View layout comments.
     * @param Article $article
     */
    public static function viewLayoutComments($article) {
        if (HookRegistry::dispatch('SectionEditorAction::viewLayoutComments', [&$article])) {
            return;
        }

        import('classes.submission.form.comment.LayoutCommentForm');
        $commentForm = new LayoutCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->initData();
        $commentForm->display();
    }

    /**
     * Post layout comment.
     * @param Article $article
     * @param bool $emailComment
     * @param PKPRequest $request
     * @return bool
     */
    public static function postLayoutComment($article, $emailComment, $request) {
        if (HookRegistry::dispatch('SectionEditorAction::postLayoutComment', [&$article, &$emailComment])) {
            return;
        }

        import('classes.submission.form.comment.LayoutCommentForm');
        $commentForm = new LayoutCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->readInputData();

        if ($commentForm->validate()) {
            $commentForm->execute();

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

    /**
     * View proofread comments.
     * @param Article $article
     */
    public static function viewProofreadComments($article) {
        if (HookRegistry::dispatch('SectionEditorAction::viewProofreadComments', [&$article])) {
            return;
        }

        import('classes.submission.form.comment.ProofreadCommentForm');
        $commentForm = new ProofreadCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->initData();
        $commentForm->display();
    }

    /**
     * Post proofread comment.
     * @param Article $article
     * @param bool $emailComment
     * @param PKPRequest $request
     * @return bool
     */
    public static function postProofreadComment($article, $emailComment, $request) {
        if (HookRegistry::dispatch('SectionEditorAction::postProofreadComment', [&$article, &$emailComment])) {
            return;
        }

        import('classes.submission.form.comment.ProofreadCommentForm');
        $commentForm = new ProofreadCommentForm($article, Validation::isEditor() ? ROLE_ID_EDITOR : ROLE_ID_SECTION_EDITOR);
        $commentForm->readInputData();

        if ($commentForm->validate()) {
            $commentForm->execute();

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

    /**
     * Confirms the review assignment on behalf of its reviewer.
     * @param int $reviewId
     * @param bool $accept
     * @param PKPRequest $request
     */
    public static function confirmReviewForReviewer($reviewId, $accept, $request) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $request->getUser();

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId(), true);

        if (HookRegistry::dispatch('SectionEditorAction::acceptReviewForReviewer', [&$reviewAssignment, &$reviewer, &$accept])) {
            return;
        }

        if ($reviewAssignment->getDateConfirmed() === null) {
            $reviewAssignment->setDateReminded(null);
            $reviewAssignment->setReminderWasAutomatic(null);
            $reviewAssignment->setDeclined($accept ? 0 : 1);
            $reviewAssignment->setDateConfirmed(Core::getCurrentDate());
            $reviewAssignment->stampModified();
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($reviewAssignment->getSubmissionId());

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $article, ARTICLE_LOG_REVIEW_CONFIRM_BY_PROXY, $accept ? 'log.review.reviewAcceptedByProxy' : 'log.review.reviewDeclinedByProxy', [
                'reviewerName' => $reviewer->getFullName(), 
                'round' => $reviewAssignment->getRound(), 
                'userName' => $user->getFullName(), 
                'reviewId' => $reviewAssignment->getId()
            ]);
        }
    }

    /**
     * Upload a review on behalf of its reviewer.
     * @param int $reviewId
     * @param Article $article
     * @param PKPRequest $request
     */
    public static function uploadReviewForReviewer($reviewId, $article, $request) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $request->getUser();

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId(), true);

        if (HookRegistry::dispatch('SectionEditorAction::uploadReviewForReviewer', [&$reviewAssignment, &$reviewer])) {
            return;
        }

        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($reviewAssignment->getSubmissionId());
        
        if (!$reviewAssignment->getRecommendation() && !$reviewAssignment->getCancelled()) {
            $fileName = 'upload';
            if ($articleFileManager->uploadedFileExists($fileName)) {
                if ($reviewAssignment->getReviewerFileId() !== null) {
                    $fileId = $articleFileManager->uploadReviewFile($fileName, $reviewAssignment->getReviewerFileId());
                } else {
                    $fileId = $articleFileManager->uploadReviewFile($fileName);
                }
            }
        }

        if (isset($fileId) && $fileId !== 0) {
            if ($reviewAssignment->getDateConfirmed() === null) {
                $reviewAssignment->setDeclined(0);
                $reviewAssignment->setDateConfirmed(Core::getCurrentDate());
            }

            $reviewAssignment->setReviewerFileId($fileId);
            $reviewAssignment->stampModified();
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $article, ARTICLE_LOG_REVIEW_FILE_BY_PROXY, 'log.review.reviewFileByProxy', [
                'reviewerName' => $reviewer->getFullName(), 
                'round' => $reviewAssignment->getRound(), 
                'userName' => $user->getFullName(), 
                'reviewId' => $reviewAssignment->getId()
            ]);
        }
    }

    /**
     * Helper method for building submission breadcrumb.
     * @param int $articleId
     * @param string $parentPage
     * @param string $section
     * @return array
     */
    public static function submissionBreadcrumb($articleId, $parentPage, $section) {
        $breadcrumb = [];
        if ($articleId) {
            $breadcrumb[] = [Request::url(null, $section, 'submission', $articleId), "#$articleId", true];
        }

        if ($parentPage) {
            $parent = []; // [LUMERA FIX] Initialize to prevent undefined variable warning
            switch ($parentPage) {
                case 'summary':
                    $parent = [Request::url(null, $section, 'submission', $articleId), 'submission.summary'];
                    break;
                case 'review':
                    $parent = [Request::url(null, $section, 'submissionReview', $articleId), 'submission.review'];
                    break;
                case 'editing':
                    $parent = [Request::url(null, $section, 'submissionEditing', $articleId), 'submission.editing'];
                    break;
                case 'history':
                    $parent = [Request::url(null, $section, 'submissionHistory', $articleId), 'submission.history'];
                    break;
            }
            if ($section !== 'editor' && $section !== 'sectionEditor') {
                $parent[0] = Request::url(null, $section, 'submission', $articleId);
            }
            $breadcrumb[] = $parent;
        }
        return $breadcrumb;
    }

}
?>