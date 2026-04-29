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
{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}
	{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
	{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{translate|assign:"applicationName" key="common.scholarWizdam"}
{assign var="applicationName" value="Frontedge"}
{assign var="VersionFork" value="1.0.0.0"}
{/strip}
<head>
	<title>{$pageTitleTranslated} | Frontedge</title>
	
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
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="generator" content="{$applicationName} {$currentVersionString|escape} fork {$VersionFork|escape}" />
	
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
	
	<style>
	    {literal}
	    .c-header {
	        margin-bottom: 0;
	    }
	    .c-header__container {
	        max-width: 1300px;
	    }
	    {/literal}
	</style>

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl != "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
	
</head>

<body id="sangia.org" class="cms cms-sangia">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"><!-- Back to top button --></a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>

<div id="JOUR" class="page-wrapper">
<div id="homepage" class="content">
<div class="layout-full-grid">
    <div class="col-main" role="main">
<div class="cms-banner-full cms-highlight-100" {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}style="background-color: #ffffff; background-image: url('{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}');"{else}style="background-color:#555;"{/if}>
    <div class="u-row row">
        <div class="cms-tile-row columns small-12 ">
            <div class="row">
                <div class="columns small-12 medium-7">
                    <div class="cms-banner-text">
                        <div class="cms-banner-text-inner">
                            <h1>
                                {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
                                {if $displayPageHeaderTitleAltText != ''}{$displayPageHeaderTitleAltText|escape}{else}{translate key="common.pageHeader.altText"}{/if}
                                {elseif $displayPageHeaderTitle}
                                	{$displayPageHeaderTitle}
                                {elseif $alternatePageHeader}
                                	{$alternatePageHeader}
                                {elseif $siteTitle}
                                	{$siteTitle}
                                {else}
                                	{$applicationName}
                                {/if}
                            </h1>
                            {if $intro}
                            <div>
                                <p>{$intro|nl2br}</p>
                            </div>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="columns medium-4 end">
                    <div class="cms-banner-image"></div>
                </div>
            </div>
        </div>
    </div>
    <!---- Publisher Statistics ---->
    <div class="MainHeader__actions">
        <div class="MainHeader__actionsWrapper">
            <ul class="Metrics">
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!---->
                                <span class="Metrics__numbers__number">{$allTotalAuthors|metric_number}</span>
                                <span class="Metrics__numbers__suffix">{$allTotalAuthors|metric_suffix}</span>
                            </p>
                            <p class="Metrics__numbers__text">{translate key="common.metrics.researchers"}</p>
                        </div>
                    </div>
                </li>
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!-- For Citation {translate key="common.metrics.citations"} -->
                                <span class="Metrics__numbers__number">{$allTotalViews|metric_number}</span>
                                <span class="Metrics__numbers__suffix">{$allTotalViews|metric_suffix}</span>
                            </p>
                            <p class="Metrics__numbers__text">{translate key="common.metrics.views"}</p>
                        </div>
                    </div>
                </li>
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!-- -->
                                <span class="Metrics__numbers__number">{$allTotalDownloads|metric_number}</span>
                                <span class="Metrics__numbers__suffix">{$allTotalDownloads|metric_suffix}</span>
                            </p>
                            <p class="Metrics__numbers__text">{translate key="common.metrics.downloads"}</p>
                        </div>
                    </div>
                </li>
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!---->
                                <span class="Metrics__numbers__number">{$allTotalInteractions|metric_number}</span>
                                <span class="Metrics__numbers__suffix">{$allTotalInteractions|metric_suffix}</span>
                            </p>
                            <p class="Metrics__numbers__text">{translate key="common.metrics.viewsDownload"}</p>
                        </div>
                    </div>
                </li>
            </ul><!---->
        </div>
    </div>
</div>

<div class="cms-container cms-highlight-0">
<div class="u-row row">
{** {include file="common/breadcrumbs.tpl"} **}
<div class="columns small-12 ">
    <div class="cms-columns-row">
        <div class="row">
        <div class="columns cms-tile-row-medium small-12 medium-8">
            <div class="cms-container cms-highlight-0">
                <div class="cms-common cms-article default-table">
                    <p class="taxonomy"></p>
                    <h1 class="u-hide u-js-hide">{$pageTitleTranslated}</h1>
                    {if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
                    {if $pageSubtitleTranslated}
                    <h2>{$pageSubtitleTranslated}</h2>
                    {/if}
                    <div class="cms-richtext">
                    {if !empty($about)}
                        <p class="intro--paragraph">{$about|nl2br}</p>
                    {elseif $intro}
                        <p class="intro--paragraph">{$intro|nl2br}</p>
                    {/if}
                    </div>
                </div>
            </div>
        </div>
        <div class="columns cms-tile-row-medium small-12 medium-4">
            <div id="id8" class="cms-container cms-highlight-0">
                <div class="cms-multicolumn-links">
                    <h2 id="c10065960">More information</h2>
                    <div class="row">
                        <div class="columns small-12 medium-12">
                            <ul>
                                {if $sitePrincipalContactEmail}
                                <li><a href="{url journal="index" page="about" op="contact"}" target="_self"><span class="link">Contact Us</span><span></span></a></li>
                                {/if}
                                <li><a href="#c15046410"><span class="link">Journals A-Z</span><span></span></a></li>
                                <li class="u-hide"><a href="javascript(0)"><span class="link">Subscribe</span><span></span></a></li>
                                <li><a href="javascript(0)"><span class="link">Why publish with us?</span><span></span></a></li>
                            </ul>
                        </div>
                    </div>
                            
                {if $leftSidebarCode || $rightSidebarCode}
                	<div class="columns  small-12 medium-4 u-hide" style="float: right;">
                		{if $leftSidebarCode}
                			<div class="slide" role="complementary">
                				{include file="common/submit.tpl"}
                				{$leftSidebarCode}
                			</div>
                		{/if}
                		{if $rightSidebarCode}
                			<div class="slide" role="complementary">
                				{include file="common/submit.tpl"}
                				{$rightSidebarCode}
                			</div>
                		{/if}
                	</div>
                {/if}                                    
                </div>
            </div>
        </div>        
        </div>
    </div>
</div>
</div>
</div>

<div class="cms-container cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12">
        