<?php
/**
 * @file classes/submission/Genre.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Genre
 * @ingroup submission
 * @see GenreDAO
 *
 * @brief Basic class describing a genre (e.g., "Article Text", "Data Set", "Bibliography").
 */

class Genre extends DataObject {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the genre ID.
     * @return int
     */
    public function getId() {
        return $this->getData('id');
    }

    /**
     * Set the genre ID.
     * @param int $id
     */
    public function setId($id) {
        $this->setData('id', (int) $id);
    }

    /**
     * Get the genre's localized name.
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null) {
        return $this->getData('name', $locale);
    }

    /**
     * Set the genre's localized name.
     * @param string $name
     * @param string|null $locale
     */
    public function setName($name, $locale = null) {
        $this->setData('name', $name, $locale);
    }

    /**
     * Get the genre's category (e.g., 'document', 'metadata', 'artwork').
     * @return string
     */
    public function getCategory() {
        return $this->getData('category');
    }

    /**
     * Set the genre's category.
     * @param string $category
     */
    public function setCategory($category) {
        $this->setData('category', $category);
    }

    /**
     * Get the localized description.
     * @param string|null $locale
     * @return string
     */
    public function getDescription($locale = null) {
        return $this->getData('description', $locale);
    }

    /**
     * Set the localized description.
     * @param string $description
     * @param string|null $locale
     */
    public function setDescription($description, $locale = null) {
        $this->setData('description', $description, $locale);
    }

    /**
     * Get the dependent flag (whether files of this genre are dependent on the parent file).
     * @return bool
     */
    public function getDependent() {
        return (bool) $this->getData('dependent');
    }

    /**
     * Set the dependent flag.
     * @param bool $dependent
     */
    public function setDependent($dependent) {
        $this->setData('dependent', (bool) $dependent);
    }

    /**
     * Get the suppress flag (whether files of this genre should be suppressed in listing).
     * @return bool
     */
    public function getSuppress() {
        return (bool) $this->getData('suppress');
    }

    /**
     * Set the suppress flag.
     * @param bool $suppress
     */
    public function setSuppress($suppress) {
        $this->setData('suppress', (bool) $suppress);
    }

    /**
     * Get the sort order (for display ordering).
     * @return int
     */
    public function getSortOrder() {
        return (int) $this->getData('sortOrder');
    }

    /**
     * Set the sort order.
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder) {
        $this->setData('sortOrder', (int) $sortOrder);
    }

}
?>