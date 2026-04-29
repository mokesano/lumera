{**
 * templates/issue/archive.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue Archive.
 *
 *}
{strip}
{assign var="pageTitleTranslated" value=$siteTitle}
{include file="common/header-issues.tpl"}
{/strip}

{if $currentJournal && $currentJournal->getSetting('onlineIssn')}
	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal && $currentJournal->getSetting('printIssn')}
	{assign var=issn value=$currentJournal->getSetting('printIssn')}
{/if}
{if $printIssn} {else if $onlineIssn}
<div id="issn">ISSN: {if $currentJournal->getSetting('printIssn')}<span class="pissn">{$currentJournal->getSetting('printIssn')} (Print)</span>{/if}
	<span class="eissn">{if $currentJournal->getSetting('onlineIssn')}{$currentJournal->getSetting('onlineIssn')} (Online){else} On process (Online){/if}</span>
</div>
{/if}

{if $currentJournal->getLocalizedSetting('history') != ''}
<div id="journal-history-link">
	<span class="icon-container info">
		This journal was previously published under other titles
        <a href="{url page="about" op="history" anchor=""}">(view Journal {translate key="about.history"})
        </a>
    </span>
</div>
{/if}

<h2 id="title">All Volumes &amp; Issues</h2>

<ul class="tabs">
	<li class="active">
		<span>Volumes</span>
	</li>
</ul>

<div class="volumes tab-content">
    {if !$issues->wasEmpty()}
	<div id="onlinefirst-item">
        <div class="expander expander-open expander-open">
            <button class="expander-title" aria-expanded="true">
                <div>
                    <h2>Online First</h2>
                </div>
            </button>
            <div class="expander-content">
                <div class="expander-content-inner">
                    <div class="formatted">
                        <ul class="issues-list">
                            <li><a href="{url page="issue" op="view" path="onlineFirst"}">View articles not assigned to an issue</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {else}
	<div id="onlinefirst-item">
        <div class="expander expander-open expander-open">
            <button class="expander-title" aria-expanded="true">
                <div>
                    <h2>Not Assigned an Issue</h2>
                </div>
            </button>
            <div class="expander-content">
                <div class="expander-content-inner">
                    <div class="formatted">
                        <ul class="issues-list">
                            <li>{translate key="current.noCurrentIssueDesc"}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    {/if}

	<div id="volumes-list" >
	{iterate from=issues item=issue}
		{if $issue->getYear() != $lastYear}
			{if !$notFirstYear}
				{assign var=notFirstYear value=1}
			{else}
			</div>
		</div>
	</div>
</div>
</div>
			{/if}
			<div class="volume-item">
				<div id="volume{$issue->getVolume()}" class="expander expander-open">
					<button class="expander-title" aria-expanded="true">
						<div><h2 title="volume {$issue->getVolume()} {$issue->getYear()|escape}">{translate key="issue.volume"} {$issue->getVolume()|escape} <span>{$issue->getDatePublished('issue.firstYear')|date_format:"%B %Y"} - {$issue->getDatePublished('issue.lastYear')|date_format:"%B %Y"}</span></h2></div>
					</button>
		            <div class="expander-content">
		            	<div class="expander-content-inner">
	    	                <div class="formatted">
	    	                	<ul class="issues-list">
			{assign var=lastYear value=$issue->getYear()}
		{/if}

			<li id="issue-{$issue->getId()}" class="issue-item" title="volume {$issue->getVolume()}, issue {$issue->getNumber()|escape}, {$issue->getYear()|escape}">
			{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
				<!-- <a class="issueCoverImage" href="{url op="view" path=$issue->getBestIssueId($currentJournal)}"><img src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if}/></a> -->
				<a href="{url op="view" path=$issue->getBestIssueId($currentJournal)}">{$issue->getDatePublished()|date_format:"%B %Y"}, {translate key="issue.number"} {$issue->getNumber()|escape}, Articles {$issue->getNumArticles()|escape}</a>
				{if $issue->getLocalizedTitle($currentJournal)}<p class="title-issue">{$issue->getLocalizedTitle($currentJournal)|escape}</p>{/if}
				{if $issue->getLocalizedDescription()}<p class="description-issue">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p>{/if}
			{else}
				<a href="{url op="view" path=$issue->getBestIssueId($currentJournal)}">{$issue->getDatePublished()|date_format:"%B %Y"}, {translate key="issue.number"} {$issue->getNumber()|escape}, Articles {$issue->getNumArticles()|escape}</a>
				{if $issue->getLocalizedTitle($currentJournal)}<p class="title-issue">{$issue->getLocalizedTitle($currentJournal)|escape}</p>{/if}
				{if $issue->getLocalizedDescription()}<p class="description-issue">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</p>{/if}
			{/if}
			</li>

	{/iterate}
</ul>

{if $notFirstYear}</div>{/if}</div></div>

<!--<div id="look-inside-interrupt" class="look-inside-interrupt">
	<h3>Continue reading...</h3>
	<div class="col-1">
		<p>To view the rest of this content please follow the download PDF link above.</p>
	</div>
</div>-->

{if !$issues->wasEmpty()}
<table id="colspan-anchor" width="100%" class="listing page_links">
	<tbody>
		<tr valign="bottom" class="page-listing paging">
			<td class="anchor" width="50%" {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$issues}</td>
			<td class="anchor" width="50%" colspan="2" style="text-align: right;">{page_links anchor="issues" name="issues" iterator=$issues}</td>
		</tr>
	</tbody>
</table>
{else}
<div><div><div>
{/if}

			</div>
		</div>
	</div>
</div>

{include file="common/footer.tpl"}

