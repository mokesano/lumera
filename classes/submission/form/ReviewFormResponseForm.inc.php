<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/ReviewFormResponseForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormResponseForm
 * @ingroup submission_form
 * @see ReviewFormResponse
 *
 * @brief Peer review form response form.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class ReviewFormResponseForm extends Form {

    /** @var int|null the ID of the review */
    public $reviewId = null;

    /** @var int|null the ID of the review form */
    public $reviewFormId = null;

    /**
     * Constructor.
     * @param int $reviewId
     * @param int $reviewFormId
     */
    public function __construct($reviewId, $reviewFormId) {
        parent::__construct('submission/reviewForm/reviewFormResponse.tpl');

        $this->reviewId = (int) $reviewId;
        $this->reviewFormId = (int) $reviewFormId;

        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        $requiredReviewFormElementIds = $reviewFormElementDao->getRequiredReviewFormElementIds($this->reviewFormId);

        // [WIZDAM] Replaced create_function with Anonymous Function
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'reviewFormResponses', 
            'required', 
            'reviewer.article.reviewFormResponse.form.responseRequired', 
            function($reviewFormResponses, $requiredReviewFormElementIds) {
                foreach ($requiredReviewFormElementIds as $requiredReviewFormElementId) { 
                    if (!isset($reviewFormResponses[$requiredReviewFormElementId]) || $reviewFormResponses[$requiredReviewFormElementId] == '') return false; 
                } 
                return true;
            }, 
            [$requiredReviewFormElementIds]
        ));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewFormResponseForm($reviewId, $reviewFormId) {
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
        // [WIZDAM] Singleton Request
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewForm = $reviewFormDao->getReviewForm($this->reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        $reviewFormElements = $reviewFormElementDao->getReviewFormElements($this->reviewFormId);
        $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
        $reviewFormResponses = $reviewFormResponseDao->getReviewReviewFormResponseValues($this->reviewId);
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($this->reviewId);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageTitle', 'submission.reviewFormResponse');
        $templateMgr->assign('reviewForm', $reviewForm);
        $templateMgr->assign('reviewFormElements', $reviewFormElements);
        $templateMgr->assign('reviewFormResponses', $reviewFormResponses);
        $templateMgr->assign('reviewId', $this->reviewId);
        $templateMgr->assign('articleId', $reviewAssignment->getSubmissionId());
        $templateMgr->assign('isLocked', isset($reviewAssignment) && $reviewAssignment->getDateCompleted() != null);
        $templateMgr->assign('editorPreview', $request->getRequestedPage() != 'reviewer');

        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'reviewFormResponses'
            ]
        );
    }

    /**
     * Save the response.
     */
    public function execute() {
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($this->reviewId);
        $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

        $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');

        $reviewFormResponses = $this->getData('reviewFormResponses');
        if (is_array($reviewFormResponses)) foreach ($reviewFormResponses as $reviewFormElementId => $reviewFormResponseValue) {
            $reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($this->reviewId, $reviewFormElementId);
            if (!isset($reviewFormResponse)) {
                $reviewFormResponse = new ReviewFormResponse();
            }
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            $reviewFormElement = $reviewFormElementDao->getReviewFormElement($reviewFormElementId);
            $elementType = $reviewFormElement->getElementType();
            switch ($elementType) {
                case REVIEW_FORM_ELEMENT_TYPE_SMALL_TEXT_FIELD:
                case REVIEW_FORM_ELEMENT_TYPE_TEXT_FIELD:
                case REVIEW_FORM_ELEMENT_TYPE_TEXTAREA:
                    $reviewFormResponse->setResponseType('string');
                    $reviewFormResponse->setValue($reviewFormResponseValue);
                    break;
                case REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS:
                case REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX:
                    $reviewFormResponse->setResponseType('int');
                    $reviewFormResponse->setValue($reviewFormResponseValue);
                    break;
                case REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES:
                    $reviewFormResponse->setResponseType('object');
                    $reviewFormResponse->setValue($reviewFormResponseValue);
                    break;
            }
            if ($reviewFormResponse->getReviewFormElementId() != null && $reviewFormResponse->getReviewId() != null) {
                $reviewFormResponseDao->updateObject($reviewFormResponse);
            } else {
                $reviewFormResponse->setReviewFormElementId($reviewFormElementId);
                $reviewFormResponse->setReviewId($this->reviewId);
                $reviewFormResponseDao->insertObject($reviewFormResponse);
            }
        }
    }
}
?>