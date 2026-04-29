{**
 * templates/admin/aboutSite.tpl
 *
 * Tampilan formulir untuk AboutSiteForm.
 *}
{strip}
{assign var="pageTitle" value="admin.aboutSiteSettings"}
{assign var="pageDisplayed" value="aboutSite"}
{include file="common/header.tpl"}
{/strip}

<p>{translate key="admin.aboutSiteSettings.description"}</p>

<form method="post" action="{url op="saveAboutSite"}">
{include file="common/formErrors.tpl"}

{* WIZDAM SECURITY: Token CSRF Wajib Ada *}
<input value="{$csrfToken|escape}" name="csrfToken" type="hidden">
    
<div id="settings">
{foreach from=$formLocales key=localeKey item=localeName}
<fieldset class="locale {if $formLocale != $localeKey}hidden{/if}">
    <legend class="hidden">{$localeName}</legend>
    <table class="data" width="100%">
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
    <input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}" />
    <input type="button" class="button" value="{translate key="common.cancel"}" onclick="document.location.href='{url op="index" escape=false}'" />
</p>
</form>

{include file="common/footer.tpl"}