/**
 * templates/js/classical-modern--style.js
 *
 * Copyright (c) 2013-2025 Sangia Publishing
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * Create with DeepSeek AI
 *
 */

/**
 * Inisialisasi Back to top.
 */
jQuery(document).ready(function($){
    const backToTop = $('.back-to-top');

    // Show or hide the back-to-top button based on scroll position
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 200) { // Adjust the scroll value as needed
            backToTop.fadeIn();
        } else {
            backToTop.fadeOut();
        }
    });

    // Scroll smoothly to the top when the button is clicked
    backToTop.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 500);
    });
});

/**
 * Inisialisasi IP Address user.
 */
async function getIPAddress() {
  try {
    const response = await fetch('https://api.ipify.org?format=json');
    const data = await response.json();
    document.getElementById('diagnostic-ip').textContent = `IP Address: ${data.ip}`;
  } catch (error) {
    console.error('Error fetching IP address:', error);
  }
}

document.addEventListener('DOMContentLoaded', getIPAddress);

/**
 * Inisialisasi event Lazyload.
 */
// lazyload.js
document.addEventListener("DOMContentLoaded", function() {
    function removeLazyAttributes(element) {
        element.removeAttribute('loading');
        if (element.classList.contains("lazyload")) {
            element.classList.remove("lazyload");
            if (element.classList.length === 0) {
                element.classList.add("sangia");
            }
        }
        element.classList.remove("lazyloaded");
    }

    function lazyLoadImages() {
        if ('IntersectionObserver' in window) {
            let lazyImages = document.querySelectorAll("img.lazyload");
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.src;
                        lazyImage.onload = () => {
                            removeLazyAttributes(lazyImage);
                        };
                        lazyImage.classList.add("lazyloaded");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            let lazyImages = document.querySelectorAll("img.lazyload");
            lazyImages.forEach(function(lazyImage) {
                lazyImage.src = lazyImage.src;
                lazyImage.onload = () => {
                    removeLazyAttributes(lazyImage);
                };
                lazyImage.classList.add("lazyloaded");
            });
        }
    }

    function lazyLoadContent() {
        if ('IntersectionObserver' in window) {
            let lazyContents = document.querySelectorAll(".u-container, .main-contents, .live-area, .issue-contents");
            let lazyContentObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyContent = entry.target;
                        lazyContent.classList.add("content-loaded");
                        lazyContentObserver.unobserve(lazyContent);
                    }
                });
            });

            lazyContents.forEach(function(lazyContent) {
                lazyContentObserver.observe(lazyContent);
            });
        } else {
            let lazyContents = document.querySelectorAll(".u-container, .main-contents, .live-area, .issue-contents");
            lazyContents.forEach(function(lazyContent) {
                lazyContent.classList.add("content-loaded");
            });
        }
    }

    // Delay content load
    function delayContentLoad() {
        setTimeout(() => {
            lazyLoadContent();
            setTimeout(() => {
                lazyLoadImages();
            }, 1000); // Adjust the delay as needed
        }, 1000); // Adjust the delay as needed
    }

    delayContentLoad();
});

/**
 * Menginisialisasi dan mengelola perilaku toggle untuk elemen flyout.
 * Kode ini akan:
 * 1. Menemukan semua button dengan atribut aria-expanded
 * 2. Menetapkan nilai default jika belum ada
 * 3. Menambahkan event listener untuk toggle status expanded/collapsed
 * @module FlyoutToggle
 */
/**
 * Enhanced Flyout Component with Mutual Exclusive Behavior
 * @description Mengatur toggle behavior flyout dengan saling menutup antar flyout
 * @version 1.3.0
 */
