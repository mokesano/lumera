<?php
declare(strict_types=1);

/**
 * @file plugins/blocks/subscription/SubscriptionBlockPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionBlockPlugin
 * @ingroup plugins_blocks_subscription
 *
 * @brief Class for subscription block plugin
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class SubscriptionBlockPlugin extends BlockPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubscriptionBlockPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::SubscriptionBlockPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Install default settings on journal creation.
     * @return string
     */
    public function getContextSpecificPluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     * @return String
     */
    public function getDisplayName(): string {
        return __('plugins.block.subscription.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return String
     */
    public function getDescription(): string {
        return __('plugins.block.subscription.description');
    }

    /**
     * Get the HTML contents for this block.
     * @param object $templateMgr object
     * @param PKPRequest $request
     * @return string
     */
    public function getContents($templateMgr, $request = null) {
        $journal = $request->getJournal();
        $journalId = ($journal) ? $journal->getId() : null;
        if (!$journal) return '';

        if ($journal->getSetting('publishingMode') != PUBLISHING_MODE_SUBSCRIPTION)
            return '';

        $user = Request::getUser();
        $userId = ($user) ? $user->getId() : null;
        $templateMgr->assign('userLoggedIn', isset($userId));

        if (isset($userId)) {
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
            $individualSubscription = $subscriptionDao->getSubscriptionByUserForJournal($userId, $journalId);

            $templateMgr->assign('individualSubscription', $individualSubscription);
        }

        if (!isset($individualSubscription) || !$individualSubscription->isValid()) {
            $ip = Request::getRemoteAddr();
            $domain = Request::getRemoteDomain();
            
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
            $subscriptionId = $subscriptionDao->isValidInstitutionalSubscription($domain, $ip, $journalId);
            
            if ($subscriptionId) {
                $institutionalSubscription = $subscriptionDao->getSubscription($subscriptionId);
                $templateMgr->assign('institutionalSubscription', $institutionalSubscription);
                $templateMgr->assign('userIP', $ip);
            }
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);

        if (isset($individualSubscription) || isset($institutionalSubscription)) {
            $acceptSubscriptionPayments = $paymentManager->acceptSubscriptionPayments();
            $templateMgr->assign('acceptSubscriptionPayments', $acceptSubscriptionPayments);
        }

        $acceptGiftSubscriptionPayments = $paymentManager->acceptGiftSubscriptionPayments();
        $templateMgr->assign('acceptGiftSubscriptionPayments', $acceptGiftSubscriptionPayments);

        $templateFilename = $this->getBlockTemplateFilename($request);
        if ($templateFilename === null) return '';
        
        return $templateMgr->fetch($this->getTemplatePath() . $templateFilename);
    }
    
}
?>