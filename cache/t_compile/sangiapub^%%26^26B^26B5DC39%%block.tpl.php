<?php /* Smarty version 2.6.26, created on 2026-04-04 06:02:55
         compiled from file:/home/sangiaor/public_html/journals/plugins/blocks/authorBios/block.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/authorBios/block.tpl', 15, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/blocks/authorBios/block.tpl', 23, false),array('modifier', 'strip_unsafe_html', 'file:/home/sangiaor/public_html/journals/plugins/blocks/authorBios/block.tpl', 31, false),array('modifier', 'nl2br', 'file:/home/sangiaor/public_html/journals/plugins/blocks/authorBios/block.tpl', 31, false),)), $this); ?>

<div class="block" id="sidebarRTAuthorBios">
	<span class="blockTitle">
		<?php if (count ( $this->_tpl_vars['article']->getAuthors() ) > 1): ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.authorBios.aboutTheAuthors"), $this);?>

		<?php else: ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.authorBios.aboutTheAuthor"), $this);?>

		<?php endif; ?>
	</span>
	<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?>
	<div class="authorBio">
	<p>
		<em><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</em>
		<?php if ($this->_tpl_vars['author']->getData('orcid')): ?><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="orcid"><img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/authorBios/orcid.png" alt="orcid" /></a><?php endif; ?>
		<?php if ($this->_tpl_vars['author']->getUrl()): ?><br /><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'quotes')); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><br /><?php endif; ?>
		<?php $this->assign('authorAffiliation', $this->_tpl_vars['author']->getLocalizedAffiliation()); ?>
		<?php if ($this->_tpl_vars['authorAffiliation']): ?><br /><?php echo ((is_array($_tmp=$this->_tpl_vars['authorAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
		<?php if ($this->_tpl_vars['author']->getCountry()): ?><br /><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getCountryLocalized())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
	</p>

	<p><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['author']->getLocalizedBiography())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
	</div>
	<?php if (! ($this->_foreach['authors']['iteration'] == $this->_foreach['authors']['total'])): ?><div class="separator"></div><?php endif; ?>

	<?php endforeach; endif; unset($_from); ?>
</div>