<?php
declare(strict_types=1);

/**
 * @file classes/codelist/ONIXParserDOMHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ONIXParserDOMHandler
 * @ingroup codelist
 * @see XMLParser
 *
 * @brief This parser extracts a specific xs:simpleType based on a name attribute
 * representing a code list within it. It returns the xs:enumeration values
 * within it along with the xs:documentation elements which serve as textual
 * descriptions of the Codelist values.
 *
 * Example:  <xs:simpleType name="List30">...</xs:simpleType>
 */

import('lib.pkp.classes.xml.XMLParserDOMHandler');
import('lib.pkp.classes.xml.XMLNode');

class ONIXParserDOMHandler extends XMLParserDOMHandler {

    /** @var string The list being searched for */
    protected string $_listName;

    /** @var bool to maintain state */
    protected bool $_foundRequestedList = false;

    /** @var array of items the parser eventually returns */
    protected array $_listItems = [];

    /** @var string|null to store the current character data */
    protected ?string $_currentValue = null;

    /** @var bool currently inside an xs:documentation element */
    protected bool $_insideDocumentation = false;

    /**
     * Constructor.
     * @param string $listName
     */
    public function __construct(string $listName) {
        parent::__construct();
        $this->_listName = $listName;
        $this->_listItems = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ONIXParserDOMHandler($listName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct(string $listName);
    }

    /**
     * Callback function to act as the start element handler.
     * @param object $parser XMLParser
     * @param string $tag
     * @param array $attributes
     */
    public function startElement($parser, $tag, $attributes): void {
        $this->currentData = null;

        switch ($tag) {
            case 'xs:simpleType':
                if (isset($attributes['name']) && $attributes['name'] == $this->_listName) {
                    $this->_foundRequestedList = true;
                }
                break;
            case 'xs:enumeration':
                if ($this->_foundRequestedList && isset($attributes['value'])) {
                    $this->_currentValue = $attributes['value'];
                    // initialize the array cell if not exists
                    if (!isset($this->_listItems[$this->_currentValue])) {
                        $this->_listItems[$this->_currentValue] = [];
                    }
                }
                break;
            case 'xs:documentation':
                if ($this->_foundRequestedList) {
                    $this->_insideDocumentation = true;
                }
                break;
        }

        $node = new XMLNode($tag);
        $node->setAttributes($attributes);
        
        if (isset($this->currentNode)) {
            $this->currentNode->addChild($node);
            $node->setParent($this->currentNode);
        } else {
            $this->rootNode = $node;
        }

        $this->currentNode = $node;
    }

    /**
     * Callback function to act as the character data handler.
     * @param object $parser XMLParser
     * @param string $data
     */
    public function characterData($parser, $data): void {
        if ($this->_insideDocumentation && $this->_currentValue !== null) {
            $this->_listItems[$this->_currentValue][] = $data;
        }
    }

    /**
     * Callback function to act as the end element handler.
     * @param object $parser XMLParser
     * @param string $tag
     */
    public function endElement($parser, $tag): void {
        switch ($tag) {
            case 'xs:simpleType':
                $this->_foundRequestedList = false;
                break;
            case 'xs:documentation':
                $this->_insideDocumentation = false;
                break;
        }
    }

    /**
     * Returns the array of found list items
     * @return array
     */
    public function getResult(): array {
        return [$this->_listName => $this->_listItems];
    }
}
?>