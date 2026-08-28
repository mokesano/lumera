{**
 * plugins/themes/sangiapub/templates/author/submit/step1.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Step 1 dari wizard submit yang DIRESTRUKTURISASI --
 * Metadata (judul, abstrak, kata kunci, dst) SEKALIGUS pembuatan
 * artikel (section, locale). Ini PENGGABUNGAN section/locale dari
 * step1.tpl LAMA dengan seluruh field metadata dari step3.tpl LAMA --
 * lihat AuthorSubmitStep1Form.inc.php untuk detail lengkap
 * restrukturisasi ini.
 *
 * SENGAJA TIDAK ADA di sini (dipindahkan ke step3.tpl BARU -
 * Deklarasi): submissionChecklist, copyrightNotice.
 * SENGAJA TIDAK ADA di sini (disederhanakan, hanya di step5.tpl -
 * Overview): commentsForEditor.
 *}
{assign var="pageTitle" value="author.submit.step1"}
{include file="author/submit/submitHeader.tpl"}

{if $journalSettings.supportPhone}
	{assign var="howToKeyName" value="author.submit.howToSubmit"}
{else}
	{assign var="howToKeyName" value="author.submit.howToSubmitNoPhone"}
{/if}

<p class="info-message u-mb-16">{translate key=$howToKeyName supportName=$journalSettings.supportName supportEmail=$journalSettings.supportEmail supportPhone=$journalSettings.supportPhone}</p>

<div class="separator"></div>

