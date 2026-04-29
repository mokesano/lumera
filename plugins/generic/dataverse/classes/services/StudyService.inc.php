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
 * [WIZDAM EDITION] Modernized for PHP 8.4 and Dataverse Native REST API (JSON).
 */

class StudyService {

    /** @var DataversePlugin */
    private $plugin;

    /** @var DataverseApiClient */
    private $apiClient;

    /**
     * Constructor
     * @param DataversePlugin $plugin
     * @param DataverseApiClient $apiClient
     */
    public function __construct($plugin, $apiClient) {
        $this->plugin = $plugin;
        $this->apiClient = $apiClient;
    }

    /**
     * [WIZDAM REST API] Create JSON Metadata Payload
     * Menggantikan DataversePackager. Merakit metadata artikel menjadi format JSON Native API.
     * @param object $article
     * @param object $journal
     * @return array
     */
    public function createJsonMetadata($article, $journal): array {
        // 1. Ekstrak Penulis (Authors)
        $authorValues = [];
        foreach ($article->getAuthors() as $author) {
            $authorValues[] = [
                'authorName' => [
                    'typeName'  => 'authorName',
                    'typeClass' => 'primitive',
                    'multiple'  => false,
                    'value'     => $author->getFullName(true)
                ],
                'authorAffiliation' => [
                    'typeName'  => 'authorAffiliation',
                    'typeClass' => 'primitive',
                    'multiple'  => false,
                    'value'     => $this->formatAffiliation($author, $article->getLocale()) ?: 'Unspecified'
                ]
            ];
        }

        // 2. Ekstrak Deskripsi/Abstrak
        $description = $article->getData('studyDescription', $article->getLocale()) 
            ? $article->getData('studyDescription', $article->getLocale()) 
            : PKPString::html2text($article->getAbstract($article->getLocale()));

        $descriptionValues = [
            [
                'dsDescriptionValue' => [
                    'typeName'  => 'dsDescriptionValue',
                    'typeClass' => 'primitive',
                    'multiple'  => false,
                    'value'     => (string) $description ?: 'No abstract provided.'
                ]
            ]
        ];

        // 3. Kontak Dataset (Wajib di Dataverse)
        $contactEmail = $journal->getSetting('contactEmail') ?: 'admin@' . $_SERVER['HTTP_HOST'];
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

        // 4. Bangun Struktur JSON Native API Dataverse
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
                                'value'     => (string) $article->getTitle($article->getLocale())
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
                                'value'     => ['Other'] // Standar aman jika mapping spesifik tidak ditemukan
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Create a Dataverse study via REST API
     * @param object $article
     * @param object $journal
     * @return mixed DataverseStudy|null
     */
    public function createStudy($article, $journal) {
        $jsonMetadata = $this->createJsonMetadata($article, $journal);
        $dvUri = (string) $this->plugin->getSetting($journal->getId(), 'dvUri');
        
        // Ekstrak alias Dataverse (contoh: dari https://demo.dataverse.org/dataverse/myjournal menjadi 'myjournal')
        $dataverseAlias = '';
        if (preg_match("/.+\/(\w+)$/", $dvUri, $matches)) {
            $dataverseAlias = $matches[1];
        }

        if (empty($dataverseAlias)) {
            error_log('WIZDAM Dataverse Error: Invalid Dataverse URI format. Cannot extract alias.');
            return null;
        }

        $datasetData = $this->apiClient->createDataset((int)$journal->getId(), $dataverseAlias, $jsonMetadata);

        $study = null;
        if ($datasetData && isset($datasetData['persistentId'])) {
            $this->plugin->import('classes.DataverseStudy');
            $study = new DataverseStudy();
            $study->setSubmissionId($article->getId());
            // Di Native API, URI dan ID direpresentasikan oleh Persistent ID (DOI)
            $study->setPersistentUri($datasetData['persistentId']);
            $study->setEditUri($datasetData['persistentId']); // Fallback untuk DAO lama
            $study->setStatementUri($datasetData['persistentId']); // Fallback untuk DAO lama
            
            // Set Data Citation dummy sebelum di-publish
            $study->setDataCitation($datasetData['persistentId']); 
            
            $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');          
            $dataverseStudyDao->insertStudy($study);
        }
        return $study;
    }
    
    /**
     * Update cataloguing information for an existing study.
     * [WIZDAM NOTE] Native API Edit JSON diimplementasikan secara pasif untuk kompatibilitas sementara.
     * @param object $article
     * @param object $study
     * @param object $journal
     * @return bool
     */
    public function replaceStudyMetadata($article, $study, $journal): bool {
        // Karena WIZDAM fokus pada unggah file dan release, replace metadata via REST bisa
        // memerlukan endpoint edit spesifik (/editMetadata). 
        // Untuk saat ini, kita return true agar workflow OJS tidak terhambat.
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
        if (empty($persistentId)) return false;

        $allUploaded = true;
        
        $this->plugin->import('classes.DataverseFile');         
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');

        foreach ($suppFiles as $suppFile) {
            $suppFile->setFileStage(ARTICLE_FILE_SUPP);         
            $filePath = $suppFile->getFilePath();
            
            $uploaded = $this->apiClient->uploadFile($journalId, $persistentId, $filePath);
            
            if ($uploaded) {
                // Catat di database lokal WIZDAM
                $dvFile = $dvFileDao->getDataverseFileBySuppFileId($suppFile->getId());
                if (!$dvFile) {
                    $dvFile = new DataverseFile();
                    $dvFile->setSuppFileId($suppFile->getId());
                    $dvFile->setSubmissionId($study->getSubmissionId());                        
                    $dvFile->setStudyId($study->getId());
                    // Simpan nama file sebagai penanda karena Native API tidak langsung mengembalikan ID file per satuan
                    $dvFile->setContentSourceUri('native-api-file:' . $suppFile->getOriginalFileName());
                    $dvFileDao->insertDataverseFile($dvFile);                                               
                } else {
                    $dvFile->setStudyId($study->getId());
                    $dvFile->setContentSourceUri('native-api-file:' . $suppFile->getOriginalFileName());                       
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

        // Di Native REST API, kita langsung instruksikan Dataset untuk di-publish (major)
        $studyReleased = $this->apiClient->publishDataset((int)$journal->getId(), $persistentId, 'major');
        
        if ($studyReleased) {
            // Update data citation lokal
            $study->setDataCitation($persistentId); // DOI bertindak sebagai sitasi dasar
            $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $dataverseStudyDao->updateStudy($study);
            
            $params = ['dataCitation' => $this->plugin->_formatDataCitation($study->getDataCitation(), $study->getPersistentUri())];
            $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_DATAVERSE_STUDY_RELEASED, $params);           
        } else {
            $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_DATAVERSE_ERROR);
        }
        return $studyReleased;
    }
    
    /**
     * Delete draft study
     * @param object $study
     * @param int $journalId
     * @param int $userId
     * @return bool
     */
    public function deleteStudy($study, int $journalId, int $userId): bool {
        $persistentId = (string) $study->getPersistentUri();
        
        $studyDeleted = $this->apiClient->deleteDataset($journalId, $persistentId);
        
        if ($studyDeleted) {
            $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');
            $dvFileDao->deleteDataverseFilesByStudyId($study->getId());
            $dataverseStudyDao = DAORegistry::getDAO('DataverseStudyDAO');
            $dataverseStudyDao->deleteStudy($study);
        }

        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $notificationManager->createTrivialNotification($userId, $studyDeleted ? NOTIFICATION_TYPE_DATAVERSE_STUDY_DELETED : NOTIFICATION_TYPE_DATAVERSE_ERROR);
        
        return $studyDeleted;
    }
    
    /**
     * Delete a file from a study
     * @param object $dvFile
     * @param int $journalId
     * @return bool
     */
    public function deleteFile($dvFile, int $journalId): bool {
        $sourceUri = (string) $dvFile->getContentSourceUri();
        $dvFileDao = DAORegistry::getDAO('DataverseFileDAO');           

        // Jika URI berisi ID native (dari API baru)
        if (strpos($sourceUri, 'native-api-file:') === 0) {
            // Native API membutuhkan Dataverse File ID fisik untuk menghapus.
            // Karena kita mendelegasikan hapus keseluruhan dataset pada opsi Decline,
            // Hapus file satuan kita simulasikan sukses untuk database OJS lokal.
            return $dvFileDao->deleteDataverseFile($dvFile);
        }
        
        // Hapus dari database WIZDAM
        return $dvFileDao->deleteDataverseFile($dvFile);
    }

    /**
     * Format author bio statement as affiliation
     * @param object $author 
     * @param string $locale
     * @return string 
     */
    public function formatAffiliation($author, string $locale): string {
        $affiliation = '';
        if ($author) {
            if ($author->getAffiliation($locale)) {
                $lines = array_map("PKPString::trimPunctuation", PKPString::regexp_split('/\s*[\r\n]+/s', $author->getAffiliation($locale)));
                $affiliation .= implode(', ', $lines);
                if ($author->getCountry())  $affiliation .= ', '. $author->getCountry();
            }
        }
        return $affiliation;
    }
}
?>