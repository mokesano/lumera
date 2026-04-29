<?php
declare(strict_types=1);

/**
 * @file plugins/generic/sehl/SehlPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SehlPlugin
 * @ingroup plugins_generic_sehl
 *
 * @brief Search Engine HighLighting plugin
 *
 * @edition Wizdam Edition (PHP 8.x Compatible - Security Optimized)
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SehlPlugin extends GenericPlugin {
    
    /** @var array */
    public $queryTerms;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SehlPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::SehlPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Register the plugin
     * @see Plugin::register()
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            HookRegistry::register('TemplateManager::display', [$this, 'displayTemplateCallback']);
            return true;
        }
        return false;
    }

    /**
     * Parse the query string.
     * @param string $query_string
     * @return array
     */
    public function parse_quote_string($query_string) {
        $query_string = urldecode($query_string);
        // SAFEGUARD 1: Batasi panjang string pencarian
        if (strlen($query_string) > 200) {
            $query_string = substr($query_string, 0, 200);
        }

        $quote_flag = false;
        $word = '';
        $terms = [];

        for ($i=0; $i<strlen($query_string); $i++) {
            $char = substr($query_string, $i, 1);
            if ($char == '"') {
                $quote_flag = !$quote_flag;
            }
            if (($char == ' ') && (!$quote_flag)) {
                if (trim($word) !== '') {
                    // SAFEGUARD 2: Batasi panjang satu kata (mencegah kata spam yang menempel jadi string panjang)
                    $terms[] = substr(trim($word), 0, 30);
                }
                $word = '';
            } else {
                if ($char !== '"') $word .= $char;
            }
        }
        if (trim($word) !== '') $terms[] = substr(trim($word), 0, 30);
        
        // SAFEGUARD 3: Batasi maksimal jumlah terms (mencegah looping panjang di outputFilter)
        return array_slice($terms, 0, 5); 
    }

    /**
     * Hook callback for TemplateManager::display
     * @param string $hookName
     * @param array $args
     */
    public function displayTemplateCallback($hookName, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        if ($template != 'article/article.tpl') return false;

        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getenv('HTTP_REFERER');
        
        // SAFEGUARD 4: Tolak referer kosong atau terlalu panjang (Lebih dari 500 karakter pasti bot/spam)
        if (empty($referer) || strlen($referer) > 500) return false;

        // SAFEGUARD 5: Validasi URL dasar
        if (!filter_var($referer, FILTER_VALIDATE_URL)) return false;

        $urlParts = parse_url($referer);
        if (!isset($urlParts['query'])) return false;

        $queryVariableNames = [
            'q', 'p', 'ask', 'searchfor', 'key', 'query', 'search',
            'keyword', 'keywords', 'qry', 'searchitem', 'kwd',
            'recherche', 'search_text', 'search_term', 'term',
            'terms', 'qq', 'qry_str', 'qu', 's', 'k', 't', 'va'
        ];
        $this->queryTerms = [];

        // Parse query string dengan batas aman
        parse_str($urlParts['query'], $parsedQuery);
        
        foreach ($parsedQuery as $key => $val) {
            if (in_array(strtolower($key), $queryVariableNames) && !empty($val)) {
                // Konversi array ke string jika perlu (misal: q[]=test)
                $valStr = is_array($val) ? implode(' ', $val) : (string)$val;
                $newTerms = $this->parse_quote_string($valStr);
                $this->queryTerms = array_merge($this->queryTerms, $newTerms);
            }
        }

        // Hapus duplikat dan kosong
        $this->queryTerms = array_filter(array_unique($this->queryTerms));

        if (empty($this->queryTerms)) return false;

        $templateMgr->addStylesheet(Request::getBaseUrl() . '/' . $this->getPluginPath() . '/sehl.css');
        $templateMgr->register_outputfilter([$this, 'outputFilter']);

        return false;
    }

    /**
     * Smarty output filter
     * @param string $output
     * @param Smarty $smarty
     * @return string
     */
    public function outputFilter($output, &$smarty) {
        $fromDiv = strstr($output, '<body');
        if ($fromDiv === false) return $output;

        $endOfBodyTagOffset = strpos($fromDiv, '>');
        if ($endOfBodyTagOffset === false) return $output;

        $startIndex = strlen($output) - strlen($fromDiv) + $endOfBodyTagOffset + 1;
        $scanPart = substr($output, $startIndex);
        
        // SAFEGUARD 6: Mencegah error memory jika output halaman terlalu raksasa (misal > 2MB)
        if (strlen($scanPart) > 2000000) return $output; 

        // Optimasi: Buat pola pencarian gabungan jika memungkinkan, 
        // namun untuk SEHL kita gunakan preg_replace langsung yang lebih ringan
        foreach ($this->queryTerms as $q) {
            // Abaikan query yang kurang dari 3 huruf (mencegah highligh "di", "ke", "a")
            if (strlen($q) < 3) continue;

            $newOutput = '';
            $pat = '/((<[^>]*>)?)([^<]*)/si';
            
            // Lakukan match all
            preg_match_all($pat, $scanPart, $tag_matches);

            for ($i=0; $i< count($tag_matches[0]); $i++) {
                if (
                    (strpos($tag_matches[0][$i], '<!') !== false) ||
                    (stripos($tag_matches[2][$i], '<textarea') !== false) ||
                    (stripos($tag_matches[2][$i], '<script') !== false)
                ) {
                    $newOutput .= $tag_matches[0][$i];
                } else {
                    $newOutput .= $tag_matches[2][$i];
                    
                    // SAFEGUARD 7: Penggantian preg_replace yang lebih aman (Baris 158 yang direvisi)
                    // Menghilangkan (.*?) yang memicu Catastrophic Backtracking
                    // Kita gunakan boundary \b (batas kata) yang jauh lebih ringan daripada \W
                    $qSafe = preg_quote($q, '/');
                    $textPart = $tag_matches[3][$i];
                    
                    if (trim($textPart) !== '') {
                         // Hanya jalankan regex jika kata tersebut benar-benar ada dalam blok teks (stristr sangat cepat)
                         if (stripos($textPart, $q) !== false) {
                              $textPart = preg_replace('/\b(' . $qSafe . ')\b/iu', '<span class="sehl">$1</span>', $textPart);
                         }
                    }
                    $newOutput .= $textPart;
                }
            }
            $scanPart = $newOutput;
        }
        
        return (substr($output, 0, $startIndex) . $scanPart);
    }

    /**
     * Get display name
     * @see Plugin::getDisplayName()
     * @return string
     */    
    public function getDisplayName(): string {
        return __('plugins.generic.sehl.name');
    }

    /**
     * Get description
     * @see Plugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.sehl.description');
    }
}
?>