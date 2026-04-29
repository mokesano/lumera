<?php
/**
 * SDG Classification API
 * 
 * API untuk menganalisis karya ilmiah dan peneliti berdasarkan Sustainable Development Goals (SDGs).
 * Mendukung analisis berdasarkan ORCID peneliti dan DOI artikel.
 * 
 * Fitur Utama:
 * - Klasifikasi SDG berdasarkan analisis kata kunci dan NLP (cosine similarity)
 * - Mendukung 17 kategori SDG dengan pengayaan kata kunci (30+ kata kunci per SDG)
 * - Dukungan kata kunci dalam bahasa Inggris dan Indonesia
 * - Sistem caching untuk meningkatkan performa melalui file JSON terkompresi
 * - Ekstraksi informasi dasar peneliti dari ORCID (nama, institusi)
 * - Ekstraksi informasi artikel lengkap termasuk abstrak dari DOI
 * 
 * Fungsi-fungsi Utama:
 * - handleOrcidRequest($orcid, $force_refresh): Menangani permintaan analisis berdasarkan ORCID
 * - handleDoiRequest($doi, $force_refresh): Menangani permintaan analisis berdasarkan DOI
 * - processOrcidData($orcid, $works_data, $person_data): Memproses data ORCID untuk analisis SDG
 * - processDoiData($doi, $data): Memproses data artikel berdasarkan DOI
 * - scoreSDGs($text): Menghitung skor SDG berdasarkan frekuensi kemunculan kata kunci
 * - calculateSDGSimilarity($text): Menghitung cosine similarity antara teks dan kategori SDG
 * - combineScores($keywordScores, $similarityScores): Menggabungkan hasil analisis kata kunci dan similarity
 * 
 * Penggunaan:
 * - Analisis Peneliti: ?orcid=0000-0002-5152-9727
 * - Analisis Artikel: ?doi=10.1234/example
 * - Refresh Cache: &refresh=true
 * 
 * @author Rochmady
 * @version 4.1.7
 * @license MIT
 */
header('Content-Type: application/json; charset=utf-8');

// ==============================================
// KONFIGURASI UMUM
// ==============================================
$CACHE_DIR = __DIR__ . '/cache';
if (!is_dir($CACHE_DIR)) {
    mkdir($CACHE_DIR, 0755, true);
}

