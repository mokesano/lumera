{**
 * plugins/generic/pdfJsViewer/templates/articleGalley.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * Embedded PDF viewer using pdf.js for article galleys.
 *}

{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}
{include file="$pluginTemplatePath/pdfViewer.tpl" pdfUrl=$pdfUrl}
