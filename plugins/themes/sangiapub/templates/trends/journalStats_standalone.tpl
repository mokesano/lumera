{**
 * @file template/trends/journalStats_standalone.tpl
 * [WIZDAM] - Tampilan Statistik Level Jurnal
 *}
<div class="wizdam-journal-dashboard">
    <h2>{$currentJournalTitle} Analytics</h2>
    <p class="last-updated"><small>Last updated: {$lastUpdated}</small></p>

    <div class="stats-grid">
        <div class="stats-card highlight">
            <h3>Total Views</h3>
            <div class="metric-display">
                <span class="metric-number">{$journalTotalViews|smartyMetricNumber}</span>
                <span class="metric-suffix">{$journalTotalViews|smartyMetricSuffix}</span>
            </div>
        </div>

        <div class="stats-card highlight">
            <h3>Total Downloads</h3>
            <div class="metric-display">
                <span class="metric-number">{$journalTotalDownloads|smartyMetricNumber}</span>
                <span class="metric-suffix">{$journalTotalDownloads|smartyMetricSuffix}</span>
            </div>
        </div>
        
        <div class="stats-card editorial">
            <h3>Acceptance Rate</h3>
            <div class="metric-display">
                <span class="metric-number">{$acceptRate}</span>
                <span class="metric-suffix">%</span>
            </div>
        </div>

        <div class="stats-card editorial">
            <h3>Median Review Time</h3>
            <div class="metric-display">
                <span class="metric-number">{$daysPerReview}</span>
                <span class="metric-suffix">Days</span>
            </div>
        </div>
        
        <div class="stats-card editorial">
            <h3>Time to Publication</h3>
            <div class="metric-display">
                <span class="metric-number">{$daysToPublication}</span>
                <span class="metric-suffix">Days</span>
            </div>
        </div>
    </div>
</div>