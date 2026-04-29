<!DOCTYPE html>
<html>
{**
 * templates/billing/invoicePdf.tpl
 * Template khusus PDF — tanpa header/footer OJS, murni HTML untuk mPDF
 *}
<head>
<meta charset="utf-8">
{literal}
<style>
    @page { margin: 10mm; }
    
    body { font-family: 'Noto Sans', 'Helvetica', 'Arial', sans-serif; color: #000; font-size: 9pt; line-height: 1.4; }
    
    .stamp { position: absolute; /* Posisi di sudut kanan atas */ top: 5mm; /* Jarak dari tepi atas kertas */ right: 5mm; /* Jarak dari tepi kanan kertas */ /* Ukuran dan gaya */ font-family: 'Helvetica', 'Arial', sans-serif; font-weight: bold; font-size: 20pt; /* Rotasi 45 derajat searah jarum jam */ transform-origin: top right; transform: rotate(45deg); z-index: 1000; pointer-events: none; }
    .stamp-paid { color: rgb(0, 128, 0, 0.6); /* Hijau dengan transparansi 0.6 */ }
    .stamp-unpaid { color: rgba(255, 0, 0, 0.6); /* Merah dengan transparansi 0.6 */ }
    
    .text-left { text-align: left; } 
    .text-center { text-align: center; } 
    .text-right { text-align: right; } 
    .font-bold { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    
    .journal-title { font-size: 14pt; font-weight: bold; color: #1a4f8b;}
    .invoice-heading { font-size: 32pt; font-weight: bold; color: #222; letter-spacing: 1px; }
    .invoice-number { border-top:1px solid #e6ffe6; font-size: 12px; padding: 4px; }
    
    .info-billing { margin-top: 20px }
    .info-billing th { background-color: #d54449; padding: 4px 8px; vertical-align: top;  }
    .info-billing .summary-box th { background-color: #ccc; color: #222; padding: 4px 8px; }
    
    .info-table th { background-color: #d54449; color: #fff; padding: 4px 8px; vertical-align: top; }
    .info-table td { vertical-align: top; }
    .summary-box { border: 1px solid #ddd; padding: 5px; }
    .summary-box th { background-color: #ccc; padding: 4px; }
    .summary-box td { padding: 4px 8px; border-bottom: 1px solid #ddd; }
    
    .item-table { margin-top: 15px; }
    .item-table th { background-color: #d54449; color: #fff; padding: 8px; text-align: center; }
    .item-table td { padding: 8px; }
    .trasaction td { border-bottom: 1px solid #d54449; }
    .balance { background-color: #ffe6e6; }
    
    .summary-table td { padding: 5px 8px; }
    .sub-total td { font-weight: bold; background: #ffe6e6; border-bottom: 1px solid #cc0000; }
    .special-discount td, .discount td, .fast-track td { background-color: #f4f4f4; }
    .total-row td { font-size: 10pt; font-weight: bold; background-color: #f4fdf8; color: #008000; border-bottom: 4px solid #28a745}

    .notes-box { margin-top: 20px; font-size: 9pt; border: 1px dashed #ccc; padding: 16px; text-align: justify; }
    .quote-box { font-family: Georgia, serif; font-size: 10pt; background-color: #d54449; color: #fff; padding: 8px; text-align: center; margin-top: 20px; }
    .wi-footer { text-align: center; font-size: 9px; color: #aaa; margin-top: 30px; border-top: 1px solid #eee; padding-top: 8px; }
</style>
{/literal}
</head>

<body>
<div class="stamp">
    <svg width="300" height="300" viewBox="0 0 300 300" style="position: absolute; top: 5mm; right: 5mm; z-index: 1000;">
        <text x="250" y="50"
            text-anchor="middle"
            dominant-baseline="middle"
            font-size="20"
            font-weight="bold"
            fill="rgba(0, 128, 0, 0.6)"
            transform="rotate(45 250 50)">
            {if $isPaid}PAID{else}UNPAID{/if}
        </text>
    </svg>
</div>

<header>
<table class="invoice-info" style="margin-bottom: 32px">
    <tr>
        <td width="60%"></td>
        <td width="30%" class="text-center">
            <div class="invoice-heading">INVOICE</div>
            <div class="invoice-number">Invoice Number:  {$wizdamInvoiceNumber|escape}</div>
        </td>
    </tr>
</table>

<table class="publisher-info">
    <tr>
        <td>
            <div class="journal-title">{if $journal}{$journal->getLocalizedTitle()|escape}{/if}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div style="font-size: 8pt; color: #555;">
                {if $journal}{$journal->getSetting('mailingAddress')|nl2br}
                <br>
                {/if}
                Mail: {$journal->getSetting('contactEmail')|escape}
                <br>
                Sangia Research Media and Publishing (AHU-050003.AH.01.30. Tahun 2022)
            </div>
        </td>
    </tr>
</table>
</header>
    
<table class="info-billing">
    <tr class="font-bold">
        <th width="60%" style="text-align: left; color: #fff; "><div>Billing to:</div></th>
        <th width="35%" style="text-align: left; color: #fff;"><div>Information Base:</div></th>
    </tr>
    <tr>
        <td width="60%" style="vertical-align: top; margin-top: 10px; padding: 8px; ">
            <div class="font-bold" style="font-size: 10pt; ">{$authorName|escape}</div>
            <div style="font-size: 8pt;">{$authorAffiliation|nl2br}</div>
            <div style="font-size: 8pt;">Corespondence email: {$authorEmail|escape}</div>
        </td>
        <td width="40%">
            <table class="summary-box">
                <tr><th class="font-bold text-left">AMOUNT DUE</th><th class="font-bold text-right">{$currencyCode} {$formattedAmount}</th></tr>
                <tr><td>Date Billed</td><td class="text-right">{$dateBilled|date_format:"%d %B %Y"}</td></tr>
                <tr><td>Invoice Code</td><td class="text-right">{$wizdamInvoiceCode|escape}</td></tr>
                <tr><td>Due Date</td><td class="font-bold text-right" style="color: {if $isPaid}green{else}red{/if};">{if $isPaid}LUNAS{else}BELUM LUNAS{/if}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="item-table">
    <tr>
        <th width="7%">Unit</th>
        <th width="53%">Title</th>
        <th width="20%">Cost per unit</th>
        <th width="20%">Amount</th>
    </tr>
    <tr class="trasaction">
        <td class="text-center">1</td>
        <td style="word-wrap: break-word;">{if $articleTitle}{$articleTitle|strip_unsafe_html|nl2br}{else}{$localizedFeeName|escape}{/if}</td>
        <td class="text-right">{$currencyCode} {if $formattedFastTrackFee != "0.00"}{$formattedFastTrackFee}{else}{$formattedBaseFee}{/if}</td>
        <td class="text-right">{$currencyCode} {if $formattedFastTrackFee != "0.00"}{$formattedFastTrackFee}{else}{$formattedBaseFee}{/if}</td>
    </tr>
</table>

<table class="summary-table">
    <tr>
        <td width="60%" rowspan="9"></td>
    </tr>
    <tr class="discount">
        <td class="text-right">Discount <small>(Trade)</small></td>
        <td class="text-right">- {$currencyCode} {$formattedDiscount}</td>
    </tr>
    <tr class="submission">
        <td class="text-right">Submission</td>
        <td class="text-right">{$currencyCode} {$formattedDiscount}</td>
    </tr>
    <tr class="fast-track">
        <td class="text-right">Fast Track</td>
        <td class="text-right">{$currencyCode} {$formattedDiscount}</td>
    </tr>
    <tr class="sub-total">   
        <td width="20%" class="text-bold text-right">Subtotal <small>(Taxable)</small></td>
        <td width="20%" class="text-right">{$currencyCode} {$formattedSubtotal}</td>
    </tr>
    <tr class="special-discount">
        <td class="text-right">Special Discount</td>
        <td class="text-right">- {$currencyCode} {$formattedDiscount}</td>
    </tr>
    <tr class="tax">
        <td class="text-right">Tax/VAT ({$taxPercentage}%) <small>{if $isTaxInclusive}Inclusive{else}Exclusive{/if}</small></td>
        <td class="text-right">{$currencyCode} {$formattedTax}</td>
    </tr>
    <tr class="total-row">
        <td class="text-right">AMOUNT DUE</td>
        <td class="text-right">{$currencyCode} {$formattedAmount}</td>
    </tr>
</table>

<div style="margin-top: 15px; font-weight: bold; font-size: 10pt;">
    Transactions Confirmation
</div>
<table class="item-table" style="margin-top: 5px; font-size: 10pt;">
    <tr>
        <th width="20%">Date time</th>
        <th width="50%">Transaction ID</th>
        <th width="10%">Gateway</th>
        <th width="20%">Amount</th>
    </tr>
    {if $isPaid}
    <tr class="trasaction">
        <td class="text-center">{$datePaid|date_format:"%d-%m-%Y %H:%M:%S"}</td>
        <td class="text-center">TXN-{$invoice->getInvoiceId()}</td>
        <td class="text-center">{$paymentMethod|escape|upper}</td>
        <td class="text-right">{$currencyCode} {$formattedAmount}</td>
    </tr>
    <tr colspan="2">
        <td width="20%"></td>
        <td width="50%"></td>
        <td width="20%" class="balance text-center font-bold">Balance Due</td>
        <td width="10%" class="balance text-right font-bold">-</td>
    </tr>
    {else}
    <tr class="trasaction">
        <td class="text-center">-</td>
        <td class="text-center">-</td>
        <td class="text-center">-</td>
        <td class="text-center">-</td>
    </tr>
    <tr>
        <td width="20%"></td>
        <td width="50%"></td>
        <td width="20%" class="balance text-center font-bold">Balance Due</td>
        <td width="10%" class="balance text-right font-bold">{$currencyCode} {$formattedAmount}</td>
    </tr>
    {/if}
</table>

<table class="notes-qrcode" style="margin-top: 48px;">
    <tr>
        <td width="20%" class="qrcode-box text-center text-bold">
            <small style="font-weight: bold;" >Authenticate</small>
            <img src="{$qrCodeBase64}" width="100" alt="QR Code" >
        </td>
        <td width="75%" class="notes-box">
            <div>
                <strong>NOTES:</strong>
                <br>Publication fee melingkupi dan tidak terbatas pada: 
                <br>1) Biaya aktivasi DOI by CrossRef. 
                <br>2) Pemeriksaan Plagiarism iThenticate. Hasil dikirimkan atas permintaan penulis.
            </div>
            <div>
                <strong>Special Discount</strong>
                <br><em>Hanya</em> diberikan kepada Mahasiswa Tingkat Sarjana (S-1). 
            </div>
        </td>
    </tr>
</table>

<footer>
    <div class="quote-box">
        <em>Membangun Perikanan Indonesia dari Pesisir dan Pulau-Pulau Kecil, Menuju Negara Maritim Dunia!</em>
    </div>
    
    {* ===== FOOTER ===== *}
    <div class="wi-footer">
        Document generated automatically by Wizdam Frontedge &bull;
        {$wizdamInvoiceNumber|escape} &bull;
        This document is digitally signed and verified.
    </div>
</footer>

</body>
</html>