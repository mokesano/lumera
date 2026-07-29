{**
 * templates/document/certificate/certificatePrivate.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * Reviewer atau Editor -- TIDAK untuk Author (Author memakai LoA).
 *}
{strip}
    {assign var="pageTitle" value="My Certificate"}
    {include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wizdam-cert-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    .wi-publisher-logo { max-height: 45px; margin-bottom: 8px; }
    .wi-header { display: flex; justify-content: space-between; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
    .wi-title h1 { margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 2px; color: #222; }
    .cert-card { border: 1px solid #ddd; padding: 30px; text-align: center; }
    .wi-manuscript-box { font-size: 1.05rem; background: #f9f9f9; padding: 15px 20px; margin: 15px 0; text-align: left; }
    .wi-footer-row { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #ccc; }
    .wi-qr-box { text-align: center; }
    .action-button a { text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 4px; font-weight: 700; border: 1px solid #005c99; color: #fff; background-color: #005c99; align-self:center; }
</style>
{/literal}

<div class="wizdam-cert-wrapper">
    <div class="wi-header">
        <div class="wi-title">
            {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
            <h1>Certificate of {if $certData.type === 'EDITOR_CERTIFICATE'}Editorial Service{else}Recognition (Reviewer){/if}</h1>
        </div>
        <a href="{$pdfDownloadUrl|escape}" class="action-button"><i class="icon-download"></i> Download Official PDF</a>
    </div>

    <div class="cert-card">
        <h2 style="text-transform: uppercase; letter-spacing: 2px;">{$certData.journalTitle|escape}</h2>
        <hr style="width: 50px; border-top: 2px solid {$publisher.colorPrimary|escape}; margin: 15px auto;">

        <p style="margin-top: 20px;">This certificate is proudly presented to</p>

        {if $certData.type === 'EDITOR_CERTIFICATE'}
            <h2 style="font-family: Georgia, serif;">{$certData.editorName|escape}</h2>
            <p>{$certData.editorAffiliation|escape}</p>
            <p style="margin-top: 20px;">In recognition of dedicated editorial service for the manuscript:</p>
            <div class="wi-manuscript-box"><em>"{$certData.articleTitle|escape}"</em></div>
            <p>Certificate Number: <strong>{$certData.certificateNumber|escape}</strong></p>
            <p>Date Assigned: {$certData.dateAssigned|date_format:"%d %B %Y"}</p>
        {else}
            <h2 style="font-family: Georgia, serif;">{$certData.reviewerName|escape}</h2>
            <p>{$certData.reviewerAffiliation|escape}</p>
            <p style="margin-top: 20px;">In recognition of a valuable peer-review contribution for the manuscript:</p>
            <div class="wi-manuscript-box"><em>"{$certData.articleTitle|escape}"</em></div>
            <p>Certificate Number: <strong>{$certData.certificateNumber|escape}</strong></p>
            <p>Date Completed: {$certData.dateCompleted|date_format:"%d %B %Y"}</p>
        {/if}

        <div class="wi-footer-row">
            <div style="text-align:left;">
                <p>Best regards,</p>
                <p><strong>
                    {if $certData.signatoryNames|@count > 0}
                        {foreach from=$certData.signatoryNames item=name name=sig}{$name|escape}{if !$smarty.foreach.sig.last} &amp; {/if}{/foreach}
                    {else}
                        Editorial Board
                    {/if}
                </strong><br>Journal Manager{if $certData.signatoryNames|@count > 1}s{/if}<br>{$certData.journalTitle|escape}</p>
            </div>
            <div class="wi-qr-box">
                <img src="{$qrCodeImage}" alt="QR Code" width="100">
                <p><small>Scan to Verify</small></p>
            </div>
        </div>
    </div>
</div>

{include file="common/footer.tpl"}