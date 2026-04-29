<?php /* Smarty version 2.6.26, created on 2026-04-14 11:12:46
         compiled from sectionEditor/submission/editorDecision.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'sectionEditor/submission/editorDecision.tpl', 12, false),array('function', 'url', 'sectionEditor/submission/editorDecision.tpl', 18, false),array('function', 'html_options_translate', 'sectionEditor/submission/editorDecision.tpl', 21, false),array('function', 'icon', 'sectionEditor/submission/editorDecision.tpl', 48, false),array('modifier', 'escape', 'sectionEditor/submission/editorDecision.tpl', 23, false),array('modifier', 'date_format', 'sectionEditor/submission/editorDecision.tpl', 34, false),array('modifier', 'assign', 'sectionEditor/submission/editorDecision.tpl', 43, false),array('modifier', 'to_array', 'sectionEditor/submission/editorDecision.tpl', 95, false),array('modifier', 'count', 'sectionEditor/submission/editorDecision.tpl', 108, false),)), $this); ?>
<div id="editorDecision" class="block">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editorDecision"), $this);?>
</h3>

<table id="table1" width="100%" class="data">
<tr valign="top">
	<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.selectDecision"), $this);?>
</td>
	<td width="80%" class="value">
		<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'recordDecision'), $this);?>
">
			<input type="hidden" name="articleId" value="<?php echo $this->_tpl_vars['submission']->getId(); ?>
" />
			<select name="decision" size="1" class="selectMenu"<?php if (! $this->_tpl_vars['allowRecommendation']): ?> disabled="disabled"<?php endif; ?>>
				<?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['editorDecisionOptions'],'selected' => $this->_tpl_vars['lastDecision']), $this);?>

			</select>
			<input type="submit" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.submissionReview.confirmDecision"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" name="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.recordDecision"), $this);?>
" <?php if (! $this->_tpl_vars['allowRecommendation']): ?>disabled="disabled"<?php endif; ?> class="button" />
			<?php if (! $this->_tpl_vars['allowRecommendation']): ?>&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.cannotRecord"), $this);?>
<?php endif; ?>
		</form>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.decision"), $this);?>
</td>
	<td class="value">
		<?php $_from = $this->_tpl_vars['submission']->getDecisions($this->_tpl_vars['round']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['decisionKey'] => $this->_tpl_vars['editorDecision']):
?>
			<?php if ($this->_tpl_vars['decisionKey'] != 0): ?> | <?php endif; ?>
			<?php $this->assign('decision', $this->_tpl_vars['editorDecision']['decision']); ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['editorDecisionOptions'][$this->_tpl_vars['decision']]), $this);?>
&nbsp;&nbsp;<?php if ($this->_tpl_vars['editorDecision']['dateDecided'] != 0): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['editorDecision']['dateDecided'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
<?php endif; ?>
		<?php endforeach; else: ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>

		<?php endif; unset($_from); ?>
	</td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notifyAuthor"), $this);?>
</td>
	<td class="value">
		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'emailEditorDecisionComment','articleId' => $this->_tpl_vars['submission']->getId()), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'notifyAuthorUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'notifyAuthorUrl'));?>


		<?php if ($this->_tpl_vars['decision'] == SUBMISSION_EDITOR_DECISION_DECLINE): ?>
						<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.submissionReview.emailWillArchive"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'quotes')))) ? $this->_run_mod_handler('assign', true, $_tmp, 'confirmString') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'confirmString'));?>

			<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['notifyAuthorUrl'],'onclick' => "return confirm('".($this->_tpl_vars['confirmString'])."')"), $this);?>

		<?php else: ?>
			<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['notifyAuthorUrl']), $this);?>

		<?php endif; ?>

		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editorAuthorRecord"), $this);?>

		<?php if ($this->_tpl_vars['submission']->getMostRecentEditorDecisionComment()): ?>
			<?php $this->assign('comment', $this->_tpl_vars['submission']->getMostRecentEditorDecisionComment()); ?>
			<a href="javascript:openComments('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewEditorDecisionComments','path' => $this->_tpl_vars['submission']->getId(),'anchor' => $this->_tpl_vars['comment']->getId()), $this);?>
');" class="icon"><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'comment'), $this);?>
</a>&nbsp;&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['comment']->getDatePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>

		<?php else: ?>
			<a href="javascript:openComments('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewEditorDecisionComments','path' => $this->_tpl_vars['submission']->getId()), $this);?>
');" class="icon"><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'comment'), $this);?>
</a><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.noComments"), $this);?>

		<?php endif; ?>
	</td>
</tr>
</table>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorReview'), $this);?>
" enctype="multipart/form-data">

<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
<input type="hidden" name="articleId" value="<?php echo $this->_tpl_vars['submission']->getId(); ?>
" />
<?php $this->assign('authorFiles', $this->_tpl_vars['submission']->getAuthorFileRevisions($this->_tpl_vars['round'])); ?>
<?php $this->assign('editorFiles', $this->_tpl_vars['submission']->getEditorFileRevisions($this->_tpl_vars['round'])); ?>

<?php $this->assign('authorRevisionExists', false); ?>
<?php $_from = $this->_tpl_vars['authorFiles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['authorFile']):
?>
	<?php $this->assign('authorRevisionExists', true); ?>
<?php endforeach; endif; unset($_from); ?>

<?php $this->assign('editorRevisionExists', false); ?>
<?php $_from = $this->_tpl_vars['editorFiles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editorFile']):
?>
	<?php $this->assign('editorRevisionExists', true); ?>
<?php endforeach; endif; unset($_from); ?>
<?php if ($this->_tpl_vars['reviewFile']): ?>
	<?php $this->assign('reviewVersionExists', 1); ?>
<?php endif; ?>

<table id="table2" class="data table-borders" width="100%">
	<?php if ($this->_tpl_vars['reviewFile']): ?>
		<tr valign="top">
			<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.reviewVersion"), $this);?>
</td>
			<td width="50%" class="value">
				<?php if ($this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_ACCEPT || $this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_RESUBMIT): ?>
					<input type="radio" name="editorDecisionFile" value="<?php echo $this->_tpl_vars['reviewFile']->getFileId(); ?>
,<?php echo $this->_tpl_vars['reviewFile']->getRevision(); ?>
" />
				<?php endif; ?>
				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'downloadFile','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['reviewFile']->getFileId(), $this->_tpl_vars['reviewFile']->getRevision()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['reviewFile']->getFileId(), $this->_tpl_vars['reviewFile']->getRevision()))), $this);?>
" class="file"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewFile']->getFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>&nbsp;&nbsp;
				<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewFile']->getDateModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>

				<?php if ($this->_tpl_vars['copyeditFile'] && $this->_tpl_vars['copyeditFile']->getSourceFileId() == $this->_tpl_vars['reviewFile']->getFileId()): ?>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.sent"), $this);?>
&nbsp;&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['copyeditFile']->getDateUploaded())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>

				<?php endif; ?>
			</td>
		</tr>
	<?php endif; ?>
	<?php $this->assign('firstItem', true); ?>
	<?php $_from = $this->_tpl_vars['authorFiles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['authorFile']):
?>
		<tr valign="top">
			<?php if ($this->_tpl_vars['firstItem']): ?>
				<?php $this->assign('firstItem', false); ?>
				<td width="20%" rowspan="<?php echo count($this->_tpl_vars['authorFiles']); ?>
" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.authorVersion"), $this);?>
</td>
			<?php endif; ?>
			<td width="80%" class="value">
				<?php if ($this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_ACCEPT || $this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_RESUBMIT): ?><input type="radio" name="editorDecisionFile" value="<?php echo $this->_tpl_vars['authorFile']->getFileId(); ?>
,<?php echo $this->_tpl_vars['authorFile']->getRevision(); ?>
" /> <?php endif; ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'downloadFile','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['authorFile']->getFileId(), $this->_tpl_vars['authorFile']->getRevision()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['authorFile']->getFileId(), $this->_tpl_vars['authorFile']->getRevision()))), $this);?>
" class="file"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorFile']->getFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>&nbsp;&nbsp;
				<?php echo ((is_array($_tmp=$this->_tpl_vars['authorFile']->getDateModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>

				<?php if ($this->_tpl_vars['copyeditFile'] && ( $this->_tpl_vars['copyeditFile']->getSourceFileId() == $this->_tpl_vars['authorFile']->getFileId() && $this->_tpl_vars['copyeditFile']->getSourceRevision() == $this->_tpl_vars['authorFile']->getRevision() )): ?>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.sent"), $this);?>
&nbsp;&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['copyeditFile']->getDateUploaded())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>

				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; else: ?>
		<tr valign="top">
			<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.authorVersion"), $this);?>
</td>
			<td width="80%" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
		</tr>
	<?php endif; unset($_from); ?>
	<?php $this->assign('firstItem', true); ?>
	<?php $_from = $this->_tpl_vars['editorFiles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['editorFile']):
?>
		<tr valign="top">
			<?php if ($this->_tpl_vars['firstItem']): ?>
				<?php $this->assign('firstItem', false); ?>
				<td width="20%" rowspan="<?php echo count($this->_tpl_vars['editorFiles']); ?>
" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editorVersion"), $this);?>
</td>
			<?php endif; ?>
			<td width="80%" class="value">
				<?php if ($this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_ACCEPT || $this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_RESUBMIT): ?><input type="radio" name="editorDecisionFile" value="<?php echo $this->_tpl_vars['editorFile']->getFileId(); ?>
,<?php echo $this->_tpl_vars['editorFile']->getRevision(); ?>
" /> <?php endif; ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'downloadFile','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['editorFile']->getFileId(), $this->_tpl_vars['editorFile']->getRevision()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['editorFile']->getFileId(), $this->_tpl_vars['editorFile']->getRevision()))), $this);?>
" class="file"><?php echo ((is_array($_tmp=$this->_tpl_vars['editorFile']->getFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>&nbsp;&nbsp;
				<?php echo ((is_array($_tmp=$this->_tpl_vars['editorFile']->getDateModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
&nbsp;&nbsp;&nbsp;&nbsp;
				<?php if ($this->_tpl_vars['copyeditFile'] && ( $this->_tpl_vars['copyeditFile']->getSourceFileId() == $this->_tpl_vars['editorFile']->getFileId() && $this->_tpl_vars['copyeditFile']->getSourceRevision() == $this->_tpl_vars['editorFile']->getRevision() )): ?>
					<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.sent"), $this);?>
&nbsp;&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['copyeditFile']->getDateUploaded())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
&nbsp;&nbsp;&nbsp;&nbsp;
				<?php endif; ?>
				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'deleteArticleFile','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['editorFile']->getFileId(), $this->_tpl_vars['editorFile']->getRevision()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['editorFile']->getFileId(), $this->_tpl_vars['editorFile']->getRevision()))), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a>
			</td>
		</tr>
	<?php endforeach; else: ?>
		<tr valign="top">
			<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editorVersion"), $this);?>
</td>
			<td width="80%" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
		</tr>
	<?php endif; unset($_from); ?>
	<tr valign="top">
		<td class="label">&nbsp;</td>
		<td class="value">
			<input type="file" name="upload" class="uploadField" />
			<input type="submit" name="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" class="button" />
		</td>
	</tr>
	<?php if ($this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_RESUBMIT): ?>
			<td><input type="submit" name="resubmit" <?php if (! ( $this->_tpl_vars['editorRevisionExists'] || $this->_tpl_vars['authorRevisionExists'] || $this->_tpl_vars['reviewVersionExists'] )): ?>disabled="disabled" <?php endif; ?>value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.resubmit"), $this);?>
" class="button" /></td>
			<td>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.resubmitFileForPeerReview"), $this);?>
			
			</td>
		</tr>
	<?php elseif ($this->_tpl_vars['lastDecision'] == SUBMISSION_EDITOR_DECISION_ACCEPT): ?>
		<tr valign="top">
			<td width="20%">
				<input type="submit" <?php if ($this->_tpl_vars['copyeditingUnavailable']): ?>disabled="disabled" <?php endif; ?>name="setCopyeditFile" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.submissionReview.sendToCopyediting"), $this);?>
" class="button" />
				<?php if ($this->_tpl_vars['copyeditingUnavailable']): ?>
					<br/>
					<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.submissionReview.cannotSendToCopyediting"), $this);?>
</span>
				<?php endif; ?>
			</td>
			<td width="80%">
				<?php if (! ( $this->_tpl_vars['editorRevisionExists'] || $this->_tpl_vars['authorRevisionExists'] || $this->_tpl_vars['reviewVersionExists'] ) || ! $this->_tpl_vars['submission']->getMostRecentEditorDecisionComment()): ?><?php $this->assign('copyeditingUnavailable', 1); ?><?php else: ?><?php $this->assign('copyeditingUnavailable', 0); ?><?php endif; ?>
				
			</td>
		</tr>
	<?php endif; ?>
</table>

</form>
</div>
