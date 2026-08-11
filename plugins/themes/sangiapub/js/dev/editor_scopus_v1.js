/**
 * scopus-visualizer-with-custom-font.js - Versi Perbaikan
 * Fokus pada:
 * 1. Ketebalan dan kecerahan sumbu Y
 * 2. Tick mark pada kedua sumbu Y
 * 3. Hover latar belakang
 * 
 * Perubahan:
 * - Standardisasi ketebalan garis menjadi 1.5px
 * - Ukuran legenda sitasi (bulatan) lebih kecil
 * - Tick mark kedua sumbu Y diatur menjadi 1px (lebih tipis dari ketebalan sumbu Y)
 */
(function() {
    // Konfigurasi
    const config = {
        apiEndpoint: '/api/scopus_editor',
        authorId: null,
        graphElementId: 'scopus-graph',
        articlesElementId: 'scopus-articles',
        articleDetailSelector: '.scopus-article-detail',
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
                fill: 'rgba(0, 158, 206, 0.85)',
                border: 'rgba(20, 184, 188, 1)',  // Warna biru kehijauan
                hover: 'rgba(20, 184, 188, 0.95)',
                fade: 'rgba(20, 184, 188, 0.3)'
            },
            citations: {
                line: 'rgba(147, 104, 206, 1)',   // Warna ungu
                point: 'rgba(255, 255, 255, 1)',
                pointBorder: 'rgba(147, 104, 206, 1)',
                hover: 'rgba(147, 104, 206, 1)',
                fade: 'rgba(147, 104, 206, 0.3)'
            },
            grid: 'rgba(200, 200, 200, 0.3)',
            gridZero: 'rgba(0, 115, 152, 1)',
            text: '#333',
            // Warna untuk hover background
            hoverBackground: 'rgba(230, 230, 230, 0.7)'
        },
        fontFamily: "NexusSans Pro, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif" 
    };

    // Fungsi utilitas (tidak diubah)
    function getParameterByName(name, defaultValue = null) {
        const url = window.location.href;
        name = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
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
        // Pastikan semua tahun antara min dan max terwakili
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

    function createPublicationChart(element, publicationData) {
        // Bersihkan elemen terlebih dahulu
        element.innerHTML = '';
        // Buat wadah chart dengan tinggi yang cukup
        const chartContainer = document.createElement('div');
        chartContainer.className = 'chart-container';
        chartContainer.style.position = 'relative';
        chartContainer.style.height = '150px';
        chartContainer.style.width = '100%';
        chartContainer.style.padding = '5px';
        // Tambahkan judul
        const titleElement = document.createElement('div');
        titleElement.textContent = 'Latest number of Publication and Citations';
        titleElement.style.textAlign = 'center';
        titleElement.style.fontWeight = 'bold';
        titleElement.style.fontSize = '14px';
        titleElement.style.marginTop = '5px';
        titleElement.style.color = '#333';
        titleElement.style.position = 'relative';
        titleElement.style.fontVariantNumeric = 'lining-nums proportional-nums';
        titleElement.style.zIndex = '1';
        titleElement.style.fontFamily = config.fontFamily;
        // Tambahkan judul dan kemudian kontainer chart
        element.appendChild(titleElement);
        element.appendChild(chartContainer);
        // Buat element canvas untuk chart
        const canvas = document.createElement('canvas');
        chartContainer.appendChild(canvas);
        // Persiapkan data
        const years = Object.keys(publicationData.byYear).sort();
        const documentCounts = years.map(year => publicationData.byYear[year].count);
        const citationCounts = years.map(year => publicationData.byYear[year].citations);
        
        // Hitung nilai maksimum nyata
        const maxDocuments = Math.max(...documentCounts, 1);
        const maxCitations = Math.max(...citationCounts, 1);
        
        // Fungsi untuk menghitung skala sumbu Y dengan pembulatan ke puluhan
        function calculateYAxisScale(maxValue) {
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
            
            // Pastikan roundedMax bisa dibagi menjadi 4 bagian untuk 5 titik
            while (roundedMax % 4 !== 0) {
                roundedMax++;
            }
            
            const interval = roundedMax / 4;
            
            return {
                min: 0,
                max: roundedMax,
                stepSize: interval
            };
        }
        
        // Terapkan skala untuk dokumen dan kutipan
        const documentsScale = calculateYAxisScale(maxDocuments);
        const citationsScale = calculateYAxisScale(maxCitations);

        // Modifikasi langsung ke Chart.js global defaults
        // untuk meningkatkan ketebalan dan kecerahan garis sumbu
        try {
            // Ubah defaults untuk ketebalan garis
            if (Chart.defaults.scale) {
                // PERUBAHAN: Standardisasi ketebalan garis menjadi 1.5
                Chart.defaults.scale.grid.lineWidth = 1;
                Chart.defaults.scale.grid.drawBorder = true;
                Chart.defaults.scale.grid.borderWidth = 1.5;
                
                // Tingkatkan kecerahan warna
                Chart.defaults.scale.grid.borderColor = function(context) {
                    if (context.scale.id === 'y-axis-documents') {
                        return 'rgba(20, 184, 188, 1)'; // Sumbu Y1: Biru kehijauan terang
                    } else if (context.scale.id === 'y-axis-citations') {
                        return 'rgba(147, 104, 206, 1)'; // Sumbu Y2: Ungu terang
                    }
                    return 'rgba(0, 0, 0, 1)'; // Default: Hitam
                };
            }
        } catch (e) {
            console.warn('Tidak dapat memodifikasi Chart.js defaults:', e);
        }

        // Buat chart dengan Chart.js
        const chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: years,
                datasets: [
                    {
                        label: 'Publications',
                        type: 'bar',
                        backgroundColor: config.colors.publications.fill,
                        borderColor: config.colors.publications.border,
                        borderWidth: 1,
                        data: documentCounts,
                        yAxisID: 'y-axis-documents',
                        // Tambahkan properti pointStyle untuk legenda Publications
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Citations',
                        type: 'line',
                        borderColor: config.colors.citations.line,
                        backgroundColor: 'transparent',
                        borderWidth: 1.5, // PERUBAHAN: Standardisasi ketebalan garis menjadi 1.5
                        pointBackgroundColor: config.colors.citations.point,
                        pointBorderColor: config.colors.citations.pointBorder,
                        pointBorderWidth: 1.5, // PERUBAHAN: Standardisasi ketebalan garis menjadi 1.5
                        pointRadius: 3, // PERUBAHAN: Ukuran titik/bulatan dikurangi dari 4 menjadi 3
                        pointHoverRadius: 4, // PERUBAHAN: Ukuran hover titik/bulatan dikurangi dari 6 menjadi 4
                        data: citationCounts,
                        fill: false,
                        yAxisID: 'y-axis-citations',
                        // PERUBAHAN: Ukuran pointStyle untuk legenda Citations diperkecil
                        pointStyle: 'circle'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // Matikan animasi
                animation: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 10,
                            boxWidth: 20,
                            color: config.colors.text,
                            font: {
                                family: config.fontFamily,
                                size: 12,
                                weight: 'normal'
                            },
                            // Aktifkan usePointStyle untuk menggunakan pointStyle dari dataset
                            usePointStyle: true,
                            // PERUBAHAN: Pengaturan tambahan untuk ukuran legenda
                            pointStyleWidth: 8, // Ukuran bulatan citations pada legenda diperkecil
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(102, 102, 102, 0.9)',
                        titleColor: 'rgba(242, 242, 242, 1)',
                        bodyColor: 'rgba(242, 242, 242, 1)',
                        borderColor: 'rgba(200, 200, 200, 0.7)',
                        size: 13,
                        borderWidth: 1,
                        titleFont: {
                            family: config.fontFamily,
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: config.fontFamily,
                            size: 12
                        },
                        padding: 10,
                        displayColors: false
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: {
                            // Matikan grid vertikal
                            display: false
                        },
                        ticks: {
                            color: config.colors.text,
                            width: 1, // PERUBAHAN: Standardisasi ketebalan garis
                            font: {
                                family: config.fontFamily,
                                size: 12,
                                weight: 'normal'
                            },
                            // Pastikan tick mark muncul
                            display: true,
                            major: {
                                enabled: true
                            }
                        },
                        // PERUBAHAN: Ketebalan sumbu X: 1.5px (standardisasi)
                        border: {
                            display: true,
                            width: 1.5,
                            color: 'rgba(0, 0, 0, 1)' // Hitam solid
                        }
                    },
                    'y-axis-documents': {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        // PERUBAHAN: Standardisasi ketebalan garis sumbu Y1 menjadi 1.5
                        border: {
                            display: true,
                            width: 1.5, // Ketebalan sumbu Y: 1.5px
                            color: 'rgba(20, 184, 188, 1)' // Biru kehijauan terang
                        },
                        grid: {
                            color: function(context) {
                                if (context.tick.value === 0) {
                                    return config.colors.gridZero;
                                }
                                return 'rgba(200, 200, 200, 0.3)';
                            },
                            lineWidth: function(context) {
                                if (context.tick.value === 0) {
                                    return 1.5; // PERUBAHAN: Standardisasi ketebalan garis
                                }
                                return 1;
                            },
                            drawBorder: true,
                            borderWidth: 1.5, // PERUBAHAN: Standardisasi ketebalan garis
                            borderColor: 'rgba(20, 184, 188, 1)'
                        },
                        min: documentsScale.min,
                        max: documentsScale.max,
                        ticks: {
                            stepSize: documentsScale.stepSize,
                            color: config.colors.publications.border,
                            font: {
                                family: config.fontFamily,
                                size: 12
                            },
                            // Pastikan tick mark muncul
                            display: true,
                            // PERUBAHAN: Tick mark sedikit lebih tipis dari sumbu Y (1px vs 1.5px)
                            width: 1,
                            major: {
                                enabled: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Documents',
                            color: config.colors.publications.border,
                            font: {
                                family: config.fontFamily,
                                weight: 'bold',
                                size: 14
                            }
                        }
                    },
                    'y-axis-citations': {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        // PERUBAHAN: Standardisasi ketebalan garis sumbu Y2 menjadi 1.5
                        border: {
                            display: true,
                            width: 1.5, // Ketebalan sumbu Y: 1.5px
                            color: 'rgba(147, 104, 206, 1)' // Ungu terang
                        },
                        grid: {
                            display: true,
                            drawBorder: true,
                            borderWidth: 1.5, // PERUBAHAN: Standardisasi ketebalan garis
                            borderColor: 'rgba(147, 104, 206, 1)'
                        },
                        min: citationsScale.min,
                        max: citationsScale.max,
                        ticks: {
                            stepSize: citationsScale.stepSize,
                            color: config.colors.citations.line,
                            font: {
                                family: config.fontFamily,
                                size: 12
                            },
                            // Pastikan tick mark muncul
                            display: true,
                            // PERUBAHAN: Tick mark sedikit lebih tipis dari sumbu Y (1px vs 1.5px)
                            width: 1,
                            major: {
                                enabled: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Citations',
                            color: config.colors.citations.line,
                            font: {
                                family: config.fontFamily,
                                weight: 'bold',
                                size: 14
                            }
                        }
                    }
                },
                // Hover
                hover: {
                    mode: 'index',
                    intersect: false
                },
                // Visual element configs
                elements: {
                    line: {
                        tension: 0.3,
                        borderWidth: 1.5 // PERUBAHAN: Standardisasi ketebalan garis menjadi 1.5
                    },
                    point: {
                        radius: 3, // PERUBAHAN: Ukuran titik/bulatan dikurangi dari 4 menjadi 3
                        hoverRadius: 4, // PERUBAHAN: Ukuran hover titik/bulatan dikurangi dari 6 menjadi 4
                        borderWidth: 1.5 // PERUBAHAN: Standardisasi ketebalan garis menjadi 1.5
                    }
                }
            }
        });

        // Tambahkan event listener utk efek hover pada latar belakang
        canvas.addEventListener('mousemove', function(evt) {
            try {
                const activePoints = chart.getElementsAtEventForMode(evt, 'index', { intersect: false });
                if (activePoints.length > 0) {
                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    const barWidth = chartArea.width / years.length;
                    const index = activePoints[0].index;
                    
                    // Clear canvas (redraw tanpa efek hover)
                    chart.update('none');
                    
                    // Gambar latar belakang untuk kolom yang di-hover
                    ctx.fillStyle = config.colors.hoverBackground;
                    ctx.fillRect(
                        chartArea.left + index * barWidth, 
                        chartArea.top, 
                        barWidth, 
                        chartArea.height
                    );
                    
                    // Bold label sumbu X yang di-hover
                    const scale = chart.scales.x;
                    scale.options.ticks.font = function(context) {
                        return {
                            family: config.fontFamily,
                            size: 12,
                            weight: context.index === index ? 'bold' : 'normal'
                        };
                    };
                    
                    // Redraw chart dengan latar belakang hover
                    chart.draw();
                }
            } catch (e) {
                console.warn('Error saat hover:', e);
            }
        });
        
        // Reset efek hover saat mouse keluar dari chart
        canvas.addEventListener('mouseout', function() {
            chart.update();
        });
        
        return chart;
    }

    // Fungsi-fungsi lainnya tidak berubah
    function renderArticlesList(element, publications) {
        if (!element) {
            console.error('Elemen articles tidak ditemukan');
            return;
        }
        const articleDetailElement = element.querySelector(config.articleDetailSelector) || element;
        const sortedPubs = publications.sort((a, b) => b.citation_count - a.citation_count);
        const container = document.createElement('div');
        container.className = 'scopus-articles-container';
        const articlesList = document.createElement('div');
        articlesList.className = 'scopus-articles-list';
        sortedPubs.forEach(pub => {
            const articleItem = document.createElement('div');
            articleItem.className = 'scopus-article-item';
            articleItem.style.padding = '15px 0';
            articleItem.style.borderBottom = '1px solid #eee';
            articleItem.style.fontFamily = config.fontFamily;
            articleItem.innerHTML = `
                <div class="article-title" style="font-weight: bold; margin-bottom: 5px; color: #333; font-family: ${config.fontFamily}">${pub.title || 'Judul tidak tersedia'}</div>
                <div class="article-authors" style="color: #666; margin-bottom: 3px; font-family: ${config.fontFamily}">${pub.authors_string || 'Informasi penulis tidak tersedia'}</div>
                <div class="article-meta" style="font-size: 0.9em; display: flex; flex-wrap: wrap; justify-content: space-between; font-family: ${config.fontFamily}">
                    <span class="article-source" style="font-style: italic; color: #333;">${pub.source || 'Sumber tidak diketahui'}, ${pub.year || 'Tahun tidak diketahui'}</span>
                    <span class="article-citations" style="color: #666; white-space: nowrap;">Kutipan: ${pub.citation_count || 0}</span>
                    ${pub.doi && pub.doi !== 'N/A' ? 
                        `<a href="https://doi.org/ ${pub.doi}" target="_blank" class="article-doi" style="color: #1565c0; text-decoration: none;">DOI: ${pub.doi}</a>` : ''}
                </div>
            `;
            articlesList.appendChild(articleItem);
        });
        container.appendChild(articlesList);
        articleDetailElement.innerHTML = '';
        articleDetailElement.appendChild(container);
    }

    async function loadScopusData() {
        const graphElement = document.getElementById(config.graphElementId);
        const articlesElement = document.getElementById(config.articlesElementId);
        if (!graphElement) {
            console.error('Elemen graph tidak ditemukan');
            return;
        }
        config.authorId = extractScopusAuthorId();
        if (!config.authorId) {
            showError(graphElement, 'Scopus Author ID tidak ditemukan');
            if (articlesElement) showError(articlesElement, 'Scopus Author ID tidak ditemukan');
            return;
        }
        showLoading(graphElement);
        if (articlesElement) showLoading(articlesElement);
        try {
            const response = await fetch(`${config.apiEndpoint}?authorid=${config.authorId}`);
            if (!response.ok) {
                throw new Error('Respons jaringan tidak baik: ' + response.statusText);
            }
            const data = await response.json();
            if (data.status !== 'success') {
                throw new Error(data.error || 'Terjadi kesalahan yang tidak diketahui');
            }
            if (!data.data.publications || data.data.publications.length === 0) {
                showNoData(graphElement);
                if (articlesElement) showNoData(articlesElement);
                return;
            }
            const groupedData = groupPublicationsByYear(data.data.publications);
            if (graphElement) {
                try {
                    createPublicationChart(graphElement, groupedData);
                } catch (chartError) {
                    console.error('Error saat membuat grafik:', chartError);
                    showError(graphElement, 'Gagal membuat grafik: ' + chartError.message);
                }
            }
            if (articlesElement) {
                renderArticlesList(articlesElement, data.data.publications);
            }
        } catch (error) {
            console.error('Kesalahan saat mengambil data Scopus:', error);
            showError(graphElement, error.message);
            if (articlesElement) showError(articlesElement, error.message);
        }
    }

    function injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            /* Load font dari Google Fonts */
            @import url('https://fonts.googleapis.com/css2?family=Fira+Sans+Condensed:wght@400;500;700&display=swap');
            /* Scopus Visualizer Styles */
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
                margin-top: -5px !important;
                height: 200px; /* Set tinggi maksimal grafik menjadi 200px */
                min-height: 150px;
            }
            /* Gaya tambahan untuk tick mark */
            .chart-container canvas {
                --axis-width: 1.5px; /* PERUBAHAN: Standardisasi ketebalan garis */
                --tick-width: 1px; /* PERUBAHAN: Tick mark lebih tipis */
                --axis-color-documents: rgba(20, 184, 188, 1);
                --axis-color-citations: rgba(147, 104, 206, 1);
            }
            /* Article List Styles */
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

    function loadChartJs() {
        return new Promise((resolve, reject) => {
            if (window.Chart) {
                console.log('Chart.js sudah dimuat, melanjutkan...');
                resolve();
                return;
            }
            console.log('Memuat Chart.js v3.3.2...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.3.2/dist/chart.min.js';
            script.onload = function() {
                console.log('Chart.js v3.3.2 berhasil dimuat');
                
                // Setelah Chart.js dimuat, ubah beberapa default untuk perbaikan
                if (window.Chart && window.Chart.defaults) {
                    try {
                        // PERUBAHAN: Standardisasi font
                        window.Chart.defaults.font = {
                            family: config.fontFamily,
                            size: 12
                        };
                        
                        // PERUBAHAN: Standardisasi ketebalan garis menjadi 1.5
                        if (window.Chart.defaults.elements) {
                            window.Chart.defaults.elements.line = {
                                borderWidth: 1.5,
                                tension: 0.3
                            };
                            
                            // PERUBAHAN: Standardisasi ukuran point dan ketebalan border
                            window.Chart.defaults.elements.point = {
                                radius: 3,
                                hoverRadius: 4,
                                borderWidth: 1.5
                            };
                        }
                    } catch (e) {
                        console.warn('Tidak dapat mengubah defaults Chart.js:', e);
                    }
                }
                
                resolve();
            };
            script.onerror = function(error) {
                console.error('Gagal memuat Chart.js:', error);
                reject('Gagal memuat Chart.js');
                const graphElement = document.getElementById(config.graphElementId);
                if (graphElement) {
                    showError(graphElement, 'Gagal memuat pustaka Chart.js');
                }
            };
            document.head.appendChild(script);
        });
    }

    function loadFonts() {
        return new Promise((resolve) => {
            // Jika menggunakan font custom, bisa menggunakan FontFaceObserver
            // Di sini kita menggunakan Google Fonts yang sudah di-load via CSS
            resolve();
        });
    }

    async function init() {
        console.log('Inisialisasi visualizer Scopus...');
        injectStyles();
        try {
            await Promise.all([
                loadFonts(),
                loadChartJs()
            ]);
            console.log('Font dan Chart.js dimuat, memuat data Scopus...');
            await loadScopusData();
        } catch (error) {
            console.error('Kesalahan inisialisasi:', error);
            const graphElement = document.getElementById(config.graphElementId);
            if (graphElement) {
                showError(graphElement, error.message || 'Inisialisasi gagal');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();