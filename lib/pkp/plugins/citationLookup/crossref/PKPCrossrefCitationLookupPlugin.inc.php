<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationLookup_crossref
 */

/**
 * @file plugins/citationLookup/crossref/PKPCrossrefCitationLookupPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPCrossrefCitationLookupPlugin
 * @ingroup plugins_citationLookup_crossref
 *
 * @brief Cross-application CrossRef citation lookup plugin
 * * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Explicit Type Hinting & Return Types
 */

import('classes.plugins.Plugin');

class PKPCrossrefCitationLookupPlugin extends Plugin {
    
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
     * * [WIZDAM NOTE] 
     * Menggunakan tipe data string ketat untuk category dan path.
     * Return bool wajib.
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
     */
    public function getName(): string {
        return 'CrossrefCitationLookupPlugin';
    }

    /**
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationLookup.crossref.displayName');
    }

    /**
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationLookup.crossref.description');
    }
}
?>