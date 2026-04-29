/**
 * Highlights
 * Article Highlight Generator (Mendukung Bahasa Indonesia dan Inggris)
 * Menghasilkan 4 poin penting dari judul dan abstrak artikel 
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */

// Fungsi utama untuk menghasilkan highlight dari abstrak artikel
function generateArticleHighlights(title, abstract) {
  // Deteksi bahasa dari abstrak
  const language = detectLanguage(abstract);
  
  // Variabel untuk menyimpan highlight yang akan dihasilkan
  const highlights = [];
  
  // Jika bukan bahasa yang didukung, kembalikan array kosong
  if (language !== 'id' && language !== 'en') {
    console.log('Bahasa tidak didukung:', language);
    return highlights;
  }
  
  // 1. Bersihkan dan potong abstrak menjadi kalimat-kalimat
  const sentences = splitIntoSentences(abstract, language);
  if (sentences.length === 0) return highlights;
  
  // 2. Ekstrak konsep utama dari judul
  const titleConcepts = extractMainConcepts(title, language);
  
  // 3. Beri skor pada setiap kalimat
  const scoredSentences = sentences.map((sentence, index) => {
    return {
      text: sentence,
      score: scoreSentence(sentence, titleConcepts, index, sentences.length, language),
      index: index
    };
  });
  
  // 4. Urutkan kalimat berdasarkan skor (tertinggi ke terendah)
  scoredSentences.sort((a, b) => b.score - a.score);
  
  // 5. Pilih kalimat terbaik, maksimal 4 kalimat
  const selectedIndexes = new Set();
  const maxHighlights = Math.min(4, sentences.length);
  
  // Iterasi melalui kalimat, pilih yang berskor tertinggi
  for (const scored of scoredSentences) {
    // Hanya ambil 4 kalimat terbaik yang berbeda
    if (highlights.length >= maxHighlights) break;
    
    // Hindari kalimat yang terlalu dekat indeksnya (kalimat berurutan)
    if (selectedIndexes.size > 0) {
      let tooClose = false;
      for (const idx of selectedIndexes) {
        if (Math.abs(scored.index - idx) <= 1) {
          tooClose = true;
          break;
        }
      }
      if (tooClose) continue;
    }
    
    // Ambil teks highlight dari kalimat terpilih
    const highlight = createHighlight(scored.text, titleConcepts, language);
    if (highlight && highlight.length >= 5) { // minimal 5 karakter
      highlights.push(highlight);
      selectedIndexes.add(scored.index);
    }
  }
  
  // 6. Jika kurang dari 4 highlight, tambahkan dari kalimat pertama atau judul
  if (highlights.length < maxHighlights) {
    // Gunakan judul sebagai highlight jika belum 4
    if (title && !highlights.includes(title) && highlights.length < maxHighlights) {
      const titleHighlight = createHighlight(title, titleConcepts, language);
      if (titleHighlight && titleHighlight.length >= 5) {
        highlights.unshift(titleHighlight); // Tambahkan judul sebagai highlight pertama
      }
    }
    
    // Jika masih kurang, gunakan kalimat pertama yang belum terpilih
    for (let i = 0; i < sentences.length && highlights.length < maxHighlights; i++) {
      if (!selectedIndexes.has(i)) {
        const highlight = createHighlight(sentences[i], titleConcepts, language);
        if (highlight && highlight.length >= 5 && !highlights.includes(highlight)) {
          highlights.push(highlight);
        }
      }
    }
  }
  
  // Pastikan setiap highlight tidak terlalu panjang (max 200 karakter)
  return highlights.map(h => shortenHighlight(h, language)).filter(h => h.length > 0);
}

