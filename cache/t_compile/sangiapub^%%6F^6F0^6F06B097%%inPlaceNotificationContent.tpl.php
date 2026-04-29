<?php /* Smarty version 2.6.26, created on 2026-04-05 20:26:00
         compiled from controllers/notification/inPlaceNotificationContent.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'controllers/notification/inPlaceNotificationContent.tpl', 11, false),)), $this); ?>

<div id="pkp_notification_<?php echo ((is_array($_tmp=$this->_tpl_vars['notificationId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="notification_block <?php echo $this->_tpl_vars['notificationStyleClass']; ?>
">
	<h4><?php echo $this->_tpl_vars['notificationTitle']; ?>
:</h4>
	<span class="description">
		<?php if ($this->_tpl_vars['notificationContents']): ?>
			<p><?php echo $this->_tpl_vars['notificationContents']; ?>
</p>
		<?php endif; ?>
	</span>
</div>