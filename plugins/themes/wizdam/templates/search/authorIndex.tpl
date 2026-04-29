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
{include file="common/header-ABOUT.tpl"}
{/strip}

<div class="c-jump">
	<p class="describe u-font-sans">Click the alphabet to chose name authors</p>	
	<span class="c-jump-navigation">{foreach from=$alphaList item=letter}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url op="authors" searchInitial=$letter}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url op="authors"}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></span>
</div>

<div id="authors">
{iterate from=authors item=author}
	{assign var=lastFirstLetter value=$firstLetter}
	{assign var=firstLetter value=$author->getLastName()|String_substr:0:1}

	{if $lastFirstLetter|lower != $firstLetter|lower}
	<div id="{$firstLetter|escape}" class="sangia-author--index">
		<header class="c-anchored-heading"><h3>{$firstLetter|escape}</h3><a class="c-anchored-heading__helper" href="#main">Back to top</a>
		</header>
	</div>
	{/if}

	{assign var=lastAuthorName value=$authorName}
	{assign var=lastAuthorCountry value=$authorCountry}
	{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
	{assign var=authorCountry value=$author->getCountry()}
	{assign var=authorFirstName value=$author->getFirstName()}
	{assign var=authorMiddleName value=$author->getMiddleName()}
	{assign var=authorLastName value=$author->getLastName()}
	{assign var=authorName value="$authorLastName, $authorFirstName"}

	{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}
	{strip}
	<div class="author-member u-font-serif">
		{if $profileImage}		
		<div class="sangia-author--profile member">
			<img class="sangia-author editor-image u-font-sans" height="{$profileImage.height|escape}" width="{$profileImage.width|escape}" alt="{$author->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
		</div>	
			{else}
		<div class="sangia-author--profile member">
			<style type="text/css">.sangia-author {margin: 0 0 .7em .7em;}</style>
			<img class="sangia-author u-font-sans" alt="{$author->getFullName()|escape}" src="//media.stipwunaraha.ac.id/static/contactPersonM.png" width="112" height="128">
		</div>
		{/if}
		
		<div class="sangia-author--name u-font-serif">
			<a href="{url op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}">{$authorName|escape}</a>	
		</div>
		
		<div class="sangia-author--affiliation u-font-serif">
		{if $authorAffiliation}{$authorAffiliation|escape}{/if}
		{if $lastAuthorName == $authorName && $lastAuthorCountry != $authorCountry}
			{* Disambiguate with country if necessary (i.e. if names are the same otherwise) *}
			{if $authorCountry} ({$author->getCountryLocalized()}){/if}
		{/if}			
		</div>
		
		<div class="sangia-author--link u-font-serif">
			<span class="author--link u-font-serif"><a href="{url op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}">View Profile</a></span>
			{if $author->getData('url')}
			<span class="author--link u-font-serif"><a href="">Scholar Profile</a></span>{/if}
			{if $author->getData('sinta')}
			<span class="author--link u-font-serif"><a href="">Sinta Profile</a></span>{/if}
			{if $author->getData('orcid')}
			<span class="author--link u-font-serif"><a href="">Orchid Profile</a></span>{/if}
			{if $author->getData('scopus')}
			<span class="author--link u-font-serif"><a href="">Scopus Profile</a></span>{/if}
		</div>
	</div>	
	{/strip}
{/iterate}

    <div id="colspan" class="colspan-anchor">
        <table width="100%">
            <tbody>
                {if !$authors->wasEmpty()}
                <tr valign="bottom">
                    <td width="50%" {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$authors}</td>
                    <td width="50%" colspan="2" style="text-align: right;">{page_links anchor="authors" iterator=$authors name="authors" searchInitial=$searchInitial}</td>
                    </tr>
                {else}
                <br />
                {/if}
             </tbody>
         </table>
     </div>

</div>

{include file="common/footer.tpl"}

