{**
 * plugins/paymethod/manual/templates/paymentForm.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Manual payment page
 *}
{strip}
{assign var="pageTitle" value="plugins.paymethod.manual"}
{include file="common/header-parts/header-payment.tpl"}
{/strip}

<div id="paymentForm" class="manual-payment">
    <section class="payment-details u-mb-32" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 30px; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;">
    
    {* --- HEADER KOP SURAT --- *}
    <div style="border-bottom: 3px solid #0f172a; padding-bottom: 16px; margin-bottom: 24px;display: flex;justify-content: space-between;align-items: flex-start;">
        <div class="publisher-id" style="color: #475569; font-size: 0.9rem; font-weight: 600; margin-top: 4px;">
            <h5 class="publisher-name">{$invoiceSiteTitle|escape}</h5>
            <div class="publisher-owner">Owner PT. Sangia Research Media and Publishing</div>
            <div class="legal-number">
                <span class="legal-title">Legal</span>
                <span class="legal-number">Dirjen AHU No. AHU-050003.AH.01.30.Tahun 2022.</span>
            </div>
            <div class="nib-number u-js-hide">
                <span class="nib-title">NIB Number</span>
                <span class="nib-number">1111220205313</span>
                <span class="certificate">Certificate Number</span>
                <span class="certificate-number">11112202053130002</span>
            </div>
        </div>
        <div class="invice-id" style="text-align: center;">
            <h2 style='margin: 0; color: #0f172a; font-size: 2.7rem; font-weight: 800; text-transform: uppercase;font-family: Bliss Bold,"Open Sans",Calibri,"Helvetica Neue",Arial,sans-serif;line-height: 1;'>
                INVOICE
            </h2>
            <div class="invoice-number" style="font-size: .75rem;">
                <span style="color: #64748b; padding-bottom: 4px;">{translate key="plugins.paymethod.manual.invoice.invoiceNumber"}:</span>
                <span style="color: #0f172a; font-weight: 700;">{$invoiceNumber|escape}</span>
            </div>
        </div>
    </div>

{* --- [WIZDAM UX] KOTAK INFORMASI INVOICE (MULTI-ITEM STRUCTURE) --- *}
{if $invoiceArticleId}
<div class="wizdam-invoice-wrapper" >

    {* --- ROW: DITAGIHKAN KEPADA & INFO INVOICE --- *}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div style="width: 65%;">
            <div style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">
                {translate key="plugins.paymethod.manual.invoice.billedTo"}
            </div>
            <div style="color: #0f172a; font-weight: 700; font-size: 1rem;">{$invoiceBilledName|escape}</div>
            
            {* Loop Multi-Afiliasi *}
            {foreach from=$invoiceBilledAffiliations item=affiliation}
                <div style="color: #475569; font-size: 0.85rem; line-height: 1.4; margin-top: 2px;">{$affiliation|escape}</div>
            {/foreach}
            
            <div style="color: #0ea5e9; font-size: 0.85rem; margin-top: 4px;">{$invoiceBilledEmail|escape}</div>
        </div>
        
        <div style="width: 35%; text-align: right;">
            <table style="width: 100%; font-size: 0.85rem !important; text-align: right;" border="0">
                <tr>
                    <td style="color: #64748b; padding-bottom: 4px;">{translate key="plugins.paymethod.manual.invoice.invoiceCode"}:</td>
                    <td style="color: #0f172a; font-weight: 700;">{$invoiceCode|escape}</td>
                </tr>
                <tr>
                    <td style="color: #64748b; padding-bottom: 4px;">{translate key="plugins.paymethod.manual.invoice.dateBilled"}:</td>
                    <td style="color: #0f172a; font-weight: 700;">{$invoiceDateBilled|escape}</td>
                </tr>
                <tr>
                    <td style="color: #ea580c; padding-bottom: 4px;">{translate key="plugins.paymethod.manual.invoice.dateDue"}:</td>
                    <td style="color: #ea580c; font-weight: 700;">{$invoiceDateDue|escape}</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="wizdam-invoice-details" style="display: flex; flex-direction: column; gap: 12px;">
        <div class="article-details">
            <div class="u-h5 u-mb-16">
                {translate key="plugins.paymethod.manual.invoice.details"}
            </div>
            {* --- DETAIL ARTIKEL --- *}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-weight: 600; width: 30%; flex-shrink: 0;">{translate key="article.title"}</span>
                <span style="color: #0f172a; text-align: right; line-height: 1.4; font-size: 1.27em;">{$invoiceArticleTitle|strip_unsafe_html|nl2br}</span>
            </div>
            {* --- MENAMPILKAN PENULIS --- *}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-weight: 600; width: 30%; flex-shrink: 0;">{translate key="article.authors"}</span>
                <span style="color: #475569; text-align: right; line-height: 1.4; font-style: italic; font-size: 1.17em;">{$invoiceAuthors|escape}</span>
            </div>
        </div>
        
        {* --- AREA RINCIAN BIAYA --- *}
        <div class="details-payment">
            <div class="payment-name u-h5">
                {translate key="plugins.paymethod.manual.purchase.fees"}
            </div>
            <div style="background-color: #f8fafc; padding: 16px; border-radius: 6px; margin-top: 8px;">
            <div class="invoice-fees" style="font-size:1.2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;">{translate key="plugins.paymethod.manual.invoice.feeSubmission"}</span>
                    <span style="color: #0f172a;">{$itemCurrencyCode|escape} {$feeSubmission|escape}</span>
                </div>
    
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;">{translate key="plugins.paymethod.manual.invoice.feePublication"}</span>
                    <span style="color: #0f172a;">{$itemCurrencyCode|escape} {$feePublication|escape}</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;">{translate key="plugins.paymethod.manual.invoice.feeFastTrack"}</span>
                    <span style="color: #0f172a;">{$itemCurrencyCode|escape} {$feeFastTrack|escape}</span>
                </div>
    
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem; color: #16a34a;">
                    <span>{translate key="plugins.paymethod.manual.invoice.discount"}</span>
                    <span>- {$itemCurrencyCode|escape} {$discount|escape}</span>
                </div>
            </div>
                <div class="u-h5" style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 16px; border-top: 2px dashed #cbd5e1;">
                    <span style="color: #475569; font-weight: 700;">{translate key="plugins.paymethod.manual.invoice.subtotal"}</span>
                    <div class="sub-total u-h5" style="display: flex; justify-content: space-between;">
                        <span style="color: #0f172a; font-weight: 700;">
                            {$itemCurrencyCode|escape} {$subtotal|escape}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px;font-size: .9rem; color: #ea580c">
            <span style="color: #475569;">{translate key="plugins.paymethod.manual.invoice.vat"} ({$taxRateLabel|escape}%){if $isTaxInclusive}<span> {translate key="plugins.paymethod.manual.invoice.vatInclusive"}</span>{/if}</span>
            <span style="{if $isTaxInclusive}color: #16a34a{else}color: #ea580{/if}">{$itemCurrencyCode|escape} {$tax|escape}</span>
        </div>
                
        {* --- TOTAL AMOUNT --- *}
        <div style="display: flex; justify-content: space-between; align-items: center;padding: 0 16px;">
            <span style="color: #0f172a; font-size: 1rem; font-weight: 700;">
                {translate key="plugins.paymethod.manual.invoice.total"}
                {if $isTaxInclusive} <span>({translate key="plugins.paymethod.manual.invoice.vatInclusive"})</span>{/if}
            </span>
            <span style="font-size: 1rem; font-weight: 700;">
                {$itemCurrencyCode|escape} {$finalAmount|escape}
            </span>
        </div>

    </div>
