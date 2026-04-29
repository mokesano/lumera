<?php /* Smarty version 2.6.26, created on 2026-04-05 21:19:14
         compiled from notification/atom.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'notification/atom.tpl', 11, false),array('function', 'translate', 'notification/atom.tpl', 14, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<feed xmlns="http://www.w3.org/2005/Atom">
	<id><?php echo ((is_array($_tmp=$this->_tpl_vars['selfUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</id>
	<title><?php echo $this->_tpl_vars['siteTitle']; ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.notifications"), $this);?>
</title>

	<link rel="self" type="application/atom+xml" href="<?php echo $this->_tpl_vars['selfUrl']; ?>
" />

	<generator uri="http://pkp.sfu.ca/ojs/" version="<?php echo ((is_array($_tmp=$this->_tpl_vars['version'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['appName']), $this);?>
</generator>

	<?php echo $this->_tpl_vars['formattedNotifications']; ?>

</feed>