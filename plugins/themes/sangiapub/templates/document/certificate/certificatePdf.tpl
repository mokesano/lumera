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
        body { font-family: helvetica, sans-serif; color: #222; text-align: center; }
        .wi-publisher-logo { max-height: 35px; margin-bottom: 6px; }
        .cert-border { border: 3px double #005c99; padding: 40px; margin: 10px; }
        .cert-journal { font-size: 14px; text-transform: uppercase; letter-spacing: 3px; color: #666; }
        .cert-title { font-size: 30px; font-weight: bold; margin: 20px 0; letter-spacing: 4px; }
        .cert-name { font-size: 22px; font-family: Georgia, serif; margin: 15px 0; border-bottom: 1px solid #999; display: inline-block; padding: 0 20px 5px; }
        .cert-affil { font-size: 12px; color: #555; }
        .cert-body { font-size: 13px; margin: 20px 60px; line-height: 1.6; }
        .cert-manuscript { font-size: 13px; font-style: italic; margin: 15px 0; }
        .cert-number { font-size: 10px; color: #888; margin-top: 10px; }
        .cert-footer { margin-top: 30px; }
        .cert-footer table { width: 100%; }
        .cert-qr img { width: 65px; height: 65px; }
        .cert-signatory { font-size: 11px; text-align: left; }
    </style>
    {/literal}
</head>
<body>

<div class="cert-border" style="border-color: {$publisher.colorPrimary|escape};">
    {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
    <div class="cert-journal">{$certData.journalTitle|escape}</div>
    <div class="cert-title">CERTIFICATE OF {if $certData.type === 'EDITOR_CERTIFICATE'}EDITORIAL SERVICE{else}RECOGNITION{/if}</div>

    <p style="font-size: 13px;">This certificate is proudly presented to</p>
    {if $certData.type === 'EDITOR_CERTIFICATE'}
        <div class="cert-name">{$certData.editorName|escape}</div><br>
        <div class="cert-affil">{$certData.editorAffiliation|escape}</div>
        <div class="cert-body">
            In recognition of dedicated editorial service for the manuscript:
            <div class="cert-manuscript">"{$certData.articleTitle|escape}"</div>
        </div>
        <div class="cert-number">Certificate No. {$certData.certificateNumber|escape} &bull; Date Assigned: {$certData.dateAssigned|date_format:"%d %B %Y"}</div>
    {else}
        <div class="cert-name">{$certData.reviewerName|escape}</div><br>
        <div class="cert-affil">{$certData.reviewerAffiliation|escape}</div>
        <div class="cert-body">
            In recognition of a valuable peer-review contribution for the manuscript:
            <div class="cert-manuscript">"{$certData.articleTitle|escape}"</div>
        </div>
        <div class="cert-number">Certificate No. {$certData.certificateNumber|escape} &bull; Date Completed: {$certData.dateCompleted|date_format:"%d %B %Y"}</div>
    {/if}

    <div class="cert-footer">
        <table>
            <tr>
                <td width="35%" class="cert-signatory">
                    Best regards,<br>
                    <strong>
                        {if $certData.signatoryNames|@count > 0}
                            {foreach from=$certData.signatoryNames item=name name=sig}{$name|escape}{if !$smarty.foreach.sig.last}<br>{/if}{/foreach}
                        {else}
                            Editorial Board
                        {/if}
                    </strong><br>
                    Journal Manager{if $certData.signatoryNames|@count > 1}s{/if}
                </td>
                <td width="30%" class="cert-qr">
                    <img src="{$qrCodeBase64}" alt="QR Verification">
                    <div style="font-size: 9px;">Scan to verify</div>
                </td>
                <td width="35%"></td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>