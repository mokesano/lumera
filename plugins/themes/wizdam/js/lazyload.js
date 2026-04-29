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
