<?php
declare(strict_types=1);

/**
 * @defgroup manager_form
 */

/**
 * @file classes/manager/form/AnnouncementForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementForm
 * @ingroup manager_form
 *
 * @brief Form for journal managers to create/edit announcements.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.manager.form.PKPAnnouncementForm');

class AnnouncementForm extends PKPAnnouncementForm {
    
    /**
     * Constructor
     * @param int $journalId
     * @param int|null $announcementId leave as default for new announcement
     */
    public function __construct($journalId, $announcementId = null) {
        // [WIZDAM FIX] Explicit parent constructor call
        parent::__construct($journalId, $announcementId);

        // [WIZDAM FIX] Replaced deprecated create_function with anonymous closure
        // If provided, announcement type is valid
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'typeId', 
            'optional', 
            'manager.announcements.form.typeIdValid', 
            function($typeId) use ($journalId) {
                $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
                return $announcementTypeDao->announcementTypeExistsByTypeId($typeId, ASSOC_TYPE_JOURNAL, $journalId);
            }, 
            [$journalId]
        ));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementForm($journalId, $announcementId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'journal.managementPages.announcements');
        parent::display($request, $template);
    }

    /**
     * Get the Assoc ID and Type for this announcement
     * [WIZDAM] Protected visibility matched in parent
     * @return array [ASSOC_TYPE_..., int]
     */
    protected function _getAnnouncementTypesAssocId() {
        $journalId = $this->getContextId();
        return [ASSOC_TYPE_JOURNAL, $journalId];
    }

    /**
     * Helper function to assign the AssocType and the AssocId
     * [WIZDAM] Protected visibility matched in parent
     * @param Announcement $announcement the announcement to be modified
     */
    protected function _setAnnouncementAssocId($announcement) {
        $journalId = $this->getContextId();
        // [WIZDAM] Removed & reference, objects are passed by identifier
        $announcement->setAssocType(ASSOC_TYPE_JOURNAL);
        $announcement->setAssocId($journalId);
    }

    /**
     * Save announcement.
     * @param Request|null $request
     * @return Announcement
     */
    public function execute($request = null) {
        // [WIZDAM] Removed & from $request signature
        // Call parent execute with request argument to satisfy signature
        $announcement = parent::execute($request);
        $journalId = $this->getContextId();

        if ($this->getData('notificationToggle')) {
            // Send a notification to associated users
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $roleDao = DAORegistry::getDAO('RoleDAO');
            
            $notificationUsers = [];
            $allUsers = $roleDao->getUsersByJournalId($journalId);
            
            while (!$allUsers->eof()) {
                $user = $allUsers->next();
                $notificationUsers[] = ['id' => $user->getId()];
                unset($user);
            }
            
            foreach ($notificationUsers as $userRole) {
                $notificationManager->createNotification(
                    $request, 
                    (int) $userRole['id'], 
                    NOTIFICATION_TYPE_NEW_ANNOUNCEMENT,
                    $journalId, 
                    ASSOC_TYPE_ANNOUNCEMENT, 
                    (int) $announcement->getId()
                );
            }
            
            $notificationManager->sendToMailingList($request,
                $notificationManager->createNotification(
                    $request, 
                    UNSUBSCRIBED_USER_NOTIFICATION, 
                    NOTIFICATION_TYPE_NEW_ANNOUNCEMENT,
                    $journalId, 
                    ASSOC_TYPE_ANNOUNCEMENT, 
                    (int) $announcement->getId()
                )
            );
        }
        
        return $announcement;
    }
}
?>