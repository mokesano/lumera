{if ($pageDisplayed == "site") }
<div id="submit-button" class="largeButton">
	<a href="mailto:journals@sangia.org">Contact Sangia Publishing</a>
</div>
{else}
<div id="submit-button" class="largeButton">
	<a href="{url page="about" op="submissions"}">{translate key="user.noRoles.submitArticle"}</em></a>
</div>
{/if}
