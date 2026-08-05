<?php
declare(strict_types=1);

/**
 * @file pages/user/EmailHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user emails.
 */

import('pages.user.UserHandler');

class EmailHandler extends UserHandler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function EmailHandler() {
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
     * Display a "send email" template or send an email.
     * @param array $args
     * @param object|null $request
     * @return void
     */
    public function email($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request, true);

        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();
        $user = $request->getUser();

        if ($journal === null || $user === null) {
            $request->redirect(null, 'login');
            return;
        }

        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $template = trim((string) $request->getUserVar('template'));
        if (empty($template) || (
            !Validation::isJournalManager($journal->getId()) &&
            !Validation::isEditor($journal->getId()) &&
            !Validation::isSectionEditor($journal->getId())
        )) {
            $template = null;
        }

        $canSendUnlimitedEmails = Validation::isSiteAdmin();
        $unlimitedEmailRoles = [
            ROLE_ID_JOURNAL_MANAGER,
            ROLE_ID_EDITOR,
            ROLE_ID_SECTION_EDITOR
        ];

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
        foreach ($roles as $role) {
            if (in_array($role->getRoleId(), $unlimitedEmailRoles, true)) {
                $canSendUnlimitedEmails = true;
                break;
            }
        }

        if (!$canSendUnlimitedEmails) {
            $dateLastEmail = $user->getDateLastEmail();
            $timeBetweenEmails = (int) Config::getVar('email', 'time_between_emails');
            
            if ($dateLastEmail !== null && (strtotime($dateLastEmail) + $timeBetweenEmails) > strtotime(Core::getCurrentDate())) {
                $templateMgr->assign('pageTitle', 'email.compose');
                $templateMgr->assign('message', 'email.compose.tooSoon');
                $templateMgr->assign('backLink', 'javascript:history.back()');
                $templateMgr->assign('backLinkLabel', 'email.compose');
                $templateMgr->display('common/message.tpl');
                return;
            }
        }

        $email = null;
        $articleId = (int) $request->getUserVar('articleId');
        
        if ($articleId > 0) {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            $hasAccess = false;

            if ($article !== null) {
                if ($article->getUserId() === $user->getId()) {
                    $hasAccess = true;
                }

                /** @var EditAssignmentDAO $editAssignmentDao */
                $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
                $editAssignments = $editAssignmentDao->getEditAssignmentsByArticleId($articleId);
                while ($editAssignment = $editAssignments->next()) {
                    if ($editAssignment->getEditorId() === $user->getId()) {
                        $hasAccess = true;
                    }
                }
                
                if (Validation::isEditor($journal->getId())) {
                    $hasAccess = true;
                }

                /** @var ReviewAssignmentDAO $reviewAssignmentDao */
                $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
                foreach ($reviewAssignmentDao->getBySubmissionId($articleId) as $reviewAssignment) {
                    if ($reviewAssignment->getReviewerId() === $user->getId()) {
                        $hasAccess = true;
                    }
                }

                $copyedSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
                if ($copyedSignoff !== null && $copyedSignoff->getUserId() === $user->getId()) {
                    $hasAccess = true;
                }

                $layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
                if ($layoutSignoff !== null && $layoutSignoff->getUserId() === $user->getId()) {
                    $hasAccess = true;
                }

                $proofSignoff = $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
                if ($proofSignoff !== null && $proofSignoff->getUserId() === $user->getId()) {
                    $hasAccess = true;
                }

                if ($article->getJournalId() !== $journal->getId()) {
                    $hasAccess = false;
                }
            }

            if ($hasAccess) {
                import('classes.mail.ArticleMailTemplate');
                $email = new ArticleMailTemplate($article, $template);
            }
        }

        if ($email === null) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate($template);
        }

        // [LUMERA FIX] Avoid (int) casting on 'send' to prevent button submission from evaluating to 0 incorrectly.
        $isSend = $request->isPost() && $request->getUserVar('send') !== null;

        if ($isSend && !$email->hasErrors()) {
            $recipients = $email->getRecipients();
            $ccs = $email->getCcs();
            $bccs = $email->getBccs();

            $recipientCount = 0;
            if (is_array($recipients)) $recipientCount += count($recipients);
            if (is_array($ccs)) $recipientCount += count($ccs);
            if (is_array($bccs)) $recipientCount += count($bccs);

            $maxRecipients = (int) Config::getVar('email', 'max_recipients');
            if (!$canSendUnlimitedEmails && $recipientCount > $maxRecipients) {
                $templateMgr->assign('pageTitle', 'email.compose');
                $templateMgr->assign('message', 'email.compose.tooManyRecipients');
                $templateMgr->assign('backLink', 'javascript:history.back()');
                $templateMgr->assign('backLinkLabel', 'email.compose');
                $templateMgr->display('common/message.tpl');
                return;
            }
            
            if ($email instanceof ArticleMailTemplate) {
                $email->send($request);
            } else {
                $email->send();
            }
            
            $redirectUrl = trim((string) $request->getUserVar('redirectUrl'));
            if (empty($redirectUrl) || !preg_match('#^($|/|index\.php)#', $redirectUrl)) {
                $redirectUrl = $request->url(null, 'user');
            }
            
            $user->setDateLastEmail(Core::getCurrentDate());
            $userDao->updateObject($user);
            $request->redirectUrl($redirectUrl);
        } else {
            $safeRedirectUrl = htmlspecialchars(trim((string) $request->getUserVar('redirectUrl')), ENT_QUOTES, 'UTF-8');
            
            $email->displayEditForm(
                $request->url(null, null, 'email'), 
                ['redirectUrl' => $safeRedirectUrl, 'articleId' => $articleId], 
                null, 
                ['disableSkipButton' => true, 'articleId' => $articleId]
            );
        }
    }
    
}
?>