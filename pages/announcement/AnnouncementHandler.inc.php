<?php
declare(strict_types=1);

/**
 * @file pages/announcement/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_announcement
 *
 * @brief Handle requests for public announcement functions.
 */

import('lib.pkp.pages.announcement.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        // Validator ini berusaha memastikan ada Journal, tapi kita tetap harus coding defensif
        $this->addCheck(new HandlerValidatorJournal($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementHandler() {
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
     * OVERRIDE: Tambahan logika Redirect jika Announcement kosong
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $announcements = $this->_getAnnouncements($request);
        if ($announcements->wasEmpty() && $request->getRequestedPage() === 'announcement') {
            $journal = $request->getJournal();
            if ($journal) {
                $request->redirectUrl($journal->getUrl());
                return;
            }
        }

        parent::index($args, $request);
    }

    //
    // Helper Method
    /**
     * HELPER: Get announcements enabled
     * @see PKPAnnouncementHandler::_getAnnouncementsEnabled()
     * @param PKPRequest $request
     * @return bool
     */
    public function _getAnnouncementsEnabled($request) {
        $journal = $request->getJournal();
        return $journal ? (bool) $journal->getSetting('enableAnnouncements') : false;
    }

    /**
     * HELPER: Get announcements
     * @see PKPAnnouncementHandler::_getAnnouncements()
     * @param PKPRequest $request
     * @param object|null $rangeInfo
     * @return DAOResultFactory
     */
    public function _getAnnouncements($request, $rangeInfo = null) {
        $journal = $request->getJournal();
        /** @var AnnouncementDAO $announcementDao */
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        if ($journal) {
            $announcements = $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), $rangeInfo);
        } else {
            $announcements = $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_SITE, 0, $rangeInfo);
        }

        return $announcements;
    }

    /**
     * HELPER: Get announcements introduction
     * @see PKPAnnouncementHandler::_getAnnouncementsIntroduction()
     * @param PKPRequest $request
     * @return string
     */
    public function _getAnnouncementsIntroduction($request) {
        $journal = $request->getJournal();
        return $journal ? (string) $journal->getLocalizedSetting('announcementsIntroduction') : '';
    }

    /**
     * HELPER: Announcement is valid
     * @see PKPAnnouncementHandler::_announcementIsValid()
     * @param PKPRequest $request
     * @param int $announcementId
     * @return bool
     */
    public function _announcementIsValid($request, $announcementId) {
        $journal = $request->getJournal();
        /** @var AnnouncementDAO $announcementDao */
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        if (!$journal) return false;

        return ($announcementId != null && $announcementDao->getAnnouncementAssocId($announcementId) == $journal->getId());
    }
    
}
?>