// ==============================================
// KONFIGURASI SDGs DENGAN PENGAYAAN KATA KUNCI
// ==============================================
$SDG_KEYWORDS = [
    "SDG1" => [
        // Bahasa Inggris
        "poverty", "inequality", "social protection", "economic disparity", "vulnerable population", 
        "basic services", "financial inclusion", "social security", "welfare", "homelessness",
        "slum", "basic income", "extreme poverty", "social safety net", "underprivileged",
        "income inequality", "marginalized communities", "poverty eradication", "poverty reduction",
        "socioeconomic", "disadvantaged", "low-income", "resource allocation", "poverty line",
        "inclusive growth", "pro-poor", "rural poverty", "urban poverty", "wealth distribution",
        "social mobility", "income distribution", "microfinance",
        // Bahasa Indonesia
        "kemiskinan", "ketimpangan", "perlindungan sosial", "kesenjangan ekonomi", "populasi rentan",
        "layanan dasar", "inklusi keuangan", "jaminan sosial", "kesejahteraan", "tunawisma",
        "permukiman kumuh", "pendapatan dasar", "kemiskinan ekstrem", "jaring pengaman sosial",
        "masyarakat kurang mampu", "pengentasan kemiskinan", "pengurangan kemiskinan",
        "pertumbuhan inklusif", "pendapatan rendah", "ketimpangan pendapatan", "akses layanan dasar",
        "mobilitas sosial", "distribusi kekayaan", "pembangunan pro-rakyat", "pemberdayaan masyarakat miskin",
        "pembiayaan mikro", "komunitas terpinggirkan"
    ],
    "SDG2" => [
        // Bahasa Inggris
        "hunger", "food security", "agriculture", "nutrition", "sustainable farming", "food system",
        "malnutrition", "crop", "livestock", "irrigation", "food production", "agricultural productivity",
        "food access", "food shortage", "farming", "food waste", "food supply", "food safety",
        "rural development", "food sovereignty", "sustainable agriculture", "agro-ecology",
        "food price", "food inflation", "agricultural research", "fisheries", "aquaculture",
        "agricultural innovation", "food distribution", "hunger eradication", "famine", "agricultural policy",
        // Bahasa Indonesia
        "kelaparan", "ketahanan pangan", "pertanian", "nutrisi", "pertanian berkelanjutan",
        "sistem pangan", "malnutrisi", "tanaman", "ternak", "irigasi", "produksi pangan",
        "akses pangan", "kekurangan pangan", "limbah pangan", "keamanan pangan",
        "pengembangan pedesaan", "kedaulatan pangan", "harga pangan", "perikanan", "akuakultur",
        "inovasi pertanian", "distribusi pangan", "penghapusan kelaparan", "krisis pangan",
        "kebijakan pertanian"
    ],
    "SDG3" => [
        // Bahasa Inggris
        "health", "disease", "vaccine", "mental health", "infectious disease", "public health",
        "child mortality", "maternal health", "hospital", "clinical", "HIV", "malaria", "tuberculosis",
        "noncommunicable", "sanitation", "wellbeing", "pandemic", "epidemic", "medical treatment",
        "healthcare", "doctor", "nurse", "surgery", "injury", "medication", "immunization",
        "nutrition", "hospitalization", "health policy", "life expectancy", "patient care", "healthcare access",
        "preventive medicine", "medical research", "wellness",
        // Bahasa Indonesia
        "kesehatan", "penyakit", "vaksin", "kesehatan mental", "penyakit menular", "kesehatan masyarakat",
        "kematian anak", "kesehatan ibu", "rumah sakit", "klinis", "imunisasi", "pengobatan",
        "perawatan", "obat-obatan", "dokter", "perawat", "sanitasi", "gizi", "akses layanan kesehatan",
        "pengobatan preventif", "harapan hidup", "penelitian medis", "pandemi", "epidemi"
    ],
    "SDG4" => [
        // Bahasa Inggris
        "education", "learning", "school", "teaching", "literacy", "higher education", "academic",
        "curriculum", "classroom", "student", "educational policy", "distance learning", "e-learning",
        "teacher training", "vocational training", "lifelong learning", "primary education",
        "secondary education", "university", "educational resources", "scholarship", "educational access",
        "education quality", "schooling", "science education", "pedagogy", "educational inequality",
        "educational technology", "inclusive education", "special education", "early childhood education", "STEM",
        // Bahasa Indonesia
        "pendidikan", "pembelajaran", "sekolah", "pengajaran", "literasi", "pendidikan tinggi",
        "akademik", "kurikulum", "ruang kelas", "siswa", "murid", "pelajar", "mahasiswa",
        "kebijakan pendidikan", "pembelajaran jarak jauh", "e-learning", "pelatihan guru",
        "pelatihan vokasi", "belajar sepanjang hayat", "pendidikan dasar", "pendidikan menengah",
        "akses pendidikan", "kualitas pendidikan", "kesetaraan pendidikan", "teknologi pendidikan",
        "pendidikan inklusif", "pendidikan khusus", "pendidikan anak usia dini", "STEM"
    ],
    "SDG5" => [
        // Bahasa Inggris
        "gender equality", "women empowerment", "gender discrimination", "gender-based violence",
        "gender parity", "equal rights", "gender gap", "female participation", "gender mainstreaming",
        "feminism", "sexual harassment", "gender stereotypes", "gender bias", "women's rights",
        "women in leadership", "women's health", "gender perspective", "gender analysis",
        "gender inclusive", "gender sensitive", "maternal", "women's education", "women entrepreneurship",
        "gender equity", "women workforce", "women representation", "gender pay gap", "sexual violence",
        "women's economic empowerment", "gender diversity", "gender identity", "women in stem",
        // Bahasa Indonesia
        "kesetaraan gender", "pemberdayaan perempuan", "diskriminasi gender", "kekerasan berbasis gender",
        "paritas gender", "hak perempuan", "kesetaraan hak", "partisipasi perempuan",
        "kepemimpinan perempuan", "kesehatan perempuan", "pendidikan perempuan", "pengusaha perempuan",
        "kesenjangan upah", "kekerasan seksual", "keragaman gender", "perspektif gender",
        "analisis gender", "inklusif gender", "sensitivitas gender", "identitas gender"
    ],
    "SDG6" => [
        // Bahasa Inggris
        "clean water", "sanitation", "water quality", "wastewater", "water access", "water shortage",
        "water resource", "water management", "water pollution", "drinking water", "water supply",
        "water scarcity", "water utility", "water treatment", "water reuse", "water conservation",
        "handwashing", "hygiene", "water system", "water infrastructure", "water security",
        "contaminated water", "groundwater", "watershed", "water stress", "water efficiency",
        "water harvesting", "water filtration", "sustainable water", "water monitoring", "hydrological",
        "water governance", "water cycle",
        // Bahasa Indonesia
        "air bersih", "sanitasi", "kualitas air", "air limbah", "akses air", "kelangkaan air",
        "sumber daya air", "pengelolaan air", "pencemaran air", "air minum", "pasokan air",
        "pengolahan air", "konservasi air", "cuci tangan", "kebersihan", "infrastruktur air",
        "keamanan air", "air tanah", "tangkapan air", "daur ulang air", "efisiensi air"
    ],
    "SDG7" => [
        // Bahasa Inggris
        "renewable energy", "clean energy", "energy access", "energy efficiency", "sustainable energy",
        "solar energy", "wind energy", "hydropower", "geothermal", "biomass energy", "biofuel",
        "energy storage", "energy infrastructure", "energy grid", "energy security", "electricity access",
        "power generation", "green energy", "energy poverty", "energy conservation", "energy policy",
        "energy transition", "fossil fuel", "carbon emission", "energy consumption", "energy production",
        "alternative energy", "fuel efficiency", "energy innovation", "energy resources", "energy system",
        "energy technology", "net zero",
        // Bahasa Indonesia
        "energi terbarukan", "energi bersih", "akses energi", "efisiensi energi", "energi berkelanjutan",
        "energi surya", "energi angin", "tenaga air", "panas bumi", "energi biomassa", "biofuel",
        "infrastruktur energi", "jaringan listrik", "keamanan energi", "pembangkit listrik",
        "kemiskinan energi", "konservasi energi", "transisi energi", "energi alternatif"
    ],
    "SDG8" => [
        // Bahasa Inggris
        "economic growth", "employment", "decent work", "job creation", "labor market", "productivity",
        "entrepreneurship", "sustainable tourism", "financial services", "labor rights", "workforce",
        "business development", "small enterprises", "medium enterprises", "job security", "labor policy",
        "economic development", "economic diversification", "economic productivity", "formal employment",
        "informal employment", "unemployment", "underemployment", "labor standards", "economic opportunity",
        "job training", "job skills", "economic resilience", "economic inclusion", "income growth",
        "livelihood", "worker protection", "full employment",
        // Bahasa Indonesia
        "pertumbuhan ekonomi", "lapangan kerja", "pekerjaan layak", "penciptaan lapangan kerja",
        "pasar tenaga kerja", "produktivitas", "kewirausahaan", "pariwisata berkelanjutan",
        "layanan keuangan", "hak tenaga kerja", "pengembangan bisnis", "usaha kecil", "usaha menengah",
        "keamanan kerja", "pengangguran", "setengah pengangguran", "peluang ekonomi", "pelatihan kerja",
        "ketahanan ekonomi", "inklusivitas ekonomi", "pendapatan berkelanjutan"
    ],
    "SDG9" => [
        // Bahasa Inggris
        "infrastructure", "innovation", "industrialization", "technology", "research development",
        "manufacturing", "industrial diversification", "technological capabilities", "industrial policy",
        "sustainable infrastructure", "resilient infrastructure", "industrial growth", "industrial productivity",
        "scientific research", "information technology", "communication technology", "technological innovation",
        "digital divide", "digital access", "digital inclusion", "internet access", "broadband",
        "rural infrastructure", "transportation infrastructure", "clean technology", "technology transfer",
        "R&D investment", "small-scale industry", "medium-scale industry", "engineering", "technical capacity",
        "digital infrastructure", "industrial development",
        // Bahasa Indonesia
        "infrastruktur", "inovasi", "industrialisasi", "teknologi", "penelitian dan pengembangan",
        "manufaktur", "diversifikasi industri", "kapasitas teknologi", "kebijakan industri",
        "infrastruktur berkelanjutan", "infrastruktur tangguh", "pertumbuhan industri",
        "produktivitas industri", "riset ilmiah", "teknologi informasi", "teknologi komunikasi",
        "inovasi teknologi", "akses digital", "inklusivitas digital", "akses internet", "broadband"
    ],
    "SDG10" => [
        // Bahasa Inggris
        "reduced inequalities", "migration", "income inequality", "social inclusion", "equality",
        "equal opportunity", "social protection", "fiscal policy", "discriminatory policies", "social inequality",
        "economic inequality", "wage gap", "social disparity", "economic disparity", "social exclusion",
        "marginalized", "social mobility", "wealth distribution", "income distribution", "migrant rights",
        "minority rights", "racial equality", "gender equality", "social equity", "economic empowerment",
        "inclusive society", "wage discrimination", "social status", "socioeconomic status", "disadvantaged groups",
        "affirmative action", "economic opportunity", "inequality reduction",
        // Bahasa Indonesia
        "ketimpangan berkurang", "migrasi", "ketimpangan pendapatan", "inklusi sosial", "kesetaraan",
        "kesetaraan kesempatan", "perlindungan sosial", "kebijakan fiskal", "kebijakan diskriminatif",
        "kesenjangan sosial", "pengucilan sosial", "disparitas ekonomi", "kelompok terpinggirkan",
        "distribusi pendapatan", "mobilitas sosial", "hak minoritas", "hak migran", "kesetaraan ras",
        "kebijakan afirmatif", "pemberdayaan ekonomi", "masyarakat inklusif"
    ],
    "SDG11" => [
        // Bahasa Inggris
        "sustainable cities", "urban planning", "housing", "transport", "waste management", "air quality",
        "public spaces", "urban development", "slum upgrading", "resilient buildings", "disaster risk reduction",
        "cultural heritage", "city planning", "urban infrastructure", "sustainable transport", "green spaces",
        "urban resilience", "urbanization", "metropolitan planning", "smart cities", "inclusive cities",
        "urban sustainability", "urban policies", "urban environment", "urban health", "urban biodiversity",
        "urban sprawl", "urban slums", "urban governance", "urban mobility", "urban safety", "urban agriculture",
        "green building",
        // Bahasa Indonesia
        "kota berkelanjutan", "permukiman layak", "perencanaan kota", "transportasi umum",
        "perumahan terjangkau", "urbanisasi", "pemukiman kumuh", "pembangunan perkotaan",
        "infrastruktur kota", "ruang publik", "tata ruang kota", "kepadatan penduduk",
        "pembangunan wilayah", "mobilitas perkotaan", "resiliensi kota", "pengurangan risiko bencana",
        "kota pintar", "akses transportasi", "pengelolaan kota", "lingkungan urban"
    ],
    "SDG12" => [
        // Bahasa Inggris
        "responsible consumption", "waste management", "sustainable consumption", "sustainable production",
        "resource efficiency", "natural resources", "material footprint", "ecological footprint",
        "recycling", "reuse", "lifecycle management", "sustainable procurement", "eco-labeling",
        "sustainable practices", "corporate sustainability", "circular economy", "sustainable lifestyle",
        "waste reduction", "food waste", "sustainable supply chain", "industrial ecology", "green products",
        "chemical management", "electronic waste", "plastic waste", "biodegradable", "environmental impact",
        "consumption patterns", "waste disposal", "sustainable materials", "resource management", "zero waste",
        "waste-to-energy",
        // Bahasa Indonesia
        "konsumsi berkelanjutan", "produksi berkelanjutan", "limbah", "daur ulang", "efisiensi sumber daya",
        "polusi", "jejak karbon", "rantai pasok", "ekonomi sirkular", "bahan kimia berbahaya",
        "manajemen limbah", "sampah makanan", "energi efisien", "penggunaan sumber daya",
        "produk ramah lingkungan", "pengurangan limbah", "kesadaran konsumen", "keberlanjutan industri",
        "label ramah lingkungan", "produksi hijau"
    ],
    "SDG13" => [
        // Bahasa Inggris
        "climate change", "global warming", "greenhouse gas", "carbon emission", "carbon footprint",
        "climate action", "climate policy", "climate mitigation", "climate adaptation", "emission reduction",
        "climate resilience", "carbon neutral", "carbon sequestration", "climate finance", "climate technology",
        "climate science", "climate impact", "extreme weather", "climate vulnerability", "carbon pricing",
        "low carbon", "carbon dioxide", "methane emission", "fossil fuel", "renewable energy",
        "climate justice", "climate agreement", "climate risk", "climate education", "climate model",
        "decarbonization", "climate emergency", "climate crisis",
        // Bahasa Indonesia
        "perubahan iklim", "pemanasan global", "adaptasi iklim", "mitigasi iklim", "gas rumah kaca",
        "emisi karbon", "energi bersih", "risiko iklim", "bencana iklim", "strategi iklim",
        "kebijakan iklim", "kerentanan iklim", "cuaca ekstrem", "pengurangan emisi", "netral karbon",
        "ketahanan iklim", "penghitungan karbon", "transisi hijau", "aksi iklim", "perjanjian Paris"
    ],
    "SDG14" => [
        // Bahasa Inggris
        "life below water", "marine pollution", "ocean acidification", "coastal ecosystem", "marine resources",
        "sustainable fishing", "overfishing", "marine conservation", "marine protected areas", "marine biodiversity",
        "ocean health", "marine litter", "marine habitat", "coral reef", "marine species", "ocean governance",
        "blue economy", "coastal management", "marine science", "fishing practices", "fishing communities",
        "aquatic ecosystem", "seafood", "maritime", "underwater life", "ocean sustainability", "sea level rise",
        "marine environment", "ocean policy", "fisheries management", "ocean temperature", "marine ecology",
        "marine sanctuaries",
        // Bahasa Indonesia
        "lautan", "ekosistem laut", "perikanan berkelanjutan", "pencemaran laut", "keanekaragaman hayati laut",
        "pengasaman laut", "zona pesisir", "konservasi laut", "perlindungan laut", "terumbu karang",
        "biota laut", "plastik di laut", "pengelolaan laut", "sumber daya kelautan", "ekonomi biru",
        "penangkapan ikan berlebihan", "restorasi laut", "sampah laut", "marine protected area", "ekosistem pesisir"

    ],
    "SDG15" => [
        // Bahasa Inggris
        "life on land", "biodiversity", "deforestation", "ecosystem", "forest management", "land degradation",
        "desertification", "wildlife conservation", "protected species", "protected areas", "habitat conservation",
        "land use", "soil erosion", "soil health", "invasive species", "natural habitat", "afforestation",
        "reforestation", "sustainable forestry", "biodiversity loss", "endangered species", "terrestrial ecosystem",
        "mountain ecosystem", "land restoration", "conservation efforts", "poaching", "flora", "fauna",
        "wetlands", "grasslands", "biomass", "land rights", "seed diversity", "genetic diversity",
        // Bahasa Indonesia
        "keanekaragaman hayati", "konservasi hutan", "penggundulan hutan", "restorasi lahan", "kerusakan lahan",
        "penggurunan", "keanekaragaman genetik", "ekosistem darat", "pertanian berkelanjutan", "kehutanan",
        "pengelolaan hutan", "reboisasi", "deforestasi", "flora dan fauna", "spesies langka",
        "pelestarian alam", "konservasi satwa liar", "kawasan lindung", "tanah dan air", "ekologi"
    ],
    "SDG16" => [
        // Bahasa Inggris
        "peace", "justice", "strong institutions", "violence reduction", "governance", "rule of law",
        "accountability", "transparency", "corruption", "bribery", "institutional capacity", "decision-making",
        "fundamental freedoms", "legal identity", "human rights", "conflict resolution", "peacebuilding",
        "democracy", "inclusive society", "public access", "judicial system", "responsive institutions",
        "violence against children", "trafficking", "arms flow", "organized crime", "national security",
        "public policy", "law enforcement", "civil justice", "fair trial", "political participation",
        "international cooperation",
        // Bahasa Indonesia
        "perdamaian", "keadilan", "hak asasi manusia", "hukum", "anti korupsi", "keamanan publik",
        "kekerasan", "perlindungan hukum", "akses keadilan", "transparansi", "akuntabilitas",
        "pembangunan institusi", "lembaga pemerintahan", "konflik sosial", "mediasi", "hak warga negara",
        "partisipasi publik", "penegakan hukum", "reformasi hukum", "kerja sama hukum", "stabilitas sosial"
    ],
    "SDG17" => [
        // Bahasa Inggris
        "partnerships", "global cooperation", "international support", "sustainable development",
        "technology transfer", "capacity building", "international trade", "debt sustainability",
        "policy coherence", "multi-stakeholder partnerships", "data monitoring", "statistical capacity",
        "foreign aid", "development assistance", "development finance", "global governance",
        "international relations", "policy coordination", "international agreements", "global south",
        "south-south cooperation", "north-south cooperation", "triangular cooperation", "development goals",
        "international institutions", "global partnership", "resource mobilization", "international collaboration",
        "financial resources", "knowledge sharing", "digital cooperation", "economic partnership", "trade system",
        // Bahasa Indonesia
        "kemitraan", "kerja sama internasional", "pendanaan pembangunan", "kapasitas nasional",
        "perdagangan internasional", "transfer teknologi", "dukungan pembangunan", "kebijakan global",
        "kolaborasi multi-sektor", "aliansi global", "komitmen pembangunan", "koordinasi antar negara",
        "kemitraan publik-swasta", "statistik pembangunan", "sumber daya pembangunan", "bantuan luar negeri",
        "komunikasi global", "data pembangunan", "monitoring global", "pelaporan SDG"
    ]
];

