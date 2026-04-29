<?php

declare(strict_types=1);

namespace Wizdam\JatsEngine;

use Exception;
use DOMDocument;
use Wizdam\JatsEngine\Builders\MetadataBuilder;
use Wizdam\JatsEngine\Builders\BodyBuilder;
use DAORegistry;

class JatsEngine {

    protected int $articleId;
    protected string $sourceFile;
    protected MetadataBuilder $metadataBuilder;
    protected BodyBuilder $bodyBuilder;
    protected string $baseDir;

    public function __construct(int $articleId) {
        $this->articleId = $articleId;
        
        // Inisialisasi Builders
        $this->metadataBuilder = new MetadataBuilder($articleId);
        $this->bodyBuilder = new BodyBuilder($articleId);
        
        // Deteksi Base Directory (Support OJS 2.x struktur folder)
        // Mundur 4 level dari lokasi file ini: lib/wizdam/JatsEngine/
        $this->baseDir = dirname(dirname(dirname(dirname(__FILE__))));
    }

    public function setSourceFile(string $filePath): void {
        if (!file_exists($filePath)) {
            throw new Exception("File sumber DOCX tidak ditemukan: " . $filePath);
        }
        $this->sourceFile = $filePath;
        
        // Serahkan path ke BodyBuilder untuk diproses nanti
        $this->bodyBuilder->setDocxPath($filePath);
    }

    public function generate(): string {
        if (empty($this->sourceFile)) {
            throw new Exception("Source file belum diset. Gunakan setSourceFile() sebelum generate.");
        }

        // --- 1. PERSIAPAN FOLDER OUTPUT ---
        $outputDir = $this->baseDir . '/assets/xmlJATS/article_' . $this->articleId;
        $this->prepareDirectory($outputDir);
        $this->prepareDirectory($outputDir . '/media');

        // --- 2. INISIALISASI DOM XML ---
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true; // Agar hasil XML rapi (pretty print)

        // Buat Root Element <article> sesuai standar JATS
        $article = $dom->createElement('article');
        $article->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $article->setAttribute('xmlns:mml', 'http://www.w3.org/1998/Math/MathML');
        $article->setAttribute('article-type', 'research-article');
        $article->setAttribute('xml:lang', 'en'); // Default en, bisa diubah dinamis jika perlu
        $dom->appendChild($article);

        // --- 3. BANGUN FRONT MATTER (Metadata dari DB) ---
        // Judul, Penulis, Abstrak, ISSN, DOI, dll.
        $this->metadataBuilder->buildFront($dom);

        // --- 4. PERSIAPAN BODY (Smart Linking) ---
        // Kita ambil data Referensi dari DB OJS *SEKARANG*.
        // Tujuannya: Agar BodyBuilder bisa "belajar" daftar pustakanya
        // dan membuat link otomatis saat menemukan teks "(Smith, 2020)".
        $citations = $this->getCitationsFromDB();
        if ($citations) {
            $this->bodyBuilder->setCitationData($citations);
        }

        // --- 5. BANGUN BODY MATTER (Isi Naskah dari DOCX) ---
        // Proses heading, paragraf, gambar, tabel, rumus.
        $this->bodyBuilder->buildBody($dom);

        // --- 6. BANGUN BACK MATTER (Referensi dari DB) ---
        // Daftar Pustaka yang rapi di bagian bawah XML.
        $this->metadataBuilder->buildBack($dom);

        // --- 7. SIMPAN FILE ---
        $xmlFilename = 'manuscript.xml';
        $fullPath = $outputDir . '/' . $xmlFilename;
        
        if ($dom->save($fullPath) === false) {
             throw new Exception("Gagal menyimpan file XML ke: " . $fullPath);
        }

        return $fullPath;
    }

    /**
     * Helper: Ambil Referensi Mentah dari Database OJS
     */
    private function getCitationsFromDB(): ?string {
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleObj = $publishedArticleDao->getPublishedArticleByArticleId($this->articleId);
        
        // Fallback jika artikel belum publish (masih tahap editing)
        if (!$articleObj) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $articleObj = $articleDao->getArticle($this->articleId);
        }

        return $articleObj ? $articleObj->getCitations() : null;
    }

    /**
     * Helper: Buat direktori dengan aman
     */
    private function prepareDirectory(string $path): void {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new Exception("Gagal membuat folder: " . $path . ". Cek permission server.");
            }
        }
    }
}