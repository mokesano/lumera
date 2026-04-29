# 📊 Most Popular Articles - OJS Plugin

A smart, high-performance system for displaying the most viewed articles in Open Journal Systems (OJS) v2.4.8.2 with intelligent caching and automatic data change detection.

## ✨ Features

- 🚀 **Smart Caching**: Auto-detects changes in article views and regenerates cache only when needed
- 📊 **Three-Tier Clustering**: Organizes articles into hero, secondary, and tertiary display groups
- ⚡ **High Performance**: JSON.GZ compression for optimal cache storage and loading speed
- 🔄 **Auto-Invalidation**: Automatically refreshes when article data changes
- 📱 **Responsive Design**: Works perfectly on all device sizes
- 🎨 **Schema.org Ready**: Full structured data markup for SEO
- 🖼️ **Multi-Locale Cover Images**: Supports covers in multiple languages
- 👥 **Robust Author Handling**: Smart fallback for author name display

## 🏗️ Architecture

### File Structure
```
plugins/themes/[theme-name]/
├── php/most_popular/
│   ├── most_popular.php           # Main processing file
│   └── cache/
│       └── popular_articles_1.json.gz  # Auto-generated cache
└── templates/
    └── popular_section.tpl        # Display template
```

### Data Flow
```
Page Load → Hash Check → Cache Hit? → Display
     ↓           ↓           ↑
Data Change → Generate → Save Cache
```

## 🎯 Article Clustering

### Cluster 1 (Hero)
- **Count**: 1 article
- **Criteria**: Highest view count
- **Display**: Featured layout with full details

### Cluster 2 (Secondary Tier)
- **Count**: 4 articles (ranks 2-5)
- **Display**: Card grid layout

### Cluster 3 (Tertiary Tier)
- **Count**: 4 articles (ranks 6-9)
- **Display**: Compact list layout

## 🚀 Installation

### 1. File Placement
Copy `most_popular.php` to your theme directory:
```
plugins/themes/[your-theme]/php/most_popular/most_popular.php
```

### 2. Template Integration
Add the following code to your template file:

```smarty
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($mostPopularFile = 'plugins/themes/' . $matches[1] . '/php/most_popular/most_popular.php')) {
        include_once($mostPopularFile);
        break;
    }
}
{/php}
```

### 3. Display Articles
Use the assigned template variables:

```smarty
{* Hero Article *}
{if $topArticle}
    {foreach from=$topArticle item=article}
        <h2>{$article.title}</h2>
        <p>Views: {$article.total_views}</p>
    {/foreach}
{/if}

{* Secondary Articles *}
{if $secondTierArticles}
    <div class="article-grid">
        {foreach from=$secondTierArticles item=article}
            <div class="article-card">
                <h3>{$article.title}</h3>
                <span>{$article.total_views} views</span>
            </div>
        {/foreach}
    </div>
{/if}
```

## 📊 Template Variables

### Main Article Groups
- `$topArticle` - Array with 1 most popular article
- `$secondTierArticles` - Array with 4 secondary popular articles  
- `$thirdTierArticles` - Array with 4 tertiary popular articles
- `$popularArticlesList` - All 10 articles for custom usage

### Meta Information
- `$totalPopularArticles` - Total number of articles found
- `$lastUpdateDate` - Last cache update timestamp
- `$cacheInfo` - Cache performance information

### Article Data Structure
Each article contains:
```php
[
    'article_id' => 123,
    'title' => 'Article Title',
    'abstract' => 'Article abstract text...',
    'authors' => [
        [
            'first_name' => 'John',
            'middle_name' => 'Middle',
            'last_name' => 'Doe', 
            'full_name' => 'John Middle Doe',
            'affiliation' => 'University Name',
            'email' => 'john@example.com'
        ]
    ],
    'total_views' => 1250,
    'date_published' => '2025-05-24 10:00:00',
    'date_published_formatted' => '2025-05-24',
    'is_open_access' => true,
    'article_type' => 'Research Article',
    'cover_image' => [
        'file_exists' => true,
        'file_url' => 'https://example.com/cover.jpg',
        'file_path' => 'public/journals/1/cover_article_123_en_US.jpg',
        'locale' => 'en_US',
        'extension' => 'jpg'
    ],
    'article_url' => 'https://example.com/article/view/123',
    'keywords' => ['keyword1', 'keyword2', 'keyword3'],
    'doi' => '10.1234/example.doi'
]
```

## ⚡ Caching System

### Smart Cache Features
- **Auto-Detection**: Monitors changes in article views, publications, and modifications
- **Hash-Based**: Uses MD5 hash of article data for change detection
- **Compression**: JSON.GZ format saves ~70% storage space
- **Daily Refresh**: Automatic daily cache refresh as fallback
- **Atomic Operations**: Prevents cache corruption during writes

### Cache Performance
- **Cache Hit**: Instant page load from compressed cache
- **Cache Miss**: Fresh data generation and cache update
- **File Location**: `plugins/themes/[theme]/php/most_popular/cache/`
- **Expiry**: 7 days (604800 seconds)

### Manual Cache Control
- **Force Refresh**: Add `?refresh=1` to any page URL
- **Debug Mode**: Add `?action=json` to see cache status and article data

## 🛠️ Configuration