// ==============================================
// FUNGSI UTAMA
// ==============================================
function main() {
    try {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new Exception('Method not allowed', 405);
        }

        // Parameter untuk force refresh cache
        $force_refresh = isset($_GET['refresh']) && $_GET['refresh'] === 'true';
        
        if (isset($_GET['orcid'])) {
            return handleOrcidRequest($_GET['orcid'], $force_refresh);
        } elseif (isset($_GET['doi'])) {
            return handleDoiRequest($_GET['doi'], $force_refresh);
        } else {
            throw new Exception('Parameter tidak valid', 400);
        }
    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 400);
        return [
            'status' => 'error',
            'code' => $e->getCode() ?: 400,
            'message' => $e->getMessage(),
            'usage' => [
                'Peneliti' => '?orcid=0000-0002-5152-9727',
                'Artikel' => '?doi=10.1234/example',
                'Refresh Cache' => 'tambahkan &refresh=true untuk memaksa pengambilan data baru'
            ],
            'timestamp' => date('c')
        ];
    }
}

function handleOrcidRequest($orcid, $force_refresh = false) {
    $orcid = trim($orcid);
    if (!preg_match('/^0000-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid)) {
        throw new Exception('Format ORCID tidak valid', 400);
    }

    // Cek cache terlebih dahulu jika tidak force refresh
    $cache_file = getCacheFilename('orcid', $orcid);
    if (!$force_refresh && file_exists($cache_file)) {
        $cached_data = readFromCache($cache_file);
        if ($cached_data !== false) {
            $cached_data['cache_info'] = [
                'from_cache' => true,
                'cache_date' => date('c', filemtime($cache_file))
            ];
            return $cached_data;
        }
    }

    // Ambil data personal dari ORCID
    $person_data = fetchOrcidPersonData($orcid);
    
    // Ambil data karya dari ORCID
    $works_data = fetchOrcidData($orcid);
    
    // Proses data
    $result = processOrcidData($orcid, $works_data, $person_data);
    
    // Simpan ke cache
    saveToCache($cache_file, $result);
    
    // Tambahkan info cache ke hasil
    $result['cache_info'] = [
        'from_cache' => false,
        'cache_date' => date('c')
    ];
    
    return $result;
}

function handleDoiRequest($doi, $force_refresh = false) {
    $doi = trim($doi);
    if (empty($doi)) {
        throw new Exception('DOI tidak boleh kosong', 400);
    }

    // Cek cache terlebih dahulu jika tidak force refresh
    $cache_file = getCacheFilename('article', $doi);
    if (!$force_refresh && file_exists($cache_file)) {
        $cached_data = readFromCache($cache_file);
        if ($cached_data !== false) {
            $cached_data['cache_info'] = [
                'from_cache' => true,
                'cache_date' => date('c', filemtime($cache_file))
            ];
            return $cached_data;
        }
    }

    // Ambil data dari Crossref
    $data = fetchDoiData($doi);
    
    // Proses data
    $result = processDoiData($doi, $data);
    
    // Simpan ke cache
    saveToCache($cache_file, $result);
    
    // Tambahkan info cache ke hasil
    $result['cache_info'] = [
        'from_cache' => false,
        'cache_date' => date('c')
    ];
    
    return $result;
}

// ==============================================
// FUNGSI PENANGANAN DATA
// ==============================================
function fetchOrcidPersonData($orcid) {
    $url = "https://pub.orcid.org/v3.0/{$orcid}/person";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    // Tambahkan timeout untuk menghindari permintaan yang terlalu lama
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    if ($errno) {
        throw new Exception('Gagal mengambil data person ORCID: ' . $error, 500);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Data person ORCID tidak valid', 500);
    }

    return $data;
}

function fetchOrcidData($orcid) {
    $url = "https://pub.orcid.org/v3.0/{$orcid}/works";
    
    $ch = curl_init();
    // Tambahkan parameter untuk membatasi jumlah data yang diambil
    curl_setopt($ch, CURLOPT_URL, $url . "?pageSize=50"); // Batasi 50 karya terbaru
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    // Implementasi timeout untuk menghindari permintaan yang terlalu lama
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    if ($errno) {
        throw new Exception('Gagal mengambil data ORCID: ' . $error, 500);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Data ORCID tidak valid', 500);
    }

    return $data;
}

function fetchDoiData($doi) {
    $url = "https://api.crossref.org/works/" . urlencode($doi);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Tambahkan User-Agent yang sesuai untuk Crossref API
    curl_setopt($ch, CURLOPT_USERAGENT, 'SDG-Classifier/1.0 (your@email.com)');
    
    // Tambahkan timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    if ($errno) {
        throw new Exception('Gagal mengambil data Crossref: ' . $error, 500);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Data Crossref tidak valid', 500);
    }

    return $data;
}

