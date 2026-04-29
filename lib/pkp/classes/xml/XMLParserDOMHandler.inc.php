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
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility)
 */

import('lib.pkp.classes.xml.XMLNode');

class XMLParserDOMHandler extends PKPXMLParserHandler {

    /** @var XMLNode reference to the root node */
    public $rootNode;

    /** @var XMLNode reference to the node currently being parsed */
    public $currentNode;

    /** @var reference to the current data */
    public $currentData;

    /**
     * Constructor.
     */
    public function __construct() {
        // WIZDAM FIX: Fixed typo from rootNodes to rootNode to match property
        $this->rootNode = null;
        $this->currentNode = null;
        $this->currentData = null;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLParserDOMHandler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::XMLParserDOMHandler(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    public function destroy() {
        unset($this->currentNode, $this->currentData, $this->rootNode);
    }

    /**
     * Callback function to act as the start element handler.
     */
    public function startElement($parser, $tag, $attributes) {
        $this->currentData = null;
        $node = new XMLNode($tag);
        $node->setAttributes($attributes);

        if (isset($this->currentNode)) {
            $this->currentNode->addChild($node);
            $node->setParent($this->currentNode);

        } else {
            // WIZDAM FIX: No reference needed
            $this->rootNode = $node;
        }

        // WIZDAM FIX: No reference needed
        $this->currentNode = $node;
    }

    /**
     * Callback function to act as the end element handler.
     */
    public function endElement($parser, $tag) {
        $this->currentNode->setValue($this->currentData);
        // WIZDAM FIX: No reference needed
        $this->currentNode = $this->currentNode->getParent();
        $this->currentData = null;
    }

    /**
     * Callback function to act as the character data handler.
     */
    public function characterData($parser, $data) {
        $this->currentData .= $data;
    }

    /**
     * Returns a reference to the root node of the tree representing the document.
     * @return XMLNode
     */
    public function getResult() {
        return $this->rootNode;
    }
}

?>