### Cache Settings
```php
$cacheEnabled = true;                    // Enable/disable caching
$cacheExpiry = 604800;                   // Cache lifetime (7 days)
$cacheDir = 'plugins/themes/.../cache/'; // Cache directory
```

### Cover Image Support
The system searches for cover images in this order:
1. `cover_article_{id}_en_US.jpg`
2. `cover_article_{id}_id_ID.jpg`
3. `cover_article_{id}_en.jpg`
4. `cover_article_{id}_id.jpg`
5. `cover_article_{id}.jpg` (fallback)

Supported formats: JPG, JPEG, PNG, GIF

## 🧪 Testing & Debugging

### Test URLs
```bash
# Normal page load
https://yoursite.com/journal/

# JSON debug output
https://yoursite.com/journal/?action=json

# Force cache refresh
https://yoursite.com/journal/?refresh=1

# API format
https://yoursite.com/journal/?action=api&refresh=1
```

### Debug Information
JSON output includes:
- Cache hit/miss status
- Data hash for change detection
- Article clustering information
- Performance metrics
- File paths and permissions

### HTTP Headers
```
X-Journal-ID: 1
X-Last-Update: 2025-05-24 15:30:00
X-Cache-Hit: true
X-Data-Hash: a1b2c3d4
```

## 🎨 Template Examples

### Basic Display
```smarty
<section class="popular-articles">
    <h2>Most Popular Articles</h2>
    
    {if $topArticle}
        {foreach from=$topArticle item=article}
            <article class="hero-article">
                <h3><a href="{$article.article_url}">{$article.title}</a></h3>
                <p>Views: {$article.total_views}</p>
                <p>By: {$article.authors[0].full_name}</p>
            </article>
        {/foreach}
    {/if}
</section>
```

### Advanced Layout with Schema.org
```smarty
{if $topArticle}
    {foreach from=$topArticle item=article}
        <article itemscope itemtype="http://schema.org/ScholarlyArticle">
            <h2 itemprop="name">{$article.title}</h2>
            
            {if $article.authors}
                <div itemprop="author">
                    {foreach from=$article.authors item=author name=authorLoop}
                        <span itemscope itemtype="http://schema.org/Person">
                            <span itemprop="name">{$author.full_name}</span>
                        </span>
                        {if !$smarty.foreach.authorLoop.last}, {/if}
                    {/foreach}
                </div>
            {/if}
            
            <div class="metrics">
                <span>{$article.total_views} views</span>
                <time itemprop="datePublished">{$article.date_published_formatted}</time>
            </div>
        </article>
    {/foreach}
{/if}
```

### Responsive Grid
```smarty
<div class="popular-grid">
    {if $secondTierArticles}
        {foreach from=$secondTierArticles item=article}
            <div class="article-card">
                {if $article.cover_image.file_exists}
                    <img src="{$article.cover_image.file_url}" alt="{$article.title}">
                {/if}
                <h4><a href="{$article.article_url}">{$article.title}</a></h4>
                <div class="meta">
                    <span>{$article.total_views} views</span>
                    <span>{$article.date_published_formatted}</span>
                </div>
            </div>
        {/foreach}
    {/if}
</div>
```

## 🔧 Customization

### Modifying Article Count
Change the `LIMIT` in the SQL query (line ~200):
```php
ORDER BY total_views DESC, pa.date_published DESC
LIMIT 15  // Change from 10 to 15
```

### Custom Clustering
Modify the clustering logic (line ~280):
```php
$clusteredData = array(
    'cluster_1' => array_slice($articles, 0, 2),  // 2 hero articles
    'cluster_2' => array_slice($articles, 2, 6),  // 6 secondary articles
    'cluster_3' => array_slice($articles, 8, 7)   // 7 tertiary articles
);
```

### Adding Custom Fields
Extend the article data array (line ~250):
```php
$articles[] = array(
    // ... existing fields ...
    'custom_field' => $article->getCustomField(),
    'citation_count' => getCitationCount($articleId),
    'social_shares' => getSocialShares($articleId)
);
```

## 🚨 Troubleshooting

### Cache Not Creating
1. Check directory permissions: `chmod 755 plugins/themes/[theme]/php/most_popular/cache/`
2. Verify PHP can write to the directory
3. Check error logs for permission issues

### Articles Not Showing
1. Verify articles are published and in published issues
2. Check if metrics data exists in the database
3. Use `?action=json` to see raw data

### Template Variables Empty
1. Ensure the PHP file is included correctly
2. Check file path in the template proxy code
3. Verify template variable names match

### Performance Issues
1. Monitor cache hit rate using `?action=json`
2. Check cache file sizes in the cache directory
3. Verify database query performance

## 📋 Requirements

- **OJS Version**: 2.4.8.2 or compatible
- **PHP Version**: 5.4+ (compatible syntax)
- **Extensions**: gzip support for compression
- **Permissions**: Write access to cache directory
- **Database**: Metrics table with view data

## 🤝 Support

For issues and questions:
1. Check the troubleshooting section above
2. Review template integration code
3. Test with `?action=json` for debugging
4. Verify file permissions and paths

## 📄 License

This code is provided as-is for OJS theme development. Modify and distribute according to your needs.

---

**Created by**: Inspired by Rochmady and Wizdam Team approach  
**Version**: 2.0  
**Last Updated**: 2025-05-24