<?php
declare(strict_types=1);

/**
 * @file plugins/gateways/metsGateway/MetsGatewayPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class METSGatewayPlugin
 * @ingroup plugins
 *
 * @brief A plugin to allow exposure of Journals in METS format for web service access.
 */

import('classes.plugins.GatewayPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

class METSGatewayPlugin extends GatewayPlugin {
    
    /** @var int|null */
    protected $journalId;

    /**
     * Called as a plugin is registered to the registry
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();

        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within its category.
     * @return string
     */
    public function getName(): string {
        return 'METSGatewayPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.gateways.metsGateway.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.gateways.metsGateway.description');
    }

    /**
     * Get the management verbs of this plugin.
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs();
        if (!$this->getEnabled()) return $verbs;
        $verbs[] = [
            'settings', 
            __('plugins.gateways.metsGateway.settings')
        ];
        
        return $verbs;
    }

    /**
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param Request|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = NULL, ?array &$messageParams = NULL, $request = NULL): bool {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (parent::manage($verb, $args, $message, $messageParams, $request)) return true;
        
        if (!$this->getEnabled()) return false;
        
        switch ($verb) {
            case 'settings':
                $journal = $request->getJournal();
                if (!$journal) {
                    header('HTTP/1.0 404 Not Found');
                    fatalError('Journal context not found.', 404);
                }

                $journalId = (int) $journal->getId();

                $this->import('SettingsForm');
                $form = new SettingsForm($this, $journalId);

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute($request);
                        $request->redirect(null, 'manager', 'plugin', ['gateways', $this->getName(), 'settings']);
                    } else {
                        $form->display();
                    }
                } else {
                    $form->initData();
                    $form->display();
                }
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Handle fetch requests for this plugin.
     * @param array $args
     * @param Request|null $request
     * @return bool
     */
    public function fetch($args, $request = null) {
        // Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (!$this->getEnabled()) {
            return false;
        }

        if (empty($args)) {
            $errors = [];
            // Fallthrough to failure handling below
        } else {
            $journal = $request->getJournal();
            if (!$journal) {
                header('HTTP/1.0 404 Not Found');
                fatalError('Journal not found', 404);
            }

            $issueDao = DAORegistry::getDAO('IssueDAO'); /** @var IssueDAO $issueDao */
            $issueId = array_shift($args);
            
            if (!$issueId) {
                // Assuming Handler class is available globally or imported
                $issuesResultSet = $issueDao->getIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));
                $issues = [];

                while (!$issuesResultSet->eof()) {
                    $issue = $issuesResultSet->next();
                    $issues[] = $issue;
                }
                $this->exportIssues($journal, $issues, $request);
                return true;

            } elseif ($issueId == 'current') {
                $issues = [];
                $issues[] = $issueDao->getCurrentIssue((int) $journal->getId(), true);
                
                if (empty($issues) || !$issues[0]) {
                     header('HTTP/1.0 404 Not Found');
                     fatalError('Current issue not found', 404);
                }

                $this->exportIssues($journal, $issues, $request);
                return true;

            } else {
                $issues = [];
                $issues[] = $issueDao->getIssueById((int) $issueId, (int) $journal->getId(), true);
                
                if (empty($issues) || !$issues[0]) {
                     header('HTTP/1.0 404 Not Found');
                     fatalError('Issue not found', 404);
                }

                $this->exportIssues($journal, $issues, $request);
                return true;
            }
        }

        // Failure: Lumera Protocol v3.4: Strict Error Handling
        header("HTTP/1.0 500 Internal Server Error");
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
        
        $errorMessage = __('plugins.gateways.metsGateway.errors.errorMessage');
        fatalError($errorMessage ?: 'Unknown METS Gateway Error', 500);
        
        return false; // Should not reach here
    }

    /**
     * @param Journal $journal
     * @param array $issues
     * @param Request|null $request
     * @return bool
     */
    public function exportIssues($journal, $issues, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $this->journalId = (int) $journal->getId();

        $this->import('MetsExportDom');
        $doc = XMLCustomWriter::createDocument();
        $root = XMLCustomWriter::createElement($doc, 'METS:mets');
        XMLCustomWriter::setAttribute($root, 'xmlns:METS', 'http://www.loc.gov/METS/');
        XMLCustomWriter::setAttribute($root, 'xmlns:xlink', 'http://www.w3.org/TR/xlink');
        XMLCustomWriter::setAttribute($root, 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        XMLCustomWriter::setAttribute($root, 'PROFILE', 'Australian METS Profile 1.0');
        XMLCustomWriter::setAttribute($root, 'TYPE', 'journal');
        XMLCustomWriter::setAttribute($root, 'OBJID', 'J-'.$this->journalId);
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', 'http://www.loc.gov/METS/ http://www.loc.gov/mets/mets.xsd');
        
        $HeaderNode = MetsExportDom::createmetsHdr($doc);
        XMLCustomWriter::appendChild($root, $HeaderNode);
        
        MetsExportDom::generateJournalDmdSecDom($doc, $root, $journal);
        
        $fileSec = XMLCustomWriter::createElement($doc, 'METS:fileSec');
        $fileGrpOriginal = XMLCustomWriter::createElement($doc, 'METS:fileGrp');
        XMLCustomWriter::setAttribute($fileGrpOriginal, 'USE', 'original');
        
        $fileGrpDerivative = XMLCustomWriter::createElement($doc, 'METS:fileGrp');
        XMLCustomWriter::setAttribute($fileGrpDerivative, 'USE', 'derivative');
        
        foreach ($issues as $issue) {
            if ($issue) {
                MetsExportDom::generateIssueDmdSecDom($doc, $root, $issue, $journal);
                MetsExportDom::generateIssueFileSecDom($doc, $fileGrpOriginal, $issue);
                MetsExportDom::generateIssueHtmlGalleyFileSecDom($doc, $fileGrpDerivative, $issue);
            }
        }
        
        $amdSec = MetsExportDom::createmetsamdSec($doc, $root, $journal);
        XMLCustomWriter::appendChild($root, $amdSec);
        XMLCustomWriter::appendChild($fileSec, $fileGrpOriginal);
        XMLCustomWriter::appendChild($fileSec, $fileGrpDerivative);
        XMLCustomWriter::appendChild($root, $fileSec);
        
        MetsExportDom::generateStructMap($doc, $root, $journal, $issues);
        XMLCustomWriter::appendChild($doc, $root);
        
        header("Content-Type: application/xml");
        XMLCustomWriter::printXML($doc);
        return true;
    }

}
?>