{strip}
{assign var="pageTitle" value="payment.gatewaySettings"}
{include file="common/header.tpl"}
{/strip}

{if $smarty.get.saved}
    <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">
        <strong>Berhasil!</strong> Pengaturan Payment Gateway telah disimpan ke dalam database.
    </div>
{/if}

{* Pesan Error bawaan Form OJS jika validasi gagal (misal CSRF tidak valid) *}
{include file="common/formErrors.tpl"}

<form method="post" action="{url page="admin" op="save-payment-settings"}">
    {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
    <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #fff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Pengaturan Lingkungan (Environment)</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="active_gateway">Gateway Aktif</label></td>
                <td width="75%" class="value">
                    <select name="active_gateway" id="active_gateway" class="selectMenu" style="padding: 5px;">
                        <option value="midtrans" {if $active_gateway == 'midtrans'}selected="selected"{/if}>Midtrans (Snap)</option>
                        <option value="xendit" {if $active_gateway == 'xendit'}selected="selected"{/if}>Xendit</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="is_production">Mode Sistem</label></td>
                <td class="value">
                    <select name="is_production" id="is_production" class="selectMenu" style="padding: 5px;">
                        <option value="0" {if !$is_production}selected="selected"{/if}>Sandbox / Testing</option>
                        <option value="1" {if $is_production}selected="selected"{/if}>Production / Live</option>
                    </select>
                    <br>
                    <span style="font-size: 11px; color: #666;">Pilih Sandbox saat Anda sedang menguji pembayaran.</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #0056b3; padding: 20px; border-radius: 5px; background: #f4f9ff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #cce5ff; padding-bottom: 10px; color: #0056b3;">Konfigurasi Xendit</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="xendit_api_key">Secret API Key</label></td>
                <td width="75%" class="value">
                    {* Menggunakan type password agar API Key tidak terlihat saat ada orang di belakang layar *}
                    <input type="password" name="xendit_api_key" id="xendit_api_key" value="{$xendit_api_key|escape}" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="xendit_webhook_token">Webhook Verification Token</label></td>
                <td class="value">
                    <input type="password" name="xendit_webhook_token" id="xendit_webhook_token" value="{$xendit_webhook_token|escape}" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #17a2b8; padding: 20px; border-radius: 5px; background: #fdfdfe;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #17a2b8;">Konfigurasi Midtrans</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="midtrans_server_key">Server Key</label></td>
                <td width="75%" class="value">
                    <input type="password" name="midtrans_server_key" id="midtrans_server_key" value="{$midtrans_server_key|escape}" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="midtrans_client_key">Client Key</label></td>
                <td class="value">
                    {* Client key bersifat publik, jadi biarkan bertipe text *}
                    <input type="text" name="midtrans_client_key" id="midtrans_client_key" value="{$midtrans_client_key|escape}" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; padding-bottom: 40px; display: flex; align-items: center;">
        <button type="submit" class="wizdam-btn wizdam-btn-success" style="padding: 10px 25px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; border-radius: 4px;">
            {if $smarty.get.saved}Perbarui Lagi{else}Simpan Pengaturan{/if}
        </button>

        {if $smarty.get.saved}
            {* WIZDAM UX: Jika baru saja disimpan, berikan tombol KEMBALI yang jelas *}
            <a href="{url page="admin"}" style="margin-left: 15px; padding: 10px 25px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s;">
                &larr; Selesai & Kembali
            </a>
        {else}
            {* Jika sedang mengedit biasa, tampilkan batal *}
            <a href="{url page="admin"}" style="margin-left: 15px; text-decoration: none; color: #666; padding: 10px 15px;">
                Batal
            </a>
        {/if}
    </div>
</form>

{include file="common/footer.tpl"}