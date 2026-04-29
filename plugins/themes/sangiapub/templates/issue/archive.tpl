{**
 * templates/issue/archive.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Halaman Arsip Volume (Telah dimodifikasi untuk HANYA menampilkan volume)
 *
 *}

{strip}
{assign var="pageTitle" value="archive.archives"}
{include file="common/header-ISSUE.tpl"}
{/strip}

{if $currentJournal->getLocalizedSetting('history') != ''}
<div id="journal-history-link" class="content">
    <span class="icon-container info history-link">
        This journal was previously published under other titles
        <a href="{url page="about" op="history" anchor=""}">(view Journal {translate key="about.history"})</a>
    </span>
</div>
{/if}

<div class="content mb30 mq1200-padded position-relative">
    <section>
        {* KONDISI JIKA TIDAK ADA DATA *}
        {if $issues->wasEmpty()}
            <div class="container cleared container-type-title" data-container-type="title">
                <div class="border-top-1 border-gray-medium"></div>
                <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
                    <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center">
                        <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
                    </div>
                    <div class="c-empty-state-card__text">
                        <h3 class="c-empty-state-card__text--title headline-5">{translate key="current.noCurrentIssueDesc"}</h3>
                        <div class="c-empty-state-card__text--description">We are currently preparing our inaugural content. Please check back soon for our upcoming publications, or consider <a href="{url page="author" op="submit"}">submitting your manuscript</a> to be part of our first issue. Visit our <a href="{url page="about" op="submissions"}">Submission Guidelines</a> for more information.</div>
                    </div>
                </div>
            </div>
        {else}
            {* TAMPILAN JIKA DATA ADA *}
            <div class="pa40 cleared background-white">
                <ul id="volume-decade-list" class="clean-list ma0 grid-auto-fill medium-row-gap background-white" style="--column-width: 120px;">
                    {iterate from=issues item=issue}
                        {if $issue->getYear() != $lastYear}
                            {assign var=lastYear value=$issue->getYear()}
                            <li>
                                <div id="{$issue->getVolume()|escape}" class="volumes-detail">
                                    <h2 class="mb6 strong tighten-line-height text-gray volumes-detail__year">
                                        {$issue->getDatePublished('issue.firstYear')|date_format:"%Y"}
                                    </h2>
                                    <a href="{native_url page="volume" volume=$issue->getVolume()}" class="volumes-detail__link">
                                        <span class="title">{translate key="issue.volume"} {$issue->getVolume()|escape}</span>
                                    </a>
                                </div>
                            </li>
                        {/if}
                    {/iterate}
                </ul>
            </div>
        {/if}
    </section>
</div>

</main>
{include file="common/footer.tpl"}
