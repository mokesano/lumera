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
 */

import('classes.handler.Handler');

class ReferralHandler extends Handler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReferralHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Setup common template variables.
     * @param mixed $request
     * @return void
     */
    public function setupTemplate($request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        parent::setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);
        $pageHierarchy = [
            [$request->url(null, 'referral', 'index'), 'plugins.generic.referral.referrals']
        ];
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }

    /**
     * Edit a referral.
     * @param array $args
     * @return void
     */
    public function editReferral($args) {
        $request = Application::get()->getRequest();
        $referralId = isset($args[0]) ? (int) $args[0] : null;
        if ($referralId === 0) {
            $referralId = null;
        }

        [$plugin, $referral, $article] = $this->validate($referralId, $request);
        $this->setupTemplate($request);

        $plugin->import('ReferralForm');
        $templateMgr = TemplateManager::getManager($request);

        if ($referralId === null) {
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
        $referralForm->display($request);
    }

    /**
     * Save changes to a referral.
     * @return void
     */
    public function updateReferral() {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();

        $referralIdVal = $request->getUserVar('referralId');
        $referralId = ($referralIdVal !== null && $referralIdVal !== '') ? (int) $referralIdVal : null;
        if ($referralId === 0) {
            $referralId = null;
        }

        [$plugin, $referral, $article] = $this->validate($referralId, $request);
        
        // If it's an insert, ensure that it's allowed for this article
        if ($referral === null) {
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $journal = $request->getJournal();
            
            $articleIdVal = $request->getUserVar('articleId');
            $articleId = ($articleIdVal !== null && $articleIdVal !== '') ? (int) $articleIdVal : 0;
            $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId);
            
            $user = $request->getUser();
            if (
                !$article || 
                !$user ||
                ($article->getUserId() !== $user->getId() && !Validation::isSectionEditor((int) $journal->getId()) && !Validation::isEditor((int) $journal->getId()))
            ) {
                $request->redirect(null, 'author');
            }
        }
        $this->setupTemplate($request);

        $plugin->import('ReferralForm');

        $referralForm = new ReferralForm($plugin, $article, $referralId);
        $referralForm->readInputData();

        if ($referralForm->validate()) {
            $referralForm->execute();
            $request->redirect(null, 'author');
        } else {
            $templateMgr = TemplateManager::getManager($request);

            if ($referralId === null) {
                $templateMgr->assign('referralTitle', 'plugins.generic.referral.createReferral');
            } else {
                $templateMgr->assign('referralTitle', 'plugins.generic.referral.editReferral');    
            }

            $referralForm->display($request);
        }
    }

    /**
     * Delete a referral.
     * @param array $args
     * @return void
     */
    public function deleteReferral($args) {
        $request = Application::get()->getRequest();
        $referralId = isset($args[0]) ? (int) $args[0] : null;
        
        [$plugin, $referral] = $this->validate($referralId, $request);

        /** @var ReferralDAO $referralDao */
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        $referralDao->deleteReferral($referral);

        $request->redirect(null, 'author');
    }

    /**
     * Validate that the user is the author of the referral.
     * @param int|null $referralId
     * @param mixed $request
     * @return array
     */
    public function validate($referralId = null, $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        parent::validate($request);

        if ($referralId !== null) {
            /** @var ReferralDAO $referralDao */
            $referralDao = DAORegistry::getDAO('ReferralDAO');
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            
            $referral = $referralDao->getReferral($referralId);
            if (!$referral) {
                $request->redirect(null, 'index');
            }

            $user = $request->getUser();
            $journal = $request->getJournal();
            $article = $publishedArticleDao->getPublishedArticleByArticleId((int) $referral->getArticleId());
            
            if (!$article || !$journal || !$user) {
                $request->redirect(null, 'index');
            }
            
            if ($article->getJournalId() !== $journal->getId()) {
                $request->redirect(null, 'index');
            }
            
            // The article's submitter, journal SE, and journal Editors are allowed.
            if (
                $article->getUserId() !== $user->getId() && 
                !Validation::isSectionEditor((int) $journal->getId()) && 
                !Validation::isEditor((int) $journal->getId())
            ) {
                $request->redirect(null, 'index');
            }
        } else {
            $referral = null;
            $article = null;
        }
        
        $plugin = Registry::get('plugin');
        return [$plugin, $referral, $article];
    }

    /**
     * Perform a batch action on a set of referrals.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function bulkAction($args, $request) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $referralIds = (array) $request->getUserVar('referralId'); 
        /** @var ReferralDAO $referralDao */
        $referralDao = DAORegistry::getDAO('ReferralDAO');
        
        foreach ($referralIds as $referralId) {
            $safeReferralId = (int) $referralId;
            
            [$plugin, $referral, $article] = $this->validate($safeReferralId, $request);
            
            if ($referral === null) {
                continue;
            }

            if ((bool) $request->getUserVar('delete')) { 
                $referralDao->deleteReferral($referral);
            } elseif ((bool) $request->getUserVar('accept')) { 
                $referral->setStatus(REFERRAL_STATUS_ACCEPT);
                $referralDao->updateReferral($referral);
            } elseif ((bool) $request->getUserVar('decline')) { 
                $referral->setStatus(REFERRAL_STATUS_DECLINE);
                $referralDao->updateReferral($referral);
            }
        }
        $request->redirect(null, 'author');
    }
    
}
?>