{**
 * templates/issue/archive.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue Archive.
 *
 *}

{strip}
{assign var="pageTitle" value="archive.archivesc"}
{include file="common/header-ISSUE.tpl"}
{/strip}
{$currentJournal->getSetting('$journalTitle')}

{if $currentJournal->getLocalizedSetting('history') != ''}
<div id="journal-history-link" class="u-hide">
	<span class="icon-container info history-link">
		This journal was previously published under other titles
        <a href="{url page="about" op="history" anchor=""}">(view Journal {translate key="about.history"})</a>
    </span>
</div>
{/if}

<div class="container cleared container-type-issue-grid" data-container-type="issue-grid" data-track-component="issue grid">

	{iterate from=issues item=issue}
	{if $issue->getYear() != $lastYear}
		{if !$notFirstYear}
			{assign var=notFirstYear value=1}
		{else}
			</div>
		</div>
		{/if}
			<div class="border-top-1 border-gray-medium"></div>
	<div class="container cleared container-type-title" data-container-type="title">
		<div id="{translate key="issue.volume"}{$issue->getVolume()|escape}" class="content mb20 mt20 mq1200-padded">
			{assign var=lastYear value=$issue->getYear()}
			<h3 class="c-card__title u-h2">{translate key="issue.volume"} {$issue->getVolume()|escape}<span class="text-gray-light"> — {$issue->getDatePublished('issue.firstYear')|date_format:"%Y"}</span></h3>
		</div>
		<div class="content mb30 mq1200-padded">
			<section>
				<ul id="issue-list" class="ma0 clean-list grid-auto-fill grid-auto-fill-w220 very-small-column medium-row-gap">
	{/if}
				{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
				<li id="issue-{$issue->getId()}" class="flex-box flex-box-column background-white">
					<div data-component="equal-height-item">
						<a class="kill-hover flex-box-item" href="{url op="view" path=$issue->getBestIssueId($currentJournal)}" data-track="click" data-track-action="view issue" data-track-label="link">
							<img src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} class="image-constraint pt10" alt="" style="margin: 0 auto;max-width: 200px;">
							<h3 class="h2 pa20 equalize-line-height text13">{translate key="issue.no"}. {$issue->getNumber()|escape}
								<span class="pin-right sans-serif text-gray">{$issue->getDatePublished()|date_format:"%B %Y"}</span>
							</h3>
						</a>
						<div class="pa20 text13 suppress-bottom-margin mq480-hide text-gray-light pt10" data-show-more="" data-show-more-length="200" data-hellip-disable-expand="true">
							{if $issue->getLocalizedTitle($currentJournal)}<h3 class="h3 strong text14">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>{/if}
							{if $issue->getLocalizedDescription()}<p>{$issue->getLocalizedDescription()|truncate:145:"..."|strip_unsafe_html|nl2br}</p>{/if}
						</div>
					</div>
				</li>
				{else}
				<li id="issue-{$issue->getId()}" class="flex-box flex-box-column background-white">
					<div data-component="equal-height-item">
						<a class="kill-hover flex-box-item" href="{url op="view" path=$issue->getBestIssueId($currentJournal)}" data-track="click" data-track-action="view issue" data-track-label="link">
							<img class="u-hide image-constraint pt10" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}/homepageImage_en_US.jpg" alt="" style="margin: 0 auto;max-width: 200px">
							<h3 class="h2 pa20 equalize-line-height text13">{translate key="issue.no"}. {$issue->getNumber()|escape}
								<span class="pin-right sans-serif text-gray">{$issue->getDatePublished()|date_format:"%B %Y"}</span>
							</h3>
						</a>
						<div class="pa20 text13 suppress-bottom-margin mq480-hide text-gray-light pt10" data-show-more="" data-show-more-length="200" data-hellip-disable-expand="true">
							{if $issue->getLocalizedTitle($currentJournal)}<h3 class="h3 strong text14">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>{/if}
							{if $issue->getLocalizedDescription()}<p>{$issue->getLocalizedDescription()|truncate:145:""|strip_unsafe_html|nl2br}</p>{/if}
						</div>
					</div>
				</li>
				{/if}
	{/iterate}
			</ul>
		</section>
	</div>		


{if $notFirstYear}</div>{/if}

    {if !$issues->wasEmpty()}
    </div>
	<div class="colspan u-mb-0" id="colspan">
	    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	        <div class="c-pagination">View {page_info iterator=$issues}</div>
        </section>
	    <section class="u-display-flex u-justify-content-center">
	        <div class="c-pagination">{page_links anchor="issues" name="issues" iterator=$issues}
	       </div>
	    </section>
	</div>
    {else}
    <div class="container cleared container-type-title" data-container-type="title">
        <div class="border-top-1 border-gray-medium"></div>
        <div class="content mb20 mt20">
            <div class="s-nocontent">{translate key="current.noCurrentIssueDesc"}</div>
        </div>
    </div>
    {/if}
    
</div>

{include file="common/footer.tpl"}

