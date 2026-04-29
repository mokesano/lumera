<?php
declare(strict_types=1);

/**
 * @file classes/handler/HandlerValidatorSubmissionComment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidatorSubmissionComment
 * @ingroup handler_validation
 *
 * @brief Class to validate that a comment exists (by id) and that the current user has access
 */

import('lib.pkp.classes.handler.validation.HandlerValidator');

class HandlerValidatorSubmissionComment extends HandlerValidator {
    /** @var int */
    public $commentId;

    /** @var PKPUser */
    public $user;

    /**
     * Constructor.
     * @param $handler Handler the associated form
     * @param $commentId int
     * @param $user object Optional user
     */
    public function __construct($handler, $commentId, $user = null) {
        parent::__construct($handler);

        $this->commentId = (int) $commentId;
        if ($user) {
            $this->user = $user;
        } else {
            $this->user = Request::getUser();
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HandlerValidatorSubmissionComment($handler, $commentId, $user = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HandlerValidatorSubmissionComment(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($handler, $commentId, $user);
    }

    /**
     * Check if field value is valid.
     * Value is valid if it is empty and optional or validated by user-supplied function.
     * @return boolean
     */
    public function isValid() {
        $isValid = true;

        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($this->commentId);

        if ($comment == null) {
            $isValid = false;
        } elseif (!$this->user || $comment->getAuthorId() != $this->user->getId()) {
            // Added check for $this->user existence to prevent Fatal Error on getId()
            $isValid = false;
        }

        if (!$isValid) {
            Request::redirect(null, Request::getRequestedPage());
            return false;
        }
        
        // Inject comment into handler (Side effect intended by original design)
        $this->handler->comment = $comment;        
        return true;
    }
}

?>