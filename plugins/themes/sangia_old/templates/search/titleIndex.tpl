{**
 * templates/search/titleIndex.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display published articles by title
 *
 *}
{strip}
{assign var=pageTitle value="navigation.search"}
{include file="common/header-search.tpl"}
{/strip}
<div class="header">
    <div id="toolbar">
        <div id="tools">
            <div>
                <a id="tool-rss" href="/search.rss?facet-creator=%22Academician+Bohum%C3%ADr+Rosick%C3%BD%22" aria-describedby="rss-tooltip">
                    <img class="icon" src="//www.stipwunaraha.ac.id/static/images/classical/rss.png" alt="Subscribe">
                    <span class="tooltip" id="rss-tooltip" role="tooltip">Subscribe to this page via RSS</span>
                </a>
                <a id="tool-download" href="/search/csv?facet-creator=%22Academician+Bohum%C3%ADr+Rosick%C3%BD%22">
                    <img src="//www.stipwunaraha.ac.id/static/images/classical/download.png" alt="Download" aria-describedby="download-tooltip">
                    <span class="tooltip" id="download-tooltip" role="tooltip">Download search results (CSV)</span>
                </a>
            </div>
        </div>
    </div>    
    <h1 id="number-of-search-results-and-search-terms"><strong>{page_info iterator=$results}</strong> Result(s) </h1>
    <p class="facet-constraint-message">within
        <a class="facet-link" href="{url page="index"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}
            <img class="remove-hover" src="//www.stipwunaraha.ac.id/static/images/classical/remove-hover.png" alt="Remove this filter">
        </a>
    </p>
</div>

{if $currentJournal}
<div id="only-searching-within" class="box-secondary">
    <a href="/">
        <noscript>
            {if $coverPagePath}
            <img class="cover journal-cover__image cover-lazy img-default" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" alt="Stipwunaraha Media & Publishing Group" />
            {else}
            <img class="cover journal-cover__image cover-lazy img-default" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Stipwunaraha Media & Publishing Group" /> {/if}
        </noscript>

        {if $coverPagePath}
        <img class="cover journal-cover__image cover-lazy img-default" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}/homepageImage_en_US.jpg" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" alt="Stipwunaraha Media & Publishing Group" />
        {else}
        <img class="cover journal-cover__image cover-lazy img-default" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" aria-describedby="cover-tooltip" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}/homepageImage_en_US.jpg" alt="Stipwunaraha Media & Publishing Group" />
        {call_hook name="Templates::Article::Article::ArticleCoverImage"}
        {/if}
        <span class="tooltip" id="cover-tooltip" role="tooltip">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</span>
    </a>
    <div class="text">
        <p class="message">You are now only within the Journal</p>
        <p class="title">
            <a href="{url page="index"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a>
        </p>
        <div class="stop">
            <a class="facet-link" href="{url page="index"}">STOP within this Journal</a>
        </div>
    </div>
</div>
{/if}

<div class="functions-bar functions-bar-top">
    <div id="sort-results" class="sorting">
        <span>Sort By</span>
        <a class="btn selected" href="#">Relevance</a>
        <a class="btn selected" href="#">Newest First</a>
        <a class="btn" href="#">Oldest First</a>
    </div>
     <form class="pagination" method="post">
        <span class="prev" title="previous">
            <img src="//www.stipwunaraha.ac.id/static/images/classical/arrow-left-inactive.png" alt="previous disabled">
        </span>
        <span class="page-nr"><span class="page_links">Page</span> {page_links anchor="results" iterator=$results name="search" query=$query searchJournal=$searchJournal authors=$authors title=$title abstract=$abstract galleyFullText=$galleyFullText suppFiles=$suppFiles discipline=$discipline subject=$subject type=$type coverage=$coverage indexTerms=$indexTerms dateFromMonth=$dateFromMonth dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateToMonth=$dateToMonth dateToDay=$dateToDay dateToYear=$dateToYear orderBy=$orderBy orderDir=$orderDir}</span>
        <span class="next" title="next">
            <img src="//www.stipwunaraha.ac.id/static/images/classical/arrow-right-inactive.png" alt="next disabled">
        </span>
    </form>
</div>

