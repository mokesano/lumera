/**
 * Format indeks afiliasi penulis pada artikel
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
 */
document.addEventListener("DOMContentLoaded", function() {
    function processAffiliations() {
        var authors = document.querySelectorAll("#author-group a");
        var affiliations = document.querySelectorAll(".affiliation");

        var affMap = new Map();
        var newIndex = new Map();
        var currentIndex = 1;

        // Mengumpulkan semua afiliasi unik dan memberi mereka indeks
        affiliations.forEach(function(affiliation) {
            var affTexts = affiliation.querySelector("dd").textContent.split("\n").map(function(text) {
                return text.trim();
            }).filter(function(text) {
                return text.length > 0;
            });

            affTexts.forEach(function(affText) {
                if (!affMap.has(affText)) {
                    affMap.set(affText, currentIndex);
                    currentIndex++;
                }
                var sup = affiliation.querySelector("dt sup").textContent;
                if (!newIndex.has(sup)) {
                    newIndex.set(sup, []);
                }
                newIndex.get(sup).push(affMap.get(affText));
            });
        });

        // Fungsi untuk membuat ID sesuai format berdasarkan nilai indeks
        function formatAffiliationId(index) {
            if (index < 10) {
                return "baff000" + index;
            } else if (index < 100) {
                return "baff00" + index;
            } else if (index < 1000) {
                return "baff0" + index;
            } else {
                return "baff" + index;
            }
        }

        // Memperbarui elemen afiliasi dengan indeks yang benar
        affiliations.forEach(function(affiliation) {
            var sup = affiliation.querySelector("dt sup");
            var affTexts = affiliation.querySelector("dd").textContent.split("\n").map(function(text) {
                return text.trim();
            }).filter(function(text) {
                return text.length > 0;
            });
            var affIndexes = affTexts.map(function(affText) {
                return affMap.get(affText);
            }).join(", ");
            sup.textContent = affIndexes;
        });

        // Memperbarui elemen penulis dengan indeks afiliasi yang benar
        authors.forEach(function(author) {
            var authorRefSpan = author.querySelector(".author-ref");
            var sups = authorRefSpan.querySelectorAll("sup");
            
            // Clear existing sup elements
            sups.forEach(function(sup) {
                sup.remove();
            });
            
            // Get original indices
            var originalIndices = [];
            if (sups.length > 0) {
                sups.forEach(function(sup) {
                    var indices = sup.textContent.split(",").map(function(idx) {
                        return idx.trim();
                    });
                    originalIndices = originalIndices.concat(indices);
                });
            } else {
                // If no sups found, try to get from ID
                var id = authorRefSpan.id;
                if (id && id.startsWith("baff")) {
                    // Extract the numeric part regardless of how many digits
                    var numericPart = id.replace("baff", "").replace(/^0+/, "");
                    if (numericPart) {
                        originalIndices.push(numericPart);
                    }
                }
            }
            
            // Map original indices to new ones
            var newIndices = [];
            originalIndices.forEach(function(idx) {
                if (newIndex.has(idx)) {
                    newIndices = newIndices.concat(newIndex.get(idx));
                }
            });
            
            // Remove duplicates and sort
            var uniqueNewIndices = newIndices.filter(function(item, pos) {
                return newIndices.indexOf(item) === pos;
            }).sort(function(a, b) {
                return a - b;
            });
            
            // Create the new structure with separate spans for each affiliation
            if (uniqueNewIndices.length > 0) {
                // Remove the existing author-ref span
                var parent = authorRefSpan.parentNode;
                var nextSibling = authorRefSpan.nextSibling;
                authorRefSpan.remove();
                
                // Create new spans for each affiliation index
                uniqueNewIndices.forEach(function(index, i) {
                    var newSpan = document.createElement("span");
                    newSpan.className = "author-ref";
                    newSpan.id = formatAffiliationId(index);
                    
                    var supElement = document.createElement("sup");
                    if (i < uniqueNewIndices.length - 1) {
                        supElement.textContent = index + ",";
                    } else {
                        supElement.textContent = index;
                    }
                    
                    newSpan.appendChild(supElement);
                    
                    // Insert the new span
                    if (nextSibling) {
                        parent.insertBefore(newSpan, nextSibling);
                    } else {
                        parent.appendChild(newSpan);
                    }
                });
            }
        });

        // Menghapus afiliasi duplikat dan menyusun ulang afiliasi berdasarkan indeks
        var uniqueAffiliations = [];
        var uniqueAffiliationsMap = new Map();
        var affIndex = 1;

        document.querySelectorAll(".affiliation-group .affiliation").forEach(function(affiliation) {
            var affTexts = affiliation.querySelector("dd").textContent.split("\n").map(function(text) {
                return text.trim();
            }).filter(function(text) {
                return text.length > 0;
            });
            affTexts.forEach(function(affText) {
                if (!uniqueAffiliationsMap.has(affText)) {
                    uniqueAffiliationsMap.set(affText, affIndex);
                    uniqueAffiliations.push({ text: affText, index: affIndex });
                    affIndex++;
                }
            });
        });

        // Menampilkan afiliasi unik
        var affGroup = document.querySelector(".affiliation-group");
        if (affGroup) {
            affGroup.innerHTML = '';
        }
        uniqueAffiliations.forEach(function(aff) {
            var dl = document.createElement("dl");
            dl.className = "affiliation";
            var dt = document.createElement("dt");
            var sup = document.createElement("sup");
            sup.textContent = aff.index;
            dt.appendChild(sup);
            var dd = document.createElement("dd");
            dd.textContent = aff.text;
            dl.appendChild(dt);
            dl.appendChild(dd);
            affGroup.appendChild(dl);
        });

        // Menyimpan status di sessionStorage untuk mencegah pemrosesan ulang
        sessionStorage.setItem("affiliationsProcessed", "true");

        // Menampilkan elemen yang telah diproses (dengan pengecekan)
        
        // 1. Cek & tampilkan affiliation-group
        const affGroupEl = document.querySelector(".affiliation-group");
        if (affGroupEl) {
            affGroupEl.style.display = '';
        }

        // 2. Cek & tampilkan author-group
        const authGroupEl = document.querySelector("#author-group");
        if (authGroupEl) {
            authGroupEl.style.display = '';
        }
    }

    // Inisialisasi pertama kali
    processAffiliations();

    var observer = new MutationObserver(function(mutations) {
        if (sessionStorage.getItem("affiliationsProcessed") !== "true") {
            processAffiliations();
        }
    });
    
    var config = { childList: true, subtree: true, characterData: true };
    var target = document.querySelector('.affiliation-group');
    if (target) {
        observer.observe(target, config);
    }
});

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
    console.error('[Wizdam Highlight]: Error mendapatkan komponen artikel:', error);
    return { title: "", abstract: "" };
  }
}

