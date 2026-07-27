<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/form/ObjectForReviewForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewForm
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReview
 *
 * @brief Object for review form.
 */

import('lib.pkp.classes.form.Form');

class ObjectForReviewForm extends Form {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /** @var int|null ID of the object for review */
    protected $_objectId;

    /** @var int|null ID of the review object type */
    protected $_reviewObjectTypeId;

    /**
     * Constructor.
     * @param string $parentPluginName
     * @param int|null $objectId
     * @param int|null $reviewObjectTypeId
     */
    public function __construct($parentPluginName, $objectId = null, $reviewObjectTypeId = null) {
        $this->_parentPluginName = (string) $parentPluginName;
        $this->_objectId = $objectId !== null ? (int) $objectId : null;
        $this->_reviewObjectTypeId = $reviewObjectTypeId !== null ? (int) $reviewObjectTypeId : null;

        // Get required metadata and role metadata ID for this review object type
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $requiredReviewObjectMetadataIds = $reviewObjectMetadataDao->getRequiredReviewObjectMetadataIds($this->_reviewObjectTypeId);
        $roleMetadataId = $reviewObjectMetadataDao->getMetadataId($this->_reviewObjectTypeId, 'role');

        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/objectForReviewForm.tpl');

        // Check required and persons fields
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'ofrSettings', 
            'required', 
            'plugins.generic.objectsForReview.editor.objectForReview.requiredFields', 
            function($ofrSettings) use ($requiredReviewObjectMetadataIds, $roleMetadataId) {
                foreach ($requiredReviewObjectMetadataIds as $requiredReviewObjectMetadataId) { 
                    if ($requiredReviewObjectMetadataId !== $roleMetadataId) { 
                        if (!isset($ofrSettings[$requiredReviewObjectMetadataId]) || $ofrSettings[$requiredReviewObjectMetadataId] === '') {
                            return false; 
                        } 
                    } 
                } 
                return true;
            }
        ));

        // The role and either the first or the last name are required for a person
        // if it is defined as required for this review object type
        if (in_array($roleMetadataId, $requiredReviewObjectMetadataIds, true)) {
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'persons', 
                'required', 
                'plugins.generic.objectsForReview.editor.objectForReview.requiredPersonFields', 
                function($persons) {
                    foreach ($persons as $person) { 
                        if (empty($person['role']) || (empty($person['firstName']) && empty($person['lastName']))) {
                            return false; 
                        } 
                    } 
                    return true;
                }
            ));
        } else {
            // If one of the fields is entered
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'persons', 
                'required', 
                'plugins.generic.objectsForReview.editor.objectForReview.requiredPersonFields', 
                function($persons) {
                    foreach ($persons as $person) { 
                        if ((int) ($person['personId'] ?? 0) > 0 && (empty($person['role']) || (empty($person['firstName']) && empty($person['lastName'])))) {
                            return false; 
                        } 
                    } 
                    return true;
                }
            ));
            
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'persons', 
                'required', 
                'plugins.generic.objectsForReview.editor.objectForReview.requiredPersonFields', 
                function($persons) {
                    foreach ($persons as $person) { 
                        if ((int) ($person['personId'] ?? 0) <= 0) { 
                            if ((empty($person['role']) && empty($person['firstName']) && empty($person['lastName'])) || (!empty($person['role']) && (!empty($person['firstName']) || !empty($person['lastName'])))) {
                                // Valid state
                            } else {
                                return false; 
                            }
                        } 
                    } 
                    return true;
                }
            ));
        }
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     * @param int|null $objectId
     * @param int|null $reviewObjectTypeId
     */
    public function ObjectForReviewForm($parentPluginName, $objectId = null, $reviewObjectTypeId = null) {
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

        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;
        
        // Get review object type
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $reviewObjectType = $reviewObjectTypeDao->getById($this->_reviewObjectTypeId, $journalId);
        
        // Get metadata of the review object type
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $reviewObjectMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($this->_reviewObjectTypeId);
        
        // Get journal editors
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $editorsDAOResultFactory = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journalId, null, null, null, null, 'name');
        $editors = [];
        
        while ($result = $editorsDAOResultFactory->next()) {
            $editors[(int) $result->getData('id')] = (string) $result->getFullName();
        }
        
        // Get language list
        /** @var LanguageDAO $languageDao */
        $languageDao = DAORegistry::getDAO('LanguageDAO');
        $languages = $languageDao->getLanguages();
        $validLanguages = ['' => __('plugins.generic.objectsForReview.editor.objectForReview.chooseLanguage')];
        
        foreach ($languages as $language) {
            $validLanguages[(string) $language->getCode()] = (string) $language->getName();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('reviewObjectType', $reviewObjectType);
        $templateMgr->assign('reviewObjectMetadata', $reviewObjectMetadata);
        $templateMgr->assign('editors', $editors);
        $templateMgr->assign('validLanguages', $validLanguages);
        $templateMgr->assign('objectId', $this->_objectId);
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($this->_objectId);
        
        if ($objectForReview !== null) {
            // Settings
            $ofrSettings = $objectForReview->getSettings();
            $this->_data = [
                'ofrSettings' => $ofrSettings,
                'notes' => $objectForReview->getNotes(),
                'persons' => [],
                'deletedPersons' => [],
                'editorId' => $objectForReview->getEditorId(),
                'available' => $objectForReview->getAvailable()
            ];
            
            // Persons
            $persons = $objectForReview->getPersons();
            foreach ($persons as $person) {
                $this->_data['persons'][] = [
                    'personId' => (int) $person->getId(),
                    'role' => (string) $person->getRole(),
                    'firstName' => (string) $person->getFirstName(),
                    'middleName' => (string) $person->getMiddleName(),
                    'lastName' => (string) $person->getLastName(),
                    'seq' => (int) $person->getSequence()
                ];
            }
            
            // Cover page
            $coverPageSetting = $objectForReview->getCoverPage();
            if (is_array($coverPageSetting)) {
                $this->_data['fileName'] = (string) ($coverPageSetting['fileName'] ?? '');
                $this->_data['coverPageAltText'] = (string) ($coverPageSetting['altText'] ?? '');
                $this->_data['originalFileName'] = (string) ($coverPageSetting['originalFileName'] ?? '');
            }

        } else {
            // Lumera Singleton Fallback
            $request = Application::get()->getRequest();
            $user = $request->getUser();
            $this->_data = [
                'editorId' => $user ? (int) $user->getId() : 0,
                'available' => 1
            ];
        }
    }

    /**
     * Read form input data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'ofrSettings',
            'notes',
            'persons',
            'deletedPersons',
            'fileName',
            'originalFileName',
            'coverPageAltText',
            'editorId',
            'available'
        ]);
    }

    /**
     * Save object for review.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ObjectForReview');
        $ofrPlugin->import('classes.ObjectForReviewPerson');
        $ofrPlugin->import('classes.ReviewObjectMetadata');

        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;

        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($this->_objectId);
        
        if ($objectForReview === null) {
            $objectForReview = new ObjectForReview();
            $objectForReview->setContextId($journalId);
            $objectForReview->setReviewObjectTypeId($this->_reviewObjectTypeId);
            $objectForReview->setDateCreated(Core::getCurrentDate());
        }
        $objectForReview->setNotes((string) $this->getData('notes'));
        $objectForReview->setEditorId((int) $this->getData('editorId'));
        $objectForReview->setAvailable((int) $this->getData('available'));

        // Insert or update object for review
        if ($objectForReview->getId() === null) {
            $ofrDao->insertObject($objectForReview);
        } else {
            $ofrDao->updateObject($objectForReview);
        }

        // Update object for review settings
        /** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $reviewObjectTypeMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($objectForReview->getReviewObjectTypeId());
        
        foreach ($reviewObjectTypeMetadata as $metadataId => $reviewObjectMetadata) {
            if ($reviewObjectMetadata->getMetadataType() !== REVIEW_OBJECT_METADATA_TYPE_ROLE_DROP_DOWN_BOX &&
                $reviewObjectMetadata->getMetadataType() !== REVIEW_OBJECT_METADATA_TYPE_COVERPAGE) {
                
                $ofrSettings = $this->getData('ofrSettings');
                $ofrSettingValue = $ofrSettings[$metadataId] ?? null;
                $metadataType = $reviewObjectMetadata->getMetadataType();
                
                switch ($metadataType) {
                    case REVIEW_OBJECT_METADATA_TYPE_SMALL_TEXT_FIELD:
                    case REVIEW_OBJECT_METADATA_TYPE_TEXT_FIELD:
                    case REVIEW_OBJECT_METADATA_TYPE_TEXTAREA:
                        $objectForReview->updateSetting((int) $metadataId, (string) $ofrSettingValue, 'string');
                        break;
                    case REVIEW_OBJECT_METADATA_TYPE_RADIO_BUTTONS:
                    case REVIEW_OBJECT_METADATA_TYPE_DROP_DOWN_BOX:
                        $objectForReview->updateSetting((int) $metadataId, (int) $ofrSettingValue, 'int');
                        break;
                    case REVIEW_OBJECT_METADATA_TYPE_CHECKBOXES:
                    case REVIEW_OBJECT_METADATA_TYPE_LANG_DROP_DOWN_BOX:
                        if (!is_array($ofrSettingValue)) {
                            $ofrSettingValue = [];
                        }
                        $objectForReview->updateSetting((int) $metadataId, $ofrSettingValue, 'object');
                        break;
                }
            }
        }

        // Handle object for review cover image
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPageAltText = (string) $this->getData('coverPageAltText');
        $coverPageMetadataId = $reviewObjectMetadataDao->getMetadataId($this->_reviewObjectTypeId, REVIEW_OBJECT_METADATA_KEY_COVERPAGE);
        
        // If a cover page is uploaded
        if ($publicFileManager->uploadedFileExists('coverPage')) {
            $originalFileName = $publicFileManager->getUploadedFileName('coverPage');
            $type = $publicFileManager->getUploadedFileType('coverPage');
            $newFileName = 'cover_ofr_' . $objectForReview->getId() . $publicFileManager->getImageExtension($type);
            $publicFileManager->uploadJournalFile($journalId, 'coverPage', $newFileName);
            
            $filePath = $publicFileManager->getJournalFilesPath($journalId) . '/' . $newFileName;
            $imageSize = getimagesize($filePath);
            $width = $imageSize ? (int) $imageSize[0] : 0;
            $height = $imageSize ? (int) $imageSize[1] : 0;
            
            $coverPageSetting = [
                'originalFileName' => $publicFileManager->truncateFileName((string) $originalFileName, 127), 
                'fileName' => $newFileName, 
                'width' => $width, 
                'height' => $height, 
                'altText' => $coverPageAltText
            ];
            $objectForReview->updateSetting((int) $coverPageMetadataId, $coverPageSetting, 'object');
        } else {
            // If cover page exists, update alt texts
            $coverPageSetting = $objectForReview->getSetting($coverPageMetadataId);
            if (is_array($coverPageSetting)) {
                $coverPageSetting['altText'] = $coverPageAltText;
                $objectForReview->updateSetting((int) $coverPageMetadataId, $coverPageSetting, 'object');
            }
        }

        /** @var ObjectForReviewPersonDAO $ofrPersonDao */
        $ofrPersonDao = DAORegistry::getDAO('ObjectForReviewPersonDAO');
        
        // Insert/update persons
        $persons = $this->getData('persons');
        if (is_array($persons)) {
            foreach ($persons as $personData) {
                $person = null;
                $isExistingPerson = false;

                $personId = (int) ($personData['personId'] ?? 0);
                if ($personId > 0) {
                    $isExistingPerson = true;
                    $person = $ofrPersonDao->getById($personId);
                } else {
                    $role = (string) ($personData['role'] ?? '');
                    $firstName = (string) ($personData['firstName'] ?? '');
                    $lastName = (string) ($personData['lastName'] ?? '');
                    if ($role !== '' && ($firstName !== '' || $lastName !== '')) {
                        $isExistingPerson = false;
                        $person = new ObjectForReviewPerson();
                    }
                }
                
                if ($person !== null) {
                    $person->setObjectId((int) $objectForReview->getId());
                    $person->setRole((string) ($personData['role'] ?? ''));
                    $person->setFirstName((string) ($personData['firstName'] ?? ''));
                    $person->setMiddleName((string) ($personData['middleName'] ?? ''));
                    $person->setLastName((string) ($personData['lastName'] ?? ''));
                    $person->setSequence((int) ($personData['seq'] ?? 0));

                    if ($isExistingPerson) {
                        $ofrPersonDao->updateObject($person);
                    } else {
                        // [WIZDAM FIX] Corrected typo from 'insertobject' to 'insertObject'
                        $ofrPersonDao->insertObject($person);
                    }
                }
            }
        }

        // Delete persons
        $deletedPersons = explode(':', (string) $this->getData('deletedPersons'));
        foreach ($deletedPersons as $deletedPersonId) {
            if ($deletedPersonId !== '') {
                $ofrPersonDao->deleteById((int) $deletedPersonId);
            }
        }

        // Update persons sequence numbers
        $ofrPersonDao->resequence((int) $objectForReview->getId());
    }

}
?>