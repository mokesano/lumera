{**
 * common/search.tpl
 *
 *
 * Search Bar
 *
 *}

<div class="u-container u-search u-mt-0 u-mb-32">
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#searchForm').pkpHandler('$.pkp.pages.search.SearchFormHandler');
	{rdelim});
</script>
    <div class="s-search c-search--background">
        {capture assign="filterInput"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName=$filterName filterValue=$filterValue}{/capture}
        {url|assign:"searchFormUrl" page="search" op="search" escape=false}
        {$searchFormUrl|parse_url:$smarty.const.PHP_URL_QUERY|parse_str:$formUrlParameters}
        <form action="{$searchFormUrl|strtok:"?"|escape}" method="GET" role="search" autocomplete="off" data-track="submit" data-track-action="search" data-track-label="form" data-track-category="inline search">
            
            {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
            <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">
            
            <input type="hidden" value="{$currentJournal->getLocalizedInitials()|strip_tags|lower|escape}" name="journal">
            <label class="c-search__input-label" for="keywords">Search {$currentJournal->getLocalizedTitle()|strip_tags|escape}</label>
            <div class="c-search__field">
            	{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
            	{if empty($queryFilter)}
                <div class="c-search__input-container c-search__input-container--sm">
        			{foreach from=$formUrlParameters key=paramKey item=paramValue}
            		<input type="hidden" name="{$paramKey|escape}" value="{$paramValue|escape}"/>
        			{/foreach}
        			<input id="search-keywords" type="text" data-test="search-box" name="query" value="{$query|escape}" placeholder="Search" class="c-search__input" />
    			</div>
        		{elseif $hasActiveFilters}
        		    {$filterValue|escape}	
        		{else}
        			{$queryFilter}
        		{/if}           
                <div class="c-search__select-container">
                    <label for="subject" class="u-visually-hidden">Subject</label>
                    <select class="c-search__select" data-track="change" data-track-action="search" data-track-label="subject" data-track-category="inline search 150" name="subject" id="subject">All Subjects
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="c-search__button-container">
                    <button type="submit" class="c-search__button" value="{translate key="common.search"}">
                        <span class="c-search__button-text">Search</span>
                        <svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                    </button>
                </div>
            </div>
        </form> 
    </div>
</div>	