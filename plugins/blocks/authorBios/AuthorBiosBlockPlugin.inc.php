<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/authorBios/AuthorBiosBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorBiosBlockPlugin
 * @ingroup plugins_blocks_author_bios
 *
 * @brief Class for author bios block plugin
 * [WIZDAM STATUS] Legacy/Redundant. 
 * Note: Core Wizdam has its own author display logic. This plugin is modernized 
 * only for safety/fallback purposes to prevent PHP 8 Fatal Errors.
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class AuthorBiosBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorBiosBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AuthorBiosBlockPlugin(). Please refactor to parent::__construct().", 
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
        return __('plugins.block.authorBios.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String
     */
    public function getDescription(): string {
        return __('plugins.block.authorBios.description');
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
     * @param $templateMgr object
     * @param $request PKPRequest
     * @return $string
     */
    public function getContents($templateMgr, $request = null) {
        // [WIZDAM] LOGIKA KUNO (Request::) KITA PERTAHANKAN
        // Menggabungkan Page dan Op untuk pengecekan switch agar kompatibel dengan OJS 2 logic
        $currentPage = Request::getRequestedPage();
        $currentOp = Request::getRequestedOp();

        switch ($currentPage . '/' . $currentOp) {
            case 'article/view':
                // Cek apakah variabel 'article' ada di template (mencegah error on null)
                if (!$templateMgr->get_template_vars('article')) return '';
                
                // [WIZDAM] SOLUSI BYPASS PARENT (WAJIB UNTUK OJS 2 BLOCK PLUGIN)
                // Mengambil filename template secara manual
                $templateFilename = $this->getBlockTemplateFilename($request);
                if ($templateFilename === null) return '';
                
                // Fetch template langsung
                return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
            default:
                return '';
        }
    }
}

?>