<?php
declare(strict_types=1);

// ### WAF v27.0 - SERVER-LEVEL PROTECTION (Standalone Mode) ###
// LOKASI: /classes/core/WizdamWAF.inc.php
// CARA KERJA: Dijalankan OTOMATIS oleh server untuk SEMUA file PHP
// IMPLEMENTASI: Via php.ini (auto_prepend_file) atau .htaccess

// ============================================
// CRITICAL: Prevent Infinite Loop & Double Load
// ============================================
if (defined('WIZDAM_WAF_V27_LOADED')) {
    return; // WAF sudah diload, skip
}
define('WIZDAM_WAF_V27_LOADED', true);

// ============================================
// CONFIGURATION
// ============================================

// Auto-detect installation path
$WAF_BASE_PATH = dirname(dirname(dirname(__FILE__))); // Go up 3 levels dari /classes/core/
$WAF_CACHE_PATH = $WAF_BASE_PATH . '/cache';

// Ensure cache directory exists
if (!is_dir($WAF_CACHE_PATH)) {
    @mkdir($WAF_CACHE_PATH, 0755, true);
}

$AF_BLOCK_LIST_FILE = $WAF_CACHE_PATH . '/AF_wizdam_IP_block.list';
$AF_ATTEMPT_LOG_FILE = $WAF_CACHE_PATH . '/AF_wizdam_attempts.log';

$WAF_BLOCKING_THRESHOLD = 10;

// ============================================
// HELPER FUNCTIONS
// ============================================

function waf_log_v27($message, $critical = false) {
    global $AF_ATTEMPT_LOG_FILE;
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN';
    $timestamp = date('Y-m-d H:i:s');
    $log = "[$timestamp] [" . ($critical ? 'CRITICAL' : 'INFO') . "] IP:$ip | $message\n";
    @file_put_contents($AF_ATTEMPT_LOG_FILE, $log, FILE_APPEND | LOCK_EX);
    if ($critical) {
        error_log("WAF v27.0 CRITICAL: $message");
    }
}

function waf_block_v27($reason, $details = '') {
    global $AF_BLOCK_LIST_FILE;
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN';
    
    // Log
    waf_log_v27("BLOCKED: $reason | $details", true);
    
    // Add to permanent block list
    $block_entry = "$ip # $reason at " . date('Y-m-d H:i:s') . "\n";
    @file_put_contents($AF_BLOCK_LIST_FILE, $block_entry, FILE_APPEND | LOCK_EX);
    
    // Send 403 response
    if (!headers_sent()) {
        header('HTTP/1.0 403 Forbidden');
        header('X-WAF-Block: WizdamWAF-v27');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }
    
    // Try to load custom 403 page
    $error_403 = '';
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $error_403 = $_SERVER['DOCUMENT_ROOT'] . '/423.shtml';
    }
    
    if (!empty($error_403) && file_exists($error_403) && is_readable($error_403)) {
        include($error_403);
    } else {
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title>'
           . '<meta name="robots" content="noindex,nofollow">'
           . '<style>body{font-family:monospace;background:#000;color:#0f0;padding:50px;}'
           . 'h1{color:#f00;text-shadow:0 0 10px #f00;}'
           . '.ref{background:#111;padding:20px;border:1px solid #0f0;margin:20px 0;}</style></head><body>'
           . '<h1>⛔ 403 FORBIDDEN</h1>'
           . '<p>Access Denied. Security policy violation detected.</p>'
           . '<div class="ref">Reference: WAF-' . substr(md5($ip . time()), 0, 12) . '</div>'
           . '<p><small>Protected by WizdamWAF v27.0 | ' . date('Y-m-d H:i:s') . '</small></p>'
           . '</body></html>';
    }
    
    exit(0);
}

// ============================================
// LAYER 0: IP BLOCK LIST CHECK
// ============================================

$WAF_IP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'UNKNOWN';

if (file_exists($AF_BLOCK_LIST_FILE)) {
    $blocked_ips = @file_get_contents($AF_BLOCK_LIST_FILE, false, null, 0, 524288);
    if ($blocked_ips && strpos($blocked_ips, $WAF_IP) !== false) {
        waf_block_v27('Previously blocked IP', 'Attempting access after block');
    }
}

