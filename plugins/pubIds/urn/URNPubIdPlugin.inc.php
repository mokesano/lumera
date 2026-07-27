<?php
declare(strict_types=1);

/**
 * @file plugins/pubIds/urn/URNPubIdPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class URNPubIdPlugin
 * @ingroup plugins_pubIds_urn
 *
 * @brief URN plugin class.
 */

import('classes.plugins.PubIdPlugin');

class URNPubIdPlugin extends PubIdPlugin {

    //
    // Implement template methods from PKPPlugin.
    //
    /**
     * Register the plugin.
     * @see PubIdPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of the plugin.
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'URNPubIdPlugin';
    }

    /**
     * Get the display name of the plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.pubIds.urn.displayName');
    }

    /**
     * Get the description of the plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.pubIds.urn.description');
    }

    /**
     * Get the template path of the plugin.
     * @see PKPPlugin::getTemplatePath()
     * @param bool $inCore
     * @return string
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath() . 'templates/';
    }

    //
    // Implement template methods from PubIdPlugin.
    //
    /**
     * Generate or retrieve a URN for the given publishing object.
     * @see PubIdPlugin::getPubId()
     * @param object $pubObject
     * @param bool $preview
     * @return string|null
     */
    public function getPubId($pubObject, $preview = false) {
        $urn = $pubObject->getStoredPubId($this->getPubIdType());
        
        if (!$urn && !$this->isExcluded($pubObject)) {
            $pubObjectType = $this->getPubObjectType($pubObject);

            // Initialize variables for publication objects
            $issue = ($pubObjectType === 'Issue' ? $pubObject : null);
            $article = ($pubObjectType === 'Article' ? $pubObject : null);
            $galley = ($pubObjectType === 'Galley' ? $pubObject : null);
            $suppFile = ($pubObjectType === 'SuppFile' ? $pubObject : null);

            // Get the journal id of the object
            if (in_array($pubObjectType, ['Issue', 'Article'], true)) {
                $journalId = (int) $pubObject->getJournalId();
            } else {
                // [WIZDAM FIX] Replaced assert with explicit null-safety check
                if (!($pubObject instanceof ArticleFile)) {
                    return null;
                }
                
                /** @var ArticleDAO $articleDao */
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $article = $articleDao->getArticle((int) $pubObject->getArticleId(), null, true);
                if (!$article) {
                    return null;
                }

                $journalId = (int) $article->getJournalId();
            }

            $journal = $this->getJournal($journalId);
            if (!$journal) {
                return null;
            }
            $journalId = (int) $journal->getId();

            // Check whether URNs are enabled for the given object type
            $urnEnabled = ($this->getSetting($journalId, "enable{$pubObjectType}URN") === '1');
            if (!$urnEnabled) {
                return null;
            }

            // Retrieve the issue
            if (!($pubObject instanceof Issue) && $article !== null) {
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao->getIssueByArticleId((int) $article->getId(), $journalId);
            }

            // Retrieve the URN prefix
            $urnPrefix = (string) $this->getSetting($journalId, 'urnPrefix');
            if ($urnPrefix === '') {
                return null;
            }

            // Generate the URN suffix
            $urnSuffixSetting = (string) $this->getSetting($journalId, 'urnSuffix');
            $urnSuffix = '';

            switch ($urnSuffixSetting) {
                case 'customIdentifier':
                    $urnSuffix = (string) $pubObject->getData('urnSuffix');
                    break;

                case 'pattern':
                    $urnSuffix = (string) $this->getSetting($journalId, "urn{$pubObjectType}SuffixPattern");

                    // %j - journal initials
                    $initials = (string) $journal->getLocalizedSetting('initials', $journal->getPrimaryLocale());
                    $urnSuffix = PKPString::regexp_replace('/%j/', PKPString::strtolower($initials), $urnSuffix);
                    
                    // %x - custom identifier
                    $publisherId = $pubObject->getStoredPubId('publisher-id');
                    if ($publisherId) {
                        $urnSuffix = PKPString::regexp_replace('/%x/', (string) $publisherId, $urnSuffix);
                    }
                    
                    if ($issue) {
                        // %v - volume number
                        $urnSuffix = PKPString::regexp_replace('/%v/', (string) $issue->getVolume(), $urnSuffix);
                        // %i - issue number
                        $urnSuffix = PKPString::regexp_replace('/%i/', (string) $issue->getNumber(), $urnSuffix);
                        // %Y - year
                        $urnSuffix = PKPString::regexp_replace('/%Y/', (string) $issue->getYear(), $urnSuffix);
                    }
                    if ($article) {
                        // %a - article id
                        $urnSuffix = PKPString::regexp_replace('/%a/', (string) $article->getId(), $urnSuffix);
                        // %p - page number
                        $pages = $article->getPages();
                        if ($pages) {
                            $urnSuffix = PKPString::regexp_replace('/%p/', (string) $pages, $urnSuffix);
                        }
                    }
                    if ($galley) {
                        // %g - galley id
                        $urnSuffix = PKPString::regexp_replace('/%g/', (string) $galley->getId(), $urnSuffix);
                    }
                    if ($suppFile) {
                        // %s - supp file id
                        $urnSuffix = PKPString::regexp_replace('/%s/', (string) $suppFile->getId(), $urnSuffix);
                    }
                    break;

                default:
                    $urnSuffix = PKPString::strtolower((string) $journal->getLocalizedSetting('initials', $journal->getPrimaryLocale()));

                    if ($issue) {
                        $urnSuffix .= '.v' . $issue->getVolume() . 'i' . $issue->getNumber();
                    } else {
                        $urnSuffix .= '.v%vi%i';
                    }

                    if ($article) {
                        $urnSuffix .= '.' . $article->getId();
                    }

                    if ($galley) {
                        $urnSuffix .= '.g' . $galley->getId();
                    }

                    if ($suppFile) {
                        $urnSuffix .= '.s' . $suppFile->getId();
                    }
            }

            if ($urnSuffix !== '') {
                $urn = $urnPrefix . $urnSuffix;
                $checkNoSetting = $this->getSetting($journalId, 'checkNo');
                if ($checkNoSetting === '1' || $checkNoSetting === true) {
                    $urn .= $this->_calculateCheckNo($urn);
                }
            } else {
                return null;
            }

            if ($urn !== null && !$preview) {
                $this->setStoredPubId($pubObject, $pubObjectType, $urn);
            }
        }
        return $urn;
    }

