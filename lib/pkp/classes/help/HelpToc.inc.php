<?php
declare(strict_types=1);

/**
 * @file classes/help/HelpToc.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpToc
 * @ingroup help
 * @see HelpTocDAO
 *
 * @brief Help table of contents class.
 * A HelpToc object is associated with zero or more HelpTopic objects.
 */

class HelpToc extends DataObject {

    /** The list of topics belonging to this toc */
    public $topics; // Mengganti var menjadi public

    /** The list of breadcrumbs belonging to this toc */
    public $breadcrumbs; // Mengganti var menjadi public

    /**
     * Constructor.
     */
    public function __construct() { // Mengganti nama konstruktor
        parent::__construct();
        $this->topics = array();
        $this->breadcrumbs = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpToc() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpToc(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get toc ID (a unique six-digit string).
     * @return string
     */
    public function getId() {
        return $this->getData('id');
    }

    /**
     * Set toc ID (a unique six-digit string).
     * @param $id int
     */
    public function setId($id) {
        $this->setData('id', $id);
    }

    /**
     * Get toc title.
     * @return string
     */
    public function getTitle() {
        return $this->getData('title');
    }

    /**
     * Set toc title.
     * @param $title string
     */
    public function setTitle($title) {
        $this->setData('title', $title);
    }

    /**
     * Get the ID of the topic one-level up from this one.
     * @return string
     */
    public function getParentTopicId() {
        return $this->getData('parentTopicId');
    }

    /**
     * Set the ID of the topic one-level up from this one.
     * @param $parentTopicId string
     */
    public function setParentTopicId($parentTopicId) {
        $this->setData('parentTopicId', $parentTopicId);
    }

    /**
     * Get the set of topics in this table of contents.
     * @return array the topics in order of appearance
     */
    public function getTopics() { // Menghapus reference (&)
        return $this->topics;
    }

    /**
     * Associate a topic with this toc.
     * Topics are added in the order they appear in the toc (i.e., FIFO).
     * @param $topic HelpTopic
     */
    public function addTopic($topic) { // Menghapus reference (&)
        $this->topics[] = $topic;
    }

    /**
     * Get breadcrumbs.
     * @return array
     */
    public function getBreadcrumbs() { // Menghapus reference (&)
        return $this->breadcrumbs;
    }

    /**
     * Set breadcrumbs.
     * @param $name string
     * @param $url string
     */
    public function addBreadcrumb($name,$url) {
        $this->breadcrumbs[$name] = $url;
    }
}

?>