// Deteksi bahasa dari teks
function detectLanguage(text) {
  if (!text || text.trim().length === 0) return 'unknown';
  
  // Hitung kata-kata khas bahasa Indonesia vs bahasa Inggris
  const idWords = ['yang', 'dan', 'di', 'dengan', 'untuk', 'pada', 'adalah', 'ini', 'dari', 'dalam',
                   'tidak', 'akan', 'mereka', 'ke', 'telah', 'tersebut', 'oleh', 'atau', 'sebagai',
                   'dapat', 'bisa', 'menjadi', 'tentang', 'bahwa', 'secara', 'juga'];
                   
  const enWords = ['the', 'and', 'of', 'to', 'in', 'is', 'that', 'for', 'it', 'as',
                   'with', 'was', 'on', 'are', 'by', 'this', 'be', 'from', 'an', 'not',
                   'have', 'were', 'they', 'has', 'their', 'which'];
  
  // Persiapkan teks untuk analisis
  const lowercased = text.toLowerCase();
  const words = lowercased.split(/\s+/);
  
  // Hitung kemunculan kata-kata khas
  let idCount = 0;
  let enCount = 0;
  
  for (const word of words) {
    if (idWords.includes(word)) idCount++;
    if (enWords.includes(word)) enCount++;
  }
  
  // Tentukan bahasa berdasarkan perbandingan jumlah kata khas
  // Normalisasi berdasarkan jumlah kata khas dalam daftar
  const idRatio = idCount / idWords.length;
  const enRatio = enCount / enWords.length;
  
  // Gunakan rasio untuk menentukan bahasa
  if (idRatio > enRatio && idCount >= 3) {
    return 'id';
  } else if (enRatio > idRatio && enCount >= 3) {
    return 'en';
  }
  
  // Jika masih belum yakin, periksa pola gramatikal
  // Pola yang khas bahasa Indonesia
  const idPatterns = [
    /\b(yang|untuk|dengan)\s+di/i,
    /\b(telah|sudah|akan)\s+(di|me)/i,
    /\bdi\s+[a-z]+kan\b/i
  ];
  
  // Pola yang khas bahasa Inggris
  const enPatterns = [
    /\b(has|have|had)\s+been\b/i,
    /\b(is|are|was|were)\s+(the|a|an)\b/i,
    /\b(in|on|at)\s+the\b/i
  ];
  
  for (const pattern of idPatterns) {
    if (pattern.test(lowercased)) {
      return 'id';
    }
  }
  
  for (const pattern of enPatterns) {
    if (pattern.test(lowercased)) {
      return 'en';
    }
  }
  
  // Default jika tidak dapat menentukan dengan pasti
  return (idCount >= enCount) ? 'id' : 'en';
}

