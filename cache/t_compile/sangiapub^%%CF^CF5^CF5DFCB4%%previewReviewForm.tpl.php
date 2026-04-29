<?php /* Smarty version 2.6.26, created on 2026-04-05 05:54:11
         compiled from manager/reviewForms/previewReviewForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/reviewForms/previewReviewForm.tpl', 26, false),array('function', 'translate', 'manager/reviewForms/previewReviewForm.tpl', 26, false),array('modifier', 'escape', 'manager/reviewForms/previewReviewForm.tpl', 49, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageId', "manager.reviewFormElements.previewReviewForm"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', $this->_tpl_vars['pageTitle']); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-EDITOR017.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php $this->assign('reviewFormId', $this->_tpl_vars['reviewForm']->getId()); ?>
<?php if ($this->_tpl_vars['completeCounts'][$this->_tpl_vars['reviewFormId']] == 0 && $this->_tpl_vars['incompleteCounts'][$this->_tpl_vars['reviewFormId']] == 0): ?>
	<?php $this->assign('canEdit', 1); ?>
<?php else: ?>
	<?php $this->assign('canEdit', 0); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['canEdit']): ?>
	<ul class="menu">
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editReviewForm','path' => $this->_tpl_vars['reviewFormId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.edit"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'reviewFormElements','path' => $this->_tpl_vars['reviewFormId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewFormElements"), $this);?>
</a></li>
		<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'previewReviewForm','path' => $this->_tpl_vars['reviewFormId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.preview"), $this);?>
</a></li>
	</ul>
<?php endif; ?>

<br/>

<h3><?php echo $this->_tpl_vars['reviewForm']->getLocalizedTitle(); ?>
</h3>
<p><?php echo $this->_tpl_vars['reviewForm']->getLocalizedDescription(); ?>
</p>

<?php $_from = $this->_tpl_vars['reviewFormElements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['reviewFormElements'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['reviewFormElements']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['reviewFormElement']):
        $this->_foreach['reviewFormElements']['iteration']++;
?>
	<p><?php echo $this->_tpl_vars['reviewFormElement']->getLocalizedQuestion(); ?>
<?php if ($this->_tpl_vars['reviewFormElement']->getRequired()): ?>*<?php endif; ?></p>
	<p>
		<?php if ($this->_tpl_vars['reviewFormElement']->getElementType() == REVIEW_FORM_ELEMENT_TYPE_SMALL_TEXT_FIELD): ?>
			<input type="text" size="10" maxlength="40" class="textField" />
		<?php elseif ($this->_tpl_vars['reviewFormElement']->getElementType() == REVIEW_FORM_ELEMENT_TYPE_TEXT_FIELD): ?>
			<input type="text" size="40" maxlength="120" class="textField" />
		<?php elseif ($this->_tpl_vars['reviewFormElement']->getElementType() == REVIEW_FORM_ELEMENT_TYPE_TEXTAREA): ?>
			<textarea rows="4" cols="40" class="textArea"></textarea>
		<?php elseif ($this->_tpl_vars['reviewFormElement']->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES): ?>
			<?php $this->assign('possibleResponses', $this->_tpl_vars['reviewFormElement']->getLocalizedPossibleResponses()); ?>
			<?php $_from = $this->_tpl_vars['possibleResponses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['responses'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['responses']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['responseId'] => $this->_tpl_vars['responseItem']):
        $this->_foreach['responses']['iteration']++;
?>
				<input id="check-<?php echo ((is_array($_tmp=$this->_tpl_vars['responseId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="checkbox"/>
				<label for="check-<?php echo ((is_array($_tmp=$this->_tpl_vars['responseId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_tpl_vars['responseItem']['content']; ?>
</label>
				<br/>
			<?php endforeach; endif; unset($_from); ?>
		<?php elseif ($this->_tpl_vars['reviewFormElement']->getElementType() == REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS): ?>
			<?php $this->assign('possibleResponses', $this->_tpl_vars['reviewFormElement']->getLocalizedPossibleResponses()); ?>
			<?php $_from = $this->_tpl_vars['possibleResponses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['responses'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['responses']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['responseId'] => $this->_tpl_vars['responseItem']):
        $this->_foreach['responses']['iteration']++;
?>
				<input id="radio-<?php echo ((is_array($_tmp=$this->_tpl_vars['responseId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="<?php echo $this->_tpl_vars['reviewFormElement']->getId(); ?>
" type="radio"/>
				<label for="radio-<?php echo ((is_array($_tmp=$this->_tpl_vars['responseId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_tpl_vars['responseItem']['content']; ?>
</label>
				<br/>
			<?php endforeach; endif; unset($_from); ?>
		<?php elseif ($this->_tpl_vars['reviewFormElement']->getElementType() == REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX): ?>
			<select size="1" class="selectMenu">
				<?php $this->assign('possibleResponses', $this->_tpl_vars['reviewFormElement']->getLocalizedPossibleResponses()); ?>
				<?php $_from = $this->_tpl_vars['possibleResponses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['responses'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['responses']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['responseId'] => $this->_tpl_vars['responseItem']):
        $this->_foreach['responses']['iteration']++;
?>
					<option><?php echo $this->_tpl_vars['responseItem']['content']; ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>
		<?php endif; ?>
	</p>
<?php endforeach; endif; unset($_from); ?>

<br/>

<form id="previewReviewForm" method="post" action="<?php if (! $this->_tpl_vars['canEdit']): ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'reviewForms'), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editReviewForm','path' => $this->_tpl_vars['reviewFormId']), $this);?>
<?php endif; ?>">
	<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.close"), $this);?>
" class="button defaultButton" /></p>
</form>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
