{**
 * templates/user/lostPassword.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Password reset form.
 *
 *}
{strip}
{assign var="pageTitle" value="user.login.resetPassword"}
{include file="common/header-parts/header-welcome.tpl"}
{/strip}
{if !$registerLocaleKey}
	{assign var="registerLocaleKey" value="user.login.registerNewAccount"}
{/if}

<div class="login-container">
    <div class="auth-card login-card">
        <div class="auth-header u-mb-32">
            <h4 class="form-section-label u-mt-16">Welcome to reset your password!</h4>
            <p class="auth-subtitle u-mb-16">Fill in this form to {translate key="user.login.resetPassword"} with this site.</p>
            <div class="alert alert-info">
                <p class="instruct">{translate key="user.login.resetPasswordInstructions"}</p>
            </div>
        </div>

        <form id="reset" action="{url page="login" op="requestResetPassword"}" method="post">
            
            {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
            <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">
            
            {if $error}
            	<div class="alert alert-error">
            	    <p><span class="pkp_form_error">{translate key="$error"}</span></p>
            	</div>
            {/if}
                
            <div class="form-row">
                <div class="form-group">
                    <input type="text" 
                        id="email" 
                        name="email" 
                        value="{$username|escape}" 
                        size="30" 
                        maxlength="90" 
                        class="form-control" 
                        required />
                    <label for="loginUsername" class="form-control-label">{translate key="user.login.registeredEmail"}<span class="required-indicator">*</span>
                    </label>
                    <div class="error-message">Please enter a valid email address</div>
                    {if $privacyStatement}
                        <div class="form-help-text">
                            <a href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>
                        </div>
                    {/if}
                </div>
            </div>
            
            {* [WIZDAM MODULAR SECURITY] *}
            {if $captchaEnabled || $turnstileEnabled || $reCaptchaEnabled}
            <div class="security-barrier">
                {* 0. CAPTCHA Default OJS - NEW Version *}
                {if $captchaEnabled && !$reCaptchaEnabled}
                <div class="form-group">
                    <label class="form-section-label">{translate key="common.captchaField"}<span class="required-indicator">*</span></label>
                    <img src="{url page="user" op="viewCaptcha" path=$captchaId}" alt="{translate key="common.captchaField.altText"}" width="100%" height="100" />
                    <input name="captcha" id="captcha" value="" size="20" maxlength="32" class="form-control" required />
                    <input type="hidden" name="captchaId" value="{$captchaId|escape:"quoted"}" />
                    <div class="error-message">{translate key="common.captchaField"} is required</div>
                    <div class="form-help-text">{translate key="common.captchaField.description"}</div>
                </div>
                {/if}
                        
                {* 1. Turnstile dinamis berbasis Handler *}
                {if $turnstileEnabled}
                <div class="turnstile-group">
                    <div id="turnstile-container" class="cf-turnstile"
                        data-sitekey="{$turnstilePublicKey|escape}"
                        data-theme="light"
                        data-size="flexible"
                        data-callback="onTurnstileSuccess">
                    </div>
                    <div id="turnstile-loading"><span class="spinner"></span> Loading security verification...</div>
                    <div id="turnstile-error"></div>
                </div>
                {/if}
    
                {* 2. reCAPTCHA Support v3/v2 dinamis berbasis Handler *}
                {if $reCaptchaEnabled}
                <div class="recaptcha-group">
                    {if $reCaptchaVersion == 3}
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <script src="https://www.google.com/recaptcha/api.js?render={$reCaptchaPublicKey|escape}"></script>
                        {literal}
                        <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById('g-recaptcha-response');if(a){var b=a.closest('form');if(b){b.addEventListener('submit',function(c){c.preventDefault();var d='{/literal}{$reCaptchaPublicKey|escape}{literal}';grecaptcha.ready(function(){grecaptcha.execute(d,{action:'reset_password'}).then(function(e){document.getElementById('g-recaptcha-response').value=e;b.submit()})})})}}});</script>
                        {/literal}
                    {elseif $reCaptchaVersion == 2}
                        <div class="g-recaptcha" 
                            data-sitekey="{$reCaptchaPublicKey|escape}" 
                            data-theme="light"
                            data-size="flexible">
                        </div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>        
                    {else}
                        {* Fallback: Legacy reCAPTCHA HTML *}
                        <div class="value u-mb-24">
                            {$reCaptchaHtml}
                        </div>
                    {/if}
                </div>
                {/if}
            </div>
            {/if}
            {* [WIZDAM] END Layer Security: Turnstile + reCAPTCHA v3 + v2 *}
                
            <p class="sw-entry-point u-mb-24"><input type="submit" value="{translate key="user.login.resetPassword"}" class="button defaultButton" /></p>
                
            {* Register Link *}
            {if !$hideRegisterLink}
            <div class="form-links">
                <div style="text-align: center;">
                    <p style="margin-bottom: 0;">Don't have an account yet? <a href="{url page="user" op=$registerOp}">{translate key=$registerLocaleKey}</a></p>
                </div>
            </div>
            {/if}
                
            {* Auto-focus Script - Sesuai OJS Original *}
            <script type="text/javascript">
            <!--
            	document.getElementById('email').focus();
            // -->
            </script>
                
        </form>
    </div>
</div>

{include file="common/footer-parts/footer-welcome.tpl"}
