<?php /* Smarty version 2.6.26, created on 2026-04-04 08:11:18
         compiled from search/titleIndex.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'iterate', 'search/titleIndex.tpl', 19, false),array('modifier', 'default', 'search/titleIndex.tpl', 22, false),array('modifier', 'escape', 'search/titleIndex.tpl', 47, false),array('modifier', 'strip_unsafe_html', 'search/titleIndex.tpl', 60, false),array('modifier', 'nl2br', 'search/titleIndex.tpl', 64, false),array('modifier', 'truncate', 'search/titleIndex.tpl', 69, false),array('modifier', 'strip_tags', 'search/titleIndex.tpl', 76, false),array('modifier', 'date_format', 'search/titleIndex.tpl', 94, false),array('modifier', 'string_format', 'search/titleIndex.tpl', 120, false),array('function', 'call_hook', 'search/titleIndex.tpl', 57, false),array('function', 'url', 'search/titleIndex.tpl', 60, false),array('function', 'translate', 'search/titleIndex.tpl', 117, false),array('function', 'page_info', 'search/titleIndex.tpl', 226, false),array('function', 'page_links', 'search/titleIndex.tpl', 230, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "search.titleIndex"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-SA07.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<section id="search-article-list" class="u-mb-48 u-mt-32" data-track-component="search grid">
    <div class="s-container">
        <ul class="app-article-list-row">
        <?php $this->_tag_stack[] = array('iterate', array('from' => 'results','item' => 'result')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
        <?php if (is_array ( $this->_tpl_vars['result'] ) || is_object ( $this->_tpl_vars['result'] )): ?>

        <?php $this->assign('publishedArticle', ((is_array($_tmp=@$this->_tpl_vars['result']['publishedArticle'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('article', ((is_array($_tmp=@$this->_tpl_vars['result']['article'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('issue', ((is_array($_tmp=@$this->_tpl_vars['result']['issue'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('issueAvailable', ((is_array($_tmp=@$this->_tpl_vars['result']['issueAvailable'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('journal', ((is_array($_tmp=@$this->_tpl_vars['result']['journal'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('section', ((is_array($_tmp=@$this->_tpl_vars['result']['section'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('sectionId', $this->_tpl_vars['article']->getSectionId()); ?>
        <?php $this->assign('section', $this->_tpl_vars['sections'][$this->_tpl_vars['sectionId']]); ?>
        
            <?php if ($this->_tpl_vars['publishedArticle']->getGalleys()): ?>
            <li class="app-article-list-row__item 1">
                <div class="u-full-height" data-native-ad-placement="false">
                    <article class="u-full-height c-card c-card--flush" itemtype="http://schema.org/ScholarlyArticle">
                        <div class="c-card__layout u-full-heights">
                            <?php if ($this->_tpl_vars['publishedArticle']->getLocalizedFileName() && $this->_tpl_vars['publishedArticle']->getLocalizedShowCoverPage()): ?>
                                <?php $this->assign('showCoverPage', true); ?>
                            <?php else: ?>
                                <?php $this->assign('showCoverPage', false); ?>
                            <?php endif; ?>
                            
                            <?php if ($this->_tpl_vars['showCoverPage']): ?>
                            <div class="c-card__image">
                                <picture>
                                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                                                                            <source type="image/webp" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
                                    <img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                                    <?php else: ?>
                                                                        <source type="image/webp" srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['publishedArticle']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['publishedArticle']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
                                    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['publishedArticle']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                                        <?php endif; ?>
                                </picture>
                            </div>
                            <?php endif; ?>
                            <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>

                            <div class="c-card__body u-display-flex u-flex-direction-column">
                                <h3 class="c-card__title" itemprop="name headline">
                                    <a class="c-card__link u-link-inherit" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId()), $this);?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a>
                                </h3>

                                <?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
                                <div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></div>
                                <?php endif; ?>
                                
                                <?php if (( ! $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
                                <?php else: ?>
                                <ul class="c-author-list c-author-list--compact u-mt-auto" data-test="author-list"><?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?><?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?><?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?><?php $this->assign('firstname', $this->_tpl_vars['author']->getFirstName()); ?><?php $this->assign('middlename', $this->_tpl_vars['author']->getMiddleName()); ?><?php $this->assign('lastname', $this->_tpl_vars['author']->getLastName()); ?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="full-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['firstname'] !== $this->_tpl_vars['lastname']): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['firstname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['middlename']): ?><span itemprop="name"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['middlename'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 1, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 1, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['lastname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></li><?php endforeach; endif; unset($_from); ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="c-card__section c-meta">
                            <span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
                                <span class="c-meta__type"><?php if ($this->_tpl_vars['issue']->getPublished() && $this->_tpl_vars['section'] && $this->_tpl_vars['journal']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php if ($this->_tpl_vars['section'] && $this->_tpl_vars['section']->getLocalizedIdentifyType()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedIdentifyType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getSectionTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php endif; ?></span>
                            </span>
                            
                                                        <?php if ($this->_tpl_vars['publishedArticle']->getAccessStatus() == @ARTICLE_ACCESS_OPEN): ?>
                                <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                    <span class="u-color-open-access">Open Access</span>
                                </span>
                            <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN): ?>
                                <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                    <span class="u-color-open-access">Open Access</span>
                                </span>
                            <?php elseif ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                                <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                    <span class="u-color-open-access">Open Access</span>
                                </span>
                            <?php endif; ?>
                            
                            <time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, '%d %b %Y') : smarty_modifier_date_format($_tmp, '%d %b %Y')); ?>
</time>
                            
                            <div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a title="Go to <?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></div>

                            <?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
                            <?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
                            <div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="http://doi.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
</a></div><?php endif; ?>

                            <?php $_from = $this->_tpl_vars['publishedArticle']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
                            <?php if ($this->_tpl_vars['issueAvailable']): ?>
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="galley">
							    <?php if ($this->_tpl_vars['galley']->isPdfGalley()): ?>
							    <a class="pdf-galley" title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId($this->_tpl_vars['journal'])), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getGalleyLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <span class="fileSize">(<?php echo $this->_tpl_vars['galley']->getNiceFileSize(); ?>
)</span> <span class="fileView"><?php echo $this->_tpl_vars['galley']->getViews(); ?>
 views</span>
							    </a>
							    <?php elseif ($this->_tpl_vars['galley']->isHTMLGalley()): ?>
							    <a class="html-galley" title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId($this->_tpl_vars['journal'])), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getGalleyLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <span class="fileSize">(<?php echo $this->_tpl_vars['galley']->getNiceFileSize(); ?>
)</span> <span class="fileView"><?php echo $this->_tpl_vars['galley']->getViews(); ?>
 views</span>
							    </a>
							    <?php endif; ?>
							</div>
                            <?php endif; ?>
                            <?php endforeach; endif; unset($_from); ?>
                            
                            <?php if (! $this->_tpl_vars['hasAccess'] || $this->_tpl_vars['hasAbstract']): ?>
                            <div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="abstract"><a class="abstract" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId($this->_tpl_vars['journal'])), $this);?>
"><?php if ($this->_tpl_vars['galley']->isHTMLGalley()): ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.article"), $this);?>
<?php elseif ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
<?php else: ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
<?php endif; ?> <span class="fileView"><?php echo $this->_tpl_vars['publishedArticle']->getViews(); ?>
 views</span></a></div>
                            <?php endif; ?>
                            
                            <div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info">Volume <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>, No. <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['article']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
<?php endif; ?></div>
                        </div>
                    </article>
                </div>
            </li>
            <?php else: ?>
            <li class="app-article-list-row__item 2">
               <div class="u-full-height" data-native-ad-placement="false">
                    <article class="u-full-height c-card c-card--flush" itemtype="http://schema.org/ScholarlyArticle">
                        <div class="c-card__layout u-full-heights">
                            <?php if ($this->_tpl_vars['publishedArticle']->getLocalizedFileName() && $this->_tpl_vars['publishedArticle']->getLocalizedShowCoverPage()): ?>
                                <?php $this->assign('showCoverPage', true); ?>
                            <?php else: ?>
                                <?php $this->assign('showCoverPage', false); ?>
                            <?php endif; ?>
                            
                            <?php if ($this->_tpl_vars['showCoverPage']): ?>
                            <div class="c-card__image">
                                <picture>
                                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                                                                                <source type="image/webp" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
                                        <img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                                    <?php else: ?>
                                                                                <source type="image/webp" srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['publishedArticle']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['publishedArticle']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
                                        <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['publishedArticle']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                                    <?php endif; ?>
                                </picture>
                            </div>
                            <?php endif; ?>
                            <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Issue::Issue::ArticleCoverImage"), $this);?>

                            <div class="c-card__body u-display-flex u-flex-direction-column">
                                <h3 class="c-card__title">
                                    <a class="c-card__link u-link-inherit" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId()), $this);?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a>
                                </h3>

                                <?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
                                <div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p><?php if ($this->_tpl_vars['showCoverPage']): ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 230, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 230, "...")); ?>
<?php else: ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 380, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 380, "...")); ?>
<?php endif; ?></p></div>
                                <?php endif; ?>
                                
                                <?php if (( ! $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
                                <?php else: ?>
                                <ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto"><?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?><?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?><?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?><?php $this->assign('firstname', $this->_tpl_vars['author']->getFirstName()); ?><?php $this->assign('middlename', $this->_tpl_vars['author']->getMiddleName()); ?><?php $this->assign('lastname', $this->_tpl_vars['author']->getLastName()); ?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="full-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['firstname'] !== $this->_tpl_vars['lastname']): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['firstname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['middlename']): ?><span itemprop="name"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['middlename'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 1, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 1, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['lastname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></li><?php endforeach; endif; unset($_from); ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="c-card__section c-meta">
                            <span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
                                <span class="c-meta__type"><?php if ($this->_tpl_vars['issue']->getPublished() && $this->_tpl_vars['section'] && $this->_tpl_vars['journal']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php if ($this->_tpl_vars['section'] && $this->_tpl_vars['section']->getLocalizedIdentifyType()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedIdentifyType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getSectionTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php endif; ?></span>
                            </span>

                                                        <?php if ($this->_tpl_vars['publishedArticle']->getAccessStatus() == @ARTICLE_ACCESS_OPEN): ?>
                                <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                    <span class="u-color-open-access">Open Access</span>
                                </span>
                            <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN): ?>
                                <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                    <span class="u-color-open-access">Open Access</span>
                                </span>
                            <?php elseif ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                                <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                    <span class="u-color-open-access">Open Access</span>
                                </span>
                            <?php endif; ?>
                            
                            <time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %b %Y') : smarty_modifier_date_format($_tmp, '%e %b %Y')); ?>
</time>
                            
                            <div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a title="Go to <?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></div>
                            
                            <?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
                            <?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
                            <div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="http://doi.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
</a></div><?php endif; ?>
                            
                            <?php if (! $this->_tpl_vars['hasAccess'] || $this->_tpl_vars['hasAbstract']): ?>
                            <div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="nopdf-galley"><a class="abstract-only" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId($this->_tpl_vars['journal'])), $this);?>
"><?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
<?php else: ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
<?php endif; ?> <span class="fileView"><?php echo $this->_tpl_vars['publishedArticle']->getViews(); ?>
 views</span></a></div>
                            <?php endif; ?>
                            
                            <div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info">Volume <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, No. <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['article']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
<?php endif; ?></div>
                        </div>
                    </article>
                </div>                
            </li>
           <?php endif; ?>
        <?php endif; ?>
        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>        
        </ul>
    </div>
    
    <?php if ($this->_tpl_vars['results']->wasEmpty()): ?>
    <div class="container cleared container-type-title" data-container-type="title">
        <div class="border-top-1 border-gray-medium"></div>
        <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
            <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
            </div>
            <div class="c-empty-state-card__text">
                <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "search.noResults"), $this);?>
</h3>
                <div class="c-empty-state-card__text--description">We are currently preparing our inaugural content. Please check back soon for our upcoming publications, or consider <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
">submitting your manuscript</a> to be part of our first issue. Visit our <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
">Submission Guidelines</a> for more information.</div>
            </div>
        </div>
    </div>
    <?php else: ?>
	<div class="colspan u-mb-0" id="colspan">	    
	    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	        <div class="c-pagination">View <?php if ($this->_tpl_vars['results'] && is_object ( $this->_tpl_vars['results'] )): ?><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['results']), $this);?>
<?php endif; ?></div>
        </section>
        <?php if ($this->_tpl_vars['results']->getPageCount() > 1): ?>
	    <section class="u-display-flex u-justify-content-center">
	        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'results','iterator' => $this->_tpl_vars['results'],'name' => 'search'), $this);?>

	       </div>
	    </section>
	    <?php endif; ?>
	</div>
    <?php endif; ?>
    
</section>
</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-search.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>