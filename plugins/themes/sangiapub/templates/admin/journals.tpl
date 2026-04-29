{**
 * templates/admin/journals.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of journals in site administration.
 *
 *}
{strip}
{assign var="pageTitle" value="journal.journals"}
{assign var="pageDisplayed" value="site"}
{include file="common/header.tpl"}
{/strip}
<script type="text/javascript">
{literal}
$(document).ready(function() { setupTableDND("#adminJournals", "moveJournal"); });
{/literal}
</script>

<div id="journals">
    <table width="100%" class="listing" id="adminJournals">
    	<thead>
        	<tr>
        		<td colspan="4" class="headseparator">&nbsp;</td>
        	</tr>
        	<tr valign="center" class="heading">
        		<td width="60%">{translate key="manager.setup.journalTitle"}</td>
        		<td width="20%">{translate key="journal.path"}</td>
        		<td width="5%">{translate key="common.order"}</td>
        		<td width="15%" align="right">{translate key="common.action"}</td>
        	</tr>
        	<tr>
        		<td colspan="4" class="headseparator">&nbsp;</td>
        	</tr>
    	</thead>
    	<tbody>
        	{iterate from=journals item=journal}
        	<tr valign="top" id="journal-{$journal->getId()}" class="data">
        		<td><a class="action" href="{url journal=$journal->getPath() page="manager"}">{$journal->getLocalizedTitle()|escape}</a></td>
        		<td class="drag">{$journal->getPath()|escape}</td>
        		<td><a href="{url op="moveJournal" d=u id=$journal->getId()}">&uarr;</a> <a href="{url op="moveJournal" d=d id=$journal->getId()}">&darr;</a></td>
        		<td align="right"><a href="{url op="editJournal" path=$journal->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a class="action" href="{url op="deleteJournal" path=$journal->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="admin.journals.confirmDelete"}')">{translate key="common.delete"}</a></td>
        	</tr>
        	{/iterate}
    	</tbody>
    </table>
</div>

<a href="{url op="createJournal"}" class="button u-hide">{translate key="admin.journals.create"}</a>
<input type="button" value="{translate key="admin.journals.create"}" class="button" onclick="document.location.href='{url op="createJournal"}'">

{if $journals->wasEmpty()}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text search-tips">
            <h2 class="c-empty-state-card__text--title headline-5">{translate key="admin.journals.noneCreated"}</h2>
            <div class="c-empty-state-card__text--description">The system currently contains no journal publications. Use the <a href="{url op="createJournal"}">Create Journal</a> function to establish your first journal and begin setting up editorial workflows, user roles, and publication processes. You can configure multiple journals within this Editorial Management System installation to manage different academic publications from a single platform.</div>
        </div>
    </div>
</div>
{else}
<div id="colspan" class="colspan u-mb-0" >
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View {page_info iterator=$journals}</div>
    </section>
    {if $journals->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="journals" name="journals" iterator=$journals}
       </div>
    </section>
    {/if}
	<div class="u-hide">
		<tr>
			<td colspan="4" class="endseparator">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" align="left">{page_info iterator=$journals}</td>
			<td colspan="2" align="right">{page_links anchor="journals" name="journals" iterator=$journals}</td>
		</tr>
	</div>
</div>
{/if}
	

{include file="common/footer.tpl"}

