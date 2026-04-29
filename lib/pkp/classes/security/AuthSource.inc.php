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
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of this source.
     * @return int
     */
    public function getAuthId() {
        return $this->getData('authId');
    }

    /**
     * Set ID of this source.
     * @param $authId int
     */
    public function setAuthId($authId) {
        return $this->setData('authId', $authId);
    }

    /**
     * Get user-specified title of this source.
     * @return string
     */
    public function getTitle() {
        return $this->getData('title');
    }

    /**
     * Set user-specified title of this source.
     * @param $title string
     */
    public function setTitle($title) {
        return $this->setData('title', $title);
    }

    /**
     * Get the authentication plugin associated with this source.
     * @return string
     */
    public function getPlugin() {
        return $this->getData('plugin');
    }

    /**
     * Set the authentication plugin associated with this source.
     * @param $plugin string
     */
    public function setPlugin($plugin) {
        return $this->setData('plugin', $plugin);
    }

    /**
     * Get flag indicating this is the default authentication source.
     * @return boolean
     */
    public function getDefault() {
        return $this->getData('authDefault');
    }

    /**
     * Set flag indicating this is the default authentication source.
     * @param $authDefault boolean
     */
    public function setDefault($authDefault) {
        return $this->setData('authDefault', $authDefault);
    }

    /**
     * Get array of plugin-specific settings for this source.
     * @return array
     */
    public function getSettings() {
        return $this->getData('settings');
    }

    /**
     * Set array of plugin-specific settings for this source.
     * @param $settings array
     */
    public function setSettings($settings) {
        return $this->setData('settings', $settings);
    }

    /**
     * Get the authentication plugin object associated with this source.
     * @return AuthPlugin
     */
    public function getPluginClass() {
        // Removed & reference
        return $this->getData('authPlugin');
    }

    /**
     * Set authentication plugin object associated with this source.
     * @param $authPlugin AuthPlugin
     */
    public function setPluginClass($authPlugin) {
        return $this->setData('authPlugin', $authPlugin);
    }
}

?>