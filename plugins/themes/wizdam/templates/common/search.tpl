{**
 * common/search.tpl
 *
 *
 * Search Bar
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#searchForm').pkpHandler('$.pkp.pages.search.SearchFormHandler');
	{rdelim});
</script>
<form class="lm-site-search" method="GET" id="search-bar" action="{url page="search" op="search"}">
	{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
	{if empty($queryFilter)}
	<div class="ms-search-field">
		<input type="text" id="query" name="query" value="{$query|escape}" placeholder="Search" class="lm-search-term" />
	</div>
	{else}
		{$queryFilter}
	{/if}
	<button type="submit" value="{translate key="common.search"}" class="uk-button uk-button-primary btn-search"><svg class="lm-icon-search" viewBox="0 0 32 32"><path fill="inherit" d="M31.1 26.9l-8.8-8.8c1.1-1.8 1.7-3.9 1.7-6.1 0-6.6-5.4-12-12-12s-12 5.4-12 12 5.4 12 12 12c2.2 0 4.3-0.6 6.1-1.7l8.8 8.8c0.6 0.6 1.4 0.9 2.1 0.9s1.5-0.3 2.1-0.9c1.2-1.2 1.2-3.1 0-4.2zM3 12c0-5 4-9 9-9s9 4 9 9c0 5-4 9-9 9s-9-4-9-9z"></path></svg></button>
</form>