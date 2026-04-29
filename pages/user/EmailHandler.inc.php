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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.user.UserHandler');

class EmailHandler extends UserHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailHandler() {
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
     * Display a "send email" template or send an email.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function email($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $this->setupTemplate($request, true);

        $templateMgr = TemplateManager::getManager();

        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();

        // See if this is the Editor or Manager and an email template has been chosen
        $template = trim((string) $request->getUserVar('template'));
        if (!$journal || empty($template) || (
            !Validation::isJournalManager($journal->getId()) &&
            !Validation::isEditor($journal->getId()) &&
            !Validation::isSectionEditor($journal->getId())
        )) {
            $template = null;
        }

        // Determine whether or not this account is subject to
        // email sending restrictions.
        $canSendUnlimitedEmails = Validation::isSiteAdmin();
        $unlimitedEmailRoles = [
            ROLE_ID_JOURNAL_MANAGER,
            ROLE_ID_EDITOR,
            ROLE_ID_SECTION_EDITOR
        ];
        $roleDao = DAORegistry::getDAO('RoleDAO');
        if ($journal) {
            $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
            foreach ($roles as $role) {
                if (in_array($role->getRoleId(), $unlimitedEmailRoles)) $canSendUnlimitedEmails = true;
            }
        }

        // Check when this user last sent an email, and if it's too
        // recent, make them wait.
        if (!$canSendUnlimitedEmails) {
            $dateLastEmail = $user->getDateLastEmail();
            if ($dateLastEmail && strtotime($dateLastEmail) + ((int) Config::getVar('email', 'time_between_emails')) > strtotime(Core::getCurrentDate())) {
                $templateMgr->assign('pageTitle', 'email.compose');
                $templateMgr->assign('message', 'email.compose.tooSoon');
                $templateMgr->assign('backLink', 'javascript:history.back()');
                $templateMgr->assign('backLinkLabel', 'email.compose');
                return $templateMgr->display('common/message.tpl');
            }
        }

        $email = null;
        $articleId = (int) $request->getUserVar('articleId');
        if ($articleId) {
            // This message is in reference to an article.
            // Determine whether the current user has access
            // to the article in some form, and if so, use an
            // ArticleMailTemplate.
            $articleDao = DAORegistry::getDAO('ArticleDAO');

            $article = $articleDao->getArticle($articleId);
            $hasAccess = false;

            // First, conditions where access is OK.
            // 1. User is submitter
            if ($article && $article->getUserId() == $user->getId()) $hasAccess = true;
            // 2. User is section editor of article or full editor
            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $editAssignments = $editAssignmentDao->getEditAssignmentsByArticleId($articleId);
            while ($editAssignment = $editAssignments->next()) {
                if ($editAssignment->getEditorId() === $user->getId()) $hasAccess = true;
            }
            if (Validation::isEditor($journal->getId())) $hasAccess = true;

            // 3. User is reviewer
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            foreach ($reviewAssignmentDao->getBySubmissionId($articleId) as $reviewAssignment) {
                if ($reviewAssignment->getReviewerId() === $user->getId()) $hasAccess = true;
            }
            // 4. User is copyeditor
            $copyedSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $articleId);
            if ($copyedSignoff && $copyedSignoff->getUserId() === $user->getId()) $hasAccess = true;
            // 5. User is layout editor
            $layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
            if ($layoutSignoff && $layoutSignoff->getUserId() === $user->getId()) $hasAccess = true;
            // 6. User is proofreader
            $proofSignoff = $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
            if ($proofSignoff && $proofSignoff->getUserId() === $user->getId()) $hasAccess = true;

            // Last, "deal-breakers" -- access is not allowed.
            if (!$article || ($article && $article->getJournalId() !== $journal->getId())) $hasAccess = false;

            if ($hasAccess) {
                import('classes.mail.ArticleMailTemplate');
                $email = new ArticleMailTemplate($articleDao->getArticle($articleId), $template);
            }
        }

        if ($email === null) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate($template);
        }

        if ((int) $request->getUserVar('send') && !$email->hasErrors()) {
            $recipients = $email->getRecipients();
            $ccs = $email->getCcs();
            $bccs = $email->getBccs();

            // Make sure there aren't too many recipients (to
            // prevent use as a spam relay)
            $recipientCount = 0;
            if (is_array($recipients)) $recipientCount += count($recipients);
            if (is_array($ccs)) $recipientCount += count($ccs);
            if (is_array($bccs)) $recipientCount += count($bccs);

            if (!$canSendUnlimitedEmails && $recipientCount > ((int) Config::getVar('email', 'max_recipients'))) {
                $templateMgr->assign('pageTitle', 'email.compose');
                $templateMgr->assign('message', 'email.compose.tooManyRecipients');
                $templateMgr->assign('backLink', 'javascript:history.back()');
                $templateMgr->assign('backLinkLabel', 'email.compose');
                return $templateMgr->display('common/message.tpl');
            }
            
            // [WIZDAM] Replaced is_a with instanceof
            if ($email instanceof ArticleMailTemplate) {
                // Make sure the email gets logged if needed
                $email->send($request);
            } else {
                $email->send();
            }
            
            // [SECURITY FIX] Validasi 'redirectUrl' untuk mencegah Open Redirect
            $redirectUrl = trim((string) $request->getUserVar('redirectUrl'));
            
            // Validasi bahwa URL aman (lokal/relatif)
            if (empty($redirectUrl) || !preg_match('#^($|/|index\.php)#', $redirectUrl)) {
                // Jika kosong ATAU berbahaya/eksternal, paksa ke default yang aman
                $redirectUrl = $request->url(null, 'user');
            }
            
            $user->setDateLastEmail(Core::getCurrentDate());
            $userDao->updateObject($user);
            $request->redirectUrl($redirectUrl);
        } else {
            // [SECURITY FIX] Terapkan htmlspecialchars untuk mencegah XSS
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