<ol id="results-list" class="content-item-list">
    {iterate from=results item=result}

        {assign var=publishedArticle value=$result.publishedArticle}
        {assign var=article value=$result.article}
        {assign var=issue value=$result.issue}
        {assign var=issueAvailable value=$result.issueAvailable}
        {assign var=journal value=$result.journal}
        {assign var=sectionId value=$article->getSectionId()}

    {if $publishedArticle->getGalleys()}
    <li>
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

        <div class="lozenges">
            <div class="open-access">
                <span class="lozenge lozenge--style1">Open Access</span>
            </div>
        </div>

        <h2>
            <a class="title" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
        </h2>

        {if $article->getLocalizedAbstract()}
        <p class="snippet">{$article->getLocalizedAbstract()|truncate:170:"..."|strip_unsafe_html|nl2br}</p>
        {/if}

        <p class="meta">
            <span class="authors">
            {assign var=count value=0}
            {assign var=authors value=$article->getAuthors()}
            {foreach from=$authors item=author name=authors key=i}
            {assign var=authorCount value=$authors|@count}
            {assign var=fullname value=$author->getFullName()}
            {assign var="pageTitle" value="search.authorIndex"}
            {assign var=authorFirstName value=$author->getFirstName()}
            {assign var=authorMiddleName value=$author->getMiddleName()}
            {assign var=authorLastName value=$author->getLastName()}
            {assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
            {assign var=authorCountry value=$author->getCountry()}
            {assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}
            {assign var=authorName value="$authorName $authorMiddleName"}{/if}
            {assign var="contact" value=$author->getData('primaryContact')}
            {assign var=count value=$count+1} <a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View articles by {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $fullname}{$fullname|escape}</a>{else}{/if}{if $i==$authorCount-2}, {elseif $i<$authorCount-1}, {elseif $i>$authorCount-3} {/if}
            {/foreach}
            </span>
            in <span class="enumeration"><a title="Go to {$journal->getLocalizedTitle()|escape}" href="{url journal=$journal->getPath()}"><em>{$currentJournal->getLocalizedTitle()|truncate:45:"..."|strip_tags|escape}</a></span> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</em></span>
        </p>

        <!--
        <div class="li-list ul-sans hide ul-journalName">Volume {$issue->getVolume()|strip_tags|escape}, Issue ({$issue->getNumber()|escape}), Page: {$article->getPages()|escape}, {assign var="doi" value=$article->getStoredPubId('doi')}{if $article->getPubId('doi')}<a href="http://dx.doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>{/if}</div> -->

        <div class="actions">
        {foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
        {if $galley->isPdfGalley()}{if $issueAvailable}
            <span class="action">
                <a title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url journal=$journal->getPath() page="article" op="download" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span></a> <span class="fileView">{$galley->getViews()} views</span>
            </span>&nbsp;
        {/if}{/if}{/foreach}

            <span class="action">
                <a title="View {$article->getLocalizedTitle()|strip_unsafe_html}" {if $galley}{if $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}{/if}>{if $galley->isHTMLGalley()}View Article{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if}</a> <span class="fileView">{$publishedArticle->getViews()} views</span>
            </span>
                        
            <div class="tocPages"></div>
        </div>
    </li>

    {else}

    <li class="no-access">
        <p class="no-access-message">
            <img src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
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

        <h2>
            <a class="title" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
        </h2>

        {if $article->getLocalizedAbstract()}
        <p class="snippet">{$article->getLocalizedAbstract()|truncate:170:"..."|strip_unsafe_html|nl2br}</p>
        {/if}

        <p class="meta">
            <span class="authors">
            {assign var=count value=0}
            {assign var=authors value=$article->getAuthors()}
            {foreach from=$authors item=author name=authors key=i}
            {assign var=authorCount value=$authors|@count}
            {assign var=fullname value=$author->getFullName()}
            {assign var="pageTitle" value="search.authorIndex"}
            {assign var=authorFirstName value=$author->getFirstName()}
            {assign var=authorMiddleName value=$author->getMiddleName()}
            {assign var=authorLastName value=$author->getLastName()}
            {assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
            {assign var=authorCountry value=$author->getCountry()}
            {assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}
            {assign var=authorName value="$authorName $authorMiddleName"}{/if}
            {assign var="contact" value=$author->getData('primaryContact')}
            {assign var=count value=$count+1} <a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View articles by {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $fullname}{$fullname|escape}</a>{else}{/if}{if $i==$authorCount-2}, {elseif $i<$authorCount-1}, {elseif $i>$authorCount-3} {/if}
            {/foreach}
            </span>
            in <span class="enumeration"><a title="Go to {$journal->getLocalizedTitle()|escape}" href="{url journal=$journal->getPath()}"><em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a></span> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</em></span>
        </p>

        <!-- <div class="li-list ul-sans hide ul-journalName">Volume {$issue->getVolume()|strip_tags|escape}, Issue ({$issue->getNumber()|escape}), Page: {$article->getPages()|escape}, {assign var="doi" value=$article->getStoredPubId('doi')}{if $article->getPubId('doi')}<a href="http://dx.doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>{/if}</div> -->

        <!-- <div class="actions">
        {foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
        {if $issueAvailable}
            <span class="action">
                <a title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url journal=$journal->getPath() page="article" op="download" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span></a> <span class="fileView">{$galley->getViews()} views</span>
            </span>&nbsp;
        {/if}{/foreach}

            <span class="action">
                <a title="View {$article->getLocalizedTitle()|strip_unsafe_html}" target="_blank" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" target="_blank" class="file">View {if $article->getLocalizedAbstract()}{translate key="article.abstract"}{else}{translate key="article.details"}{/if}</a> <span class="fileView">{$publishedArticle->getViews()} views</span>
            </span>
                        
            <div class="tocPages"></div>
        </div> -->
    </li>
    {/if}
    {/iterate}
</ol>
    
<div class="functions-bar functions-bar-bottom">
    <table width="100%">
        <tbody>{if $results->wasEmpty()}
            <tr valign="bottom">
                <td colspan="{$numCols|escape}" class="nodata">{translate key="search.noResults"}</td>
            </tr>{else}
            <tr class="page-nr">
                <td width="50%" {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$results}</td>
                <td width="50%" colspan="2" style="text-align: right;"><span class="page_links">Page</span> {page_links anchor="results" iterator=$results name="search"}</td>
            </tr>{/if}
        </tbody>
    </table>
</div>

{include file="common/footer-home.tpl"}

