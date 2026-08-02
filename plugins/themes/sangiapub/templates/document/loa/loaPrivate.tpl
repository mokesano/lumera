{**
 * templates/document/loa/loaPrivate.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{strip}
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
            <h1>{translate key="document.loa.heading"}</h1>
        </div>
        <a href="{$pdfDownloadUrl|escape}" class="wizdam-btn wizdam-btn-primary" style="align-self:center;">
            <i class="icon-download"></i> {translate key="document.downloadPdf"}
        </a>
    </div>

    <div style="text-align:center; margin-bottom: 25px;">
        <h2 style="text-transform: uppercase; letter-spacing: 2px;">{$loaData.journalTitle|escape}</h2>
    </div>

    <p>{translate key="document.loa.dearAuthor"} <strong>{$loaData.authors|escape}</strong>,</p>
    <p>{translate key="document.loa.introText"}</p>
    <div class="wi-manuscript-box" style="border-left: 4px solid {$publisher.colorPrimary|escape};"><em>"{$loaData.title|strip_unsafe_html}"</em></div>
    <p>{translate key="document.loa.acceptanceStatementBefore"} <strong>{translate key="document.loa.acceptedWord"}</strong> {translate key="document.loa.acceptanceStatementAfter" journalTitle=$loaData.journalTitle|escape}</p>

    <div class="wi-meta-grid">
        <div class="wi-meta-box">
            <strong>{translate key="document.loa.dateSubmittedLabel"}</strong>
            {$loaData.dateSubmitted|date_format:"%d %B %Y"}
        </div>
        <div class="wi-meta-box">
            <strong>{translate key="document.loa.dateAcceptedLabel"}</strong>
            {$loaData.dateAccepted|date_format:"%d %B %Y"}
        </div>
    </div>

    <div class="wi-section-title">{translate key="document.loa.editorialTeamTitle"}</div>
    <div class="wi-meta-grid">
        {if $loaData.editorNames|@count > 0}
        <div class="wi-meta-box">
            <strong>{if $loaData.editorNames|@count > 1}{translate key="document.loa.handlingEditors"}{else}{translate key="document.loa.handlingEditor"}{/if}</strong>
            {foreach from=$loaData.editorNames item=name}{$name|escape}<br>{/foreach}
        </div>
        {/if}
        <div class="wi-meta-box">
            <strong>{if $loaData.managerNames|@count > 1}{translate key="document.journalManagers"}{else}{translate key="document.journalManager"}{/if}</strong>
            {if $loaData.managerNames|@count > 0}
                {foreach from=$loaData.managerNames item=name}{$name|escape}<br>{/foreach}
            {else}
                <em>{translate key="document.loa.notConfigured"}</em>
            {/if}
        </div>
    </div>

    <div class="wi-footer-row">
        <div>
            <p>{translate key="document.loa.sincerely"}</p>
            <p><strong>{translate key="document.editorialBoard"}</strong><br>{$loaData.journalTitle|escape}</p>
        </div>
        <div class="wi-qr-box">
            <img src="{$qrCodeImage}" height="140" width="140" alt="QR Code">
            <p><small>{translate key="document.scanToVerify"}</small></p>
        </div>
    </div>
</div>

{include file="common/footer.tpl"}