(function() {
  'use strict';

  // Private variables
  const FLYOUT_SELECTORS = {
    lang: '.lang.flyout',
    search: '#search-options.flyout'
  };

  /**
   * Close other flyouts when one is opened
   * @param {HTMLElement} currentFlyout - Flyout yang sedang di-toggle
   * @private
   */
  function _closeOtherFlyouts(currentFlyout) {
    // Determine which flyout to close
    const isLangFlyout = currentFlyout.matches(FLYOUT_SELECTORS.lang);
    const selectorToClose = isLangFlyout ? FLYOUT_SELECTORS.search : FLYOUT_SELECTORS.lang;
    
    // Find and close the other flyout
    const otherFlyout = document.querySelector(selectorToClose);
    if (otherFlyout && otherFlyout.classList.contains('is-open')) {
      const button = otherFlyout.querySelector('[aria-expanded="true"]');
      if (button) {
        button.setAttribute('aria-expanded', 'false');
        otherFlyout.classList.remove('is-open');
      }
    }
  }

  /**
   * Initialize flyout with mutual exclusive behavior
   * @private
   */
  function _initFlyout(flyout) {
    const button = flyout.querySelector('[aria-expanded]');
    if (!button) return;

    // Set initial state
    if (!button.hasAttribute('aria-expanded')) {
      button.setAttribute('aria-expanded', 'false');
    }

    button.addEventListener('click', function(e) {
      e.stopPropagation();
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      const newState = !isExpanded;

      // Toggle current flyout
      this.setAttribute('aria-expanded', String(newState));
      flyout.classList.toggle('is-open', newState);

      // Close the other flyout if opening this one
      if (newState) {
        _closeOtherFlyouts(flyout);
      }
    });
  }

  /**
   * Main initialization function
   * @public
   */
  function initMutualFlyouts() {
    // Initialize both flyouts
    const langFlyout = document.querySelector(FLYOUT_SELECTORS.lang);
    const searchFlyout = document.querySelector(FLYOUT_SELECTORS.search);

    if (langFlyout) _initFlyout(langFlyout);
    if (searchFlyout) _initFlyout(searchFlyout);

    // Handle clicks outside to close all flyouts
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.flyout')) {
        [langFlyout, searchFlyout].forEach(flyout => {
          if (flyout && flyout.classList.contains('is-open')) {
            const button = flyout.querySelector('[aria-expanded="true"]');
            if (button) {
              button.setAttribute('aria-expanded', 'false');
              flyout.classList.remove('is-open');
            }
          }
        });
      }
    });

    // Handle scroll to close all flyouts
    window.addEventListener('scroll', function() {
      [langFlyout, searchFlyout].forEach(flyout => {
        if (flyout && flyout.classList.contains('is-open')) {
          const button = flyout.querySelector('[aria-expanded="true"]');
          if (button) {
            button.setAttribute('aria-expanded', 'false');
            flyout.classList.remove('is-open');
          }
        }
      });
    }, { passive: true });
  }

  // Self-initializing
  if (document.readyState !== 'loading') {
    initMutualFlyouts();
  } else {
    document.addEventListener('DOMContentLoaded', initMutualFlyouts);
  }
})();

/**
 * Header Toggle Module
 * @namespace HeaderToggle
 * @description Mengelola toggle behavior untuk header, pencarian, dan menu
 */
