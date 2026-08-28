{**
 * plugins/themes/sangiapub/templates/sectionEditor/articleTypes/sectionArticleTypes.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Form checkbox aktif/nonaktif tipe artikel BAKU untuk SATU
 * Section tertentu -- Section Editor HANYA bisa mempersempit dari yang
 * sudah aktif di level jurnal, TIDAK bisa mengaktifkan kembali yang
 * sudah dinonaktifkan Journal Manager (lihat
 * SectionEditorArticleTypeHandler::sectionArticleTypes() +
 * ArticleTypeAvailabilityDAO.inc.php untuk penjelasan hierarki
 * lengkap).
 *}
{strip}
	{assign var="pageTitle" value="article.type.configureSection"}
	{include file="common/header-ROLE.tpl"}
{/strip}

<h3>{$section->getLocalizedTitle()|escape}</h3>

<div id="sectionArticleTypeAvailability" class="block">
	<p class="instruct">{translate key="article.type.availability.sectionDescription"}</p>

	<form id="sectionArticleTypesForm" method="post" action="{url op="saveSectionArticleTypes"}">
		<input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
		<input type="hidden" name="sectionId" value="{$section->getId()|escape}" />

		<table width="100%" class="listing">
			<tr>
				<td class="headseparator" colspan="2">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td width="10%">{translate key="article.type.availability.enabled"}</td>
				<td width="90%">{translate key="article.type.label"}</td>
			</tr>
			<tr>
				<td class="headseparator" colspan="2">&nbsp;</td>
			</tr>
			{foreach from=$journalEnabledTypes item=typeCode}
				<tr valign="top" class="data">
					<td><input type="checkbox" name="enabledTypes[]" id="enabledSectionType-{$typeCode|escape}" value="{$typeCode|escape}"{if !in_array($typeCode, $disabledSectionTypes)} checked="checked"{/if} /></td>
					<td><label for="enabledSectionType-{$typeCode|escape}">{translate key="article.type.standard.`$typeCode`"}</label></td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="2" class="nodata">{translate key="article.type.customType.noneCreated"}</td>
				</tr>
			{/foreach}
			<tr>
				<td colspan="2" class="endseparator">&nbsp;</td>
			</tr>
		</table>

		<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="mySections" escape=false}'" /></p>
	</form>
</div>

{if $journalDisabledTypes}
	<div class="separator"></div>

	<div id="sectionArticleTypeDisabledAtJournal" class="block">
		<h3>{translate key="article.type.availability.disabledAtJournalLevel.title"}</h3>
		<p class="instruct">{translate key="article.type.availability.disabledAtJournalLevel"}</p>

		<table width="100%" class="listing">
			<tr>
				<td class="headseparator">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td>{translate key="article.type.label"}</td>
			</tr>
			<tr>
				<td class="headseparator">&nbsp;</td>
			</tr>
			{foreach from=$journalDisabledTypes item=typeCode}
				<tr valign="top" class="data">
					<td>{translate key="article.type.standard.`$typeCode`"}</td>
				</tr>
			{/foreach}
			<tr>
				<td class="endseparator">&nbsp;</td>
			</tr>
		</table>
	</div>
{/if}

{include file="common/footer-parts/footer-user.tpl"}