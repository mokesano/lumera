{**
 * plugins/paymethod/paypal/templates/paymentForm.tpl
 *
 * Copyright (c) 2017-2025 Wizdam Team Dev
 * Copyright (c) 2017-2025 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM HYBRID EDITION]
 * - Uses OJS Header/Footer
 * - Uses Existing Locales
 * - Uses API v2 Smart Buttons
 *}
{strip}
{assign var="pageTitle" value="plugins.paymethod.paypal"}
{include file="common/header-parts/header-payment.tpl"}
{/strip}

{* --- BLOK 1: ERROR HANDLING (ANTI-WSOD) --- *}
{if $paypalError}
    <div class="payment-details error u-mb-32" style="background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 5px;">
        <h4 class="payment-name error u-h4 u-mt-32">
            {translate key="common.error"}
        </h4>
        
        <div class="error-message errorText">
            <p>{$message}</p>
        </div>
        
        <p><a href="{url page="user"}" class="action">{translate key="common.back"}</a></p>
    </div>

{* --- BLOK 2: FORMULIR PEMBAYARAN UTAMA --- *}
{else}

    {* Tabel Detail - Menggunakan CSS dan Translate Key asli *}
    <section class="payment-details u-mb-32">
        <figure class="u-js-hide">
            <img src="{$baseUrl}/plugins/paymethod/paypal/images/paypal_cards.png" alt="paypal" />
        </figure>

{* --- [WIZDAM UX] KOTAK INFORMASI INVOICE (MULTI-ITEM STRUCTURE) --- *}
{if $invoiceArticleId}
<div class="wizdam-invoice-wrapper" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 30px; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;">
    
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
                <span style="color: #64748b; padding-bottom: 4px;">{translate key="plugins.paymethod.paypal.invoice.invoiceNumber"}:</span>
                <span style="color: #0f172a; font-weight: 700;">{$invoiceNumber|escape}</span>
            </div>
        </div>
    </div>

    {* --- ROW: DITAGIHKAN KEPADA & INFO INVOICE --- *}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div style="width: 60%;">
            <div style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">
                {translate key="plugins.paymethod.paypal.invoice.billedTo"}
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
                    <td style="color: #64748b; padding-bottom: 4px;">{translate key="plugins.paymethod.paypal.invoice.invoiceCode"}:</td>
                    <td style="color: #0f172a; font-weight: 700;">{$invoiceCode|escape}</td>
                </tr>
                <tr>
                    <td style="color: #64748b; padding-bottom: 4px;">{translate key="plugins.paymethod.paypal.invoice.dateBilled"}:</td>
                    <td style="color: #0f172a; font-weight: 700;">{$invoiceDateBilled|escape}</td>
                </tr>
                <tr>
                    <td style="color: #ea580c; padding-bottom: 4px;">{translate key="plugins.paymethod.paypal.invoice.dateDue"}:</td>
                    <td style="color: #ea580c; font-weight: 700;">{$invoiceDateDue|escape}</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="wizdam-invoice-details" style="display: flex; flex-direction: column; gap: 12px;">
        <div class="article-details">
            <div class="u-h5 u-mb-16">
                {translate key="plugins.paymethod.paypal.invoice.details"}
            </div>
            {* --- DETAIL ARTIKEL --- *}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-weight: 600; width: 30%; flex-shrink: 0;">{translate key="article.title"}</span>
                <span style="color: #0f172a; text-align: right; line-height: 1.4; font-size: 1.27em;">{$invoiceArticleTitle|escape}</span>
            </div>
            {* TUGAS KETIGA: MENAMPILKAN PENULIS *}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-weight: 600; width: 30%; flex-shrink: 0;">{translate key="article.authors"}</span>
                <span style="color: #475569; text-align: right; line-height: 1.4; font-style: italic; font-size: 1.17em;">{$invoiceAuthors|escape}</span>
            </div>
        </div>
        
        {* --- AREA RINCIAN BIAYA --- *}
        <div class="details-payment">
            <div class="payment-name u-h5">
                {translate key="plugins.paymethod.paypal.purchase.fees"}
            </div>
            <div style="background-color: #f8fafc; padding: 16px; border-radius: 6px; margin-top: 8px;">
            <div class="invoice-fees" style="font-size:1.2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;">{translate key="plugins.paymethod.paypal.invoice.feeSubmission"}</span>
                    <span style="color: #0f172a;">{$paypalParams.currency|escape} {$feeSubmission|escape}</span>
                </div>
    
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;">{translate key="plugins.paymethod.paypal.invoice.feePublication"}</span>
                    <span style="color: #0f172a;">{$paypalParams.currency|escape} {$feePublication|escape}</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;">{translate key="plugins.paymethod.paypal.invoice.feeFastTrack"}</span>
                    <span style="color: #0f172a;">{$paypalParams.currency|escape} {$feeFastTrack|escape}</span>
                </div>
    
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem; color: #16a34a;">
                    <span>{translate key="plugins.paymethod.paypal.invoice.discount"}</span>
                    <span>- {$paypalParams.currency|escape} {$discount|escape}</span>
                </div>
            </div>
                <div class="u-h5" style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 16px; border-top: 2px dashed #cbd5e1;">
                    <span style="color: #475569; font-weight: 700;">{translate key="plugins.paymethod.paypal.invoice.subtotal"}</span>
                    <span style="color: #0f172a; font-weight: 700;">{$paypalParams.currency|escape} {$subtotal|escape}</span>
                </div>
            </div>
        </div>
    
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; font-size: .9rem; color: #ea580c">
            <span style="color: #475569;">
                {translate key="plugins.paymethod.paypal.invoice.vat"} ({$taxRateLabel|escape}%)
                {if $isTaxInclusive} <span style="font-size: 0.8em;">{translate key="plugins.paymethod.paypal.invoice.vatInclusive"}</span>{/if}
            </span>
            <span style="{if $isTaxInclusive}color: #16a34a{else}color: #ea580{/if}">{$paypalParams.currency|escape} {$tax|escape}</span>
        </div>
                
        {* --- TOTAL AMOUNT --- *}
        <div style="display: flex; justify-content: space-between; align-items: center;padding: 0 16px;">
            <span style="color: #0f172a; font-size: 1rem; font-weight: 700;">
                {translate key="plugins.paymethod.paypal.invoice.total"}
                {if $isTaxInclusive} + <span style="font-size: 0.8em;">{translate key="plugins.paymethod.paypal.invoice.vatInclusive"}</span>{/if}
            </span>
            <span style="font-size: 1rem; font-weight: 700;">
                {$paypalParams.currency|escape} {$finalAmount|escape}
            </span>
        </div>

    </div>
