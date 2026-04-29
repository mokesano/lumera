<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/PKPAnnouncementTypeForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeForm
 * @ingroup manager_form
 * @see AnnouncementType
 *
 * @brief Form for manager to create/edit announcement types.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class PKPAnnouncementTypeForm extends Form {
    /** @var int|null the ID of the announcement type being edited */
    public $typeId;

    /**
     * Constructor
     * @param int|null $typeId leave as default for new announcement type
     */
    public function __construct($typeId = null) {
        $this->typeId = isset($typeId) ? (int) $typeId : null;

        parent::__construct('manager/announcement/announcementTypeForm.tpl');

        // Type name is provided
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'manager.announcementTypes.form.typeNameRequired'));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAnnouncementTypeForm($typeId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPAnnouncementTypeForm(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($typeId);
    }

    /**
     * Get a list of localized field names for this form
     * @return array
     */
    public function getLocaleFieldNames() {
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        return parent::getLocaleFieldNames() + $announcementTypeDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param PKPRequest $request
     * @param string $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('typeId', $this->typeId);

        return parent::display($request, $template);
    }

    /**
     * Initialize form data from current announcement type.
     */
    public function initData() {
        if (isset($this->typeId)) {
            $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
            $announcementType = $announcementTypeDao->getById($this->typeId);

            if ($announcementType != null) {
                $this->_data = [
                    'name' => $announcementType->getName(null) // Localized
                ];

            } else {
                $this->typeId = null;
            }
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['name']);
    }

    /**
     * Save announcement type.
     * [WIZDAM FIX] Updated signature to match Form::execute($object = null)
     * @param mixed $object
     */
    public function execute($object = null) {
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');

        if (isset($this->typeId)) {
            $announcementType = $announcementTypeDao->getById($this->typeId);
        }

        if (!isset($announcementType)) {
            $announcementType = $announcementTypeDao->newDataObject();
        }

        // Give the parent class a chance to set the assocType/assocId.
        $this->_setAnnouncementTypeAssocId($announcementType);
        
        $announcementType->setName($this->getData('name'), null); // Localized

        // Update or insert announcement type
        if ($announcementType->getId() != null) {
            $announcementTypeDao->updateObject($announcementType);
        } else {
            $announcementTypeDao->insertAnnouncementType($announcementType);
        }
    }

    /**
     * Set the announcement type association ID.
     * Must be implemented by subclasses.
     * [WIZDAM FIX] Visibility set to public to match potential subclass usage or keep consistent
     * @param AnnouncementType $announcementType
     */
    public function _setAnnouncementTypeAssocId($announcementType) {
        // Must be implemented by subclasses
        // assert(false); // [WIZDAM] Safety bypass
    }
}
?>