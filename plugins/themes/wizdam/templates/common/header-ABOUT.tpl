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
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
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
	
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MWKMKDX');</script>
    <!-- End Google Tag Manager -->
	
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}

	<!-- Base Jquery -->
	{if $allowCDN}
	<script type="text/javascript" src="//www.google.com/jsapi"></script>
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

<body id="sangia.org">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"></a>
{include file="common/tags.tpl"}

<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/journal-identity.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>
{include file="common/breadcrumbs.tpl"}

<div class="journal-content sangia" role="main">

<div class="live-area-wrapper">
	<div class="row">
	   {if $leftSidebarCode || $rightSidebarCode} 
    	<div class="sidebar">
    	    {if $leftSidebarCode}{/if}
    		<div class="columns medium-2">
    			<nav class="journal-subnav">
        			<div class="live">					
        			    <ul class="s-sidemenu">
        			        <li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="editorialTeam" anchor=""}">{translate key="about.editorialTeam"}</a></li>
        
        			        {if $peopleGroups}{iterate from=peopleGroups item=peopleGroup}
        			        <li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="displayMembership" path=$peopleGroup->getId()}">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
        			        {/iterate}{/if}
        			        
        			        {call_hook name="Templates::About::Index::People"}
        
        			        {if $peopleGroups}
        			        {iterate from=peopleGroups item=peopleGroup}
        			        <li class="c-sidemenu c-bar--menu"><a href="{url op="displayMembership" path=$peopleGroup->getId()}">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
        			        {/iterate}
        			        {/if}
        
        			        {call_hook name="Templates::About::Index::Other"}
        
        			        {if $enableAnnouncements}<li class="c-sidemenu c-bar--menu"><a href="{url page="announcement"}">News & Announcement</a></li>{/if}{* enableAnnouncements *}
        
        			        {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="journalSponsorship"}">{translate key="about.journalSponsorship"}</a></li>{/if}
    
        			        {if $currentJournal->getSetting('membershipFee')}<li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="memberships"}">{translate key="about.memberships"}</a></li>{/if}
        			        
        			        {if $currentJournal->getLocalizedSetting('history') != ''}<li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="history"}">Journal {translate key="about.history"}</a></li>{/if}
        
        			        {if $publicStatisticsEnabled}<li class="c-sidemenu c-bar--menu"><a href="{url op="statistics"}">{translate key="about.statistics"}</a></li>{/if}
        
        			        {if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="editorialPolicies" anchor="archiving"}">{translate key="about.archiving"}</a></li>{/if}
      					
          					<li class="c-sidemenu c-bar--menu"><a href="{url page="about" op="contact"}">Contacts</a></li>    			        
        			     </ul>
        			</div>    
    			</nav>
    			
				<section class="ads adsbox c-ad c-ad--160x600 u-mt-32 null">
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
                </section>    			
                
    		</div>
		
			<section class="column medium-3">
				<section class="box">
					<section><h4 class="headline-524909129">Want to publish with us? Submit your Manuscript online.</h4></section>
					<a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
						<span class="button-label-1281676810">Submit paper</span>
						<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
					</a>
				</section>
				
				<section class="ads">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Banners -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-8416265824412721"
                         data-ad-slot="5917495376"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </section>
                
                {if $rightSidebarCode}
                    {$rightSidebarCode}
                {/if}

				<section class="ads">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Banners -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-8416265824412721"
                         data-ad-slot="5917495376"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </section>
                
			</section>
		</div>
		{/if}

<div class="column medium-7" role="main">
<section class="article">
<h2 class="main-heading">{$pageTitleTranslated}</h2>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h3 class="sub-heading">{$pageSubtitleTranslated}</h3>
{/if}

<section id="content" class="publication">

