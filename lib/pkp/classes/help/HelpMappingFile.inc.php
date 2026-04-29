<?php
declare(strict_types=1);

/**
 * @file classes/help/HelpMappingFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpMappingFile
 * @ingroup help
 *
 * @brief Abstracts a Help mapping XML file.
 */

class HelpMappingFile {
    /** @var string */
    public $filename;
    /** @var Cache */
    protected $cache; // Mengubah var menjadi protected

    /**
     * Constructor.
     * @param $filename string
     */
    public function __construct($filename) { // Mengganti nama konstruktor
        $this->filename = $filename;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpMappingFile($filename) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpMappingFile(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($filename);
    }

    /**
     * Get the cache object, initializing it if necessary.
     * @return Cache
     */
    protected function _getCache() { // Menghapus reference (&)
        if (!isset($this->cache)) {
            $cacheManager = CacheManager::getManager(); // Menghapus reference (&)
            $this->cache = $cacheManager->getFileCache(
                'helpmap', md5($this->filename),
                array($this, '_cacheMiss') // Menghapus reference (&) pada $this
            );

            // Check to see if the cache info is outdated.
            $cacheTime = $this->cache->getCacheTime();
            if ($cacheTime !== null && file_exists($this->filename) && $cacheTime < filemtime($this->filename)) {
                // The cached data is out of date.
                $this->cache->flush();
            }
        }
        return $this->cache;
    }

    /**
     * Callback function for the cache object.
     * Loads the XML file and populates the cache.
     * @param $cache Cache
     * @param $id mixed
     * @return mixed
     */
    public function _cacheMiss($cache, $id) { // Menghapus reference (&) pada parameter
        $mappings = array();

        // Add a debug note indicating an XML load.
        $notes = Registry::get('system.debug.notes'); // Menghapus reference (&)
        $notes[] = array('debug.notes.helpMappingLoad', array('id' => $id, 'filename' => $this->filename));
        Registry::set('system.debug.notes', $notes); // Harus menyetel ulang notes

        // Reload help XML file
        $xmlDao = new XMLDAO();
        $data = $xmlDao->parseStruct($this->filename, array('topic'));
        // Build associative array of page keys and ids
        if (isset($data['topic'])) {
            foreach ($data['topic'] as $helpData) {
                $mappings[$helpData['attributes']['key']] = $helpData['attributes']['id'];
            }
        }

        $cache->setEntireCache($mappings);
        return isset($mappings[$id])?$mappings[$id]:null;
    }

    /**
     * Map a help key to a topic ID.
     * @param $key string
     * @return int
     */
    public function map($key) {
        $cache = $this->_getCache(); // Menghapus reference (&)
        return $cache->get($key);
    }

    /**
     * Check if a table of contents (TOC) exists for a given ID.
     * @param $tocId int
     * @return boolean
     */
    public function containsToc($tocId) {
        return file_exists($this->getTocFilename($tocId));
    }

    /**
     * Check if a help topic exists for a given ID.
     * @param $topicId int
     * @return boolean
     */
    public function containsTopic($topicId) {
        return file_exists($this->getTopicFilename($topicId));
    }

    /**
     * This is an abstract function that should be implemented by
     * subclasses.
     * @param $tocId int
     * @return string
     */
    public function getTocFilename($tocId) {
        fatalError('HelpMappingFile::getTocFilename should be overridden');
    }

    /**
     * This is an abstract function that should be implemented by
     * subclasses.
     * @param $topicId int
     * @return string
     */
    public function getTopicFilename($topicId) {
        fatalError('HelpMappingFile::getTopicFilename should be overridden');
    }

    /**
     * This is an abstract function that should be implemented by
     * subclasses.
     * @param $locale string|null
     * @return string
     */
    public function getSearchPath($locale = null) {
        fatalError('HelpMappingFile::getSearchPath should be overridden');
    }

    /**
     * This is an abstract function that should be implemented by
     * subclasses.
     * @param $filename string
     * @return int
     */
    public function getTopicIdForFilename($filename) {
        fatalError('HelpMappingFile::getTopicIdForFilename should be overridden');
    }
}

?>