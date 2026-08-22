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

	<link rel="canonical" href="{$currentUrl}" />
	
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
    
	<meta name="robots" content="max-image-preview:large" />
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
    
	{call_hook name="Templates::Article::Header::Metadata"}
	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}
    
    <!-- Cookies CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <meta name="referrer" content="strict-origin-when-cross-origin" />
    
    {** 
    <link rel="preload" href="https://badge.dimensions.ai/badge.js" as="script"><script async src="https://badge.dimensions.ai/badge.js" charset="utf-8"></script>
    <link rel="preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/fonts/fontawesome-webfont.woff2?v=4.3.0" as="font" type="font/woff2" crossorigin />
	<link rel="stylesheet preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" /> **}

    <link rel="preload" href="//assets.sangia.org/css/themes/art_srm.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="//assets.sangia.org/css/themes/art_srm.css"></noscript>

    <link rel="stylesheet preload" href="{$baseUrl}/assets/static/styles/Lumera_article--branded.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
	<link rel="stylesheet preload" href="{$baseUrl}/assets/static/styles/font.css" type="text/css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
	<link rel="stylesheet preload" href="{$baseUrl}/assets/static/branded/Lumera_frontend--branded.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />

    {**
	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
        <link rel="preload" href="{$baseUrl}/js/pkp.min.js" as="script" />
		<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}
    **}

	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/print.css" media="print" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/metrics.css" type="text/css" />

    <link rel="preload" href="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" as="script">    
    <script src="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" async="" type="text/javascript"></script>
    
    {** <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.2/lazysizes.min.js" async=""></script>
    <script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script> **}

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}
    
	{$additionalHeadData}

</head>

<body id="sangia.org" class="article-view">
<a class="sr-only sr-only-focusable u-hide" href="#SRM-Pub">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

{include file="common/banner.tpl"}
<header class="c-header" style="border-color:#000">
    {include file="article/navbar.tpl"}
    {include file="article/navmenu.tpl"}
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
