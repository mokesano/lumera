<?php /* Smarty version 2.6.26, created on 2026-04-05 19:59:16
         compiled from about/insights.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'about/insights.tpl', 14, false),array('modifier', 'default', 'about/insights.tpl', 14, false),array('modifier', 'escape', 'about/insights.tpl', 20, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.journalInsight"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '<div id="journalInsight"><h3>Aim and Scope</h3><p>'; ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, '[Aim and Scope not set]') : smarty_modifier_default($_tmp, '[Aim and Scope not set]')); ?><?php echo '</p><hr><h3>Journal Information</h3><ul><li><strong>ISSN (Online):</strong> '; ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('onlineIssn'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, 'N/A') : smarty_modifier_default($_tmp, 'N/A')); ?><?php echo '</li><li><strong>ISSN (Print):</strong> '; ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('printIssn'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, 'N/A') : smarty_modifier_default($_tmp, 'N/A')); ?><?php echo '</li></ul></div>'; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>