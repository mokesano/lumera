<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/information/InformationBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationBlockPlugin
 * @ingroup plugins_blocks_information
 *
 * @brief Class for information block plugin
 * [WIZDAM EDITION] Repurposed: Author/Reviewer/Editor Information Center
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class InformationBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function InformationBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::InformationBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Install default settings on journal creation.
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.block.information.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String
     */
    public function getDescription(): string {
        return __('plugins.block.information.description');
    }

    /**
     * Get the HTML contents for this block.
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // 1. GUNAKAN LOGIKA MODERN
        $journal = $request->getJournal();
        if (!$journal) return '';

        // 2. [WIZDAM TRANSFORMATION]
        // Kita menggunakan kolom database lama untuk tujuan baru agar tidak perlu migrasi SQL.
        // readerInformation    -> Disajikan sebagai Info Reviewer
        // authorInformation    -> Tetap Info Author
        // librarianInformation -> Disajikan sebagai Info Editor
        
        $templateMgr->assign('forAuthors', $journal->getLocalizedSetting('authorInformation'));
        
        // Mapping Reader -> Reviewer
        $templateMgr->assign('forReviewers', $journal->getLocalizedSetting('readerInformation'));
        
        // Mapping Librarian -> Editor
        $templateMgr->assign('forEditors', $journal->getLocalizedSetting('librarianInformation'));
        
        // 3. SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>