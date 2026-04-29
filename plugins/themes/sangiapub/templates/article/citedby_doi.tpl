{**
 * templates/article/citedby_doi.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article Cited by DOI -- Cited article component.
 *}
 
{** Citation DOI **}
<section class="SidePanel doi-cited u-margin-s-bottom details-44861495 u-js-hide">
    <details class="details-summary-2566262091 u-margin-s-bottom" open="">
        <summary class=" ">
            <header id="citing-articles-header" class="details-summary-label-617948308 side-panel-header">
                <div class="u-font-sans" type="button">
                    <span class="button-link-text">
                        <h3 class="section-title u-h4 u-font-sans-sang">Cited by <span class="citedby">0</span> articles <span class="fileSize u-show-inline-from-lg">DOI base by <span class="Wizdam">Wizdam</span></span> <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
                        </h3>
                    </span>
                </div>    			            
            </header>
        </summary>
        <div class="u-margin-m-top metrics-details">
            <div id="citing-articles">
                <ul class="citedby_crossref">
                    <li class="SidePanelItem article-citing">
                        <div class="sub-heading">
                            <h3 class="related-content-panel-list-entry-outline-padding text-s u-fonts-serif" id="citing-articles-article1-title">
                                <a class="anchor u-clamp-2-lines anchor-primary" href="/" title="Software defined network and graph neural network-based anomaly detection scheme for high speed networks"><span class="anchor-text-container"><span class="anchor-text"><span>Software defined network and graph neural network-based anomaly detection scheme for high speed networks</span></span></span>
                                </a>
                            </h3>
                            <div class="article-source ellipsis u-clr-grey6">
                                <div class="source">
                                    <span class="journal">Cyber Security and Applications, </span>
                                    <span class="edition">Volume 3, 2025, Article 100079</span>
                                </div>
                            </div>
                            <div class="authors ellipsis">
                                <span>Nama Penulis 1</span>, 
                                <span>Nama Penulis 2</span>
                            </div>
                        </div>
                        <div class="buttons"><a class="anchor anchor-primary anchor-icon-left anchor-with-icon" href="/" target="_blank" rel="nofollow" aria-describedby="cited-article-title"><svg focusable="false" viewBox="0 0 35 32" height="20" class="icon icon-pdf-multicolor"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg><span class="anchor-text-container"><span class="anchor-text">View PDF</span></span></a>
                        </div>
                    </li>
                </ul>
                <div id="citing-info" class="citing-info">
                    <span class="update-info">Updated: {$smarty.now|date_format:"%d %B %Y"}</span>
                </div>
                <button class="anchor button-link more-citedby-button u-margin-s-top button-link-primary button-link-icon-right" type="button">
                    <span class="button-link-text-container u-mr-8">
                        <span class="anchor-text button-link-text">Hide 3 articles</span>
                    </span>
                    <svg focusable="false" viewBox="0 0 92 128" height="20" class="icon-navigate icon-navigate-down u-flip-vertically"><path d="M1 51l7-7 38 38 38-38 7 7-45 45z"></path></svg>
                </button>
            </div>
        </div>
    </details>
</section>