<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sword/SwordHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SwordHandler
 * @ingroup plugins_generic_sword
 *
 * @brief Handle requests for author SWORD deposits
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.handler.Handler');

class SwordHandler extends Handler {
    
    /**
     * Constructor
     **/
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SwordHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::SwordHandler(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request) {
        $this->validate();
        $this->setupTemplate();

        $journal = $request->getJournal();
        $user = $request->getUser();

        $articleId = (int) array_shift($args);
        $save = array_shift($args) == 'save';

        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $article = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);

        if (    !$article || !$user || !$journal ||
            $article->getUserId() != $user->getId() ||
            $article->getJournalId() != $journal->getId()
        ) {
            $request->redirect(null, 'index');
        }

        $swordPlugin = $this->_getSwordPlugin();
        $swordPlugin->import('AuthorDepositForm');
        $authorDepositForm = new AuthorDepositForm($swordPlugin, $article);

        if ($save) {
            $authorDepositForm->readInputData();
            if ($authorDepositForm->validate()) {
                $authorDepositForm->execute($request);
                $request->redirect(null, 'author');
            } else {
                $authorDepositForm->display();
            }
        } else {
            $authorDepositForm->initData();
            $authorDepositForm->display();
        }
    }

    /**
     * Get the SWORD plugin object
     * @return object
     */
    protected function _getSwordPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', SWORD_PLUGIN_NAME);
        return $plugin;
    }
}

?>