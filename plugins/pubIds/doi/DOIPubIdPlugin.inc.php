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
     * Register the plugin.
     * 
     * @see PubIdPlugin::register()
     * @param string $category The category of the plugin.
     * @param string $path The path to the plugin.
     * @param int|null $mainContextId The context ID (optional).
     * @return bool True on successful registration.
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the symbolic name of the plugin.
     * 
     * @see PKPPlugin::getName()
     * @return string Plugin name.
     */
    public function getName(): string {
        return 'DOIPubIdPlugin';
    }

    /**
     * Get the display name of the plugin.
     * 
     * @see PKPPlugin::getDisplayName()
     * @return string Plugin display name.
     */
    public function getDisplayName(): string {
        return __('plugins.pubIds.doi.displayName');
    }

    /**
     * Get a description of the plugin.
     * 
     * @see PKPPlugin::getDescription()
     * @return string Plugin description.
     */
    public function getDescription(): string {
        return __('plugins.pubIds.doi.description');
    }

    /**
     * Get the template path for the plugin.
     * 
     * @see PKPPlugin::getTemplatePath()
     * @param bool $inCore True if core template path.
     * @return string Template path.
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath() . 'templates/';
    }

    //
    // Implement template methods from PubIdPlugin.
    //
    
    /**
     * Get the PubId for a given publishing object.
     * 
     * @see PubIdPlugin::getPubId()
     * @param object $pubObject The publishing object (Issue, Article, Galley, or SuppFile).
     * @param bool $preview Whether it's a preview or not.
     * @return string|null The generated DOI, or null if not applicable.
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
                $articleDao = DAORegistry::getDAO('PublishedArticleDAO'); /** @var PublishedArticleDAO $articleDao */
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
                $issueDao = DAORegistry::getDAO('IssueDAO'); /** @var IssueDAO $issueDao */
                $issue = $issueDao->getIssueByArticleId($article->getId(), $journal->getId());
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
                        $doiSuffix = PKPString::regexp_replace('/%x/', (string) $pubObject->getStoredPubId('publisher-id'), $doiSuffix);
                    }
                    if ($issue) {
                        // %v - volume number (Dikonversi ke string untuk PHP 8)
                        $doiSuffix = PKPString::regexp_replace('/%v/', (string) $issue->getVolume(), $doiSuffix);
                        // %i - issue number (Dikonversi ke string untuk PHP 8)
                        $doiSuffix = PKPString::regexp_replace('/%i/', (string) $issue->getNumber(), $doiSuffix);
                        // %Y - year (Dikonversi ke string untuk PHP 8)
                        $doiSuffix = PKPString::regexp_replace('/%Y/', (string) $issue->getYear(), $doiSuffix);
                    }
                    if ($article) {
                        // %a - article id (Dikonversi ke string untuk PHP 8)
                        $doiSuffix = PKPString::regexp_replace('/%a/', (string) $article->getId(), $doiSuffix);
                        // %p - page number (Dikonversi ke string untuk PHP 8)
                        if ($article->getPages()) {
                            $doiSuffix = PKPString::regexp_replace('/%p/', (string) $article->getPages(), $doiSuffix);
                        }
                    }
                    if ($galley) {
                        // %g - galley id (Dikonversi ke string untuk PHP 8)
                        $doiSuffix = PKPString::regexp_replace('/%g/', (string) $galley->getId(), $doiSuffix);
                    }
                    if ($suppFile) {
                        // %s - supp file id (Dikonversi ke string untuk PHP 8)
                        $doiSuffix = PKPString::regexp_replace('/%s/', (string) $suppFile->getId(), $doiSuffix);
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
     * Get the PubId type.
     * 
     * @see PubIdPlugin::getPubIdType()
     * @return string The PubId type (e.g. 'doi').
     */
    public function getPubIdType() {
        return 'doi';
    }

    /**
     * Get the display type of the PubId.
     * 
     * @see PubIdPlugin::getPubIdDisplayType()
     * @return string The display type.
     */
    public function getPubIdDisplayType() {
        return 'DOI';
    }

    /**
     * Get the full name of the PubId type.
     * 
     * @see PubIdPlugin::getPubIdFullName()
     * @return string The full name.
     */
    public function getPubIdFullName() {
        return 'Digital Object Identifier';
    }

    /**
     * Get the resolving URL for the DOI.
     * 
     * @see PubIdPlugin::getResolvingURL()
     * @param int $journalId The journal ID.
     * @param string $pubId The DOI string.
     * @return string The resolvable URL.
     */
    public function getResolvingURL($journalId, $pubId) {
        return 'https://doi.org/' . $this->_doiURLEncode($pubId);
    }

    /**
     * Get the form field names for the plugin.
     * 
     * @see PubIdPlugin::getFormFieldNames()
     * @return array Array of form field names.
     */
    public function getFormFieldNames() {
        return ['doiSuffix', 'excludeDoi'];
    }

    /**
     * Get the exclude form field name.
     * 
     * @see PubIdPlugin::getExcludeFormFieldName()
     * @return string The exclude form field name.
     */
    public function getExcludeFormFieldName() {
        return 'excludeDoi';
    }

    /**
     * Check if the DOI plugin is enabled for a specific object type and journal.
     * 
     * @see PubIdPlugin::isEnabled()
     * @param string $pubObjectType The type of publishing object.
     * @param int $journalId The journal ID.
     * @return bool True if enabled, false otherwise.
     */
    public function isEnabled($pubObjectType, $journalId) {
        return $this->getSetting($journalId, "enable{$pubObjectType}Doi") == '1';
    }

    /**
     * Get the DAO field names.
     * 
     * @see PubIdPlugin::getDAOFieldNames()
     * @return array Array of DAO field names.
     */
    public function getDAOFieldNames() {
        return ['pub-id::doi'];
    }

    /**
     * Get the metadata template file path.
     * 
     * @see PubIdPlugin::getPubIdMetadataFile()
     * @return string Path to the metadata template file.
     */
    public function getPubIdMetadataFile() {
        return $this->getTemplatePath() . 'doiSuffixEdit.tpl';
    }

    /**
     * Get the name of the settings form class.
     * 
     * @see PubIdPlugin::getSettingsFormName()
     * @return string Settings form class name.
     */
    public function getSettingsFormName() {
        return 'classes.form.DOISettingsForm';
    }

    /**
     * Verify data inputted via a form.
     * 
     * @see PubIdPlugin::verifyData()
     * @param string $fieldName The name of the field to verify.
     * @param string $fieldValue The value of the field.
     * @param object $pubObject The publishing object.
     * @param int $journalId The journal ID.
     * @param string &$errorMsg Reference to the error message string.
     * @return bool True if verification passes, false otherwise.
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
     * Validate the syntax of a given DOI string.
     * 
     * @see PubIdPlugin::validatePubId()
     * @param string $pubId The DOI string to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validatePubId($pubId) {
        return (bool) preg_match('/^\d+(.\d+)+\//', $pubId);
    }

    /*
     * Private methods
     */

    /**
     * Encode DOI according to ANSI/NISO Z39.84-2005, Appendix E.
     * 
     * @param string $pubId The DOI string to encode.
     * @return string The encoded DOI string.
     */
    private function _doiURLEncode($pubId) {
        $search = ['%', '"', '#', ' ', '<', '>', '{'];
        $replace = ['%25', '%22', '%23', '%20', '%3c', '%3e', '%7b'];
        $pubId = str_replace($search, $replace, $pubId);
        return $pubId;
    }

}
?>