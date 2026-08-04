<!DOCTYPE html>
<html>
{**
 * templates/document/certificate/certificatePdf.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
<head>
    <meta charset="utf-8">
    {literal}
    <style>
        @page {
            margin: 20mm 15mm 0mm 15mm;
        }
        body { font-family: helvetica, sans-serif; color: #222; text-align: center; }
        .wi-publisher-logo { max-height: 35px; margin-bottom: 6px; }

        .cert-journal { font-size: 14px; text-transform: uppercase; letter-spacing: 3px; color: #666; }
        .cert-title { font-size: 26px; font-weight: bold; text-align: center; margin: 20px 0; letter-spacing: 3px; }
        .cert-name { font-size: 22px; font-family: Georgia, serif; margin: 15px 0; border-bottom: 1px solid #999; display: inline-block; padding: 0 20px 5px; }
        .cert-affil { font-size: 12px; color: #555; }
        .cert-body { font-size: 13px; margin: 20px 60px; line-height: 1.6; }
        .cert-manuscript { font-size: 13px; font-style: italic; margin: 15px 0; }
        .cert-number { font-size: 10px; color: #888; margin-top: 10px; }
        .cert-footer { margin-top: 30px; }
        .cert-footer table { width: 100%; }
        .cert-qr img { width: 65px; height: 65px; }
        .cert-signatory { font-size: 11px; text-align: left; }


        /* === FOOTER 4 KOLOM (1 kosong) === */
        .footer-full {
            background: #f0f5fa;
            border-top: 2px solid #005c99;
            padding: 5px 0;           /* padding kecil */
            margin: 10mm -20mm 0 -20mm; /* melebar penuh (sama dengan margin @page) */
            font-size: 7.5pt;         /* font kecil */
            color: #333;
            box-sizing: border-box;
        }
        .footer-full table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .footer-full td {
            padding: 0 8px;
            vertical-align: middle;
        }
        .footer-col-left {
            text-align: left;
            width: 28%;
        }
        .footer-col-center1 {
            text-align: center;
            width: 26%;
        }
        .footer-col-center2 {
            text-align: center;
            width: 26%;
        }
        .footer-col-right {
            text-align: right;
            width: 20%;
        }
        .footer-journal {
            font-weight: bold;
            font-size: 8.5pt;
            color: #005c99;
        }
        .footer-journal-sub {
            font-size: 6.5pt;
            color: #666;
        }
        .footer-label {
            font-weight: bold;
            font-size: 7.5pt;
            color: #005c99;
            margin-bottom: 10px;
        }
        .footer-text {
            font-size: 6.5pt;
            line-height: 1.2;
        }
        .footer-text a {
            color: #005c99;
        }
    </style>
    {/literal}
</head>
<body>

<div class="cert-border" style="border-color: {$publisher.colorPrimary|escape};">
    {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
    <div class="cert-journal">{$certData.journalTitle|escape}</div>
    {if $certData.type === 'EDITOR_CERTIFICATE'}
        {translate key="document.cert.headingEditor" assign="certPdfHeadingText"}
    {else}
        {translate key="document.cert.headingReviewer" assign="certPdfHeadingText"}
    {/if}
    <div class="cert-title">{$certPdfHeadingText|upper}</div>

    <p style="font-size: 13px;">{translate key="document.cert.presentedTo"}</p>
    {if $certData.type === 'EDITOR_CERTIFICATE'}
        <div class="cert-name">{$certData.editorName|escape}</div><br>
        <div class="cert-affil">{$certData.editorAffiliation|escape}</div>
        <div class="cert-body">
            {translate key="document.cert.editorRecognitionText"}
            <div class="cert-manuscript">"{$certData.articleTitle|strip_unsafe_html}"</div>
        </div>
        <div class="cert-number">{translate key="document.cert.certificateNumberLabel"} {$certData.certificateNumber|escape} &bull; {translate key="document.cert.dateAssignedLabel"}: {$certData.dateAssigned|date_format:"%d %B %Y"}</div>
    {else}
        <div class="cert-name">{$certData.reviewerName|escape}</div><br>
        <div class="cert-affil">{$certData.reviewerAffiliation|escape}</div>
        <div class="cert-body">
            {translate key="document.cert.reviewerRecognitionText"}
            <div class="cert-manuscript">"{$certData.articleTitle|strip_unsafe_html}"</div>
        </div>
        <div class="cert-number">{translate key="document.cert.certificateNumberLabel"} {$certData.certificateNumber|escape} &bull; {translate key="document.cert.dateCompletedLabel"}: {$certData.dateCompleted|date_format:"%d %B %Y"}</div>
    {/if}

    <div class="cert-footer">
        <table>
            <tr>
                <td width="35%" class="cert-signatory">
                    {translate key="document.cert.bestRegards"}<br>
                    <strong>
                        {if $certData.signatoryNames|@count > 0}
                            {foreach from=$certData.signatoryNames item=name name=sig}{$name|escape}{if !$smarty.foreach.sig.last}<br>{/if}{/foreach}
                        {else}
                            {translate key="document.editorialBoard"}
                        {/if}
                    </strong><br>
                    {if $certData.signatoryNames|@count > 1}{translate key="document.journalManagers"}{else}{translate key="document.journalManager"}{/if}
                </td>
                <td width="30%" class="cert-qr">
                    <img src="{$qrCodeBase64}" height="120" width="120" alt="QR Verification">
                    <div style="font-size: 9px;">{translate key="document.scanToVerify"}</div>
                </td>
                <td width="35%"></td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>