{**
 * @file templates/article/pdfViewer.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * Embedded PDF viewer.
 *}

{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}

<div id="pdfCanvasContainer" class="galley_view">
	<iframe id="pdfObject" src="{$pdfUrl|escape}" title="{translate key="article.pdf.download"|escape}" allowfullscreen webkitallowfullscreen></iframe>
</div>
