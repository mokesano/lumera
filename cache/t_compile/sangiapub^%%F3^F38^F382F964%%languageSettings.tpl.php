<?php /* Smarty version 2.6.26, created on 2026-04-05 04:40:28
         compiled from manager/languageSettings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/languageSettings.tpl', 16, false),array('function', 'url', 'manager/languageSettings.tpl', 21, false),array('function', 'fieldLabel', 'manager/languageSettings.tpl', 25, false),array('function', 'html_options', 'manager/languageSettings.tpl', 28, false),array('modifier', 'escape', 'manager/languageSettings.tpl', 48, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "common.languages"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<p><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.languages.languageInstructions"), $this);?>
</span></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if (count ( $this->_tpl_vars['availableLocales'] ) > 1): ?>
<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveLanguageSettings'), $this);?>
">

<table class="data" width="100%">
<tr valign="top">
	<td width="20%" rowspan="2" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'primaryLocale','required' => 'true','key' => "locale.primary"), $this);?>
</td>
	<td width="80%" colspan="3" class="value">
		<select id="primaryLocale" name="primaryLocale" size="1" class="selectMenu">
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['availableLocales'],'selected' => $this->_tpl_vars['primaryLocale']), $this);?>

		</select>
	</td>
</tr>
<tr valign="top">
	<td colspan="3" class="value"><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.languages.primaryLocaleInstructions"), $this);?>
</span></td>
</tr>
<tr valign="top">
	<td class="label" rowspan="2"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('suppressId' => 'true','name' => 'supportedLocales','key' => "locale.supported"), $this);?>
</td>
	<td colspan="3" class="value">
		<table class="data" width="100%">
		<tr valign="top">
			<td width="20%">&nbsp;</td>
			<td align="center" width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.language.ui"), $this);?>
</td>
			<td align="center" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.language.submissions"), $this);?>
</td>
			<td align="center" width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.language.forms"), $this);?>
</td>
			<td>&nbsp;</td>
		</tr>
		<?php $_from = $this->_tpl_vars['availableLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey'] => $this->_tpl_vars['localeName']):
?>
			<tr>
				<td><?php echo ((is_array($_tmp=$this->_tpl_vars['localeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
				<td align="center"><input type="checkbox" name="supportedLocales[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['supportedLocales'] )): ?> checked="checked"<?php endif; ?>/></td>
				<td align="center"><input type="checkbox" name="supportedSubmissionLocales[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['supportedSubmissionLocales'] )): ?> checked="checked"<?php endif; ?>/></td>
				<td align="center"><input type="checkbox" name="supportedFormLocales[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['supportedFormLocales'] )): ?> checked="checked"<?php endif; ?>/></td>
				<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'reloadLocalizedDefaultSettings','localeToLoad' => $this->_tpl_vars['localeKey']), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.language.confirmDefaultSettingsOverwrite"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.language.reloadLocalizedDefaultSettings"), $this);?>
</a></td>
			</tr>
		<?php endforeach; endif; unset($_from); ?>
		</table>
	</td>
</tr>
<tr valign="top">
	<td colspan="3" class="value"><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.languages.supportedLocalesInstructions"), $this);?>
</span></td>
</tr>
</table>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager'), $this);?>
'" /></p>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

</form>

<?php else: ?>
<div class="separator"></div>
<p><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.languages.noneAvailable"), $this);?>
</span></p>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
