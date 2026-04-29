document.addEventListener("DOMContentLoaded", function() {
    const wrapper = document.getElementById('wrapper');

    // Create and append the loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = 0;
    loadingOverlay.style.left = 0;
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)'; /* Semi-transparent background */
    loadingOverlay.style.zIndex = 9999;
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.alignItems = 'center';
    loadingOverlay.style.justifyContent = 'center';

    // Create and append the spinner
    const spinner = document.createElement('div');
    spinner.style.width = '50px';
    spinner.style.height = '50px';
    spinner.style.border = '5px solid rgba(0, 0, 0, 0.1)';
    spinner.style.borderLeftColor = '#000';
    spinner.style.borderRadius = '50%';
    spinner.style.animation = 'spin 1s linear infinite';

    // Add keyframes animation
    const styleSheet = document.styleSheets[0];
    styleSheet.insertRule(`
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    `, styleSheet.cssRules.length);

    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);

    function showLoadingOverlay() {
        loadingOverlay.style.display = 'flex';
    }

    window.addEventListener('beforeunload', function() {
        showLoadingOverlay();
    });

    window.addEventListener('load', function() {
        loadingOverlay.style.display = 'none';
    });

    // Detect click on the search button and Enter key press on the search input
    const searchButton = document.getElementById('search');
    const queryInput = document.getElementById('query');
    const searchForm = document.getElementById('search-form');

    searchButton.addEventListener('click', function(event) {
        showLoadingOverlay();
    });

    queryInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission to handle loading overlay properly
            showLoadingOverlay();
            searchForm.submit(); // Submit the form programmatically
        }
    });
});
