{**
 * templates/article/googlescholar.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Metadata elements for articles based on preferred types for Google Scholar
 *
 *}

{if $currentJournal->getSetting('onlineIssn')}{assign var="issn" value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal->getSetting('printIssn')}{assign var="issn" value=$currentJournal->getSetting('printIssn')}
{elseif $currentJournal->getSetting('issn')}{assign var="issn" value=$currentJournal->getSetting('issn')}
{/if}
{if $issn}
	<meta name="citation_issn" content="{$issn|strip_tags|escape}"/>
{/if}
    <meta name="citation_title" content="{$article->getLocalizedTitle()|strip_tags|escape}"/>
	
{foreach from=$article->getTitle(null) item=alternate key=metaLocale}
{if $alternate != $article->getLocalizedTitle()}
	<meta name="citation_title_alternative" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$alternate|strip_tags|escape}"/>
{/if}
{/foreach}
	
{foreach name="authors" from=$article->getAuthors() item=author}
    <meta name="citation_author" content="{$author->getFullName()|escape}"/>
{if $author->getLocalizedAffiliation() != ""}
    <meta name="citation_author_institution" content="{$author->getLocalizedAffiliation()|strip_tags|escape}"/>
{/if}
{/foreach}
	<meta name="citation_description" content="{$article->getLocalizedAbstract()|strip_tags|escape}" />
{if $article->getLocalizedSubject()}
	<meta name="citation_keywords" content="{$article->getLocalizedSubject()|strip_tags|escape}" />
{/if}
	
{**
 * Google Scholar date: Use article publication date, falling back on issue
 * year and issue publication date in sequence. Bug #6480.
 *}
{if is_a($article, 'PublishedArticle') && $article->getDatePublished()}
	<meta name="citation_date" content="{$article->getDatePublished()|date_format:"%Y/%m/%d"|escape}"/>
{elseif $issue && $issue->getYear()}
	<meta name="citation_date" content="{$issue->getYear()|escape}"/>
{elseif $issue && $issue->getDatePublished()}
	<meta name="citation_date" content="{$issue->getDatePublished()|date_format:"%Y/%m/%d"|escape}"/>
{/if}

{if $issue}
	<meta name="citation_volume" content="{$issue->getVolume()|strip_tags|escape}"/>
	{if $issue->getNumber() ne ""}
	<meta name="citation_issue" content="{$issue->getNumber()|strip_tags|escape}"/>
	{/if}
{/if}

{if $article->getPages()}
	{if $article->getStartingPage()}
		<meta name="citation_firstpage" content="{$article->getStartingPage()|escape}"/>
	{/if}
	{if $article->getEndingPage()}
		<meta name="citation_lastpage" content="{$article->getEndingPage()|escape}"/>
	{/if}
{else}
    <meta name="citation_firstpage" content="{$article->getID()|escape}"/>
{/if}
{foreach from=$pubIdPlugins item=pubIdPlugin}
	{if $issue->getPublished()}
		{assign var=pubId value=$pubIdPlugin->getPubId($pubObject)}
	{else}
		{assign var=pubId value=$pubIdPlugin->getPubId($pubObject, true)}{* Preview rather than assign a pubId *}
	{/if}
	{if $pubId}
		<meta name="citation_{$pubIdPlugin->getPubIdDisplayType()|escape|lower}" content="{$pubId|escape}"/>
	{/if}
{/foreach}

{if $article->getLanguage()}
	<meta name="citation_language" content="{$article->getLanguage()|strip_tags|escape}"/>
{/if}

{if $article->getSubject(null)}{foreach from=$article->getSubject(null) key=metaLocale item=metaValue}
	{foreach from=$metaValue|explode:"; " item=gsKeyword}
		{if $gsKeyword}
			<meta name="citation_keywords" xml:lang="{$metaLocale|String_substr:0:2|strip_tags|escape}" content="{$gsKeyword|strip_tags|escape}"/>
		{/if}
	{/foreach}
{/foreach}
{/if}

	<meta name="citation_abstract_html_url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}"/>
	
{if is_a($article, 'PublishedArticle')}
	{foreach from=$article->getGalleys() item=gs_galley}
		{if $gs_galley->getFileType()=="application/pdf"}
			<meta name="citation_pdf_url" content="{url page="article" op="download" path=$article->getBestArticleId($currentJournal)|to_array:$gs_galley->getBestGalleyId($currentJournal)}"/>
		{else}
			<meta name="citation_fulltext_html_url" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$gs_galley->getBestGalleyId($currentJournal)}"/>
		{/if}
	{/foreach}
{/if}
