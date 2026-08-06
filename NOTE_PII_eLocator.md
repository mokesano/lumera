# Note Penerapan PII dan eLocator

Berikut adalah penerapan lengkap dan presisi untuk kedua pendekatan tersebut. Anda dapat memilih salah satu yang paling sesuai dengan filosofi operasional tim ScholarWizdam Lumera.

---

### Opsi 1: Pendekatan Konfigurasi (`config.inc.php`)
*Pilihan ini paling fleksibel. Jika prefix perlu diubah di masa depan, admin hanya perlu mengedit file config tanpa menyentuh kode PHP sama sekali.*

#### 1. Tambahkan di `config.inc.php` (Cari bagian `[general]` atau buat bagian baru)
```ini
[identifiers]
; Prefix untuk eLocator (misal: 'd' atau 's')
elocator_prefix = "d"

; Prefix untuk PII (Publisher Item Identifier)
pii_prefix = "P"
```

#### 2. Penerapan di `classes/article/Article.inc.php` (atau `PublishedArticle.inc.php`)
```php
    /**
     * Get starting page (or eLocator if pages are empty).
     * @return string
     */
    public function getStartingPage() {
        $pagesStr = $this->getPages(); 
        
        // [LUMERA] Ambil prefix dari config, dengan fallback 'd' jika belum didefinisikan
        $prefix = Config::getVar('identifiers', 'elocator_prefix') ?: 'd';
        
        // Gunakan preg_quote agar aman jika prefix suatu hari nanti berisi karakter khusus regex
        $pattern = '/^' . preg_quote($prefix, '/') . '\d+$/i';
        
        if (preg_match($pattern, $pagesStr)) {
            return $pagesStr;
        }

        if ($pagesStr !== '' && preg_match('/^[^\d]*(\d+)\D*(.*)$/', $pagesStr, $pages)) {
            return $pages[1] ?? '';
        }
        return '';
    }

    /**
     * Get ending page (returns empty if using eLocator).
     * @return string
     */
    public function getEndingPage() {
        $pagesStr = $this->getPages();
        
        // [LUMERA] Gunakan prefix yang sama dari config untuk konsistensi
        $prefix = Config::getVar('identifiers', 'elocator_prefix') ?: 'd';
        $pattern = '/^' . preg_quote($prefix, '/') . '\d+$/i';
        
        if (preg_match($pattern, $pagesStr)) {
            return ''; // Kosongkan ending page agar tidak tercetak ganda
        }

        if ($pagesStr !== '' && preg_match('/^[^\d]*(\d+)\D*(.*)$/', $pagesStr, $pages)) {
            return $pages[2] ?? '';
        }
        return '';
    }
```

