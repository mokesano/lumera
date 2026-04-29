{**
 * templates/common/search.tpl
 *
 *
 * Search Bar
 *
 *}

<div class="bar-dock">
	<div class="bar-wrapper" style="left: 0px; width: 100%;">
		<div class="bar-actions">
			<span id="journal-issue-volume-action-bar-link" class="action icon-browse-alt">
				{if $issue}<a href="{url page="issue" op="archive"}">Browse Volumes &amp; Issues</a>{else}<a href="{url page="index"}">Up to Journal</a>{/if}
			</span>
			<form class="searchWithinForm" method="get" action="{url page="search" op="search"}">
				{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
				{if empty($queryFilter)}
				<input class="focus search-within" name="query" type="text" autocomplete="off" title="Search within this journal" placeholder="Search within this journal">
				<input name="search-within" type="hidden" value="Journal">
				<input name="facet-journal-id" type="hidden" value="{$query|escape}">
				<input class="search-submit" value="Submit" title="Submit" type="submit"><img class="loading js-hidden" src="//www.stipwunaraha.ac.id/static/images/classical/loading_search.gif" alt="">
				{else}
					{$queryFilter}
				{/if}
			</form>
		</div>
	</div>
	<div class="bar-wrapper bar-scrolled" style="left: 204.5px; width: 940px;">
		<div class="bar-actions">
			<span id="journal-issue-volume-action-bar-link" class="action icon-browse-alt">
				{if $issue}<a href="{url page="issue" op="archive"}">Browse Volumes &amp; Issues</a>{else}<a href="{url page="index"}">Up to Journal</a>{/if}
			</span>
			<form class="searchWithinForm" method="get" action="{url page="search" op="search"}">
				{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
				{if empty($queryFilter)}
				<input class="focus search-within" name="query" type="text" autocomplete="off" title="Search within this journal" placeholder="Search within this journal">
				<input name="search-within" type="hidden" value="Journal">
				<input name="facet-journal-id" type="hidden" value="{$query|escape}">
				<input class="search-submit" value="Submit" title="Submit" type="submit"><img class="loading js-hidden" src="//www.stipwunaraha.ac.id/static/images/classical/loading_search.gif" alt="">
				{else}
					{$queryFilter}
				{/if}
			</form>
		</div>
	</div>	
</div>
