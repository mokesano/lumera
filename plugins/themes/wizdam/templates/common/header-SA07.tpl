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
<html class="js" xmlns="http://www.w3.org/1999/xhtml" style="--font-family-sans:Europa; --font-family-serif:Noto Serif;" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
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
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>
{include file="common/breadcrumbs.tpl"}

<div id="content" class="journal-content search-result-page search-page sangia">

<div class="u-container u-search u-mt-32 u-mb-32">
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#searchForm').pkpHandler('$.pkp.pages.search.SearchFormHandler');
	{rdelim});
</script>
    <div class="s-search c-search--background">
        <form action="{url page="search" op="search"}" method="post" role="search" autocomplete="off" data-track="submit" data-track-action="search" data-track-label="form" data-track-category="inline search 150">
            <input type="hidden" value="aps" name="journal">
            <label class="c-search__input-label" for="keywords-1">Search {$currentJournal->getLocalizedTitle()|strip_tags|escape}</label>
            <div class="c-search__field">
            	{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
            	{if empty($queryFilter)}
                <div class="c-search__input-container c-search__input-container--md">
                    <input class="c-search__input" type="text" id="query" name="query" value="{$query|escape}" placeholder="Search" id="keywords-1">
                </div>
            	{else}
            		{$queryFilter}
            	{/if}                
                <div class="c-search__select-container">
                    <label for="subject" class="u-visually-hidden">Subject</label>
                    <select class="c-search__select" data-track="change" data-track-action="search" data-track-label="subject" data-track-category="inline search 150" name="subject" id="subject">All Subjects
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="c-search__button-container">
                    <button type="submit" class="c-search__button" value="{translate key="common.search"}">
                        <span class="c-search__button-text c-search__button-text--hide-at-sm">Search</span>
                        <svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                    </button>
                </div>
            </div>
        </form>
        <form class="u-hide lm-site-search" method="GET" id="search-bar" action="{url page="search" op="search"}">
        	{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
        	{if empty($queryFilter)}
        	<div class="ms-search-field">
        		<input type="text" id="query" name="query" value="{$query|escape}" placeholder="Search" class="lm-search-term" />
        	</div>
        	{else}
        		{$queryFilter}
        	{/if}
        	<button type="submit" value="{translate key="common.search"}" class="uk-button uk-button-primary btn-search"><svg class="lm-icon-search" viewBox="0 0 32 32"><path fill="inherit" d="M31.1 26.9l-8.8-8.8c1.1-1.8 1.7-3.9 1.7-6.1 0-6.6-5.4-12-12-12s-12 5.4-12 12 5.4 12 12 12c2.2 0 4.3-0.6 6.1-1.7l8.8 8.8c0.6 0.6 1.4 0.9 2.1 0.9s1.5-0.3 2.1-0.9c1.2-1.2 1.2-3.1 0-4.2zM3 12c0-5 4-9 9-9s9 4 9 9c0 5-4 9-9 9s-9-4-9-9z"></path></svg></button>
        </form>            
    </div>
</div>

<form id="sub-search" class="area-wrapper sub-search sub-search--has-flavour sub-search--eserem">
<div class="row layout-2">
    <div class="columns medium-3 sidebar">
    
        {if $leftSidebarCode || $rightSidebarCode}
    	<div class="sub-search--srm">
    		{if $leftSidebarCode}
    		<div id="leftSidebar">
				<section class="box">
					<section><h4 class="headline-524909129">Want to publish with <i>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</i>? Submit your Manuscript online.</h4></section>
					<a href="{url page="submission" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
						<span class="button-label-1281676810">Submit paper</span>
						<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
					</a>
				</section>    		    
    		    {$leftSidebarCode}
    		</div>
    		{else}
			<aside class="column medium-3">
				<section class="box">
					<section><h4 class="headline-524909129">Want to publish with us? Submit your Manuscript online.</h4></section>
					<a href="{url page="submission" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
						<span class="button-label-1281676810">Submit paper</span>
						<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
					</a>
				</section>		
			</aside>    		
    		{/if}		
    		{if $rightSidebarCode}
    		<div id="rightSidebar" class="sub-search--srm">
    			{$rightSidebarCode}
    		</div>
    		{/if}
    	</div>
        {/if}

    </div>

<div id="main-contents" class="columns medium-9" role="main" >

