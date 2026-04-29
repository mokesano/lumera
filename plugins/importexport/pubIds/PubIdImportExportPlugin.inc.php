<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/pubIds/PubIdImportExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubIdImportExportPlugin
 * @ingroup plugins_importexport_pubIds
 *
 * @brief Public identifier import/export plugin
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

define('PID_DTD_URL', 'http://pkp.sfu.ca/ojs/dtds/2.3/pubIds.dtd');
define('PID_DTD_ID', '-//PKP//OJS PubIds XML//EN');

class PubIdImportExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PubIdImportExportPlugin() {
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
     * Register the plugin.
     * @see PKPPlugin::register()
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
     * @see ImportExportPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'PubIdImportExportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see ImportExportPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.pubIds.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see ImportExportPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.pubIds.description');
    }

    /**
     * Get the path to the templates.
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Handle display requests.
     * @see ImportExportPlugin::display()
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
                $this->exportPubIdsForIssues($journal, $issues);
                break;
            case 'exportIssue':
                $issueId = array_shift($args);
                $issue = $issueDao->getIssueById($issueId, $journal->getId());
                if (!$issue) $request->redirect();
                $issues = [$issue];
                $this->exportPubIdsForIssues($journal, $issues);
                break;
            case 'selectIssue':
                // Display a list of issues for export
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents([LOCALE_COMPONENT_OJS_EDITOR]);
                $issues = $issueDao->getIssues($journal->getId(), Handler::getRangeInfo('issues'));
                $templateMgr->assign('issues', $issues);
                $templateMgr->display($this->getTemplatePath() . 'selectIssue.tpl');
                break;
            case 'import':
                import('classes.file.TemporaryFileManager');
                $user = $request->getUser();
                $temporaryFileManager = new TemporaryFileManager();

                if (($existingFileId = $request->getUserVar('temporaryFileId'))) {
                    // The user has just entered more context. Fetch an existing file.
                    $temporaryFile = $temporaryFileManager->getFile($existingFileId, $user->getId());
                } else {
                    $temporaryFile = $temporaryFileManager->handleUpload('importFile', $user->getId());
                }
                
                if (!$temporaryFile) {
                    $templateMgr->assign('error', 'plugins.importexport.pubIds.import.error.uploadFailed');
                    $templateMgr->display($this->getTemplatePath() . 'importResults.tpl');
                    return;
                }

                $context = ['journal' => $journal];

                $doc = $this->getDocument($temporaryFile->getFilePath());
                
                @set_time_limit(0);
                
                $errors = [];
                $pubIds = [];
                $this->handleImport($context, $doc, $errors, $pubIds, false);
                
                $templateMgr->assign('errors', $errors);
                $templateMgr->assign('pubIds', $pubIds);
                $templateMgr->display($this->getTemplatePath() . 'importResults.tpl');
                break;
            default:
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'importExportIndex.tpl');
        }
    }

    /**
     * Export public identifiers of one or more issues.
     * @param object $journal
     * @param array $issues
     * @param string|null $outputFile xml file containing the exported public identifiers
     * @return bool
     */
    public function exportPubIdsForIssues($journal, $issues, $outputFile = null): bool {
        $doc = XMLCustomWriter::createDocument('pubIds', PID_DTD_URL, PID_DTD_URL);
        $pubIdsNode = XMLCustomWriter::createElement($doc, 'pubIds');
        XMLCustomWriter::appendChild($doc, $pubIdsNode);

        foreach ($issues as $issue) {
            $this->generatePubId($doc, $pubIdsNode, $issue, $journal->getId());

            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            foreach ($publishedArticleDao->getPublishedArticles($issue->getId()) as $publishedArticle) {
                $this->generatePubId($doc, $pubIdsNode, $publishedArticle, $journal->getId());

                $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                foreach ($articleGalleyDao->getGalleysByArticle($publishedArticle->getId()) as $articleGalley) {
                    $this->generatePubId($doc, $pubIdsNode, $articleGalley, $journal->getId());
                }

                $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
                foreach ($suppFileDao->getSuppFilesByArticle($publishedArticle->getId()) as $suppFile) {
                    $this->generatePubId($doc, $pubIdsNode, $suppFile, $journal->getId());
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
            header("Content-Disposition: attachment; filename=\"pubIds.xml\"");
            XMLCustomWriter::printXML($doc);
        }
        return true;
    }

    /**
     * Import public identifier.
     * @param object $journal
     * @param XMLNode $pubIdNode
     * @param array|null $pubId Array describing the successfully imported public identifier (passed by ref)
     * @param array $errors Array (passed by ref)
     * @param bool $isCommandLine
     */
    public function importPubId($journal, $pubIdNode, &$pubId, &$errors, $isCommandLine): void {
        $errors = [];
        $pubId = null;

        $pubIdValue = $pubIdNode->getValue();
        $pubIdType = $pubIdNode->getAttribute('pubIdType');
        $pubObjectType = $pubIdNode->getAttribute('pubObjectType');
        $pubObjectId = $pubIdNode->getAttribute('pubObjectId');

        $pubIdPluginFound = false;
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journal->getId());
        
        if (is_array($pubIdPlugins)) {
            foreach ($pubIdPlugins as $pubIdPlugin) {
                if ($pubIdPlugin->getPubIdType() == $pubIdType) {
                    $dao = $pubIdPlugin->getDAO($pubObjectType);
                    $pubObject = null;
                    switch ($pubObjectType) {
                        case 'Issue':
                            $pubObject = $dao->getIssueById($pubObjectId, $journal->getId());
                            break;
                        case 'Article':
                            $pubObject = $dao->getArticle($pubObjectId, $journal->getId());
                            break;
                        case 'Galley':
                            $pubObject = $dao->getGalley($pubObjectId);
                            break;
                        case 'SuppFile':
                            $pubObject = $dao->getSuppFile($pubObjectId);
                            break;
                        default:
                            $errors[] = ['plugins.importexport.pubIds.import.error.unknownObjectType', ['pubObjectType' => $pubObjectType, 'pubId' => $pubIdValue]];
                            break;
                    }

                    if ($pubObject) {
                        $storedPubId = $pubObject->getStoredPubId($pubIdType);
                        if (!$storedPubId) {
                            if (!$pubIdPlugin->checkDuplicate($pubIdValue, $pubObject, $journal->getId())) {
                                $errors[] = ['plugins.importexport.pubIds.import.error.duplicatePubId', ['pubId' => $pubIdValue]];
                            } else {
                                $pubIdPlugin->setStoredPubId($pubObject, $pubObjectType, $pubIdValue);
                                $pubId = ['pubObjectType' => $pubObjectType, 'pubObjectId' => $pubObjectId, 'value' => $pubIdValue];
                            }
                        } else {
                            $errors[] = ['plugins.importexport.pubIds.import.error.pubIdExists', ['pubIdType' => $pubIdType, 'pubObjectType' => $pubObjectType, 'pubObjectId' => $pubObjectId]];
                        }
                    } else {
                        $errors[] = ['plugins.importexport.pubIds.import.error.unknownObject', ['pubObjectType' => $pubObjectType, 'pubObjectId' => $pubObjectId, 'pubId' => $pubIdValue]];
                    }
                    $pubIdPluginFound = true;
                    break;
                }
            }
        }
        
        if (!$pubIdPluginFound) {
            $errors[] = ['plugins.importexport.native.import.error.unknownPubId', ['pubIdType' => $pubIdType]];
        }
    }

    /**
     * Import public identifiers.
     * @param object $journal
     * @param array $pubIdNodes all pubId nodes of the xml document
     * @param array $pubIds successfully imported pubIds (passed by ref)
     * @param array $errors (passed by ref)
     * @param bool $isCommandLine
     */
    public function importPubIds($journal, $pubIdNodes, &$pubIds, &$errors, $isCommandLine): void {
        $errors = [];
        $pubIds = [];
        foreach ($pubIdNodes as $pubIdNode) {
            $pubId = null;
            $pubIdErrors = [];
            $this->importPubId($journal, $pubIdNode, $pubId, $pubIdErrors, $isCommandLine);
            if ($pubId) {
                $pubIds[] = $pubId;
            }
            $errors = array_merge($errors, $pubIdErrors);
        }
    }

    /**
     * Get the tree structure of the xml document.
     * @param string $fileName full path to the XML file
     * @return object tree structure representing the document
     */
    public function getDocument($fileName) {
        $parser = new XMLParser();
        $returner = $parser->parse($fileName);
        return $returner;
    }

    /**
     * Get the name of the root node of the xml document.
     * @param object $doc
     * @return string
     */
    public function getRootNodeName($doc): string {
        return $doc->name;
    }

    /**
     * Handle import of public identifiers described in the xml document.
     * @param array $context
     * @param object $doc
     * @param array $errors (passed by ref)
     * @param array $pubIds successfully imported pubIds (passed by ref)
     * @param bool $isCommandLine
     * @return void
     */
    public function handleImport($context, $doc, &$errors, &$pubIds, $isCommandLine): void {
        $errors = [];
        $pubIds = [];

        $journal = $context['journal'];

        $rootNodeName = $this->getRootNodeName($doc);

        switch ($rootNodeName) {
            case 'pubIds':
                $this->importPubIds($journal, $doc->children, $pubIds, $errors, $isCommandLine);
                break;
            default:
                $errors[] = ['plugins.importexport.pubIds.import.error.unsupportedRoot', ['rootName' => $rootNodeName]];
                break;
        }
    }

    /**
     * Add ID-nodes to the given node.
     * @param DOMDocument $doc
     * @param DOMElement $node
     * @param object $pubObject
     * @param int $journalId
     * @return void
     */
    public function generatePubId($doc, $node, $pubObject, $journalId): void {
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journalId);
        if (is_array($pubIdPlugins)) {
            foreach ($pubIdPlugins as $pubIdPlugin) {
                $pubIdType = $pubIdPlugin->getPubIdType();
                $pubId = $pubObject->getStoredPubId($pubIdType);
                if ($pubId) {
                    $pubObjectType = $pubIdPlugin->getPubObjectType($pubObject);

                    $pubIdNode = XMLCustomWriter::createChildWithText($doc, $node, 'pubId', $pubId);

                    XMLCustomWriter::setAttribute($pubIdNode, 'pubIdType', $pubIdType);
                    XMLCustomWriter::setAttribute($pubIdNode, 'pubObjectType', $pubObjectType);
                    XMLCustomWriter::setAttribute($pubIdNode, 'pubObjectId', (string) $pubObject->getId());
                }
            }
        }
    }

    /**
     * Check if this is a relative path to the xml docuemnt
     * that describes public identifiers to be imported.
     * @param string $url path to the xml file
     * @return bool
     */
    public function isRelativePath($url): bool {
        // FIXME This is not very comprehensive, but will work for now.
        if ($this->isAllowedMethod($url)) return false;
        if ($url[0] == '/') return false;
        return true;
    }

    /**
     * Check allowed methods
     * @param string $url
     * @return bool
     */
    public function isAllowedMethod($url): bool {
        $allowedPrefixes = [
            'http://',
            'ftp://',
            'https://',
            'ftps://'
        ];
        foreach ($allowedPrefixes as $prefix) {
            if (substr($url, 0, strlen($prefix)) === $prefix) return true;
        }
        return false;
    }

    /**
     * Execute the import/export plugin from command line.
     * @see ImportExportPlugin::executeCLI()
     * @param string $scriptName
     * @param array $args
     * @return void
     */
    public function executeCLI($scriptName, $args): void {
        $command = array_shift($args);
        $xmlFile = array_shift($args);
        $journalPath = array_shift($args);

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $journal = $journalDao->getJournalByPath($journalPath);

        if (!$journal) {
            if ($journalPath != '') {
                echo __('plugins.importexport.pubIds.cliError') . "\n";
                echo __('plugins.importexport.pubIds.cliError.unknownJournal', ['journalPath' => $journalPath]) . "\n\n";
            }
            $this->usage($scriptName);
            return;
        }

        if ($xmlFile && $this->isRelativePath($xmlFile)) {
            $xmlFile = PWD . '/' . $xmlFile;
        }

        switch ($command) {
            case 'import':
                $userName = array_shift($args);
                $user = $userDao->getByUsername($userName);

                if (!$user) {
                    if ($userName != '') {
                        echo __('plugins.importexport.pubIds.cliError') . "\n";
                        echo __('plugins.importexport.pubIds.cliError.unknownUser', ['userName' => $userName]) . "\n\n";
                    }
                    $this->usage($scriptName);
                    return;
                }

                $doc = $this->getDocument($xmlFile);

                $context = [
                    'user' => $user,
                    'journal' => $journal
                ];

                $errors = [];
                $pubIds = [];
                $this->handleImport($context, $doc, $errors, $pubIds, true);
                
                if (!empty($pubIds)) {
                    echo __('plugins.importexport.pubIds.import.success.description') . "\n";
                    foreach ($pubIds as $pubId) {
                        echo "\t" . $pubId['value'] . "\n";
                    }
                }

                if (!empty($errors)) {
                    echo __('plugins.importexport.pubIds.cliError') . "\n";
                    $errorsTranslated = [];
                    foreach ($errors as $error) {
                        $errorsTranslated[] = __($error[0], $error[1]);
                    }
                    foreach ($errorsTranslated as $errorTranslated) {
                        echo "\t" . $errorTranslated . "\n";
                    }
                }
                break;

            case 'export':
                if ($xmlFile != '') {
                    $subCommand = array_shift($args);
                    switch ($subCommand) {
                        case 'issue':
                            $issueId = array_shift($args);
                            $issue = $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
                            if ($issue == null) {
                                echo __('plugins.importexport.pubIds.cliError') . "\n";
                                echo __('plugins.importexport.pubIds.cliError.issueNotFound', ['issueId' => $issueId]) . "\n\n";
                                return;
                            }
                            $issues = [$issue];
                            if (!$this->exportPubIdsForIssues($journal, $issues, $xmlFile)) {
                                echo __('plugins.importexport.pubIds.cliError') . "\n";
                                echo __('plugins.importexport.pubIds.cliError.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                            }
                            return;
                        case 'issues':
                            $issues = [];
                            while (($issueId = array_shift($args)) !== null) {
                                $issue = $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
                                if ($issue == null) {
                                    echo __('plugins.importexport.pubIds.cliError') . "\n";
                                    echo __('plugins.importexport.pubIds.cliError.issueNotFound', ['issueId' => $issueId]) . "\n\n";
                                    return;
                                }
                                $issues[] = $issue;
                            }
                            if (!$this->exportPubIdsForIssues($journal, $issues, $xmlFile)) {
                                echo __('plugins.importexport.pubIds.cliError') . "\n";
                                echo __('plugins.importexport.pubIds.cliError.couldNotWrite', ['fileName' => $xmlFile]) . "\n\n";
                            }
                            return;
                    }
                }
                break;
        }
        $this->usage($scriptName);
    }

    /**
     * Display command line usage information.
     * @see ImportExportPlugin::usage()
     * @param string $scriptName
     * @return void
     */
    public function usage($scriptName): void {
        echo __('plugins.importexport.pubIds.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }
}

?>