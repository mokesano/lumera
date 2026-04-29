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
 * [WIZDAM EDITION] Modernized. PHP 8 Safe (Closures, Constructors, References).
 */

import('lib.pkp.classes.form.Form');

class ObjectForReviewForm extends Form {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /** @var int ID of the object for review */
    public $objectId;

    /** @var int ID of the review object type */
    public $reviewObjectTypeId;

    /**
     * Constructor
     * @param $parentPluginName sting
     * @param $objectId int (optional)
     * @param $reviewObjectTypeId int (optional)
     */
    public function __construct($parentPluginName, $objectId = null, $reviewObjectTypeId = null) {
        $this->parentPluginName = $parentPluginName;
        $this->objectId = (int) $objectId;
        $this->reviewObjectTypeId = (int) $reviewObjectTypeId;

        // Get required metadata and role metadata ID for this review object type
        // [MODERNISASI] Hapus &
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $requiredReviewObjectMetadataIds = $reviewObjectMetadataDao->getRequiredReviewObjectMetadataIds($this->reviewObjectTypeId);
        $roleMetadataId = $reviewObjectMetadataDao->getMetadataId($this->reviewObjectTypeId, 'role');

        $ofrPlugin = PluginRegistry::getPlugin('generic', $parentPluginName);
        
        // [WIZDAM FIX] Parent constructor
        parent::__construct($ofrPlugin->getTemplatePath() . 'editor/objectForReviewForm.tpl');

        // Check required and persons fields
        // [WIZDAM FIX] Replaced create_function with Closure
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'ofrSettings', 
            'required', 
            'plugins.generic.objectsForReview.editor.objectForReview.requiredFields', 
            function($ofrSettings) use ($requiredReviewObjectMetadataIds, $roleMetadataId) {
                foreach ($requiredReviewObjectMetadataIds as $requiredReviewObjectMetadataId) { 
                    if ($requiredReviewObjectMetadataId != $roleMetadataId) { 
                        if (!isset($ofrSettings[$requiredReviewObjectMetadataId]) || $ofrSettings[$requiredReviewObjectMetadataId] == '') return false; 
                    } 
                } 
                return true;
            }
        ));

        // the role and either the first or the last name are required for a person
        // if it is defined as required for this review object type
        if (in_array($roleMetadataId, $requiredReviewObjectMetadataIds)) {
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'persons', 
                'required', 
                'plugins.generic.objectsForReview.editor.objectForReview.requiredPersonFields', 
                function($persons) {
                    foreach ($persons as $person) { 
                        if (empty($person['role']) || (empty($person['firstName']) && empty($person['lastName']))) return false; 
                    } 
                    return true;
                }
            ));
        } else { // if one of the fields is entered
            $this->addCheck(new FormValidatorCustom(
                $this, 
                'persons', 
                'required', 
                'plugins.generic.objectsForReview.editor.objectForReview.requiredPersonFields', 
                function($persons) {
                    foreach ($persons as $person) { 
                        if ($person['personId'] > 0 && (empty($person['role']) || (empty($person['firstName']) && empty($person['lastName'])))) return false; 
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
                        if ($person['personId'] <= 0) { 
                            if ((empty($person['role']) && empty($person['firstName']) && empty($person['lastName'])) || (!empty($person['role']) && (!empty($person['firstName']) || !empty($person['lastName'])))) {} else return false; 
                        } 
                    } 
                    return true;
                }
            ));
        }
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectForReviewForm($parentPluginName, $objectId = null, $reviewObjectTypeId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectForReviewForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName, $objectId, $reviewObjectTypeId);
    }

    /**
     * Display the form.
     * @see Form::display()
     */
    public function display($request = null, $template = null) {
        // [MODERNISASI] Hapus &
        $journal = $request->getJournal();
        $journalId = $journal->getId();
        
        // Get review object type
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $reviewObjectType = $reviewObjectTypeDao->getById($this->reviewObjectTypeId, $journalId);
        
        // Get metadata of the review object type
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $reviewObjectMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($this->reviewObjectTypeId);
        
        // Get journal editors
        $roleDao = DAORegistry::getDAO('RoleDAO');
        // [WIZDAM] Removed & reference
        $editorsDAOResultFactory = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journalId, null, null, null, null, 'name');
        $editors = array();
        
        // [WIZDAM FIX] while loop reference removal
        while ($result = $editorsDAOResultFactory->next()) {
            $editors[$result->getData('id')] = $result->getFullName();
            unset($result);
        }
        
        // Get language list
        $languageDao = DAORegistry::getDAO('LanguageDAO');
        $languages = $languageDao->getLanguages();
        $validLanguages = array('' => __('plugins.generic.objectsForReview.editor.objectForReview.chooseLanguage'));
        
        // [WIZDAM FIX] Replaced each() with foreach
        foreach ($languages as $language) {
            $validLanguages[$language->getCode()] = $language->getName();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('reviewObjectType', $reviewObjectType);
        $templateMgr->assign('reviewObjectMetadata', $reviewObjectMetadata);
        $templateMgr->assign('editors', $editors);
        $templateMgr->assign('validLanguages', $validLanguages);
        $templateMgr->assign('objectId', $this->objectId);
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     */
    public function initData() {
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($this->objectId);
        
        if (isset($objectForReview)) {
            // Settings
            $ofrSettings = $objectForReview->getSettings();
            $this->_data = array(
                'ofrSettings' => $ofrSettings,
                'notes' => $objectForReview->getNotes(),
                'persons' => array(),
                'deletedPersons' => array(),
                'editorId' => $objectForReview->getEditorId(),
                'available' => $objectForReview->getAvailable()
            );
            // Persons
            // [MODERNISASI] Hapus & pada pemanggilan getter
            $persons = $objectForReview->getPersons();
            for ($i=0, $count=count($persons); $i < $count; $i++) {
                array_push(
                    $this->_data['persons'],
                    array(
                        'personId' => $persons[$i]->getId(),
                        'role' => $persons[$i]->getRole(),
                        'firstName' => $persons[$i]->getFirstName(),
                        'middleName' => $persons[$i]->getMiddleName(),
                        'lastName' => $persons[$i]->getLastName(),
                        'seq' => $persons[$i]->getSequence()
                    )
                );
            }
            // Cover page
            $coverPageSetting = $objectForReview->getCoverPage();
            if ($coverPageSetting) {
                $this->_data['fileName'] = $coverPageSetting['fileName'];
                $this->_data['coverPageAltText'] = $coverPageSetting['altText'];
                $this->_data['originalFileName'] = $coverPageSetting['originalFileName'];
            }

        } else {
            $user = Request::getUser();
            $this->_data = array(
                'editorId' => $user->getId(),
                'available' => 1
            );
        }
    }

    /**
     * Read form input data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(
            array(
                'ofrSettings',
                'notes',
                'persons',
                'deletedPersons',
                'fileName',
                'originalFileName',
                'coverPageAltText',
                'editorId',
                'available'
            )
        );
    }

    /**
     * Save object for review.
     * @see Form::execute()
     */
    public function execute($object = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $ofrPlugin->import('classes.ObjectForReview');
        $ofrPlugin->import('classes.ObjectForReviewPerson');
        $ofrPlugin->import('classes.ReviewObjectMetadata');

        $journal = Request::getJournal();
        $journalId = $journal->getId();

        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($this->objectId);
        
        if ($objectForReview == null) {
            $objectForReview = new ObjectForReview();
            $objectForReview->setContextId($journalId);
            $objectForReview->setReviewObjectTypeId($this->reviewObjectTypeId);
            $objectForReview->setDateCreated(Core::getCurrentDate());
        }
        $objectForReview->setNotes($this->getData('notes'));
        $objectForReview->setEditorId($this->getData('editorId'));
        $objectForReview->setAvailable($this->getData('available'));

        // Insert or update object for review
        if ($objectForReview->getId() == null) {
            $ofrDao->insertObject($objectForReview);
        } else {
            $ofrDao->updateObject($objectForReview);
        }

        // Update object for review settings
        $reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
        $reviewObjectTypeMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($objectForReview->getReviewObjectTypeId());
        
        foreach ($reviewObjectTypeMetadata as $metadataId => $reviewObjectMetadata) {
            if (($reviewObjectMetadata->getMetadataType() != REVIEW_OBJECT_METADATA_TYPE_ROLE_DROP_DOWN_BOX) &&
                ($reviewObjectMetadata->getMetadataType() != REVIEW_OBJECT_METADATA_TYPE_COVERPAGE)) {
                    $ofrSettings = $this->getData('ofrSettings');
                    $ofrSettingValue = null;
                    if (isset($ofrSettings[$metadataId])) {
                            $ofrSettingValue = $ofrSettings[$metadataId];
                    }
                    $metadataType = $reviewObjectMetadata->getMetadataType();
                        switch ($metadataType) {
                            case REVIEW_OBJECT_METADATA_TYPE_SMALL_TEXT_FIELD:
                            case REVIEW_OBJECT_METADATA_TYPE_TEXT_FIELD:
                            case REVIEW_OBJECT_METADATA_TYPE_TEXTAREA:
                                $objectForReview->updateSetting((int) $metadataId, $ofrSettingValue, 'string');
                                break;
                            case REVIEW_OBJECT_METADATA_TYPE_RADIO_BUTTONS:
                            case REVIEW_OBJECT_METADATA_TYPE_DROP_DOWN_BOX:
                                $objectForReview->updateSetting((int) $metadataId, $ofrSettingValue, 'int');
                                break;
                            case REVIEW_OBJECT_METADATA_TYPE_CHECKBOXES:
                            case REVIEW_OBJECT_METADATA_TYPE_LANG_DROP_DOWN_BOX:
                                if (!isset($ofrSettingValue)) $ofrSettingValue = array();
                                $objectForReview->updateSetting((int) $metadataId, $ofrSettingValue, 'object');
                                break;
                        }
                }
        }

        // Handle object for review cover image
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $coverPageAltText = $this->getData('coverPageAltText');
        $coverPageMetadataId = $reviewObjectMetadataDao->getMetadataId($this->reviewObjectTypeId, REVIEW_OBJECT_METADATA_KEY_COVERPAGE);
        
        // If a cover page is uploaded
        if ($publicFileManager->uploadedFileExists('coverPage')) {
            $originalFileName = $publicFileManager->getUploadedFileName('coverPage');
            $type = $publicFileManager->getUploadedFileType('coverPage');
            $newFileName = 'cover_ofr_' . $objectForReview->getId() . $publicFileManager->getImageExtension($type);
            $publicFileManager->uploadJournalFile($journalId, 'coverPage', $newFileName);
            list($width, $height) = getimagesize($publicFileManager->getJournalFilesPath($journalId) . '/' . $newFileName);
            $coverPageSetting = array('originalFileName' => $publicFileManager->truncateFileName($originalFileName, 127), 'fileName' => $newFileName, 'width' => $width, 'height' => $height, 'altText' => $coverPageAltText);
            $objectForReview->updateSetting((int) $coverPageMetadataId, $coverPageSetting, 'object');
        } else {
            // If cover page exists, update alt texts
            $coverPageSetting = $objectForReview->getSetting($coverPageMetadataId);
            if ($coverPageSetting) {
                $coverPageSetting['altText'] = $coverPageAltText;
                $objectForReview->updateSetting((int) $coverPageMetadataId, $coverPageSetting, 'object');
            }
        }


        $ofrPersonDao = DAORegistry::getDAO('ObjectForReviewPersonDAO');
        // Insert/update persons
        $persons = $this->getData('persons');
        
        // [WIZDAM FIX] Replaced for loop with proper count handling
        $count = count($persons);
        for ($i=0; $i < $count; $i++) {
            $person = null; // Reset variable
            $isExistingPerson = false;

            if ($persons[$i]['personId'] > 0) {
                $isExistingPerson = true;
                $person = $ofrPersonDao->getById($persons[$i]['personId']);
            } else {
                if ($persons[$i]['role'] != '' && ($persons[$i]['firstName'] != '' || $persons[$i]['lastName'] != '')) {
                    $isExistingPerson = false;
                    $person = new ObjectForReviewPerson();
                }
            }
            if (isset($person) && $person) {
                $person->setObjectId($objectForReview->getId());
                $person->setRole($persons[$i]['role']);
                $person->setFirstName($persons[$i]['firstName']);
                $person->setMiddleName($persons[$i]['middleName']);
                $person->setLastName($persons[$i]['lastName']);
                $person->setSequence($persons[$i]['seq']);

                if ($isExistingPerson) {
                    $ofrPersonDao->updateObject($person);
                } else {
                    $ofrPersonDao->insertobject($person);
                }
            }
            unset($person);
        }

        // Delete persons
        $deletedPersons = explode(':', $this->getData('deletedPersons'));
        $count = count($deletedPersons);
        for ($i=0; $i < $count; $i++) {
            if (!empty($deletedPersons[$i])) {
                $ofrPersonDao->deleteById($deletedPersons[$i]);
            }
        }

        // Update persons sequence numbers
        $ofrPersonDao->resequence($objectForReview->getId());

    }

}
?>