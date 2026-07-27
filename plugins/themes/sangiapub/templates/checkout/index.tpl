{**
 * templates/checkout/index.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Frontedge
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * Checkout Index
 *
 *}
{strip}
    {assign var="pageTitle" value="checkout.globalBilling"}
    {include file="common/header.tpl"}
{/strip}

<div class="wizdam-billing-dashboard">
    <div class="wizdam-card">
        <table class="wizdam-table" width="100%" style="border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #ccc;">
                    <th style="padding: 10px;">Invoice ID</th>
                    <th style="padding: 10px;">Fee Type</th>
                    <th style="padding: 10px;">Date Billed</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Amount</th>
                    <th style="padding: 10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$invoices item=invoice}
                    {* [FIX] Tambahkan secureHash, sama seperti pola di billing/index.tpl *}
                    {assign var="invId" value=$invoice->getInvoiceId()}
                    {assign var="secureHash" value=$hashService->generateHash('invoice', $invId)}
                    {assign var="securePath" value="`$secureHash`-`$invId`"}

                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">#{$invId}</td>
                        <td style="padding: 10px;">{$invoice->getData('localizedFeeType')|escape}</td>
                        <td style="padding: 10px;">{$invoice->getData('dateBilled')|date_format:"%d %b %Y"}</td>
                        <td style="padding: 10px;">
                            {* [FIX] status 'PAID' adalah STRING, bukan integer 1 *}
                            {if $invoice->getData('status') === 'PAID' || $invoice->getData('datePaid') != ''}
                                <span class="wizdam-badge badge-success" style="color: #28a745; font-weight: bold;">PAID</span>
                            {else}
                                <span class="wizdam-badge badge-warning" style="color: #dc3545; font-weight: bold;">UNPAID</span>
                            {/if}
                        </td>
                        <td style="padding: 10px;">{$invoice->getData('currencyCode')|escape} {$invoice->getData('amount')|number_format:2}</td>
                        <td style="padding: 10px;">
                            {if $invoice->getData('status') !== 'PAID'}
                            {* [FIX] page="billing", bukan "checkout" -- dan pakai $securePath, bukan ID polos *}
                            <a class="cancel-action" href="{url page="billing" op="cancel" path=$securePath}" 
                            style="text-decoration: none; background: #d54449; color: white; padding: 5px 10px; border-radius: 4px; font-size: 14px; margin-right: 5px;">
                                Cancel Bill
                            </a>
                            {/if}
                            <a href="{url page="billing" op="invoice" path=$securePath}" 
                            class="wizdam-btn wizdam-btn-primary" 
                            style="text-decoration: none; background: #0056b3; color: white; padding: 5px 10px; border-radius: 4px; font-size: 14px;">
                            View Details
                            </a>
                        </td>
                    </tr>
                {foreachelse}
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px; text-align: center; color: #666;">
                            You have no invoices.
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>

{include file="common/footer.tpl"}
