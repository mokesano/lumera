<?php
declare(strict_types=1);

/**
 * @file classes/help/HelpTopic.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpTopic
 * @ingroup help
 * @see HelpTopicDAO
 *
 * @brief A HelpTopic object is associated with a single HelpToc object and zero or more HelpTopicSection objects.
 */

class HelpTopic extends DataObject {

    /** The set of sections comprising this topic */
    public $sections; // Mengganti var menjadi public

    /** The set of related topics */
    public $relatedTopics; // Mengganti var menjadi public

    /**
     * Constructor.
     */
    public function __construct() { // Mengganti nama konstruktor
        parent::__construct();
        $this->sections = array();
        $this->relatedTopics = array();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpTopic() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpTopic(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get topic ID (a unique six-digit string).
     * @return string
     */
    public function getId() {
        return $this->getData('id');
    }

    /**
     * Set topic ID (a unique six-digit string).
     * @param $id string
     */
    public function setId($id) {
        $this->setData('id', $id);
    }

    /**
     * Get topic title.
     * @return string
     */
    public function getTitle() {
        return $this->getData('title');
    }

    /**
     * Set topic title.
     * @param $title string
     */
    public function setTitle($title) {
        $this->setData('title', $title);
    }

    /**
     * Get the ID of this topic's toc.
     * @return string
     */
    public function getTocId() {
        return $this->getData('tocId');
    }

    /**
     * Set the ID of this topic's toc.
     * @param $tocId string
     */
    public function setTocId($tocId) {
        $this->setData('tocId', $tocId);
    }

    /**
     * Get the ID of this topic's subtoc.
     * @return string
     */
    public function getSubTocId() {
        return $this->getData('subTocId');
    }

    /**
     * Set the ID of this topic's subtoc.
     * @param $subTocId string
     */
    public function setSubTocId($subTocId) {
        $this->setData('subTocId', $subTocId);
    }

    /**
     * Get the set of sections comprising this topic's contents.
     * @return array the sections in order of appearance
     */
    public function getSections() { // Menghapus reference (&)
        return $this->sections;
    }

    /**
     * Associate a section with this topic.
     * Sections are added in the order they appear in the topic (i.e., FIFO).
     * @param $section HelpTopicSection
     */
    public function addSection($section) { // Menghapus reference (&)
        $this->sections[] = $section;
    }

    /**
     * Get the set of related topics.
     * @return array the related topics
     */
    public function getRelatedTopics() { // Menghapus reference (&)
        return $this->relatedTopics;
    }

    /**
     * Add a related topic
     * @param $relatedTopic HelpTopic
     */
    public function addRelatedTopic($relatedTopic) { // Menghapus reference (&)
        $this->relatedTopics[] = $relatedTopic;
    }
}

?>