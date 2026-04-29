<?php
declare(strict_types=1);

/**
 * @file MathParser.php
 * 
 * Copyright (c) 2024 Wizdam Technology. All rights reserved.
 * Copyright (c) 2024 Scholar Wizdam Publishing. All rights reserved.
 * Distributed under the GNU GPL v3 license. See LICENSE file for details.
 * 
 * @Class MathParser
 * @package Wizdam\JatsEngine\Parsers\Docx
 * @ingroup JatsEngine
 * 
 * @brief MathParser bertugas mengubah node MathML dari DOCX menjadi format XML JATS.
 * 
 * @author  Rochmady
 * @version 1.3
 * @date    2026-02-14
 */

namespace Wizdam\JatsEngine\Parsers\Docx;

use DOMDocument;
use DOMElement;
use XSLTProcessor;
use RuntimeException;

class MathParser {

    protected DOMDocument $targetDom;
    protected ?XSLTProcessor $xsltProc = null;
    protected string $xslPath;

    /**
     * Constructor
     * @param DOMDocument $targetDom DOMDocument tujuan
     * @param string|null $xslPath Path opsional ke file XSLT, jika null akan pakai default
     */
    public function __construct(DOMDocument $targetDom, ?string $xslPath = null) {
        $this->targetDom = $targetDom;
        
        // Gunakan path yang dinamis atau fallback ke struktur default menggunakan __DIR__
        $this->xslPath = $xslPath ?? dirname(__DIR__, 3) . '/resources/OMML2MML.XSL';
        
        $this->initXslt();
    }

    /**
     * Mempersiapkan XSLT Processor
     */
    protected function initXslt(): void {
        if (!file_exists($this->xslPath)) {
            // Sebaiknya throw exception daripada gagal diam-diam, 
            // agar Anda tahu jika file resource hilang saat deployment.
            throw new RuntimeException("File XSLT OMML2MML tidak ditemukan di: {$this->xslPath}");
        }

        $xsl = new DOMDocument();
        // Libxml option untuk mengabaikan error parsing XSL bawaan yang kadang cerewet
        $xsl->load($this->xslPath, LIBXML_NOCDATA); 
        
        $this->xsltProc = new XSLTProcessor();
        $this->xsltProc->importStylesheet($xsl);
    }

    /**
     * Parse node MathML dan konversi ke format XML JATS.
     * @param DOMElement $mathNode Node OMML (m:oMath atau m:oMathPara)
     * @return DOMElement|null
     */
    public function parse(DOMElement $mathNode): ?DOMElement {
        if (!$this->xsltProc) return null;

        // 1. Buat DOM sementara dengan deklarasi namespace OMML secara eksplisit
        // Ini memastikan XSLT mengenali prefix 'm:' meskipun terlepas dari dokumen Word aslinya.
        $tempDom = new DOMDocument('1.0', 'UTF-8');
        $wrapper = $tempDom->createElementNS('http://schemas.openxmlformats.org/officeDocument/2006/math', 'm:mathWrapper');
        $tempDom->appendChild($wrapper);

        $importedNode = $tempDom->importNode($mathNode, true);
        $wrapper->appendChild($importedNode);

        // 2. Lakukan Transformasi
        $resultDom = $this->xsltProc->transformToDoc($tempDom);

        if ($resultDom && $resultDom->documentElement) {
            // Kadang hasil XSLT membungkus output. Kita cari elemen <mml:math> sesungguhnya.
            $mathElements = $resultDom->getElementsByTagNameNS('http://www.w3.org/1998/Math/MathML', 'math');
            
            if ($mathElements->length === 0) return null;

            // 3. Import hasil kembali ke DOM JATS utama
            $finalNode = $this->targetDom->importNode($mathElements->item(0), true);

            // 4. Deteksi apakah ini Inline atau Display formula berdasarkan tag asalnya
            // m:oMathPara = blok rumus (display-formula), m:oMath = rumus dalam teks (inline-formula)
            $localName = $mathNode->localName; // Akan bernilai 'oMath' atau 'oMathPara'
            $tagName = ($localName === 'oMathPara') ? 'disp-formula' : 'inline-formula';

            $formulaWrapper = $this->targetDom->createElement($tagName);
            $formulaWrapper->appendChild($finalNode);

            return $formulaWrapper;
        }

        return null;
    }
}