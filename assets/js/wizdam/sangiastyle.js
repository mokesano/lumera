/**
 * Back To Top
 * Kode default, inisialisasi event ketika dokumen sudah siap.
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
jQuery(document).ready(function($){
    $( ".mtoggle" ).click(function() {
        $( ".menu" ).slideToggle(500);
    });

    $("#skip-to-content").click(function() {
        $("#sangia.org").focus()
    });
});

/**
 * Inisialisasi IP Address user.
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
async function getIPAddress() {
  try {
    const response = await fetch('https://api.ipify.org?format=json');
    const data = await response.json();
    document.getElementById('diagnostic-ip').textContent = `Your IP Address: ${data.ip}`;
  } catch (error) {
    console.error('Error fetching IP address:', error);
  }
}

document.addEventListener('DOMContentLoaded', getIPAddress);

/**
 * Notification pada Navbar User
 * Kode gabungan dengan inisialisasi event ketika dokumen sudah siap.
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
$(document).ready(function() {
    /**
     * Tombol kembali ke atas
     */
    const btn = $('.buttontop');

    $(window).scroll(function() {
        // Logika tombol Back-to-Top (asumsi variabel 'btn' sudah didefinisikan di atas)
        if ($(window).scrollTop() > 300) {
            btn.addClass('show');
        } else {
            btn.removeClass('show');
        }

        // PERBAIKAN: Cek dulu apakah fungsi closeAccountDropdown ada
        if (typeof closeAccountDropdown === 'function') {
            closeAccountDropdown();
        }

        // PERBAIKAN: Cek juga fungsi closeSearchDropdown (untuk mencegah error berikutnya)
        if (typeof closeSearchDropdown === 'function') {
            closeSearchDropdown();
        }
    });

    btn.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, '300');
    });

    const notificationCountElement = document.getElementById('notification-count');
    const myAccountElement = document.getElementById('my-account');

    if (notificationCountElement) {
        const notificationCount = parseInt(notificationCountElement.textContent, 10);

        if (notificationCount > 0) {
            notificationCountElement.classList.add('show');

            setInterval(() => {
                notificationCountElement.classList.remove('show');
                setTimeout(() => {
                    notificationCountElement.classList.add('show');
                }, 10);
            }, 10000);

            myAccountElement.addEventListener('mouseenter', () => {
                notificationCountElement.style.transform = 'translate(50%, -50%) scale(1.3)';
            });

            myAccountElement.addEventListener('mouseleave', () => {
                notificationCountElement.style.transform = 'translate(50%, -50%) scale(1)';
            });
        } else {
            notificationCountElement.style.display = 'none';
        }
    }

    const buttons = document.querySelectorAll('.c-facet__button');

    /**
     * Menutup semua expander
     */
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
                        targetElement.offsetHeight;
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

    const disableButtons = document.querySelectorAll('.button-alternative');

    disableButtons.forEach(function(button) {
        if (button.classList.contains('disabled')) {
            const svgIcon = button.querySelector('svg.icon');
            if (svgIcon) {
                if (button.classList.contains('button-alternative-primary')) {
                    svgIcon.style.backgroundColor = '#b9b9b9';
                    svgIcon.style.borderColor = '#b9b9b9';
                    svgIcon.style.fill = '#fff';
                } else if (button.classList.contains('button-alternative-secondary') || button.classList.contains('button-alternative-tertiary')) {
                    svgIcon.style.backgroundColor = '#fff';
                    svgIcon.style.borderColor = '#b9b9b9';
                    svgIcon.style.fill = '#b9b9b9';
                }
            }

            const buttonText = button.querySelector('.button-alternative-text');
            if (buttonText) {
                buttonText.style.color = '#b9b9b9';
                buttonText.style.cursor = 'default';
            }

            button.style.pointerEvents = 'none';
            button.style.cursor = 'not-allowed';

            button.addEventListener('click', function(event) {
                event.preventDefault();
            });
        }
    });

    const promoSections = document.querySelectorAll('.sangia-promo');

    promoSections.forEach(function(section) {
        const originalText = section.innerText;
        if (originalText.length > 270) {
            const truncatedText = originalText.substring(0, 270);
            section.innerText = truncatedText + '...';

            const showAllButton = document.createElement('button');
            showAllButton.innerHTML = "Show All";
            showAllButton.classList.add('show-all-btn');
            section.parentElement.appendChild(showAllButton);

            showAllButton.addEventListener('click', function() {
                section.innerText = originalText;
                showAllButton.style.display = 'none';
            });
        }
    });

    /**
     * Menginisiasi elemen terkait
     */
    function initRelatedItems() {
        $("#relatedItems").hide();
        $("#toggleRelatedItems").show();
        $("#hideRelatedItems").click(function() {
            $("#relatedItems").hide('fast');
            $("#hideRelatedItems").hide();
            $("#showRelatedItems").show();
        });
        $("#showRelatedItems").click(function() {
            $("#relatedItems").show('fast');
            $("#showRelatedItems").hide();
            $("#hideRelatedItems").show();
        });
    }
    initRelatedItems();
});

