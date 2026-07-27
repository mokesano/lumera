<?php
declare(strict_types=1);

/**
 * @file plugins/citationFormats/refWorks/RefWorksCitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RefWorksCitationPlugin
 * @ingroup plugins_citationFormats_refWorks
 *
 * @brief RefWorks citation format plugin
 */

import('classes.plugins.CitationPlugin');

class RefWorksCitationPlugin extends CitationPlugin {
    
    /**
     * Register plugin
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'RefWorksCitationPlugin';
    }

    /**
     * Get display name of plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationFormats.refWorks.displayName');
    }

    /**
     * Get citation format name of plugin
     * @return string
     */
    public function getCitationFormatName(): string {
        return __('plugins.citationFormats.refWorks.citationFormatName');
    }

    /**
     * Get description of plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationFormats.refWorks.description');
    }

}
?>