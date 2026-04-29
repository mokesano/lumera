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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{$pageTitleTranslated}</title>
	<meta name="description" content="{$metaSearchDescription|escape}" />
	<meta name="keywords" content="{$metaSearchKeywords|escape}" />
	<meta name="generator" content="{$applicationName} {$currentVersionString|escape}" />
	{$metaCustomHeaders}
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" /> 

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

	{include file="common/head.tpl"}

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl != "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
</head>

<body id="sangia.org" class="cms cms-sangia">
<a id="skip-to-content" href="#main">Skip to Main Content</a>

<!-- Back to top button -->
<a class="buttontop" href="#sangia.org"></a>

    {include file="common/navbar.tpl"}

<div id="JOUR" class="page-wrapper">
<div id="homepage" class="content">
<div class="layout-full-grid">
    <div class="col-main" role="main">
<div class="cms-banner-full cms-highlight-100" {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}style="background-color: #ffffff;background-image: url({$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape});"{else}style="background-color:#555;"{/if} >
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
                            <div>
                                <p>{if $intro}{$intro|nl2br}{/if}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="columns medium-4 end">
                    <div class="cms-banner-image"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cms-container cms-highlight-0">
<div class="u-row row">
<!-- {include file="common/breadcrumbs.tpl"} -->
<div class="columns small-12 ">
    <div class="cms-columns-row">
        <div class="row">
        <div class="columns cms-tile-row-medium small-12 medium-8">
            <div class="cms-container cms-highlight-0">
                <div class="cms-common cms-article default-table">
                    <p class="taxonomy"></p>
                    <h1>{$pageTitleTranslated}</h1>
                    {if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
                    {if $pageSubtitleTranslated}
                    <h2>{$pageSubtitleTranslated}</h2>
                    {/if}
                    {if $intro}
                    <div class="cms-richtext"><p class="intro--paragraph">{$intro|nl2br}</p>
                    </div>
                    {/if}
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
                                <li><a href="//www.ejournal.stipwunaraha.ac.id/index.php/ISLE/about/contact" target="_blank"><span class="">Contact Us</span><span></span></a></li>
                                <li><a href="#_sangiaJOUR"><span class="">Journals A-Z</span><span></span></a></li>
                                <li class="u-hide"><a href="#_sangiaJOUR"><span class="">Subscribe</span><span></span></a></li>
                                <li><a href="#_sangiaJOUR"><span class="">Why publish with us?</span><span></span></a></li>
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
        

