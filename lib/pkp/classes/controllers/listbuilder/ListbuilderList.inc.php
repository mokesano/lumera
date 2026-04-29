<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/ListbuilderList.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ListbuilderList
 * @ingroup controllers_listbuilder
 *
 * @brief Base class for a listbuilder list. This is used by MultipleListsListbuilderHandler
 * to implement multiple lists in a single listbuilder component.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

class ListbuilderList {

    /** @var mixed List id. */
    protected $_id = null;

    /** @var string|null Locale key. */
    protected $_title = null;

    /** @var array */
    protected $_data = [];

    /**
     * Constructor
     * @param mixed $id
     * @param string|null $title optional Locale key.
     */
    public function __construct($id, ?string $title = null) {
        $this->setId($id);
        $this->setTitle($title);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ListbuilderList($id, $title = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($id, $title);
    }


    //
    // Getters and setters
    //
    
    /**
     * Get this list id.
     * @return mixed
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Set this list id.
     * @param mixed $id
     */
    public function setId($id): void {
        $this->_id = $id;
    }

    /**
     * Get this list title.
     * @return string|null
     */
    public function getTitle(): ?string {
        return $this->_title;
    }

    /**
     * Set this list title.
     * @param string|null $title
     */
    public function setTitle(?string $title): void {
        $this->_title = $title;
    }

    /**
     * Get the loaded list data.
     * @return array
     */
    public function getData(): array {
        return $this->_data;
    }

    /**
     * Set the loaded list data.
     * @param array $listData
     */
    public function setData(array $listData): void {
        $this->_data = $listData;
    }
}

?>