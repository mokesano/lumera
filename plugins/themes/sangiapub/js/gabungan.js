jQuery(document).ready(function($) {
    var $menuToggle = $(".mtoggle");
    var $menu = $(".menu");
    var $skipToContent = $("#skip-to-content");
    var $content = $("#content");

    if ($menuToggle.length && $menu.length) {
        $menuToggle.click(function() {
            $menu.slideToggle(500);
        });
    }

    if ($skipToContent.length && $content.length) {
        $skipToContent.click(function() {
            $content.focus();
        });
    }
});

// Style menu dropdown
document.addEventListener('DOMContentLoaded', () => {
    const details1 = document.querySelector('details.Details-2932877531');
    const details2 = document.querySelector('details.Details-3185356244');

    function toggleDetails(detailsToOpen, detailsToClose) {
        if (detailsToOpen.hasAttribute('open')) {
            detailsToOpen.removeAttribute('open');
        } else {
            detailsToOpen.setAttribute('open', '');
            detailsToClose.removeAttribute('open');
        }
    }

    // Initial sync
    if (details1.hasAttribute('open')) {
        details2.removeAttribute('open');
    } else if (details2.hasAttribute('open')) {
        details1.removeAttribute('open');
    }

    details1.addEventListener('click', (event) => {
        event.preventDefault();
        toggleDetails(details1, details2);
    });

    details2.addEventListener('click', (event) => {
        event.preventDefault();
        toggleDetails(details2, details1);
    });
});

