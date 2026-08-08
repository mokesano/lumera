<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/services/StudyService.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class StudyService
 * @brief Handles the business logic for creating, updating, releasing, and deleting Dataverse studies.
 */

class StudyService {

    /** @var DataversePlugin */
    private $plugin;

    /** @var DataverseApiClient */
    private $apiClient;

    /**
     * Constructor.
     * @param DataversePlugin $plugin
     * @param DataverseApiClient $apiClient
     */
    public function __construct($plugin, $apiClient) {
        $this->plugin = $plugin;
        $this->apiClient = $apiClient;
    }

    /**
     * Create JSON Metadata Payload for Dataverse Native API.
     * @param object $article
     * @param object $journal
     * @return array
     */
    public function createJsonMetadata($article, $journal): array {
        $authorValues = [];
        $authors = $article->getAuthors();
        
        if (is_array($authors)) {
            $locale = (string) $article->getLocale();
            foreach ($authors as $author) {
                $authorValues[] = [
                    'authorName' => [
                        'typeName'  => 'authorName',
                        'typeClass' => 'primitive',
                        'multiple'  => false,
                        'value'     => (string) $author->getFullName(true)
                    ],
                    'authorAffiliation' => [
                        'typeName'  => 'authorAffiliation',
                        'typeClass' => 'primitive',
                        'multiple'  => false,
                        'value'     => $this->formatAffiliation($author, $locale) ?: 'Unspecified'
                    ]
                ];
            }
        }

        $locale = (string) $article->getLocale();
        $descriptionData = $article->getData('studyDescription', $locale);
        $description = !empty($descriptionData) 
            ? (string) $descriptionData 
            : PKPString::html2text((string) $article->getAbstract($locale));

        $descriptionValues = [
            [
                'dsDescriptionValue' => [
                    'typeName'  => 'dsDescriptionValue',
                    'typeClass' => 'primitive',
                    'multiple'  => false,
                    'value'     => (string) ($description ?: 'No abstract provided.')
                ]
            ]
        ];

        $contactEmail = (string) ($journal->getSetting('contactEmail') ?? ('admin@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')));
        $contactValues = [
            [
                'datasetContactEmail' => [
                    'typeName'  => 'datasetContactEmail',
                    'typeClass' => 'primitive',
                    'multiple'  => false,
                    'value'     => $contactEmail
                ]
            ]
        ];

