<?php /* Smarty version 2.6.26, created on 2026-04-14 10:32:47
         compiled from checkout/payment.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'checkout/payment.tpl', 95, false),array('modifier', 'escape', 'checkout/payment.tpl', 97, false),array('modifier', 'number_format', 'checkout/payment.tpl', 182, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-payment.tpl", 'smarty_include_vars' => array('pageTitle' => "checkout.payment.title")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<style>
    /* WIZDAM Checkout UI - Elsevier Inspired */
    .wizdam-checkout-wrapper { max-width: 1200px; margin: 0 auto; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
    
    /* Stepper UI */
    .wizdam-stepper { display: flex; justify-content: center; align-items: flex-start; margin: 40px 0 50px 0; position: relative; }
    .step-item { display: flex; flex-direction: column; align-items: center; z-index: 2; background: white; padding: 0 15px; width: 120px; }
    .step-circle { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #ccc; background: white; color: #ccc; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 14px; margin-bottom: 8px; transition: all 0.3s; }
    .step-label { font-size: 13px; color: #666; font-weight: 600; text-align: center; }
    
    .step-item.active .step-circle { border-color: #0056b3; background: #0056b3; color: white; }
    .step-item.active .step-label { color: #111; }
    .step-item.completed .step-circle { border-color: #0056b3; color: #0056b3; }
    .step-item.completed .step-label { color: #111; }
    
    .stepper-line { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); width: 300px; height: 1px; background: #ccc; z-index: 1; }
    .stepper-line-active { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); width: 300px; height: 1px; background: #0056b3; z-index: 1; }

    /* Layout Container */
    .wizdam-checkout-container { display: flex; flex-wrap: wrap; gap: 40px; margin-top: 20px; }
    .wizdam-checkout-main { flex: 1; min-width: 60%; }
    .wizdam-checkout-sidebar { width: 360px; background: #fafbfc; padding: 25px; border: 1px solid #e1e4e8; height: fit-content; border-radius: 4px; }
    
    /* Typography & Trust Badges */
    .wizdam-page-title { font-size: 28px; font-weight: 700; margin-bottom: 10px; color: #111; }
    .trust-badge { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #1e8e3e; font-weight: 600; margin-bottom: 30px; padding: 10px 15px; background: #e6f4ea; border-radius: 4px; width: fit-content; }
    
    /* Payment Options Accordion */
    .payment-methods-container { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
    .payment-box { border: 1px solid #ccc; border-radius: 6px; transition: all 0.2s; background: white; overflow: hidden; }
    .payment-box.selected { border: 2px solid #0056b3; background: #f8fbff; }
    
    .payment-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; cursor: pointer; }
    .payment-label { display: flex; align-items: center; gap: 12px; font-size: 16px; font-weight: 600; color: #333; }
    .payment-label input[type="radio"] { width: 18px; height: 18px; cursor: pointer; margin: 0; }
    
    .payment-icons { display: flex; gap: 6px; align-items: center; }
    .payment-icons span { height: 22px; border-radius: 3px; border: 1px solid #ddd; background: white; padding: 2px 6px; font-size: 11px; font-weight: bold; line-height: 18px; color: #555; }
    .icon-qris { color: #ed2c25 !important; border-color: #ed2c25 !important; }
    .icon-gpay { color: #333 !important; font-weight: 600 !important; }
    
    .payment-details { display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #555; line-height: 1.6; }
    .payment-box.selected .payment-details { display: block; border-top: 1px solid transparent; }
    
    /* Forms (Active Inputs) */
    .wizdam-input, .wizdam-select { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; font-family: inherit; margin-bottom: 15px; margin-top: 10px; box-sizing: border-box; }
    .wizdam-input:focus, .wizdam-select:focus { outline: none; border-color: #0056b3; box-shadow: 0 0 0 2px rgba(0, 86, 179, 0.1); }
    .form-row { display: flex; gap: 15px; }
    .form-row .wizdam-input { flex: 1; }

    /* Action Buttons */
    .wizdam-btn-pay { background: #0056b3; color: white; border: none; padding: 14px 24px; width: 100%; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: background 0.2s; margin-top: 15px; }
    .wizdam-btn-pay:hover { background: #004494; }
    .wizdam-btn-pay:disabled { background: #80abda; cursor: not-allowed; }

    /* Sidebar Summary */
    .summary-title { font-size: 20px; font-weight: bold; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #111; display: flex; justify-content: space-between; align-items: center;}
    .badge-locked { font-size: 12px; font-weight: normal; color: #0056b3; background: #e6f0fa; padding: 3px 8px; border-radius: 12px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #444; }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 18px; font-weight: bold; color: #000; }
</style>
'; ?>


<div class="wizdam-checkout-wrapper">

    <div class="wizdam-stepper">
        <div class="stepper-line"></div>
        <div class="stepper-line-active"></div>
        
        <div class="step-item completed"><div class="step-circle">&#10003;</div><div class="step-label">Billing details</div></div>
        <div class="step-item completed"><div class="step-circle">&#10003;</div><div class="step-label">Review order</div></div>
        <div class="step-item active"><div class="step-circle">3</div><div class="step-label">Payment</div></div>
    </div>

    <div class="wizdam-checkout-container">
        
        <div class="wizdam-checkout-main">
            
            <div class="wizdam-page-title">Payment method</div>
            <div class="trust-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Secure, encrypted transaction
            </div>

            <form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'payment','path' => $this->_tpl_vars['queuedPaymentId']), $this);?>
" method="POST" id="execute-payment-form">
                
                <input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                <input type="hidden" name="paymentMethod" id="selected-method" value="virtual_account">

                <div class="payment-methods-container">
                    
                    <div class="payment-box selected" id="box-va">
                        <div class="payment-header" onclick="selectMethod('va')">
                            <div class="payment-label">
                                <input type="radio" id="radio-va" name="pay_selector" value="va" checked>
                                <label for="radio-va" style="cursor: pointer;">Virtual Account (VA)</label>
                            </div>
                            <div class="payment-icons"><span>BCA</span><span>Mandiri</span><span>BNI</span><span>BRI</span></div>
                        </div>
                        <div class="payment-details">
                            <p style="margin-top: 0;">Pay automatically via your preferred bank's Virtual Account. The invoice status will update instantly.</p>
                            <select class="wizdam-select" id="va-bank-select">
                                <option value="">Select your bank...</option>
                                <option value="bca">BCA Virtual Account</option>
                                <option value="mandiri">Mandiri Virtual Account</option>
                                <option value="bni">BNI Virtual Account</option>
                                <option value="bri">BRIVA</option>
                            </select>
                            <button type="button" class="wizdam-btn-pay" onclick="executePayment('virtual_account', this, event)">
                                Generate VA Number
                            </button>
                        </div>
                    </div>

                    <div class="payment-box" id="box-qris">
                        <div class="payment-header" onclick="selectMethod('qris')">
                            <div class="payment-label">
                                <input type="radio" id="radio-qris" name="pay_selector" value="qris">
                                <label for="radio-qris" style="cursor: pointer;">QRIS (Quick Response)</label>
                            </div>
                            <div class="payment-icons"><span class="icon-qris">QRIS</span></div>
                        </div>
                        <div class="payment-details">
                            <p style="margin-top: 0;">Pay instantly using any supported mobile banking or e-wallet application by scanning the QR code.</p>
                            <button type="button" class="wizdam-btn-pay" onclick="executePayment('qris', this, event)">
                                Generate QRIS Code
                            </button>
                        </div>
                    </div>

                    <div class="payment-box" id="box-ewallet">
                        <div class="payment-header" onclick="selectMethod('ewallet')">
                            <div class="payment-label">
                                <input type="radio" id="radio-ewallet" name="pay_selector" value="ewallet">
                                <label for="radio-ewallet" style="cursor: pointer;">E-Wallet</label>
                            </div>
                            <div class="payment-icons">
                                <span style="color:#00a5cf; border-color:#00a5cf;">DANA</span>
                                <span style="color:#00aa13; border-color:#00aa13;">GoPay</span>
                                <span style="color:#ee4d2d; border-color:#ee4d2d;">Shopee</span>
                            </div>
                        </div>
                        <div class="payment-details">
                            <p style="margin-top: 0;">You will be redirected to your selected E-Wallet application to authorize the transaction.</p>
                            <select class="wizdam-select" id="ewallet-select">
                                <option value="">Choose E-Wallet...</option>
                                <option value="gopay">GoPay / Gojek Pay</option>
                                <option value="dana">DANA</option>
                                <option value="shopeepay">ShopeePay</option>
                            </select>
                            <button type="button" class="wizdam-btn-pay" onclick="executePayment('ewallet', this, event)">
                                Pay with E-Wallet
                            </button>
                        </div>
                    </div>

                    <div class="payment-box" id="box-cc">
                        <div class="payment-header" onclick="selectMethod('cc')">
                            <div class="payment-label">
                                <input type="radio" id="radio-cc" name="pay_selector" value="cc">
                                <label for="radio-cc" style="cursor: pointer;">Credit / Debit Card</label>
                            </div>
                            <div class="payment-icons"><span>VISA</span><span>MasterCard</span></div>
                        </div>
                        <div class="payment-details">
                            <input type="text" id="cc-number" class="wizdam-input" placeholder="Card number (16 digits)" maxlength="16">
                            <div class="form-row">
                                <input type="text" id="cc-expiry" class="wizdam-input" placeholder="MM / YY" maxlength="5">
                                <input type="password" id="cc-cvv" class="wizdam-input" placeholder="CVV" maxlength="4">
                            </div>
                            <button type="button" class="wizdam-btn-pay" onclick="executePayment('credit_card', this, event)">
                                Pay <?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['grand_total'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>

                            </button>
                        </div>
                    </div>

                    <div class="payment-box" id="box-gpay">
                        <div class="payment-header" onclick="selectMethod('gpay')">
                            <div class="payment-label">
                                <input type="radio" id="radio-gpay" name="pay_selector" value="gpay">
                                <label for="radio-gpay" style="cursor: pointer;">Google Pay</label>
                            </div>
                            <div class="payment-icons"><span class="icon-gpay">G Pay</span></div>
                        </div>
                        <div class="payment-details">
                            <p style="margin-top: 0;">Fast and secure checkout. Your payment details are encrypted by Google.</p>
                            <button type="button" class="wizdam-btn-pay" style="background: #111;" onclick="executePayment('google_pay', this, event)">
                                Pay with GPay
                            </button>
                        </div>
                    </div>

                    <div class="payment-box" id="box-manual">
                        <div class="payment-header" onclick="selectMethod('manual')">
                            <div class="payment-label">
                                <input type="radio" id="radio-manual" name="pay_selector" value="manual">
                                <label for="radio-manual" style="cursor: pointer;">Manual Bank Transfer</label>
                            </div>
                            <div class="payment-icons"><span>BANK</span></div>
                        </div>
                        <div class="payment-details">
                            <p style="margin-top: 0;">An official invoice will be generated. You will be required to transfer manually and upload your proof of payment via the Billing dashboard.</p>
                            <button type="button" class="wizdam-btn-pay" onclick="executePayment('manual', this, event)">
                                Confirm Manual Transfer
                            </button>
                        </div>
                    </div>

                </div>
            </form>
            <div style="text-align: center; margin-top: 5px; font-size: 13px; color: #666;">
                By confirming this payment, you agree to our publisher policies.
            </div>
        </div>

        <div class="wizdam-checkout-sidebar">
            <div class="summary-title">Order summary <span class="badge-locked">Locked</span></div>
            
            <div class="summary-row"><span>Subtotal</span><span><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span></div>
            
            <?php if ($this->_tpl_vars['summary']['discount'] > 0): ?>
            <div class="summary-row" style="color: #28a745;"><span>Discount (<?php echo $this->_tpl_vars['summary']['promo_code']; ?>
)</span><span>- <?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['discount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span></div>
            <?php endif; ?>

            <div class="summary-row"><span>Tax (<?php echo $this->_tpl_vars['summary']['tax_rate']; ?>
%)</span><span><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['tax_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span></div>
            
            <div class="summary-total"><span>Total to pay</span><span><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['grand_total'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span></div>
        </div>

    </div>
</div>

<script>
<?php echo '
    function selectMethod(methodId) {
        // Reset all boxes
        document.querySelectorAll(\'.payment-box\').forEach(box => {
            box.classList.remove(\'selected\');
        });
        
        // Active selected box
        document.getElementById(\'box-\' + methodId).classList.add(\'selected\');
        document.getElementById(\'radio-\' + methodId).checked = true;
    }

    function executePayment(gatewayName, btnElement, event) {
        event.preventDefault();
        event.stopPropagation();
        
        document.getElementById(\'selected-method\').value = gatewayName;
        
        // 1. Validasi VA
        if (gatewayName === \'virtual_account\' && document.getElementById(\'va-bank-select\').value === "") {
            alert("Please select a bank for your Virtual Account.");
            return;
        }

        // 2. Validasi E-Wallet
        if (gatewayName === \'ewallet\' && document.getElementById(\'ewallet-select\').value === "") {
            alert("Please select your E-Wallet application.");
            return;
        }

        // 3. Validasi Credit Card
        if (gatewayName === \'credit_card\') {
            const num = document.getElementById(\'cc-number\').value.trim();
            const exp = document.getElementById(\'cc-expiry\').value.trim();
            const cvv = document.getElementById(\'cc-cvv\').value.trim();
            
            if (num.length < 15 || exp.length < 5 || cvv.length < 3) {
                alert("Please enter valid card details to proceed.");
                return;
            }
            // (Opsional) Jika Anda akan mengirim ke Midtrans/Xendit Tokenizer, logikanya masuk di sini sebelum submit form.
        }

        // Visual Feedback (Loading State)
        btnElement.innerHTML = \'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite; margin-right:8px;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg> Redirecting...\';
        btnElement.style.background = \'#80abda\';
        btnElement.style.cursor = \'not-allowed\';
        
        // Disable all buttons to prevent double click
        document.querySelectorAll(\'.wizdam-btn-pay\').forEach(b => b.disabled = true);
        
        // Execute Form POST
        document.getElementById(\'execute-payment-form\').submit();
    }
'; ?>

</script>

<?php echo '
<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>
'; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
