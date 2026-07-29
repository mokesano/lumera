{**
 * templates/authenticate/loaPublic.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{strip}
    {assign var="pageTitle" value="Document Validation - LoA"}
    {include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wizdam-loa-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 700px; margin: 0 auto; }
    .wi-publisher-logo { max-height: 55px; margin-bottom: 10px; }
    .verify-badge-wrapper { text-align: center; margin-bottom: 25px; }
    .wi-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; font-size: 13px; }
    .wi-meta-box { background: #f9f9f9; padding: 15px; border-radius: 4px; }
    .wi-meta-box strong { display: block; margin-bottom: 5px; font-size: 11px; text-transform: uppercase; color: #777; }
    .wi-section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin: 25px 0 15px; }
    .wi-manuscript-box { font-size: 1.1rem; background: #f9f9f9; padding: 15px 20px; margin: 15px 0; }
</style>
{/literal}

<div class="wizdam-loa-wrapper">
    <div class="verify-badge-wrapper">
        {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
        <img src="{$baseUrl}/plugins/themes/wizdam/images/verified-seal.png" alt="Verified" width="80">
        <h2 class="text-success">DOCUMENT VERIFIED</h2>
        <p class="text-muted">This Letter of Acceptance is authentic and registered in our system.</p>
    </div>

    <div style="text-align:center; margin-bottom: 25px;">
        <h2 style="text-transform: uppercase; letter-spacing: 2px;">{$loaData.journalTitle|escape}</h2>
    </div>

    <p>Dear <strong>{$loaData.authors|escape}</strong>,</p>
    <p>We are pleased to inform you that the manuscript entitled:</p>
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

    <div class="wi-section-title">Abstract</div>
    <div class="abstract-content">{$loaData.abstract|strip_unsafe_html}</div>

    <div class="verify-footer mt-4" style="text-align:center; margin-top:30px;">
        <p><small>Secured by <strong>{$publisher.name|escape}</strong></small></p>
    </div>
</div>

{include file="common/footer.tpl"}