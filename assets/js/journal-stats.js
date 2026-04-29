/**
 * journal-stats.js
 * Visualisasi statistik jurnal dengan tahun pada sumbu X
 * @author Rochmady and Wizdam Team
 * @version v1.22.6-isolated
 * License None
 */
(function(window, document) {
    'use strict';
    
    // Namespace khusus untuk menghindari konflik
    const JournalStats = {
        initialized: false,
        chartInstances: [],
        timeouts: [],
        animationFrames: [],
        eventListeners: [],
        
        init() {
            if (this.initialized) return;
            
            const statsChartDiv = document.getElementById('journalStatsCharts');
            if (!statsChartDiv) return;
            
            this.initialized = true;
            const jsonPath = statsChartDiv.dataset.jsonPath;
            
            if (!jsonPath) {
                console.error('[Wizdam Journal Stats]: Data path tidak tersedia');
                return;
            }
            
            this.loadStats(jsonPath);
        },
        
        async loadStats(jsonPath) {
            try {
                // Tampilkan loading spinner dengan background transparan
                const container = document.getElementById('journalStatsCharts');
                container.innerHTML = `
                    <div class="loading-overlay" style="
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: rgba(255, 255, 255, 0.7);
                        z-index: 1000;
                    ">
                        <div class="loading-spinner" style="
                            border: 5px solid #f3f3f3;
                            border-top: 5px solid #3498db;
                            border-radius: 50%;
                            width: 50px;
                            height: 50px;
                            animation: spin 1s linear infinite;
                        "></div>
                        <style>
                            @keyframes spin {
                                0% { transform: rotate(0deg); }
                                100% { transform: rotate(360deg); }
                            }
                        </style>
                    </div>
                    <div class="loading-text" style="
                        position: absolute;
                        top: 60%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        z-index: 1001;
                        color: #333;
                        font-weight: bold;
                    ">Data statistics loading...</div>
                `;
                
                // Lakukan fetch data
                const response = await fetch(jsonPath);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Dekompresi dan parse data
                let statsData;
                if (jsonPath.endsWith('.gz')) {
                    if (typeof pako === 'undefined') {
                        throw new Error('Library pako tidak tersedia untuk dekompresi');
                    }
                    const compressedData = await response.arrayBuffer();
                    const decompressedData = pako.inflate(new Uint8Array(compressedData), { to: 'string' });
                    statsData = JSON.parse(decompressedData);
                } else {
                    statsData = await response.json();
                }
                
                // Validasi data
                if (!statsData || !statsData.yearlyStats || statsData.yearlyStats.length === 0) {
                    container.innerHTML = '<div class="error-stats">Data tidak valid atau kosong</div>';
                    return;
                }
                
                // Bersihkan kontainer dan siapkan untuk grafik
                container.innerHTML = '';
                
                // Siapkan kontainer grafik
                this.setupCharts(statsData, container);
                
            } catch (error) {
                this.handleError(error);
            }
        },
        
        handleError(error) {
            console.error('[Wizdam Journal Stats]: Error:', error);
            const container = document.getElementById('journalStatsCharts');
            if (container) {
                container.innerHTML = `<div class="error-stats">Gagal memuat data: ${error.message}</div>`;
            }
        },
        
        setupCharts(data, container) {
            // GRAFIK 1: Waktu Pemrosesan Artikel
            const reviewSection = document.createElement('div');
            reviewSection.className = 'stats-section';
            reviewSection.innerHTML = `
                <h3 class="stats-section-title">Publication Timeline</h3>
                <div class="stats-chart-container">
                    <canvas id="reviewTimelineChart" height="300"></canvas>
                </div>
            `;
            container.appendChild(reviewSection);
            
            // GRAFIK 2: Publikasi dan Penerimaan Tahunan
            const publicationSection = document.createElement('div');
            publicationSection.className = 'stats-section';
            publicationSection.innerHTML = `
                <h3 class="stats-section-title">Acceptances Rate and Days to Publication</h3>
                <div class="stats-chart-container">
                    <canvas id="publicationTimelineChart" height="300"></canvas>
                </div>
            `;
            container.appendChild(publicationSection);
            
            // GRAFIK 3: Statistik Views & Downloads
            const viewsSection = document.createElement('div');
            viewsSection.className = 'stats-section';
            viewsSection.innerHTML = `
                <h3 class="stats-section-title">Article Downloads & Abstract Views Stats</h3>
                <div class="stats-chart-container">
                    <canvas id="viewsDownloadsChart" height="300"></canvas>
                </div>
            `;
            container.appendChild(viewsSection);
            
            // CSS untuk styling - cek apakah sudah ada
            if (!document.getElementById('journal-stats-styles')) {
                const style = document.createElement('style');
                style.id = 'journal-stats-styles';
                style.textContent = `
                    .stats-section {
                        margin-bottom: 30px;
                        padding: 20px;
                    }
                    .stats-section-title {
                        text-align: center;
                        font-weight: 600;
                        color: #333;
                        font-size: 18px;
                        font-family: "Elsevier Sans",Nexus Sans,'Segoe UI',Arial,Helvetica,sans-serif;
                    }
                    .stats-chart-container {
                        position: relative;
                        height: 300px;
                        background-color: #FFFFFF;
                        border-radius: 4px;
                    }
                    .loading-stats, .error-stats {
                        text-align: center;
                        padding: 20px;
                        background-color: #f8f9fa;
                        border-radius: 4px;
                        margin: 20px 0;
                    }
                    .error-stats {
                        color: #dc3545;
                        background-color: #f8d7da;
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Buat grafik
            this.createCharts(data);
        },
        
        createCharts(data) {
            if (!data || !data.yearlyStats || data.yearlyStats.length === 0) {
                return;
            }
            
            // Filter tahun yang valid dan kemudian urutkan berdasarkan tahun
            const validYearlyStats = data.yearlyStats
                .filter(stat => stat.year !== null && stat.year !== undefined && stat.year !== 0 && !isNaN(stat.year))
                .sort((a, b) => a.year - b.year);
            
            // Ekstraksi data dari validYearlyStats
            const years = validYearlyStats.map(stat => stat.year);
            const views = validYearlyStats.map(stat => Number(stat.views) || 0);
            const downloads = validYearlyStats.map(stat => Number(stat.downloads) || 0);
            const submissions = validYearlyStats.map(stat => Number(stat.submissions) || 0);
            const published = validYearlyStats.map(stat => Number(stat.published) || 0);
            const acceptRates = validYearlyStats.map(stat => Number(stat.acceptRate) || 0);
            const daysToPublish = validYearlyStats.map(stat => Number(stat.daysToPublish) || 0);
            const daysToFirstDecision = validYearlyStats.map(stat => Number(stat.daysToFirstDecision) || 0);
            const daysReview = validYearlyStats.map(stat => Number(stat.daysPerReview) || 0);
            const daysToAcceptance = validYearlyStats.map(stat => Number(stat.daysToAcceptance) || 0);
            const daysAcceptanceToPublication = validYearlyStats.map(stat => Number(stat.daysAcceptanceToPublication) || 0);
            
            // Tema warna profesional
            const colors = {
                blue: 'rgba(54, 162, 235, 1)',
                green: 'rgba(75, 192, 192, 1)',
                purple: 'rgba(153, 102, 255, 1)',
                orange: 'rgba(255, 159, 64, 1)',
                red: 'rgba(255, 99, 132, 1)',
                yellow: 'rgba(255, 205, 86, 1)',
                grey: 'rgba(201, 203, 207, 1)',
                blueLight: 'rgba(54, 162, 235, 0.2)',
                greenLight: 'rgba(75, 192, 192, 0.2)',
                purpleLight: 'rgba(153, 102, 255, 0.2)',
                orangeLight: 'rgba(255, 159, 64, 0.2)',
                redLight: 'rgba(255, 99, 132, 0.2)',
                axisColor: 'rgba(0, 0, 0, 1)',
                gridColor: 'rgba(0, 0, 0, 0.1)',
                zebraColor1: 'rgba(242, 242, 242, 0.5)',
                zebraColor2: 'transparent'
            };
            
            // Fungsi untuk menciptakan konfigurasi sumbu yang optimal dengan 5 titik
            const createOptimalAxisConfig = (values, minValue, maxValue, title, suffix = '', position = 'left') => {
                const effectiveMin = minValue || 0;
                const effectiveMax = maxValue || Math.max(...values, 1);
                const range = Math.max(effectiveMax - effectiveMin, 1);
                const stepSize = Math.ceil(range / 4);
                
                return {
                    type: 'linear',
                    display: true,
                    position: position,
                    title: {
                        display: true,
                        text: title,
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        padding: {top: 5, bottom: 15}
                    },
                    beginAtZero: true,
                    min: effectiveMin,
                    max: effectiveMax + (range * 0.1),
                    border: {
                        display: true,
                        width: 2,
                        color: colors.axisColor,
                        dash: []
                    },
                    grid: {
                        color: colors.gridColor,
                        lineWidth: 0,
                        drawBorder: true,
                        drawOnChartArea: position === 'left',
                        drawTicks: true,
                        tickLength: 7,
                        tickWidth: 1.25,
                        tickColor: colors.axisColor
                    },
                    ticks: {
                        padding: 10,
                        count: 5,
                        stepSize: stepSize,
                        autoSkip: false,
                        maxRotation: 0,
                        font: {
                            weight: 600,
                            size: 12
                        },
                        color: colors.axisColor,
                        callback: function(value) {
                            return Math.ceil(value) + suffix;
                        }
                    }
                };
            };
            
            // Konfigurasi sumbu X
            const xAxisConfig = {
                title: {
                    display: true,
                    text: 'Year',
                    font: {
                        size: 14,
                        weight: 'bold'
                    },
                    padding: {top: 5, bottom: 5}
                },
                border: {
                    display: true,
                    width: 3,
                    color: colors.axisColor,
                    dash: []
                },
                grid: {
                    color: colors.gridColor,
                    lineWidth: 0,
                    drawBorder: true,
                    drawOnChartArea: false,
                    drawTicks: true,
                    tickLength: 7,
                    tickWidth: 1.25,
                    tickColor: colors.axisColor
                },
                ticks: {
                    padding: 10,
                    autoSkip: true,
                    maxRotation: 0,
                    font: {
                        weight: 600,
                        size: 12
                    },
                    color: colors.axisColor,
                    callback: function(value, index) {
                        return years[index];
                    }
                }
            };
            
            // Plugin untuk membuat zebra stripe - local scope
            const zebraStripePlugin = {
                id: 'zebraStripe',
                beforeDraw(chart) {
                    const { ctx, chartArea, scales } = chart;
                    const yScale = scales.y;
                    if (!chartArea || !yScale) return;
                    const { top, bottom, left, right } = chartArea;
                    const width = right - left;
                    const ticks = yScale.ticks;
                    const tickCount = ticks.length;
                    if (tickCount <= 1) return;
                    const tickHeight = (bottom - top) / (tickCount - 1);
                    ctx.save();
                    for (let i = 0; i < tickCount - 1; i++) {
                        const y = top + i * tickHeight;
                        ctx.fillStyle = i % 2 === 0 ? colors.zebraColor1 : colors.zebraColor2;
                        ctx.fillRect(left, y, width, tickHeight);
                    }
                    ctx.restore();
                }
            };
            
            // Plugin lengkap dengan throttling dan cleanup
            const fixedCompleteInteractionPlugin = {
                id: 'fixedCompleteInteraction',
                
                afterInit(chart) {
                    chart.dynamicLine = { 
                        y: null,
                        y1Value: null,
                        y2Value: null,
                        hoveredXIndex: null,
                        chartYears: [],
                        lastUpdate: 0,
                        isDrawing: false,
                        animationFrame: null,
                        lineMarkerAnimations: new Map(),
                        targetLineMarkerSizes: new Map(),
                        currentLineMarkerSizes: new Map(),
                        animationSpeed: 0.2
                    };
                    
                    chart.dynamicLine.chartYears = chart.data.labels || [];
                    chart.dynamicLine.colors = colors;
                    
                    // Inisialisasi marker sizes HANYA untuk line datasets
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        if (dataset.type === 'line' || (!dataset.type && chart.config.type === 'line')) {
                            dataset.data.forEach((_, pointIndex) => {
                                const key = `${datasetIndex}-${pointIndex}`;
                                const defaultSize = dataset.pointRadius || 3;
                                chart.dynamicLine.currentLineMarkerSizes.set(key, defaultSize);
                                chart.dynamicLine.targetLineMarkerSizes.set(key, defaultSize);
                            });
                        }
                    });
                },
                
                afterEvent(chart, args) {
                    const { event } = args;
                    const { chartArea, scales } = chart;
                    
                    // Throttling untuk mencegah terlalu sering update
                    const now = Date.now();
                    if (now - chart.dynamicLine.lastUpdate < 16) return;
                    
                    // Cegah infinite loop
                    if (chart.dynamicLine.isDrawing) return;
                    
                    const isInChartArea = event.x >= chartArea.left && event.x <= chartArea.right &&
                                         event.y >= chartArea.top && event.y <= chartArea.bottom;
                    
                    let hasChanged = false;
                    
                    if (isInChartArea) {
                        const newY = event.y;
                        let newY1Value = null;
                        let newY2Value = null;
                        let newHoveredXIndex = null;
                        
                        if (scales.y) {
                            newY1Value = scales.y.getValueForPixel(event.y);
                        }
                        if (scales.y1) {
                            newY2Value = scales.y1.getValueForPixel(event.y);
                        }
                        
                        const chartYears = chart.dynamicLine.chartYears;
                        if (chartYears.length > 0) {
                            const chartWidth = chartArea.right - chartArea.left;
                            const relativeX = event.x - chartArea.left;
                            const hoveredXIndex = Math.floor((relativeX / chartWidth) * chartYears.length);
                            
                            if (hoveredXIndex >= 0 && hoveredXIndex < chartYears.length) {
                                newHoveredXIndex = hoveredXIndex;
                            }
                        }
                        
                        if (Math.abs(newY - (chart.dynamicLine.y || 0)) > 2 || 
                            newHoveredXIndex !== chart.dynamicLine.hoveredXIndex) {
                            
                            chart.dynamicLine.y = newY;
                            chart.dynamicLine.y1Value = newY1Value;
                            chart.dynamicLine.y2Value = newY2Value;
                            
                            if (newHoveredXIndex !== chart.dynamicLine.hoveredXIndex) {
                                this.updateLineMarkerTargetSizes(chart, newHoveredXIndex);
                            }
                            
                            chart.dynamicLine.hoveredXIndex = newHoveredXIndex;
                            hasChanged = true;
                        }
                    } else {
                        if (chart.dynamicLine.y !== null) {
                            chart.dynamicLine.y = null;
                            chart.dynamicLine.y1Value = null;
                            chart.dynamicLine.y2Value = null;
                            
                            if (chart.dynamicLine.hoveredXIndex !== null) {
                                this.updateLineMarkerTargetSizes(chart, null);
                            }
                            
                            chart.dynamicLine.hoveredXIndex = null;
                            hasChanged = true;
                        }
                    }
                    
                    const lineAnimationChanged = this.updateLineMarkerAnimations(chart);
                    
                    if (hasChanged || lineAnimationChanged) {
                        chart.dynamicLine.lastUpdate = now;
                        chart.dynamicLine.isDrawing = true;
                        
                        // Cancel previous frame
                        if (chart.dynamicLine.animationFrame) {
                            cancelAnimationFrame(chart.dynamicLine.animationFrame);
                        }
                        
                        // Schedule new frame
                        chart.dynamicLine.animationFrame = requestAnimationFrame(() => {
                            chart.draw();
                            chart.dynamicLine.isDrawing = false;
                        });
                    }
                },
                
                updateLineMarkerTargetSizes(chart, hoveredXIndex) {
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        if (dataset.type === 'line' || (!dataset.type && chart.config.type === 'line')) {
                            dataset.data.forEach((_, pointIndex) => {
                                const key = `${datasetIndex}-${pointIndex}`;
                                const defaultSize = dataset.pointRadius || 3;
                                
                                if (hoveredXIndex !== null && pointIndex === hoveredXIndex) {
                                    chart.dynamicLine.targetLineMarkerSizes.set(key, defaultSize * 2.5);
                                } else {
                                    chart.dynamicLine.targetLineMarkerSizes.set(key, defaultSize);
                                }
                            });
                        }
                    });
                },
                
                updateLineMarkerAnimations(chart) {
                    let hasChanges = false;
                    const { animationSpeed } = chart.dynamicLine;
                    
                    chart.dynamicLine.currentLineMarkerSizes.forEach((currentSize, key) => {
                        const targetSize = chart.dynamicLine.targetLineMarkerSizes.get(key) || 3;
                        
                        if (Math.abs(currentSize - targetSize) > 0.1) {
                            const newSize = currentSize + (targetSize - currentSize) * animationSpeed;
                            chart.dynamicLine.currentLineMarkerSizes.set(key, newSize);
                            hasChanges = true;
                        } else {
                            chart.dynamicLine.currentLineMarkerSizes.set(key, targetSize);
                        }
                    });
                    
                    return hasChanges;
                },
                
                beforeDatasetsDraw(chart) {
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        if (dataset.type === 'line' || (!dataset.type && chart.config.type === 'line')) {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            if (meta.visible) {
                                dataset.data.forEach((_, pointIndex) => {
                                    const key = `${datasetIndex}-${pointIndex}`;
                                    const animatedSize = chart.dynamicLine.currentLineMarkerSizes.get(key);
                                    if (animatedSize !== undefined && meta.data[pointIndex]) {
                                        meta.data[pointIndex].options.radius = animatedSize;
                                    }
                                });
                            }
                        }
                    });
                },
                
                afterDraw(chart) {
                    const { ctx, chartArea, scales } = chart;
                    const { y, y1Value, y2Value, hoveredXIndex, chartYears, colors } = chart.dynamicLine;
                    
                    if (y === null) return;
                    
                    ctx.save();
                    
                    // 1. HIGHLIGHT KOLOM TAHUN (vertikal)
                    if (hoveredXIndex !== null && hoveredXIndex >= 0 && hoveredXIndex < chartYears.length) {
                        const chartWidth = chartArea.right - chartArea.left;
                        const yearWidth = chartWidth / chartYears.length;
                        const yearLeft = chartArea.left + hoveredXIndex * yearWidth;
                        
                        ctx.fillStyle = 'rgba(116, 191, 241, 0.08)';
                        ctx.fillRect(yearLeft, chartArea.top, yearWidth, chartArea.bottom - chartArea.top);
                    }
                    
                    // 2. GARIS HORIZONTAL
                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, y);
                    ctx.lineTo(chartArea.right, y);
                    ctx.lineWidth = 0.7;
                    ctx.strokeStyle = 'rgba(0, 102, 153, 0.8)';
                    ctx.setLineDash([10, 5]);
                    ctx.stroke();
                    ctx.setLineDash([]);
                    
                    // 3. LABEL Y1 (kiri)
                    if (y1Value !== null && scales.y) {
                        const leftText = Math.ceil(y1Value).toString();
                        ctx.font = 'bold 12px Arial';
                        ctx.fillStyle = colors.blue;
                        const leftTextWidth = ctx.measureText(leftText).width;
                        ctx.fillRect(chartArea.left - leftTextWidth - 20, y - 12, leftTextWidth + 16, 24);
                        ctx.strokeStyle = 'white';
                        ctx.lineWidth = 1;
                        ctx.strokeRect(chartArea.left - leftTextWidth - 20, y - 12, leftTextWidth + 16, 24);
                        ctx.fillStyle = 'white';
                        ctx.textAlign = 'right';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(leftText, chartArea.left - 12, y);
                    }
                    
                    // 4. LABEL Y2 (kanan)
                    if (y2Value !== null && scales.y1) {
                        const rightText = Math.ceil(y2Value).toString();
                        ctx.font = 'bold 12px Arial';
                        ctx.fillStyle = colors.orange;
                        const rightTextWidth = ctx.measureText(rightText).width;
                        ctx.fillRect(chartArea.right + 4, y - 12, rightTextWidth + 16, 24);
                        ctx.strokeStyle = 'white';
                        ctx.lineWidth = 2;
                        ctx.strokeRect(chartArea.right + 4, y - 12, rightTextWidth + 16, 24);
                        ctx.fillStyle = 'white';
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(rightText, chartArea.right + 12, y);
                    }
                    
                    // 5. LABEL KOLOM
                    if (hoveredXIndex !== null && hoveredXIndex >= 0 && hoveredXIndex < chartYears.length) {
                        const yearText = chartYears[hoveredXIndex].toString();
                        const chartWidth = chartArea.right - chartArea.left;
                        const yearX = chartArea.left + (hoveredXIndex + 0.5) * (chartWidth / chartYears.length);
                    
                        ctx.font = 'bold 12px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillStyle = colors.blue;
                        ctx.fill();
                        ctx.fillText(yearText, yearX, chartArea.bottom + 25);
                    }
                    
                    // 6. TITIK INDIKATOR
                    if (y1Value !== null) {
                        ctx.beginPath();
                        ctx.arc(chartArea.left, y, 4, 0, 2 * Math.PI);
                        ctx.fillStyle = colors.blue;
                        ctx.fill();
                        ctx.strokeStyle = 'white';
                        ctx.lineWidth = 2;
                        ctx.stroke();
                    }
                    
                    if (y2Value !== null) {
                        ctx.beginPath();
                        ctx.arc(chartArea.right, y, 4, 0, 2 * Math.PI);
                        ctx.fillStyle = colors.orange;
                        ctx.fill();
                        ctx.strokeStyle = 'white';
                        ctx.lineWidth = 2;
                        ctx.stroke();
                    }
                    
                    ctx.restore();
                }
            };
            
            // Plugin untuk hover pada Legenda
            const directLegendHoverPlugin = {
                id: 'directLegendHover',
                
                beforeInit(chart) {
                    if (!chart.options.plugins) {
                        chart.options.plugins = {};
                    }
                    if (!chart.options.plugins.legend) {
                        chart.options.plugins.legend = {};
                    }
                    
                    chart.legendHover = {
                        originalStyles: chart.data.datasets.map(dataset => ({
                            backgroundColor: dataset.backgroundColor,
                            borderColor: dataset.borderColor,
                            borderWidth: dataset.borderWidth || 2,
                            pointBackgroundColor: dataset.pointBackgroundColor,
                            pointRadius: dataset.pointRadius || 4
                        }))
                    };
                    
                    chart.options.plugins.legend.onHover = function(event, legendItem) {
                        const hoveredIndex = legendItem.datasetIndex;
                        
                        chart.data.datasets.forEach((dataset, index) => {
                            const original = chart.legendHover.originalStyles[index];
                            
                            if (index === hoveredIndex) {
                                dataset.borderWidth = original.borderWidth * 2;
                                dataset.pointRadius = original.pointRadius * 1.2;
                            } else {
                                dataset.backgroundColor = addOpacity(original.backgroundColor, 0.2);
                                dataset.borderColor = addOpacity(original.borderColor, 0.3);
                                dataset.pointBackgroundColor = addOpacity(original.pointBackgroundColor, 0.2);
                                dataset.borderWidth = original.borderWidth * 0.7;
                                dataset.pointRadius = original.pointRadius * 0.8;
                            }
                        });
                        
                        chart.update('none');
                    };
                    
                    chart.options.plugins.legend.onLeave = function(event, legendItem) {
                        chart.data.datasets.forEach((dataset, index) => {
                            const original = chart.legendHover.originalStyles[index];
                            dataset.backgroundColor = original.backgroundColor;
                            dataset.borderColor = original.borderColor;
                            dataset.borderWidth = original.borderWidth;
                            dataset.pointBackgroundColor = original.pointBackgroundColor;
                            dataset.pointRadius = original.pointRadius;
                        });
                        
                        chart.update('none');
                    };
                    
                    function addOpacity(color, opacity) {
                        if (!color) return color;
                        if (typeof color === 'string') {
                            if (color.includes('rgba')) {
                                return color.replace(/[\d\.]+\)$/g, opacity + ')');
                            } else if (color.includes('rgb')) {
                                return color.replace('rgb', 'rgba').replace(')', `, ${opacity})`);
                            } else if (color.startsWith('#')) {
                                const hex = color.slice(1);
                                const r = parseInt(hex.substr(0, 2), 16);
                                const g = parseInt(hex.substr(2, 2), 16);
                                const b = parseInt(hex.substr(4, 2), 16);
                                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
                            }
                        }
                        return color;
                    }
                }
            };
            
            // GRAFIK 1: WAKTU PEMROSESAN ARTIKEL
            const ctx1 = document.getElementById('reviewTimelineChart');
            if (ctx1) {
                const allDays = [
                    ...daysToFirstDecision,
                    ...daysReview,
                    ...daysToAcceptance,
                    ...daysAcceptanceToPublication
                ];
                
                const reviewTimelineChart = new Chart(ctx1, {
                    type: 'line',
                    plugins: [zebraStripePlugin, fixedCompleteInteractionPlugin, directLegendHoverPlugin],
                    data: {
                        labels: years,
                        datasets: [
                            {
                                type: 'line',
                                label: 'Time to first decision',
                                data: daysToFirstDecision,
                                borderColor: colors.blue,
                                backgroundColor: 'transparent',
                                borderWidth: 1.5,
                                pointRadius: 2.5,
                                pointStyle: 'circle',
                                pointBackgroundColor: '#fff',
                                pointBorderColor: colors.blue,
                                pointBorderWidth: 1.5,
                                tension: 0.3,
                                fill: false,
                                yAxisID: 'y',
                                order: 1
                            },
                            {
                                type: 'line',
                                label: 'Review time',
                                data: daysReview,
                                borderColor: colors.red,
                                backgroundColor: 'transparent',
                                borderWidth: 1.5,
                                pointRadius: 3,
                                pointStyle: 'rectRot',
                                pointBackgroundColor: '#fff',
                                pointBorderColor: colors.red,
                                pointBorderWidth: 1.5,
                                tension: 0.3,
                                fill: false,
                                yAxisID: 'y',
                                order: 2
                            },
                           {
                               type: 'line',
                               label: 'Submission to acceptance',
                               data: daysToAcceptance,
                               borderColor: colors.purple,
                               backgroundColor: 'transparent',
                               borderWidth: 1.5,
                               pointRadius: 3,
                               pointStyle: 'rectRounded',
                               pointBackgroundColor: '#fff',
                               pointBorderColor: colors.purple,
                               pointBorderWidth: 1.5,
                               tension: 0.3,
                               fill: false,
                               yAxisID: 'y',
                               order: 3
                           },
                           {
                               type: 'line',
                               label: 'Acceptance to publication',
                               data: daysAcceptanceToPublication,
                               borderColor: colors.green,
                               backgroundColor: 'transparent',
                               borderWidth: 1.5,
                               pointRadius: 3,
                               pointStyle: 'rect',
                               pointBackgroundColor: '#fff',
                               pointBorderColor: colors.green,
                               pointBorderWidth: 1.5,
                               tension: 0.3,
                               fill: false,
                               yAxisID: 'y',
                               order: 4
                           },
                           {
                               type: 'bar',
                               label: 'Submission',
                               data: submissions,
                               backgroundColor: colors.orange,
                               borderColor: colors.orange,
                               borderWidth: 1,
                               borderRadius: 1,
                               barPercentage: 0.9,
                               yAxisID: 'y1',
                               order: 5
                           }
                       ]
                   },
                   options: {
                       responsive: true,
                       maintainAspectRatio: false,
                       interaction: {
                           mode: 'index',
                           intersect: false,
                       },
                       animation: {
                          duration: 200,
                       },
                       plugins: {
                           tooltip: {
                               mode: 'index',
                               intersect: false,
                               position: 'nearest',
                               caretPadding: 30,
                               backgroundColor: 'rgba(255, 255, 255, 0.95)',
                               titleColor: '#333',
                               titleFont: {
                                   size: 14,
                               },
                               bodyColor: '#333',
                               bodyFont: {
                                   size: 13,
                                   lineHeight: 1.5
                                },
                               borderColor: 'rgba(0, 0, 0, 0.1)',
                               borderWidth: 1,
                               padding: 15,
                               boxPadding: 7,
                               callbacks: {
                                   title: function(context) {
                                       return 'Tahun: ' + years[context[0].dataIndex];
                                   },
                                   label: function(context) {
                                       let label = context.dataset.label || '';
                                       if (label) {
                                           label += ': ';
                                       }
                                       if (context.parsed.y !== null) {
                                           if (label.includes('Submission')) {
                                               label += Math.ceil(context.parsed.y);
                                           } else {
                                               label += Math.ceil(context.parsed.y) + ' day';
                                           }
                                       }
                                       return label;
                                   }
                               }
                           },
                           legend: {
                               position: 'top',
                               align: 'center',
                               labels: {
                                   boxWidth: 16,
                                   boxHeight: 16,
                                   usePointStyle: true,
                                   padding: 20,
                                   font: {
                                       size: 13,
                                       weight: '600'
                                   },
                                   generateLabels: function(chart) {
                                       const datasets = chart.data.datasets;
                                       return datasets.map((dataset, i) => {
                                           const meta = chart.getDatasetMeta(i);
                                           let pointStyle;
                                           let strokeStyle = dataset.borderColor;
                                           let fillStyle = dataset.backgroundColor;
                                           
                                           if (dataset.type === 'bar') {
                                               pointStyle = 'rect';
                                               fillStyle = dataset.backgroundColor;
                                               strokeStyle = dataset.borderColor;
                                           } else if (dataset.type === 'line') {
                                               pointStyle = 'line';
                                               fillStyle = 'transparent';
                                               strokeStyle = dataset.borderColor;
                                           }
                                           
                                           return {
                                               text: dataset.label,
                                               fillStyle: fillStyle,
                                               strokeStyle: strokeStyle,
                                               lineWidth: 2,
                                               pointStyle: pointStyle,
                                               hidden: meta.hidden,
                                               datasetIndex: i
                                           };
                                       });
                                   }
                               }
                           }
                       },
                       scales: {
                           x: xAxisConfig,
                           y: createOptimalAxisConfig(
                               allDays, 
                               0, 
                               null, 
                               'Time (day)', 
                               '', 
                               'left'
                           ),
                           y1: createOptimalAxisConfig(
                               submissions,
                               0,
                               null,
                               'Submission',
                               '',
                               'right'
                           )
                       },
                       hover: {
                           mode: 'index',
                           intersect: false
                       }
                   }
               });
               
               // Simpan instance chart
               this.chartInstances.push(reviewTimelineChart);
           }
               
           // GRAFIK 2: PUBLIKASI DAN PENERIMAAN TAHUNAN
           const ctx2 = document.getElementById('publicationTimelineChart');
           if (ctx2) {
               const publicationChart = new Chart(ctx2, {
                   type: 'bar',
                   plugins: [zebraStripePlugin, fixedCompleteInteractionPlugin, directLegendHoverPlugin],
                   data: {
                       labels: years,
                       datasets: [
                           {
                               type: 'bar',
                               label: 'Submissions',
                               data: submissions,
                               backgroundColor: colors.orange,
                               borderColor: colors.orange,
                               borderWidth: 1,
                               borderRadius: 1,
                               barPercentage: 0.9,
                               order: 3
                           },
                           {
                               type: 'bar',
                               label: 'Published',
                               data: published,
                               backgroundColor: colors.blue,
                               borderColor: colors.blue,
                               borderWidth: 1,
                               borderRadius: 1,
                               barPercentage: 0.9,
                               order: 4
                           },
                           {
                               type: 'line',
                               label: 'Acceptance Rate (%)',
                               data: acceptRates,
                               borderColor: colors.green,
                               backgroundColor: 'transparent',
                               borderWidth: 1.5,
                               pointRadius: 2.5,
                               pointStyle: 'circle',
                               pointBackgroundColor: '#fff',
                               pointBorderColor: colors.green,
                               pointBorderWidth: 1.5,
                               tension: 0.3,
                               fill: false,
                               yAxisID: 'y',
                               order: 1
                           },
                           {
                               type: 'line',
                               label: 'Days to Publication',
                               data: daysToPublish,
                               borderColor: colors.purple,
                               backgroundColor: 'transparent',
                               borderWidth: 1.5,
                               pointRadius: 3,
                               pointStyle: 'rectRot',
                               pointBackgroundColor: '#fff',
                               pointBorderColor: colors.purple,
                               pointBorderWidth: 1.5,
                               tension: 0.3,
                               fill: false,
                               yAxisID: 'y1',
                               order: 2
                           }
                       ]
                   },
                   options: {
                       responsive: true,
                       maintainAspectRatio: false,
                       interaction: {
                           mode: 'index',
                           intersect: false,
                       },
                       animation: {
                          duration: 200,
                       },
                       plugins: {
                           tooltip: {
                               mode: 'index',
                               intersect: false,
                               position: 'nearest',
                               caretPadding: 30,
                               backgroundColor: 'rgba(255, 255, 255, 0.95)',
                               titleColor: '#333',
                               titleFont: {
                                   size: 14,
                               },
                               bodyColor: '#333',
                               borderColor: 'rgba(0, 0, 0, 0.1)',
                               borderWidth: 1,
                               padding: 15,
                               bodyFont: {
                                   size: 13,
                                   lineHeight: 1.5
                               },
                               boxPadding: 7,
                               callbacks: {
                                   title: function(context) {
                                       return 'Tahun: ' + years[context[0].dataIndex];
                                   },
                                   label: function(context) {
                                       let label = context.dataset.label || '';
                                       if (label) {
                                           label += ': ';
                                       }
                                       if (context.parsed.y !== null) {
                                           if (label.includes('(%)')) {
                                               label += Math.ceil(context.parsed.y) + '%';
                                           } else if (label.includes('(day)')) {
                                               label += Math.ceil(context.parsed.y) + ' day';
                                           } else {
                                               label += Math.ceil(context.parsed.y);
                                           }
                                       }
                                       return label;
                                   }
                               }
                           },
                           legend: {
                               position: 'top',
                               align: 'center',
                               labels: {
                                   boxWidth: 16,
                                   boxHeight: 16,
                                   usePointStyle: true,
                                   padding: 20,
                                   font: {
                                       size: 13,
                                       weight: '600'
                                   },
                                   generateLabels: function(chart) {
                                       const datasets = chart.data.datasets;
                                       return datasets.map((dataset, i) => {
                                           const meta = chart.getDatasetMeta(i);
                                           let pointStyle;
                                           let fillStyle;
                                           let strokeStyle;
                                           
                                           if (dataset.type === 'bar') {
                                               pointStyle = 'rect';
                                               fillStyle = dataset.backgroundColor;
                                               strokeStyle = dataset.borderColor;
                                           } else if (dataset.type === 'line') {
                                               pointStyle = 'line';
                                               fillStyle = 'transparent';
                                               strokeStyle = dataset.borderColor;
                                           }
                                           
                                           return {
                                               text: dataset.label,
                                               fillStyle: fillStyle,
                                               strokeStyle: strokeStyle,
                                               lineWidth: dataset.borderWidth || 2,
                                               pointStyle: pointStyle,
                                               hidden: meta.hidden,
                                               datasetIndex: i
                                           };
                                       });
                                   }
                               }
                           }
                       },
                       scales: {
                           x: xAxisConfig,
                           y: {
                               type: 'linear',
                               display: true,
                               position: 'left',
                               title: {
                                   display: true,
                                   text: 'Acceptance Rate (%)',
                                   font: {
                                       size: 14,
                                       weight: 'bold'
                                   },
                                   padding: {top: 5, bottom: 15}
                              },
                              beginAtZero: true,
                              min: 0,
                              max: 100,
                              ...createOptimalAxisConfig(acceptRates, 0, 100, 'Acceptance Rate (%)', '%', 'left')
                          },
                          y1: {
                              ...createOptimalAxisConfig(daysToPublish, 0, null, 'Days to Publication (day)', '', 'right')
                          }
                      },
                      hover: {
                          mode: 'index',
                          intersect: false
                      }
                  }
              });
              
              // Simpan instance chart
              this.chartInstances.push(publicationChart);
           }
              
           // GRAFIK 3: VIEWS & DOWNLOADS
           const ctx3 = document.getElementById('viewsDownloadsChart');
           if (ctx3) {
               const maxPublished = Math.max(...published, 1);
               const maxEngagement = Math.max(...[...views, ...downloads], 1);
               
               const viewsDownloadsChart = new Chart(ctx3, {
                   type: 'bar',
                   plugins: [zebraStripePlugin, fixedCompleteInteractionPlugin, directLegendHoverPlugin],
                   data: {
                       labels: years,
                       datasets: [
                           {
                               type: 'bar',
                               label: 'Article Published',
                               data: published,
                               backgroundColor: colors.blue,
                               borderColor: colors.blue,
                               borderWidth: 1,
                               borderRadius: 2,
                               barPercentage: 0.9,
                               yAxisID: 'y',
                               order: 3
                           },
                           {
                               type: 'line',
                               label: 'Abstract Views',
                               data: views,
                               borderColor: colors.orange,
                               backgroundColor: 'transparent',
                               borderWidth: 1.5,
                               pointRadius: 2.5,
                               pointStyle: 'circle',
                               pointBackgroundColor: '#fff',
                               pointBorderColor: colors.orange,
                               pointBorderWidth: 1.5,
                               tension: 0.3,
                               fill: false,
                               yAxisID: 'y1',
                               order: 1
                           },
                           {
                               type: 'line',
                               label: 'Article Downloads',
                               data: downloads,
                               borderColor: colors.red,
                               backgroundColor: 'transparent',
                               borderWidth: 1.5,
                               pointRadius: 3,
                               pointStyle: 'rect',
                               pointBackgroundColor: '#fff',
                               pointBorderColor: colors.red,
                               pointBorderWidth: 1.5,
                               tension: 0.3,
                               fill: false,
                               yAxisID: 'y1',
                               order: 2
                           }
                       ]
                   },
                   options: {
                       responsive: true,
                       maintainAspectRatio: false,
                       interaction: {
                           mode: 'index',
                           intersect: false,
                       },
                       animation: {
                          duration: 200,
                       },
                       plugins: {
                           tooltip: {
                               mode: 'index',
                               intersect: false,
                               position: 'nearest',
                               caretPadding: 30,
                               backgroundColor: 'rgba(255, 255, 255, 0.9)',
                               titleColor: '#333',
                               titleFont: {
                                   size: 14,
                               },
                               family: '"Elsevier Sans", Arial',
                               bodyColor: '#333',
                               borderColor: 'rgba(0, 0, 0, 0.1)',
                               borderWidth: 1,
                               padding: 15,
                               bodyFont: {
                                   size: 13,
                                   lineHeight: 1.5
                               },
                               boxPadding: 7,
                               callbacks: {
                                   title: function(context) {
                                       return 'Tahun: ' + years[context[0].dataIndex];
                                   },
                                   label: function(context) {
                                      let label = context.dataset.label || '';
                                      if (label) {
                                          label += ': ';
                                      }
                                      if (context.parsed.y !== null) {
                                          label += Math.ceil(context.parsed.y);
                                      }
                                      return label;
                                  }
                              }
                          },
                          legend: {
                              position: 'top',
                              align: 'center',
                              labels: {
                                  boxWidth: 16,
                                  boxHeight: 16,
                                  usePointStyle: true,
                                  padding: 20,
                                  font: {
                                      size: 13,
                                      weight: '600'
                                  },
                                  generateLabels: function(chart) {
                                      const datasets = chart.data.datasets;
                                      return datasets.map((dataset, i) => {
                                          const meta = chart.getDatasetMeta(i);
                                          let pointStyle;
                                          let fillStyle;
                                          let strokeStyle;
                                          
                                          if (dataset.type === 'bar') {
                                              pointStyle = 'rect';
                                              fillStyle = dataset.backgroundColor;
                                              strokeStyle = dataset.borderColor;
                                          } else if (dataset.type === 'line') {
                                              pointStyle = 'line';
                                              fillStyle = 'transparent';
                                              strokeStyle = dataset.borderColor;
                                          }
                                          
                                          return {
                                              text: dataset.label,
                                              fillStyle: fillStyle,
                                              strokeStyle: strokeStyle,
                                              lineWidth: dataset.borderWidth || 2,
                                              pointStyle: pointStyle,
                                              hidden: meta.hidden,
                                              datasetIndex: i
                                          };
                                      });
                                  }
                              }
                          }
                      },
                      scales: {
                          x: xAxisConfig,
                          y: createOptimalAxisConfig(published, 0, null, 'Article Published', '', 'left'),
                          y1: createOptimalAxisConfig(
                              [...views, ...downloads],
                              0, 
                              null, 
                              'Views & Downloads', 
                              '', 
                              'right'
                          )
                      },
                      hover: {
                          mode: 'index',
                          intersect: false
                      }
                  }
              });
              
              // Simpan instance chart
              this.chartInstances.push(viewsDownloadsChart);
           }
         
           // Setup timeout untuk debugging dan cleanup
           const debugTimeout = setTimeout(() => {
               this.debugAxisLabels(years);
               
               // Tambahkan info jika data views/downloads terlalu rendah
               const chart3 = Chart.getChart(document.getElementById('viewsDownloadsChart'));
               if (chart3) {
                   const maxViews = Math.max(...views, 0);
                   const maxDownloads = Math.max(...downloads, 0);
                   
                   if (maxViews < 5 && maxDownloads < 5) {
                       const infoContainer = document.createElement('div');
                       infoContainer.className = 'stats-info-message';
                       infoContainer.style.textAlign = 'center';
                       infoContainer.style.padding = '10px';
                       infoContainer.style.marginTop = '10px';
                       infoContainer.style.color = '#666';
                       infoContainer.style.fontSize = '12px';
                       infoContainer.style.fontStyle = 'italic';
                       infoContainer.textContent = 'INFO: Views and downloads data has low or null for some periods.';
                       
                       const chartContainer = document.getElementById('viewsDownloadsChart').parentNode;
                       chartContainer.appendChild(infoContainer);
                   }
               }
               
               // Hapus atribut data-json-path
               const statsContainer = document.getElementById('journalStatsCharts');
               if (statsContainer && statsContainer.hasAttribute('data-json-path')) {
                   statsContainer.removeAttribute('data-json-path');
                   console.log('[Wizdam Journal Stats]: CLEAR - Chart display succesfull!');
               }
           }, 1000);
           
           // Simpan timeout untuk cleanup
           this.timeouts.push(debugTimeout);
       },
       
       debugAxisLabels(years) {
           ['reviewTimelineChart', 'publicationTimelineChart', 'viewsDownloadsChart'].forEach(chartId => {
               const chart = Chart.getChart(document.getElementById(chartId));
               if (!chart) return;
               
               console.log(`[Wizdam Journal Stats]: Chart '${chartId}' X-axis labels:`, chart.data.labels);
               
               const xAxis = chart.scales.x;
               if (xAxis) {
                   const labelValues = xAxis.ticks.map(tick => tick.label || tick.value);
                   console.log(`[Wizdam Journal Stats]: Chart '${chartId}' displayed X labels:`, labelValues);
                   
                   const mismatch = labelValues.some((label, idx) => {
                       return years[idx] !== undefined && String(years[idx]) !== String(label);
                   });
                   
                   if (mismatch) {
                       console.warn(`[Wizdam Journal Stats]: Chart '${chartId}' has mismatched year labels!`);
                       
                       chart.options.scales.x.ticks.callback = function(value, index) {
                           return years[index];
                       };
                       
                       chart.update();
                   }
               }
           });
       },
       
       cleanup() {
           // Destroy all charts
           this.chartInstances.forEach(chart => {
               if (chart) {
                   // Cancel animation frames dari plugin
                   if (chart.dynamicLine && chart.dynamicLine.animationFrame) {
                       cancelAnimationFrame(chart.dynamicLine.animationFrame);
                   }
                   chart.destroy();
               }
           });
           this.chartInstances = [];
           
           // Clear all timeouts
           this.timeouts.forEach(timeout => clearTimeout(timeout));
           this.timeouts = [];
           
           // Clear animation frames
           this.animationFrames.forEach(frame => cancelAnimationFrame(frame));
           this.animationFrames = [];
           
           // Remove event listeners
           this.eventListeners.forEach(({ element, event, handler }) => {
               element.removeEventListener(event, handler);
           });
           this.eventListeners = [];
           
           // Reset initialized flag
           this.initialized = false;
           
           console.log('[Wizdam Journal Stats]: Cleanup completed');
       },
       
       // Helper method untuk menambah event listener yang tracked
       addEventListener(element, event, handler) {
           element.addEventListener(event, handler);
           this.eventListeners.push({ element, event, handler });
       }
   };
   
   // Safe initialization
   if (document.readyState === 'loading') {
       const initHandler = () => JournalStats.init();
       JournalStats.addEventListener(document, 'DOMContentLoaded', initHandler);
   } else {
       // DOM sudah loaded, langsung init
       JournalStats.init();
   }
   
   // Cleanup on page unload
   const unloadHandler = () => JournalStats.cleanup();
   JournalStats.addEventListener(window, 'beforeunload', unloadHandler);
   
   // Export untuk debugging only (optional)
   if (typeof window.JournalStats === 'undefined') {
       window.JournalStats = JournalStats;
   }
   
})(window, document);