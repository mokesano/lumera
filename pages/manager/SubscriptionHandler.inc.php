<?php
declare(strict_types=1);

/**
 * @file pages/manager/SubscriptionHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionHandler
 * @ingroup pages_manager
 *
 * Handle requests for subscription management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class SubscriptionHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubscriptionHandler() {
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
     * Display subscriptions summary page for the current journal.
     */
    public function subscriptionsSummary() {
        $this->validate();
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptionsSummary();
    }

    /**
     * Display a list of subscriptions for the current journal.
     * @param array $args
     */
    public function subscriptions($args) {
        $request = Application::get()->getRequest();
        
        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
            } else {
                $institutional = true;
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptions($institutional);
    }

    /**
     * Delete a subscription.
     * @param array $args first parameter is the ID of the subscription to delete
     */
    public function deleteSubscription($args) {
        $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::deleteSubscription($args, $institutional);

        $request->redirect(null, null, 'subscriptions', $redirect);
    }

    /**
     * Renew a subscription.
     * @param array $args first parameter is the ID of the subscription to renew
     */
    public function renewSubscription($args) {
        $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::renewSubscription($args, $institutional);

        $request->redirect(null, null, 'subscriptions', $redirect);
    }

    /**
     * Display form to edit a subscription.
     * @param array $args optional, first parameter is the ID of the subscription to edit
     */
    public function editSubscription($args) {
        $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $editSuccess = SubscriptionAction::editSubscription($args, $institutional);

        if (!$editSuccess) {
            $request->redirect(null, null, 'subscriptions', $redirect);
        }
    }

    /**
     * Display form to create new subscription.
     * @param array $args
     */
    public function createSubscription($args) {
        $this->editSubscription($args);
    }

    /**
     * Display a list of users from which to choose a subscriber.
     * @param array $args
     */
    public function selectSubscriber($args) {
        $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::selectSubscriber($args, $institutional);
    }

    /**
     * Save changes to a subscription.
     * @param array $args
     */
    public function updateSubscription($args) {
        $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $updateSuccess = SubscriptionAction::updateSubscription($args, $institutional);

        if ($updateSuccess && (int) $request->getUserVar('createAnother')) {
            $request->redirect(null, null, 'selectSubscriber', $redirect);
        } elseif ($updateSuccess) {
            $request->redirect(null, null, 'subscriptions', $redirect);
        }
    }

    /**
     * Reset a subscription reminder date.
     * @param array $args
     * @param PKPRequest $request
     */
    public function resetDateReminded($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional  = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'manager');
        }

        $this->validate();
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        $subscriptionId = (int) $args[0];
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::resetDateReminded($args, $institutional);

        $request->redirect(null, null, 'editSubscription', [$redirect, $subscriptionId]);
    }

    /**
     * Display a list of subscription types for the current journal.
     */
    public function subscriptionTypes() {
        $this->validate();
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptionTypes();
    }

    /**
     * Rearrange the order of subscription types.
     * @param array $args
     */
    public function moveSubscriptionType($args) {
        $this->validate();
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::moveSubscriptionType($args);

        Application::get()->getRequest()->redirect(null, null, 'subscriptionTypes');
    }

    /**
     * Delete a subscription type.
     * @param array $args first parameter is the ID of the subscription type to delete
     */
    public function deleteSubscriptionType($args) {
        $this->validate();
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::deleteSubscriptionType($args);

        Application::get()->getRequest()->redirect(null, null, 'subscriptionTypes');
    }

    /**
     * Display form to edit a subscription type.
     * @param array $args optional, first parameter is the ID of the subscription type to edit
     */
    public function editSubscriptionType($args = []) {
        $this->validate();
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'subscriptionTypes'), 'manager.subscriptionTypes']);

        import('classes.subscription.SubscriptionAction');
        $editSuccess = SubscriptionAction::editSubscriptionType($args);

        if (!$editSuccess) {
            $request->redirect(null, null, 'subscriptionTypes');
        }
    }

    /**
     * Display form to create new subscription type.
     */
    public function createSubscriptionType() {
        $this->editSubscriptionType();
    }

    /**
     * Save changes to a subscription type.
     */
    public function updateSubscriptionType() {
        $this->validate();
        $this->setupTemplate();

        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager();
        $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'subscriptionTypes'), 'manager.subscriptionTypes']);

        import('classes.subscription.SubscriptionAction');
        $updateSuccess = SubscriptionAction::updateSubscriptionType();

        if ($updateSuccess && (int) $request->getUserVar('createAnother')) {
            $request->redirect(null, null, 'createSubscriptionType', null, ['subscriptionTypeCreated' => 1]);
        } elseif ($updateSuccess) {
            $request->redirect(null, null, 'subscriptionTypes');
        }
    }

    /**
     * Display subscription policies for the current journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function subscriptionPolicies($args, $request) {
        $this->validate();
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptionPolicies($args, $request);
    }

    /**
     * Save subscription policies for the current journal.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSubscriptionPolicies($args, $request) {
        $this->validate();
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::saveSubscriptionPolicies($args, $request);
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     * @param bool $institutional
     */
    public function setupTemplate($subclass = false, $institutional = false) {
        parent::setupTemplate(true);
        if ($subclass) {
            $templateMgr = TemplateManager::getManager();
            $request = Application::get()->getRequest();
            if ($institutional) {
                $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'subscriptions', 'institutional'), 'manager.institutionalSubscriptions']);
            } else {
                $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'subscriptions', 'individual'), 'manager.individualSubscriptions']);
            }
        }
    }
}
?>