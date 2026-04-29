<!DOCTYPE html>
<html class="js svg" lang="{$currentLocale|substr:0:2}" xml:lang="{$currentLocale|substr:0:2}">
{**
 * templates/article/header.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Header component.
 *}
<head>
	<title>{$article->getLocalizedTitle()|strip_tags|escape}{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"} - Sangia{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"} - Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"} - {$currentJournal->getSetting('publisherInstitution')|escape}{else} - Sangia Publishing{/if}</title>
    
	{if $article->getData('pii')}
        <meta name="citation_pii" content="{$article->getData('pii')|escape}" />
    {/if}
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
    <meta name="citation_id" content="{$article->getId()|escape|string_format:"%07d"}" />
	<meta name="citation_best_id" content="{$article->getBestArticleId($currentJournal)|escape}" />
	{assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}
		<meta name="citation_doi" content="{$article->getPubId('doi')|escape}" />
	{/if}
	<meta name="citation_type" content="JOUR" />
	<meta name="citation_journal_title" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
	<meta name="citation_journal_initials" content="{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())|escape}" />
	<meta name="citation_journal_abbrev" content="{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())|escape}" />
	<meta name="citation_publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')|escape}{else}{$currentJournal->getSetting('publisherInstitution')|escape}{/if}" />
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
	<meta name="prism.publicationDate" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"|escape}" />
    {elseif $issue && $issue->getYear()}
	<meta name="prism.publicationDate" content="{$issue->getYear()|escape}" />
    {elseif $issue && $issue->getDatePublished()}
	<meta name="prism.publicationDate" content="{$issue->getDatePublished()|date_format:"%Y/%m/%d"|escape}" />
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

	<link rel="canonical" href="{$currentUrl|escape}">
	
	<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())|escape}" />
	<meta name="twitter:card" content='summary_large_image' />
	<meta name="twitter:image:alt" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape} - Sangia" />
	<meta name="twitter:title" content="{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}" />
	<meta name="twitter:description" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape} - {$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:170:"..."}" />
	
    {call_hook name="Templates::Article::Article::ArticleCoverImage"}
    {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
    {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
    {if $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage()}
        {assign var=showCoverPage value=true}
    {else}
        {assign var=showCoverPage value=false}
    {/if}
    {if $showCoverPage}
	<meta name="twitter:image" content="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}" />
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
	<meta property="og:url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|escape}" />
	
    {call_hook name="Templates::Article::Article::ArticleCoverImage"}
    {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
    {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
    {if $coverPagePath}
    <meta property="og:image" content="{$coverPagePath|escape}{$coverPageFileName|escape}" />
    {elseif $issue && $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && is_array($displayCoverIssue)}
    <meta property="og:image" content="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}" />    
    {elseif $displayHomepageImage && is_array($displayHomepageImage)}
    <meta property="og:image" content="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" />
    {/if}
    
    <meta property='article:publisher' content='//www.facebook.com/111429340332887' />
    <meta property='fb:app_id' content='1575594642876231' />
    {if $article->getLanguage()}
    <meta property="og:locale" content="{$article->getLanguage()|strip_tags|escape}" />
    {/if}
    <meta name="csrf-token" content="{$csrfToken}">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <!-- Cookies CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
	{call_hook name="Templates::Article::Header::Metadata"}
	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

	<script async="" src="//scholar.google.com/scholar_js/casa.js" type="text/javascript" referrerpolicy="strict-origin-when-cross-origin"></script>
    <script async src="https://badge.dimensions.ai/badge.js" charset="utf-8" referrerpolicy="strict-origin-when-cross-origin"></script>
    
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
	
	<link rel="preload" href="{$baseUrl}/assets/static/styles/font.css" type="text/css" as="style" />
    <link rel="preload" href="//assets.sangia.org/css/themes/art_srm.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="//assets.sangia.org/css/themes/art_srm.css"></noscript>

    <link rel="preload" href="{$baseUrl}/assets/static/styles/wizdam_article_v1.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    
	<link rel="stylesheet preload" href="{$baseUrl}/assets/static/styles/font.css" type="text/css" as="style" />
    
	{$additionalHeadData}    

<!-- Begin for PDF Galley -->
{if $galley}
	{if $galley->isPdfGalley()}
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|escape}" property="og:url" />
 	<link rel="stylesheet" href="{$baseUrl}/styles/articleView.css" type="text/css" />
 	<link rel="canonical" href="{$currentUrl|escape}">
	
	{elseif $galley->isHTMLGalley()}
		{include file="article/head.tpl"}
		<link rel="canonical" href="{$currentUrl|escape}">
	{/if}

{else}

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
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

{/if} <!-- Finish for PDF Galley -->

	<meta name="citation_publication_date" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"|escape}" />
	<meta name="citation_online_date" content="{$article->getDateStatusModified()|date_format:"%Y/%m/%d"|escape}" />
	<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOCACHE,NOODP,NOYDIR" />
	<meta name="revisit-after" content="3 days" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />    
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	{$metaCustomHeaders}
	<meta name="publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')|escape}{else}{$currentJournal->getSetting('publisherInstitution')|escape}{/if}" />
	<meta name="owner" content="PT. Sangia Research Media and Publishing" />
	<meta name="website_owner" content="www.sangia.org" />
	<meta name="SDTech" content="Proudly brought to Rochmady & Darsilan (R&D) by the SRM Technology team in Lasunapa, Muna, Indonesia" />

    <!-- OneTrust Cookies Consent Notice -->
    <script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="ef8d6a4d-3871-4684-91c9-80259f6aacfe-test" referrerpolicy="strict-origin-when-cross-origin" ></script>
    <!-- OneTrust Cookies Consent Notice -->

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.2/lazysizes.min.js" async="" referrerpolicy="strict-origin-when-cross-origin"></script>
    
</head>

<body id="sangia.org" class="article-view">
<a class="sr-only sr-only-focusable u-hide" href="#SRM-Pub">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

<div data-iso-key="_0">
{if $galley}
	{if $galley->isPdfGalley() && $smarty.template != 'article/pdfViewer.tpl'}
	    {include file="article/pdfViewer.tpl"}
	{/if}
	{if $galley->isHTMLGalley()}
	    {include file="common/banner.tpl"}
		{include file="article/heading.tpl"}
	{/if}	
{else}
    {include file="common/banner.tpl"}
	{include file="article/heading.tpl"}
{/if}

