# 🚀 Most Downloaded Articles - Complete Implementation

## 📁 **File Structure:**
```
plugins/themes/[theme-name]/
├── php/most_downloaded/
│   ├── most_downloaded.php           # Main PHP file 
│   └── cache/
│       └── most_downloaded_1.json.gz # Auto-generated cache
└── templates/
    └── downloaded_section.tpl        # Template with {php} proxy
```

## 🎯 **Clustering Logic (Sama seperti Most Popular):**

### **Klaster Pertama:**
- **1 artikel** dengan download **terbanyak**
- Ditampilkan sebagai **featured article** dengan layout prominent

### **Klaster Kedua:**
- **4 artikel** dengan download terbanyak **urutan ke-2 sampai ke-5**
- Ditampilkan dalam **grid layout** yang menarik

### **Klaster Ketiga:**
- **4 artikel** dengan download terbanyak **urutan ke-6 sampai ke-9**
- Ditampilkan dalam **list layout** yang compact

## 🔧 **Template Integration:**
```smarty
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($mostDownloadedFile = 'plugins/themes/' . $matches[1] . '/php/most_downloaded/most_downloaded.php')) {
        include_once($mostDownloadedFile);
        break;
    }
}
{/php}
```

## 📊 **Template Variables Available:**

### **Downloaded Articles:**
- `$topDownloadedArticle` - Array dengan 1 artikel paling banyak didownload
- `$secondTierDownloadedArticles` - Array dengan 4 artikel download tinggi
- `$thirdTierDownloadedArticles` - Array dengan 4 artikel download popular

### **Meta Info:**
- `$downloadedArticlesList` - Semua 10 artikel untuk referensi
- `$totalDownloadedArticles` - Total artikel yang ditemukan
- `$lastUpdateDate` - Tanggal update terakhir
- `$cacheInfo` - Informasi cache (hit/miss, file, hash)

## 🎯 **Data Fields per Article:**
```php
[
    'article_id' => 123,
    'title' => 'Article Title',
    'abstract' => 'Article abstract...',
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
    'total_downloads' => 250,     // 🎯 Main metric!
    'total_views' => 150,         // Bonus info
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

## 🚀 **Smart Caching Features (Sama seperti Most Popular):**

### **Auto-Detection:**
- ✅ **Download Changes**: Detect perubahan jumlah downloads (primary metric)
- ✅ **View Changes**: Detect perubahan jumlah views (secondary)
- ✅ **New Articles**: Detect artikel baru yang dipublish
- ✅ **Status Changes**: Detect perubahan status artikel
- ✅ **Daily Refresh**: Auto refresh setiap hari

### **Cache Performance:**
- 📁 **Format**: JSON.GZ compression (~70% space saving)
- 🔍 **Detection**: MD5 hash dari download data + tanggal
- ⚡ **Speed**: Cache hit = instant load, cache miss = fresh data
- 🔄 **Auto-Generate**: Cache dibuat otomatis saat load pertama

## 🧪 **Testing URLs:**

### **Normal Page Load:**
```
https://yoursite.com/journal/homepage
```

### **JSON Debug API:**
```
https://yoursite.com/journal/homepage?action=json
https://yoursite.com/journal/homepage?action=api
```

### **Force Refresh Cache:**
```
https://yoursite.com/journal/homepage?refresh=1
https://yoursite.com/journal/homepage?action=json&refresh=1
```

## 🎨 **Visual Design Features:**

### **Top Downloaded (Klaster 1):**
- 🟢 **Green gradient background** (download theme)
- 📊 **Prominent download counter** dengan badge
- 🖼️ **Large cover image** (300px width)
- 📝 **Full abstract** (300 chars)
- 🏷️ **Keywords display**
- 🔗 **DOI link** jika tersedia

### **Second Tier (Klaster 2):**
- 🎴 **Card layout** dengan hover effects
- 📊 **Download stats** di corner kanan atas
- 🖼️ **Medium cover image** (200px height)
- 📝 **Short excerpt** (120 chars)
- ⬇️ **Download button** dengan icon

### **Third Tier (Klaster 3):**
- 📋 **List layout** yang compact
- 📊 **Download badge** di kiri
- 📝 **Title only** dengan basic info
- ⬇️ **Small download button**

## 📱 **Responsive Features:**
- ✅ **Mobile-First**: Grid → single column di mobile
- ✅ **Touch-Friendly**: Button sizes optimal untuk mobile
- ✅ **Performance**: Lazy loading untuk images
- ✅ **SEO**: Schema.org markup lengkap

## 🔧 **Usage Examples:**

### **Basic Downloaded Section:**
```smarty
{* Load downloaded articles *}
{php}/* include code */{/php}

{* Display top downloaded *}
{if $topDownloadedArticle}
    {foreach from=$topDownloadedArticle item=article}
        <div class="top-downloaded">
            <h2>{$article.title}</h2>
            <p>Downloads: {$article.total_downloads}</p>
            <p>Views: {$article.total_views}</p>
        </div>
    {/foreach}
{/if}
```

### **Downloaded Grid:**
```smarty
{if $secondTierDownloadedArticles}
    <div class="grid">
        {foreach from=$secondTierDownloadedArticles item=article}
            <div class="card">
                <span class="downloads">{$article.total_downloads}</span>
                <h3>{$article.title}</h3>
                <p>{$article.authors[0].full_name}</p>
            </div>
        {/foreach}
    </div>
{/if}
```

### **All Downloaded List:**
```smarty
{if $downloadedArticlesList}
    <ul>
        {foreach from=$downloadedArticlesList item=article}
            <li>
                <a href="{$article.article_url}">
                    {$article.title}
                </a>
                <span>({$article.total_downloads} downloads)</span>
            </li>
        {/foreach}
    </ul>
{/if}
```

## 🆚 **Comparison dengan Most Popular:**

| Feature | Most Popular | Most Downloaded |
|---------|-------------|----------------|
| **Primary Metric** | `total_views` | `total_downloads` |
| **Template Variables** | `$topArticle` | `$topDownloadedArticle` |
| **Cache File** | `popular_articles_1.json.gz` | `most_downloaded_1.json.gz` |
| **Color Theme** | Blue/Purple | Green |
| **Icon** | 👁️ Views | ⬇️ Downloads |
| **Query Focus** | `metrics.metric_type = 'ojs::views'` | `metrics.metric_type = 'ojs::downloads'` |

## ✨ **Key Benefits:**
- 🎯 **Download-Focused**: Prioritas pada artikel yang paling banyak didownload
- ⚡ **High Performance**: Smart caching dengan auto-invalidation
- 🔄 **Dynamic**: Otomatis update saat ada perubahan download
- 📱 **Responsive**: Perfect di semua device sizes
- 🛠️ **Easy Integration**: Simple {php} include di template
- 🎨 **Beautiful UI**: Green-themed design khusus download metrics

**Ready to use!** 🎯 File cache `most_downloaded_1.json.gz` akan otomatis terbuat saat load pertama!