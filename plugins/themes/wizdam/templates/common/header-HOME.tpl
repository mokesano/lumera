<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
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
    <title>{$pageTitleTranslated} - Sangia</title>

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
    
	{include file="common/head.tpl"}

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
	
	<!-- CSS ExternalFeeds style via JS sheet -->
	<style rel="stylesheet" type="text/css">@import url(//www.assets.sangia.org/css/common/sangia-mosaic-v1-feeds.css);</style>

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	{$additionalHeadData}

</head>

<body id="sangia.org" class="Overview">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"></a>
{include file="common/tags.tpl"}

<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/journal-identity.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>

<div class="journal-content">
<div class="live-area-wrapper">
<div class="row">
<section class="column medium-8" role="main">
    
    {if count($editors) > 0}
    <div><b>{if count($editors) == 1}{translate key="user.role.editor"}{else}{translate key="user.role.editors"}{/if}:</b> <span>{$editor->getFullName()|escape}</span>
    </div>
    {/if}
        
    <div class="main-contents" role="main">
        
        {if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
        {if $pageSubtitleTranslated}
        	<h3>{$pageSubtitleTranslated}</h3>
        {/if}
        
        <div id="content" class="main-contents" >
                        
            {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
            	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
            {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
            	{assign var=issn value=$currentJournal->getSetting('printIssn')}
            {/if}
            
            <div class="row">
            	<div class="column medium-6">
            		<ul class="journal-insight u-font-sans">
            		    {if $printIssn} {else if $onlineIssn}
						<li class="meta"><span class="bold">ISSN: </span> {if $currentJournal->getSetting('onlineIssn')}<span class="meta__item"><a class="anchor" href="//portal.issn.org/resource/ISSN/{$currentJournal->getSetting('onlineIssn')}" target="_blank">{$currentJournal->getSetting('onlineIssn')} <svg viewBox="0 0 16 16" class="button-icons-1494494357" width="12" height="11"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a> (Online)</span>{else}<span class="meta__item">on proccess (online)</span>{/if}{if $currentJournal->getSetting('printIssn')}<span class="meta__item"> | <a class="anchor" href="//portal.issn.org/resource/ISSN/{$currentJournal->getSetting('printIssn')}" target="_blank">{$currentJournal->getSetting('printIssn')} <svg viewBox="0 0 16 16" class="button-icons-1494494357" width="12" height="11"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a> (Print)</span>{/if}</li>
						{/if}

						<li class="meta"><span class="meta__item"><span class="bold">Journal title:</span> {$currentJournal->getLocalizedTitle()|strip_tags|escape}</span></li>												
						{if $currentJournal->getSetting('initials')}
						<li class="meta"><span class="meta__item"><span class="bold">Journal Initials:</span> {$currentJournal->getLocalizedInitials()|strip_tags|escape}</li></span>{/if}
												
						{if $currentJournal->getSetting('abbreviation')}
						<li class="meta"><span class="meta__item"><span class="bold">Abbreviation:</span> <span class="italic">{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}</span></span></li>{/if}

            			{if $issue}
            			{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
            			{assign var=firstVolume value=$currentJournal->getSetting('initialVolume')}
            			{assign var=initialNumber value=$currentJournal->getSetting('initialNumber')}
            			{assign var=lastYear value=$issue->getYear()}
            			{assign var=lastVolume value=$issue->getVolume()}
            			<li class="meta"><span class="meta__item"><span class="bold">First {translate key="issue.year"} published:</span> {$firstYear|escape}</span></li>
            			<li class="meta"><span class="meta__item"><span class="bold">First {translate key="issue.volume"} published:</span> {translate key="issue.volume"} {$firstVolume|escape} {translate key="issue.issue"} {$initialNumber|escape}</span></li>
            			
            			{assign var=volumePerYear value=$currentJournal->getSetting('volumePerYear')}
            			{assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            			<li class="meta"><span class="meta__item"><span class="bold">Frequency:</span> {if $issuePerVolume|escape == 1}Annually{elseif $issuePerVolume|escape == 2}Semiannually{elseif $issuePerVolume|escape == 3}Quarterly{elseif $issuePerVolume|escape == 4}Quarterly{elseif $issuePerVolume|escape == 6}Bimonthly{elseif $issuePerVolume|escape == 12}Monthly{/if} — (<span class="italic">{$issuePerVolume|escape} {translate key="issue.issue"} in {$volumePerYear|escape} {translate key="issue.volume"} per year)</span></span></li>
            			
            			<li class="meta"><span class="meta__item"><span class="bold">Coverage {translate key="issue.year"}:</span> {$firstYear|escape}{if $lastYear|escape} <i>to present</i>{else}{$lastYear|escape}{/if}</span></li>
            			<li class="meta"><span class="meta__item"><span class="bold">Coverage {translate key="issue.volume"}:</span> {translate key="issue.volume"} {$firstVolume|escape} — {translate key="issue.volume"} {$lastVolume|escape} (<span class="italic">present</span>)</span></li>
            			{else}
            			<li class="meta"><span class="meta__item"><span class="bold">First Published:</span> <span class="italic">not available</span></span></li>
            			<li class="meta"><span class="meta__item"><span class="bold">Coverage {translate key="issue.year"}:</span> <span class="italic">has not published any issues</span></span></li>
            			<li class="meta"><span class="meta__item"><span class="bold">Coverage {translate key="issue.volume"}:</span> <span class="italic">has not published any issues</span></span></li>
            			{/if}
            			
            			{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}
            			<li class="meta"><span class="bold">Production & hosting:</span> Sangia Publishing</li>
            			{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"} 
            			<li class="meta"><span class="bold">Publisher:</span> {$currentJournal->getSetting('publisherInstitution')}</li>
            			{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing LLC"} 
            			<li class="meta"><span class="bold">Publisher:</span> Sangia Research Media and Publishing, LLC</li>
            			{else}
            			<li class="meta"><span class="bold">Publisher:</span> {$currentJournal->getSetting('publisherInstitution')}</li>
            			<li class="meta"><span class="bold">Production & hosting:</span> Sangia Publishing</li>
            			{/if}
            											
            			{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
            			<li class="meta"><span class="bold">Publishing model:</span> Hybrid. <span class="italic"><a href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}" class="">Open Access options available</a></span></li>
            			{else}
            			<li class="meta"><span class="bold">Publishing model:</span> <span class="italic"><a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" class="">Open Access</a></span></li>
            			{/if}
            			{if $issue}
            			<li class="meta"><span class="bold">Citation analysis:</span> <a title="View citednes in Scopus (Need login)" href="https://www.scopus.com/results/results.uri?numberOfFields=0&src=s&clickedLink=&edit=&editSaveSearch=&origin=searchbasic&authorTab=&affiliationTab=&advancedTab=&scint=1&menu=search&tablin=&searchterm1=%22{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}%22&field1=REF&dateType=Publication_Date_Type&yearFrom=Before+1960&yearTo=Present&loadDate=7&documenttype=All&resetFormLink=&st1=%22{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}%22&st2=&sot=b&sdt=b&sl=50&s=REF%28%22{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}%22%29&sid=AAEF07A725F2EC5F99CE9D186FC358FC.wsnAw8kcdt7IPYLO0V48gA%3A10&searchId=AAEF07A725F2EC5F99CE9D186FC358FC.wsnAw8kcdt7IPYLO0V48gA%3A10&txGid=AAEF07A725F2EC5F99CE9D186FC358FC.wsnAw8kcdt7IPYLO0V48gA%3A1&sort=plf-f&originationType=b&rr=" class="" target="_blank">Citation In Scopus</a>
            			</li>
            			{url|assign:"oaiUrl" page="oai"}
            			<li class="oai-url"><span class="bold">OAI:</span> <span class="oai-url-notice"><a href="{$oaiUrl}" target="_blank" title="Go to view OAI Journal for Indexing (Metadata Harvesting)">{$oaiUrl}</a></span></li>
            			{/if}
            		</ul>
                   
            	{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
            		Associated/Sponsorship with:
            		{if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<a href="{url page="about" op="journalSponsorship"}" class="society-link"><img class="society-logo" title="Associated Journal or Sponsorship" src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="auto" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a>{/if}
            	{/if}
            		
            	</div>
            
            	<div class="column medium-6">
                    {** belum ada aplikasinya **}
            	</div>
            
            	<div class="column medium-12">
                    
            		{if $currentJournal->getLocalizedSetting('history') != ''}
            		<div id="journal-history-link">
            			<span class="icon-container info">
            				This journal was previously published under other titles
            				<a href="{url page="about" op="history" anchor=""}">(view Journal {translate key="about.history"})</a>
            			</span>
            		</div>            			
            		{/if}
            		
            	    <section data-component-mpu="" class="ads c-ad u-mt-16">
                        <div class="c-ad__inner">
                            <script async="" src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- Banners -->
                            <ins class="adsbygoogle c-ad--728x90"
                                 style="display:block"
                                 data-ad-client="ca-pub-8416265824412721"
                                 data-ad-slot="2357794744"></ins>
                            <script>
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>	
                        </div>
                    </section>
            
            		{if $journalDescription}
            		<div class="journalDescription" loading="lazy">{$journalDescription}</div>
            		{/if}
            
            		{if $additionalHomeContent}
            		<div class="b-main-contents" loading="lazy">{$additionalHomeContent}</div>
            		{/if}
            	</div>
            </div>

        </div>
    </div>
</section>

{if $leftSidebarCode || $rightSidebarCode}
<aside class="column medium-4" role="side">
    
    {if $leftSidebarCode} <!-- sidebar code --> {/if}
    
	<section class="cta-aside-section">
	    
        {if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
        <section class="sangia-section">
        	<h4 class="aside-headline-3350774344">Go to <strong>Sangia</strong> to see if you have access</h4>
        	<a href="{url page="issue" op="archive"}" target="_blank" data-track="click" class="button-base-47711979">
        		<span class="button-label-2770091062">Browse Volumes &amp; Issues</span>
        		<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1494494357"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
        
        	<a id="browseVolumeIssues" class="button-base-47711979" href="{url page="search" op="titles"}" target="_blank" data-track="click">
        		<span class="button-label-2770091062">Read All Articles</span>
        		<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1494494357"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
        
        	<a id="browseVolumeIssues" class="button-base-47711979" href="{url page="search" op="authors"}" target="_blank" data-track="click">
        		<span class="button-label-2770091062">Browse Authors</span>
        		<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1494494357"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>	
        </section>
        {/if}
        
		<a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-159610158"><span class="button-label-2658192883">Submit your manuscript</span><svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1925105232"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
		
	</section>
        	
	<div class="box">
		<section class="teaser"><h4 class="headline-424997076">Publish with us</h4>
			<div><p>Find out more about how to publish in this journal<br></p></div><a title="Submission" href="{url page="information" op="authors"}" class="button-base-3479930902"><span class="button-label-256261418">More Information</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-1913844361"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
		</section>
		<div class="resize-sensor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; z-index: -1; visibility: hidden;"><div class="resize-sensor-expand" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: hidden; z-index: -1; visibility: hidden;"><div style="position: absolute; left: 0px; top: 0px; transition: 0s; width: 100000px; height: 100000px;"></div></div><div class="resize-sensor-shrink" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: hidden; z-index: -1; visibility: hidden;"><div style="position: absolute; left: 0; top: 0; transition: 0s; width: 200%; height: 200%"></div></div>
		</div>
	</div>	
			
	{if $rightSidebarCode}
	    {$rightSidebarCode}
	{/if}

</aside>
{/if} <!-- sidebar code -->

</div>

</div>

