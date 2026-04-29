<?php
declare(strict_types=1);

/**
 * @file TextParser.php
 * @version 1.5 (Deep Dive Version)
 * @brief Peningkatan kemampuan rekursif untuk menembus wrapper layout kompleks
 * seperti wp:anchor, wp:inline, dan v:shape di dalam Textbox.
 */

namespace Wizdam\JatsEngine\Parsers\Docx;

use DOMDocument;
use DOMElement;
use DOMXPath;

class TextParser {
    protected DOMDocument $targetDom;
    protected DOMXPath $xpath;
    protected MathParser $mathParser;
    protected ImageHandler $imageHandler;

    public function __construct(DOMDocument $dom, DOMXPath $xpath, MathParser $mp, ImageHandler $ih) {
        $this->targetDom = $dom; $this->xpath = $xpath;
        $this->mathParser = $mp; $this->imageHandler = $ih;
    }

    public function isHeading(DOMElement $pNode): ?int {
        $outline = $this->xpath->query('w:pPr/w:outlineLvl/@w:val', $pNode)->item(0);
        if ($outline) return ((int) $outline->nodeValue) + 1;
        $style = $this->xpath->query('w:pPr/w:pStyle/@w:val', $pNode)->item(0);
        if ($style && preg_match('/[Hh]ead(ing)?\s*([1-6])/i', $style->nodeValue, $m)) return (int)$m[2];
        return null;
    }

    public function parseContent(DOMElement $container, DOMElement $target): void {
        $this->scanChildren($container, $target);
    }

    /**
     * FUNGSI REKURSIF "DEEP DIVE"
     * Menjelajahi setiap kemungkinan wrapper di Word untuk menemukan konten.
     */
    protected function scanChildren(DOMElement $node, DOMElement $targetNode): void {
        if (!$node->hasChildNodes()) return;

        foreach ($node->childNodes as $child) {
            if (!($child instanceof DOMElement)) continue;
            $name = $child->localName; // Gunakan localName agar lebih robust terhadap prefix namespace

            // 1. KONTEN UTAMA
            if ($name === 'r') { // w:r
                $this->parseRun($child, $targetNode);
            } elseif ($name === 'oMath') { // m:oMath
                if ($m = $this->mathParser->parse($child)) $targetNode->appendChild($m);
            }
            
            // 2. KONTAINER GAMBAR & OBJEK (Langsung coba ekstrak)
            elseif (in_array($name, ['drawing', 'pict', 'object'])) {
                $this->extractImage($child, $targetNode);
                // DAN tetap scan dalamnya, siapa tahu ada textbox bersarang
                $this->scanChildren($child, $targetNode);
            }

            // 3. WRAPPER LAYOUT KOMPLEKS (Kunci untuk screenshot Anda!)
            // Tag-tag ini sering membungkus gambar di layout yang rumit.
            // Kita WAJIB menyelami isinya.
            elseif (in_array($name, [
                'txbxContent', // Isi Textbox
                'textbox',     // VML Textbox
                'anchor',      // Floating object wrapper (wp:anchor)
                'inline',      // Inline object wrapper (wp:inline)
                'group',       // VML Grouping
                'shape',       // VML Shape wrapper
                'graphic',     // DrawingML container
                'graphicData', // DrawingML data container
                'AlternateContent', // Wrapper untuk kompatibilitas (grafik/chart)
                'Choice', 'Fallback' // Anak dari AlternateContent
            ])) {
                $this->scanChildren($child, $targetNode);
            }
            
            // 4. FALLBACK untuk tag w:p (paragraf) yang nyasar di dalam textbox
            elseif ($name === 'p') {
                // Jika ketemu paragraf di dalam textbox, proses isinya tapi jangan buat tag <p> baru
                // karena kita sudah di dalam tag <p> induk di JATS.
                $this->scanChildren($child, $targetNode);
            }
        }
    }

    protected function parseRun(DOMElement $run, DOMElement $targetNode): void {
        $rPr = $run->getElementsByTagNameNS('*', 'rPr')->item(0);
        $isBold = $rPr && $rPr->getElementsByTagNameNS('*', 'b')->length > 0;
        $isItalic = $rPr && $rPr->getElementsByTagNameNS('*', 'i')->length > 0;
        $vert = $rPr ? $rPr->getElementsByTagNameNS('*', 'vertAlign')->item(0) : null;
        $vVal = $vert ? $vert->getAttribute('w:val') : '';

        foreach ($run->childNodes as $child) {
            if (!($child instanceof DOMElement)) continue;
            $name = $child->localName;

            if ($name === 't') {
                $txt = $this->targetDom->createTextNode($child->nodeValue);
                if ($isItalic) { $w = $this->targetDom->createElement('italic'); $w->appendChild($txt); $txt = $w; }
                if ($isBold) { $w = $this->targetDom->createElement('bold'); $w->appendChild($txt); $txt = $w; }
                if ($vVal === 'superscript') { $w = $this->targetDom->createElement('sup'); $w->appendChild($txt); $txt = $w; }
                if ($vVal === 'subscript') { $w = $this->targetDom->createElement('sub'); $w->appendChild($txt); $txt = $w; }
                $targetNode->appendChild($txt);
            } 
            // Cek gambar yang inline di dalam Run
            elseif (in_array($name, ['drawing', 'pict', 'object'])) {
                $this->extractImage($child, $targetNode);
            }
            // Tangani wrapper lain yang mungkin ada di dalam run
            elseif ($name === 'AlternateContent') {
                 $this->scanChildren($child, $targetNode);
            }
        }
    }

    protected function extractImage(DOMElement $node, DOMElement $targetNode): void {
        // Cari r:embed di a:blip (DrawingML) ATAU r:id di v:imagedata (VML)
        $blips = $node->getElementsByTagNameNS('*', 'blip');
        $imgData = $node->getElementsByTagNameNS('*', 'imagedata');
        
        $rId = null;
        if ($blips->length > 0) $rId = $blips->item(0)->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed');
        elseif ($imgData->length > 0) $rId = $imgData->item(0)->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');

        if ($rId && $path = $this->imageHandler->processImage($rId)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
            // Jika formatnya masih emf/wmf, tandai sebagai octet-stream agar browser tidak bingung
            if (in_array($ext, ['emf', 'wmf'])) $mime = 'application/octet-stream';

            $tagName = in_array($targetNode->localName, ['p', 'td', 'th', 'title']) ? 'inline-graphic' : 'graphic';
            
            if ($tagName === 'graphic') {
                 $fig = $this->targetDom->createElement('fig');
                 $el = $this->targetDom->createElement($tagName);
                 $fig->appendChild($el);
                 $targetNode->appendChild($fig);
            } else {
                 $el = $this->targetDom->createElement($tagName);
                 $targetNode->appendChild($el);
            }
            $el->setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', $path);
            $el->setAttribute('mimetype', $mime);
        }
    }
}