<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewSettingsDAO
 * @ingroup submission
 *
 * @brief Operations for retrieving and modifying object for review settings.
 */

class ObjectForReviewSettingsDAO extends DAO {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->_parentPluginName = (string) $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function ObjectForReviewSettingsDAO($parentPluginName) {
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
     * Retrieve object for review setting value.
     * @param int $objectId
     * @param int $metadataId
     * @return array|null
     */
    public function getSetting($objectId, $metadataId) {
        $params = [(int) $objectId, (int) $metadataId];
        $sql = 'SELECT * FROM object_for_review_settings WHERE object_id = ? AND review_object_metadata_id = ?';
        $result = $this->retrieve($sql, $params);

        $setting = null;
        if ($result) {
            while (!$result->EOF) {
                // [WIZDAM FIX] Corrected typo from getRowAssoc to GetRowAssoc (standard ADODB)
                $row = $result->GetRowAssoc(false);
                $value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
                $setting[(int) $row['review_object_metadata_id']] = $value;
                $result->MoveNext();
            }
            $result->Close();
        }
        return $setting;
    }

    /**
     * Retrieve all settings for the object for review.
     * @param int $objectId
     * @return array
     */
    public function getSettings($objectId) {
        $result = $this->retrieve(
            'SELECT review_object_metadata_id, setting_value, setting_type FROM object_for_review_settings WHERE object_id = ?', 
            [(int) $objectId]
        );

        $objectForReviewSettings = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
                $objectForReviewSettings[(int) $row['review_object_metadata_id']] = $value;
                $result->MoveNext();
            }
            $result->Close();
        }
        return $objectForReviewSettings;
    }

    /**
     * Add/update an object for review setting.
     * @param int $objectId
     * @param int $metadataId
     * @param mixed $value
     * @param string|null $type
     * @return bool
     */
    public function updateSetting($objectId, $metadataId, $value, $type = null) {
        $keyFields = ['object_id', 'review_object_metadata_id'];
        $dbValue = $this->convertToDB($value, $type);
        
        $this->replace(
            'object_for_review_settings',
            [
                'object_id' => (int) $objectId,
                'review_object_metadata_id' => (int) $metadataId,
                'setting_value' => $dbValue,
                'setting_type' => $type !== null ? (string) $type : null
            ],
            $keyFields
        );
        return true;
    }

    /**
     * Delete an object for review setting.
     * @param int $objectId
     * @param int $metadataId
     * @return bool
     */
    public function deleteSetting($objectId, $metadataId) {
        $params = [(int) $objectId, (int) $metadataId];
        $sql = 'DELETE FROM object_for_review_settings WHERE object_id = ? AND review_object_metadata_id = ?';
        return (bool) $this->update($sql, $params);
    }

    /**
     * Delete all settings for an object for review.
     * @param int $objectId
     * @return bool
     */
    public function deleteSettings($objectId) {
        return (bool) $this->update(
            'DELETE FROM object_for_review_settings WHERE object_id = ?', 
            [(int) $objectId]
        );
    }

    /**
     * Delete settings by review object metadata ID
     * to be called only when deleting a review object metadata.
     * @param int $reviewObjectMetadataId
     * @return bool
     */
    public function deleteByReviewObjectMetadataId($reviewObjectMetadataId) {
        return (bool) $this->update(
            'DELETE FROM object_for_review_settings WHERE review_object_metadata_id = ?', 
            [(int) $reviewObjectMetadataId]
        );
    }

}
?>