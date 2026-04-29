<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/pubmed/PubMedExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubMedExportPlugin
 * @ingroup plugins_importexport_pubmed
 *
 * @brief PubMed/MEDLINE XML metadata export plugin
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

class PubMedExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PubMedExportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path to plugin
     * @return bool True if plugin initialized successfully
     */
    public function register($category, $path): bool {
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
        return 'PubMedExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.pubmed.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.pubmed.description');
    }

    /**
     * Display the plugin UI.
     * @param array $args
     * @param object $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager();
        parent::display($args, $request);

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $journal = Request::getJournal();

        $command = array_shift($args);

        switch ($command) {
            case 'exportIssues':
                $issueIds = Request::getUserVar('issueId');
                if (!isset($issueIds)) $issueIds = [];
                $issues = [];
                foreach ($issueIds as $issueId) {
                    $issue = $issueDao->getIssueById($issueId);
                    if (!$issue) Request::redirect();
                    $issues[] = $issue;
                }
                $this->exportIssues($journal, $issues);
                break;
            case 'exportIssue':
                $issueId = array_shift($args);
                $issue = $issueDao->getIssueById($issueId);
                if (!$issue) Request::redirect();
                $issues = [$issue];
                $this->exportIssues($journal, $issues);
                break;
            case 'exportArticle':
                $articleIds = [array_shift($args)];
                $result = ArticleSearch::formatResults($articleIds);
                $this->exportArticles($result);
                break;
            case 'exportArticles':
                $articleIds = Request::getUserVar('articleId');
                if (!isset($articleIds)) $articleIds = [];
                $results = ArticleSearch::formatResults($articleIds);
                $this->exportArticles($results);
                break;
            case 'issues':
                // Display a list of issues for export
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents(LOCALE_COMPONENT_OJS_EDITOR);
                $issues = $issueDao->getIssues($journal->getId(), Handler::getRangeInfo('issues'));

                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
                break;
            case 'articles':
                // Display a list of articles for export
                $this->setBreadcrumbs([], true);
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $rangeInfo = Handler::getRangeInfo('articles');
                $articleIds = $publishedArticleDao->getPublishedArticleIdsByJournal($journal->getId(), false);
                $totalArticles = count($articleIds);
                if ($rangeInfo->isValid()) {
                    $articleIds = array_slice($articleIds, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
                }
                import('lib.pkp.classes.core.VirtualArrayIterator');
                $iterator = new VirtualArrayIterator(ArticleSearch::formatResults($articleIds), $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());
                $templateMgr->assign('articles', $iterator);
                $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
                break;
            default:
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
        }
    }

    /**
     * Export articles.
     * @param array $results
     * @param string|null $outputFile
     * @return bool
     */
    public function exportArticles($results, $outputFile = null): bool {
        $this->import('PubMedExportDom');
        
        // Instantiate the DOM helper (cannot call non-static methods statically in PHP 8)
        $domHelper = new PubMedExportDom();
        
        $doc = $domHelper->generatePubMedDom();
        $articleSetNode = $domHelper->generateArticleSetDom($doc);

        foreach ($results as $result) {
            $journal = $result['journal'];
            $issue = $result['issue'];
            $section = $result['section'];
            $article = $result['publishedArticle'];

            $articleNode = $domHelper->generateArticleDom($doc, $journal, $issue, $section, $article);
            XMLCustomWriter::appendChild($articleSetNode, $articleNode);
        }

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'w')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"pubmed.xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Export issues.
     * @param object $journal
     * @param array $issues
     * @param string|null $outputFile
     * @return bool
     */
    public function exportIssues($journal, array $issues, $outputFile = null): bool {
        $this->import('PubMedExportDom');
        
        // Instantiate the DOM helper
        $domHelper = new PubMedExportDom();
        
        $doc = $domHelper->generatePubMedDom();
        $articleSetNode = $domHelper->generateArticleSetDom($doc);

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        foreach ($issues as $issue) {
            foreach ($sectionDao->getSectionsForIssue($issue->getId()) as $section) {
                foreach ($publishedArticleDao->getPublishedArticlesBySectionId($section->getId(), $issue->getId()) as $article) {
                    $articleNode = $domHelper->generateArticleDom($doc, $journal, $issue, $section, $article);
                    XMLCustomWriter::appendChild($articleSetNode, $articleNode);
                }
            }
        }

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'w')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"pubmed.xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, $args) {
        // $command = array_shift($args);
        $xmlFile = array_shift($args);
        $journalPath = array_shift($args);

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        
        $journal = $journalDao->getJournalByPath($journalPath);

        if (!$journal) {
            if ($journalPath != '') {
                echo __('plugins.importexport.pubmed.cliError') . "\n";
                echo __('plugins.importexport.pubmed.error.unknownJournal', ['journalPath' => $journalPath]) . "\n\n";
            }
            $this->usage($scriptName);
            return;
        }

        if ($xmlFile != '') {
            switch (array_shift($args)) {
                case 'articles':
                    $results = ArticleSearch::formatResults($args);
                    if (!$this->exportArticles($results, $xmlFile)) {
                        echo __('plugins.importexport.pubmed.cliError') . "\n";
                        echo __('plugins.importexport.pubmed.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                    }
                    return;
                case 'issue':
                    $issueId = array_shift($args);
                    $issue = $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
                    if ($issue == null) {
                        echo __('plugins.importexport.pubmed.cliError') . "\n";
                        echo __('plugins.importexport.pubmed.export.error.issueNotFound', ['issueId' => $issueId]) . "\n\n";
                        return;
                    }
                    $issues = [$issue];
                    if (!$this->exportIssues($journal, $issues, $xmlFile)) {
                        echo __('plugins.importexport.pubmed.cliError') . "\n";
                        echo __('plugins.importexport.pubmed.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                    }
                    return;
            }
        }
        $this->usage($scriptName);
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.pubmed.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }
}

?>