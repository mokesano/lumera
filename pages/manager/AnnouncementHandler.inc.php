<?php
declare(strict_types=1);

/**
 * @file pages/manager/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for announcement management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.pages.manager.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
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
     * Display a list of announcements for the current journal.
     * @see PKPAnnouncementHandler::announcements
     * @param array $args
     * @param PKPRequest $request
     */
    public function announcements($args, $request) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'journal.managementPages.announcements');
        parent::announcements($args, $request);
    }

    /**
     * Display a list of announcement types for the current journal.
     * @see PKPAnnouncementHandler::announcementTypes
     * @param array $args
     * @param PKPRequest $request
     */
    public function announcementTypes($args, $request) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'journal.managementPages.announcements');
        parent::announcementTypes($args, $request);
    }

    /**
     * @see PKPAnnouncementHandler::getContextId()
     * @param PKPRequest $request
     * @return int|null
     */
    public function getContextId($request) {
        $journal = $request->getJournal();
        if ($journal) {
            return (int) $journal->getId();
        }
        return null;
    }

    /**
     * @see PKPAnnouncementHandler::_getAnnouncements
     * [WIZDAM] Removed & reference return
     * @param PKPRequest $request
     * @param object|null $rangeInfo
     * @return DAOResultFactory
     */
    public function _getAnnouncements($request, $rangeInfo = null) {
        $journalId = $this->getContextId($request);
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        $announcements = $announcementDao->getByAssocId(ASSOC_TYPE_JOURNAL, (int) $journalId, $rangeInfo);

        return $announcements;
    }

    /**
     * @see PKPAnnouncementHandler::_getAnnouncementTypes
     * [WIZDAM] Removed & reference return
     * @param PKPRequest $request
     * @param object|null $rangeInfo
     * @return DAOResultFactory
     */
    public function _getAnnouncementTypes($request, $rangeInfo = null) {
        $journalId = $this->getContextId($request);
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        $announcements = $announcementTypeDao->getByAssoc(ASSOC_TYPE_JOURNAL, (int) $journalId, $rangeInfo);

        return $announcements;
    }

    /**
     * Checks the announcement to see if it belongs to this journal or scheduled journal
     * @param PKPRequest $request
     * @param int|null $announcementId
     * @return bool
     */
    public function _announcementIsValid($request, $announcementId = null) {
        if ($announcementId == null) return true;

        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        $announcement = $announcementDao->getById($announcementId);

        $journalId = $this->getContextId($request);
        if ($announcement && $journalId
            && $announcement->getAssocType() == ASSOC_TYPE_JOURNAL
            && $announcement->getAssocId() == $journalId) {
            return true;
        }

        return false;
    }

    /**
     * Checks the announcement type to see if it belongs to this journal.  All announcement types are set at the journal level.
     * @param PKPRequest $request
     * @param int|null $typeId
     * @return bool
     */
    public function _announcementTypeIsValid($request, $typeId = null) {
        $journalId = $this->getContextId($request);
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        $announcementType = $announcementTypeDao->getById($typeId);
        return (($announcementType && $announcementType->getAssocId() == $journalId && $announcementType->getAssocType() == ASSOC_TYPE_JOURNAL) || $typeId == null);
    }
}
?>