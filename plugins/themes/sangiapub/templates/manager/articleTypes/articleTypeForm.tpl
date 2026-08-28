{**
 * plugins/themes/sangiapub/templates/manager/articleTypes/articleTypeForm.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Form untuk Journal Manager menambah/mengedit satu tipe
 * artikel KUSTOM (lihat ArticleTypeCustomForm.inc.php) -- jauh lebih
 * sederhana dari manager/sections/sectionForm.tpl karena cuma satu
 * field (nama, localized).
 *}
{strip}
	{assign var="pageTitle" value="article.type.customType"}
	{assign var="pageCrumbTitle" value="article.type.customType"}
	{include file="common/header-USER027.tpl"}
{/strip}

<form id="articleTypeForm" method="post" action="{url op="updateArticleType" path=$customTypeId}">
	<input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />

	{include file="common/formErrors.tpl"}

	<div id="articleTypeFormFields">
		<table class="data" width="100%">
			{if count($formLocales) > 1}
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
					<td width="80%" class="value">
						{if $customTypeId}{url|assign:"articleTypeFormUrl" op="editArticleType" path=$customTypeId escape=false}
						{else}{url|assign:"articleTypeFormUrl" op="createArticleType" path=$customTypeId escape=false}
						{/if}
						{form_language_chooser form="articleTypeForm" url=$articleTypeFormUrl}
						<span class="instruct">{translate key="form.formLanguage.description"}</span>
					</td>
				</tr>
			{/if}
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="name" required="true" key="article.type.form.name"}</td>
				<td width="80%" class="value"><input type="text" name="name[{$formLocale|escape}]" value="{$name[$formLocale]|escape}" id="name" size="40" maxlength="120" class="textField" /></td>
			</tr>
		</table>
	</div>

	<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="articleTypes" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer-parts/footer-user.tpl"}