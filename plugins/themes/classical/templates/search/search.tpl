{**
 * templates/search/search.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A unified search interface.
 *}
{strip}
{assign var="pageTitle" value="navigation.search"}
{include file="common/header-search.tpl"}
{/strip}
	
<div class="header">
	<h1 id="number-of-search-results-and-search-terms">
		<span class="number-of-search-results"><strong>{page_info iterator=$results}</strong> </span>{if $query || $subject}<span class="results-search-terms">Result(s) for <span class="terms" value="{if $query}{$query|strip_unsafe_html}{else}{$subject|strip_unsafe_html}{/if}"><strong>'{if $query}{$query|strip_unsafe_html}{else}{$subject|strip_unsafe_html}{/if}'</strong></span></span>{/if}
	</h1>
	<div id="toolbar">
		<div id="tools">
			<div>
				<a id="tool-rss" href="{url page="search" op="search?subscribe&query=$query"}" aria-describedby="rss-tooltip" >
					<img class="icon" src="//assets.sangia.org/image/classical/rss.png" alt="Subscribe">
					<span class="tooltip" id="rss-tooltip" role="tooltip">Subscribe to this page via RSS</span>
				</a>
				<a id="tool-download" href="{url page="search" op="search?download&query=$query"}">
					<img src="//assets.sangia.org/image/classical/download.png" alt="Download" aria-describedby="download-tooltip">
					<span class="tooltip" id="download-tooltip" role="tooltip">Download search results (CSV)<br>Your download will be capped at 1000 items</span>
				</a>
			</div>
		</div>
	</div>
</div>

<div class="functions-bar functions-bar-top">
	<div id="sort-results" class="sorting">
		<span>Sort By</span>
		<a class="btn relevance" href="{url page="search" op="search?query=$query&sortOrder=relevance"}">Relevance</a>
		<a class="btn newest" href="{url page="search" op="search?query=$query&sortOrder=newestFirst"}">Newest First</a>
		<a class="btn oldest" href="{url page="search" op="search?query=$query&sortOrder=oldestFirst"}">Oldest First</a>
	</div>
	{if $results && $results->getPageCount() > 1}
	<div class="pagination" colspan="2" align="right">
	    <span class="prev disabled" title="previous"><span class="page_links prevpage_links u-hide">Prev</span><img src="//assets.sangia.org/image/classical/arrow-left-inactive.png" alt="previous disabled"></span>
	    <span class="page-nr">{page_links anchor="results" iterator=$results name="search" query=$query JournalID=$searchJournal}</span>
	    <span class="next" title="next"><span class="page_links nextpage_links u-hide">Next</span><img src="//assets.sangia.org/image/classical/arrow-right.png" alt="next"></span>
	</div>
	{/if}
</div>

{call_hook name="Templates::Search::SearchResults::PreResults"}

{if $currentJournal}
	{assign var=numCols value=3}
{else}
	{assign var=numCols value=4}
{/if}

