<?php
declare(strict_types=1);

/**
 * @file classes/journal/category/CategoryForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryForm
 * @ingroup manager_form
 * @see Category
 *
 * @brief Form for site admins to create/edit categories.
 */

import('lib.pkp.classes.form.Form');

class CategoryForm extends Form {

    /** @var mixed $category  */
    public $category;

    /**
     * Constructor
     */
    public function __construct($category = null) {
        parent::__construct('admin/categories/categoryForm.tpl');

        // Category name is provided
        $this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'admin.categories.nameRequired'));
        $this->addCheck(new FormValidatorPost($this));

        $this->category = $category;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CategoryForm($category = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::CategoryForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($category);
    }

    /**
     * Get the list of localized field names for this object
     * @return array
     */
    public function getLocaleFieldNames() {
        /** @var CategoryDAO $categoryDao */
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $categoryEntryDao = $categoryDao->getEntryDAO();
        
        return $categoryEntryDao->getLocaleFieldNames();
    }

    /**
     * Display the form.
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('category', $this->category);
        
        return parent::display();
    }

    /**
     * Initialize form data from current group group.
     */
    public function initData() {
        if ($this->category != null) {
            $this->_data = array(
                'name' => $this->category->getName(null) // Localized
            );
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('name'));
    }

    /**
     * Save group group.
     */
    public function execute($object = null) {
        /** @var CategoryDAO $categoryDao */
        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $categoryEntryDao = $categoryDao->getEntryDAO();
        $categoryControlledVocab = $categoryDao->build();

        if (!isset($this->category)) {
            $this->category = $categoryEntryDao->newDataObject();
        }

        $this->category->setName($this->getData('name'), null); // Localized
        $this->category->setControlledVocabId($categoryControlledVocab->getId());

        // Update or insert category
        if ($this->category->getId() != null) {
            $categoryEntryDao->updateObject($this->category);
        } else {
            $this->category->setSequence(defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999);
            $categoryEntryDao->insertObject($this->category);
            $categoryEntryDao->resequence($categoryControlledVocab->getId());
        }
    }

}
?>