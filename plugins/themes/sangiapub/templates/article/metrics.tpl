{**
 * templates/article/metrics.tpl
 * Custom metrics page for article statistics in OJS 2.4.8.5
 * Compatible with PHP 7.4+
 *}

{strip}
{assign var="pageTitle" value="article.articleMetrics"}
{assign var="pageDisplayed" value="article.metrics"}
{include file="article/header-metrics.tpl"}

    <div class="u-display-flex u-justify-content-space-between">
        <nav class="c-metrics-identifiers" aria-label="metrics-identifiers" data-test="metrics-identifiers">
            <p class="c-article-identifiers__label">Metrics</p>
            <p class="c-article-metrics__updated">Last updated: {$statsLastUpdated}</p>
        </nav>
        <ul class="app-article-metrics-stat">
            <li class="app-article-metrics-stat__item">{$views|default:0}<span class="app-article-metrics-stat__subitem">Views</span></li>
            <li class="app-article-metrics-stat__item">{$downloads|default:0}<span class="app-article-metrics-stat__subitem">Downloads</span></li>
            <li class="app-article-metrics-stat__item">0<span class="app-article-metrics-stat__subitem">Citations</span></li>
            <li class="app-article-metrics-stat__item">516<span class="app-article-metrics-stat__subitem">Altmetric</span></li>
            <li class="app-article-metrics-stat__item">79<span class="app-article-metrics-stat__subitem">Mentions</span></li>
        </ul>
    </div>
    <main>
        <div data-test="article-metrics-wrapper">
            <h1 class="u-hide">Metrics</h1>
            <h2 class="c-article-metrics__title u-h2">
                <a data-track="click" data-track-action="back to article" data-track-label="link" data-track-category="metrics" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
            </h2>
            <div class="app-article-metrics u-mb-24" data-test="link-metrics">
                <section class="app-article-metrics-container">
                    <div class="app-article-metrics-box">
                        <div class="app-article-metrics-box__main">
                            <h2 class="u-mb-16 u-mt-0 c-article-metrics-heading"><span class="app-article-metrics-box__icon-container"><svg class="app-article-metrics-box-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="M15.59 1a1 1 0 0 1 .706.291l5.41 5.385a1 1 0 0 1 .294.709v13.077c0 .674-.269 1.32-.747 1.796a2.549 2.549 0 0 1-1.798.742H15a1 1 0 0 1 0-2h4.455a.549.549 0 0 0 .387-.16.535.535 0 0 0 .158-.378V7.8L15.178 3H5.545a.543.543 0 0 0-.538.451L5 3.538v8.607a1 1 0 0 1-2 0V3.538A2.542 2.542 0 0 1 5.545 1h10.046ZM8 13c2.052 0 4.66 1.61 6.36 3.4l.124.141c.333.41.516.925.516 1.459 0 .6-.232 1.178-.64 1.599C12.666 21.388 10.054 23 8 23c-2.052 0-4.66-1.61-6.353-3.393A2.31 2.31 0 0 1 1 18c0-.6.232-1.178.64-1.6C3.34 14.61 5.948 13 8 13Zm0 2c-1.369 0-3.552 1.348-4.917 2.785A.31.31 0 0 0 3 18c0 .083.031.161.09.222C4.447 19.652 6.631 21 8 21c1.37 0 3.556-1.35 4.917-2.785A.31.31 0 0 0 13 18a.32.32 0 0 0-.048-.17l-.042-.052C11.553 16.348 9.369 15 8 15Zm0 1a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z"></path></svg></span>Accesses</h2>
                            <p>Accesses is an approximate count of unique views and downloads. This number can fluctuate depending on multiple factors.</p>
                            <p>We update counts daily.</p>
                        </div>
                        <div class="app-article-metrics-box__side">
                            <p class="app-article-metrics-count">{math equation="x + y" x=$views y=$downloads}<span class="app-article-metrics-count_text">Accesses</span></p>
                        </div>
                    </div>
                </section>
                <section class="app-article-metrics-container">
                    <div class="app-article-metrics-box">
                        <div class="app-article-metrics-box__main">
                            <h2 class="u-mb-16 u-mt-0 c-article-metrics-heading"><span class="app-article-metrics-box__icon-container"><svg class="app-article-metrics-box-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="M15.59 1a1 1 0 0 1 .706.291l5.41 5.385a1 1 0 0 1 .294.709v13.077c0 .674-.269 1.32-.747 1.796a2.549 2.549 0 0 1-1.798.742h-5.843a1 1 0 1 1 0-2h5.843a.549.549 0 0 0 .387-.16.535.535 0 0 0 .158-.378V7.8L15.178 3H5.545a.543.543 0 0 0-.538.451L5 3.538v8.607a1 1 0 0 1-2 0V3.538A2.542 2.542 0 0 1 5.545 1h10.046ZM5.483 14.35c.197.26.17.62-.049.848l-.095.083-.016.011c-.36.24-.628.45-.804.634-.393.409-.59.93-.59 1.562.077-.019.192-.028.345-.028.442 0 .84.158 1.195.474.355.316.532.716.532 1.2 0 .501-.173.9-.518 1.198-.345.298-.767.446-1.266.446-.672 0-1.209-.195-1.612-.585-.403-.39-.604-.976-.604-1.757 0-.744.11-1.39.33-1.938.222-.549.49-1.009.807-1.38a4.28 4.28 0 0 1 .992-.88c.07-.043.148-.087.232-.133a.881.881 0 0 1 1.121.245Zm5 0c.197.26.17.62-.049.848l-.095.083-.016.011c-.36.24-.628.45-.804.634-.393.409-.59.93-.59 1.562.077-.019.192-.028.345-.028.442 0 .84.158 1.195.474.355.316.532.716.532 1.2 0 .501-.173.9-.518 1.198-.345.298-.767.446-1.266.446-.672 0-1.209-.195-1.612-.585-.403-.39-.604-.976-.604-1.757 0-.744.11-1.39.33-1.938.222-.549.49-1.009.807-1.38a4.28 4.28 0 0 1 .992-.88c.07-.043.148-.087.232-.133a.881.881 0 0 1 1.121.245Z"></path></svg></span>Citations</h2>
                            <p>Citation counts are provided by Dimensions and depend on their data availability. Counts will update daily, once available.</p>
                        </div>
                        <div class="app-article-metrics-box__side">
                            <p class="app-article-metrics-count">0<span class="app-article-metrics-count_text">Citations</span></p>
                        </div>
                    </div>
                </section>
            </div>
            <section class="app-article-metrics-container u-mb-24">
                <div class="app-article-metrics-box">
                    <div class="app-article-metrics-box__main">
                        <h2 class="u-mb-16 u-mt-0 c-article-metrics-heading"><span class="app-article-metrics-box__icon-container"><svg class="app-article-metrics-box-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="m9.452 1.293 5.92 5.92 2.92-2.92a1 1 0 0 1 1.415 1.414l-2.92 2.92 5.92 5.92a1 1 0 0 1 0 1.415 10.371 10.371 0 0 1-10.378 2.584l.652 3.258A1 1 0 0 1 12 23H2a1 1 0 0 1-.874-1.486l4.789-8.62C4.194 9.074 4.9 4.43 8.038 1.292a1 1 0 0 1 1.414 0Zm-2.355 13.59L3.699 21h7.081l-.689-3.442a10.392 10.392 0 0 1-2.775-2.396l-.22-.28Zm1.69-11.427-.07.09a8.374 8.374 0 0 0 11.737 11.737l.089-.071L8.787 3.456Z"></path></svg></span>Citedby</h2>
                        <div class="c-article-metrics__section" data-test="metrics-mentions">
                            <div class="c-article-metrics__body">
                                <div class="c-article-metrics__section--left">
                    <ul class="u-list-reset">
                        <li>
                            <div class="c-card-metrics">
                                <div class="c-card-metrics__main">
                                    <h3 class="c-card-metrics__heading">
                                        <a href="https://quantum-server-materials.blogspot.com/2025/10/composite-metal-foams-endure-over-1.html" data-track="click" data-track-action="view news article" data-track-label="link" data-track-category="metrics">🔥 Composite Metal Foams Endure Over 1 Million Cycles at 400 °C and 600 °C — A Game Changer for Extreme Environments</a>
                                    </h3>
                                    <div>
                                        <div class="c-card-metrics__authors">Quantum Server Materials</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="app-article-metrics-box__side app-article-metrics-box__side-top">
                        <p>This list highlights individual mainstream news articles and blogs that cite the article.</p>
                        <p>Not all news and blogs link to articles in a way that Altmetric can pick up, so they are not representative of all media.</p>
                        <p>Altmetric are responsible for the curation of this list and provide updates hourly.</p>
                    </div>
                </div>
            </section>
                
            {if $doi}
            <section class="app-article-metrics-container u-mb-24" data-test="altmetric-score">
                <div class="app-article-metrics-box app-article-metrics-box--wide">
                    <div class="app-article-metrics-box__main app-article-metrics-box--wide__main">
                        <h2 class="u-mb-16 u-mt-0 c-article-metrics-heading"><span class="app-article-metrics-box__icon-container"><svg class="app-article-metrics-box-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="M12 1c5.978 0 10.843 4.77 10.996 10.712l.004.306-.002.022-.002.248C22.843 18.23 17.978 23 12 23 5.925 23 1 18.075 1 12S5.925 1 12 1Zm-1.726 9.246L8.848 12.53a1 1 0 0 1-.718.461L8.003 13l-4.947.014a9.001 9.001 0 0 0 17.887-.001L16.553 13l-2.205 3.53a1 1 0 0 1-1.735-.068l-.05-.11-2.289-6.106ZM12 3a9.001 9.001 0 0 0-8.947 8.013l4.391-.012L9.652 7.47a1 1 0 0 1 1.784.179l2.288 6.104 1.428-2.283a1 1 0 0 1 .722-.462l.129-.008 4.943.012A9.001 9.001 0 0 0 12 3Z"></path></svg></span>Altmetric</h2>
                        <div class="app-article-metrics-box__side c-article-metrics__altmetric-donut app-article-metrics-altmetric altmetric-large-donut-wrapper">
                            <div class="altmetric-embed"
                                data-badge-type="medium_donut"
                                data-badge-details="right"
                                data-doi="{$doi|escape}">
                            </div>
                            <div class="app-article-metrics-altmetric u-hide">
                                <div class="c-article-metrics__image">
                                    <img alt="Altmetric score 114" src="https://badges.altmetric.com/?size=180&amp;score=114&amp;types=mbtttttu" style="width:120px;height:120px;">
                                </div>
                                <div class="c-article-metrics__legend">
                    <ul class="u-list-reset" data-altmetric-key="" data-test="metrics-counts">
                        <li class="u-list-reset">
                            <span class="c-article-metrics__altmetric-key c-article-metrics__altmetric-key--twitter"></span>
                            <span>29 tweeters</span>
                        </li>
                        <li class="u-list-reset">
                            <span class="c-article-metrics__altmetric-key c-article-metrics__altmetric-key--blogs"></span>
                            <span>2 blogs</span>
                        </li>
                        <li class="u-list-reset">
                            <span class="c-article-metrics__altmetric-key c-article-metrics__altmetric-key--news"></span>
                            <span>12 news outlets</span>
                        </li>
                        <li class="u-list-reset">
                            <span class="c-article-metrics__altmetric-key c-article-metrics__altmetric-key--mendeley"></span>
                            <span>2 Mendeley</span>
                        </li>
                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="app-article-metrics-box__side app-article-metrics-box--wide__side">
                        <p>Altmetric calculates a score based on the online attention an article gets — the higher the score, the more online attention an article has received.</p>
                        <p>Surrounding the score can be 1 or more colours. Each colour represents a different type of online attention, like social media or news outlets.</p>
                        <p>Older articles have had more time to get noticed, so Altmetric provides context data for articles of a similar age.</p>
                        <p class="u-hide">This article is in the 98<sup>th</sup> percentile (ranked 3,254<sup>th</sup>) of the 311,458 tracked articles of a similar age in all journals and the 87<sup>th</sup> percentile (ranked 1<sup>st</sup>) of the 8 tracked articles of a similar age in <i>Journal of Materials Science</i>.</p>
                        <p>View more on <a href="//altmetric.com/details/doi/{$doi|escape}" data-track="click" data-track-action="view altmetric" data-track-label="link" data-track-category="metrics" target="_blank">Altmetric</a>.</p>
                    </div>
                </div>
            </section>
            {/if}
            
            <section class="app-article-metrics-container u-mb-24">
                <div class="app-article-metrics-box">
                    <div class="app-article-metrics-box__main">
                        <h2 class="u-mb-16 u-mt-0 c-article-metrics-heading"><span class="app-article-metrics-box__icon-container"><svg class="app-article-metrics-box-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="m9.452 1.293 5.92 5.92 2.92-2.92a1 1 0 0 1 1.415 1.414l-2.92 2.92 5.92 5.92a1 1 0 0 1 0 1.415 10.371 10.371 0 0 1-10.378 2.584l.652 3.258A1 1 0 0 1 12 23H2a1 1 0 0 1-.874-1.486l4.789-8.62C4.194 9.074 4.9 4.43 8.038 1.292a1 1 0 0 1 1.414 0Zm-2.355 13.59L3.699 21h7.081l-.689-3.442a10.392 10.392 0 0 1-2.775-2.396l-.22-.28Zm1.69-11.427-.07.09a8.374 8.374 0 0 0 11.737 11.737l.089-.071L8.787 3.456Z"></path></svg></span>Mentions</h2>
                        <div class="c-article-metrics__section" data-test="metrics-mentions">
                            <div class="c-article-metrics__body">
                                <div class="c-article-metrics__section--left">
                    <ul class="u-list-reset">
                        <li>
                            <div class="c-card-metrics">
                                <div class="c-card-metrics__main">
                                    <h3 class="c-card-metrics__heading">
                                        <a href="http://ct.moreover.com/?a=57875082082&amp;p=1pl&amp;v=1&amp;x=OBmb2A1OzstnqIMHK6_LKA" data-track="click" data-track-action="view news article" data-track-label="link" data-track-category="metrics">Some Like It Hot: Composite Metal Foam Proves Resilient Against High Stresses at High Temperatures</a>
                                    </h3>
                                    <div>
                                        <div class="c-card-metrics__authors">AlphaGalileo</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="c-card-metrics">
                                <div class="c-card-metrics__main">
                                    <h3 class="c-card-metrics__heading">
                                        <a href="https://tiisys.com/blog/2025/10/08/post-177312/" data-track="click" data-track-action="view news article" data-track-label="link" data-track-category="metrics">高温・高応力下でも耐性のある金属フォーム材料（Composite Metal Foam Proves Resilient Against High Stresses at High Temperatures）</a>
                                    </h3>
                                    <div>
                                        <div class="c-card-metrics__authors">Tii技術情報</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="c-card-metrics">
                                <div class="c-card-metrics__main">
                                    <h3 class="c-card-metrics__heading">
                                        <a href="http://ct.moreover.com/?a=57899530386&amp;p=1pl&amp;v=1&amp;x=tZNmIz7Rvb4u5gJUznMnZg" data-track="click" data-track-action="view news article" data-track-label="link" data-track-category="metrics">У США винайшли практично незнищенну металеву піну для ядерних реакторів та інших екстремальних умов</a>
                                    </h3>
                                    <div>
                                        <div class="c-card-metrics__authors">Business Information Network Ukraine</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="c-card-metrics">
                                <div class="c-card-metrics__main">
                                    <h3 class="c-card-metrics__heading">
                                        <a href="https://quantum-server-materials.blogspot.com/2025/10/composite-metal-foams-endure-over-1.html" data-track="click" data-track-action="view news article" data-track-label="link" data-track-category="metrics">🔥 Composite Metal Foams Endure Over 1 Million Cycles at 400 °C and 600 °C — A Game Changer for Extreme Environments</a>
                                    </h3>
                                    <div>
                                        <div class="c-card-metrics__authors">Quantum Server Materials</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="app-article-metrics-box__side app-article-metrics-box__side-top">
                        <p>This list highlights individual mainstream news articles and blogs that cite the article.</p>
                        <p>Not all news and blogs link to articles in a way that Altmetric can pick up, so they are not representative of all media.</p>
                        <p>Altmetric are responsible for the curation of this list and provide updates hourly.</p>
                    </div>
                </div>
            </section>
        </div>
        <div class="c-article-figure-button-container hide-print">
            <a class="c-article__pill-button" data-test="back-link" data-track="click" data-track-action="back to article" data-track-label="button" data-track-category="metric" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}"><svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z" transform="rotate(180 8 8)"></path></svg>
                <span>Back to article page</span>
            </a>
        </div>
    </main>
</div>
{/strip}

{include file="article/footer.tpl"}
