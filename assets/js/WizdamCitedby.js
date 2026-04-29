/**
* Citation Display JS Fully Optimized Version
* - Hide panel when no citations
* - Modern animated tooltip
* - Lightweight & high performance
* - Refresh button positioned on the right
* - Support for various publication types
* - PDF button only shows for allowed journals
* - Refresh cooldown system based on last update time
* 
* @author Rochmady and Wizdam Team
* @version 3.1.6
*/
(function() {
   // Flag untuk melacak pemrosesan
   let doiProcessed = false;
   
   // Variabel untuk mengelola refresh
   const REFRESH_COOLDOWN_HOURS = 12; // Minimum 12 jam antara refresh
   let lastDataTimestamp = 0; // Timestamp dari pembaruan data terakhir
   
   // Minimal CSS dengan tooltip modern yang dipastikan berfungsi
   const addStyles = () => {
       if (!document.getElementById('citation-minimal-styles')) {
           const style = document.createElement('style');
           style.id = 'citation-minimal-styles';
           style.textContent = `
               .citation-refresh-overlay {position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.5);display:flex;justify-content:center;align-items:center;z-index:10}
               .citation-refresh-spinner {width:24px;height:24px;border:2px solid rgba(0,0,0,.1);border-radius:50%;border-top-color:#3498db;animation:spin 1s linear infinite}
               .citation-blur {filter:blur(1px)}
               @keyframes spin {to{transform:rotate(360deg)}}
               .refresh-icon {margin-left:4px;vertical-align:middle}

               /* Improved Tooltip Styles */
               .tooltip-wrap {position:relative;display:inline-block}
               .citation-tooltip {
                   position:absolute;
                   bottom:130%;
                   right:0;
                   width:240px;
                   background:#e6f2ff;
                   font-family:Elsevier Sans,Gulliver,Nexus Sans,Arial,sans-serif !important;
                   font-size:1.1em;
                   line-height:1.5;
                   border-radius:10px;
                   padding:8px 16px;
                   text-align:center;
                   z-index:1;
                   box-shadow:0 2px 10px rgba(0,0,0,0.1);
                   opacity:0;
                   visibility:hidden;
                   transition:all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                   pointer-events:none;
                   transform:translateY(10px);
               }
               .citation-tooltip::after {
                   content:'';
                   position:absolute;
                   width:0;
                   height:0;
                   border-left:6px solid transparent;
                   border-right:6px solid transparent;
                   border-top:9px solid #e6f2ff;
                   bottom:-6px;
                   right:20px;
               }
               .tooltip-wrap:hover .citation-tooltip {
                   opacity:1;
                   visibility:visible;
                   transform:translateY(0);
               }
               .no-citations-hide {display:none!important}
               
               /* Disabled button styling */
               .button-disabled {
                   opacity: 0.5 !important;
                   cursor: not-allowed !important;
                   pointer-events: none !important;
               }
               
               /* New styling for citation info container */
               .citing-info-container {
                   display: flex;
                   justify-content: space-between;
                   align-items: center;
                   width: 100%;
               }
               
               .citing-info {
                   flex: 1;
               }
               
               .refresh-container {
                   margin-left: auto;
               }
           `;
           document.head.appendChild(style);
       }
   };
   
   // Efektif menangani overlay loading
   const showLoading = (container) => {
       container.style.position = 'relative';
       const list = container.querySelector('ul.citedby_crossref');
       if (list) list.classList.add('citation-blur');
       
       // Hapus overlay lama jika ada
       const oldOverlay = container.querySelector('.citation-refresh-overlay');
       if (oldOverlay) oldOverlay.remove();
       
       // Tambahkan overlay baru
       const overlay = document.createElement('div');
       overlay.className = 'citation-refresh-overlay';
       overlay.innerHTML = '<div class="citation-refresh-spinner"></div>';
       container.appendChild(overlay);
   };
   
   // Hapus loading overlay
   const hideLoading = (container) => {
       const overlay = container.querySelector('.citation-refresh-overlay');
       if (overlay) overlay.remove();
       const list = container.querySelector('ul.citedby_crossref');
       if (list) list.classList.remove('citation-blur');
   };
   
   // Fix HTML dalam judul
   const fixHtmlTitles = () => {
       document.querySelectorAll('.anchor-text span').forEach(span => {
           const text = span.textContent;
           if (text && text.includes('<') && text.includes('>')) {
               span.innerHTML = text;
           }
       });
   };
   
   // Fungsi untuk memeriksa apakah refresh diperbolehkan berdasarkan timestamp terakhir
   const canRefresh = () => {
       if (lastDataTimestamp === 0) return true; // Belum pernah refresh
       
       const now = Date.now();
       const hoursSinceLastUpdate = (now - lastDataTimestamp) / (1000 * 60 * 60);
       
       return hoursSinceLastUpdate >= REFRESH_COOLDOWN_HOURS;
   };
   
   // Fungsi untuk mendapatkan waktu tunggu yang tersisa
   const getCooldownTimeRemaining = () => {
       if (lastDataTimestamp === 0) return 0;
       
       const now = Date.now();
       const millisSinceLastUpdate = now - lastDataTimestamp;
       const millisToWait = (REFRESH_COOLDOWN_HOURS * 60 * 60 * 1000) - millisSinceLastUpdate;
       
       if (millisToWait <= 0) return 0;
       
       // Konversi milis ke format yang lebih mudah dibaca
       const hours = Math.floor(millisToWait / (1000 * 60 * 60));
       const minutes = Math.floor((millisToWait % (1000 * 60 * 60)) / (1000 * 60));
       
       if (hours > 0) {
           return `${hours}h ${minutes}m`;
       } else {
           return `${minutes}m`;
       }
   };
   
   // Template untuk tombol refresh dengan tooltip yang dipastikan berfungsi dan tidak terpotong
   const refreshButtonTemplate = (sourcesInfo, canRefreshNow) => {
       const tooltipText = !canRefreshNow 
           ? `Next refresh available in ${getCooldownTimeRemaining()}`
           : (sourcesInfo || 'Refresh citations');
       
       const disabledClass = !canRefreshNow ? 'button-disabled' : '';
       
       return `
           <div class="tooltip-wrap">
               <button class="anchor button-link refresh-citations-button button-link-secondary ${disabledClass}" type="button" ${!canRefreshNow ? 'disabled' : ''}>
                   <span class="button-link-text-container">
                       <span class="anchor-text button-link-text">Refresh</span>
                   </span>
                   <svg class="refresh-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                       <path d="M23 4v6h-6"></path>
                       <path d="M1 20v-6h6"></path>
                       <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"></path>
                       <path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"></path>
                   </svg>
               </button>
               <div class="citation-tooltip">${tooltipText}</div>
           </div>
       `;
   };
   
   // Fungsi untuk menghapus panel jika tidak ada kutipan
   const hideCitationPanelIfEmpty = (citationCount) => {
       const panels = document.querySelectorAll('.SidePanel.doi-cited');
       if (panels.length && !citationCount) {
           panels.forEach(panel => {
               panel.classList.add('no-citations-hide');
           });
           return true; // Panel disembunyikan
       }
       return false; // Panel tetap ditampilkan
   };
   
   // Helper untuk membuat tooltip sumber kutipan yang ditampilkan saja
   const formatSourcesInfo = (sources) => {
       if (!sources) return 'Refresh citations';
       
       // Hanya tampilkan sumber dengan jumlah > 0
       const sourceParts = [];
       
       if (sources.opencitations_count > 0) {
           sourceParts.push(`OpenCitations (${sources.opencitations_count})`);
       }
       
       if (sources.crossref_count > 0) {
           sourceParts.push(`CrossRef (${sources.crossref_count})`);
       }
       
       if (sources.openalex_count > 0) {
           sourceParts.push(`OpenAlex (${sources.openalex_count})`);
       }
       
       if (sources.semanticscholar_count > 0) {
           sourceParts.push(`Semantic Scholar (${sources.semanticscholar_count})`);
       }
       
       if (sources.dimensions_count > 0) {
           sourceParts.push(`Dimensions (${sources.dimensions_count})`);
       }
       
       // Jika tidak ada sumber dengan jumlah > 0, tampilkan semua
       if (sourceParts.length === 0) {
           return `Sources: OpenCitations (0), CrossRef (0), OpenAlex (0), Semantic Scholar (0), Dimensions (0)`;
       }
       
       return `Sources: ${sourceParts.join(', ')}`;
   };
   
   // Tambahkan fungsi untuk memformat timestamp UNIX ke format tanggal yang dapat dibaca
   const formatTimestamp = (timestamp) => {
       if (!timestamp) return 'Unknown date';
       
       try {
           // Pastikan timestamp adalah angka
           const timestampNum = typeof timestamp === 'string' ? parseInt(timestamp) : timestamp;
           
           // Konversi timestamp ke objek Date
           // Jika timestamp dalam detik (10 digit), kalikan dengan 1000 untuk mendapatkan milidetik
           const date = new Date(timestampNum.toString().length <= 10 ? timestampNum * 1000 : timestampNum);
           
           if (isNaN(date.getTime())) {
               return 'Invalid date';
           }
           
           // Dapatkan komponen tanggal
           const day = date.getDate();
           const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
           const month = months[date.getMonth()];
           const year = date.getFullYear();
           
           // Format jam dan menit
           const hours = date.getHours().toString().padStart(2, '0');
           const minutes = date.getMinutes().toString().padStart(2, '0');
           
           // Return format tanggal lengkap
           return `${day} ${month} ${year}, ${hours}:${minutes}`;
       } catch (e) {
           console.error('[Wizdam Log] Error formatting timestamp:', e);
           return 'Error date format';
       }
   };

   // Tambahkan fungsi untuk memformat string tanggal ISO
   const formatISODate = (dateStr) => {
       if (!dateStr) return 'Unknown date';
       
       try {
           const date = new Date(dateStr);
           
           if (isNaN(date.getTime())) {
               return 'Invalid date';
           }
           
           // Dapatkan komponen tanggal
           const day = date.getDate();
           const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
           const month = months[date.getMonth()];
           const year = date.getFullYear();
           
           // Format jam dan menit
           const hours = date.getHours().toString().padStart(2, '0');
           const minutes = date.getMinutes().toString().padStart(2, '0');
           
           // Return format tanggal lengkap
           return `${day} ${month} ${year}, ${hours}:${minutes}`;
       } catch (e) {
           console.error('[Wizdam Log] Error formatting ISO date:', e);
           return 'Error date format';
       }
   };
   
   // Fungsi untuk mengekstrak dan menyimpan timestamp dari respons
   const extractAndSaveTimestamp = (response) => {
       try {
           // Coba ekstrak timestamp dari berbagai lokasi
           if (response.data && response.data.timestamp) {
               // Jika timestamp adalah angka, gunakan langsung
               if (typeof response.data.timestamp === 'number') {
                   lastDataTimestamp = response.data.timestamp * 1000; // Konversi ke milidetik jika perlu
                   return lastDataTimestamp;
               }
           }
           
           // Jika tidak ada timestamp, coba gunakan last_updated
           if (response.last_updated) {
               const date = new Date(response.last_updated);
               if (!isNaN(date.getTime())) {
                   lastDataTimestamp = date.getTime();
                   return lastDataTimestamp;
               }
           }
           
           // Fallback ke timestamp saat ini
           console.log('[Wizdam Log] No valid timestamp found in API response, using current time');
           lastDataTimestamp = Date.now();
           return lastDataTimestamp;
       } catch (e) {
           console.error('[Wizdam Log] Error extracting timestamp:', e);
           lastDataTimestamp = Date.now();
           return lastDataTimestamp;
       }
   };
   
   // Cek apakah jurnal berada dalam daftar yang diizinkan
   const isAllowedJournal = (journalName) => {
       if (!journalName) return false;
       
       // Dapatkan nama jurnal saat ini dari meta tag
       const currentJournalMeta = document.querySelector('meta[name="citation_journal_title"]');
       const currentJournal = currentJournalMeta ? currentJournalMeta.getAttribute('content') : '';
       
       // Daftar jurnal yang diizinkan selain jurnal saat ini
       const allowedJournals = [
           'Jurnal Akuatiklestari',
           'Agrikan Jurnal Agribisnis Perikanan',
           'Agrikan: Jurnal Agribisnis Perikanan',
           'Jurnal Ilmu dan Teknologi Kelautan Tropis'
       ];
       
       // Periksa apakah sama dengan jurnal saat ini
       if (currentJournal && journalName.trim().toLowerCase() === currentJournal.trim().toLowerCase()) {
           return true;
       }
       
       // Periksa apakah termasuk dalam daftar yang diizinkan
       return allowedJournals.some(journal => 
           journalName.trim().toLowerCase() === journal.trim().toLowerCase()
       );
   };
   
   // Helper function untuk menampilkan konten publikasi berdasarkan jenis
   const formatPublicationInfo = (article) => {
       // Default values for required fields
       const type = article.type || 'article-journal';
       const year = article.year || 'N/A';
       const volume = article.volume || '';
       const issue = article.issue || '';
       const page = article.page || '';
       const container = article.container || (article.journal || 'Publication not available');
       const publisher = article.publisher || '';
       const isbn = article.isbn || '';
       const issn = article.issn || '';
       
       // Jangan gunakan ID dari OpenAlex atau sumber lain yang bukan ID artikel asli
       // Hanya gunakan ID jika itu adalah ID artikel yang valid (biasanya berupa angka atau kode singkat)
       const id = article.id && typeof article.id === 'string' && !article.id.includes('openalex') ? article.id : '';
       
       // Publication info HTML based on type
       let publicationInfo = '';
       
       switch (type) {
           case 'article-journal':
           case 'journal-article':
           case 'journal':
           case 'journal-issue':
               // Journal article
               let journalInfo = '';
               if (volume) journalInfo += `Volume ${volume}`;
               if (issue) journalInfo += journalInfo ? `, Issue ${issue}` : `Issue ${issue}`;
               if (page) journalInfo += journalInfo ? `, p: ${page}` : `p: ${page}`;
               else if (id) journalInfo += journalInfo ? `, ID: ${id}` : `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="journal">${container}, </span>
                           <span class="year">${year}, </span> ${journalInfo ? '<span class="edition">' + journalInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'book-chapter':
           case 'book-part':
           case 'book-section':
               // Book chapter - tampilkan judul buku lengkap
               let chapterInfo = '';
               if (isbn) chapterInfo += `ISBN: ${isbn}`;
               if (page) chapterInfo += chapterInfo ? `, p: ${page}` : `p: ${page}`;
               else if (id) chapterInfo += chapterInfo ? `, ID: ${id}` : `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="chapter-book">In: ${container}, </span>
                           ${publisher ? '<span class="publisher">' + publisher + ', </span>' : ''}
                           <span class="year">${year}</span> ${chapterInfo ? '<span class="edition">' + chapterInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'book':
           case 'monograph':
           case 'reference-book':
               // Book - tampilkan hanya publisher, tahun dan info lainnya tanpa judul buku
               let bookInfo = '';
               if (isbn) bookInfo += `ISBN: ${isbn}`;
               if (page) bookInfo += bookInfo ? `, p: ${page}` : `p: ${page}`;
               else if (id) bookInfo += bookInfo ? `, ID: ${id}` : `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="publisher">Publisher "${publisher}"</span>${year ? ', <span class="year">' + year + '</span>' : ''} ${bookInfo ? ',<span class="edition">' + bookInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'conference-paper':
           case 'proceedings-article':
           case 'proceedings':
               // Conference proceedings
               let confInfo = '';
               if (volume) confInfo += `Volume ${volume}`;
               if (page) confInfo += confInfo ? `, p: ${page}` : `p: ${page}`;
               else if (id) confInfo += confInfo ? `, ID: ${id}` : `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="conference">${container}, </span>
                           ${publisher ? '<span class="publisher">' + publisher + ', </span>' : ''}
                           <span class="year">${year}, </span>
                           ${confInfo ? '<span class="edition">' + confInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'dissertation':
           case 'thesis':
               // Thesis
               let thesisInfo = '';
               if (page) thesisInfo += `p: ${page}`;
               else if (id) thesisInfo += `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="thesis">${container}, </span>
                           <span class="year">${year}, </span>
                           ${thesisInfo ? '<span class="edition">' + thesisInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'report':
           case 'report-series':
           case 'technical-report':
               // Report
               let reportInfo = '';
               if (page) reportInfo += `p: ${page}`;
               else if (id) reportInfo += `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="report">${container}, </span>
                           ${publisher ? '<span class="publisher">' + publisher + ', </span>' : ''}
                           <span class="year">${year}, </span>
                           ${reportInfo ? '<span class="edition">' + reportInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'patent':
               // Patent
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="patent">${container}, </span>
                           <span class="year">${year}, </span>
                           ${id ? '<span class="edition">ID: ' + id + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'dataset':
               // Dataset
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="dataset">${container}, </span>
                           ${publisher ? '<span class="publisher">' + publisher + ', </span>' : ''}
                           <span class="year">${year}, </span>
                           ${id ? '<span class="edition">ID: ' + id + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           case 'preprint':
               // Preprint
               let preprintInfo = '';
               if (page) preprintInfo += `p: ${page}`;
               else if (id) preprintInfo += `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="preprint">${container}, </span>
                           ${publisher ? '<span class="publisher">' + publisher + ', </span>' : ''}
                           <span class="year">${year}, </span>
                           ${preprintInfo ? '<span class="edition">' + preprintInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
               
           default:
               // Generic publication type
               let genericInfo = '';
               if (volume) genericInfo += `Volume ${volume}`;
               if (issue) genericInfo += genericInfo ? `, Issue ${issue}` : `Issue ${issue}`;
               if (page) genericInfo += genericInfo ? `, p: ${page}` : `p: ${page}`;
               else if (id) genericInfo += genericInfo ? `, ID: ${id}` : `ID: ${id}`;
               
               publicationInfo = `
                   <div class="article-source ellipsis u-clr-grey6">
                       <div class="source">
                           <span class="publication">${container}, </span>
                           ${publisher ? '<span class="publisher">' + publisher + ', </span>' : ''}
                           <span class="year">${year}, </span>
                           ${genericInfo ? '<span class="edition">' + genericInfo + '</span>' : ''}
                       </div>
                   </div>
               `;
               break;
       }
       
       return publicationInfo;
   };
   
   // Fungsi utama untuk memuat kutipan artikel
   const loadCitingArticles = (doi, forceRefresh = false) => {
       // Jika sudah diproses dan bukan refresh, abaikan
       if (doiProcessed && !forceRefresh) return;
       
       const citingContainer = document.getElementById('citing-articles');
       if (!citingContainer) return; // Safety check
       
       // Periksa apakah refresh dimungkinkan
       if (forceRefresh && !canRefresh()) {
           console.log('[Wizdam Log] Refresh ditolak: period cooldown belum selesai', getCooldownTimeRemaining());
           return;
       }
       
       // Tampilkan loading pada refresh atau loading pertama
       if (forceRefresh) {
           showLoading(citingContainer);
       } else {
           citingContainer.innerHTML = '<div class="loading">Loading citations...</div>';
       }
       
       // Siapkan request dengan fetch API (lebih ringan dari jQuery)
       const apiUrl = `/api/citedby?doi=${doi}${forceRefresh ? '&refresh=1' : ''}`;
       
       fetch(apiUrl)
           .then(response => response.json())
           .then(response => {
               if (response.status === 'success') {
                   const data = response.data;
                   const citationCount = data.citation_count || 0;
                   
                   // Ekstrak dan simpan timestamp dari respons
                   extractAndSaveTimestamp(response);
                   
                   // Update jumlah kutipan
                   const citedBySpan = document.querySelector('span.citedby');
                   if (citedBySpan) citedBySpan.textContent = citationCount;
                   
                   // Hapus overlay jika ada
                   hideLoading(citingContainer);
                   
                   // PENTING: Sembunyikan panel jika tidak ada kutipan
                   if (hideCitationPanelIfEmpty(citationCount)) {
                       // Panel disembunyikan, hentikan pemrosesan lebih lanjut
                       doiProcessed = true;
                       return;
                   }
                   
                   // Jika ada kutipan, tampilkan panel dan isi konten
                   if (citationCount > 0) {
                       // Pastikan panel terlihat
                       const panels = document.querySelectorAll('.SidePanel.doi-cited');
                       panels.forEach(panel => panel.classList.remove('u-js-hide', 'no-citations-hide'));
                       
                       // Bersihkan container
                       citingContainer.innerHTML = '';
                       
                       // Buat daftar kutipan
                       const list = document.createElement('ul');
                       list.className = 'citedby_crossref';
                       
                       // Tambahkan setiap artikel
                       // Urutkan berdasarkan tahun, dari yang terbaru
                       const sortedArticles = [...data.citing_articles].sort((a, b) => {
                           const yearA = a.year ? parseInt(a.year) : 0;
                           const yearB = b.year ? parseInt(b.year) : 0;
                           return yearB - yearA; // Urutkan dari terbaru (nilai tertinggi) ke terlama
                       });
                       
                       // Batasi jumlah maksimal kutipan yang ditampilkan ke 7 item
                       const maxCitations = 7;
                       const articlesToDisplay = sortedArticles.slice(0, maxCitations);
                       
                       articlesToDisplay.forEach((article, index) => {
                           // Buat HTML untuk penulis
                           let authorsHtml = 'Authors not available';
                           
                           if (article.authors && article.authors.length > 0) {
                               const authorElements = [];
                               
                               article.authors.forEach(author => {
                                   let authorElement = '';
                                   
                                   if (author.given && author.family) {
                                       authorElement = `<span class="given-name">${author.given}</span> <span class="family-name">${author.family}</span>`;
                                   } else if (author.family) {
                                       authorElement = `<span class="family-name">${author.family}</span>`;
                                   }
                                   
                                   if (authorElement) {
                                       authorElements.push(authorElement);
                                   }
                               });
                               
                               if (authorElements.length > 0) {
                                   authorsHtml = authorElements.join(', ');
                               }
                           }
                           
                           // FIXED: Prioritize DOI URLs for article links
                           // 1. Use DOI URL if available
                           // 2. Otherwise fallback to regular article URL
                           // 3. Only if neither is available, use a placeholder '#'
                           let articleUrl = '#';
                           
                           if (article.doi) {
                               articleUrl = `https://doi.org/${article.doi}`;
                           } else if (article.url) {
                               // Make sure the URL doesn't point to a PDF
                               articleUrl = article.url;
                               // In case the URL is for a PDF but we have no alternative,
                               // at least clean it to point to the article view if possible
                               if (articleUrl.includes('/download/')) {
                                   articleUrl = articleUrl.replace('/download/', '/view/');
                               } else if (articleUrl.includes('/article/download/')) {
                                   articleUrl = articleUrl.replace('/article/download/', '/article/view/');
                               }
                           }
                           
                           // PDF URL - separate from article URL, only for the PDF button
                           let pdfUrl = article.pdf_url || '';
                           
                           // If no specific PDF URL but we know it has a PDF, use the regular URL
                           if (!pdfUrl && article.is_pdf) {
                               pdfUrl = articleUrl;
                           }
                           
                           // Make sure PDF URL points to view, not download
                           if (pdfUrl && pdfUrl.includes('/download/')) {
                               pdfUrl = pdfUrl.replace('/download/', '/view/');
                           } else if (pdfUrl && pdfUrl.includes('/article/download/')) {
                               pdfUrl = pdfUrl.replace('/article/download/', '/article/view/');
                           }
                           
                           // Khusus untuk platform OJS
                           if (pdfUrl && pdfUrl.match(/\/(index\.php\/[^\/]+\/article\/download\/\d+\/\d+)/)) {
                               pdfUrl = pdfUrl.replace('/download/', '/viewFile/');
                           }
                           
                           // Periksa apakah jurnal ini masuk dalam daftar yang diizinkan untuk menampilkan tombol PDF
                           const showPdfButton = pdfUrl && isAllowedJournal(article.container);
                           
                           // Buat item kutipan
                           const item = document.createElement('li');
                           item.className = 'SidePanelItem article-citing';
                           item.innerHTML = `
                               <div class="sub-heading">
                                   <h3 class="related-content-panel-list-entry-outline-padding text-s u-fonts-serif" id="citing-articles-article${index+1}-title">
                                       <a class="anchor u-clamp-2-lines anchor-primary" href="${articleUrl}" target="_blank">
                                           <span class="anchor-text-container">
                                               <span class="anchor-text">
                                                   <span>${article.title}</span>
                                               </span>
                                           </span>
                                       </a>
                                   </h3>
                                   ${formatPublicationInfo(article)}
                                   <div class="authors ellipsis">
                                       ${authorsHtml}
                                   </div>
                               </div>
                               ${showPdfButton ? `
                               <div class="buttons">
                                   <a class="anchor anchor-primary anchor-icon-left anchor-with-icon" href="${pdfUrl}" target="_blank" rel="nofollow">
                                       <svg focusable="false" viewBox="0 0 35 32" height="20" width="20" class="icon icon-pdf-multicolor">
                                           <path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path>
                                           <path d="M.167 2.592H22.39V9.72H.166z" fill="#da0000"></path>
                                           <path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path>
                                           <path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path>
                                       </svg>
                                       <span class="anchor-text-container"><span class="anchor-text">View PDF</span></span>
                                   </a>
                               </div>` : ''}
                           `;
                           
                           // Fix judul HTML
                           const titleSpan = item.querySelector('.anchor-text span');
                           titleSpan.innerHTML = article.title;
                           
                           // Tambahkan ke daftar
                           list.appendChild(item);
                       });
                       
                       // Tambahkan daftar ke container
                       citingContainer.appendChild(list);
                       
                       // Tambahkan info update dengan tanggal dari API, bukan tanggal hari ini
                       let formattedDate;
                       
                       // Prioritas pertama: gunakan timestamp dari response
                       if (response.data.timestamp) {
                           formattedDate = formatTimestamp(response.data.timestamp);
                       }
                       // Prioritas kedua: gunakan last_updated dari response
                       else if (response.last_updated) {
                           formattedDate = formatISODate(response.last_updated);
                       } 
                       // Prioritas ketiga: gunakan cache_expires dari response
                       else if (response.cache_expires) {
                           formattedDate = formatISODate(response.cache_expires);
                       }
                       // Fallback: jika tidak ada informasi tanggal dari server
                       else {
                           const today = new Date();
                           const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                           formattedDate = `${today.getDate()} ${months[today.getMonth()]} ${today.getFullYear()}`;
                       }
                       
                       // Info sumber (untuk tooltip) - Format baru
                       const sourcesInfo = formatSourcesInfo(response.data.citation_sources);
                       
                       // Cek jika refresh diperbolehkan berdasarkan timestamp
                       const refreshAllowed = canRefresh();
                       
                       // Buat container flexbox untuk info dan tombol refresh
                       const infoContainer = document.createElement('div');
                       infoContainer.className = 'citing-info-container u-margin-m-top';
                       
                       // Info container
                       const infoDiv = document.createElement('div');
                       infoDiv.id = 'citing-info';
                       infoDiv.className = 'citing-info';
                       infoDiv.innerHTML = `<span class="update-info">Updated: ${formattedDate}</span>`;
                       
                       // Container untuk tombol refresh
                       const refreshContainer = document.createElement('div');
                       refreshContainer.className = 'refresh-container';
                       refreshContainer.innerHTML = refreshButtonTemplate(sourcesInfo, refreshAllowed);
                       
                       // Tambahkan keduanya ke container flexbox
                       infoContainer.appendChild(infoDiv);
                       infoContainer.appendChild(refreshContainer);
                       
                       // Tambahkan container flexbox ke citing container
                       citingContainer.appendChild(infoContainer);
                       
                       // Show/hide logic - selalu tampilkan 3 teratas, sembunyikan 4 sisanya
                       if (articlesToDisplay.length > 3) {
                           const hiddenCount = articlesToDisplay.length - 3;
                           const showText = hiddenCount === 1 ? `Show ${hiddenCount} more article` : `Show ${hiddenCount} more articles`;
                           const hideText = hiddenCount === 1 ? `Hide ${hiddenCount} article` : `Hide ${hiddenCount} more articles`;
                           
                           // Create toggle button
                           const toggleButton = document.createElement('button');
                           toggleButton.className = 'anchor button-link more-citedby-button u-margin-s-top button-link-primary button-link-icon-right';
                           toggleButton.type = 'button';
                           toggleButton.innerHTML = `
                               <span class="button-link-text-container u-mr-8">
                                   <span class="anchor-text button-link-text">${showText}</span>
                               </span>
                               <svg focusable="false" viewBox="0 0 92 128" height="20" class="icon-navigate icon-navigate-down">
                                   <path d="M1 51l7-7 38 38 38-38 7 7-45 45z"></path>
                               </svg>
                           `;
                           
                           citingContainer.appendChild(toggleButton);
                           
                           // Hide items after first 3
                           Array.from(list.querySelectorAll('li')).slice(3).forEach(li => {
                               li.style.display = 'none';
                           });
                           
                           // Toggle behavior
                           let isExpanded = false;
                           toggleButton.addEventListener('click', () => {
                               if (!isExpanded) {
                                   // Show all
                                   Array.from(list.querySelectorAll('li')).forEach(li => {
                                       li.style.display = '';
                                   });
                                   toggleButton.querySelector('.button-link-text').textContent = hideText;
                                   toggleButton.querySelector('svg').classList.add('u-flip-vertically');
                                   isExpanded = true;
                               } else {
                                   // Hide after first 3
                                   Array.from(list.querySelectorAll('li')).slice(3).forEach(li => {
                                       li.style.display = 'none';
                                   });
                                   toggleButton.querySelector('.button-link-text').textContent = showText;
                                   toggleButton.querySelector('svg').classList.remove('u-flip-vertically');
                                   isExpanded = false;
                               }
                           });
                       }
                       
                       // Event handler untuk tombol refresh
                       const refreshButton = refreshContainer.querySelector('.refresh-citations-button');
                       if (refreshButton) {
                           refreshButton.addEventListener('click', function() {
                               // Cek lagi jika refresh diperbolehkan
                               if (canRefresh() && !citingContainer.querySelector('.citation-refresh-overlay')) {
                                   showLoading(citingContainer);
                                   loadCitingArticles(doi, true);
                               } else if (!canRefresh()) {
                                   // Update tooltip jika tombol diklik meskipun nonaktif
                                   const tooltip = refreshContainer.querySelector('.citation-tooltip');
                                   if (tooltip) {
                                       tooltip.textContent = `Next refresh available in ${getCooldownTimeRemaining()}`;
                                   }
                               }
                           });
                       }
                       
                       // Schedule timer untuk memperbarui tampilan tombol refresh jika masih dalam cooldown
                       if (!refreshAllowed) {
                           const updateRefreshButtonState = () => {
                               if (canRefresh()) {
                                   // Perbarui tampilan tombol jika sudah bisa refresh
                                   const refreshContainer = document.querySelector('.refresh-container');
                                   if (refreshContainer) {
                                       const button = refreshContainer.querySelector('.refresh-citations-button');
                                       const tooltip = refreshContainer.querySelector('.citation-tooltip');
                                       
                                       if (button && tooltip) {
                                           button.disabled = false;
                                           button.classList.remove('button-disabled');
                                           tooltip.textContent = button.dataset.originalTooltip || 'Refresh citations';
                                       }
                                   }
                                   // Berhenti update timer
                                   clearInterval(timerInterval);
                               } else {
                                   // Perbarui tampilan cooldown
                                   const refreshContainer = document.querySelector('.refresh-container');
                                   if (refreshContainer) {
                                       const tooltip = refreshContainer.querySelector('.citation-tooltip');
                                       if (tooltip) {
                                           tooltip.textContent = `Next refresh available in ${getCooldownTimeRemaining()}`;
                                       }
                                   }
                               }
                           };
                           
                           // Update setiap menit
                           const timerInterval = setInterval(updateRefreshButtonState, 60000);
                           
                           // Hapus interval jika halaman ditutup
                           window.addEventListener('beforeunload', () => {
                               clearInterval(timerInterval);
                           });
                       }
                       
                   } else {
                       // Jika tidak ada kutipan, sembunyikan panel
                       hideCitationPanelIfEmpty(0);
                   }
                   
                   // Fix HTML titles lagi untuk memastikan
                   setTimeout(fixHtmlTitles, 100);
                   
                   // Set flag processed
                   doiProcessed = true;
               } else {
                   // Tampilkan error dan hapus overlay jika ada
                   hideLoading(citingContainer);
                   // Sembunyikan panel jika error
                   hideCitationPanelIfEmpty(0);
                   // Kita cetak URL yang dipanggil dan Error aslinya
                   console.warn('[Wizdam Debug] DOI yang diminta:', doi); 
                   // console.warn('[Wizdam Debug] URL API:', apiUrl);
                   console.error('[Wizdam API]: Citation fetch error:', response.message || 'Unknown error');
               }
           })
           .catch(error => {
               // Error handling
               hideLoading(citingContainer);
               // Sembunyikan panel jika error
               hideCitationPanelIfEmpty(0);
               console.error('[Wizdam API]: Citation fetch error:', error);
           });
   };
   
   // Deteksi DOI element menggunakan vanilla JS
   const detectDoiElement = () => {
       if (doiProcessed) return;
       
       const doiElement = document.querySelector('.anchor.doi');
       if (doiElement) {
           const href = doiElement.getAttribute('href');
           if (href && href.includes('https://doi.org/')) {
               const doi = href.split('https://doi.org/')[1];
               if (doi) {
                   loadCitingArticles(doi);
               }
           }
       }
   };
   
   // Initialize dengan penundaan minimal
   const init = () => {
       addStyles();
       
       // Deteksi saat DOM siap
       if (document.readyState === 'loading') {
           document.addEventListener('DOMContentLoaded', detectDoiElement);
       } else {
           detectDoiElement();
       }
       
       // Pantau perubahan DOM secara efisien dengan performance budget
       let lastCheckTime = 0;
       const THROTTLE_DELAY = 300; // ms
       
       const observer = new MutationObserver(() => {
           const now = Date.now();
           if (now - lastCheckTime > THROTTLE_DELAY) {
               lastCheckTime = now;
               detectDoiElement();
               
               // Hentikan pengamatan jika sudah diproses
               if (doiProcessed) observer.disconnect();
           }
       });
       
       // Opsi yang lebih spesifik & ringan untuk performa
       observer.observe(document.body, {
           childList: true,
           subtree: true,
           attributes: false,
           characterData: false
       });
       
       // Batas waktu observer untuk menghemat resources
       setTimeout(() => observer.disconnect(), 10000);
   };
   
   // Start the process
   init();
})();