// Fungsi untuk mencoba mendapatkan abstrak dari sumber alternatif jika Crossref tidak menyediakannya
function fetchAbstractFromAlternativeSource($doi) {
    // Coba dari Semantic Scholar API
    $url = "https://api.semanticscholar.org/v1/paper/" . urlencode($doi);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$response) {
        return "";
    }
    
    $data = json_decode($response, true);
    if (isset($data['abstract']) && !empty($data['abstract'])) {
        return $data['abstract'];
    }
    
    return "";
}

function processOrcidData($orcid, $works_data, $person_data) {
    global $SDG_KEYWORDS;

    // Ekstrak informasi personal
    $name = extractOrcidName($person_data);
    $institutions = extractOrcidInstitutions($person_data);

    $result = [
        'orcid' => $orcid,
        'personal_info' => [
            'name' => $name,
            'institutions' => $institutions
        ],
        'works' => [],
        'sdg_counts' => array_fill_keys(array_keys($SDG_KEYWORDS), 0),
        'sdg_scores' => array_fill_keys(array_keys($SDG_KEYWORDS), 0),
        'sdg_similarity_scores' => array_fill_keys(array_keys($SDG_KEYWORDS), 0),
        'status' => 'success',
        'timestamp' => date('c')
    ];

    if (isset($works_data['group']) && is_array($works_data['group'])) {
        foreach ($works_data['group'] as $work) {
            // Ambil title dan detail dari work-summary[0]
            $summary = isset($work['work-summary'][0]) ? $work['work-summary'][0] : null;
            if (!$summary) continue;
            
            $title = isset($summary['title']['title']['value']) ? $summary['title']['title']['value'] : '';
            $doi = extractDoi($summary);
            
            // Ambil abstrak jika memungkinkan menggunakan DOI
            $abstract = '';
            if ($doi) {
                try {
                    $doi_data = fetchDoiData($doi);
                    if (isset($doi_data['message']['abstract'])) {
                        $abstract = $doi_data['message']['abstract'];
                        // Hapus tag HTML jika ada
                        $abstract = strip_tags($abstract);
                    }
                    // Coba alternatif lokasi abstrak
                    else if (isset($doi_data['message']['JournalAbs:abstract'])) {
                        $abstract = $doi_data['message']['JournalAbs:abstract'];
                        $abstract = strip_tags($abstract);
                    }
                    
                    // Jika masih kosong, coba dari sumber alternatif
                    if (empty($abstract)) {
                        $abstract = fetchAbstractFromAlternativeSource($doi);
                    }
                } catch (Exception $e) {
                    // Jika gagal mengambil abstrak, biarkan kosong
                }
            }
            
            // Preprocessing teks sebelum analisis
            $fullText = preprocessText($title . ' ' . $abstract);
            
            // Gunakan metode scoring untuk klasifikasi SDG dengan caching
            $cacheKey = md5($fullText) . '_score';
            $cachedScores = getCachedAnalysis($cacheKey);
            
            if ($cachedScores === false) {
                $sdgScores = scoreSDGs($fullText);
                saveCachedAnalysis($cacheKey, $sdgScores);
            } else {
                $sdgScores = $cachedScores;
            }
            
            // Gunakan cosine similarity untuk analisis tambahan dengan caching
            $cacheKey = md5($fullText) . '_similarity';
            $cachedSimilarity = getCachedAnalysis($cacheKey);
            
            if ($cachedSimilarity === false) {
                $sdgSimilarity = calculateSDGSimilarity($fullText);
                saveCachedAnalysis($cacheKey, $sdgSimilarity);
            } else {
                $sdgSimilarity = $cachedSimilarity;
            }
            
            // Kombinasikan hasil scoring dan similarity dengan pembobotan yang optimal
            $combinedScores = combineAllMethods($sdgScores, $sdgSimilarity, 0.6, 0.4);
            
            // Gunakan skor gabungan untuk menentukan SDGs yang relevan
            $sdgs = array_keys($combinedScores);
            
            $result['works'][] = [
                'title' => $title,
                'doi' => $doi,
                'abstract' => $abstract ? $abstract : '',
                'sdgs' => $sdgs,
                'sdg_confidence' => $combinedScores,
                'keyword_matches' => $sdgScores,
                'similarity_scores' => $sdgSimilarity
            ];

            // Update skor total
            foreach ($combinedScores as $sdg => $score) {
                $result['sdg_counts'][$sdg]++;
                $result['sdg_scores'][$sdg] += isset($sdgScores[$sdg]) ? $sdgScores[$sdg] : 0;
                $result['sdg_similarity_scores'][$sdg] += isset($sdgSimilarity[$sdg]) ? $sdgSimilarity[$sdg] : 0;
            }
        }
    }

    // Filter hanya SDG yang muncul
    $result['sdg_counts'] = array_filter($result['sdg_counts'], function($count) {
        return $count > 0;
    });
    
    $result['sdg_scores'] = array_filter($result['sdg_scores'], function($score) {
        return $score > 0;
    });
    
    $result['sdg_similarity_scores'] = array_filter($result['sdg_similarity_scores'], function($score) {
        return $score > 0;
    });

    // Urutkan berdasarkan jumlah kemunculan
    arsort($result['sdg_counts']);
    // Urutkan berdasarkan total skor
    arsort($result['sdg_scores']);
    // Urutkan berdasarkan similarity score
    arsort($result['sdg_similarity_scores']);
    
    $result['top_sdgs_by_count'] = array_keys($result['sdg_counts']);
    $result['top_sdgs_by_score'] = array_keys($result['sdg_scores']);
    $result['top_sdgs_by_similarity'] = array_keys($result['sdg_similarity_scores']);
    
    // Buat skor gabungan untuk semua karya
    $combinedTotalScores = [];
    foreach (array_keys($SDG_KEYWORDS) as $sdg) {
        if (isset($result['sdg_scores'][$sdg]) && isset($result['sdg_similarity_scores'][$sdg])) {
            // Normalisasi skor
            $keywordScore = $result['sdg_scores'][$sdg] / max(array_sum($result['sdg_scores']), 1);
            $similarityScore = $result['sdg_similarity_scores'][$sdg] / max(array_sum($result['sdg_similarity_scores']), 1);
            
            // Skor gabungan dengan bobot
            $combinedTotalScores[$sdg] = ($keywordScore * 0.6) + ($similarityScore * 0.4);
        }
    }
    
    // Urutkan skor gabungan
    if (!empty($combinedTotalScores)) {
        arsort($combinedTotalScores);
        $result['top_sdgs_combined'] = array_keys($combinedTotalScores);
        $result['combined_scores'] = $combinedTotalScores;
    }

    return $result;
}

