<?php /* Smarty version 2.6.26, created on 2026-04-04 23:58:21
         compiled from notification/maillist.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'notification/maillist.tpl', 20, false),array('function', 'url', 'notification/maillist.tpl', 43, false),array('modifier', 'escape', 'notification/maillist.tpl', 51, false),array('modifier', 'assign', 'notification/maillist.tpl', 141, false),array('modifier', 'nl2br', 'notification/maillist.tpl', 164, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "notification.mailList"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="login-container">
    <div class="auth-card login-card">
        <div class="auth-header u-mb-32">
            <h4 class="form-section-label u-mt-16">Welcome to subscribe!</h4>
            <p class="auth-subtitle u-mb-16">Fill in this form to <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.mailList"), $this);?>
 with this site.</p>
            <div class="alert alert-info">
                <p class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.mailListDescription"), $this);?>
</p>
            </div>
        </div>

        <?php if ($this->_tpl_vars['isError']): ?>
        <div class="alert alert-error">
        	<span class="formError"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
:</span>
        	<ul class="formErrorList">
        	<?php $_from = $this->_tpl_vars['errors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['message']):
?>
        		<li><?php echo $this->_tpl_vars['message']; ?>
</li>
        	<?php endforeach; endif; unset($_from); ?>
        	</ul>
        </div>
        <?php endif; ?>
        
        <?php if ($this->_tpl_vars['success']): ?>
        <div class="alert alert-success">
        	  <p class="formSuccess"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ($this->_tpl_vars['success'])), $this);?>
</p>
        </div>
        <?php endif; ?>

        <form id="notificationSettings" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveSubscribeMailList'), $this);?>
">
            
                        <div class="form-group">
                <input type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?php echo ((is_array($_tmp=$this->_tpl_vars['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                    maxlength="90" 
                    required>
                <label for="email" class="form-control-label">
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
<span class="required-indicator">*</span>
                </label>
                <div class="error-message">Please enter a valid email address</div>
                <div class="success-message">Email looks good!</div>
            </div>
                            
            <div class="form-group">
                <input type="email" 
                    id="confirmEmail" 
                    name="confirmEmail" 
                    class="form-control" 
                    value="<?php echo ((is_array($_tmp=$this->_tpl_vars['confirmEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                    maxlength="90" 
                    required>
                <label for="confirmEmail" class="form-control-label">
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.confirmEmail"), $this);?>
<span class="required-indicator">*</span>
                </label>
                <div class="error-message">Email addresses do not match</div>
                <div class="success-message">Email addresses match!</div>
                <div class="form-help-text">
                    <a href="#privacyStatement"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
</a>
                </div>
            </div>
            
                        <?php if ($this->_tpl_vars['captchaEnabled'] || $this->_tpl_vars['turnstileEnabled'] || $this->_tpl_vars['reCaptchaEnabled']): ?>
            <div class="security-barrier">
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
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                </div>
                <?php endif; ?>
        
                                <?php if ($this->_tpl_vars['reCaptchaEnabled']): ?>
                <div class="reCaptcha-group">
                    <?php if ($this->_tpl_vars['reCaptchaVersion'] == 3): ?>
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"></script>
                        <?php echo '
                        <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById(\'g-recaptcha-response\');if(a){var b=a.closest(\'form\');if(b){b.addEventListener(\'submit\',function(c){c.preventDefault();var d=\''; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '\';grecaptcha.ready(function(){grecaptcha.execute(d,{action:\'register\'}).then(function(e){document.getElementById(\'g-recaptcha-response\').value=e;b.submit()})})})}}});</script>
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
            
            <p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.submit"), $this);?>
" class="button defaultButton" /></p>
        
            <?php if ($this->_tpl_vars['settings']['allowRegReviewer'] || $this->_tpl_vars['settings']['allowRegAuthor'] || $this->_tpl_vars['settings']['subscriptionsEnabled']): ?>
            <h5 class="u-h5 u-mb-24"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.mailList.register"), $this);?>
</h5>
            <ul class="anonim">
            	<?php if ($this->_tpl_vars['settings']['allowRegReviewer']): ?>
            		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

            		<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.mailList.review",'reviewUrl' => $this->_tpl_vars['url']), $this);?>
 </li>
            	<?php endif; ?>
            	<?php if ($this->_tpl_vars['settings']['allowRegAuthor']): ?>
            		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'authors'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

            		<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.mailList.submit",'submitUrl' => $this->_tpl_vars['url']), $this);?>
 </li>
            	<?php endif; ?>
            	<?php if ($this->_tpl_vars['settings']['subscriptionsEnabled']): ?>
            		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

            		<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.mailList.protectedContent",'subscribeUrl' => $this->_tpl_vars['url']), $this);?>

            	<?php endif; ?>
            </ul>
            <?php endif; ?>
            
        </form>
        
    </div>
</div>

<div id="privacyStatement" class="" style="display: none;">
    <?php if ($this->_tpl_vars['privacyStatement']): ?>
        <h3 class="u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.privacyStatement"), $this);?>
</h3>
        <p><?php echo ((is_array($_tmp=$this->_tpl_vars['privacyStatement'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
        <p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'privacyStatement'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</a></p>
    <?php else: ?>
        <h3 class="u-hide">Privacy Statement</h3>
        <p>Your privacy is important to us. This privacy statement explains the personal data we process, how we process it, and for what purposes.</p>
        <p>We collect and process personal data to provide our services, improve user experience, and comply with legal obligations.</p>
        <p>We do not share your personal information with third parties without your consent, except as required by law.</p>
        <p>You have the right to access, correct, or delete your personal data. Please contact us if you have any questions about our privacy practices.</p>
        <p>View more <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'privacyStatement'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</a></p>
    <?php endif; ?>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>