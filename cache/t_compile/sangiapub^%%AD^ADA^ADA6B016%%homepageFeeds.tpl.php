<?php /* Smarty version 2.6.26, created on 2026-04-04 06:08:33
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/externalFeed/templates/homepageFeeds.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/externalFeed/templates/homepageFeeds.tpl', 4, false),array('modifier', 'default', 'file:/home/sangiaor/public_html/journals/plugins/generic/externalFeed/templates/homepageFeeds.tpl', 32, false),array('modifier', 'strip_tags', 'file:/home/sangiaor/public_html/journals/plugins/generic/externalFeed/templates/homepageFeeds.tpl', 35, false),array('modifier', 'truncate', 'file:/home/sangiaor/public_html/journals/plugins/generic/externalFeed/templates/homepageFeeds.tpl', 35, false),)), $this); ?>
<div id="externalFeedsHome">
<?php $_from = $this->_tpl_vars['processedExternalFeeds']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['feedData']):
?>
    <?php if ($this->_tpl_vars['feedData']['has_items']): ?>
        <h2 class="u-container c-slice-heading"><?php echo ((is_array($_tmp=$this->_tpl_vars['feedData']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h2>
        
        <div id="contents" class="u-container u-mb-0">
            <ul class="app-news-row externalFeeds">
                
                                <?php $_from = $this->_tpl_vars['feedData']['featured_items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
                    <li class="app-news-row__item app-news-row__item--major">
                        <div class="u-full-height title" data-native-ad-placement="false">
                            <article class="u-full-height c-card c-card--major c-card--dark" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                                <div class="c-card__layout u-full-height">
                                    
                                    <div class="c-card__image">
                                        <picture>
                                            <?php $this->assign('enclosure', $this->_tpl_vars['item']->get_enclosure()); ?>
                                            <?php if ($this->_tpl_vars['enclosure']): ?>
                                                <?php $_from = $this->_tpl_vars['enclosure']->get_link(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['thumbnailUrl']):
?>
                                                    <?php if ($this->_tpl_vars['thumbnailUrl']): ?>
                                                        <source type="image/webp" srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['thumbnailUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w, <?php echo ((is_array($_tmp=$this->_tpl_vars['thumbnailUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w" sizes="(max-width: 768px) 290px, (max-width: 1200px) 485px, 485px">
                                                        <img loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['thumbnailUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                                                    <?php endif; ?>
                                                <?php endforeach; endif; unset($_from); ?>
                                            <?php endif; ?>
                                        </picture>
                                    </div>
                                    
                                    <div class="c-card__body u-display-flex u-flex-direction-column">
                                        <h3 class="c-card__title" itemprop="name headline">
                                            <a class="c-card__link u-link-inherit" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['item']->get_permalink())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['item']->get_title())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, 'No Title') : smarty_modifier_default($_tmp, 'No Title')); ?>
</a>
                                        </h3>
                                        <div class="c-card__summary u-mb-16">
                                            <p><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['item']->get_content())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 190, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 190, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                                        </div>
                                        
                                        <div class="c-card__section c-meta">
                                            <div>
                                                <?php $this->assign('authors', $this->_tpl_vars['item']->get_authors()); ?>
                                                <?php if ($this->_tpl_vars['authors']): ?>
                                                    <?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['author']):
?>
                                                        <span class="u-color-inherit c-author-list c-author-list--compact"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->get_name())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                                    <?php endforeach; endif; unset($_from); ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <span class="c-meta__item" data-test="article.type">
                                                <?php $this->assign('category', $this->_tpl_vars['item']->get_category()); ?>
                                                <?php if ($this->_tpl_vars['category']): ?>
                                                    <span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['category']->get_label())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                                <?php endif; ?>
                                            </span>
                                            
                                            <time class="c-meta__item" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['item']->get_date('Y-m-d'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['item']->get_date('d M Y'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</time>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </li>
                <?php endforeach; endif; unset($_from); ?>

                                <?php $_from = $this->_tpl_vars['feedData']['recent_items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
                    <li class="app-news-row__item title">
                        <div class="u-full-height title" data-native-ad-placement="false">
                            <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
                                <div class="c-card__layout u-full-height">
                                    
                                    <?php $this->assign('enclosure', $this->_tpl_vars['item']->get_enclosure()); ?>
                                    <?php if ($this->_tpl_vars['enclosure']): ?>
                                        <div class="c-card__image">
                                            <picture>
                                                <?php $_from = $this->_tpl_vars['enclosure']->get_link(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['thumbnailUrl']):
?>
                                                    <?php if ($this->_tpl_vars['thumbnailUrl']): ?>
                                                        <source type="image/webp" srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['thumbnailUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w, <?php echo ((is_array($_tmp=$this->_tpl_vars['thumbnailUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                                                        <img loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['thumbnailUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                                                    <?php endif; ?>
                                                <?php endforeach; endif; unset($_from); ?>
                                            </picture>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="c-card__body u-display-flex u-flex-direction-column">
                                        <h3 class="c-card__title" itemprop="name headline">
                                            <a class="c-card__link u-link-inherit" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['item']->get_permalink())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['item']->get_title())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, 'No Title') : smarty_modifier_default($_tmp, 'No Title')); ?>
</a>
                                        </h3>
                                        <div class="u-hide c-card__summary u-mb-16 u-hide-sm-max">
                                            <p><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['item']->get_description())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                                        </div>
                                        
                                        <div class="c-card__section c-meta">
                                            <div>
                                                <?php $this->assign('authors', $this->_tpl_vars['item']->get_authors()); ?>
                                                <?php if ($this->_tpl_vars['authors']): ?>
                                                    <?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['author']):
?>
                                                        <span class="c-author-list c-author-list--compact"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->get_name())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                                    <?php endforeach; endif; unset($_from); ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <span class="c-meta__item" data-test="article.type">
                                                <?php $this->assign('category', $this->_tpl_vars['item']->get_category()); ?>
                                                <?php if ($this->_tpl_vars['category']): ?>
                                                    <span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['category']->get_label())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                                <?php endif; ?>
                                            </span>
                                            
                                            <time class="c-meta__item" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['item']->get_date('Y-m-d'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['item']->get_date('d M Y'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</time>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </li>
                <?php endforeach; endif; unset($_from); ?>
                
            </ul>
        </div>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
</div>