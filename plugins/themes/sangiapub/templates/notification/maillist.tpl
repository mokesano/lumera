{**
 * templates/notification/maillist.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the notification settings page and unchecks  
 *
 *}
{strip}
{assign var="pageTitle" value="notification.mailList"}
{include file="common/header-parts/header-welcome.tpl"}
{/strip}

<div class="login-container">
    <div class="auth-card login-card">
        <div class="auth-header u-mb-32">
            <h4 class="form-section-label u-mt-16">Welcome to subscribe!</h4>
            <p class="auth-subtitle u-mb-16">Fill in this form to {translate key="notification.mailList"} with this site.</p>
            <div class="alert alert-info">
                <p class="instruct">{translate key="notification.mailListDescription"}</p>
            </div>
        </div>

        {if $isError}
        <div class="alert alert-error">
        	<span class="formError">{translate key="form.errorsOccurred"}:</span>
        	<ul class="formErrorList">
        	{foreach key=field item=message from=$errors}
        		<li>{$message}</li>
        	{/foreach}
        	</ul>
        </div>
        {/if}
        
        {if $success}
        <div class="alert alert-success">
        	  <p class="formSuccess">{translate key="$success"}</p>
        </div>
        {/if}

        <form id="notificationSettings" method="post" action="{url op="saveSubscribeMailList"}">
            
            {* Email Fields *}
            <div class="form-group">
                <input type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="{$email|escape}" 
                    maxlength="90" 
                    required>
                <label for="email" class="form-control-label">
                    {translate key="user.email"}<span class="required-indicator">*</span>
                </label>
                <div class="error-message">Please enter a valid email address</div>
                <div class="success-message">Email looks good!</div>
            </div>
                            
            <div class="form-group">
                <input type="email" 
                    id="confirmEmail" 
                    name="confirmEmail" 
                    class="form-control" 
                    value="{$confirmEmail|escape}" 
                    maxlength="90" 
                    required>
                <label for="confirmEmail" class="form-control-label">
                    {translate key="user.confirmEmail"}<span class="required-indicator">*</span>
                </label>
                <div class="error-message">Email addresses do not match</div>
                <div class="success-message">Email addresses match!</div>
                <div class="form-help-text">
                    <a href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>
                </div>
            </div>
            
            {* [WIZDAM MODULAR SECURITY] *}
            {if $captchaEnabled || $turnstileEnabled || $reCaptchaEnabled}
            <div class="security-barrier">
                {* 0. CAPTCHA Default OJS - NEW Version *}
                {if $captchaEnabled}
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
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                </div>
                {/if}
        
                {* 2. reCAPTCHA dinamis berbasis Handler *}
                {if $reCaptchaEnabled}
                <div class="reCaptcha-group">
                    {if $reCaptchaVersion == 3}
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <script src="https://www.google.com/recaptcha/api.js?render={$reCaptchaPublicKey|escape}"></script>
                        {literal}
                        <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById('g-recaptcha-response');if(a){var b=a.closest('form');if(b){b.addEventListener('submit',function(c){c.preventDefault();var d='{/literal}{$reCaptchaPublicKey|escape}{literal}';grecaptcha.ready(function(){grecaptcha.execute(d,{action:'register'}).then(function(e){document.getElementById('g-recaptcha-response').value=e;b.submit()})})})}}});</script>
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
            
            <p><input type="submit" value="{translate key="form.submit"}" class="button defaultButton" /></p>
        
            {if $settings.allowRegReviewer || $settings.allowRegAuthor || $settings.subscriptionsEnabled}
            <h5 class="u-h5 u-mb-24">{translate key="notification.mailList.register"}</h5>
            <ul class="anonim">
            	{if $settings.allowRegReviewer}
            		{url|assign:"url" page="user" op="register"}
            		<li>{translate key="notification.mailList.review" reviewUrl=$url} </li>
            	{/if}
            	{if $settings.allowRegAuthor}
            		{url|assign:"url" page="information" op="authors"}
            		<li>{translate key="notification.mailList.submit" submitUrl=$url} </li>
            	{/if}
            	{if $settings.subscriptionsEnabled}
            		{url|assign:"url" page="user" op="register"}
            		<li>{translate key="notification.mailList.protectedContent" subscribeUrl=$url}
            	{/if}
            </ul>
            {/if}
            
        </form>
        
    </div>
</div>

{* Privacy Statement Content for Dialog - Hidden by default *}
<div id="privacyStatement" class="" style="display: none;">
    {if $privacyStatement}
        <h3 class="u-hide">{translate key="user.register.privacyStatement"}</h3>
        <p>{$privacyStatement|nl2br}</p>
        <p><a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></p>
    {else}
        <h3 class="u-hide">Privacy Statement</h3>
        <p>Your privacy is important to us. This privacy statement explains the personal data we process, how we process it, and for what purposes.</p>
        <p>We collect and process personal data to provide our services, improve user experience, and comply with legal obligations.</p>
        <p>We do not share your personal information with third parties without your consent, except as required by law.</p>
        <p>You have the right to access, correct, or delete your personal data. Please contact us if you have any questions about our privacy practices.</p>
        <p>View more <a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></p>
    {/if}
</div>

{include file="common/footer-parts/footer-welcome.tpl"}
