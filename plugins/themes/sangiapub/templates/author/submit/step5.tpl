{**
 * plugins/themes/sangiapub/templates/author/submit/step5.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Step 5 dari wizard submit yang DIRESTRUKTURISASI --
 * Overview (ringkasan Step 1-4) + tombol Submit + "Save to Draft".
 *
 * "Save to Draft" murni link biasa (BUKAN form submit) ke daftar
 * submission penulis -- TIDAK memanggil execute()/finalisasi apa pun,
 * sesuai keputusan eksplisit (artikel sudah otomatis tersimpan
 * bertahap di setiap step, bisa ditinggal & dilanjutkan kapan saja).
 *
 * commentsToEditor SEKARANG SELALU muncul di sini TANPA SYARAT
 * (sebelumnya, di form lama, textarea ini HANYA muncul di dalam blok
 * $authorFees+waiverPolicy -- kalau jurnal tidak mengaktifkan biaya,
 * field ini TIDAK PERNAH tampil ke user sama sekali). Diperbaiki
 * sekalian karena field ini sekarang jadi SATU-SATUNYA tempat mengisi
 * komentar untuk editor (dihapus dari Step 1 lama).
 *}
{assign var="pageTitle" value="author.submit.step5"}
{include file="author/submit/submitHeader.tpl"}

<div class="alert-text">{translate key="author.submit.confirmationDescription" journalTitle=$journal->getLocalizedTitle()}</div>

<div class="separator"></div>

<form id="submit" method="post" action="{url op="saveSubmit" path=$submitStep}">
	<input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
	<input type="hidden" name="articleId" value="{$articleId|escape}" />
	{include file="common/formErrors.tpl"}

	{* ===== Overview: ringkasan Step 1-4 ===== *}
	<div id="overview" class="block">
		<h3>{translate key="author.submit.overview"}</h3>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{translate key="article.title"}</td>
				<td width="80%" class="value">{$overviewTitle|escape|default:"-"}</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{translate key="article.abstract"}</td>
				<td width="80%" class="value">{if $overviewAbstract}{$overviewAbstract|strip_tags|escape}{else}-{/if}</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{translate key="article.authors"}</td>
				<td width="80%" class="value">
					{if $overviewAuthors}
						{foreach from=$overviewAuthors item=overviewAuthor name=overviewAuthorsList}
							{$overviewAuthor->getFullName()|escape}{if !$smarty.foreach.overviewAuthorsList.last}; {/if}
						{foreachelse}
							-
						{/foreach}
					{else}
						-
					{/if}
				</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{translate key="author.submit.funders"}</td>
				<td width="80%" class="value">
					{if $overviewFunders}
						<ul class="overviewList">
						{foreach from=$overviewFunders item=overviewFunder}
							<li>{$overviewFunder->getFunderName()|escape}{if $overviewFunder->getAwardNumber()} ({$overviewFunder->getAwardNumber()|escape}){/if}</li>
						{/foreach}
						</ul>
					{else}
						{translate key="author.submit.noFunders"}
					{/if}
				</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{translate key="author.submit.competingInterest"}</td>
				<td width="80%" class="value">{if $overviewCompetingInterest}{$overviewCompetingInterest|nl2br}{else}-{/if}</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{translate key="author.submit.ethicalApproval"}</td>
				<td width="80%" class="value">{if $overviewEthicalApproval}{$overviewEthicalApproval|nl2br}{else}-{/if}</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{translate key="author.submit.generativeAiDeclaration"}</td>
				<td width="80%" class="value">{if $overviewGenerativeAiDeclaration}{$overviewGenerativeAiDeclaration|nl2br}{else}-{/if}</td>
			</tr>
		</table>
	</div>

	<div class="separator"></div>

	<h3>{translate key="author.submit.filesSummary"}</h3>
	<table class="listing" width="100%">
		<tr>
			<td colspan="5" class="headseparator">&nbsp;</td>
		</tr>
		<tr class="heading" valign="bottom">
			<td width="5%">{translate key="common.id"}</td>
			<td width="55%">{translate key="common.originalFileName"}</td>
			<td width="17%">{translate key="common.type"}</td>
			<td width="10%" class="nowrap">{translate key="common.fileSize"}</td>
			<td width="8%" class="nowrap">{translate key="common.dateUploaded"}</td>
		</tr>
		<tr>
			<td colspan="5" class="headseparator">&nbsp;</td>
		</tr>
		{foreach from=$files item=file}
		<tr valign="top">
			<td>{$file->getFileId()}</td>
			<td><a class="file" href="{url op="download" path=$articleId|to_array:$file->getFileId()}">{$file->getOriginalFileName()|escape}</a></td>
			<td>{if ($file->getFileStage() == ARTICLE_FILE_SUPP)}{translate key="article.suppFile"}{else}{translate key="author.submit.submissionFile"}{/if}</td>
			<td>{$file->getNiceFileSize()}</td>
			<td>{$file->getDateUploaded()|date_format:$dateFormatTrunc}</td>
		</tr>
		{foreachelse}
			<tr valign="top">
				<td colspan="5" class="nodata">{translate key="author.submit.noFiles"}</td>
			</tr>
		{/foreach}
	</table>

	<div class="separator"></div>

	{if $authorFees}
		{include file="author/submit/authorFees.tpl" showPayLinks=1}
		{if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
			{if $manualPayment}
				<h3>{translate key="payment.alreadyPaid"}</h3>
				<table class="data" width="100%">
					<tr valign="top">
						<td width="5%" align="left"><input type="checkbox" name="paymentSent" value="1" {if $paymentSent}checked="checked"{/if} /></td>
						<td width="95%">{translate key="payment.paymentSent"}</td>
					</tr>
					<tr>
						<td />
						<td>{translate key="payment.alreadyPaidMessage"}</td>
					</tr>
				</table>
			{/if}
			<h3>{translate key="author.submit.requestWaiver"}</h3>
			<table class="data" width="100%">
				<tr valign="top">
					<td width="5%" align="left"><input type="checkbox" name="qualifyForWaiver" value="1" {if $qualifyForWaiver}checked="checked"{/if}/></td>
					<td width="95%">{translate key="author.submit.qualifyForWaiver"}</td>
				</tr>
			</table>
			<br />
		{/if}

		<div class="separator"></div>
	{/if}

	{* [WIZDAM] commentsToEditor -- SEKARANG SELALU muncul tanpa syarat,
	lihat penjelasan lengkap di docblock atas file ini. *}
	<div id="commentsForEditor" class="block">
		<h4 class="u-h3">{translate key="author.submit.commentsForEditor"}</h4>
		{fieldLabel name="commentsToEditor" key="author.submit.comments"}
		<textarea name="commentsToEditor" id="commentsToEditor" rows="7" cols="40" class="textArea">{$commentsToEditor|escape}</textarea>
	</div>

	<div class="separator"></div>

	{call_hook name="Templates::Author::Submit::Step5::AdditionalItems"}

	<p>
		<input type="submit" value="{translate key="author.submit.finishSubmission"}" class="button defaultButton" />
		{* [WIZDAM] "Save to Draft" -- link biasa, BUKAN submit form, ke
		daftar submission penulis. Artikel sudah tersimpan bertahap,
		tidak butuh aksi simpan khusus. *}
		<a href="{url page="author"}" class="button">{translate key="author.submit.saveToDraft"}</a>
		<input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" />
	</p>

</form>

{include file="common/footer-parts/footer-user.tpl"}
