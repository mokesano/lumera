<?php
/**
 * SDG Classification API
 * 
 * API untuk menganalisis karya ilmiah dan peneliti berdasarkan Sustainable Development Goals (SDGs).
 * Mendukung analisis berdasarkan ORCID peneliti dan DOI artikel.
 * 
 * Fitur Utama:
 * - Klasifikasi SDG berdasarkan analisis kata kunci, cosine similarity, dan analisis semantik mendalam
 * - Mendukung 17 kategori SDG dengan pengayaan kata kunci (30+ kata kunci per SDG)
 * - Dukungan kata kunci dalam bahasa Inggris dan Indonesia
 * - Sistem caching untuk meningkatkan performa (file JSON terkompresi + memory caching)
 * - Ekstraksi informasi dasar peneliti dari ORCID (nama, institusi)
 * - Ekstraksi informasi artikel lengkap termasuk abstrak dari DOI
 * - Analisis kontribusi substantif dan deteksi hubungan kausal
 * - Deteksi manipulasi dan filtering hasil berdasarkan kualitas
 * 
 * Penggunaan:
 * - Analisis Peneliti: ?orcid=0000-0002-5152-9727
 * - Analisis Artikel: ?doi=10.1234/example
 * - Refresh Cache: &refresh=true
 * 
 * @author Rochmady
 * @version 2.1.7
 * @license MIT
 * last update 2025-05-29
 */

header('Content-Type: application/json; charset=utf-8');

// ==============================================
// KONFIGURASI UMUM
// ==============================================
$CACHE_DIR = __DIR__ . '/cache';
if (!is_dir($CACHE_DIR)) {
    mkdir($CACHE_DIR, 0755, true);
}

// Threshold untuk analisis SDG
$CONFIG = [
    // Skor minimal untuk ditampilkan dalam analisis detail
    'MIN_SCORE_THRESHOLD' => 0.20,
    // Skor minimal untuk diklaim sebagai kontribusi SDG
    'CONFIDENCE_THRESHOLD' => 0.30,
    // Skor untuk dianggap kontribusi dengan kepercayaan tinggi
    'HIGH_CONFIDENCE_THRESHOLD' => 0.60,
    // Jumlah maksimum SDG per karya
    'MAX_SDGS_PER_WORK' => 7,
    // Bobot skor kata kunci dalam skor gabungan
    'KEYWORD_WEIGHT' => 0.40,
    // Bobot cosine similarity dalam skor gabungan
    'SIMILARITY_WEIGHT' => 0.35,
    // Bobot analisis substantif dalam skor gabungan
    'SUBSTANTIVE_WEIGHT' => 0.15,
    // Bobot analisis hubungan kausal dalam skor gabungan
    'CAUSAL_WEIGHT' => 0.10,
    // Time-to-live cache: 7 hari dalam detik
    'CACHE_TTL' => 604800
];

// Definisi CAUSAL_PATTERNS di bagian awal file (setelah $SDG_KEYWORDS)
$CAUSAL_PATTERNS = [
    // Bahasa Inggris - pola kausal yang kuat
    'contributes to', 'supports', 'advances', 'helps achieve', 'improves', 
    'enhances', 'addresses', 'tackles', 'solves', 'promotes', 'enables', 
    'facilitates', 'aims to', 'designed to', 'intended to', 'mitigates',
    // Bahasa Indonesia - pola kausal yang kuat
    'berkontribusi pada', 'mendukung', 'memajukan', 'membantu mencapai',
    'meningkatkan', 'mengatasi', 'memecahkan', 'mempromosikan', 'memungkinkan',
    'bertujuan untuk', 'dirancang untuk'
];

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

// Pola hubungan kausal antar SDG dan penelitian
$CAUSAL_PATTERNS = [
    // Bahasa Inggris
    'contributes to', 'supports', 'advances', 'helps achieve', 'improves', 
    'enhances', 'addresses', 'tackles', 'solves', 'promotes', 'enables', 
    'facilitates', 'aims to', 'designed to', 'intended to', 'mitigates',
    // Bahasa Indonesia
    'berkontribusi pada', 'mendukung', 'memajukan', 'membantu mencapai',
    'meningkatkan', 'mengatasi', 'memecahkan', 'mempromosikan', 'memungkinkan',
    'bertujuan untuk', 'dirancang untuk'
];

// Indikator substantif untuk tiap SDG
$SUBSTANTIVE_INDICATORS = [
    'solution_words' => [
        'solution', 'strategy', 'approach', 'intervention', 'policy', 'program',
        'framework', 'model', 'method', 'technique', 'tool', 'solusi', 'strategi',
        'pendekatan', 'intervensi', 'kebijakan', 'program', 'model', 'metode'
    ],
    'impact_words' => [
        'impact', 'effect', 'outcome', 'result', 'evaluation', 'assessment',
        'improvement', 'increase', 'decrease', 'reduction', 'enhancement',
        'dampak', 'efek', 'hasil', 'evaluasi', 'penilaian', 'peningkatan',
        'penurunan', 'pengurangan'
    ],
    'methodology_words' => [
        'survey', 'interview', 'analysis', 'study', 'research', 'method',
        'experiment', 'measurement', 'observation', 'data', 'statistics',
        'survei', 'wawancara', 'analisis', 'studi', 'penelitian', 'metode',
        'eksperimen', 'pengukuran', 'observasi', 'data', 'statistik'
    ]
];

