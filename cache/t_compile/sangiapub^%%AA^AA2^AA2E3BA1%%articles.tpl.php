<?php /* Smarty version 2.6.26, created on 2026-04-04 22:23:04
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 30, false),array('modifier', 'to_array', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 71, false),array('modifier', 'strip_unsafe_html', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 127, false),array('modifier', 'nl2br', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 127, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 59, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 64, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 71, false),array('function', 'html_options', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 85, false),array('function', 'page_info', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 144, false),array('function', 'page_links', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 145, false),array('block', 'iterate', 'file:/home/sangiaor/public_html/journals/plugins/generic/sword/articles.tpl', 122, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.sword.displayName"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
<!--
function toggleChecked() {
	var elements = document.getElementsByName("articleId[]");
	for (var i=0; i < elements.length; i++) {
			elements[i].checked = !elements[i].checked;
	}
}

function changeDepositPoint() {
	var depositPoints = Array();
	'; ?>

		<?php $_from = $this->_tpl_vars['depositPoints']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['depositPoint']):
?>
			depositPoints[<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
] = new Array();
			depositPoints[<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][0] = '<?php echo ((is_array($_tmp=$this->_tpl_vars['depositPoint']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
';
			depositPoints[<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][1] = '<?php echo ((is_array($_tmp=$this->_tpl_vars['depositPoint']['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
';
			depositPoints[<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][2] = '<?php if ($this->_tpl_vars['depositPoint']['password'] != ''): ?>1<?php else: ?>0<?php endif; ?>';
		<?php endforeach; endif; unset($_from); ?>
	<?php echo '
	var key = document.getElementById(\'articles\').depositPoint.options[document.getElementById(\'articles\').depositPoint.selectedIndex].value;
	document.getElementById(\'articles\').swordUrl.value = depositPoints[key][0];
	if (depositPoints[key][1] != \'\') {
		document.getElementById(\'articles\').swordUsername.value = depositPoints[key][1];
		document.getElementById(\'articles\').swordUsername.disabled = true;
	} else {
		document.getElementById(\'articles\').swordUsername.value = \'\';
		document.getElementById(\'articles\').swordUsername.disabled = false;
	}
	if (depositPoints[key][2] == 1) {
		document.getElementById(\'articles\').swordPassword.value = \'********\';
		document.getElementById(\'articles\').swordPassword.disabled = true;
	} else {
		document.getElementById(\'articles\').swordPassword.disabled = false;
	}

}

// -->
'; ?>

</script>

<br />
<form action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'deposit'), $this);?>
" method="post" id="articles">

<div id="settings">
<table width="100%" class="data">
	<tr valign="top">
		<td width="30%" class="label"><label for="depositPoint"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.sword.depositPoint"), $this);?>
</label></td>
		<td width="70%" class="value">
			<input type="hidden" name="swordUrl" value="" />
			<select class="selectMenu" name="depositPoint" id="depositPoint" onchange="changeDepositPoint()">
				<?php $_from = $this->_tpl_vars['depositPoints']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['depositPoint']):
?>
					<option value="<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['depositPoint']['url'] == $this->_tpl_vars['swordUrl']): ?>selected="selected" <?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['depositPoint']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>&nbsp;<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'plugin','path' => ((is_array($_tmp='generic')) ? $this->_run_mod_handler('to_array', true, $_tmp, 'SwordPlugin', 'settings') : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, 'SwordPlugin', 'settings'))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.sword.depositPoint.addRemove"), $this);?>
</a>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><label for="swordUsername"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
</label></td>
		<td class="value"><input type="text" id="swordUsername" name="swordUsername" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['swordUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><label for="swordPassword"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.password"), $this);?>
</label></td>
		<td class="value"><input type="password" id="swordPassword" name="swordPassword" value="" size="20" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><label for="swordDepositPoint"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.sword.depositPoint"), $this);?>
</label></td>
		<td class="value">
			<?php echo smarty_function_html_options(array('name' => 'swordDepositPoint','options' => $this->_tpl_vars['swordDepositPoints'],'selected' => $this->_tpl_vars['swordDepositPoint']), $this);?>

			<input type="button" onclick="document.getElementById('articles').action='<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array(), $this);?>
'; document.getElementById('articles').submit()" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.refresh"), $this);?>
" />
		</td>
	</tr>
	<tr valign="top">
		<td rowspan="2" class="label">
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.options"), $this);?>

		</td>
		<td class="value">
			<input type="checkbox" id="depositGalleys" name="depositGalleys" <?php if ($this->_tpl_vars['depositGalleys']): ?>checked="checked" <?php endif; ?>/>
			<label for="depositGalleys"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.sword.depositGalleys"), $this);?>
</label>
		</td>
	</tr>
	<tr valign="top">
		<td class="value">
			<input type="checkbox" id="depositEditorial" name="depositEditorial" <?php if ($this->_tpl_vars['depositEditorial']): ?>checked="checked" <?php endif; ?>/>
			<label for="depositEditorial"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.sword.depositEditorial"), $this);?>
</label>
		</td>
	</tr>
</table>
</div>

<div id="articles">
<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="5%">&nbsp;</td>
		<td width="30%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</td>
		<td width="40%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>
</td>
		<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>

	<?php $this->_tag_stack[] = array('iterate', array('from' => 'articles','item' => 'articleData')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->assign('article', $this->_tpl_vars['articleData']['article']); ?>
	<?php $this->assign('issue', $this->_tpl_vars['articleData']['issue']); ?>
	<tr valign="top">
		<td><input type="checkbox" name="articleId[]" value="<?php echo $this->_tpl_vars['article']->getId(); ?>
"/></td>
		<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a></td>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</td>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getAuthorString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
	</tr>
	<tr>
		<td colspan="4" class="<?php if ($this->_tpl_vars['articles']->eof()): ?>end<?php endif; ?>separator">&nbsp;</td>
	</tr>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php if ($this->_tpl_vars['articles']->wasEmpty()): ?>
	<tr>
		<td colspan="4" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="2" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['articles']), $this);?>
</td>
		<td colspan="3" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'articles','name' => 'articles','iterator' => $this->_tpl_vars['articles'],'swordUrl' => $this->_tpl_vars['swordUrl'],'swordUsername' => $this->_tpl_vars['swordUsername'],'swordPassword' => $this->_tpl_vars['swordPassword']), $this);?>
</td>
	</tr>
<?php endif; ?>
</table>
<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.sword.deposit"), $this);?>
" class="button defaultButton"/>&nbsp;<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.selectAll"), $this);?>
" class="button" onclick="toggleChecked()" /></p>
</form>
</div>

<script type="text/javascript">
<?php echo '
<!--
// Initialize form state with current deposit point
changeDepositPoint();
// -->
'; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>