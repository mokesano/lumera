/**
 * @file Script to manage gender and country data for publication editors
 */

document.addEventListener("DOMContentLoaded", function () {
    const genderMetricsSection = document.querySelector(".gender-indicator-metrics-section");
    const editorialBoardSection = document.querySelector(".editorial-board-by-country");

    const editorTypesExist = document.querySelector(".publication-editor-type") !== null;
    const editorsContainerExist = document.querySelector(".publication-editors") !== null;
    const editorsExist = document.querySelector(".publication-editor") !== null;

    if (!editorTypesExist && !editorsContainerExist && !editorsExist) {
        if (genderMetricsSection) {
            genderMetricsSection.parentNode.removeChild(genderMetricsSection);
        }
        if (editorialBoardSection) {
            editorialBoardSection.parentNode.removeChild(editorialBoardSection);
        }
        return;
    }

    const knownCountryNames = [
        'Indonesia', 'Malaysia', 'Singapore', 'Thailand', 'Philippines', 'Vietnam', 'Myanmar', 'Laos', 'Cambodia', 'Brunei',
        'Timor-Leste', 'United States', 'Canada', 'Mexico', 'Brazil', 'Argentina', 'Chile', 'United Kingdom', 'Germany',
        'France', 'Italy', 'Spain', 'Russia', 'China', 'Japan', 'South Korea', 'India', 'Australia', 'New Zealand', 
        'Iran', 'Paris'
    ];

    const updateData = () => {
        if (editorsContainerExist) {
            const editors = document.querySelectorAll(".publication-editor input[name='gender']");
            const genderCount = {
                man: 0,
                woman: 0,
                nonBinary: 0,
                preferNotToDisclose: 0
            };

            const countryCount = {};

            editors.forEach(function (editor) {
                const gender = editor.value;
                const affiliations = editor.closest(".publication-editor").querySelectorAll("span[itemprop='affiliation']");

                const countries = new Set();
                affiliations.forEach(function (affiliation) {
                    const affiliationText = affiliation.textContent.trim();
                    const foundCountry = knownCountryNames.find(function (country) {
                        return new RegExp("\\b" + country + "\\b", "i").test(affiliationText);
                    });
                    if (foundCountry) {
                        countries.add(foundCountry);
                    }
                });

                countries.forEach(function (country) {
                    if (countryCount[country]) {
                        countryCount[country]++;
                    } else {
                        countryCount[country] = 1;
                    }
                });

                if (gender === "M") {
                    genderCount.man++;
                } else if (gender === "F") {
                    genderCount.woman++;
                } else if (gender === "O") {
                    genderCount.nonBinary++;
                } else {
                    genderCount.preferNotToDisclose++;
                }

                editor.parentNode.removeChild(editor); // Hapus elemen input setelah diproses
            });

            const totalEditors = genderCount.man + genderCount.woman + genderCount.nonBinary + genderCount.preferNotToDisclose;
            const formatPercentage = function (count) {
                return count === 0 ? "0%" : ((count / totalEditors) * 100).toFixed(2) + "%";
            };

            const manPercentage = formatPercentage(genderCount.man);
            const womanPercentage = formatPercentage(genderCount.woman);
            const nonBinaryPercentage = formatPercentage(genderCount.nonBinary);
            const preferNotToDisclosePercentage = formatPercentage(genderCount.preferNotToDisclose);

            const updateLegendPercentage = function (selector, percentage) {
                document.querySelector(selector).textContent = percentage;
            };

            updateLegendPercentage(".legend-item:nth-child(1) .legend-percentage", manPercentage);
            updateLegendPercentage(".legend-item:nth-child(2) .legend-percentage", womanPercentage);
            updateLegendPercentage(".legend-item:nth-child(3) .legend-percentage", nonBinaryPercentage);
            updateLegendPercentage(".legend-item:nth-child(4) .legend-percentage", preferNotToDisclosePercentage);

            // Mengatur style CSS untuk elemen canvas
            const canvas = document.getElementById('genderChart');
            canvas.style.width = '100%';
            canvas.style.height = '100%';

            // Menggunakan Chart.js untuk membuat pie chart
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ["Man", "Woman", "Non-binary or gender diverse", "Prefer not to disclose"],
                    datasets: [{
                        data: [genderCount.man, genderCount.woman, genderCount.nonBinary, genderCount.preferNotToDisclose],
                        backgroundColor: ["#FF6A19", "#3F89FF", "#56BF70", "#4D4D4D"],
                        borderColor: "#ffffff",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Nonaktifkan legend default
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce(function (acc, val) { return acc + val; }, 0);
                                    const percentage = ((value / total) * 100).toFixed(2);
                                    return label + ": " + value + " (" + percentage + "%)";
                                }
                            }
                        }
                    }
                }
            });

            // Memperbarui judul dan sebutan berdasarkan elemen publication-editor-type
            const editorTypeElements = document.querySelectorAll(".publication-editor-type");
            const editorTypesText = Array.from(editorTypeElements).slice(0, 3).map(function (elem) { return elem.textContent.toLowerCase(); });

            let editorTypeText = "editors";
            if (editorTypeElements.length === 1) {
                editorTypeText = editorTypeElements[0].textContent.toLowerCase();
            }

            if (editorTypesText.length > 0) {
                const titleElement = document.querySelector(".gender-indicator-title");
                if (editorTypesText.length === 1) {
                    titleElement.textContent = "Gender diversity of " + editorTypesText[0];
                } else if (editorTypesText.length === 2) {
                    titleElement.textContent = "Gender diversity of " + editorTypesText[0] + " and " + editorTypesText[1];
                } else {
                    titleElement.textContent = "Gender diversity of " + editorTypesText[0] + ", " + editorTypesText[1] + ", and " + editorTypesText[2];
                }
            }

            const dataText = document.querySelector(".u-padding-s-top");
            dataText.textContent = "Data represents responses from 100.00% of " + totalEditors + " " + editorTypeText + " and editorial board members";

            // Memperbarui bagian editorial board by country
            const editorialBoardText = document.querySelector(".editors-by-country-text");
            const totalCountries = Object.keys(countryCount).length;
            editorialBoardText.textContent = totalEditors + " " + editorTypeText + " and editorial board members in " + totalCountries + " countries/regions";

            const editorsByCountryList = document.querySelector(".editors-by-country-ordered-list");
            editorsByCountryList.innerHTML = ''; // Kosongkan item list sebelumnya

            const sortedCountries = Object.entries(countryCount).sort(function (a, b) {
                if (b[1] === a[1]) {
                    return a[0].localeCompare(b[0]);
                }
                return b[1] - a[1];
            });

            const topCountries = sortedCountries.slice(0, 5);
            const remainingCountries = sortedCountries.slice(5);

            topCountries.forEach(function ([country, count]) {
                const listItem = document.createElement("li");
                listItem.classList.add("country-list-item");
                listItem.textContent = country + " (" + count + ")";
                editorsByCountryList.appendChild(listItem);
            });

            if (remainingCountries.length > 0) {
                const remainingItem = document.createElement("li");
                remainingItem.classList.add("country-list-item");
                remainingItem.textContent = "And " + remainingCountries.length + " more...";
                editorsByCountryList.appendChild(remainingItem);
            }
        }
    };

    // Memanggil updateData pertama kali
    updateData();

    // Menggunakan MutationObserver untuk memantau perubahan pada elemen sumber data
    const observer = new MutationObserver(updateData);

    if (editorsContainerExist) {
        observer.observe(document.querySelector(".publication-editors"), { childList: true, subtree: true });
    }
});
