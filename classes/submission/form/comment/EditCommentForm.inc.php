<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/comment/EditCommentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditCommentForm
 * @ingroup submission_form
 *
 * @brief Edit comment form.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class EditCommentForm extends Form {

    /** @var object|null the article */
    public $article = null;

    /** @var ArticleComment|null the comment */
    public $comment = null;

    /** @var int|null the role of the comment author */
    public $roleId = null;

    /** @var User|null the user */
    public $user = null;

    /**
     * Constructor.
     * @param object $article
     * @param object $comment
     */
    public function __construct($article, $comment) {
        parent::__construct('submission/comment/editComment.tpl');
        $this->addCheck(new FormValidatorPost($this));

        $this->comment = $comment;
        $this->roleId = $comment->getRoleId();

        $this->article = $article;
        
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $this->user = $request->getUser();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditCommentForm($article, $comment) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Initialize form data from current comment.
     */
    public function initData() {
        $comment = $this->comment;
        $this->_data = [
            'commentId' => $comment->getId(),
            'commentTitle' => $comment->getCommentTitle(),
            'comments' => $comment->getComments(),
            'viewable' => $comment->getViewable(),
        ];
    }    

    /**
     * Display the form.
     * @param array|null $additionalHiddenParams
     * @param object|null $request
     */
    public function display($additionalHiddenParams = null, $request = null) {
        $hiddenFormParams = [
            'articleId' => $this->article->getId(),
            'commentId' => $this->comment->getCommentId()
        ];
        if (isset($additionalHiddenParams)) {
            $hiddenFormParams = array_merge($hiddenFormParams, $additionalHiddenParams);
        }

        $templateMgr = TemplateManager::getManager();

        $isPeerReviewComment = $this->comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW;
        $templateMgr->assign('isPeerReviewComment', $isPeerReviewComment); // FIXME
        $templateMgr->assign('comment', $this->comment);
        $templateMgr->assign('hiddenFormParams', $hiddenFormParams);

        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'commentTitle',
                'comments',
                'viewable'
            ]
        );
    }

    /**
     * Update the comment.
     */
    public function execute($object = NULL) {
        $commentDao = DAORegistry::getDAO('ArticleCommentDAO');

        // Update comment        
        $comment = $this->comment;
        $comment->setCommentTitle($this->getData('commentTitle'));
        $comment->setComments($this->getData('comments'));
        $comment->setViewable($this->getData('viewable') ? 1 : 0);
        $comment->setDateModified(Core::getCurrentDate());

        $commentDao->updateArticleComment($comment);
    }

    /**
     * UGLEEE function that gets the recipients for a comment.
     * @return array recipients (email address => name)
     */
    public function emailHelper() {
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        $recipients = [];

        // Get editors for article
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditAssignmentsByArticleId($this->article->getId());
        $editAssignmentsArray = $editAssignments->toArray();
        $editorAddresses = [];
        foreach ($editAssignmentsArray as $editAssignment) {
            $editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
        }

        // If no editors are currently assigned, send this message to
        // all of the journal's editors.
        if (empty($editorAddresses)) {
            $editors = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journal->getId());
            while (!$editors->eof()) {
                $editor = $editors->next();
                $editorAddresses[$editor->getEmail()] = $editor->getFullName();
            }
        }

        // Get proofreader
        $proofSignoff = $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $this->article->getId());
        if ($proofSignoff != null && $proofSignoff->getUserId() > 0) {
            $proofreader = $userDao->getUser($proofSignoff->getUserId());
        } else {
            $proofreader = null;
        }

        // Get layout editor
        $layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $this->article->getId());
        if ($layoutSignoff != null && $layoutSignoff->getUserId() > 0) {
            $layoutEditor = $userDao->getUser($layoutSignoff->getUserId());
        } else {
            $layoutEditor = null;
        }

        // Get copyeditor
        $copySignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $this->article->getId());
        if ($copySignoff != null && $copySignoff->getUserId() > 0) {
            $copyeditor = $userDao->getUser($copySignoff->getUserId());
        } else {
            $copyeditor = null;
        }

        // Get reviewer
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($this->comment->getAssocId());
        if ($reviewAssignment != null && $reviewAssignment->getReviewerId() != null) {
            $reviewer = $userDao->getUser($reviewAssignment->getReviewerId());
        } else {
            $reviewer = null;
        }

        // Get author
        $author = $userDao->getUser($this->article->getUserId());

        switch ($this->comment->getCommentType()) {
        case COMMENT_TYPE_PEER_REVIEW:
            if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
                // Then add reviewer
                if ($reviewer != null) {
                    $recipients = array_merge($recipients, [$reviewer->getEmail() => $reviewer->getFullName()]);
                }
            }
            break;

        case COMMENT_TYPE_EDITOR_DECISION:
            if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
                // Then add author
                if (isset($author)) $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);
            } else {
                // Then add editors
                $recipients = array_merge($recipients, $editorAddresses);
            }
            break;

        case COMMENT_TYPE_COPYEDIT:
            if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
                // Then add copyeditor and author
                if ($copyeditor != null) {
                    $recipients = array_merge($recipients, [$copyeditor->getEmail() => $copyeditor->getFullName()]);
                }

                $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);

            } elseif ($this->roleId == ROLE_ID_COPYEDITOR) {
                // Then add editors and author
                $recipients = array_merge($recipients, $editorAddresses);

                if (isset($author)) $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);

            } else {
                // Then add editors and copyeditor
                $recipients = array_merge($recipients, $editorAddresses);

                if ($copyeditor != null) {
                    $recipients = array_merge($recipients, [$copyeditor->getEmail() => $copyeditor->getFullName()]);
                }
            }
            break;
        case COMMENT_TYPE_LAYOUT:
            if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
                // Then add layout editor

                // Check to ensure that there is a layout editor assigned to this article.
                if ($layoutEditor != null) {
                    $recipients = array_merge($recipients, [$layoutEditor->getEmail() => $layoutEditor->getFullName()]);
                }
            } else {
                // Then add editors
                $recipients = array_merge($recipients, $editorAddresses);
            }
            break;
        case COMMENT_TYPE_PROOFREAD:
            if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
                // Then add layout editor, proofreader and author
                if ($layoutEditor != null) {
                    $recipients = array_merge($recipients, [$layoutEditor->getEmail() => $layoutEditor->getFullName()]);
                }

                if ($proofreader != null) {
                    $recipients = array_merge($recipients, [$proofreader->getEmail() => $proofreader->getFullName()]);
                }

                if (isset($author)) $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);

            } elseif ($this->roleId == ROLE_ID_LAYOUT_EDITOR) {
                // Then add editors, proofreader and author
                $recipients = array_merge($recipients, $editorAddresses);

                if ($proofreader != null) {
                    $recipients = array_merge($recipients, [$proofreader->getEmail() => $proofreader->getFullName()]);
                }

                if (isset($author)) $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);

            } elseif ($this->roleId == ROLE_ID_PROOFREADER) {
                // Then add editors, layout editor, and author
                $recipients = array_merge($recipients, $editorAddresses);

                if ($layoutEditor != null) {
                    $recipients = array_merge($recipients, [$layoutEditor->getEmail() => $layoutEditor->getFullName()]);
                }

                if (isset($author)) $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);

            } else {
                // Then add editors, layout editor, and proofreader
                $recipients = array_merge($recipients, $editorAddresses);

                if ($layoutEditor != null) {
                    $recipients = array_merge($recipients, [$layoutEditor->getEmail() => $layoutEditor->getFullName()]);
                }

                if ($proofreader != null) {
                    $recipients = array_merge($recipients, [$proofreader->getEmail() => $proofreader->getFullName()]);
                }
            }
            break;
        }

        return $recipients;
    }

    /**
     * Email the comment.
     * @param array $recipients array of recipients (email address => name)
     * @param object $request PKPRequest
     */
    public function email($recipients, $request) {
        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($this->article, 'SUBMISSION_COMMENT');
        $journal = $request->getJournal();
        if ($journal) $email->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));

        foreach ($recipients as $emailAddress => $name) {
            $email->addRecipient($emailAddress, $name);
            $email->setSubject(strip_tags($this->article->getLocalizedTitle()));

            $paramArray = [
                'name' => $name,
                'commentName' => $this->user->getFullName(),
                'comments' => PKPString::html2text($this->getData('comments'))
            ];
            $email->assignParams($paramArray);

            $email->send($request);
            $email->clearRecipients();
        }
    }
}
?>