<?php /* Smarty version 2.6.26, created on 2026-04-16 17:24:05
         compiled from notification/settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'notification/settings.tpl', 16, false),array('function', 'url', 'notification/settings.tpl', 18, false),array('function', 'fieldLabel', 'notification/settings.tpl', 34, false),array('modifier', 'escape', 'notification/settings.tpl', 33, false),array('modifier', 'in_array', 'notification/settings.tpl', 33, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "notification.settings"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.settingsDescription"), $this);?>
</div>

<form id="notificationSettings" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveSettings'), $this);?>
">

<!-- Submission events -->
<?php $_from = $this->_tpl_vars['notificationSettingCategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['notificationSettingCategory']):
?>
	<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['notificationSettingCategory']['categoryKey']), $this);?>
</h4>
	<?php $_from = $this->_tpl_vars['notificationSettingCategory']['settings']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['settingId']):
?>
		<?php $this->assign('notificationSetting', $this->_tpl_vars['notificationSettings'][$this->_tpl_vars['settingId']]); ?>
		<?php $this->assign('settingName', $this->_tpl_vars['notificationSetting']['settingName']); ?>
		<?php $this->assign('emailSettingName', $this->_tpl_vars['notificationSetting']['emailSettingName']); ?>
		<?php $this->assign('settingKey', $this->_tpl_vars['notificationSetting']['settingKey']); ?>

		<ul>
			<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['settingKey'],'title' => $this->_tpl_vars['titleVar']), $this);?>

			<ul class="plain">
				<li><span>
					<input id="<?php echo ((is_array($_tmp=$this->_tpl_vars['settingName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="checkbox" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['settingName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if (! ((is_array($_tmp=$this->_tpl_vars['settingId'])) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['blockedNotifications']) : in_array($_tmp, $this->_tpl_vars['blockedNotifications']))): ?> checked="checked"<?php endif; ?> />
					<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => ($this->_tpl_vars['settingName'])."|escape",'key' => "notification.allow"), $this);?>

				</span></li>
				<li><span>
					<input id="<?php echo ((is_array($_tmp=$this->_tpl_vars['emailSettingName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="checkbox" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['emailSettingName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if (((is_array($_tmp=$this->_tpl_vars['settingId'])) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['emailSettings']) : in_array($_tmp, $this->_tpl_vars['emailSettings']))): ?> checked="checked"<?php endif; ?> />
					<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => ($this->_tpl_vars['emailSettingName'])."|escape",'key' => "notification.email"), $this);?>

				</span></li>
			</ul>
		</ul>
	<?php endforeach; endif; unset($_from); ?>
	<br />
<?php endforeach; endif; unset($_from); ?>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.submit"), $this);?>
" class="button defaultButton" />  <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','escape' => false), $this);?>
'" /></p>

</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
