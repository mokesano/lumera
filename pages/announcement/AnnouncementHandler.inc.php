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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & Null Safety
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

        // Mengambil data announcement
        $announcements = $this->_getAnnouncements($request);

        // LOGIKA ANTI-LOOP & CLEAN URL:
        if ($announcements->wasEmpty() && $request->getRequestedPage() === 'announcement') {
            
            $journal = $request->getJournal();
            
            // [FIX CRITICAL] Cek apakah journal ada sebelum memanggil getUrl()
            // Jika journal null, jangan redirect (atau redirect ke site index jika perlu)
            if ($journal) {
                $request->redirectUrl($journal->getUrl());
                return;
            }
        }

        // Jika ada isinya atau bukan halaman announcement langsung, panggil fungsi parent
        parent::index($args, $request);
    }

    /**
     * @see PKPAnnouncementHandler::_getAnnouncementsEnabled()
     * @param PKPRequest $request
     * @return bool
     */
    public function _getAnnouncementsEnabled($request) {
        $journal = $request->getJournal();
        // [FIX] Null coalescing logic
        return $journal ? (bool) $journal->getSetting('enableAnnouncements') : false;
    }

    /**
     * @see PKPAnnouncementHandler::_getAnnouncements()
     * [MODERNISASI] Menghapus tanda & reference return
     * @param PKPRequest $request
     * @param object|null $rangeInfo
     * @return DAOResultFactory
     */
    public function _getAnnouncements($request, $rangeInfo = null) {
        $journal = $request->getJournal();
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');

        // [FIX CRITICAL] Penyebab Fatal Error sebelumnya diperbaiki di sini.
        // Jika Journal NULL, gunakan ID 0 atau return kosong agar tidak crash.
        if ($journal) {
            $announcements = $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), $rangeInfo);
        } else {
            // Jika tidak ada konteks jurnal, kembalikan hasil kosong atau Site Level (sesuai kebutuhan)
            // Di sini kita ambil Site Level (ID 0) agar aman
            $announcements = $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_SITE, 0, $rangeInfo);
        }

        return $announcements;
    }

    /**
     * @see PKPAnnouncementHandler::_getAnnouncementsIntroduction()
     * @param PKPRequest $request
     * @return string
     */
    public function _getAnnouncementsIntroduction($request) {
        $journal = $request->getJournal();
        // [FIX] Return empty string if no journal
        return $journal ? (string) $journal->getLocalizedSetting('announcementsIntroduction') : '';
    }

    /**
     * @see PKPAnnouncementHandler::_announcementIsValid()
     * @param PKPRequest $request
     * @param int $announcementId
     * @return bool
     */
    public function _announcementIsValid($request, $announcementId) {
        $journal = $request->getJournal();
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        
        // [FIX] Pastikan journal tidak null sebelum cek ID
        if (!$journal) return false;

        return ($announcementId != null && $announcementDao->getAnnouncementAssocId($announcementId) == $journal->getId());
    }
}
?>