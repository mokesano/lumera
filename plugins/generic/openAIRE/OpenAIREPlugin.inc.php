<?php
declare(strict_types=1);

/**
 * @file plugins/generic/openAIRE/OpenAIREPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OpenAIREPlugin
 * @ingroup plugins_generic_openAIRE
 *
 * @brief OpenAIRE plugin class
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class OpenAIREPlugin extends GenericPlugin {

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path
     * @return boolean True if plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        
        if ($success && $this->getEnabled()) {
            $this->import('OpenAIREDAO');
            $openAIREDao = new OpenAIREDAO();
            DAORegistry::registerDAO('OpenAIREDAO', $openAIREDao);

            // Insert new field into author metadata submission form (submission step 3) and metadata form
            HookRegistry::register('Templates::Author::Submit::AdditionalMetadata', [$this, 'metadataFieldEdit']);
            HookRegistry::register('Templates::Submission::MetadataEdit::AdditionalMetadata', [$this, 'metadataFieldEdit']);
            
            // Consider the new field in the metadata view
            HookRegistry::register('Templates::Submission::Metadata::Metadata::AdditionalMetadata', [$this, 'metadataFieldView']);

            // Hook for initData in two forms -- init the new field
            HookRegistry::register('metadataform::initdata', [$this, 'metadataInitData']);
            HookRegistry::register('authorsubmitstep3form::initdata', [$this, 'metadataInitData']);

            // Hook for readUserVars in two forms -- consider the new field entry
            HookRegistry::register('metadataform::readuservars', [$this, 'metadataReadUserVars']);
            HookRegistry::register('authorsubmitstep3form::readuservars', [$this, 'metadataReadUserVars']);

            // Hook for execute in two forms -- consider the new field in the article settings
            HookRegistry::register('authorsubmitstep3form::execute', [$this, 'metadataExecute']);
            HookRegistry::register('metadataform::execute', [$this, 'metadataExecute']);

            // Hook for save in two forms -- add validation for the new field
            HookRegistry::register('authorsubmitstep3form::Constructor', [$this, 'addCheck']);
            HookRegistry::register('metadataform::Constructor', [$this, 'addCheck']);

            // Consider the new field for ArticleDAO for storage
            HookRegistry::register('articledao::getAdditionalFieldNames', [$this, 'articleSubmitGetFieldNames']);

            // Add OpenAIRE set to OAI results
            HookRegistry::register('OAIDAO::getJournalSets', [$this, 'sets']);
            HookRegistry::register('JournalOAI::identifiers', [$this, 'recordsOrIdentifiers']);
            HookRegistry::register('JournalOAI::records', [$this, 'recordsOrIdentifiers']);
            HookRegistry::register('OAIDAO::_returnRecordFromRow', [$this, 'addSet']);
            HookRegistry::register('OAIDAO::_returnIdentifierFromRow', [$this, 'addSet']);

             // Change Dc11Desctiption -- consider OpenAIRE elements relation, rights and date
             // Note: 'Desctiption' typo preserved from original code for integrity
            HookRegistry::register('Dc11SchemaArticleAdapter::extractMetadataFromDataObject', [$this, 'changeDc11Desctiption']);

            // consider OpenAIRE articles in article tombstones
            HookRegistry::register('ArticleTombstoneManager::insertArticleTombstone', [$this, 'insertOpenAIREArticleTombstone']);
        }
        return $success;
    }

    /**
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.openAIRE.displayName');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.openAIRE.description');
    }

    /*
     * Metadata
     */

    /**
     * Insert projectID field into author submission step 3 and metadata edit form
     */
    public function metadataFieldEdit($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2]; // Reference needed for primitive/string modification in hook

        $output .= $smarty->fetch($this->getTemplatePath() . 'projectIDEdit.tpl');
        return false;
    }

    /**
     * Add projectID to the metadata view
     */
    public function metadataFieldView($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2]; // Reference needed for primitive/string modification in hook

        $output .= $smarty->fetch($this->getTemplatePath() . 'projectIDView.tpl');
        return false;
    }

    /**
     * Add projectID element to the article
     */
    public function articleSubmitGetFieldNames($hookName, $params) {
        $fields =& $params[1]; // Reference needed for array modification in hook
        $fields[] = 'projectID';
        return false;
    }

    /**
     * Set article projectID
     */
    public function metadataExecute($hookName, $params) {
        $form = $params[0];
        $article = $form->getArticle();
        $formProjectID = $form->getData('projectID');
        $article->setData('projectID', $formProjectID);
        return false;
    }

    /**
     * Add check/validation for the projectID field (= 6 numbers)
     */
    public function addCheck($hookName, $params) {
        $form = $params[0];
        if (get_class($form) == 'AuthorSubmitStep3Form' || get_class($form) == 'MetadataForm' ) {
            $form->addCheck(new FormValidatorRegExp($form, 'projectID', 'optional', 'plugins.generic.openAIRE.projectIDValid', '/^\d{6}$/'));
        }
        return false;
    }

    /**
     * Init article projectID
     */
    public function metadataInitData($hookName, $params) {
        $form = $params[0];
        $article = $form->getArticle();
        $articleProjectID = $article->getData('projectID');
        $form->setData('projectID', $articleProjectID);
        return false;
    }

    /**
     * Concern projectID field in the form
     */
    public function metadataReadUserVars($hookName, $params) {
        $userVars =& $params[1]; // Reference needed for array modification in hook
        $userVars[] = 'projectID';
        return false;
    }


    /*
     * OAI interface
     */

    /**
     * Add OpenAIRE set
     */
    public function sets($hookName, $params) {
        $sets =& $params[5]; // Reference needed for array modification in hook
        array_push($sets, new OAISet('ec_fundedresources', 'EC_fundedresources', ''));
        return false;
    }

    /**
     * Get OpenAIRE records or identifiers
     */
    public function recordsOrIdentifiers($hookName, $params) {
        $journalOAI = $params[0];
        $from = $params[1];
        $until = $params[2];
        $set = $params[3];
        $offset = $params[4];
        $limit = $params[5];
        $total = $params[6];
        $records =& $params[7]; // Reference needed for array output

        $records = [];
        if (isset($set) && $set == 'ec_fundedresources') {
            $openAIREDao = DAORegistry::getDAO('OpenAIREDAO');
            $openAIREDao->setOAI($journalOAI);
            
            $funcName = '';
            if ($hookName == 'JournalOAI::records') {
                $funcName = '_returnRecordFromRow';
            } else if ($hookName == 'JournalOAI::identifiers') {
                $funcName = '_returnIdentifierFromRow';
            }
            
            $journalId = $journalOAI->journalId;
            $records = $openAIREDao->getOpenAIRERecordsOrIdentifiers([$journalId, null], $from, $until, $offset, $limit, $total, $funcName);
            return true;
        }
        return false;
    }

    /**
     * Change OAI record or identifier to consider the OpenAIRE set
     */
    public function addSet($hookName, $params) {
        $record = $params[0];
        $row = $params[1];

        $openAIREDao = DAORegistry::getDAO('OpenAIREDAO');
        if ($openAIREDao->isOpenAIRERecord($row)) {
            $record->sets[] = 'ec_fundedresources';
        }
        return false;
    }

    /**
     * Change Dc11 Description to consider the OpenAIRE elements
     * Note: Typo 'Desctiption' retained to match registration
     */
    public function changeDc11Desctiption($hookName, $params) {
        $adapter = $params[0];
        $article = $params[1];
        $journal = $params[2];
        $issue = $params[3];
        $dc11Description = $params[4]; // Reference needed for object modification (DC Description)

        $openAIREDao = DAORegistry::getDAO('OpenAIREDAO');
        
        // [PHP 8 FIX] Handle undefined $journalOAI safely to prevent Fatal Error
        if (isset($journalOAI)) {
             $openAIREDao->setOAI($journalOAI);
        }

        if ($openAIREDao->isOpenAIREArticle($article->getId())) {

            // Determine OpenAIRE DC elements values
            // OpenAIRE DC Relation
            $articleProjectID = $article->getData('projectID');
            $openAIRERelation = 'info:eu-repo/grantAgreement/EC/FP7/' . $articleProjectID;

            // OpenAIRE DC Rights
            $openAIRERights = 'info:eu-repo/semantics/';
            $status = '';
            
            if ($journal->getSetting('publishingMode') == PUBLISHING_MODE_OPEN) {
                $status = 'openAccess';
            } else if ($journal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION) {
                if ($issue->getAccessStatus() == 0 || $issue->getAccessStatus() == ISSUE_ACCESS_OPEN) {
                    $status = 'openAccess';
                } else if ($issue->getAccessStatus() == ISSUE_ACCESS_SUBSCRIPTION) {
                    if ($article instanceof PublishedArticle && $article->getAccessStatus() == ARTICLE_ACCESS_OPEN) {
                        $status = 'openAccess';
                    } else if ($issue->getAccessStatus() == ISSUE_ACCESS_SUBSCRIPTION && $issue->getOpenAccessDate() != NULL) {
                        $status = 'embargoedAccess';
                    } else if ($issue->getAccessStatus() == ISSUE_ACCESS_SUBSCRIPTION && $issue->getOpenAccessDate() == NULL) {
                        $status = 'closedAccess';
                    }
                }
            }
            
            if ($journal->getSetting('restrictSiteAccess') == 1 || $journal->getSetting('restrictArticleAccess') == 1) {
                $status = 'restrictedAccess';
            }
            $openAIRERights = $openAIRERights . $status;

            // OpenAIRE DC Date
            $openAIREDate = null;
            if ($status == 'embargoedAccess') {
                $openAIREDate = 'info:eu-repo/date/embargoEnd/' . date('Y-m-d', strtotime($issue->getOpenAccessDate()));
            }

            // Get current DC statements
            $dcRelationValues = [];
            $dcRightsValues = [];
            $dcDateValues = [];
            
            if ($dc11Description->hasStatement('dc:relation')) {
                $dcRelationValues = $dc11Description->getStatement('dc:relation');
            }
            if ($dc11Description->hasStatement('dc:rights')) {
                $dcRightsValues = $dc11Description->getStatementTranslations('dc:rights');
            }
            if ($dc11Description->hasStatement('dc:date')) {
                $dcDateValues = $dc11Description->getStatement('dc:date');
            }

            // Set new DC statements, concerning OpenAIRE
            array_unshift($dcRelationValues, $openAIRERelation);
            $newDCRelationStatements = ['dc:relation' => $dcRelationValues];
            $dc11Description->setStatements($newDCRelationStatements);

            foreach ($dcRightsValues as $key => $value) {
                array_unshift($value, $openAIRERights);
                $dcRightsValues[$key] = $value;
            }
            
            if (!array_key_exists($journal->getPrimaryLocale(), $dcRightsValues)) {
                $dcRightsValues[$journal->getPrimaryLocale()] = [$openAIRERights];
            }
            $newDCRightsStatements = ['dc:rights' => $dcRightsValues];
            $dc11Description->setStatements($newDCRightsStatements);

            if ($openAIREDate != null) {
                array_unshift($dcDateValues, $openAIREDate);
                $newDCDateStatements = ['dc:date' => $dcDateValues];
                $dc11Description->setStatements($newDCDateStatements);
            }
        }
        return false;
    }

    /**
     * Consider the OpenAIRE set in the article tombstone
     */
    public function insertOpenAIREArticleTombstone($hookName, $params) {
        $articleTombstone = $params[0];

        $openAIREDao = DAORegistry::getDAO('OpenAIREDAO');
        if ($openAIREDao->isOpenAIREArticle($articleTombstone->getDataObjectId())) {
            $dataObjectTombstoneSettingsDao = DAORegistry::getDAO('DataObjectTombstoneSettingsDAO');
            $dataObjectTombstoneSettingsDao->updateSetting($articleTombstone->getId(), 'openaire', true, 'bool');
        }
        return false;
    }
}
?>