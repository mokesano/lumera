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
 * @brief Search Engine HighLighting plugin.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SehlPlugin extends GenericPlugin {
    
    /** @var array */
    protected $_queryTerms = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_queryTerms = [];
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function SehlPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Register the plugin.
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
     * Parse the query string safely.
     * @param string $query_string
     * @return array
     */
    public function parse_quote_string(string $query_string): array {
        $query_string = urldecode($query_string);
        
        // SAFEGUARD 1: Limit search string length
        if (strlen($query_string) > 200) {
            $query_string = substr($query_string, 0, 200);
        }

        $quote_flag = false;
        $word = '';
        $terms = [];
        $len = strlen($query_string);

        for ($i = 0; $i < $len; $i++) {
            $char = $query_string[$i]; // Faster than substr
            if ($char === '"') {
                $quote_flag = !$quote_flag;
            }
            if ($char === ' ' && !$quote_flag) {
                $trimmed = trim($word);
                if ($trimmed !== '') {
                    // SAFEGUARD 2: Limit single word length
                    $terms[] = substr($trimmed, 0, 30);
                }
                $word = '';
            } else {
                if ($char !== '"') {
                    $word .= $char;
                }
            }
        }
        
        $trimmed = trim($word);
        if ($trimmed !== '') {
            $terms[] = substr($trimmed, 0, 30);
        }
        
        // SAFEGUARD 3: Limit maximum number of terms
        return array_slice($terms, 0, 5); 
    }

    /**
     * Hook callback for TemplateManager::display.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function displayTemplateCallback($hookName, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        if ($template !== 'article/article.tpl') {
            return false;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? getenv('HTTP_REFERER');
        $referer = (string) $referer;
        
        // SAFEGUARD 4: Reject empty or overly long referers
        if ($referer === '' || strlen($referer) > 500) {
            return false;
        }

        // SAFEGUARD 5: Validate basic URL structure
        if (!filter_var($referer, FILTER_VALIDATE_URL)) {
            return false;
        }

        $urlParts = parse_url($referer);
        if (!isset($urlParts['query'])) {
            return false;
        }

        $queryVariableNames = [
            'q', 'p', 'ask', 'searchfor', 'key', 'query', 'search',
            'keyword', 'keywords', 'qry', 'searchitem', 'kwd',
            'recherche', 'search_text', 'search_term', 'term',
            'terms', 'qq', 'qry_str', 'qu', 's', 'k', 't', 'va'
        ];
        $this->_queryTerms = [];

        // Parse query string safely
        parse_str($urlParts['query'], $parsedQuery);
        
        if (is_array($parsedQuery)) {
            foreach ($parsedQuery as $key => $val) {
                if (in_array(strtolower((string) $key), $queryVariableNames, true) && !empty($val)) {
                    $valStr = is_array($val) ? implode(' ', $val) : (string) $val;
                    $newTerms = $this->parse_quote_string($valStr);
                    $this->_queryTerms = array_merge($this->_queryTerms, $newTerms);
                }
            }
        }

        // Remove duplicates and empty values
        $this->_queryTerms = array_filter(array_unique($this->_queryTerms));

        if (empty($this->_queryTerms)) {
            return false;
        }

        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $templateMgr->addStylesheet($request->getBaseUrl() . '/' . $this->getPluginPath() . '/sehl.css');
        $templateMgr->register_outputfilter([$this, 'outputFilter']);

        return false;
    }

    /**
     * Smarty output filter.
     * @param string $output
     * @param Smarty $smarty Passed by reference
     * @return string
     */
    public function outputFilter($output, &$smarty) {
        $fromDiv = strstr($output, '<body');
        if ($fromDiv === false) {
            return $output;
        }

        $endOfBodyTagOffset = strpos($fromDiv, '>');
        if ($endOfBodyTagOffset === false) {
            return $output;
        }

        $startIndex = strlen($output) - strlen($fromDiv) + $endOfBodyTagOffset + 1;
        $scanPart = substr($output, $startIndex);
        
        // SAFEGUARD 6: Prevent memory errors on massive outputs (> 2MB)
        if (strlen($scanPart) > 2000000) {
            return $output;
        } 

        foreach ($this->_queryTerms as $q) {
            $q = (string) $q;
            // Ignore queries shorter than 3 characters
            if (strlen($q) < 3) {
                continue;
            }

            $newOutput = '';
            $pat = '/((<[^>]*>)?)([^<]*)/si';
            
            preg_match_all($pat, $scanPart, $tag_matches);
            $count = count($tag_matches[0]);

            for ($i = 0; $i < $count; $i++) {
                $match0 = $tag_matches[0][$i];
                $match2 = $tag_matches[2][$i];
                $match3 = $tag_matches[3][$i];

                if (
                    strpos($match0, '<!') !== false ||
                    stripos($match2, '<textarea') !== false ||
                    stripos($match2, '<script') !== false
                ) {
                    $newOutput .= $match0;
                } else {
                    $newOutput .= $match2;
                    $textPart = $match3;
                    
                    if (trim($textPart) !== '') {
                        // SAFEGUARD 7: Safe preg_replace avoiding catastrophic backtracking
                        if (stripos($textPart, $q) !== false) {
                            $qSafe = preg_quote($q, '/');
                            $textPart = preg_replace('/\b(' . $qSafe . ')\b/iu', '<span class="sehl lumera">$1</span>', $textPart);
                        }
                    }
                    $newOutput .= $textPart;
                }
            }
            $scanPart = $newOutput;
        }
        
        return substr($output, 0, $startIndex) . $scanPart;
    }

    /**
     * Get display name.
     * @see Plugin::getDisplayName()
     * @return string
     */    
    public function getDisplayName(): string {
        return __('plugins.generic.sehl.name');
    }

    /**
     * Get description.
     * @see Plugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.sehl.description');
    }
    
}
?>