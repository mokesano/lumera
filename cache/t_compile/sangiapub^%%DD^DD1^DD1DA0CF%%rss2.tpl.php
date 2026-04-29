<?php /* Smarty version 2.6.26, created on 2026-04-09 11:38:17
         compiled from notification/rss2.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'notification/rss2.tpl', 11, false),array('modifier', 'replace', 'notification/rss2.tpl', 19, false),array('modifier', 'strip', 'notification/rss2.tpl', 19, false),array('function', 'translate', 'notification/rss2.tpl', 15, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<rss version="2.0">
	<channel>
				<title><?php echo $this->_tpl_vars['siteTitle']; ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.notifications"), $this);?>
</title>
		<link><?php echo ((is_array($_tmp=$this->_tpl_vars['selfUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</link>

				<language><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['locale'])) ? $this->_run_mod_handler('replace', true, $_tmp, '_', '-') : smarty_modifier_replace($_tmp, '_', '-')))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</language>
		<generator><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['appName']), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['version'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</generator>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<ttl>60</ttl>

		<?php echo $this->_tpl_vars['formattedNotifications']; ?>

	</channel>
</rss>