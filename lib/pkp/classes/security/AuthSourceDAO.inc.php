<?php
declare(strict_types=1);

/**
 * @file classes/security/AuthSourceDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthSourceDAO
 * @ingroup security
 * @see AuthSource
 *
 * @brief Operations for retrieving and modifying AuthSource objects.
 */

import('lib.pkp.classes.security.AuthSource');

class AuthSourceDAO extends DAO {
    /** @var array List of loaded authentication plugins */
    public $plugins;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        // Removed & from reference
        $this->plugins = PluginRegistry::loadCategory(AUTH_PLUGIN_CATEGORY);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthSourceDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AuthSourceDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get plugin instance corresponding to the ID.
     * @param $authId int
     * @return AuthPlugin|null
     */
    public function getPlugin($authId) {
        $plugin = null;
        // Removed & reference
        $auth = $this->getSource($authId);
        if ($auth != null) {
            $plugin = $auth->getPluginClass();
            if ($plugin != null) {
                // Removed & reference
                $plugin = $plugin->getInstance($auth->getSettings(), $auth->getAuthId());
            }
        }
        return $plugin;
    }

    /**
     * Get plugin instance for the default authentication source.
     * @return AuthPlugin|null
     */
    public function getDefaultPlugin() {
        $plugin = null;
        // Removed & reference
        $auth = $this->getDefaultSource();
        if ($auth != null) {
            $plugin = $auth->getPluginClass();
            if ($plugin != null) {
                $plugin = $plugin->getInstance($auth->getSettings(), $auth->getAuthId());
            }
        }
        return $plugin;
    }

    /**
     * Retrieve a source.
     * @param $authId int
     * @return AuthSource|null
     */
    public function getSource($authId) {
        $result = $this->retrieve(
            'SELECT * FROM auth_sources WHERE auth_id = ?',
            array((int) $authId)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAuthSourceFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve the default authentication source.
     * @return AuthSource|null
     */
    public function getDefaultSource() {
        $result = $this->retrieve(
            'SELECT * FROM auth_sources WHERE auth_default = 1'
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAuthSourceFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Instantiate and return a new data object.
     * @return AuthSource
     */
    public function newDataObject() {
        return new AuthSource();
    }

    /**
     * Internal function to return an AuthSource object from a row.
     * @param $row array
     * @return AuthSource
     */
    public function _returnAuthSourceFromRow($row) {
        $auth = $this->newDataObject();
        $auth->setAuthId($row['auth_id']);
        $auth->setTitle($row['title']);
        $auth->setPlugin($row['plugin']);
        $auth->setPluginClass(@$this->plugins[$row['plugin']]);
        $auth->setDefault($row['auth_default']);
        $auth->setSettings(unserialize($row['settings']));
        return $auth;
    }

    /**
     * Insert a new source.
     * @param $auth AuthSource
     * @return int|bool Auth ID on success, false on failure
     */
    public function insertSource($auth) {
        if (!isset($this->plugins[$auth->getPlugin()])) return false;
        if (!$auth->getTitle()) $auth->setTitle($this->plugins[$auth->getPlugin()]->getDisplayName());
        
        $this->update(
            'INSERT INTO auth_sources
                (title, plugin, settings)
                VALUES
                (?, ?, ?)',
            array(
                $auth->getTitle(),
                $auth->getPlugin(),
                serialize($auth->getSettings() ? $auth->getSettings() : array())
            )
        );

        $auth->setAuthId($this->getInsertId('auth_sources', 'auth_id'));
        return $auth->getAuthId();
    }

    /**
     * Update a source.
     * @param $auth AuthSource
     */
    public function updateObject($auth) {
        return $this->update(
            'UPDATE auth_sources SET
                title = ?,
                settings = ?
            WHERE    auth_id = ?',
            array(
                $auth->getTitle(),
                serialize($auth->getSettings() ? $auth->getSettings() : array()),
                (int) $auth->getAuthId()
            )
        );
    }

    public function updateSource($auth) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($auth);
    }

    /**
     * Delete a source.
     * @param $authId int|AuthSource
     */
    public function deleteObject($authId) {
        // Handle if object passed instead of ID (polymorphism support)
        if (is_object($authId)) {
             $authId = $authId->getAuthId();
        }
        
        return $this->update(
            'DELETE FROM auth_sources WHERE auth_id = ?', (int)$authId
        );
    }

    public function deleteSource($auth) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->deleteObject($auth);
    }

    /**
     * Set the default authentication source.
     * @param $authId int
     */
    public function setDefault($authId) {
        $this->update(
            'UPDATE auth_sources SET auth_default = 0'
        );
        $this->update(
            'UPDATE auth_sources SET auth_default = 1 WHERE auth_id = ?',
            array((int) $authId)
        );
    }

    /**
     * Retrieve a list of all auth sources for the site.
     * @return DAOResultFactory AuthSource
     */
    public function getSources($rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM auth_sources ORDER BY auth_id',
            array(), // Corrected: retrieveRange expects params array as second arg, not boolean
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAuthSourceFromRow');
        return $returner;
    }
}

?>