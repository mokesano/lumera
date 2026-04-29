<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesDAO
 *
 * Operations for retrieving and modifying StaticPages objects.
 * * MODERNIZED FOR WIZDAM FORK
 */
 
 
import('lib.pkp.classes.db.DAO');

class StaticPagesDAO extends DAO {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        $this->parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StaticPagesDAO($parentPluginName) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::StaticPagesDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($parentPluginName);
    }

    /**
     * Retrieve a static page by ID.
     * @param $staticPageId int
     * @return StaticPage
     */
    public function getStaticPage($staticPageId) {
        $result = $this->retrieve(
            'SELECT * FROM static_pages WHERE static_page_id = ?', (int) $staticPageId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnStaticPageFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve all static pages for a journal.
     * @param $journalId int
     * @param $rangeInfo DBResultRange optional
     * @return DAOResultFactory<StaticPage>
     */
    public function getStaticPagesByJournalId($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM static_pages WHERE journal_id = ?', (int) $journalId, $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnStaticPageFromRow');
        return $returner;
    }

    /**
     * Retrieve a static page by path.
     * @param $journalId int
     * @param $path string
     * @return StaticPage
     */
    public function getStaticPageByPath($journalId, $path) {
        $result = $this->retrieve(
            'SELECT * FROM static_pages WHERE journal_id = ? AND path = ?', array((int) $journalId, $path)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnStaticPageFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Insert a new static page.
     * @param $staticPage StaticPage
     * @return int Static Page ID
     */
    public function insertStaticPage($staticPage) {
        $this->update(
            'INSERT INTO static_pages
                (journal_id, path)
                VALUES
                (?, ?)',
            array(
                (int) $staticPage->getJournalId(),
                $staticPage->getPath()
            )
        );

        $staticPage->setId($this->getInsertStaticPageId());
        $this->updateLocaleFields($staticPage);

        return $staticPage->getId();
    }

    /**
     * Update an existing static page.
     * @param $staticPage StaticPage
     * @return int Static Page ID
     */
    public function updateStaticPage($staticPage) {
        $returner = $this->update(
            'UPDATE static_pages
                SET
                    journal_id = ?,
                    path = ?
                WHERE static_page_id = ?',
                array(
                    (int) $staticPage->getJournalId(),
                    $staticPage->getPath(),
                    (int) $staticPage->getId()
                    )
            );
        $this->updateLocaleFields($staticPage);
        return $returner;
    }

    /**
     * Delete a static page by ID.
     * @param $staticPageId int
     * @return int
     */
    public function deleteStaticPageById($staticPageId) {
        $returner = $this->update(
            'DELETE FROM static_pages WHERE static_page_id = ?', (int) $staticPageId
        );
        return $this->update(
            'DELETE FROM static_page_settings WHERE static_page_id = ?', (int) $staticPageId
        );
    }

    /**
     * Internal function to return a StaticPage object from a row.
     * @param $row array
     * @return StaticPage
     */
    public function _returnStaticPageFromRow($row) {
        $staticPagesPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $staticPagesPlugin->import('StaticPage');

        $staticPage = new StaticPage();
        $staticPage->setId($row['static_page_id']);
        $staticPage->setPath($row['path']);
        $staticPage->setJournalId($row['journal_id']);

        $this->getDataObjectSettings('static_page_settings', 'static_page_id', $row['static_page_id'], $staticPage);
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
        return array('title', 'content');
    }

    /**
     * Update the localized data for this object
     * @param $author object
     */
    public function updateLocaleFields($staticPage) {
        $this->updateDataObjectSettings('static_page_settings', $staticPage, array(
            'static_page_id' => $staticPage->getId()
        ));
    }

    /**
     * Find duplicate path
     * @param $path String
     * @param journalId int
     * @param $staticPageId    int
     * @return boolean
     */
    public function duplicatePathExists ($path, $journalId, $staticPageId = null) {
        $params = array(
                    (int) $journalId,
                    $path
                    );
        if (isset($staticPageId)) $params[] = (int) $staticPageId;

        $result = $this->retrieve(
            'SELECT *
                FROM static_pages
                WHERE journal_id = ?
                AND path = ?' .
                (isset($staticPageId)?' AND NOT (static_page_id = ?)':''),
                $params
            );

        if($result->RecordCount() == 0) {
            // no duplicate exists
            $returner = false;
        } else {
            $returner = true;
        }
        return $returner;
    }
}

?>