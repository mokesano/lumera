<?php /* Smarty version 2.6.26, created on 2026-04-16 16:32:50
         compiled from author/submit/step1.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'author/submit/step1.tpl', 20, false),array('function', 'url', 'author/submit/step1.tpl', 24, false),array('function', 'fieldLabel', 'author/submit/step1.tpl', 56, false),array('function', 'html_options', 'author/submit/step1.tpl', 57, false),array('modifier', 'escape', 'author/submit/step1.tpl', 27, false),array('modifier', 'assign', 'author/submit/step1.tpl', 49, false),array('modifier', 'nl2br', 'author/submit/step1.tpl', 130, false),)), $this); ?>
<?php $this->assign('pageTitle', "author.submit.step1"); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "author/submit/submitHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['journalSettings']['supportPhone']): ?>
	<?php $this->assign('howToKeyName', "author.submit.howToSubmit"); ?>
<?php else: ?>
	<?php $this->assign('howToKeyName', "author.submit.howToSubmitNoPhone"); ?>
<?php endif; ?>

<p class="info-message u-mb-16"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['howToKeyName'],'supportName' => $this->_tpl_vars['journalSettings']['supportName'],'supportEmail' => $this->_tpl_vars['journalSettings']['supportEmail'],'supportPhone' => $this->_tpl_vars['journalSettings']['supportPhone']), $this);?>
</p>

<div class="separator"></div>

<form id="submit" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveSubmit','path' => $this->_tpl_vars['submitStep']), $this);?>
" onsubmit="return checkSubmissionChecklist()">
    
        <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['articleId']): ?>
<input type="hidden" name="articleId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /><?php endif; ?>

<?php if (count ( $this->_tpl_vars['sectionOptions'] ) <= 1): ?>
	<p class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.notAccepting"), $this);?>
</p>
<?php else: ?>

<?php if (count ( $this->_tpl_vars['sectionOptions'] ) == 2): ?>
		<?php $_from = $this->_tpl_vars['sectionOptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['val']):
?>
		<input type="hidden" name="sectionId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endforeach; endif; unset($_from); ?>
<?php else: ?><div id="section" class="block">

    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.journalSection"), $this);?>
</h3>
    
    <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

    <p class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.journalSectionDescription",'aboutUrl' => $this->_tpl_vars['url']), $this);?>
</p>
    
    <input type="hidden" name="submissionChecklist" value="1" />
    
    <table class="data" width="100%">
    	<tr valign="top">
    		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'sectionId','required' => 'true','key' => "section.section"), $this);?>
</td>
    		<td width="80%" class="value"><select name="sectionId" id="sectionId" size="1" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['sectionOptions'],'selected' => $this->_tpl_vars['sectionId']), $this);?>
</select></td>
    	</tr>
    </table>

</div>
<div class="separator"></div>

<?php endif; ?>
<?php if (count ( $this->_tpl_vars['supportedSubmissionLocaleNames'] ) == 1): ?>
		<?php $_from = $this->_tpl_vars['supportedSubmissionLocaleNames']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['locale'] => $this->_tpl_vars['localeName']):
?>
		<input type="hidden" name="locale" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['locale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endforeach; endif; unset($_from); ?>
<?php else: ?>
		<div id="submissionLocale" >

    	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionLocale"), $this);?>
</h3>
    	<p class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionLocaleDescription"), $this);?>
</p>
    
    	<table class="data" width="100%">
    		<tr valign="top">
    			<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'locale','required' => 'true','key' => "article.language"), $this);?>
</td>
    			<td width="80%" class="value"><select name="locale" id="locale" size="1" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['supportedSubmissionLocaleNames'],'selected' => $this->_tpl_vars['locale']), $this);?>
</select></td>
    		</tr>
    	</table>
    
    	<div class="separator"></div>

	</div><?php endif; ?>
<script type="text/javascript">
<?php echo '
<!--
function checkSubmissionChecklist() {
	var elements = document.getElementById(\'submit\').elements;
	for (var i=0; i < elements.length; i++) {
		if (elements[i].type == \'checkbox\' && !elements[i].checked) {
			if (elements[i].name.match(\'^checklist\')) {
				alert('; ?>
'<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.verifyChecklist"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
'<?php echo ');
				return false;
			} else if (elements[i].name == \'copyrightNoticeAgree\') {
				alert('; ?>
'<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.copyrightNoticeAgreeRequired"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
'<?php echo ');
				return false;
			}
		}
	}
	return true;
}
// -->
'; ?>

</script>

<?php if ($this->_tpl_vars['authorFees']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "author/submit/authorFees.tpl", 'smarty_include_vars' => array('showPayLinks' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<div class="separator"></div>
<?php endif; ?>

<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('submissionChecklist')): ?>
<?php $_from = $this->_tpl_vars['currentJournal']->getLocalizedSetting('submissionChecklist'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['checklist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['checklist']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['checklistId'] => $this->_tpl_vars['checklistItem']):
        $this->_foreach['checklist']['iteration']++;
?>
	<?php if ($this->_tpl_vars['checklistItem']['content']): ?>
		<?php if (! $this->_tpl_vars['notFirstChecklistItem']): ?>
			<?php $this->assign('notFirstChecklistItem', 1); ?>
			<div id="checklist" class="block">
			<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionChecklist"), $this);?>
</h3>
			<p class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionChecklistDescription"), $this);?>
</p>
			<table width="100%" class="alt-color sort checklist">
		<?php endif; ?>
		<tr valign="top">
			<td width="5%"><input type="checkbox" id="checklist-<?php echo $this->_foreach['checklist']['iteration']; ?>
" name="checklist[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['checklistId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['articleId'] || $this->_tpl_vars['submissionChecklist']): ?> checked="checked"<?php endif; ?> /></td>
			<td width="95%"><label for="checklist-<?php echo $this->_foreach['checklist']['iteration']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['checklistItem']['content'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</label></td>
		</tr>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
<?php if ($this->_tpl_vars['notFirstChecklistItem']): ?>
	</table>
	</div>	<div class="separator"></div>
<?php endif; ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('copyrightNotice') != ''): ?>
<div id="copyrightNotice" class="block">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</h3>
    
    <div class="alert-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('copyrightNotice'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
    
    <?php if ($this->_tpl_vars['journalSettings']['copyrightNoticeAgree']): ?>
    <table width="100%" class="data">
    	<tr valign="top">
    		<td width="5%"><input type="checkbox" name="copyrightNoticeAgree" id="copyrightNoticeAgree" value="1"<?php if ($this->_tpl_vars['articleId'] || $this->_tpl_vars['copyrightNoticeAgree']): ?> checked="checked"<?php endif; ?> /></td>
    		<td width="95%"><label for="copyrightNoticeAgree"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.copyrightNoticeAgree"), $this);?>
</label></td>
    	</tr>
    </table>
    <?php endif; ?></div>
<div class="separator"></div>
<?php endif; ?>
<div id="privacyStatement" class="block">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.privacyStatement"), $this);?>
</h3>
    <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('privacyStatement'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

</div>

<div class="separator"></div>

<div id="commentsForEditor" class="block">
    <h4 class="u-h3"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.commentsForEditor"), $this);?>
</h4>
    
    <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'commentsToEditor','key' => "author.submit.comments"), $this);?>

    <textarea name="commentsToEditor" id="commentsToEditor" rows="17" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['commentsToEditor'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
</div>
<div class="separator"></div>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.saveAndContinue"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="<?php if ($this->_tpl_vars['articleId']): ?>confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author'), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.cancelSubmission"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')<?php else: ?>document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','escape' => false), $this);?>
'<?php endif; ?>" /></p>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

</form>

<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
