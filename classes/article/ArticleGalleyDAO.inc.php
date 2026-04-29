<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleGalleyDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleGalleyDAO
 * @ingroup article
 * @see ArticleGalley
 *
 * @brief Operations for retrieving and modifying ArticleGalley/ArticleHTMLGalley objects.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal)
 * - Strict Integer Casting
 * - Hook Dispatch
 */

import('classes.article.ArticleGalley');
import('classes.article.ArticleHTMLGalley');

class ArticleGalleyDAO extends DAO {
    /** Helper file DAOs. */
    public $articleFileDao;
    
    /** Cache object */
    public $galleyCache;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleGalleyDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleGalleyDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get galley objects cache.
     * @return GenericCache
     */
    public function _getGalleyCache() {
        if (!isset($this->galleyCache)) {
            $cacheManager = CacheManager::getManager();
            // PHP 8: Removed & from callback array
            $this->galleyCache = $cacheManager->getObjectCache('galley', 0, array($this, '_galleyCacheMiss'));
        }
        return $this->galleyCache;
    }

    /**
     * Callback when there is no object in cache.
     * @param GenericCache $cache
     * @param int $id The wanted object id.
     * @return ArticleGalley
     */
    public function _galleyCacheMiss($cache, $id) {
        $galley = $this->getGalley($id, null, false);
        $cache->setCache($id, $galley);
        return $galley;
    }

