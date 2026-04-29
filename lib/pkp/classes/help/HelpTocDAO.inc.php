<?php
declare(strict_types=1);

/**
 * @file classes/help/HelpTocDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpTocDAO
 * @ingroup help
 * @see HelpToc
 *
 * @brief Operations for retrieving HelpToc objects.
 */

import('lib.pkp.classes.help.HelpToc');

class HelpTocDAO extends XMLDAO {
    
    /**
     * Constructor
     */
    public function __construct() { // Mengganti nama konstruktor
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpTocDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpTocDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the cache object for a specific TOC ID.
     * @param $tocId string
     * @return FileCache
     */
    protected function _getCache($tocId) { // Menghapus reference (&)
        $cache = Registry::get('helpTocCache', true, null); // Menghapus reference (&)
        $locale = Help::getLocale();

        if (!isset($cache[$locale][$tocId])) {
            $help = Help::getHelp(); // Menghapus reference (&)
            $cacheManager = CacheManager::getManager(); // Menghapus reference (&)
            $cache[$locale][$tocId] = $cacheManager->getFileCache('help-toc-' . $help->getLocale(), $tocId, array($this, '_cacheMiss'));

            // Check to see if the cache info is outdated.
            $cacheTime = $cache[$locale][$tocId]->getCacheTime();
            if ($cacheTime !== null && file_exists($this->getFilename($tocId)) && $cacheTime < filemtime($this->getFilename($tocId))) {
                // The cached data is out of date.
                $cache[$locale][$tocId]->flush();
            }
        }
        Registry::set('helpTocCache', $cache); // Menyimpan kembali ke registry

        return $cache[$locale][$tocId];
    }

    /**
     * Callback function for the cache object.
     * Loads the XML file and populates the cache.
     * @param $cache Cache
     * @param $id mixed
     * @return mixed
     */
    public function _cacheMiss($cache, $id) { // Menghapus reference (&) pada $cache
        $data = Registry::get('helpTocData', true, null); // Menghapus reference (&)

        if ($data === null) {
            $helpFile = $this->getFilename($cache->getCacheId());

            // Add a debug note indicating an XML load.
            $notes = Registry::get('system.debug.notes', true, array());
            $notes[] = array('debug.notes.helpTocLoad', array('id' => $id, 'filename' => $helpFile));
            Registry::set('system.debug.notes', $notes);

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
     * Get the HelpMappingFile object associated with the TOC ID.
     * @param $tocId string
     * @return HelpMappingFile|null
     */
    public function getMappingFile($tocId) { // Menghapus reference (&)
        $help = Help::getHelp(); // Menghapus reference (&)
        $mappingFiles = $help->getMappingFiles(); // Menghapus reference (&)

        for ($i=0; $i < count($mappingFiles); $i++) {
            // "foreach by reference" hack (diganti dengan akses langsung)
            $mappingFile = $mappingFiles[$i]; // Menghapus reference (&)
            if ($mappingFile->containsToc($tocId)) return $mappingFile;
            // unset($mappingFile); // Tidak diperlukan lagi
        }
        return null; // Menghapus reference (&) pada returner
    }

    /**
     * Get the full filename path for a TOC ID.
     * @param $tocId string
     * @return string|null
     */
    public function getFilename($tocId) {
        $mappingFile = $this->getMappingFile($tocId); // Menghapus reference (&)
        return $mappingFile ? $mappingFile->getTocFilename($tocId) : null;
    }

    /**
     * Retrieves a toc by its ID.
     * @param $tocId string
     * @return HelpToc|false
     */
    public function getToc($tocId) { // Menghapus reference (&)
        $cache = $this->_getCache($tocId); // Menghapus reference (&)
        $data = $cache->getContents();

        // check if data exists after loading
        if (!is_array($data)) {
            return false;
        }

        $toc = new HelpToc();

        // Set TOC Attributes
        $toc->setId($data['toc'][0]['attributes']['id']);
        $toc->setTitle($data['toc'][0]['attributes']['title']);
        if (isset($data['toc'][0]['attributes']['parent_topic'])) {
            $toc->setParentTopicId($data['toc'][0]['attributes']['parent_topic']);
        }

        // Add Topics
        if (isset($data['topic'])) {
            foreach ($data['topic'] as $topicData) {
                import('lib.pkp.classes.help.HelpTopic'); // Pastikan HelpTopic di-import
                $topic = new HelpTopic();
                $topic->setId($topicData['attributes']['id']);
                $topic->setTitle($topicData['attributes']['title']);
                $toc->addTopic($topic);
            }
        }

        // Add Breadcrumbs
        if (isset($data['breadcrumb'])) {
            foreach ($data['breadcrumb'] as $breadcrumbData) {
                $toc->addBreadcrumb($breadcrumbData['attributes']['title'], $breadcrumbData['attributes']['url']);
            }
        }

        return $toc;
    }
}

?>