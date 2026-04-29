<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/PKPAnnouncementForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementForm
 * @ingroup manager_form
 *
 * @brief Form for managers to create/edit announcements.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class PKPAnnouncementForm extends Form {
    /** @var int|null the ID of the announcement being edited */
    public $announcementId;

    /** @var int */
    public $_contextId;

    /**
     * Constructor
     * @param int $contextId
     * @param int|null $announcementId leave as default for new announcement
     */
    public function __construct($contextId, $announcementId = null) {

        $this->_contextId = (int) $contextId;
        $this->announcementId = isset($announcementId) ? (int) $announcementId : null;
        parent::__construct('manager/announcement/announcementForm.tpl');

        // Title is provided
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.announcements.form.titleRequired'));

        // Short description is provided
        $this->addCheck(new FormValidatorLocale($this, 'descriptionShort', 'required', 'manager.announcements.form.descriptionShortRequired'));

        // Description is provided
        $this->addCheck(new FormValidatorLocale($this, 'description', 'optional', 'manager.announcements.form.descriptionRequired'));

        // If provided, expiry date is valid
        $this->addCheck(new FormValidatorCustom($this, 'dateExpireYear', 'optional', 'manager.announcements.form.dateExpireValid', function($dateExpireYear) {
            $minYear = date('Y');
            $maxYear = date('Y') + ANNOUNCEMENT_EXPIRE_YEAR_OFFSET_FUTURE;
            return ($dateExpireYear >= $minYear && $dateExpireYear <= $maxYear);
        }));

        $this->addCheck(new FormValidatorCustom($this, 'dateExpireYear', 'optional', 'manager.announcements.form.dateExpireYearIncompleteDate', function($dateExpireYear, $form) {
            $dateExpireMonth = $form->getData('dateExpireMonth');
            $dateExpireDay = $form->getData('dateExpireDay');
            return ($dateExpireMonth != null && $dateExpireDay != null);
        }, [$this]));

        $this->addCheck(new FormValidatorCustom($this, 'dateExpireMonth', 'optional', 'manager.announcements.form.dateExpireValid', function($dateExpireMonth) {
            return ($dateExpireMonth >= 1 && $dateExpireMonth <= 12);
        }));

        $this->addCheck(new FormValidatorCustom($this, 'dateExpireMonth', 'optional', 'manager.announcements.form.dateExpireMonthIncompleteDate', function($dateExpireMonth, $form) {
            $dateExpireYear = $form->getData('dateExpireYear');
            $dateExpireDay = $form->getData('dateExpireDay');
            return ($dateExpireYear != null && $dateExpireDay != null);
        }, [$this]));

        $this->addCheck(new FormValidatorCustom($this, 'dateExpireDay', 'optional', 'manager.announcements.form.dateExpireValid', function($dateExpireDay) {
            return ($dateExpireDay >= 1 && $dateExpireDay <= 31);
        }));

        $this->addCheck(new FormValidatorCustom($this, 'dateExpireDay', 'optional', 'manager.announcements.form.dateExpireDayIncompleteDate', function($dateExpireDay, $form) {
            $dateExpireYear = $form->getData('dateExpireYear');
            $dateExpireMonth = $form->getData('dateExpireMonth');
            return ($dateExpireYear != null && $dateExpireMonth != null);
        }, [$this]));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAnnouncementForm($contextId, $announcementId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPAnnouncementForm(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($contextId, $announcementId);
    }


    //
    // Getters and setters.
    //
    /**
     * Get the current context id.
     * @return int
     */
    public function getContextId() {
        return $this->_contextId;
    }


    //
    // Extended methods from Form.
    //
    /**
     * Get the list of localized field names for this object
     * @return array
     */
    public function getLocaleFieldNames() {
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        return parent::getLocaleFieldNames() + $announcementDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param PKPRequest $request
     * @param string $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('announcementId', $this->announcementId);
        $templateMgr->assign('yearOffsetFuture', ANNOUNCEMENT_EXPIRE_YEAR_OFFSET_FUTURE);

        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        list($assocType, $assocId) = $this->_getAnnouncementTypesAssocId();
        // Hapus '&'
        $announcementTypes = $announcementTypeDao->getByAssoc($assocType, $assocId);
        $templateMgr->assign('announcementTypes', $announcementTypes);
        $templateMgr->assign('notificationToggle', $this->getData('notificationToggle'));

        return parent::display($request, $template);
    }

    /**
     * Initialize form data from current announcement.
     */
    public function initData() {
        if (isset($this->announcementId)) {
            $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
            $announcement = $announcementDao->getById($this->announcementId);

            if ($announcement != null) {
                $this->_data = [
                    'typeId' => $announcement->getTypeId(),
                    'assocType' => $announcement->getAssocType(),
                    'assocId' => $announcement->getAssocId(),
                    'title' => $announcement->getTitle(null), // Localized
                    'descriptionShort' => $announcement->getDescriptionShort(null), // Localized
                    'description' => $announcement->getDescription(null), // Localized
                    'datePosted' => $announcement->getDatePosted(),
                    'dateExpire' => $announcement->getDateExpire(),
                    'notificationToggle' => false,
                ];
            } else {
                $this->announcementId = null;
                $this->_data = [
                    'title' => [],            // [WIZDAM FIX] PHP 8 Safe Init
                    'descriptionShort' => [], // [WIZDAM FIX] PHP 8 Safe Init
                    'description' => [],      // [WIZDAM FIX] PHP 8 Safe Init
                    'datePosted' => Core::getCurrentDate(),
                    'notificationToggle' => true,
                ];
            }
        } else {
            // [WIZDAM FIX] Ensure localized arrays exist for new announcements
            $this->_data['title'] = [];
            $this->_data['descriptionShort'] = [];
            $this->_data['description'] = [];
            $this->_data['notificationToggle'] = true;
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['typeId', 'title', 'descriptionShort', 'description', 'notificationToggle']);
        $this->readUserDateVars(['dateExpire', 'datePosted']);
    }

    /**
     * Save announcement.
     * [WIZDAM FIX] Updated signature to accept optional request, matching child class
     * @param PKPRequest|null $request
     * @return Announcement
     */
    public function execute($request = null) {
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        if (isset($this->announcementId)) {
            $announcement = $announcementDao->getById($this->announcementId);
        }

        if (!isset($announcement)) {
            $announcement = $announcementDao->newDataObject();
        }

        // Give the parent class a chance to set the assocType/assocId.
        $this->_setAnnouncementAssocId($announcement);

        $announcement->setTitle($this->getData('title'), null); // Localized
        $announcement->setDescriptionShort($this->getData('descriptionShort'), null); // Localized
        $announcement->setDescription($this->getData('description'), null); // Localized

        if ($this->getData('typeId') != null) {
            $announcement->setTypeId($this->getData('typeId'));
        } else {
            $announcement->setTypeId(null);
        }

        // Give the parent class a chance to set the dateExpire.
        $dateExpireSet = $this->setDateExpire($announcement);
        if (!$dateExpireSet) {
            $announcement->setDateExpire($this->getData('dateExpire'));
        }
        $announcement->setDatetimePosted($this->getData('datePosted'));

        // Update or insert announcement
        if ($announcement->getId() != null) {
            $announcementDao->updateObject($announcement);
        } else {
            $announcement->setDatetimePosted(Core::getCurrentDate());
            $announcementDao->insertAnnouncement($announcement);
        }

        return $announcement;
    }


    //
    // Protected methods.
    //
    /**
     * Helper function to assign the date expire.
     * Must be implemented by subclasses.
     * @param Announcement $announcement the announcement to be modified
     * @return bool
     */
    public function setDateExpire($announcement) {
        return false;
    }


    //
    // Private methods.
    //
    /**
     * Get Assoc ID and Type
     * [WIZDAM FIX] Visibility set to protected to match child class
     * @return array
     */
    protected function _getAnnouncementTypesAssocId() {
        // must be implemented by sub-classes
        assert(false);
    }
    
    /**
     * Set Assoc ID
     * [WIZDAM FIX] Visibility set to protected to match child class
     */
    protected function _setAnnouncementAssocId($announcement) {
         // must be implemented by sub-classes
         assert(false);
    }
}
?>