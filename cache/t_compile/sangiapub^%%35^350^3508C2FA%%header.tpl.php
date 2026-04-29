<?php /* Smarty version 2.6.26, created on 2026-04-06 00:28:31
         compiled from article/header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'article/header.tpl', 2, false),array('modifier', 'strip_tags', 'article/header.tpl', 13, false),array('modifier', 'escape', 'article/header.tpl', 13, false),array('modifier', 'string_format', 'article/header.tpl', 19, false),array('modifier', 'nl2br', 'article/header.tpl', 30, false),array('modifier', 'truncate', 'article/header.tpl', 33, false),array('modifier', 'date_format', 'article/header.tpl', 66, false),array('modifier', 'strip_unsafe_html', 'article/header.tpl', 95, false),array('modifier', 'assign', 'article/header.tpl', 146, false),array('function', 'translate', 'article/header.tpl', 81, false),array('function', 'url', 'article/header.tpl', 83, false),array('function', 'call_hook', 'article/header.tpl', 98, false),)), $this); ?>
<!DOCTYPE html>
<html class="js svg" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<head>
	<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?> - Sangia<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?> - Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?> - <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?> - Sangia Publishing<?php endif; ?></title>
    
	<?php if ($this->_tpl_vars['article']->getData('pii')): ?>
        <meta name="citation_pii" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getData('pii'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php endif; ?>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <meta name="citation_id" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" />
	<meta name="citation_best_id" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
    <?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
		<meta name="citation_doi" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
	<meta name="citation_type" content="JOUR" />
	<meta name="citation_journal_title" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="citation_journal_initials" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="citation_journal_abbrev" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('abbreviation',$this->_tpl_vars['currentJournal']->getPrimaryLocale()))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="citation_publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
	<meta name="prism.publicationDate" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getYear()): ?>
	<meta name="prism.publicationDate" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getYear())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getDatePublished()): ?>
	<meta name="prism.publicationDate" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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

	<link rel="canonical" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
	
	<meta name="twitter:site" content="@<?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
    <?php $this->assign('displayCoverIssue', $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale'])); ?>
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
	<meta property="og:url" content="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" />
	
    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>

    <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
    <?php $this->assign('displayCoverIssue', $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale'])); ?>
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
    
    <meta property='article:publisher' content='//www.facebook.com/111429340332887' />
    <meta property='fb:app_id' content='1575594642876231' />
    <?php if ($this->_tpl_vars['article']->getLanguage()): ?>
    <meta property="og:locale" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php endif; ?>
    <meta name="csrf-token" content="<?php echo $this->_tpl_vars['csrfToken']; ?>
">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <!-- Cookies CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
	<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Header::Metadata"), $this);?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::LeftSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'leftSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'leftSidebarCode'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::RightSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'rightSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'rightSidebarCode'));?>


	<script async="" src="//scholar.google.com/scholar_js/casa.js" type="text/javascript" referrerpolicy="strict-origin-when-cross-origin"></script>
    <script async src="https://badge.dimensions.ai/badge.js" charset="utf-8" referrerpolicy="strict-origin-when-cross-origin"></script>
    
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
	
	<link rel="preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/font.css" type="text/css" as="style" />
    <link rel="preload" href="//assets.sangia.org/css/themes/art_srm.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="//assets.sangia.org/css/themes/art_srm.css"></noscript>

    <link rel="preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/wizdam_article_v1.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    
	<link rel="stylesheet preload" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/font.css" type="text/css" as="style" />
    
	<?php echo $this->_tpl_vars['additionalHeadData']; ?>
    

<!-- Begin for PDF Galley -->
<?php if ($this->_tpl_vars['galley']): ?>
	<?php if ($this->_tpl_vars['galley']->isPdfGalley()): ?>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta content="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" property="og:url" />
 	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/styles/articleView.css" type="text/css" />
 	<link rel="canonical" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
	
	<?php elseif ($this->_tpl_vars['galley']->isHTMLGalley()): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/head.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<link rel="canonical" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
	<?php endif; ?>

<?php else: ?>

	<!-- Default global locale keys for JavaScript -->
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/jsLocaleKeys.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

	<!-- Compiled scripts -->
	<?php if ($this->_tpl_vars['useMinifiedJavaScript']): ?>
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

<?php endif; ?> <!-- Finish for PDF Galley -->

	<meta name="citation_publication_date" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="citation_online_date" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDateStatusModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y/%m/%d") : smarty_modifier_date_format($_tmp, "%Y/%m/%d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOCACHE,NOODP,NOYDIR" />
	<meta name="revisit-after" content="3 days" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />    
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<?php echo $this->_tpl_vars['metaCustomHeaders']; ?>

	<meta name="publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" />
	<meta name="owner" content="PT. Sangia Research Media and Publishing" />
	<meta name="website_owner" content="www.sangia.org" />
	<meta name="SDTech" content="Proudly brought to Rochmady & Darsilan (R&D) by the SRM Technology team in Lasunapa, Muna, Indonesia" />

    <!-- OneTrust Cookies Consent Notice -->
    <script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="ef8d6a4d-3871-4684-91c9-80259f6aacfe-test" referrerpolicy="strict-origin-when-cross-origin" ></script>
    <!-- OneTrust Cookies Consent Notice -->

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.2/lazysizes.min.js" async="" referrerpolicy="strict-origin-when-cross-origin"></script>
    
</head>

<body id="sangia.org" class="article-view">
<a class="sr-only sr-only-focusable u-hide" href="#SRM-Pub">Skip to Main Content</a>    
<a class="sr-only sr-only-focusable u-hide" href="#screen-reader-main-title">Skip to article</a>

<div data-iso-key="_0">
<?php if ($this->_tpl_vars['galley']): ?>
	<?php if ($this->_tpl_vars['galley']->isPdfGalley() && 'article/header.tpl' != 'article/pdfViewer.tpl'): ?>
	    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/pdfViewer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['galley']->isHTMLGalley()): ?>
	    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/banner.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/heading.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>	
<?php else: ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/banner.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/heading.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
