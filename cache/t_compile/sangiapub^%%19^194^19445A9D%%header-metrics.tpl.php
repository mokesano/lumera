<?php /* Smarty version 2.6.26, created on 2026-04-16 10:06:17
         compiled from article/header-metrics.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'article/header-metrics.tpl', 2, false),array('modifier', 'strip_tags', 'article/header-metrics.tpl', 13, false),array('modifier', 'escape', 'article/header-metrics.tpl', 13, false),array('modifier', 'replace', 'article/header-metrics.tpl', 19, false),array('modifier', 'date_format', 'article/header-metrics.tpl', 19, false),array('modifier', 'count_characters', 'article/header-metrics.tpl', 19, false),array('modifier', 'string_format', 'article/header-metrics.tpl', 19, false),array('modifier', 'nl2br', 'article/header-metrics.tpl', 32, false),array('modifier', 'truncate', 'article/header-metrics.tpl', 35, false),array('modifier', 'strip_unsafe_html', 'article/header-metrics.tpl', 97, false),array('modifier', 'assign', 'article/header-metrics.tpl', 173, false),array('function', 'translate', 'article/header-metrics.tpl', 13, false),array('function', 'url', 'article/header-metrics.tpl', 85, false),array('function', 'call_hook', 'article/header-metrics.tpl', 100, false),array('block', 'iterate', 'article/header-metrics.tpl', 429, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<head>
	<title><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.articleMetrics"), $this);?>
 | <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 - Sangia Publishing</title>
    
	<?php if ($this->_tpl_vars['currentJournal']->getSetting('onlineIssn')): ?><?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('onlineIssn')); ?>
    <?php elseif ($this->_tpl_vars['currentJournal']->getSetting('printIssn')): ?><?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('printIssn')); ?>
    <?php elseif ($this->_tpl_vars['currentJournal']->getSetting('issn')): ?><?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('issn')); ?>
    <?php endif; ?>
	<meta name="citation_pii" content="P<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issn'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '') : smarty_modifier_replace($_tmp, '-', '')); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%y%m") : smarty_modifier_date_format($_tmp, "%y%m")); ?>
<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('count_characters', true, $_tmp, true) : smarty_modifier_count_characters($_tmp, true)))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%05d") : smarty_modifier_string_format($_tmp, "%05d")); ?>
" />
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <meta name="citation_id" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" />
	<meta name="citation_best_id" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
    <?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
		<meta name="citation_doi" content="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" />
	<?php endif; ?>
	<meta name="citation_type" content="JOUR" />
	<meta name="citation_journal_title" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="citation_journal_initials" content="<?php echo $this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
" />
	<meta name="citation_journal_abbrev" content="<?php echo $this->_tpl_vars['currentJournal']->getSetting('abbreviation',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
" />
	<meta name="citation_publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php else: ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php endif; ?>" />
	<meta name="description" content="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php $_from = $this->_tpl_vars['article']->getAbstract(null); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metaLocale'] => $this->_tpl_vars['alternate']):
?>
	<?php if ($this->_tpl_vars['alternate'] != $this->_tpl_vars['article']->getLocalizedAbstract()): ?>
    	<meta name="description_alternative" xml:lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" content="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['alternate'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 170, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 170, "...")); ?>
" />
	<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	<meta name="citation_article_type" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getSectionTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    
	<?php if ($this->_tpl_vars['displayFavicon']): ?>
	<link rel="icon" href="<?php echo $this->_tpl_vars['faviconDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayFavicon']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" type="<?php echo ((is_array($_tmp=$this->_tpl_vars['displayFavicon']['mimeType'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php else: ?>
	<link rel="icon" type="img/ico" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/favicon.ico" />	
	<?php endif; ?>
    <link rel="apple-touch-icon" sizes="57x57" href="//assets.sangia.org/static/favicon/apple-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="//assets.sangia.org/static/favicon/apple-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="//assets.sangia.org/static/favicon/apple-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="//assets.sangia.org/static/favicon/apple-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="//assets.sangia.org/static/favicon/apple-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="//assets.sangia.org/static/favicon/apple-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="//assets.sangia.org/static/favicon/apple-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="//assets.sangia.org/static/favicon/apple-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="//assets.sangia.org/static/favicon/apple-icon-180x180.png" />
    <link rel="icon" type="image/png" sizes="192x192"  href="//assets.sangia.org/static/favicon/android-icon-192x192.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="//assets.sangia.org/static/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="96x96" href="//assets.sangia.org/static/favicon/favicon-96x96.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="//assets.sangia.org/static/favicon/favicon-16x16.png" />
    
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/dublincore.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<meta property="journal_name" content="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />	
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/googlescholar.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>		
	
	<?php if ($this->_tpl_vars['issn']): ?>
	<meta name="prism.issn" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issn'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
	<meta name="prism.publicationName" content="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php if (is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' ) && $this->_tpl_vars['article']->getDatePublished()): ?>
	<meta name="prism.publicationDate" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
" />
    <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getYear()): ?>
	<meta name="prism.publicationDate" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getYear())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getDatePublished()): ?>
	<meta name="prism.publicationDate" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
" />
    <?php endif; ?>	
	<meta name="prism.section" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getSectionTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php if ($this->_tpl_vars['article']->getPages()): ?>
        <?php if ($this->_tpl_vars['article']->getStartingPage()): ?>
        <meta name="prism.startingPage" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getStartingPage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/><?php endif; ?>
	    <?php if ($this->_tpl_vars['article']->getEndingPage()): ?>
    	<meta name="prism.endingPage" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getEndingPage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/><?php endif; ?>
	<?php else: ?>
        <meta name="prism.startingPage" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getID())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/>	
    <?php endif; ?>
	<meta name="prism.copyright" content="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.copyrightStatement",'copyrightHolder' => ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCopyrightHolder())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'copyrightYear' => ((is_array($_tmp=$this->_tpl_vars['article']->getCopyrightYear())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" />
	<meta name="prism.rightsAgent" content="journals@sangia.org" />
	<meta name="prism.url" content="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
" />
	<?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
    <?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
	<meta name="prism.doi" content="doi:<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" />
    <meta name="DOI" content="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" />
	<?php endif; ?>

	<link rel="canonical" href="<?php echo $this->_tpl_vars['currentUrl']; ?>
">
	
	<meta name="twitter:site" content="@<?php echo $this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
" />
	<meta name="twitter:card" content='summary_large_image' />
	<meta name="twitter:image:alt" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 - Sangia" />
	<meta name="twitter:title" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
" />
	<meta name="twitter:description" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 - <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 170, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 170, "...")); ?>
" />
	
    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>

    <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
    
        <?php if ($this->_tpl_vars['issue']): ?>
        <?php $this->assign('displayCoverIssue', $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale'])); ?>
    <?php else: ?>
        <?php $this->assign('displayCoverIssue', false); ?>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['article']->getLocalizedFileName() && $this->_tpl_vars['article']->getLocalizedShowCoverPage()): ?>
        <?php $this->assign('showCoverPage', true); ?>
    <?php else: ?>
        <?php $this->assign('showCoverPage', false); ?>
    <?php endif; ?>
    <?php if ($this->_tpl_vars['showCoverPage']): ?>
    <meta name="twitter:image" content="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
        <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && is_array ( $this->_tpl_vars['displayCoverIssue'] )): ?>
    <meta name="twitter:image" content="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" />
    <?php elseif ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
    <meta name="twitter:image" content="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" />
    <?php endif; ?>
    
	<meta name="robots" content="max-image-preview:large">
	<meta property="og:type" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getSectionTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta property="og:site_name" name="site_name" content="Sangia" />
	<meta property="og:title" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
" />
	<meta property="og:description" content="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 170, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 170, "...")); ?>
" />	
	<meta property="og:url" content="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
" />
	
    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>

    <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
    
        <?php if ($this->_tpl_vars['issue']): ?>
        <?php $this->assign('displayCoverIssue', $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale'])); ?>
    <?php else: ?>
        <?php $this->assign('displayCoverIssue', false); ?>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['coverPagePath']): ?>
    <meta property="og:image" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPageFileName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
        <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && is_array ( $this->_tpl_vars['displayCoverIssue'] )): ?>
    <meta property="og:image" content="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" />    
    <?php elseif ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
    <meta property="og:image" content="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" />
    <?php endif; ?>
    <meta name="csrf-token" content="<?php echo $this->_tpl_vars['csrfToken']; ?>
">
    <meta property='article:publisher' content='//www.facebook.com/111429340332887' />
    <meta property='fb:app_id' content='1575594642876231' />
    <?php if ($this->_tpl_vars['article']->getLanguage()): ?>
    <meta property="og:locale" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php endif; ?>

	<meta name="citation_publication_date" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
" />
	<meta name="citation_online_date" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDateStatusModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")); ?>
" />
	<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOCACHE,NOODP,NOYDIR" />
	<meta name="revisit-after" content="3 days" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />    
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<?php echo $this->_tpl_vars['metaCustomHeaders']; ?>

	<meta name="publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php else: ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php endif; ?>" />
	<meta name="owner" content="PT. Sangia Research Media and Publishing" />
	<meta name="website_owner" content="www.sangia.org" />
	<meta name="SDTech" content="Proudly brought to Rochmady & Darsilan (R&D) by the SRM Technology team in Lasunapa, Muna, Indonesia" />
	
	<!-- preload -->
	<link rel="preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" />
	<link rel="preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/themes/sangiapub/css/font.css" type="text/css" as="style" />
    
	<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Header::Metadata"), $this);?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::LeftSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'leftSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'leftSidebarCode'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::RightSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'rightSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'rightSidebarCode'));?>

    
    <!-- Cookies CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <link rel="preload" href="https://badge.dimensions.ai/badge.js" as="script"><script async src="https://badge.dimensions.ai/badge.js" charset="utf-8"></script>
    <link rel="preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/fonts/fontawesome-webfont.woff2?v=4.3.0" as="font" type="font/woff2" crossorigin />
	<link rel="stylesheet preload" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" as="style" />

    <link rel="preload" href="//assets.sangia.org/css/themes/art_srm.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="//assets.sangia.org/css/themes/art_srm.css"></noscript>

    <link rel="preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/statics/styles/wizdam_article_v1.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    
	<link rel="stylesheet preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/font.css" type="text/css" as="style" />
	
	<link rel="preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/branded/wizdam_frontend_v2.branded.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    
	<?php echo $this->_tpl_vars['additionalHeadData']; ?>


	<!-- Default global locale keys for JavaScript -->
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/jsLocaleKeys.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

	<!-- Compiled scripts -->
	<?php if ($this->_tpl_vars['useMinifiedJavaScript']): ?>
        <link rel="preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/js/pkp.min.js" as="script" />
		<script type="text/javascript" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/js/pkp.min.js"></script>
	<?php else: ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/minifiedScripts.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>

	<?php $_from = $this->_tpl_vars['stylesheets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['testUrl'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['testUrl']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['cssUrl']):
        $this->_foreach['testUrl']['iteration']++;
?>
		<?php if ($this->_tpl_vars['cssUrl'] == ($this->_tpl_vars['baseUrl'])."/styles/ojs.css"): ?>
			<link rel="stylesheet" href="<?php echo $this->_tpl_vars['cssUrl']; ?>
" type="text/css" />
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>

	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/print.css" media="print" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/metrics.css" type="text/css" />

    <link rel="preload" href="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" as="script">    
    <script src="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" async="" type="text/javascript"></script>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.2/lazysizes.min.js" async=""></script>
    <script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>
    
</head>

<body id="sangia.org" class="article-view">
<a class="sr-only sr-only-focusable u-hide" href="#SRM-Pub">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

<header class="c-header" style="border-color:#000">
    <div class="c-header__row c-header__row--flush">
        <div class="c-header__container">
            <div class="c-header__split">
                <h1 class="c-header__logo-container u-mb-0">
                    <a href="//www.sangia.org" data-track="click" data-track-action="home" data-track-label="image">
                        <picture class="c-header__logo" loading="lazy">
                            <source loading="lazy" srcset="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                            <img class="lazyload" loading="lazy" src="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                        </picture>
                    </a>
                </h1>
                <ul class="c-header__menu c-header__menu--global">
                    <li class="c-header__item c-header__item--sangia-research">
                        <?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?>
                        <a class="c-header__link" href="/" data-test="siteindex-link" data-track="click" data-track-action="open sangia research index" data-track-label="link">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
</span>
                        </a>
                        <?php endif; ?>                    </li>
                    <?php if (! $this->_tpl_vars['currentJournal'] || $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
                    <li class="c-header__item c-header__item--padding c-header__item--pipe">
                        <a class="c-header__link c-header__link--search" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" data-header-expander="" data-test="search-link" data-track="click" data-track-action="open search tray" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.search"), $this);?>
</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                        </a>
                        <div id="search-menu" class="c-header__dropdown c-header__dropdown--full-width has-tethered u-js-hide" role="banner" data-track-component="sangia-split-header">
                            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/navsearch.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        </idv>
                    </li>
                    <?php endif; ?>
                    <?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
                    <li class="c-header__item c-header__item--padding c-header__item--snid-account-widget">
                        <nav class="c-account-nav" aria-labelledby="account-nav-title">
                            <a id="my-account" class="c-header__link eds-c-header__link c-account-nav__anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
" data-test="login-link" data-track="click" data-track-action="my account" data-track-category="sangia-split-header" data-track-label="link" aria-expanded="true">
                                <?php if ($this->_tpl_vars['userData']): ?>
                                <span><?php if (((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) !== ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php endif; ?><?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                <div class="Ibar__userLogged u-ml-8">
                                    <figure class="Avatar Avatar--size-32">
                                        <?php if ($this->_tpl_vars['userData']['profileImage'] && $this->_tpl_vars['userData']['profileImage']['uploadName']): ?><img src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['userData']['profileImage']['uploadName']; ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="Avatar__img is-inside-mask"><?php else: ?><img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php endif; ?>
                                    </figure>
                                </div>
                                <?php else: ?>
                                <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.myAccount"), $this);?>
</span>
                                <svg id="account-icon" role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                                <?php endif; ?>
                                <?php if ($this->_tpl_vars['unreadNotifications'] > 0): ?>
                                <span class="notification-icon" id="notification-count"><?php echo $this->_tpl_vars['unreadNotifications']; ?>
</span>
                                <?php endif; ?>
                                <svg class="chevron" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <div id="account-nav-menu" class="c-account-nav__menu c-account-nav__menu--right c-account-nav__menu--chevron-right u-js-hide">
                                <?php if ($this->_tpl_vars['userData']): ?>
                                <div class="Sangia__user__dropdown c-account-nav__menu-header">
                                    <div class="Sangia__user__avatar">
                                        <figure class="Avatar Avatar--size-96">
                                            <?php if ($this->_tpl_vars['userData']['profileImage'] && $this->_tpl_vars['userData']['profileImage']['uploadName']): ?>
                                            <img class="Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['userData']['profileImage']['uploadName']; ?>
?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                                            <?php elseif ($this->_tpl_vars['userData']['gender'] == 'F'): ?>
                                            <img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonF.png?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php elseif ($this->_tpl_vars['userData']['gender'] == 'M'): ?><img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonM.png?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php else: ?><img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php endif; ?>
                                        </figure>
                                        <?php if ($this->_tpl_vars['userData']['is_verified']): ?>
                                        <span class="verified badge" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><?php else: ?><span class="unverified badge icon" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><?php endif; ?>
                                    </div>
                                    <?php if ($this->_tpl_vars['userData']['salutation'] || $this->_tpl_vars['userData']['suffix']): ?>
                                    <div class="Sangia__user__salutation u-font-sangia-sans"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['userData']['suffix']): ?>— <?php echo $this->_tpl_vars['userData']['suffix']; ?>
<?php endif; ?></div>
                                    <?php endif; ?>
                                    <div class="Sangia__user__name"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                    <div class="Sangia__user__email"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                    <div id="account-nav-title" class="Sangia__user__account u-js-hide">
                                        <span class="u-js-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.user.loggedInAs"), $this);?>
<br></span>
                                        <span id="logged-in-username" data-username="<?php echo ((is_array($_tmp=$this->_tpl_vars['loggedInUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['loggedInUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <ul class="c-account-nav__menu-list dashoboard user-home">
                                   <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
">Dashoboard</a>
                                   </li>
                                </ul>
                                <ul class="c-account-nav__menu-list">
                                    <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'viewPublicProfile','path' => ((is_array($_tmp=$this->_tpl_vars['userSession']->getUserId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d"))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.showMyProfile"), $this);?>
</a></li>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'profile'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.editMyProfile"), $this);?>
</a></li>
                                    <?php if ($this->_tpl_vars['hasOtherJournals']): ?>
                                        <?php if (! $this->_tpl_vars['showAllJournals']): ?>
                                        <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'user'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.showAllJournals"), $this);?>
</a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                                        <?php if ($this->_tpl_vars['subscriptionsEnabled']): ?>
                                        <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'subscriptions'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.manageMySubscriptions"), $this);?>
</a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                                        <?php if ($this->_tpl_vars['acceptGiftPayments']): ?>
                                        <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'gifts'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "gifts.manageMyGifts"), $this);?>
</a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (! $this->_tpl_vars['implicitAuth']): ?>
                                    <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'changePassword'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.changeMyPassword"), $this);?>
</a></li>
                                    <?php endif; ?>
                                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                                        <?php if ($this->_tpl_vars['journalPaymentsEnabled'] && $this->_tpl_vars['membershipEnabled']): ?>
                                            <?php if ($this->_tpl_vars['dateEndMembership']): ?>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'payMembership'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.membership.renewMembership"), $this);?>
</a> (<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.membership.ends"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['dateEndMembership'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
)</li>
                                    <?php else: ?>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'payMembership'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.membership.buyMembership"), $this);?>
</a></li>
                                            <?php endif; ?>
                                        <?php endif; ?>                                    <?php endif; ?>                                    <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'signOut'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.logOut"), $this);?>
</a></li>
                                    
                                    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::User::Index::MyAccount"), $this);?>

                                    <?php if ($this->_tpl_vars['userSession']->getSessionVar('signedInAs')): ?>
                                    <li class="c-account-nav__menu-item Login_user_as">
                                        <a id="logout-button" class="c-header__link placeholder" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'signOutAsUser'), $this);?>
" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                                            <span>Logout as <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php if (((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                            <svg aria-hidden="true" focusable="false" role="img" width="22" height="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="m8.72592184 2.54588137c-.48811714-.34391207-1.08343326-.54588137-1.72592184-.54588137-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400182l-.79002171.32905522c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274v.9009805h-1v-.9009805c0-2.5479714 1.54557359-4.79153984 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4 1.09079823 0 2.07961816.43662103 2.80122451 1.1446278-.37707584.09278571-.7373238.22835063-1.07530267.40125357zm-2.72592184 14.45411863h-1v-.9009805c0-2.5479714 1.54557359-4.7915398 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4s4 1.790861 4 4c0 1.40732121-.7267788 2.64489414-1.8254829 3.3578652 2.2799093.9496145 3.8254829 3.1931829 3.8254829 5.7411543v.9009805h-1v-.9009805c0-2.1155483-1.2760206-4.0125067-3.2099783-4.8180274l-.7900217-.3290552v-1.02400184l.6301658-.40892721c.8482885-.55047139 1.3698342-1.489533 1.3698342-2.51900785 0-1.65685425-1.3431458-3-3-3-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400184l-.79002171.3290552c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274z" fill-rule="evenodd" fill="#ffffff"></path></svg>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </nav>
                    </li>
                    <?php else: ?>
                    <li class="c-header__item c-header__item--padding">
                        <a id="login-button" class="c-header__link placeholder" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login'), $this);?>
" style="login" data-test="login-link" data-track="click" data-track-action="login" data-track-category="sangia-split-header" data-track-label="link">
                            <span>Login</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                    </li>
                    <?php if (! $this->_tpl_vars['hideRegisterLink']): ?>
                    <li class="u-hide c-header__item c-header__item--padding c-header__item--pipe">
                		<a id="register-button" class="c-header__link placeholder" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register'), $this);?>
" style="register" data-test="register-link" data-track="click" data-track-action="register" data-track-category="sangia-split-header" data-track-label="link">
                		    <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.register"), $this);?>
</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                		</a>
                    </li><?php endif; ?>
                    <?php endif; ?>                </ul>
            </div>
        </div>
    </div>
    <div class="c-header__row">
        <div class="c-header__container" data-test="navigation-row">
            <div class="c-header__split">
                <div class="c-header__split">
                    <ul class="c-header__menu c-header__menu--journal lm-nav-root">
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span><span class="c-header__show-text">Explore</span> content</span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">Explore content</h2>
                                    <ul class="c-header-expander__list">
                                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'archive'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Archive Issues</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'browseSearch','op' => 'sections'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">View Section</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'browseSearch','op' => 'identifyTypes'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">View Article Type</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                        <?php endif; ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'siteMap'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.siteMap"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="//www.facebook.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Facebook</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="https://twitter.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Twitter</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-external="" data-track-label="link (mobile dropdown)">Sign up for alerts<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#fff"></path></svg></a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gateway','op' => 'plugin'), $this);?>
/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>RSS feed</span></a></li>
                                        
                                        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_tpl_vars['oaiUrl']; ?>
" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>OAI</span></a></li>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.about"), $this);?>
 <span class="c-header__show-text">the journal</span></span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">About the journal</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialTeam'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.editorialTeam"), $this);?>
</a></li>
                                        
                                        <?php if ($this->_tpl_vars['peopleGroups']): ?><?php $this->_tag_stack[] = array('iterate', array('from' => 'peopleGroups','item' => 'peopleGroup')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'displayMembership','path' => $this->_tpl_vars['peopleGroup']->getId()), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo ((is_array($_tmp=$this->_tpl_vars['peopleGroup']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
                                        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?>
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::People"), $this);?>

                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'focusAndScope'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'sectionPolicies'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</a></li>
                                        
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Policies"), $this);?>

                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('reviewPolicy') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'peerReviewProcess'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.peerReviewProcess"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'publicationFrequency'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN && $this->_tpl_vars['currentJournal']->getLocalizedSetting('openAccessPolicy') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.openAccessPolicy"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php $_from = $this->_tpl_vars['currentJournal']->getLocalizedSetting('customAboutItems'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['customAboutItem']):
?><?php if (! empty ( $this->_tpl_vars['customAboutItem']['title'] )): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => "custom-".($this->_tpl_vars['key'])), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item" style="word-break:break-all"><?php echo ((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li><?php endif; ?><?php endforeach; endif; unset($_from); ?>
                                        
                                        <?php $_from = $this->_tpl_vars['navMenuItems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['navItemKey'] => $this->_tpl_vars['navItem']):
?><?php if ($this->_tpl_vars['navItem']['url'] != '' && $this->_tpl_vars['navItem']['name'] != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php if ($this->_tpl_vars['navItem']['isAbsolute']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['baseUrl']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php if ($this->_tpl_vars['navItem']['isLiteral']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['navItem']['name']), $this);?>
<?php endif; ?></a></li><?php endif; ?><?php endforeach; endif; unset($_from); ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'archiving'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</a></li>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'history'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php if ($this->_tpl_vars['currentJournal']->getSetting('initials')): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
 of <?php echo $this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
<?php else: ?>Journal <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
<?php endif; ?></a></li>
                                        <?php endif; ?>
                                        
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::Header::Navbar::CurrentJournal"), $this);?>

                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Other"), $this);?>

                                        
                                        <?php if (! ( $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('publisherNote') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contributorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['contributors'] ) && $this->_tpl_vars['currentJournal']->getLocalizedSetting('sponsorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['sponsors'] ) )): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'journalSponsorship'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journalSponsorship"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?>
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="/" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
</span></a></li>
                                        <?php endif; ?>                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?>
 Information</a></li>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                        <li class="c-header__item c-header__item--dropdown-menu u-mr-2">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span>Publish <span class="c-header__show-text">with us</span></span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">Publish with us</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForAuthors"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Submission guidelines</a></li>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('authorGuidelines') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'authorGuidelines'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorGuidelines"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('copyrightNotice') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'copyrightNotice'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('privacyStatement') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'privacyStatement'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'librarians'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForLibrarians"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'readers'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForReaders"), $this);?>
</a></li>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('journalPaymentsEnabled') && ( $this->_tpl_vars['currentJournal']->getSetting('submissionFeeEnabled') || $this->_tpl_vars['currentJournal']->getSetting('fastTrackFeeEnabled') || $this->_tpl_vars['currentJournal']->getSetting('publicationFeeEnabled') )): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'authorFees'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorFees"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Submissions"), $this);?>

                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?>
 us</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
" target="_blank" data-track="click" data-track-action="Submit manuscript" data-track-label="link" data-track-external="">Submit manuscript<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m15 0c1.1045695 0 2 .8954305 2 2v5.5c0 .27614237-.2238576.5-.5.5s-.5-.22385763-.5-.5v-5.5c0-.51283584-.3860402-.93550716-.8833789-.99327227l-.1166211-.00672773h-9v3c0 1.1045695-.8954305 2-2 2h-3v10c0 .5128358.38604019.9355072.88337887.9932723l.11662113.0067277h7.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-7.5c-1.1045695 0-2-.8954305-2-2v-10.17157288c0-.53043297.21071368-1.0391408.58578644-1.41421356l3.82842712-3.82842712c.37507276-.37507276.88378059-.58578644 1.41421356-.58578644zm-.5442863 8.18867991 3.3545404 3.35454039c.2508994.2508994.2538696.6596433.0035959.909917-.2429543.2429542-.6561449.2462671-.9065387-.0089489l-2.2609825-2.3045251.0010427 7.2231989c0 .3569916-.2898381.6371378-.6473715.6371378-.3470771 0-.6473715-.2852563-.6473715-.6371378l-.0010428-7.2231995-2.2611222 2.3046654c-.2531661.2580415-.6562868.2592444-.9065605.0089707-.24295423-.2429542-.24865597-.6576651.0036132-.9099343l3.3546673-3.35466731c.2509089-.25090888.6612706-.25227691.9135302-.00001728zm-.9557137-3.18867991c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5zm-8.5-3.587-3.587 3.587h2.587c.55228475 0 1-.44771525 1-1zm8.5 1.587c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5z" fill="#fff"></path></svg></a></li>
                                        
                                        <?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>My Account</span></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                    </ul>
                    
                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION || $this->_tpl_vars['donationEnabled'] || $this->_tpl_vars['currentJournal']->getSetting('membershipFee')): ?>
                    <div class="c-header__menu u-ml-16 u-show-lg u-show-at-lg c-header__menu--tools">
                        <div class="c-header__item c-header__item--pipe">
                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
                            <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'subscriptions'), $this);?>
" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                                <span>Subscribe</span>
                            </a><?php endif; ?>                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('donationFeeEnabled')): ?>
                            <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'donations'), $this);?>
" data-track="click" data-track-action="donation" data-track-label="link" data-test="menu-button-donation">
                                <span>Donation</span>
                            </a><?php endif; ?>
                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('membershipFeeEnabled')): ?>
                            <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'memberships'), $this);?>
" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">
                                <span>Members</span>
                            </a><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <ul class="c-header__menu c-header__menu--tools">
                    <li class="c-header__item">
                        <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-label="link (desktop site header)" data-track-external="">
                            <span>Sign up for alerts</span><svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#222"></path></svg>
                        </a>
                    </li>
                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gateway','op' => 'plugin'), $this);?>
/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank">
                            <span>RSS feed</span>
                        </a>
                    </li>
                    <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="<?php echo $this->_tpl_vars['oaiUrl']; ?>
" data-track="click" data-track-action="oai feed" data-track-label="link" target="_blank">
                            <span>OAI</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>

<div id="breadcrumb" class="u-show-at-md u-hide-sm-max">
    <div class="row">
    	<div class="columns">
    	<a href="//www.sangia.org">sangia.org</a> <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    	<?php if ($this->_tpl_vars['currentJournal']): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => ($this->_tpl_vars['currentJournal'])), $this);?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a> <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg><?php endif; ?>
    	<?php $_from = $this->_tpl_vars['pageHierarchy']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['hierarchyLink']):
?>
    		<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['hierarchyLink'][0])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="hierarchyLink"><?php if (! $this->_tpl_vars['hierarchyLink'][2]): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['hierarchyLink'][1]), $this);?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['hierarchyLink'][1])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a> <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    	<?php endforeach; endif; unset($_from); ?>
    	<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.article"), $this);?>
</a>
    	<svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    	<span href="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="current"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.metrics"), $this);?>
</span>
    	</div>
    </div>
</div>

<div id="main-content" class="u-container u-mt-24 u-mb-32" data-component="article-container">
    