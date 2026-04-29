{**
 * templates/common/feature/mostDownloads.tpl
 *
 * Copyright (c) 2018-2025 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Most Downloads Articles - FIXED
 *
 *}
{* Include most downloaded PHP langsung di template *}
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($mostDownloadedFile = 'plugins/themes/' . $matches[1] . '/php/most_download/most_downloaded.php')) {
        include_once($mostDownloadedFile);
        break;
    }
}
{/php}

{* Hitung artikel di bagian atas template *}
{assign var="articleCount" value=0}
{if $publishedArticles}
    {foreach from=$publishedArticles item=section}
        {if $section.articles}
            {math equation="x + y" x=$articleCount y=$section.articles|@count assign="articleCount"}
        {/if}
    {/foreach}
{/if}

<section id="latest-downloaded" class="live-area u-mt-48 u-mb-32" data-track-component="latest downloaded grid">
    <div class="row raw">
        
    <div id="articles-downloaded" class="c-article-most__downloaded">
        <div class="u-container c-section-heading c-slice-heading">
            <h2 class="titles u-ma-0">
                <span class="title">Most downloads</span>
                <a class="c-section-heading__link" data-track="click" data-track-action="view all" data-track-label="button" href="{url page="about" op="statistics"}"><svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="20" width="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg>
                </a>    
            </h2>
            <span class="u-font-sans">Last update: {$lastUpdateDate|date_format:"%d %b %Y, %H:%M"}. (Smart detection updates. Regular updates weekly.)</span>
        </div>
    </div>        
    <div class="u-container" id="contents">        
        <ul class="u-list-reset{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $articleCount >= 6} app-reviews-row{/if}">
<li class="app-reviews-row__main">
    <ul class="app-reviews-row__grid">
    {* Top Downloaded Article (Klaster Pertama) - FIXED *}
    {if $topDownloadedArticle && count($topDownloadedArticle) > 0}
        {foreach from=$topDownloadedArticle item=article}
        <li class="app-reviews-row__item app-reviews-row__item--major">
            <div class="u-full-height">
                <div class="u-full-height" data-native-ad-placement="false">
                    <article class="u-full-height c-card c-card--flush c-card--major" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                        <div class="c-card__layout u-full-height">
                            {if $article.cover_image.file_exists}
                            <div class="c-card__image"><picture><source type="image/webp" srcset="{$article.cover_image.file_url}?as=webp 290w,{$article.cover_image.file_url}?as=webp 485w" sizes="(max-width: 768px) 290px,(max-width: 1200px) 485px,485px"><img src="{$article.cover_image.file_url}" alt="{$article.title}" itemprop="image"></picture>
                            </div>
                            {else}
                            <div class="c-card__image"><picture><source type="image/webp" srcset="//assets.sangia.org/static/images/not-available.webp 450w, //assets.sangia.org/static/images/not-available.webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img src="https://assets.sangia.org/static/images/not-available.webp" alt="{$article.title|escape}" itemprop="image"></picture>
    				        </div>
                            {/if}
                            <div class="c-card__body u-display-flex u-flex-direction-column">
                                <h3 class="c-card__title" itemprop="name headline"><a href="{$article.article_url}" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article.title}</a>
                                </h3>
                                {if $article.abstract}
                                <div data-test="article-description" class="c-card__summary ellipse u-mb-16" itemprop="description"><p>{$article.abstract}</p>
                                </div>
                                {/if}
                                <div class="c-card__section c-meta">
                                    <div>
                                        {if $article.authors && count($article.authors) > 0}<ul data-test="author-list" class="app-author-list app-author-list--compact">{foreach from=$article.authors item=author name=authorLoop}<li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">{if $author.first_name !== $author.last_name}<span itemprop="given-name">{$author.first_name}</span> {/if}{if $author.middle_name}<span itemprop="middle-name">{$author.middle_name}</span>{/if}<span itemprop="surname">{$author.last_name}</span></span></li>{/foreach}</ul>{/if}
                                    </div>
                                    <span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$article.article_type}</span></span>{if $article.is_open_access}<span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">Open Access</span></span>{/if}<time class="c-meta__item" datetime="{$article.date_published_formatted}" itemprop="datePublished">{$article.date_published_formatted|date_format:"%d %B %Y"}</time><span class="c-meta__item rank">🏆 {$article.total_downloads|number_format} times</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </li>
        {/foreach}
    {/if}
        
    {* Second Tier Downloaded Articles (Klaster Kedua) - FIXED *}
    {if $secondTierDownloadedArticles && count($secondTierDownloadedArticles) > 0}
        {foreach from=$secondTierDownloadedArticles item=article}
        <li class="app-reviews-row__item">
            <div class="u-full-height">
                <div class="u-full-height" data-native-ad-placement="false">
                    <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                        <div class="c-card__layout u-full-height">
                            {if $article.cover_image.file_exists}
                            <div class="c-card__image"><picture><source type="image/webp" srcset="{$article.cover_image.file_url}?as=webp 160w,{$article.cover_image.file_url}?as=webp 290w" sizes="(max-width: 640px) 160px,(max-width: 1200px) 290px,290px"><img src="{$article.cover_image.file_url}" alt="{$article.title}" itemprop="image"></picture>
                            </div>
                            {/if}
                            <div class="c-card__body u-display-flex u-flex-direction-column">
                                <h3 class="c-card__title" itemprop="name headline"><a href="{$article.article_url}" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article.title}</a>
                                </h3>
                                {if $article.abstract}
                                <div data-test="article-description" class="c-card__summary ellips u-mb-16 u-hide-sm-max" itemprop="description"><p>{$article.abstract}</p>
                                </div>
                                {/if}
                            </div>
                        </div>
                        <div class="c-card__section c-meta">
                            <div>
                            {if $article.authors && count($article.authors) > 0}<ul data-test="author-list" class="app-author-list app-author-list--compact">{foreach from=$article.authors item=author name=authorLoop}<li itemprop="creator" itemscope="" itemtype="http://schema.org/Person">{if $author.full_name}<span class="u-hide" itemprop="full-name">{$author.full_name}</span>{/if}{if $author.first_name !== $author.last_name}<span itemprop="given-name">{$author.first_name}</span>{/if}{if $author.middle_name}<span itemprop="middle-name">{$author.middle_name}</span>{/if}<span itemprop="surname">{$author.last_name}</span></li>{/foreach}</ul>{/if}
                            </div>
                            <span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$article.article_type}</span></span>{if $article.is_open_access}<span class="c-meta__item" itemprop="openAccess" data-test="open-access">{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $articleCount >= 6}<span class="u-color-open-access">OA</span>{else}<span class="u-color-open-access">Open Access</span>{/if}</span>{/if}{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $articleCount >= 6}<time class="c-meta__item" datetime="{$article.date_published_formatted}" itemprop="datePublished">{$article.date_published_formatted|date_format:"%d %b %Y"}</time>{else}<time class="c-meta__item" datetime="{$article.date_published_formatted}" itemprop="datePublished">{$article.date_published_formatted|date_format:"%d %B %Y"}</time>{/if}
                        </div>
                    </article>
                </div>
            </div>
        </li>
        {/foreach}
    {/if}
    </ul>
