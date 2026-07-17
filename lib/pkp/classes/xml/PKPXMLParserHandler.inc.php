<?php
declare(strict_types=1);

/**
 * @file classes/xml/PKPXMLParserHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPXMLParserHandler
 * @ingroup xml
 *
 * @brief Abstract base class for XML parser handlers.
 * All XML parser handler classes must extend this class and implement its methods.
 */

abstract class PKPXMLParserHandler {

    /**
     * Callback function to act as the start element handler.
     * 
     * @param resource $parser XML parser resource
     * @param string $tag Element tag name
     * @param array $attributes Element attributes
     */
    abstract public function startElement($parser, $tag, $attributes);

    /**
     * Callback function to act as the end element handler.
     * 
     * @param resource $parser XML parser resource
     * @param string $tag Element tag name
     */
    abstract public function endElement($parser, $tag);

    /**
     * Callback function to act as the character data handler.
     * 
     * @param resource $parser XML parser resource
     * @param string $data Character data
     */
    abstract public function characterData($parser, $data);

    /**
     * Returns a resulting data structure representing the parsed content.
     * The format of this object is specific to the handler.
     * 
     * @return mixed
     */
    abstract public function getResult();

    /**
     * Perform cleanup operations for this handler.
     */
    public function destroy() {
        // Default implementation does nothing
        // Subclasses can override this to clean up resources
    }
    
}
?>