{**
 * templates/article/article.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View.
 *}
{strip}
{if $galley}
    {assign var=pubObject value=$galley}
{else}
    {assign var=pubObject value=$article}
{/if}
{include file="article/header.tpl"}
{/strip}

    <!-- Begin to fulltext file -->      
    <div id="publication" class="Publication">
        <div id="SRM-Pub" class="publication-brand u-show-from-sm text-img">
            <a rel="noreferrer noopener" title="Go to Sangia Publishing" href="//www.sangia.org" target="_blank"><img class="publication-brand-image u-font-sans" src="//www.assets.sangia.org/img/sangia-mono-branded-72x89-v2.png" loading="lazy" alt="Sangia Media" width="100%" height="100%"></a>
        </div>
            
        <div class="publication-volume u-text-center">
            <h2 id="publication-title" class="publication-title u-h3"><a rel="noreferrer noopener" class="publication-title-link" title="Go to {$currentJournal->getLocalizedTitle()|strip_tags|escape}" href="{url page="$currentJournal"}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a>
            </h2>
            <div class="text-xs"> 
            {if $issue->getVolume()}<a rel="noreferrer noopener" title="Go to table of contents for this volume/issue" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}" class="file">{translate key="issue.volume"} {$issue->getVolume()|strip_tags|escape}{if $issue->getNumber()}, {translate key="issue.issue"} {$issue->getNumber()|escape}{else}{/if}</a>, {$issue->getDatePublished()|date_format:"%B %Y"}{if $article->getPages()}, Pages {$article->getPages()|escape}{else}, {$article->getId()|escape}{/if}
            {else}
            Available online {$article->getDatePublished()|date_format:"%e %B %Y"}, {$article->getId()|escape}
                <div><span class="size-m publication-aip-text"><a rel="noreferrer noopener" href="{url page="issue" op="view" path="onlineFirst"}">In Press, Corrected Proof</a></span><span><a class="anchor" href="https://service.elsevier.com/app/answers/detail/a_id/22801/supporthub/sciencedirect/" target="_blank" title="What are Corrected Proof articles?"><svg focusable="false" viewBox="0 0 114 128" width="16" height="16" class="icon icon-help"><path d="m57 8c-14.7 0-28.5 5.72-38.9 16.1-10.38 10.4-16.1 24.22-16.1 38.9 0 30.32 24.68 55 55 55 14.68 0 28.5-5.72 38.88-16.1 10.4-10.4 16.12-24.2 16.12-38.9 0-30.32-24.68-55-55-55zm0 1e1c24.82 0 45 20.18 45 45 0 12.02-4.68 23.32-13.18 31.82s-19.8 13.18-31.82 13.18c-24.82 0-45-20.18-45-45 0-12.02 4.68-23.32 13.18-31.82s19.8-13.18 31.82-13.18zm-0.14 14c-11.55 0.26-16.86 8.43-16.86 18v2h1e1v-2c0-4.22 2.22-9.66 8-9.24 5.5 0.4 6.32 5.14 5.78 8.14-1.1 6.16-11.78 9.5-11.78 20.5v6.6h1e1v-5.56c0-8.16 11.22-11.52 12-21.7 0.74-9.86-5.56-16.52-16-16.74-0.39-0.01-0.76-0.01-1.14 0zm-4.86 5e1v1e1h1e1v-1e1h-1e1z"></path></svg></a></span>
                </div>
                {/if}
            </div>
            
            {if is_a($article, 'PublishedArticle')}{assign var=galleys value=$article->getGalleys()}{/if}
            {if $galleys && $subscriptionRequired && $showGalleyLinks}
                <div id="accessKey" class="articleType" style="float: center;">
                {if $purchaseArticleEnabled}
                {else}
                {/if}
                </div>
            {/if}
        </div>

        <div class="publication-cover u-show-from-sm journal-page">
            <noscript>
            {if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
            <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="publication-cover-image" {if $issue->getCoverPageAltText($locale) != ''} title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" {else} title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}"{else} alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}"{/if} /></a>
            {else}
            <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="publication-cover-image" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="SRM Publishing Group" /></a>
            {/if}
            </noscript>

            {if $issue->getBestIssueId($currentJournal)}<a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="publication-cover-image" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}homepageImage_en_US.jpg" alt="{$currentJournal->getLocalizedTitle()|strip_tags|escape}"/></a>{else}<a rel="noreferrer noopener" class="fallback-cover u-bg-grey7 u-clr-white TitlesJournal" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}">{$currentJournal->getLocalizedTitle()|truncate:30:"..."|strip_tags|escape}</a>
            {/if}
        </div>
    </div>    
        
    <h1 id="screen-reader-main-title" class="Head u-font-serif u-h2 u-margin-s-ver">
        <div class="article-dochead u-font-sans">
            {foreach name=sections from=$publishedArticles item=section key=sectionId}
            {foreach from=$section.articles item=article}
            {if $section.title}
            <span class="tocSectionTitle">{$section.title|escape}</span>
            {/if}
            {/foreach}{* articles *}
            {/foreach}{* sections *}
            {if $section && $section->getLocalizedIdentifyType()}
            <span>{$section->getLocalizedIdentifyType()|escape}{else}{translate key="rt.metadata.pkp.peerReviewed"}</span>
            {/if}
        </div>
        <span class="title-text u-font-serif">{$article->getLocalizedTitle()|strip_unsafe_html}</span>{if $issue->getLocalizedDescription()}<a rel="noreferrer noopener" name="baep-article-footnote-id1" href="#aep-article-footnote-id1" class="workspace-trigger label">☆</a>{/if}
    </h1>
    {foreach from=$article->getTitle(null) item=alternate key=metaLocale}
    {if $alternate != $article->getLocalizedTitle()}    
    <h1 id="screen-reader-main-title" class="Head u-font-serif u-h2 u-margin-s-ver">
        <span class="title-text u-font-serif" lang="{$metaLocale|String_substr:0:2|escape}">{$alternate|strip_unsafe_html}</span>
    </h1>
    {/if}{/foreach}
    
    <div id="banner" class="Banner">
        
        <script type="text/javascript">
            {literal}initRelatedItems();{/literal}
        </script>

        <div id="relatedItems" class="wrapper">
            <div class="AuthorGroups text-xs authorName u-font-sans">
                <div id="author-group" class="author-group">
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
                    {assign var=count value=$count+1}
                    <a rel="noreferrer noopener" href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" target="_blank">{if $fullname}{$fullname|escape}<sup>{if $contact eq 1} {$count|escape}{else} {$count|escape}{/if}</sup>{if $author->getData('primaryContact')|escape}<svg title="Corresponding Author" focusable="false" viewBox="0 0 106 128" class="icon icon-person" height="11" width="12"><path d="m11.07 1.2e2l0.84-9.29c1.97-18.79 23.34-22.93 41.09-22.93 17.74 0 39.11 4.13 41.08 22.84l0.84 9.38h10.04l-0.93-10.34c-2.15-20.43-20.14-31.66-51.03-31.66s-48.89 11.22-51.05 31.73l-0.91 10.27h10.03m41.93-102.29c-9.72 0-18.24 8.69-18.24 18.59 0 13.67 7.84 23.98 18.24 23.98s18.24-10.31 18.24-23.98c0-9.9-8.52-18.59-18.24-18.59zm0 52.29c-15.96 0-28-14.48-28-33.67 0-15.36 12.82-28.33 28-28.33s28 12.97 28 28.33c0 19.19-12.04 33.67-28 33.67"></path></svg></a>{if $author->getData('email')}<a rel="noreferrer noopener" class="icon" title="{$fullname|escape}, mail: {$author->getData('email')|escape} (Corresponding Author)" href="mailto:{$author->getData('email')|escape}" target="_blank" ><svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0.741 0 13 10"><path fill="#B0A8A3" d="M13.741 0L7.24 5.121.74 0zM.742 1.714L.74 10h6.502l-.001-3.165zm6.501 5.121L7.242 10h6.499V1.714z" alt="mail" /></svg></a>{/if}{else}{/if}{if $author->getData('orcid')} <a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$author->getData('orcid')|escape}" target="_blank" class="icon extern"><img src="{$baseUrl}/public/site/images/orcid_16x16.svg" style="height:12px" alt="orcid" /></a>{esle}{/if}{if $author->getUrl()} <a rel="noreferrer noopener" title="Go to view {$fullname|escape} Google Scholar profile" href="{$author->getUrl()|escape}" target="_blank" class="icon extern"><img src="{$baseUrl}/public/site/images/scholar.svg" style="height:14px" alt="scholar" /></a>{else}</a>{/if}{if $i==$authorCount-2}, {elseif $i<$authorCount-1}, {/if}{/if}
                {/foreach}

                    {assign var=count value=0}
                    {foreach from=$article->getAuthors() item=author name=authorList}
                    <dl class="affiliation u-font-sans">
                        {assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
                        {assign var=count value=$count+1}
                        {if $authorAffiliation||$count}{if $i=$authorCount-1}<dt><sup>{$count|escape}</sup></dt>{else}<dt><sup></sup></dt>{/if} <dd>{$authorAffiliation|escape}{if $author->getCountry()}, {$author->getCountryLocalized()|escape}{/if}.{/if}</dd>
                    </dl>
                    {/foreach}
                </div>
            </div>

            <p class="articleInfo u-font-sans">
            Received {$article->getDateSubmitted()|date_format:"%e %B %Y"},  Published {$article->getDatePublished()|date_format:"%e %B %Y"}, {if $article->getLastModified()}Edited {$article->getLastModified()|date_format:"%e %B %Y"}, {/if}Available online {$article->getDateStatusModified()|date_format:"%e %B %Y"}.
            </p>
        
            <div style="margin-left: -3px; margin-bottom: 5px;">
            <link rel="preload" href="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js" as="script">
            <script src="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js"></script><a class="crossmark-button" title="Check for updates with Crossmark" data-target="crossmark"><img alt="crossmark-logo" src="https://crossmark-cdn.crossref.org/widget/v2.0/logos/CROSSMARK_Color_horizontal.svg" width="150" /></a>
            </div>
            
        </div>
    
        <div id="toggleRelatedItems">
        <button id="hideRelatedItems" style="display:none;" class="show-hide-details u-font-sans" type="button" aria-expanded="false"><svg viewBox="0 0 9 9" class="icon-collapse"><path d="M2 5V4h5v1z"></path><path d="M0 0v9h9V0zm1 1h7v7H1z"></path></svg><a rel="noreferrer noopener" href="javascript:void(0)">Show less</a></button>
        <button id="showRelatedItems" class="show-hide-details u-font-sans" type="button" aria-expanded="false"><svg viewBox="0 0 9 9" class="icon-collapse"><path d="M2 5V4h5v1z"></path><path d="M0 0v9h9V0zm1 1h7v7H1z"></path></svg><a rel="noreferrer noopener" href="javascript:void(0)">Show more</a></button>
        </div>
    </div>

    <div class="DoiLink u-font-sans" id="doi-link">
        {foreach from=$pubIdPlugins item=pubIdPlugin}
        {if $issue->getPublished()}
            {assign var=pubId value=$pubIdPlugin->getPubId($pubObject)}
        {else}
            {assign var=pubId value=$pubIdPlugin->getPubId($pubObject, true)}{* Preview rather than assign a pubId *}
        {/if}
        {if $pubId}
            {if $pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}<a rel="noreferrer noopener" id="pub-id::{$pubIdPlugin->getPubIdType()|escape}" class="doi" target="_blank" rel="noreferrer noopener" aria-label="Persistent link using digital object identifier (DOI-CrossRef)" title="Persistent link using digital object identifier (DOI-CrossRef)" href="{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}">{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}</a>{else}<a rel="noreferrer noopener" href="javascript:void(0)">DOI not available</a>{/if}
        {/if}
        {/foreach}
        <a class="rights-and-content" target="" rel="noreferrer noopener" href="#permission">Get rights and content</a>
    </div>

    <div class="LicenseInfo u-font-sans">
        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
        <div class="License"><span>Under a Creative Commons </span><a target="_blank" rel="noreferrer noopener" href="{$article->getLicenseURL()|escape}">license</a></div>
        <div class="OpenAccessLabel">Subscription Access</div>        
        {/if}
        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
        <div class="License"><span>Under a Creative Commons </span><a target="_blank" rel="noreferrer noopener" href="{$article->getLicenseURL()|escape}">license</a></div>
        <div class="OpenAccessLabel">Open Access</div>        
        {/if}
    </div>

    <section class="ReferencedArticles"></section>
    <section class="ReferencedArticles"></section>
    <div class="PageDivider"></div>

    {if $article->getLocalizedAbstract()}
    <div id="abstracts" class="u-hide Abstracts u-font-serif">
        <div id="ab005" class="abstract author" lang="{$article->getLanguage()|strip_tags|escape}">
            <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="article.abstract"}</h2>
            <div id="abs005"><p id="sp0005">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</p></div>
        </div>
    </div>
    {else}
    <div id="abstracts" class="u-hide Abstracts u-font-serif">
        <div id="ab005" class="abstract author" lang="{$article->getLanguage()|strip_tags|escape}">
            <div id="abs005"><p id="sp0005">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</p></div>
        </div>
    </div>    
    {/if}

    {if $article->getLocalizedAbstract()}
    {if $article->getAbstract(null)}{foreach from=$article->getAbstract(null) key=metaLocale item=metaValue}
    <div id="abstracts" class="Abstracts u-font-serif">
        <div id="ab005" class="abstract author" lang="{$metaLocale|String_substr:0:2|escape}">
            <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="article.abstract" from="$metaLocale"}</h2>
            <div id="abs005"><p id="sp0005">{$metaValue|strip_unsafe_html|nl2br}</p></div>
        </div>
    </div>
    {/foreach}{/if}{/if}
    
    <ul id="issue-navigation" class="issue-navigation u-margin-s-bottom u-bg-grey1"><li class="previous move-left u-padding-s-ver u-padding-s-left"><a class="button-alternative button-alternative-tertiary" href="#"><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-left"><path d="m1 61l45-45 7 7-38 38 38 38-7 7z"></path></svg><span class="button-alternative-text"><strong>Previous </strong><span class="extra-detail-1">article</span><span class="extra-detail-2"> in issue</span></span></a></li><li class="next move-right u-padding-s-ver u-padding-s-right"><a class="button-alternative button-alternative-tertiary" href="#"><span class="button-alternative-text"><strong>Next </strong><span class="extra-detail-1">article</span><span class="extra-detail-2"> in issue</span></span><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg></a></li>
    </ul>

    {if $article->getLocalizedSubject(null)}
    <div id="articleSubject" class="Keywords u-font-serif">
        <div class="keywords-section">
            <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="article.subject"}</h2>
            {foreach from=$article->getSubject(null) key=metaLocale item=metaValue}{foreach from=$metaValue|explode:"; " item=gsKeyword}
            {if $gsKeyword}<div id="keyword" class="keyword"><span>{$gsKeyword|strip_unsafe_html|nl2br}</span></div>{/if}
            {/foreach}{/foreach}
        </div>
    </div>
    {/if}