// Cache memory untuk analisis teks
$MEMORY_CACHE = [];

// ==============================================
// FUNGSI UTAMA
// ==============================================
/**
* Fungsi utama untuk memproses permintaan API
* Menentukan jenis request dan mengarahkan ke handler yang sesuai
* @return array Respons API dalam format array
*/
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

/**
* Memproses permintaan ORCID dengan penambahan pengecekan khusus untuk personal_info
* @param string $orcid ID ORCID peneliti
* @param bool $force_refresh Flag untuk memaksa refresh cache
* @return array Data peneliti dengan analisis SDG
*/
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

/**
* Menangani permintaan data artikel berdasarkan DOI
* @param string $doi DOI artikel
* @param bool $force_refresh Flag untuk memaksa refresh cache
* @return array Data artikel dengan analisis SDG
*/
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
/**
* Mengambil data personal peneliti dari API ORCID
* @param string $orcid ID ORCID peneliti
* @return array Data personal dari ORCID API
*/
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

/**
* Mengambil data karya peneliti dari API ORCID
* @param string $orcid ID ORCID peneliti
* @return array Data karya dari ORCID API
*/
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

/**
* Mengambil data artikel dari API Crossref
* @param string $doi DOI artikel
* @return array Data artikel dari Crossref API
*/
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

/**
* Fungsi untuk mendapatkan abstrak dari Semantic Scholar API
* @param string $doi DOI artikel
* @return array Data artikel dari Semantic Scholar API
*/
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

