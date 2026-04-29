<?php /* Smarty version 2.6.26, created on 2026-04-09 20:23:46
         compiled from file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 21, false),array('modifier', 'strip_unsafe_html', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 91, false),array('modifier', 'nl2br', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 91, false),array('modifier', 'string_format', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 209, false),array('modifier', 'to_array', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 216, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 39, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/paymentForm.tpl', 216, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.paymethod.manual"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-payment.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="paymentForm" class="manual-payment">
    <section class="payment-details u-mb-32" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 30px; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;">
    
        <div style="border-bottom: 3px solid #0f172a; padding-bottom: 16px; margin-bottom: 24px;display: flex;justify-content: space-between;align-items: flex-start;">
        <div class="publisher-id" style="color: #475569; font-size: 0.9rem; font-weight: 600; margin-top: 4px;">
            <h5 class="publisher-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceSiteTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h5>
            <div class="publisher-owner">Owner PT. Sangia Research Media and Publishing</div>
            <div class="legal-number">
                <span class="legal-title">Legal</span>
                <span class="legal-number">Dirjen AHU No. AHU-050003.AH.01.30.Tahun 2022.</span>
            </div>
            <div class="nib-number u-js-hide">
                <span class="nib-title">NIB Number</span>
                <span class="nib-number">1111220205313</span>
                <span class="certificate">Certificate Number</span>
                <span class="certificate-number">11112202053130002</span>
            </div>
        </div>
        <div class="invice-id" style="text-align: center;">
            <h2 style='margin: 0; color: #0f172a; font-size: 2.7rem; font-weight: 800; text-transform: uppercase;font-family: Bliss Bold,"Open Sans",Calibri,"Helvetica Neue",Arial,sans-serif;line-height: 1;'>
                INVOICE
            </h2>
            <div class="invoice-number" style="font-size: .75rem;">
                <span style="color: #64748b; padding-bottom: 4px;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.invoiceNumber"), $this);?>
:</span>
                <span style="color: #0f172a; font-weight: 700;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
            </div>
        </div>
    </div>

<?php if ($this->_tpl_vars['invoiceArticleId']): ?>
<div class="wizdam-invoice-wrapper" >

        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div style="width: 65%;">
            <div style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.billedTo"), $this);?>

            </div>
            <div style="color: #0f172a; font-weight: 700; font-size: 1rem;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceBilledName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
            
                        <?php $_from = $this->_tpl_vars['invoiceBilledAffiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['affiliation']):
?>
                <div style="color: #475569; font-size: 0.85rem; line-height: 1.4; margin-top: 2px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
            <?php endforeach; endif; unset($_from); ?>
            
            <div style="color: #0ea5e9; font-size: 0.85rem; margin-top: 4px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceBilledEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
        </div>
        
        <div style="width: 35%; text-align: right;">
            <table style="width: 100%; font-size: 0.85rem !important; text-align: right;" border="0">
                <tr>
                    <td style="color: #64748b; padding-bottom: 4px;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.invoiceCode"), $this);?>
:</td>
                    <td style="color: #0f172a; font-weight: 700;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
                </tr>
                <tr>
                    <td style="color: #64748b; padding-bottom: 4px;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.dateBilled"), $this);?>
:</td>
                    <td style="color: #0f172a; font-weight: 700;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceDateBilled'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
                </tr>
                <tr>
                    <td style="color: #ea580c; padding-bottom: 4px;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.dateDue"), $this);?>
:</td>
                    <td style="color: #ea580c; font-weight: 700;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceDateDue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="wizdam-invoice-details" style="display: flex; flex-direction: column; gap: 12px;">
        <div class="article-details">
            <div class="u-h5 u-mb-16">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.details"), $this);?>

            </div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-weight: 600; width: 30%; flex-shrink: 0;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>
</span>
                <span style="color: #0f172a; text-align: right; line-height: 1.4; font-size: 1.27em;"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['invoiceArticleTitle'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</span>
            </div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-weight: 600; width: 30%; flex-shrink: 0;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</span>
                <span style="color: #475569; text-align: right; line-height: 1.4; font-style: italic; font-size: 1.17em;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoiceAuthors'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
            </div>
        </div>
        
                <div class="details-payment">
            <div class="payment-name u-h5">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.purchase.fees"), $this);?>

            </div>
            <div style="background-color: #f8fafc; padding: 16px; border-radius: 6px; margin-top: 8px;">
            <div class="invoice-fees" style="font-size:1.2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.feeSubmission"), $this);?>
</span>
                    <span style="color: #0f172a;"><?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['feeSubmission'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                </div>
    
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.feePublication"), $this);?>
</span>
                    <span style="color: #0f172a;"><?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['feePublication'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem;padding-bottom: 8px;border-bottom: 2px dotted #cbd5e1;">
                    <span style="color: #475569;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.feeFastTrack"), $this);?>
</span>
                    <span style="color: #0f172a;"><?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['feeFastTrack'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                </div>
    
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;font-size: .9rem; color: #16a34a;">
                    <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.discount"), $this);?>
</span>
                    <span>- <?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['discount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                </div>
            </div>
                <div class="u-h5" style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 16px; border-top: 2px dashed #cbd5e1;">
                    <span style="color: #475569; font-weight: 700;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.subtotal"), $this);?>
</span>
                    <div class="sub-total u-h5" style="display: flex; justify-content: space-between;">
                        <span style="color: #0f172a; font-weight: 700;">
                            <?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['subtotal'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px;font-size: .9rem; color: #ea580c">
            <span style="color: #475569;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.vat"), $this);?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['taxRateLabel'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
%)<?php if ($this->_tpl_vars['isTaxInclusive']): ?><span> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.vatInclusive"), $this);?>
</span><?php endif; ?></span>
            <span style="<?php if ($this->_tpl_vars['isTaxInclusive']): ?>color: #16a34a<?php else: ?>color: #ea580<?php endif; ?>"><?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['tax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;padding: 0 16px;">
            <span style="color: #0f172a; font-size: 1rem; font-weight: 700;">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.total"), $this);?>

                <?php if ($this->_tpl_vars['isTaxInclusive']): ?> <span>(<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.vatInclusive"), $this);?>
)</span><?php endif; ?>
            </span>
            <span style="font-size: 1rem; font-weight: 700;">
                <?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['finalAmount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

            </span>
        </div>

    </div>
</div>
<?php endif; ?>
    </section>
    
    <?php if ($this->_tpl_vars['finalAmount']): ?>
    <section class="amount-due" style="border-top: 1px dotted;border-bottom: 1px solid;">
        <div class="payment-amount u-h5" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 26px;">
            <span class="payment-amount-fee bold" style="font-size: 1rem;text-transform: uppercase;"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.purchase.total"), $this);?>
</span>
            <span class="payment-amount-count bold" style="color: #16a34a;font-size: 1.27rem; font-weight: 800;"><?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['finalAmount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        </div>
    </section>
    <div class="vat-notes" style="font-size:.6rem;color:#0f172a;padding:4px;">
        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.invoice.vatNotes"), $this);?>

    </div>
    <?php endif; ?>
        
    <section class="payment-notes u-mb-48">
        <h4 class="payment-name u-h4 u-mt-32 u-mb-24"></h4>
        <div class="payment-notes u-mb-24" style="border: 2px dotted #ffc439;padding: 24px;border-radius: 17px;background-color: #f8fafc;">
            <?php if ($this->_tpl_vars['itemDescription']): ?>
            <div class="manual-payment">
                <div class="payment-name u-h5 u-mb-16">
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['itemName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.notes"), $this);?>

                </div>
                <div class="payment-description">
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['itemDescription'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                </div>
            </div>
            <?php endif; ?>
            <div class="manual-payment-instruction  u-mt-32">
                <div class="payment-name u-h5 u-mb-16">
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.purchase.instructions"), $this);?>

                </div>
                <div class="payment-instruction">
                    <p><?php echo ((is_array($_tmp=$this->_tpl_vars['manualInstructions'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="payment-call">
        <table class="data u-js-hide" width="100%">
        	<tr>
        		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.purchase.title"), $this);?>
</td>
        		<td class="value" width="80%"><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['itemName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong></td>
        	</tr>
        	<?php if ($this->_tpl_vars['itemDescription']): ?>
        	<tr>
        		<td colspan="2"><?php echo ((is_array($_tmp=$this->_tpl_vars['itemDescription'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
        	</tr>
        	<?php endif; ?>
        	<?php if ($this->_tpl_vars['itemAmount']): ?>
        		<tr>
        			<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.purchase.fee"), $this);?>
</td>
        			<td class="value" width="80%"><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['itemAmount'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")); ?>
<?php if ($this->_tpl_vars['itemCurrencyCode']): ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['itemCurrencyCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)<?php endif; ?></strong></td>
        		</tr>
        	<?php endif; ?>
        </table>
        <p class="u-js-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['manualInstructions'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
        
        <p>
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'payment','op' => 'plugin','path' => ((is_array($_tmp=((is_array($_tmp='ManualPayment')) ? $this->_run_mod_handler('to_array', true, $_tmp, 'notify', $this->_tpl_vars['queuedPaymentId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, 'notify', $this->_tpl_vars['queuedPaymentId'])))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" class="action u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.sendNotificationOfPayment"), $this);?>
</a>
            
            <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.sendNotificationOfPayment"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'payment','op' => 'plugin','path' => ((is_array($_tmp=((is_array($_tmp='ManualPayment')) ? $this->_run_mod_handler('to_array', true, $_tmp, 'notify', $this->_tpl_vars['queuedPaymentId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, 'notify', $this->_tpl_vars['queuedPaymentId'])))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
'">
        </p>
    </section>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>