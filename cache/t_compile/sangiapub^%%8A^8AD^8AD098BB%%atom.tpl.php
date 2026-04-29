<?php /* Smarty version 2.6.26, created on 2026-04-07 23:48:30
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl', 1, false),array('modifier', 'strip', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl', 15, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl', 26, false),array('modifier', 'regex_replace', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl', 26, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl', 14, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/atom.tpl', 89, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<feed xmlns="http://www.w3.org/2005/Atom">
		<id><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'feed'), $this);?>
</id>
	<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>

		<?php $this->assign('latestDate', $this->_tpl_vars['issue']->getDatePublished()); ?>
	<?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>
		<?php $_from = $this->_tpl_vars['section']['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
			<?php if ($this->_tpl_vars['article']->getLastModified() > $this->_tpl_vars['latestDate']): ?>
				<?php $this->assign('latestDate', $this->_tpl_vars['article']->getLastModified()); ?>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
	<?php endforeach; endif; unset($_from); ?>
	<updated><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['latestDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</updated>

		<?php if ($this->_tpl_vars['journal']->getSetting('contactName')): ?>
		<author>
			<name><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getSetting('contactName'))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</name>
			<?php if ($this->_tpl_vars['journal']->getSetting('contactEmail')): ?>
			<email><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getSetting('contactEmail'))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</email>
			<?php endif; ?>
		</author>
	<?php endif; ?>

	<link rel="alternate" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<link rel="self" type="application/atom+xml" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'feed','op' => 'atom'), $this);?>
" />

	
		
	<generator uri="http://pkp.sfu.ca/ojs/" version="<?php echo ((is_array($_tmp=$this->_tpl_vars['ojsVersion'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">Open Journal Systems</generator>
	<?php if ($this->_tpl_vars['journal']->getLocalizedDescription()): ?>
		<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedDescription()); ?>
	<?php elseif ($this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')): ?>
		<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')); ?>
	<?php endif; ?>

	<subtitle type="html"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['description'])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</subtitle>

	<?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sectionId'] => $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>
		<?php $_from = $this->_tpl_vars['section']['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
			<entry>
								<id><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
</id>
				<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>
				<updated><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLastModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</updated>

				
				<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorList']['iteration']++;
?>
					<author>
						<name><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</name>
						<?php if ($this->_tpl_vars['author']->getEmail()): ?>
							<email><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['author']->getEmail())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</email>
						<?php endif; ?>
					</author>
				<?php endforeach; endif; unset($_from); ?>
				<link rel="alternate" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
" />

				<?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
					<summary type="html" xml:base="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</summary>
				<?php endif; ?>

												
				<?php if ($this->_tpl_vars['article']->getDatePublished()): ?>
					<published><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</published>
				<?php endif; ?>

								<rights><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.copyrightStatement",'copyrightYear' => $this->_tpl_vars['article']->getCopyrightYear(),'copyrightHolder' => $this->_tpl_vars['article']->getLocalizedCopyrightHolder()), $this))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp));?>
</rights>
			</entry>
		<?php endforeach; endif; unset($_from); ?>	<?php endforeach; endif; unset($_from); ?></feed>