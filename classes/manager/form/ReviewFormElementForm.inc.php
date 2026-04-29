<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/ReviewFormElementForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormElementForm
 * @ingroup manager_form
 * @see ReviewFormElement
 *
 * @brief Form for creating and modifying review form elements.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.reviewForm.ReviewFormElement');

class ReviewFormElementForm extends Form {

    /** @var int The ID of the review form being edited */
    public $reviewFormId;

    /** @var int|null The ID of the review form element being edited */
    public $reviewFormElementId;

    /**
     * Constructor.
     * @param int $reviewFormId
     * @param int|null $reviewFormElementId
     */
    public function __construct($reviewFormId, $reviewFormElementId = null) {
        parent::__construct('manager/reviewForms/reviewFormElementForm.tpl');

        $this->reviewFormId = (int) $reviewFormId;
        $this->reviewFormElementId = $reviewFormElementId ? (int) $reviewFormElementId : null;

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'question', 'required', 'manager.reviewFormElements.form.questionRequired'));
        $this->addCheck(new FormValidator($this, 'elementType', 'required', 'manager.reviewFormElements.form.elementTypeRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewFormElementForm($reviewFormId, $reviewFormElementId = null) {
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
     * Get the names of fields for which localized data is allowed.
     * @return array
     */
    public function getLocaleFieldNames() {
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        return $reviewFormElementDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('reviewFormId', $this->reviewFormId);
        $templateMgr->assign('reviewFormElementId', $this->reviewFormElementId);
        
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('multipleResponsesElementTypes', ReviewFormElement::getMultipleResponsesElementTypes());
        
        // in order to be able to search for an element in the array in the javascript function 'togglePossibleResponses':
        $templateMgr->assign('multipleResponsesElementTypesString', ';' . implode(';', ReviewFormElement::getMultipleResponsesElementTypes()) . ';');
        
        $templateMgr->assign('reviewFormElementTypeOptions', ReviewFormElement::getReviewFormElementTypeOptions());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.reviewForms');
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current review form.
     */
    public function initData() {
        if ($this->reviewFormElementId != null) {
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            $reviewFormElement = $reviewFormElementDao->getReviewFormElement($this->reviewFormElementId);

            if ($reviewFormElement == null) {
                $this->reviewFormElementId = null;
                $this->_data = [
                    'included' => 1
                ];
            } else {
                $this->_data = [
                    'question' => $reviewFormElement->getQuestion(null), // Localized
                    'required' => $reviewFormElement->getRequired(),
                    'included' => $reviewFormElement->getIncluded(),
                    'elementType' => $reviewFormElement->getElementType(),
                    'possibleResponses' => $reviewFormElement->getPossibleResponses(null) //Localized
                ];
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['question', 'required', 'included', 'elementType', 'possibleResponses']);
    }

    /**
     * Save review form element.
     */
    public function execute() {
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');

        if ($this->reviewFormElementId != null) {
            $reviewFormElement = $reviewFormElementDao->getReviewFormElement($this->reviewFormElementId);
        }

        if (!isset($reviewFormElement)) {
            $reviewFormElement = new ReviewFormElement();
            $reviewFormElement->setReviewFormId($this->reviewFormId);
            $reviewFormElement->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
        }

        $reviewFormElement->setQuestion($this->getData('question'), null); // Localized
        $reviewFormElement->setRequired($this->getData('required') ? 1 : 0);
        $reviewFormElement->setIncluded($this->getData('included') ? 1 : 0);
        $reviewFormElement->setElementType($this->getData('elementType'));

        if (in_array($this->getData('elementType'), ReviewFormElement::getMultipleResponsesElementTypes())) {
            $reviewFormElement->setPossibleResponses($this->getData('possibleResponses'), null); // Localized
        } else {
            $reviewFormElement->setPossibleResponses(null, null);
        }

        if ($reviewFormElement->getId() != null) {
            $reviewFormElementDao->deleteSetting($reviewFormElement->getId(), 'possibleResponses');
            $reviewFormElementDao->updateObject($reviewFormElement);
            $this->reviewFormElementId = $reviewFormElement->getId();
        } else {
            $this->reviewFormElementId = $reviewFormElementDao->insertObject($reviewFormElement);
            $reviewFormElementDao->resequenceReviewFormElements($this->reviewFormId);
        }
    }
}
?>