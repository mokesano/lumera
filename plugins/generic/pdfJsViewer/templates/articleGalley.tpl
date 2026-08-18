{**
 * plugins/generic/pdfJsViewer/templates/articleGalley.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Embedded PDF viewer using pdf.js for article galleys.
 *}

{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}
{include file="$pluginTemplatePath/pdfViewer.tpl" pdfUrl=$pdfUrl}