    /**
     * Get the type of public identifier handled by this plugin.
     * @see PubIdPlugin::getPubIdType()
     * @return string
     */
    public function getPubIdType(): string {
        return 'other::urn';
    }

    /**
     * Get the display type of public identifier handled by this plugin.
     * @see PubIdPlugin::getPubIdDisplayType()
     * @return string
     */
    public function getPubIdDisplayType(): string {
        return 'URN';
    }

    /**
     * Get the full name of public identifier handled by this plugin.
     * @see PubIdPlugin::getPubIdFullName()
     * @return string
     */
    public function getPubIdFullName(): string {
        return 'Uniform Resource Name';
    }

    /**
     * Get the resolving URL for a given public identifier.
     * @see PubIdPlugin::getResolvingURL()
     * @param int $journalId
     * @param string $pubId
     * @return string
     */
    public function getResolvingURL($journalId, $pubId): string {
        $resolverURL = (string) $this->getSetting($journalId, 'urnResolver');
        return $resolverURL . $pubId;
    }

    /**
     * Get form field names for custom identifiers.
     * @see PubIdPlugin::getFormFieldNames()
     * @return array
     */
    public function getFormFieldNames(): array {
        return ['urnSuffix', 'excludeURN'];
    }

    /**
     * Get the form field name for excluding public identifiers.
     * @see PubIdPlugin::getExcludeFormFieldName()
     * @return string
     */
    public function getExcludeFormFieldName(): string {
        return 'excludeURN';
    }

    /**
     * Check whether the plugin is enabled for a given publication object type.
     * @see PubIdPlugin::isEnabled()
     * @param string $pubObjectType
     * @param int $journalId
     * @return bool
     */
    public function isEnabled($pubObjectType, $journalId): bool {
        return $this->getSetting($journalId, "enable{$pubObjectType}URN") === '1';
    }

    /**
     * Get the DAO field names for custom identifiers.
     * @see PubIdPlugin::getDAOFieldNames()
     * @return array
     */
    public function getDAOFieldNames(): array {
        return ['pub-id::other::urn'];
    }

