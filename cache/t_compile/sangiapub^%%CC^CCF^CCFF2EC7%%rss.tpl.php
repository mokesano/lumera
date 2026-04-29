<?php /* Smarty version 2.6.26, created on 2026-04-05 21:57:18
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl', 1, false),array('modifier', 'strip', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl', 21, false),array('modifier', 'replace', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl', 39, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl', 97, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl', 62, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/webFeed/templates/rss.tpl', 87, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns="http://purl.org/rss/1.0/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:prism="http://prismstandard.org/namespaces/1.2/basic/"
	xmlns:cc="http://web.resource.org/cc/">

	<channel rdf:about="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
				<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>
		<link><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</link>

		<?php if ($this->_tpl_vars['journal']->getLocalizedDescription()): ?>
			<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedDescription()); ?>
		<?php elseif ($this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')): ?>
			<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')); ?>
		<?php endif; ?>

		<description><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['description'])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</description>

				<?php $this->assign('publisherInstitution', $this->_tpl_vars['journal']->getSetting('publisherInstitution')); ?>
		<?php if ($this->_tpl_vars['publisherInstitution']): ?>
			<dc:publisher><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['publisherInstitution'])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</dc:publisher>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['journal']->getPrimaryLocale()): ?>
			<dc:language><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getPrimaryLocale())) ? $this->_run_mod_handler('replace', true, $_tmp, '_', '-') : smarty_modifier_replace($_tmp, '_', '-')))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</dc:language>
		<?php endif; ?>

		<prism:publicationName><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</prism:publicationName>

		<?php if ($this->_tpl_vars['journal']->getSetting('printIssn')): ?>
			<?php $this->assign('ISSN', $this->_tpl_vars['journal']->getSetting('printIssn')); ?>
		<?php elseif ($this->_tpl_vars['journal']->getSetting('onlineIssn')): ?>
			<?php $this->assign('ISSN', $this->_tpl_vars['journal']->getSetting('onlineIssn')); ?>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['ISSN']): ?>
			<prism:issn><?php echo ((is_array($_tmp=$this->_tpl_vars['ISSN'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</prism:issn>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['journal']->getLocalizedSetting('copyrightNotice')): ?>
			<prism:copyright><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('copyrightNotice'))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</prism:copyright>
		<?php endif; ?>

		<items>
			<rdf:Seq>
			<?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sectionId'] => $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>
				<?php $_from = $this->_tpl_vars['section']['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
					<rdf:li rdf:resource="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
"/>
				<?php endforeach; endif; unset($_from); ?>			<?php endforeach; endif; unset($_from); ?>			</rdf:Seq>
		</items>
	</channel>

<?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sectionId'] => $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>
	<?php $_from = $this->_tpl_vars['section']['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
		<item rdf:about="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
">

						<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>
			<link><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
</link>

						<?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
				<description><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</description>
			<?php endif; ?>

			<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorList']['iteration']++;
?>
				<dc:creator><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</dc:creator>
			<?php endforeach; endif; unset($_from); ?>

			<dc:rights>
				<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.copyrightStatement",'copyrightYear' => $this->_tpl_vars['article']->getCopyrightYear(),'copyrightHolder' => $this->_tpl_vars['article']->getLocalizedCopyrightHolder()), $this))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp));?>

				<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLicenseURL())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

			</dc:rights>
			<?php if (( $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || ( $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_ISSUE_DEFAULT && $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN ) ) && $this->_tpl_vars['article']->isCCLicense()): ?>
				<cc:license rdf:resource="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLicenseURL())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
			<?php else: ?>
				<cc:license></cc:license>
			<?php endif; ?>

			<?php if ($this->_tpl_vars['article']->getDatePublished()): ?>
				<dc:date><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
</dc:date>
				<prism:publicationDate><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
</prism:publicationDate>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['issue']->getVolume()): ?><prism:volume><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</prism:volume><?php endif; ?>
			<?php if ($this->_tpl_vars['issue']->getNumber()): ?><prism:number><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</prism:number><?php endif; ?>

			<?php if ($this->_tpl_vars['article']->getPages()): ?>
				<?php if ($this->_tpl_vars['article']->getStartingPage()): ?>
					<prism:startingPage><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getStartingPage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</prism:startingPage>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['article']->getEndingPage()): ?>
					<prism:endingPage><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getEndingPage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</prism:endingPage>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
				<prism:doi><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</prism:doi>
			<?php endif; ?>
		</item>
	<?php endforeach; endif; unset($_from); ?><?php endforeach; endif; unset($_from); ?>
</rdf:RDF>
