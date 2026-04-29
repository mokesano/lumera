<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/hooks/FormHookDelegator.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class FormHookDelegator
 * @brief Handles all form-related hooks for the Dataverse plugin (metadata, suppfiles, submission).
 * [WIZDAM EDITION] Modernized for PHP 8.4 and Dataverse Native REST API.
 */

class FormHookDelegator {

    /** @var DataversePlugin */
    private $plugin;

    /** @var StudyService */
    private $studyService;

    /** @var DataverseApiClient */
    private $apiClient;

    /**
     * Constructor
     */
    public function __construct($plugin, $studyService, $apiClient) {
        $this->plugin = $plugin;
        $this->studyService = $studyService;
        $this->apiClient = $apiClient;
    }

    // 
    // METADATA FORM HOOKS
    // 

    /**
     * Metadata form constructor hook to add custom validation for study locking
     */
    public function metadataFormConstructor(string $hookName, array $args): bool {
        $form = $args[0];
        $form->addCheck(new FormValidatorCustom($this->plugin, 'metadata', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.metadataForm.studyLocked', [$this, 'formValidateStudyState'], [&$form]));
        return false;
    }   
    
    /**
     * Metadata form execute hook to update study metadata 
     * if the article is linked to a study.
     */
    public function metadataFormExecute(string $hookName, array $args): bool {
        $form = $args[0];
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId($form->article->getId());
        if (isset($study)) {
            $journal = Registry::get('request')->getJournal();
            $metadataReplaced = $this->studyService->replaceStudyMetadata($form->article, $study, $journal);
            
            $user = Registry::get('request')->getUser();
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification($user->getId(), $metadataReplaced ? NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED : NOTIFICATION_TYPE_DATAVERSE_ERROR);
        }
        return false;
    }   
    
    /**
     * [WIZDAM REST API] 
     * Native REST API menangani Locking di level transaksi (HTTP 409 Conflict).
     * Mengecek lock di setiap render UI adalah pemborosan call API. Kita kembalikan true.
     */
    public function formValidateStudyState(string $field, $form): bool {
        return true; 
    }

    /**
     * Metadata form field names hook to add custom fields for study description and external data citation.
     */
    public function articleMetadataFormFieldNames(string $hookName, array $args): bool {
        $fields = &$args[1]; 
        $fields[] = 'studyDescription';
        $fields[] = 'externalDataCitation';
        return false;       
    }

    // 
    // SUPPFILE FORM HOOKS
    // 
    /**
     * Supplementary file additional metadata hook to display study information.
     */
    public function suppFileAdditionalMetadata(string $hookName, array $args): bool {
        $templateMgr = $args[1];
        $output = &$args[2];
        $articleId = $templateMgr->get_template_vars('articleId');
        
        $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dvStudyDao->getStudyBySubmissionId($articleId);

        if (isset($study)) {
            $journal = Registry::get('request')->getJournal();
            $templateMgr->assign('dataCitation', $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri()));
            
            // [WIZDAM FIX] Locking status diabaikan di UI untuk performa REST API
            $templateMgr->assign('studyLocked', false);
        }
        $output .= $templateMgr->fetch($this->plugin->getTemplatePath() . 'suppFileAdditionalMetadata.tpl');
        return false;
    }
    
    /**
     * Supplementary file form constructor hook to add custom validation.
     */
    public function suppFileFormConstructor(string $hookName, array $args): bool {
        $form = $args[0];
        $form->addCheck(new FormValidatorCustom($this->plugin, 'publishData', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.suppFile.publishData.error', [$this, 'suppFileFormValidateDeposit'], [&$form]));
        $form->addCheck(new FormValidatorCustom($this->plugin, 'publishData', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.suppFile.studyLocked', [$this, 'formValidateStudyState'], [&$form]));
        $form->addCheck(new FormValidatorCustom($this->plugin, 'externalDataCitation', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.suppFile.externalDataCitation.error', [$this, 'suppFileFormValidateCitations'], [&$form])); 
        return false;
    }
    
    /**
     * Validates the deposit information for the supplementary file.
     */
    public function suppFileFormValidateDeposit(string $publishData, $form): bool {
        if ($publishData == 'dataverse') {
            if ($form->suppFile && $form->suppFile->getFileId()) return true;
            
            import('classes.file.ArticleFileManager');
            $articleId = isset($form->article) ? $form->article->getId() : $form->articleId;
            $articleFileManager = new ArticleFileManager($articleId);
            if (!$articleFileManager->uploadedFileExists('uploadSuppFile')) return false;
        }
        return true;
    }
    
    /**
     * Validates the external data citation for the supplementary file.
     */
    public function suppFileFormValidateCitations(?string $externalCitation, $form): bool {
        if ($externalCitation && $form->getData('publishData') == 'dataverse') {
            return false;
        }
        return true;
    }
    
    /**
     * Initializes the supplementary file form data.
     */
    public function suppFileFormInitData(string $hookName, array $args): bool {
        $form = $args[0];
        $journal = Registry::get('request')->getJournal();      
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $form->article ?? $articleDao->getArticle($form->articleId, $journal->getId());
        
        $form->setData('studyDescription', $article->getLocalizedData('studyDescription'));
        $form->setData('externalDataCitation', $article->getLocalizedData('externalDataCitation'));
        
        $publishData = 'none';
        if (isset($form->suppFile) && $form->suppFile->getId()) {
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
            $dvFile = $dvFileDao->getDataverseFileBySuppFileId($form->suppFile->getId(), $article->getId());
            if (!is_null($dvFile)) { $publishData = 'dataverse'; }
        }
        $form->setData('publishData', $publishData);
        return false;
    }

    /**
     * Supplementary file form read user variables hook to add custom fields.
     */
    public function suppFileFormReadUserVars(string $hookName, array $args): bool {
        $vars = &$args[1];
        $vars[] = 'studyDescription';
        $vars[] = 'externalDataCitation';
        $vars[] = 'publishData';
        return false;
    }

    /**
     * Supplementary file form execute hook to handle 
     * the submission of supplementary files.
     */
    public function authorSubmitSuppFileFormExecute(string $hookName, array $args): bool {
        $form = $args[0];
        $journal = Registry::get('request')->getJournal();      
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($form->articleId, $journal->getId());
        
        $article->setData('studyDescription', $form->getData('studyDescription'), $form->getFormLocale());
        $article->setData('externalDataCitation', $form->getData('externalDataCitation'), $form->getFormLocale());
        $articleDao->updateArticle($article);
        
        if (!isset($form->suppFile) || !$form->suppFile->getId()) return false;
        
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
        $dvFile = $dvFileDao->getDataverseFileBySuppFileId($form->suppFile->getId(), $form->articleId);            

        switch ($form->getData('publishData')) {
            case 'none':
                if (isset($dvFile)) $dvFileDao->deleteDataverseFile($dvFile);
                break;
            case 'dataverse':
                if (!isset($dvFile)) {
                    $this->plugin->import('classes.DataverseFile');
                    $dvFile = new DataverseFile();
                    $dvFile->setSuppFileId($form->suppFile->getId());
                    $dvFile->setSubmissionId($form->articleId);
                    $dvFileDao->insertDataverseFile($dvFile);                       
                }
                break;
        }
        return false;
    }
    
    /**
     * Supplementary file form execute hook to handle the submission 
     * of supplementary files from the edit page.
     */
    public function suppFileFormExecute(string $hookName, array $args): bool {   
        $form = $args[0];
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $form->article;
        $journal = Registry::get('request')->getJournal();
        
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = Registry::get('request')->getUser();

        $article->setData('studyDescription', $form->getData('studyDescription'), $form->getFormLocale());
        $article->setData('externalDataCitation', $form->getData('externalDataCitation'), $form->getFormLocale());
        $articleDao->updateArticle($article);
        
        switch ($form->getData('publishData')) {
            case 'none':
                if ($form->suppFile->getId()) { 
                    $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');       
                    $dvFile = $dvFileDao->getDataverseFileBySuppFileId($form->suppFile->getId(), $article->getId());
                    if (isset($dvFile)) {
                        $fileDeleted = $this->studyService->deleteFile($dvFile, $journal->getId());
                        if ($fileDeleted) $dvFileDao->deleteDataverseFile($dvFile);
                        
                        $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');             
                        $study = $dvStudyDao->getStudyBySubmissionId($article->getId());
                        if (isset($study)) $this->studyService->replaceStudyMetadata($article, $study, $journal);
                        
                        $notificationManager->createTrivialNotification($user->getId(), $fileDeleted ? NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED : NOTIFICATION_TYPE_DATAVERSE_ERROR);
                    }
                }
                break;

            case 'dataverse':
                $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
                $study = $dvStudyDao->getStudyBySubmissionId($article->getId());    
                if (!$study) $study = $this->studyService->createStudy($article, $journal);
                if (!$study) {
                    $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_DATAVERSE_ERROR);
                    return false;
                }
                if (!$form->suppFile->getId()) {
                    $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
                    $form->setSuppFileData($form->suppFile);
                    $suppFileDao->insertSuppFile($form->suppFile);
                    $form->suppFileId = $form->suppFile->getId();
                    $form->suppFile = $suppFileDao->getSuppFile($form->suppFileId, $article->getId());
                    
                    $deposited = $this->studyService->depositFiles($study, [$form->suppFile], $journal->getId());
                    $this->studyService->replaceStudyMetadata($article, $study, $journal);
                    $notificationManager->createTrivialNotification($user->getId(), $deposited ? NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED: NOTIFICATION_TYPE_DATAVERSE_ERROR);
                } else {
                    import('classes.file.ArticleFileManager');
                    $fileName = 'uploadSuppFile';
                    $articleFileManager = new ArticleFileManager($article->getId());
                    if ($articleFileManager->uploadedFileExists($fileName)) {
                        $fileId = $form->suppFile->getFileId();
                        if ($fileId != 0) {
                            $articleFileManager->uploadSuppFile($fileName, $fileId);
                        } else {
                            $fileId = $articleFileManager->uploadSuppFile($fileName);   
                            $form->suppFile->setFileId($fileId);                    
                        }
                    }
                    $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
                    $form->suppFile = $suppFileDao->getSuppFile($form->suppFileId, $article->getId());
                    $form->setSuppFileData($form->suppFile);
                    $suppFileDao->updateSuppFile($form->suppFile);
                    
                    $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');                           
                    $dvFile = $dvFileDao->getDataverseFileBySuppFileId($form->suppFileId, $article->getId());
                    if (isset($dvFile)) {
                        $this->studyService->deleteFile($dvFile, $journal->getId());
                        $dvFileDao->deleteDataverseFile($dvFile);
                    }
                    $deposited = $this->studyService->depositFiles($study, [$form->suppFile], $journal->getId());
                    $this->studyService->replaceStudyMetadata($article, $study, $journal);
                    $notificationManager->createTrivialNotification($user->getId(), $deposited ? NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED: NOTIFICATION_TYPE_DATAVERSE_ERROR);
                }
                break;
        }
        return false;
    }

    // 
    // WORKFLOW & SUBMISSION HOOKS
    // 
    /**
     * Handles the insertion of a supplementary file.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function handleSuppFileInsertion(string $hookName, array $args): bool {
        $params = $args[1];
        $fileId = (int) $params[1];      
        $articleId = (int) $params[2];
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        return $suppFileDao->suppFileExistsByFileId($articleId, $fileId);
    }
    
    /**
     * Handles the deletion of a supplementary file.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function handleSuppFileDeletion(string $hookName, array $args): bool {
        $params = $args[1];
        $suppFileId = is_array($params) ? (int) $params[0] : (int) $params;
        $submissionId = is_array($params) ? (int) $params[1] : 0;
        
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
        $dvFile = $dvFileDao->getDataverseFileBySuppFileId($suppFileId, $submissionId);
        if (isset($dvFile)) {
            $journal = Registry::get('request')->getJournal();
            $fileDeleted = $this->studyService->deleteFile($dvFile, $journal->getId());
            if ($fileDeleted) $dvFileDao->deleteDataverseFile($dvFile);

            $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $study = $dvStudyDao->getStudyBySubmissionId($dvFile->getSubmissionId());
            if (isset($study)) {
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $article = $articleDao->getArticle($study->getSubmissionId(), $journal->getId());
                if ($article) $this->studyService->replaceStudyMetadata($article, $study, $journal);
            }
            
            $user = Registry::get('request')->getUser();
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification($user->getId(), $fileDeleted ? NOTIFICATION_TYPE_DATAVERSE_STUDY_UPDATED : NOTIFICATION_TYPE_DATAVERSE_ERROR);
        }
        return false;
    }
    
    /**
     * Adds a custom validator to the author submission form to require 
     * at least one dataset if the setting is enabled.
     */
    public function addAuthorSubmitFormValidator(string $hookName, array $args): void {
        $form = $args[0];
        $form->addCheck(new FormValidatorCustom($form, '', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.dataverse.settings.requireDataError', [$this, 'validateRequiredData'], [$form]));
    }
    
    /**
     * Validates that at least one dataset is linked to the submission 
     * if the setting is enabled.
     */
    public function validateRequiredData(string $fieldValue, $form): bool {
        $journal = Registry::get('request')->getJournal();
        if (!$this->plugin->getSetting($journal->getId(), 'requireData')) return true;
        
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
        $dvFiles = $dvFileDao->getDataverseFilesBySubmissionId($form->articleId);
        return count($dvFiles) > 0;
    }
    
    /**
     * Handles the author submission by creating a study if there are 
     * linked datasets and depositing the files.
     */
    public function handleAuthorSubmission(string $hookName, array $args): bool {
        $step = $args[0];
        $article = $args[1];
        if ($step == 5) {
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
            $dvFiles = $dvFileDao->getDataverseFilesBySubmissionId($article->getId());
            if ($dvFiles) { 
                $journal = Registry::get('request')->getJournal();
                $study = $this->studyService->createStudy($article, $journal);
                if ($study) {
                    $suppFileDao = DAORegistry::getDAO('SuppFileDAO');                      
                    $suppFiles = [];                  
                    foreach ($dvFiles as $dvFile) {
                        $suppFiles[] = $suppFileDao->getSuppFile($dvFile->getSuppFileId(), $article->getId());
                    }
                    $this->studyService->depositFiles($study, $suppFiles, $journal->getId());
                }
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();               
                $user = Registry::get('request')->getUser();
                $notificationManager->createTrivialNotification($user->getId(), isset($study) ? NOTIFICATION_TYPE_DATAVERSE_STUDY_CREATED : NOTIFICATION_TYPE_DATAVERSE_ERROR);
            }
        }
        return false;
    }
    
    /**
     * Handles the editor decision by releasing the study if accepted 
     * or deleting it if declined, based on the plugin settings.
     */
    public function handleEditorDecision(string $hookName, array $args): bool {
        $submission = $args[0];
        $decision = $args[1];
        
        if ($this->plugin->getSetting($submission->getJournalId(), 'studyRelease') == DATAVERSE_PLUGIN_RELEASE_ARTICLE_PUBLISHED) {
            return false;
        }

        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId($submission->getId());
        
        if (isset($study)) {
            $journal = Registry::get('request')->getJournal();
            $user = Registry::get('request')->getUser();
            $request = Registry::get('request');
            
            if ($decision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) {
                $this->studyService->releaseStudy($study, $journal, $user, $request);
            }
            if ($decision['decision'] == SUBMISSION_EDITOR_DECISION_DECLINE) {
                $this->studyService->deleteStudy($study, $journal->getId(), $user->getId());
            }
        }
        return false;
    }
    
    /**
     * Handles the article update by releasing the study if the article 
     * is published, based on the plugin settings.
     */
    public function handleArticleUpdate(string $hookName, array $args): bool {
        $params = $args[1];
        $articleId = (int) $params[count($params)-1];
        $status = (int) $params[6];
        
        if ($status == STATUS_PUBLISHED) {
            $dvStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $study = $dvStudyDao->getStudyBySubmissionId($articleId);
            if (isset($study)) {
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');          
                $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId);
                if ($article && $article->getStatus() != STATUS_PUBLISHED) {
                    $journal = Registry::get('request')->getJournal();
                    $user = Registry::get('request')->getUser();
                    $request = Registry::get('request');
                    
                    $article->setStatus(STATUS_PUBLISHED);
                    $this->studyService->replaceStudyMetadata($article, $study, $journal);
                    $this->studyService->releaseStudy($study, $journal, $user, $request);
                }
            }
        }
        return false;
    }
    
    /**
     * Handles unsuitable submission by deleting the linked study 
     * if the setting is enabled.
     */
    public function handleUnsuitableSubmission(string $hookName, array $args): bool {
        $submission = $args[0];     
        $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
        $study = $dataverseStudyDao->getStudyBySubmissionId($submission->getId());
        if (isset($study)) {
            $journal = Registry::get('request')->getJournal();
            $user = Registry::get('request')->getUser();
            $this->studyService->deleteStudy($study, $journal->getId(), $user->getId());
        }
        return false;       
    }
}
?>