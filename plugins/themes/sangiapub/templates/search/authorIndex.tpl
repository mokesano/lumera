{**
 * templates/search/authorIndex.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Index of published articles by author.
 *
 *}
{strip}
{assign var="pageTitle" value="search.authorIndex"}
{include file="common/header-parts/header-overview.tpl"}
{/strip}

<div class="c-jump u-mb-48 u-mt-32">
	<p class="describe u-font-sans">Click the alphabet to chose name authors</p>
	<span class="c-jump-navigation">{foreach from=$alphaList item=letter}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url op="authors" searchInitial=$letter}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url op="authors"}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></span>
</div>

<div id="authors-index" class="Editors">
<div id="author" class="helping-header author-card">
{iterate from=authors item=author}
	{assign var=lastFirstLetter value=$firstLetter}
	{assign var=firstLetter value=$author->getLastName()|String_substr:0:1}

	{if $lastFirstLetter|lower != $firstLetter|lower}
		{if !$firstTime}
			</div> {* Close previous CardWrapper *}
		{else}
			{assign var=firstTime value=0}
		{/if}
		
		{* Header alfabet - di luar CardWrapper *}
		<div id="{$firstLetter|escape}" class="cosire-author--index">
			<header class="c-anchored-heading"><h3>{$firstLetter|escape}</h3><a class="c-anchored-heading__helper" href="#main">Back to top</a>
			</header>
		</div>
		{* Buka CardWrapper baru untuk kelompok alfabet ini *}
		<div class="CardWrapper">
	{/if}
	
	{* --- [Mapping Data Objek ke Variabel Smarty] --- *}
    {assign var=authorId value=$author->getId()}
    {assign var=isVerifiedAuthor value=$author->getData('isVerifiedAuthor')}
    {assign var=userGender value=$author->getData('userGender')}
    {assign var=hasProfileImage value=$author->getData('hasProfileImage')}
    {assign var=profileImageUrl value=$author->getData('profileImageUrl')}
    {assign var=userInterests value=$author->getData('userInterests')}
    {assign var=userSalutation value=$author->getData('userSalutation')}
    {assign var=userPhone value=$author->getData('userPhone')}
    {assign var=userFax value=$author->getData('userFax')}
    {* ------------------------------------------------ *}
    
	{assign var=authorMiddleName value=$author->getMiddleName()}
	{assign var=authorFirstName value=$author->getFirstName()}
	{assign var=authorLastName value=$author->getLastName()}
	{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
	{assign var=authorCountry value=$author->getCountry()}
	{assign var=authorName value="$authorLastName, $authorFirstName"}
	{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}
	{strip}

	<article data-author-id="{$authorId|string_format:"%011d"}" class="CardEditor">
        {if $isVerifiedAuthor}
            <span class="verified-stamp" style="color: white; padding: 4px 8px;text-align: center;">
                Verified
            </span>
        {/if}
	    <a class="CardEditor__wrapper" href="{url op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" target="_blank" data-event="cardAuthor-a-{$authorId|string_format:"%07d"}">
	        <div class="CardEditor__info">
	            <figure class="CardEditor__mask{if $userGender} data-gender="{$userGender|escape}"{/if}">
	                <figure class="Avatar Avatar--size-96">
                    {* Profile image - use matched data if available *}
                    {if $hasProfileImage}
                        <img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="{$author->getFullName()|escape}" src="{$profileImageUrl|escape}" width="150" height="auto">
                    {elseif $profileImage}
                        {assign var="profileImage" value=$user->getSetting('profileImage')}
                        <img class="lazyload cosire-author editor-image Avatar__img is-inside-mask" loading="lazy" height="{$profileImage.height|escape}" width="{$profileImage.width|escape}" alt="{$author->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
                    {else}
                        {* Default image based on gender if available *}
                        <div class="cosire-author--profile member">
                            {if $userGender == 'M'}
                                <img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="{$author->getFullName()|escape}" src="//assets.sangia.org/img/contactPersonM.png" width="150" height="auto">
                            {elseif $userGender == 'F'}
                                <img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="{$author->getFullName()|escape}" src="//assets.sangia.org/img/contactPersonF.png" width="150" height="auto">
                            {else}
                                <img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="{$author->getFullName()|escape}" src="//assets.sangia.org/static/images/default_203.jpg" width="150" height="auto">
                            {/if}
                        </div>
                    {/if}
	                </figure>
	            </figure>
	            <div class="CardEditor__detail notranslate">
	                <div class="CardEditor__name cosire-author--name u-fonts-sans">
                		{if $userSalutation || $userPrefix}
                		<div class="italic surname full">{if $userSalutation}{$userSalutation|escape} {/if}{if $userPrefix} {$userPrefix|escape}{/if}
                		</div>
                		{/if}
                		{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName}</span>{/if}
                		{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName}</span>{/if}
                		<span class="text surname">{$authorLastName}</span>
	                </div>
	                {if $authorAffiliation}
    	                <div class="CardEditor__affiliation__name u-font-sangia-sans" itemprop="affiliation" itemtype="http://schema.org/Affiliation">
                        {assign var="affiliations" value=$authorAffiliation|explode:"\n"}
                        {foreach from=$affiliations item="affiliation" name="affiliationLoop"}
                                {$affiliation|escape}
                        {/foreach}
	                    </div>
    	                {if $smarty.foreach.affiliationLoop.last && $authorCountry}
    	                <div class="CardEditor__affiliation__location u-font-sangia-sans">
    	                    {$author->getCountryLocalized()}
    	                </div>
    	                {/if}
	                {/if}
	            </div>
	        </div>
	        <div class="CardEditor__more u-font-sangia-sans">
	            <section class="CardEditor__role">AuthorID: {$authorId|string_format:"%011d"}</section>
	            {if $isEditor && !empty($editorSections)}
	            <section class="CardEditor__role">{foreach from=$editorSections item=section name=sectionLoop}{$section}{if !$smarty.foreach.sectionLoop.last}, {/if}{/foreach}</section>
	            {/if}
	            {if !empty($userInterests)}
	            <section class="CardEditor__journal">{foreach from=$userInterests item=interest name=interestLoop}{$interest|escape}{if !$smarty.foreach.interestLoop.last}, {/if}{/foreach}</section>
	            {/if}
	            {if $authorUrl || $userPhone || $authorOrcid || $userFax}{/if}
	        </div>
    	</a>
	</article>
	{/strip}

{/iterate}

{* Tutup CardWrapper terakhir jika ada data *}
{if $authors->getCount()}
</div>
{/if}

</div>

{if !$authors->wasEmpty()}
<div class="colspan u-mb-0 u-mt-32" id="colspan">	    
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View {page_info iterator=$authors}</div>
    </section>
    {if $authors->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="authors" iterator=$authors name="authors" searchInitial=$searchInitial}
        </div>
    </section>
    {/if}
</div>
{else}
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
            </div>
        <div class="c-empty-state-card__text">
            <h2 class="c-empty-state-card__text--title headline-5">{translate key="search.noResults"}</h2>
            <div class="c-empty-state-card__text--description">There are currently no authors registered in the system, or your search criteria did not match any existing author profiles. Try adjusting your search terms or browse all available content.</div>
        </div>
    </div>
</div>
{/if}

</div>

{include file="common/footer.tpl"}