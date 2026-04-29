<?php /* Smarty version 2.6.26, created on 2026-04-05 04:41:31
         compiled from manager/groups/groupForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/groups/groupForm.tpl', 19, false),array('function', 'translate', 'manager/groups/groupForm.tpl', 19, false),array('function', 'fieldLabel', 'manager/groups/groupForm.tpl', 35, false),array('function', 'form_language_chooser', 'manager/groups/groupForm.tpl', 40, false),array('modifier', 'assign', 'manager/groups/groupForm.tpl', 37, false),array('modifier', 'escape', 'manager/groups/groupForm.tpl', 47, false),array('modifier', 'concat', 'manager/groups/groupForm.tpl', 62, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageId', "manager.groups.groupForm"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', $this->_tpl_vars['pageTitle']); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<div id="groupFormDiv">
<?php if ($this->_tpl_vars['group']): ?>
	<ul class="menu">
		<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editGroup','path' => $this->_tpl_vars['group']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.editTitle"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'groupMembership','path' => $this->_tpl_vars['group']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.membership"), $this);?>
</a></li>
	</ul>
<?php endif; ?>

<br/>

<form id="groupForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'updateGroup'), $this);?>
">
<?php if ($this->_tpl_vars['group']): ?>
	<input type="hidden" name="groupId" value="<?php echo $this->_tpl_vars['group']->getId(); ?>
"/>
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
    			<?php if ($this->_tpl_vars['group']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editGroup','path' => $this->_tpl_vars['group']->getId(),'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'groupFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'groupFormUrl'));?>

    			<?php else: ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createGroup','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'groupFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'groupFormUrl'));?>

    			<?php endif; ?>
    			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'groupForm','url' => $this->_tpl_vars['groupFormUrl']), $this);?>

    			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
    		</td>
    	</tr>
    <?php endif; ?>
    <tr valign="top">
    	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','required' => 'true','key' => "manager.groups.title"), $this);?>
</td>
    	<td width="80%" class="value"><input type="text" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="35" maxlength="80" id="title" class="textField" /></td>
    </tr>
    
    <tr valign="top">
    	<td width="20%" class="label">&nbsp;</td>
    	<td width="80%" class="value">
    		<input type="checkbox" name="publishEmail" value="1" <?php if ($this->_tpl_vars['publishEmail']): ?>checked="checked" <?php endif; ?> id="publishEmail" />&nbsp;
    		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publishEmail','key' => "manager.groups.publishEmails"), $this);?>

    	</td>
    </tr>
    <tr valign="top">
    	<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.type"), $this);?>
</td>
    	<td width="80%" class="value">
    		<?php $_from = $this->_tpl_vars['groupContextOptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['groupContextOptionValue'] => $this->_tpl_vars['groupContextOptionKey']):
?>
    			<input type="radio" name="context" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['groupContextOptionValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['context'] == $this->_tpl_vars['groupContextOptionValue']): ?>checked="checked" <?php endif; ?> id="context-<?php echo ((is_array($_tmp=$this->_tpl_vars['groupContextOptionValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />&nbsp;
    			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => ((is_array($_tmp="context-")) ? $this->_run_mod_handler('concat', true, $_tmp, $this->_tpl_vars['groupContextOptionValue']) : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, $this->_tpl_vars['groupContextOptionValue'])),'key' => $this->_tpl_vars['groupContextOptionKey']), $this);?>
<br />
    		<?php endforeach; endif; unset($_from); ?>
    	</td>
    </tr>
    </table>
    <br/>
    <p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'groups','escape' => false), $this);?>
'" /></p>
</form>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
