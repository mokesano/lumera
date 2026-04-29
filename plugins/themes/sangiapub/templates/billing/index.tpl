{strip}
{* pageTitle sudah di-assign secara dinamis dari Handler, jadi tidak perlu assign manual di sini *}
{include file="common/header.tpl"}
{/strip}

<div class="wizdam-billing-dashboard">
    
    {* [WIZDAM UX] Menambahkan Tab Navigasi Sederhana karena Handler mengirimkan $activeTab *}
    <ul style="list-style: none; padding: 0; display: flex; border-bottom: 1px solid #ccc; margin-bottom: 20px;">
        <li style="margin-right: 15px; border-bottom: {if $activeTab == 'pending'}3px solid #0056b3{else}none{/if};">
            <a href="{url page="billing" op="index"}" style="text-decoration: none; color: {if $activeTab == 'pending'}#0056b3{else}#666{/if}; font-weight: bold; padding: 5px 10px; display: block;">Active Invoices</a>
        </li>
        <li style="margin-right: 15px; border-bottom: {if $activeTab == 'history'}3px solid #0056b3{else}none{/if};">
            <a href="{url page="billing" op="history"}" style="text-decoration: none; color: {if $activeTab == 'history'}#0056b3{else}#666{/if}; font-weight: bold; padding: 5px 10px; display: block;">History</a>
        </li>
    </ul>

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
                    
                    {* [WIZDAM HASH BRIDGE] Membuat parameter URL [hash]-[id] secara dinamis *}
                    {assign var="invId" value=$invoice->getInvoiceId()}
                    {assign var="secureHash" value=$hashService->generateHash('invoice', $invId)}
                    {assign var="securePath" value="`$secureHash`-`$invId`"}

                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">#{$invId}</td>
                        
                        <td style="padding: 10px;">{$invoice->getData('feeType')|escape}</td>
                        
                        <td style="padding: 10px;">{$invoice->getData('dateBilled')|date_format:"%d %b %Y"}</td>
                        
                        <td style="padding: 10px;">
                            {* Menyesuaikan dengan standar status string dari WIZDAM Service *}
                            {if $invoice->getData('status') === 'PAID' || $invoice->getData('datePaid') != ''}
                                <span class="wizdam-badge badge-success" style="color: #28a745; font-weight: bold;">PAID</span>
                            {else}
                                <span class="wizdam-badge badge-warning" style="color: #dc3545; font-weight: bold;">UNPAID</span>
                            {/if}
                        </td>
                        
                        <td style="padding: 10px;">{$invoice->getData('currencyCode')|escape} {$invoice->getData('amount')|number_format:2}</td>
                        
                        <td style="padding: 10px;">
                            {if $invoice->getData('status') !== 'PAID'}
                            {* Menggunakan page="billing" dan mengoper variabel $securePath *}
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
                            You have no active invoices.
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>

{include file="common/footer.tpl"}