<?php /* Smarty version 2.6.26, created on 2026-04-27 10:28:03
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/googleAnalytics/authorSubmit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleAnalytics/authorSubmit.tpl', 14, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleAnalytics/authorSubmit.tpl', 18, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleAnalytics/authorSubmit.tpl', 17, false),)), $this); ?>
<!-- Google Analytics -->
<tr valign="top">
	<td class="label">
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-gs",'key' => "plugins.generic.googleAnalytics.authorAccount"), $this);?>

	</td>
	<td class="value">
		<input type="text" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][gs]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-gs" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['gs'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" class="textField" /><br />
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.generic.googleAnalytics.authorAccount.description"), $this);?>
</span>
	</td>
</tr>
<!-- /Google Analytics -->
