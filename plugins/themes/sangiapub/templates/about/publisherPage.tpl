{**
 * templates/about/publisherPage.tpl
 *
 * Copyright (c) 2024-2026 Sangia Lumera Publishing
 * Copyright (c) 2024-2026 Rochmady and Codecanau
 * Distributed under the GNU GPL v3.
 *
 * [FIX] Template ini SEBELUMNYA berkas kosong (0 byte) sejak pertama kali
 * dibuat -- lihat commit e8c45053 (blob e69de29b, "empty file"). Akibatnya
 * keempat halaman statis Penerbit (/about/mission, /about/history,
 * /about/leadership, /about/award) menampilkan halaman kosong sama sekali,
 * bukan error yang mudah dikenali, sehingga mudah luput dari pengamatan.
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
        <p class="wi-page-empty" style="color: #777; font-style: italic;">
            {translate key="about.publisher.noContent"}
        </p>
    {/if}
</div>

{include file="common/footer.tpl"}
