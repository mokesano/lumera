{**
 * plugins/generic/pdfJsViewer/templates/pdfViewer.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Embedded PDF viewer using pdf.js.
 *
 * [WIZDAM] Diperbarui total untuk pdf.js v6.2.108 (build legacy resmi
 * Mozilla, dari https://github.com/mozilla/pdf.js/releases). pdf.js
 * SEBELUMNYA hanya v1.0.907 (2015, ~10 tahun, banyak CVE parsing PDF
 * yang sudah diperbaiki di rilis-rilis setelahnya).
 *
 * pdf.js modern (bahkan varian "legacy") didistribusikan sebagai ES
 * module (.mjs) -- API level-rendah lama (global PDFJS.getDocument()
 * + render manual ke <canvas>, sebelumnya DIKOMENTARI/nonaktif di
 * berkas ini) TIDAK LAGI KOMPATIBEL begitu saja dengan format modul
 * baru. Solusinya justru lebih SEDERHANA dan lebih TANGGUH: embed
 * web/viewer.html APA ADANYA lewat iframe -- aplikasi viewer pdf.js
 * yang LENGKAP DAN MANDIRI (memuat modul/worker/CSS-nya sendiri),
 * tanpa perlu satu baris JS custom pun di sisi kita. ?file=... adalah
 * parameter resmi viewer.html untuk memuat PDF dari URL.
 *}

<div id="pdfCanvasContainer" class="galley_view">
	<iframe src="{$pluginUrl}/pdf.js/web/viewer.html?file={$pdfUrl|escape:'url'}" title="{translate key="article.pdf.download"|escape}" width="100%" height="100%" style="border:0; min-height:700px;" allowfullscreen webkitallowfullscreen></iframe>
</div>
