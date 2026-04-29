<?php /* Smarty version 2.6.26, created on 2026-04-05 05:54:02
         compiled from manager/reviewForms/reviewForms.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/reviewForms/reviewForms.tpl', 22, false),array('function', 'url', 'manager/reviewForms/reviewForms.tpl', 47, false),array('function', 'page_info', 'manager/reviewForms/reviewForms.tpl', 76, false),array('function', 'page_links', 'manager/reviewForms/reviewForms.tpl', 77, false),array('block', 'iterate', 'manager/reviewForms/reviewForms.tpl', 35, false),array('modifier', 'escape', 'manager/reviewForms/reviewForms.tpl', 43, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.reviewForms"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-EDITOR017.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
$(document).ready(function() { setupTableDND("#dragTable", "moveReviewForm"); });
'; ?>

</script>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.list.description"), $this);?>
</p>

<div id="reviewForms">
<table width="100%" class="listing" id="dragTable">
	<tr class="heading" valign="bottom">
		<td width="42%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.title"), $this);?>
</td>
		<td width="14%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.inReview"), $this);?>
</td>
		<td width="14%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.completed"), $this);?>
</td>
		<td width="40%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<tr>
		<td class="headseparator" colspan="4">&nbsp;</td>
	</tr>
<?php $this->_tag_stack[] = array('iterate', array('from' => 'reviewForms','item' => 'reviewForm','name' => 'reviewForms')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<?php $this->assign('reviewFormId', $this->_tpl_vars['reviewForm']->getId()); ?>
<?php if ($this->_tpl_vars['completeCounts'][$this->_tpl_vars['reviewFormId']] == 0 && $this->_tpl_vars['incompleteCounts'][$this->_tpl_vars['reviewFormId']] == 0): ?>
	<?php $this->assign('canEdit', 1); ?>
<?php else: ?>
	<?php $this->assign('canEdit', 0); ?>
<?php endif; ?>
	<tr valign="top" id="reviewform-<?php echo $this->_tpl_vars['reviewForm']->getId(); ?>
" class="data">
		<td class="drag"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewForm']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		<td class="drag"><?php echo $this->_tpl_vars['incompleteCounts'][$this->_tpl_vars['reviewFormId']]; ?>
</td>
		<td class="drag"><?php echo $this->_tpl_vars['completeCounts'][$this->_tpl_vars['reviewFormId']]; ?>
</td>
		<td align="right" class="nowrap">
			<?php if ($this->_tpl_vars['canEdit']): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editReviewForm','path' => $this->_tpl_vars['reviewForm']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.edit"), $this);?>
</a><?php endif; ?>
			<?php echo ''; ?><?php if ($this->_tpl_vars['reviewForm']->getActive()): ?><?php echo '<a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'deactivateReviewForm','path' => $this->_tpl_vars['reviewForm']->getId()), $this);?><?php echo '" class="action">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.deactivate"), $this);?><?php echo '</a>'; ?><?php else: ?><?php echo '<a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'activateReviewForm','path' => $this->_tpl_vars['reviewForm']->getId()), $this);?><?php echo '" class="action">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.activate"), $this);?><?php echo '</a>'; ?><?php endif; ?><?php echo ''; ?>

			<?php if (! $this->_tpl_vars['canEdit']): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'copyReviewForm','path' => $this->_tpl_vars['reviewForm']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.copy"), $this);?>
</a><?php endif; ?>
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'previewReviewForm','path' => $this->_tpl_vars['reviewForm']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.preview"), $this);?>
</a>
			<?php if ($this->_tpl_vars['canEdit']): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'deleteReviewForm','path' => $this->_tpl_vars['reviewForm']->getId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.confirmDeleteUnpublished"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a><?php endif; ?>
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveReviewForm','d' => 'u','id' => $this->_tpl_vars['reviewForm']->getId()), $this);?>
" class="action">&uarr;</a>&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveReviewForm','d' => 'd','id' => $this->_tpl_vars['reviewForm']->getId()), $this);?>
" class="action">&darr;</a>
		</td>
	</tr>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>

<?php if ($this->_tpl_vars['reviewForms']->wasEmpty()): ?>
	<tr>
		<td colspan="4" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.noneCreated"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="2" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['reviewForms']), $this);?>
</td>
		<td colspan="2" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'reviewForms','name' => 'reviewForms','iterator' => $this->_tpl_vars['reviewForms']), $this);?>
</td>
	</tr>
<?php endif; ?>

</table>

<a class="button" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createReviewForm'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.reviewForms.create"), $this);?>
</a>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
