{**
 * templates/editor/submissionsArchives.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show listing of submission archives.
 *
 *}
{if !$submissions->wasEmpty()}
<div id="submissions" class="archives">
    <table width="100%" class="listing">
    	<tr>
    		<td colspan="6" class="headseparator">&nbsp;</td>
    	</tr>
    	<tr class="heading" valign="bottom">
    		<td width="5%">{sort_search key="common.id" sort="id"}</td>
    		<td width="10%"><span class="disabled"></span>{sort_search key="submissions.submitted" sort="submitDate"}</td>
    		<td width="5%">{sort_search key="submissions.sec" sort="section"}</td>
    		<td width="20%">{sort_search key="article.authors" sort="authors"}</td>
    		<td width="40%">{sort_search key="article.title" sort="title"}</td>
    		<td width="20%" align="right">{sort_search key="common.status" sort="status"}</td>
    	</tr>
    	
    	{iterate from=submissions item=submission}
    	{assign var="articleId" value=$submission->getId()}
    	<tr valign="top"{if $submission->getFastTracked()} class="fastTracked"{/if}>
    		<td>{$articleId|escape}</td>
    		<td>{$submission->getDateSubmitted()|date_format:$dateFormatShort}</td>
    		<td>{$submission->getSectionAbbrev()|escape}</td>
    		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
    		<td><a href="{url op="submissionEditing" path=$articleId}" class="action">{$submission->getLocalizedTitle()|strip_unsafe_html|nl2br}</a></td>
    		<td align="right">
    			{assign var="status" value=$submission->getStatus()}
    			{if $status == STATUS_ARCHIVED}
    				{translate key="submissions.archived"}&nbsp;&nbsp;<a href="{url op="deleteSubmission" path=$articleId}" onclick="return confirm('{translate|escape:"jsparam" key="editor.submissionArchive.confirmDelete"}')" class="action">{translate key="common.delete"}</a>
    			{elseif $status == STATUS_PUBLISHED}
    				{print_issue_id articleId="$articleId"}	
    			{elseif $status == STATUS_DECLINED}
    				{translate key="submissions.declined"}&nbsp;&nbsp;<a href="{url op="deleteSubmission" path=$articleId}" onclick="return confirm('{translate|escape:"jsparam" key="editor.submissionArchive.confirmDelete"}')" class="action">{translate key="common.delete"}</a>
    			{/if}
    		</td>
    	</tr>
        {if $submissions->eof()}
        	<tr>
        		<td colspan="6" class="separator">&nbsp;</td>
        	</tr>
        {/if}
        {/iterate}

        {if $submissions->wasEmpty()}
        	<tr valign="top">
        		<td colspan="6" class="nodata">{translate key="submissions.noSubmissions"}</td>
        	</tr>
        	<tr valign="bottom">
        		<td colspan="6" class="separator">&nbsp;</td>
        	</tr>
        {else}
        	<tr class="u-hide" valign="bottom">
        		<td colspan="4" align="left">{page_info iterator=$submissions}</td>
        		<td colspan="2" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth dateSearchField=$dateSearchField section=$section sort=$sort sortDirection=$sortDirection}</td>
        	</tr>
        {/if}
    </table>
</div>

<p class="archives-fastTracked">
    Highlighted items indicate is Fast Tracked
</p>

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
            <h3 class="c-empty-state-card__text--title headline-5">{translate key="submissions.noSubmissions"} editorial archives are available</h3>
            <div class="c-empty-state-card__text--description">There are currently no completed editorial decisions or archived submissions to display. Published articles and completed editorial processes will appear here once they have moved through the full editorial workflow.</div>
        </div>
    </div>
</div>
{/if}
