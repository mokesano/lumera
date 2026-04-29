<?php /* Smarty version 2.6.26, created on 2026-04-11 04:50:10
         compiled from common/message.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'common/message.tpl', 16, false),array('modifier', 'assign', 'common/message.tpl', 16, false),)), $this); ?>
<?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if ($this->_tpl_vars['message']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'messageTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'messageTranslated'));?>
<?php endif; ?>

<p><?php echo $this->_tpl_vars['messageTranslated']; ?>
</p>

<?php if ($this->_tpl_vars['backLink']): ?>
	<ul>
		<li><a href="<?php echo $this->_tpl_vars['backLink']; ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ($this->_tpl_vars['backLinkLabel'])), $this);?>
</a></p></li>
	</ul>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>