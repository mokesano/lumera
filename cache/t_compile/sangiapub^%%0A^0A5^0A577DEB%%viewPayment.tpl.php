<?php /* Smarty version 2.6.26, created on 2026-04-05 05:49:41
         compiled from payments/viewPayment.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'payments/viewPayment.tpl', 17, false),array('function', 'translate', 'payments/viewPayment.tpl', 17, false),array('modifier', 'escape', 'payments/viewPayment.tpl', 40, false),array('modifier', 'number_format', 'payments/viewPayment.tpl', 58, false),array('modifier', 'strip_unsafe_html', 'payments/viewPayment.tpl', 71, false),array('modifier', 'nl2br', 'payments/viewPayment.tpl', 71, false),array('modifier', 'to_array', 'payments/viewPayment.tpl', 81, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "common.payment"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
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

<br />

<?php if ($this->_tpl_vars['payment']): ?>
	<table width="100%" class="listing">
		<tr>
			<td colspan="4" class="headseparator">&nbsp;</td>
		</tr>
		<div id="payment">
		<tr valign="top">
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentId"), $this);?>
</td>
			<td><?php echo $this->_tpl_vars['payment']->getCompletedPaymentId(); ?>
</td>
		</tr>
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
</td>
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
		</tr>
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.description"), $this);?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		</tr>
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.timestamp"), $this);?>
</td>
			<td class="nowrap">
			<?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getTimestamp())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

			</td>
		<tr>
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.amount"), $this);?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getCurrencyCode())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getAmount())) ? $this->_run_mod_handler('number_format', true, $_tmp, 2, '.', ',') : number_format($_tmp, 2, '.', ',')); ?>
</td>
		</tr>
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentMethod"), $this);?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getPayMethodPluginName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		</tr>
		<tr>
			<td colspan="2" class="separator">&nbsp;</td>
		</tr>
		<tr>
			<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.details"), $this);?>
</td>
			<td>
				<?php if ($this->_tpl_vars['payment']->getAssocDescription()): ?>
					(<?php echo ((is_array($_tmp=$this->_tpl_vars['payment']->getAssocId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
) <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['payment']->getAssocDescription())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
				<?php else: ?>
					-
				<?php endif; ?>
		</tr>
		<?php if ($this->_tpl_vars['payment']->isSubscription()): ?>
			<?php $this->assign('subscriptionId', $this->_tpl_vars['payment']->getAssocId()); ?>
			<?php if ($this->_tpl_vars['individualSubscriptionDao']->subscriptionExists($this->_tpl_vars['subscriptionId'])): ?>
				<tr>
					<td colspan="2">
						<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editSubscription','path' => ((is_array($_tmp='individual')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['subscriptionId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['subscriptionId']))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.editSubscription"), $this);?>
</a>
					</td>
				</tr>
			<?php elseif ($this->_tpl_vars['institutionalSubscriptionDao']->subscriptionExists($this->_tpl_vars['subscriptionId'])): ?>
				<tr>
					<td colspan="2">
						<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editSubscription','path' => ((is_array($_tmp='institutional')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['subscriptionId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['subscriptionId']))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.editSubscription"), $this);?>
</a>
					</td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>
		<tr>
			<td colspan="2" class="endseparator">&nbsp;</td>
		</tr>
		</div>
	</table>
<?php else: ?>
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentId"), $this);?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.notFound"), $this);?>

<?php endif; ?>
<p><input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.close"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'viewPayments','escape' => false), $this);?>
'" /></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>