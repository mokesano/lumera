<?php
declare(strict_types=1);

/**
 * @file plugins/citationFormats/cbe/CbeCitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CbeCitationPlugin
 * @ingroup plugins_citationFormats_cbe
 *
 * @brief CBE citation format plugin
 */

import('classes.plugins.CitationPlugin');

class CbeCitationPlugin extends CitationPlugin {
    
    /**
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
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
        return 'CbeCitationPlugin';
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationFormats.cbe.displayName');
    }

    /**
     * @return string
     */
    public function getCitationFormatName(): string {
        return __('plugins.citationFormats.cbe.citationFormatName');
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationFormats.cbe.description');
    }

}

?>