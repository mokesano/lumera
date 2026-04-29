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
        <a rel="noreferrer noopener" title="Go to Sangia Publishing" href="{$urlBase}" target="_blank"><img class="lazyload publication-brand-image u-font-sans" src="//assets.sangia.org/img/sangia-mono-branded-72x89-v2.png" height="100%" width="100%" loading="lazy" alt="Sangia Media"/></a>
    </div>
    <div class="publication-volume u-text-center">
        <h2 id="publication-title" class="publication-title u-h3"><a rel="noreferrer noopener" class="anchor publication-title-link anchor-navigation" title="Go to {$currentJournal->getLocalizedTitle()|strip_tags|escape}" href="{$currentJournal->getUrl()|escape}"><span class="anchor-text">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</span></a>
        </h2>
        <div class="text-xs"> 
        {if $issue->getVolume() && $article->getPages() || $status == STATUS_PUBLISHED}
            <a rel="noreferrer noopener" title="Go to table of contents for this volume/issue" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}" class="anchor anchor-default file"><span class="anchor-text-container"><span class="anchor-text">{translate key="issue.volume"} {$issue->getVolume()|escape}{if $issue->getNumber() != ""}{if $issue->getNumber()|regex_replace:"/[a-zA-Z]/":"" eq $issue->getNumber()}, {translate key="issue.issue"} {$issue->getNumber()|strip_tags|nl2br|escape}{else}, {$issue->getNumber()|strip_tags|nl2br|escape}{/if}{/if}</span></span></a>, {$issue->getDatePublished()|date_format:"%B %Y"}{if $article->getPages()}, Pages {$article->getPages()|escape}{else}, {$article->getId()|escape|string_format:"%07d"}{/if}
        {else}
            Available online {$article->getDateStatusModified()|date_format:"%e %B %Y"}, {$article->getId()|escape|string_format:"%07d"}
            {if !$galley && $galleys|@count == 0}
                {if $layoutFile != ''}{* CORRECTED PROOF *}
                <div><span class="size-m publication-aip-text"><a rel="noreferrer noopener" href="{url page="issue" op="view" path="onlineFirst"}">In Press, Corrected Proof</a></span><span><a class="anchor" href="https://service.elsevier.com/app/answers/detail/a_id/22801/supporthub/sciencedirect/" target="_blank" title="What are Corrected Proof articles?"><svg focusable="false" viewBox="0 0 114 128" width="16" height="16" class="icon icon-help"><path d="m57 8c-14.7 0-28.5 5.72-38.9 16.1-10.38 10.4-16.1 24.22-16.1 38.9 0 30.32 24.68 55 55 55 14.68 0 28.5-5.72 38.88-16.1 10.4-10.4 16.12-24.2 16.12-38.9 0-30.32-24.68-55-55-55zm0 1e1c24.82 0 45 20.18 45 45 0 12.02-4.68 23.32-13.18 31.82s-19.8 13.18-31.82 13.18c-24.82 0-45-20.18-45-45 0-12.02 4.68-23.32 13.18-31.82s19.8-13.18 31.82-13.18zm-0.14 14c-11.55 0.26-16.86 8.43-16.86 18v2h1e1v-2c0-4.22 2.22-9.66 8-9.24 5.5 0.4 6.32 5.14 5.78 8.14-1.1 6.16-11.78 9.5-11.78 20.5v6.6h1e1v-5.56c0-8.16 11.22-11.52 12-21.7 0.74-9.86-5.56-16.52-16-16.74-0.39-0.01-0.76-0.01-1.14 0zm-4.86 5e1v1e1h1e1v-1e1h-1e1z"></path></svg></a></span>
                </div>
                {else}{* ARTICLE IN PRESS (COMING SOON) *}
                <div><span class="size-m publication-aip-text"><a rel="noreferrer noopener" href="{url page="issue" op="view" path="onlineFirst"}">Article in Press</a></span>
                </div>
                {/if}
            {/if}
        {/if}
        </div>
            
        {if is_a($article, 'PublishedArticle')}{assign var=galleys value=$article->getGalleys()}{/if}
        {if $galleys && $subscriptionRequired && $showGalleyLinks}
            <div id="accessKey" class="articleType" style="float: center;">
            {if $purchaseArticleEnabled}{else}{/if}
            </div>
        {/if}
    </div>

    <div class="publication-cover u-show-from-sm journal-page">
        <noscript>
        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
        {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
        {if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && is_array($displayCoverIssue)}
        <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="lazyload publication-cover-image" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" src="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}" /></a>
        {elseif $displayHomepageImage && is_array($displayHomepageImage)}
        <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="lazyload publication-cover-image" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" /></a>
        {else}
        <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="lazyload publication-cover-image" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Sangia Publishing Group" loading="lazy"/></a>
        {/if}
        </noscript>
            
        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
        {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
        {if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && is_array($displayCoverIssue)}
        <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="lazyload publication-cover-image" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" src="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}" /></a>
        {elseif $displayHomepageImage && is_array($displayHomepageImage)}
        <a rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><img class="lazyload publication-cover-image" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape}" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" /></a>
        {else}
        <a rel="noreferrer noopener" class="fallback-cover u-bg-grey7 u-clr-white TitlesJournal" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}">{$currentJournal->getLocalizedTitle()|truncate:30:"..."|strip_tags|escape}</a>
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
        <span>{$section->getLocalizedIdentifyType()|escape}</span>
        {/if}
    </div>
    <span class="title-text u-font-serif">{$article->getLocalizedTitle()|strip_unsafe_html}</span>{if $issue->getShowTitle() && $issue->getLocalizedDescription()}<a rel="noreferrer noopener" name="article-footnote-id1" href="#article-footnote-id1" class="workspace-trigger label">☆</a>{/if}
</h1>
{if $article->getLocalizedAbstract()}
{foreach from=$article->getTitle(null) item=alternate key=metaLocale}
{if $alternate != $article->getLocalizedTitle()}    
<h1 id="screen-reader-main-title" class="Head u-font-serif u-h2 u-margin-s-ver">
    <span class="title-text u-font-serif" lang="{$metaLocale|String_substr:0:2|escape}">{$alternate|strip_unsafe_html}</span>
</h1>
{/if}{/foreach}{/if}

