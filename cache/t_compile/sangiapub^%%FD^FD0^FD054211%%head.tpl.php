<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/head.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strip_tags', 'common/head.tpl', 16, false),array('modifier', 'escape', 'common/head.tpl', 16, false),array('function', 'call_hook', 'common/head.tpl', 36, false),)), $this); ?>
<?php $this->assign('owner', 'Sangia'); ?>
<?php $this->assign('brandOwner', "PT. Sangia Research Media and Publishing"); ?>
<?php $this->assign('siteOwner', "www.sangia.org"); ?>
<?php $this->assign('publishingBrand', 'Sangia Publishing'); ?>
<?php $this->assign('themeBrand', 'Sangia Wizdam Indonesia'); ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:title" content="<?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
<?php if ($this->_tpl_vars['currentJournal']): ?> - <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 | <?php echo $this->_tpl_vars['owner']; ?>
<?php else: ?> | <?php echo $this->_tpl_vars['siteTitle']; ?>
<?php endif; ?>" />
<?php if ($this->_tpl_vars['currentJournal']): ?>
<meta name="citation_publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php else: ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php endif; ?>" />
<?php endif; ?>
<meta name="prism.rightsAgent" content="journals@sangia.org" />
<meta name="copyright_theme" content="<?php echo $this->_tpl_vars['themeBrand']; ?>
" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />   
<meta name="access" content="Yes" />
<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOCACHE,NOODP,NOYDIR" />
<meta name="revisit-after" content="3 days" />
<meta name="applicable-device" content="pc,mobile" />
<link rel="canonical" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<meta name="360-site-verification" content="d192909d8ce61cb208a46fd481a78272" />
<meta name="csrf-token" content="<?php echo $this->_tpl_vars['csrfToken']; ?>
">
<meta name="google-site-verification" content="9mVvrkXamiUxutovEQqEk2eiRcjLUWHLHcwssZo3GYs" />
<meta property="og:url" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php echo $this->_tpl_vars['publishingBrand']; ?>
" />
<?php if ($this->_tpl_vars['currentJournal']): ?>
    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>

    <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
    <?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && ! $this->_tpl_vars['issue']->getHideCoverPageArchives($this->_tpl_vars['locale'])): ?>
    <meta property="og:image" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php elseif ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
    <meta property="og:image" content="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" />
    <?php else: ?>
    <meta property="og:image" content="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/images/not-available.webp" />
    <?php endif; ?>
<?php endif; ?>
<meta name="robots" content="max-image-preview:large">
<meta name="referrer" content="origin-when-cross-origin" />
<?php if ($this->_tpl_vars['currentJournal']): ?>
<meta name="twitter:site" content="@<?php echo $this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
" />
<?php endif; ?>
<meta name="twitter:card" content='summary_large_image' />
<meta name="twitter:image:alt" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['pageTitleTranslated'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['currentJournal']): ?> - <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 | <?php echo $this->_tpl_vars['owner']; ?>
<?php endif; ?>" />
<?php if ($this->_tpl_vars['metaSearchDescription']): ?>
    <meta property="og:description" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchDescription'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php elseif ($this->_tpl_vars['journalDescription']): ?>
    <meta property="og:description" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journalDescription'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
<?php endif; ?>
<?php if ($this->_tpl_vars['currentJournal']): ?>
<meta name="publisher" content="<?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?>Sangia Publishing<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php else: ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php endif; ?>" />
<?php endif; ?>
<meta name="website_owner" content="<?php echo $this->_tpl_vars['siteOwner']; ?>
" />
<meta name="owner" content="<?php echo $this->_tpl_vars['brandOwner']; ?>
" />

<link rel="apple-touch-icon" type="image/png" sizes="180x180" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="48x48" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/favicon/android-icon-48x48.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/favicon/favicon-16x16.png">
<link rel="manifest" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/favicon/manifest.json">