/**
* Memproses data ORCID untuk menghasilkan profil peneliti dengan analisis SDG
* @param string $orcid ID ORCID peneliti
* @param array $works_data Data karya dari ORCID
* @param array $person_data Data personal dari ORCID
* @return array Profil peneliti lengkap dengan analisis SDG
*/
function processOrcidData($orcid, $works_data, $person_data) {
    global $SDG_KEYWORDS, $CONFIG;

    // Ekstrak informasi personal
    $name = extractOrcidName($person_data);
    $institutions = extractOrcidInstitutions($person_data);

    // Inisialisasi array untuk researcher_sdg_summary
    $researcher_sdg_summary = [];
    $processed_works = [];

    // Inisialisasi result dengan struktur yang benar - tanpa analisis di level atas
    $result = [
        'orcid' => $orcid,
        'personal_info' => [
            'name' => $name,
            'institutions' => $institutions
        ],
        'data_source' => 'ORCID API',
        'works' => [],
        'researcher_sdg_summary' => [],
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
            
            // Jika tidak ada judul, lewati karya ini
            if (empty($title)) continue;
            
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
                    error_log("Error fetching abstract for DOI $doi: " . $e->getMessage());
                }
            }
            
            // Preprocessing teks
            $fullText = $title . ' ' . $abstract;
            $preprocessedText = preprocessText($fullText);
            
            // Analisis komprehensif SDG
            $sdgAnalysis = [];
            $filteredSdgs = [];
            $sdgConfidence = [];
            
            foreach ($SDG_KEYWORDS as $sdg => $keywords) {
                $matched = false;
                
                // Cek kata kunci SDG dalam judul atau abstrak
                foreach ($keywords as $keyword) {
                    if (stripos($preprocessedText, $keyword) !== false) {
                        $matched = true;
                        break;
                    }
                }
                
                if ($matched) {
                    $analysis = evaluateSDGContribution($preprocessedText, $sdg);
                    
                    // Hanya simpan SDG dengan skor minimal
                    if ($analysis['score'] > $CONFIG['MIN_SCORE_THRESHOLD']) {
                        $sdgAnalysis[$sdg] = $analysis;
                        
                        // Tambahkan ke filtered sdgs jika memenuhi threshold
                        if ($analysis['score'] >= $CONFIG['CONFIDENCE_THRESHOLD']) {
                            $filteredSdgs[] = $sdg;
                            $sdgConfidence[$sdg] = $analysis['score'];
                        }
                    }
                }
            }
            
            // Urutkan sdgs berdasarkan confidence
            if (!empty($sdgConfidence)) {
                arsort($sdgConfidence);
                
                // Batasi jumlah maksimum SDG per karya
                if (count($filteredSdgs) > $CONFIG['MAX_SDGS_PER_WORK']) {
                    $filteredSdgsTemp = [];
                    $sdgConfidenceTemp = [];
                    $counter = 0;
                    
                    foreach ($sdgConfidence as $sdg => $confidence) {
                        if ($counter >= $CONFIG['MAX_SDGS_PER_WORK']) break;
                        $filteredSdgsTemp[] = $sdg;
                        $sdgConfidenceTemp[$sdg] = $confidence;
                        $counter++;
                    }
                    
                    $filteredSdgs = $filteredSdgsTemp;
                    $sdgConfidence = $sdgConfidenceTemp;
                }
            }
            
            // Deteksi manipulasi
            $manipulationFlags = detectPotentialManipulation($preprocessedText, $sdgAnalysis);
            
            // Tambahkan ke processed_works
            $processed_works[] = [
                'title' => $title,
                'doi' => $doi,
                'abstract' => $abstract ? $abstract : '',
                'sdgs' => $filteredSdgs,
                'sdg_confidence' => $sdgConfidence,
                'detailed_analysis' => $sdgAnalysis,
                'potential_flags' => $manipulationFlags
            ];
            
            // Update statistik agregat
            foreach ($sdgAnalysis as $sdg => $analysis) {
                if ($analysis['score'] >= $CONFIG['CONFIDENCE_THRESHOLD']) {
                    if (!isset($researcher_sdg_summary[$sdg])) {
                        $researcher_sdg_summary[$sdg] = [
                            'work_count' => 0,
                            'average_confidence' => 0,
                            'high_confidence_works' => 0,
                            'example_works' => []
                        ];
                    }
                    
                    $researcher_sdg_summary[$sdg]['work_count']++;
                    $researcher_sdg_summary[$sdg]['average_confidence'] += $analysis['score'];
                    
                    // Hitung high confidence works
                    if ($analysis['score'] >= $CONFIG['HIGH_CONFIDENCE_THRESHOLD']) {
                        $researcher_sdg_summary[$sdg]['high_confidence_works']++;
                    }
                    
                    // Terlepas dari high confidence, tambahkan beberapa contoh karya
                    if (count($researcher_sdg_summary[$sdg]['example_works']) < 3) {
                        $researcher_sdg_summary[$sdg]['example_works'][] = [
                            'title' => $title,
                            'doi' => $doi,
                            'confidence' => $analysis['score']
                        ];
                    }
                }
            }
        }
    }

    // Finalisasi summary
    foreach ($researcher_sdg_summary as $sdg => &$summary) {
        if ($summary['work_count'] > 0) {
            $summary['average_confidence'] = round($summary['average_confidence'] / $summary['work_count'], 3);
        }
    }

    // Urutkan summary berdasarkan jumlah karya dengan confidence tinggi
    if (!empty($researcher_sdg_summary)) {
        uasort($researcher_sdg_summary, function($a, $b) {
            return $b['high_confidence_works'] - $a['high_confidence_works'];
        });
    }
    
    // Masukkan works dan summary ke dalam result
    $result['works'] = $processed_works;
    $result['researcher_sdg_summary'] = $researcher_sdg_summary;
    
    // Tambahkan analisis kontribusi peneliti untuk setiap SDG
    $researcher_sdg_contributions = [];
    
    foreach ($researcher_sdg_summary as $sdg => $summary) {
        // Menentukan apakah peneliti memiliki kontribusi signifikan
        $isSignificantContributor = false;
        
        // KRITERIA 1: Minimal memiliki 3 karya terkait SDG ini
        $hasEnoughWorks = $summary['work_count'] >= 3;
        
        // KRITERIA 2: Memiliki minimal 1 karya high confidence
        $hasHighConfidenceWork = $summary['high_confidence_works'] > 0;
        
        // KRITERIA 3: Rata-rata confidence di atas 0.35
        $hasGoodAvgConfidence = $summary['average_confidence'] > 0.35;
        
        // Gabungkan kriteria untuk kontributor signifikan
        if ($hasEnoughWorks && ($hasHighConfidenceWork || $hasGoodAvgConfidence)) {
            $isSignificantContributor = true;
        }
        
        $researcher_sdg_contributions[$sdg] = [
            'is_significant_contributor' => $isSignificantContributor,
            'contribution_level' => $isSignificantContributor ? 
                ($hasHighConfidenceWork ? 'High' : 'Middle') : 'Low',
            'criteria_met' => [
                'enough_works' => $hasEnoughWorks,
                'has_high_confidence_work' => $hasHighConfidenceWork,
                'good_average_confidence' => $hasGoodAvgConfidence
            ]
        ];
    }
    
    // Tambahkan ke hasil
    $result['researcher_sdg_contributions'] = $researcher_sdg_contributions;
    
    return $result;
}

