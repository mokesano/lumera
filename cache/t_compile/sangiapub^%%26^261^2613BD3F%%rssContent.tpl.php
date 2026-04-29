<?php /* Smarty version 2.6.26, created on 2026-04-15 13:50:06
         compiled from notification/rssContent.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'notification/rssContent.tpl', 12, false),array('function', 'translate', 'notification/rssContent.tpl', 13, false),array('modifier', 'date_format', 'notification/rssContent.tpl', 13, false),array('modifier', 'escape', 'notification/rssContent.tpl', 16, false),array('modifier', 'strip', 'notification/rssContent.tpl', 24, false),)), $this); ?>

<item rdf:about="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification'), $this);?>
">
	<title><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.notification"), $this);?>
 : <?php echo ((is_array($_tmp=$this->_tpl_vars['notificationDateCreated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%a, %d %b %Y %T %z") : smarty_modifier_date_format($_tmp, "%a, %d %b %Y %T %z")); ?>
</title>
	<link>
		<?php if ($this->_tpl_vars['notificationUrl'] != null): ?>
			<?php echo ((is_array($_tmp=$this->_tpl_vars['notificationUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

		<?php else: ?>
			<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification'), $this);?>

		<?php endif; ?>
	</link>
	<description>
		<?php echo ((is_array($_tmp=$this->_tpl_vars['notificationContent'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>

	</description>
	<dc:creator><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['siteTitle'])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</dc:creator>
	<dc:date><?php echo ((is_array($_tmp=$this->_tpl_vars['notificationDateCreated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
</dc:date>
</item>