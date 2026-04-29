<?php /* Smarty version 2.6.26, created on 2026-04-05 06:01:24
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/pln//templates/setup.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/generic/pln//templates/setup.tpl', 11, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/pln//templates/setup.tpl', 12, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/generic/pln//templates/setup.tpl', 11, false),)), $this); ?>
<?php echo ((is_array($_tmp=$this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('page' => 'manager','op' => 'plugin','path' => 'settings'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'plnPluginURL') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'plnPluginURL'));?>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.generic.pln.manager.setup.description",'plnPluginURL' => $this->_tpl_vars['plnPluginURL']), $this);?>
</p>