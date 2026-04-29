<!DOCTYPE html>
<html lang="{$currentLocale|substr:0:2}">
{**
 * header.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *}
{strip}
{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}
	{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
	{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}
<head>
    <title>{$pageTitleTranslated} | ScholarWizdam</title>
        
    <meta name="description" content="{$metaSearchDescription|escape}" />
    <meta name="keywords" content="{$metaSearchKeywords|escape}" />
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />

	{$metaCustomHeaders}
	
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{/if}
	
	{include file="common/jqueryScripts.tpl"}
	{include file="common/head.tpl"}
	
	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/pkp.css" type="text/css" />
	<!-- <link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />-->
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" /> 

	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}
	<!-- {if $leftSidebarCode || $rightSidebarCode}<link rel="stylesheet" href="{$baseUrl}/styles/sidebar.css" type="text/css" />{/if}
	{if $leftSidebarCode}<link rel="stylesheet" href="{$baseUrl}/styles/leftSidebar.css" type="text/css" />{/if}
	{if $rightSidebarCode}<link rel="stylesheet" href="{$baseUrl}/styles/rightSidebar.css" type="text/css" />{/if}
	{if $leftSidebarCode && $rightSidebarCode}<link rel="stylesheet" href="{$baseUrl}/styles/bothSidebars.css" type="text/css" /> {/if}-->

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
		<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

	<!-- Form validation -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/validate/jquery.validate.js"></script>
	<script type="text/javascript">
		<!--
		// initialise plugins
		{literal}
		$(function(){
			jqueryValidatorI18n("{/literal}{$baseUrl}{literal}", "{/literal}{$currentLocale}{literal}"); // include the appropriate validation localization
			{/literal}{if $validateId}{literal}
				$("form[name={/literal}{$validateId}{literal}]").validate({
					errorClass: "error",
					highlight: function(element, errorClass) {
						$(element).parent().parent().addClass(errorClass);
					},
					unhighlight: function(element, errorClass) {
						$(element).parent().parent().removeClass(errorClass);
					}
				});
			{/literal}{/if}{literal}
			$(document).on('click', ".tagit", function() {
                $(this).find('input').focus();
            });
		});
		// -->
		{/literal}
	</script>

	{if $hasSystemNotifications}
		{url|assign:fetchNotificationUrl page='notification' op='fetchNotification' escape=false}
		<script type="text/javascript">
			$(function(){ldelim}
				$.get('{$fetchNotificationUrl}', null,
					function(data){ldelim}
						var notifications = data.content;
						var i, l;
						if (notifications && notifications.general) {ldelim}
							$.each(notifications.general, function(notificationLevel, notificationList) {ldelim}
								$.each(notificationList, function(notificationId, notification) {ldelim}
									$.pnotify(notification);
								{rdelim});
							{rdelim});
						{rdelim}
				{rdelim}, 'json');
			{rdelim});
		</script>
	{/if}{* hasSystemNotifications *}
	
	{include file="common/commonCSS.tpl"}

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org" class="user-role">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"></a><!-- Back to top button -->

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{include file="common/breadcrumbs.tpl"}

{if $newVersionAvailable}
	<div class="panel-s info-banner">
		<div class="alert alert-container">
			<span class="alert-icon-box u-bg-info-blue"><svg focusable="false" viewBox="0 0 16 128" width="24" height="24" class="icon icon-information u-fill-white"><path d="m2.72 42.24h9.83l0.64 59.06h-11.15l0.63-59.06zm-1.97-19.02c0-3.97 3.25-7.22 7.22-7.22 3.98 0 7.23 3.25 7.23 7.22 0 3.98-3.25 7.23-7.23 7.23-2.42 0-4.57-1.19-5.85-3.02-0.87-1.19-1.37-2.65-1.37-4.21z"></path></svg>
			</span>
			<span class="alert-text">
				<span class="text-s">{translate key="site.upgradeAvailable.manager" currentVersion=$currentVersion latestVersion=$latestVersion siteAdminName=$siteAdmin->getFullName() siteAdminEmail=$siteAdmin->getEmail()}</span>
			</span>
		</div>
	</div>    
{/if}

<div class="journal-content sangia" role="main">

<div class="live-area-wrapper">
	<div class="row">
		<div class="sidebar">
			<section class="column medium-3">
				<div id="managerUsers" class="block pseudoMenu">
					<h3>{translate key="manager.users"}</h3>
					<ul>
						<li><a href="{url op="createUser" source=$managementUrl}">{translate key="manager.people.createUser"}</a></li>
						<li><a href="{url op="people" path="all"}">{translate key="manager.people.allEnrolledUsers"}</a></li>
						<li><a href="{url op="enrollSearch"}">{translate key="manager.people.allSiteUsers"}</a></li>
						<li><a href="{url op="showNoRole"}">{translate key="manager.people.showNoRole"}</a></li>
						{url|assign:"managementUrl" page="manager"}
						<li><a href="{url op="mergeUsers"}">{translate key="manager.people.mergeUsers"}</a></li>
						{call_hook name="Templates::Manager::Index::Users"}
					</ul>
				</div>

				<div id="managerRoles" class="block pseudoMenu">
					<h3>{translate key="manager.roles"}</h3>
					<ul>
						<li><a href="{url op="people" path="managers"}">{translate key="user.role.managers"}</a></li>
						<li><a href="{url op="people" path="editors"}">{translate key="user.role.editors"}</a></li>
						<li><a href="{url op="people" path="sectionEditors"}">{translate key="user.role.sectionEditors"}</a></li>
						{if $roleSettings.useLayoutEditors}
							<li><a href="{url op="people" path="layoutEditors"}">{translate key="user.role.layoutEditors"}</a></li>
						{/if}
						{if $roleSettings.useCopyeditors}
							<li><a href="{url op="people" path="copyeditors"}">{translate key="user.role.copyeditors"}</a></li>
						{/if}
						{if $roleSettings.useProofreaders}
							<li><a href="{url op="people" path="proofreaders"}">{translate key="user.role.proofreaders"}</a></li>
						{/if}
						<li><a href="{url op="people" path="reviewers"}">{translate key="user.role.reviewers"}</a></li>
						<li><a href="{url op="people" path="authors"}">{translate key="user.role.authors"}</a></li>
						<li><a href="{url op="people" path="readers"}">{translate key="user.role.readers"}</a></li>
						<li><a href="{url op="people" path="subscriptionManagers"}">{translate key="user.role.subscriptionManagers"}</a></li>
						{call_hook name="Templates::Manager::Index::Roles"}
					</ul>
				</div>
			</section>
		</div>

<div class="column medium-9" role="main">

<h2 class="main-heading">{$pageTitleTranslated}</h2>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h3 class="sub-heading">{$pageSubtitleTranslated}</h3>
{/if}

<div id="content" class="article editor">

