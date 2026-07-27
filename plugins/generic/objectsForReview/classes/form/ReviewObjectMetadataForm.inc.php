<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/form/ReviewObjectMetadataForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectMetadataForm
 * @ingroup plugins_generic_objectsForReview
 * @see ReviewObjectMetadata
 *
 * @brief Form for creating and modifying review object metadata.
 */

import('lib.pkp.classes.form.Form');

class ReviewObjectMetadataForm extends Form {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /** @var int ID of the ReviewObjectType being edited */
    protected $_reviewObjectTypeId;

    /** @var ReviewObjectMetadata|null ReviewObjectMetadata being edited */
    protected $_reviewObjectMetadata;

    /**
     * Constructor.
     * @param string $parentPluginName
     * @param int $reviewObjectTypeId
     * @param int|null $metadataId
     */
    public function __construct($parentPluginName, $reviewObjectTypeId, $metadataId = null) {
        $this->_parentPluginName = (string) $parentPluginName;
        $this->_reviewObjectTypeId = (int) $reviewObjectTypeId;

        // [SCHOLARWIZDAM LUMERA STANDARD] Context resolution via Router
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        $journalId = $journal ? (int) $journal->getId() : 0;

        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        
        if ($metadataId !== null) {
            $this->_reviewObjectMetadata = $reviewObjectMetadataDao->getById((int) $metadataId, $this->_reviewObjectTypeId);
        } else {
            $this->_reviewObjectMetadata = null;
        }
        
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/reviewObjectMetadataForm.tpl');

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'plugins.generic.objectsForReview.editor.objectMetadata.form.nameRequired'));
        $this->addCheck(new FormValidator($this, 'metadataType', 'required', 'plugins.generic.objectsForReview.editor.objectMetadata.form.typeRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     * @param int $reviewObjectTypeId
     * @param int|null $metadataId
     */
    public function ReviewObjectMetadataForm($parentPluginName, $reviewObjectTypeId, $metadataId = null) {
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
     * Get the names of the fields that are localized.
     * @see Form::getLocaleFieldNames()
     * @return array
     */
    public function getLocaleFieldNames(): array {
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        return $reviewObjectMetadataDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param mixed $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('reviewObjectMetadata', $this->_reviewObjectMetadata);
        $templateMgr->assign('reviewObjectTypeId', $this->_reviewObjectTypeId);

        $ofrPlugin = PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        
        // [WIZDAM FIX] Instantiate to call non-static methods safely, resolving linter error
        $reviewObjectMetadata = new ReviewObjectMetadata();
        $multipleOptionsTypes = $reviewObjectMetadata->getMultipleOptionsTypes();
        
        $templateMgr->assign('multipleOptionsTypes', $multipleOptionsTypes);
        // In order to be able to search for an element in the array in the javascript function 'togglePossibleResponses'
        $templateMgr->assign('multipleOptionsTypesString', ';' . implode(';', $multipleOptionsTypes) . ';');
        $templateMgr->assign('metadataTypeOptions', $reviewObjectMetadata->getMetadataFormTypeOptions());
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        if ($this->_reviewObjectMetadata !== null) {
            $reviewObjectMetadata = $this->_reviewObjectMetadata;
            $this->_data = [
                'name' => $reviewObjectMetadata->getName(null),
                'required' => (bool) $reviewObjectMetadata->getRequired(),
                'display' => (bool) $reviewObjectMetadata->getDisplay(),
                'metadataType' => (string) $reviewObjectMetadata->getMetadataType(),
                'possibleOptions' => $reviewObjectMetadata->getPossibleOptions(null)
            ];
        }
    }

    /**
     * Read user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'name', 
            'required', 
            'display', 
            'metadataType', 
            'possibleOptions'
        ]);
    }

    /**
     * Save review object metadata. Called by submit handler.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');

        // [SCHOLARWIZDAM LUMERA STANDARD] Context resolution via Router
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        $journalId = $journal ? (int) $journal->getId() : 0;

        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        if ($this->_reviewObjectMetadata === null) {
            $reviewObjectMetadata = $reviewObjectMetadataDao->newDataObject();
            $reviewObjectMetadata->setReviewObjectTypeId($this->_reviewObjectTypeId);
            $reviewObjectMetadata->setSequence(999999); 
        } else {
            $reviewObjectMetadata = $this->_reviewObjectMetadata;
        }
        
        $reviewObjectMetadata->setName((string) $this->getData('name'), null);
        $reviewObjectMetadata->setRequired((bool) $this->getData('required') ? 1 : 0);
        $reviewObjectMetadata->setDisplay((bool) $this->getData('display') ? 1 : 0);
        $reviewObjectMetadata->setMetadataType((string) $this->getData('metadataType'));

        $reviewObjectMetadataInstance = new ReviewObjectMetadata();
        if (in_array($this->getData('metadataType'), $reviewObjectMetadataInstance->getMultipleOptionsTypes(), true)) {
            $reviewObjectMetadata->setPossibleOptions($this->getData('possibleOptions'), null);
        } else {
            $reviewObjectMetadata->setPossibleOptions(null, null);
        }

        if ($reviewObjectMetadata->getId() !== null) {
            $reviewObjectMetadataDao->deleteSetting((int) $reviewObjectMetadata->getId(), 'possibleOptions');
            $reviewObjectMetadataDao->updateObject($reviewObjectMetadata);
        } else {
            $reviewObjectMetadataDao->insertObject($reviewObjectMetadata);
            $reviewObjectMetadataDao->resequence($this->_reviewObjectTypeId);
        }
    }
    
}
?>