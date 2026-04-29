{**
 * templates/issue/viewVolumes.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue Archive.
 *
 *}

{strip}
{assign var="pageTitle" value="archive.archives"}
{include file="issue/header-volumes.tpl"}
{/strip}

	<div class="content mb30 mq1200-padded issue-grid">
		<section>
			<ul id="issue-list" class="ma0 clean-list grid-auto-fill grid-auto-fill-w220 very-small-column medium-row-gap">
        	{iterate from=issues item=issue}
			{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
			<li id="{$issue->getId()|string_format:"%07d"}" class="flex-box flex-box-column background-white">
				<div data-component="equal-height-item">
				    {assign var="issueSlug" value=$issue->getNumber()|slugify}
					<a class="kill-hover flex-box-item" href="{native_url page="issue" volume=$volumeId slug=$issueSlug}" data-track="click" data-track-action="view issue" data-track-label="link">
						<img src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" data-src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} class="lazyload image-constraint pt10" alt="" style="margin: 0 auto;max-width: 200px;">
						<h3 class="h2 pa20 equalize-line-height text13">{if $issue->getNumber()|regex_replace:"/[a-zA-Z]/":"" eq $issue->getNumber()}{translate key="issue.no"}. {$issue->getNumber()|strip_tags|nl2br|escape}{else}{$issue->getNumber()|strip_tags|nl2br|truncate:5:"."|escape}{/if}
							<span class="pin-right sans-serif text-gray">{$issue->getDatePublished()|date_format:"%B %Y"}</span>
						</h3>
					</a>
					<div class="pa20 text13 suppress-bottom-margin mq480-hide text-gray-light pt10" data-show-more="" data-show-more-length="200" data-hellip-disable-expand="true">
						{if $issue->getLocalizedTitle($currentJournal)}<h3 class="h3 strong text13">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>{/if}
						{if $issue->getLocalizedDescription()}<p>{$issue->getLocalizedDescription()|strip_tags|nl2br|truncate:270:"..."}</p>{/if}
					</div>
				</div>
			</li>
			{else}
			<li id="{$issue->getId()|string_format:"%07d"}" class="flex-box flex-box-column background-white">
				<div data-component="equal-height-item">
					{assign var="issueSlug" value=$issue->getNumber()|slugify}
					<a class="kill-hover flex-box-item" href="{native_url page="issue" volume=$volumeId slug=$issueSlug}" data-track="click" data-track-action="view issue" data-track-label="link">
					    {if $homepageImage && $homepageImage.uploadName}
						<img class="lazyload u-hide image-constraint pt10" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}" data-src="{$publicFilesDir}/{$homepageImage.uploadName|escape}" alt="{$homepageImage.altText|escape|default:''}" style="margin: 0 auto;max-width: 200px">
						{/if}
						<h3 class="h2 pa20 equalize-line-height text13">{if $issue->getNumber()|regex_replace:"/[a-zA-Z]/":"" eq $issue->getNumber()}{translate key="issue.no"}. {$issue->getNumber()|strip_tags|nl2br|escape}{else}{$issue->getNumber()|strip_tags|nl2br|truncate:5:"."|escape}{/if}
							<span class="pin-right sans-serif text-gray">{$issue->getDatePublished()|date_format:"%B %Y"}</span>
						</h3>
					</a>
					<div class="pa20 text13 suppress-bottom-margin mq480-hide text-gray-light pt10" data-show-more="" data-show-more-length="200" data-hellip-disable-expand="true">
						{if $issue->getLocalizedTitle($currentJournal)}<h3 class="h3 strong text13">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>{/if}
						{if $issue->getLocalizedDescription()}<p>{$issue->getLocalizedDescription()|strip_tags|nl2br|truncate:270:"..."}</p>{/if}
					</div>
				</div>
			</li>
			{/if}
            {/iterate}
		    </ul>
		</section>
    </div>
</div>

<div class="column medium-12 container cleared" data-track-component="volume navigation">    
    <div class="content hide-print mq1200-padded" data-track-component="volume navigation">
        <ul class="cleared pb10 pt10 pl0 pr0 double-border-inner text14 mb0 inline-list">
            {if $prevVolumeId}
            <li>
                <a href="{native_url page="volume" volume=$prevVolumeId}" class="icon icon-left icon-double-chevron-left-10x10-blue pl20" data-track="click" data-track-action="previous link" data-track-label="link">
                    <span>{translate key="navigation.prevVolume"}</span>
                </a>
                {if $prevVolumeId}
                <span class="pl10 pr10"> | </span>
                {else}
                <span class="pl10 pr10"> </span>
                {/if}
            </li>
            {/if}
            <li>
                <a href="{native_url page="volume"}" data-track="click" data-track-action="view all" data-track-label="link">
                    <span>{translate key="navigation.allVolume"}</span>
                </a>
                {if $nextVolumeId}
                <span class="pl10 pr10"> | </span>
                {else}
                <span class="pl10 pr10"> </span>
                {/if}
            </li>
            {if $nextVolumeId}
            <li>
                <a href="{native_url page="volume" volume=$nextVolumeId}" class="icon icon-right icon-double-chevron-right-10x10-blue pr20" data-track="click" data-track-action="next link" data-track-label="link">
                    <span>{translate key="navigation.nextVolume"}</span>
                </a>
            </li>
            {/if}
        </ul>
    </div>
</div>

{if $issues->wasEmpty()}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5">{translate key="current.noCurrentIssueDesc"}</h3>
            <div class="c-empty-state-card__text--description">We are currently preparing our inaugural content. Please check back soon for our upcoming publications, or consider <a href="{url page="author" op="submit"}">submitting your manuscript</a> to be part of our first issue. Visit our <a href="{url page="about" op="submissions"}">Submission Guidelines</a> for more information.</div>
        </div>
    </div>
</div>
    
</div>
{/if}

{include file="common/footer.tpl"}
