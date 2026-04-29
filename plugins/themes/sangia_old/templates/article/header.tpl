{**
 * templates/article/header.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Header component.
 *}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
<html  class="js svg" xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<title>{$article->getLocalizedTitle()|strip_tags|escape} - SRM Publishing</title>
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="citation_id" content="{$article->getBestArticleId($currentJournal)|escape}"/>	
	<meta name="citation_journal_title" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}"/>
	<meta name="citation_journal_abbrev" content="{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}"/>
	{assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}
		<meta name="citation_doi" content="{$article->getPubId('doi')}">
	{/if}
	<meta name="description" content="{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br|truncate:170:"..."}" />
	{if $article->getLocalizedSubject()}
		<meta name="keywords" content="{$article->getLocalizedSubject()|strip_unsafe_html|nl2br}" />
	{/if}
	
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{else}
	<link rel="icon" type="img/ico" href="{$baseUrl}/favicon.ico" />	
	{/if}
    <link rel="apple-touch-icon" sizes="57x57" href="//stipwunaraha.ac.id/static/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="//stipwunaraha.ac.id/static/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="//stipwunaraha.ac.id/static/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="//stipwunaraha.ac.id/static/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="//stipwunaraha.ac.id/static/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="//stipwunaraha.ac.id/static/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="//stipwunaraha.ac.id/static/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="//stipwunaraha.ac.id/static/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="//stipwunaraha.ac.id/static/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="//stipwunaraha.ac.id/static/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="//stipwunaraha.ac.id/static/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="//stipwunaraha.ac.id/static/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="//stipwunaraha.ac.id/static/favicon/favicon-16x16.png">
    <link rel="manifest" href="//stipwunaraha.ac.id/static/favicon/manifest.json">
	{include file="article/googlescholar.tpl"}		
	{include file="article/dublincore.tpl"}
	{if $issn}
	<meta name="prism.issn" content="{$issn|strip_tags|escape}"/>
	{/if}
	<meta name="prism.publicationName" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}">
    {if is_a($article, 'PublishedArticle') && $article->getDatePublished()}
	<meta name="prism.publicationDate" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"}"/>
    {elseif $issue && $issue->getYear()}
	<meta name="prism.publicationDate" content="{$issue->getYear()|escape}"/>
    {elseif $issue && $issue->getDatePublished()}
	<meta name="prism.publicationDate" content="{$issue->getDatePublished()|date_format:"%Y/%m/%d"}"/>
    {/if}	
	<meta name="prism.section" content="{$article->getSectionTitle()|strip_tags|escape}">
    {if $article->getPages()}
    {if $article->getStartingPage()}
    <meta name="prism.startingPage" content="{$article->getStartingPage()|escape}"/>{/if}
	{if $article->getEndingPage()}
	<meta name="prism.endingPage" content="{$article->getEndingPage()|escape}"/>{/if}
    {/if}
	<meta name="prism.copyright" content="{translate key="submission.copyrightStatement" copyrightHolder=$article->getLocalizedCopyrightHolder()|escape copyrightYear=$article->getCopyrightYear()|escape}">
	<meta name="prism.rightsAgent" content="admin@stipwunaraha.ac.id">
	<meta name="prism.url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">
	{assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}
	<meta name="prism.doi" content="doi:{$article->getPubId('doi')}">
    <meta name="DOI" content="{$article->getPubId('doi')}">
	{/if}
	<meta name="citation_article_type" content="{$article->getSectionTitle()|strip_tags|escape}">
	<link rel="canonical" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">
	<meta name="citation_publisher" content="SRM Publishing">	
	<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:image:alt" content="Content cover image">
	<meta property="og:title" content="{$article->getLocalizedTitle()|strip_tags|nl2br}">	
	<meta property="og:description" content="{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br|truncate:170:"..."}">	
	<meta property="og:url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">
	{if $issue}
	{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
	<meta property="og:image" content="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}">
	{else}
	<meta property="og:image" content="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}">
	{/if}{/if}
	{call_hook name="Templates::Article::Article::ArticleCoverImage"}
	{if $homepageImage}
	<meta property="og:image" content="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}">
	{/if}
	<meta property="og:type" content="{$article->getSectionTitle()|strip_tags|escape}">
	<meta property="og:site_name" name="site_name" content="Sangia">
	<meta property="journal_name" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}">
    <link rel="preload" href="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js" as="script">            
    <script data-ad-client="ca-pub-8416265824412721" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

    <link rel="preload" href="https://scholar.google.com/scholar_js/casa.js" as="script">
	<script async="" src="https://scholar.google.com/scholar_js/casa.js" type="text/javascript"></script>
	<!-- preload -->
	<link rel="preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" >
	<link rel="preload" href="{$baseUrl}/plugins/themes/stipwunaraha/css/font.css" type="text/css" as="style" />	
    <link rel="preload" href="https://scholar.google.com/scholar_js/casa.js" as="script">
    <link rel="preload" href="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js" as="script" >
    
	{call_hook name="Templates::Article::Header::Metadata"}
	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

	<!-- Base Jquery -->
	{if $allowCDN}
	<script type="text/javascript">{literal}
		// Provide a local fallback if the CDN cannot be reached
		if (typeof google == 'undefined') {
			document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
			document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js' type='text/javascript'%3E%3C/script%3E"));
		} else {
			google.load("jquery", "{/literal}{$smarty.const.CDN_JQUERY_VERSION}{literal}");
			google.load("jqueryui", "{/literal}{$smarty.const.CDN_JQUERY_UI_VERSION}{literal}");
		}
	{/literal}</script>
	{else}
    <link rel="preload" href="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js" as="script">
    <link rel="preload" href="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js" as="script">
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>
	{/if}

    <link rel="preload" href="https://stipwunaraha.ac.id/media/static/css/minifi-sangia-html.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/minifi-sangia-html.css"></noscript>
    <link rel="preload" href="https://stipwunaraha.ac.id/media/static/css/minifi-sangia-modern.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/minifi-sangia-modern.css"></noscript>
    <link rel="preload" href="https://stipwunaraha.ac.id/media/static/css/sangia.article.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/sangia.article.css"></noscript>    
    <link rel="preload" href="https://badge.dimensions.ai/badge.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://badge.dimensions.ai/badge.css"></noscript>

    <link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/minifi-sangia-html.css">
    <link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/minifi-sangia-modern.css">
    <link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/sangia.article.css">
        
    <link rel="preload" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/fonts/fontawesome-webfont.woff2?v=4.3.0" as="font" type="font/woff2" crossorigin>
	<link rel="stylesheet preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" >
	<link rel="stylesheet preload" href="{$baseUrl}/plugins/themes/stipwunaraha/css/font.css" type="text/css" as="style" />	
    
	{$additionalHeadData}    