/**
* Memproses data DOI untuk menghasilkan analisis SDG sebuah artikel
* @param string $doi DOI artikel
* @param array $data Data artikel dari Crossref
* @return array Analisis SDG artikel
*/
function processDoiData($doi, $data) {
   global $SDG_KEYWORDS, $CONFIG;
   
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
   $fullText = $title . ' ' . $abstract;
   $preprocessedText = preprocessText($fullText);
   
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
   
   // Analisis komprehensif SDG
   $sdgAnalysis = [];
   $allSdgs = array_keys($SDG_KEYWORDS);
   
   foreach ($allSdgs as $sdg) {
       $result = evaluateSDGContribution($preprocessedText, $sdg);
       
       // Hanya simpan SDG dengan skor minimal
       if ($result['score'] > $CONFIG['MIN_SCORE_THRESHOLD']) {
           $sdgAnalysis[$sdg] = $result;
       }
   }
   
   // Deteksi manipulasi
   $manipulationFlags = detectPotentialManipulation($preprocessedText, $sdgAnalysis);
   
   // Filter SDG
   $filteredSdgs = [];
   $sdgConfidence = [];
   
   foreach ($sdgAnalysis as $sdg => $analysis) {
       if ($analysis['score'] < $CONFIG['CONFIDENCE_THRESHOLD'] || 
           stripos(implode(' ', $manipulationFlags), $sdg) !== false) {
           continue;
       }
       
       $filteredSdgs[] = $sdg;
       $sdgConfidence[$sdg] = $analysis['score'];
   }
   
   // Urutkan berdasarkan score
   arsort($sdgConfidence);
   
   // Batasi jumlah maksimum SDG
   if (count($filteredSdgs) > $CONFIG['MAX_SDGS_PER_WORK']) {
       $tempArray = array_slice($sdgConfidence, 0, $CONFIG['MAX_SDGS_PER_WORK'], true);
       $filteredSdgs = array_keys($tempArray);
       $sdgConfidence = $tempArray;
   }

   return [
       'data_source' => 'DOI API',
       'doi' => $doi,
       'title' => $title,
       'abstract' => $abstract,
       'authors' => $authors,
       'journal' => $journal,
       'published_date' => $published,
       'sdgs' => $filteredSdgs,
       'sdg_confidence' => $sdgConfidence,
       'detailed_analysis' => $sdgAnalysis,
       'potential_flags' => $manipulationFlags,
       'status' => 'success',
       'timestamp' => date('c')
   ];
}

// ==============================================
// FUNGSI ANALISIS SDG KOMPREHENSIF
// ==============================================

