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
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
    <head>
        <title>{$currentJournal->getLocalizedTitle()|strip_tags|escape} - {$pageTitleTranslated} - Stipwunaraha</title>
        
        <meta name="description" content="{$metaSearchDescription|escape}" />
        <meta name="keywords" content="{$metaSearchKeywords|escape}" />
        <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />

	{$metaCustomHeaders}
	
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}
	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/pkp.css" type="text/css" />
	<!--<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />-->
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

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl != "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}
</head>

<body id="COSIRE">

<div id="wrapper">

	<div id="skip-navigation"><p class="skip-navigation--label"><a id="skip-to-content" href="#main">Skip to Main Content</a></p></div>

{include file="common/navbar.tpl"}
    
    <div {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}class="journal__identity"{else}class="c-journal-header__identity--default"{/if} >
	{if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}<img class="c-journal-header__identity" src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="100%" height="auto" />{/if}    	
    </div>

<div id="content" >
<div id="journal" class="document">
{include file="common/search.tpl"}
<div class="document-main layout-3">
	<div id="sidebar" class="col-aside" style="float: right; margin-right: 4.25531914894%">
		<div id="rightSidebar" class="slide" role="complementary">
			<div class="cover">
				<div>
				<noscript>
				{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
					<img class="look-inside-cover" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue {translate key="issue.coverPage.altText"}"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} style="margin-top: 0" width="153" />
				{else}
					<img class="look-inside-cover img-default" title="Cover issue default" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Stipwunaraha Media & Publishing Group" style="margin-top: 0" width="153" /> {/if}
				</noscript>

				{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
					<img class="look-inside-cover" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue {translate key="issue.coverPage.altText"}"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} style="margin-top: 0" width="153" />
				{else}
					<img class="look-inside-cover img-default" title="Cover issue default" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}/homepageImage_en_US.jpg" alt="Stipwunaraha Media & Publishing Group" style="margin-top: 0" width="153" />
				{/if}
				</div>	
			</div>

            <div id="copublisher-container" data-url="/">
            {if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
            {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}
            	<div class="partner-public">
            		<ul>
            			<li>
            				<div class="partner-logos__logo">
            					<img style="display: block;" class="society-logo" title="Journal Sponsorship/Associated" src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="158" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} />
            				</div>
            				<div class="partner-logos__caption">
            					<p>Published in cooperation with</p>
            					<a class="external" href="{url page="about" op="journalSponsorship"}" class="society-link" title="Click for more about cooperation" target="_blank" rel="noopener noreferrer" data-track="click" data-track-action="Co-publisher" data-track-label="">{$displayPageHeaderLogoAltText|escape}</a>
            				</div>
            			</li>
            		</ul>
            	</div>
            {/if}
            {/if}
            </div>

		</div>
	</div>
<div id="kb-nav--main" class="col-main has-enumeration" role="main" tabindex="-1">
<div id="enumeration">
	<div id="publication-title">
		<a href="{url page="index"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a>
	</div>
	<a id="all-volumes-and-issues" class="all" href="{url page="issue" op="archive"}">All Volumes &amp; Issues</a>
</div>	
<div id="publication-title">
<h1 id="title">{if $issue->getVolume()}{translate key="issue.volume"} {$issue->getVolume()|escape}, {if $issue->getNumber()}{translate key="issue.issue"} {$issue->getNumber()|escape}, {/if}{$issue->getDatePublished()|date_format:"%B %Y"}{else}{$issue->getLocalizedTitle($currentJournal)|escape}{/if}</h1>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h2 id="subtitle">{$pageSubtitleTranslated}</h2>
{/if}

{if $currentJournal && $currentJournal->getSetting('onlineIssn')}
	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal && $currentJournal->getSetting('printIssn')}
	{assign var=issn value=$currentJournal->getSetting('printIssn')}
{/if}
{if $printIssn} {else if $onlineIssn}
{if $currentJournal->getSetting('onlineIssn')}
<div id="issn">ISSN: {if $currentJournal->getSetting('printIssn')}<spab class="pissn">{$currentJournal->getSetting('printIssn')} (Print)</spab>{/if}
	{if $currentJournal->getSetting('onlineIssn')}<spaan class="eissn">{$currentJournal->getSetting('onlineIssn')} (Online)</spaan>{/if}</div>{/if}
{/if}

</div>