// dropdown-menu.js
document.addEventListener('DOMContentLoaded', function() {
    const accountWidget = document.querySelector('.c-header__item--snid-account-widget');
    const accountNav = document.querySelector('.c-account-nav');
    const myAccount = document.getElementById('my-account');
    const accountNavMenu = document.getElementById('account-nav-menu');
    const searchMenu = document.getElementById('search-menu');
    const searchLink = document.querySelector('.c-header__link--search');

    // Function to open dropdown
    function openAccountDropdown() {
        myAccount.classList.add('is-open');
        myAccount.setAttribute('aria-expanded', 'true');
        accountNavMenu.classList.remove('u-js-hide');
    }

    // Function to close dropdown
    function closeAccountDropdown() {
        myAccount.classList.remove('is-open');
        myAccount.setAttribute('aria-expanded', 'false');
        accountNavMenu.classList.add('u-js-hide');
    }

    // Disable href on myAccount if it has href attribute
    function disableHref(element) {
        if (element && element.hasAttribute('href')) {
            element.addEventListener('click', function(event) {
                event.preventDefault(); // Disable default behavior of the link
            });
        }
    }

    // Disable href on myAccount element
    disableHref(myAccount);

    // Click event listener for myAccount
    if (myAccount) {
        myAccount.addEventListener('click', function(event) {
            event.preventDefault(); // Disable default behavior of the link
            event.stopPropagation();
            // Close search menu if open
            if (searchLink.classList.contains('is-open')) {
                searchLink.classList.remove('is-open');
                searchLink.setAttribute('aria-expanded', 'false');
                searchMenu.classList.add('u-js-hide');
            }
            // Toggle account dropdown
            if (myAccount.classList.contains('is-open')) {
                closeAccountDropdown();
            } else {
                openAccountDropdown();
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!accountNavMenu.contains(event.target) && !myAccount.contains(event.target)) {
            closeAccountDropdown();
        }
    });

    // Prevent closing dropdown when clicking inside accountNavMenu
    accountNavMenu.addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Initialize with menu closed
    closeAccountDropdown();
});

// search-menu.js
$(document).ready(function() {
    const searchLink = $('.c-header__link--search');
    const searchMenu = $('#search-menu');
    const accountMenu = $('#my-account');
    const accountNavMenu = $('#account-nav-menu');

    // Pastikan elemen searchLink dan searchMenu ada
    if (searchLink.length && searchMenu.length) {
        // Klik pada elemen dengan class c-header__link--search
        searchLink.click(function(event) {
            event.preventDefault(); // Menonaktifkan prilaku default elemen <a>
            event.stopPropagation(); // Mencegah event bubbling

            // Toggle atribut is-open dan aria-expanded
            if ($(this).attr('aria-expanded') === 'false' || !$(this).attr('aria-expanded')) {
                $(this).addClass('is-open').attr('aria-expanded', 'true');
                searchMenu.removeClass('u-js-hide');
                // Close account menu if open
                if (accountMenu.hasClass('is-open')) {
                    accountMenu.removeClass('is-open').attr('aria-expanded', 'false');
                    accountNavMenu.addClass('u-js-hide');
                }
            } else {
                $(this).removeClass('is-open').attr('aria-expanded', 'false');
                searchMenu.addClass('u-js-hide');
            }
        });

        // Klik di luar elemen dengan id search-menu
        $(document).click(function(event) {
            var target = $(event.target);

            if (!target.closest('#search-menu').length && !target.closest('.c-header__link--search').length) {
                searchLink.removeClass('is-open').attr('aria-expanded', 'false');
                searchMenu.addClass('u-js-hide');
            }
        });

        // Menghentikan penyebaran event klik pada elemen search-menu agar tidak menutup dropdown
        searchMenu.click(function(event) {
            event.stopPropagation();
        });
    }
});

// kode untuk animasi menu samping
document.addEventListener('DOMContentLoaded', () => {
    const menuList = document.querySelector('.c-sidemenu');
    const menuItems = menuList.querySelectorAll('li.c-sidemenu');

    // Function to set the current menu item based on the URL
    const setCurrentMenuItem = () => {
        const currentUrl = window.location.href;
        menuItems.forEach(item => {
            const link = item.querySelector('a');
            if (link && link.href === currentUrl) {
                item.classList.add('menu-item--current');
            } else {
                item.classList.remove('menu-item--current');
            }
        });
    };

    // Set the current menu item on page load
    setCurrentMenuItem();

    // Add click event listener to menu items for dynamic changes
    menuList.addEventListener('click', (event) => {
        const clickedItem = event.target.closest('li.c-sidemenu');
        if (clickedItem) {
            // Remove the class from all items
            menuItems.forEach(item => item.classList.remove('menu-item--current'));
            // Add the class to the clicked item
            clickedItem.classList.add('menu-item--current');

            // Ensure the page loads the URL of the clicked item
            const link = clickedItem.querySelector('a');
            if (link) {
                window.location.href = link.href;
            }
        }
    });
});

// Klik tombol
document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('.c-facet__button');

  const closeAllExpanders = () => {
    const expanders = document.querySelectorAll('.c-facet-expander');
    expanders.forEach(expander => {
      if (!expander.classList.contains('u-js-hide')) {
        expander.classList.add('u-js-hide');
        expander.classList.remove('expanded');
        expander.hidden = true;
      }
    });
    buttons.forEach(button => {
      button.setAttribute('aria-expanded', 'false');
      button.classList.remove('is-open');
    });
  };

  buttons.forEach(button => {
    button.addEventListener('click', (event) => {
      event.stopPropagation();
      const targetSelector = button.getAttribute('data-facet-target');
      if (targetSelector) {
        const targetElement = document.querySelector(targetSelector);
        if (targetElement) {
          const isExpanded = button.getAttribute('aria-expanded') === 'true';

          closeAllExpanders();

          if (!isExpanded) {
            targetElement.classList.remove('u-js-hide');
            targetElement.hidden = false;
            targetElement.offsetHeight; // Trigger reflow to enable animation
            targetElement.classList.add('expanded');
            button.setAttribute('aria-expanded', 'true');
            button.classList.add('is-open');
          } else {
            targetElement.classList.remove('expanded');
            targetElement.classList.add('u-js-hide');
            targetElement.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            button.classList.remove('is-open');
          }
        }
      }
    });
  });

  document.addEventListener('click', (event) => {
    const expanders = document.querySelectorAll('.c-facet-expander');
    let isClickInsideExpander = false;
    
    expanders.forEach(expander => {
      if (expander.contains(event.target)) {
        isClickInsideExpander = true;
      }
    });
    
    if (!isClickInsideExpander) {
      closeAllExpanders();
    }
  });
});

