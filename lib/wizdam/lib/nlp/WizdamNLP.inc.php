<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/lib/nlp/WizdamNLP.inc.php
 *
 * Copyright (c) 2025 Wizdam Fork Team
 * Distributed under the GNU GPL v2.
 *
 * @class WizdamNLP
 * @ingroup wizdam_lib_nlp
 *
 * @brief Provides lightweight NLP capabilities for Wizdam Chatbot.
 */

// Imports PKP untuk string utility
if (!class_exists('PKPString')) {
    import('lib.pkp.classes.core.PKPString'); 
}

class WizdamNLP {
    
    /** @var array Cache untuk daftar stop words tunggal. */
    private static $_stopWordsCache = null;
    /** @var array Cache untuk daftar intent keywords. */
    private static $_intentKeywordsCache = null; 
    
    // --- UTILITIES DASAR ---

    private static function _splitSentences(string $text): array {
        $sentences = preg_split('/(?<=[.?!])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_map('trim', $sentences);
    }

    private static function _tokenize(string $text): array {
        $text = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
        $text = PKPString::regexp_replace('/[^a-z0-9\s]/u', ' ', $text);
        return array_filter(explode(' ', $text));
    }
    
    
    // --- MANAJEMEN DATA KONFIGURASI (STOPWORDS & INTENT) ---

    /**
     * Memuat daftar stop words dari file teks tunggal (stopword.txt).
     * @return array Daftar stop words murni.
     */
    private static function _loadStopWords(): array {
        if (self::$_stopWordsCache !== null) {
            return self::$_stopWordsCache;
        }

        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'stopword.txt';
        $stopWords = [];
        $intentKeywords = [];

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $lines = array_map('trim', explode("\n", $content));
            
            foreach ($lines as $line) {
                if (empty($line) || substr($line, 0, 1) === '#') continue;

                $lineLower = strtolower($line);

                if (strpos($lineLower, '[intent]') === 0) {
                    $keyword = trim(str_replace('[intent]', '', $lineLower));
                    if ($keyword) {
                        $intentKeywords[] = $keyword;
                        $stopWords[] = $keyword; 
                    }
                } else {
                    $stopWords[] = $lineLower;
                }
            }
        }
        
        self::$_stopWordsCache = array_unique($stopWords);
        self::$_intentKeywordsCache = array_unique($intentKeywords);

        return self::$_stopWordsCache;
    }
    
    /**
     * Mendapatkan daftar kata kunci yang MENGINDIKASIKAN intensi CONTEXT AWARE.
     * @return array
     */
    public static function getIntentKeywords(): array {
        if (self::$_intentKeywordsCache === null) {
            self::_loadStopWords();
        }
        return self::$_intentKeywordsCache ?? []; 
    }
    
    /**
     * Filter query pengguna untuk mendapatkan kata kunci inti.
     */
    public static function filterKeywords(string $query, bool $removeStopWords = true): string {
        $keywordsRaw = trim(function_exists('mb_strtolower') ? mb_strtolower(strip_tags($query)) : strtolower(strip_tags($query)));
        
        $words = self::_tokenize($keywordsRaw);
        
        if ($removeStopWords) {
            $stopWords = self::_loadStopWords(); 
            $keywordsArray = array_diff($words, $stopWords);
        } else {
            $keywordsArray = $words;
        }
        
        $keywords = trim(implode(' ', $keywordsArray));
        
        // Logika Fallback 
        if (empty($keywords) || strlen($keywords) < 5) {
            $nonStopWords = array_diff($words, self::_loadStopWords());
            if (count($nonStopWords) > 0) {
                 usort($nonStopWords, function($a, $b) { return strlen($b) <=> strlen($a); });
                 $keywords = implode(' ', array_slice($nonStopWords, 0, 2));
            }
        }
        
        return $keywords;
    }
    
    /**
     * Metode ini mengembalikan tebakan bahasa yang disederhanakan.
     */
    public static function guessLanguageCode(string $query): string {
        $queryLower = strtolower($query);
        
        // Pola sederhana: Cek karakter non-ASCII atau kata tanya B. Indonesia
        if (preg_match('/[^\x00-\x7F]/', $query) || strpos($queryLower, 'apa') !== false || strpos($queryLower, 'bagaimana') !== false) {
            return 'id';
        }
        return 'en';
    }

    /**
     * Membuat ringkasan cerdas (extractive summary) dari teks penuh.
     */
    public static function summarizeText(string $text, int $sentenceCount = 3): string {
        $sentences = self::_splitSentences($text);
        $cleanSentences = [];
        $scores = [];
        
        $allKeywords = self::filterKeywords($text, false); 
        $keywordFrequencies = array_count_values(explode(' ', $allKeywords));
        
        foreach ($sentences as $index => $sentence) {
            $words = self::_tokenize($sentence);
            $score = 0;
            
            foreach ($words as $word) {
                if (isset($keywordFrequencies[$word])) {
                    $score += $keywordFrequencies[$word];
                }
            }
            
            if ($index < 2) {
                $score += 1.5 * count($words);
            }
            
            $scores[$index] = $score;
            $cleanSentences[$index] = $sentence;
        }

        arsort($scores);
        $bestSentenceKeys = array_slice(array_keys($scores), 0, $sentenceCount);
        sort($bestSentenceKeys);
        
        $summary = '';
        foreach ($bestSentenceKeys as $key) {
            $summary .= $cleanSentences[$key] . ' ';
        }

        return trim($summary);
    }
}