(function() {
  'use strict';

  /**
   * Inisialisasi modul header toggle
   * @function init
   * @memberof HeaderToggle
   */
  function init() {
    // Variabel privat modul
    const header = document.getElementById('header');
    const searchBtn = document.querySelector('.pillow-btn.open-search');
    const menuBtn = document.querySelector('.pillow-btn.open-menu');

    if (!header || !searchBtn || !menuBtn) return;

    // Flag untuk mencegah konflik event
    let isProcessing = false;

    /**
     * Reset header ke state awal
     * @private
     */
    function resetHeader() {
      if (isProcessing) return;
      isProcessing = true;

      header.classList.remove('is-open', 'js-show');
      header.classList.add('show');
      searchBtn.setAttribute('aria-expanded', 'false');
      menuBtn.setAttribute('aria-expanded', 'false');

      setTimeout(() => { isProcessing = false; }, 10);
    }

    /**
     * Handle search button click
     * @private
     * @param {Event} e
     */
    function handleSearchClick(e) {
      e.stopImmediatePropagation();
      if (isProcessing) return;
      isProcessing = true;

      if (header.classList.contains('is-open')) {
        resetHeader();
      } else {
        header.classList.remove('js-show', 'show');
        header.classList.add('is-open');
        searchBtn.setAttribute('aria-expanded', 'true');
        menuBtn.setAttribute('aria-expanded', 'false');
      }

      setTimeout(() => { isProcessing = false; }, 10);
    }

    /**
     * Handle menu button click
     * @private
     * @param {Event} e
     */
    function handleMenuClick(e) {
      e.stopImmediatePropagation();
      if (isProcessing) return;
      isProcessing = true;

      if (header.classList.contains('js-show')) {
        resetHeader();
      } else {
        header.classList.remove('is-open', 'show');
        header.classList.add('js-show');
        menuBtn.setAttribute('aria-expanded', 'true');
        searchBtn.setAttribute('aria-expanded', 'false');
      }

      setTimeout(() => { isProcessing = false; }, 10);
    }

    /**
     * Handle click outside
     * @private
     * @param {Event} e
     */
    function handleOutsideClick(e) {
      if (!header.contains(e.target) && 
          e.target !== searchBtn && 
          e.target !== menuBtn) {
        resetHeader();
      }
    }

    // Event listeners dengan options { passive: true } untuk performa
    searchBtn.addEventListener('click', handleSearchClick, { passive: false });
    menuBtn.addEventListener('click', handleMenuClick, { passive: false });
    document.addEventListener('click', handleOutsideClick, { passive: true });
    window.addEventListener('scroll', resetHeader, { passive: true });

    // Cleanup function untuk menghapus event listeners saat tidak diperlukan
    return function cleanup() {
      searchBtn.removeEventListener('click', handleSearchClick);
      menuBtn.removeEventListener('click', handleMenuClick);
      document.removeEventListener('click', handleOutsideClick);
      window.removeEventListener('scroll', resetHeader);
    };
  }

  // Delay inisialisasi untuk menghindari blocking render
  if (document.readyState !== 'loading') {
    setTimeout(init, 0);
  } else {
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(init, 0);
    });
  }

  // Ekspos fungsi init ke global scope jika diperlukan
  window.HeaderToggle = { init: init };
})();

/**
 * Modul untuk menangani tampilan loading yang aman dan tidak mengganggu kode lain
 * @module SafeSearchLoadingHandler
 */
(function() {
    'use strict';

    // Flag untuk memastikan inisialisasi hanya sekali
    if (window.__searchLoadingHandlerInitialized) return;
    window.__searchLoadingHandlerInitialized = true;

    /**
     * Menampilkan loading indicator dengan cara yang aman
     * @param {Event} event - Event object
     */
    const handleSearchAction = function(event) {
        // Dapatkan elemen yang terkait
        const submitButton = event.currentTarget || event.target;
        const form = submitButton.closest('form');
        const loading = form ? form.querySelector('.loading.js-hidden') : 
                      document.querySelector('.loading.js-hidden');
        
        // Tampilkan loading jika ada
        if (loading) {
            loading.classList.remove('js-hidden');
        }
    };

    /**
     * Setup event listeners dengan cara yang non-destructive
     */
    const setupEventListeners = function() {
        const searchButtons = document.querySelectorAll('.search-submit');
        
        searchButtons.forEach(button => {
            // Simpan handler lama jika ada
            const oldClick = button.onclick;
            const oldKeyPress = button.onkeypress;

            // Reset handler
            button.onclick = null;
            button.onkeypress = null;

            // Tambahkan event listener baru
            button.addEventListener('click', function(e) {
                // Jalankan handler lama jika ada
                if (oldClick) oldClick(e);
                handleSearchAction(e);
            });

            button.addEventListener('keypress', function(e) {
                // Jalankan handler lama jika ada
                if (oldKeyPress) oldKeyPress(e);
                if (e.key === 'Enter') {
                    handleSearchAction(e);
                }
            });
        });
    };

    // Inisialisasi saat DOM siap
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(setupEventListeners, 0);
    } else {
        document.addEventListener('DOMContentLoaded', setupEventListeners);
    }

    // Pastikan loading disembunyikan saat halaman dimuat
    window.addEventListener('load', function() {
        document.querySelectorAll('.loading').forEach(el => {
            el.classList.add('js-hidden');
        });
    });
})();

/**
 * Modul untuk menangani pencarian global dengan isolasi scope penuh
 * @version 1.1.0
 * @license MIT
 */
