{**
 * templates/reviewer/completed.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show reviewer's submission archive.
 *
 *}
{if !$submissions->wasEmpty()}
<div id="submissions" class="block">
    <table class="listing" width="100%">
    	<tr><td colspan="6" class="headseparator">&nbsp;</td></tr>
    	<tr class="heading" valign="bottom">
    		<td width="5%">{sort_heading key="common.id" sort="id"}</td>
    		<td width="10%"><span class="disabled">{translate key="submission.date.mmdd"}</span><br />{sort_heading key="common.assigned" sort="assignDate"}</td>
    		<td width="5%">{sort_heading key="submissions.sec" sort="section"}</td>
    		<td width="40%">{sort_heading key="article.title" sort="title"}</td>
    		<td width="15%">{sort_heading key="submission.review" sort="review"}</td>
    		<td width="15%">{sort_heading key="submission.editorDecision" sort="decision"}</td>
    	</tr>
        {iterate from=submissions item=submission}
        	{assign var="articleId" value=$submission->getId()}
        	{assign var="reviewId" value=$submission->getReviewId()}
        	<tr valign="top">
        		<td>{$articleId|escape}</td>
        		<td>{$submission->getDateNotified()|date_format:$dateFormatShort}</td>
        		<td>{$submission->getSectionAbbrev()|escape}</td>
        		<td>{if !$submission->getDeclined()}<a href="{url op="submission" path=$reviewId}" class="action">{/if}{$submission->getLocalizedTitle()|strip_unsafe_html|nl2br}{if !$submission->getDeclined()}</a>{/if}</td>
        		<td>
        			{if $submission->getDeclined()}
        				{translate key="sectionEditor.regrets"}
        			{else}
        				{assign var=recommendation value=$submission->getRecommendation()}
        				{if $recommendation === '' || $recommendation === null}
        					&mdash;
        				{else}
        					{translate key=$reviewerRecommendationOptions.$recommendation}
        				{/if}
        			{/if}
        		</td>
        		<td>
        			{if $submission->getCancelled() || $submission->getDeclined()}
        				&mdash;
        			{else}
        			{* Display the most recent editor decision *}
        			{assign var=round value=$submission->getRound()}
        			{assign var=decisions value=$submission->getDecisions($round)}
        			{foreach from=$decisions item=decision name=lastDecisionFinder}
        				{if $smarty.foreach.lastDecisionFinder.last and $decision.decision == SUBMISSION_EDITOR_DECISION_ACCEPT}
        					{translate key="editor.article.decision.accept"}
        				{elseif $smarty.foreach.lastDecisionFinder.last and $decision.decision == SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS}
        					{translate key="editor.article.decision.pendingRevisions"}
        				{elseif $smarty.foreach.lastDecisionFinder.last and $decision.decision == SUBMISSION_EDITOR_DECISION_RESUBMIT}
        					{translate key="editor.article.decision.resubmit"}
        				{elseif $smarty.foreach.lastDecisionFinder.last and $decision.decision == SUBMISSION_EDITOR_DECISION_DECLINE}
        					{translate key="editor.article.decision.decline"}
        				{/if}
        			{foreachelse}
        				&mdash;
        			{/foreach}
        			{/if}
        		</td>
        	</tr>
        	{if $submissions->eof()}
            	<tr>
            		<td colspan="6" class="endseparator">&nbsp;</td>
            	</tr>
        	{/if}
        {/iterate}
    </table>
</div>

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
{else}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5">{translate key="submissions.noSubmissions"}</h3>
            <div class="c-empty-state-card__text--description">All current submissions have been assigned to appropriate editorial team members, or there are no active submissions in the system at this time. New submissions will appear here for assignment once received.</div>
        </div>
    </div>
</div>
{/if}
