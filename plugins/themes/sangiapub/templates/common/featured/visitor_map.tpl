{**
 * Journal Visitor Country Statistics
 * @file getJournalVisitorCountry.php
 * @brief Function untuk mengambil data pengunjung jurnal berdasarkan negara untuk OJS 2.4.8.2
 * @author Rochmady and Wizdam Team
 * @version v1.1.0 - Smart Detection + Weekly Updates + Dynamic Cache
 *}
 
{* Visitor Country Map Data Proxy - Versi Lengkap *}
{php}
// Cari dan include file getJournalVisitorCountry.php menggunakan template directory
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($visitorFile = 'plugins/themes/' . $matches[1] . '/php/visitor_map/getJournalVisitorCountry.php')) {
        include_once($visitorFile);
        
        // Get visitor country data
        $visitorCountryData = getJournalVisitorCountry($this->_tpl_vars['currentJournal']->getId(), Request::getUserVar('refresh_visitor') == 'true');
        
        // Assign data to template variables dengan naming konsisten
        foreach ($visitorCountryData as $key => $value) {
            // Skip large arrays untuk efisiensi
            if (!in_array($key, array('countryData', 'yearlyCountryStats'))) {
                $this->assign('visitor' . ucfirst($key), $value);
            }
        }
        
        // Assign data khusus yang diperlukan
        $this->assign('visitorTopCountries', $visitorCountryData['topCountries']);
        $this->assign('visitorCacheInfo', $visitorCountryData['cache_info']);
        
        // Generate JSON path dinamis berdasarkan theme yang ditemukan
        $jsonPath = Request::getBasePath() . '/plugins/themes/' . $matches[1] . '/php/visitor_map/cache/journal_' . $this->_tpl_vars['currentJournal']->getId() . '_visitor_country.json';
        
        // Check apakah versi compressed (.gz) ada
        $this->assign('visitorJsonPath', file_exists('.' . $jsonPath . '.gz') ? $jsonPath . '.gz' : $jsonPath);
        $this->assign('visitorJsonCompressed', file_exists('.' . $jsonPath . '.gz'));
        
        // Status flags
        $this->assign('visitorDataLoaded', true);
        $this->assign('visitorCacheHit', isset($visitorCountryData['cache_info']['cache_hit']) ? $visitorCountryData['cache_info']['cache_hit'] : false);
        
        break;
    }
}

// Fallback jika file tidak ditemukan
if (!isset($visitorCountryData)) {
    $this->assign('visitorDataLoaded', false);
    $this->assign('visitorError', 'File statistik pengunjung tidak ditemukan');
}
{/php}

{* Hidden container untuk data JSON visitor map *}
<div id="visitorCountryMap" 
     data-json-path="{$visitorJsonPath|escape}" 
     data-compressed="{if $visitorJsonCompressed}true{else}false{/if}"
     data-cache-hit="{if $visitorCacheHit}true{else}false{/if}"
     class="visitor-map-container u-mb-48">
    
    {if $visitorDataLoaded}
        <div class="loading-visitor-stats">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Memuat peta pengunjung...</span>
            </div>
            <p class="mt-2">Menyiapkan data peta pengunjung dari {$visitorTotalCountries} negara...</p>
        </div>
        
        {* Summary info while loading *}
        <div class="visitor-summary-preview" style="display: none;">
            <h3>{$visitorJournalTitle|escape}</h3>
            <p>Total Pengunjung: <strong>{$visitorTotalUniqueVisitors|number_format:0:',':'.'}</strong></p>
            <p>Dari <strong>{$visitorTotalCountries}</strong> negara</p>
            {if $visitorCacheHit}
                <small class="text-muted">Data dari cache</small>
            {else}
                <small class="text-success">Data terbaru</small>
            {/if}
        </div>
        
        {* Container for the actual map *}
        <div id="highchartsVisitorMap" style="display: none; min-height: 500px;"></div>
        
        {* Top countries preview table *}
        {if $visitorTopCountries && count($visitorTopCountries) > 0}
        <div class="visitor-top-countries-preview" style="display: none;">
            <h4>Top 5 Negara Pengunjung</h4>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Negara</th>
                        <th>Pengunjung</th>
                        <th>Total Akses</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$visitorTopCountries item=country name=topCountries}
                        {if $smarty.foreach.topCountries.index < 5}
                        <tr>
                            <td>{$country.country_code|upper}</td>
                            <td>{$country.unique_visitors|number_format:0:',':'.'}</td>
                            <td>{$country.total_metrics|number_format:0:',':'.'}</td>
                        </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        </div>
        {/if}
        
    {else}
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            {if $visitorError}
                {$visitorError|escape}
            {else}
                Data statistik pengunjung tidak tersedia
            {/if}
        </div>
    {/if}
</div>

