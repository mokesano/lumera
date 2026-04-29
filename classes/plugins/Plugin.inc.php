<?php
declare(strict_types=1);

/**
 * @file classes/plugins/Plugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Plugin
 * @ingroup plugins
 *
 * @brief Abstract class for plugins
 * MODERNIZED FOR PHP 8.x+
 */

import('lib.pkp.classes.plugins.PKPPlugin');

class Plugin extends PKPPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Backwards compatible convenience version of
     * the generic getContextSpecificSetting() method.
     * @see PKPPlugin::getContextSpecificSetting()
     * @param $journalId
     * @param $name
     */
    public function getSetting($journalId, $name) {
        if (defined('RUNNING_UPGRADE')) {
            // Bug #2504: Make sure plugin_settings table is not
            // used if it's not available.
            $versionDao = DAORegistry::getDAO('VersionDAO');
            $version = $versionDao->getCurrentVersion();
            if ($version->compare('2.1.0') < 0) return null;
        }
        return $this->getContextSpecificSetting([$journalId], $name);
    }

    /**
     * Backwards compatible convenience version of the generic method.
     * @see PKPPlugin::updateContextSpecificSetting()
     * @param $journalId int
     * @param $name string The name of the setting
     * @param $value mixed
     * @param $type string optional
     */
    public function updateSetting($journalId, $name, $value, $type = null) {
        $this->updateContextSpecificSetting([$journalId], $name, $value, $type);
    }

    /**
     * Get the filename of the settings data for this plugin to install
     * when a journal is created (i.e. journal-level plugin settings).
     * Subclasses using default settings should override this.
     * @return string|null
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        // The default implementation delegates to the old
        // method for backwards compatibility.
        return $this->getNewJournalPluginSettingsFile();
    }

    /**
     * For backwards compatibility only.
     * New plug-ins should override getContextSpecificPluginSettingsFile()
     * @see PKPPlugin::getContextSpecificPluginSettingsFile()
     * @return string|null
     */
    public function getNewJournalPluginSettingsFile(): ?string {
        return null;
    }
}
?>