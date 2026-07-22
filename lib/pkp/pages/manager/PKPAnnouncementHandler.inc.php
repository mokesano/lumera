<?php
declare(strict_types=1);

/**
 * @file pages/manager/PKPAnnouncementHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAnnouncementHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for announcement management functions.
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
     * @param PKPRequest|null $request
     */
    public function index($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->announcements($args, $request);
    }

    /**
     * Display a list of announcements for the current context.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function announcements($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate($request);

        $rangeInfo = $this->getRangeInfo('announcements', []);
        
        while (true) {
            $announcements = $this->_getAnnouncements($request, $rangeInfo);
            if ($announcements->isInBounds()) {
                break;
            }
            $rangeInfo = $announcements->getLastPageRangeInfo();
        }

        $templateMgr = TemplateManager::getManager($request);
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
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Delete an announcement.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function deleteAnnouncement($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();

        if (!empty($args)) {
            $announcementId = (int) $args[0];

            /** @var AnnouncementDAO $announcementDao */
            $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
            if ($this->_announcementIsValid($request, $announcementId)) {
                $announcementDao->deleteAnnouncementById($announcementId);
            }
        }

        $router = $request->getRouter();
        $request->redirectUrl($router->url($request, null, null, 'announcements'));
    }

    /**
     * Display form to edit an announcement.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function editAnnouncement($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate($request);

        $announcementId = empty($args) ? null : (int) $args[0];
        
        /** @var AnnouncementDAO $announcementDao */
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        if ($this->_announcementIsValid($request, $announcementId)) {
            import('classes.manager.form.AnnouncementForm');

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->append('pageHierarchy', [
                $request->url(null, 'manager', 'announcements'), 
                'manager.announcements'
            ]);

            $templateMgr->assign('announcementTitle', $announcementId === null ? 'manager.announcements.createTitle' : 'manager.announcements.editTitle');

            $contextId = $this->getContextId($request);

            $announcementForm = new AnnouncementForm($contextId, $announcementId);
            if ($announcementForm->isLocaleResubmit()) {
                $announcementForm->readInputData();
            } else {
                $announcementForm->initData();
            }
            $announcementForm->display($request);

        } else {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, null, 'announcements'));
        }
    }

    /**
     * Display form to create new announcement.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function createAnnouncement($args, $request) {
        $this->editAnnouncement($args, $request);
    }

    /**
     * Save changes to an announcement.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function updateAnnouncement($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate($request);

        $router = $request->getRouter();
        import('classes.manager.form.AnnouncementForm');

        $announcementIdVar = $request->getUserVar('announcementId');
        $announcementId = $announcementIdVar === null ? null : (int) $announcementIdVar;
        
        /** @var AnnouncementDAO $announcementDao */
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
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->append('pageHierarchy', [
                    $request->url(null, 'manager', 'announcements'), 
                    'manager.announcements'
                ]);

                $templateMgr->assign('announcementTitle', $announcementId === null ? 'manager.announcements.createTitle' : 'manager.announcements.editTitle');
                $announcementForm->display($request);
            }
        } else {
            $request->redirectUrl($router->url($request, null, null, 'announcements'));
        }
    }

    /**
     * Display a list of announcement types for the current context.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function announcementTypes($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate($request, true);

        $rangeInfo = $this->getRangeInfo('announcementTypes', []);
        $router = $request->getRouter();
        $context = $router->getContext($request);
        
        if (!$context) {
            $request->redirect(null, 'index');
            return;
        }

        $assocType = $context->getAssocType();
        $contextId = (int) $context->getId();

        /** @var AnnouncementTypeDAO $announcementTypeDao */
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        $announcementTypes = $announcementTypeDao->getByAssoc($assocType, $contextId, $rangeInfo);
        while (!$announcementTypes->isInBounds()) {
            $rangeInfo = $announcementTypes->getLastPageRangeInfo();
            $announcementTypes = $announcementTypeDao->getByAssoc($assocType, $contextId, $rangeInfo);
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('announcementTypes', $announcementTypes);
        $templateMgr->display('manager/announcement/announcementTypes.tpl');
    }

    /**
     * Delete an announcement type.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function deleteAnnouncementType($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();

        if (!empty($args)) {
            $typeId = (int) $args[0];

            /** @var AnnouncementTypeDAO $announcementTypeDao */
            $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');

            if ($this->_announcementTypeIsValid($request, $typeId)) {
                $announcementTypeDao->deleteById($typeId);
            }
        }

        $router = $request->getRouter();
        $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
    }

    /**
     * Display form to edit an announcement type.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function editAnnouncementType($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate($request, true);

        $typeId = empty($args) ? null : (int) $args[0];
        
        /** @var AnnouncementTypeDAO $announcementTypeDao */
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');

        if ($this->_announcementTypeIsValid($request, $typeId)) {
            import('classes.manager.form.AnnouncementTypeForm');

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->append('pageHierarchy', [
                $request->url(null, 'manager', 'announcementTypes'), 
                'manager.announcementTypes'
            ]);

            $templateMgr->assign('announcementTypeTitle', $typeId === null ? 'manager.announcementTypes.createTitle' : 'manager.announcementTypes.editTitle');

            $announcementTypeForm = new AnnouncementTypeForm($typeId);
            if ($announcementTypeForm->isLocaleResubmit()) {
                $announcementTypeForm->readInputData();
            } else {
                $announcementTypeForm->initData();
            }
            $announcementTypeForm->display($request);

        } else {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
        }
    }

    /**
     * Display form to create new announcement type.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function createAnnouncementType($args, $request) {
        $this->editAnnouncementType($args, $request);
    }

    /**
     * Save changes to an announcement type.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function updateAnnouncementType($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate($request, true);

        $router = $request->getRouter();
        import('classes.manager.form.AnnouncementTypeForm');

        $typeIdVar = $request->getUserVar('typeId');
        $typeId = $typeIdVar === null ? null : (int) $typeIdVar;
        
        /** @var AnnouncementTypeDAO $announcementTypeDao */
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
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->append('pageHierarchy', [
                    $request->url(null, 'manager', 'announcementTypes'), 
                    'manager.announcementTypes'
                ]);

                $templateMgr->assign('announcementTypeTitle', $typeId === null ? 'manager.announcementTypes.createTitle' : 'manager.announcementTypes.editTitle');
                $announcementTypeForm->display($request);
            }
        } else {
            $request->redirectUrl($router->url($request, null, null, 'announcementTypes'));
        }
    }

    /**
     * Set up the template with breadcrumbs etc.
     * @param PKPRequest|null $request
     * @param bool $subclass
     */
    public function setupTemplate($request = null, $subclass = false) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        parent::setupTemplate();
        
        if ($subclass) {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->append('pageHierarchy', [
                $request->url(null, 'manager', 'announcements'), 
                'manager.announcements'
            ]);
        }
    }

    //
    // Protected methods.
    //
    /**
     * Get the current context id in request.
     * @param PKPRequest $request
     * @return int
     */
    public function getContextId($request) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
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
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Check if the announcement type is valid and belongs to the current context.
     * @param PKPRequest $request
     * @param int|null $typeId
     * @return bool
     */
    public function _announcementTypeIsValid($request, $typeId = null) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

}
?>