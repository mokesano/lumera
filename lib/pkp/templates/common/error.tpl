{**
 * error.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Generic error page.
 * Displays a simple error message and (optionally) a return link.
 *
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

<div class="errorText">
    <span>{translate key=$errorMsg params=$errorParams}</span>
</div>

{if $backLink}
<div class="actions-button">
    <input type="button" value="{translate key="$backLinkLabel"}" class="button" onclick="document.location.href='{$backLink}'" />
</div>
{/if}

{include file="common/footer.tpl"}
