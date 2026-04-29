<?php /* Smarty version 2.6.26, created on 2026-04-05 06:00:20
         compiled from file:/home/sangiaor/public_html/journals/plugins/themes/custom/settingsForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/themes/custom/settingsForm.tpl', 17, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/themes/custom/settingsForm.tpl', 23, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/themes/custom/settingsForm.tpl', 28, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/themes/custom/settingsForm.tpl', 30, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.theme.custom.name"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="customThemeSettings">
<div id="description"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.description"), $this);?>
</div>

<div class="separator"></div>

<br />

<form method="post" action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'customThemeHeaderColour','required' => 'true','key' => "plugins.theme.custom.header"), $this);?>
</td>
		<td width="80%" class="value">
			<input name="customThemeHeaderColour" type="text" id="customThemeHeaderColour" size="7" maxlength="7" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeHeaderColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['customThemeHeaderColour']): ?>style="background-color: <?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeHeaderColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
;" <?php endif; ?>/>
			<span onclick="openPicker('customThemeHeaderColour')" class="picker_buttons"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.pickColour"), $this);?>
</span>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'customThemeLinkColour','required' => 'true','key' => "plugins.theme.custom.link"), $this);?>
</td>
		<td class="value">
			<input name="customThemeLinkColour" type="text" id="customThemeLinkColour" size="7" maxlength="7" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeLinkColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['customThemeLinkColour']): ?>style="background-color: <?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeLinkColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
;" <?php endif; ?>/>
			<span onclick="openPicker('customThemeLinkColour')" class="picker_buttons"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.pickColour"), $this);?>
</span>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'customThemeBackgroundColour','required' => 'true','key' => "plugins.theme.custom.background"), $this);?>
</td>
		<td class="value">
			<input name="customThemeBackgroundColour" type="text" id="customThemeBackgroundColour" size="7" maxlength="7" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeBackgroundColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['customThemeBackgroundColour']): ?>style="background-color: <?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeBackgroundColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
;" <?php endif; ?>/>
			<span onclick="openPicker('customThemeBackgroundColour')" class="picker_buttons"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.pickColour"), $this);?>
</span>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'customThemeForegroundColour','required' => 'true','key' => "plugins.theme.custom.foreground"), $this);?>
</td>
		<td class="value">
			<input name="customThemeForegroundColour" type="text" id="customThemeForegroundColour" size="7" maxlength="7" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeForegroundColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['customThemeForegroundColour']): ?>style="background-color: <?php echo ((is_array($_tmp=$this->_tpl_vars['customThemeForegroundColour'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
;" <?php endif; ?>/>
			<span onclick="openPicker('customThemeForegroundColour')" class="picker_buttons"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.pickColour"), $this);?>
</span>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'customThemePerJournal','key' => "plugins.theme.custom.perJournal"), $this);?>
</td>
		<td class="value">
			<input name="customThemePerJournal" type="checkbox" id="customThemePerJournal" value="on" <?php if (( $this->_tpl_vars['customThemePerJournal'] || $this->_tpl_vars['disablePluginPath'] )): ?>checked="checked" <?php endif; ?><?php if ($this->_tpl_vars['disablePluginPath']): ?>disabled="disabled" <?php endif; ?>/>
			<?php if ($this->_tpl_vars['disablePluginPath']): ?><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.notWritablePlugin",'stylesheetFileLocation' => $this->_tpl_vars['stylesheetFilePluginLocation']), $this);?>
</span><?php endif; ?>
		</td>
	</tr>
</table>

<br />

<?php if (! $this->_tpl_vars['canSave']): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.theme.custom.notWritable",'stylesheetFileLocation' => $this->_tpl_vars['stylesheetFileLocation']), $this);?>
<br /><?php endif; ?>

<input type="submit" <?php if (! $this->_tpl_vars['canSave']): ?>disabled="disabled" <?php endif; ?>name="save" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
"/><input type="button" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" onclick="history.go(-1)"/>
</form>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>