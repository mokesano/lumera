<?php
declare(strict_types=1);

/**
 * @file pages/manager/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for announcement management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * FIX: Removed recursive constructor calls (Infinite Loop).
 */

import('pages.manager.ManagerHandler');

class PKPAnnouncementHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Index handler.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $this->announcements($args, $request);
    }

    /**
     * Display a list of announcements for the current context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function announcements($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // FIXME: Remove call to validate() when all ManagerHandler implementations
        // (across all apps) have been migrated to the authorize() authorization approach.
        $this->validate();
        $this->setupTemplate($request);

        // [WIZDAM FIX] Handler::getRangeInfo is non-static. 
        // We use $this if available, otherwise new Handler()
        $rangeInfo = $this->getRangeInfo('announcements', []);
        
        while (true) {
            $announcements = $this->_getAnnouncements($request, $rangeInfo);
            if ($announcements->isInBounds()) break;
            unset($rangeInfo);
            $rangeInfo = $announcements->getLastPageRangeInfo();
            unset($announcements);
        }

        $templateMgr = TemplateManager::getManager();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('announcements', $announcements);
        $templateMgr->display('manager/announcement/announcements.tpl');
    }

    /**
     * Get the announcements for this request.
     * @param PKPRequest $request
     * @param mixed $rangeInfo optional
     * @return ItemIterator
     */
    public function _getAnnouncements($request, $rangeInfo = null) {
        // must be implemented by sub-classes
        assert(false);
        // [WIZDAM] PHP 8 require return value for reference return type
        $null = null;
        return $null; 
    }

    /**
     * Delete an announcement.
     * @param array $args first parameter ID of the announcement to delete
     * @param PKPRequest $request
     */
    public function deleteAnnouncement($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();

        if (isset($args) && !empty($args)) {
            $announcementId = (int) $args[0];

            $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

            // Ensure announcement is for this context
            if ($this->_announcementIsValid($request, $announcementId)) {
                $announcementDao->deleteAnnouncementById($announcementId);
            }
        }

        $router = $request->getRouter();
        $request->redirectUrl($router->url($request, null, null, 'announcements'));
    }

    /**
     * Display form to edit an announcement.
     * @param array $args first parameter is the ID of the announcement to edit
     * @param PKPRequest $request
     */
    public function editAnnouncement($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        $announcementId = !isset($args) || empty($args) ? null : (int) $args[0];
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        // Ensure announcement is valid and for this context
        if ($this->_announcementIsValid($request, $announcementId)) {
            import('classes.manager.form.AnnouncementForm');

            $templateMgr = TemplateManager::getManager();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'announcements'), 'manager.announcements']);

            if ($announcementId == null) {
                $templateMgr->assign('announcementTitle', 'manager.announcements.createTitle');
            } else {
                $templateMgr->assign('announcementTitle', 'manager.announcements.editTitle');
            }

            $contextId = $this->getContextId($request);

            $announcementForm = new AnnouncementForm($contextId, $announcementId);
            if ($announcementForm->isLocaleResubmit()) {
                $announcementForm->readInputData();
            } else {
                $announcementForm->initData();
            }
            $announcementForm->display();

        } else {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, null, 'announcements'));
        }
    }

    /**
     * Display form to create new announcement.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createAnnouncement($args, $request) {
        $this->editAnnouncement($args, $request);
    }

    /**
     * Save changes to an announcement.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateAnnouncement($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        $router = $request->getRouter();

        import('classes.manager.form.AnnouncementForm');

        $announcementId = $request->getUserVar('announcementId') == null ? null : (int) $request->getUserVar('announcementId');
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        if ($this->_announcementIsValid($request, $announcementId)) {

            $contextId = $this->getContextId($request);

            $announcementForm = new AnnouncementForm($contextId, $announcementId);
            $announcementForm->readInputData();

            if ($announcementForm->validate()) {
                $announcementForm->execute($request);

                if ((int) $request->getUserVar('createAnother')) {
                    $request->redirectUrl($router->url($request, null, null, 'createAnnouncement'));
                } else {
                    $request->redirectUrl($router->url($request, null, null, 'announcements'));
                }

            } else {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->append('pageHierarchy', [$request->url(null, null, 'manager', 'announcements'), 'manager.announcements']);

                if ($announcementId == null) {
                    $templateMgr->assign('announcementTitle', 'manager.announcements.createTitle');
                } else {
                    $templateMgr->assign('announcementTitle', 'manager.announcements.editTitle');
                }

                $announcementForm->display();
            }
        } else {
            $request->redirectUrl($router->url($request, null, null, 'announcements'));
        }
    }

    /**
     * Display a list of announcement types for the current context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function announcementTypes($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        // [WIZDAM FIX] Non-static call
        $rangeInfo = $this->getRangeInfo('announcementTypes', []);
        
        while (true) {
            $announcementTypes = $this->_getAnnouncementTypes($request, $rangeInfo);
            if ($announcementTypes->isInBounds()) break;
            unset($rangeInfo);
            $rangeInfo = $announcementTypes->getLastPageRangeInfo();
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('announcementTypes', $announcementTypes);
        $templateMgr->display('manager/announcement/announcementTypes.tpl');
    }

    /**
     * Delete an announcement type.
     * @param array $args first parameter ID of the announcement type to delete
     * @param PKPRequest $request
     */
    public function deleteAnnouncementType($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();

        if (isset($args) && !empty($args)) {
            $typeId = (int) $args[0];

            $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');

            // Ensure announcement is for this context
            if ($this->_announcementTypeIsValid($request, $typeId)) {
                $announcementTypeDao->deleteById($typeId);
            }
        }

        $router = $request->getRouter();
        $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
    }

    /**
     * Display form to edit an announcement type.
     * @param array $args first parameter ID of the announcement type to edit
     * @param PKPRequest $request
     */
    public function editAnnouncementType($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        $typeId = !isset($args) || empty($args) ? null : (int) $args[0];
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');

        // Ensure announcement type is valid and for this context
        if ($this->_announcementTypeIsValid($request, $typeId)) {
            import('classes.manager.form.AnnouncementTypeForm');

            $templateMgr = TemplateManager::getManager();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'announcementTypes'), 'manager.announcementTypes']);

            if ($typeId == null) {
                $templateMgr->assign('announcementTypeTitle', 'manager.announcementTypes.createTitle');
            } else {
                $templateMgr->assign('announcementTypeTitle', 'manager.announcementTypes.editTitle');
            }

            $announcementTypeForm = new AnnouncementTypeForm($typeId);
            if ($announcementTypeForm->isLocaleResubmit()) {
                $announcementTypeForm->readInputData();
            } else {
                $announcementTypeForm->initData();
            }
            $announcementTypeForm->display();

        } else {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
        }
    }

    /**
     * Display form to create new announcement type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createAnnouncementType($args, $request) {
        $this->editAnnouncementType($args, $request);
    }

    /**
     * Save changes to an announcement type.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateAnnouncementType($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        $router = $request->getRouter();

        import('classes.manager.form.AnnouncementTypeForm');

        $typeId = $request->getUserVar('typeId') == null ? null : (int) $request->getUserVar('typeId');
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');

        if ($this->_announcementTypeIsValid($request, $typeId)) {
            $announcementTypeForm = new AnnouncementTypeForm($typeId);
            $announcementTypeForm->readInputData();

            if ($announcementTypeForm->validate()) {
                $announcementTypeForm->execute();

                if ((int) $request->getUserVar('createAnother')) {
                    $request->redirectUrl($router->url($request, null, null, 'createAnnouncementType'));
                } else {
                    $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
                }
            } else {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->append('pageHierarchy', [$request->url(null, null, 'manager', 'announcementTypes'), 'manager.announcementTypes']);

                if ($typeId == null) {
                    $templateMgr->assign('announcementTypeTitle', 'manager.announcementTypes.createTitle');
                } else {
                    $templateMgr->assign('announcementTypeTitle', 'manager.announcementTypes.editTitle');
                }

                $announcementTypeForm->display();
            }
        } else {
            $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
        }
    }

    /**
     * Set up the template with breadcrumbs etc.
     * @param PKPRequest $request
     * @param bool $subclass
     */
    public function setupTemplate($request = null, $subclass = false) {
        // Note: Original code used Registry::get('request') inside setupTemplate but argument was passed. 
        // We prioritize argument if valid.
        if (!$request || !is_object($request)) {
             $request = Registry::get('request');
        }
        
        parent::setupTemplate(true);
        if ($subclass) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'announcements'), 'manager.announcements']);
        }
    }


    //
    // Protected methods.
    //
    /**
     * Get the current context id in request.
     * @param PKPRequest $request
     * @return int|null
     */
    public function getContextId($request) {
        // must be implemented by sub-classes
        assert(false);
    }


    //
    // Private helper methods.
    //
    /**
     * Check if the announcement is valid and belongs to the current context.
     * @param PKPRequest $request
     * @param int|null $announcementId
     * @return bool
     */
    public function _announcementIsValid($request, $announcementId = null) {
        // must be implemented by sub-classes
        assert(false);
    }

    /**
     * Get the announcement types for this request.
     * @param PKPRequest $request
     * @param mixed $rangeInfo optional
     * @return ItemIterator
     */
    public function _announcementTypeIsValid($request, $typeId = null) {
        // must be implemented by sub-classes
        assert(false);
    }
}
?>