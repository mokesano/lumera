{**
 * plugins/themes/sangiapub/templates/author/submit/step3.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Step 3 dari wizard submit yang DIRESTRUKTURISASI --
 * Deklarasi: Submission Checklist + Copyright Notice Agreement (dari
 * step1.tpl LAMA), PLUS tiga deklarasi baru level artikel -- Competing
 * Interest, Ethical Approval, Declaration of Generative AI.
 *
 * Ketiganya level artikel (SATU pernyataan mencakup seluruh penulis),
 * BUKAN per-penulis -- lihat AuthorSubmitStep3Form.inc.php untuk
 * penjelasan lengkap riset yang mendasari keputusan ini.
 *}
{assign var="pageTitle" value="author.submit.step3"}
{include file="author/submit/submitHeader.tpl"}

<div class="separator"></div>

<form id="submit" method="post" action="{url op="saveSubmit" path=$submitStep}" onsubmit="return checkSubmissionChecklist()">
    <input value="{$csrfToken|escape}" name="csrfToken" type="hidden" />
    <input type="hidden" name="articleId" value="{$articleId|escape}" />
    {include file="common/formErrors.tpl"}

	<script type="text/javascript">
	{literal}
	<!--
	function checkSubmissionChecklist() {
		var elements = document.getElementById('submit').elements;
		for (var i=0; i < elements.length; i++) {
			if (elements[i].type == 'checkbox' && !elements[i].checked) {
				if (elements[i].name.match('^checklist')) {
					alert({/literal}'{translate|escape:"jsparam" key="author.submit.verifyChecklist"}'{literal});
					return false;
				} else if (elements[i].name == 'copyrightNoticeAgree') {
					alert({/literal}'{translate|escape:"jsparam" key="author.submit.copyrightNoticeAgreeRequired"}'{literal});
					return false;
				}
			}
		}
		return true;
	}
	// -->
	{/literal}
	</script>

	{if $currentJournal->getLocalizedSetting('submissionChecklist')}
		{foreach name=checklist from=$currentJournal->getLocalizedSetting('submissionChecklist') key=checklistId item=checklistItem}
			{if $checklistItem.content}
				{if !$notFirstChecklistItem}
					{assign var=notFirstChecklistItem value=1}
					<div id="checklist" class="block">
					<h3>{translate key="author.submit.submissionChecklist"}</h3>
					<p class="alert-text">{translate key="author.submit.submissionChecklistDescription"}</p>
					<table width="100%" class="alt-color sort checklist">
				{/if}
				<tr valign="top">
					<td width="5%"><input type="checkbox" id="checklist-{$smarty.foreach.checklist.iteration}" name="checklist[]" value="{$checklistId|escape}"{if $submissionChecklist} checked="checked"{/if} /></td>
					<td width="95%"><label for="checklist-{$smarty.foreach.checklist.iteration}">{$checklistItem.content|nl2br}</label></td>
				</tr>
			{/if}
		{/foreach}
		{if $notFirstChecklistItem}
			</table>
			</div>{* checklist *}
			<div class="separator"></div>
		{/if}
	{/if}

	{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
	<div id="copyrightNotice" class="block">
		<h3>{translate key="about.copyrightNotice"}</h3>

		<div class="alert-text">{$currentJournal->getLocalizedSetting('copyrightNotice')|nl2br}</div>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="5%"><input type="checkbox" name="copyrightNoticeAgree" id="copyrightNoticeAgree" value="1"{if $copyrightNoticeAgree} checked="checked"{/if} /></td>
				<td width="95%"><label for="copyrightNoticeAgree">{translate key="author.submit.copyrightNoticeAgree"}</label></td>
			</tr>
		</table>
	</div>{* copyrightNotice *}

	<div class="separator"></div>
	{/if}{* $currentJournal->getLocalizedSetting('copyrightNotice') != '' *}

	<div id="competingInterest" class="block">
		<h3>{translate key="author.submit.competingInterest"}</h3>
		<p class="alert-text">{translate key="author.submit.competingInterestDescription"}</p>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="competingInterest" required="true" key="author.submit.competingInterestLabel"}</td>
				<td width="80%" class="value"><textarea name="competingInterest[{$formLocale|escape}]" id="competingInterest" class="textArea" rows="6" cols="60">{$competingInterest[$formLocale]|escape}</textarea></td>
			</tr>
		</table>
	</div>

	<div class="separator"></div>

	<div id="ethicalApproval" class="block">
		<h3>{translate key="author.submit.ethicalApproval"}</h3>
		<p class="alert-text">{translate key="author.submit.ethicalApprovalDescription"}</p>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="ethicalApproval" required="true" key="author.submit.ethicalApprovalLabel"}</td>
				<td width="80%" class="value"><textarea name="ethicalApproval[{$formLocale|escape}]" id="ethicalApproval" class="textArea" rows="6" cols="60">{$ethicalApproval[$formLocale]|escape}</textarea></td>
			</tr>
		</table>
	</div>

	<div class="separator"></div>

	<div id="generativeAiDeclaration" class="block">
		<h3>{translate key="author.submit.generativeAiDeclaration"}</h3>
		<p class="alert-text">{translate key="author.submit.generativeAiDeclarationDescription"}</p>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="generativeAiDeclaration" required="true" key="author.submit.generativeAiDeclarationLabel"}</td>
				<td width="80%" class="value"><textarea name="generativeAiDeclaration[{$formLocale|escape}]" id="generativeAiDeclaration" class="textArea" rows="6" cols="60">{$generativeAiDeclaration[$formLocale]|escape}</textarea></td>
			</tr>
		</table>
	</div>

	<div class="separator"></div>

	<div id="privacyStatement" class="block">
		<h3>{translate key="author.submit.privacyStatement"}</h3>
		{$currentJournal->getLocalizedSetting('privacyStatement')|nl2br}
	</div>

	<div class="separator"></div>

	<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer-parts/footer-user.tpl"}
