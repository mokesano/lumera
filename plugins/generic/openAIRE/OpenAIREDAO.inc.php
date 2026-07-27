<?php
declare(strict_types=1);

/**
 * @file plugins/generic/openAIRE/OpenAIREDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OpenAIREDAO
 * @ingroup plugins_generic_openAIRE
 *
 * @brief DAO operations for OpenAIRE.
 */

import('classes.oai.ojs.OAIDAO');

class OpenAIREDAO extends OAIDAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function OpenAIREDAO() {
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
     * Set parent OAI object.
     * @param JournalOAI $oai
     * @return void
     */
    public function setOAI($oai) {
        $this->oai = $oai;
    }

    //
    // Records
    //

    /**
     * Return set of OAI records or identifiers matching specified parameters.
     * Note: We rely on manual SQL construction here because the Parent's 
     * legacy _getRecordsRecordSet method was removed during refactoring.
     *
     * @param array $setIds
     * @param int|null $from timestamp
     * @param int|null $until timestamp
     * @param int $offset
     * @param int $limit
     * @param int $total Passed by reference
     * @param string $funcName
     * @return array
     */
    public function getOpenAIRERecordsOrIdentifiers($setIds, $from, $until, $offset, $limit, &$total, $funcName) {
        $records = [];
        $total = 0;

        // 1. Prepare Params
        $params = $this->getOrderedRecordParams(null, $setIds, null);

        // 2. Build SQL manually
        $sql = $this->getRecordSelectStatement() . ' FROM mutex m ' .
               $this->getRecordJoinClause(null, $setIds, null) . ' ' .
               $this->getAccessibleRecordWhereClause() . ' ' .
               $this->getDateRangeWhereClause($from, $until);

        // 3. Execute Query
        $result = $this->retrieve($sql, $params);

        // 4. Handle Pagination & Filtering
        if ($result) {
            $total = $result->RecordCount();
            $result->Move($offset);
            
            for ($count = 0; $count < $limit && !$result->EOF; $count++) {
                $row = $result->GetRowAssoc(false);
                
                // Filter: Only process if it's an OpenAIRE record
                if ($this->isOpenAIRERecord($row)) {
                    $records[] = $this->$funcName($row);
                }
                $result->moveNext();
            }
            $result->Close();
        }

        return $records;
    }

    /**
     * Check if it's an OpenAIRE record, if it contains projectID.
     * @param array $row
     * @return bool
     */
    public function isOpenAIRERecord($row) {
        if (!isset($row['tombstone_id']) || $row['tombstone_id'] === null || $row['tombstone_id'] === '') {
            $params = ['projectID', (int) $row['article_id']];
            $result = $this->retrieve(
                'SELECT COUNT(*) AS count FROM article_settings WHERE setting_name = ? AND setting_value IS NOT NULL AND setting_value <> \'\' AND article_id = ?',
                $params
            );
            
            $returner = false;
            // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF instead of $result->fields
            if ($result && !$result->EOF) {
                $rowAssoc = $result->GetRowAssoc(false);
                $returner = ((int) ($rowAssoc['count'] ?? 0)) > 0;
            }
            
            if ($result) {
                $result->Close();
            }

            return $returner;
        } else {
            /** @var DataObjectTombstoneSettingsDAO $dataObjectTombstoneSettingsDao */
            $dataObjectTombstoneSettingsDao = DAORegistry::getDAO('DataObjectTombstoneSettingsDAO');
            return $dataObjectTombstoneSettingsDao ? (bool) $dataObjectTombstoneSettingsDao->getSetting((int) $row['tombstone_id'], 'openaire') : false;
        }
    }

    /**
     * Check if it's an OpenAIRE article, if it contains projectID.
     * @param int $articleId
     * @return bool
     */
    public function isOpenAIREArticle($articleId) {
        $params = ['projectID', (int) $articleId];
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM article_settings WHERE setting_name = ? AND setting_value IS NOT NULL AND setting_value <> \'\' AND article_id = ?',
            $params
        );
        
        $returner = false;
        if ($result && !$result->EOF) {
            $rowAssoc = $result->GetRowAssoc(false);
            $returner = ((int) ($rowAssoc['count'] ?? 0)) > 0;
        }
        
        if ($result) {
            $result->Close();
        }

        return $returner;
    }
    
}
?>