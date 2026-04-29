<?php
declare(strict_types=1);

/**
 * @file classes/xml/XMLHelper.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLHelper
 * @ingroup xml
 *
 * @brief A class that groups useful XML helper functions.
 * * REFACTORED: Wizdam Edition (PHP 8 Static Method, No References)
 */

class XMLHelper {
    
    /**
     * Take an XML node and generate a nested array.
     * @param $xmlNode DOMDocument|DOMElement
     * @param $keepEmpty boolean whether to keep empty elements, default: false
     * @return array
     */
    public static function xmlToArray($xmlNode, $keepEmpty = false) {
        // Loop through all child nodes of the xml node.
        $resultArray = array();
        
        // WIZDAM FIX: Ensure childNodes exists (safety check)
        if (!isset($xmlNode->childNodes)) return $resultArray;

        foreach ($xmlNode->childNodes as $childNode) {
            if ($childNode->nodeType == 1) { // XML_ELEMENT_NODE
                // WIZDAM FIX: Removed reference assignment
                $childNodes = $childNode->childNodes;
                
                if ($childNodes->length > 1) {
                    // Recurse
                    // WIZDAM FIX: Changed $this-> to self:: for static context
                    // Note: Original code did not pass $keepEmpty to children, maintaining that behavior.
                    $resultArray[$childNode->nodeName] = self::xmlToArray($childNode);
                } elseif ( ($childNode->nodeValue == '' && $keepEmpty) || ($childNode->nodeValue != '') ) {
                    if (isset($resultArray[$childNode->nodeName])) {
                        if (!is_array($resultArray[$childNode->nodeName])) {
                            // We got a second value with the same key,
                            // let's convert this element into an array.
                            $resultArray[$childNode->nodeName] = array($resultArray[$childNode->nodeName]);
                        }

                        // Add the child node to the result array
                        $resultArray[$childNode->nodeName][] = $childNode->nodeValue;
                    } else {
                        // This key occurs for the first time so
                        // set it as a scalar value.
                        $resultArray[$childNode->nodeName] = $childNode->nodeValue;
                    }
                }
                unset($childNodes);
            }
        }

        return $resultArray;
    }
}

?>