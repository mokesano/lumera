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
	<title>{$pageTitleTranslated} | Frontedge</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="description" content="{$metaSearchDescription|escape}" />
	<meta name="keywords" content="{$metaSearchKeywords|escape}" />

	{$metaCustomHeaders}
	
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{/if}
	
	{include file="common/jqueryScripts.tpl"}
	{include file="common/head.tpl"}
	
    {* --- INI ZONA AMAN (TARUH SCRIPT DI SINI) --- *}
    {if $turnstileEnabled}
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    {/if}
    
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

	{include file="common/commonCSS.tpl"}

	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/modern-forms.css" type="text/css" />
	
	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl != "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org" class="Overview">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
{** <a class="buttontop" href="#sangia.org"></a> **}

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{include file="common/breadcrumbs.tpl"}

<div class="register-content sangia u-mt-48 u-mb-48">
<div class="live-area-wrapper">
<div class="u-welcome row">

{if $leftSidebarCode || $rightSidebarCode}
<div id="sidebar" class="sidebar">
	{if $leftSidebarCode}
		<div id="leftSidebar" class="column medium-2 slide"></div>
	{else}
		<div id="leftSidebar" class="slide column medium-2"></div>
	{/if}
	
	{if $rightSidebarCode}
	<aside class="column medium-4">
	    
		<section class="sangia-button">
		    <a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-47711979">
		        <span class="button-label-2770091062">Submit your paper</span>
		        <svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1494494357"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path>
		        </svg>
		    </a>
		</section>
		
		<div id="rightSidebar" class="slide" role="complementary">
			<div class="{if $privacyStatement}{else}c-separator {/if}c-separator--top c-separator--heavy" itemscope="" itemtype="http://schema.org/Organization">
				<h3 class="p-section-title">Contact <span itemprop="department">Support Team</span></h3>
				<ul class="c-list-group c-list-group--bordered c-list-group c-list-group--md">
					<li class="c-list-group__item">
						<a href="mailto:journals@sangia.org" itemprop="email" target="_blank">journals@sangia.org</a>
					</li>
					<li class="c-list-group__item">
						<a href="{url page="about" op="contact"}" target="_blank">Get more details</a>
					</li>
				</ul>
			</div>
		</div>
		
	</aside>
	{/if}
</div>
{/if}

<section class="column medium-8 welcome" role="main" tabindex="-1">

<h2 class="u-h3">{$pageTitleTranslated}</h2>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h3>{$pageSubtitleTranslated}</h3>
{/if}

<div id="welcome-content" class="content">

