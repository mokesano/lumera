<?php
declare(strict_types=1);

/**
 * @file plugins/pubIds/doi/DOIPubIdPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIPubIdPlugin
 * @ingroup plugins_pubIds_doi
 *
 * @brief DOI plugin class
 */

import('classes.plugins.PubIdPlugin');

class DOIPubIdPlugin extends PubIdPlugin {

    //
    // Implement template methods from PKPPlugin.
    //
    /**
     * @see PubIdPlugin::register()
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get a name of the plugin.
     * @see PKPPlugin::getName()
     */
    public function getName(): string {
        return 'DOIPubIdPlugin';
    }

    /**
     * Get a Display Name of the plugin.
     * @see PKPPlugin::getDisplayName()
     */
    public function getDisplayName(): string {
        return __('plugins.pubIds.doi.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see PKPPlugin::getDescription()
     */
    public function getDescription(): string {
        return __('plugins.pubIds.doi.description');
    }

    /**
     * Get a template of the plugin.
     * @see PKPPlugin::getTemplatePath()
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath($inCore) . 'templates/';
    }

    //
    // Implement template methods from PubIdPlugin.
    //
    /**
     * @see PubIdPlugin::getPubId()
     */
    public function getPubId($pubObject, $preview = false) {
        $doi = null;
        if (!$this->isExcluded($pubObject)) {
            // Determine the type of the publishing object.
            $pubObjectType = $this->getPubObjectType($pubObject);

            // Initialize variables for publication objects.
            $issue = ($pubObjectType === 'Issue' ? $pubObject : null);
            $article = ($pubObjectType === 'Article' ? $pubObject : null);
            $galley = ($pubObjectType === 'Galley' ? $pubObject : null);
            $suppFile = ($pubObjectType === 'SuppFile' ? $pubObject : null);

            // Get the journal id of the object.
            if (in_array($pubObjectType, ['Issue', 'Article'])) {
                $journalId = $pubObject->getJournalId();
            } else {
                // Retrieve the published article.
                assert($pubObject instanceof ArticleFile);
                $articleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $articleDao PublishedArticleDAO */
                $article = $articleDao->getPublishedArticleByArticleId($pubObject->getArticleId(), null, true);
                if (!$article) return null;

                // Now we can identify the journal.
                $journalId = $article->getJournalId();
            }

            $journal = $this->getJournal($journalId);
            if (!$journal) return null;
            $journalId = $journal->getId();

            // Check whether DOIs are enabled for the given object type.
            $doiEnabled = ($this->getSetting($journalId, "enable{$pubObjectType}Doi") == '1');
            if (!$doiEnabled) return null;

            // If we already have an assigned DOI, use it.
            $storedDOI = $pubObject->getStoredPubId('doi');
            if ($storedDOI) return $storedDOI;

            // Retrieve the issue.
            if (!($pubObject instanceof Issue)) {
                assert($article !== null);
                $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
                $issue = $issueDao->getIssueByArticleId($article->getId(), $journal->getId(), true);
            }
            if ($issue && $journalId != $issue->getJournalId()) return null;

            // Retrieve the DOI prefix.
            $doiPrefix = $this->getSetting($journalId, 'doiPrefix');
            if (empty($doiPrefix)) return null;

            // Generate the DOI suffix.
            $doiSuffixGenerationStrategy = $this->getSetting($journalId, 'doiSuffix');
            switch ($doiSuffixGenerationStrategy) {
                case 'publisherId':
                    switch($pubObjectType) {
                        case 'Issue':
                            $doiSuffix = (string) $pubObject->getBestIssueId($journal);
                            break;
                        case 'Article':
                            $doiSuffix = (string) $pubObject->getBestArticleId($journal);
                            break;
                        case 'Galley':
                            $doiSuffix = (string) $pubObject->getBestGalleyId($journal);
                            break;
                        case 'SuppFile':
                            $doiSuffix = (string) $pubObject->getBestSuppFileId($journal);
                            break;
                        default:
                            assert(false);
                            $doiSuffix = ''; // Fallback to prevent undefined variable
                    }

                    // When the suffix equals the object's ID then
                    // require an object-specific prefix to be sure that
                    // the suffix is unique.
                    if ($pubObjectType != 'Article' && $doiSuffix === (string) $pubObject->getId()) {
                        // FIX: $pubObjectType{0} is deprecated in PHP 8. Use []
                        $doiSuffix = strtolower_codesafe($pubObjectType[0]) . $doiSuffix;
                    }
                    break;

                case 'customId':
                    $doiSuffix = $pubObject->getData('doiSuffix');
                    break;

                case 'pattern':
                    $doiSuffix = $this->getSetting($journalId, "doi{$pubObjectType}SuffixPattern");

                    // %j - journal initials
                    $doiSuffix = PKPString::regexp_replace('/%j/', PKPString::strtolower($journal->getLocalizedSetting('initials', $journal->getPrimaryLocale())), $doiSuffix);

                    // %x - custom identifier
                    if ($pubObject->getStoredPubId('publisher-id')) {
                        $doiSuffix = PKPString::regexp_replace('/%x/', $pubObject->getStoredPubId('publisher-id'), $doiSuffix);
                    }
                    if ($issue) {
                        // %v - volume number
                        $doiSuffix = PKPString::regexp_replace('/%v/', $issue->getVolume(), $doiSuffix);
                        // %i - issue number
                        $doiSuffix = PKPString::regexp_replace('/%i/', $issue->getNumber(), $doiSuffix);
                        // %Y - year
                        $doiSuffix = PKPString::regexp_replace('/%Y/', $issue->getYear(), $doiSuffix);
                    }
                    if ($article) {
                        // %a - article id
                        $doiSuffix = PKPString::regexp_replace('/%a/', $article->getId(), $doiSuffix);
                        // %p - page number
                        if ($article->getPages()) {
                            $doiSuffix = PKPString::regexp_replace('/%p/', $article->getPages(), $doiSuffix);
                        }
                    }
                    if ($galley) {
                        // %g - galley id
                        $doiSuffix = PKPString::regexp_replace('/%g/', $galley->getId(), $doiSuffix);
                    }
                    if ($suppFile) {
                        // %s - supp file id
                        $doiSuffix = PKPString::regexp_replace('/%s/', $suppFile->getId(), $doiSuffix);
                    }
                    break;

                default:
                    $doiSuffix = PKPString::strtolower($journal->getLocalizedSetting('initials', $journal->getPrimaryLocale()));

                    if ($issue) {
                        $doiSuffix .= '.v' . $issue->getVolume() . 'i' . $issue->getNumber();
                    } else {
                        $doiSuffix .= '.v%vi%i';
                    }

                    if ($article) {
                         $doiSuffix .= '.' . $article->getId();
                    }

                    if ($galley) {
                        $doiSuffix .= '.g' . $galley->getId();
                    }

                    if ($suppFile) {
                        $doiSuffix .= '.s' . $suppFile->getId();
                    }
            }
            if (empty($doiSuffix)) return null;

            // Join prefix and suffix.
            $doi = $doiPrefix . '/' . $doiSuffix;

            if (!$preview) {
                // Save the generated DOI.
                $this->setStoredPubId($pubObject, $pubObjectType, $doi);
            }
        }
        return $doi;
    }

    /**
     * @see PubIdPlugin::getPubIdType()
     */
    public function getPubIdType() {
        return 'doi';
    }

    /**
     * @see PubIdPlugin::getPubIdDisplayType()
     */
    public function getPubIdDisplayType() {
        return 'DOI';
    }

    /**
     * @see PubIdPlugin::getPubIdFullName()
     */
    public function getPubIdFullName() {
        return 'Digital Object Identifier';
    }

    /**
     * @see PubIdPlugin::getResolvingURL()
     */
    public function getResolvingURL($journalId, $pubId) {
        return 'https://doi.org/' . $this->_doiURLEncode($pubId);
    }

    /**
     * @see PubIdPlugin::getFormFieldNames()
     */
    public function getFormFieldNames() {
        return ['doiSuffix', 'excludeDoi'];
    }

    /**
     * @see PubIdPlugin::getExcludeFormFieldName()
     */
    public function getExcludeFormFieldName() {
        return 'excludeDoi';
    }

    /**
     * @see PubIdPlugin::isEnabled()
     */
    public function isEnabled($pubObjectType, $journalId) {
        return $this->getSetting($journalId, "enable{$pubObjectType}Doi") == '1';
    }

    /**
     * @see PubIdPlugin::getDAOFieldNames()
     */
    public function getDAOFieldNames() {
        return ['pub-id::doi'];
    }

    /**
     * @see PubIdPlugin::getPubIdMetadataFile()
     */
    public function getPubIdMetadataFile() {
        return $this->getTemplatePath() . 'doiSuffixEdit.tpl';
    }

    /**
     * @see PubIdPlugin::getSettingsFormName()
     */
    public function getSettingsFormName() {
        return 'classes.form.DOISettingsForm';
    }

    /**
     * @see PubIdPlugin::verifyData()
     */
    public function verifyData($fieldName, $fieldValue, $pubObject, $journalId, &$errorMsg) {
        // Verify DOI uniqueness.
        if ($fieldName == 'doiSuffix') {
            if (empty($fieldValue)) return true;

            // Construct the potential new DOI with the posted suffix.
            $doiPrefix = $this->getSetting($journalId, 'doiPrefix');
            if (empty($doiPrefix)) return true;
            $newDoi = $doiPrefix . '/' . $fieldValue;

            if ($this->checkDuplicate($newDoi, $pubObject, $journalId)) {
                return true;
            } else {
                $errorMsg = __('plugins.pubIds.doi.editor.doiSuffixCustomIdentifierNotUnique');
                return false;
            }
        }
        return true;
    }

    /**
     * @see PubIdPlugin::validatePubId()
     */
    public function validatePubId($pubId) {
        return (bool) preg_match('/^\d+(.\d+)+\//', $pubId);
    }

    /*
     * Private methods
     */

    /**
     * Encode DOI according to ANSI/NISO Z39.84-2005, Appendix E.
     * @param $pubId string
     * @return string
     */
    private function _doiURLEncode($pubId) {
        $search = ['%', '"', '#', ' ', '<', '>', '{'];
        $replace = ['%25', '%22', '%23', '%20', '%3c', '%3e', '%7b'];
        $pubId = str_replace($search, $replace, $pubId);
        return $pubId;
    }
}
?>