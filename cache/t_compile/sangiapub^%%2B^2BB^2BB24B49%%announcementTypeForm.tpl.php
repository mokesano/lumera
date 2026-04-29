<?php /* Smarty version 2.6.26, created on 2026-04-05 03:43:46
         compiled from manager/announcement/announcementTypeForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/announcement/announcementTypeForm.tpl', 24, false),array('function', 'fieldLabel', 'manager/announcement/announcementTypeForm.tpl', 34, false),array('function', 'form_language_chooser', 'manager/announcement/announcementTypeForm.tpl', 39, false),array('function', 'translate', 'manager/announcement/announcementTypeForm.tpl', 40, false),array('modifier', 'escape', 'manager/announcement/announcementTypeForm.tpl', 26, false),array('modifier', 'assign', 'manager/announcement/announcementTypeForm.tpl', 36, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageCrumbTitle', ($this->_tpl_vars['announcementTypeTitle'])); ?><?php echo ''; ?><?php if ($this->_tpl_vars['typeId']): ?><?php echo ''; ?><?php $this->assign('pageTitle', "manager.announcementTypes.edit"); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('pageTitle', "manager.announcementTypes.create"); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $this->assign('pageId', "manager.announcementTypes.announcementTypeForm"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-EDITOR017.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<br />
<div id="announcementType">
<form id="announcementTypeForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'updateAnnouncementType'), $this);?>
">
<?php if ($this->_tpl_vars['typeId']): ?>
<input type="hidden" name="typeId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['typeId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
			<?php if ($this->_tpl_vars['typeId']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editAnnouncementType','path' => $this->_tpl_vars['typeId'],'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'announcementTypeUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'announcementTypeUrl'));?>

			<?php else: ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createAnnouncementType','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'announcementTypeUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'announcementTypeUrl'));?>

			<?php endif; ?>
			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'announcementTypeForm','url' => $this->_tpl_vars['announcementTypeUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
<?php endif; ?>
<tr valign="top">
	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'name','required' => 'true','key' => "manager.announcementTypes.form.typeName"), $this);?>
</td>
	<td width="80%" class="value"><input type="text" name="name[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['name'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" id="name" maxlength="80" class="textField" /></td>
</tr>
</table>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <?php if (! $this->_tpl_vars['typeId']): ?><input type="submit" name="createAnother" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.announcementTypes.form.saveAndCreateAnother"), $this);?>
" class="button" /> <?php endif; ?><input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'announcementTypes','escape' => false), $this);?>
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
