{**
 * @file template/trends/siteStats_standalone.tpl
 * [WIZDAM] - Tampilan Statistik Level Site dengan Smarty Modifiers Custom
 *}
<div class="wizdam-futuristic-dashboard">
    <div class="dashboard-header">
        <h2>Platform Intelligence</h2>
        <p class="last-updated"><small>Data realtime per: {$lastUpdated}</small></p>
    </div>

    <div class="stats-grid">
        <div class="stats-card">
            <h3>Total Article Views</h3>
            <div class="metric-display">
                <span class="metric-number">{$allTotalViews|smartyMetricNumber}</span>
                <span class="metric-suffix">{$allTotalViews|smartyMetricSuffix}</span>
            </div>
            <span class="raw-data" title="Exact views: {$allTotalViews|number_format}">
                <i class="icon-eye"></i> {$allTotalViews|number_format}
            </span>
        </div>

        <div class="stats-card">
            <h3>Total PDF Downloads</h3>
            <div class="metric-display">
                <span class="metric-number">{$allTotalDownloads|smartyMetricNumber}</span>
                <span class="metric-suffix">{$allTotalDownloads|smartyMetricSuffix}</span>
            </div>
            <span class="raw-data" title="Exact downloads: {$allTotalDownloads|number_format}">
                <i class="icon-download"></i> {$allTotalDownloads|number_format}
            </span>
        </div>

        <div class="stats-card">
            <h3>Registered Authors</h3>
            <div class="metric-display">
                <span class="metric-number">{$allTotalAuthors|smartyMetricNumber}</span>
                <span class="metric-suffix">{$allTotalAuthors|smartyMetricSuffix}</span>
            </div>
        </div>
    </div>

    <div class="journal-leaderboard-section">
        <h3>Top Performing Journals</h3>
        <ul class="journal-leaderboard-list">
            {foreach from=$journalsStats item=jStat}
                <li class="journal-item">
                    <div class="journal-info">
                        <a href="{url journal=$jStat.path page="index"}">{$jStat.title}</a>
                    </div>
                    <div class="journal-metrics">
                        <span class="metric-badge views">
                            <i class="icon-eye"></i> {$jStat.views|smartyMetricNumber} {$jStat.views|smartyMetricSuffix}
                        </span>
                        <span class="metric-badge downloads">
                            <i class="icon-download"></i> {$jStat.downloads|smartyMetricNumber} {$jStat.downloads|smartyMetricSuffix}
                        </span>
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
</div>