<form id="submit" method="post" action="{url op="saveSubmit" path=$submitStep}">
    <input value="{$csrfToken|escape}" name="csrfToken" type="hidden" />
    {include file="common/formErrors.tpl"}

	{if $articleId}
		<input type="hidden" name="articleId" value="{$articleId|escape}" />
	{/if}

	{if count($sectionOptions) <= 1}
		<p class="alert-text">{translate key="author.submit.notAccepting"}</p>
	</form>
	{else}

	{if count($sectionOptions) == 2}
		{* If there's only one section, force it and skip the section parts
		of the interface. *}
		{foreach from=$sectionOptions item=val key=key}
			<input type="hidden" name="sectionId" value="{$key|escape}" />
		{/foreach}
	{else}{* if count($sectionOptions) == 2 *}
		<div id="section" class="block">
			<h3>{translate key="author.submit.journalSection"}</h3>
			{url|assign:"url" page="about"}
			<p class="alert-text">{translate key="author.submit.journalSectionDescription" aboutUrl=$url}</p>

			<table class="data" width="100%">
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="sectionId" required="true" key="section.section"}</td>
					<td width="80%" class="value"><select name="sectionId" id="sectionId" size="1" class="selectMenu">{html_options options=$sectionOptions selected=$sectionId}</select></td>
				</tr>
			</table>

		</div>{* section *}

		<div class="separator"></div>
	{/if}{* if count($sectionOptions) == 2 *}

	<div id="articleType" class="block">
		<h3>{translate key="article.type.label"}</h3>
		<p class="alert-text">{translate key="article.type.description"}</p>

		<table class="data" width="100%">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="articleTypeChoice" key="article.type.label"}</td>
				<td width="80%" class="value"><select name="articleTypeChoice" id="articleTypeChoice" size="1" class="selectMenu">{html_options options=$articleTypeOptions selected=$articleTypeChoice}</select></td>
			</tr>
		</table>
	</div>{* articleType *}

	<div class="separator"></div>

	{if count($supportedSubmissionLocaleNames) == 1}
		{* There is only one supported submission locale; choose it invisibly *}
		{foreach from=$supportedSubmissionLocaleNames item=localeName key=locale}
			<input type="hidden" name="locale" value="{$locale|escape}" />
		{/foreach}
	{else}
		{* There are several submission locales available; allow choice *}
		<div id="submissionLocale" >
			<h3>{translate key="author.submit.submissionLocale"}</h3>
			<p class="alert-text">{translate key="author.submit.submissionLocaleDescription"}</p>

			<table class="data" width="100%">
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="locale" required="true" key="article.language"}</td>
					<td width="80%" class="value"><select name="locale" id="locale" size="1" class="selectMenu">{html_options options=$supportedSubmissionLocaleNames selected=$locale}</select></td>
				</tr>
			</table>

			<div class="separator"></div>

		</div>{* submissionLocale *}
	{/if}{* count($supportedSubmissionLocaleNames) == 1 *}

	<div id="titleAndAbstract" class="block">
		<h3>{translate key="submission.titleAndAbstract"}</h3>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="title" required="true" key="article.title"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="title[{$formLocale|escape}]" id="title" value="{$title[$formLocale]|escape}" size="60" maxlength="255" /></td>
			</tr>

			<tr valign="top">
				<td width="20%" class="label">{if $section && $section->getAbstractsNotRequired()==0}{fieldLabel name="abstract" key="article.abstract" required="true"}{else}{fieldLabel name="abstract" key="article.abstract"}{/if}</td>
				<td width="80%" class="value"><textarea name="abstract[{$formLocale|escape}]" id="abstract" class="textArea" rows="15" cols="60">{$abstract[$formLocale]|escape}</textarea></td>
			</tr>
		</table>
	</div>

	<div class="separator"></div>

	{if $section && $section->getMetaIndexed()==1}
		<div id="indexing" class="block">
			<h3>{translate key="submission.indexing"}</h3>
			{if $currentJournal->getSetting('metaDiscipline') || $currentJournal->getSetting('metaSubjectClass') || $currentJournal->getSetting('metaSubject') || $currentJournal->getSetting('metaCoverage') || $currentJournal->getSetting('metaType')}<p class="alert-text">{translate key="author.submit.submissionIndexingDescription"}</p>{/if}
			<table width="100%" class="data">
				{if $currentJournal->getSetting('metaDiscipline')}
					<tr valign="top">
						<td{if $currentJournal->getLocalizedSetting('metaDisciplineExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="discipline" key="article.discipline"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="discipline[{$formLocale|escape}]" id="discipline" value="{$discipline[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					{if $currentJournal->getLocalizedSetting('metaDisciplineExamples')}
						<tr valign="top">
							<td><span class="instruct">{$currentJournal->getLocalizedSetting('metaDisciplineExamples')|escape}</span></td>
						</tr>
					{/if}
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				{/if}

				{if $currentJournal->getSetting('metaSubjectClass')}
					<tr valign="top">
						<td rowspan="2" width="20%" class="label">{fieldLabel name="subjectClass" key="article.subjectClassification"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="subjectClass[{$formLocale|escape}]" id="subjectClass" value="{$subjectClass[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					<tr valign="top">
						<td width="20%" class="label"><a href="{$currentJournal->getLocalizedSetting('metaSubjectClassUrl')|escape}" target="_blank">{$currentJournal->getLocalizedSetting('metaSubjectClassTitle')|escape}</a></td>
					</tr>
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				{/if}

				{if $currentJournal->getSetting('metaSubject')}
					<tr valign="top">
						<td{if $currentJournal->getLocalizedSetting('metaSubjectExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="subject" key="article.subject"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="subject[{$formLocale|escape}]" id="subject" value="{$subject[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					{if $currentJournal->getLocalizedSetting('metaSubjectExamples') != ''}
						<tr valign="top">
							<td><span class="instruct">{$currentJournal->getLocalizedSetting('metaSubjectExamples')|escape}</span></td>
						</tr>
					{/if}
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				{/if}

				{if $currentJournal->getSetting('metaCoverage')}
					<tr valign="top">
						<td{if $currentJournal->getLocalizedSetting('metaCoverageGeoExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="coverageGeo" key="article.coverageGeo"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="coverageGeo[{$formLocale|escape}]" id="coverageGeo" value="{$coverageGeo[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					{if $currentJournal->getLocalizedSetting('metaCoverageGeoExamples')}
						<tr valign="top">
							<td><span class="instruct">{$currentJournal->getLocalizedSetting('metaCoverageGeoExamples')|escape}</span></td>
						</tr>
					{/if}
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr valign="top">
						<td{if $currentJournal->getLocalizedSetting('metaCoverageChronExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="coverageChron" key="article.coverageChron"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="coverageChron[{$formLocale|escape}]" id="coverageChron" value="{$coverageChron[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					{if $currentJournal->getLocalizedSetting('metaCoverageChronExamples') != ''}
						<tr valign="top">
							<td><span class="instruct">{$currentJournal->getLocalizedSetting('metaCoverageChronExamples')|escape}</span></td>
						</tr>
					{/if}
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr valign="top">
						<td{if $currentJournal->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="coverageSample" key="article.coverageSample"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="coverageSample[{$formLocale|escape}]" id="coverageSample" value="{$coverageSample[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					{if $currentJournal->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''}
						<tr valign="top">
							<td><span class="instruct">{$currentJournal->getLocalizedSetting('metaCoverageResearchSampleExamples')|escape}</span></td>
						</tr>
					{/if}
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				{/if}

				{if $currentJournal->getSetting('metaType')}
					<tr valign="top">
						<td width="20%" {if $currentJournal->getLocalizedSetting('metaTypeExamples') != ''}rowspan="2" {/if}class="label">{fieldLabel name="type" key="article.type"}</td>
						<td width="80%" class="value"><input type="text" class="textField" name="type[{$formLocale|escape}]" id="type" value="{$type[$formLocale]|escape}" size="40" maxlength="255" /></td>
					</tr>
					{if $currentJournal->getLocalizedSetting('metaTypeExamples') != ''}
						<tr valign="top">
							<td><span class="instruct">{$currentJournal->getLocalizedSetting('metaTypeExamples')|escape}</span></td>
						</tr>
					{/if}
					<tr valign="top">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				{/if}

				<tr valign="top">
					<td rowspan="2" width="20%" class="label">{fieldLabel name="language" key="article.language"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="language" id="language" value="{$language|escape}" size="5" maxlength="10" /></td>
				</tr>
				<tr valign="top">
					<td><span class="instruct">{translate key="author.submit.languageInstructions"}</span></td>
				</tr>
			</table>
		</div>

		<div class="separator"></div>

	{/if}

	<div id="submissionSupportingAgencies" class="block">
		<h3>{translate key="author.submit.submissionSupportingAgencies"}</h3>
		<p class="alert-text">{translate key="author.submit.submissionSupportingAgenciesDescription"}</p>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="sponsor" key="submission.agencies"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="sponsor[{$formLocale|escape}]" id="sponsor" value="{$sponsor[$formLocale]|escape}" size="60" maxlength="255" /></td>
			</tr>
		</table>
	</div>

	<div class="separator"></div>

	{if $currentJournal->getSetting('metaCitations')}
	<div id="metaCitations" class="block">
		<h3>{translate key="submission.citations"}</h3>
		<p class="alert-text">{translate key="author.submit.submissionCitations"}</p>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="citations" key="submission.citations"}</td>
				<td width="80%" class="value"><textarea name="citations" id="citations" class="textArea" rows="15" cols="60">{$citations|escape}</textarea></td>
			</tr>
		</table>

		<div class="separator"></div>

	</div>
	{/if}

	<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="{if $articleId}confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}'){else}document.location.href='{url page="author" escape=false}'{/if}" /></p>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{/if}{* If not accepting submissions *}

{include file="common/footer-parts/footer-user.tpl"}