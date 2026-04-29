<?php /* Smarty version 2.6.26, created on 2026-04-06 08:15:56
         compiled from user/lostPassword.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'user/lostPassword.tpl', 23, false),array('function', 'url', 'user/lostPassword.tpl', 29, false),array('modifier', 'escape', 'user/lostPassword.tpl', 32, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "user.login.resetPassword"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<?php if (! $this->_tpl_vars['registerLocaleKey']): ?>
	<?php $this->assign('registerLocaleKey', "user.login.registerNewAccount"); ?>
<?php endif; ?>

<div class="login-container">
    <div class="auth-card login-card">
        <div class="auth-header u-mb-32">
            <h4 class="form-section-label u-mt-16">Welcome to reset your password!</h4>
            <p class="auth-subtitle u-mb-16">Fill in this form to <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.resetPassword"), $this);?>
 with this site.</p>
            <div class="alert alert-info">
                <p class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.resetPasswordInstructions"), $this);?>
</p>
            </div>
        </div>

        <form id="reset" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'requestResetPassword'), $this);?>
" method="post">
            
                        <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
            
            <?php if ($this->_tpl_vars['error']): ?>
            	<div class="alert alert-error">
            	    <p><span class="pkp_form_error"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ($this->_tpl_vars['error'])), $this);?>
</span></p>
            	</div>
            <?php endif; ?>
                
            <div class="form-row">
                <div class="form-group">
                    <input type="text" 
                        id="email" 
                        name="email" 
                        value="<?php echo ((is_array($_tmp=$this->_tpl_vars['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                        size="30" 
                        maxlength="90" 
                        class="form-control" 
                        required />
                    <label for="loginUsername" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.registeredEmail"), $this);?>
<span class="required-indicator">*</span>
                    </label>
                    <div class="error-message">Please enter a valid email address</div>
                    <?php if ($this->_tpl_vars['privacyStatement']): ?>
                        <div class="form-help-text">
                            <a href="#privacyStatement"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
                        <?php if ($this->_tpl_vars['captchaEnabled'] || $this->_tpl_vars['turnstileEnabled'] || $this->_tpl_vars['reCaptchaEnabled']): ?>
            <div class="security-barrier">
                                <?php if ($this->_tpl_vars['captchaEnabled'] && ! $this->_tpl_vars['reCaptchaEnabled']): ?>
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
<?php echo '\';grecaptcha.ready(function(){grecaptcha.execute(d,{action:\'reset_password\'}).then(function(e){document.getElementById(\'g-recaptcha-response\').value=e;b.submit()})})})}}});</script>
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
                            
            <p class="sw-entry-point u-mb-24"><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login.resetPassword"), $this);?>
" class="button defaultButton" /></p>
                
                        <?php if (! $this->_tpl_vars['hideRegisterLink']): ?>
            <div class="form-links">
                <div style="text-align: center;">
                    <p style="margin-bottom: 0;">Don't have an account yet? <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => $this->_tpl_vars['registerOp']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['registerLocaleKey']), $this);?>
</a></p>
                </div>
            </div>
            <?php endif; ?>
                
                        <script type="text/javascript">
            <!--
            	document.getElementById('email').focus();
            // -->
            </script>
                
        </form>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>