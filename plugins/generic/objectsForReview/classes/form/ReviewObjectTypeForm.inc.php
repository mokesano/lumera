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
 */

import('lib.pkp.classes.form.Form');

class ReviewObjectTypeForm extends Form {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /** @var ReviewObjectType|null ReviewObjectType being edited */
    protected $_reviewObjectType;

    /**
     * Constructor.
     * @param string $parentPluginName
     * @param int|null $typeId
     */
    public function __construct($parentPluginName, $typeId = null) {
        $this->_parentPluginName = (string) $parentPluginName;

        // [SCHOLARWIZDAM LUMERA STANDARD] Context resolution via Router
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        $journalId = $journal ? (int) $journal->getId() : 0;

        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectType');
        
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        
        if ($typeId !== null) {
            $this->_reviewObjectType = $reviewObjectTypeDao->getById((int) $typeId, $journalId);
        } else {
            $this->_reviewObjectType = null;
        }
        
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/reviewObjectTypeForm.tpl');

        // Validation checks for this form
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'plugins.generic.objectsForReview.editor.objectType.form.nameRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     * @param int|null $typeId
     */
    public function ReviewObjectTypeForm($parentPluginName, $typeId = null) {
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
     * Get the names of localized fields for this form.
     * @see Form::getLocaleFieldNames()
     * @return array
     */
    public function getLocaleFieldNames(): array {
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        return $reviewObjectTypeDao->getLocaleFieldNames();
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
        $templateMgr->assign('reviewObjectType', $this->_reviewObjectType);
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        if ($this->_reviewObjectType !== null) {
            $reviewObjectType = $this->_reviewObjectType;
            $this->_data = [
                'name' => $reviewObjectType->getName(null),
                'description' => $reviewObjectType->getDescription(null)
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
            'description', 
            'possibleOptions'
        ]);
    }

    /**
     * Save review object type.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectType');

        // [SCHOLARWIZDAM LUMERA STANDARD] Context resolution via Router
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        $journalId = $journal ? (int) $journal->getId() : 0;

        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        
        if ($this->_reviewObjectType === null) {
            $reviewObjectType = $reviewObjectTypeDao->newDataObject();
            $reviewObjectType->setContextId($journalId);
            $reviewObjectType->setActive(0);
        } else {
            $reviewObjectType = $this->_reviewObjectType;
        }

        $reviewObjectType->setName((string) $this->getData('name'), null);
        $reviewObjectType->setDescription((string) $this->getData('description'), null);

        if ($reviewObjectType->getId() !== null) {
            $reviewObjectTypeDao->updateObject($reviewObjectType);
        } else {
            // Install common metadata
            $ofrPlugin->import('classes.ReviewObjectMetadata');
            $reviewObjectMetadataInstance = new ReviewObjectMetadata();
            $multipleOptionsTypes = $reviewObjectMetadataInstance->getMultipleOptionsTypes();
            $dtdTypes = $reviewObjectMetadataInstance->getMetadataDTDTypes();

            /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
            $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
            $reviewObjectMetadataArray = [];
            
            if ($journal) {
                $availableLocales = $journal->getSupportedLocaleNames();
                
                foreach ($availableLocales as $locale => $localeName) {
                    $xmlDao = new XMLDAO();
                    $commonDataPath = $ofrPlugin->getPluginPath() . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'commonMetadata.xml';
                    $commonData = $xmlDao->parse($commonDataPath);
                    
                    if ($commonData) {
                        $commonMetadata = $commonData->getChildByName('objectMetadata');
                        
                        if ($commonMetadata) {
                            foreach ($commonMetadata->getChildren() as $metadataNode) {
                                $key = (string) $metadataNode->getAttribute('key');
                                if (array_key_exists($key, $reviewObjectMetadataArray)) {
                                    $reviewObjectMetadata = $reviewObjectMetadataArray[$key];
                                } else {
                                    $reviewObjectMetadata = $reviewObjectMetadataDao->newDataObject();
                                    $reviewObjectMetadata->setSequence(999999); // Use explicit large integer
                                    
                                    $typeAttr = (string) $metadataNode->getAttribute('type');
                                    $metadataType = $dtdTypes[$typeAttr] ?? 0;
                                    $reviewObjectMetadata->setMetadataType((string) $metadataType);
                                    
                                    $required = (string) $metadataNode->getAttribute('required');
                                    $reviewObjectMetadata->setRequired($required === 'true' ? 1 : 0);
                                    
                                    $display = (string) $metadataNode->getAttribute('display');
                                    $reviewObjectMetadata->setDisplay($display === 'true' ? 1 : 0);
                                }
                                
                                $name = __((string) $metadataNode->getChildValue('name'), [], $locale);
                                $reviewObjectMetadata->setName($name, $locale);

                                if ($key === 'role') {
                                    $reviewObjectMetadata->setPossibleOptions($this->getData('possibleOptions'), null);
                                } else {
                                    if (in_array($reviewObjectMetadata->getMetadataType(), $multipleOptionsTypes, true)) {
                                        $selectionOptions = $metadataNode->getChildByName('selectionOptions');
                                        $possibleOptions = [];
                                        $index = 1;
                                        if ($selectionOptions) {
                                            foreach ($selectionOptions->getChildren() as $selectionOptionNode) {
                                                $possibleOptions[] = [
                                                    'order' => $index, 
                                                    'content' => __((string) $selectionOptionNode->getValue(), [], $locale)
                                                ];
                                                $index++;
                                            }
                                        }
                                        $reviewObjectMetadata->setPossibleOptions($possibleOptions, $locale);
                                    } else {
                                        $reviewObjectMetadata->setPossibleOptions(null, null);
                                    }
                                }
                                $reviewObjectMetadataArray[$key] = $reviewObjectMetadata;
                            }
                        }
                    }
                }
            }
            
            $reviewObjectTypeId = $reviewObjectTypeDao->insertObject($reviewObjectType);
            
            // Insert review object metadata
            foreach ($reviewObjectMetadataArray as $key => $reviewObjectMetadata) {
                $reviewObjectMetadata->setReviewObjectTypeId((int) $reviewObjectTypeId);
                $reviewObjectMetadata->setKey($key);
                $reviewObjectMetadataDao->insertObject($reviewObjectMetadata);
            }
            $reviewObjectMetadataDao->resequence((int) $reviewObjectTypeId);
        }
    }
    
}
?>