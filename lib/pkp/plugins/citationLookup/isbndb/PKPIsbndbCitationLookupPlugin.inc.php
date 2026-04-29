<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationLookup_isbndb
 */

/**
 * @file plugins/citationLookup/isbndb/PKPIsbndbCitationLookupPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPIsbndbCitationLookupPlugin
 * @ingroup plugins_citationLookup_isbndb
 *
 * @brief Cross-application ISBNdb citation lookup plugin
 * * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Explicit Type Hinting & Return Types
 */

import('classes.plugins.Plugin');

class PKPIsbndbCitationLookupPlugin extends Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    //
    // Override protected template methods from PKPPlugin
    //
    
    /**
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     * [WIZDAM] Added explicit type hinting and return type
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        if (!parent::register($category, $path, $mainContextId)) {
            return false;
        }
        $this->addLocaleData();
        return true;
    }

    /**
     * @see PKPPlugin::getName()
     * @return string
     * [WIZDAM] Added explicit return type
     */
    public function getName(): string {
        return 'IsbndbCitationLookupPlugin';
    }

    /**
     * @see PKPPlugin::getDisplayName()
     * @return string
     * [WIZDAM] Added explicit return type
     */
    public function getDisplayName(): string {
        return __('plugins.citationLookup.isbndb.displayName');
    }

    /**
     * @see PKPPlugin::getDescription()
     * @return string
     * [WIZDAM] Added explicit return type
     */
    public function getDescription(): string {
        return __('plugins.citationLookup.isbndb.description');
    }
}
?>