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
    protected $_plugin;

    /** @var int */
    protected $_journalId;

    /**
     * Constructor.
     * @param CustomBlockPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        parent::__construct($plugin->getTemplatePath() . 'editCustomBlockForm.tpl');
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;
        
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidator($this, 'blockContent', 'required', 'plugins.generic.customBlock.contentRequired'));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param CustomBlockPlugin $plugin
     * @param int $journalId
     */
    public function CustomBlockEditForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Initialize form data from the database.
     * @return void
     */
    public function initData() {
        $managerPlugin = $this->_plugin->getManagerPlugin();
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : $this->_journalId;
        
        $blocks = $managerPlugin->getSetting($journalId, 'blocks');
        $blockContent = $managerPlugin->getSetting($journalId, 'blockContent');
        
        $index = array_search($this->_plugin->getName(), is_array($blocks) ? $blocks : [], true);
        
        $content = [];
        if ($index !== false && is_array($blockContent) && isset($blockContent[$index])) {
            $contentData = $blockContent[$index];
            if (is_array($contentData)) {
                $content = $contentData;
            } else {
                $locale = AppLocale::getLocale();
                $content = [$locale => (string) $contentData];
            }
        }
        
        $this->setData('blockContent', $content);
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $request = $request ?? Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $this->addTinyMCE($request);
    
        $templateMgr->register_function('plugin_url', [$this->_plugin, 'smartyPluginUrl']);
    
        $locale = AppLocale::getLocale();
        $journal = $request->getJournal();
        $supportedLocales = $journal !== null ? $journal->getSupportedLocaleNames() : [$locale => AppLocale::getLocale()];
    
        $templateMgr->assign('formLocale', $locale);
        $templateMgr->assign('formLocales', $supportedLocales);
        $templateMgr->assign('pageTitleTranslated', __('plugins.generic.customBlock.editContent', ['name' => $this->_plugin->getDisplayName()]));
        
        $router = $request->getRouter();
        $pageCrumbs = [
            [$router->url($request, null, 'user'), 'navigation.user'],
            [$router->url($request, null, 'manager'), 'user.role.manager'],
            [$router->url($request, null, 'manager', 'plugins'), 'manager.plugins']
        ];
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    
        parent::display($request, $template);
    }

    /**
     * Add TinyMCE scripts to the header.
     * @param object|null $request
     * @return void
     */
    public function addTinyMCE($request = null) {
        $request = $request ?? Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : $this->_journalId;
        
        $templateMgr = TemplateManager::getManager($request);
        $additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');
        
        // [LUMERA FIX] Ensure $additionalHeadData is a string to prevent "Expected type 'string'. Found 'array'" warning
        $headDataString = is_string($additionalHeadData) 
            ? $additionalHeadData 
            : (is_array($additionalHeadData) ? implode("\n", $additionalHeadData) : '');
        
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        
        $baseUrl = $request->getBaseUrl();
        $filesPath = $publicFileManager->getJournalFilesPath($journalId);
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
        
        $templateMgr->assign('additionalHeadData', $headDataString . "\n" . $tinyMCE_script);
    }

    /**
     * Read input data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['blockContent']);
    }
    
    /**
     * Get names of localized fields.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['blockContent'];
    }

    /**
     * Save the form data.
     * @return void
     */
    public function save() {
        $plugin = $this->_plugin;
        $managerPlugin = $plugin->getManagerPlugin();
        $journalId = $this->_journalId;

        $blocks = $managerPlugin->getSetting($journalId, 'blocks');
        $contents = $managerPlugin->getSetting($journalId, 'blockContent');

        if (!is_array($blocks)) {
            return;
        }
        if (!is_array($contents)) {
            $contents = [];
        }
        
        $index = array_search($plugin->getName(), $blocks, true);
        
        if ($index === false) {
            $index = array_search(urldecode($plugin->getName()), $blocks, true);
        }

        if ($index !== false) {
            $contents[$index] = $this->getData('blockContent');
            ksort($contents);
            $managerPlugin->updateSetting($journalId, 'blockContent', $contents);        
        }
    }

}
?>