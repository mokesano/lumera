<?php /* Smarty version 2.6.26, created on 2026-04-04 05:48:04
         compiled from article/dublincore.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strip_tags', 'article/dublincore.tpl', 14, false),array('modifier', 'escape', 'article/dublincore.tpl', 14, false),array('modifier', 'String_substr', 'article/dublincore.tpl', 28, false),array('modifier', 'explode', 'article/dublincore.tpl', 37, false),array('modifier', 'date_format', 'article/dublincore.tpl', 78, false),array('function', 'url', 'article/dublincore.tpl', 108, false),array('function', 'translate', 'article/dublincore.tpl', 112, false),)), $this); ?>
<link rel="schema.dc" href="http://purl.org/dc/elements/1.1/" />

	<meta name="dc.type" content="Text.Serial.Journal" />
	<meta name="dc.source" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/>
<?php if ($this->_tpl_vars['currentJournal']->getSetting('onlineIssn')): ?><?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('onlineIssn')); ?>
<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('printIssn')): ?><?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('printIssn')); ?>
<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('issn')): ?><?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('issn')); ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['issn']): ?>
	<meta name="dc.source.ISSN" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issn'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>
    <meta name="citation_publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sekolah Tinggi Ilmu Pertanian Wuna<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" />
	<meta name="dc.source.URI" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getUrl())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	
	<meta name="dc.title" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />	
<?php $_from = $this->_tpl_vars['article']->getTitle(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['alternate']):
?>
	<?php if ($this->_tpl_vars['alternate'] != $this->_tpl_vars['article']->getLocalizedTitle()): ?>
		<meta name="dc.title.alternative" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['alternate'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<?php if ($this->_tpl_vars['article']->getAbstract(null)): ?><?php $_from = $this->_tpl_vars['article']->getAbstract(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['metaValue']):
?>
	<meta name="dc.description" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaValue'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?><?php endif; ?>

<?php if ($this->_tpl_vars['article']->getSubject(null)): ?><?php $_from = $this->_tpl_vars['article']->getSubject(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['metaValue']):
?>
	<?php $_from = ((is_array($_tmp=$this->_tpl_vars['metaValue'])) ? $this->_run_mod_handler('explode', true, $_tmp, "; ") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "; ")); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dcSubject']):
?>
		<?php if ($this->_tpl_vars['dcSubject']): ?>
			<meta name="dc.subject" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['dcSubject'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
<?php endforeach; endif; unset($_from); ?><?php endif; ?>

<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?>
	<meta name="dc.creator.personalname" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?>

<?php if ($this->_tpl_vars['article']->getSponsor(null)): ?><?php $_from = $this->_tpl_vars['article']->getSponsor(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['metaValue']):
?>
	<meta name="dc.contributor.sponsor" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaValue'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?><?php endif; ?>
<?php if ($this->_tpl_vars['article']->getCoverageSample(null)): ?><?php $_from = $this->_tpl_vars['article']->getCoverageSample(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['metaValue']):
?>
	<meta name="dc.coverage" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaValue'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?><?php endif; ?>
<?php if ($this->_tpl_vars['article']->getCoverageGeo(null)): ?><?php $_from = $this->_tpl_vars['article']->getCoverageGeo(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['metaValue']):
?>
	<meta name="dc.coverage.spatial" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaValue'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?><?php endif; ?>
<?php if ($this->_tpl_vars['article']->getCoverageChron(null)): ?><?php $_from = $this->_tpl_vars['article']->getCoverageChron(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['metaValue']):
?>
	<meta name="dc.coverage.temporal" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 2) : PKPString::substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaValue'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?><?php endif; ?>

	<meta name="dc.type.articleType" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getSectionTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php if ($this->_tpl_vars['issue']): ?>
	<meta name="dc.source.volume" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php if ($this->_tpl_vars['issue']->getNumber() != ""): ?>
	<meta name="dc.source.issue" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
<?php endif; ?>

	<meta name="dc.identifier" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php if ($this->_tpl_vars['article']->getPages()): ?>
	<meta name="dc.identifier.pageNumber" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php else: ?>	
	<meta name="dc.identifier.pageNumber" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getID())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>

<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getOpenAccessDate()): ?>
	<meta name="dc.date.available" scheme="ISO8601" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getOpenAccessDate())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>
<?php if (is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' ) && $this->_tpl_vars['article']->getDatePublished()): ?>
	<meta name="dc.date.created" scheme="ISO8601" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>
	<meta name="dc.date.dateSubmitted" scheme="ISO8601" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDateSubmitted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getDatePublished()): ?>
	<meta name="dc.date.issued" scheme="ISO8601" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>
	<meta name="dc.date.modified" scheme="ISO8601" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDateStatusModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />

<?php if (is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' )): ?><?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dcGalley']):
?>
	<meta name="dc.format" scheme="IMT" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['dcGalley']->getFileType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endforeach; endif; unset($_from); ?><?php endif; ?>

	<meta name="dc.language" scheme="ISO639-1" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	
<?php $_from = $this->_tpl_vars['pubIdPlugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pubIdPlugin']):
?>
	<?php if ($this->_tpl_vars['issue']->getPublished()): ?>
		<?php $this->assign('pubId', $this->_tpl_vars['pubIdPlugin']->getPubId($this->_tpl_vars['pubObject'])); ?>
	<?php else: ?>
		<?php $this->assign('pubId', $this->_tpl_vars['pubIdPlugin']->getPubId($this->_tpl_vars['pubObject'],true)); ?>	<?php endif; ?>
	<?php if ($this->_tpl_vars['pubId']): ?>
		<meta name="dc.identifier.<?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getPubIdDisplayType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['pubId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
	<meta name="dc.identifier.URI" content="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" />

	<meta name="dc.rights" content="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.copyrightStatement",'copyrightHolder' => ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCopyrightHolder())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'copyrightYear' => ((is_array($_tmp=$this->_tpl_vars['article']->getCopyrightYear())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" />
	<meta name="dc.rights" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLicenseURL())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />