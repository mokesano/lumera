$(document).ready(function() {
    console.log("Document is ready");

    /**
     * Memproses data halaman
     * @param {string} data - Data halaman.
     */
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

    /**
     * Mendapatkan halaman data
     * @param {number} totalPages - Total halaman.
     * @param {string} baseUrl - Base URL.
     */
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

    /**
     * Mendapatkan total halaman
     * @param {string} baseUrl - Base URL.
     */
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

    /**
     * Fungsi untuk mengupdate indeks jurnal dan tipe artikel
     */
    function updateJournalAndArticleTypeIndex() {
        console.log("Updating journal and article type index");
        const journalsIndex = {};
        const articleTypesIndex = {};

        $('.app-article-list-row__item').each(function() {
            const journalTitleElement = $(this).find('.c-meta__item[data-test="journal-title-and-link"]');
            const articleTypeElement = $(this).find('.c-meta__item[data-test="article.type"]');

            if (journalTitleElement.length > 0) {
                const journalTitle = journalTitleElement.text().trim();
                if (journalsIndex[journalTitle]) {
                    journalsIndex[journalTitle]++;
                } else {
                    journalsIndex[journalTitle] = 1;
                }
            }

            if (articleTypeElement.length > 0) {
                const articleType = articleTypeElement.text().trim();
                if (articleTypesIndex[articleType]) {
                    articleTypesIndex[articleType]++;
                } else {
                    articleTypesIndex[articleType] = 1;
                }
            }
        });

        updateFacets('#journal-target .c-facet-expander__list', journalsIndex, 'journal');
        updateFacets('#article-type-target .c-facet-expander__list', articleTypesIndex, 'article_type');
    }

    /**
     * Fungsi untuk mengupdate elemen facet
     * @param {string} targetSelector - Selektor target elemen facet.
     * @param {Object} indexData - Data indeks untuk diperbarui.
     * @param {string} dataType - Tipe data (journal atau article_type).
     */
    function updateFacets(targetSelector, indexData, dataType) {
        console.log("Updating facets", targetSelector, indexData, dataType);
        const target = $(targetSelector);
        target.empty();

        $.each(indexData, function(title, count) {
            const listItem = $('<li>', { 'class': 'c-facet-expander__list-item' });
            const checkbox = $('<input>', {
                'type': 'checkbox',
                'name': dataType,
                'id': `${dataType}-${title.replace(/\s+/g, '-').toLowerCase()}`,
                'value': title,
                'data-action': 'submit',
                'checked': 'checked'
            });
            const label = $('<label>', { 'class': 'c-facet-expander__link', 'for': `${dataType}-${title.replace(/\s+/g, '-').toLowerCase()}` });
            const span = $('<span>').text(`${title} (${count})`);
            label.append(span);
            listItem.append(checkbox).append(label);
            target.append(listItem);
        });
    }

    // Trigger initial search processing to handle preloaded values
    processSearch();
    updateJournalAndArticleTypeIndex();

    /**
     * Menampilkan hasil
     */
    function displayResults() {
        $journalTarget.empty();
        $articleTypeTarget.empty();

        var journalFragment = document.createDocumentFragment();
        var articleTypeFragment = document.createDocumentFragment();

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

        requestAnimationFrame(function() {
            $journalTarget.append(journalFragment);
            $articleTypeTarget.append(articleTypeFragment);

            $journalTarget.show();
            $articleTypeTarget.show();
        });

        var journalKeys = Object.keys(journalCount);
        if (journalKeys.length === 1) {
            var singleJournalPath = journalKeys[0];
            var singleJournalData = journalCount[singleJournalPath];

            $journalEllipsis.text(`${singleJournalData.name} (${singleJournalData.count})`);
            $journalButton.addClass('c-facet__selected');
        }

        var articleTypeKeys = Object.keys(articleTypeCount);
        if (articleTypeKeys.length === 1) {
            var singleArticleTypeName = articleTypeKeys[0];
            var singleArticleTypeCount = articleTypeCount[singleArticleTypeName];

            $articleTypeEllipsis.text(`${singleArticleTypeName} (${singleArticleTypeCount})`);
            $articleTypeButton.addClass('c-facet__selected');
        }
    }

    var currentUrl = window.location.href;
    var baseUrl = currentUrl.split('?')[0] + '?';
    var params = new URLSearchParams(window.location.search);
    params.delete('page');
    baseUrl += params.toString();

    fetchTotalPages(baseUrl);

    /**
     * Mendapatkan path jurnal dari URL
     * @param {string} url - URL jurnal.
     * @returns {string} - Path jurnal.
     */
    function getJournalPath(url) {
        var urlParts = url.split('/');
        return urlParts[urlParts.length - 1].toLowerCase();
    }

    // Inisiasi indeks nama jurnal dan jumlah artikel
    var journalCount = {};
    var articleTypeCount = {};

    var $journalTarget = $('#journal-target .c-facet-expander__list');
    var $articleTypeTarget = $('#article-type-target .c-facet-expander__list');

    var $journalButton = $('button[aria-labelledby="journal-legend"]');
    var $journalEllipsis = $journalButton.find('.c-facet__ellipsis');

    var $articleTypeButton = $('button[aria-labelledby="article-legend"]');
    var $articleTypeEllipsis = $articleTypeButton.find('.c-facet__ellipsis');

    $journalTarget.hide();
    $articleTypeTarget.hide();

});