// Fungsi utama untuk menampilkan highlight di halaman
function displayArticleHighlights() {
  try {
    // Dapatkan judul dan abstrak artikel
    const { title, abstract } = getArticleComponents();
    
    if (!abstract) {
      console.error('[Wizdam Highlight]: Abstrak tidak ditemukan.');
      return;
    }
    
    // Deteksi bahasa abstrak
    const language = detectLanguage(abstract);
    
    // Hanya hasilkan highlight untuk bahasa yang didukung (Inggris dan Indonesia)
    if (language !== 'en' && language !== 'id') {
      console.log(`[Wizdam Highlight]: Bahasa (${language}) tidak didukung. Highlight tidak akan ditampilkan.`);
      return;
    }
    
    // Hasilkan highlight
    const highlights = generateArticleHighlights(title, abstract);
    
    // Jika tidak ada highlight yang dihasilkan, keluar
    if (highlights.length === 0) {
      console.log('[Wizdam Highlight]: Tidak ada highlight yang dihasilkan.');
      return;
    }
    
    // Cari elemen container untuk highlight
    const containerElement = document.getElementById('sp1310') || 
                           document.querySelector('.highlights-container') ||
                           document.querySelector('.article-highlights');
                           
    if (!containerElement) {
      console.error('[Wizdam Highlight]: Container untuk highlight tidak ditemukan.');
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
      creditElement.textContent = 'Generate AI by Wizdam';
    }
    
    console.log(`[Wizdam Highlight]: Highlight dalam bahasa ${language === 'en' ? 'Inggris' : 'Indonesia'} berhasil ditampilkan:`, highlights);
  } catch (error) {
    console.error('[Wizdam Highlight]: Error dalam menampilkan highlight:', error);
  }
}

