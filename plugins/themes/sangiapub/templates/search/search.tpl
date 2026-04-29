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

{call_hook name="Templates::Search::SearchResults::PreResults"}

{capture assign="filterInput"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName=$filterName filterValue=$filterValue}{/capture}

<div class="app-search-adv-filters" data-test="advanced-search-filters">    
    <span class="app-search-adv-filter__filter-container">Advanced filters: <span class="app-search-adv-filters__filter">"{if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{/if}"</span></span>
    <a class="app-search-adv-filters__link" href="{url page="search" op="titles"}" data-test="clear-advanced-filters">Clear advanced filters</a>
</div>

<section id="search-article-list" class="u-mb-48 u-mt-32" data-track-component="search grid">
	<div class="s-container">
		<ul class="app-article-list-row">
		{iterate from=results item=result}
			
			{assign var=publishedArticle value=$result.publishedArticle}
			{assign var=article value=$result.article}
			{assign var=issue value=$result.issue}
			{assign var=issueAvailable value=$result.issueAvailable}
			{assign var=journal value=$result.journal}
			{assign var=section value=$result.section}
				
			{if $publishedArticle->getGalleys()}	
			<li class="app-article-list-row__item 1">
				<div class="u-full-height" data-native-ad-placement="false">
					<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
						<div class="c-card__layout u-full-heights">
                            {if $publishedArticle->getLocalizedFileName() && $publishedArticle->getLocalizedShowCoverPage()}
                                {assign var=showCoverPage value=true}
                            {else}
                                {assign var=showCoverPage value=false}
                            {/if}
                            
                            {if $showCoverPage}
                            <div class="c-card__image">
                                <picture>
                                {if $currentJournal}
                                {* Konteks jurnal - path sudah benar *}
                                    <source type="image/webp" srcset="{$publicFilesDir}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 160w,{$publicFilesDir}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 290w">
                                    <img src="{$publicFilesDir}/{$publishedArticle->getLocalizedFileName()|escape}" alt="{$publishedArticle->getLocalizedCoverPageAltText()|escape}" itemprop="image">
                                {else}
                                {* Konteks site search - gunakan path journal spesifik *}
                                    <source type="image/webp" srcset="{$baseUrl}/public/journals/{$publishedArticle->getJournalId()}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 160w,{$baseUrl}/public/journals/{$publishedArticle->getJournalId()}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 290w">
                                    <img src="{$baseUrl}/public/journals/{$publishedArticle->getJournalId()}/{$publishedArticle->getLocalizedFileName()|escape}" alt="{$publishedArticle->getLocalizedCoverPageAltText()|escape}" itemprop="image">
                                {/if}
                                </picture>
                            </div>
                            {/if}
                            
							{call_hook name="Templates::Issue::Issue::ArticleCoverImage"}
							<div class="c-card__body u-display-flex u-flex-direction-column">
								<h3 class="c-card__title" itemprop="name headline">
									<a class="c-card__link u-link-inherit" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId()}" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$publishedArticle->getLocalizedTitle()|strip_unsafe_html}</a>
								</h3>
								{if $publishedArticle->getLocalizedAbstract()}
								<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p>{$publishedArticle->getLocalizedAbstract()|nl2br}</p></div>
								{/if}
								
								{if (!$publishedArticle->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $publishedArticle->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
								{else}
								<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list">{foreach from=$publishedArticle->getAuthors() item=authorItem name=authorList}<li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name">{$authorItem->getFullName()|escape}</span>{if $authorItem->getFirstName() !== $authorItem->getLastName()}<span itemprop="name">{$authorItem->getFirstName()|escape}</span>{/if}{if $authorItem->getMiddleName()}<span itemprop="name">{$authorItem->getMiddleName()|escape}</span>{/if}<span itemprop="name">{$authorItem->getLastName()|escape}</span></li>{/foreach}
								</ul>
								{/if}
							</div>
						</div>
						<div class="c-card__section c-meta">
							<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
								<span class="c-meta__type">{if $issue->getPublished() && $section && $journal}{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$publishedArticle->getSectionTitle()|strip_tags|escape}{/if}{/if}</span>
							</span>

                            {* Versi paling sederhana tanpa operator yang bermasalah *}
                            {if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
                            <span class="c-meta__item c-meta__item--block-at-lg 1" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            {elseif $issue && $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN}
                            <span class="c-meta__item c-meta__item--block-at-lg 2" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            {elseif $currentJournal && $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                            <span class="c-meta__item c-meta__item--block-at-lg 3" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            {/if}

							<time class="c-meta__item c-meta__item--block-at-lg" datetime="{$publishedArticle->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$publishedArticle->getDatePublished()|date_format:"%d %b %Y"}</time>

							{if !$currentJournal}
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a>
							</div>
							{else}
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a>
							</div>
							{/if}

							{assign var="doi" value=$publishedArticle->getStoredPubId('doi')}
							{if $publishedArticle->getPubId('doi')}
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for {$publishedArticle->getLocalizedTitle()|strip_tags|escape}" href="http://doi.org/{$publishedArticle->getPubId('doi')|escape}">{$publishedArticle->getPubId('doi')}</a>{if $publishedArticle->getViews('doi')}{/if}
							</div>
							{/if}

							{foreach from=$publishedArticle->getGalleys() item=galley name=galleyList}
                            {if $issueAvailable}
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="galley">
							    {if $galley->isPdfGalley()}
							    <a class="pdf-galley" title="{$publishedArticle->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}">{$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span>
							    </a>
							    {elseif $galley->isHTMLGalley()}
							    <a class="html-galley" title="{$publishedArticle->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}">{$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span>
							    </a>
							    {/if}
							</div>
                            {/if}
                            {/foreach}
							
							{if !$hasAccess || $hasAbstract}
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="abstract"><a class="abstract" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}">{if $galley->isHTMLGalley()} {translate key="article.article"}{elseif $publishedArticle->getLocalizedAbstract()} {translate key="article.abstract"}{else} {translate key="article.details"}{/if} <span class="fileView">{$publishedArticle->getViews()} views</span></a>
							</div>
							{/if}

							<div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info" >Volume {$issue->getVolume()|strip_tags|escape}{if $issue->getNumber()|escape}, No. {$issue->getNumber()|escape}{/if}{if $publishedArticle->getPages()}, P: {$publishedArticle->getPages()|escape}{else}, {$publishedArticle->getId()}{/if}
							</div>
						</div>
					</article>
				</div>
			</li>
			{else}
			<li class="app-article-list-row__item 2">
				<div class="u-full-height" data-native-ad-placement="false">
					<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
						<div class="c-card__layout u-full-heights">
							{if $publishedArticle->getLocalizedFileName() && $publishedArticle->getLocalizedShowCoverPage()}
            					{assign var=showCoverPage value=true}
            				{else}
            					{assign var=showCoverPage value=false}
            				{/if}
            				
                            {if $showCoverPage}
                            <div class="c-card__image">
                                <picture>
                                    {if $currentJournal}
                                    {* Konteks jurnal - path sudah benar *}
                                    <source type="image/webp" srcset="{$publicFilesDir}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 160w,{$publicFilesDir}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 290w">
                                    <img src="{$publicFilesDir}/{$publishedArticle->getLocalizedFileName()|escape}" alt="{$publishedArticle->getLocalizedCoverPageAltText()|escape}" itemprop="image">
                                    {else}
                                    {* Konteks site search - gunakan path journal spesifik *}
                                    <source type="image/webp" srcset="{$baseUrl}/public/journals/{$publishedArticle->getJournalId()}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 160w,{$baseUrl}/public/journals/{$publishedArticle->getJournalId()}/{$publishedArticle->getLocalizedFileName()|escape}?as=webp 290w">
                                    <img src="{$baseUrl}/public/journals/{$publishedArticle->getJournalId()}/{$publishedArticle->getLocalizedFileName()|escape}" alt="{$publishedArticle->getLocalizedCoverPageAltText()|escape}" itemprop="image">
                                        {/if}
                                </picture>
                            </div>
                            {/if}
                            
							{call_hook name="Templates::Issue::Issue::ArticleCoverImage"}
							<div class="c-card__body u-display-flex u-flex-direction-column">
								<h3 class="c-card__title" itemprop="name headline">
									<a class="c-card__link u-link-inherit" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId()}" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$publishedArticle->getLocalizedTitle()|strip_unsafe_html}</a>
								</h3>

								{if $publishedArticle->getLocalizedAbstract()}
								<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p>{$publishedArticle->getLocalizedAbstract()|nl2br}</p></div>
								{/if}
								
								{if (!$publishedArticle->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $publishedArticle->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
								{else}
								<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list">{foreach from=$publishedArticle->getAuthors() item=authorItem name=authorList}<li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name">{$authorItem->getFullName()|escape}</span>{if $authorItem->getFirstName() !== $authorItem->getLastName()}<span itemprop="name">{$authorItem->getFirstName()|escape}</span>{/if}{if $authorItem->getMiddleName()}<span itemprop="name">{$authorItem->getMiddleName()|escape}</span>{/if}<span itemprop="name">{$authorItem->getLastName()|escape}</span></li>{/foreach}
								</ul>
								{/if}
							</div>
						</div>
						<div class="c-card__section c-meta">

							<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
								<span class="c-meta__type">{if $issue->getPublished() && $section && $journal}{$section->getLocalizedTitle()|escape}{else}{if $section && $section->getLocalizedIdentifyType()}{$section->getLocalizedIdentifyType()|escape}{else}{$publishedArticle->getSectionTitle()|strip_tags|escape}{/if}{/if}
								</span>
							</span>
							
                            {* Versi paling sederhana tanpa operator yang bermasalah *}
                            {if $publishedArticle->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
                            <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            {elseif $issue && $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN}
                            <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            {elseif $currentJournal && $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                            <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            {else}
                                {assign var=articleJournal value=$publishedArticle->getJournal()}
                                {if $articleJournal && $articleJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                                    <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                        <span class="u-color-open-access">Open Access</span>
                                    </span>
                                {/if}
                            {/if}
							
							<time class="c-meta__item c-meta__item--block-at-lg" datetime="{$publishedArticle->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$publishedArticle->getDatePublished()|date_format:"%d %b %Y"}</time>

							{if !$currentJournal}
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a>
							</div>
							{else}
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a>
							</div>
							{/if}

							{assign var="doi" value=$publishedArticle->getStoredPubId('doi')}
							{if $publishedArticle->getPubId('doi')}
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for {$publishedArticle->getLocalizedTitle()|strip_tags|escape}" href="http://doi.org/{$publishedArticle->getPubId('doi')|escape}">{$publishedArticle->getPubId('doi')}</a>{if $publishedArticle->getViews('doi')}{/if}
							</div>
							{/if}

							{if !$hasAccess || $hasAbstract}
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="nopdf-galley"><a class="abstract-only" href="{url journal=$journal->getPath() page="article" op="view" path=$publishedArticle->getBestArticleId($journal)}">{if $publishedArticle->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if} <span class="fileView">{$publishedArticle->getViews()} views</span></a>
							</div>
							{/if}

							<div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info" >Volume {$issue->getVolume()|strip_tags|escape}{if $issue->getNumber()|escape}, No. {$issue->getNumber()|escape}{/if}{if $publishedArticle->getPages()}, P: {$publishedArticle->getPages()|escape}{else}, {$publishedArticle->getId()}{/if}
							</div>
						</div>
					</article>
				</div>				
			</li>
			{/if}
		{/iterate}			
		</ul>
	</div>
</section>

{if $results->wasEmpty()}
<div class="search-message">
	{if $error}
	<div class="error-message" data-test="empty-search-result-message">
		{$error|escape}
	</div>
	{else}
	<div class="empty-message u-hide" data-test="empty-search-result-message">
		<h2>Sorry – we couldn’t find what you are looking for.</h2>
		<p class="intro--paragraph">Make sure that all words are spelled correctly</p>
	</div>
	{/if}
    <div class="container cleared container-type-title" data-container-type="title">
        <div class="border-top-1 border-gray-medium"></div>
        <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
            <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
            </div>
            <div class="c-empty-state-card__text search-tips">
                <h2 class="c-empty-state-card__text--title headline-5">Sorry – we couldn’t find what you are looking for "{if $query}{$query|strip_unsafe_html}{elseif $hasActiveFilters}{$filterValue|escape}{else}...{/if}"</h2>
                <div class="c-empty-state-card__text--description">Make sure that all words are spelled correctly.</div>
        		<div class="c-empty-state-card__text--description">
        		{capture assign="syntaxInstructions"}{call_hook name="Templates::Search::SearchResults::SyntaxInstructions"}{/capture}
        			{if empty($syntaxInstructions)}
        				{translate key="search.syntaxInstructions"}
        			{else}
        				{* Must be properly escaped in the controller as we potentially get HTML here! *}
        				{$syntaxInstructions}
        			{/if}
        		</div>
            </div>
        </div>
    </div>
</div>
<div class="u-hide instruct-search u-mt-32" data-test="tips-search-message">
	<h2>Seacrh Tips</h2>		
	<div class="search-tips">
	{capture assign="syntaxInstructions"}{call_hook name="Templates::Search::SearchResults::SyntaxInstructions"}{/capture}
		{if empty($syntaxInstructions)}
			{translate key="search.syntaxInstructions"}
		{else}
			{* Must be properly escaped in the controller as we potentially get HTML here! *}
			{$syntaxInstructions}
		{/if}
	</div>
</div>
{else}
<div id="colspan" class="colspan u-mb-0" >	    
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View {if $results && is_object($results)}{page_info iterator=$results}{/if}</div>
    </section>
    {if $results->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="results" iterator=$results name="search" query=$query searchJournal=$searchJournal authors=$authors title=$title abstract=$abstract galleyFullText=$galleyFullText suppFiles=$suppFiles discipline=$discipline subject=$subject type=$type coverage=$coverage indexTerms=$indexTerms dateFromMonth=$dateFromMonth dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateToMonth=$dateToMonth dateToDay=$dateToDay dateToYear=$dateToYear orderBy=$orderBy orderDir=$orderDir}
       </div>
    </section>
    {/if}
</div>
{/if}

{include file="common/footer-parts/footer-search.tpl"}
