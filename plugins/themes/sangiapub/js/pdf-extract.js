/**
 * PDF Extract - Client-side display of PDF extracted text
 * Path: /plugins/themes/sangiapub/js/pdf-extract.js
 */

(function() {
    'use strict';
    
    // Variabel global untuk caching client-side
    const pdfCache = {
        storage: window.sessionStorage,
        
        // Simpan data ke cache
        set: function(key, data) {
            try {
                this.storage.setItem('pdf_extract_' + key, JSON.stringify({
                    data: data,
                    timestamp: Date.now()
                }));
                return true;
            } catch (e) {
                console.warn('Failed to cache PDF extraction:', e);
                return false;
            }
        },
        
        // Ambil data dari cache
        get: function(key) {
            try {
                const item = this.storage.getItem('pdf_extract_' + key);
                if (!item) return null;
                
                const parsed = JSON.parse(item);
                
                // Verifikasi usia cache - valid untuk 30 menit
                const now = Date.now();
                const age = now - parsed.timestamp;
                if (age > 1800000) { // 30 menit dalam milidetik
                    this.storage.removeItem('pdf_extract_' + key);
                    return null;
                }
                
                return parsed.data;
            } catch (e) {
                console.warn('Failed to retrieve PDF extraction from cache:', e);
                return null;
            }
        },
        
        // Hapus cache
        clear: function() {
            // Hapus semua item cache yang terkait dengan ekstraksi PDF
            Object.keys(this.storage).forEach(key => {
                if (key.startsWith('pdf_extract_')) {
                    this.storage.removeItem(key);
                }
            });
        }
    };
    
    // Fungsi untuk menampilkan konten PDF ketika DOM sudah siap
    document.addEventListener('DOMContentLoaded', function() {
        // Cari URL PDF
        const pdfElement = document.querySelector('.pdf-file');
        if (!pdfElement || !pdfElement.href) {
            console.log('PDF URL not found.');
            return;
        }
        
        let pdfUrl = pdfElement.href;
        console.log(`Original PDF URL: ${pdfUrl}`);
        
        // Konversi URL jika diperlukan
        if (pdfUrl.includes('/view/')) {
            pdfUrl = pdfUrl.replace('/view/', '/viewFile/');
        }
        console.log(`Processing PDF URL: ${pdfUrl}`);
        
        // Tampilkan loading indicator
        const pdfContent = document.getElementById('pdf-content');
        if (pdfContent) {
            pdfContent.classList.add('loading');
        }
        
        // Coba ambil dari cache client-side
        const cacheKey = md5(pdfUrl); // Asumsi fungsi md5 tersedia atau gunakan hash sederhana lainnya
        const cachedData = pdfCache.get(cacheKey);
        
        if (cachedData) {
            console.log('Using client-side cached PDF extraction data');
            displayPdfSections(cachedData);
            
            if (pdfContent) {
                pdfContent.classList.remove('loading');
            }
            return;
        }
        
        // Path ke PHP proxy
        const proxyUrl = '/plugins/themes/sangiapub/php/pdf-extract/pdf-extract.php';
        
        // Panggil endpoint PHP proxy
        fetch(`${proxyUrl}?url=${encodeURIComponent(pdfUrl)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }
                return response.json();
            })
            .then(sections => {
                // Simpan ke cache client-side
                pdfCache.set(cacheKey, sections);
                
                // Tampilkan bagian-bagian PDF
                displayPdfSections(sections);
                
                // Hilangkan loading indicator
                if (pdfContent) {
                    pdfContent.classList.remove('loading');
                }
            })
            .catch(error => {
                console.error('Error processing PDF:', error);
                if (pdfContent) {
                    pdfContent.classList.remove('loading');
                }
                
                // Tampilkan pesan error
                const errorMsg = document.createElement('div');
                errorMsg.className = 'pdf-extract-error';
                errorMsg.textContent = 'Could not extract text from PDF. Please try again later or view the PDF directly.';
                pdfContent.appendChild(errorMsg);
            });
    });
    
    /**
     * Tampilkan bagian PDF dalam elemen HTML
     */
    function displayPdfSections(sections) {
        // Cek apakah ada error
        if (sections.error) {
            console.error('PDF extraction error:', sections.error);
            return;
        }
        
        // Fungsi untuk menampilkan bagian
        function displaySection(sectionText, elementId, containerId) {
            if (!sectionText || sectionText.trim().length < 20) return false;
            
            const element = document.getElementById(elementId);
            if (!element) return false;
            
            // Format paragraf
            const paragraphs = sectionText.split(/\n\n+/)
                .filter(p => p.trim().length > 20)
                .map(p => `<p>${p.trim()}</p>`)
                .join('');
            
            if (!paragraphs) return false;
            
            element.innerHTML = paragraphs;
            
            // Tampilkan container
            const container = document.getElementById(containerId);
            if (container) {
                container.classList.remove('u-js-hide');
            }
            
            return true;
        }
        
        // Tampilkan setiap bagian
        const hasIntro = displaySection(sections.introduction, 'sec1', 'preview-section-introduction');
        const hasMethods = displaySection(sections.methods, 'p0070', 'sec2');
        
        // Tangani Results dan Discussion
        let hasResults = false;
        let hasDiscussion = false;
        
        if (sections.resultsAndDiscussion) {
            // Combined Results and Discussion
            hasResults = displaySection(sections.resultsAndDiscussion, 'p0083', 'sec3');
            
            const discussionP = document.querySelector('#sec4 p');
            if (discussionP) {
                discussionP.innerHTML = 'See Results section for combined Results and Discussion.';
                hasDiscussion = true;
                
                const discussionContainer = document.getElementById('sec4');
                if (discussionContainer) {
                    discussionContainer.classList.remove('u-js-hide');
                }
            }
        } else {
            // Separate Results and Discussion
            hasResults = displaySection(sections.results, 'p0083', 'sec3');
            
            const discussionP = document.querySelector('#sec4 p');
            if (discussionP) {
                hasDiscussion = displaySection(sections.discussion, discussionP.id, 'sec4');
            }
        }
        
        // Tampilkan snippets section jika ada konten
        if (hasMethods || hasResults || hasDiscussion) {
            const snippetsSection = document.getElementById('preview-section-snippets');
            if (snippetsSection) {
                snippetsSection.classList.remove('u-js-hide');
            }
        }
        
        // Conclusion dan Acknowledgments
        const hasConclusion = displaySection(sections.conclusion, 'p0317', 'con5');
        const hasAck = displaySection(sections.acknowledgments, 'p0350', 'ack0010');
        
        // Log informasi
        console.log('PDF content display complete. Sections found:', {
            introduction: hasIntro,
            methods: hasMethods,
            results: hasResults,
            discussion: hasDiscussion,
            conclusion: hasConclusion,
            acknowledgments: hasAck
        });
        
        // Tambahkan metadata extraction jika tersedia
        if (sections._metadata) {
            console.log('PDF extraction metadata:', sections._metadata);
        }
    }
    
    // Fungsi hash sederhana jika md5 tidak tersedia
    function md5(string) {
        function hash(s) {
            let hash = 0, i, chr;
            if (s.length === 0) return hash;
            for (i = 0; i < s.length; i++) {
                chr = s.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            return hash;
        }
        
        return hash(string).toString();
    }
})();