(function() {
    'use strict';

    const config = {
        formSelector: '#global-search',
        linkSelector: '#global-search-new',
        inputSelector: '#query',
        removeClass: 'contains-new-search-link',
        removeLinkAfterClick: true, // Fitur baru: hapus link setelah klik
        debug: false
    };

    if (window.searchConfig) {
        Object.assign(config, window.searchConfig);
    }

    /**
     * Handler untuk klik pencarian baru
     * @param {Event} event
     */
    function handleSearchLinkClick(event) {
        if (shouldIgnoreClick(event)) return;
        event.preventDefault();

        const form = document.querySelector(config.formSelector);
        const input = document.querySelector(config.inputSelector);
        const link = event.currentTarget;

        if (!form || !input || !link) {
            if (config.debug) {
                console.warn('Elemen tidak ditemukan', { form, input, link });
            }
            return;
        }

        window.requestAnimationFrame(() => {
            // 1. Hapus class dari form
            form.classList.remove(config.removeClass);
            
            // 2. Reset input
            input.value = '';
            input.focus();
            
            // 3. Hapus link jika di-config
            if (config.removeLinkAfterClick) {
                link.remove();
            }

            if (config.debug) {
                console.log('Aksi pencarian selesai', { 
                    form, 
                    input, 
                    linkRemoved: config.removeLinkAfterClick 
                });
            }
        });
    }

    /**
     * Deteksi klik yang harus diabaikan
     * @param {Event} event 
     * @returns {boolean}
     */
    function shouldIgnoreClick(event) {
        return (
            event.button !== 0 || 
            event.ctrlKey || 
            event.shiftKey || 
            event.metaKey || 
            event.altKey
        );
    }

    /**
     * Inisialisasi event listeners
     */
    function setup() {
        const searchLink = document.querySelector(config.linkSelector);
        
        if (!searchLink) {
            if (config.debug) {
                console.warn('Link pencarian tidak ditemukan');
            }
            return;
        }

        searchLink.addEventListener('click', handleSearchLinkClick, { 
            passive: false 
        });
        
        if (config.debug) {
            console.log('Search handler diinisialisasi pada:', searchLink);
        }
    }

    // Helper DOM ready
    function domReady(fn) {
        document.readyState !== 'loading' 
            ? setTimeout(fn, 0) 
            : document.addEventListener('DOMContentLoaded', fn);
    }

    domReady(setup);

})();

/**
 * Inisialisasi event ketika dokumen sudah siap.
 * Main document ready handler that sets up expander functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Toggles the visibility of expander content
     * @param {HTMLElement} expander - The expander element to toggle
     */
    function toggleExpander(expander) {
        const expanderContent = expander.querySelector('.expander-content');
        if (expander.classList.contains('expander-open')) {
            expander.classList.remove('expander-open');
            expanderContent.classList.remove('show');
        } else {
            expander.classList.add('expander-open');
            expanderContent.classList.add('show');
        }
    }

    // Add event listener for all expanders
    const expanders = document.querySelectorAll('.expander');
    expanders.forEach(function(expander) {
        const expanderTitle = expander.querySelector('.expander-title');
        expanderTitle.addEventListener('click', function() {
            toggleExpander(expander);
        });
    });

    // Add scroll event listener for bar-wrapper
    const barWrapper = document.querySelector('.bar-wrapper');
    const barDock = document.querySelector('.bar-dock');
    const barWrapperHeight = barWrapper.offsetHeight;

    window.addEventListener('scroll', function() {
        if (window.scrollY > barDock.offsetTop + barWrapperHeight) {
            barWrapper.classList.add('bar-scrolled');
        } else {
            barWrapper.classList.remove('bar-scrolled');
        }
    });
});

/**
 * Search functionality module
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('global-search');
    const loadingImage = document.querySelector('.loading');
    const searchInput = document.getElementById('query');
    const submitButton = document.getElementById('search');
    const resultsList = document.getElementById('results-list');

    /**
     * Shows the loading indicator
     */
    function showLoading() {
        loadingImage.classList.remove('js-hidden');
    }

    /**
     * Hides the loading indicator
     */
    function hideLoading() {
        loadingImage.classList.add('js-hidden');
    }

    /**
     * Handles search events and observes results list changes
     */
    function handleSearchEvent() {
        showLoading();

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id === 'results-list' && mutation.target.children.length > 0) {
                    hideLoading();
                    observer.disconnect();
                }
            });
        });

        observer.observe(resultsList, { childList: true });
    }

    // Event listeners for search interactions
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            showLoading();
        });
    }

    if (submitButton) {
        submitButton.addEventListener('click', function(event) {
            showLoading();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                showLoading();
            }
        });
    }
});

