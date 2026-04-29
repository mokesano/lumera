<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/relatedItems/RelatedItemsBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RelatedItemsBlockPlugin
 * @ingroup plugins_blocks_related_items
 *
 * @brief Class for related items block plugin
 * [WIZDAM EDITION] Modernized & Cleaned Up Dead Code
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class RelatedItemsBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RelatedItemsBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RelatedItemsBlockPlugin(). Please refactor to parent::__construct().", 
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
        return __('plugins.block.relatedItems.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String
     */
    public function getDescription(): string {
        return __('plugins.block.relatedItems.description');
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
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        // 1. GUNAKAN LOGIKA MODERN
        $journal = $request->getJournal();
        if (!$journal) return '';

        // 2. Logika Original Blok
        // Pastikan kita berada di halaman artikel
        $article = $templateMgr->get_template_vars('article');
        if (!$article) return '';

        // [MODERNISASI] Ambil setting dengan default value array kosong
        $relatedItemsSettings = $this->getSetting($journal->getId(), 'relatedItems');
        if (!$relatedItemsSettings) return '';

        // [CLEANUP] Hapus DAO yang tidak terpakai (ArticleGalleyDAO & SuppFileDAO)
        // Data sudah ada di object $article.

        $relatedItems = array();

        // [WIZDAM SAFETY] Cek apakah method getRelatedItems ada sebelum dipanggil
        // Karena method ini tidak ada di BlockPlugin ataupun di file ini sebelumnya.
        if (!empty($relatedItemsSettings['relatedItems']) && method_exists($this, 'getRelatedItems')) {
            $relatedItems = $this->getRelatedItems($article);
        }
        
        if (!empty($relatedItemsSettings['suppFiles'])) {
            foreach ($article->getSuppFiles() as $suppFile) {
                if ($suppFile->getShowReviewers()) {
                    $relatedItems[] = $suppFile;
                }
            }
        }
        
        if (!empty($relatedItemsSettings['galleyFiles'])) {
            foreach ($article->getGalleys() as $galley) {
                if ($galley->getLocale() == AppLocale::getLocale()) {
                    $relatedItems[] = $galley;
                }
            }
        }

        $templateMgr->assign('relatedItems', $relatedItems);
        $templateMgr->assign('relatedItemsTitle', $journal->getLocalizedSetting('relatedItems'));
        
        // 3. SOLUSI BYPASS PARENT (WAJIB)
        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
}

?>