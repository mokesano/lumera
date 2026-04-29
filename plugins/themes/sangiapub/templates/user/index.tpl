{**
 * templates/user/index.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User index.
 *
 *}
{strip}
{assign var="pageTitle" value="user.userHome"}
{include file="common/header-parts/header-admin.tpl"}
{/strip}

{if $isSiteAdmin}
{assign var="hasRole" value=1}
<div class="account-container__section">
    <h1 class="u-h2">Administrator</h1>
    <ul class="account-container__grid">
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Manage Editorial Systems" href="{url journal="index" page=$isSiteAdmin->getRolePath()}" target="_blank">{translate key=$isSiteAdmin->getRoleName()}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">Manage system-wide settings, add journal, maintain functionality and security.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg fill="currentColor" stroke="currentColor" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" stroke-width=".7"><path d="M14.68,14.81a6.76,6.76,0,1,1,6.76-6.75A6.77,6.77,0,0,1,14.68,14.81Zm0-11.51a4.76,4.76,0,1,0,4.76,4.76A4.76,4.76,0,0,0,14.68,3.3Z"></path><path d="M16.42,31.68A2.14,2.14,0,0,1,15.8,30H4V24.22a14.81,14.81,0,0,1,11.09-4.68l.72,0a2.2,2.2,0,0,1,.62-1.85l.12-.11c-.47,0-1-.06-1.46-.06A16.47,16.47,0,0,0,2.2,23.26a1,1,0,0,0-.2.6V30a2,2,0,0,0,2,2H16.7Z"></path><path d="M26.87,16.29a.37.37,0,0,1,.15,0,.42.42,0,0,0-.15,0Z"></path><path d="M33.68,23.32l-2-.61a7.21,7.21,0,0,0-.58-1.41l1-1.86A.38.38,0,0,0,32,19l-1.45-1.45a.36.36,0,0,0-.44-.07l-1.84,1a7.15,7.15,0,0,0-1.43-.61l-.61-2a.36.36,0,0,0-.36-.24H23.82a.36.36,0,0,0-.35.26l-.61,2a7,7,0,0,0-1.44.6l-1.82-1a.35.35,0,0,0-.43.07L17.69,19a.38.38,0,0,0-.06.44l1,1.82A6.77,6.77,0,0,0,18,22.69l-2,.6a.36.36,0,0,0-.26.35v2.05A.35.35,0,0,0,16,26l2,.61a7,7,0,0,0,.6,1.41l-1,1.91a.36.36,0,0,0,.06.43l1.45,1.45a.38.38,0,0,0,.44.07l1.87-1a7.09,7.09,0,0,0,1.4.57l.6,2a.38.38,0,0,0,.35.26h2.05a.37.37,0,0,0,.35-.26l.61-2.05a6.92,6.92,0,0,0,1.38-.57l1.89,1a.36.36,0,0,0,.43-.07L32,30.4A.35.35,0,0,0,32,30l-1-1.88a7,7,0,0,0,.58-1.39l2-.61a.36.36,0,0,0,.26-.35V23.67A.36.36,0,0,0,33.68,23.32ZM24.85,28a3.34,3.34,0,1,1,3.33-3.33A3.34,3.34,0,0,1,24.85,28Z"></path></svg>
                </div>
            </div>
        </article></li>
        {call_hook name="Templates::User::Index::Site"}
        
        {foreach from=$userJournals item=journal}
        {assign var="hasRole" value=1}
        {assign var="journalId" value=$journal->getId()}
        {assign var="journalPath" value=$journal->getPath()}
        {if $currentJournal}{if $isValid.JournalManager.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Manage Editorial Systems" href="{url journal=$journalPath page="manager"}" target="_blank">{translate key="user.role.manager"}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">Manage journal settings, journal policies configurations, masthead, guide for roles, and maintain functionality.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.53,9.23l-2.87-5-.5.29a2.87,2.87,0,0,1-4.3-2.48V1.5H9.14v.58a2.87,2.87,0,0,1-4.3,2.48l-.5-.29-2.87,5L2,9.52a2.86,2.86,0,0,1,0,5l-.51.29,2.87,5,.5-.29a2.87,2.87,0,0,1,4.3,2.48v.58h5.72v-.58a2.87,2.87,0,0,1,4.3-2.48l.5.29,2.87-5L22,14.48a2.86,2.86,0,0,1,0-5Z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /><circle cx="12" cy="10.09" r="2.86" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /><path d="M7.23,19.23v-1.5a4.77,4.77,0,1,1,9.54,0v1.5" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.SubscriptionManager.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Manage Editorial Systems" href="{url journal=$journalPath page="subscriptionManager"}" target="_blank">{translate key="user.role.subscriptionManager"}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">Manage subscription settings, and journal subscribe configurations.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12.02" cy="7.24" r="5.74" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /><path d="M2.46,23.5V21.59a9.55,9.55,0,0,1,7-9.21" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /><path d="M16.8,14.89l-1,1.91H9.15L7.24,18.72l1.91,1.91h6.7l1,1.91h2.87a2.86,2.86,0,0,0,2.87-2.87V17.76a2.87,2.87,0,0,0-2.87-2.87Z" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /><line x1="12.02" y1="18.72" x2="12.02" y2="20.63" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /><line x1="19.67" y1="17.76" x2="19.67" y2="19.67" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91" /></svg>
                </div>
            </div>
        </article></li>
        {/if}{/if}
        {/foreach}
        
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Manage Editorial Systems" href="#" target="_blank">Manage your account</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">Change your password, update your communications settings or get help with your Scholar Wizdam account.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1c6.075 0 11 4.925 11 11s-4.925 11-11 11S1 18.075 1 12 5.925 1 12 1Zm0 16c-1.806 0-3.52.994-4.664 2.698A8.947 8.947 0 0 0 12 21a8.958 8.958 0 0 0 4.664-1.301C15.52 17.994 13.806 17 12 17Zm0-14a9 9 0 0 0-6.25 15.476C7.253 16.304 9.54 15 12 15s4.747 1.304 6.25 3.475A9 9 0 0 0 12 3Zm0 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"></path></svg>
                </div>
            </div>
        </article></li>
        
    </ul>
</div>
{/if}

<section class="old-dashboard account-container__section{if $currentJournal} u-js-hide{/if}">
<div id="myJournals" class="block">
{if !$currentJournal}<h3 class="u-h2">{translate key="user.myJournals"}</h3>{/if}

{foreach from=$userJournals item=journal}
	<div id="{$journal->getPath()|escape}" class="block">
	{assign var="hasRole" value=1}
	{if !$currentJournal}<h4 class="u-h2"><a href="{url journal=$journal->getPath() page="user"}">{$journal->getLocalizedTitle()|escape}</a></h4>
	{else}<h3 class="u-h2"><a href="{url page="index"}">{$journal->getLocalizedTitle()|escape}</a></h3>{/if}
	
	{assign var="journalId" value=$journal->getId()}
	{assign var="journalPath" value=$journal->getPath()}
	<table width="100%" class="info u-hide">
		<tr class="tableHeading">
			<th>User Pages</th>
			<th>Assign</th>
			<th>Review</th>
			<th>Edit</th>
			<th>Frequent Tasks</th>
		</tr>
		{if $isValid.JournalManager.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="manager"}">{translate key="user.role.manager"}</a></td>
				<td></td>
				<td></td>
				<td></td>
				<td align="right">[<a href="{url journal=$journalPath page="manager" op="setup" path="1"}">{translate key="manager.setup"}</a>]</td>
			</tr>
		{/if}
		{if $isValid.SubscriptionManager.$journalId}
			<tr>
				<td width="20%" colspan="1"><a href="{url journal=$journalPath page="subscriptionManager"}">{translate key="user.role.subscriptionManager"}</a></td>
				<td colspan="4"></td>
			</tr>
		{/if}
		{if $isValid.Editor.$journalId || $isValid.SectionEditor.$journalId || $isValid.LayoutEditor.$journalId || $isValid.Copyeditor.$journalId || $isValid.Proofreader.$journalId}
			<tr><td class="separator" width="100%" colspan="5">&nbsp;</td></tr>
		{/if}
		{if $isValid.Editor.$journalId}
			<tr>
				{assign var="editorSubmissionsCount" value=$submissionsCount.Editor.$journalId}
				<td><a href="{url journal=$journalPath page="editor"}">{translate key="user.role.editor"}</a></td>
				<td>{if $editorSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="editor" op="submissions" path="submissionsUnassigned"}">{$editorSubmissionsCount[0]} {translate key="common.queue.short.submissionsUnassigned"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsUnassigned"}</span>{/if}
				</td>
				<td>{if $editorSubmissionsCount[1]}
						<a href="{url journal=$journalPath page="editor" op="submissions" path="submissionsInReview"}">{$editorSubmissionsCount[1]} {translate key="common.queue.short.submissionsInReview"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInReview"}</span>{/if}
				</td>
				<td>{if $editorSubmissionsCount[2]}
						<a href="{url journal=$journalPath page="editor" op="submissions" path="submissionsInEditing"}">{$editorSubmissionsCount[2]} {translate key="common.queue.short.submissionsInEditing"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInEditing"}</span>{/if}
				</td>
				<td align="right">[<a href="{url journal=$journalPath page="editor" op="createIssue"}">{translate key="editor.issues.createIssue"}</a>] [<a href="{url journal=$journalPath page="editor" op="notifyUsers"}">{translate key="editor.notifyUsers"}</a>]</td>
			</tr>
		{/if}
		{if $isValid.SectionEditor.$journalId}
			{assign var="sectionEditorSubmissionsCount" value=$submissionsCount.SectionEditor.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="sectionEditor"}">{translate key="user.role.sectionEditor"}</a></td>
				<td></td>
				<td>{if $sectionEditorSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="sectionEditor" op="index" path="submissionsInReview"}">{$sectionEditorSubmissionsCount[0]} {translate key="common.queue.short.submissionsInReview"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInReview"}</span>{/if}
				</td>
				<td>{if $sectionEditorSubmissionsCount[1]}
						<a href="{url journal=$journalPath page="sectionEditor" op="index" path="submissionsInEditing"}">{$sectionEditorSubmissionsCount[1]} {translate key="common.queue.short.submissionsInEditing"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInEditing"}</span>{/if}
				</td>
				<td align="right"></td>
			</tr>
		{/if}
		{if $isValid.LayoutEditor.$journalId}
			{assign var="layoutEditorSubmissionsCount" value=$submissionsCount.LayoutEditor.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="layoutEditor"}">{translate key="user.role.layoutEditor"}</a></td>
				<td></td>
				<td></td>
				<td>{if $layoutEditorSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="layoutEditor" op="submissions"}">{$layoutEditorSubmissionsCount[0]} {translate key="common.queue.short.submissionsInEditing"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInEditing"}</span>{/if}
				</td>
				<td align="right"></td>
			</tr>
		{/if}
		{if $isValid.Copyeditor.$journalId}
			{assign var="copyeditorSubmissionsCount" value=$submissionsCount.Copyeditor.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="copyeditor"}">{translate key="user.role.copyeditor"}</a></td>
				<td></td>
				<td></td>
				<td>{if $copyeditorSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="copyeditor"}">{$copyeditorSubmissionsCount[0]} {translate key="common.queue.short.submissionsInEditing"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInEditing"}</span>{/if}
				</td>
				<td align="right"></td>
			</tr>
		{/if}
		{if $isValid.Proofreader.$journalId}
			{assign var="proofreaderSubmissionsCount" value=$submissionsCount.Proofreader.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="proofreader"}">{translate key="user.role.proofreader"}</a></td>
				<td></td>
				<td></td>
				<td>{if $proofreaderSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="proofreader"}">{$proofreaderSubmissionsCount[0]} {translate key="common.queue.short.submissionsInEditing"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.submissionsInEditing"}</span>{/if}
				</td>
				<td align="right"></td>
			</tr>
		{/if}
		{if $isValid.Author.$journalId || $isValid.Reviewer.$journalId}
			<tr><td class="separator" width="100%" colspan="5">&nbsp;</td></tr>
		{/if}
		{if $isValid.Author.$journalId}
			{assign var="authorSubmissionsCount" value=$submissionsCount.Author.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="author"}">{translate key="user.role.author"}</a></td>
				<td></td>
				<td>{if $authorSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="author"}">{$authorSubmissionsCount[0]} {translate key="common.queue.short.active"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.active"}</span>{/if}
				</td>
				{* This is for all non-pending items*}
				<td>{if $authorSubmissionsCount[1]}
						<a href="{url journal=$journalPath path="completed" page="author"}">{$authorSubmissionsCount[1]} {translate key="common.queue.short.completed"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.completed"}</span>{/if}
				</td>
				<td align="right">[<a href="{url journal=$journalPath page="author" op="submit"}">{translate key="author.submit"}</a>]</td>
			</tr>
		{/if}
		{if $isValid.Reviewer.$journalId}
			{assign var="reviewerSubmissionsCount" value=$submissionsCount.Reviewer.$journalId}
			<tr>
				<td><a href="{url journal=$journalPath page="reviewer"}">{translate key="user.role.reviewer"}</a></td>
				<td></td>
				<td></td>
				<td>{if $reviewerSubmissionsCount[0]}
						<a href="{url journal=$journalPath page="reviewer"}">{$reviewerSubmissionsCount[0]} {translate key="common.queue.short.active"}</a>
					{else}<span class="disabled">0 {translate key="common.queue.short.active"}</span>{/if}
				</td>
				<td align="right"></td>
			</tr>
		{/if}
		{* Add a row to the bottom of each table to ensure all have same width*}
		<tr>
			<td width="22%"></td>
			<td width="16%"></td>
			<td width="16%"></td>
			<td width="16%"></td>
			<td width="30%"></td>
		</tr>
	</table>
	{call_hook name="Templates::User::Index::Journal" journal=$journal}
	</div>
{/foreach}
</div>

{if !$hasRole}
<div class="account-container__section account-container__section--centered-card-title has-role">
	{if $currentJournal}
	<div id="noRolesForJournal">
    	<p>{translate key="user.noRoles.noRolesForJournal"}</p>
    	<ul class="account-container__grid">
    	    {if $allowRegAuthor}
    	    {url|assign:"submitUrl" page="author" op="submit"}
            <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
                <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                    <div class="eds-c-card-composable__body">
                        <div class="eds-c-card-composable__content">
                            <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="{url op="become" path="author" source=$submitUrl}">{translate key="user.noRoles.submitArticle"}</a>
                            </h3>
                            <p class="eds-c-card-composable__summary">Manage assigned manuscript reviews and submit your expert evaluations and recommendations.</p>
                        </div>
                    </div>
                    <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </article></li>
            {else}{* $allowRegAuthor *}
            <li><article class="eds-c-card-composable">
                <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                    <div class="eds-c-card-composable__body">
                        <div class="eds-c-card-composable__content">
                            <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="javascript:void(0)">{translate key="user.noRoles.submitArticle"}</a>
                            </h3>
                            <p class="eds-c-card-composable__summary">{translate key="user.noRoles.submitArticleRegClosed"}</p>
                        </div>
                    </div>
                    <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </article></li>
            {/if}{* $allowRegAuthor *}
    		<li class="u-hide">
    			{if $allowRegAuthor}
    				{url|assign:"submitUrl" page="author" op="submit"}
    				<a href="{url op="become" path="author" source=$submitUrl}">{translate key="user.noRoles.submitArticle"}</a>
    			{else}{* $allowRegAuthor *}
    				{translate key="user.noRoles.submitArticleRegClosed"}
    			{/if}{* $allowRegAuthor *}
    		</li>
    		{if $allowRegReviewer}
    		{url|assign:"userHomeUrl" page="user" op="index"}
            <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
                <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                    <div class="eds-c-card-composable__body">
                        <div class="eds-c-card-composable__content">
                            <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="{url op="become" path="reviewer" source=$userHomeUrl}">{translate key="user.noRoles.regReviewer"}</a>
                            </h3>
                            <p class="eds-c-card-composable__summary">Manage assigned manuscript reviews and submit your expert evaluations and recommendations.</p>
                        </div>
                    </div>
                    <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M9 1a5 5 0 1 1 0 10A5 5 0 0 1 9 1Zm6 0a5 5 0 0 1 0 10 1 1 0 0 1-.117-1.993L15 9a3 3 0 0 0 0-6 1 1 0 0 1 0-2ZM9 3a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm8.857 9.545a7.99 7.99 0 0 1 2.651 1.715A8.31 8.31 0 0 1 23 20.134V21a1 1 0 0 1-1 1h-3a1 1 0 0 1 0-2h1.995l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209a5.99 5.99 0 0 0-1.988-1.287 1 1 0 1 1 .732-1.861Zm-3.349 1.715A8.31 8.31 0 0 1 17 20.134V21a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-.877c.044-4.343 3.387-7.908 7.638-8.115a7.908 7.908 0 0 1 5.87 2.252ZM9.016 14l-.285.006c-3.104.15-5.58 2.718-5.725 5.9L3.004 20h11.991l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209A5.924 5.924 0 0 0 9.3 14.008L9.016 14Z" /></svg>
                    </div>
                </div>
            </article></li>
            {else}{* $allowRegReviewer *}
            <li><article class="eds-c-card-composable">
                <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                    <div class="eds-c-card-composable__body">
                        <div class="eds-c-card-composable__content">
                            <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="javascript:void(0)">{translate key="user.noRoles.regReviewer"}</a>
                            </h3>
                            <p class="eds-c-card-composable__summary">{translate key="user.noRoles.regReviewerClosed"}</p>
                        </div>
                    </div>
                    <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M9 1a5 5 0 1 1 0 10A5 5 0 0 1 9 1Zm6 0a5 5 0 0 1 0 10 1 1 0 0 1-.117-1.993L15 9a3 3 0 0 0 0-6 1 1 0 0 1 0-2ZM9 3a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm8.857 9.545a7.99 7.99 0 0 1 2.651 1.715A8.31 8.31 0 0 1 23 20.134V21a1 1 0 0 1-1 1h-3a1 1 0 0 1 0-2h1.995l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209a5.99 5.99 0 0 0-1.988-1.287 1 1 0 1 1 .732-1.861Zm-3.349 1.715A8.31 8.31 0 0 1 17 20.134V21a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-.877c.044-4.343 3.387-7.908 7.638-8.115a7.908 7.908 0 0 1 5.87 2.252ZM9.016 14l-.285.006c-3.104.15-5.58 2.718-5.725 5.9L3.004 20h11.991l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209A5.924 5.924 0 0 0 9.3 14.008L9.016 14Z" /></svg>
                    </div>
                </div>
            </article></li>
            {/if}{* $allowRegReviewer *}
    		<li class="u-hide">
    			{if $allowRegReviewer}
    				{url|assign:"userHomeUrl" page="user" op="index"}
    				<a href="{url op="become" path="reviewer" source=$userHomeUrl}">{translate key="user.noRoles.regReviewer"}</a>
    			{else}{* $allowRegReviewer *}
    				{translate key="user.noRoles.regReviewerClosed"}
    			{/if}{* $allowRegReviewer *}
    		</li>
    	</ul>
	</div>
	{else}{* $currentJournal *}
	<div id="currentJournal">
    	<p>{translate key="user.noRoles.chooseJournal"}</p>
    	<ul>
    		{foreach from=$allJournals item=thisJournal}
    			<li><a href="{url journal=$thisJournal->getPath() page="user" op="index"}">{$thisJournal->getLocalizedTitle()|escape}</a></li>
    		{/foreach}
    	</ul>
	</div>
	{/if}{* $currentJournal *}
</div>
{/if}{* !$hasRole *}
</section>

{if $hasRole && $currentJournal}
{foreach from=$userJournals item=journal}
        
{assign var="hasRole" value=1}
{assign var="journalId" value=$journal->getId()}
{assign var="journalPath" value=$journal->getPath()}
{if $isValid.Editor.$journalId || $isValid.SectionEditor.$journalId || $isValid.LayoutEditor.$journalId || $isValid.Copyeditor.$journalId || $isValid.Proofreader.$journalId}
<section class="account-container__section">
    <h2 class="u-h2">Editorial work</h2>
    <ul class="account-container__grid">
        
        {if $isValid.Editor.$journalId}
        {assign var="editorSubmissionsCount" value=$submissionsCount.Editor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="editor"}">Editorial tasks</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {if $editorSubmissionsCount[0]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="submissions" path="submissionsUnassigned"}" target="_blank">Submission Unassigned</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 19H21M19 17V21M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H12M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V12M9 17H12M9 13H15M9 9H10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $editorSubmissionsCount[1]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="submissions" path="submissionsInReview"}" target="_blank">Submission In Review</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 10.5H7.51M12 10.5H12.01M16.5 10.5H16.51M9.9 19.2L11.36 21.1467C11.5771 21.4362 11.6857 21.5809 11.8188 21.6327C11.9353 21.678 12.0647 21.678 12.1812 21.6327C12.3143 21.5809 12.4229 21.4362 12.64 21.1467L14.1 19.2C14.3931 18.8091 14.5397 18.6137 14.7185 18.4645C14.9569 18.2656 15.2383 18.1248 15.5405 18.0535C15.7671 18 16.0114 18 16.5 18C17.8978 18 18.5967 18 19.1481 17.7716C19.8831 17.4672 20.4672 16.8831 20.7716 16.1481C21 15.5967 21 14.8978 21 13.5V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V13.5C3 14.8978 3 15.5967 3.22836 16.1481C3.53284 16.8831 4.11687 17.4672 4.85195 17.7716C5.40326 18 6.10218 18 7.5 18C7.98858 18 8.23287 18 8.45951 18.0535C8.76169 18.1248 9.04312 18.2656 9.2815 18.4645C9.46028 18.6137 9.60685 18.8091 9.9 19.2ZM8 10.5C8 10.7761 7.77614 11 7.5 11C7.22386 11 7 10.7761 7 10.5C7 10.2239 7.22386 10 7.5 10C7.77614 10 8 10.2239 8 10.5ZM12.5 10.5C12.5 10.7761 12.2761 11 12 11C11.7239 11 11.5 10.7761 11.5 10.5C11.5 10.2239 11.7239 10 12 10C12.2761 10 12.5 10.2239 12.5 10.5ZM17 10.5C17 10.7761 16.7761 11 16.5 11C16.2239 11 16 10.7761 16 10.5C16 10.2239 16.2239 10 16.5 10C16.7761 10 17 10.2239 17 10.5Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $editorSubmissionsCount[2]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="submissions" path="submissionsInEditing"}">Submission In Editing</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M9 17H11.5M9 13H14M9 9H10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="createIssue"}" target="_blank">{translate key="editor.issues.createIssue"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 11.5V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22H12.5M21 10H3M16 2V6M8 2V6M18 21V15M15 18H21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="notifyUsers"}" target="_blank">{translate key="editor.notifyUsers"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="6" r="4" stroke-width="2" stroke="currentColor"></circle><path d="M19 2C19 2 21 3.2 21 6C21 8.8 19 10 19 10" stroke-width="1.5" stroke-linecap="round" stroke="currentColor"></path><path d="M17 4C17 4 18 4.6 18 6C18 7.4 17 8 17 8" stroke-width="1.5" stroke-linecap="round" stroke="currentColor"></path><path d="M17.9975 18C18 17.8358 18 17.669 18 17.5C18 15.0147 14.4183 13 10 13C5.58172 13 2 15.0147 2 17.5C2 19.9853 2 22 10 22C12.231 22 13.8398 21.8433 15 21.5634" stroke-width="2" stroke-linecap="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.SectionEditor.$journalId}
        {assign var="sectionEditorSubmissionsCount" value=$submissionsCount.SectionEditor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="sectionEditor"}">{translate key="user.role.sectionEditor"} tasks</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {if $sectionEditorSubmissionsCount[0]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="sectionEditor" op="index" path="submissionsInReview"}">{translate key="user.role.sectionEditor"}: {translate key="common.queue.short.submissionsInReview"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M9 17H11.5M9 13H14M9 9H10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $sectionEditorSubmissionsCount[1]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="sectionEditor" op="index" path="submissionsInEditing"}">{translate key="user.role.sectionEditor"}: {translate key="common.queue.short.submissionsInEditing"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M9 17H11.5M9 13H14M9 9H10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {/if}{** SectionEditor **}
        {if $isValid.LayoutEditor.$journalId}
        {assign var="layoutEditorSubmissionsCount" value=$submissionsCount.LayoutEditor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="layoutEditor"}">{translate key="user.role.layoutEditor"} tasks:</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.Copyeditor.$journalId}
        {assign var="copyeditorSubmissionsCount" value=$submissionsCount.Copyeditor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="copyeditor"}">{translate key="user.role.copyeditor"} tasks:</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.Proofreader.$journalId}
        {assign var="proofreaderSubmissionsCount" value=$submissionsCount.Proofreader.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="proofreader"}">Proofread</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published{if $proofreaderSubmissionsCount[0]} {$proofreaderSubmissionsCount[0]}{/if} articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 17H13M9 13H13M9 9H10M17.0098 18V15M17.0098 21H16.9998M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H13M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.Reviewer.$journalId}
        {assign var="reviewerSubmissionsCount" value=$submissionsCount.Reviewer.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="{url journal=$journalPath page="reviewer"}">Peer review</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Manage assigned {if $reviewerSubmissionsCount[0]}{$reviewerSubmissionsCount[0]} {/if}manuscript reviews and submit your expert evaluations and recommendations.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M9 1a5 5 0 1 1 0 10A5 5 0 0 1 9 1Zm6 0a5 5 0 0 1 0 10 1 1 0 0 1-.117-1.993L15 9a3 3 0 0 0 0-6 1 1 0 0 1 0-2ZM9 3a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm8.857 9.545a7.99 7.99 0 0 1 2.651 1.715A8.31 8.31 0 0 1 23 20.134V21a1 1 0 0 1-1 1h-3a1 1 0 0 1 0-2h1.995l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209a5.99 5.99 0 0 0-1.988-1.287 1 1 0 1 1 .732-1.861Zm-3.349 1.715A8.31 8.31 0 0 1 17 20.134V21a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-.877c.044-4.343 3.387-7.908 7.638-8.115a7.908 7.908 0 0 1 5.87 2.252ZM9.016 14l-.285.006c-3.104.15-5.58 2.718-5.725 5.9L3.004 20h11.991l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209A5.924 5.924 0 0 0 9.3 14.008L9.016 14Z" /></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {call_hook name="Templates::User::Index::Journal" journal=$journal}
    </ul>
</section>
{/if}

{assign var="hasRole" value=1}
{assign var="journalId" value=$journal->getId()}
{assign var="journalPath" value=$journal->getPath()}
{if $isValid.Editor.$journalId || $isValid.SectionEditor.$journalId || $isValid.LayoutEditor.$journalId || $isValid.Copyeditor.$journalId || $isValid.Proofreader.$journalId}
<section class="account-container__section account-container__section--centered-card-title">
    <h2 class="u-h2">Your work</h2>
    <ul class="account-container__grid">
        
        {if $isValid.Editor.$journalId}
        {assign var="editorSubmissionsCount" value=$submissionsCount.Editor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="editor"}">Editorial tasks</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {if $editorSubmissionsCount[0]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="submissions" path="submissionsUnassigned"}" target="_blank">Submission Unassigned</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 19H21M19 17V21M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H12M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V12M9 17H12M9 13H15M9 9H10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $editorSubmissionsCount[1]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="submissions" path="submissionsInReview"}" target="_blank">Submission In Review</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 10.5H7.51M12 10.5H12.01M16.5 10.5H16.51M9.9 19.2L11.36 21.1467C11.5771 21.4362 11.6857 21.5809 11.8188 21.6327C11.9353 21.678 12.0647 21.678 12.1812 21.6327C12.3143 21.5809 12.4229 21.4362 12.64 21.1467L14.1 19.2C14.3931 18.8091 14.5397 18.6137 14.7185 18.4645C14.9569 18.2656 15.2383 18.1248 15.5405 18.0535C15.7671 18 16.0114 18 16.5 18C17.8978 18 18.5967 18 19.1481 17.7716C19.8831 17.4672 20.4672 16.8831 20.7716 16.1481C21 15.5967 21 14.8978 21 13.5V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V13.5C3 14.8978 3 15.5967 3.22836 16.1481C3.53284 16.8831 4.11687 17.4672 4.85195 17.7716C5.40326 18 6.10218 18 7.5 18C7.98858 18 8.23287 18 8.45951 18.0535C8.76169 18.1248 9.04312 18.2656 9.2815 18.4645C9.46028 18.6137 9.60685 18.8091 9.9 19.2ZM8 10.5C8 10.7761 7.77614 11 7.5 11C7.22386 11 7 10.7761 7 10.5C7 10.2239 7.22386 10 7.5 10C7.77614 10 8 10.2239 8 10.5ZM12.5 10.5C12.5 10.7761 12.2761 11 12 11C11.7239 11 11.5 10.7761 11.5 10.5C11.5 10.2239 11.7239 10 12 10C12.2761 10 12.5 10.2239 12.5 10.5ZM17 10.5C17 10.7761 16.7761 11 16.5 11C16.2239 11 16 10.7761 16 10.5C16 10.2239 16.2239 10 16.5 10C16.7761 10 17 10.2239 17 10.5Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $editorSubmissionsCount[2]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="submissions" path="submissionsInEditing"}">Submission In Editing</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M9 17H11.5M9 13H14M9 9H10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="createIssue"}" target="_blank">{translate key="editor.issues.createIssue"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 11.5V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22H12.5M21 10H3M16 2V6M8 2V6M18 21V15M15 18H21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="editor" op="notifyUsers"}" target="_blank">{translate key="editor.notifyUsers"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="6" r="4" stroke-width="2" stroke="currentColor"></circle><path d="M19 2C19 2 21 3.2 21 6C21 8.8 19 10 19 10" stroke-width="1.5" stroke-linecap="round" stroke="currentColor"></path><path d="M17 4C17 4 18 4.6 18 6C18 7.4 17 8 17 8" stroke-width="1.5" stroke-linecap="round" stroke="currentColor"></path><path d="M17.9975 18C18 17.8358 18 17.669 18 17.5C18 15.0147 14.4183 13 10 13C5.58172 13 2 15.0147 2 17.5C2 19.9853 2 22 10 22C12.231 22 13.8398 21.8433 15 21.5634" stroke-width="2" stroke-linecap="round" stroke="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.SectionEditor.$journalId}
        {assign var="sectionEditorSubmissionsCount" value=$submissionsCount.SectionEditor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="sectionEditor"}">{translate key="user.role.sectionEditor"} tasks</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {if $sectionEditorSubmissionsCount[0]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="sectionEditor" op="index" path="submissionsInReview"}">{translate key="user.role.sectionEditor"}: {translate key="common.queue.short.submissionsInReview"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M9 17H11.5M9 13H14M9 9H10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $sectionEditorSubmissionsCount[1]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="sectionEditor" op="index" path="submissionsInEditing"}">{translate key="user.role.sectionEditor"}: {translate key="common.queue.short.submissionsInEditing"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M9 17H11.5M9 13H14M9 9H10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {/if}{** SectionEditor **}
        {if $isValid.LayoutEditor.$journalId}
        {assign var="layoutEditorSubmissionsCount" value=$submissionsCount.LayoutEditor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="layoutEditor"}">{translate key="user.role.layoutEditor"} tasks:</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.Copyeditor.$journalId}
        {assign var="copyeditorSubmissionsCount" value=$submissionsCount.Copyeditor.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Editorial tasks" href="{url journal=$journalPath page="copyeditor"}">{translate key="user.role.copyeditor"} tasks:</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Oversee editorial decisions, manage submissions, and coordinate the peer review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M13 4H14.6875C15.4124 4 16 4.61095 16 5.36461V8M5.28125 4H3.3125C2.58763 4 2 4.61095 2 5.36461V18.6354C2 19.389 2.58763 20 3.3125 20H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M11.6 2H7.4C6.6268 2 6 2.71634 6 3.6V4.4C6 5.28366 6.6268 6 7.4 6H11.6C12.3732 6 13 5.28366 13 4.4V3.6C13 2.71634 12.3732 2 11.6 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12.5C22 11.6716 21.3284 11 20.5 11L13.5 11C12.6716 11 12 11.6716 12 12.5V20.5C12 21.3284 12.6716 22 13.5 22H20.5C21.3284 22 22 21.3284 22 20.5V12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 15H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path><path d="M15 18.5H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.Proofreader.$journalId}
        {assign var="proofreaderSubmissionsCount" value=$submissionsCount.Proofreader.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath page="proofreader"}">Proofread</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published{if $proofreaderSubmissionsCount[0]} {$proofreaderSubmissionsCount[0]}{/if} articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 17H13M9 13H13M9 9H10M17.0098 18V15M17.0098 21H16.9998M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H13M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if $isValid.Reviewer.$journalId}
        {assign var="reviewerSubmissionsCount" value=$submissionsCount.Reviewer.$journalId}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="{url journal=$journalPath page="reviewer"}">Peer review</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Manage assigned {if $reviewerSubmissionsCount[0]}{$reviewerSubmissionsCount[0]} {/if}manuscript reviews and submit your expert evaluations and recommendations.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M9 1a5 5 0 1 1 0 10A5 5 0 0 1 9 1Zm6 0a5 5 0 0 1 0 10 1 1 0 0 1-.117-1.993L15 9a3 3 0 0 0 0-6 1 1 0 0 1 0-2ZM9 3a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm8.857 9.545a7.99 7.99 0 0 1 2.651 1.715A8.31 8.31 0 0 1 23 20.134V21a1 1 0 0 1-1 1h-3a1 1 0 0 1 0-2h1.995l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209a5.99 5.99 0 0 0-1.988-1.287 1 1 0 1 1 .732-1.861Zm-3.349 1.715A8.31 8.31 0 0 1 17 20.134V21a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-.877c.044-4.343 3.387-7.908 7.638-8.115a7.908 7.908 0 0 1 5.87 2.252ZM9.016 14l-.285.006c-3.104.15-5.58 2.718-5.725 5.9L3.004 20h11.991l-.005-.153a6.307 6.307 0 0 0-1.673-3.945l-.204-.209A5.924 5.924 0 0 0 9.3 14.008L9.016 14Z" /></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {call_hook name="Templates::User::Index::Journal" journal=$journal}
    </ul>
</section>
{/if}

{if $isValid.Author.$journalId}
{assign var="authorSubmissionsCount" value=$submissionsCount.Author.$journalId}
<section class="account-container__section">
    <h2 class="u-h2">{translate key="user.role.author"} work: <a href="{url journal=$journalPath page="author"}">Dashboard</a></h2>
    <ul class="account-container__grid">
        {if $authorSubmissionsCount[0]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Submission in progress" href="{url journal=$journalPath path="active" page="author"}">Submission in progress</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Track your manuscript submissions and monitor their progress through the editorial review process.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M16 4.03838L18.5 4C19.3284 4 20 4.68732 20 5.53518V20.4648C20 21.3127 19.3284 22 18.5 22H5.5C4.67157 22 4 21.3127 4 20.4648V5.53518C4 4.68732 4.67157 4 5.5 4H7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 13H15.857" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 16.929H15.857" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M14.4 2H9.6C8.71634 2 8 2.71634 8 3.6V4.4C8 5.28366 8.71634 6 9.6 6H14.4C15.2837 6 16 5.28366 16 4.4V3.6C16 2.71634 15.2837 2 14.4 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Track your research" href="{url journal=$journalPath page="author"}">Track your research</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">Track your submissions and see how your publications are performing and more.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M11 20H3.5C2.67157 20 2 19.3284 2 18.5L2 5.5C2 4.67157 2.67157 4 3.5 4H8.24306C8.73662 4 9.21914 4.1461 9.62981 4.41987L12 6L14.3702 4.41987C14.7809 4.1461 15.2634 4 15.7569 4H20.5C21.3284 4 22 4.67157 22 5.5V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M17.5 20C19.433 20 21 18.433 21 16.5C21 14.567 19.433 13 17.5 13C15.567 13 14 14.567 14 16.5C14 18.433 15.567 20 17.5 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M22 21L20 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 6V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
                </div>
            </div>
        </article></li>
        {if $authorSubmissionsCount[1]}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Published work" href="{url journal=$journalPath path="completed" page="author"}" target="_blank">Published work</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">View your published {$authorSubmissionsCount[1]} articles and access performance metrics and readership statistics.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M16 4.03838L18.5 4C19.3284 4 20 4.68732 20 5.53518V20.4648C20 21.3127 19.3284 22 18.5 22H5.5C4.67157 22 4 21.3127 4 20.4648V5.53518C4 4.68732 4.67157 4 5.5 4H7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /><path fill-rule="evenodd" clip-rule="evenodd" d="M14.4 2H9.6C8.71634 2 8 2.71634 8 3.6V4.4C8 5.28366 8.71634 6 9.6 6H14.4C15.2837 6 16 5.28366 16 4.4V3.6C16 2.71634 15.2837 2 14.4 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /><path d="M8 15.25L10.6667 17L16 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </div>
            </div>
        </article></li>
        {/if}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Your work" data-track-value="Peer review" href="{url journal=$journalPath page="author" op="submit"}">{translate key="author.submit"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Manage assigned manuscript reviews and submit your expert evaluations and recommendations.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H10M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19M19 9V10M14 21L16.025 20.595C16.2015 20.5597 16.2898 20.542 16.3721 20.5097C16.4452 20.4811 16.5147 20.4439 16.579 20.399C16.6516 20.3484 16.7152 20.2848 16.8426 20.1574L21 16C21.5523 15.4477 21.5523 14.5523 21 14C20.4477 13.4477 19.5523 13.4477 19 14L14.8426 18.1574C14.7152 18.2848 14.6516 18.3484 14.601 18.421C14.5561 18.4853 14.5189 18.5548 14.4903 18.6279C14.458 18.7102 14.4403 18.7985 14.405 18.975L14 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
    </ul>
</section>
{/if}

{/foreach}
{/if}{* !$hasRole *}

<div class="account-container__section">
    <h1 class="u-h2">{translate key="user.myAccount"}</h1>
    <ul class="account-container__grid">
        {if $isUserLoggedIn}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Manage your account" href="{url page="user" op="update-profile"}">{translate key="user.editMyProfile"}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">Manage your account and update your communications settings.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="c-masthead__main-image c-masthead__main-image--profile-image" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1c6.075 0 11 4.925 11 11s-4.925 11-11 11S1 18.075 1 12 5.925 1 12 1Zm0 16c-1.806 0-3.52.994-4.664 2.698A8.947 8.947 0 0 0 12 21a8.958 8.958 0 0 0 4.664-1.301C15.52 17.994 13.806 17 12 17Zm0-14a9 9 0 0 0-6.25 15.476C7.253 16.304 9.54 15 12 15s4.747 1.304 6.25 3.475A9 9 0 0 0 12 3Zm0 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" /></svg>
                </div>
            </div>
        </article></li>
        {/if}
        {if !$implicitAuth}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_manage_your_account_item" data-track-context="my-profile_manage-your-account" data-track-label="Settings" data-track-value="Password" href="{url page="user" op="changePassword"}">{translate key="user.changeMyPassword"}</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">••••••••••••</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M2 2V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5.07666 14.4616L11.5382 9.84619" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.30762 8.46143V15.846" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5.07666 9.84619L11.5382 14.4616" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15.5386 14.4616L22.0001 9.84619" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18.769 8.46143V15.846" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15.5386 9.84619L22.0001 14.4616" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
        <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
            <div class="eds-c-card-composable__body">
                <div class="eds-c-card-composable__content">
                    <h3 class="eds-c-card-composable__title"><a data-track="click_manage_your_account_item" data-track-context="my-profile_manage-your-account" data-track-label="Settings" data-track-value="Linked accounts" href="{url page="user" op="linked-accounts"}" >Linked accounts</a>
                    </h3>
                    <p class="eds-c-card-composable__summary">Link a Google or ORCID account to enable faster access to your work at Scholar Wizdam</p>
                </div>
            </div>
            <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false"><use xlink:href="#icon-linked-accounts-rotated"></use><symbol id="icon-linked-accounts-rotated" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M23 3.34819C23 2.05141 21.9479 1 20.6504 1H8.34962C7.05214 1 6 2.05141 6 3.34819V4C6 4.55228 6.44772 5 7 5C7.55228 5 8 4.55228 8 4V3.34819C8 3.15629 8.1564 3 8.34962 3H20.6504C20.8436 3 21 3.15629 21 3.34819V15.6502C21 15.8434 20.8434 16 20.6504 16H20C19.4477 16 19 16.4477 19 17C19 17.5523 19.4477 18 20 18H20.6504C21.9481 18 23 16.948 23 15.6502V3.34819ZM16.7724 6.27428C16.3555 6.06585 15.9605 6 15.6504 6H3.34962C2.36634 6 1.64894 6.47826 1.27428 7.2276C1.06585 7.64446 1 8.03953 1 8.34962V20.6504C1 21.6337 1.47826 22.3511 2.2276 22.7257C2.64446 22.9342 3.03953 23 3.34962 23H15.6504C16.6337 23 17.3511 22.5217 17.7257 21.7724C17.9342 21.3555 18 20.9605 18 20.6504V8.34962C18 7.36634 17.5217 6.64894 16.7724 6.27428ZM3.274 8.00182L15.6504 8C15.6777 8 15.7887 8.01851 15.878 8.06313C15.9722 8.11022 16 8.15199 16 8.34962V20.6504C16 20.6777 15.9815 20.7887 15.9369 20.878C15.8898 20.9722 15.848 21 15.6504 21L14.7629 21C13.9746 18.731 11.9492 17.5 9.5 17.5C7.05081 17.5 5.02538 18.731 4.23712 21L3.34962 21C3.32231 21 3.21127 20.9815 3.12202 20.9369C3.02785 20.8898 3 20.848 3 20.6504V8.34962C3 8.32231 3.01851 8.21127 3.06313 8.12203C3.10349 8.0413 3.13995 8.00931 3.274 8.00182ZM9.5 19.5C8.13911 19.5 7.053 20.01 6.43934 20.9996H12.5607C11.947 20.01 10.8609 19.5 9.5 19.5ZM9.5 10C11.433 10 13 11.567 13 13.5C13 15.433 11.433 17 9.5 17C7.567 17 6 15.433 6 13.5C6 11.567 7.567 10 9.5 10ZM8 13.5C8 12.6716 8.67157 12 9.5 12C10.3284 12 11 12.6716 11 13.5C11 14.3284 10.3284 15 9.5 15C8.67157 15 8 14.3284 8 13.5Z" fill="currentColor"></path></symbol></svg>
            </div>
        </div>
        </article></li>
        {if $currentJournal}{if $subscriptionsEnabled}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Subscription and purchases" href="{url page="user" op="subscriptions"}">Subscriptions and purchases</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">View and download past purchases and manage your journal and email subscriptions.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.5524 1.91577C17.9709 1.91637 19.2815 2.64998 19.9331 3.83354L21.9697 7.49923L21.9902 7.54179L22.0271 7.63119L22.0581 7.73766L22.07 7.79617L22.0796 7.86957C22.0822 7.89497 22.0836 7.92023 22.0841 7.94544L22.0842 19.6196C22.0842 20.2644 21.8377 20.8852 21.3949 21.3471C20.9441 21.8175 20.3266 22.0843 19.6799 22.0843H4.3208C3.67403 22.0843 3.05658 21.8175 2.60576 21.3471C2.16298 20.8852 1.9165 20.2644 1.9165 19.6196L1.91681 7.91419C1.9269 7.6952 2.00054 7.47949 2.13774 7.30853L4.13482 3.83731C4.77265 2.67771 6.03918 1.94828 7.44775 1.91577H16.5524ZM20.0672 8.97476H3.93237L3.93326 19.6196C3.93326 19.7153 3.96007 19.8068 4.00754 19.8821L4.06169 19.9515C4.13442 20.0274 4.22706 20.0675 4.32071 20.0675H19.6798C19.7734 20.0675 19.866 20.0274 19.9388 19.9515C20.0196 19.8673 20.0672 19.7472 20.0672 19.6196V8.97476ZM19.0588 17.5464C19.0588 16.9894 18.6073 16.538 18.0503 16.538H14.0166C13.4597 16.538 13.0082 16.9894 13.0082 17.5464C13.0082 18.1033 13.4597 18.5548 14.0166 18.5548H18.0503C18.6073 18.5548 19.0588 18.1033 19.0588 17.5464ZM16.5521 3.93263H13.0082V6.95791H19.3623L18.1682 4.80968C17.8784 4.28315 17.2527 3.93292 16.5521 3.93263ZM7.47131 3.93238L10.9914 3.93164V6.95793H4.66654L5.89275 4.82595C6.18465 4.29563 6.78777 3.94828 7.47131 3.93238Z" fill="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
        {/if}{/if}
        {if $currentJournal}{if $acceptGiftPayments}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Subscription and purchases" href="{url page="user" op="gifts"}">{translate key="gifts.manageMyGifts"}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">View and download and manage your gifts.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17H21M12 8L10 12M12 8L14 12M12 8H7.5C6.83696 8 6.20107 7.73661 5.73223 7.26777C5.26339 6.79893 5 6.16304 5 5.5C5 4.83696 5.26339 4.20107 5.73223 3.73223C6.20107 3.26339 6.83696 3 7.5 3C11 3 12 8 12 8ZM12 8H16.5C17.163 8 17.7989 7.73661 18.2678 7.26777C18.7366 6.79893 19 6.16304 19 5.5C19 4.83696 18.7366 4.20107 18.2678 3.73223C17.7989 3.26339 17.163 3 16.5 3C13 3 12 8 12 8ZM6.2 21H17.8C18.9201 21 19.4802 21 19.908 20.782C20.2843 20.5903 20.5903 20.2843 20.782 19.908C21 19.4802 21 18.9201 21 17.8V11.2C21 10.0799 21 9.51984 20.782 9.09202C20.5903 8.71569 20.2843 8.40973 19.908 8.21799C19.4802 8 18.9201 8 17.8 8H6.2C5.0799 8 4.51984 8 4.09202 8.21799C3.71569 8.40973 3.40973 8.71569 3.21799 9.09202C3 9.51984 3 10.0799 3 11.2V17.8C3 18.9201 3 19.4802 3.21799 19.908C3.40973 20.2843 3.71569 20.5903 4.09202 20.782C4.51984 21 5.07989 21 6.2 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </article></li>
        {/if}{/if}
    	{if $currentJournal}
    		{if $journalPaymentsEnabled && $membershipEnabled}
    			{if $dateEndMembership}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Subscription and purchases" href="{url page="user" op="payMembership"}">{translate key="payment.membership.renewMembership"}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">({translate key="payment.membership.ends"}: {$dateEndMembership|date_format:$dateFormatShort})</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.5524 1.91577C17.9709 1.91637 19.2815 2.64998 19.9331 3.83354L21.9697 7.49923L21.9902 7.54179L22.0271 7.63119L22.0581 7.73766L22.07 7.79617L22.0796 7.86957C22.0822 7.89497 22.0836 7.92023 22.0841 7.94544L22.0842 19.6196C22.0842 20.2644 21.8377 20.8852 21.3949 21.3471C20.9441 21.8175 20.3266 22.0843 19.6799 22.0843H4.3208C3.67403 22.0843 3.05658 21.8175 2.60576 21.3471C2.16298 20.8852 1.9165 20.2644 1.9165 19.6196L1.91681 7.91419C1.9269 7.6952 2.00054 7.47949 2.13774 7.30853L4.13482 3.83731C4.77265 2.67771 6.03918 1.94828 7.44775 1.91577H16.5524ZM20.0672 8.97476H3.93237L3.93326 19.6196C3.93326 19.7153 3.96007 19.8068 4.00754 19.8821L4.06169 19.9515C4.13442 20.0274 4.22706 20.0675 4.32071 20.0675H19.6798C19.7734 20.0675 19.866 20.0274 19.9388 19.9515C20.0196 19.8673 20.0672 19.7472 20.0672 19.6196V8.97476ZM19.0588 17.5464C19.0588 16.9894 18.6073 16.538 18.0503 16.538H14.0166C13.4597 16.538 13.0082 16.9894 13.0082 17.5464C13.0082 18.1033 13.4597 18.5548 14.0166 18.5548H18.0503C18.6073 18.5548 19.0588 18.1033 19.0588 17.5464ZM16.5521 3.93263H13.0082V6.95791H19.3623L18.1682 4.80968C17.8784 4.28315 17.2527 3.93292 16.5521 3.93263ZM7.47131 3.93238L10.9914 3.93164V6.95793H4.66654L5.89275 4.82595C6.18465 4.29563 6.78777 3.94828 7.47131 3.93238Z" fill="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
    			{else}
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h2 class="eds-c-card-composable__title"><a data-track="click_myprofile_homepage" data-track-context="my-profile_homepage" data-track-label="Account" data-track-value="Subscription and purchases" href="{url page="user" op="payMembership"}">{translate key="payment.membership.buyMembership"}</a>
                        </h2>
                        <p class="eds-c-card-composable__summary">View and download and manage your gifts.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.5524 1.91577C17.9709 1.91637 19.2815 2.64998 19.9331 3.83354L21.9697 7.49923L21.9902 7.54179L22.0271 7.63119L22.0581 7.73766L22.07 7.79617L22.0796 7.86957C22.0822 7.89497 22.0836 7.92023 22.0841 7.94544L22.0842 19.6196C22.0842 20.2644 21.8377 20.8852 21.3949 21.3471C20.9441 21.8175 20.3266 22.0843 19.6799 22.0843H4.3208C3.67403 22.0843 3.05658 21.8175 2.60576 21.3471C2.16298 20.8852 1.9165 20.2644 1.9165 19.6196L1.91681 7.91419C1.9269 7.6952 2.00054 7.47949 2.13774 7.30853L4.13482 3.83731C4.77265 2.67771 6.03918 1.94828 7.44775 1.91577H16.5524ZM20.0672 8.97476H3.93237L3.93326 19.6196C3.93326 19.7153 3.96007 19.8068 4.00754 19.8821L4.06169 19.9515C4.13442 20.0274 4.22706 20.0675 4.32071 20.0675H19.6798C19.7734 20.0675 19.866 20.0274 19.9388 19.9515C20.0196 19.8673 20.0672 19.7472 20.0672 19.6196V8.97476ZM19.0588 17.5464C19.0588 16.9894 18.6073 16.538 18.0503 16.538H14.0166C13.4597 16.538 13.0082 16.9894 13.0082 17.5464C13.0082 18.1033 13.4597 18.5548 14.0166 18.5548H18.0503C18.6073 18.5548 19.0588 18.1033 19.0588 17.5464ZM16.5521 3.93263H13.0082V6.95791H19.3623L18.1682 4.80968C17.8784 4.28315 17.2527 3.93292 16.5521 3.93263ZM7.47131 3.93238L10.9914 3.93164V6.95793H4.66654L5.89275 4.82595C6.18465 4.29563 6.78777 3.94828 7.47131 3.93238Z" fill="currentColor"></path></svg>
                </div>
            </div>
        </article></li>
    			{/if}
    		{/if}{* $journalPaymentsEnabled && $membershipEnabled *}
    	{/if}{* $userJournal *}
    	
        {call_hook name="Templates::User::Index::MyAccount"}
    </ul>
</div>

<section class="account-container__section">
    <h1 class="u-h2">Support</h2>
    <ul class="account-container__grid">
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_manage_your_account_item" data-track-context="my-profile_manage-your-account" data-track-label="Support" data-track-value="Contact support" href="javascript:void(0)" onclick="window.open('https://wa.me/' + atob('KzYyODUzNDM4ODAzODM'), '_blank')" >Contact support</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Having trouble with your account? Contact our support Sangia Wizdam team.</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M12 22C17.5229 22 22 17.5229 22 12C22 6.47715 17.5229 2 12 2C6.47715 2 2 6.47715 2 12C2 14.0934 2.64327 16.0366 3.74292 17.6426L2 22L7.4538 20.9092C8.81774 21.6066 10.3629 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.9999 18.1113C11.7874 18.1113 11.6152 17.9391 11.6152 17.7267C11.6152 17.5142 11.7874 17.342 11.9999 17.342" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18.1113C12.2124 18.1113 12.3846 17.9391 12.3846 17.7267C12.3846 17.5142 12.2124 17.342 12 17.342" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.30811 9.69231C9.30811 8.20539 10.5135 7 12.0004 7C13.4873 7 14.6927 8.20539 14.6927 9.69231C14.6927 11.1792 13.4873 12.3846 12.0004 12.3846V13.9231" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>
        </article></li>
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_manage_your_account_item" data-track-context="my-profile_manage-your-account" data-track-label="Support" data-track-value="Delete your account" href="javascript:;">Delete your account</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Delete your Sangia Wizdam account (including all related data)</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M3 7H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M5 7H19V20.5C19 21.3284 18.3036 22 17.4444 22H6.55556C5.69645 22 5 21.3284 5 20.5V7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 7V6.16667C8 3.86548 9.79086 2 12 2C14.2091 2 16 3.86548 16 6.16667V7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M10 12V18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14 12V18.0048" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>
        </article></li>
        <li><article class="eds-c-card-composable eds-c-card-composable--faux-link">
            <div class="eds-c-card-composable__container eds-c-card-composable__container--with-sidebar">
                <div class="eds-c-card-composable__body">
                    <div class="eds-c-card-composable__content">
                        <h3 class="eds-c-card-composable__title"><a data-track="click_manage_your_account_item" data-track-context="my-profile_manage-your-account" data-track-label="Support" data-track-value="Linked institutions" href="javascript:;">Linked institutions</a>
                        </h3>
                        <p class="eds-c-card-composable__summary">Link an institution to your account to enable off-site access to subscription content</p>
                    </div>
                </div>
                <div class="eds-c-card-composable__icon-container"><svg class="eds-c-card-composable__icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M15.4207 14.6309L21.0001 17.0483L15.4207 19.4656L9.84131 17.0483L15.4207 14.6309Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12.3726 18.1511L12.3781 20.7814C12.3781 20.7814 13.2992 22.0005 15.4202 22.0005C17.5411 22.0005 18.4672 20.7814 18.4672 20.7814L18.4666 18.1511" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.84131 21.3009V17.0486" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.1838 10.4709C17.2153 10.1902 17.2313 9.90479 17.2313 9.61566C17.2313 5.40964 13.8217 2 9.61566 2C5.40964 2 2 5.40964 2 9.61566C2 12.3964 3.4904 14.8291 5.71596 16.1585" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2 9.61572H17.2313" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12.4627 10.5854C12.5028 10.2668 12.5312 9.94597 12.5479 9.62356C12.4039 6.83569 11.377 4.16585 9.61573 2C7.85448 4.16585 6.82761 6.83569 6.68359 9.62356C6.7414 10.7427 6.94149 11.8428 7.27424 12.8989" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
            </div>
        </article></li>
    </ul>
</section>
    
{include file="common/footer-parts/footer-admin.tpl"}
