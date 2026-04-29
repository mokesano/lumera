/**
 * Fungsi untuk mengelola tampilan elemen <p> di dalam bagian dengan kelas .bibliography-sec.
 * Jika jumlah elemen <p> melebihi 17, elemen-elemen tersebut akan dihapus,
 * menampilkan pemberitahuan, dan menggunakan tombol view-more yang sudah ada untuk mengatur visibilitas elemen.
 * Juga mengupdate jumlah total elemen <p> di dalam elemen .section-title pada span dengan class "count".
 */
document.addEventListener('DOMContentLoaded', function () {
    const bibliographySections = document.querySelectorAll('.ref-bibliography');
    let counter = 7; // Memulai nomor urut dari 007

    const removeExcessItems = (items) => items.map(item => {
        item.parentNode.removeChild(item);
        return item;
    });

    const toggleItemsVisibility = (section, viewMoreButton, notice, items) => {
        const isExpanded = viewMoreButton.classList.contains('view-less');

        if (isExpanded) {
            // Menghapus elemen kelebihan
            removeExcessItems(items);
            section.appendChild(notice);
            viewMoreButton.querySelector('.button-alternative-text').textContent = 'View more references';
            viewMoreButton.classList.remove('view-less');
            viewMoreButton.querySelector('svg').style.transform = 'rotate(0deg)';
        } else {
            // Menambahkan elemen kelebihan kembali di akhir elemen <p> yang ada
            items.forEach(item => {
                section.insertBefore(item, viewMoreButton);
            });

            section.removeChild(notice);
            viewMoreButton.querySelector('.button-alternative-text').textContent = 'View less references';
            viewMoreButton.classList.add('view-less');
            viewMoreButton.querySelector('svg').style.transform = 'rotate(180deg)';
        }
    };

    const splitParagraphs = (section) => {
        const paragraphs = section.querySelectorAll('p');
        paragraphs.forEach(paragraph => {
            if (paragraph.innerHTML.includes('<br>')) {
                const splitContent = paragraph.innerHTML.split('<br>');
                splitContent.forEach((content, index) => {
                    if (index === 0) {
                        paragraph.innerHTML = content.trim();
                    } else {
                        const newParagraph = document.createElement('p');
                        newParagraph.innerHTML = content.trim();
                        paragraph.parentNode.insertBefore(newParagraph, paragraph.nextSibling);
                    }
                });
            }
        });
    };

    bibliographySections.forEach(section => {
        splitParagraphs(section);

        let items = Array.from(section.querySelectorAll('p')).filter(item => item.textContent.trim() !== "");
        items.forEach(item => {
            if (!item.hasAttribute('id') && !item.hasAttribute('class')) {
                item.setAttribute('id', `ref-sangia${String(counter).padStart(3, '0')}`);
                item.setAttribute('class', 'reference');
                counter++;
            }
        });

        const totalItems = items.length;
        const sectionTitle = section.closest('.bibliography').querySelector('.section-title .count');
        if (sectionTitle) sectionTitle.textContent = `(${totalItems})`;

        const viewMoreButton = section.querySelector('.view-more');
        const notice = section.querySelector('.notice');

        if (totalItems > 17) {
            const removedItems = removeExcessItems(items.slice(17));
            section.appendChild(notice);
            section.appendChild(viewMoreButton);
            viewMoreButton.addEventListener('click', () => toggleItemsVisibility(section, viewMoreButton, notice, removedItems));
        } else {
            section.removeChild(notice);
            section.removeChild(viewMoreButton);
        }
    });
});

