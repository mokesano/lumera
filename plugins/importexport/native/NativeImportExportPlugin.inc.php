<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/native/NativeImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeImportExportPlugin
 * @ingroup plugins_importexport_native
 *
 * @brief Native import/export plugin
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

define('NATIVE_DTD_ID', '-//PKP//OJS Articles and Issues XML//EN');

class NativeImportExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NativeImportExportPlugin() {
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
     * Get the DTD URL for the export XML.
     * @return string
     */
    public function getDTDUrl(): string {
        $versionDao = DAORegistry::getDAO('VersionDAO');
        $currentVersion = $versionDao->getCurrentVersion();
        return 'http://pkp.sfu.ca/ojs/dtds/' . urlencode($currentVersion->getMajor() . '.' . $currentVersion->getMinor() . '.' . $currentVersion->getRevision()) . '/native.dtd';
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
        return 'NativeImportExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.native.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.native.description');
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
        $journal = $request->getJournal();
        
        $command = array_shift($args);

        switch ($command) {
            case 'exportIssues':
                $issueIds = $request->getUserVar('issueId');
                if (!isset($issueIds)) $issueIds = [];
                $issues = [];
                foreach ($issueIds as $issueId) {
                    $issue = $issueDao->getIssueById($issueId, $journal->getId());
                    if (!$issue) $request->redirect();
                    $issues[] = $issue;
                }
                $this->exportIssues($journal, $issues);
                break;

            case 'exportIssue':
                $issueId = array_shift($args);
                $issue = $issueDao->getIssueById($issueId, $journal->getId());
                if (!$issue) $request->redirect();
                $this->exportIssue($journal, $issue);
                break;

            case 'exportArticle':
                $articleIds = [array_shift($args)];
                $results = ArticleSearch::formatResults($articleIds);
                $result = array_shift($results);
                $this->exportArticle($journal, $result['issue'], $result['section'], $result['publishedArticle']);
                break;

            case 'exportArticles':
                $articleIds = $request->getUserVar('articleId');
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
                $articleIds = $publishedArticleDao->getPublishedArticleIdsAlphabetizedByJournal($journal->getId(), false);
                $totalArticles = count($articleIds);
                if ($rangeInfo->isValid()) {
                    $articleIds = array_slice($articleIds, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
                }
                import('lib.pkp.classes.core.VirtualArrayIterator');
                $iterator = new VirtualArrayIterator(ArticleSearch::formatResults($articleIds), $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());
                $templateMgr->assign('articles', $iterator);
                $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
                break;

            case 'import':
                AppLocale::requireComponents(LOCALE_COMPONENT_OJS_EDITOR, LOCALE_COMPONENT_OJS_AUTHOR);
                import('classes.file.TemporaryFileManager');
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $sectionDao = DAORegistry::getDAO('SectionDAO');
                $user = $request->getUser();
                $temporaryFileManager = new TemporaryFileManager();

                if (($existingFileId = $request->getUserVar('temporaryFileId'))) {
                    // The user has just entered more context. Fetch an existing file.
                    $temporaryFile = $temporaryFileManager->getFile($existingFileId, $user->getId());
                } else {
                    $temporaryFile = $temporaryFileManager->handleUpload('importFile', $user->getId());
                }

                $context = [
                    'journal' => $journal,
                    'user' => $user
                ];

                if (($sectionId = $request->getUserVar('sectionId'))) {
                    $context['section'] = $sectionDao->getSection($sectionId);
                }

                if (($issueId = $request->getUserVar('issueId'))) {
                    $context['issue'] = $issueDao->getIssueById($issueId, $journal->getId());
                }

                if (!$temporaryFile) {
                    $templateMgr->assign('error', 'plugins.importexport.native.error.uploadFailed');
                    $templateMgr->display($this->getTemplatePath() . 'importError.tpl');
                    return;
                }

                $doc = $this->getDocument($temporaryFile->getFilePath());

                if (substr($this->getRootNodeName($doc), 0, 7) === 'article') {
                    // Ensure the user has supplied enough valid information to
                    // import articles within an appropriate context. If not,
                    // prompt them for the.
                    if (!isset($context['issue']) || !isset($context['section'])) {
                        $issues = $issueDao->getIssues($journal->getId(), Handler::getRangeInfo('issues'));
                        $templateMgr->assign('issues', $issues);
                        $templateMgr->assign('sectionOptions', ['0' => __('author.submit.selectSection')] + $sectionDao->getSectionTitles($journal->getId(), false));
                        $templateMgr->assign('temporaryFileId', $temporaryFile->getId());
                        $templateMgr->display($this->getTemplatePath() . 'articleContext.tpl');
                        return;
                    }
                }

                @set_time_limit(0);

                $errors = [];
                $issues = [];
                $articles = [];

                if ($this->handleImport($context, $doc, $errors, $issues, $articles, false)) {
                    $templateMgr->assign('issues', $issues);
                    $templateMgr->assign('articles', $articles);
                    $templateMgr->display($this->getTemplatePath() . 'importSuccess.tpl');
                } else {
                    $templateMgr->assign('errors', $errors);
                    $templateMgr->display($this->getTemplatePath() . 'importError.tpl');
                }
                break;

            default:
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
        }
    }

