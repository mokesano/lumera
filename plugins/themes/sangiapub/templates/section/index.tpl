{**
 * templates/section/index.tpl
 *
 * [WIZDAM] Section Index Page
 * Menampilkan editor dan artikel yang terbit di section ini.
 *}
{strip}
{assign var="pageTitleTranslated" value=$section->getLocalizedTitle()}
{include file="common/header-index.tpl"}
{/strip}

<div class="section-index">

    {* Header Section *}
    <div class="section-header">
        {if $section->getLocalizedAbbrev()}
        <span class="section-abbrev u-js-hide">{$section->getLocalizedAbbrev()|escape}</span>
        {/if}
        <div class="section-meta u-js-hide">
            <span>{$journalTitle|escape}</span>
            {if $printIssn}<span>ISSN: {$printIssn|escape}</span>{/if}
            {if $onlineIssn}<span>E-ISSN: {$onlineIssn|escape}</span>{/if}
        </div>
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

    {* Section Editors *}
    {if $sectionEditors}
    <div class="section-editors">
        <h2>{translate key="user.role.sectionEditor"}</h2>
        <div class="editors-list">
        {foreach from=$sectionEditors item=editor}
            <div class="editor-item">
                {* Foto Profil *}
                {if $editor.user->getProfileImageUrl()}
                <img src="{$editor.user->getProfileImageUrl()|escape}" 
                     alt="{$editor.user->getFullName()|escape}" 
                     class="editor-photo" />
                {/if}

                {* Identitas — Template bebas akses semua getter PKPUser *}
                <div class="editor-identity">
                    <strong>{$editor.user->getFullName()|escape}</strong>

                    {if $editor.user->getLocalizedAffiliation()}
                    {assign var="affiliations" value=$editor.user->getLocalizedAffiliation()|explode:"\n"}
                    {foreach from=$affiliations item=affiliation}
                        {if $affiliation|trim}
                        <p class="affiliation">{$affiliation|escape}</p>
                        {/if}
                    {/foreach}
                    {/if}

                    {if $editor.user->getCountry()}
                    <p class="country">{$editor.user->getCountryName()|escape}</p>
                    {/if}

                    {* Identifiers *}
                    {if $editor.user->getData('orcid')}
                    <a href="https://orcid.org/{$editor.user->getData('orcid')|escape}" 
                       target="_blank" class="orcid-link">
                        ORCID
                    </a>
                    {/if}

                    {if $editor.user->getSintaId()}
                    <a href="https://sinta.kemdikbud.go.id/authors/profile/{$editor.user->getSintaId()|escape}" 
                       target="_blank" class="sinta-link">
                        Sinta
                    </a>
                    {/if}

                    {if $editor.user->getScopusId()}
                    <a href="https://www.scopus.com/authid/detail.uri?authorId={$editor.user->getScopusId()|escape}" 
                       target="_blank" class="scopus-link">
                        Scopus
                    </a>
                    {/if}
                </div>
            </div>
        {/foreach}
        </div>
    </div>
    {/if}

    {* Artikel di Section Ini - Artikel Terbaru — 4 saja *}
    {if $publishedArticles}
    <div class="section-recent-articles">
        <h2>Recent Articles</h2>
        {foreach from=$publishedArticles item=article}
        <div class="article-item">
            <h3>
                <a href="{url page="article" op="view" path=$article->getId()}">
                    {$article->getLocalizedTitle()|escape}
                </a>
            </h3>
            <div class="article-authors">
                {foreach from=$article->getAuthors() item=author name=authorLoop}
                    {$author->getFullName()|escape}{if !$smarty.foreach.authorLoop.last}, {/if}
                {/foreach}
            </div>
        </div>
        {/foreach}
    
        {* Link ke semua artikel jika lebih dari 4 *}
        {if $totalArticleCount > 4}
        <div class="view-all-articles">
            <a href="{$allArticlesUrl|escape}">
                View all {$totalArticleCount} articles →
            </a>
        </div>
        {/if}
    </div>
    {else}
    <div class="section-no-articles">
        <p>{translate key="section.noArticles"}</p>
    </div>
    {/if}

</div>

{include file="common/footer.tpl"}