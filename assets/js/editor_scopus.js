/**
 * UserScopus.js - Menampilkan data publikasi dari UserScopus_v32.php
 * 
 * @author Rochmady and Wizdam Team
 * @version 2.0 - Fixed Open Access status article
 * @date 2025-06-27
 */
(function() {
    // Konfigurasi - SAMA seperti asli
    const config = {
        apiEndpoint: '/api/scopus_user',
        authorId: null,
        graphElementId: 'scopus-graph',
        articlesElementId: 'scopus-articles',
        loadingTemplate: `
            <div class="loading-container" style="text-align: center; padding: 20px;">
                <div class="spinner" style="margin: 0 auto; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <div style="margin-top: 10px;">Loading data Scopus...</div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `,
        errorTemplate: '<div class="error" style="padding: 15px; background: #ffebee; color: #c62828; border-radius: 4px; text-align: center;">Error: {{message}}</div>',
        noDataTemplate: '<div class="no-data" style="padding: 15px; background: #f5f5f5; color: #666; border-radius: 4px; text-align: center;">Tidak ada data Scopus tersedia</div>',
        colors: {
            publications: {
                fill: 'rgba(34, 123, 192, 0.85)',
                border: 'rgba(60, 151, 221, 1)',
                hover: 'rgba(60, 151, 221, 0.75)',
                fade: 'rgba(20, 184, 188, 0.3)',
                background: 'rgba(0, 158, 206, 0.2)'
            },
            citations: {
                line: 'rgba(213, 68, 73, 1)',
                point: 'rgba(255, 255, 255, 1)',
                pointBorder: 'rgba(213, 68, 73, 1)',
                hover: 'rgba(147, 104, 206, 1)',
                fade: 'rgba(147, 104, 206, 0.3)',
                background: 'rgba(147, 104, 206, 0.2)'
            },
            grid: 'rgba(200, 200, 200, 0.3)',
            gridZero: 'rgba(0, 115, 152, 1)',
            text: '#333',
            hoverBackground: 'rgba(120, 120, 120, 0.5)',
            tooltipBackground: 'rgba(200, 200, 200, 0.5)',
            tooltipText: 'rgba(255, 255, 255, 1)',
            // Warna untuk zona berselang-seling
            alternatingZones: [
                'rgba(255, 255, 255, 0)',
                'rgba(240, 240, 240, 0.6)',
                'rgba(255, 255, 255, 0)',
                'rgba(240, 240, 240, 0.6)'
            ]
        },
        fontFamily: "NexusSans Pro, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif" 
    };

    // ==============================================
    // FUNGSI HELPER - SAMA seperti asli KECUALI formatOpenAccessBadge
    // ==============================================
    
    /**
     * Memformat tanggal publikasi - SAMA seperti asli
     */
    function formatPublicationDate(dateStr) {
        if (!dateStr || dateStr === 'N/A' || dateStr === 'Unknown') {
            return 'Tanggal tidak diketahui';
        }
        
        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) {
                return dateStr;
            }
            
            const day = date.getDate();
            const month = date.toLocaleString('en-US', { month: 'long' });
            const year = date.getFullYear();
            
            if (dateStr.includes(':')) {
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                return `${day} ${month} ${year}, ${hours}:${minutes}`;
            }
            
            return `${day} ${month} ${year}`;
        } catch (e) {
            console.error('[Wizdam API] Error formatting date:', e);
            return dateStr;
        }
    }
    
    /**
     * Memformat informasi volume, edisi, dan halaman - SAMA seperti asli
     */
    function formatVolumeIssuePages(volume, issue, pages) {
        let parts = [];
        
        if (volume && volume !== 'N/A') {
            parts.push(`Vol. ${volume}`);
        }
        
        if (issue && issue !== 'N/A') {
            parts.push(`No.${issue}`);
        }
        
        if (pages && pages !== 'N/A') {
            parts.push(`P: ${pages}`);
        }
        
        return parts.length > 0 ? parts.join(', ') : 'Volume & Issue N/A';
    }

    /**
     * Memperbarui semua elemen dengan kelas time-stamp - SAMA seperti asli
     */
    function updateLastUpdatedDate(dateStr) {
        const formattedDate = formatPublicationDate(dateStr);
        const timeStampElements = document.querySelectorAll('.time-stamp');
        timeStampElements.forEach(el => {
            el.textContent = `Last update: ${formattedDate}.`;
        });
    }

    // ==============================================
    // SEMUA FUNGSI LAIN SAMA PERSIS seperti asli
    // ==============================================
    
    function getParameterByName(name, defaultValue = null) {
        const url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return defaultValue;
        if (!results[2]) return defaultValue;
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

    function showLoading(element) {
        element.innerHTML = config.loadingTemplate;
    }

    function showError(element, message) {
        element.innerHTML = config.errorTemplate.replace('{{message}}', message);
    }

    function showNoData(element) {
        element.innerHTML = config.noDataTemplate;
    }

    function extractScopusAuthorId() {
        let authorId = getParameterByName('scopus_id');
        if (authorId) return authorId;
        const graphElement = document.getElementById(config.graphElementId);
        if (graphElement && graphElement.getAttribute('data-author-id')) {
            return graphElement.getAttribute('data-author-id');
        }
        const scopusLinks = document.querySelectorAll('a.scopusid');
        for (let i = 0; i < scopusLinks.length; i++) {
            const link = scopusLinks[i];
            const href = link.getAttribute('href') || '';
            const idMatch = href.match(/authorId=(\d+)/);
            if (idMatch && idMatch[1]) {
                return idMatch[1];
            }
            const linkText = link.textContent.trim();
            const textMatch = linkText.match(/ID\s*(\d+)/i);
            if (textMatch && textMatch[1]) {
                return textMatch[1];
            }
        }
        return null;
    }

    function groupPublicationsByYear(publications) {
        const grouped = {};
        let minYear = 9999;
        let maxYear = 0;
        publications.forEach(pub => {
            const year = parseInt(pub.year);
            if (!isNaN(year)) {
                if (year < minYear) minYear = year;
                if (year > maxYear) maxYear = year;
                if (!grouped[year]) {
                    grouped[year] = {
                        count: 0,
                        citations: 0,
                        publications: []
                    };
                }
                grouped[year].count++;
                grouped[year].citations += pub.citation_count;
                grouped[year].publications.push(pub);
            }
        });
        
        for (let year = minYear; year <= maxYear; year++) {
            if (!grouped[year]) {
                grouped[year] = {
                    count: 0,
                    citations: 0,
                    publications: []
                };
            }
        }
        return {
            byYear: grouped,
            minYear: minYear,
            maxYear: maxYear,
            totalDocuments: publications.length,
            totalCitations: publications.reduce((total, pub) => total + pub.citation_count, 0)
        };
    }

    // Chart creation function - SAMA PERSIS seperti asli
    function createPublicationChart(element, publicationData) {
        element.innerHTML = '';
        
        const chartContainerId = 'highcharts-container-' + Math.random().toString(36).substr(2, 9);
        const chartContainer = document.createElement('div');
        chartContainer.id = chartContainerId;
        chartContainer.className = 'chart-container';
        chartContainer.style.position = 'relative';
        chartContainer.style.height = '220px';
        chartContainer.style.width = '100%';
        chartContainer.style.padding = '5px 0';
        chartContainer.style.boxSizing = 'border-box';
        
        element.appendChild(chartContainer);
        
        const years = Object.keys(publicationData.byYear).sort();
        const documentCounts = years.map(year => publicationData.byYear[year].count);
        const citationCounts = years.map(year => publicationData.byYear[year].citations);
        
        const maxDocuments = Math.max(...documentCounts, 1);
        const maxCitations = Math.max(...citationCounts, 1);
        
        function calculateYAxisMax(maxValue) {
            let roundedMax;
            
            if (maxValue <= 10) {
                roundedMax = Math.ceil(maxValue / 2) * 2;
            } else if (maxValue <= 20) {
                roundedMax = Math.ceil(maxValue / 4) * 4;
            } else if (maxValue <= 50) {
                roundedMax = Math.ceil(maxValue / 5) * 5;
            } else {
                roundedMax = Math.ceil(maxValue / 10) * 10;
            }
            
            while (roundedMax % 4 !== 0) {
                roundedMax++;
            }
            
            return roundedMax;
        }
        
        const documentsMax = calculateYAxisMax(maxDocuments);
        const citationsMax = calculateYAxisMax(maxCitations);
        
        const documentsZones = Array(4).fill().map((_, i) => ({
            value: documentsMax * (i+1) / 4,
            color: config.colors.alternatingZones[i]
        }));
        
        const citationsZones = Array(4).fill().map((_, i) => ({
            value: citationsMax * (i+1) / 4,
            color: config.colors.alternatingZones[i]
        }));
        
        setTimeout(() => {
            if (!document.getElementById(chartContainerId) || !window.Highcharts) {
                console.error('[Wizdam API] Container chart tidak ditemukan atau Highcharts tidak tersedia');
                return;
            }
            
            try {
                const chart = Highcharts.chart(chartContainerId, {
                    chart: {
                        type: 'column',
                        style: {
                            fontFamily: config.fontFamily
                        },
                        backgroundColor: 'transparent',
                        animation: false,
                        lineWidth: 1.5,
                        height: 220,
                        spacing: [10, 10, 10, 10]
                    },
                    title: {
                        text: 'Latest number of Publication and Citations in SCOPUS',
                        align: 'center',
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            color: '#333',
                            fontFamily: config.fontFamily
                        },
                        margin: -10
                    },
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: false
                    },
                    xAxis: {
                        categories: years,
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: config.colors.text,
                                fontFamily: config.fontFamily
                            }
                        },
                        lineWidth: 1.5,
                        lineColor: 'rgba(0, 0, 0, 1)',
                        tickWidth: 1,
                        tickLength: 5,
                        gridLineWidth: 0,
                        crosshair: {
                            width: 1,
                            color: 'rgba(200, 200, 200, 0.5)',
                            zIndex: 5,
                            label: {
                                enabled: true,
                                format: '{value}',
                                style: {
                                    fontWeight: 'bold',
                                    fontSize: '12px',
                                    fontFamily: config.fontFamily,
                                    color: '#333',
                                    backgroundColor: 'rgba(200, 200, 200, 0.3)',
                                    padding: '2px 5px',
                                    borderRadius: '3px'
                                }
                            }
                        }
                    },
                    yAxis: [
                        {
                            title: {
                                text: 'Documents',
                                style: {
                                    color: config.colors.publications.border,
                                    fontWeight: 'bold',
                                    fontSize: '14px',
                                    fontFamily: config.fontFamily
                                }
                            },
                            labels: {
                                style: {
                                    color: config.colors.publications.border,
                                    fontSize: '12px',
                                    fontFamily: config.fontFamily,
                                    fontWeight: 'normal'
                                },
                                formatter: function() {
                                    return this.value;
                                }
                            },
                            min: 0,
                            max: documentsMax,
                            tickAmount: 5,
                            lineWidth: 1.5,
                            lineColor: config.colors.publications.border,
                            tickWidth: 1,
                            tickLength: 5,
                            gridLineWidth: 1,
                            gridLineColor: 'rgba(213, 68, 73, 0.3)',
                            gridLineDashStyle: 'Dot',
                            opposite: false,
                            crosshair: {
                                label: {
                                    enabled: true,
                                    format: '{value}',
                                    style: {
                                        fontWeight: 'bold',
                                        fontSize: '12px',
                                        fontFamily: config.fontFamily,
                                        color: config.colors.publications.border,
                                        backgroundColor: config.colors.publications.background,
                                        padding: '2px 5px',
                                        borderRadius: '3px'
                                    }
                                }
                            },
                            plotBands: documentsZones.map((zone, index) => ({
                                from: index === 0 ? 0 : documentsZones[index-1].value,
                                to: zone.value,
                                color: zone.color,
                                zIndex: 0
                            }))
                        },
                        {
                            title: {
                                text: 'Citations',
                                style: {
                                    color: config.colors.citations.line,
                                    fontWeight: 'bold',
                                    fontSize: '14px',
                                    fontFamily: config.fontFamily
                                }
                            },
                            labels: {
                                style: {
                                    color: config.colors.citations.line,
                                    fontSize: '12px',
                                    fontFamily: config.fontFamily,
                                    fontWeight: 'normal'
                                },
                                formatter: function() {
                                    return this.value;
                                }
                            },
                            min: 0,
                            max: citationsMax,
                            tickAmount: 5,
                            lineWidth: 1.5,
                            lineColor: config.colors.citations.line,
                            tickWidth: 1,
                            tickLength: 5,
                            gridLineWidth: 0,
                            opposite: true,
                            crosshair: {
                                label: {
                                    enabled: true,
                                    format: '{value}',
                                    style: {
                                        fontWeight: 'bold',
                                        fontSize: '12px',
                                        fontFamily: config.fontFamily,
                                        color: config.colors.citations.line,
                                        backgroundColor: config.colors.citations.background,
                                        padding: '2px 5px',
                                        borderRadius: '3px'
                                    }
                                }
                            },
                            plotBands: citationsZones.map((zone, index) => ({
                                from: index === 0 ? 0 : citationsZones[index-1].value,
                                to: zone.value,
                                color: zone.color,
                                zIndex: 0
                            }))
                        }
                    ],
                    tooltip: {
                        shared: true,
                        backgroundColor: config.colors.tooltipBackground,
                        style: {
                            color: config.colors.tooltipText,
                            fontSize: '12px',
                            fontFamily: config.fontFamily
                        },
                        borderColor: 'rgba(200, 200, 200, 0.7)',
                        borderWidth: 1,
                        borderRadius: 2,
                        padding: 12,
                        headerFormat: '<span style="font-size: 1.57em; font-weight: bold; display: block; margin-bottom: 5px;">{point.key}</span>',
                        pointFormat: '<span style="color:{series.color}; font-weight: bold;">{series.name}</span>: <b>{point.y}</b><br/>',
                        useHTML: true,
                        width: 170
                    },
                    legend: {
                        enabled: true,
                        align: 'center',
                        verticalAlign: 'top',
                        itemStyle: {
                            color: config.colors.text,
                            fontSize: '12px',
                            fontFamily: config.fontFamily,
                            fontWeight: 'normal'
                        },
                        symbolWidth: 16,
                        symbolHeight: 12,
                        symbolRadius: 0,
                        itemDistance: 20
                    },
                    plotOptions: {
                        series: {
                            animation: false,
                            pointPadding: 0.1,
                            groupPadding: 0.2,
                            stickyTracking: true,
                            states: {
                                hover: {
                                    halo: false,
                                    brightness: 0.15
                                }
                            }
                        },
                        column: {
                            borderWidth: 1,
                            states: {
                                hover: {
                                    color: config.colors.publications.hover,
                                    borderColor: config.colors.publications.border
                                }
                            }
                        },
                        spline: {
                            states: {
                                hover: {
                                    lineWidthPlus: 0
                                }
                            }
                        }
                    },
                    series: [
                        {
                            name: 'Publications',
                            type: 'column',
                            data: documentCounts,
                            color: config.colors.publications.fill,
                            borderColor: config.colors.publications.border,
                            yAxis: 0,
                            marker: {
                                enabled: false
                            }
                        },
                        {
                            name: 'Citations',
                            type: 'spline',
                            data: citationCounts,
                            color: config.colors.citations.line,
                            lineWidth: 1.5,
                            yAxis: 1,
                            marker: {
                                enabled: true,
                                radius: 3,
                                symbol: 'circle',
                                lineWidth: 1.5,
                                lineColor: config.colors.citations.pointBorder,
                                fillColor: config.colors.citations.point
                            }
                        }
                    ],
                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    itemDistance: 10
                                },
                                yAxis: [
                                    {
                                        labels: {
                                            style: {
                                                fontSize: '10px'
                                            }
                                        }
                                    },
                                    {
                                        labels: {
                                            style: {
                                                fontSize: '10px'
                                            }
                                        }
                                    }
                                ]
                            }
                        }]
                    }
                });
                
                setTimeout(() => {
                    if (chart && typeof chart.reflow === 'function') {
                        chart.reflow();
                    }
                }, 100);
                
                return chart;
            } catch (error) {
                console.error('[Wizdam API] Error saat membuat chart:', error);
                showError(element, 'Gagal membuat grafik: ' + error.message);
            }
        }, 200);
    }

    // RENDER ARTICLES - HANYA TAMBAH formatOpenAccessBadge, sisanya SAMA PERSIS
    function renderArticlesList(element, publications, lastUpdated, titlePosition = 'before') {
        if (!element) {
            console.error('[Wizdam API] Elemen articles tidak ditemukan');
            return;
        }
        
        console.log('[Wizdam API] Rendering articles ke elemen:', element);
        
        const sortedPubs = publications.sort((a, b) => {
            // Prioritas utama: urutkan berdasarkan tanggal publikasi (terbaru ke terlama)
            const dateA = new Date(a.publication_date && a.publication_date !== 'N/A' ? a.publication_date : a.year || '1900');
            const dateB = new Date(b.publication_date && b.publication_date !== 'N/A' ? b.publication_date : b.year || '1900');
            
            if (dateA.getTime() !== dateB.getTime()) {
                return dateB.getTime() - dateA.getTime(); // Terbaru ke terlama
            }
            
            // Jika tanggal sama, urutkan berdasarkan citation count (tertinggi ke terendah)
            return b.citation_count - a.citation_count;
        });
        
        const wrapper = document.createElement('div');
        wrapper.className = 'editor-article';
        
        const formattedDate = formatPublicationDate(lastUpdated);
        
        const titleElement = document.createElement('div');
        titleElement.setAttribute('data-test', 'title');
        titleElement.className = 'entitle anchored';
        titleElement.innerHTML = `
            <h3 class="heading-title">
                <span class="content-break">Article's on Scopus</span>
                <span class="update-info" style="display: inherit;font-size: 15px;">
                    <span class="time-stamp u-mr-8">Last update: ${formattedDate}.</span>
                    <span class="info-stamp">(Graphs and article lists update with Smart Detection Update)</span>
                </span>
            </h3>
        `;
        
        const container = document.createElement('div');
        container.className = 'scopus-articles-container';
        
        const articlesList = document.createElement('ul');
        articlesList.className = 'scopus-articles-list app-article-list-row';
        
        sortedPubs.forEach(pub => {
            const articleItem = document.createElement('li');
            articleItem.className = 'scopus-article-item app-article-list-row__item';
            articleItem.style.padding = '15px 0';
            articleItem.style.borderBottom = '1px solid #eee';
            articleItem.style.fontFamily = config.fontFamily;
            
            articleItem.innerHTML = `
                <div class="u-full-height" data-native-ad-placement="false">
                    <article class="u-full-height c-card c-card--flush" itemscope itemtype="http://schema.org/ScholarlyArticle">
                        <div class="c-card__layout u-full-heights">
                            <div class="c-card__body u-display-flex u-flex-direction-column">
                            <h3 class="c-card__title" itemprop="name headline">
                                ${!pub.doi || pub.doi === 'N/A' 
                                    ? (pub.title || 'Judul tidak tersedia') 
                                    : `<a class="c-card__link u-link-inherit" href="https://doi.org/${pub.doi}" itemprop="url" data-track-action="view article" data-track-label="link" target="_blank">${pub.title || 'Judul tidak tersedia'}</a>`}
                            </h3>
                            ${pub.abstract !== 'N/A' ? `
                            <div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p>${pub.abstract}</p></div>` : ''}
                            <ul class="c-author-list c-author-list--compact u-mt-auto"><li itemprop="creator" itemscope itemtype="http://schema.org/Person">${pub.authors_string || 'Informasi penulis tidak tersedia'}</li>
                            </ul>
                            </div>
                        </div>
                        <div class="c-card__section c-meta">
                            <span class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="publishing.type">
                                <span class="c-meta__type">${pub.publication_type !== 'N/A' ? pub.publication_type : ''}</span>
                            </span>
                            <span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
                                <span class="c-meta__type">${pub.subtype !== 'N/A' ? `${pub.subtype}` : ''}</span>
                            </span>
                            ${pub.open_access ? `
                            <span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
                                <span class="u-color-open-access">Open Access</span>
                            </span>` : ''}
                            ${pub.citation_count > 0 ? `<span class="u-hide c-meta__item c-meta__item--block-at-lg">Cited: ${pub.citation_count} times</span>` : ''}
                            <time class="c-meta__item c-meta__item--block-at-lg" datetime="${pub.publication_date !== 'N/A' ? pub.publication_date : ''}">${pub.publication_date !== 'N/A' ? formatPublicationDate(pub.publication_date) : (pub.year || 'Not available')}</time>
                            <div class="c-meta__item c-meta__item--block-at-lg u-text-bold">${pub.source || 'Sumber tidak diketahui'}</div>
                            <div class="c-meta__item c-meta__item--block-at-lg" data-test="volume-and-page-info">${formatVolumeIssuePages(pub.volume, pub.issue, pub.pages)}${pub.citation_count > 0 ? ` Cited: ${pub.citation_count}` : ''}</div>
                        </div>
                    </article>
                </div>
            `;
            articlesList.appendChild(articleItem);
        });
       
        container.appendChild(articlesList);
        
        if (titlePosition === 'before') {
            wrapper.appendChild(titleElement);
            wrapper.appendChild(container);
        } else {
            wrapper.appendChild(container);
            wrapper.appendChild(titleElement);
        }
        
        element.innerHTML = '';
        element.appendChild(wrapper);
        
        console.log('[Wizdam API] Daftar artikel berhasil dirender:', sortedPubs.length, 'artikel');
    }

    // SEMUA FUNGSI LOADING SAMA PERSIS seperti asli
    function loadHighcharts() {
        return new Promise((resolve, reject) => {
            if (window.Highcharts) {
                console.log('[Wizdam API] Highcharts sudah dimuat, melanjutkan...');
                resolve();
                return;
            }
            console.log('[Wizdam API] Memuat Highcharts...');
            
            const oldScripts = document.querySelectorAll('script[src*="highcharts"]');
            oldScripts.forEach(script => script.parentNode.removeChild(script));
            
            const script = document.createElement('script');
            script.src = 'https://code.highcharts.com/highcharts.js';
            script.onload = function() {
                console.log('[Wizdam API] Highcharts berhasil dimuat');
                
                const accessibilityScript = document.createElement('script');
                accessibilityScript.src = 'https://code.highcharts.com/modules/accessibility.js';
                accessibilityScript.onload = function() {
                    console.log('[Wizdam API] Highcharts Accessibility Module berhasil dimuat');
                    
                    setTimeout(() => {
                        if (window.Highcharts) {
                            console.log('[Wizdam API] Setting up Highcharts defaults...');
                            try {
                                Highcharts.setOptions({
                                    lang: {
                                        thousandsSep: '.'
                                    },
                                    chart: {
                                        style: {
                                            fontFamily: config.fontFamily
                                        }
                                    }
                                });
                                console.log('[Wizdam API] Highcharts defaults set successfully');
                            } catch (e) {
                                console.warn('[Wizdam API] Gagal set defaults Highcharts:', e);
                            }
                        } else {
                            console.warn('[Wizdam API] Highcharts not available for setting defaults');
                        }
                        
                        resolve();
                    }, 100);
                };
                accessibilityScript.onerror = function(error) {
                    console.warn('[Wizdam API] Gagal memuat Highcharts Accessibility Module:', error);
                    resolve();
                };
                document.head.appendChild(accessibilityScript);
            };
            script.onerror = function(error) {
                console.error('[Wizdam API] Gagal memuat Highcharts:', error);
                reject('Gagal memuat Highcharts');
                const graphElement = document.getElementById(config.graphElementId);
                if (graphElement) {
                    showError(graphElement, 'Wizdam Gagal memuat pustaka Highcharts');
                }
            };
            document.head.appendChild(script);
        });
    }

    function loadFonts() {
        return new Promise((resolve) => {
            resolve();
        });
    }

    function injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @import url('https://fonts.googleapis.com/css2?family=Fira+Sans+Condensed:wght@400;500;700&display=swap');
            
            body {
                font-family: ${config.fontFamily};
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            .loading-container {
                padding: 20px;
                text-align: center;
                color: #666;
                font-family: ${config.fontFamily};
            }
            .spinner {
                margin: 0 auto;
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .error {
                padding: 15px;
                background: #ffebee;
                color: #c62828;
                border-radius: 4px;
                text-align: center;
                font-family: ${config.fontFamily};
            }
            .no-data {
                padding: 15px;
                background: #f5f5f5;
                color: #666;
                border-radius: 4px;
                text-align: center;
                font-family: ${config.fontFamily};
            }
            .chart-container {
                padding: 5px;
                background-color: transparent;
                margin: 10px 0 !important;
                height: 220px;
                min-height: 200px;
            }
            
            /* Highcharts Custom Styles - SAMA seperti asli */
            .highcharts-container {
                font-family: ${config.fontFamily} !important;
            }
            .highcharts-title {
                font-family: ${config.fontFamily} !important;
                font-weight: bold !important;
            }
            .highcharts-axis-title {
                font-family: ${config.fontFamily} !important;
                font-weight: bold !important;
            }
            .highcharts-axis-labels {
                font-family: ${config.fontFamily} !important;
            }
            .highcharts-legend-item text {
                font-family: ${config.fontFamily} !important;
            }
            .highcharts-tooltip {
                font-family: ${config.fontFamily} !important;
            }
            .highcharts-axis-line {
                stroke-width: 1.5px;
            }
            .highcharts-tick {
                stroke-width: 1px;
            }
            .highcharts-plot-band {
                fill-opacity: 1 !important;
            }
            
            /* Article List Styles - SAMA seperti asli */
            .scopus-articles-container {
                margin-top: 20px;
                font-family: ${config.fontFamily};
            }
            .scopus-articles-list {
                padding: 0;
                margin: 0;
            }
            .scopus-article-item {
                padding: 15px 0;
                border-bottom: 1px solid #eee;
            }
            .scopus-article-item:last-child {
                border-bottom: none;
            }
            .article-title {
                font-weight: bold;
                margin-bottom: 5px;
                color: #333;
            }
            .article-authors {
                color: #666;
                margin-bottom: 3px;
            }
            .article-meta {
                font-size: 0.9em;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 10px;
            }
            .article-source {
                font-style: italic;
                color: #333;
            }
            .article-citations {
                color: #666;
                white-space: nowrap;
            }
            .article-doi {
                color: #1565c0;
                text-decoration: none;
            }
            .article-doi:hover {
                text-decoration: underline;
            }
            
            @media (max-width: 768px) {
                .article-meta {
                    flex-direction: column;
                    gap: 5px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // loadScopusData - SAMA PERSIS seperti asli
    async function loadScopusData() {
        const graphElement = document.getElementById(config.graphElementId);
        const articlesElement = document.getElementById(config.articlesElementId);
        
        if (!graphElement) {
            console.error('[Wizdam API] Elemen graph tidak ditemukan: ' + config.graphElementId);
            return;
        }
        
        console.log('[Wizdam API] Elemen graph ditemukan:', graphElement);
        console.log('[Wizdam API] Elemen articles:', articlesElement);
        
        config.authorId = extractScopusAuthorId();
        if (!config.authorId) {
            showError(graphElement, 'Scopus Author ID tidak ditemukan');
            if (articlesElement) showError(articlesElement, 'Scopus Author ID tidak ditemukan');
            return;
        }
        
        console.log('[Wizdam API] Scopus Author ID ditemukan:', config.authorId);
        
        showLoading(graphElement);
        if (articlesElement) showLoading(articlesElement);
        
        try {
            const response = await fetch(`${config.apiEndpoint}?authorid=${config.authorId}`);
            if (!response.ok) {
                throw new Error('Respons jaringan tidak baik: ' + response.statusText);
            }
            
            const data = await response.json();
            console.log('[Wizdam API] Data Scopus diterima:', data);
            
            const lastUpdated = data.last_updated || data.data?.last_updated || new Date().toISOString().split('T')[0];
            updateLastUpdatedDate(lastUpdated);
            
            if (data.status !== 'success') {
                throw new Error(data.error || 'Terjadi kesalahan yang tidak diketahui');
            }
            
            if (!data.data.publications || data.data.publications.length === 0) {
                showNoData(graphElement);
                if (articlesElement) showNoData(articlesElement);
                return;
            }
            
            const groupedData = groupPublicationsByYear(data.data.publications);
            console.log('[Wizdam API] Data publikasi dikelompokkan:', groupedData);
            
            if (graphElement) {
                try {
                    console.log('[Wizdam API] Membuat grafik di elemen:', graphElement);
                    createPublicationChart(graphElement, groupedData);
                } catch (chartError) {
                    console.error('[Wizdam API] Error saat membuat grafik:', chartError);
                    showError(graphElement, 'Gagal membuat grafik: ' + chartError.message);
                }
            }
            
            if (articlesElement) {
                console.log('[Wizdam API] Rendering daftar artikel di elemen:', articlesElement);
                renderArticlesList(articlesElement, data.data.publications, lastUpdated);
            }
        } catch (error) {
            console.error('[Wizdam API] Kesalahan saat mengambil data Scopus:', error);
            showError(graphElement, error.message);
            if (articlesElement) showError(articlesElement, error.message);
            
            updateLastUpdatedDate('Unknown date');
        }
    }
    
    async function init() {
        console.log('[Wizdam API] Inisialisasi visualizer Scopus dengan perbaikan Open Access...');
        injectStyles();
        try {
            await Promise.all([
                loadFonts(),
                loadHighcharts()
            ]);
            console.log('[Wizdam API] Font dan Highcharts dimuat, memuat data Scopus...');
            await loadScopusData();
        } catch (error) {
            console.error('[Wizdam API] Kesalahan inisialisasi:', error);
            const graphElement = document.getElementById(config.graphElementId);
            if (graphElement) {
                showError(graphElement, error.message || 'Inisialisasi gagal');
            }
        }
    }

    // Start execution when document is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();