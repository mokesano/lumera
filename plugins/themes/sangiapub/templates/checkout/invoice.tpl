{strip}
{assign var="pageTitle" value="checkout.invoiceDetail"}
{include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wizdam-invoice-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    .wi-header { display: flex; justify-content: space-between; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
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
    
    .wi-section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px;}
    
    .wi-details-table { width: 100%; font-size: 13px; margin-bottom: 20px; border-collapse: collapse; }
    .wi-details-table td { padding: 8px 0; vertical-align: top; }
    .wi-details-table td:first-child { width: 150px; color: #666; }
    .wi-details-table td:last-child { font-weight: 500; }
    
    .wi-fees-table { width: 100%; font-size: 13px; border-collapse: collapse; margin-bottom: 20px; }
    .wi-fees-table td { padding: 10px 5px; border-bottom: 1px dotted #ccc; }
    .wi-fees-table td.amount { text-align: right; }
    .wi-fees-table tr.subtotal td { font-weight: bold; border-top: 1px solid #333; border-bottom: none; padding-top: 15px;}
    .wi-fees-table tr.tax td { color: #28a745; border-bottom: 1px solid #333; padding-bottom: 15px;}
    
    .wi-total-box { display: flex; justify-content: space-between; align-items: center; background: #f4fdf8; padding: 15px 20px; border-left: 5px solid #28a745; margin-top: 10px; }
    .wi-total-box .label { font-size: 16px; font-weight: bold; text-transform: uppercase; }
    .wi-total-box .amount { font-size: 24px; font-weight: bold; color: #28a745; }
    
    .wi-notes-box { border: 1px solid #ffeeba; background: #fffaf0; padding: 15px; border-radius: 4px; font-size: 12px; margin-top: 30px; }
    
    .wi-actions { margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .wi-pay-btn { background: #fff; border: 1px solid #ccc; padding: 15px; border-radius: 4px; cursor: pointer; text-align: center; transition: 0.2s; }
    .wi-pay-btn:hover { border-color: #1a4f8b; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    .action-button a:hover { border-color: #1a4f8b; box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: #fff; }
    .action-button .download { text-decoration: none; display: inline-block; padding: 10px 20px; border: 1px solid #1a4f8b; color: #fff; border-radius: 4px;background-color: #1a4f8b;" }
    .action-button .cancel-action { text-decoration: none; display: inline-block; padding: 10px 20px; border: 1px solid #999; border-radius: 4px; background-color: #999; color: beige; font-weight: 700; }
    .action-button .cancel-action:hover { text-decoration: none; border: 1px solid #dc3545; color: beige; background-color: #e60000; }
    .action-button a.download:hover { background-color: #297ad6; color: #fff; }
    .action-button a.cancel-action:hover { color: #fff; }
</style>
{/literal}

<div class="wizdam-invoice-wrapper">
    <div class="wi-header">
        <div class="wi-journal">
            <h2>{if $journal}{$journal->getLocalizedTitle()|escape}{else}Sangia Research Media and Publishing{/if}</h2>
            <p>Owner PT. Sangia Research Media and Publishing</p>
            <p>Legal Dirjen AHU No. AHU-050003.AH.01.30.Tahun 2022.</p>
        </div>
        <div class="wi-title">
            <h1>INVOICE</h1>
            <div class="inv-num">Invoice Number: <strong>{$wizdamInvoiceNumber|escape}</strong></div>
        </div>
    </div>

    <div class="wi-meta-grid">
        <div class="wi-meta-box">
            <strong>Billed To:</strong>
            <div style="font-size: 15px; font-weight: bold; margin-bottom: 4px;">{$authorName|escape}</div>
            <div>{$authorAffiliation|nl2br}</div>
            <div style="margin-top: 5px;">Email: <span style="color: #0056b3;">{$authorEmail|escape}</span></div>
        </div>
        <div class="wi-meta-box right-align">
            <table>
                <tr>
                    <td style="color:#666;">Invoice Code:</td>
                    <td style="text-align:right; font-weight:bold;">{$wizdamInvoiceCode|escape}</td>
                </tr>
                <tr>
                    <td style="color:#666;">Date Billed:</td>
                    <td style="text-align:right; font-weight:bold;">{$dateBilled|date_format:"%d %B %Y"}</td>
                </tr>
                <tr>
                    <td style="color:#666;">Status:</td>
                    <td style="text-align:right; font-weight:bold; color:{if $isPaid}#28a745{else}#dc3545{/if};">
                        {if $isPaid}PAID{else}UNPAID{/if}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="wi-section-title">Article Details</div>
    <table class="wi-details-table">
        <tr>
            <td>Title</td>
            <td>{if $articleTitle}{$articleTitle|strip_unsafe_html|nl2br}{else}<em>Judul naskah belum terhubung</em>{/if}</td>
        </tr>
        <tr>
            <td>Description</td>
            <td style="font-style: italic; color: #555;">{$localizedFeeName|escape}</td>
        </tr>
    </table>

    <div class="wi-section-title">Fees Breakdown</div>
    <table class="wi-fees-table">
        {if $formattedBaseFee != "0.00"}
        <tr>
            <td>Base Fee (Submission / Publication)</td>
            <td class="amount">{$currencyCode} {$formattedBaseFee}</td>
        </tr>
        {/if}
        {if $formattedFastTrackFee != "0.00"}
        <tr>
            <td>Fast-Track Review Fee</td>
            <td class="amount">{$currencyCode} {$formattedFastTrackFee}</td>
        </tr>
        {/if}
        {if $formattedDiscount != "0.00"}
        <tr>
            <td style="color: #28a745;">Discount (Trade / Promo)</td>
            <td class="amount" style="color: #28a745;">- {$currencyCode} {$formattedDiscount}</td>
        </tr>
        {/if}
        <tr class="subtotal">
            <td>Subtotal (Taxable Amount)</td>
            <td class="amount">{$currencyCode} {$formattedSubtotal}</td>
        </tr>
        <tr class="tax">
            <td>Tax/VAT ({$taxPercentage}%) {if $isTaxInclusive}Inclusive{else}Exclusive{/if}</td>
            <td class="amount">{$currencyCode} {$formattedTax}</td>
        </tr>
    </table>

    <div class="wi-total-box">
        <div class="label" style="margin-top: 0;">Amount Due</div>
        <div class="amount">{$currencyCode} {$formattedAmount}</div>
    </div>
    <div style="font-size: 10px; color: #888; text-align: center; margin-top: 5px;">
        * Taxable Amount calculated after stated trade discounts. Tax/VAT applied per Indonesian VAT Law and OECD Guidelines.
    </div>

    {if !$isPaid}
    <div class="wi-actions">
        <button type="button" class="wi-pay-btn" onclick="processPayment('qris')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" style="height: 25px; margin-bottom: 5px;"><br>
            <strong>Scan QR Code (QRIS)</strong><br>
            <span style="font-size: 11px; color: #666;">Gopay, OVO, Dana, LinkAja</span>
        </button>
        <button type="button" class="wi-pay-btn" onclick="processPayment('bank_transfer')">
            <div style="font-size: 20px; color: #0056b3; margin-bottom: 5px;"><i class="icon-building"></i></div>
            <strong>Virtual Account (VA)</strong><br>
            <span style="font-size: 11px; color: #666;">BCA, Mandiri, BNI, BRI, Permata</span>
        </button>
        <button type="button" class="wi-pay-btn" onclick="processPayment('all')">
            <div style="font-size: 20px; color: #17a2b8; margin-bottom: 5px;"><i class="icon-credit-card"></i></div>
            <strong>Metode Lainnya</strong><br>
            <span style="font-size: 11px; color: #666;">Kartu Kredit, Retail</span>
        </button>
    </div>
    
    <div id="loadingIndicator" style="display: none; text-align: center; padding: 15px; color: #1a4f8b; font-weight: bold;">
        Memproses jalur pembayaran aman... Mohon tunggu.
    </div>
    {/if}

    <div class="wi-notes-box">
        <strong>Manual Payment Instructions</strong><br><br>
        Pembayaran manual dapat dilakukan melalui Rekening:<br>
        Rekening Nomor: <strong>3273 0669 7</strong> (Bank BNI) a.n. Rochmady<br>
        Rekening Nomor: <strong>3419 0101 0494 506</strong> (Bank BRI) a.n. Rochmady<br><br>
        <em>Konfirmasi dan Bukti transfer dikirim ke email: rochmady@sangia.org. Subjek email: {$wizdamInvoiceCode|escape}</em>
    </div>

    <div class="action-button" style="margin-top: 20px; text-align: right;">
        {if !$isPaid}
        <a class="cancel-action" href="{url page="checkout" op="cancel" path=$invoice->getInvoiceId()}" 
           onclick="return confirm('Apakah Anda yakin ingin membatalkan tagihan ini?');">
            Cancel Billing
        </a>
        {/if}
        <a class="download" href="{url page="checkout" op="download-pdf" path=$invoice->getInvoiceId()}" class="wizdam-btn wizdam-btn-outline" target="_blank" >
            <i class="icon-download"></i> Download INVOICE
        </a>
    </div>
</div>

{* Script Gateway Sama seperti sebelumnya *}
<script>
    const invoiceId = {$invoice->getInvoiceId()};
    const csrfToken = "{$csrfToken|escape}";
    const payUrl = "{url page="checkout" op="pay" path=$invoice->getInvoiceId()}";

    {literal}
    function processPayment(paymentType) {
        document.getElementById('loadingIndicator').style.display = 'block';
        const formData = new URLSearchParams();
        formData.append('csrfToken', csrfToken); formData.append('ajax', '1'); formData.append('payment_type', paymentType);

        fetch(payUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: formData.toString() })
        .then(response => response.json())
        .then(res => {
            document.getElementById('loadingIndicator').style.display = 'none';
            if (res.status === 'success' && res.data.gateway === 'midtrans') {
                window.snap.pay(res.data.token, { onSuccess: function() { window.location.reload(); } });
            } else { alert("Kesalahan atau Gateway tidak dikenali."); }
        }).catch(error => { document.getElementById('loadingIndicator').style.display = 'none'; });
    }
    {/literal}
</script>

{include file="common/footer.tpl"}