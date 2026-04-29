<?php
declare(strict_types=1);

/**
 * @file plugins/citationFormats/refMan/RefManCitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RefManCitationPlugin
 * @ingroup plugins_citationFormats_refMan
 *
 * @brief Reference Manager citation format plugin
 */

import('classes.plugins.CitationPlugin');

class RefManCitationPlugin extends CitationPlugin {
    
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
        return 'RefManCitationPlugin';
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationFormats.refMan.displayName');
    }

    /**
     * @return string
     */
    public function getCitationFormatName(): string {
        return __('plugins.citationFormats.refMan.citationFormatName');
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationFormats.refMan.description');
    }

    /**
     * Display a custom-formatted citation.
     * @param Article $article
     * @param Issue $issue
     * @param Journal $journal
     */
    public function displayCitation($article, $issue, $journal) {
        header('Content-Disposition: attachment; filename="' . $article->getId() . '-refMan.ris"');
        header('Content-Type: application/x-Research-Info-Systems');
        echo parent::fetchCitation($article, $issue, $journal);
    }
}

?>