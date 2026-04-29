<?php /* Smarty version 2.6.26, created on 2026-04-14 02:26:40
         compiled from checkout/cart.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'number_format', 'checkout/cart.tpl', 47, false),array('modifier', 'escape', 'checkout/cart.tpl', 64, false),array('modifier', 'default', 'checkout/cart.tpl', 109, false),array('function', 'url', 'checkout/cart.tpl', 63, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-payment.tpl", 'smarty_include_vars' => array('pageTitle' => "checkout.cart.title")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<style>
    /* WIZDAM Checkout UI - Elsevier Inspired */
    .wizdam-checkout-container { display: flex; flex-wrap: wrap; gap: 30px; margin-top: 20px; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; }
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
'; ?>


<div class="wizdam-checkout-container">
    <div class="wizdam-checkout-main">
        
        <div class="product-box">
            <div>
                <div class="product-title">Article Publication Charge (APC)</div>
                <div class="product-meta">Manuscript ID: #<?php echo $this->_tpl_vars['articleId']; ?>
</div>
                <div style="font-size: 14px;">Standar publikasi jurnal setelah naskah dinyatakan diterima (Accepted).</div>
            </div>
            <div style="font-weight: bold; font-size: 16px;">
                <?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['base_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>

            </div>
        </div>

        <?php if ($this->_tpl_vars['fastTrackPrice'] > 0): ?>
        <div class="option-box">
            <label style="display: flex; align-items: flex-start; cursor: pointer;">
                <input type="checkbox" id="fastTrackCheck" style="margin-top: 4px; margin-right: 15px; width: 18px; height: 18px;" onchange="updateCartLocally()">
                <div>
                    <strong style="font-size: 15px; display: block; color: #0056b3;">Apply Fast-Track Review (+ <?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['fastTrackPrice'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
)</strong>
                    <span style="font-size: 13px; color: #555;">Percepatan proses review dan prioritas publikasi naskah.</span>
                </div>
            </label>
        </div>
        <?php endif; ?>

        <form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'checkoutSubmit','path' => $this->_tpl_vars['articleId']), $this);?>
" method="post" id="checkoutForm" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
            <input type="hidden" name="feeType" value="PUBLICATION">
            <input type="hidden" name="amount" value="<?php echo $this->_tpl_vars['summary']['base_amount']; ?>
">
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
            <span id="ui-subtotal"><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
        </div>
        
        <div class="summary-row" id="row-discount" style="display: none; color: #28a745;">
            <span>Discount <span id="ui-promo-label"></span></span>
            <span id="ui-discount">- <?php echo $this->_tpl_vars['summary']['currency']; ?>
 0.00</span>
        </div>
        
        <div class="summary-row">
            <span>Tax (<?php echo $this->_tpl_vars['summary']['tax_rate']; ?>
%) <?php if ($this->_tpl_vars['summary']['is_tax_inclusive']): ?><small>(Inclusive)</small><?php endif; ?></span>
            <span id="ui-tax"><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['tax_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
        </div>

        <div class="summary-total">
            <span>Today's payment</span>
            <span id="ui-grand-total"><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['grand_total'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
        </div>

        <div class="promo-box">
            <input type="text" id="promoCodeInput" class="wizdam-input" placeholder="Enter promo code">
            <button class="wizdam-btn-secondary" type="button" onclick="applyPromo()">Apply</button>
        </div>
    </div>
</div>

<script>
    const currencySym = "<?php echo $this->_tpl_vars['summary']['currency']; ?>
";
    const fastTrackPrice = parseFloat("<?php echo ((is_array($_tmp=@$this->_tpl_vars['fastTrackPrice'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)); ?>
"); 
    
    let currentPromoCode = "";

<?php echo '
    function updateCartLocally() {
        let isFastTrack = document.getElementById(\'fastTrackCheck\') ? document.getElementById(\'fastTrackCheck\').checked : false;
        
        document.getElementById(\'form-fastTrack\').value = isFastTrack ? "1" : "0";
        document.getElementById(\'form-promoCode\').value = currentPromoCode;

        let additionalItems = isFastTrack ? [{ type: \'FAST_TRACK\', amount: fastTrackPrice }] : [];

        let formData = new URLSearchParams();
        formData.append(\'csrfToken\', \''; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['ajaxCsrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '\');
        formData.append(\'queuedPaymentId\', \''; ?>
<?php echo $this->_tpl_vars['queuedPaymentId']; ?>
<?php echo '\');
        formData.append(\'additionalItems\', JSON.stringify(additionalItems));
        formData.append(\'promoCode\', currentPromoCode);

        // PERBAIKAN: Endpoint Fetch harus mengarah ke page="checkout", BUKAN page="billing"
        fetch(\''; ?>
<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'updateCartAjax'), $this);?>
<?php echo '\', {
            method: \'POST\',
            body: formData,
            headers: { \'Content-Type\': \'application/x-www-form-urlencoded\', \'X-Requested-With\': \'XMLHttpRequest\' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === \'success\') {
                let s = data.summary;
                
                document.getElementById(\'ui-subtotal\').innerText = s.currency + \' \' + formatMoney(s.subtotal);
                document.getElementById(\'ui-tax\').innerText = s.currency + \' \' + formatMoney(s.tax_amount);
                document.getElementById(\'ui-grand-total\').innerText = s.currency + \' \' + formatMoney(s.grand_total);
                
                let discRow = document.getElementById(\'row-discount\');
                if (s.discount > 0) {
                    discRow.style.display = \'flex\';
                    document.getElementById(\'ui-discount\').innerText = \'- \' + s.currency + \' \' + formatMoney(s.discount);
                    document.getElementById(\'ui-promo-label\').innerText = \'(\' + (s.promo_code || currentPromoCode) + \')\';
                } else {
                    discRow.style.display = \'none\';
                    if (currentPromoCode !== "") {
                        alert("Kupon tidak valid atau telah kadaluarsa.");
                        currentPromoCode = ""; 
                        document.getElementById(\'form-promoCode\').value = "";
                        document.getElementById(\'promoCodeInput\').value = "";
                    }
                }
            }
        });
    }

    function applyPromo() {
        let code = document.getElementById(\'promoCodeInput\').value.trim().toUpperCase();
        if (code === \'\') {
            alert("Silakan masukkan kode promo.");
            return;
        }
        currentPromoCode = code;
        updateCartLocally(); 
    }

    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString(\'en-US\', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
'; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>