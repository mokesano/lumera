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
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.oai.ojs.OAIDAO');

class OpenAIREDAO extends OAIDAO {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OpenAIREDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::OpenAIREDAO(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Set parent OAI object.
     * @param JournalOAI $oai
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
     * @param array $setIds Objects ids that specify an OAI set, in this case only journal ID.
     * @param int $from timestamp
     * @param int $until timestamp
     * @param int $offset
     * @param int $limit
     * @param int $total Output parameter for total count
     * @param string $funcName Function to call to convert row to object
     * @return array OAIRecord
     */
    public function getOpenAIRERecordsOrIdentifiers($setIds, $from, $until, $offset, $limit, &$total, $funcName) {
        $records = [];

        // 1. Prepare Params
        // Note: passing null for $set because OpenAIRE handles sets differently or expects raw IDs
        $params = $this->getOrderedRecordParams(null, $setIds, null);

        // 2. Build SQL manually (Replaces the old _getRecordsRecordSet)
        // We use the helper methods available in the modernized PKPOAIDAO
        $sql = $this->getRecordSelectStatement() . ' FROM mutex m ' .
               $this->getRecordJoinClause(null, $setIds, null) . ' ' .
               $this->getAccessibleRecordWhereClause() . ' ' .
               $this->getDateRangeWhereClause($from, $until);

        // 3. Execute Query
        $result = $this->retrieve($sql, $params);

        // 4. Handle Pagination & Filtering
        // Note: We calculate total based on DB rows before filtering, 
        // matching original OJS behavior (though arguably imprecise)
        $total = $result->RecordCount();

        $result->Move($offset);
        
        for ($count = 0; $count < $limit && !$result->EOF; $count++) {
            $row = $result->GetRowAssoc(false);
            
            // Filter: Only process if it's an OpenAIRE record
            if ($this->isOpenAIRERecord($row)) {
                // Call the conversion function ($funcName is usually _returnRecordFromRow)
                // We call it dynamically via $this
                $records[] = $this->$funcName($row);
            }
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $records;
    }

    /**
     * Check if it's an OpenAIRE record, if it contains projectID.
     * @param array $row array of database fields
     * @return boolean
     */
    public function isOpenAIRERecord($row) {
        if (!isset($row['tombstone_id'])) {
            $params = ['projectID', (int) $row['article_id']];
            $result = $this->retrieve(
                'SELECT COUNT(*) FROM article_settings WHERE setting_name = ? AND setting_value IS NOT NULL AND setting_value <> \'\' AND article_id = ?',
                $params
            );
            $returner = (isset($result->fields[0]) && $result->fields[0] == 1) ? true : false;
            $result->Close();
            unset($result);

            return $returner;
        } else {
            $dataObjectTombstoneSettingsDao = DAORegistry::getDAO('DataObjectTombstoneSettingsDAO');
            return $dataObjectTombstoneSettingsDao->getSetting($row['tombstone_id'], 'openaire');
        }
    }

    /**
     * Check if it's an OpenAIRE article, if it contains projectID.
     * @param int $articleId
     * @return boolean
     */
    public function isOpenAIREArticle($articleId) {
        $params = ['projectID', (int) $articleId];
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM article_settings WHERE setting_name = ? AND setting_value IS NOT NULL AND setting_value <> \'\' AND article_id = ?',
            $params
        );
        $returner = (isset($result->fields[0]) && $result->fields[0] == 1) ? true : false;
        $result->Close();
        unset($result);

        return $returner;
    }
}
?>