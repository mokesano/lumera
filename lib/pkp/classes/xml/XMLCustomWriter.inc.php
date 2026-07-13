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
 * @brief Wrapper class for writing XML documents using PHP 8, No References.
 */

import('lib.pkp.classes.xml.XMLNode');
import('lib.pkp.classes.xml.XMLComment');
import('lib.pkp.classes.file.FileManager');

class XMLCustomWriter {
    
    /**
     * Create a new XML document.
     * If $url is set, the DOCTYPE definition is treated as a PUBLIC
     * definition; $dtd should contain the ID, and $url should contain the
     * URL. Otherwise, $dtd should be the DTD name.
     * 
     * @param string|null $type Document type
     * @param string|null $dtd DTD identifier
     * @param string|null $url DTD URL
     * @return DOMDocument|XMLNode
     */
    public static function createDocument($type = null, $dtd = null, $url = null) {
        $version = '1.0';
        
        if (class_exists('DOMImplementation')) {
            // Use the new (PHP 5.x+) DOM
            $impl = new DOMImplementation();
            
            // Only generate a DOCTYPE if type is non-empty
            if ($type !== null && $type !== '') {
                $domdtd = $impl->createDocumentType(
                    $type, 
                    isset($url) ? $dtd : '', 
                    isset($url) ? $url : $dtd
                );
                $doc = $impl->createDocument($version, '', $domdtd);
            } else {
                $doc = $impl->createDocument($version, '');
            }
            
            // Ensure we are outputting UTF-8
            $doc->encoding = 'UTF-8';
        } else {
            // Fallback: Use the XMLNode class
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
     * 
     * @param DOMDocument|XMLNode $doc XML document
     * @param string $name Element name
     * @return DOMElement|XMLNode XML element node
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
     * 
     * @param DOMDocument|XMLNode $doc XML document
     * @param string $content Comment content
     * @return DOMComment|XMLComment XML comment node
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
     * 
     * @param DOMDocument|XMLNode $doc XML document
     * @param string $value Text value
     * @return DOMText|XMLNode XML text node
     */
    public static function createTextNode($doc, $value) {
        // WIZDAM FIX: Static call to Core for sanitization
        $value = Core::cleanVar((string) $value);

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
     * 
     * @param DOMElement|XMLNode $parentNode XML parent node
     * @param DOMElement|XMLNode $child XML child node
     * @return DOMElement|XMLNode The appended child node
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
     * 
     * @param DOMElement|XMLNode $node XML node
     * @param string $name Attribute name
     * @return string|null Attribute value
     */
    public static function getAttribute($node, $name) {
        // [WIZDAM] Safety check untuk method_exists
        if (method_exists($node, 'getAttribute')) {
            return $node->getAttribute($name);
        }
        return null;
    }

    /**
     * Check if a node has a given attribute.
     * 
     * @param DOMElement|XMLNode $node XML node
     * @param string $name Attribute name
     * @return bool
     */
    public static function hasAttribute($node, $name) {
        if (method_exists($node, 'hasAttribute')) {
            return (bool) $node->hasAttribute($name);
        } else {
            $attribute = XMLCustomWriter::getAttribute($node, $name);
            return $attribute !== null;
        }
    }

    /**
     * Set an attribute on a node.
     * 
     * @param DOMElement|XMLNode $node XML node
     * @param string $name Attribute name
     * @param string $value Attribute value
     * @param bool $appendIfEmpty Whether to append the attribute if the value is empty
     * @return DOMElement|XMLNode|null
     */
    public static function setAttribute($node, $name, $value, $appendIfEmpty = true) {
        // [WIZDAM] Strict comparison
        if (!$appendIfEmpty && (string) $value === '') {
            return null;
        }
        
        // [WIZDAM] Safety check untuk method_exists
        if (method_exists($node, 'setAttribute')) {
            return $node->setAttribute($name, (string) $value);
        }
        
        return null;
    }

    /**
     * Get the XML representation of a document.
     * 
     * @param DOMDocument|XMLNode $doc XML document
     * @return string XML
     */
    public static function getXML($doc) {
        if (method_exists($doc, 'saveXML')) {
            return $doc->saveXML();
        } else {
            return $doc->toXml();
        }
    }

    /**
     * Print the XML representation of a document.
     * 
     * @param DOMDocument|XMLNode $doc XML document
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
     * 
     * @param DOMDocument|XMLNode $doc XML document
     * @param DOMElement|XMLNode $node XML parent node
     * @param string $name Child element name
     * @param string $value Child text content
     * @param bool $appendIfEmpty Whether to append the child if the value is empty
     * @return DOMElement|XMLNode|null XML child node
     */
    public static function createChildWithText($doc, $node, $name, $value, $appendIfEmpty = true) {
        $childNode = null;
        
        // [WIZDAM] Strict comparison
        if ($appendIfEmpty || (string) $value !== '') {
            $childNode = XMLCustomWriter::createElement($doc, $name);
            $textNode = XMLCustomWriter::createTextNode($doc, $value);
            XMLCustomWriter::appendChild($childNode, $textNode);
            XMLCustomWriter::appendChild($node, $childNode);
        }
        
        return $childNode;
    }

    /**
     * Create a child element with text content read from a file.
     * 
     * @param DOMDocument|XMLNode $doc XML document
     * @param DOMElement|XMLNode $node XML parent node
     * @param string $name Child element name
     * @param string $filename File to read content from
     * @return DOMElement|XMLNode|null XML child node
     */
    public static function createChildFromFile($doc, $node, $name, $filename) {
        // [WIZDAM] FileManager sudah di-import di atas
        $fileManager = new FileManager();
        $contents = $fileManager->readFile($filename);
        
        if ($contents === false) {
            return null;
        }

        // Create the node with file contents
        return self::createChildWithText($doc, $node, $name, $contents);
    }
    
}
?>