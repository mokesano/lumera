{**
 * templates/sectionEditor/submissionsArchives.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show section editor's submission archive.
 *
 *}
<div id="submissions" class="archives">
    <table width="100%" class="listing">
    	<tr><td colspan="6" class="headseparator">&nbsp;</td></tr>
    	<tr class="heading" valign="bottom">
    		<td width="5%">{sort_search key="common.id" sort="id"}</td>
    		<td width="10%"><span class="disabled"></span>{sort_search key="submissions.submitted" sort="submitDate"}</td>
    		<td width="5%">{sort_search key="submissions.sec" sort="section"}</td>
    		<td width="25%">{sort_search key="article.authors" sort="authors"}</td>
    		<td width="35%">{sort_search key="article.title" sort="title"}</td>
    		<td width="20%" align="right">{sort_search key="common.status" sort="status"}</td>
    	</tr>
    
        {iterate from=submissions item=submission}
        	{assign var="articleId" value=$submission->getId()}
        	<tr valign="top">
        		<td>{$submission->getId()}</td>
        		<td>{$submission->getDateSubmitted()|date_format:$dateFormatShort}</td>
        		<td>{$submission->getSectionAbbrev()|escape}</td>
        		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
        		<td><a href="{url op="submissionEditing" path=$articleId}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."}</a></td>
        		<td align="right">
        			{assign var="status" value=$submission->getStatus()}
        			{if $status == STATUS_ARCHIVED}
        				{translate key="submissions.archived"}
        			{elseif $status == STATUS_PUBLISHED}
        				{print_issue_id articleId="$articleId"}
        			{elseif $status == STATUS_DECLINED}
        				{translate key="submissions.declined"}
        			{/if}
        		</td>
        	</tr>
        	{if $submissions->eof()}
            	<tr>
            		<td colspan="6" class="end separator">&nbsp;</td>
            	</tr>
        	{/if}
        {/iterate}
        {if $submissions->wasEmpty()}
        	<tr valign="bottom">
        		<td colspan="6" class="nodata">{translate key="submissions.noSubmissions"}</td>
        	</tr>
        	<tr valign="none">
        		<td colspan="6" class="endseparator">&nbsp;</td>
        	</tr>
        {else}
        	<tr valign="bottom" class="u-hide">
        		<td colspan="3" align="left">{page_info iterator=$submissions}</td>
        		<td colspan="3" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth dateSearchField=$dateSearchField section=$section sort=$sort sortDirection=$sortDirection}</td>
        	</tr>
        {/if}
    </table>
</div>

<p class="archives-fastTracked"> Items indicate is Fast Tracked</p>

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
