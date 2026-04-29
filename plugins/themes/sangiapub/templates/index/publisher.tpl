{**
 * templates/index/site.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site index.
 *
 *}
{strip}
{if $siteTitle}
	{assign var="pageTitleTranslated" value=$siteTitle}
{/if}
{assign var="pageDisplayed" value="site"}
{include file="common/header-parts/header-site.tpl"}
{/strip}

{** Kode Statistik jurnal secara global perlu perbaikan **}
{** {include file="common/featured/site_stats.tpl"} **}

{include file="trends/trendsArticlesPublisher.tpl"}

<div id="_sangiaJOUR" class="cms-common cms-article default-table">
    <p class="taxonomy"></p>
    <div class="section-header">
        <h2 id="c14965756">Agriculture and Marine Science</h2>
        <div class="cms-richtext">
            <p>You can find more journals on our&nbsp;<a href="#_sangiaJOUR" class="is-external internal">Agriculture and Marine Science</a> pages, and&nbsp;in the <a href="#_sangiaJOUR" class="is-external internal">A-Z index</a> below.</p>
        </div>
    </div>
</div>

{if $useAlphalist}
<p class="u-hide column">{foreach from=$alphaList item=letter}<a href="{url searchInitial=$letter sort="title"}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a href="{url}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></p>

<div class="c-jump">
    <p class="describe italic u-font-sans">Click the alphabet to chose name of journal(s)</p>
    <span class="c-jump-navigation">{foreach from=$alphaList item=letter}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url searchInitial=$letter sort="title"}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></span>
</div>            
{/if}

        </div>
    </div>
</div>

