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
 */

class ReviewObjectMetadataDAO extends DAO {

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
    public function ReviewObjectMetadataDAO($parentPluginName) {
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
     * Retrieve a review object metadata by ID.
     * @param int $metadataId
     * @param int|null $reviewObjectTypeId
     * @return ReviewObjectMetadata|null
     */
    public function getById($metadataId, $reviewObjectTypeId = null) {
        $params = [(int) $metadataId];
        if ($reviewObjectTypeId !== null) {
            $params[] = (int) $reviewObjectTypeId;
        }

        $result = $this->retrieve(
            'SELECT * FROM review_object_metadata WHERE metadata_id = ?' . ($reviewObjectTypeId !== null ? ' AND review_object_type_id = ?' : ''),
            $params
        );

        $returner = null;
        // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF
        if ($result && !$result->EOF) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve review object metadata by key.
     * @param string $key
     * @param int|null $reviewObjectTypeId
     * @return ReviewObjectMetadata|null
     */
    public function getByKey($key, $reviewObjectTypeId = null) {
        $params = [(string) $key];
        if ($reviewObjectTypeId !== null) {
            $params[] = (int) $reviewObjectTypeId;
        }

        $result = $this->retrieve(
            'SELECT * FROM review_object_metadata WHERE metadata_key = ?' . ($reviewObjectTypeId !== null ? ' AND review_object_type_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ReviewObjectMetadata
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        return new ReviewObjectMetadata();
    }

    /**
     * Internal function to return a ReviewObjectMetadata object from a row.
     * @param array $row
     * @return ReviewObjectMetadata
     */
    public function _fromRow($row) {
        $reviewObjectMetadata = $this->newDataObject();
        $reviewObjectMetadata->setId((int) $row['metadata_id']);
        $reviewObjectMetadata->setReviewObjectTypeId((int) $row['review_object_type_id']);
        $reviewObjectMetadata->setSequence((float) $row['seq']);
        $reviewObjectMetadata->setMetadataType((int) $row['metadata_type']);
        $reviewObjectMetadata->setRequired((int) $row['required']);
        $reviewObjectMetadata->setDisplay((int) $row['display']);
        $reviewObjectMetadata->setKey((string) $row['metadata_key']);

        $this->getDataObjectSettings('review_object_metadata_settings', 'metadata_id', (int) $row['metadata_id'], $reviewObjectMetadata);

        // Note: HookRegistry dispatch with references is legacy but preserved for signature compatibility
        $tempMetadata = $reviewObjectMetadata;
        $tempRow = $row;
        HookRegistry::dispatch('ReviewObjectMetadataDAO::_fromRow', [&$tempMetadata, &$tempRow]);

        return $reviewObjectMetadata;
    }

    /**
     * Get the list of fields for which data can be localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['name', 'possibleOptions'];
    }

    /**
     * Update the localized fields for this table.
     * @param ReviewObjectMetadata $reviewObjectMetadata
     * @return void
     */
    public function updateLocaleFields($reviewObjectMetadata) {
        $this->updateDataObjectSettings('review_object_metadata_settings', $reviewObjectMetadata, [
            'metadata_id' => (int) $reviewObjectMetadata->getId()
        ]);
    }

    /**
     * Insert a new review object metadata.
     * @param ReviewObjectMetadata $reviewObjectMetadata
     * @return int
     */
    public function insertObject($reviewObjectMetadata) {
        $this->update(
            'INSERT INTO review_object_metadata
                (review_object_type_id, seq, metadata_type, required, display, metadata_key)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            [
                (int) $reviewObjectMetadata->getReviewObjectTypeId(),
                $reviewObjectMetadata->getSequence() === null ? 0.0 : (float) $reviewObjectMetadata->getSequence(),
                (int) $reviewObjectMetadata->getMetadataType(),
                (int) $reviewObjectMetadata->getRequired(),
                (int) $reviewObjectMetadata->getDisplay(),
                (string) $reviewObjectMetadata->getKey()
            ]
        );
        $reviewObjectMetadata->setId((int) $this->getInsertId());
        $this->updateLocaleFields($reviewObjectMetadata);
        return $reviewObjectMetadata->getId();
    }

    /**
     * Update an existing review object metadata.
     * @param ReviewObjectMetadata $reviewObjectMetadata
     * @return bool
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
                WHERE metadata_id = ?',
            [
                (int) $reviewObjectMetadata->getReviewObjectTypeId(),
                (float) $reviewObjectMetadata->getSequence(),
                (int) $reviewObjectMetadata->getMetadataType(),
                (int) $reviewObjectMetadata->getRequired(),
                (int) $reviewObjectMetadata->getDisplay(),
                (string) $reviewObjectMetadata->getKey(),
                (int) $reviewObjectMetadata->getId()
            ]
        );
        $this->updateLocaleFields($reviewObjectMetadata);
        return (bool) $returner;
    }

    /**
     * Delete a review object metadata.
     * @param ReviewObjectMetadata $reviewObjectMetadata
     * @return bool
     */
    public function deleteObject($reviewObjectMetadata) {
        return $this->deleteById((int) $reviewObjectMetadata->getId());
    }

    /**
     * Delete a review object metadata by ID.
     * @param int $metadataId
     * @param int|null $reviewObjectTypeId
     * @return void
     */
    public function deleteById($metadataId, $reviewObjectTypeId = null) {
        $params = [(int) $metadataId];
        if ($reviewObjectTypeId !== null) {
            $params[] = (int) $reviewObjectTypeId;
        }

        $this->update(
            'DELETE FROM review_object_metadata WHERE metadata_id = ?' . ($reviewObjectTypeId !== null ? ' AND review_object_type_id = ?' : ''),
            $params
        );
        
        if ($this->getAffectedRows()) {
            $this->update(
                'DELETE FROM review_object_metadata_settings WHERE metadata_id = ?',
                [(int) $metadataId]
            );
            
            /** @var ObjectForReviewSettingsDAO $ofrSettingsDao */
            $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
            $ofrSettingsDao->deleteByReviewObjectMetadataId((int) $metadataId);
        }
    }

    /**
     * Delete review object metadata by review object type ID
     * to be called only when deleting a review object type.
     * @param int $reviewObjectTypeId
     * @return void
     */
    public function deleteByReviewObjectTypeId($reviewObjectTypeId) {
        $allMetadata = $this->getArrayByReviewObjectTypeId((int) $reviewObjectTypeId);
        foreach ($allMetadata as $metadataId => $reviewObjectMetadata) {
            $this->deleteById((int) $metadataId);
        }
    }

    /**
     * Delete a review object metadata setting.
     * @param int $metadataId
     * @param string $name
     * @param string|null $locale
     * @return bool
     */
    public function deleteSetting($metadataId, $name, $locale = null) {
        $params = [(int) $metadataId, (string) $name];
        $sql = 'DELETE FROM review_object_metadata_settings WHERE metadata_id = ? AND setting_name = ?';
        if ($locale !== null) {
            $params[] = (string) $locale;
            $sql .= ' AND locale = ?';
        }
        return (bool) $this->update($sql, $params);
    }

    /**
     * Retrieve metadata ID by review object type ID and metadata key.
     * @param int $reviewObjectTypeId
     * @param string $key
     * @return int|false
     */
    public function getMetadataId($reviewObjectTypeId, $key) {
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? AND metadata_key = ? ORDER BY seq',
            [(int) $reviewObjectTypeId, (string) $key]
        );
        
        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['metadata_id'];
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve all metadata array for a review object type.
     * @param int $reviewObjectTypeId
     * @return array ReviewObjectMetadata ordered by sequence
     */
    public function getArrayByReviewObjectTypeId($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT * FROM review_object_metadata WHERE review_object_type_id = ? ORDER BY seq',
            [(int) $reviewObjectTypeId]
        );

        $allMetadata = [];
        if ($result) {
            while (!$result->EOF) {
                $reviewObjectMetadata = $this->_fromRow($result->GetRowAssoc(false));
                $allMetadata[(int) $reviewObjectMetadata->getId()] = $reviewObjectMetadata;
                $result->MoveNext();
            }
            $result->Close();
        }
        return $allMetadata;
    }

    /**
     * Retrieve all metadata for a review object type.
     * @param int $reviewObjectTypeId
     * @param mixed $rangeInfo DBResultRange (optional)
     * @return DAOResultFactory containing ReviewObjectMetadata ordered by sequence
     */
    public function getByReviewObjectTypeId($reviewObjectTypeId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM review_object_metadata WHERE review_object_type_id = ? ORDER BY seq',
            [(int) $reviewObjectTypeId], 
            $rangeInfo
        );
        return new DAOResultFactory($result, $this, '_fromRow');
    }

    /**
     * Retrieve IDs of all required metadata for a review object type.
     * @param int $reviewObjectTypeId
     * @return array
     */
    public function getRequiredReviewObjectMetadataIds($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? AND required = 1 ORDER BY seq',
            [(int) $reviewObjectTypeId]
        );

        $requiredReviewObjectMetadataIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $requiredReviewObjectMetadataIds[] = (int) $row['metadata_id'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $requiredReviewObjectMetadataIds;
    }

    /**
     * Retrieve IDs of all metadata that should be displayed for a review object type.
     * Except Title - it is always displayed.
     * @param int|null $reviewObjectTypeId
     * @return array
     */
    public function getDisplayReviewObjectMetadataIds($reviewObjectTypeId = null) {
        $params = [];
        if ($reviewObjectTypeId !== null) {
            $params[] = (int) $reviewObjectTypeId;
        }
        
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE display = 1 AND metadata_key <> \'title\'' . ($reviewObjectTypeId !== null ? ' AND review_object_type_id = ?' : '') . ' ORDER BY seq',
            $params
        );

        $displayReviewObjectMetadataIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $displayReviewObjectMetadataIds[] = (int) $row['metadata_id'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $displayReviewObjectMetadataIds;
    }

    /**
     * Retrieve IDs of all metadata of the type textarea for a review object type.
     * @param int $reviewObjectTypeId
     * @return array
     */
    public function getTextareaReviewObjectMetadataIds($reviewObjectTypeId) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');

        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? AND metadata_type = ? ORDER BY seq',
            [(int) $reviewObjectTypeId, REVIEW_OBJECT_METADATA_TYPE_TEXTAREA]
        );

        $textareaReviewObjectMetadataIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $textareaReviewObjectMetadataIds[] = (int) $row['metadata_id'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $textareaReviewObjectMetadataIds;
    }

    /**
     * Check if the review object metadata exists.
     * @param int $metadataId
     * @param int|null $reviewObjectTypeId
     * @return bool
     */
    public function reviewObjectMetadataExists($metadataId, $reviewObjectTypeId = null) {
        $params = [(int) $metadataId];
        if ($reviewObjectTypeId !== null) {
            $params[] = (int) $reviewObjectTypeId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM review_object_metadata WHERE metadata_id = ?' . ($reviewObjectTypeId !== null ? ' AND review_object_type_id = ?' : ''),
            $params
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Sequentially renumber review object metadata in their sequence order.
     * @param int $reviewObjectTypeId
     * @return void
     */
    public function resequence($reviewObjectTypeId) {
        $result = $this->retrieve(
            'SELECT metadata_id FROM review_object_metadata WHERE review_object_type_id = ? ORDER BY seq', 
            [(int) $reviewObjectTypeId]
        );

        if ($result) {
            for ($i = 1; !$result->EOF; $i++) {
                $row = $result->GetRowAssoc(false);
                $metadataId = (int) $row['metadata_id'];
                $this->update(
                    'UPDATE review_object_metadata SET seq = ? WHERE metadata_id = ?',
                    [$i, $metadataId]
                );
                $result->MoveNext();
            }
            $result->Close();
        }
    }

    /**
     * Get the ID of the last inserted review object metadata.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return (int) parent::getInsertId('review_object_metadata', 'metadata_id', $callHooks);
    }

}
?>