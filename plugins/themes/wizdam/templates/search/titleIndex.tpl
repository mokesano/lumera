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
{assign var=pageTitle value="search.titleIndex"}
{include file="common/header-SA07.tpl"}
{/strip}

<div class="sub-search__meta">
    <div class="search-meta" role="main">
        <div class="result-meta-information">
            <p class="result-count-message">Showing <strong>{page_info iterator=$results} results</strong>.</p>
            <div class="result-filter">Within
                <span class="facet-constraint-group facet-constraint-group-type"><a class="facet-link facet-link-type__journal" href="{url page="index"}">Articles</a></span>
            </div>
        </div>
    </div>
</div>

{if $currentJournal}
	{assign var=numCols value=3}
{else}
	{assign var=numCols value=4}
{/if}

<div id="results" class="sub-search__result-list artByTitle">
    <table class="tocArticle"><tbody>
        {iterate from=results item=result}
        <tr class="articles TOC">
            <td class="tocArticleTitleAuthors Info">
                <div class="article-list">
                    {assign var=publishedArticle value=$result.publishedArticle}
                    {assign var=article value=$result.article}
                    {assign var=issue value=$result.issue}
                    {assign var=issueAvailable value=$result.issueAvailable}
                    {assign var=issueUnavailable value=$issuesUnavailable.$issueId}
                    {assign var=journal value=$result.journal}
                    {assign var=section value=$result.section}
                    {assign var=sectionId value=$article->getSectionId()}
                    {assign var=section value=$sections[$sectionId]}

                {if $publishedArticle->getGalleys()}

                    {if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN }
                    <span class="no-access-message">
                        <img class="content-type u-font-sans" src="//www.assets.sangia.org/image/classical/lock.png" alt="PDF file Get Access" title="PDF file Get Access">
                        <h7 class="content-type li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$article->getSectionTitle()|strip_tags|escape}{/if}</span>{/if}</h7>
                    </span>
                    {else}
                    <span class="tocMenuArticle">
                        <h7 class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$article->getSectionTitle()|strip_tags|escape}{/if}</span>{/if}</h7>
                        <div class="ArtType">Open Access</div>
                    </span>
                    {/if}

                    <div class="ul-article--value">
                    <div class="tocTitle"><a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></div>
                    
                    {assign var=count value=0}
                    {assign var=authors value=$article->getAuthors()}
                    <div class="tocAuthors">{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" target="_blank" title="View articles by: {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{$fullname|escape}</a>{/foreach}
                    </div>

                    {if $article->getLocalizedAbstract()}
                    <div class="snippet abstract authorDetails">{$article->getLocalizedAbstract()|nl2br}</div>
                    {/if}
                    
                    <section class="volume-issue">
                        <a title="Volume {$issue->getVolume()|strip_tags|escape}, Issue {$issue->getNumber()|escape}, Pages: {$article->getPages()|escape}" target="_blank" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}">Volume {$issue->getVolume()|strip_tags|escape}, Issue {$issue->getNumber()|escape}</a> in <a title="Go to {$journal->getLocalizedTitle()|escape}" target="_blank" href="{url journal=$journal->getPath()}"><em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</span></em>
                    </section>

                    {assign var="doi" value=$article->getStoredPubId('doi')}
                    <div class="li-list ul-sans">
                    {if $article->getPubId('doi')}
                         <a title="Permanent link for {$article->getLocalizedTitle()|strip_tags|escape}" href="http://dx.doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a></div>
                    {/if}
                    </div>

                    <ul id="author-article-InfoList" class="tocMenuArticle ul-list">
                        {foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
                        {if $galley->isPdfGalley()}{if $issueAvailable}
                        <li class="tocMenuArticle li-list pubDOI">
                        <a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="download" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a>
                        </li>
                        {/if}{/if}{/foreach}

                        <li class="tocMenuArticle li-list pubDOI">
                            <a title="View {$article->getLocalizedTitle()|strip_tags|escape}" {if $galley}{if $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}"{else}href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}"{/if}{/if}>{if $galley->isHTMLGalley()}View Article{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if} <span class="fileView">{$publishedArticle->getViews()} views</span></a>
                        </li>
                        
                        <div class="tocPages hide">{$article->getPages()|escape}</div>
                    </ul>

                {else}

                    <span class="no-access-message">
                        <img class="content-type u-font-sans" src="//www.assets.sangia.org/image/classical/lock.png" alt="PDF file Not Available" title="PDF file Not Available">
                        <h7 class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$article->getSectionTitle()|strip_tags|escape}{/if}</span>{/if}</h7>
                    </span>

                    <div class="ul-article--value">
                    <div class="tocTitle"><a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></div>
                    
                    {assign var=count value=0}
                    {assign var=authors value=$article->getAuthors()}
                    <div class="tocAuthors">{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" target="_blank" title="View articles by: {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{$fullname|escape}</a>{/foreach}
                    </div>

                    {if $article->getLocalizedAbstract()}
                    <div class="snippet abstract authorDetails">{$article->getLocalizedAbstract()|nl2br}</div>
                    {/if}

                    <section class="volume-issue">
                        <a title="Volume {$issue->getVolume()|strip_tags|escape}, Issue {$issue->getNumber()|escape}, Pages: {$article->getPages()|escape}" target="_blank" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}">Volume {$issue->getVolume()|strip_tags|escape}, Issue {$issue->getNumber()|escape}</a> in <a title="Go to {$journal->getLocalizedTitle()|escape}" target="_blank" href="{url journal=$journal->getPath()}"><em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</span></em>
                    </section>

                    {assign var="doi" value=$article->getStoredPubId('doi')}
                    <div class="li-list ul-sans">
                    {if $article->getPubId('doi')}
                         <a title="Permanent link for {$article->getLocalizedTitle()|strip_tags|escape}" href="http://dx.doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>
                    {/if}
                    </div>

                    <ul id="author-article-InfoList" class="tocMenuArticle ul-list">
                        {foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
                        {if $galley->isPdfGalley()}
                        <li class="tocMenuArticle li-list pubDOI">
                        <a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="download" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a>
                        </li>
                        {/if}{/foreach}

                        <li class="tocMenuArticle li-list pubDOI">
                            <a title="View {$article->getLocalizedTitle()|strip_tags|escape}" {if $galley}{if $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}{/if}>{if $galley->isHTMLGalley()}View Article{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if} <span class="fileView">{$publishedArticle->getViews()} views</span></a>
                        </li>
                        
                        <div class="tocPages hide">{$article->getPages()|escape}</div>
                    </ul>

                    {/if}

                    </div>
                </td>
            </tr>
            {/iterate}
        </tbody>
    </table>
    
        
    {if $results->wasEmpty()}
    <div id="colspan" class="u-mb-24">
        {translate key="search.noResults"}
    </div>
    {else}
	<div class="colspan u-mb-0" id="colspan">	    
	    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	        <div class="c-pagination">View {page_info iterator=$results}</div>
        </section>
	    <section class="u-display-flex u-justify-content-center">
	        <div class="c-pagination">{page_links anchor="results" iterator=$results name="search"}
	       </div>
	    </section>
	</div>
    {/if}

    </div>
</div>
	
{include file="common/footer.tpl"}

