<?php
/**
 * SDG Classification API
 * Sistem klasifikasi SDG dengan orientasi dampak yang lebih kuat
 * Fokus pada kontribusi transformatif dan hubungan kausal dengan SDG
 * 
 * Fitur utama:
 * - Peningkatan bobot kausal (dari 0.10 menjadi 0.20)
 * - Penambahan analisis orientasi dampak
 * - Pemisahan tipe kontributor SDG
 * - Deteksi jalur kontribusi yang dapat ditelusuri
 * 
 * Penggunaan:
 * - Analisis Peneliti: ?orcid=0000-0002-5152-9727
 * - Analisis Artikel: ?doi=10.1234/example
 * - Refresh Cache: &refresh=true
 * 
 * @author Rochmady and Wizdam Team
 * @version 5.1.8
 * @license MIT
 * Last update: 2025-05-18
 */

// -----------------------------------------------------------------
// BAGIAN #1: PEMERIKSAAN MONITORING (UP/DOWN)
// -----------------------------------------------------------------
// Cek apakah TIDAK ADA parameter GET yang dikirim.
// Ini mendeteksi panggilan ke: https://api.sangia.org/journalscopus
if (empty($_GET)) {
    
    // Set header HTTP 200 OK (ini sudah default, tapi baik untuk eksplisit)
    http_response_code(200); 
    
    // Set tipe konten
    header('Content-Type: application/json');
    
    // Kirim balasan status "UP"
    echo json_encode([
        'status' => 'up',
        'message' => 'Endpoint is operational'
    ]);
    
    // Selesai. Hentikan eksekusi skrip agar tidak lanjut ke logika API.
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// ==============================================
// KONFIGURASI UMUM
// ==============================================
$CACHE_DIR = __DIR__ . '/cache';
if (!is_dir($CACHE_DIR)) {
    mkdir($CACHE_DIR, 0755, true);
}

// ==============================================
// KONFIGURASI SDGs DENGAN PENGAYAAN V4
// ==============================================
$CONFIG = [
    'MIN_SCORE_THRESHOLD' => 0.20,  //Skor minimal untuk ditampilkan dalam analisis detail
    'CONFIDENCE_THRESHOLD' => 0.30, //Skor minimal untuk diklaim sebagai kontribusi SDG
    'HIGH_CONFIDENCE_THRESHOLD' => 0.60, // Skor untuk dianggap kontribusi dengan kepercayaan tinggi
    'MAX_SDGS_PER_WORK' => 7,       // Jumlah maksimum SDG per karya
    
    // V4: Perubahan bobot komponen
    'KEYWORD_WEIGHT' => 0.30,    // Bobot skor kata kunci (diturunkan dari 0.40)
    'SIMILARITY_WEIGHT' => 0.30, // Bobot Cosine Similarity (diturunkan dari 0.35)
    'SUBSTANTIVE_WEIGHT' => 0.20, // Bobot Analisis Substantif (turun dari 0.25)
    'CAUSAL_WEIGHT' => 0.20,  // Bobot Analisis Hubungan Kausal (naik dari 0.10)
    
    // Definisi tipe kontributor
    'ACTIVE_CONTRIBUTOR_THRESHOLD' => 0.50,   // Threshold Active Contributor
    'RELEVANT_CONTRIBUTOR_THRESHOLD' => 0.35, // Threshold Relevant Contributor
    'DISCUSSANT_THRESHOLD' => 0.25,           // Threshold Discutor
    
    'CACHE_TTL' => 604800              // Time-to-live cache: 7 hari dalam detik
];

// Mengaktifkan semua error kecuali notices untuk memudahkan debugging
error_reporting(E_ALL & ~E_NOTICE);

// Pendefinisian variabel global yang dibutuhkan
if (!isset($MEMORY_CACHE)) {
    $MEMORY_CACHE = array();
}

if (!isset($CAUSAL_PATTERNS)) {
    $CAUSAL_PATTERNS = array(
        'contributes to', 'supports', 'advances', 'helps achieve', 'improves',
        'untuk', 'agar', 'supaya', 'mendukung', 'membantu'
    );
}

if (!isset($TRANSFORMATIVE_VERBS)) {
    $TRANSFORMATIVE_VERBS = array(
        'develop', 'implement', 'improve', 'enhance', 'establish', 'strengthen',
        'mengembangkan', 'mengimplementasikan', 'meningkatkan', 'memperbaiki'
    );
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
// V4: PENAMBAHAN KONFIGURASI ORIENTASI DAMPAK
// ==============================================

// Kata kunci yang menunjukkan orientasi dampak
$IMPACT_INDICATORS = [
    'solution_words' => [
        'solution', 'framework', 'model', 'approach', 'strategy', 'implementation', 'tool', 'method',
        'solusi', 'kerangka', 'model', 'pendekatan', 'strategi', 'implementasi', 'alat', 'metode'
    ],
    'policy_words' => [
        'policy', 'regulation', 'governance', 'planning', 'management', 'program', 'initiative',
        'kebijakan', 'regulasi', 'tata kelola', 'perencanaan', 'manajemen', 'program', 'inisiatif'
    ],
    'outcome_words' => [
        'impact', 'outcome', 'result', 'improvement', 'benefit', 'effect', 'change', 'reduction',
        'dampak', 'hasil', 'peningkatan', 'manfaat', 'efek', 'perubahan', 'pengurangan'
    ],
    'stakeholder_words' => [
        'community', 'stakeholder', 'participant', 'practitioner', 'policymaker', 'decision-maker',
        'komunitas', 'pemangku kepentingan', 'peserta', 'praktisi', 'pembuat kebijakan', 'pengambil keputusan'
    ],
    'evaluation_words' => [
        'evaluation', 'assessment', 'monitoring', 'measurement', 'indicator', 'verification', 'validation',
        'evaluasi', 'penilaian', 'pemantauan', 'pengukuran', 'indikator', 'verifikasi', 'validasi'
    ]
];

// Kata kerja tindakan transformatif
$TRANSFORMATIVE_VERBS = [
    'develop', 'implement', 'improve', 'enhance', 'establish', 'strengthen', 'transform', 'create', 
    'innovate', 'solve', 'reduce', 'increase', 'optimize', 'facilitate', 'enable',
    'mengembangkan', 'mengimplementasikan', 'meningkatkan', 'memperbaiki', 'membangun', 'memperkuat',
    'mentransformasi', 'menciptakan', 'berinovasi', 'menyelesaikan', 'mengurangi', 'mengoptimalkan'
];

// Jalur kontribusi SDG
$CONTRIBUTION_PATHWAYS = [
    'SDG1' => [
        'poverty_reduction' => ['poverty reduction', 'poverty alleviation', 'income increase'],
        'social_protection' => ['social protection', 'safety net', 'social security'],
        'basic_services' => ['basic services', 'essential services', 'access to services']
    ],
    'SDG2' => [
        'food_security' => ['food security', 'food availability', 'food access'],
        'nutrition' => ['nutrition', 'malnutrition reduction', 'balanced diet'],
        'sustainable_agriculture' => ['sustainable agriculture', 'agroecology', 'pertanian berkelanjutan']
    ],
    'SDG3' => [
        'health_coverage' => ['health coverage', 'universal healthcare', 'akses kesehatan'],
        'disease_prevention' => ['disease prevention', 'epidemic control', 'pencegahan penyakit'],
        'well_being' => ['well-being', 'mental health', 'kesejahteraan']
    ],
    'SDG4' => [
        'quality_education' => ['quality education', 'education access', 'pendidikan berkualitas'],
        'lifelong_learning' => ['lifelong learning', 'skills development', 'pembelajaran seumur hidup'],
        'education_equity' => ['education equity', 'inclusive education', 'kesetaraan pendidikan']
    ],
    'SDG5' => [
        'gender_equality' => ['gender equality', 'women empowerment', 'kesetaraan gender'],
        'violence_prevention' => ['violence prevention', 'gender-based violence', 'pencegahan kekerasan'],
        'leadership_opportunities' => ['leadership opportunities', 'women in leadership', 'kesempatan kepemimpinan']
    ],
    'SDG6' => [
        'water_access' => ['water access', 'clean water', 'safe water', 'akses air'],
        'water_management' => ['water management', 'water conservation', 'pengelolaan air'],
        'sanitation' => ['sanitation', 'hygiene', 'sanitasi', 'kebersihan']
    ],
    'SDG7' => [
       'clean_energy' => ['clean energy', 'renewable energy', 'energi bersih'],
       'energy_access' => ['energy access', 'energy poverty', 'akses energi'],
       'energy_efficiency' => ['energy efficiency', 'energy conservation', 'efisiensi energi']
   ],
   'SDG8' => [
       'economic_growth' => ['economic growth', 'decent work', 'pertumbuhan ekonomi'],
       'employment' => ['employment', 'job creation', 'kesempatan kerja'],
       'labor_rights' => ['labor rights', 'worker protection', 'hak pekerja']
   ],
   'SDG9' => [
       'infrastructure' => ['infrastructure', 'resilient infrastructure', 'infrastruktur'],
       'industrialization' => ['industrialization', 'inclusive industrialization', 'industrialisasi'],
       'innovation' => ['innovation', 'research and development', 'inovasi']
   ],
   'SDG10' => [
       'inequality_reduction' => ['inequality reduction', 'social inclusion', 'pengurangan kesenjangan'],
       'migration' => ['migration', 'safe migration', 'migrasi'],
       'financial_inclusion' => ['financial inclusion', 'access to finance', 'inklusi keuangan']
   ],
   'SDG11' => [
       'sustainable_cities' => ['sustainable cities', 'urban planning', 'kota berkelanjutan'],
       'housing' => ['housing', 'affordable housing', 'perumahan'],
       'public_spaces' => ['public spaces', 'green spaces', 'ruang publik']
   ],
   'SDG12' => [
       'sustainable_consumption' => ['sustainable consumption', 'responsible consumption', 'konsumsi berkelanjutan'],
       'waste_management' => ['waste management', 'recycling', 'pengelolaan sampah'],
       'circular_economy' => ['circular economy', 'resource efficiency', 'ekonomi sirkular']
   ],
   'SDG13' => [
       'mitigation' => ['climate mitigation', 'emission reduction', 'carbon reduction'],
       'adaptation' => ['climate adaptation', 'climate resilience', 'adaptasi iklim'],
       'awareness' => ['climate awareness', 'climate education', 'kesadaran iklim']
   ],
   'SDG14' => [
       'marine_conservation' => ['marine conservation', 'ocean health', 'konservasi laut'],
       'sustainable_fishing' => ['sustainable fishing', 'overfishing prevention', 'perikanan berkelanjutan'],
       'marine_pollution' => ['marine pollution', 'plastic pollution', 'polusi laut']
   ],
   'SDG15' => [
       'biodiversity' => ['biodiversity', 'species protection', 'keanekaragaman hayati'],
       'land_restoration' => ['land restoration', 'combat desertification', 'restorasi lahan'],
       'forest_management' => ['forest management', 'deforestation prevention', 'pengelolaan hutan']
   ],
   'SDG16' => [
       'peace' => ['peace', 'conflict resolution', 'perdamaian'],
       'justice' => ['justice', 'rule of law', 'keadilan'],
       'institutions' => ['institutions', 'accountability', 'lembaga']
   ],
   'SDG17' => [
       'partnerships' => ['partnerships', 'global cooperation', 'kemitraan'],
       'capacity_building' => ['capacity building', 'technology transfer', 'pengembangan kapasitas'],
       'trade' => ['trade', 'fair trade', 'perdagangan']
   ]
];

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
           'timestamp' => date('c'),
           'api_version' => 'v5.1.8' // Menunjukkan versi API
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
           // Pastikan personal_info ada dalam data cache
           if (!isset($cached_data['personal_info']) || empty($cached_data['personal_info'])) {
               $cached_data['personal_info'] = [
                   'name' => 'Peneliti ' . $orcid,
                   'institutions' => [],
                   'orcid' => $orcid,
                   'data_source' => 'Fallback (cache tidak lengkap)'
               ];
           }
           
           // 5. CACHE_INFO - Tambahkan info cache ke respons
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
   
   // PENTING: Pastikan personal_info selalu ada dan valid dalam hasil 
   if (!isset($result['personal_info']) || empty($result['personal_info']['name'])) {
       $result['personal_info'] = [
           'name' => 'Peneliti ' . $orcid,
           'institutions' => [],
           'orcid' => $orcid,
           'data_source' => 'ORCID API (fallback)'
       ];
   }
   
   // Simpan ke cache
   saveToCache($cache_file, $result);
   
   // 5. CACHE_INFO - Tambahkan info cache ke hasil
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
           // 5. CACHE_INFO - Tambahkan info cache ke respons
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
   
   // 5. CACHE_INFO - Tambahkan info cache ke hasil
   $result['cache_info'] = [
       'from_cache' => false,
       'cache_date' => date('c')
   ];
   
   return $result;
}

// ==============================================
// FUNGSI PENGAMBILAN DATA
// ==============================================

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
* Mengambil data personal peneliti dari API ORCID
* @param string $orcid ID ORCID peneliti
* @return array Data personal dari ORCID API
*/
function fetchOrcidPersonData($orcid) {
   $url = "https://pub.orcid.org/v3.0/{$orcid}/person";
   
   $ch = curl_init($url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array(
       'Accept: application/json'
   ));
   
   // Tambahkan timeout untuk menghindari permintaan yang terlalu lama
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
   curl_setopt($ch, CURLOPT_TIMEOUT, 10);

   $response = curl_exec($ch);
   $error = curl_error($ch);
   $errno = curl_errno($ch);
   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   curl_close($ch);

   // Log informasi untuk debugging
   error_log("ORCID API Person Response for $orcid: HTTP Code $http_code");
   
   if ($errno) {
       error_log("ORCID API Person Error for $orcid: $error");
       // Return array kosong sebagai fallback, tidak melempar exception
       return array();
   }

   if ($http_code != 200) {
       error_log("ORCID API Person HTTP Error for $orcid: HTTP Code $http_code");
       // Return array kosong sebagai fallback
       return array();
   }

   $data = json_decode($response, true);
   if (json_last_error() !== JSON_ERROR_NONE) {
       error_log("ORCID API Person JSON Error for $orcid: " . json_last_error_msg());
       // Return array kosong sebagai fallback
       return array();
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
* Mengambil abstrak alternatif jika tidak tersedia di Crossref
* @param string $doi DOI artikel
* @return string Abstrak dari sumber alternatif
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

// ==============================================
// FUNGSI PEMROSESAN DATA
// ==============================================

/**
* Memproses data ORCID untuk menghasilkan profil peneliti dengan analisis SDG
* Diperbaiki untuk memastikan struktur data yang benar
* @param string $orcid ID ORCID peneliti
* @param array $works_data Data karya dari ORCID
* @param array $person_data Data personal dari ORCID
* @return array Profil peneliti lengkap dengan analisis SDG
*/
function processOrcidData($orcid, $works_data, $person_data) {
   global $SDG_KEYWORDS, $CONFIG;

   // 1. PERSONAL INFO - Ekstrak informasi personal dengan error handling yang lebih baik
   $name = extractOrcidName($person_data);
   $institutions = extractOrcidInstitutions($person_data);
   
   if (empty($name)) {
       error_log("ORCID Profile Debug - Name Empty. Using fallback.");
       $name = "Peneliti " . $orcid;
   }

   // Inisialisasi array untuk struktur hasil
   $processed_works = array();
   $researcher_sdg_summary = array();
   $contributor_profile = array();

   // Process setiap karya dari ORCID API
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
                       $abstract = strip_tags($doi_data['message']['abstract']);
                   }
                   // Coba alternatif lokasi abstrak
                   else if (isset($doi_data['message']['JournalAbs:abstract'])) {
                       $abstract = strip_tags($doi_data['message']['JournalAbs:abstract']);
                   }
                   
                   // Jika masih kosong, coba dari sumber alternatif
                   if (empty($abstract)) {
                       $abstract = fetchAbstractFromAlternativeSource($doi);
                   }
               } catch (Exception $e) {
                   error_log("Error fetching abstract for DOI $doi: " . $e->getMessage());
               }
           }
           
           // Proses teks untuk analisis SDG
           $fullText = $title . ' ' . $abstract;
           $preprocessedText = preprocessText($fullText);
           
           // Analisis kontribusi SDG
           $sdgAnalysis = [];
           
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
                   // PENTING: Gunakan variabel berbeda untuk hasil evaluasi, bukan $result
                   $evaluationResult = evaluateSDGContribution($preprocessedText, $sdg);
                   
                   // Hanya simpan SDG dengan skor minimal
                   if ($evaluationResult['score'] > $CONFIG['MIN_SCORE_THRESHOLD']) {
                       $sdgAnalysis[$sdg] = $evaluationResult;
                   }
               }
           }
           
           // Filter dan organisasi hasil SDG
           $filteredSdgs = [];
           $sdgConfidence = [];
           $sdgContributorTypes = [];
           $sdgPathways = [];
           
           foreach ($sdgAnalysis as $sdg => $analysis) {
               if ($analysis['score'] < $CONFIG['CONFIDENCE_THRESHOLD']) {
                   continue;
               }
               
               $filteredSdgs[] = $sdg;
               $sdgConfidence[$sdg] = $analysis['score'];
               $sdgContributorTypes[$sdg] = $analysis['contributor_type']['type'];
               
               // Tambahkan pathway jika ada
               if (!empty($analysis['impact_orientation']['dominant_pathway'])) {
                   $sdgPathways[$sdg] = $analysis['impact_orientation']['dominant_pathway'];
               }
           }
           
           // Urutkan berdasarkan score
           arsort($sdgConfidence);
           
           // Batasi jumlah maksimum SDG per karya
           if (count($filteredSdgs) > $CONFIG['MAX_SDGS_PER_WORK']) {
               $tempArray = array_slice($sdgConfidence, 0, $CONFIG['MAX_SDGS_PER_WORK'], true);
               $filteredSdgs = array_keys($tempArray);
               $sdgConfidence = $tempArray;
               
               // Juga filter contributorTypes dan pathways
               foreach ([$sdgContributorTypes, $sdgPathways] as &$map) {
                   $temp = [];
                   foreach ($map as $sdg => $value) {
                       if (in_array($sdg, $filteredSdgs)) {
                           $temp[$sdg] = $value;
                       }
                   }
                   $map = $temp;
               }
           }
           
           // PENTING: Buat struktur karya yang lengkap sebelum menambahkan ke array processed_works
           $workItem = [
               'title' => $title,
               'doi' => $doi,
               'abstract' => $abstract ? $abstract : '',
               'sdgs' => $filteredSdgs,
               'sdg_confidence' => $sdgConfidence,
               'contributor_types' => $sdgContributorTypes,
               'contribution_pathways' => $sdgPathways,
               'detailed_analysis' => $sdgAnalysis
           ];
           
           // Tambahkan ke processed_works
           $processed_works[] = $workItem;
           
           // Update statistik agregat
           foreach ($sdgAnalysis as $sdg => $analysis) {
               if ($analysis['score'] >= $CONFIG['CONFIDENCE_THRESHOLD']) {
                   if (!isset($researcher_sdg_summary[$sdg])) {
                       $researcher_sdg_summary[$sdg] = [
                           'work_count' => 0,
                           'average_confidence' => 0,
                           'high_confidence_works' => 0,
                           'contributor_types' => [
                               'Active Contributor' => 0,
                               'Relevant Contributor' => 0,
                               'Discutor' => 0
                           ],
                           'dominant_pathways' => [],
                           'example_works' => []
                       ];
                   }
                   
                   $researcher_sdg_summary[$sdg]['work_count']++;
                   $researcher_sdg_summary[$sdg]['average_confidence'] += $analysis['score'];
                   
                   // Catat tipe kontributor
                   $contributorType = $analysis['contributor_type']['type'];
                   $researcher_sdg_summary[$sdg]['contributor_types'][$contributorType]++;
                   
                   // Catat pathway jika ada
                   if (!empty($analysis['impact_orientation']['dominant_pathway'])) {
                       $pathway = $analysis['impact_orientation']['dominant_pathway'];
                       if (!isset($researcher_sdg_summary[$sdg]['dominant_pathways'][$pathway])) {
                           $researcher_sdg_summary[$sdg]['dominant_pathways'][$pathway] = 0;
                       }
                       $researcher_sdg_summary[$sdg]['dominant_pathways'][$pathway]++;
                   }
                   
                   if ($analysis['score'] >= $CONFIG['HIGH_CONFIDENCE_THRESHOLD']) {
                       $researcher_sdg_summary[$sdg]['high_confidence_works']++;
                   }
                   
                   // Tambahkan contoh karya
                   if (count($researcher_sdg_summary[$sdg]['example_works']) < 3) {
                       $pathway = isset($analysis['impact_orientation']['dominant_pathway']) ? 
                           $analysis['impact_orientation']['dominant_pathway'] : null;
                       
                       $researcher_sdg_summary[$sdg]['example_works'][] = [
                           'title' => $title,
                           'doi' => $doi,
                           'confidence' => $analysis['score'],
                           'contributor_type' => $contributorType,
                           'pathway' => $pathway
                       ];
                   }
               }
           }
       }
   }

   // Finalisasi SDG summary
   foreach ($researcher_sdg_summary as $sdg => &$summary) {
       if ($summary['work_count'] > 0) {
           $summary['average_confidence'] = round($summary['average_confidence'] / $summary['work_count'], 3);
       }
       
       // Urutkan pathways berdasarkan frekuensi
       if (!empty($summary['dominant_pathways'])) {
           arsort($summary['dominant_pathways']);
       }
   }

   // Urutkan summary berdasarkan jumlah karya
   if (!empty($researcher_sdg_summary)) {
       uasort($researcher_sdg_summary, function($a, $b) {
           return $b['work_count'] - $a['work_count'];
       });
   }
   
   // Buat profil kontributor SDG
   foreach ($researcher_sdg_summary as $sdg => $summary) {
       // Tentukan tipe kontributor berdasarkan distribusi
       $activeCount = $summary['contributor_types']['Active Contributor'];
       $relevantCount = $summary['contributor_types']['Relevant Contributor'];
       $discussantCount = $summary['contributor_types']['Discutor'];
       $totalWorks = $summary['work_count'];
       
       // Tentukan tipe kontributor dominan
       $dominantType = 'Discutor'; // Default
       if ($activeCount / $totalWorks >= 0.3) {
           $dominantType = 'Active Contributor';
       } elseif (($activeCount + $relevantCount) / $totalWorks >= 0.5) {
           $dominantType = 'Relevant Contributor';
       }
       
       // Tentukan jalur kontribusi dominan
       $dominantPathway = '';
       $highestPathwayCount = 0;
       foreach ($summary['dominant_pathways'] as $pathway => $count) {
           if ($count > $highestPathwayCount) {
               $highestPathwayCount = $count;
               $dominantPathway = $pathway;
           }
       }
       
       $contributor_profile[$sdg] = [
           'dominant_type' => $dominantType,
           'dominant_pathway' => $dominantPathway,
           'work_distribution' => [
               'active_contributor' => $activeCount,
               'relevant_contributor' => $relevantCount,
               'discussant' => $discussantCount
           ],
           'active_contributor_percentage' => round(($activeCount / $totalWorks) * 100, 1),
           'contribution_strength' => determineContributionStrength($summary)
       ];
   }

   // PENTING: Buat struktur respons baru untuk memastikan hierarki data yang benar
   $finalResult = [
       // 1. PERSONAL INFO
       'personal_info' => [
           'name' => $name,
           'institutions' => $institutions,
           'orcid' => $orcid,
           'data_source' => !empty($person_data) ? 'ORCID API' : 'Fallback data (ORCID API not available)'
       ],
       // 2. CONTRIBUTOR_PROFILE
       'contributor_profile' => $contributor_profile,
       // 3. RESEARCHER_SDG_SUMMARY
       'researcher_sdg_summary' => $researcher_sdg_summary,
       // 4. WORKS - Data karya lengkap
       'works' => $processed_works,
       // Informasi tambahan
       'status' => 'success',
       'api_version' => 'v5.1.8',
       'timestamp' => date('c')
   ];
   
   return $finalResult;
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
   
   // Analisis komprehensif SDG dengan V4
   $sdgAnalysis = [];
   $allSdgs = array_keys($SDG_KEYWORDS);
   
   foreach ($allSdgs as $sdg) {
       $matched = false;
       
       // Cek kata kunci SDG dalam judul atau abstrak
       foreach ($SDG_KEYWORDS[$sdg] as $keyword) {
           if (stripos($preprocessedText, $keyword) !== false) {
               $matched = true;
               break;
           }
       }
       
       if ($matched) {
           // PENTING: Gunakan variabel berbeda untuk hasil evaluasi, bukan $result
           $evaluationResult = evaluateSDGContribution($preprocessedText, $sdg);
           
           // Hanya simpan SDG dengan skor minimal
           if ($evaluationResult['score'] > $CONFIG['MIN_SCORE_THRESHOLD']) {
               $sdgAnalysis[$sdg] = $evaluationResult;
           }
       }
   }
   
   // Filter SDG berdasarkan threshold
   $filteredSdgs = [];
   $sdgConfidence = [];
   $sdgContributorTypes = [];
   
   foreach ($sdgAnalysis as $sdg => $analysis) {
       if ($analysis['score'] < $CONFIG['CONFIDENCE_THRESHOLD']) {
           continue;
       }
       
       $filteredSdgs[] = $sdg;
       $sdgConfidence[$sdg] = $analysis['score'];
       $sdgContributorTypes[$sdg] = $analysis['contributor_type']['type'];
   }
   
   // Urutkan berdasarkan score
   arsort($sdgConfidence);
   
   // Batasi jumlah maksimum SDG
   if (count($filteredSdgs) > $CONFIG['MAX_SDGS_PER_WORK']) {
       $tempArray = array_slice($sdgConfidence, 0, $CONFIG['MAX_SDGS_PER_WORK'], true);
       $filteredSdgs = array_keys($tempArray);
       $sdgConfidence = $tempArray;
       
       // Filter contributor types juga
       $tempContributorTypes = [];
       foreach ($sdgContributorTypes as $sdg => $type) {
           if (in_array($sdg, $filteredSdgs)) {
               $tempContributorTypes[$sdg] = $type;
           }
       }
       $sdgContributorTypes = $tempContributorTypes;
   }

   // V4: Ekstrak jalur kontribusi untuk SDG yang terdeteksi
   $contributionPathways = [];
   foreach ($filteredSdgs as $sdg) {
       if (isset($sdgAnalysis[$sdg]['impact_orientation']['dominant_pathway'])) {
           $contributionPathways[$sdg] = $sdgAnalysis[$sdg]['impact_orientation']['dominant_pathway'];
       }
   }

   // PENTING: Buat struktur respons dengan hierarki yang tepat
   $finalResult = [
       'doi' => $doi,
       'title' => $title,
       'abstract' => $abstract,
       'authors' => $authors,
       'journal' => $journal,
       'published_date' => $published,
       'sdgs' => $filteredSdgs,
       'sdg_confidence' => $sdgConfidence,
       'contributor_types' => $sdgContributorTypes,
       'contribution_pathways' => $contributionPathways,
       'detailed_analysis' => $sdgAnalysis,
       'api_version' => 'v5.1.8',
       'status' => 'success',
       'timestamp' => date('c')
   ];
   
   return $finalResult;
}

