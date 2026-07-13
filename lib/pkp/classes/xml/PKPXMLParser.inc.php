<?php
declare(strict_types=1);

/**
 * @file classes/xml/PKPXMLParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPXMLParser
 * @ingroup xml
 *
 * @brief Generic class for parsing an XML document into a data structure.
 */

// The default character encodings
define('XML_PARSER_SOURCE_ENCODING', Config::getVar('i18n', 'client_charset'));
define('XML_PARSER_TARGET_ENCODING', Config::getVar('i18n', 'client_charset'));

import('lib.pkp.classes.xml.PKPXMLParserHandler');
import('lib.pkp.classes.xml.XMLParserDOMHandler');

class PKPXMLParser {

    /** @var PKPXMLParserHandler|null Handler instance */
    public $handler;

    /** @var array List of error strings */
    public $errors = [];

    /**
     * Constructor.
     */
    public function __construct() {
        // WIZDAM: magic_quotes_runtime functions are removed in PHP 8.
        // Logic removed as it is no longer relevant/possible.
    }

    /**
     * [SHIM] Backward Compatibility
     * @deprecated
     */
    public function PKPXMLParser() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . ". Please refactor to parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Parse XML text string.
     * @param string $text XML content
     * @return mixed Result from handler
     */
    public function parseText($text) {
        $parser = $this->createParser();
        $localHandlerCreated = false;

        if ($this->handler === null) {
            // Use default handler for parsing
            $this->handler = new XMLParserDOMHandler();
            $localHandlerCreated = true;
        }

        /** @var \XMLParser|resource $parser */
        xml_set_element_handler($parser, [$this->handler, 'startElement'], [$this->handler, 'endElement']);
        xml_set_character_data_handler($parser, [$this->handler, 'characterData']);

        // If the string contains non-UTF8 characters, convert it to UTF-8 for parsing
        if (Config::getVar('i18n', 'charset_normalization') === 'On' && !PKPString::utf8_compliant($text)) {
            $text = PKPString::utf8_normalize($text);
            $text = PKPString::utf8_bad_strip($text);
            $text = strtr($text, PKPString::getHTMLEntities());
        }

        // Strip any invalid ASCII control characters
        $text = PKPString::utf8_strip_ascii_ctrl($text);

        if (!xml_parse($parser, $text, true)) {
            $this->addError(xml_error_string(xml_get_error_code($parser)));
        }

        $result = $this->handler->getResult();
        $this->destroyParser($parser);

        // Cleanup local handler if created locally
        if ($localHandlerCreated) {
            $this->handler->destroy();
            $this->handler = null;
        }

        return $result;
    }

    /**
     * Parse an XML file using the specified handler.
     * If no handler has been specified, XMLParserDOMHandler is used by default,
     * returning a tree structure representing the document.
     * @param string $file Full path to the XML file
     * @param callable|null $dataCallback Optional callback for data processing
     * @return mixed|false Result from handler or false on failure
     */
    public function parse($file, $dataCallback = null) {
        $parser = $this->createParser();
        $localHandlerCreated = false;

        if ($this->handler === null) {
            // Use default handler for parsing
            $this->handler = new XMLParserDOMHandler();
            $localHandlerCreated = true;
        }

        /** @var \XMLParser|resource $parser */
        xml_set_element_handler($parser, [$this->handler, 'startElement'], [$this->handler, 'endElement']);
        xml_set_character_data_handler($parser, [$this->handler, 'characterData']);

        import('lib.pkp.classes.file.FileWrapper');
        $wrapper = FileWrapper::wrapper($file);

        // Handle responses of various types (redirects)
        while (true) {
            $newWrapper = $wrapper->open();
            if (is_object($newWrapper)) {
                // Follow a redirect
                $wrapper = $newWrapper;
            } elseif ($newWrapper === false) {
                // Could not open resource -- error
                $this->destroyParser($parser);
                if ($localHandlerCreated) {
                    $this->handler->destroy();
                    $this->handler = null;
                }
                return false;
            } else {
                // OK, we've found the end result
                break;
            }
        }

        if (!$wrapper) {
            $this->destroyParser($parser);
            if ($localHandlerCreated) {
                $this->handler->destroy();
                $this->handler = null;
            }
            return false;
        }

        if ($dataCallback !== null) {
            call_user_func($dataCallback, 'open', $wrapper);
        }

        while (!$wrapper->eof() && ($data = $wrapper->read()) !== false) {
            // If the string contains non-UTF8 characters, convert it to UTF-8 for parsing
            if (Config::getVar('i18n', 'charset_normalization') === 'On' && !PKPString::utf8_compliant($data)) {
                $utf8_last = PKPString::substr($data, PKPString::strlen($data) - 1);

                // If the string ends in a "bad" UTF-8 character, maybe it's truncated
                while (!$wrapper->eof() && PKPString::utf8_bad_find($utf8_last) === 0) {
                    // Read another chunk of data
                    $data .= $wrapper->read();
                    $utf8_last = PKPString::substr($data, PKPString::strlen($data) - 1);
                }

                $data = PKPString::utf8_normalize($data);
                $data = PKPString::utf8_bad_strip($data);
                $data = strtr($data, PKPString::getHTMLEntities());
            }

            // Strip any invalid ASCII control characters
            $data = PKPString::utf8_strip_ascii_ctrl($data);

            if ($dataCallback !== null) {
                call_user_func($dataCallback, 'parse', $wrapper, $data);
            }

            if (!xml_parse($parser, $data, $wrapper->eof())) {
                $this->addError(xml_error_string(xml_get_error_code($parser)));
            }
        }

        if ($dataCallback !== null) {
            call_user_func($dataCallback, 'close', $wrapper);
        }

        $wrapper->close();

        $result = $this->handler->getResult();
        $this->destroyParser($parser);

        // Cleanup local handler if created locally
        if ($localHandlerCreated) {
            $this->handler->destroy();
            $this->handler = null;
        }

        return $result;
    }

