<?php
declare(strict_types=1);

/**
 * @file classes/xml/XMLNode.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLNode
 * @ingroup xml
 *
 * @brief Default handler for XMLParser returning a simple DOM-style object.
 * This handler parses an XML document into a tree structure of XMLNode objects.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility, Static Methods)
 */

class XMLNode {

    /** @var string the element (tag) name */
    public $name;

    /** @var XMLNode reference to the parent node (null if this is the root node) */
    public $parent;

    /** @var array the element's attributes */
    public $attributes;

    /** @var string the element's value */
    public $value;

    /** @var array references to the XMLNode children of this node */
    public $children;

    /**
     * Constructor.
     * @param $name element/tag name
     */
    public function __construct($name = null) {
        $this->name = $name;
        $this->parent = null;
        $this->attributes = array();
        $this->value = null;
        $this->children = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLNode($name = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::XMLNode(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($name);
    }

    /**
     * @param $includeNamespace boolean
     * @return string
     */
    public function getName($includeNamespace = true) {
        if (
            $includeNamespace ||
            ($i = strpos((string)$this->name, ':')) === false
        ) return $this->name;
        return substr($this->name, $i+1);
    }

    /**
     * @param $name string
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return XMLNode
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param $parent XMLNode
     */
    public function setParent($parent) {
        // WIZDAM FIX: No reference needed for object assignment
        $this->parent = $parent;
    }

    /**
     * @return array all attributes
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param $name string attribute name
     * @return string attribute value
     */
    public function getAttribute($name) {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * @param $name string attribute name
     * @param value string attribute value
     */
    public function setAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }

    /**
     * @param $attributes array
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param $value string
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return array this node's children (XMLNode objects)
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @param $name
     * @param $index
     * @return XMLNode|null the ($index+1)th child matching the specified name
     */
    public function getChildByName($name, $index = 0) {
        if (!is_array($name)) $name = array($name);
        
        // WIZDAM FIX: Improved loop logic
        foreach ($this->children as $child) {
            if (in_array($child->getName(), $name)) {
                if ($index == 0) {
                    return $child;
                } else {
                    $index--;
                }
            }
        }
        return null;
    }

    /**
     * Get the value of a child node.
     * @param $name String name of node
     * @param $index Optional integer index of child node to find
     */
    public function getChildValue($name, $index = 0) {
        $node = $this->getChildByName($name);
        if ($node) {
            return $node->getValue();
        } else {
            return null;
        }
    }

    /**
     * @param $node XMLNode the child node to add
     */
    public function addChild($node) {
        // WIZDAM FIX: Objects are passed by identifier, no reference needed
        $this->children[] = $node;
    }

    /**
     * @param $output file handle to write to, or true for stdout, or null if XML to be returned as string
     * @return string|null
     */
    public function toXml($output = null) {
        $out = '';

        if ($this->parent === null) {
            // This is the root node. Output information about the document.
            $out .= "<?xml version=\"" . $this->getAttribute('version') . "\" encoding=\"UTF-8\"?>\n";
            if ($this->getAttribute('type') != '') {
                if ($this->getAttribute('url') != '') {
                    $out .= "<!DOCTYPE " . $this->getAttribute('type') . " PUBLIC \"" . $this->getAttribute('dtd') . "\" \"" . $this->getAttribute('url') . "\">";
                } else {
                    $out .= "<!DOCTYPE " . $this->getAttribute('type') . " SYSTEM \"" . $this->getAttribute('dtd') . "\">";
                }
            }
        }

        if ($this->name !== null) {
            $out .= '<' . $this->name;
            foreach ($this->attributes as $name => $value) {
                $value = XMLNode::xmlentities($value);
                $out .= " $name=\"$value\"";
            }
            if ($this->name !== '!--') {
                $out .= '>';
            }
        }
        // WIZDAM FIX: Ensure value is string
        $out .= XMLNode::xmlentities((string)$this->value, ENT_NOQUOTES);
        
        foreach ($this->children as $child) {
            if ($output !== null) {
                if ($output === true) echo $out;
                else fwrite ($output, $out);
                $out = '';
            }
            $out .= $child->toXml($output);
        }
        if ($this->name === '!--') {
            $out .= '-->';
        } else if ($this->name !== null) {
            $out .= '</' . $this->name . '>';
        }
        if ($output !== null) {
            if ($output === true) echo $out;
            else fwrite ($output, $out);
            return null;
        }
        return $out;
    }

    /**
     * WIZDAM FIX: Static method as it is called via XMLNode::xmlentities
     * @param $string string
     * @param $quote_style int
     * @return string
     */
    public static function xmlentities($string, $quote_style=ENT_QUOTES) {
        return htmlspecialchars((string)$string, $quote_style, 'UTF-8');
    }

    /**
     * Destructor.
     * Frees memory used by this node and all its children.
     */
    public function destroy() {
        unset($this->value, $this->attributes, $this->parent, $this->name);
        foreach ($this->children as $child) {
            $child->destroy();
        }
        unset($this->children);
    }
}

?>