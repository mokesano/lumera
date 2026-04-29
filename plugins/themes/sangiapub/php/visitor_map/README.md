# Journal Visitor Country Map untuk OJS 2.4.8.2

Modul untuk menampilkan peta pengunjung jurnal berdasarkan negara dengan visualisasi heatmap menggunakan Highcharts Maps.

## 📁 Struktur File

```
plugins/themes/[nama-theme]/
├── php/
│   └── journal_stats/
│       ├── getJournalVisitorCountry.php    # File PHP utama
│       └── cache/                           # Direktori cache (auto-created)
│           ├── journal_[ID]_visitor_country.php
│           └── journal_[ID]_visitor_country.json.gz
└── templates/
    └── visitorCountryProxy.tpl              # Template Smarty
```

## 🚀 Fitur Utama

- **Smart Detection**: Cache otomatis ter-update jika ada perubahan data
- **Weekly Updates**: Cache expire setiap 7 hari
- **Dynamic Cache**: Direktori cache dibuat otomatis
- **Compressed Data**: Support JSON.gz untuk efisiensi
- **No Hardcoded Path**: Semua path dinamis berdasarkan theme aktif

## 📋 Instalasi

1. Copy file `getJournalVisitorCountry.php` ke:
   ```
   plugins/themes/[nama-theme]/php/journal_stats/
   ```

2. Copy template `visitorCountryProxy.tpl` ke folder templates theme Anda

3. Pastikan direktori writable untuk cache:
   ```bash
   chmod 755 plugins/themes/[nama-theme]/php/journal_stats/
   ```

## 💻 Cara Penggunaan

### 1. Versi Sederhana (Minimal)

```smarty
{* Include di template Smarty *}
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($visitorFile = 'plugins/themes/' . $matches[1] . '/php/journal_stats/getJournalVisitorCountry.php')) {
        include_once($visitorFile);
        $visitorData = getJournalVisitorCountry($this->_tpl_vars['currentJournal']->getId(), false);
        
        $jsonPath = Request::getBasePath() . '/plugins/themes/' . $matches[1] . '/php/journal_stats/cache/journal_' . $this->_tpl_vars['currentJournal']->getId() . '_visitor_country.json';
        $this->assign('visitorJsonPath', file_exists('.' . $jsonPath . '.gz') ? $jsonPath . '.gz' : $jsonPath);
        break;
    }
}
{/php}

{* Container untuk map *}
<div id="visitorCountryMap" data-json-path="{$visitorJsonPath}" class="u-mb-48">
  <div class="loading-visitor">Menyiapkan data peta pengunjung...</div>
</div>
```

### 2. Versi Lengkap (Dengan UI)

```smarty
{* Include proxy template *}
{include file="visitorCountryProxy.tpl"}
```

Template proxy otomatis menyediakan:
- Loading state
- Error handling  
- Peta interaktif dengan Highcharts
- Tabel top countries
- Tombol refresh

## 📊 Data yang Tersedia

### Variabel Template

| Variabel | Deskripsi | Contoh |
|----------|-----------|--------|
| `{$visitorJournalId}` | ID jurnal | 123 |
| `{$visitorJournalTitle}` | Nama jurnal | "Jurnal Ilmiah" |
| `{$visitorTotalUniqueVisitors}` | Total pengunjung unik | 15420 |
| `{$visitorTotalCountries}` | Jumlah negara | 87 |
| `{$visitorTopCountries}` | Array top 10 negara | Array |
| `{$visitorJsonPath}` | Path ke file JSON | "/cache/journal_123_visitor_country.json.gz" |
| `{$visitorCacheHit}` | Status cache | true/false |
| `{$visitorCalculationDate}` | Waktu perhitungan | "2025-01-20 10:30:00" |

### Struktur Data JSON

```json
{
    "journalId": 123,
    "journalTitle": "Nama Jurnal",
    "totalUniqueVisitors": 15420,
    "totalCountries": 87,
    "topCountries": [
        {
            "country_code": "ID",
            "unique_visitors": 8500,
            "total_metrics": 125000,
            "avg_metrics_per_visitor": 14.7
        },
        ...
    ],
    "yearlyCountryStats": {
        "2024": [...]
    },
    "calculationDate": "2025-01-20 10:30:00",
    "data_hash": "abc123..."
}
```

## 🔄 Cache Management

### Force Refresh

```
?refresh_visitor=true
```

### Cache Behavior

1. **Smart Detection**: 
   - Membuat hash dari total records, last update, dan country count
   - Jika hash berubah = cache invalid

2. **Weekly Expiry**:
   - Cache otomatis expire setelah 7 hari (604800 detik)
   - Mencegah data terlalu lama

3. **Cache Files**:
   - `.php` - Serialized PHP array (untuk backend)
   - `.json.gz` - Compressed JSON (untuk frontend)

## 🎨 Customisasi Map

### Mengubah Warna

```javascript
colorAxis: {
    min: 0,
    type: 'logarithmic',
    minColor: '#E6F7FF',    // Warna minimum
    maxColor: '#006BB3',    // Warna maksimum
    stops: [
        [0, '#E6F7FF'],
        [0.5, '#66B2FF'],
        [1, '#006BB3']
    ]
}
```

### Mengubah Tooltip

```javascript
tooltip: {
    pointFormatter: function() {
        return '<b>' + this.name + '</b><br/>' +
               'Pengunjung: <b>' + this.visitors + '</b><br/>' +
               'Total Akses: <b>' + this.value + '</b>';
    }
}
```

## 🛠️ Troubleshooting

### Cache tidak terbuat

1. Cek permission direktori:
   ```bash
   ls -la plugins/themes/[nama-theme]/php/journal_stats/
   ```

2. Cek error log:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

### Data tidak muncul

1. Pastikan tabel `metrics` ada dan memiliki kolom country
2. Cek apakah `gzcompress` tersedia:
   ```php
   php -r "echo function_exists('gzcompress') ? 'Available' : 'Not available';"
   ```

### Peta tidak tampil

1. Pastikan Highcharts Maps ter-load
2. Cek browser console untuk error JavaScript
3. Verifikasi JSON path benar dengan inspect element

## 📈 Performance Tips

1. **Enable Cache**: Selalu gunakan cache untuk performa optimal
2. **Compress JSON**: File .gz bisa 70% lebih kecil
3. **Limit Top Countries**: Default top 10 sudah cukup untuk visualisasi
4. **Weekly Updates**: Jangan set cache terlalu pendek

## 🔍 Debug Mode

Untuk debug, tambahkan di PHP:

```php
error_log("Visitor cache directory: " . $CACHE_DIR);
error_log("Cache file exists: " . (file_exists($cacheFile) ? 'Yes' : 'No'));
error_log("Data hash: " . $currentDataHash);
```

## 📝 Changelog

### v1.1.0
- Smart Detection dengan data hash
- Weekly automatic updates  
- Dynamic cache directory
- Improved error handling

### v1.0.0
- Initial release
- Basic visitor country statistics
- Cache support

## 🤝 Credits

Developed by Rochmady and Wizdam Team for OJS 2.4.8.2