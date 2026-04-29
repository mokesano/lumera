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
{include file="common/header-ABOUT.tpl"}
{/strip}

<div id="announcementList" class="app-announcement-list-row announcements">

{if $announcementsIntroduction != null}    
<article class="app-announcement-list-row__item c-card--flush announcement">
	<div class="c-card__layout u-full-height intro">
		<div class="c-card__body u-display-flex u-flex-direction-column">
		    <div itemprop="description" class="c-card__summary u-hide-sm-max intro">{$announcementsIntroduction|nl2br}</div>
		</div>
	</div>
</article>
{/if}

{iterate from=announcements item=announcement}
<article class="app-announcement-list-row__item c-card--flush announcement">
    <div class="c-card__layout u-full-height">
        {if $announcement->getTypeId()}
        <div class="c-card__image">
            <picture>
                <source type="image/webp" srcset="">
                <img data-test="image-1" src="" title="" itemprop="image">
            </picture>
        </div>
        {/if}
        <div class="c-card__body u-display-flex u-flex-direction-column">
            {if $announcement->getTypeId()}
            <h3 class="headline headline-2545795530" itemprop="name headline">
                {$announcement->getAnnouncementTypeName()|escape}: {$announcement->getLocalizedTitle()|escape}
            </h3>
            {else}
            <h3 class="headline headline-2545795530" itemprop="name headline">
                {$announcement->getLocalizedTitle()|escape}
            </h3>
            {/if}
            <div itemprop="description" class="c-card__summary u-mb-24 u-hide-sm-max intro">{$announcement->getLocalizedDescriptionShort()|nl2br}</div>
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
