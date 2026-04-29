<?php
declare(strict_types=1);

/**
 * @file classes/help/HelpTopicDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpTopicDAO
 * @ingroup help
 * @see HelpTopic
 *
 * @brief Operations for retrieving HelpTopic objects.
 */

import('lib.pkp.classes.help.HelpTopic');
import('lib.pkp.classes.help.PKPHelp');
import('lib.pkp.classes.help.HelpTopicSection'); // Import yang hilang

class HelpTopicDAO extends XMLDAO {
    
    /**
     * Constructor
     */
    public function __construct() { // Mengganti nama konstruktor
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpTopicDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpTopicDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the cache object for a specific Topic ID.
     * @param $topicId string
     * @return FileCache
     */
    protected function _getCache($topicId) { // Menghapus reference (&)
        $cache = Registry::get('helpTopicCache', true, null); // Menghapus reference (&)
        $locale = PKPHelp::getLocale();

        if (!isset($cache[$locale][$topicId])) {
            $help = PKPHelp::getHelp(); // Menghapus reference (&)
            $cacheManager = CacheManager::getManager(); // Menghapus reference (&)
            $cache[$locale][$topicId] = $cacheManager->getFileCache('help-topic-' . $locale, $topicId, array($this, '_cacheMiss'));

            // Check to see if the cache info is outdated.
            $cacheTime = $cache[$locale][$topicId]->getCacheTime();
            if ($cacheTime !== null && file_exists($this->getFilename($topicId)) && $cacheTime < filemtime($this->getFilename($topicId))) {
                // The cached data is out of date.
                $cache[$locale][$topicId]->flush();
            }
        }
        Registry::set('helpTopicCache', $cache); // Menyimpan kembali ke registry

        return $cache[$locale][$topicId]; // Menghapus reference (&)
    }

    /**
     * Get the HelpMappingFile object associated with the Topic ID.
     * @param $topicId string
     * @return HelpMappingFile|null
     */
    public function getMappingFile($topicId) { // Menghapus reference (&)
        $help = PKPHelp::getHelp(); // Menghapus reference (&)
        $mappingFiles = $help->getMappingFiles(); // Menghapus reference (&)

        for ($i = 0; $i < count($mappingFiles); $i++) {
            // "foreach by reference" hack (diganti dengan akses langsung)
            $mappingFile = $mappingFiles[$i]; // Menghapus reference (&)
            if ($mappingFile->containsTopic($topicId)) return $mappingFile;
            // unset($mappingFile); // Tidak diperlukan lagi
        }
        return null; // Menghapus reference (&) pada returner
    }

    /**
     * Get the full filename path for a Topic ID.
     * @param $topicId string
     * @return string|null
     */
    public function getFilename($topicId) {
        // [WIZDAM FIX] Jika topicId null, langsung return null tanpa proses
        if ($topicId === null) {
            return null;
        }
        // Menghapus reference (&)
        $mappingFile = $this->getMappingFile($topicId);
        return $mappingFile ? $mappingFile->getTopicFilename($topicId) : null;
    }

    /**
     * Callback function for the cache object.
     * Loads the XML file and populates the cache.
     * @param $cache Cache
     * @param $id mixed
     * @return mixed
     */
    public function _cacheMiss($cache, $id) { // Menghapus reference (&) pada $cache
        $data = Registry::get('helpTopicData', true, null); // Menghapus reference (&)
        if ($data === null) {
            $helpFile = $this->getFilename($cache->getCacheId());

            // Add a debug note indicating an XML load.
            $notes = Registry::get('system.debug.notes', true, array());
            $notes[] = array('debug.notes.helpTopicLoad', array('id' => $id, 'filename' => $helpFile));
            Registry::set('system.debug.notes', $notes);

            // [WIZDAM FIX] Jangan parse jika file tidak ditemukan
            if ($helpFile === null || !file_exists($helpFile)) {
                return false;
            }
            $data = $this->parseStruct($helpFile); // Menghapus reference (&)

            // check if data exists before saving it to cache
            if ($data === false) {
                return false;
            }
            $cache->setEntireCache($data);
        }
        return null;
    }

    /**
     * Retrieve a topic by its ID.
     * @param $topicId string
     * @return HelpTopic|false
     */
    public function getTopic($topicId) { // Menghapus reference (&)
        $cache = $this->_getCache($topicId); // Menghapus reference (&)
        $data = $cache->getContents();

        // check if data exists after loading
        if (!is_array($data)) {
            return false;
        }

        $topic = new HelpTopic();

        $topic->setId($data['topic'][0]['attributes']['id']);
        $topic->setTitle($data['topic'][0]['attributes']['title']);
        $topic->setTocId($data['topic'][0]['attributes']['toc']);
        if (isset($data['topic'][0]['attributes']['subtoc'])) {
            $topic->setSubTocId($data['topic'][0]['attributes']['subtoc']);
        }

        if (isset($data['section'])) {
            foreach ($data['section'] as $sectionData) {
                $section = new HelpTopicSection();
                $section->setTitle(isset($sectionData['attributes']['title']) ? $sectionData['attributes']['title'] : null);
                $section->setContent($sectionData['value']);
                $topic->addSection($section);
            }
        }

        if (isset($data['related_topic'])) {
            foreach ($data['related_topic'] as $relatedTopic) {
                $relatedTopicArray = array('id' => $relatedTopic['attributes']['id'], 'title' => $relatedTopic['attributes']['title']);
                $topic->addRelatedTopic($relatedTopicArray);
            }
        }

        return $topic;
    }

    /**
     * Returns a set of topics matching a specified keyword.
     * @param $keyword string
     * @return array matching HelpTopics
     */
    public function getTopicsByKeyword($keyword) { // Menghapus reference (&)
        $keyword = PKPString::strtolower($keyword);
        $matchingTopics = array();
        $help = PKPHelp::getHelp(); // Menghapus reference (&)

        foreach ($help->getSearchPaths() as $searchPath => $mappingFile) {
            $dir = @opendir($searchPath); // @ ditambahkan untuk menekan warning jika direktori tidak ada
            if ($dir === false) continue; // Skip jika direktori tidak dapat dibuka

            while (($file = readdir($dir)) !== false) {
                $currFile = $searchPath . DIRECTORY_SEPARATOR . $file;
                if (is_dir($currFile) && $file != 'toc' && $file != '.' && $file != '..') {
                    // Panggilan non-statis
                    $this->searchDirectory($mappingFile, $matchingTopics, $keyword, $currFile);
                }
            }
            closedir($dir);
        }

        krsort($matchingTopics);
        $topics = array_values($matchingTopics);

        return $topics; // Menghapus reference (&)
    }

    /**
     * Parses deeper into folders if subdirectories exists otherwise scans the topic xml files
     * @param $mappingFile object The responsible mapping file
     * @param $matchingTopics array stores topics that match the keyword
     * @param $keyword string
     * @param $dir string
     * @modifies $matchingTopics array by reference by making appropriate calls to functions
     */
    protected function searchDirectory($mappingFile, &$matchingTopics, $keyword, $dir) { // Menghapus reference (&) pada $mappingFile, mempertahankan pada $matchingTopics karena dimodifikasi
        $currDir = @opendir($dir);
        if ($currDir === false) return; // Skip jika direktori tidak dapat dibuka

        while (($file = readdir($currDir)) !== false) {
            $currFile = sprintf('%s/%s', $dir, $file);
            if (is_dir($currFile) && $file != '.' && $file != '..' && $file != 'toc') {
                // Panggilan non-statis, meneruskan $matchingTopics by reference
                $this->searchDirectory($mappingFile, $matchingTopics, $keyword, $currFile);
            } else {
                // Panggilan non-statis, meneruskan $matchingTopics by reference
                $this->scanTopic($mappingFile, $matchingTopics, $keyword, $dir, $file);
            }
        }
        closedir($currDir);
    }

    /**
     * Scans topic xml files for keywords
     * @param $mappingFile object The responsible mapping file
     * @param $matchingTopics array stores topics that match the keyword
     * @param $keyword string
     * @param $dir string
     * @param $file string
     * @modifies $matchingTopics array by reference
     */
    protected function scanTopic($mappingFile, &$matchingTopics, $keyword, $dir, $file) { // Menghapus reference (&) pada $mappingFile, mempertahankan pada $matchingTopics karena dimodifikasi
        if (preg_match('/^\d{6,6}\.xml$/', $file)) {
            $topicId = $mappingFile->getTopicIdForFilename($dir . DIRECTORY_SEPARATOR . $file);
            $topic = $this->getTopic($topicId); // Menghapus reference (&)

            if ($topic) {
                $numMatches = PKPString::substr_count(PKPString::strtolower($topic->getTitle()), $keyword);

                foreach ($topic->getSections() as $section) {
                    $numMatches += PKPString::substr_count(PKPString::strtolower($section->getTitle()), $keyword);
                    $numMatches += PKPString::substr_count(PKPString::strtolower($section->getContent()), $keyword);
                }

                if ($numMatches > 0) {
                    // Penambahan topic ke array dengan key prioritas
                    $matchingTopics[($numMatches << 16) + count($matchingTopics)] = $topic;
                }
            }
        }
    }
}

?>