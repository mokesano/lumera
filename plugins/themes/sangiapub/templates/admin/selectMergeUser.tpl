{**
 * templates/admin/selectMergeUser.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List users so the site administrator can choose users to merge.
 *
 *}
{strip}
{assign var="pageTitle" value="admin.mergeUsers"}
{assign var="pageDisplayed" value="site"}
{include file="common/header-ROLE.tpl"}
{/strip}
<div id="selectMergeUsers">
    <div class="alert alert-info alert-text">
        <p class="text" style="line-height: 1.5;">{if !empty($oldUserIds)}{translate key="admin.mergeUsers.into.description"}{else}{translate key="admin.mergeUsers.from.description"}{/if}</p>
    </div>
<div id="roles" class="block">
    <h3>{translate key=$roleName}</h3>
    <form method="post" action="{url path=$roleSymbolic oldUserIds=$oldUserIds}">
        {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
        <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">
    
    	<select name="roleSymbolic" class="selectMenu">
    		<option {if $roleSymbolic=='all'}selected="selected" {/if}value="all">{translate key="admin.mergeUsers.allUsers"}</option>
    		<option {if $roleSymbolic=='managers'}selected="selected" {/if}value="managers">{translate key="user.role.managers"}</option>
    		<option {if $roleSymbolic=='editors'}selected="selected" {/if}value="editors">{translate key="user.role.editors"}</option>
    		<option {if $roleSymbolic=='sectionEditors'}selected="selected" {/if}value="sectionEditors">{translate key="user.role.sectionEditors"}</option>
    		<option {if $roleSymbolic=='layoutEditors'}selected="selected" {/if}value="layoutEditors">{translate key="user.role.layoutEditors"}</option>
    		<option {if $roleSymbolic=='copyeditors'}selected="selected" {/if}value="copyeditors">{translate key="user.role.copyeditors"}</option>
    		<option {if $roleSymbolic=='proofreaders'}selected="selected" {/if}value="proofreaders">{translate key="user.role.proofreaders"}</option>
    		<option {if $roleSymbolic=='reviewers'}selected="selected" {/if}value="reviewers">{translate key="user.role.reviewers"}</option>
    		<option {if $roleSymbolic=='authors'}selected="selected" {/if}value="authors">{translate key="user.role.authors"}</option>
    		<option {if $roleSymbolic=='readers'}selected="selected" {/if}value="readers">{translate key="user.role.readers"}</option>
    		<option {if $roleSymbolic=='subscriptionManagers'}selected="selected" {/if}value="subscriptionManagers">{translate key="user.role.subscriptionManagers"}</option>
    	</select>
    	<select name="searchField" size="1" class="selectMenu">
    		{html_options_translate options=$fieldOptions selected=$searchField}
    	</select>
    	<select name="searchMatch" size="1" class="selectMenu">
    		<option value="contains"{if $searchMatch == 'contains'} selected="selected"{/if}>{translate key="form.contains"}</option>
    		<option value="is"{if $searchMatch == 'is'} selected="selected"{/if}>{translate key="form.is"}</option>
    		<option value="startsWith"{if $searchMatch == 'startsWith'} selected="selected"{/if}>{translate key="form.startsWith"}</option>
    	</select>
    	<input type="text" size="10" name="search" class="textField" value="{$search|escape}" />&nbsp;<input type="submit" value="{translate key="common.search"}" class="button" />
    </form>
    
    <div class="c-jump">
        <p class="describe italic u-font-sans">Click the alphabet to chose name of user</p>
        <span class="c-jump-navigation">{foreach from=$alphaList item=letter}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url path=$roleSymbolic oldUserIds=$oldUserIds searchInitial=$letter}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url path=$roleSymbolic oldUserIds=$oldUserIds}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></span>
    </div>
    
    {if not $roleId}
    <div class="pseudoMenu">
        <ul>
        	<li><a href="{url path="managers" oldUserIds=$oldUserIds}">{translate key="user.role.managers"}</a></li>
        	<li><a href="{url path="editors" oldUserIds=$oldUserIds}">{translate key="user.role.editors"}</a></li>
        	<li><a href="{url path="sectionEditors" oldUserIds=$oldUserIds}">{translate key="user.role.sectionEditors"}</a></li>
        	<li><a href="{url path="layoutEditors" oldUserIds=$oldUserIds}">{translate key="user.role.layoutEditors"}</a></li>
        	<li><a href="{url path="copyeditors" oldUserIds=$oldUserIds}">{translate key="user.role.copyeditors"}</a></li>
        	<li><a href="{url path="proofreaders" oldUserIds=$oldUserIds}">{translate key="user.role.proofreaders"}</a></li>
        	<li><a href="{url path="reviewers" oldUserIds=$oldUserIds}">{translate key="user.role.reviewers"}</a></li>
        	<li><a href="{url path="authors" oldUserIds=$oldUserIds}">{translate key="user.role.authors"}</a></li>
        	<li><a href="{url path="readers" oldUserIds=$oldUserIds}">{translate key="user.role.readers"}</a></li>
        	<li><a href="{url path="subscriptionManagers" oldUserIds=$oldUserIds}">{translate key="user.role.subscriptionManagers"}</a></li>
        </ul>
    </div>
    {else}
    <p><a href="{url path="all" oldUserIds=$oldUserIds}" class="action">{translate key="admin.mergeUsers.allUsers"}</a></p>
    {/if}
