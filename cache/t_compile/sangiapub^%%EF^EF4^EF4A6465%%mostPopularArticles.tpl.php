<?php /* Smarty version 2.6.26, created on 2026-04-04 06:08:34
         compiled from common/featured/mostPopularArticles.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'common/featured/mostPopularArticles.tpl', 16, false),array('function', 'url', 'common/featured/mostPopularArticles.tpl', 27, false),array('modifier', 'count', 'common/featured/mostPopularArticles.tpl', 16, false),array('modifier', 'date_format', 'common/featured/mostPopularArticles.tpl', 29, false),array('modifier', 'escape', 'common/featured/mostPopularArticles.tpl', 46, false),array('modifier', 'number_format', 'common/featured/mostPopularArticles.tpl', 63, false),)), $this); ?>

<?php $this->assign('articleCount', 0); ?>
<?php if ($this->_tpl_vars['publishedArticles']): ?>
    <?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section']):
?>
        <?php if ($this->_tpl_vars['section']['articles']): ?>
            <?php echo smarty_function_math(array('equation' => "x + y",'x' => $this->_tpl_vars['articleCount'],'y' => count($this->_tpl_vars['section']['articles']),'assign' => 'articleCount'), $this);?>

        <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>

<section id="latest-popular" class="live-area u-mt-32 u-mb-48" data-track-component="latest popular grid" >
    <div class="row raw">
        <div id="articles-popular" class="c-article-most__popular">
            <div class="u-container c-slice-heading">
                <h2 class="titles u-ma-0">
                    <span class="title">Most popular</span>
                    <a class="c-section-heading__link" data-track="click" data-track-action="view all" data-track-label="button" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'statistics'), $this);?>
"><svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="20" width="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg></a>
                </h2>
                <span class="u-display-block u-font-sans">Last update: <?php echo ((is_array($_tmp=$this->_tpl_vars['lastUpdateDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y, %H:%M") : smarty_modifier_date_format($_tmp, "%d %b %Y, %H:%M")); ?>
. (Smart detection updates. Regular updates weekly.)</span>
            </div>
        </div>
        <div class="u-container" id="contents">    

    <ul class="u-list-reset<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['articleCount'] >= 6): ?> app-reviews-row<?php endif; ?>">
        <li class="app-reviews-row__main">
            
<ul class="app-reviews-row__grid">
    <?php if ($this->_tpl_vars['topArticle']): ?>
    <?php $_from = $this->_tpl_vars['topArticle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
    <li class="app-reviews-row__item app-reviews-row__item--major">
        <div class="u-full-height">
            <div class="u-full-height" data-native-ad-placement="false">
                <article class="u-full-height c-card c-card--flush c-card--major" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                    <div class="c-card__layout u-full-height">
                        <?php if ($this->_tpl_vars['article']['cover_image']['file_exists']): ?>
                        <div class="c-card__image"><picture><source type="image/webp" srcset="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 450w,<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px,735px"><img src="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image"></picture>
                        </div>
                        <?php else: ?>
                        <div class="c-card__image"><picture><source type="image/webp" srcset="//assets.sangia.org/static/images/not-available.webp 450w, //assets.sangia.org/static/images/not-available.webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img src="//assets.sangia.org/static/images/not-available.webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image"></picture>
				        </div>
                        <?php endif; ?>
                        <div class="c-card__body u-display-flex u-flex-direction-column">
                            <h3 class="c-card__title" itemprop="name headline">
                                <a href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo $this->_tpl_vars['article']['title']; ?>
</a>
                            </h3>
                            <?php if ($this->_tpl_vars['article']['abstract']): ?>
                            <div data-test="article-description" class="c-card__summary ellipse u-mb-16" itemprop="description"><p><?php echo $this->_tpl_vars['article']['abstract']; ?>
</p>
                            </div>
                            <?php endif; ?>
                            <div class="c-card__section c-meta">
                                <div><ul data-test="author-list" class="c-author-list c-author-list--compact"><?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name"><?php if ($this->_tpl_vars['author']['first_name'] !== $this->_tpl_vars['author']['last_name']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['author']['first_name']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['author']['middle_name']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['author']['middle_name']; ?>
</span><?php endif; ?><span itemprop="surname"><?php echo $this->_tpl_vars['author']['last_name']; ?>
</span></span></li><?php endforeach; endif; unset($_from); ?></ul>
                                </div>
                                <span class="c-meta__item" data-test="article.type"><span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['article_type'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></span><?php if ($this->_tpl_vars['article']['is_open_access']): ?><span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">Open Access</span></span><?php endif; ?><?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['articleCount'] >= 6): ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time><?php else: ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</time><?php endif; ?><span class="c-meta__item rank"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['total_views'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
 views</span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </li>
    <?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['secondTierArticles']): ?>
    <?php $_from = $this->_tpl_vars['secondTierArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['secondLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['secondLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['article']):
        $this->_foreach['secondLoop']['iteration']++;
?>
    <?php $this->assign('rank', $this->_foreach['secondLoop']['iteration']+1); ?>
    <li class="app-reviews-row__item">
        <div class="u-full-height">
            <div class="u-full-height" data-native-ad-placement="false">
                <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                    <div class="c-card__layout u-full-height">
                        <?php if ($this->_tpl_vars['article']['cover_image']['file_exists']): ?>
                        <div class="c-card__image"><picture><source type="image/webp" srcset="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 160w,<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 290w" sizes="(max-width: 640px) 160px,(max-width: 1200px) 290px,290px">
                            <img src="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
" alt="" itemprop="image"></picture>
                        </div>
                        <?php endif; ?>
                        <div class="c-card__body u-display-flex u-flex-direction-column">
                            <h3 class="c-card__title" itemprop="name headline">
                                <a href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo $this->_tpl_vars['article']['title']; ?>
</a>
                            </h3>
                            <?php if ($this->_tpl_vars['article']['abstract']): ?>
                            <div data-test="article-description" class="c-card__summary ellips u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo $this->_tpl_vars['article']['abstract']; ?>
</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="c-card__section c-meta">
                        <div><ul data-test="author-list" class="c-author-list c-author-list--compact"><?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name"><?php if ($this->_tpl_vars['author']['first_name'] !== $this->_tpl_vars['author']['last_name']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['author']['first_name']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['author']['middle_name']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['author']['middle_name']; ?>
</span><?php endif; ?><span itemprop="surname"><?php echo $this->_tpl_vars['author']['last_name']; ?>
</span></span></li><?php endforeach; endif; unset($_from); ?></ul>
                        </div>
                        <span class="c-meta__item" data-test="article.type"><span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['article_type'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></span><?php if ($this->_tpl_vars['article']['is_open_access']): ?><span class="c-meta__item" itemprop="openAccess" data-test="open-access"><?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['articleCount'] >= 6): ?><span class="u-color-open-access">OA</span><?php else: ?><span class="u-color-open-access">Open Access</span><?php endif; ?></span><?php endif; ?><?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['articleCount'] >= 6): ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time><?php else: ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</time><?php endif; ?>
                    </div>
                </article>
            </div>
        </div>
    </li>
    <?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>
</ul>
    <?php if ($this->_tpl_vars['thirdTierArticles']): ?>
    <li>
        <ul class="app-reviews-row__side">
            <?php $_from = $this->_tpl_vars['thirdTierArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['thirdLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['thirdLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['article']):
        $this->_foreach['thirdLoop']['iteration']++;
?>
            <?php $this->assign('rank', $this->_foreach['thirdLoop']['iteration']+5); ?>
            <li class="app-reviews-row__side-item">
                <div class="u-full-height">
                    <div class="u-full-height" data-native-ad-placement="false">
                        <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                            <div class="c-card__layout u-full-height">
                                <div class="c-card__body u-display-flex u-flex-direction-column">
                                    <h3 class="c-card__title elipsis" itemprop="name headline">
                                        <a href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo $this->_tpl_vars['article']['title']; ?>
</a>
                                    </h3>
                                </div>
                            </div>
                            <div class="c-card__section c-meta">
                                <div><ul data-test="author-list" class="c-author-list c-author-list--compact"><?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?><li itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name"><?php if ($this->_tpl_vars['author']['first_name'] !== $this->_tpl_vars['author']['last_name']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['author']['first_name']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['author']['middle_name']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['author']['middle_name']; ?>
</span><?php endif; ?><span itemprop="surname"><?php echo $this->_tpl_vars['author']['last_name']; ?>
</span></span></li><?php endforeach; endif; unset($_from); ?></ul>
                                </div>
                                <span class="c-meta__item" data-test="article.type"><span class="c-meta__type"><?php echo $this->_tpl_vars['article']['article_type']; ?>
</span></span><?php if ($this->_tpl_vars['article']['is_open_access']): ?><span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">OA</span></span><?php endif; ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time>
                            </div>
                        </article>
                    </div>
                </div>
            </li>
            <?php endforeach; endif; unset($_from); ?>
        </ul>
    </li>
    <?php endif; ?>

            </li>
        </ul>
        </div>
    </div>
</section>