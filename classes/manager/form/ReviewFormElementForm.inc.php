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
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.reviewForm.ReviewFormElement');

class ReviewFormElementForm extends Form {

    /** @var int The ID of the review form being edited */
    protected $_reviewFormId;

    /** @var int|null The ID of the review form element being edited */
    protected $_reviewFormElementId;

    /**
     * Constructor.
     * @param int $reviewFormId
     * @param int|null $reviewFormElementId
     */
    public function __construct($reviewFormId, $reviewFormElementId = null) {
        parent::__construct('manager/reviewForms/reviewFormElementForm.tpl');

        $this->_reviewFormId = (int) $reviewFormId;
        $this->_reviewFormElementId = $reviewFormElementId !== null ? (int) $reviewFormElementId : null;

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'question', 'required', 'manager.reviewFormElements.form.questionRequired'));
        $this->addCheck(new FormValidator($this, 'elementType', 'required', 'manager.reviewFormElements.form.elementTypeRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param int $reviewFormId
     * @param int|null $reviewFormElementId
     */
    public function ReviewFormElementForm($reviewFormId, $reviewFormElementId = null) {
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
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('reviewFormId', $this->_reviewFormId);
        $templateMgr->assign('reviewFormElementId', $this->_reviewFormElementId);
        
        // [WIZDAM FIX] Instantiate ReviewFormElement to call non-static methods safely
        $reviewFormElement = new ReviewFormElement();
        $multipleResponsesElementTypes = $reviewFormElement->getMultipleResponsesElementTypes();
        
        $templateMgr->assign('multipleResponsesElementTypes', $multipleResponsesElementTypes);
        $templateMgr->assign('multipleResponsesElementTypesString', ';' . implode(';', $multipleResponsesElementTypes) . ';');
        $templateMgr->assign('reviewFormElementTypeOptions', $reviewFormElement->getReviewFormElementTypeOptions());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.reviewForms');
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current review form.
     * @return void
     */
    public function initData() {
        if ($this->_reviewFormElementId !== null) {
            /** @var ReviewFormElementDAO $reviewFormElementDao */
            $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
            $reviewFormElement = $reviewFormElementDao->getReviewFormElement($this->_reviewFormElementId);

            if ($reviewFormElement === null) {
                $this->_reviewFormElementId = null;
                $this->_data = [
                    'included' => 1
                ];
            } else {
                $this->_data = [
                    'question' => $reviewFormElement->getQuestion(null), // Localized
                    'required' => (int) $reviewFormElement->getRequired(),
                    'included' => (int) $reviewFormElement->getIncluded(),
                    'elementType' => (string) $reviewFormElement->getElementType(),
                    'possibleResponses' => $reviewFormElement->getPossibleResponses(null) // Localized
                ];
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['question', 'required', 'included', 'elementType', 'possibleResponses']);
    }

    /**
     * Save review form element.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        /** @var ReviewFormElementDAO $reviewFormElementDao */
        $reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');
        
        $reviewFormElement = null;
        if ($this->_reviewFormElementId !== null) {
            $reviewFormElement = $reviewFormElementDao->getReviewFormElement($this->_reviewFormElementId);
        }

        if ($reviewFormElement === null) {
            $reviewFormElement = new ReviewFormElement();
            $reviewFormElement->setReviewFormId($this->_reviewFormId);
            $reviewFormElement->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
        }

        $reviewFormElement->setQuestion($this->getData('question'), null); // Localized
        $reviewFormElement->setRequired((int) $this->getData('required'));
        $reviewFormElement->setIncluded((int) $this->getData('included'));
        $reviewFormElement->setElementType((string) $this->getData('elementType'));

        // [WIZDAM FIX] Instantiate ReviewFormElement to call non-static methods safely
        $tempElement = new ReviewFormElement();
        if (in_array($this->getData('elementType'), $tempElement->getMultipleResponsesElementTypes(), true)) {
            $reviewFormElement->setPossibleResponses($this->getData('possibleResponses'), null); // Localized
        } else {
            $reviewFormElement->setPossibleResponses(null, null);
        }

        if ($reviewFormElement->getId() !== null) {
            $reviewFormElementDao->deleteSetting((int) $reviewFormElement->getId(), 'possibleResponses');
            $reviewFormElementDao->updateObject($reviewFormElement);
            $this->_reviewFormElementId = (int) $reviewFormElement->getId();
        } else {
            $this->_reviewFormElementId = (int) $reviewFormElementDao->insertObject($reviewFormElement);
            $reviewFormElementDao->resequenceReviewFormElements($this->_reviewFormId);
        }
    }

}
?>