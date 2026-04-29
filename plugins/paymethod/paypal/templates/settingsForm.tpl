{**
 * plugins/paymethod/paypal/templates/settingsForm.tpl
 *
 * Copyright (c) 2017-2025 Wizdam Team Dev
 * Copyright (c) 2017-2025 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] - API v2 Configuration
 *}
 
<tr>
    <td colspan="2">
        <h4>{translate key="plugins.paymethod.paypal.displayName"} (API v2)</h4>
        <span class="instruct">Plugin ini menggunakan PayPal Smart Buttons.</span>
    </td>
</tr>
<tr valign="top">
    <td class="label" width="20%"><label for="clientId">Client ID</label></td>
    <td class="value" width="80%">
        <input type="text" class="textField" name="clientId" id="clientId" value="{$clientId|escape}" size="50" style="width: 100%;" />
        <br />
        <span class="instruct">Salin <strong>Client ID</strong> dari PayPal Developer Dashboard (Apps & Credentials).</span>
    </td>
</tr>
<tr valign="top">
    <td class="label" width="20%">Mode</td>
    <td class="value" width="80%">
        <input type="checkbox" name="testMode" id="testMode" value="1" {if $testMode}checked="checked"{/if} />
        <label for="testMode"><strong>Sandbox / Test Mode</strong></label>
    </td>
</tr>

{* Hidden field untuk paypalurl legacy agar tidak error di fungsi handle() *}
<input type="hidden" name="paypalurl" value="{$paypalurl|default:'https://www.paypal.com/cgi-bin/webscr'}" />