{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain)}
    {assign var=hasAccess value=1}
{else}
    {assign var=hasAccess value=0}
{/if}

{if $galleys}
    {if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
        {foreach from=$article->getGalleys() item=galley name=galleyList}
        {if $galley->isHTMLGalley()}
            {$galley->getHTMLContents()}
            <!-- Begin fulltext HTML -->
        {/if}    
        {/foreach}
    {else}
        &nbsp;<a rel="noreferrer noopener" href="{url page="about" op="subscriptions"}" target="_parent">{translate key="reader.subscribersOnly"}</a>
    {/if}
{/if}

    <div id="Declaration" class="Body Declaration u-font-serif">
        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom u-font-serif">Declarations</h2>

        {if $article->getLocalizedSponsor()}
        <div class="Agencies Body">
            <h3 class="u-h4 sub2-title">Funding Information</h3>
            <p class="u-font-serif">{$article->getLocalizedSponsor()|escape}</p>
        </div>
        {/if}
                
        <div id="PublisherName" class="Body u-font-serif">
            <h3 class="u-h4 sub2-title">{translate key="rt.metadata.dublinCore.publisher"}'s Note</h3>
            <p class="u-font-serif">{if $currentJournal->getSetting('publisherInstitution')}{$currentJournal->getSetting('publisherInstitution')|escape}{else}SRM Publishing{/if} remains neutral with regard to jurisdictional claims in published maps and institutional affiliations.</p>
        </div>
    </div>

    {if $journalRt->getSupplementaryFiles() && is_a($article, 'PublishedArticle') && $article->getSuppFiles()}
    <div id="SuppFiles" class="Body u-font-serif">
        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom u-font-serif">{translate key="rt.suppFiles"}</h2>
        {foreach from=$article->getSuppFiles() item=suppFile key=key}
        <div class="supplement-files--value u-font-serif">
            <h3 class="supplement-files--label u-h4 u-font-serif"><a rel="noreferrer noopener" href="{url page="article" op="downloadSuppFile" path=$article->getBestArticleId()|to_array:$suppFile->getBestSuppFileId($currentJournal)}" class="file">Supplementary File {$key+1}</a></h3>
            <span class="supplement-files--label u-font-serif">
            {if $suppFile->getSuppFileTitle()}<span class=" ">{$suppFile->getSuppFileTitle()|escape}</span>{/if}
            {if $suppFile->getSuppFileDescription()}<div supplement-files-value u-font-sans>{$suppFile->getSuppFileDescription()|strip_unsafe_html|nl2br}</div>{/if}

                <div class="supplement-files-value u-font-sans">
                {if $suppFile->getSuppFileCreator()}
                <span>{$suppFile->getSuppFileCreator()|escape} <span class="italic">(Owner)</span>;</span>{/if}
                    
                {if $suppFile->getSuppFileSponsor()}
                <span class="italic">{$suppFile->getSuppFileSponsor()|escape} (Sponsor);</span>{/if}
                    
                {if $suppFile->getSuppFilePublisher()}
                <span>{translate key="common.publisher"} {$suppFile->getSuppFilePublisher()|escape}</span>{/if}
                        
                <span>{if $suppFile->getType()|escape}({translate key="common.type"} {$suppFile->getType()|escape};{elseif $suppFile->getSuppFileTypeOther()}{translate key="common.type"} {$suppFile->getSuppFileTypeOther()|escape};{else}{translate key="common.type"} {translate key="common.other"}</span>
                    {/if}

                {if $suppFile->isInlineable() || $suppFile->getRemoteURL()}{/if} {if !$suppFile->getRemoteURL()} <span>({$suppFile->getNiceFileSize()})</span>.{/if}
                </div>
            </span>
        </div>
        {/foreach}
    </div>
    {else}
    <div id="SuppFiles" class="Body u-font-serif">
        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom u-font-serif">{translate key="rt.suppFiles"}</h2>
        <p class="u-font-serif">{translate key="author.submit.suppFile.noFile"}</p>
    </div>
    {/if}

<div class="u-padding-l-hor-from-md u-hide-from-md row"> 
    <aside class="c-article--view c-article--view-m column">
        {if $issue->getLocalizedTitle($currentJournal)}{if $issue->getVolume()}
        <section class="SpecialIssueArticles" id="special-issue-articles">
            <div class="p-separator part-of-issue u-padding-s-bottom">
                <h2 class="part-of-issue-text u-h4 special-issue--value u-font-sans">Part of special issue:</h2>
                <div>
                <div class="special-issue">
                    {if $currentJournal}<a rel="noreferrer noopener" class="part-of-issue-title file special-issue" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><p class="u-font-sans title-issue">{$issue->getLocalizedTitle($currentJournal)|escape}</p></a>{/if}
                    {if $issue->getLocalizedDescription()}<div class="part-of-issue-editors u-font-sans"><span>{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</span></div>{/if}
                </div> 
                </div>
                {if $issue}
                {if $issueGalleys}{if $issueGalley->isPdfGalley()}
                <button href="{url page="issue" op="download" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="button-alternative DownloadFullIssue button-alternative-primary" type="button" id="download-full-issue"><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text u-font-sans">Download full issue</span></button>
                {/if}{/if}
                {/if}
            </div>
        </section>
        {/if}{/if}
        
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Advertisements -->
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-8416265824412721"
             data-ad-slot="5917495376"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>        
                
        {include file="plugins/blocks/popularArticles/templates/block.tpl"}
                
        <div class="p-separator u-interface">
            {if $galleys}{if $galley->isPdfGalley()}
            <div class="_largeButton c-button--secondary"><a rel="noreferrer noopener" title="Download this article in PDF format" href="{url page="article" op="viewFile" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file" {if $galley->getRemoteURL()}target="_blank"{else}target="_blank"{/if}>Download PDF fulltext</a>
            </div>
            {/if}{/if}
            <div class="_largeButton c-button--secondary"><a rel="noreferrer noopener" target="_blank" href="{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}">Export citation</a>
            </div>
            <div class="_largeButton c-button--secondary"><a rel="noreferrer noopener" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.mendeley.com/minified/bookmarklet.js');">Save to Mendeley</a>
            </div>
        </div>

        {if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
            {assign var=hasAccess value=1}
        {else}
            {assign var=hasAccess value=0}
        {/if}
        <div class="p-separator articleInfo">
           <div class="articleInfo">
               <h3 class="p-section-title">Article Metrics</h3>
               <ul class="p-section-title__item">
                   <li class="p-section-title__item citations"><a rel="noreferrer noopener" href="https://scholar.google.co.id/scholar_lookup?title={$article->getLocalizedTitle()|strip_tags|escape}" target="_blank">
                       <span class="p-section-item--name">Citations</span>
                       <span class="p-section-item--value">not available</span></a>
                  </li>
                  <li class="p-section-title__item readers">
                      <span class="p-section-item--name">Readers</span>
                      <span class="p-section-item--value">{$article->getViews()}</span>
                  </li>
                  {if (!$issueUnavailable || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN)}
                  {foreach from=$article->getGalleys() item=galley name=galleyList}{if $galley->isPdfGalley()}
                  <li class="p-section-title__item download">
                      <span class="p-section-item--name">Download</span>
                      {if $galley->isPdfGalley()}<span class="p-section-item--value">{$galley->getViews()}</span>{/if}
                  </li>{/if}
                  {/foreach}
                  {/if}
                  
                  <br />
                  
                  <li class="p-section-title__altmetric">Altmetric Attention score: {if $pubId}{$articleDOI|escape}{else}{/if}
                  </li>
             </ul>
        </div>
                     
        {if $pubId}
        <link rel="preload" href="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js" as="script">
        <script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script><div data-badge-popover="bottom" data-badge-type="bar" data-doi="{$articleDOI|escape}" class="altmetric-embed" target="_blank" >Altmetric badge</div>
        {else}
        <link rel="preload" href="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js" as="script">        
        <script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script><div data-badge-popover="bottom" data-badge-type="bar" data-doi="" class="altmetric-embed" target="_blank">Altmetric badge</div>
        {/if}

        <span class="__dimensions_badge_embed__" data-doi="{$articleDOI|escape}" data-style="small_rectangle" style="text-align: left;margin-top: .69em;"></span>
        <link rel="preload" href="https://badge.dimensions.ai/badge.js" as="script">
        <script async src="https://badge.dimensions.ai/badge.js" charset="utf-8"></script>

        </div>
                
        <div class="p-separator">
        <div class="js-shown">
            <h3 class="p-section-title u-font-sans">Share this article</h3>
            <ul data-test="social-media-share-buttons" class="c-social-links">
                <li class="c-social-links__item">
                    <a rel="noreferrer noopener" id="shareOnTwitter" class="js-btnShareOnTwitter" data-track="click" data-track-category="Article Page" data-track-action="Share Twitter" data-track-label="{$articleDOI|escape}" href="https://twitter.com/share?text={$article->getLocalizedTitle()|strip_tags|escape}&amp;url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;via=SRM_Publishing @SRMadhy" target="_blank">
                        <span class="u-visually-hidden">Share on Twitter</span>
                        <svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-twitter"><symbol id="icon-twitter" viewBox="0 0 24 24"><circle fill="#26A7DF" cx="12" cy="12" r="12"></circle><path fill="#FFF" d="M5.903 6.768s2.176 2.883 5.953 3.082c0 0-.51-1.702 1.058-3.004 1.568-1.305 3.508-.2 3.879.292 0 0 1.104-.172 1.875-.691 0 0-.252.995-1.18 1.594 0 0 1.086-.146 1.596-.439 0 0-.533.853-1.447 1.503 0 0 .381 3.595-2.709 6.57-3.09 2.971-8.176 2.352-10.012.797 0 0 2.495.277 4.249-1.219 0 0-2.074-.037-2.671-1.973 0 0 1.036.08 1.215-.119 0 0-2.232-.539-2.254-2.873 0 0 .68.339 1.297.359 0-.001-2.153-1.557-.849-3.879z"></path></symbol></use>
                        </svg>
                    </a>
                </li>
                <li class="c-social-links__item">
                    <a rel="noreferrer noopener" id="shareOnFacebook" class="js-btnShareOnFacebook" data-track="click" data-track-category="Article Page" data-track-action="Share Facebook" data-track-label="{$articleDOI|escape}" href="//www.facebook.com/sharer.php?u={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank">
                        <span class="u-visually-hidden">Share on Facebook</span>
                        <svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-facebook"><symbol id="icon-facebook" viewBox="0 0 24 24"><circle fill="#4D67A4" cx="12" cy="12" r="12"></circle><path fill="#FFF" d="M8.396 10.143h2.137V8.165s-.092-1.292.892-2.274c.979-.979 2.298-.887 4.177-.724v2.21h-1.387s-.586-.013-.861.303c-.271.315-.24.764-.24.875v1.588h2.41l-.311 2.458h-2.116v6.336h-2.56v-6.345H8.396v-2.449z"></path></symbol></use>
                        </svg>
                    </a>
                </li>
                <li class="c-social-links__item">
                    <a rel="noreferrer noopener" id="shareOnLinkedIn" class="js-btnShareOnLinkedIn" data-track="click" data-track-category="Article Page" data-track-action="Share LinkedIn" data-track-label="{$articleDOI|escape}" href="https://www.linkedin.com/shareArticle?mini=true&amp;url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;title={$article->getLocalizedTitle()|strip_tags|escape}&amp;source=SRM Publishing" target="_blank">
                        <span class="u-visually-hidden">Share on LinkedIn</span>
                        <svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-linkedin"><symbol id="icon-linkedin" viewBox="0 0 24 24"><circle fill="#0178B5" cx="12" cy="12" r="12"></circle><g fill="#FFF"><circle cx="8.022" cy="8.043" r="1.256"></circle><path d="M6.929 10.246h2.17v6.967h-2.17zm3.533 6.967h2.157v-3.594s-.078-1.627 1.219-1.627c1.301 0 1.211 1.231 1.211 1.635v3.586h2.183v-3.775s.129-2.745-1.472-3.191c-1.605-.445-2.661.071-3.219.985v-.985h-2.079v6.966z"></path></g></symbol></use>
                        </svg>
                    </a>
                </li>
                <li class="c-social-links__item">
                    <a rel="noreferrer noopener" id="shareOnWeibo" class="js-btnShareOnWeibo" data-track="click" data-track-category="Article Page" data-track-action="Share Weibo" data-track-label="{$articleDOI|escape}" href="#">
                        <span class="u-visually-hidden">Share on Weibo</span>
                        <svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-weibo"><symbol id="icon-weibo" viewBox="4 4 24 24"><circle fill="#C8E8F9" cx="16" cy="16" r="12"></circle><path fill="#FFF" d="M9.098 18.194c0 1.981 2.574 3.593 5.757 3.593 3.178 0 5.756-1.611 5.756-3.593 0-1.989-2.578-3.601-5.756-3.601-3.183.001-5.757 1.612-5.757 3.601"></path><path fill="#DF0A21" d="M14.991 21.496c-2.817.278-5.244-.996-5.428-2.85-.183-1.855 1.95-3.586 4.767-3.862 2.813-.278 5.243.996 5.428 2.849.18 1.855-1.957 3.584-4.767 3.863m5.628-6.155c-.24-.067-.404-.116-.279-.432.27-.688.299-1.278.004-1.699-.551-.791-2.062-.748-3.789-.022 0-.001-.548.241-.406-.191.266-.859.224-1.577-.191-1.993-.937-.942-3.437.037-5.577 2.18-1.602 1.61-2.533 3.317-2.533 4.789 0 2.817 3.606 4.533 7.13 4.533 4.626 0 7.701-2.696 7.701-4.834.001-1.292-1.085-2.027-2.06-2.331"></path><path fill="#F4992C" d="M23.689 10.182a4.492 4.492 0 0 0-4.283-1.39.651.651 0 1 0 .269 1.275 3.2 3.2 0 0 1 3.045.99 3.224 3.224 0 0 1 .672 3.138.647.647 0 0 0 .418.823.647.647 0 0 0 .816-.421v-.003a4.51 4.51 0 0 0-.937-4.412"></path><path fill="#F4992C" d="M21.973 11.736a2.183 2.183 0 0 0-2.086-.679.56.56 0 0 0-.428.667.554.554 0 0 0 .662.43v.001c.361-.075.754.034 1.018.33.266.299.34.698.227 1.054a.563.563 0 0 0 .359.706.563.563 0 0 0 .709-.362 2.2 2.2 0 0 0-.461-2.147"></path><path fill="#13110C" d="M15.143 18.139c-.1.17-.315.251-.483.179-.168-.07-.222-.255-.128-.42.097-.167.311-.248.479-.182.165.062.228.252.132.423m-.896 1.155c-.271.434-.854.625-1.295.425-.433-.195-.561-.707-.285-1.128.269-.426.832-.609 1.265-.427.443.183.58.688.315 1.13m1.024-3.084c-1.34-.351-2.852.32-3.434 1.506-.597 1.206-.021 2.546 1.33 2.984 1.402.453 3.055-.243 3.629-1.543.569-1.275-.142-2.587-1.525-2.947"></path></symbol></use>
                        </svg>
                    </a>
                </li>
                <li class="c-social-links__item">
                    <a rel="noreferrer noopener" id="shareOnReddit" class="js-btnShareOnReddit" data-track="click" data-track-category="Article Page" data-track-action="Share Reddit" data-track-label="{$articleDOI|escape}" href="https://reddit.com/submit?url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;title={$article->getLocalizedTitle()|strip_tags|escape}" aria-label="Reddit" target="_blank">
                        <span class="u-visually-hidden">Share on Reddit</span>
                        <svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-reddit"><symbol id="icon-reddit" viewBox="0 0 24 24"><circle fill="#BCBCBC" cx="12" cy="12" r="12"></circle><path fill="#FFF" d="M4.661 9.741c.941 0 1.703.761 1.703 1.704 0 .938-.762 1.705-1.703 1.705s-1.704-.767-1.704-1.705c0-.943.763-1.704 1.704-1.704zm13.844 0a1.704 1.704 0 1 1 .001 3.409 1.704 1.704 0 0 1-.001-3.409z"></path><path fill="#FFF" d="M11.736 8.732c4.285 0 7.762 2.283 7.762 5.104 0 2.812-3.477 5.1-7.762 5.1-4.288 0-7.762-2.285-7.762-5.1.001-2.82 3.474-5.104 7.762-5.104z"></path><path fill="#010101" d="M11.736 19.262c-4.461 0-8.088-2.437-8.088-5.426 0-2.994 3.626-5.431 8.088-5.431 4.457 0 8.087 2.437 8.087 5.431 0 2.989-3.63 5.426-8.087 5.426zm0-10.205c-4.104 0-7.438 2.145-7.438 4.779 0 2.633 3.334 4.773 7.438 4.773 4.098 0 7.437-2.142 7.437-4.773 0-2.636-3.339-4.779-7.437-4.779z"></path><circle fill="#FFF" cx="18.014" cy="5.765" r="1.385"></circle><path fill="#010101" d="M18.014 7.456a1.694 1.694 0 0 1-1.695-1.691c0-.934.764-1.694 1.695-1.694s1.693.762 1.693 1.694c0 .934-.762 1.691-1.693 1.691zm0-2.734c-.574 0-1.043.469-1.043 1.043s.469 1.041 1.043 1.041a1.042 1.042 0 0 0 0-2.084zM3.818 13.275a2.068 2.068 0 0 1-1.068-1.811 2.074 2.074 0 0 1 3.538-1.466l-.458.462a1.412 1.412 0 0 0-1.007-.42 1.424 1.424 0 0 0-.688 2.669l-.317.566zm15.866 0l-.316-.566a1.43 1.43 0 0 0 .73-1.245c0-.784-.635-1.424-1.42-1.424-.381 0-.736.149-1.008.42l-.459-.462a2.074 2.074 0 1 1 2.473 3.277z"></path><path fill="#F04B23" d="M9.165 11.604c.704 0 1.272.564 1.272 1.27a1.273 1.273 0 1 1-1.272-1.27zm5.321 0a1.274 1.274 0 1 1-1.275 1.27c0-.704.57-1.27 1.275-1.27z"></path><path fill="#010101" d="M11.79 8.915a.327.327 0 0 1-.307-.44l1.472-4.181 3.616.862c.177.044.283.22.238.395a.32.32 0 0 1-.391.239l-3.045-.729-1.275 3.632a.334.334 0 0 1-.308.222zm-.069 8.376c-2.076 0-2.908-.939-2.943-.982a.332.332 0 0 1 .035-.461.334.334 0 0 1 .459.033c.019.021.713.758 2.449.758 1.766 0 2.539-.763 2.549-.767a.324.324 0 1 1 .469.446c-.04.043-.954.973-3.018.973z"></path></symbol></use>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
        </div>
    </aside>    
</div>

{if $citationFactory->getCount()}
    <section id="References" class="bibliography u-font-serif text-s">
    <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="submission.citations"}</h2>    
    {iterate from=citationFactory item=citation}
        <section class="bibliography-sec">
            {$citation->getRawCitation()}
        </section>
    {/iterate}            
    </section>
{/if}    
            
    <div id="copyright" class="Body u-font-serif">
        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom u-font-serif">{translate key="submission.copyright} and permissions</h2>
        <p class="Body u-font-serif">
        {if $currentJournal->getSetting('includeCopyrightStatement')}
        {translate key="submission.copyrightStatement" copyrightYear=$article->getCopyrightYear()|strip_unsafe_html|nl2br copyrightHolder=$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}{/if}</p>
        <p id="copyrightBadge" class="Body u-font-serif">
        {if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if} {$article->getLicense|escape} (<a href="{$article->getLicenseURL()|escape}" rel="license">{$article->getLicenseURL()|escape}</a>), {translate key="submission.license.Statement1"}</p>
    </div>
    
    <div id="additionalNotes" class="additionalNotes col-lg-12">
        <h2 class="sub-title u-h3 u-font-serif">About this article</h2>
            <div id="bibliometricts-info" class="section__content bibliographic-information col-lg-12">
                <div class="crossmark col-lg-12">
                    <span class="bibliometricts">
                        <div class="crosmark__adjacent c-bibliographic-information__column embed">
                        <!-- Start Crossmark Snippet v2.0 -->
                        <link rel="preload" href="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js" as="script">
                            <script src="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js"></script>
                        <a rel="noreferrer noopener" data-target="crossmark" class="u-font-sans"><img alt="Verify authenticity via CrossMark" src="https://media.stipwunaraha.ac.id/img/crossmark.png" width="57" height="81" /></a>
                        <!-- End Crossmark Snippet -->
                        </div>
                </div>

                <div class="crossmark__adjacent col-lg-12">
                    <div id="CiteAs" class="crossmark__adjacent CiteAs">
                        <h3 class="heading">Cite this article as:</h3>
                        <div class="stateCiteAs u-font-sans">
                        {assign var=authors value=$article->getAuthors()}
                        {assign var=authorCount value=$authors|@count}
                        {foreach from=$authors item=author name=authors key=i}
                        {assign var=firstName value=$author->getFirstName()}
                        {assign var=middleName value=$author->getMiddleName()}
                        {$author->getLastName()|escape}, {$firstName|escape|truncate:1:".":true}{$middleName|escape|truncate:1:".":true}{if $i==$authorCount-2}, &amp; {elseif $i<$authorCount-1}, {/if}{/foreach}, 
                        {if $article->getDatePublished()}{$article->getDatePublished()|date_format:'%Y'}{elseif $issue->getDatePublished()}{$issue->getDatePublished()|date_format:'%Y'}{else}{$issue->getYear()|escape}{/if}. {$article->getLocalizedTitle()|strip_unsafe_html|nl2br}. <em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</em>&nbsp;{if $currentJournal}{$issue->getVolume()|strip_tags|escape}({$issue->getNumber()|escape}): {$article->getPages()|escape}{/if}. {assign var="doi" value=$article->getStoredPubId('doi')}{if $article->getPubId('doi')}<a rel="noreferrer noopener" title="Permanent link for this article" href="https://doi.org/{$article->getPubId('doi')|escape}">https://doi.org/{$article->getPubId('doi')|escape}</a>{/if}
                        </div>
                    </div>

                    <ul class="c-bibliographic-information__list">
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Submitted</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getDateSubmitted()|date_format:"%e %B %Y"}</span>
                    </li>
                    <li class="u-hide c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Revised</h5>
                        <span class="c-bibliographic-information__value u-font-sans">Not available</span>
                    </li>
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Accepted</h5>
                        <span class="c-bibliographic-information__value u-font-sans">Not available</span>
                    </li>            
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Published</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getDatePublished()|date_format:"%e %B %Y"}</span>
                    </li>
                </ul>

                <ul class="c-bibliographic-information__list">            
                    {if $article->getLocalizedDiscipline()}    
                    <li class="c-bibliographic-information__item">
                        <h5 class="strong u-font-serif">{translate key="rt.metadata.pkp.discipline"}</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getLocalizedDiscipline()|escape}</span>
                    </li>{/if}
                    
                    {if $article->getLocalizedSubjectClass()}
                    <li class="c-bibliographic-information__item">
                        <h5 class="strong u-font-serif">Sub-{translate key="rt.metadata.pkp.discipline"}</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getLocalizedSubjectClass()|escape}</span>
                    </li>{/if}
                </ul>        
                
                <div class="c-bibliographic-information__list">
                    {assign var="doi" value=$article->getStoredPubId('doi')}
                    {if $article->getPubId('doi')}
                    <div class="">
                        <h2 class="strong u-font-serif">DOI</h2>
                        <span class="c-bibliographic-information__value u-font-sans"><a rel="noreferrer noopener" title="Permanent link for this article" href="https://doi.org/{$article->getPubId('doi')|escape}">https://doi.org/{$article->getPubId('doi')|escape}</a></span>
                    </div>
                    {/if}
                </div>

                {if $article->getLocalizedSubject()}
                <div class="c-article__heading">
                    <h4 class="c-article__sub-heading u-font-serif">{translate key="rt.metadata.dublinCore.subject"}</h4>
                    <ul class="c-article-subject-list u-font-sans">
                        {if $article->getSubject(null)}{foreach from=$article->getSubject(null) key=metaLocale item=metaValue}
                        {foreach from=$metaValue|explode:"; " item=dcSubject}
                        <li class="c-article-subject-list__subject u-font-sans">
                            <span itemprop="about">{if $dcSubject}<span class="subjectId--value" title="Go to Google Scholar"><a rel="noreferrer noopener" class="q-gs q-cf" href="//scholar.google.com/scholar?q={$dcSubject|strip_tags|escape}" target="_blank">{$dcSubject|strip_unsafe_html|nl2br}</a></span>{/if}</span></li>
                        {/foreach}{/foreach}
                        {/if}
                    </ul>
                </div>
                {/if}
            </div>
        </div>
    </div>
            
