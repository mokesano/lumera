{**
 * templates/author/completed.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the details of completed submissions.
 *
 *}
<div id="submissions">
    <table class="listing" width="100%">
    	<tr><td class="headseparator" colspan="{if $statViews}7{else}6{/if}">&nbsp;</td></tr>
    	<tr valign="bottom" class="heading">
    		<td width="5%">{sort_heading key="common.id" sort="id"}</td>
    		<td width="10%"><span class="disabled">{translate key="submission.date.mmdd"}</span><br />{sort_heading key="submissions.submit" sort="submitDate"}</td>
    		<td width="5%">{sort_heading key="submissions.sec" sort="section"}</td>
    		<td width="15%">{sort_heading key="article.authors" sort="authors"}</td>
    		<td width="45%">{sort_heading key="article.title" sort="title"}</td>
    		{if $statViews}<td width="5%">{sort_heading key="submission.views" sort="views"}</td>{/if}
    		<td width="20%" align="center">{sort_heading key="common.status" sort="status"}</td>
    	</tr>
    	
        {iterate from=submissions item=submission}
        	{assign var="articleId" value=$submission->getId()}
        	<tr valign="top">
        		<td>{$articleId|escape}</td>
        		<td>{$submission->getDateSubmitted()|date_format:$dateFormatShort}</td>
        		<td>{$submission->getSectionAbbrev()|escape}</td>
        		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
        		<td><a href="{url op="submission" path=$articleId}" class="action">{$submission->getLocalizedTitle()|strip_unsafe_html|nl2br}</a></td>
        		{assign var="status" value=$submission->getSubmissionStatus()}
        		{if $statViews}
        			<td>
        				{if $status==STATUS_PUBLISHED}
        					{assign var=viewCount value=0}
        					{foreach from=$submission->getGalleys() item=galley}
        						{assign var=thisCount value=$galley->getViews()}
        						{assign var=viewCount value=$viewCount+$thisCount}
        					{/foreach}
        					{$viewCount|escape}
        				{else}
        					&mdash;
        				{/if}
        			</td>
        		{/if}
        		<td align="right">
        			{if $status==STATUS_ARCHIVED}{translate key="submissions.archived"}
        			{elseif $status==STATUS_PUBLISHED}{print_issue_id articleId="$articleId"}
        			{elseif $status==STATUS_DECLINED}{translate key="submissions.declined"}
        			{/if}
        		</td>
        	</tr>
        {/iterate}
        
        {if $submissions->wasEmpty()}
        	<tr class="u-hide">
        		<td colspan="{if $statViews}7{else}6{/if}" class="nodata">{translate key="submissions.noSubmissions"}</td>
        	</tr>
        	<tr>
        		<td colspan="{if $statViews}7{else}6{/if}" class="endseparator">&nbsp;</td>
        	</tr>
        {else}
        	<tr class="functions-bar functions-bar-bottom u-hide">
        		<td colspan="{if $statViews}5{else}4{/if}" align="left">{page_info iterator=$submissions}</td>
        		<td colspan="2" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions sort=$sort sortDirection=$sortDirection}</td>
        	</tr>
        {/if}
    </table>
</div>

{if $submissions->wasEmpty()}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5">{translate key="submissions.noSubmissions"}</h3>
            <div class="c-empty-state-card__text--description">All current submissions or new submissions will appear here.</div>
        </div>
    </div>
</div>

{else}

<p class="archives-fastTracked"> Items indicate is Fast Tracked</p>

<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination">{page_info iterator=$submissions}</div>
    </section>
    {if $submissions->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="submissions" name="submissions" iterator=$submissions sort=$sort sortDirection=$sortDirection}
       </div>
    </section>
    {/if}
</div>
{/if}
