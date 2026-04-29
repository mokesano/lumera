{**
 * templates/common/feature/article_Hero.tpl
 *
 * Copyright (c) 2018-2025 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Articles Hero and Featured
 *
 *}
{* Include article hero PHP dengan proxy yang sudah diperbaiki *}
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($articleHeroFile = 'plugins/themes/' . $matches[1] . '/php/hero_futured/article_hero.php')) {
        include_once($articleHeroFile);
        break;
    }
}
{/php}

{* Hero Article (Main Featured) *}
{if $heroArticle && count($heroArticle) > 0}
{foreach from=$heroArticle item=article}
<article>
    <div data-track-component="hero" class="u-container content-loaded">
        <div class="c-hero c-hero--flush-md-max">
            {* Cover Image *}
            {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
            {if $article.cover_image.file_exists}
            <div class="c-hero__image">
	    	<picture><source type="image/webp" srcset="{$article.cover_image.file_url}?as=webp 450w, {$article.cover_image.file_url}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img src="{$article.cover_image.file_url}" alt="" itemprop="image" loading="lazy">
	    	</picture>
	    	</div>
            {elseif $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
  			<div class="c-hero__image">
  			<picture>
  				<source srcset="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp 450w, {$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px" type="image/webp">
  				<img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}"{else} title="Cover issue"{/if}{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
  			</picture>
  			</div>
  			{elseif $displayHomepageImage && is_array($displayHomepageImage)}
  			<div class="c-hero__image">
  			<picture>
  				<source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 450w, {$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px" type="image/webp">
  				<img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" title="" alt="" itemprop="image">
  			</picture>
  			</div>
  			{else}
  			<div class="c-hero__image">
  			<picture>
  				<source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp 450w, //assets.sangia.org/img/img-default_v2.png?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px" type="image/webp">
  				<img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" title="" alt="" itemprop="image">
  			</picture>
  			</div>
	    	{/if}
	    	<div class="c-hero__copy">
	    	    {* Title *}
	    	    <h2 class="c-hero__title" itemprop="name headline">
	    	        <a class="c-hero__link u-link-faux-block" href="{$article.article_url}" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article.title}</a>
	            </h2>
	            {* Abstract *}
	            {if $article.abstract}
	            <p class="c-hero__summary ellipsis u-mb-8" itemprop="description">{$article.abstract}</p>
	            {/if}
	            {* Authors *}
	            {if $article.authors && count($article.authors) > 0}
	            <ul class="c-author-list c-author-list--compact c-author u-hide-sm-max u-mb-4 u-mt-auto" data-test="author-list">{foreach from=$article.authors item=author name=authorLoop}<li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name">{$author.full_name}</span>{if $author.first_name !== $author.last_name}<span itemprop="given-name">{$author.first_name}</span>{/if}{if $author.middle_name}<span itemprop="middle-name">{$author.middle_name}</span>{/if}<span itemprop="surname">{$author.last_name}</span></li>{/foreach}
	            </ul>
	            {/if}
	            {* Article Meta *}
				<div class="c-card__section c-meta u-hide-sm-max"><span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$article.article_type}</span></span>{if $article.is_open_access}<span class="c-meta__item" itemprop="openAccess" data-test="open-access">Open Access</span>{/if}<time class="c-meta__item" datetime="{$article.date_published_formatted}" itemprop="datePublished">{$article.date_published_formatted|date_format:"%d %B %Y"}</time>
				</div>
			</div>
		</div>
	</div>
</article>
{/foreach}
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
{/if}

{* Latest Articles Grid *}
{if $latestArticles && count($latestArticles) > 0}
<section id="featured-content" class="area-wrapper featured u-mb-32" data-track-component="featured content">
	<h2 class="u-visually-hidden">Featured Content</h2>
	<div class="u-container content-loaded">

<ul class="app-featured-row">
    {foreach from=$latestArticles item=article}
	<li class="app-featured-row__item last">
	    <div class="u-full-height" data-native-ad-placement="false">
	        <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
	            <div class="c-card__layout u-full-height">
	                {* Cover Image *}
	                {if $article.cover_image.file_exists}
	                <div class="c-card__image">
					<picture>
						<source srcset="{$article.cover_image.file_url}?as=webp 290w" type="image/webp" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
						<img class="lazyload" loading="lazy" src="{$article.cover_image.file_url}" alt="" itemprop="image">
					</picture>
					</div>
					{/if}
					<div class="c-card__body u-display-flex u-flex-direction-column">
					    {* Title *}
					    <h3 class="c-card__title" itemprop="name headline"><a class="c-card__link u-link-inherit" href="{$article.article_url}" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article.title}</a>
					    </h3>
					    {* Abstract *}
					    {if $article.abstract}
						<div class="c-card__summary ellips u-mb-16 u-hide-sm-max" itemprop="description"><p>{$article.abstract}</p>
						</div>
						{/if}
					</div>
				</div>
				<div class="c-card__section c-meta">
				    {* Authors *}
				    {if $article.authors && count($article.authors) > 0}
					<ul class="c-author-list c-author-list--compact c-author-list--separated u-mb-4 u-mt-auto" data-test="author-list">{foreach from=$article.authors item=author name=authorLoop}<li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name">{$author.full_name}</span>{if $author.first_name !== $author.last_name}<span itemprop="given-name">{$author.first_name}</span>{/if}{if $author.middle_name}<span itemprop="middle-name">{$author.middle_name}</span>{/if}<span itemprop="surname">{$author.last_name}</span></li>{/foreach}
					</ul>
					{/if}
					<span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$article.article_type|escape|capitalize|replace:'Research':''}</span></span>{if $article.is_open_access}<span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">Open Access</span></span>{/if}<time class="c-meta__item" datetime="{$article.date_published_formatted}" itemprop="datePublished">{$article.date_published_formatted|date_format:"%d %b %Y"}</time>
				</div>
			</article>
		</div>
	</li>
	{/foreach}
	<li class="app-featured-row__item app-featured-row__item--current-issue">
	    <div class="c-card c-card--flush u-full-height">
	        <a class="c-card__image" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="image">
	            {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
	            {if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
	  			<picture>
	  				<source srcset="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp" type="image/webp">
	  				<img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}"{else} title="Cover issue"{/if}{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
	  			</picture>
	  			{elseif $displayHomepageImage && is_array($displayHomepageImage)}
	  			<picture>
	  				<source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp" type="image/webp">
	  				<img class="lazyload" loading="lazy" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" title="" alt="Cover issue" itemprop="image">
	  			</picture>
	  			{else}
	  			<picture>
	  				<source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp" type="image/webp">
	  				<img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" title="" alt="Cover issue" itemprop="image">
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
{/if}