/**
 * Share Sosial Media Button Article
 * Tampilkan elemen Share, hapus elemen dari DOM segera setelah halaman dimuat
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
document.addEventListener("DOMContentLoaded", function() {
    // Hapus elemen dari DOM segera setelah halaman dimuat
    var popoverContentSocial = document.getElementById("popover-content-social-popover");
    var popoverContentSocialJQ;
    var popoverContentExportCitation = document.getElementById("popover-content-export-citation-popover");
    var popoverContentExportCitationJQ;
    var popoverContentAnother = document.getElementById("popover-content-another-popover");
    var popoverContentAnotherJQ;

    if (popoverContentSocial) {
        popoverContentSocialJQ = $(popoverContentSocial).remove();
    }

    if (popoverContentExportCitation) {
        popoverContentExportCitationJQ = $(popoverContentExportCitation).remove();
    }

    if (popoverContentAnother) {
        popoverContentAnotherJQ = $(popoverContentAnother).remove();
    }

    var isAnimating = false;

    function hidePopover(popoverContent) {
        popoverContent.slideUp(150, function() {
            $(this).remove();
            isAnimating = false;
        });
    }

    function showPopover(popoverContainer, popoverContent) {
        popoverContainer.append(popoverContent);
        popoverContent.hide().removeClass('u-js-hide').slideDown(150, function() {
            isAnimating = false;
        });
    }

    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    /**
     * Menangani klik pada elemen pemicu untuk menampilkan atau menyembunyikan elemen popover
     * @param {Event} event - Event klik pada elemen pemicu
     */
    $("#popover-trigger-social-popover").on("click", function(event) {
        if (isAnimating) return;
        isAnimating = true;

        if ($("#social-popover").find("#popover-content-social-popover").length) {
            hidePopover($("#popover-content-social-popover"));
        } else {
            if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
                hidePopover($("#popover-content-export-citation-popover"));
            }
            if ($("#another-popover").find("#popover-content-another-popover").length) {
                hidePopover($("#popover-content-another-popover"));
            }
            showPopover($("#social-popover"), popoverContentSocialJQ);
        }
        event.stopPropagation();
    });

    $("#popover-trigger-export-citation-popover").on("click", function(event) {
        if (isAnimating) return;
        isAnimating = true;

        if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
            hidePopover($("#popover-content-export-citation-popover"));
        } else {
            if ($("#social-popover").find("#popover-content-social-popover").length) {
                hidePopover($("#popover-content-social-popover"));
            }
            if ($("#another-popover").find("#popover-content-another-popover").length) {
                hidePopover($("#popover-content-another-popover"));
            }
            showPopover($("#export-citation-popover"), popoverContentExportCitationJQ);
        }
        event.stopPropagation();
    });

    $("#popover-trigger-another-popover").on("click", function(event) {
        if (isAnimating) return;
        isAnimating = true;

        if ($("#another-popover").find("#popover-content-another-popover").length) {
            hidePopover($("#popover-content-another-popover"));
        } else {
            if ($("#social-popover").find("#popover-content-social-popover").length) {
                hidePopover($("#popover-content-social-popover"));
            }
            if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
                hidePopover($("#popover-content-export-citation-popover"));
            }
            showPopover($("#another-popover"), popoverContentAnotherJQ);
        }
        event.stopPropagation();
    });

    /**
     * Menangani klik di luar elemen popover untuk menutup popover
     * @param {Event} event - Event klik pada dokumen
     */
    $(document).on("click", function(event) {
        if (isAnimating) return;

        if (!$(event.target).closest("#popover-content-social-popover, #popover-trigger-social-popover").length) {
            if ($("#social-popover").find("#popover-content-social-popover").length) {
                isAnimating = true;
                hidePopover($("#popover-content-social-popover"));
            }
        }
        if (!$(event.target).closest("#popover-content-export-citation-popover, #popover-trigger-export-citation-popover").length) {
            if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
                isAnimating = true;
                hidePopover($("#popover-content-export-citation-popover"));
            }
        }
        if (!$(event.target).closest("#popover-content-another-popover, #popover-trigger-another-popover").length) {
            if ($("#another-popover").find("#popover-content-another-popover").length) {
                isAnimating = true;
                hidePopover($("#popover-content-another-popover"));
            }
        }
    });

    /**
     * Menangani scroll pada jendela untuk menutup popover jika elemen berada di luar viewport
     */
    $(window).on("scroll", function() {
        if (isAnimating) return;

        var socialPopover = document.getElementById("popover-content-social-popover");
        var exportCitationPopover = document.getElementById("popover-content-export-citation-popover");
        var anotherPopover = document.getElementById("popover-content-another-popover");

        if ($("#social-popover").find("#popover-content-social-popover").length && !isElementInViewport(socialPopover)) {
            isAnimating = true;
            hidePopover($("#popover-content-social-popover"));
        }
        if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length && !isElementInViewport(exportCitationPopover)) {
            isAnimating = true;
            hidePopover($("#popover-content-export-citation-popover"));
        }
        if ($("#another-popover").find("#popover-content-another-popover").length && !isElementInViewport(anotherPopover)) {
            isAnimating = true;
            hidePopover($("#popover-content-another-popover"));
        }
    });
});

/**
 * Lokasi asal Highlight dan References
 */
 
/**
 * Mengatur pencarian dengan caching
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.1.7
 */
$(document).ready(function() {
    const searchCache = new Map();

    /**
     * Menghapus cache setelah interval tertentu.
     */
    function clearCacheInterval() {
        setInterval(() => {
            searchCache.clear();
            console.log("[Wizdam Tools]: Cache cleared");
        }, 600000); // 10 menit dalam milidetik
    }
    
    /**
     * Fungsi debounce
     * @param {Function} func - Fungsi yang akan di-debounce.
     * @param {number} wait - Waktu tunggu dalam milidetik.
     * @returns {Function} - Fungsi yang di-debounce.
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
     * Fungsi untuk memproses pencarian dengan mekanisme caching.
     */
    function processSearch() {
        // console.log("Processing search");
        
        // 1. Simpan elemen ke variabel
        const searchInput = $('#search-keywords');

        // 2. TAMBAHKAN PENJAGA INI:
        // Cek apakah elemennya ada di halaman ini. Jika tidak (length === 0), hentikan fungsi.
        if (searchInput.length === 0) {
            return; 
        }
        
        // 3. SEKARANG AMAN untuk mengambil nilai
        const keyword = searchInput.val().toLowerCase();

        if (searchCache.has(keyword)) {
            console.log("Cache hit");
            updateSearchResults(searchCache.get(keyword));
            return;
        }

        const articles = $('#search-article-list .app-article-list-row__item');
        let hasResults = false;

        if (keyword === "") {
            $('.c-facet, .c-list-header').removeClass('u-hide');
            return;
        }

        articles.each(function() {
            const articleText = $(this).text().toLowerCase();
            if (articleText.includes(keyword)) {
                hasResults = true;
                return false; // keluar dari loop each
            }
        });

        searchCache.set(keyword, hasResults);

        updateSearchResults(hasResults);
    }

    $('#search-keywords').on('input', debounce(processSearch, 300));

    $('#search-article-list').on('DOMNodeInserted', '.app-article-list-row', function() {
        updateJournalAndArticleTypeIndex();
    });

    /**
     * Memperbarui hasil pencarian berdasarkan apakah ada hasil yang ditemukan.
     * @param {boolean} hasResults - Indikasi apakah hasil pencarian ditemukan.
     */
    function updateSearchResults(hasResults) {
        if (!hasResults) {
            $('.c-facet, .c-list-header').addClass('u-hide');
        } else {
            $('.c-facet, .c-list-header').removeClass('u-hide');
        }
    }

    /**
     * Menginisialisasi fungsi pencarian dengan caching.
     */
    function initSearchFunctionality() {
        $('#search-keywords').on('input', debounce(processSearch, 300));

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

        processSearch();
    }

    initSearchFunctionality();

    clearCacheInterval();
    
});

