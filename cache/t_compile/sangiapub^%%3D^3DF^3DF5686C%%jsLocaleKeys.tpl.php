<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/jsLocaleKeys.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'common/jsLocaleKeys.tpl', 16, false),array('modifier', 'assign', 'common/jsLocaleKeys.tpl', 16, false),array('modifier', 'replace', 'common/jsLocaleKeys.tpl', 18, false),array('modifier', 'escape', 'common/jsLocaleKeys.tpl', 18, false),)), $this); ?>

<script type="text/javascript">
	jQuery.pkp = jQuery.pkp || { };
	jQuery.pkp.locale = { };
	<?php $_from = $this->_tpl_vars['jsLocaleKeys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['keyName']):
?>
		<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['keyName']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'keyValue') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'keyValue'));?>

				jQuery.pkp.locale.<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['keyName'])) ? $this->_run_mod_handler('replace', true, $_tmp, '.', '_') : smarty_modifier_replace($_tmp, '.', '_')))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
 = <?php if (is_numeric ( $this->_tpl_vars['keyValue'] )): ?><?php echo $this->_tpl_vars['keyValue']; ?>
<?php else: ?>'<?php echo ((is_array($_tmp=$this->_tpl_vars['keyValue'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
'<?php endif; ?>;
	<?php endforeach; endif; unset($_from); ?>
</script>