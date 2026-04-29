{**
 * @file template/future/trends_hub_standalone.tpl
 *
 * [WIZDAM] - Trends Hub Standalone Page
 * Halaman pusat untuk navigasi metrik: Popular, Download, dan Cited.
 *}
{strip}
    {assign var="pageTitleTranslated" value="Trends & Metrics"}
    {include file="common/header-index.tpl"}
{/strip}

<div class="wizdam-trends-hub-container page">
    
    {* Header Halaman Hub *}
    <header class="page-header">
        <h1>ScholarWizdam Trends & Metrics</h1>
        <p class="wizdam-hub-description">
            Selamat datang di pusat analitik ScholarWizdam. Temukan artikel-artikel dengan performa tertinggi, 
            dampak literatur terluas, dan tingkat keterbacaan paling masif di dalam ekosistem publikasi kami. 
            Silakan pilih kategori metrik di bawah ini untuk melihat daftar lengkapnya.
        </p>
    </header>

    {* Grid Layout untuk 3 Pilar Metrik *}
    <div class="wizdam-metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">

        {* KARTU 1: MOST POPULAR *}
        <div class="wizdam-metric-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 2rem; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div class="metric-icon" style="font-size: 3rem; margin-bottom: 1rem; color: #0056b3;">
                👁️ </div>
            <h2>Most Popular</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">
                Daftar 25 artikel yang paling sering dilihat dan dibaca oleh pengunjung. 
                Metrik ini mencerminkan tingginya minat pembaca terhadap topik yang dibahas.
            </p>
            <a href="{$hubPopularUrl}" class="wizdam-btn" style="display: inline-block; padding: 10px 20px; background-color: #0056b3; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Lihat Most Popular
            </a>
        </div>

        {* KARTU 2: MOST DOWNLOADED *}
        <div class="wizdam-metric-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 2rem; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div class="metric-icon" style="font-size: 3rem; margin-bottom: 1rem; color: #28a745;">
                📥 </div>
            <h2>Most Downloaded</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">
                Daftar artikel dengan jumlah unduhan berkas PDF/Galley tertinggi. 
                Metrik ini menunjukkan tingkat kebutuhan praktis dan referensial dari artikel.
            </p>
            <a href="{$hubDownloadUrl}" class="wizdam-btn" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Lihat Most Downloaded
            </a>
        </div>

        {* KARTU 3: MOST CITED *}
        <div class="wizdam-metric-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 2rem; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div class="metric-icon" style="font-size: 3rem; margin-bottom: 1rem; color: #dc3545;">
                Ц </div>
            <h2>Most Cited</h2>
            <p style="color: #666; margin-bottom: 1.5rem;">
                Daftar artikel yang paling banyak disitasi oleh penelitian lain. 
                Metrik ini adalah indikator utama dari dampak ilmiah (*Scientific Impact*) sebuah publikasi.
            </p>
            <a href="{$hubCitedUrl}" class="wizdam-btn" style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Lihat Most Cited
            </a>
        </div>

    </div>
    
</div>

{include file="common/footer.tpl"}