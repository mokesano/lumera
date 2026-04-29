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
	<title>{$article->getLocalizedTitle()|strip_tags|escape}{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"} - Sangia{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing LLC"} - Sangia Group{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"} - {$currentJournal->getSetting('publisherInstitution')}{else} - Sangia{/if}</title>
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="citation_id" content="{$article->getBestArticleId($currentJournal)|escape}" />
	{assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}
		<meta name="citation_doi" content="{$article->getPubId('doi')}" />
	{/if}
	<meta name="citation_type" content="JOUR" />
	<meta name="citation_journal_title" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
	<meta name="citation_journal_initials" content="{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}" />
	<meta name="citation_journal_abbrev" content="{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}" />
	<meta name="citation_publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sekolah Tinggi Ilmu Pertanian Wuna{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing LLC"}Sangia Publishing Group{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}{/if}" />
	<meta name="description" content="{$article->getLocalizedAbstract()|strip_tags|escape}" />
	{foreach from=$article->getAbstract(null) item=alternate key=metaLocale}
	{if $alternate != $article->getLocalizedAbstract()}
    	<meta name="description_alternative" xml:lang="{$metaLocale|String_substr:0:|escape}" content="{$alternate|strip_tags|escape|truncate:170:"..."}" />
	{/if}
	{/foreach}
	<meta name="citation_article_type" content="{$article->getSectionTitle()|strip_tags|escape}" />
	
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MWKMKDX');</script>
    
	{if $displayFavicon}
	<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />
	{else}
	<link rel="icon" type="img/ico" href="{$baseUrl}/favicon.ico" />	
	{/if}
    <link rel="apple-touch-icon" sizes="57x57" href="//stipwunaraha.ac.id/static/favicon/apple-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="//stipwunaraha.ac.id/static/favicon/apple-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="//stipwunaraha.ac.id/static/favicon/apple-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="//stipwunaraha.ac.id/static/favicon/apple-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="//stipwunaraha.ac.id/static/favicon/apple-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="//stipwunaraha.ac.id/static/favicon/apple-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="//stipwunaraha.ac.id/static/favicon/apple-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="//stipwunaraha.ac.id/static/favicon/apple-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="//stipwunaraha.ac.id/static/favicon/apple-icon-180x180.png" />
    <link rel="icon" type="image/png" sizes="192x192"  href="//stipwunaraha.ac.id/static/favicon/android-icon-192x192.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="//stipwunaraha.ac.id/static/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="96x96" href="//stipwunaraha.ac.id/static/favicon/favicon-96x96.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="//stipwunaraha.ac.id/static/favicon/favicon-16x16.png" />
    
	{include file="article/dublincore.tpl"}
	<meta property="journal_name" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />	
	{include file="article/googlescholar.tpl"}		
	
	{if $issn}
	<meta name="prism.issn" content="{$issn|strip_tags|escape}" />
	{/if}
	<meta name="prism.publicationName" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
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

	<link rel="canonical" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" />
	<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}" />
	<meta name="twitter:card" content="summary" />
	<meta name="twitter:image:alt" content="Content cover image" />
	<meta property="og:title" content="{$article->getLocalizedTitle()|strip_tags|nl2br}" />
	<meta property="og:description" content="{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br|truncate:170:"..."}" />	
	<meta property="og:url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" />
	{if $issue}
	{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
	<meta property="og:image" content="{$coverPagePath|escape}{$issue->getFileName($locale)|escape:"url"}" />
	{else}
	<meta property="og:image" content="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" />
	{/if}{/if}
	{call_hook name="Templates::Article::Article::ArticleCoverImage"}
	{if $homepageImage}
	<meta property="og:image" content="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" />
	{/if}
	<meta property="og:type" content="{$article->getSectionTitle()|strip_tags|escape}" />
	<meta property="og:site_name" name="site_name" content="Sangia" />
	
	<!-- Cookies Policy style sheet -->
	<style rel="stylesheet" type="text/css">@import url(//media.stipwunaraha.ac.id/static/css/cokies.css);</style>
	
    <link rel="preload" href="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js" as="script" />            
    <script data-ad-client="ca-pub-8416265824412721" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script async="" src="https://scholar.google.com/scholar_js/casa.js" type="text/javascript"></script>
	<!-- preload -->
    <link rel="preload" href="https://scholar.google.com/scholar_js/casa.js" as="script" />
    
	{call_hook name="Templates::Article::Header::Metadata"}
	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

	<!-- Base Jquery -->
	{if $allowCDN}
	<script type="text/javascript">
	{literal}
		// Provide a local fallback if the CDN cannot be reached
		if (typeof google == 'undefined') {
			document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
			document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js' type='text/javascript'%3E%3C/script%3E"));
		} else {
			google.load("jquery", "{/literal}{$smarty.const.CDN_JQUERY_VERSION}{literal}");
			google.load("jqueryui", "{/literal}{$smarty.const.CDN_JQUERY_UI_VERSION}{literal}");
		}
	{/literal}
	</script>
	{else}
    <link rel="preload" href="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js" as="script">
    <link rel="preload" href="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js" as="script">
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>
	{/if}
	
    <link rel="preload" href="https://badge.dimensions.ai/badge.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="https://badge.dimensions.ai/badge.css"></noscript>
    <link rel="preload" href="https://badge.dimensions.ai/badge.js" as="script"><script async src="https://badge.dimensions.ai/badge.js" charset="utf-8"></script>
    
    <link rel="stylesheet" href="{$baseUrl}/plugins/themes/sangia-clas/css/view-article.css" type="text/css" />

	<!-- Subscribe style sheet -->
    <link rel="preload" href="https://stipwunaraha.ac.id/media/static/css/subcribe.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://stipwunaraha.ac.id/media/static/css/subcribe.css"></noscript>
        
    <link rel="preload" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/fonts/fontawesome-webfont.woff2?v=4.3.0" as="font" type="font/woff2" crossorigin />
	<link rel="stylesheet preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" />
	<link rel="stylesheet preload" href="{$baseUrl}/plugins/themes/sangia-clas/css/font.css" type="text/css" as="style" />	
    
	{$additionalHeadData}    

<!-- Begin for PDF Galley -->
{if $galley}
	{if $galley->isPdfGalley()}
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta content="https://reader.readcube.com/v4/scripts/pdf.worker.82ef90b4.js" name="worker-src" />
	<meta content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" property="og:url" />
	
	{elseif $galley->isHTMLGalley()}
		{include file="article/head.tpl"}
	{/if}

{else}

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

	<link rel="stylesheet" href="{$baseUrl}/plugins/themes/stipwunaraha/css/print.css" media="print" type="text/css" />

{/if} <!-- Finish for PDF Galley -->

	<meta name="citation_publication_date" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"}" />
	<meta name="citation_online_date" content="{$article->getDateStatusModified()|date_format:"%Y/%m/%d"}" />
	<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOODP,NOYDIR" />
	<meta name="revisit-after" content="3 days" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />    
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	{$metaCustomHeaders}
	<meta name="website_owner" content="kumebano.com™" />
	<meta name="SDTech" content="Proudly brought to Rochmady & Darsilan (R&D) by the SRM Technology team in Lasunapa, Muna, Indonesia" />

    <link href="//app.wizdom.ai" rel="preconnect" />
    <link rel="preload" href="https://www.googletagservices.com/tag/js/gpt.js" as="script" />
    <script async="" src="https://www.googletagservices.com/tag/js/gpt.js" type="text/javascript"></script>
    
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;
    
        
        j.addEventListener('load', function() {
        var _ge = new CustomEvent('gtm_loaded', { bubbles: true });
        d.dispatchEvent(_ge);
        });
    
        f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NWDMT9Q');</script>    
    <link rel="preload" href="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" as="script">    
    <script src="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" async="" type="text/javascript"></script>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.2/lazysizes.min.js" async=""></script>
     
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6Leneg8fAAAAAGPvmwdmpa3IqQm-jm7vdpus-4Eo"></script>
    <script>
    grecaptcha.enterprise.ready(function() {
        grecaptcha.enterprise.execute('6Leneg8fAAAAAGPvmwdmpa3IqQm-jm7vdpus-4Eo', {action: 'login'}).then(function(token) {
           ...
        });
    });
    </script>    

</head>

<body id="sangia.org" class="article-view">
<a class="sr-only sr-only-focusable u-hide" href="#sangia.org">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

<div data-iso-key="_0">
{if $galley}
	{if $galley->isPdfGalley()}{include file="article/pdfViewer.tpl"}{/if}
	{if $galley->isHTMLGalley()}
	    {include file="common/banner.tpl"}
		{include file="article/heading.tpl"}
	{/if}	
{else}
    {include file="common/banner.tpl"}
	{include file="article/heading.tpl"}
{/if}

