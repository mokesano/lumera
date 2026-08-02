{**
 * templates/authenticate/loaPending.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Frontedge
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * LOA Pending the author's submission.
 *
 *}
{strip}
    {include file="common/header.tpl"}
{/strip}

<div class="wizdam-verify-container text-center py-5">
    <div class="verify-badge-wrapper mb-4">
        <i class="icon-warning" style="font-size: 60px; color: #ff9800;"></i>
        <h2 class="text-warning mt-3">{translate key="authenticate.loa.pendingHeading"}</h2>
    </div>
    <div class="wizdam-card bg-light p-4" style="max-width: 600px; margin: 0 auto;">
        <h4>{translate key="authenticate.loa.pendingStatusLabel"}</h4>
        <p class="text-muted mt-3">
            {if $message}{translate|assign:"messageTranslated" key=$message}{/if}
            {$messageTranslated}
        </p>
        <p>
            {translate key="authenticate.loa.pendingLoginPrompt"}
        </p>
        <a href="{url router=$smarty.const.ROUTE_PAGE page="login"}" class="wizdam-btn wizdam-btn-outline mt-3">{translate key="user.login"}</a>
    </div>
</div>

{include file="common/footer.tpl"}