    /**
     * Export a single issue.
     * @param object $journal
     * @param object $issue
     * @param string|null $outputFile
     * @return bool
     */
    public function exportIssue($journal, $issue, $outputFile = null): bool {
        $this->import('NativeExportDom');
        $doc = XMLCustomWriter::createDocument('issue', NATIVE_DTD_ID, $this->getDTDUrl());
        $issueNode = NativeExportDom::generateIssueDom($doc, $journal, $issue);
        XMLCustomWriter::appendChild($doc, $issueNode);

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'wb')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"issue-" . $issue->getId() . ".xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Export a single article.
     * @param object $journal
     * @param object $issue
     * @param object $section
     * @param object $article
     * @param string|null $outputFile
     * @return bool
     */
    public function exportArticle($journal, $issue, $section, $article, $outputFile = null): bool {
        $this->import('NativeExportDom');
        $doc = XMLCustomWriter::createDocument('article', NATIVE_DTD_ID, $this->getDTDUrl());
        $articleNode = NativeExportDom::generateArticleDom($doc, $journal, $issue, $section, $article);
        XMLCustomWriter::appendChild($doc, $articleNode);

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'w')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"article-" . $article->getId() . ".xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Export multiple issues.
     * @param object $journal
     * @param array $issues
     * @param string|null $outputFile
     * @return bool
     */
    public function exportIssues($journal, array $issues, $outputFile = null): bool {
        $this->import('NativeExportDom');
        $doc = XMLCustomWriter::createDocument('issues', NATIVE_DTD_ID, $this->getDTDUrl());
        $issuesNode = XMLCustomWriter::createElement($doc, 'issues');
        XMLCustomWriter::appendChild($doc, $issuesNode);

        foreach ($issues as $issue) {
            $issueNode = NativeExportDom::generateIssueDom($doc, $journal, $issue);
            XMLCustomWriter::appendChild($issuesNode, $issueNode);
        }

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'w')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"issues.xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Export multiple articles.
     * @param array $results
     * @param string|null $outputFile
     * @return bool
     */
    public function exportArticles($results, $outputFile = null): bool {
        $this->import('NativeExportDom');
        $doc = XMLCustomWriter::createDocument('articles', NATIVE_DTD_ID, $this->getDTDUrl());
        $articlesNode = XMLCustomWriter::createElement($doc, 'articles');
        XMLCustomWriter::appendChild($doc, $articlesNode);

        foreach ($results as $result) {
            $article = $result['publishedArticle'];
            $section = $result['section'];
            $issue = $result['issue'];
            $journal = $result['journal'];
            $articleNode = NativeExportDom::generateArticleDom($doc, $journal, $issue, $section, $article);
            XMLCustomWriter::appendChild($articlesNode, $articleNode);
        }

        if (!empty($outputFile)) {
            if (($h = fopen($outputFile, 'w')) === false) return false;
            fwrite($h, XMLCustomWriter::getXML($doc));
            fclose($h);
        } else {
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=\"articles.xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Get the parsed XML document.
     * @param string $fileName
     * @return object
     */
    public function getDocument($fileName) {
        $parser = new XMLParser();
        return $parser->parse($fileName);
    }

    /**
     * Get root node name.
     * @param object $doc
     * @return string
     */
    public function getRootNodeName($doc): string {
        return $doc->name;
    }

    /**
     * Handle import of XML data.
     * @param array $context
     * @param object $doc
     * @param array $errors (Reference)
     * @param array $issues (Reference)
     * @param array $articles (Reference)
     * @param bool $isCommandLine
     * @return bool
     */
    public function handleImport($context, $doc, &$errors, &$issues, &$articles, $isCommandLine): bool {
        $errors = [];
        $issues = [];
        $articles = [];

        $user = $context['user'];
        $journal = $context['journal'];

        $rootNodeName = $this->getRootNodeName($doc);

        $this->import('NativeImportDom');

        switch ($rootNodeName) {
            case 'issues':
                return NativeImportDom::importIssues($journal, $doc->children, $issues, $errors, $user, $isCommandLine);
            case 'issue':
                $dependentItems = [];
                $issue = null;
                $result = NativeImportDom::importIssue($journal, $doc, $issue, $errors, $user, $isCommandLine, $dependentItems);
                if ($result) $issues = [$issue];
                return $result;
            case 'articles':
                $section = $context['section'];
                $issue = $context['issue'];
                return NativeImportDom::importArticles($journal, $doc->children, $issue, $section, $articles, $errors, $user, $isCommandLine);
            case 'article':
                $section = $context['section'];
                $issue = $context['issue'];
                $article = null;
                $result = NativeImportDom::importArticle($journal, $doc, $issue, $section, $article, $errors, $user, $isCommandLine);
                if ($result) $articles = [$article];
                return $result;
            default:
                $errors[] = ['plugins.importexport.native.import.error.unsupportedRoot', ['rootName' => $rootNodeName]];
                return false;
        }
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param string $scriptName
     * @param array $args Parameters to the plugin
     */
    public function executeCLI($scriptName, $args) {
        $command = array_shift($args);
        $xmlFile = array_shift($args);
        $journalPath = array_shift($args);

        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $journal = $journalDao->getJournalByPath($journalPath);

        if (!$journal) {
            if ($journalPath != '') {
                echo __('plugins.importexport.native.cliError') . "\n";
                echo __('plugins.importexport.native.error.unknownJournal', ['journalPath' => $journalPath]) . "\n\n";
            }
            $this->usage($scriptName);
            return;
        }

        $this->import('NativeImportDom');
        if ($xmlFile && NativeImportDom::isRelativePath($xmlFile)) {
            $xmlFile = PWD . '/' . $xmlFile;
        }

        switch ($command) {
            case 'import':
                $userName = array_shift($args);
                $user = $userDao->getByUsername($userName);

                if (!$user) {
                    if ($userName != '') {
                        echo __('plugins.importexport.native.cliError') . "\n";
                        echo __('plugins.importexport.native.error.unknownUser', ['userName' => $userName]) . "\n\n";
                    }
                    $this->usage($scriptName);
                    return;
                }

                $doc = $this->getDocument($xmlFile);

                $context = [
                    'user' => $user,
                    'journal' => $journal
                ];

                switch ($this->getRootNodeName($doc)) {
                    case 'article':
                    case 'articles':
                        // Determine the extra context information required
                        // for importing articles.
                        if (array_shift($args) !== 'issue_id') {
                            $this->usage($scriptName);
                            return;
                        }
                        $issueId = array_shift($args);
                        $issue = $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
                        if (!$issue) {
                            echo __('plugins.importexport.native.cliError') . "\n";
                            echo __('plugins.importexport.native.export.error.issueNotFound', ['issueId' => $issueId]) . "\n\n";
                            return;
                        }

                        $context['issue'] = $issue;

                        $sectionSwitch = array_shift($args);
                        $sectionIdentifier = array_shift($args);
                        $section = null;

                        switch ($sectionSwitch) {
                            case 'section_id':
                                $section = $sectionDao->getSection($sectionIdentifier);
                                break;
                            case 'section_name':
                                $section = $sectionDao->getSectionByTitle($sectionIdentifier, $journal->getId());
                                break;
                            case 'section_abbrev':
                                $section = $sectionDao->getSectionByAbbrev($sectionIdentifier, $journal->getId());
                                break;
                            default:
                                $this->usage($scriptName);
                                return;
                        }

                        if (!$section) {
                            echo __('plugins.importexport.native.cliError') . "\n";
                            echo __('plugins.importexport.native.export.error.sectionNotFound', ['sectionIdentifier' => $sectionIdentifier]) . "\n\n";
                            return;
                        }
                        $context['section'] = $section;
                }

                $errors = [];
                $issues = [];
                $articles = [];
                $result = $this->handleImport($context, $doc, $errors, $issues, $articles, true);
                
                if ($result) {
                    echo __('plugins.importexport.native.import.success.description') . "\n\n";
                    if (!empty($issues)) {
                        echo __('issue.issues') . ":\n";
                        foreach ($issues as $issue) {
                            echo "\t" . $issue->getIssueIdentification() . "\n";
                        }
                    }

                    if (!empty($articles)) {
                        echo __('article.articles') . ":\n";
                        foreach ($articles as $article) {
                            echo "\t" . $article->getLocalizedTitle() . "\n";
                        }
                    }
                } else {
                    $errorsTranslated = [];
                    foreach ($errors as $error) {
                        $errorsTranslated[] = __($error[0], $error[1]);
                    }
                    echo __('plugins.importexport.native.cliError') . "\n";
                    foreach ($errorsTranslated as $errorTranslated) {
                        echo "\t" . $errorTranslated . "\n";
                    }
                }
                return;

            case 'export':
                if ($xmlFile != '') {
                    $subCommand = array_shift($args);
                    switch ($subCommand) {
                        case 'article':
                            $articleId = array_shift($args);
                            $publishedArticle = $publishedArticleDao->getPublishedArticleByBestArticleId($journal->getId(), $articleId);
                            if ($publishedArticle == null) {
                                echo __('plugins.importexport.native.cliError') . "\n";
                                echo __('plugins.importexport.native.export.error.articleNotFound', ['articleId' => $articleId]) . "\n\n";
                                return;
                            }
                            $issue = $issueDao->getIssueById($publishedArticle->getIssueId(), $journal->getId());
                            $section = $sectionDao->getSection($publishedArticle->getSectionId());

                            if (!$this->exportArticle($journal, $issue, $section, $publishedArticle, $xmlFile)) {
                                echo __('plugins.importexport.native.cliError') . "\n";
                                echo __('plugins.importexport.native.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                            }
                            return;
                        case 'articles':
                            $results = ArticleSearch::formatResults($args);
                            if (!$this->exportArticles($results, $xmlFile)) {
                                echo __('plugins.importexport.native.cliError') . "\n";
                                echo __('plugins.importexport.native.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                            }
                            return;
                        case 'issue':
                            $issueId = array_shift($args);
                            $issue = $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
                            if ($issue == null) {
                                echo __('plugins.importexport.native.cliError') . "\n";
                                echo __('plugins.importexport.native.export.error.issueNotFound', ['issueId' => $issueId]) . "\n\n";
                                return;
                            }
                            if (!$this->exportIssue($journal, $issue, $xmlFile)) {
                                echo __('plugins.importexport.native.cliError') . "\n";
                                echo __('plugins.importexport.native.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                            }
                            return;
                        case 'issues':
                            $issues = [];
                            while (($issueId = array_shift($args)) !== null) {
                                $issue = $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
                                if ($issue == null) {
                                    echo __('plugins.importexport.native.cliError') . "\n";
                                    echo __('plugins.importexport.native.export.error.issueNotFound', ['issueId' => $issueId]) . "\n\n";
                                    return;
                                }
                                $issues[] = $issue;
                            }
                            if (!$this->exportIssues($journal, $issues, $xmlFile)) {
                                echo __('plugins.importexport.native.cliError') . "\n";
                                echo __('plugins.importexport.native.export.error.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                            }
                            return;
                    }
                }
                break;
        }
        $this->usage($scriptName);
    }

    /**
     * Display the command-line usage information
     * @param string $scriptName
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.native.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }
}

?>