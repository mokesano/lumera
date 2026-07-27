<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesDAO
 * @ingroup plugins_generic_staticPages
 *
 * @brief Operations for retrieving and modifying StaticPages objects.
 */

import('lib.pkp.classes.db.DAO');

class StaticPagesDAO extends DAO {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        $this->parentPluginName = (string) $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function StaticPagesDAO($parentPluginName) {
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
     * Retrieve a static page by ID.
     * @param int $staticPageId
     * @return StaticPage|null
     */
    public function getStaticPage($staticPageId) {
        $result = $this->retrieve(
            'SELECT * FROM static_pages WHERE static_page_id = ?',
            [(int) $staticPageId]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnStaticPageFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve all static pages for a journal.
     * @param int $journalId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getStaticPagesByJournalId($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM static_pages WHERE journal_id = ?',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnStaticPageFromRow');
    }

    /**
     * Retrieve a static page by path.
     * @param int $journalId
     * @param string $path
     * @return StaticPage|null
     */
    public function getStaticPageByPath($journalId, $path) {
        $result = $this->retrieve(
            'SELECT * FROM static_pages WHERE journal_id = ? AND path = ?',
            [(int) $journalId, (string) $path]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnStaticPageFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Insert a new static page.
     * @param StaticPage $staticPage
     * @return int
     */
    public function insertStaticPage($staticPage) {
        $this->update(
            'INSERT INTO static_pages (journal_id, path) VALUES (?, ?)',
            [
                (int) $staticPage->getJournalId(),
                (string) $staticPage->getPath()
            ]
        );

        $staticPage->setId((int) $this->getInsertStaticPageId());
        $this->updateLocaleFields($staticPage);

        return $staticPage->getId();
    }

    /**
     * Update an existing static page.
     * @param StaticPage $staticPage
     * @return bool
     */
    public function updateStaticPage($staticPage) {
        $returner = $this->update(
            'UPDATE static_pages
                SET
                    journal_id = ?,
                    path = ?
                WHERE static_page_id = ?',
            [
                (int) $staticPage->getJournalId(),
                (string) $staticPage->getPath(),
                (int) $staticPage->getId()
            ]
        );
        $this->updateLocaleFields($staticPage);
        return $returner;
    }

    /**
     * Delete a static page by ID.
     * @param int $staticPageId
     * @return bool
     */
    public function deleteStaticPageById($staticPageId) {
        $staticPageId = (int) $staticPageId;
        $this->update(
            'DELETE FROM static_pages WHERE static_page_id = ?',
            [$staticPageId]
        );
        return $this->update(
            'DELETE FROM static_page_settings WHERE static_page_id = ?',
            [$staticPageId]
        );
    }

    /**
     * Internal function to return a StaticPage object from a row.
     * @param array $row
     * @return StaticPage
     */
    public function _returnStaticPageFromRow($row) {
        $staticPagesPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $staticPagesPlugin->import('StaticPage');

        $staticPage = new StaticPage();
        $staticPage->setId((int) $row['static_page_id']);
        $staticPage->setPath((string) $row['path']);
        $staticPage->setJournalId((int) $row['journal_id']);

        $this->getDataObjectSettings('static_page_settings', 'static_page_id', (int) $row['static_page_id'], $staticPage);
        return $staticPage;
    }

    /**
     * Get the ID of the last inserted static page.
     * @return int
     */
    public function getInsertStaticPageId() {
        return $this->getInsertId('static_pages', 'static_page_id');
    }

    /**
     * Get field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['title', 'content'];
    }

    /**
     * Update the localized data for this object.
     * @param StaticPage $staticPage
     */
    public function updateLocaleFields($staticPage) {
        $this->updateDataObjectSettings('static_page_settings', $staticPage, [
            'static_page_id' => (int) $staticPage->getId()
        ]);
    }

    /**
     * Find duplicate path.
     * @param string $path
     * @param int $journalId
     * @param int|null $staticPageId
     * @return bool
     */
    public function duplicatePathExists($path, $journalId, $staticPageId = null) {
        $params = [
            (int) $journalId,
            (string) $path
        ];
        if ($staticPageId !== null) {
            $params[] = (int) $staticPageId;
        }

        $result = $this->retrieve(
            'SELECT *
                FROM static_pages
                WHERE journal_id = ?
                AND path = ?' .
                ($staticPageId !== null ? ' AND NOT (static_page_id = ?)' : ''),
            $params
        );

        $returner = ($result && !$result->EOF);
        if ($result) {
            $result->Close();
        }
        
        return $returner;
    }

}
?>