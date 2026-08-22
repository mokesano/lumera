/**
 * @file public/assets/js/priceupdate.js
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @brief Versi dengan keamanan yang ditingkatkan.
 * 
 * @author Rochmady
 * @version v0.0.7
 */
document.addEventListener("DOMContentLoaded", function() {

    /**
     * Fetches the user's IP address using the ipify API.
     * @returns {Promise<string>} A promise that resolves to the user's IP address.
     */
    function getUserIP() {
        return fetch('https://api.ipify.org?format=json')
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
        return fetch('https://ipinfo.io/' + userIP + '/json')
            .then(response => {
                if (!response.ok) throw new Error('Geo API error: ' + response.status);
                return response.json();
            });
    }

    /**
     * Fetches country data using fetch (supports CORS).
     * @param {string} countryCode - The country code.
     * @returns {Promise<Object>} A promise that resolves to the country and currency information.
     */
    function getCountryData(countryCode) {
        return fetch('https://restcountries.com/v3.1/alpha/' + countryCode)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Country API error: ' + response.status + ' - ' + response.statusText);
                }
                return response.json();
            });
    }

    /**
     * Fetches exchange rates with IDR as base (using exchangerate.host which supports CORS).
     * @returns {Promise<Object>} A promise that resolves to the exchange rate data.
     */
    function getExchangeRates() {
        return fetch('https://api.exchangerate.host/latest?base=IDR')
            .then(response => {
                if (!response.ok) throw new Error('Exchange rate API error: ' + response.status);
                return response.json();
            })
            .then(data => data.rates);
    }

    /**
     * Extracts numeric price from a string (handles thousands separators and comma decimal).
     * @param {string} priceText - The text containing the price.
     * @returns {number} The numeric value of the price.
     */
    function extractPrice(priceText) {
        // Remove non-numeric except comma and period
        let cleaned = priceText.replace(/[^0-9,.]/g, '');
        // If comma is used as decimal (e.g., "1.234,56"), replace comma with period and remove dots
        if (cleaned.includes(',') && cleaned.lastIndexOf(',') > cleaned.lastIndexOf('.')) {
            cleaned = cleaned.replace(/\./g, '').replace(',', '.');
        } else {
            // Otherwise just remove commas (thousands separators)
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

    // Main flow
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
            // countryData is the array from restcountries
            const countryInfo = countryData[0];
            const countryName = countryInfo.name.common;
            // Safely get currency code
            const currencies = countryInfo.currencies;
            let currencyCode = currencies ? Object.keys(currencies)[0] : 'IDR';
            // If currencyCode is undefined or not in rates, fallback to IDR
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
            // Log detailed error
            console.error('Error in price update:', error.message);
            console.error('Stack:', error.stack);
            // Optionally show a user-friendly message
            $('.price-table-2827577461').addClass('error');
        });
});