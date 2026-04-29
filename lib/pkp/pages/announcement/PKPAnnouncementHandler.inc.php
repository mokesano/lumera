<?php
declare(strict_types=1);

/**
 * @file pages/announcement/PKPAnnouncementHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAnnouncementHandler
 * @ingroup pages_announcement
 *
 * @brief Handle requests for public announcement functions.
 *
 * [WIZDAM EDITION] PHP 8.1+ Compatibility, Strict Types, Visibility Modifiers
 */

import('classes.handler.Handler');

class PKPAnnouncementHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display announcement index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index(array $args = [], $request = null) {
        $this->validate();
        $this->setupTemplate($request);

        if ($this->_getAnnouncementsEnabled($request)) {
            $rangeInfo = Handler::getRangeInfo('announcements');

            $announcements = $this->_getAnnouncements($request, $rangeInfo);
            $announcementsIntroduction = $this->_getAnnouncementsIntroduction($request);

            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('announcements', $announcements);
            $templateMgr->assign('announcementsIntroduction', $announcementsIntroduction);
            $templateMgr->display('announcement/index.tpl');
        } else {
            $request->redirect();
        }
    }

    /**
     * View announcement details.
     * @param array $args first parameter is the ID of announcement to display
     * @param PKPRequest $request
     */
    public function view($args, $request) {
        $this->validate();
        $this->setupTemplate($request);

        $announcementId = !isset($args) || empty($args) ? null : (int) $args[0];
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        if ($this->_getAnnouncementsEnabled($request) && $this->_announcementIsValid($request, $announcementId)) {
            $announcement = $announcementDao->getById($announcementId);

            // [Wizdam Fix] Check for null dateExpire or future expiration
            if ($announcement->getDateExpire() == null || strtotime($announcement->getDateExpire()) > time()) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('announcement', $announcement);
                
                if (!$announcement->getTypeId()) {
                    $templateMgr->assign('announcementTitle', $announcement->getLocalizedTitle());
                } else {
                    $templateMgr->assign('announcementTitle', $announcement->getAnnouncementTypeName() . ": " . $announcement->getLocalizedTitle());
                }
                
                $templateMgr->append('pageHierarchy', [$request->url(null, 'announcement'), 'announcement.announcements']);
                $templateMgr->display('announcement/view.tpl');
            } else {
                $request->redirect(null, 'announcement');
            }
        } else {
            $request->redirect(null, 'announcement');
        }
    }

    /**
     * Setup common template variables.
     * @param PKPRequest $request
     * @param bool $subclass
     */
    public function setupTemplate($request = null, $subclass = false) {
        parent::setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        
        // [Wizdam Refactor] Modern array syntax
        $templateMgr->assign('pageHierachy', [[$request->url(null, null, 'announcements'), 'announcement.announcements']]);
    }

    /**
     * Returns true when announcements are enabled 
     * in the context, otherwise false.
     * @param PKPRequest $request
     * @return bool
     */
    protected function _getAnnouncementsEnabled($request) {
        // must be implemented by sub-classes
        assert(false);
        return false;
    }

    /**
     * Returns a list of (non-expired) announcements for this context.
     * @param PKPRequest $request
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory|null
     */
    protected function _getAnnouncements($request, $rangeInfo = null) {
        // must be implemented by sub-classes
        assert(false);
        return null;
    }

    /**
     * Returns an introductory text to be displayed with the announcements.
     * @param PKPRequest $request
     * @return string|null
     */
    protected function _getAnnouncementsIntroduction($request) {
        // must be implemented by sub-classes
        assert(false);
        return null;
    }

    /**
     * Checks whether the given announcement is valid for display.
     * @param PKPRequest $request
     * @param int $announcementId
     * @return bool
     */
    protected function _announcementIsValid($request, $announcementId) {
        // must be implemented by sub-classes
        assert(false);
        return false;
    }
}
?>