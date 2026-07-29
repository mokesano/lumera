{**
 * templates/admin/journalSettings.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic journal settings under site administration.
 *
 *}
{strip}
	{assign var="pageTitle" value="admin.journals.journalSettings"}
	{include file="common/header.tpl"}
{/strip}

<br />

<script type="text/javascript">
	{literal}
	<!--
	// Ensure that the form submit button cannot be double-clicked
	function doSubmit() {
		var form = document.querySelector('form#journal');
		
		if (form) {
			var submittedInput = form.querySelector('input[name="submitted"]');
			
			if (submittedInput) {
				if (submittedInput.value != '1') {
					submittedInput.value = '1';
					form.submit();
				}
			} else {
				form.submit();
			}
		} else {
			console.error("Form dengan ID 'journal' tidak ditemukan.");
			alert("Gagal menyimpan: Form tidak terdeteksi. Silakan refresh halaman.");
		}
		return true;
	}
	// -->
	{/literal}
</script>

<form id="journal" method="post" action="{url op="updateJournal"}">
	<input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
	<input type="hidden" name="submitted" value="0" />
	{if $journalId}
		<input type="hidden" name="journalId" value="{$journalId|escape}" />
	{/if}

	{include file="common/formErrors.tpl"}

	{if not $journalId}
		<p><span class="instruct">{translate key="admin.journals.createInstructions"}</span></p>
	{/if}

	<table class="data" width="100%">
	{if count($formLocales) > 1}
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
			<td width="80%" class="value">
				{url|assign:"settingsUrl" op="editJournal" path=$journalId escape=false}
				{form_language_chooser form="journal" url=$settingsUrl}
				<span class="instruct">{translate key="form.formLanguage.description"}</span>
			</td>
		</tr>
	{/if}
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="title" key="manager.setup.journalTitle" required="true"}</td>
			<td width="80%" class="value"><input type="text" id="title" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" size="40" maxlength="120" class="textField" /></td>
		</tr>
		<tr valign="top">
			<td class="label">{fieldLabel name="description" key="admin.journals.journalDescription"}</td>
			<td class="value"><textarea name="description[{$formLocale|escape}]" id="description" cols="40" rows="10" class="textArea">{$description[$formLocale]|escape}</textarea></td>
		</tr>
		<tr valign="top">
			<td class="label">{fieldLabel name="journalPath" key="journal.path" required="true"}</td>
			<td class="value">
				<input type="text" id="journalPath" name="journalPath" value="{$journalPath|escape}" size="16" maxlength="32" class="textField" />
				<br />
				{url|assign:"sampleUrl" journal="path"}
				<span class="instruct">{translate key="admin.journals.urlWillBe" sampleUrl=$sampleUrl}</span>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" class="label">
				<input type="checkbox" name="enabled" id="enabled" value="1"{if $enabled} checked="checked"{/if} /> <label for="enabled">{translate key="admin.journals.enableJournalInstructions"}</label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" class="label">
				<input type="checkbox" name="showOnHomepage" id="showOnHomepage" value="1"{if $showOnHomepage} checked="checked"{/if} />
				<label for="showOnHomepage">{translate key="admin.journals.showOnHomepage"}</label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" class="label">
				<input type="checkbox" name="paymentIndependent" id="paymentIndependent" value="1"{if $paymentIndependent} checked="checked"{/if} /> <label for="paymentIndependent">{translate key="admin.journals.paymentIndependentInstructions"}</label>
			</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{translate key="admin.journals.certificateSignatory.label"}</td>
			<td width="80%" class="value">
				{if !isset($certificateSignatoryManagerCount) || $certificateSignatoryManagerCount == 0}
					<span class="instruct">{translate key="admin.journals.certificateSignatory.noManagers"}</span>

				{elseif $certificateSignatoryManagerCount <= 2}
					<span class="instruct">{translate key="admin.journals.certificateSignatory.autoInfoIntro"}</span>
					<div style="margin-top:10px;">
						{foreach from=$certificateSignatoryCandidates item=candidate}
							<div style="display:flex; align-items:center; gap:10px; border:1px solid #eee; border-radius:4px; padding:8px; margin-bottom:6px;">
								<img src="{$candidate.photoUrl|escape}" alt="" width="40" height="40" style="border-radius:50%; object-fit:cover;">
								<div>
									<strong>{$candidate.name|escape}</strong>
									<span style="color:#666; font-size:0.9em;"> &mdash; @{$candidate.username|escape}</span><br>
									<span style="color:#666; font-size:0.9em;">{$candidate.email|escape}{if $candidate.affiliation} &bull; {$candidate.affiliation|escape}{/if}</span>
								</div>
							</div>
						{/foreach}
					</div>

				{else}
					<span class="instruct">{translate key="admin.journals.certificateSignatory.selectInstructions"}</span>
					<div style="margin-top:12px;">
						{foreach from=$certificateSignatoryCandidates item=candidate}
							<label style="display:flex; align-items:center; gap:10px; border:1px solid #ddd; border-radius:4px; padding:10px; margin-bottom:8px; cursor:pointer;">
								<input type="radio" name="certificateSignatoryUserId" value="{$candidate.id}"{if $certificateSignatoryUserId == $candidate.id} checked="checked"{/if} />
								<img src="{$candidate.photoUrl|escape}" alt="" width="40" height="40" style="border-radius:50%; object-fit:cover;">
								<div>
									<strong>{$candidate.name|escape}</strong>
									<span style="color:#666; font-size:0.9em;"> &mdash; @{$candidate.username|escape}</span><br>
									<span style="color:#666; font-size:0.9em;">{$candidate.email|escape}{if $candidate.affiliation} &bull; {$candidate.affiliation|escape}{/if}</span>
								</div>
							</label>
						{/foreach}
					</div>
				{/if}
			</td>
		</tr>
	</table>

	<p>
		<input type="button" id="saveJournal" value="{translate key="common.save"}" class="button defaultButton" onclick="doSubmit()" />
		<input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="journals" escape=false}'" />
	</p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
