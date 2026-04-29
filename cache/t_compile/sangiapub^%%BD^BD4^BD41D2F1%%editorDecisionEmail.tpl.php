<?php /* Smarty version 2.6.26, created on 2026-04-28 04:23:26
         compiled from submission/comment/editorDecisionEmail.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'submission/comment/editorDecisionEmail.tpl', 31, false),array('modifier', 'assign', 'submission/comment/editorDecisionEmail.tpl', 56, false),array('function', 'translate', 'submission/comment/editorDecisionEmail.tpl', 52, false),array('function', 'fieldLabel', 'submission/comment/editorDecisionEmail.tpl', 69, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "email.compose"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "email.email"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
<!--
function deleteAttachment(fileId) {
	document.getElementById(\'emailForm\').deleteAttachment.value = fileId;
	document.getElementById(\'emailForm\').submit();
}
// -->
'; ?>

</script>
<div id="editorDecisionEmail">
<form method="post" id="emailForm" name="emailForm" action="<?php echo $this->_tpl_vars['formActionUrl']; ?>
"<?php if ($this->_tpl_vars['attachmentsEnabled']): ?> enctype="multipart/form-data"<?php endif; ?>>

<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
<input type="hidden" name="continued" value="1"/>
<?php if ($this->_tpl_vars['hiddenFormParams']): ?>
	<?php $_from = $this->_tpl_vars['hiddenFormParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['hiddenFormParam']):
?>
		<input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['hiddenFormParam'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['attachmentsEnabled']): ?>
	<input type="hidden" name="deleteAttachment" value="" />
	<?php $_from = $this->_tpl_vars['persistAttachments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['temporaryFile']):
?>
		<input type="hidden" name="persistAttachments[]" value="<?php echo $this->_tpl_vars['temporaryFile']->getId(); ?>
" />
	<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php $_from = $this->_tpl_vars['errorMessages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['message']):
?>
	<?php if (! $this->_tpl_vars['notFirstMessage']): ?>
		<?php $this->assign('notFirstMessage', 1); ?>
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
</h4>
		<ul class="plain">
	<?php endif; ?>
	<?php if ($this->_tpl_vars['message']['type'] == MAIL_ERROR_INVALID_EMAIL): ?>
		<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.invalid",'email' => $this->_tpl_vars['message']['address']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'message') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'message'));?>

		<li><?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<?php if ($this->_tpl_vars['notFirstMessage']): ?>
	</ul>
	<br/>
<?php endif; ?>

<table class="data" width="100%">
<?php if ($this->_tpl_vars['addressFieldsEnabled']): ?>
<tr valign="top">
	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'to','key' => "email.to"), $this);?>
</td>
	<td width="80%" class="value">
		<?php $_from = $this->_tpl_vars['to']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['toAddress']):
?>
			<input type="text" name="to[]" id="to" value="<?php if ($this->_tpl_vars['toAddress']['name'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['toAddress']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &lt;<?php echo ((is_array($_tmp=$this->_tpl_vars['toAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&gt;<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['toAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" size="40" maxlength="120" class="textField" /><br/>
		<?php endforeach; else: ?>
			<input type="text" name="to[]" id="to" size="40" maxlength="120" class="textField" />
		<?php endif; unset($_from); ?>

		<?php if ($this->_tpl_vars['blankTo']): ?>
			<input type="text" name="to[]" id="to" size="40" maxlength="120" class="textField" />
		<?php endif; ?>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'cc','key' => "email.cc"), $this);?>
</td>
	<td class="value">
		<?php $_from = $this->_tpl_vars['cc']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ccAddress']):
?>
			<input type="text" name="cc[]" id="cc" value="<?php if ($this->_tpl_vars['ccAddress']['name'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['ccAddress']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &lt;<?php echo ((is_array($_tmp=$this->_tpl_vars['ccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&gt;<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['ccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" size="40" maxlength="120" class="textField" /><br/>
		<?php endforeach; else: ?>
			<input type="text" name="cc[]" id="cc" size="40" maxlength="120" class="textField" />
		<?php endif; unset($_from); ?>

		<?php if ($this->_tpl_vars['blankCc']): ?>
			<input type="text" name="cc[]" id="cc" size="40" maxlength="120" class="textField" />
		<?php endif; ?>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'bcc','key' => "email.bcc"), $this);?>
</td>
	<td class="value">
		<?php $_from = $this->_tpl_vars['bcc']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bccAddress']):
?>
			<input type="text" name="bcc[]" id="bcc" value="<?php if ($this->_tpl_vars['bccAddress']['name'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['bccAddress']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &lt;<?php echo ((is_array($_tmp=$this->_tpl_vars['bccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&gt;<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['bccAddress']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" size="40" maxlength="120" class="textField" /><br/>
		<?php endforeach; else: ?>
			<input type="text" name="bcc[]" id="bcc" size="40" maxlength="120" class="textField" />
		<?php endif; unset($_from); ?>

		<?php if ($this->_tpl_vars['blankBcc']): ?>
			<input type="text" name="bcc[]" id="bcc" size="40" maxlength="120" class="textField" />
		<?php endif; ?>
	</td>
</tr>
<tr valign="top">
	<td></td>
	<td class="value">
		<input type="submit" name="blankTo" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.addToRecipient"), $this);?>
"/>
		<input type="submit" name="blankCc" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.addCcRecipient"), $this);?>
"/>
		<input type="submit" name="blankBcc" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.addBccRecipient"), $this);?>
"/>
		<?php if ($this->_tpl_vars['senderEmail']): ?>
			<br/>
			<input type="checkbox" name="bccSender" id="bccSender" value="1"<?php if ($this->_tpl_vars['bccSender']): ?> checked<?php endif; ?> />&nbsp;&nbsp;<label for="bccSender"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.bccSender",'address' => ((is_array($_tmp=$this->_tpl_vars['senderEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
</label>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['attachmentsEnabled']): ?>
<tr valign="top">
	<td colspan="2">&nbsp;</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.attachments"), $this);?>
</td>
	<td class="value">
		<?php $this->assign('attachmentNum', 1); ?>
		<?php $_from = $this->_tpl_vars['persistAttachments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['temporaryFile']):
?>
			<?php echo ((is_array($_tmp=$this->_tpl_vars['attachmentNum'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
.&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['temporaryFile']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&nbsp;
			(<?php echo $this->_tpl_vars['temporaryFile']->getNiceFileSize(); ?>
)&nbsp;
			<a href="javascript:deleteAttachment(<?php echo $this->_tpl_vars['temporaryFile']->getId(); ?>
)" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a>
			<br/>
			<?php $this->assign('attachmentNum', $this->_tpl_vars['attachmentNum']+1); ?>
		<?php endforeach; endif; unset($_from); ?>

		<?php if ($this->_tpl_vars['attachmentNum'] != 1): ?><br/><?php endif; ?>

		<input type="file" name="newAttachment" class="uploadField" /> <input name="addAttachment" type="submit" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" />
	</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['isAnEditor']): ?>
	<tr valign="top">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">&nbsp;</td>
		<td class="value">
			<input type="submit" name="importPeerReviews" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.comments.importPeerReviews"), $this);?>
"/>
		</td>
	</tr>
<?php endif; ?>
<tr valign="top">
	<td colspan="2">&nbsp;</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.from"), $this);?>
</td>
	<td class="value"><?php echo ((is_array($_tmp=$this->_tpl_vars['from'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
</tr>
<tr valign="top">
	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'subject','key' => "email.subject"), $this);?>
</td>
	<td width="80%" class="value"><input type="text" id="subject" name="subject" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['subject'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="90" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'body','key' => "email.body"), $this);?>
</td>
	<td class="value"><textarea name="body" cols="60" rows="15" class="textArea markdown-editor" id="emailBody" data-markdown-editor="true"><?php echo ((is_array($_tmp=$this->_tpl_vars['body'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
</tr>

<?php if ($this->_tpl_vars['isAnEditor']): ?>
	<td class="label">&nbsp;</td>
	<td class="value"><input type="checkbox" name="blindCcReviewers" value="1" id="blindCcReviewers"/>&nbsp;&nbsp;<label for="blindCcReviewers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.comments.blindCcReviewers"), $this);?>
</label></td>
<?php endif; ?>
</table>

<p><input name="send" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.send"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="history.go(-1)" /><?php if (! $this->_tpl_vars['disableSkipButton']): ?> <input name="send[skip]" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.skip"), $this);?>
" class="button" /><?php endif; ?></p>
</form>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-profile.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
