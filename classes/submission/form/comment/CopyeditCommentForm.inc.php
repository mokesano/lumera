<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/comment/CopyeditCommentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditCommentForm
 * @ingroup submission_form
 * @see Form
 *
 * @brief CopyeditComment form.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.submission.form.comment.CommentForm');

class CopyeditCommentForm extends CommentForm {

    /**
     * Constructor.
     * @param object $article Article
     * @param int $roleId
     */
    public function __construct($article, $roleId) {
        parent::__construct($article, COMMENT_TYPE_COPYEDIT, $roleId, $article->getId());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CopyeditCommentForm($article, $roleId) {
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
        $article = $this->article;

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageTitle', 'submission.comments.copyeditComments');
        $templateMgr->assign('commentAction', 'postCopyeditComment');
        $templateMgr->assign('commentType', 'copyedit');
        $templateMgr->assign('hiddenFormParams', 
            [
                'articleId' => $article->getId()
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

        $article = $this->article;
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();

        // Create list of recipients:
        $recipients = [];

        // Copyedit comments are to be sent to the editor, author, and copyeditor,
        // excluding whomever posted the comment.

        // Get editors
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignments = $editAssignmentDao->getEditAssignmentsByArticleId($article->getId());
        $editAssignmentsArray = $editAssignments->toArray();
        $editorAddresses = [];
        foreach ($editAssignmentsArray as $editAssignment) {
            if ($editAssignment->getCanEdit()) {
                $editorAddresses[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
            }
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

        // Get copyeditor
        $copySignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $article->getId());
        if ($copySignoff != null && $copySignoff->getUserId() > 0) {
            $copyeditor = $userDao->getUser($copySignoff->getUserId());
        } else {
            $copyeditor = null;
        }

        // Get author
        $author = $userDao->getUser($article->getUserId());

        // Choose who receives this email
        if ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) {
            // Then add copyeditor and author
            if ($copyeditor != null) {
                $recipients = array_merge($recipients, [$copyeditor->getEmail() => $copyeditor->getFullName()]);
            }

            $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);

        } elseif ($this->roleId == ROLE_ID_COPYEDITOR) {
            // Then add editors and author
            $recipients = array_merge($recipients, $editorAddresses);

            if (isset($author)) {
                $recipients = array_merge($recipients, [$author->getEmail() => $author->getFullName()]);
            }

        } else {
            // Then add editors and copyeditor
            $recipients = array_merge($recipients, $editorAddresses);

            if ($copyeditor != null) {
                $recipients = array_merge($recipients, [$copyeditor->getEmail() => $copyeditor->getFullName()]);
            }
        }

        parent::email($recipients, $request);
    }
}
?>