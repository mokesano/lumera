{**
 * templates/document/loa/loaPrivate.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{strip}
    {assign var="pageTitle" value="My Letter of Acceptance"}
    {include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wizdam-loa-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    .wi-publisher-logo { max-height: 55px; margin-bottom: 10px; }
    .wi-header { display: flex; justify-content: space-between; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
    .wi-title h1 { margin: 0; font-size: 32px; font-weight: 800; letter-spacing: 2px; color: #222; }
    .wi-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; font-size: 13px; }
    .wi-meta-box { background: #f9f9f9; padding: 15px; border-radius: 4px; }
    .wi-meta-box strong { display: block; margin-bottom: 5px; font-size: 11px; text-transform: uppercase; color: #777; }
    .wi-section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin: 25px 0 15px; }
    .wi-manuscript-box { font-size: 1.1rem; background: #f9f9f9; padding: 15px 20px; margin: 15px 0; }
    .wi-footer-row { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #ccc; }
    .wi-qr-box { text-align: center; }
    .action-button { margin-top: 20px; text-align: right; }
    .action-button a { text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 4px; font-weight: 700; border: 1px solid #005c99; color: #fff; background-color: #005c99; }
</style>
{/literal}

<div class="wizdam-loa-wrapper">
    <div class="wi-header">
        <div class="wi-title">
            {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
            <h1>LETTER OF ACCEPTANCE</h1>
        </div>
        <a href="{$pdfDownloadUrl|escape}" class="wizdam-btn wizdam-btn-primary" style="align-self:center;">
            <i class="icon-download"></i> Download Official PDF
        </a>
    </div>

    <div style="text-align:center; margin-bottom: 25px;">
        <h2 style="text-transform: uppercase; letter-spacing: 2px;">{$loaData.journalTitle|escape}</h2>
    </div>

    <p>Dear <strong>{$loaData.authors|escape}</strong>,</p>
    <p>We are pleased to inform you that your manuscript entitled:</p>
    <div class="wi-manuscript-box" style="border-left: 4px solid {$publisher.colorPrimary|escape};"><em>"{$loaData.title|escape}"</em></div>
    <p>has been officially <strong>ACCEPTED</strong> for publication in {$loaData.journalTitle|escape}.</p>

    <div class="wi-meta-grid">
        <div class="wi-meta-box">
            <strong>Date Submitted</strong>
            {$loaData.dateSubmitted|date_format:"%d %B %Y"}
        </div>
        <div class="wi-meta-box">
            <strong>Date Accepted</strong>
            {$loaData.dateAccepted|date_format:"%d %B %Y"}
        </div>
    </div>

    <div class="wi-section-title">Editorial Team</div>
    <div class="wi-meta-grid">
        <div class="wi-meta-box">
            <strong>Handling Editor{if $loaData.editorNames|@count > 1}s{/if}</strong>
            {if $loaData.editorNames|@count > 0}
                {foreach from=$loaData.editorNames item=name}{$name|escape}<br>{/foreach}
            {else}
                <em>Not yet assigned</em>
            {/if}
        </div>
        <div class="wi-meta-box">
            <strong>Journal Manager{if $loaData.managerNames|@count > 1}s{/if}</strong>
            {if $loaData.managerNames|@count > 0}
                {foreach from=$loaData.managerNames item=name}{$name|escape}<br>{/foreach}
            {else}
                <em>Not configured</em>
            {/if}
        </div>
    </div>

    <div class="wi-footer-row">
        <div>
            <p>Sincerely,</p>
            <p><strong>Editorial Board</strong><br>{$loaData.journalTitle|escape}</p>
        </div>
        <div class="wi-qr-box">
            <img src="{$qrCodeImage}" alt="QR Code" width="100">
            <p><small>Scan to Verify</small></p>
        </div>
    </div>
</div>

{include file="common/footer.tpl"}