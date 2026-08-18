{**
 * templates/article/articleGalley.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * [FIX] Standalone article PDF galley view. Mirrors templates/issue/issueGalley.tpl
 * for articles: a genuinely standalone page (minimal page shell + the PDF
 * viewer + the generic site footer) -- NOT the full article/article.tpl
 * detail page (which additionally renders all article metadata and, most
 * relevantly, article/footer.tpl).
 *
 * Deliberately reuses the existing article/pdfViewer.tpl fragment (rather
 * than duplicating its PDFObject embed script here) so that
 * PdfJsViewerPlugin::_includeCallback()'s existing hook on
 * 'article/pdfViewer.tpl' continues to work unchanged, swapping in the
 * plugin's enhanced viewer (with its "Return to Article Details" bar) when
 * that plugin is enabled -- exactly as that plugin template was designed
 * to be used.
 *}
{include file="article/header.tpl"}

{include file="article/pdfViewer.tpl"}

{include file="common/footer.tpl"}