</div>
{/if}
    </section>
        
    <div class="amount" style="border-top: 1px dotted;border-bottom: 1px solid;">
        <div class="payment-amount u-h5" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 26px;">
            <span class="payment-amount-fee bold" style="font-size: 1rem;text-transform: uppercase;">
                {translate key="plugins.paymethod.paypal.purchase.total"}
            </span>
            <span class="payment-amount-count bold" style="color: #0070ba;font-size: 1.27rem; font-weight: 800;">{$paypalParams.currency} {$paypalParams.displayAmount}</span>
        </div>
    </div>
    <div class="vat-notes" style="font-size:.6rem;color:#0f172a;padding:4px;">
        {translate key="plugins.paymethod.paypal.invoice.vatNotes"}
    </div>
        
    <section class="payment-notes u-mb-32">
        <h4 class="payment-name u-h4 u-mt-32 u-mb-24"></h4>
        <div class="payment-notes u-mb-24" style="border: 2px dotted #ffc439;padding: 24px;border-radius: 17px;background-color: #f8fafc;">
            <div class="payment-name u-h5 u-mb-16">{$paypalParams.itemName|escape} {translate key="common.notes"}</div>  
            <div class="payment-description">
                {$paypalParams.itemDesc|nl2br}
            </div>
        </div>
    </section>
    
    <section class="payment-call">
        <table class="data u-js-hide" width="100%">
            <tr>
                <td class="label" width="20%">{translate key="plugins.paymethod.paypal.purchase.title"}</td>
                <td class="value" width="80%">
                    <strong>{$paypalParams.itemName|escape}</strong>
                    {if $paypalParams.itemDesc}
                        <br/><span style="font-size: 0.9em; color: #666;">{$paypalParams.itemDesc|nl2br}</span>
                    {/if}
                </td>
            </tr>
            <tr>
                <td class="label" width="20%">{translate key="plugins.paymethod.paypal.purchase.fee"}</td>
                <td class="value" width="80%">
                    <strong style="font-size: 1.2em; color: #0070ba;">
                        {$paypalParams.currency} {$paypalParams.amount}
                    </strong>
                </td>
            </tr>
        </table>
    
        <div class="warning-message alert alert-info u-mb-48">
            <p>{translate key="plugins.paymethod.paypal.warning"}</p>
        </div>
        
        {* --- SMART BUTTONS (Pengganti tombol Continue lama) --- *}
        <div id="paypal-button-container" class="paypal-button" style="max-width: 350px; margin-top: 27px;"></div>
    </section>
    
    {* Script PayPal API v2 *}
    <script src="https://www.paypal.com/sdk/js?client-id={$paypalParams.clientId}&currency={$paypalParams.currency}"></script>

    <script>
    {literal}
        paypal.Buttons({
            style: {
                layout: 'vertical',
                color:  'gold',
                shape:  'rect',
                label:  'pay'
            },
            
            // Setup Transaksi
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '{$paypalParams.amount}'
                        },
                        description: '{$paypalParams.itemName|escape:"javascript"}'
                    }]
                });
            },
            
            // Jika Pembayaran Berhasil (Approved)
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Redirect ke ReturnURL yang sudah disiapkan OJS
                    // Ini akan memicu pencatatan pembayaran di sisi OJS
                    window.location.href = "{$paypalParams.returnUrl|escape:'javascript'}";
                });
            },
            
            // Jika Error Koneksi
            onError: function (err) {
                console.error('PayPal Error:', err);
                alert('{translate key="common.error"} (PayPal Connection)');
            }
        }).render('#paypal-button-container');
    {/literal}
    </script>

{/if}

{include file="common/footer-parts/footer-user.tpl"}