{if $galley}
    {if $galley->isHTMLGalley()}
    
    {if $issue->getLocalizedDescription()}
    <div class="Footnotes"><dl class="footnote"><dt class="footnote-label"><sup><a rel="noreferrer noopener" href="#baep-article-footnote-id1">☆</a></sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}
    
    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans" href="{url page="article" op="view" path=$articleId}" aria-disabled="false" tabindex="0"><span class="anchor-text">View Abstract</span></a>

    <div class="Copyright"><span class="copyright-line u-font-sans">Copyright {if $currentJournal->getSetting('includeCopyrightStatement')}© {$article->getCopyrightYear()|strip_unsafe_html|nl2br} {$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}.{/if} {if $currentJournal->getSetting('publisherInstitution')}Published by {$currentJournal->getSetting('publisherInstitution')|escape}.{else}Published by SRM Publishing.{/if}</span>
        <div class="License u-hide" id="copyrightBadge"><span class="anchor-text">{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if}{$article->getLicense|escape}, {translate key="submission.license.Statement1"}</span></div>
    </div>   
            
    {else} <!-- End to fulltext HTML -->         

    {if $issue->getLocalizedDescription()}
    <div class="Footnotes"><dl class="footnote"><dt class="footnote-label"><sup><a rel="noreferrer noopener" href="#baep-article-footnote-id1">☆</a></sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}

    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans file-link" {if $galley}{if $galley->isHTMLGalley()}href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file" {if $galley->getRemoteURL()}target="_blank"{else}{/if} aria-disabled="false" tabindex="0"{elseif $galley->isPdfGalley()}aria-disabled="true" tabindex="-1"{/if}{/if}><span class="anchor-text">View full text</span></a>

    <div id="permission" class="Copyright"><span class="copyright-line u-font-sans">Copyright {if $currentJournal->getSetting('includeCopyrightStatement')}© {$article->getCopyrightYear()|strip_unsafe_html|nl2br} {$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}.{/if} {if $currentJournal->getSetting('publisherInstitution')}Published by {$currentJournal->getSetting('publisherInstitution')|escape}.{else}Published by SRM Publishing.{/if}</span>
        <div class="License u-hide" id="copyrightBadge"><span class="anchor-text">{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if}{$article->getLicense|escape}, {translate key="submission.license.Statement1"}</span></div>
    </div>

    {/if}   <!-- End fulltext with Galley PDF -->

{else}  <!-- Galley not available begin -->
    
    {if $issue->getLocalizedDescription()}
    <div class="Footnotes"><dl class="footnote"><dt class="footnote-label"><sup><a rel="noreferrer noopener" href="#baep-article-footnote-id1">☆</a></sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}

    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans file-link" aria-disabled="true" tabindex="-1"><span class="anchor-text">View full text</span></a>

    <div id="permission" class="Copyright"><span class="copyright-line u-font-sans">{if $currentJournal->getSetting('includeCopyrightStatement')}© {$article->getCopyrightYear()|strip_unsafe_html|nl2br} {$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}.{/if} {if $currentJournal->getSetting('publisherInstitution')}Published by {$currentJournal->getSetting('publisherInstitution')|escape}.{else} Published by SRM Publishing.{/if}</span>
        <div class="License u-hide" id="copyrightBadge"><span class="anchor-text">{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if}{$article->getLicense|escape}, {translate key="submission.license.Statement1"}</span></div>
    </div>

{/if}   <!-- Galley not available end -->

{include file="article/comments.tpl"}
    
{include file="article/footer.tpl"}
