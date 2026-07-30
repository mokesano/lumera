{**
 * templates/admin/paymentSettings.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Frontedge
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * Administrator Payment Settings.
 *
 *}
{strip}
    {assign var="pageTitle" value="payment.gatewaySettings"}
    {include file="common/header.tpl"}
{/strip}

{if $smarty.get.saved}
    <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">
        <strong>{translate key="common.success"}</strong> {translate key="payment.settingsSaved"}
    </div>
{/if}

{include file="common/formErrors.tpl"}

{literal}
<style>
    .wi-bank-row { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 8px; align-items: center; margin-bottom: 8px; padding: 10px; background: #f9f9f9; border-radius: 4px; }
    .wi-bank-row input { padding: 6px; border: 1px solid #ccc; border-radius: 3px; width: 100%; box-sizing: border-box; }
    .wi-bank-row .wi-remove-bank { background: #dc3545; color: #fff; border: none; border-radius: 3px; padding: 6px 10px; cursor: pointer; }
    .wi-add-bank { margin-top: 8px; background: #28a745; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
</style>
{/literal}

<form method="post" action="{url page="admin" op="save-payment-settings"}">
    <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
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

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #fff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">{translate key="payment.enabledMethods"}</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label">{translate key="payment.method.manual"}</td>
                <td width="75%" class="value">
                    <input type="checkbox" name="enabled_manual" id="enabled_manual" value="1"{if $enabled_manual} checked="checked"{/if} />
                    <label for="enabled_manual">{translate key="payment.method.enableThis"}</label>

                    <div style="margin-top: 15px;">
                        <strong style="font-size: 13px;">{translate key="payment.bankAccounts.title"}</strong>
                        <p style="font-size: 11px; color: #666; margin: 4px 0 10px;">{translate key="payment.bankAccounts.description"}</p>

                        <div id="bankAccountsContainer">
                            {foreach from=$bank_accounts item=account name=bankLoop}
                            <div class="wi-bank-row">
                                <input type="text" name="bank_name[]" value="{$account.bankName|escape}" placeholder="{translate key="payment.bankAccounts.bankNamePlaceholder"|escape}">
                                <input type="text" name="account_number[]" value="{$account.accountNumber|escape}" placeholder="{translate key="payment.bankAccounts.accountNumberPlaceholder"|escape}">
                                <input type="text" name="account_holder[]" value="{$account.accountHolder|escape}" placeholder="{translate key="payment.bankAccounts.accountHolderPlaceholder"|escape}">
                                <input type="text" name="bank_branch[]" value="{$account.branch|escape}" placeholder="{translate key="payment.bankAccounts.branchPlaceholder"|escape}">
                                <button type="button" class="wi-remove-bank" onclick="this.parentElement.remove();">&times;</button>
                            </div>
                            {foreachelse}
                            <div class="wi-bank-row">
                                <input type="text" name="bank_name[]" value="" placeholder="{translate key="payment.bankAccounts.bankNamePlaceholder"|escape}">
                                <input type="text" name="account_number[]" value="" placeholder="{translate key="payment.bankAccounts.accountNumberPlaceholder"|escape}">
                                <input type="text" name="account_holder[]" value="" placeholder="{translate key="payment.bankAccounts.accountHolderPlaceholder"|escape}">
                                <input type="text" name="bank_branch[]" value="" placeholder="{translate key="payment.bankAccounts.branchPlaceholder"|escape}">
                                <button type="button" class="wi-remove-bank" onclick="this.parentElement.remove();">&times;</button>
                            </div>
                            {/foreach}
                        </div>
                        <button type="button" class="wi-add-bank" onclick="addBankRow()">+ {translate key="payment.bankAccounts.addAnother"}</button>

                        <div style="margin-top: 12px;">
                            <label style="font-size: 12px; color: #666;">{translate key="payment.bankAccounts.generalNotes"}</label>
                            <textarea name="bank_notes[]" cols="60" rows="2" class="textArea" placeholder="{translate key="payment.bankAccounts.generalNotesPlaceholder"|escape}">{if $bank_accounts|@count > 0}{$bank_accounts[0].notes|escape}{/if}</textarea>
                        </div>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <td class="label">{translate key="payment.method.paypal"}</td>
                <td class="value">
                    <input type="checkbox" name="enabled_paypal" id="enabled_paypal" value="1"{if $enabled_paypal} checked="checked"{/if} />
                    <label for="enabled_paypal">{translate key="payment.method.enableThis"}</label>
                    <br><br>
                    <input type="email" name="paypal_seller_email" id="paypal_seller_email" value="{$paypal_seller_email|escape}" size="40" class="textField" placeholder="seller@example.com" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label">{translate key="payment.method.midtrans"}</td>
                <td class="value">
                    <input type="checkbox" name="enabled_midtrans" id="enabled_midtrans" value="1"{if $enabled_midtrans} checked="checked"{/if} />
                    <label for="enabled_midtrans">{translate key="payment.method.enableThis"}</label>
                </td>
            </tr>
            <tr valign="top">
                <td class="label">{translate key="payment.method.xendit"}</td>
                <td class="value">
                    <input type="checkbox" name="enabled_xendit" id="enabled_xendit" value="1"{if $enabled_xendit} checked="checked"{/if} />
                    <label for="enabled_xendit">{translate key="payment.method.enableThis"}</label>
                </td>
            </tr>
        </table>
        <span style="font-size: 11px; color: #666;">{translate key="payment.enabledMethods.description"}</span>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #0056b3; padding: 20px; border-radius: 5px; background: #f4f9ff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #cce5ff; padding-bottom: 10px; color: #0056b3;">Konfigurasi Xendit</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="xendit_api_key">Secret API Key</label></td>
                <td width="75%" class="value">
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
            <a href="{url page="admin"}" style="margin-left: 15px; padding: 10px 25px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s;">
                &larr; Selesai & Kembali
            </a>
        {else}
            <a href="{url page="admin"}" style="margin-left: 15px; text-decoration: none; color: #666; padding: 10px 15px;">
                Batal
            </a>
        {/if}
    </div>
</form>

<script>
{literal}
function addBankRow() {
    const container = document.getElementById('bankAccountsContainer');
    const row = document.createElement('div');
    row.className = 'wi-bank-row';
    row.innerHTML = '<input type="text" name="bank_name[]" placeholder="Nama Bank">' +
        '<input type="text" name="account_number[]" placeholder="Nomor Rekening">' +
        '<input type="text" name="account_holder[]" placeholder="Atas Nama">' +
        '<input type="text" name="bank_branch[]" placeholder="Cabang (opsional)">' +
        '<button type="button" class="wi-remove-bank" onclick="this.parentElement.remove();">&times;</button>';
    container.appendChild(row);
}
{/literal}
</script>

{include file="common/footer.tpl"}
