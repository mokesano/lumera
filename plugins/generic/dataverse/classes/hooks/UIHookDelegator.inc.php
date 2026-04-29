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
 * @brief Handles all presentation and UI-related hooks (output filters, templates, notifications).
 * Modernized for PHP 8.4
 */

class UIHookDelegator {

    /** @var DataversePlugin */
    private $plugin;

    /**
     * Constructor
     * @param DataversePlugin $plugin
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Hook callback: register pages to display terms of use & data policy
     */
    public function setupPublicHandler(string $hookName, array $args): bool {
        $page = $args[0];
        if ($page == 'dataverse') {
            $op = $args[1];
            if ($op) {
                $publicPages = ['index', 'dataAvailabilityPolicy', 'termsOfUse'];

                if (in_array($op, $publicPages)) {
                    define('HANDLER_CLASS', 'DataverseHandler');
                    define('DATAVERSE_PLUGIN_NAME', $this->plugin->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                    // Parameter ke-3 adalah path ke file handler. Kita override di sini.
                    $args[2] = $this->plugin->getHandlerPath() . 'DataverseHandler.inc.php';
                }
            }
        }
        return false;
    }   
    
    /**
     * Hook callback: register output filters
     */
    public function handleTemplateDisplay(string $hookName, array $args): bool {
        $templateMgr = $args[0];
        $template = $args[1];

        // [WIZDAM FIX] Menghapus operator referensi &$this
        switch ($template) {
            case $this->plugin->getTemplatePath() .'/termsOfUse.tpl':
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
     * Output filter: Terms of Use
     */
    public function termsOfUseOutputFilter(string $output, $templateMgr): string {
        $title = '<title>'. __('rt.readingTools') .'</title>';
        $titleIndex = strpos($output, $title);
        if ($titleIndex !== false) {
            $output = str_replace($title, '<title>'. __('plugins.generic.dataverse.termsOfUse.dataverse') .': '. __('plugins.generic.dataverse.termsOfUse.title') .'</title>', $output);
        }
        $header = __('rt.readingTools') .'</h1>';
        $headerIndex = strpos($output, $header);
        if ($headerIndex !== false) {
            $output = str_replace($header, __('plugins.generic.dataverse.termsOfUse.dataverse') .'</h1>', $output);
        }
        $templateMgr->unregister_outputfilter('termsOfUseOutputFilter');
        return $output;
    }
    
    /**
     * Output filter: RT Metadata
     */
    public function rtMetadataOutputFilter(string $output, $templateMgr): string {
        $article = $templateMgr->get_template_vars('article');
        $currentJournal = $templateMgr->get_template_vars('currentJournal');        
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');      
        $study = $dataverseStudyDao->getStudyBySubmissionId($article->getId());
        
        $dataCitation = isset($study) 
            ? $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()) 
            : $article->getLocalizedData('externalDataCitation');
        
        if ($dataCitation) {
            $suppFileLabel = '<td>'. __('rt.metadata.pkp.suppFiles') .'</td>';
            $suppFileLabelIndex = strpos($output, $suppFileLabel);
            if ($suppFileLabelIndex !== false) {
                $newOutput = substr($output, 0, $suppFileLabelIndex);
                $newOutput .= '<td>'. __('plugins.generic.dataverse.dataCitation') .'</td>';
                $newOutput .= '<td>'. PKPString::stripUnsafeHtml($dataCitation) .'</td>';
                $newOutput .= '</tr><tr valign="top"><td>13.</td><td>'. __('rt.metadata.dublinCore.relation') .'</td>';
                $newOutput .= substr($output, $suppFileLabelIndex);
                $output = $newOutput;
            }
        }
            
        $suppFiles = $article->getSuppFiles();      
        if (isset($study) && !empty($suppFiles)) {
            $suppFileOutput = '';
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');

            foreach ($article->getSuppFiles() as $suppFile) {
                $dvFile = $dvFileDao->getDataverseFileBySuppFileId($suppFile->getId(), $article->getId());
                if (isset($dvFile)) { 
                    $suppFileOutput .= $templateMgr->smartyEscape($suppFile->getSuppFileTitle()) . ' ';
                    $suppFileOutput .= '<a href="'. $study->getPersistentUri() .'" target="_new" class="action">'. __('plugins.generic.dataverse.suppFiles.view') .'</a><br/>';
                } else {
                    $params = [
                        'page' => 'article',
                        'op'   => 'downloadSuppFile',
                        'path' => [$article->getId(), $suppFile->getBestSuppFileId($currentJournal)]
                    ];
                    $suppFileOutput .= '<a href="'. $templateMgr->smartyUrl($params, $templateMgr) .'">'. $templateMgr->smartyEscape($suppFile->getSuppFileTitle()) .'</a> ('. $suppFile->getNiceFileSize() .')<br />';
                }
            } 

            $preMatch = '<tr valign="top">\s*<td>13.<\/td>\s*<td>'. preg_quote(__('rt.metadata.dublinCore.relation'), '/') .'<\/td>\s*<td>'. preg_quote(__('rt.metadata.pkp.suppFiles'), '/') .'<\/td>\s*<td>';
            $postMatch = '<\/td>\s*<\/tr>';

            if ($suppFileOutput) {
                $output = preg_replace("/($preMatch).*?($postMatch)/s", "$1${suppFileOutput}$2", $output);
            } else {
                $output = preg_replace("/($preMatch).*?($postMatch)/s", "", $output);
            }
        } 
        $templateMgr->unregister_outputfilter('rtMetadataOutputFilter');
        return (string) $output;
    }
    
    /**
     * Output filter: RT SuppFiles
     */
    public function rtSuppFilesOutputFilter(string $output, $templateMgr): string {
        $article = $templateMgr->get_template_vars('article');
        $currentJournal = $templateMgr->get_template_vars('currentJournal');        
        $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');             
        $study = $dvStudyDao->getStudyBySubmissionId($article->getId());
        if (isset($study)) {
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
            foreach ($article->getSuppFiles() as $suppFile) {
                $dvFile = $dvFileDao->getDataverseFileBySuppFileId($suppFile->getId(), $article->getId());
                if (isset($dvFile)) {
                    $params = [
                        'page' => 'article',
                        'op'   => 'downloadSuppFile',
                        'path' => [$article->getBestArticleId(), $suppFile->getBestSuppFileId($currentJournal)]
                    ];
                    $suppFileUrl = $templateMgr->smartyUrl($params, $templateMgr);
                    $pattern = '/<a href="'. preg_quote($suppFileUrl, '/') .'" class="action">.+?<\/a>/';
                    $replace = '<a href="'. $study->getPersistentUri() .'" class="action">'. __('plugins.generic.dataverse.suppFiles.view') .'</a>';
                    $output = preg_replace($pattern, $replace, $output);
                }
            }
        }
        $templateMgr->unregister_outputfilter('rtSuppFilesOutputFilter');
        return (string) $output;
    }

    /**
     * Output filter: RT SuppFile View
     */
    public function rtSuppFileViewOutputFilter(string $output, $templateMgr): string {
        $article = $templateMgr->get_template_vars('article');
        $suppFile = $templateMgr->get_template_vars('suppFile');
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
        $dvFile = $dvFileDao->getDataverseFileBySuppFileId($suppFile->getId(), $article->getId());
        if (isset($dvFile)) {
            $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $study = $dvStudyDao->getStudyBySubmissionId($article->getId());
            if (isset($study)) {
                $preMatch = '(<div id="supplementaryFileUpload">.+?<table width="100%" class="data">)';
                $postMatch = '(<\/table>\s*<\/div>)';
                $replace =  '<tr valign="top"><td width="20%" class="label">'. __('plugins.generic.dataverse.dataCitation') .'</td><td width="80%" class="value">'. $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()) .'</td></tr>';
                $output = preg_replace("/$preMatch.+?$postMatch/s", "$1$replace$2", $output);
            }
        }
        $templateMgr->unregister_outputfilter('rtSuppFileViewOutputFilter');
        return (string) $output;
    }
    
    /**
     * Output filter: Submission Summary
     */
    public function submissionOutputFilter(string $output, $templateMgr): string {
        $submission = $templateMgr->get_template_vars('submission');
        if (!isset($submission)) return $output;
            
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId((int) $submission->getId());

        $dataCitation = isset($study) 
            ? $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri())
            : $submission->getLocalizedData('externalDataCitation');
            
        if (!$dataCitation) return $output;

        $index = strpos($output, '<td class="label">'. __('submission.submitter'));
        if ($index !== false) {
            $newOutput = substr($output,0,$index);
            $newOutput .= '<td class="label">'.  __('plugins.generic.dataverse.dataCitation') .'</td>';
            $newOutput .= '<td class="value" colspan="2">'. PKPString::stripUnsafeHtml($dataCitation) .'</td></tr><tr>';
            $newOutput .= substr($output, $index);
            $output = $newOutput;
        }
        $templateMgr->unregister_outputfilter('submissionSummaryOutputFilter');
        return $output;
    }
    
