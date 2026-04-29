<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/setup/JournalSetupStep2Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSetupStep2Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 2 of journal setup.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.manager.form.setup.JournalSetupForm');

class JournalSetupStep2Form extends JournalSetupForm {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            2,
            [
                'focusScopeDesc' => 'string',
                'numWeeksPerReview' => 'int',
                'remindForInvite' => 'bool',
                'remindForSubmit' => 'bool',
                'numDaysBeforeInviteReminder' => 'int',
                'numDaysBeforeSubmitReminder' => 'int',
                'rateReviewerOnQuality' => 'bool',
                'restrictReviewerFileAccess' => 'bool',
                'reviewerAccessKeysEnabled' => 'bool',
                'showEnsuringLink' => 'bool',
                'reviewPolicy' => 'string',
                'mailSubmissionsToReviewers' => 'bool',
                'reviewGuidelines' => 'string',
                'authorSelectsEditor' => 'bool',
                'privacyStatement' => 'string',
                'customAboutItems' => 'object',
                'enableLockss' => 'bool',
                'lockssLicense' => 'string',
                'reviewerDatabaseLinks' => 'object',
                'notifyAllAuthorsOnDecision' => 'bool'
            ]
        );

        $this->addCheck(new FormValidatorEmail($this, 'envelopeSender', 'optional', 'user.profile.form.emailRequired'));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSetupStep2Form() {
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
     * Get the list of field names for which localized settings are used.
     * @return array
     */
    public function getLocaleFieldNames() {
        return [
            'focusScopeDesc', 
            'reviewPolicy', 
            'reviewGuidelines', 
            'privacyStatement', 
            'customAboutItems', 
            'lockssLicense'
        ];
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Request Singleton
        $request = $request ?? Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager();
        if (Config::getVar('general', 'scheduled_tasks')) {
            $templateMgr->assign('scheduledTasksEnabled', true);
        }

        parent::display($request, $template);
    }
}
?>