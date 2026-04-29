<!DOCTYPE html>
<html lang="{$currentLocale|substr:0:2}" xml:lang="{$currentLocale|substr:0:2}">
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
{if !$pageTitleTranslated}
    {translate|assign:"pageTitleTranslated" key=$pageTitle}
    {* Override untuk key default OJS *}
    {if $pageTitle == "common.openJournalSystems"}
        {assign var="pageTitleTranslated" value="No Current Issue"}
    {/if}
{/if}
{if $pageCrumbTitle}
    {translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
    {assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}
<head>
    <title>{$pageTitleTranslated} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | Sangia Publishing</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
    <meta name="description" content="{$metaSearchDescription|escape}" />
    <meta name="keywords" content="{$metaSearchKeywords|escape}" />
    <meta name="csrf-token" content="{$csrfToken}">
    {$metaCustomHeaders}
    
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{/if}
	
	{include file="common/jqueryScripts.tpl"}
	{include file="common/head.tpl"}
	
	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/pkp.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" />

	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

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

	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/wizdam-mosaic-v1-branded.css" type="text/css" />

	{include file="common/commonCSS.tpl"}
	
	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<!-- Back to top button -->
<a class="buttontop" href="#sangia.org"></a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>

<div id="journal-identity" class="identity u-mb-24 u-show-at-md u-hide-sm-max">
{include file="common/breadcrumbs.tpl"}
</div>

<div class="journal-content sangia s-volume__detail" role="main">
    <div class="live-area-wrapper">
    	<div class="row">
            
<div class="column medium-12 cleared container-type-title" role="main" data-container-type="title">
    <div class="content mb20 mt20 mq1200-padded">
        <h1 class="content main-heading u-mb-16" itemprop="name">{translate key="issue.volume"}
            <span itemprop="volumeNumber">{$issue->getVolume()|escape}</span>
        </h1>
        {if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
        {if $pageSubtitleTranslated}<h2 class="content sub-heading">{$pageSubtitleTranslated}</h2>{/if}
    </div>
</div>

<div class="column medium-12 cleared container-type-issue-grid" data-container-type="issue-grid" data-track-component="issue grid" >

