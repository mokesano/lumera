<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/hooks/UIHookDelegator.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class UIHookDelegator
 * @brief Handles all presentation and UI-related hooks.
 */

class UIHookDelegator {

    /** @var DataversePlugin */
    private $plugin;

    /**
     * Constructor.
     * @param DataversePlugin $plugin
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Hook callback: register pages to display terms of use & data policy.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function setupPublicHandler(string $hookName, array $args): bool {
        $page = $args[0];
        if ($page === 'dataverse') {
            $op = $args[1];
            if ($op !== null && $op !== '') {
                $publicPages = ['index', 'dataAvailabilityPolicy', 'termsOfUse'];

                if (in_array($op, $publicPages, true)) {
                    if (!defined('HANDLER_CLASS')) {
                        define('HANDLER_CLASS', 'DataverseHandler');
                    }
                    if (!defined('DATAVERSE_PLUGIN_NAME')) {
                        define('DATAVERSE_PLUGIN_NAME', $this->plugin->getName());
                    }
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                    $args[2] = $this->plugin->getHandlerPath() . 'DataverseHandler.inc.php';
                }
            }
        }
        return false;
    }   
    
    /**
     * Hook callback: register output filters.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function handleTemplateDisplay(string $hookName, array $args): bool {
        $templateMgr = $args[0];
        $template = $args[1];

        $pluginPath = rtrim($this->plugin->getTemplatePath(), '/') . '/';

        switch ($template) {
            case $pluginPath . 'termsOfUse.tpl':
                $templateMgr->register_outputfilter([$this, 'termsOfUseOutputFilter']);
                break;
            case 'author/submission.tpl':           
            case 'sectionEditor/submission.tpl':
                $templateMgr->register_outputfilter([$this, 'submissionOutputFilter']);
                break;
            case 'rt/metadata.tpl':
                $templateMgr->register_outputfilter([$this, 'rtMetadataOutputFilter']);
                break;
            case 'rt/suppFiles.tpl':
                $templateMgr->register_outputfilter([$this, 'rtSuppFilesOutputFilter']);
                break;
            case 'rt/suppFileView.tpl':
                $templateMgr->register_outputfilter([$this, 'rtSuppFileViewOutputFilter']);
                break;
        }
        return false;
    }
    
    /**
     * Output filter: Terms of Use.
     * @param string $output
     * @param object $templateMgr
     * @return string
     */
    public function termsOfUseOutputFilter(string $output, $templateMgr): string {
        $title = '<title>' . __('rt.readingTools') . '</title>';
        $titleIndex = strpos($output, $title);
        if ($titleIndex !== false) {
            $output = str_replace(
                $title, 
                '<title>' . __('plugins.generic.dataverse.termsOfUse.dataverse') . ': ' . __('plugins.generic.dataverse.termsOfUse.title') . '</title>', 
                $output
            );
        }
        
        $header = __('rt.readingTools') . '</h1>';
        $headerIndex = strpos($output, $header);
        if ($headerIndex !== false) {
            $output = str_replace($header, __('plugins.generic.dataverse.termsOfUse.dataverse') . '</h1>', $output);
        }
        
        $templateMgr->unregister_outputfilter([$this, 'termsOfUseOutputFilter']);
        return $output;
    }
    
    /**
     * Output filter: RT Metadata.
     * @param string $output
     * @param object $templateMgr
     * @return string
     */
    public function rtMetadataOutputFilter(string $output, $templateMgr): string {
        $article = $templateMgr->get_template_vars('article');
        if ($article === null) {
            return $output;
        }

        /** @var DataverseStudyDAO $dataverseStudyDao */
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId((int) $article->getId());
        
        $dataCitation = $study !== null 
            ? $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()) 
            : $article->getLocalizedData('externalDataCitation');
        
        if (!empty($dataCitation)) {
            $suppFileLabel = '<td>' . __('rt.metadata.pkp.suppFiles') . '</td>';
            $suppFileLabelIndex = strpos($output, $suppFileLabel);
            if ($suppFileLabelIndex !== false) {
                $newOutput = substr($output, 0, $suppFileLabelIndex);
                $newOutput .= '<td>' . __('plugins.generic.dataverse.dataCitation') . '</td>';
                $newOutput .= '<td>' . PKPString::stripUnsafeHtml((string) $dataCitation) . '</td>';
                $newOutput .= '</tr><tr valign="top"><td>13.</td><td>' . __('rt.metadata.dublinCore.relation') . '</td>';
                $newOutput .= substr($output, $suppFileLabelIndex);
                $output = $newOutput;
            }
        }
            
