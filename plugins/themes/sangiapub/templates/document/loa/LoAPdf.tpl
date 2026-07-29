<!DOCTYPE html>
<html>
{**
 * templates/document/loa/LoAPdf.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
<head>
    <meta charset="utf-8">
    {literal}
    <style>
        body { font-family: helvetica, sans-serif; font-size: 12px; color: #222; }
        .wi-publisher-logo { max-height: 40px; margin-bottom: 8px; }
        .wi-header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
        .wi-header h2 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .wi-title { font-size: 26px; font-weight: bold; text-align: center; margin: 20px 0; letter-spacing: 3px; }
        .wi-body { font-size: 12px; line-height: 1.7; padding: 0 20px; }
        .wi-manuscript-box { font-size: 13px; font-style: italic; background: #f9f9f9; padding: 10px 15px; margin: 15px 0; }
        .wi-meta-grid { width: 100%; margin: 20px 0; }
        .wi-meta-grid td { vertical-align: top; width: 50%; padding: 0 10px; }
        .wi-meta-box { background: #f9f9f9; padding: 12px; font-size: 11px; }
        .wi-meta-box .label { font-size: 9px; text-transform: uppercase; color: #777; font-weight: bold; margin-bottom: 4px; }
        .wi-footer { margin-top: 40px; }
        .wi-footer table { width: 100%; }
        .wi-qr img { width: 90px; height: 90px; }
    </style>
    {/literal}
</head>
<body>

<div class="wi-header">
    {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
    <h2>{$loaData.journalTitle|escape}</h2>
</div>

<div class="wi-title">LETTER OF ACCEPTANCE</div>

<div class="wi-body">
    <p>Dear <strong>{$loaData.authors|escape}</strong>,</p>
    <p>We are pleased to inform you that your manuscript entitled:</p>
    <div class="wi-manuscript-box" style="border-left: 3px solid {$publisher.colorPrimary|escape};">"{$loaData.title|escape}"</div>
    <p>has been officially <strong>ACCEPTED</strong> for publication in {$loaData.journalTitle|escape}.</p>
    <p>Date Submitted: {$loaData.dateSubmitted|date_format:"%d %B %Y"}</p>
    <p>Date Accepted: {$loaData.dateAccepted|date_format:"%d %B %Y"}</p>
</div>

<table class="wi-meta-grid">
    <tr>
        <td>
            <div class="wi-meta-box">
                <div class="label">Handling Editor{if $loaData.editorNames|@count > 1}s{/if}</div>
                {if $loaData.editorNames|@count > 0}
                    {foreach from=$loaData.editorNames item=name}{$name|escape}<br>{/foreach}
                {else}
                    <em>Not yet assigned</em>
                {/if}
            </div>
        </td>
        <td>
            <div class="wi-meta-box">
                <div class="label">Journal Manager{if $loaData.managerNames|@count > 1}s{/if}</div>
                {if $loaData.managerNames|@count > 0}
                    {foreach from=$loaData.managerNames item=name}{$name|escape}<br>{/foreach}
                {else}
                    <em>Not configured</em>
                {/if}
            </div>
        </td>
    </tr>
</table>

<div class="wi-footer">
    <table>
        <tr>
            <td width="70%">
                <p>Sincerely,</p>
                <p><strong>Editorial Board</strong><br>{$loaData.journalTitle|escape}</p>
            </td>
            <td width="30%" class="wi-qr" style="text-align:center;">
                <img src="{$qrCodeBase64}" alt="QR Verification">
                <p><small>Scan to verify</small></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>