// ============================================
// LAYER 1: CRITICAL FILE ACCESS PROTECTION
// ============================================

$REQUEST_URI = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$SCRIPT_NAME = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
$QUERY_STRING = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

// CRITICAL: Block dangerous files by filename
$BLOCKED_FILENAMES = [
    'zip.php', 'shell.php', 'c99.php', 'r57.php', 'wso.php', 'b374k.php',
    'adminer.php', 'pma.php', 'phpmyadmin.php', 'info.php', 'phpinfo.php',
    'test.php', 'temp.php', 'upload.php', 'uploader.php', 'fw.php',
    'alfa.php', 'alfav2.php', 'alfav3.php', 'indoxploit.php', 'mini.php',
    'configuration.php.bak', 'config.php.old', 'wp-config.php.bak'
];

foreach ($BLOCKED_FILENAMES as $blocked_file) {
    if (stripos($SCRIPT_NAME, $blocked_file) !== false ||
        stripos($REQUEST_URI, $blocked_file) !== false) {
        waf_block_v27('Blocked file access', "File: $blocked_file | Script: $SCRIPT_NAME");
    }
}

// CRITICAL: Block sensitive file patterns in URI
$BLOCKED_URI_PATTERNS = [
    '/\.git\//i',
    '/\.svn\//i',
    '/\.env/i',
    '/\.htaccess/i',
    '/\.htpasswd/i',
    '/config\.inc\.php/i',
    '/wp-config\.php/i',
    '/database\.php/i',
    '/\.sql$/i',
    '/backup\.(sql|zip|tar|gz|rar)/i',
    '/phpmyadmin/i',
    '/pma\//i',
    '/adminer/i',
];

foreach ($BLOCKED_URI_PATTERNS as $pattern) {
    if (preg_match($pattern, $REQUEST_URI)) {
        waf_block_v27('Sensitive file access', "Pattern matched: $pattern");
    }
}

// CRITICAL: Block zip.php in ALL contexts
if (stripos($REQUEST_URI, 'zip.php') !== false ||
    stripos($QUERY_STRING, 'zip.php') !== false ||
    stripos(http_build_query($_GET), 'zip.php') !== false ||
    stripos(http_build_query($_POST), 'zip.php') !== false) {
    waf_block_v27('zip.php injection detected', "URI: $REQUEST_URI");
}

// CRITICAL: Path traversal
if (preg_match('#\.\.[/\\\\]#', $REQUEST_URI) ||
    preg_match('#\.\.[/\\\\]#', $QUERY_STRING) ||
    preg_match('#\.\.[/\\\\]{2,}#', http_build_query($_GET))) {
    waf_block_v27('Path traversal', "URI: $REQUEST_URI");
}

// CRITICAL: Null byte injection
if (strpos($REQUEST_URI, "\0") !== false ||
    strpos($QUERY_STRING, "\0") !== false) {
    waf_block_v27('Null byte injection', '');
}

// ============================================
// LAYER 2: HTTP HEADER ANALYSIS
// ============================================

$USER_AGENT = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$REQUEST_METHOD = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

// Block dangerous user-agents on POST
$ATTACK_TOOLS_UA = [
    'sqlmap', 'havij', 'nikto', 'nessus', 'openvas', 'metasploit',
    'nmap', 'masscan', 'acunetix', 'burpsuite', 'w3af', 'wget', 'curl'
];

if ($REQUEST_METHOD === 'POST' || $REQUEST_METHOD === 'PUT') {
    $ua_lower = strtolower($USER_AGENT);
    foreach ($ATTACK_TOOLS_UA as $tool) {
        if (strpos($ua_lower, $tool) !== false) {
            waf_block_v27('Attack tool detected', "UA: $USER_AGENT | Method: $REQUEST_METHOD");
        }
    }
    
    // Block POST without User-Agent
    if (empty($USER_AGENT)) {
        waf_block_v27('POST without User-Agent', '');
    }
}

// Block dangerous HTTP methods
if (in_array($REQUEST_METHOD, ['TRACE', 'TRACK', 'DEBUG'])) {
    waf_block_v27('Dangerous HTTP method', "Method: $REQUEST_METHOD");
}

