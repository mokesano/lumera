<?php /* Smarty version 2.6.26, created on 2026-04-16 13:47:09
         compiled from admin/selectMergeUser.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'admin/selectMergeUser.tpl', 18, false),array('function', 'url', 'admin/selectMergeUser.tpl', 22, false),array('function', 'html_options_translate', 'admin/selectMergeUser.tpl', 40, false),array('function', 'icon', 'admin/selectMergeUser.tpl', 112, false),array('function', 'page_info', 'admin/selectMergeUser.tpl', 152, false),array('function', 'page_links', 'admin/selectMergeUser.tpl', 156, false),array('function', 'math', 'admin/selectMergeUser.tpl', 162, false),array('modifier', 'escape', 'admin/selectMergeUser.tpl', 24, false),array('modifier', 'wordwrap', 'admin/selectMergeUser.tpl', 106, false),array('modifier', 'concat', 'admin/selectMergeUser.tpl', 109, false),array('modifier', 'assign', 'admin/selectMergeUser.tpl', 110, false),array('modifier', 'to_array', 'admin/selectMergeUser.tpl', 111, false),array('modifier', 'truncate', 'admin/selectMergeUser.tpl', 112, false),array('modifier', 'count', 'admin/selectMergeUser.tpl', 117, false),array('block', 'iterate', 'admin/selectMergeUser.tpl', 100, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "admin.mergeUsers"); ?><?php echo ''; ?><?php $this->assign('pageDisplayed', 'site'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<div id="selectMergeUsers">
    <div class="alert alert-info alert-text">
        <p class="text" style="line-height: 1.5;"><?php if (! empty ( $this->_tpl_vars['oldUserIds'] )): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.into.description"), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.from.description"), $this);?>
<?php endif; ?></p>
    </div>
<div id="roles" class="block">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['roleName']), $this);?>
</h3>
    <form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic'],'oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
">
                <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
    	<select name="roleSymbolic" class="selectMenu">
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'all'): ?>selected="selected" <?php endif; ?>value="all"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.allUsers"), $this);?>
</option>
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'managers'): ?>selected="selected" <?php endif; ?>value="managers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.managers"), $this);?>
</option>
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'editors'): ?>selected="selected" <?php endif; ?>value="editors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editors"), $this);?>
</option>
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'sectionEditors'): ?>selected="selected" <?php endif; ?>value="sectionEditors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</option>
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'layoutEditors'): ?>selected="selected" <?php endif; ?>value="layoutEditors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.layoutEditors"), $this);?>
</option>
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'copyeditors'): ?>selected="selected" <?php endif; ?>value="copyeditors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.copyeditors"), $this);?>
</option>
    		<option <?php if ($this->_tpl_vars['roleSymbolic'] == 'proofreaders'): ?>selected="selected" <?php endif; ?>value="proofreaders"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.proofreaders"), $this);?>
</option>
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
?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic'],'oldUserIds' => $this->_tpl_vars['oldUserIds'],'searchInitial' => $this->_tpl_vars['letter']), $this);?>
"><?php if ($this->_tpl_vars['letter'] == $this->_tpl_vars['searchInitial']): ?><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a> <?php endforeach; endif; unset($_from); ?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic'],'oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php if ($this->_tpl_vars['searchInitial'] == ''): ?><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
</strong><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
<?php endif; ?></a></span>
    </div>
    
    <?php if (! $this->_tpl_vars['roleId']): ?>
    <div class="pseudoMenu">
        <ul>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'managers','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.managers"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'editors','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editors"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'sectionEditors','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'layoutEditors','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.layoutEditors"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'copyeditors','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.copyeditors"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'proofreaders','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.proofreaders"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'reviewers','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reviewers"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'authors','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.authors"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'readers','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.readers"), $this);?>
</a></li>
        	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'subscriptionManagers','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.subscriptionManagers"), $this);?>
</a></li>
        </ul>
    </div>
    <?php else: ?>
    <p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => 'all','oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.allUsers"), $this);?>
</a></p>
    <?php endif; ?>
</div>

<div id="users" class="block">
<?php if (! empty ( $this->_tpl_vars['oldUserIds'] )): ?>
		<?php $this->assign('numCols', 4); ?>
<?php else: ?>
		<?php $this->assign('numCols', 5); ?>
       <form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array(), $this);?>
">
<?php endif; ?>
<table width="100%" class="listing">
	<tr>
		<td colspan="<?php echo $this->_tpl_vars['numCols']; ?>
" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<?php if (empty ( $this->_tpl_vars['oldUserIds'] )): ?>
			<td width="5%">&nbsp;</td>
		<?php endif; ?>
		<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
</td>
		<td width="29%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.name"), $this);?>
</td>
		<td width="29%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
</td>
		<td width="15%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="<?php echo $this->_tpl_vars['numCols']; ?>
" class="headseparator">&nbsp;</td>
	</tr>
	<?php $this->_tag_stack[] = array('iterate', array('from' => 'users','item' => 'user')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->assign('userExists', 1); ?>
	<tr valign="top">
		<?php if (empty ( $this->_tpl_vars['oldUserIds'] )): ?>
			<td><input type="checkbox" name="oldUserIds[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['thisUser']->getId() == $this->_tpl_vars['user']->getId()): ?>disabled="disabled" <?php endif; ?>/></td>
		<?php endif; ?>
		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('wordwrap', true, $_tmp, 15, ' ', true) : smarty_modifier_wordwrap($_tmp, 15, ' ', true)); ?>
</td>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		<td class="nowrap">
			<?php $this->assign('emailString', ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('concat', true, $_tmp, " <", $this->_tpl_vars['user']->getEmail(), ">") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, " <", $this->_tpl_vars['user']->getEmail(), ">"))); ?>
			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => $this->_tpl_vars['roleSymbolic']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'redirectUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'redirectUrl'));?>

			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'email','to' => ((is_array($_tmp=$this->_tpl_vars['emailString'])) ? $this->_run_mod_handler('to_array', true, $_tmp) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp)),'redirectUrl' => $this->_tpl_vars['redirectUrl']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

			<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getEmail())) ? $this->_run_mod_handler('truncate', true, $_tmp, 15, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 15, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
&nbsp;<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['url']), $this);?>

		</td>
		<td align="right">
			<?php if (! empty ( $this->_tpl_vars['oldUserIds'] )): ?>
				<?php if (! in_array ( $this->_tpl_vars['user']->getId() , $this->_tpl_vars['oldUserIds'] )): ?>
					<a href="#" onclick="confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('oldUserIds' => $this->_tpl_vars['oldUserIds'],'newUserId' => $this->_tpl_vars['user']->getId()), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.confirm",'oldAccountCount' => count($this->_tpl_vars['oldUserIds']),'newUsername' => $this->_tpl_vars['user']->getUsername()), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.mergeUser"), $this);?>
</a>
				<?php endif; ?>
			<?php elseif ($this->_tpl_vars['thisUser']->getId() != $this->_tpl_vars['user']->getId()): ?>
				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('oldUserIds' => $this->_tpl_vars['user']->getId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.mergeUser"), $this);?>
</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['users']->eof()): ?>
    	<tr>
    		<td colspan="<?php echo $this->_tpl_vars['numCols']; ?>
" class="endseparator">&nbsp;</td>
    	</tr>
	<?php endif; ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</table>

<?php if (empty ( $this->_tpl_vars['oldUserIds'] )): ?>
	<input type="submit" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers"), $this);?>
" />
	</form>
<?php endif; ?>

<?php if ($this->_tpl_vars['users']->wasEmpty()): ?>
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text search-tips">
            <h2 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.mergeUsers.noneEnrolled"), $this);?>
</h2>
            <div class="c-empty-state-card__text--description">There are currently no user accounts available for merging. Users must be registered in the system before they can be selected for account consolidation or merger processes.</div>
        </div>
    </div>
</div>
<?php else: ?>
<div id="colspan" class="colspan u-mb-0" >	    
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View <?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['users']), $this);?>
</div>
    </section>
    <?php if ($this->_tpl_vars['users']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'users','name' => 'users','iterator' => $this->_tpl_vars['users'],'searchInitial' => $this->_tpl_vars['searchInitial'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'roleSymbolic' => $this->_tpl_vars['roleSymbolic'],'oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>

       </div>
    </section>
    <?php endif; ?>
    <div class="u-hide">
    	<tr class="u-hide">
    		<td colspan="<?php echo smarty_function_math(array('equation' => "floor(numCols / 2)",'numCols' => $this->_tpl_vars['numCols']), $this);?>
" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['users']), $this);?>
</td>
    		<td colspan="<?php echo smarty_function_math(array('equation' => "ceil(numCols / 2)",'numCols' => $this->_tpl_vars['numCols']), $this);?>
" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'users','name' => 'users','iterator' => $this->_tpl_vars['users'],'searchInitial' => $this->_tpl_vars['searchInitial'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'roleSymbolic' => $this->_tpl_vars['roleSymbolic'],'oldUserIds' => $this->_tpl_vars['oldUserIds']), $this);?>
</td>
    	</tr>
    </div>
</div>
<?php endif; ?>

</div>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
