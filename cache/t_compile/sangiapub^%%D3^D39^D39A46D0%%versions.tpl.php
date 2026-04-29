<?php /* Smarty version 2.6.26, created on 2026-04-05 05:53:48
         compiled from rtadmin/versions.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'rtadmin/versions.tpl', 22, false),array('function', 'url', 'rtadmin/versions.tpl', 30, false),array('function', 'page_info', 'rtadmin/versions.tpl', 43, false),array('function', 'page_links', 'rtadmin/versions.tpl', 44, false),array('block', 'iterate', 'rtadmin/versions.tpl', 26, false),array('modifier', 'escape', 'rtadmin/versions.tpl', 28, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "rt.versions"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<br/>

<div id="versions">
<table class="listing" width="100%">
	<tr><td class="headseparator" colspan="3">&nbsp;</td></tr>
	<tr valign="top">
		<td class="heading" width="40%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.version.title"), $this);?>
</td>
		<td class="heading" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.version.locale"), $this);?>
</td>
		<td class="heading" width="40%" align="right">&nbsp;</td>
	</tr>
	<?php $this->_tag_stack[] = array('iterate', array('from' => 'versions','item' => 'version')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<tr valign="top">
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
			<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getLocale())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
			<td align="right"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'validateUrls','path' => $this->_tpl_vars['version']->getVersionId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.validateUrls.validate"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editVersion','path' => $this->_tpl_vars['version']->getVersionId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.metadata"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'contexts','path' => $this->_tpl_vars['version']->getVersionId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.contexts"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'exportVersion','path' => $this->_tpl_vars['version']->getVersionId()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.export"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'deleteVersion','path' => $this->_tpl_vars['version']->getVersionId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a></td>
		</tr>
		<?php if ($this->_tpl_vars['versions']->eof()): ?>
		<tr class=""><td class="<?php if ($this->_tpl_vars['versions']->eof()): ?>end<?php endif; ?>separator" colspan="3"></td></tr>
		<?php endif; ?>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<?php if ($this->_tpl_vars['versions']->wasEmpty()): ?>
		<tr valign="top">
			<td class="nodata" colspan="3"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
		</tr>
		<tr><td class="endseparator" colspan="3"></td></tr>
	<?php else: ?>
		<tr>
			<td align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['versions']), $this);?>
</td>
			<td colspan="2" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'versions','name' => 'versions','iterator' => $this->_tpl_vars['versions']), $this);?>
</td>
		</tr>
	<?php endif; ?>
	</table>
</div>	
<br/>

<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createVersion'), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.createVersion"), $this);?>
</a><br/>

<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'restoreVersions'), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.confirmRestore"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.restoreVersions"), $this);?>
</a>

<br/>
<br/>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'importVersion'), $this);?>
" enctype="multipart/form-data">
	<input type="file" class="uploadField" name="versionFile" />
	<input type="submit" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.versions.importVersion"), $this);?>
" />
</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