<div id="banner" class="Banner">
    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
    <div class="wrapper truncated">
        <div class="AuthorGroups text-s">
            <div id="author-group" class="author-group">
                <span class="sr-only">Author links open overlay panel</span>
            </div>
            <p class="text-s">Available online {$article->getDateStatusModified()|date_format:"%e %B %Y"}.</p>
            <button class="button-link crossmark-button button-link-primary button-link-icon-only"><link rel="preload" href="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js" as="script"><script src="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js"></script><img loading="lazy" class="lazyload crossmark-button" title="Check for updates with Crossmark" data-target="crossmark" alt="crossmark-logo" src="https://crossmark-cdn.crossref.org/widget/v2.0/logos/CROSSMARK_Color_horizontal.svg" width="150" />
            </button>
        </div>
    </div>
    {else}
    <div class="wrapper truncated">
        <div class="AuthorGroups text-s">
            <div id="author-group" class="author-group">
                {assign var=count value=0}
                {assign var=authors value=$article->getAuthors()}
                <span class="sr-only">Author links open overlay panel</span>
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
                <a rel="noreferrer noopener" href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="button-link author size-m workspace-trigger button-link-primary" target="_blank"><span class="button-link-text" ><span class="react-xocs-alternative-link" itemprop="creator" itemscope="name" itemtype="http://schema.org/Person">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName}</span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName}</span>{/if}<span class="text surname">{$authorLastName}</span></span>{if $authorCount-1}<span class="author-ref" id="baff0010"><sup>{$count|escape}</sup></span>{elseif $authorCount+1}<span class="author-ref" id="baff0010"><sup>{$count|escape}</sup></span>{/if}{if $author->getData('primaryContact')|escape}<svg title="Corresponding author" focusable="false" viewBox="0 0 106 128" class="icon-person react-xocs-author-icon" role="img" height="12" width="12" aria-label="Person"><path d="m11.07 1.2e2l0.84-9.29c1.97-18.79 23.34-22.93 41.09-22.93 17.74 0 39.11 4.13 41.08 22.84l0.84 9.38h10.04l-0.93-10.34c-2.15-20.43-20.14-31.66-51.03-31.66s-48.89 11.22-51.05 31.73l-0.91 10.27h10.03m41.93-102.29c-9.72 0-18.24 8.69-18.24 18.59 0 13.67 7.84 23.98 18.24 23.98s18.24-10.31 18.24-23.98c0-9.9-8.52-18.59-18.24-18.59zm0 52.29c-15.96 0-28-14.48-28-33.67 0-15.36 12.82-28.33 28-28.33s28 12.97 28 28.33c0 19.19-12.04 33.67-28 33.67"></path></svg>{if $author->getData('email')}<svg title="{$fullname|escape} corresponding mail: {$author->getData('email')|escape}" version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" class="icon-envelope react-xocs-author-icon" role="img" aria-label="Envelope" focusable="false" width="20" height="20" alt="mail"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg>{/if}{else}{/if}</span>{if $i==$authorCount-2}, {elseif $i<$authorCount-1}, {/if}</a>{/foreach}
            </div>
            <div id="affiliation-group" class="author-group affiliation-group">
            {assign var=count value=0}
            {foreach from=$article->getAuthors() item=author name=authorList}
                <dl class="affiliation">
                {assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
                {assign var=count value=$count+1}
                {if $authorAffiliation||$count}
                    {if $i == $authorCount-1}
                    <dt><sup>{$count|escape}</sup></dt>
                    {elseif $i == $authorCount+1}
                    <dt><sup>{$count|escape}</sup></dt>
                    {/if}
                    <dd>{if $authorAffiliation|escape}{$authorAffiliation|escape}{else}[<i>Author affiliation not available</i>]{/if}{if $author->getCountry()}, {$author->getCountryLocalized()|escape}{/if}</dd>
                {/if}
                </dl>
            {/foreach}
            </div>
            
            <p class="text-s">{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}Available online {$article->getDateStatusModified()|date_format:"%e %B %Y"}.{else}{if $article->getLocalizedAbstract(null) || $article->getLocalizedSubject(null)}Received {$article->getDateSubmitted()|date_format:"%e %B %Y"}{if $revisionDate}, Revised {$revisionDate|date_format:"%e %B %Y"}{/if}{if $acceptedDate}, Accepted {$acceptedDate|date_format:"%e %B %Y"}{/if}, Published {$article->getDatePublished()|date_format:"%e %B %Y"}, Available online {$article->getDateStatusModified()|date_format:"%e %B %Y"}, Version of Record {$article->getLastModified()|date_format:"%e %B %Y"}.{else}Available online {$article->getDateStatusModified()|date_format:"%e %B %Y"}.{/if}{/if}</p>
            <button class="button-link crossmark-button button-link-primary button-link-icon-only"><link rel="preload" href="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js" as="script"><script src="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js"></script><img loading="lazy" class="lazyload crossmark-button" title="Check for updates with Crossmark" data-target="crossmark" alt="crossmark-logo" src="https://crossmark-cdn.crossref.org/widget/v2.0/logos/CROSSMARK_Color_horizontal.svg" width="150" />
            </button>
        </div>
    </div>
    {/if}
    
    <button id="show-more-btn" class="button-link u-margin-s-ver show-hide-details u-font-sans-sang" type="button" aria-expanded="false" data-aa-button="icon-collapse"><span class="anchor-text text-s">Show more</span><svg focusable="false" viewBox="0 0 92 128" height="20" width="17.25" class="icon icon-navigate-down u-flip-vertically"><path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path></svg></button>

    <div class="banner-options u-padding-xs-bottom">
        <div id="AddToMendeley" class="button-link AddToMendeley button-link-secondary u-display-inline-block u-display-inline-flex-from-md button-link-icon-left"><a rel="noreferrer noopener" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.mendeley.com/minified/bookmarklet.js');"><button class="button button-anchor" role="button" aria-expanded="false" aria-haspopup="true" type="button"><svg focusable="false" viewBox="0 0 86 128" height="20" width="20" class="icon icon-plus"><path d="m48 58v-38h-1e1v38h-38v1e1h38v38h1e1v-38h38v-1e1z"></path></svg><span class="button-link-text-container"><span class="button-text">Save to Mendeley</span></span></button></a></div>
        <div id="social" class="Social u-display-inline-block">
            <div id="social-popover" class="popover social-popover" aria-label="Share article on social media">
                <div id="popover-trigger-social-popover"><button class="button button-anchor" role="button" aria-expanded="false" aria-haspopup="true" type="button"><svg focusable="false" viewBox="0 0 128 128" height="20" width="20" class="icon icon-share"><path d="m9e1 112c-6.62 0-12-5.38-12-12s5.38-12 12-12 12 5.38 12 12-5.38 12-12 12zm-66-36c-6.62 0-12-5.38-12-12s5.38-12 12-12 12 5.38 12 12-5.38 12-12 12zm66-6e1c6.62 0 12 5.38 12 12s-5.38 12-12 12-12-5.38-12-12 5.38-12 12-12zm0 62c-6.56 0-12.44 2.9-16.48 7.48l-28.42-15.28c0.58-1.98 0.9-4.04 0.9-6.2s-0.32-4.22-0.9-6.2l28.42-15.28c4.04 4.58 9.92 7.48 16.48 7.48 12.14 0 22-9.86 22-22s-9.86-22-22-22-22 9.86-22 22c0 1.98 0.28 3.9 0.78 5.72l-28.64 15.38c-4.02-4.34-9.76-7.1-16.14-7.1-12.14 0-22 9.86-22 22s9.86 22 22 22c6.38 0 12.12-2.76 16.14-7.12l28.64 15.38c-0.5 1.84-0.78 3.76-0.78 5.74 0 12.14 9.86 22 22 22s22-9.86 22-22-9.86-22-22-22z"></path></svg><span class="button-text">Share</span></button>
                </div>
                <div id="popover-content-social-popover" class="popover-content popover-align-left u-js-hide" role="region">
                    <div class="popover-content-inner popover-close-button-hidden">
                        <div class="popover-children">
                            <ul class="share-options-list">
                                <li class="u-margin-xs-bottom"><a class="anchor social-anchor anchor-primary anchor-icon-left" href="mailto:?subject=I would like to share with you '{$article->getLocalizedTitle()|strip_tags|escape}': {url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank" id="Email" aria-label="Email"><svg focusable="false" viewBox="0 0 102 128" height="20" class="icon icon-envelope"><path d="M55.8 57.2c-1.78 1.31-5.14 1.31-6.9 0L17.58 34h69.54L55.8 57.19zM0 32.42l42.94 32.62c2.64 1.95 6.02 2.93 9.4 2.93s6.78-.98 9.42-2.93L102 34.34V24H0zM92 88.9L73.94 66.16l-8.04 5.95L83.28 94H18.74l18.38-23.12-8.04-5.96L10 88.94V51.36L0 42.9V104h102V44.82l-10 8.46V88.9"></path></svg><span class="anchor-text-container"><span class="anchor-text">Email</span><svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>
                                    </span></a>
                                </li>
                                <li class="u-margin-xs-bottom"><a class="anchor social-anchor anchor-primary anchor-icon-left" href="https://www.facebook.com/sharer/sharer.php?u={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank" id="Facebook" aria-label="Facebook"><svg focusable="false" viewBox="0 0 24 24" height="20" class="icon icon-facebook"><path d="M24.08 12c0-6.629-5.373-12.001-12-12.001-6.63 0-12.002 5.371-12.002 12 0 5.99 4.388 10.955 10.125 11.855v-8.386H7.157V12h3.047V9.355c0-3.007 1.792-4.669 4.533-4.669 1.313 0 2.686.235 2.686.235v2.953H15.91c-1.49 0-1.956.925-1.956 1.874V12h3.329l-.532 3.47h-2.797v8.385C19.692 22.954 24.08 17.99 24.08 12"></path><path d="M16.75 15.468L17.284 12h-3.329V9.75c0-.95.465-1.875 1.956-1.875h1.513V4.921s-1.373-.235-2.686-.235c-2.741 0-4.533 1.662-4.533 4.67v2.643H7.157v3.47h3.047v8.385a12.09 12.09 0 0 0 3.75 0v-8.386h2.797" fill="#fff"></path></svg><span class="anchor-text-container"><span class="anchor-text">Facebook</span><svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg></span></a>
                                </li>
                                <li class="u-margin-xs-bottom"><a class="anchor social-anchor anchor-primary anchor-icon-left" href="https://twitter.com/share?text={$article->getLocalizedTitle()|strip_tags|escape}&amp;url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;via=SangiaNews @SRMadhy" target="_blank" id="Twitter" aria-label="Twitter"><svg focusable="false" viewBox="0 0 24 24" height="20" class="icon icon-twitter"><path d="M14.226 10.162 22.97 0h-2.072l-7.591 8.824L7.243 0H.25l9.168 13.343L.25 24h2.072l8.016-9.318L16.741 24h6.993l-9.508-13.838Zm-2.837 3.299-.93-1.329L3.07 1.56h3.18l5.965 8.532.93 1.329 7.753 11.09h-3.182l-6.327-9.05z"></path></svg><span class="anchor-text-container"><span class="anchor-text">X (Twitter)</span><svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg></span></a>
                                </li>
                                <li class="u-margin-xs-bottom"><a class="anchor social-anchor anchor-primary anchor-icon-left" href="https://www.linkedin.com/shareArticle?mini=true&amp;url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;title={$article->getLocalizedTitle()|strip_tags|escape}&amp;source=SangiaPublishing" target="_blank" id="LinkedIn" aria-label="LinkedIn"><svg focusable="false" viewBox="0 0 128 128" height="20" class="icon icon-linkedin"><path d="M114.597 1H13.685A12.632 12.632 0 001 13.685v100.63A12.632 12.632 0 0013.685 127h100.63A12.632 12.632 0 00127 114.315V13.403C127 6.638 121.362 1 114.597 1z" fill-rule="evenodd" clip-rule="evenodd"></path><path d="M29.47 21.013c-6.202 0-11.557 5.074-11.557 11.557 0 6.484 5.355 11.558 11.557 11.558 6.201 0 11.557-5.074 11.557-11.558 0-6.483-5.356-11.557-11.557-11.557zm-9.302 86.82h18.886V51.173H20.168zM86.409 50.61c-7.61 0-13.53 3.382-16.349 7.892v-7.047H51.456v56.658h18.886V84.436c0-8.738 3.101-14.094 9.866-14.094 5.074 0 9.02 3.383 9.02 12.403v25.37h18.886v-34.39c0-16.35-7.329-23.114-21.705-23.114z" fill-rule="evenodd" clip-rule="evenodd" fill="#fff"></path></svg><span class="anchor-text-container"><span class="anchor-text">LinkedIn</span><svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg></span></a>
                                </li>
                                <li><a class="anchor social-anchor anchor-primary anchor-icon-left" href="https://reddit.com/submit?url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;title={$article->getLocalizedTitle()|strip_tags|escape}" target="_blank" id="Reddit" aria-label="Reddit"><svg focusable="false" viewBox="0 0 24 24" height="20" class="icon icon-reddit"><path d="M8.584 17.03c-.919.005-1.018 1.35-.191 1.632 1.957 1.277 4.64 1.284 6.723.313.64-.253 1.51-1.007.87-1.702-.829-.777-1.635.615-2.467.62-1.607.358-3.379.176-4.71-.836a.707.707 0 0 0-.225-.026zm12.345-6.57c1.035-.048 1.72 1.418 1.147 2.067-.371-.773-.948-1.463-1.554-2.03.133-.033.27-.044.407-.036zm-17.856-.005c.066-.004.135-.002.203.006.468.064-.467.576-.576.885a6.533 6.533 0 0 0-.784 1.226c-.53-.898.162-2.068 1.157-2.117zm8.936-1.296c3.194.032 6.867 1.013 8.61 3.92 1.245 2.127-.204 4.67-2.143 5.767-3.767 2.263-8.786 2.296-12.626.189-1.956-1.024-3.628-3.396-2.61-5.627 1.534-3.146 5.524-4.22 8.77-4.249zm7.752-7.052a2.529 2.529 0 0 0-2.1 1.27c-1.418-.206-4.242-.853-4.242-.853L11.254 7.63c-2.22.107-4.452.668-6.358 1.832C3.216 8.142.448 9.314.223 11.44a2.977 2.977 0 0 0 1.198 2.852c-.362 3.075 2.173 5.576 4.81 6.668 4.381 1.833 9.82 1.567 13.723-1.266 1.672-1.232 2.91-3.268 2.642-5.402 1.83-1.19 1.53-4.249-.464-5.096a2.977 2.977 0 0 0-3.01.266c-1.812-1.108-3.918-1.665-6.023-1.813l1.065-3.469 3.193.83c.2 1.874 2.711 2.832 4.104 1.559 1.498-1.13 1.039-3.756-.752-4.313a2.475 2.475 0 0 0-.948-.15zm.082 1.253a1.189 1.189 0 0 1 1.189 1.19 1.189 1.189 0 0 1-1.189 1.19 1.189 1.189 0 0 1-1.189-1.19 1.189 1.189 0 0 1 1.19-1.19zM10.5 13.656a1.875 1.875 0 0 1-1.875 1.875 1.875 1.875 0 0 1-1.875-1.875 1.875 1.875 0 0 1 1.875-1.875 1.875 1.875 0 0 1 1.875 1.875zm6.947-.002a1.875 1.875 0 0 1-1.875 1.875 1.875 1.875 0 0 1-1.875-1.875 1.875 1.875 0 0 1 1.875-1.875 1.875 1.875 0 0 1 1.875 1.875z"></path></svg><span class="anchor-text-container"><span class="anchor-text">Reddit</span><svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg></span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ExportCitation u-display-inline-block" id="export-citation">
            <div class="popover export-citation-popover" id="export-citation-popover" aria-label="Export or save citation">
                <div id="popover-trigger-export-citation-popover"><button class="button button-anchor" role="button" aria-expanded="false" aria-haspopup="true" type="button"><svg focusable="false" viewBox="0 0 106 128" height="20" width="20" class="icon icon-cited-by-66"><path xmlns="http://www.w3.org/2000/svg" d="m2 58.78v47.22h44v-42h-34v-5.22c0-18.5 17.08-26.78 34-26.78v-1e1c-25.9 0-44 15.12-44 36.78zm1e2 -26.78v-1e1c-25.9 0-44 15.12-44 36.78v47.22h44v-42h-34v-5.22c0-18.5 17.08-26.78 34-26.78z"></path></svg><span class="button-text">Cite</span></button>
                </div>
                <div id="popover-content-export-citation-popover" class="popover-content popover-align-left u-js-hide" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children"><ul class="export-options-list link"><li class="u-margin-xs-bottom"><form action="{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}/RefManCitationPlugin" aria-label="Refworks"><button class="button-link button-link-secondary citation-type button-link-icon-left button-link-has-colored-icon" aria-label="ris" type="submit"><svg focusable="false" viewBox="0 0 54 128" height="20" class="icon icon-navigate-right"><path d="M1 99l38-38L1 23l7-7 45 45-45 45z"></path></svg><span class="button-link-text-container"><span class="button-link-text">Save to Refworks</span><svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg></span></button></form></li><li class="u-margin-xs-bottom"><form action="{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}/ProCiteCitationPlugin"><input type="hidden" name="format" value="application/x-research-info-systems"><input type="hidden" name="withabstract" value="true"><button class="button-link button-link-secondary citation-type button-link-icon-left button-link-has-colored-icon" aria-label="ris" type="submit"><svg focusable="false" viewBox="0 0 54 128" height="20" class="icon icon-navigate-right"><path d="M1 99l38-38L1 23l7-7 45 45-45 45z"></path></svg><span class="button-link-text-container"><span class="button-link-text">Export citation to RIS</span></span></button></form></li><li class="u-margin-xs-bottom"><form action="{url page="rt" op="captureCite" path=$article->getBestArticleId()|to_array:$galleyId}/BibtexCitationPlugin"><input type="hidden" name="format" value="text/x-bibtex"><input type="hidden" name="withabstract" value="true"><button class="button-link button-link-secondary citation-type button-link-icon-left button-link-has-colored-icon" aria-label="bibtex" type="submit"><svg focusable="false" viewBox="0 0 54 128" height="20" class="icon icon-navigate-right"><path d="M1 99l38-38L1 23l7-7 45 45-45 45z"></path></svg><span class="button-link-text-container"><span class="button-link-text">Export citation to BibTeX</span></span></button></form></li><li><form action="{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}/EndNoteCitationPlugin"><input type="hidden" name="format" value="text/plain"><input type="hidden" name="withabstract" value="true"><button class="button-link button-link-secondary citation-type button-link-icon-left button-link-has-colored-icon" aria-label="text" type="submit"><svg focusable="false" viewBox="0 0 54 128" height="20" class="icon icon-navigate-right"><path d="M1 99l38-38L1 23l7-7 45 45-45 45z"></path></svg><span class="button-link-text-container"><span class="button-link-text">Export citation to text</span></span></button></form></li></ul></div></div></div>
                <a href="javascript:openRTWindow('{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}');"></a>
            </div>
        </div>
    </div>
</div>

<div class="DoiLink u-font-sans-sang" id="doi-link">
    {foreach from=$pubIdPlugins item=pubIdPlugin}
    {if $issue->getPublished()}
        {assign var=pubId value=$pubIdPlugin->getPubId($pubObject)}
    {else}
        {assign var=pubId value=$pubIdPlugin->getPubId($pubObject, true)}{* Preview rather than assign a pubId *}
    {/if}
    {if $pubId}
        {if $pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}<a class="anchor doi anchor-default anchor-external-link" target="_blank" rel="noreferrer noopener" aria-label="Persistent link using digital object identifier" title="Persistent link using digital object identifier" href="{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}"><span class="anchor-text">{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}</span></a>{else}<a rel="noreferrer noopener" href="javascript:void(0)">DOI not available</a>{/if}
    {else}
    <a href="https://doi.org/{$article->getPubId('doi')}" class="anchor doi anchor-default anchor-external-link" target="_blank" rel="noreferrer noopener" title="Persistent link using digital object identifier"><span class="anchor-text">https://doi.org/{$article->getPubId('doi')}</span></a>
    {/if}
    {/foreach}
    <a class="anchor rights-and-content anchor-default anchor-external-link" rel="noreferrer noopener" href="{url page="about" op="submissions" anchor="copyrightNotice"}" target="_blank"><span class="anchor-text">Get rights and content</span></a>
</div>

<div class="LicenseInfo u-font-sans-sang">
    <div class="License"><span>Under a Creative Commons </span><a class="anchor anchor-default anchor-external-link" target="_blank" rel="noreferrer noopener" href="{$article->getLicenseURL()|escape}"><span class="anchor-text">license</span></a></div>
    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN}
    <div class="OpenAccessLabel">Open Access<span class="access-indicator"></span></div>        
    {/if}
