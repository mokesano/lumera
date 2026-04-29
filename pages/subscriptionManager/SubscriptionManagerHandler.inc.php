<?php
declare(strict_types=1);

/**
 * @file pages/subscriptionManager/SubscriptionManagerHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubscriptionManagerHandler
 * @ingroup pages_subscriptionManager
 *
 * @brief Handle requests for subscription management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.handler.Handler');

class SubscriptionManagerHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SUBSCRIPTION_MANAGER]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubscriptionManagerHandler() {
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
     * Display the index page.
     * @param array $args
     * @param object|null $request
     */
    public function index($args = [], $request = null) {
        $this->subscriptionsSummary($args, $request);
    }

    /**
     * Display subscriptions summary page for the current journal.
     * @param array $args
     * @param object|null $request
     */
    public function subscriptionsSummary($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptionsSummary();
    }

    /**
     * Display a list of subscriptions for the current journal.
     * @param array $args
     * @param object|null $request
     */
    public function subscriptions($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
            } else {
                $institutional = true;
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptions($institutional);
    }

    /**
     * Delete a subscription.
     * @param array $args first parameter is the ID of the subscription to delete
     * @param object|null $request
     */
    public function deleteSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::deleteSubscription($args, $institutional);

        $request->redirect(null, null, 'subscriptions', $redirect);
    }

    /**
     * Renew a subscription.
     * @param array $args first parameter is the ID of the subscription to renew
     * @param object|null $request
     */
    public function renewSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::renewSubscription($args, $institutional);

        $request->redirect(null, null, 'subscriptions', $redirect);
    }

    /**
     * Display form to edit a subscription.
     * @param array $args optional, first parameter is the ID of the subscription to edit
     * @param object|null $request
     */
    public function editSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
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
     * @param object|null $request
     */
    public function createSubscription($args, $request = null) {
        $this->editSubscription($args, $request);
    }

    /**
     * Display a list of users from which to choose a subscriber.
     * @param array $args
     * @param object|null $request
     */
    public function selectSubscriber($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::selectSubscriber($args, $institutional);
    }

    /**
     * Save changes to a subscription.
     * @param array $args
     * @param object|null $request
     */
    public function updateSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $updateSuccess = SubscriptionAction::updateSubscription($args, $institutional);

        // [SECURITY FIX] Amankan 'createAnother' sebagai flag boolean dengan (int) trim()
        $createAnotherFlag = (int) trim((string) $request->getUserVar('createAnother'));
        
        if ($updateSuccess && $createAnotherFlag) {
            $request->redirect(null, null, 'selectSubscriber', $redirect);
        } elseif ($updateSuccess) {
            $request->redirect(null, null, 'subscriptions', $redirect);
        }
    }

    /**
     * Reset a subscription reminder date.
     * @param array $args
     * @param object|null $request
     */
    public function resetDateReminded($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (isset($args) && !empty($args)) {
            if ($args[0] == 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } else {
                $institutional = true;
                $redirect = 'institutional';
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        $subscriptionId = (int) $args[0];
        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::resetDateReminded($args, $institutional);

        $request->redirect(null, null, 'editSubscription', [$redirect, $subscriptionId]);
    }

    /**
     * Display a list of subscription types for the current journal.
     * @param array $args
     * @param object|null $request
     */
    public function subscriptionTypes($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
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
     * @param object|null $request
     */
    public function moveSubscriptionType($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::moveSubscriptionType($args);

        $request->redirect(null, null, 'subscriptionTypes');
    }

    /**
     * Delete a subscription type.
     * @param array $args first parameter is the ID of the subscription type to delete
     * @param object|null $request
     */
    public function deleteSubscriptionType($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::deleteSubscriptionType($args);

        $request->redirect(null, null, 'subscriptionTypes');
    }

    /**
     * Display form to edit a subscription type.
     * @param array $args optional, first parameter is the ID of the subscription type to edit
     * @param object|null $request
     */
    public function editSubscriptionType($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->append('pageHierarchy', [$request->url(null, 'subscriptionManager', 'subscriptionTypes'), 'subscriptionManager.subscriptionTypes']);

        import('classes.subscription.SubscriptionAction');
        $editSuccess = SubscriptionAction::editSubscriptionType($args);

        if (!$editSuccess) {
            $request->redirect(null, null, 'subscriptionTypes');
        }
    }

    /**
     * Display form to create new subscription type.
     * @param array $args
     * @param object|null $request
     */
    public function createSubscriptionType($args = [], $request = null) {
        $this->editSubscriptionType($args, $request);
    }

    /**
     * Save changes to a subscription type.
     * @param array $args
     * @param object|null $request
     */
    public function updateSubscriptionType($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->append('pageHierarchy', [$request->url(null, 'subscriptionManager', 'subscriptionTypes'), 'subscriptionManager.subscriptionTypes']);

        import('classes.subscription.SubscriptionAction');
        $updateSuccess = SubscriptionAction::updateSubscriptionType();

        // [SECURITY FIX] Amankan 'createAnother' sebagai flag boolean dengan (int) trim()
        $createAnotherFlag = (int) trim((string) $request->getUserVar('createAnother'));
        
        if ($updateSuccess && $createAnotherFlag) {
            $request->redirect(null, null, 'createSubscriptionType', null, ['subscriptionTypeCreated' => 1]);
        } elseif ($updateSuccess) {
            $request->redirect(null, null, 'subscriptionTypes');
        }
    }

    /**
     * Display subscription policies for the current journal.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function subscriptionPolicies($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::subscriptionPolicies($args, $request);
    }

    /**
     * Save subscription policies for the current journal.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function saveSubscriptionPolicies($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        SubscriptionAction::saveSubscriptionPolicies($args, $request);
    }

    /**
     * Display form to create a user profile.
     * @param array $args optional
     * @param object|null $request
     */
    public function createUser($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate(true);

        $journal = $request->getJournal();

        $templateMgr = TemplateManager::getManager();

        import('classes.manager.form.UserManagementForm');

        $templateMgr->assign('currentUrl', $request->url(null, null, 'createUser'));
        $userForm = new UserManagementForm();
        if ($userForm->isLocaleResubmit()) {
            $userForm->readInputData();
        } else {
            $userForm->initData();
        }
        $userForm->display();
    }

    /**
     * Save changes to a user profile.
     * @param array $args
     * @param object|null $request
     */
    public function updateUser($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate(true);

        $journal = $request->getJournal();

        import('classes.manager.form.UserManagementForm');

        $userForm = new UserManagementForm();
        $userForm->readInputData();

        if ($userForm->validate()) {
            $userForm->execute();

            // [SECURITY FIX] Amankan 'createAnother' sebagai flag boolean dengan (int) trim()
            $createAnotherFlag = (int) trim((string) $request->getUserVar('createAnother'));

            if ($createAnotherFlag) {
                $this->setupTemplate(true);
                $templateMgr = TemplateManager::getManager(); 
                $templateMgr->assign('currentUrl', $request->url(null, null, 'index'));
                $templateMgr->assign('userCreated', true);
                $userForm = new UserManagementForm();
                $userForm->initData();
                $userForm->display();

            } else {
                $source = trim((string) $request->getUserVar('source'));
                
                if (!empty($source)) { 
                    $request->redirectUrl($source);
                } else {
                    $request->redirect(null, null, 'selectSubscriber');
                }
            }

        } else {
            $userForm->display();
        }
    }

    /**
     * Display payments settings form
     * @param array $args
     * @param object|null $request
     */
    public function payments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::payments($args);
    }

    /**
     * Execute the payments form or display it again if there are problems
     * @param array $args
     * @param object|null $request
     */
    public function savePaymentSettings($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $success = OJSPaymentAction::savePaymentSettings($args);

        if ($success) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign([
                'currentUrl' => $request->url(null, null, 'payments'),
                'pageTitle' => 'manager.payment.feePaymentOptions',
                'message' => 'common.changesSaved',
                'backLink' => $request->url(null, null, 'payments'),
                'backLinkLabel' => 'manager.payment.feePaymentOptions'
            ]);
            $templateMgr->display('common/message.tpl');
        }
    }

    /**
     * Display all payments previously made
     * @param array $args
     * @param object|null $request
     */
    public function viewPayments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::viewPayments($args);
    }

    /**
     * Display a single Completed payment
     * @param array $args
     * @param object|null $request
     */
    public function viewPayment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::viewPayment($args);
    }

    /**
    * Display form to edit program settings.
    * @param array $args
    * @param object|null $request
    */
    public function payMethodSettings($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        OJSPaymentAction::payMethodSettings();
    }

    /**
     * Save changes to payment settings.
     * @param array $args
     * @param object|null $request
     */
    public function savePayMethodSettings($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $success = OJSPaymentAction::savePayMethodSettings();

        if ($success) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign([
                'currentUrl' => $request->url(null, null, 'payMethodSettings'),
                'pageTitle' => 'manager.payment.paymentMethods',
                'message' => 'common.changesSaved',
                'backLink' => $request->url(null, null, 'payMethodSettings'),
                'backLinkLabel' => 'manager.payment.paymentMethods'
            ]);
            $templateMgr->display('common/message.tpl');
        }
    }

    /**
     * Get a suggested username, making sure it's not
     * already used by the system. (Poor-man's AJAX.)
     * @param array $args
     * @param object|null $request
     */
    public function suggestUsername($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $suggestion = Validation::suggestUsername(
            // [SECURITY FIX] Amankan input dengan trim()
            trim((string) $request->getUserVar('firstName')),
            trim((string) $request->getUserVar('lastName'))
        );
        echo $suggestion;
    }

    /**
     * Display a user's profile.
     * @param array $args first parameter is the ID or username of the user to display
     * @param object|null $request
     */
    public function userProfile($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('currentUrl', $request->url(null, null, 'viewPayments'));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.payments');

        $userDao = DAORegistry::getDAO('UserDAO');
        $userId = isset($args[0]) ? $args[0] : 0;
        if (is_numeric($userId)) {
            $userId = (int) $userId;
            $user = $userDao->getById($userId);
        } else {
            $user = $userDao->getByUsername($userId);
        }

        if ($user == null) {
            // Non-existent user requested
            $templateMgr->assign('pageTitle', 'user.profile');
            $templateMgr->assign('errorMsg', 'manager.people.invalidUser');
            $templateMgr->assign('backLink', $request->url(null, null, 'viewPayments'));
            $templateMgr->assign('backLinkLabel', 'manager.payment.feePaymentOptions');
            $templateMgr->display('common/error.tpl');
        } else {
            $site = $request->getSite();
            $journal = $request->getJournal();

            $countryDao = DAORegistry::getDAO('CountryDAO');
            $country = null;
            if ($user->getCountry() != '') {
                $country = $countryDao->getCountry($user->getCountry());
            }
            $templateMgr->assign('country', $country);

            $templateMgr->assign('userInterests', $user->getInterestString());

            $templateMgr->assign('user', $user);
            $templateMgr->assign('localeNames', AppLocale::getAllLocales());
            $templateMgr->display('subscription/userProfile.tpl');
        }
    }

    /**
     * Setup common template variables.
     * @param boolean $subclass
     * @param boolean $institutional
     * @param object|null $request
     */
    public function setupTemplate($subclass = false, $institutional = false, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        parent::setupTemplate(true);
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_MANAGER, 
            LOCALE_COMPONENT_APP_MANAGER
        );
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageHierarchy', [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'subscriptionManager'), 'subscriptionManager.subscriptionManagement']]);
        if ($subclass) {
            if ($institutional) {
                $templateMgr->append('pageHierarchy', [$request->url(null, 'subscriptionManager', 'subscriptions', 'institutional'), 'subscriptionManager.institutionalSubscriptions']);
            } else {
                $templateMgr->append('pageHierarchy', [$request->url(null, 'subscriptionManager', 'subscriptions', 'individual'), 'subscriptionManager.individualSubscriptions']);
            }
        }
    }
}
?>