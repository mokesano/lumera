<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminCategoriesHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminCategoriesHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for changing admin's category list.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.admin.AdminHandler');

class AdminCategoriesHandler extends AdminHandler {
    
    /** @var ControlledVocab|null Category controlled vocab, if one is validated */
    public $categoryControlledVocab;

    /** @var ControlledVocabEntry|null Category entry, if one is validated */
    public $category;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AdminCategoriesHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display a list of categories.
     * @param array $args
     * @param PKPRequest $request
     */
    public function categories($args, $request) {
        $this->validate($request);
        $this->setupTemplate($request);

        $rangeInfo = $this->getRangeInfo('categories');

        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        // Unused variable removed: $categoryEntryDao

        $categoriesArray = $categoryDao->getCategories();
        import('lib.pkp.classes.core.ArrayItemIterator');
        $categories = ArrayItemIterator::fromRangeInfo($categoriesArray, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        // [WIZDAM] assign_by_ref deprecated
        $templateMgr->assign('categories', $categories);

        $site = $request->getSite();
        $templateMgr->assign('categoriesEnabled', $site->getSetting('categoriesEnabled'));

        $templateMgr->display('admin/categories/categories.tpl');
    }

    /**
     * Delete a category.
     * @param array $args first parameter is the ID of the category to delete
     * @param PKPRequest $request
     */
    public function deleteCategory($args, $request) {
        $categoryId = (int) array_shift($args);
        $this->validate($request, $categoryId);

        $category = $this->category;

        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $categoryEntryDao = $categoryDao->getEntryDAO();
        $categoryEntryDao->deleteObject($category);

        $categoryEntryDao->resequence($this->categoryControlledVocab->getId());
        $categoryDao->rebuildCache();

        $request->redirect(null, null, 'categories');
    }

    /**
     * Change the sequence of a category.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveCategory($args, $request) {
        $categoryId = (int) $request->getUserVar('id');
        $this->validate($request, $categoryId);

        $category = $this->category;

        // Penjaga 'null' (sudah benar)
        if (!$category) {
            $request->redirect(null, null, 'categories');
            return;
        }

        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $categoryEntryDao = $categoryDao->getEntryDAO();
        // [SECURITY FIX] Whitelist 'd' (direction)
        $direction = trim((string) $request->getUserVar('d'));

        if (!empty($direction)) {
            // moving with up or down arrow
            $category->setSequence($category->getSequence() + ($direction == 'u' ? -1.5 : 1.5));

        } else {
            // Dragging and dropping
            $prevId = (int) $request->getUserVar('prevId');
            if ($prevId == 0) { // Will match null or 0 from (int)
                $prevSeq = 0;
            } else {
                $prevCategory = $categoryEntryDao->getById($prevId, $this->categoryControlledVocab->getId());
                $prevSeq = $prevCategory ? $prevCategory->getSequence() : 0;
            }

            $category->setSequence($prevSeq + .5);
        }

        $categoryEntryDao->updateObject($category);
        $categoryEntryDao->resequence($this->categoryControlledVocab->getId());
        $categoryDao->rebuildCache();

        // Moving up or down with the arrows requires a page reload.
        // In the case of a drag and drop move, the display has been
        // updated on the client side, so no reload is necessary.
        if ($direction != null) {
            $request->redirect(null, null, 'categories');
        }
    }

    /**
     * Display form to edit a category.
     * @param array $args optional, parameter is the ID of the category to edit
     * @param PKPRequest $request
     */
    public function editCategory($args, $request) {
        $categoryId = (int) array_shift($args);
        if (!$categoryId) $categoryId = null;

        $this->validate($request, $categoryId);

        $this->setupTemplate($request, $this->category, true);
        import('classes.journal.categories.CategoryForm');

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageTitle',
            $this->category === null ?
                'admin.categories.createTitle' :
                'admin.categories.editTitle'
        );

        $categoryForm = new CategoryForm($this->category);
        if ($categoryForm->isLocaleResubmit()) {
            $categoryForm->readInputData();
        } else {
            $categoryForm->initData();
        }
        $categoryForm->display();
    }

    /**
     * Display form to create new category.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createCategory($args, $request) {
        $this->editCategory($args, $request);
    }

    /**
     * Save changes to a category.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateCategory($args, $request) {
        $categoryIdInput = $request->getUserVar('categoryId');
        $categoryId = $categoryIdInput === null ? null : (int) $categoryIdInput;
        
        if ($categoryId === null) {
            $this->validate($request);
            $category = null;
        } else {
            $this->validate($request, $categoryId);
            $category = $this->category;
        }
        $this->setupTemplate($request, $category);

        import('classes.journal.categories.CategoryForm');

        $categoryForm = new CategoryForm($category);
        $categoryForm->readInputData();

        if ($categoryForm->validate()) {
            $categoryForm->execute();
            $categoryDao = DAORegistry::getDAO('CategoryDAO');
            $categoryDao->rebuildCache();
            $request->redirect(null, null, 'categories');
        } else {

            $templateMgr = TemplateManager::getManager();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'admin', 'categories'), 'admin.categories']);

            $templateMgr->assign('pageTitle',
                $category ?
                    'admin.categories.editTitle' :
                    'admin.categories.createTitle'
            );

            $categoryForm->display();
        }
    }

    /**
     * Set the site-wide categories enabled/disabled flag.
     * @param array $args
     * @param PKPRequest $request
     */
    public function setCategoriesEnabled($args, $request) {
        $this->validate($request);
        $categoriesEnabled = (int) $request->getUserVar('categoriesEnabled') === 1;
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        $siteSettingsDao->updateSetting('categoriesEnabled', $categoriesEnabled);
        $request->redirect(null, null, 'categories');
    }

    /**
     * Set up the template.
     * @param PKPRequest $request
     * @param Category|null $category optional
     * @param bool $subclass optional
     */
    public function setupTemplate($request = null, $category = null, $subclass = false) {
        parent::setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        if ($subclass) {
            $templateMgr->append('pageHierarchy', [$request->url(null, 'admin', 'categories'), 'admin.categories']);
        }
        if ($category) {
            $templateMgr->append('pageHierarchy', [$request->url(null, 'admin', 'editCategory', $category->getId()), $category->getLocalizedName(), true]);
        }
    }

    /**
     * Validate the request. If a category ID is supplied, the category object
     * will be fetched and validated against. If, additionally, the user ID is 
     * supplied, the user and membership objects will be validated and fetched.
     * @param PKPRequest|null $request
     * @param int|null $categoryId optional
     * @return bool
     */
    public function validate($request = null, $categoryId = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        parent::validate();
        $passedValidation = true;

        $categoryDao = DAORegistry::getDAO('CategoryDAO');
        $this->categoryControlledVocab = $categoryDao->build();

        // Logika ini sekarang akan berfungsi karena $categoryId sudah benar
        if ($categoryId !== null) {
            $categoryEntryDao = $categoryDao->getEntryDAO();

            $category = $categoryEntryDao->getById($categoryId, $this->categoryControlledVocab->getId());
            
            if (!$category) {
                $passedValidation = false;
            } else {
                $this->category = $category;
            }
        } else {
            $this->category = null;
        }

        // Variabel $request sekarang sudah benar
        if (!$passedValidation) $request->redirect(null, null, 'categories');
        return true;
    }
}
?>