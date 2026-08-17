{**
 * templates/admin/aboutSite.tpl
 * 
 * Copyright (c) 2017-2026 Sangia Code Lumera 
 * Copyright (c) 2024-2026 Rochmady and Development Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * Tampilan formulir untuk AboutSiteForm -- narasi (Mission/History/dst)
 * SEKALIGUS identitas resmi Penerbit yang dipakai ulang oleh Invoice/LoA/
 * Sertifikat dan halaman root Penerbit.
 *}
{strip}
    {assign var="pageTitle" value="admin.aboutSiteSettings"}
    {assign var="pageDisplayed" value="aboutSite"}
    {include file="common/header.tpl"}
{/strip}

<p>{translate key="admin.aboutSiteSettings.description"}</p>

<form method="post" action="{url op="saveAboutSite"}" enctype="multipart/form-data">
    <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
    {include file="common/formErrors.tpl"}

    {* ===== IDENTITAS RESMI PENERBIT (non-multibahasa) ===== *}
    <table class="data" width="100%">
        <tr valign="top">
            <td width="20%" class="label">{translate key="admin.siteSettings.publisherName"}</td>
            <td width="80%" class="value">
                <input type="text" name="publisherName" id="publisherName" value="{$publisherName|escape}" size="60" class="textField" />
            </td>
        </tr>
        <tr valign="top">
            <td class="label">{translate key="admin.siteSettings.publisherLegalEntity"}</td>
            <td class="value">
                <textarea name="publisherLegalEntity" id="publisherLegalEntity" cols="60" rows="3" class="textArea">{$publisherLegalEntity|escape}</textarea>
                <span class="instruct">{translate key="admin.siteSettings.publisherLegalEntity.description"}</span>
            </td>
        </tr>
        <tr valign="top">
            <td class="label">{translate key="admin.siteSettings.publisherAddress"}</td>
            <td class="value">
                <textarea name="publisherAddress" id="publisherAddress" cols="60" rows="3" class="textArea">{$publisherAddress|escape}</textarea>
            </td>
        </tr>
        <tr valign="top">
            <td class="label">{translate key="admin.siteSettings.publisherLogo"}</td>
            <td class="value">
                {if $currentLogoUrl}
                    <img src="{$currentLogoUrl|escape}" alt="Publisher Logo" style="max-height:80px; display:block; margin-bottom:10px;">
                {/if}
                <input type="file" name="publisherLogo" id="publisherLogo" accept="image/*" />
                <span class="instruct">{translate key="admin.siteSettings.publisherLogo.description"}</span>
            </td>
        </tr>
        <tr valign="top">
            <td class="label">{translate key="admin.siteSettings.publisherColors"}</td>
            <td class="value">
                {translate key="admin.siteSettings.publisherColorPrimary"}
                <input type="color" name="publisherColorPrimary" value="{$publisherColorPrimary|escape}" style="vertical-align:middle; margin-right:20px;" />
                {translate key="admin.siteSettings.publisherColorSecondary"}
                <input type="color" name="publisherColorSecondary" value="{$publisherColorSecondary|escape}" style="vertical-align:middle;" />
            </td>
        </tr>
    </table>

    <div id="settings">
        {foreach from=$formLocales key=localeKey item=localeName}
        <fieldset class="locale {if $formLocale != $localeKey}hidden{/if}">
            <legend class="hidden">{$localeName}</legend>
            <table class="data" width="100%">
                <tr valign="top">
                    <td class="label">{translate key="admin.siteSettings.publisherTagline"}</td>
                    <td class="value">
                        <input type="text" name="publisherTagline[{$localeKey|escape}]" id="publisherTagline" value="{$publisherTagline[$localeKey]|escape}" size="60" class="textField" />
                    </td>
                </tr>
                <tr valign="top">
                    <td width="20%" class="label">{translate key="admin.siteSettings.publisherMotto"}</td>
                    <td width="80%" class="value">
                        <input type="text" name="publisherMotto[{$localeKey|escape}]" id="publisherMotto" value="{$publisherMotto[$localeKey]|escape}" size="60" class="textField" />
                    </td>
                </tr>
                <tr valign="top">
                    <td width="20%" class="label">{fieldLabel name="publisherMission" key=$publisherMissionKey}</td>
                    <td width="80%" class="value">
                        {* PERBAIKAN: 'id' statis, 'name' multilingual *}
                        <textarea name="publisherMission[{$localeKey|escape}]" id="publisherMission" cols="60" rows="10" class="textArea">{$publisherMission[$localeKey]|escape}</textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <td class="label">{fieldLabel name="publisherHistory" key=$publisherHistoryKey}</td>
                    <td class="value">
                        {* PERBAIKAN: 'id' statis, 'name' multilingual *}
                        <textarea name="publisherHistory[{$localeKey|escape}]" id="publisherHistory" cols="60" rows="10" class="textArea">{$publisherHistory[$localeKey]|escape}</textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <td class="label">{fieldLabel name="publisherLeaderships" key=$publisherLeadershipsKey}</td>
                    <td class="value">
                        {* PERBAIKAN: 'id' statis, 'name' multilingual *}
                        <textarea name="publisherLeaderships[{$localeKey|escape}]" id="publisherLeaderships" cols="60" rows="10" class="textArea">{$publisherLeaderships[$localeKey]|escape}</textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <td class="label">{fieldLabel name="publisherAwards" key=$publisherAwardsKey}</td>
                    <td class="value">
                        {* PERBAIKAN: 'id' statis, 'name' multilingual *}
                        <textarea name="publisherAwards[{$localeKey|escape}]" id="publisherAwards" cols="60" rows="10" class="textArea">{$publisherAwards[$localeKey]|escape}</textarea>
                    </td>
                </tr>
            </table>
        </fieldset>
        {/foreach}
    </div>

    <p>
        <input type="submit" name="save" class="button" value="{translate key="common.save"}" />
        <input type="button" class="button" value="{translate key="common.cancel"}" onclick="document.location.href='{url op="index" escape=false}'" />
        {if $justSaved}
            <input type="button" class="button" value="{translate key="common.back"}" onclick="document.location.href='{url op="index" escape=false}'" />
        {/if}
    </p>
</form>

{include file="common/footer.tpl"}
