document.addEventListener("DOMContentLoaded", function() {
    const totalPages = 18; // Jumlah halaman sebenarnya

    function updatePagination(pagination) {
        const pageNr = pagination.querySelector('.page-nr');
        const currentPageElement = pagination.querySelector('.current-page, strong');
        const currentPage = currentPageElement ? parseInt(currentPageElement.textContent.trim(), 10) : 1;
        const maxVisiblePages = 5; // Maksimal nomor halaman yang akan ditampilkan

        function updatePageUrl(pageNum) {
            return `https://example.com?page=${pageNum}`;
        }

        function addPageLink(pageNum, isCurrent) {
            if (isCurrent) {
                const span = document.createElement('span');
                span.className = 'pagination-link current-page';
                span.textContent = pageNum;
                pageNr.appendChild(span);
            } else {
                const link = document.createElement('a');
                link.href = updatePageUrl(pageNum);
                link.className = 'pagination-link';
                link.textContent = pageNum;
                pageNr.appendChild(link);
            }
            pageNr.appendChild(document.createTextNode(' '));
        }

        function addEllipsis() {
            const span = document.createElement('span');
            span.textContent = '...';
            pageNr.appendChild(span);
            pageNr.appendChild(document.createTextNode(' '));
        }

        // Mengatur link halaman sebelumnya
        const prevContainer = pagination.querySelector('.prev');
        if (currentPage > 1) {
            const prevLink = document.createElement('a');
            prevLink.href = updatePageUrl(currentPage - 1);
            prevLink.innerHTML = `<img src="//assets.sangia.org/image/classical/arrow-left.png" alt="previous"><span class="page_links prevpage_links">Prev</span>`;
            prevContainer.innerHTML = '';
            prevContainer.appendChild(prevLink);
        } else {
            prevContainer.classList.add('disabled');
        }

        // Mengatur link halaman berikutnya
        const nextContainer = pagination.querySelector('.next');
        if (currentPage < totalPages) {
            const nextLink = document.createElement('a');
            nextLink.href = updatePageUrl(currentPage + 1);
            nextLink.innerHTML = `<span class="page_links nextpage_links">Next</span><img src="//assets.sangia.org/image/classical/arrow-right.png" alt="next">`;
            nextContainer.innerHTML = '';
            nextContainer.appendChild(nextLink);
        } else {
            nextContainer.classList.add('disabled');
        }

        // Mengatur halaman yang ditampilkan
        pageNr.innerHTML = '';

        if (totalPages > maxVisiblePages) {
            if (currentPage <= Math.floor(maxVisiblePages / 2) + 1) {
                for (let i = 1; i <= maxVisiblePages; i++) {
                    addPageLink(i, i === currentPage);
                }
                addEllipsis();
                addPageLink(totalPages, false);
            } else if (currentPage >= totalPages - Math.floor(maxVisiblePages / 2)) {
                addPageLink(1, false);
                addEllipsis();
                for (let i = totalPages - maxVisiblePages + 1; i <= totalPages; i++) {
                    addPageLink(i, i === currentPage);
                }
            } else {
                addPageLink(1, false);
                addEllipsis();
                for (let i = currentPage - Math.floor(maxVisiblePages / 2); i <= currentPage + Math.floor(maxVisiblePages / 2); i++) {
                    addPageLink(i, i === currentPage);
                }
                addEllipsis();
                addPageLink(totalPages, false);
            }
        } else {
            for (let i = 1; i <= totalPages; i++) {
                addPageLink(i, i === currentPage);
            }
        }

        // Menghapus atribut hidden setelah pembaruan selesai
        pagination.classList.remove('hidden');
    }

    function initializePagination() {
        document.querySelectorAll('.pagination').forEach(function(pagination) {
            updatePagination(pagination);
        });
    }

    // Menggunakan MutationObserver untuk memantau perubahan dalam DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList.contains('pagination')) {
                        updatePagination(node);
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Inisialisasi pagination setelah halaman dimuat
    initializePagination();
});
