<?php /* Smarty version 2.6.26, created on 2026-04-16 13:14:12
         compiled from user/register.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'user/register.tpl', 23, false),array('function', 'url', 'user/register.tpl', 29, false),array('function', 'form_language_chooser', 'user/register.tpl', 84, false),array('function', 'html_options_translate', 'user/register.tpl', 304, false),array('function', 'html_options', 'user/register.tpl', 326, false),array('modifier', 'escape', 'user/register.tpl', 34, false),array('modifier', 'assign', 'user/register.tpl', 39, false),array('modifier', 'nl2br', 'user/register.tpl', 689, false),)), $this); ?>

<?php echo ''; ?><?php $this->assign('pageTitle', "user.register"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php $this->assign('isloggedin', "Validation::isLoggedIn()"); ?>

<div class="auth-container">
    <div class="auth-card register-card">
        <div class="auth-header u-mb-24">
            <p class="auth-subtitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.completeForm",'contextName' => $this->_tpl_vars['siteTitle']), $this);?>
</p>
        </div>

                <?php if ($this->_tpl_vars['implicitAuth'] === true && ! $this->_tpl_vars['isloggedin']): ?>
            <div class="alert alert-info">
                <p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'implicitAuthLogin'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.implicitAuth"), $this);?>
</a></p>
            </div>
        <?php else: ?>
            <form id="registerForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'register'), $this);?>
">
                                <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
                                <?php if (! $this->_tpl_vars['implicitAuth'] || ( $this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL && ! $this->_tpl_vars['isloggedin'] )): ?>
                    <?php if (! $this->_tpl_vars['existingUser']): ?>
                        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register','existingUser' => 1), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

                        <div class="alert alert-info">
                            <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.alreadyRegisteredOtherJournal",'registerUrl' => $this->_tpl_vars['url']), $this);?>
</p>
                        </div>
                    <?php else: ?>
                        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

                        <div class="alert alert-info">
                            <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.notAlreadyRegisteredOtherJournal",'registerUrl' => $this->_tpl_vars['url']), $this);?>
</p>
                        </div>
                        <input type="hidden" name="existingUser" value="1"/>
                        <div class="alert alert-info">
                            <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.loginToRegister"), $this);?>
</p>
                        </div>
                    <?php endif; ?>

                                        <?php if ($this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL): ?>
                        <div class="alert alert-info">
                            <p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'implicitAuthLogin'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.implicitAuth"), $this);?>
</a></p>
                        </div>
                    <?php endif; ?>

                                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>

                                <?php if ($this->_tpl_vars['source']): ?>
                    <input type="hidden" name="source" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['source'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
                <?php endif; ?>

                                <?php if (! $this->_tpl_vars['implicitAuth'] || $this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL): ?>
                    <div class="form-section-note">
                        <p><span class="required-indicator">*</span> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</p>
                    </div>
                <?php endif; ?>

                                <?php if (count ( $this->_tpl_vars['formLocales'] ) > 1 && ! $this->_tpl_vars['existingUser']): ?>
                <div class="form-section">
                    <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage"), $this);?>
</h3>
                    <div class="form-group">
                        <div class="language-selector-container">
                        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'userRegisterUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'userRegisterUrl'));?>

                        <?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'registerForm','url' => $this->_tpl_vars['userRegisterUrl']), $this);?>

                        </div>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</div>
                    </div>
                </div>
                <?php endif; ?>

                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.account"), $this);?>
</h3>

                                <?php if (! $this->_tpl_vars['implicitAuth'] || ( $this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL && ! $this->_tpl_vars['isloggedin'] )): ?>
                                        <div class="form-group">
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo ((is_array($_tmp=$this->_tpl_vars['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="32" required>
                        <label for="username" class="form-control-label">
                            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
<span class="required-indicator">*</span>
                        </label>
                        <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
 is required</div>
                        <div class="success-message">Your <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
 is so cool!</div>
                        <?php if (! $this->_tpl_vars['existingUser']): ?>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.usernameRestriction"), $this);?>
</div>
                        <?php endif; ?>
                    </div>

                                        <div class="form-group">
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['password'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                            <label for="password" class="form-control-label">
                                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.password"), $this);?>
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
                                <span class="strength-label" id="strengthLabel"></span>
                            </div>
                            <div class="error-message">Please enter your password</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.password"), $this);?>
 Looks good!</div>
                        </div>
                        
                                                <div class="password-requirements">
                            <p class="requirements-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.passwordContainLeast"), $this);?>
</p>
                            <div class="requirements-grid">
                                <div class="requirement-item" id="req-length">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordLength",'length' => $this->_tpl_vars['minPasswordLength']), $this);?>
</span>
                                </div>
                                <div class="requirement-item" id="req-number">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordNumber"), $this);?>
</span>
                                </div>
                                <div class="requirement-item" id="req-special">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordSpecial"), $this);?>
</span>
                                </div>
                                <div class="requirement-item" id="req-uppercase">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordUppercase"), $this);?>
</span>
                                </div>
                                <div class="requirement-item" id="req-lowercase">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordLowercase"), $this);?>
</span>
                                </div>
                                <div class="requirement-item" id="req-username">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordDifferUsername"), $this);?>
</span>
                                </div>
                            </div>
                        </div>
                        <?php if (! $this->_tpl_vars['existingUser']): ?>
                        <div class="form-help-text u-js-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.passwordLengthRestriction",'length' => $this->_tpl_vars['minPasswordLength']), $this);?>
</div>
                        <?php endif; ?>
                    </div>

                                        <?php if (! $this->_tpl_vars['existingUser']): ?>
                        <div class="form-group">
                            <div class="password-wrapper">
                                <input type="password" id="password2" name="password2" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['password2'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                                <label for="password2" class="form-control-label">
                                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.repeatPassword"), $this);?>
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

                                                <div class="form-row">
                            <div class="form-group">
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90" required>
                                <label for="email" class="form-control-label">
                                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
<span class="required-indicator">*</span>
                                </label>
                                <div class="error-message">Please enter a valid email address</div>
                                <div class="success-message">Email looks good!</div>
                                <?php if ($this->_tpl_vars['privacyStatement']): ?>
                                <div class="form-help-text">
                                    <a href="#privacyStatement"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
</a>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group u-js-hide">
                                <input type="email" id="confirmEmail" name="confirmEmail" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['confirmEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90">
                                <label for="confirmEmail" class="form-control-label">
                                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.confirmEmail"), $this);?>
<span class="required-indicator">*</span>
                                </label>
                                <div class="error-message">Email addresses do not match</div>
                                <div class="success-message">Email addresses match!</div>
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

                                                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.personal"), $this);?>
</h3>

                                                <div class="form-group">
                            <input type="text" id="salutation" name="salutation" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="40">
                            <label for="salutation" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.salutation"), $this);?>
</label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.salutation.description"), $this);?>
</div>
                            <div class="success-message">Looks good!</div>
                        </div>

                                                <div class="form-group">
                            <input type="text" id="firstName" name="firstName" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="40" required>
                            <label for="firstName" class="form-control-label">
                                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
<span class="required-indicator">*</span>
                            </label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName.description"), $this);?>
</div>
                            <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
 is required</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
 looks good!</div>
                        </div>

                                                <div class="form-group">
                            <input type="text" id="middleName" name="middleName" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="40">
                            <label for="middleName" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName"), $this);?>
</label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName.description"), $this);?>
</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName"), $this);?>
 looks good!</div>
                        </div>

                                                <div class="form-group">
                            <input type="text" id="lastName" name="lastName" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90" required>
                            <label for="lastName" class="form-control-label">
                                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
<span class="required-indicator">*</span>
                            </label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName.description"), $this);?>
</div>
                            <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
 is required</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
 looks good!</div>
                        </div>
                        
                                                <div class="form-group">
                            <input type="text" id="suffix" name="suffix" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['suffix'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90">
                            <label for="suffix" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.suffix"), $this);?>
</label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.suffix.description"), $this);?>
</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.suffix"), $this);?>
 looks good!</div>
                        </div>

                                                <div class="form-row">
                            <div class="form-group">
                                <input type="text" id="initials" name="initials" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['initials'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="5">
                                <label for="initials" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.initials"), $this);?>
</label>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.initialsExample"), $this);?>
</div>
                            </div>
                            
                            <div class="form-group">
                                <select id="gender" name="gender" class="form-control">
                                    <option value="" disabled selected></option>
                                    <?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['genderOptions'],'selected' => $this->_tpl_vars['gender']), $this);?>

                                </select>
                                <label for="gender" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gender"), $this);?>
</label>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gender.description"), $this);?>
</div>
                            </div>
                        </div>

                                                <div class="form-group">
                            <textarea id="affiliation" name="affiliation[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" 
                                     class="form-control form-textarea" rows="3" required><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                            <label for="affiliation" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
<span class="required-indicator">*</span>
                            </label>
                            <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
 is required</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
 looks good!</div>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation.description"), $this);?>
</div>
                        </div>

                                                <div class="form-group">
                            <select id="country" name="country" class="form-control" required>
                                <option value="" disabled selected></option>
                                <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['countries'],'selected' => $this->_tpl_vars['country']), $this);?>

                            </select>
                            <label for="country" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.country"), $this);?>
<span class="required-indicator">*</span>
                            </label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.country.description"), $this);?>
</div>
                            <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.country"), $this);?>
 is required</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.country"), $this);?>
 looks good!</div>
                        </div>
                        
                                                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.identifiers"), $this);?>
</h3>
                        
                                                <div class="form-group">
                            <input type="text" id="orcid" name="orcid" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['orcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                            <label for="orcid" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid"), $this);?>
</label>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid"), $this);?>
 looks good!</div>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid.description"), $this);?>
</div>
                        </div>

                                                <div class="form-group u-js-hide">
                            <input type="url" id="userUrl" name="userUrl" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['userUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                            <label for="userUrl" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.url"), $this);?>
</label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.url.description"), $this);?>
</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.url"), $this);?>
 looks good!</div>
                        </div>

                                                <div class="form-row">
                            <div class="form-group">
                                <input type="text" id="sintaId" name="sintaId" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sintaId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                                <label for="sintaId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.sintaId"), $this);?>
</label>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.sintaId.description"), $this);?>
</div>
                                <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.sintaId"), $this);?>
 looks good!</div>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" id="scopusId" name="scopusId" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['scopusId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                                <label for="scopusId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.scopusId"), $this);?>
</label>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.scopusId.description"), $this);?>
</div>
                                <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.scopusId"), $this);?>
 looks good!</div>
                            </div>
                        </div>

                                                <div class="form-row">
                            <div class="form-group">
                                <input type="text" id="dimensionId" name="dimensionId" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['dimensionId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                                <label for="dimensionId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dimensionId"), $this);?>
</label>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dimensionId.description"), $this);?>
</div>
                                <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dimensionId"), $this);?>
 looks good!</div>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" id="researcherId" name="researcherId" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['researcherId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                                <label for="researcherId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.researcherId"), $this);?>
</label>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.researcherId.description"), $this);?>
</div>
                                <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.researcherId"), $this);?>
 looks good!</div>
                            </div>
                        </div>

                                                <div class="form-group">
                            <input type="url" id="googleScholar" name="googleScholar" class="form-control" 
                                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['googleScholar'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                            <label for="googleScholar" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.googleScholar"), $this);?>
</label>
                            <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.googleScholar.description"), $this);?>
</div>
                            <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.googleScholar"), $this);?>
 looks good!</div>
                        </div>

                                                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.contact"), $this);?>
</h3>

                                                <div class="form-row">
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['phone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                                <label for="phone" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.phones"), $this);?>
</label>
                                <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.phones"), $this);?>
 looks good!</div>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.phone.description"), $this);?>
</div>
                            </div>
                            
                            <div class="form-group">
                                <input type="tel" id="fax" name="fax" class="form-control" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['fax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                                <label for="fax" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.fax"), $this);?>
</label>
                                <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.fax"), $this);?>
 looks good!</div>
                                <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.fax.description"), $this);?>
</div>
                            </div>
                        </div>

                                                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.settings"), $this);?>
</h3>

                                                <div class="checkbox-container" data-checkbox="sendPassword">
                            <div class="checkbox-content">
                                <input type="checkbox" id="sendPassword" name="sendPassword" 
                                       value="1" class="checkbox-input"<?php if ($this->_tpl_vars['sendPassword']): ?><?php else: ?> checked="checked"<?php endif; ?>>
                                <div class="checkbox-indicator"></div>
                                <div class="checkbox-text">
                                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.sendPassword.description"), $this);?>

                                </div>
                            </div>
                        </div>

                                                <?php if (count ( $this->_tpl_vars['availableLocales'] ) > 1): ?>
                        <div class="form-group u-js-hide">
                            <label class="form-section-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.workingLanguages"), $this);?>
</label>
                            <div class="checkbox-group">
                                <?php $_from = $this->_tpl_vars['availableLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey'] => $this->_tpl_vars['localeName']):
?>
                                <div class="checkbox-container" data-checkbox="userLocales-<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                                    <div class="checkbox-content">
                                        <input type="checkbox" name="userLocales[]" 
                                               id="userLocales-<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                               value="<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                               class="checkbox-input"<?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['userLocales'] )): ?> checked="checked"<?php endif; ?>>
                                        <div class="checkbox-indicator"></div>
                                        <div class="checkbox-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['localeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                    </div>
                                </div>
                                <?php endforeach; endif; unset($_from); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                                <?php if (! $this->_tpl_vars['implicitAuth'] || $this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL || ( $this->_tpl_vars['implicitAuth'] === true && $this->_tpl_vars['isloggedin'] )): ?>
                    <?php if ($this->_tpl_vars['allowRegReader'] || $this->_tpl_vars['allowRegReader'] === null || $this->_tpl_vars['allowRegAuthor'] || $this->_tpl_vars['allowRegAuthor'] === null || $this->_tpl_vars['allowRegReviewer'] || $this->_tpl_vars['allowRegReviewer'] === null || ( $this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['enableOpenAccessNotification'] )): ?>
                        <h3 class="form-section-title u-js-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.registerAs"), $this);?>
</h3>
                        
                        <div class="modern-checkbox-group u-js-hide">
                            <?php if ($this->_tpl_vars['allowRegReader'] || $this->_tpl_vars['allowRegReader'] === null): ?>
                            <div class="modern-checkbox-item" data-checkbox="registerAsReader">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="registerAsReader" id="registerAsReader" 
                                           value="1" class="modern-checkbox-input"<?php if ($this->_tpl_vars['registerAsReader']): ?> checked="checked"<?php endif; ?>>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reader"), $this);?>
</div>
                                        <div class="modern-checkbox-description">
                                            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.readerDescription"), $this);?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['enableOpenAccessNotification']): ?>
                            <div class="modern-checkbox-item u-js-hide" data-checkbox="openAccessNotification">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="openAccessNotification" id="openAccessNotification" 
                                           value="1" class="modern-checkbox-input"<?php if ($this->_tpl_vars['openAccessNotification']): ?> checked="checked"<?php endif; ?>>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reader"), $this);?>
</div>
                                        <div class="modern-checkbox-description">
                                            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.openAccessNotificationDescription"), $this);?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($this->_tpl_vars['allowRegAuthor'] || $this->_tpl_vars['allowRegAuthor'] === null): ?>
                            <div class="modern-checkbox-item u-js-hide" data-checkbox="registerAsAuthor">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="registerAsAuthor" id="registerAsAuthor" 
                                           value="1" class="modern-checkbox-input"<?php if ($this->_tpl_vars['registerAsAuthor']): ?> checked="checked"<?php endif; ?>>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.author"), $this);?>
</div>
                                        <div class="modern-checkbox-description">
                                            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.authorDescription"), $this);?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($this->_tpl_vars['allowRegReviewer'] || $this->_tpl_vars['allowRegReviewer'] === null): ?>
                            <div class="modern-checkbox-item" data-checkbox="registerAsReviewer">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="registerAsReviewer" id="registerAsReviewer" 
                                           value="1" class="modern-checkbox-input"<?php if ($this->_tpl_vars['registerAsReviewer']): ?> checked="checked"<?php endif; ?>>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reviewer"), $this);?>
</div>
                                        <div class="modern-checkbox-description">
                                            <?php if ($this->_tpl_vars['existingUser']): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.reviewerDescriptionNoInterests"), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.reviewerDescription"), $this);?>
<?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                                                        <div id="reviewerInterestsContainer" style="margin-top: 15px; display: none;">
                                <div class="form-group">
                                    <input type="text" id="interests" name="interests" class="form-control">
                                    <label for="interests" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.reviewerInterests"), $this);?>
</label>
                                    <div class="form-help-text">Please list your areas of expertise or interests, separated by commas.</div>
                                </div>
                                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "form/interestsInput.tpl", 'smarty_include_vars' => array('FBV_interestsKeywords' => $this->_tpl_vars['interestsKeywords'],'FBV_interestsTextOnly' => $this->_tpl_vars['interestsTextOnly'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                        
                                <div class="privacy-agreement">
                    <div class="checkbox-container" data-checkbox="privacyAgreement">
                        <div class="checkbox-content">
                            <input type="checkbox" id="privacyAgreement" name="privacyAgreement" 
                                   value="1" class="checkbox-input" required>
                            <div class="checkbox-indicator"></div>
                            <div class="checkbox-text">
                                I agree to the <a aria-label="Term and Condition" href="#">Terms and Conditions</a>, <a aria-label="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
" href="#privacyStatement"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
</a>, and <a aria-label="Cookies policy" href="#">Cookies policy</a>.
                            </div>
                        </div>
                    </div>
                </div>
                
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
<script>
document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById(\'registerForm\');
    
    if (form) {
        form.addEventListener(\'submit\', function(e) {
            e.preventDefault(); 
            
            // [WIZDAM FIX] Kunci tombol mati-matian untuk mencegah Duplicate Request
            var submitBtn = form.querySelector(\'[type="submit"]\') || form.querySelector(\'button.defaultButton\');
            if (submitBtn && submitBtn.disabled) {
                return; // Hentikan eksekusi jika tombol sudah lumpuh
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.value = "Memproses..."; // Jika input type
                submitBtn.innerHTML = "Memproses..."; // Jika tag button
            }
            
            var turnstileInput = form.querySelector(\'[name="cf-turnstile-response"]\');
            if (turnstileInput && turnstileInput.value === "") {
                alert("Mohon tunggu hingga verifikasi keamanan Cloudflare selesai.");
                if (submitBtn) { submitBtn.disabled = false; submitBtn.value = "Register"; submitBtn.innerHTML = "Register"; } // Buka kunci
                return;
            }

            var recaptchaSiteKey = \''; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '\';
            
            grecaptcha.ready(function() {
                grecaptcha.execute(recaptchaSiteKey, {action: \'register\'}).then(function(token) {
                    var recaptchaInput = document.getElementById(\'g-recaptcha-response\');
                    if (recaptchaInput) {
                        recaptchaInput.value = token;
                    }
                    
                    // Tembak form secara native
                    HTMLFormElement.prototype.submit.call(form);
                }).catch(function(error) {
                    console.error("reCAPTCHA Error:", error);
                    alert("Terjadi kesalahan pada verifikasi Google reCAPTCHA. Silakan muat ulang halaman.");
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.value = "Register"; submitBtn.innerHTML = "Register"; } // Buka kunci
                });
            });
        });
    }
});
</script>
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
                                
                                <div class="form-actions">
                    <button type="submit" id="registerButton" class="btn-primary">
                        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register"), $this);?>

                    </button>
                    
                    <button type="button" class="btn-secondary" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'index','escape' => false), $this);?>
'">
                        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>

                    </button>
                </div>

                                <?php if (! $this->_tpl_vars['implicitAuth'] || $this->_tpl_vars['implicitAuth'] === @IMPLICIT_AUTH_OPTIONAL): ?>
                    <div class="form-section-note">
                        <p><span class="required-indicator">*</span> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</p>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<div id="privacyStatement" style="display: none;">
    <?php if ($this->_tpl_vars['privacyStatement']): ?>
        <h3 class="u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
</h3>
        <p><?php echo ((is_array($_tmp=$this->_tpl_vars['privacyStatement'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    <?php else: ?>
        <h3 class="u-hide">Privacy Statement</h3>
        <p>Your privacy is important to us. This privacy statement explains the personal data we process, how we process it, and for what purposes.</p>
        <p>We collect and process personal data to provide our services, improve user experience, and comply with legal obligations.</p>
        <p>We do not share your personal information with third parties without your consent, except as required by law.</p>
        <p>You have the right to access, correct, or delete your personal data. Please contact us if you have any questions about our privacy practices.</p>
    <?php endif; ?>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>