<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/form/ObjectForReviewAssignmentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewAssignmentForm
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReviewAssignment
 *
 * @brief Object for review assignment form.
 * [WIZDAM] MODERNIZED FOR PHP 8.x compatibility.
 */

import('lib.pkp.classes.form.Form');

class ObjectForReviewAssignmentForm extends Form {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /** @var int ID of the object for review assignment */
    public $assignmentId;

    /** @var int ID of the object for review assignment */
    public $objectId;

    /**
     * Constructor
     */
    public function __construct($parentPluginName, $assignmentId, $objectId) {
        $this->parentPluginName = $parentPluginName;
        $this->assignmentId = (int) $assignmentId;
        $this->objectId = (int) $objectId;

        $ofrPlugin = PluginRegistry::getPlugin('generic', $parentPluginName);
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/objectForReviewAssignmentForm.tpl');

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectForReviewAssignmentForm($parentPluginName, $assignmentId, $objectId) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectForReviewAssignmentForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($parentPluginName, $assignmentId, $objectId);
    }

    /**
     * Display the form
     * @see Form::display()
     */
    public function display($request = null, $template = null) {
        // get the assignment
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($this->assignmentId, $this->objectId);
        // get the object for review
        $objectForReview = $ofrAssignment->getObjectForReview();
        // get the reviewer
        $reviewer = $ofrAssignment->getUser();

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();

        // If there is a submission, get date submitted
        $dateSubmitted = null;
        if ($ofrAssignment->getSubmissionId()) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($ofrAssignment->getSubmissionId(), $journalId);
            $dateSubmitted = $article->getDateSubmitted();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('objectForReviewAssignment', $ofrAssignment);
        $templateMgr->assign('objectForReview', $objectForReview);
        $templateMgr->assign('reviewer', $reviewer);
        $templateMgr->assign('dateSubmitted', $dateSubmitted);
        $templateMgr->assign('countries', $countries);
        parent::display($request, $template);
    }

    /**
     * Read user input
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(
            array(
                'dateDueYear',
                'dateDueMonth',
                'dateDueDay',
                'notes'
            )
        );
        // Format the date
        if (!empty($this->_data['dateDueYear']) && !empty($this->_data['dateDueMonth']) && !empty($this->_data['dateDueDay'])) {
            $this->_data['dateDue'] = $this->_data['dateDueYear'] . '-' . $this->_data['dateDueMonth'] . '-' . $this->_data['dateDueDay'] . ' 00:00:00';
        } else {
            $this->_data['dateDue'] = '';
        }
    }

    /**
     * Save the changes to the object for review assignment
     * @see Form::execute()
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $ofrPlugin->import('classes.ObjectForReviewAssignment');

        $journal = Request::getJournal();
        $journalId = $journal->getId();

        $ofrAssignemntDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignemntDao->getById($this->assignmentId, $this->objectId);
        if (isset($ofrAssignment)) {
            if ($this->getData('dateDue') != $ofrAssignment->getDateDue()) {
                $ofrAssignment->setDateDue($this->getData('dateDue'));
                $ofrAssignment->setDateRemindedBefore(null);
                $ofrAssignment->setDateRemindedAfter(null);
            }
            $ofrAssignment->setNotes($this->getData('notes'));
            $ofrAssignemntDao->updateObject($ofrAssignment);
        }
    }
}
?>