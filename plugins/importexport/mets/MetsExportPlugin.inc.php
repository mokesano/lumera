<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/mets/METSExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class METSExportPlugin
 * @ingroup plugins_importexport_mets
 *
 * @brief METS/MODS XML metadata export plugin
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

class METSExportPlugin extends ImportExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function METSExportPlugin() {
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
     * @param string $category
     * @param string $path
     * @return bool
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
        return 'METSExportPlugin';
    }

    /**
     * Get the display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.METSExport.displayName');
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.METSExport.description');
    }

    /**
     * Display the plugin.
     * @param array $args
     * @param Request $request
     */
    public function display($args, $request): void {
        $templateMgr = TemplateManager::getManager($request);
        parent::display($args, $request);
        
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $journal = $request->getJournal();

        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }
        
        $command = array_shift($args);

        switch ($command) {
            case 'exportIssues':
                $issueIds = (array) $request->getUserVar('issueId');
                $issues = [];
                foreach ($issueIds as $issueId) {
                    $issue = $issueDao->getIssueById((int) $issueId);
                    if (!$issue) {
                        $request->redirect(null, null, 'index');
                        return;
                    }
                    $issues[] = $issue;
                }
                $this->exportIssues($journal, $issues);
                break;
                
            case 'exportIssue':
                $issueId = array_shift($args);
                $issue = $issueDao->getIssueById((int) $issueId);
                if (!$issue) {
                    $request->redirect(null, null, 'index');
                    return;
                }
                $this->exportIssues($journal, [$issue]);
                break;
                
            case 'issues':
                // Display a list of issues for export
                $this->setBreadcrumbs([], true);
                AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
                $issues = $issueDao->getIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

                /** @var SiteDAO $siteDao */
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $site = $siteDao->getSite();

                $organization = $site ? $site->getTitle($site->getPrimaryLocale()) : '';

                $templateMgr->assign('issues', $issues);
                $templateMgr->assign('organization', $organization);
                $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
                break;
                
            default:
                $this->setBreadcrumbs();
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
        }
    }

    /**
     * Export issues to METS XML
     * @param Journal $journal
     * @param array $issues
     * @return bool
     */
    public function exportIssues($journal, $issues): bool {
        $this->import('MetsExportDom');
        $doc = XMLCustomWriter::createDocument();
        $root = XMLCustomWriter::createElement($doc, 'METS:mets');
        XMLCustomWriter::setAttribute($root, 'xmlns:METS', 'http://www.loc.gov/METS/');
        XMLCustomWriter::setAttribute($root, 'xmlns:xlink', 'http://www.w3.org/TR/xlink');
        XMLCustomWriter::setAttribute($root, 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        XMLCustomWriter::setAttribute($root, 'PROFILE', 'Australian METS Profile 1.0');
        XMLCustomWriter::setAttribute($root, 'TYPE', 'journal');

        XMLCustomWriter::setAttribute($root, 'OBJID', 'J-' . (int) $journal->getId());
        XMLCustomWriter::setAttribute($root, 'xsi:schemaLocation', 'http://www.loc.gov/METS/ http://www.loc.gov/mets/mets.xsd');
        
        $headerNode = MetsExportDom::createmetsHdr($doc);
        XMLCustomWriter::appendChild($root, $headerNode);
        
        MetsExportDom::generateJournalDmdSecDom($doc, $root, $journal);
        
        $fileSec = XMLCustomWriter::createElement($doc, 'METS:fileSec');
        $fileGrpOriginal = XMLCustomWriter::createElement($doc, 'METS:fileGrp');
        XMLCustomWriter::setAttribute($fileGrpOriginal, 'USE', 'original');
        $fileGrpDerivative = XMLCustomWriter::createElement($doc, 'METS:fileGrp');
        XMLCustomWriter::setAttribute($fileGrpDerivative, 'USE', 'derivative');
        
        foreach ($issues as $issue) {
            MetsExportDom::generateIssueDmdSecDom($doc, $root, $issue, $journal);
            MetsExportDom::generateIssueFileSecDom($doc, $fileGrpOriginal, $issue, $journal);
            MetsExportDom::generateIssueHtmlGalleyFileSecDom($doc, $fileGrpDerivative, $issue, $journal);
        }
        
        $amdSec = MetsExportDom::createmetsamdSec($doc, $root, $journal);
        XMLCustomWriter::appendChild($root, $amdSec);
        XMLCustomWriter::appendChild($fileSec, $fileGrpOriginal);
        XMLCustomWriter::appendChild($fileSec, $fileGrpDerivative);
        XMLCustomWriter::appendChild($root, $fileSec);
        
        MetsExportDom::generateStructMap($doc, $root, $journal, $issues);
        
        XMLCustomWriter::appendChild($doc, $root);
        
        header("Content-Type: application/xml");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=\"" . $journal->getPath() . "-mets.xml\"");
        XMLCustomWriter::printXML($doc);
        
        return true;
    }

}
?>