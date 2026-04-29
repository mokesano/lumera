<?php /* Smarty version 2.6.26, created on 2026-04-16 16:17:00
         compiled from controllers/grid/feature/featuresOptions.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'controllers/grid/feature/featuresOptions.tpl', 16, false),)), $this); ?>
{
	<?php $_from = $this->_tpl_vars['features']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['features'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['features']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['feature']):
        $this->_foreach['features']['iteration']++;
?>
		<?php echo $this->_tpl_vars['feature']->getId(); ?>
: {
			JSClass: '<?php echo $this->_tpl_vars['feature']->getJSClass(); ?>
',
			options: {
				<?php $_from = $this->_tpl_vars['feature']->getOptions(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['featureOptions'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['featureOptions']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['optionName'] => $this->_tpl_vars['optionValue']):
        $this->_foreach['featureOptions']['iteration']++;
?>
					<?php echo $this->_tpl_vars['optionName']; ?>
: <?php if ($this->_tpl_vars['optionValue']): ?>'<?php echo ((is_array($_tmp=$this->_tpl_vars['optionValue'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
'<?php else: ?>false<?php endif; ?><?php if (! ($this->_foreach['featureOptions']['iteration'] == $this->_foreach['featureOptions']['total'])): ?>,<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
			}
		}<?php if (! ($this->_foreach['features']['iteration'] == $this->_foreach['features']['total'])): ?>,<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
}