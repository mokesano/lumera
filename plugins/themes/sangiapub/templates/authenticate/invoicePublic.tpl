{strip}
{assign var="pageTitle" value="authenticate.invoiceTitle"}
{include file="common/header.tpl"}
{/strip}

<div class="wizdam-verify-container text-center py-5 u-mt-48">
    
    {* -- BAGIAN HEADER & STEMPEL DINAMIS -- *}
    <div class="verify-badge-wrapper mb-4" style="display: flex;gap: 8px;">
        <fig class="download-icon" style="background-color: blue;height: 32px;width: 32px;display: inline-flex;padding: 4px;border-radius: 50%;">
            <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none">
                <path d="M13.8889 1.85181H5.55559C5.06445 1.85181 4.59342 2.04691 4.24613 2.3942C3.89884 2.74149 3.70374 3.21252 3.70374 3.70366V18.5185C3.70374 19.0096 3.89884 19.4806 4.24613 19.8279C4.59342 20.1752 5.06445 20.3703 5.55559 20.3703H16.6667C17.1578 20.3703 17.6289 20.1752 17.9762 19.8279C18.3234 19.4806 18.5185 19.0096 18.5185 18.5185V6.48144L13.8889 1.85181Z" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12.963 1.85181V5.55551C12.963 6.04665 13.1581 6.51768 13.5054 6.86497C13.8527 7.21226 14.3237 7.40736 14.8149 7.40736H18.5186" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M11.1111 16.6666V11.1111" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M8.33337 13.8889L11.1112 16.6667L13.8889 13.8889" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </fig>
        <fig class="secure-icon" style="display: inline-flex;padding: 4px;border-radius: 50%;height: 32px;width: 32px;border: 1px solid #22C55E;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 22 27" fill="none">
                <path d="M1 4.18656L11.005 1L21 4.18656V10.721C20.9996 14.0697 20.0338 17.3335 18.2394 20.05C16.4449 22.7666 13.9128 24.7982 11.0017 25.8571C8.08942 24.7986 5.55627 22.7668 3.76117 20.0496C1.96606 17.3324 1.00004 14.0677 1 10.7179V4.18656Z" stroke="#22C55E" stroke-width="2" stroke-linejoin="round"></path>
                <path d="M6 12.5192L9.88889 16.7631L16.5556 9.48779" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </fig>
        <fig>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="20 15 90 100" width="32" height="32"><g><path d="M87.779,54.16A23.779,23.779,0,1,0,64,77.94,23.807,23.807,0,0,0,87.779,54.16ZM64,74.44A20.28,20.28,0,1,1,84.279,54.16,20.3,20.3,0,0,1,64,74.44Z"></path><path d="M88.084,76.878a17.1,17.1,0,0,1,2.419-.907c2.029-.65,4.329-1.387,5.464-3.358,1.12-1.936.62-4.272.179-6.333a9.1,9.1,0,0,1-.344-3.6,8.862,8.862,0,0,1,2.026-2.808c1.445-1.591,3.082-3.394,3.082-5.713s-1.638-4.123-3.082-5.714a8.866,8.866,0,0,1-2.027-2.81,9.113,9.113,0,0,1,.345-3.6c.442-2.062.942-4.4-.179-6.333C94.832,33.736,92.533,33,90.5,32.35A9.112,9.112,0,0,1,87.27,30.9a9.1,9.1,0,0,1-1.46-3.24c-.649-2.029-1.386-4.328-3.351-5.46-1.94-1.124-4.275-.625-6.34-.182a9.08,9.08,0,0,1-3.6.344,8.854,8.854,0,0,1-2.807-2.026C68.122,18.887,66.319,17.25,64,17.25s-4.122,1.637-5.713,3.082a8.842,8.842,0,0,1-2.811,2.027,9.072,9.072,0,0,1-3.595-.345c-2.065-.442-4.4-.943-6.334.179-1.971,1.135-2.708,3.434-3.358,5.463a9.086,9.086,0,0,1-1.454,3.234A9.075,9.075,0,0,1,37.5,32.35c-2.029.649-4.328,1.386-5.46,3.352-1.124,1.938-.624,4.276-.183,6.338a9.108,9.108,0,0,1,.345,3.6,8.9,8.9,0,0,1-2.026,2.806c-1.445,1.591-3.082,3.4-3.082,5.714s1.637,4.122,3.082,5.713A8.865,8.865,0,0,1,32.2,62.685a9.111,9.111,0,0,1-.346,3.595c-.441,2.063-.941,4.4.18,6.333,1.135,1.971,3.435,2.708,5.464,3.358a16.889,16.889,0,0,1,2.418.909L29.105,99.989a1.75,1.75,0,0,0,1.872,2.468l10.833-1.794,5.572,9.465a1.748,1.748,0,0,0,1.508.862l.082,0a1.75,1.75,0,0,0,1.5-1.006L60.06,89.5a5.738,5.738,0,0,0,7.879,0l9.586,20.478a1.75,1.75,0,0,0,1.5,1.006l.082,0a1.749,1.749,0,0,0,1.508-.862l5.571-9.465,10.834,1.794a1.749,1.749,0,0,0,1.871-2.468ZM48.713,105.49l-4.495-7.637a1.749,1.749,0,0,0-1.794-.839l-8.74,1.447,8.432-18.028c.027.082.053.163.079.245.65,2.021,1.386,4.313,3.347,5.446,1.936,1.125,4.276.623,6.339.182a9.116,9.116,0,0,1,3.592-.347,5.51,5.51,0,0,1,1.839,1.161Zm11.917-20.1a11,11,0,0,0-4.246-2.808,6.477,6.477,0,0,0-1.673-.206,17.271,17.271,0,0,0-3.564.511c-1.512.324-3.079.661-3.852.211-.8-.459-1.289-2-1.768-3.489a12.211,12.211,0,0,0-2.054-4.355c-.025-.032-.043-.047-.067-.075l-.012-.014c-.068-.078-.138-.16-.19-.213a11.187,11.187,0,0,0-4.64-2.311c-1.494-.479-3.039-.974-3.5-1.776-.446-.769-.111-2.335.213-3.849a11.286,11.286,0,0,0,.3-5.233,10.951,10.951,0,0,0-2.818-4.259c-1.069-1.177-2.173-2.394-2.173-3.361s1.1-2.184,2.173-3.361a10.953,10.953,0,0,0,2.817-4.255,11.283,11.283,0,0,0-.3-5.236c-.324-1.515-.659-3.08-.21-3.855.459-.8,2-1.292,3.5-1.77a11.2,11.2,0,0,0,4.647-2.319,11.194,11.194,0,0,0,2.312-4.64c.479-1.494.974-3.039,1.777-3.5.77-.448,2.335-.112,3.848.212a11.289,11.289,0,0,0,5.233.3,10.943,10.943,0,0,0,4.26-2.818c1.176-1.069,2.393-2.173,3.36-2.173s2.184,1.1,3.36,2.173a10.945,10.945,0,0,0,4.256,2.817,11.283,11.283,0,0,0,5.236-.3c1.515-.325,3.081-.658,3.855-.209.8.458,1.292,2,1.77,3.5A11.206,11.206,0,0,0,84.8,33.37a11.2,11.2,0,0,0,4.64,2.313c1.494.478,3.039.973,3.5,1.775.447.77.112,2.335-.212,3.85a11.282,11.282,0,0,0-.305,5.232A10.94,10.94,0,0,0,95.237,50.8c1.069,1.177,2.173,2.393,2.173,3.361s-1.1,2.184-2.173,3.361a10.942,10.942,0,0,0-2.817,4.255,11.286,11.286,0,0,0,.3,5.237c.324,1.514.659,3.08.212,3.849l0,.005c-.459.8-2,1.292-3.5,1.771a11.184,11.184,0,0,0-4.654,2.326,1.948,1.948,0,0,0-.178.2,1.838,1.838,0,0,0-.208.283,12.9,12.9,0,0,0-1.921,4.159C82,81.1,81.5,82.635,80.7,83.1c-.771.444-2.338.11-3.85-.213a11.293,11.293,0,0,0-5.239-.3,11,11,0,0,0-4.244,2.808C66.19,86.461,64.972,87.57,64,87.57S61.81,86.461,60.63,85.387ZM85.576,97.014a1.752,1.752,0,0,0-1.794.839l-4.495,7.637-8.6-18.37a5.48,5.48,0,0,1,1.836-1.16,9.094,9.094,0,0,1,3.595.346c2.064.441,4.4.943,6.336-.18,1.965-1.135,2.7-3.426,3.351-5.447.026-.081.053-.161.079-.242l8.431,18.024Z"></path></g></svg>
        </fig>
        {if $invoice->isPaid()}
        <fig class="trust-icon">
            <img src="{$baseUrl}/assets/static/images/trust-icon.png" alt="Paid and Verified" width="32" height="32">
        </fig>
            <h2 class="text-success mt-3">PAID & VERIFIED</h2>
            <p class="text-muted">This invoice is authentic, registered in our system, and has been <strong>fully settled</strong>.</p>
        {else}
            <i class="icon-clock" style="font-size: 60px; color: #ff9800;"></i>
            <h2 class="text-warning mt-3">AUTHENTIC (UNPAID)</h2>
            <p class="text-muted">This invoice is an authentic document issued by our system, but the payment is currently <strong>pending</strong>.</p>
        {/if}
    </div>

    {* -- KARTU DETAIL INVOICE -- *}
    <div class="wizdam-card bg-light p-4" style="max-width: 600px; margin: 0 auto; text-align: left;">
        <h3 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; color: #333;">
            Invoice Summary #{$invoice->getInvoiceId()}
        </h3>
        
        <table style="width: 100%; line-height: 2;">
            <tr>
                <td style="width: 40%; font-weight: bold; color: #555;">Fee Type</td>
                <td>: {$invoice->getFeeType()|escape}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #555;">Date Billed</td>
                <td>: {$invoice->getData('dateBilled')|date_format:"%d %B %Y"}</td>
            </tr>
            {if $invoice->isPaid() && $invoice->getData('datePaid')}
            <tr>
                <td style="font-weight: bold; color: #555;">Date Paid</td>
                <td>: {$invoice->getData('datePaid')|date_format:"%d %B %Y"}</td>
            </tr>
            {/if}
            <tr>
                <td style="font-weight: bold; color: #555;">Amount Due</td>
                <td style="font-size: 1.2em;">
                    : <strong>{$invoice->getCurrencyCode()} {$invoice->getAmount()|number_format:2}</strong>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #555;">Payment Status</td>
                <td>: 
                    {if $invoice->isPaid()}
                        <span class="wizdam-badge" style="background-color: #28a745; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 0.9em;">
                            PAID {if $invoice->getPaymentMethod()}via {$invoice->getPaymentMethod()|escape}{/if}
                        </span>
                    {else}
                        <span class="wizdam-badge" style="background-color: #ffc107; color: #333; padding: 3px 8px; border-radius: 4px; font-size: 0.9em;">
                            UNPAID
                        </span>
                    {/if}
                </td>
            </tr>
        </table>

        {* -- CALL TO ACTION JIKA BELUM LUNAS -- *}
        {if !$invoice->isPaid()}
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px dashed #ccc; text-align: center;">
                <p style="font-size: 0.9em; color: #666;">Are you the author? Log in to your dashboard to complete this payment via our secure gateway.</p>
                <a href="{url router=$smarty.const.ROUTE_PAGE page="login"}" class="wizdam-btn wizdam-btn-outline mt-2">Go to Login</a>
            </div>
        {/if}
    </div>
</div>

<div class="wf_authenticate u-mt-32">
    <div class="verify-footer mt-4">
        <p><small>Secured by <strong>Wizdam Frontedge Authenticate System</strong></small></p>
    </div>
</div>

{include file="common/footer.tpl"}