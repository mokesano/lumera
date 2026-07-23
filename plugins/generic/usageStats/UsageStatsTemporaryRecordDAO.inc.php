<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/UsageStatsTemporaryRecordDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsTemporaryRecordDAO
 * @ingroup plugins_generic_usageStats
 *
 * @brief Operations for retrieving and adding temporary usage statistics records.
 */

import('lib.pkp.classes.db.DAO');

class UsageStatsTemporaryRecordDAO extends DAO {

    /** @var ADORecordSet|false */
    protected $_result = false;

    /** @var string|null */
    protected $_loadId = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->_result = false;
        $this->_loadId = null;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UsageStatsTemporaryRecordDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::UsageStatsTemporaryRecordDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Add the passed usage statistic record.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param string $day
     * @param mixed $time
     * @param string $countryCode
     * @param string $region
     * @param string $cityName
     * @param mixed $fileType
     * @param string $loadId
     * @return bool
     */
    public function insert($assocType, $assocId, $day, $time, $countryCode, $region, $cityName, $fileType, $loadId) {
        $this->update(
            'INSERT INTO usage_stats_temporary_records
                (assoc_type, assoc_id, day, entry_time, country_id, region, city, file_type, load_id)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $assocType,
                (int) $assocId,
                (string) $day,
                (int) $time,
                (string) $countryCode,
                (string) $region,
                (string) $cityName,
                (int) $fileType,
                (string) $loadId
            ]
        );

        return true;
    }

    /**
     * Get next temporary stats record by load id.
     * @param string $loadId
     * @return array|false
     */
    public function getNextByLoadId($loadId) {
        $returner = false;

        if (!$this->_result || $this->_loadId !== $loadId) {
            $this->_result = $this->_getGrouped($loadId);
            $this->_loadId = $loadId;
        }

        $result = $this->_result;

        if ($result === false || $result->EOF) {
            if ($result !== false) {
                $result->Close();
            }
            $this->_result = false;
            $this->_loadId = null;
            return $returner;
        }

        $returner = $result->getRowAssoc(false);
        $result->MoveNext();
        
        return $returner;
    }

    /**
     * Delete all temporary records associated with the passed load id.
     * @param string $loadId
     * @return bool
     */
    public function deleteByLoadId($loadId) {
        return $this->update('DELETE FROM usage_stats_temporary_records WHERE load_id = ?', [(string) $loadId]);
    }

    /**
     * Delete the record with the passed assoc id and type with the most recent day value.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $time
     * @param string $loadId
     * @return bool
     */
    public function deleteRecord($assocType, $assocId, $time, $loadId) {
        return $this->update(
            'DELETE FROM usage_stats_temporary_records
            WHERE assoc_type = ? AND assoc_id = ? AND entry_time = ? AND load_id = ?',
            [(int) $assocType, (int) $assocId, (int) $time, (string) $loadId]
        );
    }

    //
    // Protected helper methods.
    //
    /**
     * Get all temporary records with the passed load id grouped.
     * @param string $loadId
     * @return ADORecordSet|false
     */
    protected function _getGrouped($loadId) {
        return $this->retrieve(
            'SELECT assoc_type, assoc_id, day, country_id, region, city, file_type, load_id, COUNT(metric) AS metric
            FROM usage_stats_temporary_records WHERE load_id = ?
            GROUP BY assoc_type, assoc_id, day, country_id, region, city, file_type, load_id',
            [(string) $loadId]
        );
    }

}
?>