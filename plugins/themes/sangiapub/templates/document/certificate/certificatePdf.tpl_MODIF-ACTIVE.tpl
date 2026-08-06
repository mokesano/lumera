<!DOCTYPE html>
<html>
{**
 * templates/document/certificate/certificatePdf.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * Application: Sicola
 *}
<head>
    <meta charset="utf-8">
    {literal}
    <style>
        
        /* 2. Pengaturan Font & Teks dasar serta Padding Atas/Bawah Dokumen */
        body { 
            font-family: helvetica, sans-serif; 
            color: #222; 
            text-align: left; 
            font-size: 12pt;
            line-height: 1.5;
        }
        
        .wi-publisher-logo { max-height: 45px; margin-bottom: 25px; }

        /* 3. Tipografi Konten Dokumen */
        .doc-date { font-size: 11pt; margin-bottom: 20px; }
        .doc-title { font-size: 18pt; font-weight: bold; margin-bottom: 25px; color: #222; }
        .doc-salutation { font-size: 11pt; margin-bottom: 15px; }
        .doc-body { margin-bottom: 20px; text-align: justify; }
        
        .doc-signatory { font-size: 11pt; margin-top: 30px; line-height: 1.4; }
        
        .qr-section { margin-top: 40px; }
        .qr-section img { width: 75px; height: 75px; margin-bottom: 5px; }
        .qr-section .qr-text { font-size: 9pt; color: #555; }

        /* 4. Desain Footer Khusus mPDF (Full Bleed Menempel Sempurna ke Tepi Bawah Kertas) */
        .footer-wrapper {
            position: relatif;
            background-color: #f0f5fa;
            
            /* Menarik background melewati batas margin kiri-kanan (15mm) */
            margin-left: -45mm; 
            margin-right: -45mm;
            margin-bottom: 0mm;
            margin-top: 15mm;
            
            /* Mengembalikan posisi teks agar tetap lurus dengan konten */
            
            padding-top: 10mm;
            padding-bottom: 15mm;
            
            /* Garis tebal diposisikan tepat di batas paling bawah file PDF */
            border-bottom: 12px solid #005c99; 
        }

        .footer-table {
            width: 100%;
            font-size: 12pt;
            color: #333;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: top;
            width: 33.33%;
        }
        .footer-journal { font-weight: bold; font-size: 15pt; color: #005c99; margin-bottom: 3px; }
        .footer-chars { font-size: 13pt; padding-top: 7px; }
        .footer-table a { color: #005c99; text-decoration: none; }
        
        .journal-name { padding-left:10mm; }
        
        .flex-display { display: flex; }
        .no-color { color: #999999; }
        .no-decoration { text-decoration: none !important; }
        .header { font-size: 14pt; }
        .footer-watermark { background-color: #000099; }
        .u-mb-16 { margin-bottom: 16px; }
    </style>
    {/literal}
</head>
<body>

<!-- ==========================================
     DEFINISI FOOTER mPDF (Stempel Bawah Full Bleed)
     ========================================== -->
<htmlpagefooter name="CertFooter">
    <div class="footer-wrapper">
        <table class="footer-table">
            <tr>
                <td>
                    {* kosong *}
                </td>
                <!-- Kolom Kiri: Nama Jurnal -->
                <td class="journal-name">
                    <div class="footer-journal">{$certData.journalTitle|escape}</div>
                    <div class="footer-chars">Open Access Journal</div>
                </td>
                <!-- Kolom Tengah: Alamat -->
                <td class="publisher-address">
                    <span class="header">{$publisher.name|escape}</span>
                    {if $publisher.address}<p>{$publisher.address|nl2br}</p>{/if}
                </td>
                <!-- Kolom Kanan: Kontak -->
                <td class="contact">
                    Tel +41 (0)21 519 17 00<br>
                    <a class="no-color no-decoration" href="mailto:nama.jurnal@sangia.org">nama.jurnal@sangia.org</a><br>
                    <a class="no-decoration" href="https://sangia.org">sangia.org</a>
                </td>
            </tr>
        </table>
    </div>
</htmlpagefooter>
<!-- Mengaktifkan footer pada halaman ini -->
<sethtmlpagefooter name="CertFooter" value="on" />


<!-- ==========================================
     KONTEN UTAMA DOKUMEN
     ========================================== -->
<div class="cert-container">
    
    <!-- Logo Publisher -->
    {if $publisher.logoUrl}
        <img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>
    {/if}

    <!-- Tanggal -->
    <div class="doc-date">
        {$certData.dateCompleted|date_format:"%d %B %Y"|default:$smarty.now|date_format:"%d %B %Y"}
    </div>
    
    <!-- Judul Surat -->
    {if $certData.type === 'EDITOR_CERTIFICATE'}
        <div class="doc-title">{translate key="document.cert.headingEditor"|default:"Editing confirmation"}</div>
    {else}
        <div class="doc-title">Reviewing confirmation</div>
    {/if}
    
    <!-- Salam Pembuka -->
    <div class="doc-salutation">To whom it may concern,</div>
    
    <!-- Isi Surat Dinamis -->
    <div class="doc-body">
        {if $certData.type === 'EDITOR_CERTIFICATE'}
            We are pleased to confirm that <strong>{$certData.editorName|escape}</strong> has served as an editor, contributing to the rigorous review process of <em>{$certData.articleTitle|strip_unsafe_html}</em> published on {$certData.dateAssigned|date_format:"%d %B %Y"} within the section in <strong>{$certData.journalTitle|escape}</strong>.
            <br><br>
            <strong>{$certData.editorName|escape}</strong> was selected for this role by peers based on their expertise in the field and has played an important role in our collaborative peer review process.
        {else}
            We are pleased to confirm that <strong>{$certData.reviewerName|escape}</strong> has served as a reviewer, contributing to the rigorous review process of <em>{$certData.articleTitle|strip_unsafe_html}</em> published on {$certData.dateCompleted|date_format:"%d %B %Y"} within the section in <strong>{$certData.journalTitle|escape}</strong>.
            <br><br>
            <strong>{$certData.reviewerName|escape}</strong> was selected for this role by peers based on their expertise in the field and has played an important role in our collaborative peer review process.
        {/if}
        <br><br>
        Please feel free to contact the editorial office at contact@sangia.org for any further questions.
    </div>
        
    <!-- Tanda Tangan & Keterangan Pengirim -->
    <div class="doc-signatory u-mb-16">
        Best regards,<br><br>
        <strong>
            {if $certData.signatoryNames|@count > 0}
                {foreach from=$certData.signatoryNames item=name name=sig}{$name|escape}{if !$smarty.foreach.sig.last}<br>{/if}{/foreach}
            {/if}
        </strong><br>
        {if $certData.signatoryNames|@count > 1}
            {translate key="document.journalManagers"}
        {else}
            {translate key="document.journalManager"}
        {/if}
    </div>
    <div class="u-mb-16">
        Editorial office - {$certData.journalTitle|escape}
    </div>
        
    <!-- QR Code & Verifikasi -->
    <div class="qr-section">
        <img src="{$qrCodeBase64}" height="120" width="120" alt="QR Verification"><br>
        <div class="qr-text">{translate key="document.scanToVerify"}</div>
    </div>
    
</div>

</body>
</html>