<?php
declare(strict_types=1);

/**
 * @file classes/help/HelpTopicSection.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpTopicSection
 * @ingroup help
 *
 * @brief Help section class, designated a subsection of a topic.
 * A HelpTopicSection is associated with a single HelpTopic.
 */

class HelpTopicSection extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() { // Mengganti nama konstruktor
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpTopicSection() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HelpTopicSection(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get section title.
     * @return string
     */
    public function getTitle() {
        return $this->getData('title');
    }

    /**
     * Set section title.
     * @param $title string
     */
    public function setTitle($title) {
        $this->setData('title', $title);
    }

    /**
     * Get section content (assumed to be in HTML format).
     * @return string
     */
    public function getContent() {
        return $this->getData('content');
    }

    /**
     * Set section content.
     * @param $content string
     */
    public function setContent($content) {
        $this->setData('content', $content);
    }
}

?>