// ==============================================
// FUNGSI ANALISIS SDG
// ==============================================

/**
* Melakukan evaluasi kontribusi SDG dari suatu teks
* @param string $text Teks yang akan dianalisis
* @param string $sdg Kode SDG yang akan dievaluasi
* @return array Hasil evaluasi kontribusi SDG
*/
function evaluateSDGContribution($text, $sdg) {
   global $CONFIG, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_' . $sdg . '_contribution_v4');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Analisis dasar
   $keywordScores = scoreSDGs($text);
   $keywordScore = isset($keywordScores[$sdg]) ? $keywordScores[$sdg] : 0;
   
   $similarityScores = calculateSDGSimilarity($text);
   $similarityScore = isset($similarityScores[$sdg]) ? $similarityScores[$sdg] : 0;
   
   // Analisis substantif
   $substantiveResult = analyzeSubstantiveContribution($text, $sdg);
   $substantiveScore = isset($substantiveResult['score']) ? $substantiveResult['score'] : 0;
   
   // Analisis kausal - dengan bobot lebih tinggi di V4
   $causalResult = detectCausalRelationship($text, $sdg);
   $causalScore = isset($causalResult['score']) ? $causalResult['score'] : 0;
   
   // V4: Analisis orientasi dampak
   $impactResult = evaluateImpactOrientation($text, $sdg);
   $impactScore = isset($impactResult['score']) ? $impactResult['score'] : 0;
   
   // Adaptif: Sesuaikan bobot berdasarkan ketersediaan teks
   $weights = array(
       'KEYWORD_WEIGHT' => isset($CONFIG['KEYWORD_WEIGHT']) ? $CONFIG['KEYWORD_WEIGHT'] : 0.30,
       'SIMILARITY_WEIGHT' => isset($CONFIG['SIMILARITY_WEIGHT']) ? $CONFIG['SIMILARITY_WEIGHT'] : 0.30,
       'SUBSTANTIVE_WEIGHT' => isset($CONFIG['SUBSTANTIVE_WEIGHT']) ? $CONFIG['SUBSTANTIVE_WEIGHT'] : 0.20,
       'CAUSAL_WEIGHT' => isset($CONFIG['CAUSAL_WEIGHT']) ? $CONFIG['CAUSAL_WEIGHT'] : 0.20
   );
   
   // Jika teks sangat pendek (seperti hanya judul tanpa abstrak)
   if (strlen($text) < 100) {
       $weights['KEYWORD_WEIGHT'] = 0.40;
       $weights['SIMILARITY_WEIGHT'] = 0.40;
       $weights['SUBSTANTIVE_WEIGHT'] = 0.10;
       $weights['CAUSAL_WEIGHT'] = 0.10;
   }
   
   // Kombinasikan skor dengan pembobotan yang direvisi
   $combinedScore = (
       ($keywordScore * $weights['KEYWORD_WEIGHT']) +
       ($similarityScore * $weights['SIMILARITY_WEIGHT']) +
       ($substantiveScore * $weights['SUBSTANTIVE_WEIGHT']) +
       ($causalScore * $weights['CAUSAL_WEIGHT'])
   );
   
   // Default config values jika tidak ada dalam CONFIG
   $HIGH_CONFIDENCE_THRESHOLD = isset($CONFIG['HIGH_CONFIDENCE_THRESHOLD']) ? $CONFIG['HIGH_CONFIDENCE_THRESHOLD'] : 0.60;
   $CONFIDENCE_THRESHOLD = isset($CONFIG['CONFIDENCE_THRESHOLD']) ? $CONFIG['CONFIDENCE_THRESHOLD'] : 0.30;
   
   // Tentukan level kepercayaan
   $confidenceLevel = 'Low';
   if ($combinedScore > $HIGH_CONFIDENCE_THRESHOLD) {
       $confidenceLevel = 'High';
   } elseif ($combinedScore > $CONFIDENCE_THRESHOLD) {
       $confidenceLevel = 'Middle';
   }
   
   // V4: Tentukan tipe kontributor
   $contributorType = determineContributorType($combinedScore, $causalScore, $impactScore);
   
   // Kumpulkan bukti
   $evidence = array();
   
   // Tambahkan bukti kata kunci
   $matchedKeywords = array();
   global $SDG_KEYWORDS;
   if (isset($SDG_KEYWORDS[$sdg]) && is_array($SDG_KEYWORDS[$sdg])) {
       foreach ($SDG_KEYWORDS[$sdg] as $keyword) {
           if (stripos($text, $keyword) !== false) {
               $context = extractKeywordContext($text, $keyword);
               if (!empty($context)) {
                   $matchedKeywords[] = array(
                       'keyword' => $keyword,
                       'context' => $context
                   );
                   
                   // Batasi jumlah contoh
                   if (count($matchedKeywords) >= 3) break;
               }
           }
       }
   }
   
   if (!empty($matchedKeywords)) {
       $evidence['keyword_matches'] = $matchedKeywords;
   }
   
   // Tambahkan bukti kausal
   if (isset($causalResult['evidence']) && !empty($causalResult['evidence'])) {
       $evidence['causal_relationship'] = $causalResult['evidence'];
   }
   
   // Tambahkan bukti orientasi dampak
   if (isset($impactResult['evidence']) && !empty($impactResult['evidence'])) {
       $evidence['impact_orientation'] = $impactResult['evidence'];
   }
   
   // Hasil akhir dengan pengecekan null/array
   $evaluationResult = array(
       'score' => round($combinedScore, 3),
       'confidence_level' => $confidenceLevel,
       'contributor_type' => $contributorType,
       'components' => array(
           'keyword_score' => round($keywordScore, 3),
           'similarity_score' => round($similarityScore, 3),
           'substantive_score' => round($substantiveScore, 3),
           'causal_score' => round($causalScore, 3),
           'impact_score' => round($impactScore, 3)
       ),
       'impact_orientation' => array(
           'score' => $impactResult['score'],
           'level' => $impactResult['level'],
           'dominant_pathway' => isset($impactResult['dominant_pathway']) ? $impactResult['dominant_pathway'] : ''
       ),
       'evidence' => $evidence,
       'weights_used' => $weights
   );
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $evaluationResult;
   
   return $evaluationResult;
}

