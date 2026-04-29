<?php
declare(strict_types=1);

/**
 * @defgroup rt_ojs_form
 */

/**
 * @file classes/comment/form/CommentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CommentForm
 * @ingroup rt_ojs_form
 * @see Comment, CommentDAO
 *
 * @brief Form to change metadata information for an RT comment.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 * - TRUE MODULAR SECURITY: Decoupled Default Captcha, reCAPTCHA, and Turnstile
 */

import('lib.pkp.classes.form.Form');

class CommentForm extends Form {

    /** @var int|null the ID of the comment */
    protected $commentId = null;

    /** @var int|null the ID of the article */
    protected $articleId = null;

    /** @var Comment|null current comment */
    protected $comment = null;

    /** @var int|null parent comment ID if applicable */
    protected $parentId = null;

    /** @var int|null Galley view by which the user entered the comments pages */
    protected $galleyId = null;

    // --- [WIZDAM SECURITY] Tiga pilar keamanan berdiri sendiri ---
    protected $captchaEnabled = false;
    protected $reCaptchaEnabled = false;
    protected $reCaptchaVersion = 0;
    protected $turnstileEnabled = false;

    /**
     * Constructor.
     * @param int|null $commentId
     * @param int $articleId
     * @param int $galleyId
     * @param int|null $parentId
     */
    public function __construct($commentId, $articleId, $galleyId, $parentId = null) {
        parent::__construct('comment/comment.tpl');

        $this->articleId = $articleId;

        $commentDao = DAORegistry::getDAO('CommentDAO');
        $this->comment = $commentDao->getById($commentId, $articleId);

        if (isset($this->comment)) {
            $this->commentId = $commentId;
        }

        $this->parentId = $parentId;
        $this->galleyId = $galleyId;

        $this->addCheck(new FormValidator($this, 'title', 'required', 'comments.titleRequired'));

        // --- [WIZDAM SECURITY] PENENTUAN STATUS KEAMANAN ---
        
        // PILAR 1 & 2: MODERN SECURITY (Bisa aktif bersamaan)
        $this->turnstileEnabled = (bool) Config::getVar('turnstile', 'turnstile');
        $this->reCaptchaEnabled = (bool) Config::getVar('recaptcha', 'recaptcha');
        if ($this->reCaptchaEnabled) {
            $this->reCaptchaVersion = (int) Config::getVar('recaptcha', 'recaptcha_version');
        }

        // PILAR 3: DEFAULT CAPTCHA (HANYA JIKA TURNSTILE & RECAPTCHA OFF)
        if (!$this->turnstileEnabled && !$this->reCaptchaEnabled) {
            if (Config::getVar('captcha', 'captcha') && Config::getVar('captcha', 'captcha_on_comments')) {
                import('lib.pkp.classes.captcha.CaptchaManager');
                $captchaManager = new CaptchaManager();
                if ($captchaManager->isEnabled()) {
                    $this->captchaEnabled = true;
                }
            }
        }

        // --- INJEKSI VALIDATOR BERDASARKAN PILAR AKTIF ---

        // 1. Validator Default Captcha
        if ($this->captchaEnabled) {
            $this->addCheck(new FormValidatorCaptcha($this, 'captcha', 'captchaId', 'common.captchaField.badCaptcha'));
        }

        // 2. Validator Turnstile
        if ($this->turnstileEnabled) {
            $this->addCheck(new FormValidatorCustom(
                $this, 'cf-turnstile-response', 'required', 'common.captchaField.badCaptcha',
                function($turnstileResponse) {
                    $ch = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                        'secret' => Config::getVar('turnstile', 'turnstile_private_key'),
                        'response' => $turnstileResponse
                    ]));
                    $result = json_decode(curl_exec($ch));
                    curl_close($ch);
                    return ($result && $result->success);
                }
            ));
        }

        // 3. Validator reCAPTCHA
        if ($this->reCaptchaEnabled) {
            if ($this->reCaptchaVersion === 2 || $this->reCaptchaVersion === 3) {
                $this->addCheck(new FormValidatorCustom(
                    $this, 'g-recaptcha-response', 'required', 'common.captchaField.badCaptcha',
                    function($recaptchaResponse) {
                        $ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                            'secret' => Config::getVar('recaptcha', 'recaptcha_private_key'),
                            'response' => $recaptchaResponse
                        ]));
                        $result = json_decode(curl_exec($ch));
                        curl_close($ch);

                        if (!$result || !$result->success) return false;
                        if ($this->reCaptchaVersion === 3 && isset($result->score) && $result->score < 0.5) return false;
                        return true;
                    }
                ));
            } elseif ($this->reCaptchaVersion === 0) {
                // [LEGACY SHIM] Validasi reCAPTCHA v1
                $this->addCheck(new FormValidatorCustom(
                    $this, 'recaptcha_response_field', 'required', 'common.captchaField.badCaptcha',
                    function($recaptchaResponse) {
                        require_once('lib/recaptcha/recaptchalib.php');
                        $request = Application::get()->getRequest();
                        $resp = recaptcha_check_answer(
                            Config::getVar('recaptcha', 'recaptcha_private_key'),
                            $_SERVER["REMOTE_ADDR"],
                            $request->getUserVar('recaptcha_challenge_field'),
                            $recaptchaResponse
                        );
                        return ($resp && $resp->is_valid);
                    }
                ));
            }
        }

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CommentForm($commentId, $articleId, $galleyId, $parentId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($commentId, $articleId, $galleyId, $parentId);
    }

    /**
     * Initialize form data from current comment.
     */
    public function initData() {
        if (isset($this->comment)) {
            $comment = $this->comment;
            $this->_data = [
                'title' => $comment->getTitle(),
                'body' => $comment->getBody(),
                'posterName' => $comment->getPosterName(),
                'posterEmail' => $comment->getPosterEmail()
            ];
        } else {
            $commentDao = DAORegistry::getDAO('CommentDAO');
            $comment = $commentDao->getById($this->parentId, $this->articleId);
            $this->_data = [];
            
            // [WIZDAM] Singleton Fallback
            $request = Application::get()->getRequest();
            $user = $request->getUser();
            
            if ($user) {
                $this->_data['posterName'] = $user->getFullName();
                $this->_data['posterEmail'] = $user->getEmail();
                $this->_data['title'] = ($comment ? __('common.re') . ' ' . $comment->getTitle() : '');
            }
        }
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        if (isset($this->comment)) {
            $templateMgr->assign('comment', $this->comment);
            $templateMgr->assign('commentId', $this->commentId);
        }

        $user = $request->getUser();
        if ($user) {
            $templateMgr->assign('userName', $user->getFullName());
            $templateMgr->assign('userEmail', $user->getEmail());
        }

        // --- [WIZDAM SECURITY] LEMPAR FLAG KE SMARTY ---
        
        // 1. Turnstile
        $templateMgr->assign('turnstileEnabled', $this->turnstileEnabled);
        if ($this->turnstileEnabled) {
            $templateMgr->assign('turnstilePublicKey', Config::getVar('turnstile', 'turnstile_public_key'));
        }

        // 2. reCAPTCHA
        $templateMgr->assign('reCaptchaEnabled', $this->reCaptchaEnabled);
        if ($this->reCaptchaEnabled) {
            $templateMgr->assign('reCaptchaVersion', $this->reCaptchaVersion);
            $publicKey = Config::getVar('recaptcha', 'recaptcha_public_key');
            $templateMgr->assign('reCaptchaPublicKey', $publicKey);

            if ($this->reCaptchaVersion === 2) {
                $reCaptchaHtml = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . "\n";
                $reCaptchaHtml .= '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($publicKey, ENT_QUOTES, 'UTF-8') . '"></div>';
                $templateMgr->assign('reCaptchaHtml', $reCaptchaHtml);
            } elseif ($this->reCaptchaVersion === 0) {
                require_once('lib/recaptcha/recaptchalib.php');
                $templateMgr->assign('reCaptchaHtml', recaptcha_get_html($publicKey));
            }
        }

        // 3. Default Captcha
        $templateMgr->assign('captchaEnabled', $this->captchaEnabled);
        if ($this->captchaEnabled) {
            import('lib.pkp.classes.captcha.CaptchaManager');
            $captchaManager = new CaptchaManager();
            $captcha = $captchaManager->createCaptcha();
            if ($captcha) {
                $templateMgr->assign('captchaId', $captcha->getId());
                $templateMgr->assign('captcha', $captcha);
                $this->setData('captchaId', $captcha->getId());
            }
        }

        $templateMgr->assign('parentId', $this->parentId);
        $templateMgr->assign('articleId', $this->articleId);
        $templateMgr->assign('galleyId', $this->galleyId);
        $templateMgr->assign('enableComments', $journal->getSetting('enableComments'));

        parent::display($request, $template);
    }


    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $userVars = [
            'body',
            'title',
            'posterName',
            'posterEmail'
        ];

        // --- [WIZDAM SECURITY] TANGKAP VARIABEL POST ---
        if ($this->turnstileEnabled) {
            $userVars[] = 'cf-turnstile-response';
        }

        if ($this->reCaptchaEnabled) {
            if ($this->reCaptchaVersion === 0) {
                $userVars[] = 'recaptcha_challenge_field';
                $userVars[] = 'recaptcha_response_field';
            } else {
                $userVars[] = 'g-recaptcha-response';
            }
        }

        if ($this->captchaEnabled) {
            $userVars[] = 'captchaId';
            $userVars[] = 'captcha';
        }

        $this->readUserVars($userVars);
    }

    /**
     * Save changes to comment.
     * @return int the comment ID
     */
    public function execute() {
        // [WIZDAM] Singleton Fallback
        $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();
        $enableComments = $journal->getSetting('enableComments');

        $commentDao = DAORegistry::getDAO('CommentDAO');

        $comment = $this->comment;
        if (!isset($comment)) {
            $comment = $commentDao->newDataObject();
        }

        $user = $request->getUser();

        $comment->setTitle($this->getData('title'));
        $comment->setBody($this->getData('body'));

        if (($enableComments == COMMENTS_ANONYMOUS || $enableComments == COMMENTS_UNAUTHENTICATED) && ($request->getUserVar('anonymous') || $user == null)) {
            $comment->setPosterName($this->getData('posterName'));
            $comment->setPosterEmail($this->getData('posterEmail'));
            $comment->setUser(null);
        } else {
            $comment->setPosterName($user->getFullName());
            $comment->setPosterEmail($user->getEmail());
            $comment->setUser($user);
        }

        $comment->setParentCommentId($this->parentId);

        if (isset($this->comment)) {
            $commentDao->updateComment($comment);
        } else {
            $comment->setSubmissionId($this->articleId);
            $comment->setChildCommentCount(0);
            $commentDao->insertComment($comment);
            $this->commentId = $comment->getId();
        }

        return $this->commentId;
    }

}
?>