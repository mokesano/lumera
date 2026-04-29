<?php /* Smarty version 2.6.26, created on 2026-04-05 21:19:14
         compiled from notification/atomContent.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'notification/atomContent.tpl', 14, false),array('function', 'url', 'notification/atomContent.tpl', 18, false),array('modifier', 'date_format', 'notification/atomContent.tpl', 14, false),array('modifier', 'escape', 'notification/atomContent.tpl', 16, false),array('modifier', 'regex_replace', 'notification/atomContent.tpl', 25, false),)), $this); ?>

<entry>
	<id><?php echo $this->_tpl_vars['notificationId']; ?>
</id>
	<title><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.notification"), $this);?>
 : <?php echo ((is_array($_tmp=$this->_tpl_vars['notificationDateCreated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%a, %d %b %Y %T %z") : smarty_modifier_date_format($_tmp, "%a, %d %b %Y %T %z")); ?>
</title>
	<?php if ($this->_tpl_vars['notificationUrl'] != null): ?>
		<link rel="alternate" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['notificationUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php else: ?>
		<link rel="alternate" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification'), $this);?>
" />
	<?php endif; ?>

	<summary type="html" xml:base="<?php if ($this->_tpl_vars['notificationUrl'] != null): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['notificationUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification'), $this);?>
<?php endif; ?>">
		<?php echo ((is_array($_tmp=$this->_tpl_vars['notificationContent'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>

	</summary>

	<published><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['notificationDateCreated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</published>
</entry>