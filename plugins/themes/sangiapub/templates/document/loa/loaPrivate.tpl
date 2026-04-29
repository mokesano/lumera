{strip}
{assign var="pageTitle" value="My Letter of Acceptance"}
{include file="common/header.tpl"}
{/strip}

<div class="wizdam-loa-private-container">
    <div class="pkp_page_title flex-between">
        <h1>Letter of Acceptance</h1>
        <a href="{url router=$smarty.const.ROUTE_PAGE page="checkout" op="download" path=$submissionId}" class="wizdam-btn wizdam-btn-primary">
            <i class="icon-download"></i> Download Official PDF
        </a>
    </div>

    <div class="wizdam-card loa-preview-card mt-3" style="border: 1px solid #ddd; padding: 30px;">
        <div class="text-center mb-4">
            <h2 style="text-transform: uppercase; letter-spacing: 2px;">{$loaData.journalTitle|escape}</h2>
            <hr style="width: 50px; border-top: 2px solid #333;">
        </div>

        <p>Dear <strong>{$loaData.authors|escape}</strong>,</p>
        <p>We are pleased to inform you that your manuscript entitled:</p>
        
        <blockquote style="font-size: 1.2rem; border-left: 4px solid #005c99; padding-left: 15px; background: #f9f9f9;">
            <em>"{$loaData.title|escape}"</em>
        </blockquote>

        <p>has been officially <strong>ACCEPTED</strong> for publication in our upcoming issue.</p>
        <p>Date Accepted: {$loaData.dateAccepted|date_format:"%d %B %Y"}</p>

        <div class="flex-between mt-5 pt-3" style="border-top: 1px dashed #ccc;">
            <div>
                <p>Sincerely,</p>
                <p><strong>Editorial Board</strong></p>
            </div>
            <div class="text-center">
                <img src="{$qrCodeImage}" alt="QR Code" width="100">
                <p><small>Scan to Verify</small></p>
            </div>
        </div>
    </div>
</div>

{include file="common/footer.tpl"}