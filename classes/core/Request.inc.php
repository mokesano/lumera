<?php
declare(strict_types=1);

/**
 * @file classes/core/Request.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Request
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<journal_id>/<page_name>/<operation_name>/<arguments...>
 * <journal_id> is assumed to be "index" for top-level site requests.
 */

import('lib.pkp.classes.core.PKPRequest');

class Request extends PKPRequest {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Request() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Request(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PKPPageRouter::getRequestedContextPath()
     * @deprecated
     */
    public static function getRequestedJournalPath() {
        static $journal;
        $instance = PKPRequest::_checkThis();

        if (!isset($journal)) {
            $journal = $instance->_delegateToRouter('getRequestedContextPath', 1);
            HookRegistry::dispatch('Request::getRequestedJournalPath', array(&$journal));
        }

        return $journal;
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PKPPageRouter::getContext()
     * @deprecated
     * @return Journal|null
     */
    public static function getJournal() {
        $instance = PKPRequest::_checkThis();
        $returner = $instance->_delegateToRouter('getContext', 1);
        return $returner;
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PKPPageRouter::getRequestedContextPath()
     * @deprecated
     */
    public static function getRequestedContextPath($contextLevel = null) {
        $instance = PKPRequest::_checkThis();

        // Emulate the old behavior of getRequestedContextPath for backwards compatibility.
        if (is_null($contextLevel)) {
            return $instance->_delegateToRouter('getRequestedContextPaths');
        } else {
            return array($instance->_delegateToRouter('getRequestedContextPath', $contextLevel));
        }
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PKPPageRouter::getContext()
     * 
     * Note: Adds an optional $level parameter not present in
     * PKPRequest::getContext(). This is LSP-compatible because the added
     * parameter is optional — callers using the parent signature are unaffected.
     * @deprecated
     */
    public static function getContext($level = 1) {
        $instance = PKPRequest::_checkThis();
        $returner = $instance->_delegateToRouter('getContext', $level);
        return $returner;
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PKPPageRouter::getContextByName()
     * @param mixed $contextName
     * @deprecated
     */
    public static function getContextByName($contextName) {
        $instance = PKPRequest::_checkThis();
        $returner = $instance->_delegateToRouter('getContextByName', $contextName);
        return $returner;
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PKPPageRouter::url()
     * @deprecated
     */
    public static function url($journalPath = null, $page = null, $op = null, $path = null,
            $params = null, $anchor = null, $escape = false) {
        $instance = PKPRequest::_checkThis();
        return $instance->_delegateToRouter('url', $journalPath, $page, $op, $path,
            $params, $anchor, $escape);
    }

    /**
     * [DEPRICATED] Backward compatibility.
     * @see PageRouter::redirectHome()
     * @deprecated
     */
    public static function redirectHome() {
        $instance = PKPRequest::_checkThis();
        return $instance->_delegateToRouter('redirectHome');
    }
    
}
?>