<?php /* Smarty version 2.6.26, created on 2026-04-04 14:21:57
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDView.tpl', 13, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDView.tpl', 17, false),array('modifier', 'default', 'file:/home/sangiaor/public_html/journals/plugins/generic/openAIRE/projectIDView.tpl', 17, false),)), $this); ?>
<!-- OpenAIRE -->
<div id="openAIRE">
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.generic.openAIRE.metadata"), $this);?>
</h4>
<table width="100%" class="data">
	<tr valign="top">
		<td rowspan="2" width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.generic.openAIRE.projectID"), $this);?>
</td>
		<td width="80%" class="value"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['submission']->getData('projectID'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, "&mdash;") : smarty_modifier_default($_tmp, "&mdash;")); ?>
</td>
	</tr>
</table>
</div>
<!-- /OpenAIRE -->
