<?php /* Smarty version 2.6.26, created on 2026-04-05 04:41:19
         compiled from manager/groups/groups.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/groups/groups.tpl', 22, false),array('function', 'translate', 'manager/groups/groups.tpl', 26, false),array('function', 'icon', 'manager/groups/groups.tpl', 68, false),array('function', 'page_info', 'manager/groups/groups.tpl', 96, false),array('function', 'page_links', 'manager/groups/groups.tpl', 97, false),array('modifier', 'assign', 'manager/groups/groups.tpl', 23, false),array('modifier', 'escape', 'manager/groups/groups.tpl', 68, false),array('block', 'iterate', 'manager/groups/groups.tpl', 53, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.groups"); ?><?php echo ''; ?><?php $this->assign('pageId', "manager.groups"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
$(document).ready(function() { setupTableDND("#dragTable", "moveGroup"); });
'; ?>

</script>
<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setBoardEnabled'), $this);?>
" method="post">
	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialTeam'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'aboutEditorialTeamUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'aboutEditorialTeamUrl'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'people','path' => 'all'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'peopleManagementUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'peopleManagementUrl'));?>

	<div class="instruct">
	    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.enableBoard.description",'aboutEditorialTeamUrl' => $this->_tpl_vars['aboutEditorialTeamUrl']), $this);?>
<br/>
	</div>
	<div class="board-enabled-off">
	    <input type="radio" id="boardEnabledOff" <?php if (! $this->_tpl_vars['boardEnabled']): ?>checked="checked" <?php endif; ?>name="boardEnabled" value="0"/>&nbsp;<label for="boardEnabledOff"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.disableBoard"), $this);?>
</label>
	</div>
	<div class="board-enabled-on">
	    <input type="radio" id="boardEnabledOn" <?php if ($this->_tpl_vars['boardEnabled']): ?>checked="checked" <?php endif; ?>name="boardEnabled" value="1"/>&nbsp;<label for="boardEnabledOn"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.enableBoard"), $this);?>
</label>
	</div>
	<div class="action u-mt-16">
	    <input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
" class="button defaultButton"/>
	</div>
</form>
<br/>
<div id="groups">

<table width="100%" class="listing" id="dragTable">
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td colspan="2" width="75%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.title"), $this);?>
</td>
		<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<?php $this->assign('isFirstEditorialTeamEntry', 1); ?>
<?php $this->_tag_stack[] = array('iterate', array('from' => 'groups','item' => 'group')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php if ($this->_tpl_vars['group']->getContext() == GROUP_CONTEXT_EDITORIAL_TEAM): ?>
		<?php if ($this->_tpl_vars['isFirstEditorialTeamEntry']): ?>
			<?php $this->assign('isFirstEditorialTeamEntry', 0); ?>
				<tr valign="top">
					<td colspan="3"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.context.editorialTeam.short"), $this);?>
</td>
				</tr>
							<?php endif; ?>
		<tr valign="top" id=editorialteam-<?php echo $this->_tpl_vars['group']->getId(); ?>
 class="data">
			<td class="drag" width="5%">&nbsp;</td>
			<td class="drag" width="60%">
				<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'email','toGroup' => $this->_tpl_vars['group']->getId()), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

				<?php echo ((is_array($_tmp=$this->_tpl_vars['group']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&nbsp;<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['url']), $this);?>

			</td>
		<?php else: ?>
		<tr valign="top" id="other-<?php echo $this->_tpl_vars['group']->getId(); ?>
" class="data">
			<td class="drag" colspan="2">
				<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','op' => 'email','toGroup' => $this->_tpl_vars['group']->getId()), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

				<?php echo ((is_array($_tmp=$this->_tpl_vars['group']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&nbsp;<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['url']), $this);?>

			</td>
		<?php endif; ?>
		<td width="25%">
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editGroup','path' => $this->_tpl_vars['group']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.edit"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'groupMembership','path' => $this->_tpl_vars['group']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.membership"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'deleteGroup','path' => $this->_tpl_vars['group']->getId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveGroup','d' => 'u','id' => $this->_tpl_vars['group']->getId()), $this);?>
">&uarr;</a>&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveGroup','d' => 'd','id' => $this->_tpl_vars['group']->getId()), $this);?>
">&darr;</a>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['groups']->eof()): ?>
	<tr>
		<td colspan="3" class="<?php if ($this->_tpl_vars['groups']->eof()): ?>end<?php endif; ?>separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php if ($this->_tpl_vars['groups']->wasEmpty()): ?>
	<tr>
		<td colspan="3" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.noneCreated"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="2" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['groups']), $this);?>
</td>
		<td colspan="1" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'groups','name' => 'groups','iterator' => $this->_tpl_vars['groups']), $this);?>
</td>
	</tr>
<?php endif; ?>
</table>

<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.groups.create"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createGroup'), $this);?>
'" />
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
