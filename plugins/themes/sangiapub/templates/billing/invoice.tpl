{**
 * templates/billing/invoice.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Publishing
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * Billing the Author / Invoice Details.
 *}
{strip}
    {include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wizdam-invoice-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    .wi-header { display: flex; justify-content: space-between; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
    .wi-publisher-logo { max-height: 50px; margin-bottom: 8px; }
    .wi-journal h2 { margin: 0; font-size: 1.2rem; font-weight: bold; color: #1a4f8b; }
    .wi-journal p { margin: 2px 0 0 0; font-size: 1em; color: #666; line-height: 1;}
    .wi-title h1 { margin: 0; font-size: 36px; font-weight: 800; letter-spacing: 2px; color: #222; text-align: right; }
    .wi-title .inv-num { font-size: 14px; color: #555; text-align: right; }

    .wi-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; font-size: 13px; }
    .wi-meta-box { background: #f9f9f9; padding: 15px; border-radius: 4px; }
    .wi-meta-box strong { display: block; margin-bottom: 5px; font-size: 11px; text-transform: uppercase; color: #777; }
    .wi-meta-box.right-align table { width: 100%; }
    .wi-meta-box.right-align td { padding: 4px 0; border-bottom: 1px solid #eee; }
    .wi-meta-box.right-align tr:last-child td { border-bottom: none; }

    .wi-section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px; }

    .wi-details-table { width: 100%; font-size: 13px; margin-bottom: 20px; border-collapse: collapse; }
    .wi-details-table td { padding: 8px 0; vertical-align: top; }
    .wi-details-table td:first-child { width: 150px; color: #666; }
    .wi-details-table td:last-child { font-weight: 500; }

    .wi-fees-table { width: 100%; font-size: 13px; border-collapse: collapse; margin-bottom: 20px; }
    .wi-fees-table td { padding: 10px 5px; border-bottom: 1px dotted #ccc; }
    .wi-fees-table td.amount { text-align: right; }
    .wi-fees-table tr.subtotal td { font-weight: bold; border-top: 1px solid #333; border-bottom: none; padding-top: 15px; }
    .wi-fees-table tr.tax td { color: #28a745; border-bottom: 1px solid #333; padding-bottom: 15px; }

    .wi-total-box { display: flex; justify-content: space-between; align-items: center; background: #f4fdf8; padding: 15px 20px; border-left: 5px solid #28a745; margin-top: 10px; }
    .wi-total-box .label { font-size: 16px; font-weight: bold; text-transform: uppercase; }
    .wi-total-box .amount { font-size: 24px; font-weight: bold; color: #28a745; }

    .wi-notes-box { border: 1px solid #ffeeba; background: #fffaf0; padding: 15px; border-radius: 4px; font-size: 12px; margin-top: 30px; }

    .wi-actions { margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .wi-pay-btn { background: #fff; border: 1px solid #ccc; padding: 15px; border-radius: 4px; cursor: pointer; text-align: center; transition: 0.2s; }
    .wi-pay-btn:hover { border-color: #1a4f8b; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .wi-pay-btn-disabled { opacity: 0.45; cursor: not-allowed; }
    .wi-pay-btn-disabled:hover { border-color: #ccc; box-shadow: none; }
    .wi-badge-recommended { display: inline-block; font-size: 10px; background: #28a745; color: #fff; padding: 2px 6px; border-radius: 10px; vertical-align: middle; }

    .wi-manual-instructions-box { display:none; margin-top:20px; padding:20px; background:#fffaf0; border:1px solid #ffeeba; border-radius:4px; }
    .wi-manual-instructions-box h3 { margin-top:0; color:#856404; }
    .wi-manual-instructions-box h4 { margin: 0 0 8px; color:#856404; font-size: 13px; text-transform: uppercase; }
    .wi-manual-instructions-text { white-space: pre-line; line-height:1.6; font-size: 15px; font-weight: 600; }
    .wi-manual-instructions-divider { margin: 15px 0; border: none; border-top: 1px solid #ffeeba; }
    .wi-manual-instructions-note { margin: 0; font-size:13px; color:#666; }

    .action-button { margin-top: 20px; text-align: right; display: flex; gap: 10px; justify-content: flex-end; align-items: center; }
    .action-button a { text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 4px; font-weight: 700; transition: 0.2s; }
    .action-button .download { border: 1px solid #1a4f8b; color: #fff; background-color: #1a4f8b; }
    .action-button .download:hover { background-color: #297ad6; border-color: #297ad6; }
    .action-button .cancel-action { border: 1px solid #999; background-color: #999; color: #f5f5dc; }
    .action-button .cancel-action:hover { border-color: #dc3545; background-color: #e60000; color: #fff; }
</style>
{/literal}

<div class="wizdam-invoice-wrapper">

    {* ===== HEADER ===== *}
    <div class="wi-header">
        <div class="wi-journal">
            {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo">{/if}
            <h2>{if $journal}{$journal->getLocalizedTitle()|escape}{else}{$publisher.name|escape}{/if}</h2>
            <h4>{$publisher.legalEntity|escape|nl2br}</h4>
            {if $publisher.address}<p>{$publisher.address|nl2br}</p>{/if}
        </div>
        <div class="wi-title">
            <h1>{translate key="billing.invoiceLabel"}</h1>
            <div class="inv-num">{translate key="billing.invoiceNumberLabel"}: <strong>{$wizdamInvoiceNumber|escape}</strong></div>
        </div>
    </div>

    {* ===== META GRID ===== *}
    <div class="wi-meta-grid">
        <div class="wi-meta-box">
            <strong>{translate key="billing.billedTo"}:</strong>
            <div style="font-size: 15px; font-weight: bold; margin-bottom: 4px;">{$authorName|escape}</div>
            <div>{$authorAffiliation|nl2br}</div>
            <div style="margin-top: 5px;">{translate key="billing.email"}: <span style="color: #0056b3;">{$authorEmail|escape}</span></div>
        </div>
        <div class="wi-meta-box right-align">
            <table>
                <tr>
                    <td style="color:#666;">{translate key="billing.invoiceCode"}:</td>
                    <td style="text-align:right; font-weight:bold;">{$wizdamInvoiceCode|escape}</td>
                </tr>
                <tr>
                    <td style="color:#666;">{translate key="billing.dateBilled"}:</td>
                    <td style="text-align:right; font-weight:bold;">{$dateBilled|date_format:"%d %B %Y"}</td>
                </tr>
                <tr>
                    <td style="color:#666;">{translate key="billing.statusLabel"}:</td>
                    <td style="text-align:right; font-weight:bold; color:{if $isPaid}#28a745{else}#dc3545{/if};">
                        {if $isPaid}{translate key="billing.statusPaid"}{else}{translate key="billing.statusUnpaid"}{/if}
                    </td>
                </tr>
                {if $isPaid && $datePaid}
                <tr>
                    <td style="color:#666;">{translate key="billing.datePaid"}:</td>
                    <td style="text-align:right; font-weight:bold; color:#28a745;">{$datePaid|date_format:"%d %B %Y"}</td>
                </tr>
                {/if}
            </table>
        </div>
    </div>

    {* ===== ARTICLE DETAILS ===== *}
    <div class="wi-section-title">{translate key="billing.articleDetails"}</div>
    <table class="wi-details-table">
        <tr>
            <td>{translate key="billing.titleLabel"}</td>
            <td>{if $articleTitle}{$articleTitle|strip_unsafe_html|nl2br}{else}<em>{translate key="billing.noArticleLinked"}</em>{/if}</td>
        </tr>
        <tr>
            <td>{translate key="billing.descriptionLabel"}</td>
            <td style="font-style: italic; color: #555;">{$localizedFeeName|escape}</td>
        </tr>
        {if $paymentMethod}
        <tr>
            <td>{translate key="billing.paymentVia"}</td>
            <td>{$paymentMethod|escape}</td>
        </tr>
        {/if}
    </table>

    {* ===== FEES BREAKDOWN ===== *}
    <div class="wi-section-title">{translate key="billing.feesBreakdown"}</div>
    <table class="wi-fees-table">
        {if $formattedBaseFee != "0.00"}
        <tr>
            <td>{translate key="billing.baseFee"}</td>
            <td class="amount">{$currencyCode} {$formattedBaseFee}</td>
        </tr>
        {/if}
        {if $formattedFastTrackFee != "0.00"}
        <tr>
            <td>{translate key="billing.fastTrackFee"}</td>
            <td class="amount">{$currencyCode} {$formattedFastTrackFee}</td>
        </tr>
        {/if}
        {if $formattedDiscount != "0.00"}
        <tr>
            <td style="color: #28a745;">{translate key="billing.discount"}</td>
            <td class="amount" style="color: #28a745;">- {$currencyCode} {$formattedDiscount}</td>
        </tr>
        {/if}
        <tr class="subtotal">
            <td>{translate key="billing.subtotal"}</td>
            <td class="amount">{$currencyCode} {$formattedSubtotal}</td>
        </tr>
        <tr class="tax">
            <td>{translate key="billing.taxVat"} ({$taxPercentage}%) {if $isTaxInclusive}{translate key="billing.taxInclusive"}{else}{translate key="billing.taxExclusive"}{/if}</td>
            <td class="amount">{$currencyCode} {$formattedTax}</td>
        </tr>
    </table>

    <div class="wi-total-box">
        <div class="label" style="margin-top: 0;">{translate key="billing.amountDue"}</div>
        <div class="amount">{$currencyCode} {$formattedAmount}</div>
    </div>
    <div style="font-size: 10px; color: #888; text-align: center; margin-top: 5px;">
        {translate key="billing.taxDisclaimer"}
    </div>

    {* ===== PAYMENT OPTIONS (hanya untuk tagihan yang belum lunas) ===== *}
    {if !$isPaid}
    <div class="wi-actions">
        {foreach from=$availablePaymentMethods item=method}
        <button type="button" class="wi-pay-btn {if !$method.is_configured}wi-pay-btn-disabled{/if}"
                {if !$method.is_configured}disabled{/if}
                onclick="processPayment('{$method.id}', 'all')">
            <strong>{$method.label|escape}</strong>{if $method.is_recommended} <span class="wi-badge-recommended">{translate key="billing.recommended"}</span>{/if}
            {if !$method.is_configured}<br><small>{translate key="billing.notAvailable"}</small>{/if}
        </button>
        {/foreach}
    </div>
    <div id="loadingIndicator" style="display: none; text-align: center; padding: 15px; color: #1a4f8b; font-weight: bold;"></div>

    <div id="manualInstructionsBox" class="wi-manual-instructions-box">
        <h3>{translate key="billing.manualInstructionsTitle"}</h3>
        <h4>{translate key="billing.manualTransferInstructionsLabel"}</h4>
        <div id="manualInstructionsText" class="wi-manual-instructions-text"></div>
        <hr class="wi-manual-instructions-divider">
        <p class="wi-manual-instructions-note">
            <strong>{translate key="billing.manualNoteTitle"}:</strong> {translate key="billing.manualNote"}
        </p>
    </div>
    {/if}

    {* ===== ACTION BUTTONS ===== *}
    <div class="action-button">
        {if !$isPaid}
        <a class="cancel-action"
           href="{url page="billing" op="cancel" path=$securePath}"
           onclick="return confirm('{translate key="billing.cancelConfirm"|escape:"javascript"}');">
            <i class="icon-remove"></i> {translate key="billing.cancelBilling"}
        </a>
        {/if}
        <a class="download" href="{$pdfDownloadUrl|escape}" target="_blank">
            <i class="icon-download"></i> {translate key="billing.downloadInvoice"}
        </a>
    </div>

</div>

{* ===== JAVASCRIPT PAYMENT GATEWAY ===== *}
<script>
    const csrfToken = "{$csrfToken|escape}";
    const payUrl    = "{url page="billing" op="pay" path=$securePath}";
    const confirmManualUrl = "{url page="billing" op="confirmManual" path=$securePath}";
    const manualInstructionsFallback = "{translate key="billing.manualInstructionsFallback"|escape:"javascript"}";
    const connectionErrorMessage = "{translate key="billing.connectionError"|escape:"javascript"}";
    const gatewayUnknownMessage = "{translate key="billing.gatewayError"|escape:"javascript"}";
    const paymentFailedPrefix = "{translate key="billing.paymentFailed"|escape:"javascript"}";
function processPayment(method, paymentType) {
    const indicator = document.getElementById('loadingIndicator');
    // [FIX] Teks dibedakan sesuai konteks nyata -- Manual TIDAK memproses
    // apapun yang perlu "diamankan" (tidak ada uang lewat aplikasi ini,
    // cuma update status + kirim email ke admin), beda dari gateway
    // (Midtrans/Xendit/PayPal) yang genuinely memanggil API pihak ketiga.
    indicator.textContent = (method === 'manual')
        ? manualPreparingMessage
        : gatewayProcessingMessage;
    indicator.style.display = 'block';
    ...
    {literal}
    function processPayment(method, paymentType) {
        document.getElementById('loadingIndicator').style.display = 'block';

        if (method === 'manual') {
            const manualData = new URLSearchParams();
            manualData.append('csrfToken', csrfToken);
            manualData.append('ajax', '1');
            fetch(confirmManualUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: manualData.toString()const manualPreparingMessage = "{translate key="billing.manualPreparing"|escape:"javascript"}";
const gatewayProcessingMessage = "{translate key="billing.processingPayment"|escape:"javascript"}";
            })
            .then(response => response.json())
            .then(res => {
                document.getElementById('loadingIndicator').style.display = 'none';
                if (res.status === 'success') {
                    const box = document.getElementById('manualInstructionsBox');
                    const text = document.getElementById('manualInstructionsText');
                    text.textContent = (res.data.instructions && res.data.instructions.trim() !== '')
                        ? res.data.instructions
                        : manualInstructionsFallback;
                    box.style.display = 'block';
                    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(function() {
                document.getElementById('loadingIndicator').style.display = 'none';
                alert(connectionErrorMessage);
            });
            return;
        }

        const formData = new URLSearchParams();
        formData.append('csrfToken', csrfToken);
        formData.append('ajax', '1');
        formData.append('method', method);
        formData.append('payment_type', paymentType);

        fetch(payUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(res => {
            document.getElementById('loadingIndicator').style.display = 'none';
            if (res.status === 'success') {
                if (res.data.gateway === 'midtrans' && res.data.token) {
                    window.snap.pay(res.data.token, {
                        onSuccess: function() { window.location.reload(); },
                        onPending: function() { window.location.reload(); },
                        onError:   function(result) { alert(paymentFailedPrefix + ': ' + result.status_message); }
                    });
                } else if ((res.data.gateway === 'xendit' || res.data.gateway === 'paypal') && res.data.url) {
                    window.location.href = res.data.url;
                } else {
                    alert(gatewayUnknownMessage);
                }
            } else {
                alert('Error: ' + res.message);
            }
        })
        .catch(function() {
            document.getElementById('loadingIndicator').style.display = 'none';
            alert(connectionErrorMessage);
        });
    }
    {/literal}
</script>

{include file="common/footer.tpl"}