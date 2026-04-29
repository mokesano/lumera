{**
 * templates/editor/submissionsInReview.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show editor's submissions in review.
 *}
{if !$submissions->wasEmpty()}
<div id="submissions" class="review">
    <table width="100%" class="listing">
    	<tr>
    		<td colspan="8" class="headseparator">&nbsp;</td>
    	</tr>
    	<tr class="heading">
    		<td width="1%"></td>
    		<td width="5%">{sort_search key="common.id" sort="id"}</td>
    		<td width="5%"><span class="disabled">{translate key="submission.date.yyyymmdd"}</span><br />{sort_search key="submissions.submitted" sort="submitDate"}</td>
    		<td width="5%">{sort_search key="submissions.sec" sort="section"}</td>
    		<td width="15%">{sort_search key="article.authors" sort="authors"}</td>
    		<td width="35%">{sort_search key="article.title" sort="title"}</td>
    		<td width="20%">
    			{translate key="submission.peerReview"}
    			<table width="100%" class="nested">
    				<tr valign="center">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.ask"}</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.due"}</td>
    					<td width="34%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.done"}</td>
    				</tr>
    			</table>
    		</td>
    		<td width="5%">{translate key="submissions.ruling"}</td>
    		<td width="10%">{translate key="article.sectionEditor"}</td>
    	</tr>
    	{if $submissions->eof()}
        	<tr valign="bottom">
        		<td colspan="8" class="headseparator">&nbsp;</td>
        	</tr>
    	{/if}
    	
    	{iterate from=submissions item=submission}
    	{assign var="highlightClass" value=$submission->getHighlightClass()}
    	{assign var="fastTracked" value=$submission->getFastTracked()}
    	<tr valign="review-top"{if $highlightClass || $fastTracked} class="{$highlightClass|escape}{if $fastTracked} fastTracked{/if}"{/if}>
    		{if !isset($highlightClass)}
    		<td></td>
    		{/if}
    		<td>{$submission->getId()}</td>
    		<td>{$submission->getDateSubmitted()|date_format:$dateFormatShort}</td>
    		<td>{$submission->getSectionAbbrev()|escape}</td>
    		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
    		<td><a href="{url op="submissionReview" path=$submission->getId()}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:70:"..."}</a></td>
    		<td>
    			<table width="100%">
    			{foreach from=$submission->getReviewAssignments() item=reviewAssignments}
    				{foreach from=$reviewAssignments item=assignment name=assignmentList}
    					{if not $assignment->getCancelled() and not $assignment->getDeclined()}
    					<tr valign="time-review-top">
    						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateNotified()}{$assignment->getDateNotified()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
    						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateCompleted() || !$assignment->getDateConfirmed()}&mdash;{else}{$assignment->getWeeksDue()|default:"&mdash;"}{/if}</td>
    						<td width="34%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateCompleted()}{$assignment->getDateCompleted()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
    					</tr>
    					{/if}
    				{foreachelse}
    				<tr valign="time-review-top">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="34%" style="padding: 0 0 0 0; font-size: 1.0em">&mdash;</td>
    				</tr>
    				{/foreach}
    			{foreachelse}
    				<tr valign="time-review-top">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="34%" style="padding: 0 0 0 0; font-size: 1.0em">&mdash;</td>
    				</tr>
    			{/foreach}
    			</table>
    		</td>
    		<td>
    			{foreach from=$submission->getDecisions() item=decisions}
    				{foreach from=$decisions item=decision name=decisionList}
    					{if $smarty.foreach.decisionList.last}
    							{$decision.dateDecided|date_format:$dateFormatTrunc}				
    					{/if}
    				{foreachelse}
    					&mdash;
    				{/foreach}
    			{foreachelse}
    				&mdash;
    			{/foreach}
    		</td>
    		<td align="center">
    			{assign var="editAssignments" value=$submission->getEditAssignments()}
    			{foreach from=$editAssignments item=editAssignment}{$editAssignment->getEditorInitials()|escape} {/foreach}
    		</td>
    	</tr>
    	{if $submissions->eof()}
        	<tr valign="bottom">
        		<td colspan="8" class="separator">&nbsp;</td>
        	</tr>
    	{/if}
    {/iterate}
    {if $submissions->wasEmpty()}
    	<tr valign="top" class="u-hide">
    		<td colspan="8" class="nodata">{translate key="submissions.noSubmissions"}</td>
    	</tr>
    	<tr valign="bottom">
    		<td colspan="8" class="separator">&nbsp;</td>
    	</tr>
    {else}
    	<tr class="u-hide" valign="bottom">
    		<td colspan="5" align="left">{page_info iterator=$submissions}</td>
    		<td colspan="3" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth dateSearchField=$dateSearchField section=$section sort=$sort sortDirection=$sortDirection}</td>
    	</tr>
    {/if}
    </table>
</div>

<p class="fastTracked">Highlighted items indicate action is Fast Tracked</p>

<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination">{page_info iterator=$submissions}</div>
    </section>
    {if $submissions->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth dateSearchField=$dateSearchField section=$section sort=$sort sortDirection=$sortDirection}
       </div>
    </section>
    {/if}
</div>
{else}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5">{translate key="submissions.noSubmissions"} are currently in review</h3>
            <div class="c-empty-state-card__text--description">All submissions have either completed the review process or are in other editorial stages. New submissions will appear here once they enter the peer review phase and are assigned to reviewers.</div>
        </div>
    </div>
</div>
{/if}
