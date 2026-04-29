<?php
declare(strict_types=1);

/**
 * @file classes/db/XMLDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLDAO
 * @ingroup db
 *
 * @brief Operations for retrieving and modifying objects from an XML data source.
 */

import('lib.pkp.classes.xml.XMLParser');

class XMLDAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function XMLDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor XMLDAO(). Please refactor to use __construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Parse an XML file and return data in an object.
     * @see xml.XMLParser::parse()
     * @param $file string
     * @return mixed
     */
    public function parse($file) {
        $parser = new PKPXMLParser();
        $data = $parser->parse($file);
        $parser->destroy();
        return $data;
    }

    /**
     * Parse an XML file with the specified handler and return data in an object
     * @see xml.XMLParser::parse()
     * @param $file string
     * @param $handler object Reference to the handler to use with the parser.
     * @return mixed
     */
    public function parseWithHandler($file, $handler) {
        $parser = new PKPXMLParser();
        $parser->setHandler($handler);
        $data = $parser->parse($file);
        $parser->destroy();
        return $data;
    }

    /**
     * Parse an XML file and return data in an array.
     * @see xml.XMLParser::parseStruct()
     * @param $file string
     * @param $tagsToMatch array
     * @return array
     */
    public function parseStruct($file, $tagsToMatch = array()) {
        $parser = new PKPXMLParser();
        $data = $parser->parseStruct($file, $tagsToMatch);
        $parser->destroy();
        return $data;
    }
}

?>