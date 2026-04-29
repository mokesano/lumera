<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ReviewObjectMetadataDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectMetadataDAO
 * @ingroup plugins_generic_objectsForReview
 * @see ReviewObjectMetadata
 *
 * @brief Operations for retrieving and modifying ReviewObjectMetadata objects.
 * * MODERNIZED FOR WIZDAM FORK
 */

class ReviewObjectMetadataDAO extends DAO {

    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor.
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->parentPluginName = $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewObjectMetadataDAO($parentPluginName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ReviewObjectMetadataDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($parentPluginName);
    }

    /**
     * Retrieve a review object metadata by ID.
     * @param $metadataId int
     * @param $reviewObjectTypeId int (optional)
     * @return ReviewObjectMetadata
     */
    public function getById($metadataId, $reviewObjectTypeId = null) {
        $params = array((int) $metadataId);
        if ($reviewObjectTypeId) $params[] = (int) $reviewObjectTypeId;

        $result = $this->retrieve(
            'SELECT * FROM review_object_metadata WHERE metadata_id = ?' . ($reviewObjectTypeId ? ' AND review_object_type_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve review object metadata by key.
     * @param $key string
     * @param $reviewObjectTypeId int (optional)
     * @return ReviewObjectMetadata
     */
    public function getByKey($key, $reviewObjectTypeId = null) {
        $params = array($key);
        if ($reviewObjectTypeId) $params[] = (int) $reviewObjectTypeId;

        $result = $this->retrieve(
            'SELECT * FROM review_object_metadata WHERE metadata_key = ?' . ($reviewObjectTypeId ? ' AND review_object_type_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ReviewObjectMetadata
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        return new ReviewObjectMetadata();
    }

    /**
     * Internal function to return a ReviewObjectMetadata object from a row.
     * @param $row array
     * @return ReviewObjectMetadata
     */
    public function _fromRow($row) {
        $reviewObjectMetadata = $this->newDataObject();
        $reviewObjectMetadata->setId($row['metadata_id']);
        $reviewObjectMetadata->setReviewObjectTypeId($row['review_object_type_id']);
        $reviewObjectMetadata->setSequence($row['seq']);
        $reviewObjectMetadata->setMetadataType($row['metadata_type']);
        $reviewObjectMetadata->setRequired($row['required']);
        $reviewObjectMetadata->setDisplay($row['display']);
        $reviewObjectMetadata->setKey($row['metadata_key']);

        $this->getDataObjectSettings('review_object_metadata_settings', 'metadata_id', $row['metadata_id'], $reviewObjectMetadata);

        HookRegistry::dispatch('ReviewObjectMetadataDAO::_fromRow', array(&$reviewObjectMetadata, &$row));

        return $reviewObjectMetadata;
    }

    /**
     * Get the list of fields for which data can be localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array('name', 'possibleOptions');
    }

    /**
     * Update the localized fields for this table
     * @param $reviewObjectMetadata ReviewObjectMetadata
     */
    public function updateLocaleFields($reviewObjectMetadata) {
        $this->updateDataObjectSettings('review_object_metadata_settings', $reviewObjectMetadata, array(
            'metadata_id' => $reviewObjectMetadata->getId()
        ));
    }

    /**
     * Insert a new review object metadata.
     * @param $reviewObjectMetadata ReviewObjectMetadata
     * @return int
     */
    public function insertObject($reviewObjectMetadata) {
        $this->update(
            'INSERT INTO review_object_metadata
                (review_object_type_id, seq, metadata_type, required, display, metadata_key)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            array(
                (int) $reviewObjectMetadata->getReviewObjectTypeId(),
                $reviewObjectMetadata->getSequence() == null ? 0 : $reviewObjectMetadata->getSequence(),
                $reviewObjectMetadata->getMetadataType(),
                $reviewObjectMetadata->getRequired() ? 1 : 0,
                $reviewObjectMetadata->getDisplay() ? 1 : 0,
                $reviewObjectMetadata->getKey()
                )
        );
        $reviewObjectMetadata->setId($this->getInsertId());
        $this->updateLocaleFields($reviewObjectMetadata);
        return $reviewObjectMetadata->getId();
    }

    /**
     * Update an existing review object metadata.
     * @param $reviewObjectMetadata ReviewObjectMetadata
     * @return boolean
     */
    public function updateObject($reviewObjectMetadata) {
        $returner = $this->update(
            'UPDATE review_object_metadata
                SET
                    review_object_type_id = ?,
                    seq = ?,
                    metadata_type = ?,
                    required = ?,
                    display = ?,
                    metadata_key = ?
                WHERE    metadata_id = ?',
            array(
                (int) $reviewObjectMetadata->getReviewObjectTypeId(),
                $reviewObjectMetadata->getSequence(),
                $reviewObjectMetadata->getMetadataType(),
                $reviewObjectMetadata->getRequired(),
                $reviewObjectMetadata->getDisplay(),
                $reviewObjectMetadata->getKey(),
                (int) $reviewObjectMetadata->getId()
            )
        );
        $this->updateLocaleFields($reviewObjectMetadata);
        return $returner;
    }

    /**
     * Delete a review object metadata.
     * @param $reviewObjectMetadata ReviewObjectMetadata
     */
    public function deleteObject($reviewObjectMetadata) {
        return $this->deleteById($reviewObjectMetadata->getId());
    }

    /**
     * Delete a review object metadata by ID.
     * @param $metadataId int
     * @param $reviewObjectTypeId int (optional)
     */
    public function deleteById($metadataId, $reviewObjectTypeId = null) {
        $params = array((int) $metadataId);
        if ($reviewObjectTypeId) $params[] = (int) $reviewObjectTypeId;

        $this->update('DELETE FROM review_object_metadata WHERE metadata_id = ?' . ($reviewObjectTypeId ? ' AND review_object_type_id = ?' : ''),
            $params
        );
        if ($this->getAffectedRows()) {
            // Delete settings
            $this->update('DELETE FROM review_object_metadata_settings WHERE metadata_id = ?',
                (int) $metadataId
            );
            // Delete the same objects for review metadata
            $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
            $ofrSettingsDao->deleteByReviewObjectMetadataId($metadataId);
        }
    }

    /**
     * Delete review object metadata by review object type ID
     * to be called only when deleting a review object type.
     * @param $reviewObjectTypeId int
     */
    public function deleteByReviewObjectTypeId($reviewObjectTypeId) {
        $allMetadata = $this->getArrayByReviewObjectTypeId($reviewObjectTypeId);
        foreach ($allMetadata as $metadataId => $reviewObjectMetadata) {
            $this->deleteById($metadataId);
        }
    }

    /**
     * Delete a review object metadata setting
     * @param $metadataId int
     * @param $name string
     * @param $locale string (optional)
     */
    public function deleteSetting($metadataId, $name, $locale = null) {
        $params = array((int) $metadataId, $name);
        $sql = 'DELETE FROM review_object_metadata_settings WHERE metadata_id = ? AND setting_name = ?';
        if ($locale !== null) {
            $params[] = $locale;
            $sql .= ' AND locale = ?';
        }
        return $this->update($sql, $params);
    }

    /**
     * Retrieve metadata ID by review object type ID and metadata key.
     * @param $reviewObjectTypeId int
     * @param $key string
     * @return int
     */
    public function getMetadataId($reviewObjectTypeId, $key) {
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? AND metadata_key = ? ORDER BY seq',
            array((int) $reviewObjectTypeId, $key)
        );
        $returner = isset($result->fields[0]) ? $result->fields[0] : false;
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve all metadata array for a review object type.
     * @param $reviewObjectTypeId int
     * @return array ReviewObjectMetadata ordered by sequence
     */
    public function getArrayByReviewObjectTypeId($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT * FROM review_object_metadata WHERE review_object_type_id = ? ORDER BY seq',
            (int) $reviewObjectTypeId
        );

        $allMetadata = array();
        while (!$result->EOF) {
            $reviewObjectMetadata = $this->_fromRow($result->GetRowAssoc(false));
            $allMetadata[$reviewObjectMetadata->getId()] = $reviewObjectMetadata;
            $result->MoveNext();
        }
        $result->Close();
        return $allMetadata;
    }

    /**
     * Retrieve all metadata for a review object type.
     * @param $reviewObjectTypeId int
     * @param $rangeInfo DBResultRange (optional)
     * @return DAOResultFactory containing ReviewObjectMetadata ordered by sequence
     */
    public function getByReviewObjectTypeId($reviewObjectTypeId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM review_object_metadata WHERE review_object_type_id = ? ORDER BY seq',
            (int) $reviewObjectTypeId, $rangeInfo
        );
        $returner = new DAOResultFactory($result, $this, '_fromRow');
        return $returner;
    }

    /**
     * Retrieve IDs of all required metadata for a review object type.
     * @param $reviewObjectTypeId int
     * @return array
     */
    public function getRequiredReviewObjectMetadataIds($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? AND required = 1 ORDER BY seq',
            (int) $reviewObjectTypeId
        );

        $requiredReviewObjectMetadataIds = array();
        while (!$result->EOF) {
            $requiredReviewObjectMetadataIds[] = $result->fields[0];
            $result->MoveNext();
        }
        $result->Close();
        return $requiredReviewObjectMetadataIds;
    }

    /**
     * Retrieve IDs of all metadata that should be displayed for a review object type.
     * Except Title - it is always displayed.
     * @param $reviewObjectTypeId int (optional)
     * @return array
     */
    public function getDisplayReviewObjectMetadataIds($reviewObjectTypeId = null) {
        if ($reviewObjectTypeId) $params[] = (int) $reviewObjectTypeId;
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE display = 1 AND metadata_key <> \'title\'' . ($reviewObjectTypeId ? ' AND review_object_type_id = ?' : '') . ' ORDER BY seq',
            $params
        );

        $displayReviewObjectMetadataIds = array();
        while (!$result->EOF) {
            $displayReviewObjectMetadataIds[] = $result->fields[0];
            $result->MoveNext();
        }
        $result->Close();
        return $displayReviewObjectMetadataIds;
    }

    /**
     * Retrieve IDs of all metadata of the type textarea for a review object type.
     * @param $reviewObjectTypeId int
     * @return array
     */
    public function getTextareaReviewObjectMetadataIds($reviewObjectTypeId) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');

        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? AND metadata_type = ? ORDER BY seq',
            array((int) $reviewObjectTypeId, REVIEW_OBJECT_METADATA_TYPE_TEXTAREA)
        );

