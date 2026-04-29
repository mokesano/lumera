<?php
declare(strict_types=1);

/**
 * @file classes/journal/category/CategoryDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryDAO
 * @ingroup category
 * @see Category, ControlledVocabDAO
 *
 * @brief Operations for retrieving and modifying Category objects
 * [WIZDAM] Cache mechanism updated to use .wiz extension
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CATEGORY_SYMBOLIC', 'category');

class CategoryDAO extends ControlledVocabDAO {
    
    /**
     * Build the Category controlled vocabulary.
     * @return ControlledVocab
     */
    public function build($symbolic = CATEGORY_SYMBOLIC, $assocType = 0, $assocId = 0) {
        return parent::build($symbolic, $assocType, $assocId);
    }

    /**
     * Get the categories list from database.
     * @return array
     */
    public function getCategories() {
        return $this->enumerateBySymbolic(CATEGORY_SYMBOLIC, 0, 0);
    }

    /**
     * Rebuild the cache.
     */
    public function rebuildCache() {
        // Read the full set of categories into an associative array
        $categoryEntryDao = $this->getEntryDAO();
        $categoryControlledVocab = $this->build();
        $categoriesIterator = $categoryEntryDao->getByControlledVocabId($categoryControlledVocab->getId());
        
        $allCategories = array();
        while ($category = $categoriesIterator->next()) {
            $allCategories[$category->getId()] = $category;
            unset($category);
        }

        // Prepare our results array to cache
        $categories = array();

        // Add each journal's categories to the data structure
        $journalDao = DAORegistry::getDAO('JournalDAO');
        
        // [WIZDAM] Mengambil jurnal aktif saja untuk Sitemap/Cache
        $journals = $journalDao->getJournals(true);
        
        while ($journal = $journals->next()) {
            $selectedCategories = $journal->getSetting('categories');
            
            // [PHP 8 Safety] Casting ke array untuk menghindari error pada foreach jika null
            foreach ((array) $selectedCategories as $categoryId) {
                if (!isset($allCategories[$categoryId])) continue;
                
                if (!isset($categories[$categoryId])) {
                    $categories[$categoryId] = array(
                        'category' => $allCategories[$categoryId],
                        'journals' => array()
                    );
                }
                $categories[$categoryId]['journals'][] = $journal;
            }
            unset($journal);
        }

        // Save the cache file
        // Fungsi ini akan memanggil getCacheFilename() yang sudah diarahkan ke .wiz
        $fp = fopen($this->getCacheFilename(), 'w');
        if (!$fp) return false;

        // [WIZDAM] Data diserialisasi dan disimpan ke format .wiz
        fwrite($fp, serialize($categories));
        fclose($fp);
    }

    /**
     * Get the cached set of categories, building it if necessary.
     * @return array
     */
    public function getCache() {
        $categoryEntryDao = $this->getEntryDAO();
        $journalDao = DAORegistry::getDAO('JournalDAO');

        // Load and return the cache, building it if necessary.
        $filename = $this->getCacheFilename();
        
        // Logika file_get_contents tetap bekerja sempurna untuk file .wiz
        if (!file_exists($filename)) $this->rebuildCache();
        
        $contents = file_get_contents($filename);
        if ($contents) return unserialize($contents);
        return null;
    }

    /**
     * Get the cache filename.
     * [WIZDAM] Updated to use .wiz extension for security
     * @return string
     */
    public function getCacheFilename() {
        return 'cache/fc-categories.wiz';
    }
}

?>