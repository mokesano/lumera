{**
 * lib/pkp/templates/announcement/index.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of announcements.
 *
 *}
{strip}
{assign var="pageTitle" value="announcement.announcements"}
{assign var="pageId" value="announcement.announcements"}
{include file="common/header.tpl"}
{/strip}

<div id="announcementList" class="app-announcement-list-row announcements">
    
    {if $announcementsIntroduction != null}    
    <article class="app-announcement-list-row__item c-card--flush announcement">
    	<div class="c-card__layout u-full-height intro">
    		<div class="c-card__body u-display-flex u-flex-direction-column">
    		    <div itemprop="description" class="c-card__announcement u-hide-sm-max intro">{$announcementsIntroduction|nl2br}</div>
    		</div>
    	</div>
    </article>
    {/if}
    
    {iterate from=announcements item=announcement}
    <article class="app-announcement-list-row__item c-card--flush announcement">
        <div class="c-card__layout u-full-height">
            {if $announcement->getAnnouncementTypeName() == "Call for" || $announcement->getAnnouncementTypeName() == "Editor"}
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-megaphone.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-megaphone.png" alt="Icon of a megaphone, indicating the Call for Papers page." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            {elseif $announcement->getAnnouncementTypeName() == "Notice" || $announcement->getAnnouncementTypeName() == "Noted" || $announcement->getAnnouncementTypeName() == "News"}
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-noted.jpg?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-noted.jpg" alt="Icon of a newspaper, indicating the News page." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            {elseif $announcement->getAnnouncementTypeName() == "Metric" || $announcement->getAnnouncementTypeName() == "Indexed"}
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-metric.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-metric.png" alt="Icon of metric." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            {elseif $announcement->getAnnouncementTypeName() == "Social"}
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-twitter.png.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-twitter.png" alt="Icon of Twitter." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            {elseif $announcement->getAnnouncementTypeName() == "Join Us"}
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-enginer.png.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-enginer.png" alt="Icon of cogs, indicating the Engineering scope expansion announcement." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            {/if}
            <div class="c-card__body u-display-flex u-flex-direction-column">
                {if $announcement->getTypeId()}
                <h2 class="headline headline-2545795530 u-h2" itemprop="name headline">
                    {$announcement->getAnnouncementTypeName()|escape}: {$announcement->getLocalizedTitle()|escape}
                </h2>
                {else}
                <h2 class="headline headline-2545795530 u-h2" itemprop="name headline">
                    {$announcement->getLocalizedTitle()|escape}
                </h2>
                {/if}
                <div itemprop="description" class="c-card__announcement u-mb-24 u-hide-sm-max intro">{$announcement->getLocalizedDescriptionShort()|nl2br}</div>
                <div class="c-card__summary u-flex-direction-column details">
                    <time class="published posted">{translate key="announcement.posted"}: {$announcement->getDatePosted()|date_format:"%e %B %Y"}</time>
                    {if $announcement->getLocalizedDescription() != null}
                    <span class="more"><a itemprop="url" href="{url op="view" path=$announcement->getId()}">View {translate key="announcement.viewLink"}</a>
                    </span>
                    {/if}
                </div>
            </div>
        </div>
    </article>
    {/iterate}
    
    <div id="colspan" class="colspan u-mb-0">
    {if $announcements->wasEmpty()}
        <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
            <div class="c-pagination">
                {translate key="announcement.noneExist"}
            </div>
        </section>
    {else}
    	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
    	    <div class="c-pagination">{page_info iterator=$announcements}</div>
    	</section>
    	<section class="u-display-flex u-justify-content-center">
    	    <div class="c-pagination">{page_links anchor="announcements" name="announcements" iterator=$announcements}</div>
    	</section>
    {/if}
    </div>

</div>

{include file="common/footer.tpl"}
