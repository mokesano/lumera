<?php /* Smarty version 2.6.26, created on 2026-04-27 11:28:42
         compiled from author/submit/step5.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'author/submit/step5.tpl', 14, false),array('function', 'url', 'author/submit/step5.tpl', 16, false),array('function', 'call_hook', 'author/submit/step5.tpl', 87, false),array('modifier', 'escape', 'author/submit/step5.tpl', 19, false),array('modifier', 'to_array', 'author/submit/step5.tpl', 42, false),array('modifier', 'date_format', 'author/submit/step5.tpl', 45, false),)), $this); ?>
<?php $this->assign('pageTitle', "author.submit.step5"); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "author/submit/submitHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.confirmationDescription",'journalTitle' => $this->_tpl_vars['journal']->getLocalizedTitle()), $this);?>
</div>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveSubmit','path' => $this->_tpl_vars['submitStep']), $this);?>
">

<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
<input type="hidden" name="articleId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.filesSummary"), $this);?>
</h3>
<table class="listing" width="100%">
<tr>
	<td colspan="5" class="headseparator">&nbsp;</td>
</tr>
<tr class="heading" valign="bottom">
	<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.id"), $this);?>
</td>
	<td width="55%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.originalFileName"), $this);?>
</td>
	<td width="17%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.type"), $this);?>
</td>
	<td width="10%" class="nowrap"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fileSize"), $this);?>
</td>
	<td width="8%" class="nowrap"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.dateUploaded"), $this);?>
</td>
</tr>
<tr>
	<td colspan="5" class="headseparator">&nbsp;</td>
</tr>
<?php $_from = $this->_tpl_vars['files']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>
<tr valign="top">
	<td><?php echo $this->_tpl_vars['file']->getFileId(); ?>
</td>
	<td><a class="file" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'download','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['file']->getFileId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['file']->getFileId()))), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['file']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></td>
	<td><?php if (( $this->_tpl_vars['file']->getFileStage() == ARTICLE_FILE_SUPP )): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.suppFile"), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.submissionFile"), $this);?>
<?php endif; ?></td>
	<td><?php echo $this->_tpl_vars['file']->getNiceFileSize(); ?>
</td>
	<td><?php echo ((is_array($_tmp=$this->_tpl_vars['file']->getDateUploaded())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatTrunc']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatTrunc'])); ?>
</td>
</tr>
<?php endforeach; else: ?>
<tr valign="top">
<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.noFiles"), $this);?>
</td>
</tr>
<?php endif; unset($_from); ?>
</table>

<div class="separator"></div>

<?php if ($this->_tpl_vars['authorFees']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "author/submit/authorFees.tpl", 'smarty_include_vars' => array('showPayLinks' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('waiverPolicy') != ''): ?>
		<?php if ($this->_tpl_vars['manualPayment']): ?>
			<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.alreadyPaid"), $this);?>
</h3>
			<table class="data" width="100%">
				<tr valign="top">
				<td width="5%" align="left"><input type="checkbox" name="paymentSent" value="1" <?php if ($this->_tpl_vars['paymentSent']): ?>checked="checked"<?php endif; ?> /></td>
				<td width="95%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paymentSent"), $this);?>
</td>
				</tr>
				<tr>
				<td />
				<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.alreadyPaidMessage"), $this);?>
</td>
				<tr>
			</table>
		<?php endif; ?>
		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.requestWaiver"), $this);?>
</h3>
		<table class="data" width="100%">
			<tr valign="top">
				<td width="5%" align="left"><input type="checkbox" name="qualifyForWaiver" value="1" <?php if ($this->_tpl_vars['qualifyForWaiver']): ?>checked="checked"<?php endif; ?>/></td>
				<td width="95%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.qualifyForWaiver"), $this);?>
</td>
			</tr>
		</table>
		<br />
		<label for="commentsToEditor"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.addReasonsForWaiver"), $this);?>
</label><br />
		<textarea name="commentsToEditor" id="commentsToEditor" rows="7" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['commentsToEditor'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
	<?php endif; ?>

	<div class="separator"></div>
<?php endif; ?>

<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Author::Submit::Step5::AdditionalItems"), $this);?>


<br />
<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.finishSubmission"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author'), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.cancelSubmission"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" /></p>

</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
