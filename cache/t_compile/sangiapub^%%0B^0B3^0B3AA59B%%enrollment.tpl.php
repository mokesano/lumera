<?php /* Smarty version 2.6.26, created on 2026-04-16 13:57:13
         compiled from manager/people/enrollment.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/people/enrollment.tpl', 16, false),array('function', 'translate', 'manager/people/enrollment.tpl', 35, false),array('function', 'html_options_translate', 'manager/people/enrollment.tpl', 70, false),array('function', 'sort_heading', 'manager/people/enrollment.tpl', 121, false),array('function', 'icon', 'manager/people/enrollment.tpl', 141, false),array('function', 'page_info', 'manager/people/enrollment.tpl', 174, false),array('function', 'page_links', 'manager/people/enrollment.tpl', 175, false),array('modifier', 'escape', 'manager/people/enrollment.tpl', 17, false),array('modifier', 'wordwrap', 'manager/people/enrollment.tpl', 135, false),array('modifier', 'concat', 'manager/people/enrollment.tpl', 138, false),array('modifier', 'assign', 'manager/people/enrollment.tpl', 139, false),array('modifier', 'to_array', 'manager/people/enrollment.tpl', 140, false),array('modifier', 'truncate', 'manager/people/enrollment.tpl', 141, false),array('block', 'iterate', 'manager/people/enrollment.tpl', 131, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.people.enrollment"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<form id="disableUser" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'disableUser'), $this);?>
">
    <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
	<input type="hidden" name="reason" value=""/>
	<input type="hidden" name="userId" value=""/>
</form>

<script type="text/javascript">
<?php echo '
<!--
function toggleChecked() {
	var elements = document.getElementById(\'people\').elements;
	for (var i=0; i < elements.length; i++) {
		if (elements[i].name == \'bcc[]\') {
			elements[i].checked = !elements[i].checked;
		}
	}
}

function confirmAndPrompt(userId) {
	var reason = prompt(\''; ?>
<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.confirmDisable"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript'));?>
<?php echo '\');
	if (reason == null) return;

	document.getElementById(\'disableUser\').reason.value = reason;
	document.getElementById(\'disableUser\').userId.value = userId;

	document.getElementById(\'disableUser\').submit();
}
// -->
'; ?>

</script>

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['roleName']), $this);?>
</h3>
<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic']), $this);?>
">
    <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
	<select name="roleSymbolic" class="selectMenu">
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'all'): ?>selected="selected" <?php endif; ?>value="all"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.allUsers"), $this);?>
</option>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'managers'): ?>selected="selected" <?php endif; ?>value="managers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.managers"), $this);?>
</option>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'editors'): ?>selected="selected" <?php endif; ?>value="editors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editors"), $this);?>
</option>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'sectionEditors'): ?>selected="selected" <?php endif; ?>value="sectionEditors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</option>
		<?php if ($this->_tpl_vars['roleSettings']['useLayoutEditors']): ?>
			<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'layoutEditors'): ?>selected="selected" <?php endif; ?>value="layoutEditors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.layoutEditors"), $this);?>
</option>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['roleSettings']['useCopyeditors']): ?>
			<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'copyeditors'): ?>selected="selected" <?php endif; ?>value="copyeditors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.copyeditors"), $this);?>
</option>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['roleSettings']['useProofreaders']): ?>
			<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'proofreaders'): ?>selected="selected" <?php endif; ?>value="proofreaders"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.proofreaders"), $this);?>
</option>
		<?php endif; ?>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'reviewers'): ?>selected="selected" <?php endif; ?>value="reviewers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reviewers"), $this);?>
</option>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'authors'): ?>selected="selected" <?php endif; ?>value="authors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.authors"), $this);?>
</option>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'readers'): ?>selected="selected" <?php endif; ?>value="readers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.readers"), $this);?>
</option>
		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'subscriptionManagers'): ?>selected="selected" <?php endif; ?>value="subscriptionManagers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.subscriptionManagers"), $this);?>
</option>
	</select>
	<select name="searchField" size="1" class="selectMenu">
		<?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['fieldOptions'],'selected' => $this->_tpl_vars['searchField']), $this);?>

	</select>
	<select name="searchMatch" size="1" class="selectMenu">
		<option value="contains"<?php if ($this->_tpl_vars['searchMatch'] == 'contains'): ?> selected="selected"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.contains"), $this);?>
</option>
		<option value="is"<?php if ($this->_tpl_vars['searchMatch'] == 'is'): ?> selected="selected"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.is"), $this);?>
</option>
		<option value="startsWith"<?php if ($this->_tpl_vars['searchMatch'] == 'startsWith'): ?> selected="selected"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.startsWith"), $this);?>
</option>
	</select>
	<input type="text" size="10" name="search" class="textField" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['search'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />&nbsp;<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
" class="button" />
</form>

<div class="c-jump">
    <p class="describe italic u-font-sans">Click the alphabet to chose name of user</p>
    <span class="c-jump-navigation"><?php $_from = $this->_tpl_vars['alphaList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['letter']):
?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic'],'searchInitial' => $this->_tpl_vars['letter']), $this);?>
"><?php if ($this->_tpl_vars['letter'] == $this->_tpl_vars['searchInitial']): ?><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a> <?php endforeach; endif; unset($_from); ?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic']), $this);?>
"><?php if ($this->_tpl_vars['searchInitial'] == ''): ?><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
</strong><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
<?php endif; ?></a></span>
</div>

<?php if (! $this->_tpl_vars['roleId']): ?>
<ul>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'managers'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.managers"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'editors'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editors"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'sectionEditors'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</a></li>
	<?php if ($this->_tpl_vars['roleSettings']['useLayoutEditors']): ?>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'layoutEditors'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.layoutEditors"), $this);?>
</a></li>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['roleSettings']['useCopyeditors']): ?>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'copyeditors'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.copyeditors"), $this);?>
</a></li>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['roleSettings']['useProofreaders']): ?>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'proofreaders'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.proofreaders"), $this);?>
</a></li>
	<?php endif; ?>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'reviewers'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reviewers"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'authors'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.authors"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'readers'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.readers"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'subscriptionManagers'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.subscriptionManagers"), $this);?>
</a></li>
</ul>
<?php else: ?>
<p class="u-mb-16">
    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'all'), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.allUsers"), $this);?>
</a>
</p>
<?php endif; ?>

<form id="people" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'email'), $this);?>
" method="post">
<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
<input type="hidden" name="redirectUrl" value="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic']), $this);?>
"/>

<div id="users">
<table width="100%" class="listing">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="3%">&nbsp;</td>
		<td width="15%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "user.username",'sort' => 'username'), $this);?>
</td>
		<td width="30%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "user.name",'sort' => 'name'), $this);?>
</td>
		<td width="20%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "user.email",'sort' => 'email'), $this);?>
</td>
		<td width="28%" align="center"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<?php if ($this->_tpl_vars['users']->eof()): ?>
	<tr valign="bottom">
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<?php endif; ?>
	<?php $this->_tag_stack[] = array('iterate', array('from' => 'users','item' => 'user')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->assign('userExists', 1); ?>
	<tr valign="top">
		<td><input type="checkbox" name="bcc[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/></td>
		<td><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'userProfile','path' => $this->_tpl_vars['user']->getId()), $this);?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('wordwrap', true, $_tmp, 15, ' ', true) : smarty_modifier_wordwrap($_tmp, 15, ' ', true)); ?>
</a></td>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		<td class="nowrap">
			<?php $this->assign('emailString', ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('concat', true, $_tmp, " <", $this->_tpl_vars['user']->getEmail(), ">") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, " <", $this->_tpl_vars['user']->getEmail(), ">"))); ?>
			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic'],'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'redirectUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'redirectUrl'));?>

			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'email','to' => ((is_array($_tmp=$this->_tpl_vars['emailString'])) ? $this->_run_mod_handler('to_array', true, $_tmp) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp)),'redirectUrl' => $this->_tpl_vars['redirectUrl']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

			<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getEmail())) ? $this->_run_mod_handler('truncate', true, $_tmp, 15, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 15, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&nbsp;<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['url']), $this);?>

		</td>
		<td class="actionRole" align="center">
			<?php if ($this->_tpl_vars['roleId']): ?>
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'unEnroll','path' => $this->_tpl_vars['roleId'],'userId' => $this->_tpl_vars['user']->getId(),'journalId' => $this->_tpl_vars['currentJournal']->getId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.confirmUnenroll"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.unenroll"), $this);?>
</a>&nbsp;|
			<?php endif; ?>
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editUser','path' => $this->_tpl_vars['user']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.edit"), $this);?>
</a>
			<?php if ($this->_tpl_vars['thisUser']->getId() != $this->_tpl_vars['user']->getId()): ?>
				|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'signInAsUser','path' => $this->_tpl_vars['user']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.signInAs"), $this);?>
</a>
				<?php if (! $this->_tpl_vars['roleId']): ?>|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'removeUser','path' => $this->_tpl_vars['user']->getId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.confirmRemove"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.remove"), $this);?>
</a><?php endif; ?>
				<?php if ($this->_tpl_vars['user']->getDisabled()): ?>
					|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'enableUser','path' => $this->_tpl_vars['user']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.enable"), $this);?>
</a>
				<?php else: ?>
					|&nbsp;<a href="javascript:confirmAndPrompt(<?php echo $this->_tpl_vars['user']->getId(); ?>
)" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.disable"), $this);?>
</a>
				<?php endif; ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['users']->eof()): ?>
	<tr valign="bottom">
		<td colspan="5" class="end separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php if ($this->_tpl_vars['users']->wasEmpty()): ?>
	<tr valign="top">
		<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.noneEnrolled"), $this);?>
</td>
	</tr>
	<tr valign="bottom">
		<td colspan="5" class="endseparator">&nbsp;</td>
	</tr>
<?php else: ?>
	<tr id="colspan" class="colspan u-hide">
		<td colspan="4" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['users']), $this);?>
</td>
		<td align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'users','name' => 'users','iterator' => $this->_tpl_vars['users'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'roleSymbolic' => $this->_tpl_vars['roleSymbolic'],'searchInitial' => $this->_tpl_vars['searchInitial'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>
</td>
	</tr>
<?php endif; ?>
</table>

<?php if ($this->_tpl_vars['userExists']): ?>
	<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.compose"), $this);?>
" class="button defaultButton"/>&nbsp;<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.selectAll"), $this);?>
" class="button" onclick="toggleChecked()" />  <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager','escape' => false), $this);?>
'" />
	</p>
<?php endif; ?>
</div>
</form>

<p class="u-mt-24">
    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'enrollSearch','path' => $this->_tpl_vars['roleId']), $this);?>
" class="link-button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.enrollExistingUser"), $this);?>
</a> |
    <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic'],'searchInitial' => $this->_tpl_vars['searchInitial'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'enrollmentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'enrollmentUrl'));?>

    <a href="<?php if ($this->_tpl_vars['roleId']): ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createUser','roleId' => $this->_tpl_vars['roleId'],'source' => $this->_tpl_vars['enrollmentUrl']), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createUser','source' => $this->_tpl_vars['enrollmentUrl']), $this);?>
<?php endif; ?>" class="link-button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.createUser"), $this);?>
</a> | <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'enrollSyncSelect','path' => $this->_tpl_vars['rolePath']), $this);?>
" class="link-button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.enrollSync"), $this);?>
</a>
</p>

<?php if (! $this->_tpl_vars['users']->wasEmpty()): ?>
<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['users']), $this);?>
</div>
    </section>
    <?php if ($this->_tpl_vars['users']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'users','name' => 'users','iterator' => $this->_tpl_vars['users'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'roleSymbolic' => $this->_tpl_vars['roleSymbolic'],'searchInitial' => $this->_tpl_vars['searchInitial'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>

       </div>
    </section>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
