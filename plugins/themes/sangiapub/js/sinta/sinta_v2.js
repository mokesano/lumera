/**
 * Script untuk menampilkan Sinta Impact Factor di OJS v2.4.8
 * Versi dengan keamanan yang ditingkatkan dan efek skeleton loading
 * Enhanced with comprehensive Wizdam logging system
 * #author Rochmady and Wizdam Team
 * #version v1.04.6-wizdam-logging
 */

/**
 * Wizdam Sinta Score Logging System
 */
const WizdamLogger = {
    prefix: '[Wizdam Sinta Score]',
    
    log: function(message, level = 'INFO') {
        const timestamp = new Date().toISOString();
        const logMessage = `${this.prefix} [${level}] [${timestamp}] ${message}`;
        console.log(logMessage);
    },
    
    info: function(message) {
        this.log(message, 'INFO');
    },
    
    warn: function(message) {
        this.log(message, 'WARN');
    },
    
    error: function(message) {
        this.log(message, 'ERROR');
    },
    
    debug: function(message) {
        this.log(message, 'DEBUG');
    },
    
    success: function(message) {
        this.log(message, 'SUCCESS');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    WizdamLogger.info('Starting Sinta Impact Factor fetch process');
    WizdamLogger.debug('DOM Content loaded, initializing Sinta proxy client');
    
    // 1. Fungsi untuk menemukan ISSN dari berbagai elemen input
    const findIssn = () => {
        WizdamLogger.debug('Searching for ISSN from various input elements');
        
        const possibleElements = [
            document.getElementById('printIssn'),
            document.getElementById('eIssn'),
            document.querySelector('input[name="issn"]'),
            document.querySelector('[data-issn]')
        ].filter(el => el?.value?.trim());
        
        const foundElements = possibleElements.length;
        WizdamLogger.debug(`Found ${foundElements} potential ISSN elements`);
        
        if (foundElements === 0) {
            WizdamLogger.warn('No ISSN input elements found in DOM');
            return null;
        }
        
        const selectedElement = possibleElements[0];
        const issn = selectedElement.value.trim();
        WizdamLogger.info(`ISSN found from element: ${selectedElement.id || selectedElement.name || 'unnamed'} -> ${issn}`);
        
        return issn;
    };
    
    // 2. Normalisasi ISSN
    const normalizeIssn = (rawIssn) => {
        WizdamLogger.debug(`Normalizing ISSN: ${rawIssn}`);
        
        if (!rawIssn) {
            WizdamLogger.error('Raw ISSN is null or empty');
            return null;
        }
        
        const cleaned = rawIssn.replace(/\D/g, '');
        WizdamLogger.debug(`ISSN after cleaning: ${cleaned} (length: ${cleaned.length})`);
        
        if (cleaned.length !== 8) {
            WizdamLogger.error(`Invalid ISSN length: ${cleaned.length}, expected 8 digits`);
            return null;
        }
        
        WizdamLogger.success(`ISSN normalized successfully: ${cleaned}`);
        return cleaned;
    };
    
    const rawIssn = findIssn();
    const issn = normalizeIssn(rawIssn);
    
    if (!issn) {
        WizdamLogger.error('Failed to find or normalize valid ISSN, aborting process');
        return;
    }
    
    // 3. Temukan elemen tampilan untuk score dan grade
    WizdamLogger.debug('Searching for display elements');
    
    const sintaScoreContainer = document.querySelector('.js-sinta-score');
    const sintaGradeContainer = document.querySelector('.js-sinta-grade');
    
    if (!sintaScoreContainer) {
        WizdamLogger.error('Sinta score container element not found (.js-sinta-score)');
        return;
    } else {
        WizdamLogger.info('Sinta score container found');
    }
    
    if (!sintaGradeContainer) {
        WizdamLogger.warn('Sinta grade container not found (.js-sinta-grade)');
    } else {
        WizdamLogger.info('Sinta grade container found');
    }
    
    // Elemen untuk sinta score
    const scoreElement = sintaScoreContainer.querySelector('.text-l.u-display-block');
    const scoreLabelElement = sintaScoreContainer.querySelector('.text-xs.__info');
    
    // Elemen untuk sinta grade
    const gradeElement = sintaGradeContainer?.querySelector('.text-l.u-display-block');
    const gradeLabelElement = sintaGradeContainer?.querySelector('.text-xs.__info');
    
    WizdamLogger.debug(`Score element found: ${!!scoreElement}, Grade element found: ${!!gradeElement}`);
    
    // 4. Tambahkan style untuk animasi secara dinamis (akan dihapus setelah selesai)
    const tempStyleId = 'temp-style-' + Math.random().toString(36).substring(2, 9);
    WizdamLogger.debug(`Creating temporary style with ID: ${tempStyleId}`);
    
    const tempStyle = document.createElement('style');
    tempStyle.id = tempStyleId;
    tempStyle.textContent = `
        @keyframes tempPulse {
            0% { opacity: 0.4; }
            50% { opacity: 0.7; }
            100% { opacity: 0.4; }
        }
        .temp-skeleton {
            display: inline-block;
            width: 100%;
            height: 1em;
            background-color: rgba(244, 244, 244, 0.7);
            border-radius: 3px;
            animation: tempPulse 1.5s infinite ease-in-out;
            position: relative;
            overflow: hidden;
        }
        .temp-skeleton::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: tempShimmer 1.8s infinite;
        }
        @keyframes tempShimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
    `;
    document.head.appendChild(tempStyle);
    WizdamLogger.info('Skeleton loading styles injected into DOM');
    
    // 5. Fungsi untuk menambahkan efek skeleton pada elemen
    const addSkeletonEffect = (element, elementName) => {
        if (!element) {
            WizdamLogger.warn(`Cannot add skeleton effect to ${elementName}: element not found`);
            return;
        }
        
        WizdamLogger.debug(`Adding skeleton effect to ${elementName}`);
        
        // Simpan nilai asli
        element.dataset.originalContent = element.innerHTML;
        
        // Ukur lebar elemen asli
        const width = element.offsetWidth || 60; // Fallback jika tidak bisa diukur
        const height = element.offsetHeight || 16; // Fallback jika tidak bisa diukur
        
        WizdamLogger.debug(`${elementName} dimensions: ${width}x${height}px`);
        
        // Ganti teks dengan blok skeleton
        element.innerHTML = `<span class="temp-skeleton" style="width: ${width}px; height: ${height}px;"></span>`;
        
        WizdamLogger.success(`Skeleton effect applied to ${elementName}`);
    };
    
    // 6. Fungsi untuk menghapus efek skeleton
    const removeSkeletonEffect = (element, newValue, elementName) => {
        if (!element) {
            WizdamLogger.warn(`Cannot remove skeleton effect from ${elementName}: element not found`);
            return;
        }
        
        WizdamLogger.debug(`Removing skeleton effect from ${elementName}, setting value: ${newValue}`);
        
        // Ganti dengan nilai baru
        element.innerHTML = newValue;
        
        WizdamLogger.success(`Skeleton effect removed from ${elementName}`);
    };
    
    // 7. Fungsi untuk membersihkan semua efek style
    const cleanupAllStyles = () => {
        WizdamLogger.debug('Starting cleanup of temporary styles');
        
        // Hapus style yang ditambahkan
        const styleElement = document.getElementById(tempStyleId);
        if (styleElement) {
            document.head.removeChild(styleElement);
            WizdamLogger.info(`Temporary style ${tempStyleId} removed from DOM`);
        } else {
            WizdamLogger.warn(`Temporary style ${tempStyleId} not found during cleanup`);
        }
        
        WizdamLogger.success('Style cleanup completed');
    };
    
    // 8. Tampilkan efek skeleton pada semua elemen yang diperlukan
    WizdamLogger.info('Applying skeleton effects to UI elements');
    
    if (sintaScoreContainer) {
        sintaScoreContainer.classList.remove('u-js-hide');
        addSkeletonEffect(scoreElement, 'score element');
        addSkeletonEffect(scoreLabelElement, 'score label element');
        WizdamLogger.info('Sinta score container made visible with skeleton effects');
    }
    
    if (sintaGradeContainer) {
        sintaGradeContainer.classList.remove('u-js-hide');
        addSkeletonEffect(gradeElement, 'grade element');
        addSkeletonEffect(gradeLabelElement, 'grade label element');
        WizdamLogger.info('Sinta grade container made visible with skeleton effects');
    }
    
    // 9. Fungsi untuk efek highlight sederhana yang akan dihapus setelah selesai
    const addSimpleHighlight = (element, elementName) => {
        if (!element) {
            WizdamLogger.warn(`Cannot add highlight to ${elementName}: element not found`);
            return;
        }
        
        WizdamLogger.debug(`Adding highlight effect to ${elementName}`);
        
        // Tambahkan highlight dengan timeout untuk menghapusnya
        element.style.backgroundColor = 'rgba(255, 255, 150, 0.08)';
        
        // Hapus highlight setelah beberapa saat
        setTimeout(() => {
            element.style.backgroundColor = '';
            WizdamLogger.debug(`Highlight effect removed from ${elementName}`);
        }, 1500);
        
        WizdamLogger.success(`Highlight effect applied to ${elementName}`);
    };
    
    // 10. Konfigurasi endpoint (menggunakan endpoint yang lebih generik)
    const proxyUrl = '/api/sinta';
    WizdamLogger.info(`Configured proxy endpoint: ${proxyUrl}`);
    
    // 11. Dapatkan CSRF token jika ada
    WizdamLogger.debug('Searching for CSRF token');
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';
    
    if (csrfToken) {
        WizdamLogger.info('CSRF token found and will be included in request');
    } else {
        WizdamLogger.warn('No CSRF token found, proceeding without it');
    }
    
    // 12. Fungsi untuk format angka
    const formatNumber = (num) => {
        const n = parseFloat(num);
        const result = isNaN(n) ? 'N/A' : n.toFixed(3);
        WizdamLogger.debug(`Number formatting: ${num} -> ${result}`);
        return result;
    };
    
    // 13. Proses fetch data dengan keamanan ditingkatkan
    const fetchData = async () => {
        WizdamLogger.info(`Starting data fetch for ISSN: ${issn}`);
        
        try {
            // Persiapkan header keamanan
            const headers = {
                'X-Requested-With': 'XMLHttpRequest' // Header standar untuk Ajax
            };
            
            // Tambahkan CSRF token jika tersedia
            if (csrfToken) {
                headers['X-CSRF-Token'] = csrfToken;
                WizdamLogger.debug('CSRF token added to request headers');
            }
            
            const requestUrl = `${proxyUrl}?issn=${encodeURIComponent(issn)}`;
            WizdamLogger.info(`Making request to: ${requestUrl}`);
            
            // Lakukan request dengan tambahan keamanan
            const response = await fetch(requestUrl, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin' // Kirim cookies untuk otentikasi
            });
            
            WizdamLogger.info(`Response received - Status: ${response.status} ${response.statusText}`);
            
            // Tangani respons error
            if (!response.ok) {
                // Periksa kode status khusus
                if (response.status === 429) {
                    WizdamLogger.error('Rate limit exceeded (HTTP 429)');
                    throw new Error('Rate limit exceeded. Please try again later.');
                }
                WizdamLogger.error(`HTTP error: ${response.status} ${response.statusText}`);
                throw new Error(`HTTP ${response.status}`);
            }
            
            WizdamLogger.debug('Parsing JSON response');
            const data = await response.json();
            WizdamLogger.success('JSON response parsed successfully');
            WizdamLogger.debug(`Response data: ${JSON.stringify(data)}`);
            
            if (!data.success) {
                WizdamLogger.error(`API returned error: ${data.error || 'Unknown error'}`);
                throw new Error(data.error || 'Data not valid');
            }
            
            const result = {
                impact: formatNumber(data.impact),
                grade: data.grade || 'N/A',
                issn: data.issn || issn
            };
            
            WizdamLogger.success(`Data fetch successful - Impact: ${result.impact}, Grade: ${result.grade}`);
            return result;
            
        } catch (error) {
            WizdamLogger.error(`Fetch error: ${error.message}`);
            throw error;
        }
    };
    
    // 14. Eksekusi dengan timeout
    WizdamLogger.info('Setting up fetch timeout (15 seconds)');
    const controller = new AbortController();
    const timeout = setTimeout(() => {
        WizdamLogger.error('Request timeout after 15 seconds');
        controller.abort();
    }, 15000);
    
    WizdamLogger.info('Starting main fetch process');
    fetchData({ signal: controller.signal })
        .then(({ impact, grade }) => {
            clearTimeout(timeout);
            WizdamLogger.success('Data fetch completed successfully');
            WizdamLogger.info(`Processing successful response - Impact: ${impact}, Grade: ${grade}`);
            
            // Sedikit penundaan untuk efek lebih smooth
            setTimeout(() => {
                WizdamLogger.debug('Applying fetched data to UI elements');
                
                // Update sinta score
                if (sintaScoreContainer) {
                    removeSkeletonEffect(scoreElement, impact, 'score element');
                    removeSkeletonEffect(scoreLabelElement, 'SintaScore', 'score label element');
                    addSimpleHighlight(sintaScoreContainer, 'score container');
                    WizdamLogger.success('Sinta score updated in UI');
                }
                
                // Update sinta grade
                if (sintaGradeContainer) {
                    removeSkeletonEffect(gradeElement, `<span class="grade">Sinta</span> ${grade}`, 'grade element');
                    removeSkeletonEffect(gradeLabelElement, 'SintaGrade', 'grade label element');
                    addSimpleHighlight(sintaGradeContainer, 'grade container');
                    
                    // Ubah atribut title menjadi aria-title
                    sintaGradeContainer.setAttribute('aria-title', `Sinta Impact ${impact} | National Grade Accredited ${grade}`);
                    WizdamLogger.success('Sinta grade updated in UI with accessibility attributes');
                }
                
                // Bersihkan semua efek style setelah beberapa saat
                setTimeout(() => {
                    cleanupAllStyles();
                    WizdamLogger.success('UI update process completed');
                }, 2000);
                
            }, 800);
        })
        .catch(error => {
            clearTimeout(timeout);
            WizdamLogger.error(`Main process failed: ${error.message}`);
            
            // Sedikit penundaan untuk efek lebih smooth
            setTimeout(() => {
                WizdamLogger.info('Applying error state to UI elements');
                
                if (sintaScoreContainer) {
                    removeSkeletonEffect(scoreElement, 'N/A', 'score element');
                    const errorMessage = error.message.includes('HTTP') ? 'Server Error' : 'Not found';
                    removeSkeletonEffect(scoreLabelElement, `<span style="color:#dc3545">${errorMessage}</span>`, 'score label element');
                    WizdamLogger.info('Error state applied to score elements');
                }
                
                // Jika terjadi error, kembalikan grade ke nilai default
                if (sintaGradeContainer) {
                    const originalGradeContent = gradeElement?.dataset.originalContent || 'N/A';
                    const originalLabelContent = gradeLabelElement?.dataset.originalContent || 'SintaGrade';
                    removeSkeletonEffect(gradeElement, originalGradeContent, 'grade element');
                    removeSkeletonEffect(gradeLabelElement, originalLabelContent, 'grade label element');
                    WizdamLogger.info('Grade elements restored to original state');
                }
                
                // Bersihkan semua efek style
                cleanupAllStyles();
                WizdamLogger.warn('Error handling completed, process finished');
                
            }, 800);
        });
});