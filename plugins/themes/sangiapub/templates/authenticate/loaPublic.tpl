{**
 * templates/authenticate/loaPublic.tpl
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
    .wizdam-loa-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0 auto; }
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
        <img src="{$baseUrl}/assets/ico/verified.svg" alt="Verified" width="80">
        <h2 class="text-success">{translate key="document.verifiedTitle"}</h2>
        <p class="text-muted">{translate key="document.loa.verifiedSubtitle"}</p>
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

    <div class="wi-section-title">{translate key="document.loa.abstractTitle"}</div>
    <div class="abstract-content">{$loaData.abstract|strip_unsafe_html}</div>

    <div class="verify-footer mt-4" style="text-align:center; margin-top:30px;">
        <p><small>{translate key="document.securedBy"} <strong>{$publisher.name|escape}</strong></small></p>
    </div>
</div>

{include file="common/footer.tpl"}