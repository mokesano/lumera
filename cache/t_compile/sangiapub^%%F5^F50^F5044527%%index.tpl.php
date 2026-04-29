<?php /* Smarty version 2.6.26, created on 2026-04-04 21:38:12
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 30, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 53, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 105, false),array('modifier', 'default', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 115, false),array('modifier', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 115, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 41, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 43, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 51, false),array('function', 'form_language_chooser', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 68, false),array('function', 'html_options', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 96, false),array('function', 'math', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 108, false),array('function', 'html_select_date', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 115, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/quickSubmit/index.tpl', 149, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.quickSubmit.displayName"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php echo '
<script type="text/javascript">
<!--
// Move author up/down
function moveAuthor(dir, authorIndex) {
	var form = document.getElementById(\'submit\');
	form.moveAuthor.value = 1;
	form.moveAuthorDir.value = dir;
	form.moveAuthorIndex.value = authorIndex;
	form.submit();
}

// Update the required attribute of the abstract field
function updateAbstractRequired() {
	var a = {'; ?>
<?php $_from = $this->_tpl_vars['sectionAbstractsRequired']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rSectionId'] => $this->_tpl_vars['rAbstractRequired']):
?><?php echo ((is_array($_tmp=$this->_tpl_vars['rSectionId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['rAbstractRequired'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php endforeach; endif; unset($_from); ?><?php echo '};
	var selectedIndex = document.getElementById(\'submit\').sectionId.selectedIndex;
	var sectionId = document.getElementById(\'submit\').sectionId.options[selectedIndex].value;
	var abstractRequired = a[sectionId];
	var e = document.getElementById("abstractRequiredAsterisk");
	e.style.visibility = abstractRequired?"visible":"hidden";
}
// -->
</script>
'; ?>


<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.quickSubmit.descriptionLong"), $this);?>
</p>

<form enctype="multipart/form-data" id="submit" method="post" action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'saveSubmit'), $this);?>
">

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
<div id="locales">
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php echo ((is_array($_tmp=$this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'quickSubmitUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'quickSubmitUrl'));?>

						<?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['authorIndex'] => $this->_tpl_vars['author']):
?>
				<?php if ($this->_tpl_vars['currentJournal']->getSetting('requireAuthorCompetingInterests')): ?>
					<?php $_from = $this->_tpl_vars['author']['competingInterests']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['thisLocale'] => $this->_tpl_vars['thisCompetingInterests']):
?>
						<?php if ($this->_tpl_vars['thisLocale'] != $this->_tpl_vars['formLocale']): ?><input type="hidden" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][competingInterests][<?php echo ((is_array($_tmp=$this->_tpl_vars['thisLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['thisCompetingInterests'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /><?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				<?php endif; ?>
				<?php $_from = $this->_tpl_vars['author']['biography']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['thisLocale'] => $this->_tpl_vars['thisBiography']):
?>
					<?php if ($this->_tpl_vars['thisLocale'] != $this->_tpl_vars['formLocale']): ?><input type="hidden" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][biography][<?php echo ((is_array($_tmp=$this->_tpl_vars['thisLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['thisBiography'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /><?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
				<?php $_from = $this->_tpl_vars['author']['affiliation']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['thisLocale'] => $this->_tpl_vars['thisAffiliation']):
?>
					<?php if ($this->_tpl_vars['thisLocale'] != $this->_tpl_vars['formLocale']): ?><input type="hidden" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][affiliation][<?php echo ((is_array($_tmp=$this->_tpl_vars['thisLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['thisAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /><?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
			<?php endforeach; endif; unset($_from); ?>
			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'submit','url' => $this->_tpl_vars['quickSubmitUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>
<?php endif; ?>

<div id="chooseDestination">
	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.quickSubmit.chooseDestination"), $this);?>
</h3>

	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.quickSubmit.chooseDestinationDescription"), $this);?>
</p>

	<table class="data" width="100%">
		<tr valign="top">
			<td class="label" width="5%">
				<input type="radio" name="destination" id="destinationUnpublished" value="queue" <?php if (! $this->_tpl_vars['publishToIssue']): ?> checked="checked"<?php endif; ?><?php if ($this->_tpl_vars['enablePageNumber']): ?> onclick="document.getElementById('submit').pages.disabled = true;document.getElementById('submit').pagesHidden.value = document.getElementById('submit').pages.value; document.getElementById('submit').pages.value = '';"<?php endif; ?>/>
			</td>
			<td colspan="2" class="value" width="95%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'destinationUnpublished','key' => "plugins.importexport.quickSubmit.leaveUnpublished"), $this);?>
</td>
		</tr>
		<tr valign="top">
			<td rowspan="2" class="label">
				<input type="radio" id="destinationIssue" name="destination" value="issue" <?php if ($this->_tpl_vars['publishToIssue']): ?> checked="checked"<?php endif; ?><?php if ($this->_tpl_vars['enablePageNumber']): ?> onclick="document.getElementById('submit').pages.disabled = false;document.getElementById('submit').pages.value = document.getElementById('submit').pagesHidden.value;"<?php endif; ?>/>
			</td>
			<td width="20%" class="value">
				<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'destinationIssue','key' => "plugins.importexport.quickSubmit.addToExisting"), $this);?>

			</td>
			<td class="value">
				<select name="issueId" id="issueId" size="1" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['issueOptions'],'selected' => $this->_tpl_vars['issueNumber']), $this);?>
</select>
			</td>
		</tr>
		<tr valign="top">
			<td class="label">
				<label for="issueId"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.published"), $this);?>
</label>
			</td>
			<td class="value">
								<?php $this->assign('currentYear', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php if ($this->_tpl_vars['datePublished']): ?>
					<?php $this->assign('publishedYear', ((is_array($_tmp=$this->_tpl_vars['datePublished'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
					<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "min(x,y)-10",'x' => $this->_tpl_vars['publishedYear'],'y' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'minYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'minYear'));?>

					<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "max(x,y)+2",'x' => $this->_tpl_vars['publishedYear'],'y' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'maxYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'maxYear'));?>

				<?php else: ?>
										<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x-10",'x' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'minYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'minYear'));?>

					<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x+2",'x' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'maxYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'maxYear'));?>

				<?php endif; ?>
				<?php echo smarty_function_html_select_date(array('prefix' => 'datePublished','time' => ((is_array($_tmp=@$this->_tpl_vars['datePublished'])) ? $this->_run_mod_handler('default', true, $_tmp, "---") : smarty_modifier_default($_tmp, "---")),'all_extra' => "class=\"selectMenu\"",'start_year' => $this->_tpl_vars['minYear'],'end_year' => $this->_tpl_vars['maxYear'],'year_empty' => ((is_array($_tmp="common.year")) ? $this->_run_mod_handler('translate', true, $_tmp) : AppLocale::translate($_tmp)),'month_empty' => ((is_array($_tmp="common.month")) ? $this->_run_mod_handler('translate', true, $_tmp) : AppLocale::translate($_tmp)),'day_empty' => ((is_array($_tmp="common.day")) ? $this->_run_mod_handler('translate', true, $_tmp) : AppLocale::translate($_tmp))), $this);?>

			</td>
		</tr>
		<?php if ($this->_tpl_vars['enablePageNumber']): ?>
			<tr valign="top">
				<td class="label">&nbsp;</td>
				<td colspan="2" class="value">
					<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'pages','key' => "editor.issues.pages"), $this);?>
&nbsp;
					<input name="pages" id="pages" <?php if ($this->_tpl_vars['publishToIssue']): ?>value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pages'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php else: ?>disabled="disabled" <?php endif; ?>size="20" maxlength="40" class="textField" />
					<input type="hidden" name="pagesHidden" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pages'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
				</td>
			</tr>
		<?php endif; ?>	</table>
</div> <!-- /chooseDestination -->

<div class="separator"></div>

<br />

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.quickSubmit.submissionData"), $this);?>
</h3>

<div id="submission" style="margin: 0 10px 0 10px;">
	<div id="section">
		<?php if (count ( $this->_tpl_vars['sectionOptions'] ) == 2): ?>
						<?php $_from = $this->_tpl_vars['sectionOptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['val']):
?>
				<input type="hidden" name="sectionId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
				<?php $this->assign('abstractRequired', $this->_tpl_vars['sectionAbstractsRequired'][$this->_tpl_vars['key']]); ?>
			<?php endforeach; endif; unset($_from); ?>
		<?php else: ?>		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.journalSection"), $this);?>
</h4>

		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.journalSectionDescription",'aboutUrl' => $this->_tpl_vars['url']), $this);?>
</p>

		<table class="data" width="100%">
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'sectionId','required' => 'true','key' => "section.section"), $this);?>
</td>
				<td width="70%" class="value"><select name="sectionId" id="sectionId" size="1" class="selectMenu" onchange="updateAbstractRequired()"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['sectionOptions'],'selected' => $this->_tpl_vars['sectionId']), $this);?>
</select></td>
			</tr>
		</table>

		<?php endif; ?>	</div> <!-- /section -->

<?php if (count ( $this->_tpl_vars['supportedSubmissionLocaleNames'] ) == 1): ?>
		<?php $_from = $this->_tpl_vars['supportedSubmissionLocaleNames']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['locale'] => $this->_tpl_vars['localeName']):
?>
		<input type="hidden" name="locale" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['locale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endforeach; endif; unset($_from); ?>
<?php else: ?>
		<div id="submissionLocale">

	<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionLocale"), $this);?>
</h4>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionLocaleDescription"), $this);?>
</p>

	<table class="data" width="100%">
		<tr valign="top">
			<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'locale','required' => 'true','key' => "article.language"), $this);?>
</td>
			<td width="70%" class="value"><select name="locale" id="locale" size="1" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['supportedSubmissionLocaleNames'],'selected' => $this->_tpl_vars['locale']), $this);?>
</select></td>
		</tr>
	</table>
	</div><?php endif; ?>
	<div id="submissionFile">
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionFile"), $this);?>
</h4>
		<table class="data" width="100%">
		<?php if ($this->_tpl_vars['submissionFile']): ?>
		<tr valign="top">
			<td width="30%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.originalFileName"), $this);?>
</td>
			<td width="70%" class="value"><?php echo ((is_array($_tmp=$this->_tpl_vars['submissionFile']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		</tr>
		<tr valign="top">
			<td width="30%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fileSize"), $this);?>
</td>
			<td width="70%" class="value"><?php echo $this->_tpl_vars['submissionFile']->getNiceFileSize(); ?>
</td>
		</tr>
		<tr valign="top">
			<td width="30%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.dateUploaded"), $this);?>
</td>
			<td width="70%" class="value"><?php echo ((is_array($_tmp=$this->_tpl_vars['submissionFile']->getDateUploaded())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatShort'])); ?>
</td>
		</tr>
		<?php else: ?>
		<tr valign="top">
			<td colspan="2" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.quickSubmit.submissionDescription"), $this);?>
</td>
		</tr>
		<?php endif; ?>
		</table>
	</div> <!-- /submissionFile -->

	<div id="addSubmissionFile">
		<input type="hidden" name="tempFileId[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="tempFileId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['tempFileId'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
		<table class="data" width="100%">
		<tr>
			<td width="30%" class="label">
				<?php if ($this->_tpl_vars['submissionFile']): ?>
					<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'submissionFile','key' => "author.submit.replaceSubmissionFile"), $this);?>

				<?php else: ?>
					<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'submissionFile','key' => "author.submit.uploadSubmissionFile"), $this);?>

				<?php endif; ?>
			</td>
			<td width="70%" class="value">
				<input type="file" class="uploadField" name="submissionFile" id="submissionFileUpload" /> <input name="uploadSubmissionFile" type="submit" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" />
			</td>
		</tr>
		</table>
	</div>  <!-- /addSubmissionFile -->

	<div id="authors">
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</h4>
		<input type="hidden" name="moveAuthor" value="0" />
		<input type="hidden" name="moveAuthorDir" value="" />
		<input type="hidden" name="moveAuthorIndex" value="" />

		<?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['authorIndex'] => $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?>
			<input type="hidden" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][authorId]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['authorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
			<input type="hidden" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][seq]" value="<?php echo $this->_tpl_vars['authorIndex']+1; ?>
" />
			<?php if ($this->_foreach['authors']['total'] <= 1): ?>
			<input type="hidden" name="primaryContact" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
			<?php endif; ?>

			<table width="100%" class="data">
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-firstName",'required' => 'true','key' => "user.firstName"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][firstName]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-firstName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="40" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-middleName",'key' => "user.middleName"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][middleName]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-middleName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="40" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-lastName",'required' => 'true','key' => "user.lastName"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][lastName]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-lastName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-email",'required' => 'true','key' => "user.email"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][email]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-email" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-orcid",'key' => "user.orcid"), $this);?>
</td>
				<td width="80%" class="value"><input type="text" class="textField" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][orcid]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-orcid" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['orcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" /><br /><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid.description"), $this);?>
</td>
			</tr>
			<tr valign="top">
				<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-url",'key' => "user.url"), $this);?>
</td>
				<td class="value"><input type="text" name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][url]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-url" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="255" class="textField" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-affiliation",'key' => "user.affiliation"), $this);?>
</td>
				<td width="70%" class="value"><textarea name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][affiliation][<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" class="textArea" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-affiliation" rows="5" cols="40"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']['affiliation'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-country",'key' => "common.country"), $this);?>
</td>
				<td width="70%" class="value">
					<select name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][country]" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-country" class="selectMenu">
						<option value=""></option>
						<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['countries'],'selected' => $this->_tpl_vars['author']['country']), $this);?>

					</select>
				</td>
			</tr>
			<?php if ($this->_tpl_vars['journal']->getSetting('requireAuthorCompetingInterests')): ?>
				<tr valign="top">
					<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-competingInterests",'key' => "author.competingInterests",'competingInterestGuidelinesUrl' => $this->_tpl_vars['competingInterestGuidelinesUrl']), $this);?>
</td>
					<td width="70%" class="value"><textarea name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][competingInterests][<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" class="textArea" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-competingInterests" rows="5" cols="40"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']['competingInterests'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
				</tr>
			<?php endif; ?>			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-".($this->_tpl_vars['authorIndex'])."-biography",'key' => "user.biography"), $this);?>
</td>
				<td width="70%" class="value"><textarea name="authors[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][biography][<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" class="textArea" id="authors-<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-biography" rows="5" cols="40"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']['biography'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
				    <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography.description"), $this);?>
</span>
				</td>
			</tr>
			<?php if ($this->_foreach['authors']['total'] > 1): ?>
			<tr valign="top">
				<td colspan="2">
					<a href="javascript:moveAuthor('u', '<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
')" class="action">&uarr;</a> <a href="javascript:moveAuthor('d', '<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
')" class="action">&darr;</a>
					<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.reorderInstructions"), $this);?>

				</td>
			</tr>
			<tr valign="top">
				<td width="70%" class="value" colspan="2"><input type="radio" id="primaryContact" name="primaryContact" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['primaryContact'] == $this->_tpl_vars['authorIndex']): ?> checked="checked"<?php endif; ?> /> <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'primaryContact','key' => "author.submit.selectPrincipalContact"), $this);?>
 <input type="submit" name="delAuthor[<?php echo ((is_array($_tmp=$this->_tpl_vars['authorIndex'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.deleteAuthor"), $this);?>
" class="button" /></td>
			</tr>
			<tr>
				<td colspan="2"><br /></td>
			</tr>
			<?php endif; ?>
			</table>

			<?php if (! ($this->_foreach['authors']['iteration'] == $this->_foreach['authors']['total'])): ?><div class="separator" style="width:70%"></div><?php endif; ?>
		<?php endforeach; else: ?>
			<input type="hidden" name="authors[0][authorId]" value="0" />
			<input type="hidden" name="primaryContact" value="0" />
			<input type="hidden" name="authors[0][seq]" value="1" />
			<table width="100%" class="data">
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-firstName",'required' => 'true','key' => "user.firstName"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[0][firstName]" id="authors-0-firstName" size="20" maxlength="40" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-middleName",'key' => "user.middleName"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[0][middleName]" id="authors-0-middleName" size="20" maxlength="40" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-lastName",'required' => 'true','key' => "user.lastName"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[0][lastName]" id="authors-0-lastName" size="20" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-affiliation",'key' => "user.affiliation"), $this);?>
</td>
				<td width="70%" class="value"><textarea name="authors[0][affiliation][<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" class="textArea" id="authors-0-affiliation" rows="5" cols="40"></textarea></td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-country",'key' => "common.country"), $this);?>
</td>
				<td width="70%" class="value">
					<select name="authors[0][country]" id="authors-0-country" class="selectMenu">
						<option value=""></option>
						<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['countries']), $this);?>

					</select>
				</td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-email",'required' => 'true','key' => "user.email"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[0][email]" id="authors-0-email" size="30" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-orcid",'key' => "user.orcid"), $this);?>
</td>
				<td width="80%" class="value"><input type="text" class="textField" name="authors[0][orcid]" id="authors-0-orcid" size="30" maxlength="90" />
				    <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid.description"), $this);?>
</span>
				</td>
			</tr>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-url",'key' => "user.url"), $this);?>
</td>
				<td width="70%" class="value"><input type="text" class="textField" name="authors[0][url]" id="authors-0-url" size="30" maxlength="255" /></td>
			</tr>
			<?php if ($this->_tpl_vars['journal']->getSetting('requireAuthorCompetingInterests')): ?>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-competingInterests",'key' => "author.competingInterests",'competingInterestGuidelinesUrl' => $this->_tpl_vars['competingInterestGuidelinesUrl']), $this);?>
</td>
				<td width="70%" class="value"><textarea name="authors[0][competingInterests][<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" class="textArea" id="authors-0-competingInterests" rows="5" cols="40"></textarea></td>
			</tr>
			<?php endif; ?>
			<tr valign="top">
				<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "authors-0-biography",'key' => "user.biography"), $this);?>
</td>
				<td width="70%" class="value"><textarea name="authors[0][biography][<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" class="textArea" id="authors-0-biography" rows="5" cols="40"></textarea>
				    <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography.description"), $this);?>
</span>
				</td>
			</tr>
			</table>
		<?php endif; unset($_from); ?>

		<p><input type="submit" class="button" name="addAuthor" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.addAuthor"), $this);?>
" /></p>
	</div> <!-- /authors -->

	<div id="titleAndAbstract">
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.titleAndAbstract"), $this);?>
</h4>

		<table width="100%" class="data">

		<tr valign="top">
			<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','required' => 'true','key' => "article.title"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" /></td>
		</tr>

		<tr valign="top">
			<?php if ($this->_tpl_vars['sectionAbstractsRequired'][$this->_tpl_vars['sectionId']]): ?>
								<?php $this->assign('abstractRequired', 'true'); ?>
			<?php endif; ?>
			<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'abstract','key' => "article.abstract",'required' => $this->_tpl_vars['abstractRequired']), $this);?>
<span id="abstractRequiredAsterisk" style="visibility: hidden;">*</div></td>
			<td width="70%" class="value"><textarea name="abstract[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="abstract" class="textArea" rows="15" cols="60"><?php echo ((is_array($_tmp=$this->_tpl_vars['abstract'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
		</tr>
		</table>
	</div> <!-- /titleAndAbstract -->

	<div id="indexing">
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.indexing"), $this);?>
</h4>
		<?php if ($this->_tpl_vars['journal']->getSetting('metaDiscipline') || $this->_tpl_vars['journal']->getSetting('metaSubjectClass') || $this->_tpl_vars['journal']->getSetting('metaSubject') || $this->_tpl_vars['journal']->getSetting('metaCoverage') || $this->_tpl_vars['journal']->getSetting('metaType')): ?><p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionIndexingDescription"), $this);?>
</p><?php endif; ?>
		<table width="100%" class="data">
		<?php if ($this->_tpl_vars['journal']->getSetting('metaDiscipline')): ?>
		<tr valign="top">
			<td<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaDisciplineExamples') != ''): ?> rowspan="2"<?php endif; ?> width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'discipline','key' => "article.discipline"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="discipline[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="discipline" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['discipline'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" />
    			<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaDisciplineExamples')): ?>
    			<span class="instruct"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaDisciplineExamples'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
    			<?php endif; ?>
			</td>
		</tr>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['journal']->getSetting('metaSubjectClass')): ?>
		<tr valign="top">
			<td rowspan="2" width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'subjectClass','key' => "article.subjectClassification"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="subjectClass[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="subjectClass" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['subjectClass'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" /></td>
		</tr>
		<tr valign="top">
			<td width="30%" class="label"><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaSubjectClassUrl'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaSubjectClassTitle'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></td>
		</tr>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['journal']->getSetting('metaSubject')): ?>
		<tr valign="top">
			<td<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaSubjectExamples') != ''): ?> rowspan="2"<?php endif; ?> width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'subject','key' => "article.subject"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="subject[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="subject" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['subject'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" /></td>
		</tr>
		<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaSubjectExamples') != ''): ?>
		<tr valign="top">
			<td><span class="instruct"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaSubjectExamples'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['journal']->getSetting('metaCoverage')): ?>
		<tr valign="top">
			<td<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageGeoExamples') != ''): ?> rowspan="2"<?php endif; ?> width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'coverageGeo','key' => "article.coverageGeo"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="coverageGeo[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="coverageGeo" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverageGeo'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" /></td>
		</tr>
		<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageGeoExamples')): ?>
		<tr valign="top">
			<td><span class="instruct"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageGeoExamples'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr valign="top">
			<td<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageChronExamples') != ''): ?> rowspan="2"<?php endif; ?> width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'coverageChron','key' => "article.coverageChron"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="coverageChron[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="coverageChron" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverageChron'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" /></td>
		</tr>
		<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageChronExamples') != ''): ?>
		<tr valign="top">
			<td><span class="instruct"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageChronExamples'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr valign="top">
			<td<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''): ?> rowspan="2"<?php endif; ?> width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'coverageSample','key' => "article.coverageSample"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="coverageSample[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="coverageSample" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverageSample'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" /></td>
		</tr>
		<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''): ?>
		<tr valign="top">
			<td><span class="instruct"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaCoverageResearchSampleExamples'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['journal']->getSetting('metaType')): ?>
		<tr valign="top">
			<td width="30%" <?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaTypeExamples') != ''): ?>rowspan="2" <?php endif; ?>class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'type','key' => "article.type"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="type[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="type" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['type'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" /></td>
		</tr>

		<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('metaTypeExamples') != ''): ?>
		<tr valign="top">
			<td><span class="instruct"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('metaTypeExamples'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></td>
		</tr>
		<?php endif; ?>
		<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php endif; ?>

		<tr valign="top">
			<td rowspan="2" width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'language','key' => "article.language"), $this);?>
</td>
			<td width="70%" class="value"><input type="text" class="textField" name="language" id="language" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['language'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="5" maxlength="10" />
			    <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.languageInstructions"), $this);?>
</span>
			</td>
		</tr>
		</table>
	</div> <!-- /indexing -->

	<div id="submissionSupportingAgencies">
	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionSupportingAgencies"), $this);?>
</h3>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionSupportingAgenciesDescription"), $this);?>
</p>

	<table width="100%" class="data">
	<tr valign="top">
		<td width="30%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'sponsor','key' => "submission.agencies"), $this);?>
</td>
		<td width="70%" class="value"><input type="text" class="textField" name="sponsor[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="sponsor" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsor'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="255" /></td>
	</tr>
	</table>
	</div> <!-- /submissionSupportingAgencies -->

	<?php if ($this->_tpl_vars['journal']->getSetting('metaCitations')): ?>
	<div id="metaCitations">
	<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations"), $this);?>
</h4>

	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionCitations"), $this);?>
</p>

	<table width="100%" class="data">
		<tr valign="top">
						<td width="70%" class="value"><textarea name="citations" id="citations" class="textArea" rows="15" cols="60"><?php echo ((is_array($_tmp=$this->_tpl_vars['citations'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
		</tr>
	</table>
	</div> <!-- /metaCitations -->
	<?php endif; ?>
</div> <!-- /submission -->

<div class="separator"></div>

<br />

<p class="u-mt-32">
    <input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.saveAndContinue"), $this);?>
" class="button defaultButton" />
    <input type="submit" class="button" name="createAnother" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.quickSubmit.saveAndCreateAnother"), $this);?>
" />
    <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author'), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.cancelSubmission"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" />
</p>

<p class="u-mt-24">
    <span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span>
</p>

</form>

<?php if ($this->_tpl_vars['scrollToAuthor']): ?>
	<?php echo '
	<script type="text/javascript">
		var authors = document.getElementById(\'authors\');
		authors.scrollIntoView(false);
	</script>
	'; ?>

<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>