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
    <title>{$pageTitleTranslated} | ScholarWizdam Editorial System</title>
        
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
	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/user-home.css" type="text/css" />

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl != "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org" class="user-role">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"><!-- Back to top button --></a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{include file="common/breadcrumbs.tpl"}

<div class="journal-content sangia user admin u-mt-48" role="main">

<div class="live-area-wrapper">
	<div class="row">
	<div class="sidebar">
			<section class="column medium-3">
				<div id="myAccount" class="block pseudoMenu">
					<h3>{translate key="user.myAccount"}</h3>
					<ul>
					    {if $userSession}
					    <li><a href="{url page="user" op="my-profile" path=$userSession->getUserId()|string_format:"%011d"}">{translate key="user.showMyProfile"}</a></li>
						{/if}
						<li><a href="{url page="user" op="update-profile"}">{translate key="user.editMyProfile"}</a></li>
						{if $hasOtherJournals}
							{if !$showAllJournals}
							<li><a href="{url journal="index" page="user"}">{translate key="user.showAllJournals"}</a></li>
							{/if}
						{/if}
						{if $currentJournal}
							{if $subscriptionsEnabled}
							<li><a href="{url page="user" op="subscriptions"}">{translate key="user.manageMySubscriptions"}</a></li>
							{/if}
						{/if}
						{if $currentJournal}
							{if $acceptGiftPayments}
							<li><a href="{url page="user" op="gifts"}">{translate key="gifts.manageMyGifts"}</a></li>
							{/if}
						{/if}
						{if !$implicitAuth}
						<li><a href="{url page="user" op="changePassword"}">{translate key="user.changeMyPassword"}</a></li>
						{/if}

						{if $currentJournal}
							{if $journalPaymentsEnabled && $membershipEnabled}
								{if $dateEndMembership}
								<li><a href="{url page="user" op="payMembership"}">{translate key="payment.membership.renewMembership"}</a> ({translate key="payment.membership.ends"}: {$dateEndMembership|date_format:$dateFormatShort})</li>
								{else}
								<li><a href="{url page="user" op="payMembership"}">{translate key="payment.membership.buyMembership"}</a></li>
								{/if}
							{/if}{* $journalPaymentsEnabled && $membershipEnabled *}
						{/if}{* $userJournal *}

						<li><a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
						{call_hook name="Templates::User::Index::MyAccount"}
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

<div id="content" class="sangia-user sangia-admin sangia-content article">

