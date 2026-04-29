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
 *
 * [WIZDAM v2] Logika URL Bertingkat:
 *   L1 (Normal)      : volume ada + number ada  → /volumes/{vol}/issue/{slug}
 *   L2 (Degradasi)   : volume ada + number NULL → /volumes/{vol}
 *   L3 (Deg. Penuh)  : volume NULL              → /year/{year} atau /volumes/
 *
 * PERHATIAN SMARTY 2.x:
 *   {if $someVar} mengevaluasi "0" sebagai FALSY — sama dengan string kosong!
 *   Selalu gunakan |strlen > 0 untuk memeriksa keberadaan nilai string,
 *   terutama untuk volume dan number yang bisa bernilai 0 (valid).
 *}
{strip}
{if !$pageTitleTranslated}
    {translate|assign:"pageTitleTranslated" key=$pageTitle}
    {* Override untuk key default OJS *}
    {if $pageTitle == "common.openJournalSystems"}
        {assign var="pageTitleTranslated" value="No Current Issue"}
    {/if}
{/if}
{if $pageCrumbTitle}
    {translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
    {assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{$pageTitleTranslated} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | Sangia Publishing</title>
    <meta name="description" content="{$metaSearchDescription|escape}" />
    <meta name="keywords" content="{$metaSearchKeywords|escape}" />
    
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

	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/wizdam-mosaic-v1-branded.css" type="text/css" />
	
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
<a class="buttontop" href="#sangia.org"><!-- Back to top button --></a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="common/navbar.tpl"}
    {include file="common/navmenu.tpl"}
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
{include file="common/breadcrumbs.tpl"}

<main class="{if $issue}journal-content{else}volumes-content{/if} sangia-volumes" role="main">
<div class="live-area-wrapper">
	<div class="row">

{if $issue}
<div class="column medium-12 cleared container-type-title" role="main" data-container-type="title" >
    
<main itemscope="itemscope" itemtype="https://schema.org/PublicationIssue">
	<section class="u-display-flex u-align-items-center u-justify-content-space-between u-flex-wrap u-mb-16">

	{* ================================================================
	   WIZDAM: Hitung currentUrl untuk heading issue (tombol showToc)
	   
	   ATURAN PENTING (Smarty 2.x):
	     - {if $someVar} mengevaluasi "0" sebagai FALSY!
	     - Selalu gunakan |strlen > 0 untuk cek keberadaan nilai.
	     - number=0 adalah VALID → harus lolos ke L1 jika volume ada.
	     - volume=NULL di DB → (string)'' → strlen == 0 → trigger L2/L3.
	   ================================================================ *}
	{if !$showToc}
		{if $issueId}
			{* --- Hitung issueVolume --- *}
			{assign var="issueVolume" value=$issue->getVolume()}

			{* --- Hitung issueSlug dari number --- *}
			{assign var="issueNum" value=$issue->getNumber()}
			{* Gunakan strlen: number="0" → strlen=1 → masuk else, jadi slug="0" (valid) *}
			{if $issueNum|strlen == 0}
				{* number kosong/NULL → tidak bisa jadi slug → tandai kosong *}
				{assign var="issueSlug" value=""}
			{else}
				{* number ada ("0", "1", "2", "Supplement") → slugify *}
				{assign var="issueSlug" value=$issueNum|slugify}
				{* Jika hasil slugify kosong (misal karakter aneh semua) → fallback ke ID *}
				{if $issueSlug|strlen == 0}
					{assign var="issueSlug" value=$issue->getId()}
				{/if}
			{/if}

			{* --- Pilih URL sesuai level degradasi --- *}
			{* Gunakan strlen : issueVolume="0" → strlen=1 → valid *}
			{if $issueSlug|strlen > 0 && $issueVolume|strlen > 0}
				{* L1 (Normal): Ada volume DAN ada slug → URL issue lengkap *}
				{native_url|assign:"currentUrl" page="issue" volume=$issueVolume slug=$issueSlug showToc=true}
			{elseif $issueVolume|strlen > 0}
				{* L2 (Degradasi): Volume ada, number NULL → URL volume *}
				{url|assign:"currentUrl" page="volumes" op="view" path=$issueVolume}
			{else}
				{* L3 (Degradasi Penuh): Volume NULL → coba tahun *}
				{assign var="issueYear" value=$issue->getYear()}
				{if $issueYear|strlen == 0}
					{assign var="issueYear" value=$issue->getDatePublished()|date_format:"%Y"}
				{/if}
				{if $issueYear|strlen > 0}
					{url|assign:"currentUrl" page="volumes" op="year" path=$issueYear}
				{else}
					{url|assign:"currentUrl" page="volumes" op="displayArchive"}
				{/if}
			{/if}

		{else}
			{* Tidak ada issueId → ke current issue *}
			{url|assign:"currentUrl" page="issue" op="current" path="showToc"}
		{/if}

		<h2 class="headline-4241089976"><a href="{$currentUrl}">{translate key="issue.volume"} {$issue->getVolume()|escape}{if $issue->getNumber()} {translate key="issue.issue"} {$issue->getNumber()|strip_tags|nl2br|escape}{/if}, {$issue->getDatePublished()|date_format:"%B %Y"|escape}</a></h2>
	{else}
		<h2 class="headline-4241089976">{translate key="issue.volume"} {$issue->getVolume()|escape}{if $issue->getNumber()} {translate key="issue.issue"} {$issue->getNumber()|strip_tags|nl2br|escape}{/if}, {$issue->getDatePublished()|date_format:"%B %Y"|escape}</h2>
	{/if}

    	<nav class="u-hide-print" data-track-component="issue navigation" aria-label="issue navigation" role="navigation">
    	    <span class="c-pagination app-pagination-borderless">

			{* ================================================================
			   WIZDAM: Dua mode navigasi berdasarkan $isVolumeAsIssue

			   MODE A ($isVolumeAsIssue = true):
			     Volume menggantikan issue. Navigasi menggunakan Prev/Next
			     VOLUME ($prevVolumeId / $nextVolumeId).
			     $prevIssue / $nextIssue sengaja di-null dari VolumesHandler
			     agar mode ini aktif dan tidak terjadi loop.

			   MODE B (normal, $isVolumeAsIssue tidak di-set):
			     Issue memiliki number valid. Navigasi menggunakan Prev/Next
			     ISSUE ($prevIssue / $nextIssue) dengan logika degradasi URL.
			   ============================================================== *}

			{if $isVolumeAsIssue}

			{* ================================================================
			   MODE A: Navigasi Prev/Next VOLUME
			   URL: /volumes/{prevVolumeId} dan /volumes/{nextVolumeId}
			   Tidak ada kalkulasi slug — volume diakses via ID langsung.
			   ============================================================== *}

				{if $prevVolumeId}
				<span class="c-pagination__item">
				    <a href="{url page="volumes" op="view" path=$prevVolumeId}" class="c-pagination__link" data-track="click" data-track-action="previous volume" data-track-label="link">
				        <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z" transform="rotate(180 8 8)"></path></svg>
				        {translate key="navigation.prevVolume"}
				    </a>
				</span>
				{/if}

				<span class="c-pagination__item">
				    <a class="c-pagination__link" href="{url page="volumes" op="view" path=$volumeId}" data-track="click" data-track-action="view volume" data-track-label="link">
				        {translate key="issue.volume"} {$issue->getVolume()|escape}
				        <h2 class="kicker u-hide">{translate key="issue.vol"} {$issue->getVolume()|escape} ({$issue->getDatePublished()|date_format:"%Y"})</h2>
				    </a>
				</span>

				{if $nextVolumeId}
				<span class="c-pagination__item">
				    <a href="{url page="volumes" op="view" path=$nextVolumeId}" class="c-pagination__link" data-track="click" data-track-action="next volume" data-track-label="link">
				        {translate key="navigation.nextVolume"}
				        <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z"></path></svg>
				    </a>
				</span>
				{/if}

			{else}

			{* ================================================================
			   MODE B: Navigasi Prev/Next ISSUE (normal)
			   Hitung URL dengan logika degradasi bertingkat.
			   ============================================================== *}

				{* --- Tombol Issue SEBELUMNYA --- *}
				{if $prevIssue}
				<span class="c-pagination__item">

					{* Hitung volume dan slug prevIssue *}
					{assign var="prevIssueVolume" value=$prevIssue->getVolume()}
					{assign var="prevIssueNum"    value=$prevIssue->getNumber()}
					{if $prevIssueNum|strlen == 0}
						{assign var="prevIssueSlug" value=""}
					{else}
						{assign var="prevIssueSlug" value=$prevIssueNum|slugify}
						{if $prevIssueSlug|strlen == 0}
							{assign var="prevIssueSlug" value=$prevIssue->getId()}
						{/if}
					{/if}

					{* Pilih URL sesuai degradasi *}
					{if $prevIssueSlug|strlen > 0 && $prevIssueVolume|strlen > 0}
						{native_url|assign:"prevIssueUrl" page="issue" volume=$prevIssueVolume slug=$prevIssueSlug}
					{elseif $prevIssueVolume|strlen > 0}
						{url|assign:"prevIssueUrl" page="volumes" op="view" path=$prevIssueVolume}
					{else}
						{assign var="prevIssueYear" value=$prevIssue->getYear()}
						{if $prevIssueYear|strlen == 0}
							{assign var="prevIssueYear" value=$prevIssue->getDatePublished()|date_format:"%Y"}
						{/if}
						{if $prevIssueYear|strlen > 0}
							{url|assign:"prevIssueUrl" page="volumes" op="year" path=$prevIssueYear}
						{else}
							{url|assign:"prevIssueUrl" page="volumes" op="displayArchive"}
						{/if}
					{/if}

					<a href="{$prevIssueUrl}" class="c-pagination__link" data-track="click" data-track-action="previous link" data-track-label="link">
					    <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z" transform="rotate(180 8 8)"></path></svg>
					    {translate key="navigation.prevIssue"}
					</a>
				</span>
				{/if}

				{* --- Tombol Volume (tengah) --- *}
				<span class="c-pagination__item">
				    <a class="c-pagination__link" href="{native_url page="volume" volume=$issue->getVolume()}" data-track="click" data-track-action="view volume" data-track-label="link">
				        {translate key="issue.volume"} {$issue->getVolume()|escape}
				        <h2 class="kicker u-hide">{translate key="issue.vol"} {$issue->getVolume()|escape}{if $issue->getNumber() eq "0"} Sup{elseif $issue->getNumber()|regex_replace:"/[^A-Za-z]/":"" eq $issue->getNumber()} {$issue->getNumber()|strip_tags|nl2br|truncate:3:"."|escape}{else} {translate key="issue.no"} {$issue->getNumber()|strip_tags|escape}{/if} ({$issue->getDatePublished()|date_format:"%Y"})</h2>
				    </a>
				</span>

				{* --- Tombol Issue BERIKUTNYA --- *}
				{if $nextIssue}
				<span class="c-pagination__item">

					{* Hitung volume dan slug nextIssue *}
					{assign var="nextIssueVolume" value=$nextIssue->getVolume()}
					{assign var="nextIssueNum"    value=$nextIssue->getNumber()}
					{if $nextIssueNum|strlen == 0}
						{assign var="nextIssueSlug" value=""}
					{else}
						{assign var="nextIssueSlug" value=$nextIssueNum|slugify}
						{if $nextIssueSlug|strlen == 0}
							{assign var="nextIssueSlug" value=$nextIssue->getId()}
						{/if}
					{/if}

					{* Pilih URL sesuai degradasi *}
					{if $nextIssueSlug|strlen > 0 && $nextIssueVolume|strlen > 0}
						{native_url|assign:"nextIssueUrl" page="issue" volume=$nextIssueVolume slug=$nextIssueSlug}
					{elseif $nextIssueVolume|strlen > 0}
						{url|assign:"nextIssueUrl" page="volumes" op="view" path=$nextIssueVolume}
					{else}
						{assign var="nextIssueYear" value=$nextIssue->getYear()}
						{if $nextIssueYear|strlen == 0}
							{assign var="nextIssueYear" value=$nextIssue->getDatePublished()|date_format:"%Y"}
						{/if}
						{if $nextIssueYear|strlen > 0}
							{url|assign:"nextIssueUrl" page="volumes" op="year" path=$nextIssueYear}
						{else}
							{url|assign:"nextIssueUrl" page="volumes" op="displayArchive"}
						{/if}
					{/if}

					<a href="{$nextIssueUrl}" class="c-pagination__link" data-track="click" data-track-action="next link" data-track-label="link">
					    {translate key="navigation.nextIssue"}
					    <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z"></path></svg>
					</a>
				</span>
				{/if}

			{/if}{* end $isVolumeAsIssue *}

            </span>
    	</nav>
	</section>
	
	<section class="l-with-sidebar" style="--with-sidebar--gap:0;--with-sidebar--min:58%">
		<div class="app-volumes-cover" data-test="issue-cover-container">
		    <div class="app-volumes-cover__copy" data-test="issue-description">
		        <div class="app-volumes-description">
		            {if $issue->getLocalizedTitle($currentJournal)}
		            <h2 class="u-h3-title">{$issue->getLocalizedTitle($currentJournal)|escape}</h2>{/if}
		            {if $issue->getLocalizedDescription()}
		            <p data-promo-text="" data-promo-text-threshold="560">{$issue->getLocalizedDescription()|nl2br}</p>{/if}
		            {if $issue && !$showToc}
		            <p data-promo-text="ShowToc" data-promo-text-threshold="560">This issue may be a special issue, so it was deemed necessary to first display the cover issue before viewing the table of contents. To see the list of articles in this issue, please click <a href="{$currentUrl}">HERE</a> or on the Table of contents.</p>
		            {/if}
		            {if $issue->getLocalizedCoverPageDescription()}
		            <p class="app-volumes-cover__image-copy-text" data-test="issue-image-credit" data-credit="{$issue->getLocalizedCoverPageDescription()|nl2br}">{$issue->getLocalizedCoverPageDescription()|nl2br}</p>
		            {/if}
		        </div>
		        <div class="app-volumes-contents">
                    {if $subscriptionRequired && $showGalleyLinks && $showToc}
                    <section id="accessKey" class="access-key block">
                        <div class="access">
                            <span class="open">
                                <img class="lazyload u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
                                <span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span>
                                <span class="open-access" data-color="gold">{translate key="reader.openAccess"}</span>
                            </span>&nbsp;|&nbsp;
                            <span class="subscribe">
                                <img class="lazyload u-text-top" src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="{translate key="article.accessLogoRestricted.altText"}" height="17" />
                                <span aria-hidden="false" aria-label="Open Access" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig" data-color="silver"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">S</span></span>
                        		<span class="subscribe-access" data-color="silver">
                        		{if $purchaseArticleEnabled}
                        			{translate key="reader.subscriptionOrFeeAccess"}
                        		{else}
                        			{translate key="reader.subscriptionAccess"}
                        		{/if}
                        		</span>
                        	</span>
                    	</div>
                    </section>
                    {/if}
		            
                    {foreach from=$pubIdPlugins item=pubIdPlugin}
                    {if $issue->getPublished()}
                    	{assign var=pubId value=$pubIdPlugin->getPubId($issue)}
                    {else}
                    	{assign var=pubId value=$pubIdPlugin->getPubId($issue, true)}{* Preview rather than assign a pubId *}
                    {/if}
                    {if $pubId}
                    <p class="app-volumes-doi-link u-mb-0">{$pubIdPlugin->getPubIdDisplayType()|escape}: {if $pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}<a id="pub-id::{$pubIdPlugin->getPubIdType()|escape}" href="{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}">{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}</a>{else}{$pubId|escape}{/if}</p>
                    {/if}
                    {/foreach}
		        </div>
		    </div>
			{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
		    <div class="app-volumes-cover__image">
				<img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" data-src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" {if $issue->getCoverPageAltText($locale) != ''}title="Cover issue {$issue->getCoverPageAltText($locale)|escape}"{else}title="Cover issue {translate key="issue.coverPage.altText"}"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} data-test="issue-cover-image" />
				{call_hook name="Templates::Article::Article::ArticleCoverImage"}		        
		        <p class="u-mt-16 u-mb-0"><a class="u-button u-button--primary u-button--full-width" href="{url page="about" op="subscriptions"}" data-test="issue-page-subscribe-button" data-track="click" data-track-action="subscribe" data-track-category="sangia-issue-page" data-track-label="button">Subscribe</a>
		        </p>
		    </div>
		    {/if}
		</div>

		<div class="l-with-sidebar__sidebar" style="--with-sidebar--basis: 385px;" data-test="issue-toc-container">
		    <aside class="u-full-height app-toc u-pa-16" aria-label="Issue navigation"><div data-container-type="issue-reading-companion">
		      <div class="clear cleared" data-component="reading-companion-placeholder">
		          <div data-component="reading-companion-sticky">
		              <nav id="toc">
		                  <div data-component="reading-companion-sections">

		                  {* ===================================================
		                     Sidebar TOC: Hitung currentUrl untuk link TOC (showToc)
		                     Logika degradasi sama persis dengan blok heading di atas.
		                     ================================================ *}
		                  {if $issue && !$showToc}

		                  	  {* Reuse variabel yang sudah dihitung di blok heading atas *}
		                  	  {* Jika issueVolume/issueSlug belum di-assign (misal showToc=false
		                  	     tidak masuk blok atas), hitung ulang di sini *}
		                  	  {if !isset($issueVolume)}
		                  	  	  {assign var="issueVolume" value=$issue->getVolume()}
		                  	  {/if}
		                  	  {if !isset($issueSlug)}
		                  	  	  {assign var="issueNum" value=$issue->getNumber()}
		                  	  	  {if $issueNum|strlen == 0}
		                  	  	  	  {assign var="issueSlug" value=""}
		                  	  	  {else}
		                  	  	  	  {assign var="issueSlug" value=$issueNum|slugify}
		                  	  	  	  {if $issueSlug|strlen == 0}
		                  	  	  	  	  {assign var="issueSlug" value=$issue->getId()}
		                  	  	  	  {/if}
		                  	  	  {/if}
		                  	  {/if}

		                  	  {* Bangun currentUrl untuk sidebar (showToc=true) *}
		                      {if $issueId}
		                          {if $issueSlug|strlen > 0 && $issueVolume|strlen > 0}
		                              {native_url|assign:"currentUrl" page="issue" volume=$issueVolume slug=$issueSlug showToc=true}
		                          {elseif $issueVolume|strlen > 0}
		                              {url|assign:"currentUrl" page="volumes" op="view" path=$issueVolume}
		                          {else}
		                              {assign var="issueYear" value=$issue->getYear()}
		                              {if $issueYear|strlen == 0}
		                                  {assign var="issueYear" value=$issue->getDatePublished()|date_format:"%Y"}
		                              {/if}
		                              {if $issueYear|strlen > 0}
		                                  {url|assign:"currentUrl" page="volumes" op="year" path=$issueYear}
		                              {else}
		                                  {url|assign:"currentUrl" page="volumes" op="displayArchive"}
		                              {/if}
		                          {/if}
		                      {else}
		                          {url|assign:"currentUrl" page="issue" op="current" path="showToc"}
		                      {/if}

		                      <h1 class="app-toc__title u-mb-16"><a href="{$currentUrl}"><span class="content-break">{translate key="issue.toc"}</span><span class="text-gray-light altSize">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></a></h1>
		                      <ol class="u-list-reset u-mb-0" data-component="reading-companion-section-items">
		                          {if $issueGalleys}
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#{translate key="issue.fullIssue"}" data-id="{translate key="issue.fullIssue"}" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:{translate key="issue.fullIssue"}">{translate key="issue.fullIssue"}</a>
		                          </li>
		                          {/if}
		                          {foreach name=sections from=$publishedArticles item=section key=sectionId}
		                          {assign var=sections value=$publishedArticles}
		                          {if $section.title}
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#{$section.title|escape|replace:" ":""}" data-id="{$section.title|escape|replace:" ":""}" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:{$section.title|escape}">{$section.title|escape}</a>
		                          </li>
		                          {/if}
		                          {/foreach}
		                      </ol>
		                      <p data-promo-text="ShowToc" data-promo-text-threshold="560">This issue may be a special issue, so it was deemed necessary to first display the cover issue before viewing the table of contents. To see the list of articles in this issue, please click <a href="{$currentUrl}">HERE</a> or on the Table of contents.</p>
		                  {else}
		                      <h1 class="app-toc__title u-mb-16"><span class="content-break">{translate key="issue.toc"}</span><span class="text-gray-light altSize">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></h1>
		                      <ol class="u-list-reset u-mb-0" data-component="reading-companion-section-items">
		                          {if $issueGalleys}
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#{translate key="issue.fullIssue"}" data-id="{translate key="issue.fullIssue"}" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:{translate key="issue.fullIssue"}">{translate key="issue.fullIssue"}</a>
		                          </li>
		                          {/if}
		                          {foreach name=sections from=$publishedArticles item=section key=sectionId}
		                          {assign var=sections value=$publishedArticles}
		                          {if $section.title}
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#{$section.title|escape|replace:" ":""}" data-id="{$section.title|escape|replace:" ":""}" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:{$section.title|escape}">{$section.title|escape}</a>
		                          </li>
		                          {/if}
		                          {/foreach}
		                      </ol>
		                  {/if}
		                  </div>
		              </nav>
		          </div>
		      </div>
		    </div>
		    </aside>
		</div>
	</section>
</main>

<div id="volumes-contents" issueid="{$issue->getId()|string_format:"%07d"}" class="issue-contents" data-component="article-container">
		
{else}

<div class="container cleared container-type-title" data-container-type="title" >
    <div class="content mb20 mt20 mq1200-padded">
    	<h1 class="content main-heading">{$pageTitleTranslated}</h1>
    	{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
    	{if $pageSubtitleTranslated}<h3 class="content sub-heading">{$pageSubtitleTranslated}</h3>{/if}
    </div>
</div>
<div class="column medium-12 cleared container-type-volume-grid" data-container-type="volume-grid" data-track-component="volume grid">

{/if}