        $suppFiles = $article->getSuppFiles();      
        if ($study !== null && is_array($suppFiles) && !empty($suppFiles)) {
            $suppFileOutput = '';
            $currentJournal = $templateMgr->get_template_vars('currentJournal');
            
            /** @var DataverseFileDAO $dvFileDao */
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');

            foreach ($suppFiles as $suppFile) {
                $dvFile = $dvFileDao->getDataverseFileBySuppFileId((int) $suppFile->getId(), (int) $article->getId());
                if ($dvFile !== null) { 
                    $suppFileOutput .= $templateMgr->smartyEscape($suppFile->getSuppFileTitle()) . ' ';
                    $suppFileOutput .= '<a href="' . $study->getPersistentUri() . '" target="_new" class="action">' . __('plugins.generic.dataverse.suppFiles.view') . '</a><br/>';
                } else {
                    $params = [
                        'page' => 'article',
                        'op'   => 'downloadSuppFile',
                        'path' => [(int) $article->getId(), $suppFile->getBestSuppFileId($currentJournal)]
                    ];
                    $suppFileOutput .= '<a href="' . $templateMgr->smartyUrl($params, $templateMgr) . '">' . $templateMgr->smartyEscape($suppFile->getSuppFileTitle()) . '</a> (' . $suppFile->getNiceFileSize() . ')<br />';
                }
            } 

            $preMatch = '<tr valign="top">\s*<td>13.<\/td>\s*<td>' . preg_quote(__('rt.metadata.dublinCore.relation'), '/') . '<\/td>\s*<td>' . preg_quote(__('rt.metadata.pkp.suppFiles'), '/') . '<\/td>\s*<td>';
            $postMatch = '<\/td>\s*<\/tr>';

            if ($suppFileOutput !== '') {
                $output = preg_replace("/($preMatch).*?($postMatch)/s", "$1${suppFileOutput}$2", $output);
            } else {
                $output = preg_replace("/($preMatch).*?($postMatch)/s", "", $output);
            }
        } 
        