// Tunda pemuatan tautan eksternal
$(document).ready(function() {
    // Menemukan elemen dengan id "standardFooter"
    var footer = $('#standardFooter');

    if (footer.length) {
        // Menemukan semua elemen <a> dengan href eksternal
        footer.find('a[href^="http"]').each(function() {
            var link = $(this);
            // Menyimpan href asli dalam data-href dan mengosongkan href sementara
            link.attr('data-href', link.attr('href'));
            link.attr('href', '#');
        });
    }

    // Mengembalikan href asli setelah halaman selesai dimuat
    $(window).on('load', function() {
        if (footer.length) {
            footer.find('a[data-href]').each(function() {
                var link = $(this);
                // Mengembalikan href asli dari data-href
                link.attr('href', link.attr('data-href'));
                link.removeAttr('data-href');
            });
        }
    });
});

// Searching for your mind
$(document).ready(function() {
    // Fungsi debounce
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

    // Fungsi untuk memproses pencarian
    function processSearch() {
        const keyword = $('#search-keywords').val().toLowerCase();
        const articles = $('#search-article-list .app-article-list-row__item');
        let hasResults = false;

        articles.each(function() {
            const articleText = $(this).text().toLowerCase();
            if (articleText.includes(keyword)) {
                hasResults = true;
                return false; // keluar dari loop each
            }
        });

        if (!hasResults) {
            $('.c-facet.c-facet--small, .app-search-adv-filters').addClass('u-hide');
        } else {
            $('.c-facet.c-facet--small, .app-search-adv-filters').removeClass('u-hide');
        }
    }

    // Menggunakan debounce pada event input
    $('#search-keywords').on('input', debounce(processSearch, 300));

    // Event handler untuk DOMNodeInserted
    $('#search-article-list').on('DOMNodeInserted', '.app-article-list-row', function() {
        const journalsIndex = {};
        
        $('.app-article-list-row__item').each(function() {
            const journalTitleElement = $(this).find('.c-meta__item[data-test="journal-title-and-link"]');
            if (journalTitleElement.length > 0) {
                const journalTitle = journalTitleElement.text().trim();
                if (journalsIndex[journalTitle]) {
                    journalsIndex[journalTitle]++;
                } else {
                    journalsIndex[journalTitle] = 1;
                }
            }
        });

        const journalTarget = $('#journal-target .c-facet-expander__list');
        journalTarget.empty();

        $.each(journalsIndex, function(title, count) {
            const listItem = $('<li>', { 'class': 'c-facet-expander__list-item' });
            const checkbox = $('<input>', {
                'type': 'checkbox',
                'name': 'journal',
                'id': 'journal-' + title.replace(/\s+/g, '-').toLowerCase(),
                'value': title,
                'data-action': 'submit',
                'checked': 'checked'
            });
            const label = $('<label>', { 'class': 'c-facet-expander__link', 'for': 'journal-' + title.replace(/\s+/g, '-').toLowerCase() });
            const span = $('<span>').text(title + ' (' + count + ')');
            label.append(span);
            listItem.append(checkbox).append(label);
            journalTarget.append(listItem);
        });
    });

    // Trigger initial search processing to handle preloaded values
    processSearch();
});

