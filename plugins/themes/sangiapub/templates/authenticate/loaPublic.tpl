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
        <p><small>Secured by <strong>Wizdam Frontedge Verification System</strong></small></p>
    </div>
</div>

{include file="common/footer.tpl"}