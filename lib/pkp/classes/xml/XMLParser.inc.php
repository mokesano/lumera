<?php
declare(strict_types=1);

/**
 * @defgroup xml
 */

/**
 * @file classes/xml/PKPXMLParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLParser
 * @ingroup xml
 *
 * @brief Generic class for parsing an XML document into a data structure.
 * * REFACTORED: Wizdam Edition (PHP 8 Compatibility, No Magic Quotes, Modern XML Handlers)
 */

// The default character encodings
define('XML_PARSER_SOURCE_ENCODING', Config::getVar('i18n', 'client_charset'));
define('XML_PARSER_TARGET_ENCODING', Config::getVar('i18n', 'client_charset'));

import('lib.pkp.classes.xml.XMLParserDOMHandler');

class PKPXMLParser {

    /** @var int original magic_quotes_runtime setting (Deprecated/Removed in PHP 8) */
    public $magicQuotes;

    /** @var $handler object instance of XMLParserHandler */
    public $handler;

    /** @var $errors array List of error strings */
    public $errors;

    /**
     * Constructor.
     */
    public function __construct() {
        // WIZDAM FIX: magic_quotes_runtime functions are removed in PHP 8.
        // Logic removed as it is no longer relevant/possible.
        $this->errors = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLParser() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::XMLParser(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    public function parseText($text) {
        $parser = $this->createParser();

        if (!isset($this->handler)) {
            // Use default handler for parsing
            $handler = new XMLParserDOMHandler();
            $this->setHandler($handler);
        }

        // WIZDAM FIX: xml_set_object is deprecated. Use array callbacks.
        xml_set_element_handler($parser, [$this->handler, "startElement"], [$this->handler, "endElement"]);
        xml_set_character_data_handler($parser, [$this->handler, "characterData"]);

        // if the string contains non-UTF8 characters, convert it to UTF-8 for parsing
        if ( Config::getVar('i18n', 'charset_normalization') == 'On' && !PKPString::utf8_compliant($text) ) {

            $text = PKPString::utf8_normalize($text);

            // strip any invalid UTF-8 sequences
            $text = PKPString::utf8_bad_strip($text);

            // convert named entities to numeric entities
            $text = strtr($text, PKPString::getHTMLEntities());
        }

        // strip any invalid ASCII control characters
        $text = PKPString::utf8_strip_ascii_ctrl($text);

        if (!xml_parse($parser, $text, true)) {
            $this->addError(xml_error_string(xml_get_error_code($parser)));
        }

        $result = $this->handler->getResult();
        $this->destroyParser($parser);
        
        // Cleanup local handler if created locally
        // Note: Logic logic suggests checking if $handler variable exists in scope
        if (isset($handler)) {
            $handler->destroy();
            unset($handler);
        }
        return $result;
    }

    /**
     * Parse an XML file using the specified handler.
     * If no handler has been specified, XMLParserDOMHandler is used by default, returning a tree structure representing the document.
     * @param $file string full path to the XML file
     * @param $dataCallback mixed Optional callback for data handling: function dataCallback($operation, $wrapper, $data = null)
     * @return object actual return type depends on the handler
     */
    public function parse($file, $dataCallback = null) {
        $parser = $this->createParser();

        if (!isset($this->handler)) {
            // Use default handler for parsing
            $handler = new XMLParserDOMHandler();
            $this->setHandler($handler);
        }

        // WIZDAM FIX: xml_set_object is deprecated.
        xml_set_element_handler($parser, [$this->handler, "startElement"], [$this->handler, "endElement"]);
        xml_set_character_data_handler($parser, [$this->handler, "characterData"]);

        import('lib.pkp.classes.file.FileWrapper');
        $wrapper = FileWrapper::wrapper($file);

        // Handle responses of various types
        while (true) {
            $newWrapper = $wrapper->open();
            if (is_object($newWrapper)) {
                // Follow a redirect
                unset($wrapper);
                $wrapper = $newWrapper;
                unset ($newWrapper);
            } elseif (!$newWrapper) {
                // Could not open resource -- error
                return false;
            } else {
                // OK, we've found the end result
                break;
            }
        }

        if (!$wrapper) {
            return false;
        }

        if ($dataCallback) call_user_func($dataCallback, 'open', $wrapper);

        while (!$wrapper->eof() && ($data = $wrapper->read()) !== false) {

            // if the string contains non-UTF8 characters, convert it to UTF-8 for parsing
            if ( Config::getVar('i18n', 'charset_normalization') == 'On' && !PKPString::utf8_compliant($data) ) {

                $utf8_last = PKPString::substr($data, PKPString::strlen($data) - 1);

                // if the string ends in a "bad" UTF-8 character, maybe it's truncated
                while (!$wrapper->eof() && PKPString::utf8_bad_find($utf8_last) === 0) {
                    // read another chunk of data
                    $data .= $wrapper->read();
                    $utf8_last = PKPString::substr($data, PKPString::strlen($data) - 1);
                }

                $data = PKPString::utf8_normalize($data);

                // strip any invalid UTF-8 sequences
                $data = PKPString::utf8_bad_strip($data);

                // convert named entities to numeric entities
                $data = strtr($data, PKPString::getHTMLEntities());
            }

            // strip any invalid ASCII control characters
            $data = PKPString::utf8_strip_ascii_ctrl($data);

            if ($dataCallback) call_user_func($dataCallback, 'parse', $wrapper, $data);
            if (!xml_parse($parser, $data, $wrapper->eof())) {
                $this->addError(xml_error_string(xml_get_error_code($parser)));
            }
        }

        if ($dataCallback) call_user_func($dataCallback, 'close', $wrapper);
        $wrapper->close();
        
        $result = $this->handler->getResult();
        $this->destroyParser($parser);
        
        if (isset($handler)) {
            $handler->destroy();
            unset($handler);
        }
        return $result;
    }

    /**
     * Add an error to the current error list
     * @param $error string
     */
    public function addError($error) {
        array_push($this->errors, $error);
    }

    /**
     * Get the current list of errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Determine whether or not the parser encountered an error (false)
     * or completed successfully (true)
     * @return boolean
     */
    public function getStatus() {
        return empty($this->errors);
    }

    /**
     * Set the handler to use for parse(...).
     * @param $handler XMLParserHandler
     */
    public function setHandler($handler) {
        $this->handler = $handler;
    }

    /**
     * Parse XML data using xml_parse_into_struct and return data in an array.
     * This is best suited for XML documents with fairly simple structure.
     * @param $text string XML data
     * @param $tagsToMatch array optional, if set tags not in the array will be skipped
     * @return array a struct of the form ($TAG => array('attributes' => array( ... ), 'value' => $VALUE), ... )
     */
    public function parseTextStruct(&$text, $tagsToMatch = array()) {
        $parser = $this->createParser();
        xml_parse_into_struct($parser, $text, $values, $tags);
        $this->destroyParser($parser);

        $data = array(); // Initialize array

        // Clean up data struct, removing undesired tags if necessary
        foreach ($tags as $key => $indices) {
            if (!empty($tagsToMatch) && !in_array($key, $tagsToMatch)) {
                continue;
            }

            $data[$key] = array();

            foreach ($indices as $index) {
                if (!isset($values[$index]['type']) || ($values[$index]['type'] != 'open' && $values[$index]['type'] != 'complete')) {
                    continue;
                }

                $data[$key][] = array(
                    'attributes' => isset($values[$index]['attributes']) ? $values[$index]['attributes'] : array(),
                    'value' => isset($values[$index]['value']) ? trim($values[$index]['value']) : ''
                );
            }
        }

        return $data;
    }

    /**
     * Parse an XML file using xml_parse_into_struct and return data in an array.
     * This is best suited for XML documents with fairly simple structure.
     * @param $file string full path to the XML file
     * @param $tagsToMatch array optional, if set tags not in the array will be skipped
     * @return array a struct of the form ($TAG => array('attributes' => array( ... ), 'value' => $VALUE), ... )
     */
    public function parseStruct($file, $tagsToMatch = array()) {
        import('lib.pkp.classes.file.FileWrapper');
        $wrapper = FileWrapper::wrapper($file);
        $fileContents = $wrapper->contents();
        if (!$fileContents) {
            return false;
        }
        $returner = $this->parseTextStruct($fileContents, $tagsToMatch);
        return $returner;
    }

    /**
     * Initialize a new XML parser.
     * @return XMLParser|resource
     */
    public function createParser() {
        $parser = xml_parser_create(XML_PARSER_SOURCE_ENCODING);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, XML_PARSER_TARGET_ENCODING);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        return $parser;
    }

    /**
     * Destroy XML parser.
     * @param $parser XMLParser|resource
     */
    public function destroyParser($parser) {
        xml_parser_free($parser);
        unset($parser);
    }

    /**
     * Perform required clean up for this object.
     */
    public function destroy() {
        // WIZDAM FIX: No magic quotes to restore
    }

}

/**
 * Interface for handler class used by XMLParser.
 * All XML parser handler classes must implement these methods.
 */
class PKPXMLParserHandler {

    /**
     * Callback function to act as the start element handler.
     */
    public function startElement($parser, $tag, $attributes) {
        // WIZDAM FIX: Removed reference from $parser
    }

    /**
     * Callback function to act as the end element handler.
     */
    public function endElement($parser, $tag) {
        // WIZDAM FIX: Removed reference from $parser
    }

    /**
     * Callback function to act as the character data handler.
     */
    public function characterData($parser, $data) {
        // WIZDAM FIX: Removed reference from $parser
    }

    /**
     * Returns a resulting data structure representing the parsed content.
     * The format of this object is specific to the handler.
     * @return mixed
     */
    public function getResult() {
        return null;
    }
}

?>