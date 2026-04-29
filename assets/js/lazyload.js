/**
 * @fileoverview Modul ini menangani lazy loading untuk gambar dan konten, 
 * menggunakan Intersection Observer API jika tersedia, dengan fallback untuk browser yang tidak mendukung.
 * @author Rochmady and Wizdam team
 * @version 1.0
 */

document.addEventListener("DOMContentLoaded", function() {
    /**
     * Menghapus atribut dan kelas terkait lazy loading dari elemen
     * @param {HTMLElement} element - Elemen DOM yang akan dihapus atribut lazy loadingnya
     * @returns {void}
     */
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

    /**
     * Menangani lazy loading untuk gambar menggunakan Intersection Observer jika tersedia,
     * dengan fallback untuk browser yang tidak mendukung
     * @returns {void}
     */
    function lazyLoadImages() {
        if ('IntersectionObserver' in window) {
            /** @type {NodeListOf<HTMLImageElement>} */
            let lazyImages = document.querySelectorAll("img.lazyload");
            
            /**
             * @type {IntersectionObserver}
             * Observer untuk memantau gambar yang masuk viewport
             */
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        /** @type {HTMLImageElement} */
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.src; // Memicu pemuatan gambar
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
            // Fallback untuk browser yang tidak mendukung Intersection Observer
            /** @type {NodeListOf<HTMLImageElement>} */
            let lazyImages = document.querySelectorAll("img.lazyload");
            lazyImages.forEach(function(lazyImage) {
                lazyImage.src = lazyImage.src; // Memicu pemuatan gambar
                lazyImage.onload = () => {
                    removeLazyAttributes(lazyImage);
                };
                lazyImage.classList.add("lazyloaded");
            });
        }
    }

    /**
     * Menangani lazy loading untuk konten kontainer menggunakan Intersection Observer jika tersedia,
     * dengan fallback untuk browser yang tidak mendukung
     * @returns {void}
     */
    function lazyLoadContent() {
        if ('IntersectionObserver' in window) {
            /** @type {NodeListOf<HTMLElement>} */
            let lazyContents = document.querySelectorAll(".u-container, .main-contents, .live-area, .issue-contents");
            
            /**
             * @type {IntersectionObserver}
             * Observer untuk memantau elemen konten yang masuk viewport
             */
            let lazyContentObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        /** @type {HTMLElement} */
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
            // Fallback untuk browser yang tidak mendukung Intersection Observer
            /** @type {NodeListOf<HTMLElement>} */
            let lazyContents = document.querySelectorAll(".u-container, .main-contents, .live-area, .issue-contents");
            lazyContents.forEach(function(lazyContent) {
                lazyContent.classList.add("content-loaded");
            });
        }
    }

    /**
     * Menjalankan fungsi lazy loading dengan delay untuk mengoptimalkan kinerja halaman
     * @returns {void}
     */
    function delayContentLoad() {
        setTimeout(() => {
            lazyLoadContent();
            setTimeout(() => {
                lazyLoadImages();
            }, 1000); // Waktu delay dapat disesuaikan sesuai kebutuhan
        }, 1000); // Waktu delay dapat disesuaikan sesuai kebutuhan
    }

    // Memulai proses lazy loading dengan delay
    delayContentLoad();
});