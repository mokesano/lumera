/**
 * Style animasi sort artikel pencarian
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.1 (Fixed Null Safety)
 */
document.addEventListener('DOMContentLoaded', function() {
    
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

    function sortArticles(order) {
        const articleList = document.querySelector('.app-article-list-row');
        
        // PERBAIKAN PENTING: Jika tidak ada list artikel (misal di Homepage), berhenti.
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
                
                // Cek jika elemen tanggal ada untuk menghindari error
                if (!dateElemA || !dateElemB) return 0;

                const dateA = new Date(dateElemA.getAttribute('datetime'));
                const dateB = new Date(dateElemB.getAttribute('datetime'));
                return order === 'asc' ? dateA - dateB : dateB - dateA;
            });
        }

        animateListChanges(articleList, articles, sortedArticles);
    }

    function filterArticles(showOpenAccess) {
        const articleList = document.querySelector('.app-article-list-row');
        
        // PERBAIKAN PENTING: Cek eksistensi list artikel
        if (!articleList) return;

        const articles = Array.from(articleList.getElementsByClassName('app-article-list-row__item'));

        articles.forEach(article => {
            const metaItem = article.querySelector('.c-meta__item[data-test="nopdf-galley"]');
            if (showOpenAccess) {
                if (metaItem) {
                    article.style.display = '';
                    article.style.opacity = 0;
                    requestAnimationFrame(() => {
                        article.style.transition = 'opacity 500ms';
                        article.style.opacity = 1;
                    });
                }
            } else {
                if (metaItem) {
                    article.style.transition = 'opacity 500ms';
                    article.style.opacity = 0;
                    setTimeout(() => {
                        article.style.display = 'none';
                    }, 500);
                } else {
                    article.style.display = '';
                    article.style.opacity = 0;
                    requestAnimationFrame(() => {
                        article.style.transition = 'opacity 500ms';
                        article.style.opacity = 1;
                    });
                }
            }
        });
    }

    function updateCheckboxState() {
        // Array.from aman walaupun selector kosong (return array kosong)
        const articles = Array.from(document.querySelectorAll('.app-article-list-row__item .c-meta__item'));
        const resultsOnlyCheckbox = document.querySelector('#results-only-access-checkbox');

        // PERBAIKAN: Cek dulu apakah checkbox ada sebelum diproses
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
        
        // Perbaikan: Cek dulu apakah fieldset ada sebelum setAttribute
        if (sortByFieldset) {
            sortByFieldset.setAttribute('data-sort-by', value);
        }
    }

    function handleSearchInput(event) {
        const searchKeywords = event.target.value.trim();
        const facets = document.querySelectorAll('.c-facet.c-facet--small[data-test="search-filter-box"]');
        const advancedFilters = document.querySelectorAll('.app-search-adv-filters__filter');

        if (searchKeywords === "" || advancedFilters.length === 0) {
            facets.forEach(facet => {
                facet.style.display = 'none';
            });
        } else {
            facets.forEach(facet => {
                facet.style.display = '';
            });
        }
    }

    // --- EVENT LISTENERS (Sudah Aman) ---

    // 1. Sort by Date Descending
    const btnSortDesc = document.querySelector('label[for="sort-by-date_desc"]');
    if (btnSortDesc) {
        btnSortDesc.addEventListener('click', function() {
            const sortInput = document.querySelector('#sort-by-date_desc');
            if(sortInput) {
                sortArticles('desc');
                updateSortByAttribute(sortInput.value);
            }
        });
    }

    // 2. Sort by Date Ascending
    const btnSortAsc = document.querySelector('label[for="sort-by-date_asc"]');
    if (btnSortAsc) {
        btnSortAsc.addEventListener('click', function() {
            const sortInput = document.querySelector('#sort-by-date_asc');
            if(sortInput) {
                sortArticles('asc');
                updateSortByAttribute(sortInput.value);
            }
        });
    }

    // 3. Sort by Relevance
    const btnSortRel = document.querySelector('label[for="sort-by-relevance"]');
    if (btnSortRel) {
        btnSortRel.addEventListener('click', function() {
            const sortInput = document.querySelector('#sort-by-relevance');
            if(sortInput) {
                sortArticles('relevance');
                updateSortByAttribute(sortInput.value);
            }
        });
    }

    // 4. Checkbox Filter
    const resultsOnlyCheckbox = document.querySelector('#results-only-access-checkbox');
    if (resultsOnlyCheckbox) {
        resultsOnlyCheckbox.addEventListener('change', function() {
            filterArticles(this.checked);
        });
    }

    // 5. Search Input
    const searchInput = document.querySelector('#search-keywords');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
    }

    // --- EKSEKUSI AWAL (INITIALIZATION) ---
    
    // Perbaikan: Cek dulu apakah tombolnya ada sebelum dicentang
    const sortByRelevanceBtn = document.querySelector('#sort-by-relevance');
    if (sortByRelevanceBtn) {
        sortByRelevanceBtn.checked = true;
    }

    // PERBAIKAN FINAL: 
    // Jangan jalankan sortArticles jika kita tidak di halaman pencarian (list artikel tidak ada)
    const checkArticleList = document.querySelector('.app-article-list-row');
    if (checkArticleList) {
        sortArticles('relevance');
    }
    
    updateSortByAttribute('relevance');
    updateCheckboxState();
});