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
 * @brief Operations for retrieving and modifying Category objects.
 * [LUMERA] Cache mechanism updated to use .wiz extension for security.
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CATEGORY_SYMBOLIC', 'category');

class CategoryDAO extends ControlledVocabDAO {
    
    /**
     * Build the Category controlled vocabulary.
     * @param string $symbolic
     * @param mixed $assocType
     * @param mixed $assocId
     * @return ControlledVocab
     */
    public function build($symbolic = CATEGORY_SYMBOLIC, $assocType = 0, $assocId = 0) {
        return parent::build($symbolic, (int) $assocType, (int) $assocId);
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
     * @return bool
     */
    public function rebuildCache() {
        $categoryEntryDao = $this->getEntryDAO();
        $categoryControlledVocab = $this->build();

        $categoriesIterator = $categoryEntryDao->getByControlledVocabId($categoryControlledVocab->getId());
        
        $allCategories = [];
        while ($category = $categoriesIterator->next()) {
            $allCategories[$category->getId()] = $category;
        }

        $categories = [];

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);
        
        while ($journal = $journals->next()) {
            $selectedCategories = (array) $journal->getSetting('categories');
            
            foreach ($selectedCategories as $categoryId) {
                if (!isset($allCategories[$categoryId])) {
                    continue;
                }
                
                if (!isset($categories[$categoryId])) {
                    $categories[$categoryId] = [
                        'category' => $allCategories[$categoryId],
                        'journals' => []
                    ];
                }
                $categories[$categoryId]['journals'][] = $journal;
            }
        }

        $cacheData = serialize($categories);
        return file_put_contents($this->getCacheFilename(), $cacheData) !== false;
    }

    /**
     * Get the cached set of categories, building it if necessary.
     * @return array|null
     */
    public function getCache() {
        $filename = $this->getCacheFilename();
        
        if (!file_exists($filename)) {
            $this->rebuildCache();
        }
        
        $contents = file_get_contents($filename);
        if ($contents !== false) {
            return unserialize((string) $contents);
        }
        
        return null;
    }

    /**
     * Get the cache filename.
     * [LUMERA] Updated to use .wiz extension for security.
     * @return string
     */
    public function getCacheFilename() {
        return 'cache/fc-categories.wiz';
    }

}
?>