<?php /* Smarty version 2.6.26, created on 2026-04-05 04:45:25
         compiled from payments/payMethodSettingsForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'payments/payMethodSettingsForm.tpl', 17, false),array('function', 'translate', 'payments/payMethodSettingsForm.tpl', 17, false),array('function', 'call_hook', 'payments/payMethodSettingsForm.tpl', 56, false),array('modifier', 'escape', 'payments/payMethodSettingsForm.tpl', 29, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.payment.paymentMethods"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<ul class="menu">
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payments'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.options"), $this);?>
</a></li>
	<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payMethodSettings'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentMethods"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewPayments'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.records"), $this);?>
</a></li>
</ul>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script type="text/javascript">
<!--

<?php echo '
function changePaymentMethod() {
	document.getElementById(\'paymentSettingsForm\').action="'; ?>
<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payMethodSettings','escape' => false), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript'));?>
<?php echo '";
		document.getElementById(\'paymentSettingsForm\').submit();
	}

	'; ?>

// -->
</script>

<form method="post" id="paymentSettingsForm" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'savePayMethodSettings'), $this);?>
">

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentMethod.description"), $this);?>
</p>


<table width="100%" class="data">
	<tr>
		<th><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentMethods"), $this);?>
</th>
	</tr>
	<tr valign="top">
		<td class="data" colspan="2">
			<?php $this->assign('pluginIndex', 1); ?>
			<?php $_from = $this->_tpl_vars['paymentMethodPlugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['plugin']):
?>
				&nbsp;<input type="radio" name="paymentMethodPluginName" id="paymentMethodPluginName-<?php echo ((is_array($_tmp=$this->_tpl_vars['pluginIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['plugin']->getName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" onclick="changePaymentMethod();" <?php if ($this->_tpl_vars['paymentMethodPluginName'] == $this->_tpl_vars['plugin']->getName()): ?>checked="checked" <?php endif; ?>/>&nbsp;<label for="paymentMethodPluginName-<?php echo ((is_array($_tmp=$this->_tpl_vars['pluginIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['plugin']->getDisplayName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</label><br/>
				<p><?php echo $this->_tpl_vars['plugin']->getDescription(); ?>
</p>
				<?php $this->assign('pluginIndex', $this->_tpl_vars['pluginIndex']+1); ?>
			<?php endforeach; endif; unset($_from); ?>
		</td>
	</tr>
	<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Template::Manager::Payment::displayPaymentSettingsForm",'plugin' => $this->_tpl_vars['paymentMethodPluginName']), $this);?>

</table>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager'), $this);?>
'" /></p>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>