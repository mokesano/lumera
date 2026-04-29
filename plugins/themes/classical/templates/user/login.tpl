{**
 * templates/user/login.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User login form.
 *
 *}
{strip}
{assign var="pageTitle" value="user.login"}
{include file="common/header.tpl"}
{/strip}

{if !$registerOp}
	{assign var="registerOp" value="register"}
{/if}
{if !$registerLocaleKey}
	{assign var="registerLocaleKey" value="user.login.registerNewAccount"}
{/if}

<div class="login-container">
    <div class="auth-card login-card">
        <div class="auth-header u-mb-32">
            <label class="form-section-label">Welcome back!</label>
            <p class="auth-subtitle">Sign in to your account please enter your credentials below{if !$hideRegisterLink}, or <a href="{url page="user" op=$registerOp}">register</a> if you don’t have an account yet{/if}.</p>
        </div>

        {* Hidden source field *}
        {if $source}
            <input type="hidden" name="source" value="{$source|escape}" />
        {/if}
                
        {* Login Message Display *}
        {if $loginMessage}
            <div class="alert alert-info">
                <span class="instruct">{translate key="$loginMessage"}</span>
            </div>
        {/if}

        {* Error Message Display *}
        {if $error}
            <div class="alert alert-error">
                <span class="pkp_form_error">{translate key="$error" reason=$reason}</span>
            </div>
        {/if}

        {* Implicit Auth Section *}
        {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
            <h3 class="form-section-title">{translate key="user.login.implicitAuth"}</h3>
        {/if}

        {if $implicitAuth}
            <div class="alert alert-info">
                <p><strong>{translate key="user.login.implicitAuthLogin"}</strong></p>
                <p>Login through your institution's authentication system.</p>
            </div>
            
            <div class="form-actions">
                <a id="implicitAuthLogin" href="{url page="login" op="implicitAuthLogin"}" class="btn-primary" style="text-decoration: none; display: block; text-align: center;">
                    <svg style="width: 16px; height: 16px; margin-right: 8px; fill: currentColor; vertical-align: middle;" viewBox="0 0 24 24">
                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                    </svg>
                    {translate key="user.login.implicitAuthLogin"}
                </a>
            </div>
        {/if}

        {* Local Auth Section *}
        {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
            <h3 class="form-section-title">{translate key="user.login.localAuth"}</h3>
        {/if}

        {* Main Login Form *}
        {if !$implicitAuth || $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
            <form id="signinForm" method="post" action="{$loginUrl}">
                <input type="hidden" name="source" value="{$source|strip_unsafe_html|escape}" />
                
                {* Username Field *}
                <div class="form-group">
                    <input type="text" 
                           id="loginUsername" 
                           name="username" 
                           class="form-control" 
                           value="{$username|escape}"
                           maxlength="32"
                           required>
                    <label for="loginUsername" class="form-control-label">{translate key="user.username"}</label>
                    <div class="error-message">Please enter your username</div>
                    <div class="success-message">Username is available!</div>
                </div>

                {* Password Field *}
                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" 
                               id="loginPassword" 
                               name="password" 
                               class="form-control" 
                               value="{$password|escape}"
                               required>
                        <label for="loginPassword" class="form-control-label">{translate key="user.password"}</label>
                        <div class="password-toggle" data-target="loginPassword">
                            <svg class="icon-eye" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" style="display: none;">
                                <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
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
                            <span class="strength-label" id="strengthLabel"></span>
                        </div>
                        <div class="error-message">Please enter your password</div>
                        <div class="success-message">Looks good!</div>
                    </div>
                </div>

                {* Turnstile Security Widget *}
                <div class="turnstile-group">
                    <div id="turnstile-container" class="cf-turnstile"
                        data-sitekey="0x4AAAAAAA7b4JHByoY2iX27"
                        data-theme="light"
                        data-size="flexible"
                        data-callback="onTurnstileSuccess">
                    </div>
                    <div id="turnstile-loading">
                        <span class="spinner"></span>
                        Loading security verification...
                    </div>
                    <div id="turnstile-error"></div>
                </div>

                {* Remember Me & Forgot Password - Horizontal Layout *}
                {if $showRemember}
                <div class="form-options-row">
                    <div class="checkbox-container">
                        <div class="checkbox-content">
                            <input type="checkbox" 
                                   id="loginRemember" 
                                   name="remember" 
                                   value="1" class="checkbox-input"{if $remember} checked="checked"{/if}>
                            <span class="checkbox-indicator"></span>
                            <div class="checkbox-text">
                                {translate key="user.login.rememberUsernameAndPassword"}
                            </div>
                        </div>
                    </div>
                    <div class="forgot-password-link">
                        <a href="{url page="login" op="lostPassword"}">
                            {translate key="user.login.forgotPassword"}
                        </a>
                    </div>
                </div>
                {else}
                <div class="form-options-row">
                    <div></div>
                    <div class="forgot-password-link">
                        <a href="{url page="login" op="lostPassword"}">
                            {translate key="user.login.forgotPassword"}
                        </a>
                    </div>
                </div>
                {/if}

                {* Submit Button *}
                <div class="form-actions">
                    <button type="submit" id="loginButton" class="btn-primary">
                        {translate key="user.login"}
                    </button>
                </div>

                {* Register Link *}
                <div class="form-links">
                    <div style="text-align: center;">
                        {if !$hideRegisterLink}
                        <p style="margin-bottom: 0;">Don't have an account yet?
                            <a href="{url page="user" op=$registerOp}">
                                {translate key=$registerLocaleKey}
                            </a>
                        </p>
                        {/if}
                    </div>
                </div>

                {* Auto-focus Script - Sesuai OJS Original *}
                <script type="text/javascript">
                <!--
                    document.getElementById('{if $username}loginPassword{else}loginUsername{/if}').focus();
                // -->
                </script>
            </form>
        {/if}
    </div>
</div>

{* Method 1: Include init.php (recommended) *}
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches)) {
        $initFile = 'plugins/themes/' . $matches[1] . '/php/turnstile/init.php';
        if (file_exists($initFile)) {
            include_once($initFile);
            break;
        }
    }
}
{/php}

{* Sisanya tinggal pakai variables yang sudah di-set oleh init.php *}
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
{literal}
AuthForms.setConfig({
    siteKey: '{$turnstileSiteKey}',
    proxyUrl: '{$baseUrl}/plugins/themes/{$themePath}/php/turnstile/proxy.php',
    theme: '{$turnstileTheme}',
    size: '{$turnstileSize}'
});
{/literal}
</script>

{include file="common/footer.tpl"}