/**
 * Account dan Search Menu - Safe Isolated Version
 * Mengatur dropdown slider menu utama pencarian dan akun
 * 
 * @author Rochmady and Wizdam Team
 * @version 2.1.0 - Safe Isolated
 */
(function() {
    'use strict';
    
    // Namespace yang sangat spesifik untuk header saja
    const WizdamHeaderDropdown = {
        // Konfigurasi yang sangat spesifik
        config: {
            namespace: 'wizdam-header-dropdown',
            selectors: {
                // Hanya target elemen header yang sangat spesifik
                searchLink: '.c-header__link--search',
                searchMenu: '#search-menu',
                searchInput: '#search-menu .c-search__input',
                searchForm: '#search-form',
                accountMenu: '#my-account',
                accountNavMenu: '#account-nav-menu'
            },
            classes: {
                open: 'is-open',
                hidden: 'u-js-hide'
            },
            debounceDelay: 150,
            clickDelay: 200
        },

        state: {
            isInitialized: false,
            clickEnabled: false,
            elements: {},
            eventCleanup: []
        },

        // Utility yang lebih aman
        utils: {
            debounce: function(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },

            // Validasi elemen sangat ketat - hanya elemen header
            isHeaderElement: function(element) {
                if (!element || !element.closest) return false;
                const headerContainer = element.closest('.c-header, header, .header');
                return headerContainer !== null;
            },

            // Safe DOM query khusus untuk header
            safeHeaderQuery: function(selector) {
                try {
                    const elements = document.querySelectorAll(selector);
                    // Filter hanya elemen yang berada di dalam header
                    for (let element of elements) {
                        if (this.isHeaderElement(element)) {
                            return element;
                        }
                    }
                    return null;
                } catch (e) {
                    return null;
                }
            }
        },

        // Cache elemen dengan validasi ketat
        cacheElements: function() {
            const elements = {};
            const selectors = this.config.selectors;

            // Hanya cache elemen yang benar-benar ada di header
            for (const [key, selector] of Object.entries(selectors)) {
                const element = this.utils.safeHeaderQuery(selector);
                if (element) {
                    elements[key] = element;
                }
            }

            this.state.elements = elements;
            
            // Validasi minimal - search atau account harus ada
            return elements.searchLink || elements.accountMenu;
        },

        // Search dropdown functions
        searchDropdown: {
            open: function() {
                const { searchLink, searchMenu, searchInput } = WizdamHeaderDropdown.state.elements;
                const classes = WizdamHeaderDropdown.config.classes;

                if (searchLink && searchMenu) {
                    searchLink.classList.add(classes.open);
                    searchLink.setAttribute('aria-expanded', 'true');
                    searchMenu.classList.remove(classes.hidden);
                    
                    if (searchInput) {
                        setTimeout(() => {
                            if (searchInput.focus && typeof searchInput.focus === 'function') {
                                searchInput.focus();
                            }
                        }, 100);
                    }
                }
            },

            close: function() {
                const { searchLink, searchMenu } = WizdamHeaderDropdown.state.elements;
                const classes = WizdamHeaderDropdown.config.classes;

                if (searchLink && searchMenu) {
                    searchLink.classList.remove(classes.open);
                    searchLink.setAttribute('aria-expanded', 'false');
                    searchMenu.classList.add(classes.hidden);
                }
            }
        },

        // Account dropdown functions
        accountDropdown: {
            open: function() {
                const { accountMenu, accountNavMenu } = WizdamHeaderDropdown.state.elements;
                const classes = WizdamHeaderDropdown.config.classes;

                if (accountMenu && accountNavMenu) {
                    accountMenu.classList.add(classes.open);
                    accountMenu.setAttribute('aria-expanded', 'true');
                    accountNavMenu.classList.remove(classes.hidden);
                }
            },

            close: function() {
                const { accountMenu, accountNavMenu } = WizdamHeaderDropdown.state.elements;
                const classes = WizdamHeaderDropdown.config.classes;

                if (accountMenu && accountNavMenu) {
                    accountMenu.classList.remove(classes.open);
                    accountMenu.setAttribute('aria-expanded', 'false');
                    accountNavMenu.classList.add(classes.hidden);
                }
            }
        },

        // Event handlers yang sangat spesifik
        createEventHandlers: function() {
            const self = this;
            
            return {
                searchClick: function(event) {
                    // Double check ini adalah elemen yang benar
                    if (!self.utils.isHeaderElement(this)) return;
                    if (!self.state.clickEnabled) return;
                    
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const { searchLink, accountMenu } = self.state.elements;
                    const classes = self.config.classes;
                    
                    if (this === searchLink) {
                        const isOpen = searchLink.getAttribute('aria-expanded') === 'true';
                        
                        if (isOpen) {
                            self.searchDropdown.close();
                        } else {
                            self.searchDropdown.open();
                            // Close account jika ada
                            if (accountMenu && accountMenu.classList.contains(classes.open)) {
                                self.accountDropdown.close();
                            }
                        }
                    }
                },

                accountClick: function(event) {
                    // Double check ini adalah elemen yang benar
                    if (!self.utils.isHeaderElement(this)) return;
                    if (!self.state.clickEnabled) return;
                    
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const { accountMenu, searchLink } = self.state.elements;
                    const classes = self.config.classes;
                    
                    if (this === accountMenu) {
                        // Close search jika ada
                        if (searchLink && searchLink.classList.contains(classes.open)) {
                            self.searchDropdown.close();
                        }
                        
                        const isOpen = accountMenu.classList.contains(classes.open);
                        if (isOpen) {
                            self.accountDropdown.close();
                        } else {
                            self.accountDropdown.open();
                        }
                    }
                },

                // Event document click yang sangat spesifik
                documentClick: function(event) {
                    if (!self.state.clickEnabled) return;
                    
                    const target = event.target;
                    const elements = self.state.elements;
                    
                    // Hanya proses jika tidak mengklik elemen header dropdown
                    let isHeaderDropdownClick = false;
                    
                    // Check apakah klik pada elemen search
                    if (elements.searchMenu && elements.searchMenu.contains(target)) {
                        isHeaderDropdownClick = true;
                    }
                    if (elements.searchLink && elements.searchLink.contains(target)) {
                        isHeaderDropdownClick = true;
                    }
                    
                    // Check apakah klik pada elemen account
                    if (elements.accountNavMenu && elements.accountNavMenu.contains(target)) {
                        isHeaderDropdownClick = true;
                    }
                    if (elements.accountMenu && elements.accountMenu.contains(target)) {
                        isHeaderDropdownClick = true;
                    }
                    
                    // Hanya close jika klik di luar elemen header dropdown
                    if (!isHeaderDropdownClick) {
                        self.searchDropdown.close();
                        self.accountDropdown.close();
                    }
                },

                scroll: self.utils.debounce(function() {
                    if (self.state.clickEnabled) {
                        self.searchDropdown.close();
                        self.accountDropdown.close();
                    }
                }, self.config.debounceDelay),

                searchFormSubmit: function(event) {
                    if (!self.utils.isHeaderElement(this)) return;
                    
                    const { searchInput } = self.state.elements;
                    if (this === self.state.elements.searchForm && 
                        searchInput && searchInput.value.trim() === '') {
                        event.preventDefault();
                        alert('Input must be filled to proceed to the search page.');
                        searchInput.focus();
                    }
                },

                // Prevent bubbling hanya untuk menu content
                menuContentClick: function(event) {
                    if (!self.utils.isHeaderElement(this)) return;
                    event.stopPropagation();
                }
            };
        },

        // Event binding yang sangat spesifik
        bindEvents: function() {
            const elements = this.state.elements;
            const handlers = this.createEventHandlers();
            const cleanup = [];

            // Bind hanya pada elemen yang ada dan valid
            if (elements.searchLink) {
                elements.searchLink.addEventListener('click', handlers.searchClick);
                cleanup.push(['searchLink', 'click', handlers.searchClick]);
            }

            if (elements.searchMenu) {
                elements.searchMenu.addEventListener('click', handlers.menuContentClick);
                cleanup.push(['searchMenu', 'click', handlers.menuContentClick]);
            }

            if (elements.searchForm) {
                elements.searchForm.addEventListener('submit', handlers.searchFormSubmit);
                cleanup.push(['searchForm', 'submit', handlers.searchFormSubmit]);
            }

            if (elements.accountMenu) {
                elements.accountMenu.addEventListener('click', handlers.accountClick);
                cleanup.push(['accountMenu', 'click', handlers.accountClick]);
            }

            if (elements.accountNavMenu) {
                elements.accountNavMenu.addEventListener('click', handlers.menuContentClick);
                cleanup.push(['accountNavMenu', 'click', handlers.menuContentClick]);
            }

            // Global events dengan namespace
            document.addEventListener('click', handlers.documentClick, true);
            cleanup.push(['document', 'click', handlers.documentClick, true]);

            window.addEventListener('scroll', handlers.scroll, { passive: true });
            cleanup.push(['window', 'scroll', handlers.scroll, { passive: true }]);

            // Store cleanup info
            this.state.eventCleanup = cleanup;
            this.state.handlers = handlers;
        },

        // Cleanup events
        removeEvents: function() {
            const elements = this.state.elements;
            const cleanup = this.state.eventCleanup;

            cleanup.forEach(([target, event, handler, options]) => {
                let element;
                if (target === 'document') element = document;
                else if (target === 'window') element = window;
                else element = elements[target];

                if (element && element.removeEventListener) {
                    element.removeEventListener(event, handler, options);
                }
            });

            this.state.eventCleanup = [];
        },

        // Initialization yang aman
        init: function() {
            if (this.state.isInitialized) return;

            try {
                // Hanya lanjut jika berhasil cache elemen header
                if (!this.cacheElements()) {
                    console.log('[Wizdam Header Dropdown]: Header elements not found, skipping initialization');
                    return;
                }

                // Set initial state
                this.accountDropdown.close();
                this.searchDropdown.close();

                // Bind events
                this.bindEvents();

                // Enable clicks setelah delay
                setTimeout(() => {
                    this.state.clickEnabled = true;
                    this.state.isInitialized = true;
                    console.log('[Wizdam Header Dropdown]: Initialized successfully');
                }, this.config.clickDelay);

            } catch (error) {
                console.error('[Wizdam Header Dropdown]: Initialization failed', error);
            }
        },

        // Cleanup
        destroy: function() {
            this.removeEvents();
            this.state.isInitialized = false;
            this.state.clickEnabled = false;
            this.state.elements = {};
            this.state.eventCleanup = [];
        }
    };

    // Initialize hanya saat DOM ready, tanpa MutationObserver yang invasif
    function safeInitialize() {
        // Tunggu DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function initOnce() {
                document.removeEventListener('DOMContentLoaded', initOnce);
                setTimeout(() => WizdamHeaderDropdown.init(), 100);
            });
        } else {
            setTimeout(() => WizdamHeaderDropdown.init(), 100);
        }
    }

    // Hanya expose untuk debugging di development
    if (typeof window !== 'undefined' && 
        (window.location.hostname === 'localhost' || window.location.hostname.includes('dev'))) {
        window.WizdamHeaderDropdown = WizdamHeaderDropdown;
    }

    // Initialize
    safeInitialize();

    // Cleanup pada unload
    window.addEventListener('beforeunload', function() {
        WizdamHeaderDropdown.destroy();
    });

})();

