<?php
declare(strict_types=1);

/**
 * @file plugins/generic/coins/CoinsPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CoinsPlugin
 * @ingroup plugins_generic_coins
 *
 * @brief COinS plugin class for embedding metadata in HTML.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class CoinsPlugin extends GenericPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        
        if ($success && $this->getEnabled()) {
            HookRegistry::register('Templates::Article::Footer::PageFooter', [$this, 'insertFooter']);
            HookRegistry::register('Templates::Issue::Issue::Article', [$this, 'insertFooter']);
        }
        
        return $success;
    }

    /**
     * Get display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.coins.displayName');
    }

    /**
     * Get description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.coins.description');
    }

    /**
     * Get the name of the settings file to be installed site-wide.
     * @return string|null
     */
    public function getInstallSitePluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Insert COinS tag into the page footer.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function insertFooter($hookName, $params) {
        if (!$this->getEnabled()) {
            return false;
        }

        // Standard PKP hook signature: [$hookName, $templateMgr, &$output]
        $templateMgr = $params[1] ?? TemplateManager::getManager();
        $output =& $params[2]; 

        $article = $templateMgr->get_template_vars('article');
        $journal = $templateMgr->get_template_vars('currentJournal');
        $issue = $templateMgr->get_template_vars('issue');

        // [LUMERA FIX] Strict null-safety check to prevent "Call to a member function on null"
        if (!$article || !$journal || !$issue) {
            return false;
        }

        $authors = $article->getAuthors();
        if (!is_array($authors) || empty($authors)) {
            return false;
        }
        
        $firstAuthor = $authors[0];
        $request = Application::get()->getRequest();

        $vars = [
            ['ctx_ver', 'Z39.88-2004'],
            ['rft_id', $request->getRouter()->url($request, null, 'article', 'view', [(int) $article->getId()])],
            ['rft_val_fmt', 'info:ofi/fmt:kev:mtx:journal'],
            ['rft.genre', 'article'],
            ['rft.title', (string) $journal->getLocalizedTitle()],
            ['rft.jtitle', (string) $journal->getLocalizedTitle()],
            ['rft.atitle', (string) $article->getLocalizedTitle()],
            ['rft.artnum', (string) $article->getBestArticleId()],
            ['rft.stitle', (string) $journal->getLocalizedSetting('abbreviation')],
            ['rft.volume', (string) $issue->getVolume()],
            ['rft.issue', (string) $issue->getNumber()],
            ['rft.aulast', (string) $firstAuthor->getLastName()],
            ['rft.aufirst', (string) $firstAuthor->getFirstName()],
            ['rft.auinit', (string) $firstAuthor->getMiddleName()]
        ];

        $datePublished = $article->getDatePublished() ?: $issue->getDatePublished();
        if ($datePublished) {
            $timestamp = strtotime((string) $datePublished);
            if ($timestamp !== false) {
                $vars[] = ['rft.date', date('Y-m-d', $timestamp)];
            }
        }

        foreach ($authors as $author) {
            $vars[] = ['rft.au', (string) $author->getFullName()];
        }

        $doi = $article->getPubId('doi');
        if (!empty($doi)) {
            $vars[] = ['rft_id', 'info:doi/' . (string) $doi];
        }
        
        $pages = $article->getPages();
        if (!empty($pages)) {
            $vars[] = ['rft.pages', (string) $pages];
        }
        
        $printIssn = $journal->getSetting('printIssn');
        if (!empty($printIssn)) {
            $vars[] = ['rft.issn', (string) $printIssn];
        }
        
        $onlineIssn = $journal->getSetting('onlineIssn');
        if (!empty($onlineIssn)) {
            $vars[] = ['rft.eissn', (string) $onlineIssn];
        }

        $titleParts = [];
        foreach ($vars as $entries) {
            $name = (string) $entries[0];
            $value = (string) $entries[1];
            $titleParts[] = $name . '=' . urlencode($value);
        }
        
        // [LUMERA FIX] Use implode and explicit ENT_QUOTES for safer HTML entity encoding
        $titleString = htmlentities(implode('&', $titleParts), ENT_QUOTES, 'UTF-8');
        $output .= "<span class=\"Z3988\" title=\"$titleString\"></span>\n";
        
        return false;
    }
    
}
?>