function processDoiData($doi, $data) {
    global $SDG_KEYWORDS;
    
    $title = isset($data['message']['title'][0]) ? $data['message']['title'][0] : '';
    
    // Ekstraksi abstrak dengan lebih hati-hati
    $abstract = '';
    if (isset($data['message']['abstract'])) {
        $abstract = $data['message']['abstract'];
        // Hapus tag HTML jika ada
        $abstract = strip_tags($abstract);
    }
    // Coba alternatif lokasi abstrak
    else if (isset($data['message']['JournalAbs:abstract'])) {
        $abstract = $data['message']['JournalAbs:abstract'];
        $abstract = strip_tags($abstract);
    }
    
    // Jika abstrak masih tidak ditemukan, coba gunakan pendekatan API alternatif
    if (empty($abstract)) {
        try {
            $abstract = fetchAbstractFromAlternativeSource($doi);
        } catch (Exception $e) {
            // Jika alternatif gagal, lanjutkan dengan abstrak kosong
        }
    }
    
    // Preprocessing teks
    $fullText = preprocessText($title . ' ' . $abstract);
    
    // Informasi tambahan tentang artikel
    $authors = [];
    if (isset($data['message']['author']) && is_array($data['message']['author'])) {
        foreach ($data['message']['author'] as $author) {
            $authorName = '';
            if (isset($author['given'])) {
                $authorName .= $author['given'] . ' ';
            }
            if (isset($author['family'])) {
                $authorName .= $author['family'];
            }
            $authors[] = trim($authorName);
        }
    }
    
    $journal = isset($data['message']['container-title'][0]) ? $data['message']['container-title'][0] : '';
    $published = isset($data['message']['published']['date-parts'][0]) ? 
                 implode('-', $data['message']['published']['date-parts'][0]) : '';
    
    // Gunakan metode scoring untuk klasifikasi SDG dengan caching
    $cacheKey = md5($fullText) . '_score';
    $cachedScores = getCachedAnalysis($cacheKey);
    
    if ($cachedScores === false) {
        $sdgScores = scoreSDGs($fullText);
        saveCachedAnalysis($cacheKey, $sdgScores);
    } else {
        $sdgScores = $cachedScores;
    }
    
    // Gunakan cosine similarity untuk analisis tambahan dengan caching
    $cacheKey = md5($fullText) . '_similarity';
    $cachedSimilarity = getCachedAnalysis($cacheKey);
    
    if ($cachedSimilarity === false) {
        $sdgSimilarity = calculateSDGSimilarity($fullText);
        saveCachedAnalysis($cacheKey, $sdgSimilarity);
    } else {
        $sdgSimilarity = $cachedSimilarity;
    }
    
    // Kombinasikan hasil scoring dan similarity dengan pembobotan
    $combinedScores = combineAllMethods($sdgScores, $sdgSimilarity, 0.6, 0.4);
    
    // Gunakan skor gabungan untuk menentukan SDGs yang relevan
    $sdgs = array_keys($combinedScores);

    return [
        'doi' => $doi,
        'title' => $title,
        'abstract' => $abstract,
        'authors' => $authors,
        'journal' => $journal,
        'published_date' => $published,
        'sdgs' => $sdgs,
        'sdg_confidence' => $combinedScores,
        'keyword_matches' => $sdgScores,
        'similarity_scores' => $sdgSimilarity,
        'status' => 'success',
        'timestamp' => date('c')
    ];
}