</div>

<div id="users" class="block">
{if !empty($oldUserIds)}
	{* Selecting target user; do not include checkboxes on LHS *}
	{assign var="numCols" value=4}
{else}
	{* Selecting user(s) to merge; include checkboxes on LHS *}
	{assign var="numCols" value=5}
       <form method="post" action="{url}">
{/if}
<table width="100%" class="listing">
	<tr>
		<td colspan="{$numCols}" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		{if empty($oldUserIds)}
			<td width="5%">&nbsp;</td>
		{/if}
		<td>{translate key="user.username"}</td>
		<td width="29%">{translate key="user.name"}</td>
		<td width="29%">{translate key="user.email"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="{$numCols}" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=users item=user}
	{assign var=userExists value=1}
	<tr valign="top">
		{if empty($oldUserIds)}
			<td><input type="checkbox" name="oldUserIds[]" value="{$user->getId()|escape}" {if $thisUser->getId() == $user->getId()}disabled="disabled" {/if}/></td>
		{/if}
		<td>{$user->getUsername()|escape|wordwrap:15:" ":true}</td>
		<td>{$user->getFullName()|escape}</td>
		<td class="nowrap">
			{assign var=emailString value=$user->getFullName()|concat:" <":$user->getEmail():">"}
			{url|assign:"redirectUrl" path=$roleSymbolic}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$redirectUrl}
			{$user->getEmail()|truncate:15:"..."|escape}&nbsp;{icon name="mail" url=$url}
		</td>
		<td align="right">
			{if !empty($oldUserIds)}
				{if !in_array($user->getId(), $oldUserIds)}
					<a href="#" onclick="confirmAction('{url oldUserIds=$oldUserIds newUserId=$user->getId()}', '{translate|escape:"jsparam" key="admin.mergeUsers.confirm" oldAccountCount=$oldUserIds|@count newUsername=$user->getUsername()}')" class="action">{translate key="admin.mergeUsers.mergeUser"}</a>
				{/if}
			{elseif $thisUser->getId() != $user->getId()}
				<a href="{url oldUserIds=$user->getId()}" class="action">{translate key="admin.mergeUsers.mergeUser"}</a>
			{/if}
		</td>
	</tr>
	{if $users->eof()}
    	<tr>
    		<td colspan="{$numCols}" class="endseparator">&nbsp;</td>
    	</tr>
	{/if}
{/iterate}
</table>

{if empty($oldUserIds)}
	<input type="submit" class="button defaultButton" value="{translate key="admin.mergeUsers"}" />
	</form>
{/if}

{if $users->wasEmpty()}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text search-tips">
            <h2 class="c-empty-state-card__text--title headline-5">{translate key="admin.mergeUsers.noneEnrolled"}</h2>
            <div class="c-empty-state-card__text--description">There are currently no user accounts available for merging. Users must be registered in the system before they can be selected for account consolidation or merger processes.</div>
        </div>
    </div>
</div>
{else}
<div id="colspan" class="colspan u-mb-0" >	    
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View {page_info iterator=$users}</div>
    </section>
    {if $users->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="users" name="users" iterator=$users searchInitial=$searchInitial searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth roleSymbolic=$roleSymbolic oldUserIds=$oldUserIds}
       </div>
    </section>
    {/if}
    <div class="u-hide">
    	<tr class="u-hide">
    		<td colspan="{math equation="floor(numCols / 2)" numCols=$numCols}" align="left">{page_info iterator=$users}</td>
    		<td colspan="{math equation="ceil(numCols / 2)" numCols=$numCols}" align="right">{page_links anchor="users" name="users" iterator=$users searchInitial=$searchInitial searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth roleSymbolic=$roleSymbolic oldUserIds=$oldUserIds}</td>
    	</tr>
    </div>
</div>
{/if}

</div>
</div>
{include file="common/footer.tpl"}

