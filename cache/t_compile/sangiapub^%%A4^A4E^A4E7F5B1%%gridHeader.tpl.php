<?php /* Smarty version 2.6.26, created on 2026-04-16 16:17:00
         compiled from controllers/grid/gridHeader.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'controllers/grid/gridHeader.tpl', 14, false),array('function', 'translate', 'controllers/grid/gridHeader.tpl', 22, false),)), $this); ?>
<div class="grid_header">
	<?php echo $this->_tpl_vars['gridFilterForm']; ?>

	<?php if ($this->_tpl_vars['grid']->getActions(@GRID_ACTION_POSITION_ABOVE) || $this->_tpl_vars['grid']->getTitle()): ?>
		<div class="grid_header_bar">
			<?php if ($this->_tpl_vars['grid']->getTitle()): ?><h3><?php echo ((is_array($_tmp=$this->_tpl_vars['grid']->getTitle())) ? $this->_run_mod_handler('translate', true, $_tmp) : AppLocale::translate($_tmp)); ?>
</h3><?php endif; ?>
			<?php if ($this->_tpl_vars['grid']->getActions(@GRID_ACTION_POSITION_ABOVE)): ?>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "controllers/grid/gridActionsAbove.tpl", 'smarty_include_vars' => array('actions' => $this->_tpl_vars['grid']->getActions(@GRID_ACTION_POSITION_ABOVE),'gridId' => $this->_tpl_vars['staticId'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<?php endif; ?>
			<div class="pkp_helpers_clear"></div>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['grid']->getInstructions()): ?>
		<p class="pkp_grid_description"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['grid']->getInstructions()), $this);?>
</p>
	<?php endif; ?>
</div>