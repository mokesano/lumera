<?php
declare(strict_types=1);

namespace Wizdam\JatsEngine\Builders;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;
use Wizdam\JatsEngine\Parsers\Docx\ImageHandler;
use Wizdam\JatsEngine\Parsers\Docx\MathParser;
use Wizdam\JatsEngine\Parsers\Docx\TableParser;
use Wizdam\JatsEngine\Parsers\Docx\TextParser;

class BodyBuilder {
    protected int $articleId;
    protected string $docxPath;
    protected ?ImageHandler $imageHandler = null;
    protected int $sectionCounter = 1;
    protected DOMDocument $dom;
    protected array $numericMap = []; 

    public function __construct(int $articleId) {
        $this->articleId = $articleId;
    }

    public function setDocxPath(string $path) {
        $this->docxPath = $path;
    }

    public function setCitationData(?string $rawCitations): void {
        if (empty($rawCitations)) return;
        $lines = explode("\n", $rawCitations);
        $i = 1;
        foreach ($lines as $line) {
            $line = trim($line); if (empty($line)) continue;
            $this->numericMap[$i] = 'B' . $i; $i++;
        }
    }

    public function buildBody(DOMDocument $dom): void {
        $this->dom = $dom;
        if (!file_exists($this->docxPath)) return;

        $root = $dom->documentElement;
        $body = $dom->createElement('body');
        
        $assetsDir = dirname($this->docxPath) . '/assets/xmlJATS/article_' . $this->articleId;
        $this->imageHandler = new ImageHandler($this->docxPath, $assetsDir);

        $zip = new ZipArchive;
        if ($zip->open($this->docxPath) === true) {
            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if ($xmlContent) {
                // 1. TINGGALKAN SIMPLE XML, GUNAKAN DOM SEJAK AWAL
                $sourceDom = new DOMDocument();
                @$sourceDom->loadXML($xmlContent); // @ untuk suppress warning struktur raw word
                
                $xpath = new DOMXPath($sourceDom);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/officeDocument/2006/math');
                $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');
                $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                $xpath->registerNamespace('mc', 'http://schemas.openxmlformats.org/markup-compatibility/2006');

                // 2. INISIALISASI PARSER SATU KALI
                $mathParser = new MathParser($dom);
                $textParser = new TextParser($dom, $xpath, $mathParser, $this->imageHandler);
                $tableParser = new TableParser($dom, $xpath);
                $tableParser->setTextParser($textParser);

                // State Machine untuk Hirarki Section
                $currentSecLevel1 = null; // Bab
                $currentSecLevel2 = null; // Sub-bab
                $currentSecLevel3 = null; // Sub-sub-bab
                $currentList = null;      // Tracker untuk List Berurutan

                // 3. ITERASI NATIVE DOM ELEMENTS
                $nodes = $xpath->query('w:body/*', $sourceDom->documentElement);

                foreach ($nodes as $node) {
                    if (!($node instanceof DOMElement)) continue;

                    $nodeName = $node->localName; // Akan bernilai 'p', 'tbl', dll

                    // --- A. PENANGANAN TABEL ---
                    if ($nodeName === 'tbl') {
                        $tableWrap = $tableParser->parse($node);
                        $parent = $currentSecLevel3 ?? $currentSecLevel2 ?? $currentSecLevel1 ?? $body;
                        if ($tableWrap) $parent->appendChild($tableWrap);
                        continue;
                    }

                    // --- B. PENANGANAN PARAGRAF ---
                    if ($nodeName !== 'p') continue;

                    // Gunakan TextParser untuk mendeteksi Heading dengan akurat (TOC-Ready)
                    $level = $textParser->isHeading($node);

                    // Ambil teks murni untuk filter metadata & deteksi kosong
                    $plainText = trim($node->textContent);
                    $hasMedia = $xpath->query('.//w:drawing | .//w:pict | .//m:oMath', $node)->length > 0;
                    
                    if (empty($plainText) && !$hasMedia) continue;

                    // --- C. LOGIKA HIERARKI (Strict Nesting) ---
                    if ($level === 1) {
                        $currentList = null; 
                        if ($currentSecLevel1) $body->appendChild($currentSecLevel1);
                        
                        $currentSecLevel1 = $dom->createElement('sec');
                        $currentSecLevel1->setAttribute('id', 'sec-' . $this->sectionCounter++);
                        $currentSecLevel1->setAttribute('sec-type', $this->detectSectionType($plainText));
                        
                        $title = $dom->createElement('title');
                        // Gunakan parseContent agar cetak miring di judul ikut terbawa
                        $textParser->parseContent($node, $title); 
                        $currentSecLevel1->appendChild($title);
                        
                        $currentSecLevel2 = null;
                        $currentSecLevel3 = null;

                    } elseif ($level === 2) {
                        $currentList = null;
                        if (!$currentSecLevel1) {
                            $currentSecLevel1 = $dom->createElement('sec');
                            $currentSecLevel1->setAttribute('id', 'sec-' . $this->sectionCounter++);
                            $body->appendChild($currentSecLevel1);
                        }
                        
                        $currentSecLevel2 = $dom->createElement('sec');
                        $currentSecLevel2->setAttribute('id', 'sec-' . $this->sectionCounter++);
                        
                        $title = $dom->createElement('title');
                        $textParser->parseContent($node, $title);
                        $currentSecLevel2->appendChild($title);
                        
                        $currentSecLevel1->appendChild($currentSecLevel2);
                        $currentSecLevel3 = null;

                    } elseif ($level === 3) {
                        $currentList = null;
                        $parent = $currentSecLevel2 ?? $currentSecLevel1 ?? $body;
                        
                        $currentSecLevel3 = $dom->createElement('sec');
                        $currentSecLevel3->setAttribute('id', 'sec-' . $this->sectionCounter++);
                        
                        $title = $dom->createElement('title');
                        $textParser->parseContent($node, $title);
                        $currentSecLevel3->appendChild($title);
                        
                        $parent->appendChild($currentSecLevel3);

                    } else {
                        // === LEVEL 0: PARAGRAF BIASA / LIST ===
                        if ($this->isMetadataJunk($plainText) && !$currentSecLevel1) {
                             continue;
                        }

                        $pNode = $dom->createElement('p');
                        $textParser->parseContent($node, $pNode);
                        
                        // Eksekusi Citation Linker di level TextNode
                        $this->applyCitations($pNode);

                        $parent = $currentSecLevel3 ?? $currentSecLevel2 ?? $currentSecLevel1 ?? $body;

                        // Deteksi List Bawaan Word
                        $isList = $xpath->query('w:pPr/w:numPr', $node)->length > 0;

                        if ($isList) {
                            if (!$currentList) {
                                $currentList = $dom->createElement('list');
                                $currentList->setAttribute('list-type', 'bullet'); 
                                $parent->appendChild($currentList);
                            }
                            $listItem = $dom->createElement('list-item');
                            $listItem->appendChild($pNode);
                            $currentList->appendChild($listItem);
                        } else {
                            $currentList = null; // Putus rantai list
                            $parent->appendChild($pNode);
                        }
                    }
                }
                
                // Jangan lupa append section terakhir yang menggantung
                if ($currentSecLevel1) $body->appendChild($currentSecLevel1);
            }
        }
        $root->appendChild($body);
    }

