{**
 * templates/user/linkedAccounts.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User index.
 *
 *}
{strip}
{assign var="pageTitle" value="user.linkedAccounts"}
{include file="common/header-parts/header-admin.tpl"}
{/strip}

<div id="linkedAccounts" style="max-width: 800px;">
    <p style="margin-bottom: 25px; color: #555; line-height: 1.6;">
        Tautkan akun ScholarWizdam Anda dengan layanan pihak ketiga untuk mempermudah akses masuk (Login) dan melakukan sinkronisasi otomatis terhadap data identitas riset Anda.
    </p>

    {* --- [WIZDAM MAGIC] NOTIFIKASI STATUS (FEEDBACK) --- *}
    {if $smarty.get.success == 'google_linked'}
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
            &#10004; Akun Google berhasil ditautkan.
        </div>
    {elseif $smarty.get.success == 'google_unlinked'}
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; border-radius: 4px;">
            &#10004; Tautan akun Google berhasil dilepas.
        </div>
    {elseif $smarty.get.error == 'google_in_use'}
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
            &#10008; <strong>Gagal:</strong> Akun Google tersebut sudah tertaut dengan pengguna lain di jurnal ini.
        </div>
    {elseif $smarty.get.success == 'orcid_linked'}
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
            &#10004; ORCID iD berhasil ditautkan.
        </div>
    {elseif $smarty.get.success == 'orcid_unlinked'}
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; border-radius: 4px;">
            &#10004; Tautan ORCID iD berhasil dilepas.
        </div>
    {elseif $smarty.get.error == 'orcid_in_use'}
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
            &#10008; <strong>Gagal:</strong> ORCID iD tersebut sudah tertaut dengan pengguna lain di jurnal ini.
        </div>
    {/if}

    <table class="data" width="100%" style="border-collapse: collapse; border: 1px solid #ddd; background: #fff; border-radius: 5px;">

        {* ================================================================= *}
        {* BARIS: GOOGLE ACCOUNT                                             *}
        {* ================================================================= *}
        {if $googleSsoEnabled}
        <tr valign="middle">
            <td width="25%" class="label" style="padding: 20px 15px; background: #fcfcfc;">
                <strong>Google Account</strong>
            </td>
            <td width="55%" class="value" style="padding: 20px 15px;">
                {if $isGoogleLinked}
                    <span style="color: #28a745; font-weight: bold;">&#10004; Tertaut</span><br/>
                    {if $googleEmail}
                        <span style="font-size: 0.9em; color: #555;">{$googleEmail|escape}</span>
                    {/if}
                {else}
                    <span style="color: #6c757d;">Belum tertaut</span>
                {/if}
            </td>
            <td width="20%" align="right" style="padding: 20px 15px;">
                {if $isGoogleLinked}
                    <button type="button" class="button" onclick="if(confirm('Apakah Anda yakin ingin memutus tautan Akun Google ini dari profil Anda?')) window.location.href='{url journal="index" page="login" op="google-unlink"}';">Unlink</button>
                {else}
                    <button type="button" class="button defaultButton" onclick="window.location.href='{url journal="index" page="login" op="google-auth"}';">Link Google</button>
                {/if}
            </td>
        </tr>
        {/if}

        {* ================================================================= *}
        {* BARIS: ORCID iD                                                   *}
        {* ================================================================= *}
        {if $orcidSsoEnabled}
        <tr valign="middle" style="border-bottom: 1px solid #eee;">
            <td width="25%" class="label" style="padding: 20px 15px; background: #fcfcfc;">
                <strong><span style="color: #A6CE39; font-size: 1.1em;">iD</span> ORCID</strong>
            </td>
            <td width="55%" class="value" style="padding: 20px 15px;">
                {if $isOrcidLinked}
                    <span style="color: #28a745; font-weight: bold;">&#10004; Tertaut</span><br/>
                    <a href="{$orcidUrl|escape}" target="_blank" style="font-size: 0.9em; color: #0056b3;">{$orcidUrl|escape}</a>
                {else}
                    <span style="color: #6c757d;">Belum tertaut</span>
                {/if}
            </td>
            <td width="20%" align="right" style="padding: 20px 15px;">
                {if $isOrcidLinked}
                    <button type="button" class="button" onclick="if(confirm('Apakah Anda yakin ingin memutus tautan ORCID iD ini dari profil Anda?')) window.location.href='{url journal="index" page="login" op="orcid-unlink"}';">Unlink</button>
                {else}
                    <button type="button" class="button defaultButton" onclick="window.location.href='{url journal="index" page="login" op="orcid-auth"}';">Link ORCID</button>
                {/if}
            </td>
        </tr>
        {/if}

    </table>
    
    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
        <a href="{url page="user"}" class="action" style="text-decoration: none;">&larr; Kembali ke Profil Utama</a>
    </div>
</div>

{include file="common/footer-parts/footer-admin.tpl"}

