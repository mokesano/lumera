<?php /* Smarty version 2.6.26, created on 2026-04-06 02:28:02
         compiled from rt/context.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'capitalize', 'rt/context.tpl', 12, false),array('modifier', 'escape', 'rt/context.tpl', 46, false),array('modifier', 'strip_unsafe_html', 'rt/context.tpl', 74, false),array('modifier', 'trim', 'rt/context.tpl', 107, false),array('function', 'translate', 'rt/context.tpl', 77, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', ((is_array($_tmp=$this->_tpl_vars['context']->getTitle())) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp))); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "rt/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
<!--
	function addKeywords(formIndex) {
		var termsGet = \'\';
		var termsPost = \'\';

		var searchForm = document.forms[formIndex];

		// Get a list of search terms
		var elements = document.getElementById(\'terms\').elements;
		for (var i=0; i<elements.length; i++) {
			if (elements[i].type==\'text\') {
				var value = elements[i].value;

				if (value != \'\' && (i==0 || elements[i-1].type!=\'checkbox\' || elements[i-1].checked)) {
					if (termsGet != \'\') {
						termsGet += \'+AND+\';
						termsPost += \' AND \';
					}
					termsGet += value.replace(/ /g,\'+\');
					termsPost += value;
				}
			}
		}

		// Add the search terms to the action URL if necessary
		var newAction = searchForm.action;
		newAction = newAction.replace(/{\\$formKeywords}/g, termsGet);
		'; ?>
<?php $_from = $this->_tpl_vars['searchParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['param']):
?><?php echo '
		newAction = newAction.replace(/{\\$'; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['param'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '}/g, document.getElementById(\'additionalParams\').'; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['param'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '.value.replace(/ /g,\'+\'));
		'; ?>
<?php endforeach; endif; unset($_from); ?><?php echo '
		searchForm.action = newAction;

		// Add the search terms to the POST fields if necessary
		elements = searchForm.elements;
		for (var i=0; i<elements.length; i++) {
			if (elements[i].type==\'hidden\') {
				elements[i].value = elements[i].value.replace(/{\\$formKeywords}/g, termsPost);
				'; ?>
<?php $_from = $this->_tpl_vars['searchParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['param']):
?><?php echo '
				elements[i].value = elements[i].value.replace(/{\\$'; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['param'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '}/g, document.additionalParams.'; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['param'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '.value);
				'; ?>
<?php endforeach; endif; unset($_from); ?><?php echo '
			}
		}

		// Submit the form via POST or GET as appropriate.
		if (searchForm.method==\'post\') {
			searchForm.submit();
		} else {
			document.location = searchForm.action;
		}
		return true;
	}
// -->
'; ?>

</script>

<div id="context">
<h3><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</h3>


<p><?php if ($this->_tpl_vars['context']->getDefineTerms()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.defineTermsDescription"), $this);?>
<?php elseif ($this->_tpl_vars['context']->getAuthorTerms()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.authorTermsDescription"), $this);?>
<?php elseif ($this->_tpl_vars['context']->getCitedBy()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.citesContextDescription"), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.searchDescription"), $this);?>
<?php endif; ?></p>

<table class="data" width="100%">
	<form id="terms">
	<?php if ($this->_tpl_vars['context']->getDefineTerms()): ?>
		<tr valign="top">
			<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.termToDefine"), $this);?>
</td>
			<td width="80%" class="value"><input name="searchTerm" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['defineTerm'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" length="40" class="textField" />
		</tr>
	<?php elseif ($this->_tpl_vars['context']->getAuthorTerms() || $this->_tpl_vars['context']->getCitedBy()): ?>
		<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['author']):
?>
			<tr valign="top">
				<td width="20%" class="label" align="right">
					<input type="checkbox" checked="checked" style="checkbox" name="searchTerm<?php echo $this->_tpl_vars['key']+1; ?>
Check" value="1" />
				</td>
				<td width="80%" class="value">
					<input name="searchTerm<?php echo $this->_tpl_vars['key']+1; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" length="40" class="textField" />
				</td>
			</tr>
		<?php endforeach; endif; unset($_from); ?>
	<?php elseif ($this->_tpl_vars['context']->getGeoTerms()): ?>
		<tr valign="top">
			<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.termToDefine"), $this);?>
</td>
			<td width="80%" class="value"><input name="searchTerm" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverageGeo'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" length="40" class="textField" />
		</tr>
	<?php else: ?>
		<tr valign="top">
			<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.searchTerms"), $this);?>
</td>
			<td width="80%" class="value">
				<?php $_from = $this->_tpl_vars['keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['keywords'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['keywords']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['keyword']):
        $this->_foreach['keywords']['iteration']++;
?>
					<input name="searchTerm<?php echo $this->_tpl_vars['key']+1; ?>
" value="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['keyword'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" length="40" class="textField" />
					<?php if (! ($this->_foreach['keywords']['iteration'] == $this->_foreach['keywords']['total'])): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.context.and"), $this);?>
<?php endif; ?>
					<br />
				<?php endforeach; endif; unset($_from); ?>
			</td>
		</tr>
	<?php endif; ?>
	</form>


	<form id="additionalParams">
	<?php $_from = $this->_tpl_vars['searchValues']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramKey'] => $this->_tpl_vars['value']):
?>
		<tr valign="top">
			<td width="20%" class="label">
				<?php if ($this->_tpl_vars['paramKey'] == 'author'): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.author"), $this);?>

				<?php elseif ($this->_tpl_vars['paramKey'] == 'coverageGeo'): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.coverageGeo"), $this);?>

				<?php elseif ($this->_tpl_vars['paramKey'] == 'title'): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>

				<?php endif; ?>
			</td>
			<td width="80%" class="value">
					<input name="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['value'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" length="40" class="textField" />
			</td>
	<?php endforeach; endif; unset($_from); ?>
	</form>
</table>

<div class="separator"></div>

<table class="listing" width="100%">
	<?php $_from = $this->_tpl_vars['searches']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['searches'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['searches']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['search']):
        $this->_foreach['searches']['iteration']++;
?>
	<form id="search<?php echo $this->_tpl_vars['key']+1; ?>
form" method="<?php if ($this->_tpl_vars['search']->getSearchPost()): ?>post<?php else: ?>get<?php endif; ?>" action="<?php echo ((is_array($_tmp=$this->_tpl_vars['search']->getSearchUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
	<?php $_from = $this->_tpl_vars['search']->postParams; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['postParam']):
?>
		<input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['postParam']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['postParam']['value'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endforeach; endif; unset($_from); ?>
	<tr valign="top">
		<td width="10%">
			<input value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
" type="button" onclick="addKeywords(<?php echo $this->_tpl_vars['key']+2; ?>
);" class="button" />
		</td>
		<td width="2%"><?php echo $this->_tpl_vars['key']+1; ?>
.</td>
		<td width="88%"><?php echo ((is_array($_tmp=$this->_tpl_vars['search']->getTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <a target="_new" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['search']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.about"), $this);?>
</a></td>
	</tr>
	<tr><td colspan="3" class="<?php if (($this->_foreach['searches']['iteration'] == $this->_foreach['searches']['total'])): ?>end<?php endif; ?>separator">&nbsp;</td></tr>
	</form>
	<?php endforeach; endif; unset($_from); ?>
</table>

<a href="http://pkp.sfu.ca/ojs/suggestRt" target="_new" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.search.suggest"), $this);?>
</a><br/>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "rt/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
