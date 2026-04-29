<?php
declare(strict_types=1);

/**
 * @file plugins/metadata/nlm30/filter/Nlm30NameSchemaPersonStringFilter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Nlm30NameSchemaPersonStringFilter
 * @ingroup plugins_metadata_nlm30_filter
 * @see Nlm30NameSchema
 *
 * @brief Filter that converts from NLM name to
 * a string.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & Reference Fixes
 */

import('lib.pkp.plugins.metadata.nlm30.filter.Nlm30PersonStringFilter');

class Nlm30NameSchemaPersonStringFilter extends Nlm30PersonStringFilter {
    /** @var string */
    public $_template;

    /** @var string */
    public $_delimiter;

    /**
     * Constructor
     * @param int $filterMode
     * @param string $template default: DRIVER guidelines 2.0 name template
     * Possible template variables are %surname%, %suffix%, %prefix%, %initials%, %firstname%
     * @param string $delimiter
     */
    public function __construct($filterMode = PERSON_STRING_FILTER_SINGLE, $template = '%surname%%suffix%,%initials% (%firstname%)%prefix%', $delimiter = '; ') {
        $this->setDisplayName('NLM Name Schema to string conversion');

        assert(!empty($template) && is_string($template));
        $this->_template = $template;
        assert(is_string($delimiter));
        $this->_delimiter = $delimiter;

        $inputType = 'metadata::lib.pkp.plugins.metadata.nlm30.schema.Nlm30NameSchema(*)';
        $outputType = 'primitive::string';
        if ($filterMode == PERSON_STRING_FILTER_MULTIPLE) $inputType .= '[]';

        parent::__construct($inputType, $outputType, $filterMode);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Nlm30NameSchemaPersonStringFilter($filterMode = PERSON_STRING_FILTER_SINGLE, $template = '%surname%%suffix%,%initials% (%firstname%)%prefix%', $delimiter = '; ') {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the output template
     * @return string
     */
    public function getTemplate() {
        return $this->_template;
    }

    /**
     * Set the output template
     * @param string $template
     */
    public function setTemplate($template) {
        $this->_template = $template;
    }

    /**
     * Get the author delimiter (for multiple mode)
     * @return string
     */
    public function getDelimiter() {
        return $this->_delimiter;
    }

    /**
     * Set the author delimiter (for multiple mode)
     * @param string $delimiter
     */
    public function setDelimiter($delimiter) {
        $this->_delimiter = $delimiter;
    }


    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::supports()
     * [WIZDAM FIX] Removed references (&) to comply with PHP 8 strict standards if parent doesn't use them.
     * Original: supports(&$input, &$output)
     */
    public function supports($input, $output) {
        // We intercept the supports() method so that
        // we can remove et-al entries which are valid but
        // do not conform to the canonical type definition.
        // [WIZDAM FIX] removeEtAlEntries likely doesn't need & either for return
        $filteredInput = $this->removeEtAlEntries($input);
        if ($filteredInput === false) return false;

        return parent::supports($filteredInput, $output);
    }

    /**
     * @see Filter::process()
     * [WIZDAM FIX] Removed references (&) to comply with PHP 8 strict standards
     * @param mixed $input a(n array of) MetadataDescription(s)
     * @return string
     */
    public function process($input) {
        switch ($this->getFilterMode()) {
            case PERSON_STRING_FILTER_MULTIPLE:
                $personDescription = $this->_flattenPersonDescriptions($input);
                break;

            case PERSON_STRING_FILTER_SINGLE:
                $personDescription = $this->_flattenPersonDescription($input);
                break;

            default:
                $personDescription = '';
                assert(false);
        }

        return $personDescription;
    }

    //
    // Private helper methods
    //
    /**
     * Transform an NLM name description array to a person string.
     * NB: We use ; as name separator.
     * [WIZDAM FIX] Removed reference (&) on parameter
     * @param array $personDescriptions an array of MetadataDescriptions
     * @return string
     */
    public function _flattenPersonDescriptions($personDescriptions) {
        assert(is_array($personDescriptions));
        // [WIZDAM] Callback fix: using [$this, method] array syntax
        $personDescriptionStrings = array_map([$this, '_flattenPersonDescription'], $personDescriptions);
        $personString = implode($this->getDelimiter(), $personDescriptionStrings);
        return $personString;
    }

    /**
     * Transform a single NLM name description to a person string.
     * NB: We use the style: surname suffix, initials (first-name) prefix
     * which is relatively easy to parse back.
     * [WIZDAM FIX] Removed reference (&) on parameter
     * @param MetadataDescription|string $personDescription
     * @return string
     */
    public function _flattenPersonDescription($personDescription) {
        // Handle et-al
        if (is_string($personDescription) && $personDescription == PERSON_STRING_FILTER_ETAL) return 'et al';

        $nameVars['%surname%'] = (string)$personDescription->getStatement('surname');

        $givenNames = $personDescription->getStatement('given-names');
        $nameVars['%firstname%'] = $nameVars['%initials%'] = '';
        if(is_array($givenNames) && count($givenNames)) {
            if (PKPString::strlen($givenNames[0]) > 1) {
                $nameVars['%firstname%'] = array_shift($givenNames);
            }
            foreach($givenNames as $givenName) {
                $nameVars['%initials%'] .= PKPString::substr($givenName, 0, 1).'.';
            }
        }
        if (!empty($nameVars['%initials%'])) $nameVars['%initials%'] = ' '.$nameVars['%initials%'];

        $nameVars['%prefix%'] = (string)$personDescription->getStatement('prefix');
        if (!empty($nameVars['%prefix%'])) $nameVars['%prefix%'] = ' '.$nameVars['%prefix%'];
        $nameVars['%suffix%'] = (string)$personDescription->getStatement('suffix');
        if (!empty($nameVars['%suffix%'])) $nameVars['%suffix%'] = ' '.$nameVars['%suffix%'];

        // Fill placeholders in person template.
        $personString = str_replace(array_keys($nameVars), array_values($nameVars), $this->getTemplate());

        // Remove empty brackets and trailing/leading whitespace
        $personString = trim(str_replace('()', '', $personString));

        return $personString;
    }
}
?>