{* JavaScript untuk load dan render map *}
{if $visitorDataLoaded}
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Configuration
    const mapContainer = document.getElementById('visitorCountryMap');
    const jsonPath = mapContainer.getAttribute('data-json-path');
    const isCompressed = mapContainer.getAttribute('data-compressed') === 'true';
    
    // Load visitor country data
    function loadVisitorData() {
        fetch(jsonPath)
            .then(response => {
                if (!response.ok) throw new Error('Failed to load visitor data');
                return isCompressed ? response.arrayBuffer() : response.json();
            })
            .then(data => {
                if (isCompressed) {
                    // Decompress gzip data
                    return decompressGzip(data);
                }
                return data;
            })
            .then(jsonData => {
                // Hide loading, show content
                document.querySelector('.loading-visitor-stats').style.display = 'none';
                document.querySelector('.visitor-summary-preview').style.display = 'block';
                document.getElementById('highchartsVisitorMap').style.display = 'block';
                
                // Render the map
                renderVisitorMap(jsonData);
                
                // Show top countries if available
                const topCountriesEl = document.querySelector('.visitor-top-countries-preview');
                if (topCountriesEl) {
                    topCountriesEl.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading visitor data:', error);
                document.querySelector('.loading-visitor-stats').innerHTML = 
                    '<div class="alert alert-danger">Error loading visitor map data: ' + error.message + '</div>';
            });
    }
    
    // Decompress gzip data (requires pako library)
    function decompressGzip(arrayBuffer) {
        // Load pako if not already loaded
        if (typeof pako === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pako/2.0.4/pako.min.js';
            script.onload = function() {
                const decompressed = pako.ungzip(new Uint8Array(arrayBuffer), { to: 'string' });
                return JSON.parse(decompressed);
            };
            document.head.appendChild(script);
        } else {
            const decompressed = pako.ungzip(new Uint8Array(arrayBuffer), { to: 'string' });
            return JSON.parse(decompressed);
        }
    }
    
    // Render map using Highcharts
    function renderVisitorMap(data) {
        // Load Highcharts Maps if not already loaded
        if (typeof Highcharts === 'undefined' || !Highcharts.maps) {
            loadHighchartsMaps(function() {
                createMap(data);
            });
        } else {
            createMap(data);
        }
    }
    
    // Load Highcharts Maps dependencies
    function loadHighchartsMaps(callback) {
        const scripts = [
            'https://code.highcharts.com/maps/highmaps.js',
            'https://code.highcharts.com/maps/modules/exporting.js',
            'https://code.highcharts.com/mapdata/custom/world.js'
        ];
        
        let loaded = 0;
        scripts.forEach(src => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = function() {
                loaded++;
                if (loaded === scripts.length) {
                    callback();
                }
            };
            document.head.appendChild(script);
        });
    }
    
    // Create the actual map
    function createMap(data) {
        // Prepare data for Highcharts
        const mapData = data.topCountries.map(country => ({
            code: country.country_code.toLowerCase(),
            value: country.total_metrics,
            visitors: country.unique_visitors,
            name: country.country_code
        }));
        
        // Create the map
        Highcharts.mapChart('highchartsVisitorMap', {
            chart: {
                map: 'custom/world'
            },
            
            title: {
                text: 'Peta Pengunjung Jurnal'
            },
            
            subtitle: {
                text: 'Berdasarkan data akses dari ' + data.totalCountries + ' negara'
            },
            
            mapNavigation: {
                enabled: true,
                buttonOptions: {
                    verticalAlign: 'bottom'
                }
            },
            
            colorAxis: {
                min: 0,
                type: 'logarithmic',
                minColor: '#E6F7FF',
                maxColor: '#006BB3',
                stops: [
                    [0, '#E6F7FF'],
                    [0.5, '#66B2FF'],
                    [1, '#006BB3']
                ]
            },
            
            series: [{
                data: mapData,
                name: 'Total Akses',
                states: {
                    hover: {
                        color: '#FF6B6B'
                    }
                },
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                },
                tooltip: {
                    pointFormatter: function() {
                        return '<b>' + this.name + '</b><br/>' +
                               'Pengunjung Unik: <b>' + (this.visitors || 0).toLocaleString('id-ID') + '</b><br/>' +
                               'Total Akses: <b>' + (this.value || 0).toLocaleString('id-ID') + '</b>';
                    }
                }
            }],
            
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            
            credits: {
                enabled: false
            }
        });
    }
    
    // Initialize
    setTimeout(loadVisitorData, 100);
});
</script>

{* Refresh button *}
<div class="text-center mt-3">
    <a href="?refresh_visitor_stats=true" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-sync-alt"></i> Refresh Data Pengunjung
    </a>
    {if $visitorCacheHit}
        <small class="text-muted d-block mt-1">
            Cache terakhir: {$visitorCalculationDate|date_format:"%d %B %Y %H:%M"}
        </small>
    {/if}
</div>
{/if}