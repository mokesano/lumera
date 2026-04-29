<?php /* Smarty version 2.6.26, created on 2026-04-09 11:41:03
         compiled from notification/rss2Content.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'notification/rss2Content.tpl', 12, false),array('function', 'url', 'notification/rss2Content.tpl', 17, false),array('modifier', 'date_format', 'notification/rss2Content.tpl', 12, false),array('modifier', 'escape', 'notification/rss2Content.tpl', 15, false),)), $this); ?>

<item>
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
	<pubDate><?php echo ((is_array($_tmp=$this->_tpl_vars['notificationDateCreated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%a, %d %b %Y %T %z") : smarty_modifier_date_format($_tmp, "%a, %d %b %Y %T %z")); ?>
</pubDate>
</item>
