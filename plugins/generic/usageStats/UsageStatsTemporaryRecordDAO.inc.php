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
 * MODERNIZED FOR PHP 7.4+ (Fork Version)
 */


import('lib.pkp.classes.db.DAO');

class UsageStatsTemporaryRecordDAO extends DAO {

    /** @var object ADORecordSet */
    protected $_result;

    /** @var string */
    protected $_loadId;

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
     * @param $assocType int
     * @param $assocId int
     * @param $day string
     * @param $time int
     * @param $countryCode string
     * @param $region string
     * @param $cityName string
     * @param $fileType int
     * @param $loadId string
     * @return boolean
     */
    public function insert($assocType, $assocId, $day, $time, $countryCode, $region, $cityName, $fileType, $loadId) {
        $this->update(
            'INSERT INTO usage_stats_temporary_records
                (assoc_type, assoc_id, day, entry_time, country_id, region, city, file_type, load_id)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                (int) $assocType,
                (int) $assocId,
                $day,
                (int) $time,
                $countryCode,
                $region,
                $cityName,
                (int) $fileType,
                $loadId // Not number.
            )
        );

        return true;
    }

    /**
     * Get next temporary stats record by load id.
     * @param $loadId string
     * @return mixed array or false if the end of records is reached.
     */
    public function getNextByLoadId($loadId) {
        $returner = false;

        // Cek apakah result set sudah ada dan loadId cocok
        if (!$this->_result || $this->_loadId != $loadId) {
            // Assignment tanpa reference (&)
            $this->_result = $this->_getGrouped($loadId);
            $this->_loadId = $loadId;
        }

        // Assignment local variable tanpa reference
        $result = $this->_result;

        if ($result->EOF) {
            // Wizdam Optimization: Close result set and clear property when iteration finishes
            $result->Close();
            $this->_result = false;
            $this->_loadId = null;
            return $returner;
        }
        
        // GetRowAssoc tanpa reference
        $returner = $result->GetRowAssoc(false);
        $result->MoveNext();
        
        return $returner;
    }

    /**
     * Delete all temporary records associated with the passed load id.
     * @param $loadId string
     * @return boolean
     */
    public function deleteByLoadId($loadId) {
        return $this->update('DELETE from usage_stats_temporary_records WHERE load_id = ?', array($loadId));
    }

    /**
     * Delete the record with the passed assoc id and type with
     * the most recent day value.
     * @param $assocType int
     * @param $assocId int
     * @param $time int
     * @param $loadId string
     * @return boolean
     */
    public function deleteRecord($assocType, $assocId, $time, $loadId) {
        return $this->update(
            'DELETE from usage_stats_temporary_records
            WHERE assoc_type = ? AND assoc_id = ? AND entry_time = ? AND load_id = ?',
            array((int) $assocType, (int) $assocId, (int) $time, $loadId)
        );
    }


    //
    // Protected helper methods.
    //
    /**
    * Get all temporary records with the passed load id grouped.
    * @param $loadId string
    * @return ADORecordSet
    */
    protected function _getGrouped($loadId) {
        // Logika GROUP BY ini sangat penting! 
        // Ini memastikan data diagregasi sebelum masuk ke tabel Metrics.
        // Kita menyertakan region dan city dalam grouping agar hitungan tidak meleset (misal 2 akses dari kota berbeda tidak digabung jadi 1).
        $result = $this->retrieve(
            'SELECT assoc_type, assoc_id, day, country_id, region, city, file_type, load_id, count(metric) as metric
            FROM usage_stats_temporary_records WHERE load_id = ?
            GROUP BY assoc_type, assoc_id, day, country_id, region, city, file_type, load_id',
            array($loadId)
        );

        return $result;
    }
}

?>