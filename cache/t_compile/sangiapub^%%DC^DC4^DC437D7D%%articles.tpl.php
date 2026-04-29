<?php /* Smarty version 2.6.26, created on 2026-04-04 21:32:58
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 30, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 38, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 59, false),array('function', 'page_info', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 84, false),array('function', 'page_links', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 85, false),array('block', 'iterate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 47, false),array('modifier', 'strip_tags', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 59, false),array('modifier', 'strip_unsafe_html', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 60, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/datacite/templates/articles.tpl', 61, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.common.export.selectArticle"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "plugins.importexport.common.export.selectArticle"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript"><?php echo '
	function toggleChecked() {
		var elements = document.getElementById(\'articlesForm\').elements;
		for (var i=0; i < elements.length; i++) {
			if (elements[i].name == \'articleId[]\') {
				elements[i].checked = !elements[i].checked;
			}
		}
	}
'; ?>
</script>

<br />

<div id="articles">
	<form action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'process'), $this);?>
" method="post" id="articlesForm">
		<input type="hidden" name="target" value="article" />
		<table width="100%" class="listing">
			<tr>
				<td colspan="5" class="headseparator">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td width="5%">&nbsp;</td>
				<td width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</td>
				<td width="45%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>
</td>
				<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</td>
				<td width="5%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
			</tr>
			<tr>
				<td colspan="5" class="headseparator">&nbsp;</td>
			</tr>

			<?php $this->_tag_stack[] = array('iterate', array('from' => 'articles','item' => 'articleData')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<?php $this->assign('article', $this->_tpl_vars['articleData']['article']); ?>
				<?php $this->assign('issue', $this->_tpl_vars['articleData']['issue']); ?>
				<?php if ($this->_tpl_vars['article']->getData('datacite::registeredDoi')): ?>
					<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.update"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('updateOrRegister', ob_get_contents());ob_end_clean(); ?>
					<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.updateDescription"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('updateOrRegisterDescription', ob_get_contents());ob_end_clean(); ?>
				<?php else: ?>
					<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('updateOrRegister', ob_get_contents());ob_end_clean(); ?>
					<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.registerDescription"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('updateOrRegisterDescription', ob_get_contents());ob_end_clean(); ?>
				<?php endif; ?>
				<tr valign="top">
					<td><input type="checkbox" name="articleId[]" value="<?php echo $this->_tpl_vars['article']->getId(); ?>
"/></td>
					<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
</a></td>
					<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a></td>
					<td><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getAuthorString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
					<td align="right"><nobr>
						<?php if ($this->_tpl_vars['hasCredentials']): ?>
							<a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'process','articleId' => $this->_tpl_vars['article']->getId(),'params' => $this->_tpl_vars['testMode'],'target' => 'article','register' => true), $this);?>
" title="<?php echo $this->_tpl_vars['updateOrRegisterDescription']; ?>
" class="action"><?php echo $this->_tpl_vars['updateOrRegister']; ?>
</a>
						<?php endif; ?>
						<a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'process','articleId' => $this->_tpl_vars['article']->getId(),'params' => $this->_tpl_vars['testMode'],'target' => 'article','export' => true), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.exportDescription"), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.export"), $this);?>
</a>
					</nobr></td>
				</tr>
				<?php if ($this->_tpl_vars['articles']->eof()): ?>
				<tr>
					<td colspan="5" class="<?php if ($this->_tpl_vars['articles']->eof()): ?>end<?php endif; ?>separator">&nbsp;</td>
				</tr>
				<?php endif; ?>
			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			<?php if ($this->_tpl_vars['articles']->wasEmpty()): ?>
				<tr>
					<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.noArticles"), $this);?>
</td>
				</tr>
				<tr>
					<td colspan="5" class="endseparator">&nbsp;</td>
				</tr>
			<?php else: ?>
				<tr>
					<td colspan="2" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['articles']), $this);?>
</td>
					<td colspan="3" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'articles','name' => 'articles','iterator' => $this->_tpl_vars['articles']), $this);?>
</td>
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