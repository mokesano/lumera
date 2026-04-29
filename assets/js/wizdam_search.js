/**
 * Main Script: Search, Sort, Filter, and Animation
 * Menggabungkan fitur sorting animasi dengan live search caching.
 * * Menerapkan Solusi Hybrid:
 * 1. Menghapus elemen UI jika tidak ada artikel saat load (Server empty).
 * 2. Menyembunyikan elemen UI jika tidak ada hasil saat pencarian (Live search empty).
 * * @author Rochmady and Wizdam Team
 * @version 1.2.0 (Consolidated Hybrid Solution)
 */
$(document).ready(function() {

    // =========================================================================
    // 1. HYBRID CHECK (DOM CLEANUP)
    // =========================================================================
    
    // Cek apakah ada artikel saat halaman pertama kali dimuat
    const $initialList = $('#search-article-list');
    const $initialItems = $initialList.find('.app-article-list-row__item');

    // Jika container tidak ada atau item kosong, hapus elemen UI sampah dan hentikan script.
    if ($initialList.length === 0 || $initialItems.length === 0) {
        // Hapus Facet dan Header dari DOM sepenuhnya
        $('.c-facet, .c-list-header').remove();
        console.log("[Wizdam Tools]: No articles found on load. UI elements removed.");
        return; // Hentikan eksekusi script di sini
    }

    // =========================================================================
    // 2. VARIABLE & CONFIGURATION
    // =========================================================================
    
    const searchCache = new Map();
    const CACHE_TIMEOUT = 600000; // 10 menit
    const DEBOUNCE_TIME = 300;

    // =========================================================================
    // 3. HELPER FUNCTIONS
    // =========================================================================

    /**
     * Fungsi debounce untuk menunda eksekusi
     * @param {Function} func 
     * @param {number} wait 
     */
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Menghapus cache secara berkala
     */
    function clearCacheInterval() {
        setInterval(() => {
            searchCache.clear();
            console.log("[Wizdam Tools]: Cache cleared");
        }, CACHE_TIMEOUT);
    }

    // =========================================================================
    // 4. VISUAL & ANIMATION LOGIC (From Code 1)
    // =========================================================================

    /**
     * Animasi perubahan daftar artikel (FLIP technique)
     * @param container 
     * @param oldItems 
     * @param newItems 
     */
    function animateListChanges(container, oldItems, newItems) {
        const oldRects = oldItems.map(item => item.getBoundingClientRect());
        const fragment = document.createDocumentFragment();
        newItems.forEach(item => fragment.appendChild(item));
        container.appendChild(fragment);
        const newRects = newItems.map(item => item.getBoundingClientRect());

        newItems.forEach((item, i) => {
            const oldRect = oldRects[i];
            const newRect = newRects[i];

            const deltaX = oldRect.left - newRect.left;
            const deltaY = oldRect.top - newRect.top;

            item.style.transition = 'none';
            item.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
            requestAnimationFrame(() => {
                item.style.transition = 'transform 500ms';
                item.style.transform = '';
            });
        });
    }

    /**
     * Sort artikel berdasarkan order (Relevance / Date)
     * @param {string} order 
     */
    function sortArticles(order) {
        const articleList = document.querySelector('.app-article-list-row');
        if (!articleList) return;

        const articles = Array.from(articleList.getElementsByClassName('app-article-list-row__item'));
        let sortedArticles;

        if (order === 'relevance') {
            sortedArticles = articles.sort((a, b) => {
                const titleA = a.querySelector('.c-card__title').textContent.toLowerCase();
                const titleB = b.querySelector('.c-card__title').textContent.toLowerCase();
                const summaryA = a.querySelector('.c-card__summary')?.textContent.toLowerCase() || '';
                const summaryB = b.querySelector('.c-card__summary')?.textContent.toLowerCase() || '';
                const sectionA = a.querySelector('.c-card__section')?.textContent.toLowerCase() || '';
                const sectionB = b.querySelector('.c-card__section')?.textContent.toLowerCase() || '';
                const authorA = a.querySelector('.c-author-list')?.textContent.toLowerCase() || '';
                const authorB = b.querySelector('.c-author-list')?.textContent.toLowerCase() || '';

                const relevanceA = titleA + summaryA + sectionA + authorA;
                const relevanceB = titleB + summaryB + sectionB + authorB;

                return relevanceA.localeCompare(relevanceB);
            });
        } else {
            sortedArticles = articles.sort((a, b) => {
                const dateElemA = a.querySelector('time.c-meta__item');
                const dateElemB = b.querySelector('time.c-meta__item');
                
                if (!dateElemA || !dateElemB) return 0;

                const dateA = new Date(dateElemA.getAttribute('datetime'));
                const dateB = new Date(dateElemB.getAttribute('datetime'));
                return order === 'asc' ? dateA - dateB : dateB - dateA;
            });
        }

        animateListChanges(articleList, articles, sortedArticles);
    }

    /**
     * Filter artikel berdasarkan akses terbuka (Checkbox)
     * @param {boolean} showOpenAccess 
     */
    function filterArticles(showOpenAccess) {
        const articleList = document.querySelector('.app-article-list-row');
        if (!articleList) return;

        const articles = Array.from(articleList.getElementsByClassName('app-article-list-row__item'));

        articles.forEach(article => {
            const metaItem = article.querySelector('.c-meta__item[data-test="nopdf-galley"]');
            
            // Logic animasi fade in/out
            const show = () => {
                article.style.display = '';
                article.style.opacity = 0;
                requestAnimationFrame(() => {
                    article.style.transition = 'opacity 500ms';
                    article.style.opacity = 1;
                });
            };
            const hide = () => {
                article.style.transition = 'opacity 500ms';
                article.style.opacity = 0;
                setTimeout(() => { article.style.display = 'none'; }, 500);
            };

            if (showOpenAccess) {
                if (metaItem) show();
                // Note: Jika showOpenAccess true, artikel tertutup tidak di-hide di sini
                // karena logika aslinya hanya menghandle yang "nopdf-galley".
                // Sesuaikan jika perlu else { hide() }
            } else {
                if (metaItem) hide();
                else show();
            }
        });
    }

    function updateCheckboxState() {
        const articles = Array.from(document.querySelectorAll('.app-article-list-row__item .c-meta__item'));
        const resultsOnlyCheckbox = document.querySelector('#results-only-access-checkbox');

        if (resultsOnlyCheckbox) {
            const hasNoPdfGalley = articles.some(article => article.getAttribute('data-test') === 'nopdf-galley');
            resultsOnlyCheckbox.disabled = !hasNoPdfGalley;

            if (hasNoPdfGalley) {
                resultsOnlyCheckbox.checked = true;
                filterArticles(true);
            } else {
                resultsOnlyCheckbox.checked = false;
                filterArticles(false);
            }
        }
    }

    function updateSortByAttribute(value) {
        const sortByFieldset = document.querySelector('.c-sort-by');
        if (sortByFieldset) {
            sortByFieldset.setAttribute('data-sort-by', value);
        }
    }

    // =========================================================================
    // 5. LIVE SEARCH LOGIC (From Code 2)
    // =========================================================================

    /**
     * Memproses pencarian dengan caching.
     * Menggunakan metode HIDE (bukan remove) agar bisa dikembalikan.
     */
    function processSearch() {
        const searchInput = $('#search-keywords');
        if (searchInput.length === 0) return;
        
        const keyword = searchInput.val().toLowerCase();

        // Cek Cache
        if (searchCache.has(keyword)) {
            console.log("Cache hit");
            updateSearchResults(searchCache.get(keyword));
            return;
        }

        const articles = $('#search-article-list .app-article-list-row__item');
        let hasResults = false;

        // Jika keyword kosong, reset tampilan
        if (keyword === "") {
            articles.show();
            // Tampilkan kembali UI Facet & Header (Remove class Hide)
            $('.c-facet, .c-list-header').removeClass('u-hide');
            return;
        }

        // Filter artikel
        articles.each(function() {
            const row = $(this);
            const articleText = row.text().toLowerCase();
            if (articleText.includes(keyword)) {
                row.show();
                hasResults = true;
            } else {
                row.hide();
            }
        });

        // Simpan ke cache
        searchCache.set(keyword, hasResults);
        updateSearchResults(hasResults);
    }

    /**
     * Update UI Facet & Header berdasarkan hasil pencarian.
     * Menggunakan class 'u-hide' untuk menyembunyikan sementara.
     * @param {boolean} hasResults 
     */
    function updateSearchResults(hasResults) {
        if (!hasResults) {
            $('.c-facet, .c-list-header').addClass('u-hide');
        } else {
            $('.c-facet, .c-list-header').removeClass('u-hide');
        }
    }

    /**
     * Update dynamic journal list (Facet Sidebar)
     */
    function updateJournalFacet() {
        const journalsIndex = {};
        
        // Loop hanya pada item yang visible (opsional, tergantung kebutuhan)
        // Di sini kita loop semua item yang ada di DOM (termasuk yang di-hide search)
        // Agar facet tetap konsisten dengan data yang dimuat.
        $('.app-article-list-row__item').each(function() {
            const journalTitleElement = $(this).find('.c-meta__item[data-test="journal-title-and-link"]');
            if (journalTitleElement.length > 0) {
                const journalTitle = journalTitleElement.text().trim();
                journalsIndex[journalTitle] = (journalsIndex[journalTitle] || 0) + 1;
            }
        });

        const journalTarget = $('#journal-target .c-facet-expander__list');
        journalTarget.empty();

        $.each(journalsIndex, function(title, count) {
            const idSafe = 'journal-' + title.replace(/\s+/g, '-').toLowerCase();
            const listItem = $('<li>', { 'class': 'c-facet-expander__list-item' });
            
            const checkbox = $('<input>', {
                'type': 'checkbox', 'name': 'journal', 'id': idSafe,
                'value': title, 'data-action': 'submit', 'checked': 'checked'
            });
            
            const label = $('<label>', { 'class': 'c-facet-expander__link', 'for': idSafe });
            label.append($('<span>').text(`${title} (${count})`));
            
            listItem.append(checkbox).append(label);
            journalTarget.append(listItem);
        });
    }

    // =========================================================================
    // 6. INITIALIZATION & EVENT LISTENERS
    // =========================================================================

    function init() {
        // A. Event Listeners untuk Sorting
        const sortMappings = [
            { btn: 'label[for="sort-by-date_desc"]', input: '#sort-by-date_desc', type: 'desc' },
            { btn: 'label[for="sort-by-date_asc"]', input: '#sort-by-date_asc', type: 'asc' },
            { btn: 'label[for="sort-by-relevance"]', input: '#sort-by-relevance', type: 'relevance' }
        ];

        sortMappings.forEach(map => {
            const btn = document.querySelector(map.btn);
            if (btn) {
                btn.addEventListener('click', function() {
                    const sortInput = document.querySelector(map.input);
                    if (sortInput) {
                        sortArticles(map.type);
                        updateSortByAttribute(sortInput.value);
                    }
                });
            }
        });

        // B. Event Listener untuk Checkbox Filter
        const resultsOnlyCheckbox = document.querySelector('#results-only-access-checkbox');
        if (resultsOnlyCheckbox) {
            resultsOnlyCheckbox.addEventListener('change', function() {
                filterArticles(this.checked);
            });
        }

        // C. Event Listener untuk Search (Live)
        $('#search-keywords').on('input', debounce(processSearch, DEBOUNCE_TIME));

        // D. Observer untuk update Journal Facet saat DOM berubah
        $('#search-article-list').on('DOMNodeInserted', '.app-article-list-row', function() {
            updateJournalFacet();
        });

        // E. Setup Awal
        const sortByRelevanceBtn = document.querySelector('#sort-by-relevance');
        if (sortByRelevanceBtn) sortByRelevanceBtn.checked = true;

        // Jalankan sort default
        sortArticles('relevance');
        updateSortByAttribute('relevance');
        updateCheckboxState();
        
        // Init Journal Facet
        updateJournalFacet();
        
        // Jalankan cache cleaner
        clearCacheInterval();
    }

    // Jalankan semua fungsi
    init();
});