</li>
{if $thirdTierDownloadedArticles && count($thirdTierDownloadedArticles) > 0}
<li>
    <ul class="app-reviews-row__side">
    {* Third Tier Downloaded Articles (Klaster Ketiga) - FIXED *}
        {foreach from=$thirdTierDownloadedArticles item=article}
        <li class="app-reviews-row__side-item">
            <div class="u-full-height">
                <div class="u-full-height" data-native-ad-placement="false">
                    <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                        <div class="c-card__layout u-full-height">
                            <div class="c-card__body u-display-flex u-flex-direction-column">
                                <h3 class="c-card__title elipsis" itemprop="name headline"><a href="{$article.article_url}" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article.title}</a>
                                </h3>
                            </div>
                        </div>
                        <div class="c-card__section c-meta">
                            <div>
                            {if $article.authors && count($article.authors) > 0}<ul data-test="author-list" class="app-author-list app-author-list--compact">{foreach from=$article.authors item=author name=authorLoop}<li itemprop="creator" itemscope="" itemtype="http://schema.org/Person">{if $author.full_name}<span class="u-hide" itemprop="full-name">{$author.full_name}</span>{/if}{if $author.first_name !== $author.last_name}<span itemprop="given-name">{$author.first_name}</span>{/if}{if $author.middle_name}<span itemprop="middle-name">{$author.middle_name}</span>{/if}<span itemprop="surname">{$author.last_name}</span></li>{/foreach}</ul>{/if}
                            </div>
                            <span class="c-meta__item" data-test="article.type"><span class="c-meta__type">{$article.article_type}</span></span>{if $article.is_open_access}<span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">OA</span></span>{/if}<time class="c-meta__item" datetime="{$article.date_published_formatted}" itemprop="datePublished">{$article.date_published_formatted|date_format:"%d %b %Y"}</time>
                        </div>
                    </article>
                </div>
            </div>
        </li>
        {/foreach}
    </ul>
</li>
{/if}
        </ul>
    </div>
</section>