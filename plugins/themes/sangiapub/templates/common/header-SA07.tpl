<!DOCTYPE html>
<html lang="{$currentLocale|substr:0:2}" xml:lang="{$currentLocale|substr:0:2}">
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
	<title>{if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{else}{$pageTitleTranslated}{/if}{if $currentJournal} in {$currentJournal->getLocalizedInitials()|strip_tags|escape}{/if} | {if $siteSearch}Sangia Search Results{else}{$siteTitle}{/if}</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
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
	
	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/search.css" type="text/css" />
	
	<!-- CSS style via JS sheet -->
	<link rel="stylesheet" href="{$baseUrl}/assets/static/styles/wizdam_search.css" type="text/css" />
	<!--
	<style rel="stylesheet" type="text/css">@import url({$baseUrl}/plugins/themes/sangiapub/css/journal-mosaic-v1-branded.css);</style>
	-->
	
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

<div class="journal-content sangia">
 <div class="s-container u-mb-16">
   <div class="row">

<div id="article" role="main">
<div class="u-mb-16" data-test="search-results-title">

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#searchForm').pkpHandler('$.pkp.pages.search.SearchFormHandler');
	{rdelim});
</script>

{capture assign="filterInput"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName=$filterName filterValue=$filterValue}{/capture}
{url|assign:"searchFormUrl" page="search" op="search" escape=false}
{$searchFormUrl|parse_url:$smarty.const.PHP_URL_QUERY|parse_str:$formUrlParameters}
<form method="GET" id="sub-search" action="{$searchFormUrl|strtok:"?"|escape}">
    <input type="hidden" value="{if $currentJournal}{$currentJournal->getLocalizedInitials()|strip_tags|lower|escape}{else}all{/if}" name="journal">
	<div class="c-search c-search--max-width u-mb-24">
		<h1 class="title">
		    <div class="c-search__input-label u-mb-16 u-h1" for="search-keywords">Search</div>
		</h1>
		<div class="c-search__field">
		{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
		{if empty($queryFilter)}
		<div class="c-search__input-container c-search__input-container--sm">
			{foreach from=$formUrlParameters key=paramKey item=paramValue}
			<input type="hidden" name="{$paramKey|escape}" value="{$paramValue|escape}"/>
			{/foreach}
			<input id="search-keywords" type="text" data-test="search-box" name="query" value="{$query|escape}" placeholder="Search" class="c-search__input" />
		</div>
		{elseif $hasActiveFilters}
		    {$filterValue|escape}	
		{else}
			{$queryFilter|escape}
		{/if}
		<div class="c-search__button-container">
			<button type="submit" value="{translate key="common.search"}" class="c-search__button" data-action="clear-adv">
				<span class="c-search__button-text">Search</span>
				<svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
			</button>
		</div>
		<a href="#" class="c-search__link" data-track="click" data-track-category="search results page n150" data-track-action="click link to advanced search page" data-track-label="(not set)" data-test="advance-search-link" tabindex="0">Advance Search</a>
	</div>
	</div>
	<div class="c-facet c-facet--small u-mb-16" data-facet="" data-test="search-filter-box">
	    <h2 class="u-visually-hidden">Filter By:</h2>
	    <div class="c-facet__container">
	        
{assign var=journalArticlesCount value=[]}
{assign var=sectionArticlesCount value=[]}
{assign var=disciplineSubjectIndex value=[]}
{assign var=safeResults value=$results}
{if !is_array($safeResults)}{assign var=safeResults value=[]}{/if}
{foreach from=$safeResults item=result}
    {if is_array($result) || is_object($result)}
        {assign var=publishedArticle value=$result.publishedArticle|default:null}
        {assign var=article value=$result.article|default:null}
        {assign var=issue value=$result.issue|default:null}
        {assign var=issueAvailable value=$result.issueAvailable|default:null}
        {assign var=journal value=$result.journal|default:null}
        {assign var=section value=$result.section|default:null}

        {if $journal && is_object($journal)}
            {assign var=journalName value=$journal->getLocalizedTitle()}
            {if !isset($journalArticlesCount[$journalName])}
                {assign var="journalArticlesCount[$journalName]" value=0}
            {/if}
            {assign var="journalArticlesCount[$journalName]" value=$journalArticlesCount[$journalName]+1}
        {/if}

        {if $section && is_object($section)}
            {assign var=sectionTitle value=$section->getLocalizedTitle()}
            {if !isset($sectionArticlesCount[$sectionTitle])}
                {assign var="sectionArticlesCount[$sectionTitle]" value=0}
            {/if}
            {assign var="sectionArticlesCount[$sectionTitle]" value=$sectionArticlesCount[$sectionTitle]+1}
        {/if}

        {if $article && is_object($article)}
            {if $article->getLocalizedDiscipline()}
                {assign var=discipline value=$article->getLocalizedDiscipline()|escape}
                {if !in_array($discipline, $disciplineSubjectIndex)}
                    {assign var=disciplineSubjectIndex[] value=$discipline}
                {/if}
            {/if}

            {if $article->getLocalizedSubjectClass()}
                {assign var=subjectClass value=$article->getLocalizedSubjectClass()|escape}
                {if !in_array($subjectClass, $disciplineSubjectIndex)}
                    {assign var=disciplineSubjectIndex[] value=$subjectClass}
                {/if}
            {/if}
        {/if}
    {/if}
{/foreach}

    	{if $siteSearch}
        <fieldset class="c-facet__item" data-test="field-set-journals">
            <legend>
                <span id="journal-legend" class="c-facet__label">{translate key="search.withinJournal"} Journal</span>
                <span class="grade-c-hide u-js-show">
                    <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="journal-legend" data-facet-expander="" data-facet-target="#journal-target" data-track="click" data-track-action="journal filter" data-track-label="button" aria-expanded="false">
                        <span class="c-facet__ellipsis">All</span>
                        <svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                    </button>
                </span>
            </legend>
            <span class="u-visually-hidden">Check one or more journals to show results from those journals only.</span>
            <div id="journal-target" data-test="journal-target" data-facet-analytics="journal" class="c-facet-expander u-js-hide" hidden="">
                <div class="c-facet-expander__button-container">
                    <button class="c-facet__submit">Apply filters</button>
                    <button class="c-facet-expander__clear-selection">Clear selection</button>
                </div>
                <ul class="c-facet-expander__list">    
                    <li class="c-facet-expander__list-item">
                        <input name="journal" id="journal-all" value="all" data-action="submit" type="checkbox" checked="checked">
                        <label class="c-facet-expander__link" for="journal-all">
                            <span>{html_options options=$journalOptions selected=$searchJournal}</span>
                        </label>
                    </li>
                </ul>
                <p class="u-mb-0 u-mt-16">
                    <a href="{$baseUrl}/search/search?query={if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{/if}" data-track="click" data-track-category="search results page n150" data-track-action="click choose more link in journal filter" data-track-label="(not set)" class="c-facet-expander__link c-facet-expander__link--underline" data-test="advance-search-link-journals">Choose more<span class="u-visually-hidden"> journals</span>
                    </a>
                </p>
            </div>
        </fieldset>
    	{else}
    	<fieldset class="c-facet__item u-pa-0" data-test="field-set-journals">
    	    <legend>
    	        <span class="c-facet__label" id="journal-legend">Journal</span>
    	        <span class="grade-c-hide u-js-show">
    	            <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="journal-legend" data-facet-expander="" data-facet-target="#journal-target" data-track="click" data-track-action="journal filter" data-track-label="button" aria-expanded="false">
    	                <span class="c-facet__ellipsis">All</span>
    	                <svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
    	            </button>
    	            </span>
    	       </span>
    	   </legend>
    	   <span class="u-visually-hidden">Check one or more journals to show results from those journals only.</span>
    	   <div id="journal-target" data-test="journal-target" data-facet-analytics="journal" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    {foreach from=$journalArticlesCount key=journalName item=articleCount}
                    <li class="u-hide c-facet-expander__list-item">
                        <input name="journal" id="journal-{$currentUrl|replace:$baseUrl:""|lower}" value="{$currentUrl|replace:$baseUrl:""|lower}" data-action="submit" type="checkbox" checked="checked">
                        <label class="c-facet-expander__link" for="journal-{$currentUrl|replace:$baseUrl:""|lower}">
                            <span>{$journalName} ({$articleCount})</span>
                        </label>
                    </li>
                    {/foreach}
                </ul>
                <p class="u-mb-0 u-mt-16">
                    <a href="{$baseUrl}/{$journalPath|escape}/search/search?query={if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{/if}" data-track="click" data-track-category="search results page" data-track-action="click choose more link in journal filter" data-track-label="(not set)" class="c-facet-expander__link c-facet-expander__link--underline" data-test="advance-search-link-journals">Choose more<span class="u-visually-hidden"> journals</span>
                    </a>
                </p>
            </div>
        </fieldset>
    	{/if}
    	<fieldset class="c-facet__item u-pa-0" data-test="field-set-article-type">
    	    <legend>
    	        <span class="c-facet__label" id="article-legend">Article type</span>
    	        <span class="grade-c-hide u-js-show">
    	            <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="article-legend" data-facet-expander="" data-facet-target="#article-type-target" data-track="click" data-track-action="article type filter" data-track-label="button" aria-expanded="false"><span class="c-facet__ellipsis">All</span><svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
    	            </button>
    	       </span>
            </legend>
            <span class="u-visually-hidden">Check one or more article types to show results from those article types only.</span>
            <div id="article-type-target" data-test="article-type-target" data-facet-analytics="article type" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    {foreach from=$sectionArticlesCount key=sectionTitle item=articleCount}
                    <li class="c-facet-expander__list-item">
                        <input name="article_type" id="article-type-{$sectionTitle|lower}" value="{$sectionTitle|lower}" type="checkbox">
                        <label class="c-facet-expander__link" for="article-type-{$sectionTitle|lower}">
                            <span>{$sectionTitle} ({$articleCount})</span>
                        </label>
                    </li>
                    {/foreach}
                </ul>
            </div>
        </fieldset>
        <fieldset class="c-facet__item u-pa-0" data-test="field-set-subjects">
            <legend>
                <span class="c-facet__label" id="subject-legend">Subject</span>
                <span class="grade-c-hide u-js-show">
                    <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="subject-legend" data-facet-expander="" data-facet-target="#subject-target" data-track="click" data-track-action="subject filter" data-track-label="button" aria-expanded="false"><span class="c-facet__ellipsis">All</span><svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                    </button>
                </span>
            </legend>
            <span class="u-visually-hidden">Check one or more subjects to show results from those subjects only.</span>
            <div id="subject-target" data-test="subject-target" data-facet-analytics="subject" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    {foreach from=$disciplineSubjectIndex item=indexEntry}
                    <li class="c-facet-expander__list-item">
                        <input type="checkbox" name="subject" id="subject-{$indexEntry|lower}" value="{$indexEntry|lower}" data-action="submit">
                        <label class="c-facet-expander__link" for="subject-{$indexEntry|lower}">
                            <span>{$indexEntry}</span>
                        </label>
                    </li>
                    {/foreach}
                </ul>
            </div>
        </fieldset>
        <fieldset class="c-facet__item u-pa-0" data-test="field-set-date">
            <legend>
                <span class="c-facet__label" id="date-legend">Date</span>
                <span class="grade-c-hide u-js-show">
                    <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="date-legend" data-facet-expander="" data-facet-target="#date-target" data-track="click" data-track-action="date filter" data-track-label="button" aria-expanded="false"><span class="c-facet__ellipsis">All</span><svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                    </button>
                </span>
            </legend>
            <span class="u-visually-hidden">Choose a date option to show results from those dates only.</span>
            <div id="date-target" data-test="date-target" data-facet-analytics="date" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-today" value="today" type="radio">
                        <label class="c-facet-expander__link" for="date-range-today">
                            <span>Today</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_7_days" value="last_7_days" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_7_days">
                            <span>Last 7 days</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_30_days" value="last_30_days" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_30_days">
                            <span>Last 30 days</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_year" value="last_year" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_year">
                            <span>Last 12 months</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_2_years" value="last_2_years" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_2_years">
                            <span>Last 2 years</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_5_years" value="last_5_years" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_5_years">
                            <span>Last 5 years</span>
                        </label>
                    </li>
                </ul>
                <p class="u-mb-0 u-mt-16"><a class="c-facet-expander__link c-facet-expander__link--underline" data-test="advance-search-link-date" href="{$baseUrl}/search/search?query={if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{/if}">Custom date range</a></p>
            </div>
        </fieldset>
        <span class="c-facet__clear-all">
            <a href="{if $currentJournal}{$currentJournal->getUrl()|strip_tags|escape}{else}{$baseUrl}{/if}/search/search?query={if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{/if}" data-track="click" data-track-category="search results page n150" data-track-action="click clear all filters" data-track-label="(not set)">Clear all filters</a>
        </span>
    	</div>
    	<fieldset class="c-sort-by u-pa-0" data-sort-by="">
    	    <legend class="c-sort-by__heading">Sort by:</legend>
    	    <div class="c-sort-by__input-container">
    	        <input id="sort-by-relevance" class="c-sort-by__input" name="order" value="relevance" type="radio" checked="" data-test="order-relevance"><label class="c-sort-by__label" for="sort-by-relevance">Relevance</label>
    	    </div>
    	    <div class="c-sort-by__input-container">
    	        <input id="sort-by-date_desc" class="c-sort-by__input" name="order" value="date_desc" type="radio" checked="" data-test="order-date_desc"><label class="c-sort-by__label" for="sort-by-date_desc">Date Published (newest to oldest)</label>
    	    </div>
    	    <div class="c-sort-by__input-container">
    	        <input id="sort-by-date_asc" class="c-sort-by__input" name="order" value="date_asc" type="radio" checked="" data-test="order-date_asc"><label class="c-sort-by__label" for="sort-by-date_asc">Date Published (oldest to newest)</label>
    	    </div>
    	    <div class="c-sort-by__input-container">
    	        <input id="results-only-access-checkbox" class="c-sort-by__input" type="checkbox" checked="checked" aria-describedby="preview-tooltip"><span class="tooltip" id="preview-tooltip" role="tooltip">
					Content is preview-only when you or your institution have not yet subscribed to it.
					<br>
					<br>
					By making our abstracts and previews universally accessible we help you purchase only the content that is relevant to you.
				</span>
    	        <label class="c-sort-by__label" for="results-only-access-checkbox">Include Preview-Only content <img src="//assets.sangia.org/image/classical/lock.png" alt="restricted content" height="13" width="13"></label>
            </div>
    	</fieldset>
	</div>

	<div class="c-list-header">
	    <div class="u-mb-0"><span data-test="results-data" class="u-display-flex"><span>Showing {if $results && is_object($results)}{page_info iterator=$results}{/if} results.</span></span>
        </div>
    </div>

