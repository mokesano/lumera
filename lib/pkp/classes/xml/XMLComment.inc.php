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
 */

import('lib.pkp.classes.xml.XMLNode');

class XMLComment extends XMLNode {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('!--');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLComment() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . ". Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the name of the comment node.
     * Comments do not have standard element names.
     * 
     * @param bool $includeNamespace
     * @return false
     */
    public function getName($includeNamespace = true) {
        return false;
    }

    /**
     * Prevent setting a name on a comment node.
     * 
     * @param mixed $name
     * @throws \BadMethodCallException
     */
    public function setName($name) {
        throw new \BadMethodCallException('Cannot set a name on an XML comment node.');
    }

    /**
     * Get attributes (comments do not have attributes).
     * 
     * @return array
     */
    public function getAttributes() {
        return [];
    }

    /**
     * Get a specific attribute (comments do not have attributes).
     * 
     * @param string $name
     * @return null
     */
    public function getAttribute($name) {
        return null;
    }

    /**
     * Prevent setting an attribute on a comment node.
     * 
     * @param string $name
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function setAttribute($name, $value) {
        throw new \BadMethodCallException('Cannot set attribute on an XML comment node.');
    }

    /**
     * Prevent setting attributes on a comment node.
     * 
     * @param array $attributes
     * @throws \BadMethodCallException
     */
    public function setAttributes($attributes) {
        throw new \BadMethodCallException('Cannot set attributes on an XML comment node.');
    }

    /**
     * Get child nodes (comments do not have children).
     * 
     * @return array
     */
    public function getChildren() {
        return [];
    }

    /**
     * Get a child node by name (comments do not have children).
     * 
     * @param string|array $name
     * @param int $index
     * @return null
     */
    public function getChildByName($name, $index = 0) {
        return null;
    }

    /**
     * Get the value of a child node (comments do not have children).
     * 
     * @param string|array $name
     * @param int $index
     * @return null
     */
    public function getChildValue($name, $index = 0) {
        return null;
    }

    /**
     * Prevent adding a child node to a comment.
     * 
     * @param mixed $node
     * @throws \BadMethodCallException
     */
    public function addChild($node) {
        throw new \BadMethodCallException('Cannot add child nodes to an XML comment.');
    }

}
?>