<?php
declare(strict_types=1);

/**
 * @file classes/security/AuthSource.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthSource
 * @ingroup security
 * @see AuthSourceDAO
 *
 * @brief Describes an authentication source.
 */

import('classes.plugins.AuthPlugin');

class AuthSource extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthSource() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AuthSource(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $this->__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of this source.
     * @return int
     */
    public function getAuthId() {
        $data = $this->getData('authId');
        return $data !== null ? (int) $data : 0;
    }

    /**
     * Set ID of this source.
     * @param mixed $authId
     */
    public function setAuthId($authId) {
        $this->setData('authId', $authId !== null ? (int) $authId : 0);
    }

    /**
     * Get user-specified title of this source.
     * @return string
     */
    public function getTitle() {
        $data = $this->getData('title');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set user-specified title of this source.
     * @param mixed $title
     */
    public function setTitle($title) {
        $this->setData('title', $title !== null ? (string) $title : '');
    }

    /**
     * Get the authentication plugin associated with this source.
     * @return string
     */
    public function getPlugin() {
        $data = $this->getData('plugin');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set the authentication plugin associated with this source.
     * @param mixed $plugin
     */
    public function setPlugin($plugin) {
        $this->setData('plugin', $plugin !== null ? (string) $plugin : '');
    }

    /**
     * Get flag indicating this is the default authentication source.
     * @return boolean
     */
    public function getDefault() {
        $data = $this->getData('authDefault');
        return (bool) $data;
    }

    /**
     * Set flag indicating this is the default authentication source.
     * @param mixed $authDefault
     */
    public function setDefault($authDefault) {
        $this->setData('authDefault', (bool) $authDefault);
    }

    /**
     * Get array of plugin-specific settings for this source.
     * @return array
     */
    public function getSettings() {
        $data = $this->getData('settings');
        return is_array($data) ? $data : [];
    }

    /**
     * Set array of plugin-specific settings for this source.
     * @param mixed $settings
     */
    public function setSettings($settings) {
        $this->setData('settings', is_array($settings) ? $settings : []);
    }

    /**
     * Get the authentication plugin object associated with this source.
     * @return AuthPlugin|null
     */
    public function getPluginClass() {
        return $this->getData('authPlugin');
    }

    /**
     * Set authentication plugin object associated with this source.
     * @param mixed $authPlugin
     */
    public function setPluginClass($authPlugin) {
        $this->setData('authPlugin', $authPlugin);
    }

}
?>