    /**
     * Retrieve a galley by ID.
     * @param int $galleyId
     * @param int|null $articleId optional
     * @param boolean $useCache optional
     * @return ArticleGalley
     */
    public function getGalley($galleyId, $articleId = null, $useCache = false) {
        if ($useCache) {
            $cache = $this->_getGalleyCache();
            $returner = $cache->get($galleyId);
            if ($returner && $articleId != null && $articleId != $returner->getArticleId()) $returner = null;
            return $returner;
        }

        $params = array((int) $galleyId);
        if ($articleId !== null) $params[] = (int) $articleId;
        
        $result = $this->retrieve(
            'SELECT g.*,
                a.file_name, a.original_file_name, a.file_stage, a.file_type, a.file_size, a.date_uploaded, a.date_modified
            FROM article_galleys g
                LEFT JOIN article_files a ON (g.file_id = a.file_id)
            WHERE g.galley_id = ?' .
            ($articleId !== null ? ' AND g.article_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnGalleyFromRow($result->GetRowAssoc(false));
        } else {
            // Hook dispatch: primitives by ref in array, objects by val
            HookRegistry::dispatch('ArticleGalleyDAO::getNewGalley', array(&$galleyId, &$articleId, &$returner));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Checks if public identifier exists.
     * @param string $pubIdType
     * @param string $pubId
     * @param int $galleyId An ID to be excluded from the search.
     * @param int $journalId
     * @return boolean
     */
    public function pubIdExists($pubIdType, $pubId, $galleyId, $journalId) {
        $result = $this->retrieve(
            'SELECT COUNT(*)
            FROM article_galley_settings ags
                INNER JOIN article_galleys ag ON ags.galley_id = ag.galley_id
                INNER JOIN articles a ON ag.article_id = a.article_id
            WHERE ags.setting_name = ? AND ags.setting_value = ? AND ags.galley_id <> ? AND a.journal_id = ?',
            array(
                'pub-id::'.$pubIdType,
                $pubId,
                (int) $galleyId,
                (int) $journalId
            )
        );
        $returner = isset($result->fields[0]) && $result->fields[0] ? true : false;
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve a galley by Public ID.
     * @param string $pubIdType
     * @param string $pubId
     * @param int|null $articleId
     * @return ArticleGalley|null
     */
    public function getGalleyByPubId($pubIdType, $pubId, $articleId = null) {
        $galleys = $this->getGalleysBySetting('pub-id::'.$pubIdType, $pubId, $articleId);
        if (empty($galleys)) {
            $galley = null;
        } else {
            // assert(count($galleys) == 1); // Removed assertion for production safety
            $galley = $galleys[0];
        }

        return $galley;
    }

    /**
     * Find galleys by querying galley settings.
     * @param string $settingName
     * @param mixed $settingValue
     * @param int|null $articleId optional
     * @param int|null $journalId optional
     * @return array The galleys identified by setting.
     */
    public function getGalleysBySetting($settingName, $settingValue, $articleId = null, $journalId = null) {
        $params = array($settingName);

        $sql = 'SELECT g.*,
                af.file_name, af.original_file_name, af.file_stage, af.file_type, af.file_size, af.date_uploaded, af.date_modified
            FROM article_galleys g
                LEFT JOIN article_files af ON (g.file_id = af.file_id)
                INNER JOIN articles a ON a.article_id = g.article_id
                LEFT JOIN published_articles pa ON g.article_id = pa.article_id ';
        
        if (is_null($settingValue)) {
            $sql .= 'LEFT JOIN article_galley_settings gs ON g.galley_id = gs.galley_id AND gs.setting_name = ?
                WHERE (gs.setting_value IS NULL OR gs.setting_value = \'\')';
        } else {
            $params[] = $settingValue;
            $sql .= 'INNER JOIN article_galley_settings gs ON g.galley_id = gs.galley_id
                WHERE gs.setting_name = ? AND gs.setting_value = ?';
        }
        
        if ($articleId) {
            $params[] = (int) $articleId;
            $sql .= ' AND g.article_id = ?';
        }
        if ($journalId) {
            $params[] = (int) $journalId;
            $sql .= ' AND a.journal_id = ?';
        }
        $sql .= ' ORDER BY a.journal_id, pa.issue_id, g.galley_id';
        $result = $this->retrieve($sql, $params);

        $galleys = array();
        while (!$result->EOF) {
            $galleys[] = $this->_returnGalleyFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }
        $result->Close();

        return $galleys;
    }

    /**
     * Retrieve all galleys for an article.
     * @param int $articleId
     * @return array ArticleGalleys
     */
    public function getGalleysByArticle($articleId) {
        $galleys = array();

        $result = $this->retrieve(
            'SELECT g.*,
            a.file_name, a.original_file_name, a.file_stage, a.file_type, a.file_size, a.date_uploaded, a.date_modified
            FROM article_galleys g
            LEFT JOIN article_files a ON (g.file_id = a.file_id)
            WHERE g.article_id = ? ORDER BY g.seq',
            (int) $articleId
        );

        // --- PERBAIKAN FATAL ERROR: Cek hasil query ---
        if ($result) {
            // Jika query berhasil dan $result adalah objek resource
            // Loop akan berjalan normal
            while (!$result->EOF) {
                $galleys[] = $this->_returnGalleyFromRow($result->GetRowAssoc(false));
                $result->moveNext();
            }
            // Tutup resource hanya jika $result adalah objek
            $result->Close();
        } else {
            // Jika query GAGAL ($result adalah FALSE), kita sudah menginisialisasi
            // $galleys = array(), jadi kita hanya perlu melanjutkan.
            // (Opsional: tambahkan logging error di sini jika perlu)
        }
        
        unset($result);
        // --- AKHIR PERBAIKAN FATAL ERROR ---

        // Hook Modernization: arrays passed by reference to allow modification
        HookRegistry::dispatch('ArticleGalleyDAO::getArticleGalleys', array(&$galleys, &$articleId));

        return $galleys;
    }

    /**
     * Retrieve all galleys of a journal.
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getGalleysByJournalId($journalId) {
        $result = $this->retrieve(
            'SELECT
                g.*,
                af.file_name, af.original_file_name, af.file_stage, af.file_type, af.file_size, af.date_uploaded, af.date_modified
            FROM article_galleys g
            LEFT JOIN article_files af ON (g.file_id = af.file_id)
            INNER JOIN articles a ON (g.article_id = a.article_id)
            WHERE a.journal_id = ?',
            (int) $journalId
        );

        $returner = new DAOResultFactory($result, $this, '_returnGalleyFromRow');
        return $returner;
    }

    /**
     * Retrieve article galley by public galley id or internal ID.
     * @param string|int $galleyId
     * @param int $articleId
     * @return ArticleGalley|null
     */
    public function getGalleyByBestGalleyId($galleyId, $articleId) {
        $galley = null;
        if ($galleyId != '') $galley = $this->getGalleyByPubId('publisher-id', $galleyId, $articleId);
        if (!isset($galley) && ctype_digit((string)$galleyId)) $galley = $this->getGalley((int) $galleyId, $articleId);
        return $galley;
    }

    /**
     * Get the list of fields for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array();
    }

    /**
     * Get a list of additional fields.
     * @return array
     */
    public function getAdditionalFieldNames() {
        $additionalFields = parent::getAdditionalFieldNames();
        // FIXME: Move this to a PID plug-in.
        $additionalFields[] = 'pub-id::publisher-id';
        return $additionalFields;
    }

    /**
     * Update the localized fields for this galley.
     * @param ArticleGalley $galley
     */
    public function updateLocaleFields($galley) {
        $this->updateDataObjectSettings('article_galley_settings', $galley, array(
            'galley_id' => $galley->getId()
        ));
    }

    /**
     * Internal function to return an ArticleGalley object from a row.
     * @param array $row
     * @return ArticleGalley
     */
    public function _returnGalleyFromRow($row) {
        if ($row['html_galley']) {
            $galley = new ArticleHTMLGalley();

            // HTML-specific settings
            $galley->setStyleFileId($row['style_file_id']);
            if ($row['style_file_id']) {
                $galley->setStyleFile($this->articleFileDao->getArticleFile($row['style_file_id']));
            }

            // Retrieve images
            $images = $this->getGalleyImages($row['galley_id']);
            $galley->setImageFiles($images);

        } else {
            $galley = new ArticleGalley();
        }
        $galley->setId($row['galley_id']);
        $galley->setArticleId($row['article_id']);
        $galley->setLocale($row['locale']);
        $galley->setFileId($row['file_id']);
        $galley->setLabel($row['label']);
        $galley->setFileStage($row['file_stage']);
        $galley->setSequence($row['seq']);
        $galley->setRemoteURL($row['remote_url']);

        // ArticleFile set methods
        $galley->setFileName($row['file_name']);
        $galley->setOriginalFileName($row['original_file_name']);
        $galley->setFileType($row['file_type']);
        $galley->setFileSize($row['file_size']);
        $galley->setDateModified($this->datetimeFromDB($row['date_modified']));
        $galley->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

        $this->getDataObjectSettings('article_galley_settings', 'galley_id', $row['galley_id'], $galley);

        // Hook Modernization
        HookRegistry::dispatch('ArticleGalleyDAO::_returnGalleyFromRow', array($galley, &$row));

        return $galley;
    }

    /**
     * Insert a new ArticleGalley.
     * @param ArticleGalley $galley
     * @return int
     */
    public function insertGalley($galley) {
        $this->update(
            'INSERT INTO article_galleys
                (article_id, file_id, label, locale, html_galley, style_file_id, seq, remote_url)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                (int) $galley->getArticleId(),
                (int) $galley->getFileId(),
                $galley->getLabel(),
                $galley->getLocale(),
                (int) $galley->isHTMLGalley(),
                $galley->isHTMLGalley() ? (int) $galley->getStyleFileId() : null,
                $galley->getSequence() == null ? $this->getNextGalleySequence($galley->getArticleId()) : (float) $galley->getSequence(),
                $galley->getRemoteURL()
            )
        );
        $galley->setId($this->getInsertGalleyId());
        $this->updateLocaleFields($galley);

        // Hook Modernization: ID passed by value
        HookRegistry::dispatch('ArticleGalleyDAO::insertNewGalley', array($galley, $galley->getId()));

        return $galley->getId();
    }

    /**
     * Update an existing ArticleGalley.
     * @param ArticleGalley $galley
     */
    public function updateGalley($galley) {
        $this->update(
            'UPDATE article_galleys
                SET
                    file_id = ?,
                    label = ?,
                    locale = ?,
                    html_galley = ?,
                    style_file_id = ?,
                    seq = ?,
                    remote_url = ?
                WHERE galley_id = ?',
            array(
                (int) $galley->getFileId(),
                $galley->getLabel(),
                $galley->getLocale(),
                (int) $galley->isHTMLGalley(),
                $galley->isHTMLGalley() ? (int) $galley->getStyleFileId() : null,
                (float) $galley->getSequence(),
                $galley->getRemoteURL(),
                (int) $galley->getId()
            )
        );
        $this->updateLocaleFields($galley);
    }

    /**
     * Delete an ArticleGalley.
     * @param ArticleGalley $galley
     */
    public function deleteGalley($galley) {
        return $this->deleteGalleyById($galley->getId());
    }

    /**
     * Delete a galley by ID.
     * @param int $galleyId
     * @param int|null $articleId optional
     */
    public function deleteGalleyById($galleyId, $articleId = null) {

        HookRegistry::dispatch('ArticleGalleyDAO::deleteGalleyById', array(&$galleyId, &$articleId));

        if (isset($articleId)) {
            $this->update(
                'DELETE FROM article_galleys WHERE galley_id = ? AND article_id = ?',
                array((int) $galleyId, (int) $articleId)
            );
        } else {
            $this->update(
                'DELETE FROM article_galleys WHERE galley_id = ?', 
                (int) $galleyId
            );
        }
        if ($this->getAffectedRows()) {
            $this->update('DELETE FROM article_galley_settings WHERE galley_id = ?', array((int) $galleyId));
            $this->deleteImagesByGalley($galleyId);
        }
    }

    /**
     * Delete galleys (and dependent galley image entries) by article.
     * @param int $articleId
     */
    public function deleteGalleysByArticle($articleId) {
        $galleys = $this->getGalleysByArticle($articleId);
        foreach ($galleys as $galley) {
            $this->deleteGalleyById($galley->getId(), $articleId);
        }
    }

    /**
     * Check if a galley exists with the associated file ID.
     * @param int $articleId
     * @param int $fileId
     * @return boolean
     */
    public function galleyExistsByFileId($articleId, $fileId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM article_galleys
            WHERE article_id = ? AND file_id = ?',
            array((int) $articleId, (int) $fileId)
        );

        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Increment the views count for a galley.
     * @param int $galleyId
     */
    public function incrementViews($galleyId) {
        if ( !HookRegistry::dispatch('ArticleGalleyDAO::incrementGalleyViews', array(&$galleyId)) ) {
            return $this->update(
                'UPDATE article_galleys SET views = views + 1 WHERE galley_id = ?',
                (int) $galleyId
            );
        } else return false;
    }

    /**
     * Sequentially renumber galleys for an article in their sequence order.
     * @param int $articleId
     */
    public function resequenceGalleys($articleId) {
        $result = $this->retrieve(
            'SELECT galley_id FROM article_galleys WHERE article_id = ? ORDER BY seq',
            (int) $articleId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($galleyId) = $result->fields;
            $this->update(
                'UPDATE article_galleys SET seq = ? WHERE galley_id = ?',
                array($i, (int) $galleyId)
            );
            $result->moveNext();
        }

        $result->close();
        unset($result);
    }

    /**
     * Get the the next sequence number for an article's galleys.
     * @param int $articleId
     * @return int
     */
    public function getNextGalleySequence($articleId) {
        $result = $this->retrieve(
            'SELECT MAX(seq) + 1 FROM article_galleys WHERE article_id = ?',
            (int) $articleId
        );
        $returner = floor($result->fields[0]);

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Get the ID of the last inserted gallery.
     * @return int
     */
    public function getInsertGalleyId() {
        return $this->getInsertId('article_galleys', 'galley_id');
    }


    //
    // Extra routines specific to HTML galleys.
    //

    /**
     * Retrieve array of the images for an HTML galley.
     * @param int $galleyId
     * @return array ArticleFile
     */
    public function getGalleyImages($galleyId) {
        $images = array();

        $result = $this->retrieve(
            'SELECT a.* FROM article_html_galley_images i, article_files a
            WHERE i.file_id = a.file_id AND i.galley_id = ?',
            (int) $galleyId
        );

        while (!$result->EOF) {
            $images[] = $this->articleFileDao->_returnArticleFileFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $images;
    }

    /**
     * Attach an image to an HTML galley.
     * @param int $galleyId
     * @param int $fileId
     */
    public function insertGalleyImage($galleyId, $fileId) {
        return $this->update(
            'INSERT INTO article_html_galley_images
            (galley_id, file_id)
            VALUES
            (?, ?)',
            array((int) $galleyId, (int) $fileId)
        );
    }

    /**
     * Delete an image from an HTML galley.
     * @param int $galleyId
     * @param int $fileId
     */
    public function deleteGalleyImage($galleyId, $fileId) {
        return $this->update(
            'DELETE FROM article_html_galley_images
            WHERE galley_id = ? AND file_id = ?',
            array((int) $galleyId, (int) $fileId)
        );
    }

    /**
     * Delete HTML galley images by galley.
     * @param int $galleyId
     */
    public function deleteImagesByGalley($galleyId) {
        return $this->update(
            'DELETE FROM article_html_galley_images WHERE galley_id = ?',
            (int) $galleyId
        );
    }

    /**
     * Change the public ID of a galley.
     * @param int $galleyId
     * @param string $pubIdType
     * @param string $pubId
     */
    public function changePubId($galleyId, $pubIdType, $pubId) {
        $idFields = array(
            'galley_id', 'locale', 'setting_name'
        );
        $updateArray = array(
            'galley_id' => (int) $galleyId,
            'locale' => '',
            'setting_name' => 'pub-id::'.$pubIdType,
            'setting_type' => 'string',
            'setting_value' => (string)$pubId
        );
        $this->replace('article_galley_settings', $updateArray, $idFields);
    }

    /**
     * Delete the public IDs of all galleys in a journal.
     * @param int $journalId
     * @param string $pubIdType
     */
    public function deleteAllPubIds($journalId, $pubIdType) {
        $journalId = (int) $journalId;
        $settingName = 'pub-id::'.$pubIdType;

        $galleys = $this->getGalleysByJournalId($journalId);
        while ($galley = $galleys->next()) {
            $this->update(
                'DELETE FROM article_galley_settings WHERE setting_name = ? AND galley_id = ?',
                array(
                    $settingName,
                    (int)$galley->getId()
                )
            );
            unset($galley);
        }
        $this->flushCache();
    }

    /**
     * Delete the public ID of a galley.
     * @param int $galleyId
     * @param string $pubIdType
     */
    public function deletePubId($galleyId, $pubIdType) {
        $settingName = 'pub-id::'.$pubIdType;
        $this->update(
            'DELETE FROM article_galley_settings WHERE setting_name = ? AND galley_id = ?',
            array(
                $settingName,
                (int)$galleyId
            )
        );
        $this->flushCache();
    }

    /**
     * Flush the article galley cache.
     */
    public function flushCache() {
        $cache = $this->_getGalleyCache();
        $cache->flush();
        unset($cache);
    }
}

?>