/**
 * Show More/Less Affiliation
 * Mengatur tampilan "Show More/Less" pada elemen afiliasi author
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
document.addEventListener("DOMContentLoaded", function () {
    const showMoreBtn = document.getElementById("show-more-btn");
    const wrapper = document.querySelector(".wrapper");

    // 1. Buat array dengan elemen standar dulu
    const elementsToToggle = [
        document.getElementById("affiliation-group"),
        document.querySelector(".crossmark-button")
    ];

    // 2. Cek apakah wrapper ada. Jika ada, tambahkan elemen <p> ke dalam array tadi
    if (wrapper) {
        elementsToToggle.push(wrapper.querySelector("p"));
    }

    /**
     * Mengatur arah panah
     * @param {boolean} isExpanded - Menentukan apakah panah diperluas atau tidak.
     */
    const toggleArrowDirection = (isExpanded) => {
        const svgIcon = showMoreBtn.querySelector("svg");
        if (svgIcon) {
            svgIcon.style.transform = isExpanded ? "rotate(180deg)" : "rotate(0deg)";
        }
    }

    // Perbaikan: Cek apakah tombol showMoreBtn ada sebelum diproses
    if (showMoreBtn) {
        showMoreBtn.addEventListener("click", function () {
            const isExpanded = showMoreBtn.getAttribute("aria-expanded") === "true";
    
            if (isExpanded) {
                elementsToToggle.forEach(element => {
                    if (element) {
                        element.hidden = true;
                    }
                });
                
                // Cek wrapper untuk keamanan ekstra
                if (typeof wrapper !== 'undefined' && wrapper) {
                    wrapper.classList.add("truncated");
                }
                
                showMoreBtn.setAttribute("aria-expanded", "false");
                showMoreBtn.setAttribute("data-aa-button", "icon-collapse");
                showMoreBtn.querySelector(".anchor-text").textContent = "Show more";
                toggleArrowDirection(false);
            } else {
                elementsToToggle.forEach(element => {
                    if (element) {
                        element.hidden = false;
                    }
                });
                
                // Cek wrapper untuk keamanan ekstra
                if (typeof wrapper !== 'undefined' && wrapper) {
                    wrapper.classList.remove("truncated");
                }
                
                showMoreBtn.setAttribute("aria-expanded", "true");
                showMoreBtn.setAttribute("data-aa-button", "icon-expand");
                showMoreBtn.querySelector(".anchor-text").textContent = "Show less";
                toggleArrowDirection(true);
            }
        });
    }

    // Perbaikan: Cek dulu apakah showMoreBtn ada
    if (showMoreBtn) {
        const initialState = showMoreBtn.getAttribute("aria-expanded") === "true";
        
        if (!initialState) {
            // Pastikan wrapper ada sebelum mengubah class-nya
            if (typeof wrapper !== 'undefined' && wrapper) {
                wrapper.classList.add("truncated");
            }
            
            elementsToToggle.forEach(element => {
                if (element) {
                    element.hidden = true;
                }
            });
        }

        // PERBAIKAN UTAMA: Baris ini harus ada di dalam blok IF agar bisa membaca 'initialState'
        toggleArrowDirection(initialState);
    }
});

