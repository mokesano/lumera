<?php
declare(strict_types=1);

/**
 * @file classes/xml/XMLComment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLComment
 * @ingroup xml
 *
 * @brief Extension of XMLNode for a simple DOM-style comment.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility)
 */

import ('lib.pkp.classes.xml.XMLNode');

class XMLComment extends XMLNode {

    /**
     * Constructor.
     */
    public function __construct() {
        // Manually initialize properties as in legacy code
        $this->name = '!--';
        $this->parent = null;
        $this->attributes = array();
        $this->value = null;
        $this->children = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLComment() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::XMLComment(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * @param $includeNamespace boolean
     * @return string|bool
     */
    public function getName($includeNamespace = true) {
        return false;
    }

    /**
     * @param $name string
     */
    public function setName($name) {
        assert(false);
    }

    /**
     * @return array all attributes
     */
    public function getAttributes() {
        return array();
    }

    /**
     * @param $name string attribute name
     * @return string attribute value
     */
    public function getAttribute($name) {
        return null;
    }

    /**
     * @param $name string attribute name
     * @param value string attribute value
     */
    public function setAttribute($name, $value) {
        assert(false);
    }

    /**
     * @param $attributes array
     */
    public function setAttributes($attributes) {
        assert(false);
    }

    /**
     * @return array this node's children (XMLNode objects)
     */
    public function getChildren() {
        return array();
    }

    /**
     * @param $name
     * @param $index
     * @return XMLNode|null the ($index+1)th child matching the specified name
     */
    public function getChildByName($name, $index = 0) {
        return null;
    }

    /**
     * Get the value of a child node.
     * @param $name String name of node
     * @param $index Optional integer index of child node to find
     * @return string|null
     */
    public function getChildValue($name, $index = 0) {
        return null;
    }

    /**
     * @param $node XMLNode the child node to add
     */
    public function addChild($node) {
        assert(false);
    }
}
?>