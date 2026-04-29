{**
 * templates/manager/index.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal management index.
 *
 *}
{strip}
{assign var="pageTitle" value="manager.journalManagement"}
{include file="common/header-parts/header-manager.tpl"}
{/strip}

{if $newVersionAvailable}
<div class="warningMessage">
	<span class="alert-icon-box u-bg-info-blue"><svg focusable="false" viewBox="0 0 16 128" width="24" height="24" class="icon icon-information u-fill-white"><path d="m2.72 42.24h9.83l0.64 59.06h-11.15l0.63-59.06zm-1.97-19.02c0-3.97 3.25-7.22 7.22-7.22 3.98 0 7.23 3.25 7.23 7.22 0 3.98-3.25 7.23-7.23 7.23-2.42 0-4.57-1.19-5.85-3.02-0.87-1.19-1.37-2.65-1.37-4.21z"></path></svg></span>
	<span class="message">{translate key="site.upgradeAvailable.manager" currentVersion=$currentVersion latestVersion=$latestVersion siteAdminName=$siteAdmin->getFullName() siteAdminEmail=$siteAdmin->getEmail()}</span>
</div>
{/if}

<div id="managementPages" class="block pseudoMenu">
<h3>{translate key="manager.managementPages"}</h3>

<ul>
	{if $announcementsEnabled}
		<li><a href="{url op="announcements"}">{translate key="manager.announcements"}</a></li>
	{/if}
	<li><a href="{url op="files"}">{translate key="manager.filesBrowser"}</a></li>
	<li><a href="{url op="importexport"}">{translate key="manager.importExport"}</a></li>
	<li><a href="{url op="sections"}">{translate key="section.sections"}</a></li>
	<li><a href="{url op="languages"}">{translate key="common.languages"}</a></li>
	<li><a href="{url op="groups"}">{translate key="manager.groups"}</a></li>
	<li><a href="{url op="payments"}">{translate key="manager.payments"}</a></li>
	<li><a href="{url op="emails"}">{translate key="manager.emails"}</a></li>
	<li><a href="{url page="rtadmin"}">{translate key="manager.readingTools"}</a></li>
	<li><a href="{url op="reviewForms"}">{translate key="manager.reviewForms"}</a></li>
	<li><a href="{url op="setup"}">{translate key="manager.setup"}</a></li>
	<li><a href="{url op="statistics"}">{translate key="manager.statistics"}</a></li>
	<li><a href="{url op="plugins"}">{translate key="manager.plugins"}</a></li>
	
	{if $publishingMode == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
		<li><a href="{url op="subscriptionsSummary"}">{translate key="manager.subscriptions"}</a></li>
	{/if}
	{call_hook name="Templates::Manager::Index::ManagementPages"}
</ul>
</div>


{include file="common/footer-parts/footer-user.tpl"}
