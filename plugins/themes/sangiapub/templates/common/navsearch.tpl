{**
 * common/search.tpl
 *
 *
 * Search Bar
 *
 *}

<div class="c-header__container">
    <h2 class="c-header__visually-hidden">Search</h2>
{capture assign="filterInput"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName=$filterName filterValue=$filterValue}{/capture}
{* Determine search type and base URL *}
{if $smarty.get.journal == '' || $smarty.get.journal == 'all' || (!$smarty.get.journal && !$currentJournal)}
    {* All Journals Search *}
    {url|assign:"searchFormUrl" journal="index" page="search" escape=false}
    {assign var="isAllJournals" value=true}
{else}
    {* This Journal Search *}
    {url|assign:"searchFormUrl" page="search" escape=false}
    {assign var="isAllJournals" value=false}
{/if}

{* Parse existing URL parameters *}
{$searchFormUrl|parse_url:$smarty.const.PHP_URL_QUERY|parse_str:$formUrlParameters}

<form class="c-header__search-form" action="{$searchFormUrl|strtok:"?"|escape}" method="GET" role="search" autocomplete="off" data-track="submit" data-track-action="search" data-track-label="form" data-track-category="inline search" onsubmit="return validateSearch(event)">
    
    {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
    <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">
    
    <label class="c-header__heading" for="keywords">Search articles by subject, keyword or author</label>
    
    <div class="c-header__search-layout c-header__search-layout--max-width">
        {capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
        {if empty($queryFilter)}
            <div>
                {* Preserve other URL parameters except journal *}
                {foreach from=$formUrlParameters key=paramKey item=paramValue}
                    {if $paramKey != 'journal' && $paramKey != 'query'}
                        <input type="hidden" name="{$paramKey|escape}" value="{$paramValue|escape}"/>
                    {/if}
                {/foreach}
                
                <input class="c-search__input" type="text" id="query" name="query" value="{$query|escape}" placeholder="Search" required>
            </div>
        {else}
            {$queryFilter}
        {/if}
        
        <div class="c-header__search-layout">
            <div>
                <label for="journal" class="u-visually-hidden">Journal</label>
                <select class="c-search__select" 
                        data-track="change" 
                        data-track-action="search" 
                        data-track-label="journal" 
                        data-track-category="inline search" 
                        name="journal" 
                        id="journal"
                        onchange="handleJournalChange(this)">
                    <option value="all" {if $isAllJournals}selected="selected"{/if}>All journals</option>
                    {if $currentJournal}
                        <option value="{$currentJournal->getPath()|strip_tags|lower|escape}" {if !$isAllJournals}selected="selected"{/if}>This journal</option>
                    {/if}
                </select>
            </div>
        </div>
        
        <div>
            <button type="submit" value="Search" class="c-search__button" data-action="clear-adv">
                <span class="c-search__button-text">Search</span>
                <svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path>
                </svg>
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
{literal}
<!--
// Simple and GUARANTEED working validation
function validateSearch(event) {
    var input = document.getElementById('query');
    var value = input.value.trim();
    
    if (value === '') {
        alert('Please fill out this field.');
        input.focus();
        return false;
    }
    return true;
}

function handleJournalChange(selectElement) {
    const form = selectElement.closest('form');
    const journalValue = selectElement.value;
    const baseUrl = window.location.origin;
    
    if (journalValue === 'all') {
        form.action = baseUrl + '/index/search';
    } else {
        form.action = baseUrl + '/' + journalPath + '/search';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const journalSelect = document.getElementById('journal');
    
    if (journalSelect) {
        handleJournalChange(journalSelect);
    }
});

// HANYA attach ke form search tertentu - BUKAN semua form
document.addEventListener('DOMContentLoaded', function() {
    // Target HANYA form search dengan selector spesifik
    var searchForms = document.querySelectorAll(
        'form[role="search"], ' +
        'form.c-header__search-form, ' +
        'form[data-track-category*="inline search"], ' +
        '.s-search form'
    );
    
    for (var i = 0; i < searchForms.length; i++) {
        var form = searchForms[i];
        // Double check: pastikan ada input query sebelum attach
        if (form.querySelector('input[name="query"]')) {
            form.onsubmit = function() { return checkEmpty(this); };
        }
    }
});
// -->
{/literal}
</script>

    <div class="c-header__flush"><a class="c-header__link" href="{$url page="search" op="search"}" data-track="click" data-track-action="advanced search" data-track-label="link">Advanced search</a>
    </div>
    <h3 class="c-header__heading c-header__heading--keyline">Quick links</h3>
    <ul class="c-header__list">
        <li><a class="c-header__link" href="{url page="browseSearch" op="sections"}" data-track="click" data-track-action="explore articles by section" data-track-label="link">Explore articles by section</a></li>
        <li><a class="c-header__link" href="//www.sangia.org" data-track="click" data-track-action="find a news daily" data-track-label="link">Sangia Daily</a></li>
        <li><a class="c-header__link" href="{url page="about" op="submissions"}" data-track="click" data-track-action="guide for authors" data-track-label="link">Guide for authors</a></li>
        <li><a class="c-header__link" href="{url page="about" op="editorialPolicies"}" data-track="click" data-track-action="editorial policies" data-track-label="link">Editorial policies</a></li>
    </ul>
</div>
