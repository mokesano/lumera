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
 * @brief Provides lightweight, multi-locale NLP capabilities for the Wizdam Chatbot.
 *
 * [FIX] Root cause of the "Kesalahan koneksi" / Fatal Error after the
 * multi-locale stopword change:
 *   1. _loadStopWords() was being called with a $locale argument by the
 *      chatbot integration, but its signature took none -> ArgumentCountError
 *      (Fatal) under PHP 8 with declare(strict_types=1).
 *   2. It still pointed at a single 'stopword.txt' file that no longer
 *      exists (the project moved to per-locale files under stopword/),
 *      so even without the arity error it would silently return no
 *      stopwords for every locale.
 *   3. Several per-locale files (e.g. en_stopwords.txt) are still empty,
 *      which must NOT cause an error - just an empty stopword set.
 *
 * This version loads a base (locale-agnostic) file plus an optional
 * per-locale overlay, caches per locale, and never throws for a
 * missing/empty file.
 */

// Imports PKP untuk string utility
if (!class_exists('PKPString')) {
    import('lib.pkp.classes.core.PKPString');
}

class WizdamNLP {

    /** @var string Default locale (2-letter) used when none can be resolved. */
    private const DEFAULT_LOCALE = 'id';

    /** @var array<string,array> Cache stop words per locale code, e.g. ['id' => [...], 'en' => [...]]. */
    private static $_stopWordsCache = [];

    /** @var array<string,array> Cache intent keywords per locale code. */
    private static $_intentKeywordsCache = [];

    // --- UTILITIES DASAR ---

    private static function _splitSentences(string $text): array {
        $sentences = preg_split('/(?<=[.?!])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        return array_map('trim', $sentences ?: []);
    }

    private static function _tokenize(string $text): array {
        $text = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
        $text = PKPString::regexp_replace('/[^a-z0-9\s]/u', ' ', $text);
        return array_values(array_filter(explode(' ', $text)));
    }

    /**
     * Normalisasi kode locale OJS (mis. 'id_ID', 'en_US') menjadi kode
     * pendek 2 huruf yang dipakai sebagai nama file (mis. 'id', 'en').
     * @param string|null $locale
     * @return string
     */
    private static function _normalizeLocale(?string $locale): string {
        if ($locale === null || $locale === '') {
            // Coba ambil locale aktif aplikasi jika tersedia.
            $locale = class_exists('AppLocale') ? (string) AppLocale::getLocale() : self::DEFAULT_LOCALE;
        }
        $short = strtolower(substr($locale, 0, 2));
        return $short !== '' ? $short : self::DEFAULT_LOCALE;
    }

    // --- MANAJEMEN DATA KONFIGURASI (STOPWORDS & INTENT) ---

    /**
     * Parse satu file stopword (format [INTENT]/[LANG] + kata biasa).
     * @param string $filePath
     * @return array{0: array, 1: array} [stopWords, intentKeywords]
     */
    private static function _parseStopWordFile(string $filePath): array {
        $stopWords = [];
        $intentKeywords = [];

        if ($filePath === '' || !is_readable($filePath)) {
            return [$stopWords, $intentKeywords];
        }

        $content = @file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            return [$stopWords, $intentKeywords];
        }

        $lines = array_map('trim', explode("\n", $content));

        foreach ($lines as $line) {
            if ($line === '' || strpos($line, '#') === 0) continue;

            $lineLower = strtolower($line);

            if (strpos($lineLower, '[intent]') === 0) {
                $keyword = trim(str_replace('[intent]', '', $lineLower));
                if ($keyword !== '') {
                    $intentKeywords[] = $keyword;
                    $stopWords[] = $keyword;
                }
            } elseif (strpos($lineLower, '[lang]') === 0) {
                $keyword = trim(str_replace('[lang]', '', $lineLower));
                if ($keyword !== '') {
                    $stopWords[] = $keyword;
                }
            } else {
                $stopWords[] = $lineLower;
            }
        }

        return [$stopWords, $intentKeywords];
    }

