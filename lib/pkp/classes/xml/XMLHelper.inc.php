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
 */

class XMLHelper {
    
    /**
     * Take an XML node and generate a nested array.
     * @param mixed $xmlNode DOMDocument|DOMElement
     * @param $keepEmpty boolean whether to keep empty elements, default: false
     * @return array
     */
    public static function xmlToArray($xmlNode, $keepEmpty = false) {
        $resultArray = array();
        
        if (!isset($xmlNode->childNodes)) return $resultArray;

        foreach ($xmlNode->childNodes as $childNode) {
            if ($childNode->nodeType == 1) {
                $childNodes = $childNode->childNodes;
                
                if ($childNodes->length > 1) {
                    // Recurse
                    $resultArray[$childNode->nodeName] = self::xmlToArray($childNode);
                } elseif ( ($childNode->nodeValue == '' && $keepEmpty) || ($childNode->nodeValue != '') ) {
                    if (isset($resultArray[$childNode->nodeName])) {
                        if (!is_array($resultArray[$childNode->nodeName])) {
                            $resultArray[$childNode->nodeName] = array($resultArray[$childNode->nodeName]);
                        }

                        // Add the child node to the result array
                        $resultArray[$childNode->nodeName][] = $childNode->nodeValue;
                    } else {
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