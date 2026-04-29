<?php /* Smarty version 2.6.26, created on 2026-04-04 15:09:01
         compiled from manager/files/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/files/index.tpl', 17, false),array('function', 'url', 'manager/files/index.tpl', 20, false),array('function', 'icon', 'manager/files/index.tpl', 46, false),array('modifier', 'escape', 'manager/files/index.tpl', 17, false),array('modifier', 'explode', 'manager/files/index.tpl', 20, false),array('modifier', 'concat', 'manager/files/index.tpl', 40, false),array('modifier', 'default', 'manager/files/index.tpl', 48, false),array('modifier', 'date_format', 'manager/files/index.tpl', 49, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.filesBrowser"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php $this->assign('displayDir', "/".($this->_tpl_vars['currentDir'])); ?>
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.indexOfDir",'dir' => ((is_array($_tmp=$this->_tpl_vars['displayDir'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
</h3>

<?php if ($this->_tpl_vars['currentDir']): ?>
<p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'files','path' => ((is_array($_tmp=$this->_tpl_vars['parentDir'])) ? $this->_run_mod_handler('explode', true, $_tmp, "/") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "/"))), $this);?>
" class="action">&lt; <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.parentDir"), $this);?>
</a></p>
<?php endif; ?>

<table width="100%" class="listing">
	<tr>
		<td class="headseparator" colspan="6">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td></td>
		<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fileName"), $this);?>
</td>
		<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.type"), $this);?>
</td>
		<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.dateModified"), $this);?>
</td>
		<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.size"), $this);?>
</td>
		<td width="20%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
		<?php $_from = $this->_tpl_vars['files']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['files'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['files']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['file']):
        $this->_foreach['files']['iteration']++;
?>
	<?php if ($this->_tpl_vars['currentDir']): ?>
		<?php $this->assign('filePath', ((is_array($_tmp=$this->_tpl_vars['currentDir'])) ? $this->_run_mod_handler('concat', true, $_tmp, "/", $this->_tpl_vars['file']['name']) : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, "/", $this->_tpl_vars['file']['name']))); ?>
	<?php else: ?>
		<?php $this->assign('filePath', $this->_tpl_vars['file']['name']); ?>
	<?php endif; ?>
	<?php $this->assign('filePath', ((is_array($_tmp=$this->_tpl_vars['filePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?>
	<tr valign="top">
		<td><?php if ($this->_tpl_vars['file']['isDir']): ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'folder'), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'letter'), $this);?>
<?php endif; ?></td>
		<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'files','path' => ((is_array($_tmp=$this->_tpl_vars['filePath'])) ? $this->_run_mod_handler('explode', true, $_tmp, "/") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "/"))), $this);?>
"><?php echo $this->_tpl_vars['file']['name']; ?>
</a></td>
		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['file']['mimetype'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, "&mdash;") : smarty_modifier_default($_tmp, "&mdash;")); ?>
</td>
		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['file']['mtime'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatShort'])); ?>
</td>
		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['file']['size'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, "&mdash;") : smarty_modifier_default($_tmp, "&mdash;")); ?>
</td>
		<td align="right" class="nowrap">
			<?php if (! $this->_tpl_vars['file']['isDir']): ?>
				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'files','path' => ((is_array($_tmp=$this->_tpl_vars['filePath'])) ? $this->_run_mod_handler('explode', true, $_tmp, "/") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "/")),'download' => 1), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.download"), $this);?>
</a>&nbsp;|
			<?php endif; ?>
			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'fileDelete','path' => ((is_array($_tmp=$this->_tpl_vars['filePath'])) ? $this->_run_mod_handler('explode', true, $_tmp, "/") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "/"))), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a>
		</td>
	</tr>
	<?php if (($this->_foreach['files']['iteration'] == $this->_foreach['files']['total'])): ?>
	<tr>
		<td colspan="6" class="<?php if (($this->_foreach['files']['iteration'] == $this->_foreach['files']['total'])): ?>end<?php endif; ?>separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php endforeach; else: ?>
	<tr>
		<td colspan="6" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.emptyDir"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="6" class="endseparator">&nbsp;</td>
	</tr>
<?php endif; unset($_from); ?>
</table>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'fileUpload','path' => ((is_array($_tmp=$this->_tpl_vars['currentDir'])) ? $this->_run_mod_handler('explode', true, $_tmp, "/") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "/"))), $this);?>
" enctype="multipart/form-data">
	<input type="file" size="20" name="file" class="uploadField" />
	<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.uploadFile"), $this);?>
" class="button" />
</form>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'fileMakeDir','path' => ((is_array($_tmp=$this->_tpl_vars['currentDir'])) ? $this->_run_mod_handler('explode', true, $_tmp, "/") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "/"))), $this);?>
" enctype="multipart/form-data"  class="u-mt-16">
	<input type="text" size="20" maxlength="255" name="dirName" class="textField" />
	<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.createDir"), $this);?>
" class="button" />
</form>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.files.note"), $this);?>
</p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
