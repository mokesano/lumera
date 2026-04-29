{**
 * templates/index/journal.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal index page.
 *
 *}
{strip}
{assign var="pageTitleTranslated" value=$siteTitle}
{include file="common/header-HOME.tpl"}
{/strip}
{if $currentJournal && $currentJournal->getSetting('onlineIssn')}
	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal && $currentJournal->getSetting('printIssn')}
	{assign var=issn value=$currentJournal->getSetting('printIssn')}
{/if}

<div id="JOUR" class="journal-page">

<div class="journal-content">
{* Hitung artikel di bagian atas template *}
{assign var="articleCount" value=0}
{if $publishedArticles}
    {foreach from=$publishedArticles item=section}
        {if $section.articles}
            {math equation="x + y" x=$articleCount y=$section.articles|@count assign="articleCount"}
        {/if}
    {/foreach}
{/if}

{include file="common/featured/article_Hero.tpl"}

{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $articleCount >= 4}
{**
<article>
    <div data-track-component="hero" class="u-container">
        <div class="c-hero c-hero--flush-md-max u-mb-0 u-position-relative">
		    <div class="c-hero__image">
		        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
 					{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
		        <picture><source type="image/webp" srcset="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp?as=webp 450w, {$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" loading="lazy" class="lazyload" alt="">
		        </picture>
		        {elseif $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
		        <picture><source type="image/webp" srcset="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}?as=webp 450w, {$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" alt="{if $displayPageHeaderTitleAltText != ''}{$displayPageHeaderTitleAltText|escape}{else}{translate key="common.pageHeader.altText"}{/if}">
		        </picture>
		        {elseif $displayHomepageImage && is_array($displayHomepageImage)}
		        <picture><source type="image/webp" srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 450w, {$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="">
		        </picture>
		        {else}
		        <picture><source type="image/webp" srcset="//assets.sangia.org/img/img-default_v2.png?as=webp 450w, //assets.sangia.org/img/img-default_v2.png?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" alt="">
		        </picture>
		        {/if}
		    </div>
		    <div class="c-hero__copy">
		        <h2 class="c-hero__title">
		            <a class="c-hero__link u-link-faux-block" href="{url page="issue" op="current"}" data-track="click" data-track-action="view announcement" data-track-label="link">Read our {$issue->getDatePublished()|date_format:"%B"} issue</a>
		        </h2>
		        <p class="c-hero__summary"><em>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</em> in {$issue->getDatePublished()|date_format:"%B %Y"} issue {if $issue->getLocalizedTitle($currentJournal)}on the topic of {$issue->getLocalizedTitle($currentJournal)|escape}{/if} now live.{if $issue->getLocalizedDescription()} {$issue->getLocalizedDescription()|strip_tags|escape}{/if}</p>
		    </div>
		</div>
	</div>
</article>
<section id="featured-content" class="u-mb-0" data-track-component="featured content">
	<h2 class="u-visually-hidden">Featured Content</h2>
	<div class="u-container">
		<ul class="app-featured-row">
			{include file="issue/issues.tpl"}
			<li class="app-featured-row__item app-featured-row__item--current-issue">
				<div class="c-card c-card--flush u-full-height">
	  				<a class="c-card__image" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="image">
	  					{assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
	  					{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
	  					<picture><source srcset="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp" type="image/webp"><img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
	  					</picture>
	  					{elseif $displayHomepageImage && is_array($displayHomepageImage)}
	  					<picture><source type="image/webp" srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 450w, {$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="">
	  					</picture>	  						
	  					{else}
	  					<picture><source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp 450w, //assets.sangia.org/img/img-default_v2.png?as=webp 735w" type="image/webp" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
  						</picture>
  						{/if}
  					</a>
					<div class="c-card__body u-display-flex u-flex-direction-column">
						<ul class="u-mb-16 u-list-reset u-display-flex">
							<li class="u-mr-4 u-flex-grow">
								<a class="u-button u-button--full-width" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="button">Contents</a>
							</li>
							<li class="u-ml-4 u-flex-grow">
								<a class="u-button u-button--primary u-button--full-width" href="{url page="notification" op="subscribeMailList"}" data-track="click" data-track-action="subscribe" data-track-label="button">Subscribe</a>
							</li>
						</ul>
					</div>
					<div class="c-card__section c-meta"><span class="c-meta__item"><span class="c-meta__type">{translate key="journal.currentIssue"}</span></span><time class="c-meta__item" datetime="{$issue->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$issue->getDatePublished()|date_format:"%e %B %Y"}</time>
					</div>
				</div>
			</li>
		</ul>
	</div>
</section>
{else}
<article>
    <div data-track-component="hero" class="u-container">
        <div class="c-hero c-hero--flush-md-max u-mb-0 u-position-relative">
		    <div class="c-hero__image">
		        {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
		        <picture><source type="image/webp" srcset="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}?as=webp 450w, {$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" alt="">
		        </picture>
		        {else}
		        <picture><source type="image/webp" srcset="//assets.sangia.org/static/images/not-available.webp 450w, //assets.sangia.org/static/images/not-available.webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="//assets.sangia.org/static/images/not-available.webp" alt="">
		        </picture>
		        {/if}
		    </div>
		    <div class="c-hero__copy">
		        <h2 class="c-hero__title">
		            <a class="c-hero__link u-link-faux-block" href="{url page="issue" op="current"}" data-track="click" data-track-action="view announcement" data-track-label="link">Publish with {$currentJournal->getLocalizedTitle()|strip_tags|escape}</a>
		        </h2>
		        {if $metaSearchDescription}
		        <p class="c-hero__summary ellipsis">{$metaSearchDescription|strip_tags|escape}</p>
		        {else}
		        <p class="c-hero__summary ellipsis">{$journalDescription|strip_tags|escape}</p>
		        {/if}
		    </div>
		</div>
	</div>
</article>
**}
{/if}

{include file="common/featured/editor-home.tpl"}

{**
<section class="area-wrapper u-mt-32">
  <div class="live-area">
  	<section class="row raw">
  	    <div class="column medium-12 editorial-timeline">
			{include file="common/featured/sample.tpl"}
		</div>
	</section>
  </div>
</section>
**}

{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
<section class="area-wrapper u-mt-32">
  <div class="live-area">
  	<section class="row raw">
  	    <div class="column medium-12 editorial-timeline">
			{include file="common/featured/editorial-timeline.tpl"}
		</div>
	</section>
  </div>
</section>
    {include file="common/featured/mostDownloads.tpl"}
    {** include file="common/featured/mostPopularArticles.tpl" **}
{/if}

{call_hook name="Templates::Index::journal"}

{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $articleCount >= 1}
{* Display the table of contents or cover page of the current issue. *}
<section class="area-wrapper u-mb-24 u-mt-32">
  <div class="live-area">
  	<div class="row raw">
  		<div class="position-relative z-index-1">
  			<div class="u-container c-slice-heading" data-test="title">
                <h2 class="titles"><span class="title">Latest issue</span><span class="sub-title">{translate key="issue.volume"} {$issue->getVolume()|escape}, {translate key="issue.issue"} {$issue->getNumber()|escape} ({$issue->getDatePublished()|date_format:"%Y"})</span></h2>
                {if $issue->getLocalizedTitle($currentJournal)}
				<h3 class="u-hide issue-title headline-2545795530">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>
				{/if}
				{* Ekstrak tahun dan bulan dari waktu sekarang *}
				{assign var="currentYear" value=$smarty.now|date_format:"%Y"}
				{assign var="currentMonth" value=$smarty.now|date_format:"%m"}
									
				{* Ekstrak tahun dan bulan dari tanggal terbit issue *}
				{assign var="issueYear" value=$issue->getDatePublished()|date_format:"%Y"}
				{assign var="issueMonth" value=$issue->getDatePublished()|date_format:"%m"}
				<div class="insight-label u-font-sans">This issue is {if $currentYear == $issueYear}{if $currentMonth <= $issueMonth}in progress but{else}completed,{/if}{else}halted but{/if} contains articles that are final and fully citable.</div>
            </div>
  		</div>
	  	<div id="contents" class="position-relative z-index-1" role="main">
	  		<section id="latest-content" class="u-mb-0" data-track-component="latest content">
	  			<h2 class="u-visually-hidden">Latest Articles Content</h2>
	  			<div class="u-container">
	  				<ul class="app-article-list-row">
	  					{include file="issue/issueLates.tpl"}
	  				</ul>
	  			</div>
	  		</section>
	  	</div>
	  </div>
  </div>
</section>
{/if}

{if $externalHomeContent}
<section id="{$externalFeedSectionId|escape}" class="live-area">
    <div class="row raw">
    {$externalHomeContent}
    </div>
</section>
{/if}

{** include file="common/featured/recentArticle.tpl" **}

{** include file="common/featured/mostCitedArticles.tpl" **}

{if $enableAnnouncementsHomepage}
{* Display announcements *}
<section class="live-area-wrapper u-mb-32">
  <div class="live-area">
  	<section class="row raw">
  		<div class="columns medium-12 main-contents c-slice-heading">
      		<h2 class="headline">
      		    <a title="View more News and Announcements" href="{url page="announcement"}">{translate key="announcement.announcementsHome"}<svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="20" width="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg></a>
      		</h2>
  		</div>
  		
  		<section class="column medium-8">
  			<div class="app-announcement-list-row announcements">
			{counter start=1 skip=1 assign="count"}
			{iterate from=announcements item=announcement}
			{if !$numAnnouncementsHomepage || $count <= $numAnnouncementsHomepage}
				<section class="app-announcement-list-row__item c-card--flush announcement">
				{if $announcement->getTypeId()}
					<h3 class="headline-2545795530 u-mb-8">{$announcement->getAnnouncementTypeName()|escape}: {$announcement->getLocalizedTitle()|escape}</h3>
					{else}
					<h3 class="headline-2545795530 u-mb-8">{$announcement->getLocalizedTitle()|escape}</h3>
				{/if}

				<div class="description">{$announcement->getLocalizedDescriptionShort()|nl2br}</div>
				<br />
    				<div class="u-flex-direction-column">
        				<span class="details">
        					<time class="published posted">{translate key="announcement.posted"}: {$announcement->getDatePosted()|date_format:"%e %B %Y"}</time>
        					<td class="u-hide more">&nbsp;</td>
        					{if $announcement->getLocalizedDescription() != null}
        					<span class="more"><a itemprop="url" href="{url page="announcement" op="view" path=$announcement->getId()}">View {translate key="announcement.viewLink"}</a></span>
        					{/if}
        				</span>
        			</div>
				</section>
			{/if}
			{counter}
			{/iterate}
    		</div>
    	</section>

		<div class="column medium-4">
			<div class="box">
				<section class="teaser"><h4 class="headline-3789142952">Browse our latest news</h4>
					<div><p>Stay up to date on events, announcements &amp; new releases</p></div>
					<a title="News and Announcements" href="{url page="announcement"}" class="button-base-3588540778" class="sangia-button"><span class="button-label-1262423735">Browse now</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
				</section>
			</div>
		</div>
		
	</section>
  </div>
</section>
{/if}

{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}

	{include file="common/search.tpl"}
	
<section class="area-wrapper u-mt-16 u-mb-24 u-hide">
  <div class="live-area">
  	<div class="row raw">
  		<div class="position-relative z-index-1">
  			<div class="u-container c-slice-heading" data-test="title">
                <h2 class="titles"><span class="title">Latest Research articles</span><span class="sub-title u-hide">{translate key="issue.volume"} {$issue->getVolume()|escape}, {translate key="issue.issue"} {$issue->getNumber()|escape} ({$issue->getDatePublished()|date_format:"%Y"})</span></h2>
                {if $issue->getLocalizedTitle($currentJournal)}
				<h3 class="u-hide issue-title headline-2545795530">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>
				{/if}
				{* Ekstrak tahun dan bulan dari waktu sekarang *}
				{assign var="currentYear" value=$smarty.now|date_format:"%Y"}
				{assign var="currentMonth" value=$smarty.now|date_format:"%m"}
									
				{* Ekstrak tahun dan bulan dari tanggal terbit issue *}
				{assign var="issueYear" value=$issue->getDatePublished()|date_format:"%Y"}
				{assign var="issueMonth" value=$issue->getDatePublished()|date_format:"%m"}
				<div class="insight-label u-font-sans">This issue is {if $currentYear == $issueYear}{if $currentMonth <= $issueMonth}in progress but{else}completed,{/if}{else}halted but{/if} contains articles that are final and fully citable.</div>
            </div>
  		</div>
	  	<div id="contents" class="position-relative z-index-1" role="main">
	  		<section id="featured-content" class="u-mb-0" data-track-component="featured content">
	  			<h2 class="u-visually-hidden">Featured Content</h2>
	  			<div class="u-container">
	  				<ul class="app-featured-row">
	  					<li class="u-hide app-featured-row__item app-featured-row__item--current-issue">
	  						<div class="c-card c-card--flush u-full-height">
        	  					<a class="c-card__image" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="image">
        	  						{assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
        	  						{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
        	  						<picture>
        	  							<source srcset="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp" type="image/webp">
        	  							<img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
        	  						</picture>
        	  						{elseif $displayHomepageImage && is_array($displayHomepageImage)}
        	  						<picture><source type="image/webp" srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 450w, {$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="">
        	  						</picture>	  						
        	  						{else}
        	  						<picture >
        	  							<source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp 450w, //assets.sangia.org/img/img-default_v2.png?as=webp 735w" type="image/webp" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px">
        	  							<img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
        	  						</picture>
        	  						{/if}
        	  					</a>
	  							<div class="c-card__body u-display-flex u-flex-direction-column">
	  								<ul class="u-mb-16 u-list-reset u-display-flex">
	  									<li class="u-mr-4 u-flex-grow">
	  										<a class="u-button u-button--full-width" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="button">Contents</a>
	  									</li>
	  									<li class="u-ml-4 u-flex-grow">
	  										<a class="u-button u-button--primary u-button--full-width" href="{url page="notification" op="subscribeMailList"}" data-track="click" data-track-action="subscribe" data-track-label="button">Subscribe</a>
	  									</li>
	  								</ul>
	  							</div>

	  							<div class="c-card__section c-meta">
	  								<span class="c-meta__item"><span class="c-meta__type">{translate key="journal.currentIssue"}</span></span><time class="c-meta__item" datetime="{$issue->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$issue->getDatePublished()|date_format:"%e %B %Y"}</time>
	  							</div>
	  						</div>
	  					</li>
	  					{include file="issue/issues.tpl"}
	  				</ul>
	  			</div>
	  		</section>
	  	</div>
	</div>
  </div>
</section>
{/if}

{include file="common/journal-identity.tpl"}

<section class="area-wrapper u-mt-32">
  <div class="row raw">
		    
	<div data-test="title" class="column c-slice-heading u-mt-0 u-hide">
	    <h2 class="headline"><a title="View more about {$currentJournal->getLocalizedTitle()|strip_tags|escape}" href="{url page="about"}">{translate key="navigation.about"} {$currentJournal->getLocalizedInitials()|strip_tags|escape}<svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="25" width="25" viewBox="0 0 16 12" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg></a>
	   </h2>
	</div>
			
	<section class="columns medium-12">
		<div class="main-contents" role="main">	

		<div class="explore-contents">
		    <div class="row">
		        <div class="columns medium-8" role="main">
		            <div class="row">
		                <div class="column {if $issue}medium-8{else}medium-12{/if} insight">
	                <ul class="journalinsights u-mb-16 u-fonts">
		                {if $printIssn} {else if $onlineIssn}
						<li class="meta"><span><span class="bold">ISSN:</span> {if $currentJournal->getSetting('onlineIssn')}<span class="c-meta__item"><a class="anchor" href="//portal.issn.org/resource/ISSN/{$currentJournal->getSetting('onlineIssn')}" target="_blank">{$currentJournal->getSetting('onlineIssn')}</a> (Medium online)</span>{else}<span class="c-meta__item">on proccess (Medium online)</span>{/if}{if $currentJournal->getSetting('printIssn')} <span class="c-meta__item"><a class="anchor" href="//portal.issn.org/resource/ISSN/{$currentJournal->getSetting('printIssn')}" target="_blank">{$currentJournal->getSetting('printIssn')}</a> (Medium Print)</span>{/if}</span></li>
						{/if}

						<li class="meta"><span class="c-meta__item"><span class="bold">Journal title:</span> {$currentJournal->getLocalizedTitle()|strip_tags|escape}</span></li>												
						{if $currentJournal->getSetting('initials')}
						<li class="meta"><span class="c-meta__item"><span class="bold">Journal Initials:</span> {$currentJournal->getLocalizedInitials()|strip_tags|escape}</li></span>{/if}
												
						{if $currentJournal->getSetting('abbreviation')}
						<li class="meta"><span class="c-meta__item"><span class="bold">Abbreviation:</span> <span class="italic">{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}</span></span></li>{/if}

            			{if $issue}
            			{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
            			{assign var=firstVolume value=$currentJournal->getSetting('initialVolume')}
            			{assign var=initialNumber value=$currentJournal->getSetting('initialNumber')}
            			{assign var=lastYear value=$issue->getYear()}
            			{assign var=lastVolume value=$issue->getVolume()}
            			<li class="meta"><span class="c-meta__item"><span class="bold">First {translate key="issue.year"} published:</span> {$firstYear|escape}</span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">First {translate key="issue.volume"} published:</span> {translate key="issue.volume"} {$firstVolume|escape} {translate key="issue.issue"} {$initialNumber|escape}</span></li>
            			
            			{assign var=volumePerYear value=$currentJournal->getSetting('volumePerYear')}
            			{assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            			<li class="meta"><span class="c-meta__item"><span class="bold">Frequency:</span> {if $issuePerVolume|escape == 1}Annually{elseif $issuePerVolume|escape == 2}Semiannually{elseif $issuePerVolume|escape == 3}Quarterly{elseif $issuePerVolume|escape == 4}Quarterly{elseif $issuePerVolume|escape == 6}Bimonthly{elseif $issuePerVolume|escape == 12}Monthly{/if} — (<span class="italic">{$issuePerVolume|escape} {translate key="issue.issue"} in {$volumePerYear|escape} {translate key="issue.volume"} per year)</span></span></li>
            			
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage {translate key="issue.year"}:</span> {$firstYear|escape}{if $lastYear|escape} <i>to present</i>{else}{$lastYear|escape}{/if}</span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage {translate key="issue.volume"}:</span> {translate key="issue.volume"} {$firstVolume|escape} — {translate key="issue.volume"} {$lastVolume|escape} (<span class="italic">present</span>)</span></li>
            			{else}
            			<li class="meta"><span class="c-meta__item"><span class="bold">First Published:</span> <span class="italic">not available</span></span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage {translate key="issue.year"}:</span> <span class="italic">has not published any issues</span></span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage {translate key="issue.volume"}:</span> <span class="italic">has not published any issues</span></span></li>
            			{/if}
                        {if $currentJournal->getSetting('copyrightYearBasis')}
                        <li class="meta"><span class="c-meta__item"><span class="bold">Publishing Model:</span> Publish-as-you-go</span></li>
                        {/if}
						</ul>
										
							<div class="u-journalinsights u-font-sanss publishing-options__Wrapper-sc-5j168a-0 hpQXlC">
							    <span class="ublishing-options__Label-sc-5j168a-1 dWYPSC u-fonts">Publishing options:</span>
							    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
							    <a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" data-aa-name="Publishing options open access" data-aa-region="header" class="tag__TextWrapper-sc-1fw5i3t-5-a tag__AnchorWrapper-sc-1fw5i3t-6 hFFtfC jHbgct header-publishing-options-open-access"><span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig icNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span><span class="tag__LabelText-sc-1fw5i3t-2 kqUCww">Open Access</span><svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="tag__ExternalLinkIcon-sc-1fw5i3t-7 hQLDKg"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></a>
							    {/if}
											
								{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
								<a href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}" data-aa-name="Publishing options subscription" data-aa-region="header" class="tag__TextWrapper-sc-1fw5i3t-5-a tag__AnchorWrapper-sc-1fw5i3t-6 hFFtfC jHbgct header-publishing-options-subscription"><span aria-hidden="false" aria-label="Subscription" data-color="silver" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">S</span></span><span class="tag__LabelText-sc-1fw5i3t-2 kqUCww">Subscription</span><svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="tag__ExternalLinkIcon-sc-1fw5i3t-7 hQLDKg"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></a>

								<a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" data-aa-name="Publishing options open access" data-aa-region="header" class="tag__TextWrapper-sc-1fw5i3t-5-a tag__AnchorWrapper-sc-1fw5i3t-6 hFFtfC jHbgct header-publishing-options-open-access"><span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig icNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span><span class="tag__LabelText-sc-1fw5i3t-2 kqUCww">Open Access</span><svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="tag__ExternalLinkIcon-sc-1fw5i3t-7 hQLDKg"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></a>
								{/if}
							</div>
						</div>		

						{if $issue}
						{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
					    {assign var=lastYear value=$issue->getYear()}
					    {assign var=lastVolume value=$issue->getVolume()}
						{assign var=lastNumber value=$issue->getNumber()}
						<div class="column medium-4 insight">
						    <div class="insight-box box">
						        <section class="teaser u-jour-insight u-font-sans">
						            <div class="js-title title insight-head">Latest {translate key="issue.issue"}</div>
									{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
									<div class="contents">
									    <div class="insight-data"><a href="{url page="issue" op="current"}">{translate key="issue.volume"} {$lastVolume|escape}{if $issue->getNumber()}, {translate key="issue.issue"} {$lastNumber|escape}{/if}</a></div>
									{/if}
									{* Ekstrak tahun dan bulan dari waktu sekarang *}
									{assign var="currentYear" value=$smarty.now|date_format:"%Y"}
									{assign var="currentMonth" value=$smarty.now|date_format:"%m"}
									
									{* Ekstrak tahun dan bulan dari tanggal terbit issue *}
									{assign var="issueYear" value=$issue->getDatePublished()|date_format:"%Y"}
									{assign var="issueMonth" value=$issue->getDatePublished()|date_format:"%m"}
									{if $currentYear == $issueYear}
									    {if $currentMonth <= $issueMonth}
									    <div class="insight-tip in-progress"><i>in progress</i></div>
									    {else}
									    <div class="insight-tip"><i>Completed</i></div>
									    {/if}
									{else}
									<div class="insight-tip Halted"><i>Halted </i></div>
									{/if}
									<p class="insight-date">{$issue->getDatePublished()|date_format:"%B %Y"}</p>
									</div>
								</section>
							</div>
						</div>
                    	{/if}
					</div>

					{if $currentJournal->getLocalizedSetting('history') != ''}
					<div id="journal-history-link" class="u-mb-32"><span class="icon-container info">This journal was previously published under other titles <a href="{url page="about" op="history" anchor=""}">(view Journal {translate key="about.history"})</a></span>
					</div>
					{/if}

					{if $journalDescription}
					<div class="about-journal">{$journalDescription}</div>
					{/if}
					
					{if $additionalHomeContent}
					<div class="u-hide journal-contents">{$additionalHomeContent}</div>
					{/if}

				</div>

				<aside class="sangia-aside column medium-4" role="side">
				    <section class="cta-aside-section">
				        <section class="sangia-button submission"><a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-47711979"><span class="button-label-2770091062">Submit your paper</span><svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1494494357"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
				        </section>

						<div class="sangia-box bbnsMx u-font-sangia-sans">
						    {if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
						    <ul class="sc-391xmi-0 hlGAVI">
						        {if $alternatePageHeader}
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 78 128" height="1em" width="1em" aria-hidden="true"><path d="M39 52C18.013 52 1 69.013 1 90s17.013 38 38 38 38-17.013 38-38-17.013-38-38-38zm0 66c-15.464 0-28-12.536-28-28s12.536-28 28-28 28 12.536 28 28-12.536 28-28 28zM1 8.72v9.487l26.689 27.138c4.252-1.067 8.336-1.481 12.613-1.356l-1.12-1.167h-.006L11.622 14.853c16.698-6.248 37.036-6.514 55.037.066L44.782 37.127l6.922 7.243L77 18.737V8.454C53-3.15 21-2.169 1 8.72zM29 82h6v26h10V72H29z" fill="currentColor"></path></svg><span>The Sinta Score of this journal is <span class="SintaScore">{$alternatePageHeader}</span>. Sinta: Science and Technology Index by <em>Ministry of Education, Research and Technology, Republic of Indonesia</em>.</span>
						        </li>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 128" height="1em" width="1em" aria-hidden="true"><path d="M51.832 11.982c-28.168 0-51.002 22.834-51.002 51s22.834 51 51 51c24.624 0 45.17-17.45 49.952-40.656 6.392-33.232-18.762-61.344-49.95-61.344zm-25.08 18.612a40.794 40.794 0 0115.21-7.366c.394 3.9.68 7.83.75 10.76-4.036-.686-9.334.084-15.168 5.188-.76-3.49-.828-6.808-.79-8.58zm43.994 5.368c-1.826 6.384-2.996 15.16 1.478 21.494 6.786 9.61 21.162 6.396 19.77 13.808-3.83 18.674-20.356 32.718-40.162 32.718-32.65 0-52.144-36.4-34.23-63.544 1.44 7.378 4.976 13.42 10.358 19.548-1.682 1.92-4.378 4.266-4.824 6.986-2.37 14.44 9.768 17.2 14.284 18.38.608.16 1.38.36 1.842.5 2.318 4.35 5.108 12.24 11.52 12.24 8.792 0 15.148-17.72 14.392-28.584-.766-11.02-16.8-17.148-28.276-14.544a48.454 48.454 0 01-4.958-6.106c9.734-10.94 11.348-.09 17.5-3.972 2.08-1.31 4.636-2.928 2.468-22.9a40.717 40.717 0 0115.86 3.256l.012-.03a41.496 41.496 0 0120.054 18.16l-.042.024a40.68 40.68 0 014.574 13.73c-8.498-3.374-14.106-2.536-13.152-12.51a33.256 33.256 0 00-8.468-8.654zm-37.742 32.63c1.24-7.534 21.828-3.66 22.194 1.608.422 6.08-2.216 13.11-4.296 16.444-4.09-7.14-2.836-8.848-10.956-10.966-4.694-1.226-7.836-1.64-6.942-7.086z" fill="currentColor"></path></svg><span><i>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</i> is indexed in national and international databases, your published article can be read and cited by researchers worldwide.</span>
						        </li>
						        {elseif $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 128" height="1em" width="1em" aria-hidden="true"><path d="M51.832 11.982c-28.168 0-51.002 22.834-51.002 51s22.834 51 51 51c24.624 0 45.17-17.45 49.952-40.656 6.392-33.232-18.762-61.344-49.95-61.344zm-25.08 18.612a40.794 40.794 0 0115.21-7.366c.394 3.9.68 7.83.75 10.76-4.036-.686-9.334.084-15.168 5.188-.76-3.49-.828-6.808-.79-8.58zm43.994 5.368c-1.826 6.384-2.996 15.16 1.478 21.494 6.786 9.61 21.162 6.396 19.77 13.808-3.83 18.674-20.356 32.718-40.162 32.718-32.65 0-52.144-36.4-34.23-63.544 1.44 7.378 4.976 13.42 10.358 19.548-1.682 1.92-4.378 4.266-4.824 6.986-2.37 14.44 9.768 17.2 14.284 18.38.608.16 1.38.36 1.842.5 2.318 4.35 5.108 12.24 11.52 12.24 8.792 0 15.148-17.72 14.392-28.584-.766-11.02-16.8-17.148-28.276-14.544a48.454 48.454 0 01-4.958-6.106c9.734-10.94 11.348-.09 17.5-3.972 2.08-1.31 4.636-2.928 2.468-22.9a40.717 40.717 0 0115.86 3.256l.012-.03a41.496 41.496 0 0120.054 18.16l-.042.024a40.68 40.68 0 014.574 13.73c-8.498-3.374-14.106-2.536-13.152-12.51a33.256 33.256 0 00-8.468-8.654zm-37.742 32.63c1.24-7.534 21.828-3.66 22.194 1.608.422 6.08-2.216 13.11-4.296 16.444-4.09-7.14-2.836-8.848-10.956-10.966-4.694-1.226-7.836-1.64-6.942-7.086z" fill="currentColor"></path></svg><span><i>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</i> is a NEW journal an bring to index in national and international databases, your published article can be read and cited by researchers worldwide.</span>
						        </li>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 113 128" height="1em" width="1em" aria-hidden="true"><path d="M26 14v16H2v66h10V40h14v64H2v10h64V49.47l12.39 64.73 33.35-6.44-15.42-80.31L66 33.29V14H26zm10 10h20v26H44v10h12v44H36V24zm52.3 15.15l11.69 60.69-13.75 2.64L74.55 41.8l13.75-2.65z"></path></svg><span>{$currentJournal->getLocalizedSetting('pubFreqPolicy')|strip_tags|nl2br|truncate:150:""}</span>
						        </li>
						        {elseif $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 128" height="1em" width="1em" aria-hidden="true"><path d="M51.832 11.982c-28.168 0-51.002 22.834-51.002 51s22.834 51 51 51c24.624 0 45.17-17.45 49.952-40.656 6.392-33.232-18.762-61.344-49.95-61.344zm-25.08 18.612a40.794 40.794 0 0115.21-7.366c.394 3.9.68 7.83.75 10.76-4.036-.686-9.334.084-15.168 5.188-.76-3.49-.828-6.808-.79-8.58zm43.994 5.368c-1.826 6.384-2.996 15.16 1.478 21.494 6.786 9.61 21.162 6.396 19.77 13.808-3.83 18.674-20.356 32.718-40.162 32.718-32.65 0-52.144-36.4-34.23-63.544 1.44 7.378 4.976 13.42 10.358 19.548-1.682 1.92-4.378 4.266-4.824 6.986-2.37 14.44 9.768 17.2 14.284 18.38.608.16 1.38.36 1.842.5 2.318 4.35 5.108 12.24 11.52 12.24 8.792 0 15.148-17.72 14.392-28.584-.766-11.02-16.8-17.148-28.276-14.544a48.454 48.454 0 01-4.958-6.106c9.734-10.94 11.348-.09 17.5-3.972 2.08-1.31 4.636-2.928 2.468-22.9a40.717 40.717 0 0115.86 3.256l.012-.03a41.496 41.496 0 0120.054 18.16l-.042.024a40.68 40.68 0 014.574 13.73c-8.498-3.374-14.106-2.536-13.152-12.51a33.256 33.256 0 00-8.468-8.654zm-37.742 32.63c1.24-7.534 21.828-3.66 22.194 1.608.422 6.08-2.216 13.11-4.296 16.444-4.09-7.14-2.836-8.848-10.956-10.966-4.694-1.226-7.836-1.64-6.942-7.086z" fill="currentColor"></path></svg><span>{$currentJournal->getLocalizedSetting('focusScopeDesc')|strip_tags|escape}</span>
						        </li>
						        {/if}
						    </ul>
						    {else}
						    <p class="jour-profile u-font-sangia-sans"><span class="italic">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</span> is a NEW journal an bring to index in national and international databases, your published article can be read and cited by researchers worldwide.</p>      
						    </p>
						    {/if}
						</div>
						{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
						<section class="sangia-box cta-aside-section"><a class="button-base-159610158" href="{url page="search" op="titles"}" target="_blank" data-track="click"><span class="button-label-2658192883">View All Articles</span><svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1925105232"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
						</section>
						{/if}

						{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
						<div class="box u-box-sangia">
						    <h4 class="headline-424997076">Societies, partners and affiliations</h4>
						    {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<a href="{url page="about" op="journalSponsorship"}" class="society-link"><img class="lazyload society-logo" title="Societies, partners and affiliations of this Journal" src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="auto" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} loading="lazy" /></a>{/if}
						    <div>Find out more about Societies, partners and affiliations</div>
						    <a title="Societies, partners and affiliations" href="{url page="about" op="journalSponsorship"}" class="button-base-3588540778"><span class="button-label-1262423735">Find out more</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
						</div>
						{/if}

						{if $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $currentJournal->getSetting('currency')}

						{include file="common/payment.tpl"}
						
						{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_NONE *}
					</section>	
				</aside>
				<div class="column medium-12 u-mt-32"></div>
			</div>
		</div>
		</div>	
	</section>    
  </div>
</section>

{** include file="common/featured/trending_Altmetric.tpl" **}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
<div class="u-container position-relative">
  	<div data-test="transformative-journal-announcement" class="c-status-message c-status-message--info {if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}u-mt-32 u-mb-0{else} u-mb-32{/if} c-status-message--boxed"><svg class="c-status-message__icon c-status-message__icon--top" width="24" height="24" aria-hidden="true" focusable="false" role="img"><use xlink:href="#icon-info"><symbol id="icon-info" viewBox="0 0 18 18"><path d="m9 0c4.9705627 0 9 4.02943725 9 9 0 4.9705627-4.0294373 9-9 9-4.97056275 0-9-4.0294373-9-9 0-4.97056275 4.02943725-9 9-9zm0 7h-1.5l-.11662113.00672773c-.49733868.05776511-.88337887.48043643-.88337887.99327227 0 .47338693.32893365.86994729.77070917.97358929l.1126697.01968298.11662113.00672773h.5v3h-.5l-.11662113.0067277c-.42082504.0488782-.76196299.3590206-.85696816.7639815l-.01968298.1126697-.00672773.1166211.00672773.1166211c.04887817.4208251.35902055.761963.76398144.8569682l.1126697.019683.11662113.0067277h3l.1166211-.0067277c.4973387-.0577651.8833789-.4804365.8833789-.9932723 0-.4733869-.3289337-.8699473-.7707092-.9735893l-.1126697-.019683-.1166211-.0067277h-.5v-4l-.00672773-.11662113c-.04887817-.42082504-.35902055-.76196299-.76398144-.85696816l-.1126697-.01968298zm0-3.25c-.69035594 0-1.25.55964406-1.25 1.25s.55964406 1.25 1.25 1.25 1.25-.55964406 1.25-1.25-.55964406-1.25-1.25-1.25z" fill-rule="evenodd"></path></symbol></use></svg>
  	    <div class="u-flex-shrink" data-track-component="tj-announcement">
  			<p class="u-mb-0 u-fonts-sans"><span class="italic">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</span> is a <a data-track="click" data-track-action="click transformative journals link" href="{url page="information" op="librarians"}">Transformative Journal</a>; authors can publish using gold Open Access.</p>
  			<p class="u-fonts-sans">Our Open Access option complies with <a data-track="click" data-track-action="click funding link" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">Open Access Policy</a>.</p>
  		</div>
  	</div>
</div>
{/if}

<section class="live-area-wrapper">
<div class="live-area">
  	<section class="row raw">
  		<div class="columns medium-12 main-contents c-slice-heading u-hide">
  			<h2 class="headline">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</h2>
  		</div>
  		<section class="medium-8 teaser-navigation" role="main">
  		<section class="column medium-3">
  			<h4 class="headline-3332373131">{translate key="navigation.infoForReaders"}</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
  				    <li class="sub_menu"><a class="anchor" href="{url page="search" op="titles"}"><span class="anchor-text">View Articles</span></a></li>
  				    <li class="sub_menu"><a class="anchor" href="{url page="notification" op="subscribeMailList"}"><span class="anchor-text">Volume/Issue Alert</span></a></li>
	  				<li class="sub_menu"><a class="anchor" href="{url page="information" op="readers"}"><span class="anchor-text">{translate key="navigation.infoForReaders.long"}</span></a></li>
  				</ul>
  			</div>
  			</nav>
  		</section>
  		<section class="column medium-3">
  			<h4 class="headline-3332373131">{translate key="navigation.infoForAuthors"}</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
  					<li class="sub_menu"><a class="anchor" href="{url page="author" op="submit"}"><span class="anchor-text">Submit your Paper</span></a></li>
  					<li class="sub_menu"><a class="anchor" href="{url page="about" op="submissions" anchor="onlineSubmissions"}"><span class="anchor-text">Guide for Submission</span></a></li>
  					{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li class="sub_menu"><a class="anchor" href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}"><span class="anchor-text">{translate key="about.focusAndScope"}</span></a></li>{/if}
  					{if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li class="sub_menu"><a class="anchor"  href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}"><span class="anchor-text">{translate key="about.publicationFrequency"}</span></a></li>{/if}
  					<li class="sub_menu"><a class="anchor" href="{url page="about" op="submissions" anchor="submissionPreparationChecklist"}"><span class="anchor-text">{translate key="about.submissionPreparationChecklist"}</span></a></li>
  					<li class="sub_menu"><a class="anchor" href="{url page="information" op="authors"}"><span class="anchor-text">{translate key="navigation.infoForAuthors.long"}</span></a></li>
  					{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')}<li class="sub_menu"><a class="anchor" href="{url page="about" op="editorialPolicies" anchor="authorSelfArchivePolicy"}"><span class="anchor-text">{translate key="about.authorSelfArchive"}</span></a></li>{/if}
  					{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}<li class="sub_menu"><a class="anchor" href="{url page="about" op="submissions" anchor="copyrightNotice"}"><span class="anchor-text">{translate key="about.copyrightNotice"}</span></a></li>{/if}
  					<li class="sub_menu"><a class="anchor" href="{url page="about" op="contact"}"><span class="anchor-text">{translate key="about.contact"}</span></a></li>
  				</ul>
  			</div>
  			</nav>
  		</section>
  		<section class="column medium-3">
  			<h4 class="headline-3332373131">{translate key="navigation.infoForLibrarians"}</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
  				    <li class="sub_menu"><a class="anchor" href="{url page="about" op="editorialPolicies" anchor="archiving"}"><span class="anchor-text">{translate key="about.archiving"}</span></a></li>
  					<li class="sub_menu"><a class="anchor" href="{url page="information" op="librarians"}"><span class="anchor-text">{translate key="navigation.infoForLibrarians.long"}</span></a></li>
  				</ul>
  			</div>
  			</nav>
  		</section>
  		<section class="column medium-3">
  			<h4 class="headline-3332373131">Editors and Reviewers</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
			      <li class="sub_menu"><a class="anchor" href="{url page="about" op="editorialPolicies" anchor=peerReviewProcess}"><span class="anchor-text">{translate key="about.peerReviewProcess"}</span></a></li>
			      {foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem name=customAboutItems}
			        {if !empty($customAboutItem.title)}<li class="sub_menu"><a class="anchor" href="{url page="about" op="editorialPolicies" anchor=$customAboutItem.title|replace:" ":""|escape}"><span class="anchor-text">{$customAboutItem.title|escape}</span></a></li>{/if}
			      {/foreach}
			      {call_hook name="Templates::About::Index::Policies"}
  				</ul>
  			</div>
  			</nav>
  		</section>
		</section>
		<aside class="sangia-aside column medium-4">
		    <section class="box2-321956623 box">
			<h4 class="headline-424997076">Publish with us</h4>
			<div><p>Find out more about how to publish in this journal<br></p></div><a title="Submission" href="{url page="information" op="authors"}" class="button-base-3479930902"><span class="button-label-256261418">More Information</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-1913844361"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
			</section>
			{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
			<section class="sign-up-form box2-321956623 box"><div>
				<div id="alert-widget_body">
					<div class="sign-up"><h4>Get informed about updates</h4>
					<p>Receive updates for all new issues/articles published {$currentJournal->getLocalizedTitle()|strip_tags|escape}.</p><a id="signUpSRMPub" class="sangia-button" href="{url page="notification" op="subscribeMailList"}"><button type="submit"><span>Sign Up for Alerts</span><svg width="32" height="32" viewBox="0 0 32 32"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></button></a>
				    </div>
				</div></div>
			</section>
			{/if}
		</aside>  
	</section>	
</div>
</section>

</div>{* journal-content *}

<div class="editors-socialMedia">
	<section class="live-area-wrapper {if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}cms-highlight-2{else}{if $enableAnnouncementsHomepage}{else}cms-highlight{/if}{/if}">
		<div class="live-area">
		  	<div class="row raw">
		  		<div class="column medium-12">
		  			<h2 class="headline-1283242569">Stay informed</h2>
		  		</div>
		  	</div>	
		    <div class="row raw">
			    <div class="columns small-12 ">
				<div class="cms-collection-list">
					<ul class="small-block-grid-1 medium-block-grid-3">
						<li>
							<div id="id19" class=""><a class="cms-teaser-text" href="{url page="about" op="editorialTeam"}" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id3" ><div class="article-meta"></div><div class="cms-font-icon">N</div><h3>Contact an Editor</h3></div></a></div></li>
						<li>
							<div id="id1a" class=""><a class="cms-teaser-text" href="//twitter.com/SangiaNews" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id3" ><div class="article-meta"></div><div class="cms-font-icon">1</div><h3>Follow @Sangia_Publishing</h3><p>Follow Sangia Publishing Journals</p></div></a></div></li>
						<li>
							<div id="id1b" class=""><a class="cms-teaser-text" href="//www.facebook.com/sangiapublishing" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id3" ><div class="article-meta"></div><div class="cms-font-icon">2</div><h3>Sangia Publishing group</h3><p>Follow Fanpage Sangia Publishing group</p></div></a></div></li>
					</ul>
				</div>
			</div>
		    </div>
        </div>
    </section>
</div>

<section class="u-container u-mb-32">
	<div class="live-area">
		<div class="row">					
    	    {if $currentJournal->getSetting('publisherInstitution')}
    	    <div class="membership medium-6 u-mb-32">
    	        <section class="membership__item">
    	            <h3 class="headfoot u-mb-16">Copyright</h3>
    	            <div class="c-meta__copyright text-s">Copyright © {$smarty.now|date_format:"%Y"} {if $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing" || $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"} {$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}. Production & hosting by Sangia Publishing{/if}, under <a href="{$currentJournal->getSetting('licenseURL')}" target="_blank">Creative Commons Attribution Licensed</a>.{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''} <span class="popup">Go to <a href="{url page="about" op="submissions" anchor="copyrightNotice"}" target="_blank">{translate key="about.copyrightNotice"}</a> for more information about copyrights.</span>{/if}</div>
    	        </section>
    	    </div>
    	    {/if}
			
    	    {assign var="contactMailingAddress" value=$currentJournal->getLocalizedSetting('contactMailingAddress')}
    	    {if $currentJournal->getLocalizedSetting('contactMailingAddress')}
    	    <div class="membership medium-6 u-mb-32">
    	        <section class="membership__item">
    	            <h3 class="headfoot u-mb-16">Editorial Office</h3>
    	            <div class="c-meta__editorial text-s">{$currentJournal->getLocalizedSetting('contactMailingAddress')|strip_tags}</div>
    	        </section>
    	    </div>
    		{/if}
			
			{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $alternatePageHeader}
			<div class="indexedin u-mt-16">
				<ul class="indexedin-link">
					<li class="indexedin-item" data-test="logo-listing"><a href="https://garuda.kemdikbud.go.id/journal/" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/assets/img/static/garuda.png?as=webp" type="image/webp">
					        <img class="garuda u-mb-0" src="{$baseUrl}/assets/img/static/garuda.png" loading="lazy" alt="Garba Rujukan Digital" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://www.crossref.org/" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/assets/img/static/indexedin_06.png?as=webp" type="image/webp">
					        <img class="crossref u-mb-0" src="{$baseUrl}/assets/img/static/indexedin_06.png" loading="lazy" alt="CrossRef" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://sinta.kemdikbud.go.id/journals" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/assets/img/static/sinta.png?as=webp" type="image/webp">
					        <img class="sinta u-mb-0" src="{$baseUrl}/assets/img/static/sinta.png" loading="lazy" alt="Sinta" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://scholar.google.com/" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/assets/img/static/indexedin_02.gif?as=webp" type="image/webp">
					        <img class="scholar u-mb-0" src="{$baseUrl}/assets/img/static/indexedin_02.gif" loading="lazy" alt="Google Scholar" />
					    </picture>
					    </a></li>
				</ul>
			</div>
			{/if}
		</div>
	</div>
</section>    

<section id="cope-ithenticate-container" class="u-container u-mb-48">
    <ul class="app-membership-row">
        <li class="app-membership-row__item" data-test="logo-listing">
            <a href="http://publicationethics.org/" data-test="logo-link" target="_blank">
                <picture>
                    <source srcset="{$baseUrl}/assets/img/static/logos/cope-8d92c4b829.png?as=webp" type="image/webp">
                    <img class="u-mb-0" alt="Committee on Publication Ethics" data-test="logo-image" src="{$baseUrl}/assets/img/static/logos/cope-8d92c4b829.png" loading="lazy">
                </picture>
            </a>
            <p class="c-meta u-mt-16" data-test="logo-label">This journal is a member of and subscribes to the principles of the Committee on Publication Ethics.</p>
        </li>
        <li class="app-membership-row__item" data-test="logo-listing">
            <a href="http://www.ithenticate.com/" data-test="logo-link" target="_blank">
                <picture>
                    <source srcset="{$baseUrl}/assets/img/static/logos/ithenticate-eabe2377a3.png?as=webp" type="image/webp">
                    <img class="u-mb-0" alt="Ithenticate Plagiarism Detection" data-test="logo-image" src="{$baseUrl}/assets/img/static/logos/ithenticate-eabe2377a3.png" loading="lazy">
                </picture>
            </a>
            <p class="c-meta u-mt-16" data-test="logo-label"></p>
        </li>
    </ul>
</section>
    
{include file="common/footer.tpl"}
