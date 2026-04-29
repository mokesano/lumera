<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customBlockManager/CustomBlockEditForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomBlockEditForm
 * @ingroup Form
 *
 * @brief Form for editing individual custom block content.
 */

import('lib.pkp.classes.form.Form');

class CustomBlockEditForm extends Form {

    /** @var CustomBlockPlugin */
    public $plugin;

    /** @var int */
    public $journalId;

    /**
     * Constructor
     * @param CustomBlockPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        parent::__construct($plugin->getTemplatePath() . 'editCustomBlockForm.tpl');
        $this->journalId = $journalId;
        $this->plugin = $plugin;
        
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidator($this, 'blockContent', 'required', 'plugins.generic.customBlock.contentRequired'));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param CustomBlockPlugin $plugin
     * @param int $journalId
     */
    public function CustomBlockEditForm($plugin, $journalId) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomBlockEditForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($plugin, $journalId);
    }

    /**
     * Initialize form data from the database.
     */
    public function initData() {
        $managerPlugin = $this->plugin->getManagerPlugin();
        $journal = Request::getJournal();
        
        $blocks = $managerPlugin->getSetting($journal->getId(), 'blocks');
        $blockContent = $managerPlugin->getSetting($journal->getId(), 'blockContent');
        
        $index = array_search($this->plugin->getName(), $blocks ?? []);
        
        // Pastikan selalu array berlokale, bukan string
        $content = [];
        if ($index !== false && isset($blockContent[$index])) {
            $contentData = $blockContent[$index];
            if (is_array($contentData)) {
                $content = $contentData; // Sudah format ['en_US' => '...']
            } else {
                // Legacy: data lama tersimpan sebagai string
                $locale = AppLocale::getLocale();
                $content = [$locale => $contentData];
            }
        }
        
        $this->setData('blockContent', $content); // ← Harus array
    }

    /**
     * Display the form.
     * Sets up the Action URL with proper encoding for spaces.
     */
    public function display($request = NULL, $template = NULL) {
        $templateMgr = TemplateManager::getManager();
        $this->addTinyMCE();
    
        // FIX 1: Register plugin_url agar dikenali Smarty
        $templateMgr->register_function(
            'plugin_url', 
            array($this->plugin, 'smartyPluginUrl')
        );
    
        // FIX 2: Assign formLocale dan formLocales
        $locale = AppLocale::getLocale();
        $journal = Request::getJournal();
        $supportedLocales = $journal 
            ? $journal->getSupportedLocaleNames() 
            : array($locale => AppLocale::getLocaleName($locale));
    
        $templateMgr->assign('formLocale', $locale);
        $templateMgr->assign('formLocales', $supportedLocales);
    
        // Set page title dan breadcrumbs
        $templateMgr->assign(
            'pageTitleTranslated', 
            __('plugins.generic.customBlock.editContent', 
                array('name' => $this->plugin->getDisplayName())
            )
        );
        $pageCrumbs = array(
            array(Request::url(null, 'user'), 'navigation.user'),
            array(Request::url(null, 'manager'), 'user.role.manager'),
            array(Request::url(null, 'manager', 'plugins'), 'manager.plugins')
        );
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    
        // FIX 3: Teruskan parameter ke parent
        parent::display($request, $template);
    }

    /**
     * Add TinyMCE scripts to the header.
     */
    public function addTinyMCE() {
        $journalId = $this->journalId;
        $templateMgr = TemplateManager::getManager();
        $additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');
        
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        
        $baseUrl = Request::getBaseUrl();
        $filesPath = $publicFileManager->getJournalFilesPath($journalId);
        
        // Ensure constant exists or fallback
        $tinyMcePath = defined('TINYMCE_JS_PATH') ? TINYMCE_JS_PATH : 'lib/pkp/lib/tinymce/jscripts/tiny_mce';

        $tinyMCE_script = '
        <script language="javascript" type="text/javascript" src="'.$baseUrl.'/'.$tinyMcePath.'/tiny_mce.js"></script>
        <script language="javascript" type="text/javascript">
            tinyMCE.init({
            mode : "textareas",
            plugins : "style,paste,jbimages",
            theme : "advanced",
            theme_advanced_buttons1 : "formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink",
            theme_advanced_buttons3 : "cut,copy,paste,pastetext,pasteword,|,cleanup,help,code,jbimages",
            theme_advanced_toolbar_location : "bottom",
            theme_advanced_toolbar_align : "left",
            content_css : "' . $baseUrl . '/styles/common.css", 
            relative_urls : false,
            document_base_url : "'. $baseUrl .'/'.$filesPath .'/", 
            extended_valid_elements : "span[*], div[*]"
            });
        </script>';
        
        $templateMgr->assign('additionalHeadData', $additionalHeadData."\n".$tinyMCE_script);
    }

    /**
     * Read input data.
     */
    public function readInputData() {
        $this->readUserVars(array('blockContent'));
    }
    
    /**
     * Get names of localized fields.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array('blockContent');
    }

    /**
     * Save the form data.
     */
    public function save() {
        $plugin = $this->plugin;
        $managerPlugin = $plugin->getManagerPlugin();
        $journalId = $this->journalId;

        $blocks = $managerPlugin->getSetting($journalId, 'blocks');
        $contents = $managerPlugin->getSetting($journalId, 'blockContent');

        if (!is_array($blocks)) return;
        if (!is_array($contents)) $contents = array();
        
        $index = array_search($plugin->getName(), $blocks);
        
        // Defensive check for decoded name
        if ($index === false) {
            $index = array_search(urldecode($plugin->getName()), $blocks);
        }

        if ($index !== false) {
            $contents[$index] = $this->getData('blockContent');
            ksort($contents); // Maintain array integrity
            $managerPlugin->updateSetting($journalId, 'blockContent', $contents);        
        }
    }
}
?>