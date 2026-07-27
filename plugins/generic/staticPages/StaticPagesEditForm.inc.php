<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesEditForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesEditForm
 * @ingroup plugins_generic_staticPages
 *
 * @brief Form for journal managers to view and modify static pages.
 */

import('lib.pkp.classes.form.Form');

class StaticPagesEditForm extends Form {
    
    /** @var int */
    protected $_journalId;

    /** @var GenericPlugin */
    protected $_plugin;

    /** @var int|null */
    protected $_staticPageId;

    /** @var string */
    public $_errors;

    /**
     * Constructor.
     * @param GenericPlugin $plugin
     * @param int $journalId
     * @param int|null $staticPageId
     */
    public function __construct($plugin, $journalId, $staticPageId = null) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;
        $this->_staticPageId = $staticPageId !== null ? (int) $staticPageId : null;

        parent::__construct($plugin->getTemplatePath() . 'editStaticPageForm.tpl');

        $this->addCheck(new FormValidatorCustom(
            $this, 
            'pagePath', 
            'required', 
            'plugins.generic.staticPages.duplicatePath', 
            [$this, 'checkForDuplicatePath'], 
            [$this->_journalId, $this->_staticPageId]
        ));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param GenericPlugin $plugin
     * @param int $journalId
     * @param int|null $staticPageId
     */
    public function StaticPagesEditForm($plugin, $journalId, $staticPageId = null) {
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
     * Custom Form Validator for PATH to ensure no duplicate PATHs are created.
     * @param string $pagePath
     * @param int $journalId
     * @param int|null $staticPageId
     * @return bool
     */
    public function checkForDuplicatePath($pagePath, $journalId, $staticPageId) {
        /** @var StaticPagesDAO $staticPageDao */
        $staticPageDao = DAORegistry::getDAO('StaticPagesDAO');

        return !$staticPageDao->duplicatePathExists((string) $pagePath, (int) $journalId, $staticPageId !== null ? (int) $staticPageId : null);
    }

    /**
     * Initialize form data from current group.
     * @return void
     */
    public function initData() {
        $this->addTinyMCE();

        if ($this->_staticPageId !== null) {
            /** @var StaticPagesDAO $staticPageDao */
            $staticPageDao = DAORegistry::getDAO('StaticPagesDAO');
            $staticPage = $staticPageDao->getStaticPage($this->_staticPageId);

            if ($staticPage !== null) {
                $this->_data = [
                    'staticPageId' => $staticPage->getId(),
                    'pagePath' => $staticPage->getPath(),
                    'title' => $staticPage->getTitle(null),
                    'content' => $staticPage->getContent(null)
                ];
            } else {
                $this->_staticPageId = null;
            }
        }
    }

    /**
     * Add TinyMCE to the form.
     * @return void
     */
    public function addTinyMCE() {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        // [WIZDAM FIX] Cast to string to satisfy linter expecting string|Stringable|null
        $additionalHeadData = (string) $templateMgr->get_template_vars('additionalHeadData');
        $baseUrl = (string) $request->getBaseUrl();

        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $journalFilesPath = $publicFileManager->getJournalFilesPath($this->_journalId);

        $tinyMCE_script = '
        <script type="text/javascript" src="' . $baseUrl . '/' . TINYMCE_JS_PATH . '/tiny_mce.js"></script>
        <script type="text/javascript">
            tinyMCE.init({
                mode : "textareas",
                plugins : "safari,spellchecker,style,layer,table,save,advhr,jbimages,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,pagebreak,jbimages",
                theme_advanced_buttons1_add : "fontsizeselect",
                theme_advanced_buttons2_add : "separator,preview,separator,forecolor,backcolor",
                theme_advanced_buttons2_add_before: "search,replace,separator",
                theme_advanced_buttons3_add_before : "tablecontrols,separator",
                theme_advanced_buttons3_add : "media,separator",
                theme_advanced_buttons4 : "cut,copy,paste,pastetext,pasteword,separator,styleprops,|,spellchecker,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,print,separator",
                theme_advanced_disable: "styleselect",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                relative_urls : false,
                document_base_url : "' . $baseUrl . '/' . $journalFilesPath . '/",
                theme : "advanced",
                theme_advanced_layout_manager : "SimpleLayout",
                extended_valid_elements : "span[*], div[*]",
                spellchecker_languages : "+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv"
            });
        </script>';

        $templateMgr->assign('additionalHeadData', $additionalHeadData . "\n" . $tinyMCE_script);
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['staticPageId', 'pagePath', 'title', 'content']);
    }

    /**
     * Get the names of localized fields.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['title', 'content'];
    }

    /**
     * Save page into DB.
     * @return void
     */
    public function save() {
        $this->_plugin->import('StaticPage');
        
        /** @var StaticPagesDAO $staticPagesDao */
        $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');
        
        $staticPage = null;
        if ($this->_staticPageId !== null) {
            $staticPage = $staticPagesDao->getStaticPage($this->_staticPageId);
        }

        if ($staticPage === null) {
            $staticPage = new StaticPage();
        }

        $staticPage->setJournalId($this->_journalId);
        $staticPage->setPath((string) $this->getData('pagePath'));
        $staticPage->setTitle((string) $this->getData('title'), null);
        $staticPage->setContent((string) $this->getData('content'), null);

        if ($this->_staticPageId !== null) {
            $staticPagesDao->updateStaticPage($staticPage);
        } else {
            $staticPagesDao->insertStaticPage($staticPage);
        }
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param mixed $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        parent::display($request, $template);
    }

}
?>