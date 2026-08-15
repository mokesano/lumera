/**
 * @file assets/js/LumeraCitedby.js
 * 
 * Copyright (c) 2017-2026 Sangia Code Lumera 
 * Copyright (c) 2024-2026 Rochmady and Development Team
 * Distributed under the GNU GPL v3.
 * 
 * @brief Pengaturan tampilan kutipan artikel yang sudah disediakan backend.
 *        - Menampilkan animasi loading (spinner) saat halaman dimuat
 *        - Menyembunyikan panel jika tidak ada kutipan
 *        - Menampilkan tombol "Show more" jika jumlah kutipan > 3
 *        - Tidak melakukan fetch API atau injeksi konten
 * 
 * @author Rochmady and Team
 * @version 2.0.0
 */
(function() {
    // [LUMERA] Jadwal pemutakhiran terjadwal berikutnya, dikirim SERVER lewat
    // atribut data pada <section>. Tidak ada pemanggilan jaringan di sini.
    let nextScheduledUpdate = 0;

    const nextUpdateText = () => {
        if (nextScheduledUpdate > 0) {
            return 'Citations are updated on a weekly schedule. Next update: '
                 + formatTimestamp(nextScheduledUpdate);
        }
        return 'Citations are updated on a weekly schedule by the server.';
    };

    // Variabel untuk mengelola refresh
    const REFRESH_COOLDOWN_HOURS = 12; // Minimum 12 jam antara refresh
    let lastDataTimestamp = 0;         // Timestamp dari pembaruan data terakhir

    // Minimal CSS dengan tooltip modern yang dipastikan berfungsi
    const addStyles = () => {
        if (!document.getElementById('citation-minimal-styles')) {
            const style = document.createElement('style');
            style.id = 'citation-minimal-styles';
            style.textContent = `
                .citation-refresh-overlay {position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.5);display:flex;justify-content:center;align-items:center;z-index:10}
                .citation-refresh-spinner {width:40px;height:40px;background:url('/public/assets/icons/lum-loading.svg') center/contain no-repeat}
                .lum-cite-pending {opacity:0;transform:translateY(6px)}
                .lum-cite-reveal {opacity:1;transform:translateY(0);transition:opacity .35s ease,transform .35s ease}
                .citation-blur {filter:blur(1px)}
                @keyframes spin {to{transform:rotate(360deg)}}
                .refresh-icon {margin-left:4px;vertical-align:middle}

                /* Improved Tooltip Styles */
                .tooltip-wrap {position:relative;display:inline-block}
                .citation-tooltip {
                    position:absolute;
                    bottom:130%;
                    right:0;
                    width:240px;
                    background:#e6f2ff;
                    font-family:Elsevier Sans,Gulliver,Nexus Sans,Arial,sans-serif !important;
                    font-size:1.1em;
                    line-height:1.5;
                    border-radius:10px;
                    padding:8px 16px;
                    text-align:center;
                    z-index:1;
                    box-shadow:0 2px 10px rgba(0,0,0,0.1);
                    opacity:0;
                    visibility:hidden;
                    transition:all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    pointer-events:none;
                    transform:translateY(10px);
                }
                .citation-tooltip::after {
                    content:'';
                    position:absolute;
                    width:0;
                    height:0;
                    border-left:6px solid transparent;
                    border-right:6px solid transparent;
                    border-top:9px solid #e6f2ff;
                    bottom:-6px;
                    right:20px;
                }
                .tooltip-wrap:hover .citation-tooltip {
                    opacity:1;
                    visibility:visible;
                    transform:translateY(0);
                }
                .no-citations-hide {display:none!important}

                /* Disabled button styling */
                .button-disabled {
                    opacity: 0.5 !important;
                    cursor: not-allowed !important;
                    pointer-events: none !important;
                }

                /* New styling for citation info container */
                .citing-info-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    width: 100%;
                }

                .citing-info {
                    flex: 1;
                }

                .refresh-container {
                    margin-left: auto;
                }
            `;
            document.head.appendChild(style);
        }
    };

    // Efektif menangani overlay loading
    const showLoading = (container) => {
        container.style.position = 'relative';
        const list = container.querySelector('ul.citedby_crossref');
        if (list) list.classList.add('citation-blur');

        const oldOverlay = container.querySelector('.citation-refresh-overlay');
        if (oldOverlay) oldOverlay.remove();

        const overlay = document.createElement('div');
        overlay.className = 'citation-refresh-overlay';
        overlay.innerHTML = '<div class="citation-refresh-spinner"></div>';
        container.appendChild(overlay);
    };

    // Hapus loading overlay
    const hideLoading = (container) => {
        const overlay = container.querySelector('.citation-refresh-overlay');
        if (overlay) overlay.remove();
        const list = container.querySelector('ul.citedby_crossref');
        if (list) list.classList.remove('citation-blur');
    };

    // Fix HTML dalam judul
    const fixHtmlTitles = () => {
        document.querySelectorAll('.anchor-text span').forEach(span => {
            const text = span.textContent;
            if (text && text.includes('<') && text.includes('>')) {
                span.innerHTML = text;
            }
        });
    };

    // Fungsi untuk memeriksa apakah refresh diperbolehkan berdasarkan timestamp terakhir
    const canRefresh = () => {
        if (lastDataTimestamp === 0) return true; // Belum pernah refresh
        const now = Date.now();
        const hoursSinceLastUpdate = (now - lastDataTimestamp) / (1000 * 60 * 60);
        return hoursSinceLastUpdate >= REFRESH_COOLDOWN_HOURS;
    };

    // Fungsi untuk mendapatkan waktu tunggu yang tersisa
    const getCooldownTimeRemaining = () => {
        if (lastDataTimestamp === 0) return 0;
        const now = Date.now();
        const millisSinceLastUpdate = now - lastDataTimestamp;
        const millisToWait = (REFRESH_COOLDOWN_HOURS * 60 * 60 * 1000) - millisSinceLastUpdate;
        if (millisToWait <= 0) return 0;
        const hours = Math.floor(millisToWait / (1000 * 60 * 60));
        const minutes = Math.floor((millisToWait % (1000 * 60 * 60)) / (1000 * 60));
        if (hours > 0) {
            return `${hours}h ${minutes}m`;
        } else {
            return `${minutes}m`;
        }
    };

    // Template untuk tombol refresh dengan tooltip yang dipastikan berfungsi dan tidak terpotong
    const refreshButtonTemplate = (sourcesInfo, canRefreshNow) => {
        const tooltipText = !canRefreshNow
            ? `Next refresh available in ${getCooldownTimeRemaining()}`
            : (sourcesInfo || nextUpdateText());

        const disabledClass = !canRefreshNow ? 'button-disabled' : '';

        return `
            <div class="tooltip-wrap">
                <button class="anchor button-link refresh-citations-button button-link-secondary ${disabledClass}" type="button" ${!canRefreshNow ? 'disabled' : ''}>
                    <span class="button-link-text-container">
                        <span class="anchor-text button-link-text">Refresh</span>
                    </span>
                    <svg class="refresh-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 4v6h-6"></path>
                        <path d="M1 20v-6h6"></path>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"></path>
                        <path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"></path>
                    </svg>
                </button>
                <div class="citation-tooltip">${tooltipText}</div>
            </div>
        `;
    };

    // Fungsi untuk menghapus panel jika tidak ada kutipan
    const hideCitationPanelIfEmpty = (citationCount) => {
        const panels = document.querySelectorAll('.SidePanel.doi-cited');
        if (panels.length && !citationCount) {
            panels.forEach(panel => {
                panel.classList.add('no-citations-hide');
            });
            return true; // Panel disembunyikan
        }
        return false; // Panel tetap ditampilkan
    };

    // Helper untuk membuat tooltip sumber kutipan yang ditampilkan saja
    const formatSourcesInfo = (sources) => {
        if (!sources) return 'Refresh citations';

        const sourceParts = [];

        if (sources.opencitations_count > 0) {
            sourceParts.push(`OpenCitations (${sources.opencitations_count})`);
        }
        if (sources.crossref_count > 0) {
            sourceParts.push(`CrossRef (${sources.crossref_count})`);
        }
        if (sources.openalex_count > 0) {
            sourceParts.push(`OpenAlex (${sources.openalex_count})`);
        }
        if (sources.semanticscholar_count > 0) {
            sourceParts.push(`Semantic Scholar (${sources.semanticscholar_count})`);
        }
        if (sources.dimensions_count > 0) {
            sourceParts.push(`Dimensions (${sources.dimensions_count})`);
        }

        if (sourceParts.length === 0) {
            return `Sources: OpenCitations (0), CrossRef (0), OpenAlex (0), Semantic Scholar (0), Dimensions (0)`;
        }

        return `Sources: ${sourceParts.join(', ')}`;
    };

    // Format timestamp UNIX ke tanggal yang dapat dibaca
    const formatTimestamp = (timestamp) => {
        if (!timestamp) return 'Unknown date';
        try {
            const timestampNum = typeof timestamp === 'string' ? parseInt(timestamp) : timestamp;
            const date = new Date(timestampNum.toString().length <= 10 ? timestampNum * 1000 : timestampNum);
            if (isNaN(date.getTime())) return 'Invalid date';

            const day = date.getDate();
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${day} ${month} ${year}, ${hours}:${minutes}`;
        } catch (e) {
            console.error('[Wizdam Log] Error formatting timestamp:', e);
            return 'Error date format';
        }
    };

    // Format string tanggal ISO
    const formatISODate = (dateStr) => {
        if (!dateStr) return 'Unknown date';
        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return 'Invalid date';

            const day = date.getDate();
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${day} ${month} ${year}, ${hours}:${minutes}`;
        } catch (e) {
            console.error('[Wizdam Log] Error formatting ISO date:', e);
            return 'Error date format';
        }
    };

    // Ekstrak dan simpan timestamp dari respons
    const extractAndSaveTimestamp = (response) => {
        try {
            if (response.data && response.data.timestamp) {
                if (typeof response.data.timestamp === 'number') {
                    lastDataTimestamp = response.data.timestamp * 1000;
                    return lastDataTimestamp;
                }
            }
            if (response.last_updated) {
                const date = new Date(response.last_updated);
                if (!isNaN(date.getTime())) {
                    lastDataTimestamp = date.getTime();
                    return lastDataTimestamp;
                }
            }
            console.log('[Wizdam Log] No valid timestamp found in API response, using current time');
            lastDataTimestamp = Date.now();
            return lastDataTimestamp;
        } catch (e) {
            console.error('[Wizdam Log] Error extracting timestamp:', e);
            lastDataTimestamp = Date.now();
            return lastDataTimestamp;
        }
    };

    // Cek apakah jurnal berada dalam daftar yang diizinkan (tombol PDF)
    const isAllowedJournal = (journalName) => {
        if (!journalName) return false;

        const currentJournalMeta = document.querySelector('meta[name="citation_journal_title"]');
        const currentJournal = currentJournalMeta ? currentJournalMeta.getAttribute('content') : '';

        const allowedJournals = [
            'Jurnal Akuatiklestari',
            'Agrikan Jurnal Agribisnis Perikanan',
            'Agrikan: Jurnal Agribisnis Perikanan',
            'Jurnal Ilmu dan Teknologi Kelautan Tropis'
        ];

        if (currentJournal && journalName.trim().toLowerCase() === currentJournal.trim().toLowerCase()) {
            return true;
        }

        return allowedJournals.some(journal =>
            journalName.trim().toLowerCase() === journal.trim().toLowerCase()
        );
    };

    /**
     * [WIZDAM v4.0] Terapkan aturan tombol PDF pada markup yang SUDAH
     * dirender server. Dulu keputusan ini diambil saat membangun HTML dari
     * respons AJAX; kini tombolnya sudah ada di DOM, jadi yang dilakukan
     * adalah MENGHAPUS tombol untuk jurnal di luar daftar yang diizinkan --
     * perilaku akhirnya sama persis.
     */
    const applyPdfButtonPolicy = (scope) => {
        (scope || document).querySelectorAll('.SidePanelItem.article-citing .buttons').forEach(box => {
            const link = box.querySelector('a[data-citation-container]');
            const container = link ? link.getAttribute('data-citation-container') : '';
            if (!isAllowedJournal(container)) box.remove();
        });
    };

    /**
     * [WIZDAM v4.0] Bangun blok "Updated: ..." + tombol Refresh, dan pasang
     * toggle show/hide. Isi & perilakunya sama dengan versi AJAX, bedanya
     * data awal diambil dari atribut data pada <section> (hasil render
     * server), bukan dari respons fetch.
     */
    const buildFooter = (citingContainer, sources, formattedDate) => {
        const old = citingContainer.querySelector('.citing-info-container');
        if (old) old.remove();
        const oldToggle = citingContainer.querySelector('.more-citedby-button');
        if (oldToggle) oldToggle.remove();

        const sourcesInfo = formatSourcesInfo(sources);
        const refreshAllowed = canRefresh();

        const infoContainer = document.createElement('div');
        infoContainer.className = 'citing-info-container u-margin-m-top';

        const infoDiv = document.createElement('div');
        infoDiv.id = 'citing-info';
        infoDiv.className = 'citing-info';
        infoDiv.innerHTML = `<span class="update-info">Updated: ${formattedDate}</span>`;

        const refreshContainer = document.createElement('div');
        refreshContainer.className = 'refresh-container';
        refreshContainer.innerHTML = refreshButtonTemplate(sourcesInfo, refreshAllowed);

        infoContainer.appendChild(infoDiv);
        infoContainer.appendChild(refreshContainer);
        citingContainer.appendChild(infoContainer);

        // Show/hide logic - selalu tampilkan 3 teratas, sembunyikan sisanya
        const list = citingContainer.querySelector('ul.citedby_crossref');
        const items = list ? Array.from(list.querySelectorAll('li')) : [];
        if (items.length > 3) {
            const hiddenCount = items.length - 3;
            const showText = hiddenCount === 1 ? `Show ${hiddenCount} more article` : `Show ${hiddenCount} more articles`;
            const hideText = hiddenCount === 1 ? `Hide ${hiddenCount} article` : `Hide ${hiddenCount} more articles`;

            const toggleButton = document.createElement('button');
            toggleButton.className = 'anchor button-link more-citedby-button u-margin-s-top button-link-primary button-link-icon-right';
            toggleButton.type = 'button';
            toggleButton.innerHTML = `
                <span class="button-link-text-container u-mr-8">
                    <span class="anchor-text button-link-text">${showText}</span>
                </span>
                <svg focusable="false" viewBox="0 0 92 128" height="20" class="icon-navigate icon-navigate-down">
                    <path d="M1 51l7-7 38 38 38-38 7 7-45 45z"></path>
                </svg>
            `;
            citingContainer.appendChild(toggleButton);

            items.slice(3).forEach(li => { li.style.display = 'none'; });

            let isExpanded = false;
            toggleButton.addEventListener('click', () => {
                if (!isExpanded) {
                    items.forEach(li => { li.style.display = ''; });
                    toggleButton.querySelector('.button-link-text').textContent = hideText;
                    toggleButton.querySelector('svg').classList.add('u-flip-vertically');
                    isExpanded = true;
                } else {
                    items.slice(3).forEach(li => { li.style.display = 'none'; });
                    toggleButton.querySelector('.button-link-text').textContent = showText;
                    toggleButton.querySelector('svg').classList.remove('u-flip-vertically');
                    isExpanded = false;
                }
            });
        }

        // Event handler untuk tombol refresh
        const refreshButton = refreshContainer.querySelector('.refresh-citations-button');
        if (refreshButton) {
            refreshButton.addEventListener('click', function(e) {
                e.preventDefault();
                const tooltip = refreshContainer.querySelector('.citation-tooltip');
                if (tooltip) tooltip.textContent = nextUpdateText();
            });
        }

        // Timer untuk memperbarui tampilan tombol refresh selama cooldown
        if (!refreshAllowed) {
            const updateRefreshButtonState = () => {
                const rc = document.querySelector('.refresh-container');
                if (!rc) { clearInterval(timerInterval); return; }
                const button = rc.querySelector('.refresh-citations-button');
                const tooltip = rc.querySelector('.citation-tooltip');
                if (canRefresh()) {
                    if (button && tooltip) {
                        button.disabled = false;
                        button.classList.remove('button-disabled');
                        tooltip.textContent = sourcesInfo || 'Refresh citations';
                    }
                    clearInterval(timerInterval);
                } else if (tooltip) {
                    tooltip.textContent = `Next refresh available in ${getCooldownTimeRemaining()}`;
                }
            };
            const timerInterval = setInterval(updateRefreshButtonState, 60000);
            window.addEventListener('beforeunload', () => clearInterval(timerInterval));
        }
    };

    /** Bangun ulang <ul> daftar kutipan setelah refresh. */
    const renderList = (citingContainer, articles) => {
        const list = citingContainer.querySelector('ul.citedby_crossref');
        if (!list) return;

        const sorted = [...articles].sort((a, b) => (parseInt(b.year) || 0) - (parseInt(a.year) || 0));
        const toDisplay = sorted.slice(0, 7);

        list.innerHTML = '';
        toDisplay.forEach((article, index) => {
            let authorsHtml = 'Authors not available';
            if (article.authors && article.authors.length > 0) {
                const parts = [];
                article.authors.forEach(a => {
                    if (a.given && a.family) parts.push(`<span class="given-name">${a.given}</span> <span class="family-name">${a.family}</span>`);
                    else if (a.family) parts.push(`<span class="family-name">${a.family}</span>`);
                });
                if (parts.length) authorsHtml = parts.join(', ');
            }

            let articleUrl = '#';
            if (article.doi) articleUrl = `https://doi.org/${article.doi}`;
            else if (article.url) {
                articleUrl = article.url
                    .replace('/article/download/', '/article/view/')
                    .replace('/download/', '/view/');
            }

            let pdfUrl = article.pdf_url || '';
            if (!pdfUrl && article.is_pdf) pdfUrl = articleUrl;
            if (pdfUrl) {
                pdfUrl = pdfUrl.replace('/article/download/', '/article/view/').replace('/download/', '/view/');
                if (pdfUrl.match(/\/(index\.php\/[^\/]+\/article\/download\/\d+\/\d+)/)) {
                    pdfUrl = pdfUrl.replace('/download/', '/viewFile/');
                }
            }

            const src = [];
            if (article.container) src.push(`<span class="journal">${article.container}, </span>`);
            if (article.year) src.push(`<span class="year">${article.year}, </span>`);
            const ed = [];
            if (article.volume) ed.push(`Volume ${article.volume}`);
            if (article.issue) ed.push(`Issue ${article.issue}`);
            if (article.page) ed.push(`p: ${article.page}`);
            if (ed.length) src.push(`<span class="edition">${ed.join(', ')}</span>`);

            const li = document.createElement('li');
            li.className = 'SidePanelItem article-citing';
            li.innerHTML = `
                <div class="sub-heading">
                    <h3 class="related-content-panel-list-entry-outline-padding text-s u-fonts-serif" id="citing-articles-article${index + 1}-title">
                        <a class="anchor u-clamp-2-lines anchor-primary" href="${articleUrl}" target="_blank" rel="nofollow noopener">
                            <span class="anchor-text-container"><span class="anchor-text"><span></span></span></span>
                        </a>
                    </h3>
                    ${src.length ? `<div class="article-source ellipsis u-clr-grey6"><div class="source">${src.join('')}</div></div>` : ''}
                    <div class="authors ellipsis">${authorsHtml}</div>
                </div>
                ${pdfUrl ? `<div class="buttons"><a class="anchor anchor-primary anchor-icon-left anchor-with-icon" href="${pdfUrl}" target="_blank" rel="nofollow noopener" data-citation-container="${article.container || ''}"><svg focusable="false" viewBox="0 0 35 32" height="20" width="20" class="icon icon-pdf-multicolor"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" fill="#da0000"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg><span class="anchor-text-container"><span class="anchor-text">View PDF</span></span></a></div>` : ''}
            `;
            // Judul disisipkan sebagai HTML supaya tag <i>/<sub>/<sup> tetap hidup
            const titleSpan = li.querySelector('.anchor-text span');
            if (titleSpan) titleSpan.innerHTML = article.title || '';
            const anchor = li.querySelector('a.anchor');
            if (anchor) anchor.setAttribute('title', (article.title || '').replace(/<[^>]*>/g, ''));

            list.appendChild(li);
        });
    };

    /**
     * [LUMERA] Tampilkan kutipan SATU PER SATU setelah seleksi tombol PDF.
     * Panel dirender server dengan u-js-hide, jadi pengguna tidak pernah
     * melihat tombol PDF untuk jurnal di luar daftar yang diizinkan.
     */
    const revealSequentially = (citingContainer, section) => {
        const items = Array.from(citingContainer.querySelectorAll('li.lum-cite-pending'));
        items.forEach(li => {
            const box = li.querySelector('.buttons');
            if (!box) return;
            const link = box.querySelector('a[data-citation-container]');
            const container = link ? link.getAttribute('data-citation-container') : '';
            if (!isAllowedJournal(container)) box.remove();
        });
        section.classList.remove('u-js-hide');
        showLoading(citingContainer);
        let i = 0;
        const step = () => {
            if (i >= items.length) {
                hideLoading(citingContainer);
                items.slice(3).forEach(li => { li.style.display = 'none'; });
                return;
            }
            const li = items[i++];
            li.classList.remove('lum-cite-pending');
            li.classList.add('lum-cite-reveal');
            setTimeout(step, 90);
        };
        setTimeout(step, 150);
    };

    // Inisialisasi: bekerja pada markup yang SUDAH dirender server
    const init = () => {
        addStyles();

        const section = document.querySelector('.SidePanel.doi-cited');
        const citingContainer = document.getElementById('citing-articles');

        // Panel tidak dirender server (tidak ada kutipan) -- tidak ada yang perlu dikerjakan.
        if (!section || !citingContainer) return;

        // Ambil metadata awal dari atribut data (pengganti respons AJAX awal)
        const ts = parseInt(section.getAttribute('data-citation-timestamp'), 10) || 0;
        const nu = parseInt(section.getAttribute('data-citation-next-update'), 10) || 0;
        nextScheduledUpdate = nu > 0 && nu.toString().length <= 10 ? nu : nu;
        if (ts > 0) lastDataTimestamp = ts.toString().length <= 10 ? ts * 1000 : ts;

        let sources = null;
        const rawSources = section.getAttribute('data-citation-sources');
        if (rawSources) {
            try { sources = JSON.parse(rawSources); } catch (e) { sources = null; }
        }

        // Pengaman: kalau ternyata daftar kosong, sembunyikan panel (perilaku asli)
        const count = citingContainer.querySelectorAll('ul.citedby_crossref li').length;
        if (hideCitationPanelIfEmpty(count)) return;

        revealSequentially(citingContainer, section);
        buildFooter(citingContainer, sources, ts > 0 ? formatTimestamp(ts) : 'Unknown date');
        setTimeout(fixHtmlTitles, 100);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();