# Turnstile Cloudflare untuk OJS v2.4.8.2

Integrasi Turnstile Cloudflare untuk sistem Open Journal Systems (OJS) versi 2.4.8.2 dengan dukungan PHP 5.4.

## 📁 Struktur File

```
/plugins/themes/[nama_tema]/
├── php/turnstile/
│   ├── config.php              # Konfigurasi keys dan pengaturan
│   ├── TurnstileValidator.php   # Class validator utama
│   ├── helper.php              # Helper functions
│   ├── proxy.php               # AJAX endpoint untuk verifikasi
│   └── init.php                # Inisialisasi template
├── templates/user/
│   ├── login.tpl               # Template login dengan Turnstile
│   └── register.tpl            # Template register dengan Turnstile
├── styles/
│   └── turnstile.css           # Styling untuk widget
└── README.md                   # Dokumentasi ini
```

## 🚀 Instalasi

### 1. Setup Cloudflare Turnstile

1. Login ke [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Pilih akun Anda → **Turnstile**
3. Klik **Add Site**
4. Masukkan domain website OJS Anda
5. Pilih widget type: **Managed**
6. Simpan **Site Key** dan **Secret Key**

### 2. Upload File

Upload semua file sesuai dengan struktur di atas ke direktori tema OJS Anda.

### 3. Konfigurasi

Edit file `config.php`:

```php
define('TURNSTILE_SECRET_KEY', 'your-actual-secret-key-here');
define('TURNSTILE_SITE_KEY', 'your-actual-site-key-here');
```

### 4. Permissions

Pastikan file `proxy.php` memiliki permission yang tepat untuk diakses via web.

```bash
chmod 644 /plugins/themes/[nama_tema]/php/turnstile/proxy.php
```

## 🔧 Konfigurasi Lanjutan

### Timeout Verifikasi

```php
// Di config.php
define('TURNSTILE_TIMEOUT', 600); // 10 menit (default: 300 detik)
```

### Debug Mode

```php
// Di config.php
define('TURNSTILE_DEBUG', true); // Aktifkan logging
```

### Theme Widget

```php
// Di config.php
define('TURNSTILE_DEFAULT_THEME', 'dark'); // auto, light, dark
define('TURNSTILE_DEFAULT_SIZE', 'compact'); // normal, compact
```

## 🎨 Styling

Include CSS di header template:

```html
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/{$currentTheme->getPath()}/styles/turnstile.css" />
```

## 📝 Penggunaan

### Template Login/Register

Widget Turnstile akan otomatis muncul di halaman login dan register jika:
1. Path URL mengandung `/login` atau `/user/register`
2. Template menggunakan `{include}` untuk init.php

### Manual Integration

Untuk halaman lain, gunakan:

```smarty
{* Di template Smarty *}
{php}
include_once('plugins/themes/' . $this->get_template_vars('currentTheme')->getPath() . '/php/turnstile/init.php');
{/php}

{if $needsTurnstile}
<div id="turnstile-container"></div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
window.onloadTurnstileCallback = function() {
    turnstile.render('#turnstile-container', {
        sitekey: '{$turnstileSiteKey}',
        callback: function(token) {
            // Handle success
        }
    });
};
</script>
{/if}
```

## 🔍 Testing

### 1. Test Proxy Endpoint

Akses langsung: `https://yoursite.com/plugins/themes/[nama_tema]/php/turnstile/proxy.php`

Respons yang diharapkan:
```json
{"success":false,"error":"Method not allowed"}
```

### 2. Test Widget Loading

1. Buka halaman login
2. Widget Turnstile harus muncul
3. Cek console browser untuk error

### 3. Test Form Submission

1. Isi form login tanpa menyelesaikan Turnstile
2. Submit harus diblokir dengan pesan error
3. Selesaikan Turnstile, lalu submit → harus berhasil

## 🐛 Troubleshooting

### Widget Tidak Muncul

1. **Cek Console Browser**: Buka Developer Tools → Console
2. **Cek Network Tab**: Pastikan `api.js` Cloudflare berhasil dimuat
3. **Cek Site Key**: Pastikan Site Key benar di `config.php`
4. **Cek Path**: Pastikan path template dan file PHP benar

### Error "Method Not Allowed"

- Pastikan request ke `proxy.php` menggunakan method POST
- Cek permission file `proxy.php`

### Error "Gagal menghubungi server verifikasi"

1. **Cek cURL**: Pastikan PHP cURL extension aktif
2. **Cek Firewall**: Pastikan server bisa akses `challenges.cloudflare.com`
3. **Cek SSL**: Pastikan SSL certificate server valid

### Session Issues

```php
// Di awal file yang menggunakan session
if (!session_id()) {
    session_start();
}
```

## 🔒 Keamanan

### Best Practices

1. **HTTPS Required**: Turnstile hanya bekerja di HTTPS
2. **Validate Server-Side**: Selalu validasi di server, jangan hanya di client
3. **Rate Limiting**: Implementasi rate limiting untuk endpoint proxy
4. **IP Whitelist**: Pertimbangkan whitelist IP untuk admin

### Security Headers

Tambahkan di `.htaccess` atau server config:

```apache
# Untuk proxy.php
<Files "proxy.php">
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</Files>
```

## 📊 Monitoring

### Log Files

Aktifkan debug mode untuk monitoring:

```php
// Di config.php
define('TURNSTILE_DEBUG', true);
```

Log akan tersimpan di PHP error log server.

### Analytics

Monitor di Cloudflare Dashboard → Turnstile → Analytics untuk melihat:
- Jumlah verifikasi
- Success rate
- Error types
- Geographic distribution

## 🚨 Known Issues

### OJS v2.4.8.2 Specific

1. **Smarty Version**: OJS 2.4.8.2 menggunakan Smarty lama, gunakan `{php}` tags
2. **Session Handling**: Pastikan session sudah aktif sebelum include helper
3. **Template Caching**: Clear template cache jika ada perubahan

### PHP 5.4 Compatibility

1. **Array Syntax**: Gunakan `array()` bukan `[]`
2. **Closure**: Hindari arrow functions
3. **Short Tags**: Gunakan `<?php` lengkap

## 📞 Support

Jika mengalami masalah:

1. **Enable Debug Mode** di `config.php`
2. **Check Browser Console** untuk JavaScript errors
3. **Check Server Logs** untuk PHP errors
4. **Test Proxy Endpoint** secara langsung

## 📄 License

Kode ini bebas digunakan dan dimodifikasi sesuai kebutuhan.

## 🔄 Updates

- **v1.0**: Initial release dengan dukungan login/register
- **v1.1**: Penambahan CSS styling dan error handling
- **v1.2**: Dukungan responsive design dan dark theme