#### 3. Penerapan di `classes/article/ArticleDAO.inc.php`
```php
    /**
     * [WORKER] Audit existing PII using strict mathematical check digit
     * TIDAK MEMBANDINGKAN dengan ISSN jurnal saat ini untuk menjaga integritas historis.
     * @param string $pii
     * @return bool
     */
    private function auditExistingPii(string $pii): bool {
        // [LUMERA] Ambil prefix PII dari config, fallback ke 'P'
        $piiPrefix = Config::getVar('identifiers', 'pii_prefix') ?: 'P';
        
        if (strlen($pii) !== 18 || substr($pii, 0, 1) !== $piiPrefix) { 
            return false;
        }

        $extractedIssn = substr($pii, 1, 8);
        $reconstructedIssn = substr($extractedIssn, 0, 4) . '-' . substr($extractedIssn, 4, 4);

        import('lib.pkp.classes.validation.ValidatorISSN');
        $validator = new ValidatorISSN();

        return $validator->isValid($reconstructedIssn);
    }

    /**
     * [WORKER] Generate and save eLocator
     * @param int $articleId
     * @return string
     */
    private function generateELocator(int $articleId): string {
        $secretSalt = Config::getVar('security', 'salt');
        if (empty($secretSalt)) {
            $secretSalt = 'KhayraSalsabilaRakhaIbrahim_' . Config::getVar('general', 'base_url');
        }

        $hashHex = substr(md5($articleId . microtime(true) . $secretSalt), 0, 8);
        $hashInt = hexdec($hashHex);
        $numeric7 = str_pad((string)($hashInt % 10000000), 7, '0', STR_PAD_LEFT);

        // [LUMERA] Gunakan prefix dari config
        $prefix = Config::getVar('identifiers', 'elocator_prefix') ?: 'd';
        $generatedELocator = $prefix . $numeric7;
        
        $this->updateSetting($articleId, 'eLocator', $generatedELocator, 'string');
        return $generatedELocator;
    }

    /**
     * [WORKER] Generate and save PII strictly relying on ValidatorISSN
     * Menghapus fallback date('ym'). PII harus mencerminkan tanggal terbit asli.
     * @param int $articleId
     * @param int $journalId
     * @param string|null $datePublished
     * @param string $eLocator
     * @return string
     */
    private function generatePii(int $articleId, int $journalId, ?string $datePublished, string $eLocator): string {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao ? $journalDao->getById($journalId) : null;
        
        $rawIssn = $journal ? ($journal->getSetting('onlineIssn') ?: $journal->getSetting('printIssn')) : '';
        
        import('lib.pkp.classes.validation.ValidatorISSN');
        $validator = new ValidatorISSN();

        if ($validator->isValid($rawIssn)) {
            $issnClean = str_replace('-', '', strtoupper($rawIssn));

            if (empty($datePublished)) {
                return ''; 
            }
            $yymm = date('ym', strtotime($datePublished));

            // Asumsi: eLocator selalu diawali dengan 1 karakter prefix
            $numeric7 = substr($eLocator, 1);
            $piiSuffix = substr($numeric7, 0, 5);
                        
            // [LUMERA] Gunakan prefix dari config
            $prefix = Config::getVar('identifiers', 'pii_prefix') ?: 'P';
            $generatedPii = $prefix . $issnClean . $yymm . $piiSuffix;
            
            $this->updateSetting($articleId, 'pii', $generatedPii, 'string');
            return $generatedPii;
        }

        return '';
    }
```

---

### Opsi 2: Pendekatan Konstanta Global (`PKPApplication.inc.php`)
*Pilihan ini paling kokoh secara struktural. Prefix dianggap sebagai aturan bisnis mutlak (hard rule) dari fork Lumera, bukan sekadar konfigurasi yang bisa diubah sembarangan.*

#### 1. Deklarasi (Pilih SATU lokasi pusat)
Tempatkan ini di file bootstrap utama atau kelas inti aplikasi Anda, misalnya di `classes/core/Application.inc.php` atau `includes/bootstrap.inc.php` (di bagian paling atas setelah `<?php`):

```php
// [LUMERA] Global Article Identifier Prefixes
// Menggunakan define() native PHP. Aman, cepat, dan otomatis tersedia di Smarty via {$smarty.const.ELOCATOR_PREFIX}
define('ELOCATOR_PREFIX', 'd');
define('PII_PREFIX', 'P');
```

#### 2. Penerapan di `classes/article/Article.inc.php` (atau `PublishedArticle.inc.php`)

```php
    /**
     * Get starting page (or eLocator if pages are empty).
     * @return string
     */
    public function getStartingPage() {
        $pagesStr = $this->getPages(); 
        
        // [LUMERA] Gunakan konstanta global PHP native. preg_quote memastikan keamanan regex.
        $pattern = '/^' . preg_quote(ELOCATOR_PREFIX, '/') . '\d+$/i';
        
        if (preg_match($pattern, $pagesStr)) {
            return $pagesStr;
        }

        if ($pagesStr !== '' && preg_match('/^[^\d]*(\d+)\D*(.*)$/', $pagesStr, $pages)) {
            return $pages[1] ?? '';
        }
        return '';
    }

    /**
     * Get ending page (returns empty if using eLocator).
     * @return string
     */
    public function getEndingPage() {
        $pagesStr = $this->getPages();
        
        $pattern = '/^' . preg_quote(ELOCATOR_PREFIX, '/') . '\d+$/i';
        
        if (preg_match($pattern, $pagesStr)) {
            return ''; 
        }

        if ($pagesStr !== '' && preg_match('/^[^\d]*(\d+)\D*(.*)$/', $pagesStr, $pages)) {
            return $pages[2] ?? '';
        }
        return '';
    }
```

#### 3. Penerapan di `classes/article/ArticleDAO.inc.php`

