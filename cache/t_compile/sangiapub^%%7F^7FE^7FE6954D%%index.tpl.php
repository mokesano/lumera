<?php /* Smarty version 2.6.26, created on 2026-04-04 21:18:00
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl', 17, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl', 20, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl', 24, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl', 20, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl', 21, false),array('modifier', 'concat', 'file:/home/sangiaor/public_html/journals/plugins/importexport/duracloud/index.tpl', 28, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.duracloud.displayName"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<br />

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.duracloud.configuration"), $this);?>
</h3>

<?php if ($this->_tpl_vars['isConfigured']): ?>	<?php echo ((is_array($_tmp=$this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'signOut'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'duracloudLogoutUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'duracloudLogoutUrl'));?>

	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.duracloud.configuration.configured.description",'url' => $this->_tpl_vars['duracloudUrl'],'escapedUrl' => ((is_array($_tmp=$this->_tpl_vars['duracloudUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'username' => $this->_tpl_vars['duracloudUsername'],'logoutUrl' => $this->_tpl_vars['duracloudLogoutUrl']), $this);?>
</p>

	<form action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'selectSpace'), $this);?>
" method="post">
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'duracloudSpace','key' => "plugins.importexport.duracloud.configuration.space"), $this);?>
&nbsp;&nbsp;
		<select name="duracloudSpace" id="duracloudSpace" class="selectMenu">
				<option disabled="disabled" <?php if ($this->_tpl_vars['duracloudSpace'] == ""): ?>selected="selected" <?php endif; ?>/>
			<?php $_from = $this->_tpl_vars['spaces']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['space']):
?>
				<option <?php if (((is_array($_tmp=$this->_tpl_vars['space'])) ? $this->_run_mod_handler('concat', true, $_tmp, "") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, "")) === ((is_array($_tmp=$this->_tpl_vars['duracloudSpace'])) ? $this->_run_mod_handler('concat', true, $_tmp, "") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, ""))): ?>selected <?php endif; ?>value="<?php echo ((is_array($_tmp=$this->_tpl_vars['space'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['space'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
		</select>
		<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" />
	</form>

	<?php if (in_array ( $this->_tpl_vars['duracloudSpace'] , $this->_tpl_vars['spaces'] )): ?>		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.importExport"), $this);?>
</h3>
		<ul>
			<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'exportableIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.duracloud.export.issues"), $this);?>
</a></li>
			<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'importableIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.duracloud.import.issues"), $this);?>
</a></li>
		</ul>
	<?php endif; ?>
<?php else: ?>	<form action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'signIn'), $this);?>
" method="post">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('key' => "common.url",'name' => 'duracloudUrl','required' => true), $this);?>
</td>
				<td width="80%" class="value"><input type="text" name="duracloudUrl" id="duracloudUrl" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['duracloudUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
			</tr>
			<tr valign="top">
				<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('key' => "user.username",'name' => 'duracloudUsername','required' => true), $this);?>
</td>
				<td class="value"><input type="text" name="duracloudUsername" id="duracloudUsername" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['duracloudUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
			</tr>
			<tr valign="top">
				<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('key' => "user.password",'name' => 'duracloudPassword','required' => true), $this);?>
</td>
				<td class="value"><input type="password" name="duracloudPassword" id="duracloudPassword" value="" /></td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<input type="submit" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.login"), $this);?>
" />
				</td>
			</tr>
		</table>
	</form>
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>