<?php
declare(strict_types=1);

/**
 * @file plugins/citationFormats/bibtex/BibtexCitationPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BibtexCitationPlugin
 * @ingroup plugins_citationFormats_bibtex
 *
 * @brief BibTeX citation format plugin
 */

import('classes.plugins.CitationPlugin');

class BibtexCitationPlugin extends CitationPlugin {
    
    /**
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();

        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->register_modifier('bibtex_escape', [$this, 'bibtexEscape']);

        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'BibtexCitationPlugin';
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.citationFormats.bibtex.displayName');
    }

    /**
     * @return string
     */
    public function getCitationFormatName(): string {
        return __('plugins.citationFormats.bibtex.citationFormatName');
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.citationFormats.bibtex.description');
    }

    /**
     * @function bibtex_escape Escape strings for inclusion in BibTeX cites
     * @param string $arg
     * @return string
     */
    public function bibtexEscape($arg) {
        return htmlspecialchars(str_replace(
            ['{', '}', '$','"', '&apos;'],
            ['\\{', '\\}', '\\$', '\\"', '\''],
            html_entity_decode((string) $arg, ENT_QUOTES, 'UTF-8')
        ));
    }
}

?>