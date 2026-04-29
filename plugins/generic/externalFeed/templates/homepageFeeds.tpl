<div id="externalFeedsHome">
{foreach from=$processedExternalFeeds item=feedData}
    {if $feedData.has_items}
        <h2 class="u-container c-slice-heading">{$feedData.title|escape}</h2>
        
        <div id="contents" class="u-container u-mb-0">
            <ul class="app-news-row externalFeeds">
                
                {* --- FEATURED ITEM (1 Utama) --- *}
                {foreach from=$feedData.featured_items item=item}
                    <li class="app-news-row__item app-news-row__item--major">
                        <div class="u-full-height title" data-native-ad-placement="false">
                            <article class="u-full-height c-card c-card--major c-card--dark" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                                <div class="c-card__layout u-full-height">
                                    
                                    <div class="c-card__image">
                                        <picture>
                                            {assign var=enclosure value=$item->get_enclosure()}
                                            {if $enclosure}
                                                {foreach from=$enclosure->get_link() item=thumbnailUrl}
                                                    {if $thumbnailUrl}
                                                        <source type="image/webp" srcset="{$thumbnailUrl|escape}?as=webp 160w, {$thumbnailUrl|escape}?as=webp 290w" sizes="(max-width: 768px) 290px, (max-width: 1200px) 485px, 485px">
                                                        <img loading="lazy" src="{$thumbnailUrl|escape}" itemprop="image">
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </picture>
                                    </div>
                                    
                                    <div class="c-card__body u-display-flex u-flex-direction-column">
                                        <h3 class="c-card__title" itemprop="name headline">
                                            <a class="c-card__link u-link-inherit" href="{$item->get_permalink()|escape}" target="_blank">{$item->get_title()|escape|default:"No Title"}</a>
                                        </h3>
                                        <div class="c-card__summary u-mb-16">
                                            <p>{$item->get_content()|strip_tags|truncate:190:"..."|escape}</p>
                                        </div>
                                        
                                        <div class="c-card__section c-meta">
                                            <div>
                                                {assign var=authors value=$item->get_authors()}
                                                {if $authors}
                                                    {foreach from=$authors item=author}
                                                        <span class="u-color-inherit c-author-list c-author-list--compact">{$author->get_name()|escape}</span>
                                                    {/foreach}
                                                {/if}
                                            </div>
                                            
                                            <span class="c-meta__item" data-test="article.type">
                                                {assign var=category value=$item->get_category()}
                                                {if $category}
                                                    <span class="c-meta__type">{$category->get_label()|escape}</span>
                                                {/if}
                                            </span>
                                            
                                            <time class="c-meta__item" datetime="{$item->get_date('Y-m-d')|escape}" itemprop="datePublished">{$item->get_date('d M Y')|escape}</time>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </li>
                {/foreach}

                {* --- RECENT ITEMS (Sisa item) --- *}
                {foreach from=$feedData.recent_items item=item}
                    <li class="app-news-row__item title">
                        <div class="u-full-height title" data-native-ad-placement="false">
                            <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                                <div class="c-card__layout u-full-height">
                                    
                                    {assign var=enclosure value=$item->get_enclosure()}
                                    {if $enclosure}
                                        <div class="c-card__image">
                                            <picture>
                                                {foreach from=$enclosure->get_link() item=thumbnailUrl}
                                                    {if $thumbnailUrl}
                                                        <source type="image/webp" srcset="{$thumbnailUrl|escape}?as=webp 160w, {$thumbnailUrl|escape}?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                                                        <img loading="lazy" src="{$thumbnailUrl|escape}" itemprop="image">
                                                    {/if}
                                                {/foreach}
                                            </picture>
                                        </div>
                                    {/if}
                                    
                                    <div class="c-card__body u-display-flex u-flex-direction-column">
                                        <h3 class="c-card__title" itemprop="name headline">
                                            <a class="c-card__link u-link-inherit" href="{$item->get_permalink()|escape}" target="_blank">{$item->get_title()|escape|default:"No Title"}</a>
                                        </h3>
                                        <div class="u-hide c-card__summary u-mb-16 u-hide-sm-max">
                                            <p>{$item->get_description()|strip_tags|escape}</p>
                                        </div>
                                        
                                        <div class="c-card__section c-meta">
                                            <div>
                                                {assign var=authors value=$item->get_authors()}
                                                {if $authors}
                                                    {foreach from=$authors item=author}
                                                        <span class="c-author-list c-author-list--compact">{$author->get_name()|escape}</span>
                                                    {/foreach}
                                                {/if}
                                            </div>
                                            
                                            <span class="c-meta__item" data-test="article.type">
                                                {assign var=category value=$item->get_category()}
                                                {if $category}
                                                    <span class="c-meta__type">{$category->get_label()|escape}</span>
                                                {/if}
                                            </span>
                                            
                                            <time class="c-meta__item" datetime="{$item->get_date('Y-m-d')|escape}" itemprop="datePublished">{$item->get_date('d M Y')|escape}</time>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </li>
                {/foreach}
                
            </ul>
        </div>
    {/if}
{/foreach}
</div>