    /**
     * Add an error to the current error list.
     * @param string $error Error message
     */
    public function addError($error) {
        $this->errors[] = $error;
    }

    /**
     * Get the current list of errors.
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Determine whether or not the parser encountered an error (false)
     * or completed successfully (true).
     * @return bool
     */
    public function getStatus() {
        return empty($this->errors);
    }

    /**
     * Set the handler to use for parse(...).
     * @param PKPXMLParserHandler $handler
     */
    public function setHandler($handler) {
        $this->handler = $handler;
    }

    /**
     * Parse XML data using xml_parse_into_struct and return data in an array.
     * This is best suited for XML documents with fairly simple structure.
     * @param string $text XML data
     * @param array $tagsToMatch Optional, if set tags not in the array will be skipped
     * @return array A struct of the form ($TAG => array('attributes' => array(...), 'value' => $VALUE), ...)
     */
    public function parseTextStruct($text, $tagsToMatch = []) {
        $parser = $this->createParser();
        $values = [];
        $tags = [];

        /** @var \XMLParser|resource $parser */
        xml_parse_into_struct($parser, $text, $values, $tags);
        $this->destroyParser($parser);

        $data = [];

        // Clean up data struct, removing undesired tags if necessary
        foreach ($tags as $key => $indices) {
            if (!empty($tagsToMatch) && !in_array($key, $tagsToMatch, true)) {
                continue;
            }

            $data[$key] = [];

            foreach ($indices as $index) {
                if (!isset($values[$index]['type']) || ($values[$index]['type'] !== 'open' && $values[$index]['type'] !== 'complete')) {
                    continue;
                }

                $data[$key][] = [
                    'attributes' => $values[$index]['attributes'] ?? [],
                    'value' => isset($values[$index]['value']) ? trim($values[$index]['value']) : ''
                ];
            }
        }

        return $data;
    }

    /**
     * Parse an XML file using xml_parse_into_struct and return data in an array.
     * This is best suited for XML documents with fairly simple structure.
     * @param string $file Full path to the XML file
     * @param array $tagsToMatch Optional, if set tags not in the array will be skipped
     * @return array|false A struct or false on failure
     */
    public function parseStruct($file, $tagsToMatch = []) {
        import('lib.pkp.classes.file.FileWrapper');
        $wrapper = FileWrapper::wrapper($file);
        $fileContents = $wrapper->contents();
        if (!$fileContents) {
            return false;
        }
        return $this->parseTextStruct($fileContents, $tagsToMatch);
    }

    /**
     * Initialize a new XML parser resource.
     * @return resource XML parser resource
     */
    public function createParser() {
        $parser = xml_parser_create(XML_PARSER_SOURCE_ENCODING);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, XML_PARSER_TARGET_ENCODING);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        return $parser;
    }

    /**
     * Destroy XML parser resource.
     * @param \PKPXMLParser|resource|null $parser XML parser instance
     */
    public function destroyParser($parser) {
        if (is_resource($parser)) {
            // PHP 7.x: Explicit free untuk resource
            /** @var \XMLParser|resource $parser */
            xml_parser_free($parser);
        }
        // PHP 8+: Object akan di-free otomatis oleh GC
        unset($parser);
    }

    /**
     * Perform required clean up for this object.
     */
    public function destroy() {
        // WIZDAM: No magic quotes to restore in PHP 8+
        $this->handler = null;
        $this->errors = [];
    }

}
?>