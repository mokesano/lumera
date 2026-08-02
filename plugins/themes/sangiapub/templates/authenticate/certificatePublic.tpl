{**
 * templates/authenticate/certificatePublic.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{**
 * [FIX] TIDAK ADA {assign var="pageTitle"...} di sini -- AuthenticateHandler::certificate()
 * SUDAH benar mengirim pageTitle = 'authenticate.cert.verifiedTitle' sebelum
 * memanggil display().
 *}
{strip}
    {include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wizdam-cert-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 700px; margin: 0 auto; text-align: center; }
    .wi-publisher-logo { max-height: 50px; margin-bottom: 10px; }
    .verify-badge-wrapper { margin-bottom: 25px; }
    .wi-manuscript-box { font-size: 1.05rem; background: #f9f9f9; padding: 15px 20px; margin: 15px 0; text-align: left; }
    .wi-footer-row { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #ccc; }
</style>
{/literal}

<div class="wizdam-cert-wrapper">
    <div class="verify-badge-wrapper">
        {if $publisher.logoUrl}<img src="{$publisher.logoUrl|escape}" alt="{$publisher.name|escape}" class="wi-publisher-logo"><br>{/if}
        <img src="{$baseUrl}/plugins/themes/wizdam/images/verified-seal.png" alt="Verified" width="80">
        <h2 class="text-success">{translate key="document.verifiedTitle"}</h2>
        <p class="text-muted">{translate key="document.cert.verifiedSubtitle"}</p>
    </div>

    <h2 style="text-transform: uppercase; letter-spacing: 2px;">{$certData.journalTitle|escape}</h2>
    <hr style="width: 50px; border-top: 2px solid {$publisher.colorPrimary|escape}; margin: 15px auto;">

    <p style="margin-top: 20px;">{translate key="document.cert.presentedTo"}</p>

    {if $certData.type === 'EDITOR_CERTIFICATE'}
        <h2 style="font-family: Georgia, serif;">{$certData.editorName|escape}</h2>
        <p>{$certData.editorAffiliation|escape}</p>
        <p style="margin-top: 20px;">{translate key="document.cert.editorRecognitionText"}</p>
        <div class="wi-manuscript-box"><em>"{$certData.articleTitle|escape}"</em></div>
        <p>{translate key="document.cert.certificateNumberLabel"}: <strong>{$certData.certificateNumber|escape}</strong></p>
        <p>{translate key="document.cert.dateAssignedLabel"}: {$certData.dateAssigned|date_format:"%d %B %Y"}</p>
    {else}
        <h2 style="font-family: Georgia, serif;">{$certData.reviewerName|escape}</h2>
        <p>{$certData.reviewerAffiliation|escape}</p>
        <p style="margin-top: 20px;">{translate key="document.cert.reviewerRecognitionText"}</p>
        <div class="wi-manuscript-box"><em>"{$certData.articleTitle|escape}"</em></div>
        <p>{translate key="document.cert.certificateNumberLabel"}: <strong>{$certData.certificateNumber|escape}</strong></p>
        <p>{translate key="document.cert.dateCompletedLabel"}: {$certData.dateCompleted|date_format:"%d %B %Y"}</p>
    {/if}

    <div class="wi-footer-row" style="text-align:left;">
        <div>
            <p>{translate key="document.cert.bestRegards"}</p>
            <p><strong>
                {if $certData.signatoryNames|@count > 0}
                    {foreach from=$certData.signatoryNames item=name name=sig}{$name|escape}{if !$smarty.foreach.sig.last} &amp; {/if}{/foreach}
                {else}
                    {translate key="document.editorialBoard"}
                {/if}
            </strong><br>{if $certData.signatoryNames|@count > 1}{translate key="document.journalManagers"}{else}{translate key="document.journalManager"}{/if}<br>{$certData.journalTitle|escape}</p>
        </div>
    </div>

    <div class="verify-footer mt-4" style="margin-top:20px;">
        <p><small>{translate key="document.securedBy"} <strong>{$publisher.name|escape}</strong></small></p>
    </div>
</div>

{include file="common/footer.tpl"}