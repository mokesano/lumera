{**
 * plugins/themes/sangiapub/templates/author/submit/step2.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] Step 2 dari wizard submit yang DIRESTRUKTURISASI --
 * Authors + CRediT (peran kontribusi per-penulis) + Funders
 * (pendanaan/hibah). Diambil dari bagian authors+funders step3.tpl
 * LAMA (metadata-nya sudah pindah ke step1.tpl BARU).
 *
 * PERUBAHAN: competingInterests PER-PENULIS (textarea lama) DIHAPUS,
 * DIGANTIKAN checkbox CRediT (14 peran baku) -- competingInterest
 * level artikel sekarang ada di step3.tpl BARU (Deklarasi).
 *}
{assign var="pageTitle" value="author.submit.step2"}
{include file="author/submit/submitHeader.tpl"}

<div class="separator"></div>

<form id="submit" method="post" action="{url op="saveSubmit" path=$submitStep}">
	<input value="{$csrfToken|escape}" name="csrfToken" type="hidden" />
	<input type="hidden" name="articleId" value="{$articleId|escape}" />
	{include file="common/formErrors.tpl"}

	{literal}
	<script type="text/javascript">
		<!--
		// Move author up/down
		function moveAuthor(dir, authorIndex) {
			var form = document.getElementById('submit');
			form.moveAuthor.value = 1;
			form.moveAuthorDir.value = dir;
			form.moveAuthorIndex.value = authorIndex;
			form.submit();
		}
		// [WIZDAM] Move funder up/down -- pola sama persis moveAuthor di atas.
		function moveFunder(dir, funderIndex) {
			var form = document.getElementById('submit');
			form.moveFunder.value = 1;
			form.moveFunderDir.value = dir;
			form.moveFunderIndex.value = funderIndex;
			form.submit();
		}
		// -->
	</script>
	{/literal}

	{if count($formLocales) > 1}
	<div id="locales">
		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
				<td width="80%" class="value">
					{url|assign:"submitFormUrl" op="submit" path="2" articleId=$articleId escape=false}
					{* Maintain localized author info across requests --
					   [WIZDAM] competingInterests per-penulis DIHAPUS dari
					   sini (field itu sudah tidak ada di struktur data
					   Step 2 baru, digantikan competingInterest level
					   artikel di Step 3). *}
					{foreach from=$authors key=authorIndex item=author}
						{foreach from=$author.biography key="thisLocale" item="thisBiography"}
							{if $thisLocale != $formLocale}<input type="hidden" name="authors[{$authorIndex|escape}][biography][{$thisLocale|escape}]" value="{$thisBiography|escape}" />{/if}
						{/foreach}
						{foreach from=$author.affiliation key="thisLocale" item="thisAffiliation"}
							{if $thisLocale != $formLocale}<input type="hidden" name="authors[{$authorIndex|escape}][affiliation][{$thisLocale|escape}]" value="{$thisAffiliation|escape}" />{/if}
						{/foreach}
					{/foreach}
					{form_language_chooser form="submit" url=$submitFormUrl}
					<span class="instruct">{translate key="form.formLanguage.description"}</span>
				</td>
			</tr>
		</table>
	</div>
	{/if}

	<div id="authors" class="block">
		<h3>{translate key="article.authors"}</h3>
		<input type="hidden" name="deletedAuthors" value="{$deletedAuthors|escape}" />
		<input type="hidden" name="moveAuthor" value="0" />
		<input type="hidden" name="moveAuthorDir" value="" />
		<input type="hidden" name="moveAuthorIndex" value="" />

		{foreach name=authors from=$authors key=authorIndex item=author}
			<input type="hidden" name="authors[{$authorIndex|escape}][authorId]" value="{$author.authorId|escape}" />
			<input type="hidden" name="authors[{$authorIndex|escape}][seq]" value="{$authorIndex+1}" />
			{if $smarty.foreach.authors.total <= 1}
				<input type="hidden" name="primaryContact" value="{$authorIndex|escape}" />
			{/if}

			<table width="100%" class="data">
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-firstName" required="true" key="user.firstName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[{$authorIndex|escape}][firstName]" id="authors-{$authorIndex|escape}-firstName" value="{$author.firstName|escape}" size="20" maxlength="40" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-middleName" key="user.middleName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[{$authorIndex|escape}][middleName]" id="authors-{$authorIndex|escape}-middleName" value="{$author.middleName|escape}" size="20" maxlength="40" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-lastName" required="true" key="user.lastName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[{$authorIndex|escape}][lastName]" id="authors-{$authorIndex|escape}-lastName" value="{$author.lastName|escape}" size="20" maxlength="90" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-email" required="true" key="user.email"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[{$authorIndex|escape}][email]" id="authors-{$authorIndex|escape}-email" value="{$author.email|escape}" size="30" maxlength="90" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-orcid" key="user.orcid"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[{$authorIndex|escape}][orcid]" id="authors-{$authorIndex|escape}-orcid" value="{$author.orcid|escape}" size="30" maxlength="90" /><br />{translate key="user.orcid.description"}</td>
				</tr>
				<tr valign="top">
					<td class="label">{fieldLabel name="authors-$authorIndex-url" key="user.url"}</td>
					<td class="value"><input type="text" name="authors[{$authorIndex|escape}][url]" id="authors-{$authorIndex|escape}-url" value="{$author.url|escape}" size="30" maxlength="255" class="textField" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-affiliation" required="true" key="user.affiliation"}</td>
					<td width="80%" class="value">
						<textarea name="authors[{$authorIndex|escape}][affiliation][{$formLocale|escape}]" class="textArea" id="authors-{$authorIndex|escape}-affiliation" rows="5" cols="40">{$author.affiliation[$formLocale]|escape}</textarea><br/>
						<span class="instruct">{translate key="user.affiliation.description"}</span>
					</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-country" required="true" key="common.country"}</td>
					<td width="80%" class="value">
						<select name="authors[{$authorIndex|escape}][country]" id="authors-{$authorIndex|escape}-country" class="selectMenu">
							<option value=""></option>
							{html_options options=$countries selected=$author.country}
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-biography" key="user.biography"}</td>
					<td width="80%" class="value"><textarea name="authors[{$authorIndex|escape}][biography][{$formLocale|escape}]" class="textArea" id="authors-{$authorIndex|escape}-biography" rows="5" cols="40">{$author.biography[$formLocale]|escape}</textarea>
						<span class="instruct">{translate key="user.biography.description"}</span>
					</td>
				</tr>
				{* [WIZDAM] CRediT -- 14 peran baku, checkbox multi-pilih,
				   MENGGANTIKAN textarea competingInterests per-penulis
				   yang lama. Opsional (tidak diwajibkan). *}
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-creditRoles" key="author.credit.label"}</td>
					<td width="80%" class="value">
						<div class="creditRolesGrid">
						{foreach from=$allCreditRoles item=roleCode}
							<label class="creditRoleOption">
								<input type="checkbox" name="authors[{$authorIndex|escape}][creditRoles][]" value="{$roleCode|escape}"{if in_array($roleCode, $author.creditRoles)} checked="checked"{/if} />
								{translate key="author.credit.role.`$roleCode`"}
							</label>
						{/foreach}
						</div>
						<span class="instruct">{translate key="author.credit.description"}</span>
					</td>
				</tr>

				{call_hook name="Templates::Author::Submit::Authors"}

				{if $smarty.foreach.authors.total > 1}
					<tr valign="top">
						<td colspan="2">
							<a href="javascript:moveAuthor('u', '{$authorIndex|escape}')" class="action">&uarr;</a> <a href="javascript:moveAuthor('d', '{$authorIndex|escape}')" class="action">&darr;</a>
							{translate key="author.submit.reorderInstructions"}
						</td>
					</tr>
					<tr valign="top">
						<td width="80%" class="value" colspan="2"><input type="radio" name="primaryContact" value="{$authorIndex|escape}"{if $primaryContact == $authorIndex} checked="checked"{/if} /> <label for="primaryContact">{translate key="author.submit.selectPrincipalContact"}</label> <input type="submit" name="delAuthor[{$authorIndex|escape}]" value="{translate key="author.submit.deleteAuthor"}" class="button" /></td>
					</tr>
					<tr>
						<td colspan="2"><br/></td>
					</tr>
				{/if}
			</table>
		{foreachelse}
			<input type="hidden" name="authors[0][authorId]" value="0" />
			<input type="hidden" name="primaryContact" value="0" />
			<input type="hidden" name="authors[0][seq]" value="1" />
			<table width="100%" class="data">
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-firstName" required="true" key="user.firstName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[0][firstName]" id="authors-0-firstName" size="20" maxlength="40" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-middleName" key="user.middleName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[0][middleName]" id="authors-0-middleName" size="20" maxlength="40" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-lastName" required="true" key="user.lastName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[0][lastName]" id="authors-0-lastName" size="20" maxlength="90" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-email" required="true" key="user.email"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[0][email]" id="authors-0-email" size="30" maxlength="90" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-orcid" key="user.orcid"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[0][orcid]" id="authors-0-orcid" size="30" maxlength="90" /><br />{translate key="user.orcid.description"}</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-url" key="user.url"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="authors[0][url]" id="authors-0-url" size="30" maxlength="255" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-affiliation" required="true" key="user.affiliation"}</td>
					<td width="80%" class="value">
						<textarea name="authors[0][affiliation][{$formLocale|escape}]" class="textArea" id="authors-0-affiliation" rows="5" cols="40"></textarea><br/>
						<span class="instruct">{translate key="user.affiliation.description"}</span>
					</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-country" required="true" key="common.country"}</td>
					<td width="80%" class="value">
						<select name="authors[0][country]" id="authors-0-country" class="selectMenu">
							<option value=""></option>
							{html_options options=$countries}
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-biography" key="user.biography"}</td>
					<td width="80%" class="value"><textarea name="authors[0][biography][{$formLocale|escape}]" class="textArea" id="authors-0-biography" rows="7" cols="40"></textarea>
						<span class="instruct">{translate key="user.biography.description"}</span>
					</td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="authors-0-creditRoles" key="author.credit.label"}</td>
					<td width="80%" class="value">
						<div class="creditRolesGrid">
						{foreach from=$allCreditRoles item=roleCode}
							<label class="creditRoleOption">
								<input type="checkbox" name="authors[0][creditRoles][]" value="{$roleCode|escape}" />
								{translate key="author.credit.role.`$roleCode`"}
							</label>
						{/foreach}
						</div>
						<span class="instruct">{translate key="author.credit.description"}</span>
					</td>
				</tr>
			</table>
		{/foreach}

		<p><input type="submit" class="button" name="addAuthor" value="{translate key="author.submit.addAuthor"}" /></p>
	</div>

	<div class="separator"></div>

	{* [WIZDAM] Blok Funders (pendanaan/hibah) -- pola sama persis blok
	Authors di atas, disederhanakan karena tidak perlu penanganan
	locale/primaryContact. *}
	<div id="funders" class="block">
		<h3>{translate key="author.submit.funders"}</h3>
		<input type="hidden" name="deletedFunders" value="{$deletedFunders|escape}" />
		<input type="hidden" name="moveFunder" value="0" />
		<input type="hidden" name="moveFunderDir" value="" />
		<input type="hidden" name="moveFunderIndex" value="" />

		{foreach name=funders from=$funders key=funderIndex item=funder}
			<input type="hidden" name="funders[{$funderIndex|escape}][funderId]" value="{$funder.funderId|escape}" />
			<input type="hidden" name="funders[{$funderIndex|escape}][seq]" value="{$funderIndex+1}" />

			<table width="100%" class="data">
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="funders-$funderIndex-funderName" required="true" key="author.submit.funderName"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="funders[{$funderIndex|escape}][funderName]" id="funders-{$funderIndex|escape}-funderName" value="{$funder.funderName|escape}" size="40" maxlength="255" /></td>
				</tr>
				<tr valign="top">
					<td width="20%" class="label">{fieldLabel name="funders-$funderIndex-awardNumber" key="author.submit.awardNumber"}</td>
					<td width="80%" class="value"><input type="text" class="textField" name="funders[{$funderIndex|escape}][awardNumber]" id="funders-{$funderIndex|escape}-awardNumber" value="{$funder.awardNumber|escape}" size="40" maxlength="255" />
						<span class="instruct">{translate key="author.submit.awardNumber.description"}</span>
					</td>
				</tr>
				{if $smarty.foreach.funders.total > 1}
					<tr valign="top">
						<td colspan="2">
							<a href="javascript:moveFunder('u', '{$funderIndex|escape}')" class="action">&uarr;</a> <a href="javascript:moveFunder('d', '{$funderIndex|escape}')" class="action">&darr;</a>
							{translate key="author.submit.reorderInstructions"}
						</td>
					</tr>
				{/if}
				<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="submit" name="delFunder[{$funderIndex|escape}]" value="{translate key="author.submit.deleteFunder"}" class="button" /></td>
				</tr>
			</table>
		{foreachelse}
			<p class="instruct">{translate key="author.submit.noFunders"}</p>
		{/foreach}

		{* [WIZDAM] projectID (OpenAIRE, nomor proyek hibah UE) --
		   dikelompokkan di sini karena konseptual dekat dengan Funders,
		   lihat OpenAIREPlugin.inc.php untuk penjelasan lengkap
		   keputusan ini. *}
		{call_hook name="Templates::Author::Submit::AdditionalMetadata"}

		<p><input type="submit" class="button" name="addFunder" value="{translate key="author.submit.addFunder"}" /></p>
	</div>

	<div class="separator"></div>

	<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{if $scrollToAuthor}
	{literal}
	<script type="text/javascript">
		var authors = document.getElementById('authors');
		authors.scrollIntoView(false);
	</script>
	{/literal}
{/if}

{include file="common/footer-parts/footer-user.tpl"}
