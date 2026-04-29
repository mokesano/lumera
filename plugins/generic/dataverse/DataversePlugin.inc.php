<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/DataversePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataversePlugin
 * @ingroup plugins_generic_dataverse
 *
 * @brief Dataverse plugin class
 * [WIZDAM EDITION] Refactored into a Clean Architecture Orchestration Hub for PHP 8.4
 */

import('lib.pkp.classes.plugins.GenericPlugin');

// HTTP status codes
define('DATAVERSE_PLUGIN_HTTP_STATUS_OK',         200);
define('DATAVERSE_PLUGIN_HTTP_STATUS_CREATED',    201);
define('DATAVERSE_PLUGIN_HTTP_STATUS_NO_CONTENT', 204);

// Dataverse field delimiters
define('DATAVERSE_PLUGIN_TOU_POLICY_SEPARATOR', '---');
define('DATAVERSE_PLUGIN_SUBJECT_SEPARATOR', ';');

// Default format of publication citation in dataset metadata
define('DATAVERSE_PLUGIN_CITATION_FORMAT_APA', 'APA');

// Study release options
define('DATAVERSE_PLUGIN_RELEASE_ARTICLE_ACCEPTED',  0x01);
define('DATAVERSE_PLUGIN_RELEASE_ARTICLE_PUBLISHED', 0x02);

// Notification types
define('NOTIFICATION_TYPE_DATAVERSE_STUDY_CREATED',  NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001001);
define('NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED',  NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001002);
define('NOTIFICATION_TYPE_DATAVERSE_FILE_ADDED',     NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001003);
define('NOTIFICATION_TYPE_DATAVERSE_FILE_DELETED',   NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001004);
define('NOTIFICATION_TYPE_DATAVERSE_STUDY_DELETED',  NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001005);
define('NOTIFICATION_TYPE_DATAVERSE_STUDY_RELEASED', NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001006);
define('NOTIFICATION_TYPE_DATAVERSE_UNRELEASED',     NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001007);
define('NOTIFICATION_TYPE_DATAVERSE_ERROR',          NOTIFICATION_TYPE_PLUGIN_BASE + 0x0001008);

class DataversePlugin extends GenericPlugin {

    /**
     * Register the plugin to make it available in the system.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        
        if ($success && $this->getEnabled()) {
            // Register DAOs
            $this->import('classes.DataverseStudyDAO');
            $dataverseStudyDao = new DataverseStudyDAO($this->getName());
            DAORegistry::registerDAO('DataverseStudyDAO', $dataverseStudyDao);

            $this->import('classes.DataverseFileDAO');          
            $dataverseFileDao = new DataverseFileDAO($this->getName());         
            DAORegistry::registerDAO('DataverseFileDAO', $dataverseFileDao);

            // [WIZDAM ARCHITECTURE] Instantiate Delegators
            $this->import('classes.api.DataverseApiClient');
            $this->import('classes.services.StudyService');
            $this->import('classes.hooks.FormHookDelegator');
            $this->import('classes.hooks.UIHookDelegator');

            $apiClient = new DataverseApiClient($this);
            $studyService = new StudyService($this, $apiClient);
            $uiDelegator = new UIHookDelegator($this);
            $formDelegator = new FormHookDelegator($this, $studyService, $apiClient);

            // [WIZDAM FIX] Delegate UI Hooks (No pass-by-reference ampersands)
            HookRegistry::register('LoadHandler', [$uiDelegator, 'setupPublicHandler']);
            HookRegistry::register('TemplateManager::display', [$uiDelegator, 'handleTemplateDisplay']);
            HookRegistry::register('Templates::Article::MoreInfo', [$uiDelegator, 'addDataCitationArticle']);
            HookRegistry::register('TinyMCEPlugin::getEnableFields', [$uiDelegator, 'getTinyMCEEnabledFields']);
            HookRegistry::register('Templates::About::Index::Policies', [$uiDelegator, 'addPolicyLinks']);
            HookRegistry::register('NotificationManager::getNotificationContents', [$uiDelegator, 'getNotificationContents']);
            
            // [WIZDAM FIX] Delegate Form & Workflow Hooks
            HookRegistry::register('Templates::Author::Submit::SuppFile::AdditionalMetadata', [$formDelegator, 'suppFileAdditionalMetadata']);
            HookRegistry::register('authorsubmitsuppfileform::initdata', [$formDelegator, 'suppFileFormInitData']);
            HookRegistry::register('authorsubmitsuppfileform::readuservars', [$formDelegator, 'suppFileFormReadUserVars']);
            HookRegistry::register('authorsubmitsuppfileform::execute', [$formDelegator, 'authorSubmitSuppFileFormExecute']);
            
            HookRegistry::register('Templates::Submission::SuppFile::AdditionalMetadata', [$formDelegator, 'suppFileAdditionalMetadata']);
            HookRegistry::register('suppfileform::initdata', [$formDelegator, 'suppFileFormInitData']);
            HookRegistry::register('suppfileform::readuservars', [$formDelegator, 'suppFileFormReadUserVars']);
            HookRegistry::register('suppfileform::execute', [$formDelegator, 'suppFileFormExecute']);
            
            HookRegistry::register('articledao::getAdditionalFieldNames', [$formDelegator, 'articleMetadataFormFieldNames']);
            HookRegistry::register('authorsubmitsuppfileform::Constructor', [$formDelegator, 'suppFileFormConstructor']);
            HookRegistry::register('suppfileform::Constructor', [$formDelegator, 'suppFileFormConstructor']);   
            
            HookRegistry::register('metadataform::Constructor', [$formDelegator, 'metadataFormConstructor']);
            HookRegistry::register('metadataform::execute', [$formDelegator, 'metadataFormExecute']);
            
            HookRegistry::register('suppfiledao::_insertsuppfile', [$formDelegator, 'handleSuppFileInsertion']);
            HookRegistry::register('suppfiledao::_deletesuppfilebyid', [$formDelegator, 'handleSuppFileDeletion']);
            
            HookRegistry::register('authorsubmitstep4form::Constructor', [$formDelegator, 'addAuthorSubmitFormValidator']);
            HookRegistry::register('Author::SubmitHandler::saveSubmit', [$formDelegator, 'handleAuthorSubmission']);
            
            HookRegistry::register('SectionEditorAction::unsuitableSubmission', [$formDelegator, 'handleUnsuitableSubmission']);
            HookRegistry::register('SectionEditorAction::recordDecision', [$formDelegator, 'handleEditorDecision']);
            HookRegistry::register('articledao::_updatearticle', [$formDelegator, 'handleArticleUpdate']);
        }
        return $success;
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.dataverse.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.dataverse.description');
    }

    /**
     * Get the name of the schema file to install on new journal creation.
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/schema.xml';
    }

    /**
     * Get the path to the plugin's handler directory.
     * @return string
     */
    public function getHandlerPath(): string {
        return $this->getPluginPath() . '/pages/';
    }
    
