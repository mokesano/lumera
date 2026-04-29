# 🚀 Hero Articles - Complete Implementation

## 📁 **File Structure:**
```
plugins/themes/[theme-name]/
├── php/article_hero/
│   ├── article_hero.php          # Main PHP file 
│   └── cache/
│       └── article_hero_1.json.gz # Auto-generated cache
└── templates/
    └── hero_section.tpl           # Template with {php} proxy
```

## 🧠 **Smart Hero Selection Logic:**

### **Klaster Pertama (Hero):**
1. **Default**: Artikel terbaru (tanggal publish)
2. **Override**: Jika ada artikel lain dalam 10 terbaru dengan views lebih tinggi → artikel itu jadi Hero
3. **Result**: 1 artikel hero yang ditampilkan prominent

### **Klaster Kedua (Latest):**
1. **Base**: 4 artikel terbaru setelah hero
2. **Smart Sorting**: Re-order berdasarkan views jika ada perbedaan signifikan (>50%)
3. **Result**: 4 artikel dalam grid layout

## 🔧 **Template Integration:**
```smarty
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($articleHeroFile = 'plugins/themes/' . $matches[1] . '/php/article_hero/article_hero.php')) {
        include_once($articleHeroFile);
        break;
    }
}
{/php}
```

## 📊 **Template Variables Available:**

### **Hero Article:**
- `$heroArticle` - Array dengan 1 artikel hero
- `$heroSelectionInfo` - Info mengapa artikel ini jadi hero

### **Latest Articles:**
- `$latestArticles` - Array dengan 4 artikel terbaru
- `$allLatestArticles` - Semua 10 artikel untuk referensi

### **Meta Info:**
- `$lastUpdateDate` - Tanggal update terakhir
- `$totalLatestArticles` - Total artikel yang ditemukan
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
    'total_views' => 150,
    'total_downloads' => 45,
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

## 🚀 **Smart Caching Features:**

### **Auto-Detection:**
- ✅ **Views Changes**: Detect perubahan jumlah views
- ✅ **Downloads Changes**: Detect perubahan jumlah downloads  
- ✅ **New Articles**: Detect artikel baru yang dipublish
- ✅ **Status Changes**: Detect perubahan status artikel
- ✅ **Daily Refresh**: Auto refresh setiap hari

### **Cache Performance:**
- 📁 **Format**: JSON.GZ compression (~70% space saving)
- 🔍 **Detection**: MD5 hash dari data artikel + tanggal
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

## 📱 **Responsive Design:**
- ✅ **Desktop**: Grid layout dengan hero prominent
- ✅ **Mobile**: Stack layout, hero tetap prominent
- ✅ **Performance**: Lazy loading untuk images
- ✅ **SEO**: Schema.org markup untuk search engines

## 🔧 **Usage Examples:**

### **Basic Hero Section:**
```smarty
{* Load hero articles *}
{php}/* include code */{/php}

{* Display hero *}
{if $heroArticle}
    {foreach from=$heroArticle item=article}
        <div class="hero">
            <h1>{$article.title}</h1>
            <p>Views: {$article.total_views}</p>
        </div>
    {/foreach}
{/if}
```

### **Latest Articles Grid:**
```smarty
{if $latestArticles}
    <div class="grid">
        {foreach from=$latestArticles item=article}
            <div class="card">
                <h3>{$article.title}</h3>
                <p>{$article.authors[0].full_name}</p>
            </div>
        {/foreach}
    </div>
{/if}
```

### **Debug Info:**
```smarty
{if $heroSelectionInfo}
    <p>Hero ID: {$heroSelectionInfo.hero_article_id}</p>
    <p>Hero Views: {$heroSelectionInfo.hero_views}</p>
    <p>Is Latest?: {$heroSelectionInfo.is_hero_latest}</p>
{/if}
```

## ✨ **Key Benefits:**
- 🎯 **Smart Selection**: Hero berdasarkan kombinasi recency + popularity
- ⚡ **High Performance**: Smart caching dengan auto-invalidation
- 🔄 **Dynamic**: Otomatis update saat ada perubahan data
- 📱 **Responsive**: Works perfectly di semua device
- 🛠️ **Easy Integration**: Simple {php} include di template