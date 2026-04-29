<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/AnnouncementTypeForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementTypeForm
 * @ingroup manager_form
 * @see AnnouncementType
 *
 * @brief Form for journal managers to create/edit announcement types.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.manager.form.PKPAnnouncementTypeForm');

class AnnouncementTypeForm extends PKPAnnouncementTypeForm {
    
    /**
     * Constructor
     * @param int|null $typeId leave as default for new announcement type
     */
    public function __construct($typeId = null) {
        // [WIZDAM FIX] Call parent constructor explicitly
        parent::__construct($typeId);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AnnouncementTypeForm($typeId = null) {
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
     * Helper function to assign the AssocType and the AssocId
     * [WIZDAM FIX] Visibility MUST BE PUBLIC to match parent definition
     * @param AnnouncementType $announcementType the announcement type to be modified
     */
    public function _setAnnouncementTypeAssocId($announcementType) {
        // [WIZDAM] Use Application Singleton instead of static Request
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        // [WIZDAM] Removed & reference
        if ($journal) {
            $announcementType->setAssocType(ASSOC_TYPE_JOURNAL);
            $announcementType->setAssocId($journal->getId());
        }
    }
}
?>