</div>

<section class="ReferencedArticles"></section>
<div class="PageDivider"></div>

{if $article->getLocalizedAbstract(null) && $article->getAbstract(null)}
<div id="abstracts" class="Abstracts u-font-serif">
    {if $article->getLocalizedSubject(null)}
    <div id="ab810" class="abstract author-highlights u-js-hide" lang="{$metaLocale|String_substr:0:2|escape}">
        <h2 class="u-fonts-serif u-h4 u-margin-l-top u-margin-xs-bottom">Highlights</h2>
        <div id="as710">
            <div id="sp1310" class="Abstracts highlights u-fonts-serif">
                <ul class="non-list">
                    <li class="react-xocs-list-item">
                        <span id="p2205"></span>
                    </li>
                </ul>
            </div>
        </div>
        <p class="highlights u-font-sans" style="margin: 0;font-size: 14px;text-align: end;">Generate NLP AI by Wizdam ID.</p>
    </div>
    {/if}
    {foreach from=$article->getAbstract(null) key=metaLocale item=metaValue}
    <div id="ab005" class="abstract author" lang="{$metaLocale|String_substr:0:2|escape}">
        {if $article->getLocalizedSubject(null)}<h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="article.abstract" from="$metaLocale"}</h2>{/if}
        <div id="abs005"><p id="sp0005">{$metaValue|strip_unsafe_html|nl2br}</p></div>
    </div>
    {/foreach}
    {if $coverPagePath}
    <div id="ab0010" class="abstract graphical u-font-serif">
        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Graphical abstract</h2>
        <div id="abssec0050">
            <p id="abspara0050"><span class="display">
                <figure id="undfig1" class="figure text-xs">
                    <span><img loading="lazy" src="{$coverPagePath|escape}{$coverPageFileName|escape}" alt="{$coverPageAltText|escape}" {if $width} width="{$width|escape}"{/if}{if $height} height="250"{/if}></span>
                    {if $coverPageAltText != ''}
                    <span class="captions"><span id="cn0005"><p id="sp0010">{translate key="article.coverPage.altText"}</p></span></span>
                    {/if}
                </figure>
            </span></p>
        </div>
    </div>
    {/if}
    {call_hook name="Templates::Article::Article::ArticleCoverImage"}    
