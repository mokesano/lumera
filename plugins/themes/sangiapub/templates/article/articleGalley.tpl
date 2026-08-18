<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}">
{**
 * templates/article/articleGalley.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * [FIX] Halaman PDF galley yang benar-benar mandiri: TIDAK memakai
 * article/header.tpl (header situs penuh: logo, navbar, breadcrumb) atau
 * common/footer.tpl (footer situs penuh). Bar "return to article" di
 * bawah memakai markup asli (.header_view/.return/.title/.download) yang
 * SUDAH punya styling lengkap di styles/pdfView.css -- bukan markup baru.
 *}
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{$article->getLocalizedTitle()|strip_tags|escape} | {$currentJournal->getLocalizedTitle()|strip_tags|escape}</title>
	{if $article->getData('pii')}
        <meta name="citation_pii" content="{$article->getData('pii')|escape}" />
    {/if}
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

	<link rel="canonical" href="{$currentUrl|escape}" />
	
	<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())|escape}" />
	<meta name="twitter:card" content='summary_large_image' />
	<meta name="twitter:image:alt" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape} - Sangia" />
	<meta name="twitter:title" content="{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}" />
	<meta name="twitter:description" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape} - {$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:170:"..."}" />
    
	<meta name="robots" content="max-image-preview:large" />
	<meta property="og:type" content="{$article->getSectionTitle()|strip_tags|escape}" />
	<meta property="og:site_name" name="site_name" content="Sangia" />
	<meta property="og:title" content="{$article->getLocalizedTitle()|strip_tags|nl2br}" />
	<meta property="og:description" content="{$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:170:"..."}" />	
	<meta property="og:url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|escape}" />
    
    <meta property='article:publisher' content='//www.facebook.com/111429340332887' />
    <meta property='fb:app_id' content='1575594642876231' />
    {if $article->getLanguage()}
    <meta property="og:locale" content="{$article->getLanguage()|strip_tags|escape}" />
    {/if}
    <meta name="csrf-token" content="{$csrfToken}" />
    <meta name="referrer" content="strict-origin-when-cross-origin" />

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

	{* jQuery + jQuery UI: dibutuhkan oleh script embed PDF di article/pdfViewer.tpl (.resizable(), $(document).ready) *}
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>

    <link rel="stylesheet preload" href="{$baseUrl}/assets/static/styles/font.css" type="text/css" as="style" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="{$baseUrl}/styles/articleView.css" type="text/css" />

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}

	{$additionalHeadData}
</head>
<body class="article-view">
	<div id="pdfDownloadLinkContainer" class="header_view">
		<a class="return" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}"><span class="screen_reader">Return to Article Details</span></a>
		<a class="title" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
		<a class="action pdf download" id="pdfDownloadLink" target="_parent" href="{url op="download" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}"><span class="label">{translate key="article.pdf.download"}</span></a>
	</div>

	{include file="article/pdfViewer.tpl"}
</body>
</html>