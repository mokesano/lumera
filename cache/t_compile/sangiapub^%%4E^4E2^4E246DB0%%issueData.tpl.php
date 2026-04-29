<?php /* Smarty version 2.6.26, created on 2026-04-13 22:20:17
         compiled from editor/issues/issueData.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'editor/issues/issueData.tpl', 18, false),array('function', 'translate', 'editor/issues/issueData.tpl', 18, false),array('function', 'html_options', 'editor/issues/issueData.tpl', 26, false),array('function', 'fieldLabel', 'editor/issues/issueData.tpl', 51, false),array('function', 'form_language_chooser', 'editor/issues/issueData.tpl', 54, false),array('function', 'math', 'editor/issues/issueData.tpl', 98, false),array('function', 'html_select_date', 'editor/issues/issueData.tpl', 105, false),array('function', 'call_hook', 'editor/issues/issueData.tpl', 153, false),array('modifier', 'escape', 'editor/issues/issueData.tpl', 26, false),array('modifier', 'truncate', 'editor/issues/issueData.tpl', 26, false),array('modifier', 'assign', 'editor/issues/issueData.tpl', 53, false),array('modifier', 'date_format', 'editor/issues/issueData.tpl', 95, false),array('modifier', 'default', 'editor/issues/issueData.tpl', 105, false),array('modifier', 'to_array', 'editor/issues/issueData.tpl', 163, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['issue']->getIssueIdentification()); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['issue']->getIssueIdentification(false,true)); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if (! $this->_tpl_vars['isLayoutEditor']): ?>	<ul class="menu">
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createIssue'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.createIssue"), $this);?>
</a></li>
		<li<?php if ($this->_tpl_vars['unpublished']): ?> class="current"<?php endif; ?>><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'futureIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.futureIssues"), $this);?>
</a></li>
		<li<?php if (! $this->_tpl_vars['unpublished']): ?> class="current"<?php endif; ?>><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'backIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.issueArchive"), $this);?>
</a></li>
	</ul>
<?php endif; ?>
<br />

<form action="#">
<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
: <select name="issue" class="selectMenu" onchange="if(this.options[this.selectedIndex].value > 0) location.href='<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueToc','path' => 'ISSUE_ID','escape' => false), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript'));?>
'.replace('ISSUE_ID', this.options[this.selectedIndex].value)" size="1"><?php echo smarty_function_html_options(array('options' => ((is_array($_tmp=$this->_tpl_vars['issueOptions'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")),'selected' => $this->_tpl_vars['issueId']), $this);?>
</select>
</form>

<div class="separator"></div>

<ul class="menu">
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueToc','path' => $this->_tpl_vars['issueId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</a></li>
	<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueData','path' => $this->_tpl_vars['issueId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.issueData"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueGalleys','path' => $this->_tpl_vars['issueId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.galleys"), $this);?>
</a></li>
	<?php if ($this->_tpl_vars['unpublished']): ?><li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getBestIssueId()), $this);?>
" target="_blank"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.previewIssue"), $this);?>
</a></li><?php endif; ?>
</ul>

<form id="issue" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editIssue','path' => $this->_tpl_vars['issueId']), $this);?>
" enctype="multipart/form-data">
<input type="hidden" name="fileName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['fileName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<input type="hidden" name="originalFileName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['originalFileName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php if ($this->_tpl_vars['styleFileName']): ?>
<input type="hidden" name="styleFileName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['styleFileName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<input type="hidden" name="originalStyleFileName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['originalStyleFileName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div id="issueId">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.identification"), $this);?>
</h3>
<table width="100%" class="data">
<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueData','path' => $this->_tpl_vars['issueId'],'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'issueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'issueUrl'));?>

			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'issue','url' => $this->_tpl_vars['issueUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
<?php endif; ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'volume','key' => "issue.volume"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="volume" id="volume" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['volume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="5" maxlength="5" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'number','key' => "issue.number"), $this);?>
</td>
		<td class="value"><input type="text" name="number" id="number" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['number'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="5" maxlength="10" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'year','key' => "issue.year"), $this);?>
</td>
		<td class="value"><input type="text" name="year" id="year" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['year'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="5" maxlength="4" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'labelFormat','key' => "editor.issues.issueIdentification"), $this);?>
</td>
		<td class="value"><input type="checkbox" name="showVolume" id="showVolume" value="1"<?php if ($this->_tpl_vars['showVolume']): ?> checked="checked"<?php endif; ?> /><label for="showVolume"> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
</label><br /><input type="checkbox" name="showNumber" id="showNumber" value="1"<?php if ($this->_tpl_vars['showNumber']): ?> checked="checked"<?php endif; ?> /><label for="showNumber"> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.number"), $this);?>
</label><br /><input type="checkbox" name="showYear" id="showYear" value="1"<?php if ($this->_tpl_vars['showYear']): ?> checked="checked"<?php endif; ?> /><label for="showYear"> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.year"), $this);?>
</label><br /><input type="checkbox" name="showTitle" id="showTitle" value="1"<?php if ($this->_tpl_vars['showTitle']): ?> checked="checked"<?php endif; ?> /><label for="showTitle"> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.title"), $this);?>
</label></td>
	</tr>
	<?php if ($this->_tpl_vars['enablePublicIssueId']): ?>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publicIssueId','key' => "editor.issues.publicIssueIdentifier"), $this);?>
</td>
		<td class="value"><input type="text" name="publicIssueId" id="publicIssueId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['publicIssueId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="255" class="textField" /></td>
	</tr>
	<?php endif; ?>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','key' => "issue.title"), $this);?>
</td>
		<td class="value"><input type="text" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'description','key' => "editor.issues.description"), $this);?>
</td>
		<td class="value"><textarea name="description[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="description" cols="40" rows="5" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['description'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.status"), $this);?>
</td>
		<td class="value">
			<?php if ($this->_tpl_vars['issue']->getPublished()): ?>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.published"), $this);?>
&nbsp;&nbsp;
								<?php $this->assign('currentYear', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php if ($this->_tpl_vars['datePublished']): ?>
					<?php $this->assign('publishedYear', ((is_array($_tmp=$this->_tpl_vars['datePublished'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
					<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "min(x,y)-10",'x' => $this->_tpl_vars['publishedYear'],'y' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'minYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'minYear'));?>

					<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "max(x,y)+2",'x' => $this->_tpl_vars['publishedYear'],'y' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'maxYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'maxYear'));?>

				<?php else: ?>
										<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x-10",'x' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'minYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'minYear'));?>

					<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x+2",'x' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'maxYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'maxYear'));?>

				<?php endif; ?>
				<?php echo smarty_function_html_select_date(array('prefix' => 'datePublished','time' => ((is_array($_tmp=@$this->_tpl_vars['datePublished'])) ? $this->_run_mod_handler('default', true, $_tmp, "---") : smarty_modifier_default($_tmp, "---")),'all_extra' => "class=\"selectMenu\"",'start_year' => $this->_tpl_vars['minYear'],'end_year' => $this->_tpl_vars['maxYear'],'year_empty' => "-",'month_empty' => "-",'day_empty' => "-"), $this);?>

				<br/>
				<input type="checkbox" id="resetArticlePublicationDates" name="resetArticlePublicationDates" />&nbsp;<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'resetArticlePublicationDates','key' => "editor.issues.resetArticlePublicationDates"), $this);?>
<br/>
			<?php else: ?>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.unpublished"), $this);?>

			<?php endif; ?>

			<?php if ($this->_tpl_vars['issue']->getDateNotified()): ?>
				<br/>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.usersNotified"), $this);?>
&nbsp;&nbsp;
				<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDateNotified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>

			<?php endif; ?>
		</td>
	</tr>
</table>
</div>

<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
<div class="separator"></div>
<div id="issueAccess">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.access"), $this);?>
</h3>
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'accessStatus','key' => "editor.issues.accessStatus"), $this);?>
</td>
		<td width="80%" class="value"><select name="accessStatus" id="accessStatus" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['accessOptions'],'selected' => $this->_tpl_vars['accessStatus']), $this);?>
</select></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'openAccessDate','key' => "editor.issues.accessDate"), $this);?>
</td>
		<td class="value">
			<input type="checkbox" id="enableOpenAccessDate" name="enableOpenAccessDate" <?php if ($this->_tpl_vars['openAccessDate']): ?>checked="checked" <?php endif; ?>onchange="document.getElementById('issue').openAccessDateMonth.disabled=this.checked?false:true;document.getElementById('issue').openAccessDateDay.disabled=this.checked?false:true;document.getElementById('issue').openAccessDateYear.disabled=this.checked?false:true;" />&nbsp;<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'enableOpenAccessDate','key' => "editor.issues.enableOpenAccessDate"), $this);?>
<br/>
			<?php if ($this->_tpl_vars['openAccessDate']): ?>
				<?php echo smarty_function_html_select_date(array('prefix' => 'openAccessDate','time' => $this->_tpl_vars['openAccessDate'],'end_year' => "+20",'all_extra' => "class=\"selectMenu\""), $this);?>

			<?php else: ?>
				<?php echo smarty_function_html_select_date(array('prefix' => 'openAccessDate','time' => $this->_tpl_vars['openAccessDate'],'end_year' => "+20",'all_extra' => "class=\"selectMenu\" disabled=\"disabled\""), $this);?>

			<?php endif; ?>
		</td>
	</tr>
</table>
</div>
<?php endif; ?>

<div class="separator"></div>

<?php $_from = $this->_tpl_vars['pubIdPlugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pubIdPlugin']):
?>
	<?php $this->assign('pubIdMetadataFile', $this->_tpl_vars['pubIdPlugin']->getPubIdMetadataFile()); ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['pubIdMetadataFile']), 'smarty_include_vars' => array('pubObject' => $this->_tpl_vars['issue'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endforeach; endif; unset($_from); ?>

<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Editor::Issues::IssueData::AdditionalMetadata"), $this);?>


<div id="issueCover">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.cover"), $this);?>
</h3>
<table width="100%" class="data">
	<tr valign="top">
		<td class="label" colspan="2"><input type="checkbox" name="showCoverPage[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="showCoverPage" value="1" <?php if ($this->_tpl_vars['showCoverPage'][$this->_tpl_vars['formLocale']]): ?> checked="checked"<?php endif; ?> /> <label for="showCoverPage"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.showCoverPage"), $this);?>
</label></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'coverPage','key' => "editor.issues.coverPage"), $this);?>
</td>
		<td width="80%" class="value"><input type="file" name="coverPage" id="coverPage" class="uploadField" />&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.saveToUpload"), $this);?>
<br /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.coverPageInstructions"), $this);?>
<br /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.uploaded"), $this);?>
:&nbsp;<?php if ($this->_tpl_vars['fileName'][$this->_tpl_vars['formLocale']]): ?><a href="javascript:openWindow('<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['fileName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
');" class="file"><?php echo $this->_tpl_vars['originalFileName'][$this->_tpl_vars['formLocale']]; ?>
</a>&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'removeIssueCoverPage','path' => ((is_array($_tmp=$this->_tpl_vars['issueId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['formLocale']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['formLocale']))), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.removeCoverPage"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.remove"), $this);?>
</a><?php else: ?>&mdash;<?php endif; ?></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'coverPageAltText','key' => "common.altText"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="coverPageAltText[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPageAltText'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value"><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.altTextInstructions"), $this);?>
</span></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'styleFile','key' => "editor.issues.styleFile"), $this);?>
</td>
		<td width="80%" class="value"><input type="file" name="styleFile" id="styleFile" class="uploadField" />&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.saveToUpload"), $this);?>
<br /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.uploaded"), $this);?>
:&nbsp;<?php if ($this->_tpl_vars['styleFileName']): ?><a href="javascript:openWindow('<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['styleFileName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
');" class="file"><?php echo ((is_array($_tmp=$this->_tpl_vars['originalStyleFileName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'removeStyleFile','path' => $this->_tpl_vars['issueId']), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.removeStyleFile"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.remove"), $this);?>
</a><?php else: ?>&mdash;<?php endif; ?></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'coverPageDescription','key' => "editor.issues.coverPageCaption"), $this);?>
</td>
		<td class="value"><textarea name="coverPageDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="coverPageDescription" cols="40" rows="5" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['coverPageDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'hideCoverPageArchives','key' => "editor.issues.coverPageDisplay"), $this);?>
</td>
		<td width="80%" class="value"><input type="checkbox" name="hideCoverPageArchives[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="hideCoverPageArchives" value="1" <?php if ($this->_tpl_vars['hideCoverPageArchives'][$this->_tpl_vars['formLocale']]): ?> checked="checked"<?php endif; ?> /> <label for="hideCoverPageArchives"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.hideCoverPageArchives"), $this);?>
</label></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">&nbsp;</td>
		<td class="value"><input type="checkbox" name="hideCoverPageCover[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="hideCoverPageCover" value="1" <?php if ($this->_tpl_vars['hideCoverPageCover'][$this->_tpl_vars['formLocale']]): ?> checked="checked"<?php endif; ?> /> <label for="hideCoverPageCover"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.hideCoverPageCover"), $this);?>
</label></td>
	</tr>
</table>
</div>
<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'backIssues','escape' => false), $this);?>
'" class="button" /></p>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
