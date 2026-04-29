<?php
declare(strict_types=1);

/**
 * @file classes/help/PluginHelpMappingFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginHelpMappingFile
 * @ingroup help
 *
 * @brief Abstracts the plugin's help mapping XML files.
 */

import('lib.pkp.classes.help.HelpMappingFile');

class PluginHelpMappingFile extends HelpMappingFile {
    /** @var object */
    public $plugin; // Mengganti var menjadi public

    /**
     * Constructor
     * @param $plugin object The plugin object this mapping file belongs to.
     */
    public function __construct($plugin) { // Menghapus reference (&) pada parameter
        parent::__construct($plugin->getHelpMappingFilename());
        $this->plugin = $plugin; // Menghapus reference (&)
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PluginHelpMappingFile($plugin) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PluginHelpMappingFile(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($plugin);
    }

    /**
     * Return the filename for a plugin help TOC filename.
     * @param $tocId string
     * @return string
     */
    public function getTocFilename($tocId) {
        $help = Help::getHelp(); // Menghapus reference (&)
        return $this->plugin->getPluginPath() . DIRECTORY_SEPARATOR . 'help' . DIRECTORY_SEPARATOR . $help->getLocale() . DIRECTORY_SEPARATOR . $tocId . '.xml';
    }

    /**
     * Return the filename for a plugin help topic filename.
     * @param $topicId string
     * @return string
     */
    public function getTopicFilename($topicId) {
        $help = Help::getHelp(); // Menghapus reference (&)
        return $this->plugin->getPluginPath() . DIRECTORY_SEPARATOR . 'help' . DIRECTORY_SEPARATOR . $help->getLocale() . DIRECTORY_SEPARATOR . $topicId . '.xml';
    }

    /**
     * Given a help file filename, get the topic ID.
     * @param $filename string
     * @return string
     */
    public function getTopicIdForFilename($filename) {
        // Logika penghapusan path disederhanakan dan dibersihkan dari referensi
        $parts = explode('/', str_replace('\\', '/', $filename));
        array_shift($parts); // Knock off "plugins"
        array_shift($parts); // Knock off category
        array_shift($parts); // Knock off plugin name
        array_shift($parts); // Knock off "help"
        array_shift($parts); // Knock off locale
        return substr(join('/', $parts), 0, -4); // Knock off .xml
    }

    /**
     * Get the directory containing help files to search.
     * @param $locale string
     * @return string
     */
    public function getSearchPath($locale = null) {
        if (empty($locale)) {
            $help = Help::getHelp(); // Menghapus reference (&)
            $locale = $help->getLocale();
        }
        return $this->plugin->getPluginPath() . DIRECTORY_SEPARATOR . 'help' . DIRECTORY_SEPARATOR . $locale;
    }
}

?>