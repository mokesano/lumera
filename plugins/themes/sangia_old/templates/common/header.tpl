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
<html xmlns="http://www.w3.org/1999/xhtml" class="js svg" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
    <head>
        <title>{$pageTitleTranslated} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} - Stipwunaraha</title>
        
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

	{$additionalHeadData}
</head>

<body id="sangia.org">
<div id="wrapper">
	
	<div id="skip-navigation"><p class="skip-navigation--label"><a id="skip-to-content" href="#main">Skip to Main Content</a></p></div>

{include file="common/navbar.tpl"}
    
    <div {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}class="journal__identity"{else}class="c-journal-header__identity--default"{/if} >
	{if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}<img class="c-journal-header__identity" src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="100%" height="auto" />{/if}    	
    </div>

<div id="content" >
<!-- {include file="common/breadcrumbs.tpl"} -->
<div id="kb-nav--main" class="document-main main" tabindex="-1">
<div class="layout-1"> 
{if $leftSidebarCode || $rightSidebarCode}
	<aside id="sidebar" class="col-aside">
		<div id="leftSidebar" class="slide slide-left" role="complementary">
			<div class="portlet" id="E_CONTENT">
				<div class="portletHeadline" >
					<div class="portletHeadline"><h3 class="portletHeadline">Read This journal on Stipwunaraha RM®</h3></div>
				</div>
				<div class="containerToOpenLayers">
					<ul class="listToOpenLayer ">
						<li class="listItemToOpenLayer" >
							<a class="linkToOpenLayer isLink rightArrow" href="{url page="search" op="titles"}" target="_blank" title="It opens in new window" >
								<span >All Volumes &amp; Issues</span></a>
						</li>
					</ul>
				</div>
			</div>

			<div class="portlet" id="E_CONTENT">
				<div class="portletHeadline" >
					<div class="portletHeadline"><h3 class="portletHeadline">For Authors and Editors</h3></div>
				</div>
				<div id="abstract-about-additional-links" class="containerToOpenLayers">
					<ul class="impactFactor">
						<li class="ifLabel">
							<div class="itemInformationToolTip"><a class="customBtn infoWhite itemInformationToolTipPoint ifLink alreadyAttached" href="#"></a>
								<div class="itemInformationToolBoxOuter w328Px" style="display: none; z-index: 105;" shadowid="ds2214">
									<div class="itemInformationToolBoxInner">
										<br><span class="toolTipHelper">Journal Citation Reports®</span>
									</div>
								</div>
							</div>			
							<span class="ifYear">2018</span><span class="ifText">&nbsp;Impact Factor</span>
						</li>
						<li class="ifCount" >1.208</li>
					</ul>
					<ul class="listToOpenLayer">
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer isLink rightArrow" href="{url page="author" op="submit"}" target="_blank" title="It opens in new window"><span>Manuscript Submission</span></a>
						</li>
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="editorialTeam"}" ><span>Editorial Board</span></a>
						</li>

						{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}" ><span>Aims and Scope</span></a>
						</li>{/if}

						{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="submissions" anchor="authorGuidelines"}" ><span>Instructions for Authors</span></a>
						</li>{/if}
						
						{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li class="listItemToOpenLayer hashover"><a class="external linkToOpenLayer" href="{url op="editorialPolicies" anchor="openAccessPolicy"}"><span>{translate key="about.openAccessPolicy"}</span></a></li>{/if}

						{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
							<li class="listItemToOpenLayer hashover"><a class="external linkToOpenLayer" href="{url op="subscriptions"}"><span>{translate key="about.subscriptions"}</span></a></li>
							{if !empty($journalSettings.enableAuthorSelfArchive)}<li class="listItemToOpenLayer hashover"><a class="external linkToOpenLayer" href="{url op="editorialPolicies" anchor="authorSelfArchivePolicy"}"><span>{translate key="about.authorSelfArchive"}</span></a></li>{/if}
							{if !empty($journalSettings.enableDelayedOpenAccess)}<li class="listItemToOpenLayer hashover"><a class="external linkToOpenLayer" href="{url op="editorialPolicies" anchor="delayedOpenAccessPolicy"}"><span>{translate key="about.delayedOpenAccess"}</span></a></li>{/if}
							{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseIssueFeeEnabled && $journalSettings.purchaseIssueFee > 0}<li class="listItemToOpenLayer hashover"><a class="external linkToOpenLayer" href="{url op="editorialPolicies" anchor="purchaseIssue"}"><span>{translate key="about.purchaseIssue"}</span></a></li>{/if}
							{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseArticleFeeEnabled && $journalSettings.purchaseArticleFee > 0}<li class="listItemToOpenLayer hashover"><a class="external linkToOpenLayer" href="{url op="editorialPolicies" anchor="purchaseArticle"}"><span>{translate key="about.purchaseArticle"}</span></a></li>{/if}
						{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}

						{if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="submissions" anchor="authorFees"}" ><span>Authors Fees</span></a>
						</li>{/if}
						
						{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="submissions" anchor="copyrightNotice"}" ><span>Copyright Information</span></a>
						</li>{/if}
					</ul>
				</div>
			</dl>
		</div>

		<div class="portlet" id="E_CONTENT">
			<div class="portletHeadline" >
				<div class="portletHeadline"><h3 class="portletHeadline">Service for the Journal</h3></div>
			</div>
				<div id="abstract-about-additional-links" class="containerToOpenLayers">
					<ul class="listToOpenLayer">

						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="contact" anchor="principalContact"}"><span>Contacts</span></a>
						</li>

						{foreach from=$navMenuItems item=navItem key=navItemKey}
							{if $navItem.url != '' && $navItem.name != ''}
							<li class="listItemToOpenLayer hashover" id="navItem-{$navItemKey|escape}">
								<a class="external linkToOpenLayer" href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}"><span>{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</span></a></li>
							{/if}
						{/foreach}

						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="contact"}"><span>Contact this Journal</span></a>
						</li>

						{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.membershipFeeEnabled && $journalSettings.membershipFee > 0}
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="about" op="memberships"}" ><span>Memberships</span></a>
						</li>{/if}
						
						<li class="listItemToOpenLayer hashover">
							<a class="external linkToOpenLayer" href="{url page="user" op="register"}"><span>Sign up for article alerts</span></a>
						</li>
						
						{call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}

					</ul>
				</div>
		</div>
		{if $leftSidebarCode}
			{$leftSidebarCode}
		{/if}
		</div>
	</aside>
{/if}

<main class="col-main" role="main">

<h1>{$pageTitleTranslated}</h1>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h2>{$pageSubtitleTranslated}</h2>
{/if}

<div class="col-main--value" >