// ==============================================
// FUNGSI BANTU
// ==============================================
function extractOrcidName($person_data) {
    $name = '';
    
    if (isset($person_data['name'])) {
        if (isset($person_data['name']['given-names']['value'])) {
            $name .= $person_data['name']['given-names']['value'] . ' ';
        }
        
        if (isset($person_data['name']['family-name']['value'])) {
            $name .= $person_data['name']['family-name']['value'];
        }
    }
    
    return trim($name);
}

function extractOrcidInstitutions($person_data) {
    $institutions = array();
    
    if (isset($person_data['employments']['employment-summary']) && 
        is_array($person_data['employments']['employment-summary'])) {
        
        foreach ($person_data['employments']['employment-summary'] as $employment) {
            if (isset($employment['organization']['name'])) {
                $institutions[] = $employment['organization']['name'];
            }
        }
    }
    
    if (isset($person_data['educations']['education-summary']) && 
        is_array($person_data['educations']['education-summary'])) {
        
        foreach ($person_data['educations']['education-summary'] as $education) {
            if (isset($education['organization']['name'])) {
                $institutions[] = $education['organization']['name'];
            }
        }
    }
    
    return array_unique($institutions);
}

function extractDoi($summary) {
    if (!isset($summary['external-ids']['external-id']) || !is_array($summary['external-ids']['external-id'])) {
        return null;
    }

    foreach ($summary['external-ids']['external-id'] as $id) {
        if (isset($id['external-id-type']) && 
            strtolower($id['external-id-type']) === 'doi' &&
            isset($id['external-id-value']) && 
            !empty($id['external-id-value'])) {
            return $id['external-id-value'];
        }
    }

    return null;
}