<div class="cms-container cms-container-tiles cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12">
            <div class="cms-tile-row">
                <div class="row">
                    {iterate from=journals item=journal}
                    <div class="columns small-6 medium-3 large-3 cms-tile-row-medium">
                        <div class="">
                            {if $site->getSetting('showThumbnail')}
                            {assign var="displayJournalThumbnail" value=$journal->getLocalizedSetting('journalThumbnail')}
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" {if $displayJournalThumbnail && is_array($displayJournalThumbnail)}style="background-image: url({$journalFilesPath}{$journal->getId()}/{$displayJournalThumbnail.uploadName|escape:"url"});"{else}style="background-image: url(//assets.sangia.org/img/img-default.jpg);"{/if}>
                                <a class="tile-toggle" href="{url journal=$journal->getPath()}">
                                    {assign var="altText" value=$journal->getLocalizedSetting('journalThumbnailAltText')}
                                    <div class="tile-detail">{if $altText != ''}{$altText|escape}{else}{translate key="common.pageHeaderLogo.altText"}{/if}</div>
                                    <div class="tile-bottom">
                                        {if $site->getSetting('showTitle')}
                                        <h3 class="">{$journal->getLocalizedTitle()|escape}</h3>
                                        {/if}
                                    </div>
                                </a>
                            </div>
                            {/if}
                        </div>
                    </div>
                    {/iterate}
                    {if $journals->wasEmpty()}
                    <div class="cms-common cms-article cms-tile-row-medium">
                        <p class="taxonomy"></p>
                        <div class="section-header">
                            <h2 id="c14965756">Journal Results</h2>
                            <div class="cms-richtext">
                                <p>{translate key="site.noJournals"}</p>
                            </div>
                        </div>
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="u-hide cms-container cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-common cms-article default-table">
                <div class="cms-richtext">
                    <div id="journalListPageInfo" class=" ">{page_info iterator=$journals}</div>
                    <div id="journalListPageLinks">{page_links anchor="journals" name="journals" iterator=$journals}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cms-container cms-highlight-2">

{iterate from=journals item=journal}
	{if $site->getSetting('showTitle')}
		<h3>{$journal->getLocalizedTitle()|escape}</h3>
	{/if}
	{if $site->getSetting('showDescription')}
		{if $journal->getLocalizedDescription()}
			<p>{$journal->getLocalizedDescription()|nl2br}</p>
		{/if}
	{/if}
	<p><a href="{url journal=$journal->getPath()}" class="action">{translate key="site.journalView"}</a> | <a href="{url journal=$journal->getPath() page="issue" op="current"}" class="action">{translate key="site.journalCurrent"}</a> | <a href="{url journal=$journal->getPath() page="user" op="register"}" class="action">{translate key="site.journalRegister"}</a></p>
{/iterate}

    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-common cms-article default-table">
                <p class="taxonomy"></p>
                <div class="section-header">
                    <h2 id="c15046410">Journals A-Z Index</h2>
                </div>
                <div class="cms-richtext"><p>Browse all of our journals by subject area</p></div>
                {** include file="search/categories.tpl" **}
            </div>
        </div>
    </div>
</div>

<div id="_sangia" class="cms-container cms-container-tiles cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-tile-row">
                <div class="row">
                    <div class="columns  small-12 medium-4 large-4 cms-tile-row-medium">
                        <div id="id5f" class="">
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" style="background-image: url(//assets.sangia.org/static/img/Journals+Impact+Report.jpg);">
                                <a class="tile-toggle" href="//sinta.kemdikbud.go.id/journals" target="_blank">
                                    <div class="tile-detail"></div>
                                    <div class="tile-bottom"><h3 class="">Journal Impact Report Indonesia</h3></div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="columns  small-12 medium-4 large-4 cms-tile-row-medium">
                        <div id="id60" class="">
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" style="background-image: url(//journals.sangia.org/public/journals/1/cover_issue_48_en_US.jpg?as=webp);">
                                <a class="tile-toggle" href="//journals.sangia.org/ISLE" target="_blank">
                                    <div class="tile-detail">
                                        <div class="cms-richtext"><p>Our open access online journal</p></div>
                                    </div>
                                    <div class="tile-bottom"><h3 class="">Akuatikisle: Jurnal Akuakultur, Pesisir dan Pulau-Pulau Kecil </h3></div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="columns  small-12 medium-4 large-4 cms-tile-row-medium">
                        <div id="id61" class="">
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" style="background-image: url(//assets.sangia.org/static/img/Journals_+Why+Publish+With+Us_.jpg);">
                                <a class="tile-toggle" href="#_sangia">
                                    <div class="tile-detail"><div class="cms-richtext"><p>Here's why you should submit your work to Sangia Journals.</p></div></div>
                                    <div class="tile-bottom"><h3 class="">Want to publish an article?</h3></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cms-container cms-container-grid cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-grid-collection">
                <h2 id="c10046798">Stay informed</h2>
                <div class="cms-collection-list">
                    <ul class="small-block-grid-1  medium-block-grid-3">
                        <li>
                            <div id="id63" class="">
                                <a class="cms-teaser-text" href="https://twitter.com/SangiaNews" target="_blank">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id1" style="">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">1</div>
                                        <h3>Follow @SangiaNews</h3>
                                        <p>Follow Sangia Research & Comment</p>
                                    </div>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div id="id64" class="">
                                <a class="cms-teaser-text" href="//www.facebook.com/sangiapublishing" target="_blank">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id1" style="">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">2</div>
                                        <h3>Sangia News & Publishing</h3>
                                        <p class=""></p>
                                    </div>
                                </a>
                            </div>
                        </li>                        
                        <li>
                            <div id="id64" class="">
                                <a class="cms-teaser-text" href="//www.linkedin.com/company/sangia-publishing" target="_blank">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id1" style="">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">3</div>
                                        <h3>Sangia Publishing group</h3>
                                        <p>Follow Sangia Research Media & Publishing</p>
                                    </div>
                                </a>
                            </div>
                        </li>
                        <li class="u-hide">
                            <div id="id10" class="">
                                <a class="cms-teaser-text" href="#" data-track="click" data-track-label="#" data-track-category="content links" data-track-action="click - # - >Sign up for our e-newsletter">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id2" style="height: 127px;">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">&gt;</div>
                                        <h3>Sign up for our e-newsletter</h3>
                                    </div>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="common/footer.tpl"}

