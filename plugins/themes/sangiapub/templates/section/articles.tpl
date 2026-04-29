{**
 * templates/section/articles.tpl
 *
 * [WIZDAM] Halaman semua artikel section dengan paginasi.
 *}
{strip}
{assign var="pageTitleTranslated" value=$section->getLocalizedTitle()}
{include file="common/header-index.tpl"}
{/strip}

<div class="section-articles-page">

    <div class="section-header">
        <div class="section-nav">
            <a href="{url page="section" op=$section->getSectionUrlTitle()}">
                {translate key="section.sectionTheSection"}
            </a>
            <a href="{url page="section" op=$section->getSectionUrlTitle() path="about"}">
                {translate key="section.aboutTheSection"}
            </a>
            <a href="{url page="section" op=$section->getSectionUrlTitle() path="articles"}" class="active">
                {translate key="section.sectionArticle"}
            </a>
        </div>
    </div>

    <div class="section-articles-list">
        <p class="total-count">{$totalCount} articles</p>

        {iterate from=articles item=article}
        <div class="article-item">
            <h3>
                <a href="{url page="article" op="view" path=$article->getId()}">
                    {$article->getLocalizedTitle()|escape}
                </a>
            </h3>
            <div class="article-meta">
                <span class="date-published">{$article->getDatePublished()|date_format:"%Y"}</span>
            </div>
            <div class="article-authors">
                {foreach from=$article->getAuthors() item=author name=authorLoop}
                    {$author->getFullName()|escape}{if !$smarty.foreach.authorLoop.last}, {/if}
                {/foreach}
            </div>
        </div>
        {/iterate}

        {* Paginasi menggunakan sistem bawaan OJS *}
        {page_links anchor="articles" name=$rangeInfoName iterator=$articles}
    </div>

</div>

{include file="common/footer.tpl"}