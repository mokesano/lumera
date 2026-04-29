<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:06
         compiled from file:/home/sangiaor/public_html/journals/plugins/blocks/keywordCloud/block.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/keywordCloud/block.tpl', 12, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/blocks/keywordCloud/block.tpl', 14, false),array('function', 'math', 'file:/home/sangiaor/public_html/journals/plugins/blocks/keywordCloud/block.tpl', 14, false),)), $this); ?>
<div class="block" id="sidebarKeywordCloud">
	<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.keywordCloud.title"), $this);?>
</span>
	<?php $_from = $this->_tpl_vars['cloudKeywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['cloud'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cloud']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['keyword'] => $this->_tpl_vars['count']):
        $this->_foreach['cloud']['iteration']++;
?>
		<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','subject' => $this->_tpl_vars['keyword']), $this);?>
"><span style="font-size: <?php echo smarty_function_math(array('equation' => "round(((x-1) / y * 100)+75)",'x' => $this->_tpl_vars['count'],'y' => $this->_tpl_vars['maxOccurs']), $this);?>
%;"><?php echo $this->_tpl_vars['keyword']; ?>
</span></a>
	<?php endforeach; endif; unset($_from); ?>
</div>