        $textareaReviewObjectMetadataIds = array();
        while (!$result->EOF) {
            $textareaReviewObjectMetadataIds[] = $result->fields[0];
            $result->MoveNext();
        }
        $result->Close();
        return $textareaReviewObjectMetadataIds;
    }

    /**
     * Check if the review object metadata exists.
     * @param $metadataId int
     * @param $reviewObjectTypeId int (optional)
     * @return boolean
     */
    public function reviewObjectMetadataExists($metadataId, $reviewObjectTypeId = null) {
        $params = array((int) $metadataId);
        if ($reviewObjectTypeId) $params[] = (int) $reviewObjectTypeId;

        $result = $this->retrieve(
            'SELECT COUNT(*) FROM review_object_metadata WHERE metadata_id = ?' . ($reviewObjectTypeId ? ' AND review_object_type_id = ?' : ''),
            $params
        );

        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;
        $result->Close();
        return $returner;
    }

    /**
     * Sequentially renumber review object metadata in their sequence order.
     * @param $reviewObjectTypeId int
     */
    public function resequence($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? ORDER BY seq', (int) $reviewObjectTypeId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($metadataId) = $result->fields;
            $this->update(
                'UPDATE review_object_metadata SET seq = ? WHERE metadata_id = ?',
                array(
                    $i,
                    $metadataId
                )
            );
            $result->MoveNext();
        }
        $result->Close();
    }

    /**
     * Get the ID of the last inserted review object metadata.
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return parent::getInsertId('review_object_metadata', 'metadata_id', $callHooks);
    }

}

?>