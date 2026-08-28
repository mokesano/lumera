{**
 * plugins/themes/sangiapub/templates/manager/articleTypes/articleTypes.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Halaman Journal Manager untuk mengatur ketersediaan tipe
 * artikel BAKU level jurnal + CRUD tipe artikel KUSTOM. Pola mengikuti
 * manager/sections/sections.tpl (drag-reorder tipe kustom) DITAMBAH
 * satu form checkbox terpisah untuk availability tipe baku -- lihat
 * ArticleTypeCustomHandler.inc.php untuk data yang diinjeksikan ke sini.
 *}
{strip}
	{assign var="pageTitle" value="article.type.articleTypes"}
	{include file="common/header-USER027.tpl"}
{/strip}

<script type="text/javascript">
	{literal}
		$(document).ready(function() { setupTableDND("#dragTable", "moveArticleType"); });
	{/literal}
</script>

<div id="articleTypeAvailability" class="block">
	<h3>{translate key="article.type.availability"}</h3>
	<p class="instruct">{translate key="article.type.availability.journalDescription"}</p>

	<form id="articleTypeAvailabilityForm" method="post" action="{url op="saveArticleTypeAvailability"}">
		<input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />

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
			{foreach from=$standardPublicTypes item=typeCode}
				<tr valign="top" class="data">
					<td><input type="checkbox" name="enabledTypes[]" id="enabledType-{$typeCode|escape}" value="{$typeCode|escape}"{if !in_array($typeCode, $disabledJournalTypes)} checked="checked"{/if} /></td>
					<td><label for="enabledType-{$typeCode|escape}">{translate key="article.type.standard.`$typeCode`"}</label></td>
				</tr>
			{/foreach}
			<tr>
				<td colspan="2" class="endseparator">&nbsp;</td>
			</tr>
		</table>

		<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /></p>
	</form>
</div>

<div class="separator"></div>

<div id="articleTypeEditorialOnly" class="block">
	<h3>{translate key="article.type.availability.editorialOnlyReference"}</h3>
	<p class="instruct">{translate key="article.type.availability.editorialOnlyReference.description"}</p>

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
		{foreach from=$standardEditorialOnlyTypes item=typeCode}
			<tr valign="top" class="data">
				<td>{translate key="article.type.standard.`$typeCode`"}</td>
			</tr>
		{/foreach}
		<tr>
			<td class="endseparator">&nbsp;</td>
		</tr>
	</table>
</div>

<div class="separator"></div>

<div id="articleTypesCustom" class="block">
	<h3>{translate key="article.type.customType"}</h3>
	<p class="instruct">{translate key="article.type.customType.description"}</p>

	<table width="100%" class="listing" id="dragTable">
		<tr>
			<td class="headseparator" colspan="2">&nbsp;</td>
		</tr>
		<tr class="heading" valign="bottom">
			<td width="85%">{translate key="article.type.form.name"}</td>
			<td width="15%">{translate key="common.action"}</td>
		</tr>
		<tr>
			<td class="headseparator" colspan="2">&nbsp;</td>
		</tr>
		{foreach from=$customTypes item=customType}
			<tr valign="top" id="articleTypeCustom-{$customType->getId()}" class="data">
				<td class="drag">{$customType->getLocalizedName()|escape}</td>
				<td align="right" class="nowrap">
					<a href="{url op="editArticleType" path=$customType->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteArticleType" path=$customType->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="article.type.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveArticleType" d=u id=$customType->getId()}">&uarr;</a>&nbsp;<a href="{url op="moveArticleType" d=d id=$customType->getId()}">&darr;</a>
				</td>
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
	<a class="button" href="{url op="createArticleType"}">{translate key="article.type.customType.create"}</a>
</div>

{include file="common/footer-parts/footer-user.tpl"}