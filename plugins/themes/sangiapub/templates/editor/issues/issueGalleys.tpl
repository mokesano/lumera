{**
 * templates/editor/issues/issueGalleys.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for uploading and editing issue galleys
 *}
{strip}
{assign var="pageTitleTranslated" value=$issue->getIssueIdentification()}
{assign var="pageCrumbTitleTranslated" value=$issue->getIssueIdentification(false,true)}
{include file="common/header-ROLE.tpl"}
{/strip}

{if !$isLayoutEditor}{* Layout Editors can also access this page. *}
	<ul class="menu">
		<li><a href="{url op="createIssue"}">{translate key="editor.navigation.createIssue"}</a></li>
		<li{if $unpublished} class="current"{/if}><a href="{url op="futureIssues"}">{translate key="editor.navigation.futureIssues"}</a></li>
		<li{if !$unpublished} class="current"{/if}><a href="{url op="backIssues"}">{translate key="editor.navigation.issueArchive"}</a></li>
	</ul>
{/if}
<br />

<form action="#">
{translate key="issue.issue"}: <select name="issue" class="selectMenu" onchange="if(this.options[this.selectedIndex].value > 0) location.href='{url|escape:"javascript" op="issueToc" path="ISSUE_ID" escape=false}'.replace('ISSUE_ID', this.options[this.selectedIndex].value)" size="1">{html_options options=$issueOptions selected=$issueId}</select>
</form>

<div class="separator"></div>

<ul class="menu">
	<li><a href="{url op="issueToc" path=$issueId}">{translate key="issue.toc"}</a></li>
	<li><a href="{url op="issueData" path=$issueId}">{translate key="editor.issues.issueData"}</a></li>
	<li class="current"><a href="{url op="issueGalleys" path=$issueId}">{translate key="editor.issues.galleys"}</a></li>
	{if $unpublished}<li><a href="{url page="issue" op="view" path=$issue->getBestIssueId()}" target="_blank">{translate key="editor.issues.previewIssue"}</a></li>{/if}
</ul>

<form id="issueGalleys" method="post" action="{url op="uploadIssueGalley" path=$issueId}" enctype="multipart/form-data">
    <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
{include file="common/formErrors.tpl"}
<div id="issueId">
<h3>{translate key="editor.issues.galleys"}</h3>
<p>{translate key="editor.issues.issueGalleysDescription"}</p>
<table width="100%" class="data">
{if is_array($formLocales) && count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"issueUrl" op="issueGalleys" path=$issueId escape=false}
			{form_language_chooser form="issue" url=$issueUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
</table>

{foreach name=galleys from=$issueGalleys item=galley}
<table width="100%" class="info">
	<tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" class="heading">{translate key="submission.layout.galleyFormat"}</td>
		<td class="heading">{translate key="common.file"}</td>
		<td class="heading">{translate key="common.order"}</td>
		<td class="heading">{translate key="common.action"}</td>
		<td class="heading">{translate key="submission.views"}</td>
	</tr>
	<tr>
		<td width="2%">{$smarty.foreach.galleys.iteration}.</td>
		<td width="26%">{$galley->getGalleyLabel()|escape} &nbsp; <a href="{url op="proofIssueGalley" path=$issue->getId()|to_array:$galley->getId()}" class="action" target="_blank">{translate key="submission.layout.viewProof"}</a></td>
		<td><a href="{url op="downloadIssueFile" path=$issue->getId()|to_array:$galley->getFileId()}" class="file">{$galley->getFileName()|escape}</a>&nbsp;&nbsp;{$galley->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderIssueGalley" d=u issueId=$issue->getId() galleyId=$galley->getId()}" class="plain">&uarr;</a> <a href="{url op="orderIssueGalley" d=d issueId=$issue->getId() galleyId=$galley->getId()}" class="plain">&darr;</a></td>
		<td>
			<a href="{url op="editIssueGalley" path=$issue->getId()|to_array:$galley->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteIssueGalley" path=$issue->getId()|to_array:$galley->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="editor.issues.confirmDeleteGalley"}')" class="action">{translate key="common.delete"}</a>
		</td>
		<td>{$galley->getViews()|escape}</td>
	</tr>
	<tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
</table>

{foreachelse}
<article id="articles_issue" itemtype="http://schema.org/ScholarlyArticle">
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5">{translate key="editor.issues.noneIssueGalleys"}</h3>
            <p class="c-empty-state-card__text--description">Browse our <a href="{url page="issue" op="archive"}">Issue Archive</a> to explore previously published research and scholarly articles, or visit the <a href="{url page="issue" op="current"}">{translate key="journal.currentIssue"}</a> to access the latest manuscripts. You can also use the <a href="{url page="search"}">Search</a> function to find specific topics or authors, or check our <a href="{url page="search" op="titles"}">Titles Index</a> for a comprehensive list of all published articles within our journal collection.</p>
        </div>
    </div>
</article>
{/foreach}

	<br />
	<input type="file" name="galleyFile" id="galleyFile" size="10" class="uploadField" />
	<input type="submit" value="{translate key="common.upload"}" class="button" />
</div>
</form>

{include file="common/footer-parts/footer-user.tpl"}
