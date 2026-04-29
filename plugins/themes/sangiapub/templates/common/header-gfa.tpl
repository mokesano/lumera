<!DOCTYPE html>
<html lang="{$currentLocale|substr:0:2}">
{**
 * header-gfa.tpl
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
	<title>{$pageTitleTranslated} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | ScholarWizdam</title>
	
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
<a class="buttontop" href="#sangia.org"></a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{include file="common/breadcrumbs.tpl"}

<div class="journal-content sangia" role="main">

<div class="live-area-wrapper u-mt-48">
	<div class="row u-row">
		<div class="sidebar">
		    <aside class="u-hide column medium-3"></aside>
			<div id="menu" class="column medium-3">
			    <ul class="c-sidemenu c-nav c-nav--stacked c-collapse-at-lt-md">
  					<li class="journal-navigation-header u-hide">Header</li>
	  				<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies"}"><span class="c-flex c-flex--align-baseline"><svg class="u-icon u-flex-static u-mr-2" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>{translate key="about.aboutJournal"} Policies (redevelop)</span></a>
	  				    <ul class="c-menu--anchor c-nav c-nav--stacked">
          					{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="focusAndScope"}"><span class="c-flex c-flex--align-baseline">Aims &amp; Scope</span></a></li>{/if}
          					<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="sectionPolicies"}"><span class="c-flex c-flex--align-baseline">{translate key="about.sectionPolicies"}</span></a></li>
          					{foreach key=key from=$customAboutItems item=customAboutItem}{if $customAboutItem.title!=''}
          					<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor=custom-$key}"><span class="c-flex c-flex--align-baseline">{$customAboutItem.title|escape}</span></a></li>{/if}
          					{/foreach}
          					{call_hook name="Templates::About::Index::Policies"}
        			        {if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li id="linkReviewPolicy" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="peerReviewProcess"}"><span class="c-flex c-flex--align-baseline">{translate key="about.peerReviewProcess"}</span></a></li>{/if}
        			        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li id="linkPubFreqPolicy" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="publicationFrequency"}"><span class="c-flex c-flex--align-baseline">{translate key="about.publicationFrequency"}</span></a></li>{/if}
        			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li id="linkOpenAccessPolicy" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="openAccessPolicy"}"><span class="c-flex c-flex--align-baseline">{translate key="about.openAccessPolicy"}</span></a></li>{/if}
        			        {if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li id="linkLockssLicense" class="c-bar--menu"><a class="c-nav__link" href="{url op="editorial-policies" anchor="archiving"}"><span class="c-flex c-flex--align-baseline">{translate key="about.archiving"}</span></a></li>{/if}
        			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')}<li id="enabledAuthorSelfArchive" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="authorSelfArchivePolicy"}"><span class="c-flex c-flex--align-baseline">{translate key="about.authorSelfArchive"}</span></a></li>{/if}
        			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableDelayedOpenAccess')}<li id="enabledDelayedOpenAccess" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor="delayedOpenAccessPolicy"}"><span class="c-flex c-flex--align-baseline">{translate key="about.delayedOpenAccess"}</span></a></li>{/if}
        			        {foreach from=$currentJournal->getLocalizedSetting('customAboutItems') key=key item=customAboutItem}
        			        {if !empty($customAboutItem.title)}
        			            <li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="editorial-policies" anchor=$customAboutItem.title|replace:" ":""|escape}"><span class="c-flex c-flex--align-baseline">{$customAboutItem.title|escape}</span></a></li>
        			        {/if}
        			        {/foreach}
	  				    </ul>
	  				</li>
	  				
  					{foreach from=$navMenuItems item=navItem key=navItemKey}
  					{if $navItem.url != '' && $navItem.name != ''}
  					<li class="c-bar--menu"><a class="c-nav__link" href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}"><span class="c-flex c-flex--align-baseline">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</span></a></li>{/if}
  					{/foreach}
  					{if $enableAnnouncements}<li class="c-bar--menu"><a class="c-nav__link" href="{url page="announcement"}"><span class="c-flex c-flex--align-baseline">News & Announcement</span></a></li>{/if}
  					{* enableAnnouncements *}
  					
  					<li class="c-bar--menu"><a class="c-nav__link" href="{url page="policies"}"><span class="c-flex c-flex--align-baseline"><svg class="u-icon u-flex-static u-mr-2" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>{translate key="about.journalPolicies"}</span></a>
	  				    <ul class="c-menu--anchor c-nav c-nav--stacked">
	  				        <li class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op="privacy-statement"}"><span class="c-flex c-flex--align-baseline">{translate key="about.privacyStatement"}</span></a></li>
        			        {if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li id="linkReviewPolicy" class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op="peer-review"}"><span class="c-flex c-flex--align-baseline">{translate key="about.peerReviewProcess"}</span></a></li>{/if}
        			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li id="linkOpenAccessPolicy" class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op="open-access"}"><span class="c-flex c-flex--align-baseline">{translate key="about.openAccessPolicy"}</span></a></li>{/if}
        			        {if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li id="linkLockssLicense" class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op="archiving"}"><span class="c-flex c-flex--align-baseline">{translate key="about.archiving"}</span></a></li>{/if}
	  				        <li class="c-bar--menu"><a class="c-nav__link"  href="{url op="copyright"}"><span class="c-flex c-flex--align-baseline">{translate key="about.copyrightNotice"}</span></a></li>
        			        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li id="linkPubFreqPolicy" class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op="publication-frequency"}"><span class="c-flex c-flex--align-baseline">{translate key="about.publicationFrequency"}</span></a></li>{/if}
          					<li class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op="section-policies"}"><span class="c-flex c-flex--align-baseline">{translate key="about.sectionPolicies"}</span></a></li>
        			        {foreach from=$customPolicies item=policy}
        			        {if !empty($policy.title)}
        			            <li class="c-bar--menu"><a class="c-nav__link" href="{url page="policies" op=$policy.slug}"><span class="c-flex c-flex--align-baseline">{$policy.title|escape}</span></a></li>
        			        {/if}
        			        {/foreach}
	  				    </ul>
	  				</li>
	  				
  					<li class="c-bar--menu"><a class="c-nav__link" href="{url page="information" op="authors"}"><span class="c-flex c-flex--align-baseline">{translate key="navigation.infoForAuthors.long"}</span></a></li>
  					
	  				<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions"}"><span class="c-flex c-flex--align-baseline"><svg class="u-icon u-flex-static u-mr-2" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>Submission (redevelop)</span></a>
	  				    <ul class="c-menu--anchor c-nav c-nav--stacked">
	  				        <li id="linkDisableUserReg" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions" anchor="onlineSubmissions"}"><span class="c-flex c-flex--align-baseline">{translate key="about.onlineSubmissions"}</span></a></li>
	  				        {if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions" anchor="authorGuidelines"}"><span class="c-flex c-flex--align-baseline">{translate key="about.authorGuidelines"}</span></a></li>{/if}
	  				        {if $submissionChecklist}<li id="linkSubmissionPreparationChecklist" class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions" anchor="submissionPreparationChecklist"}"><span class="c-flex c-flex--align-baseline">{translate key="about.submissionPreparationChecklist"}</span></a></li>{/if}{* $submissionChecklist *}
	  				        {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions" anchor="copyrightNotice"}"><span class="c-flex c-flex--align-baseline">{translate key="about.copyrightNotice"}</span></a></li>{/if}
	  				        {call_hook name="Templates::About::Index::Submissions"}
	  				        {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions" anchor="privacyStatement"}"><span class="c-flex c-flex--align-baseline">{translate key="about.privacyStatement"}</span></a></li>{/if}
	  				        {if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}<li class="c-bar--menu"><a class="c-nav__link" href="{url page="about" op="submissions" anchor="authorFees"}"><span class="c-flex c-flex--align-baseline">{translate key="about.authorFees"}</span></a></li>{/if}
	  				    </ul>
	  				</li>
	  				<li class="c-bar--menu"><a class="c-nav__link" href="{url page="information" op="readers"}"><span class="c-flex c-flex--align-baseline">{translate key="navigation.infoForReaders.long"}</span></a></li>
  					<li class="c-bar--menu"><a class="c-nav__link" href="{url page="information" op="librarians"}"><span class="c-flex c-flex--align-baseline">{translate key="navigation.infoForLibrarians.long"}</span></a></li>
			     </ul>
			     
				<section class="u-mt-32 box u-js-hide">
					<section><h4 class="headline-524909129">Want to publish with us? Submit your Manuscript online.</h4></section>
					<a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
						<span class="button-label-1281676810">Submit paper </span>
						<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
					</a>
				</section>
			</div>
		</div>

<div class="column medium-9" role="main">

<h2 class="main-heading">{$pageTitleTranslated}</h2>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h3 class="sub-heading">{$pageSubtitleTranslated}</h3>
{/if}

<div id="content" class="article body">