/**
 * Search input handling module
 */
document.addEventListener('DOMContentLoaded', function() {
    const queryInput = document.getElementById('query');
    const noResultsMessage = document.getElementById('no-results-message');
    const searchTips = document.querySelector('.search-tips');
    const resultsList = document.getElementById('results-list');

    queryInput.addEventListener('input', function() {
        const query = queryInput.value.trim();

        // Show/hide search tips based on input
        if (query === "") {
            searchTips.classList.remove('hidden');
        } else {
            searchTips.classList.add('hidden');
        }

        // Show/hide no results message
        const hasResults = checkResultsList();
        if (!hasResults) {
            noResultsMessage.classList.remove('hidden');
        } else {
            noResultsMessage.classList.add('hidden');
        }
    });

    /**
     * Checks if there are any results in the results list
     * @returns {boolean} True if results exist, false otherwise
     */
    function checkResultsList() {
        return resultsList.children.length > 0;
    }
});

/**
 * jQuery module for handling results access and notifications
 */
$(document).ready(function() {
    const fadeDuration = 800; // Duration for the fade effect

    // Load preferences from local storage
    if (localStorage.getItem('results-only-access-checkbox') === 'checked') {
        $("#results-only-access-checkbox").prop('checked', true);
    } else {
        $("#results-only-access-checkbox").prop('checked', false);
    }

    const savedPosition = localStorage.getItem('notification-position') || 'top-right';
    const savedColor = localStorage.getItem('notification-color') || '#4caf50';
    $("#notification-position").val(savedPosition);
    $("#notification-color").val(savedColor);

    /**
     * Checks for no-access items and shows/hides the access control section
     */
    function checkNoAccess() {
        if ($("#results-list .no-access").length > 0) {
            $("#results-only-access").fadeIn(fadeDuration);
        } else {
            $("#results-only-access").fadeOut(fadeDuration);
        }
    }

    /**
     * Toggles visibility of no-access content based on checkbox state
     */
    function toggleNoAccessContent() {
        if ($("#results-only-access-checkbox").is(":checked")) {
            $("#results-list .no-access").each(function(index) {
                $(this).delay(index * 400).fadeIn(fadeDuration);
            });
        } else {
            $("#results-list .no-access").each(function(index) {
                $(this).delay(index * 400).fadeOut(fadeDuration);
            });
        }
    }

    /**
     * Shows a notification message
     * @param {string} message - The message to display
     */
    function showNotification(message) {
        $('#notification').text(message).fadeIn().delay(2000).fadeOut();
    }

    // Initial setup
    checkNoAccess();
    toggleNoAccessContent();
    updateNotificationStyle();

    // Event handlers
    $("#results-only-access-checkbox").change(function() {
        toggleNoAccessContent();
        if ($(this).is(":checked")) {
            localStorage.setItem('results-only-access-checkbox', 'checked');
            showNotification("Status saved to local storage.");
        } else {
            localStorage.setItem('results-only-access-checkbox', 'unchecked');
            showNotification("Status saved to local storage.");
        }
    });

    $("#clear-storage-btn").click(function() {
        localStorage.removeItem('results-only-access-checkbox');
        $("#results-only-access-checkbox").prop('checked', false);
        toggleNoAccessContent();
        showNotification("Status cleared from local storage.");
    });

    /**
     * Updates notification style based on user preferences
     */
    function updateNotificationStyle() {
        const position = $("#notification-position").val();
        const color = $("#notification-color").val();
        
        localStorage.setItem('notification-position', position);
        localStorage.setItem('notification-color', color);

        let positionStyles = {
            top: '',
            bottom: '',
            left: '',
            right: ''
        };

        switch (position) {
            case 'top-right':
                positionStyles.top = '10px';
                positionStyles.right = '10px';
                break;
            case 'top-left':
                positionStyles.top = '10px';
                positionStyles.left = '10px';
                break;
            case 'bottom-right':
                positionStyles.bottom = '10px';
                positionStyles.right = '10px';
                break;
            case 'bottom-left':
                positionStyles.bottom = '10px';
                positionStyles.left = '10px';
                break;
        }

        $('#notification').css({
            ...positionStyles,
            backgroundColor: color
        });
    }

    // Apply initial notification style
    updateNotificationStyle();

    // Event listeners for notification style changes
    $("#notification-position").change(updateNotificationStyle);
    $("#notification-color").change(updateNotificationStyle);
});