    /**
     * Menerapkan smartCitationLinker dengan aman ke dalam DOM TextNode
     * tanpa merusak tag-tag format (italic/bold) dari TextParser
     */
    private function applyCitations(DOMElement $pNode): void {
        if (empty($this->numericMap)) return;

        $xpath = new DOMXPath($this->dom);
        $textNodes = [];
        // Kumpulkan semua text node murni di dalam paragraf
        foreach ($xpath->query('.//text()', $pNode) as $tn) {
            $textNodes[] = $tn;
        }

        foreach ($textNodes as $textNode) {
            $text = $textNode->nodeValue;
            if (preg_match('/\[(\d+)(?:,\s*(\d+))?\]/', $text)) {
                $processedXml = $this->smartCitationLinker(htmlspecialchars($text));
                
                if ($processedXml !== htmlspecialchars($text)) {
                    $fragment = $this->dom->createDocumentFragment();
                    @$fragment->appendXML('<root>' . $processedXml . '</root>');
                    
                    if ($fragment->firstChild) {
                        $parent = $textNode->parentNode;
                        while ($fragment->firstChild->firstChild) {
                            $parent->insertBefore($fragment->firstChild->firstChild, $textNode);
                        }
                        $parent->removeChild($textNode);
                    }
                }
            }
        }
    }

    private function detectSectionType(string $text): string {
        $t = mb_strtolower($text);
        if (strpos($t, 'pendahuluan') !== false || strpos($t, 'introduction') !== false) return 'intro';
        if (strpos($t, 'metode') !== false || strpos($t, 'method') !== false) return 'methods';
        if (strpos($t, 'hasil') !== false || strpos($t, 'result') !== false) return 'results';
        if (strpos($t, 'pembahasan') !== false || strpos($t, 'discussion') !== false) return 'discussion';
        if (strpos($t, 'simpulan') !== false || strpos($t, 'conclusion') !== false) return 'conclusions';
        if (strpos($t, 'pustaka') !== false || strpos($t, 'ref') !== false) return 'ref';
        return 'sec';
    }

    private function isMetadataJunk(string $text): bool {
        $junk = ['diterima:', 'disetujui:', 'dipublikasi:', 'keywords:', 'correspondence', 'how to cite'];
        $t = mb_strtolower($text);
        foreach ($junk as $j) {
            if (strpos($t, $j) !== false) return true;
        }
        return false;
    }

    private function smartCitationLinker(string $text): string {
        return preg_replace_callback('/\[(\d+)(?:,\s*(\d+))?\]/', function($matches) {
            $num1 = (int)$matches[1];
            $link1 = $this->numericMap[$num1] ?? null;
            if ($link1) {
                $out = '<xref ref-type="bibr" rid="'.$link1.'">['.$num1.']</xref>';
                if (isset($matches[2])) {
                    $link2 = $this->numericMap[(int)$matches[2]] ?? null;
                    if ($link2) $out .= ', <xref ref-type="bibr" rid="'.$link2.'">['.$matches[2].']</xref>';
                }
                return $out;
            }
            return $matches[0]; 
        }, $text);
    }
}