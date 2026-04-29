<?php /* Smarty version 2.6.26, created on 2026-04-15 01:28:44
         compiled from sectionEditor/submission/editors.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'sectionEditor/submission/editors.tpl', 12, false),array('function', 'url', 'sectionEditor/submission/editors.tpl', 13, false),array('function', 'icon', 'sectionEditor/submission/editors.tpl', 34, false),array('modifier', 'concat', 'sectionEditor/submission/editors.tpl', 32, false),array('modifier', 'to_array', 'sectionEditor/submission/editors.tpl', 33, false),array('modifier', 'strip_tags', 'sectionEditor/submission/editors.tpl', 33, false),array('modifier', 'assign', 'sectionEditor/submission/editors.tpl', 33, false),array('modifier', 'escape', 'sectionEditor/submission/editors.tpl', 34, false),array('modifier', 'date_format', 'sectionEditor/submission/editors.tpl', 62, false),)), $this); ?>
<div id="editors" class="block">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editors"), $this);?>
</h3>
<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'setEditorFlags'), $this);?>
" method="post">
<input type="hidden" name="articleId" value="<?php echo $this->_tpl_vars['submission']->getId(); ?>
"/>
<table width="100%" class="listing sort block">
	<tr class="heading" valign="bottom">
		<td width="<?php if ($this->_tpl_vars['isEditor']): ?>20%<?php else: ?>25%<?php endif; ?>">&nbsp;</td>
		<td width="30%">&nbsp;</td>
		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.review"), $this);?>
</td>
		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editing"), $this);?>
</td>
		<td width="<?php if ($this->_tpl_vars['isEditor']): ?>20%<?php else: ?>25%<?php endif; ?>"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.request"), $this);?>
</td>
		<?php if ($this->_tpl_vars['isEditor']): ?><td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td><?php endif; ?>
	</tr>
	<?php $this->assign('editAssignments', $this->_tpl_vars['submission']->getEditAssignments()); ?>
	<?php $_from = $this->_tpl_vars['editAssignments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['editAssignments'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['editAssignments']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['editAssignment']):
        $this->_foreach['editAssignments']['iteration']++;
?>
	<?php if ($this->_tpl_vars['editAssignment']->getEditorId() == $this->_tpl_vars['userId']): ?>
		<?php $this->assign('selfAssigned', 1); ?>
	<?php endif; ?>
		<tr valign="top">
			<td><?php if ($this->_tpl_vars['editAssignment']->getIsEditor()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editor"), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditor"), $this);?>
<?php endif; ?></td>
			<td>
				<?php $this->assign('emailString', ((is_array($_tmp=$this->_tpl_vars['editAssignment']->getEditorFullName())) ? $this->_run_mod_handler('concat', true, $_tmp, " <", $this->_tpl_vars['editAssignment']->getEditorEmail(), ">") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, " <", $this->_tpl_vars['editAssignment']->getEditorEmail(), ">"))); ?>
				<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'email','redirectUrl' => $this->_tpl_vars['currentUrl'],'to' => ((is_array($_tmp=$this->_tpl_vars['emailString'])) ? $this->_run_mod_handler('to_array', true, $_tmp) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp)),'subject' => ((is_array($_tmp=$this->_tpl_vars['submission']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)),'articleId' => $this->_tpl_vars['submission']->getId()), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

				<?php echo ((is_array($_tmp=$this->_tpl_vars['editAssignment']->getEditorFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['url']), $this);?>

			</td>
			<td>
				&nbsp;&nbsp;<input
					type="checkbox"
					name="canReview-<?php echo $this->_tpl_vars['editAssignment']->getEditId(); ?>
"
					<?php if ($this->_tpl_vars['editAssignment']->getIsEditor()): ?>
						checked="checked"
						disabled="disabled"
					<?php else: ?>
						<?php if ($this->_tpl_vars['editAssignment']->getCanReview()): ?> checked="checked"<?php endif; ?>
						<?php if (! $this->_tpl_vars['isEditor']): ?>disabled="disabled"<?php endif; ?>
					<?php endif; ?>
				/>
			</td>
			<td>
				&nbsp;&nbsp;<input
					type="checkbox"
					name="canEdit-<?php echo $this->_tpl_vars['editAssignment']->getEditId(); ?>
"
					<?php if ($this->_tpl_vars['editAssignment']->getIsEditor()): ?>
						checked="checked"
						disabled="disabled"
					<?php else: ?>
						<?php if ($this->_tpl_vars['editAssignment']->getCanEdit()): ?> checked="checked"<?php endif; ?>
						<?php if (! $this->_tpl_vars['isEditor']): ?>disabled="disabled"<?php endif; ?>
					<?php endif; ?>
				/>
			</td>
			<td><?php if ($this->_tpl_vars['editAssignment']->getDateNotified()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['editAssignment']->getDateNotified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
<?php else: ?>&mdash;<?php endif; ?></td>
			<?php if ($this->_tpl_vars['isEditor']): ?>
				<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'deleteEditAssignment','path' => $this->_tpl_vars['editAssignment']->getEditId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a></td>
			<?php endif; ?>
		</tr>
	<?php endforeach; else: ?>
		<tr><td colspan="<?php if ($this->_tpl_vars['isEditor']): ?>6<?php else: ?>5<?php endif; ?>" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.noneAssigned"), $this);?>
</td></tr>
	<?php endif; unset($_from); ?>
</table>
<?php if ($this->_tpl_vars['isEditor']): ?>
	<input type="submit" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
"/>&nbsp;&nbsp;
	<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'assignEditor','path' => 'sectionEditor','articleId' => $this->_tpl_vars['submission']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.assignSectionEditor"), $this);?>
</a>
	|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'assignEditor','path' => 'editor','articleId' => $this->_tpl_vars['submission']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.assignEditor"), $this);?>
</a>
	<?php if (! $this->_tpl_vars['selfAssigned']): ?>|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'assignEditor','path' => 'editor','editorId' => $this->_tpl_vars['userId'],'articleId' => $this->_tpl_vars['submission']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.addSelf"), $this);?>
</a><?php endif; ?>
<?php endif; ?>
</form>
</div>
