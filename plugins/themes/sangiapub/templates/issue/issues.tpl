{**
 * templates/issue/issues.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue
 *
 *}

{foreach name=sections from=$publishedArticles item=section key=sectionId}

{foreach from=$section.articles item=article}

	{assign var=articlePath value=$article->getBestArticleId($currentJournal)}
	{assign var=articleId value=$article->getId()}
        
	{if $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage() && !$article->getHideCoverPageToc($locale)}
		{assign var=showCoverPage value=true}
	{else}
		{assign var=showCoverPage value=false}
	{/if}

	{if $article->getLocalizedAbstract() == ""}
		{assign var=hasAbstract value=0}
	{else}
		{assign var=hasAbstract value=1}
	{/if}

	{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
		{assign var=hasAccess value=1}
	{else}
		{assign var=hasAccess value=0}
	{/if}
	
	{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
	{if $subscriptionRequired && $showGalleyLinks && $restrictOnly}

<li class="app-featured-row__item">
	<div class="u-full-height" data-native-ad-placement="false">
		<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
			<div class="c-card__layout u-full-height">
				{if $showCoverPage}
				<div class="c-card__image">
					<picture>
						<source srcset="{$coverPagePath|escape}{$article->getFileName($locale)|escape}?as=webp 290w" type="image/webp" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px" >
						<img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$article->getFileName($locale)|escape}" alt="" itemprop="image" />
					</picture>
				</div>
				{/if}
				{call_hook name="Templates::Article::Article::ArticleCoverImage"}

				<div class="c-card__body u-display-flex u-flex-direction-column">
					<h3 class="c-card__title" itemprop="name headline">
						<a class="c-card__link u-link-inherit" href="{url page="article" op="view" path=$articlePath}" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
					</h3>
					{if $article->getLocalizedAbstract()}
					<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p>{$article->getLocalizedAbstract()|nl2br}</p></div>
					{/if}
				</div>
			</div>
			<div class="c-card__section c-meta">
				{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
				{else}
				<ul class="c-author-list c-author-list--compact c-author-list--separated u-mb-4 u-mt-auto" data-test="author-list">{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors}{assign var=fullname value=$author->getFullName()}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}<li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name">{$fullname|escape}</span>{if $authorFirstName !== $authorLastName}<span itemprop="given-name">{$authorFirstName}</span>{/if}{if $authorMiddleName}<span itemprop="middle-name">{$authorMiddleName|truncate:1:"."}</span>{/if}{if $authorLastName}<span itemprop="surname">{$authorLastName}</span>{/if}</li>{/foreach}
				</ul>
				{/if}
				{if $section.title}<span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$section.title|escape|capitalize|replace:'Research':''}</span></span>{/if}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}<span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">{translate key="reader.openAccess"}</span></span>{/if}<span class="u-hide c-meta__item" data-test="article.pages">{if $article->getPages()}P: {$article->getPages()|escape}{else}e{$article->getId()}{/if}</span><time class="c-meta__item" datetime="{$article->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$article->getDatePublished()|date_format:"%d %b %Y"}</time>
			</div>
		</article>
	</div>
</li>

{else}

<li class="app-featured-row__item">
	<div class="u-full-height" data-native-ad-placement="false">
		<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
			<div class="c-card__layout u-full-height">
				{if $showCoverPage}
				<div class="c-card__image">
					<picture>
						<source srcset="{$coverPagePath|escape}{$article->getFileName($locale)|escape}?as=webp 290w" type="image/webp" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px" >
						<img class="lazyload" loading="lazy" src="{$coverPagePath|escape}{$article->getFileName($locale)|escape}" alt="" itemprop="image" />
					</picture>
				</div>		
				{/if}
				{call_hook name="Templates::Article::Article::ArticleCoverImage"}

				<div class="c-card__body u-display-flex u-flex-direction-column">
					<h3 class="c-card__title" itemprop="name headline">
						<a class="c-card__link u-link-inherit" href="{url page="article" op="view" path=$articlePath}" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
					</h3>
					{if $article->getLocalizedAbstract()}
					<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p>{$article->getLocalizedAbstract()|nl2br}</p></div>
					{/if}
				</div>
			</div>
			<div class="c-card__section c-meta">
				{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
				{else}
				<ul class="c-author-list c-author-list--compact c-author-list--separated u-mb-4 u-mt-auto" data-test="author-list">{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors}{assign var=fullname value=$author->getFullName()}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}<li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name">{$fullname|escape}</span>{if $authorFirstName !== $authorLastName}<span itemprop="given-name">{$authorFirstName}</span>{/if}{if $authorMiddleName}<span itemprop="middle-name">{$authorMiddleName}</span>{/if}{if $authorLastName}<span itemprop="surname">{$authorLastName}</span>{/if}</li>{/foreach}
				</ul>
				{/if}
				{if $section.title}<span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$section.title|escape|capitalize|replace:'Research':''}</span></span>{/if}{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}<span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">{translate key="reader.openAccess"}</span></span>{/if}<span class="u-hide c-meta__item" data-test="article.pages">{if $article->getPages()}P: {$article->getPages()|escape}{else}e{$article->getId()}{/if}</span><time class="c-meta__item" datetime="{$article->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$article->getDatePublished()|date_format:"%d %b %Y"}</time>
			</div>
		</article>
	</div>
</li>

	{/if}
	{/if}

{/foreach}

{/foreach}