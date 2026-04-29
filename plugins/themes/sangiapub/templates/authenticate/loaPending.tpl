{strip}
{assign var="pageTitle" value="Verification Pending"}
{include file="common/header.tpl"}
{/strip}

<div class="wizdam-verify-container text-center py-5">
    
    <div class="verify-badge-wrapper mb-4">
        <i class="icon-warning" style="font-size: 60px; color: #ff9800;"></i>
        <h2 class="text-warning mt-3">VERIFICATION PENDING</h2>
    </div>

    <div class="wizdam-card bg-light p-4" style="max-width: 600px; margin: 0 auto;">
        <h4>Document Status: Unresolved</h4>
        <p class="text-muted mt-3">
            The Letter of Acceptance for this manuscript cannot be verified at this time. 
            This is usually because the administrative or publication fees associated with this manuscript have <strong>not been fully settled</strong>.
        </p>
        <p>
            If you are the author, please log in to your dashboard and complete your payment via the Billing section.
        </p>
        
        <a href="{url router=$smarty.const.ROUTE_PAGE page="login"}" class="wizdam-btn wizdam-btn-outline mt-3">Go to Login</a>
    </div>

</div>

{include file="common/footer.tpl"}