// Jalankan fungsi saat DOM selesai dimuat (dengan pengecekan)
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan fungsi displayArticleHighlights ada sebelum dijalankan
    if (typeof displayArticleHighlights === 'function') {
        displayArticleHighlights();
    }
});

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

/**
 * Smart Link References (View PDF, DOI, dan GS)
 * 1. Fungsi untuk mengelola tampilan elemen <p> pada kelas .bibliography-sec.
 * 2. Jika jumlah elemen <p> melebihi 17, elemen-elemen tersebut akan dihapus, menampilkan pemberitahuan menggunakan tombol view-more yang sudah ada untuk mengatur visibilitas elemen.
 * 3. Mengupdate total elemen <p> dielemen .section-title dispan class "count".
 * @author Rochmady and Wizdam Team
 * @version 7.0.0
 */
document.addEventListener('DOMContentLoaded', function () {
    const bibliographySections = document.querySelectorAll('.ref-bibliography');
    let counter = 7;

    const removeExcessItems = (items) => items.map(item => {
        item.parentNode.removeChild(item);
        return item;
    });

    const toggleItemsVisibility = (section, viewMoreButton, notice, items) => {
        const isExpanded = viewMoreButton.classList.contains('view-less');
        if (isExpanded) {
            removeExcessItems(items);
            section.appendChild(notice);
            viewMoreButton.querySelector('.button-alternative-text').textContent = 'View more references';
            viewMoreButton.classList.remove('view-less');
            viewMoreButton.querySelector('svg').style.transform = 'rotate(0deg)';
        } else {
            items.forEach(item => {
                section.insertBefore(item, viewMoreButton);
            });
            section.removeChild(notice);
            viewMoreButton.querySelector('.button-alternative-text').textContent = 'View less references';
            viewMoreButton.classList.add('view-less');
            viewMoreButton.querySelector('svg').style.transform = 'rotate(180deg)';
        }
    };

    const splitParagraphs = (section) => {
        const paragraphs = section.querySelectorAll('p');
        paragraphs.forEach(paragraph => {
            if (paragraph.innerHTML.includes('<br>')) {
                const splitContent = paragraph.innerHTML.split('<br>');
                splitContent.forEach((content, index) => {
                    if (index === 0) {
                        paragraph.innerHTML = content.trim();
                    } else {
                        const newParagraph = document.createElement('p');
                        newParagraph.innerHTML = content.trim();
                        paragraph.parentNode.insertBefore(newParagraph, paragraph.nextSibling);
                    }
                });
            }
        });
    };

    bibliographySections.forEach(section => {
        splitParagraphs(section);
        let items = Array.from(section.querySelectorAll('p')).filter(item => item.textContent.trim() !== "");
        items.forEach(item => {
            if (!item.hasAttribute('id') && !item.hasAttribute('class')) {
                item.setAttribute('id', `ref-sangia${String(counter).padStart(3, '0')}`);
                item.setAttribute('class', 'reference');
                counter++;
            }
        });

        const totalItems = items.length;
        const sectionTitle = section.closest('.bibliography').querySelector('.section-title .count');
        if (sectionTitle) sectionTitle.textContent = `(${totalItems})`;

        const viewMoreButton = section.querySelector('.view-more');
        const notice = section.querySelector('.notice');

        if (totalItems > 17) {
            const removedItems = removeExcessItems(items.slice(17));
            section.appendChild(notice);
            section.appendChild(viewMoreButton);
            viewMoreButton.addEventListener('click', () => toggleItemsVisibility(section, viewMoreButton, notice, removedItems));
        } else {
            section.removeChild(notice);
            section.removeChild(viewMoreButton);
        }
    });
});

