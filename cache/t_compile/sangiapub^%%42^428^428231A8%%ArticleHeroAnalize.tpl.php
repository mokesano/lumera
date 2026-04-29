<?php /* Smarty version 2.6.26, created on 2026-04-04 19:37:21
         compiled from common/featured/ArticleHeroAnalize.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'common/featured/ArticleHeroAnalize.tpl', 25, false),array('modifier', 'default', 'common/featured/ArticleHeroAnalize.tpl', 187, false),array('modifier', 'truncate', 'common/featured/ArticleHeroAnalize.tpl', 249, false),array('modifier', 'count', 'common/featured/ArticleHeroAnalize.tpl', 364, false),)), $this); ?>

<?php 
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($articleHeroFile = 'plugins/themes/' . $matches[1] . '/php/hero_futured/article_hero.php')) {
        include_once($articleHeroFile);
        break;
    }
}
 ?>

<?php if ($this->_tpl_vars['heroCandidatesScoring'] && $this->_tpl_vars['featuredCandidatesScoring']): ?>

<section class="hero-articles-section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Article</h2>
            <p class="section-description">Highlighting our most engaging content</p>
            <small class="last-update">Last Update: <?php echo ((is_array($_tmp=$this->_tpl_vars['lastUpdateDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y, %H:%M") : smarty_modifier_date_format($_tmp, "%d %B %Y, %H:%M")); ?>
</small>
        </div>
        
                <?php if ($this->_tpl_vars['heroArticle'] && count ( $this->_tpl_vars['heroArticle'] ) > 0): ?>
            <?php $_from = $this->_tpl_vars['heroArticle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
            <div class="hero-article-container u-hide">
                <article class="hero-article" itemscope itemtype="http://schema.org/ScholarlyArticle">
                    <div class="hero-layout">
                                                <?php if ($this->_tpl_vars['article']['cover_image']['file_exists']): ?>
                        <div class="hero-image">
                            <img src="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
" 
                                 alt="<?php echo $this->_tpl_vars['article']['title']; ?>
" 
                                 itemprop="image"
                                 class="hero-cover">
                        </div>
                        <?php endif; ?>
                        
                        <div class="hero-content">
                                                        <div class="hero-meta">
                                <span class="article-type"><?php echo $this->_tpl_vars['article']['article_type']; ?>
</span>
                                <?php if ($this->_tpl_vars['article']['is_open_access']): ?>
                                    <span class="open-access-badge">Open Access</span>
                                <?php endif; ?>
                                <time datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished">
                                    <?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>

                                </time>
                            </div>
                            
                                                        <h3 class="hero-title" itemprop="name headline">
                                <a href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" itemprop="url">
                                    <?php echo $this->_tpl_vars['article']['title']; ?>

                                </a>
                            </h3>
                            
                                                        <?php if ($this->_tpl_vars['article']['abstract']): ?>
                            <div class="hero-abstract" itemprop="description">
                                <p><?php echo $this->_tpl_vars['article']['abstract']; ?>
</p>
                            </div>
                            <?php endif; ?>
                            
                                                        <?php if ($this->_tpl_vars['article']['authors'] && count ( $this->_tpl_vars['article']['authors'] ) > 0): ?>
                            <div class="hero-authors">
                                <strong>Authors:</strong>
                                <?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?>
                                    <span itemprop="creator" itemscope itemtype="http://schema.org/Person">
                                        <span itemprop="name"><?php echo $this->_tpl_vars['author']['full_name']; ?>
</span>
                                    </span><?php if (! ($this->_foreach['authorLoop']['iteration'] == $this->_foreach['authorLoop']['total'])): ?>, <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?>
                            </div>
                            <?php endif; ?>
                            
                                                        <div class="hero-stats">
                                <span class="stat-item">
                                    <i class="icon-eye"></i> <?php echo $this->_tpl_vars['article']['total_views']; ?>
 views
                                </span>
                                <span class="stat-item">
                                    <i class="icon-download"></i> <?php echo $this->_tpl_vars['article']['total_downloads']; ?>
 downloads
                                </span>
                                <?php if ($this->_tpl_vars['article']['doi']): ?>
                                <span class="stat-item">
                                    DOI: <?php echo $this->_tpl_vars['article']['doi']; ?>

                                </span>
                                <?php endif; ?>
                            </div>
                            
                                                        <?php if ($this->_tpl_vars['article']['keywords'] && count ( $this->_tpl_vars['article']['keywords'] ) > 0): ?>
                            <div class="hero-keywords">
                                <strong>Keywords:</strong>
                                <?php $_from = $this->_tpl_vars['article']['keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['keywordLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['keywordLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['keyword']):
        $this->_foreach['keywordLoop']['iteration']++;
?>
                                    <span class="keyword-tag"><?php echo $this->_tpl_vars['keyword']; ?>
</span><?php if (! ($this->_foreach['keywordLoop']['iteration'] == $this->_foreach['keywordLoop']['total'])): ?>, <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?>
                            </div>
                            <?php endif; ?>
                            
                                                        <div class="hero-actions">
                                <a href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" class="btn btn-primary btn-lg">
                                    Read Full Article
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; endif; unset($_from); ?>
        <?php else: ?>
            <div class="hero-placeholder u-hide">
                <h3>No featured article available</h3>
                <p>Featured articles will appear here when content is available.</p>
            </div>
        <?php endif; ?>
        
                <?php if ($this->_tpl_vars['latestArticles'] && count ( $this->_tpl_vars['latestArticles'] ) > 0): ?>
        <div class="latest-articles-section u-hide">
            <h4>Latest Articles</h4>
            <div class="latest-articles-grid">
                <?php $_from = $this->_tpl_vars['latestArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
                <article class="latest-article-card" itemscope itemtype="http://schema.org/ScholarlyArticle">
                                        <?php if ($this->_tpl_vars['article']['cover_image']['file_exists']): ?>
                    <div class="card-image">
                        <img src="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
" 
                             alt="<?php echo $this->_tpl_vars['article']['title']; ?>
" 
                             itemprop="image">
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-content">
                                                <div class="card-meta">
                            <span class="article-type"><?php echo $this->_tpl_vars['article']['article_type']; ?>
</span>
                            <?php if ($this->_tpl_vars['article']['is_open_access']): ?>
                                <span class="oa-badge">OA</span>
                            <?php endif; ?>
                            <time datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
">
                                <?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>

                            </time>
                        </div>
                        
                                                <h5 class="card-title" itemprop="name headline">
                            <a href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" itemprop="url">
                                <?php echo $this->_tpl_vars['article']['title']; ?>

                            </a>
                        </h5>
                        
                                                <?php if ($this->_tpl_vars['article']['authors'] && count ( $this->_tpl_vars['article']['authors'] ) > 0): ?>
                        <div class="card-authors">
                            <?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?>
                                <span itemprop="creator"><?php echo $this->_tpl_vars['author']['full_name']; ?>
</span><?php if (! ($this->_foreach['authorLoop']['iteration'] == $this->_foreach['authorLoop']['total'])): ?>, <?php endif; ?>
                            <?php endforeach; endif; unset($_from); ?>
                        </div>
                        <?php endif; ?>
                        
                                                <div class="card-stats">
                            <span><?php echo $this->_tpl_vars['article']['total_views']; ?>
 views</span>
                            <span><?php echo $this->_tpl_vars['article']['total_downloads']; ?>
 downloads</span>
                        </div>
                    </div>
                </article>
                <?php endforeach; endif; unset($_from); ?>
            </div>
        </div>
        <?php endif; ?>
        
                
                <?php if ($this->_tpl_vars['heroSelectionInfo']): ?>
        <div class="hero-debug-info u-hide" style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; font-size: 0.875rem;">
            <strong>🎯 Hero Selection Info:</strong><br>
            Mode: <?php echo ((is_array($_tmp=@$this->_tpl_vars['heroSelectionInfo']['mode'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Not Set') : smarty_modifier_default($_tmp, 'Not Set')); ?>
<br>
            Selection Method: <?php echo ((is_array($_tmp=@$this->_tpl_vars['heroSelectionInfo']['selection_method'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Not Set') : smarty_modifier_default($_tmp, 'Not Set')); ?>
<br>
            Total Candidates: <?php echo ((is_array($_tmp=@$this->_tpl_vars['heroSelectionInfo']['total_candidates'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)); ?>
<br>
            <?php if ($this->_tpl_vars['heroSelectionInfo']['grace_period_active']): ?>
                Grace Period: ✅ Active<br>
                <?php if ($this->_tpl_vars['heroSelectionInfo']['days_since_publish']): ?>
                    Days Since Publish: <?php echo $this->_tpl_vars['heroSelectionInfo']['days_since_publish']; ?>
<br>
                <?php endif; ?>
            <?php else: ?>
                Grace Period: ❌ Expired<br>
            <?php endif; ?>
            Hero Article ID: <?php echo ((is_array($_tmp=@$this->_tpl_vars['heroSelectionInfo']['hero_article_id'])) ? $this->_run_mod_handler('default', true, $_tmp, 'None') : smarty_modifier_default($_tmp, 'None')); ?>
<br>            
            <?php if ($this->_tpl_vars['heroSelectionInfo']['hero_views']): ?>
                Hero Views: <?php echo $this->_tpl_vars['heroSelectionInfo']['hero_views']; ?>
<br>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['heroSelectionInfo']['hero_downloads']): ?>
                Hero Downloads: <?php echo $this->_tpl_vars['heroSelectionInfo']['hero_downloads']; ?>
<br>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['heroSelectionInfo']['hero_score']): ?>
                Selection Score: <?php echo $this->_tpl_vars['heroSelectionInfo']['hero_score']; ?>
<br>
            <?php endif; ?>
            
            <?php if ($this->_tpl_vars['heroSelectionInfo']['reason']): ?>
                Reason: <?php echo $this->_tpl_vars['heroSelectionInfo']['reason']; ?>
<br>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="hero-debug-info" style="margin-top: 2rem; padding: 1rem; background: #ffebee; border-radius: 5px; font-size: 0.875rem; color: #c62828;">
            <strong>⚠️ Hero Selection Info:</strong> NOT AVAILABLE
            <br><small>Variable $heroSelectionInfo is not assigned to template</small>
        </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['heroCandidatesScoring']): ?>
        <div class="hero-scoring-section" style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
            <h3 style="margin-bottom: 1rem; color: #495057;">🏆 Hero Selection Scoring Analysis</h3>
            
            <div class="scoring-table-container" style="overflow-x: auto;">
                <table class="scoring-table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <thead>
                        <tr style="background: #007bff; color: white;">
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Rank</th>
                            <th style="padding: 0.5rem; text-align: left; font-size: 0.8rem;width: 40%;">Article</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Views</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Downloads</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Recency</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Grace</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem; background: #28a745;"><strong>Total</strong></th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $_from = $this->_tpl_vars['heroCandidatesScoring']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['candidate']):
?>
                        <tr style="border-bottom: 1px solid #dee2e6; <?php if ($this->_tpl_vars['candidate']['article_id'] == $this->_tpl_vars['heroSelectionInfo']['hero_article_id']): ?>background: #d4edda; font-weight: bold;<?php elseif ($this->_tpl_vars['candidate']['is_in_grace_period']): ?>background: #fff3cd;<?php endif; ?>">
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem;vertical-align: middle;">
                                <span style="display: inline-block; width: 20px; height: 20px; background: <?php if ($this->_tpl_vars['candidate']['final_rank'] == 1): ?>#28a745<?php elseif ($this->_tpl_vars['candidate']['final_rank'] <= 3): ?>#ffc107<?php else: ?>#6c757d<?php endif; ?>; color: white; text-align: center; border-radius: 50%; line-height: 20px; font-weight: bold; font-size: 0.7rem;">
                                    <?php echo $this->_tpl_vars['candidate']['final_rank']; ?>

                                </span>
                            </td>
                            <td style="padding: 0.5rem; font-size: 0.75rem;vertical-align: middle;">
                                <strong>ID: <?php echo $this->_tpl_vars['candidate']['article_id']; ?>
</strong><br>
                                <small style="color: #6c757d;"><?php echo ((is_array($_tmp=$this->_tpl_vars['candidate']['title'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 170) : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 170)); ?>
</small><br>
                                <small style="color: #007bff;">📅 <?php echo $this->_tpl_vars['candidate']['days_since_publish']; ?>
 days ago</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #007bff;vertical-align: middle;">
                                <strong><?php echo $this->_tpl_vars['candidate']['views_score']; ?>
</strong>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #28a745;vertical-align: middle;">
                                <strong><?php echo $this->_tpl_vars['candidate']['downloads_score']; ?>
</strong><br>
                                <small style="color: #6c757d;">( <?php echo $this->_tpl_vars['candidate']['total_downloads']; ?>
 × 2 )</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #ffc107;vertical-align: middle;">
                                <strong><?php echo $this->_tpl_vars['candidate']['recency_score']; ?>
</strong>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #e83e8c;vertical-align: middle;">
                                <strong><?php echo $this->_tpl_vars['candidate']['grace_period_bonus']; ?>
</strong>
                                <?php if ($this->_tpl_vars['candidate']['is_in_grace_period']): ?><br><small style="color: #28a745;">✅</small><?php endif; ?>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.8rem; background: <?php if ($this->_tpl_vars['candidate']['article_id'] == $this->_tpl_vars['heroSelectionInfo']['hero_article_id']): ?>#28a745<?php else: ?>#f8f9fa<?php endif; ?>; color: <?php if ($this->_tpl_vars['candidate']['article_id'] == $this->_tpl_vars['heroSelectionInfo']['hero_article_id']): ?>white<?php else: ?>#495057<?php endif; ?>;vertical-align: middle;">
                                <strong><?php echo $this->_tpl_vars['candidate']['total_score']; ?>
</strong>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.7rem;vertical-align: middle;">
                                <?php if ($this->_tpl_vars['candidate']['article_id'] == $this->_tpl_vars['heroSelectionInfo']['hero_article_id']): ?>
                                    <span style="background: #28a745; color: white; padding: 0.2rem 0.4rem; border-radius: 3px; font-weight: bold;">🏆 HERO</span>
                                <?php elseif ($this->_tpl_vars['candidate']['final_rank'] <= 5): ?>
                                    <span style="background: #17a2b8; color: white; padding: 0.2rem 0.4rem; border-radius: 3px;">⭐ FEATURED</span>
                                <?php else: ?>
                                    <span style="background: #6c757d; color: white; padding: 0.2rem 0.4rem; border-radius: 3px;">📄 CANDIDATE</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; unset($_from); ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1rem; padding: 0.75rem; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 0 5px 5px 0; font-size: 0.8rem;">
                <strong>📊 Scoring Formula:</strong> Views + (Downloads × 2) + Recency + Grace Bonus = Total Score<br>
                <strong>🎯 Selection:</strong> <?php if ($this->_tpl_vars['heroSelectionInfo']['grace_period_active']): ?>Grace Period Mode (newest article wins)<?php else: ?>Highest Score Wins<?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['featuredCandidatesScoring']): ?>
        <div class="featured-scoring-section" style="margin-top: 2rem; padding: 1.5rem; background: #f1f8ff; border-radius: 8px;">
            <h3 style="margin-bottom: 1rem; color: #495057;">⭐ Featured Articles Scoring</h3>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <thead>
                        <tr style="background: #17a2b8; color: white;">
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Rank</th>
                            <th style="padding: 0.5rem; text-align: left; font-size: 0.8rem;width: 50%;">Article</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;width: 30%;">Score</th>
                            <th style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">Selected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $_from = $this->_tpl_vars['featuredCandidatesScoring']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['candidate']):
?>
                        <tr style="border-bottom: 1px solid #dee2e6; <?php if ($this->_tpl_vars['candidate']['selected_as_featured']): ?>background: #d1ecf1; font-weight: bold;<?php endif; ?>">
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem;">
                                <span style="display: inline-block; width: 20px; height: 20px; background: <?php if ($this->_tpl_vars['candidate']['selected_as_featured']): ?>#17a2b8<?php else: ?>#6c757d<?php endif; ?>; color: white; text-align: center; border-radius: 50%; line-height: 20px; font-weight: bold; font-size: 0.7rem;">
                                    <?php echo $this->_tpl_vars['candidate']['final_rank']; ?>

                                </span>
                            </td>
                            <td style="padding: 0.5rem; font-size: 0.75rem;">
                                <strong>ID: <?php echo $this->_tpl_vars['candidate']['article_id']; ?>
</strong><br>
                                <small style="color: #6c757d;"><?php echo ((is_array($_tmp=$this->_tpl_vars['candidate']['title'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 170) : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 170)); ?>
</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">
                                <strong><?php echo $this->_tpl_vars['candidate']['total_score']; ?>
</strong><br>
                                <small style="color: #6c757d;">Views: <?php echo $this->_tpl_vars['candidate']['views_score']; ?>
 - Downloads: <?php echo $this->_tpl_vars['candidate']['downloads_score']; ?>
 - Recency: <?php echo $this->_tpl_vars['candidate']['recency_score']; ?>
 - Grace: <?php echo $this->_tpl_vars['candidate']['grace_period_bonus']; ?>
</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.7rem;vertical-align: middle;">
                                <?php if ($this->_tpl_vars['candidate']['selected_as_featured']): ?>
                                    <span style="background: #17a2b8; color: white; padding: 0.2rem 0.4rem; border-radius: 3px; font-weight: bold;">⭐ YES</span>
                                <?php else: ?>
                                    <span style="background: #6c757d; color: white; padding: 0.2rem 0.4rem; border-radius: 3px;">❌ NO</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; unset($_from); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

                <?php if ($this->_tpl_vars['cacheInfo']): ?>
        <div class="cache-debug-info" style="margin-top: 1rem; padding: 1rem; background: #e8f4f8; border-radius: 5px; font-size: 0.875rem;text-align: center;">
            <strong>🔧 Cache Info:</strong>
            Cache <?php if ($this->_tpl_vars['cacheInfo']['hit']): ?>Hit<?php else: ?>Miss<?php endif; ?> | 
            File: <?php echo $this->_tpl_vars['cacheInfo']['file']; ?>
 | 
            Hash: <?php echo $this->_tpl_vars['cacheInfo']['hash']; ?>
 | 
            Last Update: <?php echo $this->_tpl_vars['lastUpdateDate']; ?>
 | 
            Total Articles: <?php echo $this->_tpl_vars['totalLatestArticles']; ?>
<br>
            <small>
                Cache Dir: <?php if ($this->_tpl_vars['cacheInfo']['cache_dir_exists']): ?>✅ Exists<?php else: ?>❌ Missing<?php endif; ?> | 
                Writable: <?php if ($this->_tpl_vars['cacheInfo']['cache_dir_writable']): ?>✅ Yes<?php else: ?>❌ No<?php endif; ?> | 
                File Size: <?php echo $this->_tpl_vars['cacheInfo']['cache_file_size']; ?>
 bytes
            </small>
        </div>
        <?php else: ?>
        <div class="cache-debug-info" style="margin-top: 1rem; padding: 1rem; background: #ffebee; border-radius: 5px; font-size: 0.875rem; color: #c62828;text-align: center;">
            <strong>⚠️ Cache Info:</strong> NOT AVAILABLE
            <br><small>Variable $cacheInfo is not assigned to template</small>
        </div>
        <?php endif; ?>

                <div style="margin-top: 1rem;margin-bottom: 3rem;padding: 1rem; background: #f3e5f5; border-radius: 5px; font-size: 0.875rem;text-align: center;">
            <strong>📊 Basic Data Test:</strong> 
            Total Latest Articles: <?php echo ((is_array($_tmp=@$this->_tpl_vars['totalLatestArticles'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Not Set') : smarty_modifier_default($_tmp, 'Not Set')); ?>
 | 
            Last Update: <?php echo ((is_array($_tmp=@$this->_tpl_vars['lastUpdateDate'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Not Set') : smarty_modifier_default($_tmp, 'Not Set')); ?>
 | 
            Hero Article Count: <?php if ($this->_tpl_vars['heroArticle']): ?><?php echo count($this->_tpl_vars['heroArticle']); ?>
<?php else: ?>0<?php endif; ?> | 
            Latest Articles Count: <?php if ($this->_tpl_vars['latestArticles']): ?><?php echo count($this->_tpl_vars['latestArticles']); ?>
<?php else: ?>0<?php endif; ?>
        </div>
        
    </div>
</section>

<?php endif; ?>