<?php /* Smarty version 2.6.26, created on 2026-04-16 16:17:00
         compiled from controllers/grid/gridActionsAbove.tpl */ ?>

<span class="options">
	<?php $_from = $this->_tpl_vars['actions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['action']):
?>
		<?php if (is_a ( $this->_tpl_vars['action'] , 'LegacyLinkAction' )): ?>
			<?php if ($this->_tpl_vars['action']->getMode() == @LINK_ACTION_MODE_AJAX): ?>
				<?php $this->assign('actionActOnId', $this->_tpl_vars['action']->getActOn()); ?>
			<?php else: ?>
				<?php $this->assign('actionActOnId', $this->_tpl_vars['gridActOnId']); ?>
			<?php endif; ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "linkAction/legacyLinkAction.tpl", 'smarty_include_vars' => array('action' => $this->_tpl_vars['action'],'id' => $this->_tpl_vars['gridId'],'actOnId' => $this->_tpl_vars['actionActOnId'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php else: ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "linkAction/linkAction.tpl", 'smarty_include_vars' => array('action' => $this->_tpl_vars['action'],'contextId' => $this->_tpl_vars['gridId'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
</span>