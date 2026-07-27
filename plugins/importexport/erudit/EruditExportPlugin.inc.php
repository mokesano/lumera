<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/erudit/EruditExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EruditExportPlugin
 * @ingroup plugins_importexport_erudit
 *
 * @brief Erudit english DTD article export plugin
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

class EruditExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EruditExportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path Path to plugin
     * @return bool True iff plugin initialized successfully
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
        return 'EruditExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.erudit.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.erudit.description');
    }

    /**
     * Display the plugin UI.
     * @param array $args
     * @param mixed $request
     */
    public function display($args, $request): void {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        parent::display($args, $request);

        $issueDao = DAORegistry::getDAO('IssueDAO'); /** @var IssueDAO $issueDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /** @var PublishedArticleDAO $publishedArticleDao */
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /** @var ArticleGalleyDAO $articleGalleyDao */

        $journal = $request->getJournal();
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }
        
        $command = array_shift($args);

        switch ($command) {
            case 'exportGalley':
                $articleId = array_shift($args);
                $galleyId = array_shift($args);

                $article = $publishedArticleDao->getPublishedArticleByArticleId((int) $articleId);
                $galley = $articleGalleyDao->getGalley((int) $galleyId, (int) $articleId);
                
                if ($article && $galley) {
                    $issue = $issueDao->getIssueById((int) $article->getIssueId(), (int) $journal->getId());
                    if ($issue) {
                        $this->exportArticle($journal, $issue, $article, $galley);
                        break;
                    }
                }
                // Fallthrough if invalid article/galley
            default:
                // Display a list of articles for export
                $this->setBreadcrumbs();
                AppLocale::requireComponents(LOCALE_COMPONENT_CORE_SUBMISSION);
                
                $rangeInfo = Handler::getRangeInfo('articles');
                $articleIds = $publishedArticleDao->getPublishedArticleIdsAlphabetizedByJournal((int) $journal->getId(), false);
                $totalArticles = count($articleIds);
                
                if ($rangeInfo && $rangeInfo->isValid()) {
                    $articleIds = array_slice($articleIds, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
                }
                
                import('lib.pkp.classes.core.VirtualArrayIterator');
                $iterator = new VirtualArrayIterator(ArticleSearch::formatResults($articleIds), $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());
                
                $templateMgr->assign('articles', $iterator);
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
                break;
        }
    }

    /**
     * Export article to Erudit format.
     * @param Journal $journal
     * @param Issue $issue
     * @param PublishedArticle $article
     * @param ArticleGalley $galley
     * @param string|null $outputFile
     * @return bool
     */
    public function exportArticle($journal, $issue, $article, $galley, $outputFile = null): bool {
        $this->import('EruditExportDom');
        $doc = XMLCustomWriter::createDocument('article', '-//ERUDIT//Erudit Article DTD 3.0.0//EN', 'http://www.erudit.org/dtd/article/3.0.0/en/eruditarticle.dtd');
        
        $articleNode = EruditExportDom::generateArticleDom($doc, $journal, $issue, $article, $galley);
        XMLCustomWriter::appendChild($doc, $articleNode);

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'wb')) === false) {
                return false;
            }
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"erudit.xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Execute export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, $args) {
        $xmlFile = array_shift($args);
        $journalPath = array_shift($args);
        $articleId = array_shift($args);
        $galleyLabel = array_shift($args);

        $journalDao = DAORegistry::getDAO('JournalDAO'); /** @var JournalDAO $journalDao */
        $issueDao = DAORegistry::getDAO('IssueDAO'); /** @var IssueDAO $issueDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /** @var PublishedArticleDAO $publishedArticleDao */

        $journal = $journalDao->getJournalByPath((string) $journalPath);

        if (!$journal) {
            if ($journalPath !== '') {
                echo __('plugins.importexport.erudit.cliError') . "\n";
                echo __('plugins.importexport.erudit.error.unknownJournal', ['journalPath' => $journalPath]) . "\n\n";
            }
            $this->usage($scriptName);
            return;
        }

        $publishedArticle = $publishedArticleDao->getPublishedArticleByBestArticleId((int) $journal->getId(), $articleId);
        if ($publishedArticle === null) {
            echo __('plugins.importexport.erudit.cliError') . "\n";
            echo __('plugins.importexport.erudit.export.error.articleNotFound', ['articleId' => $articleId]) . "\n\n";
            return;
        }

        $galley = null;
        foreach ($publishedArticle->getGalleys() as $thisGalley) {
            if ($thisGalley->getLabel() === $galleyLabel) {
                $galley = $thisGalley;
                break;
            }
        }

        if (!isset($galley)) {
            echo __('plugins.importexport.erudit.export.error.galleyNotFound', ['galleyLabel' => $galleyLabel]) . "\n\n";
            return;
        }

        $issue = $issueDao->getIssueById((int) $publishedArticle->getIssueId());
        if ($issue && !$this->exportArticle($journal, $issue, $publishedArticle, $galley, $xmlFile)) {
            echo __('plugins.importexport.erudit.cliError') . "\n";
            echo __('plugins.importexport.erudit.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
        } elseif (!$issue) {
            echo __('plugins.importexport.erudit.cliError') . "\n";
            echo __('plugins.importexport.erudit.export.error.issueNotFound', ['articleId' => $articleId]) . "\n\n";
        }
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.erudit.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }

}
?>