<?php /* Smarty version 2.6.26, created on 2026-04-27 10:28:03
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDEdit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDEdit.tpl', 13, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDEdit.tpl', 16, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDEdit.tpl', 17, false),)), $this); ?>
<!-- OpenAIRE -->
<div id="openAIRE">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.generic.openAIRE.metadata"), $this);?>
</h3>
<table width="100%" class="data">
<tr valign="top">
	<td rowspan="2" width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'projectID','key' => "plugins.generic.openAIRE.projectID"), $this);?>
</td>
	<td width="80%" class="value"><input type="text" class="textField" name="projectID" id="projectID" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['projectID'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="5" maxlength="10" /></td>
</tr>
<tr valign="top">
	<td><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.generic.openAIRE.projectID.description"), $this);?>
</span></td>
</tr>
</table>
</div>
<div class="separator"></div>
<!-- /OpenAIRE -->
