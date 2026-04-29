<?php /* Smarty version 2.6.26, created on 2026-04-04 21:39:29
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 30, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 30, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 69, false),array('function', 'page_info', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 109, false),array('function', 'page_links', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 110, false),array('block', 'iterate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 60, false),array('modifier', 'default', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 62, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 68, false),array('modifier', 'strip_tags', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 69, false),array('modifier', 'nl2br', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 70, false),array('modifier', 'truncate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/articles.tpl', 71, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.crossref.manageArticleDOIs"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "plugins.importexport.crossref.manageArticleDOIs"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-manager.tpl", 'smarty_include_vars' => array()));
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
	<div><a class="link-button" href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'issues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.manageIssues"), $this);?>
</a></div>
	<br />
	<ul class="menu">
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles'), $this);?>
"<?php if (! $this->_tpl_vars['filter']): ?> class="current"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.all"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles','filter' => @CROSSREF_STATUS_NOT_DEPOSITED), $this);?>
"<?php if ($this->_tpl_vars['filter'] == @CROSSREF_STATUS_NOT_DEPOSITED): ?> class="current"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.non"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles','filter' => @CROSSREF_STATUS_FAILED), $this);?>
"<?php if ($this->_tpl_vars['filter'] == @CROSSREF_STATUS_FAILED): ?> class="current"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.failed"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles','filter' => @CROSSREF_STATUS_SUBMITTED), $this);?>
"<?php if ($this->_tpl_vars['filter'] == @CROSSREF_STATUS_SUBMITTED): ?> class="current"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.submitted"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles','filter' => @CROSSREF_STATUS_COMPLETED), $this);?>
"<?php if ($this->_tpl_vars['filter'] == @CROSSREF_STATUS_COMPLETED): ?> class="current"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.completed"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles','filter' => @CROSSREF_STATUS_REGISTERED), $this);?>
"<?php if ($this->_tpl_vars['filter'] == @CROSSREF_STATUS_REGISTERED): ?> class="current"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.registered"), $this);?>
</a></li>
	</ul>
	<br />
	<form action="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'process'), $this);?>
" method="post" id="articlesForm">
		<input type="hidden" name="target" value="article" />
		<table width="100%" class="listing">
			<tr>
				<td colspan="7" class="headseparator">&nbsp;</td>
			</tr>
			<tr class="heading" valign="bottom">
				<td width="5%">&nbsp;</td>
				<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.id"), $this);?>
</td>
				<td width="15%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</td>
				<td width="30%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>
</td>
				<td width="25%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</td>
				<td width="10%">DOI</td>
				<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.status"), $this);?>
</td>
			</tr>
			<tr>
				<td colspan="7" class="headseparator">&nbsp;</td>
			</tr>

			<?php $this->_tag_stack[] = array('iterate', array('from' => 'articles','item' => 'articleData')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<?php $this->assign('article', $this->_tpl_vars['articleData']['article']); ?>
				<?php $this->assign('status', ((is_array($_tmp=@$this->_tpl_vars['article']->getData($this->_tpl_vars['depositStatusSettingName']))) ? $this->_run_mod_handler('default', true, $_tmp, @CROSSREF_STATUS_NOT_DEPOSITED) : smarty_modifier_default($_tmp, @CROSSREF_STATUS_NOT_DEPOSITED))); ?>
				<?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
					<?php if (( $this->_tpl_vars['filter'] && $this->_tpl_vars['filter'] == $this->_tpl_vars['status'] ) || ! $this->_tpl_vars['filter']): ?>
						<?php $this->assign('issue', $this->_tpl_vars['articleData']['issue']); ?>
						<tr valign="top">
							<td><input type="checkbox" name="articleId[]" value="<?php echo $this->_tpl_vars['article']->getId(); ?>
"<?php if ($this->_tpl_vars['status'] == @CROSSREF_STATUS_NOT_DEPOSITED): ?> checked="checked"<?php endif; ?> /></td>
							<td><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
							<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
</a></td>
							<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getId()), $this);?>
" class="action truncate-title"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a></td>
							<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getAuthorString())) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
							<td>
								<?php if ($this->_tpl_vars['isEditor']): ?>
									<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'viewMetadata','path' => $this->_tpl_vars['article']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></td>
								<?php else: ?>
									<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

								<?php endif; ?>
							<td>
								<?php if ($this->_tpl_vars['status'] == @CROSSREF_STATUS_NOT_DEPOSITED): ?>
									<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.status.non"), $this);?>

								<?php else: ?>
									<input type="hidden" name="filter" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['filter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
									<a href="https://api.crossref.org<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getData($this->_tpl_vars['depositStatusUrlSettingName']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['statusMapping'][$this->_tpl_vars['status']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></td>
								<?php endif; ?>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['articles']->eof()): ?>
				<tr>
					<td colspan="7" class="<?php if ($this->_tpl_vars['articles']->eof()): ?>end<?php endif; ?>separator">&nbsp;</td>
				</tr>
				<?php endif; ?>
			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			<?php if ($this->_tpl_vars['articles']->wasEmpty()): ?>
				<tr>
					<td colspan="7" class="nodata">
						<?php if (! $this->_tpl_vars['filter']): ?>
							<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.noArticles"), $this);?>

						<?php else: ?>
							<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.articles.".($this->_tpl_vars['filter'])), $this);?>

						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td colspan="7" class="endseparator">&nbsp;</td>
				</tr>
			<?php else: ?>
				<tr>
					<td colspan="3" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['articles']), $this);?>
</td>
					<td colspan="4" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'articles','name' => 'articles','iterator' => $this->_tpl_vars['articles'],'filter' => $this->_tpl_vars['filter']), $this);?>
</td>
				</tr>
			<?php endif; ?>
		</table>
		<p>
			<?php if ($this->_tpl_vars['hasCredentials']): ?>
				<input type="submit" name="register" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register"), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.registerDescription.multi"), $this);?>
" class="button defaultButton"/>
				&nbsp;
				<input type="submit" name="checkStatus" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.checkStatus"), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.checkStatusDescription"), $this);?>
" class="button"/>
				&nbsp;
			<?php endif; ?>
			<input type="submit" name="export" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.downloadXML"), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.exportDescription"), $this);?>
" class="button<?php if (! $this->_tpl_vars['hasCredentials']): ?>  defaultButton<?php endif; ?>"/>
			&nbsp;
			<input type="submit" name="markRegistered" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.markRegistered"), $this);?>
" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.markRegisteredDescription"), $this);?>
" class="button"/>
			&nbsp;
			<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.selectAll"), $this);?>
" class="button" onclick="toggleChecked()" />
		</p>
		<div class="warning-message doi">
    		<p>
    			<?php if ($this->_tpl_vars['hasCredentials']): ?>
    				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register.warning"), $this);?>

    			<?php else: ?>
    				<?php ob_start(); ?><?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('settingsUrl', ob_get_contents());ob_end_clean(); ?>
    				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.register.noCredentials",'settingsUrl' => $this->_tpl_vars['settingsUrl']), $this);?>

    			<?php endif; ?>
    		</p>
		</div>
	</form>
	<div class="status-legend doi">
	    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.statusLegend"), $this);?>

	</div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>