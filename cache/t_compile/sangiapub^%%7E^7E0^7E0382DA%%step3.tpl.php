<?php /* Smarty version 2.6.26, created on 2026-04-14 13:10:29
         compiled from manager/setup/step3.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/setup/step3.tpl', 13, false),array('function', 'fieldLabel', 'manager/setup/step3.tpl', 21, false),array('function', 'form_language_chooser', 'manager/setup/step3.tpl', 24, false),array('function', 'translate', 'manager/setup/step3.tpl', 25, false),array('function', 'load_url_in_div', 'manager/setup/step3.tpl', 361, false),array('function', 'fbvElement', 'manager/setup/step3.tpl', 368, false),array('modifier', 'escape', 'manager/setup/step3.tpl', 15, false),array('modifier', 'assign', 'manager/setup/step3.tpl', 23, false),array('modifier', 'translate', 'manager/setup/step3.tpl', 133, false),)), $this); ?>
<?php $this->assign('pageTitle', "manager.setup.guidingSubmissions"); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "manager/setup/setupHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form name="setupForm" id="setupForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','path' => '3'), $this);?>
">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">

<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
<div id="locale" class="block">
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','path' => '3','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'setupFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'setupFormUrl'));?>

			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'setupForm','url' => $this->_tpl_vars['setupFormUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>
<?php endif; ?>

<div id="authorGuidelinesInfo" class="block">
<h3>3.1 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.authorGuidelines"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.authorGuidelinesDescription"), $this);?>
</p>

<p>
	<textarea name="authorGuidelines[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="authorGuidelines" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorGuidelines'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
</p>

</div>

<div id="submissionPreparationChecklist" class="block">
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.submissionPreparationChecklist"), $this);?>
</h4>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.submissionPreparationChecklistDescription"), $this);?>
</p>

<?php $_from = $this->_tpl_vars['submissionChecklist'][$this->_tpl_vars['formLocale']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['checklist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['checklist']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['checklistId'] => $this->_tpl_vars['checklistItem']):
        $this->_foreach['checklist']['iteration']++;
?>
	<?php if (! $this->_tpl_vars['notFirstChecklistItem']): ?>
		<?php $this->assign('notFirstChecklistItem', 1); ?>
		<table width="100%" class="data">
			<tr valign="top">
				<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.order"), $this);?>
</td>
				<td width="95%" colspan="2">&nbsp;</td>
			</tr>
	<?php endif; ?>

	<tr valign="top">
		<td width="5%" class="label"><input type="text" name="submissionChecklist[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][<?php echo ((is_array($_tmp=$this->_tpl_vars['checklistId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][order]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['checklistItem']['order'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="3" maxlength="2" class="textField" /></td>
		<td class="value" width="85%"><textarea name="submissionChecklist[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][<?php echo ((is_array($_tmp=$this->_tpl_vars['checklistId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][content]" id="submissionChecklist-<?php echo ((is_array($_tmp=$this->_tpl_vars['checklistId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" rows="3" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['checklistItem']['content'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
		<td width="10%"><input type="submit" name="delChecklist[<?php echo ((is_array($_tmp=$this->_tpl_vars['checklistId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" /></td>
	</tr>
<?php endforeach; endif; unset($_from); ?>

<?php if ($this->_tpl_vars['notFirstChecklistItem']): ?>
	</table>
<?php endif; ?>

<p><input type="submit" name="addChecklist" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.addChecklistItem"), $this);?>
" class="button" /></p>
</div>

<div class="separator"></div>

<div id="permissions" class="block">
<h3>3.2 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.permissions"), $this);?>
</h3>

<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.authorCopyrightNotice"), $this);?>
</h4>
<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'sampleCopyrightWording'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'sampleCopyrightWordingUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'sampleCopyrightWordingUrl'));?>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.authorCopyrightNoticeDescription",'sampleCopyrightWordingUrl' => $this->_tpl_vars['sampleCopyrightWordingUrl']), $this);?>
</p>

<p><textarea name="copyrightNotice[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="copyrightNotice" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['copyrightNotice'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label" rowspan="3">
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.copyrightHolder"), $this);?>

		</td>
		<td width="80%" class="data">
			<input type="radio" value="author" name="copyrightHolderType" <?php if ($this->_tpl_vars['copyrightHolderType'] == 'author'): ?>checked="checked" <?php endif; ?>id="copyrightHolderType-author" />&nbsp;<label for="copyrightHolderType-author"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.author"), $this);?>
</label>
		</td>
	</tr>
	<tr valign="top">
		<td class="data">
			<input type="radio" value="journal" name="copyrightHolderType" <?php if ($this->_tpl_vars['copyrightHolderType'] == 'journal'): ?>checked="checked" <?php endif; ?>id="copyrightHolderType-journal" />&nbsp;<label for="copyrightHolderType-journal"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.journal"), $this);?>
</label> (<?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)
		</td>
	</tr>
	<tr valign="top">
		<td class="data">
			<input type="radio" value="other" name="copyrightHolderType" <?php if ($this->_tpl_vars['copyrightHolderType'] == 'other'): ?>checked="checked" <?php endif; ?>id="copyrightHolderType-other" />&nbsp;<label for="copyrightHolderType-other"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.other"), $this);?>
</label>&nbsp;&nbsp;<input type="text" name="copyrightHolderOther[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="copyrightHolderOther" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['copyrightHolderOther'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
		</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label" rowspan="2">
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.copyrightYearBasis"), $this);?>

		</td>
		<td width="80%" class="data">
			<input type="radio" value="issue" name="copyrightYearBasis" <?php if ($this->_tpl_vars['copyrightYearBasis'] == 'issue'): ?>checked="checked" <?php endif; ?>id="copyrightYearBasis-issue" />&nbsp;<label for="copyrightYearBasis-issue"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</label> (<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.copyrightYearBasis.Issue"), $this);?>
)
		</td>
	</tr>
	<tr valign="top">
		<td class="data">
			<input type="radio" value="article" name="copyrightYearBasis" <?php if ($this->_tpl_vars['copyrightYearBasis'] == 'article'): ?>checked="checked" <?php endif; ?>id="copyrightYearBasis-article" />&nbsp;<label for="copyrightYearBasis-article"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.article"), $this);?>
</label> (<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.copyrightYearBasis.Article"), $this);?>
)
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.permissions.priorAgreement"), $this);?>
</td>
		<td class="label">
			<input type="checkbox" name="copyrightNoticeAgree" id="copyrightNoticeAgree" value="1"<?php if ($this->_tpl_vars['copyrightNoticeAgree']): ?> checked="checked"<?php endif; ?> />&nbsp;<label for="copyrightNoticeAgree"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.authorCopyrightNoticeAgree"), $this);?>
</label>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.permissions.display"), $this);?>
</td>
		<td class="value">
			<input type="checkbox" name="includeCopyrightStatement" id="includeCopyrightStatement" value="1"<?php if ($this->_tpl_vars['includeCopyrightStatement']): ?> checked="checked"<?php endif; ?> />&nbsp;<label for="includeCopyrightStatement"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.includeCopyrightStatement"), $this);?>
</label>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'licenseURL','key' => "submission.licenseURL"), $this);?>
</td>
		<td class="value">
			<select name="licenseURLSelect" id="licenseURLSelect" onchange="document.getElementById('licenseURL').value=document.getElementById('licenseURLSelect').options[document.getElementById('licenseURLSelect').selectedIndex].value; document.getElementById('licenseURL').readOnly=(document.getElementById('licenseURL').value==''?false:true);">
				<?php $this->assign('foundCc', 0); ?>
				<?php $_from = $this->_tpl_vars['ccLicenseOptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ccUrl'] => $this->_tpl_vars['ccNameKey']):
?>
					<option <?php if ($this->_tpl_vars['licenseURL'] == $this->_tpl_vars['ccUrl']): ?>selected="selected" <?php endif; ?>value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ccUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['ccNameKey'])) ? $this->_run_mod_handler('translate', true, $_tmp) : AppLocale::translate($_tmp)); ?>
</option>
					<?php if ($this->_tpl_vars['licenseURL'] == $this->_tpl_vars['ccUrl']): ?>
						<?php $this->assign('foundCc', 1); ?>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
				<option <?php if (! $this->_tpl_vars['foundCc']): ?>selected="selected" <?php endif; ?>value="">Other</option>
			</select>
			<br/>
			<input type="text" name="licenseURL" id="licenseURL" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['licenseURL'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['foundCc']): ?>readonly="readonly" <?php endif; ?>size="40" maxlength="255" class="textField" />
			<br/>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.licenseURLDescription"), $this);?>

		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.permissions.display"), $this);?>
</td>
		<td class="value">
			<input type="checkbox" name="includeLicense" id="includeLicense" value="1"<?php if ($this->_tpl_vars['includeLicense']): ?> checked="checked"<?php endif; ?> />&nbsp;<label for="includeLicense"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.includeLicense"), $this);?>
</label>
		</td>
	</tr>
</table>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.resetPermissions.description"), $this);?>
</p>
<p><input id="resetPermissionsButton" type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.resetPermissions"), $this);?>
" class="button" /></p>
</div>

<div class="separator"></div>

<div id="competingInterests" class="block">
<h3>3.3 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.competingInterests"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.competingInterests.description"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td class="label" width="5%">
			<input type="checkbox" name="requireAuthorCompetingInterests" id="requireAuthorCompetingInterests" value="1"<?php if ($this->_tpl_vars['requireAuthorCompetingInterests']): ?> checked="checked"<?php endif; ?> />
		</td>
		<td class="value" width="95%">
			<label for="requireAuthorCompetingInterests"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.competingInterests.requireAuthors"), $this);?>
</label>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">
			<input type="checkbox" name="requireReviewerCompetingInterests" id="requireReviewerCompetingInterests" value="1"<?php if ($this->_tpl_vars['requireReviewerCompetingInterests']): ?> checked="checked"<?php endif; ?> />
		</td>
		<td class="value">
			<label for="requireReviewerCompetingInterests"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.competingInterests.requireReviewers"), $this);?>
</label>
		</td>
	</tr>
</table>

<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.competingInterests.guidelines"), $this);?>
</h4>
<p><textarea name="competingInterestGuidelines[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="competingInterestGuidelines" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['competingInterestGuidelines'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></p>
</div>

<div class="separator"></div>

<div id="forAuthorsToIndexTheirWork" class="block">
<h3>3.4 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.forAuthorsToIndexTheirWork"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.forAuthorsToIndexTheirWorkDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="5%" class="label" valign="bottom"><input type="checkbox" name="metaDiscipline" id="metaDiscipline" value="1"<?php if ($this->_tpl_vars['metaDiscipline']): ?> checked="checked"<?php endif; ?> /></td>
		<td width="95%" class="value" >
			<h4 class="heading"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaDiscipline','key' => "manager.setup.discipline"), $this);?>
</h4>
		</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.disciplineDescription"), $this);?>
</span>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.disciplineProvideExamples"), $this);?>
:</span>
			<input type="text" name="metaDisciplineExamples[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaDisciplineExamples" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaDisciplineExamples'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.disciplineExamples"), $this);?>
</span>
		</td>
	</tr>

	<tr valign="top">
		<td width="5%" class="label" valign="bottom"><input type="checkbox" name="metaSubjectClass" id="metaSubjectClass" value="1"<?php if ($this->_tpl_vars['metaSubjectClass']): ?> checked="checked"<?php endif; ?> /></td>
		<td width="95%" class="value">
			<h4 class="heading"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaSubjectClass','key' => "manager.setup.subjectClassification"), $this);?>
</h4>
		</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<table width="100%">
				<tr valign="top">
					<td width="10%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaSubjectClassTitle','key' => "common.title"), $this);?>
</td>
					<td width="90%"><input type="text" name="metaSubjectClassTitle[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaSubjectClassTitle" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSubjectClassTitle'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" class="textField" /></td>
				</tr>
				<tr valign="top">
					<td width="10%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaSubjectClassUrl','key' => "common.url"), $this);?>
</td>
					<td width="90%"><input type="text" name="metaSubjectClassUrl[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaSubjectClassUrl" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSubjectClassUrl'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" class="textField" /></td>
				</tr>
			</table>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.subjectClassificationExamples"), $this);?>
</span>
		</td>
	</tr>

	<tr valign="top">
		<td width="5%" class="label" valign="bottom"><input type="checkbox" name="metaSubject" id="metaSubject" value="1"<?php if ($this->_tpl_vars['metaSubject']): ?> checked="checked"<?php endif; ?> /></td>
		<td width="95%" class="value">
			<h4 class="heading"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaSubject','key' => "manager.setup.subjectKeywordTopic"), $this);?>
</h4>
		</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.subjectProvideExamples"), $this);?>
:</span>
			<input type="text" name="metaSubjectExamples[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaSubjectExamples" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSubjectExamples'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.subjectExamples"), $this);?>
</span>
		</td>
	</tr>

	<tr valign="top">
		<td width="5%" class="label" valign="bottom"><input type="checkbox" name="metaCoverage" id="metaCoverage" value="1"<?php if ($this->_tpl_vars['metaCoverage']): ?> checked="checked"<?php endif; ?> /></td>
		<td width="95%" class="value">
			<h4 class="heading"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaCoverage','key' => "manager.setup.coverage"), $this);?>
</h4>
		</td>
	</tr>

	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageDescription"), $this);?>
</span>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageGeoProvideExamples"), $this);?>
:</span>
			<input type="text" name="metaCoverageGeoExamples[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaCoverageGeoExamples" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaCoverageGeoExamples'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageGeoExamples"), $this);?>
</span>
		</td>
	</tr>
	<tr>
		<td class="separator" colspan="2">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageChronProvideExamples"), $this);?>
:</span>
			<input type="text" name="metaCoverageChronExamples[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaCoverageChronExamples" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaCoverageChronExamples'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageChronExamples"), $this);?>
</span>
		</td>
	</tr>

	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageResearchSampleProvideExamples"), $this);?>
:</span>
			<input type="text" name="metaCoverageResearchSampleExamples[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaCoverageResearchSampleExamples" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaCoverageResearchSampleExamples'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.coverageResearchSampleExamples"), $this);?>
</span>
		</td>
	</tr>

	<tr valign="top">
		<td width="5%" class="label" valign="bottom"><input type="checkbox" name="metaType" id="metaType" value="1"<?php if ($this->_tpl_vars['metaType']): ?> checked="checked"<?php endif; ?> /></td>
		<td width="95%" class="value">
			<h4 class="heading"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'metaType','key' => "manager.setup.typeMethodApproach"), $this);?>
</h4>
		</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value">
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.typeProvideExamples"), $this);?>
:</span>
			<input type="text" name="metaTypeExamples[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="metaTypeExamples" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaTypeExamples'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.typeExamples"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>


<div id="registerJournalForIndexing" class="block">
<h3>3.5 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.registerJournalForIndexing"), $this);?>
</h3>

<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.registerJournalForIndexingDescription",'oaiUrl' => $this->_tpl_vars['oaiUrl'],'siteUrl' => $this->_tpl_vars['baseUrl']), $this);?>
</p>
</div>

<div class="separator"></div>

<div id="notifications" class="block">
<h3>3.6 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.notifications"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.notifications.description"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td class="label"><input <?php if (! $this->_tpl_vars['submissionAckEnabled']): ?>disabled="disabled" <?php endif; ?>type="checkbox" name="copySubmissionAckPrimaryContact" id="copySubmissionAckPrimaryContact" value="true" <?php if ($this->_tpl_vars['copySubmissionAckPrimaryContact']): ?>checked="checked"<?php endif; ?>/></td>
		<td class="value"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'copySubmissionAckPrimaryContact','key' => "manager.setup.notifications.copyPrimaryContact"), $this);?>
</td>
	</tr>
	<tr valign="top">
		<td class="label"><input <?php if (! $this->_tpl_vars['submissionAckEnabled']): ?>disabled="disabled" <?php endif; ?>type="checkbox" name="copySubmissionAckSpecified" id="copySubmissionAckSpecified" value="true" <?php if ($this->_tpl_vars['copySubmissionAckSpecified']): ?>checked="checked"<?php endif; ?>/></td>
		<td class="value"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'copySubmissionAckAddress','key' => "manager.setup.notifications.copySpecifiedAddress"), $this);?>
&nbsp;&nbsp;<input <?php if (! $this->_tpl_vars['submissionAckEnabled']): ?>disabled="disabled" <?php endif; ?>type="text" class="textField" id="copySubmissionAckAddress" name="copySubmissionAckAddress" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['copySubmissionAckAddress'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/></td>
	</tr>
	<?php if (! $this->_tpl_vars['submissionAckEnabled']): ?>
	<tr valign="top">
		<td>&nbsp;</td>
		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'emails'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'preparedEmailsUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'preparedEmailsUrl'));?>

		<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.notifications.submissionAckDisabled",'preparedEmailsUrl' => $this->_tpl_vars['preparedEmailsUrl']), $this);?>
</td>
	</tr>
	<?php endif; ?>
</table>
</div>

<div class="separator"></div>

<div id="citationAssistant" class="block">
<h3>3.7 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationAssistant"), $this);?>
</h3>

<a name="metaCitationEditing"></a>
<?php if ($this->_tpl_vars['citationEditorError']): ?>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['citationEditorError']), $this);?>
</p>
<?php else: ?>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.metaCitationsDescription"), $this);?>
</p>
	<table width="100%" class="data">
		<tr valign="top">
			<td width="5%" class="label">
				<input type="checkbox" name="metaCitations" id="metaCitations" value="1"<?php if ($this->_tpl_vars['metaCitations']): ?> checked="checked"<?php endif; ?> />
			</td>
			<td width="95%" class="value"><label for="metaCitations"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citations"), $this);?>
</label>
			</td>
		</tr>
	</table>

	<div id="citationFilterSetupToggle" <?php if (! $this->_tpl_vars['metaCitations']): ?>style="visible: none"<?php endif; ?>>
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationFilterParser"), $this);?>
</h4>
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationFilterParserDescription"), $this);?>
</p>
		<?php echo $this->_plugins['function']['load_url_in_div'][0][0]->smartyLoadUrlInDiv(array('id' => 'parserFilterGridContainer','loadMessageId' => "manager.setup.filter.parser.grid.loadMessage",'url' => ($this->_tpl_vars['parserFilterGridUrl'])), $this);?>


		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationFilterLookup"), $this);?>
</h4>
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationFilterLookupDescription"), $this);?>
</p>
		<?php echo $this->_plugins['function']['load_url_in_div'][0][0]->smartyLoadUrlInDiv(array('id' => 'lookupFilterGridContainer','loadMessageId' => "manager.setup.filter.lookup.grid.loadMessage",'url' => ($this->_tpl_vars['lookupFilterGridUrl'])), $this);?>

		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationOutput"), $this);?>
</h4>
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.citationOutputStyleDescription"), $this);?>
</p>
		<?php echo $this->_plugins['function']['fbvElement'][0][0]->smartyFBVElement(array('type' => 'select','id' => 'metaCitationOutputFilterSelect','name' => 'metaCitationOutputFilterId','from' => $this->_tpl_vars['metaCitationOutputFilters'],'translate' => false,'selected' => ((is_array($_tmp=$this->_tpl_vars['metaCitationOutputFilterId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'defaultValue' => "-1",'defaultLabel' => ((is_array($_tmp="manager.setup.filter.pleaseSelect")) ? $this->_run_mod_handler('translate', true, $_tmp) : AppLocale::translate($_tmp))), $this);?>

	</div>
<?php echo '<script type=\'text/javascript\'>
    $(function(){
        // jQuerify DOM elements
        var $metaCitationsCheckbox = $(\'#metaCitations\');
        var $metaCitationsSetupBox = $(\'#citationFilterSetupToggle\');

        // [FIX] Gunakan is(\':checked\') untuk jQuery 3.x
        // Set the initial state
        if ($metaCitationsCheckbox.is(\':checked\')) {
            $metaCitationsSetupBox.css(\'display\', \'block\');
        } else {
            $metaCitationsSetupBox.css(\'display\', \'none\');
        }

        // Toggle the settings box.
        $metaCitationsCheckbox.click(function(){
            // [FIX] Logika yang lebih aman untuk jQuery 3.x
            if ($(this).is(\':checked\')) {
                $metaCitationsSetupBox.show(300);
            } else {
                $metaCitationsSetupBox.hide(300);
            }
        });
    });
    
    function permissionsValues() {
        var perm = \':\';
        var licenseNames = ["copyrightYearBasis", "copyrightHolderType"];
        for (var l = 0; l < licenseNames.length; l++) {
            var ele = document.getElementsByName(licenseNames[l]);
            for (var i = 0; i < ele.length; i++) {
                if (ele[i].type == \'radio\') {
                    if (ele[i].checked) {
                        perm += ele[i].value + \':\';
                        break;
                    }
                } else {
                    perm += ele[i].value + \':\';
                }
            }
        };
        var licenseIds = ["copyrightHolderOther", "licenseURL"];
        for (var l = 0; l < licenseIds.length; l++) {
            var ele = document.getElementById(licenseIds[l]);
            if (ele) {
                perm += ele.value + \':\';
            }
        }
        return perm;
    }

    var resetValues;
    $(document).ready( function () {
        resetValues = permissionsValues();
        $(\'#resetPermissionsButton\').click( function ( event ) {
            if (resetValues == permissionsValues()) {
                '; ?>

                confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'resetPermissions'), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.confirmResetLicense"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
');
                <?php echo '
            } else {
                '; ?>

                window.alert('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.confirmResetLicenseChanged"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')
                <?php echo '
            }
            event.preventDefault();
        });
    });
</script>'; ?>

<?php endif; ?>
</div>

<div class="separator"></div>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.saveAndContinue"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','escape' => false), $this);?>
'" /></p>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

</form>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>