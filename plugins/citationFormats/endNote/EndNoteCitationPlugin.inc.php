<?php
declare(strict_types=1);

/**
 * @file plugins/citationFormats/endNote/EndNoteCitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EndNoteCitationPlugin
 * @ingroup plugins_citationFormats_endNote
 *
 * @brief EndNote citation format plugin
 */

import('classes.plugins.CitationPlugin');

class EndNoteCitationPlugin extends CitationPlugin {
    
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
        return 'EndNoteCitationPlugin';
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationFormats.endNote.displayName');
    }

    /**
     * @return string
     */
    public function getCitationFormatName(): string {
        return __('plugins.citationFormats.endNote.citationFormatName');
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationFormats.endNote.description');
    }

    /**
     * Return a custom-formatted citation.
     * @param Article $article
     * @param Issue $issue
     * @param Journal $journal
     */
    public function displayCitation($article, $issue, $journal) {
        header('Content-Disposition: attachment; filename="' . $article->getId() . '-endNote.enw"');
        header('Content-Type: application/x-endnote-refer');
        echo parent::fetchCitation($article, $issue, $journal);
    }
}

?>