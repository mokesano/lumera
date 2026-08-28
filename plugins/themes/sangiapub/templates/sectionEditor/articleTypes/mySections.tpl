{**
 * plugins/themes/sangiapub/templates/sectionEditor/articleTypes/mySections.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Daftar Section yang jadi tanggung jawab Section Editor yang
 * sedang login, dengan tautan ke halaman pengaturan tipe artikel
 * masing-masing -- lihat SectionEditorArticleTypeHandler::mySections().
 *}
{strip}
	{assign var="pageTitle" value="article.type.mySections"}
	{include file="common/header-ROLE.tpl"}
{/strip}

<p class="instruct">{translate key="article.type.mySections.description"}</p>

<div id="mySections" class="block">
	<table width="100%" class="listing">
		<tr>
			<td class="headseparator" colspan="2">&nbsp;</td>
		</tr>
		<tr class="heading" valign="bottom">
			<td width="70%">{translate key="section.title"}</td>
			<td width="30%">{translate key="common.action"}</td>
		</tr>
		<tr>
			<td class="headseparator" colspan="2">&nbsp;</td>
		</tr>
		{foreach from=$mySections item=section}
			<tr valign="top" class="data">
				<td>{$section->getLocalizedTitle()|escape}</td>
				<td align="right"><a href="{url op="sectionArticleTypes" path=$section->getId()}" class="action">{translate key="article.type.configureSection"}</a></td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="2" class="nodata">{translate key="article.type.mySections.noneAssigned"}</td>
			</tr>
		{/foreach}
		<tr>
			<td colspan="2" class="endseparator">&nbsp;</td>
		</tr>
	</table>
</div>

{include file="common/footer-parts/footer-user.tpl"}