// ============================================
// LAYER 3: DEEP CONTENT INSPECTION - htaccess
// ============================================

function waf_scan_htaccess_v27($data, $source) {
    if (!is_string($data) || strlen($data) < 10) return;
    
    // Multi-layer decode
    $decoded = $data;
    for ($i = 0; $i < 3; $i++) {
        $decoded = urldecode($decoded);
    }
    
    // Try base64 decode
    if (preg_match('/^[A-Za-z0-9+\/=]{30,}$/', $decoded)) {
        $b64 = @base64_decode($decoded, true);
        if ($b64 !== false) {
            $decoded .= ' ' . $b64;
        }
    }
    
    $lower = strtolower($decoded);
    
    // htaccess signatures
    $htaccess_keywords = [
        'rewriteengine', 'rewritecond', 'rewriterule', 'addtype',
        'addhandler', 'sethandler', 'php_value', 'php_flag',
        'auto_prepend_file', 'auto_append_file', 'application/x-httpd-php'
    ];
    
    $matches = [];
    foreach ($htaccess_keywords as $keyword) {
        if (strpos($lower, $keyword) !== false) {
            $matches[] = $keyword;
        }
    }
    
    // 2+ keywords = htaccess injection
    if (count($matches) >= 2) {
        waf_block_v27('htaccess injection', "Source: $source | Keywords: " . implode(',', $matches));
    }
    
    // Special: zip.php + rewrite combo
    if ((strpos($lower, 'zip.php') !== false || strpos($lower, 'zip\.php') !== false) &&
        (strpos($lower, 'rewrite') !== false)) {
        waf_block_v27('zip.php htaccess combo', "Source: $source");
    }
}

// Scan all inputs
foreach ($_GET as $k => $v) {
    if (is_string($v)) waf_scan_htaccess_v27($v, "GET[$k]");
}
foreach ($_POST as $k => $v) {
    if (is_string($v)) waf_scan_htaccess_v27($v, "POST[$k]");
}
foreach ($_COOKIE as $k => $v) {
    if (is_string($v)) waf_scan_htaccess_v27($v, "COOKIE[$k]");
}

// Scan raw POST data
$raw_post = @file_get_contents('php://input');
if ($raw_post && strlen($raw_post) > 0) {
    waf_scan_htaccess_v27($raw_post, 'RAW_POST');
}

// ============================================
// LAYER 4: MALICIOUS PATTERN DETECTION
// ============================================

$WAF_SCORE = 0;
$WAF_DETAILS = [];

$WAF_RULES = [
    // RCE
    ['pattern' => '/\b(eval|system|passthru|shell_exec|exec|popen|proc_open)\s*\(/i', 'score' => 100, 'cat' => 'RCE'],
    ['pattern' => '/\b(assert|create_function)\s*\(\s*\$_/i', 'score' => 100, 'cat' => 'RCE'],
    ['pattern' => '/preg_replace.*\/[imosx]*e/i', 'score' => 100, 'cat' => 'RCE'],
    
    // PHP Wrappers
    ['pattern' => '/\b(php|file|data|glob|phar|zip|expect):\/\//i', 'score' => 80, 'cat' => 'Wrapper'],
    
    // Obfuscation
    ['pattern' => '/\b(base64_decode|gzinflate|gzuncompress|str_rot13)\s*\(\s*[\'"][A-Za-z0-9+\/=]{50,}/i', 'score' => 70, 'cat' => 'Obfuscation'],
    ['pattern' => '/eval\s*\(\s*(base64_decode|gzinflate)/i', 'score' => 100, 'cat' => 'Obfuscation'],
    
    // SQL Injection
    ['pattern' => '/union\s+(all\s+)?select/i', 'score' => 80, 'cat' => 'SQLi'],
    ['pattern' => '/\'\s*(or|and)\s+[\'"]\w+[\'"]\s*=\s*[\'"]\w+/i', 'score' => 70, 'cat' => 'SQLi'],
    ['pattern' => '/(into\s+outfile|load_file)\s*\(/i', 'score' => 80, 'cat' => 'SQLi'],
    ['pattern' => '/information_schema\.(tables|columns)/i', 'score' => 60, 'cat' => 'SQLi'],
    
    // XSS
    ['pattern' => '/<script[^>]*>.*<\/script>/is', 'score' => 40, 'cat' => 'XSS'],
    ['pattern' => '/javascript\s*:\s*(alert|eval|prompt)\s*\(/i', 'score' => 40, 'cat' => 'XSS'],
];

