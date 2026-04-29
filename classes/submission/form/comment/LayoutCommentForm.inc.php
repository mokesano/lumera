<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/comment/LayoutCommentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LayoutCommentForm
 * @ingroup submission_form
 *
 * @brief LayoutComment form.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.submission.form.comment.CommentForm');

class LayoutCommentForm extends CommentForm {

    /**
     * Constructor.
     * @param object $article Article
     * @param int $roleId
     */
    public function __construct($article, $roleId) {
        parent::__construct($article, COMMENT_TYPE_LAYOUT, $roleId, $article->getId());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LayoutCommentForm($article, $roleId) {
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
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageTitle', 'submission.comments.comments');
        $templateMgr->assign('commentAction', 'postLayoutComment');
        $templateMgr->assign('commentType', 'layout');
        $templateMgr->assign('hiddenFormParams', 
            [
                'articleId' => $this->article->getId()
            ]
        );

        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        parent::readInputData();
    }

    /**
     * Add the comment.
     */
    public function execute($object = NULL) {
        parent::execute($object = NULL);
    }

    /**
     * Email the comment.
     * [WIZDAM] Signature adjusted to match Parent::email($recipients, $request)
     * Legacy calls passing only ($request) are handled via type detection.
     * @param mixed $recipients (Legacy: PKPRequest object)
     * @param mixed $request (Legacy: null)
     */
    public function email($recipients, $request = null) {
        // [WIZDAM] Polyfill for legacy signature mismatch
        // Original: email($request)
        // Parent: email($recipients, $request)
        if ($recipients instanceof PKPRequest && $request === null) {
            $request = $recipients;
            $recipients = []; // Will be populated below
        }

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();

        // Create list of recipients:

        // Layout comments are to be sent to the editor or layout editor;
        // the opposite of whomever posted the comment.
        $recipients = [];

        if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
            // Then add layout editor
            $signoffDao = DAORegistry::getDAO('SignoffDAO');
            $layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $this->article->getId());

            // Check to ensure that there is a layout editor assigned to this article.
            if ($layoutSignoff != null && $layoutSignoff->getUserId() > 0) {
                $user = $userDao->getUser($layoutSignoff->getUserId());

                if ($user) {
                    $recipients = array_merge($recipients, [$user->getEmail() => $user->getFullName()]);
                }
            }
        } else {
            // Then add editor
            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $editAssignments = $editAssignmentDao->getEditAssignmentsByArticleId($this->article->getId());
            $editorAddresses = [];
            while (!$editAssignments->eof()) {
                $editAssignment = $editAssignments->next();
                if ($editAssignment->getCanEdit()) {
                    $editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
                }
            }

            // If no editors are currently assigned to this article,
            // send the email to all editors for the journal
            if (empty($editorAddresses)) {
                $editors = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journal->getId());
                while (!$editors->eof()) {
                    $editor = $editors->next();
                    $editorAddresses[$editor->getEmail()] = $editor->getFullName();
                }
            }
            $recipients = array_merge($recipients, $editorAddresses);
        }

        parent::email($recipients, $request);
    }
}
?>