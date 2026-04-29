{**
 * templates/checkout/cart.tpl
 *
 * [WIZDAM EDITION]
 * Pre-Checkout: Keranjang Belanja Publik (Belum masuk antrean DB)
 *}
{include file="common/header-parts/header-payment.tpl" pageTitle="checkout.cart.title"}

{literal}
<style>
    /* WIZDAM Checkout UI - Elsevier Inspired */
    .wizdam-checkout-container { display: flex; flex-wrap: wrap; gap: 30px; margin-top: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .wizdam-checkout-main { flex: 1; min-width: 60%; }
    .wizdam-checkout-sidebar { width: 350px; background: #f8f9fa; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0; height: fit-content; }
    
    /* Product Item */
    .product-box { border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; }
    .product-title { font-size: 18px; font-weight: 600; margin-bottom: 5px; color: #333; }
    .product-meta { font-size: 13px; color: #666; margin-bottom: 15px; }
    
    /* Options & Promo */
    .option-box { background: #f0f4f8; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #0056b3; }
    .promo-box { display: flex; gap: 10px; margin-top: 20px; }
    .wizdam-input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    
    /* Summary */
    .summary-title { font-size: 20px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #444; }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 18px; font-weight: bold; color: #000; }
    
    .wizdam-btn-primary { background: #0056b3; color: white; border: none; padding: 12px 24px; width: fit-content; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; text-align: center; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; margin-top: 10px; transition: background 0.3s; }
    .wizdam-btn-primary:hover { background: #004494; text-decoration: none; color: white;}
    .wizdam-btn-secondary { background: #e0e0e0; color: #333; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
</style>
{/literal}

<div class="wizdam-checkout-container">
    <div class="wizdam-checkout-main">
        
        <div class="product-box">
            <div>
                <div class="product-title">Article Publication Charge (APC)</div>
                <div class="product-meta">Manuscript ID: #{$articleId}</div>
                <div style="font-size: 14px;">Standar publikasi jurnal setelah naskah dinyatakan diterima (Accepted).</div>
            </div>
            <div style="font-weight: bold; font-size: 16px;">
                {$summary.currency} {$summary.base_amount|number_format:2}
            </div>
        </div>

        {if $fastTrackPrice > 0}
        <div class="option-box">
            <label style="display: flex; align-items: flex-start; cursor: pointer;">
                <input type="checkbox" id="fastTrackCheck" style="margin-top: 4px; margin-right: 15px; width: 18px; height: 18px;" onchange="updateCartLocally()">
                <div>
                    <strong style="font-size: 15px; display: block; color: #0056b3;">Apply Fast-Track Review (+ {$summary.currency} {$fastTrackPrice|number_format:2})</strong>
                    <span style="font-size: 13px; color: #555;">Percepatan proses review dan prioritas publikasi naskah.</span>
                </div>
            </label>
        </div>
        {/if}

        <form action="{url page="checkout" op="checkoutSubmit" path=$articleId}" method="post" id="checkoutForm" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <input type="hidden" name="csrfToken" value="{$csrfToken|escape}">
            <input type="hidden" name="feeType" value="PUBLICATION">
            <input type="hidden" name="amount" value="{$summary.base_amount}">
            <input type="hidden" name="fastTrack" id="form-fastTrack" value="0">
            <input type="hidden" name="promoCode" id="form-promoCode" value="">
            
            <button type="submit" class="wizdam-btn-primary">
                Continue to Checkout <span>&rsaquo;</span>
            </button>
        </form>

    </div>

    <div class="wizdam-checkout-sidebar">
        <div class="summary-title">Order summary</div>
        
        <div class="summary-row">
            <span>Subtotal</span>
            <span id="ui-subtotal">{$summary.currency} {$summary.subtotal|number_format:2}</span>
        </div>
        
        <div class="summary-row" id="row-discount" style="display: none; color: #28a745;">
            <span>Discount <span id="ui-promo-label"></span></span>
            <span id="ui-discount">- {$summary.currency} 0.00</span>
        </div>
        
        <div class="summary-row">
            <span>Tax ({$summary.tax_rate}%) {if $summary.is_tax_inclusive}<small>(Inclusive)</small>{/if}</span>
            <span id="ui-tax">{$summary.currency} {$summary.tax_amount|number_format:2}</span>
        </div>

        <div class="summary-total">
            <span>Today's payment</span>
            <span id="ui-grand-total">{$summary.currency} {$summary.grand_total|number_format:2}</span>
        </div>

        <div class="promo-box">
            <input type="text" id="promoCodeInput" class="wizdam-input" placeholder="Enter promo code">
            <button class="wizdam-btn-secondary" type="button" onclick="applyPromo()">Apply</button>
        </div>
    </div>
</div>

<script>
    const currencySym = "{$summary.currency}";
    const fastTrackPrice = parseFloat("{$fastTrackPrice|default:0}"); 
    
    let currentPromoCode = "";

{literal}
    function updateCartLocally() {
        let isFastTrack = document.getElementById('fastTrackCheck') ? document.getElementById('fastTrackCheck').checked : false;
        
        document.getElementById('form-fastTrack').value = isFastTrack ? "1" : "0";
        document.getElementById('form-promoCode').value = currentPromoCode;

        let additionalItems = isFastTrack ? [{ type: 'FAST_TRACK', amount: fastTrackPrice }] : [];

        let formData = new URLSearchParams();
        formData.append('csrfToken', '{/literal}{$ajaxCsrfToken|escape}{literal}');
        formData.append('queuedPaymentId', '{/literal}{$queuedPaymentId}{literal}');
        formData.append('additionalItems', JSON.stringify(additionalItems));
        formData.append('promoCode', currentPromoCode);

        // PERBAIKAN: Endpoint Fetch harus mengarah ke page="checkout", BUKAN page="billing"
        fetch('{/literal}{url page="checkout" op="updateCartAjax"}{literal}', {
            method: 'POST',
            body: formData,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                let s = data.summary;
                
                document.getElementById('ui-subtotal').innerText = s.currency + ' ' + formatMoney(s.subtotal);
                document.getElementById('ui-tax').innerText = s.currency + ' ' + formatMoney(s.tax_amount);
                document.getElementById('ui-grand-total').innerText = s.currency + ' ' + formatMoney(s.grand_total);
                
                let discRow = document.getElementById('row-discount');
                if (s.discount > 0) {
                    discRow.style.display = 'flex';
                    document.getElementById('ui-discount').innerText = '- ' + s.currency + ' ' + formatMoney(s.discount);
                    document.getElementById('ui-promo-label').innerText = '(' + (s.promo_code || currentPromoCode) + ')';
                } else {
                    discRow.style.display = 'none';
                    if (currentPromoCode !== "") {
                        alert("Kupon tidak valid atau telah kadaluarsa.");
                        currentPromoCode = ""; 
                        document.getElementById('form-promoCode').value = "";
                        document.getElementById('promoCodeInput').value = "";
                    }
                }
            }
        });
    }

    function applyPromo() {
        let code = document.getElementById('promoCodeInput').value.trim().toUpperCase();
        if (code === '') {
            alert("Silakan masukkan kode promo.");
            return;
        }
        currentPromoCode = code;
        updateCartLocally(); 
    }

    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
{/literal}
</script>

{include file="common/footer.tpl"}