    /**
     * Memuat daftar stop words untuk locale tertentu.
     * Menggabungkan file dasar (stopwords.txt, berlaku untuk semua locale -
     * berisi terutama marker [INTENT] umum) dengan file overlay khusus
     * locale (stopword/{kode}_stopwords.txt), jika ada.
     *
     * Tidak pernah fatal: file yang hilang/kosong hanya menghasilkan
     * kumpulan stopword yang lebih kecil.
     *
     * @param string|null $locale Kode locale OJS penuh atau pendek. Null = locale aktif.
     * @return array Daftar stop words untuk locale tsb.
     */
    private static function _loadStopWords(?string $locale = null): array {
        $code = self::_normalizeLocale($locale);

        if (isset(self::$_stopWordsCache[$code])) {
            return self::$_stopWordsCache[$code];
        }

        $baseDir = dirname(__FILE__);

        // File dasar (global, dipakai lintas locale untuk marker umum).
        [$baseWords, $baseIntents] = self::_parseStopWordFile($baseDir . DIRECTORY_SEPARATOR . 'stopwords.txt');

        // Overlay khusus locale.
        $localeFile = $baseDir . DIRECTORY_SEPARATOR . 'stopword' . DIRECTORY_SEPARATOR . $code . '_stopwords.txt';
        [$localeWords, $localeIntents] = self::_parseStopWordFile($localeFile);

        // Fallback: jika overlay locale kosong/tidak ada dan locale != default,
        // coba locale default supaya chatbot tetap punya stopword dasar.
        if (empty($localeWords) && $code !== self::DEFAULT_LOCALE) {
            $fallbackFile = $baseDir . DIRECTORY_SEPARATOR . 'stopword' . DIRECTORY_SEPARATOR . self::DEFAULT_LOCALE . '_stopwords.txt';
            [$localeWords, $localeIntents] = self::_parseStopWordFile($fallbackFile);
        }

        $stopWords = array_values(array_unique(array_merge($baseWords, $localeWords)));
        $intentKeywords = array_values(array_unique(array_merge($baseIntents, $localeIntents)));

        self::$_stopWordsCache[$code] = $stopWords;
        self::$_intentKeywordsCache[$code] = $intentKeywords;

        return $stopWords;
    }

    /**
     * Mendapatkan daftar kata kunci yang MENGINDIKASIKAN intensi CONTEXT AWARE.
     * @param string|null $locale
     * @return array
     */
    public static function getIntentKeywords(?string $locale = null): array {
        $code = self::_normalizeLocale($locale);
        if (!isset(self::$_intentKeywordsCache[$code])) {
            self::_loadStopWords($code);
        }
        return self::$_intentKeywordsCache[$code] ?? [];
    }

    /**
     * Filter query pengguna untuk mendapatkan kata kunci inti.
     * @param string $query
     * @param bool $removeStopWords
     * @param string|null $locale Kode locale OJS (mis. 'id_ID'). Null = locale aktif.
     */
    public static function filterKeywords(string $query, bool $removeStopWords = true, ?string $locale = null): string {
        $keywordsRaw = trim(function_exists('mb_strtolower') ? mb_strtolower(strip_tags($query)) : strtolower(strip_tags($query)));

        $words = self::_tokenize($keywordsRaw);

        if ($removeStopWords) {
            $stopWords = self::_loadStopWords($locale);
            $keywordsArray = array_diff($words, $stopWords);
        } else {
            $keywordsArray = $words;
        }

        $keywords = trim(implode(' ', $keywordsArray));

        // Logika Fallback
        if (empty($keywords) || strlen($keywords) < 5) {
            $nonStopWords = array_diff($words, self::_loadStopWords($locale));
            if (count($nonStopWords) > 0) {
                usort($nonStopWords, function ($a, $b) { return strlen($b) <=> strlen($a); });
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
     * @param string $text
     * @param int $sentenceCount
     * @param string|null $locale
     */
    public static function summarizeText(string $text, int $sentenceCount = 3, ?string $locale = null): string {
        $sentences = self::_splitSentences($text);
        $cleanSentences = [];
        $scores = [];

        $allKeywords = self::filterKeywords($text, false, $locale);
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