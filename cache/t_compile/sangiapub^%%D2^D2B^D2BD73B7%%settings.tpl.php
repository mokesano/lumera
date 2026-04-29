<?php /* Smarty version 2.6.26, created on 2026-04-04 21:38:39
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 16, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 20, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 21, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 53, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 21, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 24, false),array('modifier', 'default', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/settings.tpl', 55, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.common.settings"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<div id="crossrefSettings">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.registrationIntro"), $this);?>
</p>
	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.requirements"), $this);?>
</h3>
	<br />

	<?php ob_start(); ?><?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('settingsUrl', ob_get_contents());ob_end_clean(); ?>
	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'setup','path' => '1','anchor' => 'setupPublisher'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'publisherUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'publisherUrl'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'plugin','path' => 'pubIds'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'doiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'doiUrl'));?>


	<?php if (! empty ( $this->_tpl_vars['configurationErrors'] ) || ! ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>
	<ul>
		<?php $_from = $this->_tpl_vars['configurationErrors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['configurationError']):
?>
			<?php if ($this->_tpl_vars['configurationError'] == @DOI_EXPORT_CONFIGERROR_DOIPREFIX): ?>
				<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.error.DOIsNotAvailable",'doiUrl' => $this->_tpl_vars['doiUrl']), $this);?>
</li>
			<?php elseif ($this->_tpl_vars['configurationError'] == @DOI_EXPORT_CONFIGERROR_SETTINGS): ?>
				<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.error.pluginNotConfigured",'settingsUrl' => $this->_tpl_vars['settingsUrl']), $this);?>
</li>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
		<?php if (! ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>
			<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.error.publisherNotConfigured",'publisherUrl' => $this->_tpl_vars['publisherUrl']), $this);?>
</li>
		<?php endif; ?>

	</ul>
	<?php else: ?>
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.requirements.satisfied"), $this);?>

	<?php endif; ?>

	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.settings"), $this);?>
</h3>
	<br />
	<form method="post" action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
">
		<table width="100%" class="data">
			<tr valign="top">
				<td colspan="2">
					<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.settings.depositorIntro"), $this);?>
</span>
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'depositorName','key' => "plugins.importexport.crossref.settings.form.depositorName",'required' => 'true'), $this);?>
</td>
				<td width="80%" class="value">
					<input type="text" name="depositorName" value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['depositorName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['currentJournal']->getSetting('supportName')) : smarty_modifier_default($_tmp, @$this->_tpl_vars['currentJournal']->getSetting('supportName'))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="60" id="depositorName" class="textField" />
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'depositorEmail','key' => "plugins.importexport.crossref.settings.form.depositorEmail",'required' => 'true'), $this);?>
</td>
				<td width="80%" class="value">
					<input type="text" name="depositorEmail" value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['depositorEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['currentJournal']->getSetting('supportEmail')) : smarty_modifier_default($_tmp, @$this->_tpl_vars['currentJournal']->getSetting('supportEmail'))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" id="depositorEmail" class="textField" />
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td colspan="2">
					<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.registrationIntro"), $this);?>
</span>
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'username','key' => "plugins.importexport.crossref.settings.form.username"), $this);?>
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
					<br /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.settings.form.password.description"), $this);?>

				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'automaticRegistration','key' => "plugins.importexport.crossref.settings.form.automaticRegistration"), $this);?>
</td>
				<td width="80%" class="value">
					<input type="checkbox" name="automaticRegistration" id="automaticRegistration" value="1" <?php if ($this->_tpl_vars['automaticRegistration']): ?> checked="checked"<?php endif; ?> />&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.settings.form.automaticRegistration.description",'unregisteredURL' => $this->_tpl_vars['unregisteredURL']), $this);?>

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