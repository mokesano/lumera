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
    <title>{$pageTitleTranslated}{if $currentJournal} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | Sangia{else} | {$siteTitle}{/if}</title>
        
    <meta name="description" content="{$metaSearchDescription|escape}" />
    <meta name="keywords" content="{$metaSearchKeywords|escape}" />
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />

	{$metaCustomHeaders}
	
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{/if}
	
	{include file="common/jqueryScripts.tpl"}
	{include file="common/head.tpl"}
	
	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js" referrerpolicy="strict-origin-when-cross-origin"></script>

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
<a class="buttontop" href="#sangia.org"></a><!-- Back to top button -->

{include file="common/banner.tpl"}

<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/journal-identity.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
    {include file="common/breadcrumbs.tpl"}

<div class="journal-content sangia u-mt-48" role="main">

<div class="journal-content live-area-wrapper">
	<div class="row">
	
	{strip}
	<div class="sidebar">
		<div class="column medium-2">
		    
		    {if $leftSidebarCode || $rightSidebarCode}
                {if !$currentJournal}
    		    <div class="default-menu">
    		        {$leftSidebarCode}
    		        {$rightSidebarCode}
    		    </div>
    		    {/if}
    		{/if}
		    
		    {if $currentJournal}
			<nav class="journal-subnav">
			<div class="live">					
			    <ul class="c-sidemenu c-nav c-nav--stacked c-collapse-at-lt-md">
			        <li class="c-sidemenu"><a href="{url page="about" op="editorial-team"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.editorialTeam"}</a></li>

                    {if $membershipGroups}
                        {foreach from=$membershipGroups item=peopleGroup}
                        <li class="c-sidemenu"><a href="{url page="about" op="display-membership" path=$peopleGroup.group_id}" data-track="click" data-track-label="link" data-test="explore-nav-item">{$peopleGroup.title|escape}</a></li>
                        {/foreach}
                    {/if}
			        
			        {call_hook name="Templates::About::Index::Other"}
			        
			        {foreach from=$navMenuItems item=navItem key=navItemKey}{if $navItem.url != '' && $navItem.name != ''}
			        <li class="c-sidemenu"><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}"  data-track="click" data-track-label="link" data-test="explore-nav-item">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>{/if}{/foreach}
                    
			        {if $enableAnnouncements}<li class="c-sidemenu"><a href="{url page="announcement"}">News & Announcement</a></li>{/if}{* enableAnnouncements *}
			        
			        {if $donationEnabled}<li id="linkJournalDonations" class="c-sidemenu"><a href="{url page="donations"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="payment.type.donation"}</a></li>{/if}

			        {if $currentJournal->getSetting('membershipFee')}<li class="c-sidemenu u-hide"><a href="{url page="about" op="memberships"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.memberships"}</a></li>{/if}

			        {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<li class="c-sidemenu"><a href="{url page="about" op="sponsorship"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.journalSponsorship"}</a></li>{/if}

			        {if $currentJournal->getLocalizedSetting('history') != ''}<li class="c-sidemenu"><a href="{url page="about" op="history"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.history"}</a></li>{/if}
			        
			        <li class="c-sidemenu"><a href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"}</a></li>
			     </ul>
			</div>    
			</nav>
			{/if}
		</div>
		
		<section class="column medium-3 section" role="aside">
			<section class="box">
				<section><h4 class="headline-524909129">Want to publish with us? Submit your Manuscript online.</h4></section>
				<a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
					<span class="button-label-1281676810">Submit paper</span>
					<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
				</a>
			</section>
			
			{strip}
			<section class="editorial-board-by-country">
			    <div class="divider"></div>
			    <h3 class="editorial-board-by-country-title u-h4">Editorial board by country/region </h3>
			    <div id="country-map" class="country-map"></div>
			    <div class="editors-by-country-text u-margin-s-ver text-s">0 editors and editorial board members in 0 countries/regions</div>
			    <ol class="editors-by-country-ordered-list text-s">
			        <li class="country-list-item">Negara (0)</li>
			    </ol>
			</section>
			<section class="gender-indicator-metrics-section">
			    <div class="divider"></div>
			    <h3 class="gender-indicator-title u-h4">Gender diversity of editors and editorial board members</h3>
			    <div class="chart-area">
			        <div class="pie-chart"><canvas id="genderChart"></canvas></div>
			        <ul class="legend">
			            <li class="legend-item u-margin-xs-bottom" style="--bullet-color: #FF6A19;"><div><span class="legend-percentage">0%</span><span class="atribut">man</span></div></li>
			            <li class="legend-item u-margin-xs-bottom" style="--bullet-color: #3F89FF;"><div><span class="legend-percentage">0%</span><span class="atribut">woman</span></div></li>
			            <li class="legend-item u-margin-xs-bottom" style="--bullet-color: #56BF70;"><div><span class="legend-percentage">0%</span><span class="atribut">non-binary or gender diverse</span></div></li>
			            <li class="legend-item u-margin-xs-bottom" style="--bullet-color: #4D4D4D;"><div><span class="legend-percentage">0%</span><span class="atribut">prefer not to disclose</span></div></li>
			        </ul>
			    </div>
			    <p class="u-padding-s-top text-s u-padding-s-right">Data represents responses from 100.00% of 0 editors and editorial board members</p>
			    <div class="divider"></div>
			</section>
			{/strip}
			
            {if $leftSidebarCode || $rightSidebarCode}     
                {if $currentJournal}
                <div class="default-sidemenu">
                    {if $rightSidebarCode}
                        {strip}
                        {$rightSidebarCode}
                        {/strip}
                    {/if}
                </div>
                {/if}
            {/if}
		</section>
	</div>
	{/strip}

<div class="column medium-7" role="main">
<section class="article about">
<h2 class="main-heading">{$pageTitleTranslated}</h2>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h3 class="sub-heading">{$pageSubtitleTranslated}</h3>
{/if}

<section id="content" class="publication content-body">