$(document).ready(function() {
    const toggleLoading = show => {
        const loadingSvg = `<div class="loading-indicator"><svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="#000">
            <circle cx="50" cy="50" r="35" stroke-width="10" stroke="#000" fill="none"></circle>
            <circle cx="50" cy="50" r="25" stroke-width="10" stroke="#000" fill="none">
                <animate attributeName="r" from="25" to="35" dur="1s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="1s" repeatCount="indefinite" />
            </circle>
            <circle cx="50" cy="50" r="15" stroke-width="8" stroke="#585858" fill="none">
                <animate attributeName="r" from="15" to="25" dur="0.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="0.8s" repeatCount="indefinite" />
            </circle></svg></div>`;
        show ? $('body').append(loadingSvg) : $('.loading-indicator').remove();
    };

    const extractTitle = content => {
        const yearPattern = /\b\d{4}[a-z]?\b/;
        const yearMatch = content.match(yearPattern);
        if (yearMatch) {
            const yearIndex = content.indexOf(yearMatch[0]);
            const titleStart = content.indexOf('. ', yearIndex) + 2;
            let titleEnd = content.indexOf('. ', titleStart);
            if (titleEnd === -1) titleEnd = content.length;
            return content.substring(titleStart, titleEnd).trim().replace(/<[^>]*>/g, '');
        }
        return '';
    };

    const isElementEmpty = element => $(element).text().trim().length === 0;

    const addLinkWithLoading = (referenceLinks, href, text, svgIcon) => {
        const loadingSvg = `<svg class="icon" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" viewBox="0 0 100 100" width="20" height="20" fill="#585858">
            <circle cx="50" cy="50" r="25" stroke-width="10" stroke="#000" fill="none">
                <animate attributeName="r" from="25" to="35" dur="1s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="1s" repeatCount="indefinite" />
            </circle>
            <circle cx="50" cy="50" r="15" stroke-width="8" stroke="#585858" fill="none">
                <animate attributeName="r" from="15" to="25" dur="0.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="0.8s" repeatCount="indefinite" />
            </circle></svg>`;
        let link = $(text === 'View PDF'
            ? `<a class="anchor link anchor-primary" href="${href}" target="_blank" aria-label="${text}">
                <span class="anchor-text-container">${svgIcon}<span class="anchor-text">${text}</span></span></a>`
            : `<a class="anchor link anchor-primary" href="${href}" target="_blank" aria-label="${text}">
                <span class="anchor-text-container"><span class="anchor-text">${text}</span>${svgIcon}</span></a>`
        );
        referenceLinks.append(link);
        link.prepend(loadingSvg);
        setTimeout(() => link.find('svg').first().remove(), 2000);
    };

    const addCrossrefLinks = (item, referenceLinks) => {
        const pdfLink = item.link && item.link.length > 0 ? item.link.find(link => link['content-type'] === 'application/pdf').URL : null;
        const articleLink = item.URL;
        if (pdfLink) addLinkWithLoading(referenceLinks, pdfLink, 'View PDF', '<svg focusable="false" viewBox="0 0 32 32" class="icon" part="icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>');
        if (articleLink) addLinkWithLoading(referenceLinks, articleLink, 'View Article', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
    };

    const disableActiveLinks = element => $(element).find('a').each(function() {
        const text = $(this).text();
        $(this).replaceWith(text);
    });

    const processVisibleReferences = () => {
        $('.reference').each(function() {
            const referenceElement = $(this);
            if (isElementEmpty(referenceElement) || referenceElement.find('.ReferenceLinks').length > 0) return;

            disableActiveLinks(referenceElement);
            const content = referenceElement.html();
            const journalName = $('meta[property="journal_name"]').attr('content');
            const referenceLinks = $('<div class="ReferenceLinks text-s"></div>');
            referenceElement.append(referenceLinks);

            const journalNames = [
                "Jurnal Akuatiklestari",
                "Agrikan: Jurnal Agribisnis Perikanan",
                "Omni-Akuatika"
            ];

            const promises = [];
            const title = extractTitle(content);

            if (content.includes(journalName)) {
                promises.push($.ajax({
                    url: "https://api.crossref.org/works?query.bibliographic=" + encodeURIComponent(title),
                    success: response => {
                        if (response.message.items.length > 0) addCrossrefLinks(response.message.items[0], referenceLinks);
                    },
                    error: () => console.error('Failed to fetch data from Crossref.')
                }));
            }

            journalNames.forEach(journal => {
                if (content.includes(journal)) {
                    promises.push($.ajax({
                        url: "https://api.crossref.org/works?query.bibliographic=" + encodeURIComponent(title),
                        success: response => {
                            if (response.message.items.length > 0) {
                                const item = response.message.items[0];
                                const pdfLink = item.link && item.link.length > 0 ? item.link.find(link => link['content-type'] === 'application/pdf').URL : null;
                                const articleLink = item.URL;

                                if (pdfLink) addLinkWithLoading(referenceLinks, pdfLink, 'View PDF', '<svg focusable="false" viewBox="0 0 32 32" class="icon" part="icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>');
                                if (articleLink) addLinkWithLoading(referenceLinks, articleLink, 'View Article', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
                            }
                        },
                        error: () => console.error('Failed to fetch data from Crossref.')
                    }));
                }
            });

            const httpMatches = content.match(/https?:\/\/[^\s]+|www\.[^\s]+/g);
            if (httpMatches) {
                httpMatches.forEach(httpUrl => {
                    if (!httpUrl.includes('doi.org')) addLinkWithLoading(referenceLinks, httpUrl, 'View Source', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
                });
            }

            const doiMatch = content.match(/(?:doi:\s*|DOI:\s*|https:\/\/doi\.org\/|http:\/\/dx\.doi\.org\/)(\S+)/i);
            if (doiMatch) {
                let doiUrl = doiMatch[1].replace(/\.$/, '');
                if (doiUrl.startsWith('10.')) doiUrl = 'https://doi.org/' + doiUrl;
                addLinkWithLoading(referenceLinks, doiUrl, 'Crossref', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
            }

            const titleForGoogleScholar = extractTitle(content);
            if (titleForGoogleScholar) addLinkWithLoading(referenceLinks, 'https://scholar.google.com/scholar_lookup?title=' + encodeURIComponent(titleForGoogleScholar), 'Google Scholar', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');

            Promise.all(promises).then(() => {
                const links = referenceLinks.children('a');
                links.sort((a, b) => ['View PDF', 'View at Publisher', 'View Article', 'View Source', 'Crossref', 'Google Scholar'].indexOf($(a).text().trim()) - ['View PDF', 'View at Publisher', 'View Article', 'View Source', 'Crossref', 'Google Scholar'].indexOf($(b).text().trim()));
                referenceLinks.append(links);
            }).catch(() => console.error('An error occurred while processing references.'));
        });
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                toggleLoading(true);
                processVisibleReferences();
                toggleLoading(false);
                observer.unobserve(entry.target);
            }
        });
    });

    $('.reference').each(function() {
        observer.observe(this);
    });

    $('.view-more').on('click', function() {
        toggleLoading(true);
        setTimeout(() => {
            processVisibleReferences();
            toggleLoading(false);
        }, 1000);
    });
});

/**
 * Dibuat oleh: ChatGPT, sebuah model AI dari OpenAI
 * Dibantu oleh: Anda, pengguna yang luar biasa
 */