<ol id="results-list" class="content-item-list">
	{iterate from=results item=result}
	{assign var=publishedArticle value=$result.publishedArticle}
	{assign var=article value=$result.article}
	{assign var=issue value=$result.issue}
	{assign var=issueAvailable value=$result.issueAvailable}
	{assign var=journal value=$result.journal}
	{assign var=section value=$result.section}

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
		
	{if $publishedArticle->getGalleys()}
	
	<li {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}class="no-access version1"{elseif $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}class="access version1"{/if}>
	
	{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
	
		<p class="no-access-message">
			<img src="//assets.sangia.org/image/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
		</p>
		{foreach name=sections from=$publishedArticles item=section key=sectionId}
		{foreach from=$section.articles item=article}
		{if $section.title}
		<p class="content-type">{$section.title|escape}</p>
		{/if} {/foreach}{* articles *}{/foreach}{* sections *}
		
		{if $section && $section->getLocalizedIdentifyType()}
		<p class="content-type">{$section->getLocalizedIdentifyType()|escape}</p>
			{else}
		<p class="content-type">{translate key="rt.metadata.pkp.peerReviewed"}</p>
		{/if}

		<div class="lozenges"></div>
	
	{else}

		{foreach name=sections from=$publishedArticles item=section key=sectionId}
		{foreach from=$section.articles item=article}
		{if $section.title}
		<p class="content-type">{$section.title|escape}</p>
		{/if} {/foreach}{* articles *}{/foreach}{* sections *}
		
		{if $section && $section->getLocalizedIdentifyType()}
		<p class="content-type">{$section->getLocalizedIdentifyType()|escape}</p>
			{else}
		<p class="content-type">	
		{translate key="rt.metadata.pkp.peerReviewed"}
		</p>
		{/if}

		<div class="lozenges">
			<div class="open-access">
				<span class="lozenge lozenge--style1">Open Access
				</span>
			</div>
		</div>
		
	{/if}

		<h2><a class="title" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></h2>

		{if $article->getLocalizedAbstract()}
		<div class="snippet">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
		{/if}
		
		<p class="meta">
		    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
            {else}
			<span class="authors">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View articles by: {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName} </span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape|regex_replace:"/([a-z])[a-z]*(\s|$)/":"$1."|trim|regex_replace:"[^A-Z.]":""}</span>{/if}<span class="text surname">{$authorLastName}</span></a>{/foreach}
			</span>
		    {/if}	
			<span>in <a class="enumeration" datetime="{$publishedArticle->getDatePublished()|date_format:"$dateFormatShort"}" href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|truncate:45:"..."|escape}</a> <span class="year enumeration" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</span></span>
		</p>
		
		<div class="actions">
			{foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
			{if $galley->isPdfGalley() || $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			<span class="action" data-format="PDF|eHTML">
				<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getLabel()|escape} <span>({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a>
			</span>&nbsp;
			{/if}
			{/foreach}

			{if !$hasAccess || $hasAbstract}
			<span class="action" data-format="HTML">
				<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" class="file">{if $galley->isHTMLGalley()}View Article{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if}</a> <span class="fileView">{$publishedArticle->getViews()} views</span>
			</span>
			{/if}

		</div>
		{call_hook name="Templates::Search::SearchResults::AdditionalArticleInfo" articleId=$publishedArticle->getId() numCols=$numCols|escape}
	</li>

{else}

	<li {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}{else}class="no-access"{/if}>
	    
	{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
	
		{foreach name=sections from=$publishedArticles item=section key=sectionId}
		{foreach from=$section.articles item=article}
		{if $section.title}
		<p class="content-type">{$section.title|escape}</p>
		{/if} {/foreach}{* articles *}{/foreach}{* sections *}
		
		{if $section && $section->getLocalizedIdentifyType()}
		<p class="content-type">{$section->getLocalizedIdentifyType()|escape}</p>
			{else}
		<p class="content-type">	
		{translate key="rt.metadata.pkp.peerReviewed"}
		</p>
		{/if}

		<div class="lozenges">
			<div class="open-access">
				<span class="lozenge lozenge--style1">Open Access
				</span>
			</div>
		</div>
	
	{else}
	
		<p class="no-access-message">
			<img src="//assets.sangia.org/image/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
		</p>
		{foreach name=sections from=$publishedArticles item=section key=sectionId}
		{foreach from=$section.articles item=article}
		{if $section.title}
		<p class="content-type">{$section.title|escape}</p>
		{/if} {/foreach}{* articles *}{/foreach}{* sections *}
		
		{if $section && $section->getLocalizedIdentifyType()}
		<p class="content-type">{$section->getLocalizedIdentifyType()|escape}</p>
			{else}
		<p class="content-type">{translate key="rt.metadata.pkp.peerReviewed"}</p>
		{/if}

		<div class="lozenges"></div>	
	
	{/if}

		<h2><a class="title" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></h2>

		{if $article->getLocalizedAbstract()}
		<div class="snippet">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
		{/if}
		
		<p class="meta">
		    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
		    {else}
			<span class="authors">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View articles by: {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName} </span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape|regex_replace:"/([a-z])[a-z]*(\s|$)/":"$1."|trim|regex_replace:"[^A-Z.]":""}</span>{/if}<span class="text surname">{$authorLastName}</span></a>{/foreach}
			</span>
			{/if}
			<span>in <a class="enumeration" datetime="{$publishedArticle->getDatePublished()|date_format:"$dateFormatShort"}" href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|truncate:45:"..."|escape}</a> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</span></span>
		</p>

		<div class="actions">
			{foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
			{if $galley->isPdfGalley() && $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			<span class="action" data-format="PDF|eHTML">
				<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getLabel()|escape} <span>({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a>
			</span>
			{/if}
			{/foreach}

			{if !$hasAccess || $hasAbstract}
			<span class="action" data-format="HTML">
				<a href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" class="file">View {if !$hasAbstract}{translate key="article.details"}{else}{translate key="article.abstract"}{/if}</a> <span class="fileView">{$publishedArticle->getViews()} views</span>
			</span>
			{/if}
		</div>
		
		{call_hook name="Templates::Search::SearchResults::AdditionalArticleInfo" articleId=$publishedArticle->getId() numCols=$numCols|escape}
	</li>	
	{/if}
	{/iterate}
</ol>


{if $results->wasEmpty()}
<div id="results">
	<div class="page-listing">
		<div class="side-l" colspan="{$numCols|escape}" class="nodata">
	    	{if $error}
	    		{$error|escape}
	    	{else}
			<div id="no-results-message" class="formatted">
				<h2>
					<strong>Sorry</strong>– we couldn’t find what you are looking for. Why not...
				</h2>
				<ul>
					<li>Make sure that all words are spelled correctly</li>
					<li>Try different keywords</li>
					<li>Try more general keywords</li>
					<li>Include preview-only content</li>
					<li>Remove some/all of your search filters</li>
					<li>Try using a wildcard seach where:</li>
				</ul>
				<p>
					A * matches zero or more non-space characters.
					<br>
					A ? matches exactly one non-space character.
				</p>
			</div>
			<div class="search-tips">
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
		<div id="no-results-remove" class="box-secondary">
            <p>Improve your search results by removing some or all of your search filters.</p>
        </div>
	</div>
</div>	
{else}
<div class="functions-bar functions-bar-bottom">
	<div class="page-listing">
		<div class="side-l" {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$results}</div>
		{if $results && $results->getPageCount() > 1}
		<div class="pagination" colspan="2" align="right">
		    <span class="prev disabled" title="previous"><span class="page_links prevpage_links u-hide">Prev</span><img src="//assets.sangia.org/image/classical/arrow-left-inactive.png" alt="previous disabled"></span>
		    <span class="page-nr">{page_links anchor="results" iterator=$results name="search" query=$query searchJournal=$searchJournal}</span>
		    <span class="next" title="next"><span class="page_links nextpage_links u-hide">Next</span><img src="//assets.sangia.org/image/classical/arrow-right.png" alt="next"></span>
		</div>
		{/if}
	</div>
</div>	
{/if}

{include file="common/footer-home.tpl"}

