<?php /* Smarty version 2.6.26, created on 2026-04-04 21:26:24
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/settings.tpl', 19, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/settings.tpl', 23, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/settings.tpl', 32, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/settings.tpl', 34, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.common.settings"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<div id="dataciteSettings">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<br />
	<br />

	<div id="description"><b><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.datacite.settings.form.description"), $this);?>
</b></div>

	<br />

	<form method="post" action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
">
		<table width="100%" class="data">
			<tr valign="top">
				<td colspan="2">
					<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.datacite.intro"), $this);?>
</span>
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'username','key' => "plugins.importexport.datacite.settings.form.username"), $this);?>
</td>
				<td width="80%" class="value">
					<input type="text" name="username" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="50" id="username" class="textField" />
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'password','key' => "plugins.importexport.common.settings.form.password"), $this);?>
</td>
				<td width="80%" class="value">
					<input type="password" name="password" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['password'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="50" id="password" class="textField" />
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'automaticRegistration','key' => "plugins.importexport.datacite.settings.form.automaticRegistration"), $this);?>
</td>
				<td width="80%" class="value">
					<input type="checkbox" name="automaticRegistration" id="automaticRegistration" value="1" <?php if ($this->_tpl_vars['automaticRegistration']): ?> checked="checked"<?php endif; ?> />&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.datacite.settings.form.automaticRegistration.description",'unregisteredURL' => $this->_tpl_vars['unregisteredURL']), $this);?>

				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
		</table>

		<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

		<p>
			<input type="submit" name="save" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
"/>
			&nbsp;
			<input type="button" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" onclick="history.go(-1)"/>
		</p>
	</form>

</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>