document.addEventListener("DOMContentLoaded", function() {
    /**
     * Fetches the user's IP address using the ipify API.
     * @returns {Promise<string>} A promise that resolves to the user's IP address.
     */
    function getUserIP() {
        return $.getJSON('https://api.ipify.org?format=json').then(function(data) {
            return data.ip;
        });
    }

    /**
     * Fetches geographic information based on the user's IP address using the ipinfo API.
     * @param {string} userIP - The user's IP address.
     * @returns {Promise<Object>} A promise that resolves to the geographic information.
     */
    function getGeolocationData(userIP) {
        return $.getJSON('https://ipinfo.io/' + userIP + '/json');
    }

    /**
     * Fetches country and currency information based on the country code using the restcountries API.
     * @param {string} countryCode - The country code.
     * @returns {Promise<Object>} A promise that resolves to the country and currency information.
     */
    function getCountryData(countryCode) {
        return $.getJSON('https://restcountries.com/v3.1/alpha/' + countryCode);
    }

    /**
     * Fetches exchange rates with respect to IDR using the exchangerate-api.
     * @returns {Promise<Object>} A promise that resolves to the exchange rate data.
     */
    function getExchangeRates() {
        return $.getJSON('https://api.exchangerate-api.com/v4/latest/IDR');
    }

    /**
     * Extracts the price value from a text string.
     * @param {string} priceText - The text containing the price.
     * @returns {number} The numeric value of the price.
     */
    function extractPrice(priceText) {
        return parseFloat(priceText.replace(/[^0-9,-]+/g, "").replace(",", "."));
    }

    /**
     * Formats a number as a string with commas as thousand separators.
     * @param {number} number - The number to format.
     * @returns {string} The formatted number.
     */
    function formatPrice(number) {
        return number.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Main function to update price and country information
    getUserIP().then(function(userIP) {
        $('#diagnostic-ip').text(userIP);

        return getGeolocationData(userIP);
    }).then(function(ipData) {
        var userCountry = ipData.country;

        return $.when(
            getCountryData(userCountry),
            getExchangeRates()
        );
    }).then(function(countryData, rateData) {
        var countryInfo = countryData[0][0];
        var exchangeRateData = rateData[0];
        var countryName = countryInfo.name.common;
        var currencyCode = Object.keys(countryInfo.currencies)[0];

        // Update country name in HTML
        $('.price-table-small-print-2284778856').text('price for ' + countryName + ' (gross)');

        // Extract and convert price
        var priceText = $('.price-cell-2689446056').text().trim();
        var priceInIDR = extractPrice(priceText);
        var conversionRate = exchangeRateData.rates[currencyCode];
        var priceInLocalCurrency = priceInIDR * conversionRate;

        // Format and update price and currency code in HTML
        $('.price-cell-2689446056').text(currencyCode + ' ' + formatPrice(priceInLocalCurrency));

        // Add "update" class to indicate successful update
        $('.price-table-2827577461').addClass('update');
    }).catch(function(error) {
        console.error('Error:', error);
    });
});
