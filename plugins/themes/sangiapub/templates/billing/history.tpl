{strip}
{* pageTitle sudah di-assign secara dinamis dari Handler *}
{include file="common/header.tpl"}
{/strip}

<div class="wizdam-billing-dashboard">
    
    {* [WIZDAM UX] Tab Navigasi - Tab 'History' yang aktif *}
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
                    
                    {* [WIZDAM HASH BRIDGE] Menggunakan getInvoiceId() sesuai perbaikan sebelumnya *}
                    {assign var="invId" value=$invoice->getInvoiceId()}
                    {assign var="secureHash" value=$hashService->generateHash('invoice', $invId)}
                    {assign var="securePath" value="`$secureHash`-`$invId`"}

                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">#{$invId}</td>
                        
                        <td style="padding: 10px;">{$invoice->getData('feeType')|escape}</td>
                        
                        <td style="padding: 10px;">{$invoice->getData('dateBilled')|date_format:"%d %b %Y"}</td>
                        
                        <td style="padding: 10px;">
                            {* Menampilkan Status History: PAID atau CANCELLED *}
                            {if $invoice->getData('status') === 'PAID' || $invoice->getData('datePaid') != ''}
                                <span class="wizdam-badge badge-success" style="color: #28a745; font-weight: bold;">PAID</span>
                                <div style="font-size: 11px; color: #666; margin-top: 4px;">{$invoice->getData('datePaid')|date_format:"%d %b %Y"}</div>
                            {elseif $invoice->getData('status') === 'CANCELLED'}
                                <span class="wizdam-badge badge-secondary" style="color: #6c757d; font-weight: bold;">CANCELLED</span>
                            {else}
                                <span class="wizdam-badge" style="color: #666; font-weight: bold;">{$invoice->getData('status')|escape}</span>
                            {/if}
                        </td>
                        
                        <td style="padding: 10px;">{$invoice->getData('currencyCode')|escape} {$invoice->getData('amount')|number_format:2}</td>
                        
                        <td style="padding: 10px;">
                            {* Tidak ada tombol Cancel Bill di History. Hanya View Details untuk mencetak Kuitansi (PDF) *}
                            <a href="{url page="billing" op="invoice" path=$securePath}" 
                               class="wizdam-btn wizdam-btn-primary" 
                               style="text-decoration: none; background: #0056b3; color: white; padding: 5px 10px; border-radius: 4px; font-size: 14px;">
                               View Receipt
                            </a>
                        </td>
                    </tr>
                {foreachelse}
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px; text-align: center; color: #666;">
                            You have no invoice history.
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>

{include file="common/footer.tpl"}