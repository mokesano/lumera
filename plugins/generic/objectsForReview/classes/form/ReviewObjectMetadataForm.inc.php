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
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('lib.pkp.classes.form.Form');

class ReviewObjectMetadataForm extends Form {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /** @var int ID of the ReviewObjectType being edited */
    public $reviewObjectTypeId;

    /** @var object ReviewObjectMetadata being edited */
    public $reviewObjectMetadata;

    /**
     * Constructor
     * @param $parentPluginName sting
     * @param $reviewObjectTypeId int
     * @param $metadataId int (optional)
     */
    public function __construct($parentPluginName, $reviewObjectTypeId, $metadataId = null) {
        $this->parentPluginName = $parentPluginName;
        $this->reviewObjectTypeId = (int) $reviewObjectTypeId;

        // [MODERNISASI] Hapus &
        $ofrPlugin = PluginRegistry::getPlugin('generic', $parentPluginName);
        $journal = Request::getJournal();
        $journalId = $journal->getId();

        $ofrPlugin->import('classes.ReviewObjectMetadata');
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        
        if (!empty($metadataId)) {
            $this->reviewObjectMetadata = $reviewObjectMetadataDao->getById((int) $metadataId, $this->reviewObjectTypeId);
        } else {
            $this->reviewObjectMetadata = null;
        }
        
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/reviewObjectMetadataForm.tpl');

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'plugins.generic.objectsForReview.editor.objectMetadata.form.nameRequired'));
        $this->addCheck(new FormValidator($this, 'metadataType', 'required', 'plugins.generic.objectsForReview.editor.objectMetadata.form.typeRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewObjectMetadataForm($parentPluginName, $reviewObjectTypeId, $metadataId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewObjectMetadataForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName, $reviewObjectTypeId, $metadataId);
    }

    /**
     * Get the names of the fields that are localized.
     * @see Form::getLocaleFieldNames()
     */
    public function getLocaleFieldNames() {
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        return $reviewObjectMetadataDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @see Form::display()
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('reviewObjectMetadata', $this->reviewObjectMetadata);
        $templateMgr->assign('reviewObjectTypeId', $this->reviewObjectTypeId);

        $ofrPlugin = PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        $templateMgr->assign('multipleOptionsTypes', ReviewObjectMetadata::getMultipleOptionsTypes());
        // in order to be able to search for an element in the array in the javascript function 'togglePossibleResponses':
        $templateMgr->assign('multipleOptionsTypesString', ';'.implode(';', ReviewObjectMetadata::getMultipleOptionsTypes()).';');
        $templateMgr->assign('metadataTypeOptions', ReviewObjectMetadata::getMetadataFormTypeOptions());
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     */
    public function initData() {
        if ($this->reviewObjectMetadata != null) {
            $reviewObjectMetadata = $this->reviewObjectMetadata;
            $this->_data = array(
                'name' => $reviewObjectMetadata->getName(null), // Localized
                'required' => $reviewObjectMetadata->getRequired(),
                'display' => $reviewObjectMetadata->getDisplay(),
                'metadataType' => $reviewObjectMetadata->getMetadataType(),
                'possibleOptions' => $reviewObjectMetadata->getPossibleOptions(null) //Localized
            );
        }
    }

    /**
     * Read user-submitted data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(array('name', 'required', 'display', 'metadataType', 'possibleOptions'));
    }

    /**
     * Save review object metadata. Called by submit handler.
     * @see Form::execute()
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $journal = Request::getJournal();
        $journalId = $journal->getId();

        $ofrPlugin->import('classes.ReviewObjectMetadata');
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        
        if ($this->reviewObjectMetadata == null) {
            $reviewObjectMetadata = $reviewObjectMetadataDao->newDataObject();
            $reviewObjectMetadata->setReviewObjectTypeId($this->reviewObjectTypeId);
            $reviewObjectMetadata->setSequence(REALLY_BIG_NUMBER);
        } else {
            $reviewObjectMetadata = $this->reviewObjectMetadata;
        }
        
        $reviewObjectMetadata->setName($this->getData('name'), null); // Localized
        $reviewObjectMetadata->setRequired($this->getData('required') ? 1 : 0);
        $reviewObjectMetadata->setDisplay($this->getData('display') ? 1 : 0);
        $reviewObjectMetadata->setMetadataType($this->getData('metadataType'));

        if (in_array($this->getData('metadataType'), ReviewObjectMetadata::getMultipleOptionsTypes())) {
            $reviewObjectMetadata->setPossibleOptions($this->getData('possibleOptions'), null); // Localized
        } else {
            $reviewObjectMetadata->setPossibleOptions(null, null);
        }

        if ($reviewObjectMetadata->getId() != null) {
            $reviewObjectMetadataDao->deleteSetting($reviewObjectMetadata->getId(), 'possibleOptions');
            $reviewObjectMetadataDao->updateObject($reviewObjectMetadata);
        } else {
            $reviewObjectMetadataDao->insertObject($reviewObjectMetadata);
            $reviewObjectMetadataDao->resequence($this->reviewObjectTypeId);
        }
    }
}
?>