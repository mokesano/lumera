<?php /* Smarty version 2.6.26, created on 2026-04-05 23:54:36
         compiled from user/login.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'user/login.tpl', 24, false),array('function', 'translate', 'user/login.tpl', 49, false),array('modifier', 'escape', 'user/login.tpl', 56, false),array('modifier', 'strip_unsafe_html', 'user/login.tpl', 103, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "user.login"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if (! $this->_tpl_vars['registerOp']): ?>
    <?php $this->assign('registerOp', 'register'); ?>
<?php endif; ?>
<?php if (! $this->_tpl_vars['registerLocaleKey']): ?>
    <?php $this->assign('registerLocaleKey', "user.login.registerNewAccount"); ?>
<?php endif; ?>

<div class="login-container">
    <div class="auth-card login-card">
        <div class="auth-header u-mb-32">
            <h4 class="form-section-label">Welcome back!</h4>
            <p class="auth-subtitle">Please enter your credentials below to sign in to your account<?php if (! $this->_tpl_vars['hideRegisterLink']): ?>, or <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => $this->_tpl_vars['registerOp']), $this);?>
">register</a> if you don’t have an account yet<?php endif; ?>.</p>
        </div>

                <?php if ($this->_tpl_vars['orcidSsoEnabled'] || $this->_tpl_vars['googleSsoEnabled']): ?>
        <div class="sso-section u-text-center u-mb-32">
            <?php if ($this->_tpl_vars['orcidSsoEnabled']): ?>
            <div class="sso-orcid u-mb-24">
                <button type="button" onclick="window.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'login','op' => "orcid-auth"), $this);?>
'" class="link-button wiz-sso-button wiz-sso-orcid flex">
                    <svg class="u-js-hide" width="87" height="30" viewBox="0 0 221 77" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="nonzero"><path fill="#A6A8AB" d="M26.922 15C43.418 15 54 26.975 54 41c0 13.708-10.115 25.999-27.078 25.999C10.427 67.156 0 54.866 0 41.157 0 26.975 10.738 15 26.922 15zm0 45.538c11.205 0 19.608-8.194 19.608-19.381 0-11.188-8.248-19.381-19.608-19.381-11.204 0-19.452 8.193-19.452 19.38 0 11.03 8.248 19.382 19.452 19.382zM75.687 16c10.18 0 16.602 5.435 16.602 13.975 0 5.745-2.976 10.093-8.458 12.267 4.542 2.95 7.362 7.609 11.277 13.82C97.301 59.478 98.554 61.186 102 66h-8.614L86.65 55.752C79.916 45.503 76.94 44.26 73.18 44.26h-2.976V66H63V16h12.687zm-5.482 21.74h4.699c7.83 0 10.024-4.038 9.867-8.075 0-4.659-2.82-7.61-9.867-7.61h-4.7V37.74zM146.536 27.097c-6.185-3.614-11.443-5.342-17.165-5.342-11.443 0-19.948 8.17-19.948 19.324 0 11.31 8.196 19.166 20.103 19.166 5.567 0 11.752-2.043 17.474-5.656v8.012C142.052 65.271 136.794 67 128.907 67 110.196 67 102 52.39 102 41.707 102 26.31 113.443 15 129.371 15c5.103 0 10.361 1.257 17.165 4.085v8.012z"/><path fill="#A5CD39" d="M175 16h19.36C212.791 16 221 29.199 221 41c0 12.888-10.067 25-26.485 25H175V16zm7.125 43.478h11.46c16.263 0 19.98-12.422 19.98-18.633 0-10.093-6.35-18.634-20.289-18.634H182.28v37.267h-.154z"/><g fill="#A5CD39"><path d="M164.114 66h-7.228V15.4h7.228v28.129zM165.214 4.714c0 2.672-2.2 4.715-4.714 4.715-2.671 0-4.714-2.2-4.714-4.715 0-2.514 2.2-4.714 4.714-4.714 2.671 0 4.714 2.2 4.714 4.714z"/></g></g></svg>
                    <svg width="30px" height="30px" viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#A6CE39"/><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"/><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"/><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"/></g></g></g></g></g>
                    </svg>
                    <span class="u-ml-16">Continue with ORCID</span>
                </button>
            </div>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['googleSsoEnabled']): ?>
            <div class="sso-google">
                <button type="button" onclick="window.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => "google-auth"), $this);?>
'" class="link-button wiz-sso-button wiz-sso-google flex">
                    <svg width="30px" height="30px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="LgbsSe-Bz112c"><g><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></g></svg>
                    <span class="u-ml-16">Continue with Google</span>
                </button>
            </div>
            <?php endif; ?>
            <div class="flex items-center justify-center wiz-line">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.or"), $this);?>

            </div>
        </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['source']): ?>
            <input type="hidden" name="source" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['source'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
        <?php endif; ?>

                <?php if ($this->_tpl_vars['loginMessage']): ?>
            <div class="alert alert-info">
                <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ($this->_tpl_vars['loginMessage'])), $this);?>
</p>
            </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['error']): ?>
            <div class="alert alert-error">
                <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ($this->_tpl_vars['error']),'reason' => $this->_tpl_vars['reason']), $this);?>
</p>
            </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL): ?>
            <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.implicitAuth"), $this);?>
</h3>
        <?php endif; ?>

        <?php if ($this->_tpl_vars['implicitAuth']): ?>
            <div class="alert alert-info">
                <p><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.implicitAuthLogin"), $this);?>
</strong></p>
                <p>Login through your institution's authentication system.</p>
            </div>
            <div class="form-actions">
                <a id="implicitAuthLogin" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'implicitAuthLogin'), $this);?>
" class="btn-primary" style="text-decoration: none; display: block; text-align: center;">
                    <svg style="width: 16px; height: 16px; margin-right: 8px; fill: currentColor; vertical-align: middle;" viewBox="0 0 24 24">
                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                    </svg>
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.implicitAuthLogin"), $this);?>

                </a>
            </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL): ?>
            <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.localAuth"), $this);?>
</h3>
        <?php endif; ?>

                <?php if (! $this->_tpl_vars['implicitAuth'] || $this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL): ?>
            <form id="signinForm" method="post" action="<?php echo $this->_tpl_vars['loginUrl']; ?>
">
                                <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
                <input type="hidden" name="source" value="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['source'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
                
                                <div class="form-group">
                    <input type="text" 
                           id="loginUsername" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo ((is_array($_tmp=$this->_tpl_vars['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                           maxlength="32"
                           required>
                    <label for="loginUsername" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.usernameOrEmail"), $this);?>
</label>
                    <div class="error-message">Please enter your email or username</div>
                    <div class="success-message">Email or Username is available!</div>
                </div>

                                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" 
                               id="loginPassword" 
                               name="password" 
                               class="form-control" 
                               value="<?php echo ((is_array($_tmp=$this->_tpl_vars['password'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                               required>
                        <label for="loginPassword" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.password"), $this);?>
</label>
                        <div class="password-toggle" data-target="loginPassword">
                            <svg class="icon-eye" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24">
                                <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                            </svg>
                        </div>
                                                <div class="password-strength-indicator" id="loginPasswordStrengthIndicator">
                            <div class="strength-bar">
                                <div class="strength-segment" data-level="very-weak"></div>
                                <div class="strength-segment" data-level="weak"></div>
                                <div class="strength-segment" data-level="fair"></div>
                                <div class="strength-segment" data-level="good"></div>
                                <div class="strength-segment" data-level="strong"></div>
                                <div class="strength-segment" data-level="very-strong"></div>
                            </div>
                            <span class="strength-label" id="loginStrengthLabel"></span>
                        </div>
                        <div class="error-message">Please enter your password</div>
                        <div class="success-message">Looks good!</div>
                    </div>
                </div>

                                <?php if ($this->_tpl_vars['captchaEnabled']): ?>
                <div class="form-group">
                    <label class="form-section-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.captchaField"), $this);?>
<span class="required-indicator">*</span></label>
                    <img src="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'viewCaptcha','path' => $this->_tpl_vars['captchaId']), $this);?>
" alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.captchaField.altText"), $this);?>
" width="100%" height="100" />
                    <input name="captcha" id="captcha" value="" size="20" maxlength="32" class="form-control" required />
                    <input type="hidden" name="captchaId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['captchaId'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quoted') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'quoted')); ?>
" />
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.captchaField"), $this);?>
 is required</div>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.captchaField.description"), $this);?>
</div>
                </div>
                <?php endif; ?>
            
                                <?php if ($this->_tpl_vars['turnstileEnabled'] || $this->_tpl_vars['reCaptchaEnabled']): ?>
                <div class="security-barrier">
                                        <?php if ($this->_tpl_vars['turnstileEnabled']): ?>
                        <div class="turnstile-group">
                            <div id="turnstile-container" class="cf-turnstile"
                                data-sitekey="<?php echo ((is_array($_tmp=$this->_tpl_vars['turnstilePublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                                data-theme="light"
                                data-size="flexible"
                                data-callback="onTurnstileSuccess">
                            </div>
                            <div id="turnstile-loading"><span class="spinner"></span> Loading security verification...</div>
                            <div id="turnstile-error"></div>
                        </div>
                    <?php endif; ?>

                                        <?php if ($this->_tpl_vars['reCaptchaEnabled']): ?>
                    <div class="recaptcha-group">
                        <?php if ($this->_tpl_vars['reCaptchaVersion'] == 3): ?>
                            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"></script>
                            <?php echo '
                            <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById(\'g-recaptcha-response\');if(a){var b=a.closest(\'form\');if(b){b.addEventListener(\'submit\',function(c){c.preventDefault();var d=\''; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '\';grecaptcha.ready(function(){grecaptcha.execute(d,{action:\'login\'}).then(function(e){document.getElementById(\'g-recaptcha-response\').value=e;b.submit()})})})}}});</script>
                            '; ?>

                        <?php elseif ($this->_tpl_vars['reCaptchaVersion'] == 2): ?>
                            <div class="g-recaptcha" 
                                data-sitekey="<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                data-theme="light"
                                data-size="flexible">
                            </div>
                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>        
                        <?php else: ?>
                                                        <div class="value u-mb-24">
                                <?php echo $this->_tpl_vars['reCaptchaHtml']; ?>

                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                                <?php if ($this->_tpl_vars['showRemember']): ?>
                <div class="form-options-row">
                    <div class="checkbox-container">
                        <div class="checkbox-content">
                            <input type="checkbox" 
                                   id="loginRemember" 
                                   name="remember" 
                                   value="1" class="checkbox-input"<?php if ($this->_tpl_vars['remember']): ?> checked="checked"<?php endif; ?>>
                            <span class="checkbox-indicator"></span>
                            <div class="checkbox-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.rememberUsernameAndPassword"), $this);?>
</div>
                        </div>
                    </div>
                    <div class="forgot-password-link">
                        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'lostPassword'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.forgotPassword"), $this);?>
</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="form-options-row">
                    <div></div>
                    <div class="forgot-password-link">
                        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'lostPassword'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.forgotPassword"), $this);?>
</a>
                    </div>
                </div>
                <?php endif; ?>

                                <div class="form-actions">
                    <button type="submit" id="loginButton" class="btn-primary"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login"), $this);?>
</button>
                </div>

                <?php if (! $this->_tpl_vars['hideRegisterLink']): ?>
                <div class="form-links">
                    <div class="u-text-center" style="text-align: center;">
                        <p class="u-mb-0" style="margin-bottom: 0;">Don't have an account yet?
                            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => $this->_tpl_vars['registerOp']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['registerLocaleKey']), $this);?>
</a>
                        </p>   
                    </div>
                </div>
                <?php endif; ?>

                                <script type="text/javascript">
                <!--
                    document.getElementById('<?php if ($this->_tpl_vars['username']): ?>loginPassword<?php else: ?>loginUsername<?php endif; ?>').focus();
                // -->
                </script>

            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->_tpl_vars['turnstileEnabled']): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>