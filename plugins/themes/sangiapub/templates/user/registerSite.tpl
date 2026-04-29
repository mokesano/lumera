{**
 * templates/user/registerSite.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site registration.
 *
 *}
{strip}
{include file="common/header-parts/header-register.tpl"}
{/strip}

<div id="journals">
{iterate from=journals item=journal}
	{if !$notFirstJournal}
		<h4 class="form-section-label">{translate key="user.register.selectJournal"}:</h4>
		<ul>
		{assign var=notFirstJournal value=1}
	{/if}
	<li><a href="{url journal=$journal->getPath() page="user" op="register"}">{$journal->getLocalizedTitle()|escape}</a></li>
{/iterate}
{if $journals->wasEmpty()}
	{translate key="user.register.noJournals"}
{else}
	</ul>
{/if}
</div>

{include file="common/footer-parts/footer-welcome.tpl"}
