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
 */

import('lib.pkp.classes.form.Form');

class ObjectForReviewAssignmentForm extends Form {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /** @var int ID of the object for review assignment */
    protected $_assignmentId;

    /** @var int ID of the object for review */
    protected $_objectId;

    /**
     * Constructor.
     * @param string $parentPluginName
     * @param int $assignmentId
     * @param int $objectId
     */
    public function __construct($parentPluginName, $assignmentId, $objectId) {
        $this->_parentPluginName = (string) $parentPluginName;
        $this->_assignmentId = (int) $assignmentId;
        $this->_objectId = (int) $objectId;

        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/objectForReviewAssignmentForm.tpl');

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     * @param int $assignmentId
     * @param int $objectId
     */
    public function ObjectForReviewAssignmentForm($parentPluginName, $assignmentId, $objectId) {
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
     * Display the form.
     * @see Form::display()
     * @param mixed $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($this->_assignmentId, $this->_objectId);
        
        if (!$ofrAssignment) {
            return;
        }

        // Get the object for review
        $objectForReview = $ofrAssignment->getObjectForReview();
        // Get the reviewer
        $reviewer = $ofrAssignment->getUser();

        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        $journalId = $journal ? (int) $journal->getId() : 0;

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();

        // If there is a submission, get date submitted
        $dateSubmitted = null;
        if ($ofrAssignment->getSubmissionId()) {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle((int) $ofrAssignment->getSubmissionId(), $journalId);
            if ($article) {
                $dateSubmitted = $article->getDateSubmitted();
            }
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
     * Read user input.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'dateDueYear',
            'dateDueMonth',
            'dateDueDay',
            'notes'
        ]);
        
        // Format the date
        if (!empty($this->_data['dateDueYear']) && !empty($this->_data['dateDueMonth']) && !empty($this->_data['dateDueDay'])) {
            $this->_data['dateDue'] = (string) $this->_data['dateDueYear'] . '-' . (string) $this->_data['dateDueMonth'] . '-' . (string) $this->_data['dateDueDay'] . ' 00:00:00';
        } else {
            $this->_data['dateDue'] = '';
        }
    }

    /**
     * Save the changes to the object for review assignment.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ObjectForReviewAssignment');

        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        $journalId = $journal ? (int) $journal->getId() : 0;

        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($this->_assignmentId, $this->_objectId);
        
        if ($ofrAssignment !== null) {
            $dateDue = (string) $this->getData('dateDue');
            if ($dateDue !== (string) $ofrAssignment->getDateDue()) {
                $ofrAssignment->setDateDue($dateDue);
                $ofrAssignment->setDateRemindedBefore(null);
                $ofrAssignment->setDateRemindedAfter(null);
            }
            $ofrAssignment->setNotes((string) $this->getData('notes'));
            $ofrAssignmentDao->updateObject($ofrAssignment);
        }
    }
    
}
?>