{**
 * templates/sectionEditor/submissionsInReview.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show section editor's submissions in review.
 *
 *}
<div id="submissions" class="review">
    <table width="100%" class="listing">
    	<tr><td colspan="7" class="headseparator">&nbsp;</td></tr>
    	<tr class="heading" valign="bottom">
    		<td width="1%"></td>
    		<td width="5%">{sort_search key="common.id" sort="id"}</td>
    		<td width="15%"><span class="disabled">{translate key="submission.date.yyyymmdd"}</span><br />{sort_search key="submissions.submit" sort="submitDate"}</td>
    		<td width="5%">{sort_search key="submissions.sec" sort="section"}</td>
    		<td width="20%">{sort_search key="article.authors" sort="authors"}</td>
    		<td width="30%">{sort_search key="article.title" sort="title"}</td>
    		<td width="20%">
    			{translate key="submission.peerReview"}
    			<table width="100%" class="nested">
    				<tr valign="top">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.ask"}</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.due"}</td>
    					<td width="34%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.done"}</td>
    				</tr>
    			</table>
    		</td>
    		<td width="5%">{translate key="submissions.ruling"}</td>
    	</tr>
    	<tr valign="none"><td colspan="7" class="headseparator">&nbsp;</td></tr>
    
        {iterate from=submissions item=submission}
        	{assign var="articleId" value=$submission->getId()}
        	{assign var="highlightClass" value=$submission->getHighlightClass()}
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
        					<tr valign="review-top">
        						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateNotified()}{$assignment->getDateNotified()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
        						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateCompleted() || !$assignment->getDateConfirmed()}&mdash;{else}{$assignment->getWeeksDue()|default:"&mdash;"}{/if}</td>
        						<td width="34%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateCompleted()}{$assignment->getDateCompleted()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
        					</tr>
        					{/if}
        				{foreachelse}
        					<tr valign="review-top">
        						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
        						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
        						<td width="34%" style="padding: 0 0 0 0; font-size:1.0em">&mdash;</td>
        					</tr>
        				{/foreach}
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
        			{/foreach}			
        		</td>
        	</tr>
        	{if $submissions->eof()}
            	<tr valign="bottom">
            		<td colspan="7" class="end separator">&nbsp;</td>
            	</tr>
        	{/if}
        {/iterate}
        {if $submissions->wasEmpty()}
        	<tr>
        		<td colspan="7" class="nodata">{translate key="submissions.noSubmissions"}</td>
        	</tr>
        	<tr valign="bottom">
        		<td colspan="7" class="endseparator">&nbsp;</td>
        	</tr>
        {else}
        	<tr class="u-hide" valign="bottom">
        		<td colspan="5" align="left">{page_info iterator=$submissions}</td>
        		<td colspan="2" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth dateSearchField=$dateSearchField section=$section sort=$sort sortDirection=$sortDirection}</td>
        	</tr>
        {/if}
    </table>
</div>

<p class="fastTracked"> Items indicate is Fast Tracked</p>

{if !$submissions->wasEmpty()}
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
{/if}
