<?php
declare(strict_types=1);

/**
 * @file classes/DuraCloudXMLParser.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudXMLParser
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud PHP client XML helper class
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, XMLParser Object, Standardized SHIM)
 */

class DuraCloudXMLParser {
    
    /** @var array The full parsed data tree */
    protected $data;

    /** @var array|null Pointer reference into current element of $data */
    protected $currentElement;

    /** @var \XMLParser|resource|null */
    protected $parser;

    /**
     * Constructor
     */
    public function __construct() {
        $this->data = ['children' => []];
        // Reference is REQUIRED here because we are traversing an array tree structure
        $this->currentElement =& $this->data;
        
        $encoding = defined('DURACLOUD_XML_ENCODING') ? DURACLOUD_XML_ENCODING : 'UTF-8';
        $this->parser = xml_parser_create($encoding);

        if ($this->parser) {
            xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
            // xml_set_object is valid in PHP 8
            xml_set_object($this->parser, $this); 
            xml_set_element_handler($this->parser, 'startElement', 'endElement');
            xml_set_character_data_handler($this->parser, 'charData');
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudXMLParser() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Free resources and unset data
     */
    public function destroy(): void {
        if ($this->parser) {
            xml_parser_free($this->parser);
        }
        unset($this->data);
        unset($this->currentElement);
    }

    /**
     * Parse XML content
     * @param string $xmlContents
     * @return int|bool 1/true on success, 0/false on failure
     */
    public function parse($xmlContents) {
        if (!$this->parser) return false;
        return xml_parse($this->parser, $xmlContents);
    }

    /**
     * Get the first child of the parsed tree (the root result)
     * @return array
     */
    public function getResults() {
        // Use safe check instead of raw assert for production safety
        if (!isset($this->data['children'][0])) {
            return [];
        }
        return $this->data['children'][0];
    }

    //
    // Internals - Callbacks for XML Parser
    //

    /**
     * Handle start element
     * @param \XMLParser|resource $parser
     * @param string $tag
     * @param array $attributes
     */
    protected function startElement($parser, $tag, $attributes): void {
        $newElement = [
            'name' => $tag,
            'children' => [],
            // 'parent' reference is REQUIRED for tree traversal logic in endElement
            'parent' => &$this->currentElement, 
            'content' => '',
            'attributes' => $attributes
        ];

        // Assign by reference so modifications to $newElement reflect in the parent's children array
        $this->currentElement['children'][] =& $newElement;
        
        // Move pointer deeper into the tree
        $temp =& $newElement;
        unset($this->currentElement);
        $this->currentElement =& $temp;
    }

    /**
     * Handle end element
     * @param \XMLParser|resource $parser
     * @param string $tag
     */
    protected function endElement($parser, $tag): void {
        if (isset($this->currentElement['parent'])) {
            $parent =& $this->currentElement['parent'];
            unset($this->currentElement);
            // Move pointer back up the tree
            $this->currentElement =& $parent;
        }
    }

    /**
     * Handle character data
     * @param \XMLParser|resource $parser
     * @param string $data
     */
    protected function charData($parser, $data): void {
        if (isset($this->currentElement)) {
            $this->currentElement['content'] .= $data;
        }
    }
}
?>