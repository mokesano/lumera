{**
 * templates/about/publisherPage.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Publishing
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * Dipakai bersama oleh PublisherAboutHandler::mission()/history()/
 * leadership()/award() lewat _renderPublisherPage() -- dibedakan lewat
 * $pageTitleKey (locale key judul) dan $pageContent (teks dari Site
 * Settings, lihat PublisherProfileService/AboutSiteForm).
 *}
{strip}
    {assign var="pageTitle" value=$pageTitleKey}
    {include file="common/header.tpl"}
{/strip}

<div id="publisherStaticPage" class="wizdam-publisher-page">
    {if $pageContent|trim != ''}
        <div class="wi-page-content">
            {$pageContent|nl2br}
        </div>
    {else}
        <p class="wi-page-empty">
            {translate key="about.publisher.noContent"}
        </p>
    {/if}
</div>

{include file="common/footer.tpl"}