// Memecah teks menjadi kalimat-kalimat (mendukung bahasa Indonesia)
function splitIntoSentences(text, language) {
  if (!text) return [];
  
  // Regex yang berbeda untuk bahasa Indonesia dan Inggris
  let sentences = [];
  
  // Pra-proses: tangani singkatan umum dan pola khusus
  let preprocessed = text;
  
  if (language === 'en') {
    // Pra-proses untuk bahasa Inggris
    preprocessed = text
      .replace(/(\b[A-Z][a-z]*\.)(\s*[A-Z][a-z]*\.)+/g, match => match.replace(/\./g, '@DOT@')) // Singkatan seperti U.S.A.
      .replace(/([A-Za-z]\.)(\d)/g, '$1@DOT@$2') // Pola seperti "Fig.2" atau "p.34"
      .replace(/(\b[A-Za-z]\.)(\s+[a-z])/g, '$1@DOT@$2'); // Singkatan di tengah kalimat
      
    // Split berdasarkan tanda baca akhir kalimat untuk bahasa Inggris
    sentences = preprocessed.split(/(?<=[.!?])\s+(?=[A-Z"])/);
  } else {
    // Pra-proses untuk bahasa Indonesia
    preprocessed = text
      .replace(/(\b[A-Z][a-z]*\.)(\s*[A-Z][a-z]*\.)+/g, match => match.replace(/\./g, '@DOT@')) // Singkatan
      .replace(/([A-Za-z]\.)(\d)/g, '$1@DOT@$2') // Gambar atau halaman
      .replace(/(\b[A-Za-z]\.)(\s+[a-z])/g, '$1@DOT@$2') // Singkatan lain
      .replace(/(\bdrs?\.)(\s+[A-Z])/g, '$1@DOT@$2') // dr. atau drs.
      .replace(/(\bbpk?\.)(\s+[A-Z])/g, '$1@DOT@$2'); // bp. atau bpk.
      
    // Split berdasarkan tanda baca akhir kalimat untuk bahasa Indonesia
    // Bahasa Indonesia mungkin menggunakan huruf kecil setelah titik
    sentences = preprocessed.split(/(?<=[.!?])\s+/);
  }
  
  // Pemrosesan pasca-split: pulihkan singkatan dan perbaiki kalimat
  sentences = sentences
    .map(s => s.replace(/@DOT@/g, '.').trim())
    .filter(s => s.length > 10); // Filter kalimat terlalu pendek
  
  return sentences;
}

// Ekstrak konsep utama dari teks berdasarkan bahasa
function extractMainConcepts(text, language) {
  if (!text) return [];
  
  // Bersihkan dan lowercase
  const cleaned = text.toLowerCase()
    .replace(/[^\w\s]/g, ' ') // Ganti tanda baca dengan spasi
    .replace(/\s+/g, ' ')     // Normalisasi spasi
    .trim();
  
  // Pilih stopwords berdasarkan bahasa
  let stopWords = [];
  
  if (language === 'en') {
    // Stopwords bahasa Inggris
    stopWords = [
      'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been',
      'being', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'about', 'against', 'between',
      'into', 'through', 'during', 'before', 'after', 'above', 'below', 'from', 'up',
      'down', 'of', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here',
      'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more',
      'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so',
      'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don', 'should', 'now', 'i',
      'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours',
      'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers',
      'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves',
      'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'have', 'has',
      'had', 'having', 'do', 'does', 'did', 'doing', 'would', 'shall', 'should', 'could',
      'may', 'might', 'must', 'study', 'research', 'paper', 'article'
    ];
  } else {
    // Stopwords bahasa Indonesia
    stopWords = [
      'yang', 'di', 'dan', 'itu', 'dengan', 'untuk', 'pada', 'adalah', 'ini', 'dari', 'dalam',
      'akan', 'tidak', 'mereka', 'ke', 'kepada', 'telah', 'tersebut', 'oleh', 'atau', 'sebagai',
      'dapat', 'bisa', 'saya', 'kamu', 'kita', 'kami', 'dia', 'nya', 'anda', 'mereka', 'menjadi',
      'tentang', 'bahwa', 'secara', 'juga', 'ada', 'tanpa', 'para', 'tetapi', 'seperti', 'jika',
      'ketika', 'maka', 'karena', 'belum', 'sudah', 'saat', 'waktu', 'lebih', 'sebuah', 'hingga',
      'penelitian', 'studi', 'jurnal', 'artikel', 'makalah'
    ];
  }
  
  // Ekstrak kata-kata, filter stopwords dan kata pendek
  const words = cleaned.split(' ')
    .filter(word => word.length > 2 && !stopWords.includes(word));
  
  // Hitung frekuensi kata
  const wordFreq = {};
  words.forEach(word => {
    wordFreq[word] = (wordFreq[word] || 0) + 1;
  });
  
  // Ambil kata-kata dengan frekuensi tertinggi (max 5 kata)
  return Object.entries(wordFreq)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 5)
    .map(entry => entry[0]);
}

// Memberi skor pada kalimat berdasarkan beberapa kriteria dengan mempertimbangkan bahasa
function scoreSentence(sentence, titleConcepts, position, totalSentences, language) {
  if (!sentence) return 0;
  
  const words = sentence.toLowerCase().split(/\s+/);
  let score = 0;
  
  // 1. Skor posisi: kalimat di awal dan akhir biasanya lebih penting
  if (position === 0 || position === totalSentences - 1) {
    score += 3; // Kalimat pertama dan terakhir
  } else if (position <= Math.floor(totalSentences * 0.2)) {
    score += 2; // 20% kalimat awal
  } else if (position >= Math.floor(totalSentences * 0.8)) {
    score += 1; // 20% kalimat akhir
  }
  
  // 2. Skor panjang: kalimat terlalu pendek atau terlalu panjang kurang ideal
  const length = words.length;
  if (length >= 10 && length <= 25) {
    score += 2; // Panjang ideal
  } else if (length < 10 && length >= 5) {
    score += 1; // Agak pendek tapi masih ok
  } else if (length > 25 && length <= 40) {
    score -= 1; // Agak panjang
  } else if (length > 40) {
    score -= 2; // Terlalu panjang
  }
  
  // 3. Skor kata kunci: berisi kata-kata penting dari judul
  for (const concept of titleConcepts) {
    if (sentence.toLowerCase().includes(concept)) {
      score += 2; // Berisi konsep dari judul
    }
  }
  
  // 4. Skor indikator konten penting berdasarkan bahasa
  let importantPatterns = [];
  
  if (language === 'en') {
    // Pola untuk bahasa Inggris
    importantPatterns = [
      /\b(result|found|show|reveal|conclude|significant|important)\w*\b/i, // Hasil penting
      /\b(aim|goal|purpose|objective)\w*\b/i,                              // Tujuan
      /\b(\d+\s*%|\d+\.\d+)\b/,                                            // Statistik/angka
      /\b(increase|decrease|higher|lower|better|worse|more|less)\b/i,     // Perbandingan
      /\b(first|novel|new|unique|innovative)\b/i                          // Kebaruan
    ];
  } else {
    // Pola untuk bahasa Indonesia
    importantPatterns = [
      /\b(hasil|menunjukkan|ditemukan|disimpulkan|signifikan|penting)\w*\b/i, // Hasil penting
      /\b(tujuan|bertujuan|dimaksudkan|diharapkan)\w*\b/i,                    // Tujuan
      /\b(\d+\s*%|\d+\,\d+)\b/,                                               // Statistik/angka
      /\b(meningkat|menurun|lebih tinggi|lebih rendah|lebih baik|lebih buruk)\b/i, // Perbandingan
      /\b(pertama|baru|unik|inovatif)\b/i                                     // Kebaruan
    ];
  }
  
  for (const pattern of importantPatterns) {
    if (pattern.test(sentence)) {
      score += 1;
    }
  }
  
  return score;
}

// Buat highlight yang bermakna dari kalimat berdasarkan bahasa
function createHighlight(sentence, concepts, language) {
  if (!sentence) return '';
  
  // Bersihkan kalimat
  let highlight = sentence.trim();
  
  if (language === 'en') {
    // Hapus label bagian dalam bahasa Inggris
    highlight = highlight.replace(/^(introduction|methods|results|conclusions|discussion):\s*/i, '');
  } else {
    // Hapus label bagian dalam bahasa Indonesia
    highlight = highlight.replace(/^(pendahuluan|metode|hasil|kesimpulan|pembahasan):\s*/i, '');
  }
  
  // Normalisasi spasi
  highlight = highlight.replace(/\s+/g, ' ');
  
  // Potong kalimat jika terlalu panjang
  const words = highlight.split(/\s+/);
  if (words.length > 40) {
    // Cari titik potong yang baik
    let cutPoint = 30; // Default: 30 kata pertama
    
    // Cari titik potong berdasarkan frasa kunci sesuai bahasa
    if (language === 'en') {
      // Frasa kunci bahasa Inggris
      for (let i = 15; i < Math.min(40, words.length); i++) {
        const phrase = words.slice(i-2, i+1).join(' ').toLowerCase();
        if (phrase.match(/\b(however|therefore|thus|in conclusion|as a result|furthermore|in addition)\b/)) {
          cutPoint = i-2;
          break;
        }
      }
    } else {
      // Frasa kunci bahasa Indonesia
      for (let i = 15; i < Math.min(40, words.length); i++) {
        const phrase = words.slice(i-2, i+1).join(' ').toLowerCase();
        if (phrase.match(/\b(namun|tetapi|oleh karena itu|dengan demikian|sebagai kesimpulan|akibatnya|selain itu)\b/)) {
          cutPoint = i-2;
          break;
        }
      }
    }
    
    // Potong di titik yang ditentukan
    highlight = words.slice(0, cutPoint).join(' ');
    
    // Pastikan tidak berakhir dengan kata penghubung
    const lastWord = highlight.split(/\s+/).pop().toLowerCase();
    
    // Kata penghubung berdasarkan bahasa
    let connectors = [];
    
    if (language === 'en') {
      connectors = ['and', 'or', 'but', 'that', 'which', 'when', 'where', 'while', 'because', 
                    'as', 'if', 'for', 'to', 'with', 'by', 'from', 'of', 'in', 'on', 'at'];
    } else {
      connectors = ['dan', 'atau', 'tetapi', 'yang', 'ketika', 'dimana', 'sementara', 'karena',
                   'sebagai', 'jika', 'untuk', 'ke', 'dengan', 'oleh', 'dari', 'di', 'pada'];
    }
    
    if (connectors.includes(lastWord)) {
      highlight = highlight.substring(0, highlight.lastIndexOf(' '));
    }
    
    // Tambahkan tanda titik jika belum ada
    if (!highlight.match(/[.!?]$/)) {
      highlight += '.';
    }
  }
  
  return highlight;
}

// Membuat highlight lebih singkat dengan fokus pada bagian penting
function shortenHighlight(text, language) {
  if (!text) return '';
  
  const maxLength = 200; // Maksimal 200 karakter
  
  // Jika sudah cukup pendek, gunakan apa adanya
  if (text.length <= maxLength) return text;
  
  // Potong berdasarkan kalimat
  const sentences = text.split(/(?<=[.!?])\s+/);
  let shortened = sentences[0];
  
  // Tambahkan kalimat berikutnya jika masih dalam batas
  for (let i = 1; i < sentences.length; i++) {
    if ((shortened + ' ' + sentences[i]).length <= maxLength) {
      shortened += ' ' + sentences[i];
    } else {
      break;
    }
  }
  
  // Jika masih terlalu panjang, potong dengan cerdas
  if (shortened.length > maxLength) {
    // Cari posisi terakhir dari penanda "., !, ?"
    const lastPunctuation = Math.max(
      shortened.lastIndexOf('. ', maxLength),
      shortened.lastIndexOf('! ', maxLength),
      shortened.lastIndexOf('? ', maxLength)
    );
    
    if (lastPunctuation > maxLength / 2) {
      // Potong di tanda baca terakhir yang masih dalam batas
      shortened = shortened.substring(0, lastPunctuation + 1);
    } else {
      // Jika tidak ada tanda baca yang cocok, potong di spasi terakhir
      const lastSpace = shortened.lastIndexOf(' ', maxLength - 3);
      if (lastSpace > maxLength / 2) {
        shortened = shortened.substring(0, lastSpace) + '...';
      } else {
        // Jika tidak ditemukan titik potong yang baik, potong di batas maksimal
        shortened = shortened.substring(0, maxLength - 3) + '...';
      }
    }
  }
  
  return shortened;
}

// Fungsi untuk mendapatkan judul dan abstrak dari halaman artikel
function getArticleComponents() {
  try {
    // Komponen artikel
    const components = {
      title: "",
      abstract: ""
    };
    
    // Coba temukan judul artikel
    const titleSelectors = [
      'h1.article-title',
      'h1.title',
      'h1.page-title',
      'h1:first-of-type',
      '.article-title',
      '#article-title',
      'meta[name="citation_title"]',
      'meta[property="og:title"]'
    ];
    
    for (const selector of titleSelectors) {
      const titleElement = document.querySelector(selector);
      if (titleElement) {
        components.title = titleElement.textContent || titleElement.content || '';
        break;
      }
    }
    
    // Coba temukan abstrak artikel
    const abstractSelectors = [
      '#ab005[lang="en"] p#sp0005',
      '#ab005[lang="id"] p#sp0005',
      '.abstract',
      '#abstract',
      'section.abstract p',
      'div.abstract-content',
      '#abstract-content',
      'meta[name="description"]',
      'meta[name="citation_abstract"]'
    ];
    
    for (const selector of abstractSelectors) {
      const abstractElement = document.querySelector(selector);
      if (abstractElement) {
        components.abstract = abstractElement.innerHTML || abstractElement.content || '';
        break;
      }
    }
    
    return components;
  } catch (error) {
    console.error('Error mendapatkan komponen artikel:', error);
    return { title: "", abstract: "" };
  }
}

// Fungsi utama untuk menampilkan highlight di halaman
function displayArticleHighlights() {
  try {
    // Dapatkan judul dan abstrak artikel
    const { title, abstract } = getArticleComponents();
    
    if (!abstract) {
      console.error('Abstrak tidak ditemukan.');
      return;
    }
    
    // Deteksi bahasa abstrak
    const language = detectLanguage(abstract);
    
    // Hanya hasilkan highlight untuk bahasa yang didukung (Inggris dan Indonesia)
    if (language !== 'en' && language !== 'id') {
      console.log(`Bahasa (${language}) tidak didukung. Highlight tidak akan ditampilkan.`);
      return;
    }
    
    // Hasilkan highlight
    const highlights = generateArticleHighlights(title, abstract);
    
    // Jika tidak ada highlight yang dihasilkan, keluar
    if (highlights.length === 0) {
      console.log('Tidak ada highlight yang dihasilkan.');
      return;
    }
    
    // Cari elemen container untuk highlight
    const containerElement = document.getElementById('sp1310') || 
                           document.querySelector('.highlights-container') ||
                           document.querySelector('.article-highlights');
                           
    if (!containerElement) {
      console.error('Container untuk highlight tidak ditemukan.');
      return;
    }
    
    // Cari atau buat elemen untuk daftar highlight
    let highlightsElement = containerElement.querySelector('ul.non-list');
    if (!highlightsElement) {
      // Jika elemen tidak ditemukan, buat elemen baru
      highlightsElement = document.createElement('ul');
      highlightsElement.className = 'non-list';
      containerElement.appendChild(highlightsElement);
    }
    
    // Buat HTML untuk highlight
    let html = '';
    
    for (let highlight of highlights) {
      html += `
        <li class="react-xocs-list-item">
          <span class="list-label">• </span>
          <span class="u-ml-16"><p>${highlight}</p></span>
        </li>`;
    }
    
    highlightsElement.innerHTML = html;
    
    // Tampilkan elemen highlight jika sebelumnya tersembunyi
    const highlightSection = document.getElementById('ab810') || 
                           containerElement.closest('.highlights-section');
    if (highlightSection && highlightSection.classList.contains('u-js-hide')) {
      highlightSection.classList.remove('u-js-hide');
    }
    
    // Atur tag kredit jika ada
    const creditElement = document.querySelector('.highlights.u-font-sans');
    if (creditElement) {
      creditElement.textContent = 'Generate NLP-AI by Wizdam. v1';
    }
    
    console.log(`Highlight dalam bahasa ${language === 'en' ? 'Inggris' : 'Indonesia'} berhasil ditampilkan:`, highlights);
  } catch (error) {
    console.error('Error dalam menampilkan highlight:', error);
  }
}

// Jalankan fungsi saat DOM selesai dimuat
document.addEventListener('DOMContentLoaded', displayArticleHighlights);

// Untuk penggunaan di luar browser (misalnya dengan Node.js)
if (typeof module !== 'undefined') {
  module.exports = {
    generateArticleHighlights,
    detectLanguage,
    createHighlight,
    shortenHighlight,
    getArticleComponents
  };
}