function waf_scan_recursive_v27(&$data, $depth = 0) {
    global $WAF_RULES, $WAF_SCORE, $WAF_DETAILS, $WAF_BLOCKING_THRESHOLD;
    
    if ($depth > 10 || $WAF_SCORE >= $WAF_BLOCKING_THRESHOLD) return;
    
    foreach ($data as $k => &$v) {
        if ($WAF_SCORE >= $WAF_BLOCKING_THRESHOLD) return;
        
        if (is_array($v)) {
            waf_scan_recursive_v27($v, $depth + 1);
        } elseif (is_string($v) && strlen($v) >= 4) {
            $decoded = urldecode($v);
            $lower = strtolower($decoded);
            
            foreach ($WAF_RULES as $rule) {
                if (preg_match($rule['pattern'], $lower, $m)) {
                    $WAF_SCORE += $rule['score'];
                    $WAF_DETAILS[] = [
                        'cat' => $rule['cat'],
                        'match' => $m[0],
                        'score' => $rule['score'],
                        'key' => $k
                    ];
                    
                    if ($WAF_SCORE >= $WAF_BLOCKING_THRESHOLD) return;
                }
            }
        }
    }
    unset($v);
}

waf_scan_recursive_v27($_GET);
waf_scan_recursive_v27($_POST);
waf_scan_recursive_v27($_COOKIE);

// ============================================
// LAYER 5: FILE UPLOAD VALIDATION
// ============================================

if (!empty($_FILES)) {
    $dangerous_exts = '/\.(php\d?|phtml|pht|phps|shtml|asp|aspx|jsp|cgi|pl|py|sh|bash|exe|com|bat|cmd|vbs|jar|htaccess|ini|conf|config)\s*$/i';
    
    foreach ($_FILES as $fkey => $finfo) {
        $files = [];
        
        if (is_array($finfo['name'])) {
            foreach ($finfo['name'] as $i => $fname) {
                $files[] = [
                    'name' => $fname,
                    'tmp' => isset($finfo['tmp_name'][$i]) ? $finfo['tmp_name'][$i] : ''
                ];
            }
        } else {
            $files[] = [
                'name' => $finfo['name'],
                'tmp' => isset($finfo['tmp_name']) ? $finfo['tmp_name'] : ''
            ];
        }
        
        foreach ($files as $file) {
            // Check extension
            if (preg_match($dangerous_exts, $file['name'])) {
                waf_block_v27('Dangerous file upload', "File: {$file['name']} | Key: $fkey");
            }
            
            // Check double extension
            if (preg_match('/\.(php|asp|jsp).*\./i', $file['name']) ||
                strpos($file['name'], "\0") !== false) {
                waf_block_v27('File extension manipulation', "File: {$file['name']}");
            }
            
            // Scan content
            if (!empty($file['tmp']) && is_uploaded_file($file['tmp'])) {
                $content = @file_get_contents($file['tmp'], false, null, 0, 16384);
                if ($content && (
                    preg_match('/<\?php/i', $content) ||
                    preg_match('/\$_(GET|POST|REQUEST|SERVER)\[/i', $content) ||
                    preg_match('/\b(eval|exec|system|shell_exec)\s*\(/i', $content)
                )) {
                    waf_block_v27('Malicious file content', "File: {$file['name']}");
                }
            }
        }
    }
}

// ============================================
// FINAL: BLOCK IF THRESHOLD EXCEEDED
// ============================================

if ($WAF_SCORE >= $WAF_BLOCKING_THRESHOLD) {
    $cats = array_unique(array_column($WAF_DETAILS, 'cat'));
    waf_block_v27('Pattern match threshold', "Score: $WAF_SCORE | Types: " . implode(',', $cats));
}

// WAF passed - log legitimate request (optional, for monitoring)
// waf_log_v27("Allowed: {$REQUEST_URI}", false);

// ### END WAF v27.0 ###

?>