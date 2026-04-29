<?php /* Smarty version 2.6.26, created on 2026-04-14 02:26:02
         compiled from checkout/billing.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'checkout/billing.tpl', 99, false),array('modifier', 'number_format', 'checkout/billing.tpl', 185, false),array('function', 'html_options', 'checkout/billing.tpl', 112, false),array('function', 'url', 'checkout/billing.tpl', 162, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-payment.tpl", 'smarty_include_vars' => array('pageTitle' => "checkout.billing.title")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo '
<style>
    /* WIZDAM Checkout UI - Elsevier Inspired */
    .wizdam-checkout-wrapper { max-width: 1200px; margin: 0 auto; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
    
    /* Stepper UI Elsevier Style */
    .wizdam-stepper { display: flex; justify-content: center; align-items: flex-start; margin: 40px 0 50px 0; position: relative; }
    .step-item { display: flex; flex-direction: column; align-items: center; z-index: 2; background: white; padding: 0 15px; width: 120px; }
    .step-circle { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #ccc; background: white; color: #ccc; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 14px; margin-bottom: 8px; transition: all 0.3s; }
    .step-label { font-size: 13px; color: #666; font-weight: 600; text-align: center; }
    
    .step-item.active .step-circle { border-color: #0056b3; background: #0056b3; color: white; }
    .step-item.active .step-label { color: #111; }
    .step-item.completed .step-circle { border-color: #0056b3; color: #0056b3; }
    .step-item.completed .step-label { color: #111; }
    
    .stepper-line { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); width: 300px; height: 1px; background: #ccc; z-index: 1; }
    .stepper-line-active { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); width: 150px; height: 1px; background: #0056b3; z-index: 1; display: none; }

    /* Layout Container */
    .wizdam-checkout-container { display: flex; flex-wrap: wrap; gap: 40px; margin-top: 20px; }
    .wizdam-checkout-main { flex: 1; min-width: 60%; }
    .wizdam-checkout-sidebar { width: 360px; background: #fafbfc; padding: 25px; border: 1px solid #e1e4e8; height: fit-content; border-radius: 4px; }
    
    /* Typography & Spacing */
    .wizdam-page-title { font-size: 28px; font-weight: 700; margin-bottom: 30px; color: #111; }
    .section-title { font-size: 20px; font-weight: 700; margin: 25px 0 10px 0; display: flex; align-items: center; gap: 15px; }
    .section-desc { font-size: 14px; color: #555; margin-bottom: 20px; line-height: 1.5; }
    .contact-info { font-size: 15px; line-height: 1.6; color: #111; margin-bottom: 10px; }
    
    /* Forms */
    .form-group { margin-bottom: 20px; }
    .form-row { display: flex; gap: 20px; }
    .form-row .form-group { flex: 1; }
    label.wizdam-label { display: block; font-size: 14px; margin-bottom: 6px; color: #333; }
    .wizdam-label span { color: #d93025; }
    .wizdam-input, .wizdam-select { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; font-family: inherit; box-sizing: border-box; }
    .wizdam-input:focus, .wizdam-select:focus { outline: none; border-color: #0056b3; box-shadow: 0 0 0 2px rgba(0, 86, 179, 0.2); }
    
    /* Legal / ToC */
    .legal-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 30px 0; border: 1px solid #e1e4e8; display: flex; align-items: flex-start; gap: 10px; }
    .legal-box input[type="checkbox"] { margin-top: 4px; width: 18px; height: 18px; cursor: pointer; }
    .legal-box label { font-size: 14px; line-height: 1.5; cursor: pointer; color: #333; }
    .legal-box a { color: #0056b3; text-decoration: none; }
    
    /* Buttons */
    .wizdam-btn-primary { background: #0056b3; color: white; border: none; padding: 12px 24px; width: fit-content; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; display: inline-flex; justify-content: space-between; align-items: center; transition: background 0.2s; margin-top: 10px; }
    .wizdam-btn-primary:hover { background: #004494; }
    .wizdam-btn-primary:disabled { background: #80abda; cursor: not-allowed; }
    .btn-edit { font-size: 14px; color: #0056b3; font-weight: normal; cursor: pointer; text-decoration: none; }

    /* Sidebar Summary */
    .summary-title { font-size: 20px; font-weight: bold; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #111; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #444; }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccc; font-size: 18px; font-weight: bold; color: #000; }
    .payment-badges { margin-top: 30px; font-size: 13px; color: #666; }
    .badges-container { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
    .badge-icon { border: 1px solid #e0e0e0; border-radius: 4px; padding: 2px 6px; background: white; font-size: 11px; font-weight: bold; color: #555; }
</style>
'; ?>


<div class="wizdam-checkout-wrapper">

    <div class="wizdam-stepper">
        <div class="stepper-line"></div>
        <div class="stepper-line-active" id="stepper-progress"></div>
        
        <div class="step-item active" id="indicator-step-1">
            <div class="step-circle">&#10003;</div>
            <div class="step-label">Billing details</div>
        </div>
        <div class="step-item" id="indicator-step-2">
            <div class="step-circle">2</div>
            <div class="step-label">Review order</div>
        </div>
        <div class="step-item">
            <div class="step-circle">3</div>
            <div class="step-label">Payment</div>
        </div>
    </div>

    <div class="wizdam-checkout-container">
        
        <div class="wizdam-checkout-main">
            
            <div id="container-step-1">
                <div class="wizdam-page-title">Billing details</div>
                
                <div class="section-title">Contact details</div>
                <div class="contact-info">
                    <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['userFullName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><br>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['userEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                </div>
                <div class="section-desc">We will send the confirmation email to this address once the payment has been received.</div>

                <div class="section-title">Billing address</div>
                <div class="section-desc">The address on your credit card statement. If you plan to claim these expenses, check your organization's reimbursement policy for address information.</div>

                <form id="wizdam-billing-form" onsubmit="return false;">
                    <div class="form-group">
                        <label class="wizdam-label">Country <span>*</span></label>
                        <select id="input-country" class="wizdam-select" required>
                            <option value="">Select Country</option>
                            <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['countries'],'selected' => $this->_tpl_vars['defaultAddress']['country']), $this);?>

                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="wizdam-label">City <span>*</span></label>
                            <input type="text" id="input-city" class="wizdam-input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultAddress']['city'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                        </div>
                        <div class="form-group">
                            <label class="wizdam-label">Zip/Postal Code <span>*</span></label>
                            <input type="text" id="input-zip" class="wizdam-input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultAddress']['postal_code'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="wizdam-label">Street Address <span>*</span></label>
                        <input type="text" id="input-street" class="wizdam-input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultAddress']['address'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" required>
                    </div>

                    <button type="button" id="btn-to-review" class="wizdam-btn-primary">
                        Review order &rsaquo;
                    </button>
                </form>
            </div>

            <div id="container-step-2" style="display: none;">
                <div class="wizdam-page-title" style="display: flex; align-items: center; gap: 15px;">
                    <a href="#" id="btn-back" style="font-size: 16px; color: #666; text-decoration: none;">&lsaquo; Back</a>
                    Review order details
                </div>
                
                <div class="section-title">Contact details</div>
                <div class="contact-info">
                    <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['userFullName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><br>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['userEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                </div>
                <div class="section-desc">We will send the confirmation email to this address once the payment has been received.</div>

                <div class="section-title">Billing address <a href="#" id="btn-edit-address" class="btn-edit">Edit</a></div>
                <div class="contact-info" id="review-address-display">
                    </div>

                <div class="legal-box">
                    <input type="checkbox" id="toc-checkbox">
                    <label for="toc-checkbox">
                        I accept the <a href="#" target="_blank">Privacy Policy</a> and the <a href="#" target="_blank">Terms and Conditions</a>. I also confirm that I am not a corporate or commercial user (required). <span>*</span>
                    </label>
                </div>

                <form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'billing','path' => $this->_tpl_vars['queuedPaymentId']), $this);?>
" method="POST" id="final-payment-form">
                    <input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                    
                    <input type="hidden" name="billingCountry" id="hidden-country">
                    <input type="hidden" name="billingCity" id="hidden-city">
                    <input type="hidden" name="billingPostalCode" id="hidden-zip">
                    <input type="hidden" name="billingStreetAddress" id="hidden-street">
                    
                    <input type="hidden" name="acceptTerms" value="1">
                    
                    <button type="submit" id="btn-final-checkout" class="wizdam-btn-primary" disabled>
                        Continue to payment &rsaquo;
                    </button>
                </form>
            </div>

        </div>

        <div class="wizdam-checkout-sidebar">
            <div class="summary-title">Order summary</div>
            
            <div class="summary-row">
                <span>Subtotal</span>
                <span><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['subtotal'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
            </div>
            
            <?php if ($this->_tpl_vars['summary']['discount'] > 0): ?>
            <div class="summary-row" style="color: #28a745;">
                <span>Discount (<?php echo $this->_tpl_vars['summary']['promo_code']; ?>
)</span>
                <span>- <?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['discount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
            </div>
            <?php endif; ?>

            <div class="summary-row">
                <span>Tax (<?php echo $this->_tpl_vars['summary']['tax_rate']; ?>
%)</span>
                <span><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['tax_amount'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
            </div>

            <div class="summary-total">
                <span>Today's charge</span>
                <span><?php echo $this->_tpl_vars['summary']['currency']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['summary']['grand_total'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</span>
            </div>

            <div class="payment-badges">
                Accepted payment methods
                <div class="badges-container">
                    <span class="badge-icon">VISA</span>
                    <span class="badge-icon">MasterCard</span>
                    <span class="badge-icon">PayPal</span>
                    <span class="badge-icon">Bank Transfer</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
<?php echo '
document.addEventListener(\'DOMContentLoaded\', function() {
    
    const step1Container = document.getElementById(\'container-step-1\');
    const step2Container = document.getElementById(\'container-step-2\');
    const billingForm = document.getElementById(\'wizdam-billing-form\');
    const finalForm = document.getElementById(\'final-payment-form\');
    const tocCheckbox = document.getElementById(\'toc-checkbox\');
    const btnToReview = document.getElementById(\'btn-to-review\');
    const btnFinalCheckout = document.getElementById(\'btn-final-checkout\');
    
    const indicatorStep1 = document.getElementById(\'indicator-step-1\');
    const indicatorStep2 = document.getElementById(\'indicator-step-2\');
    const stepperProgress = document.getElementById(\'stepper-progress\');

    // Input Elemen
    const inputCountry = document.getElementById(\'input-country\');
    const inputCity = document.getElementById(\'input-city\');
    const inputZip = document.getElementById(\'input-zip\');
    const inputStreet = document.getElementById(\'input-street\');

    // ==========================================
    // WIZDAM STATE MANAGER (Mencegah data hilang saat klik Back)
    // ==========================================
    const storageKey = \'wizdam_billing_state\';

    function saveStateToMemory() {
        const state = {
            country: inputCountry.value,
            city: inputCity.value,
            zip: inputZip.value,
            street: inputStreet.value
        };
        sessionStorage.setItem(storageKey, JSON.stringify(state));
    }

    // Auto-save setiap kali ada ketikan/perubahan
    [inputCountry, inputCity, inputZip, inputStreet].forEach(el => {
        el.addEventListener(\'input\', saveStateToMemory);
        el.addEventListener(\'change\', saveStateToMemory);
    });

    // Restore data jika ada (Menimpa default dari server)
    const savedState = sessionStorage.getItem(storageKey);
    if (savedState) {
        try {
            const state = JSON.parse(savedState);
            if (state.country) inputCountry.value = state.country;
            if (state.city) inputCity.value = state.city;
            if (state.zip) inputZip.value = state.zip;
            if (state.street) inputStreet.value = state.street;
        } catch(e) {}
    }

    // ==========================================
    // SPA TRANSITIONS
    // ==========================================
    btnToReview.addEventListener(\'click\', function() {
        if (!billingForm.checkValidity()) {
            billingForm.reportValidity();
            return;
        }

        saveStateToMemory(); // Paksa save sebelum lanjut

        const countryText = inputCountry.options[inputCountry.selectedIndex].text;

        // Render ke UI Review
        document.getElementById(\'review-address-display\').innerHTML = 
            `${inputStreet.value}<br>` + 
            `${inputCity.value}, ${inputZip.value}<br>` + 
            `${countryText}`;

        // Salin ke form hidden (The actual POST data)
        document.getElementById(\'hidden-country\').value = inputCountry.value;
        document.getElementById(\'hidden-city\').value = inputCity.value;
        document.getElementById(\'hidden-zip\').value = inputZip.value;
        document.getElementById(\'hidden-street\').value = inputStreet.value;

        // SPA Illusion
        step1Container.style.display = \'none\';
        step2Container.style.display = \'block\';
        window.scrollTo({ top: 0, behavior: \'smooth\' });

        indicatorStep1.classList.remove(\'active\');
        indicatorStep1.classList.add(\'completed\');
        indicatorStep1.querySelector(\'.step-circle\').innerHTML = \'&#10003;\';
        
        indicatorStep2.classList.add(\'active\');
        stepperProgress.style.display = \'block\';
    });

    // Transisi Mundur (Edit)
    const backToStep1 = function(e) {
        if(e) e.preventDefault();
        step2Container.style.display = \'none\';
        step1Container.style.display = \'block\';
        window.scrollTo({ top: 0, behavior: \'smooth\' });

        indicatorStep2.classList.remove(\'active\');
        indicatorStep1.classList.remove(\'completed\');
        indicatorStep1.classList.add(\'active\');
        indicatorStep1.querySelector(\'.step-circle\').innerHTML = \'&#10003;\';
        stepperProgress.style.display = \'none\';
    };

    document.getElementById(\'btn-back\').addEventListener(\'click\', backToStep1);
    document.getElementById(\'btn-edit-address\').addEventListener(\'click\', backToStep1);

    // ==========================================
    // KEAMANAN FORM & CSRF
    // ==========================================
    tocCheckbox.addEventListener(\'change\', function() {
        btnFinalCheckout.disabled = !this.checked;
    });

    finalForm.addEventListener(\'submit\', function() {
        btnFinalCheckout.innerHTML = \'Processing...\';
        btnFinalCheckout.disabled = true;
        // Setelah sukses submit, bersihkan memory agar checkout naskah lain tidak tercampur
        sessionStorage.removeItem(storageKey);
    });
});
'; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>