/**
* Evaluasi orientasi dampak dalam teks
* Mendeteksi indikator bahwa penelitian berorientasi pada dampak nyata
* @param string $text Teks yang akan dianalisis
* @param string $sdg Kode SDG yang dianalisis
* @return array Hasil evaluasi orientasi dampak
*/
function evaluateImpactOrientation($text, $sdg) {
   global $IMPACT_INDICATORS, $TRANSFORMATIVE_VERBS, $CONTRIBUTION_PATHWAYS, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_' . $sdg . '_impact');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   $text = strtolower($text);
   $impact_scores = array();
   $evidence = array();
   
   // 1. Analisis indikator dampak
   foreach ($IMPACT_INDICATORS as $category => $indicators) {
       $score = 0;
       foreach ($indicators as $indicator) {
           if (stripos($text, $indicator) !== false) {
               $score += 1;
               // Berikan bonus jika indikator berdekatan dengan kata kerja transformatif
               if (isset($TRANSFORMATIVE_VERBS) && is_array($TRANSFORMATIVE_VERBS)) { // Periksa apakah array
                   foreach ($TRANSFORMATIVE_VERBS as $verb) {
                       // Cek pola: verb + indicator atau indicator + verb dalam jarak dekat
                       $pattern1 = $verb . ' ' . $indicator;
                       $pattern2 = $indicator . ' ' . $verb;
                       
                       if (stripos($text, $pattern1) !== false || stripos($text, $pattern2) !== false) {
                           $score += 0.5;
                           $context = extractKeywordContext($text, stripos($text, $pattern1) !== false ? $pattern1 : $pattern2);
                           if (!isset($evidence[$category])) {
                               $evidence[$category] = array();
                           }
                           $evidence[$category][] = $context;
                           break;  // Cukup satu bonus per indikator
                       }
                   }
               }
           }
       }
       // Normalisasi skor kategori (maks 1)
       $impact_scores[$category] = min(1, $score / (count($indicators) * 0.5));
   }
   
   // 2. Analisis jalur kontribusi spesifik SDG
   $pathway_scores = array();
   if (isset($CONTRIBUTION_PATHWAYS[$sdg]) && is_array($CONTRIBUTION_PATHWAYS[$sdg])) {
       foreach ($CONTRIBUTION_PATHWAYS[$sdg] as $pathway => $indicators) {
           $score = 0;
           foreach ($indicators as $indicator) {
               if (stripos($text, $indicator) !== false) {
                   $score += 1;
                   $context = extractKeywordContext($text, $indicator);
                   if (!isset($evidence['pathways'])) {
                       $evidence['pathways'] = array();
                   }
                   $evidence['pathways'][] = array(
                       'pathway' => $pathway,
                       'indicator' => $indicator,
                       'context' => $context
                   );
               }
           }
           // Normalisasi skor jalur (maks 1)
           $pathway_scores[$pathway] = min(1, $score / max(1, count($indicators))); // Hindari div by zero
       }
   }
   
   // 3. Evaluasi sintaks transformatif
   $transformative_patterns = array(
       'this research contributes to', 'we propose', 'we develop', 'this study aims to',
       'the results show', 'the findings indicate', 'we found that', 'implications for',
       'penelitian ini berkontribusi', 'kami mengusulkan', 'kami mengembangkan', 
       'studi ini bertujuan', 'hasil menunjukkan', 'temuan mengindikasikan'
   );
   
   $transformative_score = 0;
   foreach ($transformative_patterns as $pattern) {
       if (stripos($text, $pattern) !== false) {
           $transformative_score += 0.2;
           $context = extractKeywordContext($text, $pattern);
           if (!isset($evidence['transformative_language'])) {
               $evidence['transformative_language'] = array();
           }
           $evidence['transformative_language'][] = array(
               'pattern' => $pattern,
               'context' => $context
           );
       }
   }
   $transformative_score = min(1, $transformative_score);
   
   // Gabungkan semua skor
   $total_impact_score = 0;
   $total_weight = 0;
   
   // Rata-rata skor kategori impact (50% bobot)
   if (!empty($impact_scores)) {
       $total_impact_score += array_sum($impact_scores) / max(1, count($impact_scores)) * 0.5;
       $total_weight += 0.5;
   }
   
   // Rata-rata skor jalur kontribusi (30% bobot)
   if (!empty($pathway_scores)) {
       $total_impact_score += array_sum($pathway_scores) / max(1, count($pathway_scores)) * 0.3;
       $total_weight += 0.3;
   }
   
   // Skor bahasa transformatif (20% bobot)
   $total_impact_score += $transformative_score * 0.2;
   $total_weight += 0.2;
   
   // Normalisasi skor akhir berdasarkan total bobot yang digunakan
   $final_impact_score = $total_weight > 0 ? $total_impact_score / $total_weight : 0;
   
   // Tentukan level orientasi dampak
   $impact_level = 'Low';
   if ($final_impact_score > 0.6) {
       $impact_level = 'High';
   } elseif ($final_impact_score > 0.3) {
       $impact_level = 'Middle';
   }
   
   // Identifikasi jalur kontribusi dominan
   $dominant_pathway = '';
   $highest_pathway_score = 0;
   foreach ($pathway_scores as $pathway => $score) {
       if ($score > $highest_pathway_score) {
           $highest_pathway_score = $score;
           $dominant_pathway = $pathway;
       }
   }
   
   $result = array(
       'score' => round($final_impact_score, 3),
       'level' => $impact_level,
       'components' => array(
           'impact_indicators' => $impact_scores,
           'contribution_pathways' => $pathway_scores,
           'transformative_language' => $transformative_score
       ),
       'dominant_pathway' => $dominant_pathway,
       'evidence' => $evidence
   );
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Deteksi hubungan kausal antara teks dan SDG - versi yang ditingkatkan
* Dengan bobot yang lebih tinggi (0.20) dalam versi 4
* @param string $text Teks yang akan dianalisis
* @param string $sdg Kode SDG yang dianalisis
* @return array Hasil analisis hubungan kausal
*/
function detectCausalRelationship($text, $sdg) {
   global $CAUSAL_PATTERNS, $SDG_KEYWORDS, $MEMORY_CACHE, $TRANSFORMATIVE_VERBS;
   
   // Cek cache memori
   $cacheKey = md5($text . '_' . $sdg . '_causal_v4');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Inisialisasi array yang diperlukan
   if (!isset($CAUSAL_PATTERNS) || !is_array($CAUSAL_PATTERNS)) {
       $CAUSAL_PATTERNS = array(
           'contributes to', 'supports', 'advances', 'helps achieve', 'improves',
           'untuk', 'agar', 'supaya', 'mendukung', 'membantu'
       );
   }
   
   // Tambahkan pola kausal yang lebih luas dan lebih fleksibel
   $expandedPatterns = array();
   if (is_array($CAUSAL_PATTERNS)) {
       $expandedPatterns = array_merge($CAUSAL_PATTERNS, array(
           // Pola kausal implisit (bahasa Inggris)
           'for', 'to', 'can', 'will', 'could', 'toward', 
           'reduce', 'increase', 'improve', 'prevent', 'ensure',
           'provide', 'allow', 'enable', 'help', 'support',
           // Pola kausal implisit (bahasa Indonesia)
           'untuk', 'guna', 'agar', 'supaya', 'dapat', 'akan', 'bisa',
           'mengurangi', 'meningkatkan', 'memperbaiki', 'mencegah', 'memastikan',
           'menyediakan', 'memungkinkan', 'membantu', 'mendukung'
       ));
   } else {
       $expandedPatterns = array(
           'for', 'to', 'can', 'will', 'could', 'toward',
           'reduce', 'increase', 'improve', 'prevent', 'ensure',
           'untuk', 'guna', 'agar', 'supaya', 'dapat', 'akan', 'bisa'
       );
   }
   
   // Prioritaskan kata kunci SDG yang paling relevan
   $relevantKeywords = array();
   if (isset($SDG_KEYWORDS[$sdg]) && is_array($SDG_KEYWORDS[$sdg])) {
       $relevantKeywords = array_slice($SDG_KEYWORDS[$sdg], 0, 10);
   }
   
   $score = 0;
   $evidences = array();
   
   // 1. Deteksi kausalitas langsung: pola kausal + kata kunci SDG
   foreach ($expandedPatterns as $pattern) {
       foreach ($relevantKeywords as $keyword) {
           // Pola: "pattern keyword" atau "keyword pattern"
           $forwards = stripos($text, $pattern . ' ' . $keyword);
           $backwards = stripos($text, $keyword . ' ' . $pattern);
           
           if ($forwards !== false) {
               $score += 0.3;
               $context = extractKeywordContext($text, $pattern . ' ' . $keyword, 150);
               if (!empty($context)) {
                   $evidences[] = array(
                       'type' => 'direct_causality',
                       'pattern' => $pattern . ' ' . $keyword,
                       'context' => $context
                   );
               }
           }
           
           if ($backwards !== false) {
               $score += 0.3;
               $context = extractKeywordContext($text, $keyword . ' ' . $pattern, 150);
               if (!empty($context)) {
                   $evidences[] = array(
                       'type' => 'direct_causality',
                       'pattern' => $keyword . ' ' . $pattern,
                       'context' => $context
                   );
               }
           }
       }
   }
   
   // 2. Deteksi kata kerja transformatif dalam konteks SDG
   if (isset($TRANSFORMATIVE_VERBS) && is_array($TRANSFORMATIVE_VERBS)) {
       foreach ($TRANSFORMATIVE_VERBS as $verb) {
           // Cek apakah kata kerja transformatif dekat dengan kata kunci SDG
           foreach ($relevantKeywords as $keyword) {
               // Cari jarak antara kata kerja dan kata kunci
               $verbPos = stripos($text, $verb);
               $keywordPos = stripos($text, $keyword);
               
               if ($verbPos !== false && $keywordPos !== false) {
                   $distance = abs($verbPos - $keywordPos);
                   // Jika dalam rentang dekat (50 karakter)
                   if ($distance < 50) {
                       $score += 0.25;
                       $context = substr($text, max(0, min($verbPos, $keywordPos) - 30), 100);
                       if (!empty($context)) {
                           $evidences[] = array(
                               'type' => 'transformative_verb',
                               'verb' => $verb,
                               'keyword' => $keyword,
                               'context' => '...' . $context . '...'
                           );
                       }
                       break; // Hindari multiple counting untuk kata kunci yang sama
                   }
               }
           }
       }
   }
   
   // Normalisasi skor, maksimal 1.0
   $normalizedScore = min(1, $score);
   
   // Untuk judul pendek tanpa abstrak, berikan skor minimum
   if (strlen($text) < 100 && $normalizedScore < 0.1 && 
       (stripos($text, getSdgMainTerm($sdg)) !== false || hasSDGConcept($text, $sdg))) {
       $normalizedScore = max($normalizedScore, 0.1);
   }
   
   $result = array(
       'score' => $normalizedScore,
       'evidence' => array_slice($evidences, 0, 3) // Batasi bukti
   );
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Analisis kontribusi substantif untuk SDG
* @param string $text Teks yang akan dianalisis
* @param string $sdg Kode SDG yang dianalisis
* @return array Hasil analisis kontribusi substantif
*/
function analyzeSubstantiveContribution($text, $sdg) {
   global $SUBSTANTIVE_INDICATORS, $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_' . $sdg . '_substantive');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Pastikan $SUBSTANTIVE_INDICATORS terdefinisi dan merupakan array
   if (!isset($SUBSTANTIVE_INDICATORS) || !is_array($SUBSTANTIVE_INDICATORS)) {
       $SUBSTANTIVE_INDICATORS = array(
           'solution_words' => array(
               'solution', 'strategy', 'approach', 'intervention', 'policy', 'program',
               'solusi', 'strategi', 'pendekatan', 'intervensi', 'kebijakan', 'program'
           ),
           'impact_words' => array(
               'impact', 'effect', 'outcome', 'result', 'evaluation', 'assessment',
               'dampak', 'efek', 'hasil', 'evaluasi', 'penilaian'
           ),
           'methodology_words' => array(
               'survey', 'interview', 'analysis', 'study', 'research', 'method',
               'survei', 'wawancara', 'analisis', 'studi', 'penelitian', 'metode'
           )
       );
   }
   
   // Hitung skor untuk setiap kategori
   $scores = array();
   
   foreach ($SUBSTANTIVE_INDICATORS as $category => $indicators) {
       if (!is_array($indicators)) {
           continue; // Skip jika bukan array
       }
       
       $categoryScore = 0;
       foreach ($indicators as $indicator) {
           if (stripos($text, $indicator) !== false) {
               $categoryScore++;
               
               // Berikan bonus untuk indikator yang muncul dalam frasa bermakna
               $phrases = extractPhrases($text);
               
               // Pastikan $phrases adalah array
               if (is_array($phrases)) {
                   foreach ($phrases as $phrase) {
                       if (stripos($phrase, $indicator) !== false) {
                           $categoryScore += 0.5;
                           break;
                       }
                   }
               }
           }
       }
       
       // Normalisasi skor kategori, hindari division by zero
       $divisor = count($indicators) * 0.5;
       $scores[$category] = min(1, $divisor > 0 ? $categoryScore / $divisor : 0);
   }
   
   // Hitung skor rata-rata
   $avgScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
   
   $result = array(
       'score' => $avgScore,
       'components' => $scores
   );
   
   // Simpan ke cache memori
   $MEMORY_CACHE[$cacheKey] = $result;
   
   return $result;
}

/**
* Fungsi untuk mendeteksi tipe kontributor SDG
* @param float $combinedScore Skor gabungan evaluasi SDG
* @param float $causalScore Skor hubungan kausal
* @param float $impactScore Skor orientasi dampak
* @return array Tipe kontributor SDG
*/
function determineContributorType($combinedScore, $causalScore, $impactScore) {
   global $CONFIG;
   
   // Hitung skor kontribusi gabungan
   $contributionScore = ($combinedScore * 0.5) + ($causalScore * 0.3) + ($impactScore * 0.2);
   
   if ($contributionScore >= $CONFIG['ACTIVE_CONTRIBUTOR_THRESHOLD'] && 
       $causalScore >= 0.3 && $impactScore >= 0.3) {
       return [
           'type' => 'Active Contributor',
           'description' => 'Research with substantive contribution to SDG',
           'score' => round($contributionScore, 3)
       ];
   } elseif ($contributionScore >= $CONFIG['RELEVANT_CONTRIBUTOR_THRESHOLD']) {
       return [
           'type' => 'Relevant Contributor',
           'description' => 'Research with clear relevance to SDGs',
           'score' => round($contributionScore, 3)
       ];
   } elseif ($contributionScore >= $CONFIG['DISCUSSANT_THRESHOLD']) {
       return [
           'type' => 'Discutor',
           'description' => 'Research discusses SDG-related themes without clear contributions',
           'score' => round($contributionScore, 3)
       ];
   } else {
       return [
           'type' => 'Not Relevant',
           'description' => 'Research does not show sufficient relevance to the SDGs',
           'score' => round($contributionScore, 3)
       ];
   }
}

/**
* Menentukan kekuatan kontribusi peneliti terhadap SDG
* @param array $summary Ringkasan kontribusi SDG peneliti
* @return string Level kekuatan kontribusi
*/
function determineContributionStrength($summary) {
  // Hitung skor berdasarkan beberapa faktor
  $score = 0;
  
  // Faktor 1: Jumlah karya (max 3 pts)
  if ($summary['work_count'] >= 10) $score += 3;
  elseif ($summary['work_count'] >= 5) $score += 2;
  elseif ($summary['work_count'] >= 3) $score += 1;
  
  // Faktor 2: Karya confidence tinggi (max 3 pts)
  $highConfidenceRatio = $summary['high_confidence_works'] / max(1, $summary['work_count']);
  if ($highConfidenceRatio >= 0.5) $score += 3;
  elseif ($highConfidenceRatio >= 0.3) $score += 2;
  elseif ($highConfidenceRatio >= 0.1) $score += 1;
  
  // Faktor 3: Tipe kontributor (max 4 pts)
  $activeRatio = $summary['contributor_types']['Active Contributor'] / max(1, $summary['work_count']);
  if ($activeRatio >= 0.5) $score += 4;
  elseif ($activeRatio >= 0.3) $score += 3;
  elseif ($activeRatio >= 0.2) $score += 2;
  elseif ($activeRatio >= 0.1) $score += 1;
  
  // Faktor 4: Konsentrasi jalur kontribusi (max 2 pts)
  if (!empty($summary['dominant_pathways'])) {
      $pathwayValues = array_values($summary['dominant_pathways']);
      rsort($pathwayValues);
      $dominantPathwayRatio = $pathwayValues[0] / $summary['work_count'];
      
      if ($dominantPathwayRatio >= 0.6) $score += 2;
      elseif ($dominantPathwayRatio >= 0.3) $score += 1;
  }
  
  // Tentukan tingkat kekuatan (max score = 12)
  if ($score >= 10) return 'Very Strong';
  elseif ($score >= 7) return 'Strong';
  elseif ($score >= 4) return 'Moderate';
  else return 'Low';
}

// ==============================================
// FUNGSI ANALISIS SDG DASAR
// ==============================================

/**
* Klasifikasi SDG berdasarkan kemunculan keyword
* @param string $text Teks yang akan dianalisis
* @return array SDG yang terdeteksi
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
* @param string $text Teks yang akan dianalisis
* @return array Skor SDG
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
* @param string $text Teks yang akan dianalisis
* @return array Skor similarity SDG
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
* @param string $text Teks yang akan dibuat vektornya
* @return array Vektor teks
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
* @param array $vector1 Vektor pertama
* @param array $vector2 Vektor kedua
* @return float Nilai cosine similarity
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
// FUNGSI UTILITAS
// ==============================================

/**
* Mendapatkan istilah utama yang terkait dengan SDG
* @param string $sdg Kode SDG
* @return string Istilah utama SDG
*/
function getSdgMainTerm($sdg) {
  $mainTerms = [
      'SDG1' => 'poverty',
      'SDG2' => 'hunger',
      'SDG3' => 'health',
      'SDG4' => 'education',
      'SDG5' => 'gender',
      'SDG6' => 'water',
      'SDG7' => 'energy',
      'SDG8' => 'work',
      'SDG9' => 'industry',
      'SDG10' => 'inequality',
      'SDG11' => 'cities',
      'SDG12' => 'consumption',
      'SDG13' => 'climate',
      'SDG14' => 'ocean',
      'SDG15' => 'land',
      'SDG16' => 'peace',
      'SDG17' => 'partnership'
  ];
  
  return isset($mainTerms[$sdg]) ? $mainTerms[$sdg] : '';
}

/**
* Cek apakah teks berisi konsep terkait SDG
* @param string $text Teks yang akan diperiksa
* @param string $sdg Kode SDG
* @return bool True jika teks mengandung konsep SDG
*/
function hasSDGConcept($text, $sdg) {
  $conceptMap = [
       'SDG1' => ['extreme poverty', 'social protection', 'economic inclusion',
           'kemiskinan ekstrim', 'perlindungan sosial', 'inklusi ekonomi'],
       'SDG2' => ['food security', 'sustainable agriculture', 'nutrition improvement', 
           'ketahanan pangan', 'pertanian berkelanjutan', 'perbaikan gizi'],
       'SDG3' => ['maternal health', 'child mortality', 'communicable diseases', 
           'kesehatan ibu', 'kematian anak', 'penyakit menular'],
       'SDG4' => ['quality education', 'lifelong learning', 'educational infrastructure', 
           'pendidikan berkualitas', 'pembelajaran seumur hidup', 'infrastruktur pendidikan'],
       'SDG5' => ['gender equality', 'women empowerment', 'gender-based violence', 
           'kesetaraan gender', 'pemberdayaan perempuan', 'kekerasan berbasis gender'],
       'SDG6' => ['water management', 'water conservation', 'sanitation facilities', 
           'pengelolaan air', 'konservasi air', 'fasilitas sanitasi'],
       'SDG7' => ['renewable energy', 'energy efficiency', 'clean cooking',
           'energi terbarukan', 'efisiensi energi', 'memasak bersih'],
       'SDG8' => ['economic growth', 'decent work', 'youth employment',
           'pertumbuhan ekonomi', 'pekerjaan layak', 'lapangan kerja pemuda'],
       'SDG9' => ['industrial innovation', 'sustainable infrastructure', 'scientific research', 
           'inovasi industri', 'infrastruktur berkelanjutan', 'penelitian ilmiah'],
       'SDG10' => ['reduced inequalities', 'social inclusion', 'migration policies', 
           'pengurangan ketimpangan', 'inklusi sosial', 'kebijakan migrasi'],
       'SDG11' => ['sustainable cities', 'urban planning', 'public transport',
           'kota berkelanjutan', 'tata kota', 'transportasi umum'],
       'SDG12' => ['responsible consumption', 'waste reduction', 'circular economy', 
           'konsumsi bertanggung jawab', 'pengurangan limbah', 'ekonomi sirkular'],
       'SDG13' => ['climate action', 'carbon reduction', 'climate resilience',
           'aksi iklim', 'pengurangan karbon', 'ketahanan iklim'],
       'SDG14' => ['marine pollution', 'ocean acidification', 'sustainable fishing',
           'polusi laut', 'asidifikasi laut', 'perikanan berkelanjutan'],
       'SDG15' => ['biodiversity', 'forest conservation', 'land degradation',
           'keanekaragaman hayati', 'konservasi hutan', 'degradasi lahan'],
       'SDG16' => ['peacebuilding', 'access to justice', 'corruption reduction', 
           'pembangunan perdamaian', 'akses keadilan', 'pengurangan korupsi'],
       'SDG17' => ['global partnership', 'technology transfer', 'capacity building',
           'kemitraan global', 'transfer teknologi', 'pengembangan kapasitas']
  ];
  
  if (!isset($conceptMap[$sdg])) return false;
  
  foreach ($conceptMap[$sdg] as $concept) {
      if (stripos($text, $concept) !== false) {
          return true;
      }
  }
  
  return false;
}

/**
* Preprocess teks untuk analisis
* @param string $text Teks yang akan dipreprocess
* @return string Teks yang sudah dipreprocess
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

/**
* Ekstraksi frasa penting dari teks dengan penanganan error
* @param string $text Teks yang akan diekstrak frasanya
* @return array Frasa penting dari teks
*/
function extractPhrases($text) {
   // Pengecekan parameter
   if (empty($text) || !is_string($text)) {
       return array(); // Return array kosong jika input tidak valid
   }
   
   global $MEMORY_CACHE;
   
   // Cek cache memori
   $cacheKey = md5($text . '_phrases');
   if (isset($MEMORY_CACHE[$cacheKey])) {
       return $MEMORY_CACHE[$cacheKey];
   }
   
   // Pola sederhana untuk mendeteksi frasa: dua atau lebih kata yang bermakna
   $patterns = array(
       '/\b[a-z]{3,}\s+[a-z]{3,}(\s+[a-z]{3,})?\b/i', // 2-3 kata berurutan
   );
   
   $phrases = array();
   foreach ($patterns as $pattern) {
       preg_match_all($pattern, $text, $matches);
       if (!empty($matches[0])) {
           $phrases = array_merge($phrases, $matches[0]);
       }
   }
   
   // Filter frasa yang mengandung stopword sebagai kata pertama atau terakhir
   $stopwords = array('the', 'and', 'of', 'to', 'a', 'in', 'for', 'on', 'with', 'at', 'by', 'as');
   $filteredPhrases = array();
   
   foreach ($phrases as $phrase) {
       $words = explode(' ', strtolower($phrase));
       // Pastikan array $words tidak kosong
       if (empty($words)) continue;
       
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
* Ekstraksi konteks sekitar kata kunci
* @param string $text Teks sumber
* @param string $keyword Kata kunci yang dicari
* @param int $contextLength Panjang konteks yang akan diekstrak
* @return string Konteks di sekitar kata kunci
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
* Ekstrak nama peneliti dari data ORCID dengan penanganan error yang ditingkatkan
* @param array $person_data Data personal ORCID
* @return string Nama peneliti
*/
function extractOrcidName($person_data) {
   // Jika person_data null atau bukan array, return string kosong
   if (empty($person_data) || !is_array($person_data)) {
       error_log("extractOrcidName: person_data kosong atau bukan array");
       return "Unknown Researcher";
   }
   
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
       // Coba ekstrak nama dari biography jika ada
       if (is_string($person_data['biography']['content']) && !empty($person_data['biography']['content'])) {
           // Ekstrak 30 karakter pertama sebagai perkiraan nama
           $name = substr($person_data['biography']['content'], 0, 30) . "...";
       } else {
           $name = "Unknown Researcher";
       }
   }
   
   return !empty(trim($name)) ? trim($name) : "Unknown Researcher";
}

/**
* Ekstrak institusi peneliti dari data ORCID
* @param array $person_data Data personal ORCID
* @return array Daftar institusi peneliti
*/
function extractOrcidInstitutions($person_data) {
   // Jika person_data null atau bukan array, return array kosong
   if (empty($person_data) || !is_array($person_data)) {
       error_log("extractOrcidInstitutions: person_data kosong atau bukan array");
       return array();
   }
   
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

// ==============================================
// FUNGSI CACHE
// ==============================================

/**
* Menyimpan data ke cache
* @param string $filename Nama file cache
* @param array $data Data yang akan disimpan
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
* Membaca data dari cache
* @param string $filename Nama file cache
* @return array|false Data dari cache atau false jika gagal
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
* Menghasilkan nama file cache berdasarkan tipe dan ID
* @param string $type Tipe cache ('orcid' atau 'article')
* @param string $id ID ORCID atau DOI
* @return string|false Nama file cache atau false jika gagal
*/
function getCacheFilename($type, $id) {
  global $CACHE_DIR;
  
  $unique_code = substr(md5($id . '_v4'), 0, 8); // Tambahkan _v4 untuk membedakan dengan cache versi sebelumnya
  
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
// EKSEKUSI API
// ==============================================

/**
* Menjalankan API dan menghasilkan output JSON
*/
try {
  $result = main();
  echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
      'status' => 'error',
      'code' => 500,
      'message' => 'Terjadi kesalahan internal: ' . $e->getMessage(),
      'timestamp' => date('c'),
      'api_version' => 'v5.1.8'
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}