    /**
     * Get the template file for the public identifier metadata field.
     * @see PubIdPlugin::getPubIdMetadataFile()
     * @return string
     */
    public function getPubIdMetadataFile(): string {
        return $this->getTemplatePath() . 'urnSuffixEdit.tpl';
    }

    /**
     * Get the settings form class name.
     * @see PubIdPlugin::getSettingsFormName()
     * @return string
     */
    public function getSettingsFormName(): string {
        return 'classes.form.URNSettingsForm';
    }

    /**
     * Verify the data input for custom identifiers.
     * @see PubIdPlugin::verifyData()
     * @param string $fieldName
     * @param string $fieldValue
     * @param object $pubObject
     * @param int $journalId
     * @param string &$errorMsg
     * @return bool
     */
    public function verifyData($fieldName, $fieldValue, $pubObject, $journalId, &$errorMsg): bool {
        if ($fieldName === 'urnSuffix') {
            if ($fieldValue === '') {
                return true;
            }

            // Construct the potential new URN with the posted suffix.
            $urnPrefix = (string) $this->getSetting($journalId, 'urnPrefix');
            if ($urnPrefix === '') {
                return true;
            }
            
            $newURN = $urnPrefix . $fieldValue;
            $checkNoSetting = $this->getSetting($journalId, 'checkNo');
            
            if ($checkNoSetting === '1' || $checkNoSetting === true) {
                $newURNWithoutCheckNo = substr($newURN, 0, -1);
                $newURNWithCheckNo = $newURNWithoutCheckNo . $this->_calculateCheckNo($newURNWithoutCheckNo);
                if ($newURN !== $newURNWithCheckNo) {
                    $errorMsg = __('plugins.pubIds.urn.form.checkNoRequired');
                    return false;
                }
            }
            
            if (!$this->checkDuplicate($newURN, $pubObject, $journalId)) {
                $errorMsg = __('plugins.pubIds.urn.form.customIdentifierNotUnique');
                return false;
            }
        }
        return true;
    }

    //
    // Private helper methods
    //
    /**
     * Get the last, check number.
     * Algorithm: every URN character is replaced with a number according to the conversion table,
     * every number is multiplied by its position/index (beginning with 1),
     * the numbers' sum is calculated, the sum is divided by the last number,
     * the last number of the quotient before the decimal point is the check number.
     * @param string $urn
     * @return string
     */
    private function _calculateCheckNo(string $urn): string {
        $urnLower = strtolower_codesafe($urn);

        $conversionTable = [
            '9' => '41', '8' => '9', '7' => '8', '6' => '7', '5' => '6', '4' => '5', '3' => '4', '2' => '3', '1' => '2', '0' => '1',
            'a' => '18', 'b' => '14', 'c' => '19', 'd' => '15', 'e' => '16', 'f' => '21', 'g' => '22', 'h' => '23', 'i' => '24', 'j' => '25',
            'k' => '42', 'l' => '26', 'm' => '27', 'n' => '13', 'o' => '28', 'p' => '29', 'q' => '31', 'r' => '12', 's' => '32', 't' => '33',
            'u' => '11', 'v' => '34', 'w' => '35', 'x' => '36', 'y' => '37', 'z' => '38', '-' => '39', ':' => '17', '_' => '43', '/' => '45',
            '.' => '47', '+' => '49'
        ];

        $newURN = '';
        $len = strlen($urnLower);
        for ($i = 0; $i < $len; $i++) {
            $char = $urnLower[$i];
            $newURN .= $conversionTable[$char] ?? '';
        }

        $sum = 0;
        $newUrLen = strlen($newURN);
        if ($newUrLen === 0) {
            return '0';
        }

        for ($j = 1; $j <= $newUrLen; $j++) {
            $sum += ((int) $newURN[$j - 1] * $j);
        }

        $lastNumber = (int) $newURN[$newUrLen - 1];
        
        // Prevent division by zero
        if ($lastNumber === 0) {
            return '0';
        }

        $quot = $sum / $lastNumber;
        $quotRound = (int) floor($quot);
        $quotString = (string) $quotRound;

        return $quotString[strlen($quotString) - 1] ?? '0';
    }

}
?>