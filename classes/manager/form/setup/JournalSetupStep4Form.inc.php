<?php
declare(strict_types=1);

/**
 * @file classes/manager/form/setup/JournalSetupStep4Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSetupStep4Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 4 of journal setup.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.manager.form.setup.JournalSetupForm');

class JournalSetupStep4Form extends JournalSetupForm {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            4,
            [
                'disableUserReg' => 'bool',
                'allowRegReader' => 'bool',
                'allowRegAuthor' => 'bool',
                'allowRegReviewer' => 'bool',
                'restrictSiteAccess' => 'bool',
                'restrictArticleAccess' => 'bool',
                'publicationFormatVolume' => 'bool',
                'publicationFormatNumber' => 'bool',
                'publicationFormatYear' => 'bool',
                'publicationFormatTitle' => 'bool',
                'initialVolume' => 'string',
                'initialNumber' => 'string',
                'initialYear' => 'int',
                'pubFreqPolicy' => 'string',
                'useCopyeditors' => 'bool',
                'copyeditInstructions' => 'string',
                'useLayoutEditors' => 'bool',
                'layoutInstructions' => 'string',
                'provideRefLinkInstructions' => 'bool',
                'refLinkInstructions' => 'string',
                'useProofreaders' => 'bool',
                'proofInstructions' => 'string',
                'publishingMode' => 'int',
                'showGalleyLinks' => 'bool',
                'openAccessPolicy' => 'string',
                'enableAnnouncements' => 'bool',
                'enableAnnouncementsHomepage' => 'bool',
                'numAnnouncementsHomepage' => 'int',
                'announcementsIntroduction' => 'string',
                'volumePerYear' => 'string',
                'issuePerVolume' => 'string',
                'enablePublicIssueId' => 'bool',
                'enablePublicArticleId' => 'bool',
                'enablePublicGalleyId' => 'bool',
                'enablePublicSuppFileId' => 'bool',
                'enablePageNumber' => 'bool'
            ]
        );
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalSetupStep4Form() {
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
            'pubFreqPolicy', 
            'copyeditInstructions', 
            'layoutInstructions', 
            'refLinkInstructions', 
            'proofInstructions', 
            'openAccessPolicy', 
            'announcementsIntroduction'
        ];
    }
}
?>