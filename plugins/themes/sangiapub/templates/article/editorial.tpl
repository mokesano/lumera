{**
 * templates/article/editorial.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Editor Article Journal
 *
 * @file editorial.tpl
 * @brief Template untuk menampilkan cuplikan journal managers dan editors di halaman article
 *}

{if $editAssignments && $editAssignments|@count > 0}
<h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">
    {translate key="common.article.editors"}
</h3>

<div id="editors" class="p-separator cms-person overview">
    <ul class="app-editor-row">
    {foreach from=$editAssignments item=assignment}
        {* Mengambil objek User yang sudah disuntikkan di DAO *}
        {assign var=editor value=$assignment->getData('editorUser')}
        
        <li class="app-editor-row__item">
            <div class="editor">
                <div class="editor-name cms-person">
                    {if $editor->getProfilePictureName()}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$editor->getProfilePictureName()|escape}" alt="Profile Picture" width="50" height="50">
                    </figure>
                    {else}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" 
                            {if $editor->getGender() == "M"}src="//assets.sangia.org/static/images/contactPersonM.png"
                            {elseif $editor->getGender() == "F"}src="//assets.sangia.org/static/images/contactPersonF.png"
                            {elseif $editor->getGender() == "O"}src="//assets.sangia.org/static/images/default_203.jpg"
                            {else}src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"{/if} 
                            alt="Profile Picture" width="50" height="50">
                    </figure>
                    {/if}

                    {* Nama Lengkap dan Gelar Method Global *}
                    <h3 class="u-h4 u-fonts-sans u-mb-4" itemprop="name">
                        <a class="button-link author size-m workspace-trigger button-link-primary" href="{url page="about" op="editorial-team-bio" path=$editor->getId()|string_format:"%011d" from="board" anchor=$editor->getFullName()|escape}">{if $editor->getSalutation()}<span class="text degree" itemprop="degree">{$editor->getSalutation()|escape}</span>{/if}{if $editor->getFirstName() !== $editor->getLastName()}<span class="text given-name" itemprop="given-name">{$editor->getFirstName()|escape}</span>{/if}{if $editor->getMiddleName()}<span class="text middle-name" itemprop="middle-name">{$editor->getMiddleName()|escape}</span>{/if}<span class="text surname" itemprop="surname">{$editor->getLastName()|escape}</span>{if $editor->getSuffix()}<span class="last-degree" itemprop="last-degree">, {$editor->getSuffix()|escape}</span>{/if}
                        </a>
                    </h3>

                    {* Afiliasi dan Negara menggunakan Method Global *}
                    {if $editor->getAffiliation($locale)}
                        <dl>{$editor->getAffiliation($locale)|escape|nl2br}{if $editor->getCountryName()}, {$editor->getCountryName()|escape}{/if}</dl>
                    {/if}

                    <p class="u-js-hide u-mb-0">
                        {* SVG Icon Mail tetap sama *}
                        <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$editor->getEmail()|escape}" href="mailto:{$editor->getEmail()|escape}" target="_blank">
                            <span class="anchor-text">{$editor->getEmail()|escape}</span>
                        </a>
                    </p>

                    <dl class="u-js-hide">
                        {if $editor->getUrl()}
                            <p class="u-mb-0">URL Personal: <a href="{$editor->getUrl()|escape}">{$editor->getUrl()|escape}</a></p>
                        {/if}
                        {* Menggunakan getData untuk field custom ID *}
                        {if $editor->getData('sintaId')}
                            <p class="u-mb-0">Sinta ID: {$editor->getData('sintaId')|escape}</p>
                        {/if}
                        {if $editor->getData('scopusId')}
                            <p class="u-mb">Scopus ID: {$editor->getData('scopusId')|escape}</p>
                        {/if}
                    </dl>
                    
                    {* Tanggal Tindakan Editor (Wizdam Modernized) *}
                    <div class="action-dates u-font-size-small u-mt-8 u-js-hide" itemprop="assignment">
                        <time datetime="{$assignment->getDateNotified()|date_format:"$dateFormatShort"}" itemprop="date-assignment" >Assigned: {$assignment->getDateNotified()|date_format:"%e %B %Y"}</time>
                    </div>
                </div>
            </div>
        </li>
    {/foreach}
    </ul>
</div>
{else}
<div id="edited" class="p-separator overview">
    <h3 class="u-h4 u-mb-8 u-font-sans">
        {translate key="submission.noEditorsAssigned"}
    </h3>
    {if $article->getLastModified()}
    <p class="action-dates u-mb-0">
        <time datetime="{$article->getLastModified()|date_format:"$dateFormatShort"}" itemprop="last-modified">{translate key="submission.lastModified"} {$article->getLastModified()|date_format:"%e %B %Y"}</time>
    </p>
    {/if}
</div>
{/if}

{if $reviewAssignments && $reviewAssignments|@count > 0}
<h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">
    {translate key="common.article.reviewed"}
</h3>

<div id="reviewers" class="p-separator cms-person overview">
    <ul class="app-reviewer-row">
    {foreach from=$reviewAssignments item=assignment}
        {* Mengambil objek User yang sudah disuntikkan di DAO *}
        {assign var=reviewer value=$assignment->getData('reviewerUser')}
        
        <li class="app-reviewer-row__item">
            <div class="reviewer">
                <div class="reviewer-name cms-person">
                    {if $reviewer->getProfilePictureName()}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$reviewer->getProfilePictureName()|escape}" alt="Profile Picture" width="50" height="50">
                    </figure>
                    {else}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" 
                            {if $reviewer->getGender() == "M"}src="//assets.sangia.org/static/images/contactPersonM.png"
                            {elseif $reviewer->getGender() == "F"}src="//assets.sangia.org/static/images/contactPersonF.png"
                            {else}src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"{/if} 
                            alt="Profile Picture" width="50" height="50">
                    </figure>
                    {/if}

                    <h3 class="u-h4 u-mb-4 u-mt-0">
                        <a class="button-link author size-m workspace-trigger button-link-primary" href="{url page="about" op="editorialTeamBio" path=$reviewer->getId()|string_format:"%011d" from="membership" anchor=$reviewer->getFullName()|escape}">{if $reviewer->getSalutation()}<span class="text degree" itemprop="degree">{$reviewer->getSalutation()|escape}</span>{/if}{if $reviewer->getFirstName() !== $reviewer->getLastName()}<span class="text given-name" itemprop="given-name">{$reviewer->getFirstName()|escape}</span>{/if}{if $reviewer->getMiddleName()}<span class="text middle-name" itemprop="middle-name">{$reviewer->getMiddleName()|escape}</span>{/if}<span class="text surname" itemprop="surname">{$reviewer->getLastName()|escape}</span>{if $reviewer->getSuffix()}<span class="last-degree" itemprop="last-degree">, {$reviewer->getSuffix()|escape}</span>{/if}
                        </a>
                    </h3>

                    {if $reviewer->getAffiliation($locale)}
                        <dl>{$reviewer->getAffiliation($locale)|escape|nl2br}{if $reviewer->getCountryName()}, {$reviewer->getCountryName()|escape}{/if}</dl>
                    {/if}
                    
                    {* Tanggal Selesai Review *}
                    <div class="action-dates u-font-size-small u-mt-8 u-js-hide"  itemprop="assignment">
                        <time datetime="{$assignment->getDateCompleted()|date_format:"$dateFormatShort"|escape}" itemprop="date-assignment">Review Completed: {$assignment->getDateCompleted()|date_format:"%e %B %Y"|escape}</time>
                    </div>
                </div>
            </div>
        </li>
    {/foreach}
    </ul>
</div>
{/if}