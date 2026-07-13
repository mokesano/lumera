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
 */

class XMLNode {

    /** @var string|null the element (tag) name */
    public $name;

    /** @var XMLNode|null reference to the parent node (null if this is the root node) */
    public $parent;

    /** @var array the element's attributes */
    public $attributes;

    /** @var string|null the element's value */
    public $value;

    /** @var array references to the XMLNode children of this node */
    public $children;

    /**
     * Constructor.
     */
    public function __construct($name = null) {
        $this->name = $name;
        $this->parent = null;
        $this->attributes = [];
        $this->value = null;
        $this->children = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XMLNode($name = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($name);
    }

    /**
     * Get the element (tag) name, optionally stripping the namespace prefix.
     * 
     * @param bool $includeNamespace
     * @return string|null
     */
    public function getName($includeNamespace = true) {
        $nameStr = (string) $this->name;
        if ($includeNamespace || ($i = strpos($nameStr, ':')) === false) {
            return $this->name;
        }
        return substr($nameStr, $i + 1);
    }

    /**
     * Set the element (tag) name.
     * 
     * @param string|null $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get the parent node.
     * 
     * @return XMLNode|null
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Set the parent node.
     * 
     * @param XMLNode|null $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * Get all attributes of this node.
     * 
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Get the value of a specific attribute.
     * 
     * @param string $name
     * @return string|null
     */
    public function getAttribute($name) {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set the value of a specific attribute.
     * 
     * @param string $name
     * @param string|null $value
     */
    public function setAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }

    /**
     * Set all attributes for this node, replacing any existing ones.
     * 
     * @param array $attributes
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Get the text value of this node.
     * 
     * @return string|null
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Set the text value of this node.
     * 
     * @param string|null $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * Get all child nodes.
     * 
     * @return array
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Find a child node by its name.
     * 
     * @param string|array $name
     * @param int $index
     * @return XMLNode|null
     */
    public function getChildByName($name, $index = 0) {
        if (!is_array($name)) {
            $name = [$name];
        }
        
        foreach ($this->children as $child) {
            if (in_array($child->getName(), $name, true)) {
                if ($index === 0) {
                    return $child;
                }
                $index--;
            }
        }
        return null;
    }

    /**
     * Get the text value of a specific child node.
     * 
     * @param string|array $name
     * @param int $index
     * @return string|null
     */
    public function getChildValue($name, $index = 0) {
        $node = $this->getChildByName($name, $index);
        return $node ? $node->getValue() : null;
    }

    /**
     * Add a child node to this node.
     * 
     * @param XMLNode $node
     */
    public function addChild($node) {
        $this->children[] = $node;
    }

    /**
     * Generate the XML string representation of this node and its children.
     * 
     * @param resource|bool|null $output
     * @return string|null
     */
    public function toXml($output = null) {
        $out = '';

        if ($this->parent === null) {
            $out .= "<?xml version=\"" . $this->getAttribute('version') . "\" encoding=\"UTF-8\"?>\n";
            $type = $this->getAttribute('type');
            if ($type !== null && $type !== '') {
                $url = $this->getAttribute('url');
                if ($url !== null && $url !== '') {
                    $out .= "<!DOCTYPE " . $type . " PUBLIC \"" . $this->getAttribute('dtd') . "\" \"" . $url . "\">";
                } else {
                    $out .= "<!DOCTYPE " . $type . " SYSTEM \"" . $this->getAttribute('dtd') . "\">";
                }
            }
        }

        if ($this->name !== null) {
            $out .= '<' . $this->name;
            foreach ($this->attributes as $attrName => $attrValue) {
                $safeValue = XMLNode::xmlentities((string) $attrValue);
                $out .= " $attrName=\"$safeValue\"";
            }
            if ($this->name !== '!--') {
                $out .= '>';
            }
        }
        $out .= XMLNode::xmlentities((string) $this->value, ENT_NOQUOTES);
        
        foreach ($this->children as $child) {
            if ($output !== null) {
                if ($output === true) {
                    echo $out;
                } else {
                    fwrite($output, $out);
                }
                $out = '';
            }
            $out .= $child->toXml($output);
        }
        
        if ($this->name === '!--') {
            $out .= '-->';
        } elseif ($this->name !== null) {
            $out .= '</' . $this->name . '>';
        }
        
        if ($output !== null) {
            if ($output === true) {
                echo $out;
            } else {
                fwrite($output, $out);
            }
            return null;
        }
        return $out;
    }

    /**
     * Convert special characters to HTML entities for safe XML output.
     * 
     * @param string|null $string
     * @param int $quote_style
     * @return string
     */
    public static function xmlentities($string, $quote_style = ENT_QUOTES) {
        return htmlspecialchars((string) $string, $quote_style, 'UTF-8');
    }

    /**
     * Destructor.
     * Frees memory used by this node and recursively destroys all its children.
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