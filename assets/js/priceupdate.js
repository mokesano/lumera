/**
 * @file public/assets/js/priceupdate.js
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @brief Versi dengan fallback proxy dan penanganan error yang tidak merusak tampilan.
 * 
 * @author Rochmady
 * @version v0.0.7
 */
document.addEventListener("DOMContentLoaded", function() {

    // Daftar proxy publik yang bisa digunakan (urutan prioritas)
    const PROXIES = [
        'https://api.allorigins.win/raw?url=',
        'https://api.codetabs.com/v1/proxy?quest=',
        'https://corsproxy.io/?'
    ];

    /**
     * Mencoba mengambil data dari URL target melalui proxy.
     * Jika satu proxy gagal, coba proxy berikutnya.
     */
    function fetchWithProxy(targetUrl) {
        let proxyIndex = 0;

        function tryNextProxy() {
            if (proxyIndex >= PROXIES.length) {
                return Promise.reject(new Error('Semua proxy gagal untuk: ' + targetUrl));
            }
            const proxyUrl = PROXIES[proxyIndex] + encodeURIComponent(targetUrl);
            proxyIndex++;
            return fetch(proxyUrl, {
                mode: 'cors',
                credentials: 'omit'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Proxy responded with status ' + response.status);
                }
                return response.json();
            })
            .catch(err => {
                // Coba proxy berikutnya
                return tryNextProxy();
            });
        }

        return tryNextProxy();
    }

    /**
     * Fetches the user's IP address using the ipify API.
     * @returns {Promise<string>} A promise that resolves to the user's IP address.
     */
    function getUserIP() {
        return fetch('https://api.ipify.org?format=json', {
            mode: 'cors',
            credentials: 'omit'
        })
        .then(response => {
            if (!response.ok) throw new Error('IP API error: ' + response.status);
            return response.json();
        })
        .then(data => data.ip);
    }

    /**
     * Fetches geographic information based on the user's IP.
     * @param {string} userIP - The user's IP address.
     * @returns {Promise<Object>} A promise that resolves to the geographic information.
     */
    function getGeolocationData(userIP) {
        return fetch('https://ipinfo.io/' + userIP + '/json', {
            mode: 'cors',
            credentials: 'omit'
        })
        .then(response => {
            if (!response.ok) throw new Error('Geo API error: ' + response.status);
            return response.json();
        });
    }

    /**
     * Fetches country data using proxy to avoid CORS.
     * @param {string} countryCode - The country code.
     * @returns {Promise<Object>} A promise that resolves to the country and currency information.
     */
    function getCountryData(countryCode) {
        const targetUrl = 'https://restcountries.com/v3.1/alpha/' + encodeURIComponent(countryCode);
        return fetchWithProxy(targetUrl);
    }

    /**
     * Fetches exchange rates with IDR as base (exchangerate.host supports CORS).
     * @returns {Promise<Object>} A promise that resolves to the exchange rate data.
     */
    function getExchangeRates() {
        return fetch('https://api.exchangerate.host/latest?base=IDR', {
            mode: 'cors',
            credentials: 'omit'
        })
        .then(response => {
            if (!response.ok) throw new Error('Exchange rate API error: ' + response.status);
            return response.json();
        })
        .then(data => data.rates);
    }

    /**
     * Extracts numeric price from a string.
     * @param {string} priceText - The text containing the price.
     * @returns {number} The numeric value of the price.
     */
    function extractPrice(priceText) {
        let cleaned = priceText.replace(/[^0-9,.]/g, '');
        if (cleaned.includes(',') && cleaned.lastIndexOf(',') > cleaned.lastIndexOf('.')) {
            cleaned = cleaned.replace(/\./g, '').replace(',', '.');
        } else {
            cleaned = cleaned.replace(/,/g, '');
        }
        return parseFloat(cleaned) || 0;
    }

    /**
     * Formats a number with thousand separators and two decimals.
     * @param {number} number - The number to format.
     * @returns {string} The formatted number.
     */
    function formatPrice(number) {
        return number.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // ================ MAIN FLOW ================
    getUserIP()
        .then(userIP => {
            $('#diagnostic-ip').text(userIP);
            return getGeolocationData(userIP);
        })
        .then(ipData => {
            const userCountry = ipData.country;
            // Fetch both country and exchange rate data in parallel
            return Promise.all([
                getCountryData(userCountry),
                getExchangeRates()
            ]);
        })
        .then(([countryData, rates]) => {
            const countryInfo = countryData[0];
            const countryName = countryInfo.name.common;
            const currencies = countryInfo.currencies;
            let currencyCode = currencies ? Object.keys(currencies)[0] : 'IDR';
            if (!currencyCode || !rates[currencyCode]) {
                console.warn('Currency ' + currencyCode + ' not found, using IDR');
                currencyCode = 'IDR';
            }

            // Update country name
            $('.price-table-small-print-2284778856').text('price for ' + countryName + ' (gross)');

            // Extract price in IDR
            const priceText = $('.price-cell-2689446056').text().trim();
            const priceInIDR = extractPrice(priceText);
            const conversionRate = rates[currencyCode] || 1;
            const priceInLocalCurrency = priceInIDR * conversionRate;

            // Update price display
            $('.price-cell-2689446056').text(currencyCode + ' ' + formatPrice(priceInLocalCurrency));
            $('.price-table-2827577461').addClass('update');
        })
        .catch(error => {
            // Hanya log error, jangan ubah elemen harga
            console.error('Error in price update:', error.message);
            // Tambahkan class error untuk indikasi (tapi biarkan konten asli)
            $('.price-table-2827577461').addClass('error');
        });
});