/**
 * Lokasi asal Afiliasi penulis
 */
 
/**
 * Style menu members dan about journals 
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
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

    // Perbaikan: Cek eksistensi variabel sebelum mengakses atributnya
    
    if (details1 && details1.hasAttribute('open')) {
        // Jika details1 terbuka, tutup details2 (hanya jika details2 ada)
        if (details2) {
            details2.removeAttribute('open');
        }
    } else if (details2 && details2.hasAttribute('open')) {
        // Jika details2 terbuka, tutup details1 (hanya jika details1 ada)
        if (details1) {
            details1.removeAttribute('open');
        }
    }

    // Perbaikan: Cek dulu apakah details1 ada sebelum diproses
    if (details1) {
        details1.addEventListener('click', (event) => {
            event.preventDefault();
            toggleDetails(details1, details2);
        });
    }

    // Perbaikan: Cek dulu apakah details2 ada
    if (details2) {
        details2.addEventListener('click', (event) => {
            event.preventDefault();
            toggleDetails(details2, details1);
        });
    }
});

/**
 * Style side menu about journals 
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.7
 */
document.addEventListener('DOMContentLoaded', () => {
    const menuList = document.querySelector('.c-sidemenu');

    // PERBAIKAN: Cek dulu apakah menuList ada sebelum lanjut
    if (menuList) {
        const menuItems = menuList.querySelectorAll('li.c-sidemenu');

        /**
         * Menetapkan item menu saat ini berdasarkan URL
         */
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

        setCurrentMenuItem();

        menuList.addEventListener('click', (event) => {
            const clickedItem = event.target.closest('li.c-sidemenu');
            if (clickedItem) {
                menuItems.forEach(item => item.classList.remove('menu-item--current'));
                clickedItem.classList.add('menu-item--current');

                const link = clickedItem.querySelector('a');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    }
});

/**
 * Wizdam Side Menu Controller
 * Features: 
 * 1. Strict DOM Caching (Removes unused submenus for performance)
 * 2. Deep Link Support (StartsWith logic for /view/1 etc.)
 * 3. Hybrid Support (Policies Nested + Announcement Flat)
 * 4. Diagnostics Logging
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Target Root Menu (Ambil SEMUA direct children LI)
    const sidebar = document.querySelector('.c-sidemenu');
    if (!sidebar) return;

    const rootMenuItems = Array.from(sidebar.querySelectorAll('.c-sidemenu > li'));
    
    // 2. Memory Cache untuk Submenu
    const menuCache = new Map();

    // Inisialisasi: Simpan submenu ke memori & Hapus dari DOM
    rootMenuItems.forEach(parent => {
        const subMenu = parent.querySelector('.c-nav--stacked'); 
        if (subMenu) {
            menuCache.set(parent, subMenu);
            if (parent.contains(subMenu)) {
                parent.removeChild(subMenu);
            }
        }
    });

    /**
     * Helper: Normalisasi Path URL
     */
    const normalize = (url) => {
        try {
            const urlObj = new URL(url, window.location.href);
            let path = decodeURIComponent(urlObj.pathname).toLowerCase();
            path = path.replace(/\/index\.php/, ''); 
            path = path.replace(/\/$/, '');
            if (!path.startsWith('/')) path = '/' + path;
            return { path: path, hash: urlObj.hash };
        } catch (e) {
            return { path: '', hash: '' };
        }
    };

    /**
     * Helper: Cek Kesamaan URL (Deep Linking Support)
     */
    const isUrlMatch = (linkHref) => {
        const current = normalize(window.location.href);
        const target = normalize(linkHref);

        if (target.path.length <= 1) return false;

        // KASUS 1: Hash Match
        if (target.hash) {
            return (current.path === target.path && current.hash === target.hash);
        }

        // KASUS 2: Page Match (STARTS WITH + Boundary Check)
        if (current.path.startsWith(target.path)) {
            const charAfter = current.path.charAt(target.path.length);
            if (charAfter === '' || charAfter === '/' || charAfter === '?') {
                return true;
            }
        }
        return false;
    };

    /**
     * Fungsi Render Utama
     */
    const renderMenuState = () => {
        let bestMatchItem = null;
        let maxLen = 0;
        let bestMatchSubLink = null; 

        // Pass 1: Cari Match Terpanjang (Logic Pemenang)
        rootMenuItems.forEach(parent => {
            const cachedSubMenu = menuCache.get(parent);
            
            // A. Cek Anak (Deep Scan di Cache)
            if (cachedSubMenu) {
                const childLinks = Array.from(cachedSubMenu.querySelectorAll('a'));
                childLinks.forEach(link => {
                    const norm = normalize(link.href);
                    if (isUrlMatch(link.href)) {
                        if (norm.path.length > maxLen) {
                            maxLen = norm.path.length;
                            bestMatchItem = parent;
                            bestMatchSubLink = link;
                        }
                    }
                });
            }

            // B. Cek Induk (Direct Link)
            const parentLink = parent.querySelector('a');
            if (parentLink) {
                const norm = normalize(parentLink.href);
                if (isUrlMatch(parentLink.href)) {
                     if (norm.path.length > maxLen) {
                        maxLen = norm.path.length;
                        bestMatchItem = parent;
                        bestMatchSubLink = null;
                     }
                }
            }
        });

        // Logging Diagnostik
        if (bestMatchItem) {
            const linkText = bestMatchItem.querySelector('a').innerText.trim();
            console.log(`[Wizdam Sidemenu]: Active Item Found -> "${linkText}"`);
        }

        // Pass 2: Render DOM
        rootMenuItems.forEach(parent => {
            const cachedSubMenu = menuCache.get(parent);
            const isWinner = (parent === bestMatchItem);

            // Reset Class
            parent.classList.remove('c-menu--current');
            parent.classList.remove('menu-item--current');
            parent.classList.remove('is-active');

            if (isWinner) {
                // KASUS NESTED
                if (cachedSubMenu) {
                    parent.classList.add('c-menu--current');
                    parent.classList.add('is-active');

                    if (!parent.contains(cachedSubMenu)) {
                        parent.appendChild(cachedSubMenu);
                    }

                    if (bestMatchSubLink) {
                        const allSubLis = cachedSubMenu.querySelectorAll('li');
                        allSubLis.forEach(li => li.classList.remove('is-active'));
                        
                        if (bestMatchSubLink.parentElement) {
                            bestMatchSubLink.parentElement.classList.add('is-active');
                        }
                    }
                }
                // KASUS FLAT
                else {
                    parent.classList.add('menu-item--current');
                }

            } else {
                // KASUS TIDAK AKTIF (Strict Cleanup)
                if (cachedSubMenu && parent.contains(cachedSubMenu)) {
                    parent.removeChild(cachedSubMenu);
                }
            }
        });
    };

    // --- EXECUTION ---
    renderMenuState();
    
    // LOG PENANDA BERFUNGSI
    console.log('[Wizdam Sidemenu]: Initialized successfully');

    window.addEventListener('hashchange', renderMenuState);
    window.addEventListener('popstate', renderMenuState);

    // Click Handler (UX Instan)
    sidebar.addEventListener('click', (e) => {
        const clickedParent = e.target.closest('.c-sidemenu > li');
        
        if (clickedParent) {
            const cachedSubMenu = menuCache.get(clickedParent);
            
            // Accordion Logic
            if (cachedSubMenu && !clickedParent.contains(cachedSubMenu)) {
                rootMenuItems.forEach(p => {
                    const otherSub = menuCache.get(p);
                    if (p !== clickedParent && otherSub && p.contains(otherSub)) {
                        p.removeChild(otherSub);
                        p.classList.remove('c-menu--current');
                        p.classList.remove('is-active');
                    }
                });
                clickedParent.classList.add('c-menu--current');
                clickedParent.classList.add('is-active');
                clickedParent.appendChild(cachedSubMenu);
            }
            // Flat Menu Logic
            else if (!cachedSubMenu) {
                rootMenuItems.forEach(p => p.classList.remove('menu-item--current'));
                clickedParent.classList.add('menu-item--current');
            }
        }
    });
});

/**
 * Style dropdow pencarian journal, type, subject dan date 
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.0.7
 */
document.addEventListener("DOMContentLoaded", function() {
    function initializeFacetButtons() {
        // console.log("Initializing facet buttons");

        var buttons = document.querySelectorAll('.c-facet__button');
        var isProcessing = false; // Lock to prevent excessive clicks

        /**
         * Close all expander elements and reset button states.
         */
        function closeAllExpanders() {
            var expanders = document.querySelectorAll('.c-facet-expander');
            expanders.forEach(function(expander) {
                if (!expander.classList.contains('u-js-hide')) {
                    expander.classList.add('u-js-hide');
                    expander.classList.remove('expanded');
                    expander.hidden = true;
                }
            });
            buttons.forEach(function(button) {
                button.setAttribute('aria-expanded', 'false');
                button.classList.remove('is-open');
            });
        }

        buttons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                if (isProcessing) return; // Prevent execution if already processing

                isProcessing = true;
                event.stopPropagation();
                var targetSelector = button.getAttribute('data-facet-target');
                if (targetSelector) {
                    var targetElement = document.querySelector(targetSelector);
                    if (targetElement) {
                        var isExpanded = button.getAttribute('aria-expanded') === 'true';

                        closeAllExpanders();

                        if (!isExpanded) {
                            targetElement.classList.remove('u-js-hide');
                            targetElement.hidden = false;
                            targetElement.offsetHeight; // Force reflow
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

                setTimeout(function() {
                    isProcessing = false; // Release the lock after a short delay
                }, 300); // Adjust the delay as needed
            });
        });

        document.addEventListener('click', function(event) {
            var expanders = document.querySelectorAll('.c-facet-expander');
            var isClickInsideExpander = false;

            expanders.forEach(function(expander) {
                if (expander.contains(event.target)) {
                    isClickInsideExpander = true;
                }
            });

            if (!isClickInsideExpander) {
                closeAllExpanders();
            }
        });
    }

    /**
     * Observe changes in the DOM and execute callback when mutations are detected.
     * @param {Node} obj - The DOM node to observe.
     * @param {function} callback - The callback function to execute on mutations.
     */
    function observeDOM(obj, callback) {
        var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        if (!obj || obj.nodeType !== 1) return; // validation
        if (MutationObserver) {
            // Define a new observer
            var obs = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length || mutation.removedNodes.length) {
                        callback(mutation);
                    }
                });
            });
            // Have the observer observe for changes in children
            obs.observe(obj, { childList: true, subtree: true });
        } else if (window.addEventListener) {
            obj.addEventListener('DOMNodeInserted', callback, false);
            obj.addEventListener('DOMNodeRemoved', callback, false);
        }
    }

    /**
     * Initialize the observer on specific elements
     */
    function initializeObserver() {
        observeDOM(document.getElementById('search-article-list'), function() {
            setTimeout(function() {
                initializeFacetButtons();
            }, 100); // Add a small delay to ensure the content is loaded
        });

        // Additionally observe the entire document body to ensure buttons added elsewhere are also initialized
        observeDOM(document.body, function() {
            setTimeout(function() {
                initializeFacetButtons();
            }, 100); // Add a small delay to ensure the content is loaded
        });
    }

    // Check if user has visited before
    if (!localStorage.getItem('facetButtonsInitialized')) {
        localStorage.setItem('facetButtonsInitialized', 'true');

        initializeFacetButtons();
        initializeObserver();
    } else {
        initializeFacetButtons();
    }
});

