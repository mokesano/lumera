<?php
declare(strict_types=1);

/**
 * @file classes/core/JSONManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JSONManager
 * @ingroup core
 *
 * @brief to build and manipulate JSON (Javascript Object Notation) objects.
 *
 */

class JSONManager {
    
    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JSONManager() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * PHP4 compatible version of json_encode()
     *
     * @param $value mixed The content to encode.
     * @return string The encoded content.
     */
    public function encode($value = false) {
        // Use the native function if possible
        if (function_exists('json_encode')) return json_encode($value);

        // Otherwise fall back on the JSON services library
        $jsonServices = $this->_getJsonServices();
        return $jsonServices->encode($value);
    }

    /**
     * Decode a JSON string.
     * 
     * @param mixed $json string
     * @return mixed
     */
    public function decode($json) {
        // Use the native function if possible
        if (function_exists('json_decode')) return json_decode($json);

        // Otherwise fall back on the JSON services library
        $jsonServices = $this->_getJsonServices();
        return $jsonServices->decode($json);
    }

    /**
     * Private function to get the JSON services library
     */
    public function _getJsonServices() {
        require_once('lib/pkp/lib/json/JSON.php');
        return new Services_JSON();
    }

}
?>