// animasi sort dan checkbox filter
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
                const dateA = new Date(a.querySelector('time.c-meta__item').getAttribute('datetime'));
                const dateB = new Date(b.querySelector('time.c-meta__item').getAttribute('datetime'));
                return order === 'asc' ? dateA - dateB : dateB - dateA;
            });
        }

        animateListChanges(articleList, articles, sortedArticles);
    }

    function filterArticles(showOpenAccess) {
        const articleList = document.querySelector('.app-article-list-row');
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
        const articles = Array.from(document.querySelectorAll('.app-article-list-row__item .c-meta__item'));
        const resultsOnlyCheckbox = document.querySelector('#results-only-access-checkbox');

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

    function updateSortByAttribute(value) {
        const sortByFieldset = document.querySelector('.c-sort-by');
        sortByFieldset.setAttribute('data-sort-by', value);
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

        // Optional: Code to update or reload article list can be added here
    }

    document.querySelector('label[for="sort-by-date_desc"]').addEventListener('click', function() {
        const value = document.querySelector('#sort-by-date_desc').value;
        sortArticles('desc');
        updateSortByAttribute(value);
    });

    document.querySelector('label[for="sort-by-date_asc"]').addEventListener('click', function() {
        const value = document.querySelector('#sort-by-date_asc').value;
        sortArticles('asc');
        updateSortByAttribute(value);
    });

    document.querySelector('label[for="sort-by-relevance"]').addEventListener('click', function() {
        const value = document.querySelector('#sort-by-relevance').value;
        sortArticles('relevance');
        updateSortByAttribute(value);
    });

    const resultsOnlyCheckbox = document.querySelector('#results-only-access-checkbox');
    resultsOnlyCheckbox.addEventListener('change', function() {
        filterArticles(this.checked);
    });

    // Event listener untuk search keywords
    document.querySelector('#search-keywords').addEventListener('input', handleSearchInput);

    // Tetapkan relevansi sebagai default
    document.querySelector('#sort-by-relevance').checked = true;
    sortArticles('relevance');
    updateSortByAttribute('relevance');

    // Set initial checkbox state and filter
    updateCheckboxState();
});