</div>
{/if}

<ul id="issue-navigation" class="issue-navigation u-margin-s-bottom u-bg-grey1">
    <li class="previous move-left u-padding-s-ver u-padding-s-left">
        {if $prevArticle}
        <a class="button-alternative button-alternative-tertiary button-alternative-icon-right" href="{url page="article" op="view" path=$prevArticle->getId()}" title="{$prevArticle->getLocalizedTitle()|truncate:170|escape} - {$prevArticle->getAuthorString()}"><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-left"><path d="m1 61l45-45 7 7-38 38 38 38-7 7z"></path></svg><span class="button-alternative-text"><strong>Previous </strong><span class="extra-detail-1">article</span><span class="extra-detail-2"> in issue</span></span></a>
        {else}
        <button class="button-alternative button-alternative-tertiary button-alternative-icon-right disabled" href="javascript:void(0);" disabled="" type="button"><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-left"><path d="m1 61l45-45 7 7-38 38 38 38-7 7z"></path></svg><span class="button-alternative-text"><strong>Previous </strong><span class="extra-detail-1">article</span><span class="extra-detail-2"> in issue</span></span></button>
        {/if}
    </li>
    <li class="next move-right u-padding-s-ver u-padding-s-right">
        {if $nextArticle}
        <a class="button-alternative button-alternative-tertiary button-alternative-icon-right" href="{url page="article" op="view" path=$nextArticle->getId()}" title="{$nextArticle->getLocalizedTitle()|truncate:170|escape} - {$nextArticle->getAuthorString()}"><span class="button-alternative-text"><strong>Next </strong><span class="extra-detail-1">article</span><span class="extra-detail-2"> in issue</span></span><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg></a>
        {else}
        <button class="button-alternative button-alternative-tertiary button-alternative-icon-right disabled" href="javascript:void(0);" disabled="" type="button"><span class="button-alternative-text"><strong>Next </strong><span class="extra-detail-1">article</span><span class="extra-detail-2"> in issue</span></span><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg></button>
        {/if}
    </li>
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

{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION || $subscribedUser || $subscribedDomain)}
    {assign var=hasAccess value=1}
{else}
    {assign var=hasAccess value=0}
{/if}