$(document).ready(function() {
    const toggleLoading = show => {
        const loadingSvg = `<div class="loading-indicator"><svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="#000">
            <circle cx="50" cy="50" r="35" stroke-width="10" stroke="#000" fill="none"></circle>
            <circle cx="50" cy="50" r="25" stroke-width="10" stroke="#000" fill="none">
                <animate attributeName="r" from="25" to="35" dur="1s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="1s" repeatCount="indefinite" />
            </circle>
            <circle cx="50" cy="50" r="15" stroke-width="8" stroke="#585858" fill="none">
                <animate attributeName="r" from="15" to="25" dur="0.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="0.8s" repeatCount="indefinite" />
            </circle></svg></div>`;
        show ? $('body').append(loadingSvg) : $('.loading-indicator').remove();
    };

    const extractTitle = content => {
        const yearPattern = /\b\d{4}[a-z]?\b/;
        const yearMatch = content.match(yearPattern);
        if (yearMatch) {
            const yearIndex = content.indexOf(yearMatch[0]);
            const titleStart = content.indexOf('. ', yearIndex) + 2;
            let titleEnd = content.indexOf('. ', titleStart);
            if (titleEnd === -1) titleEnd = content.length;
            return content.substring(titleStart, titleEnd).trim().replace(/<[^>]*>/g, '');
        }
        return '';
    };

    const isElementEmpty = element => $(element).text().trim().length === 0;

    const addLinkWithLoading = (referenceLinks, href, text, svgIcon) => {
        const loadingSvg = `<svg class="icon" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" viewBox="0 0 100 100" width="20" height="20" fill="#585858">
            <circle cx="50" cy="50" r="25" stroke-width="10" stroke="#000" fill="none">
                <animate attributeName="r" from="25" to="35" dur="1s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="1s" repeatCount="indefinite" />
            </circle>
            <circle cx="50" cy="50" r="15" stroke-width="8" stroke="#585858" fill="none">
                <animate attributeName="r" from="15" to="25" dur="0.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="0.8s" repeatCount="indefinite" />
            </circle></svg>`;
        let link = $(text === 'View PDF'
            ? `<a class="anchor link anchor-primary" href="${href}" target="_blank" aria-label="${text}">
                <span class="anchor-text-container">${svgIcon}<span class="anchor-text">${text}</span></span></a>`
            : `<a class="anchor link anchor-primary" href="${href}" target="_blank" aria-label="${text}">
                <span class="anchor-text-container"><span class="anchor-text">${text}</span>${svgIcon}</span></a>`
        );
        referenceLinks.append(link);
        link.prepend(loadingSvg);
        setTimeout(() => link.find('svg').first().remove(), 2000);
    };

    const addCrossrefLinks = (item, referenceLinks) => {
        const pdfLink = item.link && item.link.length > 0 ? item.link.find(link => link['content-type'] === 'application/pdf').URL : null;
        const articleLink = item.URL;
        const publisherLink = item.resource.primary ? item.resource.primary.url : null;

        if (pdfLink) addLinkWithLoading(referenceLinks, pdfLink, 'View PDF', '<svg focusable="false" viewBox="0 0 32 32" class="icon" part="icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>');
        if (articleLink) addLinkWithLoading(referenceLinks, articleLink, 'View Article', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
        if (publisherLink) addLinkWithLoading(referenceLinks, publisherLink, 'View at Publisher', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
    };

    const disableActiveLinks = element => $(element).find('a').each(function() {
        const text = $(this).text();
        $(this).replaceWith(text);
    });

    const processVisibleReferences = () => {
        $('.reference').each(function() {
            const referenceElement = $(this);
            if (isElementEmpty(referenceElement) || referenceElement.find('.ReferenceLinks').length > 0) return;

            disableActiveLinks(referenceElement);
            const content = referenceElement.html();
            const journalName = $('meta[property="journal_name"]').attr('content');
            const referenceLinks = $('<div class="ReferenceLinks text-s"></div>');
            referenceElement.append(referenceLinks);

            // Ganti atau tambah nama jurnal sesuai kebutuhan
            const journalNames = [
                "Jurnal Akuatiklestari",
                "Agrikan: Jurnal Agribisnis Perikanan",
                "Jurnal Ilmu dan Teknologi Kelautan Tropis"
            ];

            const promises = [];
            const title = extractTitle(content);

            const cacheKey = `crossref_${title}`;
            const cachedData = localStorage.getItem(cacheKey);

            const handleCrossrefResponse = (item) => {
                localStorage.setItem(cacheKey, JSON.stringify(item));
                addCrossrefLinks(item, referenceLinks);
            };

            if (cachedData) {
                const item = JSON.parse(cachedData);
                addCrossrefLinks(item, referenceLinks);
            } else {
                if (content.includes(journalName)) {
                    promises.push($.ajax({
                        url: "https://api.crossref.org/works?query.bibliographic=" + encodeURIComponent(title),
                        success: response => {
                            if (response.message.items.length > 0) handleCrossrefResponse(response.message.items[0]);
                        },
                        error: () => console.error('[Wizdam API]: Failed to fetch data from Crossref.')
                    }));
                }

                journalNames.forEach(journal => {
                    if (content.includes(journal)) {
                        promises.push($.ajax({
                            url: "https://api.crossref.org/works?query.bibliographic=" + encodeURIComponent(title),
                            success: response => {
                                if (response.message.items.length > 0) handleCrossrefResponse(response.message.items[0]);
                            },
                            error: () => console.error('[Wizdam API]: Failed to fetch data from Crossref.')
                        }));
                    }
                });
            }

            const httpMatches = content.match(/https?:\/\/[^\s]+|www\.[^\s]+/g);
            if (httpMatches) {
                httpMatches.forEach(httpUrl => {
                    if (!httpUrl.includes('doi.org')) addLinkWithLoading(referenceLinks, httpUrl, 'View Source', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
                });
            }

            const doiMatch = content.match(/(?:doi:\s*|DOI:\s*|https:\/\/doi\.org\/|http:\/\/dx\.doi\.org\/)(\S+)/i);
            if (doiMatch) {
                let doiUrl = doiMatch[1].replace(/\.$/, '');
                if (doiUrl.startsWith('10.')) doiUrl = 'https://doi.org/' + doiUrl;
                addLinkWithLoading(referenceLinks, doiUrl, 'Crossref', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
            }

            const titleForGoogleScholar = extractTitle(content);
            if (titleForGoogleScholar) addLinkWithLoading(referenceLinks, 'https://scholar.google.com/scholar_lookup?title=' + encodeURIComponent(titleForGoogleScholar), 'Google Scholar', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');

            Promise.all(promises).then(() => {
                const links = referenceLinks.children('a');
                links.sort((a, b) => ['View PDF', 'View at Publisher', 'View Article', 'View Source', 'Crossref', 'Google Scholar'].indexOf($(a).text().trim()) - ['View PDF', 'View at Publisher', 'View Article', 'View Source', 'Crossref', 'Google Scholar'].indexOf($(b).text().trim()));
                referenceLinks.append(links);
            }).catch(() => console.error('An error occurred while processing references.'));
        });
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                toggleLoading(true);
                processVisibleReferences();
                toggleLoading(false);
                observer.unobserve(entry.target);
            }
        });
    });

    $('.reference').each(function() {
        observer.observe(this);
    });

    $('.view-more').on('click', function() {
        toggleLoading(true);
        setTimeout(() => {
            processVisibleReferences();
            toggleLoading(false);
        }, 500);
    });
});

/**
 * Pengaturan Referensi dibuat oleh: ChatGPT, sebuah model AI dari OpenAI
 * Dibantu oleh: Rochmady and Wizdam, pengguna yang luar biasa
 */