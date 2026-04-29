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
{if !$pageTitleTranslated}
    {translate|assign:"pageTitleTranslated" key=$pageTitle}
    {* Override untuk key default OJS *}
    {if $pageTitle == "common.openJournalSystems"}
        {assign var="pageTitleTranslated" value="Editorial Management System"}
    {/if}
{/if}
{if $pageCrumbTitle}
	{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
	{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}
<head>
    <title>Account Overview ({$pageTitleTranslated}) | ScholarWizdam</title>
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
	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/user-home.css"  type="text/css" />

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl != "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org" class="u-full-height white">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"></a>
{include file="common/banner.tpl"}

<div class="idp-layout-container">

<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {** include file="common/navmenu.tpl" **}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{** include file="common/breadcrumbs.tpl" **}

<main id="main-account-page" class="u-flex-grow">
    <section class="c-masthead" aria-label="masthead" data-c-masthead="">
        <div class="c-masthead__container">
            <div class="c-masthead__main">
                <div class="c-masthead__breadcrumbs"></div>
                {if $userData}
                    <div class="c-masthead__main-image c-masthead__main-image--profile-image c-masthead__main-image-icon">
                        {if $userData.profileImage && $userData.profileImage.uploadName}
                        <figure class="Avatar Avatar--size-108"><img src="{$sitePublicFilesDir}/{$userData.profileImage.uploadName}" alt="{$userData.firstName|substr:0:1}{$userData.lastName|substr:0:1}" class="Avatar__img is-inside-mask">
                        </figure>
                        {else}
                        <svg class="c-masthead__main-image-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1c6.075 0 11 4.925 11 11s-4.925 11-11 11S1 18.075 1 12 5.925 1 12 1Zm0 16c-1.806 0-3.52.994-4.664 2.698A8.947 8.947 0 0 0 12 21a8.958 8.958 0 0 0 4.664-1.301C15.52 17.994 13.806 17 12 17Zm0-14a9 9 0 0 0-6.25 15.476C7.253 16.304 9.54 15 12 15s4.747 1.304 6.25 3.475A9 9 0 0 0 12 3Zm0 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"></path>
                        </svg>
                        {/if}
                        {if $userData.is_verified}
                        <span class="verified badge icon" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        {else}
                        <span class="unverified badge icon" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        {/if}
                </div>
                <div class="c-masthead__header">
                    <div class="c-masthead__welcome">
                        <span class="user-welcome">{translate key="common.user.welcome"}</span>
                        {if $userData.current_login}
                        <span class="date-item">{$userData.current_login|escape|time_ago} 🚩</span>
                        {/if}
                    </div>
                    <h1 class="c-masthead__heading">{if $userData.salutation}{$userData.salutation|escape} {/if}{if $userData.firstName !== $userData.lastName}{$userData.firstName|escape} {/if}{if $userData.middleName} {$userData.middleName|escape} {/if}{$userData.lastName|escape}{if $userData.suffix}, {$userData.suffix|escape}{/if}</h1>
                    <p class="c-masthead__subheading" data-test-masthead-subheading="">{$userData.email|escape}</p>
                </div>
                {/if}
            </div>
        </div>
    </section>
        
    <div class="account-container u-mt-48" role="main">
        <p class="dated-info">
            {if $userData.registered}
            <span class="date-item registered"><strong>{translate key="common.user.registered"}:</strong> {$userData.registered|escape|date_format:"%d %b %Y"} <span class="text-muted date-item">({$userData.registered|escape|time_ago})</span>
            </span>
            {/if}
            <span class="date-item validated"><strong>{translate key="common.user.validated"}:</strong> 
                {if $userData.validated}
                    {$userData.validated|date_format:"%d %b %Y"}
                    <span class="text-muted date-item">({$userData.validated|time_ago})</span>
                {else}
                    <span class="text-warning unvalidated highlight">{translate key="common.user.unvalidated"}</span>
                {/if}
            </span>
            <span class="date-item last_login"><strong>{translate key="common.user.lastLogin"}:</strong> 
                {if $userData.last_login}
                    {$userData.last_login|date_format:"%d %b %Y"}
                    <span class="text-muted date-item">({$userData.last_login|time_ago})</span>
                {else}
                    <span class="text-muted date-item">{translate key="common.timeAgo.never"}</span>
                {/if}
            </span>
            <span class="date-item current_login"><strong>{translate key="common.user.currentLogin"}:</strong> 
                {if $userData.current_login}
                    {$userData.current_login|date_format:"%d %b %Y"}
                    <span class="text-muted date-item">({$userData.current_login|time_ago})</span>
                {else}
                    {$userData.current_login|time_ago}
                {/if}
            </span>
        </p>
        <h2 class="main-heading u-hide">{$pageTitleTranslated}</h2>
        {if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
        {if $pageSubtitleTranslated}
        	<h3 class="sub-heading">{$pageSubtitleTranslated}</h3>
        {/if}
            
        