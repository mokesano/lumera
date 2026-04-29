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
{translate|assign:"applicationName" key="common.openJournalSystems"}
{assign var="applicationName" value="ScholarWizdam"}
{assign var="VersionFork" value="1.0.0.0"}
{/strip}
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{$pageTitleTranslated}{if $currentJournal} - {$currentJournal->getLocalizedTitle()|strip_tags|escape}{/if} | Sangia</title>

	{if !empty($about)}
    	<meta name="description" content="{$about|escape}" />
	{else}
    	<meta name="description" content="{$metaSearchDescription|escape}" />
    {/if}
    {if $intro}
        <meta name="keywords" content="{$intro|nl2br|escape}" />
    {else}
        <meta name="keywords" content="{$metaSearchKeywords|escape}" />
	{/if}
	<meta name="generator" content="{$applicationName} {$VersionFork|escape} - {$currentVersionString|escape}" />

	{$metaCustomHeaders}
	
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{/if}
	
	{include file="common/jqueryScripts.tpl"}
	{include file="common/head.tpl"}

	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/pkp.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" /> 
	
	<script>
    	{literal}
    	$(document).ready(function(){
    	  $(".nav-tabs a").click(function(){
    	    $(this).tab('show');
    	  });
    	});
    	{/literal}
	</script>	

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
	
	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"><!-- Back to top button --></a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{include file="common/breadcrumbs.tpl"}

<div class="journal-content sangia u-mt-48" role="main">

<div class="live-area-wrapper">
	<div class="row">
	{if $leftSidebarCode || $rightSidebarCode} 
	<div class="sidebar">
	    <section class="column u-js-hide"></section>
		<div class="column medium-3">
		    
		    {if !$currentJournal}
    		    {if $leftSidebarCode}
        		    <div class="default-menu-left u-hide">
        		        {$leftSidebarCode}
                    </div>
    		    {/if}
		    {/if}
            
            {if $currentJournal}
			<nav class="journal-subnav">
    			<div class="live">					
    			    <ul class="c-sidemenu c-nav c-nav--stacked c-collapse-at-lt-md">
    			        
    			        <li class="c-sidemenu"><a href="{url page="about" op="editorial-team" anchor=""}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.editorialTeam"}</a></li>
    
                        {if $membershipGroups}
                            {foreach from=$membershipGroups item=peopleGroup}
                            <li class="c-sidemenu"><a href="{url page="about" op="display-membership" path=$peopleGroup.group_id}" data-track="click" data-track-label="link" data-test="explore-nav-item">{$peopleGroup.title|escape}</a></li>
                            {/foreach}
                        {/if}
    			        
    			        {call_hook name="Templates::About::Index::Other"}
    			        
    			        {foreach from=$navMenuItems item=navItem key=navItemKey}{if $navItem.url != '' && $navItem.name != ''}
    			        <li class="c-sidemenu"><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}" data-track="click" data-track-label="link" data-test="explore-nav-item" >{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>{/if}{/foreach}
                        
    			        {if $enableAnnouncements}<li class="c-sidemenu"><a href="{url page="announcement"}" data-track="click" data-track-label="link" data-test="explore-nav-item">News & Announcement</a></li>{/if}{* enableAnnouncements *}
    			        
    			        {if $donationEnabled}<li id="linkJournalContact" class="c-sidemenu"><a href="{url page="donations"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="payment.type.donation"}</a></li>{/if}
    
    			        {if $currentJournal->getSetting('membershipFee')}<li class="c-sidemenu u-hide"><a href="{url page="about" op="memberships"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.memberships"}</a></li>{/if}
    
    			        {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<li class="c-sidemenu"><a href="{url page="about" op="sponsorship"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.journalSponsorship"}</a></li>{/if}
    
    			        {if $currentJournal->getLocalizedSetting('history') != ''}<li class="c-sidemenu"><a href="{url op="history"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.history"}</a></li>{/if}
    			        
    			        <li class="c-sidemenu"><a href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"}</a></li>
    			     </ul>
    			</div>
			</nav>
			{/if}
					    
            {if $rightSidebarCode}
                <div class="default-menu-right">
                    {$rightSidebarCode}
                </div>
            {/if}

		</div>
	</div>
	{/if}

    <div class="column medium-9" role="main">
        <section class="article about">
            <h2 class="main-heading">{$pageTitleTranslated}</h2>
            
            {if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
            {if $pageSubtitleTranslated}
            	<h3 class="sub-heading">{$pageSubtitleTranslated}</h3>
            {/if}
            
            <div id="content" class="article publication content-body body">

