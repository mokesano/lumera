<?php
declare(strict_types=1);

/**
 * @defgroup plugins_citationLookup_worldcat
 */

/**
 * @file plugins/citationLookup/worldcat/PKPWorldcatCitationLookupPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPWorldcatCitationLookupPlugin
 * @ingroup plugins_citationLookup_worldcat
 *
 * @brief Cross-application WorldCat citation lookup plugin
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Explicit Visibility & Return Types
 */

import('classes.plugins.Plugin');

class PKPWorldcatCitationLookupPlugin extends Plugin {
    
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
        return 'WorldcatCitationLookupPlugin';
    }

    /**
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationLookup.worldcat.displayName');
    }

    /**
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationLookup.worldcat.description');
    }
}
?>