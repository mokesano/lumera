{**
 * File: /templates/user/regDisabled.tpl
 *
 * Modern login form yang kompatibel dengan OJS v2.4.8.2
 * Production-ready dengan struktur OJS asli
 *}
{strip}
{assign var="pageTitle" value="user.register"}
{include file="common/header-parts/header-welcome.tpl"}
{/strip}

<div class="errorText">
    <span>{translate key=$errorMsg params=$errorParams}</span>
</div>

{if $backLink}
<div class="actions-button">
    <input type="button" value="{translate key="$backLinkLabel"}" class="button" onclick="document.location.href='{$backLink}'" />
</div>
{/if}

{* --- INI ZONA AMAN (TARUH SCRIPT DI SINI) --- *}
{if $turnstileEnabled}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
{/if}

{include file="common/footer-parts/footer-welcome.tpl"}