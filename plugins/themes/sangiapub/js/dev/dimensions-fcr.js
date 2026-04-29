/**
 * Dimensions Field Citation Ratio Fetcher
 * 
 * This script fetches the Field Citation Ratio from Dimensions API via a PHP proxy
 * and updates the HTML element with the result.
 */

// Self-executing function to avoid polluting global namespace
(function() {
    // Function to fetch Field Citation Ratio from Dimensions via PHP proxy
    function fetchFieldCitationRatio(doi) {
        // URL to your PHP proxy script
        const proxyUrl = 'dimensions-fcr.php';
        
        // Create a new URL object and add the DOI as a query parameter
        const url = new URL(proxyUrl, window.location.origin);
        url.searchParams.append('doi', doi);
        
        // Fetch data from the PHP proxy
        return fetch(url.toString())
            .then(response => {
                // Check if the response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Check if there's an error in the response
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Return the Field Citation Ratio
                return data.field_citation_ratio;
            });
    }
    
    // Function to update the HTML element with the Field Citation Ratio
    function updateFieldCitationRatio(ratio, isLoading = false, isError = false) {
        // Find all elements with the class 'FCR-ratio'
        const elements = document.querySelectorAll('.FCR-ratio');
        
        elements.forEach(element => {
            if (isLoading) {
                // Show loading spinner or text
                element.innerHTML = '<small>Loading...</small>';
                element.classList.add('loading');
            } else if (isError) {
                // Show error message
                element.innerHTML = '<small title="Failed to load data">N/A</small>';
                element.classList.add('error');
                element.classList.remove('loading');
            } else {
                // Format the ratio to 2 decimal places if it's a number
                const formattedRatio = typeof ratio === 'number' ? ratio.toFixed(2) : ratio;
                element.textContent = formattedRatio;
                element.classList.remove('loading', 'error');
            }
        });
    }
    
    // Function to extract DOI from the current page
    function extractDOIFromPage() {
        // Extract from the anchor element with class "anchor doi"
        const doiAnchor = document.querySelector('a.anchor.doi');
        if (doiAnchor) {
            // Get the href attribute which contains the DOI URL
            const doiHref = doiAnchor.getAttribute('href');
            // Extract the DOI from the URL (format: https://doi.org/DOI)
            if (doiHref && doiHref.startsWith('https://doi.org/')) {
                return doiHref.replace('https://doi.org/', '');
            }
            
            // If href doesn't work, try getting it from the span inside
            const doiSpan = doiAnchor.querySelector('.anchor-text');
            if (doiSpan && doiSpan.textContent.startsWith('https://doi.org/')) {
                return doiSpan.textContent.replace('https://doi.org/', '');
            }
        }
        
        // Fallback methods if the anchor element is not found
        
        // Try to extract from meta tag
        const metaDOI = document.querySelector('meta[name="citation_doi"]');
        if (metaDOI) {
            return metaDOI.getAttribute('content');
        }
        
        // Try to extract from a specific element
        const doiElement = document.querySelector('#doi-value');
        if (doiElement) {
            return doiElement.textContent.trim();
        }
        
        // Try to extract from URL
        const urlMatch = window.location.pathname.match(/\/doi\/([^\/]+)/);
        if (urlMatch && urlMatch[1]) {
            return urlMatch[1];
        }
        
        // If DOI can't be found using any method, return the example DOI from your HTML
        return '10.29239/j.akuatikisle.1.1.1-10';
    }
    
    // Main function to initialize the fetcher
    function init() {
        try {
            // Extract DOI from the page
            const doi = extractDOIFromPage();
            
            if (!doi) {
                console.error('Could not extract DOI from the page');
                updateFieldCitationRatio('N/A', false, true);
                return;
            }
            
            console.log('Found DOI:', doi);
            
            // Show loading state
            updateFieldCitationRatio(null, true, false);
            
            // Fetch the Field Citation Ratio
            fetchFieldCitationRatio(doi)
                .then(ratio => {
                    // Update the HTML element with the fetched ratio
                    if (ratio === null || ratio === undefined) {
                        // Handle case where the API returned a valid response but no FCR data
                        console.warn('No Field Citation Ratio found for DOI:', doi);
                        updateFieldCitationRatio('0', false, false); // Default to 0 when not found
                    } else {
                        updateFieldCitationRatio(ratio, false, false);
                    }
                })
                .catch(error => {
                    console.error('Error fetching Field Citation Ratio:', error);
                    updateFieldCitationRatio(null, false, true);
                });
        } catch (error) {
            console.error('Error initializing Field Citation Ratio fetcher:', error);
            updateFieldCitationRatio(null, false, true);
        }
    }
    
    // Run the initialization when the DOM is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();