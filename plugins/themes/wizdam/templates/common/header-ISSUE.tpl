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
{/strip}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="--font-family-sans:Europa; --font-family-serif:Noto Serif;" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
    <title>{$pageTitleTranslated} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | Sangia</title>

	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	
	{if $metaSearchDescription|escape}
	<meta name="description" content="{$metaSearchDescription|escape}" />
	{elseif $journalDescription}
	<meta name="description" content="{$journalDescription|strip_tags|nl2br}" />
	{/if}
	{if $metaSearchKeywords|escape}
	<meta name="keywords" content="{$metaSearchKeywords|escape}" />
	{/if}
	
	{$metaCustomHeaders}
	
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}

	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/pkp.css" type="text/css" />
	<!-- <link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />-->
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" /> 
    
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MWKMKDX');</script>
    <!-- End Google Tag Manager -->

	<!-- Base Jquery -->
	{if $allowCDN}<script type="text/javascript" src="//www.google.com/jsapi"></script>
		<script type="text/javascript">{literal}
			<!--
			// Provide a local fallback if the CDN cannot be reached
			if (typeof google == 'undefined') {
				document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
				document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js' type='text/javascript'%3E%3C/script%3E"));
			} else {
				google.load("jquery", "{/literal}{$smarty.const.CDN_JQUERY_VERSION}{literal}");
				google.load("jqueryui", "{/literal}{$smarty.const.CDN_JQUERY_UI_VERSION}{literal}");
			}
			// -->
		{/literal}</script>
	{else}
		<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>
	{/if}

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
			$(".tagit").live('click', function() {
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

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{include file="common/head.tpl"}

	{$additionalHeadData}

</head>

<body id="sangia.org">
{include file="common/tags.tpl"}
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"></a>

<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/journal-identity.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>

<div class="u-hide journal-page">
    <nav class="journal-navigation">
    	<div class="row">
    		<ul class="column">
    			<li class="journal-navigation-header"><a><b>Navigation</b>
    				<svg width="32" height="32" viewBox="0 0 32 32" class="journal-navigation-arrow-icon-size16"><path fill="inherit" fill-rule="evenodd" d="M28 11.5c0-0.38-0.142-0.76-0.432-1.052-0.58-0.59-1.528-0.598-2.118-0.016l-9.492 10.166-9.404-10.162c-0.588-0.584-1.538-0.58-2.118 0.008-0.588 0.59-0.578 1.54 0.008 2.122l10.104 10.86c0.776 0.772 2.028 0.774 2.808 0.006l10.196-10.862c0.298-0.294 0.448-0.682 0.448-1.070z"></path>
    				</svg></a>
    			</li>
    			<li class="  "><a href="{url page="$currentJournal"}">Overview</a></li>
    			<li class="  "><a href="{url page="about" op="editorialTeam"}" >{translate key="navigation.about"}</a></li>
    			{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
    			<li class="menu-item--current  menu-item--current menu-item--current-parent"><a href="{url page="issue" op="current"}">Issues &amp; Articles</a></li>
    			{/if}
    			<li class="  "><a href="{url page="about" op="submissions"}">Submissions</a></li>
    		</ul>
		</div>
    </nav>
</div>
{include file="common/breadcrumbs.tpl"}
<div class="journal-content sangia" role="main">

<div class="live-area-wrapper">
	<div class="{if $issue}row{else}row raw{/if}">
	{if $leftSidebarCode || $rightSidebarCode}    
	<div class="sidebar">
    	{if $issue}	    
		<aside class="column medium-2">
		    {if $leftSidebarCode}{/if}
			<section class="box">
				<section>
					<noscript>
					{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
						<img loading="lazy" class="journal-cover__image cover-lazy img-default" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue {translate key="issue.coverPage.altText"}"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} style="margin-top: 0" width="100%" />
					{else}
					<img class="journal-cover__image cover-lazy img-default" title="Cover issue default" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" alt="Sangia Media & Publishing" style="margin-top: 0" width="100%" /> {/if}
					</noscript>

					{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
						<img loading="lazy" class="journal-cover__image cover-lazy img-default" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue {translate key="issue.coverPage.altText"}"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} style="margin-top: 0" width="100%" />
					{else}
					<img class="journal-cover__image cover-lazy img-default" title="Cover issue default" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" alt="Sangia Media & Publishing" style="margin-top: 0" width="100%" />
					{/if}
					{call_hook name="Templates::Article::Article::ArticleCoverImage"}
				</section>
			</section>
			
		</aside>
		
		<div class="columns medium-3">
			<nav class="journal-subnav">
    			<div class="live">	
    			    <ul >
    			        <li class="menu-item--current"><a href="{url page="issue" op="current"}" title="Browse Latest Issue">Latest Issue</a></li>
    			        <li {if $setupStep == 1} class="menu-item--current"{/if}><a href="{url page="issue" op="archive"}" title="Browse Volume & Issue">All Volume & Issue</a></li>
    			     </ul>
    			</div>
			</nav>
			
			<section class="u-hide box u-mt-32">
				<section><h4 class="headline-524909129">Want to publish with us? Submit your Manuscript online.</h4></section>
				<a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
					<span class="button-label-1281676810">Submit paper</span>
					<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
				</a>
			</section>
			
			<aside class="adsbox c-ad c-ad--160x600 u-mt-32 u-mb-32 null" data-component-mpu="">
                <div class="c-ad__inner">
                    <p class="c-ad__label">Advertisement</p>
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Banners -->
                    <ins class="adsbygoogle c-ad--160x600"
                         style="display:block"
                         data-ad-client="ca-pub-8416265824412721"
                         data-ad-slot="7137622495"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>	
                </div>
            </aside>
            
            {if $rightSidebarCode}{$rightSidebarCode}{/if}
            
		</div>
		{/if}	
	</div>
	{/if}

<div class="column {if $issue}medium-7{else} medium-12{/if}">

{if $issue}	
	<section class="article">
		<h2 class="kicker headline-656086398">{translate key="issue.vol"} {$issue->getVolume()|escape} {translate key="issue.no"} {$issue->getNumber()|escape} ({$issue->getDatePublished()|date_format:"%Y"})</h2>
	<section class="article-body">
		{if !$showToc}
		{if $issueId}
			{url|assign:"currentUrl" page="issue" op="view" path=$issueId|to_array:"showToc"}
		{else}
			{url|assign:"currentUrl" page="issue" op="current" path="showToc"}
		{/if}
			<h2 class="headline-4241089976"><a href="{$currentUrl}">{translate key="issue.volume"} {$issue->getVolume()|escape} {translate key="issue.issue"} {$issue->getNumber()|escape}, {$issue->getDatePublished()|date_format:"%B %Y"}</a></h2>
		{else}
		<h2 class="headline-4241089976">{translate key="issue.volume"} {$issue->getVolume()|escape} {translate key="issue.issue"} {$issue->getNumber()|escape}, {$issue->getDatePublished()|date_format:"%B %Y"}</h2>
		{/if}

		<h3 class="issue-title">{if $issue->getLocalizedTitle($currentJournal)}{$issue->getLocalizedTitle($currentJournal)|escape}{/if}</h3>
		
	<div id="{$issue->getId()|string_format:"%07d"}" class="issue-content">
	    <section class="issue-description u-font-sans" itemprop="description">

{else}

	<h2 class="content main-heading">{$pageTitleTranslated}</h2>

	{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
	<h3 class="sub-heading">{if $pageSubtitleTranslated}{$pageSubtitleTranslated}{/if}</h3>

	</div>
	<div class="column medium-12" >

{/if}


