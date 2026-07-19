<?php
declare(strict_types=1);

/**
 * @file classes/sword/OJSSwordDeposit.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSSwordDeposit
 * @ingroup sword
 *
 * @brief Class providing a SWORD deposit wrapper for articles.
 */

require_once('./lib/pkp/lib/swordappv2/swordappclient.php');
require_once('./lib/pkp/lib/swordappv2/swordappentry.php');
require_once('./lib/pkp/lib/swordappv2/packager_mets_swap.php');

class OJSSwordDeposit {

    /** @var \PackagerMetsSwap $package deposit METS package */
    public $package;

    /** @var string Complete path and directory name to use for package creation files */
    public $outPath;

    /** @var Journal */
    public $journal;

    /** @var Section */
    public $section;

    /** @var Issue */
    public $issue;

    /** @var Article|PublishedArticle */
    public $article;

    /**
     * Constructor.
     * @param object $article Article|PublishedArticle
     */
    public function __construct($article) {
        $this->outPath = tempnam('/tmp', 'sword');

        if (file_exists($this->outPath)) {
            unlink($this->outPath);
        }
        
        mkdir($this->outPath);
        mkdir($this->outPath . '/files');

        // Create a package
        $this->package = new PackagerMetsSwap(
            $this->outPath,
            'files',
            $this->outPath,
            'deposit.zip'
        );

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $this->journal = $journalDao->getById($article->getJournalId());

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $this->section = $sectionDao->getSection($article->getSectionId());

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($article->getId());

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        if ($publishedArticle) {
            $this->issue = $issueDao->getIssueById($publishedArticle->getIssueId());
        }

        $this->article = $article;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param object $article Article|PublishedArticle
     */
    public function OJSSwordDeposit($article) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor OJSSwordDeposit(). Please refactor to use __construct().",
            E_USER_DEPRECATED
        );
        $this->__construct($article);
    }

    /**
     * Register the article's metadata with the SWORD deposit.
     */
    public function setMetadata() {
        $this->package->setCustodian($this->journal->getSetting('contactName'));
        $this->package->setTitle(html_entity_decode($this->article->getTitle($this->journal->getPrimaryLocale()), ENT_QUOTES, 'UTF-8'));
        $this->package->setAbstract(html_entity_decode(strip_tags($this->article->getAbstract($this->journal->getPrimaryLocale())), ENT_QUOTES, 'UTF-8'));
        $this->package->setType($this->section->getIdentifyType($this->journal->getPrimaryLocale()));

        // The article can be published or not. Support either.
        if ($this->article instanceof PublishedArticle) {
            $doi = $this->article->getPubId('doi');
            if ($doi !== null) {
                $this->package->setIdentifier($doi);
            }
        }

        foreach ($this->article->getAuthors() as $author) {
            $creator = $author->getFullName(true);
            $affiliation = $author->getAffiliation($this->journal->getPrimaryLocale());
            if (!empty($affiliation)) {
                $creator .= "; $affiliation";
            }
            $this->package->addCreator($creator);
        }

        // The article can be published or not. Support either.
        if ($this->article instanceof PublishedArticle) {
            $plugin = PluginRegistry::loadPlugin('citationFormats', 'bibtex');
            if ($plugin && method_exists($plugin, 'fetchCitation')) {
                $citationText = $plugin->fetchCitation($this->article, $this->issue, $this->journal);
                if ($citationText) {
                    $this->package->setCitation(html_entity_decode(strip_tags($citationText), ENT_QUOTES, 'UTF-8'));
                }
            }
        }
    }

    /**
     * Add a file to a package. Used internally.
     * @param mixed $file
     */
    protected function _addFile($file) {
        $targetFilename = $this->outPath . '/files/' . $file->getFilename();
        copy($file->getFilePath(), $targetFilename);
        $this->package->addFile($file->getFilename(), $file->getFileType());
    }

    /**
     * Add all article galleys to the deposit package.
     */
    public function addGalleys() {
        foreach ($this->article->getGalleys() as $galley) {
            $this->_addFile($galley);
        }
    }

    /**
     * Add the single most recent editorial file to the deposit package.
     * @return boolean true iff a file was successfully added to the package
     */
    public function addEditorial() {
        foreach (['SIGNOFF_LAYOUT', 'SIGNOFF_COPYEDITING_FINAL', 'SIGNOFF_COPYEDITING_AUTHOR', 'SIGNOFF_COPYEDITING_INITIAL'] as $signoffName) {
            $file = $this->article->getFileBySignoffType($signoffName);
            if ($file) {
                $this->_addFile($file);
                return true;
            }
        }

        /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $sectionEditorSubmission = $sectionEditorSubmissionDao->getSectionEditorSubmission($this->article->getId());
        $file = $sectionEditorSubmission->getEditorFile();
        if ($file) {
            $this->_addFile($file);
            return true;
        }

        // Try the Review Version.
        $file = $sectionEditorSubmission->getReviewFile();
        if ($file) {
            $this->_addFile($file);
            return true;
        }

        return false;
    }

    /**
     * Build the package.
     */
    public function createPackage() {
        return $this->package->create();
    }

    /**
     * Deposit the package.
     * @param string $url string SWORD deposit URL
     * @param string $username string SWORD deposit username
     * @param string $password string SWORD deposit password
     */
    public function deposit($url, $username, $password) {
        $client = new SWORDAPPClient();
        $response = $client->deposit(
            $url, $username, $password,
            '',
            $this->outPath . '/deposit.zip',
            'http://purl.org/net/sword/package/METSDSpaceSIP',
            'application/zip', false
        );
        return $response;
    }

    /**
     * Clean up after a deposit, i.e. removing all created files.
     */
    public function cleanup() {
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();

        $fileManager->rmtree($this->outPath);
    }

}
?>