/**
 * IMPROVED jQuery module for CSV export functionality
 */
$(document).ready(function() {
    const CSVExporter = {
        init: function() {
            this.$downloadBtn = $('#tool-download');
            this.$resultsList = $('#results-list');
            this.$alert = $('#alert');
            this.$searchInput = $('#query');
            
            this.$downloadBtn.removeAttr('href');
            this.setupDownloadHandler();
        },

        setupDownloadHandler: function() {
            this.$downloadBtn.on('click', (e) => {
                e.preventDefault();
                this.exportToCSV();
            });
        },

        exportToCSV: function() {
            const csvContent = this.generateCSV();
            if (!csvContent) return;
            
            this.downloadCSV(csvContent);
        },

        generateCSV: function() {
            const items = this.$resultsList.find('.content-item');
            if (items.length === 0) {
                this.showAlert("No search results available to download.", "error");
                return null;
            }

            // CSV Headers (adjust based on your HTML structure)
            const headers = ["No", "Title", "Authors", "Date", "Description"];
            let csvContent = headers.join(",") + "\r\n";

            items.each((index, item) => {
                const $item = $(item);
                const row = [
                    index + 1,
                    this.cleanText($item.find('.title').text()),
                    this.cleanText($item.find('.authors').text()),
                    this.cleanText($item.find('.date').text()),
                    this.cleanText($item.find('.description').text())
                ].map(this.formatCSVField).join(",");
                
                csvContent += row + "\r\n";
            });

            return csvContent;
        },

        cleanText: function(text) {
            return text.replace(/\s+/g, ' ').trim();
        },

        formatCSVField: function(field) {
            if (typeof field !== 'string') return '""';
            if (/[",\n\r]/.test(field)) {
                return `"${field.replace(/"/g, '""')}"`;
            }
            return field;
        },

        downloadCSV: function(csvContent) {
            try {
                const blob = new Blob(["\uFEFF" + csvContent], { 
                    type: 'text/csv;charset=utf-8;' 
                });
                const url = URL.createObjectURL(blob);
                
                // Generate dynamic filename based on URL and query
                const filename = this.generateDynamicFilename();
                
                this.$downloadBtn.attr({
                    'href': url,
                    'download': filename,
                    'target': '_blank'
                });
                
                setTimeout(() => {
                    this.$downloadBtn[0].click();
                    
                    setTimeout(() => {
                        URL.revokeObjectURL(url);
                        this.$downloadBtn.removeAttr('href download target');
                    }, 500);
                }, 100);
                
                this.showAlert(`Downloading ${filename}...`, "success");
            } catch (error) {
                console.error("CSV Export Error:", error);
                this.showAlert("Failed to generate CSV file", "error");
            }
        },

        generateDynamicFilename: function() {
            // Extract journal name from URL path (e.g., 'AGRIKAN' or 'ISLE')
            const urlParts = window.location.pathname.split('/');
            const journalName = urlParts.length >= 3 ? urlParts[2] : 'results';
            
            // Get search query from input or URL parameter
            let query = this.$searchInput.val()?.trim();
            if (!query) {
                const urlParams = new URLSearchParams(window.location.search);
                query = urlParams.get('query')?.trim();
            }
            
            // Clean query for filename
            const cleanQuery = query 
                ? query.toLowerCase()
                      .replace(/[^a-z0-9]+/g, '-')
                      .replace(/^-|-$/g, '')
                : 'results';
            
            return `Sangia-${journalName}-${cleanQuery}.csv`;
        },

        showAlert: function(message, type) {
            this.$alert.removeClass().addClass(`alert alert-${type}`)
                .text(message).show().delay(3000).fadeOut();
        }
    };

    // Initialize CSV Exporter
    CSVExporter.init();
});

/**
 * jQuery module for sorting functionality
 */
$(document).ready(function() {
    /**
     * Sorts items by date
     * @param {string} order - The sort order ('newest' or 'oldest')
     */
    function sortItems(order) {
        var $list = $('#results-list');
        var $items = $list.children('li');

        $items.sort(function(a, b) {
            var dateA = $(a).find('.enumeration').attr('datetime');
            var dateB = $(b).find('.enumeration').attr('datetime');
            return order === 'newest' ? new Date(dateB) - new Date(dateA) : new Date(dateA) - new Date(dateB);
        });

        animateList($list, $items);
    }

    /**
     * Sorts items by relevance to search query
     */
    function sortRelevance() {
        var $list = $('#results-list');
        var $items = $list.children('li');
        var query = $('#query').val().toLowerCase().split(' ');
        var keywords = ['agriculture', 'farming', 'crop', 'yield', 'improvement'].concat(query);

        $items.sort(function(a, b) {
            var snippetA = $(a).find('.snippet').text().toLowerCase();
            var snippetB = $(b).find('.snippet').text().toLowerCase();
            var titleA = $(a).find('.title').text().toLowerCase();
            var titleB = $(b).find('.title').text().toLowerCase();
            var relevanceA = getRelevanceScore(snippetA, titleA, keywords);
            var relevanceB = getRelevanceScore(snippetB, titleB, keywords);
            return relevanceB - relevanceA;
        });

        animateList($list, $items);
    }

    /**
     * Calculates relevance score for an item
     * @param {string} snippet - The snippet text
     * @param {string} title - The title text
     * @param {string[]} keywords - Array of keywords to match against
     * @returns {number} The relevance score
     */
    function getRelevanceScore(snippet, title, keywords) {
        return keywords.reduce(function(score, keyword) {
            var regex = new RegExp('\\b' + keyword + '\\b', 'gi');
            var snippetMatches = snippet.match(regex);
            var titleMatches = title.match(regex);
            return score + (snippetMatches ? snippetMatches.length : 0) + (titleMatches ? titleMatches.length : 0);
        }, 0);
    }

    /**
     * Animates the list during sorting
     * @param {jQuery} $list - The list element
     * @param {jQuery} $items - The items to animate
     */
    function animateList($list, $items) {
        $list.fadeOut(300, function() {
            $items.each(function(index, item) {
                $(item).css({
                    'transform': 'translateY(20px)',
                    'opacity': '0'
                });
            });

            $list.empty().append($items);

            $items.each(function(index, item) {
                setTimeout(function() {
                    $(item).css({
                        'transform': 'translateY(0)',
                        'opacity': '1'
                    });
                }, index * 100);
            });

            $list.fadeIn(300);
        });
    }

    /**
     * Updates the active sorting button
     * @param {HTMLElement} button - The button to mark as active
     */
    function updateActiveButton(button) {
        $('.sorting .btn').removeClass('selected');
        $(button).addClass('selected');
    }

    // Event handlers for sorting buttons
    $('a.btn.newest').on('click', function(event) {
        event.preventDefault();
        sortItems('newest');
        updateActiveButton(this);
    });

    $('a.btn.oldest').on('click', function(event) {
        event.preventDefault();
        sortItems('oldest');
        updateActiveButton(this);
    });

    $('a.btn.relevance').on('click', function(event) {
        event.preventDefault();
        sortRelevance();
        updateActiveButton(this);
    });

    $('#query').on('input', function() {
        sortRelevance();
        updateActiveButton('a.btn.relevance');
    });
});

/**
 * jQuery module for flyout menu functionality
 */
jQuery(document).ready(function($){
    $('.flyout-caption, .pillow-btn').click(function(event) {
        event.stopPropagation();
        const $flyout = $(this).closest('.flyout');
        $flyout.toggleClass('show');
        const expanded = $flyout.hasClass('show');
        $(this).attr('aria-expanded', expanded);

        // Close other open flyouts
        $('.flyout').not($flyout).removeClass('show');
        $('.flyout-caption, .pillow-btn').not(this).attr('aria-expanded', 'false');
    });

    // Close the dropdown when clicking outside of it
    $(document).click(function(event) {
        if (!$(event.target).closest('.flyout').length) {
            $('.flyout').removeClass('show');
            $('.flyout-caption, .pillow-btn').attr('aria-expanded', 'false');
        }
    });
});
