<?php
declare(strict_types=1);

/**
 * @file classes/xml/XMLParserDOMHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLParserDOMHandler
 * @ingroup xml
 * @see XMLParser
 *
 * @brief Default handler for XMLParser returning a simple DOM-style object.
 * This handler parses an XML document into a tree structure of XMLNode objects.
 */

import('lib.pkp.classes.xml.XMLNode');

class XMLParserDOMHandler extends PKPXMLParserHandler {

    /** @var XMLNode|null reference to the root node */
    public $rootNode;

    /** @var XMLNode|null reference to the node currently being parsed */
    public $currentNode;

    /** @var string|null reference to the current data */
    public $currentData;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->rootNode = null;
        $this->currentNode = null;
        $this->currentData = null;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLParserDOMHandler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Destructor.
     * Frees memory used by this handler.
     */
    public function destroy() {
        unset($this->currentNode, $this->currentData, $this->rootNode);
    }

    /**
     * Callback function to act as the start element handler.
     * 
     * @param resource $parser
     * @param string $tag
     * @param array|null $attributes
     */
    public function startElement($parser, $tag, $attributes) {
        $this->currentData = null;
        $node = new XMLNode($tag);
        
        $node->setAttributes(is_array($attributes) ? $attributes : []);

        if ($this->currentNode !== null) {
            $this->currentNode->addChild($node);
            $node->setParent($this->currentNode);
        } else {
            $this->rootNode = $node;
        }

        $this->currentNode = $node;
    }

    /**
     * Callback function to act as the end element handler.
     * 
     * @param resource $parser
     * @param string $tag
     */
    public function endElement($parser, $tag) {
        if ($this->currentNode !== null) {
            $this->currentNode->setValue($this->currentData);
            $this->currentNode = $this->currentNode->getParent();
        }
        $this->currentData = null;
    }

    /**
     * Callback function to act as the character data handler.
     * 
     * @param resource $parser
     * @param string $data
     */
    public function characterData($parser, $data) {
        $this->currentData = ($this->currentData ?? '') . (string) $data;
    }

    /**
     * Returns a reference to the root node of the tree representing the document.
     * 
     * @return XMLNode|null
     */
    public function getResult() {
        return $this->rootNode;
    }

}
?>