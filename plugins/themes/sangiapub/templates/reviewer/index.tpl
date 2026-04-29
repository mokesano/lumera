{**
 * templates/reviewer/index.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Reviewer index.
 *
 *}
{strip}
{assign var="pageTitle" value="reviewer.dashboard"}
{include file="common/header-parts/header-reviewer.tpl"}
{/strip}

<ul class="menu">
	<li{if ($pageDisplay == "active")} class="current"{/if}><a href="{url path="invitations"}">Invitations</a></li>
	<li{if ($pageDisplay == "completed")} class="current"{/if}><a href="{url path="reviews"}">Reviews</a></li>
	<li{if ($pageDisplay == "certificates")} class="current"{/if}><a href="{url path="certificates"}">Certificates</a></li>
</ul>

{assign var="headerTitle" value="common.queue.long.$pageToDisplay"}
<h3>{$headerTitleTranslated}</h3>

<ul class="menu">
	<li{if ($pageToDisplay == "active")} class="current"{/if}><a href="{url path="active"}">{translate key="common.queue.short.active"}</a></li>
	<li{if ($pageToDisplay == "completed")} class="current"{/if}><a href="{url path="completed"}">{translate key="common.queue.short.completed"}</a></li>
	<li{if ($pageToDisplay == "expired")} class="current"{/if}><a href="{url path="expired"}">Expired</a></li>
</ul>

{include file="reviewer/$pageToDisplay.tpl"}

{include file="common/footer-parts/footer-user.tpl"}

