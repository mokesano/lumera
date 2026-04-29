<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/ReviewFormForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewFormForm
 * @ingroup manager_form
 * @see ReviewForm
 *
 * @brief Form for creating and modifying review forms.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class ReviewFormForm extends Form {

    /** @var int|null The ID of the review form being edited */
    public $reviewFormId = null;

    /**
     * Constructor.
     * @param int|null $reviewFormId
     */
    public function __construct($reviewFormId = null) {
        parent::__construct('manager/reviewForms/reviewFormForm.tpl');

        $this->reviewFormId = $reviewFormId ? (int) $reviewFormId : null;

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.reviewForms.form.titleRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewFormForm($reviewFormId = null) {
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
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        return $reviewFormDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('reviewFormId', $this->reviewFormId);
        $templateMgr->assign('helpTopicId', 'journal.managementPages.reviewForms');
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current review form.
     */
    public function initData() {
        if ($this->reviewFormId != null) {
            $journal = Application::get()->getRequest()->getJournal();
            $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
            $reviewForm = $reviewFormDao->getReviewForm($this->reviewFormId, ASSOC_TYPE_JOURNAL, $journal->getId());

            if ($reviewForm == null) {
                $this->reviewFormId = null;
            } else {
                $this->_data = [
                    'title' => $reviewForm->getTitle(null), // Localized
                    'description' => $reviewForm->getDescription(null) // Localized
                ];
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['title', 'description']);
    }

    /**
     * Save review form.
     */
    public function execute($object = NULL) {
        $journal = Application::get()->getRequest()->getJournal();
        $journalId = $journal->getId();

        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');

        if ($this->reviewFormId != null) {
            $reviewForm = $reviewFormDao->getReviewForm($this->reviewFormId, ASSOC_TYPE_JOURNAL, $journalId);
        }

        if (!isset($reviewForm)) {
            $reviewForm = $reviewFormDao->newDataObject();
            $reviewForm->setAssocType(ASSOC_TYPE_JOURNAL);
            $reviewForm->setAssocId($journalId);
            $reviewForm->setActive(0);
            $reviewForm->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
        }

        $reviewForm->setTitle($this->getData('title'), null); // Localized
        $reviewForm->setDescription($this->getData('description'), null); // Localized

        if ($reviewForm->getId() != null) {
            $reviewFormDao->updateObject($reviewForm);
            // $reviewFormId = $reviewForm->getId(); // Unused variable
        } else {
            // $reviewFormId = ... // Unused variable
            $reviewFormDao->insertObject($reviewForm);
            $reviewFormDao->resequenceReviewForms(ASSOC_TYPE_JOURNAL, $journalId);
        }
    }
}
?>