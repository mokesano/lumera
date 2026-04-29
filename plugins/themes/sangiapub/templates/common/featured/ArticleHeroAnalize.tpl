{**
 * Article Hero Template Implementation - FIXED
 * Contoh lengkap penggunaan dengan PHP proxy dan data yang dihasilkan
 *}

{* Include article hero PHP dengan proxy yang sudah diperbaiki *}
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($articleHeroFile = 'plugins/themes/' . $matches[1] . '/php/hero_futured/article_hero.php')) {
        include_once($articleHeroFile);
        break;
    }
}
{/php}

{if $heroCandidatesScoring && $featuredCandidatesScoring}

{* Hero Article Section *}
<section class="hero-articles-section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Article</h2>
            <p class="section-description">Highlighting our most engaging content</p>
            <small class="last-update">Last Update: {$lastUpdateDate|date_format:"%d %B %Y, %H:%M"}</small>
        </div>
        
        {* Hero Article (Main Featured) *}
        {if $heroArticle && count($heroArticle) > 0}
            {foreach from=$heroArticle item=article}
            <div class="hero-article-container u-hide">
                <article class="hero-article" itemscope itemtype="http://schema.org/ScholarlyArticle">
                    <div class="hero-layout">
                        {* Cover Image *}
                        {if $article.cover_image.file_exists}
                        <div class="hero-image">
                            <img src="{$article.cover_image.file_url}" 
                                 alt="{$article.title}" 
                                 itemprop="image"
                                 class="hero-cover">
                        </div>
                        {/if}
                        
                        <div class="hero-content">
                            {* Article Meta *}
                            <div class="hero-meta">
                                <span class="article-type">{$article.article_type}</span>
                                {if $article.is_open_access}
                                    <span class="open-access-badge">Open Access</span>
                                {/if}
                                <time datetime="{$article.date_published_formatted}" itemprop="datePublished">
                                    {$article.date_published_formatted|date_format:"%d %b %Y"}
                                </time>
                            </div>
                            
                            {* Title *}
                            <h3 class="hero-title" itemprop="name headline">
                                <a href="{$article.article_url}" itemprop="url">
                                    {$article.title}
                                </a>
                            </h3>
                            
                            {* Abstract *}
                            {if $article.abstract}
                            <div class="hero-abstract" itemprop="description">
                                <p>{$article.abstract}</p>
                            </div>
                            {/if}
                            
                            {* Authors *}
                            {if $article.authors && count($article.authors) > 0}
                            <div class="hero-authors">
                                <strong>Authors:</strong>
                                {foreach from=$article.authors item=author name=authorLoop}
                                    <span itemprop="creator" itemscope itemtype="http://schema.org/Person">
                                        <span itemprop="name">{$author.full_name}</span>
                                    </span>{if !$smarty.foreach.authorLoop.last}, {/if}
                                {/foreach}
                            </div>
                            {/if}
                            
                            {* Statistics *}
                            <div class="hero-stats">
                                <span class="stat-item">
                                    <i class="icon-eye"></i> {$article.total_views} views
                                </span>
                                <span class="stat-item">
                                    <i class="icon-download"></i> {$article.total_downloads} downloads
                                </span>
                                {if $article.doi}
                                <span class="stat-item">
                                    DOI: {$article.doi}
                                </span>
                                {/if}
                            </div>
                            
                            {* Keywords *}
                            {if $article.keywords && count($article.keywords) > 0}
                            <div class="hero-keywords">
                                <strong>Keywords:</strong>
                                {foreach from=$article.keywords item=keyword name=keywordLoop}
                                    <span class="keyword-tag">{$keyword}</span>{if !$smarty.foreach.keywordLoop.last}, {/if}
                                {/foreach}
                            </div>
                            {/if}
                            
                            {* Action Button *}
                            <div class="hero-actions">
                                <a href="{$article.article_url}" class="btn btn-primary btn-lg">
                                    Read Full Article
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            {/foreach}
        {else}
            <div class="hero-placeholder u-hide">
                <h3>No featured article available</h3>
                <p>Featured articles will appear here when content is available.</p>
            </div>
        {/if}
        
        {* Latest Articles Grid *}
        {if $latestArticles && count($latestArticles) > 0}
        <div class="latest-articles-section u-hide">
            <h4>Latest Articles</h4>
            <div class="latest-articles-grid">
                {foreach from=$latestArticles item=article}
                <article class="latest-article-card" itemscope itemtype="http://schema.org/ScholarlyArticle">
                    {* Cover Image *}
                    {if $article.cover_image.file_exists}
                    <div class="card-image">
                        <img src="{$article.cover_image.file_url}" 
                             alt="{$article.title}" 
                             itemprop="image">
                    </div>
                    {/if}
                    
                    <div class="card-content">
                        {* Meta *}
                        <div class="card-meta">
                            <span class="article-type">{$article.article_type}</span>
                            {if $article.is_open_access}
                                <span class="oa-badge">OA</span>
                            {/if}
                            <time datetime="{$article.date_published_formatted}">
                                {$article.date_published_formatted|date_format:"%d %b %Y"}
                            </time>
                        </div>
                        
                        {* Title *}
                        <h5 class="card-title" itemprop="name headline">
                            <a href="{$article.article_url}" itemprop="url">
                                {$article.title}
                            </a>
                        </h5>
                        
                        {* Authors *}
                        {if $article.authors && count($article.authors) > 0}
                        <div class="card-authors">
                            {foreach from=$article.authors item=author name=authorLoop}
                                <span itemprop="creator">{$author.full_name}</span>{if !$smarty.foreach.authorLoop.last}, {/if}
                            {/foreach}
                        </div>
                        {/if}
                        
                        {* Stats *}
                        <div class="card-stats">
                            <span>{$article.total_views} views</span>
                            <span>{$article.total_downloads} downloads</span>
                        </div>
                    </div>
                </article>
                {/foreach}
            </div>
        </div>
        {/if}
        
        {* === DEBUG SECTION - WITH DETAILED SCORING === *}
        
        {* Hero Selection Info - ALWAYS SHOW *}
        {if $heroSelectionInfo}
        <div class="hero-debug-info u-hide" style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; font-size: 0.875rem;">
            <strong>🎯 Hero Selection Info:</strong><br>
            Mode: {$heroSelectionInfo.mode|default:'Not Set'}<br>
            Selection Method: {$heroSelectionInfo.selection_method|default:'Not Set'}<br>
            Total Candidates: {$heroSelectionInfo.total_candidates|default:0}<br>
            {if $heroSelectionInfo.grace_period_active}
                Grace Period: ✅ Active<br>
                {if $heroSelectionInfo.days_since_publish}
                    Days Since Publish: {$heroSelectionInfo.days_since_publish}<br>
                {/if}
            {else}
                Grace Period: ❌ Expired<br>
            {/if}
            Hero Article ID: {$heroSelectionInfo.hero_article_id|default:'None'}<br>            
            {if $heroSelectionInfo.hero_views}
                Hero Views: {$heroSelectionInfo.hero_views}<br>
            {/if}
            {if $heroSelectionInfo.hero_downloads}
                Hero Downloads: {$heroSelectionInfo.hero_downloads}<br>
            {/if}
            {if $heroSelectionInfo.hero_score}
                Selection Score: {$heroSelectionInfo.hero_score}<br>
            {/if}
            
            {if $heroSelectionInfo.reason}
                Reason: {$heroSelectionInfo.reason}<br>
            {/if}
        </div>
        {else}
        <div class="hero-debug-info" style="margin-top: 2rem; padding: 1rem; background: #ffebee; border-radius: 5px; font-size: 0.875rem; color: #c62828;">
            <strong>⚠️ Hero Selection Info:</strong> NOT AVAILABLE
            <br><small>Variable $heroSelectionInfo is not assigned to template</small>
        </div>
        {/if}

        {* DETAILED SCORING TABLES *}
        {if $heroCandidatesScoring}
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
                        {foreach from=$heroCandidatesScoring item=candidate}
                        <tr style="border-bottom: 1px solid #dee2e6; {if $candidate.article_id == $heroSelectionInfo.hero_article_id}background: #d4edda; font-weight: bold;{elseif $candidate.is_in_grace_period}background: #fff3cd;{/if}">
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem;vertical-align: middle;">
                                <span style="display: inline-block; width: 20px; height: 20px; background: {if $candidate.final_rank == 1}#28a745{elseif $candidate.final_rank <= 3}#ffc107{else}#6c757d{/if}; color: white; text-align: center; border-radius: 50%; line-height: 20px; font-weight: bold; font-size: 0.7rem;">
                                    {$candidate.final_rank}
                                </span>
                            </td>
                            <td style="padding: 0.5rem; font-size: 0.75rem;vertical-align: middle;">
                                <strong>ID: {$candidate.article_id}</strong><br>
                                <small style="color: #6c757d;">{$candidate.title|truncate:170}</small><br>
                                <small style="color: #007bff;">📅 {$candidate.days_since_publish} days ago</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #007bff;vertical-align: middle;">
                                <strong>{$candidate.views_score}</strong>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #28a745;vertical-align: middle;">
                                <strong>{$candidate.downloads_score}</strong><br>
                                <small style="color: #6c757d;">( {$candidate.total_downloads} × 2 )</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #ffc107;vertical-align: middle;">
                                <strong>{$candidate.recency_score}</strong>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem; color: #e83e8c;vertical-align: middle;">
                                <strong>{$candidate.grace_period_bonus}</strong>
                                {if $candidate.is_in_grace_period}<br><small style="color: #28a745;">✅</small>{/if}
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.8rem; background: {if $candidate.article_id == $heroSelectionInfo.hero_article_id}#28a745{else}#f8f9fa{/if}; color: {if $candidate.article_id == $heroSelectionInfo.hero_article_id}white{else}#495057{/if};vertical-align: middle;">
                                <strong>{$candidate.total_score}</strong>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.7rem;vertical-align: middle;">
                                {if $candidate.article_id == $heroSelectionInfo.hero_article_id}
                                    <span style="background: #28a745; color: white; padding: 0.2rem 0.4rem; border-radius: 3px; font-weight: bold;">🏆 HERO</span>
                                {elseif $candidate.final_rank <= 5}
                                    <span style="background: #17a2b8; color: white; padding: 0.2rem 0.4rem; border-radius: 3px;">⭐ FEATURED</span>
                                {else}
                                    <span style="background: #6c757d; color: white; padding: 0.2rem 0.4rem; border-radius: 3px;">📄 CANDIDATE</span>
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1rem; padding: 0.75rem; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 0 5px 5px 0; font-size: 0.8rem;">
                <strong>📊 Scoring Formula:</strong> Views + (Downloads × 2) + Recency + Grace Bonus = Total Score<br>
                <strong>🎯 Selection:</strong> {if $heroSelectionInfo.grace_period_active}Grace Period Mode (newest article wins){else}Highest Score Wins{/if}
            </div>
        </div>
        {/if}

        {* Featured Articles Scoring (if available) *}
        {if $featuredCandidatesScoring}
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
                        {foreach from=$featuredCandidatesScoring item=candidate}
                        <tr style="border-bottom: 1px solid #dee2e6; {if $candidate.selected_as_featured}background: #d1ecf1; font-weight: bold;{/if}">
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.75rem;">
                                <span style="display: inline-block; width: 20px; height: 20px; background: {if $candidate.selected_as_featured}#17a2b8{else}#6c757d{/if}; color: white; text-align: center; border-radius: 50%; line-height: 20px; font-weight: bold; font-size: 0.7rem;">
                                    {$candidate.final_rank}
                                </span>
                            </td>
                            <td style="padding: 0.5rem; font-size: 0.75rem;">
                                <strong>ID: {$candidate.article_id}</strong><br>
                                <small style="color: #6c757d;">{$candidate.title|truncate:170}</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.8rem;">
                                <strong>{$candidate.total_score}</strong><br>
                                <small style="color: #6c757d;">Views: {$candidate.views_score} - Downloads: {$candidate.downloads_score} - Recency: {$candidate.recency_score} - Grace: {$candidate.grace_period_bonus}</small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center; font-size: 0.7rem;vertical-align: middle;">
                                {if $candidate.selected_as_featured}
                                    <span style="background: #17a2b8; color: white; padding: 0.2rem 0.4rem; border-radius: 3px; font-weight: bold;">⭐ YES</span>
                                {else}
                                    <span style="background: #6c757d; color: white; padding: 0.2rem 0.4rem; border-radius: 3px;">❌ NO</span>
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        {/if}

        {* Cache Info *}
        {if $cacheInfo}
        <div class="cache-debug-info" style="margin-top: 1rem; padding: 1rem; background: #e8f4f8; border-radius: 5px; font-size: 0.875rem;text-align: center;">
            <strong>🔧 Cache Info:</strong>
            Cache {if $cacheInfo.hit}Hit{else}Miss{/if} | 
            File: {$cacheInfo.file} | 
            Hash: {$cacheInfo.hash} | 
            Last Update: {$lastUpdateDate} | 
            Total Articles: {$totalLatestArticles}<br>
            <small>
                Cache Dir: {if $cacheInfo.cache_dir_exists}✅ Exists{else}❌ Missing{/if} | 
                Writable: {if $cacheInfo.cache_dir_writable}✅ Yes{else}❌ No{/if} | 
                File Size: {$cacheInfo.cache_file_size} bytes
            </small>
        </div>
        {else}
        <div class="cache-debug-info" style="margin-top: 1rem; padding: 1rem; background: #ffebee; border-radius: 5px; font-size: 0.875rem; color: #c62828;text-align: center;">
            <strong>⚠️ Cache Info:</strong> NOT AVAILABLE
            <br><small>Variable $cacheInfo is not assigned to template</small>
        </div>
        {/if}

        {* Simple Variables Test *}
        <div style="margin-top: 1rem;margin-bottom: 3rem;padding: 1rem; background: #f3e5f5; border-radius: 5px; font-size: 0.875rem;text-align: center;">
            <strong>📊 Basic Data Test:</strong> 
            Total Latest Articles: {$totalLatestArticles|default:'Not Set'} | 
            Last Update: {$lastUpdateDate|default:'Not Set'} | 
            Hero Article Count: {if $heroArticle}{$heroArticle|@count}{else}0{/if} | 
            Latest Articles Count: {if $latestArticles}{$latestArticles|@count}{else}0{/if}
        </div>
        
    </div>
</section>

{/if}{** END DEBUG SECTION **}