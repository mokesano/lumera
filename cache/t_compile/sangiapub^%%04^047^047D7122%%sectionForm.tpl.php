<?php /* Smarty version 2.6.26, created on 2026-04-05 00:36:56
         compiled from manager/sections/sectionForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/sections/sectionForm.tpl', 17, false),array('function', 'translate', 'manager/sections/sectionForm.tpl', 50, false),array('function', 'fieldLabel', 'manager/sections/sectionForm.tpl', 65, false),array('function', 'form_language_chooser', 'manager/sections/sectionForm.tpl', 70, false),array('function', 'html_options', 'manager/sections/sectionForm.tpl', 92, false),array('function', 'call_hook', 'manager/sections/sectionForm.tpl', 96, false),array('modifier', 'escape', 'manager/sections/sectionForm.tpl', 50, false),array('modifier', 'assign', 'manager/sections/sectionForm.tpl', 67, false),array('modifier', 'to_array', 'manager/sections/sectionForm.tpl', 172, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "section.section"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "section.section"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<form id="section" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'updateSection','path' => $this->_tpl_vars['sectionId']), $this);?>
" onsubmit="return checkEditorAssignments()">
<input type="hidden" name="editorAction" value="" />
<input type="hidden" name="userId" value="" />

<?php echo '
<script type="text/javascript">
<!--

function addSectionEditor(editorId) {
	var sectionForm = document.getElementById(\'section\');
	sectionForm.editorAction.value = "addSectionEditor";
	sectionForm.userId.value = editorId;
	sectionForm.submit();
}

function removeSectionEditor(editorId) {
	var sectionForm = document.getElementById(\'section\');
	sectionForm.editorAction.value = "removeSectionEditor";
	sectionForm.userId.value = editorId;
	sectionForm.submit();
}

function checkEditorAssignments() {
	var isOk = true;
	'; ?>

	<?php $_from = $this->_tpl_vars['assignedEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editorEntry']):
?>
	<?php $this->assign('editor', $this->_tpl_vars['editorEntry']['user']); ?>
	<?php echo '
		if (!document.getElementById(\'section\').canReview'; ?>
<?php echo $this->_tpl_vars['editor']->getId(); ?>
<?php echo '.checked && !document.getElementById(\'section\').canEdit'; ?>
<?php echo $this->_tpl_vars['editor']->getId(); ?>
<?php echo '.checked) {
			isOk = false;
		}
	'; ?>
<?php endforeach; endif; unset($_from); ?><?php echo '
	if (!isOk) {
		alert('; ?>
'<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.form.mustAllowPermission"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
'<?php echo ');
		return false;
	}
	return true;
}

// -->
</script>
'; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div id="sectionForm">
<table class="data" width="100%">
<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php if ($this->_tpl_vars['sectionId']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editSection','path' => $this->_tpl_vars['sectionId'],'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'sectionFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'sectionFormUrl'));?>

			<?php else: ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createSection','path' => $this->_tpl_vars['sectionId'],'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'sectionFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'sectionFormUrl'));?>

			<?php endif; ?>
			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'section','url' => $this->_tpl_vars['sectionFormUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
<?php endif; ?>
<tr valign="top">
	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','required' => 'true','key' => "section.title"), $this);?>
</td>
	<td width="80%" class="value"><input type="text" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" id="title" size="40" maxlength="120" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'abbrev','required' => 'true','key' => "section.abbreviation"), $this);?>
</td>
	<td class="value"><input type="text" name="abbrev[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="abbrev" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['abbrev'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="20" class="textField" />&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.abbreviation.example"), $this);?>
</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'policy','key' => "manager.sections.policy"), $this);?>
</td>
	<td class="value"><textarea name="policy[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" rows="4" cols="40" id="policy" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['policy'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'reviewFormId','key' => "submission.reviewForm"), $this);?>
</td>
	<td class="value">
		<select name="reviewFormId" size="1" id="reviewFormId" class="selectMenu">
			<option value=""><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.noneChosen"), $this);?>
</option>
			<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['reviewFormOptions'],'selected' => $this->_tpl_vars['reviewFormId']), $this);?>

		</select>
	</td>
</tr>
<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Manager::Sections::SectionForm::AdditionalMetadata",'sectionId' => $this->_tpl_vars['sectionId']), $this);?>

<tr valign="top">
	<td rowspan="4" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('suppressId' => 'true','key' => "submission.indexing"), $this);?>
</td>
	<td class="value">
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.section.submissionsToThisSection"), $this);?>
<br/>
		<input type="checkbox" name="metaReviewed" id="metaReviewed" value="1" <?php if ($this->_tpl_vars['metaReviewed']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaReviewed','key' => "manager.sections.submissionReview"), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="value">
		<input type="checkbox" name="abstractsNotRequired" id="abstractsNotRequired" value="1" <?php if ($this->_tpl_vars['abstractsNotRequired']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'abstractsNotRequired','key' => "manager.sections.abstractsNotRequired"), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="value">
		<input type="checkbox" name="metaIndexed" id="metaIndexed" value="1" <?php if ($this->_tpl_vars['metaIndexed']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaIndexed','key' => "manager.sections.submissionIndexing"), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="value">
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'identifyType','key' => "manager.sections.identifyType"), $this);?>
 <input type="text" name="identifyType[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="identifyType" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['identifyType'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="60" class="textField" />
		<br />
		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.identifyTypeExamples"), $this);?>
</span>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('suppressId' => 'true','key' => "submission.restrictions"), $this);?>
</td>
	<td class="value">
		<input type="checkbox" name="editorRestriction" id="editorRestriction" value="1" <?php if ($this->_tpl_vars['editorRestriction']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'editorRestriction','key' => "manager.sections.editorRestriction"), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('key' => "manager.sections.wordCount"), $this);?>
</td>
	<td class="value">
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'wordCount','key' => "manager.sections.wordCountInstructions"), $this);?>
&nbsp;&nbsp;<input type="text" name="wordCount" id="abbrev" value="<?php echo $this->_tpl_vars['wordCount']; ?>
" size="10" maxlength="20" class="textField" />
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'hideTitle','key' => "issue.toc"), $this);?>
</td>
	<td class="value">
		<input type="checkbox" name="hideTitle" id="hideTitle" value="1" <?php if ($this->_tpl_vars['hideTitle']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'hideTitle','key' => "manager.sections.hideTocTitle"), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="label">&nbsp;</td>
	<td class="value">
		<input type="checkbox" name="hideAuthor" id="hideAuthor" value="1" <?php if ($this->_tpl_vars['hideAuthor']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'hideAuthor','key' => "manager.sections.hideTocAuthor"), $this);?>

	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'hideAbout','key' => "navigation.about"), $this);?>
</td>
	<td class="value">
		<input type="checkbox" name="hideAbout" id="hideAbout" value="1" <?php if ($this->_tpl_vars['hideAbout']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'hideAbout','key' => "manager.sections.hideAbout"), $this);?>

	</td>
</tr>
<?php if ($this->_tpl_vars['commentsEnabled']): ?>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'disableComments','key' => "comments.readerComments"), $this);?>
</td>
	<td class="value">
		<input type="checkbox" name="disableComments" id="disableComments" value="1" <?php if ($this->_tpl_vars['disableComments']): ?>checked="checked"<?php endif; ?> />
		<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'disableComments','key' => "manager.sections.disableComments"), $this);?>

	</td>
</tr>
<?php endif; ?>
</table>
</div>
<div class="separator"></div>
<div id="sectionEditors">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</h3>
<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'people','path' => ((is_array($_tmp='sectionEditors')) ? $this->_run_mod_handler('to_array', true, $_tmp) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp))), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'sectionEditorsUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'sectionEditorsUrl'));?>

<p><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.section.sectionEditorInstructions"), $this);?>
</span></p>
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.unassigned"), $this);?>
</h4>

<table width="100%" class="listing" id="unassignedSectionEditors">
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="bottom" class="heading">
		<td width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
</td>
		<td width="60%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.name"), $this);?>
</td>
		<td width="20%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<?php $_from = $this->_tpl_vars['unassignedEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editor']):
?>
		<tr valign="top">
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
			<td align="right">
				<a class="action" href="javascript:addSectionEditor(<?php echo $this->_tpl_vars['editor']->getId(); ?>
)"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.add"), $this);?>
</a>
			</td>
		</tr>
	<?php endforeach; else: ?>
		<tr>
			<td colspan="3" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
		</tr>
	<?php endif; unset($_from); ?>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
</table>
</div>
<div id="sectionsAssigned">
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.assigned"), $this);?>
</h4>

<table width="100%" class="listing" id="assignedSectionEditors">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="bottom" class="heading">
		<td width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
</td>
		<td width="50%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.name"), $this);?>
</td>
		<td width="10%" align="center"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.review"), $this);?>
</td>
		<td width="10%" align="center"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editing"), $this);?>
</td>
		<td width="10%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<?php $_from = $this->_tpl_vars['assignedEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editorEntry']):
?>
		<?php $this->assign('editor', $this->_tpl_vars['editorEntry']['user']); ?>
		<input type="hidden" name="assignedEditorIds[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
		<tr valign="top">
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
			<td align="center"><input type="checkbox" <?php if ($this->_tpl_vars['editorEntry']['canReview']): ?>checked="checked"<?php endif; ?> name="canReview<?php echo $this->_tpl_vars['editor']->getId(); ?>
" /></td>
			<td align="center"><input type="checkbox" <?php if ($this->_tpl_vars['editorEntry']['canEdit']): ?>checked="checked"<?php endif; ?> name="canEdit<?php echo $this->_tpl_vars['editor']->getId(); ?>
" /></td>
			<td align="right">
				<a class="action" href="javascript:removeSectionEditor(<?php echo $this->_tpl_vars['editor']->getId(); ?>
)"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.remove"), $this);?>
</a>
			</td>
		</tr>
	<?php endforeach; else: ?>
		<tr>
			<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
		</tr>
	<?php endif; unset($_from); ?>
	<tr>
		<td colspan="5" class="endseparator">&nbsp;</td>
	</tr>
</table>
</div>
<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'sections','escape' => false), $this);?>
'" /></p>

</form>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