    /**
     * Get the path to the plugin's template directory.
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }   
    
    /**
     * Get management verbs (actions) for this plugin.
     * @param array $verbs Existing verbs to merge with
     * @param Request|null $request Optional request to check for context
     * @return array List of verbs with their localized names
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        
        if ($this->getEnabled($request)) {
            $verbs[] = ['connect', __('plugins.generic.dataverse.settings.connect')];
            $verbs[] = ['select', __('plugins.generic.dataverse.settings.selectDataverse')]; 
            $verbs[] = ['settings', __('plugins.generic.dataverse.settings')];
        }
        return $verbs;
    }

    /**
     * Handle management actions for this plugin.
     * @param string|null $verb The management verb (action) to handle
     * @param array $args Additional arguments for the action
     * @param string $message Message to display (if any)
     * @param array $messageParams Parameters for the message (if any)
     * @param Request|null $request Optional request object for context
     * @return bool True if the action was handled, false otherwise
     */
    public function manage(?string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb ?? '', $args, $message, $messageParams)) return false;

        if (!$request) $request = Registry::get('request');

        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        
        $journal = $request->getJournal();
        
        switch ($verb) {
            case 'connect':
                $this->import('classes.form.DataverseAuthForm');
                $form = new DataverseAuthForm($this, $journal->getId());
                
                if ($request->getUserVar('cancel')) {
                    $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                } elseif ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute(); 
                        $request->redirect(null, 'manager', 'plugin', [$this->getCategory(), $this->getName(), 'select']);
                        return false;
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                return true;
                
            case 'select':
                $this->import('classes.form.DataverseSelectForm');
                $form = new DataverseSelectForm($this, $journal->getId());
                
                if ($request->getUserVar('cancel')) {
                    $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                } elseif ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugin', [$this->getCategory(), $this->getName(), 'settings']);
                        return false;
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                return true;
                
            case 'settings':
                $this->import('classes.form.SettingsForm');
                $form = new SettingsForm($this, $journal->getId());
                
                if ($request->getUserVar('cancel')) {
                    $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                } elseif ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                        return false;
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                return true;
        
            default:
                assert(false);
                return false;
        }
    }

    /**
     * Smarty plugin to generate URLs for this plugin's management pages.
     * This allows Smarty templates to use {plugin_url path="..." id="..."} to create links to the plugin's pages.
     * @param array $params
     * @param Smarty $smarty
     * @return string The generated URL
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (isset($params['path']) && is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [$params['id']]);
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Used by Delegators to format citation.
     * @param string $dataCitation
     * @param string $persistentUri
     * @return string Formatted citation with persistent URI as a hyperlink
     */
    public function _formatDataCitation(string $dataCitation, string $persistentUri): string {
        return str_replace($persistentUri, '<a href="'. $persistentUri .'">'. $persistentUri .'</a>', strip_tags($dataCitation));
    }
}
?>