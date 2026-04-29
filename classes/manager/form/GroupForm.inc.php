<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/GroupForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupForm
 * @ingroup manager_form
 * @see Group
 *
 * @brief Form for journal managers to create/edit groups.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.group.Group');

class GroupForm extends Form {
    /** @var Group|null the group being edited */
    public $group = null;

    /**
     * Constructor
     * @param Group|null $group Group object; null to create new
     */
    public function __construct($group = null) {
        // [WIZDAM FIX] Explicit parent constructor
        parent::__construct('manager/groups/groupForm.tpl');

        // Group title is provided
        $this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.groups.form.groupTitleRequired'));

        $this->addCheck(new FormValidatorPost($this));

        $this->group = $group;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GroupForm($group = null) {
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
     * Get the list of localized field names for this object
     * @return array
     */
    public function getLocaleFieldNames() {
        $groupDao = DAORegistry::getDAO('GroupDAO');
        return $groupDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        
        // [WIZDAM FIX] assign_by_ref is deprecated/unnecessary for objects in modern PHP
        $templateMgr->assign('group', $this->group);
        
        $templateMgr->assign('helpTopicId', 'journal.managementPages.groups');
        $templateMgr->assign('groupContextOptions', [
            GROUP_CONTEXT_EDITORIAL_TEAM => 'manager.groups.context.editorialTeam',
            GROUP_CONTEXT_PEOPLE => 'manager.groups.context.people'
        ]);
        
        parent::display($request, $template);
    }

    /**
     * Initialize form data from current group group.
     */
    public function initData() {
        if ($this->group != null) {
            $this->_data = [
                'title' => $this->group->getTitle(null), // Localized
                'publishEmail' => $this->group->getPublishEmail(),
                'context' => $this->group->getContext()
            ];
        } else {
            $this->_data = [
                'publishEmail' => 1,
                'context' => GROUP_CONTEXT_EDITORIAL_TEAM
            ];
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['title', 'context', 'publishEmail']);
    }

    /**
     * Save group group.
     */
    public function execute($object = NULL) {
        $groupDao = DAORegistry::getDAO('GroupDAO');
        
        // [WIZDAM] Singleton Pattern
        $journal = Application::get()->getRequest()->getJournal();

        if (!isset($this->group)) {
            $this->group = $groupDao->newDataObject();
        }

        $this->group->setAssocType(ASSOC_TYPE_JOURNAL);
        $this->group->setAssocId($journal->getId());
        $this->group->setTitle($this->getData('title'), null); // Localized
        $this->group->setContext($this->getData('context'));
        $this->group->setPublishEmail($this->getData('publishEmail'));

        // Eventually this will be a general Groups feature; for now,
        // we're just using it to display journal team entries in About.
        $this->group->setAboutDisplayed(true);

        // Update or insert group group
        if ($this->group->getId() != null) {
            $groupDao->updateObject($this->group);
        } else {
            // [WIZDAM] Ensure REALLY_BIG_NUMBER is defined or handle gracefully if missing (usually in Core)
            $this->group->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 99999);
            $groupDao->insertGroup($this->group);

            // Re-order the groups so the new one is at the end of the list.
            $groupDao->resequenceGroups($this->group->getAssocType(), $this->group->getAssocId());
        }
    }
}

?>