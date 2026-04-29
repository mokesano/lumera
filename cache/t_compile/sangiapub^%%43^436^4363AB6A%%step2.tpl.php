<?php /* Smarty version 2.6.26, created on 2026-04-14 13:09:13
         compiled from manager/setup/step2.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/setup/step2.tpl', 14, false),array('function', 'fieldLabel', 'manager/setup/step2.tpl', 22, false),array('function', 'form_language_chooser', 'manager/setup/step2.tpl', 25, false),array('function', 'translate', 'manager/setup/step2.tpl', 26, false),array('function', 'get_help_id', 'manager/setup/step2.tpl', 158, false),array('function', 'call_hook', 'manager/setup/step2.tpl', 218, false),array('modifier', 'escape', 'manager/setup/step2.tpl', 16, false),array('modifier', 'assign', 'manager/setup/step2.tpl', 24, false),)), $this); ?>
<?php $this->assign('pageTitle', "manager.setup.journalPolicies"); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "manager/setup/setupHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form id="setupForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','path' => '2'), $this);?>
">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">

<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
<div id="locales" class="block">
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','path' => '2','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'setupFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'setupFormUrl'));?>

			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'setupForm','url' => $this->_tpl_vars['setupFormUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>
<?php endif; ?>
<div id="focusAndScopeDescription" class="block">
<h3>2.1 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.focusAndScopeOfJournal"), $this);?>
</h3>
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.focusAndScopeDescription"), $this);?>
</p>
<p>
	<textarea name="focusScopeDesc[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="focusScopeDesc" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['focusScopeDesc'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
</p>
</div>

<div class="separator"></div>

<div id="peerReviewPolicy" class="block">
<h3>2.2 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.peerReviewPolicy"), $this);?>
</h3>
<div id="peerReviewDescription">
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.peerReviewDescription"), $this);?>
</p>

<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewPolicy"), $this);?>
</h4>

<p><textarea name="reviewPolicy[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="reviewPolicy" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewPolicy'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></p>
</div>
<div id="reviewGuidelinesInfo" class="block">

<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewGuidelines"), $this);?>
</h4>

<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'reviewForms'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'reviewFormsUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'reviewFormsUrl'));?>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewGuidelinesDescription",'reviewFormsUrl' => $this->_tpl_vars['reviewFormsUrl']), $this);?>
</p>

<p><textarea name="reviewGuidelines[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="reviewGuidelines" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewGuidelines'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></p>
</div>
<div id="reviewProcess" class="block">
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewProcess"), $this);?>
</h4>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewProcessDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="5%" class="label" align="right">
			<input type="radio" name="mailSubmissionsToReviewers" id="mailSubmissionsToReviewers-0" value="0"<?php if (! $this->_tpl_vars['mailSubmissionsToReviewers']): ?> checked="checked"<?php endif; ?> />
		</td>
		<td width="95%" class="value">
			<label for="mailSubmissionsToReviewers-0"><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewProcessStandard"), $this);?>
</strong></label>
			<br />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewProcessStandardDescription"), $this);?>
</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td width="5%" class="label" align="right">
			<input type="radio" name="mailSubmissionsToReviewers" id="mailSubmissionsToReviewers-1" value="1"<?php if ($this->_tpl_vars['mailSubmissionsToReviewers']): ?> checked="checked"<?php endif; ?> />
		</td>
		<td width="95%" class="value">
			<label for="mailSubmissionsToReviewers-1"><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewProcessEmail"), $this);?>
</strong></label>
			<br />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewProcessEmailDescription"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>
<div id="reviewOptions" class="block">
<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions"), $this);?>
</h4>

	<script type="text/javascript">
		<?php echo '
		<!--
			function toggleAllowSetInviteReminder(form) {
				form.numDaysBeforeInviteReminder.disabled = !form.numDaysBeforeInviteReminder.disabled;
			}
			function toggleAllowSetSubmitReminder(form) {
				form.numDaysBeforeSubmitReminder.disabled = !form.numDaysBeforeSubmitReminder.disabled;
			}
		// -->
		'; ?>

	</script>

<p>
	<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.reviewTime"), $this);?>
</strong><br/>
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.numWeeksPerReview"), $this);?>
: <input type="text" name="numWeeksPerReview" id="numWeeksPerReview" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['numWeeksPerReview'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="2" maxlength="8" class="textField" /> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.weeks"), $this);?>
</p>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.note"), $this);?>
: <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.noteOnModification"), $this);?>
</p>


	<p>
		<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.reviewerReminders"), $this);?>
</strong></p>
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.automatedReminders"), $this);?>
:<br/>
		<input type="checkbox" name="remindForInvite" id="remindForInvite" value="1" onclick="toggleAllowSetInviteReminder(this.form)"<?php if (! $this->_tpl_vars['scheduledTasksEnabled']): ?> disabled="disabled" <?php elseif ($this->_tpl_vars['remindForInvite']): ?> checked="checked"<?php endif; ?> />&nbsp;
		<label for="remindForInvite"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.remindForInvite1"), $this);?>
</label>
		<select name="numDaysBeforeInviteReminder" size="1" class="selectMenu"<?php if (! $this->_tpl_vars['remindForInvite'] || ! $this->_tpl_vars['scheduledTasksEnabled']): ?> disabled="disabled"<?php endif; ?>>
			<?php unset($this->_sections['inviteDayOptions']);
$this->_sections['inviteDayOptions']['name'] = 'inviteDayOptions';
$this->_sections['inviteDayOptions']['start'] = (int)3;
$this->_sections['inviteDayOptions']['loop'] = is_array($_loop=11) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['inviteDayOptions']['show'] = true;
$this->_sections['inviteDayOptions']['max'] = $this->_sections['inviteDayOptions']['loop'];
$this->_sections['inviteDayOptions']['step'] = 1;
if ($this->_sections['inviteDayOptions']['start'] < 0)
    $this->_sections['inviteDayOptions']['start'] = max($this->_sections['inviteDayOptions']['step'] > 0 ? 0 : -1, $this->_sections['inviteDayOptions']['loop'] + $this->_sections['inviteDayOptions']['start']);
else
    $this->_sections['inviteDayOptions']['start'] = min($this->_sections['inviteDayOptions']['start'], $this->_sections['inviteDayOptions']['step'] > 0 ? $this->_sections['inviteDayOptions']['loop'] : $this->_sections['inviteDayOptions']['loop']-1);
if ($this->_sections['inviteDayOptions']['show']) {
    $this->_sections['inviteDayOptions']['total'] = min(ceil(($this->_sections['inviteDayOptions']['step'] > 0 ? $this->_sections['inviteDayOptions']['loop'] - $this->_sections['inviteDayOptions']['start'] : $this->_sections['inviteDayOptions']['start']+1)/abs($this->_sections['inviteDayOptions']['step'])), $this->_sections['inviteDayOptions']['max']);
    if ($this->_sections['inviteDayOptions']['total'] == 0)
        $this->_sections['inviteDayOptions']['show'] = false;
} else
    $this->_sections['inviteDayOptions']['total'] = 0;
if ($this->_sections['inviteDayOptions']['show']):

            for ($this->_sections['inviteDayOptions']['index'] = $this->_sections['inviteDayOptions']['start'], $this->_sections['inviteDayOptions']['iteration'] = 1;
                 $this->_sections['inviteDayOptions']['iteration'] <= $this->_sections['inviteDayOptions']['total'];
                 $this->_sections['inviteDayOptions']['index'] += $this->_sections['inviteDayOptions']['step'], $this->_sections['inviteDayOptions']['iteration']++):
$this->_sections['inviteDayOptions']['rownum'] = $this->_sections['inviteDayOptions']['iteration'];
$this->_sections['inviteDayOptions']['index_prev'] = $this->_sections['inviteDayOptions']['index'] - $this->_sections['inviteDayOptions']['step'];
$this->_sections['inviteDayOptions']['index_next'] = $this->_sections['inviteDayOptions']['index'] + $this->_sections['inviteDayOptions']['step'];
$this->_sections['inviteDayOptions']['first']      = ($this->_sections['inviteDayOptions']['iteration'] == 1);
$this->_sections['inviteDayOptions']['last']       = ($this->_sections['inviteDayOptions']['iteration'] == $this->_sections['inviteDayOptions']['total']);
?>
			<option value="<?php echo $this->_sections['inviteDayOptions']['index']; ?>
"<?php if ($this->_tpl_vars['numDaysBeforeInviteReminder'] == $this->_sections['inviteDayOptions']['index'] || ( $this->_sections['inviteDayOptions']['index'] == 5 && ! $this->_tpl_vars['remindForInvite'] )): ?> selected="selected"<?php endif; ?>><?php echo $this->_sections['inviteDayOptions']['index']; ?>
</option>
			<?php endfor; endif; ?>
		</select>
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.remindForInvite2"), $this);?>

		<br/>

		<input type="checkbox" name="remindForSubmit" id="remindForSubmit" value="1" onclick="toggleAllowSetSubmitReminder(this.form)"<?php if (! $this->_tpl_vars['scheduledTasksEnabled']): ?> disabled="disabled"<?php elseif ($this->_tpl_vars['remindForSubmit']): ?> checked="checked"<?php endif; ?> />&nbsp;
		<label for="remindForSubmit"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.remindForSubmit1"), $this);?>
</label>
		<select name="numDaysBeforeSubmitReminder" size="1" class="selectMenu"<?php if (! $this->_tpl_vars['remindForSubmit'] || ! $this->_tpl_vars['scheduledTasksEnabled']): ?> disabled="disabled"<?php endif; ?>>
			<?php unset($this->_sections['submitDayOptions']);
$this->_sections['submitDayOptions']['name'] = 'submitDayOptions';
$this->_sections['submitDayOptions']['start'] = (int)0;
$this->_sections['submitDayOptions']['loop'] = is_array($_loop=11) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['submitDayOptions']['show'] = true;
$this->_sections['submitDayOptions']['max'] = $this->_sections['submitDayOptions']['loop'];
$this->_sections['submitDayOptions']['step'] = 1;
if ($this->_sections['submitDayOptions']['start'] < 0)
    $this->_sections['submitDayOptions']['start'] = max($this->_sections['submitDayOptions']['step'] > 0 ? 0 : -1, $this->_sections['submitDayOptions']['loop'] + $this->_sections['submitDayOptions']['start']);
else
    $this->_sections['submitDayOptions']['start'] = min($this->_sections['submitDayOptions']['start'], $this->_sections['submitDayOptions']['step'] > 0 ? $this->_sections['submitDayOptions']['loop'] : $this->_sections['submitDayOptions']['loop']-1);
if ($this->_sections['submitDayOptions']['show']) {
    $this->_sections['submitDayOptions']['total'] = min(ceil(($this->_sections['submitDayOptions']['step'] > 0 ? $this->_sections['submitDayOptions']['loop'] - $this->_sections['submitDayOptions']['start'] : $this->_sections['submitDayOptions']['start']+1)/abs($this->_sections['submitDayOptions']['step'])), $this->_sections['submitDayOptions']['max']);
    if ($this->_sections['submitDayOptions']['total'] == 0)
        $this->_sections['submitDayOptions']['show'] = false;
} else
    $this->_sections['submitDayOptions']['total'] = 0;
if ($this->_sections['submitDayOptions']['show']):

            for ($this->_sections['submitDayOptions']['index'] = $this->_sections['submitDayOptions']['start'], $this->_sections['submitDayOptions']['iteration'] = 1;
                 $this->_sections['submitDayOptions']['iteration'] <= $this->_sections['submitDayOptions']['total'];
                 $this->_sections['submitDayOptions']['index'] += $this->_sections['submitDayOptions']['step'], $this->_sections['submitDayOptions']['iteration']++):
$this->_sections['submitDayOptions']['rownum'] = $this->_sections['submitDayOptions']['iteration'];
$this->_sections['submitDayOptions']['index_prev'] = $this->_sections['submitDayOptions']['index'] - $this->_sections['submitDayOptions']['step'];
$this->_sections['submitDayOptions']['index_next'] = $this->_sections['submitDayOptions']['index'] + $this->_sections['submitDayOptions']['step'];
$this->_sections['submitDayOptions']['first']      = ($this->_sections['submitDayOptions']['iteration'] == 1);
$this->_sections['submitDayOptions']['last']       = ($this->_sections['submitDayOptions']['iteration'] == $this->_sections['submitDayOptions']['total']);
?>
				<option value="<?php echo $this->_sections['submitDayOptions']['index']; ?>
"<?php if ($this->_tpl_vars['numDaysBeforeSubmitReminder'] == $this->_sections['submitDayOptions']['index']): ?> selected="selected"<?php endif; ?>><?php echo $this->_sections['submitDayOptions']['index']; ?>
</option>
		<?php endfor; endif; ?>
		</select>
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.remindForSubmit2"), $this);?>
</p>
		<?php if (! $this->_tpl_vars['scheduledTasksEnabled']): ?>
	
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.automatedRemindersDisabled"), $this);?>
</p>
		<?php endif; ?>
	

<p>
	<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.reviewerRatings"), $this);?>
</strong><br/>
	<input type="checkbox" name="rateReviewerOnQuality" id="rateReviewerOnQuality" value="1"<?php if ($this->_tpl_vars['rateReviewerOnQuality']): ?> checked="checked"<?php endif; ?> />&nbsp;
	<label for="rateReviewerOnQuality"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.onQuality"), $this);?>
</label>
</p>

<p>
	<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.reviewerAccess"), $this);?>
</strong><br/>
	<input type="checkbox" name="reviewerAccessKeysEnabled" id="reviewerAccessKeysEnabled" value="1"<?php if ($this->_tpl_vars['reviewerAccessKeysEnabled']): ?> checked="checked"<?php endif; ?> />&nbsp;
	<label for="reviewerAccessKeysEnabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.reviewerAccessKeysEnabled"), $this);?>
</label><br/>
	<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.reviewerAccessKeysEnabled.description"), $this);?>
</span><br/>
	<input type="checkbox" name="restrictReviewerFileAccess" id="restrictReviewerFileAccess" value="1"<?php if ($this->_tpl_vars['restrictReviewerFileAccess']): ?> checked="checked"<?php endif; ?> />&nbsp;
	<label for="restrictReviewerFileAccess"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.restrictReviewerFileAccess"), $this);?>
</label>
</p>

<p>
	<strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.blindReview"), $this);?>
</strong><br/>
	<input type="checkbox" name="showEnsuringLink" id="showEnsuringLink" value="1"<?php if ($this->_tpl_vars['showEnsuringLink']): ?> checked="checked"<?php endif; ?> />&nbsp;
	<?php echo ((is_array($_tmp=$this->_plugins['function']['get_help_id'][0][0]->smartyGetHelpId(array('key' => "editorial.sectionEditorsRole.review.blindPeerReview",'url' => 'true'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'blindReviewHelpId') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'blindReviewHelpId'));?>

	<label for="showEnsuringLink"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewOptions.showEnsuringLink",'blindReviewHelpId' => $this->_tpl_vars['blindReviewHelpId']), $this);?>
</label><br/>
</p>
</div>
</div>
<div class="separator"></div>
<div id="privacyStatementInfo" class="block">
<h3>2.3 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.privacyStatement"), $this);?>
</h3>

<p><textarea name="privacyStatement[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="privacyStatement" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['privacyStatement'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></p>
</div>

<div class="separator"></div>

<div id="editorDecision" class="block">
<h3>2.4 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.editorDecision"), $this);?>
</h3>

<p><input type="checkbox" name="notifyAllAuthorsOnDecision" id="notifyAllAuthorsOnDecision" value="1"<?php if ($this->_tpl_vars['notifyAllAuthorsOnDecision']): ?> checked="checked"<?php endif; ?> /> <label for="notifyAllAuthorsOnDecision"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.notifyAllAuthorsOnDecision"), $this);?>
</label></p>
</div>
<div class="separator"></div>

<div id="addItemtoAboutJournal" class="block">
<h3>2.5 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.addItemtoAboutJournal"), $this);?>
</h3>

<table width="100%" class="data">
<?php $_from = $this->_tpl_vars['customAboutItems'][$this->_tpl_vars['formLocale']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['customAboutItems'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['customAboutItems']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['aboutId'] => $this->_tpl_vars['aboutItem']):
        $this->_foreach['customAboutItems']['iteration']++;
?>
	<tr valign="top">
		<td width="5%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "customAboutItems-".($this->_tpl_vars['aboutId'])."-title",'key' => "common.title"), $this);?>
</td>
		<td width="95%" class="value"><input type="text" name="customAboutItems[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][<?php echo ((is_array($_tmp=$this->_tpl_vars['aboutId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][title]" id="customAboutItems-<?php echo ((is_array($_tmp=$this->_tpl_vars['aboutId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['aboutItem']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /><?php if ($this->_foreach['customAboutItems']['total'] > 1): ?> <input type="submit" name="delCustomAboutItem[<?php echo ((is_array($_tmp=$this->_tpl_vars['aboutId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" /><?php endif; ?></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "customAboutItems-".($this->_tpl_vars['aboutId'])."-content",'key' => "manager.setup.aboutItemContent"), $this);?>
</td>
		<td width="80%" class="value"><textarea name="customAboutItems[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][<?php echo ((is_array($_tmp=$this->_tpl_vars['aboutId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][content]" id="customAboutItems-<?php echo ((is_array($_tmp=$this->_tpl_vars['aboutId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-content" rows="12" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['aboutItem']['content'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
	</tr>
	<?php if (! ($this->_foreach['customAboutItems']['iteration'] == $this->_foreach['customAboutItems']['total'])): ?>
	<tr valign="top">
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php endforeach; else: ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "customAboutItems-0-title",'key' => "common.title"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="customAboutItems[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][0][title]" id="customAboutItems-0-title" value="" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "customAboutItems-0-content",'key' => "manager.setup.aboutItemContent"), $this);?>
</td>
		<td width="80%" class="value"><textarea name="customAboutItems[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][0][content]" id="customAboutItems-0-content" rows="12" cols="40" class="textArea"></textarea></td>
	</tr>
<?php endif; unset($_from); ?>
</table>

<p><input type="submit" name="addCustomAboutItem" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.addAboutItem"), $this);?>
" class="button" /></p>
</div>
<div class="separator"></div>

<div id="journalArchiving" class="block">
<h3>2.6 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.journalArchiving"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.preservationDescription"), $this);?>
</p>

<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Manager::Setup::JournalArchiving"), $this);?>


<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.lockssDescription",'lockssExistingArchiveUrl' => $this->_tpl_vars['lockssExistingArchiveUrl'],'lockssNewArchiveUrl' => $this->_tpl_vars['lockssNewArchiveUrl']), $this);?>
</p>

<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gateway','op' => 'lockss'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'lockssUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'lockssUrl'));?>

<p><input type="checkbox" name="enableLockss" id="enableLockss" value="1"<?php if ($this->_tpl_vars['enableLockss']): ?> checked="checked"<?php endif; ?> /> <label for="enableLockss"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.lockssEnable",'lockssUrl' => $this->_tpl_vars['lockssUrl']), $this);?>
</label></p>

<p>
	<textarea name="lockssLicense[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="lockssLicense" rows="6" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['lockssLicense'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
	<br />
	<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.lockssLicenses"), $this);?>
</span>
</p>
</div>

<div class="separator"></div>

<div id="reviewerDatabaseLink" class="block">
<h3>2.7 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewerDatabaseLink"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.reviewerDatabaseLink.desc"), $this);?>
</p>

<table width="100%" class="data">
<?php $_from = $this->_tpl_vars['reviewerDatabaseLinks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['reviewerDatabaseLinks'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['reviewerDatabaseLinks']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['reviewerDatabaseLinkId'] => $this->_tpl_vars['reviewerDatabaseLink']):
        $this->_foreach['reviewerDatabaseLinks']['iteration']++;
?>
	<tr valign="top">
		<td width="5%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "reviewerDatabaseLinks-".($this->_tpl_vars['reviewerDatabaseLinkId'])."-title",'key' => "common.title"), $this);?>
</td>
		<td width="95%" class="value"><input type="text" name="reviewerDatabaseLinks[<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLinkId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][title]" id="reviewerDatabaseLinks-<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLinkId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLink']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /><?php if ($this->_foreach['reviewerDatabaseLinks']['total'] > 1): ?> <input type="submit" name="delReviewerDatabaseLink[<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLinkId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" /><?php endif; ?></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "reviewerDatabaseLinks-".($this->_tpl_vars['reviewerDatabaseLinkId'])."-url",'key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="reviewerDatabaseLinks[<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLinkId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][url]" id="reviewerDatabaseLinks-<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLinkId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-url" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerDatabaseLink']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<?php if (! ($this->_foreach['reviewerDatabaseLinks']['iteration'] == $this->_foreach['reviewerDatabaseLinks']['total'])): ?>
	<tr valign="top">
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php endforeach; else: ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "reviewerDatabaseLinks-0-title",'key' => "common.title"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="reviewerDatabaseLinks[0][title]" id="reviewerDatabaseLinks-0-title" value="" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "reviewerDatabaseLinks-0-url",'key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="reviewerDatabaseLinks[0][url]" id="reviewerDatabaseLinks-0-url" value="" size="40" maxlength="255" class="textField" /></td>
	</tr>
<?php endif; unset($_from); ?>
</table>

<p><input type="submit" name="addReviewerDatabaseLink" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.addReviewerDatabaseLink"), $this);?>
" class="button" /></p>
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