        return [
            'datasetVersion' => [
                'metadataBlocks' => [
                    'citation' => [
                        'displayName' => 'Citation Metadata',
                        'fields' => [
                            [
                                'typeName'  => 'title',
                                'typeClass' => 'primitive',
                                'multiple'  => false,
                                'value'     => (string) $article->getTitle($locale)
                            ],
                            [
                                'typeName'  => 'author',
                                'typeClass' => 'compound',
                                'multiple'  => true,
                                'value'     => $authorValues
                            ],
                            [
                                'typeName'  => 'datasetContact',
                                'typeClass' => 'compound',
                                'multiple'  => true,
                                'value'     => $contactValues
                            ],
                            [
                                'typeName'  => 'dsDescription',
                                'typeClass' => 'compound',
                                'multiple'  => true,
                                'value'     => $descriptionValues
                            ],
                            [
                                'typeName'  => 'subject',
                                'typeClass' => 'controlledVocabulary',
                                'multiple'  => true,
                                'value'     => ['Other']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Create a Dataverse study via REST API.
     * @param object $article
     * @param object $journal
     * @return DataverseStudy|null
     */
    public function createStudy($article, $journal) {
        $jsonMetadata = $this->createJsonMetadata($article, $journal);
        $dvUri = (string) ($this->plugin->getSetting((int) $journal->getId(), 'dvUri') ?? '');
        
        $dataverseAlias = '';
        if (preg_match("/.+\/(\w+)$/", $dvUri, $matches)) {
            $dataverseAlias = $matches[1];
        }

        if (empty($dataverseAlias)) {
            error_log('WIZDAM Dataverse Error: Invalid Dataverse URI format. Cannot extract alias.');
            return null;
        }

        $datasetData = $this->apiClient->createDataset((int) $journal->getId(), $dataverseAlias, $jsonMetadata);

        $study = null;
        if ($datasetData && isset($datasetData['persistentId']) && !empty($datasetData['persistentId'])) {
            $this->plugin->import('classes.DataverseStudy');
            $study = new DataverseStudy();
            $study->setSubmissionId((int) $article->getId());
            $study->setPersistentUri((string) $datasetData['persistentId']);
            $study->setEditUri((string) $datasetData['persistentId']);
            $study->setStatementUri((string) $datasetData['persistentId']);
            $study->setDataCitation((string) $datasetData['persistentId']);
            
            /** @var DataverseStudyDAO $dataverseStudyDao */
            $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');          
            $dataverseStudyDao->insertStudy($study);
        }
        return $study;
    }
    
    /**
     * Update cataloguing information for an existing study.
     * @param object $article
     * @param object $study
     * @param object $journal
     * @return bool
     */
    public function replaceStudyMetadata($article, $study, $journal): bool {
        return true; 
    }
    
    /**
     * Deposit suppfiles in Dataverse study via REST API.
     * @param object $study
     * @param array $suppFiles
     * @param int $journalId
     * @return bool
     */
    public function depositFiles($study, array $suppFiles, int $journalId): bool {
        $persistentId = (string) $study->getPersistentUri();
        if (empty($persistentId)) {
            return false;
        }

        $allUploaded = true;
        
        $this->plugin->import('classes.DataverseFile');         
        /** @var DataverseFileDAO $dvFileDao */
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');

        foreach ($suppFiles as $suppFile) {
            $suppFile->setFileStage(ARTICLE_FILE_SUPP);         
            $filePath = $suppFile->getFilePath();
            
            if (empty($filePath)) {
                $allUploaded = false;
                continue;
            }

            $uploaded = $this->apiClient->uploadFile($journalId, $persistentId, (string) $filePath);
            
            if ($uploaded) {
                $suppFileId = $suppFile->getId();
                $dvFile = $suppFileId !== null ? $dvFileDao->getDataverseFileBySuppFileId((int) $suppFileId) : null;
                
                if ($dvFile === null) {
                    $dvFile = new DataverseFile();
                    if ($suppFileId !== null) {
                        $dvFile->setSuppFileId((int) $suppFileId);
                    }
                    $dvFile->setSubmissionId((int) $study->getSubmissionId());                        
                    $dvFile->setStudyId((int) $study->getId());
                    $dvFile->setContentSourceUri('native-api-file:' . (string) $suppFile->getOriginalFileName());
                    $dvFileDao->insertDataverseFile($dvFile);                                               
                } else {
                    $dvFile->setStudyId((int) $study->getId());
                    $dvFile->setContentSourceUri('native-api-file:' . (string) $suppFile->getOriginalFileName());                       
                    $dvFileDao->updateDataverseFile($dvFile);
                }
            } else {
                $allUploaded = false;
            }
        }
        
        return $allUploaded;
    }   

    /**
     * Release draft study.
     * @param object $study
     * @param object $journal
     * @param object $user
     * @param object $request
     * @return bool
     */
    public function releaseStudy($study, $journal, $user, $request): bool {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();       
        $persistentId = (string) $study->getPersistentUri();

        if (empty($persistentId)) {
            return false;
        }

        $studyReleased = $this->apiClient->publishDataset((int) $journal->getId(), $persistentId, 'major');
        
        if ($studyReleased) {
            $study->setDataCitation($persistentId);
            /** @var DataverseStudyDAO $dataverseStudyDao */
            $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $dataverseStudyDao->updateStudy($study);
            
            $params = ['dataCitation' => $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri())];
            $notificationManager->createTrivialNotification((int) $user->getId(), NOTIFICATION_TYPE_DATAVERSE_STUDY_RELEASED, $params);           
        } else {
            $notificationManager->createTrivialNotification((int) $user->getId(), NOTIFICATION_TYPE_DATAVERSE_ERROR);
        }
        return $studyReleased;
    }
    
    /**
     * Delete draft study.
     * @param object $study
     * @param int $journalId
     * @param int $userId
     * @return bool
     */
    public function deleteStudy($study, int $journalId, int $userId): bool {
        $persistentId = (string) $study->getPersistentUri();
        
        $studyDeleted = $this->apiClient->deleteDataset($journalId, $persistentId);
        
        if ($studyDeleted) {
            /** @var DataverseFileDAO $dvFileDao */
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
            $dvFileDao->deleteDataverseFilesByStudyId((int) $study->getId());
            /** @var DataverseStudyDAO $dataverseStudyDao */
            $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $dataverseStudyDao->deleteStudy($study);
        }

        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $notificationManager->createTrivialNotification($userId, $studyDeleted ? NOTIFICATION_TYPE_DATAVERSE_STUDY_DELETED : NOTIFICATION_TYPE_DATAVERSE_ERROR);
        
        return $studyDeleted;
    }
    
    /**
     * Delete a file from a study.
     * @param object $dvFile
     * @param int $journalId
     * @return bool
     */
    public function deleteFile($dvFile, int $journalId): bool {
        $sourceUri = (string) $dvFile->getContentSourceUri();
        /** @var DataverseFileDAO $dvFileDao */
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');           

        if (strpos($sourceUri, 'native-api-file:') === 0) {
            return $dvFileDao->deleteDataverseFile($dvFile);
        }
        
        return $dvFileDao->deleteDataverseFile($dvFile);
    }

    /**
     * Format author bio statement as affiliation.
     * @param object $author
     * @param string $locale
     * @return string
     */
    public function formatAffiliation($author, string $locale): string {
        $affiliation = '';
        if ($author !== null) {
            $authorAffiliation = $author->getAffiliation($locale);
            if (!empty($authorAffiliation)) {
                $lines = PKPString::regexp_split('/\s*[\r\n]+/s', (string) $authorAffiliation);
                if (is_array($lines)) {
                    $lines = array_map([PKPString::class, 'trimPunctuation'], $lines);
                    $affiliation .= implode(', ', $lines);
                }
                $country = $author->getCountry();
                if (!empty($country)) {
                    $affiliation .= ', ' . (string) $country;
                }
            }
        }
        return $affiliation;
    }
    
}
?>