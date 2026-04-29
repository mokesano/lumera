$(document).ready(function() {
    updateJournalAndArticleTypeIndex();
    triggerInitialSearch();
    fetchTotalPages(getBaseUrl());
});

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

function triggerInitialSearch() {
    processSearch();
    updateJournalAndArticleTypeIndex();
}

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

function getBaseUrl() {
    var currentUrl = window.location.href;
    var baseUrl = currentUrl.split('?')[0] + '?';
    var params = new URLSearchParams(window.location.search);
    params.delete('page');
    return baseUrl + params.toString();
}
