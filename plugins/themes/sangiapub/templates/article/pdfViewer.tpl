{**
 * @file templates/article/pdfViewer.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * Embedded PDF viewer.
 *
 * [WIZDAM] Ditulis ulang total. Versi sebelumnya bergantung pada PDFObject
 * v1.2 (2011), yang mendeteksi kemampuan PDF browser lewat
 * navigator.mimeTypes/navigator.plugins -- API NPAPI yang SUDAH DIHAPUS
 * dari semua browser modern (Chrome sejak ~2015, Firefox ~2017). Ketika
 * deteksi gagal (SELALU gagal di browser modern), embed() langsung
 * return false TANPA merender apa pun -- pengguna cuma melihat tombol
 * download, terlepas dari plugin PdfJsViewer/GoogleViewer aktif atau
 * tidak. Ini BUKAN soal ketergantungan aktivasi plugin -- viewer bawaan
 * ini SEHARUSNYA mandiri, dan sekarang benar-benar mandiri.
 *
 * Pendekatan baru: <iframe> langsung ke file PDF, TANPA deteksi
 * kemampuan apa pun. Ini yang direkomendasikan MDN & dipraktikkan
 * seluruh web modern -- SEMUA browser mainstream (Chrome, Firefox, Edge,
 * Safari) sudah merender PDF secara native di dalam iframe sejak
 * bertahun-tahun, tanpa perlu plugin/deteksi JS sama sekali.
 *}

{* target="_parent" untuk iPhone, yang bermasalah dengan scroll kalau tidak. *}
<div id="pdfDownloadLinkContainer" class="header_view">
	<a class="action pdf" id="pdfDownloadLink" target="_parent" href="{url op="download" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}"><span class="label">{translate key="article.pdf.download"}</span></a>
</div>

{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}

<div id="inlinePdfResizer">
	<div id="inlinePdf" class="ui-widget-content">
		{* [WIZDAM] iframe langsung -- tidak ada deteksi plugin, tidak ada
		   fallback JS yang bisa gagal senyap. title="" untuk aksesibilitas
		   pembaca layar. loading="lazy" supaya tidak memblokir render
		   halaman utama saat PDF berukuran besar. *}
		<iframe id="pdfObject" src="{$pdfUrl|escape}" title="{translate key="article.pdf.download"|escape}" width="100%" height="100%" style="border:0; min-height:600px;" loading="lazy" allowfullscreen webkitallowfullscreen></iframe>
	</div>
</div>
<p>
	<a class="action" href="#" id="fullscreenShow">{translate key="common.fullscreen"}</a>
	<a class="action" href="#" id="fullscreenHide">{translate key="common.fullscreenOff"}</a>
</p>
<div style="clear: both;"></div>

<script type="text/javascript">
<!--
{literal}
	$(document).ready(function(){
		// [WIZDAM] Fullscreen tetap didukung lewat toggle CSS/JS sederhana,
		// TANPA bergantung pada berhasil-tidaknya embed (iframe SELALU
		// berhasil dirender oleh browser, jadi tombol ini aman ditampilkan
		// langsung, tidak perlu digerbangi kondisi embed() seperti dulu).
		$('#fullscreenShow').show();
		$("#inlinePdfResizer").resizable({ containment: 'parent', handles: 'se' });
	});
{/literal}
// -->
</script>
