<?php
declare(strict_types=1);

/**
 * @file plugins/generic/referral/ReferralHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReferralHandler
 * @ingroup plugins_generic_referral
 *
 * @brief This handles requests for the referral plugin.
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.handler.Handler');

class ReferralHandler extends Handler {
    
    /**
     * Constructor
     **/
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReferralHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ReferralHandler(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Setup common template variables.
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        $pageHierarchy = [[Request::url(null, 'referral', 'index'), 'plugins.generic.referral.referrals']];
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }

    /**
     * Edit a referral.
     * @param array $args
     */
    public function editReferral($args) {
        $referralId = (int) array_shift($args);
        if ($referralId === 0) $referralId = null;

        list($plugin, $referral, $article) = $this->validate($referralId);
        $this->setupTemplate();

        $plugin->import('ReferralForm');
        $templateMgr = TemplateManager::getManager();

        if ($referralId == null) {
            $templateMgr->assign('referralTitle', 'plugins.generic.referral.createReferral');
        } else {
            $templateMgr->assign('referralTitle', 'plugins.generic.referral.editReferral');    
        }

        $referralForm = new ReferralForm($plugin, $article, $referralId);
        if ($referralForm->isLocaleResubmit()) {
            $referralForm->readInputData();
        } else {
            $referralForm->initData();
        }
        $referralForm->display();
    }

    /**
     * Save changes to a referral.
     */
    public function updateReferral() {
        // [SECURITY FIX] Amankan 'referralId' (ID integer) dengan trim()
        $referralId = (int) trim(Request::getUserVar('referralId') ?? '');
        
        if ($referralId === 0) $referralId = null;

        list($plugin, $referral, $article) = $this->validate($referralId);
        
        // If it's an insert, ensure that it's allowed for this article
        if (!isset($referral)) {
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $journal = Request::getJournal();
            // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
            $articleId = (int) trim(Request::getUserVar('articleId') ?? '');
            $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId);
            
            $user = Request::getUser();
            if (!$article || ($article->getUserId() != $user->getId() && !Validation::isSectionEditor($journal->getId()) && !Validation::isEditor($journal->getId()))) {
                Request::redirect(null, 'author');
            }
        }
        $this->setupTemplate();

        $plugin->import('ReferralForm');

        $referralForm = new ReferralForm($plugin, $article, $referralId);
        $referralForm->readInputData();

        if ($referralForm->validate()) {
            $referralForm->execute();
            Request::redirect(null, 'author');
        } else {
            $templateMgr = TemplateManager::getManager();

            if ($referralId == null) {
                $templateMgr->assign('referralTitle', 'plugins.generic.referral.createReferral');
            } else {
                $templateMgr->assign('referralTitle', 'plugins.generic.referral.editReferral');    
            }

            $referralForm->display();
        }
    }

    /**
     * Delete a referral.
     * @param array $args
     */
    public function deleteReferral($args) {
        $referralId = (int) array_shift($args);
        list($plugin, $referral) = $this->validate($referralId);

        $referralDao = DAORegistry::getDAO('ReferralDAO');
        $referralDao->deleteReferral($referral);

        Request::redirect(null, 'author');
    }

    /**
     * Validate that the user is the author of the referral.
     * @param int $referralId
     * @return array
     */
    public function validate($referralId = null, $request = null) {
        parent::validate();

        if ($referralId) {
            $referralDao = DAORegistry::getDAO('ReferralDAO');
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $referral = $referralDao->getReferral($referralId);
            if (!$referral) Request::redirect(null, 'index');

            $user = Request::getUser();
            $journal = Request::getJournal();
            $article = $publishedArticleDao->getPublishedArticleByArticleId($referral->getArticleId());
            if (!$article || !$journal) Request::redirect(null, 'index');
            if ($article->getJournalId() != $journal->getId()) Request::redirect(null, 'index');
            // The article's submitter, journal SE, and journal Editors are allowed.
            if ($article->getUserId() != $user->getId() && !Validation::isSectionEditor($journal->getId()) && !Validation::isEditor($journal->getId())) Request::redirect(null, 'index');
        } else {
            $referral = $article = null;
        }
        $plugin = Registry::get('plugin');
        return [$plugin, $referral, $article];
    }

    /**
     * Perform a batch action on a set of referrals.
     * @param array $args
     * @param PKPRequest $request
     */
    public function bulkAction($args, $request) {
        // [SECURITY FIX] Amankan referralId: Casting ke array sudah ada.
        $referralIds = (array) $request->getUserVar('referralId'); 
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        
        foreach ($referralIds as $referralId) {
            // [SECURITY FIX] Amankan ID integer di dalam loop sebelum digunakan
            $safeReferralId = (int) trim($referralId);
            
            // Periksa validitas ID yang sudah diamankan
            list($plugin, $referral, $article) = $this->validate($safeReferralId);
            
            if ((int) trim($request->getUserVar('delete') ?? '')) { 
                $referralDao->deleteReferral($referral);
            } else if ((int) trim($request->getUserVar('accept') ?? '')) { 
                $referral->setStatus(REFERRAL_STATUS_ACCEPT);
                $referralDao->updateReferral($referral);
            } else if ((int) trim($request->getUserVar('decline') ?? '')) { 
                $referral->setStatus(REFERRAL_STATUS_DECLINE);
                $referralDao->updateReferral($referral);
            }
        }
        $request->redirect(null, 'author');
    }
}

?>