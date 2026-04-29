<?php /* Smarty version 2.6.26, created on 2026-04-06 21:41:42
         compiled from verify/invoicePublic.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'verify/invoicePublic.tpl', 30, false),array('modifier', 'date_format', 'verify/invoicePublic.tpl', 34, false),array('modifier', 'number_format', 'verify/invoicePublic.tpl', 45, false),array('function', 'url', 'verify/invoicePublic.tpl', 68, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "Document Validation - Invoice"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="wizdam-verify-container text-center py-5">
    
        <div class="verify-badge-wrapper mb-4">
        <?php if ($this->_tpl_vars['invoice']->isPaid()): ?>
            <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/themes/wizdam/images/verified-seal.png" alt="Paid and Verified" width="80">
            <h2 class="text-success mt-3">INVOICE PAID & VERIFIED</h2>
            <p class="text-muted">This invoice is authentic, registered in our system, and has been <strong>fully settled</strong>.</p>
        <?php else: ?>
            <i class="icon-clock" style="font-size: 60px; color: #ff9800;"></i>
            <h2 class="text-warning mt-3">AUTHENTIC (UNPAID)</h2>
            <p class="text-muted">This invoice is an authentic document issued by our system, but the payment is currently <strong>pending</strong>.</p>
        <?php endif; ?>
    </div>

        <div class="wizdam-card bg-light p-4" style="max-width: 600px; margin: 0 auto; text-align: left;">
        <h3 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; color: #333;">
            Invoice Summary #<?php echo $this->_tpl_vars['invoice']->getInvoiceId(); ?>

        </h3>
        
        <table style="width: 100%; line-height: 2;">
            <tr>
                <td style="width: 40%; font-weight: bold; color: #555;">Fee Type</td>
                <td>: <?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getFeeType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #555;">Date Billed</td>
                <td>: <?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getData('dateBilled'))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</td>
            </tr>
            <?php if ($this->_tpl_vars['invoice']->isPaid() && $this->_tpl_vars['invoice']->getData('datePaid')): ?>
            <tr>
                <td style="font-weight: bold; color: #555;">Date Paid</td>
                <td>: <?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getData('datePaid'))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="font-weight: bold; color: #555;">Amount Due</td>
                <td style="font-size: 1.2em;">
                    : <strong><?php echo $this->_tpl_vars['invoice']->getCurrencyCode(); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getAmount())) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</strong>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #555;">Payment Status</td>
                <td>: 
                    <?php if ($this->_tpl_vars['invoice']->isPaid()): ?>
                        <span class="wizdam-badge" style="background-color: #28a745; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 0.9em;">
                            PAID <?php if ($this->_tpl_vars['invoice']->getPaymentMethod()): ?>via <?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getPaymentMethod())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="wizdam-badge" style="background-color: #ffc107; color: #333; padding: 3px 8px; border-radius: 4px; font-size: 0.9em;">
                            UNPAID
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

                <?php if (! $this->_tpl_vars['invoice']->isPaid()): ?>
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px dashed #ccc; text-align: center;">
                <p style="font-size: 0.9em; color: #666;">Are you the author? Log in to your dashboard to complete this payment via our secure gateway.</p>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('router' => @ROUTE_PAGE,'page' => 'login'), $this);?>
" class="wizdam-btn wizdam-btn-outline mt-2">Go to Login</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="verify-footer mt-4">
        <p><small>Secured by <strong>Wizdam Frontedge Verification System</strong></small></p>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>