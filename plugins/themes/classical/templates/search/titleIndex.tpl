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
                <a id="tool-rss" href="{url page="search" op="titles?subscribe"}" aria-describedby="rss-tooltip">
                    <img class="icon" src="//assets.sangia.org/image/classical/rss.png" alt="Subscribe">
                    <span class="tooltip" id="rss-tooltip" role="tooltip">Subscribe to this page via RSS</span>
                </a>
                <a id="tool-download" href="{url page="search" op="titles?download"}">
                    <img src="//assets.sangia.org/image/classical/download.png" alt="Download" aria-describedby="download-tooltip">
                    <span class="tooltip" id="download-tooltip" role="tooltip">Download search results (CSV)</span>
                </a>
            </div>
        </div>
    </div>    
    <h1 id="number-of-search-results-and-search-terms"><span class="number-of-search-results"><strong>{page_info iterator=$results}</strong> </span><span class="search-terms">Result(s) <span class="facet-constraint-message">within <a class="facet-link" href="{url page="$currentJournal"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape} <img class="remove-hover" src="//assets.sangia.org/image/classical/remove-hover.png" alt="Remove this filter"></a></span></span>
    </h1>
</div>

{if $currentJournal}
<div id="only-searching-within" class="box-secondary">
    <a href="/">
        <noscript>
        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
        {if $homepageImage && is_array($displayHomepageImage)}
        <img class="cover journal-cover__image cover-lazy img-default" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
        {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
        <img class="cover journal-cover__image cover-lazy img-default" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
        {else}
        <img class="cover journal-cover__image cover-lazy img-default" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" src="//assets.sangia.org/img/img-default.jpg" alt="Sangia Media & Publishing Group" />
        {/if}
        </noscript>

        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
        {assign var="displayJournalThumbnail" value=$currentJournal->getLocalizedSetting('journalThumbnail')}
        {if $homepageImage && is_array($displayHomepageImage)}
        <img class="cover journal-cover__image cover-lazy img-default" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
        {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
        <img class="cover journal-cover__image cover-lazy img-default" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
        {else}
        <img class="cover journal-cover__image cover-lazy img-default" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" aria-describedby="cover-tooltip" src="//assets.sangia.org/img/img-default.jpg" alt="Sangia Media & Publishing Group" />
        {call_hook name="Templates::Article::Article::ArticleCoverImage"}
        {/if}
        <span class="tooltip" id="cover-tooltip" role="tooltip">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</span>
    </a>
    <div class="text">
        <p class="message">You are now only within the Journal</p>
        <p class="title"><a href="{url page="$currentJournal"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a></p>
        <div class="stop"><a class="facet-link" href="{$baseUrl}">STOP within this Journal</a></div>
    </div>
</div>
{/if}

<div class="functions-bar functions-bar-top">
    <div class="page-listing">
        <div id="sort-results" class="sorting">
            <span>Sort By</span>
            <a class="btn relevance" href="{url page="search" op="search?title&sortOrder=relevance"}">Relevance</a>
            <a class="btn newest" href="{url page="search" op="search?title&sortOrder=newestFirst"}">Newest First</a>
            <a class="btn oldest" href="{url page="search" op="search?title&sortOrder=oldestFirst"}">Oldest First</a>
        </div>
        <div class="pagination" method="post">
            <span class="prev" title="previous">
                 <span class="page_links prevpage_links u-hide">Prev</span>
                 <img src="//assets.sangia.org/image/classical/arrow-left-inactive.png" alt="previous disabled">
            </span>
            <span class="page-nr">
                <span class="page_links">{page_links anchor="results" iterator=$results name="search" JournalID=$currentJournal->getId() sort=$sort sortDirection=$sortDirection}</span>
            </span>
            <span class="next" title="next">
                <span class="page_links nextpage_links u-hide">Next</span>
                <img src="//assets.sangia.org/image/classical/arrow-right-inactive.png" alt="next disabled">
            </span>
        </div>
    </div>    
</div>

<ol id="results-list" class="content-item-list">
    {iterate from=results item=result}

        {assign var=publishedArticle value=$result.publishedArticle}
        {assign var=article value=$result.article}
        {assign var=issue value=$result.issue}
        {assign var=issueAvailable value=$result.issueAvailable}
        {assign var=journal value=$result.journal}
        {assign var=sectionId value=$article->getSectionId()}
        {assign var=section value=$sections[$sectionId]}

    {if $publishedArticle->getGalleys()}
    <li class="mode">
        {foreach name=sections from=$publishedArticles item=section key=sectionId}
        {foreach from=$section.articles item=article}
        {if $section.title}
        <p class="content-type non-version">{$section.title|escape}</p>
        {/if} {/foreach}{* articles *}{/foreach}{* sections *}
        
        {if $issue->getPublished() && $section && $journal}
            <p class="content-type version1">{$section->getLocalizedTitle()|escape}</p>
        {else}
            <p class="content-type version2">{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$publishedArticle->getSectionTitle()|strip_tags|escape}{/if}</p>
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
        <div class="snippet">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
        {/if}

        <p class="meta">
            {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
            {else}
            <span class="authors">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View articles by {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName} </span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape|regex_replace:"/([a-z])[a-z]*(\s|$)/":"$1."|trim|regex_replace:"[^A-Z.]":""}</span>{/if}<span class="text surname">{$authorLastName}</span></a>{/foreach}
            </span>
            {/if}
            <span>in <span class="enumeration" datetime="{$publishedArticle->getDatePublished()|date_format:"$dateFormatShort"}"><a title="Go to {$journal->getLocalizedTitle()|escape}" href="{url journal=$journal->getPath()}"><em>{$currentJournal->getLocalizedTitle()|truncate:45:"..."|strip_tags|escape}</a></span> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</em></span>
            </span>
        </p>

        <div class="li-list ul-sans u-hide ul-journalName">Volume {$issue->getVolume()|strip_tags|escape}, Issue ({$issue->getNumber()|escape}), Page: {$article->getPages()|escape}, {assign var="doi" value=$article->getStoredPubId('doi')}{if $article->getPubId('doi')}<a href="http://dx.doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>{/if}
        </div>

        <div class="actions">
            {foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
            {if $galley->isPdfGalley()}{if $issueAvailable}
            <span class="action">
                <a title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url journal=$journal->getPath() page="article" op="download" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span></a> <span class="fileView">{$galley->getViews()} views</span>
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
            <img src="//assets.sangia.org/image/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
        </p>
        {foreach name=sections from=$publishedArticles item=section key=sectionId}
        {foreach from=$section.articles item=article}
        {if $section.title}
            <p class="content-type">{$section.title|escape}</p>
        {/if} {/foreach}{* articles *}{/foreach}{* sections *}
        
        {if $issue->getPublished() && $section && $journal}
            <p class="content-type version1">{$section->getLocalizedTitle()|escape}</p>
        {else}
            <p class="content-type version2">{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$publishedArticle->getSectionTitle()|strip_tags|escape}{/if}</p>
        {/if}

        <div class="lozenges"></div>

        <h2>
            <a class="title" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
        </h2>

        {if $article->getLocalizedAbstract()}
        <div class="snippet">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
        {/if}

        <p class="meta">
            {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
            {else}
            <span class="authors">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View articles by {if $fullname}{$fullname|escape}{/if}" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName} </span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape|regex_replace:"/([a-z])[a-z]*(\s|$)/":"$1."|trim|regex_replace:"[^A-Z.]":""}</span>{/if}<span class="text surname">{$authorLastName}</span></a>{/foreach}
            </span>
            {/if}
            <span>in <span class="enumeration" datetime="{$publishedArticle->getDatePublished()|date_format:"$dateFormatShort"}"><a title="Go to {$journal->getLocalizedTitle()|escape}" href="{url journal=$journal->getPath()}"><em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a></span> <span class="year" title="{$issue->getDatePublished()|date_format:'%B %Y'}">({$issue->getDatePublished()|date_format:'%Y'})</em></span>
            </span>
        </p>

        <div class="li-list ul-sans u-hide ul-journalName">Volume {$issue->getVolume()|strip_tags|escape}, Issue ({$issue->getNumber()|escape}), Page: {$article->getPages()|escape}, {assign var="doi" value=$article->getStoredPubId('doi')}{if $article->getPubId('doi')}<a href="http://dx.doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>{/if}</div>

        <div class="actions u-hide">
            {foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
            {if $issueAvailable}
            <span class="action">
                <a title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url journal=$journal->getPath() page="article" op="download" path=$publishedArticle->getBestArticleId($journal)|to_array:$galley->getBestGalleyId($journal)}" class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span></a> <span class="fileView">{$galley->getViews()} views</span>
            </span>&nbsp;
            {/if}{/foreach}
            <span class="action">
                <a title="View {$article->getLocalizedTitle()|strip_unsafe_html}" target="_blank" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}" target="_blank" class="file">View {if $article->getLocalizedAbstract()}{translate key="article.abstract"}{else}{translate key="article.details"}{/if}</a> <span class="fileView">{$publishedArticle->getViews()} views</span>
            </span>
            <div class="tocPages"></div>
        </div>
    </li>
    {/if}
    {/iterate}
</ol>
    
<div class="functions-bar functions-bar-bottom">
    {if $results->wasEmpty()}
    <div class="nodata">{translate key="search.noResults"}</div>
    {else}
    <div class="page-listing">
        <div id="result-item">{page_info iterator=$results}</div>
        <div class="pagination" method="post">
            <span class="prev" title="previous">
                 <span class="page_links prevpage_links u-hide">Prev</span>
                 <img src="//assets.sangia.org/image/classical/arrow-left-inactive.png" alt="previous disabled">
            </span>
            <span class="page-nr">
                <span class="page_links">{page_links anchor="results" iterator=$results name="search" JournalID=$currentJournal->getId()}</span>
            </span>
            <span class="next" title="next">
                <span class="page_links nextpage_links u-hide">Next</span>
                <img src="//assets.sangia.org/image/classical/arrow-right-inactive.png" alt="next disabled">
            </span>
        </div>
    </div>    
    {/if}
</div>

{include file="common/footer-home.tpl"}

