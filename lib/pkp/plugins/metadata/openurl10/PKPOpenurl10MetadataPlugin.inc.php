<?php
declare(strict_types=1);

/**
 * @defgroup plugins_metadata_openurl10
 */

/**
 * @file plugins/metadata/openurl10/PKPOpenurl10MetadataPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPOpenurl10MetadataPlugin
 * @ingroup plugins_metadata_openurl10
 *
 * @brief Abstract base class for OpenURL 1.0 metadata plugins
 *
 * [WIZDAM REFACTOR]
 * - PHP 8.1+ Strict Compliance
 * - Explicit Visibility & Return Types
 */

import('lib.pkp.classes.plugins.MetadataPlugin');

class PKPOpenurl10MetadataPlugin extends MetadataPlugin {
    
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
     * @see PKPPlugin::getName()
     * @return string The name of this plugin
     */
    public function getName(): string {
        return 'Openurl10MetadataPlugin';
    }

    /**
     * @see PKPPlugin::getDisplayName()
     * @return string Human-readable name of plugin
     */
    public function getDisplayName(): string {
        return __('plugins.metadata.openurl10.displayName');
    }

    /**
     * @see PKPPlugin::getDescription()
     * @return string Human-readable description of plugin
     */
    public function getDescription(): string {
        return __('plugins.metadata.openurl10.description');
    }
}
?>