{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION && $subscriptionRequired}

    <div id="body" class="Body u-font-serif"></div>
    
    {if $galleys}
        {if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
            {foreach from=$article->getGalleys() item=galley name=galleyList}
            {if $galley->isHTMLGalley()}
                {$galley->getHTMLContents()} <!-- Begin fulltext HTML -->
            {/if}
            {/foreach}
        {else}
            &nbsp;<a rel="noreferrer noopener" href="{url page="about" op="subscriptions"}" target="_parent">{translate key="reader.subscribersOnly"}</a>
        {/if}
    {/if}
    
{else}
    
    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
    
        {if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain)}
    		{assign var=hasAccess value=1}
    	{else}
    		{assign var=hasAccess value=0}
    	{/if}
        
        {foreach from=$article->getGalleys() item=galley name=galleyList}
        {if $galleys && $hasAccess || !$galley->isHTMLGalley() || ($subscriptionRequired && !$showGalleyLinks) && $restrictOnlyPdf}
        {if $galleys && $galley->isPdfGalley()}
        <div class="PdfEmbed" role="region" aria-label="PDF viewer">
            {include file="article/pdfViewer.tpl"}
            <div class="u-margin-s-ver medium-bar"><a class="anchor" href="{url op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank"><svg focusable="false" viewBox="0 0 32 32" width="32" height="32" class="icon icon-pdf-multicolor"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg><span class="anchor-text u-font-sans">Download full text in {$galley->getLabel()|escape}</span></a>
            </div>
        </div>
        {elseif $galleys && !$galley->isHTMLGalley()}
            <div class="PdfPreview">
                <h3 class="pdf-preview-heading u-font-sans">First page preview</h3>
                <a href="{url page="article" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}" rel="nofollow noreferrer noopener" target="_blank" class="image-pdf-preview-link">
                    <div class="preview-link-text u-hide-from-lg">Open this preview in {$galley->getLabel()}</div>
                    <div class="image-preview-container">
                        <img alt="" src="{url page="article" op="view" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}">
                        <div class="first-page-hover-overlay"><div class="first-page-overlay-content"><span class="icon svg-search"></span>Click to open first page preview</div></div>
                    </div>
                    <div class="preview-link-text u-show-from-lg">Open this preview in {$galley->getLabel()}</div>
                </a>
            </div>
        {/if}{/if}
        {/foreach}
    
    {else}
    
        {if $article->getLocalizedAbstract(null) && $article->getLocalizedSubject(null)}
        <div id="body" class="Body text-pdf u-font-serif">
            <section id="preview-section-introduction" class="u-js-hide">
                <div class="Introduction u-font-serif u-margin-l-ver">
                    <h2 class="section-title u-h3 u-margin-s-bottom">Introduction</h2>
                    <section id="sec1">Introduction from the full-text PDF of this article cannot be displayed.</section>
                </div>
            </section>
            
            <section id="preview-section-snippets"  class="u-js-hide">
                <div class="PageDivider"></div>
                <section class="Snippets u-font-serif">
                    <h2 class="section-title u-h3 u-margin-l-ver">Section snippets</h2>
                    <section>
                        <section id="sec2" class="section-material-methods">
                            <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Material and Methods</h2>
                            <p id="p0070">Materials and methods from the full-text PDF of this article cannot be displayed.</p>
                        </section>
                        <section id="sec30" class="section-results">
                            <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Results</h2>
                            <p id="p0083">Results from the full-text PDF of this article cannot be displayed.</p>
                        </section>
                        <section id="sec31" class="section-discussion">
                            <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Discussion</h2>
                            <p id="p0083">Discussion from the full-text PDF of this article cannot be displayed.</p>
                        </section>
                    </section>
                </section>
            </section>
            
            <section id="sec4" class="section-conclusions u-js-hide">
                <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Conclusions</h2>
                <p id="p0317">Conclusions from the full-text PDF of this article cannot be displayed.</p>
            </section>
                        
            <section id="ack0010" class="section-acknowledgment u-js-hide">
                <h2 id="sectitle0200" class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Acknowledgment</h2>
                <p id="p0350">Acknowledgment from the full-text PDF of this article cannot be displayed.</p>
            </section>
            
            {if $article->getLocalizedSponsor()}
            <section id="fund0020" class="section-funding Agencies Body">
                <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Funding Information</h2>
                <p id="p0450">{$article->getLocalizedSponsor()|escape}</p>
            </section>
            {/if}
                
            {if $article->getLocalizedAbstract(null) && $article->getLocalizedSubject(null)}
                <section id="coi0025" class="section-coi Declaration u-font-serif">
                    <h2 id="st0005" class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Competing interest</h2>
                    <p id="p0125">The authors declare that they have no known competing financial interests or personal relationships that could have appeared to influence the work reported in this paper.</p>
                </section>
                
                {assign var="articleYear" value=$article->getDatePublished()|date_format:"%Y"}
                {if $articleYear >= 2024}
                <section id="dGAI0115" class="section-declareGAI Body Declaration u-font-serif">
                    <h2 id="st0115" class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Declaration of generative AI and AI-assisted</h2>
                    <p id="p0310">During the preparation of this work the authors not used any AI tools like ChatGPT 4, DeepSeek R1 or the others in order to improve the readability and language of the manuscript.</p>
                </section>
                {/if}
                
                <section id="scoi0025" class="Body Declaration u-font-serif u-hide">
                    <h2 id="st0025" class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Conflict of interest</h2>
                    <p id="p0310">The authors declare that the research was conducted in the absence of any commercial or financial relationships that could be construed as a potential conflict of interest.</p>
                </section>
                
                <section id="s0135" class="Body Declaration u-font-serif">
                    <h2 id="st0190" class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Ethical approval acknowledgements</h2>
                    <p id="p0310">No ethical approval required for this article. All procedures followed were in accordance with the ethical standards of the responsible committee on human experimentation (institutional and national) and with the Helsinki Declaration of 1975, as revised in 2008 (5)</p>
                </section>
            
                {if $article->getSuppFiles()}
                <section id="s0145">
                    <h2 id="st0200" class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Data availability</h2>
                    <p id="p0320">All the data have been provided via Supplementary files. Any further information can be requested from the corresponding author via email upon reasonable request.</p>
                </section>
                {/if}
            
                {if $journalRt->getSupplementaryFiles() && is_a($article, 'PublishedArticle') && $article->getSuppFiles()}
                <section id="SuppFiles" class="Body u-font-serif">
                    <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Appendix. Supplementary materials</h2>
                    <span class="download-all-supplemental-data"><span class="article-attachment"><span class="button-link button-link-secondary u-font-sans" type="submit"><svg focusable="false" viewBox="0 0 98 128" width="18.375" height="24" class="icon icon-download"><path d="m77.38 56.18l-6.6-7.06-16.78 17.24v-40.36h-1e1v40.34l-17.72-17.24-7.3 7.08 29.2 29.32 29.2-29.32m10.62 17.82v2e1h-78v-2e1h-1e1v3e1h98v-3e1h-1e1"></path></svg><span class="button-link-text"><span class="download-all-title">Download all </span>supplementary files<span class="desktop-text"> included with this article</span></span></span><a class="anchor anchor-external-link help-link move-right u-font-sans u-margin-0-bottom" href="{url page="information" op="authors"}" title="Help (Opens in new window)" target="_blank"><span class="anchor-text">Help</span></a></span>
                    </span>
                    
                    {foreach from=$article->getSuppFiles() item=suppFile key=key}
                    <div id="SuppFile-{$key+1}" class="supplement-files--value e-component">
                        <span class="article-attachment">
                            <a class="icon-link" title="Download Word document ({$suppFile->getNiceFileSize()|escape})" rel="noreferrer noopener" href="{url page="article" op="downloadSuppFile" path=$article->getBestArticleId()|to_array:$suppFile->getBestSuppFileId($currentJournal)}" target="_blank"><svg focusable="false" viewBox="0 0 94 128" width="17.625" height="24" class="icon icon-text-document"><path d="m35.6 1e1c-5.38 0-10.62 1.92-14.76 5.4-9.1 7.68-18.84 20.14-18.84 32.1v70.5h9e1v-15.99-2.01-4e1 -17.64-32.36h-56.4zm0 1e1h46.4v22.36 17.64 4e1 2.01 5.99h-7e1v-49c0-6.08 4.92-11 11-11h17v-2e1h-6c-2.2 0-4 1.8-4 4v6h-7c-3.32 0-6.44 0.78-9.22 2.16 2.46-5.62 7.28-11.86 13.5-17.1 2.34-1.98 5.3-3.06 8.32-3.06zm-13.6 38v1e1h5e1v-1e1h-5e1zm0 2e1v1e1h5e1v-1e1h-5e1z"></path></svg></a><a title="Download Word document ({$suppFile->getNiceFileSize()})" rel="noreferrer noopener" href="{url page="article" op="downloadSuppFile" path=$article->getBestArticleId()|to_array:$suppFile->getBestSuppFileId($currentJournal)}" class="anchor download-link u-font-sans" target="_blank"><span class="anchor-text">Download: <span class="download-link-title">{$suppFile->getOriginalFileName()|escape} ({$suppFile->getNiceFileSize()|escape})</span></span></a>
                        </span>                     
                        <div class="display" {if $suppFile->getSuppFileDescription()} style="background-color:#ebebeb;padding-top:1.7em"{/if}>
                            <span class="captions" style="margin-top:0;">
                                <p class="supplement-files-title supplement-files--label u-h4 download-link">Supplementary File {$key+1}{if $suppFile->getSuppFileTitle()}<span>: {$suppFile->getSuppFileTitle()|escape}</span>{/if}
                                    <span class="u-font-sans" style="font-size: initial;">{if $suppFile->getType()|escape}({$suppFile->getType()|escape}{elseif $suppFile->getSuppFileTypeOther()}; {translate key="common.type"}{$suppFile->getSuppFileTypeOther()|escape}{else}; {translate key="common.type"} {translate key="common.other"}{/if})</span>
                                </p>
                                <div class="contents u-font-sans">
                                    {if $suppFile->getSuppFileCreator()}
                                    <p class="files-owner u-font-sans"><span class="italic">Owner: </span> {$suppFile->getSuppFileCreator()|escape}
                                    </p>
                                    {/if}
                                    {if $suppFile->getSuppFileSponsor()}
                                    <p class="files-sponsor u-font-sans"><span class="italic">Sponsor </span>{$suppFile->getSuppFileSponsor()|escape}
                                    </p>
                                    {/if}
                                    {if $suppFile->getSuppFilePublisher()}
                                    <p class="files-publisher u-font-sans"><span class="italic">{translate key="common.publisher"} </span>{$suppFile->getSuppFilePublisher()|escape}
                                    </p>
                                    {/if}
                                </div>
                                {if $suppFile->getSuppFileDescription()}
                                <div class="u-hide supplement-files-value u-font-sans">{$suppFile->getSuppFileDescription()|strip_unsafe_html|nl2br} {if $suppFile->isInlineable() || $suppFile->getRemoteURL()}{/if}{if !$suppFile->getRemoteURL()}{/if}
                                </div>
                                {/if}
                            </span>
                        </div>
                    </div>
                    {/foreach}
                </section>
                <section id="Declaration" class="Body Declaration u-font-serif">
                    <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">Declarations information</h2>        
                    <div id="copyrightBadge" class="u-hide Body u-margin-s-bottom">
                        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">License and permission</h2>
                        <p>{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if} {$article->getLicense()|escape} (<a href="{$article->getLicenseURL()|escape}" rel="license" class="anchor"><span class="anchor-text">{$article->getLicenseURL()|escape}</span></a>), {translate key="submission.license.Statement1"}</p>                
                    </div>
                    <div id="PublisherName" class="Body">
                        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="rt.metadata.dublinCore.publisher"}'s Note</h2>
                        <p>{if $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')|escape}{else}Sangia Publishing{/if} remains neutral with regard to jurisdictional claims in published maps and institutional affiliations.</p>
                    </div>
                </section>
                {else}
                <section id="SuppFiles" class="Body u-font-serif">
                    <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="rt.suppFiles"}</h2>
                    <p>{translate key="author.submit.suppFile.noFile"}</p>
                </section>
                {/if}
            {/if}
        </div>
        {/if}
    {/if} {* omit authors *} <!-- end omit authors -->
{/if} {* article access subscription *} <!-- end open access -->

