<?php
declare(strict_types=1);

/**
 * @file pages/user/UserHandler.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class UserHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.handler.Handler');

class UserHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserHandler() {
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
     * Gather information about a user's role within a journal.
     * @param int $userId
     * @param int $journalId
     * @param array $submissionsCount reference
     * @param array $isValid reference
     */
    public function _getRoleDataForJournal($userId, $journalId, &$submissionsCount, &$isValid) {
        if (Validation::isJournalManager($journalId)) {
            $isValid["JournalManager"][$journalId] = true;
        }
        if (Validation::isSubscriptionManager($journalId)) {
            $isValid["SubscriptionManager"][$journalId] = true;
        }
        if (Validation::isAuthor($journalId)) {
            $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
            $submissionsCount["Author"][$journalId] = $authorSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid["Author"][$journalId] = true;
        }
        if (Validation::isCopyeditor($journalId)) {
            $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
            $submissionsCount["Copyeditor"][$journalId] = $copyeditorSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid["Copyeditor"][$journalId] = true;
        }
        if (Validation::isLayoutEditor($journalId)) {
            $layoutEditorSubmissionDao = DAORegistry::getDAO('LayoutEditorSubmissionDAO');
            $submissionsCount["LayoutEditor"][$journalId] = $layoutEditorSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid["LayoutEditor"][$journalId] = true;
        }
        if (Validation::isEditor($journalId)) {
            $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
            $submissionsCount["Editor"][$journalId] = $editorSubmissionDao->getEditorSubmissionsCount($journalId);
            $isValid["Editor"][$journalId] = true;
        }
        if (Validation::isSectionEditor($journalId)) {
            $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
            $submissionsCount["SectionEditor"][$journalId] = $sectionEditorSubmissionDao->getSectionEditorSubmissionsCount($userId, $journalId);
            $isValid["SectionEditor"][$journalId] = true;
        }
        if (Validation::isProofreader($journalId)) {
            $proofreaderSubmissionDao = DAORegistry::getDAO('ProofreaderSubmissionDAO');
            $submissionsCount["Proofreader"][$journalId] = $proofreaderSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid["Proofreader"][$journalId] = true;
        }
        if (Validation::isReviewer($journalId)) {
            $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
            $submissionsCount["Reviewer"][$journalId] = $reviewerSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid["Reviewer"][$journalId] = true;
        }
    }

    /**
     * Determine if the journal's setup has been sufficiently completed.
     * @param object $journal Journal
     * @return boolean True iff setup is incomplete
     */
    public function _checkIncompleteSetup($journal) {
        if($journal->getLocalizedInitials() == "" || $journal->getSetting('contactEmail') == "" ||
           $journal->getSetting('contactName') == "" || $journal->getLocalizedSetting('abbreviation') == "") {
            return true;
        } else return false;
    }

    /**
     * Change the locale for the current user.
     * @param array $args first parameter is the new locale
     * @param object|null $request PKPRequest
     */
    public function setLocale($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $setLocale = array_shift($args);

        $site = $request->getSite();
        $journal = $request->getJournal();
        $journalSupportedLocales = [];
        
        if ($journal != null) {
            $journalSupportedLocales = $journal->getSetting('supportedLocales');
            if (!is_array($journalSupportedLocales)) {
                $journalSupportedLocales = [];
            }
        }

        if (AppLocale::isLocaleValid($setLocale) && (!isset($journalSupportedLocales) || in_array($setLocale, $journalSupportedLocales)) && in_array($setLocale, $site->getSupportedLocales())) {
            $session = $request->getSession();
            $session->setSessionVar('currentLocale', $setLocale);
        }

        $source = trim((string) $request->getUserVar('source'));
        
        if (isset($source) && !empty($source)) {
            // [SECURITY] Prevent Open Redirect
            if (preg_match('#^($|/|index\.php)#', $source)) {
                $request->redirectUrl($request->getProtocol() . '://' . $request->getServerHost() . $source, false);
            }
        }

        // TODO: this seems bad form, but is kept for legacy purposes
        // Evaluate removal, or only trust the REFERER when it is a known base_url?
        if(isset($_SERVER['HTTP_REFERER'])) {
            $request->redirectUrl($_SERVER['HTTP_REFERER']);
        }

        $request->redirect(null, 'index');
    }

    /**
     * Become a given role.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function become($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate(true);

        $journal = $request->getJournal();
        $user = $request->getUser();

        $roleId = null;
        $setting = null;
        $deniedKey = null;

        switch (array_shift($args)) {
            case 'author':
                $roleId = ROLE_ID_AUTHOR;
                $setting = 'allowRegAuthor';
                $deniedKey = 'user.noRoles.submitArticleRegClosed';
                break;
            case 'reviewer':
                $roleId = ROLE_ID_REVIEWER;
                $setting = 'allowRegReviewer';
                $deniedKey = 'user.noRoles.regReviewerClosed';
                break;
            default:
                $request->redirect(null, null, 'index');
        }

        if ($journal->getSetting($setting)) {
            $role = new Role();
            $role->setJournalId($journal->getId());
            $role->setRoleId($roleId);
            $role->setUserId($user->getId());

            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleDao->insertRole($role);
            $source = trim((string) $request->getUserVar('source'));

            if ($source && preg_match('#^($|/|index\.php)#', $source)) {
                // Hanya redirect jika $source adalah path relatif lokal yang aman
                $request->redirectUrl($source);
            } else {
                // Alihkan ke halaman 'user' default jika 'source' berbahaya/eksternal
                $request->redirect(null, 'user');
            }
        } else {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('message', $deniedKey);
            return $templateMgr->display('common/message.tpl');
        }
    }

    /**
     * Display an authorization denied message.
     * @param array $args
     * @param object|null $request Request
     */
    public function authorizationDenied($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate(true);
        $authorizationMessage = htmlentities((string) $request->getUserVar('message'));
        $this->setupTemplate($request, true);
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_USER);
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('message', $authorizationMessage);
        return $templateMgr->display('common/message.tpl');
    }

    /**
     * Validate that user is logged in.
     * Redirects to login form if not logged in.
     * [WIZDAM] Polyfill for legacy signature mismatch ($loginCheck vs $requiredContexts)
     * @param mixed $requiredContexts (Legacy boolean loginCheck or context array)
     * @param object|null $request PKPRequest
     */
    public function validate($requiredContexts = null, $request = null) {
        // Logic to detect if $requiredContexts is actually the old boolean $loginCheck
        $loginCheck = true;
        if (is_bool($requiredContexts)) {
            $loginCheck = $requiredContexts;
            $requiredContexts = null; // Reset for parent call
        } elseif ($requiredContexts === null) {
            $loginCheck = true; // Default behavior
        }

        // [WIZDAM] Use correct parent call
        parent::validate($requiredContexts, $request);

        if ($loginCheck && !Validation::isLoggedIn()) {
            Validation::redirectLogin();
        }
        
        // [WIZDAM FIX] Kembalikan true agar subclass bisa mengecek return value
        return true;
    }

    /**
     * Setup common template variables.
     * @param object|null $request PKPRequest
     * @param boolean $subclass set to true if caller is below this handler in the hierarchy
     */
    public function setupTemplate($request = null, $subclass = false) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_AUTHOR, 
            LOCALE_COMPONENT_APP_EDITOR, 
            LOCALE_COMPONENT_APP_MANAGER
        );
        $templateMgr = TemplateManager::getManager();
        if ($subclass) {
            $templateMgr->assign('pageHierarchy', [[$request->url(null, 'user'), 'navigation.user']]);
        }
    }

    //
    // Captcha
    //

    /**
     * View Captcha
     * @param array $args
     * @param object|null $request
     */
    public function viewCaptcha($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $captchaId = (int) array_shift($args);
        import('lib.pkp.classes.captcha.CaptchaManager');
        $captchaManager = new CaptchaManager();
        if ($captchaManager->isEnabled()) {
            $captchaDao = DAORegistry::getDAO('CaptchaDAO');
            $captcha = $captchaDao->getCaptcha($captchaId);
            if ($captcha) {
                $captchaManager->generateImage($captcha);
                exit();
            }
        }
        $request->redirect(null, 'user');
    }
}
?>