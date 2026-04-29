<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/form/ReviewObjectTypeForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectTypeForm
 * @ingroup plugins_generic_objectsForReview
 * @see ReviewObjectType
 *
 * @brief Form for journal managers to create/edit review object types.
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('lib.pkp.classes.form.Form');

class ReviewObjectTypeForm extends Form {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /** @var object ReviewObjectType being edited */
    public $reviewObjectType;

    /**
     * Constructor
     * @param $parentPluginName sting
     * @param $typeId int
     */
    public function __construct($parentPluginName, $typeId = null) {
        $this->parentPluginName = $parentPluginName;

        $ofrPlugin = PluginRegistry::getPlugin('generic', $parentPluginName);
        $journal = Request::getJournal();
        $journalId = $journal->getId();

        $ofrPlugin->import('classes.ReviewObjectType');
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        if (!empty($typeId)) {
            $this->reviewObjectType = $reviewObjectTypeDao->getById((int) $typeId, $journalId);
        } else {
            $this->reviewObjectType = null;
        }
        
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/reviewObjectTypeForm.tpl');

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'plugins.generic.objectsForReview.editor.objectType.form.nameRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewObjectTypeForm($parentPluginName, $typeId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewObjectTypeForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName, $typeId);
    }

    /**
     * Get the names of localized fields for this form
     * @see Form::getLocaleFieldNames()
     */
    public function getLocaleFieldNames() {
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        return $reviewObjectTypeDao->getLocaleFieldNames();
    }

    /**
     * Display the form
     * @see Form::display()
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('reviewObjectType', $this->reviewObjectType);
        parent::display($request, $template);
    }

    /**
     * Initialize form data
     * @see Form::initData()
     */
    public function initData() {
        if ($this->reviewObjectType != null) {
            $reviewObjectType = $this->reviewObjectType;
            $this->_data = array(
                'name' => $reviewObjectType->getName(null), // Localized
                'description' => $reviewObjectType->getDescription(null) // Localized
            );
        }
    }

    /**
     * Read user-submitted data
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(array('name', 'description', 'possibleOptions'));
    }

    /**
     * Save review object type
     * @see Form::execute()
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $journal = Request::getJournal();
        $journalId = $journal->getId();

        $ofrPlugin->import('classes.ReviewObjectType');
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        
        if ($this->reviewObjectType == null) {
            $reviewObjectType = $reviewObjectTypeDao->newDataObject();
            $reviewObjectType->setContextId($journalId);
            $reviewObjectType->setActive(0);
        } else {
            $reviewObjectType = $this->reviewObjectType;
        }

        $reviewObjectType->setName($this->getData('name'), null); // Localized
        $reviewObjectType->setDescription($this->getData('description'), null); // Localized

        if ($reviewObjectType->getId() != null) {
            $reviewObjectTypeDao->updateObject($reviewObjectType);
        } else {
            //install common metadata
            $ofrPlugin->import('classes.ReviewObjectMetadata');
            $multipleOptionsTypes = ReviewObjectMetadata::getMultipleOptionsTypes();
            $dtdTypes = ReviewObjectMetadata::getMetadataDTDTypes();

            $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
            $reviewObjectMetadataArray = array();
            $availableLocales = $journal->getSupportedLocaleNames();
            
            foreach ($availableLocales as $locale => $localeName) {
                $xmlDao = new XMLDAO();
                $commonDataPath = $ofrPlugin->getPluginPath() . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'commonMetadata.xml';
                $commonData = $xmlDao->parse($commonDataPath);
                $commonMetadata = $commonData->getChildByName('objectMetadata');
                
                foreach ($commonMetadata->getChildren() as $metadataNode) {
                    $key = $metadataNode->getAttribute('key');
                    if (array_key_exists($key, $reviewObjectMetadataArray)) {
                        $reviewObjectMetadata = $reviewObjectMetadataArray[$key];
                    } else {
                        $reviewObjectMetadata = $reviewObjectMetadataDao->newDataObject();
                        // Ensure constant REALLY_BIG_NUMBER is defined or use max int
                        $reviewObjectMetadata->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999);
                        $metadataType = $dtdTypes[$metadataNode->getAttribute('type')];
                        $reviewObjectMetadata->setMetadataType($metadataType);
                        $required = $metadataNode->getAttribute('required');
                        $reviewObjectMetadata->setRequired($required == 'true' ? 1 : 0);
                        $display = $metadataNode->getAttribute('display');
                        $reviewObjectMetadata->setDisplay($display == 'true' ? 1 : 0);
                    }
                    $name = __($metadataNode->getChildValue('name'), array(), $locale);
                    $reviewObjectMetadata->setName($name, $locale);

                    if ($key == 'role') {
                        $reviewObjectMetadata->setPossibleOptions($this->getData('possibleOptions'), null); // Localized
                    } else {
                        if (in_array($reviewObjectMetadata->getMetadataType(), $multipleOptionsTypes)) {
                            $selectionOptions = $metadataNode->getChildByName('selectionOptions');
                            $possibleOptions = array();
                            $index = 1;
                            foreach ($selectionOptions->getChildren() as $selectionOptionNode) {
                                $possibleOptions[] = array('order' => $index, 'content' => __($selectionOptionNode->getValue(), array(), $locale));
                                $index++;
                            }
                            $reviewObjectMetadata->setPossibleOptions($possibleOptions, $locale);
                        } else {
                            $reviewObjectMetadata->setPossibleOptions(null, null);
                        }
                    }
                    $reviewObjectMetadataArray[$key] = $reviewObjectMetadata;
                }
            }
            $reviewObjectTypeId = $reviewObjectTypeDao->insertObject($reviewObjectType);
            // insert review object metadata
            foreach ($reviewObjectMetadataArray as $key => $reviewObjectMetadata) {
                $reviewObjectMetadata->setReviewObjectTypeId($reviewObjectTypeId);
                $reviewObjectMetadata->setKey($key);
                $reviewObjectMetadataDao->insertObject($reviewObjectMetadata);
                $reviewObjectMetadataDao->resequence($reviewObjectTypeId);
            }
        }
    }
}
?>