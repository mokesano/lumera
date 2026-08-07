{**
 * templates/article/citedby_doi.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera
 * Distributed under the GNU GPL v3.
 *
 * Article Cited by DOI -- Cited article component.
 *
 * [DIPERBAIKI] Sebelumnya HTML mock statis, diisi ulang oleh JavaScript
 * (WizdamCitedby.js) lewat AJAX ke /api/citedby (proxy .htaccess ke skrip
 * mandiri doi_citation.php). Sekarang data dikirim LANGSUNG dari backend
 * (CitationFetcherService) lewat variabel Smarty $citingArticles /
 * $citationCount -- tidak ada lagi AJAX/proxy untuk render awal.
 *
 * Partial ini dipakai bersama oleh:
 * - article/heading.tpl (panel 7 kutipan terbaru di halaman artikel)
 * - article/metrics.tpl (daftar LENGKAP semua kutipan)
 * Pemanggil menentukan ukuran list lewat $citingArticles yang dikirim
 * (partial ini sendiri tidak membatasi jumlah).
 *}

{if $citingArticles}
<section class="SidePanel doi-cited u-margin-s-bottom details-44861495">
    <details class="details-summary-2566262091 u-margin-s-bottom" open="">
        <summary class=" ">
            <header id="citing-articles-header" class="details-summary-label-617948308 side-panel-header">
                <div class="u-font-sans" type="button">
                    <span class="button-link-text">
                        <h3 class="section-title u-h4 u-font-sans-sang">Cited by <span class="citedby">{$citationCount|default:0}</span> articles <span class="fileSize u-show-inline-from-lg">DOI base by <span class="Wizdam">Wizdam</span></span> <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
                        </h3>
                    </span>
                </div>
            </header>
        </summary>
        <div class="u-margin-m-top metrics-details">
            <div id="citing-articles">
                {if $citingArticles|@count > 0}
                <ul class="citedby_crossref">
                    {foreach from=$citingArticles item=citation}
                    <li class="SidePanelItem article-citing">
                        <div class="sub-heading">
                            <h3 class="related-content-panel-list-entry-outline-padding text-s u-fonts-serif">
                                {if $citation.url}
                                <a class="anchor u-clamp-2-lines anchor-primary" href="{$citation.url|escape}" target="_blank" rel="nofollow noopener" title="{$citation.title|escape}"><span class="anchor-text-container"><span class="anchor-text"><span>{$citation.title|escape}</span></span></span>
                                </a>
                                {else}
                                <span>{$citation.title|escape}</span>
                                {/if}
                            </h3>
                            {if $citation.container || $citation.year}
                            <div class="article-source ellipsis u-clr-grey6">
                                <div class="source">
                                    {if $citation.container}<span class="journal">{$citation.container|escape}, </span>{/if}
                                    {if $citation.year}<span class="edition">{$citation.year|escape}</span>{/if}
                                </div>
                            </div>
                            {/if}
                            {if $citation.authors && $citation.authors|@count > 0}
                            <div class="authors ellipsis">
                                {foreach from=$citation.authors item=author name=citeAuthorLoop}
                                <span>{$author.given|escape} {$author.family|escape}</span>{if !$smarty.foreach.citeAuthorLoop.last}, {/if}
                                {/foreach}
                            </div>
                            {/if}
                        </div>
                    </li>
                    {/foreach}
                </ul>
                <div id="citing-info" class="citing-info">
                    <span class="update-info">Updated: {$smarty.now|date_format:"%d %B %Y"}</span>
                </div>
                {else}
                <p class="no-citations">No citing articles found yet.</p>
                {/if}
            </div>
        </div>
    </details>
</section>
{/if}