/**
 * Praproses teks untuk analisis yang lebih baik
 * Menghilangkan karakter khusus dan stopwords
 */
function preprocessText($text) {
    // Konversi ke lowercase
    $text = strtolower($text);
    
    // Hapus tag HTML jika ada
    $text = strip_tags($text);
    
    // Hapus karakter khusus
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    
    // Hapus stopwords yang umum dalam bahasa Inggris dan Indonesia
    static $stopwords = array(
        // Inggris
        'the', 'and', 'in', 'of', 'to', 'a', 'is', 'that', 'for', 'on', 'with', 
        'as', 'by', 'at', 'from', 'were', 'was', 'this', 'these', 'those', 'are',
        'been', 'have', 'has', 'had', 'be', 'an', 'or', 'not', 'but', 'if', 'which',
        // Indonesia
        'yang', 'dan', 'di', 'ke', 'pada', 'dengan', 'dari', 'untuk', 'adalah', 
        'ini', 'itu', 'atau', 'serta', 'juga', 'ada', 'akan', 'oleh', 'dalam'
    );
    
    // Split text into words and filter stopwords
    $words = preg_split('/\s+/', $text);
    $filteredWords = array();
    
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) > 2 && !in_array($word, $stopwords)) {
            $filteredWords[] = $word;
        }
    }
    
    return implode(' ', $filteredWords);
}

/**
 * Klasifikasi SDG berdasarkan kemunculan keyword
 * Sudah dioptimasi untuk performa lebih baik
 */
function classifySDGs($text) {
    global $SDG_KEYWORDS;
    $text = strtolower($text);
    $matched = array();

    foreach ($SDG_KEYWORDS as $sdg => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($text, strtolower($keyword)) !== false) {
                $matched[] = $sdg;
                break;
            }
        }
    }

    return array_values(array_unique($matched));
}

/**
 * Metode scoring untuk SDG berdasarkan frekuensi kemunculan keyword
 * Dengan optimasi performa
 */
function scoreSDGs($text) {
    global $SDG_KEYWORDS;
    $text = strtolower($text);
    $scores = array();
    
    // Precompute word frequency untuk analisis yang lebih cepat
    $wordFreq = array_count_values(str_word_count($text, 1));
    
    foreach ($SDG_KEYWORDS as $sdg => $keywords) {
        $count = 0;
        foreach ($keywords as $keyword) {
            // Untuk kata kunci multi-kata, gunakan substr_count
            if (strpos($keyword, ' ') !== false) {
                $count += substr_count($text, strtolower($keyword));
            } 
            // Untuk kata kunci kata tunggal, gunakan hasil yang sudah dihitung
            else if (isset($wordFreq[$keyword])) {
                $count += $wordFreq[$keyword];
            }
        }
        
        if ($count > 0) {
            $scores[$sdg] = $count;
        }
    }
    
    // Hitung total skor semua SDG
    $total = array_sum($scores);
    
    // Normalisasi menjadi confidence (0-1)
    if ($total > 0) {
        foreach ($scores as $sdg => $value) {
            $scores[$sdg] = round($value / $total, 3);  // confidence value
        }
    }
    
    // Urutkan dari confidence tertinggi
    arsort($scores);
    
    return $scores;
}

/**
 * Fungsi untuk menghitung cosine similarity antara teks dan SDG
 * Dengan optimasi menggunakan caching vektor SDG
 */
function calculateSDGSimilarity($text) {
    global $SDG_KEYWORDS;
    $text = strtolower($text);
    $similarity_scores = array();
    
    // Static cache untuk vektor SDG agar tidak dihitung ulang
    static $sdgVectors = array();
    
    // Buat representasi vektor untuk teks input
    $text_vector = createTextVector($text);
    
    // Buat representasi vektor untuk setiap SDG
    foreach ($SDG_KEYWORDS as $sdg => $keywords) {
        // Cek apakah vektor SDG sudah ada di cache
        if (!isset($sdgVectors[$sdg])) {
            $sdg_text = implode(' ', $keywords);
            $sdgVectors[$sdg] = createTextVector($sdg_text);
        }
        
        // Hitung cosine similarity
        $similarity = calculateCosineSimilarity($text_vector, $sdgVectors[$sdg]);
        
        if ($similarity > 0) {
            $similarity_scores[$sdg] = $similarity;
        }
    }
    
    // Urutkan dari similarity tertinggi
    arsort($similarity_scores);
    
    return $similarity_scores;
}

