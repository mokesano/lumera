<?php /* Smarty version 2.6.26, created on 2026-04-16 16:47:02
         compiled from user/changePassword.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'user/changePassword.tpl', 13, false),array('function', 'translate', 'user/changePassword.tpl', 31, false),array('function', 'fieldLabel', 'user/changePassword.tpl', 41, false),array('modifier', 'assign', 'user/changePassword.tpl', 13, false),array('modifier', 'escape', 'user/changePassword.tpl', 26, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "user.changePassword"); ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'changePassword'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="changePassword" class="pass-container">
    <div class="user-card password-card login-card">
        <div class="auth-header u-mb-24">
            <p class="auth-subtitle">Fill in this form to changes your password.</p>
        </div>
        
        <form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'savePassword'), $this);?>
">
    
                <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
            
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

            <div class="alert alert-info">
                <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.changePasswordInstructions"), $this);?>
</p>
            </div>

            <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.oldPassword"), $this);?>
</h3>
            
                        <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="oldPassword" name="oldPassword" class="form-control" 
                        value="<?php echo ((is_array($_tmp=$this->_tpl_vars['oldPassword'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                    <label for="loginPassword" class="form-control-label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'oldPassword','key' => "user.profile.oldPassword"), $this);?>
<span class="required-indicator">*</span>
                    </label>
                    <div class="password-toggle" data-target="oldPassword">
                        <svg class="icon-eye-off" viewBox="0 0 24 24">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        </svg>
                        <svg class="icon-eye" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    
                                        <div class="password-strength-indicator" id="oldPasswordStrengthIndicator">
                        <div class="strength-bar">
                            <div class="strength-segment" data-level="very-weak"></div>
                            <div class="strength-segment" data-level="weak"></div>
                            <div class="strength-segment" data-level="fair"></div>
                            <div class="strength-segment" data-level="good"></div>
                            <div class="strength-segment" data-level="strong"></div>
                            <div class="strength-segment" data-level="very-strong"></div>
                        </div>
                        <span class="strength-label" id="oldStrengthLabel">Empty</span>
                    </div>
                    <div class="error-message">Please enter your password</div>
                </div>
            </div>

            <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.newPassword"), $this);?>
</h3>
            
                        <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" 
                           value="<?php echo ((is_array($_tmp=$this->_tpl_vars['password'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                    <label for="password" class="form-control-label">
                        <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'password','key' => "user.profile.newPassword"), $this);?>
<span class="required-indicator">*</span>
                    </label>
                    <div class="password-toggle" data-target="password">
                        <svg class="icon-eye-off" viewBox="0 0 24 24">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        </svg>
                        <svg class="icon-eye" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                                        <div class="password-strength-indicator" id="passwordStrengthIndicator">
                        <div class="strength-bar">
                            <div class="strength-segment" data-level="very-weak"></div>
                            <div class="strength-segment" data-level="weak"></div>
                            <div class="strength-segment" data-level="fair"></div>
                            <div class="strength-segment" data-level="good"></div>
                            <div class="strength-segment" data-level="strong"></div>
                            <div class="strength-segment" data-level="very-strong"></div>
                        </div>
                        <span class="strength-label" id="strengthLabel">Empty</span>
                            </div>
                    <div class="error-message">Please enter <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'password','key' => "user.profile.newPassword"), $this);?>
</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'password','key' => "user.profile.newPassword"), $this);?>
 looks good!</div>
                </div>
                        
                                <div class="password-requirements">
                    <p class="requirements-title">Password must contain at least:</p>
                    <div class="requirements-grid">
                        <div class="requirement-item" id="req-length">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text"><?php echo $this->_tpl_vars['minPasswordLength']; ?>
 characters</span>
                        </div>
                        <div class="requirement-item" id="req-number">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text">1 number</span>
                        </div>
                        <div class="requirement-item" id="req-special">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text">1 special character</span>
                        </div>
                        <div class="requirement-item" id="req-uppercase">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text">1 UPPERCASE</span>
                        </div>
                        <div class="requirement-item" id="req-lowercase">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text">1 lowercase</span>
                        </div>
                        <div class="requirement-item" id="req-username">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text">Different from username</span>
                        </div>
                    </div>
                </div>
                <div class="form-help-text u-js-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordLengthRestriction",'length' => $this->_tpl_vars['minPasswordLength']), $this);?>
</div>
            </div>

            <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.repeatNewPassword"), $this);?>
</h3>
            
                        <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password2" name="password2" class="form-control" 
                           value="<?php echo ((is_array($_tmp=$this->_tpl_vars['password2'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                    <label for="password2" class="form-control-label">
                        <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'password2','key' => "user.profile.repeatNewPassword"), $this);?>
<span class="required-indicator">*</span>
                    </label>
                    <div class="password-toggle" data-target="password2">
                        <svg class="icon-eye-off" viewBox="0 0 24 24">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        </svg>
                        <svg class="icon-eye" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    <div class="error-message">Passwords do not match</div>
                    <div class="success-message">Passwords match!</div>
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
<?php echo '\';grecaptcha.ready(function(){grecaptcha.execute(d,{action:\'change_password\'}).then(function(e){document.getElementById(\'g-recaptcha-response\').value=e;b.submit()})})})}}});</script>
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
                    
                                <?php if ($this->_tpl_vars['turnstileEnabled'] || $this->_tpl_vars['reCaptchaEnabled']): ?>
                    <div class="form-help-text u-hide">
                        ScholarWizdam register system protected by 
                        <?php if ($this->_tpl_vars['turnstileEnabled'] && $this->_tpl_vars['reCaptchaEnabled']): ?>Cloudflare Turnstile & Google reCAPTCHA
                        <?php elseif ($this->_tpl_vars['turnstileEnabled']): ?>Cloudflare Turnstile
                        <?php else: ?>Google reCAPTCHA<?php endif; ?>.
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
                    
            <p class="action-button">
                <input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button" />
                <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="defaultButton" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','escape' => false), $this);?>
'" />
            </p>
        
        </form>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>