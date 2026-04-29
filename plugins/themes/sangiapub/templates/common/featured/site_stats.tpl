{* Kode untuk menampilkan jumlah total artikel view, download, dan penulis pada OJS v2.4.8.2 - Versi Dioptimasi *}

{* 1. Untuk Jurnal Tertentu *}
<div class="journal-stats article">
    {php}
    // Definisi konstanta ASSOC_TYPE jika belum tersedia
    if (!defined('ASSOC_TYPE_JOURNAL')) define('ASSOC_TYPE_JOURNAL', 256);
    if (!defined('ASSOC_TYPE_ISSUE')) define('ASSOC_TYPE_ISSUE', 257);
    if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
    if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);
    
    // Flag untuk memaksa refresh data
    $forceRefresh = Request::getUserVar('refresh_stats') == 'true';
    
    // Tanggal dan waktu untuk tracking update
    $lastUpdated = date('Y-m-d H:i:s');
    $this->assign('lastUpdated', $lastUpdated);
    
    // Mendapatkan DAO yang diperlukan
    $journalDao = DAORegistry::getDAO('JournalDAO');
    $articleDao = DAORegistry::getDAO('ArticleDAO');
    $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
    $issueDao = DAORegistry::getDAO('IssueDAO');
    $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
    $authorDao = DAORegistry::getDAO('AuthorDAO');
    
    // Cek keberadaan tabel metrics dan informasi struktur DB untuk diagnostik
    $metricsTableExists = "Tidak";
    $metricsColumns = "Tidak ditemukan";
    $articleStatsExists = "Tidak";
    $galleyStatsExists = "Tidak";
    $authorsTableExists = "Tidak";
    
    try {
        // Cek tabel metrics
        $result = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
        $metricsTableExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
        $result->Close();
        
        // Jika tabel metrics ada, cek kolomnya
        if ($metricsTableExists == "Ya") {
            $result = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
            $columns = array();
            while (!$result->EOF) {
                $columns[] = $result->fields[0];
                $result->MoveNext();
            }
            $result->Close();
            $metricsColumns = implode(", ", $columns);
        }
        
        // Cek tabel article_view_stats
        $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_view_stats'");
        $articleStatsExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
        $result->Close();
        
        // Cek tabel article_galley_view_stats
        $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'");
        $galleyStatsExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
        $result->Close();
        
        // Cek tabel authors
        $result = $articleDao->retrieve("SHOW TABLES LIKE 'authors'");
        $authorsTableExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
        $result->Close();
        
    } catch (Exception $e) {
        error_log("OJS Stats Error checking tables: " . $e->getMessage());
    }
    
    // Mendapatkan jurnal saat ini
    $journalId = null;
    $journalTitle = "Jurnal Tidak Ditemukan";
    
    // Coba dapatkan dari variabel template
    $journal = $this->get_template_vars('currentJournal');
    if (!$journal) {
        $journal = $this->get_template_vars('journal');
    }
    
    // Jika tidak tersedia dari template, coba cara lain
    if (!$journal) {
        $journalPath = Request::getRequestedJournalPath();
        if ($journalPath != '') {
            $journal = $journalDao->getJournalByPath($journalPath);
        }
    }
    
    if ($journal) {
        $journalId = $journal->getId();
        $journalTitle = $journal->getLocalizedTitle();
    }
    
    $this->assign('currentJournalTitle', $journalTitle);
    
    // Inisialisasi statistik
    $totalViews = 0;
    $totalDownloads = 0;
    $totalAuthors = 0;
    
    /**
     * Mendapatkan statistik jurnal - Versi yang dioptimasi
     * @param int $journalId ID jurnal
     * @return array statistik dengan kunci 'views', 'downloads', dan 'authors'
     */
    function getJournalStatsOptimized($journalId, $metricsTableExists, $articleDao) {
        $stats = array('views' => 0, 'downloads' => 0, 'authors' => 0);
        
        // Strategi 1: Gunakan tabel metrics (paling efisien)
        if ($metricsTableExists == "Ya") {
            try {
                // Query untuk artikel views
                $viewResult = $articleDao->retrieve(
                    "SELECT SUM(metric) AS total_views FROM metrics 
                     WHERE assoc_type = ? AND context_id = ?
                     AND (metric_type = 'ojs::counter::article' OR metric_type LIKE '%view%')",
                    array(ASSOC_TYPE_ARTICLE, $journalId)
                );
                
                if ($viewResult && !$viewResult->EOF) {
                    $stats['views'] = (int)$viewResult->fields['total_views'];
                }
                $viewResult->Close();
                
                // Query untuk galley downloads
                $downloadResult = $articleDao->retrieve(
                    "SELECT SUM(metric) AS total_downloads FROM metrics 
                     WHERE assoc_type = ? AND context_id = ?
                     AND (metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')",
                    array(ASSOC_TYPE_GALLEY, $journalId)
                );
                
                if ($downloadResult && !$downloadResult->EOF) {
                    $stats['downloads'] = (int)$downloadResult->fields['total_downloads'];
                }
                $downloadResult->Close();
                
                // Jika masih belum dapat hasil, coba query alternatif
                if ($stats['views'] == 0) {
                    $viewResult = $articleDao->retrieve(
                        "SELECT SUM(metric) AS total_views FROM metrics 
                         WHERE assoc_type = ? AND context_id = ?",
                        array(ASSOC_TYPE_ARTICLE, $journalId)
                    );
                    
                    if ($viewResult && !$viewResult->EOF) {
                        $stats['views'] = (int)$viewResult->fields['total_views'];
                    }
                    $viewResult->Close();
                }
                
                if ($stats['downloads'] == 0) {
                    $downloadResult = $articleDao->retrieve(
                        "SELECT SUM(metric) AS total_downloads FROM metrics 
                         WHERE assoc_type = ? AND context_id = ?",
                        array(ASSOC_TYPE_GALLEY, $journalId)
                    );
                    
                    if ($downloadResult && !$downloadResult->EOF) {
                        $stats['downloads'] = (int)$downloadResult->fields['total_downloads'];
                    }
                    $downloadResult->Close();
                }
                
                // Jika berhasil mendapatkan statistik dari metrics, return hasilnya
                if ($stats['views'] > 0 || $stats['downloads'] > 0) {
                    return $stats;
                }
            } catch (Exception $e) {
                error_log("OJS Stats Error from metrics: " . $e->getMessage());
            }
        }
        
        // Jika tidak berhasil mendapatkan dari metrics, gunakan metode original
        return null;
    }
    
    /**
     * Fungsi untuk mendapatkan jumlah penulis unik di jurnal tertentu
     * @param int $journalId
     * @return int jumlah penulis unik
     */
    function getJournalAuthorsCount($journalId, $issueDao, $publishedArticleDao) {
        $authorCount = 0;
        $uniqueAuthors = array();
        
        try {
            // Dapatkan semua issue yang diterbitkan untuk jurnal ini
            $issues = $issueDao->getPublishedIssues($journalId);
            
            // Loop melalui semua issue
            while ($issue = $issues->next()) {
                $issueArticles = $publishedArticleDao->getPublishedArticles($issue->getId());
                
                // Periksa jika hasilnya array atau iterator
                if (is_array($issueArticles)) {
                    foreach ($issueArticles as $article) {
                        // Ambil penulis dari artikel
                        $authors = $article->getAuthors();
                        if (is_array($authors)) {
                            foreach ($authors as $author) {
                                $authorId = $author->getId();
                                if ($authorId) {
                                    $uniqueAuthors[$authorId] = true;
                                }
                            }
                        }
                    }
                } else if ($issueArticles) {
                    // Jika hasilnya iterator (ResultSet)
                    while ($article = $issueArticles->next()) {
                        // Ambil penulis dari artikel
                        $authors = $article->getAuthors();
                        if (is_array($authors)) {
                            foreach ($authors as $author) {
                                $authorId = $author->getId();
                                if ($authorId) {
                                    $uniqueAuthors[$authorId] = true;
                                }
                            }
                        }
                    }
                }
            }
            
            $authorCount = count($uniqueAuthors);
            
        } catch (Exception $e) {
            error_log("OJS Author Count Error: " . $e->getMessage());
        }
        
        return $authorCount;
    }
    
    /**
     * Fungsi untuk mendapatkan statistik artikel dengan cara tradisional - sebagai fallback
     * @param int $journalId
     * @return array dengan kunci 'views', 'downloads', dan 'authors'
     */
    function getJournalStatsOriginal($journalId, $issueDao, $publishedArticleDao, $articleDao, $articleGalleyDao, $metricsTableExists, $metricsColumns, $articleStatsExists) {
        $stats = array('views' => 0, 'downloads' => 0, 'authors' => 0);
        
        // Kumpulkan semua artikel yang dipublikasikan di jurnal
        $publishedArticleIds = array();
        $galleyIds = array();
        $uniqueAuthors = array();
        
        // Dapatkan semua issue yang diterbitkan untuk jurnal ini
        $issues = $issueDao->getPublishedIssues($journalId);
        
        // Loop melalui semua issue
        while ($issue = $issues->next()) {
            $issueArticles = $publishedArticleDao->getPublishedArticles($issue->getId());
            // Periksa jika hasilnya array atau iterator
            if (is_array($issueArticles)) {
                foreach ($issueArticles as $article) {
                    $publishedArticleIds[] = $article->getId();
                    
                    // Kumpulkan author IDs unik
                    $authors = $article->getAuthors();
                    if (is_array($authors)) {
                        foreach ($authors as $author) {
                            $authorId = $author->getId();
                            if ($authorId) {
                                $uniqueAuthors[$authorId] = true;
                            }
                        }
                    }
                }
            } else if ($issueArticles) {
                // Jika hasilnya iterator (ResultSet)
                while ($article = $issueArticles->next()) {
                    $publishedArticleIds[] = $article->getId();
                    
                    // Kumpulkan author IDs unik
                    $authors = $article->getAuthors();
                    if (is_array($authors)) {
                        foreach ($authors as $author) {
                            $authorId = $author->getId();
                            if ($authorId) {
                                $uniqueAuthors[$authorId] = true;
                            }
                        }
                    }
                }
            }
        }
        
        // Hitung jumlah penulis unik
        $stats['authors'] = count($uniqueAuthors);
        
        if (empty($publishedArticleIds)) {
            return $stats; // Tidak ada artikel untuk jurnal ini
        }
        
        // Metode 1: Gunakan getViews() dari objek artikel dan galley
        foreach ($publishedArticleIds as $articleId) {
            $article = $articleDao->getArticle($articleId);
            if ($article) {
                // Tambahkan views artikel
                if (method_exists($article, 'getViews')) {
                    $stats['views'] += (int)$article->getViews();
                }
                
                // Kumpulkan galley dan tambahkan downloads
                $galleys = $articleGalleyDao->getGalleysByArticle($articleId);
                if (is_array($galleys)) {
                    foreach ($galleys as $galley) {
                        $galleyIds[] = $galley->getId();
                        if (method_exists($galley, 'getViews')) {
                            $stats['downloads'] += (int)$galley->getViews();
                        }
                    }
                } else if ($galleys) {
                    while ($galley = $galleys->next()) {
                        $galleyIds[] = $galley->getId();
                        if (method_exists($galley, 'getViews')) {
                            $stats['downloads'] += (int)$galley->getViews();
                        }
                    }
                }
            }
        }
        
        // Metode 2: Coba dengan tabel article_view_stats jika views masih 0
        if ($stats['views'] == 0 && $articleStatsExists == "Ya" && !empty($publishedArticleIds)) {
            $articleIdList = implode(',', array_map('intval', $publishedArticleIds));
            try {
                $result = $articleDao->retrieve(
                    "SELECT SUM(views) AS total_views FROM article_view_stats WHERE article_id IN ($articleIdList)"
                );
                if ($result && !$result->EOF) {
                    $stats['views'] = (int)$result->fields['total_views'];
                }
                $result->Close();
            } catch (Exception $e) {
                error_log("OJS Stats Error artikel_view_stats: " . $e->getMessage());
            }
        }
        
        // Metode 3: Coba dengan tabel metrics jika views masih 0
        if ($stats['views'] == 0 && $metricsTableExists == "Ya" && !empty($publishedArticleIds)) {
            // Cek jika kolom yang dibutuhkan ada di tabel metrics
            if (strpos($metricsColumns, "assoc_id") !== false && strpos($metricsColumns, "metric_type") !== false && strpos($metricsColumns, "metric") !== false) {
                $articleIdList = implode(',', array_map('intval', $publishedArticleIds));
                try {
                    // Query untuk artikel views dari metrics
                    $result = $articleDao->retrieve(
                        "SELECT SUM(metric) AS total_views FROM metrics 
                         WHERE assoc_type = ? AND assoc_id IN ($articleIdList)
                         AND metric_type = 'ojs::counter::article'",
                        array(ASSOC_TYPE_ARTICLE)
                    );
                    if ($result && !$result->EOF) {
                        $stats['views'] = (int)$result->fields['total_views'];
                    }
                    $result->Close();
                    
                    // Jika masih 0, coba variasi lain
                    if ($stats['views'] == 0) {
                        $result = $articleDao->retrieve(
                            "SELECT SUM(metric) AS total_views FROM metrics 
                             WHERE assoc_type = ? AND assoc_id IN ($articleIdList)",
                            array(ASSOC_TYPE_ARTICLE)
                        );
                        if ($result && !$result->EOF) {
                            $stats['views'] = (int)$result->fields['total_views'];
                        }
                        $result->Close();
                    }
                } catch (Exception $e) {
                    error_log("OJS Stats Error metrics articles: " . $e->getMessage());
                }
            }
        }
        
        // Metode 4: Coba dapatkan statistik downloads dari metrics jika download masih 0
        if ($stats['downloads'] == 0 && $metricsTableExists == "Ya" && !empty($galleyIds)) {
            if (strpos($metricsColumns, "assoc_id") !== false && strpos($metricsColumns, "metric_type") !== false && strpos($metricsColumns, "metric") !== false) {
                $galleyIdList = implode(',', array_map('intval', $galleyIds));
                try {
                    // Query untuk galley downloads dari metrics
                    $result = $articleDao->retrieve(
                        "SELECT SUM(metric) AS total_downloads FROM metrics 
                         WHERE assoc_type = ? AND assoc_id IN ($galleyIdList)
                         AND metric_type = 'ojs::counter::galley'",
                        array(ASSOC_TYPE_GALLEY)
                    );
                    if ($result && !$result->EOF) {
                        $stats['downloads'] = (int)$result->fields['total_downloads'];
                    }
                    $result->Close();
                    
                    // Jika masih 0, coba variasi lain
                    if ($stats['downloads'] == 0) {
                        $result = $articleDao->retrieve(
                            "SELECT SUM(metric) AS total_downloads FROM metrics 
                             WHERE assoc_type = ? AND assoc_id IN ($galleyIdList)",
                            array(ASSOC_TYPE_GALLEY)
                        );
                        if ($result && !$result->EOF) {
                            $stats['downloads'] = (int)$result->fields['total_downloads'];
                        }
                        $result->Close();
                    }
                } catch (Exception $e) {
                    error_log("OJS Stats Error metrics galleys: " . $e->getMessage());
                }
            }
        }
        
        return $stats;
    }
    
    if ($journalId) {
        try {
            // Coba gunakan metode yang dioptimasi terlebih dahulu
            $journalStats = getJournalStatsOptimized($journalId, $metricsTableExists, $articleDao);
            
            // Jika tidak berhasil, gunakan metode original
            if ($journalStats === null) {
                $journalStats = getJournalStatsOriginal($journalId, $issueDao, $publishedArticleDao, $articleDao, $articleGalleyDao, $metricsTableExists, $metricsColumns, $articleStatsExists);
            } else {
                // Jika berhasil dari optimized, tambahkan author count secara terpisah
                $journalStats['authors'] = getJournalAuthorsCount($journalId, $issueDao, $publishedArticleDao);
            }
            
            $totalViews = $journalStats['views'];
            $totalDownloads = $journalStats['downloads'];
            $totalAuthors = $journalStats['authors'];
        } catch (Exception $e) {
            error_log("OJS Jurnal Stats Error: " . $e->getMessage());
        }
    }
    
    // Assign hasil ke template
    $this->assign('journalTotalViews', $totalViews);
    $this->assign('journalTotalDownloads', $totalDownloads);
    $this->assign('journalTotalAuthors', $totalAuthors);
    $this->assign('metricsTableExists', $metricsTableExists);
    $this->assign('metricsColumns', $metricsColumns);
    $this->assign('articleStatsExists', $articleStatsExists);
    $this->assign('galleyStatsExists', $galleyStatsExists);
    $this->assign('authorsTableExists', $authorsTableExists);
    
    // Jika ingin menambahkan informasi diagnostik di output
    $this->assign('showDiagnostics', false); // Ubah menjadi true untuk debugging
    {/php}
    
    <h3 class="bold">Statistik Jurnal:</h3>
    
    <div class="stats-update">
        Terakhir diperbarui: {$lastUpdated|date_format:"%d %B %Y %H:%M:%S"|escape}
        <a href="?refresh_stats=true" style="margin-left: 10px; font-size: 0.9em;">[Refresh]</a>
    </div>
    
    {if $showDiagnostics}
    <div class="diagnostics" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
        <h4>Informasi Diagnostik:</h4>
        <ul>
            <li>Tabel 'metrics' ada: {$metricsTableExists}</li>
            <li>Kolom di 'metrics': {$metricsColumns}</li>
            <li>Tabel 'article_view_stats' ada: {$articleStatsExists}</li>
            <li>Tabel 'article_galley_view_stats' ada: {$galleyStatsExists}</li>
            <li>Tabel 'authors' ada: {$authorsTableExists}</li>
        </ul>
    </div>
    {/if}
</div>

{* 2. Untuk Seluruh Jurnal *}
<div class="all-journals-stats article">
    {php}
    // Variabel untuk menyimpan hasil
    $allTotalViews = 0;
    $allTotalDownloads = 0;
    $allTotalAuthors = 0;
    $journalsStats = array();
    
    try {
        // Dapatkan semua jurnal yang aktif
        $journals = $journalDao->getJournals(true);
        
        if ($journals) {
            while ($journal = $journals->next()) {
                $jId = $journal->getId();
                $jTitle = $journal->getLocalizedTitle();
                
                // Coba gunakan metode optimasi terlebih dahulu
                $journalStats = getJournalStatsOptimized($jId, $metricsTableExists, $articleDao);
                
                // Jika tidak berhasil, gunakan metode original
                if ($journalStats === null) {
                    $journalStats = getJournalStatsOriginal($jId, $issueDao, $publishedArticleDao, $articleDao, $articleGalleyDao, $metricsTableExists, $metricsColumns, $articleStatsExists);
                } else {
                    // Jika berhasil dari optimized, tambahkan author count secara terpisah
                    $journalStats['authors'] = getJournalAuthorsCount($jId, $issueDao, $publishedArticleDao);
                }
                
                $jViews = $journalStats['views'];
                $jDownloads = $journalStats['downloads'];
                $jAuthors = $journalStats['authors'];
                
                // Tambahkan ke total seluruh jurnal
                $allTotalViews += $jViews;
                $allTotalDownloads += $jDownloads;
                $allTotalAuthors += $jAuthors;
                
                // Simpan data jurnal
                $journalsStats[] = array(
                    'id' => $jId,
                    'title' => $jTitle,
                    'views' => $jViews,
                    'downloads' => $jDownloads,
                    'authors' => $jAuthors
                );
            }
        }
    } catch (Exception $e) {
        error_log("OJS All Journal Stats Error: " . $e->getMessage());
    }
    
    // Urutkan jurnal berdasarkan views tertinggi
    usort($journalsStats, function($a, $b) {
        return $b['views'] - $a['views'];
    });
    
    // Assign hasil ke template
    $this->assign('journalsStats', $journalsStats);
    $this->assign('allTotalViews', $allTotalViews);
    $this->assign('allTotalDownloads', $allTotalDownloads);
    $this->assign('allTotalAuthors', $allTotalAuthors);
    
    // Aktifkan mode debugging jika diperlukan
    $this->assign('showAllDiagnostics', false); // Ubah menjadi true untuk debugging
    {/php}
    
    <h3 class="bold">Statistik Seluruh Jurnal</h3>
    
    <div class="stats-container">
        <div class="stats-item">
            <span class="stats-label">Total Artikel View (Semua Jurnal):</span>
            <span class="stats-value">{$allTotalViews|number_format|default:"0"|escape}</span>
        </div>
        <div class="stats-item">
            <span class="stats-label">Total Artikel Download (Semua Jurnal):</span>
            <span class="stats-value">{$allTotalDownloads|number_format|default:"0"|escape}</span>
        </div>
        <div class="stats-item">
            <span class="stats-label">Total Penulis (Semua Jurnal):</span>
            <span class="stats-value">{$allTotalAuthors|number_format|default:"0"|escape}</span>
        </div>
    </div>
    
    {* Informasi Diagnostik *}
    {if $showAllDiagnostics}
    <div class="stats-diagnostics" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
        <h4>Informasi Diagnostik</h4>
        <ul>
            <li>Tabel metrics ada: {$metricsTableExists|escape}</li>
            <li>Kolom dalam tabel metrics: {$metricsColumns|escape}</li>
            <li>Tabel article_view_stats ada: {$articleStatsExists|escape}</li>
            <li>Tabel galley_view_stats ada: {$galleyStatsExists|escape}</li>
            <li>Tabel authors ada: {$authorsTableExists|escape}</li>
        </ul>
    </div>
    {/if}
    
    {* 3. Daftar Detail Semua Jurnal *}
    <div class="all-journals-detail">
        <h3 class="bold">Detail Statistik per Jurnal</h3>
        {if $journalsStats|@count > 0}
        <table class="data">
            <tr>
                <th style="width: 50%;">Jurnal</th>
                <th style="width: 16%;">Total Views</th>
                <th style="width: 17%;">Total Downloads</th>
                <th style="width: 17%;">Total Penulis</th>
            </tr>
            {foreach from=$journalsStats item=jstat}
            <tr>
                <td>{$jstat.title|escape}</td>
                <td>{$jstat.views|number_format}</td>
                <td>{$jstat.downloads|number_format}</td>
                <td>{$jstat.authors|number_format}</td>
            </tr>
            {/foreach}
        </table>
        {else}
        <p>Tidak ada data jurnal yang tersedia.</p>
        {/if}
    </div>
</div>

{* 4. Ringkasan Statistik Global *}
{php}
// Hitung rata-rata per jurnal
$avgViewsPerJournal = 0;
$avgDownloadsPerJournal = 0;
$avgAuthorsPerJournal = 0;
$journalCount = count($journalsStats);

if ($journalCount > 0) {
    $avgViewsPerJournal = round($allTotalViews / $journalCount);
    $avgDownloadsPerJournal = round($allTotalDownloads / $journalCount);
    $avgAuthorsPerJournal = round($allTotalAuthors / $journalCount);
}

$this->assign('avgViewsPerJournal', $avgViewsPerJoural);
$this->assign('avgDownloadsPerJournal', $avgDownloadsPerJournal);
$this->assign('avgAuthorsPerJournal', $avgAuthorsPerJournal);
$this->assign('journalCount', $journalCount);
{/php}

<div class="stats-summary" style="background-color: #f0f8ff; border: 1px solid #0066cc; border-radius: 6px; padding: 15px; margin: 20px 0;">
    <h4 style="margin-top: 0; color: #0066cc;">Ringkasan Statistik Platform</h4>
    <div class="stats-container">
        <div class="stats-item">
            <span class="stats-label">Jumlah Jurnal Aktif:</span>
            <span class="stats-value">{$journalCount|number_format|default:"0"|escape}</span>
        </div>
        <div class="stats-item">
            <span class="stats-label">Rata-rata Views per Jurnal:</span>
            <span class="stats-value">{$avgViewsPerJournal|number_format|default:"0"|escape}</span>
        </div>
        <div class="stats-item">
            <span class="stats-label">Rata-rata Downloads per Jurnal:</span>
            <span class="stats-value">{$avgDownloadsPerJournal|number_format|default:"0"|escape}</span>
        </div>
        <div class="stats-item">
            <span class="stats-label">Rata-rata Penulis per Jurnal:</span>
            <span class="stats-value">{$avgAuthorsPerJournal|number_format|default:"0"|escape}</span>
        </div>
    </div>
</div>