</div>
{/if}
    </section>
    
    {if $finalAmount}
    <section class="amount-due" style="border-top: 1px dotted;border-bottom: 1px solid;">
        <div class="payment-amount u-h5" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 26px;">
            <span class="payment-amount-fee bold" style="font-size: 1rem;text-transform: uppercase;">{translate key="plugins.paymethod.manual.purchase.total"}</span>
            <span class="payment-amount-count bold" style="color: #16a34a;font-size: 1.27rem; font-weight: 800;">{$itemCurrencyCode|escape} {$finalAmount|escape}</span>
        </div>
    </section>
    <div class="vat-notes" style="font-size:.6rem;color:#0f172a;padding:4px;">
        {translate key="plugins.paymethod.manual.invoice.vatNotes"}
    </div>
    {/if}
        
    <section class="payment-notes u-mb-48">
        <h4 class="payment-name u-h4 u-mt-32 u-mb-24"></h4>
        <div class="payment-notes u-mb-24" style="border: 2px dotted #ffc439;padding: 24px;border-radius: 17px;background-color: #f8fafc;">
            {if $itemDescription}
            <div class="manual-payment">
                <div class="payment-name u-h5 u-mb-16">
                    {$itemName|escape} {translate key="common.notes"}
                </div>
                <div class="payment-description">
                    {$itemDescription|nl2br}
                </div>
            </div>
            {/if}
            <div class="manual-payment-instruction  u-mt-32">
                <div class="payment-name u-h5 u-mb-16">
                    {translate key="plugins.paymethod.manual.purchase.instructions"}
                </div>
                <div class="payment-instruction">
                    <p>{$manualInstructions|nl2br}</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="payment-call">
        <table class="data u-js-hide" width="100%">
        	<tr>
        		<td class="label" width="20%">{translate key="plugins.paymethod.manual.purchase.title"}</td>
        		<td class="value" width="80%"><strong>{$itemName|escape}</strong></td>
        	</tr>
        	{if $itemDescription}
        	<tr>
        		<td colspan="2">{$itemDescription|nl2br}</td>
        	</tr>
        	{/if}
        	{if $itemAmount}
        		<tr>
        			<td class="label" width="20%">{translate key="plugins.paymethod.manual.purchase.fee"}</td>
        			<td class="value" width="80%"><strong>{$itemAmount|string_format:"%.2f"}{if $itemCurrencyCode} ({$itemCurrencyCode|escape}){/if}</strong></td>
        		</tr>
        	{/if}
        </table>
        <p class="u-js-hide">{$manualInstructions|nl2br}</p>
        
        <p>
            <a href="{url page="payment" op="plugin" path="ManualPayment"|to_array:"notify":$queuedPaymentId|escape}" class="action u-hide">{translate key="plugins.paymethod.manual.sendNotificationOfPayment"}</a>
            
            <input type="button" value="{translate key="plugins.paymethod.manual.sendNotificationOfPayment"}" class="button" onclick="document.location.href='{url page="payment" op="plugin" path="ManualPayment"|to_array:"notify":$queuedPaymentId|escape}'">
        </p>
    </section>
</div>

{include file="common/footer-parts/footer-user.tpl"}
