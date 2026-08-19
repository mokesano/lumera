<!DOCTYPE html>
<html lang="{$currentLocale|substr:0:2}">
{**
 * templates/article/header-metrics.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * Header Metrics Article -- Header component.
 *}
<head>
	<title>{translate key="article.articleMetrics"} | {$article->getLocalizedTitle()|strip_tags|escape} - Sangia Publishing</title>
    
	{if $currentJournal->getSetting('onlineIssn')}{assign var="issn" value=$currentJournal->getSetting('onlineIssn')}
    {elseif $currentJournal->getSetting('printIssn')}{assign var="issn" value=$currentJournal->getSetting('printIssn')}
    {elseif $currentJournal->getSetting('issn')}{assign var="issn" value=$currentJournal->getSetting('issn')}
    {/if}
	<meta name="citation_pii" content="P{$issn|strip_tags|escape|replace:'-':''}{$article->getDatePublished()|date_format:"%y%m"}{$article->getLocalizedAbstract()|strip_tags|escape|count_characters:true|string_format:"%05d"}" />
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
    <meta name="citation_id" content="{$article->getId()|escape|string_format:"%07d"}" />
	<meta name="citation_best_id" content="{$article->getBestArticleId($currentJournal)|escape}" />
	{assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}
		<meta name="citation_doi" content="{$article->getPubId('doi')}" />
	{/if}
	<meta name="citation_type" content="JOUR" />
	<meta name="citation_journal_title" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
	<meta name="citation_journal_initials" content="{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}" />
	<meta name="citation_journal_abbrev" content="{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}" />
	<meta name="citation_publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}{/if}" />
	<meta name="description" content="{$article->getLocalizedAbstract()|strip_tags|nl2br|escape}" />
	{foreach from=$article->getAbstract(null) item=alternate key=metaLocale}
	{if $alternate != $article->getLocalizedAbstract()}
    	<meta name="description_alternative" xml:lang="{$metaLocale|substr:0:2|escape}" content="{$alternate|strip_tags|nl2br|escape|truncate:170:"..."}" />
	{/if}
	{/foreach}
	<meta name="citation_article_type" content="{$article->getSectionTitle()|strip_tags|escape}" />
    
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{else}
	<link rel="icon" type="img/ico" href="{$baseUrl}/favicon.ico" />	
	{/if}
    <link rel="apple-touch-icon" sizes="57x57" href="//assets.sangia.org/static/favicon/apple-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="//assets.sangia.org/static/favicon/apple-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="//assets.sangia.org/static/favicon/apple-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="//assets.sangia.org/static/favicon/apple-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="//assets.sangia.org/static/favicon/apple-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="//assets.sangia.org/static/favicon/apple-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="//assets.sangia.org/static/favicon/apple-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="//assets.sangia.org/static/favicon/apple-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="//assets.sangia.org/static/favicon/apple-icon-180x180.png" />
    <link rel="icon" type="image/png" sizes="192x192"  href="//assets.sangia.org/static/favicon/android-icon-192x192.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="//assets.sangia.org/static/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="96x96" href="//assets.sangia.org/static/favicon/favicon-96x96.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="//assets.sangia.org/static/favicon/favicon-16x16.png" />
    
	{include file="article/dublincore.tpl"}
	<meta property="journal_name" content="{$currentJournal->getLocalizedTitle()|strip_tags|nl2br|escape}" />	
	{include file="article/googlescholar.tpl"}		
	
	{if $issn}
	<meta name="prism.issn" content="{$issn|strip_tags|escape}" />
	{/if}
	<meta name="prism.publicationName" content="{$currentJournal->getLocalizedTitle()|strip_tags|nl2br|escape}" />
    {if is_a($article, 'PublishedArticle') && $article->getDatePublished()}
	<meta name="prism.publicationDate" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"}" />
    {elseif $issue && $issue->getYear()}
	<meta name="prism.publicationDate" content="{$issue->getYear()|escape}" />
    {elseif $issue && $issue->getDatePublished()}
	<meta name="prism.publicationDate" content="{$issue->getDatePublished()|date_format:"%Y/%m/%d"}" />
    {/if}	
	<meta name="prism.section" content="{$article->getSectionTitle()|strip_tags|escape}" />
    {if $article->getPages()}
        {if $article->getStartingPage()}
        <meta name="prism.startingPage" content="{$article->getStartingPage()|escape}"/>{/if}
	    {if $article->getEndingPage()}
    	<meta name="prism.endingPage" content="{$article->getEndingPage()|escape}"/>{/if}
	{else}
        <meta name="prism.startingPage" content="{$article->getID()|escape}"/>	
    {/if}
	<meta name="prism.copyright" content="{translate key="submission.copyrightStatement" copyrightHolder=$article->getLocalizedCopyrightHolder()|escape copyrightYear=$article->getCopyrightYear()|escape}" />
	<meta name="prism.rightsAgent" content="journals@sangia.org" />
	<meta name="prism.url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" />
	{assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}
	<meta name="prism.doi" content="doi:{$article->getPubId('doi')}" />
    <meta name="DOI" content="{$article->getPubId('doi')}" />
	{/if}

	<link rel="canonical" href="{$currentUrl}">
	
	<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}" />
	<meta name="twitter:card" content='summary_large_image' />
	<meta name="twitter:image:alt" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape} - Sangia" />
	<meta name="twitter:title" content="{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}" />
	<meta name="twitter:description" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape} - {$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:170:"..."}" />
	
    {call_hook name="Templates::Article::Article::ArticleCoverImage"}
    {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
    
    {** PERBAIKAN 1: Cek apakah $issue ada sebelum menggunakannya **}
    {if $issue}
        {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
    {else}
        {assign var="displayCoverIssue" value=false}
    {/if}

    {if $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage()}
        {assign var=showCoverPage value=true}
    {else}
        {assign var=showCoverPage value=false}
    {/if}
    {if $showCoverPage}
    <meta name="twitter:image" content="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}" />
    {** PERBAIKAN 2: Cek lagi apakah $issue ada sebelum menggunakannya **}
    {elseif $issue && $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && is_array($displayCoverIssue)}
    <meta name="twitter:image" content="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}" />
    {elseif $displayHomepageImage && is_array($displayHomepageImage)}
    <meta name="twitter:image" content="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" />
    {/if}
    
	<meta name="robots" content="max-image-preview:large">
	<meta property="og:type" content="{$article->getSectionTitle()|strip_tags|escape}" />
	<meta property="og:site_name" name="site_name" content="Sangia" />
	<meta property="og:title" content="{$article->getLocalizedTitle()|strip_tags|nl2br}" />
	<meta property="og:description" content="{$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:170:"..."}" />	
	<meta property="og:url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" />
	
    {call_hook name="Templates::Article::Article::ArticleCoverImage"}
    {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
    
    {** PERBAIKAN 1: Cek apakah $issue ada **}
    {if $issue}
        {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
    {else}
        {assign var="displayCoverIssue" value=false}
    {/if}
    
    {if $coverPagePath}
    <meta property="og:image" content="{$coverPagePath|escape}{$coverPageFileName|escape}" />
    {** PERBAIKAN 2: Cek lagi apakah $issue ada **}
    {elseif $issue && $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && is_array($displayCoverIssue)}
    <meta property="og:image" content="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}" />    
    {elseif $displayHomepageImage && is_array($displayHomepageImage)}
    <meta property="og:image" content="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" />
    {/if}
    <meta name="csrf-token" content="{$csrfToken}">
    <meta property='article:publisher' content='//www.facebook.com/111429340332887' />
    <meta property='fb:app_id' content='1575594642876231' />
    {if $article->getLanguage()}
    <meta property="og:locale" content="{$article->getLanguage()|strip_tags|escape}" />
    {/if}

	<meta name="citation_publication_date" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"}" />
	<meta name="citation_online_date" content="{$article->getDateStatusModified()|date_format:"%Y/%m/%d"}" />
	<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOCACHE,NOODP,NOYDIR" />
	<meta name="revisit-after" content="3 days" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />    
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	{$metaCustomHeaders}
	<meta name="publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}{/if}" />
	<meta name="owner" content="PT. Sangia Research Media and Publishing" />
	<meta name="website_owner" content="www.sangia.org" />
	<meta name="SDTech" content="Proudly brought to Rochmady & Darsilan (R&D) by the SRM Technology team in Lasunapa, Muna, Indonesia" />
	
	<!-- preload -->
	<link rel="preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" />
	<link rel="preload" href="{$baseUrl}/assets/static/styles/font.css" type="text/css" as="style" />
    
	{call_hook name="Templates::Article::Header::Metadata"}
	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}
    
    <!-- Cookies CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <link rel="preload" href="https://badge.dimensions.ai/badge.js" as="script"><script async src="https://badge.dimensions.ai/badge.js" charset="utf-8"></script>
    <link rel="preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/fonts/fontawesome-webfont.woff2?v=4.3.0" as="font" type="font/woff2" crossorigin />
	<link rel="stylesheet preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" />

    <link rel="preload" href="//assets.sangia.org/css/themes/art_srm.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="//assets.sangia.org/css/themes/art_srm.css"></noscript>

    <link rel="preload" href="{$baseUrl}/assets/static/styles/wizdam_article_v1.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    
	<link rel="stylesheet preload" href="{$baseUrl}/assets/static/styles/font.css" type="text/css" as="style" />
	
	<link rel="preload" href="{$baseUrl}/assets/static/branded/wizdam_frontend_v2.branded.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    
	{$additionalHeadData}

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
        <link rel="preload" href="{$baseUrl}/js/pkp.min.js" as="script" />
		<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/print.css" media="print" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/metrics.css" type="text/css" />

    <link rel="preload" href="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" as="script">    
    <script src="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" async="" type="text/javascript"></script>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.2/lazysizes.min.js" async=""></script>
    <script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>
    
</head>

<body id="sangia.org" class="article-view">
<a class="sr-only sr-only-focusable u-hide" href="#SRM-Pub">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

<header class="c-header" style="border-color:#000">
    <div class="c-header__row c-header__row--flush">
        <div class="c-header__container">
            <div class="c-header__split">
                <h1 class="c-header__logo-container u-mb-0">
                    <a href="//www.sangia.org" data-track="click" data-track-action="home" data-track-label="image">
                        <picture class="c-header__logo" loading="lazy">
                            <source loading="lazy" srcset="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                            <img class="lazyload" loading="lazy" src="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                        </picture>
                    </a>
                </h1>
                <ul class="c-header__menu c-header__menu--global">
                    <li class="c-header__item c-header__item--sangia-research">
                        {if $siteCategoriesEnabled}
                        <a class="c-header__link" href="/" data-test="siteindex-link" data-track="click" data-track-action="open sangia research index" data-track-label="link">
                            <span>{translate key="navigation.otherJournals"}</span>
                        </a>
                        {/if}{* $categoriesEnabled *}
                    </li>
                    {if !$currentJournal || $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                    <li class="c-header__item c-header__item--padding c-header__item--pipe">
                        <a class="c-header__link c-header__link--search" href="{url page="search" op="titles"}" data-header-expander="" data-test="search-link" data-track="click" data-track-action="open search tray" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span>{translate key="navigation.search"}</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                        </a>
                        <div id="search-menu" class="c-header__dropdown c-header__dropdown--full-width has-tethered u-js-hide" role="banner" data-track-component="sangia-split-header">
                            {include file="common/navsearch.tpl"}
                        </idv>
                    </li>
                    {/if}
                    {if $isUserLoggedIn}
                    <li class="c-header__item c-header__item--padding c-header__item--snid-account-widget">
                        <nav class="c-account-nav" aria-labelledby="account-nav-title">
                            <a id="my-account" class="c-header__link eds-c-header__link c-account-nav__anchor" href="{url page="user"}" data-test="login-link" data-track="click" data-track-action="my account" data-track-category="sangia-split-header" data-track-label="link" aria-expanded="true">
                                {if $userData}
                                <span>{if $userData.firstName|escape !== $userData.lastName|escape}{$userData.firstName|escape|substr:0:1}.{/if}{if $userData.middleName} {$userData.middleName|escape|substr:0:1}.{/if} {$userData.lastName|escape}</span>
                                <div class="Ibar__userLogged u-ml-8">
                                    <figure class="Avatar Avatar--size-32">
                                        {if $userData.profileImage && $userData.profileImage.uploadName}<img src="{$sitePublicFilesDir}/{$userData.profileImage.uploadName}" alt="{$userData.firstName|escape}{if $userData.middleName} {$userData.middleName|escape}{/if} {$userData.lastName|escape}" class="Avatar__img is-inside-mask">{else}<img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="{$userData.firstName|escape}">{/if}
                                    </figure>
                                </div>
                                {else}
                                <span>{translate key="user.myAccount"}</span>
                                <svg id="account-icon" role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                                {/if}
                                {if $unreadNotifications > 0}
                                <span class="notification-icon" id="notification-count">{$unreadNotifications}</span>
                                {/if}
                                <svg class="chevron" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <div id="account-nav-menu" class="c-account-nav__menu c-account-nav__menu--right c-account-nav__menu--chevron-right u-js-hide">
                                {if $userData}
                                <div class="Sangia__user__dropdown c-account-nav__menu-header">
                                    <div class="Sangia__user__avatar">
                                        <figure class="Avatar Avatar--size-96">
                                            {if $userData.profileImage && $userData.profileImage.uploadName}
                                            <img class="Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$userData.profileImage.uploadName}?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">
                                            {elseif $userData.gender == 'F'}
                                            <img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonF.png?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">{elseif $userData.gender == 'M'}<img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonM.png?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">{else}<img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">{/if}
                                        </figure>
                                        {if $userData.is_verified}
                                        <span class="verified badge" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span>{else}<span class="unverified badge icon" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span>{/if}
                                    </div>
                                    {if $userData.salutation || $userData.suffix}
                                    <div class="Sangia__user__salutation u-font-sangia-sans">{$userData.salutation|escape} {if $userData.suffix}— {$userData.suffix}{/if}</div>
                                    {/if}
                                    <div class="Sangia__user__name">{$userData.firstName|escape} {if $userData.middleName} {$userData.middleName|escape}{/if} {$userData.lastName|escape}</div>
                                    <div class="Sangia__user__email">{$userData.email|escape}</div>
                                    <div id="account-nav-title" class="Sangia__user__account u-js-hide">
                                        <span class="u-js-hide">{translate key="plugins.block.user.loggedInAs"}<br></span>
                                        <span id="logged-in-username" data-username="{$loggedInUsername|escape}">{$loggedInUsername|escape}</span>
                                    </div>
                                </div>
                                {/if}
                                <ul class="c-account-nav__menu-list dashoboard user-home">
                                   <li class="c-account-nav__menu-item"><a href="{url page="user"}">Dashoboard</a>
                                   </li>
                                </ul>
                                <ul class="c-account-nav__menu-list">
                                    <li class="c-account-nav__menu-item"><a href="{url page="user" op="viewPublicProfile" path=$userSession->getUserId()|string_format:"%011d"}">{translate key="user.showMyProfile"}</a></li>
                                    <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="profile"}">{translate key="user.editMyProfile"}</a></li>
                                    {if $hasOtherJournals}
                                        {if !$showAllJournals}
                                        <li class="c-account-nav__menu-item"><a href="{url journal="index" page="user"}">{translate key="user.showAllJournals"}</a></li>
                                        {/if}
                                    {/if}
                                    {if $currentJournal}
                                        {if $subscriptionsEnabled}
                                        <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="subscriptions"}">{translate key="user.manageMySubscriptions"}</a></li>
                                        {/if}
                                    {/if}
                                    {if $currentJournal}
                                        {if $acceptGiftPayments}
                                        <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="gifts"}">{translate key="gifts.manageMyGifts"}</a></li>
                                        {/if}
                                    {/if}
                                    {if !$implicitAuth}
                                    <li class="c-account-nav__menu-item"><a href="{url page="user" op="changePassword"}">{translate key="user.changeMyPassword"}</a></li>
                                    {/if}
                                    {if $currentJournal}
                                        {if $journalPaymentsEnabled && $membershipEnabled}
                                            {if $dateEndMembership}
                                    <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="payMembership"}">{translate key="payment.membership.renewMembership"}</a> ({translate key="payment.membership.ends"}: {$dateEndMembership|date_format:$dateFormatShort})</li>
                                    {else}
                                    <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="payMembership"}">{translate key="payment.membership.buyMembership"}</a></li>
                                            {/if}
                                        {/if}{* $journalPaymentsEnabled && $membershipEnabled *}
                                    {/if}{* $userJournal *}
                                    <li class="c-account-nav__menu-item"><a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
                                    
                                    {call_hook name="Templates::User::Index::MyAccount"}
                                    {if $userSession->getSessionVar('signedInAs')}
                                    <li class="c-account-nav__menu-item Login_user_as">
                                        <a id="logout-button" class="c-header__link placeholder" href="{url page="login" op="signOutAsUser"}" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                                            <span>Logout as {$userData.firstName|escape|substr:0:1}.{if $userData.middleName|escape}{$userData.middleName|escape|substr:0:1}.{/if} {$userData.lastName|escape}</span>
                                            <svg aria-hidden="true" focusable="false" role="img" width="22" height="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="m8.72592184 2.54588137c-.48811714-.34391207-1.08343326-.54588137-1.72592184-.54588137-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400182l-.79002171.32905522c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274v.9009805h-1v-.9009805c0-2.5479714 1.54557359-4.79153984 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4 1.09079823 0 2.07961816.43662103 2.80122451 1.1446278-.37707584.09278571-.7373238.22835063-1.07530267.40125357zm-2.72592184 14.45411863h-1v-.9009805c0-2.5479714 1.54557359-4.7915398 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4s4 1.790861 4 4c0 1.40732121-.7267788 2.64489414-1.8254829 3.3578652 2.2799093.9496145 3.8254829 3.1931829 3.8254829 5.7411543v.9009805h-1v-.9009805c0-2.1155483-1.2760206-4.0125067-3.2099783-4.8180274l-.7900217-.3290552v-1.02400184l.6301658-.40892721c.8482885-.55047139 1.3698342-1.489533 1.3698342-2.51900785 0-1.65685425-1.3431458-3-3-3-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400184l-.79002171.3290552c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274z" fill-rule="evenodd" fill="#ffffff"></path></svg>
                                        </a>
                                    </li>
                                    {/if}
                                </ul>
                            </div>
                        </nav>
                    </li>
                    {else}
                    <li class="c-header__item c-header__item--padding">
                        <a id="login-button" class="c-header__link placeholder" href="{url page="login"}" style="login" data-test="login-link" data-track="click" data-track-action="login" data-track-category="sangia-split-header" data-track-label="link">
                            <span>Login</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                    </li>
                    {if !$hideRegisterLink}
                    <li class="u-hide c-header__item c-header__item--padding c-header__item--pipe">
                		<a id="register-button" class="c-header__link placeholder" href="{url page="register"}" style="register" data-test="register-link" data-track="click" data-track-action="register" data-track-category="sangia-split-header" data-track-label="link">
                		    <span>{translate key="navigation.register"}</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                		</a>
                    </li>{/if}
                    {/if}{* $isUserLoggedIn *}
                </ul>
            </div>
        </div>
    </div>
    <div class="c-header__row">
        <div class="c-header__container" data-test="navigation-row">
            <div class="c-header__split">
                <div class="c-header__split">
                    <ul class="c-header__menu c-header__menu--journal lm-nav-root">
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span><span class="c-header__show-text">Explore</span> content</span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">Explore content</h2>
                                    <ul class="c-header-expander__list">
                                        {if $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="issue" op="current"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="journal.currentIssue"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="issue" op="archive"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Archive Issues</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="titles"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="browseSearch" op="sections"}" data-track="click" data-track-label="link" data-test="explore-nav-item">View Section</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="browseSearch" op="identifyTypes"}" data-track="click" data-track-label="link" data-test="explore-nav-item">View Article Type</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                        {/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="siteMap"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.siteMap"}</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="//www.facebook.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Facebook</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="https://twitter.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Twitter</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="notification" op="subscribeMailList"}" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-external="" data-track-label="link (mobile dropdown)">Sign up for alerts<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#fff"></path></svg></a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="gateway" op="plugin"}/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>RSS feed</span></a></li>
                                        
                                        {url|assign:"oaiUrl" page="oai"}
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{$oaiUrl}" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>OAI</span></a></li>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span>{translate key="navigation.about"} <span class="c-header__show-text">the journal</span></span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">About the journal</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialTeam"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.editorialTeam"}</a></li>
                                        
                                        {if $peopleGroups}{iterate from=peopleGroups item=peopleGroup}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="displayMembership" path=$peopleGroup->getId()}" data-track="click" data-track-label="link" data-test="explore-nav-item">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
                                        {/iterate}{/if}
                                        {call_hook name="Templates::About::Index::People"}
                                        
                                        {if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.focusAndScope"}</a></li>{/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="sectionPolicies"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.sectionPolicies"}</a></li>
                                        
                                        {call_hook name="Templates::About::Index::Policies"}
                                        
                                        {if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="peerReviewProcess"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.peerReviewProcess"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.publicationFrequency"}</a></li>{/if}
                                        
                                        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.openAccessPolicy"}</a></li>{/if}
                                        
                                        {foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem}{if !empty($customAboutItem.title)}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="custom-$key"}" data-track="click" data-track-label="link" data-test="explore-nav-item" style="word-break:break-all">{$customAboutItem.title|escape}</a></li>{/if}{/foreach}
                                        
                                        {foreach from=$navMenuItems item=navItem key=navItemKey}{if $navItem.url != '' && $navItem.name != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}" data-track="click" data-track-label="link" data-test="explore-nav-item">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>{/if}{/foreach}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="archiving"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.archiving"}</a></li>
                                        
                                        {if $currentJournal->getLocalizedSetting('history') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="history"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{if $currentJournal->getSetting('initials')}{translate key="about.history"} of {$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}{else}Journal {translate key="about.history"}{/if}</a></li>
                                        {/if}
                                        
                                        {call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}
                                        {call_hook name="Templates::About::Index::Other"}
                                        
                                        {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="journalSponsorship"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.journalSponsorship"}</a></li>{/if}
                                        
                                        {if $siteCategoriesEnabled}
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="/" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>{translate key="navigation.otherJournals"}</span></a></li>
                                        {/if}{* $categoriesEnabled *}
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"} Information</a></li>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                        <li class="c-header__item c-header__item--dropdown-menu u-mr-2">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span>Publish <span class="c-header__show-text">with us</span></span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">Publish with us</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForAuthors"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Submission guidelines</a></li>
                                        
                                        {if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="authorGuidelines"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorGuidelines"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="copyrightNotice"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.copyrightNotice"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="privacyStatement"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.privacyStatement"}</a></li>{/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="librarians"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForLibrarians"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="readers"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForReaders"}</a></li>
                                        
                                        {if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="authorFees"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorFees"}</a></li>{/if}
                                        
                                        {call_hook name="Templates::About::Index::Submissions"}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"} us</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline"><a class="c-header-expander__link" href="{url page="author" op="submit"}" target="_blank" data-track="click" data-track-action="Submit manuscript" data-track-label="link" data-track-external="">Submit manuscript<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m15 0c1.1045695 0 2 .8954305 2 2v5.5c0 .27614237-.2238576.5-.5.5s-.5-.22385763-.5-.5v-5.5c0-.51283584-.3860402-.93550716-.8833789-.99327227l-.1166211-.00672773h-9v3c0 1.1045695-.8954305 2-2 2h-3v10c0 .5128358.38604019.9355072.88337887.9932723l.11662113.0067277h7.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-7.5c-1.1045695 0-2-.8954305-2-2v-10.17157288c0-.53043297.21071368-1.0391408.58578644-1.41421356l3.82842712-3.82842712c.37507276-.37507276.88378059-.58578644 1.41421356-.58578644zm-.5442863 8.18867991 3.3545404 3.35454039c.2508994.2508994.2538696.6596433.0035959.909917-.2429543.2429542-.6561449.2462671-.9065387-.0089489l-2.2609825-2.3045251.0010427 7.2231989c0 .3569916-.2898381.6371378-.6473715.6371378-.3470771 0-.6473715-.2852563-.6473715-.6371378l-.0010428-7.2231995-2.2611222 2.3046654c-.2531661.2580415-.6562868.2592444-.9065605.0089707-.24295423-.2429542-.24865597-.6576651.0036132-.9099343l3.3546673-3.35466731c.2509089-.25090888.6612706-.25227691.9135302-.00001728zm-.9557137-3.18867991c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5zm-8.5-3.587-3.587 3.587h2.587c.55228475 0 1-.44771525 1-1zm8.5 1.587c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5z" fill="#fff"></path></svg></a></li>
                                        
                                        {if $isUserLoggedIn}
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="user"}" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>My Account</span></a></li>
                                        {/if}
                                    </ul>
                                </div>
                            </nav>
                        </li>
                    </ul>
                    
                    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION || $donationEnabled || $currentJournal->getSetting('membershipFee')}
                    <div class="c-header__menu u-ml-16 u-show-lg u-show-at-lg c-header__menu--tools">
                        <div class="c-header__item c-header__item--pipe">
                            {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                            <a class="c-header__link" href="{url page="about" op="subscriptions"}" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                                <span>Subscribe</span>
                            </a>{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}
                            {if $currentJournal->getSetting('donationFeeEnabled')}
                            <a class="c-header__link" href="{url page="donations"}" data-track="click" data-track-action="donation" data-track-label="link" data-test="menu-button-donation">
                                <span>Donation</span>
                            </a>{/if}
                            {if $currentJournal->getSetting('membershipFeeEnabled')}
                            <a class="c-header__link" href="{url page="about" op="memberships"}" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">
                                <span>Members</span>
                            </a>{/if}
                        </div>
                    </div>
                    {/if}
                    
                </div>
                
                <ul class="c-header__menu c-header__menu--tools">
                    <li class="c-header__item">
                        <a class="c-header__link" href="{url page="notification" op="subscribeMailList"}" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-label="link (desktop site header)" data-track-external="">
                            <span>Sign up for alerts</span><svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#222"></path></svg>
                        </a>
                    </li>
                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="{url page="gateway" op="plugin"}/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank">
                            <span>RSS feed</span>
                        </a>
                    </li>
                    {url|assign:"oaiUrl" page="oai"}
                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="{$oaiUrl}" data-track="click" data-track-action="oai feed" data-track-label="link" target="_blank">
                            <span>OAI</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>

<div id="breadcrumb" class="u-show-at-md u-hide-sm-max">
    <div class="row">
    	<div class="columns">
    	<a href="//www.sangia.org">sangia.org</a> <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    	{if $currentJournal}<a href="{url page="$currentJournal"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a> <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>{/if}
    	{foreach from=$pageHierarchy item=hierarchyLink}
    		<a href="{$hierarchyLink[0]|escape}" class="hierarchyLink">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]|escape}{/if}</a> <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    	{/foreach}
    	<a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">{translate key="article.article"}</a>
    	<svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    	<span href="{$currentUrl|escape}" class="current">{translate key="article.metrics"}</span>
    	</div>
    </div>
</div>

<div id="main-content" class="u-container u-mt-24 u-mb-32" data-component="article-container">
