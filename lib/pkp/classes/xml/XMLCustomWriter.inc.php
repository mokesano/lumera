<?php
declare(strict_types=1);

/**
 * @file classes/xml/XMLCustomWriter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLCustomWriter
 * @ingroup xml
 *
 * @brief Wrapper class for writing XML documents using PHP 4.x or 5.x
 * * REFACTORED: Wizdam Edition (PHP 8 Static Methods, No References)
 */

import ('lib.pkp.classes.xml.XMLNode');
import ('lib.pkp.classes.xml.XMLComment');

class XMLCustomWriter {
    
    /**
     * Create a new XML document.
     * If $url is set, the DOCTYPE definition is treated as a PUBLIC
     * definition; $dtd should contain the ID, and $url should contain the
     * URL. Otherwise, $dtd should be the DTD name.
     */
    public static function createDocument($type = null, $dtd = null, $url = null) {
        $version = '1.0';
        if (class_exists('DOMImplementation')) {
            // Use the new (PHP 5.x) DOM
            $impl = new DOMImplementation();
            // only generate a DOCTYPE if type is non-empty
            if ($type != '') {
                $domdtd = $impl->createDocumentType($type, isset($url)?$dtd:'', isset($url)?$url:$dtd);
                $doc = $impl->createDocument($version, '', $domdtd);
            } else {
                $doc = $impl->createDocument($version, '');
            }
            // ensure we are outputting UTF-8
            $doc->encoding = 'UTF-8';
        } else {
            // Use the XMLNode class
            $doc = new XMLNode();
            $doc->setAttribute('version', $version);
            if ($type !== null) $doc->setAttribute('type', $type);
            if ($dtd !== null) $doc->setAttribute('dtd', $dtd);
            if ($url !== null) $doc->setAttribute('url', $url);
        }
        return $doc;
    }

    /**
     * Create an element node.
     * @param $doc XML document
     * @param $name string element name
     * @return XML element node
     */
    public static function createElement($doc, $name) {
        if (method_exists($doc, 'createElement')) {
            $element = $doc->createElement($name);
        } else {
            $element = new XMLNode($name);
        }

        return $element;
    }

    /**
     * Create a comment node.
     * @param $doc XML document
     * @param $content string comment content
     * @return XML comment node
     */
    public static function createComment($doc, $content) {
        if (method_exists($doc, 'createComment')) {
            $element = $doc->createComment($content);
        } else {
            $element = new XMLComment();
            $element->setValue($content);
        }

        return $element;
    }

    /**
     * Create a text node.
     * @param $doc XML document
     * @param $value string text value
     * @return XML text node
     */
    public static function createTextNode($doc, $value) {
        // WIZDAM FIX: Static call to Core
        $value = Core::cleanVar($value);

        if (method_exists($doc, 'createTextNode')) {
            $element = $doc->createTextNode($value);
        } else {
            $element = new XMLNode();
            $element->setValue($value);
        }

        return $element;
    }

    /**
     * Append a child node to a parent node.
     * @param $parentNode XML parent node
     * @param $child XML child node
     * @return XML node the appended child node
     */
    public static function appendChild($parentNode, $child) {
        if (method_exists($parentNode, 'appendChild')) {
            $node = $parentNode->appendChild($child);
        } else {
            // Assumes XMLNode
            $parentNode->addChild($child);
            $child->setParent($parentNode);
            $node = $child;
        }

        return $node;
    }

    /**
     * Get an attribute from a node.
     * @param $node XML node
     * @param $name string attribute name
     * @return string attribute value
     */
    public static function getAttribute($node, $name) {
        return $node->getAttribute($name);
    }

    /**
     * Check if a node has a given attribute.
     * @param $node XML node
     * @param $name string attribute name
     * @return boolean
     */
    public static function hasAttribute($node, $name) {
        if (method_exists($node, 'hasAttribute')) {
            $value = $node->hasAttribute($name);
        } else {
            $attribute = XMLCustomWriter::getAttribute($node, $name);
            $value = ($attribute !== null);
        }
        return $value;
    }

    /**
     * Set an attribute on a node.
     * @param $node XML node
     * @param $name string attribute name
     * @param $value string attribute value
     * @param $appendIfEmpty boolean whether to append the attribute if the value is empty
     */
    public static function setAttribute($node, $name, $value, $appendIfEmpty = true) {
        if (!$appendIfEmpty && (string) $value == '') return;
        return $node->setAttribute($name, (string) $value);
    }

    /**
     * Get the XML representation of a document.
     * @param $doc XML document
     * @return string XML
     */
    public static function getXML($doc) {
        if (method_exists($doc, 'saveXML')) {
            $xml = $doc->saveXML();
        } else {
            $xml = $doc->toXml();
        }
        return $xml;
    }

    /**
     * Print the XML representation of a document.
     * @param $doc XML document
     */
    public static function printXML($doc) {
        if (method_exists($doc, 'saveXML')) {
            echo $doc->saveXML();
        } else {
            $doc->toXml(true);
        }
    }

    /**
     * Create a child element with text content.
     * @param $doc XML document
     * @param $node XML parent node
     * @param $name string child element name
     * @param $value string child text content
     * @param $appendIfEmpty boolean whether to append the child if the value is empty
     * @return XML child node
     */
    public static function createChildWithText($doc, $node, $name, $value, $appendIfEmpty = true) {
        $childNode = null;
        if ($appendIfEmpty || $value != '') {
            $childNode = XMLCustomWriter::createElement($doc, $name);
            $textNode = XMLCustomWriter::createTextNode($doc, $value);
            XMLCustomWriter::appendChild($childNode, $textNode);
            XMLCustomWriter::appendChild($node, $childNode);
        }
        return $childNode;
    }

    /**
     * Create a child element with text content read from a file.
     * @param $doc XML document
     * @param $node XML parent node
     * @param $name string child element name
     * @param $filename string file to read content from
     * @return XML child node
     */
    public static function createChildFromFile($doc, $node, $name, $filename) {
        $fileManager = new FileManager();
        $contents = $fileManager->readFile($filename);
        
        if ($contents === false) {
            return null;
        }

        // WIZDAM FIX: Original code stopped here. Added logic to actually create the node.
        return self::createChildWithText($doc, $node, $name, $contents);
    }
}

?>