// Inisiasi indeks nama jurnal dan jumlah artikel
$(document).ready(function() {
    var journalCount = {};
    var articleTypeCount = {};
    
    var $journalTarget = $('#journal-target .c-facet-expander__list');
    var $articleTypeTarget = $('#article-type-target .c-facet-expander__list');
    
    var $journalButton = $('button[aria-labelledby="journal-legend"]');
    var $journalEllipsis = $journalButton.find('.c-facet__ellipsis');
    
    var $articleTypeButton = $('button[aria-labelledby="article-legend"]');
    var $articleTypeEllipsis = $articleTypeButton.find('.c-facet__ellipsis');

    // Sembunyikan elemen target selama proses pengambilan data
    $journalTarget.hide();
    $articleTypeTarget.hide();

    // Fungsi untuk mendapatkan path dari URL jurnal
    function getJournalPath(url) {
        var urlParts = url.split('/');
        return urlParts[urlParts.length - 1].toLowerCase();
    }

    // Proses data dari setiap halaman
    function processPageData(data) {
        var $page = $(data);
        var $articles = $page.find('.app-article-list-row .app-article-list-row__item');
        
        $articles.each(function() {
            var $article = $(this);
            var $journalElement = $article.find('.c-meta__item[data-test="journal-title-and-link"]');
            var $articleTypeElement = $article.find('.c-meta__item[data-test="article.type"]');

            if ($journalElement.length) {
                var journalName = $journalElement.text().trim();
                var journalURL = $journalElement.find('a').attr('href');
                var journalPath = getJournalPath(journalURL);

                if (!journalCount[journalPath]) {
                    journalCount[journalPath] = {
                        name: journalName,
                        count: 0
                    };
                }
                journalCount[journalPath].count++;
            }

            if ($articleTypeElement.length) {
                var articleTypeName = $articleTypeElement.text().trim();
                articleTypeCount[articleTypeName] = (articleTypeCount[articleTypeName] || 0) + 1;
            }
        });
    }

    // Ambil data dari setiap halaman secara paralel
    function fetchPages(totalPages, baseUrl) {
        var promises = [];
        for (let pageNumber = 1; pageNumber <= totalPages; pageNumber++) {
            promises.push($.ajax({
                url: baseUrl + '&page=' + pageNumber
            }));
        }

        Promise.all(promises).then(function(results) {
            results.forEach(function(data) {
                processPageData(data);
            });
            displayResults();
        }).catch(function(error) {
            console.error('Failed to fetch data', error);
        });
    }

    // Ambil total halaman
    function fetchTotalPages(baseUrl) {
        $.ajax({
            url: baseUrl + '&page=1',
            success: function(data) {
                var $page = $(data);
                var resultsText = $page.find('[data-test="results-data"] span').text();
                var totalItemsMatch = resultsText.match(/of\s+(\d+)\s+Items/);
                var totalItems = totalItemsMatch ? parseInt(totalItemsMatch[1], 10) : 0;
                var itemsPerPage = $page.find('.app-article-list-row__item').length;
                var totalPages = Math.ceil(totalItems / itemsPerPage);

                if (totalPages > 0) {
                    fetchPages(totalPages, baseUrl);
                }
            },
            error: function() {
                console.error('Failed to fetch the total number of pages');
            }
        });
    }

    // Tampilkan hasil setelah semua data diambil
    function displayResults() {
        // Bersihkan elemen target sebelum menambahkan data baru
        $journalTarget.empty();
        $articleTypeTarget.empty();

        // Buat document fragment untuk menghindari reflow dan repaint
        var journalFragment = document.createDocumentFragment();
        var articleTypeFragment = document.createDocumentFragment();

        // Iterasi jumlah jurnal untuk membuat daftar HTML jurnal
        Object.keys(journalCount).forEach(function(journalPath) {
            var journalData = journalCount[journalPath];
            var journalName = journalData.name;
            var count = journalData.count;
            
            var li = document.createElement('li');
            li.className = 'c-facet-expander__list-item';
            
            var input = document.createElement('input');
            input.name = 'journal';
            input.id = `journal-${journalPath}`;
            input.value = journalPath;
            input.dataset.action = 'submit';
            input.type = 'checkbox';

            var label = document.createElement('label');
            label.className = 'c-facet-expander__link';
            label.htmlFor = `journal-${journalPath}`;
            
            var span = document.createElement('span');
            span.textContent = `${journalName} (${count})`;

            label.appendChild(span);
            li.appendChild(input);
            li.appendChild(label);
            journalFragment.appendChild(li);
        });

        // Iterasi jumlah jenis artikel untuk membuat daftar HTML jenis artikel
        Object.keys(articleTypeCount).forEach(function(articleTypeName) {
            var count = articleTypeCount[articleTypeName];
            var articleTypePathName = articleTypeName.toLowerCase().replace(/[^a-z0-9]+/g, '-');
            
            var li = document.createElement('li');
            li.className = 'c-facet-expander__list-item';
            
            var input = document.createElement('input');
            input.name = 'article_type';
            input.id = `article-type-${articleTypePathName}`;
            input.value = articleTypePathName;
            input.type = 'checkbox';

            var label = document.createElement('label');
            label.className = 'c-facet-expander__link';
            label.htmlFor = `article-type-${articleTypePathName}`;
            
            var span = document.createElement('span');
            span.textContent = `${articleTypeName} (${count})`;

            label.appendChild(span);
            li.appendChild(input);
            li.appendChild(label);
            articleTypeFragment.appendChild(li);
        });

        // Tambahkan document fragments ke elemen target
        requestAnimationFrame(function() {
            $journalTarget.append(journalFragment);
            $articleTypeTarget.append(articleTypeFragment);

            // Tampilkan kembali elemen target setelah semua data ditambahkan
            $journalTarget.show();
            $articleTypeTarget.show();
        });

        // Periksa jumlah jurnal
        var journalKeys = Object.keys(journalCount);
        if (journalKeys.length === 1) {
            var singleJournalPath = journalKeys[0];
            var singleJournalData = journalCount[singleJournalPath];
            
            // Perbarui teks dan tambahkan class
            $journalEllipsis.text(`${singleJournalData.name} (${singleJournalData.count})`);
            $journalButton.addClass('c-facet__selected');
        }

        // Periksa jumlah jenis artikel
        var articleTypeKeys = Object.keys(articleTypeCount);
        if (articleTypeKeys.length === 1) {
            var singleArticleTypeName = articleTypeKeys[0];
            var singleArticleTypeCount = articleTypeCount[singleArticleTypeName];
            
            // Perbarui teks dan tambahkan class
            $articleTypeEllipsis.text(`${singleArticleTypeName} (${singleArticleTypeCount})`);
            $articleTypeButton.addClass('c-facet__selected');
        }
    }

    // Mulai mengambil data dengan menentukan jumlah halaman
    var currentUrl = window.location.href;
    var baseUrl = currentUrl.split('?')[0] + '?';
    var params = new URLSearchParams(window.location.search);
    params.delete('page'); // Hapus parameter halaman jika ada
    baseUrl += params.toString();

    fetchTotalPages(baseUrl);
});
