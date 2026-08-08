{**
 * templates/admin/doiSettings.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Frontedge
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * Administrator DOI Credential Settings.
 *
 *}
{strip}
    {assign var="pageTitle" value="admin.doi.settings"}
    {include file="common/header.tpl"}
{/strip}

{if $smarty.get.saved}
    <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">
        <strong>{translate key="common.success"}</strong> {translate key="admin.doi.settingsSaved"}
    </div>
{/if}

{include file="common/formErrors.tpl"}

<div class="instruct">
    <span class="instruct" style="color: #666; margin-bottom: 20px;">{translate key="admin.doi.settingsDescription"}</span>
</div>

<form method="post" action="{url page="admin" op="save-doi-settings"}">
    <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #fff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Crossref</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="crossref_depositor_name">{translate key="admin.doi.crossrefDepositorName"} <span class="formRequired">*</span></label></td>
                <td width="75%" class="value">
                    <input type="text" name="crossref_depositor_name" id="crossref_depositor_name" value="{$crossref_depositor_name|escape}" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
            <tr valign="top">
                <td width="25%" class="label"><label for="crossref_email">{translate key="admin.doi.crossrefEmail"} <span class="formRequired">*</span></label></td>
                <td width="75%" class="value">
                    <input type="email" name="crossref_email" id="crossref_email" value="{$crossref_email|escape}" size="60" class="textField" placeholder="you@example.org" />
                </td>
            </tr>
        </table>

        <div class="separator"></div>

        <p class="instruct" style="margin: 15px 0;">{translate key="admin.doi.crossrefRegistrationIntro"}</p>

        <table class="data" width="100%">
            <tr valign="top">
                <td class="label"><label for="crossref_username">{translate key="admin.doi.crossrefUsername"}</label></td>
                <td class="value">
                    <input type="text" name="crossref_username" id="crossref_username" value="{$crossref_username|escape}" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="crossref_password">{translate key="admin.doi.crossrefPassword"}</label></td>
                <td class="value">
                    <input type="password" name="crossref_password" id="crossref_password" value="{$crossref_password|escape}" size="60" maxlength="255" class="textField" autocomplete="new-password" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label">{fieldLabel name="crossref_automatic_registration" key="plugins.importexport.crossref.settings.form.automaticRegistration"}</td>
                <td class="value">
                    <input type="checkbox" name="crossref_automatic_registration" id="crossref_automatic_registration" value="1"{if $crossref_automatic_registration} checked="checked"{/if} />&nbsp;{translate key="admin.doi.crossrefAutomaticRegistrationDescription"}
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #fff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">{translate key="admin.doi.optionalSources"}</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="semantic_scholar_api_key">Semantic Scholar API Key</label></td>
                <td width="75%" class="value">
                    <input type="password" name="semantic_scholar_api_key" id="semantic_scholar_api_key" value="{$semantic_scholar_api_key|escape}" size="60" maxlength="255" class="textField" autocomplete="new-password" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="dimensions_api_key">Dimensions API Key</label></td>
                <td class="value">
                    <input type="password" name="dimensions_api_key" id="dimensions_api_key" value="{$dimensions_api_key|escape}" size="60" maxlength="255" class="textField" autocomplete="new-password" />
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; padding-bottom: 40px; display: flex; align-items: center;">
        <button type="submit" class="wizdam-btn wizdam-btn-success" style="padding: 10px 25px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; border-radius: 4px;">
            {if $smarty.get.saved}Perbarui Lagi{else}Simpan Pengaturan{/if}
        </button>

        {if $smarty.get.saved}
            <a href="{url page="admin"}" style="margin-left: 15px; padding: 10px 25px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s;">
                &larr; Selesai &amp; Kembali
            </a>
        {else}
            <a href="{url page="admin"}" style="margin-left: 15px; text-decoration: none; color: #666; padding: 10px 15px;">
                Batal
            </a>
        {/if}
    </div>
</form>

{include file="common/footer.tpl"}
