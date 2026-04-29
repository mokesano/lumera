<?php /* Smarty version 2.6.26, created on 2026-04-05 03:43:53
         compiled from manager/announcement/announcementForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/announcement/announcementForm.tpl', 24, false),array('function', 'fieldLabel', 'manager/announcement/announcementForm.tpl', 34, false),array('function', 'form_language_chooser', 'manager/announcement/announcementForm.tpl', 39, false),array('function', 'translate', 'manager/announcement/announcementForm.tpl', 40, false),array('function', 'html_select_date', 'manager/announcement/announcementForm.tpl', 78, false),array('modifier', 'escape', 'manager/announcement/announcementForm.tpl', 26, false),array('modifier', 'assign', 'manager/announcement/announcementForm.tpl', 36, false),array('block', 'iterate', 'manager/announcement/announcementForm.tpl', 50, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageCrumbTitle', ($this->_tpl_vars['announcementTitle'])); ?><?php echo ''; ?><?php if ($this->_tpl_vars['announcementId']): ?><?php echo ''; ?><?php $this->assign('pageTitle', "manager.announcements.edit"); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('pageTitle', "manager.announcements.create"); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $this->assign('pageId', "manager.announcement.announcementForm"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-EDITOR017.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<br />
<div id="announcement">
<form id="announcementForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'updateAnnouncement'), $this);?>
">
<?php if ($this->_tpl_vars['announcementId']): ?>
<input type="hidden" name="announcementId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['announcementId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<table class="data" width="100%">
<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php if ($this->_tpl_vars['typeId']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editAnnouncement','path' => $this->_tpl_vars['announcementId'],'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'announcementUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'announcementUrl'));?>

			<?php else: ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createAnnouncement','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'announcementUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'announcementUrl'));?>

			<?php endif; ?>
			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'announcementForm','url' => $this->_tpl_vars['announcementUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
<?php endif; ?>

<?php if ($this->_tpl_vars['announcementTypes']->getCount() != 0): ?>
<tr valign="top">
	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'typeId','key' => "manager.announcements.form.typeId"), $this);?>
</td>
	<td width="80%" class="value"><select name="typeId" id="typeId" class="selectMenu">
		<option value=""></option>
		<?php $this->_tag_stack[] = array('iterate', array('from' => 'announcementTypes','item' => 'announcementType')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<option value="<?php echo $this->_tpl_vars['announcementType']->getId(); ?>
"<?php if ($this->_tpl_vars['typeId'] == $this->_tpl_vars['announcementType']->getId()): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['announcementType']->getLocalizedTypeName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	</select></td>
</tr>
<?php endif; ?>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','required' => 'true','key' => "manager.announcements.form.title"), $this);?>
</td>
	<td class="value"><input type="text" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" id="title" maxlength="255" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'descriptionShort','required' => 'true','key' => "manager.announcements.form.descriptionShort"), $this);?>
</td>
	<td class="value"><textarea name="descriptionShort[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="descriptionShort" cols="40" rows="6" class="textArea richContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['descriptionShort'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
		<br />
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.announcements.form.descriptionShortInstructions"), $this);?>
</span>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'description','key' => "manager.announcements.form.description"), $this);?>
</td>
	<td class="value"><textarea name="description[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="description" cols="40" rows="6" class="textArea richContent"><?php echo ((is_array($_tmp=$this->_tpl_vars['description'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
		<br />
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.announcements.form.descriptionInstructions"), $this);?>
</span>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'datePosted','key' => "manager.announcements.datePublish"), $this);?>
</td>
	<td class="value">
		<?php echo smarty_function_html_select_date(array('prefix' => 'datePosted','all_extra' => "class=\"selectMenu\"",'end_year' => ($this->_tpl_vars['yearOffsetFuture']),'year_empty' => "",'month_empty' => "",'day_empty' => "",'time' => ($this->_tpl_vars['datePosted'])), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'dateExpire','key' => "manager.announcements.form.dateExpire"), $this);?>
</td>
	<td class="value">
		<?php if ($this->_tpl_vars['dateExpire'] != null): ?>
			<?php echo smarty_function_html_select_date(array('prefix' => 'dateExpire','all_extra' => "class=\"selectMenu\"",'end_year' => ($this->_tpl_vars['yearOffsetFuture']),'year_empty' => "",'month_empty' => "",'day_empty' => "",'time' => ($this->_tpl_vars['dateExpire'])), $this);?>

		<?php else: ?>
			<?php echo smarty_function_html_select_date(array('prefix' => 'dateExpire','all_extra' => "class=\"selectMenu\"",'end_year' => ($this->_tpl_vars['yearOffsetFuture']),'year_empty' => "",'month_empty' => "",'day_empty' => "",'time' => "-00-00"), $this);?>

		<?php endif; ?>
		<input type="hidden" name="dateExpireHour" value="23" />
		<input type="hidden" name="dateExpireMinute" value="59" />
		<input type="hidden" name="dateExpireSecond" value="59" />
		<br />
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.announcements.form.dateExpireInstructions"), $this);?>
</span>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'notificationToggle','key' => "manager.announcements.form.notificationToggle"), $this);?>
</td>
		<td class="value">
		<input type="checkbox" name="notificationToggle" id="notificationToggle" value="1" <?php if ($this->_tpl_vars['notificationToggle']): ?> checked="checked"<?php endif; ?> /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.announcements.form.notificationToggleInstructions"), $this);?>

	</td>
</tr>
</table>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <?php if (! $this->_tpl_vars['announcementId']): ?><input type="submit" name="createAnother" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.announcements.form.saveAndCreateAnother"), $this);?>
" class="button" /> <?php endif; ?><input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'announcements','escape' => false), $this);?>
'" /></p>

</form>
</div>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
