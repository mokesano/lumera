<?php
declare(strict_types=1);

/**
 * @file plugins/generic/xmlGalley/XMLGalleyPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLGalleyPlugin
 * @ingroup plugins_generic_xmlGalley
 *
 * @brief XML Galley Plugin
 * MODERNIZED FOR PHP 7.4+ & SCHOLARWIZDAM FORK
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class XMLGalleyPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLGalleyPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Register the plugin to the registry.
     * @see Plugin::register()
     * @param string $category
     * @param string $path
     * @return bool True on successful registration, false on failure
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                $this->import('ArticleXMLGalleyDAO');
                $xmlGalleyDao = new ArticleXMLGalleyDAO($this->getName());
                DAORegistry::registerDAO('ArticleXMLGalleyDAO', $xmlGalleyDao);

                // NB: These hooks essentially modify/overload the existing ArticleGalleyDAO methods
                HookRegistry::register('ArticleGalleyDAO::getArticleGalleys', array($xmlGalleyDao, 'appendXMLGalleys') );
                HookRegistry::register('ArticleGalleyDAO::insertNewGalley', array($xmlGalleyDao, 'insertXMLGalleys') );
                HookRegistry::register('ArticleGalleyDAO::deleteGalleyById', array($xmlGalleyDao, 'deleteXMLGalleys') );
                HookRegistry::register('ArticleGalleyDAO::incrementGalleyViews', array($xmlGalleyDao, 'incrementXMLViews') );
                HookRegistry::register('ArticleGalleyDAO::_returnGalleyFromRow', array($this, 'returnXMLGalley') );
                HookRegistry::register('ArticleGalleyDAO::getNewGalley', array($this, 'getXMLGalley') );

                // This hook is required in the absence of hooks in the viewFile and download methods
                HookRegistry::register( 'ArticleHandler::viewFile', array($this, 'viewXMLGalleyFile') );
                HookRegistry::register( 'ArticleHandler::downloadFile', array($this, 'viewXMLGalleyFile') );
            }

            return true;
        }
        return false;
    }

    /**
     * Get display name
     * @see Plugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.xmlGalley.displayName');
    }

    /**
     * Get description
     * @see Plugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.xmlGalley.description');
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/' . 'schema.xml';
    }

    /**
     * Return XML-derived galley object.
     * @param $hookName string
     * @param $args array
     * @return boolean
     */
    public function getXMLGalley($hookName, $args) {
        if (!$this->getEnabled()) return false;
        
        // WIZDAM FIX: Type cast arguments to integer to satisfy strict_types in DAO
        $galleyId = isset($args[0]) ? (int) $args[0] : null;
        $articleId = (isset($args[1]) && $args[1] !== null) ? (int) $args[1] : null;
        
        $returner =& $args[2]; // Reference required to modify return value

        $xmlGalleyDao = new ArticleXMLGalleyDAO($this->getName());
        $xmlGalley = $xmlGalleyDao->_getXMLGalleyFromId($galleyId, $articleId);
        if ($xmlGalley) {
            $xmlGalley->setId($galleyId);
            $returner = $xmlGalley;
            return true;
        }
        return false;
    }

    /**
     * Return XML-derived galley as a file; basically this is a FO-rendered PDF file
     * @param $hookName string
     * @param $args array
     * @return boolean
     */
    public function viewXMLGalleyFile($hookName, $args) {
        if (!$this->getEnabled()) return false;
        $article = $args[0];
        $galley = $args[1];
        $fileId = $args[2];

        $journal = Request::getJournal();

        if (get_class($galley) == 'ArticleXMLGalley' && $galley->isPdfGalley() &&
            $this->getSetting($journal->getId(), 'nlmPDF') == 1) {
            return $galley->viewFileContents();
        } else return false;
    }

    /**
     * Append some special attributes to a galley identified as XML, and
     * Return an ArticleXMLGalley object as appropriate
     * @param $hookName string
     * @param $args array
     * @return boolean
     */
    public function returnXMLGalley($hookName, $args) {
        if (!$this->getEnabled()) return false;
        $galley =& $args[0]; // Reference required to modify galley object
        $row = $args[1];

        // If the galley is an XML file, then convert it from an HTML Galley to an XML Galley
        if ($galley->getFileType() == "text/xml" || $galley->getFileType() == "application/xml") {
            $galley = $this->_returnXMLGalleyFromArticleGalley($galley);
            return true;
        }

        return false;
    }

    /**
     * Internal function to return an ArticleXMLGalley object from an ArticleGalley object
     * @param $galley ArticleGalley
     * @return ArticleXMLGalley
     */
    public function _returnXMLGalleyFromArticleGalley($galley) {
        $this->import('ArticleXMLGalley');
        $articleXMLGalley = new ArticleXMLGalley($this->getName());

        // Create XML Galley with previous values
        $articleXMLGalley->setId($galley->getId());
        $articleXMLGalley->setArticleId($galley->getArticleId());
        $articleXMLGalley->setFileId($galley->getFileId());
        $articleXMLGalley->setLabel($galley->getLabel());
        $articleXMLGalley->setSequence($galley->getSequence());
        $articleXMLGalley->setFileName($galley->getFileName());
        $articleXMLGalley->setOriginalFileName($galley->getOriginalFileName());
        $articleXMLGalley->setFileType($galley->getFileType());
        $articleXMLGalley->setFileSize($galley->getFileSize());
        $articleXMLGalley->setDateModified($galley->getDateModified());
        $articleXMLGalley->setDateUploaded($galley->getDateUploaded());
        $articleXMLGalley->setLocale($galley->getLocale());

        $articleXMLGalley->setType('public');
        $articleXMLGalley->setFileStage($galley->getFileStage());

        // Copy CSS and image file references from source galley
        if ($galley->isHTMLGalley()) {
            $articleXMLGalley->setStyleFileId($galley->getStyleFileId());
            $articleXMLGalley->setStyleFile($galley->getStyleFile());
            $articleXMLGalley->setImageFiles($galley->getImageFiles());
        }

        return $articleXMLGalley;
    }

    /**
     * Set the enabled/disabled state of this plugin.
     * @see Plugin::setEnabled()
     * @param $enabled bool
     * @return bool
     */
    public function setEnabled(bool $enabled, $request = NULL): bool {
        parent::setEnabled($enabled, $request);
        $journal = Request::getJournal();
        if ($journal) {
            // set default XSLT renderer
            if ($this->getSetting($journal->getId(), 'XSLTrenderer') == "") {

                if ( extension_loaded('xsl') && extension_loaded('dom') ) {
                    $this->updateSetting($journal->getId(), 'XSLTrenderer', 'Native');
                } else {
                    $this->updateSetting($journal->getId(), 'XSLTrenderer', 'external');
                }
            }

            // set default XSL stylesheet to NLM
            if ($this->getSetting($journal->getId(), 'XSLstylesheet') == "") {
                $this->updateSetting($journal->getId(), 'XSLstylesheet', 'NLM');
            }

            return true;
        }
        return false;
    }


    /**
     * Display verbs for the management interface.
     * @see Plugin::getManagementVerbs()
     * @param $verbs array Existing management verbs
     * @param $request PKPRequest|null
     * @return array Modified management verbs
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
       $verbs = parent::getManagementVerbs($verbs, $request);

       if ($this->getEnabled($request)) {
           $verbs[] = array('settings', __('plugins.generic.xmlGalley.manager.settings'));
       }
       return $verbs;
    }

    /**
     * Execute a management verb on this plugin.
     * @see Plugin::manage()
     * @param $verb string
     * @param $args array
     * @param $message string Result status message
     * @param $messageParams array Parameters for the message key
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        $journal = Request::getJournal();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));

        $this->import('XMLGalleySettingsForm');
        $form = new XMLGalleySettingsForm($this, $journal->getId());

        switch ($verb) {
            case 'test':
                // test external XSLT renderer
                $xsltRenderer = $this->getSetting($journal->getId(), 'XSLTrenderer');

                if ($xsltRenderer == "external") {
                    // get command for external XSLT tool
                    $xsltCommand = $this->getSetting($journal->getId(), 'externalXSLT');

                    // get test XML/XSL files
                    $xmlFile = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . $this->getPluginPath() . '/transform/test.xml';
                    $xslFile = $this->getPluginPath() . '/transform/test.xsl';

                    // create a testing article galley object (to access the XSLT render method)
                    $this->import('ArticleXMLGalley');
                    $xmlGalley = new ArticleXMLGalley($this->getName());

                    // transform the XML using whatever XSLT processor we have available
                    $result = $xmlGalley->transformXSLT($xmlFile, $xslFile, $xsltCommand);

                    // check the result
                    if (trim(preg_replace("/\s+/", " ", $result)) != "Open Journal Systems Success" ) {
                        $form->addError('content', __('plugins.generic.xmlGalley.settings.externalXSLTFailure'));
                    } else $templateMgr->assign('testSuccess', true);

                }

            case 'settings':
                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
                // if we are updating XSLT settings or switching XSL sheets
                if (Request::getUserVar('save')) {
                    $form->readInputData();
                    $form->initData();
                    if ($form->validate()) {
                        $form->execute();
                    }
                    $form->display();

                // if we are uploading a custom XSL sheet
                } elseif (Request::getUserVar('uploadCustomXSL')) {
                    $form->readInputData();

                    import('classes.file.JournalFileManager');

                    // if the a valid custom XSL is uploaded, process it
                    $fileManager = new JournalFileManager($journal);
                    if ($fileManager->uploadedFileExists('customXSL')) {

                        // check type and extension -- should be text/xml and xsl, respectively
                        $type = $fileManager->getUploadedFileType('customXSL');
                        $fileName = $fileManager->getUploadedFileName('customXSL');
                        $extension = strtolower_codesafe($fileManager->getExtension($fileName));

                        if (($type == 'text/xml' || $type == 'text/xsl' || $type == 'application/xml' || $type == 'application/xslt+xml')
                            && $extension == 'xsl') {

                            // if there is an existing XSL file, delete it from the journal files folder
                            $existingFile = $this->getSetting($journal->getId(), 'customXSL');
                            if (!empty($existingFile) && $fileManager->fileExists($fileManager->filesDir . $existingFile)) {
                                $fileManager->deleteFile($existingFile);
                            }

                            // upload the file into the journal files folder
                            $fileManager->uploadFile('customXSL', $fileName);

                            // update the plugin and form settings
                            $this->updateSetting($journal->getId(), 'XSLstylesheet', 'custom');
                            $this->updateSetting($journal->getId(), 'customXSL', $fileName);

                        } else $form->addError('content', __('plugins.generic.xmlGalley.settings.customXSLInvalid'));

                    } else $form->addError('content', __('plugins.generic.xmlGalley.settings.customXSLRequired'));

                    // re-populate the form values with the new settings
                    $form->initData();
                    $form->display();

                // if we are deleting an existing custom XSL sheet
                } elseif (Request::getUserVar('deleteCustomXSL')) {

                    import('classes.file.JournalFileManager');

                    // if the a valid custom XSL is uploaded, process it
                    $fileManager = new JournalFileManager($journal);

                    // delete the file from the journal files folder
                    $fileName = $this->getSetting($journal->getId(), 'customXSL');
                    if (!empty($fileName)) $fileManager->deleteFile($fileName);

                    // update the plugin and form settings
                    $this->updateSetting($journal->getId(), 'XSLstylesheet', 'NLM');
                    $this->updateSetting($journal->getId(), 'customXSL', '');


                    $form->initData();
                    $form->display();

                } else {
                    $form->initData();
                    $form->display();
                }
                return true;
            default:
                // Unknown management verb
                assert(false);
                return false;
        }
    }
}
?>