<!-- Begin for PDF Galley -->
{if $galley}
	{if $galley->isPdfGalley()}
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta content="https://reader.readcube.com/v4/scripts/pdf.worker.82ef90b4.js" name="worker-src">
	<meta content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" property="og:url">
	<link rel="stylesheet" media="all" href="https://reader.readcube.com/v4/styles/viewer.320c193d.css">
	<link rel="prefetch" href="https://reader.readcube.com/v4/scripts/pdf.worker.82ef90b4.js" crossorigin="anonymous">
	<link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/sangia.pdf.css" type="text/css" />	
 	<link rel="stylesheet" href="{$baseUrl}/styles/articleView.css" type="text/css" />	
	<base href="resource://epdf.js/web/" rel="preconnect">
	<link rel="stylesheet" href="viewer.css">	
	<script src="../build/epdf.js" rel="preconnect"></script>
	<script src="viewer.js"></script> 
    <link rel="preload" href="https://reader.readcube.com/v4/scripts/pdf.b8221cb0.js" as="script">
    <link rel="preload" href="https://reader.readcube.com/v4/scripts/index.5575059c.js" as="script">    
	<script src="https://reader.readcube.com/v4/scripts/pdf.b8221cb0.js"></script>
	<script src="https://reader.readcube.com/v4/scripts/index.5575059c.js"></script>
	
	{elseif $galley->isHTMLGalley()}
		{include file="article/head.tpl"}
	{/if}

{else}

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
        <link rel="preload" href="{$baseUrl}/js/pkp.min.js" as="script">	
		<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

	{foreach from=$stylesheets name="testUrl" item=cssUrl}
		{if $cssUrl == "$baseUrl/styles/ojs.css"}
			<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
		{/if}
	{/foreach}

	<link rel="stylesheet" href="{$baseUrl}/plugins/themes/stipwunaraha/css/print.css" media="print" type="text/css" />

{/if} <!-- Finish for PDF Galley -->

    <link href="//app.wizdom.ai" rel="preconnect">
    <link rel="preload" href="https://www.googletagservices.com/tag/js/gpt.js" as="script">        
    <script async="" src="https://www.googletagservices.com/tag/js/gpt.js" type="text/javascript"></script>
	<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOODP,NOYDIR">    
	<meta name="viewport" content="width=device-width, initial-scale=1">    
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="publisher" content="Sangia Research Media" />
	<meta name="website_owner" content="www.sangia.org" />
	<meta name="owner" content="PT. Sangia Research Media and Publishing" />
	<meta name="SDTech" content="Proudly brought to Rochmady & Darsilan (R&D) by the SRM Technology team in Lasunapa, Muna, Indonesia">

</head>

<body id="sangia" class="article-view">

<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MWKMKDX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-8416265824412721"
     data-ad-slot="5917495376"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>

<a class="sr-only sr-only-focusable u-hide" href="#SRM-Pub">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

<style type="text/css">
.lm-masthead > :first-of-type > .lm-column, .lm-masthead > :first-child > .lm-column {padding-bottom: 3.2%;padding-top: 2.8%;}
</style>

<div data-iso-key="_0">
{if $galley}
	{if $galley->isPdfGalley()}{include file="article/pdfViewer.tpl"}{/if}
	{if $galley->isHTMLGalley()}
		{include file="article/heading.tpl"}
	{/if}	
{else}
	{include file="article/heading.tpl"}
{/if}

