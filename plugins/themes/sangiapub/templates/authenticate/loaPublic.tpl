{**
 * templates/authenticate/loaPublic.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Frontedge
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * LOA Public the author's submission.
 *
 *}
{strip}
    {assign var="pageTitle" value="Document Validation - LoA"}
    {include file="common/header.tpl"}
{/strip}

<div class="wizdam-verify-container text-center">
    <div class="verify-badge-wrapper">
        <img src="{$baseUrl}/plugins/themes/wizdam/images/verified-seal.png" alt="Verified" width="80">
        <h2 class="text-success">DOCUMENT VERIFIED</h2>
        <p class="text-muted">This Letter of Acceptance is authentic and registered in our system.</p>
    </div>
    <div class="wizdam-card verify-loa-card">
        <h3>{$loaData.title|escape}</h3>
        <p class="authors-list"><strong>Authors:</strong> {$loaData.authors|escape}</p>
        <p class="journal-name"><strong>Target Journal:</strong> {$loaData.journalTitle|escape}</p>
        <p class="accepted-date"><strong>Date Accepted:</strong> {$loaData.dateAccepted|date_format:"%d %B %Y"}</p>
        <hr>
        <h4>Abstract</h4>
        <div class="abstract-content">
            {$loaData.abstract|strip_unsafe_html}
        </div>
    </div>
    <div class="verify-footer mt-4">
        <p><small>Secured by <strong>Lumera Frontedge Verification System</strong></small></p>
    </div>
</div>

{include file="common/footer.tpl"}
