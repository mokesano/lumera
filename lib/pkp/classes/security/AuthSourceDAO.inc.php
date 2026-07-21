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
    public $plugins = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
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
        $this->__construct();
    }

    /**
     * Get plugin instance corresponding to the ID.
     * @param mixed $authId
     * @return AuthPlugin|null
     */
    public function getPlugin($authId) {
        $plugin = null;
        $auth = $this->getSource((int) $authId);
        
        if ($auth !== null) {
            $plugin = $auth->getPluginClass();
            if ($plugin !== null) {
                $plugin = $plugin->getInstance($auth->getSettings(), (int) $auth->getAuthId());
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
        $auth = $this->getDefaultSource();
        
        if ($auth !== null) {
            $plugin = $auth->getPluginClass();
            if ($plugin !== null) {
                $plugin = $plugin->getInstance($auth->getSettings(), (int) $auth->getAuthId());
            }
        }
        return $plugin;
    }

    /**
     * Retrieve a source.
     * @param mixed $authId
     * @return AuthSource|null
     */
    public function getSource($authId) {
        $result = $this->retrieve(
            'SELECT * FROM auth_sources WHERE auth_id = ?',
            [(int) $authId]
        );

        $returner = null;

        if ($result && !$result->EOF) {
            $returner = $this->_returnAuthSourceFromRow($result->GetRowAssoc(false));
        }

        if ($result) {
            $result->Close();
        }

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

        if ($result && !$result->EOF) {
            $returner = $this->_returnAuthSourceFromRow($result->GetRowAssoc(false));
        }

        if ($result) {
            $result->Close();
        }

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
     * @param mixed $row
     * @return AuthSource
     */
    public function _returnAuthSourceFromRow($row) {
        $auth = $this->newDataObject();

        $auth->setAuthId((int) $row['auth_id']);
        $auth->setTitle((string) ($row['title'] ?? ''));
        
        $pluginName = (string) ($row['plugin'] ?? '');
        $auth->setPlugin($pluginName);

        $auth->setPluginClass($this->plugins[$pluginName] ?? null);
        
        $auth->setDefault((bool) ($row['auth_default'] ?? false));

        $settingsRaw = $row['settings'] ?? '';
        $settings = is_string($settingsRaw) && !empty($settingsRaw) ? @unserialize($settingsRaw) : [];
        $auth->setSettings(is_array($settings) ? $settings : []);
        
        return $auth;
    }

    /**
     * Insert a new source.
     * @param mixed $auth AuthSource
     * @return int|bool Auth ID on success, false on failure
     */
    public function insertSource($auth) {
        $pluginName = (string) $auth->getPlugin();
        
        if (!isset($this->plugins[$pluginName])) {
            return false;
        }
        
        if (!$auth->getTitle()) {
            $auth->setTitle((string) $this->plugins[$pluginName]->getDisplayName());
        }
        
        $settings = $auth->getSettings();
        $settingsArray = is_array($settings) ? $settings : [];

        $this->update(
            'INSERT INTO auth_sources (title, plugin, settings) VALUES (?, ?, ?)',
            [
                (string) $auth->getTitle(),
                $pluginName,
                serialize($settingsArray)
            ]
        );

        $auth->setAuthId((int) $this->getInsertId('auth_sources', 'auth_id'));
        return $auth->getAuthId();
    }

    /**
     * Update a source.
     * @param mixed $auth AuthSource
     */
    public function updateObject($auth) {
        $settings = $auth->getSettings();
        $settingsArray = is_array($settings) ? $settings : [];

        return $this->update(
            'UPDATE auth_sources SET title = ?, settings = ? WHERE auth_id = ?',
            [
                (string) $auth->getTitle(),
                serialize($settingsArray),
                (int) $auth->getAuthId()
            ]
        );
    }

    /**
     * DEPRECATED
     * @param mixed $auth
     */
    public function updateSource($auth) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->updateObject($auth);
    }

    /**
     * Delete a source.
     * @param mixed $authId int|AuthSource
     */
    public function deleteObject($authId) {
        if (is_object($authId)) {
            $authId = (int) $authId->getAuthId();
        }

        return $this->update(
            'DELETE FROM auth_sources WHERE auth_id = ?', 
            [(int) $authId]
        );
    }

    /**
     * DEPRECATED
     * @param mixed $auth
     */
    public function deleteSource($auth) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->deleteObject($auth);
    }

    /**
     * Set the default authentication source.
     * @param mixed $authId
     */
    public function setDefault($authId) {
        $this->update('UPDATE auth_sources SET auth_default = 0');
        $this->update(
            'UPDATE auth_sources SET auth_default = 1 WHERE auth_id = ?',
            [(int) $authId]
        );
    }

    /**
     * Retrieve a list of all auth sources for the site.
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getSources($rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM auth_sources ORDER BY auth_id',
            [], 
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAuthSourceFromRow');
    }

}
?>