    /**
     * Hook callback: add data citation to article landing page.
     */
    public function addDataCitationArticle(string $hookName, array $args): bool {
        $templateMgr = $args[1];
        $output = &$args[2];

        $article = $templateMgr->get_template_vars('article');
        
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId((int) $article->getId());
        
        if (isset($study)) {
            $templateMgr->assign('dataCitation', $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()));
        } else {
            $templateMgr->assign('dataCitation', $article->getLocalizedData('externalDataCitation'));
        }
        $output .= $templateMgr->fetch($this->plugin->getTemplatePath() . 'dataCitationArticle.tpl');
        return false;
    }   
    
    /**
     * Hook callback: register plugin settings fields with TinyMCE
     */
    public function getTinyMCEEnabledFields(string $hookName, array $args): bool {
        $fields = &$args[1];
        $request = Registry::get('request');
        $router = $request->getRouter();

        $page = $router->getRequestedPage($request);
        $op = $router->getRequestedOp($request);
        $requestArgs = $router->getRequestedArgs($request);

        if ($page == 'manager' && $op == 'plugin' && in_array('dataverseplugin', $requestArgs)) {
            $fields = ['dataAvailability', 'termsOfUse'];
        }
        return false;
    }

    /**
     * Hook callback: add link to data availability policy to policies section
     */
    public function addPolicyLinks(string $hookName, array $args): bool {
        $journal = Registry::get('request')->getJournal();
        $dataAvailability = $this->plugin->getSetting($journal->getId(), 'dataAvailability');
        if (!empty($dataAvailability)) {
            $templateMgr = $args[1];
            $output = &$args[2];
            $output .= '<li><a href="'. $templateMgr->smartyUrl(['page' => 'dataverse', 'op'=>'dataAvailabilityPolicy'], $templateMgr) .'">';
            $output .= __('plugins.generic.dataverse.settings.dataAvailabilityPolicy');
            $output .= '</a></li>';
        }
        return false;
    }
    
    /**
     * Hook callback: add content to custom notifications
     */
    public function getNotificationContents(string $hookName, array $args): bool {
        $notification = $args[0];
        $message = &$args[1];
        
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
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                $params = $notificationSettingsDao->getNotificationSettings($notification->getId());
                $message = __('plugins.generic.dataverse.notification.studyReleased', $notificationManager->getParamsForCurrentLocale($params));
                break;
            case NOTIFICATION_TYPE_DATAVERSE_UNRELEASED:
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                $params = $notificationSettingsDao->getNotificationSettings($notification->getId());
                $message = __('plugins.generic.dataverse.notification.releaseDataverse', $notificationManager->getParamsForCurrentLocale($params));
                break;
        }
        return false;
    }   
}
?>