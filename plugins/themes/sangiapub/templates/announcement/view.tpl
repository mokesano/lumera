{**
 * view.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View full announcement text.
 *
 *}
{strip}
{assign var="pageTitleTranslated" value=$announcementTitle}
{assign var="pageId" value="announcement.view"}
{include file="common/header.tpl"}
{/strip}
<article class="announcement">
    <div id="announcementDescription" itemprop="description" class="c-card__description u-mb-24">{$announcement->getLocalizedDescription()|nl2br}
    </div>
    <div class="details">
    	<time class="published posted">{translate key="announcement.posted"}: {$announcement->getDatePosted()|date_format:"%e %B %Y"}</time>
    	<span class="more"></span>
    </div>
</article>

{include file="common/footer.tpl"}

