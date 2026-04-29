{**
 * templates/article/dublincore.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dublin Core metadata elements for articles.
 *
 *}
<link rel="schema.dc" href="http://purl.org/dc/elements/1.1/" />

	<meta name="dc.type" content="Text.Serial.Journal" />
	<meta name="dc.source" content="{$currentJournal->getLocalizedTitle()|strip_tags|escape}"/>
{if $currentJournal->getSetting('onlineIssn')}{assign var="issn" value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal->getSetting('printIssn')}{assign var="issn" value=$currentJournal->getSetting('printIssn')}
{elseif $currentJournal->getSetting('issn')}{assign var="issn" value=$currentJournal->getSetting('issn')}
{/if}
{if $issn}
	<meta name="dc.source.ISSN" content="{$issn|strip_tags|escape}" />
{/if}
    <meta name="citation_publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sekolah Tinggi Ilmu Pertanian Wuna{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')|escape}{else}{$currentJournal->getSetting('publisherInstitution')|escape}{/if}" />
	<meta name="dc.source.URI" content="{$currentJournal->getUrl()|strip_tags|escape}" />
	
	<meta name="dc.title" content="{$article->getLocalizedTitle()|strip_tags|escape}" />	
{foreach from=$article->getTitle(null) item=alternate key=metaLocale}
	{if $alternate != $article->getLocalizedTitle()}
		<meta name="dc.title.alternative" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$alternate|strip_tags|escape}" />
	{/if}
{/foreach}

{if $article->getAbstract(null)}{foreach from=$article->getAbstract(null) key=metaLocale item=metaValue}
	<meta name="dc.description" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}" />
{/foreach}{/if}

{if $article->getSubject(null)}{foreach from=$article->getSubject(null) key=metaLocale item=metaValue}
	{foreach from=$metaValue|explode:"; " item=dcSubject}
		{if $dcSubject}
			<meta name="dc.subject" xml:lang="{$metaLocale|String_substr:0:2|strip_tags|escape}" content="{$dcSubject|strip_tags|escape}"/>
		{/if}
	{/foreach}
{/foreach}{/if}

{foreach name="authors" from=$article->getAuthors() item=author}
	<meta name="dc.creator.personalname" content="{$author->getFullName()|escape}" />
{/foreach}

{* DC.Contributor.PersonalName (reviewer) *}
{if $article->getSponsor(null)}{foreach from=$article->getSponsor(null) key=metaLocale item=metaValue}
	<meta name="dc.contributor.sponsor" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}" />
{/foreach}{/if}
{if $article->getCoverageSample(null)}{foreach from=$article->getCoverageSample(null) key=metaLocale item=metaValue}
	<meta name="dc.coverage" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}" />
{/foreach}{/if}
{if $article->getCoverageGeo(null)}{foreach from=$article->getCoverageGeo(null) key=metaLocale item=metaValue}
	<meta name="dc.coverage.spatial" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}" />
{/foreach}{/if}
{if $article->getCoverageChron(null)}{foreach from=$article->getCoverageChron(null) key=metaLocale item=metaValue}
	<meta name="dc.coverage.temporal" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}" />
{/foreach}{/if}

	<meta name="dc.type.articleType" content="{$article->getSectionTitle()|strip_tags|escape}" />
{if $issue}
	<meta name="dc.source.volume" content="{$issue->getVolume()|strip_tags|escape}" />
	{if $issue->getNumber() ne ""}
	<meta name="dc.source.issue" content="{$issue->getNumber()|strip_tags|escape}" />
	{/if}
{/if}

	<meta name="dc.identifier" content="{$article->getBestArticleId($currentJournal)|escape}" />
{if $article->getPages()}
	<meta name="dc.identifier.pageNumber" content="{$article->getPages()|escape}" />
{else}	
	<meta name="dc.identifier.pageNumber" content="{$article->getID()|escape}" />
{/if}

{if $issue && $issue->getOpenAccessDate()}
	<meta name="dc.date.available" scheme="ISO8601" content="{$issue->getOpenAccessDate()|date_format:"%Y-%m-%d"|escape}" />
{/if}
{if is_a($article, 'PublishedArticle') && $article->getDatePublished()}
	<meta name="dc.date.created" scheme="ISO8601" content="{$article->getDatePublished()|date_format:"%Y-%m-%d"|escape}" />
{/if}
{* DC.Date.dateAccepted (editor submission DAO) *}
{* DC.Date.dateCopyrighted *}
{* DC.Date.dateReveiwed (revised file DAO) *}
	<meta name="dc.date.dateSubmitted" scheme="ISO8601" content="{$article->getDateSubmitted()|date_format:"%Y-%m-%d"|escape}" />
{if $issue && $issue->getDatePublished()}
	<meta name="dc.date.issued" scheme="ISO8601" content="{$issue->getDatePublished()|date_format:"%Y-%m-%d"|escape}" />
{/if}
	<meta name="dc.date.modified" scheme="ISO8601" content="{$article->getDateStatusModified()|date_format:"%Y-%m-%d"|escape}" />

{if is_a($article, 'PublishedArticle')}{foreach from=$article->getGalleys() item=dcGalley}
	<meta name="dc.format" scheme="IMT" content="{$dcGalley->getFileType()|escape}" />
{/foreach}{/if}

	<meta name="dc.language" scheme="ISO639-1" content="{$article->getLanguage()|strip_tags|escape}" />
	
{foreach from=$pubIdPlugins item=pubIdPlugin}
	{if $issue->getPublished()}
		{assign var=pubId value=$pubIdPlugin->getPubId($pubObject)}
	{else}
		{assign var=pubId value=$pubIdPlugin->getPubId($pubObject, true)}{* Preview rather than assign a pubId *}
	{/if}
	{if $pubId}
		<meta name="dc.identifier.{$pubIdPlugin->getPubIdDisplayType()|escape}" content="{$pubId|escape}" />
	{/if}
{/foreach}
	<meta name="dc.identifier.URI" content="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|escape}" />

{* DC.Publisher (publishing institution) *}
{* DC.Publisher.Address (email addr) *}
	<meta name="dc.rights" content="{translate key="submission.copyrightStatement" copyrightHolder=$article->getLocalizedCopyrightHolder()|escape copyrightYear=$article->getCopyrightYear()|escape}" />
	<meta name="dc.rights" content="{$article->getLicenseURL()|escape}" />
{* DC.Rights.accessRights *}
