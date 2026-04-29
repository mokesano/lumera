<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/role/RoleBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleBlockPlugin
 * @ingroup plugins_blocks_role
 *
 * @brief Class for role block plugin
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class RoleBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RoleBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RoleBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Install default settings on journal creation.
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     */
    public function getDisplayName(): string {
        return __('plugins.block.role.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        return __('plugins.block.role.description');
    }

    /**
     * Override the block contents based on the current role being browsed.
     * @param PKPRequest $request
     * @return string|null
     */
    public function getBlockTemplateFilename($request = null) {
        // [WIZDAM] Gunakan $request yang diinjeksi
        // Jika null (dipanggil dari konteks lama), coba ambil global
        if (!$request) $request = Application::getRequest();

        $journal = $request->getJournal();
        $user = $request->getUser();

        if (!$journal || !$user) {
            return null;
        }

        $userId = $user->getId();
        $journalId = $journal->getId();

        $templateMgr = TemplateManager::getManager();

        switch ($request->getRequestedPage()) {
            case 'author': 
                switch ($request->getRequestedOp()) {
                    case 'submit':
                    case 'saveSubmit':
                    case 'submitSuppFile':
                    case 'saveSubmitSuppFile':
                    case 'deleteSubmitSuppFile':
                    case 'expediteSubmission':
                        return null;
                    default:
                        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
                        $submissionsCount = $authorSubmissionDao->getSubmissionsCount($userId, $journalId);
                        $templateMgr->assign('submissionsCount', $submissionsCount);
                        return 'author.tpl';
                }
            case 'copyeditor':
                $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
                $submissionsCount = $copyeditorSubmissionDao->getSubmissionsCount($userId, $journalId);
                $templateMgr->assign('submissionsCount', $submissionsCount);
                return 'copyeditor.tpl';
            case 'layoutEditor':
                $layoutEditorSubmissionDao = DAORegistry::getDAO('LayoutEditorSubmissionDAO');
                $submissionsCount = $layoutEditorSubmissionDao->getSubmissionsCount($userId, $journalId);
                $templateMgr->assign('submissionsCount', $submissionsCount);
                return 'layoutEditor.tpl';
            case 'editor':
                if ($request->getRequestedOp() == 'index') return null;
                $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
                $submissionsCount = $editorSubmissionDao->getEditorSubmissionsCount($journal->getId());
                $templateMgr->assign('submissionsCount', $submissionsCount);
                return 'editor.tpl';
            case 'sectionEditor':
                $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
                $submissionsCount = $sectionEditorSubmissionDao->getSectionEditorSubmissionsCount($userId, $journalId);
                $templateMgr->assign('submissionsCount', $submissionsCount);
                return 'sectionEditor.tpl';
            case 'proofreader':
                $proofreaderSubmissionDao = DAORegistry::getDAO('ProofreaderSubmissionDAO');
                $submissionsCount = $proofreaderSubmissionDao->getSubmissionsCount($userId, $journalId);
                $templateMgr->assign('submissionsCount', $submissionsCount);
                return 'proofreader.tpl';
            case 'reviewer':
                $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
                $submissionsCount = $reviewerSubmissionDao->getSubmissionsCount($userId, $journalId);
                $templateMgr->assign('submissionsCount', $submissionsCount);
                return 'reviewer.tpl';
        }
        return null;
    }

    /**
     * Get the HTML contents for this block.
     * [WIZDAM] Explicit override to ensure compatibility
     */
    public function getContents($templateMgr, $request = null) {
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>