/**
 * Fungsi untuk membuat vektor kata dari teks
 * Optimasi untuk performa
 */
function createTextVector($text) {
    $words = preg_split('/\s+/', $text);
    $vector = array();
    
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) > 2) { // Abaikan kata yang terlalu pendek
            if (!isset($vector[$word])) {
                $vector[$word] = 0;
            }
            $vector[$word]++;
        }
    }
    
    return $vector;
}

/**
 * Fungsi untuk menghitung cosine similarity antara dua vektor
 */
function calculateCosineSimilarity($vector1, $vector2) {
    // Gabungkan semua dimensi
    $dimensions = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));
    
    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;
    
    foreach ($dimensions as $dim) {
        $v1 = isset($vector1[$dim]) ? $vector1[$dim] : 0;
        $v2 = isset($vector2[$dim]) ? $vector2[$dim] : 0;
        
        $dotProduct += $v1 * $v2;
        $magnitude1 += $v1 * $v1;
        $magnitude2 += $v2 * $v2;
    }
    
    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);
    
    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0;
    }
    
    return round($dotProduct / ($magnitude1 * $magnitude2), 3);
}

/**
 * Fungsi untuk menggabungkan semua metode analisis SDG menjadi satu nilai akhir
 * @param array $keywordScores - Skor berdasarkan kata kunci
 * @param array $similarityScores - Skor berdasarkan cosine similarity
 * @param float $keywordWeight - Bobot untuk skor kata kunci (default: 0.5)
 * @param float $similarityWeight - Bobot untuk skor similarity (default: 0.5)
 * @return array - Array skor gabungan dengan nilai normalisasi
 */
function combineAllMethods($keywordScores, $similarityScores, $keywordWeight = 0.5, $similarityWeight = 0.5) {
    // Pastikan bobot berjumlah 1.0
    $totalWeight = $keywordWeight + $similarityWeight;
    $keywordWeight = $keywordWeight / $totalWeight;
    $similarityWeight = $similarityWeight / $totalWeight;
    
    // Gabungkan semua SDG yang terdeteksi dari semua metode
    $allSDGs = array_unique(array_merge(array_keys($keywordScores), array_keys($similarityScores)));
    
    $combinedScores = array();
    
    // Hitung skor berdasarkan pembobotan
    foreach ($allSDGs as $sdg) {
        $kScore = isset($keywordScores[$sdg]) ? $keywordScores[$sdg] : 0;
        $sScore = isset($similarityScores[$sdg]) ? $similarityScores[$sdg] : 0;
        
        // Formula kombinasi dengan pembobotan
        $finalScore = ($kScore * $keywordWeight) + ($sScore * $similarityWeight);
        
        if ($finalScore > 0) {
            $combinedScores[$sdg] = round($finalScore, 3);
        }
    }
    
    // Urutkan dari tertinggi ke terendah
    arsort($combinedScores);
    
    // Normalisasi skor agar total = 1.0
    $total = array_sum($combinedScores);
    if ($total > 0) {
        foreach ($combinedScores as $sdg => $score) {
            $combinedScores[$sdg] = round($score / $total, 3);
        }
    }
    
    return $combinedScores;
}

/**
 * Fungsi untuk menyimpan data ke cache
 */
function saveToCache($filename, $data) {
    global $CACHE_DIR;
    
    if (!is_dir($CACHE_DIR)) {
        mkdir($CACHE_DIR, 0755, true);
    }
    
    $json_data = json_encode($data);
    $compressed_data = gzencode($json_data, 9); // Level kompresi 9 (maksimum)
    
    file_put_contents($filename, $compressed_data);
}

/**
 * Fungsi untuk membaca data dari cache
 */
function readFromCache($filename) {
    if (!file_exists($filename)) {
        return false;
    }
    
    $compressed_data = file_get_contents($filename);
    if ($compressed_data === false) {
        return false;
    }
    
    $json_data = gzdecode($compressed_data);
    if ($json_data === false) {
        return false;
    }
    
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }
    
    return $data;
}

/**
 * Fungsi untuk mendapatkan nama file cache
 */
function getCacheFilename($type, $id) {
    global $CACHE_DIR;
    
    $unique_code = substr(md5($id), 0, 8);
    
    if ($type === 'orcid') {
        return $CACHE_DIR . '/orcid_' . $unique_code . '_' . $id . '.json.gz';
    } else if ($type === 'article') {
        // Bersihkan DOI untuk penggunaan di nama file
        $safe_doi = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $id);
        return $CACHE_DIR . '/article_' . $safe_doi . '_' . $unique_code . '.json.gz';
    }
    
    return false;
}

/**
 * Cache untuk hasil analisis teks - menyimpan hasil analisis ke memori
 */
function getCachedAnalysis($cacheKey) {
    static $memoryCache = array();
    
    if (isset($memoryCache[$cacheKey])) {
        return $memoryCache[$cacheKey];
    }
    
    $cacheFile = __DIR__ . '/cache/text_' . $cacheKey . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 604800)) { // 7 hari
        $data = unserialize(file_get_contents($cacheFile));
        $memoryCache[$cacheKey] = $data;
        return $data;
    }
    
    return false;
}

/**
 * Menyimpan hasil analisis teks ke cache
 */
function saveCachedAnalysis($cacheKey, $data) {
    static $memoryCache = array();
    
    // Simpan ke memori
    $memoryCache[$cacheKey] = $data;
    
    // Simpan ke file
    $cacheDir = __DIR__ . '/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/text_' . $cacheKey . '.cache';
    file_put_contents($cacheFile, serialize($data));
}

// ==============================================
// EKSEKUSI
// ==============================================
try {
    $result = main();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'message' => 'Terjadi kesalahan internal: ' . $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}