<?php /* Smarty version 2.6.26, created on 2026-04-14 02:19:46
         compiled from billing/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'billing/index.tpl', 11, false),array('modifier', 'escape', 'billing/index.tpl', 41, false),array('modifier', 'date_format', 'billing/index.tpl', 43, false),array('modifier', 'number_format', 'billing/index.tpl', 54, false),)), $this); ?>
<?php echo ''; ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="wizdam-billing-dashboard">
    
        <ul style="list-style: none; padding: 0; display: flex; border-bottom: 1px solid #ccc; margin-bottom: 20px;">
        <li style="margin-right: 15px; border-bottom: <?php if ($this->_tpl_vars['activeTab'] == 'pending'): ?>3px solid #0056b3<?php else: ?>none<?php endif; ?>;">
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'billing','op' => 'index'), $this);?>
" style="text-decoration: none; color: <?php if ($this->_tpl_vars['activeTab'] == 'pending'): ?>#0056b3<?php else: ?>#666<?php endif; ?>; font-weight: bold; padding: 5px 10px; display: block;">Active Invoices</a>
        </li>
        <li style="margin-right: 15px; border-bottom: <?php if ($this->_tpl_vars['activeTab'] == 'history'): ?>3px solid #0056b3<?php else: ?>none<?php endif; ?>;">
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'billing','op' => 'history'), $this);?>
" style="text-decoration: none; color: <?php if ($this->_tpl_vars['activeTab'] == 'history'): ?>#0056b3<?php else: ?>#666<?php endif; ?>; font-weight: bold; padding: 5px 10px; display: block;">History</a>
        </li>
    </ul>

    <div class="wizdam-card">
        <table class="wizdam-table" width="100%" style="border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #ccc;">
                    <th style="padding: 10px;">Invoice ID</th>
                    <th style="padding: 10px;">Fee Type</th>
                    <th style="padding: 10px;">Date Billed</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Amount</th>
                    <th style="padding: 10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $_from = $this->_tpl_vars['invoices']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['invoice']):
?>
                    
                                        <?php $this->assign('invId', $this->_tpl_vars['invoice']->getInvoiceId()); ?>
                    <?php $this->assign('secureHash', $this->_tpl_vars['hashService']->generateHash('invoice',$this->_tpl_vars['invId'])); ?>
                    <?php $this->assign('securePath', ($this->_tpl_vars['secureHash'])."-".($this->_tpl_vars['invId'])); ?>

                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">#<?php echo $this->_tpl_vars['invId']; ?>
</td>
                        
                        <td style="padding: 10px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getData('feeType'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
                        
                        <td style="padding: 10px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getData('dateBilled'))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</td>
                        
                        <td style="padding: 10px;">
                                                        <?php if ($this->_tpl_vars['invoice']->getData('status') === 'PAID' || $this->_tpl_vars['invoice']->getData('datePaid') != ''): ?>
                                <span class="wizdam-badge badge-success" style="color: #28a745; font-weight: bold;">PAID</span>
                            <?php else: ?>
                                <span class="wizdam-badge badge-warning" style="color: #dc3545; font-weight: bold;">UNPAID</span>
                            <?php endif; ?>
                        </td>
                        
                        <td style="padding: 10px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getData('currencyCode'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['invoice']->getData('amount'))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</td>
                        
                        <td style="padding: 10px;">
                            <?php if ($this->_tpl_vars['invoice']->getData('status') !== 'PAID'): ?>
                                                        <a class="cancel-action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'billing','op' => 'cancel','path' => $this->_tpl_vars['securePath']), $this);?>
" 
                               style="text-decoration: none; background: #d54449; color: white; padding: 5px 10px; border-radius: 4px; font-size: 14px; margin-right: 5px;">
                                Cancel Bill
                            </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'billing','op' => 'invoice','path' => $this->_tpl_vars['securePath']), $this);?>
" 
                               class="wizdam-btn wizdam-btn-primary" 
                               style="text-decoration: none; background: #0056b3; color: white; padding: 5px 10px; border-radius: 4px; font-size: 14px;">
                               View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px; text-align: center; color: #666;">
                            You have no active invoices.
                        </td>
                    </tr>
                <?php endif; unset($_from); ?>
            </tbody>
        </table>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>