/**
* Evaluasi kontribusi SDG dengan kombinasi metode
*/
function evaluateSDGContribution($text, $sdg) {
   global $CONFIG, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_' . $sdg . '_contribution');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Analisis dasar (keyword matching)
   $keywordScores = scoreSDGs($text);
   $keywordScore = isset($keywordScores[$sdg]) ? $keywordScores[$sdg] : 0;
   
   // Analisis similarity
   $similarityScores = calculateSDGSimilarity($text);
   $similarityScore = isset($similarityScores[$sdg]) ? $similarityScores[$sdg] : 0;
   
   // Analisis substantif
   $substantiveResult = analyzeSubstantiveContribution($text, $sdg);
   $substantiveScore = $substantiveResult['score'];
   
   // Analisis hubungan kausal
   $causalResult = detectCausalRelationship($text, $sdg);
   $causalScore = $causalResult['score'];
   
   // Kombinasikan skor dengan pembobotan
   $combinedScore = (
       ($keywordScore * $CONFIG['KEYWORD_WEIGHT']) +
       ($similarityScore * $CONFIG['SIMILARITY_WEIGHT']) +
       ($substantiveScore * $CONFIG['SUBSTANTIVE_WEIGHT']) +
       ($causalScore * $CONFIG['CAUSAL_WEIGHT'])
   );
   
   // Tentukan tingkat kepercayaan
   $confidenceLevel = 'Low';
   if ($combinedScore > $CONFIG['HIGH_CONFIDENCE_THRESHOLD']) {
       $confidenceLevel = 'High';
   } elseif ($combinedScore > $CONFIG['CONFIDENCE_THRESHOLD']) {
       $confidenceLevel = 'Middle';
   }
   
   // Kumpulkan bukti konkret
   $evidence = [];
   if (!empty($causalResult['evidence'])) {
       $evidence['causal_relationship'] = $causalResult['evidence'];
   }
   
   // Ekstrak contoh kata kunci yang cocok
   $matchedKeywords = [];
   global $SDG_KEYWORDS;
   foreach ($SDG_KEYWORDS[$sdg] as $keyword) {
       if (stripos($text, $keyword) !== false) {
           $context = extractKeywordContext($text, $keyword);
           if (!empty($context)) {
               $matchedKeywords[] = [
                   'keyword' => $keyword,
                   'context' => $context
               ];
               
               // Batasi jumlah contoh
               if (count($matchedKeywords) >= 3) break;
           }
       }
   }
   
   if (!empty($matchedKeywords)) {
       $evidence['keyword_matches'] = $matchedKeywords;
   }
   
   // Hasil akhir
   $result = [
       'score' => round($combinedScore, 3),
       'confidence_level' => $confidenceLevel,
       'components' => [
           'keyword_score' => round($keywordScore, 3),
           'similarity_score' => round($similarityScore, 3),
           'substantive_score' => round($substantiveScore, 3),
           'causal_score' => round($causalScore, 3),
       ],
       'evidence' => $evidence,
       'substantive_analysis' => $substantiveResult['components']
   ];
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Analisis kontribusi substantif untuk SDG
*/
function analyzeSubstantiveContribution($text, $sdg) {
   global $SUBSTANTIVE_INDICATORS, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_' . $sdg . '_substantive');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Hitung skor untuk setiap kategori
   $scores = [];
   foreach ($SUBSTANTIVE_INDICATORS as $category => $indicators) {
       $categoryScore = 0;
       foreach ($indicators as $indicator) {
           if (stripos($text, $indicator) !== false) {
               $categoryScore++;
               
               // Berikan bonus untuk indikator yang muncul dalam frasa bermakna
               $phrases = extractPhrases($text);
               foreach ($phrases as $phrase) {
                   if (stripos($phrase, $indicator) !== false) {
                       $categoryScore += 0.5;
                       break;
                   }
               }
           }
       }
       
       // Normalisasi skor kategori
       $scores[$category] = min(1, $categoryScore / (count($indicators) * 0.5));
   }
   
   // Hitung skor rata-rata
   $avgScore = array_sum($scores) / count($scores);
   
   $result = [
       'score' => $avgScore,
       'components' => $scores
   ];
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Deteksi hubungan kausal antara teks dan SDG
*/
function detectCausalRelationship($text, $sdg) {
    global $CAUSAL_PATTERNS, $SDG_KEYWORDS, $MEMORY_CACHE;
    
    // Cek cache memori
    $cacheKey = md5($text . '_' . $sdg . '_causal');
    if (isset($MEMORY_CACHE[$cacheKey])) {
        return $MEMORY_CACHE[$cacheKey];
    }
    
    // Tambahkan pola kausal yang lebih luas dan lebih fleksibel
    $expandedPatterns = array_merge($CAUSAL_PATTERNS, [
        // Pola kausal implisit (bahasa Inggris)
        'for', 'to', 'can', 'will', 'could', 'toward', 
        'reduce', 'increase', 'improve', 'prevent', 'ensure',
        'provide', 'allow', 'enable', 'help', 'support',
        // Pola kausal implisit (bahasa Indonesia)
        'untuk', 'guna', 'agar', 'supaya', 'dapat', 'akan', 'bisa',
        'mengurangi', 'meningkatkan', 'memperbaiki', 'mencegah', 'memastikan',
        'menyediakan', 'memungkinkan', 'membantu', 'mendukung'
    ]);
    
    // Gunakan pola yang lebih luas untuk mendeteksi kausalitas
    $keywords = $SDG_KEYWORDS[$sdg];
    $bestKeywords = array_slice($keywords, 0, 10); // Ambil 10 kata kunci utama
    
    $score = 0;
    $evidences = [];
    
    // 1. Deteksi kausalitas langsung: pola kausal + kata kunci SDG
    foreach ($expandedPatterns as $pattern) {
        foreach ($bestKeywords as $keyword) {
            // Pola: "pattern keyword" atau "keyword pattern"
            $forwards = stripos($text, $pattern . ' ' . $keyword);
            $backwards = stripos($text, $keyword . ' ' . $pattern);
            
            if ($forwards !== false) {
                $score += 0.5;
                $context = extractKeywordContext($text, $pattern . ' ' . $keyword, 150);
                if (!empty($context)) {
                    $evidences[] = $context;
                }
            }
            
            if ($backwards !== false) {
                $score += 0.5;
                $context = extractKeywordContext($text, $keyword . ' ' . $pattern, 150);
                if (!empty($context)) {
                    $evidences[] = $context;
                }
            }
        }
    }
    
    // 2. Deteksi kontribusi implisit: judul/teks langsung menyebutkan target SDG
    $sdgTargets = getSdgTargetKeywords($sdg);
    foreach ($sdgTargets as $target) {
        if (stripos($text, $target) !== false) {
            $score += 0.3;
            $context = extractKeywordContext($text, $target, 150);
            if (!empty($context)) {
                $evidences[] = $context;
            }
        }
    }
    
    // 3. Analisis konteks akademis: penelitian yang dimaksudkan untuk tujuan tertentu
    $researchPurposePatterns = [
        'study', 'research', 'analyze', 'investigate', 'examine', 'assess',
        'studi', 'penelitian', 'analisis', 'investigasi', 'meneliti', 'mengkaji', 'menilai'
    ];
    
    foreach ($researchPurposePatterns as $purpose) {
        if (stripos($text, $purpose) !== false) {
            foreach ($bestKeywords as $keyword) {
                // Cek apakah ada kata kunci SDG dalam rentang 10 kata dari kata penelitian
                $pos1 = stripos($text, $purpose);
                $pos2 = stripos($text, $keyword);
                
                if ($pos1 !== false && $pos2 !== false) {
                    $distance = abs($pos1 - $pos2);
                    // Jika dalam rentang 50 karakter
                    if ($distance < 50) {
                        $score += 0.2;
                        $startPos = min($pos1, $pos2);
                        $context = substr($text, max(0, $startPos - 30), 100);
                        if (!empty($context)) {
                            $evidences[] = '...' . $context . '...';
                        }
                    }
                }
            }
        }
    }
    
    // 4. Deteksi kata kerja tindakan terkait SDG
    $actionVerbs = getSdgActionVerbs($sdg);
    foreach ($actionVerbs as $verb) {
        if (stripos($text, $verb) !== false) {
            $score += 0.2;
            $context = extractKeywordContext($text, $verb, 120);
            if (!empty($context)) {
                $evidences[] = $context;
            }
        }
    }
    
    // Normalisasi skor, maksimal 1.0
    $normalizedScore = min(1, $score);
    
    // Untuk judul pendek tanpa abstrak, berikan skor minimum untuk kausalitas
    // jika mengandung kata kunci SDG dan ada pola teks yang mendukung tujuan SDG
    if (strlen($text) < 100 && $normalizedScore == 0 && strpos($text, 'water') !== false) {
        $normalizedScore = 0.1; // Minimal score
    }
    
    $result = [
        'score' => $normalizedScore,
        'evidence' => array_slice(array_unique($evidences), 0, 3) // Ambil 3 bukti teratas
    ];
    
    // Simpan ke cache memori
    $MEMORY_CACHE[$cacheKey] = $result;
    
    return $result;
}

// Fungsi helper untuk mendapatkan kata kunci target SDG
function getSdgTargetKeywords($sdg) {
    $targets = [
        'SDG1' => ['poverty reduction', 'poverty eradication', 'social protection', 'pengentasan kemiskinan', 'perlindungan sosial'],
        'SDG2' => ['food security', 'sustainable agriculture', 'nutrition', 'hunger', 'ketahanan pangan', 'pertanian berkelanjutan'],
        'SDG3' => ['health', 'well-being', 'healthcare', 'disease prevention', 'kesehatan', 'kesejahteraan'],
        'SDG4' => ['education', 'learning', 'schooling', 'literacy', 'pendidikan', 'pembelajaran'],
        'SDG5' => ['gender equality', 'women empowerment', 'kesetaraan gender', 'pemberdayaan perempuan'],
        'SDG6' => ['clean water', 'sanitation', 'water management', 'air bersih', 'sanitasi', 'pengelolaan air'],
        'SDG7' => ['energy', 'renewable', 'sustainable energy', 'energi', 'terbarukan'],
        'SDG8' => ['economic growth', 'employment', 'decent work', 'pertumbuhan ekonomi', 'lapangan kerja'],
        'SDG9' => ['infrastructure', 'innovation', 'industrialization', 'infrastruktur', 'inovasi'],
        'SDG10' => ['inequality', 'equality', 'ketimpangan', 'kesetaraan'],
        'SDG11' => ['cities', 'settlements', 'urban', 'kota', 'permukiman', 'perkotaan'],
        'SDG12' => ['consumption', 'production', 'konsumsi', 'produksi'],
        'SDG13' => ['climate', 'climate change', 'global warming', 'iklim', 'perubahan iklim'],
        'SDG14' => ['ocean', 'marine', 'sea', 'lautan', 'kelautan', 'laut'],
        'SDG15' => ['forest', 'biodiversity', 'ecosystem', 'land', 'hutan', 'keanekaragaman hayati'],
        'SDG16' => ['peace', 'justice', 'institutions', 'perdamaian', 'keadilan', 'institusi'],
        'SDG17' => ['partnership', 'cooperation', 'global', 'kemitraan', 'kerjasama']
    ];
    
    return isset($targets[$sdg]) ? $targets[$sdg] : [];
}

// Fungsi helper untuk mendapatkan kata kerja tindakan SDG
function getSdgActionVerbs($sdg) {
    $commonVerbs = [
        'improve', 'increase', 'enhance', 'promote', 'ensure', 'strengthen',
        'develop', 'reduce', 'implement', 'create', 'establish', 'maintain',
        'meningkatkan', 'memperbaiki', 'mengembangkan', 'memastikan', 'memperkuat',
        'mengurangi', 'melaksanakan', 'menciptakan', 'mendirikan', 'memelihara'
    ];
    
    $specificVerbs = [
        'SDG6' => ['conserve', 'manage', 'treat', 'sanitize', 'purify', 'distribute',
                   'mengkonservasi', 'mengelola', 'mengolah', 'menyanitasi', 'memurnikan']
    ];
    
    return array_merge($commonVerbs, isset($specificVerbs[$sdg]) ? $specificVerbs[$sdg] : []);
}

/**
* Deteksi kemungkinan manipulasi dalam teks
*/
function detectPotentialManipulation($text, $sdgScores) {
   global $SDG_KEYWORDS;
   $flags = [];
   $wordCount = str_word_count($text);
   
   // Periksa kepadatan kata kunci yang tidak wajar
   foreach ($sdgScores as $sdg => $details) {
       $score = $details['score'];
       if ($score < 0.2) continue; // Abaikan SDG dengan skor rendah
       
       $keywords = $SDG_KEYWORDS[$sdg];
       $keywordCount = 0;
       
       foreach ($keywords as $keyword) {
           $keywordCount += substr_count(strtolower($text), strtolower($keyword));
       }
       
       // Hitung kepadatan
       $density = $wordCount > 0 ? $keywordCount / $wordCount : 0;
       
       if ($density > 0.1) { // Lebih dari 10% teks adalah kata kunci SDG?
           $flags[] = "The keyword density $sdg is very high ($density)";
       }
   }
   
   // Periksa ketidakseimbangan komponen skor
   foreach ($sdgScores as $sdg => $details) {
       if ($details['score'] < 0.2) continue;
       
       $components = $details['components'];
       $keywordScore = $components['keyword_score'];
       $substantiveScore = $components['substantive_score'];
       $causalScore = $components['causal_score'];
       
       // Jika keyword score tinggi tapi substantive dan causal score rendah
       if ($keywordScore > 0.6 && $substantiveScore < 0.2 && $causalScore < 0.2) {
           $flags[] = "SDG $sdg: High keyword match without adequate substance";
       }
   }
   
   // Terlalu banyak SDG relevan
   $highScoreSdgs = array_filter($sdgScores, function($details) {
       return $details['score'] > 0.3;
   });
   
   if (count($highScoreSdgs) > 5) {
       $flags[] = "Too many SDGs detected with high scores (" . count($highScoreSdgs) . ")";
   }
   
   return $flags;
}

// ==============================================
// FUNGSI BANTU EKSTRAKSI DAN PREPROCESSING
// ==============================================
/**
* Ekstrak nama peneliti dari data ORCID dengan penanganan error yang ditingkatkan
* @param array $person_data Data personal ORCID
* @return string Nama peneliti
*/
function extractOrcidName($person_data) {
    $name = '';
    
    // Coba ambil dari credit-name terlebih dahulu (biasanya lebih lengkap)
    if (isset($person_data['name']['credit-name']['value'])) {
        $name = $person_data['name']['credit-name']['value'];
    }
    // Jika tidak ada, coba kombinasikan given-name dan family-name
    else if (isset($person_data['name'])) {
        if (isset($person_data['name']['given-names']['value'])) {
            $name .= $person_data['name']['given-names']['value'] . ' ';
        }
        
        if (isset($person_data['name']['family-name']['value'])) {
            $name .= $person_data['name']['family-name']['value'];
        }
    }
    
    // Jika masih kosong, coba cari di biography
    if (empty(trim($name)) && isset($person_data['biography']['content'])) {
        $name = "Unknown Name"; // Default jika sama sekali tidak ditemukan
    }
    
    return trim($name);
}

/**
* Ekstrak institusi peneliti dari data ORCID
* @param array $person_data Data personal ORCID
* @return array Daftar institusi peneliti
*/
function extractOrcidInstitutions($person_data) {
    $institutions = array();
    
    // Coba ambil dari employments terlebih dahulu
    if (isset($person_data['employments']['employment-summary']) && 
        is_array($person_data['employments']['employment-summary'])) {
        
        foreach ($person_data['employments']['employment-summary'] as $employment) {
            if (isset($employment['organization']['name'])) {
                $institution = trim($employment['organization']['name']);
                if (!empty($institution) && strlen($institution) > 2) {
                    $institutions[] = $institution;
                }
            }
        }
    }
    
    // Coba ambil dari educations jika employments kosong
    if (empty($institutions) && isset($person_data['educations']['education-summary']) && 
        is_array($person_data['educations']['education-summary'])) {
        
        foreach ($person_data['educations']['education-summary'] as $education) {
            if (isset($education['organization']['name'])) {
                $institution = trim($education['organization']['name']);
                if (!empty($institution) && strlen($institution) > 2) {
                    $institutions[] = $institution;
                }
            }
        }
    }
    
    // Jika masih kosong, coba cari di affiliation-group
    if (empty($institutions) && isset($person_data['affiliation-group']) && 
        is_array($person_data['affiliation-group'])) {
        
        foreach ($person_data['affiliation-group'] as $affiliation) {
            if (isset($affiliation['summaries'][0]['organization']['name'])) {
                $institution = trim($affiliation['summaries'][0]['organization']['name']);
                if (!empty($institution) && strlen($institution) > 2) {
                    $institutions[] = $institution;
                }
            }
        }
    }
    
    return array_unique($institutions);
}

/**
* Ekstrak DOI dari summary ORCID
* @param array $summary Summary ORCID
* @return string|null DOI yang diekstrak atau null jika tidak ditemukan
*/
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
* Ekstraksi konteks sekitar kata kunci
*/
function extractKeywordContext($text, $keyword, $contextLength = 100) {
   $position = stripos($text, $keyword);
   
   if ($position === false) {
       return '';
   }
   
   $start = max(0, $position - $contextLength/2);
   $length = strlen($keyword) + $contextLength;
   
   if ($start + $length > strlen($text)) {
       $length = strlen($text) - $start;
   }
   
   $context = substr($text, $start, $length);
   
   // Pastikan tidak memotong kata
   if ($start > 0) {
       $context = '...' . $context;
   }
   
   if ($start + $length < strlen($text)) {
       $context = $context . '...';
   }
   
   // Highlight kata kunci (opsional)
   $context = preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<strong>$1</strong>', $context);
   
   return $context;
}

/**
* Ekstraksi frasa penting dari teks
*/
function extractPhrases($text) {
   global $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_phrases');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Pola sederhana untuk mendeteksi frasa: dua atau lebih kata yang bermakna
   $patterns = [
       '/\b[a-z]{3,}\s+[a-z]{3,}(\s+[a-z]{3,})?\b/i', // 2-3 kata berurutan
   ];
   
   $phrases = [];
   foreach ($patterns as $pattern) {
       preg_match_all($pattern, $text, $matches);
       if (!empty($matches[0])) {
           $phrases = array_merge($phrases, $matches[0]);
       }
   }
   
   // Filter frasa yang mengandung stopword sebagai kata pertama atau terakhir
   $stopwords = ['the', 'and', 'of', 'to', 'a', 'in', 'for', 'on', 'with', 'at', 'by', 'as'];
   $filteredPhrases = [];
   
   foreach ($phrases as $phrase) {
       $words = explode(' ', strtolower($phrase));
       $firstWord = $words[0];
       $lastWord = end($words);
       
       if (!in_array($firstWord, $stopwords) && !in_array($lastWord, $stopwords)) {
           $filteredPhrases[] = $phrase;
       }
   }
   
   $result = array_unique($filteredPhrases);
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Preprocess teks untuk analisis (optimasi performa)
*/
function preprocessText($text) {
   global $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_preprocessed');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Konversi ke lowercase
   $text = strtolower($text);
   
   // Hapus tag HTML jika ada
   $text = strip_tags($text);
   
   // Hapus karakter khusus (kecuali spasi dan alphanumeric)
   $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
   
   // Hapus spasi berlebih
   $text = preg_replace('/\s+/', ' ', $text);
   $text = trim($text);
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $text;
   
   return $text;
}

// ==============================================
// FUNGSI ANALISIS SDG DASAR (OPTIMIZED)
// ==============================================
/**
* Klasifikasi SDG berdasarkan kemunculan keyword
* Optimasi: Cache hasil, penggunaan array_intersect
*/
function classifySDGs($text) {
   global $SDG_KEYWORDS, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_classify');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   $text = strtolower($text);
   $matched = array();
   
   // Ekstrak semua kata dari teks
   $words = array_unique(str_word_count($text, 1));
   
   foreach ($SDG_KEYWORDS as $sdg => $keywords) {
       foreach ($keywords as $keyword) {
           // Untuk kata kunci kata tunggal, gunakan array_intersect untuk performa lebih baik
           if (strpos($keyword, ' ') === false) {
               if (in_array(strtolower($keyword), $words)) {
                   $matched[] = $sdg;
                   break;
               }
           } 
           // Untuk frasa, tetap gunakan strpos
           else if (strpos($text, strtolower($keyword)) !== false) {
               $matched[] = $sdg;
               break;
           }
       }
   }
   
   $result = array_values(array_unique($matched));
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Metode scoring untuk SDG berdasarkan frekuensi kemunculan keyword
* Optimasi: Precompute word frequency
*/
function scoreSDGs($text) {
   global $SDG_KEYWORDS, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_score');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
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
           else if (isset($wordFreq[strtolower($keyword)])) {
               $count += $wordFreq[strtolower($keyword)];
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
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $scores;
   
   return $scores;
}

/**
* Fungsi untuk menghitung cosine similarity antara teks dan SDG
* Optimasi: Caching, static vector cache
*/
function calculateSDGSimilarity($text) {
   global $SDG_KEYWORDS, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_similarity');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
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
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $similarity_scores;
   
   return $similarity_scores;
}

/**
* Fungsi untuk membuat vektor kata dari teks
* Optimasi: Filter kata singkat
*/
function createTextVector($text) {
   global $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_vector');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
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
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $vector;
   
   return $vector;
}

/**
* Fungsi untuk menghitung cosine similarity antara dua vektor
* Optimasi: Prioritaskan vektor lebih kecil sebagai iterator
*/
function calculateCosineSimilarity($vector1, $vector2) {
   // Pilih vektor yang lebih kecil sebagai iterator untuk performa lebih baik
   if (count($vector1) > count($vector2)) {
       $temp = $vector1;
       $vector1 = $vector2;
       $vector2 = $temp;
   }
   
   $dotProduct = 0;
   $magnitude1 = 0;
   $magnitude2 = 0;
   
   // Hitung dot product dan magnitude vektor 1
   foreach ($vector1 as $dim => $v1) {
       $v2 = isset($vector2[$dim]) ? $vector2[$dim] : 0;
       $dotProduct += $v1 * $v2;
       $magnitude1 += $v1 * $v1;
   }
   
   // Hitung magnitude vektor 2 terpisah
   foreach ($vector2 as $v2) {
       $magnitude2 += $v2 * $v2;
   }
   
   $magnitude1 = sqrt($magnitude1);
   $magnitude2 = sqrt($magnitude2);
   
   if ($magnitude1 == 0 || $magnitude2 == 0) {
       return 0;
   }
   
   return round($dotProduct / ($magnitude1 * $magnitude2), 3);
}

// ==============================================
// FUNGSI CACHE MANAGEMENT
// ==============================================
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
   global $CONFIG;
   
   if (!file_exists($filename)) {
       return false;
   }
   
   // Cek umur cache
   if ((time() - filemtime($filename)) > $CONFIG['CACHE_TTL']) {
       return false; // Cache sudah kadaluarsa
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