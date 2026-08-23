{**
 * plugins/themes/sangiapub/templates/author/submit/step4.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Step 4 dari wizard submit yang DIRESTRUKTURISASI --
 * GABUNGAN upload naskah utama (step2.tpl LAMA) DAN daftar file
 * pendukung (step4.tpl LAMA) dalam satu form/halaman. Pengelolaan
 * metadata per-file pendukung (tambah/edit/hapus) TETAP lewat
 * AuthorSubmitSuppFileForm terpisah (op submitSuppFile/
 * saveSubmitSuppFile/deleteSubmitSuppFile, TIDAK berubah).
 *}
{assign var="pageTitle" value="author.submit.step4"}
{include file="author/submit/submitHeader.tpl"}

<script type="text/javascript">
{literal}
<!--
function confirmForgottenUpload() {
	var fieldValue = document.getElementById('submit').uploadSuppFile.value;
	if (fieldValue) {
		return confirm("{/literal}{translate key="author.submit.forgottenSubmitSuppFile"}{literal}");
	}
	return true;
}
// -->
{/literal}
</script>

<div class="separator"></div>

<form id="submit" method="post" action="{url op="saveSubmit" path=$submitStep}" enctype="multipart/form-data">
	<input value="{$csrfToken|escape}" name="csrfToken" type="hidden" />
	<input type="hidden" name="articleId" value="{$articleId|escape}" />
	{include file="common/formErrors.tpl"}

	{if $journalSettings.supportPhone}
		{assign var="howToKeyName" value="author.submit.howToSubmit"}
	{else}
		{assign var="howToKeyName" value="author.submit.howToSubmitNoPhone"}
	{/if}

	<div id="uploadInstructions" class="block alert-text">{translate key="author.submit.uploadInstructions"}</div>

	<p class="info-message u-mb-16">{translate key=$howToKeyName supportName=$journalSettings.supportName supportEmail=$journalSettings.supportEmail supportPhone=$journalSettings.supportPhone}</p>

	<div class="separator"></div>

	{* ===== Bagian 1: Naskah utama (dari step2.tpl LAMA) ===== *}
	<div id="submissionFile" class="block">
		<h3>{translate key="author.submit.submissionFile"}</h3>
		<table class="data" width="100%">
			{if $submissionFile}
				<tr valign="top">
					<td width="20%" class="label">{translate key="common.fileName"}</td>
					<td width="80%" class="value"><a href="{url op="download" path=$articleId|to_array:$submissionFile->getFileId()}">{$submissionFile->getFileName()|escape}</a></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{translate key="common.originalFileName"}</td>
					<td width="80%" class="value">{$submissionFile->getOriginalFileName()|escape}</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{translate key="common.fileSize"}</td>
					<td width="80%" class="value">{$submissionFile->getNiceFileSize()}</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{translate key="common.dateUploaded"}</td>
					<td width="80%" class="value">{$submissionFile->getDateUploaded()|date_format:$datetimeFormatShort}</td>
				</tr>
			{else}
				<tr valign="top">
					<td colspan="2" class="nodata">{translate key="author.submit.noSubmissionFile"}</td>
				</tr>
			{/if}
		</table>
	</div>

	<div class="separator"></div>

	<div id="addSubmissionFile" class="block">
		{if $submissionFile}
			{fieldLabel name="submissionFile" key="author.submit.replaceSubmissionFile"}
		{else}
			{fieldLabel name="submissionFile" key="author.submit.uploadSubmissionFile"}
		{/if}
		<input type="file" class="uploadField" name="submissionFile" id="submissionFile" />
		<input name="uploadSubmissionFile" type="submit" class="button" value="{translate key="common.upload"}" />
		{if $currentJournal->getSetting('showEnsuringLink')}
			<div><a class="action" href="javascript:openHelp('{get_help_id key="editorial.sectionEditorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.article.ensuringBlindReview"}</a></div>
		{/if}
	</div>

	<div class="separator"></div>

	{* ===== Bagian 2: File pendukung (dari step4.tpl LAMA) ===== *}
	<div id="supplementaryFiles" class="block">
		<h3>{translate key="author.submit.supplementaryFiles"}</h3>
		<div class="alert-text">{translate key="author.submit.supplementaryFilesInstructions"}</div>

		<table class="listing" width="100%">
		<tr>
			<td colspan="5" class="headseparator">&nbsp;</td>
		</tr>
		<tr class="heading" valign="bottom">
			<td width="5%">{translate key="common.id"}</td>
			<td width="40%">{translate key="common.title"}</td>
			<td width="25%">{translate key="common.originalFileName"}</td>
			<td width="15%" class="nowrap">{translate key="common.dateUploaded"}</td>
			<td width="15%" align="right">{translate key="common.action"}</td>
		</tr>
		<tr>
			<td colspan="6" class="headseparator">&nbsp;</td>
		</tr>
		{foreach from=$suppFiles item=file}
		<tr valign="top">
			<td>{$file->getId()}</td>
			<td>{$file->getSuppFileTitle()|escape}</td>
			<td>{$file->getOriginalFileName()|escape}</td>
			<td>{$file->getDateSubmitted()|date_format:$dateFormatTrunc}</td>
			<td align="right"><a href="{url op="submitSuppFile" path=$file->getId() articleId=$articleId}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSubmitSuppFile" path=$file->getId() articleId=$articleId}" onclick="return confirm('{translate|escape:"jsparam" key="author.submit.confirmDeleteSuppFile"}')" class="action">{translate key="common.delete"}</a></td>
		</tr>
		{foreachelse}
		<tr valign="top">
			<td colspan="6" class="nodata">{translate key="author.submit.noSupplementaryFiles"}</td>
		</tr>
		{/foreach}
		</table>

		<div class="separator"></div>

		{fieldLabel name="uploadSuppFile" key="author.submit.uploadSuppFile"}
		<input type="file" name="uploadSuppFile" id="uploadSuppFile" class="uploadField" /> <input name="submitUploadSuppFile" type="submit" class="button" value="{translate key="common.upload"}" />
		{if $currentJournal->getSetting('showEnsuringLink')}
		<div><a class="action" href="javascript:openHelp('{get_help_id key="editorial.sectionEditorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.article.ensuringBlindReview"}</a></div>
		{/if}
	</div>

	<div class="separator"></div>

	{if !$submissionFile}
		<input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" />
	{else}
		<input type="submit" onclick="return confirmForgottenUpload()" value="{translate key="common.saveAndContinue"}" class="button defaultButton" />
		<input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" />
	{/if}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer-parts/footer-user.tpl"}