        $templateMgr->unregister_outputfilter([$this, 'rtMetadataOutputFilter']);
        return $output;
    }
    
    /**
     * Output filter: RT SuppFiles.
     * @param string $output
     * @param object $templateMgr
     * @return string
     */
    public function rtSuppFilesOutputFilter(string $output, $templateMgr): string {
        $article = $templateMgr->get_template_vars('article');
        if ($article === null) {
            return $output;
        }
        
        $currentJournal = $templateMgr->get_template_vars('currentJournal');        

        /** @var DataverseStudyDAO $dvStudyDao */
        $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');             
        $study = $dvStudyDao->getStudyBySubmissionId((int) $article->getId());
        
        if ($study !== null) {
            /** @var DataverseFileDAO $dvFileDao */
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
            $suppFiles = $article->getSuppFiles();
            
            if (is_array($suppFiles)) {
                foreach ($suppFiles as $suppFile) {
                    $dvFile = $dvFileDao->getDataverseFileBySuppFileId((int) $suppFile->getId(), (int) $article->getId());
                    if ($dvFile !== null) {
                        $params = [
                            'page' => 'article',
                            'op'   => 'downloadSuppFile',
                            'path' => [(int) $article->getBestArticleId(), $suppFile->getBestSuppFileId($currentJournal)]
                        ];
                        $suppFileUrl = $templateMgr->smartyUrl($params, $templateMgr);
                        $pattern = '/<a href="' . preg_quote($suppFileUrl, '/') . '" class="action">.+?<\/a>/';
                        $replace = '<a href="' . $study->getPersistentUri() . '" class="action">' . __('plugins.generic.dataverse.suppFiles.view') . '</a>';
                        $output = preg_replace($pattern, $replace, $output);
                    }
                }
            }
        }
        $templateMgr->unregister_outputfilter([$this, 'rtSuppFilesOutputFilter']);
        return $output;
    }

    /**
     * Output filter: RT SuppFile View.
     * @param string $output
     * @param object $templateMgr
     * @return string
     */
    public function rtSuppFileViewOutputFilter(string $output, $templateMgr): string {
        $article = $templateMgr->get_template_vars('article');
        $suppFile = $templateMgr->get_template_vars('suppFile');

        if ($article === null || $suppFile === null) {
            return $output;
        }

        /** @var DataverseFileDAO $dvFileDao */
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
        $dvFile = $dvFileDao->getDataverseFileBySuppFileId((int) $suppFile->getId(), (int) $article->getId());
        
        if ($dvFile !== null) {
            /** @var DataverseStudyDAO $dvStudyDao */
            $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $study = $dvStudyDao->getStudyBySubmissionId((int) $article->getId());
            
            if ($study !== null) {
                $preMatch = '(<div id="supplementaryFileUpload">.+?<table width="100%" class="data">)';
                $postMatch = '(<\/table>\s*<\/div>)';
                $replace = '<tr valign="top"><td width="20%" class="label">' . __('plugins.generic.dataverse.dataCitation') . '</td><td width="80%" class="value">' . $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()) . '</td></tr>';
                $output = preg_replace("/$preMatch.+?$postMatch/s", "$1$replace$2", $output);
            }
        }
        $templateMgr->unregister_outputfilter([$this, 'rtSuppFileViewOutputFilter']);
        return $output;
    }
    
    /**
     * Output filter: Submission Summary.
     * @param string $output
     * @param object $templateMgr
     * @return string
     */
    public function submissionOutputFilter(string $output, $templateMgr): string {
        $submission = $templateMgr->get_template_vars('submission');
        if ($submission === null) {
            return $output;
        }
        
        /** @var DataverseStudyDAO $dataverseStudyDao */
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId((int) $submission->getId());

        $dataCitation = $study !== null 
            ? $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri())
            : $submission->getLocalizedData('externalDataCitation');
            
        if (empty($dataCitation)) {
            return $output;
        }

        $index = strpos($output, '<td class="label">' . __('submission.submitter'));
        if ($index !== false) {
            $newOutput = substr($output, 0, $index);
            $newOutput .= '<td class="label">' . __('plugins.generic.dataverse.dataCitation') . '</td>';
            $newOutput .= '<td class="value" colspan="2">' . PKPString::stripUnsafeHtml((string) $dataCitation) . '</td></tr><tr>';
            $newOutput .= substr($output, $index);
            $output = $newOutput;
        }
        $templateMgr->unregister_outputfilter([$this, 'submissionOutputFilter']);
        return $output;
    }
    
    /**
     * Hook callback: add data citation to article landing page.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function addDataCitationArticle(string $hookName, array $args): bool {
        $templateMgr = $args[1];
        $output = &$args[2];

        $article = $templateMgr->get_template_vars('article');
        if ($article === null) {
            return false;
        }
        
        /** @var DataverseStudyDAO $dataverseStudyDao */
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId((int) $article->getId());
        
        if ($study !== null) {
            $templateMgr->assign('dataCitation', $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()));
        } else {
            $templateMgr->assign('dataCitation', $article->getLocalizedData('externalDataCitation'));
        }
        $output .= $templateMgr->fetch($this->plugin->getTemplatePath() . 'dataCitationArticle.tpl');
        return false;
    }   
    
    /**
     * Hook callback: register plugin settings fields with TinyMCE.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function getTinyMCEEnabledFields(string $hookName, array $args): bool {
        $fields = &$args[1];
        $request = Application::get()->getRequest();

        // [LUMERA FIX] Call routing methods directly on $request to avoid 
        // linter "Undefined method" on the generic Router base class.
        $page = $request->getRequestedPage();
        $op = $request->getRequestedOp();
        $requestArgs = $request->getRequestedArgs();

        // Safe cast $requestArgs to array to prevent in_array strict type warning if null
        if ($page === 'manager' && $op === 'plugin' && in_array('dataverseplugin', (array) $requestArgs, true)) {
            $fields = ['dataAvailability', 'termsOfUse'];
        }
        return false;
    }

    /**
     * Hook callback: add link to data availability policy to policies section.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function addPolicyLinks(string $hookName, array $args): bool {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        if ($journal !== null) {
            $dataAvailability = $this->plugin->getSetting((int) $journal->getId(), 'dataAvailability');
            if (!empty($dataAvailability)) {
                $templateMgr = $args[1];
                $output = &$args[2];
                $output .= '<li><a href="' . $templateMgr->smartyUrl(['page' => 'dataverse', 'op' => 'dataAvailabilityPolicy'], $templateMgr) . '">';
                $output .= __('plugins.generic.dataverse.settings.dataAvailabilityPolicy');
                $output .= '</a></li>';
            }
        }
        return false;
    }
    
    /**
     * Hook callback: add content to custom notifications.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function getNotificationContents(string $hookName, array $args): bool {
        $notification = $args[0];
        $message = &$args[1];
        
        if ($notification === null) {
            return false;
        }
        
        $type = $notification->getType();
        
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        switch ($type) {
            case NOTIFICATION_TYPE_DATAVERSE_ERROR:
                $message = __('plugins.generic.dataverse.notification.error');
                break;
            case NOTIFICATION_TYPE_DATAVERSE_FILE_ADDED:
                $message = __('plugins.generic.dataverse.notification.fileAdded');
                break;
            case NOTIFICATION_TYPE_DATAVERSE_FILE_DELETED:
                $message = __('plugins.generic.dataverse.notification.fileDeleted');
                break;
            case NOTIFICATION_TYPE_DATAVERSE_STUDY_CREATED:
                $message = __('plugins.generic.dataverse.notification.studyCreated');
                break;
            case NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED:
                $message = __('plugins.generic.dataverse.notification.studyUpdated');
                break;
            case NOTIFICATION_TYPE_DATAVERSE_STUDY_DELETED:
                $message = __('plugins.generic.dataverse.notification.studyDeleted');
                break;
            case NOTIFICATION_TYPE_DATAVERSE_STUDY_RELEASED:
                /** @var NotificationSettingsDAO $notificationSettingsDao */
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                $params = $notificationSettingsDao->getNotificationSettings((int) $notification->getId());
                $message = __('plugins.generic.dataverse.notification.studyReleased', $notificationManager->getParamsForCurrentLocale($params));
                break;
            case NOTIFICATION_TYPE_DATAVERSE_UNRELEASED:
                /** @var NotificationSettingsDAO $notificationSettingsDao */
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                $params = $notificationSettingsDao->getNotificationSettings((int) $notification->getId());
                $message = __('plugins.generic.dataverse.notification.releaseDataverse', $notificationManager->getParamsForCurrentLocale($params));
                break;
        }
        return false;
    }   
    
}
?>