<?php /* Smarty version 2.6.26, created on 2026-04-04 12:26:43
         compiled from policies/generic.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'policies/generic.tpl', 12, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', ((is_array($_tmp=@$this->_tpl_vars['pageTitleTranslated'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['pageTitle']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['pageTitle']))); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-gfa.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '<div id="wizdamPolicyPage" class="policy-content-wrapper"><div class="content-body u-mt-48"><p>'; ?><?php echo $this->_tpl_vars['content']; ?><?php echo '</p></div></div>'; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 