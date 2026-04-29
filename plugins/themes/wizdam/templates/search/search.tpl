{**
 * templates/search/search.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A unified search interface.
 *}
{strip}
{assign var="pageTitle" value="navigation.search"}
{include file="common/header-SA07.tpl"}
{/strip}

<div class="sub-search__meta">
	<div class="search-meta" role="main">
		<div class="result-meta-information">
			<p class="result-count-message">Showing <strong>{page_info iterator=$results} results.</strong></p>
			<div class="result-filter">Within
				<span class="facet-constraint-group facet-constraint-group-type"><a class="facet-link facet-link-type__journal" href="{url page="search" op="titles"}">{if $query}{$query|strip_unsafe_html}{else}{$subject|strip_unsafe_html}{/if}</a></span>
			</div>
		</div>
	</div>
</div>

{call_hook name="Templates::Search::SearchResults::PreResults"}

{if $currentJournal}
	{assign var=numCols value=3}
{else}
	{assign var=numCols value=4}
{/if}

<div id="results" class="search">
	<div id="result-list" class="sub-search__result-list">
	{iterate from=results item=result}
		{assign var=publishedArticle value=$result.publishedArticle}
		{assign var=article value=$result.article}
		{assign var=issue value=$result.issue}
		{assign var=issueAvailable value=$result.issueAvailable}
		{assign var=journal value=$result.journal}
		{assign var=section value=$result.section}

    {if $publishedArticle->getGalleys()}

	<div class="result-item item-0 type-journal">

        {if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN }
        <span class="no-access-message tocMenuArticle result-type">
        	<img class="content-type u-font-sans" src="//www.assets.sangia.org/image/classical/lock.png" alt="PDF file Get Access" title="PDF file Get Access">
        	<h7 class="content-type li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{translate key="rt.metadata.pkp.peerReviewed"}{/if}</span>{/if}</h7>
        </span>
        {else}
        <span class="tocMenuArticle result-type">
            <h7 class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{translate key="rt.metadata.pkp.peerReviewed"}{/if}</span>{/if}</h7>
            <div class="ArtType">Open Access</div>
        </span>
    	{/if}

		<h4 class="u-font-sans"><a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></h4>

		
		<div class="meta contributors journal-contributors">
		{foreach from=$article->getAuthors() item=authorItem name=authorList}
			{$authorItem->getFullName()|escape}{if !$smarty.foreach.authorList.last},{/if}
		{/foreach}
		</div>

		
		{if !$currentJournal}
			<div class="jika-home" valign="top"><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a></div>
		{/if}

		<div>in <a href="{url journal=$journal->getPath() page="issue" op="view" path=$issue->getBestIssueId($journal)}">{$issue->getIssueIdentification()|escape}</a></div>
		
		{if $article->getLocalizedAbstract()}
		<div class="snippet abstract authorDetails"><p class="abs">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</p></div>
		{/if}

		<p class="format" valign="top"><strong>Available Formats:</strong>
		{if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN|| $issueAvailable}
				{assign var=hasAccess value=1}
			{else}
				{assign var=hasAccess value=0}
			{/if}
			{if $publishedArticle->getLocalizedAbstract() != ""}
				{assign var=hasAbstract value=1}
			{else}
				{assign var=hasAbstract value=0}
			{/if}
			{if !$hasAccess || $hasAbstract}
				<span data-format="HTML"><a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" class="file">
					{if !$hasAbstract}
						{translate key="article.details"}
					{else}
						{translate key="article.abstract"}
					{/if}
				<span class="fileView">{$publishedArticle->getViews()} views</span></a></span>
			{/if}
			{if $hasAccess}
				{foreach from=$publishedArticle->getLocalizedGalleys() item=galley name=galleyList}
					&nbsp;<span data-format="PDF|eHTML"><a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">{$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()}) <span class="fileView">{$galley->getViews()} views</span></span></a></span>
				{/foreach}
			{/if}
		</p>

		{if !$currentJournal}
		<div class="url" align="right">
			{if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN|| $issueAvailable}
				{assign var=hasAccess value=1}
			{else}
				{assign var=hasAccess value=0}
			{/if}
			{if $publishedArticle->getLocalizedAbstract() != ""}
				{assign var=hasAbstract value=1}
			{else}
				{assign var=hasAbstract value=0}
			{/if}
			{if !$hasAccess || $hasAbstract}
				<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" class="file">
					{if !$hasAbstract}
						{translate key="article.details"}
					{else}
						{translate key="article.abstract"}
					{/if}
				</a>
			{/if}
			{if $hasAccess}
				{foreach from=$publishedArticle->getLocalizedGalleys() item=galley name=galleyList}
					&nbsp;<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">{$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()}) <span class="fileView">{$galley->getViews()} views</span></span></a>
				{/foreach}
			{/if}
			{call_hook name="Templates::Search::SearchResults::AdditionalArticleLinks" articleId=$publishedArticle->getId()}
		</div>
		{/if}
		
		{call_hook name="Templates::Search::SearchResults::AdditionalArticleInfo" articleId=$publishedArticle->getId() numCols=$numCols|escape}
	</div>

	{else}

	<div class="result-item item-0 type-article">
	    <span class="no-access-message tocMenuArticle result-type">
	        <img class="content-type u-font-sans" src="//www.assets.sangia.org/image/classical/lock.png" alt="PDF file Not Available" title="PDF file Not Available">
	        <h7 class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{translate key="rt.metadata.pkp.peerReviewed"}{/if}</span>{/if}</h7>
	    </span>

		<h4 class="u-font-sans"><a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></h4>

		
		<div class="meta contributors journal-contributors">
		{foreach from=$article->getAuthors() item=authorItem name=authorList}
			{$authorItem->getFullName()|escape}{if !$smarty.foreach.authorList.last},{/if}
		{/foreach}
		</div>

		
		{if !$currentJournal}
			<div class="jika-home" valign="top"><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a></div>
		{/if}

		<div>in <a href="{url journal=$journal->getPath() page="issue" op="view" path=$issue->getBestIssueId($journal)}">{$issue->getIssueIdentification()|escape}</a></div>
		
		{if $article->getLocalizedAbstract()}
		<div class="snippet abstract authorDetails"><p class="abs">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</p></div>
		{/if}

		<p class="format" valign="top"><strong>Available Formats:</strong>
		{if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN|| $issueAvailable}
				{assign var=hasAccess value=1}
			{else}
				{assign var=hasAccess value=0}
			{/if}
			{if $publishedArticle->getLocalizedAbstract() != ""}
				{assign var=hasAbstract value=1}
			{else}
				{assign var=hasAbstract value=0}
			{/if}
			{if !$hasAccess || $hasAbstract}
				<span data-format="HTML"><a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" class="file">
					{if !$hasAbstract}
						{translate key="article.details"}
					{else}
						{translate key="article.abstract"}
					{/if}
				<span class="fileView">{$publishedArticle->getViews()} views</span></a></span>
			{/if}
			{if $hasAccess}
				{foreach from=$publishedArticle->getLocalizedGalleys() item=galley name=galleyList}
					&nbsp;<span data-format="PDF|eHTML"><a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">{$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a></span>
				{/foreach}
			{/if}
		</p>

		{if !$currentJournal}
		<div class="url" align="right">
			{if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN|| $issueAvailable}
				{assign var=hasAccess value=1}
			{else}
				{assign var=hasAccess value=0}
			{/if}
			{if $publishedArticle->getLocalizedAbstract() != ""}
				{assign var=hasAbstract value=1}
			{else}
				{assign var=hasAbstract value=0}
			{/if}
			{if !$hasAccess || $hasAbstract}
				<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" class="file">
					{if !$hasAbstract}
						{translate key="article.details"}
					{else}
						{translate key="article.abstract"}
					{/if}
				</a>
			{/if}
			{if $hasAccess}
				{foreach from=$publishedArticle->getLocalizedGalleys() item=galley name=galleyList}
					&nbsp;<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">{$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a>
				{/foreach}
			{/if}
			{call_hook name="Templates::Search::SearchResults::AdditionalArticleLinks" articleId=$publishedArticle->getId()}
		</div>
		{/if}
		
		{call_hook name="Templates::Search::SearchResults::AdditionalArticleInfo" articleId=$publishedArticle->getId() numCols=$numCols|escape}
	</div>

	{/if}

	{/iterate}

	{if $results->wasEmpty()}
	<div class="paging">
		<div class="page-listing">
			<div class="side-l" colspan="{$numCols|escape}" class="nodata">
				{if $error}
					{$error|escape}
				{else}
				<div class="cms-common">
					<h2>Sorry – we couldn’t find what you are looking for.</h2>
					<p class="intro--paragraph">Make sure that all words are spelled correctly</p>
		{capture assign="syntaxInstructions"}{call_hook name="Templates::Search::SearchResults::SyntaxInstructions"}{/capture}
		{if empty($syntaxInstructions)}
			{translate key="search.syntaxInstructions"}
		{else}
			{* Must be properly escaped in the controller as we potentially get HTML here! *}
			{$syntaxInstructions}
		{/if}
				</div>
				{/if}
			</div>
		</div>
	</div>	
	{else}
	<div class="colspan u-mb-0" id="colspan">	    
	    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	        <div class="c-pagination">View {page_info iterator=$results}</div>
        </section>
	    <section class="u-display-flex u-justify-content-center">
	        <div class="c-pagination">{page_links anchor="results" iterator=$results name="search" query=$query searchJournal=$searchJournal authors=$authors title=$title abstract=$abstract galleyFullText=$galleyFullText suppFiles=$suppFiles discipline=$discipline subject=$subject type=$type coverage=$coverage indexTerms=$indexTerms dateFromMonth=$dateFromMonth dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateToMonth=$dateToMonth dateToDay=$dateToDay dateToYear=$dateToYear orderBy=$orderBy orderDir=$orderDir}</div>
	    </section>
	</div>
	<div class="u-hide paging">
		<div class="functions-bar functions-bar-bottom page-listing">
			<div class="side-l" {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$results}</div>
			<div class="side-r" colspan="2" align="right">{page_links anchor="results" iterator=$results name="search" query=$query searchJournal=$searchJournal authors=$authors title=$title abstract=$abstract galleyFullText=$galleyFullText suppFiles=$suppFiles discipline=$discipline subject=$subject type=$type coverage=$coverage indexTerms=$indexTerms dateFromMonth=$dateFromMonth dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateToMonth=$dateToMonth dateToDay=$dateToDay dateToYear=$dateToYear orderBy=$orderBy orderDir=$orderDir}</div>

			<div class="u-hide pagination">
				<span class="prev" title="previous">previous</span>
				<span class="page-nr">Page
					<span class="field">
						<input class="page-number" type="text" name="page" value="1">
						<input type="hidden" name="query" value="vote">
						<input type="hidden" name="facet-type" value="type__journal">
						<span class="pagination-error error-message hidden"><strong><span class="invalid-page-number"></span></strong>
							is not a valid page number. Please enter a number between <strong></strong> and <strong></strong>.
						</span>
					</span>
						of
					<span class="number-of-pages">1</span>
				</span>
				<span class="next" title="next"></span>
			</div>
		</div>
	</div>	
	{/if}	

        	<div class="u-hide insttruct">
        		<details class="details-44861495">
        			<summary class="details-summary-2566262091">
                    <span class="details-summary-label-617948308">Seacrh Tips (click for more)</span><svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        			</summary>
                    {capture assign="syntaxInstructions"}{call_hook name="Templates::Search::SearchResults::SyntaxInstructions"}{/capture}
            		{if empty($syntaxInstructions)}
            			{translate key="search.syntaxInstructions"}
            		{else}
            			{* Must be properly escaped in the controller as we potentially get HTML here! *}
            			{$syntaxInstructions}
            		{/if}
        		</details>
    		</div>
    		
    	</div>
    </div>
</form>

{include file="common/footer.tpl"}

