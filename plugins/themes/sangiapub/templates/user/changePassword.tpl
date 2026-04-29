{**
 * templates/user/changePassword.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to change a user's password.
 *
 *}
{strip}
{assign var="pageTitle" value="user.changePassword"}
{url|assign:"currentUrl" page="user" op="changePassword"}
{include file="common/header-parts/header-user.tpl"}
{/strip}

<div id="changePassword" class="pass-container">
    <div class="user-card password-card login-card">
        <div class="auth-header u-mb-24">
            <p class="auth-subtitle">Fill in this form to changes your password.</p>
        </div>
        
        <form method="post" action="{url op="savePassword"}">
    
        {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
        <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">
            
        {include file="common/formErrors.tpl"}

            <div class="alert alert-info">
                <p>{translate key="user.profile.changePasswordInstructions"}</p>
            </div>

            <h3 class="form-section-title">{translate key="user.profile.oldPassword"}</h3>
            
            {* Old Password Field *}
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="oldPassword" name="oldPassword" class="form-control" 
                        value="{$oldPassword|escape}" required>
                    <label for="loginPassword" class="form-control-label">{fieldLabel name="oldPassword" key="user.profile.oldPassword"}<span class="required-indicator">*</span>
                    </label>
                    <div class="password-toggle" data-target="oldPassword">
                        <svg class="icon-eye-off" viewBox="0 0 24 24">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        </svg>
                        <svg class="icon-eye" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    
                    {* Password Strength Indicator *}
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

            <h3 class="form-section-title">{translate key="user.profile.newPassword"}</h3>
            
            {* New Password *}
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" 
                           value="{$password|escape}" required>
                    <label for="password" class="form-control-label">
                        {fieldLabel name="password" key="user.profile.newPassword"}<span class="required-indicator">*</span>
                    </label>
                    <div class="password-toggle" data-target="password">
                        <svg class="icon-eye-off" viewBox="0 0 24 24">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        </svg>
                        <svg class="icon-eye" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    {* Password Strength Indicator *}
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
                    <div class="error-message">Please enter {fieldLabel name="password" key="user.profile.newPassword"}</div>
                    <div class="success-message">{fieldLabel name="password" key="user.profile.newPassword"} looks good!</div>
                </div>
                        
                {* Password Requirements *}
                <div class="password-requirements">
                    <p class="requirements-title">Password must contain at least:</p>
                    <div class="requirements-grid">
                        <div class="requirement-item" id="req-length">
                            <span class="requirement-icon">✗</span>
                            <span class="requirement-text">{$minPasswordLength} characters</span>
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
                <div class="form-help-text u-js-hide">{translate key="user.register.passwordLengthRestriction" length=$minPasswordLength}</div>
            </div>

            <h3 class="form-section-title">{translate key="user.profile.repeatNewPassword"}</h3>
            
            {* Confirm Password *}
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password2" name="password2" class="form-control" 
                           value="{$password2|escape}" required>
                    <label for="password2" class="form-control-label">
                        {fieldLabel name="password2" key="user.profile.repeatNewPassword"}<span class="required-indicator">*</span>
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
            
            {* CAPTCHA Default NEW Version design *}
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

            {* [WIZDAM] Layer Security: Turnstile + reCAPTCHA v3 + v2 *}
            {if $turnstileEnabled || $reCaptchaEnabled}
            <div class="security-barrier">
                {* CLOUDFLARE TURNSTILE *}
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
                    
                {* reCAPTCHA Support v3/v2 *}
                {if $reCaptchaEnabled}
                <div class="recaptcha-group">
                    {if $reCaptchaVersion == 3}
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <script src="https://www.google.com/recaptcha/api.js?render={$reCaptchaPublicKey|escape}"></script>
                        {literal}
                        <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById('g-recaptcha-response');if(a){var b=a.closest('form');if(b){b.addEventListener('submit',function(c){c.preventDefault();var d='{/literal}{$reCaptchaPublicKey|escape}{literal}';grecaptcha.ready(function(){grecaptcha.execute(d,{action:'change_password'}).then(function(e){document.getElementById('g-recaptcha-response').value=e;b.submit()})})})}}});</script>
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
                    
                {* Teks Bantuan Dinamis *}
                {if $turnstileEnabled || $reCaptchaEnabled}
                    <div class="form-help-text u-hide">
                        ScholarWizdam register system protected by 
                        {if $turnstileEnabled && $reCaptchaEnabled}Cloudflare Turnstile & Google reCAPTCHA
                        {elseif $turnstileEnabled}Cloudflare Turnstile
                        {else}Google reCAPTCHA{/if}.
                    </div>
                {/if}
            </div>
            {/if}
            {* [WIZDAM] END Layer Security: Turnstile + reCAPTCHA v3 + v2 *}
        
            <p class="action-button">
                <input type="submit" value="{translate key="common.save"}" class="button" />
                <input type="button" value="{translate key="common.cancel"}" class="defaultButton" onclick="document.location.href='{url page="user" escape=false}'" />
            </p>
        
        </form>
    </div>
</div>

{include file="common/footer-parts/footer-welcome.tpl"}
