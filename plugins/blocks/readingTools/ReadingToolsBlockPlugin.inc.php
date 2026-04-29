<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/readingTools/ReadingToolsBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReadingToolsBlockPlugin
 * @ingroup plugins_blocks_reading_tools
 *
 * @brief Class for reading tools block plugin
 * [WIZDAM EDITION] Modernized Syntax.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class ReadingToolsBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReadingToolsBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ReadingToolsBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.block.readingTools.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription(): string {
        return __('plugins.block.readingTools.description');
    }

    /**
     * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
     * @return array
     */
    public function getSupportedContexts() {
        return array(BLOCK_CONTEXT_RIGHT_SIDEBAR);
    }

    /**
     * Get the HTML contents for this block.
     * [WIZDAM] Explicit implementation to ensure PHP 8 safety
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // [WIZDAM] Safety Check: Reading Tools biasanya hanya relevan di halaman artikel
        // Jika Anda ingin menghemat resource, bisa tambahkan cek ini:
        /*
        $currentPage = Request::getRequestedPage();
        if ($currentPage != 'article') return '';
        */

        // SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>