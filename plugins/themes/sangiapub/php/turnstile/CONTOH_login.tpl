{**
 * File: /plugins/themes/[nama_tema]/templates/user/login.tpl
 *
 * Modern login form yang kompatibel dengan OJS v2.4.8.2
 * Menggunakan struktur dan variabel yang sama dengan OJS asli
 *}

{* Include Turnstile initialization *}
{php}
$themePath = $this->get_template_vars('currentTheme') ? $this->get_template_vars('currentTheme')->getPath() : '';
if ($themePath && file_exists("plugins/themes/$themePath/php/turnstile/init.php")) {
    include_once("plugins/themes/$themePath/php/turnstile/init.php");
}
{/php}

{strip}
{assign var="pageTitle" value="user.login"}
{include file="common/header.tpl"}
{/strip}

{* Include Modern Forms CSS *}
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/{$currentTheme->getPath()}/styles/modern-forms.css">

{* Handle OJS default variables *}
{if !$registerOp}
    {assign var="registerOp" value="register"}
{/if}
{if !$registerLocaleKey}
    {assign var="registerLocaleKey" value="user.login.registerNewAccount"}
{/if}

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">{translate key="user.login"}</h1>
            <p class="auth-subtitle" id="authSubtitle">Welcome back! Please sign in to your account</p>
        </div>

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

        {* Implicit Auth Toggle - Only show if implicit auth is available *}
        {if $implicitAuth}
            <div class="form-group" style="text-align: center; margin-bottom: 24px;">
                <button type="button" id="toggleAuthMode" class="btn-secondary" style="width: auto; padding: 8px 16px; font-size: 13px;">
                    {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
                        Use Institution Login
                    {else}
                        Use Local Login
                    {/if}
                </button>
            </div>
        {/if}

        {* Implicit Auth Optional Header *}
        {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
            <div id="implicitAuthHeader" class="alert alert-info" style="display: none;">
                <h3>{translate key="user.login.implicitAuth"}</h3>
            </div>
        {/if}

        {* Implicit Auth Form *}
        {if $implicitAuth}
            <div id="implicitAuthForm" style="{if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}display: none;{/if}">
                <div class="alert alert-info">
                    <p><strong>{translate key="user.login.implicitAuthLogin"}</strong></p>
                    <p>Login through your institution's authentication system. You will be redirected to your institution's login page.</p>
                </div>
                
                <div class="form-actions">
                    <a id="implicitAuthLogin" href="{url page="login" op="implicitAuthLogin"}" class="btn-primary" style="text-decoration: none; display: block; text-align: center;">
                        <svg style="width: 16px; height: 16px; margin-right: 8px; fill: currentColor; vertical-align: middle;" viewBox="0 0 24 24">
                            <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                        </svg>
                        {translate key="user.login.implicitAuthLogin"}
                    </a>
                </div>
                
                <div class="form-links">
                    <p style="font-size: 13px; color: #718096; margin-top: 16px; text-align: center;">
                        Having trouble? Contact your institution's IT support or use the local login option above.
                    </p>
                </div>
            </div>
        {/if}

        {* Local Auth Optional Header *}
        {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
            <div id="localAuthHeader" class="form-section-title" style="display: none;">
                {translate key="user.login.localAuth"}
            </div>
        {/if}

        {* Main Login Form *}
        {if !$implicitAuth || $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
            <div id="localAuthForm" style="{if $implicitAuth === true}display: none;{/if}">
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
                            <div class="error-message">Please enter your password</div>
                        </div>
                    </div>

                    {* Turnstile Security Widget *}
                    {if $needsTurnstile}
                    <div class="turnstile-group">
                        <label class="turnstile-label">Security Verification</label>
                        <div id="turnstile-container"></div>
                        <div id="turnstile-loading">
                            <span class="spinner"></span>
                            Loading security verification...
                        </div>
                        <div id="turnstile-error"></div>
                    </div>
                    {/if}

                    {* Remember Me Checkbox *}
                    {if $showRemember}
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   id="loginRemember" 
                                   name="remember" 
                                   value="1"{if $remember} checked="checked"{/if}>
                            <span class="checkbox-custom"></span>
                            {translate key="user.login.rememberUsernameAndPassword"}
                        </label>
                    </div>
                    {/if}

                    {* Submit Button *}
                    <div class="form-actions">
                        <button type="submit" id="loginButton" class="btn-primary">
                            {translate key="user.login"}
                        </button>
                    </div>

                    {* Form Links *}
                    <div class="form-links">
                        <ul style="list-style: none; padding: 0; margin: 0; text-align: center;">
                            {if !$hideRegisterLink}
                            <li style="margin-bottom: 8px;">
                                <a href="{url page="user" op=$registerOp}">
                                    {translate key=$registerLocaleKey}
                                </a>
                            </li>
                            {/if}
                            <li>
                                <a href="{url page="login" op="lostPassword"}">
                                    {translate key="user.login.forgotPassword"}
                                </a>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>

            {* Auto-focus Script (from original OJS) *}
            <script type="text/javascript">
            <!--
                // Delay focus to ensure DOM is ready
                setTimeout(function() {
                    var focusElement = document.getElementById('{if $username}loginPassword{else}loginUsername{/if}');
                    if (focusElement && document.getElementById('localAuthForm').style.display !== 'none') {
                        focusElement.focus();
                    }
                }, 100);
            // -->
            </script>
        {/if}
    </div>
</div>

{* Turnstile JavaScript *}
{if $needsTurnstile}
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
// Configure AuthForms before including the main script
AuthForms = AuthForms || {};
AuthForms.setConfig = AuthForms.setConfig || function(config) {
    this.config = Object.assign(this.config || {}, config);
};

AuthForms.setConfig({
    siteKey: '{$turnstileSiteKey}',
    proxyUrl: '{$baseUrl}/plugins/themes/{$currentTheme->getPath()}/php/turnstile/proxy.php',
    theme: '{$turnstileTheme|default:"auto"}',
    size: '{$turnstileSize|default:"normal"}'
});
</script>
{/if}

{* Auth Mode Toggle JavaScript *}
{if $implicitAuth}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleButton = document.getElementById('toggleAuthMode');
    var localForm = document.getElementById('localAuthForm');
    var implicitForm = document.getElementById('implicitAuthForm');
    var authSubtitle = document.getElementById('authSubtitle');
    
    {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
        var implicitAuthHeader = document.getElementById('implicitAuthHeader');
        var localAuthHeader = document.getElementById('localAuthHeader');
        var isImplicitMode = false;
    {else}
        var isImplicitMode = true;
    {/if}

    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            if (!isImplicitMode) {
                // Switch to Implicit Auth
                localForm.style.display = 'none';
                implicitForm.style.display = 'block';
                {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
                implicitAuthHeader.style.display = 'block';
                localAuthHeader.style.display = 'none';
                {/if}
                toggleButton.textContent = 'Use Local Login';
                authSubtitle.textContent = 'Login through your institution';
                isImplicitMode = true;
            } else {
                // Switch to Local Auth
                implicitForm.style.display = 'none';
                localForm.style.display = 'block';
                {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
                implicitAuthHeader.style.display = 'none';
                localAuthHeader.style.display = 'block';
                {/if}
                toggleButton.textContent = 'Use Institution Login';
                authSubtitle.textContent = 'Welcome back! Please sign in to your account';
                isImplicitMode = false;
                
                // Re-focus username field
                setTimeout(function() {
                    var focusElement = document.getElementById('{if $username}loginPassword{else}loginUsername{/if}');
                    if (focusElement) {
                        focusElement.focus();
                    }
                }, 100);
            }
        });
    }
});
</script>
{/if}

{* Include Auth Forms JavaScript *}
<script src="{$baseUrl}/plugins/themes/{$currentTheme->getPath()}/js/auth-forms.js"></script>

{include file="common/footer.tpl"}