<div class="Tail"></div>
        
{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
    {assign var=hasAccess value=1}
{else}
    {assign var=hasAccess value=0}
{/if}

{if $hasAccess || ($subscriptionRequired)}
    {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION && $subscriptionRequired}
    
    <subscribe>
        
    </subscribe>
        
    {else}

    {if $section && $section->getLocalizedIdentifyType() == "Erratum" || $section->getLocalizedIdentifyType() == "Retraction notice" || $section->getLocalizedIdentifyType() == "Corrigendum" || $section->getLocalizedIdentifyType() == "Correction"}
    
        {foreach from=$article->getGalleys() item=galley name=galleyList}
        {if $galleys && $hasAccess || ($subscriptionRequired && $showGalleyLinks) || $article->getLocalizedAbstract(null) && $article->getLocalizedSubject(null)}
        <div class="PdfEmbed" role="region" aria-label="PDF viewer">
            {include file="article/pdfViewer.tpl"}
            <div class="u-margin-s-ver medium-bar"><a class="anchor" href="{url op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank"><svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg><span class="anchor-text">Download full text in {$galley->getLabel()|escape}</span></a>
            </div>
        </div>
        {/if}
        {/foreach}
    {/if}
    
    {/if}
    
{/if}

{if $citationFactory && $citationFactory->getCount() > 0}
    <div class="PageDivider"></div>
    <section id="References" class="bibliography u-font-serif text-m">
        <header>
            <h2 class="section-title u-h3 u-margin-l-top u-margin-1-bottom">
                <span>{translate key="submission.citations"}</span>
                <span class="count">({$citationFactory->getCount()|escape})</span>
            </h2>
        </header>
        <section class="ref-bibliography">
            {iterate from=citationFactory item=citation}
            <p id="ref-sangia{$citation->getId()|escape}" class="reference">{$citation->getRawCitation()|strip_unsafe_html}</p>
            {/iterate}
            <div class="notice u-font-sans u-mb-0 u-margin-xl-top">There are more references available in the full text version of this article.</div>
            <button class="button-alternative view-more button-alternative-secondary u-margin-xl-top">
                <svg focusable="false" viewBox="0 0 92 128" height="34" width="34" class="icon icon-navigate-down" style="transform: rotate(0deg);"><path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path></svg>
                <span class="button-alternative-text-container text-m">
                    <span class="button-alternative-text">View more references</span>
                </span>
            </button>
        </section>
    </section>
{/if}

{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
 <!-- Rectracted article -->
{elseif $article->getLocalizedSubject(null) && $article->getLocalizedAbstract(null)}
<section id="CopyrightNote" class="further-reading">    
    <div class="PageDivider"></div>
    <div id="additionalNotes" class="additionalNotes">
        <h2 class="section-title sub-title u-h3">Bibliographic Information</h2>
        <div id="bibliometricts-info" class="bibliographic-information col-lg-12">
            <div class="crossmark col-lg-12">
                <span class="bibliometricts">
                    <div class="crosmark__adjacent c-bibliographic-information__column embed">
                    <!-- Start Crossmark Snippet v2.0 -->
                    <link rel="preload" href="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js" as="script">
                            <script src="https://crossmark-cdn.crossref.org/widget/v2.0/widget.js"></script>
                    <a rel="noreferrer noopener" data-target="crossmark" class="u-font-sans"><img class="lazyload" alt="Verify authenticity via CrossMark" src="//assets.sangia.org/img/crossmark.png" width="57" height="81" loading="lazy" /></a>
                    <!-- End Crossmark Snippet -->
                    </div>
                </span>
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
                    {assign var=lastName value=$author->getLastName()}
                    {$lastName|escape}{if $firstName !== $lastName}, {$firstName|escape|truncate:1:".":true}{/if}{$middleName|escape|truncate:1:".":true}{if $i==$authorCount-2}, &amp; {elseif $i<$authorCount-1}, {/if}{/foreach}, 
                    {if $article->getDatePublished()}{$article->getDatePublished()|date_format:'%Y'}{elseif $issue->getDatePublished()}{$issue->getDatePublished()|date_format:'%Y'}{else}{$issue->getYear()|escape}{/if}. {$article->getLocalizedTitle()|strip_unsafe_html|nl2br}. <em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</em>&nbsp;{if $currentJournal}{$issue->getVolume()|strip_tags|escape}({$issue->getNumber()|escape}): {if $article->getPages()}{$article->getPages()|escape}{else}{$article->getId()|escape}{/if}{/if}. {assign var="doi" value=$article->getStoredPubId('doi')}{if $article->getPubId('doi')}<a class="anchor" rel="noreferrer noopener" title="Permanent link for this article" href="https://doi.org/{$article->getPubId('doi')|escape}"><span class="anchor-text">https://doi.org/{$article->getPubId('doi')|escape}</span></a>{/if}
                    </div>
                </div>

                <ul class="c-bibliographic-information__list">
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Submitted</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getDateSubmitted()|date_format:"%e %B %Y"}</span>
                    </li>
                    {if $revisionDate}
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Revised</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$revisionDate|date_format:"%e %B %Y"}</span>
                    </li>
                    {/if}
                    {if $acceptedDate}
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Accepted</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$acceptedDate|date_format:"%e %B %Y"}</span>
                    </li>
                    {/if}
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Published</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getDatePublished()|date_format:"%e %B %Y"}</span>
                    </li>
                    {if $article->getLastModified()}
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Version of record</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getLastModified()|date_format:"%e %B %Y"}</span>
                    </li>
                    {/if}
                    <li class="c-bibliographic-information__list-item">
                        <h5 class="strong u-font-serif">Issue date</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$issue->getDatePublished()|date_format:"%e %B %Y"}</span>
                    </li>
                </ul>

                <ul class="c-bibliographic-information__list">            
                    {if $article->getLocalizedDiscipline()}    
                    <li class="c-bibliographic-information__item Discipline">
                        <h5 class="strong u-font-serif">{translate key="article.discipline"}</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getLocalizedDiscipline()|escape}</span>
                    </li>{/if}    
                    {if $article->getLocalizedSubjectClass()}
                    <li class="c-bibliographic-information__item Subject">
                        <h5 class="strong u-font-serif">Sub-{translate key="article.subjectClassification"}</h5>
                        <span class="c-bibliographic-information__value u-font-sans">{$article->getLocalizedSubjectClass()|escape}</span>
                    </li>{/if}
                </ul>        
                    
                <div class="c-bibliographic-information__list">
                    {assign var="doi" value=$article->getStoredPubId('doi')}
                    {if $article->getPubId('doi')}
                    <div class="">
                        <h2 class="strong u-font-serif">DOI</h2>
                        <span class="c-bibliographic-information__value u-font-sans"><a class="anchor" rel="noreferrer noopener" title="Permanent link for this article" href="https://doi.org/{$article->getPubId('doi')|escape}"><span class="anchor-text">https://doi.org/{$article->getPubId('doi')|escape}</span></a></span>
                    </div>
                    {/if}
                </div>
    
                {if $article->getLocalizedSubject()}
                <div class="c-article__heading">
                    <h4 class="c-article__sub-heading u-font-sans-sang">{translate key="article.subject"}</h4>
                    <ul class="c-article-subject-list u-font-sans">
                        {if $article->getSubject(null)}{foreach from=$article->getSubject(null) key=metaLocale item=metaValue}
                        {foreach from=$metaValue|explode:"; " item=dcSubject}
                        <li class="c-article-subject-list__subject u-font-sans">
                            <span itemprop="about">{if $dcSubject}<span class="subjectId--value" title="Go to Google Scholar"><a rel="noreferrer noopener" class="anchor q-gs q-cf" href="//scholar.google.com/scholar?q={$dcSubject|strip_tags|escape}" target="_blank"><span class="anchor-text">{$dcSubject|strip_unsafe_html|nl2br}</span></a></span>{/if}</span></li>
                        {/foreach}{/foreach}
                        {/if}
                    </ul>
                </div>
                {/if}
            </div>
        </div>
    </div>

    <div id="copyright" class="Body u-font-serif u-hide">
        <h2 class="section-title u-h3 u-margin-l-top u-margin-xs-bottom">{translate key="submission.copyright}</h2>
        <div class="Body u-font-serif">
        {if $currentJournal->getSetting('includeCopyrightStatement')}
        {translate key="submission.copyrightStatement" copyrightYear=$article->getCopyrightYear()|strip_unsafe_html|nl2br copyrightHolder=$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}{/if}</div>
    </div>
</section>    
{/if}

<div class="js-ad u-mb-32">
    <aside data-component-mpu="" class="adsbox c-ad c-ad--970x90 u-mt-16 u-mb-16">
        <div class="c-ad__inner">
            <p class="c-ad__label">Sangia Advertisement</p>
            {literal}
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
            <!-- Sangia_Publishing_ads -->
            <ins class="adsbygoogle c-ad--300x250"
                style="display:inline-block;width:300px;height:auto"
                data-ad-client="ca-pub-8416265824412721"
                data-ad-slot="2738201692">
            </ins>
            <script>
                 (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
            {/literal}
        </div>
    </aside>
</div>


{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
<div class="article-biography author-group"></div>
{else}
<div class="authors-group">

    {foreach from=$authors item=author name=authors}
        {assign var=authorEmail value=$author->getEmail()}
        {assign var=authorOrcid value=$author->getData('orcid')}
        {assign var=authorSalutation value=$author->getSalutation()}
        {assign var=fullname value=$author->getFullName()}
        {assign var=authorFirstName value=$author->getFirstName()}
        {assign var=authorMiddleName value=$author->getMiddleName()}
        {assign var=authorLastName value=$author->getLastName()}
        {assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
        {assign var=authorCountry value=$author->getCountry()}
        {assign var=authorFax value=$author->getData('fax')}
        
        {* Cari user berdasarkan ORCID atau email *}
        {assign var="currentAuthorId" value=$author->getId()}
        
        {* Tentukan apakah author memiliki gambar *}
        {assign var="profileImageData" value=$authorProfileImages[$currentAuthorId]}
        
        {* Gunakan variabel $profileImageData *}
        {assign var="hasImage" value=$profileImageData}

        <div class="article-biography author-group{if $hasImage} article-biography-has-image{/if}" id="vt00{$smarty.foreach.authors.index+5}">
            {if $hasImage}
                <div class="article-biography-image">
                    {if $profileImageData.uploadName}
                        <img class="lazyload" loading="lazy" title="{$authorFirstName|escape} {if $authorMiddleName} {$authorMiddleName|escape}{/if} {$authorLastName|escape}" src="{$sitePublicFilesDir}/{$profileImageData.uploadName}" alt="{$authorFirstName|escape} {if $authorMiddleName} {$authorMiddleName|escape}{/if} {$authorLastName|escape}" height="156" />
                    {/if}
                </div>
            {/if}
            <div class="article-biography-text">
                <p id="spar00{$smarty.foreach.authors.index+15}" class="author{if !$hasImage} u-mb-0{/if}">{$authorSalutation|escape}<strong><span class="content">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName|escape}</span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape}</span>{/if}<span class="text surname">{$authorLastName|escape},</span></span></strong>{if $author->getLocalizedBiography()} {$author->getLocalizedBiography()|strip_unsafe_html|nl2br}. {/if} {$authorAffiliation|escape}{if $author->getCountry()}, {$author->getCountryLocalized()|escape}{/if}.</p>
                {if $hasImage}<p class="author-link external-link">{else}<span class="author-link external-link">{/if}
                    <a rel="noreferrer noopener" class="anchor icon" title="{$fullname|escape} mail: {$author->getData('email')|escape}"><span class="anchor-text"><svg class="icon icon-envelope" width="14" height="10" viewBox="0.741 0 13 10"><path fill="#7c716a" d="M13.741 0L7.24 5.121.74 0zM.742 1.714L.74 10h6.502l-.001-3.165zm6.501 5.121L7.242 10h6.499V1.714z" alt="mail"></path></svg><span class="anchor-text--mail">{$author->getData('email')|escape}</span></span></a>{if $author->getData('orcid')}<a class="anchor anchor-external-link text-bar" rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$author->getData('orcid')|escape}" target="_blank" class="icon extern"><span class="anchor-text"><img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" width="14" height="14" alt="orcid" /><span class="anchor-text text">Orcid Profile</span></span></a>{/if}{if $author->getUrl()}<a rel="noreferrer noopener" title="Go to view {$fullname|escape} Google Scholar profile" href="{$author->getUrl()|escape}" target="_blank" class="icon extern anchor anchor-external-link text-bar"><span class="anchor-text"><img class="lazyload" src="//assets.sangia.org/img/scholar.svg" width="16" height="16" alt="scholar"/><span class="anchor-text text">Google Scholar Profile</span></span></a>{/if}{if $author->getData('fax')}<a title="Go to Scopus profile of {$author->getFullName()|escape}" href="https://www.scopus.com/authid/detail.uri?authorId={$author->getData('fax')|escape}" target="_blank" class="scopus anchor anchor-external-link text-bar"><span class="anchor-text"><img class="lazyload" src="//assets.sangia.org/img/scholar.svg" width="20" height="20" alt="scholar" /><span class="anchor-text text">{$author->getData('fax')|escape}</span></span></a>{/if}{if $author->getData('phone')}<a title="Go to Sinta profile of {$author->getFullName()|escape}" href="https://sinta.kemdikbud.go.id/authors/detail?id={$author->getData('phone')|escape}&view=overview" target="_blank" class="sinta anchor anchor-external-link text-bar"><span class="anchor-text"><img class="lazyload" src="//assets.sangia.org/img/scholar.svg" width="20" height="20" alt="scholar" /><span class="anchor-text text">{$author->getData('phone')|escape}</span></span></a>{/if}{if $hasImage}</p>{else}</span>{/if}
            </div>
        </div>
    {/foreach}
</div>
{/if}
            
{if $galley}
    {if $galley->isHTMLGalley()}
    
    {if $issue->getLocalizedDescription()}
    <div id="article-footnote-id1" class="Footnotes"><dl class="footnote"><dt class="footnote-label"><sup>{if $issue->getShowTitle()}<a rel="noreferrer noopener" href="#special-issue-articles">☆</a>{else}<a rel="noreferrer noopener" href="#special-issue-articles"></a>{/if}</sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}
    {if $issue->getShowTitle()}
    <div id="article-footnote-id1" class="Footnotes u-hide"><dl class="footnote"><dt class="footnote-label"><sup><a rel="noreferrer noopener" href="#special-issue-articles">☆</a></sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}    
    
    {if $galley && $galley->isHTMLGalley()}
    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans" href="{url page="article" op="view" path=$articleId}" aria-disabled="false" tabindex="1"><span class="anchor-text">View Abstract</span></a>
    {else}
    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" aria-disabled="false" tabindex="1"><span class="anchor-text">View full text</span></a>
    {/if}
    
    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
    {else}    
    <div id="permission" class="Copyright">
        <p class="copyright-line u-font-sans-sang">{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}<span class="bold">Copyright</span> {/if}{if $currentJournal->getSetting('includeCopyrightStatement')}© {$article->getCopyrightYear()|strip_unsafe_html|nl2br}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN} {$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}.{/if}{/if} {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosting by Sangia Publishing on behalf of Sangia Research Media. {elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing LLC"}Published by {$currentJournal->getSetting('publisherInstitution')} on behalf of Sangia Research Media. {elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}Published by {$currentJournal->getSetting('publisherInstitution')} on behalf of Sangia Research Media.{else}{$currentJournal->getSetting('publisherInstitution')}. Production and hosting by Sangia (SRM™).{/if}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN} <span class="License" id="copyrightBadge"><span class="anchor-text">{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if}{$article->getLicense()|escape}.</span></span>{/if}
        </p>
    </div>
    <p data-test-id="article-disclaimer-text"><span class="bold">Disclaimer: </span>All claims expressed in this article are solely those of the authors and do not necessarily represent those of their affiliated organizations, or those of the publisher, the editors and the reviewers. Any product that may be evaluated in this article or claim that may be made by its manufacturer is not guaranteed or endorsed by the publisher.</p>
    {/if}
    
    {else}{** <!-- End to fulltext HTML --> **}         

    {if $issue->getLocalizedDescription()}
    <div id="article-footnote-id1" class="Footnotes"><dl class="footnote"><dt class="footnote-label"><sup>{if $issue->getShowTitle()}<a rel="noreferrer noopener" href="#special-issue-articles">☆</a>{else}<a rel="noreferrer noopener" href="#special-issue-articles"></a>{/if}</sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}
    {if $issue->getShowTitle()}
    <div id="article-footnote-id1" class="Footnotes u-hide"><dl class="footnote"><dt class="footnote-label"><sup><a rel="noreferrer noopener" href="#special-issue-articles">☆</a></sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>{/if}    

    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans file-link" {if $galley && $galley->isHTMLGalley()}href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)|escape}" class="file" {if $galley->getRemoteURL()}target="_blank"{/if} aria-disabled="false" tabindex="1"{elseif $galley && $galley->isPdfGalley()}aria-disabled="true" tabindex="1"{/if}><span class="anchor-text">View full text</span></a>
    
    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
    {else}            
    <div id="permission" class="Copyright">
        <p class="copyright-line u-font-sans-sang">{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}<span class="bold">Copyright</span> {/if}{if $currentJournal->getSetting('includeCopyrightStatement')}© {$article->getCopyrightYear()|strip_unsafe_html|nl2br}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN} {$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}.{/if}{/if} {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosting by Sangia Publishing on behalf of Sangia Research Media. {elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing LLC"}Published by {$currentJournal->getSetting('publisherInstitution')|escape} on behalf of Sangia Research Media. {elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}Published by {$currentJournal->getSetting('publisherInstitution')|escape} on behalf of Sangia Research Media.{else}{$currentJournal->getSetting('publisherInstitution')|escape}. Production and hosting by Sangia (SRM™).{/if}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN} <span class="License" id="copyrightBadge"><span class="anchor-text">{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if}{$article->getLicense()|escape}.</span></span>{/if}
        </p>
    </div>
    <p data-test-id="article-disclaimer-text"><span class="bold">Disclaimer: </span>All claims expressed in this article are solely those of the authors and do not necessarily represent those of their affiliated organizations, or those of the publisher, the editors and the reviewers. Any product that may be evaluated in this article or claim that may be made by its manufacturer is not guaranteed or endorsed by the publisher.</p>
    {/if}

    {/if}   <!-- End fulltext with Galley PDF -->

{else}{** <!-- Galley not available begin --> **}
    {if $issue->getShowTitle() && $issue->getLocalizedDescription()}
    <div id="article-footnote-id1" class="Footnotes"><dl class="footnote"><dt class="footnote-label"><sup>{if $issue->getShowTitle()}<a rel="noreferrer noopener" href="#special-issue-articles">☆</a>{else}<a rel="noreferrer noopener" href="#special-issue-articles"></a>{/if}</sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>
    {/if}
    {if $issue->getShowTitle()}
    <div id="article-footnote-id1" class="Footnotes u-hide"><dl class="footnote"><dt class="footnote-label"><sup><a rel="noreferrer noopener" href="#special-issue-articles">☆</a></sup></dt><dd class="u-margin-xxl-left"><p id="np005">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p></dd></dl></div>
    {/if}

    <a rel="noreferrer noopener" class="anchor full-text-link u-font-sans file-link" aria-disabled="true" tabindex="-1"><span class="anchor-text">View full text</span></a>
    
    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
    {else}
    <div id="permission" class="Copyright">
        <p class="copyright-line u-font-sans-sang">{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}<span class="bold">Copyright</span> {/if}{if $currentJournal->getSetting('includeCopyrightStatement')}© {$article->getCopyrightYear()|strip_unsafe_html|nl2br}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN} {$article->getLocalizedCopyrightHolder()|strip_unsafe_html|nl2br}.{/if}{/if} {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosting by Sangia Publishing on behalf of Sangia Research Media. {elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing LLC"}Published by {$currentJournal->getSetting('publisherInstitution')|escape} on behalf of Sangia Research Media. {elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}Published by {$currentJournal->getSetting('publisherInstitution')|escape} on behalf of Sangia Research Media.{else}{$currentJournal->getSetting('publisherInstitution')|escape}. Production and hosting by Sangia (SRM™).{/if}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN} <span class="License" id="copyrightBadge"><span class="anchor-text">{if $ccLicenseBadge}{$ccLicenseBadge}{elseif $article->getLicenseURL()}{/if}{$article->getLicense()|escape}.</span></span>{/if}
        </p>
    </div>
    <p data-test-id="article-disclaimer-text"><span class="bold">Disclaimer: </span>All claims expressed in this article are solely those of the authors and do not necessarily represent those of their affiliated organizations, or those of the publisher, the editors and the reviewers. Any product that may be evaluated in this article or claim that may be made by its manufacturer is not guaranteed or endorsed by the publisher.</p>
    {/if}

{/if}{** -- Galley not available end -- **}

{** include file="article/capture_cite.tpl" **}

{include file="article/footer.tpl"}
