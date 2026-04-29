<?php /* Smarty version 2.6.26, created on 2026-04-04 11:07:04
         compiled from file:/home/sangiaor/public_html/journals/plugins/blocks/subscription/block.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/subscription/block.tpl', 12, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/blocks/subscription/block.tpl', 56, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/blocks/subscription/block.tpl', 20, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/blocks/subscription/block.tpl', 31, false),)), $this); ?>
<div class="block" id="sidebarSubscription">
	<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.subscription.blockTitle"), $this);?>
</span>
	<?php if ($this->_tpl_vars['individualSubscription']): ?>
		<?php $this->assign('individualSubscriptionValid', $this->_tpl_vars['individualSubscription']->isValid()); ?>
	<?php else: ?>
		<?php $this->assign('individualSubscriptionValid', false); ?>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['individualSubscription'] && $this->_tpl_vars['individualSubscriptionValid']): ?>
		<?php $this->assign('subscriptionStatus', $this->_tpl_vars['individualSubscription']->getStatus()); ?>
		<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['individualSubscription']->getSubscriptionTypeName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong>
		<?php if ($this->_tpl_vars['individualSubscription']->getMembership()): ?>(<?php echo ((is_array($_tmp=$this->_tpl_vars['individualSubscription']->getMembership())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)<?php endif; ?><br />

		<?php if ($this->_tpl_vars['journalPaymentsEnabled'] && $this->_tpl_vars['acceptSubscriptionPayments'] && $this->_tpl_vars['subscriptionStatus'] == @SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT): ?>
			<span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "subscriptions.status.awaitingOnlinePayment"), $this);?>
</span><br />
		<?php elseif ($this->_tpl_vars['journalPaymentsEnabled'] && $this->_tpl_vars['acceptSubscriptionPayments'] && $this->_tpl_vars['subscriptionStatus'] == @SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT): ?>
			<span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "subscriptions.status.awaitingOnlinePayment"), $this);?>
</span><br />
		<?php else: ?>
			<?php if ($this->_tpl_vars['individualSubscription']->isNonExpiring()): ?>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "subscriptionTypes.nonExpiring"), $this);?>
<br />
			<?php else: ?>
				<?php if ($this->_tpl_vars['individualSubscription']->isExpired()): ?><span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.subscriptions.expired"), $this);?>
: <?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.subscription.expires"), $this);?>
: <?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['individualSubscription']->getDateEnd())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
<br />
			<?php endif; ?>
		<?php endif; ?>	
	<?php elseif ($this->_tpl_vars['institutionalSubscription']): ?>
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.subscription.providedBy"), $this);?>
: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['institutionalSubscription']->getInstitutionName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><br /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.subscription.comingFromIP"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['userIP'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<br />
	<?php elseif ($this->_tpl_vars['individualSubscription'] && ! $this->_tpl_vars['individualSubscriptionValid']): ?>
		<?php $this->assign('subscriptionStatus', $this->_tpl_vars['individualSubscription']->getStatus()); ?>
		<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['individualSubscription']->getSubscriptionTypeName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong>
		<?php if ($this->_tpl_vars['individualSubscription']->getMembership()): ?>(<?php echo ((is_array($_tmp=$this->_tpl_vars['individualSubscription']->getMembership())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)<?php endif; ?><br />

		<?php if ($this->_tpl_vars['journalPaymentsEnabled'] && $this->_tpl_vars['acceptSubscriptionPayments'] && $this->_tpl_vars['subscriptionStatus'] == @SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT): ?>
			<span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "subscriptions.status.awaitingOnlinePayment"), $this);?>
</span><br />
		<?php elseif ($this->_tpl_vars['journalPaymentsEnabled'] && $this->_tpl_vars['acceptSubscriptionPayments'] && $this->_tpl_vars['subscriptionStatus'] == @SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT): ?>
			<span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "subscriptions.status.awaitingManualPayment"), $this);?>
</span><br />
		<?php else: ?>
			<?php if ($this->_tpl_vars['individualSubscription']->isNonExpiring()): ?>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "subscriptionTypes.nonExpiring"), $this);?>
<br />
			<?php else: ?>
				<?php if ($this->_tpl_vars['individualSubscription']->isExpired()): ?><span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.subscriptions.expired"), $this);?>
: <?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.subscription.expires"), $this);?>
: <?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['individualSubscription']->getDateEnd())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
<br />
			<?php endif; ?>
		<?php endif; ?>		
	<?php elseif (! $this->_tpl_vars['userLoggedIn']): ?>
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.subscription.loginToVerifySubscription"), $this);?>

	<?php endif; ?>
	<?php if ($this->_tpl_vars['userLoggedIn']): ?>
		<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'subscriptions'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.subscriptions.mySubscriptions"), $this);?>
</a>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['acceptGiftSubscriptionPayments']): ?>
		<br />
		<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gifts','op' => 'purchaseGiftSubscription'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "gifts.purchaseGiftSubscription"), $this);?>
</a>
	<?php endif; ?>
</div>