```php
    /**
     * [WORKER] Audit existing PII using strict mathematical check digit
     * @param string $pii
     * @return bool
     */
    private function auditExistingPii(string $pii): bool {
        // [LUMERA] Langsung bandingkan dengan konstanta global PHP
        if (strlen($pii) !== 18 || substr($pii, 0, 1) !== PII_PREFIX) { 
            return false;
        }

        $extractedIssn = substr($pii, 1, 8);
        $reconstructedIssn = substr($extractedIssn, 0, 4) . '-' . substr($extractedIssn, 4, 4);

        import('lib.pkp.classes.validation.ValidatorISSN');
        $validator = new ValidatorISSN();

        return $validator->isValid($reconstructedIssn);
    }

    /**
     * [WORKER] Generate and save eLocator
     * @param int $articleId
     * @return string
     */
    private function generateELocator(int $articleId): string {
        $secretSalt = Config::getVar('security', 'salt');
        if (empty($secretSalt)) {
            $secretSalt = 'KhayraSalsabilaRakhaIbrahim_' . Config::getVar('general', 'base_url');
        }

        $hashHex = substr(md5($articleId . microtime(true) . $secretSalt), 0, 8);
        $hashInt = hexdec($hashHex);
        $numeric7 = str_pad((string)($hashInt % 10000000), 7, '0', STR_PAD_LEFT);

        // [LUMERA] Gunakan konstanta global PHP
        $generatedELocator = ELOCATOR_PREFIX . $numeric7;
        
        $this->updateSetting($articleId, 'eLocator', $generatedELocator, 'string');
        return $generatedELocator;
    }

    /**
     * [WORKER] Generate and save PII strictly relying on ValidatorISSN
     * @param int $articleId
     * @param int $journalId
     * @param string|null $datePublished
     * @param string $eLocator
     * @return string
     */
    private function generatePii(int $articleId, int $journalId, ?string $datePublished, string $eLocator): string {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao ? $journalDao->getById($journalId) : null;
        
        $rawIssn = $journal ? ($journal->getSetting('onlineIssn') ?: $journal->getSetting('printIssn')) : '';
        
        import('lib.pkp.classes.validation.ValidatorISSN');
        $validator = new ValidatorISSN();

        if ($validator->isValid($rawIssn)) {
            $issnClean = str_replace('-', '', strtoupper($rawIssn));

            if (empty($datePublished)) {
                return ''; 
            }
            $yymm = date('ym', strtotime($datePublished));

            $numeric7 = substr($eLocator, 1);
            $piiSuffix = substr($numeric7, 0, 5);
                        
            // [LUMERA] Gunakan konstanta global PHP
            $generatedPii = PII_PREFIX . $issnClean . $yymm . $piiSuffix;
            
            $this->updateSetting($articleId, 'pii', $generatedPii, 'string');
            return $generatedPii;
        }

        return '';
    }
```

---

### Ringkasan Perbandingan untuk Keputusan Lumera:

| Aspek | Opsi 1: `config.inc.php` | Opsi 2: `PKPApplication` (Global Constant) |
| :--- | :--- | :--- |
| **Filosofi** | Konfigurasi yang fleksibel dan dapat diubah oleh admin tanpa menyentuh kode. | Aturan bisnis struktural mutlak yang tertanam dalam DNA fork Lumera. |
| **Performa** | Sangat sedikit overhead (membaca array config yang sudah di-cache). | Paling cepat (konstanta global langsung di-resolve oleh PHP engine). |
| **Keamanan Regex** | Aman (menggunakan `preg_quote` pada nilai config). | Aman (menggunakan `preg_quote` pada nilai konstanta). |
| **Rekomendasi** | Pilih ini jika Anda berencana membuat plugin/tema yang mungkin perlu mengubah prefix secara dinamis per-jurnal. | **Pilih ini** jika `'d'` dan `'P'` adalah standar baku ScholarWizdam Lumera yang tidak akan berubah. |

Kedua pendekatan ini sepenuhnya mematuhi kerangka kerja OJS 2.4.8.5, menghindari *over-engineering*, dan menjamin **Single Source of Truth** yang dapat diakses oleh semua kelas tanpa duplikasi. Silakan pilih yang paling resonan dengan visi Anda.
