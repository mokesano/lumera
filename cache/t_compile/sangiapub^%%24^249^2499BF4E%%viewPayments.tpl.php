<?php /* Smarty version 2.6.26, created on 2026-04-05 05:49:15
         compiled from payments/viewPayments.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'payments/viewPayments.tpl', 17, false),array('function', 'translate', 'payments/viewPayments.tpl', 17, false),array('function', 'page_info', 'payments/viewPayments.tpl', 87, false),array('function', 'page_links', 'payments/viewPayments.tpl', 88, false),array('block', 'iterate', 'payments/viewPayments.tpl', 30, false),array('modifier', 'escape', 'payments/viewPayments.tpl', 47, false),array('modifier', 'to_array', 'payments/viewPayments.tpl', 55, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "common.payments"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<ul class="menu">
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payments'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.options"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payMethodSettings'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentMethods"), $this);?>
</a></li>
	<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewPayments'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.records"), $this);?>
</a></li>
</ul>

<table width="100%" class="listing">
	<tr valign="bottom">
		<th width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user"), $this);?>
</th>
		<th width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentType"), $this);?>
</th>
		<th width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.timestamp"), $this);?>
</th>
		<th width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.action"), $this);?>
</th>
	</tr>

	<?php $this->_tag_stack[] = array('iterate', array('from' => 'payments','item' => 'payment')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->assign('isSubscription', $this->_tpl_vars['payment']->isSubscription()); ?>
	<?php if ($this->_tpl_vars['isSubscription']): ?>
		<?php $this->assign('subscriptionId', $this->_tpl_vars['payment']->getAssocId()); ?>
		<?php if ($this->_tpl_vars['individualSubscriptionDao']->subscriptionExists($this->_tpl_vars['subscriptionId'])): ?>
			<?php $this->assign('isIndividual', true); ?>
		<?php elseif ($this->_tpl_vars['institutionalSubscriptionDao']->subscriptionExists($this->_tpl_vars['subscriptionId'])): ?>
			<?php $this->assign('isInstitutional', true); ?>
		<?php else: ?>
			<?php $this->assign('isIndividual', false); ?>
			<?php $this->assign('isInstitutional', false); ?>
		<?php endif; ?>
	<?php endif; ?>
	<tr valign="top">
		<td>
			<?php $this->assign('user', $this->_tpl_vars['userDao']->getById($this->_tpl_vars['payment']->getUserId())); ?>
			<?php if ($this->_tpl_vars['isJournalManager']): ?>
				<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'userProfile','path' => $this->_tpl_vars['payment']->getUserId()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
			<?php else: ?>
				<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

			<?php endif; ?>
		</td>
		<td>
			<?php if ($this->_tpl_vars['isSubscription']): ?>
				<?php if ($this->_tpl_vars['isIndividual']): ?>
					<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editSubscription','path' => ((is_array($_tmp='individual')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['subscriptionId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['subscriptionId']))), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
				<?php elseif ($this->_tpl_vars['isInstitutional']): ?>
					<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editSubscription','path' => ((is_array($_tmp='institutional')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['subscriptionId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['subscriptionId']))), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
				<?php else: ?>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

				<?php endif; ?>
			<?php else: ?>
				<?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

			<?php endif; ?>
		</td>
		<td class="nowrap">
		<?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getTimestamp())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

		</td>
		<td>
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewPayment','path' => $this->_tpl_vars['payment']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.details"), $this);?>
</a>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['payments']->eof()): ?>
    	<tr>
    		<td colspan="4" class="endseparator">&nbsp;</td>
    	</tr>
	<?php endif; ?>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    <?php if ($this->_tpl_vars['payments']->wasEmpty()): ?>
    	<tr>
    		<td colspan="4" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.noPayments"), $this);?>
</td>
    	</tr>
    	<tr>
    		<td colspan="4" class="endseparator">&nbsp;</td>
    	</tr>
    <?php else: ?>
    	<tr class="u-hide">
    		<td colspan="3" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['payments']), $this);?>
</td>
    		<td align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'payments','name' => 'payments','iterator' => $this->_tpl_vars['payments']), $this);?>
</td>
    	</tr>
    <?php endif; ?>
</table>
<?php if (! $this->_tpl_vars['payments']->wasEmpty()): ?>
<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['payments']), $this);?>
</div>
    </section>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'payments','name' => 'payments','iterator' => $this->_tpl_vars['payments']), $this);?>

       </div>
    </section>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>