<?php
declare(strict_types=1);

/**
 * @file TableParser.php
 * 
 * Copyright (c) 2024 Wizdam Technology. All rights reserved.
 * Copyright (c) 2024 Scholar Wizdam Publishing. All rights reserved.
 * Distributed under the GNU GPL v3 license. See LICENSE file for details.
 * 
 * @Class TableParser
 * @package Wizdam\JatsEngine\Parsers\Docx
 * @ingroup JatsEngine
 * 
 * @brief TableParser is responsible for parsing table elements from 
 * a DOCX document and converting them into JATS XML format. 
 * It handles the structure of the table, including rows, cells, 
 * and any relevant attributes such as colspan. The parser also integrates 
 * with a TextParser to process the content of each cell, ensuring 
 * that the text is properly formatted in the resulting JATS XML.
 * 
 * @author  Rochmady
 * @version 1.2
 * @date    2026-02-14
 */

namespace Wizdam\JatsEngine\Parsers\Docx;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Class TableParser
 * @package Wizdam\JatsEngine\Parsers\Docx
 */
class TableParser {

    protected DOMDocument $targetDom;
    protected DOMXPath $xpath;
    protected ?TextParser $textParser = null;

    /**
     * Constructor
     * @param DOMDocument $targetDom
     * @param DOMXPath $xpath
     */
    public function __construct(DOMDocument $targetDom, DOMXPath $xpath) {
        $this->targetDom = $targetDom;
        $this->xpath = $xpath;
    }

    /**
     * Set the TextParser instance
     * @param TextParser $textParser
     */
    public function setTextParser(TextParser $textParser): void {
        $this->textParser = $textParser;
    }

    /**
     * Parse a table node and convert it to JATS XML format
     * @param DOMElement $tblNode
     * @return DOMElement
     */
    public function parse(DOMElement $tblNode): DOMElement {
        // 1. Buat Wrapper <table-wrap>
        $tableWrap = $this->targetDom->createElement('table-wrap');
        $tableWrap->setAttribute('position', 'float');
        
        // Catatan: JATS lebih menyukai <caption><title>...</title></caption> daripada <label>
        $caption = $this->targetDom->createElement('caption');
        $title = $this->targetDom->createElement('title', 'Table Title Placeholder');
        $caption->appendChild($title);
        $tableWrap->appendChild($caption);

        // 2. Buat Struktur Tabel JATS
        $table = $this->targetDom->createElement('table');
        // Optional tapi sering diwajibkan oleh validator JATS
        $table->setAttribute('frame', 'hsides'); 
        $table->setAttribute('rules', 'groups');
        $tableWrap->appendChild($table);

        $rows = $this->xpath->query('w:tr', $tblNode);
        
        $thead = $this->targetDom->createElement('thead');
        $tbody = $this->targetDom->createElement('tbody');
        $hasThead = false;

        // Tracker untuk Rowspan (Vertical Merge)
        // Format: [logicalColIndex => DOMElement (td/th yang sedang di-span)]
        $activeRowspans = []; 

        foreach ($rows as $rowIndex => $row) {
            $tr = $this->targetDom->createElement('tr');
            
            // --- DETEKSI HEADER ROBUST ---
            // Cek apakah baris ini ditandai secara eksplisit sebagai header oleh Word
            $isHeaderRow = $this->xpath->query('w:trPr/w:tblHeader', $row)->length > 0;
            
            // Fallback: Jika ini baris pertama dan dokumen Word tidak menandai header sama sekali
            if ($rowIndex === 0 && !$isHeaderRow) {
                // Pastikan memang tidak ada satupun baris yang di-set sebagai header di tabel ini
                if ($this->xpath->query('w:tr/w:trPr/w:tblHeader', $tblNode)->length === 0) {
                    $isHeaderRow = true;
                }
            }

            $cells = $this->xpath->query('w:tc', $row);
            $logicalColIndex = 0;

            foreach ($cells as $cell) {
                // 1. Hitung Colspan
                $colspan = 1;
                $gridSpan = $this->xpath->query('w:tcPr/w:gridSpan/@w:val', $cell)->item(0);
                if ($gridSpan) {
                    $colspan = (int)$gridSpan->nodeValue;
                }

                // 2. Deteksi VMerge (Rowspan)
                $vMergeNode = $this->xpath->query('w:tcPr/w:vMerge', $cell)->item(0);
                $vMergeVal = $vMergeNode ? $vMergeNode->getAttribute('w:val') : null;

                // --- LOGIKA MERGE ---
                if ($vMergeNode && $vMergeVal !== 'restart') {
                    // KONDISI A: Ini adalah kelanjutan dari rowspan di baris sebelumnya.
                    // Jangan buat elemen <td>/<th> baru. Tambahkan angka rowspan di <td> asalnya.
                    if (isset($activeRowspans[$logicalColIndex])) {
                        $originCell = $activeRowspans[$logicalColIndex];
                        $currentSpan = (int)$originCell->getAttribute('rowspan') ?: 1;
                        $originCell->setAttribute('rowspan', (string)($currentSpan + 1));
                    }
                } else {
                    // KONDISI B: Sel Normal ATAU Awal dari sebuah Rowspan ('restart')
                    $tagName = $isHeaderRow ? 'th' : 'td';
                    $td = $this->targetDom->createElement($tagName);

                    if ($colspan > 1) {
                        $td->setAttribute('colspan', (string)$colspan);
                        $td->setAttribute('align', 'center');
                    }

                    // Pemrosesan Konten (TextParser)
                    $paras = $this->xpath->query('w:p', $cell);
                    foreach ($paras as $p) {
                        if ($this->textParser) {
                            $pTag = $this->targetDom->createElement('p');
                            $this->textParser->parseContent($p, $pTag);
                            $td->appendChild($pTag);
                        } else {
                            $text = trim($p->nodeValue);
                            if ($text !== '') {
                                $td->appendChild($this->targetDom->createElement('p', htmlspecialchars($text)));
                            }
                        }
                    }

                    $tr->appendChild($td);

                    // Daftarkan sel ini ke tracker jika ia memulai VMerge
                    if ($vMergeNode && $vMergeVal === 'restart') {
                        $activeRowspans[$logicalColIndex] = $td;
                    } else {
                        // Bersihkan tracker untuk kolom ini jika tidak ada merge
                        unset($activeRowspans[$logicalColIndex]);
                    }
                }
                
                // Geser indeks kolom sesuai jumlah colspan agar sel berikutnya tidak tumpang tindih
                $logicalColIndex += $colspan; 
            }

            // Masukkan ke thead atau tbody
            if ($isHeaderRow) {
                $thead->appendChild($tr);
                $hasThead = true;
            } else {
                $tbody->appendChild($tr);
            }
        }

        if ($hasThead) $table->appendChild($thead);
        $table->appendChild($tbody);

        return $tableWrap;
    }
}