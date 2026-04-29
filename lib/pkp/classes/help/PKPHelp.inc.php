<?php
declare(strict_types=1);

/**
 * @defgroup help
 */

/**
 * @file classes/help/PKPHelp.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPHelp
 * @ingroup help
 *
 * @brief Provides methods for translating help topic keys to their respected topic
 * help ids.
 */

class PKPHelp {
    /** @var array<HelpMappingFile> of HelpMappingFile objects */
    public $mappingFiles; // Mengganti var menjadi public

    /**
     * Get an instance of the Help object.
     * @return PKPHelp
     */
    public static function getHelp() { // Menjadikan static, menghapus reference (&)
        $instance = Registry::get('help');
        if ($instance == null) {
            unset($instance);
            $application = PKPApplication::getApplication();
            $instance = $application->instantiateHelp();
            Registry::set('help', $instance);
        }
        return $instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->mappingFiles = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPHelp() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPHelp(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the registered mapping files.
     * @return array
     */
    public function getMappingFiles() {
        return $this->mappingFiles;
    }

    /**
     * Add a help mapping file object.
     * @param $mappingFile HelpMappingFile
     */
    public function addMappingFile($mappingFile) {
        $this->mappingFiles[] = $mappingFile;
    }

    /**
     * Get the locale to display help files in.
     * If help isn't available for the current locale,
     * defaults to en_US.
     * @return string
     */
    public static function getLocale() {
        $locale = AppLocale::getLocale();
        if (!file_exists("help/$locale/.")) {
            return 'en_US';
        }
        return $locale;
    }

    /**
     * Translate a help topic key to its numerical id.
     * @param $key string
     * @return string
     */
    public function translate($key) {
        $key = trim($key);
        if (empty($key)) {
            return '';
        }

        $mappingFiles = $this->getMappingFiles();
        for ($i=0; $i < count($mappingFiles); $i++) {
            $mappingFile = $mappingFiles[$i];
            $value = $mappingFile->map($key);
            if ($value !== null) return $value;
        }

        if (!isset($value)) {
            return '##' . $key . '##';
        }
    }

    /**
     * Get the TOC cache object.
     * @return FileCache
     */
    protected function _getTocCache() {
        $cache = Registry::get('pkpHelpTocCache', true, null);

        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                'help', 'toc',
                array($this, '_tocCacheMiss')
            );
            Registry::set('pkpHelpTocCache', $cache);

            // Check to see if the cache info is outdated.
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && file_exists('help/'. $this->getLocale() . '/.') && $cacheTime < $this->dirmtime('help/'. $this->getLocale() . '/.', true)) {
                // The cached data is out of date.
                $cache->flush();
            }
        }
        return $cache;
    }

    /**
     * Cache miss callback for the mapping cache.
     * @param $cache Cache
     * @param $id mixed
     * @return mixed
     */
    public function _mappingCacheMiss($cache, $id) {
        // Keep a secondary cache of the mappings so that a few
        // cache misses won't destroy the server
        $mappings = Registry::get('pkpHelpMappings', true, null);

        $result = null;
        // KOREKSI DITERAPKAN DI SINI: call diubah menjadi dispatch
        if (HookRegistry::dispatch('Help::_mappingCacheMiss', array($cache, $id, &$mappings, &$result))) return $result;

        if ($mappings === null) {
            $mappings = $this->loadHelpMappings();
            $cache->setEntireCache($mappings);
            Registry::set('pkpHelpMappings', $mappings);
        }
        return isset($mappings[$id])?$mappings[$id]:null;
    }

    /**
     * Cache miss callback for the TOC cache.
     * @param $cache Cache
     * @param $id mixed
     * @return null
     */
    public function _tocCacheMiss($cache, $id) {
        // Keep a secondary cache of the TOC so that a few
        // cache misses won't destroy the server
        $toc = Registry::get('pkpHelpTocData', true, null);
        if ($toc === null) {
            $topicId = 'index/topic/000000';
            $help = $this->getHelp();
            $helpToc = $help->buildTopicSection($topicId);
            $toc = $help->buildToc($helpToc);

            $cache->setEntireCache($toc);
            Registry::set('pkpHelpTocData', $toc);
        }
        return null;
    }

    /**
     * Load table of contents from xml help topics and their tocs
     * (return cache, if available)
     * @return array associative array of topics and subtopics
     */
    public function getTableOfContents() {
        $cache = $this->_getTocCache();
        return $cache->getContents();
    }

    /**
     * Modifies retrieved array of topics and arranges them into toc
     * @param $helpToc array
     * @return array
     */
    public function buildToc($helpToc) {
        $toc = array();
        foreach($helpToc as $topicId => $section) {
            $toc[$topicId] = array('title' => $section['title'], 'prefix' => '');
            $this->buildTocHelper($toc, $section['section'], '');
        }
        return $toc;
    }

    /**
     * Helper method for buildToc
     * @param $toc array array by reference to be modified
     * @param $section array
     * @param $prefix string numbering of topic
     */
    public function buildTocHelper(&$toc, $section, $prefix) {
        if (isset($section)) {
            $prefix = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$prefix";
            foreach($section as $topicId => $sect) {
                $toc[$topicId] = array('title' => $sect['title'], 'prefix' => $prefix);
                $this->buildTocHelper($toc, $sect['section'], $prefix);
            }
        }
    }

    /**
     * Helper method for getTableOfContents
     * @param $topicId string
     * @param $prevTocId string
     * @return array
     */
    public function buildTopicSection($topicId, $prevTocId = null) {
        $topicDao = DAORegistry::getDAO('HelpTopicDAO');
        $tocDao = DAORegistry::getDAO('HelpTocDAO');

        $topic = $topicDao->getTopic($topicId);
        if ($topicId == 'index/topic/000000') {
            $tocId = $topic->getTocId();
        } else {
            $tocId = $topic->getSubTocId();
        }

        $section = array();
        if ($tocId && $tocId != $prevTocId) {
            $toc = $tocDao->getToc($tocId);
            $topics = $toc->getTopics();
            foreach($topics as $currTopic) {
                $currId = $currTopic->getId();
                $currTitle = $currTopic->getTitle();
                if ($currId != $topicId) {
                    $section[$currId] = array('title' => $currTitle, 'section' => $this->buildTopicSection($currId, $tocId));
                }
            }
        }
        if (empty($section)) {
            $section = null;
        }

        return $section;
    }

    /**
     * Returns the most recent modified file in the specified directory
     * Taken from the php.net site under filemtime
     * @param $dirName string
     * @param $doRecursive bool
     * @return int
     */
    public function dirmtime($dirName, $doRecursive) {
        $lastModified = 0;

        // Cek apakah direktori valid dan dapat dibuka
        if (!is_dir($dirName)) {
            return $lastModified;
        }

        $d = @dir($dirName);
        if ($d === false) {
            // Gagal membuka direktori
            return $lastModified;
        }

        while(($entry = $d->read()) !== false) {
            if ($entry != "." && $entry != "..") {
                $currentPath = $dirName . DIRECTORY_SEPARATOR . $entry;
                $currentModified = 0;

                if (!is_dir($currentPath)) {
                    $currentModified = @filemtime($currentPath);
                } else if ($doRecursive && is_dir($currentPath)) {
                    $currentModified = $this->dirmtime($currentPath, true);
                }

                if ($currentModified > $lastModified) {
                    $lastModified = $currentModified;
                }
            }
        }
        $d->close();
        return $lastModified;
    }

    /**
     * Get an associative array of search paths and their corresponding mapping files.
     * @return array<string, HelpMappingFile>
     */
    public function getSearchPaths() {
        $mappingFiles = $this->getMappingFiles();
        $searchPaths = array();
        for ($i = 0; $i < count($mappingFiles); $i++) {
            $searchPaths[$mappingFiles[$i]->getSearchPath()] = $mappingFiles[$i];
        }
        return $searchPaths;
    }

    /**
     * Placeholder method; to be implemented by subclass (e.g. OJS/OMP/PKPApplication::instantiateHelp() must call this).
     * @return array
     */
    public function loadHelpMappings() {
        return array();
    }
}

?>