{**
 * templates/admin/manualPayments.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{strip}
    {assign var="pageTitle" value="admin.manualPayments.pageTitle"}
    {include file="common/header.tpl"}
{/strip}

<div class="wizdam-card">
    <h2>{translate key="admin.manualPayments.pageTitle"}</h2>
    <p>{translate key="admin.manualPayments.description"}</p>

    <table class="wizdam-table" width="100%" style="border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #ccc;">
                <th style="padding: 10px; text-align: left;">Invoice</th>
                <th style="padding: 10px; text-align: left;">Nominal</th>
                <th style="padding: 10px; text-align: left;">Tanggal</th>
                <th style="padding: 10px; text-align: left;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$pendingInvoices item=invoice}
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">{$invoice->getData('invoiceNumber')|escape}</td>
                    <td style="padding: 10px;">{$invoice->getData('currencyCode')|escape} {$invoice->getData('amount')|number_format:2}</td>
                    <td style="padding: 10px;">{$invoice->getData('dateBilled')|date_format:"%d %b %Y"}</td>
                    <td style="padding: 10px;">
                        <form method="post" action="{url page="admin" op="confirmManualPaymentAction" path=$invoice->getInvoiceId()}">
                            <input type="hidden" name="csrfToken" value="{$csrfToken|escape}">
                            <button type="submit" class="wizdam-btn wizdam-btn-primary" onclick="return confirm('{translate key="admin.manualPayments.confirmPrompt"}');">
                                {translate key="admin.manualPayments.confirmButton"}
                            </button>
                        </form>
                    </td>
                </tr>
            {foreachelse}
                <tr><td colspan="4" style="padding: 20px; text-align: center; color: #666;">{translate key="admin.manualPayments.empty"}</td></tr>
            {/foreach}
        </tbody>
    </table>
</div>

{include file="common/footer.tpl"}
