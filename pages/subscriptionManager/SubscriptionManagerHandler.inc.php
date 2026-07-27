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
     * @param mixed $request
     */
    public function index($args = [], $request = null) {
        $this->subscriptionsSummary($args, $request);
    }

    /**
     * Display subscriptions summary page for the current journal.
     * @param array $args
     * @param mixed $request
     */
    public function subscriptionsSummary($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->subscriptionsSummary();
    }

    /**
     * Display a list of subscriptions for the current journal.
     * @param array $args
     * @param mixed $request
     */
    public function subscriptions($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->subscriptions($institutional);
    }

    /**
     * Delete a subscription.
     * @param array $args
     * @param mixed $request
     */
    public function deleteSubscription($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->deleteSubscription($args, $institutional);

        $request->redirect(null, null, 'subscriptions', $redirect);
    }

    /**
     * Renew a subscription.
     * @param array $args
     * @param mixed $request
     */
    public function renewSubscription($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate();

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->renewSubscription($args, $institutional);

        $request->redirect(null, null, 'subscriptions', $redirect);
    }

    /**
     * Display form to edit a subscription.
     * @param array $args
     * @param mixed $request
     */
    public function editSubscription($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $editSuccess = $subscriptionAction->editSubscription($args, $institutional);

        if (!$editSuccess) {
            $request->redirect(null, null, 'subscriptions', $redirect);
        }
    }

    /**
     * Display form to create new subscription.
     * @param array $args
     * @param mixed $request
     */
    public function createSubscription($args, $request = null) {
        $this->editSubscription($args, $request);
    }

    /**
     * Display a list of users from which to choose a subscriber.
     * @param array $args
     * @param mixed $request
     */
    public function selectSubscriber($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->selectSubscriber($args, $institutional);
    }

    /**
     * Save changes to a subscription.
     * @param array $args
     * @param mixed $request
     */
    public function updateSubscription($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $updateSuccess = $subscriptionAction->updateSubscription($args, $institutional);

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
     * @param mixed $request
     */
    public function resetDateReminded($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $institutional = false;
        $redirect = 'individual';
        if (!empty($args)) {
            if ($args[0] === 'individual') {
                $institutional = false;
                $redirect = 'individual';
            } elseif ($args[0] === 'institutional') {
                $institutional = true;
                $redirect = 'institutional';
            } else {
                $request->redirect(null, 'subscriptionManager');
            }
        } else {
            $request->redirect(null, 'subscriptionManager');
        }

        $this->validate($request);
        $this->setupTemplate(true, $institutional);

        array_shift($args);
        $subscriptionId = (int) ($args[0] ?? 0);
        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->resetDateReminded($args, $institutional);

        $request->redirect(null, null, 'editSubscription', [$redirect, $subscriptionId]);
    }

    /**
     * Display a list of subscription types for the current journal.
     * @param array $args
     * @param mixed $request
     */
    public function subscriptionTypes($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->subscriptionTypes();
    }

    /**
     * Rearrange the order of subscription types.
     * @param array $args
     * @param mixed $request
     */
    public function moveSubscriptionType($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->moveSubscriptionType($args);

        $request->redirect(null, null, 'subscriptionTypes');
    }

    /**
     * Delete a subscription type.
     * @param array $args
     * @param mixed $request
     */
    public function deleteSubscriptionType($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->deleteSubscriptionType($args);

        $request->redirect(null, null, 'subscriptionTypes');
    }

    /**
     * Display form to edit a subscription type.
     * @param array $args optional, first parameter is the ID of the subscription type to edit
     * @param mixed $request
     */
    public function editSubscriptionType($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->append('pageHierarchy', [$request->url(null, 'subscriptionManager', 'subscriptionTypes'), 'subscriptionManager.subscriptionTypes']);

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $editSuccess = $subscriptionAction->editSubscriptionType($args);

        if (!$editSuccess) {
            $request->redirect(null, null, 'subscriptionTypes');
        }
    }

    /**
     * Display form to create new subscription type.
     * @param array $args
     * @param mixed $request
     */
    public function createSubscriptionType($args = [], $request = null) {
        $this->editSubscriptionType($args, $request);
    }

    /**
     * Save changes to a subscription type.
     * @param array $args
     * @param mixed $request
     */
    public function updateSubscriptionType($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->append('pageHierarchy', [$request->url(null, 'subscriptionManager', 'subscriptionTypes'), 'subscriptionManager.subscriptionTypes']);

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $updateSuccess = $subscriptionAction->updateSubscriptionType();

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
     * @param mixed $request
     */
    public function subscriptionPolicies($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->subscriptionPolicies($args, $request);
    }

    /**
     * Save subscription policies for the current journal.
     * @param array $args
     * @param mixed $request
     */
    public function saveSubscriptionPolicies($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.subscription.SubscriptionAction');
        $subscriptionAction = new SubscriptionAction();
        $subscriptionAction->saveSubscriptionPolicies($args, $request);
    }

    /**
     * Display form to create a user profile.
     * @param array $args optional
     * @param mixed $request
     */
    public function createUser($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager($request);

        import('classes.manager.form.UserManagementForm');

        $templateMgr->assign('currentUrl', $request->url(null, null, 'createUser'));
        $userForm = new UserManagementForm();
        if ($userForm->isLocaleResubmit()) {
            $userForm->readInputData();
        } else {
            $userForm->initData();
        }
        $userForm->display($request);
    }

    /**
     * Save changes to a user profile.
     * @param array $args
     * @param mixed $request
     */
    public function updateUser($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate(true);

        import('classes.manager.form.UserManagementForm');

        $userForm = new UserManagementForm();
        $userForm->readInputData();

        if ($userForm->validate()) {
            $userForm->execute();

            $createAnotherFlag = (int) trim((string) $request->getUserVar('createAnother'));

            if ($createAnotherFlag) {
                $this->setupTemplate(true);
                $templateMgr = TemplateManager::getManager($request); 
                $templateMgr->assign('currentUrl', $request->url(null, null, 'index'));
                $templateMgr->assign('userCreated', true);
                $userForm = new UserManagementForm();
                $userForm->initData();
                $userForm->display($request);

            } else {
                $source = trim((string) $request->getUserVar('source'));
                
                if (!empty($source)) { 
                    $request->redirectUrl($source);
                } else {
                    $request->redirect(null, null, 'selectSubscriber');
                }
            }

        } else {
            $userForm->display($request);
        }
    }

    /**
     * Display payments settings form
     * @param array $args
     * @param mixed $request
     */
    public function payments($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $paymentAction = new OJSPaymentAction();
        $paymentAction->payments($args);
    }

    /**
     * Execute the payments form or display it again if there are problems
     * @param array $args
     * @param mixed $request
     */
    public function savePaymentSettings($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $paymentAction = new OJSPaymentAction();
        $success = $paymentAction->savePaymentSettings($args);

        if ($success) {
            $templateMgr = TemplateManager::getManager($request);
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
     * @param mixed $request
     */
    public function viewPayments($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $paymentAction = new OJSPaymentAction();
        $paymentAction->viewPayments($args);
    }

    /**
     * Display a single Completed payment
     * @param array $args
     * @param mixed $request
     */
    public function viewPayment($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $paymentAction = new OJSPaymentAction();
        $paymentAction->viewPayment($args);
    }

    /**
     * Display form to edit program settings.
     * @param array $args
     * @param mixed $request
     */
    public function payMethodSettings($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $paymentAction = new OJSPaymentAction();
        $paymentAction->payMethodSettings();
    }

    /**
     * Save changes to payment settings.
     * @param array $args
     * @param mixed $request
     */
    public function savePayMethodSettings($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        import('classes.payment.ojs.OJSPaymentAction');
        $paymentAction = new OJSPaymentAction();
        $success = $paymentAction->savePayMethodSettings();

        if ($success) {
            $templateMgr = TemplateManager::getManager($request);
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
     * @param mixed $request
     */
    public function suggestUsername($args = [], $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $suggestion = Validation::suggestUsername(
            trim((string) $request->getUserVar('firstName')),
            trim((string) $request->getUserVar('lastName'))
        );
        echo $suggestion;
    }

    /**
     * Display a user's profile.
     * @param array $args
     * @param mixed $request
     */
    public function userProfile($args, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('currentUrl', $request->url(null, null, 'viewPayments'));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.payments');

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $userId = $args[0] ?? 0;
        if (is_numeric($userId)) {
            $userId = (int) $userId;
            $user = $userDao->getById($userId);
        } else {
            $user = $userDao->getByUsername((string) $userId);
        }

        if ($user === null) {
            // Non-existent user requested
            $templateMgr->assign('pageTitle', 'user.profile');
            $templateMgr->assign('errorMsg', 'manager.people.invalidUser');
            $templateMgr->assign('backLink', $request->url(null, null, 'viewPayments'));
            $templateMgr->assign('backLinkLabel', 'manager.payment.feePaymentOptions');

            $templateMgr->display('common/error.tpl');
        } else {
            $site = $request->getSite();
            $journal = $request->getJournal();

            /** @var CountryDAO $countryDao */
            $countryDao = DAORegistry::getDAO('CountryDAO');
            $country = null;
            if ($user->getCountry() !== '') {
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
     * @param bool $subclass
     * @param bool $institutional
     * @param mixed $request
     */
    public function setupTemplate($subclass = false, $institutional = false, $request = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        parent::setupTemplate(true);
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_MANAGER, 
            LOCALE_COMPONENT_APP_MANAGER
        );
        $templateMgr = TemplateManager::getManager($request);
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