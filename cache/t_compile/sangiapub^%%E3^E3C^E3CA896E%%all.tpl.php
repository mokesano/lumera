<?php /* Smarty version 2.6.26, created on 2026-04-04 21:29:13
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 30, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 39, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 53, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 54, false),array('modifier', 'strip_unsafe_html', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 54, false),array('modifier', 'nl2br', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 54, false),array('modifier', 'strip_tags', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 73, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 75, false),array('modifier', 'to_array', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 95, false),array('modifier', 'cat', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 95, false),array('modifier', 'default', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/all.tpl', 116, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.common.export.selectUnregistered"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "plugins.importexport.common.export.selectUnregistered"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript"><?php echo '
	function toggleChecked() {
		var elements = document.getElementById(\'exportAllForm\').elements;
		for (var i=0; i < elements.length; i++) {
			if (elements[i].name == \'issueId[]\' || elements[i].name == \'articleId[]\' || elements[i].name == \'galleyId[]\' || elements[i].name == \'suppFileId[]\') {
				elements[i].checked = !elements[i].checked;
			}
		}
	}
'; ?>
</script>

<br />

<div id="allUnregistered">
	<form action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'process'), $this);?>
" method="post" id="exportAllForm">
		<input type="hidden" name="target" value="all" />
		<table width="100%" class="listing">
			<tr>
				<td colspan="5" class="headseparator">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td width="5%">&nbsp;</td>
				<td width="5%">&nbsp;</td>
				<td width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</td>
				<td width="45%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>
</td>
				<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</td>
			</tr>
			<tr>
				<td colspan="5" class="headseparator">&nbsp;</td>
			</tr>

			<?php $this->assign('noObjects', true); ?>
			<?php $_from = $this->_tpl_vars['issues']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['issue']):
?>
				<?php if ($this->_tpl_vars['issue']->getPubId('doi')): ?>
					<?php $this->assign('noObjects', false); ?>
					<tr valign="top">
						<td><input type="checkbox" name="issueId[]" value="<?php echo $this->_tpl_vars['issue']->getId(); ?>
" checked="checked" /></td>
						<td><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "issueId[]",'key' => "issue.issue"), $this);?>
</td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a></td>
						<td>&mdash;</td>
						<td>&mdash;</td>
					</tr>
					<?php if ($this->_foreach['issues']): ?>
					<tr>
						<td colspan="5" class="separator">&nbsp;</td>
					</tr>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
			<?php $_from = $this->_tpl_vars['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['articleData']):
?>
				<?php $this->assign('article', $this->_tpl_vars['articleData']['article']); ?>
				<?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
					<?php $this->assign('noObjects', false); ?>
					<?php $this->assign('issue', $this->_tpl_vars['articleData']['issue']); ?>
					<tr valign="top">
						<td><input type="checkbox" name="articleId[]" value="<?php echo $this->_tpl_vars['article']->getId(); ?>
" checked="checked" /></td>
						<td><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "articleId[]",'key' => "article.article"), $this);?>
</td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
</a></td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a></td>
						<td><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getAuthorString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
					</tr>
					<?php if ($this->_foreach['articles']): ?>
					<tr>
						<td colspan="5" class="separator">&nbsp;</td>
					</tr>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
			<?php $_from = $this->_tpl_vars['galleys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleys'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleys']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galleyData']):
        $this->_foreach['galleys']['iteration']++;
?>
				<?php $this->assign('galley', $this->_tpl_vars['galleyData']['galley']); ?>
				<?php if ($this->_tpl_vars['galley']->getPubId('doi')): ?>
					<?php $this->assign('noObjects', false); ?>
					<?php $this->assign('language', $this->_tpl_vars['galleyData']['language']); ?>
					<?php $this->assign('article', $this->_tpl_vars['galleyData']['article']); ?>
					<?php $this->assign('issue', $this->_tpl_vars['galleyData']['issue']); ?>
					<tr valign="top">
						<td><input type="checkbox" name="galleyId[]" value="<?php echo $this->_tpl_vars['galley']->getId(); ?>
" checked="checked" /></td>
						<td><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "galleyId[]",'key' => "submission.galley"), $this);?>
</td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
</a></td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getId()))), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('cat', true, $_tmp, ' (') : smarty_modifier_cat($_tmp, ' (')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['galley']->getLabel()) : smarty_modifier_cat($_tmp, $this->_tpl_vars['galley']->getLabel())))) ? $this->_run_mod_handler('cat', true, $_tmp, ', ') : smarty_modifier_cat($_tmp, ', ')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['language']->getName()) : smarty_modifier_cat($_tmp, $this->_tpl_vars['language']->getName())))) ? $this->_run_mod_handler('cat', true, $_tmp, ')') : smarty_modifier_cat($_tmp, ')')))) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a></td>
						<td><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getAuthorString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
					</tr>
					<?php if (($this->_foreach['galleys']['iteration'] == $this->_foreach['galleys']['total'])): ?>
					<tr>
						<td colspan="5" class="separator">&nbsp;</td>
					</tr>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
			<?php $_from = $this->_tpl_vars['suppFiles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['suppFiles'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['suppFiles']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['suppFileData']):
        $this->_foreach['suppFiles']['iteration']++;
?>
				<?php $this->assign('suppFile', $this->_tpl_vars['suppFileData']['suppFile']); ?>
				<?php if ($this->_tpl_vars['suppFile']->getPubId('doi')): ?>
					<?php $this->assign('noObjects', false); ?>
					<?php $this->assign('article', $this->_tpl_vars['suppFileData']['article']); ?>
					<?php $this->assign('issue', $this->_tpl_vars['suppFileData']['issue']); ?>
					<tr valign="top">
						<td><input type="checkbox" name="suppFileId[]" value="<?php echo $this->_tpl_vars['suppFile']->getId(); ?>
" checked="checked" /></td>
						<td><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "suppFileId[]",'key' => "article.suppFile"), $this);?>
</td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
</a></td>
						<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'suppFileMetadata','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, 0, $this->_tpl_vars['suppFile']->getId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, 0, $this->_tpl_vars['suppFile']->getId()))), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('cat', true, $_tmp, ' (') : smarty_modifier_cat($_tmp, ' (')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['suppFile']->getSuppFileTitle()) : smarty_modifier_cat($_tmp, $this->_tpl_vars['suppFile']->getSuppFileTitle())))) ? $this->_run_mod_handler('cat', true, $_tmp, ')') : smarty_modifier_cat($_tmp, ')')))) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a></td>
						<td><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['suppFile']->getSuppFileCreator())) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['article']->getAuthorString()) : smarty_modifier_default($_tmp, @$this->_tpl_vars['article']->getAuthorString())))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
					</tr>
					<?php if (($this->_foreach['suppFiles']['iteration'] == $this->_foreach['suppFiles']['total'])): ?>
					<tr>
						<td colspan="5" class="<?php if (($this->_foreach['suppFiles']['iteration'] == $this->_foreach['suppFiles']['total'])): ?>end<?php endif; ?>separator">&nbsp;</td>
					</tr>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
			<?php if ($this->_tpl_vars['noObjects']): ?>
				<tr>
					<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.noUnregistered"), $this);?>
</td>
				</tr>
				<tr>
					<td colspan="5" class="endseparator">&nbsp;</td>
				</tr>
			<?php endif; ?>
		</table>
		<p>
			<?php if (! empty ( $this->_tpl_vars['testMode'] )): ?><input type="hidden" name="testMode" value="1" /><?php endif; ?>
			<?php if ($this->_tpl_vars['hasCredentials']): ?>
				<input type="submit" name="register" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register"), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.registerDescription.multi"), $this);?>
" class="button defaultButton"/>
				&nbsp;
			<?php endif; ?>
			<input type="submit" name="export" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.export"), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.exportDescription"), $this);?>
" class="button<?php if (! $this->_tpl_vars['hasCredentials']): ?>  defaultButton<?php endif; ?>"/>
			&nbsp;
			<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.selectAll"), $this);?>
" class="button" onclick="toggleChecked()" />
		</p>
		<p>
			<?php if ($this->_tpl_vars['hasCredentials']): ?>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register.warning"), $this);?>

			<?php else: ?>
				<?php ob_start(); ?><?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('settingsUrl', ob_get_contents());ob_end_clean(); ?>
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register.noCredentials",'settingsUrl' => $this->_tpl_vars['settingsUrl']), $this);?>

			<?php endif; ?>
		</p>
	</form>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>