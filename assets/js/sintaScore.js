/**
 * Sinta Impact Factor
 * Versi dengan keamanan yang ditingkatkan dan efek skeleton loading
 * 
 * @author Rochmady
 * @version v1.04.5
 */
document.addEventListener('DOMContentLoaded', function() {
    // console.log('Memulai proses pengambilan data SINTA...');
    
    // 1. Fungsi untuk menemukan ISSN dari berbagai elemen input
    const findIssn = () => {
        const possibleElements = [
            document.getElementById('printIssn'),
            document.getElementById('eIssn'),
            document.querySelector('input[name="issn"]'),
            document.querySelector('[data-issn]')
        ].filter(el => el?.value?.trim());
        
        return possibleElements[0]?.value.trim() || null;
    };
    
    // 2. Normalisasi ISSN
    const normalizeIssn = (rawIssn) => {
        const cleaned = rawIssn?.replace(/\D/g, '');
        return cleaned?.length === 8 ? cleaned : null;
    };
    
    const rawIssn = findIssn();
    const issn = normalizeIssn(rawIssn);
    
    if (!issn) {
        // console.error('Tidak ditemukan ISSN yang valid');
        return;
    }
    
    // 3. Temukan elemen tampilan untuk score dan grade
    const sintaScoreContainer = document.querySelector('.js-sinta-score');
    const sintaGradeContainer = document.querySelector('.js-sinta-grade');
    
    if (!sintaScoreContainer) {
        // console.error('Elemen tampilan score tidak ditemukan');
        return;
    }
    
    // Elemen untuk sinta score
    const scoreElement = sintaScoreContainer.querySelector('.text-l.u-display-block');
    const scoreLabelElement = sintaScoreContainer.querySelector('.text-xs.__info');
    
    // Elemen untuk sinta grade
    const gradeElement = sintaGradeContainer?.querySelector('.text-l.u-display-block');
    const gradeLabelElement = sintaGradeContainer?.querySelector('.text-xs.__info');
    
    // 4. Tambahkan style untuk animasi secara dinamis (akan dihapus setelah selesai)
    const tempStyleId = 'temp-style-' + Math.random().toString(36).substring(2, 9);
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
    
    // 5. Fungsi untuk menambahkan efek skeleton pada elemen
    const addSkeletonEffect = (element) => {
        if (!element) return;
        
        // Simpan nilai asli
        element.dataset.originalContent = element.innerHTML;
        
        // Ukur lebar elemen asli
        const width = element.offsetWidth || 60; // Fallback jika tidak bisa diukur
        const height = element.offsetHeight || 16; // Fallback jika tidak bisa diukur
        
        // Ganti teks dengan blok skeleton
        element.innerHTML = `<span class="temp-skeleton" style="width: ${width}px; height: ${height}px;"></span>`;
    };
    
    // 6. Fungsi untuk menghapus efek skeleton
    const removeSkeletonEffect = (element, newValue) => {
        if (!element) return;
        
        // Ganti dengan nilai baru
        element.innerHTML = newValue;
    };
    
    // 7. Fungsi untuk membersihkan semua efek style
    const cleanupAllStyles = () => {
        // Hapus style yang ditambahkan
        const styleElement = document.getElementById(tempStyleId);
        if (styleElement) {
            document.head.removeChild(styleElement);
        }
    };
    
    // 8. Tampilkan efek skeleton pada semua elemen yang diperlukan
    if (sintaScoreContainer) {
        sintaScoreContainer.classList.remove('u-js-hide');
        addSkeletonEffect(scoreElement);
        addSkeletonEffect(scoreLabelElement);
    }
    
    if (sintaGradeContainer) {
        sintaGradeContainer.classList.remove('u-js-hide');
        addSkeletonEffect(gradeElement);
        addSkeletonEffect(gradeLabelElement);
    }
    
    // 9. Fungsi untuk efek highlight sederhana yang akan dihapus setelah selesai
    const addSimpleHighlight = (element) => {
        if (!element) return;
        
        // Tambahkan highlight dengan timeout untuk menghapusnya
        element.style.backgroundColor = 'rgba(255, 255, 150, 0.08)';
        
        // Hapus highlight setelah beberapa saat
        setTimeout(() => {
            element.style.backgroundColor = '';
        }, 1500);
    };
    
    // 10. Konfigurasi endpoint (menggunakan endpoint yang lebih generik)
    const proxyUrl = '/api/sinta_v2';
    
    // 11. Dapatkan CSRF token jika ada
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';
    
    // 12. Fungsi untuk format angka
    const formatNumber = (num) => {
        const n = parseFloat(num);
        return isNaN(n) ? 'N/A' : n.toFixed(3);
    };
    
    // 13. Proses fetch data dengan keamanan ditingkatkan
    const fetchData = async () => {
        try {
            // Persiapkan header keamanan
            const headers = {
                'X-Requested-With': 'XMLHttpRequest' // Header standar untuk Ajax
            };
            
            // Tambahkan CSRF token jika tersedia
            if (csrfToken) {
                headers['X-CSRF-Token'] = csrfToken;
            }
            
            // Lakukan request dengan tambahan keamanan
            const response = await fetch(`${proxyUrl}?issn=${encodeURIComponent(issn)}`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin' // Kirim cookies untuk otentikasi
            });
            
            // Tangani respons error
            if (!response.ok) {
                // Periksa kode status khusus
                if (response.status === 429) {
                    throw new Error('Rate limit exceeded. Please try again later.');
                }
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            // console.log('Data dari Sinta:', data);
            
            if (!data.success) {
                throw new Error(data.error || 'Data not valid');
            }
            
            return {
                impact: formatNumber(data.impact),
                grade: data.grade || 'N/A',
                issn: data.issn || issn
            };
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    };
    
    // 14. Eksekusi dengan timeout
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    
    fetchData({ signal: controller.signal })
        .then(({ impact, grade }) => {
            clearTimeout(timeout);
            
            // Sedikit penundaan untuk efek lebih smooth
            setTimeout(() => {
                // Update sinta score
                if (sintaScoreContainer) {
                    removeSkeletonEffect(scoreElement, impact);
                    removeSkeletonEffect(scoreLabelElement, 'SintaScore');
                    addSimpleHighlight(sintaScoreContainer);
                }
                
                // Update sinta grade
                if (sintaGradeContainer) {
                    removeSkeletonEffect(gradeElement, `<span class="grade">Sinta</span> ${grade}`);
                    removeSkeletonEffect(gradeLabelElement, 'SintaGrade');
                    addSimpleHighlight(sintaGradeContainer);
                    
                    // Ubah atribut title menjadi aria-title
                    sintaGradeContainer.setAttribute('aria-title', `Sinta Impact ${impact} | National Grade Accredited ${grade}`);
                }
                
                // Bersihkan semua efek style setelah beberapa saat
                setTimeout(cleanupAllStyles, 2000);
                
            }, 800);
        })
        .catch(error => {
            clearTimeout(timeout);
            console.error('Gagal:', error);
            
            // Sedikit penundaan untuk efek lebih smooth
            setTimeout(() => {
                if (sintaScoreContainer) {
                    removeSkeletonEffect(scoreElement, 'N/A');
                    removeSkeletonEffect(scoreLabelElement, `<span style="color:#dc3545">${
                        error.message.includes('HTTP') ? 'Server Error' : 'Not found'
                    }</span>`);
                }
                
                // Jika terjadi error, kembalikan grade ke nilai default
                if (sintaGradeContainer) {
                    removeSkeletonEffect(gradeElement, gradeElement.dataset.originalContent || 'N/A');
                    removeSkeletonEffect(gradeLabelElement, gradeLabelElement.dataset.originalContent || 'SintaGrade');
                }
                
                // Bersihkan semua efek style
                cleanupAllStyles();
                
            }, 800);
        });
});