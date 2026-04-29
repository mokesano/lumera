<?php /* Smarty version 2.6.26, created on 2026-04-04 15:32:16
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'iterate', 'file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl', 22, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl', 23, false),array('function', 'page_info', 'file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl', 32, false),array('function', 'page_links', 'file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl', 32, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl', 35, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/browse/templates/searchIndex.tpl', 23, false),)), $this); ?>
<?php echo ''; ?><?php if ($this->_tpl_vars['enableBrowseBySections']): ?><?php echo ''; ?><?php $this->assign('pageTitle', "plugins.generic.browse.search.sectionIndex"); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('pageTitle', "plugins.generic.browse.search.identifyTypeIndex"); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="results">
<?php if ($this->_tpl_vars['enableBrowseBySections']): ?>
<?php $this->_tag_stack[] = array('iterate', array('from' => 'results','key' => 'title','item' => 'id')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<h4><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'sections','path' => 'view','sectionId' => $this->_tpl_vars['id']), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></h4>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php else: ?>
<?php $this->_tag_stack[] = array('iterate', array('from' => 'results','item' => 'identifyType')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<h4><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'identifyTypes','path' => 'view','identifyType' => $this->_tpl_vars['identifyType']), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['identifyType'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></h4>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endif; ?>
<?php if (! $this->_tpl_vars['results']->wasEmpty()): ?>
	<br />
	<?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['results']), $this);?>
&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'results','iterator' => $this->_tpl_vars['results'],'name' => 'search'), $this);?>

<?php else: ?>
	<br />
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "search.noResults"), $this);?>

<?php endif; ?>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>