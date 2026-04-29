<?php
declare(strict_types=1);

/**
 * @file classes/rt/ojs/form/SearchForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SearchForm
 * @ingroup rt_ojs_form
 *
 * @brief Form to change metadata information for an RT search.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class SearchForm extends Form {

    /** @var int|null the ID of the search */
    public $searchId;

    /** @var RTSearch|null current search */
    public $search;

    /** @var int ID of the context */
    public $contextId;

    /** @var int ID of the version */
    public $versionId;

    /**
     * Constructor.
     * @param int|null $searchId
     * @param int $contextId
     * @param int $versionId
     */
    public function __construct($searchId, $contextId, $versionId) {
        parent::__construct('rtadmin/search.tpl');
        $this->addCheck(new FormValidatorPost($this));

        $rtDao = DAORegistry::getDAO('RTDAO');
        $this->search = $rtDao->getSearch($searchId);

        $this->contextId = (int) $contextId;
        $this->versionId = (int) $versionId;

        if (isset($this->search)) {
            $this->searchId = (int) $searchId;
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SearchForm($searchId, $contextId, $versionId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::SearchForm(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($searchId, $contextId, $versionId);
    }

    /**
     * Initialize form data from current search.
     */
    public function initData() {
        if (isset($this->search)) {
            $search = $this->search;
            $this->_data = [
                'url' => $search->getUrl(),
                'title' => $search->getTitle(),
                'searchUrl' => $search->getSearchUrl(),
                'description' => $search->getDescription(),
                'searchPost' => $search->getSearchPost(),
                'order' => $search->getOrder()
            ];
        } else {
            $this->_data = [];
        }
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('contextId', $this->contextId);
        $templateMgr->assign('versionId', $this->versionId);

        if (isset($this->search)) {
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('search', $this->search);
            $templateMgr->assign('searchId', $this->searchId);
        }

        $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.contexts');
        parent::display($request, $template);
    }


    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'url',
                'title',
                'order',
                'description',
                'searchUrl',
                'searchPost'
            ]
        );
    }

    /**
     * Save changes to search.
     * @param mixed $object
     * @return int the search ID
     */
    public function execute($object = null) {
        $rtDao = DAORegistry::getDAO('RTDAO');

        $search = $this->search;
        if (!isset($search)) {
            $search = new RTSearch();
            $search->setContextId($this->contextId);
        }

        $search->setTitle($this->getData('title'));
        $search->setUrl($this->getData('url'));
        $search->setSearchUrl($this->getData('searchUrl'));
        $search->setSearchPost($this->getData('searchPost'));
        $search->setDescription($this->getData('description'));
        if (!isset($this->search)) $search->setOrder(0);

        if (isset($this->search)) {
            $rtDao->updateSearch($search);
        } else {
            $rtDao->insertSearch($search);
            $this->searchId = $search->getSearchId();
        }

        return $this->searchId;
    }

}
?>