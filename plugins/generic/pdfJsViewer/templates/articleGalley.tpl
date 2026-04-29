{**
 * plugins/generic/pdfJsViewer/templates/articleGalley.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Embedded PDF viewer using pdf.js for article galleys.
 *}
<div id="pdfDownloadLinkContainer" class="header_view">
    <a class="return" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}"><span class="pkp_screen_reader">Return to Article Details</span></a>
    <a class="title" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
	<a class="action pdf download" id="pdfDownloadLink" target="_parent" href="{url op="download" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}"><span class="label">{translate key="article.pdf.download"}</span></a>
</div>

{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}
{include file="$pluginTemplatePath/pdfViewer.tpl" pdfUrl=$pdfUrl}

<div class="u-hide">