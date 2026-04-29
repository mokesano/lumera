<?php /* Smarty version 2.6.26, created on 2026-04-04 05:56:01
         compiled from search/search.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'call_hook', 'search/search.tpl', 15, false),array('function', 'url', 'search/search.tpl', 21, false),array('function', 'translate', 'search/search.tpl', 130, false),array('function', 'page_info', 'search/search.tpl', 294, false),array('function', 'page_links', 'search/search.tpl', 298, false),array('modifier', 'strip_unsafe_html', 'search/search.tpl', 20, false),array('modifier', 'escape', 'search/search.tpl', 20, false),array('modifier', 'nl2br', 'search/search.tpl', 69, false),array('modifier', 'strip_tags', 'search/search.tpl', 81, false),array('modifier', 'date_format', 'search/search.tpl', 99, false),array('block', 'iterate', 'search/search.tpl', 27, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "navigation.search"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-SA07.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::PreResults"), $this);?>


<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => $this->_tpl_vars['filterName'],'filterValue' => $this->_tpl_vars['filterValue']), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('filterInput', ob_get_contents());ob_end_clean(); ?>

<div class="app-search-adv-filters" data-test="advanced-search-filters">    
    <span class="app-search-adv-filter__filter-container">Advanced filters: <span class="app-search-adv-filters__filter">"<?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>"</span></span>
    <a class="app-search-adv-filters__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" data-test="clear-advanced-filters">Clear advanced filters</a>
</div>

<section id="search-article-list" class="u-mb-48 u-mt-32" data-track-component="search grid">
	<div class="s-container">
		<ul class="app-article-list-row">
		<?php $this->_tag_stack[] = array('iterate', array('from' => 'results','item' => 'result')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			
			<?php $this->assign('publishedArticle', $this->_tpl_vars['result']['publishedArticle']); ?>
			<?php $this->assign('article', $this->_tpl_vars['result']['article']); ?>
			<?php $this->assign('issue', $this->_tpl_vars['result']['issue']); ?>
			<?php $this->assign('issueAvailable', $this->_tpl_vars['result']['issueAvailable']); ?>
			<?php $this->assign('journal', $this->_tpl_vars['result']['journal']); ?>
			<?php $this->assign('section', $this->_tpl_vars['result']['section']); ?>
				
			<?php if ($this->_tpl_vars['publishedArticle']->getGalleys()): ?>	
			<li class="app-article-list-row__item 1">
				<div class="u-full-height" data-native-ad-placement="false">
					<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
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
								<h3 class="c-card__title" itemprop="name headline">
									<a class="c-card__link u-link-inherit" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId()), $this);?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a>
								</h3>
								<?php if ($this->_tpl_vars['publishedArticle']->getLocalizedAbstract()): ?>
								<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedAbstract())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></div>
								<?php endif; ?>
								
								<?php if (( ! $this->_tpl_vars['publishedArticle']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['publishedArticle']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
								<?php else: ?>
								<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list"><?php $_from = $this->_tpl_vars['publishedArticle']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['authorItem']):
        $this->_foreach['authorList']['iteration']++;
?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['authorItem']->getFirstName() !== $this->_tpl_vars['authorItem']->getLastName()): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorItem']->getMiddleName()): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
                            <span class="c-meta__item c-meta__item--block-at-lg 1" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN): ?>
                            <span class="c-meta__item c-meta__item--block-at-lg 2" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            <?php elseif ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                            <span class="c-meta__item c-meta__item--block-at-lg 3" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>
                            <?php endif; ?>

							<time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time>

							<?php if (! $this->_tpl_vars['currentJournal']): ?>
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
							</div>
							<?php else: ?>
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
							</div>
							<?php endif; ?>

							<?php $this->assign('doi', $this->_tpl_vars['publishedArticle']->getStoredPubId('doi')); ?>
							<?php if ($this->_tpl_vars['publishedArticle']->getPubId('doi')): ?>
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="http://doi.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_tpl_vars['publishedArticle']->getPubId('doi'); ?>
</a><?php if ($this->_tpl_vars['publishedArticle']->getViews('doi')): ?><?php endif; ?>
							</div>
							<?php endif; ?>

							<?php $_from = $this->_tpl_vars['publishedArticle']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
                            <?php if ($this->_tpl_vars['issueAvailable']): ?>
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="galley">
							    <?php if ($this->_tpl_vars['galley']->isPdfGalley()): ?>
							    <a class="pdf-galley" title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId($this->_tpl_vars['journal'])), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getGalleyLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <span class="fileSize">(<?php echo $this->_tpl_vars['galley']->getNiceFileSize(); ?>
)</span> <span class="fileView"><?php echo $this->_tpl_vars['galley']->getViews(); ?>
 views</span>
							    </a>
							    <?php elseif ($this->_tpl_vars['galley']->isHTMLGalley()): ?>
							    <a class="html-galley" title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
"><?php if ($this->_tpl_vars['galley']->isHTMLGalley()): ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.article"), $this);?>
<?php elseif ($this->_tpl_vars['publishedArticle']->getLocalizedAbstract()): ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
<?php else: ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
<?php endif; ?> <span class="fileView"><?php echo $this->_tpl_vars['publishedArticle']->getViews(); ?>
 views</span></a>
							</div>
							<?php endif; ?>

							<div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info" >Volume <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>, No. <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['publishedArticle']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>, <?php echo $this->_tpl_vars['publishedArticle']->getId(); ?>
<?php endif; ?>
							</div>
						</div>
					</article>
				</div>
			</li>
			<?php else: ?>
			<li class="app-article-list-row__item 2">
				<div class="u-full-height" data-native-ad-placement="false">
					<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
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
								<h3 class="c-card__title" itemprop="name headline">
									<a class="c-card__link u-link-inherit" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId()), $this);?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a>
								</h3>

								<?php if ($this->_tpl_vars['publishedArticle']->getLocalizedAbstract()): ?>
								<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedAbstract())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></div>
								<?php endif; ?>
								
								<?php if (( ! $this->_tpl_vars['publishedArticle']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['publishedArticle']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
								<?php else: ?>
								<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list"><?php $_from = $this->_tpl_vars['publishedArticle']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['authorItem']):
        $this->_foreach['authorList']['iteration']++;
?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['authorItem']->getFirstName() !== $this->_tpl_vars['authorItem']->getLastName()): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorItem']->getMiddleName()): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorItem']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
<?php endif; ?><?php endif; ?>
								</span>
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
                            <?php else: ?>
                                <?php $this->assign('articleJournal', $this->_tpl_vars['publishedArticle']->getJournal()); ?>
                                <?php if ($this->_tpl_vars['articleJournal'] && $this->_tpl_vars['articleJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                                    <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                        <span class="u-color-open-access">Open Access</span>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
							
							<time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time>

							<?php if (! $this->_tpl_vars['currentJournal']): ?>
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
							</div>
							<?php else: ?>
							<div class="c-meta__item c-meta__item--block-at-lg u-text-bold" data-test="journal-title-and-link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
							</div>
							<?php endif; ?>

							<?php $this->assign('doi', $this->_tpl_vars['publishedArticle']->getStoredPubId('doi')); ?>
							<?php if ($this->_tpl_vars['publishedArticle']->getPubId('doi')): ?>
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="http://doi.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_tpl_vars['publishedArticle']->getPubId('doi'); ?>
</a><?php if ($this->_tpl_vars['publishedArticle']->getViews('doi')): ?><?php endif; ?>
							</div>
							<?php endif; ?>

							<?php if (! $this->_tpl_vars['hasAccess'] || $this->_tpl_vars['hasAbstract']): ?>
							<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="nopdf-galley"><a class="abstract-only" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['publishedArticle']->getBestArticleId($this->_tpl_vars['journal'])), $this);?>
"><?php if ($this->_tpl_vars['publishedArticle']->getLocalizedAbstract()): ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
<?php else: ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
<?php endif; ?> <span class="fileView"><?php echo $this->_tpl_vars['publishedArticle']->getViews(); ?>
 views</span></a>
							</div>
							<?php endif; ?>

							<div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info" >Volume <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>, No. <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['publishedArticle']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>, <?php echo $this->_tpl_vars['publishedArticle']->getId(); ?>
<?php endif; ?>
							</div>
						</div>
					</article>
				</div>				
			</li>
			<?php endif; ?>
		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>			
		</ul>
	</div>
</section>

<?php if ($this->_tpl_vars['results']->wasEmpty()): ?>
<div class="search-message">
	<?php if ($this->_tpl_vars['error']): ?>
	<div class="error-message" data-test="empty-search-result-message">
		<?php echo ((is_array($_tmp=$this->_tpl_vars['error'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

	</div>
	<?php else: ?>
	<div class="empty-message u-hide" data-test="empty-search-result-message">
		<h2>Sorry – we couldn’t find what you are looking for.</h2>
		<p class="intro--paragraph">Make sure that all words are spelled correctly</p>
	</div>
	<?php endif; ?>
    <div class="container cleared container-type-title" data-container-type="title">
        <div class="border-top-1 border-gray-medium"></div>
        <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
            <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
            </div>
            <div class="c-empty-state-card__text search-tips">
                <h2 class="c-empty-state-card__text--title headline-5">Sorry – we couldn’t find what you are looking for "<?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>...<?php endif; ?>"</h2>
                <div class="c-empty-state-card__text--description">Make sure that all words are spelled correctly.</div>
        		<div class="c-empty-state-card__text--description">
        		<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::SyntaxInstructions"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('syntaxInstructions', ob_get_contents());ob_end_clean(); ?>
        			<?php if (empty ( $this->_tpl_vars['syntaxInstructions'] )): ?>
        				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "search.syntaxInstructions"), $this);?>

        			<?php else: ?>
        				        				<?php echo $this->_tpl_vars['syntaxInstructions']; ?>

        			<?php endif; ?>
        		</div>
            </div>
        </div>
    </div>
</div>
<div class="u-hide instruct-search u-mt-32" data-test="tips-search-message">
	<h2>Seacrh Tips</h2>		
	<div class="search-tips">
	<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::SyntaxInstructions"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('syntaxInstructions', ob_get_contents());ob_end_clean(); ?>
		<?php if (empty ( $this->_tpl_vars['syntaxInstructions'] )): ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "search.syntaxInstructions"), $this);?>

		<?php else: ?>
						<?php echo $this->_tpl_vars['syntaxInstructions']; ?>

		<?php endif; ?>
	</div>
</div>
<?php else: ?>
<div id="colspan" class="colspan u-mb-0" >	    
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View <?php if ($this->_tpl_vars['results'] && is_object ( $this->_tpl_vars['results'] )): ?><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['results']), $this);?>
<?php endif; ?></div>
    </section>
    <?php if ($this->_tpl_vars['results']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'results','iterator' => $this->_tpl_vars['results'],'name' => 'search','query' => $this->_tpl_vars['query'],'searchJournal' => $this->_tpl_vars['searchJournal'],'authors' => $this->_tpl_vars['authors'],'title' => $this->_tpl_vars['title'],'abstract' => $this->_tpl_vars['abstract'],'galleyFullText' => $this->_tpl_vars['galleyFullText'],'suppFiles' => $this->_tpl_vars['suppFiles'],'discipline' => $this->_tpl_vars['discipline'],'subject' => $this->_tpl_vars['subject'],'type' => $this->_tpl_vars['type'],'coverage' => $this->_tpl_vars['coverage'],'indexTerms' => $this->_tpl_vars['indexTerms'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'orderBy' => $this->_tpl_vars['orderBy'],'orderDir' => $this->_tpl_vars['orderDir']), $this);?>

       </div>
    </section>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-search.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>