/**
 * Posisi Sort Search - wizdam_search.js
 */ 

/**
 * Menunda pemuatan tautan eksternal
 * 
 * @author Rochmady and Wizdam Team
 * @version 1.7.0
 */
$(document).ready(function() {
    var footer = $('#standardFooter');

    if (footer.length) {
        // Menemukan semua elemen <a> dengan href eksternal
        footer.find('a[href^="http"]').each(function() {
            var link = $(this);
            // Menyimpan href asli dalam data-href dan mengosongkan href sementara
            link.attr('data-href', link.attr('href'));
            link.attr('href', 'javascript:void(0)');
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

/**
 * Skrip tooltip dan popover dinamis untuk button info
 * 
 * @author Rochmady and Wizdam Team
 * @version 7.0.0
 */

(function() {
  // Simpan referensi button yang sedang ditampilkan popover-nya
  let activePopoverButton = null;
  // Simpan referensi button yang sedang di-hover
  let activeHoverButton = null;
  
  // Jalankan ketika DOM sudah siap
  function init() {
    // console.log('Inisialisasi tooltip dan popover...');
    
    // Buat elemen tooltip hover
    const hoverTooltip = document.createElement('div');
    hoverTooltip.id = 'dynamic-hover-tooltip';
    
    // Styling untuk tooltip hover
    Object.assign(hoverTooltip.style, {
      position: 'absolute',
      backgroundColor: '#e6f2ff',
      padding: '8px 16px',
      borderRadius: '16px',
      boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
      zIndex: '1000',
      maxWidth: '300px',
      display: 'none',
      fontFamily: 'Elsevier Sans, Nexus Sans, Arial, sans-serif',
      fontSize: '1.17em',
      lineHeight: '1.5',
      color: '#333',
      border: '1px solid #b3d9ff',
      transition: 'opacity 0.25s ease, transform 0.25s ease',
      opacity: '0',
      transform: 'translateY(-5px)'
    });
    
    // Tambahkan tooltip ke body
    document.body.appendChild(hoverTooltip);
    
    // Simpan semua popover dalam cache dan hapus dari DOM
    const popoverCache = {};
    document.querySelectorAll('.tooltip__Wrapper-sc-1lc2ea0-0 .popover-content').forEach(popover => {
      // Simpan referensi ke wrapper asli
      const wrapper = popover.closest('.tooltip__Wrapper-sc-1lc2ea0-0');
      if (wrapper) {
        const button = wrapper.querySelector('button');
        if (button) {
          const buttonId = button.getAttribute('data-button-id') || ('button-' + Math.random().toString(36).substr(2, 9));
          button.setAttribute('data-button-id', buttonId);
          
          // Clone popover sebelum dihapus
          popoverCache[buttonId] = popover.cloneNode(true);
          
          // Hapus dari DOM
          popover.parentNode.removeChild(popover);
        }
      }
    });
    
    // Seleksi semua tombol info dalam wrapper
    const infoButtons = document.querySelectorAll('.tooltip__Wrapper-sc-1lc2ea0-0 button');
    
    // console.log('Ditemukan', infoButtons.length, 'tombol info');
    
    // Pasang event listeners untuk setiap tombol
    infoButtons.forEach(function(button) {
      // Beri ID unik untuk tombol jika belum ada
      if (!button.hasAttribute('data-button-id')) {
        const buttonId = 'button-' + Math.random().toString(36).substr(2, 9);
        button.setAttribute('data-button-id', buttonId);
      }
      
      // Set nilai default atribut
      if (!button.hasAttribute('aria-expanded')) {
        button.setAttribute('aria-expanded', 'false');
      }
      if (!button.hasAttribute('aria-haspopup')) {
        button.setAttribute('aria-haspopup', 'false');
      }
      
      // Hover event - mouseenter
      button.addEventListener('mouseenter', function() {
        // Simpan referensi button yang sedang di-hover
        activeHoverButton = this;
        
        // Jika button sedang menampilkan popover, tidak perlu tampilkan tooltip
        if (this.getAttribute('aria-expanded') === 'true') {
          return;
        }
        
        const ariaLabel = this.getAttribute('aria-label');
        
        if (ariaLabel) {
          // Ubah status aria-haspopup menjadi true hanya jika tidak expanded
          if (this.getAttribute('aria-expanded') !== 'true') {
            this.setAttribute('aria-haspopup', 'true');
          }
          
          // Tampilkan tooltip dengan isi dari aria-label
          hoverTooltip.textContent = ariaLabel;
          
          // Posisikan tooltip di bawah tombol
          const rect = this.getBoundingClientRect();
          hoverTooltip.style.top = (rect.bottom + window.scrollY + 5) + 'px';
          hoverTooltip.style.left = (rect.left + window.scrollX) + 'px';
          
          // Tampilkan tooltip dengan animasi
          hoverTooltip.style.display = 'block';
          // Trigger reflow
          void hoverTooltip.offsetWidth;
          hoverTooltip.style.opacity = '1';
          hoverTooltip.style.transform = 'translateY(0)';
        }
      });
      
      // Mouse leave event
      button.addEventListener('mouseleave', function() {
        // Reset referensi button hover
        if (activeHoverButton === this) {
          activeHoverButton = null;
        }
        
        // Jika tombol tidak dalam keadaan expanded, kembalikan aria-haspopup ke false
        if (this.getAttribute('aria-expanded') === 'false') {
          this.setAttribute('aria-haspopup', 'false');
        }
        
        // Sembunyikan tooltip dengan animasi
        hoverTooltip.style.opacity = '0';
        hoverTooltip.style.transform = 'translateY(-5px)';
        setTimeout(() => {
          if (hoverTooltip.style.opacity === '0') {
            hoverTooltip.style.display = 'none';
          }
        }, 250);
      });
      
      // Click event
      button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const buttonId = this.getAttribute('data-button-id');
        const wrapper = this.closest('.tooltip__Wrapper-sc-1lc2ea0-0');
        
        // Cek status tombol
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        
        // Jika ada tombol aktif lain, tutup popover-nya
        if (activePopoverButton && activePopoverButton !== this) {
          closePopover(activePopoverButton);
        }
        
        if (isExpanded) {
          // Tutup popover jika sudah terbuka
          closePopover(this);
          activePopoverButton = null;
        } else {
          // Buka popover
          this.setAttribute('aria-expanded', 'true');
          
          // PENTING: Jika sedang expand, aria-haspopup tetap false
          // (Tidak mengubah aria-haspopup menjadi true saat expanded)
          
          // Cek apakah ada di cache
          if (popoverCache[buttonId] && wrapper) {
            // Clone popover dari cache
            const popover = popoverCache[buttonId].cloneNode(true);
            
            // Set style untuk animasi
            Object.assign(popover.style, {
              opacity: '0',
              transform: 'translateY(10px)',
              transition: 'opacity 0.25s ease, transform 0.25s ease'
            });
            
            // Pastikan tidak memiliki class u-js-hide
            popover.classList.remove('u-js-hide');
            
            // Tambahkan ke wrapper
            wrapper.appendChild(popover);
            
            // Trigger reflow untuk animasi
            void popover.offsetWidth;
            
            // Tampilkan dengan animasi
            popover.style.opacity = '1';
            popover.style.transform = 'translateY(0)';
            
            // Catat tombol aktif
            activePopoverButton = this;
            
            // Sembunyikan tooltip jika sedang ditampilkan
            if (hoverTooltip.style.display === 'block') {
              hoverTooltip.style.opacity = '0';
              hoverTooltip.style.transform = 'translateY(-5px)';
              setTimeout(() => {
                hoverTooltip.style.display = 'none';
              }, 250);
            }
          } else {
            console.error('Popover tidak ditemukan di cache untuk button:', buttonId);
          }
        }
      });
    });
    
    // Tutup popover ketika mengklik di luar
    document.addEventListener('click', function(e) {
      // Cek apakah yang diklik adalah tombol info atau ada di dalam popover
      if (e.target.closest('.tooltip__Wrapper-sc-1lc2ea0-0 button') || 
          e.target.closest('.popover-content')) {
        return;
      }
      
      // Tutup popover aktif jika ada
      if (activePopoverButton) {
        closePopover(activePopoverButton);
        activePopoverButton = null;
      }
    });
    
    // Tambahkan event mouseover global untuk menangani hover pada elemen lain
    document.addEventListener('mouseover', function(e) {
      // Cek apakah yang di-hover adalah button yang dimonitor
      const hoveredButton = e.target.closest('.tooltip__Wrapper-sc-1lc2ea0-0 button');
      
      // Jika tidak ada hover pada button yang dimonitor, sembunyikan tooltip
      if (!hoveredButton && hoverTooltip.style.display === 'block') {
        hoverTooltip.style.opacity = '0';
        hoverTooltip.style.transform = 'translateY(-5px)';
        setTimeout(() => {
          hoverTooltip.style.display = 'none';
        }, 250);
      }
    });
    
    // console.log('Inisialisasi selesai dengan cache untuk', Object.keys(popoverCache).length, 'popover');
    
    /**
     * Fungsi untuk menutup popover
     * @param {HTMLElement} button - Tombol yang terkait dengan popover
     */
    function closePopover(button) {
      if (!button) return;
      
      button.setAttribute('aria-expanded', 'false');
      
      // PENTING: Ketika popover ditutup, periksa status hover
      // Hanya set aria-haspopup true jika button sedang di-hover
      if (activeHoverButton === button) {
        button.setAttribute('aria-haspopup', 'true');
      } else {
        button.setAttribute('aria-haspopup', 'false');
      }
      
      const wrapper = button.closest('.tooltip__Wrapper-sc-1lc2ea0-0');
      if (wrapper) {
        const popover = wrapper.querySelector('.popover-content');
        if (popover) {
          // Animasi fade out
          popover.style.opacity = '0';
          popover.style.transform = 'translateY(10px)';
          
          // Hapus dari DOM setelah animasi selesai
          setTimeout(() => {
            if (popover.parentNode) {
              popover.parentNode.removeChild(popover);
            }
          }, 250);
        }
      }
    }
  }
  
  // Pastikan DOM sudah dimuat sebelum menjalankan inisialisasi
  if (document.readyState !== 'loading') {
    init();
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})();