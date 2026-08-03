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
 * @brief Handle requests for user functions with strict compliance.
 */

import('classes.handler.Handler');

class UserHandler extends Handler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [DEPRECATED] SHIM Backward compatibility.
     * Use __construct() instead.
     * @deprecated
     */
    public function UserHandler() {
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
     * Gather information about a user's role within a journal.
     * @param int $userId
     * @param int $journalId
     * @param array $submissionsCount Reference array for submission counts.
     * @param array $isValid Reference array for role validation status.
     * @return void
     */
    public function _getRoleDataForJournal($userId, $journalId, &$submissionsCount, &$isValid) {
        $userId = (int) $userId;
        $journalId = (int) $journalId;
        
        if (Validation::isJournalManager($journalId)) {
            if (!is_array($isValid['JournalManager'])) $isValid['JournalManager'] = [];
            $isValid['JournalManager'][$journalId] = true;
        }
        if (Validation::isSubscriptionManager($journalId)) {
            if (!is_array($isValid['SubscriptionManager'])) $isValid['SubscriptionManager'] = [];
            $isValid['SubscriptionManager'][$journalId] = true;
        }
        if (Validation::isAuthor($journalId)) {
            if (!is_array($submissionsCount['Author'])) $submissionsCount['Author'] = [];
            if (!is_array($isValid['Author'])) $isValid['Author'] = [];
            /** @var AuthorSubmissionDAO $authorSubmissionDao */
            $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
            $submissionsCount['Author'][$journalId] = $authorSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid['Author'][$journalId] = true;
        }
        if (Validation::isCopyeditor($journalId)) {
            if (!is_array($submissionsCount['Copyeditor'])) $submissionsCount['Copyeditor'] = [];
            if (!is_array($isValid['Copyeditor'])) $isValid['Copyeditor'] = [];
            /** @var CopyeditorSubmissionDAO $copyeditorSubmissionDao */
            $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
            $submissionsCount['Copyeditor'][$journalId] = $copyeditorSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid['Copyeditor'][$journalId] = true;
        }
        if (Validation::isLayoutEditor($journalId)) {
            if (!is_array($submissionsCount['LayoutEditor'])) $submissionsCount['LayoutEditor'] = [];
            if (!is_array($isValid['LayoutEditor'])) $isValid['LayoutEditor'] = [];
            /** @var LayoutEditorSubmissionDAO $layoutEditorSubmissionDao */
            $layoutEditorSubmissionDao = DAORegistry::getDAO('LayoutEditorSubmissionDAO');
            $submissionsCount['LayoutEditor'][$journalId] = $layoutEditorSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid['LayoutEditor'][$journalId] = true;
        }
        if (Validation::isEditor($journalId)) {
            if (!is_array($submissionsCount['Editor'])) $submissionsCount['Editor'] = [];
            if (!is_array($isValid['Editor'])) $isValid['Editor'] = [];
            /** @var EditorSubmissionDAO $editorSubmissionDao */
            $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
            $submissionsCount['Editor'][$journalId] = $editorSubmissionDao->getEditorSubmissionsCount($journalId);
            $isValid['Editor'][$journalId] = true;
        }
        if (Validation::isSectionEditor($journalId)) {
            if (!is_array($submissionsCount['SectionEditor'])) $submissionsCount['SectionEditor'] = [];
            if (!is_array($isValid['SectionEditor'])) $isValid['SectionEditor'] = [];
            /** @var SectionEditorSubmissionDAO $sectionEditorSubmissionDao */
            $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
            $submissionsCount['SectionEditor'][$journalId] = $sectionEditorSubmissionDao->getSectionEditorSubmissionsCount($userId, $journalId);
            $isValid['SectionEditor'][$journalId] = true;
        }
        if (Validation::isProofreader($journalId)) {
            if (!is_array($submissionsCount['Proofreader'])) $submissionsCount['Proofreader'] = [];
            if (!is_array($isValid['Proofreader'])) $isValid['Proofreader'] = [];
            /** @var ProofreaderSubmissionDAO $proofreaderSubmissionDao */
            $proofreaderSubmissionDao = DAORegistry::getDAO('ProofreaderSubmissionDAO');
            $submissionsCount['Proofreader'][$journalId] = $proofreaderSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid['Proofreader'][$journalId] = true;
        }
        if (Validation::isReviewer($journalId)) {
            if (!is_array($submissionsCount['Reviewer'])) $submissionsCount['Reviewer'] = [];
            if (!is_array($isValid['Reviewer'])) $isValid['Reviewer'] = [];
            /** @var ReviewerSubmissionDAO $reviewerSubmissionDao */
            $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
            $submissionsCount['Reviewer'][$journalId] = $reviewerSubmissionDao->getSubmissionsCount($userId, $journalId);
            $isValid['Reviewer'][$journalId] = true;
        }
    }

    /**
     * Determine if the journal's setup has been sufficiently completed.
     * @param object $journal Journal object.
     * @return bool True if setup is incomplete, false otherwise.
     */
    public function _checkIncompleteSetup($journal) {
        if ((string) $journal->getLocalizedInitials() === '' || 
            (string) $journal->getSetting('contactEmail') === '' ||
            (string) $journal->getSetting('contactName') === '' || 
            (string) $journal->getLocalizedSetting('abbreviation') === '') {
            return true;
        }
        return false;
    }

    /**
     * Change the locale for the current user.
     * @param array $args First parameter is the new locale.
     * @param object|null $request PKPRequest
     * @return void
     */
    public function setLocale($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [LUMERA SECURITY] STRICT CSRF
        import('lib.pkp.classes.validation.ValidatorCSRF'); 
        $clientToken = (string) $request->getUserVar(ValidatorCSRF::FIELD_NAME);
        
        if ($clientToken === '' || !ValidatorCSRF::checkSignedToken($clientToken, 'global', [], false)) {
            error_log('[LUMERA SECURITY] setLocale() blocked: Missing or invalid ValidatorCSRF token via direct URL.');
            $this->getDispatcher()->handle404($request);
            return;
        }

        $setLocale = array_shift($args);
        $site = $request->getSite();
        $journal = $request->getJournal();

        $journalSupportedLocales = [];
        if ($journal !== null) {
            $journalSupportedLocales = (array) $journal->getSetting('supportedLocales');
        }

        $isLocaleAllowed = is_string($setLocale)
            && preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $setLocale)
            && AppLocale::isLocaleValid($setLocale)
            && in_array($setLocale, $site->getSupportedLocales(), true)
            && (empty($journalSupportedLocales) || in_array($setLocale, $journalSupportedLocales, true));

        if (!$isLocaleAllowed) {
            $this->getDispatcher()->handle404($request);
            return;
        }

        $request->getSession()->setSessionVar('currentLocale', (string) $setLocale);

        $source = trim((string) $request->getUserVar('source'));
        if ($source !== '') {
            if (preg_match('#^($|/|index\.php)#', $source)) {
                $request->redirectUrl($source);
                return;
            }
        }

        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== '') {
            $request->redirectUrl((string) $_SERVER['HTTP_REFERER']);
            return;
        }

        $request->redirect(null, 'index');
    }

    /**
     * Become a given role.
     * @param array $args
     * @param object|null $request PKPRequest
     * @return void
     */
    public function become($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate(true);

        $journal = $request->getJournal();
        $user = $request->getUser();

        if ($journal === null || $user === null) {
            $request->redirect(null, 'index');
            return;
        }

        $roleId = null;
        $setting = null;
        $deniedKey = null;

        $action = array_shift($args);
        switch ($action) {
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
                return;
        }

        if ((bool) $journal->getSetting($setting)) {
            $role = new Role();
            $role->setJournalId((int) $journal->getId());
            $role->setRoleId((int) $roleId);
            $role->setUserId((int) $user->getId());

            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleDao->insertRole($role);
            
            $source = trim((string) $request->getUserVar('source'));

            if ($source !== '' && preg_match('#^($|/|index\.php)#', $source)) {
                $request->redirectUrl($source);
            } else {
                $request->redirect(null, 'user');
            }
        } else {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('message', $deniedKey);
            $templateMgr->display('common/message.tpl');
        }
    }

    /**
     * Display an authorization denied message.
     * @param array $args
     * @param object|null $request Request
     * @return void
     */
    public function authorizationDenied($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate(true);
        $authorizationMessage = htmlentities((string) $request->getUserVar('message'), ENT_QUOTES, 'UTF-8');
        $this->setupTemplate($request, true);
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_USER);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('message', $authorizationMessage);
        $templateMgr->display('common/message.tpl');
    }

    /**
     * Validate that user is logged in.
     * Redirects to login form if not logged in.
     * Polyfill for legacy signature mismatch ($loginCheck vs $requiredContexts).
     * @param mixed $requiredContexts
     * @param object|null $request PKPRequest
     * @return bool
     */
    public function validate($requiredContexts = null, $request = null) {
        $loginCheck = true;
        if (is_bool($requiredContexts)) {
            $loginCheck = $requiredContexts;
            $requiredContexts = null;
        } elseif ($requiredContexts === null) {
            $loginCheck = true;
        }

        parent::validate($requiredContexts, $request);

        if ($loginCheck && !Validation::isLoggedIn()) {
            Validation::redirectLogin();
        }
        
        return true;
    }

    /**
     * Setup common template variables.
     * @param object|null $request PKPRequest
     * @param bool $subclass Set to true if caller is below this handler in the hierarchy.
     * @return void
     */
    public function setupTemplate($request = null, $subclass = false) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_AUTHOR, 
            LOCALE_COMPONENT_APP_EDITOR, 
            LOCALE_COMPONENT_APP_MANAGER,
            LOCALE_COMPONENT_APP_PAYMENT
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
     * View Captcha.
     * @param array $args
     * @param object|null $request PKPRequest
     * @return void
     */
    public function viewCaptcha($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $captchaId = (int) array_shift($args);
        import('lib.pkp.classes.captcha.CaptchaManager');
        $captchaManager = new CaptchaManager();
        
        if ($captchaManager->isEnabled()) {
            /** @var CaptchaDAO $captchaDao */
            $captchaDao = DAORegistry::getDAO('CaptchaDAO');
            $captcha = $captchaDao->getCaptcha($captchaId);
            
            if ($captcha !== null) {
                $captchaManager->generateImage($captcha);
                exit();
            }
        }
        $request->redirect(null, 'user');
    }

}
?>